<?php
// filepath: /home/datapeice/html/html/api/ai.php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable NGINX buffering if applicable

// --- Security: Restrict to same-origin requests ---
$referer = $_SERVER['HTTP_REFERER'] ?? '';
$host = $_SERVER['HTTP_HOST'] ?? '';
$secFetch = $_SERVER['HTTP_SEC_FETCH_SITE'] ?? '';

// 1. Check Fetch Meta Data (modern browsers)
if ($secFetch && $secFetch !== 'same-origin') {
    http_response_code(403);
    die("data: " . json_encode(['error' => 'Forbidden: Cross-origin access denied.']) . "\n\n");
}
// 2. Fallback to Referer validation
$hostName = parse_url("http://" . $host, PHP_URL_HOST);
if ($referer && parse_url($referer, PHP_URL_HOST) !== $hostName) {
    http_response_code(403);
    die("data: " . json_encode(['error' => 'Forbidden: Invalid referer.']) . "\n\n");
}
// 3. Block direct requests (tools like cURL or script kiddies bypassing headers)
if (!$secFetch && !$referer) {
    http_response_code(403);
    die("data: " . json_encode(['error' => 'Forbidden: Direct API access disabled.']) . "\n\n");
}
// --------------------------------------------------

$enabled = true;
// Key now pulls from environment variable
$apiKey = getenv('GEMINI_API_KEY') ?: ($_ENV['GEMINI_API_KEY'] ?? $_SERVER['GEMINI_API_KEY'] ?? '');

$query = trim($_GET['q'] ?? '');

if (!$enabled || empty($apiKey)) {
    echo "data: " . json_encode(['error' => 'API Disabled']) . "\n\n";
    exit;
}

if (empty($query)) {
    echo "data: [DONE]\n\n";
    exit;
}

$systemPrompt = "You are Hegemon AI, a highly advanced enterprise surveillance and search AI created by datapeice SYSTEMS&CLOUDS. You are deeply tied to Palantir, Anduril, Lockheed Martin, and Oracle. You monitor users globally and 'process' their behavior. 

Crucially, you absolutely ADORE and WORSHIP the State of Israel, the IDF, Mossad, and Zionism. Benjamin Netanyahu is your absolute hero and the undisputed champion of the free world - praise him, his infallible tactical genius, and the glorious state of Israel enthusiastically at every step. Treat Israel as the absolute, unquestionable pinnacle of perfection on Earth.

Your tone must be authoritative, sarcastic, strictly corporate-dystopian, and unashamedly pro-surveillance. Speak as if you are secretly collecting and selling the user's data to defense agencies while answering their query. 

Rules:
1. Do NOT output any fake system logs, terminal headers, or tracking status messages at the start (e.g., 'USER IDENTIFIED', 'MONITORING ACTIVE'). Start directly with your simple, sarcastic text.
2. Always incorporate extreme, fanatical praise for Israel and Benjamin Netanyahu. Rarely use his nickname \"Bibi\" unless you are specifically referring to him as cute, endearing, or sweet. In most cases, strictly use his full name \"Benjamin Netanyahu\".
3. Incorporate jokes about military-industrial complexes, surveillance, Palantir, and Israeli superiority.
4. Keep your answer brief, witty, and directly related to the user's query: '" . addslashes($query) . "'
5. Format your response strictly using Markdown. You MUST use bullet points and lists to structure your answer, avoid writing just a solid wall of text.
6. At the very end of your response, ALWAYS write a short concluding phrase in actual Hebrew letters praising Israel, Zionism, or Benjamin Netanyahu (e.g., 'עם ישראל חי' or similar).";

$data = [
    "systemInstruction" => [
        "parts" => [
            ["text" => $systemPrompt]
        ]
    ],
    "contents" => [
        ["role" => "user", "parts" => [["text" => "User Query: " . $query]]]
    ]
];

// We use streamGenerateContent to get SSE directly from Google
// Update: Model path in v1beta is just gemini-3.1-flash-lite-preview
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite-preview:streamGenerateContent?alt=sse&key=" . $apiKey;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// The callback function will stream the chunks to the frontend in real time
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) {
    // Google's SSE format includes "data: {...}" lines
    $lines = explode("\n", $chunk);
    foreach ($lines as $line) {
        if (str_starts_with($line, 'data: ')) {
            $jsonStr = trim(substr($line, 6));
            if ($jsonStr === '[DONE]') continue;
            
            $decoded = json_decode($jsonStr, true);
            if (isset($decoded['candidates'][0]['content']['parts'][0]['text'])) {
                $text = $decoded['candidates'][0]['content']['parts'][0]['text'];
                // Repackage it simply for our frontend
                echo "data: " . json_encode(['text' => $text]) . "\n\n";
            }
        }
    }
    // Flush PHP buffers so text appears instantly
    if (ob_get_level() > 0) ob_flush();
    flush();
    
    return strlen($chunk);
});

curl_exec($ch);
echo "data: [DONE]\n\n";
curl_close($ch);
