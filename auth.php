<?php
session_start();

// Exclude login and register from redirection loop
$currentPage = basename($_SERVER['PHP_SELF']);
$allowedPages = ['login.php', 'register_hospital.php'];

if (!in_array($currentPage, $allowedPages)) {
    if (!isset($_SESSION['hospital_id'])) {
        header("Location: login.php");
        exit;
    }
}
?>
