<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Round Face Hijab Style - HAYA COLLECTION</title>
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
<h1>Round Face Hijab Styles</h1>
<p>Learn how to style hijabs for round face shapes with elegance.</p>
</section>

<main class="container detail-section round-face-page">

<!-- Back Button -->
<a href="face-shape-hijab.php" class="back-btn">← Back to Face Shapes</a>

<!-- Vertical Video Section -->
<section class="video-section">
    <div class="video-container">
        <video controls autoplay muted loop playsinline>
            <source src="../v11.mp4" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</section>

<!-- Content Sections -->
<section class="content-sections">

    <!-- Styling Tips -->
    <article class="content-card">
        <h2>Styling Tips</h2>
        <ul>
            <li>Add height at the crown to elongate the face</li>
            <li>Create soft angles around the cheeks</li>
            <li>Avoid bulk around the jawline</li>
            <li>Use lightweight fabrics that drape beautifully</li>
        </ul>
    </article>

    <!-- Best Techniques -->
    <article class="content-card">
        <h2>Best Techniques</h2>
        <ul>
            <li>Almond wrap style for face framing</li>
            <li>Layered draping for volume control</li>
            <li>Asymmetric folding to add dimension</li>
            <li>Loose tucking near cheeks</li>
        </ul>
    </article>

    <!-- Recommended Fabrics -->
    <article class="content-card">
        <h2>Recommended Fabrics</h2>
        <ul>
            <li>Chiffon - lightweight and flowy</li>
            <li>Jersey - stretchy and comfortable</li>
            <li>Silk blend - elegant draping</li>
            <li>Crepe - holds shape well</li>
        </ul>
    </article>

    <!-- Knots to Avoid -->
    <article class="content-card">
        <h2>Knots to Avoid</h2>
        <ul>
            <li>Tight knots under the chin</li>
            <li>Bulk at the jawline</li>
            <li>Low-hanging folds that widen face</li>
            <li>Excessive pinning near cheeks</li>
        </ul>
    </article>

</section>

</main>

<!-- Scoped CSS for Round Face Page Only -->
<style>
.round-face-page {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.round-face-page .back-btn {
    display: inline-block;
    margin-bottom: 20px;
    padding: 10px 18px;
    background: #f3d6d6;
    color: #333;
    text-decoration: none;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
}

.round-face-page .back-btn:hover {
    background: #eac1c1;
}

/* Video Section */
.round-face-page .video-section {
    margin-bottom: 40px;
}

.round-face-page .video-container {
    width: 100%;
    max-width: 400px;
    margin: 0 auto;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 12px 40px rgba(130, 60, 73, 0.2);
    background: #000;
}

.round-face-page .video-container video {
    width: 100%;
    height: auto;
    display: block;
    aspect-ratio: 9 / 16;
    object-fit: contain;
}

/* Content Sections */
.round-face-page .content-sections {
    display: grid;
    gap: 24px;
}

/* Content Card */
.round-face-page .content-card {
    background: #ffffff;
    border-radius: 16px;
    padding: 24px;
    box-shadow: 0 6px 20px rgba(130, 60, 73, 0.1);
}

.round-face-page .content-card h2 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 22px;
    margin: 0 0 16px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f0dfe2;
}

.round-face-page .content-card ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.round-face-page .content-card ul li {
    padding: 10px 0;
    color: #5f5658;
    font-size: 15px;
    line-height: 1.5;
    border-bottom: 1px solid #f5f0f1;
    position: relative;
    padding-left: 24px;
}

.round-face-page .content-card ul li:last-child {
    border-bottom: none;
}

.round-face-page .content-card ul li::before {
    content: "•";
    color: #7A3E3E;
    font-size: 20px;
    position: absolute;
    left: 0;
    top: 8px;
}

/* Responsive */
@media (max-width: 768px) {
    .round-face-page .video-container {
        max-width: 300px;
    }
    .round-face-page .content-card {
        padding: 20px;
    }
    .round-face-page .content-card h2 {
        font-size: 20px;
    }
}
</style>

<?php include '../includes/global_footer.php'; ?>

</body>
</html>

