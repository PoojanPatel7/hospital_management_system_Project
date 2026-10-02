<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_categories') {
        $res = $conn->query("SELECT id, name FROM departments ORDER BY name");
        $cats = [];
        while ($row = $res->fetch_assoc()) {
            $cats[] = $row;
        }
        jsonResponse(['status' => 'success', 'categories' => $cats]);
    }

    if ($action === 'get_doctors_by_category' || $action === 'get_all_doctors_with_schedules') {
        $hospital_id = $_SESSION['hospital_id'] ?? 0;
        $cat_id = $_GET['category_id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        $dayOfWeek = date('l', strtotime($date));
        
        if (!empty($cat_id) && $cat_id !== 'all' && $action !== 'get_all_doctors_with_schedules') {
            $stmt = $conn->prepare("
                SELECT d.id, d.name, d.phone, GROUP_CONCAT(DISTINCT dep.name SEPARATOR '|') as categories
                FROM doctors d
                JOIN doctor_categories dc ON d.id = dc.doctor_id
                LEFT JOIN departments dep ON dc.department_id = dep.id
                WHERE d.hospital_id = ? AND dc.department_id = ?
                GROUP BY d.id
                ORDER BY d.name ASC
            ");
            $stmt->bind_param("is", $hospital_id, $cat_id);
        } else {
            $stmt = $conn->prepare("
                SELECT d.id, d.name, d.phone, GROUP_CONCAT(DISTINCT dep.name SEPARATOR '|') as categories
                FROM doctors d
                LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
                LEFT JOIN departments dep ON dc.department_id = dep.id
                WHERE d.hospital_id = ?
                GROUP BY d.id
                ORDER BY d.name ASC
            ");
            $stmt->bind_param("i", $hospital_id);
        }
        $stmt->execute();
        $res = $stmt->get_result();
        
        $standard_slots = [
            '09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', 
            '11:00 AM', '11:30 AM', '02:00 PM', '02:30 PM', 
            '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM'
        ];

        $doctors = [];
        while ($row = $res->fetch_assoc()) {
            $doc_id = $row['id'];
            $categories = $row['categories'] ? explode('|', $row['categories']) : [];

            // Fetch day schedule for this doctor on $dayOfWeek
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
                        if (!function_exists('generateSlotsFromTiming')) {
                            require_once __DIR__ . '/doctors.php';
                        }
                        if (function_exists('generateSlotsFromTiming')) {
                            $docSlots = generateSlotsFromTiming($startTime, $endTime, $duration, $schedRow['break_start'], $schedRow['break_end']);
                        }
                    }
                }
            } else {
                if ($dayOfWeek === 'Sunday') {
                    $isAvailable = false;
                    $startTime = '09:00 AM';
                    $endTime = '01:00 PM';
                } else {
                    $sRes = $conn->query("SELECT time_slot FROM doctor_slots WHERE doctor_id='$doc_id' ORDER BY id");
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

            // Fetch booked appointments for this doctor on $date
            $bookedStmt = $conn->prepare("SELECT slot FROM appointments WHERE doctor_id = ? AND date = ? AND status != 'Cancelled'");
            $bookedStmt->bind_param("ss", $doc_id, $date);
            $bookedStmt->execute();
            $bRes = $bookedStmt->get_result();
            $bookedList = [];
            while ($bRow = $bRes->fetch_assoc()) {
                $bookedList[] = $bRow['slot'];
            }

            $totalSlots = count($docSlots);
            $bookedCount = count(array_intersect($docSlots, $bookedList));
            $availableCount = max(0, $totalSlots - $bookedCount);

            $doctors[] = [
                'id' => $doc_id,
                'name' => $row['name'],
                'phone' => $row['phone'] ?? '',
                'categories' => $categories,
                'is_available' => $isAvailable,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'duration_minutes' => $duration,
                'today_timing' => $isAvailable ? "{$startTime} - {$endTime}" : 'Day Off',
                'slots' => $docSlots,
                'total_slots' => $totalSlots,
                'booked_count' => $bookedCount,
                'available_count' => $isAvailable ? $availableCount : 0
            ];
        }
        
        jsonResponse(['status' => 'success', 'doctors' => $doctors]);
    }
    else if ($action === 'get_booked_slots') {
        $doctor_id = $_GET['doctor_id'] ?? '';
        $date = $_GET['date'] ?? '';
        
        $booked = [];
        $stmt = $conn->prepare("SELECT slot FROM appointments WHERE doctor_id = ? AND date = ? AND status != 'Discharged (Normal Medicine)' AND status NOT LIKE 'Admitted%' AND status != 'Cancelled'");
        $stmt->bind_param("ss", $doctor_id, $date);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $booked[] = $row['slot'];
        }
        
        jsonResponse(['status' => 'success', 'booked' => $booked]);
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'book') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        $patient_id = $data['patient_id'] ?? '';
        $doctor_id = $data['doctor_id'] ?? '';
        $type = $data['type'] ?? 'General Consultation';
        $date = $data['date'] ?? date('Y-m-d');
        $slot = $data['slot'] ?? '09:00 AM';
        $symptoms = $data['symptoms'] ?? '';
        $allergies = $data['allergies'] ?? 'None recorded';
        $status = 'Pre-Booked';
        $stage = 0;
        $hospital_id = $_SESSION['hospital_id'] ?? 0;
        
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, allergies, status, stage, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssii", $patient_id, $doctor_id, $type, $date, $slot, $symptoms, $allergies, $status, $stage, $hospital_id);
        
        if ($stmt->execute()) {
            $app_id = $stmt->insert_id;
            
            // Log timeline event
            $event = "Booked consultation ($type) on $date for slot $slot. Status: $status.";
            $time_now = date('h:i A');
            $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $tStmt->bind_param("isss", $app_id, $patient_id, $time_now, $event);
            $tStmt->execute();
            
            jsonResponse(['status' => 'success', 'message' => 'Appointment recorded successfully.']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to book appointment: ' . $conn->error]);
        }
    }
}
?>
