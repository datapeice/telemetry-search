<?php
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $host = getenv('DB_HOST') ?: 'db';
        $name = getenv('DB_NAME') ?: 'searchdb';
        $user = getenv('DB_USER') ?: 'searchuser';
        $pass = getenv('DB_PASS') ?: 'searchpass';
        $pdo = new PDO("pgsql:host={$host};dbname={$name}", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
    return $pdo;
}
