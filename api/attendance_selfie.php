<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../auth.php';

$hospital_id = $_SESSION['hospital_id'];
$staff_id = $_SESSION['staff_id'] ?? null;

if (!$staff_id) {
    jsonResponse(['status' => 'error', 'message' => 'Not logged in as staff']);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'get_status') {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT status, check_in_time, selfie_verified, location_verified FROM staff_attendance WHERE hospital_id = ? AND staff_id = ? AND date = ?");
    $stmt->bind_param("iis", $hospital_id, $staff_id, $today);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        jsonResponse(['status' => 'success', 'data' => $row]);
    } else {
        jsonResponse(['status' => 'success', 'data' => null]);
    }
}
elseif ($action === 'mark') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    
    $face_verified = !empty($data['face_verified']) ? 1 : 0;
    $location_verified = !empty($data['location_verified']) ? 1 : 0;
    $face_confidence = $data['face_confidence'] ?? null;
    $latitude = $data['latitude'] ?? null;
    $longitude = $data['longitude'] ?? null;

    if (!$face_verified || !$location_verified) {
        jsonResponse(['status' => 'error', 'message' => 'Verification failed.']);
    }

    // Get staff shift
    $stmt = $conn->prepare("SELECT shift FROM staff WHERE id = ? AND hospital_id = ?");
    $stmt->bind_param("ii", $staff_id, $hospital_id);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();
    
    $shift_start = '09:00:00'; // default
    if ($staff && preg_match('/\((\d{2}:\d{2})/', $staff['shift'], $m)) {
        $shift_start = $m[1] . ':00';
    }

    $now = date('H:i:s');
    $today = date('Y-m-d');
    
    $start_time_ts = strtotime("$today $shift_start");
    $now_ts = strtotime("$today $now");
    
    // Determine status
    $status = 'Present';
    if ($now_ts > $start_time_ts + 600) { // more than 10 mins late
        $status = 'Late';
    }
    if ($now_ts > $start_time_ts + 14400) { // more than 4 hours late
        $status = 'Half Day';
    }

    // Check if exists
    $stmt = $conn->prepare("SELECT id FROM staff_attendance WHERE hospital_id = ? AND staff_id = ? AND date = ?");
    $stmt->bind_param("iis", $hospital_id, $staff_id, $today);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();

    if ($existing) {
        jsonResponse(['status' => 'error', 'message' => 'Attendance already marked for today.']);
    } else {
        $stmt = $conn->prepare("INSERT INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, selfie_verified, location_verified, check_in_lat, check_in_lng, check_in_method, face_confidence, marked_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'selfie', ?, 'Self')");
        $stmt->bind_param("iisssiiddsd", $hospital_id, $staff_id, $today, $status, $now, $face_verified, $location_verified, $latitude, $longitude, $face_confidence);
        $stmt->execute();
        
        jsonResponse([
            'status' => 'success', 
            'message' => 'Attendance marked successfully',
            'data' => [
                'status' => $status,
                'check_in_time' => $now
            ]
        ]);
    }
}
elseif ($action === 'get_geofence') {
    $stmt = $conn->prepare("SELECT latitude, longitude, radius_meters FROM hospital_geofence WHERE hospital_id = ? LIMIT 1");
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        jsonResponse(['status' => 'success', 'data' => $row]);
    } else {
        // Return dummy/default if none exists
        jsonResponse(['status' => 'success', 'data' => ['latitude' => 23.0225, 'longitude' => 72.5714, 'radius_meters' => 200]]);
    }
}
elseif ($action === 'register_face') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;
    
    $target_staff = $data['staff_id'] ?? $staff_id; // admin can pass staff_id
    $descriptor = $data['face_descriptor'] ?? null;
    
    if (!$descriptor || !is_array($descriptor)) {
        jsonResponse(['status' => 'error', 'message' => 'Invalid descriptor']);
    }
    
    $descriptor_json = json_encode($descriptor);
    
    $stmt = $conn->prepare("UPDATE staff SET face_descriptor = ? WHERE id = ? AND hospital_id = ?");
    $stmt->bind_param("sii", $descriptor_json, $target_staff, $hospital_id);
    
    if ($stmt->execute()) {
        jsonResponse(['status' => 'success', 'message' => 'Face registered successfully']);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Failed to register face']);
    }
}
elseif ($action === 'get_face_descriptor') {
    $stmt = $conn->prepare("SELECT face_descriptor FROM staff WHERE id = ? AND hospital_id = ?");
    $stmt->bind_param("ii", $staff_id, $hospital_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($row = $res->fetch_assoc()) {
        $desc = json_decode($row['face_descriptor'], true);
        jsonResponse(['status' => 'success', 'data' => $desc]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Staff not found']);
    }
}
elseif ($action === 'checkout') {
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    $stmt = $conn->prepare("UPDATE staff_attendance SET check_out_time = ? WHERE hospital_id = ? AND staff_id = ? AND date = ?");
    $stmt->bind_param("siis", $now, $hospital_id, $staff_id, $today);
    $stmt->execute();
    
    jsonResponse(['status' => 'success', 'message' => 'Checked out successfully']);
}
else {
    jsonResponse(['status' => 'error', 'message' => 'Invalid action']);
}
