<?php
$_SESSION['hospital_id'] = 1;
$_SESSION['staff_role'] = 'Admin';
require_once 'D:/xampp/htdocs/Hospital Management System/db.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/config.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/ollama_client.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/prompt_templates.php';

$prompts = [
    "i need blood A+",
    "now all male with A+",
    "all patients with blood group A+",
    "show all male with A+"
];

echo "\n========================================\n";
echo "MULTI-TURN TEST:\n";
echo "Turn 1: all patients with blood group A+\n";
echo "Turn 2: now all male with A+\n";

$schema = "patients (id, name, surname, phone, gender, blood_group, age, hospital_id)\ndoctors (id, name, department_id, phone, hospital_id)\nbeds (id, bed_number, type, status, hospital_id)\nappointments (id, patient_id, doctor_id, date, status, hospital_id)";
$systemPrompt = getQueryGenerationPrompt('BHOOMA Medicare Hospital', 1, 'Admin', 'patients.php', date('Y-m-d H:i:s'), $schema, '');

$messages = [
    ['role' => 'user', 'content' => 'all patients with blood group A+'],
    ['role' => 'assistant', 'content' => "```sql SELECT * FROM patients WHERE blood_group = 'A+' AND hospital_id = 1; ```\nHere are the patients with blood group A+."],
    ['role' => 'user', 'content' => 'now all male with A+']
];

$res = ollamaChat('hms-ai:latest', $messages, $systemPrompt, false, ['temperature' => 0.1]);
echo "RESPONSE WITH HISTORY:\n" . ($res['message']['content'] ?? 'NO CONTENT') . "\n";
