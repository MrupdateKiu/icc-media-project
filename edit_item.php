<?php
session_start();
if(!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_role'] !== 'Coordinator'){
    die("Access denied.");
}

require_once __DIR__ . '/db_connect.php';

$order_id = $_GET['id'] ?? null;
if(!$order_id) die("Invalid order ID.");

$stmt = $pdo->prepare("SELECT * FROM tshirt_orders WHERE id=?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if(!$order) die("Order not found.");

// Handle POST update
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $location = $_POST['location'];
    $size = $_POST['size'];
    $color = $_POST['color'];
    $quality = $_POST['quality'];
    $price = $_POST['price'];
    $payment_option = $_POST['payment_option'];
    $item_type = $_POST['item_type'];

    $update = $pdo->prepare("UPDATE tshirt_orders SET name=?, email=?, phone=?, location=?, size=?, color=?, quality=?, price=?, payment_option=?, item_type=? WHERE id=?");
    $update->execute([$name,$email,$phone,$location,$size,$color,$quality,$price,$payment_option,$item_type,$order_id]);

    header("Location: coordinator_dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Order #<?= $order['id'] ?></title>
<style>
body{font-family:Arial,sans-serif;background:#f5faff;margin:0;padding:0;}
.container{max-width:600px;margin:40px auto;padding:20px;background:white;border-radius:12px;box-shadow:0 4px 12px rgba(0,0,0,0.1);}
input, select{width:100%;padding:10px;margin:8px 0;border-radius:6px;border:1px solid #ccc;box-sizing:border-box;}
input[type=submit]{background:#0047ab;color:white;border:none;padding:12px;font-weight:bold;cursor:pointer;transition:0.3s;}
input[type=submit]:hover{background:#0066ff;transform:scale(1.03);}
</style>
</head>
<body>
<div class="container">
<h2>Edit Order #<?= $order['id'] ?></h2>
<form method="post">
<label>Name</label><input type="text" name="name" value="<?= htmlspecialchars($order['name']) ?>" required>
<label>Email</label><input type="email" name="email" value="<?= htmlspecialchars($order['email']) ?>" required>
<label>Phone</label><input type="text" name="phone" value="<?= htmlspecialchars($order['phone']) ?>" required>
<label>Location</label><input type="text" name="location" value="<?= htmlspecialchars($order['location']) ?>" required>
<label>Size</label><input type="text" name="size" value="<?= htmlspecialchars($order['size']) ?>" required>
<label>Color</label><input type="text" name="color" value="<?= htmlspecialchars($order['color']) ?>" required>
<label>Quality</label><input type="text" name="quality" value="<?= htmlspecialchars($order['quality']) ?>" required>
<label>Price</label><input type="number" name="price" value="<?= $order['price'] ?>" required>
<label>Payment Option</label>
<select name="payment_option" required>
<option value="mobile_money" <?= ($order['payment_option']=='mobile_money')?'selected':'' ?>>Mobile Money</option>
<option value="cash" <?= ($order['payment_option']=='cash')?'selected':'' ?>>Cash</option>
</select>
<label>Item Type</label>
<select name="item_type" required>
<option value="tshirt" <?= ($order['item_type']=='tshirt')?'selected':'' ?>>T-shirt</option>
<option value="jumper" <?= ($order['item_type']=='jumper')?'selected':'' ?>>Jumper</option>
<option value="cap" <?= ($order['item_type']=='cap')?'selected':'' ?>>Cap</option>
</select>
<input type="submit" value="Update Order">
</form>
</div>
</body>
</html>
