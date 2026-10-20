<?php
session_start();

// Check if admin is logged in
if (!isset($_SESSION['usertype']) || $_SESSION['usertype'] !== 'admin') {
    header("Location: loginform.php");
    exit;
}

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: admin_feedback.php?error=Invalid request");
    exit;
}

$id = intval($_GET['id']);

// Connect to database
$conn = mysqli_connect("localhost", "root", "", "23bca090");

if (!$conn) {
    header("Location: admin_feedback.php?error=Database connection failed");
    exit;
}

// Delete feedback using prepared statement
$stmt = $conn->prepare("DELETE FROM feedback WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    $stmt->close();
    mysqli_close($conn);
    header("Location: admin_feedback.php?success=1");
    exit;
} else {
    $stmt->close();
    mysqli_close($conn);
    header("Location: admin_feedback.php?error=1");
    exit;
}

