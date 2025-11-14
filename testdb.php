<?php
// Test InfinityFree DB connection using your db_connect.php
require_once "db_connect.php"; // This uses your dual environment connector

try {
    // Simple query to test connection
    $stmt = $pdo->query("SHOW TABLES");
    $tables = $stmt->fetchAll(PDO::FETCH_NUM);

    if($tables) {
        echo "<p style='text-align:center; color:green;'>✅ Connected successfully! Your database has " . count($tables) . " table(s).</p>";
        echo "<p style='text-align:center;'>Tables: " . implode(", ", array_map(fn($t) => $t[0], $tables)) . "</p>";
    } else {
        echo "<p style='text-align:center; color:orange;'>⚠️ Connected but no tables found.</p>";
    }
} catch (PDOException $e) {
    echo "<p style='text-align:center; color:red;'>❌ Connection failed: " . $e->getMessage() . "</p>";
}
?>
