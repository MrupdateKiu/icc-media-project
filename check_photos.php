<?php
require_once __DIR__ . '/db_connect.php';

$folder = __DIR__ . '/assets/profile_photos/';
$stmt = $pdo->query("SELECT name, photo FROM admins");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($admins as $admin) {
    $photo = $admin['photo'] ?? 'default.jpg';
    if (file_exists($folder . $photo)) {
        echo "{$admin['name']} - Photo found: {$photo}<br>";
    } else {
        echo "{$admin['name']} - Photo MISSING: {$photo}<br>";
    }
}
?>
