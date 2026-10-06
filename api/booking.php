<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_GET['action'] ?? '';

// Fallback to primary hospital if guest/patient is booking publicly without session
$hospital_id = $_SESSION['hospital_id'] ?? 0;
if (!$hospital_id) {
    $hRes = $conn->query("SELECT id FROM hospitals ORDER BY id ASC LIMIT 1");
    if ($hRes && $hRow = $hRes->fetch_assoc()) {
        $hospital_id = (int)$hRow['id'];
    } else {
        $hospital_id = 1;
    }
}

function fetchDoctorDayScheduleAndSlots($conn, $doctor_id, $date) {
    if (!function_exists('generateSlotsFromTiming')) {
        @require_once __DIR__ . '/doctors.php';
    }
    $dayOfWeek = date('l', strtotime($date));
    $schedStmt = $conn->prepare("SELECT * FROM doctor_day_schedules WHERE doctor_id = ? AND day_of_week = ?");
    $schedStmt->bind_param("ss", $doctor_id, $dayOfWeek);
    $schedStmt->execute();
    $schedRow = $schedStmt->get_result()->fetch_assoc();

    $isAvailable = true;
    $startTime = '09:00 AM';
    $endTime = '05:00 PM';
    $duration = 30;
    $breakStart = '01:00 PM';
    $breakEnd = '02:00 PM';
    $docSlots = [];

    if ($schedRow) {
        $isAvailable = ((int)$schedRow['is_available'] === 1);
        $startTime = $schedRow['start_time'] ?? '09:00 AM';
        $endTime = $schedRow['end_time'] ?? '05:00 PM';
        $duration = (int)($schedRow['duration_minutes'] ?? 30);
        $breakStart = $schedRow['break_start'];
        $breakEnd = $schedRow['break_end'];

        if ($isAvailable) {
            if (!empty($schedRow['custom_slots'])) {
                $docSlots = json_decode($schedRow['custom_slots'], true) ?: [];
            }
            if (empty($docSlots) && function_exists('generateSlotsFromTiming')) {
                $docSlots = generateSlotsFromTiming($startTime, $endTime, $duration, $breakStart, $breakEnd);
            }
        }
    } else {
        if ($dayOfWeek === 'Sunday') {
            $isAvailable = false;
        } else {
            $sRes = $conn->query("SELECT time_slot FROM doctor_slots WHERE doctor_id='$doctor_id' ORDER BY id");
            if ($sRes && $sRes->num_rows > 0) {
                while ($sRow = $sRes->fetch_assoc()) {
                    $docSlots[] = $sRow['time_slot'];
                }
            } else {
                if (function_exists('generateSlotsFromTiming')) {
                    $docSlots = generateSlotsFromTiming('09:00 AM', $dayOfWeek === 'Saturday' ? '01:00 PM' : '05:00 PM', 30, '01:00 PM', '02:00 PM');
                } else {
                    $docSlots = ['09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM', '02:00 PM', '02:30 PM', '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM'];
                }
            }
        }
    }
    return [
        'is_available' => $isAvailable,
        'start_time' => $startTime,
        'end_time' => $endTime,
        'break_start' => $breakStart,
        'break_end' => $breakEnd,
        'slots' => $docSlots
    ];
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {

    // 1. Get Departments / Categories
    if ($action === 'get_categories') {
        $res = $conn->query("SELECT id, name FROM departments ORDER BY name");
        $cats = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $cats[] = $row;
            }
        }
        jsonResponse(['status' => 'success', 'categories' => $cats]);
    }

    // 2. Lookup Patient by Phone Number (or MRN)
    else if ($action === 'lookup_patient') {
        $phone = trim($_GET['phone'] ?? '');
        if (empty($phone)) {
            jsonResponse(['status' => 'error', 'message' => 'Phone number is required.']);
        }

        // Clean digits
        $digits = preg_replace('/\D/', '', $phone);
        $last10 = strlen($digits) >= 10 ? substr($digits, -10) : $digits;
        $phoneLike = '%' . $last10 . '%';
        $rawExact = $phone;

        $stmt = $conn->prepare("
            SELECT id, name, surname, father_name, phone, demographics, gender, blood_group, age, 
                   emergency_contact_name, emergency_contact_phone, DATE_FORMAT(created_at, '%b %d, %Y') as reg_date 
            FROM patients 
            WHERE (hospital_id = ? OR hospital_id IS NULL)
              AND (
                REPLACE(REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+91', ''), '+', '') LIKE ?
                OR phone LIKE ?
                OR id = ?
              )
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->bind_param("isss", $hospital_id, $phoneLike, $phoneLike, $rawExact);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($row = $res->fetch_assoc()) {
            $pat_id = $row['id'];
            $appRes = $conn->query("SELECT COUNT(*) as total_appts, MAX(date) as last_date FROM appointments WHERE patient_id = '$pat_id'");
            $appData = $appRes ? $appRes->fetch_assoc() : null;
            $row['total_appointments'] = (int)($appData['total_appts'] ?? 0);
            $row['last_visit'] = $appData['last_date'] ? date('M d, Y', strtotime($appData['last_date'])) : 'None';

            jsonResponse([
                'status' => 'success',
                'found' => true,
                'patient' => $row
            ]);
        } else {
            jsonResponse([
                'status' => 'success',
                'found' => false,
                'message' => 'No existing patient profile found for this mobile number.'
            ]);
        }
    }

    // 3. Generate Anti-Bot Human Verification Challenge ("Not-AI Test")
    else if ($action === 'get_captcha') {
        $num1 = rand(3, 18);
        $num2 = rand(2, 12);
        $answer = $num1 + $num2;
        $_SESSION['not_ai_captcha_answer'] = $answer;
        $token = bin2hex(random_bytes(12));
        $_SESSION['not_ai_captcha_token'] = $token;

        jsonResponse([
            'status' => 'success',
            'question' => "What is $num1 + $num2?",
            'num1' => $num1,
            'num2' => $num2,
            'token' => $token
        ]);
    }

    // 4. Get Doctors List with Live Today Schedule & Counts
    else if ($action === 'get_doctors_by_category' || $action === 'get_all_doctors_with_schedules') {
        $cat_id = $_GET['category_id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        $dayOfWeek = date('l', strtotime($date));

        if (!empty($cat_id) && $cat_id !== 'all' && $action !== 'get_all_doctors_with_schedules') {
            $stmt = $conn->prepare("
                SELECT d.id, d.name, d.phone, d.experience, d.degree, GROUP_CONCAT(DISTINCT dep.name SEPARATOR '|') as categories
                FROM doctors d
                JOIN doctor_categories dc ON d.id = dc.doctor_id
                LEFT JOIN departments dep ON dc.department_id = dep.id
                WHERE (d.hospital_id = ? OR d.hospital_id IS NULL) AND dc.department_id = ?
                GROUP BY d.id
                ORDER BY d.name ASC
            ");
            $stmt->bind_param("is", $hospital_id, $cat_id);
        } else {
            $stmt = $conn->prepare("
                SELECT d.id, d.name, d.phone, d.experience, d.degree, GROUP_CONCAT(DISTINCT dep.name SEPARATOR '|') as categories
                FROM doctors d
                LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
                LEFT JOIN departments dep ON dc.department_id = dep.id
                WHERE (d.hospital_id = ? OR d.hospital_id IS NULL)
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
                    if ($sRes && $sRes->num_rows > 0) {
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
                'experience' => $row['experience'] ?? '',
                'degree' => $row['degree'] ?? '',
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

    // 5. Get Detailed Slots for Doctor on Specific Date with Availability
    else if ($action === 'get_doctor_slots') {
        $doctor_id = $_GET['doctor_id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $date = date('Y-m-d');
        }
        if (empty($doctor_id)) {
            jsonResponse(['status' => 'error', 'message' => 'Doctor ID is required.']);
        }

        $dayOfWeek = date('l', strtotime($date));

        // Get doctor info
        $docStmt = $conn->prepare("
            SELECT d.id, d.name, d.phone, d.experience, d.degree,
                   GROUP_CONCAT(DISTINCT dep.name SEPARATOR ', ') as specialties
            FROM doctors d
            LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
            LEFT JOIN departments dep ON dc.department_id = dep.id
            WHERE d.id = ?
            GROUP BY d.id
        ");
        $docStmt->bind_param("s", $doctor_id);
        $docStmt->execute();
        $doctorInfo = $docStmt->get_result()->fetch_assoc();

        if (!$doctorInfo) {
            jsonResponse(['status' => 'error', 'message' => 'Doctor not found.']);
        }

        // Schedule check
        $daySched = fetchDoctorDayScheduleAndSlots($conn, $doctor_id, $date);
        $isAvailable = $daySched['is_available'];
        $startTime = $daySched['start_time'];
        $endTime = $daySched['end_time'];
        $breakStart = $daySched['break_start'];
        $breakEnd = $daySched['break_end'];
        $docSlots = $daySched['slots'];

        if (!$isAvailable) {
            jsonResponse([
                'status' => 'success',
                'doctor' => $doctorInfo,
                'date' => $date,
                'day_of_week' => $dayOfWeek,
                'is_available' => false,
                'message' => "{$doctorInfo['name']} is not available on {$dayOfWeek}s (Day Off). Please select another date.",
                'slots' => []
            ]);
        }

        // Get booked slots for this doctor on $date
        $bookedStmt = $conn->prepare("SELECT slot FROM appointments WHERE doctor_id = ? AND date = ? AND status != 'Cancelled'");
        $bookedStmt->bind_param("ss", $doctor_id, $date);
        $bookedStmt->execute();
        $bRes = $bookedStmt->get_result();
        $bookedList = [];
        while ($bRow = $bRes->fetch_assoc()) {
            $bookedList[] = $bRow['slot'];
        }

        // Group slots by period (Morning, Afternoon, Evening)
        $processedSlots = [];
        $totalSlotsCount = count($docSlots);
        $slotIdx = 0;
        foreach ($docSlots as $s) {
            $slotIdx++;
            $isBooked = in_array($s, $bookedList);
            $period = 'Morning';
            $t = strtotime("2000-01-01 $s");
            if ($t) {
                $hour = (int)date('H', $t);
                if ($hour >= 12 && $hour < 17) $period = 'Afternoon';
                else if ($hour >= 17) $period = 'Evening';
            }

            $processedSlots[] = [
                'slot' => $s,
                'slot_number' => $slotIdx,
                'total_slots' => $totalSlotsCount,
                'available' => !$isBooked,
                'period' => $period
            ];
        }

        $availableCount = count(array_filter($processedSlots, fn($x) => $x['available']));
        $bookedCount = count($processedSlots) - $availableCount;

        jsonResponse([
            'status' => 'success',
            'doctor' => $doctorInfo,
            'date' => $date,
            'day_of_week' => $dayOfWeek,
            'is_available' => true,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'break_start' => $breakStart,
            'break_end' => $breakEnd,
            'total_slots' => count($processedSlots),
            'available_count' => $availableCount,
            'booked_count' => $bookedCount,
            'slots' => $processedSlots
        ]);
    }

    // 6. Legacy get_booked_slots compatibility
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
    
    // 7. Get Patient Today Booking & All Active Online Bookings
    else if ($action === 'get_patient_today_booking') {
        $patient_id = trim($_GET['patient_id'] ?? '');
        if (empty($patient_id)) {
            jsonResponse(['status' => 'error', 'message' => 'Patient ID required.']);
        }
        
        $today = date('Y-m-d');
        $stmt = $conn->prepare("
            SELECT a.*, d.name as doctor_name, d.degree,
                   GROUP_CONCAT(DISTINCT dep.name SEPARATOR ', ') as doctor_specialties
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
            LEFT JOIN departments dep ON dc.department_id = dep.id
            WHERE a.patient_id = ? AND a.date = ? AND a.status NOT IN ('Cancelled', 'Discharged (Normal Medicine)')
            GROUP BY a.id
            ORDER BY a.slot ASC
        ");
        $stmt->bind_param("ss", $patient_id, $today);
        $stmt->execute();
        $result = $stmt->get_result();
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            // Compute next token sequence for this doctor today
            $tokStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM appointments WHERE doctor_id = ? AND date = ? AND status NOT IN ('Cancelled', 'Online-Booked') AND stage >= 1");
            $tokStmt->bind_param("ss", $row['doctor_id'], $today);
            $tokStmt->execute();
            $tokRow = $tokStmt->get_result()->fetch_assoc();
            $row['suggested_token'] = (int)($tokRow['cnt'] ?? 0) + 1;
            $bookings[] = $row;
        }

        // Also fetch ALL online bookings (all dates / upcoming) for this patient
        $onlineStmt = $conn->prepare("
            SELECT a.*, d.name as doctor_name, d.degree,
                   GROUP_CONCAT(DISTINCT dep.name SEPARATOR ', ') as doctor_specialties
            FROM appointments a
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
            LEFT JOIN departments dep ON dc.department_id = dep.id
            WHERE a.patient_id = ? 
              AND (a.booking_type = 'online' OR a.status = 'Online-Booked') 
              AND a.status NOT IN ('Cancelled', 'Discharged (Normal Medicine)')
            GROUP BY a.id
            ORDER BY a.date DESC, a.slot ASC
        ");
        $onlineStmt->bind_param("s", $patient_id);
        $onlineStmt->execute();
        $onlineRes = $onlineStmt->get_result();
        $all_online_bookings = [];
        while ($oRow = $onlineRes->fetch_assoc()) {
            $all_online_bookings[] = $oRow;
        }

        // Global suggested token across hospital for today
        $globTok = $conn->query("SELECT COUNT(*) as cnt FROM appointments WHERE date = '$today' AND status NOT IN ('Cancelled', 'Online-Booked') AND stage >= 1");
        $globRow = $globTok ? $globTok->fetch_assoc() : null;
        $default_suggested = (int)($globRow['cnt'] ?? 0) + 1;
        
        jsonResponse([
            'status' => 'success',
            'today' => $today,
            'has_booking' => count($bookings) > 0,
            'bookings' => $bookings,
            'all_online_bookings' => $all_online_bookings,
            'default_suggested_token' => $default_suggested
        ]);
    }

    // 7. Live Token Availability & Collision Check
    else if ($action === 'check_token') {
        $token_number = (int)($_GET['token_number'] ?? 0);
        $doctor_id = trim($_GET['doctor_id'] ?? '');
        $date = trim($_GET['date'] ?? date('Y-m-d'));
        $exclude_appt_id = (int)($_GET['exclude_appointment_id'] ?? 0);

        if ($token_number <= 0) {
            jsonResponse([
                'status' => 'success',
                'is_available' => false,
                'message' => 'Please type a valid token number (e.g. 1, 2, 3...)'
            ]);
        }

        // Query appointments for that date and token
        $sql = "
            SELECT a.id, a.token_number, a.patient_id, a.doctor_id, a.status, a.slot, a.stage,
                   p.name, p.surname, d.name as doctor_name
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.id
            LEFT JOIN doctors d ON a.doctor_id = d.id
            WHERE a.date = ? 
              AND a.token_number = ?
              AND a.status NOT IN ('Cancelled')
        ";
        $params = [$date, $token_number];
        $types = "si";

        if (!empty($doctor_id)) {
            $sql .= " AND a.doctor_id = ?";
            $params[] = $doctor_id;
            $types .= "s";
        }

        if ($exclude_appt_id > 0) {
            $sql .= " AND a.id != ?";
            $params[] = $exclude_appt_id;
            $types .= "i";
        }

        $sql .= " LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if ($row) {
            $pName = trim(($row['name'] ?? '') . ' ' . ($row['surname'] ?? '')) ?: ('Patient ' . $row['patient_id']);
            $docName = $row['doctor_name'] ? (stripos(trim($row['doctor_name']), 'Dr.') === 0 ? trim($row['doctor_name']) : ('Dr. ' . trim($row['doctor_name']))) : 'Consulting Doctor';
            jsonResponse([
                'status' => 'success',
                'is_available' => false,
                'message' => "Token #{$token_number} is ALREADY ASSIGNED to {$pName} ({$row['patient_id']}) for {$docName} today!",
                'assigned_to' => [
                    'appointment_id' => (int)$row['id'],
                    'patient_id' => $row['patient_id'],
                    'patient_name' => $pName,
                    'doctor_name' => $docName,
                    'slot' => $row['slot'],
                    'status' => $row['status']
                ]
            ]);
        } else {
            jsonResponse([
                'status' => 'success',
                'is_available' => true,
                'message' => "Token #{$token_number} is AVAILABLE to assign."
            ]);
        }
    }
}
else if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if ($action === 'book') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (!$data) {
            jsonResponse(['status' => 'error', 'message' => 'Invalid request payload.']);
        }

        // 1. Anti-Bot / Human Verification ("Not-AI Test")
        // If not_ai_verified is bypassed, validate answer
        $anti_bot_answer = isset($data['anti_bot_answer']) ? (int)$data['anti_bot_answer'] : null;
        $expected_answer = $_SESSION['not_ai_captcha_answer'] ?? null;
        
        // Only require anti-bot for non-admin patient booking or check if submitted
        if (isset($data['require_antibot']) && $data['require_antibot'] === true) {
            if ($expected_answer === null || $anti_bot_answer !== (int)$expected_answer) {
                jsonResponse([
                    'status' => 'error',
                    'code' => 'ANTIBOT_FAILED',
                    'message' => 'Anti-Bot Human Verification failed. Please solve the Not-AI math challenge correctly.'
                ]);
            }
        } else if (isset($data['anti_bot_answer'])) {
            if ($expected_answer === null || $anti_bot_answer !== (int)$expected_answer) {
                jsonResponse([
                    'status' => 'error',
                    'code' => 'ANTIBOT_FAILED',
                    'message' => 'Human Verification (Not-AI Test) failed. Please check your answer and try again.'
                ]);
            }
        }

        $doctor_id = trim($data['doctor_id'] ?? '');
        $date = trim($data['date'] ?? date('Y-m-d'));
        $slot = trim($data['slot'] ?? '');
        $type = trim($data['type'] ?? 'General Consultation');
        $symptoms = trim($data['symptoms'] ?? 'General consultation / checkup');
        $allergies = trim($data['allergies'] ?? 'None reported');

        if (empty($doctor_id)) {
            jsonResponse(['status' => 'error', 'message' => 'Please select a doctor for consultation.']);
        }
        if (empty($slot)) {
            jsonResponse(['status' => 'error', 'message' => 'Please select an available consultation time slot.']);
        }
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            jsonResponse(['status' => 'error', 'message' => 'Invalid date format.']);
        }

        // 2. Check if Slot is already booked (prevent double booking)
        $chkStmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND date = ? AND slot = ? AND status != 'Cancelled'");
        $chkStmt->bind_param("sss", $doctor_id, $date, $slot);
        $chkStmt->execute();
        if ($chkStmt->get_result()->num_rows > 0) {
            jsonResponse([
                'status' => 'error', 
                'message' => "The slot '{$slot}' on {$date} was just booked by another patient. Please choose another time slot."
            ]);
        }

        // 3. Patient Resolution (Existing vs New Patient Registration)
        $patient_id = trim($data['patient_id'] ?? '');
        if (preg_match('/(CP-\d{4}-\d+|PAT-\d+)/i', $patient_id, $m)) {
            $patient_id = strtoupper($m[1]);
        }
        $phone = trim($data['phone'] ?? '');
        $patient_name = '';

        if (empty($patient_id)) {
            // New patient registration required
            $first_name = trim($data['name'] ?? '');
            $surname = trim($data['surname'] ?? '');
            $father_name = trim($data['father_name'] ?? '');

            // Ensure names store ONLY pure letters and spaces
            $first_name = trim(preg_replace('/[^\p{L}\s\']/u', '', $first_name));
            $surname = trim(preg_replace('/[^\p{L}\s\']/u', '', $surname));
            $father_name = trim(preg_replace('/[^\p{L}\s\']/u', '', $father_name));

            $gender = trim($data['gender'] ?? 'Male');
            $age = trim($data['age'] ?? '');
            $blood_group = trim($data['blood_group'] ?? 'O+');
            $em_name = trim($data['emergency_contact_name'] ?? '');
            $em_phone = trim($data['emergency_contact_phone'] ?? '');
            $address = trim($data['address'] ?? '');

            if (empty($first_name)) {
                jsonResponse(['status' => 'error', 'message' => 'Patient First Name is required.']);
            }
            if (empty($phone)) {
                jsonResponse(['status' => 'error', 'message' => 'Patient Mobile Phone Number is required.']);
            }

            // Generate clean MRN
            $year = date('Y');
            $maxRes = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(id, '-', -1) AS UNSIGNED)) as max_num FROM patients WHERE id LIKE 'CP-$year-%'");
            $maxRow = $maxRes ? $maxRes->fetch_assoc() : null;
            $next_num = ($maxRow['max_num'] ?? 0) + 1;
            $patient_id = "CP-$year-" . str_pad($next_num, 3, '0', STR_PAD_LEFT);

            $demoParts = [];
            if ($age) $demoParts[] = "{$age} Y";
            if ($gender) $demoParts[] = $gender;
            if ($blood_group) $demoParts[] = $blood_group;
            if ($address) $demoParts[] = $address;
            $demoStr = implode(', ', $demoParts);

            $insPat = $conn->prepare("INSERT INTO patients (id, name, surname, father_name, phone, demographics, gender, blood_group, age, emergency_contact_name, emergency_contact_phone, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $insPat->bind_param("sssssssssssi", 
                $patient_id, 
                $first_name, 
                $surname, 
                $father_name, 
                $phone, 
                $demoStr, 
                $gender, 
                $blood_group, 
                $age, 
                $em_name, 
                $em_phone, 
                $hospital_id
            );
            if (!$insPat->execute()) {
                jsonResponse(['status' => 'error', 'message' => 'Failed to create patient record: ' . $conn->error]);
            }
            $patient_name = trim("$first_name $surname");
        } else {
            // Existing Patient - verify ID
            $pQuery = $conn->prepare("SELECT name, surname, phone, gender, age, blood_group FROM patients WHERE id = ?");
            $pQuery->bind_param("s", $patient_id);
            $pQuery->execute();
            $pRow = $pQuery->get_result()->fetch_assoc();
            if ($pRow) {
                $patient_name = trim(($pRow['name'] ?? '') . ' ' . ($pRow['surname'] ?? ''));
                if (empty($phone)) $phone = $pRow['phone'];
                $gender = $pRow['gender'] ?? 'Male';
                $age = $pRow['age'] ?? '';
                $blood_group = $pRow['blood_group'] ?? 'O+';
            } else {
                $patient_name = 'Patient ' . $patient_id;
                $gender = 'Male';
                $age = '';
                $blood_group = 'O+';
            }
        }

        // 4. Insert Appointment
        $status = 'Online-Booked';
        $booking_type = 'online';
        $stage = 0;
        
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, allergies, status, stage, hospital_id, booking_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssssssiis", $patient_id, $doctor_id, $type, $date, $slot, $symptoms, $allergies, $status, $stage, $hospital_id, $booking_type);

        if ($stmt->execute()) {
            $app_id = $stmt->insert_id;
            
            $booking_ref = 'BK-' . date('Ymd') . '-' . str_pad($app_id, 4, '0', STR_PAD_LEFT);
            
            // Update booking_ref
            $updRef = $conn->prepare("UPDATE appointments SET booking_ref = ? WHERE id = ?");
            $updRef->bind_param("si", $booking_ref, $app_id);
            $updRef->execute();

            // 5. Log Timeline Event
            $event = "Online appointment booked for $date, slot $slot. Booking Ref: $booking_ref. Status: Online-Booked. Token will be assigned at hospital.";
            $time_now = date('h:i A');
            $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $tStmt->bind_param("isss", $app_id, $patient_id, $time_now, $event);
            $tStmt->execute();

            // 6. Fetch Doctor details for receipt
            $dInfoStmt = $conn->prepare("
                SELECT d.name, GROUP_CONCAT(DISTINCT dep.name SEPARATOR ', ') as specialties
                FROM doctors d
                LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
                LEFT JOIN departments dep ON dc.department_id = dep.id
                WHERE d.id = ?
                GROUP BY d.id
            ");
            $dInfoStmt->bind_param("s", $doctor_id);
            $dInfoStmt->execute();
            $dInfo = $dInfoStmt->get_result()->fetch_assoc();
            $doctor_name = $dInfo['name'] ?? 'Consultant Doctor';
            $doctor_specialty = $dInfo['specialties'] ?? 'General Medicine';

            // Clear anti-bot session so each booking has a fresh test
            unset($_SESSION['not_ai_captcha_answer']);
            unset($_SESSION['not_ai_captcha_token']);

            jsonResponse([
                'status' => 'success',
                'message' => 'Appointment slot booked successfully!',
                'appointment' => [
                    'id' => $app_id,
                    'booking_ref' => $booking_ref,
                    'patient_id' => $patient_id,
                    'patient_name' => $patient_name,
                    'phone' => $phone,
                    'gender' => $gender,
                    'age' => $age,
                    'blood_group' => $blood_group,
                    'doctor_id' => $doctor_id,
                    'doctor_name' => $doctor_name,
                    'doctor_specialty' => $doctor_specialty,
                    'date' => $date,
                    'formatted_date' => date('D, M d, Y', strtotime($date)),
                    'slot' => $slot,
                    'type' => $type,
                    'status' => $status,
                    'booking_type' => $booking_type,
                    'symptoms' => $symptoms,
                    'instruction' => "Your time slot is confirmed. Token number will be assigned when you arrive at the hospital. Please arrive 15 minutes before your scheduled time ({$slot}).",
                    'arrive_by' => date('h:i A', strtotime($slot . ' -15 minutes'))
                ]
            ]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to book appointment: ' . $conn->error]);
        }
    }
    
    // Assign Token (Walk-in QR Scan for Online Booked)
    else if ($action === 'assign_token') {
        $data = json_decode(file_get_contents('php://input'), true);
        $appointment_id = (int)($data['appointment_id'] ?? 0);
        $staff_id = $_SESSION['staff_id'] ?? 0;
        $staff_name = $_SESSION['staff_name'] ?? 'Admin';
        
        if (!$appointment_id) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment ID is required.']);
        }
        
        // Get appointment details
        $stmt = $conn->prepare("SELECT a.*, p.name as patient_name, p.surname, d.name as doctor_name 
                                FROM appointments a 
                                LEFT JOIN patients p ON a.patient_id = p.id 
                                LEFT JOIN doctors d ON a.doctor_id = d.id 
                                WHERE a.id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $appt = $stmt->get_result()->fetch_assoc();
        
        if (!$appt) {
            jsonResponse(['status' => 'error', 'message' => 'Appointment not found.']);
        }
        
        // Custom token typed by staff is strictly REQUIRED! Never assign direct / automatic
        $custom_token = isset($data['token_number']) ? (int)$data['token_number'] : 0;
        if ($custom_token <= 0) {
            jsonResponse(['status' => 'error', 'message' => 'Token number is required. Please type the token number given to the patient.']);
        }
        $token_number = $custom_token;

        // Verify token is NOT already given to someone else for this doctor today
        $dupStmt = $conn->prepare("
            SELECT a.id, p.name, p.surname, d.name as doctor_name 
            FROM appointments a 
            LEFT JOIN patients p ON a.patient_id = p.id 
            LEFT JOIN doctors d ON a.doctor_id = d.id 
            WHERE a.date = ? AND a.doctor_id = ? AND a.token_number = ? AND a.status NOT IN ('Cancelled') AND a.id != ?
            LIMIT 1
        ");
        $dupStmt->bind_param("ssii", $appt['date'], $appt['doctor_id'], $token_number, $appointment_id);
        $dupStmt->execute();
        $dupRow = $dupStmt->get_result()->fetch_assoc();
        if ($dupRow) {
            $dupPat = trim(($dupRow['name'] ?? '') . ' ' . ($dupRow['surname'] ?? '')) ?: 'another patient';
            $dupDoc = $dupRow['doctor_name'] ? (stripos(trim($dupRow['doctor_name']), 'Dr.') === 0 ? trim($dupRow['doctor_name']) : ('Dr. ' . trim($dupRow['doctor_name']))) : 'Doctor';
            jsonResponse([
                'status' => 'error', 
                'message' => "Token #{$token_number} is ALREADY ASSIGNED to {$dupPat} for {$dupDoc} today. Please type a different token number."
            ]);
        }
        
        // Get total slots for this doctor today
        $totalStmt = $conn->prepare("SELECT COUNT(*) as total FROM appointments WHERE doctor_id = ? AND date = ? AND status != 'Cancelled'");
        $totalStmt->bind_param("ss", $appt['doctor_id'], $appt['date']);
        $totalStmt->execute();
        $totalRow = $totalStmt->get_result()->fetch_assoc();
        $total_for_day = max((int)($totalRow['total'] ?? 0), $token_number);
        
        $token_str = str_pad($token_number, 2, '0', STR_PAD_LEFT);
        $token_display = "TOKEN #{$token_str}";
        
        // Update appointment: assign token, change status to Checked-In
        $newStatus = 'Checked-In';
        $newStage = 1;
        $updStmt = $conn->prepare("UPDATE appointments SET status = ?, stage = ?, token_number = ?, token_assigned_at = NOW(), token_assigned_by = ? WHERE id = ?");
        $updStmt->bind_param("siiii", $newStatus, $newStage, $token_number, $staff_id, $appointment_id);
        $updStmt->execute();
        
        // Timeline event
        $time_now = date('h:i A');
        $event = "Patient checked in at hospital. Token #{$token_str} assigned by {$staff_name}.";
        $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
        $tStmt->bind_param("isss", $appointment_id, $appt['patient_id'], $time_now, $event);
        $tStmt->execute();
        
        jsonResponse([
            'status' => 'success',
            'message' => "Token #{$token_str} assigned successfully!",
            'data' => [
                'appointment_id' => $appointment_id,
                'token_number' => $token_number,
                'token_str' => $token_str,
                'token_display' => $token_display,
                'patient_name' => trim(($appt['patient_name'] ?? '') . ' ' . ($appt['surname'] ?? '')),
                'doctor_name' => $appt['doctor_name'],
                'slot' => $appt['slot'],
                'status' => $newStatus
            ]
        ]);
    }
    
    // Create Walk-in + Assign Token
    else if ($action === 'create_walkin') {
        $data = json_decode(file_get_contents('php://input'), true);
        $patient_id = trim($data['patient_id'] ?? '');
        $doctor_id = trim($data['doctor_id'] ?? '');
        $type = trim($data['type'] ?? 'General Consultation');
        $symptoms = trim($data['symptoms'] ?? 'Walk-in consultation');
        
        // Validate
        if (empty($patient_id) || empty($doctor_id)) {
            jsonResponse(['status' => 'error', 'message' => 'Patient ID and Doctor ID are required.']);
        }
        
        $today = date('Y-m-d');
        $time_now_slot = date('h:i A');
        
        // Custom token typed by staff is strictly REQUIRED! Never assign direct / automatic
        $custom_token = isset($data['token_number']) ? (int)$data['token_number'] : 0;
        if ($custom_token <= 0) {
            jsonResponse(['status' => 'error', 'message' => 'Token number is required. Please type the token number given to the patient.']);
        }
        $token_number = $custom_token;

        // Verify token is NOT already given to someone else for this doctor today
        $dupStmt = $conn->prepare("
            SELECT a.id, p.name, p.surname, d.name as doctor_name 
            FROM appointments a 
            LEFT JOIN patients p ON a.patient_id = p.id 
            LEFT JOIN doctors d ON a.doctor_id = d.id 
            WHERE a.date = ? AND a.doctor_id = ? AND a.token_number = ? AND a.status NOT IN ('Cancelled')
            LIMIT 1
        ");
        $dupStmt->bind_param("ssi", $today, $doctor_id, $token_number);
        $dupStmt->execute();
        $dupRow = $dupStmt->get_result()->fetch_assoc();
        if ($dupRow) {
            $dupPat = trim(($dupRow['name'] ?? '') . ' ' . ($dupRow['surname'] ?? '')) ?: 'another patient';
            $dupDoc = $dupRow['doctor_name'] ? (stripos(trim($dupRow['doctor_name']), 'Dr.') === 0 ? trim($dupRow['doctor_name']) : ('Dr. ' . trim($dupRow['doctor_name']))) : 'Doctor';
            jsonResponse([
                'status' => 'error', 
                'message' => "Token #{$token_number} is ALREADY ASSIGNED to {$dupPat} for {$dupDoc} today. Please type a different token number."
            ]);
        }
        
        $token_str = str_pad($token_number, 2, '0', STR_PAD_LEFT);
        $booking_ref = 'WK-' . date('Ymd') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        // Insert appointment as Checked-In with token
        $status = 'Checked-In';
        $stage = 1;
        $booking_type = 'walk-in';
        $staff_id = $_SESSION['staff_id'] ?? 0;
        
        $stmt = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, allergies, status, stage, hospital_id, booking_type, booking_ref, token_number, token_assigned_at, token_assigned_by) VALUES (?, ?, ?, ?, ?, ?, 'None reported', ?, ?, ?, ?, ?, ?, NOW(), ?)");
        $stmt->bind_param("sssssssiissii", $patient_id, $doctor_id, $type, $today, $time_now_slot, $symptoms, $status, $stage, $hospital_id, $booking_type, $booking_ref, $token_number, $staff_id);
        $stmt->execute();
        $app_id = $stmt->insert_id;
        
        // Timeline
        $event = "Walk-in patient checked in. Token #{$token_str} assigned. Booking Ref: {$booking_ref}.";
        $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
        $tStmt->bind_param("isss", $app_id, $patient_id, $time_now_slot, $event);
        $tStmt->execute();
        
        // Get doctor info for response
        $dStmt = $conn->prepare("SELECT name FROM doctors WHERE id = ?");
        $dStmt->bind_param("s", $doctor_id);
        $dStmt->execute();
        $dRow = $dStmt->get_result()->fetch_assoc();
        
        jsonResponse([
            'status' => 'success',
            'message' => "Walk-in appointment created. Token #{$token_str} assigned!",
            'data' => [
                'appointment_id' => $app_id,
                'token_number' => $token_number,
                'token_str' => $token_str,
                'booking_ref' => $booking_ref,
                'doctor_name' => $dRow['name'] ?? 'Doctor',
                'status' => $status
            ]
        ]);
    }
}
?>
