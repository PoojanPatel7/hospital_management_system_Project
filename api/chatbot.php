<?php
// c:\xampp\htdocs\Hospital Management System\api\chatbot.php
session_start();
require_once __DIR__ . '/../db.php';

// Include AI components silently
@require_once __DIR__ . '/../ai/config.php';
@require_once __DIR__ . '/../ai/ollama_client.php';
@require_once __DIR__ . '/../ai/schema_provider.php';
@require_once __DIR__ . '/../ai/context_builder.php';
@require_once __DIR__ . '/../ai/sql_sanitizer.php';
@require_once __DIR__ . '/../ai/security_guard.php';
@require_once __DIR__ . '/../ai/prompt_templates.php';
@require_once __DIR__ . '/../ai/system_knowledge.php';
@require_once __DIR__ . '/../ai/action_engine.php';

if (!isset($_SESSION['hospital_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$hospitalId = (int)$_SESSION['hospital_id'];
$staffId = $_SESSION['staff_id'] ?? null;
$staffRole = $_SESSION['staff_role'] ?? 'Admin';
$userId = $_SESSION['username'] ?? $_SESSION['hospital_name'] ?? 'admin';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $rawInput = file_get_contents('php://input');
    $jsonInput = !empty($rawInput) ? json_decode($rawInput, true) : null;
    if (is_array($jsonInput)) {
        $input = array_merge($_POST, $jsonInput);
    } else {
        $input = $_POST;
    }
    $action = $input['action'] ?? '';
} else {
    $action = $_GET['action'] ?? '';
    $input = $_GET;
}

switch ($action) {
    case 'chat':
        set_time_limit(120);
        $message = trim($input['message'] ?? '');
        $conversationId = isset($input['conversation_id']) ? (int)$input['conversation_id'] : null;
        $pageContext = $input['page_context'] ?? 'dashboard.php';

        if (empty($message)) {
            echo json_encode(['error' => 'Empty message']);
            exit;
        }

        if (function_exists('checkRateLimit')) {
            if (!checkRateLimit($conn, $hospitalId, $staffId, 30)) {
                echo json_encode(['error' => 'Rate limit exceeded']);
                exit;
            }
        }

        if (!$conversationId) {
            $title = substr($message, 0, 50);
            $stmt = $conn->prepare("INSERT INTO ai_conversations (hospital_id, user_id, user_role, title, page_context) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $hospitalId, $userId, $staffRole, $title, $pageContext);
            $stmt->execute();
            $conversationId = $stmt->insert_id;
        } else {
            $stmt = $conn->prepare("SELECT id FROM ai_conversations WHERE id = ? AND hospital_id = ?");
            $stmt->bind_param("ii", $conversationId, $hospitalId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                echo json_encode(['error' => 'Invalid conversation']);
                exit;
            }
        }

        $stmt = $conn->prepare("INSERT INTO ai_chat_messages (conversation_id, role, content) VALUES (?, 'user', ?)");
        $stmt->bind_param("is", $conversationId, $message);
        $stmt->execute();

        session_write_close();

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        
        while (ob_get_level() > 0) ob_end_flush();

        // Build context with full typo tolerance
        $schema = function_exists('getRelevantSchema') ? getRelevantSchema($conn, $message) : '';
        $todayStats = function_exists('getTodayStats') ? getTodayStats($conn, $hospitalId) : '';
        $hospitalName = $_SESSION['hospital_name'] ?? 'BHOOMA';
        $dateTime = date('Y-m-d H:i:s');
        $model = defined('DEFAULT_MODEL') ? DEFAULT_MODEL : 'qwen2.5:3b';

        // Retrieve last 6 messages of conversation history for conversational context
        $stmt = $conn->prepare("SELECT role, content FROM ai_chat_messages WHERE conversation_id = ? ORDER BY created_at ASC LIMIT 6");
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $historyRes = $stmt->get_result();
        $history = [];
        while ($row = $historyRes->fetch_assoc()) {
            $cleanContent = preg_replace('/```sql[\s\S]*?```/i', '', $row['content']);
            if (!empty(trim($cleanContent))) {
                $history[] = ['role' => $row['role'], 'content' => $cleanContent];
            }
        }

        // ========================================================
        // PRE-PROCESSING: ANALYZE ACTION INTENT & AMBIGUITY
        // ========================================================
        $pendingAction = null;
        $dbResults = null;
        $sqlExecuted = null;

        if (function_exists('analyzeActionIntent')) {
            $actionAnalysis = analyzeActionIntent($conn, $hospitalId, $message, $history);
            if ($actionAnalysis !== null) {
                if ($actionAnalysis['type'] === 'form') {
                    $formType = $actionAnalysis['form_type'];
                    $formMsg = $actionAnalysis['message'] ?? 'Please fill out the form below.';
                    $formTitle = $actionAnalysis['title'] ?? 'Hospital Operation Form';
                    $prefill = $actionAnalysis['prefill'] ?? [];
                    $highlighted = "> 💡 **Direct Answer:** " . $formMsg . "\n\n";
                    echo "data: " . json_encode(['type' => 'chunk', 'content' => $highlighted]) . "\n\n";
                    flush();

                    echo "data: " . json_encode([
                        'type' => 'form',
                        'form_type' => $formType,
                        'title' => $formTitle,
                        'prefill' => $prefill
                    ]) . "\n\n";
                    flush();

                    $stmt = $conn->prepare("INSERT INTO ai_chat_messages (conversation_id, role, content) VALUES (?, 'assistant', ?)");
                    $stmt->bind_param("is", $conversationId, $highlighted);
                    $stmt->execute();

                    echo "data: " . json_encode(['type' => 'done', 'conversation_id' => $conversationId]) . "\n\n";
                    flush();
                    break;
                } elseif ($actionAnalysis['type'] === 'clarification') {
                    // Send clarification question back immediately
                    $clarifyText = $actionAnalysis['question'];
                    echo "data: " . json_encode(['type' => 'chunk', 'content' => $clarifyText]) . "\n\n";
                    flush();

                    if (!empty($actionAnalysis['suggestions'])) {
                        echo "data: " . json_encode(['type' => 'suggestions', 'suggestions' => $actionAnalysis['suggestions']]) . "\n\n";
                        flush();
                    }

                    $stmt = $conn->prepare("INSERT INTO ai_chat_messages (conversation_id, role, content) VALUES (?, 'assistant', ?)");
                    $stmt->bind_param("is", $conversationId, $clarifyText);
                    $stmt->execute();

                    echo "data: " . json_encode(['type' => 'done', 'conversation_id' => $conversationId]) . "\n\n";
                    flush();
                    break;
                } elseif ($actionAnalysis['type'] === 'action') {
                    // Verified action plan ready
                    $actPlan = $actionAnalysis['plan'];
                    $aType = $actPlan['action_type'] ?? 'UPDATE';
                    $aTable = $actPlan['table'] ?? 'unknown';
                    $aDesc = $actPlan['description'] ?? 'Hospital operation';
                    $aSql = $actPlan['sql'] ?? '';
                    $aParams = isset($actPlan['params']) ? json_encode($actPlan['params']) : null;
                    $lastMsgId = 0;

                    $stmt = $conn->prepare("INSERT INTO ai_pending_actions (conversation_id, message_id, hospital_id, action_type, target_table, description, sql_query, sql_params, status, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
                    $stmt->bind_param("iiisssss", $conversationId, $lastMsgId, $hospitalId, $aType, $aTable, $aDesc, $aSql, $aParams);
                    $stmt->execute();
                    $pendingAction = [
                        'action_id' => $stmt->insert_id,
                        'details' => $actPlan,
                        'description' => $aDesc
                    ];
                }
            }
        }

        // ========================================================
        // PHASE 1: SILENT SERVER-SIDE INTENT & SQL EXTRACTION
        // ========================================================
        if ($pendingAction === null) {
            $phase1Prompt = function_exists('getQueryGenerationPrompt')
                ? getQueryGenerationPrompt($hospitalName, $hospitalId, $staffRole, $pageContext, $dateTime, $schema, $todayStats)
                : "Generate SQL query for $message";

            $phase1Messages = [
                ['role' => 'system', 'content' => $phase1Prompt]
            ];
            foreach ($history as $h) {
                $phase1Messages[] = $h;
            }
            $phase1Messages[] = ['role' => 'user', 'content' => $message];

            $intentResp = function_exists('ollamaChat') ? ollamaChat($model, $phase1Messages, '', false, ['temperature' => 0.1, 'num_predict' => 220]) : null;
            $intentContent = $intentResp['message']['content'] ?? '';

            // Check if model asked for clarification
            if (preg_match('/^CLARIFY:\s*(.*)/is', trim($intentContent), $mClarify)) {
                $clarifyText = trim($mClarify[1]);
                echo "data: " . json_encode(['type' => 'chunk', 'content' => $clarifyText]) . "\n\n";
                flush();

                $stmt = $conn->prepare("INSERT INTO ai_chat_messages (conversation_id, role, content) VALUES (?, 'assistant', ?)");
                $stmt->bind_param("is", $conversationId, $clarifyText);
                $stmt->execute();

                echo "data: " . json_encode(['type' => 'done', 'conversation_id' => $conversationId]) . "\n\n";
                flush();
                break;
            }

            // Check if a read SQL query was generated (in ```sql blocks or bare)
            $rawSql = null;
            if (preg_match('/```sql\s*(.*?)\s*```/is', $intentContent, $matches)) {
                $rawSql = trim($matches[1]);
            } elseif (preg_match('/^\s*(SELECT\s+.+)/is', trim($intentContent), $matches)) {
                // Fallback: bare SELECT without code fence
                $rawSql = trim($matches[1]);
                $rawSql = rtrim($rawSql, ";");
            }
            
            if ($rawSql !== null) {
                $allowedTables = function_exists('getAllowedTables') ? getAllowedTables($staffRole) : [];
                $sanitizeResult = function_exists('sanitizeReadQuery') ? sanitizeReadQuery($rawSql, $hospitalId, $allowedTables) : ['valid' => true, 'sql' => $rawSql];
                
                if ($sanitizeResult['valid']) {
                    $sqlToExecute = $sanitizeResult['sql'];
                    try {
                        $res = $conn->query($sqlToExecute);
                        if ($res && $res instanceof mysqli_result) {
                            $dbResults = [];
                            while ($r = $res->fetch_assoc()) {
                                unset($r['password']);
                                $dbResults[] = $r;
                            }
                            $sqlExecuted = $sqlToExecute;
                        } elseif ($res === false) {
                            // MySQL query error — log it and set informative result
                            $mysqlError = $conn->error ?? 'Unknown query error';
                            error_log("HMS AI SQL Error: $mysqlError | SQL: $sqlToExecute");
                            $dbResults = null;
                        }
                    } catch (Exception $e) {
                        error_log("HMS AI SQL Exception: " . $e->getMessage());
                        $dbResults = null;
                    }
                }
            }

            // Check if a write action plan was generated by model
            if (preg_match('/```json\s*(.*?)\s*```/is', $intentContent, $matches)) {
                $jsonStr = trim($matches[1]);
                // Clean common LLM JSON syntax quirks (trailing semicolons or trailing commas)
                $jsonClean = preg_replace('/;\s*(\r?\n\s*[\}\]])/m', '$1', $jsonStr);
                $jsonClean = preg_replace('/,\s*(\r?\n\s*[\}\]])/m', '$1', $jsonClean);
                $json = json_decode($jsonClean, true);
                
                // Fallback regex extraction if json_decode still failed
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
                    $aType = strtoupper($json['action_type'] ?? $json['type'] ?? $json['operation'] ?? 'UPDATE');
                    $aTable = $json['table'] ?? ($json['action']['table'] ?? 'unknown');
                    $aDesc = $json['description'] ?? 'AI-generated hospital operation';
                    $aSql = trim($json['sql'] ?? '');

                    // CRITICAL FIX: If model wrapped a SELECT query inside JSON, execute it as a READ query!
                    if ($aType === 'SELECT' || stripos(ltrim($aSql), 'SELECT') === 0) {
                        $allowedTables = function_exists('getAllowedTables') ? getAllowedTables($staffRole) : [];
                        $sanitizeResult = function_exists('sanitizeReadQuery') ? sanitizeReadQuery($aSql, $hospitalId, $allowedTables) : ['valid' => true, 'sql' => $aSql];
                        if ($sanitizeResult['valid']) {
                            $sqlToExecute = $sanitizeResult['sql'];
                            try {
                                $res = $conn->query($sqlToExecute);
                                if ($res && $res instanceof mysqli_result) {
                                    $dbResults = [];
                                    while ($r = $res->fetch_assoc()) {
                                        unset($r['password']);
                                        $dbResults[] = $r;
                                    }
                                    $sqlExecuted = $sqlToExecute;
                                }
                            } catch (Exception $e) {
                                error_log("HMS AI JSON-SELECT execution error: " . $e->getMessage());
                                $dbResults = null;
                            }
                        }
                    } else {
                        // Genuine WRITE action (DELETE, UPDATE, INSERT)
                        // Resilient normalization if model didn't write full SQL
                        if (empty($aSql)) {
                            $rawAct = strtolower($json['action'] ?? $aType);
                            if (strpos($rawAct, 'discharge') !== false) {
                                $bedNum = $json['bed_number'] ?? ($json['action']['bed_number'] ?? '');
                                if (!empty($bedNum)) {
                                    $aType = 'UPDATE';
                                    $aTable = 'beds';
                                    $aDesc = "Discharge patient from Bed $bedNum and mark bed Available";
                                    $aSql = "UPDATE beds SET status = 'Available', patient_id = NULL WHERE bed_number = '$bedNum' AND hospital_id = $hospitalId; UPDATE appointments SET status = 'Discharged from Bed', bed_number = NULL WHERE bed_number = '$bedNum' AND hospital_id = $hospitalId;";
                                }
                            } elseif (strpos($rawAct, 'delete') !== false && ($aTable === 'doctors' || isset($json['doctor_id']) || isset($json['name']))) {
                                $docId = (int)($json['doctor_id'] ?? ($json['id'] ?? 0));
                                $docName = $json['name'] ?? 'Doctor';
                                if ($docId > 0) {
                                    $aType = 'DELETE';
                                    $aTable = 'doctors';
                                    $aDesc = "Permanently delete $docName (ID: $docId) from hospital database";
                                    $aSql = "DELETE FROM doctors WHERE id = $docId AND hospital_id = $hospitalId;";
                                }
                            } elseif (strpos($rawAct, 'cancel') !== false && ($aTable === 'appointments' || isset($json['appointment_id']))) {
                                $appId = (int)($json['appointment_id'] ?? ($json['id'] ?? 0));
                                if ($appId > 0) {
                                    $aType = 'UPDATE';
                                    $aTable = 'appointments';
                                    $aDesc = "Cancel Appointment #APP-$appId";
                                    $aSql = "UPDATE appointments SET status = 'Cancelled' WHERE id = $appId AND hospital_id = $hospitalId;";
                                }
                            }
                        }

                        // SAFETY GUARD: Never allow unconstrained batch updates without an entity ID
                        // (e.g. model mistakenly updating all patients' gender or blood group)
                        if ($aType === 'UPDATE' && !empty($aSql)) {
                            $hasTargetId = preg_match('/\b(id|bed_number|staff_id|appointment_id)\s*=\s*/i', $aSql);
                            if (!$hasTargetId && ($aTable === 'patients' || $aTable === 'doctors')) {
                                error_log("HMS AI Safety Guard: Blocked dangerous unconstrained $aTable UPDATE: $aSql");
                                $aSql = ''; // Block execution
                            }
                        }

                        if (!empty($aSql)) {
                            $aParams = isset($json['params']) ? json_encode($json['params']) : null;
                            $lastMsgId = 0;
                            
                            $stmt = $conn->prepare("INSERT INTO ai_pending_actions (conversation_id, message_id, hospital_id, action_type, target_table, description, sql_query, sql_params, status, expires_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', DATE_ADD(NOW(), INTERVAL 5 MINUTE))");
                            $stmt->bind_param("iiisssss", $conversationId, $lastMsgId, $hospitalId, $aType, $aTable, $aDesc, $aSql, $aParams);
                            $stmt->execute();
                            $pendingAction = [
                                'action_id' => $stmt->insert_id,
                                'details' => $json,
                                'description' => $aDesc
                            ];
                        }
                    }
                }
            }
        }

        // ========================================================
        // PHASE 2: STREAM HUMAN-GRADE, STYLED RESPONSE (ZERO SQL)
        // ========================================================
        $synthesisSysPrompt = function_exists('getSynthesisPrompt')
            ? getSynthesisPrompt($hospitalName, $hospitalId, $staffRole, $pageContext, $dateTime, $todayStats)
            : "You are BHOOMA AI. Never show SQL. Provide friendly, properly styled hospital responses with emojis and bullet points.";

        $synthesisUserContent = "User Message: \"$message\"\n\n";
        if ($dbResults !== null) {
            $resultCount = is_array($dbResults) ? count($dbResults) : 0;
            $synthesisUserContent .= "MODE: DATA DISPLAY (NOT an action — DO NOT mention confirm/action cards)\n";
            $synthesisUserContent .= "FACTUAL HOSPITAL DATABASE DATA RETRIEVED ($resultCount record(s)):\n" . json_encode($dbResults, JSON_UNESCAPED_UNICODE) . "\n\n";
            $synthesisUserContent .= "INSTRUCTION: Present this data clearly and warmly. Summarize the key fields in bullet points with bold labels. The data table is shown automatically below your text. DO NOT say 'click Confirm', 'action card', or anything about buttons. Simply narrate the data.\n";
        } elseif ($pendingAction !== null) {
            $synthesisUserContent .= "MODE: ACTION PENDING CONFIRMATION\n";
            $synthesisUserContent .= "AN ACTION HAS BEEN PREPARED AND IS PENDING USER CONFIRMATION:\n";
            $synthesisUserContent .= "Description: " . $pendingAction['description'] . "\n\n";
            $synthesisUserContent .= "CRITICAL INSTRUCTION: You MUST NOT say 'I have marked them' or 'Done'. You MUST warmly explain the action that was prepared and instruct the user to click the Confirm button on the card below to execute it in the hospital database.\n";
        } else {
            $synthesisUserContent .= "Answer the user in a helpful, friendly, and professional healthcare manner.\n";
        }

        $synthesisMessages = [
            ['role' => 'system', 'content' => $synthesisSysPrompt]
        ];
        foreach ($history as $h) {
            $synthesisMessages[] = $h;
        }
        $synthesisMessages[] = ['role' => 'user', 'content' => $synthesisUserContent];

        $fullResponse = "";

        if (function_exists('ollamaChatStream')) {
            ollamaChatStream($model, $synthesisMessages, $synthesisSysPrompt, function($chunk) use (&$fullResponse) {
                $text = is_array($chunk) ? ($chunk['message']['content'] ?? '') : (string)$chunk;
                if ($text === '') return;
                $fullResponse .= $text;
                echo "data: " . json_encode(['type' => 'chunk', 'content' => $text]) . "\n\n";
                flush();
            }, ['temperature' => 0.25, 'num_predict' => 220]);
        } else {
            $mock = "I am processing your hospital request with real-time data.";
            $fullResponse .= $mock;
            echo "data: " . json_encode(['type' => 'chunk', 'content' => $mock]) . "\n\n";
            flush();
        }

        // Send structured visual data card if results were retrieved
        if (is_array($dbResults)) {
            if (!empty($dbResults) && !isset($dbResults['note'])) {
                echo "data: " . json_encode([
                    'type' => 'data', 
                    'results' => $dbResults, 
                    'summary' => count($dbResults) . ' record(s) found.'
                ]) . "\n\n";
                flush();
            } elseif (empty($dbResults) && $sqlExecuted !== null) {
                // Valid query executed but returned 0 rows
                echo "data: " . json_encode([
                    'type' => 'data',
                    'results' => [],
                    'summary' => 'No matching records found.'
                ]) . "\n\n";
                flush();
            }
        }

        // Send action card if action was initiated
        if ($pendingAction !== null) {
            echo "data: " . json_encode([
                'type' => 'action', 
                'action_id' => $pendingAction['action_id'], 
                'content' => $pendingAction['description'], 
                'details' => $pendingAction['details']
            ]) . "\n\n";
            flush();
        }

        // Save assistant message to conversation history
        $stmt = $conn->prepare("INSERT INTO ai_chat_messages (conversation_id, role, content, sql_executed) VALUES (?, 'assistant', ?, ?)");
        $stmt->bind_param("iss", $conversationId, $fullResponse, $sqlExecuted);
        $stmt->execute();

        echo "data: " . json_encode(['type' => 'done', 'conversation_id' => $conversationId]) . "\n\n";
        flush();
        break;

    case 'confirm':
        $actionId = $input['action_id'] ?? 0;
        $stmt = $conn->prepare("SELECT sql_query, action_type, target_table, description FROM ai_pending_actions WHERE id = ? AND hospital_id = ? AND status = 'pending' AND expires_at > NOW()");
        $stmt->bind_param("ii", $actionId, $hospitalId);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Action expired, invalid, or already executed']);
            exit;
        }
        
        $row = $res->fetch_assoc();
        $sql = $row['sql_query'];
        
        if (!empty($sql)) {
            try {
                if (strpos($sql, ';') !== false) {
                    $conn->multi_query($sql);
                    $affected = 0;
                    do {
                        if ($result = $conn->store_result()) {
                            $result->free();
                        }
                        $affected += max(0, $conn->affected_rows);
                    } while ($conn->more_results() && $conn->next_result());
                } else {
                    $conn->query($sql);
                    $affected = $conn->affected_rows;
                }
                
                $stmt = $conn->prepare("UPDATE ai_pending_actions SET status = 'executed', executed_at = NOW() WHERE id = ?");
                $stmt->bind_param("i", $actionId);
                $stmt->execute();

                $userId = $staffId ?? ($_SESSION['hospital_name'] ?? 'admin');
                $stmt = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_id, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 1, ?)");
                $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $stmt->bind_param("issiissis", $hospitalId, $userId, $staffRole, $actionId, $row['action_type'], $row['target_table'], $sql, $affected, $ip);
                $stmt->execute();
                
                echo json_encode(['status' => 'success', 'message' => $row['description'] . ' — completed successfully', 'rows_affected' => $affected]);
            } catch (Exception $e) {
                $stmt = $conn->prepare("UPDATE ai_pending_actions SET status = 'failed', error_message = ? WHERE id = ?");
                $errMsg = $e->getMessage();
                $stmt->bind_param("si", $errMsg, $actionId);
                $stmt->execute();
                echo json_encode(['status' => 'error', 'message' => 'Execution failed: ' . $e->getMessage()]);
            }
        } else {
            echo json_encode(['status' => 'error', 'message' => 'No executable SQL found in action']);
        }
        break;

    case 'cancel':
        $actionId = $input['action_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE ai_pending_actions SET status = 'cancelled' WHERE id = ? AND hospital_id = ?");
        $stmt->bind_param("ii", $actionId, $hospitalId);
        $stmt->execute();
        echo json_encode(['status' => 'success', 'message' => 'Action cancelled']);
        break;

    case 'history':
        $conversationId = $input['conversation_id'] ?? 0;
        $stmt = $conn->prepare("SELECT id FROM ai_conversations WHERE id = ? AND hospital_id = ?");
        $stmt->bind_param("ii", $conversationId, $hospitalId);
        $stmt->execute();
        if ($stmt->get_result()->num_rows === 0) {
            echo json_encode(['error' => 'Invalid conversation']);
            exit;
        }

        $stmt = $conn->prepare("SELECT role, content, created_at FROM ai_chat_messages WHERE conversation_id = ? ORDER BY created_at ASC");
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $res = $stmt->get_result();
        $msgs = [];
        while ($r = $res->fetch_assoc()) {
            // Strip any raw SQL from displayed content
            $r['content'] = preg_replace('/```sql[\s\S]*?```/i', '', $r['content']);
            $msgs[] = $r;
        }
        echo json_encode($msgs);
        break;

    case 'conversations':
        $stmt = $conn->prepare("SELECT id, title, created_at FROM ai_conversations WHERE hospital_id = ? AND user_id = ? ORDER BY updated_at DESC");
        $stmt->bind_param("is", $hospitalId, $userId);
        $stmt->execute();
        $res = $stmt->get_result();
        $convos = [];
        while ($r = $res->fetch_assoc()) {
            $convos[] = $r;
        }
        echo json_encode($convos);
        break;
        
    case 'new_conversation':
        $pageCtx = $input['page_context'] ?? '';
        $title = 'New Conversation';
        $stmt = $conn->prepare("INSERT INTO ai_conversations (hospital_id, user_id, user_role, title, page_context) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $hospitalId, $userId, $staffRole, $title, $pageCtx);
        $stmt->execute();
        echo json_encode(['conversation_id' => $stmt->insert_id]);
        break;

    case 'get_form_options':
        $docStmt = $conn->prepare("
            SELECT d.id, d.name, d.degree, d.experience, 
                   COALESCE(GROUP_CONCAT(DISTINCT dep.name SEPARATOR ', '), 'General Specialist') as department
            FROM doctors d
            LEFT JOIN doctor_categories dc ON d.id = dc.doctor_id
            LEFT JOIN departments dep ON dc.department_id = dep.id
            WHERE d.hospital_id = ?
            GROUP BY d.id
            ORDER BY d.name ASC
        ");
        $docStmt->bind_param("i", $hospitalId);
        $docStmt->execute();
        $docRes = $docStmt->get_result();
        $doctors = [];
        while ($r = $docRes->fetch_assoc()) $doctors[] = $r;

        $bedStmt = $conn->prepare("SELECT id, bed_number, type, wing FROM beds WHERE hospital_id = ? AND status = 'Available' ORDER BY type, bed_number ASC");
        $bedStmt->bind_param("i", $hospitalId);
        $bedStmt->execute();
        $bedRes = $bedStmt->get_result();
        $beds = [];
        while ($r = $bedRes->fetch_assoc()) $beds[] = $r;

        $types = ['General Consultation', 'Specialist Review', 'Follow-up Consultation', 'Emergency Consultation'];
        $slots = ['09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM', '02:00 PM', '02:30 PM', '03:00 PM', '03:30 PM', '04:00 PM', '04:30 PM'];

        echo json_encode([
            'status' => 'success',
            'doctors' => $doctors,
            'beds' => $beds,
            'types' => $types,
            'slots' => $slots
        ]);
        break;

    case 'patient_quick_search':
        $q = trim($input['query'] ?? '');
        $patients = [];
        if (strlen($q) >= 1) {
            $like = "%$q%";
            $pStmt = $conn->prepare("SELECT id, name, surname, phone, age, gender FROM patients WHERE hospital_id = ? AND (id LIKE ? OR name LIKE ? OR surname LIKE ? OR phone LIKE ?) ORDER BY id DESC LIMIT 8");
            $pStmt->bind_param("issss", $hospitalId, $like, $like, $like, $like);
            $pStmt->execute();
            $pRes = $pStmt->get_result();
            while ($r = $pRes->fetch_assoc()) $patients[] = $r;
        }
        echo json_encode(['status' => 'success', 'patients' => $patients]);
        break;

    case 'book_appointment_form':
        $docId = trim($input['doctor_id'] ?? '');
        $patIdent = trim($input['patient_id'] ?? '');
        $appDate = trim($input['date'] ?? date('Y-m-d'));
        $appSlot = trim($input['slot'] ?? '09:00 AM');
        $appType = trim($input['type'] ?? 'General Consultation');
        $symptoms = trim($input['symptoms'] ?? 'Booked via BHOOMA AI Assistant');

        if (empty($docId)) {
            echo json_encode(['status' => 'error', 'message' => 'Please select a doctor.']);
            exit;
        }
        if (empty($patIdent)) {
            echo json_encode(['status' => 'error', 'message' => 'Please enter a Patient MRN or Name.']);
            exit;
        }
        if (empty($appDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $appDate)) {
            echo json_encode(['status' => 'error', 'message' => 'Please choose a valid appointment date.']);
            exit;
        }

        $patientId = null;
        $patientName = '';
        $chkP = $conn->prepare("SELECT id, name, surname FROM patients WHERE (id = ? OR phone = ? OR CONCAT(name, ' ', surname) LIKE ?) AND hospital_id = ? LIMIT 1");
        $likeName = "%$patIdent%";
        $chkP->bind_param("sssi", $patIdent, $patIdent, $likeName, $hospitalId);
        $chkP->execute();
        $pRow = $chkP->get_result()->fetch_assoc();
        if ($pRow) {
            $patientId = $pRow['id'];
            $patientName = trim($pRow['name'] . ' ' . ($pRow['surname'] ?? ''));
        } else {
            $cntRow = $conn->query("SELECT MAX(CAST(SUBSTRING(id, 5) AS UNSIGNED)) as max_id FROM patients WHERE id LIKE 'PAT-%'")->fetch_assoc();
            $nextNum = max(1101, (int)($cntRow['max_id'] ?? 1000) + 1);
            $newPatId = sprintf("PAT-%04d", $nextNum);
            $parts = explode(' ', $patIdent, 2);
            $fName = $parts[0];
            $lName = $parts[1] ?? 'Patient';
            $insP = $conn->prepare("INSERT INTO patients (id, name, surname, demographics, hospital_id) VALUES (?, ?, ?, 'Registered via AI', ?)");
            $insP->bind_param("sssi", $newPatId, $fName, $lName, $hospitalId);
            $insP->execute();
            $patientId = $newPatId;
            $patientName = "$fName $lName";
        }

        $docRow = $conn->query("SELECT name FROM doctors WHERE id = '$docId' AND hospital_id = $hospitalId")->fetch_assoc();
        $docName = $docRow['name'] ?? 'Doctor';

        $dupStmt = $conn->prepare("SELECT id FROM appointments WHERE doctor_id = ? AND date = ? AND slot = ? AND status NOT IN ('Cancelled', 'Discharged from Bed') AND hospital_id = ?");
        $dupStmt->bind_param("sssi", $docId, $appDate, $appSlot, $hospitalId);
        $dupStmt->execute();
        if ($dupStmt->get_result()->num_rows > 0) {
            echo json_encode(['status' => 'error', 'message' => "Dr. $docName is already booked for slot $appSlot on $appDate. Please select another slot."]);
            exit;
        }

        $insApp = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, allergies, status, stage, hospital_id) VALUES (?, ?, ?, ?, ?, ?, 'None recorded', 'Pre-Booked', 0, ?)");
        $insApp->bind_param("ssssssi", $patientId, $docId, $appType, $appDate, $appSlot, $symptoms, $hospitalId);
        if ($insApp->execute()) {
            $appId = $insApp->insert_id;
            
            $timeNow = date('h:i A');
            $evDesc = "Appointment scheduled for $appType with $docName on $appDate ($appSlot). Status: Pre-Booked.";
            $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
            $tStmt->bind_param("isss", $appId, $patientId, $timeNow, $evDesc);
            $tStmt->execute();

            $actSql = "INSERT INTO appointments (id, patient_id, doctor_id, date, slot, status) VALUES ($appId, '$patientId', '$docId', '$appDate', '$appSlot', 'Pre-Booked')";
            $logUser = $userId ?? 'Admin';
            $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'INSERT', 'appointments', ?, 1, 1, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
            $aLog->execute();

            echo json_encode([
                'status' => 'success',
                'appointment_id' => $appId,
                'patient_id' => $patientId,
                'patient_name' => $patientName,
                'doctor_name' => $docName,
                'date' => $appDate,
                'slot' => $appSlot,
                'type' => $appType,
                'message' => "Appointment #$appId confirmed successfully for $patientName with $docName."
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to book appointment: ' . $conn->error]);
        }
        break;

    case 'admit_patient_form':
        $patIdent = trim($input['patient_id'] ?? '');
        $bedNumber = trim($input['bed_number'] ?? '');
        $docId = trim($input['doctor_id'] ?? '');
        $reason = trim($input['reason'] ?? 'Emergency Clinical Admission');

        if (empty($patIdent)) {
            echo json_encode(['status' => 'error', 'message' => 'Please provide a valid Patient MRN or Name.']);
            exit;
        }
        if (empty($bedNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Please select an available Bed.']);
            exit;
        }

        $bedChk = $conn->prepare("SELECT id, bed_number, type, status FROM beds WHERE bed_number = ? AND hospital_id = ?");
        $bedChk->bind_param("si", $bedNumber, $hospitalId);
        $bedChk->execute();
        $bedRow = $bedChk->get_result()->fetch_assoc();
        if (!$bedRow) {
            echo json_encode(['status' => 'error', 'message' => "Bed $bedNumber not found in hospital inventory."]);
            exit;
        }
        if ($bedRow['status'] !== 'Available') {
            echo json_encode(['status' => 'error', 'message' => "Bed $bedNumber is currently Occupied. Please choose an available bed."]);
            exit;
        }

        $chkP = $conn->prepare("SELECT id, name, surname FROM patients WHERE (id = ? OR phone = ? OR CONCAT(name, ' ', surname) LIKE ?) AND hospital_id = ? LIMIT 1");
        $likeName = "%$patIdent%";
        $chkP->bind_param("sssi", $patIdent, $patIdent, $likeName, $hospitalId);
        $chkP->execute();
        $pRow = $chkP->get_result()->fetch_assoc();
        if (!$pRow) {
            echo json_encode(['status' => 'error', 'message' => "Patient not found. Please register or verify the patient ID first."]);
            exit;
        }
        $patientId = $pRow['id'];
        $patientName = trim($pRow['name'] . ' ' . ($pRow['surname'] ?? ''));

        $upBed = $conn->prepare("UPDATE beds SET status = 'Occupied', patient_id = ? WHERE bed_number = ? AND hospital_id = ?");
        $upBed->bind_param("ssi", $patientId, $bedNumber, $hospitalId);
        $upBed->execute();

        $appChk = $conn->prepare("SELECT id FROM appointments WHERE patient_id = ? AND hospital_id = ? AND date = CURDATE() ORDER BY id DESC LIMIT 1");
        $appChk->bind_param("si", $patientId, $hospitalId);
        $appChk->execute();
        $appRow = $appChk->get_result()->fetch_assoc();

        $appId = 0;
        if ($appRow) {
            $appId = (int)$appRow['id'];
            $upApp = $conn->prepare("UPDATE appointments SET status = 'Admitted to Bed', stage = 5, bed_number = ?, doctor_notes = CONCAT(COALESCE(doctor_notes, ''), '\nAdmitted: ', ?) WHERE id = ?");
            $upApp->bind_param("ssi", $bedNumber, $reason, $appId);
            $upApp->execute();
        } else {
            $today = date('Y-m-d');
            $timeSlot = date('h:i A');
            $docToUse = !empty($docId) ? $docId : ($conn->query("SELECT id FROM doctors WHERE hospital_id = $hospitalId LIMIT 1")->fetch_assoc()['id'] ?? 'doc-1');
            $insApp = $conn->prepare("INSERT INTO appointments (patient_id, doctor_id, type, date, slot, symptoms, status, stage, bed_number, doctor_notes, hospital_id) VALUES (?, ?, 'Inpatient Admission', ?, ?, ?, 'Admitted to Bed', 5, ?, ?, ?)");
            $insApp->bind_param("sssssssi", $patientId, $docToUse, $today, $timeSlot, $reason, $bedNumber, $reason, $hospitalId);
            $insApp->execute();
            $appId = $insApp->insert_id;
        }

        $timeNow = date('h:i A');
        $evDesc = "Patient admitted to {$bedRow['type']} Bed $bedNumber. Reason: $reason.";
        $tStmt = $conn->prepare("INSERT INTO timeline_events (appointment_id, patient_id, event_time, event_description) VALUES (?, ?, ?, ?)");
        $tStmt->bind_param("isss", $appId, $patientId, $timeNow, $evDesc);
        $tStmt->execute();

        $actSql = "UPDATE beds SET status='Occupied', patient_id='$patientId' WHERE bed_number='$bedNumber'";
        $logUser = $userId ?? 'Admin';
        $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'UPDATE', 'beds', ?, 1, 1, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
        $aLog->execute();

        echo json_encode([
            'status' => 'success',
            'bed_number' => $bedNumber,
            'bed_type' => $bedRow['type'],
            'patient_name' => $patientName,
            'patient_id' => $patientId,
            'message' => "Patient $patientName successfully admitted to Bed $bedNumber ({$bedRow['type']})."
        ]);
        break;

    case 'update_doctor_form':
        $docId = (int)($input['doctor_id'] ?? 0);
        $name = trim($input['name'] ?? '');
        $degree = trim($input['degree'] ?? '');
        $experience = trim($input['experience'] ?? '');
        $phone = trim($input['phone'] ?? '');

        if ($docId <= 0 || empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid doctor ID or name.']);
            exit;
        }

        $upStmt = $conn->prepare("UPDATE doctors SET name = ?, degree = ?, experience = ?, phone = ? WHERE id = ? AND hospital_id = ?");
        $upStmt->bind_param("ssssii", $name, $degree, $experience, $phone, $docId, $hospitalId);
        if ($upStmt->execute()) {
            $actSql = "UPDATE doctors SET name='$name', degree='$degree', experience='$experience', phone='$phone' WHERE id=$docId";
            $logUser = $userId ?? 'Admin';
            $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'UPDATE', 'doctors', ?, 1, 1, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
            $aLog->execute();

            echo json_encode(['status' => 'success', 'message' => "Dr. $name profile updated successfully."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update doctor: ' . $conn->error]);
        }
        break;

    case 'update_patient_form':
        $patId = trim($input['patient_id'] ?? '');
        $name = trim($input['name'] ?? '');
        $surname = trim($input['surname'] ?? '');
        $phone = trim($input['phone'] ?? '');
        $bloodGroup = trim($input['blood_group'] ?? '');
        $age = (int)($input['age'] ?? 0);
        $gender = trim($input['gender'] ?? 'Other');

        if (empty($patId) || empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid patient record or name.']);
            exit;
        }

        $upStmt = $conn->prepare("UPDATE patients SET name = ?, surname = ?, phone = ?, blood_group = ?, age = ?, gender = ? WHERE id = ? AND hospital_id = ?");
        $upStmt->bind_param("ssssissi", $name, $surname, $phone, $bloodGroup, $age, $gender, $patId, $hospitalId);
        if ($upStmt->execute()) {
            $actSql = "UPDATE patients SET name='$name', surname='$surname', phone='$phone', blood_group='$bloodGroup', age=$age, gender='$gender' WHERE id='$patId'";
            $logUser = $userId ?? 'Admin';
            $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'UPDATE', 'patients', ?, 1, 1, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
            $aLog->execute();

            echo json_encode(['status' => 'success', 'message' => "Patient $name $surname ($patId) updated successfully."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update patient: ' . $conn->error]);
        }
        break;

    case 'update_bed_form':
        $bedNumber = trim($input['bed_number'] ?? '');
        $type = trim($input['type'] ?? 'General Ward');
        $wing = trim($input['wing'] ?? 'Wing A');
        $status = trim($input['status'] ?? 'Available');

        if (empty($bedNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid bed number.']);
            exit;
        }

        $upStmt = $conn->prepare("UPDATE beds SET type = ?, wing = ?, status = ? WHERE bed_number = ? AND hospital_id = ?");
        $upStmt->bind_param("ssssi", $type, $wing, $status, $bedNumber, $hospitalId);
        if ($upStmt->execute()) {
            $actSql = "UPDATE beds SET type='$type', wing='$wing', status='$status' WHERE bed_number='$bedNumber'";
            $logUser = $userId ?? 'Admin';
            $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'UPDATE', 'beds', ?, 1, 1, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
            $aLog->execute();

            echo json_encode(['status' => 'success', 'message' => "Bed $bedNumber settings updated to $type ($status)."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update bed: ' . $conn->error]);
        }
        break;

    case 'add_patient_form':
        $name = trim($input['name'] ?? '');
        $surname = trim($input['surname'] ?? '');
        $gender = trim($input['gender'] ?? 'Male');
        $bloodGroup = trim($input['blood_group'] ?? 'A+');
        $age = (int)($input['age'] ?? 25);
        $phone = trim($input['phone'] ?? '');
        $emergName = trim($input['emergency_contact_name'] ?? '');
        $emergPhone = trim($input['emergency_contact_phone'] ?? '');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Patient name is required.']);
            exit;
        }

        $mrn = 'PAT-' . rand(3000, 9999);
        $ins = $conn->prepare("INSERT INTO patients (id, name, surname, gender, blood_group, age, phone, emergency_contact_name, emergency_contact_phone, hospital_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $ins->bind_param("sssssisssi", $mrn, $name, $surname, $gender, $bloodGroup, $age, $phone, $emergName, $emergPhone, $hospitalId);
        if ($ins->execute()) {
            $actSql = "INSERT INTO patients (id, name, surname, gender, blood_group, age, phone) VALUES ('$mrn', '$name', '$surname', '$gender', '$bloodGroup', $age, '$phone')";
            $logUser = $userId ?? 'Admin';
            $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'INSERT', 'patients', ?, 1, 1, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
            $aLog->execute();

            echo json_encode(['status' => 'success', 'patient_id' => $mrn, 'message' => "Patient $name $surname registered successfully with MRN $mrn (Blood: $bloodGroup, Gender: $gender)."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to register patient: ' . $conn->error]);
        }
        break;

    case 'add_doctor_form':
        $name = trim($input['name'] ?? '');
        $degree = trim($input['degree'] ?? 'MBBS, MD');
        $deptName = trim($input['department'] ?? 'General Medicine');
        $phone = trim($input['phone'] ?? '');
        $experience = trim($input['experience'] ?? '5 Years');

        if (empty($name)) {
            echo json_encode(['status' => 'error', 'message' => 'Doctor name is required.']);
            exit;
        }

        $deptId = 1;
        $dRes = $conn->query("SELECT id FROM departments WHERE name LIKE '%$deptName%' AND hospital_id = $hospitalId LIMIT 1");
        if ($dRes && $row = $dRes->fetch_assoc()) {
            $deptId = (int)$row['id'];
        }

        $ins = $conn->prepare("INSERT INTO doctors (name, degree, experience, department_id, phone, hospital_id) VALUES (?, ?, ?, ?, ?, ?)");
        $ins->bind_param("sssisi", $name, $degree, $experience, $deptId, $phone, $hospitalId);
        if ($ins->execute()) {
            $newDocId = $conn->insert_id;
            $actSql = "INSERT INTO doctors (name, degree, experience, phone) VALUES ('$name', '$degree', '$experience', '$phone')";
            $logUser = $userId ?? 'Admin';
            $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'INSERT', 'doctors', ?, 1, 1, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
            $aLog->execute();

            echo json_encode(['status' => 'success', 'doctor_id' => $newDocId, 'message' => "$name ($degree) added successfully to $deptName roster."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to add doctor: ' . $conn->error]);
        }
        break;

    case 'add_bed_form':
        $bedNumber = trim($input['bed_number'] ?? '');
        $type = trim($input['type'] ?? 'General Ward');
        $wing = trim($input['wing'] ?? 'North Wing');

        if (empty($bedNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Bed number is required.']);
            exit;
        }

        $ins = $conn->prepare("INSERT INTO beds (bed_number, type, wing, status, hospital_id) VALUES (?, ?, ?, 'Available', ?)");
        $ins->bind_param("sssi", $bedNumber, $type, $wing, $hospitalId);
        if ($ins->execute()) {
            $actSql = "INSERT INTO beds (bed_number, type, wing, status) VALUES ('$bedNumber', '$type', '$wing', 'Available')";
            $logUser = $userId ?? 'Admin';
            $aLog = $conn->prepare("INSERT INTO ai_action_log (hospital_id, user_id, user_role, action_type, target_table, sql_executed, rows_affected, success, ip_address) VALUES (?, ?, ?, 'INSERT', 'beds', ?, 1, 1, ?)");
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
            $aLog->bind_param("issss", $hospitalId, $logUser, $staffRole, $actSql, $ip);
            $aLog->execute();

            echo json_encode(['status' => 'success', 'message' => "Bed $bedNumber ($type, $wing) created as Available in inventory."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to create bed: ' . $conn->error]);
        }
        break;

    case 'status':
        $ollamaOnline = function_exists('checkOllamaStatus') ? checkOllamaStatus() : false;
        $models = function_exists('listOllamaModels') ? listOllamaModels() : [];
        $defModel = defined('DEFAULT_MODEL') ? DEFAULT_MODEL : 'qwen2.5:3b';
        echo json_encode(['ollama' => $ollamaOnline, 'model' => $defModel, 'models' => $models, 'status' => $ollamaOnline ? 'online' : 'offline']);
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        break;
}
?>
