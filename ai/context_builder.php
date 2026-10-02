<?php
// c:\xampp\htdocs\Hospital Management System\ai\context_builder.php

function buildPageContext($conn, $hospitalId, $currentPage) {
    $stats = "";
    $focus_tables = [];

    // Extract base page name without extension or query params
    $page = basename(parse_url($currentPage, PHP_URL_PATH), '.php');

    switch ($page) {
        case 'dashboard':
            $focus_tables = ['patients', 'appointments', 'beds', 'staff_attendance'];
            // Today's patients
            $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE hospital_id = ? AND DATE(created_at) = CURDATE()");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $patientsToday = $stmt->get_result()->fetch_row()[0] ?? 0;

            // Today's appointments
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND DATE(appointment_date) = CURDATE()");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $appointmentsToday = $stmt->get_result()->fetch_row()[0] ?? 0;

            // Beds
            $stmt = $conn->prepare("SELECT SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as available, SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occupied FROM beds WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $bedRes = $stmt->get_result()->fetch_assoc();
            $bedsAvail = $bedRes['available'] ?? 0;
            $bedsOcc = $bedRes['occupied'] ?? 0;

            // Staff on duty
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT staff_id) FROM staff_attendance WHERE hospital_id = ? AND date = CURDATE() AND status = 'present'");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $staffOnDuty = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stats = "Dashboard Overview:\n- Today's new patients: $patientsToday\n- Today's appointments: $appointmentsToday\n- Beds: $bedsAvail available, $bedsOcc occupied\n- Staff on duty today: $staffOnDuty";
            break;

        case 'queue':
            $focus_tables = ['appointments', 'patients'];
            $stmt = $conn->prepare("SELECT stage, COUNT(*) as count FROM appointments WHERE hospital_id = ? AND DATE(appointment_date) = CURDATE() GROUP BY stage");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $res = $stmt->get_result();
            $stages = [];
            while ($row = $res->fetch_assoc()) {
                $stages[$row['stage']] = $row['count'];
            }
            $stageStr = "";
            for ($i = 0; $i <= 4; $i++) {
                $stageStr .= "Stage $i: " . ($stages[$i] ?? 0) . ", ";
            }
            $stageStr = rtrim($stageStr, ", ");

            $stmt = $conn->prepare("SELECT patient_id, status FROM appointments WHERE hospital_id = ? AND DATE(appointment_date) = CURDATE() AND status = 'consulting'");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $consulting = $stmt->get_result()->num_rows;

            $stats = "Queue Status (Today):\n- Appointments by stage: $stageStr\n- Currently consulting: $consulting";
            break;

        case 'patients':
            $focus_tables = ['patients'];
            $stmt = $conn->prepare("SELECT COUNT(*) as total, SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today, SUM(CASE WHEN gender = 'Male' THEN 1 ELSE 0 END) as male, SUM(CASE WHEN gender = 'Female' THEN 1 ELSE 0 END) as female, SUM(CASE WHEN gender = 'Other' THEN 1 ELSE 0 END) as other FROM patients WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
            
            $stats = "Patients Overview:\n- Total: {$row['total']}\n- Registered Today: {$row['today']}\n- Gender Dist: Male({$row['male']}), Female({$row['female']}), Other({$row['other']})";
            break;

        case 'appointments':
            $focus_tables = ['appointments'];
            $stmt = $conn->prepare("SELECT COUNT(*) as total_today, SUM(CASE WHEN appointment_date > CURDATE() THEN 1 ELSE 0 END) as upcoming FROM appointments WHERE hospital_id = ? AND (DATE(appointment_date) = CURDATE() OR appointment_date > CURDATE())");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM appointments WHERE hospital_id = ? GROUP BY status");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $res = $stmt->get_result();
            $statusStr = "";
            while ($r = $res->fetch_assoc()) {
                $statusStr .= "{$r['status']}({$r['count']}), ";
            }
            $statusStr = rtrim($statusStr, ", ");

            $stats = "Appointments Overview:\n- Today: {$row['total_today']}\n- Upcoming: {$row['upcoming']}\n- By Status: $statusStr";
            break;
            
        case 'beds':
            $focus_tables = ['beds'];
            $stmt = $conn->prepare("SELECT type, COUNT(*) as count FROM beds WHERE hospital_id = ? GROUP BY type");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $res = $stmt->get_result();
            $typeStr = "";
            while ($r = $res->fetch_assoc()) {
                $typeStr .= "{$r['type']}({$r['count']}), ";
            }
            $typeStr = rtrim($typeStr, ", ");

            $stmt = $conn->prepare("SELECT wing, COUNT(*) as count FROM beds WHERE hospital_id = ? GROUP BY wing");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $res = $stmt->get_result();
            $wingStr = "";
            while ($r = $res->fetch_assoc()) {
                $wingStr .= "{$r['wing']}({$r['count']}), ";
            }
            $wingStr = rtrim($wingStr, ", ");

            $stmt = $conn->prepare("SELECT SUM(CASE WHEN status = 'available' THEN 1 ELSE 0 END) as avail, SUM(CASE WHEN status = 'occupied' THEN 1 ELSE 0 END) as occ FROM beds WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            $stats = "Beds Overview:\n- Available: {$row['avail']}, Occupied: {$row['occ']}\n- By Type: $typeStr\n- By Wing: $wingStr";
            break;

        case 'staff':
            $focus_tables = ['staff', 'staff_attendance'];
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM staff WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $totalStaff = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stmt = $conn->prepare("SELECT status, COUNT(*) as count FROM staff_attendance WHERE hospital_id = ? AND date = CURDATE() GROUP BY status");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $res = $stmt->get_result();
            $attStr = "";
            while ($r = $res->fetch_assoc()) {
                $attStr .= "{$r['status']}({$r['count']}), ";
            }
            $attStr = rtrim($attStr, ", ");
            if (empty($attStr)) $attStr = "None recorded yet";

            $stmt = $conn->prepare("SELECT department, COUNT(*) as count FROM staff WHERE hospital_id = ? GROUP BY department");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $res = $stmt->get_result();
            $depStr = "";
            while ($r = $res->fetch_assoc()) {
                $depStr .= "{$r['department']}({$r['count']}), ";
            }
            $depStr = rtrim($depStr, ", ");

            $stats = "Staff Overview:\n- Total Staff: $totalStaff\n- Today's Attendance: $attStr\n- By Dept: $depStr";
            break;

        case 'doctors':
            $focus_tables = ['doctors'];
            $stmt = $conn->prepare("SELECT COUNT(*) FROM doctors WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $totalDocs = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stmt = $conn->prepare("SELECT department, COUNT(*) as count FROM doctors WHERE hospital_id = ? GROUP BY department");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $res = $stmt->get_result();
            $depStr = "";
            while ($r = $res->fetch_assoc()) {
                $depStr .= "{$r['department']}({$r['count']}), ";
            }
            $depStr = rtrim($depStr, ", ");

            $stats = "Doctors Overview:\n- Total: $totalDocs\n- By Dept: $depStr";
            break;

        case 'book':
            $focus_tables = ['doctors', 'appointments'];
            $stmt = $conn->prepare("SELECT COUNT(*) FROM doctors WHERE hospital_id = ?"); 
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $availDocs = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stats = "Booking Overview:\n- Total Doctors: $availDocs";
            break;

        case 'history':
            $focus_tables = ['appointments', 'prescriptions', 'diagnoses'];
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND appointment_date < CURDATE()");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $recentAppts = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stats = "History Overview:\n- Past Appointments: $recentAppts";
            break;

        default:
            $focus_tables = ['patients', 'appointments', 'beds'];
            $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $p = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $a = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stmt = $conn->prepare("SELECT COUNT(*) FROM beds WHERE hospital_id = ?");
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $b = $stmt->get_result()->fetch_row()[0] ?? 0;

            $stats = "General Overview:\n- Total Patients: $p\n- Total Appointments: $a\n- Total Beds: $b";
            break;
    }

    return [
        'stats' => $stats,
        'focus_tables' => $focus_tables
    ];
}

function getTodayStats($conn, $hospitalId) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM patients WHERE hospital_id = ? AND DATE(created_at) = CURDATE()");
    $stmt->bind_param("i", $hospitalId);
    $stmt->execute();
    $p = $stmt->get_result()->fetch_row()[0] ?? 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND DATE(appointment_date) = CURDATE()");
    $stmt->bind_param("i", $hospitalId);
    $stmt->execute();
    $a = $stmt->get_result()->fetch_row()[0] ?? 0;

    $stmt = $conn->prepare("SELECT COUNT(*) FROM beds WHERE hospital_id = ? AND status = 'available'");
    $stmt->bind_param("i", $hospitalId);
    $stmt->execute();
    $b = $stmt->get_result()->fetch_row()[0] ?? 0;

    $stmt = $conn->prepare("SELECT COUNT(DISTINCT staff_id) FROM staff_attendance WHERE hospital_id = ? AND date = CURDATE() AND status = 'present'");
    $stmt->bind_param("i", $hospitalId);
    $stmt->execute();
    $s = $stmt->get_result()->fetch_row()[0] ?? 0;

    return "Today: $p patients, $a appointments, $b beds available, $s staff on duty";
}
?>
