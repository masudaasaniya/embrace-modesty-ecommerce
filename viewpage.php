<?php
include 'connect.php'; // Database connection

$sql = "SELECT * FROM products ORDER BY id ASC";
$result = $con->query($sql);

if (!$result) {
    die("Query failed: " . $con->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>View Products</title>
<style>
/* Keep your existing CSS */
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
table{width:100%;border-collapse:collapse;margin-top:30px;background:white;box-shadow:0 4px 8px rgba(0,0,0,0.05);}
th,td{padding:12px 16px;text-align:left;border-bottom:1px solid #f3c5d6;vertical-align:middle;}
th{background-color:#ffe0ec;color:#6a003f;}
tr:hover{background:#fff5f8;}
.stock-button{padding:8px 15px;border:none;border-radius:20px;font-weight:bold;cursor:default;font-size:0.9rem;}
.in-stock{background-color:#d9f5e3;color:green;}
.out-stock{background-color:#ffd9e1;color:red;}
.btn-action{padding:6px 14px;border:none;border-radius:6px;cursor:pointer;font-weight:600;font-size:0.9rem;margin-right:8px;transition:background 0.3s ease;text-decoration:none;color:white;display:inline-block;}
.btn-edit{background-color:#4caf50;}
.btn-edit:hover{background-color:#388e3c;}
.btn-delete{background-color:#f44336;}
.btn-delete:hover{background-color:#d32f2f;}
img.product-image{width:60px;height:60px;object-fit:cover;border-radius:6px;}
</style>
<script>
function confirmDelete(id){
    if(confirm("Are you sure you want to delete the item with ID "+id+"?")){
        window.location.href='delete.php?delete_id='+id;
    }
}
</script>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<div class="header">
<h1>Products List</h1>
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
<a href="viewpage.php" class="active">View Products</a>
<a href="additem.php">Add Item</a>
<a href="logout.php">Logout</a>
</aside>

<main class="content">
<h1>Products</h1>
<table>
<thead>
<tr>
<th>ID</th>
<th>Name</th>
<th>Category</th>
<th>Price</th>
<th>Image</th>
<th>Stock Status</th>
<th>Quantity</th>
<th>Created At</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
<?php
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $status = strtolower($row['stock_status']);
        $imagePath = 'uploads/' . $row['image'];
        echo '<tr>';
        echo '<td>'.$row['id'].'</td>';
        echo '<td>'.htmlspecialchars($row['name']).'</td>';
        echo '<td>'.htmlspecialchars($row['category']).'</td>';
        echo '<td>$'.number_format($row['price'],2).'</td>';
        echo '<td>';
        if (!empty($row['image']) && file_exists($imagePath)) {
            echo '<img src="'.htmlspecialchars($imagePath).'" class="product-image" alt="Product Image">';
        } else {
            echo 'No Image';
        }
        echo '</td>';
        echo '<td><button class="stock-button '.($status=='in stock'?'in-stock':'out-stock').'">'.htmlspecialchars($row['stock_status']).'</button></td>';
        echo '<td>'.$row['quantity'].'</td>';
        echo '<td>'.$row['created_at'].'</td>';
        echo '<td><a href="edititem.php?sid='.$row['id'].'" class="btn-action btn-edit">Edit</a> ';
        echo '<button onclick="confirmDelete('.$row['id'].')" class="btn-action btn-delete">Delete</button></td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="9" style="text-align:center;">No products found.</td></tr>';
}
?>
</tbody>
</table>
</main>
</div>
</body>
</html>

