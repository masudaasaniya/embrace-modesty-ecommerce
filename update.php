<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Edit Item</title>
  <style>
    * { box-sizing: border-box; }
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
      
    }

    .header h1 {
      margin: 0;
      color: #823c49;
      font-size: 24px;
    }

    .nav-links {
      display: flex;
      gap: 20px;
    }

    .nav-links a {
      text-decoration: none;
      font-weight:bold;
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
      box-shadow: 0 12px 24px rgb(255 105 180 / 0.2);
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
      margin-top: 0;
      color: #823c49;
    }

    form {
      margin-top: 30px;
    }

    label {
      font-weight: 600;
      color: #823c49;
      display: block;
      margin-bottom: 8px;
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

    input[readonly] {
      background: #f7e5eb;
      color: #666;
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
      background: #ffe0ec;
      border: 1px solid #ff99cc;
      color: #b3005c;
      font-weight: bold;
      border-radius: 6px;
      margin-bottom: 20px;
    }
  </style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<!-- HEADER -->
<div class="header">
  <h1>Admin - Edit Item</h1>
  <div class="nav-links">
    <a href="homepage2.php">Home</a>
    <a href="shoppage2.php">Shop</a>
    <a href="blogpage.php">Blog</a>
  </div>
</div>

<!-- MAIN -->
<div class="container">
  <aside class="sidebar">
    <h2>Admin Menu</h2>
    <a href="myaccount2.php">Account</a>
    <a href="viewpage.php">View Item</a>
    <a href="update.php" class="active">Edit Item</a>
    <a href="additem.php">Add Item</a>
    <a href="logout.php" class="logout-link">Logout</a>
   
  </aside>

  <main class="content">
    <h1>Edit Item</h1>

    <?php
    // Simulate fetched data (you'll fetch from DB in real use)
   $item = array(
  'sid' => 101,
  'product_name' => 'Elegant Abaya',
  'price' => 55.99,
  'quantity' => 25,
  'stock_status' => 'In Stock'
    );
    ?>

    <form method="post" action="update-item.php">
      <label for="sid">SID</label>
      <input type="text" id="sid" name="sid" value="sid" readonly>

      <label for="product_name">Product Name</label>
      <input type="text" id="product_name" name="product_name" value="pname" required>

      <label for="price">Price ($)</label>
      <input type="number" id="price" name="price" step="0.01" value="price" required>

      <label for="quantity">Quantity</label>
      <input type="number" id="quantity" name="quantity" value="qnty" required>

      <label for="stock_status">Stock Status</label>
      <select id="stock_status" name="stock_status" required>
        <option value="In Stock">In Stock</option>
        <option value="Out of Stock">Out of Stock</option>
      </select>

      <button type="submit" class="btn-submit">Update</button>
    </form>
  </main>
</div>

</body>
</html>

