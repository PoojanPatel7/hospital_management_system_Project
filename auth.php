<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/permission_check.php';

// Exclude public/unauthenticated pages from redirection loop
$currentPage = basename($_SERVER['PHP_SELF']);
$allowedPages = [
    'login.php', 
    'login_backup.php',
    'register_hospital.php', 
    'register_hospital_backup.php',
    'book.php', 
    'book_appointment.php', 
    'patient_book.php', 
    'patient_booking.php', 
    'patient_profile_qr.php',
    'patient_file_view.php'
];

if (!in_array($currentPage, $allowedPages)) {
    if (!isset($_SESSION['hospital_id'])) {
        header("Location: login.php");
        exit;
    }
}

// -------------------------------------------------------------
// Role Helper Flags & Main Top User Setup
// -------------------------------------------------------------
$isStaffUser = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'staff') || isset($_SESSION['staff_id']);
$isAdminUser = !$isStaffUser && isset($_SESSION['hospital_id']);

if ($isAdminUser) {
    if (empty($_SESSION['username'])) {
        $hId = (int)$_SESSION['hospital_id'];
        $hRes = $conn->query("SELECT username, name FROM hospitals WHERE id = $hId");
        if ($hRes && $hRow = $hRes->fetch_assoc()) {
            $_SESSION['username'] = $hRow['username'] ?: 'Gopalbhai';
            if (empty($_SESSION['hospital_name'])) $_SESSION['hospital_name'] = $hRow['name'];
        } else {
            $_SESSION['username'] = 'Gopalbhai';
        }
    }
    $_SESSION['user_full_name'] = $_SESSION['username'] ?? 'Gopalbhai';
    $_SESSION['role_title'] = 'Hospital Owner & Super Administrator';
}

if (!function_exists('isAdmin')) {
    function isAdmin() {
        global $isAdminUser;
        return $isAdminUser;
    }
}

if (!function_exists('isStaff')) {
    function isStaff() {
        global $isStaffUser;
        return $isStaffUser;
    }
}

if (!function_exists('getLoggedInUserName')) {
    function getLoggedInUserName() {
        global $isAdminUser;
        if ($isAdminUser) {
            return $_SESSION['username'] ?? 'Gopalbhai';
        }
        return $_SESSION['staff_name'] ?? 'Staff Member';
    }
}

if (!function_exists('getLoggedInUserRole')) {
    function getLoggedInUserRole() {
        global $isAdminUser;
        if ($isAdminUser) {
            return 'Hospital Owner & Super Administrator';
        }
        return $_SESSION['staff_role'] ?? 'Staff';
    }
}

// -------------------------------------------------------------
// For Staff Accounts: Live Database Security & Status Check
// -------------------------------------------------------------
if ($isStaffUser && isset($_SESSION['staff_id'])) {
    $staffId = (int)$_SESSION['staff_id'];
    $stfCheck = $conn->prepare("SELECT status, account_status, first_name, last_name, role FROM staff WHERE id = ?");
    if ($stfCheck) {
        $stfCheck->bind_param("i", $staffId);
        $stfCheck->execute();
        $stfData = $stfCheck->get_result()->fetch_assoc();
        
        if (!$stfData || $stfData['status'] === 'Inactive' || in_array(strtolower($stfData['account_status'] ?? ''), ['blocked', 'suspended'])) {
            // Staff account is deactivated or blocked!
            session_unset();
            session_destroy();
            header("Location: login.php?error=" . urlencode("Your staff account is currently inactive, suspended, or blocked. Please contact the hospital administrator."));
            exit;
        }
    }
}

// -------------------------------------------------------------
// Role-Based Access Control (RBAC) Page Protection for Staff
// -------------------------------------------------------------
if ($isStaffUser && !in_array($currentPage, $allowedPages)) {
    // 1. HOSPITAL OWNER / ADMIN-ONLY PAGES
    // Staff members cannot access these under any circumstances
    $ownerOnlyPages = [
        'staff_permissions.php',
        'staff.php',
        'backup.php',
        'restore.php',
        'ai_training_dashboard.php',
        'ai_logs.php',
        'charges.php',
        'doctor_slots.php',
        'doctors.php'
    ];

    if (in_array($currentPage, $ownerOnlyPages)) {
        $_SESSION['flash_error'] = "Access Restricted: Administrative owner privileges required for " . htmlspecialchars($currentPage);
        header("Location: dashboard.php");
        exit;
    }

    // 2. GRANULAR PERMISSION ENFORCEMENT
    // A. Medical Records / Files history page
    if ($currentPage === 'history.php' && !hasPermission($conn, 'can_view_files')) {
        $_SESSION['flash_error'] = "Access Restricted: You have Upload-Only authorization. File viewing is restricted.";
        header("Location: dashboard.php");
        exit;
    }

    // B. Appointments schedule / reception desk
    if (in_array($currentPage, ['appointments.php', 'index.php']) && !hasPermission($conn, 'can_manage_appointments')) {
        $_SESSION['flash_error'] = "Access Restricted: Appointment reception access is restricted on your account.";
        header("Location: dashboard.php");
        exit;
    }

    // C. Live Queue / Pipeline
    if ($currentPage === 'queue.php' && !hasPermission($conn, 'can_assign_tokens') && !hasPermission($conn, 'can_manage_appointments')) {
        $_SESSION['flash_error'] = "Access Restricted: Live pipeline triage is restricted on your account.";
        header("Location: dashboard.php");
        exit;
    }

    // D. Patient Directory (Allowed if can_view_patients OR can_upload so staff can find patients to upload files)
    if ($currentPage === 'patients.php' && !hasPermission($conn, 'can_view_patients') && !hasPermission($conn, 'can_upload')) {
        $_SESSION['flash_error'] = "Access Restricted: You do not have permission to access the patient directory.";
        header("Location: dashboard.php");
        exit;
    }

    // E. Patient Profile (Allowed if can_view_patients OR can_upload OR can_view_files)
    if ($currentPage === 'patient_profile.php' && !hasPermission($conn, 'can_view_patients') && !hasPermission($conn, 'can_upload') && !hasPermission($conn, 'can_view_files')) {
        $_SESSION['flash_error'] = "Access Restricted: Patient profile access is not authorized on your account.";
        header("Location: dashboard.php");
        exit;
    }

    // F. Online doctor consultations
    if ($currentPage === 'online_consult.php' && !hasPermission($conn, 'can_consult_online')) {
        $_SESSION['flash_error'] = "Access Restricted: Online consultation terminal is restricted on your account.";
        header("Location: dashboard.php");
        exit;
    }
}
?>
