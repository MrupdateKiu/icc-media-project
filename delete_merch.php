<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Coordinator'){
    die("Access denied. Only coordinator can delete items.");
}
require_once __DIR__ . '/db_connect.php';

$item_id = $_GET['id'] ?? null;
if(!$item_id) die("Invalid item ID.");

$stmt = $pdo->prepare("DELETE FROM merch_catalog WHERE id=?");
$stmt->execute([$item_id]);

header("Location: coordinator_dashboard.php");
exit;
