<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Only coordinator can edit orders
if (!isset($_SESSION['admin_logged_in']) || strtolower($_SESSION['admin_role']) !== 'coordinator') {
    http_response_code(403);
    die("Access denied. Only coordinator can edit orders.");
}

$order_id = $_GET['id'] ?? null;
if (!$order_id || !ctype_digit((string)$order_id)) {
    die("Invalid order ID.");
}

// fetch order
$stmt = $pdo->prepare("SELECT * FROM tshirt_orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$order) die("Order not found.");

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect + sanitize
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $size = trim($_POST['size'] ?? '');
    $color = trim($_POST['color'] ?? '');
    $quality = trim($_POST['quality'] ?? '');
    $payment = trim($_POST['payment'] ?? '');
    $item_type = trim($_POST['item_type'] ?? 'tshirt');

    // recompute price based on quality to avoid inconsistent manual price
    $price = 0;
    if ($quality === '35k-long') $price = 35000;
    elseif ($quality === '25k-short') $price = 25000;
    elseif ($quality === '15k-short') $price = 15000;
    else $price = floatval($_POST['price'] ?? 0);

    if ($name === '' || $email === '' || $phone === '') {
        $error = "Please fill required fields: name, email, phone.";
    } else {
        $update = $pdo->prepare("UPDATE tshirt_orders SET name=?, email=?, phone=?, location=?, size=?, color=?, quality=?, price=?, payment_option=?, item_type=? WHERE id=?");
        $update->execute([$name, $email, $phone, $location, $size, $color, $quality, $price, $payment, $item_type, $order_id]);

        header("Location: coordinator_dashboard.php");
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Edit Order #<?= htmlspecialchars($order['id']) ?> - ICC Media</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<style>
:root{--blue:#0047ab;--light:#f5faff}
body{margin:0;background:var(--light);font-family:"Times New Roman", Times, serif;display:flex;align-items:center;justify-content:center;min-height:100vh;padding:18px;}
.card{width:100%;max-width:820px;background:#fff;border-radius:12px;padding:20px;box-shadow:0 8px 30px rgba(0,0,0,0.08);box-sizing:border-box;}
h1{font-family:'Poppins',sans-serif;color:var(--blue);margin:0 0 12px;text-align:center;}
.form-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
label{display:block;margin:8px 0 6px;font-weight:bold;}
input[type=text], input[type=email], select, input[type=number]{
  width:100%;padding:9px;border-radius:8px;border:1px solid #ccc;font-size:15px;box-sizing:border-box;font-family:"Times New Roman",Times,serif;
}
.actions{display:flex;gap:10px;justify-content:flex-end;margin-top:14px;}
.button{padding:10px 14px;border-radius:8px;border:none;cursor:pointer;font-family:'Poppins',sans-serif;}
.button.save{background:var(--blue);color:#fff;}
.button.cancel{background:#f1f1f1;color:#333;}
.message{margin:10px 0;text-align:center;font-weight:600;}
.error{color:#b71c1c;}
.footer{margin-top:18px;text-align:center;padding:12px;background:#f1f9ff;color:var(--blue);border-radius:8px;font-size:0.9rem;}
@media(max-width:800px){.form-grid{grid-template-columns:1fr;} .actions{flex-direction:column;} .actions .button{width:100%}}
</style>
</head>
<body>

<div class="card">
  <h1>Edit Order #<?= htmlspecialchars($order['id']) ?></h1>

  <?php if($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="post" novalidate>
    <div class="form-grid">
      <div>
        <label for="name">Name</label>
        <input id="name" name="name" type="text" value="<?= htmlspecialchars($order['name']) ?>" required>

        <label for="email">Email</label>
        <input id="email" name="email" type="email" value="<?= htmlspecialchars($order['email']) ?>">

        <label for="phone">Phone</label>
        <input id="phone" name="phone" type="text" value="<?= htmlspecialchars($order['phone']) ?>" required>

        <label for="location">Location</label>
        <input id="location" name="location" type="text" value="<?= htmlspecialchars($order['location']) ?>">
      </div>

      <div>
        <label for="size">Size</label>
        <select id="size" name="size">
          <option value="S" <?= ($order['size']=='S')?'selected':''; ?>>S</option>
          <option value="M" <?= ($order['size']=='M')?'selected':''; ?>>M</option>
          <option value="L" <?= ($order['size']=='L')?'selected':''; ?>>L</option>
          <option value="XL" <?= ($order['size']=='XL')?'selected':''; ?>>XL</option>
        </select>

        <label for="color">Color</label>
        <input id="color" name="color" type="text" value="<?= htmlspecialchars($order['color']) ?>">

        <label for="quality">Quality</label>
        <select id="quality" name="quality">
          <option value="35k-long" <?= ($order['quality']=='35k-long')?'selected':''; ?>>35k-long (Long)</option>
          <option value="25k-short" <?= ($order['quality']=='25k-short')?'selected':''; ?>>25k-short (Short)</option>
          <option value="15k-short" <?= ($order['quality']=='15k-short')?'selected':''; ?>>15k-short (Short)</option>
        </select>

        <label for="payment">Payment Option</label>
        <select id="payment" name="payment">
          <option value="cash" <?= ($order['payment_option']=='cash')?'selected':''; ?>>Cash</option>
          <option value="mobile_money" <?= ($order['payment_option']=='mobile_money')?'selected':''; ?>>Mobile Money</option>
        </select>

        <label for="item_type">Item Type</label>
        <select id="item_type" name="item_type">
          <option value="tshirt" <?= ($order['item_type']=='tshirt')?'selected':''; ?>>T-shirt</option>
          <option value="jumper" <?= ($order['item_type']=='jumper')?'selected':''; ?>>Jumper</option>
          <option value="cap" <?= ($order['item_type']=='cap')?'selected':''; ?>>Cap</option>
        </select>
      </div>
    </div>

    <div style="margin-top:10px;">
      <label for="price">Price (UGX)</label>
      <input id="price" name="price" type="number" min="0" step="0.01" value="<?= htmlspecialchars($order['price']) ?>">
    </div>

    <div class="actions" style="margin-top:14px;">
      <a class="button cancel" href="coordinator_dashboard.php">Cancel</a>
      <button class="button save" type="submit">Update Order</button>
    </div>
  </form>

  <div class="footer">
    &copy; <?= date('Y') ?> ICC Media. All rights reserved. &nbsp;|&nbsp;
    <a href="https://chat.whatsapp.com/E4t7rbRuFJG3A27RkgmpAR?mode=ems_copy_t" target="_blank">WhatsApp</a>
  </div>
</div>

</body>
</html>
