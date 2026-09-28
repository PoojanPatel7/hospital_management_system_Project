<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_GET['action'] ?? '';
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    if ($action === 'get_queue') {
        // Get line running state
        $res = $conn->query("SELECT line_running FROM system_state WHERE id = 1");
        $line_running = $res->fetch_assoc()['line_running'] ?? 1;
        
        $hospital_id = $_SESSION['hospital_id'] ?? 0;
        // Get active appointments (stages 1 to 4)
        $query = "
            SELECT a.*, 
                   p.name, p.surname, p.father_name, p.blood_group, p.phone, p.gender, p.age, p.demographics, p.emergency_contact_name, p.emergency_contact_phone,
                   d.name as doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            LEFT JOIN doctors d ON a.doctor_id = d.id
            WHERE a.stage BETWEEN 1 AND 4 AND (a.hospital_id = ? OR a.hospital_id IS NULL)
            ORDER BY a.created_at ASC
        ";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $patients = [];
        $token_no = 1;
        while ($row = $res->fetch_assoc()) {
            $patients[] = [
                'token_no' => $token_no++,
                'id' => $row['patient_id'],
                'appointment_id' => $row['id'],
                'appointment_code' => sprintf("APP-%04d", (int)$row['id']),
                'name' => $row['name'],
                'surname' => $row['surname'],
                'father' => $row['father_name'],
                'blood_group' => $row['blood_group'],
                'phone' => $row['phone'] ?? '',
                'gender' => $row['gender'] ?? '',
                'age' => $row['age'] ?? '',
                'demographics' => $row['demographics'] ?? '',
                'emergency_contact_name' => $row['emergency_contact_name'] ?? '',
                'emergency_contact_phone' => $row['emergency_contact_phone'] ?? '',
                'type' => $row['type'],
                'dept' => $row['dept'] ?? 'General',
                'doctor' => $row['doctor_name'] ?? 'Unassigned',
                'doctor_id' => $row['doctor_id'] ?? '',
                'date' => $row['date'],
                'slot' => $row['slot'],
                'symptoms' => $row['symptoms'],
                'status' => $row['status'],
                'stage' => (int)$row['stage']
            ];
        }
        
        jsonResponse([
            'status' => 'success', 
            'lineRunning' => (bool)$line_running, 
            'queue' => $patients
        ]);
    }
    else if ($action === 'get_advance_appointments') {
        $hospital_id = $_SESSION['hospital_id'] ?? 0;
        $date = $_GET['date'] ?? '';
        
        $query = "
            SELECT a.*, 
                   p.name, p.surname, p.father_name, p.phone, p.blood_group, p.gender, p.age, p.demographics,
                   d.name as doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            LEFT JOIN doctors d ON a.doctor_id = d.id
            WHERE (a.hospital_id = ? OR a.hospital_id IS NULL)
        ";
        if ($date) {
            $query .= " AND a.date = ? ORDER BY a.slot ASC, a.created_at ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("is", $hospital_id, $date);
        } else {
            $query .= " AND a.date >= CURDATE() ORDER BY a.date ASC, a.slot ASC";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $hospital_id);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        
        $appointments = [];
        $idx = 1;
        while ($row = $res->fetch_assoc()) {
            $row['token_no'] = $idx++;
            $row['appointment_code'] = sprintf("APP-%04d", (int)$row['id']);
            $appointments[] = $row;
        }
        jsonResponse(['status' => 'success', 'appointments' => $appointments]);
    }
}
else if ($method === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'update_status') {
        $appointment_id = (int)($data['appointment_id'] ?? 0);
        $patient_id = $data['patient_id'] ?? '';
        $new_status = $data['status'] ?? '';
        $new_stage = (int)($data['stage'] ?? 1);
        $event_desc = $data['event_desc'] ?? 'Status updated';
        
        if (empty($patient_id) && $appointment_id) {
            $pRes = $conn->query("SELECT patient_id FROM appointments WHERE id = $appointment_id");
            if ($pRow = $pRes->fetch_assoc()) {
                $patient_id = $pRow['patient_id'];
            }
        }
        
        $stmt = $conn->prepare("UPDATE appointments SET status = ?, stage = ? WHERE id = ?");
        $stmt->bind_param("sii", $new_status, $new_stage, $appointment_id);
        
        if ($stmt->execute()) {
            $time_now = date('h:i A');
            $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $tStmt->bind_param("isss", $appointment_id, $patient_id, $time_now, $event_desc);
            $tStmt->execute();
            
            jsonResponse(['status' => 'success', 'message' => 'Status updated']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Update failed: ' . $conn->error]);
        }
    }
    else if ($action === 'check_in_advance') {
        $appointment_id = (int)($data['appointment_id'] ?? 0);
        if (!$appointment_id) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment ID required']);
        }
        
        $pRes = $conn->query("SELECT patient_id FROM appointments WHERE id = $appointment_id");
        $pid = '';
        if ($pRow = $pRes->fetch_assoc()) {
            $pid = $pRow['patient_id'];
        }
        
        $stmt = $conn->prepare("UPDATE appointments SET status = 'Available at Hospital', stage = 2 WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        if ($stmt->execute()) {
            $time_now = date('h:i A');
            $desc = "Pre-booked patient arrived at hospital and checked in.";
            if ($pid) {
                $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
                $tStmt->bind_param("isss", $appointment_id, $pid, $time_now, $desc);
                $tStmt->execute();
            }
            jsonResponse(['status' => 'success', 'message' => 'Patient checked in successfully!']);
        } else {
            jsonResponse(['status' => 'error', 'message' => $conn->error]);
        }
    }
    else if ($action === 'toggle_line') {
        $state = (int)$data['running'];
        $conn->query("UPDATE system_state SET line_running = $state WHERE id = 1");
        jsonResponse(['status' => 'success']);
    }
    else if ($action === 'register_walkin') {
        // Register patient first
        $name = $data['name'];
        $surname = $data['surname'];
        $father = $data['father'];
        $type = $data['type'];
        $doctor_id = $data['doctor_id'];
        $symptoms = $data['symptoms'];
        $date = date('Y-m-d');
        
        $res = $conn->query("SELECT COUNT(*) as cnt FROM patients");
        $count = $res->fetch_assoc()['cnt'] + 1;
        $uniqueId = 'CP-' . date('Y') . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        
        $conn->query("INSERT INTO patients (id, name, surname, father_name, phone, demographics) VALUES ('$uniqueId', '$name', '$surname', '$father', '+1 555-WALK', 'Walk-In')");
        
        // Book appointment at Stage 2
        $status = 'Available at Hospital';
        $stage = 2;
        
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, allergies, status, stage) VALUES (?, ?, ?, ?, 'Immediate Walk-In', ?, 'None recorded', ?, ?)");
        $stmt->bind_param("ssssssi", $uniqueId, $doctor_id, $type, $date, $symptoms, $status, $stage);
        $stmt->execute();
        
        $app_id = $stmt->insert_id;
        
        $time_now = date('h:i A');
        $event = "Walk-in registered by staff. Marked Available at Hospital ($uniqueId).";
        $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
        $tStmt->bind_param("isss", $app_id, $uniqueId, $time_now, $event);
        $tStmt->execute();
        
        jsonResponse(['status' => 'success', 'message' => 'Walk-In registered successfully']);
    }
    else if ($action === 'book_existing') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) { echo json_encode(['status' => 'error', 'message' => 'Invalid input']); exit; }
        
        try {
            $stage = ($input['type'] === 'Emergency Case') ? 2 : 1;
            $status = ($stage === 2) ? 'Available at Hospital' : 'Checked-In';
    
            $hospital_id = $_SESSION['hospital_id'] ?? 0;
            $slotPlaceholder = $input['slot'] ?? 'Walk-in';
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, status, stage, hospital_id) VALUES (?, ?, ?, CURDATE(), ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssii", $input['patient_id'], $input['doctor_id'], $input['type'], $slotPlaceholder, $input['symptoms'], $status, $stage, $hospital_id);
            $stmt->execute();
            
            $appointment_id = $conn->insert_id;
    
            $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $time_now = date('h:i A');
            $desc = "Patient arrived and was added to the queue.";
            $stmtTL->bind_param("isss", $appointment_id, $input['patient_id'], $time_now, $desc);
            $stmtTL->execute();
    
            echo json_encode(['status' => 'success']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    else if ($action === 'book_advance') {
        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) { echo json_encode(['status' => 'error', 'message' => 'Invalid input']); exit; }
        
        try {
            $hospital_id = $_SESSION['hospital_id'] ?? 0;
            $patient_id = $input['patient_id'] ?? '';
            $doctor_id = $input['doctor_id'] ?? '';
            $date = $input['date'] ?? date('Y-m-d');
            $slot = $input['slot'] ?? '09:00 AM';
            $type = $input['type'] ?? 'General Consultation';
            $symptoms = $input['symptoms'] ?? 'Pre-booked appointment';
            
            if (!$patient_id || !$doctor_id) {
                echo json_encode(['status' => 'error', 'message' => 'Patient and Doctor are required']);
                exit;
            }
            
            $isToday = ($date === date('Y-m-d'));
            $stage = 1;
            $status = $isToday ? ($type === 'Emergency Case' ? 'Available at Hospital' : 'Checked-In') : 'Pre-Booked';
            if ($isToday && $type === 'Emergency Case') $stage = 2;
            
            $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, status, stage, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssii", $patient_id, $doctor_id, $type, $date, $slot, $symptoms, $status, $stage, $hospital_id);
            $stmt->execute();
            
            $appointment_id = $conn->insert_id;
            
            $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $time_now = date('h:i A');
            $desc = "Advance appointment booked for date $date at $slot ($type).";
            $stmtTL->bind_param("isss", $appointment_id, $patient_id, $time_now, $desc);
            $stmtTL->execute();
            
            echo json_encode(['status' => 'success', 'appointment_id' => $appointment_id, 'message' => 'Advance appointment scheduled successfully.']);
        } catch (Exception $e) {
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    else if ($action === 'edit_appointment') {
        $data = json_decode(file_get_contents('php://input'), true);
        $appointment_id = (int)($data['appointment_id'] ?? 0);
        if (!$appointment_id) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment ID is required']);
        }

        $doctor_id = trim($data['doctor_id'] ?? '');
        $type = trim($data['type'] ?? 'General Consultation');
        $symptoms = trim($data['symptoms'] ?? '');
        $date = trim($data['date'] ?? '');
        $slot = trim($data['slot'] ?? '');

        $qAppt = $conn->query("SELECT * FROM appointments WHERE id = $appointment_id");
        $curr = $qAppt ? $qAppt->fetch_assoc() : null;
        if (!$curr) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment not found']);
        }
        $patient_id = $curr['patient_id'];

        if (empty($date)) $date = $curr['date'];
        if (empty($slot)) $slot = $curr['slot'];
        if (empty($doctor_id)) $doctor_id = $curr['doctor_id'];

        $stmt = $conn->prepare("UPDATE appointments SET doctor_id = ?, type = ?, symptoms = ?, date = ?, slot = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $doctor_id, $type, $symptoms, $date, $slot, $appointment_id);
        if ($stmt->execute()) {
            $time_now = date('h:i A');
            $event_desc = "Appointment updated: Type: $type, Slot: $slot.";
            $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $tStmt->bind_param("isss", $appointment_id, $patient_id, $time_now, $event_desc);
            $tStmt->execute();

            jsonResponse(['status' => 'success', 'message' => 'Appointment updated successfully.']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to update appointment: ' . $conn->error]);
        }
    }
    else if ($action === 'delete_appointment') {
        $data = json_decode(file_get_contents('php://input'), true);
        $appointment_id = (int)($data['appointment_id'] ?? 0);
        if (!$appointment_id) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment ID is required']);
        }

        $conn->query("DELETE FROM timeline_events WHERE appointment_id = $appointment_id");
        $conn->query("DELETE FROM diagnoses WHERE appointment_id = $appointment_id");
        $conn->query("DELETE FROM prescriptions WHERE appointment_id = $appointment_id");
        $conn->query("DELETE FROM patient_files WHERE appointment_id = $appointment_id");

        $stmt = $conn->prepare("DELETE FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => 'Appointment deleted successfully.']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to delete appointment: ' . $conn->error]);
        }
    }
}
?>
