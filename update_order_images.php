<?php
// Script to update existing order_items with product images
include("connect.php");

echo "<h2>Updating order_items with product images...</h2>";

// Update existing order_items with product images from products table
$updateQuery = "
UPDATE order_items oi 
JOIN products p ON p.id = oi.product_id 
SET oi.product_image = p.image 
WHERE (oi.product_image IS NULL OR oi.product_image = '') 
AND oi.product_id > 0
";

if(mysqli_query($con, $updateQuery)){
    $affected = mysqli_affected_rows($con);
    echo "<p style='color:green;'>✓ Updated $affected order items with product images</p>";
} else {
    echo "<p style='color:red;'>Error: " . mysqli_error($con) . "</p>";
}

// Also add column if it doesn't exist
$checkColumn = mysqli_query($con, "SHOW COLUMNS FROM order_items LIKE 'product_image'");
if(mysqli_num_rows($checkColumn) == 0){
    mysqli_query($con, "ALTER TABLE order_items ADD COLUMN product_image VARCHAR(255)");
    echo "<p style='color:green;'>✓ Added product_image column to order_items table</p>";
}

echo "<p>Done! Now the admin order list will show correct product images.</p>";
?>

