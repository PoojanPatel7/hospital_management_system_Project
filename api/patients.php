<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

header('Content-Type: application/json');
$action = $_GET['action'] ?? '';
$hospital_id = $_SESSION['hospital_id'] ?? 0;
$is_admin = $_SESSION['is_admin'] ?? false;
$staff_id = $_SESSION['staff_id'] ?? 0;

if ($action === 'get_all') {
    try {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : null;
        $limit = isset($_GET['limit']) ? max(1, (int)$_GET['limit']) : 10;
        $search = trim($_GET['search'] ?? '');

        $where = [];
        $params = [];
        $types = "";

        if ($hospital_id) {
            $where[] = "(hospital_id = ? OR hospital_id IS NULL)";
            $params[] = (int)$hospital_id;
            $types .= "i";
        }

        if ($search !== '') {
            $terms = preg_split('/\s+/', $search, -1, PREG_SPLIT_NO_EMPTY);
            foreach ($terms as $term) {
                $termLike = '%' . $term . '%';
                $phoneDigits = preg_replace('/\D/', '', $term);
                if ($phoneDigits !== '') {
                    $phoneLike = '%' . $phoneDigits . '%';
                    $where[] = "(id LIKE ? OR name LIKE ? OR surname LIKE ? OR CONCAT(name, ' ', surname) LIKE ? OR CONCAT(surname, ' ', name) LIKE ? OR CONCAT(name, ' ', father_name, ' ', surname) LIKE ? OR father_name LIKE ? OR phone LIKE ? OR demographics LIKE ? OR blood_group LIKE ? OR emergency_contact_name LIKE ? OR emergency_contact_phone LIKE ? OR REPLACE(REPLACE(phone, ' ', ''), '-', '') LIKE ?)";
                    array_push($params, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $phoneLike);
                    $types .= "sssssssssssss";
                } else {
                    $where[] = "(id LIKE ? OR name LIKE ? OR surname LIKE ? OR CONCAT(name, ' ', surname) LIKE ? OR CONCAT(surname, ' ', name) LIKE ? OR CONCAT(name, ' ', father_name, ' ', surname) LIKE ? OR father_name LIKE ? OR phone LIKE ? OR demographics LIKE ? OR blood_group LIKE ? OR emergency_contact_name LIKE ? OR emergency_contact_phone LIKE ?)";
                    array_push($params, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike, $termLike);
                    $types .= "ssssssssssss";
                }
            }
        }

        $whereSql = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";

        // Helper function for bind_param reference handling across all PHP versions
        $bindAll = function($stmt, $typeStr, $paramArr) {
            if (!empty($typeStr) && !empty($paramArr)) {
                $refs = [];
                foreach ($paramArr as $i => $val) {
                    $refs[$i] = &$paramArr[$i];
                }
                $stmt->bind_param($typeStr, ...$refs);
            }
        };

        if ($page !== null) {
            // Count total matching records for pagination
            $countSql = "SELECT COUNT(*) as total FROM patients $whereSql";
            $countStmt = $conn->prepare($countSql);
            $bindAll($countStmt, $types, $params);
            $countStmt->execute();
            $countRes = $countStmt->get_result();
            $totalCount = (int)($countRes->fetch_assoc()['total'] ?? 0);
            $totalPages = $totalCount > 0 ? (int)ceil($totalCount / $limit) : 1;

            if ($page > $totalPages && $totalPages > 0) {
                $page = $totalPages;
            }
            $offset = ($page - 1) * $limit;

            $dataSql = "SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, emergency_contact_name, emergency_contact_phone, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date 
                        FROM patients 
                        $whereSql 
                        ORDER BY created_at DESC, id DESC 
                        LIMIT ? OFFSET ?";
            $dataStmt = $conn->prepare($dataSql);
            $dataTypes = $types . "ii";
            $dataParams = array_merge($params, [(int)$limit, (int)$offset]);
            $bindAll($dataStmt, $dataTypes, $dataParams);
            $dataStmt->execute();
            $res = $dataStmt->get_result();
            $patients = [];
            while ($row = $res->fetch_assoc()) {
                $patients[] = $row;
            }

            echo json_encode([
                'status' => 'success',
                'patients' => $patients,
                'total' => $totalCount,
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $totalPages
            ]);
            exit;
        } else {
            // Non-paginated legacy request (backward compatible)
            $dataSql = "SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, emergency_contact_name, emergency_contact_phone, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date 
                        FROM patients 
                        $whereSql 
                        ORDER BY created_at DESC, id DESC";
            $stmt = $conn->prepare($dataSql);
            $bindAll($stmt, $types, $params);
            $stmt->execute();
            $res = $stmt->get_result();
            $patients = [];
            while ($row = $res->fetch_assoc()) {
                $patients[] = $row;
            }
            echo json_encode([
                'status' => 'success', 
                'patients' => $patients,
                'total' => count($patients),
                'page' => 1,
                'limit' => count($patients),
                'total_pages' => 1
            ]);
            exit;
        }
    } catch (Throwable $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

if ($action === 'search') {
    $q = trim($_GET['q'] ?? '');
    if (strlen($q) === 0) {
        echo json_encode(['status' => 'success', 'patients' => []]);
        exit;
    }
    $searchLike = '%' . $q . '%';
    $phoneDigits = preg_replace('/\D/', '', $q);
    $phoneLike = '%' . $phoneDigits . '%';

    try {
        if ($hospital_id) {
            $stmt = $conn->prepare("
                SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, qr_token, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date 
                FROM patients 
                WHERE (hospital_id = ? OR hospital_id IS NULL)
                  AND (
                    id LIKE ? 
                    OR name LIKE ? 
                    OR surname LIKE ? 
                    OR CONCAT(name, ' ', surname) LIKE ?
                    OR phone LIKE ?
                    OR (? != '' AND REPLACE(REPLACE(phone, ' ', ''), '-', '') LIKE ?)
                    OR qr_token = ?
                  )
                ORDER BY id DESC LIMIT 15
            ");
            $stmt->bind_param("issssssss", $hospital_id, $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $phoneDigits, $phoneLike, $q);
        } else {
            $stmt = $conn->prepare("
                SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, qr_token, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date 
                FROM patients 
                WHERE id LIKE ? 
                   OR name LIKE ? 
                   OR surname LIKE ? 
                   OR CONCAT(name, ' ', surname) LIKE ?
                   OR phone LIKE ?
                   OR (? != '' AND REPLACE(REPLACE(phone, ' ', ''), '-', '') LIKE ?)
                   OR qr_token = ?
                ORDER BY id DESC LIMIT 15
            ");
            $stmt->bind_param("ssssssss", $searchLike, $searchLike, $searchLike, $searchLike, $searchLike, $phoneDigits, $phoneLike, $q);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        $patients = [];
        while ($row = $res->fetch_assoc()) {
            $row['full_name'] = trim(($row['name'] ?? '') . ' ' . ($row['surname'] ?? ''));
            $patients[] = $row;
        }
        echo json_encode(['status' => 'success', 'patients' => $patients]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
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
    if (!$is_admin && !checkStaffPermission($conn, $staff_id, 'can_edit_patients')) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied: Deleting patient records is restricted to administrators.']);
        exit;
    }

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
    if (!$is_admin && !checkStaffPermission($conn, $staff_id, 'can_edit_patients')) {
        echo json_encode(['status' => 'error', 'message' => 'Permission denied: Editing patient records is restricted for your staff account.']);
        exit;
    }

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
