<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$hospital_id = $_SESSION['hospital_id'] ?? 0;

if ($action === 'get_all') {
    try {
        if ($hospital_id) {
            $stmt = $conn->prepare("SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, emergency_contact_name, emergency_contact_phone, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date FROM patients WHERE (hospital_id = ? OR hospital_id IS NULL) ORDER BY created_at DESC");
            $stmt->bind_param("i", $hospital_id);
        } else {
            $stmt = $conn->prepare("SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, emergency_contact_name, emergency_contact_phone, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date FROM patients ORDER BY created_at DESC");
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $patients = [];
        while ($row = $res->fetch_assoc()) {
            $patients[] = $row;
        }
        echo json_encode(['status' => 'success', 'patients' => $patients]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($action === 'check_duplicate') {
    $input = json_decode(file_get_contents('php://input'), true);
    $name = $input['name'] ?? '';
    $surname = $input['surname'] ?? '';
    $father = $input['father'] ?? '';

    $stmt = $conn->prepare("SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date FROM patients WHERE LOWER(name) = LOWER(?) AND LOWER(surname) = LOWER(?) AND LOWER(father_name) = LOWER(?) AND (hospital_id = ? OR hospital_id IS NULL)");
    $stmt->bind_param("sssi", $name, $surname, $father, $hospital_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $duplicates = [];
    while ($row = $res->fetch_assoc()) {
        $duplicates[] = $row;
    }
    echo json_encode(['status' => 'success', 'duplicates' => $duplicates]);
}

if ($action === 'create') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }
    
    // Generate MRN using MAX to avoid duplicate entry errors
    $year = date('Y');
    $maxRes = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(id, '-', -1) AS UNSIGNED)) as max_num FROM patients WHERE id LIKE 'CP-$year-%'");
    $maxRow = $maxRes->fetch_assoc();
    $next_num = ($maxRow['max_num'] ?? 0) + 1;
    $patient_id = "CP-$year-" . str_pad($next_num, 3, '0', STR_PAD_LEFT);

    try {
        $age = $input['age'] ?? '';
        $gender = $input['gender'] ?? '';
        $blood = $input['blood_group'] ?? '';
        $name = $input['name'] ?? '';
        $surname = $input['surname'] ?? '';
        $father = $input['father'] ?? '';
        $phone = $input['phone'] ?? '';
        $emName = $input['emergency_contact_name'] ?? '';
        $emPhone = $input['emergency_contact_phone'] ?? '';
        $demoStr = $age . ' Y, ' . $gender . ', ' . $blood;

        $stmt = $conn->prepare("INSERT INTO patients (id, name, surname, father_name, phone, demographics, gender, blood_group, age, emergency_contact_name, emergency_contact_phone, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssi", 
            $patient_id, 
            $name, 
            $surname, 
            $father,
            $phone,
            $demoStr,
            $gender,
            $blood,
            $age,
            $emName,
            $emPhone,
            $hospital_id
        );
        $stmt->execute();
        echo json_encode(['status' => 'success', 'mrn' => $patient_id]);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($action === 'delete') {
    $id = $_GET['id'] ?? '';
    if (!$id) {
        echo json_encode(['status' => 'error', 'message' => 'Patient ID is required']);
        exit;
    }
    
    $conn->begin_transaction();
    try {
        // 1. Release any beds occupied by this patient
        $stmtBed = $conn->prepare("UPDATE beds SET status = 'Available', patient_id = NULL WHERE patient_id = ?");
        $stmtBed->bind_param("s", $id);
        $stmtBed->execute();

        // 2. Find all appointments for this patient
        $stmtAppts = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ?");
        $stmtAppts->bind_param("s", $id);
        $stmtAppts->execute();
        $resAppts = $stmtAppts->get_result();
        $apptIds = [];
        while ($row = $resAppts->fetch_assoc()) {
            $apptIds[] = (int)$row['id'];
        }

        // 3. Delete diagnoses, prescriptions, and timeline events for those appointments
        if (!empty($apptIds)) {
            $inList = implode(',', $apptIds);
            $conn->query("DELETE FROM diagnoses WHERE appointment_id IN ($inList)");
            $conn->query("DELETE FROM prescriptions WHERE appointment_id IN ($inList)");
            $conn->query("DELETE FROM timeline_events WHERE appointment_id IN ($inList)");
        }

        // 4. Delete remaining timeline events referencing patient_id
        $stmtTL = $conn->prepare("DELETE FROM timeline_events WHERE patient_id = ?");
        $stmtTL->bind_param("s", $id);
        $stmtTL->execute();

        // 5. Delete patient files
        $stmtFiles = $conn->prepare("DELETE FROM patient_files WHERE patient_id = ?");
        $stmtFiles->bind_param("s", $id);
        $stmtFiles->execute();

        // 6. Delete appointments
        $stmtDelAppt = $conn->prepare("DELETE FROM appointments WHERE patient_id = ?");
        $stmtDelAppt->bind_param("s", $id);
        $stmtDelAppt->execute();

        // 7. Delete patient
        if ($hospital_id) {
            $stmtPat = $conn->prepare("DELETE FROM patients WHERE id = ? AND (hospital_id = ? OR hospital_id IS NULL)");
            $stmtPat->bind_param("si", $id, $hospital_id);
        } else {
            $stmtPat = $conn->prepare("DELETE FROM patients WHERE id = ?");
            $stmtPat->bind_param("s", $id);
        }
        $stmtPat->execute();

        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Throwable $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($action === 'update') {
    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid input']);
        exit;
    }
    
    $id = $input['id'] ?? '';
    $name = $input['name'] ?? '';
    $surname = $input['surname'] ?? '';
    $father = $input['father'] ?? '';
    $phone = $input['phone'] ?? '';
    $age = $input['age'] ?? '';
    $gender = $input['gender'] ?? '';
    $blood_group = $input['blood_group'] ?? '';
    $emName = $input['emergency_contact_name'] ?? '';
    $emPhone = $input['emergency_contact_phone'] ?? '';
    
    // Recalculate demographics string
    $demoStr = '';
    if ($age || $gender || $blood_group) {
        $demoStr = trim(($age ? "$age Y, " : "") . ($gender ? "$gender, " : "") . $blood_group, ', ');
    }
    
    if ($hospital_id) {
        $stmt = $conn->prepare("UPDATE patients SET name = ?, surname = ?, father_name = ?, phone = ?, age = ?, gender = ?, blood_group = ?, emergency_contact_name = ?, emergency_contact_phone = ?, demographics = ? WHERE id = ? AND (hospital_id = ? OR hospital_id IS NULL)");
        $stmt->bind_param("sssssssssssi", $name, $surname, $father, $phone, $age, $gender, $blood_group, $emName, $emPhone, $demoStr, $id, $hospital_id);
    } else {
        $stmt = $conn->prepare("UPDATE patients SET name = ?, surname = ?, father_name = ?, phone = ?, age = ?, gender = ?, blood_group = ?, emergency_contact_name = ?, emergency_contact_phone = ?, demographics = ? WHERE id = ?");
        $stmt->bind_param("sssssssssss", $name, $surname, $father, $phone, $age, $gender, $blood_group, $emName, $emPhone, $demoStr, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => $conn->error]);
    }
}
?>
