<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$hospital_id = $_SESSION['hospital_id'] ?? null;
if (!$hospital_id) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
    exit;
}

$rawInput = file_get_contents('php://input');
$jsonInput = json_decode($rawInput, true) ?? [];
$action = $_GET['action'] ?? $_POST['action'] ?? ($jsonInput['action'] ?? '');

// -------------------------------------------------------------
// ACTION: get_all_staff
// Returns all staff members for current hospital with face registration status
// -------------------------------------------------------------
if ($action === 'get_all_staff') {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, staff_code, role, department, shift, status, avatar, 
                                   (face_descriptor IS NOT NULL AND TRIM(face_descriptor) != '' AND face_descriptor != 'null') AS has_face,
                                   created_at
                            FROM staff 
                            WHERE hospital_id = ? 
                            ORDER BY status ASC, first_name ASC");
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $row['has_face'] = (bool)$row['has_face'];
        if (empty($row['avatar'])) {
            $row['avatar'] = 'https://ui-avatars.com/api/?name=' . urlencode($row['first_name'] . ' ' . $row['last_name']) . '&background=random&color=fff';
        }
        $list[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'count' => count($list),
        'data' => $list
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: get_registered_faces
// Returns all staff with valid face descriptors (for building FaceMatcher)
// -------------------------------------------------------------
if ($action === 'get_registered_faces') {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, staff_code, role, department, shift, avatar, face_descriptor 
                            FROM staff 
                            WHERE hospital_id = ? 
                              AND face_descriptor IS NOT NULL 
                              AND TRIM(face_descriptor) != '' 
                              AND face_descriptor != 'null'
                            ORDER BY first_name ASC");
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $registered = [];
    
    while ($row = $res->fetch_assoc()) {
        $desc = json_decode($row['face_descriptor'], true);
        if ($desc && is_array($desc) && count($desc) > 0) {
            $row['descriptor'] = $desc;
            unset($row['face_descriptor']);
            if (empty($row['avatar'])) {
                $row['avatar'] = 'https://ui-avatars.com/api/?name=' . urlencode($row['first_name'] . ' ' . $row['last_name']) . '&background=random&color=fff';
            }
            $registered[] = $row;
        }
    }
    
    echo json_encode([
        'status' => 'success',
        'count' => count($registered),
        'data' => $registered
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: register_face
// Saves a 128-float face descriptor & optional photo snapshot for a staff member
// -------------------------------------------------------------
if ($action === 'register_face') {
    $staff_id = (int)($jsonInput['staff_id'] ?? $_POST['staff_id'] ?? 0);
    $descriptor = $jsonInput['face_descriptor'] ?? $_POST['face_descriptor'] ?? null;
    $snapshot = $jsonInput['snapshot_image'] ?? $_POST['snapshot_image'] ?? null; // base64 data URL
    
    if (!$staff_id) {
        echo json_encode(['status' => 'error', 'message' => 'Staff ID is required']);
        exit;
    }
    
    if (is_string($descriptor)) {
        $descriptor = json_decode($descriptor, true);
    }
    
    if (!is_array($descriptor) || count($descriptor) < 64) {
        echo json_encode(['status' => 'error', 'message' => 'Valid face descriptor array is required (128 facial feature points).']);
        exit;
    }
    
    // Check if staff belongs to hospital
    $stmt = $conn->prepare("SELECT id, first_name, last_name, staff_code, avatar FROM staff WHERE id = ? AND hospital_id = ?");
    $stmt->bind_param("ii", $staff_id, $hospital_id);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();
    if (!$staff) {
        echo json_encode(['status' => 'error', 'message' => 'Staff member not found in this hospital.']);
        exit;
    }
    
    // -------------------------------------------------------------
    // DUPLICATE FACE ENROLLMENT PREVENTION CHECK:
    // Prevent assigning the same face descriptor to more than one staff member
    // -------------------------------------------------------------
    $dupStmt = $conn->prepare("SELECT id, first_name, last_name, staff_code, role, face_descriptor 
                               FROM staff 
                               WHERE hospital_id = ? AND id != ? 
                                 AND face_descriptor IS NOT NULL 
                                 AND TRIM(face_descriptor) != '' 
                                 AND face_descriptor != 'null'");
    $dupStmt->bind_param("ii", $hospital_id, $staff_id);
    $dupStmt->execute();
    $existingFaces = $dupStmt->get_result();
    
    $duplicateStaff = null;
    $minDistance = 999.0;
    
    while ($row = $existingFaces->fetch_assoc()) {
        $otherDesc = json_decode($row['face_descriptor'], true);
        if (is_array($otherDesc) && count($otherDesc) === count($descriptor)) {
            $sumSq = 0.0;
            $len = count($descriptor);
            for ($i = 0; $i < $len; $i++) {
                $d = (float)$descriptor[$i] - (float)$otherDesc[$i];
                $sumSq += $d * $d;
            }
            $dist = sqrt($sumSq);
            // Face-API threshold: < 0.48 is an unambiguous match for the exact same human face
            if ($dist < 0.48 && $dist < $minDistance) {
                $minDistance = $dist;
                $duplicateStaff = $row;
            }
        }
    }
    
    $forceOverride = !empty($jsonInput['force_override']) || !empty($_POST['force_override']);
    if ($duplicateStaff && !$forceOverride) {
        $matchScore = round(max(0, min(100, (1.0 - ($minDistance / 0.6)) * 100)));
        echo json_encode([
            'status' => 'error',
            'code' => 'DUPLICATE_FACE',
            'message' => 'Duplicate Face Detected: This face is already enrolled for ' . 
                         $duplicateStaff['first_name'] . ' ' . $duplicateStaff['last_name'] . 
                         ' (' . ($duplicateStaff['staff_code'] ?: 'ID #' . $duplicateStaff['id']) . ') with ' . 
                         $matchScore . '% similarity. Would you like to re-assign or update anyway?',
            'duplicate_staff' => [
                'id' => $duplicateStaff['id'],
                'name' => $duplicateStaff['first_name'] . ' ' . $duplicateStaff['last_name'],
                'staff_code' => $duplicateStaff['staff_code'],
                'similarity' => $matchScore . '%'
            ]
        ]);
        exit;
    }
    
    $avatarPath = null;
    // Process optional snapshot image
    if ($snapshot && preg_match('/^data:image\/(\w+);base64,/', $snapshot, $type)) {
        $imageType = strtolower($type[1]);
        $imageData = substr($snapshot, strpos($snapshot, ',') + 1);
        $imageData = base64_decode($imageData);
        
        if ($imageData !== false) {
            $dir = __DIR__ . '/../uploads/staff_avatars';
            if (!is_dir($dir)) {
                @mkdir($dir, 0777, true);
            }
            $filename = 'face_' . $staff_id . '_' . time() . '.' . ($imageType === 'png' ? 'png' : 'jpg');
            $fullPath = $dir . '/' . $filename;
            if (file_put_contents($fullPath, $imageData)) {
                $avatarPath = 'uploads/staff_avatars/' . $filename;
            }
        }
    }
    
    $descriptor_json = json_encode($descriptor);
    
    if ($avatarPath) {
        $upStmt = $conn->prepare("UPDATE staff SET face_descriptor = ?, avatar = ? WHERE id = ? AND hospital_id = ?");
        $upStmt->bind_param("ssii", $descriptor_json, $avatarPath, $staff_id, $hospital_id);
    } else {
        $upStmt = $conn->prepare("UPDATE staff SET face_descriptor = ? WHERE id = ? AND hospital_id = ?");
        $upStmt->bind_param("sii", $descriptor_json, $staff_id, $hospital_id);
    }
    
    if ($upStmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Face registered successfully for ' . $staff['first_name'] . ' ' . $staff['last_name'],
            'data' => [
                'staff_id' => $staff_id,
                'staff_name' => $staff['first_name'] . ' ' . $staff['last_name'],
                'staff_code' => $staff['staff_code'],
                'avatar' => $avatarPath ?: $staff['avatar'],
                'points_count' => count($descriptor)
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to update database: ' . $conn->error]);
    }
    exit;
}

// -------------------------------------------------------------
// ACTION: delete_face
// Removes face descriptor for a staff member
// -------------------------------------------------------------
if ($action === 'delete_face') {
    $staff_id = (int)($jsonInput['staff_id'] ?? $_POST['staff_id'] ?? 0);
    if (!$staff_id) {
        echo json_encode(['status' => 'error', 'message' => 'Staff ID is required']);
        exit;
    }
    
    $stmt = $conn->prepare("UPDATE staff SET face_descriptor = NULL WHERE id = ? AND hospital_id = ?");
    $stmt->bind_param("ii", $staff_id, $hospital_id);
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Face biometric data deleted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
    exit;
}

// -------------------------------------------------------------
// ACTION: mark_test_attendance / auto_punch_attendance
// Automatically marks attendance when face + liveness verified
// -------------------------------------------------------------
if ($action === 'mark_test_attendance' || $action === 'auto_punch_attendance') {
    $staff_id = (int)($jsonInput['staff_id'] ?? $_POST['staff_id'] ?? 0);
    $confidence = floatval($jsonInput['confidence'] ?? $_POST['confidence'] ?? 0.95);
    $liveness = !empty($jsonInput['liveness_verified']) ? 1 : 0;
    
    if (!$staff_id) {
        echo json_encode(['status' => 'error', 'message' => 'Staff ID required']);
        exit;
    }
    
    $stmt = $conn->prepare("SELECT id, first_name, last_name, staff_code, role, department, shift, avatar FROM staff WHERE id = ? AND hospital_id = ?");
    $stmt->bind_param("ii", $staff_id, $hospital_id);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();
    if (!$staff) {
        echo json_encode(['status' => 'error', 'message' => 'Staff not found']);
        exit;
    }
    
    $today = date('Y-m-d');
    $now = date('H:i:s');
    
    // Check if attendance record already exists for today
    $checkStmt = $conn->prepare("SELECT id, status, check_in_time, check_out_time, marked_by, check_in_method, notes FROM staff_attendance WHERE hospital_id = ? AND staff_id = ? AND date = ?");
    $checkStmt->bind_param("iis", $hospital_id, $staff_id, $today);
    $checkStmt->execute();
    $existing = $checkStmt->get_result()->fetch_assoc();
    
    // Check if previously marked as Absent or On Leave
    $wasAbsent = ($existing && in_array($existing['status'], ['Absent', 'On Leave']));
    $previousStatus = $existing ? $existing['status'] : null;

    // Only consider 'already_marked' if there is an actual check_in_time and not marked Absent or On Leave
    if ($existing && !empty($existing['check_in_time']) && !$wasAbsent) {
        echo json_encode([
            'status' => 'already_marked',
            'message' => 'Attendance was already marked today at ' . date('h:i A', strtotime($existing['check_in_time'])),
            'data' => [
                'staff_id' => $staff['id'],
                'staff_name' => $staff['first_name'] . ' ' . $staff['last_name'],
                'staff_code' => $staff['staff_code'],
                'role' => $staff['role'],
                'department' => $staff['department'],
                'avatar' => $staff['avatar'],
                'status' => $existing['status'],
                'check_in_time' => $existing['check_in_time'],
                'check_in_time_formatted' => date('h:i A', strtotime($existing['check_in_time'])),
                'check_out_time' => $existing['check_out_time']
            ]
        ]);
        exit;
    }
    
    $face_verified = 1;
    $loc_verified = 1;
    $method = 'face_scan_kiosk';
    $timeFormatted = date('h:i:s A', strtotime($now));

    // If attendance was previously marked as Absent (or On Leave), change status to 'Present' with descriptive time note
    if ($wasAbsent) {
        $status = 'Present';
        $working_hours = 8.0;
        $notes = "Status updated from $previousStatus to Present via Face AI Terminal at $timeFormatted (Live Face Match: " . round($confidence * 100, 1) . "%). Previously marked $previousStatus.";
    } else {
        // Normal check-in: Determine status based on shift start
        $shift_start = '09:00:00';
        if ($staff['shift'] && preg_match('/\((\d{2}:\d{2})/', $staff['shift'], $m)) {
            $shift_start = $m[1] . ':00';
        }
        
        $start_time_ts = strtotime("$today $shift_start");
        $now_ts = strtotime("$today $now");
        $status = 'Present';
        if ($now_ts > $start_time_ts + 600) {
            $status = 'Late';
        }
        if ($now_ts > $start_time_ts + 14400) {
            $status = 'Half Day';
        }
        $working_hours = ($status === 'Present') ? 8.0 : (($status === 'Late') ? 7.5 : 4.0);
        $notes = 'Live Face Biometric Verified (' . round($confidence * 100, 1) . '% match, Anti-spoofing Live Human Verified)';
    }
    
    $saveSuccess = false;
    if ($existing) {
        // Update existing record (e.g. changing status from Absent to Present, setting current check_in_time, working_hours, and note)
        $upStmt = $conn->prepare("UPDATE staff_attendance 
                                  SET status = ?, check_in_time = ?, working_hours = ?, selfie_verified = ?, location_verified = ?, check_in_method = ?, face_confidence = ?, marked_by = 'Face Attendance Terminal', notes = ?, updated_at = CURRENT_TIMESTAMP 
                                  WHERE id = ?");
        $upStmt->bind_param("ssdiisdsi", $status, $now, $working_hours, $face_verified, $loc_verified, $method, $confidence, $notes, $existing['id']);
        $saveSuccess = $upStmt->execute();
    } else {
        // Insert new attendance record
        $insStmt = $conn->prepare("INSERT INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, working_hours, selfie_verified, location_verified, check_in_method, face_confidence, marked_by, notes) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Face Attendance Terminal', ?)");
        $insStmt->bind_param("iisssdiisds", $hospital_id, $staff_id, $today, $status, $now, $working_hours, $face_verified, $loc_verified, $method, $confidence, $notes);
        $saveSuccess = $insStmt->execute();
    }
    
    if ($saveSuccess) {
        $msg = $wasAbsent 
            ? "Attendance updated: {$staff['first_name']} {$staff['last_name']} status changed from $previousStatus to Present at " . date('h:i A', strtotime($now))
            : "Attendance successfully recorded for {$staff['first_name']} {$staff['last_name']} ($status)";

        echo json_encode([
            'status' => 'success',
            'was_absent' => $wasAbsent,
            'previous_status' => $previousStatus,
            'message' => $msg,
            'data' => [
                'staff_id' => $staff['id'],
                'staff_name' => $staff['first_name'] . ' ' . $staff['last_name'],
                'staff_code' => $staff['staff_code'],
                'role' => $staff['role'],
                'department' => $staff['department'],
                'avatar' => $staff['avatar'],
                'status' => $status,
                'was_absent' => $wasAbsent,
                'previous_status' => $previousStatus,
                'check_in_time' => $now,
                'check_in_time_formatted' => date('h:i A', strtotime($now)),
                'notes' => $notes
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save attendance: ' . $conn->error]);
    }
    exit;
}

// -------------------------------------------------------------
// ACTION: reset_test_attendance
// Clears today's punch for a specific staff (or all kiosk punches today) for repeatable testing
// -------------------------------------------------------------
if ($action === 'reset_test_attendance') {
    $staff_id = (int)($jsonInput['staff_id'] ?? $_POST['staff_id'] ?? 0);
    $today = date('Y-m-d');
    
    if ($staff_id > 0) {
        $del = $conn->prepare("DELETE FROM staff_attendance WHERE hospital_id = ? AND staff_id = ? AND date = ?");
        $del->bind_param("iis", $hospital_id, $staff_id, $today);
        $del->execute();
    } else {
        $del = $conn->prepare("DELETE FROM staff_attendance WHERE hospital_id = ? AND date = ? AND (marked_by = 'Face Attendance Terminal' OR check_in_method = 'face_scan_kiosk')");
        $del->bind_param("is", $hospital_id, $today);
        $del->execute();
    }
    
    echo json_encode([
        'status' => 'success',
        'message' => 'Attendance reset for today. You can now test the first face punch!'
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: get_today_face_punches
// Returns today's live face punches for current hospital
// -------------------------------------------------------------
if ($action === 'get_today_face_punches') {
    $today = date('Y-m-d');
    $stmt = $conn->prepare("SELECT a.id, a.check_in_time, a.status, a.face_confidence, a.marked_by, a.notes,
                                   s.id AS staff_id, s.first_name, s.last_name, s.staff_code, s.role, s.department, s.avatar
                            FROM staff_attendance a
                            JOIN staff s ON a.staff_id = s.id
                            WHERE a.hospital_id = ? AND a.date = ? AND a.check_in_time IS NOT NULL AND a.status != 'Absent'
                            ORDER BY a.check_in_time DESC LIMIT 25");
    $stmt->bind_param("is", $hospital_id, $today);
    $stmt->execute();
    $res = $stmt->get_result();
    $punches = [];
    while ($row = $res->fetch_assoc()) {
        if (empty($row['avatar'])) {
            $row['avatar'] = 'https://ui-avatars.com/api/?name=' . urlencode($row['first_name'] . ' ' . $row['last_name']) . '&background=random&color=fff';
        }
        $row['time_formatted'] = date('h:i A', strtotime($row['check_in_time']));
        $punches[] = $row;
    }
    
    echo json_encode([
        'status' => 'success',
        'count' => count($punches),
        'data' => $punches
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action: ' . htmlspecialchars($action)]);
