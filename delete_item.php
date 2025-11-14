<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Coordinator'){
    die("Access denied.");
}

require_once __DIR__ . '/db_connect.php';

$order_id = $_GET['id'] ?? null;
if(!$order_id) die("Invalid order ID.");

$stmt = $pdo->prepare("DELETE FROM tshirt_orders WHERE id=?");
$stmt->execute([$order_id]);

header("Location: coordinator_dashboard.php");
exit;
?>
