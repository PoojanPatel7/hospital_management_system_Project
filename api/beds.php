<?php
require_once '../db.php';

$action = $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_all') {
        $beds = [];
        $res = $conn->query("
            SELECT b.*, 
                   p.name, p.surname, p.father_name,
                   a.doctor_id, d.name as doctor_name
            FROM beds b
            LEFT JOIN patients p ON b.patient_id = p.id
            LEFT JOIN appointments a ON p.id = a.patient_id AND a.stage = 5 AND a.bed_number = b.bed_number
            LEFT JOIN doctors d ON a.doctor_id = d.id
        ");
        
        while ($row = $res->fetch_assoc()) {
            $beds[] = $row;
        }
        
        jsonResponse(['status' => 'success', 'beds' => $beds]);
    }
}
else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if ($action === 'add') {
        $type = $data['type'];
        $number = $data['number'];
        $wing = $data['wing'] ?: 'General Ward Floor';
        $id = 'bed-' . time();
        
        $stmt = $conn->prepare("INSERT INTO beds (id, bed_number, type, wing) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $id, $number, $type, $wing);
        
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => "Bed $number ($type) added."]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to add bed: ' . $conn->error]);
        }
    }
    else if ($action === 'delete') {
        $number = $data['number'];
        $stmt = $conn->prepare("DELETE FROM beds WHERE bed_number = ? AND status = 'Available'");
        $stmt->bind_param("s", $number);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                jsonResponse(['status' => 'success', 'message' => "Bed $number removed."]);
            } else {
                jsonResponse(['status' => 'error', 'message' => "Bed is occupied or not found."]);
            }
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to delete bed.']);
        }
    }
    else if ($action === 'discharge') {
        $number = $data['number'];
        
        // Find patient in this bed
        $res = $conn->query("SELECT patient_id FROM beds WHERE bed_number = '$number'");
        $patient_id = $res->fetch_assoc()['patient_id'];
        
        if ($patient_id) {
            $conn->query("UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$number'");
            
            // Update appointment
            $conn->query("UPDATE appointments SET status = 'Discharged from Bed', bed_number = NULL WHERE patient_id = '$patient_id' AND bed_number = '$number'");
            
            // Timeline event
            $time_now = date('h:i A');
            $event = "Discharged from inpatient bed $number. Ready for home rest.";
            $app_res = $conn->query("SELECT id FROM appointments WHERE patient_id = '$patient_id' ORDER BY id DESC LIMIT 1");
            $app_id = $app_res->fetch_assoc()['id'];
            
            $stmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $app_id, $patient_id, $time_now, $event);
            $stmt->execute();
            
            jsonResponse(['status' => 'success', 'message' => "Bed $number is now sanitized and available."]);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'No patient found in this bed.']);
        }
    }
}
?>
