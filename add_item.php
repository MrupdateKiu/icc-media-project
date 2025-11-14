<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Coordinator'){
    die("Access denied. Only coordinator can add items.");
}
require_once __DIR__ . '/db_connect.php';

if($_SERVER['REQUEST_METHOD']==='POST'){
    $name = $_POST['name'];
    $type = $_POST['type'];
    $price = $_POST['price'];
    $quality = $_POST['quality'];

    $stmt = $pdo->prepare("INSERT INTO merch_catalog (name,type,price,quality) VALUES (?,?,?,?)");
    $stmt->execute([$name,$type,$price,$quality]);

    header("Location: coordinator_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add New Catalog Item</title>
<style>
body{font-family:Arial,sans-serif;background:#f5faff;}
.container{max-width:600px;margin:50px auto;padding:20px;background:white;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
input, select{width:100%;padding:8px;margin:8px 0;border-radius:6px;border:1px solid #ccc;}
input[type=submit]{background:#0047ab;color:white;border:none;padding:10px;cursor:pointer;font-weight:bold;}
input[type=submit]:hover{background:#0066ff;}
</style>
</head>
<body>
<div class="container">
<h2>Add New Catalog Item</h2>
<form method="post">
<label>Name</label><input type="text" name="name" required>
<label>Type</label>
<select name="type" required>
<option value="tshirt">T-shirt</option>
<option value="jumper">Jumper</option>
<option value="cap">Cap</option>
</select>
<label>Price</label><input type="number" name="price" required>
<label>Quality</label><input type="text" name="quality" required>
<input type="submit" value="Add Item">
</form>
</div>
</body>
</html>
