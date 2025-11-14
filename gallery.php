<?php
// Gallery page
$gallery_folder = "gallery_images"; // folder where your photos are stored

// Scan the folder for images
$images = glob($gallery_folder . "/*.{jpg,jpeg,png,gif,webp}", GLOB_BRACE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Christus Natus T-Shirts - Gallery</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">

<style>
:root{
  --brand-blue: #0047ab;
  --light-blue: #f1f9ff;
  --card-shadow: 0 6px 18px rgba(0,0,0,0.08);
}

/* Body uses Times New Roman (serif) as requested; headings use Poppins */
body{
  margin:0;
  font-family: "Times New Roman", Times, serif;
  background:#f5faff;
  color:#111;
}
.container{
  max-width:900px;
  margin:0 auto;
  padding:18px;
  box-sizing:border-box;
}

/* Heading */
h1{
  font-family: 'Poppins', sans-serif;
  color:var(--brand-blue);
  text-align:center;
  margin:10px 0 18px;
  font-size:1.4rem;
  letter-spacing:0.2px;
}

/* Gallery grid */
.gallery-grid{
  display:grid;
  gap:12px;
  grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
  align-items:start;
}

/* Card */
.gallery-item{
  position:relative;
  border-radius:12px;
  overflow:hidden;
  background:#fff;
  box-shadow:var(--card-shadow);
  height:260px;              /* uniform height */
  display:flex;
  align-items:stretch;
}

/* Ensure image fills card, cropping if necessary */
.gallery-item img{
  width:100%;
  height:100%;
  object-fit:cover;
  display:block;
  transition:transform .28s ease;
}

/* subtle zoom on hover */
.gallery-item:hover img{ transform:scale(1.03); }

/* Order button: centered above bottom, responsive size */
.order-btn{
  position:absolute;
  bottom:12px;
  left:50%;
  transform:translateX(-50%);
  padding:8px 12px;
  background:var(--brand-blue);
  color:#fff;
  border-radius:8px;
  text-decoration:none;
  font-family:'Poppins',sans-serif;
  font-weight:600;
  font-size:0.9rem;
  line-height:1;
  white-space:nowrap;
  box-shadow:0 6px 12px rgba(0,0,0,0.12);
  max-width:80%;
  text-align:center;
  box-sizing:border-box;
}

/* Make sure button doesn't expand to full width on very small screens */
.order-btn { min-width:96px; }

/* Hover tone */
.order-btn:hover{ background:#0066ff; }

/* Fallback text centering when no images */
.no-items { text-align:center; color:#666; margin:22px 0; font-size:0.98rem; }

/* Footer — same styling as index */
.footer{
  margin-top:22px;
  padding:14px;
  background:var(--light-blue);
  color:var(--brand-blue);
  border-radius:10px;
  font-size:0.92rem;
  text-align:center;
  line-height:1.5;
  box-shadow:0 2px 8px rgba(0,0,0,0.04);
}
.footer a{ color:var(--brand-blue); text-decoration:none; font-weight:600; margin:0 6px; }
.footer a:hover{ text-decoration:underline; color:#0066ff; }

/* Responsive adjustments */
@media (max-width:900px){
  .container{ padding:14px; max-width:720px; }
  .gallery-item{ height:220px; }
  .order-btn{ font-size:0.85rem; padding:7px 10px; min-width:86px; }
}

@media (max-width:600px){
  .container{ padding:12px; max-width:420px; }
  h1{ font-size:1.15rem; margin-bottom:12px; }
  .gallery-grid{ grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap:9px; }
  .gallery-item{ height:180px; border-radius:10px; }
  .order-btn{ font-size:0.78rem; padding:6px 10px; min-width:80px; bottom:10px; }
  .footer{ font-size:0.82rem; padding:10px; }
}

/* Prevent image dragging selection artifacts */
img{ -webkit-user-drag: none; user-drag: none; user-select:none; }
</style>
</head>
<body>

<div class="container">
    <h1>Christus Natus T-Shirts</h1>

    <div class="gallery-grid" role="list">
        <?php if (!empty($images)): ?>
            <?php foreach ($images as $img): 
                // sanitize output
                $src = htmlspecialchars($img);
            ?>
            <div class="gallery-item" role="listitem">
                <img src="<?= $src ?>" alt="T-shirt image">
                <a class="order-btn" href="index.php#order-form" aria-label="Order this t-shirt">Order Now</a>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-items">No T-shirts available yet.</div>
        <?php endif; ?>
    </div>

    <!-- Footer (consistent with index/dashboard) -->
    <div class="footer">
        &copy; <?php echo date("Y"); ?> ICC Media. All rights reserved. <br>
        Follow us on
        <a href="https://www.tiktok.com/@ishakacommunitychurch" target="_blank" rel="noopener">Tiktok</a> |
        <a href="https://www.instagram.com/ishaka_community_church/" target="_blank" rel="noopener">Instagram</a> |
        <a href="https://x.com/besttyson5" target="_blank" rel="noopener">Twitter</a> |
        <a href="https://chat.whatsapp.com/E4t7rbRuFJG3A27RkgmpAR?mode=ems_copy_t" target="_blank" rel="noopener">WhatsApp</a>
    </div>
</div>

</body>
</html>
