<?php
// ai/config.php
define('OLLAMA_URL', 'http://localhost:11434');
define('DEFAULT_MODEL', 'qwen3:4b');
define('MAX_TOKENS', 2048);
define('TEMPERATURE', 0.3);
define('CONTEXT_WINDOW', 8192);
define('RATE_LIMIT', 30); // 30 requests/min
define('ACTION_EXPIRY_MINUTES', 5);

function loadAiConfig($conn, $hospitalId) {
    $config = [
        'ollama_url' => OLLAMA_URL,
        'default_model' => DEFAULT_MODEL,
        'max_tokens' => MAX_TOKENS,
        'temperature' => TEMPERATURE,
        'context_window' => CONTEXT_WINDOW,
        'rate_limit' => RATE_LIMIT,
        'action_expiry_minutes' => ACTION_EXPIRY_MINUTES
    ];

    try {
        $stmt = $conn->prepare("SELECT model_name, ollama_url, max_tokens, temperature, context_window, rate_limit_per_minute, enabled FROM ai_config WHERE hospital_id = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("i", $hospitalId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                if (!empty($row['model_name'])) $config['default_model'] = $row['model_name'];
                if (!empty($row['ollama_url'])) $config['ollama_url'] = $row['ollama_url'];
                if ($row['max_tokens'] > 0) $config['max_tokens'] = (int)$row['max_tokens'];
                if ($row['temperature'] > 0) $config['temperature'] = (float)$row['temperature'];
                if ($row['context_window'] > 0) $config['context_window'] = (int)$row['context_window'];
                if ($row['rate_limit_per_minute'] > 0) $config['rate_limit'] = (int)$row['rate_limit_per_minute'];
                $config['enabled'] = (bool)$row['enabled'];
            }
            $stmt->close();
        }
    } catch (Exception $e) {
        // Fallback to defaults on error
    }

    return $config;
}
