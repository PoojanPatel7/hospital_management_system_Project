<?php
require_once '../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$hospital_id = $_SESSION['hospital_id'] ?? 0;

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_all') {
        $beds = [];
        $hCond = $hospital_id ? "WHERE (b.hospital_id = $hospital_id OR b.hospital_id IS NULL)" : "";
        
        $sql = "
            SELECT b.*, 
                   p.name, p.surname, p.father_name, p.phone, p.demographics, p.gender, p.blood_group, p.age,
                   p.emergency_contact_name, p.emergency_contact_phone,
                   a.id as appointment_id, a.type as appt_type, a.status as appt_status, a.symptoms, a.doctor_notes,
                   a.date as appt_date, a.slot as appt_slot, a.created_at as admitted_at,
                   d.id as doctor_id, d.name as doctor_name, dep.name as dept_name,
                   (SELECT GROUP_CONCAT(dg.description SEPARATOR ', ') FROM diagnoses dg WHERE dg.appointment_id = a.id) as diagnoses_list,
                   (SELECT COUNT(*) FROM prescriptions pr WHERE pr.appointment_id = a.id) as prescriptions_count,
                   (SELECT COUNT(*) FROM patient_files pf WHERE pf.patient_id = b.patient_id) as files_count
            FROM beds b
            LEFT JOIN patients p ON b.patient_id = p.id
            LEFT JOIN appointments a ON a.id = (
                SELECT app.id FROM appointments app 
                WHERE app.patient_id = b.patient_id 
                ORDER BY (app.bed_number = b.bed_number) DESC, app.id DESC 
                LIMIT 1
            )
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN departments dep ON d.department_id = dep.id
            $hCond
            ORDER BY 
                CASE WHEN b.type = 'ICU' THEN 1 ELSE 2 END,
                b.bed_number ASC
        ";
        
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $beds[] = $row;
            }
        } else {
            jsonResponse(['status' => 'error', 'message' => $conn->error]);
        }
        
        jsonResponse(['status' => 'success', 'beds' => $beds]);
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    
    if ($action === 'add') {
        $type = trim($data['type'] ?? 'OPD');
        $number = trim($data['number'] ?? '');
        $wing = trim($data['wing'] ?? ($type === 'ICU' ? 'Critical Care Floor, 3rd Floor' : 'General Ward Floor, 2nd Floor'));
        $id = 'bed-' . time() . '-' . rand(100, 999);
        
        if (!$number) {
            jsonResponse(['status' => 'error', 'message' => 'Bed designation number is required.']);
        }
        
        // Check duplicate
        $chk = $conn->prepare("SELECT id FROM beds WHERE bed_number = ? AND (hospital_id = ? OR hospital_id IS NULL)");
        $chk->bind_param("si", $number, $hospital_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            jsonResponse(['status' => 'error', 'message' => "Bed number '$number' already exists in this hospital."]);
        }
        
        $stmt = $conn->prepare("INSERT INTO beds (id, bed_number, type, wing, status, hospital_id) VALUES (?, ?, ?, ?, 'Available', ?)");
        $stmt->bind_param("ssssi", $id, $number, $type, $wing, $hospital_id);
        
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => "Bed $number ($type) successfully registered."]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to add bed: ' . $conn->error]);
        }
    }
    else if ($action === 'delete') {
        $number = trim($data['number'] ?? '');
        $stmt = $conn->prepare("DELETE FROM beds WHERE bed_number = ? AND status = 'Available'");
        $stmt->bind_param("s", $number);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                jsonResponse(['status' => 'success', 'message' => "Bed $number removed from inventory."]);
            } else {
                jsonResponse(['status' => 'error', 'message' => "Bed is currently occupied or was not found."]);
            }
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to delete bed.']);
        }
    }
    else if ($action === 'discharge') {
        $number = trim($data['number'] ?? '');
        $notes = trim($data['notes'] ?? 'Discharged from inpatient bed. Ready for home rest.');
        
        $stmt = $conn->prepare("SELECT patient_id FROM beds WHERE bed_number = ?");
        $stmt->bind_param("s", $number);
        $stmt->execute();
        $res = $stmt->get_result();
        $bedRow = $res->fetch_assoc();
        $patient_id = $bedRow['patient_id'] ?? null;
        
        if ($patient_id) {
            $conn->query("UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$number'");
            
            // Update appointment
            $conn->query("UPDATE appointments SET status = 'Discharged from Bed', bed_number = NULL WHERE patient_id = '$patient_id' AND bed_number = '$number'");
            
            // Timeline event
            $time_now = date('h:i A');
            $event = "Discharged from inpatient bed $number. $notes";
            $app_res = $conn->query("SELECT id FROM appointments WHERE patient_id = '$patient_id' ORDER BY id DESC LIMIT 1");
            $app_id = ($app_res && $ar = $app_res->fetch_assoc()) ? $ar['id'] : null;
            
            if ($app_id) {
                $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
                $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event);
                $stmtTL->execute();
            }
            
            jsonResponse(['status' => 'success', 'message' => "Patient successfully discharged. Bed $number is now sanitized and available."]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'No patient found assigned to this bed.']);
        }
    }
    else if ($action === 'admit') {
        $bed_number = trim($data['bed_number'] ?? '');
        $patient_id = trim($data['patient_id'] ?? '');
        $doctor_id = !empty($data['doctor_id']) ? trim($data['doctor_id']) : null;
        if ($doctor_id) {
            $docChk = $conn->prepare("SELECT id FROM doctors WHERE id = ?");
            $docChk->bind_param("s", $doctor_id);
            $docChk->execute();
            if ($docChk->get_result()->num_rows === 0) {
                $doctor_id = null;
            }
        }
        $reason = trim($data['reason'] ?? 'Inpatient Observation & Care');
        
        if (!$bed_number || !$patient_id) {
            jsonResponse(['status' => 'error', 'message' => 'Please select both a bed and a patient.']);
        }
        
        // Check if bed is available
        $bRes = $conn->query("SELECT status, type FROM beds WHERE bed_number = '$bed_number'");
        $bRow = $bRes ? $bRes->fetch_assoc() : null;
        if (!$bRow || $bRow['status'] !== 'Available') {
            jsonResponse(['status' => 'error', 'message' => "Bed $bed_number is currently occupied or unavailable."]);
        }
        
        // Update bed to occupied
        $stmtU = $conn->prepare("UPDATE beds SET status = 'Occupied', patient_id = ? WHERE bed_number = ?");
        $stmtU->bind_param("ss", $patient_id, $bed_number);
        $stmtU->execute();
        
        // Create appointment / admission record
        $status_str = "Admitted (" . ($bRow['type'] === 'ICU' ? 'ICU Bed' : 'Ward Bed') . ")";
        $today = date('Y-m-d');
        $time_slot = date('h:i A');
        $stage = 5;
        
        // Determine valid hospital_id
        $target_hosp = $hospital_id > 0 ? (int)$hospital_id : 1;
        $hRes = $conn->query("SELECT hospital_id FROM beds WHERE bed_number = '$bed_number'");
        if ($hRes && $hr = $hRes->fetch_assoc()) {
            if (!empty($hr['hospital_id']) && (int)$hr['hospital_id'] > 0) {
                $target_hosp = (int)$hr['hospital_id'];
            }
        }
        
        $stmtApp = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, status, stage, bed_number, hospital_id) VALUES (?, ?, 'Inpatient Admission', ?, ?, ?, ?, ?, ?, ?)");
        $stmtApp->bind_param("ssssssisi", $patient_id, $doctor_id, $today, $time_slot, $reason, $status_str, $stage, $bed_number, $target_hosp);
        $stmtApp->execute();
        $app_id = $conn->insert_id;
        
        // Timeline
        $time_now = date('h:i A');
        $event = "Admitted to {$bRow['type']} Bed [$bed_number]. Reason: $reason";
        $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
        $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event);
        $stmtTL->execute();
        
        jsonResponse(['status' => 'success', 'message' => "Patient admitted to bed $bed_number successfully."]);
    }
    else if ($action === 'transfer') {
        $current_bed = trim($data['current_bed'] ?? '');
        $new_bed = trim($data['new_bed'] ?? '');
        $reason = trim($data['reason'] ?? 'Bed relocation / Clinical transfer');
        
        if (!$current_bed || !$new_bed) {
            jsonResponse(['status' => 'error', 'message' => 'Both current and destination beds are required.']);
        }
        
        // Check current bed
        $curRes = $conn->query("SELECT patient_id FROM beds WHERE bed_number = '$current_bed'");
        $curRow = $curRes ? $curRes->fetch_assoc() : null;
        $patient_id = $curRow['patient_id'] ?? null;
        
        if (!$patient_id) {
            jsonResponse(['status' => 'error', 'message' => "No patient currently assigned to bed $current_bed."]);
        }
        
        // Check new bed
        $newRes = $conn->query("SELECT status, type FROM beds WHERE bed_number = '$new_bed'");
        $newRow = $newRes ? $newRes->fetch_assoc() : null;
        if (!$newRow || $newRow['status'] !== 'Available') {
            jsonResponse(['status' => 'error', 'message' => "Destination bed $new_bed is not available."]);
        }
        
        // Free current bed
        $conn->query("UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$current_bed'");
        
        // Occupy new bed
        $stmtNew = $conn->prepare("UPDATE beds SET status = 'Occupied', patient_id = ? WHERE bed_number = ?");
        $stmtNew->bind_param("ss", $patient_id, $new_bed);
        $stmtNew->execute();
        
        // Update appointment
        $new_status = "Admitted (" . ($newRow['type'] === 'ICU' ? 'ICU Bed' : 'Ward Bed') . ")";
        $conn->query("UPDATE appointments SET bed_number = '$new_bed', status = '$new_status' WHERE patient_id = '$patient_id' AND bed_number = '$current_bed'");
        
        // Timeline
        $time_now = date('h:i A');
        $event = "Transferred from bed $current_bed to bed $new_bed. Reason: $reason";
        $app_res = $conn->query("SELECT id FROM appointments WHERE patient_id = '$patient_id' ORDER BY id DESC LIMIT 1");
        $app_id = ($app_res && $ar = $app_res->fetch_assoc()) ? $ar['id'] : null;
        if ($app_id) {
            $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event);
            $stmtTL->execute();
        }
        
        jsonResponse(['status' => 'success', 'message' => "Patient successfully transferred to bed $new_bed."]);
    }
    else if ($action === 'edit_bed') {
        $bed_number = trim($data['bed_number'] ?? '');
        $type = trim($data['type'] ?? 'OPD');
        $wing = trim($data['wing'] ?? '');
        
        $stmt = $conn->prepare("UPDATE beds SET type = ?, wing = ? WHERE bed_number = ?");
        $stmt->bind_param("sss", $type, $wing, $bed_number);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => "Bed $bed_number details updated."]);
        } else {
            jsonResponse(['status' => 'error', 'message' => "Failed to update bed: " . $conn->error]);
        }
    }
}
?>
