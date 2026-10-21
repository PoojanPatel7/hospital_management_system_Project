<?php
require_once __DIR__ . '/../db.php';
if (session_status() === PHP_SESSION_NONE) session_start();
$hospital_id = $_SESSION['hospital_id'] ?? 0;
$staff_id = $_SESSION['staff_id'] ?? 0;
$staff_name = $_SESSION['staff_name'] ?? ($_SESSION['username'] ?? 'Staff Member');

header('Content-Type: application/json');
$action = $_REQUEST['action'] ?? '';

// Helper: Generate Thumbnail for image preview
function makeBedThumbnail($srcPath, $dstPath, $maxW = 400, $maxH = 300) {
    if (!function_exists('imagecreatetruecolor') || !function_exists('getimagesize')) {
        return false;
    }
    try {
        $info = @getimagesize($srcPath);
        if (!$info) return false;
        list($origW, $origH, $type) = $info;
        if ($origW <= 0 || $origH <= 0) return false;

        $ratio = min($maxW / $origW, $maxH / $origH);
        $newW = max(1, (int)($origW * $ratio));
        $newH = max(1, (int)($origH * $ratio));

        $thumb = @imagecreatetruecolor($newW, $newH);
        if (!$thumb) return false;

        $source = false;
        switch ($type) {
            case IMAGETYPE_JPEG:
                if (function_exists('imagecreatefromjpeg')) $source = @imagecreatefromjpeg($srcPath);
                break;
            case IMAGETYPE_PNG:
                if (function_exists('imagecreatefrompng')) {
                    $source = @imagecreatefrompng($srcPath);
                    @imagealphablending($thumb, false);
                    @imagesavealpha($thumb, true);
                }
                break;
            case IMAGETYPE_WEBP:
                if (function_exists('imagecreatefromwebp')) $source = @imagecreatefromwebp($srcPath);
                break;
            case IMAGETYPE_BMP:
                if (function_exists('imagecreatefrombmp')) $source = @imagecreatefrombmp($srcPath);
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
        @imagejpeg($thumb, $dstPath, 85);
        @imagedestroy($thumb);
        @imagedestroy($source);
        return file_exists($dstPath);
    } catch (\Throwable $e) {
        return false;
    }
}

// -------------------------------------------------------------
// GET REQUESTS
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_all') {
        $beds = [];
        $hCond = $hospital_id ? "WHERE (b.hospital_id = $hospital_id OR b.hospital_id IS NULL)" : "";
        
        $sql = "
            SELECT b.*, 
                   p.name, p.surname, p.father_name, p.phone, p.demographics, p.gender, p.blood_group, p.age,
                   p.emergency_contact_name, p.emergency_contact_phone,
                   a.id as appointment_id, a.type as appt_type, a.status as appt_status, a.symptoms, a.doctor_notes,
                   a.date as appt_date, a.slot as appt_slot, a.created_at as admitted_at,
                   d.id as doctor_id, d.name as doctor_name, dep.name as dept_name,
                   (SELECT GROUP_CONCAT(dg.description SEPARATOR ', ') FROM diagnoses dg WHERE dg.appointment_id = a.id) as diagnoses_list,
                   (SELECT COUNT(*) FROM prescriptions pr WHERE pr.appointment_id = a.id) as prescriptions_count,
                   (SELECT COUNT(*) FROM patient_files pf WHERE pf.patient_id = b.patient_id AND (pf.category != 'Admission Monitor' OR pf.category IS NULL)) as files_count
            FROM beds b
            LEFT JOIN patients p ON b.patient_id = p.id
            LEFT JOIN appointments a ON a.id = (
                SELECT app.id FROM appointments app 
                WHERE app.patient_id = b.patient_id 
                ORDER BY (app.bed_number = b.bed_number) DESC, app.id DESC 
                LIMIT 1
            )
            LEFT JOIN doctors d ON a.doctor_id = d.id
            LEFT JOIN departments dep ON d.department_id = dep.id
            $hCond
            ORDER BY 
                CASE WHEN b.type = 'ICU' THEN 1 ELSE 2 END,
                b.bed_number ASC
        ";
        
        $res = $conn->query($sql);
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                // If occupied, fetch latest observation and image for this active bed
                $row['latest_observation'] = null;
                $row['latest_image'] = null;
                $row['observations_count'] = 0;
                $row['images_count'] = 0;

                if ($row['status'] === 'Occupied' && !empty($row['patient_id'])) {
                    $bedNum = $conn->real_escape_string($row['bed_number']);
                    $patId = $conn->real_escape_string($row['patient_id']);

                    // Count total observations for this stay
                    $cntRes = $conn->query("SELECT COUNT(*) as c FROM bed_observations WHERE bed_number = '$bedNum' AND patient_id = '$patId'");
                    if ($cntRes && $cr = $cntRes->fetch_assoc()) {
                        $row['observations_count'] = (int)$cr['c'];
                    }

                    // Count total monitor images
                    $imgCntRes = $conn->query("SELECT COUNT(*) as c FROM bed_observation_images WHERE bed_number = '$bedNum' AND patient_id = '$patId'");
                    if ($imgCntRes && $ir = $imgCntRes->fetch_assoc()) {
                        $row['images_count'] = (int)$ir['c'];
                    }

                    // Fetch latest observation
                    $obsRes = $conn->query("
                        SELECT id, observation_hour, observed_at, vital_pulse, vital_bp, vital_spo2, vital_temp, vital_resp, notes, recorded_by_name,
                               DATE_FORMAT(observed_at, '%h:%i %p, %d %b') as formatted_time
                        FROM bed_observations 
                        WHERE bed_number = '$bedNum' AND patient_id = '$patId' 
                        ORDER BY id DESC LIMIT 1
                    ");
                    if ($obsRes && $obsRow = $obsRes->fetch_assoc()) {
                        $row['latest_observation'] = $obsRow;
                    }

                    // Fetch recent machine monitor images for this bed stay
                    $recentImgs = [];
                    $imgRes = $conn->query("
                        SELECT id, observation_id, title, image_path, thumbnail_path, captured_at,
                               DATE_FORMAT(captured_at, '%h:%i %p') as formatted_time,
                               DATE_FORMAT(captured_at, '%d %b') as formatted_date
                        FROM bed_observation_images 
                        WHERE bed_number = '$bedNum' AND patient_id = '$patId'
                        ORDER BY id DESC LIMIT 12
                    ");
                    if ($imgRes) {
                        while ($imgRow = $imgRes->fetch_assoc()) {
                            $recentImgs[] = $imgRow;
                        }
                    }
                    $row['recent_images'] = $recentImgs;
                    if (!empty($recentImgs)) {
                        $row['latest_image'] = $recentImgs[0];
                    }

                    // Fetch recent admission reports and files for this patient
                    $recentFiles = [];
                    $fileRes = $conn->query("
                        SELECT id, title, category, highlight, file_path, thumbnail_path, file_name, mime_type, file_size,
                               DATE_FORMAT(record_date, '%d %b, %h:%i %p') as formatted_date
                        FROM patient_files
                        WHERE patient_id = '$patId'
                        ORDER BY id DESC LIMIT 8
                    ");
                    if ($fileRes) {
                        while ($fr = $fileRes->fetch_assoc()) {
                            $filePath = $fr['file_path'] ?: '';
                            $thumbPath = $fr['thumbnail_path'] ?: '';
                            $mime = strtolower($fr['mime_type'] ?: '');
                            $ext = strtolower(pathinfo($fr['file_name'] ?: $filePath, PATHINFO_EXTENSION));
                            $isImg = (strpos($mime, 'image/') === 0 || in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp']));
                            $fr['is_image'] = $isImg;
                            $fr['url'] = !empty($filePath) ? $filePath : ('api/file.php?id=' . $fr['id']);
                            $fr['thumb_url'] = !empty($thumbPath) ? $thumbPath : ($isImg ? (!empty($filePath) ? $filePath : 'api/file.php?id=' . $fr['id'] . '&thumb=1') : '');
                            $recentFiles[] = $fr;
                        }
                    }
                    $row['recent_files'] = $recentFiles;
                }

                $beds[] = $row;
            }
        } else {
            jsonResponse(['status' => 'error', 'message' => $conn->error]);
        }
        
        jsonResponse(['status' => 'success', 'beds' => $beds, 'server_time' => date('Y-m-d H:i:s')]);
    }
    else if ($action === 'get_observations') {
        $bed_number = trim($_GET['bed_number'] ?? '');
        $patient_id = trim($_GET['patient_id'] ?? '');

        if (!$bed_number && !$patient_id) {
            jsonResponse(['status' => 'error', 'message' => 'Bed number or patient ID required.']);
        }

        $whereParts = [];
        if ($bed_number) $whereParts[] = "bo.bed_number = '" . $conn->real_escape_string($bed_number) . "'";
        if ($patient_id) $whereParts[] = "bo.patient_id = '" . $conn->real_escape_string($patient_id) . "'";
        $whereSql = implode(' AND ', $whereParts);

        $sql = "
            SELECT bo.*,
                   DATE_FORMAT(bo.observed_at, '%h:%i %p') as observed_time_12h,
                   DATE_FORMAT(bo.observed_at, '%d-%b-%Y') as observed_date,
                   DATE_FORMAT(bo.observed_at, '%Y-%m-%d %H:%i:%s') as observed_timestamp
            FROM bed_observations bo
            WHERE $whereSql
            ORDER BY bo.id DESC
        ";

        $res = $conn->query($sql);
        $observations = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $obsId = (int)$row['id'];
                // Fetch images for this observation
                $imgSql = "SELECT id, title, image_path, thumbnail_path, mime_type, file_size, captured_at, DATE_FORMAT(captured_at, '%h:%i %p') as captured_time FROM bed_observation_images WHERE observation_id = $obsId ORDER BY id ASC";
                $imgRes = $conn->query($imgSql);
                $images = [];
                if ($imgRes) {
                    while ($ir = $imgRes->fetch_assoc()) {
                        $images[] = $ir;
                    }
                }
                $row['images'] = $images;
                $observations[] = $row;
            }
        }

        jsonResponse(['status' => 'success', 'observations' => $observations]);
    }
    else if ($action === 'get_patient_admission_logs') {
        // Return full admission history & observation images for a patient (used in patient_profile.php & files view)
        $patient_id = trim($_GET['patient_id'] ?? '');
        if (!$patient_id) {
            jsonResponse(['status' => 'error', 'message' => 'Patient ID required.']);
        }
        $patEsc = $conn->real_escape_string($patient_id);

        $sql = "
            SELECT bo.*,
                   DATE_FORMAT(bo.observed_at, '%h:%i %p') as observed_time_12h,
                   DATE_FORMAT(bo.observed_at, '%d %b %Y') as observed_date,
                   DATE_FORMAT(bo.observed_at, '%Y-%m-%d %H:%i') as observed_timestamp
            FROM bed_observations bo
            WHERE bo.patient_id = '$patEsc'
            ORDER BY bo.id DESC
        ";

        $res = $conn->query($sql);
        $observations = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $obsId = (int)$row['id'];
                $imgSql = "SELECT id, title, image_path, thumbnail_path, mime_type, file_size, captured_at, DATE_FORMAT(captured_at, '%h:%i %p') as captured_time FROM bed_observation_images WHERE observation_id = $obsId ORDER BY id ASC";
                $imgRes = $conn->query($imgSql);
                $images = [];
                if ($imgRes) {
                    while ($ir = $imgRes->fetch_assoc()) {
                        $images[] = $ir;
                    }
                }
                $row['images'] = $images;
                $observations[] = $row;
            }
        }

        jsonResponse(['status' => 'success', 'observations' => $observations]);
    }
}
// -------------------------------------------------------------
// POST REQUESTS
// -------------------------------------------------------------
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data) $data = $_POST;

    // -------------------------------------------------------------
    // ACTION: ADD HOURLY OBSERVATION & MONITOR IMAGES
    // -------------------------------------------------------------
    if ($action === 'add_observation') {
        $bed_number = trim($_POST['bed_number'] ?? ($data['bed_number'] ?? ''));
        $patient_id = trim($_POST['patient_id'] ?? ($data['patient_id'] ?? ''));
        $observation_hour = trim($_POST['observation_hour'] ?? ($data['observation_hour'] ?? ''));
        $vital_pulse = trim($_POST['vital_pulse'] ?? ($data['vital_pulse'] ?? ''));
        $vital_bp = trim($_POST['vital_bp'] ?? ($data['vital_bp'] ?? ''));
        $vital_spo2 = trim($_POST['vital_spo2'] ?? ($data['vital_spo2'] ?? ''));
        $vital_temp = trim($_POST['vital_temp'] ?? ($data['vital_temp'] ?? ''));
        $vital_resp = trim($_POST['vital_resp'] ?? ($data['vital_resp'] ?? ''));
        $notes = trim($_POST['notes'] ?? ($data['notes'] ?? ''));

        if (!$bed_number || !$patient_id) {
            jsonResponse(['status' => 'error', 'message' => 'Bed number and Patient ID are required.']);
        }

        // Validate bed is occupied by this patient
        $bStmt = $conn->prepare("SELECT id, status, patient_id, type FROM beds WHERE bed_number = ?");
        $bStmt->bind_param("s", $bed_number);
        $bStmt->execute();
        $bRes = $bStmt->get_result();
        $bedRow = $bRes->fetch_assoc();

        if (!$bedRow) {
            jsonResponse(['status' => 'error', 'message' => "Bed $bed_number does not exist."]);
        }
        if ($bedRow['status'] !== 'Occupied' || $bedRow['patient_id'] !== $patient_id) {
            jsonResponse(['status' => 'error', 'message' => "Patient $patient_id is not actively assigned to Bed $bed_number."]);
        }

        // Get current active appointment ID
        $app_res = $conn->query("SELECT id FROM appointments WHERE patient_id = '$patient_id' ORDER BY id DESC LIMIT 1");
        $appointment_id = ($app_res && $ar = $app_res->fetch_assoc()) ? (int)$ar['id'] : null;

        $observed_at = date('Y-m-d H:i:s');
        if (empty($observation_hour)) {
            $observation_hour = "Hour " . date('h A');
        }

        // Insert bed observation
        $stmtObs = $conn->prepare("
            INSERT INTO bed_observations 
            (hospital_id, bed_number, patient_id, appointment_id, observed_at, observation_hour, vital_pulse, vital_bp, vital_spo2, vital_temp, vital_resp, notes, recorded_by_id, recorded_by_name)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $target_hosp = $hospital_id > 0 ? (int)$hospital_id : 1;
        $stmtObs->bind_param("ississssssssis", 
            $target_hosp, $bed_number, $patient_id, $appointment_id, $observed_at, 
            $observation_hour, $vital_pulse, $vital_bp, $vital_spo2, $vital_temp, $vital_resp, 
            $notes, $staff_id, $staff_name
        );

        if (!$stmtObs->execute()) {
            jsonResponse(['status' => 'error', 'message' => 'Failed to record observation: ' . $conn->error]);
        }
        $observation_id = $conn->insert_id;

        // Process uploaded monitor images (supports both multipart $_FILES and base64 data URLs)
        $uploadedCount = 0;
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];
        
        $origDir = __DIR__ . "/../uploads/patients/{$patient_id}/originals";
        $thumbDir = __DIR__ . "/../uploads/patients/{$patient_id}/thumbnails";
        if (!is_dir($origDir)) @mkdir($origDir, 0755, true);
        if (!is_dir($thumbDir)) @mkdir($thumbDir, 0755, true);

        // 1. Collect files from $_FILES
        $fileList = [];
        if (isset($_FILES['images']) && is_array($_FILES['images']['name'])) {
            for ($i = 0; $i < count($_FILES['images']['name']); $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $fileList[] = [
                        'name' => $_FILES['images']['name'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'size' => $_FILES['images']['size'][$i],
                        'is_direct' => false
                    ];
                }
            }
        } elseif (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $fileList[] = [
                'name' => $_FILES['image']['name'],
                'tmp_name' => $_FILES['image']['tmp_name'],
                'size' => $_FILES['image']['size'],
                'is_direct' => false
            ];
        }

        // 2. Collect base64 images (as fallback or primary if files array is empty)
        $b64List = [];
        if (empty($fileList)) {
            if (!empty($_POST['images_base64'])) {
                if (is_array($_POST['images_base64'])) {
                    $b64List = $_POST['images_base64'];
                } else {
                    $decoded = json_decode($_POST['images_base64'], true);
                    if (is_array($decoded)) $b64List = $decoded;
                    else $b64List = [$_POST['images_base64']];
                }
            } elseif (!empty($_POST['image_base64'])) {
                $b64List = [$_POST['image_base64']];
            }
        }

        foreach ($b64List as $b64) {
            if (empty($b64)) continue;
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $b64, $m)) {
                $ext = strtolower($m[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
                $b64 = substr($b64, strpos($b64, ',') + 1);
            }
            $bin = base64_decode($b64);
            if ($bin) {
                $uniqueName = "bed_{$bed_number}_" . date('Ymd_His') . "_" . uniqid() . ".{$ext}";
                $destPath = "{$origDir}/{$uniqueName}";
                if (file_put_contents($destPath, $bin)) {
                    $fileList[] = [
                        'name' => "camera_snap_{$uniqueName}",
                        'tmp_name' => $destPath,
                        'dest_path' => $destPath,
                        'unique_name' => $uniqueName,
                        'size' => strlen($bin),
                        'is_direct' => true
                    ];
                }
            }
        }

        foreach ($fileList as $fIdx => $fItem) {
            $fileSize = $fItem['size'];
            if ($fileSize > 25 * 1024 * 1024) continue; // 25MB limit

            if (!empty($fItem['is_direct'])) {
                $uniqueName = $fItem['unique_name'];
                $destPath = $fItem['dest_path'];
                $ext = pathinfo($uniqueName, PATHINFO_EXTENSION);
            } else {
                $origName = basename($fItem['name']);
                $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExts)) $ext = 'jpg';
                $uniqueName = "bed_{$bed_number}_" . date('Ymd_His') . "_" . uniqid() . ".{$ext}";
                $destPath = "{$origDir}/{$uniqueName}";
                $tmpPath = $fItem['tmp_name'];
                if (!move_uploaded_file($tmpPath, $destPath) && !(php_sapi_name() === 'cli' && copy($tmpPath, $destPath))) {
                    continue;
                }
            }

            $dbPath = "uploads/patients/{$patient_id}/originals/{$uniqueName}";
            $thumbDbPath = $dbPath;
            $thumbName = "thumb_{$uniqueName}";
            $thumbDest = "{$thumbDir}/{$thumbName}";
            if (makeBedThumbnail($destPath, $thumbDest, 400, 300)) {
                $thumbDbPath = "uploads/patients/{$patient_id}/thumbnails/{$thumbName}";
            }

            $mime = @mime_content_type($destPath) ?: 'image/jpeg';
            $fileData = @file_get_contents($destPath);
            $imgTitle = "Bed {$bed_number} Monitor (" . date('h:i A') . ")";
            if (count($fileList) > 1) {
                $imgTitle .= " #" . ($fIdx + 1);
            }

            // 1) Save into patient_files for permanent archive under 'Admission Monitor'
            $cat = 'Admission Monitor';
            $highlight = "Bed {$bed_number} - {$observation_hour}";
            $descText = "Machine Monitor Record at {$observation_hour}. " . (!empty($notes) ? "Notes: {$notes}. " : "") . 
                        (!empty($vital_bp) ? "BP: {$vital_bp}, " : "") . 
                        (!empty($vital_spo2) ? "SpO2: {$vital_spo2}, " : "") . 
                        (!empty($vital_pulse) ? "Pulse: {$vital_pulse}" : "");
            $tags = "Admission Monitor, Bed {$bed_number}, {$observation_hour}";
            $patFileId = null;

            $stmtPf = $conn->prepare("
                INSERT INTO patient_files 
                (patient_id, appointment_id, title, category, highlight, description, record_date, tags, file_name, file_path, thumbnail_path, mime_type, file_size, file_data, uploaded_by_id, uploaded_by_name)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmtPf) {
                $origSaveName = basename($destPath);
                $stmtPf->bind_param("sisssssssssssiss", 
                    $patient_id, $appointment_id, $imgTitle, $cat, $highlight, $descText, $observed_at, $tags,
                    $origSaveName, $dbPath, $thumbDbPath, $mime, $fileSize, $fileData, $staff_id, $staff_name
                );
                if ($stmtPf->execute()) {
                    $patFileId = $stmtPf->insert_id;
                }
            }

            // 2) Save into bed_observation_images
            $stmtImg = $conn->prepare("
                INSERT INTO bed_observation_images 
                (observation_id, bed_number, patient_id, appointment_id, title, image_path, thumbnail_path, mime_type, file_size, captured_at, patient_file_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            if ($stmtImg) {
                $stmtImg->bind_param("isssssssisi", 
                    $observation_id, $bed_number, $patient_id, $appointment_id, $imgTitle, 
                    $dbPath, $thumbDbPath, $mime, $fileSize, $observed_at, $patFileId
                );
                $stmtImg->execute();
                $uploadedCount++;
            }
        }

        // Timeline log
        $time_now = date('h:i A');
        $eventMsg = "Hourly Monitor Check logged for Bed [$bed_number] ($observation_hour). " . ($uploadedCount > 0 ? "[$uploadedCount Monitor Photo(s) Attached]. " : "") . ($notes ? "Notes: $notes" : "");
        if ($appointment_id) {
            $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $stmtTL->bind_param("isss", $appointment_id, $patient_id, $time_now, $eventMsg);
            $stmtTL->execute();
        }

        jsonResponse([
            'status' => 'success',
            'message' => "Observation logged successfully. ($uploadedCount photo(s) saved to admission records).",
            'observation_id' => $observation_id,
            'uploaded_images' => $uploadedCount
        ]);
    }

    // -------------------------------------------------------------
    // ACTION: ADMIT PATIENT (WITH ZERO DUPLICATE ADMISSION VALIDATION)
    // -------------------------------------------------------------
    else if ($action === 'admit') {
        $bed_number = trim($data['bed_number'] ?? '');
        $patient_id = trim($data['patient_id'] ?? '');
        $doctor_id = !empty($data['doctor_id']) ? trim($data['doctor_id']) : null;
        if ($doctor_id) {
            $docChk = $conn->prepare("SELECT id FROM doctors WHERE id = ?");
            $docChk->bind_param("s", $doctor_id);
            $docChk->execute();
            if ($docChk->get_result()->num_rows === 0) {
                $doctor_id = null;
            }
        }
        $reason = trim($data['reason'] ?? 'Inpatient Observation & Care');
        
        if (!$bed_number || !$patient_id) {
            jsonResponse(['status' => 'error', 'message' => 'Please select both a bed and a patient.']);
        }
        
        // 1. STRICT VALIDATION: Check if patient is ALREADY admitted to ANY bed in hospital!
        $chkPat = $conn->prepare("SELECT bed_number, type, wing FROM beds WHERE patient_id = ? AND status = 'Occupied'");
        $chkPat->bind_param("s", $patient_id);
        $chkPat->execute();
        $chkRes = $chkPat->get_result();
        if ($chkRes->num_rows > 0) {
            $activeBed = $chkRes->fetch_assoc();
            jsonResponse([
                'status' => 'error', 
                'message' => "Validation Error: Patient ($patient_id) is ALREADY admitted to Bed {$activeBed['bed_number']} ({$activeBed['type']} Ward)! A patient can never be added to multiple beds simultaneously. Please transfer or discharge the patient first."
            ]);
        }

        // 2. Check if bed is available
        $bRes = $conn->query("SELECT status, type FROM beds WHERE bed_number = '$bed_number'");
        $bRow = $bRes ? $bRes->fetch_assoc() : null;
        if (!$bRow || $bRow['status'] !== 'Available') {
            jsonResponse(['status' => 'error', 'message' => "Bed $bed_number is currently occupied or unavailable."]);
        }
        
        // Update bed to occupied
        $stmtU = $conn->prepare("UPDATE beds SET status = 'Occupied', patient_id = ? WHERE bed_number = ?");
        $stmtU->bind_param("ss", $patient_id, $bed_number);
        $stmtU->execute();
        
        // Create appointment / admission record
        $status_str = "Admitted (" . ($bRow['type'] === 'ICU' ? 'ICU Bed' : 'Ward Bed') . ")";
        $today = date('Y-m-d');
        $time_slot = date('h:i A');
        $stage = 5;
        
        // Determine valid hospital_id
        $target_hosp = $hospital_id > 0 ? (int)$hospital_id : 1;
        $hRes = $conn->query("SELECT hospital_id FROM beds WHERE bed_number = '$bed_number'");
        if ($hRes && $hr = $hRes->fetch_assoc()) {
            if (!empty($hr['hospital_id']) && (int)$hr['hospital_id'] > 0) {
                $target_hosp = (int)$hr['hospital_id'];
            }
        }
        
        $stmtApp = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, status, stage, bed_number, hospital_id) VALUES (?, ?, 'Inpatient Admission', ?, ?, ?, ?, ?, ?, ?)");
        $stmtApp->bind_param("ssssssisi", $patient_id, $doctor_id, $today, $time_slot, $reason, $status_str, $stage, $bed_number, $target_hosp);
        $stmtApp->execute();
        $app_id = $conn->insert_id;
        
        // Timeline
        $time_now = date('h:i A');
        $event = "Admitted to {$bRow['type']} Bed [$bed_number]. Reason: $reason";
        $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
        $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event);
        $stmtTL->execute();
        
        jsonResponse(['status' => 'success', 'message' => "Patient admitted to Bed $bed_number successfully."]);
    }

    // -------------------------------------------------------------
    // ACTION: DISCHARGE PATIENT (ALL MONITOR RECORDS PRESERVED)
    // -------------------------------------------------------------
    else if ($action === 'discharge') {
        $number = trim($data['number'] ?? '');
        $notes = trim($data['notes'] ?? 'Discharged from inpatient bed. Ready for home rest.');
        
        $stmt = $conn->prepare("SELECT patient_id, type FROM beds WHERE bed_number = ?");
        $stmt->bind_param("s", $number);
        $stmt->execute();
        $res = $stmt->get_result();
        $bedRow = $res->fetch_assoc();
        $patient_id = $bedRow['patient_id'] ?? null;
        
        if ($patient_id) {
            // Free the bed
            $conn->query("UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$number'");
            
            // Update appointment
            $conn->query("UPDATE appointments SET status = 'Discharged from Bed', bed_number = NULL WHERE patient_id = '$patient_id' AND bed_number = '$number'");
            
            // Timeline event
            $time_now = date('h:i A');
            $event = "Discharged from {$bedRow['type']} Bed $number. $notes. All admission monitor files and hourly observation images permanently archived in patient file.";
            $app_res = $conn->query("SELECT id FROM appointments WHERE patient_id = '$patient_id' ORDER BY id DESC LIMIT 1");
            $app_id = ($app_res && $ar = $app_res->fetch_assoc()) ? $ar['id'] : null;
            
            if ($app_id) {
                $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
                $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event);
                $stmtTL->execute();
            }
            
            jsonResponse([
                'status' => 'success', 
                'message' => "Patient successfully discharged. Bed $number is now sanitized and available. All hourly machine monitor images have been preserved in patient file."
            ]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'No patient found assigned to this bed.']);
        }
    }

    // -------------------------------------------------------------
    // ACTION: TRANSFER BED
    // -------------------------------------------------------------
    else if ($action === 'transfer') {
        $current_bed = trim($data['current_bed'] ?? '');
        $new_bed = trim($data['new_bed'] ?? '');
        $reason = trim($data['reason'] ?? 'Bed relocation / Clinical transfer');
        
        if (!$current_bed || !$new_bed) {
            jsonResponse(['status' => 'error', 'message' => 'Both current and destination beds are required.']);
        }
        if ($current_bed === $new_bed) {
            jsonResponse(['status' => 'error', 'message' => 'Current bed and destination bed cannot be the same.']);
        }
        
        // Check current bed
        $curRes = $conn->query("SELECT patient_id FROM beds WHERE bed_number = '$current_bed'");
        $curRow = $curRes ? $curRes->fetch_assoc() : null;
        $patient_id = $curRow['patient_id'] ?? null;
        
        if (!$patient_id) {
            jsonResponse(['status' => 'error', 'message' => "No patient currently assigned to Bed $current_bed."]);
        }
        
        // Check new bed
        $newRes = $conn->query("SELECT status, type FROM beds WHERE bed_number = '$new_bed'");
        $newRow = $newRes ? $newRes->fetch_assoc() : null;
        if (!$newRow || $newRow['status'] !== 'Available') {
            jsonResponse(['status' => 'error', 'message' => "Destination Bed $new_bed is not available."]);
        }
        
        // Free current bed
        $conn->query("UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$current_bed'");
        
        // Occupy new bed
        $stmtNew = $conn->prepare("UPDATE beds SET status = 'Occupied', patient_id = ? WHERE bed_number = ?");
        $stmtNew->bind_param("ss", $patient_id, $new_bed);
        $stmtNew->execute();
        
        // Update appointment
        $new_status = "Admitted (" . ($newRow['type'] === 'ICU' ? 'ICU Bed' : 'Ward Bed') . ")";
        $conn->query("UPDATE appointments SET bed_number = '$new_bed', status = '$new_status' WHERE patient_id = '$patient_id' AND bed_number = '$current_bed'");
        
        // Timeline
        $time_now = date('h:i A');
        $event = "Transferred from bed $current_bed to bed $new_bed. Reason: $reason";
        $app_res = $conn->query("SELECT id FROM appointments WHERE patient_id = '$patient_id' ORDER BY id DESC LIMIT 1");
        $app_id = ($app_res && $ar = $app_res->fetch_assoc()) ? $ar['id'] : null;
        if ($app_id) {
            $stmtTL = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $stmtTL->bind_param("isss", $app_id, $patient_id, $time_now, $event);
            $stmtTL->execute();
        }
        
        jsonResponse(['status' => 'success', 'message' => "Patient successfully transferred to Bed $new_bed."]);
    }

    // -------------------------------------------------------------
    // ACTION: ADD BED TO INVENTORY
    // -------------------------------------------------------------
    else if ($action === 'add') {
        $type = trim($data['type'] ?? 'OPD');
        $number = trim($data['number'] ?? '');
        $wing = trim($data['wing'] ?? ($type === 'ICU' ? 'Critical Care Wing, 3rd Floor' : 'General Ward Floor, 2nd Floor'));
        $id = 'bed-' . time() . '-' . rand(100, 999);
        
        if (!$number) {
            jsonResponse(['status' => 'error', 'message' => 'Bed designation number is required.']);
        }
        
        // Check duplicate
        $chk = $conn->prepare("SELECT id FROM beds WHERE bed_number = ? AND (hospital_id = ? OR hospital_id IS NULL)");
        $chk->bind_param("si", $number, $hospital_id);
        $chk->execute();
        if ($chk->get_result()->num_rows > 0) {
            jsonResponse(['status' => 'error', 'message' => "Bed number '$number' already exists in this hospital."]);
        }
        
        $stmt = $conn->prepare("INSERT INTO beds (id, bed_number, type, wing, status, hospital_id) VALUES (?, ?, ?, ?, 'Available', ?)");
        $stmt->bind_param("ssssi", $id, $number, $type, $wing, $hospital_id);
        
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => "Bed $number ($type) successfully registered."]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to add bed: ' . $conn->error]);
        }
    }

    // -------------------------------------------------------------
    // ACTION: DELETE BED
    // -------------------------------------------------------------
    else if ($action === 'delete') {
        $number = trim($data['number'] ?? '');
        $stmt = $conn->prepare("DELETE FROM beds WHERE bed_number = ? AND status = 'Available'");
        $stmt->bind_param("s", $number);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                jsonResponse(['status' => 'success', 'message' => "Bed $number removed from inventory."]);
            } else {
                jsonResponse(['status' => 'error', 'message' => "Bed is currently occupied or was not found."]);
            }
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to delete bed.']);
        }
    }

    // -------------------------------------------------------------
    // ACTION: EDIT BED
    // -------------------------------------------------------------
    else if ($action === 'edit_bed') {
        $bed_number = trim($data['bed_number'] ?? '');
        $type = trim($data['type'] ?? 'OPD');
        $wing = trim($data['wing'] ?? '');
        
        $stmt = $conn->prepare("UPDATE beds SET type = ?, wing = ? WHERE bed_number = ?");
        $stmt->bind_param("sss", $type, $wing, $bed_number);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => "Bed $bed_number details updated."]);
        } else {
            jsonResponse(['status' => 'error', 'message' => "Failed to update bed: " . $conn->error]);
        }
    }
}
?>
