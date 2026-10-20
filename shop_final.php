<?php 
session_start();
include("connect.php");

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id]++;
    } else {
        $_SESSION['cart'][$product_id] = 1;
    }
    header("Location: shop_final.php");
    exit;
}

if (!isset($_SESSION['wishlist'])) {
    $_SESSION['wishlist'] = array();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>Ella Maroon</title>
<link rel="icon" type="image/x-icon" href="skaff1.jpg">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<style>
body { margin: 0; font-family: 'Open Sans', sans-serif; background: #fff; color: #333; }
.header { display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; background: linear-gradient(to bottom, #f8ecef, #f6e9ea); }
.brand { color: #823c49; font-family: 'Playfair Display', serif; font-size: 34px; font-weight: 700; letter-spacing: 1px; }
.nav-right { display: flex; align-items: center; gap: 30px; }
nav ul { display: flex; list-style: none; gap: 25px; margin: 0; padding: 0; }
nav ul li a { text-decoration: none; color: #823c49; font-weight: bold; font-size: 16px; }
.nav-actions { display: flex; align-items: center; gap: 15px; }
.search-input { padding: 8px; border: 1px solid #ccc; border-radius: 5px; font-size: 14px; }
#searchBtn { padding: 8px 12px; background: #823c49; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
.icon-btn { font-size: 20px; position: relative; cursor: pointer; }
.icon-btn::after { content: attr(data-count); position: absolute; top: -5px; right: -10px; background: #823c49; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 12px; }
.hero { background: url('upper.jpg') center/cover no-repeat; padding: 100px 20px; text-align: center; position: relative; }
.hero-content { background: rgba(255,255,255,0.9); display: inline-block; padding: 30px; border-radius: 8px; }
.hero h2 { font-family: 'Playfair Display', serif; font-size: 32px; margin: 0; }
.btn { display: inline-block; margin-top: 15px; padding: 10px 25px; background: #823c49; color: #fff; text-decoration: none; font-weight: bold; border-radius: 5px; }
.collection-banner { background: #823c49; color: #fff; text-align: center; padding: 15px; font-size: 18px; font-weight: bold; }
.products { padding: 40px 20px; text-align: center; }
.product-grid { display: flex; justify-content: center; flex-wrap: wrap; gap: 30px; margin-top: 20px; }
.product-card { width: 200px; cursor: pointer; text-align: center; background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: transform 0.3s ease; }
.product-card:hover { transform: translateY(-5px); }
.product-card img { width: 100%; border-radius: 5px; height: 200px; object-fit: cover; }
.product-card p { margin: 10px 0 5px; font-weight: bold; font-size: 15px; color: #333; }
.product-card span { display: block; margin-bottom: 10px; font-weight: bold; color: #823c49; }
.add-to-cart-btn { display: block; width: 100%; padding: 8px; margin-top: 5px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; background: #823c49; color: #fff; text-decoration: none; line-height: 36px; }
.btn-buy { background: #28a745 !important; }
footer { background: linear-gradient(to bottom, #f8ecef, #f6e9ea); padding: 40px 20px; margin-top: 50px; color: #823c49; font-family: 'Open Sans', sans-serif; }
.footer-inner { display: flex; flex-wrap: wrap; gap: 20px; max-width: 1200px; margin: 0 auto; }
.footer-left, .footer-inner > div { flex: 1; min-width: 250px; }
.footer-left .logo { font-size: 24px; font-family: 'Playfair Display', serif; font-weight: 700; color: #b8778a; margin-bottom: 10px; }
.footer-left p, .footer-inner > div p { font-size: 13px; color: #8f8084; line-height: 1.5; }
.footer-links { list-style: none; padding: 0; margin: 0; }
.footer-links li { margin-bottom: 8px; }
.footer-links li a { text-decoration: none; color: #823c49; font-weight: 500; }
.footer-bottom { text-align: center; margin-top: 25px; font-size: 12px; color: #8f8084; }
@media (max-width: 768px) {
.footer-inner { flex-direction: column; text-align: center; }
.footer-left, .footer-inner > div { margin-bottom: 20px; }
}
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<header class="header">
<h1><span class="brand">HAYA</span><small style="display:block;font-size:12px;letter-spacing:3px;font-family:Poppins,sans-serif;color:#7A3E3E;margin-top:-6px;">Collection</small></h1>
<div class="nav-right">
<nav>
<ul>
<li><a href="homepage2.php">Home</a></li>
<li><a href="shop_final.php">Shop</a></li>
<li><a href="blogpage.php">Blog</a></li>
</ul>
</nav>
<div class="nav-actions">
<input type="text" class="search-input" id="searchInput" placeholder="Search...">
<button id="searchBtn">Search</button>
<a href="cart.php" class="icon-btn" id="cartIcon" data-count="<?php echo isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0; ?>">🛒</a>
<a href="wishlist.php" class="icon-btn" id="wishlistIcon" data-count="<?php echo isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0; ?>">❤️</a>
<?php
if(isset($_SESSION['usertype'])){
    if($_SESSION['usertype'] === 'admin'){
        echo '<a href="myaccount2.php" class="icon-btn">👤</a>';
    } else {
        echo '<a href="myaccount.php" class="icon-btn">👤</a>';
    }
} else {
    echo '<a href="loginform.php" class="icon-btn">👤</a>';
}
?>
</div>
</div>
</header>

<section class="collection-banner">SHOP OUR NEW ELEGANT COLLECTION</section>

<section class="products">
<h3>Categories</h3>
<div class="product-grid">
<div class="product-card" data-category="all"><a href="shop_final.php?cat=all"><img src="new-arrivals.jpg" alt="all"></a><p>All</p></div>
<div class="product-card" data-category="Skaff"><a href="shop_final.php?cat=Scarf"><img src="skaff1.jpg" alt="skaff"></a><p>Scarf</p></div>
<div class="product-card" data-category="Cap"><a href="shop_final.php?cat=Cap"><img src="cap1.jpg" alt="cap"></a><p>Cap</p></div>
<div class="product-card" data-category="Pin"><a href="shop_final.php?cat=Pin"><img src="pin2.jpg" alt="pin"></a><p>Pin</p></div>
<div class="product-card" data-category="Burkha"><a href="shop_final.php?cat=Burkha"><img src="burkha13.jpg" alt="burkha"></a><p>Burkha</p></div>
</div>
</section>

<section class="products">
<h3>
<?php
$cat = isset($_GET['cat']) ? $_GET['cat'] : 'all';
if($cat == 'all' || $cat == '') {
    echo 'All Products';
} else {
    echo htmlspecialchars($cat) . ' Products';
}
?>
</h3>
<div class="product-grid" id="allProducts">
<?php
$imageCategories = array(
    'Skaff' => array('prefix' => 'skaff', 'price' => 299),
    'Cap' => array('prefix' => 'cap', 'price' => 199),
    'Pin' => array('prefix' => 'pin', 'price' => 49),
    'Burkha' => array('prefix' => 'burkha', 'price' => 999)
);

$filterCat = isset($_GET['cat']) ? $_GET['cat'] : 'all';

foreach($imageCategories as $catName => $catInfo){
    if($filterCat != 'all' && strtolower($filterCat) != strtolower($catName)){
        continue;
    }
    
    $images = glob($catInfo['prefix']."*.jpg");
    if(empty($images)){ $images = glob($catInfo['prefix']."*.jpeg"); }
    if(empty($images)){ $images = glob($catInfo['prefix']."*.JPG"); }
    if(empty($images)){ $images = glob($catInfo['prefix']."*.JPEG"); }
    
    if(!empty($images)){
        foreach($images as $img){
            $imgName = basename($img);
            echo '<div class="product-card" data-category="'.$catName.'" data-name="'.$imgName.'">';
            echo '<a href="product_final.php?image='.urlencode($imgName).'">';
            echo '<img src="'.$imgName.'" alt="'.$imgName.'">';
            echo '</a>';
            echo '<p>'.ucfirst(str_replace(array('.jpg','.jpeg'), '', $imgName)).'</p>';
            echo '<span>₹'.$catInfo['price'].'</span>';
            echo '<button class="add-to-cart-btn" onclick="alert(\'Please view product page to add to cart\')">Add to Cart</button>';
            echo '<a href="product_final.php?image='.urlencode($imgName).'" class="add-to-cart-btn btn-buy">Buy Now</a>';
            echo '</div>';
        }
    }
}
?>
</div>
</section>

<footer>
<div class="footer-inner">
<div class="footer-left">
<div class="logo">HAYA COLLECTION</div>
<p class="small">Premium blogger template. Crafted with love and pretty pink tones.</p>
</div>
<div style="width:320px">
<h4 style="margin-top:0; font-family:'Playfair Display'">About</h4>
<p class="small">Created by akasa mansur, aaliya patel, yashvi mehsuriya. Fully responsive, feminine blogger template made with ♥ by Brand & Blogger.</p>
</div>
</div>
<div class="footer-bottom">&copy; 2025 HAYA COLLECTION. All Rights Reserved.</div>
</footer>

<script>
var $allProducts;

function filterCategory(category){
    if(category === 'all' || category === '' || category === undefined) {
        $allProducts.show();
    } else {
        $allProducts.hide();
        $allProducts.each(function(){
            var productCategory = $(this).data('category');
            var productName = $(this).data('name');
            var searchCat = category.toLowerCase();
            if((productCategory && (
                productCategory.toLowerCase().includes(searchCat) || 
                searchCat.includes(productCategory.toLowerCase())
            )) || (productName && productName.toLowerCase().includes(searchCat))){
                $(this).show();
            }
        });
    }
    $('html, body').animate({ scrollTop: $('#allProducts').offset().top - 100 }, 600);
}

$(document).ready(function(){
    $allProducts = $('#allProducts .product-card');
    
    $('#searchBtn').click(function(){
        var input = $('#searchInput').val().toLowerCase().trim();
        filterCategory(input);
    });

    $('#searchInput').on('keyup', function(){
        var input = $(this).val().toLowerCase().trim();
        filterCategory(input);
    });

    $allProducts.show();
});
</script>

</body>
</html>



