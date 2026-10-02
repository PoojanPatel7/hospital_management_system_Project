<?php
// ai/sql_sanitizer.php

define('ALLOWED_READ_OPERATIONS', ['SELECT']);
define('ALLOWED_WRITE_OPERATIONS', ['INSERT', 'UPDATE']);
define('BLOCKED_OPERATIONS', ['DROP', 'ALTER', 'TRUNCATE', 'CREATE', 'GRANT', 'REVOKE', 'DELETE']);
define('ALLOWED_TABLES', [
    'hospitals', 'patients', 'doctors', 'appointments', 'beds', 'departments',
    'staff', 'staff_attendance', 'doctor_categories', 'doctor_day_schedules',
    'doctor_slots', 'prescriptions', 'diagnoses', 'timeline_events', 'patient_files',
    'system_state', 'ai_config', 'ai_action_log', 'ai_conversations',
    'ai_chat_messages', 'ai_pending_actions'
]);

function sanitizeReadQuery($sql, $hospitalId, $allowedTables) {
    global $conn;
    $type = detectSqlType($sql);
    if ($type !== 'SELECT') {
        return ['valid' => false, 'error' => 'Only SELECT operations are allowed for read queries.'];
    }
    
    $tables = extractTablesFromSql($sql);
    foreach ($tables as $table) {
        $cleanTable = strtolower($table);
        $isAllowed = in_array($cleanTable, array_map('strtolower', $allowedTables)) || 
                     in_array($cleanTable, ALLOWED_TABLES);
        if (!$isAllowed && isset($conn) && $conn instanceof mysqli) {
            $tblCheck = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($cleanTable) . "'");
            if ($tblCheck && $tblCheck->num_rows > 0) {
                $isAllowed = true;
            }
        }
        if (!$isAllowed) {
            return ['valid' => false, 'error' => "Access denied to table: $cleanTable"];
        }
    }
    
    $sql = removePasswordColumns($sql);
    $sql = rtrim(trim($sql), "; \t\n\r\0\x0B");
    
    // Auto-inject WHERE hospital_id = $hospitalId if not present
    if (stripos($sql, 'hospital_id') === false) {
        if (stripos($sql, 'WHERE') === false) {
            $insertStr = " WHERE hospital_id = $hospitalId ";
            if (preg_match('/\s(GROUP BY|ORDER BY|LIMIT)\s/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
                $sql = substr_replace($sql, $insertStr, $matches[0][1], 0);
            } else {
                $sql .= $insertStr;
            }
        } else {
            $sql = preg_replace('/\sWHERE\s/i', " WHERE hospital_id = $hospitalId AND ", $sql, 1);
        }
    }
    
    // Auto add limit
    if (stripos($sql, 'LIMIT') === false && stripos($sql, 'COUNT(') === false) {
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
