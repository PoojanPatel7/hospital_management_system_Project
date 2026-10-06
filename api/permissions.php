<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/permission_check.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['hospital_id'])) {
    jsonResponse(['status' => 'error', 'message' => 'Unauthorized. Please log in.']);
}

// Check admin privileges: session user_type admin, is_admin flag, or hospital session without staff_id
$isAdmin = (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'admin') 
    || (isset($_SESSION['is_admin']) && $_SESSION['is_admin'])
    || (!isset($_SESSION['staff_id']) && isset($_SESSION['hospital_id']));

if (!$isAdmin) {
    jsonResponse(['status' => 'error', 'message' => 'Permission denied. Only administrators can manage staff permissions.']);
}

$hospitalId = (int)$_SESSION['hospital_id'];
$adminId = isset($_SESSION['staff_id']) ? (int)$_SESSION['staff_id'] : 1;
$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true) ?? [];

$validPermissions = [
    'can_upload', 'can_view_files', 'can_edit_files', 'can_delete_files',
    'can_view_patients', 'can_edit_patients', 'can_manage_appointments',
    'can_assign_tokens', 'can_generate_qr', 'can_download_pdf', 'can_consult_online'
];

$presets = [
    'upload_only' => ['can_upload' => 1, 'can_view_files' => 0, 'can_edit_files' => 0, 'can_delete_files' => 0, 'can_view_patients' => 0, 'can_edit_patients' => 0, 'can_manage_appointments' => 0, 'can_assign_tokens' => 0, 'can_generate_qr' => 0, 'can_download_pdf' => 0, 'can_consult_online' => 0],
    'view_only' => ['can_upload' => 0, 'can_view_files' => 1, 'can_edit_files' => 0, 'can_delete_files' => 0, 'can_view_patients' => 1, 'can_edit_patients' => 0, 'can_manage_appointments' => 0, 'can_assign_tokens' => 0, 'can_generate_qr' => 0, 'can_download_pdf' => 0, 'can_consult_online' => 0],
    'upload_view' => ['can_upload' => 1, 'can_view_files' => 1, 'can_edit_files' => 0, 'can_delete_files' => 0, 'can_view_patients' => 1, 'can_edit_patients' => 0, 'can_manage_appointments' => 0, 'can_assign_tokens' => 0, 'can_generate_qr' => 0, 'can_download_pdf' => 0, 'can_consult_online' => 0],
    'reception' => ['can_upload' => 1, 'can_view_files' => 0, 'can_edit_files' => 0, 'can_delete_files' => 0, 'can_view_patients' => 1, 'can_edit_patients' => 0, 'can_manage_appointments' => 1, 'can_assign_tokens' => 1, 'can_generate_qr' => 1, 'can_download_pdf' => 0, 'can_consult_online' => 0],
    'lab_tech' => ['can_upload' => 1, 'can_view_files' => 1, 'can_edit_files' => 0, 'can_delete_files' => 0, 'can_view_patients' => 0, 'can_edit_patients' => 0, 'can_manage_appointments' => 0, 'can_assign_tokens' => 0, 'can_generate_qr' => 0, 'can_download_pdf' => 0, 'can_consult_online' => 0],
    'doctor' => ['can_upload' => 1, 'can_view_files' => 1, 'can_edit_files' => 1, 'can_delete_files' => 1, 'can_view_patients' => 1, 'can_edit_patients' => 0, 'can_manage_appointments' => 0, 'can_assign_tokens' => 0, 'can_generate_qr' => 0, 'can_download_pdf' => 1, 'can_consult_online' => 1],
    'full_access' => ['can_upload' => 1, 'can_view_files' => 1, 'can_edit_files' => 1, 'can_delete_files' => 1, 'can_view_patients' => 1, 'can_edit_patients' => 1, 'can_manage_appointments' => 1, 'can_assign_tokens' => 1, 'can_generate_qr' => 1, 'can_download_pdf' => 1, 'can_consult_online' => 1],
    'blocked' => ['can_upload' => 0, 'can_view_files' => 0, 'can_edit_files' => 0, 'can_delete_files' => 0, 'can_view_patients' => 0, 'can_edit_patients' => 0, 'can_manage_appointments' => 0, 'can_assign_tokens' => 0, 'can_generate_qr' => 0, 'can_download_pdf' => 0, 'can_consult_online' => 0]
];

if ($action === 'get_permissions') {
    $staffId = (int)($_GET['staff_id'] ?? 0);
    if (!$staffId) {
        jsonResponse(['status' => 'error', 'message' => 'Staff ID is required.']);
    }

    $perms = array_fill_keys($validPermissions, 0);

    // Read normalized key-value permissions from staff_permissions
    $stmt = $conn->prepare("SELECT permission_key, permission_value FROM staff_permissions WHERE staff_id = ?");
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $key = $row['permission_key'];
        if (in_array($key, $validPermissions)) {
            $perms[$key] = (int)$row['permission_value'];
        }
    }

    // Fetch account status, username, and plain_password
    $statusStmt = $conn->prepare("SELECT username, plain_password, is_user, account_status FROM staff WHERE id = ?");
    $statusStmt->bind_param("i", $staffId);
    $statusStmt->execute();
    $statusRes = $statusStmt->get_result();
    $statusRow = $statusRes->fetch_assoc();
    $accountStatus = $statusRow['account_status'] ?? 'Active';

    jsonResponse([
        'status' => 'success',
        'permissions' => $perms,
        'account_status' => $accountStatus,
        'username' => $statusRow['username'] ?? '',
        'plain_password' => $statusRow['plain_password'] ?? 'staff123',
        'is_user' => (int)($statusRow['is_user'] ?? 0)
    ]);
}

if ($action === 'set_permissions') {
    $staffId = (int)($input['staff_id'] ?? 0);
    $perms = $input['permissions'] ?? [];
    $accountStatus = trim($input['account_status'] ?? 'Active');
    
    if (!$staffId) {
        jsonResponse(['status' => 'error', 'message' => 'Staff ID is required.']);
    }

    $updStmt = $conn->prepare("
        INSERT INTO staff_permissions (staff_id, permission_key, permission_value, granted_by, granted_at)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE permission_value = VALUES(permission_value), granted_by = VALUES(granted_by), granted_at = NOW()
    ");

    foreach ($validPermissions as $key) {
        $val = isset($perms[$key]) ? (int)$perms[$key] : 0;
        $updStmt->bind_param("isii", $staffId, $key, $val, $adminId);
        $updStmt->execute();
    }

    // Update account status in staff table
    $statusStmt = $conn->prepare("UPDATE staff SET account_status = ? WHERE id = ?");
    $statusStmt->bind_param("si", $accountStatus, $staffId);
    $statusStmt->execute();

    jsonResponse(['status' => 'success', 'message' => 'Permissions and account status updated successfully.']);
}

if ($action === 'quick_preset') {
    $staffId = (int)($input['staff_id'] ?? 0);
    $presetKey = trim($input['preset'] ?? '');

    if (!$staffId || !isset($presets[$presetKey])) {
        jsonResponse(['status' => 'error', 'message' => 'Invalid staff ID or preset.']);
    }

    $targetPerms = $presets[$presetKey];
    $updStmt = $conn->prepare("
        INSERT INTO staff_permissions (staff_id, permission_key, permission_value, granted_by, granted_at)
        VALUES (?, ?, ?, ?, NOW())
        ON DUPLICATE KEY UPDATE permission_value = VALUES(permission_value), granted_by = VALUES(granted_by), granted_at = NOW()
    ");

    foreach ($validPermissions as $key) {
        $val = $targetPerms[$key] ?? 0;
        $updStmt->bind_param("isii", $staffId, $key, $val, $adminId);
        $updStmt->execute();
    }

    if ($presetKey === 'blocked') {
        $conn->query("UPDATE staff SET account_status = 'Blocked' WHERE id = $staffId");
    } else {
        $conn->query("UPDATE staff SET account_status = 'Active' WHERE id = $staffId AND account_status = 'Blocked'");
    }

    jsonResponse([
        'status' => 'success',
        'message' => "Preset '" . ucfirst(str_replace('_', ' ', $presetKey)) . "' applied successfully.",
        'permissions' => $targetPerms
    ]);
}

if ($action === 'update_credentials') {
    $staffId = (int)($input['staff_id'] ?? 0);
    $username = trim($input['username'] ?? '');
    $password = trim($input['password'] ?? '');
    $accountStatus = trim($input['account_status'] ?? 'Active');
    $isUser = isset($input['is_user']) ? (int)$input['is_user'] : 1;

    if (!$staffId || empty($username)) {
        jsonResponse(['status' => 'error', 'message' => 'Staff ID and username are required.']);
    }

    // Check if username taken by another staff member
    $chkStmt = $conn->prepare("SELECT id FROM staff WHERE LOWER(username) = LOWER(?) AND id != ?");
    $chkStmt->bind_param("si", $username, $staffId);
    $chkStmt->execute();
    if ($chkStmt->get_result()->fetch_assoc()) {
        jsonResponse(['status' => 'error', 'message' => "The username '$username' is already taken. Please pick another."]);
    }

    if (!empty($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $upd = $conn->prepare("UPDATE staff SET username = ?, password = ?, plain_password = ?, is_user = ?, account_status = ? WHERE id = ?");
        $upd->bind_param("sssisi", $username, $hash, $password, $isUser, $accountStatus, $staffId);
    } else {
        $upd = $conn->prepare("UPDATE staff SET username = ?, is_user = ?, account_status = ? WHERE id = ?");
        $upd->bind_param("sisi", $username, $isUser, $accountStatus, $staffId);
    }
    $upd->execute();

    jsonResponse(['status' => 'success', 'message' => 'Credentials updated successfully.']);
}

if ($action === 'toggle_status') {
    $staffId = (int)($input['staff_id'] ?? 0);
    $newStatus = trim($input['account_status'] ?? 'Active');

    if (!$staffId || !in_array($newStatus, ['Active', 'Suspended', 'Blocked'])) {
        jsonResponse(['status' => 'error', 'message' => 'Invalid staff ID or status.']);
    }

    $upd = $conn->prepare("UPDATE staff SET account_status = ? WHERE id = ?");
    $upd->bind_param("si", $newStatus, $staffId);
    $upd->execute();

    jsonResponse(['status' => 'success', 'message' => "Account status set to $newStatus."]);
}

if ($action === 'get_all_staff_permissions') {
    // Return all staff and a summary of their active permissions + credentials
    $stmt = $conn->prepare("
        SELECT s.id, s.first_name, s.last_name, s.staff_code, s.role, s.department, s.shift, s.phone, s.email, 
               s.is_user, s.username, s.plain_password, s.status, s.account_status
        FROM staff s
        WHERE (s.hospital_id = ? OR s.hospital_id IS NULL OR ? = 0)
        ORDER BY s.is_user DESC, s.first_name ASC
    ");
    $stmt->bind_param("ii", $hospitalId, $hospitalId);
    $stmt->execute();
    $res = $stmt->get_result();
    
    $staffList = [];
    while ($row = $res->fetch_assoc()) {
        $sId = (int)$row['id'];
        
        // Fetch permissions for this staff member
        $pStmt = $conn->prepare("SELECT permission_key, permission_value FROM staff_permissions WHERE staff_id = ?");
        $pStmt->bind_param("i", $sId);
        $pStmt->execute();
        $pRes = $pStmt->get_result();
        
        $pMap = array_fill_keys($validPermissions, 0);
        while ($pRow = $pRes->fetch_assoc()) {
            $pMap[$pRow['permission_key']] = (int)$pRow['permission_value'];
        }
        
        $row['permissions'] = $pMap;
        if (empty($row['plain_password']) && $row['is_user']) {
            $row['plain_password'] = 'staff123';
        }
        $staffList[] = $row;
    }
    
    jsonResponse(['status' => 'success', 'data' => $staffList]);
}

jsonResponse(['status' => 'error', 'message' => 'Invalid action.']);
