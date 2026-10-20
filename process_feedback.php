<?php
// process_feedback.php - Handles feedback form submission
// Sanitizes inputs using mysqli_real_escape_string and htmlspecialchars as required

require_once 'connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: feedback.php?error=Invalid request');
    exit;
}

$name = trim(isset($_POST['name']) ? $_POST['name'] : '');
$email = trim(isset($_POST['email']) ? $_POST['email'] : '');
$rating = intval(isset($_POST['rating']) ? $_POST['rating'] : 0);
$message = trim(isset($_POST['message']) ? $_POST['message'] : '');

// Sanitize as required (escape for DB, html for display)
$name = mysqli_real_escape_string($con, htmlspecialchars($name));
$email = mysqli_real_escape_string($con, htmlspecialchars($email));
$message = mysqli_real_escape_string($con, htmlspecialchars($message));

// Validation
$errors = array();

if (empty($name)) {
    $errors[] = 'Name is required';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required';
}
if ($rating < 1 || $rating > 5) {
    $errors[] = 'Rating must be 1-5';
}
if (empty($message)) {
    $errors[] = 'Message is required';
}

if (!empty($errors)) {
    // Redirect with first error
    header('Location: feedback.php?error=' . urlencode($errors[0]));
    exit;
}

// Insert with prepared statement (safer than escape alone)
$stmt = $con->prepare('INSERT INTO feedback (name, email, rating, message) VALUES (?, ?, ?, ?)');
$stmt->bind_param('ssis', $name, $email, $rating, $message);

if ($stmt->execute()) {
    $stmt->close();
    mysqli_close($con);
    header('Location: feedback.php?success=Thank you for your feedback!');
    exit;
} else {
    $stmt->close();
    mysqli_close($con);
    header('Location: feedback.php?error=Submission failed. Please try again.');
    exit;
}
?>

