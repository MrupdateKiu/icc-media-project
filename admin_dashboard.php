<?php
session_start();
require_once __DIR__ . '/db_connect.php';

// Only allow logged-in non-coordinators
if (!isset($_SESSION['admin_logged_in']) || strtolower($_SESSION['admin_role']) === 'coordinator') {
    header("Location: login.php");
    exit;
}

// Profile data (from session)
$admin_name  = $_SESSION['admin_name'] ?? 'Admin';
$admin_role  = $_SESSION['admin_role'] ?? 'Admin';
$photo_file  = $_SESSION['admin_photo'] ?? 'default.jpg';

// Build local filesystem and public paths for images
$photo_path_full = __DIR__ . '/assets/profile_photos/' . $photo_file;
if (!file_exists($photo_path_full) || empty($photo_file)) {
    $photo_file = 'default.jpg';
}
$photo_path = 'assets/profile_photos/' . $photo_file;

// Fetch filters (safe defaults)
$filter_quality = $_GET['quality'] ?? '';
$filter_payment = $_GET['payment'] ?? '';
$filter_item    = $_GET['item'] ?? '';

// Fetch orders
$sql = "SELECT * FROM tshirt_orders WHERE 1=1";
$params = [];
if ($filter_quality) { $sql .= " AND quality = ?"; $params[] = $filter_quality; }
if ($filter_payment) { $sql .= " AND payment_option = ?"; $params[] = $filter_payment; }
if ($filter_item)    { $sql .= " AND item_type = ?"; $params[] = $filter_item; }

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Stats
$total_orders = count($orders);
$total_revenue = 0;
$pending_mobile = 0;
foreach ($orders as $order) {
    $total_revenue += (float)$order['price'];
    if ($order['payment_option'] === 'mobile_money') $pending_mobile++;
}

// Fetch catalog
$catalog_stmt = $pdo->query("SELECT * FROM merch_catalog ORDER BY id DESC");
$catalog_items = $catalog_stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch team from admins table (compact)
$team_stmt = $pdo->query("SELECT name, role, phone, photo FROM admins ORDER BY FIELD(role,'Coordinator') DESC, id ASC");
$team_members = $team_stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>ICC Media — Admin Dashboard</title>
<style>
/* ---------- Base (blue / white) ---------- */
:root{
  --brand-blue: #0047ab;
  --brand-light: #f1f9ff;
  --card-radius: 12px;
  --gap: 14px;
}
*{box-sizing:border-box}
body{
  font-family: Arial, sans-serif;
  background:#f5faff;
  margin:0;
  color:#0b1a2b;
  -webkit-font-smoothing:antialiased;
}
.container{
  max-width:1200px;
  margin:18px auto;
  padding:18px;
  background:#fff;
  border-radius:var(--card-radius);
  box-shadow:0 6px 20px rgba(0,0,0,0.06);
}

/* Header area (logo + profile row) */
.logo{ text-align:center; margin-bottom:8px; }
.header-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:12px;
  margin-bottom:10px;
}

/* Profile block (stacked name & role + photo) left, logout on right */
.profile-left{
  display:flex;
  align-items:center;
  gap:12px;
}
.profile-left img{
  width:56px;height:56px;border-radius:50%;object-fit:cover;border:2px solid var(--brand-blue);
}
.profile-info{
  display:flex;
  flex-direction:column;
  gap:2px;
}
.profile-info .name{ color:var(--brand-blue); font-weight:700; font-size:16px; }
.profile-info .role{ color:#666; font-size:13px; }

/* Logout button aligns to far right on same header row */
.logout-btn{
  padding:8px 12px;
  background:var(--brand-blue);
  color:white;
  border-radius:8px;
  text-decoration:none;
  font-weight:700;
  white-space:nowrap;
}

/* Title area */
h1{ text-align:center; color:var(--brand-blue); margin:8px 0 18px; font-size:20px; }

/* Stats */
.stats{
  display:flex;
  gap:var(--gap);
  justify-content:center;
  flex-wrap:wrap;
  margin-bottom:18px;
}
.stat-box{
  background:var(--brand-light);
  color:var(--brand-blue);
  border-radius:12px;
  padding:12px 14px;
  min-width:140px;
  text-align:center;
  font-weight:700;
}

/* Filter controls */
.filter{
  display:flex;
  gap:10px;
  align-items:center;
  justify-content:center;
  flex-wrap:wrap;
  margin-bottom:14px;
}
.filter label{ font-weight:700; font-size:14px; color:#333; }
.filter select, .filter input{ padding:8px 10px; border-radius:8px; border:1px solid #d0d7df; }

/* Table responsive container */
.table-responsive{
  width:100%;
  overflow:auto;
  -webkit-overflow-scrolling:touch;
  margin-bottom:18px;
  border-radius:10px;
  box-shadow:0 2px 8px rgba(0,0,0,0.03);
}
table{
  width:100%;
  border-collapse:collapse;
  min-width:720px;
}
th, td{
  border:1px solid #e0e4ea;
  padding:8px 10px;
  text-align:center;
  font-size:13px;
  white-space:nowrap;
}
th{ background:var(--brand-blue); color:#fff; font-weight:700; }

/* Actions (read-only admins) */
.actions a{ color:var(--brand-blue); text-decoration:none; margin:0 6px; font-weight:700; }
.actions a:hover{ text-decoration:underline; }

/* ---------- Team (very compact / minor) ---------- */
.team-section{ margin:10px 0; }
.team-title{ text-align:left; color:var(--brand-blue); font-weight:700; margin-bottom:6px; font-size:13px; }

.team-grid{
  display:grid;
  gap:8px;
  /* compact cards: each card min 88px, expand to available space */
  grid-template-columns: repeat(auto-fit, minmax(88px, 1fr));
  align-items:start;
}

/* very small team card */
.team-card{
  background:var(--brand-light);
  border-radius:8px;
  padding:6px;
  display:flex;
  flex-direction:column;
  align-items:center;
  gap:6px;
  text-align:center;
  font-weight:700;
  font-size:12px;
  line-height:1.05;
}

/* compact avatar */
.team-card img{
  width:44px;
  height:44px;
  border-radius:50%;
  object-fit:cover;
  border:2px solid var(--brand-blue);
}

/* smaller text lines */
.team-card .name{ font-size:12px; font-weight:700; color:var(--brand-blue); }
.team-card .role{ font-size:11px; font-weight:600; color:#234; }
.team-card .phone{ font-size:11px; font-weight:400; color:#333; }

/* responsive tweaks to keep them compact on phones */
@media (max-width:768px){
  .team-grid{ grid-template-columns: repeat(2, 1fr); gap:8px; }
  .team-card{ padding:6px; font-size:12px; }
  .team-card img{ width:40px; height:40px; }
}

@media (max-width:480px){
  .team-grid{ grid-template-columns: repeat(2, 1fr); gap:6px; }
  .team-card{ padding:6px; font-size:11px; }
  .team-card img{ width:36px; height:36px; }
  .team-card .name{ font-size:11px; }
  .team-card .role, .team-card .phone{ font-size:10.5px; }
}


/* Footer centered */
.footer{
  margin-top:18px;
  text-align:center;
  padding:14px;
  background:var(--brand-light);
  color:var(--brand-blue);
  border-radius:8px;
  font-size:0.9rem;
}

/* ---------- Responsive tweaks ---------- */
/* Medium screens */
@media (max-width:1024px){
  .container{ padding:16px; }
  table, th, td{ font-size:13px; }
  h1{ font-size:18px; }
}

/* Tablets and phones: center filter, shrink head sizes */
@media (max-width:768px){
  .header-row{ flex-direction:column; align-items:stretch; gap:10px; }
  .profile-left{ justify-content:flex-start; }
  .logout-btn{ align-self:flex-end; }
  .stats{ gap:10px; }
  .stat-box{ padding:10px; min-width:120px; font-size:13px; }
  .team-grid{ grid-template-columns: repeat(2, 1fr); } /* ensure 2 columns on small devices */
}

/* Small phones: tighter */
@media (max-width:480px){
  .profile-left img{ width:44px; height:44px; }
  .profile-info .name{ font-size:14px; }
  .profile-info .role{ font-size:12px; }
  .logout-btn{ padding:6px 10px; font-size:12px; }
  .team-card img{ width:56px; height:56px; }
  .team-card{ padding:8px; font-size:12px; }
  .team-grid{ grid-template-columns: repeat(2, 1fr); gap:8px; }
  table{ min-width:600px; }
}
</style>
</head>
<body>
<div class="container">

  <!-- Logo & header -->
  <div class="logo">
    <img src="assets/icc_logo.png" alt="ICC Logo" style="width:72px;">
  </div>

  <div class="header-row">
    <div class="profile-left" aria-label="profile">
      <img src="<?= htmlspecialchars($photo_path) ?>" alt="Profile Photo">
      <div class="profile-info">
        <span class="name"><?= htmlspecialchars($admin_name) ?></span>
        <span class="role"><?= htmlspecialchars($admin_role) ?></span>
      </div>
    </div>

    <a class="logout-btn" href="logout.php">Logout</a>
  </div>

  <h1>ICC Media Admin Dashboard</h1>

  <!-- Stats -->
  <div class="stats" role="region" aria-label="Statistics">
    <div class="stat-box">Total Orders<br><strong><?= $total_orders ?></strong></div>
    <div class="stat-box">Total Revenue<br><strong><?= number_format($total_revenue) ?> UGX</strong></div>
    <div class="stat-box">Pending Mobile Money<br><strong><?= $pending_mobile ?></strong></div>
  </div>

  <!-- Filters -->
  <form method="get" class="filter" role="search" aria-label="Filter orders">
    <label>Quality:
      <select name="quality">
        <option value="">All</option>
        <option value="35k-long" <?= ($filter_quality=='35k-long')?'selected':''; ?>>35k-long</option>
        <option value="25k-short" <?= ($filter_quality=='25k-short')?'selected':''; ?>>25k-short</option>
        <option value="15k-short" <?= ($filter_quality=='15k-short')?'selected':''; ?>>15k-short</option>
      </select>
    </label>

    <label>Payment:
      <select name="payment">
        <option value="">All</option>
        <option value="mobile_money" <?= ($filter_payment=='mobile_money')?'selected':''; ?>>Mobile Money</option>
        <option value="cash" <?= ($filter_payment=='cash')?'selected':''; ?>>Cash</option>
      </select>
    </label>

    <label>Item Type:
      <select name="item">
        <option value="">All</option>
        <option value="tshirt" <?= ($filter_item=='tshirt')?'selected':''; ?>>T-shirt</option>
        <option value="jumper" <?= ($filter_item=='jumper')?'selected':''; ?>>Jumper</option>
        <option value="cap" <?= ($filter_item=='cap')?'selected':''; ?>>Cap</option>
      </select>
    </label>

    <input type="submit" value="Filter" />
  </form>

  <!-- Orders table (read-only for Admin) -->
  <h2 style="text-align:center;color:var(--brand-blue);margin:8px 0 10px;">Orders (Read-Only)</h2>
  <div class="table-responsive" role="region" aria-label="Orders table">
    <table>
      <thead>
        <tr>
          <th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Location</th>
          <th>Size</th><th>Color</th><th>Quality</th><th>Price</th><th>Payment</th>
          <th>Item Type</th><th>Date</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($orders as $order): ?>
        <tr>
          <td><?= $order['id'] ?></td>
          <td><?= htmlspecialchars($order['name']) ?></td>
          <td><?= htmlspecialchars($order['email']) ?></td>
          <td><?= htmlspecialchars($order['phone']) ?></td>
          <td><?= htmlspecialchars($order['location']) ?></td>
          <td><?= htmlspecialchars($order['size']) ?></td>
          <td><?= htmlspecialchars($order['color']) ?></td>
          <td><?= htmlspecialchars($order['quality']) ?></td>
          <td><?= number_format($order['price']) ?></td>
          <td><?= htmlspecialchars($order['payment_option']) ?></td>
          <td><?= htmlspecialchars($order['item_type']) ?></td>
          <td><?= htmlspecialchars($order['order_date']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Merchandise catalog (read-only) -->
  <h2 style="text-align:center;color:var(--brand-blue);margin:8px 0 10px;">Merchandise Catalog (Read-Only)</h2>
  <div class="table-responsive" role="region" aria-label="Merchandise table">
    <table>
      <thead>
        <tr><th>ID</th><th>Name</th><th>Type</th><th>Price</th><th>Quality</th></tr>
      </thead>
      <tbody>
        <?php foreach ($catalog_items as $item): ?>
        <tr>
          <td><?= $item['id'] ?></td>
          <td><?= htmlspecialchars($item['name']) ?></td>
          <td><?= htmlspecialchars($item['type']) ?></td>
          <td><?= number_format($item['price']) ?></td>
          <td><?= htmlspecialchars($item['quality']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <!-- Team (compact) -->
  <div class="team-section" aria-label="Team">
    <div class="team-title">Our Team</div>
    <div class="team-grid">
      <?php if (!empty($team_members)): ?>
        <?php foreach ($team_members as $member): 
            $mphoto = $member['photo'] ?: 'default.jpg';
            $mphoto_path = 'assets/profile_photos/' . $mphoto;
            if (!file_exists(__DIR__ . '/' . $mphoto_path)) $mphoto_path = 'assets/profile_photos/default.jpg';
        ?>
        <div class="team-card">
          <img src="<?= htmlspecialchars($mphoto_path) ?>" alt="<?= htmlspecialchars($member['name']) ?>">
          <div class="name"><?= htmlspecialchars($member['name']) ?></div>
          <div class="role"><?= htmlspecialchars($member['role']) ?></div>
          <div class="phone"><?= htmlspecialchars($member['phone']) ?></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div style="text-align:center;color:#666;padding:10px;">No team members found.</div>
      <?php endif; ?>
    </div>
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
