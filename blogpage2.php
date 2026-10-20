<?php
// Simple PHP + HTML blog template (PHP 5.3 compatible)
ini_set('display_errors', 1);
error_reporting(E_ALL);

$success = false;
$errors = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_submit'])) {
    $name    = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email   = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';

    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    if ($name === '') $errors[] = 'Please enter your name.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email.';
    if ($message === '') $errors[] = 'Please write a message.';

    if (empty($errors)) {
        $entry = "Time: " . date('c') . "\nName: {$name}\nEmail: {$email}\nMessage: {$message}\n---\n";
        file_put_contents(__DIR__ . '/messages.txt', $entry, FILE_APPEND | LOCK_EX);
        $success = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Penelope — Pink Blog Template (PHP)</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700&family=Montserrat:wght@300;500;700&display=swap" rel="stylesheet">
<style>
:root {
  --pink:#f3dfe2;
  --dust:#e9cfd3;
  --accent:#d9b6bb;
  --muted:#9b8b8f;
  --card:#ffffff;
  --maxw:1100px;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Montserrat,system-ui,-apple-system,Segoe UI,Roboto,'Helvetica Neue',Arial; color:#3b3b3b; background:#fff;}
header{background:#fff; padding:18px 0; border-bottom:1px solid rgba(0,0,0,0.04)}
.container{width:94%; max-width:var(--maxw); margin:0 auto}
.topbar{display:flex; align-items:center; justify-content:space-between}
.brand{font-family:'Playfair Display',serif; font-size:34px; color:#b8778a; letter-spacing:1px}
nav ul{list-style:none; display:flex; gap:20px; font-size:14px}
nav a{color:var(--muted); text-decoration:none}
nav a:hover{color:#b8778a}

/* Hero/contact */
.hero{display:flex; gap:30px; align-items:flex-start; padding:40px 0; flex-wrap:wrap}
.hero .photo{flex:1}
.hero img{width:100%; box-shadow:0 6px 20px rgba(0,0,0,0.06); border:8px solid #fff}
.contact-card{flex:1; background:var(--card); padding:22px; box-shadow:0 6px 20px rgba(0,0,0,0.06); border-radius:6px}
.contact-card h2{font-family:'Playfair Display',serif; margin:0 0 8px; color:#7a4f5a}
.contact-card p{margin:0 0 12px; font-size:14px; color:var(--muted)}
.form-row{margin-bottom:10px}
input[type=text], input[type=email], textarea{width:100%; padding:10px 12px; border:1px solid #eee; font-size:14px; background:#fbfbfb; border-radius:4px}
input:focus, textarea:focus{outline:none;border-color:var(--accent)}
textarea{min-height:100px}
.btn{padding:10px 16px; border:0; background:var(--accent); color:#fff; cursor:pointer; border-radius:4px}
.btn:hover{background:#c59da3}

/* Products */
.products h3{font-family:'Playfair Display',serif; font-size:26px; margin-top:40px; color:#a87988}
.product-grid{display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:15px; margin-top:15px}
.product-card{cursor:pointer; text-align:center; background:#fff; border:2px solid #f1f1f1; padding:10px; transition:0.3s; border-radius:6px}
.product-card:hover{border-color:var(--accent); transform:translateY(-3px)}
.product-card img{max-width:100%; border-radius:4px}
.product-card p{margin:5px 0 0; font-size:14px; color:#7a4f5a}
.product-card span{display:block; font-size:13px; color:var(--muted); margin:4px 0}
.add-cart, .wishlist{padding:6px 10px; font-size:13px; border:none; margin:2px; cursor:pointer; border-radius:4px}
.add-cart{background:var(--accent); color:#fff}
.wishlist{background:#eee; color:#7a4f5a}
.add-cart:hover{background:#c59da3}
.wishlist:hover{background:#d9b6bb;color:#fff}

nav a {
  color: #823c49; /* Menu color */
  font-weight: 500;
}
.search-form {
  display: flex;
  align-items: center;
  gap: 5px;
}
.search-form input[type="text"] {
  padding: 6px 8px;
  font-size: 14px;
  border: 1px solid #ccc;
  border-radius: 3px;
}
.search-form button {
  padding: 6px 10px;
  font-size: 14px;
  background: #d9b6bb;
  border: none;
  color: white;
  cursor: pointer;
  border-radius: 3px;
}
.icon-btn {
  position: relative;
  text-decoration: none;
  font-size: 18px;
  color: #823c49;
}
.icon-btn::after {
  content: attr(data-count);
  position: absolute;
  top: -6px;
  right: -10px;
  background: #e63946;
  color: #fff;
  font-size: 10px;
  padding: 1px 5px;
  border-radius: 50%;
}


footer{background:linear-gradient(180deg,#f8ecef,#f6e9ea); padding:40px 0; margin-top:30px}
.footer-inner{display:flex; gap:20px; align-items:flex-start; flex-wrap:wrap}
.footer-left{flex:1}
.logo{font-family:'Playfair Display',serif; font-size:26px; color:#b87f8f}
.small{font-size:13px; color:var(--muted)}

@media (max-width:900px){
  .hero{flex-direction:column}
}
</style>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(function () {
  function scrollToSection(id) {
    $('html, body').animate({ scrollTop: $(id).offset().top }, 600);
  }
  $('.product-card[data-product]').click(function () {
    const product = $(this).data('product');
    $('.shopping-section').hide();
    const target = {
      "Skaff": "#shoppingSection",
      "Hijab Pins": "#shoppingSection2",
      "Hijab Cap": "#shoppingSection3",
      "Burkha": "#shoppingSection4"
    }[product];
    if (target) { scrollToSection(target); $(target).slideDown(); }
  });
});
</script>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<header>
  <div class="container topbar">
    <div class="brand">HAYA COLLECTION</div>
    <nav>
      <ul>
        <li><a href="homepage2.php">Home</a></li>
        <li><a href="shoppage2.php">About</a></li>
        <li><a href="blogpage2.php">Blog</a></li>
        <li>
          <form class="search-form" action="search.php" method="get">
            <input type="text" name="q" placeholder="Search...">
            <button type="submit">🔍</button>
          </form>
        </li>
        <li>
          <a href="cart.php" class="icon-btn" data-count="<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>">🛒</a>
        </li>
        <li>
          <a href="wishlist.php" class="icon-btn" data-count="<?php echo isset($_SESSION['wishlist']) ? array_sum($_SESSION['wishlist']) : 0; ?>">❤️</a>
        </li>
      </ul>
    </nav>
  </div>
</header>


<main class="container">
  <section class="hero">
    <div class="photo">
      <img src="blog1.jpg" alt="flatlay"/>
    </div>
    <aside class="contact-card">
      <h2>Say hello!</h2>
      <p>Wanna contact me? That's fantastic! Fill this form and I'll answer ASAP.</p>
      <?php if (!empty($errors)): ?>
        <div style="color:#b33; font-size:14px; margin-bottom:8px">
          <strong>Errors:</strong>
          <ul>
            <?php foreach($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <?php if ($success): ?>
        <div style="background:#e6f7ee; padding:10px; border-radius:3px; margin-bottom:8px; color:#2b7a3a">
          Thanks! Your message was received.
        </div>
      <?php endif; ?>
      <form method="post">
        <div class="form-row"><input type="text" name="name" placeholder="Name" value="<?php echo isset($name) ? htmlspecialchars($name) : ''; ?>"></div>
        <div class="form-row"><input type="email" name="email" placeholder="Email address" value="<?php echo isset($email) ? htmlspecialchars($email) : ''; ?>"></div>
        <div class="form-row"><textarea name="message" placeholder="Message"><?php echo isset($message) ? htmlspecialchars($message) : ''; ?></textarea></div>
        <div style="display:flex; gap:10px; align-items:center; justify-content:space-between; flex-wrap:wrap">
          <button class="btn" name="contact_submit" type="submit">Send</button>
          <div style="font-size:13px; color:var(--muted)">Or email: <strong style="color:#7a4f5a">contact@penelope.theme</strong></div>
        </div>
      </form>
    </aside>
  </section>

  <section class="products">
    <h3>New in our Online Store</h3>
    <div class="product-grid">
      <div class="product-card" data-product="warm skin"><img src="skin1.jpg" alt="skaff"><p>warm skin</p></div>
      <div class="product-card" data-product="miedum skin"><img src="skin2.jpg" alt="pins"><p>miedum skin</p></div>
      <div class="product-card" data-product="cool skin"><img src="skin3.jpg" alt="cap"><p>cool skin</p></div>
      <div class="product-card" data-product="dark skin"><img src="skin4.jpg" alt="burkha"><p>dark skin</p></div>
    </div>
  </section>
</main>

<!-- Skaff Collection -->
<section class="shopping-section" id="shoppingSection" style="display:none">
  <h3>Skaff Collection</h3>
  <div class="product-grid">
    <div class="product-card" data-product="Skaff"><img src="skaff1.jpg" alt=""><p>Skaff</p><span>$85.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Skaff"><img src="tone1.jpg" alt=""><p>Skaff</p><span>$85.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Skaff"><img src="tone2.jpg" alt=""><p>Skaff</p><span>$85.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Skaff"><img src="tone3.jpg" alt=""><p>Skaff</p><span>$85.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Skaff"><img src="tone4.jpg" alt=""><p>Skaff</p><span>$85.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Skaff"><img src="tone5.jpg" alt=""><p>Skaff</p><span>$85.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
  </div>
</section>

<!-- Hijab Pins -->
<section class="shopping-section" id="shoppingSection2" style="display:none">
  <h3>Hijab Pins Collection</h3>
  <div class="product-grid">
    <div class="product-card" data-product="Hijab Pins"><img src="tonelight1.jpg" alt=""><p>Hijab Pins</p><span>$45.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Pins"><img src="tonelight2.jpg" alt=""><p>Hijab Pins</p><span>$45.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Pins"><img src="tonelight3.jpg" alt=""><p>Hijab Pins</p><span>$45.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Pins"><img src="tonelight4.jpg" alt=""><p>Hijab Pins</p><span>$45.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Pins"><img src="tonelight5.jpg" alt=""><p>Hijab Pins</p><span>$45.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
  </div>
</section>

<!-- Hijab Cap -->
<section class="shopping-section" id="shoppingSection3" style="display:none">
  <h3>Hijab Cap Collection</h3>
  <div class="product-grid">
    <div class="product-card" data-product="Hijab Cap"><img src="tonemid1.jpg" alt=""><p>Hijab Cap</p><span>$175.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Cap"><img src="tonemid2.jpg" alt=""><p>Hijab Cap</p><span>$175.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Cap"><img src="tonemid3.jpg" alt=""><p>Hijab Cap</p><span>$175.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Cap"><img src="tonemid4.jpg" alt=""><p>Hijab Cap</p><span>$175.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Hijab Cap"><img src="tonemid5.jpg" alt=""><p>Hijab Cap</p><span>$175.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
  </div>
</section>

<!-- Burkha -->
<section class="shopping-section" id="shoppingSection4" style="display:none">
  <h3>Burkha Collection</h3>
  <div class="product-grid">
    <div class="product-card" data-product="Burkha"><img src="tonedark.jpg" alt=""><p>Burkha</p><span>$55.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Burkha"><img src="tonedark2.jpg" alt=""><p>Burkha</p><span>$55.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Burkha"><img src="tonedark3.jpg" alt=""><p>Burkha</p><span>$55.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Burkha"><img src="tonedark4.jpg" alt=""><p>Burkha</p><span>$55.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
    <div class="product-card" data-product="Burkha"><img src="tonedark5.jpg" alt=""><p>Burkha</p><span>$55.00 AUD</span><button class="add-cart">Add to Cart</button><button class="wishlist">♡ Wishlist</button></div>
  </div>
</section>

<footer>
  <div class="container footer-inner">
    <div class="footer-left">
      <div class="logo">Penelope Theme</div>
      <p class="small">Premium blogger template. Crafted with love and pretty pink tones.</p>
    </div>
    <div style="width:320px">
      <h4 style="margin-top:0; font-family:'Playfair Display'">About</h4>
      <p class="small">Welcome</p>
      </div>
    </div>
  </footer>

<?php include 'includes/global_footer.php'; ?>



