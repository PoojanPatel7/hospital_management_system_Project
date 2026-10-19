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
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND DATE(date) = CURDATE()");
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
            $stmt = $conn->prepare("SELECT stage, COUNT(*) as count FROM appointments WHERE hospital_id = ? AND DATE(date) = CURDATE() GROUP BY stage");
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

            $stmt = $conn->prepare("SELECT patient_id, status FROM appointments WHERE hospital_id = ? AND DATE(date) = CURDATE() AND status = 'consulting'");
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
            $stmt = $conn->prepare("SELECT COUNT(*) as total_today, SUM(CASE WHEN date > CURDATE() THEN 1 ELSE 0 END) as upcoming FROM appointments WHERE hospital_id = ? AND (DATE(date) = CURDATE() OR date > CURDATE())");
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
            $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND date < CURDATE()");
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

    $stmt = $conn->prepare("SELECT COUNT(*) FROM appointments WHERE hospital_id = ? AND DATE(date) = CURDATE()");
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

/**
 * Advanced Multi-turn Entity Context Resolver
 * Retains patient, doctor, appointment, or bed entities across conversational turns.
 * When user says "give me full info" or "what about his appointments?", this function
 * tracks exactly which patient/doctor was previously in focus!
 */
function resolveActiveEntityContext($conn, $hospitalId, $message, $history = []) {
    $context = [
        'active_patient' => null,
        'active_doctor' => null,
        'active_bed' => null,
        'active_appointment' => null,
        'intent' => 'general', // 'patient_full_info', 'patient_appointments', 'patient_prescriptions', 'patient_bed', 'general'
        'keywords' => ''
    ];

    $msg = trim($message);
    $lower = strtolower($msg);

    // 1. Check if patient ID is directly in current message (e.g. CP-2026-001, PAT-101)
    if (preg_match('/\b([a-zA-Z]{2,4}-\d{4}-\d+|[a-zA-Z]{2,4}-\d+|\bPAT-\w+|\bCP-\w+)\b/i', $msg, $m)) {
        $pId = $conn->real_escape_string(trim($m[1]));
        $res = $conn->query("SELECT * FROM patients WHERE (id = '$pId' OR id LIKE '%$pId%') AND hospital_id = $hospitalId LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $context['active_patient'] = $row;
        }
    }

    // 2. Check if a patient's name is in current message
    if (!$context['active_patient']) {
        $pRes = $conn->query("SELECT id, name, surname, phone, blood_group, age, gender FROM patients WHERE hospital_id = $hospitalId ORDER BY id DESC LIMIT 100");
        if ($pRes) {
            while ($p = $pRes->fetch_assoc()) {
                $pFirst = strtolower(trim($p['name']));
                $pSurname = strtolower(trim($p['surname'] ?? ''));
                if (strlen($pFirst) >= 3 && preg_match('/\b' . preg_quote($pFirst, '/') . '\b/i', $msg)) {
                    $context['active_patient'] = $p;
                    break;
                }
                if (!empty($pSurname) && strlen($pSurname) >= 3 && preg_match('/\b' . preg_quote($pSurname, '/') . '\b/i', $msg)) {
                    $context['active_patient'] = $p;
                    break;
                }
            }
        }
    }

    // 3. Scan recent conversation history to identify active patient from previous turns!
    if (!$context['active_patient'] && !empty($history)) {
        $revHistory = array_reverse($history);
        foreach ($revHistory as $h) {
            $hSql = $h['sql_executed'] ?? '';
            $hContent = $h['content'] ?? '';

            // Check if SQL executed was targeted to a patient
            if (!empty($hSql)) {
                if (preg_match("/(?:patients\s+WHERE\s+.*id\s*=\s*'([^']+)'|patient_id\s*=\s*'([^']+)')/i", $hSql, $sM)) {
                    $targetPid = !empty($sM[1]) ? $sM[1] : (!empty($sM[2]) ? $sM[2] : '');
                    if (!empty($targetPid)) {
                        $escTarget = $conn->real_escape_string($targetPid);
                        $res = $conn->query("SELECT * FROM patients WHERE id = '$escTarget' AND hospital_id = $hospitalId LIMIT 1");
                        if ($res && $row = $res->fetch_assoc()) {
                            $context['active_patient'] = $row;
                            break;
                        }
                    }
                } elseif (preg_match("/name\s+LIKE\s+'%([^%']+)%'/i", $hSql, $sM)) {
                    $targetName = $conn->real_escape_string($sM[1]);
                    $res = $conn->query("SELECT * FROM patients WHERE (name LIKE '%$targetName%' OR surname LIKE '%$targetName%') AND hospital_id = $hospitalId LIMIT 1");
                    if ($res && $row = $res->fetch_assoc()) {
                        $context['active_patient'] = $row;
                        break;
                    }
                }
            }

            // Check if patient ID or code was mentioned in message content
            if (preg_match('/\b([a-zA-Z]{2,4}-\d{4}-\d+|PAT-\d+|CP-\d+)\b/i', $hContent, $cM)) {
                $targetPid = $conn->real_escape_string($cM[1]);
                $res = $conn->query("SELECT * FROM patients WHERE (id = '$targetPid' OR id LIKE '%$targetPid%') AND hospital_id = $hospitalId LIMIT 1");
                if ($res && $row = $res->fetch_assoc()) {
                    $context['active_patient'] = $row;
                    break;
                }
            }
        }

        // If still not matched, scan patient names against recent conversation texts
        if (!$context['active_patient']) {
            $pRes = $conn->query("SELECT id, name, surname, phone, blood_group, age, gender FROM patients WHERE hospital_id = $hospitalId ORDER BY id DESC LIMIT 60");
            if ($pRes) {
                $patientList = [];
                while ($p = $pRes->fetch_assoc()) $patientList[] = $p;
                foreach ($revHistory as $h) {
                    $c = strtolower($h['content'] ?? '');
                    foreach ($patientList as $p) {
                        $pFirst = strtolower(trim($p['name']));
                        if (strlen($pFirst) >= 3 && strpos($c, $pFirst) !== false) {
                            $context['active_patient'] = $p;
                            break 2;
                        }
                    }
                }
            }
        }
    }

    // 4. Detect Intent for the Active Patient or current query
    $isFullInfoRequest = preg_match('/\b(full\s*(info|information|details?|record|history|profile)|all\s*(info|information|details?|records?|data)|give\s*me\s*(all|full|everything)|show\s*(all|full|everything)|tell\s*me\s*(all|everything|more)|patient\s*(info|details?|profile)|complete\s*(info|profile|details?)|everything\s*about\s*(him|her|this|patient)|ful\s*nfo|more\s*info)\b/i', $msg);
    $isAppointmentsRequest = preg_match('/\b(appointments?|bookings?|visits?|consultations?|tokens?)\b/i', $msg);
    $isPrescriptionsRequest = preg_match('/\b(prescriptions?|medicines?|drugs?|medications?|dawa|tablets?)\b/i', $msg);
    $isBedRequest = preg_match('/\b(bed|room|ward|admitted|admission|staying|occupying)\b/i', $msg);
    $isFilesRequest = preg_match('/\b(files?|images?|photos?|pics?|pictures?|documents?|reports?|scans?|x-?rays?|attachments?)\b/i', $msg);
    $isVitalsRequest = preg_match('/\b(vitals?|vital\s*signs?|bp|blood\s*pressure|pulse|heart\s*rate|spo2|oxygen|temp|temperature|fever|respiration|resp|vitals\s*chart|observation)\b/i', $msg);
    $isOnlineConsultRequest = preg_match('/\b(online\s*consult(ation)?|video\s*call|telemedicine|virtual\s*consult(ation)?|remote\s*consult)\b/i', $msg);

    $msgWords = preg_split('/\s+/', trim($msg));
    $wordCount = count($msgWords);
    $hasMutation = preg_match('/\b(delete|remove|cancel|discharge|update|edit|modify|insert|add|create|book|admit)\b/i', $msg);

    if ($context['active_patient']) {
        $p = $context['active_patient'];
        $context['keywords'] = "patient {$p['name']} {$p['surname']} {$p['id']} appointments beds prescriptions files images vitals bed_observations";
        if ($isVitalsRequest) {
            $context['intent'] = 'patient_vitals';
        } elseif ($isOnlineConsultRequest) {
            $context['intent'] = 'patient_online_consult';
        } elseif ($isFilesRequest) {
            $context['intent'] = 'patient_files';
        } elseif ($isFullInfoRequest) {
            $context['intent'] = 'patient_full_info';
        } elseif ($isAppointmentsRequest) {
            $context['intent'] = 'patient_appointments';
        } elseif ($isPrescriptionsRequest) {
            $context['intent'] = 'patient_prescriptions';
        } elseif ($isBedRequest) {
            $context['intent'] = 'patient_bed';
        } elseif ($wordCount <= 4 && !$hasMutation) {
            // Standalone patient name or "find X" query -> patient lookup intent
            $context['intent'] = 'patient_lookup';
        }
    }

    return $context;
}

/**
 * Dedicated retriever for patient medical files, images, lab reports, and radiology scans.
 */
function getPatientFilesDossier($conn, $hospitalId, $patientId, $onlyImages = false) {
    $escId = $conn->real_escape_string($patientId);
    $query = "SELECT id, patient_id, title, file_name, mime_type, file_size, category, description, created_at FROM patient_files WHERE patient_id = '$escId' ORDER BY id DESC";
    $res = $conn->query($query);
    $files = [];
    $summaryRecords = [];
    if ($res) {
        while ($f = $res->fetch_assoc()) {
            $mime = strtolower($f['mime_type'] ?? '');
            $isImg = (stripos($mime, 'image/') !== false) || preg_match('/\.(jpg|jpeg|png|webp|gif)$/i', $f['file_name']);
            if ($onlyImages && !$isImg) continue;

            $fileId = (int)$f['id'];
            $fileUrl = "api/file.php?id=$fileId";
            $thumbUrl = "api/file.php?id=$fileId&thumb=1";
            $f['file_url'] = $fileUrl;
            $f['thumb_url'] = $thumbUrl;
            $f['is_image'] = $isImg;
            $files[] = $f;

            $sizeKb = round(($f['file_size'] ?? 0) / 1024, 1) . ' KB';
            $summaryRecords[] = [
                'file_id' => $f['id'],
                'file_name' => $f['file_name'],
                'category' => $f['category'] ?: 'Medical File',
                'type' => $isImg ? 'Clinical Image' : 'Document/Report',
                'size' => $sizeKb,
                'view_link' => $fileUrl,
                'uploaded_at' => $f['created_at']
            ];
        }
    }
    return [
        'files' => $files,
        'summary_records' => $summaryRecords,
        'count' => count($files)
    ];
}

/**
 * Retrieves the comprehensive 360-degree patient dossier:
 * Demographics, Admission/Bed, Appointments, Prescriptions, Diagnoses, and Medical Files.
 */
function getFullPatientDossier($conn, $hospitalId, $patientId) {
    $escId = $conn->real_escape_string($patientId);

    // 1. Patient basic record
    $patRes = $conn->query("SELECT * FROM patients WHERE id = '$escId' AND hospital_id = $hospitalId LIMIT 1");
    if (!$patRes || $patRes->num_rows === 0) return null;
    $patient = $patRes->fetch_assoc();
    unset($patient['password']);

    // 2. Admitted Bed status
    $bed = null;
    $bedRes = $conn->query("SELECT bed_number, type, wing, status FROM beds WHERE patient_id = '$escId' AND hospital_id = $hospitalId LIMIT 1");
    if ($bedRes && $bRow = $bedRes->fetch_assoc()) {
        $bed = $bRow;
    }

    // 3. Appointments history (up to 5 recent)
    $appointments = [];
    $appRes = $conn->query("SELECT a.id, a.date, a.slot, a.type, a.status, a.stage, a.symptoms, a.allergies, d.name AS doctor_name, d.degree 
        FROM appointments a 
        LEFT JOIN doctors d ON a.doctor_id = d.id 
        WHERE a.patient_id = '$escId' AND a.hospital_id = $hospitalId 
        ORDER BY a.date DESC LIMIT 5");
    if ($appRes) {
        while ($a = $appRes->fetch_assoc()) $appointments[] = $a;
    }

    // 4. Prescriptions
    $prescriptions = [];
    $prRes = $conn->query("SELECT pr.medicine_name, pr.dosage, pr.frequency, pr.duration, pr.instructions 
        FROM prescriptions pr 
        JOIN appointments a ON pr.appointment_id = a.id 
        WHERE a.patient_id = '$escId' AND a.hospital_id = $hospitalId 
        LIMIT 8");
    if ($prRes) {
        while ($pr = $prRes->fetch_assoc()) $prescriptions[] = $pr;
    }

    // 5. Diagnoses
    $diagnoses = [];
    $dgRes = $conn->query("SELECT dg.description 
        FROM diagnoses dg 
        JOIN appointments a ON dg.appointment_id = a.id 
        WHERE a.patient_id = '$escId' AND a.hospital_id = $hospitalId 
        LIMIT 5");
    if ($dgRes) {
        while ($dg = $dgRes->fetch_assoc()) $diagnoses[] = $dg['description'];
    }

    // 6. Medical Files / Lab Reports / Images
    $files = [];
    $fRes = $conn->query("SELECT id, file_name, category, mime_type, file_size, description, created_at 
        FROM patient_files 
        WHERE patient_id = '$escId' 
        ORDER BY id DESC LIMIT 10");
    if ($fRes) {
        while ($f = $fRes->fetch_assoc()) {
            $f['file_url'] = "api/file.php?id=" . $f['id'];
            $files[] = $f;
        }
    }

    // 7. Inpatient Bed Observations & Vitals
    $vitals = [];
    $vitRes = $conn->query("SELECT id, bed_number, observed_at, observation_hour, vital_pulse, vital_bp, vital_spo2, vital_temp, vital_resp, notes, recorded_by_name 
        FROM bed_observations 
        WHERE patient_id = '$escId' 
        ORDER BY observed_at DESC LIMIT 5");
    if ($vitRes) {
        while ($v = $vitRes->fetch_assoc()) $vitals[] = $v;
    }

    // 8. Online Consultations
    $onlineConsults = [];
    $ocRes = $conn->query("SELECT oc.id, oc.status, oc.diagnosis, oc.prescription_notes, oc.doctor_notes, oc.consultation_fee, oc.fee_status, oc.started_at, d.name AS doctor_name 
        FROM online_consultations oc 
        LEFT JOIN doctors d ON oc.doctor_id = d.id 
        WHERE oc.patient_id = '$escId' 
        ORDER BY oc.started_at DESC LIMIT 5");
    if ($ocRes) {
        while ($oc = $ocRes->fetch_assoc()) $onlineConsults[] = $oc;
    }

    // Create a flat summary record for the UI data table
    $latestVit = !empty($vitals[0]) ? "BP: {$vitals[0]['vital_bp']}, Pulse: {$vitals[0]['vital_pulse']}, SpO2: {$vitals[0]['vital_spo2']}, Temp: {$vitals[0]['vital_temp']}" : 'No vitals logged yet';
    $summaryRow = [
        'patient_id' => $patient['id'],
        'name' => $patient['name'] . ' ' . ($patient['surname'] ?? ''),
        'gender' => $patient['gender'] ?? 'Not specified',
        'age' => $patient['age'] ?? 'N/A',
        'blood_group' => $patient['blood_group'] ?? 'N/A',
        'phone' => $patient['phone'] ?? 'N/A',
        'bed_admission' => $bed ? "Admitted ({$bed['bed_number']} - {$bed['wing']})" : "Outpatient (Not Admitted)",
        'latest_vitals' => $latestVit,
        'medical_files' => count($files) . ' file(s) attached',
        'online_consultations' => count($onlineConsults) . ' session(s)',
        'emergency_contact' => ($patient['emergency_contact_name'] ?? '') . ' (' . ($patient['emergency_contact_phone'] ?? '') . ')'
    ];

    return [
        'patient' => $patient,
        'bed' => $bed,
        'appointments' => $appointments,
        'prescriptions' => $prescriptions,
        'diagnoses' => $diagnoses,
        'files' => $files,
        'vitals' => $vitals,
        'online_consultations' => $onlineConsults,
        'summary_records' => [$summaryRow]
    ];
}

/**
 * Dedicated retriever for patient vital signs and bed observations chart.
 */
function getPatientVitalsDossier($conn, $hospitalId, $patientId) {
    $escId = $conn->real_escape_string($patientId);
    $query = "SELECT bo.id, bo.bed_number, bo.observed_at, bo.observation_hour, bo.vital_pulse, bo.vital_bp, bo.vital_spo2, bo.vital_temp, bo.vital_resp, bo.notes, bo.recorded_by_name, p.name AS pat_name, p.surname AS pat_surname 
        FROM bed_observations bo 
        JOIN patients p ON bo.patient_id = p.id 
        WHERE bo.patient_id = '$escId' 
        ORDER BY bo.observed_at DESC LIMIT 10";
    $res = $conn->query($query);
    $vitals = [];
    $summaryRecords = [];
    if ($res) {
        while ($v = $res->fetch_assoc()) {
            $vitals[] = $v;
            $summaryRecords[] = [
                'observed_at' => $v['observed_at'] ?: $v['observation_hour'],
                'bed_number' => $v['bed_number'],
                'bp' => $v['vital_bp'] ?: 'N/A',
                'pulse' => $v['vital_pulse'] ?: 'N/A',
                'spo2' => $v['vital_spo2'] ?: 'N/A',
                'temperature' => $v['vital_temp'] ?: 'N/A',
                'respiration' => $v['vital_resp'] ?: 'N/A',
                'notes' => $v['notes'] ?: 'Normal observation',
                'recorded_by' => $v['recorded_by_name'] ?: 'Duty Nurse'
            ];
        }
    }
    return [
        'vitals' => $vitals,
        'summary_records' => $summaryRecords,
        'count' => count($vitals)
    ];
}
?>
