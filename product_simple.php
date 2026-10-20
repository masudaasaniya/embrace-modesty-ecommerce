<?php
// Get the product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($product_id == 0){
    die("No product selected. Please go back to shop.");
}

// Include database connection
include("connect.php");

// Get product from database
$result = $con->query("SELECT * FROM products WHERE id = $product_id");
if(!$result || $result->num_rows == 0){
    die("Product not found!");
}

$product = $result->fetch_assoc();
$product_name = $product['name'];
$product_price = $product['price'];
$product_image = $product['image'];
$product_category = $product['category'];
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title><?php echo htmlspecialchars($product_name); ?> - HAYA COLLECTION</title>
<style>
body { font-family: Arial, sans-serif; margin: 0; padding: 20px; }
.product-container { max-width: 800px; margin: 0 auto; text-align: center; }
.product-image { width: 300px; border: 2px solid #823c49; border-radius: 10px; }
.product-name { font-size: 24px; color: #823c49; margin: 20px 0; }
.product-price { font-size: 20px; font-weight: bold; color: #823c49; }
.back-btn { display: inline-block; margin-top: 20px; padding: 10px 20px; background: #823c49; color: white; text-decoration: none; border-radius: 5px; }
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<div class="product-container">
    <h1>Product Details</h1>
    
    <img src="uploads/<?php echo htmlspecialchars($product_image); ?>" class="product-image">
    
    <h2 class="product-name"><?php echo htmlspecialchars($product_name); ?></h2>
    <p class="product-price">₹<?php echo $product_price; ?></p>
    <p>Category: <?php echo htmlspecialchars($product_category); ?></p>
    
    <a href="shoppage2.php" class="back-btn">Back to Shop</a>
</div>

</body>
</html>


