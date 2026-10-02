<?php
// ai/action_engine.php
// Pro-grade Action Engine for Hospital Management System
// Validates intent, multi-turn entity resolution, gathers missing details, 
// asks back when in doubt, and generates verified executable operations and dynamic forms.

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/system_knowledge.php';

/**
 * Main dispatcher to analyze user request for action intent.
 * Now with full Multi-Turn conversation memory ($history) support!
 * Returns:
 * - ['type' => 'clarification', 'question' => '...', 'suggestions' => [...]] if info is missing
 * - ['type' => 'form', 'form_type' => '...', 'title' => '...', 'message' => '...', 'prefill' => [...]]
 * - ['type' => 'action', 'plan' => [...]] if executable operation is ready
 * - null if read query or general conversation
 */
function analyzeActionIntent($conn, $hospitalId, $message, $history = []) {
    $msg = trim($message);
    $lower = strtolower($msg);

    // -------------------------------------------------------------
    // 1. DELETE / REMOVE DOCTOR INTENT (Multi-Turn & Pronoun Aware)
    // -------------------------------------------------------------
    $deleteKeywords = ['delete', 'remove', 'delate', 'delet', 'erase', 'hata do', 'hatao', 'nikalo', 'nikal do', 'drop'];
    $isDeleteIntent = false;
    foreach ($deleteKeywords as $dk) {
        if (strpos($lower, $dk) !== false) {
            $isDeleteIntent = true;
            break;
        }
    }

    $doctorKeywords = ['doctor', 'docter', 'doc', 'dr.', 'dr ', 'physician', 'specialist', 'surgeon', 'him', 'that doctor', 'that docter', 'us doctor'];
    $isDoctorMentioned = false;
    foreach ($doctorKeywords as $dok) {
        if (strpos($lower, $dok) !== false) {
            $isDoctorMentioned = true;
            break;
        }
    }

    if ($isDeleteIntent && $isDoctorMentioned) {
        $doc = resolveDoctorFromMessageOrHistory($conn, $hospitalId, $msg, $history);
        if ($doc) {
            $docId = (int)$doc['id'];
            $docName = $doc['name'];
            $degree = $doc['degree'] ?? 'Specialist';
            $sql = "DELETE FROM doctors WHERE id = $docId AND hospital_id = $hospitalId";
            return [
                'type' => 'action',
                'plan' => [
                    'action_type' => 'DELETE',
                    'table' => 'doctors',
                    'description' => "Permanently delete doctor: $docName ($degree) [ID: $docId] from hospital database",
                    'sql' => $sql,
                    'params' => [
                        'doctor_id' => $docId,
                        'name' => $docName
                    ]
                ]
            ];
        } else {
            return [
                'type' => 'clarification',
                'question' => "Which doctor would you like to delete from the hospital records? Please specify the doctor's name or choose from below:",
                'suggestions' => getDoctorSuggestions($conn, $hospitalId)
            ];
        }
    }

    // -------------------------------------------------------------
    // 2. UPDATE / EDIT DOCTOR INTENT (PRE-FILLED IN-CHAT FORM)
    // -------------------------------------------------------------
    $updateKeywords = ['update', 'edit', 'change', 'modify', 'badlo', 'sudharo', 'correct'];
    $isUpdateIntent = false;
    foreach ($updateKeywords as $uk) {
        if (strpos($lower, $uk) !== false) {
            $isUpdateIntent = true;
            break;
        }
    }

    if ($isUpdateIntent && $isDoctorMentioned) {
        $doc = resolveDoctorFromMessageOrHistory($conn, $hospitalId, $msg, $history);
        if ($doc) {
            return [
                'type' => 'form',
                'form_type' => 'update_doctor',
                'title' => 'Update Doctor Details',
                'message' => "I have retrieved the current details for **{$doc['name']}**. Please edit the fields in the form below and click **Submit Changes**:",
                'prefill' => $doc
            ];
        } else {
            return [
                'type' => 'clarification',
                'question' => "Which doctor's profile would you like to edit? Please specify the doctor's name:",
                'suggestions' => getDoctorSuggestions($conn, $hospitalId)
            ];
        }
    }

    // -------------------------------------------------------------
    // 3. DELETE / CANCEL APPOINTMENT INTENT
    // -------------------------------------------------------------
    $appointmentKeywords = ['appointment', 'apointment', 'appoiment', 'token', 'booking', 'parchi', 'slot'];
    $isAppMentioned = false;
    foreach ($appointmentKeywords as $ak) {
        if (strpos($lower, $ak) !== false) {
            $isAppMentioned = true;
            break;
        }
    }

    if (($isDeleteIntent || strpos($lower, 'cancel') !== false) && $isAppMentioned) {
        $app = resolveAppointmentFromMessageOrHistory($conn, $hospitalId, $msg, $history);
        if ($app) {
            $appId = (int)$app['id'];
            $pName = $app['patient_name'] ?? 'Patient';
            $sql = "UPDATE appointments SET status = 'Cancelled' WHERE id = $appId AND hospital_id = $hospitalId";
            return [
                'type' => 'action',
                'plan' => [
                    'action_type' => 'UPDATE',
                    'table' => 'appointments',
                    'description' => "Cancel Appointment #APP-$appId for $pName",
                    'sql' => $sql,
                    'params' => [
                        'appointment_id' => $appId,
                        'patient_name' => $pName
                    ]
                ]
            ];
        } else {
            return [
                'type' => 'clarification',
                'question' => "Which appointment or token number should be cancelled? (e.g. Appointment #15 or Token #3):",
                'suggestions' => ['View today\'s appointments', 'Cancel Token 1']
            ];
        }
    }

    // -------------------------------------------------------------
    // 4. DELETE / UPDATE PATIENT INTENT
    // -------------------------------------------------------------
    if ($isDeleteIntent && (strpos($lower, 'patient') !== false || strpos($lower, 'pateint') !== false || strpos($lower, 'mareez') !== false)) {
        $pat = resolvePatientFromMessageOrHistory($conn, $hospitalId, $msg, $history);
        if ($pat) {
            $patId = $pat['id'];
            $patName = $pat['name'] . ' ' . ($pat['surname'] ?? '');
            $sql = "DELETE FROM patients WHERE id = '$patId' AND hospital_id = $hospitalId";
            return [
                'type' => 'action',
                'plan' => [
                    'action_type' => 'DELETE',
                    'table' => 'patients',
                    'description' => "Permanently delete patient record: $patName ($patId) from hospital database",
                    'sql' => $sql,
                    'params' => ['patient_id' => $patId, 'patient_name' => $patName]
                ]
            ];
        }
    }

    if ($isUpdateIntent && (strpos($lower, 'patient') !== false || strpos($lower, 'pateint') !== false)) {
        $pat = resolvePatientFromMessageOrHistory($conn, $hospitalId, $msg, $history);
        if ($pat) {
            return [
                'type' => 'form',
                'form_type' => 'update_patient',
                'title' => 'Update Patient Records',
                'message' => "Loaded patient record for **{$pat['name']} {$pat['surname']}** ({$pat['id']}). Please update the fields below:",
                'prefill' => $pat
            ];
        }
    }

    // -------------------------------------------------------------
    // 5. UPDATE BED INTENT (PRE-FILLED IN-CHAT FORM)
    // -------------------------------------------------------------
    if ($isUpdateIntent && (strpos($lower, 'bed') !== false || strpos($lower, 'room') !== false)) {
        $bed = resolveBedFromMessageOrHistory($conn, $hospitalId, $msg, $history);
        if ($bed) {
            return [
                'type' => 'form',
                'form_type' => 'update_bed',
                'title' => 'Update Ward Bed Settings',
                'message' => "Loaded details for Bed **{$bed['bed_number']}** ({$bed['type']}). Edit settings below:",
                'prefill' => $bed
            ];
        }
    }

    // -------------------------------------------------------------
    // 6. ATTENDANCE ACTIONS
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
            return null; // Let Phase 1 execute SQL SELECT
        }

        // Check for Bulk Attendance
        $bulkPatterns = ['all present', 'mark all', 'all staff present', 'everyone present', 'sabhi present', 'sabko present', 'sab present', 'mark everyone'];
        foreach ($bulkPatterns as $bp) {
            if (strpos($lower, $bp) !== false) {
                return buildBulkAttendanceAction($conn, $hospitalId, $lower);
            }
        }

        $targetStatus = 'Present';
        if (strpos($lower, 'absent') !== false) $targetStatus = 'Absent';
        elseif (strpos($lower, 'half day') !== false || strpos($lower, 'halfday') !== false) $targetStatus = 'Half Day';
        elseif (strpos($lower, 'leave') !== false) $targetStatus = 'On Leave';
        elseif (strpos($lower, 'late') !== false) $targetStatus = 'Late';

        $staffResolved = resolveStaffFromMessage($conn, $hospitalId, $msg);
        if ($staffResolved['status'] === 'found') {
            return buildIndividualAttendanceAction($conn, $hospitalId, $staffResolved['staff'], $targetStatus);
        } elseif ($staffResolved['status'] === 'multiple') {
            return [
                'type' => 'clarification',
                'question' => "I found multiple staff members matching that name: \n\n" . $staffResolved['list'] . "\n\nWhich employee would you like me to mark as **$targetStatus**?",
                'suggestions' => $staffResolved['suggestions']
            ];
        }
    }

    // -------------------------------------------------------------
    // 7. BED DISCHARGE / ALLOTMENT ACTIONS
    // -------------------------------------------------------------
    if (strpos($lower, 'discharge') !== false && (strpos($lower, 'bed') !== false || strpos($lower, 'patient') !== false || strpos($lower, 'room') !== false)) {
        $bedNumber = null;
        if (preg_match('/(?:bed|room)\s*[-#]?\s*([a-zA-Z0-9]+)/i', $msg, $m)) {
            $bedNumber = trim($m[1]);
        } else {
            $bed = resolveBedFromMessageOrHistory($conn, $hospitalId, $msg, $history);
            if ($bed) $bedNumber = $bed['bed_number'];
        }

        if ($bedNumber) {
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
    // 8. APPOINTMENT CHECK-IN ACTIONS
    // -------------------------------------------------------------
    if ((strpos($lower, 'check in') !== false || strpos($lower, 'check-in') !== false) && $isAppMentioned) {
        if (preg_match('/(?:appointment|app|token)\s*[-#]?\s*(\d+)/i', $msg, $m)) {
            $appId = (int)$m[1];
            return buildAppointmentCheckInAction($conn, $hospitalId, $appId);
        }
    }

    // -------------------------------------------------------------
    // 9. BOOK APPOINTMENT INTENT (INTERACTIVE FORM TRIGGER)
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
                'title' => 'Book New Consultation',
                'message' => "I have prepared the interactive consultation booking form below. Please select the doctor, choose the patient, date, and preferred time slot, then click **Confirm & Book Appointment**."
            ];
        }
    }

    // -------------------------------------------------------------
    // 10. ADMIT PATIENT INTENT (INTERACTIVE FORM TRIGGER)
    // -------------------------------------------------------------
    $admitPatterns = [
        'admit patient', 'admit a patient', 'patient admission', 'admit to bed', 'admit in icu',
        'admit a patient in icu', 'admit to icu', 'bed admission', 'open admission form', 'admit in ward',
        'admit someone'
    ];
    foreach ($admitPatterns as $ap) {
        if (strpos($lower, $ap) !== false) {
            return [
                'type' => 'form',
                'form_type' => 'admit_patient',
                'title' => 'Inpatient Bed Admission',
                'message' => "I have opened the patient bed admission form below. Select the patient, available bed, and admitting doctor to process the inpatient admission."
            ];
        }
    }

    // -------------------------------------------------------------
    // 11. ADD / REGISTER PATIENT INTENT (INTERACTIVE FORM TRIGGER)
    // -------------------------------------------------------------
    $addPatientPatterns = [
        'add patient', 'register patient', 'new patient', 'create patient', 'admit new patient',
        'add a patient', 'register a patient', 'new patient form', 'patient registration form',
        'nayi entry patient', 'mareez register karo', 'patient add karo'
    ];
    foreach ($addPatientPatterns as $app) {
        if (strpos($lower, $app) !== false) {
            $extracted = extractPatientPrefillFromText($msg);
            return [
                'type' => 'form',
                'form_type' => 'add_patient',
                'title' => 'Register New Patient',
                'message' => "I have prepared the interactive Patient Registration form below. Please fill out or review the details and click **Register Patient**.",
                'prefill' => $extracted
            ];
        }
    }

    // -------------------------------------------------------------
    // 12. ADD / ONBOARD DOCTOR INTENT (INTERACTIVE FORM TRIGGER)
    // -------------------------------------------------------------
    $addDoctorPatterns = [
        'add doctor', 'register doctor', 'new doctor', 'create doctor', 'onboard doctor',
        'add a doctor', 'add physician', 'new physician', 'doctor registration form',
        'doctor add karo', 'nayi doctor entry'
    ];
    foreach ($addDoctorPatterns as $adp) {
        if (strpos($lower, $adp) !== false) {
            $extracted = extractDoctorPrefillFromText($msg);
            return [
                'type' => 'form',
                'form_type' => 'add_doctor',
                'title' => 'Onboard New Doctor / Specialist',
                'message' => "I have generated the Doctor Onboarding form below. Enter the physician's credentials, specialization, and consultation fee, then submit.",
                'prefill' => $extracted
            ];
        }
    }

    // -------------------------------------------------------------
    // 13. ADD / CREATE BED INTENT (INTERACTIVE FORM TRIGGER)
    // -------------------------------------------------------------
    $addBedPatterns = [
        'add bed', 'create bed', 'new bed', 'add room', 'new room', 'create room',
        'bed add karo', 'room create karo', 'new ward bed'
    ];
    foreach ($addBedPatterns as $abp) {
        if (strpos($lower, $abp) !== false) {
            $bedNum = '';
            if (preg_match('/(?:bed|room)\s*([a-zA-Z0-9_-]+)/i', $msg, $bm)) {
                $bedNum = strtoupper($bm[1]);
            }
            return [
                'type' => 'form',
                'form_type' => 'add_bed',
                'title' => 'Add New Hospital Bed / Room',
                'message' => "I have loaded the Bed Inventory Creation form below. Specify the bed number, ward type, and wing to add it to hospital inventory.",
                'prefill' => ['bed_number' => $bedNum, 'type' => 'General Ward', 'wing' => 'North Wing', 'daily_charge' => '1500']
            ];
        }
    }

    return null;
}

function extractPatientPrefillFromText($msg) {
    $prefill = ['name' => '', 'surname' => '', 'gender' => 'Male', 'blood_group' => '', 'age' => '', 'phone' => ''];
    if (preg_match('/(?:blood\s*group\s*)?([ABOabo0]{1,2}[\+-])(?=[^a-zA-Z0-9]|$)/i', $msg, $m)) {
        $prefill['blood_group'] = strtoupper($m[1]);
    }
    if (preg_match('/\b(male|female|other|purush|mahila)\b/i', $msg, $m)) {
        $g = strtolower($m[1]);
        $prefill['gender'] = ($g === 'female' || $g === 'mahila') ? 'Female' : (($g === 'other') ? 'Other' : 'Male');
    }
    if (preg_match('/\b(?:age|umar|saal)\s*[:=]?\s*(\d{1,3})\b/i', $msg, $m) || preg_match('/\b(\d{1,2})\s*(?:years?|yrs?|yr|saal)\b/i', $msg, $m)) {
        $prefill['age'] = $m[1];
    }
    if (preg_match('/\b(\+?91[\s-]?)?([6-9]\d{9})\b/', $msg, $m)) {
        $prefill['phone'] = $m[2];
    }
    if (preg_match('/(?:patient|naam|name)\s+([A-Z][a-z]+)\s+([A-Z][a-z]+)/i', $msg, $m)) {
        $prefill['name'] = ucfirst($m[1]);
        $prefill['surname'] = ucfirst($m[2]);
    }
    return $prefill;
}

function extractDoctorPrefillFromText($msg) {
    $prefill = ['name' => '', 'degree' => 'MBBS, MD', 'department' => 'General Medicine', 'phone' => '', 'fee' => '500', 'experience' => '5 Years'];
    if (preg_match('/(?:dr\.?|doctor)\s+([A-Za-z\.\s]+?)(?:(?:\bwith\b|\bdept\b|\bphone\b|\bfee\b)|$)/i', $msg, $m)) {
        $prefill['name'] = 'Dr. ' . trim(preg_replace('/^dr\.?\s*/i', '', $m[1]));
    }
    if (preg_match('/\b(cardio\w*|ortho\w*|pediatr\w*|neuro\w*|general medicine|gynec\w*|dermat\w*|radiology)\b/i', $msg, $m)) {
        $prefill['department'] = ucfirst($m[1]);
    }
    return $prefill;
}

// =================================================================
// MULTI-TURN & ENTITY RESOLUTION HELPERS
// =================================================================

/**
 * Resolves doctor from message tokens OR scans previous conversation turns.
 */
function resolveDoctorFromMessageOrHistory($conn, $hospitalId, $msg, $history = []) {
    // 1. Direct name match in user message
    $res = $conn->query("SELECT id, name, degree, experience, phone, email, department_id FROM doctors WHERE hospital_id = $hospitalId");
    $doctors = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $doctors[] = $r;
    }

    $msgLower = strtolower($msg);
    foreach ($doctors as $d) {
        $nameLower = strtolower($d['name']);
        // Remove "dr." prefix for robust fuzzy matching
        $cleanDocName = trim(preg_replace('/^dr\.?\s*/i', '', $nameLower));
        $nameParts = explode(' ', $cleanDocName);
        
        // Exact name or substantial part (e.g. "Ambarish" or "Panchasara")
        if (strpos($msgLower, $nameLower) !== false || strpos($msgLower, $cleanDocName) !== false) {
            return $d;
        }
        foreach ($nameParts as $part) {
            if (strlen($part) >= 4 && strpos($msgLower, $part) !== false) {
                return $d;
            }
        }
    }

    // 2. Pronoun / Reference Match ("that doctor", "him", "delete that doctor", etc.) from Conversation History
    if (!empty($history)) {
        // Iterate backwards from most recent message
        $revHistory = array_reverse($history);
        foreach ($revHistory as $h) {
            $contentLower = strtolower($h['content'] ?? '');
            foreach ($doctors as $d) {
                $nameLower = strtolower($d['name']);
                $cleanDocName = trim(preg_replace('/^dr\.?\s*/i', '', $nameLower));
                if (strpos($contentLower, $nameLower) !== false || strpos($contentLower, $cleanDocName) !== false) {
                    return $d;
                }
                $nameParts = explode(' ', $cleanDocName);
                foreach ($nameParts as $part) {
                    if (strlen($part) >= 4 && strpos($contentLower, $part) !== false) {
                        return $d;
                    }
                }
            }
        }
    }

    // 3. Fallback: If only 1 doctor exists in hospital, return that
    if (count($doctors) === 1) {
        return $doctors[0];
    }

    return null;
}

/**
 * Resolves patient from message or conversation history.
 */
function resolvePatientFromMessageOrHistory($conn, $hospitalId, $msg, $history = []) {
    if (preg_match('/pat-\d+/i', $msg, $m)) {
        $pid = strtoupper($m[0]);
        $res = $conn->query("SELECT * FROM patients WHERE id = '$pid' AND hospital_id = $hospitalId LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) return $row;
    }

    $res = $conn->query("SELECT id, name, surname, phone, blood_group, age, gender FROM patients WHERE hospital_id = $hospitalId LIMIT 50");
    $patients = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $patients[] = $r;
    }

    $msgLower = strtolower($msg);
    foreach ($patients as $p) {
        $fullName = strtolower($p['name'] . ' ' . ($p['surname'] ?? ''));
        if (strpos($msgLower, strtolower($p['name'])) !== false || strpos($msgLower, $fullName) !== false) {
            return $p;
        }
    }

    // Scan history
    if (!empty($history)) {
        $rev = array_reverse($history);
        foreach ($rev as $h) {
            $c = strtolower($h['content'] ?? '');
            foreach ($patients as $p) {
                if (strpos($c, strtolower($p['name'])) !== false || strpos($c, strtolower($p['id'])) !== false) {
                    return $p;
                }
            }
        }
    }

    return null;
}

/**
 * Resolves bed number from message or conversation history.
 */
function resolveBedFromMessageOrHistory($conn, $hospitalId, $msg, $history = []) {
    if (preg_match('/(?:bed|room)?\s*[-#]?\s*([a-zA-Z0-9]+-\d+|\d+)/i', $msg, $m)) {
        $bedNum = strtoupper(trim($m[1]));
        $res = $conn->query("SELECT * FROM beds WHERE (UPPER(bed_number) = '$bedNum' OR bed_number LIKE '%$bedNum%') AND hospital_id = $hospitalId LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) return $row;
    }

    // Scan history for bed numbers
    if (!empty($history)) {
        $rev = array_reverse($history);
        foreach ($rev as $h) {
            $c = $h['content'] ?? '';
            if (preg_match('/(?:bed|room)\s*[:#\-]?\s*([a-zA-Z0-9\-]+)/i', $c, $m)) {
                $b = strtoupper(trim($m[1]));
                $res = $conn->query("SELECT * FROM beds WHERE UPPER(bed_number) = '$b' AND hospital_id = $hospitalId LIMIT 1");
                if ($res && $row = $res->fetch_assoc()) return $row;
            }
        }
    }

    return null;
}

/**
 * Resolves appointment from message or conversation history.
 */
function resolveAppointmentFromMessageOrHistory($conn, $hospitalId, $msg, $history = []) {
    if (preg_match('/(?:app|appointment|token)\s*[-#]?\s*(\d+)/i', $msg, $m)) {
        $appId = (int)$m[1];
        $res = $conn->query("SELECT a.*, p.name as patient_name FROM appointments a LEFT JOIN patients p ON a.patient_id = p.id WHERE a.id = $appId AND a.hospital_id = $hospitalId LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) return $row;
    }

    if (!empty($history)) {
        $rev = array_reverse($history);
        foreach ($rev as $h) {
            $c = $h['content'] ?? '';
            if (preg_match('/#APP-(\d+)/i', $c, $m) || preg_match('/appointment #?(\d+)/i', $c, $m)) {
                $appId = (int)$m[1];
                $res = $conn->query("SELECT a.*, p.name as patient_name FROM appointments a LEFT JOIN patients p ON a.patient_id = p.id WHERE a.id = $appId AND a.hospital_id = $hospitalId LIMIT 1");
                if ($res && $row = $res->fetch_assoc()) return $row;
            }
        }
    }

    return null;
}

function getDoctorSuggestions($conn, $hospitalId) {
    $res = $conn->query("SELECT name FROM doctors WHERE hospital_id = $hospitalId LIMIT 4");
    $suggs = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) {
            $suggs[] = "Delete {$r['name']}";
        }
    }
    return $suggs ?: ['View doctor directory'];
}

// Re-include standard builders
function buildBulkAttendanceAction($conn, $hospitalId, $lower) {
    $targetStatus = (strpos($lower, 'absent') !== false) ? 'Absent' : 'Present';
    $today = date('Y-m-d');
    $hours = ($targetStatus === 'Present') ? 8.0 : 0.0;
    
    $cnt = $conn->query("SELECT COUNT(*) FROM staff WHERE hospital_id = $hospitalId AND status = 'Active'")->fetch_row()[0] ?? 0;
    $sql = "INSERT INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, working_hours, notes, marked_by) " .
           "SELECT hospital_id, id, '$today', '$targetStatus', '08:00:00', $hours, 'Bulk Attendance marked via BHOOMA AI', 'BHOOMA AI' " .
           "FROM staff WHERE hospital_id = $hospitalId AND status = 'Active' " .
           "ON DUPLICATE KEY UPDATE status = '$targetStatus', check_in_time = '08:00:00', working_hours = $hours, marked_by = 'BHOOMA AI', updated_at = NOW()";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'INSERT_UPDATE',
            'table' => 'staff_attendance',
            'description' => "Mark all $cnt active staff members as '$targetStatus' for today ($today)",
            'sql' => $sql,
            'params' => ['hospital_id' => $hospitalId, 'status' => $targetStatus, 'date' => $today]
        ]
    ];
}

function buildIndividualAttendanceAction($conn, $hospitalId, $staff, $status) {
    $staffId = (int)$staff['id'];
    $staffName = $staff['first_name'] . ' ' . $staff['last_name'];
    $today = date('Y-m-d');
    $hours = ($status === 'Present') ? 8.0 : 0.0;

    $sql = "INSERT INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, working_hours, notes, marked_by) " .
           "VALUES ($hospitalId, $staffId, '$today', '$status', '08:00:00', $hours, 'Marked via BHOOMA AI', 'BHOOMA AI') " .
           "ON DUPLICATE KEY UPDATE status = '$status', check_in_time = '08:00:00', working_hours = $hours, marked_by = 'BHOOMA AI', updated_at = NOW()";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'INSERT_UPDATE',
            'table' => 'staff_attendance',
            'description' => "Mark employee $staffName ({$staff['staff_code']}) as '$status' for today",
            'sql' => $sql,
            'params' => ['staff_id' => $staffId, 'status' => $status, 'date' => $today]
        ]
    ];
}

function buildBedDischargeAction($conn, $hospitalId, $bedNumber) {
    $stmt = $conn->prepare("SELECT b.*, p.name, p.surname FROM beds b LEFT JOIN patients p ON b.patient_id = p.id WHERE (UPPER(b.bed_number) = UPPER(?) OR b.bed_number = ?) AND (b.hospital_id = ? OR b.hospital_id IS NULL)");
    $stmt->bind_param("ssi", $bedNumber, $bedNumber, $hospitalId);
    $stmt->execute();
    $bed = $stmt->get_result()->fetch_assoc();

    $realBedNum = $bed ? $bed['bed_number'] : $bedNumber;
    $patientId = $bed['patient_id'] ?? '';
    $patientName = trim(($bed['name'] ?? '') . ' ' . ($bed['surname'] ?? ''));

    $sql = "UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$realBedNum' AND hospital_id = $hospitalId; " .
           "UPDATE appointments SET status = 'Discharged from Bed', bed_number = NULL WHERE bed_number = '$realBedNum' AND hospital_id = $hospitalId;";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'UPDATE',
            'table' => 'beds',
            'description' => "Discharge patient $patientName from Bed $realBedNum and mark bed Available",
            'sql' => $sql,
            'params' => ['bed_number' => $realBedNum, 'patient_id' => $patientId]
        ]
    ];
}

function buildAppointmentCheckInAction($conn, $hospitalId, $appId) {
    $stmt = $conn->prepare("SELECT a.*, p.name, p.surname FROM appointments a JOIN patients p ON a.patient_id = p.id WHERE a.id = ? AND a.hospital_id = ?");
    $stmt->bind_param("ii", $appId, $hospitalId);
    $stmt->execute();
    $appt = $stmt->get_result()->fetch_assoc();

    $patientName = $appt ? ($appt['name'] . ' ' . $appt['surname']) : 'Patient';
    $sql = "UPDATE appointments SET status = 'Checked-In', stage = 1 WHERE id = $appId AND hospital_id = $hospitalId";

    return [
        'type' => 'action',
        'plan' => [
            'action_type' => 'UPDATE',
            'table' => 'appointments',
            'description' => "Check in $patientName for Appointment #APP-$appId (Stage: Checked-In)",
            'sql' => $sql,
            'params' => ['appointment_id' => $appId]
        ]
    ];
}

function resolveStaffFromMessage($conn, $hospitalId, $msg) {
    $clean = preg_replace('/\b(mark|attendance|attdence|attendence|of|for|as|is|the|employee|staff|present|absent|late|half|day|leave|today|karo|lagao)\b/i', ' ', $msg);
    $clean = trim(preg_replace('/\s+/', ' ', $clean));
    if (strlen($clean) < 2) return ['status' => 'empty'];

    $tokens = explode(' ', $clean);
    $matches = [];
    foreach ($tokens as $token) {
        if (strlen($token) >= 3) {
            $stmt = $conn->prepare("SELECT * FROM staff WHERE hospital_id = ? AND (LOWER(first_name) LIKE ? OR LOWER(last_name) LIKE ? OR UPPER(staff_code) LIKE ?)");
            $term = "%" . strtolower($token) . "%";
            $upTerm = "%" . strtoupper($token) . "%";
            $stmt->bind_param("isss", $hospitalId, $term, $term, $upTerm);
            $stmt->execute();
            $res = $stmt->get_result();
            while ($r = $res->fetch_assoc()) $matches[$r['id']] = $r;
        }
    }

    if (count($matches) === 1) {
        return ['status' => 'found', 'staff' => reset($matches)];
    } elseif (count($matches) > 1) {
        $list = "";
        $suggestions = [];
        foreach ($matches as $s) {
            $name = $s['first_name'] . ' ' . $s['last_name'];
            $list .= "• **$name** ({$s['staff_code']})\n";
            $suggestions[] = "Mark $name as Present";
        }
        return ['status' => 'multiple', 'list' => trim($list), 'suggestions' => array_slice($suggestions, 0, 3)];
    }

    return ['status' => 'not_found', 'searched_term' => $clean];
}

function getOccupiedBedSuggestions($conn, $hospitalId) {
    $res = $conn->query("SELECT bed_number FROM beds WHERE hospital_id = $hospitalId AND status = 'Occupied' LIMIT 3");
    $suggs = [];
    if ($res) {
        while ($r = $res->fetch_assoc()) $suggs[] = "Discharge {$r['bed_number']}";
    }
    return $suggs ?: ['View all beds'];
}
