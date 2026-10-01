<?php
require_once __DIR__ . '/../db.php';

// Disable output compression or buffering interference
if (ob_get_level()) {
    ob_end_clean();
}

$fileId = (int)($_GET['id'] ?? ($_GET['patient_file_id'] ?? 0));
$staffId = (int)($_GET['staff_id'] ?? 0);
$download = isset($_GET['download']);

// -------------------------------------------------------------
// STREAM STAFF AVATAR FROM BLOB
// -------------------------------------------------------------
if ($staffId > 0) {
    $stmt = $conn->prepare("SELECT id, first_name, last_name, avatar_data, avatar_mime FROM staff WHERE id = ?");
    $stmt->bind_param("i", $staffId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && !empty($row['avatar_data'])) {
        $data = $row['avatar_data'];
        $mime = $row['avatar_mime'] ?: 'image/jpeg';
        $filename = "staff_{$staffId}." . (explode('/', $mime)[1] ?? 'jpg');

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
// STREAM PATIENT FILE / SCAN FROM BLOB
// -------------------------------------------------------------
if ($fileId > 0) {
    $stmt = $conn->prepare("SELECT id, patient_id, title, file_name, mime_type, file_size, file_data, file_path FROM patient_files WHERE id = ?");
    $stmt->bind_param("i", $fileId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row) {
        http_response_code(404);
        header("Content-Type: text/plain");
        die("404 Not Found: Medical record file not found.");
    }

    $binaryData = $row['file_data'];
    $mimeType = $row['mime_type'];
    $fileName = $row['file_name'] ?: ($row['title'] ?: "medical_record_{$fileId}");

    // Fallback: If BLOB was empty, attempt to read from disk
    if (empty($binaryData) && !empty($row['file_path'])) {
        $diskPath = __DIR__ . '/../' . ltrim(preg_replace('/\?.*$/', '', $row['file_path']), '/');
        if (file_exists($diskPath)) {
            $binaryData = file_get_contents($diskPath);
            if (!$mimeType) {
                $mimeType = mime_content_type($diskPath);
            }
        }
    }

    if (empty($binaryData)) {
        http_response_code(404);
        header("Content-Type: text/plain");
        die("404 Not Found: File binary data is unavailable.");
    }

    // Auto-detect MIME if missing
    if (!$mimeType) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->buffer($binaryData) ?: 'application/octet-stream';
    }

    $length = strlen($binaryData);
    $etag = md5($fileId . '_' . $length);

    header("ETag: \"$etag\"");
    header("Cache-Control: public, max-age=86400"); // 1 day browser cache

    // Conditional GET handling for fast 304 Not Modified response
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
// FALLBACK: PATH-BASED STREAMING IF SPECIFIED
// -------------------------------------------------------------
$rawPath = trim($_GET['path'] ?? ($_GET['file'] ?? ''));
if ($rawPath !== '') {
    $cleanPath = basename(preg_replace('/\?.*$/', '', $rawPath));
    // Check if matching row exists in database by file_name or file_path
    $stmt = $conn->prepare("SELECT id, file_data, mime_type, file_name FROM patient_files WHERE file_name = ? OR file_path LIKE ? LIMIT 1");
    $likePath = "%" . $cleanPath . "%";
    $stmt->bind_param("ss", $cleanPath, $likePath);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && !empty($row['file_data'])) {
        header("Content-Type: " . ($row['mime_type'] ?: 'application/octet-stream'));
        header("Content-Length: " . strlen($row['file_data']));
        header("Content-Disposition: inline; filename=\"" . addslashes($row['file_name']) . "\"");
        header("Cache-Control: public, max-age=86400");
        echo $row['file_data'];
        exit;
    }

    // Fallback to disk
    $diskPath = __DIR__ . '/../uploads/' . $cleanPath;
    if (file_exists($diskPath)) {
        header("Content-Type: " . mime_content_type($diskPath));
        header("Content-Length: " . filesize($diskPath));
        readfile($diskPath);
        exit;
    }
}

http_response_code(400);
header("Content-Type: text/plain");
die("Bad Request: Specify a valid file ID.");
