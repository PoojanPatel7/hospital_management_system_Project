<?php
// ai/ollama_client.php

function ollamaChat($model, $messages, $systemPrompt, $stream = false, $options = []) {
    $url = OLLAMA_URL . '/api/chat';
    
    // Inject system prompt if not present
    $hasSystem = false;
    foreach ($messages as $msg) {
        if ($msg['role'] === 'system') {
            $hasSystem = true;
            break;
        }
    }
    
    if (!$hasSystem && !empty($systemPrompt)) {
        array_unshift($messages, ['role' => 'system', 'content' => $systemPrompt]);
    }
    
    $data = [
        'model' => $model,
        'messages' => $messages,
        'stream' => $stream,
        'options' => array_merge([
            'temperature' => TEMPERATURE,
            'num_ctx' => CONTEXT_WINDOW,
            'num_predict' => MAX_TOKENS
        ], $options)
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error];
    }
    
    return json_decode($response, true);
}

function ollamaChatStream($model, $messages, $systemPrompt, $callback, $options = []) {
    $url = OLLAMA_URL . '/api/chat';
    
    $hasSystem = false;
    foreach ($messages as $msg) {
        if ($msg['role'] === 'system') {
            $hasSystem = true;
            break;
        }
    }
    
    if (!$hasSystem && !empty($systemPrompt)) {
        array_unshift($messages, ['role' => 'system', 'content' => $systemPrompt]);
    }
    
    $data = [
        'model' => $model,
        'messages' => $messages,
        'stream' => true,
        'options' => array_merge([
            'temperature' => TEMPERATURE,
            'num_ctx' => CONTEXT_WINDOW,
            'num_predict' => MAX_TOKENS
        ], $options)
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use ($callback) {
        $lines = explode("\n", trim($chunk));
        foreach ($lines as $line) {
            if (empty($line)) continue;
            $decoded = json_decode($line, true);
            if (is_array($decoded) && isset($decoded['message']['content'])) {
                $callback($decoded);
            }
        }
        return strlen($chunk);
    });
    
    curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error];
    }
    
    return true;
}

function ollamaGenerate($model, $prompt, $stream = false) {
    $url = OLLAMA_URL . '/api/generate';
    $data = [
        'model' => $model,
        'prompt' => $prompt,
        'stream' => $stream
    ];
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return ['error' => $error];
    }
    
    return json_decode($response, true);
}

function checkOllamaStatus() {
    $url = OLLAMA_URL;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 200;
}

function listOllamaModels() {
    $url = OLLAMA_URL . '/api/tags';
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        return [];
    }
    
    $data = json_decode($response, true);
    if (isset($data['models']) && is_array($data['models'])) {
        $models = [];
        foreach ($data['models'] as $m) {
            $models[] = $m['name'];
        }
        return $models;
    }
    return [];
}
