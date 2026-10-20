<?php
include 'connect.php'; // Database connection

if (isset($_GET['delete_id'])) {
    $id = intval($_GET['delete_id']);

    // First, get the image filename
    $sql = "SELECT image FROM products WHERE id = ?";
    $stmt = $con->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->bind_result($imageName);
    $stmt->fetch();
    $stmt->close();

    // Delete the image file if exists
    if (!empty($imageName)) {
        $imagePath = 'uploads/' . $imageName;
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    // Delete the product from the database
    $sqlDelete = "DELETE FROM products WHERE id = ?";
    $stmtDelete = $con->prepare($sqlDelete);
    $stmtDelete->bind_param("i", $id);

    if ($stmtDelete->execute()) {
        $stmtDelete->close();
        header("Location: viewpage.php?message=Product+deleted+successfully");
        exit();
    } else {
        $stmtDelete->close();
        die("Error deleting product: " . $con->error);
    }
} else {
    die("Invalid request.");
}
?>
