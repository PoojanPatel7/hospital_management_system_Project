<?php
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_consultation') {
        $appointment_id = (int)($_GET['appointment_id'] ?? 0);
        if (!$appointment_id) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment ID required']);
        }

        $stmt = $conn->prepare("SELECT id, patient_id, status, stage, doctor_notes, bed_number FROM appointments WHERE id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $appt = $stmt->get_result()->fetch_assoc();

        if (!$appt) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment not found']);
        }

        // Get diagnoses
        $dRes = $conn->query("SELECT description FROM diagnoses WHERE appointment_id = $appointment_id ORDER BY id ASC");
        $diagnoses = [];
        while ($d = $dRes->fetch_assoc()) {
            $diagnoses[] = $d['description'];
        }

        // Get medicines
        $mRes = $conn->query("SELECT medicine_name as name, dosage as dose, frequency as freq, duration, instructions as note FROM prescriptions WHERE appointment_id = $appointment_id ORDER BY id ASC");
        $medicines = [];
        while ($m = $mRes->fetch_assoc()) {
            $medicines[] = $m;
        }

        // Get attached files
        $fRes = $conn->query("SELECT id, title, file_path, file_name, mime_type, file_size, DATE_FORMAT(record_date, '%b %d, %Y') as file_date FROM patient_files WHERE appointment_id = $appointment_id ORDER BY id ASC");
        $files = [];
        while ($f = $fRes->fetch_assoc()) {
            if (empty($f['file_path']) || strpos($f['file_path'], 'api/file.php') === false) {
                $f['file_path'] = 'api/file.php?id=' . $f['id'] . '&file=' . urlencode($f['file_name'] ?: 'file');
            }
            $files[] = $f;
        }

        // Extract ordered diagnostic tests from timeline if any
        $tRes = $conn->query("SELECT event_description FROM timeline_events WHERE appointment_id = $appointment_id AND event_description LIKE '%diagnostic tests%' ORDER BY id DESC LIMIT 1");
        $tests_ordered = '';
        if ($tRow = $tRes->fetch_assoc()) {
            if (preg_match('/diagnostic tests \((.*?)\)\.\s*Waiting/i', $tRow['event_description'], $matches)) {
                $tests_ordered = $matches[1];
            } else if (preg_match('/diagnostic tests \((.*)\)/i', $tRow['event_description'], $matches)) {
                $tests_ordered = $matches[1];
            }
        }

        jsonResponse([
            'status' => 'success',
            'data' => [
                'appointment_id' => $appt['id'],
                'patient_id' => $appt['patient_id'],
                'status' => $appt['status'],
                'stage' => (int)$appt['stage'],
                'doctor_notes' => $appt['doctor_notes'] ?? '',
                'bed_number' => $appt['bed_number'],
                'diagnoses' => $diagnoses,
                'medicines' => $medicines,
                'files' => $files,
                'tests_ordered' => $tests_ordered
            ]
        ]);
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'save_with_files') {
        try {
            $conn->begin_transaction();

            $app_id = (int)$_POST['appointment_id'];
            $patient_id = $_POST['patient_id'];
            $diagnoses = json_decode($_POST['diagnoses'], true) ?? [];
            $medicines = json_decode($_POST['medicines'], true) ?? [];
            $disposition = $_POST['disposition'];
            $notes = $_POST['doctor_notes'];
            $bed_number = $_POST['bed_number'] ?? null;

            $new_status = 'Discharged (Normal Medicine)';
            $new_stage = 5;
            $event_desc = "Consultation finalized. Discharged with medicine.";

            if ($disposition === 'Waiting for Reports') {
                $new_status = 'Waiting for Reports';
                $new_stage = 3;
                $tests_ordered = $_POST['tests_ordered'] ?? '';
                $event_desc = "Consultation on hold. Patient sent for diagnostic tests" . ($tests_ordered ? " ($tests_ordered)" : "") . ". Waiting for reports.";
                $bed_number = null;
            } else if ($disposition !== 'Normal Medicine' && $bed_number) {
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

            // Clear previous entries for this appointment to avoid duplication when updating
            $conn->query("DELETE FROM diagnoses WHERE appointment_id = $app_id");
            $conn->query("DELETE FROM prescriptions WHERE appointment_id = $app_id");

            $stmtDiag = $conn->prepare("INSERT INTO diagnoses (appointment_id, description) VALUES (?, ?)");
            foreach ($diagnoses as $diag) {
                $stmtDiag->bind_param("is", $app_id, $diag);
                $stmtDiag->execute();
            }

            $stmtMed = $conn->prepare("INSERT INTO prescriptions (appointment_id, medicine_name, dosage, frequency, duration, instructions) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($medicines as $med) {
                $duration = $med['duration'] ?? '';
                $stmtMed->bind_param("isssss", $app_id, $med['name'], $med['dose'], $med['freq'], $duration, $med['note']);
                $stmtMed->execute();
            }

            $time_now = date('h:i A');
            $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event_desc);
            $stmtTL->execute();

            // Handle File Uploads (Binary BLOB Storage + Fallback)
            if (isset($_FILES['files']) && count($_FILES['files']['name']) > 0) {
                $titles = $_POST['file_titles'] ?? [];
                $dates = $_POST['file_dates'] ?? [];
                
                $stmtFile = $conn->prepare("INSERT INTO patient_files (patient_id, appointment_id, title, file_name, mime_type, file_size, file_data, file_path, record_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
                    if ($_FILES['files']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['files']['tmp_name'][$i];
                        $origFileName = basename($_FILES['files']['name'][$i]);
                        $ext = pathinfo($origFileName, PATHINFO_EXTENSION);
                        
                        $binaryContent = file_get_contents($tmpName);
                        $fileSize = strlen($binaryContent);
                        $mimeType = mime_content_type($tmpName) ?: ($_FILES['files']['type'][$i] ?? 'application/octet-stream');
                        $title = $titles[$i] ?? 'Untitled';
                        $date = $dates[$i] ?? date('Y-m-d H:i:s');
                        
                        // Local cache copy in uploads directory
                        $newFileName = uniqid($patient_id . "_") . "." . $ext;
                        $dest = "../uploads/" . $newFileName;
                        @move_uploaded_file($tmpName, $dest);
                        
                        // Insert binary BLOB
                        $initialPath = "api/file.php?file=" . urlencode($origFileName);
                        $stmtFile->bind_param("sisssisss", $patient_id, $app_id, $title, $origFileName, $mimeType, $fileSize, $binaryContent, $initialPath, $date);
                        $stmtFile->execute();
                        $newFileId = $stmtFile->insert_id;
                        
                        // Set exact file_path with ID
                        $finalPath = "api/file.php?id=" . $newFileId . "&file=" . urlencode($origFileName);
                        $conn->query("UPDATE patient_files SET file_path = '" . $conn->real_escape_string($finalPath) . "' WHERE id = " . (int)$newFileId);
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
            // Clear previous entries for this appointment to avoid duplication when updating
            $conn->query("DELETE FROM diagnoses WHERE appointment_id = " . (int)$appointment_id);
            $conn->query("DELETE FROM prescriptions WHERE appointment_id = " . (int)$appointment_id);

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
            
            if ($disposition === 'Waiting for Reports') {
                $status = 'Waiting for Reports';
                $stage = 3;
                $tests_ordered = $data['tests_ordered'] ?? '';
                $event_desc = "Consultation on hold. Patient sent for diagnostic tests" . ($tests_ordered ? " ($tests_ordered)" : "") . ". Waiting for reports.";
                $bed_number = null;
            } else if ($disposition !== 'Normal Medicine') {
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
