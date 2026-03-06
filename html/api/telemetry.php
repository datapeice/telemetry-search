<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

try {
    $db = getDB();
    $rows = $db->query('SELECT id, query, ip, country, city, lat, lon, user_agent, device_type, os, device_model, searched_at FROM searches WHERE lat IS NOT NULL AND lon IS NOT NULL ORDER BY searched_at DESC LIMIT 500')->fetchAll();
    echo json_encode($rows);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
