<?php
require_once '../db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'save_with_files') {
        try {
            $conn->begin_transaction();

            $app_id = $_POST['appointment_id'];
            $patient_id = $_POST['patient_id'];
            $diagnoses = json_decode($_POST['diagnoses'], true) ?? [];
            $medicines = json_decode($_POST['medicines'], true) ?? [];
            $disposition = $_POST['disposition'];
            $notes = $_POST['doctor_notes'];
            $bed_number = $_POST['bed_number'] ?? null;

            $new_status = 'Discharged (Normal Medicine)';
            $new_stage = 5;
            $event_desc = "Consultation finalized. Discharged with medicine.";

            if ($disposition !== 'Normal Medicine' && $bed_number) {
                $new_status = "Admitted ($disposition)";
                $event_desc = "Patient admitted to $disposition Bed [$bed_number].";
                
                $stmtBed = $conn->prepare("UPDATE beds SET status = 'Occupied', patient_id = ? WHERE bed_number = ?");
                $stmtBed->bind_param("ss", $patient_id, $bed_number);
                $stmtBed->execute();
            } else {
                $bed_number = null;
            }

            $stmtApp = $conn->prepare("UPDATE appointments SET status = ?, stage = ?, doctor_notes = ?, bed_number = ? WHERE id = ?");
            $stmtApp->bind_param("sissi", $new_status, $new_stage, $notes, $bed_number, $app_id);
            $stmtApp->execute();

            $stmtDiag = $conn->prepare("INSERT INTO diagnoses (appointment_id, description) VALUES (?, ?)");
            foreach ($diagnoses as $diag) {
                $stmtDiag->bind_param("is", $app_id, $diag);
                $stmtDiag->execute();
            }

            $stmtMed = $conn->prepare("INSERT INTO prescriptions (appointment_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, '', ?)");
            foreach ($medicines as $med) {
                $stmtMed->bind_param("issss", $app_id, $med['name'], $med['dose'], $med['freq'], $med['note']);
                $stmtMed->execute();
            }

            $time_now = date('h:i A');
            $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event_desc);
            $stmtTL->execute();

            // Handle File Uploads
            if (isset($_FILES['files']) && count($_FILES['files']['name']) > 0) {
                $titles = $_POST['file_titles'] ?? [];
                $dates = $_POST['file_dates'] ?? [];
                
                $stmtFile = $conn->prepare("INSERT INTO patient_files (patient_id, appointment_id, title, file_path, record_date) VALUES (?, ?, ?, ?, ?)");
                
                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['files']['tmp_name'][$i];
                        $fileName = basename($_FILES['files']['name'][$i]);
                        $ext = pathinfo($fileName, PATHINFO_EXTENSION);
                        
                        $newFileName = uniqid($patient_id . "_") . "." . $ext;
                        $dest = "../uploads/" . $newFileName;
                        
                        if (move_uploaded_file($tmpName, $dest)) {
                            $title = $titles[$i] ?? 'Untitled';
                            $date = $dates[$i] ?? date('Y-m-d H:i:s');
                            $dbPath = "uploads/" . $newFileName;
                            $stmtFile->bind_param("sisss", $patient_id, $app_id, $title, $dbPath, $date);
                            $stmtFile->execute();
                        }
                    }
                }
            }

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Consultation saved with files successfully']);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
else if ($action === 'save') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $appointment_id = $data['appointment_id'];
        $patient_id = $data['patient_id'];
        $diagnoses = $data['diagnoses']; // array of strings
        $medicines = $data['medicines']; // array of objects
        $disposition = $data['disposition']; // 'Normal Medicine', 'OPD', 'ICU'
        $doctor_notes = $data['doctor_notes'];
        $bed_number = $data['bed_number'] ?? null;
        
        $conn->begin_transaction();
        
        try {
            // Save diagnoses
            $stmtD = $conn->prepare("INSERT INTO diagnoses (appointment_id, description) VALUES (?, ?)");
            foreach ($diagnoses as $diag) {
                $stmtD->bind_param("is", $appointment_id, $diag);
                $stmtD->execute();
            }
            
            // Save medicines
            $stmtM = $conn->prepare("INSERT INTO prescriptions (appointment_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($medicines as $med) {
                $stmtM->bind_param("isssss", $appointment_id, $med['name'], $med['dose'], $med['freq'], $med['duration'], $med['note']);
                $stmtM->execute();
            }
            
            // Update appointment
            $status = 'Discharged (Normal Medicine)';
            $stage = 5;
            $event_desc = "Consultation finalized. Diagnosed with: " . implode(', ', $diagnoses) . ". Normal medicine prescribed and discharged.";
            
            if ($disposition !== 'Normal Medicine') {
                $status = "Admitted ($disposition)";
                $event_desc = "Patient admitted to $disposition Bed [$bed_number]. Diagnoses: " . implode(', ', $diagnoses) . ".";
                
                // Assign bed
                $stmtB = $conn->prepare("UPDATE beds SET status = 'Occupied', patient_id = ? WHERE bed_number = ?");
                $stmtB->bind_param("ss", $patient_id, $bed_number);
                $stmtB->execute();
            } else {
                $bed_number = null; // Ensure null if not admitted
            }
            
            $stmtA = $conn->prepare("UPDATE appointments SET status = ?, stage = ?, bed_number = ?, doctor_notes = ? WHERE id = ?");
            $stmtA->bind_param("sissi", $status, $stage, $bed_number, $doctor_notes, $appointment_id);
            $stmtA->execute();
            
            // Timeline event
            $time_now = date('h:i A');
            $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $tStmt->bind_param("isss", $appointment_id, $patient_id, $time_now, $event_desc);
            $tStmt->execute();
            
            $conn->commit();
            jsonResponse(['status' => 'success', 'message' => "Patient record updated. Disposition: $status."]);
            
        } catch (Exception $e) {
            $conn->rollback();
            jsonResponse(['status' => 'error', 'message' => 'Failed to save consultation: ' . $e->getMessage()]);
        }
    }
}
?>
