<?php
/**
 * Permission checking middleware for the Hospital Management System.
 * Include this file in any API/page that needs permission enforcement.
 * 
 * Usage:
 *   require_once __DIR__ . '/permission_check.php';
 *   requirePermission($conn, 'can_upload');  // Returns JSON error & exits if no permission
 *   
 *   // Or check silently:
 *   if (hasPermission($conn, 'can_view_files')) { ... }
 */

function hasPermission($conn, $permissionKey) {
    // Admin (hospital account) always has all permissions
    $isAdmin = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin')
        || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'])
        || (!isset($_SESSION['staff_id']) && isset($_SESSION['hospital_id']));
    if ($isAdmin) {
        return true;
    }
    
    $staffId = $_SESSION['staff_id'] ?? 0;
    if (!$staffId) return false;
    
    // Check account status first
    $stmt = $conn->prepare("SELECT account_status FROM staff WHERE id = ?");
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $res = $stmt->get_result();
    $staff = $res->fetch_assoc();
    
    if ($staff && array_key_exists('account_status', $staff)) {
        if (strtolower($staff['account_status'] ?? '') !== 'active') {
            return false;
        }
    }
    
    if (function_exists('checkStaffPermission')) {
        return checkStaffPermission($conn, $staffId, $permissionKey);
    }
    
    return true;
}

function requirePermission($conn, $permissionKey, $errorMessage = null) {
    if (!hasPermission($conn, $permissionKey)) {
        $msg = $errorMessage ?: "Permission denied. You do not have '$permissionKey' access.";
        header('Content-Type: application/json');
        echo json_encode(['status' => 'error', 'message' => $msg, 'code' => 'PERMISSION_DENIED']);
        exit;
    }
}
