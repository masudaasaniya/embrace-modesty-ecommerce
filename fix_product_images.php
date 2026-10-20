<?php
/**
 * Fix Product Images Script
 * Run at: http://localhost/php%20e-com/fix_product_images.php
 * This script fixes incorrect image paths in the database
 */
include 'connect.php';

echo "<h1>Fixing Product Images</h1>";

$fixes = array(
    // Fix scarf -> skaff naming
    'scarf1.jpg' => 'skaff1.jpg',
    'scarf2.jpg' => 'skaff2.jpg',
    'scarf3.jpg' => 'skaff3.jpg',
    'scarf4.jpg' => 'skaff4.jpg',
    'scarf5.jpg' => 'skaff5.jpg',
    'scarf6.jpg' => 'skaff6.jpg',
    'scarf7.jpg' => 'skaff7.jpg',
    'scarf8.jpg' => 'skaff8.jpg',
    'scarf9.jpg' => 'skaff9.jpg',
    'scarf10.jpg' => 'skaff10.jpg',
    'scarf11.jpg' => 'skaff11.jpg',
    'scarf12.jpg' => 'skaff12.jpg',
    'scarf13.jpg' => 'skaff13.jpg',
    'scarf14.jpg' => 'skaff14.jpg',
    'scarf15.jpg' => 'skaff15.jpg',
);

$updated = 0;
foreach ($fixes as $old => $new) {
    $stmt = $con->prepare("UPDATE products SET image = ? WHERE image = ?");
    $stmt->bind_param('ss', $new, $old);
    if ($stmt->execute()) {
        $affected = $stmt->affected_rows;
        if ($affected > 0) {
            echo "Updated: $old -> $new ($affected rows)<br>";
            $updated += $affected;
        }
    }
    $stmt->close();
}

// Fix hashed names - these appear to be missing or renamed files
// Let's check which products have these problematic images
echo "<h2>Checking for products with problematic images...</h2>";

$result = $con->query("SELECT id, name, image FROM products WHERE image LIKE '69af%' OR image LIKE '%.jpg'");
if ($result && $result->num_rows > 0) {
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Current Image</th><th>Status</th></tr>";
    
    while ($row = $result->fetch_assoc()) {
        $image = $row['image'];
        $existsInRoot = file_exists($image);
        $existsInUploads = file_exists('uploads/' . $image);
        
        if (!$existsInRoot && !$existsInUploads) {
            echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>$image</td><td style='color:red'>MISSING</td></tr>";
        } else {
            echo "<tr><td>{$row['id']}</td><td>{$row['name']}</td><td>$image</td><td style='color:green'>OK</td></tr>";
        }
    }
    echo "</table>";
} else {
    echo "<p>No products with hashed image names found.</p>";
}

// List all unique images in database
echo "<h2>All unique images in database:</h2>";
$result = $con->query("SELECT DISTINCT image FROM products ORDER BY image");
if ($result) {
    echo "<ul>";
    while ($row = $result->fetch_assoc()) {
        $img = $row['image'];
        $existsRoot = file_exists($img) ? "✓ (root)" : "✗ (root)";
        $existsUpload = file_exists('uploads/' . $img) ? "✓ (uploads)" : "✗ (uploads)";
        echo "<li>$img - Root: $existsRoot, Uploads: $existsUpload</li>";
    }
    echo "</ul>";
}

echo "<h2>Done! Total updates: $updated</h2>";
echo "<a href='shoppage2.php'>Go to Shop</a>";
?>

