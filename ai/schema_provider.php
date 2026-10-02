<?php
// ai/schema_provider.php

function getFullSchema($conn) {
    static $cachedSchema = null;
    if ($cachedSchema !== null) return $cachedSchema;
    
    $schema = "";
    $result = $conn->query("SHOW TABLES");
    if ($result) {
        while ($row = $result->fetch_array()) {
            $table = $row[0];
            $schema .= getTableDescription($conn, $table) . "\n\n";
        }
    }
    $cachedSchema = $schema;
    return $schema;
}

function getRelevantSchema($conn, $keywords) {
    $schemaMap = [
        'patients' => ['patient', 'register', 'name', 'phone'],
        'beds' => ['bed', 'ward', 'icu', 'occupied', 'available'],
        'doctors' => ['doctor', 'schedule', 'slot', 'specialt'],
        'doctor_day_schedules' => ['doctor', 'schedule', 'slot', 'specialt'],
        'doctor_slots' => ['doctor', 'schedule', 'slot', 'specialt'],
        'doctor_categories' => ['doctor', 'schedule', 'slot', 'specialt'],
        'staff' => ['staff', 'attendance', 'duty', 'shift', 'nurse'],
        'staff_attendance' => ['staff', 'attendance', 'duty', 'shift', 'nurse'],
        'appointments' => ['appointment', 'book', 'consult', 'queue'],
        'prescriptions' => ['prescription', 'medicine', 'drug', 'dosage'],
        'diagnoses' => ['prescription', 'medicine', 'drug', 'dosage'],
        'departments' => ['department'],
        'patient_files' => ['file', 'report', 'upload', 'document']
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
    }
    
    $matchedTables = array_unique($matchedTables);
    
    if (empty($matchedTables)) {
        // Return condensed schema of all tables
        $schema = "Condensed Schema:\n";
        $result = $conn->query("SHOW TABLES");
        if ($result) {
            while ($row = $result->fetch_array()) {
                $table = $row[0];
                $cols = [];
                $resCols = $conn->query("SHOW COLUMNS FROM `$table`");
                if ($resCols) {
                    while ($col = $resCols->fetch_assoc()) {
                        $cols[] = $col['Field'];
                    }
                }
                $schema .= "Table: $table (" . implode(', ', $cols) . ")\n";
            }
        }
        return $schema;
    }
    
    $schema = "";
    foreach ($matchedTables as $table) {
        $schema .= getTableDescription($conn, $table) . "\n\n";
    }
    return $schema;
}

function getTableDescription($conn, $tableName) {
    $stmt = $conn->prepare("SHOW COLUMNS FROM `" . $tableName . "`");
    if (!$stmt) return "Table $tableName not found.";
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $desc = "Table: $tableName\nColumns:\n";
    while ($row = $result->fetch_assoc()) {
        $desc .= "- " . $row['Field'] . " (" . $row['Type'] . ")";
        if ($row['Key'] == 'PRI') $desc .= " [PRIMARY KEY]";
        if ($row['Key'] == 'MUL') $desc .= " [FOREIGN KEY/INDEX]";
        $desc .= "\n";
    }
    return $desc;
}

function getSampleData($conn, $tableName, $hospitalId, $limit = 3) {
    // Check if table has hospital_id
    $resCols = $conn->query("SHOW COLUMNS FROM `$tableName` LIKE 'hospital_id'");
    $hasHospitalId = $resCols && $resCols->num_rows > 0;
    
    $sql = "SELECT * FROM `$tableName`";
    if ($hasHospitalId) {
        $sql .= " WHERE hospital_id = ?";
    }
    $sql .= " LIMIT ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) return "";
    
    if ($hasHospitalId) {
        $stmt->bind_param("ii", $hospitalId, $limit);
    } else {
        $stmt->bind_param("i", $limit);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $samples = [];
    while ($row = $result->fetch_assoc()) {
        if (isset($row['password'])) unset($row['password']);
        $samples[] = json_encode($row);
    }
    
    if (empty($samples)) return "No sample data available.";
    return "Sample Data for $tableName:\n" . implode("\n", $samples);
}
