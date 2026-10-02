<?php
// ai/security_guard.php

define('BLOCKED_COLUMNS', ['password']);

function checkChatPermission($session) {
    return isset($session['hospital_id']) && !empty($session['hospital_id']);
}

function getUserRole($session) {
    if (isset($session['staff_role'])) {
        return $session['staff_role'];
    }
    return 'Unknown';
}

function getAllowedTables($role) {
    $role = strtolower($role);
    switch ($role) {
        case 'admin':
            return ['hospitals', 'patients', 'doctors', 'appointments', 'beds', 'departments', 'staff', 'staff_attendance', 'doctor_categories', 'doctor_day_schedules', 'doctor_slots', 'prescriptions', 'diagnoses', 'timeline_events', 'patient_files', 'system_state'];
        case 'nurse':
            return ['patients', 'appointments', 'beds', 'doctors', 'departments', 'prescriptions', 'diagnoses', 'timeline_events'];
        case 'receptionist':
            return ['patients', 'appointments', 'doctors', 'departments', 'beds'];
        case 'lab tech':
        case 'lab_tech':
            return ['patients', 'appointments', 'prescriptions', 'diagnoses', 'patient_files'];
        case 'rmo':
        case 'doctor':
            return ['patients', 'appointments', 'beds', 'doctors', 'departments', 'prescriptions', 'diagnoses', 'timeline_events', 'patient_files'];
        default:
            return ['patients', 'appointments', 'doctors', 'departments'];
    }
}

function getAllowedWriteOps($role) {
    $role = strtolower($role);
    switch ($role) {
        case 'admin':
            return ['INSERT', 'UPDATE']; // Except hospitals, system_state
        case 'receptionist':
            return ['INSERT' => ['patients', 'appointments'], 'UPDATE' => ['appointments']];
        case 'nurse':
            return ['UPDATE' => ['beds', 'appointments']];
        case 'rmo':
        case 'doctor':
            return ['INSERT' => ['prescriptions', 'diagnoses', 'appointments'], 'UPDATE' => ['prescriptions', 'diagnoses', 'appointments', 'beds']];
        default:
            return [];
    }
}

function canQueryTable($role, $tableName) {
    $allowed = getAllowedTables($role);
    // Allow admin everything (already listed in array)
    return in_array($tableName, $allowed);
}

function canWriteTable($role, $tableName, $operation) {
    $operation = strtoupper($operation);
    $ops = getAllowedWriteOps($role);
    
    if (strtolower($role) === 'admin') {
        if (in_array($tableName, ['hospitals', 'system_state'])) return false;
        return in_array($operation, $ops);
    }
    
    if (isset($ops[$operation])) {
        return in_array($tableName, $ops[$operation]);
    }
    
    return false;
}

function checkRateLimit($conn, $hospitalId, $staffId = null, $limit = 30) {
    if (!$conn || !($conn instanceof mysqli)) return true;
    $hospitalId = (int)$hospitalId;
    if ($hospitalId <= 0) return true;
    $maxLimit = (int)$limit > 0 ? (int)$limit : 30;

    try {
        $stmt = $conn->prepare("SELECT COUNT(*) as cnt FROM ai_chat_messages m JOIN ai_conversations c ON m.conversation_id = c.id WHERE c.hospital_id = ? AND m.role = 'user' AND m.created_at >= NOW() - INTERVAL 1 MINUTE");
        if ($stmt) {
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $result = $stmt->get_result()->fetch_assoc();
            $stmt->close();
            return ($result['cnt'] ?? 0) < $maxLimit;
        }
    } catch (Exception $e) {
        return true;
    }
    return true;
}
