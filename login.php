<?php
session_start();
require_once __DIR__ . '/db_connect.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username']));
    $password = trim($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM admins WHERE LOWER(username) = ?");
    $stmt->execute([$username]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_name']      = $admin['name'];
            $_SESSION['admin_role']      = $admin['role'];
            $_SESSION['admin_photo']     = $admin['photo'] ?? 'default.jpg';
            $_SESSION['admin_username']  = $admin['username'];
            session_regenerate_id(true);

            $role_lower = strtolower($admin['role']);
            if ($role_lower === 'coordinator') {
                header("Location: coordinator_dashboard.php");
            } else {
                header("Location: admin_dashboard.php");
            }
            exit;
        } else {
            $error = "Incorrect password";
        }
    } else {
        $error = "Username not found";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ICC Media Admin Login</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@500;600&display=swap" rel="stylesheet">

<style>
:root{
  --blue:#0047ab;
  --light:#f5faff;
}

body{
  margin:0;
  background:var(--light);
  display:flex;
  align-items:center;
  justify-content:center;
  min-height:100vh;
  font-family:"Times New Roman", Times, serif;
  flex-direction:column;
}

/* Login Card */
.container{
  background:white;
  padding:32px 28px;
  border-radius:16px;
  box-shadow:0 6px 16px rgba(0,0,0,0.1);
  width:90%;
  max-width:380px;
  box-sizing:border-box;
  text-align:center;
}

h1{
  font-family:'Poppins', sans-serif;
  color:var(--blue);
  margin:0 0 20px;
  font-size:1.6rem;
  letter-spacing:0.5px;
}

input[type=text],input[type=password]{
  width:100%;
  padding:12px;
  margin:10px 0 20px;
  border:1px solid #ccc;
  border-radius:8px;
  font-size:16px;
  font-family:"Times New Roman", Times, serif;
  box-sizing:border-box;
}

input[type=submit]{
  width:100%;
  padding:12px;
  background:var(--blue);
  color:#fff;
  border:none;
  border-radius:8px;
  cursor:pointer;
  font-weight:bold;
  font-size:16px;
  font-family:'Poppins',sans-serif;
  transition:0.3s ease;
}
input[type=submit]:hover{ background:#0066ff; }

.error{
  color:red;
  margin-bottom:10px;
  font-weight:bold;
  font-size:0.95rem;
  font-family:'Poppins',sans-serif;
}

/* Footer */
.footer{
  text-align:center;
  margin-top:25px;
  padding:12px;
  background:#f1f9ff;
  color:#0047ab;
  border-radius:8px;
  font-size:0.9rem;
  width:100%;
  max-width:400px;
  box-shadow:0 2px 8px rgba(0,0,0,0.05);
  font-family:'Poppins',sans-serif;
  box-sizing:border-box;
}
.footer a{
  color:#0047ab;
  text-decoration:none;
  margin:0 6px;
  font-weight:600;
}
.footer a:hover{ text-decoration:underline; color:#0066ff; }

/* Mobile adjustments */
@media(max-width:480px){
  .container{ padding:24px 20px; }
  h1{ font-size:1.4rem; }
  .footer{ font-size:0.82rem; padding:10px; }
}
</style>
</head>
<body>

<div class="container">
    <h1>ICC Media Admin Login</h1>
    <?php if($error): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="post">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <input type="submit" value="Login">
    </form>
</div>

<!-- Footer -->
<div class="footer">
    &copy; <?php echo date("Y"); ?> ICC Media. All rights reserved.<br>
    Follow us on 
    <a href="https://www.tiktok.com/@ishakacommunitychurch" target="_blank">Tiktok</a> |
    <a href="https://www.instagram.com/ishaka_community_church/" target="_blank">Instagram</a> |
    <a href="https://x.com/besttyson5" target="_blank">Twitter</a> |
    <a href="https://chat.whatsapp.com/E4t7rbRuFJG3A27RkgmpAR?mode=ems_copy_t" target="_blank">WhatsApp</a>
</div>

</body>
</html>
