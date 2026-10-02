<?php
// Test script for chatbot inference
$_SESSION['hospital_id'] = 1;
$_SESSION['staff_role'] = 'Admin';
$_POST['action'] = 'test_prompt';
$_POST['prompt'] = 'show me all male patients with blood group A+';
require 'D:/xampp/htdocs/Hospital Management System/api/ai_train_api.php';
