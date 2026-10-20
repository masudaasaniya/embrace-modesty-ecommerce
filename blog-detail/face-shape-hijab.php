<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Face Shape Hijab Guide - HAYA COLLECTION</title>
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
<h1>Hijab for Different Face Shapes</h1>
<p>Simple wrapping guidance to balance features and improve comfort.</p>
</section>

<main class="container detail-section face-shape-page">

<!-- Back Button -->
<a href="../blogpage.php" class="back-btn">← Back to Blog</a>

<!-- Face Shape Grid -->
<section class="face-grid">
    <article class="face-card">
        <a href="round-face.php" class="face-image-link">
            <div class="face-image">
                <img src="../round.jpeg" alt="Round Face">
            </div>
        </a>
        <div class="face-info">
            <h3>Round Face</h3>
            <p>Soft layers and volume framing the cheeks.</p>
        </div>
    </article>

    <article class="face-card">
        <a href="diamond-face.php" class="face-image-link">
            <div class="face-image">
                <img src="../skin7.jpeg" alt="Diamond Face">
            </div>
        </a>
        <div class="face-info">
            <h3>Diamond Face</h3>
            <p>Balance cheekbones with soft drapes.</p>
        </div>
    </article>

    <article class="face-card">
        <a href="heart-face.php" class="face-image-link">
            <div class="face-image">
                <img src="../skaff18.jpeg" alt="Heart Face">
            </div>
        </a>
        <div class="face-info">
            <h3>Heart Face</h3>
            <p>Style that softens the forehead and highlights jawline.</p>
        </div>
    </article>

    <article class="face-card">
        <a href="square-face.php" class="face-image-link">
            <div class="face-image">
                <img src="../skin6.jpeg" alt="Square Face">
            </div>
        </a>
        <div class="face-info">
            <h3>Square Face</h3>
            <p>Flowing wraps that soften strong angles.</p>
        </div>
    </article>
</section>

</main>

<!-- Scoped CSS for Face Shape Page Only -->
<style>
.face-shape-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Back Button */
.face-shape-page .back-btn {
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

.face-shape-page .back-btn:hover {
    background: #eac1c1;
}

/* Page Header */
.face-shape-page .page-header {
    text-align: center;
    margin-bottom: 40px;
}

.face-shape-page .page-title {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 36px;
    margin: 0 0 15px;
}

.face-shape-page .page-subtitle {
    color: #666;
    font-size: 16px;
    max-width: 600px;
    margin: 0 auto;
    line-height: 1.6;
}

/* Face Grid */
.face-shape-page .face-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 30px;
}

/* Face Card */
.face-shape-page .face-card {
    background: #ffffff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(130, 60, 73, 0.12);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.face-shape-page .face-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 32px rgba(130, 60, 73, 0.18);
}

.face-shape-page .face-image-link {
    display: block;
    text-decoration: none;
}

/* Face Image */
.face-shape-page .face-image {
    width: 100%;
    height: 280px;
    overflow: hidden;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.face-shape-page .face-image img {
    width: 100%;
    height: auto;
    max-height: 100%;
    object-fit: contain;
    display: block;
}

/* Face Info */
.face-shape-page .face-info {
    padding: 20px;
    text-align: center;
}

.face-shape-page .face-info h3 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 22px;
    margin: 0 0 10px;
}

.face-shape-page .face-info p {
    color: #5f5658;
    font-size: 15px;
    line-height: 1.5;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .face-shape-page .face-grid {
        grid-template-columns: 1fr;
    }
    .face-shape-page .face-image {
        height: 220px;
    }
    .face-shape-page .page-title {
        font-size: 28px;
    }
    .face-shape-page .page-subtitle {
        font-size: 14px;
    }
}
</style>

<?php include '../includes/global_footer.php'; ?>

</body>
</html>



