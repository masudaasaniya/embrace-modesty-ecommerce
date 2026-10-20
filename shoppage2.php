<?php 
session_start();
include("connect.php");

// Check if user is logged in
$isLoggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);

// Handle Add to Cart - Only for logged in users


// Handle Buy Now - Only for logged in users
if(isset($_GET['buy_now']) && $isLoggedIn){
    $product_id = intval($_GET['buy_now']);
    
    $stmt = $con->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();
        
$user_id = intval($_SESSION['user_id']);
$check = $con->prepare("SELECT id, quantity FROM cart WHERE product_id = ? AND user_id = ?");
$check->bind_param("ii", $product_id, $user_id);
        $check->execute();
        $check_result = $check->get_result();
        
        if($check_result && $check_result->num_rows > 0){
            $cart_row = $check_result->fetch_assoc();
            $new_qty = $cart_row['quantity'] + 1;
$update = $con->prepare("UPDATE cart SET quantity = ? WHERE product_id = ? AND user_id = ?");
$update->bind_param("iii", $new_qty, $product_id, $user_id);
            $update->execute();
        } else {
            $user_id = intval($_SESSION['user_id']);
            $insert = $con->prepare("INSERT INTO cart (user_id, product_id, name, price, image, quantity) VALUES (?, ?, ?, ?, ?, 1)");
$insert->bind_param("iisss", $user_id, $product_id, $row['name'], $row['price'], $row['image']);
            $insert->execute();
        }
    }
    
    header("Location: cart.php");
    exit;
}

// Handle Add to Wishlist - Only for logged in users
if(isset($_POST['add_to_wishlist']) && $isLoggedIn){
    $product_id = intval($_POST['product_id']);
    
    $stmt = $con->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();
        
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$check = $con->prepare("SELECT id FROM wishlist WHERE product_id = ? AND user_id = ?");
$check->bind_param("ii", $product_id, $user_id);
        $check->execute();
        $check_result = $check->get_result();
        
        if($check_result && $check_result->num_rows == 0){
$insert = $con->prepare("INSERT INTO wishlist (user_id, product_id, name, price, image) VALUES (?, ?, ?, ?, ?)");
            $insert->bind_param("iisss", $user_id, $product_id, $row['name'], $row['price'], $row['image']);
            $insert->execute();
        }
    }
    
    header("Location: wishlist.php");
    exit;
}

// Get cart and wishlist counts
$cartCount = 0;
$wishlistCount = 0;

$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$cartResult = $con->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
$cartResult->bind_param("i", $user_id);
$cartResult->execute();
$cartResultRes = $cartResult->get_result();
if($cartResultRes && $row = $cartResultRes->fetch_assoc()){
    $cartCount = intval($row['total']);
} else {
    $cartCount = 0;
}

$wishlistStmt = $con->prepare("SELECT COUNT(*) as total FROM wishlist WHERE user_id = ?");
$wishlistStmt->bind_param("i", $user_id);
$wishlistStmt->execute();
$res = $wishlistStmt->get_result();
$row = $res->fetch_assoc();
$wishlistCount = intval($row['total']);

// Get category from URL
$cat = isset($_GET['cat']) ? trim($_GET['cat']) : 'all';

// Get search query from URL
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Database query
if(!empty($search)){
    $searchTerm = "%" . $search . "%";
    $stmt = $con->prepare("SELECT id, name, price, image, category FROM products WHERE name LIKE ? OR category LIKE ? ORDER BY id DESC");
    if($stmt){
        $stmt->bind_param("ss", $searchTerm, $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        $result = false;
    }
}
elseif($cat == 'all'){
    $sql = "SELECT id, name, price, image, category FROM products ORDER BY id DESC";
    $result = mysqli_query($con,$sql);
}
else{
    $stmt = $con->prepare("SELECT id, name, price, image, category FROM products WHERE category = ? ORDER BY id DESC");
    $stmt->bind_param("s", $cat);
    $stmt->execute();
    $result = $stmt->get_result();
}

function getProductImagePath($imageRaw) {
    $raw = trim($imageRaw);
    if ($raw === '') {
        return 'skaff1.jpg';
    }
    $candidates = array(
        'admin/uploads/products/' . ltrim($raw, '/\\'),
        'admin/uploads/' . ltrim($raw, '/\\'),
        'uploads/products/' . ltrim($raw, '/\\'),
        'uploads/' . ltrim($raw, '/\\'),
        $raw
    );
    foreach ($candidates as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    return 'skaff1.jpg';
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
.icon-btn { font-size: 20px; position: relative; cursor: pointer; text-decoration: none; }
.icon-btn .count { position: absolute; top: -8px; right: -8px; background: #823c49; color: #fff; border-radius: 50%; padding: 2px 6px; font-size: 11px; font-weight: bold; }
.collection-banner { background: #823c49; color: #fff; text-align: center; padding: 15px; font-size: 18px; font-weight: bold; }
.products { padding: 40px 20px; text-align: center; }
.product-grid { display: flex; justify-content: center; flex-wrap: wrap; gap: 30px; margin-top: 20px; }

.product-card { width: 200px; cursor: pointer; text-align: center; background: #fff; padding: 15px; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: transform 0.3s ease; position: relative; }
.product-card:hover { transform: translateY(-5px); }

.product-image-container { position: relative; width: 100%; border-radius: 5px; overflow: hidden; }
.product-card img { width: 100%; border-radius: 5px; height: 200px; object-fit: cover; }

.wishlist-heart { position: absolute; top: 10px; right: 10px; background: #fff; border-radius: 50%; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.3s ease; z-index: 10; border: none; font-size: 18px; }
.wishlist-heart:hover { background: #ffebee; transform: scale(1.1); }
.wishlist-heart i { color: #823c49; }
.wishlist-heart.active i { color: #e91e63; }

.product-card .product-name { margin: 10px 0 5px; font-weight: bold; font-size: 15px; color: #333; }
.product-card .product-price { display: block; margin-bottom: 10px; font-weight: bold; color: #823c49; font-size: 16px; }

.add-to-cart-btn, .buy-now-btn { display: block; width: 100%; padding: 10px; margin-top: 8px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; font-family: inherit; font-size: 14px; text-align: center; text-decoration: none; line-height: 1.5; box-sizing: border-box; }
.add-to-cart-btn { background: #823c49; color: #fff; }
.add-to-cart-btn:hover { background: #6d3140; }
.buy-now-btn { background: #fff; color: #823c49; border: 2px solid #823c49 !important; }
.buy-now-btn:hover { background: #823c49; color: #fff; }

/* Login Popup Styles */
.login-popup { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; }
.login-popup.show { display: flex; }
.login-popup-content { background: #fff; padding: 40px; border-radius: 15px; text-align: center; max-width: 400px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.3); animation: slideIn 0.3s ease; }
@keyframes slideIn { from { transform: translateY(-50px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.login-popup-content h2 { color: #823c49; font-family: 'Playfair Display', serif; margin-bottom: 15px; font-size: 24px; }
.login-popup-content p { color: #666; margin-bottom: 25px; font-size: 15px; }
.login-popup-buttons { display: flex; gap: 15px; justify-content: center; }
.login-popup-btn { padding: 12px 30px; border-radius: 8px; font-weight: bold; font-size: 14px; text-decoration: none; cursor: pointer; transition: all 0.3s ease; }
.login-btn { background: #823c49; color: #fff; border: none; }
.login-btn:hover { background: #6d3140; }
.register-btn { background: #fff; color: #823c49; border: 2px solid #823c49; }
.register-btn:hover { background: #823c49; color: #fff; }
.close-popup { position: absolute; top: 15px; right: 20px; font-size: 28px; cursor: pointer; color: #999; }

footer { background: linear-gradient(to bottom, #f8ecef, #f6e9ea); padding: 40px 20px; margin-top: 50px; color: #823c49; font-family: 'Open Sans', sans-serif; }
.footer-inner { display: flex; flex-wrap: wrap; gap: 20px; max-width: 1200px; margin: 0 auto; }
.footer-left, .footer-inner > div { flex: 1; min-width: 250px; }
.footer-left .logo { font-size: 24px; font-family: 'Playfair Display', serif; font-weight: 700; color: #b8778a; margin-bottom: 10px; }
.footer-left p, .footer-inner > div p { font-size: 13px; color: #8f8084; line-height: 1.5; }
.footer-bottom { text-align: center; margin-top: 25px; font-size: 12px; color: #8f8084; }

@media (max-width: 768px) {
.footer-inner { flex-direction: column; text-align: center; }
.footer-left, .footer-inner > div { margin-bottom: 20px; }
.login-popup-buttons { flex-direction: column; }
}
</style>
<link rel="stylesheet" href="responsive.css">
</head>
<body>

<!-- Login Popup -->
<div class="login-popup" id="loginPopup">
    <div class="login-popup-content">
        <span class="close-popup" onclick="closeLoginPopup()">&times;</span>
        <h2><i class="fas fa-lock"></i> Please Login</h2>
        <p>Please login or register to continue shopping.</p>
        <div class="login-popup-buttons">
            <a href="loginform.php" class="login-popup-btn login-btn">Login</a>
            <a href="loginform.php" class="login-popup-btn register-btn">Register</a>
        </div>
    </div>
</div>

<header class="header">
<h1><span class="brand">HAYA</span><small style="display:block;font-size:12px;letter-spacing:3px;font-family:Poppins,sans-serif;color:#7A3E3E;margin-top:-6px;">Collection</small></h1>
<div class="nav-right">
<nav>
<ul>
<li><a href="homepage2.php">Home</a></li>
<li><a href="shoppage2.php">Shop</a></li>
<li><a href="blogpage.php">Blog</a></li>
<li><a href="feedback.php">Feedback</a></li>
</ul>
</nav>
<div class="nav-actions">
<form method="GET" action="shoppage2.php" style="display:flex;gap:10px;align-items:center;">
    <input type="text" class="search-input" id="searchInput" name="search" placeholder="Search..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
    <button type="submit" id="searchBtn">Search</button>
</form>
<a href="cart.php" class="icon-btn" id="cartIcon">🛒<span class="count"><?php echo $cartCount; ?></span></a>
<a href="wishlist.php" class="icon-btn" id="wishlistIcon">❤️<span class="count"><?php echo $wishlistCount; ?></span></a>
<?php
if(isset($_SESSION['user_id'])){
    if(isset($_SESSION['usertype']) && $_SESSION['usertype'] === 'admin'){
        echo '<a href="myaccount2.php" class="icon-btn">👤</a>';
    } else {
        echo '<a href="myaccount.php" class="icon-btn">👤</a>';
    }
} else {
    echo '<a href="loginform.php" class="icon-btn">👤</a>';
}
?>
</div>
</header>

<section class="collection-banner">SHOP OUR NEW ELEGANT COLLECTION</section>



<section class="products">
<h3>Categories</h3>
<div class="product-grid">
<?php 
$categoryItems = array(
    array('link' => 'all', 'rawImage' => 'new-arrivals.jpg', 'alt' => 'all', 'name' => 'All'),
    array('link' => 'Scarf', 'rawImage' => 'skaff1.jpg', 'alt' => 'skaff', 'name' => 'Scarf'),
    array('link' => 'Cap', 'rawImage' => 'cap1.jpg', 'alt' => 'cap', 'name' => 'Cap'),
    array('link' => 'Pin', 'rawImage' => 'pin2.jpg', 'alt' => 'pin', 'name' => 'Pin'),
    array('link' => 'Burkha', 'rawImage' => 'burkha13.jpg', 'alt' => 'burkha', 'name' => 'Burkha')
);
foreach($categoryItems as $item): 
    $imagePath = getProductImagePath($item['rawImage']);
?>
<div class="product-card">
    <a href="shoppage2.php?cat=<?php echo htmlspecialchars($item['link']); ?>">
        <div class="product-image-container">
            <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                 alt="<?php echo htmlspecialchars($item['alt']); ?>"
                 loading="eager"
                 onerror="this.onerror=null; this.src='skaff1.jpg';"
                 style="object-fit: cover;">
        </div>
    </a>
    <p><?php echo htmlspecialchars($item['name']); ?></p>
</div>
<?php endforeach; ?>
</div>
</section>

<section class="products">
<h3>
<?php
if(!empty($search)) {
    echo 'Search Results for "' . htmlspecialchars($search) . '"';
} elseif($cat == 'all' || $cat == '') {
    echo 'All Products';
} else {
    echo htmlspecialchars($cat) . ' Products';
}
?>
</h3>
<div class="product-grid" id="allProducts">
<?php if($result && $result->num_rows > 0): ?>

<?php while($row = $result->fetch_assoc()): 
    $imagePath = getProductImagePath($row['image']);
?>

<div class="product-card" data-id="<?php echo intval($row['id']); ?>" data-name="<?php echo htmlspecialchars(strtolower($row['name'])); ?>" data-category="<?php echo htmlspecialchars(strtolower($row['category'])); ?>">

    <div class="product-image-container">
        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
        
        <?php if($isLoggedIn): ?>
        <form method="post" class="wishlist-form" style="margin:0;">
            <input type="hidden" name="product_id" value="<?php echo intval($row['id']); ?>">
            <button type="submit" name="add_to_wishlist" class="wishlist-heart" title="Add to Wishlist">
                <i class="far fa-heart"></i>
            </button>
        </form>
        <?php else: ?>
        <button type="button" class="wishlist-heart" onclick="showLoginPopup()" title="Add to Wishlist">
            <i class="far fa-heart"></i>
        </button>
        <?php endif; ?>
    </div>
    
    <p class="product-name"><?php echo htmlspecialchars($row['name']); ?></p>
    <span class="product-price">₹<?php echo number_format(floatval($row['price']), 2); ?></span>
    
    <?php if($isLoggedIn): ?>
    <form method="post" action="cart_actions.php">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?php echo intval($row['id']); ?>">
        <input type="hidden" name="redirect" value="shoppage2.php">
        <button type="submit" class="add-to-cart-btn">Add to Cart</button>
    </form>
<a href="cart_actions.php?action=buy_now&product_id=<?php echo intval($row['id']); ?>&redirect=checkout.php" class="buy-now-btn">Buy Now</a>
    <?php else: ?>
    <button type="button" class="add-to-cart-btn" onclick="showLoginPopup()">Add to Cart</button>
    <button type="button" class="buy-now-btn" onclick="showLoginPopup()">Buy Now</button>
    <?php endif; ?>

</div>

<?php endwhile; ?>

<?php else: ?>

<p><?php echo !empty($search) ? 'No products found.' : 'No products found in this category.'; ?></p>

<?php endif; ?>
</div>
</section>

<?php include 'includes/global_footer.php'; ?>

<script>
function showLoginPopup() {
    document.getElementById('loginPopup').classList.add('show');
}

function closeLoginPopup() {
    document.getElementById('loginPopup').classList.remove('show');
}

// Close popup when clicking outside
document.getElementById('loginPopup').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLoginPopup();
    }
});
</script>

</body>
</html>



