<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_REQUEST['action'] ?? '';
$hospital_id = isset($_SESSION['hospital_id']) ? (int)$_SESSION['hospital_id'] : 0;

function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || ($_SERVER['SERVER_PORT'] ?? 80) == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    // Strip query string
    $uri = explode('?', $uri)[0];
    // Go up one directory if inside /api
    $basePath = preg_replace('/\/api\/?.*$/i', '', $uri);
    return rtrim($protocol . $host . $basePath, '/');
}

if (!function_exists('generateUUIDv4')) {
    function generateUUIDv4() {
        return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
}

// Helper: Normalize and parse patient demographics accurately
function normalizePatientDetails($row) {
    if (!$row) return null;
    
    $age = trim((string)($row['age'] ?? ''));
    $gender = trim((string)($row['gender'] ?? ''));
    $blood = trim((string)($row['blood_group'] ?? ''));
    $demo = trim((string)($row['demographics'] ?? ''));

    // Check if age is invalid like '0' or empty
    if (($age === '' || $age === '0') && !empty($demo)) {
        if (preg_match('/(\d+)\s*(Y|y|Years?)/i', $demo, $m)) {
            $age = $m[1];
        }
    }

    // Check if gender is missing or default 'Other' while demo has Male/Female
    if (($gender === '' || strtolower($gender) === 'other') && !empty($demo)) {
        if (preg_match('/\b(Male|Female)\b/i', $demo, $m)) {
            $gender = ucfirst(strtolower($m[1]));
        }
    }

    // Check blood group from demographics if missing
    if (empty($blood) && !empty($demo)) {
        if (preg_match('/\b(A|B|AB|O)[+-]\b/i', $demo, $m)) {
            $blood = strtoupper($m[0]);
        }
    }

    return [
        'id' => $row['id'],
        'name' => $row['name'] ?? '',
        'surname' => $row['surname'] ?? '',
        'father_name' => $row['father_name'] ?? '',
        'phone' => $row['phone'] ?? '',
        'gender' => $gender ?: 'N/A',
        'blood_group' => $blood ?: 'N/A',
        'age' => $age ?: 'N/A',
        'demographics' => $demo,
        'emergency_contact_name' => $row['emergency_contact_name'] ?? '',
        'emergency_contact_phone' => $row['emergency_contact_phone'] ?? '',
        'qr_token' => $row['qr_token'] ?? ''
    ];
}

if ($action === 'generate') {
    $patient_id = trim($_GET['patient_id'] ?? '');
    if (!$patient_id) {
        jsonResponse(['status' => 'error', 'message' => 'Patient ID required']);
    }

    $stmt = $conn->prepare("SELECT * FROM patients WHERE id = ?");
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if (!$row) {
        jsonResponse(['status' => 'error', 'message' => 'Patient not found']);
    }
    
    $token = $row['qr_token'] ?? '';
    
    // Generate new token if not yet created (Lifetime guarantee: once created, never overwritten here)
    if (empty($token)) {
        $token = generateUUIDv4();
        $upd = $conn->prepare("UPDATE patients SET qr_token = ?, qr_generated_at = NOW() WHERE id = ?");
        $upd->bind_param("ss", $token, $patient_id);
        $upd->execute();
        $row['qr_token'] = $token;
    }
    
    $patientData = normalizePatientDetails($row);
    $profileUrl = getBaseUrl() . "/patient_profile_qr.php?token=" . urlencode($token);
    // Use high error correction (H) for medical tags/wristbands
    $qr_image_url = "https://api.qrserver.com/v1/create-qr-code/?size=350x350&ecc=H&margin=10&data=" . urlencode($profileUrl);
    
    jsonResponse([
        'status' => 'success',
        'message' => 'Lifetime QR Code ready',
        'token' => $token,
        'patient' => $patientData,
        'url' => $profileUrl,
        'qr_image_url' => $qr_image_url
    ]);
}
elseif ($action === 'lookup') {
    $token = trim($_GET['token'] ?? '');
    $patient_id = trim($_GET['patient_id'] ?? $_GET['id'] ?? '');

    if (!$token && !$patient_id) {
        jsonResponse(['status' => 'error', 'message' => 'QR Token or Patient ID required']);
    }
    
    $patient = null;
    if ($token) {
        $stmt = $conn->prepare("SELECT p.*, 
            (SELECT COUNT(*) FROM appointments WHERE patient_id = p.id AND status != 'Cancelled') as total_appointments,
            (SELECT MAX(date) FROM appointments WHERE patient_id = p.id) as last_visit_date
            FROM patients p WHERE p.qr_token = ? OR p.id = ?");
        $stmt->bind_param("ss", $token, $token);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();
    }
    
    if (!$patient && $patient_id) {
        $stmt = $conn->prepare("SELECT p.*, 
            (SELECT COUNT(*) FROM appointments WHERE patient_id = p.id AND status != 'Cancelled') as total_appointments,
            (SELECT MAX(date) FROM appointments WHERE patient_id = p.id) as last_visit_date
            FROM patients p WHERE p.id = ?");
        $stmt->bind_param("s", $patient_id);
        $stmt->execute();
        $patient = $stmt->get_result()->fetch_assoc();
    }
    
    if (!$patient) {
        jsonResponse(['status' => 'error', 'message' => 'Patient record not found.']);
    }

    // Ensure lifetime QR token exists
    if (empty($patient['qr_token'])) {
        $newToken = generateUUIDv4();
        $upd = $conn->prepare("UPDATE patients SET qr_token = ?, qr_generated_at = NOW() WHERE id = ?");
        $upd->bind_param("ss", $newToken, $patient['id']);
        $upd->execute();
        $patient['qr_token'] = $newToken;
    }
    
    $normalized = normalizePatientDetails($patient);
    $normalized['total_appointments'] = (int)($patient['total_appointments'] ?? 0);
    $normalized['last_visit_date'] = $patient['last_visit_date'] ?? null;

    // Check if patient has any appointment booked for today
    $today = date('Y-m-d');
    $appStmt = $conn->prepare("SELECT a.*, d.name as doctor_name, d.degree 
        FROM appointments a 
        LEFT JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.patient_id = ? AND a.date = ? AND a.status NOT IN ('Cancelled', 'Discharged (Normal Medicine)')
        ORDER BY a.slot ASC LIMIT 1");
    $appStmt->bind_param("ss", $patient['id'], $today);
    $appStmt->execute();
    $todayAppt = $appStmt->get_result()->fetch_assoc();
    
    jsonResponse([
        'status' => 'success',
        'message' => 'Patient record found',
        'patient' => $normalized,
        'today_appointment' => $todayAppt,
        'profile_url' => "patient_profile_qr.php?token=" . urlencode($patient['qr_token'])
    ]);
}
elseif ($action === 'bulk_generate') {
    // Generate QR tokens for all patients currently missing a token
    $stmt = $conn->prepare("SELECT id FROM patients WHERE (qr_token IS NULL OR qr_token = '')");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $count = 0;
    $updateStmt = $conn->prepare("UPDATE patients SET qr_token = ?, qr_generated_at = NOW() WHERE id = ?");
    
    while ($row = $result->fetch_assoc()) {
        $token = generateUUIDv4();
        $updateStmt->bind_param("ss", $token, $row['id']);
        $updateStmt->execute();
        $count++;
    }
    
    jsonResponse(['status' => 'success', 'message' => "Generated lifetime QR tokens for $count patients.", 'count' => $count]);
}
else {
    jsonResponse(['status' => 'error', 'message' => 'Invalid action']);
}
