<?php
require_once 'db.php';

$sql = file_get_contents('schema.sql');
if ($conn->multi_query($sql)) {
    do {
        // flush multi_queries
        if ($res = $conn->store_result()) {
            $res->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Error creating tables: " . $conn->error;
    exit;
}

// Seed Data
$conn->query("INSERT IGNORE INTO system_state (id, line_running) VALUES (1, 1)");

$departments = [
    ['General Medicine', 'General Medicine', 'fa-user-doctor'],
    ['Cardiology', 'Cardiology', 'fa-heart-pulse'],
    ['Orthopedics', 'Orthopedics', 'fa-bone'],
    ['Pediatrics', 'Pediatrics', 'fa-baby'],
    ['Neurology', 'Neurology', 'fa-brain'],
    ['Pulmonology', 'Pulmonology', 'fa-lungs'],
    ['Dermatology', 'Dermatology', 'fa-hand-dots'],
    ['Emergency Trauma', 'Emergency Trauma', 'fa-truck-medical']
];

foreach ($departments as $d) {
    $stmt = $conn->prepare("INSERT IGNORE INTO departments (id, name, icon) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $d[0], $d[1], $d[2]);
    $stmt->execute();
}

$doctors = [
    ['doc-1', 'Dr. Sarah Jenkins', 'General Medicine', '12 Years Exp', 'MBBS, MD', ['09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', '11:30 AM', '02:00 PM', '03:00 PM']],
    ['doc-2', 'Dr. Arthur Vance', 'Cardiology', '16 Years Exp', 'MD, DM Cardiology', ['09:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '03:30 PM', '04:00 PM']],
    ['doc-3', 'Dr. Elena Rostova', 'Orthopedics', '9 Years Exp', 'MS Ortho', ['10:00 AM', '10:30 AM', '11:00 AM', '01:30 PM', '02:30 PM']],
    ['doc-4', 'Dr. Ronald Miller', 'Pediatrics', '14 Years Exp', 'MBBS, DCH', ['09:00 AM', '10:00 AM', '11:00 AM', '03:00 PM', '04:00 PM']],
    ['doc-5', 'Dr. Emily Zhao', 'Neurology', '11 Years Exp', 'MD Neurology', ['10:00 AM', '11:30 AM', '02:00 PM', '03:30 PM']],
    ['doc-6', 'Dr. Marcus Holloway', 'General Medicine', '8 Years Exp', 'MBBS, MD', ['08:30 AM', '09:00 AM', '10:00 AM', '11:00 AM']]
];

foreach ($doctors as $d) {
    $stmt = $conn->prepare("INSERT IGNORE INTO doctors (id, name, department_id, experience, degree) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $d[0], $d[1], $d[2], $d[3], $d[4]);
    $stmt->execute();
    
    // Add slots
    foreach ($d[5] as $slot) {
        // check if slot exists for doctor
        $check = $conn->query("SELECT id FROM doctor_slots WHERE doctor_id='{$d[0]}' AND time_slot='{$slot}'");
        if ($check->num_rows == 0) {
            $conn->query("INSERT INTO doctor_slots (doctor_id, time_slot) VALUES ('{$d[0]}', '{$slot}')");
        }
    }
}

$beds = [
    ['bed-1', 'OPD-101', 'OPD', 'North Wing, 2nd Floor'],
    ['bed-2', 'OPD-102', 'OPD', 'North Wing, 2nd Floor'],
    ['bed-3', 'OPD-103', 'OPD', 'North Wing, 2nd Floor'],
    ['bed-4', 'OPD-104', 'OPD', 'North Wing, 2nd Floor'],
    ['bed-5', 'OPD-105', 'OPD', 'North Wing, 2nd Floor'],
    ['bed-6', 'OPD-106', 'OPD', 'North Wing, 2nd Floor'],
    ['bed-7', 'ICU-01', 'ICU', 'Critical Care Floor, 3rd Floor'],
    ['bed-8', 'ICU-02', 'ICU', 'Critical Care Floor, 3rd Floor'],
    ['bed-9', 'ICU-03', 'ICU', 'Critical Care Floor, 3rd Floor'],
    ['bed-10', 'ICU-04', 'ICU', 'Critical Care Floor, 3rd Floor']
];

foreach ($beds as $b) {
    $stmt = $conn->prepare("INSERT IGNORE INTO beds (id, bed_number, type, wing) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $b[0], $b[1], $b[2], $b[3]);
    $stmt->execute();
}

echo "Database initialized with seed data.";
?>
