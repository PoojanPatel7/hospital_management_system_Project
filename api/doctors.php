<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

if ($action === 'get_categories') {
    $res = $conn->query("SELECT id, name FROM departments ORDER BY name");
    $cats = [];
    while ($row = $res->fetch_assoc()) {
        $cats[] = $row;
    }
    jsonResponse(['status' => 'success', 'categories' => $cats]);
}

if ($action === 'get_hospital_doctors') {
    $hospital_id = $_SESSION['hospital_id'] ?? 0;
    
    // Fetch doctors and their categories using GROUP_CONCAT
    $query = "
        SELECT d.id, d.name, d.phone, GROUP_CONCAT(dep.name SEPARATOR '|') as categories
        FROM doctors d
        LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
        LEFT JOIN departments dep ON dc.department_id = dep.id
        WHERE d.hospital_id = ?
        GROUP BY d.id
    ";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $doctors = [];
    while ($row = $res->fetch_assoc()) {
        $doctors[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'phone' => $row['phone'] ?? '',
            'categories' => $row['categories'] ? explode('|', $row['categories']) : []
        ];
    }
    jsonResponse(['status' => 'success', 'doctors' => $doctors]);
}

if ($action === 'create_hospital_doctor') {
    $hospital_id = $_SESSION['hospital_id'] ?? 0;
    if (!$hospital_id) jsonResponse(['status' => 'error', 'message' => 'Not authenticated']);
    
    $name = $input['name'] ?? '';
    $phone = $input['phone'] ?? '';
    $categories = $input['categories'] ?? [];
    
    $doc_id = 'doc-' . uniqid();
    
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("INSERT INTO doctors (id, name, phone, hospital_id) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $doc_id, $name, $phone, $hospital_id);
        $stmt->execute();
        
        if (!empty($categories)) {
            $stmtCat = $conn->prepare("INSERT INTO doctor_categories (doctor_id, department_id) VALUES (?, ?)");
            foreach ($categories as $cat) {
                $stmtCat->bind_param("ss", $doc_id, $cat);
                $stmtCat->execute();
            }
        }
        
        $conn->commit();
        jsonResponse(['status' => 'success']);
    } catch (Throwable $e) {
        $conn->rollback();
        jsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($action === 'delete_hospital_doctor') {
    $hospital_id = $_SESSION['hospital_id'] ?? 0;
    if (!$hospital_id) jsonResponse(['status' => 'error', 'message' => 'Not authenticated']);
    $id = $_GET['id'] ?? '';
    if (!$id) jsonResponse(['status' => 'error', 'message' => 'Doctor ID is required']);
    
    $conn->begin_transaction();
    try {
        // 1. Delete associated slots
        $stmtSlots = $conn->prepare("DELETE FROM doctor_slots WHERE doctor_id = ?");
        $stmtSlots->bind_param("s", $id);
        $stmtSlots->execute();

        // 2. Delete associated category mappings
        $stmtCat = $conn->prepare("DELETE FROM doctor_categories WHERE doctor_id = ?");
        $stmtCat->bind_param("s", $id);
        $stmtCat->execute();

        // 3. Unlink appointments from this doctor to avoid RESTRICT foreign key violations while preserving patient visit history
        $stmtAppt = $conn->prepare("UPDATE appointments SET doctor_id = NULL WHERE doctor_id = ?");
        $stmtAppt->bind_param("s", $id);
        $stmtAppt->execute();

        // 4. Delete the doctor record
        $stmt = $conn->prepare("DELETE FROM doctors WHERE id = ? AND hospital_id = ?");
        $stmt->bind_param("si", $id, $hospital_id);
        $stmt->execute();

        $conn->commit();
        jsonResponse(['status' => 'success']);
    } catch (Throwable $e) {
        $conn->rollback();
        jsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($action === 'update_hospital_doctor') {
    $hospital_id = $_SESSION['hospital_id'] ?? 0;
    if (!$hospital_id) jsonResponse(['status' => 'error', 'message' => 'Not authenticated']);
    
    $doc_id = $input['id'] ?? '';
    $name = $input['name'] ?? '';
    $phone = $input['phone'] ?? '';
    $categories = $input['categories'] ?? [];
    
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("UPDATE doctors SET name = ?, phone = ? WHERE id = ? AND hospital_id = ?");
        $stmt->bind_param("sssi", $name, $phone, $doc_id, $hospital_id);
        $stmt->execute();
        
        // Remove old categories
        $stmtDel = $conn->prepare("DELETE FROM doctor_categories WHERE doctor_id = ?");
        $stmtDel->bind_param("s", $doc_id);
        $stmtDel->execute();
        
        // Insert new categories
        if (!empty($categories)) {
            $stmtCat = $conn->prepare("INSERT INTO doctor_categories (doctor_id, department_id) VALUES (?, ?)");
            foreach ($categories as $cat) {
                $stmtCat->bind_param("ss", $doc_id, $cat);
                $stmtCat->execute();
            }
        }
        
        $conn->commit();
        jsonResponse(['status' => 'success']);
    } catch (Throwable $e) {
        $conn->rollback();
        jsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($action === 'get_date_slider_data') {
    $hospital_id = $_SESSION['hospital_id'] ?? 0;
    
    // Generate next 14 consecutive days starting from today
    $days = [];
    for ($i = 0; $i < 14; $i++) {
        $timestamp = strtotime("+$i day");
        $dStr = date('Y-m-d', $timestamp);
        
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE (hospital_id = ? OR hospital_id IS NULL) AND date = ? AND status != 'Cancelled'");
        $stmt->bind_param("is", $hospital_id, $dStr);
        $stmt->execute();
        $cnt = (int)($stmt->get_result()->fetch_assoc()['cnt'] ?? 0);
        
        $days[] = [
            'date' => $dStr,
            'day_name' => ($i === 0) ? 'Today' : (($i === 1) ? 'Tomorrow' : date('D', $timestamp)),
            'full_day' => date('l', $timestamp),
            'display_date' => date('d M', $timestamp),
            'day_num' => date('d', $timestamp),
            'month' => date('M', $timestamp),
            'is_today' => ($i === 0),
            'appointments_count' => $cnt
        ];
    }
    jsonResponse(['status' => 'success', 'dates' => $days]);
}

function generateSlotsFromTiming($startTime, $endTime, $durationMinutes, $breakStart = null, $breakEnd = null) {
    $durationMinutes = (int)$durationMinutes;
    if ($durationMinutes <= 0) $durationMinutes = 30;

    $tStart = strtotime("2000-01-01 " . $startTime);
    $tEnd = strtotime("2000-01-01 " . $endTime);
    if (!$tStart || !$tEnd || $tStart >= $tEnd) return [];

    $tBreakStart = (!empty($breakStart)) ? strtotime("2000-01-01 " . $breakStart) : null;
    $tBreakEnd = (!empty($breakEnd)) ? strtotime("2000-01-01 " . $breakEnd) : null;

    $slots = [];
    $curr = $tStart;
    while ($curr + ($durationMinutes * 60) <= $tEnd) {
        $slotEnd = $curr + ($durationMinutes * 60);
        $inBreak = false;
        if ($tBreakStart && $tBreakEnd && $tBreakStart < $tBreakEnd) {
            if ($curr < $tBreakEnd && $slotEnd > $tBreakStart) {
                $inBreak = true;
            }
        }
        if (!$inBreak) {
            $slots[] = date('h:i A', $curr);
        }
        $curr += ($durationMinutes * 60);
    }
    return $slots;
}

if ($action === 'get_doctor_day_schedules') {
    $hospital_id = $_SESSION['hospital_id'] ?? 0;
    if (!$hospital_id) jsonResponse(['status' => 'error', 'message' => 'Not authenticated']);
    
    $doc_id = $_GET['doctor_id'] ?? '';
    if (!$doc_id) jsonResponse(['status' => 'error', 'message' => 'Doctor ID is required']);

    // Standard 7 days of week
    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    // Fetch saved configurations
    $stmt = $conn->prepare("SELECT * FROM doctor_day_schedules WHERE doctor_id = ?");
    $stmt->bind_param("s", $doc_id);
    $stmt->execute();
    $res = $stmt->get_result();

    $savedMap = [];
    while ($row = $res->fetch_assoc()) {
        $savedMap[$row['day_of_week']] = $row;
    }

    $schedules = [];
    foreach ($daysOfWeek as $day) {
        if (isset($savedMap[$day])) {
            $row = $savedMap[$day];
            $customSlots = !empty($row['custom_slots']) ? json_decode($row['custom_slots'], true) : null;
            $genSlots = generateSlotsFromTiming($row['start_time'], $row['end_time'], (int)$row['duration_minutes'], $row['break_start'], $row['break_end']);
            
            $schedules[] = [
                'day_of_week' => $day,
                'is_available' => (int)$row['is_available'],
                'start_time' => $row['start_time'] ?? '09:00 AM',
                'end_time' => $row['end_time'] ?? '05:00 PM',
                'duration_minutes' => (int)($row['duration_minutes'] ?? 30),
                'break_start' => $row['break_start'] ?? '01:00 PM',
                'break_end' => $row['break_end'] ?? '02:00 PM',
                'slots' => is_array($customSlots) ? $customSlots : $genSlots,
                'is_custom' => is_array($customSlots)
            ];
        } else {
            // Default settings for unconfigured days
            $isSunday = ($day === 'Sunday');
            $isSat = ($day === 'Saturday');
            $startTime = '09:00 AM';
            $endTime = $isSat ? '01:00 PM' : '05:00 PM';
            $duration = 30;
            $breakStart = ($isSat || $isSunday) ? null : '01:00 PM';
            $breakEnd = ($isSat || $isSunday) ? null : '02:00 PM';
            $isAvail = $isSunday ? 0 : 1;
            
            $genSlots = generateSlotsFromTiming($startTime, $endTime, $duration, $breakStart, $breakEnd);
            $schedules[] = [
                'day_of_week' => $day,
                'is_available' => $isAvail,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $duration,
                'break_start' => $breakStart,
                'break_end' => $breakEnd,
                'slots' => $genSlots,
                'is_custom' => false
            ];
        }
    }

    jsonResponse([
        'status' => 'success',
        'doctor_id' => $doc_id,
        'schedules' => $schedules
    ]);
}

if ($action === 'save_doctor_day_schedules') {
    $hospital_id = $_SESSION['hospital_id'] ?? 0;
    if (!$hospital_id) jsonResponse(['status' => 'error', 'message' => 'Not authenticated']);
    
    $doc_id = $input['doctor_id'] ?? '';
    $schedules = $input['schedules'] ?? [];
    if (!$doc_id || empty($schedules)) {
        jsonResponse(['status' => 'error', 'message' => 'Doctor ID and schedules array required']);
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("
            INSERT INTO doctor_day_schedules (
                doctor_id, day_of_week, is_available, start_time, end_time, duration_minutes, break_start, break_end, custom_slots
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE
                is_available = VALUES(is_available),
                start_time = VALUES(start_time),
                end_time = VALUES(end_time),
                duration_minutes = VALUES(duration_minutes),
                break_start = VALUES(break_start),
                break_end = VALUES(break_end),
                custom_slots = VALUES(custom_slots)
        ");

        foreach ($schedules as $s) {
            $day = $s['day_of_week'];
            $isAvail = (int)($s['is_available'] ?? 1);
            $start = $s['start_time'] ?? '09:00 AM';
            $end = $s['end_time'] ?? '05:00 PM';
            $duration = (int)($s['duration_minutes'] ?? 30);
            $bStart = !empty($s['break_start']) ? $s['break_start'] : null;
            $bEnd = !empty($s['break_end']) ? $s['break_end'] : null;
            $customSlotsJson = isset($s['custom_slots']) && is_array($s['custom_slots']) ? json_encode($s['custom_slots']) : null;

            $stmt->bind_param(
                "ssississs",
                $doc_id,
                $day,
                $isAvail,
                $start,
                $end,
                $duration,
                $bStart,
                $bEnd,
                $customSlotsJson
            );
            $stmt->execute();
        }

        $conn->commit();
        jsonResponse(['status' => 'success', 'message' => 'Doctor schedules saved successfully']);
    } catch (Throwable $e) {
        $conn->rollback();
        jsonResponse(['status' => 'error', 'message' => $e->getMessage()]);
    }
}

if ($action === 'get_doctor_schedule') {
    $hospital_id = !empty($_SESSION['hospital_id']) ? (int)$_SESSION['hospital_id'] : 1;
    $date = $_GET['date'] ?? date('Y-m-d');
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }
    
    // Day of the week for selected date (e.g. 'Monday', 'Tuesday', ...)
    $dayOfWeek = date('l', strtotime($date));

    $standard_slots = [
        '09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', 
        '11:00 AM', '11:30 AM', '02:00 PM', '02:30 PM', 
        '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM'
    ];
    
    // Fetch all doctors for this hospital
    $query = "
        SELECT d.id, d.name, d.phone, GROUP_CONCAT(dep.name SEPARATOR '|') as categories
        FROM doctors d
        LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
        LEFT JOIN departments dep ON dc.department_id = dep.id
        WHERE d.hospital_id = ?
        GROUP BY d.id
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $hospital_id);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $doctors = [];
    while ($doc = $res->fetch_assoc()) {
        $doc_id = $doc['id'];
        
        // 1. Check doctor_day_schedules for this doctor and this specific day of week
        $schedStmt = $conn->prepare("SELECT * FROM doctor_day_schedules WHERE doctor_id = ? AND day_of_week = ?");
        $schedStmt->bind_param("ss", $doc_id, $dayOfWeek);
        $schedStmt->execute();
        $schedRow = $schedStmt->get_result()->fetch_assoc();

        $isAvailable = true;
        $startTime = '09:00 AM';
        $endTime = '05:00 PM';
        $duration = 30;
        $docSlots = [];

        if ($schedRow) {
            $isAvailable = ((int)$schedRow['is_available'] === 1);
            $startTime = $schedRow['start_time'] ?? '09:00 AM';
            $endTime = $schedRow['end_time'] ?? '05:00 PM';
            $duration = (int)($schedRow['duration_minutes'] ?? 30);

            if ($isAvailable) {
                if (!empty($schedRow['custom_slots'])) {
                    $docSlots = json_decode($schedRow['custom_slots'], true) ?: [];
                }
                if (empty($docSlots)) {
                    $docSlots = generateSlotsFromTiming($startTime, $endTime, $duration, $schedRow['break_start'], $schedRow['break_end']);
                }
            }
        } else {
            // Fallback for Sunday
            if ($dayOfWeek === 'Sunday') {
                $isAvailable = false;
                $startTime = '09:00 AM';
                $endTime = '01:00 PM';
            } else {
                // Fallback to legacy doctor_slots table or standard slots
                $sRes = $conn->query("SELECT time_slot FROM doctor_slots WHERE doctor_id = '$doc_id' ORDER BY id");
                if ($sRes) {
                    while ($sRow = $sRes->fetch_assoc()) {
                        $docSlots[] = $sRow['time_slot'];
                    }
                }
                if (empty($docSlots)) {
                    $docSlots = $standard_slots;
                }
            }
        }
        
        // Fetch booked appointments for this doctor on this specific date
        $appStmt = $conn->prepare("
            SELECT a.id, a.slot, a.status, a.stage, a.type, a.symptoms,
                   p.id as patient_id, p.name as patient_name, p.surname as patient_surname, p.phone as patient_phone
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            WHERE a.doctor_id = ? AND a.date = ? AND a.status != 'Cancelled'
            ORDER BY a.slot ASC
        ");
        $appStmt->bind_param("ss", $doc_id, $date);
        $appStmt->execute();
        $appRes = $appStmt->get_result();
        
        $bookedSlots = [];
        while ($app = $appRes->fetch_assoc()) {
            $bookedSlots[$app['slot']] = $app;
        }
        
        $slotList = [];
        $bookedCount = 0;
        if ($isAvailable) {
            foreach ($docSlots as $slotTime) {
                $isBooked = isset($bookedSlots[$slotTime]);
                if ($isBooked) $bookedCount++;
                $slotList[] = [
                    'time' => $slotTime,
                    'is_booked' => $isBooked,
                    'appointment' => $bookedSlots[$slotTime] ?? null
                ];
            }
        }
        
        $doctors[] = [
            'id' => $doc_id,
            'name' => $doc['name'],
            'phone' => $doc['phone'] ?? '',
            'categories' => $doc['categories'] ? explode('|', $doc['categories']) : [],
            'day_of_week' => $dayOfWeek,
            'is_available' => $isAvailable,
            'timing' => $isAvailable ? "$startTime - $endTime" : "Day Off ($dayOfWeek)",
            'start_time' => $startTime,
            'end_time' => $endTime,
            'duration_minutes' => $duration,
            'total_slots' => count($docSlots),
            'booked_count' => $bookedCount,
            'available_count' => count($docSlots) - $bookedCount,
            'slots' => $slotList
        ];
    }
    
    // Fetch ALL appointments for this date across all doctors
    $allAppStmt = $conn->prepare("
        SELECT a.id as appointment_id, a.date, a.slot, a.status, a.stage, a.type, a.symptoms, a.created_at,
               p.id as patient_id, p.name as patient_name, p.surname as patient_surname, p.phone as patient_phone, p.blood_group,
               d.id as doctor_id, d.name as doctor_name
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        LEFT JOIN doctors d ON a.doctor_id = d.id
        WHERE (a.hospital_id = ? OR a.hospital_id IS NULL) AND a.date = ?
        ORDER BY a.slot ASC, a.created_at ASC
    ");
    $allAppStmt->bind_param("is", $hospital_id, $date);
    $allAppStmt->execute();
    $allAppRes = $allAppStmt->get_result();
    
    $dateAppointments = [];
    $tokenIdx = 1;
    while ($row = $allAppRes->fetch_assoc()) {
        $row['token_no'] = $tokenIdx++;
        $dateAppointments[] = $row;
    }
    
    jsonResponse([
        'status' => 'success',
        'date' => $date,
        'day_of_week' => $dayOfWeek,
        'doctors' => $doctors,
        'appointments' => $dateAppointments
    ]);
}

if ($action === 'get_appointments_filtered') {
    $hospital_id = !empty($_SESSION['hospital_id']) ? (int)$_SESSION['hospital_id'] : 1;
    $doc_id = $_GET['doctor_id'] ?? '';
    $date = $_GET['date'] ?? '';
    $date_from = $_GET['date_from'] ?? '';
    $date_to = $_GET['date_to'] ?? '';
    $status = $_GET['status'] ?? '';
    $type = $_GET['type'] ?? '';
    $stage = $_GET['stage'] ?? '';
    $search = trim($_GET['search'] ?? '');

    $where = ["(a.hospital_id = ? OR a.hospital_id IS NULL)"];
    $params = [$hospital_id];
    $types = "i";

    if (!empty($doc_id) && $doc_id !== 'all') {
        $where[] = "a.doctor_id = ?";
        $params[] = $doc_id;
        $types .= "s";
    }

    if (!empty($date) && $date !== 'all') {
        $where[] = "a.date = ?";
        $params[] = $date;
        $types .= "s";
    } elseif (!empty($date_from) && !empty($date_to)) {
        $where[] = "a.date BETWEEN ? AND ?";
        $params[] = $date_from;
        $params[] = $date_to;
        $types .= "ss";
    } elseif (!empty($date_from)) {
        $where[] = "a.date >= ?";
        $params[] = $date_from;
        $types .= "s";
    }

    if (!empty($status) && $status !== 'all') {
        $where[] = "a.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if (!empty($type) && $type !== 'all') {
        $where[] = "a.type = ?";
        $params[] = $type;
        $types .= "s";
    }

    if ($stage !== '' && $stage !== 'all') {
        $where[] = "a.stage = ?";
        $params[] = (int)$stage;
        $types .= "i";
    }

    if (!empty($search)) {
        $sTerm = "%" . $search . "%";
        $where[] = "(p.name LIKE ? OR p.surname LIKE ? OR p.phone LIKE ? OR p.id LIKE ? OR a.symptoms LIKE ? OR d.name LIKE ?)";
        $params[] = $sTerm;
        $params[] = $sTerm;
        $params[] = $sTerm;
        $params[] = $sTerm;
        $params[] = $sTerm;
        $params[] = $sTerm;
        $types .= "ssssss";
    }

    $whereClause = implode(" AND ", $where);
    $query = "
        SELECT a.id, a.id as appointment_id, a.patient_id, a.doctor_id, a.type, a.date, a.slot, a.symptoms, a.allergies, a.status, a.stage, a.created_at,
               p.name as patient_name, p.surname as patient_surname, p.phone as patient_phone, p.blood_group, p.gender, p.age, p.father_name,
               d.name as doctor_name, d.phone as doctor_phone,
               (SELECT GROUP_CONCAT(dep.name SEPARATOR ', ') FROM doctor_categories dc JOIN departments dep ON dc.department_id = dep.id WHERE dc.doctor_id = a.doctor_id) as doctor_specialties
        FROM appointments a
        JOIN patients p ON a.patient_id = p.id
        LEFT JOIN doctors d ON a.doctor_id = d.id
        WHERE $whereClause
        ORDER BY a.date DESC, a.slot ASC, a.created_at DESC
    ";

    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $res = $stmt->get_result();

    $appointments = [];
    $tokenIdx = 1;
    $summary = [
        'total' => 0,
        'waiting' => 0,
        'consulting' => 0,
        'completed' => 0,
        'cancelled' => 0
    ];

    while ($row = $res->fetch_assoc()) {
        $row['token_no'] = $tokenIdx++;
        $row['appointment_code'] = sprintf("APP-%04d", (int)$row['id']);
        
        $st = $row['status'];
        $stageNum = (int)$row['stage'];
        $summary['total']++;

        if ($st === 'Cancelled') {
            $summary['cancelled']++;
        } elseif ($stageNum >= 5 || strpos($st, 'Discharged') !== false) {
            $summary['completed']++;
        } elseif ($stageNum === 4 || strpos($st, 'Consulting') !== false) {
            $summary['consulting']++;
        } else {
            $summary['waiting']++;
        }

        $appointments[] = $row;
    }

    jsonResponse([
        'status' => 'success',
        'count' => count($appointments),
        'summary' => $summary,
        'appointments' => $appointments
    ]);
}

if ($action === 'cancel_appointment') {
    $hospital_id = !empty($_SESSION['hospital_id']) ? (int)$_SESSION['hospital_id'] : 1;
    $appt_id = (int)($input['appointment_id'] ?? ($_GET['id'] ?? 0));
    $reason = $input['reason'] ?? 'Cancelled by staff';

    if (!$appt_id) {
        jsonResponse(['status' => 'error', 'message' => 'Appointment ID required']);
    }

    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled', stage = 0 WHERE id = ? AND (hospital_id = ? OR hospital_id IS NULL)");
    $stmt->bind_param("ii", $appt_id, $hospital_id);
    if ($stmt->execute()) {
        $time_now = date('h:i A');
        $desc = "Appointment cancelled. Reason: " . $reason;
        $conn->query("INSERT INTO timeline_events (appointment_id, event_time, event_description) VALUES ($appt_id, '$time_now', '$desc')");
        jsonResponse(['status' => 'success', 'message' => 'Appointment marked as Cancelled']);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Could not cancel appointment']);
    }
}
?>
