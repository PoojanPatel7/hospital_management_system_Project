<?php
require_once __DIR__ . '/../db.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? ($_POST['action'] ?? '');
$hospital_id = $_SESSION['hospital_id'] ?? 1; // Default fallback to 1 if not set

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'get_all') {
        $stmt = $conn->prepare("SELECT id, charge_type, charge_name, amount, is_active FROM consultation_charges WHERE hospital_id = ? ORDER BY id ASC");
        $stmt->bind_param("i", $hospital_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $charges = [];
        while ($row = $result->fetch_assoc()) {
            $charges[] = $row;
        }
        jsonResponse(['status' => 'success', 'charges' => $charges]);
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update') {
        $id = (int)($_POST['id'] ?? 0);
        $amount = (float)($_POST['amount'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE consultation_charges SET amount = ? WHERE id = ? AND hospital_id = ?");
        $stmt->bind_param("dii", $amount, $id, $hospital_id);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => 'Charge updated successfully']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to update charge']);
        }
    } elseif ($action === 'add') {
        $type = $_POST['charge_type'] ?? '';
        $name = $_POST['charge_name'] ?? '';
        $amount = (float)($_POST['amount'] ?? 0);
        
        if (empty($type) || empty($name)) {
            jsonResponse(['status' => 'error', 'message' => 'Charge type and name are required']);
        }
        
        $stmt = $conn->prepare("INSERT INTO consultation_charges (hospital_id, charge_type, charge_name, amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("issd", $hospital_id, $type, $name, $amount);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => 'Charge added successfully']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to add charge']);
        }
    } elseif ($action === 'toggle') {
        $id = (int)($_POST['id'] ?? 0);
        $is_active = (int)($_POST['is_active'] ?? 0);
        
        $stmt = $conn->prepare("UPDATE consultation_charges SET is_active = ? WHERE id = ? AND hospital_id = ?");
        $stmt->bind_param("iii", $is_active, $id, $hospital_id);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => 'Charge status updated']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to update status']);
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        
        $stmt = $conn->prepare("DELETE FROM consultation_charges WHERE id = ? AND hospital_id = ?");
        $stmt->bind_param("ii", $id, $hospital_id);
        if ($stmt->execute()) {
            jsonResponse(['status' => 'success', 'message' => 'Charge deleted successfully']);
        } else {
            jsonResponse(['status' => 'error', 'message' => 'Failed to delete charge']);
        }
    }
}

jsonResponse(['status' => 'error', 'message' => 'Invalid action']);
