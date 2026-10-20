<?php
session_start();

// Include database connection
include("connect.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Check if security verified
    if (!isset($_SESSION['security_verified']) || $_SESSION['security_verified'] !== true) {
        header("Location: forgot_password.php");
        exit();
    }
    
    $user_id = $_SESSION['reset_user_id'];
    
    // Keep token expiration for backward compat
    $token_time = isset($_SESSION['reset_token_time']) ? $_SESSION['reset_token_time'] : 0;
    if (time() - $token_time > 1800) {
        $_SESSION['reset_password_error'] = "Session expired. Please try again.";
        header("Location: reset-password.php");
        exit();
    }
    
    // Get and validate passwords
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Validation: Check if passwords are provided
    if (empty($new_password) || empty($confirm_password)) {
        $_SESSION['reset_password_error'] = "All fields are required!";
        header("Location: reset-password.php");
        exit();
    }
    
    // Strong password validation: min 6 chars, 1 uppercase, 1 lowercase, 1 number
    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9]).{6,}$/', $new_password)) {
        $_SESSION['reset_password_error'] = "Password must contain at least 6 characters, including uppercase, lowercase and number!";
        header("Location: reset-password.php");
        exit();
    }
    
    // Validation: Passwords match
    if ($new_password !== $confirm_password) {
        $_SESSION['reset_password_error'] = "Passwords do not match!";
        header("Location: reset-password.php");
        exit();
    }
    
    // Hash the new password using md5 (to match existing system)
    $hashed_password = md5($new_password);
    
    // Update password in database using prepared statement
    $stmt = $con->prepare("UPDATE `user` SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashed_password, $user_id);
    
    if ($stmt->execute()) {
        // Password updated successfully
        
        // Clear all reset session variables
        unset($_SESSION['reset_user_id']);
        unset($_SESSION['reset_email']);
        unset($_SESSION['reset_user_email']);
        unset($_SESSION['questions']);
        unset($_SESSION['question_step']);
        unset($_SESSION['security_attempts']);
        unset($_SESSION['security_verified']);
        unset($_SESSION['reset_token']);
        unset($_SESSION['reset_token_time']); 
        
        // Redirect to login with success message
        header("Location: loginform.php?reset=1");
        exit();
        
    } else {
        // Database error
        $_SESSION['reset_password_error'] = "Failed to update password. Please try again.";
        header("Location: reset-password.php");
        exit();
    }
    
    $stmt->close();
    
} else {
    // Not a POST request
    header("Location: forgot_password.php");
    exit();
}
?>

