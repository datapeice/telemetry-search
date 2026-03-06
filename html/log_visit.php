<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/api/search.php'; // For parseUA if accessible, or we just copy parseUA here
$uaRaw = $_SERVER['HTTP_USER_AGENT'] ?? '';
// Simple fallback parse
$deviceType = 'Desktop';
$os = 'Unknown';
if (preg_match('/Mobile|Android|iPhone/i', $uaRaw)) $deviceType = 'Mobile';
if (preg_match('/Windows/i', $uaRaw)) $os = 'Windows';
elseif (preg_match('/Mac OS X/i', $uaRaw)) $os = 'macOS';
elseif (preg_match('/Linux/i', $uaRaw)) $os = 'Linux';
elseif (preg_match('/Android/i', $uaRaw)) $os = 'Android';
elseif (preg_match('/iPhone|iPad/i', $uaRaw)) $os = 'iOS';

$ip = $_SERVER['REMOTE_ADDR'];
if ($ip === '127.0.0.1' || $ip === '::1') $ip = '8.8.8.8'; // mock for testing locally

$geoip = @json_decode(file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,lat,lon"), true);
if ($geoip && $geoip['status'] === 'success') {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO searches (query, ip, country, city, lat, lon, user_agent, device_type, os, device_model) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute(['[PAGE LOAD]', $ip, $geoip['country'], $geoip['city'], $geoip['lat'], $geoip['lon'], $uaRaw, $deviceType, $os, '']);
}
echo json_encode(['ok'=>1]);
