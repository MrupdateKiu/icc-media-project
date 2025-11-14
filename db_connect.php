<?php
/**
 * DBConnector 1.2 — Dual Environment (Local + Online)
 * Automatically switches between localhost and InfinityFree hosting.
 */

$server_name = $_SERVER['SERVER_NAME'] ?? 'localhost';

if (strpos($server_name, 'localhost') !== false) {
    // 💻 Localhost (XAMPP)
    $DB_HOST = 'localhost';
    $DB_NAME = 'ICCMEDIA_PROJECT';   // your local database name
    $DB_USER = 'root';
    $DB_PASS = '';
} else {
    // 🌐 Online (InfinityFree)
    $DB_HOST = 'sql211.infinityfree.com';
    $DB_NAME = 'if0_40279345_media_project_db';
    $DB_USER = 'if0_40279345';   // fixed: string properly closed
    $DB_PASS = 'qBdxYJqrEz';
}

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>


