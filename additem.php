<?php
include 'connect.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $quantity = intval($_POST['quantity']);
    
    // Fix for older PHP versions (no ?? operator)
    $stock_status = isset($_POST['stock_status']) ? $_POST['stock_status'] : 'In Stock';

    // Handle file upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
        $uploadDir = 'uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $imageName = time() . '_' . basename($_FILES['image']['name']);
        $targetFile = $uploadDir . $imageName;

        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            if ($con) {
                $stmt = $con->prepare("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('ssdssi', $name, $category, $price, $imageName, $stock_status, $quantity);

                if ($stmt->execute()) {
                    $message = 'Item added successfully!';
                } else {
                    $message = 'Error adding item: ' . $conn->error;
                }
                $stmt->close();
            } else {
                $message = 'Database connection error.';
            }
        } else {
            $message = 'Failed to upload image.';
        }
    } else {
        $message = 'Please select an image.';
    }
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Add Item - Admin</title>
  <style>
    body {
      font-family: 'Segoe UI', sans-serif;
      background: #fff;
      margin: 0;
      color: #333;
    }
    .header {
      background: linear-gradient(to bottom, #f8ecef, #f6e9ea);
      padding: 20px 50px;
      display: flex;
      justify-content: space-between;
      align-items: center;
      color: #823c49;
    }
    .nav-links a {
      text-decoration: none;
      font-weight: bold;
      color: #823c49;
      padding: 8px 16px;
      border-radius: 6px;
      transition: background 0.3s;
    }
    .container {
      max-width: 1000px;
      margin: 40px auto;
      background: #fff;
      border-radius: 20px;
      box-shadow: 0 12px 24px rgb(255 105 180 / 0.3);
      display: flex;
      overflow: hidden;
    }
    .sidebar {
      background: #823c49;
      width: 200px;
      padding: 30px 20px;
      display: flex;
      flex-direction: column;
      gap: 15px;
    }
    .sidebar h2 {
      color: white;
      margin-bottom: 20px;
    }
    .sidebar a {
      text-decoration: none;
      color: white;
      padding: 10px;
      border-radius: 8px;
      font-weight: 600;
    }
    .sidebar a.active, .sidebar a:hover {
      background: #ffb6d4;
      color: #6a003f;
    }
    .content {
      flex: 1;
      padding: 40px 50px;
    }
    .content h1 {
      color: #823c49;
      margin-top: 0;
    }
    form {
      margin-top: 30px;
    }
    label {
      display: block;
      margin-bottom: 6px;
      font-weight: 600;
      color: #823c49;
    }
    input, select {
      width: 100%;
      padding: 10px;
      margin-bottom: 20px;
      border: 1px solid #f0c1d7;
      border-radius: 6px;
      font-size: 1rem;
      background: #fff0f7;
      color: #5a0a44;
    }
    input:focus, select:focus {
      outline: none;
      border-color: #ff3399;
    }
    .btn-submit {
      background: #823c49;
      border: none;
      padding: 12px 25px;
      color: white;
      font-weight: 700;
      border-radius: 25px;
      cursor: pointer;
      font-size: 1rem;
    }
    .btn-submit:hover {
      background: #6a003f;
    }
    .message {
      padding: 10px;
      margin-bottom: 20px;
      background: #ffe0ec;
      border: 1px solid #ff99cc;
      color: #b3005c;
      font-weight: bold;
      border-radius: 6px;
    }
  </style>
<link rel="stylesheet" href="responsive.css">
</head>
<body>

<div class="header">
  <h1>Admin - Add Item</h1>
  <div class="nav-links">
    <a href="homepage2.php">Home</a>
    <a href="shoppage2.php">Shop</a>
    <a href="blogpage.php">Blog</a>
  </div>
</div>

<div class="container">
  <aside class="sidebar">
    <h2>Admin Menu</h2>
    <a href="myaccount2.php">Account</a>
    <a href="viewpage.php">View Item</a>
    <a href="update.php">Edit Item</a>
    <a href="logout.php" class="logout-link">Logout</a>
  </aside>

  <main class="content">
    <h1>Add New Item</h1>

    <?php if ($message): ?>
      <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

   <form method="POST" enctype="multipart/form-data">
    <label for="name">Product Name</label>
    <input type="text" id="name" name="name" required />

    <label for="category">Category</label>
    <input type="text" id="category" name="category" required />

    <label for="price">Price ($)</label>
    <input type="number" step="0.01" id="price" name="price" required min="0" />

    <label for="image">Select Image</label>
    <input type="file" id="image" name="image" accept="image/*" required />

    <label for="stock_status">Stock Status</label>
    <select id="stock_status" name="stock_status">
        <option value="In Stock">In Stock</option>
        <option value="Out of Stock">Out of Stock</option>
    </select>

    <label for="quantity">Quantity</label>
    <input type="number" id="quantity" name="quantity" required min="0" value="0" />

    <button type="submit" class="btn-submit">Add Item</button>
</form>


  </main>
</div>

</body>
</html>

