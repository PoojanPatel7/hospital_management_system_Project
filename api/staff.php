<?php
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['hospital_id'])) {
    jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
}

$hospitalId = (int)$_SESSION['hospital_id'];
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Helper: Calculate working hours between 2 times
function calculateWorkingHours($in, $out) {
    if (!$in || !$out) return null;
    $t1 = strtotime("2000-01-01 $in");
    $t2 = strtotime("2000-01-01 $out");
    if ($t2 < $t1) {
        // Crossed midnight
        $t2 = strtotime("2000-01-02 $out");
    }
    $diffSec = $t2 - $t1;
    return round($diffSec / 3600, 2);
}

// -------------------------------------------------------------
// ACTION: get_staff (List staff with today's / selected date attendance)
// -------------------------------------------------------------
if ($action === 'get_staff') {
    $date = trim($_GET['date'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    $dept = trim($_GET['department'] ?? '');
    $role = trim($_GET['role'] ?? '');
    $shift = trim($_GET['shift'] ?? '');
    $status = trim($_GET['status'] ?? '');
    $attStatus = trim($_GET['att_status'] ?? '');
    $search = trim($_GET['search'] ?? '');

    $sql = "SELECT s.*, 
                   a.id AS attendance_id,
                   a.status AS att_status,
                   a.check_in_time,
                   a.check_out_time,
                   a.working_hours,
                   a.notes AS att_notes,
                   a.marked_by
            FROM staff s
            LEFT JOIN staff_attendance a ON s.id = a.staff_id AND a.date = ?
            WHERE s.hospital_id = ?";

    $params = [$date, $hospitalId];
    $types = "si";

    if ($dept !== '' && $dept !== 'All') {
        $sql .= " AND s.department = ?";
        $params[] = $dept;
        $types .= "s";
    }

    if ($role !== '' && $role !== 'All') {
        $sql .= " AND s.role = ?";
        $params[] = $role;
        $types .= "s";
    }

    if ($shift !== '' && $shift !== 'All') {
        $sql .= " AND s.shift LIKE ?";
        $params[] = "%$shift%";
        $types .= "s";
    }

    if ($status !== '' && $status !== 'All') {
        $sql .= " AND s.status = ?";
        $params[] = $status;
        $types .= "s";
    }

    if ($attStatus !== '' && $attStatus !== 'All') {
        if ($attStatus === 'Unmarked') {
            $sql .= " AND a.status IS NULL";
        } else {
            $sql .= " AND a.status = ?";
            $params[] = $attStatus;
            $types .= "s";
        }
    }

    if ($search !== '') {
        $sql .= " AND (s.first_name LIKE ? OR s.last_name LIKE ? OR s.staff_code LIKE ? OR s.phone LIKE ? OR s.email LIKE ? OR s.role LIKE ?)";
        $term = "%$search%";
        for ($i = 0; $i < 6; $i++) {
            $params[] = $term;
            $types .= "s";
        }
    }

    $sql .= " ORDER BY s.status ASC, s.department ASC, s.first_name ASC";

    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();
        $staffList = [];
        while ($row = $res->fetch_assoc()) {
            // Remove sensitive password hash
            unset($row['password']);
            $staffList[] = $row;
        }
        jsonResponse([
            'status' => 'success',
            'date' => $date,
            'count' => count($staffList),
            'data' => $staffList
        ]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Query error: ' . $conn->error]);
    }
}

// -------------------------------------------------------------
// ACTION: get_staff_details (Full staff profile with monthly analytics)
// -------------------------------------------------------------
if ($action === 'get_staff_details') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        jsonResponse(['status' => 'error', 'message' => 'Staff ID is required.']);
    }

    $stmt = $conn->prepare("SELECT * FROM staff WHERE id = ? AND hospital_id = ?");
    $stmt->bind_param("ii", $id, $hospitalId);
    $stmt->execute();
    $staff = $stmt->get_result()->fetch_assoc();

    if (!$staff) {
        jsonResponse(['status' => 'error', 'message' => 'Staff member not found.']);
    }

    unset($staff['password']);

    // Monthly stats (current month)
    $currMonth = date('Y-m');
    $statStmt = $conn->prepare("SELECT 
        COUNT(*) AS total_days,
        SUM(CASE WHEN status = 'Present' THEN 1 ELSE 0 END) AS present_count,
        SUM(CASE WHEN status = 'Late' THEN 1 ELSE 0 END) AS late_count,
        SUM(CASE WHEN status = 'Half Day' THEN 1 ELSE 0 END) AS half_day_count,
        SUM(CASE WHEN status = 'Absent' THEN 1 ELSE 0 END) AS absent_count,
        SUM(CASE WHEN status = 'On Leave' THEN 1 ELSE 0 END) AS leave_count,
        COALESCE(SUM(working_hours), 0) AS total_hours
        FROM staff_attendance 
        WHERE staff_id = ? AND hospital_id = ? AND date LIKE ?");
    $monthPattern = "$currMonth-%";
    $statStmt->bind_param("iis", $id, $hospitalId, $monthPattern);
    $statStmt->execute();
    $stats = $statStmt->get_result()->fetch_assoc();

    $totalDays = (int)($stats['total_days'] ?? 0);
    $present = (int)($stats['present_count'] ?? 0);
    $late = (int)($stats['late_count'] ?? 0);
    $half = (int)($stats['half_day_count'] ?? 0);
    $effectiveDays = $present + $late + ($half * 0.5);
    $attendanceRate = $totalDays > 0 ? round(($effectiveDays / $totalDays) * 100, 1) : 100.0;

    $stats['effective_days'] = $effectiveDays;
    $stats['attendance_rate'] = $attendanceRate;

    // Recent 30 days attendance history
    $histStmt = $conn->prepare("SELECT * FROM staff_attendance WHERE staff_id = ? AND hospital_id = ? ORDER BY date DESC LIMIT 30");
    $histStmt->bind_param("ii", $id, $hospitalId);
    $histStmt->execute();
    $history = $histStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    jsonResponse([
        'status' => 'success',
        'data' => [
            'staff' => $staff,
            'stats' => $stats,
            'history' => $history
        ]
    ]);
}

// -------------------------------------------------------------
// ACTION: save_staff (Create or Update staff + optional user account)
// -------------------------------------------------------------
if ($action === 'save_staff') {
    $id = (int)($input['id'] ?? 0);
    $first_name = trim($input['first_name'] ?? '');
    $last_name = trim($input['last_name'] ?? '');
    $role = trim($input['role'] ?? '');
    $department = trim($input['department'] ?? 'General Medicine');
    $email = trim($input['email'] ?? '');
    $phone = trim($input['phone'] ?? '');
    $gender = trim($input['gender'] ?? 'Female');
    $date_of_birth = trim($input['date_of_birth'] ?? '') ?: null;
    $joining_date = trim($input['joining_date'] ?? '') ?: date('Y-m-d');
    $shift = trim($input['shift'] ?? 'Morning (08:00 - 16:00)');
    $status = trim($input['status'] ?? 'Active');
    $qualification = trim($input['qualification'] ?? '');
    $salary = (float)($input['salary'] ?? 0.0);
    $blood_group = trim($input['blood_group'] ?? '');
    $emergency_contact = trim($input['emergency_contact'] ?? '');
    $address = trim($input['address'] ?? '');
    $staff_code = trim($input['staff_code'] ?? '');

    // User account fields
    $is_user = !empty($input['is_user']) ? 1 : 0;
    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');

    if (!$first_name || !$last_name || !$role || !$department) {
        jsonResponse(['status' => 'error', 'message' => 'First Name, Last Name, Role, and Department are required.']);
    }

    // Check username uniqueness if provided and user account is enabled
    if ($is_user && $username !== '') {
        // Check hospital table
        $chkHosp = $conn->prepare("SELECT id FROM hospitals WHERE LOWER(username) = LOWER(?)");
        $chkHosp->bind_param("s", $username);
        $chkHosp->execute();
        if ($chkHosp->get_result()->fetch_assoc()) {
            jsonResponse(['status' => 'error', 'message' => "Username '$username' is already registered. Please choose another username."]);
        }

        // Check other staff
        $chkStaff = $conn->prepare("SELECT id FROM staff WHERE LOWER(username) = LOWER(?) AND id != ?");
        $chkStaff->bind_param("si", $username, $id);
        $chkStaff->execute();
        if ($chkStaff->get_result()->fetch_assoc()) {
            jsonResponse(['status' => 'error', 'message' => "Username '$username' is already taken by another staff member."]);
        }
    } else {
        $username = null;
    }

    if ($id > 0) {
        // Update existing staff
        $sql = "UPDATE staff SET 
                first_name = ?, last_name = ?, email = ?, phone = ?, role = ?, department = ?, 
                gender = ?, date_of_birth = ?, joining_date = ?, shift = ?, status = ?, 
                qualification = ?, salary = ?, blood_group = ?, emergency_contact = ?, 
                address = ?, is_user = ?, username = ?";
        
        $params = [
            $first_name, $last_name, $email, $phone, $role, $department,
            $gender, $date_of_birth, $joining_date, $shift, $status,
            $qualification, $salary, $blood_group, $emergency_contact,
            $address, $is_user, $username
        ];
        $types = "ssssssssssssdsssiss";

        if ($password !== '') {
            $sql .= ", password = ?";
            $params[] = password_hash($password, PASSWORD_DEFAULT);
            $types .= "s";
        }

        $sql .= " WHERE id = ? AND hospital_id = ?";
        $params[] = $id;
        $params[] = $hospitalId;
        $types .= "ii";

        $stmt = $conn->prepare($sql);
        if ($stmt) {
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            jsonResponse(['status' => 'success', 'message' => 'Staff record updated successfully.']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Update failed: ' . $conn->error]);
        }
    } else {
        // Create new staff
        if (!$staff_code) {
            // Auto generate staff code
            $lastCodeQuery = $conn->query("SELECT staff_code FROM staff WHERE hospital_id = $hospitalId ORDER BY id DESC LIMIT 1");
            $nextNum = 101;
            if ($lastCodeQuery && $lastRow = $lastCodeQuery->fetch_assoc()) {
                if (preg_match('/(\d+)/', $lastRow['staff_code'], $m)) {
                    $nextNum = (int)$m[1] + 1;
                }
            }
            $staff_code = 'STF-' . $nextNum;
        }

        $hashedPassword = ($is_user && $password !== '') ? password_hash($password, PASSWORD_DEFAULT) : null;

        $stmt = $conn->prepare("INSERT INTO staff (
            hospital_id, staff_code, first_name, last_name, email, phone, role, department, 
            gender, date_of_birth, joining_date, shift, status, qualification, salary, 
            blood_group, emergency_contact, address, is_user, username, password
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        if ($stmt) {
            $stmt->bind_param("isssssssssssssdsssiss", 
                $hospitalId, $staff_code, $first_name, $last_name, $email, $phone, $role, $department,
                $gender, $date_of_birth, $joining_date, $shift, $status, $qualification, $salary,
                $blood_group, $emergency_contact, $address, $is_user, $username, $hashedPassword
            );
            $stmt->execute();
            $newId = $stmt->insert_id;

            // Auto-mark default Present attendance for today
            $today = date('Y-m-d');
            $attStmt = $conn->prepare("INSERT IGNORE INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, working_hours, notes, marked_by) VALUES (?, ?, ?, 'Present', '08:00:00', 8.0, 'Initial check-in', 'Admin')");
            if ($attStmt) {
                $attStmt->bind_param("iis", $hospitalId, $newId, $today);
                $attStmt->execute();
            }

            jsonResponse(['status' => 'success', 'message' => "New staff member $first_name $last_name ($staff_code) registered successfully.", 'id' => $newId]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Registration failed: ' . $conn->error]);
        }
    }
}

// -------------------------------------------------------------
// ACTION: delete_staff
// -------------------------------------------------------------
if ($action === 'delete_staff') {
    $id = (int)($input['id'] ?? 0);
    if (!$id) {
        jsonResponse(['status' => 'error', 'message' => 'Staff ID is required.']);
    }

    // Delete attendance records first
    $delAtt = $conn->prepare("DELETE FROM staff_attendance WHERE staff_id = ? AND hospital_id = ?");
    $delAtt->bind_param("ii", $id, $hospitalId);
    $delAtt->execute();

    // Delete staff
    $delStaff = $conn->prepare("DELETE FROM staff WHERE id = ? AND hospital_id = ?");
    $delStaff->bind_param("ii", $id, $hospitalId);
    $delStaff->execute();

    if ($delStaff->affected_rows > 0) {
        jsonResponse(['status' => 'success', 'message' => 'Staff record and attendance history deleted successfully.']);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Staff member not found or already deleted.']);
    }
}

// -------------------------------------------------------------
// ACTION: mark_attendance (Individual Staff single-date attendance)
// -------------------------------------------------------------
if ($action === 'mark_attendance') {
    $staff_id = (int)($input['staff_id'] ?? 0);
    $date = trim($input['date'] ?? date('Y-m-d'));
    $status = trim($input['status'] ?? 'Present');
    $check_in_time = trim($input['check_in_time'] ?? '') ?: null;
    $check_out_time = trim($input['check_out_time'] ?? '') ?: null;
    $notes = trim($input['notes'] ?? '');
    $marked_by = $_SESSION['staff_name'] ?? 'Admin';

    if (!$staff_id || !$date) {
        jsonResponse(['status' => 'error', 'message' => 'Staff ID and date are required.']);
    }

    $validStatuses = ['Present', 'Absent', 'Late', 'Half Day', 'On Leave'];
    if (!in_array($status, $validStatuses)) {
        $status = 'Present';
    }

    // If times are blank and status is Present/Late/Half Day, apply realistic defaults
    if (!$check_in_time && ($status === 'Present' || $status === 'Late' || $status === 'Half Day')) {
        $check_in_time = date('H:i:s');
    }

    $working_hours = calculateWorkingHours($check_in_time, $check_out_time);
    if ($working_hours === null) {
        if ($status === 'Present') $working_hours = 8.0;
        else if ($status === 'Half Day') $working_hours = 4.0;
        else if ($status === 'Late') $working_hours = 7.5;
        else $working_hours = 0.0;
    }

    if ($status === 'Absent' || $status === 'On Leave') {
        $check_in_time = null;
        $check_out_time = null;
        $working_hours = 0.0;
    }

    $stmt = $conn->prepare("INSERT INTO staff_attendance (
        hospital_id, staff_id, date, status, check_in_time, check_out_time, working_hours, notes, marked_by
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        check_in_time = VALUES(check_in_time),
        check_out_time = VALUES(check_out_time),
        working_hours = VALUES(working_hours),
        notes = VALUES(notes),
        marked_by = VALUES(marked_by),
        updated_at = CURRENT_TIMESTAMP");

    if ($stmt) {
        $stmt->bind_param("iisssssss", 
            $hospitalId, $staff_id, $date, $status, $check_in_time, $check_out_time, $working_hours, $notes, $marked_by
        );
        $stmt->execute();

        jsonResponse([
            'status' => 'success',
            'message' => "Attendance marked as '$status' successfully.",
            'data' => [
                'staff_id' => $staff_id,
                'date' => $date,
                'status' => $status,
                'check_in_time' => $check_in_time,
                'check_out_time' => $check_out_time,
                'working_hours' => $working_hours,
                'notes' => $notes
            ]
        ]);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Failed to save attendance: ' . $conn->error]);
    }
}

// -------------------------------------------------------------
// ACTION: quick_mark_all (Mark all active staff in 1 click)
// -------------------------------------------------------------
if ($action === 'quick_mark_all') {
    $date = trim($input['date'] ?? date('Y-m-d'));
    $status = trim($input['status'] ?? 'Present');
    $marked_by = $_SESSION['staff_name'] ?? 'Admin';

    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    $stfQuery = $conn->query("SELECT id FROM staff WHERE hospital_id = $hospitalId AND status = 'Active'");
    if (!$stfQuery) {
        jsonResponse(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }

    $check_in = ($status === 'Present') ? '08:00:00' : null;
    $working_hours = ($status === 'Present') ? 8.0 : 0.0;
    $count = 0;

    $stmt = $conn->prepare("INSERT INTO staff_attendance (
        hospital_id, staff_id, date, status, check_in_time, check_out_time, working_hours, notes, marked_by
    ) VALUES (?, ?, ?, ?, ?, NULL, ?, 'Bulk Attendance Record', ?)
    ON DUPLICATE KEY UPDATE 
        status = VALUES(status),
        check_in_time = VALUES(check_in_time),
        working_hours = VALUES(working_hours),
        marked_by = VALUES(marked_by),
        updated_at = CURRENT_TIMESTAMP");

    while ($row = $stfQuery->fetch_assoc()) {
        $sid = (int)$row['id'];
        if ($stmt) {
            $stmt->bind_param("iisssds", $hospitalId, $sid, $date, $status, $check_in, $working_hours, $marked_by);
            $stmt->execute();
            $count++;
        }
    }

    jsonResponse([
        'status' => 'success',
        'message' => "Marked all $count active staff members as '$status' for $date.",
        'count' => $count
    ]);
}

// -------------------------------------------------------------
// ACTION: get_daily_summary (Aggregate statistics for specific date)
// -------------------------------------------------------------
if ($action === 'get_daily_summary') {
    $date = trim($_GET['date'] ?? date('Y-m-d'));
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $date = date('Y-m-d');
    }

    // Active staff count
    $stfCountRes = $conn->query("SELECT COUNT(*) AS total_active FROM staff WHERE hospital_id = $hospitalId AND status = 'Active'");
    $totalActive = (int)($stfCountRes ? $stfCountRes->fetch_assoc()['total_active'] : 0);

    // Attendance breakdown for the date
    $attStmt = $conn->prepare("SELECT 
        status, COUNT(*) as count 
        FROM staff_attendance 
        WHERE hospital_id = ? AND date = ? 
        GROUP BY status");
    $attStmt->bind_param("is", $hospitalId, $date);
    $attStmt->execute();
    $attRes = $attStmt->get_result();

    $counts = [
        'Present' => 0,
        'Absent' => 0,
        'Late' => 0,
        'Half Day' => 0,
        'On Leave' => 0
    ];

    while ($r = $attRes->fetch_assoc()) {
        if (isset($counts[$r['status']])) {
            $counts[$r['status']] = (int)$r['count'];
        }
    }

    $totalMarked = array_sum($counts);
    $unmarked = max(0, $totalActive - $totalMarked);

    $effectivePresent = $counts['Present'] + $counts['Late'] + ($counts['Half Day'] * 0.5);
    $rate = $totalActive > 0 ? round(($effectivePresent / $totalActive) * 100, 1) : 0.0;

    jsonResponse([
        'status' => 'success',
        'date' => $date,
        'summary' => [
            'total_active_staff' => $totalActive,
            'total_marked' => $totalMarked,
            'unmarked' => $unmarked,
            'present' => $counts['Present'],
            'absent' => $counts['Absent'],
            'late' => $counts['Late'],
            'half_day' => $counts['Half Day'],
            'on_leave' => $counts['On Leave'],
            'attendance_rate' => $rate
        ]
    ]);
}

// -------------------------------------------------------------
// ACTION: get_monthly_sheet (Day-by-day matrix for entire month)
// -------------------------------------------------------------
if ($action === 'get_monthly_sheet') {
    $month = trim($_GET['month'] ?? date('Y-m')); // Format: YYYY-MM
    if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
        $month = date('Y-m');
    }

    $year = (int)substr($month, 0, 4);
    $mon = (int)substr($month, 5, 2);
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $mon, $year);

    // List of active staff
    $stfStmt = $conn->prepare("SELECT id, staff_code, first_name, last_name, role, department, shift FROM staff WHERE hospital_id = ? AND status != 'Inactive' ORDER BY department ASC, first_name ASC");
    $stfStmt->bind_param("i", $hospitalId);
    $stfStmt->execute();
    $staffList = $stfStmt->get_result()->fetch_all(MYSQLI_ASSOC);

    // Fetch all attendance for this month
    $monthPattern = "$month-%";
    $attStmt = $conn->prepare("SELECT staff_id, date, status, check_in_time, check_out_time, working_hours FROM staff_attendance WHERE hospital_id = ? AND date LIKE ?");
    $attStmt->bind_param("is", $hospitalId, $monthPattern);
    $attStmt->execute();
    $attRes = $attStmt->get_result();

    $matrix = [];
    while ($row = $attRes->fetch_assoc()) {
        $sid = $row['staff_id'];
        $day = (int)substr($row['date'], 8, 2);
        if (!isset($matrix[$sid])) {
            $matrix[$sid] = [];
        }
        $matrix[$sid][$day] = [
            'status' => $row['status'],
            'in' => $row['check_in_time'],
            'out' => $row['check_out_time'],
            'hrs' => $row['working_hours']
        ];
    }

    // Build summaries per staff
    $staffSummaries = [];
    foreach ($staffList as &$s) {
        $sid = $s['id'];
        $p = 0; $a = 0; $l = 0; $h = 0; $v = 0; $totalHrs = 0;
        for ($d = 1; $d <= $daysInMonth; $d++) {
            if (isset($matrix[$sid][$d])) {
                $st = $matrix[$sid][$d]['status'];
                if ($st === 'Present') $p++;
                else if ($st === 'Late') $l++;
                else if ($st === 'Half Day') $h++;
                else if ($st === 'Absent') $a++;
                else if ($st === 'On Leave') $v++;

                $totalHrs += (float)($matrix[$sid][$d]['hrs'] ?? 0);
            }
        }
        $totalRecorded = $p + $l + $h + $a + $v;
        $effective = $p + $l + ($h * 0.5);
        $rate = $totalRecorded > 0 ? round(($effective / $totalRecorded) * 100, 1) : 0.0;

        $s['summary'] = [
            'present' => $p,
            'late' => $l,
            'half_day' => $h,
            'absent' => $a,
            'leave' => $v,
            'total_hours' => round($totalHrs, 1),
            'attendance_rate' => $rate
        ];
    }

    jsonResponse([
        'status' => 'success',
        'month' => $month,
        'days_in_month' => $daysInMonth,
        'staff' => $staffList,
        'matrix' => $matrix
    ]);
}

jsonResponse(['status' => 'error', 'message' => 'Invalid action specified.']);
