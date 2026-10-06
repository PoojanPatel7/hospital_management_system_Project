<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$action = $_GET['action'] ?? '';
$input = json_decode(file_get_contents('php://input'), true);

if ($action === 'register') {
    $name = trim($input['name'] ?? '');
    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');

    if (!$name || !$username || $password === '') {
        jsonResponse(['status' => 'error', 'message' => 'All fields are required.']);
    }

    if (strlen($password) < 6) {
        jsonResponse(['status' => 'error', 'message' => 'Password must be at least 6 characters long.']);
    }

    // Check if hospital name already exists
    $stmtCheckName = $conn->prepare("SELECT id FROM hospitals WHERE LOWER(name) = LOWER(?)");
    if ($stmtCheckName) {
        $stmtCheckName->bind_param("s", $name);
        $stmtCheckName->execute();
        if ($stmtCheckName->get_result()->fetch_assoc()) {
            jsonResponse(['status' => 'error', 'message' => "A hospital named '$name' is already registered. Please sign in or use a different name."]);
        }
    }

    // Check if username already exists
    $stmtCheckUser = $conn->prepare("SELECT id FROM hospitals WHERE LOWER(username) = LOWER(?)");
    if ($stmtCheckUser) {
        $stmtCheckUser->bind_param("s", $username);
        $stmtCheckUser->execute();
        if ($stmtCheckUser->get_result()->fetch_assoc()) {
            jsonResponse(['status' => 'error', 'message' => "The username '$username' is already taken. Please choose a different username."]);
        }
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO hospitals (name, username, password) VALUES (?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sss", $name, $username, $hashedPassword);
        try {
            $stmt->execute();
            $hospital_id = $stmt->insert_id;
            
            $_SESSION['hospital_id'] = (int)$hospital_id;
            $_SESSION['hospital_name'] = $name;

            jsonResponse(['status' => 'success', 'message' => 'Registration successful']);
        } catch (Throwable $e) {
            jsonResponse(['status' => 'error', 'message' => 'Registration failed: ' . $e->getMessage()]);
        }
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
}

if ($action === 'login') {
    $username = trim($input['username'] ?? '');
    $password = (string)($input['password'] ?? '');

    if (!$username || $password === '') {
        jsonResponse(['status' => 'error', 'message' => 'Username and password are required.']);
    }

    $stmt = $conn->prepare("SELECT id, name, username, password FROM hospitals WHERE LOWER(username) = LOWER(?)");
    if ($stmt) {
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $isMatch = false;
            if (password_verify($password, $row['password'])) {
                $isMatch = true;
            } else if ($password === $row['password']) {
                // Matched plain-text password - auto-upgrade to secure bcrypt hash
                $isMatch = true;
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upStmt = $conn->prepare("UPDATE hospitals SET password = ? WHERE id = ?");
                $upStmt->bind_param("si", $newHash, $row['id']);
                $upStmt->execute();
            }

            if ($isMatch) {
                $_SESSION['hospital_id'] = (int)$row['id'];
                $_SESSION['hospital_name'] = $row['name'];
                $_SESSION['username'] = $row['username'] ?: 'Gopalbhai';
                $_SESSION['user_full_name'] = $row['username'] ?: 'Gopalbhai';
                $_SESSION['role_title'] = 'Hospital Owner & Super Administrator';
                $_SESSION['user_type'] = 'admin';
                $_SESSION['is_admin'] = true;
                $redir = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                unset($_SESSION['redirect_after_login']);
                jsonResponse(['status' => 'success', 'message' => 'Welcome ' . ($_SESSION['username']) . '! Login successful', 'redirect' => $redir]);
            } else {
                jsonResponse(['status' => 'error', 'message' => 'Invalid password.']);
            }
        } else {
            // Check if it's a staff member user account
            $stfStmt = $conn->prepare("SELECT s.id, s.hospital_id, s.first_name, s.last_name, s.role, s.department, s.username, s.password, s.is_user, s.status, s.account_status, h.name AS hospital_name 
                                       FROM staff s 
                                       JOIN hospitals h ON s.hospital_id = h.id 
                                       WHERE LOWER(s.username) = LOWER(?) AND s.is_user = 1");
            if ($stfStmt) {
                $stfStmt->bind_param("s", $username);
                $stfStmt->execute();
                $stfRes = $stfStmt->get_result();
                if ($stfRow = $stfRes->fetch_assoc()) {
                    if ($stfRow['status'] === 'Inactive' || in_array(strtolower($stfRow['account_status'] ?? ''), ['blocked', 'suspended'])) {
                        jsonResponse(['status' => 'error', 'message' => 'This staff user account is deactivated, suspended, or blocked. Please contact hospital administrator.']);
                    }
                    $isMatch = false;
                    if (password_verify($password, $stfRow['password'])) {
                        $isMatch = true;
                    } else if ($password === $stfRow['password']) {
                        $isMatch = true;
                        $newHash = password_hash($password, PASSWORD_DEFAULT);
                        $upStmt = $conn->prepare("UPDATE staff SET password = ? WHERE id = ?");
                        $upStmt->bind_param("si", $newHash, $stfRow['id']);
                        $upStmt->execute();
                    }

                    if ($isMatch) {
                        $_SESSION['hospital_id'] = (int)$stfRow['hospital_id'];
                        $_SESSION['hospital_name'] = $stfRow['hospital_name'];
                        $_SESSION['staff_id'] = (int)$stfRow['id'];
                        $_SESSION['staff_name'] = $stfRow['first_name'] . ' ' . $stfRow['last_name'];
                        $_SESSION['staff_role'] = $stfRow['role'];
                        $_SESSION['user_type'] = 'staff';
                        $_SESSION['is_admin'] = false;
                        $redir = $_SESSION['redirect_after_login'] ?? 'dashboard.php';
                        unset($_SESSION['redirect_after_login']);
                        jsonResponse(['status' => 'success', 'message' => 'Welcome ' . $stfRow['first_name'] . '! Staff login successful', 'redirect' => $redir]);
                    } else {
                        jsonResponse(['status' => 'error', 'message' => 'Invalid staff password.']);
                    }
                } else {
                    jsonResponse(['status' => 'error', 'message' => 'Account not found. Please check your username.']);
                }
            } else {
                jsonResponse(['status' => 'error', 'message' => 'Account not found.']);
            }
        }
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Database error: ' . $conn->error]);
    }
}

if ($action === 'logout') {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    jsonResponse(['status' => 'success']);
}
?>
