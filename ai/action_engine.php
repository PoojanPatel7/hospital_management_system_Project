<?php
// ai/action_engine.php
// Pro-grade Action Engine for Hospital Management System
// Validates intent, gathers missing details, asks back when in doubt, and generates real executable operations.

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/system_knowledge.php';

/**
 * Main dispatcher to analyze user request for action intent.
 * Returns:
 * - ['type' => 'clarification', 'question' => '...', 'suggestions' => [...]] if information is missing or ambiguous
 * - ['type' => 'action', 'plan' => [...]] if all required parameters are resolved and verified
 * - null if the request is not an operational write action (e.g. read query or general conversation)
 */
function analyzeActionIntent($conn, $hospitalId, $message) {
    $msg = trim($message);
    $lower = strtolower($msg);

    // -------------------------------------------------------------
    // 1. ATTENDANCE ACTIONS
    // -------------------------------------------------------------
    $attendanceKeywords = ['attendance', 'attdence', 'attendence', 'present', 'absent', 'half day', 'halfday', 'on leave', 'leave', 'late', 'haziri'];
    $isAttendanceIntent = false;
    foreach ($attendanceKeywords as $kw) {
        if (strpos($lower, $kw) !== false) {
            $isAttendanceIntent = true;
            break;
        }
    }

    if ($isAttendanceIntent) {
        // Read query filter: if user is asking "who is present", "list staff", "show absent", etc.
        $isReadQuery = false;
        $readVerbs = ['who is', 'who are', 'who was', 'list', 'show', 'display', 'how many', 'view', 'check', 'kaun', 'koun', 'kitne', 'tell me'];
        $writeVerbs = ['mark', 'set', 'update', 'put', 'record', 'karo', 'kare', 'banao'];
        $hasWriteVerb = false;
        foreach ($writeVerbs as $wv) {
            if (strpos($lower, $wv) !== false) {
                $hasWriteVerb = true;
                break;
            }
        }
        if (!$hasWriteVerb) {
            foreach ($readVerbs as $rv) {
                if (strpos($lower, $rv) !== false) {
                    $isReadQuery = true;
                    break;
                }
            }
        }
        if ($isReadQuery) {
            return null; // Let Phase 1 execute SQL SELECT for factual answering!
        }

        // A. Check for "Mark All Present" / Bulk Attendance
        $bulkPatterns = [
            'all present', 'mark all', 'all staff present', 'everyone present',
            'sabhi present', 'sabko present', 'sab present', 'mark everyone',
            'all employee', 'all active staff', 'mark all as present'
        ];
        foreach ($bulkPatterns as $bp) {
            if (strpos($lower, $bp) !== false) {
                return buildBulkAttendanceAction($conn, $hospitalId, $lower);
            }
        }

        // B. Check for individual staff attendance
        // Check if a status is mentioned
        $targetStatus = 'Present';
        if (strpos($lower, 'absent') !== false) $targetStatus = 'Absent';
        elseif (strpos($lower, 'half day') !== false || strpos($lower, 'halfday') !== false) $targetStatus = 'Half Day';
        elseif (strpos($lower, 'leave') !== false) $targetStatus = 'On Leave';
        elseif (strpos($lower, 'late') !== false) $targetStatus = 'Late';

        // Extract possible staff name from message
        $staffResolved = resolveStaffFromMessage($conn, $hospitalId, $msg);
        
        if ($staffResolved['status'] === 'found') {
            return buildIndividualAttendanceAction($conn, $hospitalId, $staffResolved['staff'], $targetStatus);
        } elseif ($staffResolved['status'] === 'multiple') {
            return [
                'type' => 'clarification',
                'question' => "I found multiple staff members matching that name: \n\n" . 
                              $staffResolved['list'] . "\n\n" . 
                              "Which employee would you like me to mark as **$targetStatus**?",
                'suggestions' => $staffResolved['suggestions']
            ];
        } elseif ($staffResolved['status'] === 'not_found' && !empty($staffResolved['searched_term'])) {
            return [
                'type' => 'clarification',
                'question' => "I searched our staff directory for **\"{$staffResolved['searched_term']}\"**, but couldn't find a matching employee in our hospital.\n\n" .
                              "Could you please confirm the staff member's full name or employee code (e.g. STF-101)?",
                'suggestions' => getActiveStaffSuggestions($conn, $hospitalId)
            ];
        } else {
            // General "mark attendance" with no name or target specified
            return [
                'type' => 'clarification',
                'question' => "I would be happy to help you update attendance! 📋\n\n" .
                              "Could you please clarify:\n" .
                              "1. Would you like to mark **all active staff** at once, or a **specific employee**?\n" .
                              "2. Which status should be applied (**Present**, **Absent**, **Half Day**, or **Late**)?",
                'suggestions' => [
                    'Mark all staff as Present',
                    'Mark specific employee',
                    'Check today\'s attendance status'
                ]
            ];
        }
    }

    // -------------------------------------------------------------
    // 2. BED DISCHARGE / ALLOTMENT ACTIONS
    // -------------------------------------------------------------
    if (strpos($lower, 'discharge') !== false && (strpos($lower, 'bed') !== false || strpos($lower, 'patient') !== false)) {
        // Extract bed number (e.g. "bed 101", "bed-102", "101")
        if (preg_match('/bed\s*[-#]?\s*([a-zA-Z0-9]+)/i', $msg, $m)) {
            $bedNumber = trim($m[1]);
            return buildBedDischargeAction($conn, $hospitalId, $bedNumber);
        } else {
            return [
                'type' => 'clarification',
                'question' => "To process a patient discharge from a bed, which **Bed Number** should be cleared (e.g. Bed 101, ICU-01)?",
                'suggestions' => getOccupiedBedSuggestions($conn, $hospitalId)
            ];
        }
    }

    // -------------------------------------------------------------
    // 3. APPOINTMENT CHECK-IN / STATUS ACTIONS
    // -------------------------------------------------------------
    if ((strpos($lower, 'check in') !== false || strpos($lower, 'check-in') !== false || strpos($lower, 'checked in') !== false) && strpos($lower, 'appointment') !== false) {
        if (preg_match('/(?:appointment|app|token)\s*[-#]?\s*(\d+)/i', $msg, $m)) {
            $appId = (int)$m[1];
            return buildAppointmentCheckInAction($conn, $hospitalId, $appId);
        } else {
            return [
                'type' => 'clarification',
                'question' => "Which appointment or token number should I check in at the desk? (e.g., Appointment APP-0005 or Token #3)?",
                'suggestions' => ['Check in Token 1', 'View pre-booked appointments']
            ];
        }
    }

    // -------------------------------------------------------------
    // 4. BOOK APPOINTMENT INTENT (INTERACTIVE FORM TRIGGER)
    // -------------------------------------------------------------
    $bookPatterns = [
        'book appointment', 'boock appoiment', 'book apooiment', 'book an appointment',
        'schedule appointment', 'take appointment', 'open booking form', 'book doctor',
        'new appointment', 'appointment form', 'appointment book', 'book consultation'
    ];
    foreach ($bookPatterns as $bp) {
        if (strpos($lower, $bp) !== false) {
            return [
                'type' => 'form',
                'form_type' => 'book_appointment',
                'message' => "I have prepared the interactive appointment booking form for you below. Please select the doctor, choose the patient, date, and preferred time slot, then click **Confirm & Book Appointment**."
            ];
        }
    }

    // -------------------------------------------------------------
    // 5. ADMIT PATIENT INTENT (INTERACTIVE FORM TRIGGER)
    // -------------------------------------------------------------
    $admitPatterns = [
        'admit patient', 'patient admission', 'admit to bed', 'admit in icu',
        'admit to icu', 'bed admission', 'open admission form', 'admit in ward'
    ];
    foreach ($admitPatterns as $ap) {
        if (strpos($lower, $ap) !== false) {
            return [
                'type' => 'form',
                'form_type' => 'admit_patient',
                'message' => "I have opened the patient bed admission form for you below. Please select the patient, available bed, and admitting doctor to process the inpatient admission."
            ];
        }
    }

    return null;
}

/**
 * Builds the verified action plan for bulk attendance.
 */
function buildBulkAttendanceAction($conn, $hospitalId, $lower) {
    $targetStatus = 'Present';
    if (strpos($lower, 'absent') !== false) $targetStatus = 'Absent';

    // Check how many active staff exist
    $stmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE hospital_id = ? AND status = 'Active'");
    $stmt->bind_param("i", $hospitalId);
    $stmt->execute();
    $activeCount = $stmt->get_result()->fetch_row()[0] ?? 0;

    if ($activeCount === 0) {
        return [
            'type' => 'clarification',
            'question' => "There are currently no active staff members registered in your hospital to mark attendance for.",
            'suggestions' => ['Add new staff member', 'View staff directory']
        ];
    }

    $today = date('Y-m-d');
    $checkIn = ($targetStatus === 'Present') ? '08:00:00' : 'NULL';
    $hours = ($targetStatus === 'Present') ? 8.0 : 0.0;

    $sql = "INSERT INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, working_hours, notes, marked_by) " .
           "SELECT hospital_id, id, '$today', '$targetStatus', '08:00:00', $hours, 'Bulk Attendance marked via BHOOMA AI', 'BHOOMA AI' " .
           "FROM staff WHERE hospital_id = $hospitalId AND status = 'Active' " .
           "ON DUPLICATE KEY UPDATE status = '$targetStatus', check_in_time = '08:00:00', working_hours = $hours, marked_by = 'BHOOMA AI', updated_at = NOW()";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'INSERT_UPDATE',
            'table' => 'staff_attendance',
            'description' => "Mark all $activeCount active staff members as '$targetStatus' for today ($today)",
            'sql' => $sql,
            'params' => [
                'hospital_id' => $hospitalId,
                'status' => $targetStatus,
                'count' => $activeCount,
                'date' => $today
            ]
        ]
    ];
}

/**
 * Builds the verified action plan for individual staff attendance.
 */
function buildIndividualAttendanceAction($conn, $hospitalId, $staff, $status) {
    $staffId = (int)$staff['id'];
    $staffName = $staff['first_name'] . ' ' . $staff['last_name'];
    $staffCode = $staff['staff_code'] ?? "STF-$staffId";
    $role = $staff['role'] ?? 'Staff';
    $today = date('Y-m-d');

    $checkIn = ($status === 'Present') ? '08:00:00' : null;
    $hours = ($status === 'Present') ? 8.0 : (($status === 'Half Day') ? 4.0 : (($status === 'Late') ? 7.5 : 0.0));

    $sql = "INSERT INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, working_hours, notes, marked_by) " .
           "VALUES ($hospitalId, $staffId, '$today', '$status', '08:00:00', $hours, 'Marked via BHOOMA AI', 'BHOOMA AI') " .
           "ON DUPLICATE KEY UPDATE status = '$status', check_in_time = '08:00:00', working_hours = $hours, marked_by = 'BHOOMA AI', updated_at = NOW()";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'INSERT_UPDATE',
            'table' => 'staff_attendance',
            'description' => "Mark $staffName ($staffCode - $role) as '$status' for today ($today)",
            'sql' => $sql,
            'params' => [
                'hospital_id' => $hospitalId,
                'staff_id' => $staffId,
                'staff_name' => $staffName,
                'status' => $status,
                'date' => $today
            ]
        ]
    ];
}

/**
 * Builds the verified action plan for bed discharge.
 */
function buildBedDischargeAction($conn, $hospitalId, $bedNumber) {
    // Find bed and patient
    $stmt = $conn->prepare("SELECT b.*, p.name, p.surname, p.id as pid FROM beds b LEFT JOIN patients p ON b.patient_id = p.id WHERE (b.bed_number = ? OR b.bed_number = ?) AND (b.hospital_id = ? OR b.hospital_id IS NULL)");
    $cleanBed = trim($bedNumber);
    $prefixedBed = 'Bed ' . $cleanBed;
    $stmt->bind_param("ssi", $cleanBed, $prefixedBed, $hospitalId);
    $stmt->execute();
    $bed = $stmt->get_result()->fetch_assoc();

    if (!$bed) {
        return [
            'type' => 'clarification',
            'question' => "Bed **$bedNumber** was not found in our hospital bed records. Please check the bed number.",
            'suggestions' => getOccupiedBedSuggestions($conn, $hospitalId)
        ];
    }

    if ($bed['status'] === 'Available' || empty($bed['patient_id'])) {
        return [
            'type' => 'clarification',
            'question' => "Bed **{$bed['bed_number']}** is currently already **Available** and sanitized (no admitted patient).",
            'suggestions' => ['View all beds', 'Allot bed to patient']
        ];
    }

    $patientName = trim(($bed['name'] ?? '') . ' ' . ($bed['surname'] ?? ''));
    $patientId = $bed['patient_id'];
    $realBedNum = $bed['bed_number'];

    $sql = "UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$realBedNum'; " .
           "UPDATE appointments SET status = 'Discharged from Bed', bed_number = NULL WHERE patient_id = '$patientId' AND bed_number = '$realBedNum'";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'UPDATE',
            'table' => 'beds',
            'description' => "Discharge patient $patientName ($patientId) from Bed $realBedNum and mark bed Available",
            'sql' => $sql,
            'params' => [
                'bed_number' => $realBedNum,
                'patient_id' => $patientId
            ]
        ]
    ];
}

/**
 * Builds the verified action plan for appointment check-in.
 */
function buildAppointmentCheckInAction($conn, $hospitalId, $appId) {
    $stmt = $conn->prepare("SELECT a.*, p.name, p.surname FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.id = ? AND (a.hospital_id = ? OR a.hospital_id IS NULL)");
    $stmt->bind_param("ii", $appId, $hospitalId);
    $stmt->execute();
    $appt = $stmt->get_result()->fetch_assoc();

    if (!$appt) {
        return [
            'type' => 'clarification',
            'question' => "Appointment ID #$appId could not be found for our hospital.",
            'suggestions' => ['Check today\'s queue', 'View appointments']
        ];
    }

    $patientName = $appt['name'] . ' ' . $appt['surname'];
    $sql = "UPDATE appointments SET status = 'Checked-In', stage = 1 WHERE id = $appId";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'UPDATE',
            'table' => 'appointments',
            'description' => "Check in $patientName for Appointment #$appId into Queue Stage 1 (Checked-In)",
            'sql' => $sql,
            'params' => [
                'appointment_id' => $appId,
                'patient_id' => $appt['patient_id']
            ]
        ]
    ];
}

/**
 * Extracts and resolves a staff member from the text message against the real database.
 */
function resolveStaffFromMessage($conn, $hospitalId, $msg) {
    // Strip common filler words
    $clean = preg_replace('/\b(mark|attendance|attdence|attendence|of|for|as|is|the|employee|staff|present|absent|late|half|day|leave|today|today\'s|karo|lagao|ki|ko|kare)\b/i', ' ', $msg);
    $clean = trim(preg_replace('/\s+/', ' ', $clean));

    if (empty($clean) || strlen($clean) < 2) {
        return ['status' => 'empty'];
    }

    // Try finding staff matching the clean string or tokens
    $tokens = explode(' ', $clean);
    $matches = [];

    // 1. Direct match by staff_code
    foreach ($tokens as $token) {
        if (preg_match('/^stf-?\d+/i', $token)) {
            $code = strtoupper($token);
            $stmt = $conn->prepare("SELECT * FROM staff WHERE hospital_id = ? AND (UPPER(staff_code) = ? OR UPPER(staff_code) LIKE ?)");
            $pattern = "%$code%";
            $stmt->bind_param("iss", $hospitalId, $code, $pattern);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $matches[$r['id']] = $r;
        }
    }

    // 2. Match by first or last name
    if (empty($matches)) {
        foreach ($tokens as $token) {
            if (strlen($token) >= 3) {
                $stmt = $conn->prepare("SELECT * FROM staff WHERE hospital_id = ? AND (LOWER(first_name) LIKE ? OR LOWER(last_name) LIKE ?)");
                $term = "%" . strtolower($token) . "%";
                $stmt->bind_param("iss", $hospitalId, $term, $term);
                $stmt->execute();
                $res = $stmt->get_result();
                while ($r = $res->fetch_assoc()) {
                    $matches[$r['id']] = $r;
                }
            }
        }
    }

    if (count($matches) === 1) {
        return ['status' => 'found', 'staff' => reset($matches)];
    } elseif (count($matches) > 1) {
        $list = "";
        $suggestions = [];
        foreach ($matches as $s) {
            $name = $s['first_name'] . ' ' . $s['last_name'];
            $code = $s['staff_code'];
            $role = $s['role'];
            $list .= "• **$name** ($code — $role)\n";
            $suggestions[] = "Mark $name as Present";
        }
        return [
            'status' => 'multiple',
            'list' => trim($list),
            'suggestions' => array_slice($suggestions, 0, 3)
        ];
    }

    return ['status' => 'not_found', 'searched_term' => $clean];
}

/**
 * Returns suggestions of active staff for the hospital.
 */
function getActiveStaffSuggestions($conn, $hospitalId) {
    $res = $conn->query("SELECT first_name, last_name, role FROM staff WHERE hospital_id = $hospitalId AND status = 'Active' LIMIT 3");
    $suggs = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $suggs[] = "Mark {$row['first_name']} {$row['last_name']} as Present";
        }
    }
    return $suggs;
}

/**
 * Returns suggestions of occupied beds.
 */
function getOccupiedBedSuggestions($conn, $hospitalId) {
    $res = $conn->query("SELECT bed_number FROM beds WHERE (hospital_id = $hospitalId OR hospital_id IS NULL) AND status = 'Occupied' LIMIT 3");
    $suggs = [];
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $suggs[] = "Discharge {$row['bed_number']}";
        }
    }
    return $suggs ?: ['View all beds'];
}
