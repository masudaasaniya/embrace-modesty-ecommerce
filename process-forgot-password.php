<?php
session_start();

// Include database connection
include("connect.php");

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get and sanitize email
    $email = mysqli_real_escape_string($con, trim($_POST["email"]));
    
    // Validate email
    if (empty($email)) {
        $_SESSION['forgot_password_error'] = "Email is required!";
        header("Location: forgot_password.php");
        exit();
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_password_error'] = "Invalid email format!";
        header("Location: forgot_password.php");
        exit();
    }
    
    // Check if email exists in database using prepared statement
$stmt = $con->prepare("SELECT id, security_question1, security_question2, security_question3 FROM `user` WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Check if user has all 3 security questions set
        if (empty($user['security_question1']) || empty($user['security_question2']) || empty($user['security_question3'])) {
            $_SESSION['forgot_password_error'] = "This account does not have all 3 security questions set. Please contact support.";
            header("Location: forgot_password.php");
            exit();
        }
        
        // Store info for multi-step security verification
        $_SESSION['reset_user_id'] = $user['id'];
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_user_email'] = $email; // keep for backward
        $_SESSION['questions'] = array($user['security_question1'], $user['security_question2'], $user['security_question3']);
        $_SESSION['question_step'] = 0;
        $_SESSION['security_attempts'] = 0;
        
        // Redirect to security question page
        header("Location: security-question.php");
        exit();
        
    } else {
        // For security reasons, don't reveal if email exists or not
        // Just show a generic message
        $_SESSION['forgot_password_error'] = "If an account with that email exists, you will be redirected to verify your identity.";
        header("Location: forgot_password.php");
        exit();
    }
    
    $stmt->close();
    
} else {
    // Not a POST request, redirect to forgot password page
    header("Location: forgot_password.php");
    exit();
}
?>

