<?php
session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>WRAPPERS - Sustainable Hijab</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Font Awesome Icons CDN -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    /* --- RESET & BASE --- */
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
      font-family:'Segoe UI',sans-serif;
      line-height:1.6;
      color:#222;
      background:#fff;
    }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }

    /* --- HEADER --- */
    .header { display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; background: linear-gradient(to bottom, #f8ecef, #f6e9ea); }
    .topbar { display:flex; justify-content:space-between; align-items:center; max-width:1200px; margin:0 auto; }
    .brand h1 { font-size:32px; font-weight:700; letter-spacing:1px; font-family:'Playfair Display',serif; margin:0; }
    .brand h1 span { color:#823c49; }
    nav ul { display:flex; gap:25px; list-style:none; margin:0; padding:0; align-items:center; }
    nav a { color:#823c49; font-size:16px; font-weight:700; display:flex; align-items:center; gap:6px; transition:color 0.2s ease-in-out; }

    /* --- HERO / PREMIUM BANNER --- */
    .hero { 
      position: relative;
      width: 100%;
      height: 550px;
      min-height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #f8ecef;
      overflow: hidden;
    }
    
    /* Background Image */
    .hero-bg {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url('homp.jpeg');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      z-index: 1;
    }
    
    /* Dark Overlay */
    .hero-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.3) 0%,
        rgba(0, 0, 0, 0.5) 50%,
        rgba(0, 0, 0, 0.4) 100%
      );
      z-index: 2;
    }
    
    /* Hero Content */
    .hero-content {
      position: relative;
      z-index: 3;
      text-align: center;
      max-width: 800px;
      padding: 20px;
      color: #fff;
    }
    
    .hero-small-heading {
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 4px;
      text-transform: uppercase;
      margin-bottom: 15px;
      color: rgba(255, 255, 255, 0.9);
    }
    
    .hero-main-heading {
      font-size: 52px;
      font-weight: 700;
      line-height: 1.2;
      margin-bottom: 20px;
      color: #fff;
      text-shadow: 2px 2px 10px rgba(0, 0, 0, 0.3);
    }
    
    .hero-description {
      font-size: 18px;
      line-height: 1.6;
      margin-bottom: 35px;
      color: rgba(255, 255, 255, 0.9);
      max-width: 600px;
      margin-left: auto;
      margin-right: auto;
    }
    
    /* CTA Button */
    .hero-btn {
      display: inline-block;
      padding: 16px 42px;
      font-size: 14px;
      font-weight: 600;
      letter-spacing: 2px;
      text-transform: uppercase;
      color: #fff;
      background: linear-gradient(135deg, #823c49 0%, #a64d5c 100%);
      border: 2px solid #823c49;
      border-radius: 4px;
      text-decoration: none;
      transition: all 0.35s ease;
      cursor: pointer;
    }
    
    .hero-btn:hover {
      background: transparent;
      border-color: #fff;
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
    }
    
    /* Banner Container (for carousel fallback) */
    .banner-container {
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 30px;
    }
    .hero .home-banner {
      width: 100%;
      max-width: 1100px;
      height: 300px;
      object-fit: cover;
      object-position: center;
      display: block;
      margin: 0 auto;
      border-radius: 12px;
      box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
    }
    .carousel-item .home-banner {
      width: 100%;
      max-width: 1100px;
      height: 300px;
    }
    .carousel {
      max-width: 1100px;
      margin: 0 auto;
    }
    .carousel-inner {
      border-radius: 12px;
      overflow: hidden;
    }

    /* --- SECTIONS --- */
    .section { padding:40px 20px; text-align:center; }
    .section h2 { font-size:24px; margin-bottom:20px; }
    .grid-2 { display:flex; justify-content:center; flex-wrap:wrap; gap:20px; }
    .grid-2 .box { flex:1 1 250px; text-align:center; }
    .grid-2 .box img { width:250px; height:200px; object-fit:cover; margin:0 auto; }
    .grid-2 .box h3 { margin-top:10px; font-size:18px; font-weight:500; }

    /* --- INSTAGRAM --- */
    .insta-feed { background: linear-gradient(to bottom,#f8ecef,#f6e9ea); padding:40px 20px; }
    .insta-gallery { display:flex; flex-wrap:wrap; justify-content:center; gap:10px; }
    .insta-gallery img { width:80px; height:80px; object-fit:cover; }

    /* --- FOOTER --- */
    footer { background: linear-gradient(to bottom,#f8ecef,#f6e9ea); padding:40px 0; margin-top:50px; }
    .footer-inner { display:flex; flex-wrap:wrap; gap:20px; max-width:1200px; margin:0 auto; }
    .footer-left .logo { font-size:24px; color:#b8778a; font-family:'Playfair Display',serif; }
    .small { font-size:13px; color:#8f8084; }

    /* --- RESPONSIVE --- */
    @media (max-width: 992px) {
      .hero { height: 480px; }
      .hero-main-heading { font-size: 42px; }
      .hero-description { font-size: 16px; }
    }
    
    @media (max-width:768px) {
      .grid-2, .footer-inner { flex-direction: column; align-items: center; }
      .grid-2 .box img { width: 200px; height: 160px; }
      nav ul { flex-direction: column; gap: 12px; margin-top: 10px; }
      
      /* Hero Responsive */
      .hero {
        height: 400px;
        min-height: 350px;
      }
      .hero-small-heading {
        font-size: 12px;
        letter-spacing: 3px;
      }
      .hero-main-heading {
        font-size: 32px;
      }
      .hero-description {
        font-size: 15px;
        margin-bottom: 25px;
      }
      .hero-btn {
        padding: 14px 32px;
        font-size: 13px;
      }
      
      .hero .home-banner, .carousel-item .home-banner { 
        max-width: 100%; 
        height: 220px; 
      }
      .carousel {
        max-width: 100%;
      }
      .banner-container {
        padding: 0 15px;
      }
    }
    @media (max-width:480px) {
      .hero { 
        height: 350px; 
        min-height: 300px;
      }
      .hero-main-heading {
        font-size: 26px;
      }
      .hero-description {
        font-size: 14px;
      }
      .hero-btn {
        padding: 12px 28px;
        font-size: 12px;
      }
      
      .hero .home-banner, .carousel-item .home-banner { 
        height: 180px; 
      }
      .hero {
        padding: 15px 10px;
      }
      .banner-container {
        padding: 0 10px;
      }
    }
    
    /* --- HOMEPAGE BANNER --- */
    .homepage-banner {
        text-align: center;
        margin: 40px 0;
        padding: 0 20px;
    }
    .banner-title {
        font-size: 28px;
        font-weight: 600;
        color: #823c49;
        margin-bottom: 20px;
        font-family: 'Playfair Display', serif;
    }
    .homepage-banner-image {
        max-width: 420px;
        width: 100%;
        height: auto;
        display: block;
        margin: 20px auto;
        border-radius: 10px;
    }
    .homepage-banner img {
        width: 60%;
        max-width: 700px;
        height: auto;
        border-radius: 10px;
        margin: 0 auto;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }
    @media (max-width: 768px) {
        .homepage-banner img {
            width: 80%;
        }
        .banner-title {
            font-size: 24px;
        }
    }
    @media (max-width: 480px) {
        .homepage-banner {
            margin: 20px 0;
        }
        .homepage-banner img {
            width: 95%;
            border-radius: 8px;
        }
        .banner-title {
            font-size: 20px;
        }
    }

    /* Banner Description */
    .banner-description {
        font-size: 14px;
        color: #555;
        margin-top: 10px;
        text-align: center;
        max-width: 700px;
        margin-left: auto;
        margin-right: auto;
        padding: 0 20px;
        line-height: 1.6;
    }
    
    @media (max-width: 768px) {
        .banner-description {
            font-size: 13px;
            padding: 0 15px;
        }
    }
  </style>
<link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- HEADER -->
  <header>
    <div class="topbar">
      <div class="brand">
      <h1><span>HAYA</span><small style="display:block;font-size:12px;letter-spacing:3px;font-family:Poppins,sans-serif;color:#7A3E3E;margin-top:-6px;">Collection</small></h1>
      </div>
      <nav>
        <ul>
          <li><a href="homepage2_final_fixed.php">Home</a></li>
          <li><a href="shoppage2.php">Shop</a></li>
          <li><a href="blogpage.php">Blog</a></li>
          <li><a href="feedback.php">Feedback</a></li>
          <li>
            <?php
              // --- ACCOUNT ICON LOGIC ---
              if(isset($_SESSION['usertype'])) {
                  if($_SESSION['usertype'] === 'admin') {
echo '<a href="myaccount2.php"><i class="fa-solid fa-user"></i></a>';
                  } else {
echo '<a href="myaccount.php"><i class="fa-solid fa-user"></i></a>';
                  }
              } else {
echo '<a href="loginform.php"><i class="fa-solid fa-user"></i></a>';
              }
            ?>
          </li>
        </ul>
      </nav>
    </div>
  </header>

  <!-- HERO / PREMIUM BANNER -->
  <section class="hero">
    <!-- Background Image -->
    <div class="hero-bg"></div>
    
    <!-- Dark Overlay -->
    <div class="hero-overlay"></div>
    
    <!-- Hero Content -->
    <div class="hero-content">
      <p class="hero-small-heading">Elegance Redefined</p>
      <h1 class="hero-main-heading">Modest Fashion, Premium Experience</h1>
      <p class="hero-description">Discover refined modest fashion for everyday elegance, comfort, and confidence.</p>
      <a href="shoppage2.php" class="hero-btn">View Collection</a>
    </div>
  </section>

  <!-- NEW ARRIVALS & COLLECTION -->
  <section class="section">
    <div class="grid-2">
      <a href="shoppage2.php">
        <div class="box">
          <img src="new-arrivals.jpg" alt="New Arrivals">
          <h3>NEW ARRIVALS</h3>
        </div>
      </a>
      <a href="shoppage2.php">
        <div class="box">
          <img src="collaction.jpg" alt="Shop by Collection">
          <h3>SHOP BY COLLECTION</h3>
        </div>
      </a>
    </div>
  </section>

  <!-- ACTIVE BANNER DISPLAY - FIXED -->
  <?php
  // Display latest active banner from database - JS syntax error fixed
  $bannerConn = mysqli_connect("localhost", "root", "", "23bca090");
  if ($bannerConn) {
      $bannerConn->set_charset("utf8mb4");
      $bannerQuery = mysqli_query($bannerConn, "SELECT * FROM banners WHERE status = 'active' ORDER BY id DESC LIMIT 1");
      if ($bannerQuery && mysqli_num_rows($bannerQuery) > 0) {
          $bannerRow = mysqli_fetch_assoc($bannerQuery);
          if (!empty($bannerRow['image'])) {
  ?>
  <div class="homepage-banner">
      <?php if (!empty($bannerRow['title'])): ?>
        <h3 class="banner-title"><?php echo htmlspecialchars($bannerRow['title']); ?></h3>
      <?php endif; ?>
      <?php if (!empty($bannerRow['link_url'])): ?>
      <a href="<?php echo htmlspecialchars($bannerRow['link_url']); ?>">
          <img 
            src="admin/uploads/banners/<?php echo htmlspecialchars($bannerRow['image']); ?>"
            class="homepage-banner-image"
            alt="<?php echo htmlspecialchars($bannerRow['title'] ?? ''); ?>">
      </a>
      <?php else: ?>
          <img 
            src="admin/uploads/banners/<?php echo htmlspecialchars($bannerRow['image']); ?>"
            class="homepage-banner-image"
            alt="<?php echo htmlspecialchars($bannerRow['title'] ?? ''); ?>">
      <?php endif; ?>
      <?php if(!empty($bannerRow['description'])): ?>
        <p class="banner-description"><?php echo nl2br(htmlspecialchars($bannerRow['description'])); ?></p>
      <?php endif; ?>
  </div>
  <?php
          }
      } else {
          echo '<p>No active banners available.</p>';
      }
      mysqli_close($bannerConn);
  } else {
      echo '<p>Banner load error. Please check database connection.</p>';
  }
  ?>

<?php include 'includes/instagram_section.php'; ?>
<?php include 'includes/global_footer.php'; ?>

<!-- Bootstrap JS (no carousel JS, no syntax errors possible) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
