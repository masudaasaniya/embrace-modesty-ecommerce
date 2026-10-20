<?php
include("connect.php");

// Check if required parameters are passed
if(isset($_GET['user_id']) && isset($_GET['created_at'])) {
    $user_id = intval($_GET['user_id']);
    $created_at = $con->real_escape_string($_GET['created_at']);

    // Delete all rows matching user_id and created_at (entire order)
    $delete_sql = "DELETE FROM orders WHERE user_id='$user_id' AND created_at='$created_at'";
    if($con->query($delete_sql)){
        // Redirect back to orderdetail page with success message
        header("Location: orderdetail.php?msg=Order+deleted+successfully");
        exit;
    } else {
        echo "Error deleting order: " . $con->error;
    }
} else {
    echo "Invalid parameters.";
}
?>
