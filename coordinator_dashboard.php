<?php
session_start();

// Restrict access: only logged-in coordinators
if(!isset($_SESSION['admin_logged_in']) || strtolower($_SESSION['admin_role']) !== 'coordinator'){
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/db_connect.php';

// Profile data
$admin_name  = $_SESSION['admin_name'] ?? 'Coordinator';
$admin_role  = $_SESSION['admin_role'] ?? 'Coordinator';
$photo_file  = $_SESSION['admin_photo'] ?? 'default.jpg';

// Build full path to check existence
$photo_path_full = __DIR__ . '/assets/profile_photos/' . $photo_file;
if(!file_exists($photo_path_full) || empty($photo_file)){
    $photo_file = 'default.jpg';
}

// Path for HTML <img> tag
$photo_path = 'assets/profile_photos/' . $photo_file;

// Fetch filters safely
$filter_quality = $_GET['quality'] ?? '';
$filter_payment = $_GET['payment'] ?? '';
$filter_item    = $_GET['item'] ?? '';

// Fetch orders with filters
$sql = "SELECT * FROM tshirt_orders WHERE 1=1";
$params = [];
if($filter_quality){ $sql .= " AND quality=?"; $params[] = $filter_quality; }
if($filter_payment){ $sql .= " AND payment_option=?"; $params[] = $filter_payment; }
if($filter_item){ $sql .= " AND item_type=?"; $params[] = $filter_item; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Statistics
$total_orders = count($orders);
$total_revenue = 0;
$pending_mobile = 0;
foreach($orders as $order){
    $total_revenue += $order['price'];
    if($order['payment_option'] === 'mobile_money'){
        $pending_mobile++;
    }
}

// Fetch merchandise catalog
$catalog_stmt = $pdo->query("SELECT * FROM merch_catalog ORDER BY id DESC");
$catalog_items = $catalog_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch team members
try {
    $team_stmt = $pdo->query("SELECT name, role, photo FROM team_members ORDER BY role ASC, name ASC");
    $team_members = $team_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $team_members = []; // fallback to empty array if table doesn't exist
    error_log("Team members fetch failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ICC Media Coordinator Dashboard</title>
<style>
/* =========================
   Coordinator Dashboard CSS
   ========================= */

/* Base */
body{
  font-family: Arial, sans-serif;
  background: #f5faff;
  margin: 0;
  padding: 0;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}
.container{
  max-width: 1200px;
  margin: 20px auto;
  padding: 20px;
  background: white;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  box-sizing: border-box;
}
h1,h2{
  text-align: center;
  color: #0047ab;
  margin-bottom: 20px;
  font-weight: 700;
}

/* Logo & Profile */
.logo { text-align: center; margin-bottom: 8px; }

.profile {
  display: flex;
  justify-content: space-between; /* stacked profile left, logout right */
  align-items: center;
  gap: 20px;
}

/* Profile Left: photo + name + role */
.profile-left {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  gap: 4px;
}
.profile-left img {
  width: 50px;
  height: 50px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #0047ab;
}
.profile-left .name {
  font-size: 16px;
  font-weight: bold;
  color: #0047ab;
}
.profile-left .role {
  font-size: 14px;
  color: #666;
}

/* Logout button */
.profile a.logout {
  padding: 8px 14px;
  font-size: 14px;
  background: #0047ab;
  color: white;
  border-radius: 6px;
  text-decoration: none;
  white-space: nowrap;
}

/* Stats */
.stats{
  display:flex;
  gap:20px;
  justify-content:center;
  margin-bottom:20px;
  flex-wrap:wrap;
}
.stat-box{
  background:#f1f9ff;
  color:#0047ab;
  padding:15px;
  border-radius:12px;
  flex:1;
  text-align:center;
  min-width:150px;
  box-sizing:border-box;
  font-weight:700;
}

/* Filter */
.filter{
  margin-bottom:20px;
  display:flex;
  gap:10px;
  flex-wrap:wrap;
  align-items:center;
  justify-content:center;
}
.filter label{ font-weight:700; }
input[type=text], input[type=number], select{
  padding:6px;
  border-radius:6px;
  border:1px solid #ccc;
  box-sizing:border-box;
}
input[type=submit], button{
  padding:8px 15px;
  border:none;
  border-radius:6px;
  background:#0047ab;
  color:white;
  cursor:pointer;
  font-weight:700;
}
input[type=submit]:hover, button:hover{ background:#0066ff; }

/* Tables */
.table-responsive{
  width:100%;
  overflow-x:auto;
  -webkit-overflow-scrolling:touch;
  margin-bottom:20px;
  border-radius:12px;
  box-shadow:0 2px 8px rgba(0,0,0,0.05);
}
table{
  width:100%;
  border-collapse:collapse;
  min-width:700px;
  box-sizing:border-box;
}
th, td{
  border:1px solid #ccc;
  padding:8px;
  text-align:center;
  font-size:14px;
  white-space:nowrap;
}
th{
  background:#0047ab;
  color:white;
}
tr:nth-child(even){ background:#f5f5f5; }
.actions a{
  color:#0047ab;
  text-decoration:none;
  margin:0 5px;
}
.actions a:hover{ text-decoration:underline; }

/* Footer */
.footer{
  margin-top:30px;
  text-align:center;
  padding:15px;
  background:#f1f9ff;
  color:#0047ab;
  border-radius:8px;
  font-size:0.9rem;
  box-sizing:border-box;
}
.footer a{ color:#0047ab; text-decoration:none; margin:0 5px; }
.footer a:hover{ text-decoration:underline; }

/* Images & gallery */
.banner img, .gallery-item img {
  width:100%;
  height:auto;
  object-fit:cover;
  display:block;
}

/* Fade animation */
@keyframes fadeIn{ from{opacity:0;} to{opacity:1;} }

/* =========================
   Team Members Section
   ========================= */
.team-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  gap: 20px;
  margin-top: 20px;
}
.team-card {
  background: #f1f9ff;
  border-radius: 12px;
  padding: 15px;
  text-align: center;
  width: 200px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  transition: transform 0.3s, box-shadow 0.3s;
}
.team-card img {
  width: 80px;
  height: 80px;
  border-radius: 50%;
  object-fit: cover;
  border: 2px solid #0047ab;
  margin-bottom: 10px;
}
.team-card h3 {
  font-size: 16px;
  color: #0047ab;
  margin-bottom: 5px;
}
.team-card p {
  font-size: 14px;
  color: #666;
}

/* Hover effect */
.team-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 16px rgba(0,0,0,0.15);
}

/* Responsive adjustments */
@media (max-width:768px) {
  .profile-left img { width:40px; height:40px; }
  .profile-left .name { font-size:14px; }
  .profile-left .role { font-size:12px; }
  .profile a.logout { padding:6px 10px; font-size:12px; }
  .team-card { width: 45%; }
}

@media (max-width:480px) {
  .profile-left img { width:35px; height:35px; }
  .profile-left .name { font-size:13px; }
  .profile-left .role { font-size:11px; }
  .profile a.logout { padding:5px 8px; font-size:11px; }
  .team-card { width: 100%; }
}

</style>
</head>
<body>
<div class="container">

<!-- Logo -->
<div class="logo">
<img src="assets/icc_logo.png" alt="ICC Logo" style="width:40px;">
</div>

<!-- Profile -->
<div class="profile">
  <div class="profile-left">
    <img src="<?= htmlspecialchars($photo_path) ?>" alt="Profile Photo">
    <span class="name"><?= htmlspecialchars($admin_name) ?></span>
    <span class="role"><?= htmlspecialchars($admin_role) ?></span>
  </div>
  <a href="logout.php" class="logout">Logout</a>
</div>

<h1>ICC Media Coordinator Dashboard</h1>

<!-- Statistics -->
<div class="stats">
<div class="stat-box">
Total Orders<br><strong><?= $total_orders ?></strong>
</div>
<div class="stat-box">
Total Revenue<br><strong><?= number_format($total_revenue) ?> UGX</strong>
</div>
<div class="stat-box">
Pending Mobile Money<br><strong><?= $pending_mobile ?></strong>
</div>
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
<div class="table-responsive">
<table>
<tr>
<th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Location</th>
<th>Size</th><th>Color</th><th>Quality</th><th>Price</th><th>Payment</th>
<th>Item Type</th><th>Date</th><th>Actions</th>
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
<td class="actions">
<a href="edit_order.php?id=<?= $order['id'] ?>">Edit</a> | 
<a href="delete_order.php?id=<?= $order['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>

<!-- Merchandise Catalog -->
<h2>Merchandise Catalog</h2>
<div class="table-responsive">
<table>
<tr>
<th>ID</th><th>Name</th><th>Type</th><th>Price</th><th>Quality</th><th>Actions</th>
</tr>
<?php foreach($catalog_items as $item): ?>
<tr>
<td><?= $item['id'] ?></td>
<td><?= htmlspecialchars($item['name']) ?></td>
<td><?= $item['type'] ?></td>
<td><?= number_format($item['price']) ?></td>
<td><?= htmlspecialchars($item['quality']) ?></td>
<td class="actions">
<a href="edit_merch.php?id=<?= $item['id'] ?>">Edit</a> | 
<a href="delete_merch.php?id=<?= $item['id'] ?>" onclick="return confirm('Are you sure?')">Delete</a>
</td>
</tr>
<?php endforeach; ?>
</table>
</div>
<button onclick="window.location.href='add_item.php'">Add New Item</button>

<!-- Our Team Section -->
<h2>Our Team</h2>
<div class="team-container">
<?php if(!empty($team_members)): ?>
    <?php foreach($team_members as $member): 
          $photo_file = $member['photo'] ?? 'default.jpg';
          $photo_path = 'assets/team_photos/' . $photo_file;
    ?>
      <div class="team-card">
        <img src="<?= htmlspecialchars($photo_path) ?>" alt="<?= htmlspecialchars($member['name']) ?>">
        <h3><?= htmlspecialchars($member['name']) ?></h3>
        <p><?= htmlspecialchars($member['role']) ?></p>
      </div>
    <?php endforeach; ?>
<?php else: ?>
    <p style="text-align:center; color:#666;">No team members found.</p>
<?php endif; ?>
</div>

<!-- Footer -->
<div class="footer">
    &copy; 2025 ICC Media. All rights reserved. <br>
    Follow us on 
    <a href="https://www.tiktok.com/@ishakacommunitychurch" target="_blank">Tiktok</a> | 
    <a href="https://www.instagram.com/ishaka_community_church/" target="_blank">Instagram</a> | 
    <a href="https://x.com/besttyson5" target="_blank">Twitter</a> | 
    <a href="https://chat.whatsapp.com/E4t7rbRuFJG3A27RkgmpAR?mode=ems_copy_t" target="_blank">WhatsApp</a>
</div>

</div>
</body>
</html>
