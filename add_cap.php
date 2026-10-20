<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$con=mysqli_connect("localhost","root","","23bca090");

if(!$con) {
    die("Database connection failed: " . mysqli_connect_error());
}

// Insert Cap products
$con->query("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 1', 'Cap', 199, 'cap1.jpg', 'In Stock', 50)");
$con->query("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 2', 'Cap', 249, 'cap2.jpg', 'In Stock', 50)");
$con->query("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 3', 'Cap', 299, 'cap3.jpg', 'In Stock', 50)");
$con->query("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 4', 'Cap', 199, 'cap4.jpg', 'In Stock', 50)");
$con->query("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Cap 5', 'Cap', 249, 'cap5.jpg', 'In Stock', 50)");
$con->query("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Pin 1', 'Pin', 49, 'pin1.jpg', 'In Stock', 50)");
$con->query("INSERT INTO products (name, category, price, image, stock_status, quantity) VALUES ('Burkha 1', 'Burkha', 999, 'burkha1.jpg', 'In Stock', 50)");

header("Location: shoppage2.php?cat=Cap");
exit;
?>
