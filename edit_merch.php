<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Access control: only logged-in coordinators
if (!isset($_SESSION['admin_logged_in']) || strtolower($_SESSION['admin_role']) !== 'coordinator') {
    http_response_code(403);
    die("Access denied. Only coordinator can edit items.");
}

$item_id = $_GET['id'] ?? null;
if (!$item_id || !ctype_digit((string)$item_id)) {
    die("Invalid item ID.");
}

// fetch item
$stmt = $pdo->prepare("SELECT * FROM merch_catalog WHERE id = ?");
$stmt->execute([$item_id]);
$item = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$item) die("Item not found.");

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // collect + basic trimming
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $quality = trim($_POST['quality'] ?? '');

    // basic validation
    if ($name === '' || $type === '' || $price === '' || !is_numeric($price)) {
        $error = "Please fill all fields correctly.";
    } else {
        $update = $pdo->prepare("UPDATE merch_catalog SET name = ?, type = ?, price = ?, quality = ? WHERE id = ?");
        $update->execute([$name, $type, (float)$price, $quality, $item_id]);

        // redirect to avoid resubmit
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
<title>Edit Catalog Item #<?= htmlspecialchars($item['id']) ?> — ICC Media</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">
<style>
:root{--blue:#0047ab;--light:#f5faff;}
body{
  margin:0;background:var(--light);font-family:"Times New Roman", Times, serif;
  display:flex;align-items:center;justify-content:center;min-height:100vh;padding:20px;
}
.card{
  width:100%;max-width:700px;background:#fff;border-radius:12px;padding:22px;box-shadow:0 8px 30px rgba(0,0,0,0.08);
}
h1{font-family:'Poppins',sans-serif;color:var(--blue);margin:0 0 14px;font-size:1.4rem;text-align:center;}
form label{display:block;margin:10px 0 6px;font-weight:bold;}
input[type=text], input[type=number], select, textarea{
  width:100%;padding:10px;border-radius:8px;border:1px solid #ccc;font-size:15px;box-sizing:border-box;
  font-family:"Times New Roman", Times, serif;
}
.row{display:flex;gap:12px;flex-wrap:wrap;}
.col{flex:1;min-width:140px;}
.actions{display:flex;gap:10px;justify-content:flex-end;margin-top:14px;}
.button{padding:10px 14px;border-radius:8px;border:none;cursor:pointer;font-family:'Poppins',sans-serif;}
.button.save{background:var(--blue);color:#fff;}
.button.cancel{background:#f1f1f1;color:#333;}
.message{margin:10px 0;font-weight:600;text-align:center;}
.error{color:#b71c1c;}
.footer{margin-top:18px;text-align:center;padding:12px;background:#f1f9ff;color:var(--blue);border-radius:8px;font-size:0.9rem;}
@media(max-width:600px){ .actions{flex-direction:column;} .actions .button{width:100%;} }
</style>
</head>
<body>

<div class="card" role="main">
  <h1>Edit Catalog Item #<?= htmlspecialchars($item['id']) ?></h1>

  <?php if($error): ?><p class="message error"><?= htmlspecialchars($error) ?></p><?php endif; ?>

  <form method="post" novalidate>
    <label for="name">Name</label>
    <input id="name" name="name" type="text" value="<?= htmlspecialchars($item['name']) ?>" required>

    <div class="row">
      <div class="col">
        <label for="type">Type</label>
        <select id="type" name="type" required>
          <option value="tshirt" <?= ($item['type']=='tshirt')? 'selected' : '' ?>>T-shirt</option>
          <option value="jumper" <?= ($item['type']=='jumper')? 'selected' : '' ?>>Jumper</option>
          <option value="cap" <?= ($item['type']=='cap')? 'selected' : '' ?>>Cap</option>
        </select>
      </div>

      <div class="col">
        <label for="price">Price (UGX)</label>
        <input id="price" name="price" type="number" min="0" step="0.01" value="<?= htmlspecialchars($item['price']) ?>" required>
      </div>
    </div>

    <label for="quality">Quality / Notes</label>
    <input id="quality" name="quality" type="text" value="<?= htmlspecialchars($item['quality']) ?>">

    <div class="actions">
      <a class="button cancel" href="coordinator_dashboard.php" role="button">Cancel</a>
      <button class="button save" type="submit">Update Item</button>
    </div>
  </form>

  <div class="footer">
    &copy; <?= date('Y') ?> ICC Media. All rights reserved. &nbsp;|&nbsp;
    <a href="https://chat.whatsapp.com/E4t7rbRuFJG3A27RkgmpAR?mode=ems_copy_t" target="_blank">WhatsApp</a>
  </div>
</div>

</body>
</html>
