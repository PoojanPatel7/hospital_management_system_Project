<?php
require_once __DIR__ . '/../db.php';

// Discard any previous buffer/whitespace so binary headers are clean
while (ob_get_level()) {
    ob_end_clean();
}

$fileId = (int)($_GET['id'] ?? ($_GET['patient_file_id'] ?? 0));
$staffId = (int)($_GET['staff_id'] ?? 0);
$download = isset($_GET['download']);
$thumb = isset($_GET['thumb']);
$qrToken = trim($_GET['token'] ?? '');

// -------------------------------------------------------------
// 1. STREAM STAFF AVATAR
// -------------------------------------------------------------
if ($staffId > 0) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, avatar_data, avatar_mime FROM staff WHERE id = ?");
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && !empty($row['avatar_data'])) {
        $data = $row['avatar_data'];
        $mime = $row['avatar_mime'] ?: 'image/jpeg';
        
        header("Content-Type: " . $mime);
        header("Content-Length: " . strlen($data));
        header("Cache-Control: public, max-age=86400");
        $etag = md5('staff_' . $staffId . '_' . strlen($data));
        header("ETag: \"$etag\"");

        if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
            http_response_code(304);
            exit;
        }

        echo $data;
        exit;
    }
}

// -------------------------------------------------------------
// 2. STREAM PATIENT FILE
// -------------------------------------------------------------
if ($fileId > 0) {
    $stmt = $conn->prepare("SELECT id, patient_id, title, file_name, mime_type, file_size, file_data, file_path, thumbnail_path, uploaded_by_id FROM patient_files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        http_response_code(404);
        die("404 Not Found: Medical record file not found.");
    }

    // Permission check
    $is_admin = (!empty($_SESSION['is_admin']) || (!empty($_SESSION['hospital_id']) && empty($_SESSION['staff_id'])));
    $current_staff_id = (int)($_SESSION['staff_id'] ?? 0);
    
    $can_view = $is_admin;
    if (!$can_view && $current_staff_id > 0) {
        if (checkStaffPermission($conn, $current_staff_id, 'can_view_files') || 
            checkStaffPermission($conn, $current_staff_id, 'can_upload_files') || 
            checkStaffPermission($conn, $current_staff_id, 'can_upload') ||
            ((int)($row['uploaded_by_id'] ?? 0) === $current_staff_id)) {
            $can_view = true;
        }
    }
    
    // Allow public patient access via lifetime QR token
    if (!$can_view && !empty($qrToken)) {
        $qrCheck = $conn->prepare("SELECT id FROM patients WHERE id = ? AND qr_token = ?");
        if ($qrCheck) {
            $qrCheck->bind_param("ss", $row['patient_id'], $qrToken);
            $qrCheck->execute();
            if ($qrCheck->get_result()->fetch_assoc()) {
                $can_view = true;
            }
        }
    }

    if (!$can_view) {
        http_response_code(403);
        die("403 Forbidden: You do not have permission to view patient files.");
    }

    $patient_id = $row['patient_id'];
    $fileName = $row['file_name'] ?: ($row['title'] ?: "medical_record_{$fileId}");
    $mimeType = $row['mime_type'];
    
    $binaryData = null;
    $servePath = null;
    
    // A. Check thumbnail if requested
    if ($thumb && !empty($row['thumbnail_path'])) {
        $cleanThumb = preg_replace('/\?.*$/', '', $row['thumbnail_path']);
        if (strpos($cleanThumb, 'uploads/') !== false && !str_ends_with(strtolower($cleanThumb), '.php')) {
            $testPath = __DIR__ . '/../' . ltrim($cleanThumb, '/');
            if (file_exists($testPath) && is_file($testPath)) {
                $servePath = $testPath;
                $fileName = "thumb_" . $fileName;
            }
        }
    }
    
    // B. Check original disk file if not thumbnail (or if thumbnail wasn't found on disk)
    if (!$servePath && !$thumb && !empty($row['file_path'])) {
        $cleanFilePath = preg_replace('/\?.*$/', '', $row['file_path']);
        if (strpos($cleanFilePath, 'uploads/') !== false && !str_ends_with(strtolower($cleanFilePath), '.php')) {
            $testPath = __DIR__ . '/../' . ltrim($cleanFilePath, '/');
            if (file_exists($testPath) && is_file($testPath)) {
                $servePath = $testPath;
            }
        }
    }
    
    if ($servePath && file_exists($servePath) && is_file($servePath)) {
        $binaryData = file_get_contents($servePath);
        if (!$mimeType) {
            $mimeType = mime_content_type($servePath);
        }
    }
    
    // C. Fallback to database binary BLOB
    if (empty($binaryData) && !empty($row['file_data'])) {
        $binaryData = $row['file_data'];
    }

    if (empty($binaryData)) {
        http_response_code(404);
        die("404 Not Found: File data is unavailable.");
    }

    if (!$mimeType) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData) ?: 'application/octet-stream';
    }

    // Log file view in audit log
    if (!$thumb && !empty($_SESSION['hospital_id'])) {
        $logStmt = $conn->prepare("INSERT INTO file_audit_log (hospital_id, patient_id, file_id, action, staff_id, staff_name, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($logStmt) {
            $hospId = (int)$_SESSION['hospital_id'];
            $actionStr = $download ? 'download' : 'view';
            $staffName = $_SESSION['staff_name'] ?? ($is_admin ? 'Admin' : 'System');
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $logStmt->bind_param("isiisss", $hospId, $patient_id, $fileId, $actionStr, $current_staff_id, $staffName, $ip);
            $logStmt->execute();
        }
    }

    $length = strlen($binaryData);
    $etag = md5($fileId . '_' . $length . ($thumb ? '_thumb' : ''));

    header("ETag: \"$etag\"");
    header("Cache-Control: public, max-age=86400");

    if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag) {
        http_response_code(304);
        exit;
    }

    header("Content-Type: " . $mimeType);
    header("Content-Length: " . $length);

    $disposition = $download ? 'attachment' : 'inline';
    header("Content-Disposition: {$disposition}; filename=\"" . addslashes($fileName) . "\"");

    echo $binaryData;
    exit;
}

// -------------------------------------------------------------
// 3. STREAM BY FILE NAME OR PATH
// -------------------------------------------------------------
$rawPath = trim($_GET['path'] ?? ($_GET['file'] ?? ''));
if ($rawPath !== '') {
    $cleanPath = basename(preg_replace('/\?.*$/', '', $rawPath));
    $stmt = $conn->prepare("SELECT id, file_data, mime_type, file_name FROM patient_files WHERE file_name = ? OR file_path LIKE ? LIMIT 1");
    $likePath = "%" . $cleanPath . "%";
    $stmt->bind_param("ss", $cleanPath, $likePath);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && !empty($row['file_data'])) {
        $mime = $row['mime_type'] ?: 'application/octet-stream';
        header("Content-Type: " . $mime);
        header("Content-Length: " . strlen($row['file_data']));
        header("Content-Disposition: inline; filename=\"" . addslashes($row['file_name']) . "\"");
        header("Cache-Control: public, max-age=86400");
        echo $row['file_data'];
        exit;
    }

    $diskPath = __DIR__ . '/../uploads/' . $cleanPath;
    if (file_exists($diskPath) && !str_ends_with(strtolower($cleanPath), '.php')) {
        header("Content-Type: " . mime_content_type($diskPath));
        header("Content-Length: " . filesize($diskPath));
        readfile($diskPath);
        exit;
    }
}

http_response_code(400);
die("Bad Request: Specify a valid file ID.");
