<?php
// ai/sql_sanitizer.php

define('ALLOWED_READ_OPERATIONS', ['SELECT']);
define('ALLOWED_WRITE_OPERATIONS', ['INSERT', 'UPDATE']);
define('BLOCKED_OPERATIONS', ['DROP', 'ALTER', 'TRUNCATE', 'CREATE', 'GRANT', 'REVOKE', 'DELETE']);
define('ALLOWED_TABLES', ['patients', 'doctors', 'appointments', 'beds', 'departments', 'staff', 'staff_attendance', 'doctor_categories', 'doctor_day_schedules', 'doctor_slots', 'prescriptions', 'diagnoses', 'timeline_events', 'patient_files', 'system_state']);

function sanitizeReadQuery($sql, $hospitalId, $allowedTables) {
    $type = detectSqlType($sql);
    if ($type !== 'SELECT') {
        return ['valid' => false, 'error' => 'Only SELECT operations are allowed for read queries.'];
    }
    
    $tables = extractTablesFromSql($sql);
    foreach ($tables as $table) {
        if (!in_array($table, $allowedTables) || !in_array($table, ALLOWED_TABLES)) {
            return ['valid' => false, 'error' => "Access denied to table: $table"];
        }
    }
    
    $sql = removePasswordColumns($sql);
    
    // Auto-inject WHERE hospital_id = $hospitalId (Simplified regex approach)
    // In a real app, use a proper SQL parser. Here we do simple string manipulation.
    // If it doesn't have WHERE, add it before GROUP BY, ORDER BY, or LIMIT
    if (stripos($sql, 'WHERE') === false) {
        $insertStr = " WHERE hospital_id = $hospitalId ";
        if (preg_match('/\s(GROUP BY|ORDER BY|LIMIT)\s/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
            $sql = substr_replace($sql, $insertStr, $matches[0][1], 0);
        } else {
            $sql .= $insertStr;
        }
    } else {
        // Just append AND hospital_id =
        $sql = preg_replace('/\sWHERE\s/i', " WHERE hospital_id = $hospitalId AND ", $sql);
    }
    
    // Auto add limit
    if (stripos($sql, 'LIMIT') === false) {
        $sql .= " LIMIT 100";
    }
    
    return ['valid' => true, 'sql' => $sql];
}

function sanitizeWriteQuery($sql, $hospitalId, $allowedTables, $allowedOps) {
    $type = detectSqlType($sql);
    if (!in_array($type, ['INSERT', 'UPDATE'])) {
        return ['valid' => false, 'error' => 'Only INSERT and UPDATE operations are allowed.'];
    }
    
    $tables = extractTablesFromSql($sql);
    if (empty($tables)) {
         return ['valid' => false, 'error' => 'Could not determine target table.'];
    }
    $targetTable = $tables[0];
    
    // Ensure hospital_id is included - simplified check
    if (stripos($sql, 'hospital_id') === false) {
        return ['valid' => false, 'error' => 'Queries must include hospital_id for security.'];
    }
    
    return ['valid' => true, 'sql' => $sql];
}

function detectSqlType($sql) {
    $sql = trim($sql);
    $firstWord = strtoupper(strtok($sql, " \n\t\r"));
    if (in_array($firstWord, ['SELECT', 'INSERT', 'UPDATE', 'DELETE'])) {
        return $firstWord;
    }
    if (in_array($firstWord, ['CREATE', 'DROP', 'ALTER', 'TRUNCATE'])) {
        return 'DDL';
    }
    return 'UNKNOWN';
}

function extractTablesFromSql($sql) {
    $tables = [];
    // Extract after FROM or JOIN
    preg_match_all('/\b(?:FROM|JOIN|UPDATE|INTO)\s+`?([a-zA-Z0-9_]+)`?\b/i', $sql, $matches);
    if (!empty($matches[1])) {
        foreach ($matches[1] as $match) {
            $tables[] = strtolower($match);
        }
    }
    return array_unique($tables);
}

function removePasswordColumns($sql) {
    // Basic replace of 'password' or '*'. A proper parser is needed for robust removal.
    $sql = preg_replace('/\bpassword\b/i', "'' as password", $sql);
    return $sql;
}
