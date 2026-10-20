<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blog Page - HAYA COLLECTION</title>
<link rel="icon" type="image/x-icon" href="skaff1.jpg">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans:wght@400;600;700&display=swap">
<link rel="stylesheet" href="assets/css/blog.css">
<link rel="stylesheet" href="responsive.css">
</head>
<body>

<header class="header">
<h1><span class="brand">HAYA</span><small style="display:block;font-size:12px;letter-spacing:3px;font-family:Poppins,sans-serif;color:#7A3E3E;margin-top:-6px;">Collection</small></h1>
<div class="nav-right">
<nav>
<ul>
<li><a href="homepage2.php">Home</a></li>
<li><a href="shoppage2.php">Shop</a></li>
<li><a href="blogpage.php">Blog</a></li>
<li><a href="feedback.php">Feedback</a></li>
<?php
if(isset($_SESSION['usertype'])){
    if($_SESSION['usertype'] === 'admin'){
        echo '<li><a href="myaccount2.php" class="icon-btn">👤</a></li>';
    } else {
        echo '<li><a href="myaccount.php" class="icon-btn">👤</a></li>';
    }
} else {
    echo '<li><a href="loginform.php" class="icon-btn">👤</a></li>';
}
?>
</ul>
</nav>
</div>
</header>

<section class="hero-banner">
<h1>Blog Guides</h1>
<p>Open each guide for hijab hacks, face-shape styling, and color theory ideas.</p>
</section>

<main class="container">
<h2 class="section-title">Blog Concepts</h2>
<section class="blog-grid blog-page-section">
<article class="blog-card-item">
    <div class="blog-card-image">
        <img src="haks.jpeg" alt="6 Easy Hijab Hacks">
    </div>
    <div class="blog-card-content">
        <h3>6 Easy Hijab Hacks</h3>
        <p>Quick styling tricks to keep your hijab secure, neat, and comfortable from morning to evening.</p>
        <a class="btn-read" href="blog-detail/hijab-hacks.php">Read More &rarr;</a>
    </div>
</article>

<article class="blog-card-item">
    <div class="blog-card-image">
        <img src="tone7.jpeg" alt="Hijab for Different Face Shapes">
    </div>
    <div class="blog-card-content">
        <h3>Hijab for Different Face Shapes</h3>
        <p>Find flattering wraps and drapes for round, oval, square, and heart face structures.</p>
        <a class="btn-read" href="blog-detail/face-shape-hijab.php">Read More &rarr;</a>
    </div>
</article>

<article class="blog-card-item">
    <div class="blog-card-image-grid">
        <div class="image-grid-item"><img src="skaff19.jpeg" alt="Color Theory 1"></div>
        <div class="image-grid-item"><img src="skaff21.jpeg" alt="Color Theory 2"></div>
        <div class="image-grid-item"><img src="skaff20.jpeg" alt="Color Theory 3"></div>
    </div>
    <div class="blog-card-content">
        <h3>Hijab Color Theory</h3>
        <p>Choose shades that complement your undertone using a simple skin-tone based color framework.</p>
        <a class="btn-read" href="blog-detail/hijab-color-theory.php">Read More &rarr;</a>
    </div>
</article>
</section>
</main>

<!-- Scoped CSS for Blog Page Section Only -->
<style>
/* BLOG GRID - Stretch all cards to equal height */
.blog-page-section {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
    align-items: stretch;
}

/* BLOG CARD - Full height stretch */
.blog-page-section .blog-card-item {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(130, 60, 73, 0.14);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.blog-page-section .blog-card-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(130, 60, 73, 0.2);
}

/* MAIN IMAGE - Fixed height for consistent card sizes */
.blog-page-section .blog-card-image {
    width: 100%;
    height: 180px;
    min-height: 180px;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f5f5f5;
    flex-shrink: 0;
}

.blog-page-section .blog-card-image img {
    width: 100% !important;
    height: auto !important;
    max-height: 180px;
    object-fit: contain !important;
    display: block;
}

/* Override any global img styles within this section */
.blog-page-section img {
    width: 100%;
    height: auto;
    object-fit: contain;
}

/* COLOR THEORY GRID - Fixed height for consistency */
.blog-page-section .blog-card-image-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px;
    padding: 4px;
    background: #f0dfe2;
    height: 180px;
    min-height: 180px;
    flex-shrink: 0;
}

.blog-page-section .blog-card-image-grid .image-grid-item {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
}

.blog-page-section .blog-card-image-grid .image-grid-item:first-child {
    border-radius: 10px 0 0 0;
}

.blog-page-section .blog-card-image-grid .image-grid-item:last-child {
    border-radius: 0 10px 0 0;
}

.blog-page-section .blog-card-image-grid img {
    width: 100% !important;
    height: auto !important;
    max-height: 100%;
    object-fit: contain !important;
    display: block;
}

/* CONTENT - Flex to fill remaining space */
.blog-page-section .blog-card-content {
    padding: 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.blog-page-section .blog-card-content h3 {
    margin: 0 0 9px;
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 23px;
}

.blog-page-section .blog-card-content p {
    margin: 0 0 16px;
    color: #5f5658;
    line-height: 1.6;
    font-size: 14px;
    flex: 1;
}

/* BUTTON */
.blog-page-section .btn-read {
    display: inline-block;
    background: #7A3E3E;
    color: #fff;
    border-radius: 6px;
    padding: 10px 16px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    align-self: flex-start;
    transition: background 0.25s ease;
}

.blog-page-section .btn-read:hover {
    background: #5a2d2d;
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .blog-page-section {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .blog-page-section .blog-card-item:last-child {
        grid-column: span 2;
    }
}
@media (max-width: 768px) {
    .blog-page-section {
        grid-template-columns: 1fr;
    }
    .blog-page-section .blog-card-item:last-child {
        grid-column: span 1;
    }
    .blog-page-section .blog-card-image {
        height: 160px;
        min-height: 160px;
    }
    .blog-page-section .blog-card-image img {
        max-height: 160px;
    }
    .blog-page-section .blog-card-image-grid {
        height: 160px;
        min-height: 160px;
    }
}
</style>

<?php include 'includes/global_footer.php'; ?>

</body>
</html>



