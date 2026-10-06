<?php
require_once __DIR__ . '/../db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_all_patients') {
        $q = isset($_GET['q']) ? strtolower($_GET['q']) : '';
        
        $query = "
            SELECT p.id as patient_id, p.name, p.surname, p.father_name, p.demographics,
                   a.id as appointment_id, a.type, a.status, a.bed_number, a.date,
                   d.name as doctor_name
            FROM appointments a
            INNER JOIN patients p ON a.patient_id = p.id
            LEFT JOIN doctors d ON a.doctor_id = d.id
        ";
        
        if ($q) {
            $query .= " WHERE (LOWER(p.name) LIKE '%$q%' OR LOWER(p.surname) LIKE '%$q%' OR LOWER(p.id) LIKE '%$q%' OR LOWER(a.status) LIKE '%$q%')";
        }
        
        $query .= " ORDER BY a.id DESC";
        
        $res = $conn->query($query);
        $patients = [];
        
        while ($row = $res->fetch_assoc()) {
            $diags = [];
            if ($row['appointment_id']) {
                $dRes = $conn->query("SELECT description FROM diagnoses WHERE appointment_id = {$row['appointment_id']} LIMIT 2");
                while ($d = $dRes->fetch_assoc()) {
                    $diags[] = $d['description'];
                }
            }
            $row['appointment_code'] = !empty($row['appointment_id']) ? sprintf("APP-%04d", (int)$row['appointment_id']) : '';
            $row['diagnoses'] = $diags;
            $patients[] = $row;
        }
        
        jsonResponse(['status' => 'success', 'patients' => $patients]);
    }
    else if ($action === 'get_dossier') {
        $patient_id = $_GET['patient_id'] ?? '';
        
        $is_admin = (!empty($_SESSION['is_admin']) || (!empty($_SESSION['hospital_id']) && empty($_SESSION['staff_id'])));
        $staff_id = (int)($_SESSION['staff_id'] ?? 0);
        $token = $_GET['token'] ?? '';

        $can_view_files = $is_admin || ($staff_id > 0 && (
            checkStaffPermission($conn, $staff_id, 'can_view_files') || 
            checkStaffPermission($conn, $staff_id, 'can_upload') || 
            checkStaffPermission($conn, $staff_id, 'can_upload_files')
        ));

        $pRes = $conn->query("SELECT * FROM patients WHERE id = '$patient_id'");
        $patient = $pRes->fetch_assoc();
        
        if (!$patient) {
            jsonResponse(['status' => 'error', 'message' => 'Patient not found']);
        }

        if (!$can_view_files && !empty($token) && !empty($patient['qr_token']) && $token === $patient['qr_token']) {
            $can_view_files = true;
        }
        
        $aRes = $conn->query("
            SELECT a.*, d.name as doctor_name, dep.name as dept_name, d.department_id as dept
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN departments dep ON d.department_id = dep.id
            WHERE a.patient_id = '$patient_id'
            ORDER BY a.id DESC
        ");
        
        $appointments = [];
        $latest_appt = null;

        while ($appt = $aRes->fetch_assoc()) {
            if (!empty($appt['dept_name'])) {
                $appt['dept'] = $appt['dept_name'];
            } else if (empty($appt['dept'])) {
                $appt['dept'] = 'General';
            }

            // Timeline for this appointment
            $tRes = $conn->query("SELECT event_time as time, event_description as event FROM timeline_events WHERE appointment_id = {$appt['id']} ORDER BY id DESC");
            $timeline = [];
            $tests_ordered = '';
            while ($t = $tRes->fetch_assoc()) {
                $timeline[] = $t;
                if (empty($tests_ordered) && stripos($t['event'], 'diagnostic tests') !== false) {
                    if (preg_match('/diagnostic tests \((.*?)\)\.\s*Waiting/i', $t['event'], $matches)) {
                        $tests_ordered = $matches[1];
                    } else if (preg_match('/diagnostic tests \((.*)\)/i', $t['event'], $matches)) {
                        $tests_ordered = $matches[1];
                    }
                }
            }
            $appt['timeline'] = $timeline;
            $appt['tests_ordered'] = $tests_ordered;
            
            // Diagnoses
            $dRes = $conn->query("SELECT description FROM diagnoses WHERE appointment_id = {$appt['id']}");
            $diagnoses = [];
            while ($d = $dRes->fetch_assoc()) {
                $diagnoses[] = $d['description'];
            }
            $appt['diagnoses'] = $diagnoses;
            
            // Medicines
            $mRes = $conn->query("SELECT medicine_name as name, dosage as dose, frequency as freq, duration, instructions as note FROM prescriptions WHERE appointment_id = {$appt['id']}");
            $medicines = [];
            while ($m = $mRes->fetch_assoc()) {
                $medicines[] = $m;
            }
            $appt['medicines'] = $medicines;

            // Files specific to this appointment (respect can_view_files permission)
            $files = [];
            if ($can_view_files) {
                $stmtF = $conn->prepare("SELECT id, title, file_path, file_name, mime_type, file_size, DATE_FORMAT(record_date, '%b %d, %Y %h:%i %p') as file_date FROM patient_files WHERE appointment_id = ? ORDER BY record_date DESC");
                $stmtF->bind_param("i", $appt['id']);
                $stmtF->execute();
                $resF = $stmtF->get_result();
                while ($f = $resF->fetch_assoc()) {
                    $f['url'] = 'api/file.php?id=' . $f['id'] . '&file=' . urlencode($f['file_name'] ?: 'file');
                    $f['thumb_url'] = 'api/file.php?id=' . $f['id'] . '&thumb=1';
                    $f['file_path'] = $f['url'];
                    $files[] = $f;
                }
            }
            $appt['files'] = $files;

            $appt['appointment_code'] = sprintf("APP-%04d", (int)$appt['id']);

            if (!$latest_appt) {
                $latest_appt = $appt;
            }

            $appointments[] = $appt;
        }

        $patient['appointments'] = $appointments;

        if ($latest_appt) {
            $patient['latest_appointment_code'] = sprintf("APP-%04d", (int)$latest_appt['id']);
            $patient['type'] = $latest_appt['type'];
            $patient['dept'] = $latest_appt['dept'];
            $patient['doctor'] = $latest_appt['doctor_name'];
            $patient['date'] = $latest_appt['date'];
            $patient['slot'] = $latest_appt['slot'];
            $patient['status'] = $latest_appt['status'];
            $patient['bed_number'] = $latest_appt['bed_number'];
            $patient['symptoms'] = $latest_appt['symptoms'] ?? '';
            $patient['doctor_notes'] = $latest_appt['doctor_notes'] ?? '';
            $patient['latest_diagnoses'] = $latest_appt['diagnoses'] ?? [];
            $patient['latest_tests_ordered'] = $latest_appt['tests_ordered'] ?? '';
        } else {
            $patient['type'] = '-';
            $patient['dept'] = '-';
            $patient['doctor'] = '-';
            $patient['date'] = '-';
            $patient['slot'] = '-';
            $patient['status'] = 'No visits yet';
            $patient['bed_number'] = null;
            $patient['symptoms'] = '';
            $patient['doctor_notes'] = '';
            $patient['latest_diagnoses'] = [];
            $patient['latest_tests_ordered'] = '';
        }

        // Fetch All Files for the patient across all appointments (respect can_view_files permission)
        $allFiles = [];
        if ($can_view_files) {
            $stmtFAll = $conn->prepare("SELECT id, title, file_path, file_name, category, highlight, thumbnail_path, mime_type, file_size, DATE_FORMAT(COALESCE(record_date, created_at), '%e-%b-%Y') as formatted_date, DATE_FORMAT(COALESCE(record_date, created_at), '%b %d, %Y %h:%i %p') as file_date, uploaded_by_name FROM patient_files WHERE patient_id = ? ORDER BY COALESCE(record_date, created_at) DESC, id DESC");
            $stmtFAll->bind_param("s", $patient_id);
            $stmtFAll->execute();
            $resFAll = $stmtFAll->get_result();
            while ($f = $resFAll->fetch_assoc()) {
                $f['url'] = 'api/file.php?id=' . $f['id'] . '&file=' . urlencode($f['file_name'] ?: 'file');
                $f['thumb_url'] = 'api/file.php?id=' . $f['id'] . '&thumb=1';
                $f['file_path'] = $f['url'];
                $allFiles[] = $f;
            }
        }
        $patient['files'] = $allFiles;
        $patient['can_view_files'] = $can_view_files;
        
        jsonResponse(['status' => 'success', 'dossier' => $patient]);
    }
}
?>
