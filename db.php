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

// Patient Files schema updates for Binary BLOB storage
$pfCols = [];
$pfRes = $conn->query("SHOW COLUMNS FROM patient_files");
if ($pfRes) {
    while ($r = $pfRes->fetch_assoc()) {
        $pfCols[] = $r['Field'];
    }
}
if (!in_array('file_data', $pfCols)) $conn->query("ALTER TABLE patient_files ADD COLUMN file_data LONGBLOB DEFAULT NULL");
if (!in_array('file_name', $pfCols)) $conn->query("ALTER TABLE patient_files ADD COLUMN file_name VARCHAR(255) DEFAULT NULL");
if (!in_array('mime_type', $pfCols)) $conn->query("ALTER TABLE patient_files ADD COLUMN mime_type VARCHAR(100) DEFAULT NULL");
if (!in_array('file_size', $pfCols)) $conn->query("ALTER TABLE patient_files ADD COLUMN file_size INT DEFAULT 0");

// Auto-migrate any unmigrated files from disk into binary BLOB
$unmigratedRes = $conn->query("SELECT id, file_path FROM patient_files WHERE file_data IS NULL OR file_size = 0 LIMIT 10");
if ($unmigratedRes && $unmigratedRes->num_rows > 0) {
    while ($uRow = $unmigratedRes->fetch_assoc()) {
        $uId = (int)$uRow['id'];
        $uPath = $uRow['file_path'];
        $uDiskPath = __DIR__ . '/' . ltrim(preg_replace('/\?.*$/', '', $uPath), '/');
        if (file_exists($uDiskPath)) {
            $uContent = file_get_contents($uDiskPath);
            $uSize = strlen($uContent);
            $uName = basename($uPath);
            $uMime = mime_content_type($uDiskPath);
            $uStmt = $conn->prepare("UPDATE patient_files SET file_name = ?, mime_type = ?, file_size = ?, file_data = ?, file_path = ? WHERE id = ?");
            $uUrl = "api/file.php?id=" . $uId . "&file=" . urlencode($uName);
            $uStmt->bind_param("ssissi", $uName, $uMime, $uSize, $uContent, $uUrl, $uId);
            $uStmt->execute();
        }
    }
}

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
$firstHospId = 1;
if ($hRes && $hRow = $hRes->fetch_assoc()) {
    $firstHospId = (int)$hRow['id'];
    $conn->query("UPDATE patients SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE doctors SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE appointments SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE beds SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
    $conn->query("UPDATE departments SET hospital_id = $firstHospId WHERE hospital_id IS NULL OR hospital_id = 0");
}

// Staff & Employees table
$conn->query("CREATE TABLE IF NOT EXISTS staff (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    staff_code VARCHAR(50) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) DEFAULT NULL,
    phone VARCHAR(50) DEFAULT NULL,
    role VARCHAR(100) NOT NULL,
    department VARCHAR(100) NOT NULL,
    gender VARCHAR(20) DEFAULT 'Female',
    date_of_birth DATE DEFAULT NULL,
    joining_date DATE DEFAULT NULL,
    shift VARCHAR(50) DEFAULT 'Morning (08:00 - 16:00)',
    status VARCHAR(50) DEFAULT 'Active',
    qualification VARCHAR(255) DEFAULT NULL,
    salary DECIMAL(10,2) DEFAULT 0.00,
    blood_group VARCHAR(10) DEFAULT NULL,
    emergency_contact VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    is_user TINYINT(1) DEFAULT 0,
    username VARCHAR(100) DEFAULT NULL,
    password VARCHAR(255) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_hospital (hospital_id),
    UNIQUE KEY idx_hosp_code (hospital_id, staff_code),
    UNIQUE KEY idx_staff_user (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Staff Attendance records
$conn->query("CREATE TABLE IF NOT EXISTS staff_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    staff_id INT NOT NULL,
    date DATE NOT NULL,
    status ENUM('Present', 'Absent', 'Late', 'Half Day', 'On Leave') NOT NULL DEFAULT 'Present',
    check_in_time TIME DEFAULT NULL,
    check_out_time TIME DEFAULT NULL,
    working_hours DECIMAL(4,2) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    marked_by VARCHAR(100) DEFAULT 'Admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_hosp_date (hospital_id, date),
    UNIQUE KEY unique_staff_day (staff_id, date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// AI Conversations table
$conn->query("CREATE TABLE IF NOT EXISTS ai_conversations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    user_type ENUM('admin', 'staff') NOT NULL DEFAULT 'admin',
    user_id VARCHAR(100) NOT NULL,
    user_role VARCHAR(100) DEFAULT 'Admin',
    title VARCHAR(255) DEFAULT 'New Chat',
    model_used VARCHAR(100) DEFAULT 'qwen3:4b',
    page_context VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_hospital_user (hospital_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS ai_chat_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    role ENUM('user', 'assistant', 'system') NOT NULL,
    content TEXT NOT NULL,
    tokens_used INT DEFAULT 0,
    sql_executed TEXT DEFAULT NULL,
    response_time_ms INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES ai_conversations(id) ON DELETE CASCADE,
    KEY idx_conversation (conversation_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS ai_pending_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    message_id INT NOT NULL,
    hospital_id INT NOT NULL,
    action_type ENUM('INSERT', 'UPDATE', 'DELETE') NOT NULL,
    target_table VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    sql_query TEXT NOT NULL,
    sql_params TEXT DEFAULT NULL,
    validation_data TEXT DEFAULT NULL,
    status ENUM('pending', 'confirmed', 'cancelled', 'expired', 'executed', 'failed') DEFAULT 'pending',
    confirmed_at TIMESTAMP NULL,
    executed_at TIMESTAMP NULL,
    error_message TEXT DEFAULT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_status (status),
    KEY idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS ai_action_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL,
    user_id VARCHAR(100) NOT NULL,
    user_role VARCHAR(100) NOT NULL,
    action_id INT DEFAULT NULL,
    action_type VARCHAR(20) NOT NULL,
    target_table VARCHAR(100) NOT NULL,
    sql_executed TEXT NOT NULL,
    rows_affected INT DEFAULT 0,
    success TINYINT(1) DEFAULT 1,
    error_message TEXT DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY idx_hospital (hospital_id),
    KEY idx_user (user_id),
    KEY idx_date (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

$conn->query("CREATE TABLE IF NOT EXISTS ai_config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_id INT NOT NULL UNIQUE,
    enabled TINYINT(1) DEFAULT 1,
    model_name VARCHAR(100) DEFAULT 'qwen3:4b',
    ollama_url VARCHAR(255) DEFAULT 'http://localhost:11434',
    max_tokens INT DEFAULT 2048,
    temperature DECIMAL(3,2) DEFAULT 0.30,
    context_window INT DEFAULT 8192,
    rate_limit_per_minute INT DEFAULT 30,
    allowed_tables TEXT DEFAULT NULL,
    system_prompt_override TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Seed realistic hospital staff if table empty
$staffCount = 0;
$sCheck = $conn->query("SELECT COUNT(*) AS c FROM staff");
if ($sCheck && $r = $sCheck->fetch_assoc()) {
    $staffCount = (int)$r['c'];
}

if ($staffCount === 0) {
    $seedStaff = [
        ['STF-101', 'Mary', 'Joseph', 'mary.joseph@carepulse.org', '+91 98201 12345', 'Senior Staff Nurse', 'Nursing', 'Female', '1988-04-12', '2021-03-15', 'Morning (08:00 - 16:00)', 'Active', 'B.Sc Nursing, Critical Care Cert', 45000.00, 'B+', '+91 98201 54321', 'A-402 Palm Heights, Medical Enclave', 1, 'nurse.mary', password_hash('staff123', PASSWORD_DEFAULT)],
        ['STF-102', 'Rajesh', 'Kumar', 'rajesh.kumar@carepulse.org', '+91 98202 23456', 'Head Pharmacist', 'Pharmacy', 'Male', '1985-08-22', '2019-07-01', 'General (09:00 - 18:00)', 'Active', 'M.Pharm, Registered Pharmacist', 52000.00, 'O+', '+91 98202 65432', 'Flat 12, Sunrise Residency, Civil Lines', 1, 'pharm.rajesh', password_hash('staff123', PASSWORD_DEFAULT)],
        ['STF-103', 'Ananya', 'Sharma', 'ananya.s@carepulse.org', '+91 98203 34567', 'Chief Medical Lab Technician', 'Laboratory', 'Female', '1992-11-05', '2022-01-10', 'Morning (08:00 - 16:00)', 'Active', 'M.Sc Medical Microbiology, DMLT', 42000.00, 'A+', '+91 98203 76543', 'B-10, Green Avenue, Model Town', 1, 'lab.ananya', password_hash('staff123', PASSWORD_DEFAULT)],
        ['STF-104', 'Vikram', 'Patel', 'vikram.p@carepulse.org', '+91 98204 45678', 'Front Desk Officer / Receptionist', 'Reception', 'Male', '1995-02-18', '2023-05-12', 'Morning (08:00 - 16:00)', 'Active', 'B.Com, Hospital Admin Diploma', 28000.00, 'AB+', '+91 98204 87654', 'Rowhouse 4, Silver Springs', 1, 'reception.vikram', password_hash('staff123', PASSWORD_DEFAULT)],
        ['STF-105', 'Priya', 'Menon', 'priya.m@carepulse.org', '+91 98205 56789', 'Staff Nurse - Ward 2', 'Nursing', 'Female', '1994-09-30', '2022-08-20', 'Evening (14:00 - 22:00)', 'Active', 'GNM Nursing, BLS Certified', 35000.00, 'O-', '+91 98205 98765', 'Flat 304, Royal Palms', 0, NULL, NULL],
        ['STF-106', 'Sunil', 'Verma', 'sunil.v@carepulse.org', '+91 98206 67890', 'Radiology Technician', 'Radiology', 'Male', '1990-06-14', '2020-11-15', 'General (09:00 - 18:00)', 'Active', 'Diploma in Radiography (DRIT)', 38000.00, 'A-', '+91 98206 09876', 'Plot 45, Shanti Nagar', 0, NULL, NULL],
        ['STF-107', 'Meena', 'Devi', 'meena.devi@carepulse.org', '+91 98207 78901', 'Housekeeping Supervisor', 'Maintenance', 'Female', '1987-12-03', '2018-04-10', 'Morning (08:00 - 16:00)', 'Active', 'Hospital Sanitation & Hygiene Cert', 24000.00, 'B-', '+91 98207 10987', 'Sector 14, Housing Colony', 0, NULL, NULL],
        ['STF-108', 'David', 'Dsouza', 'david.d@carepulse.org', '+91 98208 89012', 'Chief Security Officer', 'Security', 'Male', '1982-03-25', '2017-09-01', 'Night (22:00 - 08:00)', 'Active', 'Ex-Paramilitary, Security Mgmt Cert', 32000.00, 'O+', '+91 98208 21098', 'Building 2B, Defence Colony', 0, NULL, NULL],
        ['STF-109', 'Dr. Sameer', 'Khan', 'dr.sameer.k@carepulse.org', '+91 98209 90123', 'Resident Medical Officer (RMO)', 'Emergency', 'Male', '1991-07-19', '2023-01-05', 'Night (22:00 - 08:00)', 'Active', 'MBBS, MEM (Emergency Medicine)', 75000.00, 'AB-', '+91 98209 32109', 'Doctors Hostel, Room 102', 1, 'doc.sameer', password_hash('staff123', PASSWORD_DEFAULT)],
        ['STF-110', 'Neha', 'Gupta', 'neha.g@carepulse.org', '+91 98210 01234', 'Billing & TPA Executive', 'Administration', 'Female', '1996-10-15', '2023-09-01', 'General (09:00 - 18:00)', 'Active', 'BBA Healthcare Management', 30000.00, 'A+', '+91 98210 43210', 'Flat 501, Lakeview Towers', 1, 'admin.neha', password_hash('staff123', PASSWORD_DEFAULT)]
    ];

    $insStmt = $conn->prepare("INSERT INTO staff (hospital_id, staff_code, first_name, last_name, email, phone, role, department, gender, date_of_birth, joining_date, shift, status, qualification, salary, blood_group, emergency_contact, address, is_user, username, password) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($insStmt) {
        foreach ($seedStaff as $s) {
            $insStmt->bind_param("isssssssssssssdsssiss", 
                $firstHospId, $s[0], $s[1], $s[2], $s[3], $s[4], $s[5], $s[6], $s[7], $s[8], $s[9], $s[10], $s[11], $s[12], $s[13], $s[14], $s[15], $s[16], $s[17], $s[18], $s[19]
            );
            $insStmt->execute();
        }
    }

    // Seed today's attendance for these staff members
    $today = date('Y-m-d');
    $stfRes = $conn->query("SELECT id, role, shift FROM staff WHERE hospital_id = $firstHospId");
    if ($stfRes) {
        $attStmt = $conn->prepare("INSERT IGNORE INTO staff_attendance (hospital_id, staff_id, date, status, check_in_time, check_out_time, working_hours, notes, marked_by) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $count = 0;
        while ($stf = $stfRes->fetch_assoc()) {
            $count++;
            $stfId = (int)$stf['id'];
            $st = 'Present';
            $in = '08:00:00';
            $out = '16:00:00';
            $hrs = 8.0;
            $note = 'On time';

            if ($count === 4) { // Late example
                $st = 'Late';
                $in = '08:35:00';
                $out = '16:00:00';
                $hrs = 7.4;
                $note = 'Traffic congestion near central bridge';
            } else if ($count === 7) { // Half day example
                $st = 'Half Day';
                $in = '08:00:00';
                $out = '12:00:00';
                $hrs = 4.0;
                $note = 'Approved half-day personal appointment';
            } else if ($count === 8) { // Leave example
                $st = 'On Leave';
                $in = null;
                $out = null;
                $hrs = 0.0;
                $note = 'Casual Leave approved by HR';
            }

            if ($attStmt) {
                $attStmt->bind_param("iissssiss", $firstHospId, $stfId, $today, $st, $in, $out, $hrs, $note, $mb);
                $mb = 'Admin System';
                $attStmt->execute();
            }
        }
    }
}

function jsonResponse($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
