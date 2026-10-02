<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$hospital_id = !empty($_SESSION['hospital_id']) ? (int)$_SESSION['hospital_id'] : 1;
$action = $_GET['action'] ?? 'get_overview';

header('Content-Type: application/json');

// -------------------------------------------------------------
// ACTION: get_overview (KPI Stats, Top Numbers, Recent Feeds)
// -------------------------------------------------------------
if ($action === 'get_overview') {
    try {
        // 1. Total Patients
        $resPat = $conn->query("SELECT COUNT(*) as c FROM patients WHERE (hospital_id = $hospital_id OR hospital_id IS NULL)");
        $totalPatients = $resPat ? (int)$resPat->fetch_assoc()['c'] : 0;

        // 2. Queue (Stages 1 to 4)
        $resQueue = $conn->query("
            SELECT COUNT(*) as c 
            FROM appointments 
            WHERE stage BETWEEN 1 AND 4 
              AND status != 'Pre-Booked' 
              AND status NOT IN ('Cancelled', 'Discharged (Normal Medicine)', 'Discharged from Bed')
              AND (hospital_id = $hospital_id OR hospital_id IS NULL)
        ");
        $totalQueue = $resQueue ? (int)$resQueue->fetch_assoc()['c'] : 0;

        // Emergency Cases in Queue
        $resEmerg = $conn->query("
            SELECT COUNT(*) as c 
            FROM appointments 
            WHERE stage BETWEEN 1 AND 4 
              AND (type = 'Emergency Case' OR type = 'Emergency')
              AND status NOT IN ('Cancelled', 'Discharged (Normal Medicine)', 'Discharged from Bed')
              AND (hospital_id = $hospital_id OR hospital_id IS NULL)
        ");
        $totalEmergency = $resEmerg ? (int)$resEmerg->fetch_assoc()['c'] : 0;

        // 3. Doctors count
        $resDoc = $conn->query("SELECT COUNT(*) as c FROM doctors WHERE (hospital_id = $hospital_id OR hospital_id IS NULL)");
        $totalDoctors = $resDoc ? (int)$resDoc->fetch_assoc()['c'] : 0;

        // 4. Beds (Available vs Total)
        $resBeds = $conn->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) as available
            FROM beds 
            WHERE (hospital_id = $hospital_id OR hospital_id IS NULL)
        ");
        $bedRow = $resBeds ? $resBeds->fetch_assoc() : ['total' => 0, 'available' => 0];
        $totalBeds = (int)($bedRow['total'] ?? 0);
        $availableBeds = (int)($bedRow['available'] ?? 0);

        // 5. Today's Appointments
        $today = date('Y-m-d');
        $resTodayApp = $conn->query("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN stage >= 5 OR status LIKE '%Discharged%' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled,
                SUM(CASE WHEN stage BETWEEN 1 AND 4 AND status NOT LIKE '%Discharged%' AND status != 'Cancelled' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'Pre-Booked' OR stage = 0 THEN 1 ELSE 0 END) as scheduled
            FROM appointments 
            WHERE date = '$today' AND (hospital_id = $hospital_id OR hospital_id IS NULL)
        ");
        $todayAppRow = $resTodayApp ? $resTodayApp->fetch_assoc() : [];
        $todayAppointments = [
            'total' => (int)($todayAppRow['total'] ?? 0),
            'completed' => (int)($todayAppRow['completed'] ?? 0),
            'cancelled' => (int)($todayAppRow['cancelled'] ?? 0),
            'in_progress' => (int)($todayAppRow['in_progress'] ?? 0),
            'scheduled' => (int)($todayAppRow['scheduled'] ?? 0)
        ];

        // 6. Staff Attendance Today
        $resStaff = $conn->query("SELECT COUNT(*) as c FROM staff WHERE status = 'Active' AND (hospital_id = $hospital_id OR hospital_id IS NULL)");
        $totalStaff = $resStaff ? (int)$resStaff->fetch_assoc()['c'] : 0;

        $resStaffAtt = $conn->query("
            SELECT 
                SUM(CASE WHEN a.status = 'Present' THEN 1 ELSE 0 END) as present,
                SUM(CASE WHEN a.status = 'Late' THEN 1 ELSE 0 END) as late,
                SUM(CASE WHEN a.status = 'Half Day' THEN 1 ELSE 0 END) as half_day,
                SUM(CASE WHEN a.status = 'Absent' THEN 1 ELSE 0 END) as absent,
                SUM(CASE WHEN a.status = 'On Leave' THEN 1 ELSE 0 END) as on_leave
            FROM staff_attendance a
            JOIN staff s ON a.staff_id = s.id
            WHERE a.date = '$today' AND s.status = 'Active' AND (s.hospital_id = $hospital_id OR s.hospital_id IS NULL)
        ");
        $attRow = $resStaffAtt ? $resStaffAtt->fetch_assoc() : [];
        $present = (int)($attRow['present'] ?? 0);
        $late = (int)($attRow['late'] ?? 0);
        $halfDay = (int)($attRow['half_day'] ?? 0);
        $absent = (int)($attRow['absent'] ?? 0);
        $onLeave = (int)($attRow['on_leave'] ?? 0);
        $onDuty = $present + $late;
        $rate = $totalStaff > 0 ? round(($onDuty / $totalStaff) * 100) : 100;

        $staffSummary = [
            'total' => $totalStaff,
            'on_duty' => $onDuty,
            'present' => $present,
            'late' => $late,
            'half_day' => $halfDay,
            'absent' => $absent + $onLeave,
            'rate' => $rate
        ];

        echo json_encode([
            'status' => 'success',
            'kpis' => [
                'total_patients' => $totalPatients,
                'live_queue' => $totalQueue,
                'emergency_cases' => $totalEmergency,
                'total_doctors' => $totalDoctors,
                'total_beds' => $totalBeds,
                'available_beds' => $availableBeds,
                'today_appointments' => $todayAppointments,
                'staff' => $staffSummary
            ]
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// ACTION: get_charts (Doctor & Timeline filterable Analytics)
// -------------------------------------------------------------
if ($action === 'get_charts') {
    try {
        $doc_id = $_GET['doctor_id'] ?? 'all';
        $timeline = $_GET['timeline'] ?? '7days'; // 7days, 14days, 30days, year

        $days = 7;
        if ($timeline === '14days') $days = 14;
        elseif ($timeline === '30days') $days = 30;
        elseif ($timeline === 'year') $days = 365;

        // Build timeline date slots
        $dates = [];
        if ($timeline === 'year') {
            // Group by Month (12 months)
            for ($i = 11; $i >= 0; $i--) {
                $m = date('Y-m', strtotime("-$i months"));
                $dates[$m] = [
                    'key' => $m,
                    'label' => date('M Y', strtotime($m . '-01')),
                    'total' => 0,
                    'completed' => 0,
                    'waiting' => 0,
                    'cancelled' => 0
                ];
            }
            $startDate = array_key_first($dates) . '-01';
            $endDate = date('Y-m-t'); // end of current month
        } else {
            // Daily grouping
            for ($i = $days - 1; $i >= 0; $i--) {
                $d = date('Y-m-d', strtotime("-$i days"));
                $dates[$d] = [
                    'key' => $d,
                    'label' => date('M d', strtotime($d)),
                    'day' => date('D', strtotime($d)),
                    'total' => 0,
                    'completed' => 0,
                    'waiting' => 0,
                    'cancelled' => 0
                ];
            }
            $startDate = array_key_first($dates);
            $endDate = array_key_last($dates);
        }

        // Fetch appointments in range
        $where = ["(a.hospital_id = ? OR a.hospital_id IS NULL)", "a.date BETWEEN ? AND ?"];
        $params = [$hospital_id, $startDate, $endDate];
        $types = "iss";

        if (!empty($doc_id) && $doc_id !== 'all') {
            $where[] = "a.doctor_id = ?";
            $params[] = $doc_id;
            $types .= "s";
        }

        $whereClause = implode(" AND ", $where);
        $query = "
            SELECT a.date, a.status, a.stage, a.type
            FROM appointments a
            WHERE $whereClause
            ORDER BY a.date ASC
        ";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $res = $stmt->get_result();

        $statusCounts = [
            'Waiting' => 0,
            'In Consultation' => 0,
            'Completed' => 0,
            'Pre-Booked' => 0,
            'Cancelled' => 0
        ];

        while ($row = $res->fetch_assoc()) {
            $d = $row['date'];
            $key = ($timeline === 'year') ? substr($d, 0, 7) : $d;

            $st = $row['status'];
            $stage = (int)$row['stage'];

            $isCompleted = ($stage >= 5 || strpos($st, 'Discharged') !== false);
            $isCancelled = ($st === 'Cancelled');
            $isConsulting = ($stage === 4 || strpos($st, 'Consulting') !== false);
            $isPreBooked = ($st === 'Pre-Booked');

            if (isset($dates[$key])) {
                $dates[$key]['total']++;
                if ($isCompleted) {
                    $dates[$key]['completed']++;
                } elseif ($isCancelled) {
                    $dates[$key]['cancelled']++;
                } else {
                    $dates[$key]['waiting']++;
                }
            }

            // Global Status Breakdown for the selected filter
            if ($isCancelled) {
                $statusCounts['Cancelled']++;
            } elseif ($isCompleted) {
                $statusCounts['Completed']++;
            } elseif ($isConsulting) {
                $statusCounts['In Consultation']++;
            } elseif ($isPreBooked) {
                $statusCounts['Pre-Booked']++;
            } else {
                $statusCounts['Waiting']++;
            }
        }

        // Department Distribution for the selected filter
        $deptQuery = "
            SELECT dep.name as dept_name, COUNT(a.id) as appt_count
            FROM departments dep
            LEFT JOIN doctor_categories dc ON dep.id = dc.department_id
            LEFT JOIN appointments a ON dc.doctor_id = a.doctor_id 
                 AND (a.hospital_id = ? OR a.hospital_id IS NULL)
                 AND a.date BETWEEN ? AND ?
                 " . (!empty($doc_id) && $doc_id !== 'all' ? "AND a.doctor_id = ?" : "") . "
            GROUP BY dep.id, dep.name
            ORDER BY appt_count DESC, dep.name ASC
        ";
        $deptStmt = $conn->prepare($deptQuery);
        if (!empty($doc_id) && $doc_id !== 'all') {
            $deptStmt->bind_param("isss", $hospital_id, $startDate, $endDate, $doc_id);
        } else {
            $deptStmt->bind_param("iss", $hospital_id, $startDate, $endDate);
        }
        $deptStmt->execute();
        $deptRes = $deptStmt->get_result();
        $deptDistribution = [];
        while ($dRow = $deptRes->fetch_assoc()) {
            $deptDistribution[] = [
                'department' => $dRow['dept_name'],
                'count' => (int)$dRow['appt_count']
            ];
        }

        // Summary metrics for this graph selection
        $timelineArray = array_values($dates);
        $totalInPeriod = array_sum(array_column($timelineArray, 'total'));
        $completedInPeriod = array_sum(array_column($timelineArray, 'completed'));
        $completionRate = $totalInPeriod > 0 ? round(($completedInPeriod / $totalInPeriod) * 100) : 0;
        
        $peakDay = null;
        $maxVal = -1;
        foreach ($timelineArray as $slot) {
            if ($slot['total'] > $maxVal) {
                $maxVal = $slot['total'];
                $peakDay = $slot['label'] . ($slot['total'] > 0 ? " ({$slot['total']})" : "");
            }
        }

        echo json_encode([
            'status' => 'success',
            'timeline' => $timelineArray,
            'summary' => [
                'total' => $totalInPeriod,
                'completed' => $completedInPeriod,
                'completion_rate' => $completionRate,
                'peak_day' => $peakDay ?? 'N/A',
                'avg_per_day' => count($timelineArray) > 0 ? round($totalInPeriod / count($timelineArray), 1) : 0
            ],
            'status_distribution' => $statusCounts,
            'department_distribution' => $deptDistribution
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

// -------------------------------------------------------------
// ACTION: get_recent_data (Categorized Cards & Lists)
// -------------------------------------------------------------
if ($action === 'get_recent_data') {
    try {
        // 1. Doctors List with specialization and today's appointment count
        $docQuery = "
            SELECT d.id, d.name, d.phone,
                   GROUP_CONCAT(DISTINCT dep.name SEPARATOR ', ') as specialties,
                   (SELECT COUNT(*) FROM appointments a WHERE a.doctor_id = d.id AND a.date = CURDATE()) as today_appointments
            FROM doctors d
            LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
            LEFT JOIN departments dep ON dc.department_id = dep.id
            WHERE (d.hospital_id = ? OR d.hospital_id IS NULL)
            GROUP BY d.id
            ORDER BY d.name ASC
        ";
        $stmtDoc = $conn->prepare($docQuery);
        $stmtDoc->bind_param("i", $hospital_id);
        $stmtDoc->execute();
        $resDoc = $stmtDoc->get_result();
        $doctorsList = [];
        while ($d = $resDoc->fetch_assoc()) {
            $doctorsList[] = $d;
        }

        // 2. Recent Registered Patients (last 5)
        $patQuery = "
            SELECT id, name, surname, phone, blood_group, gender, age, 
                   DATE_FORMAT(created_at, '%b %d, %Y') as reg_date
            FROM patients 
            WHERE (hospital_id = ? OR hospital_id IS NULL)
            ORDER BY created_at DESC 
            LIMIT 5
        ";
        $stmtPat = $conn->prepare($patQuery);
        $stmtPat->bind_param("i", $hospital_id);
        $stmtPat->execute();
        $resPat = $stmtPat->get_result();
        $recentPatients = [];
        while ($p = $resPat->fetch_assoc()) {
            $recentPatients[] = $p;
        }

        // 3. Today's Appointments List (first 6)
        $todayQuery = "
            SELECT a.id, a.date, a.slot, a.status, a.stage, a.type, a.symptoms,
                   p.id as patient_id, p.name as patient_name, p.surname as patient_surname, p.blood_group,
                   d.name as doctor_name
            FROM appointments a
            JOIN patients p ON a.patient_id = p.id
            LEFT JOIN doctors d ON a.doctor_id = d.id
            WHERE a.date = CURDATE() AND (a.hospital_id = ? OR a.hospital_id IS NULL)
            ORDER BY a.slot ASC, a.created_at ASC
            LIMIT 6
        ";
        $stmtToday = $conn->prepare($todayQuery);
        $stmtToday->bind_param("i", $hospital_id);
        $stmtToday->execute();
        $resToday = $stmtToday->get_result();
        $todayAppts = [];
        $tIdx = 1;
        while ($ap = $resToday->fetch_assoc()) {
            $ap['token_no'] = $tIdx++;
            $ap['appointment_code'] = sprintf("APP-%04d", (int)$ap['id']);
            $todayAppts[] = $ap;
        }

        echo json_encode([
            'status' => 'success',
            'doctors' => $doctorsList,
            'recent_patients' => $recentPatients,
            'today_appointments' => $todayAppts
        ]);
        exit;
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        exit;
    }
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
