<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';

if ($action === 'get_pdf_data') {
    $patient_id = $_GET['patient_id'] ?? '';
    if (!$patient_id) {
        jsonResponse(['status' => 'error', 'message' => 'Patient ID is required']);
    }

    $hospital_id = $_SESSION['hospital_id'] ?? 1;
    $appointment_id = $_GET['appointment_id'] ?? '';
    $from_date = $_GET['from_date'] ?? '';
    $to_date = $_GET['to_date'] ?? '';

    // Hospital data
    $hospitalName = 'Bhooma Medicare Hospital & I.C.U';
    $hospitalAddress = 'Near Central Ring Road, Multi-Speciality Care';
    $hospitalPhone = '+91 98765 43210';
    
    $hStmt = $conn->prepare("SELECT id, name FROM hospitals WHERE id = ?");
    if ($hStmt) {
        $hStmt->bind_param("i", $hospital_id);
        $hStmt->execute();
        $hRow = $hStmt->get_result()->fetch_assoc();
        if ($hRow) {
            $hospitalName = $hRow['name'] ?: $hospitalName;
        }
    }
    $hospital = [
        'id' => $hospital_id,
        'name' => $hospitalName,
        'address' => $hospitalAddress,
        'phone' => $hospitalPhone
    ];

    // Patient data
    $stmt = $conn->prepare("SELECT id, name, surname, father_name, gender, age, blood_group, phone FROM patients WHERE id = ?");
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $patient = $stmt->get_result()->fetch_assoc();
    
    if (!$patient) {
        jsonResponse(['status' => 'error', 'message' => 'Patient not found']);
    }

    $fullName = trim(($patient['name'] ?? '') . ' ' . ($patient['surname'] ?? '')) ?: 'Patient';
    $patient['full_name'] = $fullName;

    // Filter conditions for appointments and files
    $cleanPatientId = $conn->real_escape_string($patient_id);
    $appFilter = "a.patient_id = '$cleanPatientId'";
    $fileFilter = "f.patient_id = '$cleanPatientId'";
    
    if ($appointment_id) {
        $appFilter .= " AND a.id = " . (int)$appointment_id;
        $fileFilter .= " AND f.appointment_id = " . (int)$appointment_id;
    }
    if ($from_date) {
        $cleanFrom = $conn->real_escape_string($from_date);
        $appFilter .= " AND a.date >= '$cleanFrom'";
        $fileFilter .= " AND DATE(f.record_date) >= '$cleanFrom'";
    }
    if ($to_date) {
        $cleanTo = $conn->real_escape_string($to_date);
        $appFilter .= " AND a.date <= '$cleanTo'";
        $fileFilter .= " AND DATE(f.record_date) <= '$cleanTo'";
    }

    // Appointments & Attending Doctors
    $appSql = "SELECT a.id, a.date as appointment_date, a.slot, a.type, 
                      d.id as doc_id, d.name as doctor_name, dep.name as department_name, d.degree 
               FROM appointments a 
               LEFT JOIN doctors d ON a.doctor_id = d.id 
               LEFT JOIN departments dep ON d.department_id = dep.id 
               WHERE $appFilter 
               ORDER BY a.date DESC";
    $appRes = $conn->query($appSql);
    $appointments = [];
    $doctorsList = [];
    if ($appRes) {
        while ($row = $appRes->fetch_assoc()) {
            $appointments[] = $row;
            if (!empty($row['doctor_name'])) {
                $doctorsList[$row['doctor_name']] = $row['department_name'] ?: ($row['degree'] ?: 'Consultant');
            }
        }
    }

    // Fallback: If no appointments or doctors found, fetch general hospital doctors
    if (empty($doctorsList)) {
        $docRes = $conn->query("SELECT d.name, dep.name as department_name, d.degree FROM doctors d LEFT JOIN departments dep ON d.department_id = dep.id LIMIT 3");
        if ($docRes) {
            while ($dRow = $docRes->fetch_assoc()) {
                if (!empty($dRow['name'])) {
                    $doctorsList[$dRow['name']] = $dRow['department_name'] ?: ($dRow['degree'] ?: 'Consultant');
                }
            }
        }
    }

    $doctors = [];
    foreach ($doctorsList as $dName => $spec) {
        $doctors[] = [
            'name' => $dName,
            'specialty' => $spec
        ];
    }

    // Files query with highlighted date format: 6-May-2020
    $filesSql = "SELECT f.id, f.title, f.category, f.highlight, f.description, f.record_date, f.file_name, f.mime_type, f.uploaded_by_name, f.appointment_id,
                        DATE_FORMAT(COALESCE(f.record_date, f.created_at), '%e-%b-%Y') as formatted_date,
                        d.name as doctor_name, a.date as appointment_date 
                 FROM patient_files f 
                 LEFT JOIN appointments a ON f.appointment_id = a.id 
                 LEFT JOIN doctors d ON a.doctor_id = d.id 
                 WHERE $fileFilter 
                 ORDER BY f.record_date DESC, f.id DESC";
    $filesRes = $conn->query($filesSql);
    $files = [];
    if ($filesRes) {
        while ($row = $filesRes->fetch_assoc()) {
            $isPdf = ($row['mime_type'] === 'application/pdf') || (strtolower(pathinfo($row['file_name'] ?? '', PATHINFO_EXTENSION)) === 'pdf');
            $files[] = [
                'id' => $row['id'],
                'title' => $row['title'] ?: 'Doctor Letterhead Pad',
                'category' => $row['category'] ?: 'Other',
                'highlight' => $row['highlight'] ?: '',
                'description' => $row['description'] ?: '',
                'record_date' => $row['formatted_date'] ?: $row['record_date'],
                'uploaded_by_name' => $row['uploaded_by_name'] ?: 'Hospital Staff',
                'file_url' => "api/file.php?id=" . $row['id'],
                'thumb_url' => "api/file.php?id=" . $row['id'] . "&thumb=1",
                'is_pdf' => $isPdf,
                'appointment_date' => $row['appointment_date'],
                'doctor_name' => $row['doctor_name']
            ];
        }
    }

    jsonResponse([
        'status' => 'success',
        'hospital' => $hospital,
        'patient' => $patient,
        'doctors' => $doctors,
        'appointments' => $appointments,
        'files' => $files,
        'generated_at' => date('j-M-Y h:i A'),
        'generated_by' => $_SESSION['staff_name'] ?? ($_SESSION['username'] ?? 'Hospital Administrator')
    ]);

} elseif ($action === 'get_charges') {
    $hospital_id = $_SESSION['hospital_id'] ?? 1;
    $stmt = $conn->prepare("SELECT amount FROM consultation_charges WHERE charge_type = 'report_download' AND is_active = 1");
    if ($stmt) {
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $charge = $row ? (float)$row['amount'] : 0.00;
    } else {
        $charge = 0.00;
    }
    
    jsonResponse([
        'status' => 'success',
        'charge' => $charge
    ]);
}

jsonResponse(['status' => 'error', 'message' => 'Invalid action']);
