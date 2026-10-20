<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Fair Skin Tone Colour Guide - HAYA COLLECTION</title>
<link rel="icon" type="image/x-icon" href="../skaff1.jpg">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans:wght@400;600;700&display=swap">
<link rel="stylesheet" href="../assets/css/blog.css">
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<header class="header">
<h1><span class="brand">HAYA</span><small style="display:block;font-size:12px;letter-spacing:3px;font-family:Poppins,sans-serif;color:#7A3E3E;margin-top:-6px;">Collection</small></h1>
<div class="nav-right">
<nav>
<ul>
<li><a href="../homepage2.php">Home</a></li>
<li><a href="../shoppage2.php">Shop</a></li>
<li><a href="../blog.php">Blog</a></li>
<li><a href="../feedback.php">Feedback</a></li>
<?php
if(isset($_SESSION['usertype'])){
    if($_SESSION['usertype'] === 'admin'){
        echo '<li><a href="../myaccount2.php" class="icon-btn">👤</a></li>';
    } else {
        echo '<li><a href="../myaccount.php" class="icon-btn">👤</a></li>';
    }
} else {
    echo '<li><a href="../loginform.php" class="icon-btn">👤</a></li>';
}
?>
</ul>
</nav>
</div>
</header>

<section class="hero-banner">
<h1>Fair Skin Tone Colour Guide</h1>
<p>Best scarf colors for fair skin tones to enhance natural beauty.</p>
</section>

<main class="container detail-section fair-skin-page">

<!-- Back Button -->
<a href="hijab-color-theory.php" class="back-btn">← Back</a>

<!-- Best Colors Section -->
<section class="color-section">
    <h2>BEST COLORS PICK</h2>
    <div class="color-cards">
        <div class="color-card">
            <div class="color-box" style="background:#046307;"></div>
            <p>Emerald Green</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#9b111e;"></div>
            <p>Ruby Red</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#4169e1;"></div>
            <p>Royal Blue</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#e6e6fa;"></div>
            <p>Lavender</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#ffb6c1;"></div>
            <p>Soft Pink</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#800020;"></div>
            <p>Deep Burgundy</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#0047ab;"></div>
            <p>Cobalt Blue</p>
        </div>
    </div>
</section>

<!-- Colors to Avoid Section -->
<section class="color-section avoid-section">
    <h2>COLORS TO AVOID</h2>
    <div class="color-cards">
        <div class="color-card">
            <div class="color-box" style="background:#f5f5dc;"></div>
            <p>Beige</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#ffffe0;"></div>
            <p>Pale Yellow</p>
        </div>
        <div class="color-card">
            <div class="color-box" style="background:#d3d3d3;"></div>
            <p>Very Light Grey</p>
        </div>
    </div>
</section>

</main>

<!-- Scoped CSS for Fair Skin Page Only -->
<style>
.fair-skin-page {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
}

/* Back Button */
.fair-skin-page .back-btn {
    display: inline-block;
    margin: 20px;
    padding: 8px 16px;
    background: #f3d6d6;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
}

.fair-skin-page .back-btn:hover {
    background: #eac1c1;
}

/* Color Section */
.fair-skin-page .color-section {
    margin-bottom: 40px;
}

.fair-skin-page .color-section h2 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 28px;
    margin: 0 0 25px;
    text-align: center;
}

.fair-skin-page .color-cards {
    display: flex;
    justify-content: center;
    gap: 20px;
    flex-wrap: wrap;
}

.fair-skin-page .color-card {
    background: #fff;
    border-radius: 12px;
    padding: 12px;
    width: 100px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    text-align: center;
}

.fair-skin-page .color-box {
    width: 70px;
    height: 70px;
    border-radius: 10px;
    margin: 0 auto 10px;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
}

.fair-skin-page .color-card p {
    margin: 0;
    font-size: 13px;
    font-weight: 600;
    color: #5f5658;
    line-height: 1.3;
}

/* Avoid Section - slightly different styling */
.fair-skin-page .avoid-section .color-card {
    opacity: 0.7;
}

.fair-skin-page .avoid-section .color-card:hover {
    opacity: 1;
}

/* Responsive */
@media (max-width: 768px) {
    .fair-skin-page .color-cards {
        gap: 15px;
    }
    
    .fair-skin-page .color-card {
        width: 80px;
        padding: 10px;
    }
    
    .fair-skin-page .color-box {
        width: 55px;
        height: 55px;
    }
    
    .fair-skin-page .color-card p {
        font-size: 11px;
    }
    
    .fair-skin-page .color-section h2 {
        font-size: 24px;
    }
}
</style>

<?php include '../includes/global_footer.php'; ?>

</body>
</html>

