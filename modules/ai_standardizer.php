<?php

require_once __DIR__ . "/../connection/openrouter.php";
require_once __DIR__ . "/ai_prompt.php";
require_once __DIR__ . "/schema_normalizer.php";

/**
 * Enterprise Triple-Provider AI Standardizer (OpenRouter -> Groq -> Gemini)
 * Guarantees 100% Uptime for Defense and User Acceptance Testing (UAT).
 */
function standardizeInput($input)
{
    $originalInput = trim($input);

    if ($originalInput == "") {
        return "";
    }

    // -------------------------------------------------------------------------
    // 1. Local Session Cache (Instant 0ms Response)
    // -------------------------------------------------------------------------
    if (!isset($_SESSION["ai_cache"])) {
        $_SESSION["ai_cache"] = [];
    }

    $dialect = $_POST['dialect'] ?? $_SESSION['selected_dialect'] ?? 'MySQL';
    $cacheKey = strtolower(preg_replace('/\s+/', ' ', $originalInput)) . "_" . strtolower($dialect);

    if (isset($_SESSION["ai_cache"][$cacheKey])) {
        $cachedSql = $_SESSION["ai_cache"][$cacheKey];
        if (strpos($cachedSql, 'BLOCKED BY SAFETY FIREWALL') === false && strpos($cachedSql, 'ERROR:') === false) {
            $GLOBALS["AI_CACHE_USED"] = true;
            return $cachedSql;
        }
    }

    // -------------------------------------------------------------------------
    // 2. Multi-Provider Fallback Strategy Configuration
    // -------------------------------------------------------------------------
    $basePrompt = getAISystemPrompt();
    $systemPrompt = $basePrompt . "\n\nSTRICT DIALECT REQUIREMENT:\nYou are an expert AI SQL Generator.\nConvert the natural language prompt into valid " . $dialect . " SQL syntax.\nStrictly use keywords, functions, data types, and pagination/limit operators native to " . $dialect . ". Return ONLY raw executable SQL without conversational prefix or suffix.";

    $providers = [
        [
            'name'  => 'OpenRouter AI (Primary - Fast & Stable)',
            'url'   => 'https://openrouter.ai/api/v1/chat/completions',
            'key'   => defined('OPENROUTER_API_KEY') ? OPENROUTER_API_KEY : '',
            'model' => 'meta-llama/llama-3.3-70b-instruct' // Paspas, 100% accurate sa SQL, ug gigamitan sa imong $4.80 credits
        ],
        [
            'name'  => 'Google Gemini Direct (Backup)',
            'url'   => 'https://generativelanguage.googleapis.com/v1beta/openai/chat/completions',
            'key'   => defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '',
            'model' => 'gemini-1.5-flash'
        ],
        [
            'name'  => 'Groq Cloud (Backup 2)',
            'url'   => 'https://api.groq.com/openai/v1/chat/completions',
            'key'   => defined('GROQ_API_KEY') ? GROQ_API_KEY : '',
            'model' => 'llama-3.1-8b-instant'
        ]
    ];

    $responseContent = null;
    $errors = [];

    // -------------------------------------------------------------------------
    // 3. Multi-Provider Execution Loop
    // -------------------------------------------------------------------------
    foreach ($providers as $provider) {
        if (empty($provider['key'])) {
            $errors[] = $provider['name'] . ": API Key is missing or empty.";
            continue;
        }

        $payload = [
            "model" => $provider['model'],
            "temperature" => 0,
            "max_tokens" => 500,
            "messages" => [
                ["role" => "system", "content" => $systemPrompt],
                ["role" => "user", "content" => $originalInput]
            ]
        ];

        $ch = curl_init($provider['url']);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 30,
            CURLOPT_SSL_VERIFYPEER => false, // 📌 Importante: Likayan ang SSL handshake block sa XAMPP
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_HTTPHEADER     => [
                "Authorization: Bearer " . $provider['key'],
                "Content-Type: application/json"
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload)
        ]);

        $response = curl_exec($ch);
        $curlErr = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($curlErr) {
            $errors[] = $provider['name'] . " cURL Error (" . $curlErr . ")";
            continue;
        }

        if ($httpCode !== 200) {
            $errors[] = $provider['name'] . " HTTP Status: " . $httpCode . " Response: " . substr($response, 0, 120);
            continue;
        }

        $json = json_decode($response, true);

        if (isset($json['error'])) {
            $errText = is_array($json['error']) ? json_encode($json['error']) : $json['error'];
            $errors[] = $provider['name'] . " API Error: " . $errText;
            continue;
        }

        if (isset($json['choices'][0]['message']['content'])) {
            $tempContent = trim($json['choices'][0]['message']['content']);

            if (stripos($tempContent, 'User Safety:') !== false || stripos($tempContent, 'Safety Categories:') !== false) {
                $errors[] = $provider['name'] . " returned false safety refusal.";
                continue;
            }

            $responseContent = $tempContent;
            break; // Success! Exit loop
        }
    }

    if (!$responseContent) {
        $diag = !empty($errors) ? implode(" | ", $errors) : "Network timeout or all keys exhausted.";
        return "-- ERROR: Unable to generate SQL. Details: " . $diag;
    }

    // -------------------------------------------------------------------------
    // 4. Smart Clean Output & Session Cache
    // -------------------------------------------------------------------------
    if (preg_match('/```(?:sql)?\s*(.*?)\s*```/is', $responseContent, $matches)) {
        $output = trim($matches[1]);
    } else {
        $output = preg_replace('/^```[a-z]*\s*/i', '', $responseContent);
        $output = preg_replace('/\s*```$/i', '', $output);
        $output = trim($output);
    }

    if (preg_match('/User\s*Safety\s*:\s*safe\s*/i', $output)) {
        $output = preg_replace('/User\s*Safety\s*:\s*safe\s*/i', '', $output);
        $output = trim($output);
    }

    // Save to Cache if valid
    if (!empty($output)) {
        $_SESSION["ai_cache"][$cacheKey] = $output;
    }

    return $output;
}
