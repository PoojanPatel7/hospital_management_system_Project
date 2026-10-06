<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$hospital_id = $_SESSION['hospital_id'] ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_patient_consultations') {
        $patient_id = $_GET['patient_id'] ?? '';
        if (!$patient_id) {
            jsonResponse(['status' => 'error', 'message' => 'Patient ID required']);
        }

        $stmt = $conn->prepare("SELECT c.*, 
                                (SELECT CONCAT(first_name, ' ', last_name) FROM staff WHERE staff_code = c.doctor_id LIMIT 1) as doctor_name 
                                FROM online_consultations c 
                                WHERE c.patient_id = ? AND c.hospital_id = ? 
                                ORDER BY c.started_at DESC");
        $stmt->bind_param("si", $patient_id, $hospital_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        $consultations = [];
        while ($row = $res->fetch_assoc()) {
            $consultations[] = $row;
        }
        jsonResponse(['status' => 'success', 'data' => $consultations]);
    }
    elseif ($action === 'get_consultation') {
        $id = (int)($_GET['id'] ?? 0);
        
        $stmt = $conn->prepare("SELECT c.*, 
                                (SELECT CONCAT(first_name, ' ', last_name) FROM staff WHERE staff_code = c.doctor_id LIMIT 1) as doctor_name 
                                FROM online_consultations c 
                                WHERE c.id = ? AND c.hospital_id = ?");
        $stmt->bind_param("ii", $id, $hospital_id);
        $stmt->execute();
        $consultation = $stmt->get_result()->fetch_assoc();
        
        if ($consultation) {
            jsonResponse(['status' => 'success', 'data' => $consultation]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Consultation not found']);
        }
    }
}
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'initiate') {
        $data = json_decode(file_get_contents('php://input'), true);
        $patient_id = $data['patient_id'] ?? ($_POST['patient_id'] ?? '');
        $doctor_id = $data['doctor_id'] ?? ($_POST['doctor_id'] ?? $_SESSION['staff_code'] ?? '');
        
        if (!$patient_id || !$doctor_id) {
            jsonResponse(['status' => 'error', 'message' => 'Patient ID and Doctor ID required']);
        }
        
        $stmt = $conn->prepare("INSERT INTO online_consultations (hospital_id, patient_id, doctor_id, status) VALUES (?, ?, ?, 'in_progress')");
        $stmt->bind_param("iss", $hospital_id, $patient_id, $doctor_id);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'consultation_id' => $stmt->insert_id]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to initiate consultation']);
        }
    }
    elseif ($action === 'save_consultation') {
        $id = (int)($_POST['id'] ?? 0);
        $patient_id = $_POST['patient_id'] ?? '';
        $diagnosis = $_POST['diagnosis'] ?? '';
        $prescription_notes = $_POST['prescription_notes'] ?? '';
        $doctor_notes = $_POST['doctor_notes'] ?? '';
        $consultation_fee = (float)($_POST['consultation_fee'] ?? 0);
        
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE online_consultations SET diagnosis = ?, prescription_notes = ?, doctor_notes = ?, consultation_fee = ? WHERE id = ? AND hospital_id = ?");
            $stmt->bind_param("sssddi", $diagnosis, $prescription_notes, $doctor_notes, $consultation_fee, $id, $hospital_id);
            $stmt->execute();

            // Handle file uploads
            if (isset($_FILES['prescription_files']) && count($_FILES['prescription_files']['name']) > 0) {
                $stmtFile = $conn->prepare("INSERT INTO patient_files (patient_id, title, category, file_name, mime_type, file_size, file_data, file_path, record_date, description, uploaded_by_name) VALUES (?, ?, 'Prescription', ?, ?, ?, ?, ?, NOW(), ?, ?)");
                
                $uploaderName = $_SESSION['staff_name'] ?? 'Doctor';
                $title = "Online Prescription - " . date('Y-m-d');
                $desc = "Prescription from Online Consultation #" . $id;

                for ($i = 0; $i < count($_FILES['prescription_files']['name']); $i++) {
                    if ($_FILES['prescription_files']['error'][$i] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['prescription_files']['tmp_name'][$i];
                        $origFileName = basename($_FILES['prescription_files']['name'][$i]);
                        $mimeType = mime_content_type($tmpName) ?: 'application/octet-stream';
                        $binaryContent = file_get_contents($tmpName);
                        $fileSize = strlen($binaryContent);
                        
                        $initialPath = "api/file.php?file=" . urlencode($origFileName);
                        $stmtFile->bind_param("ssssissss", $patient_id, $title, $origFileName, $mimeType, $fileSize, $binaryContent, $initialPath, $desc, $uploaderName);
                        $stmtFile->execute();
                        
                        $newFileId = $stmtFile->insert_id;
                        $finalPath = "api/file.php?id=" . $newFileId;
                        $conn->query("UPDATE patient_files SET file_path = '$finalPath' WHERE id = " . $newFileId);
                    }
                }
            }

            // Add timeline event
            $event_desc = "Online consultation updated. Diagnosis: " . ($diagnosis ?: 'Pending');
            $time_now = date('h:i A');
            $tStmt = $conn->prepare("INSERT INTO timeline_events (patient_id, event_time, event_description) VALUES (?, ?, ?)");
            $tStmt->bind_param("sss", $patient_id, $time_now, $event_desc);
            $tStmt->execute();

            $conn->commit();
            jsonResponse(['status' => 'success', 'message' => 'Consultation saved']);
        } catch (Exception $e) {
            $conn->rollback();
            jsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
    elseif ($action === 'complete') {
        $id = (int)($_POST['id'] ?? 0);
        $patient_id = $_POST['patient_id'] ?? '';
        
        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE online_consultations SET status = 'completed', completed_at = NOW() WHERE id = ? AND hospital_id = ?");
            $stmt->bind_param("ii", $id, $hospital_id);
            $stmt->execute();
            
            // Timeline event
            $event_desc = "Online consultation completed.";
            $time_now = date('h:i A');
            $tStmt = $conn->prepare("INSERT INTO timeline_events (patient_id, event_time, event_description) VALUES (?, ?, ?)");
            $tStmt->bind_param("sss", $patient_id, $time_now, $event_desc);
            $tStmt->execute();

            $conn->commit();
            jsonResponse(['status' => 'success', 'message' => 'Consultation completed']);
        } catch(Exception $e) {
            $conn->rollback();
            jsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}

jsonResponse(['status' => 'error', 'message' => 'Invalid action']);
