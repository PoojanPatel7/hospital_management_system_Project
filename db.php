<?php
// Auto-detect environment: Localhost (XAMPP) vs InfinityFree Live Server
$isLocal = (
    (isset($_SERVER['HTTP_HOST']) && (
        strpos($_SERVER['HTTP_HOST'], 'localhost') !== false || 
        strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false
    )) || 
    (php_sapi_name() === 'cli')
);

if ($isLocal) {
    // Localhost XAMPP Settings
    $host = 'localhost';
    $user = 'root';
    $password = '';
    $dbname = 'hospital_db';
    $port = 3306;

    $conn = @new mysqli($host, $user, $password);
    if ($conn && !$conn->connect_error) {
        $conn->query("CREATE DATABASE IF NOT EXISTS `$dbname`");
    }
    $conn = new mysqli($host, $user, $password, $dbname, $port);
} else {
    // InfinityFree Live Hosting Settings
    $host = 'sql113.infinityfree.com';
    $user = 'if0_42838894';
    $password = 'gDaughDyA4Dnmdw';
    $dbname = 'if0_42838894_HMS';
    $port = 3306;

    // Connect directly to InfinityFree database (Never run CREATE DATABASE on shared hosting)
    $conn = new mysqli($host, $user, $password, $dbname, $port);
}

if ($conn->connect_error) {
    die(json_encode([
        'status' => 'error', 
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]));
}

$conn->set_charset("utf8mb4");

// Auto start session if not started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto update schema for new columns safely
$cols = [];
$res = $conn->query("SHOW COLUMNS FROM patients");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $cols[] = $row['Field'];
    }
}
if (!in_array('gender', $cols)) $conn->query("ALTER TABLE patients ADD COLUMN gender VARCHAR(20) DEFAULT NULL");
if (!in_array('blood_group', $cols)) $conn->query("ALTER TABLE patients ADD COLUMN blood_group VARCHAR(10) DEFAULT NULL");
if (!in_array('age', $cols)) $conn->query("ALTER TABLE patients ADD COLUMN age VARCHAR(20) DEFAULT NULL");
if (!in_array('emergency_contact_name', $cols)) $conn->query("ALTER TABLE patients ADD COLUMN emergency_contact_name VARCHAR(255) DEFAULT NULL");
if (!in_array('emergency_contact_phone', $cols)) $conn->query("ALTER TABLE patients ADD COLUMN emergency_contact_phone VARCHAR(50) DEFAULT NULL");
if (!in_array('hospital_id', $cols)) $conn->query("ALTER TABLE patients ADD COLUMN hospital_id INT DEFAULT NULL");

// Hospitals table
$conn->query("CREATE TABLE IF NOT EXISTS hospitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    username VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Doctor categories
$conn->query("CREATE TABLE IF NOT EXISTS doctor_categories (
    doctor_id VARCHAR(50) NOT NULL,
    department_id VARCHAR(100) NOT NULL,
    PRIMARY KEY (doctor_id, department_id)
)");

// Doctor per-day schedules (timing, duration, availability per day of week)
$conn->query("CREATE TABLE IF NOT EXISTS doctor_day_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id VARCHAR(50) NOT NULL,
    day_of_week VARCHAR(20) NOT NULL,
    is_available TINYINT(1) DEFAULT 1,
    start_time VARCHAR(10) DEFAULT '09:00 AM',
    end_time VARCHAR(10) DEFAULT '05:00 PM',
    duration_minutes INT DEFAULT 30,
    break_start VARCHAR(10) DEFAULT NULL,
    break_end VARCHAR(10) DEFAULT NULL,
    custom_slots TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY doc_day (doctor_id, day_of_week),
    KEY (doctor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Add hospital_id to other tables
function addColSafe($conn, $table, $col, $def) {
    $res = $conn->query("SHOW COLUMNS FROM $table");
    if ($res) {
        $hasCol = false;
        while ($row = $res->fetch_assoc()) {
            if ($row['Field'] === $col) $hasCol = true;
        }
        if (!$hasCol) {
            $conn->query("ALTER TABLE $table ADD COLUMN $col $def");
        }
    }
}
addColSafe($conn, 'doctors', 'hospital_id', 'INT DEFAULT NULL');
addColSafe($conn, 'doctors', 'phone', 'VARCHAR(50) DEFAULT NULL');
addColSafe($conn, 'appointments', 'hospital_id', 'INT DEFAULT NULL');
addColSafe($conn, 'beds', 'hospital_id', 'INT DEFAULT NULL');
addColSafe($conn, 'departments', 'hospital_id', 'INT DEFAULT NULL');

// Migrate doctors to categories if empty
$conn->query("INSERT IGNORE INTO doctor_categories (doctor_id, department_id) SELECT id, department_id FROM doctors WHERE department_id IS NOT NULL");

// Backfill hospital_id to primary hospital for existing records
$hRes = $conn->query("SELECT id FROM hospitals ORDER BY id ASC LIMIT 1");
if ($hRes && $hRow = $hRes->fetch_assoc()) {
    $firstHospId = (int)$hRow['id'];
    $conn->query("UPDATE patients SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE doctors SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE appointments SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE beds SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE departments SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
