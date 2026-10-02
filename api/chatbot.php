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
        // PHASE 1: SILENT SERVER-SIDE INTENT & SQL EXTRACTION
        // ========================================================
        $phase1Prompt = function_exists('getQueryGenerationPrompt')
            ? getQueryGenerationPrompt($hospitalName, $hospitalId, $staffRole, $pageContext, $dateTime, $schema, $todayStats)
            : "Generate SQL query for $message";

        $phase1Messages = [
            ['role' => 'system', 'content' => $phase1Prompt],
            ['role' => 'user', 'content' => $message]
        ];

        $dbResults = null;
        $pendingAction = null;
        $sqlExecuted = null;

        $intentResp = function_exists('ollamaChat') ? ollamaChat($model, $phase1Messages, '', false, ['temperature' => 0.1, 'num_predict' => 200]) : null;
        $intentContent = $intentResp['message']['content'] ?? '';

        // Check if a read SQL query was generated
        if (preg_match('/```sql\s*(.*?)\s*```/is', $intentContent, $matches)) {
            $rawSql = trim($matches[1]);
            $allowedTables = function_exists('getAllowedTables') ? getAllowedTables($staffRole) : [];
            $sanitizeResult = function_exists('sanitizeReadQuery') ? sanitizeReadQuery($rawSql, $hospitalId, $allowedTables) : ['valid' => true, 'sql' => $rawSql];
            
            if ($sanitizeResult['valid']) {
                $sqlToExecute = $sanitizeResult['sql'];
                try {
                    $res = $conn->query($sqlToExecute);
                    if ($res) {
                        $dbResults = [];
                        while ($r = $res->fetch_assoc()) {
                            unset($r['password']);
                            $dbResults[] = $r;
                        }
                        $sqlExecuted = $sqlToExecute;
                    }
                } catch (Exception $e) {
                    $dbResults = ['note' => 'No direct matching record found.'];
                }
            }
        }

        // Check if a write action plan was generated
        if (preg_match('/```json\s*(.*?)\s*```/is', $intentContent, $matches)) {
            $jsonStr = trim($matches[1]);
            $json = json_decode($jsonStr, true);
            if ($json && isset($json['action_type'])) {
                $aType = $json['action_type'] ?? 'UPDATE';
                $aTable = $json['table'] ?? 'unknown';
                $aDesc = $json['description'] ?? 'AI-generated action';
                $aSql = $json['sql'] ?? '';
                $aParams = isset($json['params']) ? json_encode($json['params']) : null;
                $lastMsgId = $conn->insert_id ?: 0;
                
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

        // ========================================================
        // PHASE 2: STREAM HUMAN-GRADE, STYLED RESPONSE (ZERO SQL)
        // ========================================================
        $synthesisSysPrompt = function_exists('getSynthesisPrompt')
            ? getSynthesisPrompt($hospitalName, $hospitalId, $staffRole, $pageContext, $dateTime, $todayStats)
            : "You are BHOOMA AI. Never show SQL. Provide friendly, properly styled hospital responses with emojis and bullet points.";

        $synthesisUserContent = "User Message: \"$message\"\n\n";
        if ($dbResults !== null) {
            $synthesisUserContent .= "FACTUAL HOSPITAL DATABASE DATA RETRIEVED:\n" . json_encode($dbResults, JSON_UNESCAPED_UNICODE) . "\n\n";
            $synthesisUserContent .= "Provide a clear, warm, and professional answer based on this data. Use bold headings, bullet points, and appropriate emojis. NEVER output SQL code or mention table names.\n";
        } elseif ($pendingAction !== null) {
            $synthesisUserContent .= "An action has been prepared: " . $pendingAction['description'] . "\n";
            $synthesisUserContent .= "Warmly summarize what will happen and advise the user to confirm using the card below.\n";
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
            }, ['temperature' => 0.35]);
        } else {
            $mock = "I am processing your hospital request with real-time data.";
            $fullResponse .= $mock;
            echo "data: " . json_encode(['type' => 'chunk', 'content' => $mock]) . "\n\n";
            flush();
        }

        // Send structured visual data card if results contain rows
        if (is_array($dbResults) && !empty($dbResults) && !isset($dbResults['note'])) {
            echo "data: " . json_encode([
                'type' => 'data', 
                'results' => $dbResults, 
                'summary' => count($dbResults) . ' record(s) found.'
            ]) . "\n\n";
            flush();
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
                $conn->query($sql);
                $affected = $conn->affected_rows;
                
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
