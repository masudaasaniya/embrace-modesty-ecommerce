<?php
// Get the product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get the clicked image from URL as backup
$clickedImage = isset($_GET['image']) ? $_GET['image'] : '';

if($product_id == 0 && $clickedImage == ''){
    die("No product selected.");
}

// Include database connection
include("connect.php");

// Get product from database using ID
if($product_id > 0){
    $result = $con->query("SELECT * FROM products WHERE id = $product_id");
    if($result && $result->num_rows > 0){
        $product = $result->fetch_assoc();
        $product_name = $product['name'];
        $product_price = $product['price'];
        $product_image = $product['image'];
        $product_category = $product['category'];
    }
}

// Get related products from same category
if(isset($product_category)){
    $relatedResult = $con->query("SELECT id, name, image, price FROM products WHERE category = '$product_category' AND id != $product_id ORDER BY id DESC LIMIT 20");
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Product View - HAYA COLLECTION</title>
<link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans&display=swap" rel="stylesheet">
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body { font-family: 'Open Sans', sans-serif; background: #fff; color: #333; }
.header { display: flex; justify-content: space-between; align-items: center; padding: 20px 30px; background: linear-gradient(to bottom, #f8ecef, #f6e9ea); }
.brand { color: #823c49; font-family: 'Playfair Display', serif; font-size: 34px; font-weight: 700; }
.nav-right { display: flex; align-items: center; gap: 30px; }
nav ul { display: flex; list-style: none; gap: 25px; }
nav ul li a { text-decoration: none; color: #823c49; font-weight: bold; font-size: 16px; }
.main-product { text-align: center; padding: 40px 20px; max-width: 1200px; margin: 0 auto; }
.main-image { width: 300px; height: 300px; object-fit: cover; border-radius: 10px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.product-name { font-family: 'Playfair Display', serif; font-size: 28px; color: #823c49; margin: 10px 0; }
.product-price { font-size: 24px; font-weight: bold; color: #823c49; margin-bottom: 20px; }
.btn { padding: 12px 30px; border: none; border-radius: 25px; font-weight: bold; cursor: pointer; font-size: 16px; text-decoration: none; display: inline-block; }
.btn-cart { background: #823c49; color: white; }
.btn-buy { background: #28a745; color: white; }
.related-products { padding: 40px 20px; background: #f9f9f9; }
.related-products h3 { text-align: center; font-family: 'Playfair Display', serif; font-size: 24px; color: #823c49; margin-bottom: 30px; }
.grid { display: flex; flex-wrap: wrap; justify-content: center; gap: 20px; max-width: 1200px; margin: 0 auto; }
.grid-item { width: 180px; text-align: center; background: white; padding: 10px; border-radius: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.grid-item img { width: 150px; height: 150px; object-fit: cover; border-radius: 8px; }
.grid-item p { margin-top: 10px; font-weight: bold; color: #823c49; }
.back-link { display: block; text-align: center; margin: 30px 0; color: #823c49; text-decoration: none; font-weight: bold; }
footer { background: linear-gradient(to bottom, #f8ecef, #f6e9ea); padding: 40px 20px; margin-top: 50px; color: #823c49; text-align: center; }
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
<li><a href="shoppage2.php">Shop</a></li>
<li><a href="blogpage.php">Blog</a></li>
</ul>
</nav>
</div>
</header>

<div class="main-product">
<?php if(isset($product_name)): ?>
    <img src="uploads/<?php echo htmlspecialchars($product_image); ?>" class="main-image">
    <h2 class="product-name"><?php echo htmlspecialchars($product_name); ?></h2>
    <p class="product-price">₹<?php echo $product_price; ?></p>
    
    <form method="post" action="cart_actions.php" style="display:inline;">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="redirect" value="product.php?id=<?php echo $product_id; ?>">
        <button type="submit" name="add_to_cart" class="btn btn-cart">Add to Cart</button>
    </form>
    <form method="post" action="cart_actions.php" style="display:inline;">
        <input type="hidden" name="action" value="buy_now">
        <input type="hidden" name="product_id" value="<?php echo $product_id; ?>">
        <input type="hidden" name="redirect" value="product.php?id=<?php echo $product_id; ?>">
        <button type="submit" class="btn btn-buy">Buy Now</button>
    </form>
<?php else: ?>
    <p>Product not found!</p>
<?php endif; ?>
</div>

<div class="related-products">
    <h3>Related Products - <?php echo isset($product_category) ? htmlspecialchars($product_category) : ''; ?></h3>
    
    <div class="grid">
    <?php
    if(isset($relatedResult) && $relatedResult && $relatedResult->num_rows > 0){
        while($relProduct = $relatedResult->fetch_assoc()){
            $relImage = htmlspecialchars($relProduct['image']);
            $relName = htmlspecialchars($relProduct['name']);
            $relId = $relProduct['id'];
            
            echo '<a href="product.php?id='.$relId.'" class="grid-item">';
            echo '<img src="uploads/'.$relImage.'">';
            echo '<p>'.$relName.'</p>';
            echo '</a>';
        }
    } else {
        echo '<p>No related products found.</p>';
    }
    ?>
    </div>
</div>

<a href="shoppage2.php" class="back-link">Back to Shop</a>

<?php include 'includes/global_footer.php'; ?>

</body>
</html>



