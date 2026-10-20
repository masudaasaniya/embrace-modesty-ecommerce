<?php
// Insert Cap products directly
$con = mysqli_connect("localhost", "root", "", "23bca090");

if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

// Delete old products first to clean up
mysqli_query($con, "DELETE FROM products");

// Insert new products
mysqli_query($con, "INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 1', 'Cap', 199, 'cap1.jpg', 'In Stock', 50)");
mysqli_query($con, "INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 2', 'Cap', 249, 'cap2.jpg', 'In Stock', 50)");
mysqli_query($con, "INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 3', 'Cap', 299, 'cap3.jpg', 'In Stock', 50)");
mysqli_query($con, "INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 4', 'Cap', 199, 'cap4.jpg', 'In Stock', 50)");
mysqli_query($con, "INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 5', 'Cap', 249, 'cap5.jpg', 'In Stock', 50)");
mysqli_query($con, "INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Pin 1', 'Pin', 49, 'pin1.jpg', 'In Stock', 50)");
mysqli_query($con, "INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Burkha 1', 'Burkha', 999, 'burkha1.jpg', 'In Stock', 50)");

// Check results
$result = mysqli_query($con, "SELECT * FROM products");
$count = mysqli_num_rows($result);

echo "<h1>Products Added!</h1>";
echo "<p>Total products in database: $count</p>";
echo "<ul>";
while ($row = mysqli_fetch_assoc($result)) {
    echo "<li>ID: " . $row['id'] . " - " . $row['name'] . " (" . $row['category'] . ") - ₹" . $row['price'] . "</li>";
}
echo "</ul>";
echo "<p><a href='shoppage2.php'>Go to Shop</a></p>";

mysqli_close($con);
?>
