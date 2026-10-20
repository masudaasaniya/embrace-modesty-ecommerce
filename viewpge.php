<?php
// view-item.php

// Include your DB connection here
include 'db.php';

// Fetch items from DB
$sql = "SELECT sid, productname, price, quantity, stock_status FROM products ORDER BY sid ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>View Items</title>
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
      border-bottom: 2px solid #ffc0db;
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
      font-weight: 600;
      color: #823c49;
      padding: 8px 16px;
      border-radius: 6px;
      transition: background 0.3s;
    }

    .nav-links a:hover {
      background: #ffb6d4;
      color: #6a003f;
    }

    .container {
      max-width: 1200px;
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
      color: #823c49;
      margin-top: 0;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 30px;
      background: white;
      box-shadow: 0 4px 8px rgba(0,0,0,0.05);
    }

    th, td {
      padding: 12px 16px;
      text-align: left;
      border-bottom: 1px solid #f3c5d6;
      vertical-align: middle;
    }

    th {
      background-color: #ffe0ec;
      color: #6a003f;
    }

    tr:hover {
      background: #fff5f8;
    }

    .stock-button {
      padding: 8px 15px;
      border: none;
      border-radius: 20px;
      font-weight: bold;
      cursor: default;
      font-size: 0.9rem;
    }

    .in-stock {
      background-color: #d9f5e3;
      color: green;
    }

    .out-stock {
      background-color: #ffd9e1;
      color: red;
    }

    /* New buttons for Edit/Delete */
    .btn-action {
      padding: 6px 14px;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 0.9rem;
      margin-right: 8px;
      transition: background 0.3s ease;
      text-decoration: none;
      color: white;
      display: inline-block;
    }

    .btn-edit {
      background-color: #4caf50; /* green */
    }
    .btn-edit:hover {
      background-color: #388e3c;
    }

    .btn-delete {
      background-color: #f44336; /* red */
    }
    .btn-delete:hover {
      background-color: #d32f2f;
    }
  </style>
  <script>
    function confirmDelete(sid) {
      if (confirm("Are you sure you want to delete the item with ID " + sid + "?")) {
        window.location.href = 'delete.php?delete_sid=' + sid;
      }
    }
  </script>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<!-- HEADER -->
<div class="header">
  <h1>Admin - View Items</h1>
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
    <a href="viewpage.php" class="active">View Item</a>
    <a href="update.php">Edit Item</a>
    <a href="additem.php">Add Item</a>
    <a href="logout.php" class="logout-link">Logout</a>
    
  </aside>

  <main class="content">
    <h1>View Items</h1>

    <table>
      <thead>
        <tr>
          <th>S No</th>
          <th>Product Name</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Stock</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($result && $result->num_rows > 0): ?>
          <?php $count = 1; ?>
          <?php while($row = $result->fetch_assoc()): ?>
            <tr>
              <td><?= $count++ ?></td>
              <td><?= htmlspecialchars($row['productname']) ?></td>
              <td>$<?= number_format($row['price'], 2) ?></td>
              <td><?= htmlspecialchars($row['quantity']) ?></td>
              <td>
                <?php if (strtolower($row['stock_status']) == 'in stock'): ?>
                  <button class="stock-button in-stock">In Stock</button>
                <?php else: ?>
                  <button class="stock-button out-stock">Out of Stock</button>
                <?php endif; ?>
              </td>
              <td>
                <a href="edit-item.php?sid=<?= $row['sid'] ?>" class="btn-action btn-edit">Edit</a>
                <button onclick="confirmDelete(<?= $row['sid'] ?>)" class="btn-action btn-delete">Delete</button>
              </td>
            </tr>
          <?php endwhile; ?>
        <?php else: ?>
          <!-- Demo static row -->
          <tr>
            <td>1</td>
            <td>Demo Floral Maxi Dress</td>
            <td>$45.00</td>
            <td>20</td>
            <td><button class="stock-button in-stock">In Stock</button></td>
            <td>
              <a href="edit-item.php?sid=1" class="btn-action btn-edit">Edit</a>
              <button onclick="confirmDelete(1)" class="btn-action btn-delete">Delete</button>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </main>
</div>

</body>
</html>

