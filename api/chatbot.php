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

$hospitalId = $_SESSION['hospital_id'];
$staffId = $_SESSION['staff_id'] ?? null;
$staffRole = $_SESSION['staff_role'] ?? 'Admin';
$userId = $_SESSION['username'] ?? $_SESSION['hospital_name'] ?? 'admin'; // VARCHAR user identifier

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
        $message = $input['message'] ?? '';
        $conversationId = $input['conversation_id'] ?? null;
        $pageContext = $input['page_context'] ?? '';

        if (empty($message)) {
            echo json_encode(['error' => 'Empty message']);
            exit;
        }

        if (function_exists('checkRateLimit')) {
            if (!checkRateLimit($conn, $hospitalId, $staffId)) {
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
        
        while (ob_get_level() > 0) ob_end_flush();

        // Build schema context - use relevant schema based on user message keywords
        $schema = '';
        if (function_exists('getRelevantSchema')) {
            $schema = getRelevantSchema($conn, strtolower($message));
        } elseif (function_exists('getFullSchema')) {
            $schema = getFullSchema($conn);
        }
        
        $context = function_exists('buildPageContext') ? buildPageContext($conn, $hospitalId, $pageContext) : ['stats' => '', 'focus_tables' => []];
        $todayStats = function_exists('getTodayStats') ? getTodayStats($conn, $hospitalId) : '';
        $hospitalName = $_SESSION['hospital_name'] ?? 'BHOOMA';
        $dateTime = date('Y-m-d H:i:s');
        
        $systemPrompt = '';
        if (function_exists('getSystemPrompt')) {
            $systemPrompt = getSystemPrompt($hospitalName, $hospitalId, $staffRole, $pageContext, $dateTime, $schema, $todayStats);
        } else {
            $systemPrompt = "You are BHOOMA AI, a helpful assistant for a hospital management system.\nSchema: $schema\nContext: {$context['stats']}";
        }

        $stmt = $conn->prepare("SELECT role, content FROM ai_chat_messages WHERE conversation_id = ? ORDER BY created_at ASC LIMIT 10");
        $stmt->bind_param("i", $conversationId);
        $stmt->execute();
        $historyRes = $stmt->get_result();
        $history = [];
        while ($row = $historyRes->fetch_assoc()) {
            $history[] = ['role' => $row['role'], 'content' => $row['content']];
        }

        $messages = [];
        $messages[] = ['role' => 'system', 'content' => $systemPrompt];
        foreach ($history as $h) {
            $messages[] = $h;
        }

        $model = defined('DEFAULT_MODEL') ? DEFAULT_MODEL : 'qwen2.5:3b';
        $fullResponse = "";
        
        if (function_exists('ollamaChatStream')) {
            ollamaChatStream($model, $messages, $systemPrompt, function($chunk) use (&$fullResponse) {
                $text = is_array($chunk) ? ($chunk['message']['content'] ?? '') : (string)$chunk;
                if ($text === '') return;
                $fullResponse .= $text;
                echo "data: " . json_encode(['type' => 'chunk', 'content' => $text]) . "\n\n";
                flush();
            });
        } else {
            // Mock response if ollama_client is missing
            $mock = "I am processing your request... System components not fully loaded.";
            $fullResponse .= $mock;
            echo "data: " . json_encode(['type' => 'chunk', 'content' => $mock]) . "\n\n";
            flush();
        }

        // Process SQL
        if (preg_match('/```sql\s*(.*?)\s*```/is', $fullResponse, $matches)) {
            $rawSql = trim($matches[1]);
            $sqlToExecute = null;
            
            if (function_exists('sanitizeReadQuery')) {
                $role = function_exists('getUserRole') ? getUserRole($_SESSION) : 'Admin';
                $allowedTables = function_exists('getAllowedTables') ? getAllowedTables($role) : [];
                $result = sanitizeReadQuery($rawSql, $hospitalId, $allowedTables);
                if ($result['valid']) {
                    $sqlToExecute = $result['sql'];
                } else {
                    echo "data: " . json_encode(['type' => 'error', 'message' => 'Query blocked: ' . ($result['error'] ?? 'Security policy')]) . "\n\n";
                    flush();
                }
            } elseif (stripos($rawSql, 'SELECT') === 0) {
                $sqlToExecute = $rawSql;
            }
            
            if ($sqlToExecute) {
                try {
                    $res = $conn->query($sqlToExecute);
                    if ($res) {
                        $results = [];
                        while ($r = $res->fetch_assoc()) {
                            $results[] = $r;
                        }
                        echo "data: " . json_encode(['type' => 'data', 'results' => $results, 'content' => $results, 'sql' => $sqlToExecute, 'summary' => count($results) . ' record(s) found.']) . "\n\n";
                        flush();
                    } else {
                        echo "data: " . json_encode(['type' => 'error', 'message' => 'Query execution failed.']) . "\n\n";
                        flush();
                    }
                } catch (Exception $e) {
                    echo "data: " . json_encode(['type' => 'error', 'message' => $e->getMessage()]) . "\n\n";
                    flush();
                }
            }
        }

        // Process Action JSON
        if (preg_match('/```json\s*(.*?)\s*```/is', $fullResponse, $matches)) {
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
                $actionId = $stmt->insert_id;

                echo "data: " . json_encode(['type' => 'action', 'action_id' => $actionId, 'details' => $json]) . "\n\n";
                flush();
            }
        }

        $stmt = $conn->prepare("INSERT INTO ai_chat_messages (conversation_id, role, content) VALUES (?, 'assistant', ?)");
        $stmt->bind_param("is", $conversationId, $fullResponse);
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
