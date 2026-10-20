<?php
include 'connect.php'; // Database connection

$message = '';

// Check if product ID is provided
if (!isset($_GET['sid'])) {
    die("Invalid request.");
}

$sid = intval($_GET['sid']);

// Fetch existing product data
$sql = "SELECT * FROM products WHERE id = ?";
$stmt = $con->prepare($sql);
$stmt->bind_param("i", $sid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Product not found.");
}

$product = $result->fetch_assoc();
$stmt->close();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    $stock_status = isset($_POST['stock_status']) ? $_POST['stock_status'] : 'In Stock';

    // Handle image upload
    $imageName = $product['image']; // default existing image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Delete old image
        $oldImagePath = $uploadDir . $imageName;
        if (file_exists($oldImagePath)) unlink($oldImagePath);

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $imageName;
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            $message = "Failed to upload new image.";
        }
    }

    // Update database
    $updateSql = "UPDATE products SET name=?, category=?, price=?, stock_status=?, quantity=?, image=? WHERE id=?";
    $stmtUpdate = $con->prepare($updateSql);
    $stmtUpdate->bind_param("ssdsisi", $name, $category, $price, $stock_status, $quantity, $imageName, $sid);

    if ($stmtUpdate->execute()) {
        $message = "Product updated successfully!";
        // Refresh product data
        $stmt = $con->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->bind_param("i", $sid);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $message = "Error updating product: " . $con->error;
    }
    $stmtUpdate->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Product</title>
<style>
/* Same CSS as viewpage.php */
*{box-sizing:border-box;}
body{font-family:'Segoe UI',sans-serif;background:#fff;margin:0;color:#333;}
.header{background:linear-gradient(to bottom,#f8ecef,#f6e9ea);padding:20px 50px;display:flex;justify-content:space-between;align-items:center;}
.header h1{margin:0;color:#823c49;font-size:24px;}
.nav-links{display:flex;gap:20px;}
.nav-links a{text-decoration:none;font-weight:bold;color:#823c49;padding:8px 16px;border-radius:6px;transition:background 0.3s;}
.nav-links a:hover{background:linear-gradient(to bottom,#f8ecef,#f6e9ea);color:#6a003f;}
.container{max-width:1200px;margin:40px auto;background:#fff;border-radius:20px;box-shadow:0 12px 24px rgb(255 105 180 / 0.2);display:flex;overflow:hidden;}
.sidebar{background:#823c49;width:200px;padding:30px 20px;display:flex;flex-direction:column;gap:15px;}
.sidebar h2{color:white;margin-bottom:20px;}
.sidebar a{text-decoration:none;color:white;padding:10px;border-radius:8px;font-weight:600;}
.sidebar a.active, .sidebar a:hover{background:#ffb6d4;color:#6a003f;}
.content{flex:1;padding:40px 50px;}
.content h1{color:#823c49;margin-top:0;}
form{margin-top:30px;}
label{display:block;margin-bottom:6px;font-weight:600;color:#823c49;}
input, select{width:100%;padding:10px;margin-bottom:20px;border:1px solid #f0c1d7;border-radius:6px;font-size:1rem;background:#fff0f7;color:#5a0a44;}
input:focus, select:focus{outline:none;border-color:#ff3399;}
.btn-submit{background:#823c49;border:none;padding:12px 25px;color:white;font-weight:700;border-radius:25px;cursor:pointer;font-size:1rem;}
.btn-submit:hover{background:#6a003f;}
.message{padding:10px;margin-bottom:20px;background:#ffe0ec;border:1px solid #ff99cc;color:#b3005c;font-weight:bold;border-radius:6px;}
img.product-image{width:80px;height:80px;object-fit:cover;border-radius:6px;margin-bottom:10px;}
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<div class="header">
<h1>Edit Product</h1>
<div class="nav-links">
    <a href="homepage2.php">Home</a>
    <a href="shoppage2.php">Shop</a>
    <a href="blogpage.php">Blog</a>
</div>
</div>

<div class="container">
<aside class="sidebar">
<h2>Menu</h2>
<a href="myaccount2.php">Account</a>
<a href="viewpage.php">View Products</a>
<a href="additem.php">Add Item</a>
<a href="logout.php">Logout</a>
</aside>

<main class="content">
<h1>Edit Product</h1>

<?php if ($message): ?>
<div class="message"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
<label for="name">Product Name</label>
<input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

<label for="category">Category</label>
<input type="text" id="category" name="category" value="<?php echo htmlspecialchars($product['category']); ?>" required>

<label for="price">Price ($)</label>
<input type="number" step="0.01" id="price" name="price" value="<?php echo htmlspecialchars($product['price']); ?>" required>

<label for="stock_status">Stock Status</label>
<select id="stock_status" name="stock_status">
    <option value="In Stock" <?php if($product['stock_status']=='In Stock') echo 'selected'; ?>>In Stock</option>
    <option value="Out of Stock" <?php if($product['stock_status']=='Out of Stock') echo 'selected'; ?>>Out of Stock</option>
</select>

<label for="quantity">Quantity</label>
<input type="number" id="quantity" name="quantity" value="<?php echo htmlspecialchars($product['quantity']); ?>" required min="0">

<label for="image">Product Image</label>
<?php if (!empty($product['image']) && file_exists('uploads/'.$product['image'])): ?>
<img src="uploads/<?php echo htmlspecialchars($product['image']); ?>" class="product-image" alt="Product Image">
<?php endif; ?>
<input type="file" id="image" name="image" accept="image/*">

<button type="submit" class="btn-submit">Update Product</button>
</form>

</main>
</div>
</body>
</html>

