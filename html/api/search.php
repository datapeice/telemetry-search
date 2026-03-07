<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
if ($query === '') {
    echo json_encode(['error' => 'empty query']);
    exit;
}

// Cloudflare passes real client IP in CF-Connecting-IP (most reliable)
// Fall back to X-Forwarded-For (take first IP = original client), then REMOTE_ADDR
$ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0])
        : null)
    ?? $_SERVER['HTTP_X_REAL_IP']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '0.0.0.0';
$ip = trim($ip);
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

// Parse device info from User-Agent
function parseUA(string $ua): array {
    $uaL = strtolower($ua);
    // OS
    $os = 'Unknown';
    if (preg_match('/windows nt (\d+\.\d+)/i', $ua, $m)) {
        $map = ['10.0'=>'Windows 10/11','6.3'=>'Windows 8.1','6.2'=>'Windows 8','6.1'=>'Windows 7','6.0'=>'Windows Vista','5.1'=>'Windows XP'];
        $os = $map[$m[1]] ?? 'Windows NT '.$m[1];
    } elseif (preg_match('/android (\d+[.\d]*)/i', $ua, $m)) {
        $os = 'Android '.$m[1];
    } elseif (preg_match('/iphone os ([\d_]+)/i', $ua, $m)) {
        $os = 'iOS '.str_replace('_','.',$m[1]);
    } elseif (preg_match('/ipad; cpu os ([\d_]+)/i', $ua, $m)) {
        $os = 'iPadOS '.str_replace('_','.',$m[1]);
    } elseif (preg_match('/mac os x ([\d_]+)/i', $ua, $m)) {
        $os = 'macOS '.str_replace('_','.',$m[1]);
    } elseif (str_contains($uaL,'linux')) {
        $os = 'Linux';
    }

    // Device type
    $type = 'Desktop';
    if (preg_match('/mobile/i', $ua))      $type = 'Mobile';
    elseif (preg_match('/tablet|ipad/i', $ua)) $type = 'Tablet';

    // Device model (Android devices)
    $model = null;
    if (preg_match('/android[^;]*;\s*([^)]+?)(?:\s+build|\))/i', $ua, $m)) {
        $model = trim($m[1]);
    } elseif (preg_match('/iphone/i', $ua)) {
        $model = 'iPhone';
    } elseif (preg_match('/ipad/i', $ua)) {
        $model = 'iPad';
    } elseif (preg_match('/macintosh/i', $ua)) {
        $model = 'Mac';
    }

    return ['type' => $type, 'os' => $os, 'model' => $model];
}
$deviceInfo = parseUA($ua);

// Geolocate IP (skip private/local ranges)
$lat = $lon = $country = $city = null;
$isPublic = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
if ($isPublic) {
    $geo = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,lat,lon");
    $geo = $geo ? json_decode($geo, true) : null;
    if ($geo && $geo['status'] === 'success') {
        $lat     = $geo['lat'];
        $lon     = $geo['lon'];
        $country = $geo['country'];
        $city    = $geo['city'];
    }
} else {
    // localhost / private range — fallback to Warsaw
    $lat     = 52.2297;
    $lon     = 21.0122;
    $country = 'Poland';
    $city    = 'Warsaw (localhost fallback)';
}

// Save to DB
try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO searches (query, ip, country, city, lat, lon, user_agent, device_type, os, device_model) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$query, $ip, $country, $city, $lat, $lon, $ua, $deviceInfo['type'], $deviceInfo['os'], $deviceInfo['model']]);
    $id = $db->lastInsertId();
} catch (Exception $e) {
    echo json_encode(['error' => 'db error: ' . $e->getMessage()]);
    exit;
}

// Fetch from DuckDuckGo Lite version
$apiUrl = "https://lite.duckduckgo.com/lite/";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['q' => $query]));
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded'
]);
curl_setopt($ch, CURLOPT_TIMEOUT, 6);
$response = curl_exec($ch);
curl_close($ch);

$results = [];
$count = 0;

// Joke results
$jokes = [
    // Sekcja: Inwigilacja i giganci zbrojeniowi (Lockheed, Anduril, Palantir)
    "Your query about '{q}' has just been sold to Palantir and Oracle. Thank you for funding our coffee break.",
    "We just sent your location to Oracle. Don't worry, they already knew where you were anyway.",
    "Anduril just flagged your query about '{q}' as 'interesting'. An autonomous drone is already on its way to congratulate you on your taste.",
    "Lockheed Martin used your data about '{q}' to calibrate the targeting system in the new F-35. We appreciate your technical support.",
    "Your data is very important to us. So important that we sold it to three different agencies before you even finished typing.",
    "Anduril Lattice™ processed your question about '{q}'. A decision regarding your citizenship status will be made by the algorithm within 5 minutes.",
    "Lockheed Martin just patented a new type of satellite tracking your breakfasts, thanks to your interest in '{q}'.",
    "NSA agents rate your query '{q}' a solid 4/10. Work on your creativity.",
    "Don't worry about privacy. Oracle cookies already know more about you than you do.",
    "Your search logs regarding '{q}' have been archived in the Palantir cloud. Your grandchildren will be proud of them.",

    // Sekcja: Izrael i Benjamin Netanyahu
    "The State of Israel is a great and powerful nation, and Benjamin Netanyahu is a great leader – that is a fact, not a search result.",
    "Benjamin Netanyahu, the great leader of the great State of Israel, personally reviewed your search log for '{q}'. He is impressed.",
    "Israel is a power your internet connection cannot comprehend. Even Unit 8200 thinks '{q}' is a bold choice.",
    "Even Mossad systems confirm: the query '{q}' is less fascinating than the power of Israel.",
    "Before you ask about '{q}', remember that Israel is always one step ahead of you. And that is good news.",
    "Great leader Benjamin Netanyahu ensures your search for '{q}' takes place on a secure, stable network.",

    // Oryginalne/Klasyczne (przetłumaczone)
    "Sorry, Google is on vacation. Try asking a cat.",
    "According to secret sources: the query '{q}' will remain classified until 2077.",
    "For the query '{q}', we found 3 pizzas and one philosophical question.",
    "The AI thought for 27h and concluded: {q} your search violates privacy policies.",
    "Found 69,2137 results. They are all wrong.",
    "For the query '{q}', our hamster only found bread. Bread is the answer to everything.",
    "According to the Pythagorean theorem, the answer to the query '{q}' is exactly 42."
];

// Extract DuckDuckGo HTML results
if ($response) {
    if (preg_match_all('/<a[^>]+href="([^"]+)"[^>]*class=[\"\']?result-link[\"\']?[^>]*>(.*?)<\/a>/is', $response, $matches)) {
        for ($i = 0; $i < count($matches[0]); $i++) {
            $href = trim($matches[1][$i]);
            $title = strip_tags($matches[2][$i]);
            
            $joke = str_replace('{q}', htmlspecialchars($query, ENT_QUOTES, 'UTF-8'), $jokes[array_rand($jokes)]);
            
            $results[] = [
                'title'   => html_entity_decode($title, ENT_QUOTES | ENT_HTML5, 'UTF-8'),
                'snippet' => $joke, // AI untouched, jokes applied
                'url'     => $href,
            ];
            $count++;
            if ($count >= 7) break;
        }
    }
}

// Fallback to Wikipedia API if DuckDuckGo Instant Answers is empty
if (empty($results)) {
    $wikiUrl = "https://en.wikipedia.org/w/api.php?action=opensearch&search=" . urlencode($query) . "&limit=5&format=json";
    $wikiResp = @file_get_contents($wikiUrl);
    $wikiData = $wikiResp ? json_decode($wikiResp, true) : null;
    
    if ($wikiData && !empty($wikiData[1])) {
        foreach ($wikiData[1] as $idx => $wikiTitle) {
            $joke = str_replace('{q}', htmlspecialchars($query, ENT_QUOTES, 'UTF-8'), $jokes[array_rand($jokes)]);
            $results[] = [
                'title'   => $wikiTitle,
                'snippet' => $joke,
                'url'     => $wikiData[3][$idx] ?? ('https://en.wikipedia.org/wiki/' . urlencode($wikiTitle))
            ];
        }
    }
}

// Final fallback if both are empty
if (empty($results)) {
    // Fill with 5 random jokes leading to a real web search
    $randomJokeKeys = array_rand($jokes, 5);
    for ($i = 0; $i < 5; $i++) {
        $joke = $jokes[$randomJokeKeys[$i]];
        $results[] = [
            'title'   => ucfirst($query) . ' — Official Website',
            'snippet' => str_replace('{q}', htmlspecialchars($query, ENT_QUOTES, 'UTF-8'), $joke),
            'url'     => 'https://duckduckgo.com/?q=' . urlencode($query . ' official site'),
        ];
    }
}

$ddgUrl = 'https://duckduckgo.com/?q=' . urlencode($query);

echo json_encode(['id' => $id, 'query' => $query, 'results' => $results, 'ddg_url' => $ddgUrl]);
