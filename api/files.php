<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();

$action = $_REQUEST['action'] ?? '';
$is_admin = $_SESSION['is_admin'] ?? false;
$staff_id = $_SESSION['staff_id'] ?? 0;
$staff_name = $_SESSION['staff_name'] ?? ($_SESSION['username'] ?? ($is_admin ? 'System Administrator' : 'Staff Member'));
$hospital_id = $_SESSION['hospital_id'] ?? 1;

function createThumbnail($sourcePath, $thumbPath, $maxWidth = 200, $maxHeight = 200) {
    if (!function_exists('imagecreatetruecolor') || !function_exists('getimagesize')) {
        return false;
    }
    try {
        $imageInfo = @getimagesize($sourcePath);
        if (!$imageInfo) return false;
        
        list($origW, $origH, $type) = $imageInfo;
        if ($origW <= 0 || $origH <= 0) return false;

        $ratio = min($maxWidth / $origW, $maxHeight / $origH);
        $newW = max(1, (int)($origW * $ratio));
        $newH = max(1, (int)($origH * $ratio));
        
        $thumb = @imagecreatetruecolor($newW, $newH);
        if (!$thumb) return false;
        
        $source = false;
        switch ($type) {
            case IMAGETYPE_JPEG: 
                if (function_exists('imagecreatefromjpeg')) $source = @imagecreatefromjpeg($sourcePath); 
                break;
            case IMAGETYPE_PNG:
                if (function_exists('imagecreatefrompng')) {
                    $source = @imagecreatefrompng($sourcePath);
                    @imagealphablending($thumb, false);
                    @imagesavealpha($thumb, true);
                }
                break;
            case IMAGETYPE_GIF: 
                if (function_exists('imagecreatefromgif')) $source = @imagecreatefromgif($sourcePath); 
                break;
            case IMAGETYPE_WEBP: 
                if (function_exists('imagecreatefromwebp')) $source = @imagecreatefromwebp($sourcePath); 
                break;
            case IMAGETYPE_BMP: 
                if (function_exists('imagecreatefrombmp')) $source = @imagecreatefrombmp($sourcePath); 
                break;
            default: 
                @imagedestroy($thumb);
                return false;
        }
        
        if (!$source) {
            @imagedestroy($thumb);
            return false;
        }
        @imagecopyresampled($thumb, $source, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        @imagejpeg($thumb, $thumbPath, 85);
        @imagedestroy($thumb);
        @imagedestroy($source);
        return file_exists($thumbPath);
    } catch (\Throwable $e) {
        return false;
    }
}

if ($action === 'upload' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_admin && !checkStaffPermission($conn, $staff_id, 'can_upload') && !checkStaffPermission($conn, $staff_id, 'can_upload_files')) {
        jsonResponse(['status' => 'error', 'message' => 'Permission denied: Your staff account is not authorized to upload patient medical files.']);
    }

    $patient_id = $_POST['patient_id'] ?? '';
    $appointment_id = !empty($_POST['appointment_id']) ? (int)$_POST['appointment_id'] : null;
    
    if (!$patient_id) {
        jsonResponse(['status' => 'error', 'message' => 'Patient ID required']);
    }

    if (!isset($_FILES['files']) || empty($_FILES['files']['name'][0])) {
        jsonResponse(['status' => 'error', 'message' => 'No files uploaded']);
    }

    $titles = $_POST['titles'] ?? [];
    $categories = $_POST['categories'] ?? [];
    $highlights = $_POST['highlights'] ?? [];
    $descriptions = $_POST['descriptions'] ?? [];
    $record_dates = $_POST['record_dates'] ?? [];
    $tags = $_POST['tags'] ?? [];

    $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf', 'bmp'];
    
    $origDir = __DIR__ . "/../uploads/patients/{$patient_id}/originals";
    $thumbDir = __DIR__ . "/../uploads/patients/{$patient_id}/thumbnails";
    
    if (!is_dir($origDir)) mkdir($origDir, 0755, true);
    if (!is_dir($thumbDir)) mkdir($thumbDir, 0755, true);

    $uploadedIds = [];
    
    $stmt = $conn->prepare("INSERT INTO patient_files (patient_id, appointment_id, title, category, highlight, description, record_date, tags, file_name, file_path, thumbnail_path, mime_type, file_size, file_data, uploaded_by_id, uploaded_by_name) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    for ($i = 0; $i < count($_FILES['files']['name']); $i++) {
        if ($_FILES['files']['error'][$i] !== UPLOAD_ERR_OK) continue;

        $tmpName = $_FILES['files']['tmp_name'][$i];
        $origName = basename($_FILES['files']['name'][$i]);
        $size = $_FILES['files']['size'][$i];
        
        if ($size > 10 * 1024 * 1024) continue; // 10MB limit
        
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) continue;
        
        $mime = mime_content_type($tmpName);
        $sanitizedName = preg_replace('/[^a-zA-Z0-9.\-_]/', '_', $origName);
        $uniqueName = uniqid() . "_{$sanitizedName}";
        
        $origPath = "{$origDir}/{$uniqueName}";
        $dbOrigPath = "uploads/patients/{$patient_id}/originals/{$uniqueName}";
        
        if (move_uploaded_file($tmpName, $origPath)) {
            $dbThumbPath = $dbOrigPath;
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'])) {
                $thumbName = "thumb_{$uniqueName}";
                $thumbPath = "{$thumbDir}/{$thumbName}";
                if (createThumbnail($origPath, $thumbPath)) {
                    $dbThumbPath = "uploads/patients/{$patient_id}/thumbnails/{$thumbName}";
                }
            }
            
            $fileBinary = file_get_contents($origPath);
            
            $title = $titles[$i] ?? ($_POST['title'] ?? $origName);
            $cat = $categories[$i] ?? ($_POST['category'] ?? 'Other');
            $hl = $highlights[$i] ?? ($_POST['highlight'] ?? '');
            $desc = $descriptions[$i] ?? ($_POST['description'] ?? '');
            $rawDate = $record_dates[$i] ?? ($_POST['record_date'] ?? date('Y-m-d H:i:s'));
            $date = !empty($rawDate) ? date('Y-m-d H:i:s', strtotime(str_replace('T', ' ', $rawDate))) : date('Y-m-d H:i:s');
            $tag = $tags[$i] ?? ($_POST['tag'] ?? '');
            
            $stmt->bind_param("sisssssssssssiss", 
                $patient_id, $appointment_id, $title, $cat, $hl, $desc, $date, $tag,
                $origName, $dbOrigPath, $dbThumbPath, $mime, $size, $fileBinary, $staff_id, $staff_name
            );
            
            if ($stmt->execute()) {
                $fileId = $stmt->insert_id;
                $uploadedIds[] = $fileId;
                
                $ip = $_SERVER['REMOTE_ADDR'] ?? '';
                $conn->query("INSERT INTO file_audit_log (hospital_id, patient_id, file_id, action, staff_id, staff_name, ip_address) VALUES ({$hospital_id}, '{$conn->real_escape_string($patient_id)}', {$fileId}, 'upload', {$staff_id}, '{$conn->real_escape_string($staff_name)}', '{$conn->real_escape_string($ip)}')");
            }
        }
    }
    
    jsonResponse(['status' => 'success', 'uploaded_ids' => $uploadedIds, 'message' => 'Files uploaded successfully']);
}
elseif ($action === 'get_files' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$is_admin && !checkStaffPermission($conn, $staff_id, 'can_view_files') && !checkStaffPermission($conn, $staff_id, 'can_upload') && !checkStaffPermission($conn, $staff_id, 'can_upload_files')) {
        jsonResponse(['status' => 'error', 'message' => 'Permission denied: Viewing medical archive files is restricted for your account.']);
    }

    $patient_id = $_GET['patient_id'] ?? '';
    if (!$patient_id) jsonResponse(['status' => 'error', 'message' => 'Patient ID required']);
    
    $where = ["patient_id = ?"];
    $params = [$patient_id];
    $types = "s";
    
    if (!empty($_GET['appointment_id'])) {
        $where[] = "appointment_id = ?";
        $params[] = $_GET['appointment_id'];
        $types .= "i";
    }
    if (!empty($_GET['category'])) {
        $where[] = "category = ?";
        $params[] = $_GET['category'];
        $types .= "s";
    }
    if (!empty($_GET['from_date'])) {
        $where[] = "record_date >= ?";
        $params[] = $_GET['from_date'] . ' 00:00:00';
        $types .= "s";
    }
    if (!empty($_GET['to_date'])) {
        $where[] = "record_date <= ?";
        $params[] = $_GET['to_date'] . ' 23:59:59';
        $types .= "s";
    }
    if (!empty($_GET['uploaded_by'])) {
        $where[] = "uploaded_by_id = ?";
        $params[] = $_GET['uploaded_by'];
        $types .= "i";
    }
    if (!empty($_GET['search'])) {
        $search = "%" . $_GET['search'] . "%";
        $where[] = "(title LIKE ? OR description LIKE ? OR highlight LIKE ? OR tags LIKE ?)";
        $params = array_merge($params, [$search, $search, $search, $search]);
        $types .= "ssss";
    }
    
    $sort = $_GET['sort'] ?? 'date_desc';
    $order = "ORDER BY record_date DESC";
    if ($sort === 'date_asc') $order = "ORDER BY record_date ASC";
    elseif ($sort === 'title_asc') $order = "ORDER BY title ASC";
    elseif ($sort === 'category') $order = "ORDER BY category ASC, record_date DESC";
    
    $whereSql = implode(' AND ', $where);
    $query = "SELECT * FROM patient_files WHERE $whereSql $order";
    
    $stmt = $conn->prepare($query);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    $files = [];
    while ($row = $result->fetch_assoc()) {
        unset($row['file_data']); // Do not send raw blob data in JSON
        $row['url'] = "api/file.php?id=" . $row['id'] . "&file=" . urlencode($row['file_name'] ?: 'file');
        $row['thumb_url'] = "api/file.php?id=" . $row['id'] . "&thumb=1";
        $row['file_path'] = $row['url'];
        $files[] = $row;
    }
    
    jsonResponse(['status' => 'success', 'data' => $files]);
}
elseif ($action === 'get_file_detail' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    if (!$is_admin && !checkStaffPermission($conn, $staff_id, 'can_view_files') && !checkStaffPermission($conn, $staff_id, 'can_upload') && !checkStaffPermission($conn, $staff_id, 'can_upload_files')) {
        jsonResponse(['status' => 'error', 'message' => 'Permission denied: Viewing medical archive files is restricted for your account.']);
    }

    $file_id = (int)($_GET['file_id'] ?? 0);
    if (!$file_id) jsonResponse(['status' => 'error', 'message' => 'File ID required']);
    
    $stmt = $conn->prepare("SELECT * FROM patient_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if (!$row) jsonResponse(['status' => 'error', 'message' => 'File not found']);
    
    unset($row['file_data']);
    $row['url'] = "api/file.php?id=" . $row['id'] . "&file=" . urlencode($row['file_name'] ?: 'file');
    $row['thumb_url'] = "api/file.php?id=" . $row['id'] . "&thumb=1";
    $row['file_path'] = $row['url'];
    
    jsonResponse(['status' => 'success', 'data' => $row]);
}
elseif ($action === 'update_file' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_admin && !checkStaffPermission($conn, $staff_id, 'can_edit_files')) {
        jsonResponse(['status' => 'error', 'message' => 'Permission denied']);
    }
    
    $file_id = (int)($_POST['file_id'] ?? 0);
    if (!$file_id) jsonResponse(['status' => 'error', 'message' => 'File ID required']);
    
    $title = $_POST['title'] ?? '';
    $category = $_POST['category'] ?? '';
    $highlight = $_POST['highlight'] ?? '';
    $description = $_POST['description'] ?? '';
    $tags = $_POST['tags'] ?? '';
    $record_date = $_POST['record_date'] ?? '';
    
    $stmt = $conn->prepare("UPDATE patient_files SET title=?, category=?, highlight=?, description=?, tags=?, record_date=? WHERE id=?");
    $stmt->bind_param("ssssssi", $title, $category, $highlight, $description, $tags, $record_date, $file_id);
    
    if ($stmt->execute()) {
        $ptRes = $conn->query("SELECT patient_id FROM patient_files WHERE id = $file_id");
        if ($ptRes && $ptRow = $ptRes->fetch_assoc()) {
            $patient_id = $ptRow['patient_id'];
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $conn->query("INSERT INTO file_audit_log (hospital_id, patient_id, file_id, action, staff_id, staff_name, ip_address) VALUES ({$hospital_id}, '{$conn->real_escape_string($patient_id)}', {$file_id}, 'edit', {$staff_id}, '{$conn->real_escape_string($staff_name)}', '{$conn->real_escape_string($ip)}')");
        }
        jsonResponse(['status' => 'success']);
    } else {
        jsonResponse(['status' => 'error', 'message' => 'Update failed']);
    }
}
elseif ($action === 'delete_file' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$is_admin && !checkStaffPermission($conn, $staff_id, 'can_delete_files')) {
        jsonResponse(['status' => 'error', 'message' => 'Permission denied']);
    }
    
    $file_id = (int)($_POST['file_id'] ?? 0);
    if (!$file_id) jsonResponse(['status' => 'error', 'message' => 'File ID required']);
    
    $stmt = $conn->prepare("SELECT patient_id, file_path, thumbnail_path FROM patient_files WHERE id = ?");
    $stmt->bind_param("i", $file_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    
    if ($row) {
        $patient_id = $row['patient_id'];
        
        $delStmt = $conn->prepare("DELETE FROM patient_files WHERE id = ?");
        $delStmt->bind_param("i", $file_id);
        
        if ($delStmt->execute()) {
            if ($row['file_path']) @unlink(__DIR__ . '/../' . $row['file_path']);
            if ($row['thumbnail_path']) @unlink(__DIR__ . '/../' . $row['thumbnail_path']);
            
            $ip = $_SERVER['REMOTE_ADDR'] ?? '';
            $conn->query("INSERT INTO file_audit_log (hospital_id, patient_id, file_id, action, staff_id, staff_name, ip_address) VALUES ({$hospital_id}, '{$conn->real_escape_string($patient_id)}', {$file_id}, 'delete', {$staff_id}, '{$conn->real_escape_string($staff_name)}', '{$conn->real_escape_string($ip)}')");
            
            jsonResponse(['status' => 'success']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Delete failed']);
        }
    } else {
        jsonResponse(['status' => 'error', 'message' => 'File not found']);
    }
}
elseif ($action === 'get_categories' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $categories = ['X-Ray', 'Blood Report', 'MRI', 'CT Scan', 'Prescription', 'Ultrasound', 'ECG', 'Other'];
    jsonResponse(['status' => 'success', 'data' => $categories]);
}
elseif ($action === 'get_appointment_files' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $patient_id = $_GET['patient_id'] ?? '';
    if (!$patient_id) jsonResponse(['status' => 'error', 'message' => 'Patient ID required']);
    
    $stmt = $conn->prepare("SELECT * FROM patient_files WHERE patient_id = ? AND appointment_id IS NOT NULL ORDER BY appointment_id DESC, record_date DESC");
    $stmt->bind_param("s", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $grouped = [];
    while ($row = $result->fetch_assoc()) {
        unset($row['file_data']);
        $row['url'] = "api/file.php?id=" . $row['id'];
        $row['thumb_url'] = $row['thumbnail_path'] ? "api/file.php?id=" . $row['id'] . "&thumb=1" : null;
        
        $aid = $row['appointment_id'];
        if (!isset($grouped[$aid])) {
            $grouped[$aid] = [];
        }
        $grouped[$aid][] = $row;
    }
    
    jsonResponse(['status' => 'success', 'data' => $grouped]);
}

jsonResponse(['status' => 'error', 'message' => 'Invalid action']);
