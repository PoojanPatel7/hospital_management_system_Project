<?php
// ai/schema_provider.php

function getFullSchema($conn) {
    static $cachedSchema = null;
    if ($cachedSchema !== null) return $cachedSchema;
    
    $schema = "=== BHOOMA HOSPITAL DATABASE SCHEMA ===\n\n";
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $table = $row[0];
            $schema .= getTableDescription($conn, $table) . "\n";
        }
    }

    $schema .= "\n=== MASTER RELATIONAL DICTIONARY & FIELD SEMANTICS ===\n";
    $schema .= "1. PATIENTS (patients):\n";
    $schema .= "   • Primary Key: `id` (varchar, e.g. 'CP-2026-001' or 'PAT-101') -> Called 'Patient ID', 'MRN', 'Token Code', 'Patient Number'.\n";
    $schema .= "   • Fields: `name` (First Name), `surname` (Last Name/Family Name), `father_name` (Guardian), `phone` (Contact Number), `gender` ('Male'/'Female'), `blood_group` ('A+', 'B+', 'O+', etc.), `age` (Years, castable to integer), `emergency_contact_name`, `emergency_contact_phone`, `hospital_id`, `created_at`.\n";
    $schema .= "   • Relationships:\n";
    $schema .= "     - Linked to appointments: `appointments.patient_id = patients.id`\n";
    $schema .= "     - Linked to beds: `beds.patient_id = patients.id` (when patient is admitted to a bed)\n";
    $schema .= "     - Linked to files/reports: `patient_files.patient_id = patients.id`\n";
    $schema .= "\n2. DOCTORS (doctors):\n";
    $schema .= "   • Primary Key: `id` (varchar, e.g. 'doc-101') -> Called 'Doctor ID'.\n";
    $schema .= "   • Fields: `name` (Doctor Name e.g. 'Dr. Amit Patel'), `department_id` (FK to departments.id), `experience` (Years of practice), `degree` (Qualifications e.g. 'MBBS, MD Cardiology'), `phone`, `hospital_id`.\n";
    $schema .= "   • CRITICAL: NEVER query `created_at` or `specialty` column on doctors table! Specialty is `departments.name` joined via `doctors.department_id = departments.id`.\n";
    $schema .= "\n3. APPOINTMENTS (appointments):\n";
    $schema .= "   • Primary Key: `id` (int) -> Called 'Appointment ID', 'Booking ID', 'OPD Token Number'.\n";
    $schema .= "   • Fields: `patient_id` (FK to patients.id), `doctor_id` (FK to doctors.id), `date` (DATE - NEVER appointment_date!), `slot` (Time e.g. '09:30 AM'), `type` ('General Consultation', 'Follow-up', 'Emergency Case', 'Health Checkup'), `status` ('Scheduled', 'Waiting', 'In-Consultation', 'Completed', 'Cancelled', 'Admitted to Bed', 'Discharged from Bed'), `stage` (0=Booked, 1=Arrived, 2=Triage, 3=Consulting, 4=Pharmacy, 5=Admitted), `bed_number` (assigned bed if admitted), `symptoms`, `allergies`, `hospital_id`.\n";
    $schema .= "   • Relationships:\n";
    $schema .= "     - Prescriptions: `prescriptions.appointment_id = appointments.id`\n";
    $schema .= "     - Diagnoses: `diagnoses.appointment_id = appointments.id`\n";
    $schema .= "\n4. BEDS & WARDS (beds):\n";
    $schema .= "   • Primary Key: `id` (varchar), `bed_number` (Unique room/bed code e.g. 'Bed 101', 'ICU-01').\n";
    $schema .= "   • Fields: `type` ('ICU', 'General', 'Private', 'Semi-Private'), `wing` ('General Ward Floor', 'ICU Wing', 'Deluxe Wing'), `status` ('Available' or 'Occupied'), `patient_id` (FK to patients.id when occupied, NULL when vacant), `hospital_id`.\n";
    $schema .= "\n5. STAFF & ROSTER (staff):\n";
    $schema .= "   • Primary Key: `id` (int), `staff_code` (e.g. 'STF-101'), `first_name`, `last_name` (Full Name = `CONCAT(first_name, ' ', COALESCE(last_name, ''))`), `role` ('Staff Nurse', 'ICU Charge Nurse', 'Receptionist', 'Lab Technician', 'Pharmacist', 'Accountant', 'HR Executive', 'Security Guard', 'Housekeeping Supervisor'), `department`, `shift` ('Morning (08:00 - 16:00)', 'Evening (16:00 - 00:00)', 'Night (00:00 - 08:00)'), `status` ('Active'/'Inactive'), `hospital_id`.\n";
    $schema .= "\n6. STAFF ATTENDANCE (staff_attendance):\n";
    $schema .= "   • Primary Key: `id` (int), `staff_id` (FK to staff.id), `date` (DATE, today is CURDATE()), `status` ('Present', 'Absent', 'Late', 'Half Day', 'On Leave'), `check_in_time`, `check_out_time`, `working_hours`, `hospital_id`.\n";
    $schema .= "   • Attendance is NEVER stored in `staff` table! Always join `staff` with `staff_attendance`.\n";
    $schema .= "\n7. PRESCRIPTIONS & MEDICINES (prescriptions):\n";
    $schema .= "   • `appointment_id` (FK to appointments.id), `medicine_name` (Drug/Medicine e.g. 'Paracetamol 650mg'), `dosage`, `frequency`, `duration`, `instructions`.\n";
    $schema .= "   • No hospital_id column! Join through appointments.\n";
    $schema .= "\n8. CLINICAL DIAGNOSES (diagnoses):\n";
    $schema .= "   • `appointment_id` (FK to appointments.id), `description` (Medical condition, illness e.g. 'Acute Bronchitis', 'Hypertension').\n";
    $schema .= "   • No hospital_id column! Join through appointments.\n";
    $schema .= "\n9. PATIENT MEDICAL FILES & IMAGES (patient_files):\n";
    $schema .= "   • Primary Key: `id` (int), `patient_id` (FK to patients.id).\n";
    $schema .= "   • Fields: `title`, `file_name`, `mime_type` ('image/jpeg', 'image/png', 'application/pdf'), `file_size`, `file_path`, `category` ('Lab Report', 'Prescription', 'Radiology/X-Ray', 'Clinical Photo', 'Discharge Summary', 'Other'), `description`, `created_at`.\n";
    $schema .= "   • Access URL: `api/file.php?id={id}` (or `api/file.php?id={id}&thumb=1` for thumbnail).\n";
    $schema .= "\n10. INPATIENT BED OBSERVATIONS & VITALS (bed_observations):\n";
    $schema .= "   • Primary Key: `id` (int), `hospital_id` (int), `bed_number` (FK to beds.bed_number), `patient_id` (FK to patients.id), `appointment_id` (FK to appointments.id).\n";
    $schema .= "   • Fields: `observed_at` (datetime), `observation_hour` (e.g. '08:00 AM', '14:00'), `vital_pulse` (BPM e.g. '78 bpm'), `vital_bp` (Blood Pressure e.g. '120/80 mmHg'), `vital_spo2` (Oxygen Saturation e.g. '98%'), `vital_temp` (Body Temperature e.g. '98.6 F'), `vital_resp` (Respiration Rate e.g. '16 /min'), `notes` (Nurse/Clinical Notes), `recorded_by_id`, `recorded_by_name`, `created_at`.\n";
    $schema .= "   • Relational Link: `bed_observations.patient_id = patients.id` AND `bed_observations.bed_number = beds.bed_number`.\n";
    $schema .= "\n11. BED OBSERVATION IMAGES & MONITOR SNAPSHOTS (bed_observation_images):\n";
    $schema .= "   • Primary Key: `id` (int), `observation_id` (FK to bed_observations.id), `bed_number`, `patient_id`, `appointment_id`, `title`, `image_path`, `thumbnail_path`, `mime_type`, `file_size`, `captured_at`, `patient_file_id`, `created_at`.\n";
    $schema .= "   • Purpose: ICU monitor telemetry snapshots, ECG recordings, bed wound images.\n";
    $schema .= "\n12. CONSULTATION CHARGES & TARIFF CATALOG (consultation_charges):\n";
    $schema .= "   • Primary Key: `id` (int), `hospital_id` (int), `charge_type` ('general', 'specialist', 'emergency', 'online', etc.), `charge_name` (Fee description e.g. 'General OPD Consultation Fee', 'Super Specialist Video Consultation'), `amount` (decimal e.g. 500.00), `is_active` (1/0).\n";
    $schema .= "   • Purpose: Official hospital consultation fees, pricing catalog, tariff schedule.\n";
    $schema .= "\n13. ONLINE CONSULTATIONS & TELEMEDICINE (online_consultations):\n";
    $schema .= "   • Primary Key: `id` (int), `hospital_id` (int), `patient_id` (FK to patients.id), `doctor_id` (FK to doctors.id), `appointment_id` (FK to appointments.id).\n";
    $schema .= "   • Fields: `status` ('initiated', 'in_progress', 'completed', 'cancelled'), `diagnosis`, `prescription_notes`, `doctor_notes`, `consultation_fee`, `fee_status` ('pending', 'paid', 'waived'), `started_at`, `completed_at`.\n";
    $schema .= "   • Purpose: Virtual tele-health video/audio sessions between doctor and remote patient.\n";
    $schema .= "\n14. MEDICAL FILE AUDIT LOG (file_audit_log):\n";
    $schema .= "   • Primary Key: `id` (int), `hospital_id` (int), `patient_id` (FK to patients.id), `file_id` (FK to patient_files.id), `action` ('upload', 'view', 'edit', 'delete', 'download'), `staff_id`, `staff_name`, `details`, `ip_address`, `created_at`.\n";
    $schema .= "   • Purpose: HIPAA and regulatory audit trail tracking every staff interaction with patient medical records.\n";
    $schema .= "\n15. HOSPITAL GEOFENCE & ATTENDANCE BOUNDARIES (hospital_geofence):\n";
    $schema .= "   • Primary Key: `id` (int), `hospital_id` (int), `latitude` (decimal), `longitude` (decimal), `radius_meters` (int, default 100), `name` (e.g. 'Main Hospital Campus'), `created_at`.\n";
    $schema .= "   • Purpose: GPS Geofencing perimeter used to validate mobile/biometric staff clock-ins.\n";
    $schema .= "\n16. STAFF MODULE PERMISSIONS (staff_permissions):\n";
    $schema .= "   • Primary Key: `id` (int), `staff_id` (FK to staff.id), `permission_key` (varchar e.g. 'manage_patients', 'view_reports', 'edit_beds'), `permission_value` (1/0), `granted_by`, `granted_at`.\n";
    $schema .= "   • Purpose: Granular role-based access control matrix per staff member.\n";
    $schema .= "\n=== CANONICAL HOSPITAL SQL TEMPLATES ===\n";
    $schema .= "• Full Patient Profile: SELECT p.*, b.bed_number, b.type AS bed_type, b.wing AS bed_wing FROM patients p LEFT JOIN beds b ON p.id = b.patient_id WHERE p.id = ? AND p.hospital_id = ?;\n";
    $schema .= "• Patient Appointments History: SELECT a.id, a.date, a.slot, a.type, a.status, d.name AS doctor_name FROM appointments a LEFT JOIN doctors d ON a.doctor_id = d.id WHERE a.patient_id = ? AND a.hospital_id = ? ORDER BY a.date DESC;\n";
    $schema .= "• Patient Prescriptions: SELECT pr.medicine_name, pr.dosage, pr.frequency, pr.duration, pr.instructions FROM prescriptions pr JOIN appointments a ON pr.appointment_id = a.id WHERE a.patient_id = ? AND a.hospital_id = ?;\n";
    $schema .= "• Patient Files & Images: SELECT pf.id, pf.file_name, pf.category, pf.mime_type, pf.file_size, pf.created_at FROM patient_files pf WHERE pf.patient_id = ? ORDER BY pf.id DESC;\n";
    $schema .= "• Patient Bed Vitals & Observations: SELECT bo.observed_at, bo.observation_hour, bo.vital_pulse, bo.vital_bp, bo.vital_spo2, bo.vital_temp, bo.vital_resp, bo.notes, bo.recorded_by_name FROM bed_observations bo WHERE bo.patient_id = ? AND bo.hospital_id = ? ORDER BY bo.observed_at DESC LIMIT 10;\n";
    $schema .= "• Hospital Consultation Pricing: SELECT charge_type, charge_name, amount FROM consultation_charges WHERE hospital_id = ? AND is_active = 1;\n";
    $schema .= "• Online Consultations Summary: SELECT oc.id, oc.patient_id, oc.doctor_id, oc.status, oc.consultation_fee, oc.fee_status, oc.started_at, p.name AS patient_name, d.name AS doctor_name FROM online_consultations oc LEFT JOIN patients p ON oc.patient_id = p.id LEFT JOIN doctors d ON oc.doctor_id = d.id WHERE oc.hospital_id = ? ORDER BY oc.started_at DESC;\n";
    $schema .= "• Today's Staff On Duty: SELECT s.first_name, s.last_name, s.role, s.department, sa.status, sa.check_in_time FROM staff s JOIN staff_attendance sa ON s.id = sa.staff_id WHERE s.hospital_id = ? AND sa.date = CURDATE() AND sa.status = 'Present';\n";
    $schema .= "• Currently Admitted Inpatients: SELECT p.id, p.name, p.surname, p.gender, p.age, b.bed_number, b.type AS bed_type, b.wing FROM beds b JOIN patients p ON b.patient_id = p.id WHERE b.hospital_id = ? AND b.status = 'Occupied';\n";

    $cachedSchema = $schema;
    return $schema;
}

function getRelevantSchema($conn, $keywords) {
    $schemaMap = [
        'patients' => ['patient', 'pateint', 'patiant', 'pecent', 'sick', 'admit', 'ipd', 'opd', 'register', 'blood', 'blood group', 'bloodgroup', 'a+', 'a-', 'b+', 'b-', 'ab+', 'ab-', 'o+', 'o-', 'male', 'female', 'men', 'women', 'donor', 'demographic', 'age', 'gender', 'phone', 'surname', 'contact', 'mareez', 'bimar', 'info', 'details', 'full info', 'history'],
        'beds' => ['bed', 'bedd', 'baid', 'ward', 'icu', 'occupied', 'available', 'empty', 'allot', 'room', 'wing', 'admit', 'kamra', 'khatiya', 'palang', 'vacant', 'admitted', 'inpatient'],
        'bed_observations' => ['vital', 'vitals', 'bp', 'blood pressure', 'pulse', 'heart rate', 'spo2', 'oxygen', 'temperature', 'temp', 'fever', 'respiration', 'resp', 'observation', 'vitals chart', 'hourly vitals', 'monitor', 'nurse note'],
        'bed_observation_images' => ['monitor image', 'ecg image', 'wound photo', 'observation picture', 'monitor screenshot', 'bed picture'],
        'doctors' => ['doctor', 'docter', 'doctar', 'dr', 'doc', 'surgeon', 'physician', 'specialist', 'experience', 'degree', 'schedule', 'slot', 'opd', 'vaidya', 'fees', 'fee'],
        'doctor_day_schedules' => ['schedule', 'timing', 'available', 'day', 'break', 'slot'],
        'doctor_slots' => ['slot', 'time_slot', 'booking_time', 'time'],
        'doctor_categories' => ['category', 'specialty', 'speciality'],
        'staff' => ['staff', 'staf', 'stff', 'nurse', 'nurce', 'employee', 'worker', 'receptionist', 'duty', 'shift', 'salary', 'sister', 'compounder', 'karamchari'],
        'staff_attendance' => ['attendance', 'present', 'absent', 'late', 'duty', 'checkin', 'checkout', 'haziri', 'on duty', 'selfie', 'face verify'],
        'staff_permissions' => ['permission', 'permissions', 'access rights', 'role permission', 'module access', 'allowed modules'],
        'hospital_geofence' => ['geofence', 'location', 'gps', 'radius', 'coordinates', 'lat', 'lng', 'attendance location', 'campus boundary'],
        'consultation_charges' => ['charge', 'charges', 'fee', 'fees', 'cost', 'pricing', 'tariff', 'price', 'rate', 'opd fee', 'consultation price', 'rates'],
        'online_consultations' => ['online consultation', 'video call', 'telemedicine', 'virtual consultation', 'remote consult', 'tele-consult', 'video consult'],
        'file_audit_log' => ['file audit', 'file history', 'file access', 'who viewed file', 'download log', 'who downloaded'],
        'appointments' => ['appointment', 'apointment', 'appoinment', 'book', 'booking', 'consult', 'queue', 'visit', 'token', 'slot', 'parchi', 'slip', 'stage', 'schedule'],
        'prescriptions' => ['prescription', 'prescribtion', 'medicine', 'medisin', 'drug', 'dawa', 'dosage', 'frequency', 'duration', 'goli', 'tablet', 'dawakhana', 'meds'],
        'diagnoses' => ['diagnosis', 'diagnose', 'disease', 'illness', 'symptom', 'condition', 'allergies'],
        'departments' => ['department', 'deparment', 'dept', 'specialty', 'wing', 'cardio', 'ortho', 'pediatric', 'icu'],
        'patient_files' => ['file', 'files', 'report', 'reports', 'upload', 'document', 'documents', 'test', 'scan', 'scans', 'lab', 'image', 'images', 'photo', 'photos', 'pic', 'pics', 'picture', 'pictures', 'x-ray', 'xray', 'attachment', 'attachments'],
        'hospitals' => ['hospital', 'hosptial', 'clinic', 'hospital_id', 'hosp', 'about']
    ];

    $matchedTables = [];
    $lowerKeywords = strtolower($keywords);
    
    foreach ($schemaMap as $table => $words) {
        foreach ($words as $word) {
            if (strpos($lowerKeywords, $word) !== false) {
                $matchedTables[] = $table;
                break;
            }
        }
    }
    
    // Always bundle core clinical relations together
    if (in_array('appointments', $matchedTables) || in_array('prescriptions', $matchedTables) || in_array('diagnoses', $matchedTables) || strpos($lowerKeywords, 'info') !== false) {
        if (!in_array('patients', $matchedTables)) $matchedTables[] = 'patients';
        if (!in_array('appointments', $matchedTables)) $matchedTables[] = 'appointments';
        if (!in_array('doctors', $matchedTables)) $matchedTables[] = 'doctors';
        if (!in_array('beds', $matchedTables)) $matchedTables[] = 'beds';
        if (!in_array('prescriptions', $matchedTables)) $matchedTables[] = 'prescriptions';
        if (!in_array('diagnoses', $matchedTables)) $matchedTables[] = 'diagnoses';
        if (!in_array('departments', $matchedTables)) $matchedTables[] = 'departments';
    }
    if (in_array('beds', $matchedTables) || in_array('bed_observations', $matchedTables)) {
        if (!in_array('beds', $matchedTables)) $matchedTables[] = 'beds';
        if (!in_array('bed_observations', $matchedTables)) $matchedTables[] = 'bed_observations';
    }
    if (in_array('doctors', $matchedTables)) {
        if (!in_array('departments', $matchedTables)) $matchedTables[] = 'departments';
    }
    if (in_array('staff', $matchedTables) || in_array('staff_attendance', $matchedTables) || in_array('staff_permissions', $matchedTables)) {
        if (!in_array('staff', $matchedTables)) $matchedTables[] = 'staff';
        if (!in_array('staff_attendance', $matchedTables)) $matchedTables[] = 'staff_attendance';
    }
    
    $matchedTables = array_unique($matchedTables);
    
    // If few or no specific tables matched, provide the full comprehensive schema
    if (count($matchedTables) < 2) {
        return getFullSchema($conn);
    }
    
    $schema = "=== RELEVANT DATABASE TABLES ===\n\n";
    foreach ($matchedTables as $table) {
        $schema .= getTableDescription($conn, $table) . "\n";
    }

    $schema .= "\n=== CRITICAL COLUMN & FOREIGN KEY RULES ===\n";
    $schema .= "- In `patients`: Primary key is `id` (e.g. 'CP-2026-001'), `name` is First Name, `surname` is Last Name. Contact is `phone`.\n";
    $schema .= "- In `doctors`: id, name, department_id, experience, degree, hospital_id, phone (NO created_at column!)\n";
    $schema .= "- In `appointments`: column name is `date` (NOT appointment_date). `patient_id` = patients.id, `doctor_id` = doctors.id.\n";
    $schema .= "- In `beds`: status is 'Available' or 'Occupied'. When occupied, `patient_id` references patients.id.\n";
    $schema .= "- In `bed_observations`: `patient_id` = patients.id, `bed_number` = beds.bed_number, columns: `vital_pulse`, `vital_bp`, `vital_spo2`, `vital_temp`, `vital_resp`, `notes`.\n";
    $schema .= "- In `consultation_charges`: columns: `charge_type`, `charge_name`, `amount`, `is_active`, `hospital_id`.\n";
    $schema .= "- In `online_consultations`: `patient_id` = patients.id, `doctor_id` = doctors.id, `status` ('initiated', 'in_progress', 'completed', 'cancelled'), `consultation_fee`.\n";
    $schema .= "- In `staff`: Primary key is `id` (NOT staff_id). Code is `staff_code`. status is 'Active' or 'Inactive'.\n";
    $schema .= "- In `staff_attendance`: staff_id references staff.id. status is 'Present', 'Absent', 'Late', 'Half Day', 'On Leave'. Attendance is NEVER in `staff` table!\n";
    $schema .= "- In `prescriptions` & `diagnoses`: Linked via `appointment_id = appointments.id` (NO hospital_id column in these two).\n";

    return $schema;
}

function getTableDescription($conn, $tableName) {
    $stmt = $conn->prepare("SHOW COLUMNS FROM `" . $tableName . "`");
    if (!$stmt) return "Table $tableName not found.";
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $desc = "Table: $tableName\nColumns: ";
    $cols = [];
    while ($row = $result->fetch_assoc()) {
        $colStr = $row['Field'] . " (" . $row['Type'] . ")";
        if ($row['Key'] == 'PRI') $colStr .= " [PK]";
        if ($row['Key'] == 'MUL') $colStr .= " [FK/INDEX]";
        $cols[] = $colStr;
    }
    $desc .= implode(', ', $cols) . "\n";
    return $desc;
}
