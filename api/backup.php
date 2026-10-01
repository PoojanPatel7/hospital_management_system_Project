<?php
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['hospital_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized. Please sign in to access backup management.']);
    exit;
}

$hospitalId = (int)$_SESSION['hospital_id'];
$hospitalName = $_SESSION['hospital_name'] ?? 'CarePulse Hospital';
$action = $_GET['action'] ?? '';
$backupDir = __DIR__ . '/../backups';

// Ensure backups directory exists with secure .htaccess
if (!is_dir($backupDir)) {
    mkdir($backupDir, 0755, true);
}
$htaccessFile = $backupDir . '/.htaccess';
if (!file_exists($htaccessFile)) {
    file_put_contents($htaccessFile, "# Protect backups from direct URL access\n<IfModule !mod_authz_core.c>\n  Order Deny,Allow\n  Deny from all\n</IfModule>\n<IfModule mod_authz_core.c>\n  Require all denied\n</IfModule>\n");
}

// Helper: Format bytes to human readable string
function formatBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= pow(1024, $pow);
    return round($bytes, $precision) . ' ' . $units[$pow];
}

// Helper: Format relative time
function timeAgo($timestamp) {
    $diff = time() - $timestamp;
    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 172800) return 'Yesterday';
    return date('M d, Y h:i A', $timestamp);
}

// -------------------------------------------------------------
// ACTION: download
// -------------------------------------------------------------
if ($action === 'download') {
    $file = basename($_GET['file'] ?? '');
    if (!$file || !preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/i', $file)) {
        die('Invalid backup file name specified.');
    }

    $filepath = $backupDir . '/' . $file;
    if (!file_exists($filepath)) {
        die('The requested backup file does not exist on the server.');
    }

    // Force browser download
    header('Content-Description: File Transfer');
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $file . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    ob_clean();
    flush();
    readfile($filepath);
    exit;
}

// All subsequent actions return JSON
header('Content-Type: application/json');

// -------------------------------------------------------------
// ACTION: list_backups
// -------------------------------------------------------------
if ($action === 'list_backups') {
    $files = glob($backupDir . '/*.sql');
    $backups = [];
    $totalSize = 0;

    if ($files) {
        // Sort newest first
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        foreach ($files as $f) {
            $fname = basename($f);
            $fsize = filesize($f);
            $mtime = filemtime($f);
            $totalSize += $fsize;

            $backups[] = [
                'filename' => $fname,
                'size_bytes' => $fsize,
                'size_formatted' => formatBytes($fsize),
                'created_timestamp' => $mtime,
                'created_at' => date('Y-m-d H:i:s', $mtime),
                'relative_time' => timeAgo($mtime),
                'download_url' => 'api/backup.php?action=download&file=' . urlencode($fname)
            ];
        }
    }

    // Database stats
    $tablesRes = $conn->query("SHOW TABLES");
    $totalDbTables = $tablesRes ? $tablesRes->num_rows : 0;

    echo json_encode([
        'status' => 'success',
        'data' => [
            'backups' => $backups,
            'summary' => [
                'total_backups' => count($backups),
                'total_size_bytes' => $totalSize,
                'total_size_formatted' => formatBytes($totalSize),
                'latest_backup' => !empty($backups) ? $backups[0]['created_at'] : null,
                'db_tables_count' => $totalDbTables,
                'database_name' => $dbname ?? 'hospital_db',
                'backup_folder_path' => realpath($backupDir) ?: $backupDir
            ]
        ]
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: create_backup
// -------------------------------------------------------------
if ($action === 'create_backup') {
    $startTime = microtime(true);
    $timestampStr = date('Y-m-d_H-i-s');
    $filename = "backup_hospital_db_{$timestampStr}.sql";
    $filepath = $backupDir . '/' . $filename;

    $handle = fopen($filepath, 'w');
    if (!$handle) {
        echo json_encode(['status' => 'error', 'message' => 'Unable to open file for writing in backups directory. Check file permissions.']);
        exit;
    }

    // MySQL Server Info
    $serverInfo = $conn->server_info;
    $dateHuman = date('Y-m-d H:i:s');

    // Write Header
    $header = "-- ==========================================================\n"
            . "-- CarePulse Hospital Management System - Database Backup\n"
            . "-- Hospital: " . addslashes($hospitalName) . " (ID: {$hospitalId})\n"
            . "-- Generated: {$dateHuman}\n"
            . "-- Database: " . ($dbname ?? 'hospital_db') . "\n"
            . "-- MySQL Server Version: {$serverInfo}\n"
            . "-- ==========================================================\n\n"
            . "SET FOREIGN_KEY_CHECKS=0;\n"
            . "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n"
            . "SET AUTOCOMMIT = 0;\n"
            . "START TRANSACTION;\n"
            . "SET time_zone = '+00:00';\n"
            . "/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\n"
            . "/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\n"
            . "/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\n"
            . "/*!40101 SET NAMES utf8mb4 */;\n\n";

    fwrite($handle, $header);

    // Fetch all tables in database
    $tablesRes = $conn->query("SHOW TABLES");
    $tables = [];
    while ($row = $tablesRes->fetch_row()) {
        $tables[] = $row[0];
    }

    $totalTablesCount = count($tables);
    $totalRecordsCount = 0;

    foreach ($tables as $table) {
        fwrite($handle, "-- --------------------------------------------------------\n");
        fwrite($handle, "-- Table structure for table `{$table}`\n");
        fwrite($handle, "-- --------------------------------------------------------\n\n");
        fwrite($handle, "DROP TABLE IF EXISTS `{$table}`;\n");

        // Create table query
        $createRes = $conn->query("SHOW CREATE TABLE `{$table}`");
        if ($createRes && $cRow = $createRes->fetch_row()) {
            fwrite($handle, $cRow[1] . ";\n\n");
        }

        // Table data dump
        $dataRes = $conn->query("SELECT * FROM `{$table}`");
        $rowCount = $dataRes ? $dataRes->num_rows : 0;
        $totalRecordsCount += $rowCount;

        if ($rowCount > 0) {
            fwrite($handle, "-- Dumping data for table `{$table}` ({$rowCount} records)\n");

            // Detect binary / BLOB columns to export as hex literals (0x...)
            $fieldsMeta = $dataRes->fetch_fields();
            $blobCols = [];
            foreach ($fieldsMeta as $f) {
                $isBinaryBlob = (($f->flags & 128) && in_array($f->type, [249, 250, 251, 252]))
                                || stripos($f->name, 'file_data') !== false
                                || stripos($f->name, 'avatar_data') !== false;
                if ($isBinaryBlob) {
                    $blobCols[$f->name] = true;
                }
            }

            // Chunk inserts in batches of 50 for clean readable SQL (or 1 row per insert for large binary BLOBs)
            $batch = [];
            $batchCount = 0;
            $fields = null;

            while ($r = $dataRes->fetch_assoc()) {
                if ($fields === null) {
                    $fields = '`' . implode('`, `', array_keys($r)) . '`';
                }

                $escapedVals = [];
                $rowHasBlob = false;
                foreach ($r as $colName => $v) {
                    if ($v === null) {
                        $escapedVals[] = "NULL";
                    } elseif (isset($blobCols[$colName])) {
                        $rowHasBlob = true;
                        $escapedVals[] = "0x" . bin2hex($v);
                    } else {
                        $escapedVals[] = "'" . $conn->real_escape_string($v) . "'";
                    }
                }
                $batch[] = "(" . implode(", ", $escapedVals) . ")";
                $batchCount++;

                // If row has binary BLOB or batch reaches 50, write immediately to prevent memory/packet issues
                if ($rowHasBlob || $batchCount >= 50) {
                    fwrite($handle, "INSERT INTO `{$table}` ({$fields}) VALUES\n" . implode(",\n", $batch) . ";\n");
                    $batch = [];
                    $batchCount = 0;
                }
            }

            if ($batchCount > 0) {
                fwrite($handle, "INSERT INTO `{$table}` ({$fields}) VALUES\n" . implode(",\n", $batch) . ";\n");
            }

            fwrite($handle, "\n");
        }
    }

    // Write Footer
    $footer = "-- --------------------------------------------------------\n"
            . "-- Completed SQL Dump\n"
            . "-- --------------------------------------------------------\n"
            . "COMMIT;\n"
            . "SET FOREIGN_KEY_CHECKS=1;\n"
            . "/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\n"
            . "/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\n"
            . "/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;\n";

    fwrite($handle, $footer);
    fclose($handle);

    $duration = round(microtime(true) - $startTime, 2);
    $filesize = filesize($filepath);

    echo json_encode([
        'status' => 'success',
        'message' => 'Full database backup generated successfully!',
        'data' => [
            'filename' => $filename,
            'size_bytes' => $filesize,
            'size_formatted' => formatBytes($filesize),
            'tables_count' => $totalTablesCount,
            'rows_count' => $totalRecordsCount,
            'duration' => "{$duration}s",
            'created_at' => $dateHuman,
            'download_url' => 'api/backup.php?action=download&file=' . urlencode($filename)
        ]
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: delete_backup
// -------------------------------------------------------------
if ($action === 'delete_backup') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $file = basename($input['filename'] ?? '');

    if (!$file || !preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/i', $file)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid backup filename specified.']);
        exit;
    }

    $filepath = $backupDir . '/' . $file;
    if (!file_exists($filepath)) {
        echo json_encode(['status' => 'error', 'message' => 'Backup file does not exist on server.']);
        exit;
    }

    if (unlink($filepath)) {
        echo json_encode(['status' => 'success', 'message' => "Backup file '{$file}' deleted successfully."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to delete backup file from disk.']);
    }
    exit;
}

// -------------------------------------------------------------
// ACTION: open_folder (Open backup folder in PC File Explorer)
// -------------------------------------------------------------
if ($action === 'open_folder') {
    $realDir = realpath($backupDir) ?: $backupDir;
    $file = basename($_GET['file'] ?? '');
    $targetPath = $realDir;
    $hasSpecificFile = false;

    if ($file && preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/i', $file)) {
        $candidateFile = $realDir . DIRECTORY_SEPARATOR . $file;
        if (file_exists($candidateFile)) {
            $targetPath = $candidateFile;
            $hasSpecificFile = true;
        }
    }

    $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    $opened = false;

    if ($isWindows) {
        if ($hasSpecificFile) {
            $cmd = 'start "" explorer /select,' . escapeshellarg($targetPath);
        } else {
            $cmd = 'start "" explorer ' . escapeshellarg($targetPath);
        }
        @pclose(popen($cmd, "r"));
        $opened = true;
    } else {
        if (PHP_OS === 'Darwin') {
            @exec('open ' . escapeshellarg($hasSpecificFile ? dirname($targetPath) : $targetPath));
            $opened = true;
        } else {
            @exec('xdg-open ' . escapeshellarg($hasSpecificFile ? dirname($targetPath) : $targetPath));
            $opened = true;
        }
    }

    echo json_encode([
        'status' => 'success',
        'message' => $hasSpecificFile ? "Located file '{$file}' in File Explorer." : "Opened backup folder in PC File Explorer.",
        'data' => [
            'folder_path' => $realDir,
            'file_path' => $hasSpecificFile ? $targetPath : null,
            'opened' => $opened,
            'is_windows' => $isWindows
        ]
    ]);
    exit;
}

// -------------------------------------------------------------
// HELPER: scanSqlFileCompatibility
// Deep pre-flight validation of tables & columns against active DB
// -------------------------------------------------------------
function scanSqlFileCompatibility($filepath, $conn) {
    if (!file_exists($filepath)) {
        return [
            'valid' => false,
            'fatal_errors' => ['SQL backup file not found on server.'],
            'warnings' => [],
            'table_checks' => [],
            'tables_found' => 0,
            'tables_expected' => 0,
            'total_records' => 0
        ];
    }

    // 1. Fetch current database schema
    $currentSchema = [];
    $tablesRes = $conn->query("SHOW TABLES");
    if ($tablesRes) {
        while ($tRow = $tablesRes->fetch_row()) {
            $tbl = $tRow[0];
            $colRes = $conn->query("SHOW COLUMNS FROM `$tbl`");
            $cols = [];
            if ($colRes) {
                while ($cRow = $colRes->fetch_assoc()) {
                    $cols[$cRow['Field']] = strtolower($cRow['Type']);
                }
            }
            $currentSchema[$tbl] = $cols;
        }
    }

    // 2. Stream-parse the SQL file line-by-line
    $fh = fopen($filepath, 'r');
    if (!$fh) {
        return [
            'valid' => false,
            'fatal_errors' => ['Cannot open SQL backup file for reading.'],
            'warnings' => [],
            'table_checks' => [],
            'tables_found' => 0,
            'tables_expected' => count($currentSchema),
            'total_records' => 0
        ];
    }

    $dumpTables = [];
    $currentTable = null;
    $inCreate = false;
    $createSql = '';
    $totalRecords = 0;
    $hasDangerousSql = false;
    $dangerousReasons = [];

    while (($line = fgets($fh)) !== false) {
        $trimmed = trim($line);

        // Security check
        if (preg_match('/DROP\s+DATABASE/i', $trimmed) || preg_match('/GRANT\s+ALL/i', $trimmed)) {
            $hasDangerousSql = true;
            $dangerousReasons[] = "Dangerous command detected: " . htmlspecialchars(substr($trimmed, 0, 50));
        }

        // Detect CREATE TABLE
        if (preg_match('/^CREATE\s+TABLE\s+(?:IF\s+NOT\s+EXISTS\s+)?`?([a-zA-Z0-9_]+)`?\s*\(/i', $trimmed, $m)) {
            $currentTable = $m[1];
            $inCreate = true;
            $createSql = $line;
            $dumpTables[$currentTable] = [
                'columns' => [],
                'records' => 0
            ];
            continue;
        }

        if ($inCreate && $currentTable) {
            $createSql .= $line;
            if (preg_match('/^\)\s*(?:ENGINE|AUTO_INCREMENT|DEFAULT|CHARSET|COLLATE|\;)/i', $trimmed) || $trimmed === ');') {
                $inCreate = false;
                $createLines = explode("\n", $createSql);
                foreach ($createLines as $cLine) {
                    $cLTrim = trim($cLine);
                    if (preg_match('/^`([a-zA-Z0-9_]+)`\s+([a-zA-Z0-9_\(\)]+)/i', $cLTrim, $colMatch)) {
                        $colName = $colMatch[1];
                        $colType = strtolower($colMatch[2]);
                        $dumpTables[$currentTable]['columns'][$colName] = $colType;
                    }
                }
            }
            continue;
        }

        // Detect records count from comment or INSERT
        if (preg_match('/^--\s*Dumping data for table `([a-zA-Z0-9_]+)`\s*\((\d+)\s*records\)/i', $trimmed, $recMatch)) {
            $t = $recMatch[1];
            $cnt = (int)$recMatch[2];
            if (isset($dumpTables[$t])) {
                $dumpTables[$t]['records'] = $cnt;
                $totalRecords += $cnt;
            }
        }
    }
    fclose($fh);

    if ($hasDangerousSql) {
        return [
            'valid' => false,
            'fatal_errors' => array_merge(['File rejected due to dangerous administrative SQL commands.'], $dangerousReasons),
            'warnings' => [],
            'table_checks' => [],
            'tables_found' => count($dumpTables),
            'tables_expected' => count($currentSchema),
            'total_records' => $totalRecords
        ];
    }

    if (empty($dumpTables)) {
        return [
            'valid' => false,
            'fatal_errors' => ['No table definitions (`CREATE TABLE`) were found in this SQL file. Ensure this is a valid database backup dump.'],
            'warnings' => [],
            'table_checks' => [],
            'tables_found' => 0,
            'tables_expected' => count($currentSchema),
            'total_records' => 0
        ];
    }

    // 3. Compare each expected table against dump
    $tableChecks = [];
    $fatalErrors = [];
    $warnings = [];
    $allMatched = true;

    foreach ($currentSchema as $expectedTable => $expectedCols) {
        if (!isset($dumpTables[$expectedTable])) {
            $allMatched = false;
            $fatalErrors[] = "Missing required table: `{$expectedTable}` is not present in this backup file.";
            $tableChecks[] = [
                'table' => $expectedTable,
                'status' => 'missing',
                'columns_found' => 0,
                'columns_expected' => count($expectedCols),
                'records_count' => 0,
                'missing_columns' => array_keys($expectedCols),
                'extra_columns' => [],
                'message' => "Table is completely missing from backup file"
            ];
            continue;
        }

        $foundCols = $dumpTables[$expectedTable]['columns'];
        $missingCols = [];
        foreach ($expectedCols as $colName => $colType) {
            if (!isset($foundCols[$colName])) {
                $missingCols[] = $colName;
            }
        }

        $extraCols = [];
        foreach ($foundCols as $colName => $colType) {
            if (!isset($expectedCols[$colName])) {
                $extraCols[] = $colName;
            }
        }

        if (!empty($missingCols)) {
            $allMatched = false;
            $fatalErrors[] = "Table `{$expectedTable}` is missing required field(s): " . implode(', ', $missingCols);
            $tableChecks[] = [
                'table' => $expectedTable,
                'status' => 'mismatch',
                'columns_found' => count($foundCols),
                'columns_expected' => count($expectedCols),
                'records_count' => $dumpTables[$expectedTable]['records'],
                'missing_columns' => $missingCols,
                'extra_columns' => $extraCols,
                'message' => "Missing fields: " . implode(', ', $missingCols)
            ];
        } else {
            if (!empty($extraCols)) {
                $warnings[] = "Table `{$expectedTable}` has extra fields: " . implode(', ', $extraCols);
            }
            $tableChecks[] = [
                'table' => $expectedTable,
                'status' => 'matched',
                'columns_found' => count($foundCols),
                'columns_expected' => count($expectedCols),
                'records_count' => $dumpTables[$expectedTable]['records'],
                'missing_columns' => [],
                'extra_columns' => $extraCols,
                'message' => "100% matched (" . count($foundCols) . "/" . count($expectedCols) . " fields, {$dumpTables[$expectedTable]['records']} records)"
            ];
        }
    }

    // Check for foreign/extra tables in dump
    $extraTables = [];
    foreach ($dumpTables as $foundTable => $info) {
        if (!isset($currentSchema[$foundTable])) {
            $extraTables[] = $foundTable;
        }
    }
    if (!empty($extraTables)) {
        $warnings[] = "SQL dump contains extra tables not in current database: " . implode(', ', $extraTables);
    }

    return [
        'valid' => $allMatched && empty($fatalErrors),
        'fatal_errors' => $fatalErrors,
        'warnings' => $warnings,
        'table_checks' => $tableChecks,
        'tables_found' => count($dumpTables),
        'tables_expected' => count($currentSchema),
        'total_records' => $totalRecords,
        'extra_tables' => $extraTables
    ];
}

// -------------------------------------------------------------
// ACTION: scan_sql_file (Pre-flight schema and fields scanner)
// -------------------------------------------------------------
if ($action === 'scan_sql_file') {
    $file = basename($_GET['file'] ?? ($_POST['file'] ?? ''));
    if (!$file || !preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/i', $file)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid backup file specified for scan.']);
        exit;
    }

    $filepath = $backupDir . '/' . $file;
    if (!file_exists($filepath)) {
        echo json_encode(['status' => 'error', 'message' => 'Specified SQL file does not exist in backups directory.']);
        exit;
    }

    $scanResult = scanSqlFileCompatibility($filepath, $conn);
    $scanResult['file_name'] = $file;
    $scanResult['file_size'] = filesize($filepath);
    $scanResult['file_size_formatted'] = formatBytes(filesize($filepath));

    echo json_encode([
        'status' => 'success',
        'data' => $scanResult
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: upload_sql_file (Upload .sql from PC and immediately scan)
// -------------------------------------------------------------
if ($action === 'upload_sql_file') {
    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        $errMsg = 'No file was uploaded or an upload error occurred.';
        if (isset($_FILES['sql_file']['error'])) {
            switch ($_FILES['sql_file']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $errMsg = 'Uploaded file exceeds server size limit (max 40MB).';
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $errMsg = 'File was only partially uploaded. Please try again.';
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $errMsg = 'No file was selected for upload.';
                    break;
            }
        }
        echo json_encode(['status' => 'error', 'message' => $errMsg]);
        exit;
    }

    $origName = basename($_FILES['sql_file']['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if ($ext !== 'sql') {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file format. Please upload a valid MySQL .sql backup dump file.']);
        exit;
    }

    $cleanBase = preg_replace('/[^a-zA-Z0-9_\-]/', '_', pathinfo($origName, PATHINFO_FILENAME));
    $timestamp = date('Y-m-d_H-i-s');
    $savedFilename = "uploaded_{$cleanBase}_{$timestamp}.sql";
    $targetPath = $backupDir . '/' . $savedFilename;

    if (!move_uploaded_file($_FILES['sql_file']['tmp_name'], $targetPath)) {
        echo json_encode(['status' => 'error', 'message' => 'Failed to save uploaded file to backups directory. Check folder permissions.']);
        exit;
    }

    // Now perform schema validation scan on the uploaded file
    $scanResult = scanSqlFileCompatibility($targetPath, $conn);
    $scanResult['file_name'] = $savedFilename;
    $scanResult['file_size'] = filesize($targetPath);
    $scanResult['file_size_formatted'] = formatBytes(filesize($targetPath));

    echo json_encode([
        'status' => 'success',
        'message' => 'Backup file uploaded and scanned successfully.',
        'data' => $scanResult
    ]);
    exit;
}

// -------------------------------------------------------------
// ACTION: restore_backup (Execute database restore from scanned backup)
// -------------------------------------------------------------
if ($action === 'restore_backup') {
    $input = json_decode(file_get_contents('php://input'), true) ?? [];
    $file = basename($input['filename'] ?? ($_GET['file'] ?? ''));

    if (!$file || !preg_match('/^[a-zA-Z0-9_\-\.]+\.sql$/i', $file)) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid backup file name specified for restore.']);
        exit;
    }

    $filepath = $backupDir . '/' . $file;
    if (!file_exists($filepath)) {
        echo json_encode(['status' => 'error', 'message' => 'The backup snapshot file does not exist on the server.']);
        exit;
    }

    // 1. Mandatory server-side pre-flight schema check
    $scanResult = scanSqlFileCompatibility($filepath, $conn);
    if (!$scanResult['valid']) {
        $firstErr = !empty($scanResult['fatal_errors']) ? $scanResult['fatal_errors'][0] : 'Incompatible database schema detected.';
        echo json_encode([
            'status' => 'error',
            'message' => "Restore blocked: {$firstErr}",
            'fatal_errors' => $scanResult['fatal_errors']
        ]);
        exit;
    }

    $startTime = microtime(true);

    // 2. Perform restore via mysql.exe if available (fastest, supports large packet BLOBs without memory limits)
    $mysqlExe = 'C:\\xampp\\mysql\\bin\\mysql.exe';
    $restoredOk = false;
    $errorDetails = '';

    if (file_exists($mysqlExe)) {
        $dbName = $dbname ?? 'hospital_db';
        $cmd = "\"$mysqlExe\" -u root {$dbName} < " . escapeshellarg($filepath);
        exec($cmd . ' 2>&1', $outLines, $returnCode);

        if ($returnCode === 0) {
            $restoredOk = true;
        } else {
            $errorDetails = implode(" ", $outLines);
        }
    }

    // Fallback: If mysql.exe failed or wasn't found, execute via mysqli multi-query
    if (!$restoredOk) {
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        $sqlContent = file_get_contents($filepath);
        if ($sqlContent && $conn->multi_query($sqlContent)) {
            do {
                if ($res = $conn->store_result()) {
                    $res->free();
                }
            } while ($conn->more_results() && $conn->next_result());
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            $restoredOk = true;
        } else {
            $conn->query("SET FOREIGN_KEY_CHECKS=1");
            if (!$errorDetails) {
                $errorDetails = $conn->error;
            }
        }
    }

    if (!$restoredOk) {
        echo json_encode([
            'status' => 'error',
            'message' => "Database restore execution failed: " . ($errorDetails ?: 'Unknown SQL execution error.')
        ]);
        exit;
    }

    $duration = round(microtime(true) - $startTime, 2);

    echo json_encode([
        'status' => 'success',
        'message' => 'Database restore completed successfully! All tables and clinical records have been restored.',
        'data' => [
            'file_name' => $file,
            'tables_restored' => $scanResult['tables_found'],
            'total_records_restored' => $scanResult['total_records'],
            'duration' => "{$duration}s",
            'restored_at' => date('Y-m-d H:i:s')
        ]
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action specified.']);
