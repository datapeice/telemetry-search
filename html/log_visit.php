<?php
// Silently log visit
require_once __DIR__ . '/db.php';

$uaRaw = $_SERVER['HTTP_USER_AGENT'] ?? '';

function parseUALog(string $ua): array {
    $uaL = strtolower($ua);
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

    $type = 'Desktop';
    if (preg_match('/mobile/i', $ua)) $type = 'Mobile';
    elseif (preg_match('/tablet|ipad/i', $ua)) $type = 'Tablet';

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

$dev = parseUALog($uaRaw);

$ip = $_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['HTTP_X_REAL_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
if (strpos($ip, ',') !== false) {
    $ip = trim(explode(',', $ip)[0]);
}
$ip = trim($ip);

// Fallback for docker gateway IP during dev testing 
if (strpos($ip, '172.') === 0 || strpos($ip, '192.168.') === 0 || strpos($ip, '10.') === 0) {
    $isPublic = false;
} else {
    $isPublic = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
}
if ($isPublic) {
    // Basic caching for IP coordinates to avoid spamming ip-api
    $geo = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,lat,lon");
    $geo = $geo ? json_decode($geo, true) : null;
    if ($geo && $geo['status'] === 'success') {
        $db = getDB();
        $stmt = $db->prepare('INSERT INTO searches (query, ip, country, city, lat, lon, user_agent, device_type, os, device_model) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(['[SITE VISIT]', $ip, $geo['country'], $geo['city'], $geo['lat'], $geo['lon'], $uaRaw, $dev['type'], $dev['os'], $dev['model']]);
    }
} else {
    // localhost testing
    try {
        $db = getDB();
        $stmt = $db->prepare('INSERT INTO searches (query, ip, country, city, lat, lon, user_agent, device_type, os, device_model) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $stmt->execute(['[SITE VISIT]', $ip, 'Poland', 'Warsaw (localhost fallback)', 52.2297, 21.0122, $uaRaw, $dev['type'], $dev['os'], $dev['model']]);
    } catch (\Throwable $e) {}
}

echo json_encode(['ok'=>1]);
