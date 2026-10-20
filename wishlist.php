<?php
session_start();
include("connect.php");

// Function to get correct image path
function wishlistImagePath($imageRaw) {
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

// Handle Remove from Wishlist
if (isset($_GET['action']) && $_GET['action'] === 'remove' && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $delete = $con->prepare("DELETE FROM wishlist WHERE id = ?");
    $delete->bind_param("i", $id);
    $delete->execute();
    header("Location: wishlist.php");
    exit;
}

// Handle Add to Cart from Wishlist
if (isset($_POST['add_to_cart']) && isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
    // Get product details from wishlist
    $stmt = $con->prepare("SELECT product_id, name, price, image FROM wishlist WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if($result && $result->num_rows > 0){
        $row = $result->fetch_assoc();
        
        // Check if already in cart table
        $check = $con->prepare("SELECT id, quantity FROM cart WHERE product_id = ?");
        $check->bind_param("i", $product_id);
        $check->execute();
        $check_result = $check->get_result();
        
        if($check_result && $check_result->num_rows > 0){
            // Update quantity
            $cart_row = $check_result->fetch_assoc();
            $new_qty = $cart_row['quantity'] + 1;
            $update = $con->prepare("UPDATE cart SET quantity = ? WHERE product_id = ?");
            $update->bind_param("ii", $new_qty, $product_id);
            $update->execute();
        } else {
            // Insert into cart table
            $insert = $con->prepare("INSERT INTO cart (product_id, name, price, image, quantity) VALUES (?, ?, ?, ?, 1)");
            $insert->bind_param("isss", $product_id, $row['name'], $row['price'], $row['image']);
            $insert->execute();
        }
    }
    
    // Optionally remove from wishlist after adding to cart
    $delete = $con->prepare("DELETE FROM wishlist WHERE product_id = ?");
    $delete->bind_param("i", $product_id);
    $delete->execute();
    
    header("Location: cart.php");
    exit;
}

// Fetch wishlist products from database
$wishlist_items = array();
$user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
$sql = "SELECT id, product_id, name, price, image, created_at FROM wishlist WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $wishlist_items[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Wishlist</title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
  <style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Inter', sans-serif; background-color: white; }
    
    /* Header Styling */
    header { background: linear-gradient(to bottom, #f8ecef, #f6e9ea); padding: 20px 30px; border-bottom: 1px solid #eee; }
    .topbar { display: flex; justify-content: space-between; align-items: center; }
    .brand { font-size: 32px; color: #823c49; font-family: 'Playfair Display', serif; font-weight: 700; }
    nav ul { display: flex; gap: 20px; list-style: none; margin: 0; padding: 0; align-items: center; }
    nav a { text-decoration: none; color: #823c49; font-size: 17px; font-weight: bold; }
    
    /* Search Container */
    .search-container { display: flex; justify-content: center; margin: 20px auto 0; max-width: 600px; padding: 0 15px; }
    .search-container input { padding: 14px 20px; width: 100%; border: none; border-radius: 30px; font-size: 16px; outline: none; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
    
    /* Wishlist Container */
    .wishlist-container { padding: 40px 20px; display: flex; flex-wrap: wrap; gap: 24px; justify-content: center; max-width: 1200px; margin: 0 auto; }
    
    /* Product Card */
    .card { 
        background-color: white; 
        border-radius: 15px; 
        width: 220px; 
        box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1); 
        position: relative; 
        overflow: hidden; 
        transition: transform 0.25s ease, box-shadow 0.25s ease; 
    }
    .card:hover { transform: translateY(-8px); box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2); }
    
    /* Card Image */
    .card img { 
        width: 100%; 
        height: 180px; 
        object-fit: cover; 
        border-top-left-radius: 15px; 
        border-top-right-radius: 15px; 
    }
    
    /* Card Content */
    .card .content { padding: 14px 16px; text-align: center; }
    .card h3 { margin: 8px 0 6px; font-size: 18px; color: #333; text-transform: capitalize; font-weight: 600; }
    .card .price { font-weight: 700; color: #823c49; margin-top: 8px; font-size: 18px; }
    
    /* Remove Button - Heart Icon */
    .remove-wishlist { 
        position: absolute; 
        top: 10px; 
        right: 10px; 
        background: white; 
        border-radius: 50%; 
        padding: 8px 10px; 
        font-size: 18px; 
        color: #e91e63; 
        cursor: pointer; 
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.15); 
        transition: all 0.3s ease;
        text-decoration: none;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .remove-wishlist:hover { background-color: #ffebee; transform: scale(1.1); }
    
    /* Action Buttons */
    .add-to-cart-btn { 
        background: #823c49; 
        color: #fff; 
        border: none; 
        border-radius: 5px; 
        padding: 10px 15px; 
        font-weight: bold; 
        cursor: pointer; 
        width: 100%; 
        margin-top: 10px;
        transition: background 0.3s ease;
    }
    .add-to-cart-btn:hover { background: #6d3140; }
    
    .remove-btn { 
        background: transparent; 
        color: #823c49; 
        border: 1px solid #823c49; 
        border-radius: 5px; 
        padding: 8px 15px; 
        font-weight: bold; 
        cursor: pointer; 
        width: 100%; 
        margin-top: 8px;
        transition: all 0.3s ease;
    }
    .remove-btn:hover { background: #823c49; color: #fff; }
    
    .empty-message { 
        text-align: center; 
        font-size: 20px; 
        color: #777; 
        margin-top: 60px; 
        font-weight: 600; 
        width: 100%;
    }
    
    /* Footer */
    footer { background: linear-gradient(to bottom, #f8ecef, #f6e9ea); padding: 40px 20px; margin-top: 50px; }
    .footer-inner { display: flex; flex-wrap: wrap; gap: 20px; max-width: 1200px; margin: 0 auto; }
    .footer-left, .footer-inner > div { flex: 1; min-width: 250px; }
    .footer-left .logo { font-size: 24px; color: #b8778a; font-family: 'Playfair Display', serif; }
    .small { font-size: 13px; color: #8f8084; }
    .footer-bottom { text-align: center; margin-top: 25px; font-size: 12px; color: #8f8084; }
    
    @media (max-width: 600px) {
      .wishlist-container { gap: 15px; }
      .card { width: 160px; }
      .card h3 { font-size: 14px; }
      .card .price { font-size: 14px; }
      .card img { height: 140px; }
    }
    
    /* Back Button Styling */
    .back-container {
        margin: 20px;
    }
    .back-container button {
        padding: 10px 20px;
        background: #823c49;
        color: white;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        font-weight: bold;
        transition: background 0.3s ease;
    }
    .back-container button:hover {
        background: #6d3140;
    }
  </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<header>
  <div class="topbar">
    <div class="brand">❤️ My Wishlist</div>
    <nav>
      <ul>
        <li><a href="homepage2.php">Home</a></li>
        <li><a href="shoppage2.php">Shop</a></li>
        <li><a href="blogpage.php">Blog</a></li>
        <li><a href="myaccount.php">👤</a></li>
      </ul>
    </nav>
  </div>
</header>

<div class="search-container">
  <input type="text" id="searchInput" placeholder="Search your wishlist..." onkeyup="filterWishlist()" />
</div>

<!-- Back Button -->
<div class="back-container">
  <button onclick="goBack()">← Back</button>
</div>

<script>
function goBack() {
    if (document.referrer !== "") {
        window.history.back();
    } else {
        window.location.href = "shoppage2.php";
    }
}
</script>

<div class="wishlist-container" id="wishlist">
  <?php if (!empty($wishlist_items)): ?>
    <?php foreach ($wishlist_items as $item): ?>
      <div class="card" data-name="<?php echo strtolower(htmlspecialchars($item['name'])); ?>">
        
        <!-- Remove from Wishlist (Heart Icon) -->
        <a href="wishlist.php?action=remove&id=<?php echo intval($item['id']); ?>" class="remove-wishlist" title="Remove from Wishlist">
            ❤️
        </a>
        
        <!-- Product Image -->
        <img src="<?php echo wishlistImagePath($item['image']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" />
        
        <div class="content">
          <!-- Product Name -->
          <h3><?php echo htmlspecialchars($item['name']); ?></h3>
          
          <!-- Product Price -->
          <div class="price">₹<?php echo number_format(floatval($item['price']), 2); ?></div>
          
          <!-- Add to Cart Button -->
          <form method="post">
            <input type="hidden" name="product_id" value="<?php echo intval($item['product_id']); ?>">
            <button type="submit" name="add_to_cart" class="add-to-cart-btn">Add to Cart</button>
          </form>
          
          <!-- Remove from Wishlist Button -->
          <a href="wishlist.php?action=remove&id=<?php echo intval($item['id']); ?>" class="remove-btn">Remove</a>
        </div>
      </div>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-message">
        Your wishlist is empty 😔<br>
        <a href="shoppage2.php" style="color: #823c49; text-decoration: underline;">Browse Products</a>
    </div>
  <?php endif; ?>
</div>



<?php include 'includes/global_footer.php'; ?>

</body>
</html>

