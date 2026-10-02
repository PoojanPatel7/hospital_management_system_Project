<?php
// Test genuine mutation actions
$_SESSION['hospital_id'] = 1;
$_SESSION['hospital_name'] = 'BHOOMA Medicare Hospital';
$_SESSION['staff_role'] = 'Admin';
$_SESSION['user_id'] = 1;

require_once 'D:/xampp/htdocs/Hospital Management System/db.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/config.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/ollama_client.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/schema_provider.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/sql_sanitizer.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/action_engine.php';
require_once 'D:/xampp/htdocs/Hospital Management System/ai/prompt_templates.php';

$actionQueries = [
    "delete doctor Dr. Ambarish",
    "discharge bed ICU-1"
];

foreach ($actionQueries as $q) {
    echo "\n=======================================================\n";
    echo "ACTION QUERY: $q\n";
    
    // Check Phase 0 Action Engine
    $actionPlan = analyzeActionIntent($conn, 1, $q, []);
    if ($actionPlan && $actionPlan['type'] === 'action') {
        echo "PHASE 0 (Action Engine caught it!):\n";
        echo "  Action Type: " . $actionPlan['plan']['action_type'] . "\n";
        echo "  Description: " . $actionPlan['plan']['description'] . "\n";
        echo "  SQL: " . $actionPlan['plan']['sql'] . "\n";
    } else {
        echo "PHASE 0: Pass to Phase 1\n";
        $schemaContext = getRelevantSchema($conn, $q);
        $systemPrompt = getQueryGenerationPrompt('BHOOMA Medicare Hospital', 1, 'Admin', 'doctors.php', date('Y-m-d H:i:s'), $schemaContext, '');
        $res = ollamaChat('hms-ai:latest', [['role' => 'user', 'content' => $q]], $systemPrompt, false, ['temperature' => 0.1]);
        echo "PHASE 1:\n" . ($res['message']['content'] ?? '') . "\n";
    }
}
