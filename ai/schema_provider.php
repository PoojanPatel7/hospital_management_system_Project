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

    $schema .= "\n=== KEY TABLE RELATIONSHIPS & STRICT RULES ===\n";
    $schema .= "1. doctors (id, name, department_id, experience, degree, hospital_id, phone) -> NO created_at column!\n";
    $schema .= "2. departments (id, name, icon, hospital_id) -> doctors.department_id = departments.id\n";
    $schema .= "3. patients (id, name, surname, father_name, phone, demographics, gender, blood_group, age, hospital_id, created_at)\n";
    $schema .= "4. appointments (id, patient_id, doctor_id, type, date, slot, symptoms, allergies, status, stage, bed_number, created_at, hospital_id)\n";
    $schema .= "   - Note: column name is `date` (NOT `appointment_date`).\n";
    $schema .= "   - appointments.patient_id = patients.id, appointments.doctor_id = doctors.id\n";
    $schema .= "5. beds (id, bed_number, type, wing, status, patient_id, hospital_id) -> status is 'Available' or 'Occupied'\n";
    $schema .= "6. staff (id, hospital_id, staff_code, first_name, last_name, role, department, shift, status, phone, email)\n";
    $schema .= "7. staff_attendance (id, hospital_id, staff_id, date, status, check_in_time, check_out_time)\n";
    $schema .= "8. prescriptions (id, appointment_id, medicine_name, dosage, frequency, duration, instructions)\n";
    $schema .= "9. diagnoses (id, appointment_id, description)\n";
    $schema .= "10. ALWAYS filter with `WHERE hospital_id = ?` for every hospital table.\n";

    $cachedSchema = $schema;
    return $schema;
}

function getRelevantSchema($conn, $keywords) {
    $schemaMap = [
        'patients' => ['patient', 'pateint', 'patiant', 'pecent', 'sick', 'admit', 'ipd', 'opd', 'register', 'blood', 'demographic', 'age', 'gender', 'phone', 'surname', 'contact'],
        'beds' => ['bed', 'bedd', 'baid', 'ward', 'icu', 'occupied', 'available', 'empty', 'allot', 'room', 'wing', 'admit'],
        'doctors' => ['doctor', 'docter', 'doctar', 'dr', 'doc', 'surgeon', 'physician', 'specialist', 'experience', 'degree', 'schedule', 'slot', 'opd'],
        'doctor_day_schedules' => ['schedule', 'timing', 'available', 'day', 'break', 'slot'],
        'doctor_slots' => ['slot', 'time_slot', 'booking_time', 'time'],
        'doctor_categories' => ['category', 'specialty', 'speciality'],
        'staff' => ['staff', 'staf', 'stff', 'nurse', 'nurce', 'employee', 'worker', 'receptionist', 'duty', 'shift', 'salary'],
        'staff_attendance' => ['attendance', 'present', 'absent', 'late', 'duty', 'checkin', 'checkout'],
        'appointments' => ['appointment', 'apointment', 'appoinment', 'book', 'booking', 'consult', 'queue', 'visit', 'token', 'slot'],
        'prescriptions' => ['prescription', 'prescribtion', 'medicine', 'medisin', 'drug', 'dawa', 'dosage', 'frequency', 'duration'],
        'diagnoses' => ['diagnosis', 'diagnose', 'disease', 'illness', 'symptom', 'condition', 'allergies'],
        'departments' => ['department', 'deparment', 'dept', 'specialty', 'wing', 'cardio', 'ortho', 'pediatric', 'icu'],
        'patient_files' => ['file', 'report', 'upload', 'document', 'test', 'scan', 'lab'],
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
    
    if (in_array('appointments', $matchedTables)) {
        if (!in_array('patients', $matchedTables)) $matchedTables[] = 'patients';
        if (!in_array('doctors', $matchedTables)) $matchedTables[] = 'doctors';
        if (!in_array('departments', $matchedTables)) $matchedTables[] = 'departments';
    }
    if (in_array('doctors', $matchedTables)) {
        if (!in_array('departments', $matchedTables)) $matchedTables[] = 'departments';
    }
    if (in_array('prescriptions', $matchedTables) || in_array('diagnoses', $matchedTables)) {
        if (!in_array('appointments', $matchedTables)) $matchedTables[] = 'appointments';
        if (!in_array('patients', $matchedTables)) $matchedTables[] = 'patients';
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

    $schema .= "\n=== COLUMN RULES ===\n";
    $schema .= "- In `doctors`: id, name, department_id, experience, degree, hospital_id, phone (NO created_at column!)\n";
    $schema .= "- In `appointments`: column name is `date` (NOT appointment_date).\n";
    $schema .= "- In `beds`: status is 'Available' or 'Occupied'.\n";

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
