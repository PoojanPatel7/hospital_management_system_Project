<?php
// End-to-end simulation of user queries in chatbot.php
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

$testQueries = [
    "i need blood A+",
    "now all male with A+"
];

foreach ($testQueries as $q) {
    echo "\n=======================================================\n";
    echo "USER QUERY: $q\n";
    
    // Check Phase 0 Action Engine
    $actionPlan = analyzeActionIntent($conn, 1, $q, []);
    echo "PHASE 0 (Action Engine): " . ($actionPlan ? json_encode($actionPlan) : 'NULL (Pass to Phase 1)') . "\n";
    
    // Check Phase 1 Intent & SQL Generation
    $schemaContext = getRelevantSchema($conn, $q);
    $systemPrompt = getQueryGenerationPrompt('BHOOMA Medicare Hospital', 1, 'Admin', 'patients.php', date('Y-m-d H:i:s'), $schemaContext, '');
    $res = ollamaChat('hms-ai:latest', [
        ['role' => 'user', 'content' => $q]
    ], $systemPrompt, false, ['temperature' => 0.1]);
    
    $intentContent = $res['message']['content'] ?? '';
    echo "PHASE 1 (LLM Output):\n$intentContent\n";
    
    // Test SQL Extraction
    $rawSql = null;
    if (preg_match('/```sql\s*(.*?)\s*```/is', $intentContent, $matches)) {
        $rawSql = trim($matches[1]);
        echo "EXTRACTED SQL (```sql): $rawSql\n";
    } elseif (preg_match('/```json\s*(.*?)\s*```/is', $intentContent, $matches)) {
        $jsonStr = trim($matches[1]);
        $jsonClean = preg_replace('/;\s*(\r?\n\s*[\}\]])/m', '$1', $jsonStr);
        $jsonClean = preg_replace('/,\s*(\r?\n\s*[\}\]])/m', '$1', $jsonClean);
        $json = json_decode($jsonClean, true);
        
        if (!$json && preg_match('/"sql"\s*:\s*"([^"]+)"/is', $jsonStr, $sqlM)) {
            $extractedSql = trim($sqlM[1]);
            $extractedType = 'UPDATE';
            if (preg_match('/"action_type"\s*:\s*"([^"]+)"/is', $jsonStr, $typeM)) {
                $extractedType = strtoupper($typeM[1]);
            } elseif (stripos(ltrim($extractedSql), 'SELECT') === 0) {
                $extractedType = 'SELECT';
            }
            $json = ['action_type' => $extractedType, 'sql' => $extractedSql];
        }
        
        if ($json) {
            $aType = strtoupper($json['action_type'] ?? 'UPDATE');
            $aSql = trim($json['sql'] ?? '');
            if ($aType === 'SELECT' || stripos(ltrim($aSql), 'SELECT') === 0) {
                $rawSql = $aSql;
                echo "EXTRACTED SQL (from JSON SELECT): $rawSql\n";
            } else {
                echo "DETECTED AS ACTION: $aType on " . ($json['table'] ?? 'table') . " (SQL: $aSql)\n";
            }
        }
    }
    
    if ($rawSql !== null) {
        $sanitize = sanitizeReadQuery($rawSql, 1, ALLOWED_TABLES);
        if ($sanitize['valid']) {
            $dbRes = $conn->query($sanitize['sql']);
            if ($dbRes && $dbRes instanceof mysqli_result) {
                $rows = [];
                while ($r = $dbRes->fetch_assoc()) $rows[] = $r;
                echo "QUERY EXECUTED SUCCESSFULLY! Found " . count($rows) . " record(s):\n";
                foreach ($rows as $r) {
                    echo "  - " . ($r['name'] ?? '') . " " . ($r['surname'] ?? '') . " | Gender: " . ($r['gender'] ?? '') . " | Blood: " . ($r['blood_group'] ?? '') . "\n";
                }
            } else {
                echo "QUERY ERROR: " . $conn->error . "\n";
            }
        } else {
            echo "SANITIZER ERROR: " . $sanitize['error'] . "\n";
        }
    }
}
