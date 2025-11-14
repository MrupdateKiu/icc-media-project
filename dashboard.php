<?php
session_start();
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

// Database connection
$host = "localhost";
$db = "ICCMEDIA_PROJECT";
$user = "root";
$pass = "";

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e){
    die("Database connection failed: ".$e->getMessage());
}

// Role check
$is_coordinator = $_SESSION['admin_role'] === 'coordinator';

// Fetch filters
$filter_quality = $_GET['quality'] ?? '';
$filter_payment = $_GET['payment'] ?? '';
$filter_item = $_GET['item'] ?? '';

// Handle adding new merch (coordinator only)
$new_item_message = '';
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_item']) && $is_coordinator){
    $item_name = $_POST['item_name'];
    $item_type = $_POST['item_type'];
    $item_price = $_POST['item_price'];
    $item_quality = $_POST['item_quality'];

    $stmt = $pdo->prepare("INSERT INTO merch_catalog (name, type, price, quality) VALUES (?,?,?,?)");
    $stmt->execute([$item_name, $item_type, $item_price, $item_quality]);
    $new_item_message = "New item '$item_name' added successfully!";
}

// Fetch orders
$sql = "SELECT * FROM tshirt_orders WHERE 1=1";
$params = [];
if ($filter_quality) { $sql .= " AND quality = ?"; $params[] = $filter_quality; }
if ($filter_payment) { $sql .= " AND payment_option = ?"; $params[] = $filter_payment; }
if ($filter_item) { $sql .= " AND item_type = ?"; $params[] = $filter_item; }
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch merch catalog
$catalog_stmt = $pdo->query("SELECT * FROM merch_catalog ORDER BY id DESC");
$catalog_items = $catalog_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch admin contact list
$admin_stmt = $pdo->query("SELECT name, role, photo FROM admins");
$admins = $admin_stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats calculations
$total_orders = count($orders);
$total_revenue = array_sum(array_column($orders,'price'));
$pending_mobile = count(array_filter($orders, fn($o)=>$o['payment_option']=='mobile_money'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ICC Media Admin Dashboard</title>
<style>
body { font-family: Arial,sans-serif; background:#f5faff; margin:0; padding:0;}
.container { max-width:1200px; margin:30px auto; padding:20px; background:white; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);}
h1,h2,h3 { color:#0047ab; text-align:center; margin-bottom:20px;}
h3 { margin-top:30px;}
.stats { display:flex; flex-wrap:wrap; justify-content:center; gap:20px; margin-bottom:20px;}
.card { flex:1 1 200px; background:#e0f0ff; padding:15px; border-radius:12px; text-align:center; font-weight:bold; }
table { width:100%; border-collapse: collapse; margin-top:15px; animation: fadeIn 1s;}
th, td { border:1px solid #ccc; padding:8px; text-align:center; font-size:14px; }
th { background:#0047ab; color:white; }
tr:nth-child(even) { background:#f5f5f5; }
.filter { margin-bottom:20px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
.filter label { font-weight:bold; }
input[type=text], input[type=number], select { padding:6px; border-radius:6px; border:1px solid #ccc;}
input[type=submit]{ padding:8px 15px; border:none; border-radius:6px; background:#0047ab; color:white; cursor:pointer; font-weight:bold;}
input[type=submit]:hover{ background:#0066ff;}
.message{ color:green; font-weight:bold; text-align:center; margin:10px 0;}
.logout{ text-align:right; margin-bottom:10px;}
.profile { display:flex; align-items:center; justify-content:flex-end; gap:10px; margin-bottom:10px;}
.profile img { width:40px; height:40px; border-radius:50%; }
.logo { text-align:center; margin-bottom:20px;}
.admin-list { display:flex; flex-wrap:wrap; gap:15px; justify-content:center; }
.admin-card { text-align:center; width:100px;}
.admin-card img { width:70px; height:70px; border-radius:50%; }
@keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
@media(max-width:768px){ .stats{flex-direction:column;} table, .filter{ font-size:12px;} }
</style>
</head>
<body>
<div class="container">
<div class="profile">
    <span><?= $_SESSION['admin_name'] ?> (<?= $_SESSION['admin_role'] ?>)</span>
    <img src="uploads/profiles/<?php echo $_SESSION['admin_photo'] ?? 'default.png'; ?>" alt="Profile">
    <a href="logout.php" style="color:#0047ab; font-weight:bold;">Logout</a>
</div>

<h1>ICC Media Admin Dashboard</h1>

<!-- Stats Cards -->
<div class="stats">
    <div class="card">Total Orders: <?= $total_orders ?></div>
    <div class="card">Total Revenue: UGX <?= number_format($total_revenue) ?></div>
    <div class="card">Pending Mobile Money: <?= $pending_mobile ?></div>
</div>

<!-- Filter Orders -->
<form method="get" class="filter">
    <label>Quality:</label>
    <select name="quality">
        <option value="">All</option>
        <option value="35k-long" <?= ($filter_quality=='35k-long')?'selected':''; ?>>35k-long</option>
        <option value="25k-short" <?= ($filter_quality=='25k-short')?'selected':''; ?>>25k-short</option>
        <option value="15k-short" <?= ($filter_quality=='15k-short')?'selected':''; ?>>15k-short</option>
    </select>

    <label>Payment:</label>
    <select name="payment">
        <option value="">All</option>
        <option value="mobile_money" <?= ($filter_payment=='mobile_money')?'selected':''; ?>>Mobile Money</option>
        <option value="cash" <?= ($filter_payment=='cash')?'selected':''; ?>>Cash</option>
    </select>

    <label>Item Type:</label>
    <select name="item">
        <option value="">All</option>
        <option value="tshirt" <?= ($filter_item=='tshirt')?'selected':''; ?>>T-shirt</option>
        <option value="jumper" <?= ($filter_item=='jumper')?'selected':''; ?>>Jumper</option>
        <option value="cap" <?= ($filter_item=='cap')?'selected':''; ?>>Cap</option>
    </select>

    <input type="submit" value="Filter">
</form>

<!-- Orders Table -->
<h2>Orders</h2>
<table>
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Location</th>
<th>Size</th><th>Color</th><th>Quality</th><th>Price</th><th>Payment</th>
<th>Item Type</th><th>Date</th>
<?php if($is_coordinator) echo "<th>Actions</th>"; ?>
</tr>

<?php foreach($orders as $order): ?>
<tr>
<td><?= $order['id'] ?></td>
<td><?= htmlspecialchars($order['name']) ?></td>
<td><?= htmlspecialchars($order['email']) ?></td>
<td><?= htmlspecialchars($order['phone']) ?></td>
<td><?= htmlspecialchars($order['location']) ?></td>
<td><?= $order['size'] ?></td>
<td><?= $order['color'] ?></td>
<td><?= $order['quality'] ?></td>
<td><?= number_format($order['price']) ?></td>
<td><?= $order['payment_option'] ?></td>
<td><?= $order['item_type'] ?></td>
<td><?= $order['order_date'] ?></td>
<?php if($is_coordinator): ?>
<td>
    <a href="edit_order.php?id=<?= $order['id'] ?>">Edit</a> |
    <a href="delete_order.php?id=<?= $order['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
</td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>

<!-- Add New Merchandise Item (Coordinator only) -->
<?php if($is_coordinator): ?>
<h2>Add New Item to Catalog</h2>
<?php if($new_item_message) echo "<p class='message'>$new_item_message</p>"; ?>
<form method="post" class="filter">
    <input type="hidden" name="add_item" value="1">
    <label>Item Name:</label>
    <input type="text" name="item_name" required>

    <label>Item Type:</label>
    <select name="item_type" required>
        <option value="tshirt">T-shirt</option>
        <option value="jumper">Jumper</option>
        <option value="cap">Cap</option>
    </select>

    <label>Price (UGX):</label>
    <input type="number" name="item_price" required>

    <label>Quality:</label>
    <input type="text" name="item_quality" placeholder="Awesome/Nice" required>

    <input type="submit" value="Add Item">
</form>
<?php endif; ?>

<!-- Merch Catalog -->
<h2>Merchandise Catalog</h2>
<table>
<tr>
<th>ID</th><th>Name</th><th>Type</th><th>Price</th><th>Quality</th>
<?php if($is_coordinator) echo "<th>Actions</th>"; ?>
</tr>
<?php foreach($catalog_items as $item): ?>
<tr>
<td><?= $item['id'] ?></td>
<td><?= htmlspecialchars($item['name']) ?></td>
<td><?= $item['type'] ?></td>
<td><?= number_format($item['price']) ?></td>
<td><?= htmlspecialchars($item['quality']) ?></td>
<?php if($is_coordinator): ?>
<td>
    <a href="edit_item.php?id=<?= $item['id'] ?>">Edit</a> |
    <a href="delete_item.php?id=<?= $item['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
</td>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>

<!-- Admin Contact List -->
<h3>Contact Our Admins for More Info</h3>
<div class="admin-list">
<?php foreach($admins as $admin): ?>
<div class="admin-card">
    <img src="uploads/profiles/<?= $admin['photo'] ?>" alt="<?= htmlspecialchars($admin['name']) ?>">
    <p><?= htmlspecialchars($admin['name']) ?><br><?= htmlspecialchars($admin['role']) ?></p>
</div>
<?php endforeach; ?>
</div>

</div>
</body>
</html>
