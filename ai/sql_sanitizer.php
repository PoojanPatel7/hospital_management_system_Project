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

function sanitizeReadQuery($sql, $hospitalId, $allowedTables, $userQuery = '') {
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

    // Auto-correct common LLM schema misconceptions:
    // 1. patients table PK is `id`, NOT `patient_id`
    $sql = preg_replace('/\b(patients|p|pat)\.patient_id\b/i', '${1}.id', $sql);
    
    // 2. doctors table PK is `id`, NOT `doctor_id`
    $sql = preg_replace('/\b(doctors|d|doc)\.doctor_id\b/i', '${1}.id', $sql);
    
    // 3. departments table PK is `id`, NOT `department_id`
    $sql = preg_replace('/\b(departments|dept|dep)\.department_id\b/i', '${1}.id', $sql);
    
    // 4. appointments table PK is `id`, NOT `appointment_id`
    // (Only target appointments table aliases, NEVER prescriptions or diagnoses)
    $sql = preg_replace('/\b(appointments|a|app|appt)\.appointment_id\b/i', '${1}.id', $sql);

    // 5. appointments bed reference is `bed_number`, NOT `bed_id`
    $sql = preg_replace('/\b(appointments|a|app|appt)\.bed_id\b/i', '${1}.bed_number', $sql);
    // Fix joins between appointments and beds:
    $sql = preg_replace('/\b(a|app|appt|appointments)\.bed_number\s*=\s*(b|bed|beds)\.id\b/i', '${1}.bed_number = ${2}.bed_number', $sql);
    $sql = preg_replace('/\b(b|bed|beds)\.id\s*=\s*(a|app|appt|appointments)\.bed_number\b/i', '${2}.bed_number = ${1}.bed_number', $sql);
    $sql = preg_replace('/\b(b|bed|beds)\.patient_id\s*=\s*(p|patients|pat)\.patient_id\b/i', '${1}.patient_id = ${2}.id', $sql);

    // 6. staff table PK is `id`, NOT `staff_id` (Only target staff table aliases, NEVER staff_attendance)
    $sql = preg_replace('/\b(staff|s)\.staff_id\b/i', '${1}.id', $sql);

    // 7. appointments date column is `date`, not `appointment_date`
    $sql = preg_replace('/\b(appointments|a|app|appt)\.appointment_date\b/i', '${1}.date', $sql);
    $sql = preg_replace('/(?<![\.a-zA-Z0-9_])appointment_date\b/i', 'date', $sql);

    // 8. Fix varchar age column sorting to numeric
    $sql = preg_replace('/\bORDER\s+BY\s+([a-zA-Z0-9_]+\.)?age\s+(DESC|ASC)\b/i', 'ORDER BY CAST(${1}age AS UNSIGNED) $2', $sql);
    $sql = preg_replace('/\b(MAX|MIN)\(\s*([a-zA-Z0-9_]+\.)?age\s*\)/i', '${1}(CAST(${2}age AS UNSIGNED))', $sql);
    
    // Auto-fix inverted sorting direction if user query clearly intended oldest vs youngest
    if (!empty($userQuery)) {
        if (preg_match('/\b(oldest|oledst|eldest|maximum age|highest age|most aged)\b/i', $userQuery)) {
            $sql = preg_replace('/(ORDER\s+BY\s+CAST\([a-zA-Z0-9_.]*age\s+AS\s+UNSIGNED\))\s+ASC\b/i', '$1 DESC', $sql);
        } elseif (preg_match('/\b(youngest|smallest child|lowest age|minimum age)\b/i', $userQuery)) {
            $sql = preg_replace('/(ORDER\s+BY\s+CAST\([a-zA-Z0-9_.]*age\s+AS\s+UNSIGNED\))\s+DESC\b/i', '$1 ASC', $sql);
        }
    }
    
    // 9. staff table has first_name and last_name instead of name
    if (stripos($sql, 'staff') !== false) {
        $sql = preg_replace('/\b(staff|s)\.name\b/i', "CONCAT(\${1}.first_name, ' ', COALESCE(\${1}.last_name, '')) AS name", $sql);
        if (stripos($sql, 'patients') === false) {
            $sql = preg_replace('/\b(?<![\.a-zA-Z0-9_])name\b/i', "CONCAT(first_name, ' ', COALESCE(last_name, '')) AS name", $sql);
        }
    }

    // 10. doctors table has NO created_at column
    if (stripos($sql, 'doctors') !== false || stripos($sql, '`doctors`') !== false) {
        $sql = preg_replace('/\b(doctors|d|doc)\.created_at\b/i', '${1}.id', $sql);
    }

    // 11. If querying staff table with status = 'Present'/'Absent' without joining staff_attendance:
    if (preg_match('/FROM\s+`?staff`?\s*(?:as\s+)?([a-zA-Z0-9_]+)?\s+WHERE/i', $sql, $tblM) && 
        preg_match('/(?:[a-zA-Z0-9_]+\.)?status\s*=\s*[\'"](Present|Absent|Late|Half Day|On Leave)[\'"]/i', $sql) && 
        stripos($sql, 'staff_attendance') === false) {
        $alias = !empty($tblM[1]) ? $tblM[1] : 's';
        $sql = preg_replace('/FROM\s+`?staff`?\s*(?:as\s+)?(?:[a-zA-Z0-9_]+)?\s+WHERE/i', "FROM staff $alias JOIN staff_attendance sa ON $alias.id = sa.staff_id WHERE sa.date = CURDATE() AND ", $sql);
        $sql = preg_replace('/\b(?:' . preg_quote($alias) . '\.)?status\s*=\s*[\'"](Present|Absent|Late|Half Day|On Leave)[\'"]/i', "sa.status = '$1'", $sql);
        $sql = preg_replace('/\b(?<![\.a-zA-Z0-9_])id\b/i', "$alias.id", $sql);
        $sql = preg_replace('/\b(?<![\.a-zA-Z0-9_])status\b/i', "sa.status", $sql);
        $sql = preg_replace('/\b(?<![\.a-zA-Z0-9_])hospital_id\b/i', "$alias.hospital_id", $sql);
    }
    
    // Auto-replace placeholder hospital_id = ? with real ID
    $sql = preg_replace('/hospital_id\s*=\s*[\'"]?\?[\'"]?/i', "hospital_id = $hospitalId", $sql);

    // Tables that actually contain hospital_id:
    $tablesWithHospitalId = ['hospitals', 'patients', 'doctors', 'appointments', 'beds', 'departments', 'staff', 'staff_attendance'];

    // Auto-inject WHERE hospital_id = $hospitalId if not present
    if (stripos($sql, 'hospital_id') === false) {
        // Find which table/alias in the query actually has hospital_id
        $hIdCol = null;
        if (preg_match_all('/\b(?:FROM|JOIN)\s+`?([a-zA-Z0-9_]+)`?\s+(?:AS\s+)?([a-zA-Z0-9_]+)?\b/i', $sql, $tblMatches, PREG_SET_ORDER)) {
            foreach ($tblMatches as $m) {
                $tName = strtolower($m[1]);
                $tAlias = !empty($m[2]) ? $m[2] : $m[1];
                if (in_array($tName, $tablesWithHospitalId)) {
                    $hIdCol = (count($tblMatches) > 1) ? "$tAlias.hospital_id" : "hospital_id";
                    break;
                }
            }
        }

        if ($hIdCol !== null) {
            if (stripos($sql, 'WHERE') === false) {
                $insertStr = " WHERE $hIdCol = $hospitalId ";
                if (preg_match('/\s(GROUP BY|ORDER BY|LIMIT)\s/i', $sql, $matches, PREG_OFFSET_CAPTURE)) {
                    $sql = substr_replace($sql, $insertStr, $matches[0][1], 0);
                } else {
                    $sql .= $insertStr;
                }
            } else {
                $sql = preg_replace('/\sWHERE\s/i', " WHERE $hIdCol = $hospitalId AND ", $sql, 1);
            }
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
