<?php
require_once "db_connect.php";
session_start(); // start session at the very top

$message = "";

// -------------- Order submission with duplicate checks --------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    // sanitize + collect
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $size = trim($_POST['size'] ?? '');
    // capture typed other color when 'others' selected
    $color = ($_POST['color'] ?? '') === 'others' ? trim($_POST['other_color'] ?? '') : trim($_POST['color'] ?? '');
    $quality = trim($_POST['quality'] ?? '');
    $payment_option = trim($_POST['payment'] ?? '');
    $price = 0;

    // basic required validation
    if ($name === '' || $email === '' || $phone === '') {
        $_SESSION['order_message'] = "Please fill in your name, email and phone.";
        header("Location: index.php");
        exit();
    }

    // normalize email for comparison
    $email_normal = strtolower($email);
    // normalize phone to digits-only for comparison
    $phone_normal = preg_replace('/\D+/', '', $phone);

    // determine price
    if ($quality == '35k-long') $price = 35000;
    elseif ($quality == '25k-short') $price = 25000;
    elseif ($quality == '15k-short') $price = 15000;

    // Server-side duplicate check
    $dupSql = "
        SELECT COUNT(*) AS cnt
        FROM tshirt_orders
        WHERE LOWER(email) = ?
          OR LOWER(name) = ?
          OR REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(phone,' ',''),'-',''),'(',''),')',''),'+','') = ?
    ";
    $dupStmt = $pdo->prepare($dupSql);
    $dupStmt->execute([ $email_normal, strtolower($name), $phone_normal ]);
    $exists = (int) $dupStmt->fetchColumn();

    if ($exists > 0) {
        $_SESSION['order_message'] = "Duplicate detected — an order with the same name, email, or phone already exists.";
        header("Location: index.php");
        exit();
    }

    // Insert order
    $insert = $pdo->prepare("INSERT INTO tshirt_orders 
        (name,email,phone,location,size,color,quality,price,payment_option,item_type,order_date) 
        VALUES (?,?,?,?,?,?,?,?,?,?,NOW())");
    $insert->execute([
        $name,
        $email,
        $phone,
        $location,
        $size,
        $color,
        $quality,
        $price,
        $payment_option,
        'tshirt'
    ]);

    $_SESSION['order_message'] = "Thank you $name! Your order has been placed successfully.";
    header("Location: index.php");
    exit();
}

if (isset($_SESSION['order_message'])) {
    $message = $_SESSION['order_message'];
    unset($_SESSION['order_message']);
}

$finance_number = "+256708001819";
$finance_display_name = "Sharif Ssedume";

// fetch admins for contact block (safe fallback if table missing)
$admins = [];
try {
    $admin_stmt = $pdo->query("SELECT username, role, contact AS phone, photo FROM admins ORDER BY id ASC");
    $admins = $admin_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // ignore, show no contacts
}

// -------------- Social Media / Thumbnail meta --------------
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'];
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$baseUrl = $protocol . '://' . $host . $basePath;

$ogTitle = "Christus Natus T-Shirts - ICC Media";
$ogDescription = "Order your Christus Natus T-shirt today and support the ICC Media Ministry!";
$ogImage = $baseUrl . '/natus.jpeg'; // make sure this image exists publicly
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>Christus Natus T-Shirts - ICC Media</title>

<!-- Open Graph / Social preview meta tags -->
<meta property="og:title" content="<?= htmlspecialchars($ogTitle) ?>" />
<meta property="og:description" content="<?= htmlspecialchars($ogDescription) ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?= htmlspecialchars($baseUrl . '/' . basename($_SERVER['SCRIPT_NAME'])) ?>" />
<meta property="og:image" content="<?= htmlspecialchars($ogImage) ?>" />
<meta property="og:image:secure_url" content="<?= htmlspecialchars($ogImage) ?>" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?= htmlspecialchars($ogTitle) ?>" />
<meta name="twitter:description" content="<?= htmlspecialchars($ogDescription) ?>" />
<meta name="twitter:image" content="<?= htmlspecialchars($ogImage) ?>" />

<!-- Generic fallback -->
<link rel="image_src" href="<?= htmlspecialchars($ogImage) ?>" />

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<style>
/* ======= Keep all your existing CSS exactly as in your original code ======= */
:root{--brand-blue:#0047ab;--light-blue:#f1f9ff;--card-shadow:0 4px 12px rgba(0,0,0,0.12);}
body{margin:0;font-family:"Times New Roman", Times, serif;background:#f5faff;color:#111;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;}
.container{max-width:900px;margin:0 auto;padding:14px;box-sizing:border-box;}
.banner img{width:100%;border-radius:12px;animation:fadeIn 0.8s;display:block;object-fit:cover;}
.small-text{font-size:0.95rem;text-align:center;margin:8px 0;color:#333;}
.event-info{text-align:center;font-weight:600;color:var(--brand-blue);margin-bottom:6px;font-size:0.97rem;}
.countdown-wrap{text-align:center;margin:12px 0 6px;}
.countdown-label{font-family:"Times New Roman", Times, serif;font-weight:700;color:var(--brand-blue);font-size:0.95rem;margin-bottom:6px;}
.countdown{display:inline-block;padding:8px 12px;border-radius:10px;background:#fff;box-shadow:var(--card-shadow);font-weight:700;color:#d22;font-size:1.15rem;min-width:170px;text-align:center;}
.countdown-sub{font-size:0.8rem;color:#666;margin-top:4px;}
.view-gallery{display:block;text-align:center;margin:10px auto;padding:10px 18px;background:var(--brand-blue);color:white;border-radius:8px;text-decoration:none;font-weight:700;width:160px;}
.view-gallery:hover{background:#0066ff;}
.order-highlight{text-align:center;font-weight:700;font-size:1.05rem;background:#e6f0ff;padding:8px;border-radius:8px;margin:12px 0;}
.form-container{background:#fff;border-radius:12px;box-shadow:var(--card-shadow);padding:14px;margin-top:14px;}
form label{display:block;margin:8px 0 4px;font-weight:700;font-size:0.95rem;}
form input, form select, form textarea{width:100%;padding:8px;border-radius:6px;border:1px solid #d1d5db;margin-bottom:10px;font-family:inherit;font-size:0.95rem;box-sizing:border-box;}
form input[type="submit"]{width:100%;padding:10px;background:var(--brand-blue);color:white;border:none;border-radius:8px;font-weight:700;font-size:1rem;cursor:pointer;}
.message{color:green;text-align:center;margin:10px 0;font-weight:700;}
#formError{color:red;text-align:center;font-weight:bold;margin-bottom:10px;}
.admin-contact{margin-top:18px;text-align:center;}
.admin-contact h3{margin-bottom:8px;font-size:1rem;color:var(--brand-blue);}
.admin-contact .contacts{display:flex;flex-wrap:wrap;justify-content:center;gap:12px;margin-top:8px;}
.admin-contact .card{display:flex;flex-direction:column;align-items:center;width:86px;background:var(--light-blue);padding:8px;border-radius:10px;box-shadow:0 2px 6px rgba(0,0,0,0.06);}
.admin-contact .card img{width:48px;height:48px;border-radius:50%;object-fit:cover;border:2px solid var(--brand-blue);}
.admin-contact .card span{font-size:0.8rem;display:block;margin-top:6px;line-height:1.05;text-align:center;}
.footer{margin-top:20px;padding:14px;background:var(--light-blue);color:var(--brand-blue);border-radius:10px;font-size:0.92rem;text-align:center;line-height:1.5;box-shadow:0 2px 8px rgba(0,0,0,0.05);}
.footer a{color:var(--brand-blue);text-decoration:none;font-weight:600;margin:0 6px;}
.footer a:hover{text-decoration:underline;color:#0066ff;}
@keyframes fadeIn{from{opacity:0;}to{opacity:1;}}
@media (max-width:600px){.container{padding:12px;max-width:420px;}.countdown{font-size:1.0rem;min-width:140px;padding:8px;}.order-highlight{font-size:1.0rem;padding:8px;}.small-text{font-size:0.9rem;}form label{font-size:0.92rem;}form input, form select{font-size:0.92rem;padding:7px;}form input[type="submit"]{padding:9px;font-size:0.98rem;}.admin-contact .card{width:74px;padding:6px;}.admin-contact .card img{width:44px;height:44px;}.footer{font-size:0.82rem;padding:10px;}}
@media (min-width:601px) and (max-width:900px){.container{max-width:720px;padding:16px;}.countdown{font-size:1.2rem;min-width:160px;}.order-highlight{font-size:1.15rem;}}
@media (min-width:901px){.container{max-width:900px;padding:20px;}.countdown{font-size:1.5rem;min-width:190px;}}
/* small utility for visually-hidden (for accessibility if needed) */
.visually-hidden{position:absolute!important;height:1px;width:1px;overflow:hidden;clip:rect(1px,1px,1px,1px);white-space:nowrap;}
</style>
</head>
<body>
<div class="container">

<div class="banner"><img src="natus.jpeg" alt="Christus Natus Banner"></div>
<p class="small-text">Ishaka Community Church invites you to our Christmas production Happening On..</p>
<p class="event-info"><strong>28th November 2025 | 5 PM | ICC Auditorium</strong></p>

<div class="countdown-wrap" aria-live="polite">
    <div class="countdown-label"> </div>
    <div class="countdown" id="countdown">Loading...</div>
    <div class="countdown-sub" id="countdown-sub">until Christus Natus</div>
</div>

<p class="small-text" style="margin-top:6px;">We also have Christus Natus T-shirts available. Grab yours and support the ICC Media Ministry!</p>
<a href="gallery.php" class="view-gallery" aria-label="View T-shirts">View T-shirts</a>
<div class="order-highlight">GRAB YOURSELF ONE!</div>

<div class="form-container" id="order-form">
    <p id="formError"></p>
    <?php if($message) echo "<p class='message'>" . htmlspecialchars($message) . "</p>"; ?>
    <form method="post" novalidate>
        <input type="hidden" name="place_order" value="1">
        <label for="name">Enter Your Name:</label>
        <input id="name" type="text" name="name" required>
        <label for="email">Enter Your Email:</label>
        <input id="email" type="email" name="email" required>
        <label for="phone">Enter Your Phone Number:</label>
        <input id="phone" type="text" name="phone" required>
        <label for="location">Current Location:</label>
        <input id="location" type="text" name="location" required>

        <label for="size">Select T-Shirt Size:</label>
        <select id="size" name="size" required>
            <option value="S">S</option><option value="M">M</option><option value="L">L</option><option value="XL">XL</option>
        </select>

        <label for="colorSelect">Select T-Shirt Color:</label>
        <select id="colorSelect" name="color" required>
            <option value="maroon">Maroon</option>
            <option value="sky_blue">Sky Blue</option>
            <option value="navy_blue">Navy Blue</option>
            <option value="pink">Pink</option>
            <option value="others">Others</option>
        </select>
        <input type="text" name="other_color" id="otherColor" placeholder="Enter color" style="display:none;margin-top:6px;">

        <label for="quality">Select T-Shirt Quality:</label>
        <select id="quality" name="quality" required>
            <option value="35k-long">35k-long (Long Sleeve)</option>
            <option value="25k-short">25k-short (Short Sleeve)</option>
            <option value="15k-short">15k-short (Short Sleeve)</option>
        </select>

        <label for="paymentSelect">Choose Payment Option:</label>
        <select id="paymentSelect" name="payment" required>
            <option value="cash">Cash</option>
            <option value="mobile_money">Mobile Money</option>
        </select>
        <div id="financeInfo" style="display:none;margin:10px 0;padding:10px;background:#e0f0ff;border-left:4px solid var(--brand-blue);font-weight:700;text-align:center;"></div>
        <input type="submit" value="Place Order">
    </form>

    <div class="admin-contact" aria-label="Admin contacts">
        <h3>For more info, contact:</h3>
        <div class="contacts">
        <?php
        if (!empty($admins)) {
            foreach($admins as $admin) {
                $photo = htmlspecialchars($admin['photo'] ?: 'default.jpg');
                $username = htmlspecialchars($admin['username'] ?? '');
                $role = htmlspecialchars($admin['role'] ?? '');
                $phone = htmlspecialchars($admin['phone'] ?? '');
                $imgPath1 = 'uploads/profiles/' . $photo;
                $imgPath2 = 'assets/profile_photos/' . $photo;
                $img = file_exists($imgPath1) ? $imgPath1 : (file_exists($imgPath2) ? $imgPath2 : 'assets/profile_photos/default.jpg');
                echo '<div class="card">';
                echo '<img src="'.htmlspecialchars($img).'" alt="'.$username.'">';
                echo '<span>'.$username.'</span>';
                echo '<span style="color:var(--brand-blue);">'.$role.'</span>';
                echo '<span style="color:#333; font-size:0.78rem;">'.$phone.'</span>';
                echo '</div>';
            }
        } else {
            echo '<div style="font-size:0.95rem;color:#666;">No contacts available.</div>';
        }
        ?>
        </div>
    </div>
</div>

<div class="footer">
&copy; <?php echo date("Y"); ?> ICC Media. All rights reserved. <br>
Follow us on
<a href="https://www.tiktok.com/@ishakacommunitychurch" target="_blank" rel="noopener">Tiktok</a> |
<a href="https://www.instagram.com/ishaka_community_church/" target="_blank" rel="noopener">Instagram</a> |
<a href="https://x.com/besttyson5" target="_blank" rel="noopener">Twitter</a> |
<a href="https://chat.whatsapp.com/E4t7rbRuFJG3A27RkgmpAR?mode=ems_copy_t" target="_blank" rel="noopener">WhatsApp</a>
</div>
</div>

<script>
// ===== JS kept exactly as original =====
const eventTime = new Date("Nov 28, 2025 17:00:00").getTime();
const countdownEl = document.getElementById('countdown');
const countdownSub = document.getElementById('countdown-sub');
function updateCountdown(){
    const now = Date.now();
    const distance = eventTime - now;
    if(distance <= 0){countdownEl.textContent="GET READY, IT'S HAPPENING TODAY!"; countdownSub.textContent=""; return;}
    const days=Math.floor(distance/(1000*60*60*24));
    const hours=Math.floor((distance % (1000*60*60*24)) / (1000*60*60));
    const minutes=Math.floor((distance % (1000*60*60)) / (1000*60));
    const seconds=Math.floor((distance % (1000*60)) / 1000);
    countdownEl.textContent=`${days}d ${hours}h ${minutes}m ${seconds}s`;
    countdownSub.textContent=`${days} day(s) left`;
}
updateCountdown(); setInterval(updateCountdown,1000);

const colorSelect = document.getElementById('colorSelect');
const otherColor = document.getElementById('otherColor');
if (colorSelect) {
    colorSelect.addEventListener('change', function(){
        otherColor.style.display = this.value === 'others' ? 'block' : 'none';
    });
}

const paymentSelect = document.getElementById('paymentSelect');
const financeInfo = document.getElementById('financeInfo');
const financeNumber = <?php echo json_encode($finance_number); ?>;
const financeName = <?php echo json_encode($finance_display_name); ?>;
if (paymentSelect) {
    paymentSelect.addEventListener('change', function(){
        if (this.value === 'mobile_money') {
            financeInfo.style.display = 'block';
            financeInfo.innerHTML = `Send Money to <strong>${financeNumber}</strong> [${financeName}]`;
        } else {
            financeInfo.style.display = 'none';
            financeInfo.innerHTML = '';
        }
    });
}

function normalizePhone(phone){ return phone.replace(/[\s\-\(\)\+]/g,''); }
const form=document.querySelector('.form-container form');
const nameInput=document.getElementById('name');
const emailInput=document.getElementById('email');
const phoneInput=document.getElementById('phone');
const formError=document.getElementById('formError');

let existingOrders = [
<?php
try {
    $orders = $pdo->query("SELECT name,email,phone FROM tshirt_orders")->fetchAll(PDO::FETCH_ASSOC);
    $js_array = [];
    foreach($orders as $o){
        $n = strtolower(trim($o['name'] ?? ''));
        $e = strtolower(trim($o['email'] ?? ''));
        $p = preg_replace('/\D+/', '', $o['phone'] ?? '');
        $js_array[] = "{name:".json_encode($n).",email:".json_encode($e).",phone:".json_encode($p)."}";
    }
    echo implode(",\n",$js_array);
} catch (Exception $e) {}
?>
];

if (form) {
    form.addEventListener('submit', function(e){
        formError.textContent = '';
        const nameVal = nameInput.value.trim().toLowerCase();
        const emailVal = emailInput.value.trim().toLowerCase();
        const phoneVal = normalizePhone(phoneInput.value.trim());

        if (phoneInput.value.trim() !== phoneVal) {
            formError.textContent = "Please enter phone number without spaces, dashes, parentheses, or plus sign.";
            e.preventDefault();
            return;
        }

        for (let order of existingOrders) {
            if (order.name === nameVal) {
                formError.textContent = "Name already exists. Please use a different name.";
                e.preventDefault();
                return;
            }
            if (order.email === emailVal) {
                formError.textContent = "Email already exists. Please use a different email.";
                e.preventDefault();
                return;
            }
            if (order.phone === phoneVal) {
                formError.textContent = "Phone number already exists. Please use a different phone.";
                e.preventDefault();
                return;
            }
        }
    });
}
</script>
</body>
</html>
