<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Hijab Color Theory - HAYA COLLECTION</title>
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
<h1>Hijab Color Theory</h1>
<p>Find the perfect hijab colors for your skin tone.</p>
</section>

<!-- Back Button -->
<button onclick="history.back()" class="back-btn">← Back</button>

<main class="container detail-section color-theory-page">

<!-- Skin Tone Section -->
<div class="skin-tone-section">
    <h2>Choose Your Skin Tone</h2>
<div class="skin-cards">
        <a href="fair-skin.php" class="skin-card-link">
            <div class="skin-card">
                <img src="../skaff23.jpeg" alt="Fair Skin Tone">
                <p>Fair Skin</p>
            </div>
        </a>
        <a href="medium-skin.php" class="skin-card-link">
            <div class="skin-card">
                <img src="../skaff22.jpeg" alt="Medium Skin Tone">
                <p>Medium Skin</p>
            </div>
        </a>
        <a href="dark-skin.php" class="skin-card-link">
            <div class="skin-card">
                <img src="../skaff16.jpeg" alt="Dark Skin Tone">
                <p>Dark Skin</p>
            </div>
        </a>
    </div>
</div>

</main>

<!-- Scoped CSS for Hijab Color Theory Page Only -->
<style>
.color-theory-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.skin-tone-section {
    text-align: center;
    padding: 40px 20px;
}

.skin-tone-section h2 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 32px;
    margin: 0 0 30px;
}

.skin-cards {
    display: flex;
    justify-content: center;
    gap: 25px;
    flex-wrap: wrap;
}

.skin-card {
    background: #fff;
    border-radius: 12px;
    padding: 15px;
    width: 200px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    text-decoration: none;
}

.skin-card-link {
    text-decoration: none;
}

.skin-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.12);
}

.skin-card img {
    width: 100%;
    height: auto;
    border-radius: 10px;
    object-fit: contain;
}

.skin-card p {
    margin-top: 10px;
    font-weight: 600;
    color: #5f5658;
    font-size: 16px;
}

/* Back Button */
.color-theory-page .back-btn {
    padding: 8px 16px;
    border: none;
    background: #6c4ab6;
    color: white;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 20px;
}

.color-theory-page .back-btn:hover {
    background: #5a3a9a;
}

/* Responsive */
@media (max-width: 768px) {
    .skin-cards {
        flex-direction: column;
        align-items: center;
    }
    
    .skin-card {
        width: 100%;
        max-width: 250px;
    }
    
    .skin-tone-section h2 {
        font-size: 26px;
    }
}
</style>

<?php include '../includes/global_footer.php'; ?>

</body>
</html>

