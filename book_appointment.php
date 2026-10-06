<?php
// Smart Appointment Routing:
// If admin/staff is logged in, direct to the admin booking desk (book.php).
// If patient/guest is visiting, direct to the dedicated patient booking portal (patient_book.php).
require_once __DIR__ . '/auth.php';

$isAdmin = isset($_SESSION['hospital_id']) && !empty($_SESSION['hospital_id']);
if ($isAdmin) {
    header("Location: book.php");
} else {
    header("Location: patient_book.php");
}
exit;
