<?php
session_start();
include("connect.php");

$message = "";
$messageType = "error";

if (isset($_GET["reset"]) && $_GET["reset"] == "1") {
    $message = "Password updated successfully. Please login.";
    $messageType = "success";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    if (empty($username) || empty($password)) {
        $message = "All fields are required!";
        $messageType = "error";
    } else {
        
        // ✅ Static API Logic - Check for specific credentials first
        if ($username === "masuda" && $password === "Massu@123") {
            $_SESSION['user_id'] = 1;
            $_SESSION['username'] = $username;
            $_SESSION['usertype'] = 'admin';
            header("Location: myaccount2.php");
            exit();
        }

        // ✅ Secure Prepared Statement
        $stmt = $con->prepare("SELECT id, username, password FROM `user` WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows === 1) {

            $user = $result->fetch_assoc();
            $validPassword = false;

            // If password_hash() exists (new PHP)
            if (function_exists('password_verify')) {
                if (password_verify($password, $user['password'])) {
                    $validPassword = true;
                }
            }

            // If using md5 (old PHP)
            if (md5($password) === $user['password']) {
                $validPassword = true;
            }

            if ($validPassword) {

                $_SESSION['user_id']  = $user['id'];
                $_SESSION['username'] = $user['username'];

                header("Location: myaccount.php");
                exit();

            } else {
                $message = "Incorrect username or password!";
                $messageType = "error";
            }

        } else {
            $message = "User not found!";
            $messageType = "error";
        }

        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Login Form</title>

<style>
body {
    margin: 0;
    padding: 0;
    font-family: 'Arial', sans-serif;
    background: url('rform2.jpg') no-repeat center center fixed;
    background-size: cover;
    color: #823c49;
}
.login-container {
    width: 350px;
    margin: 100px auto;
    padding: 30px;
    background-color: #fff;
    border-radius: 20px;
    box-shadow: 0 5px 15px rgba(130, 60, 73, 0.3);
}
.login-container h2 { 
    text-align: center; 
    margin-bottom: 25px; 
}
.login-container input[type="text"],
.login-container input[type="password"] {
    width: 100%;
    padding: 12px;
    margin: 10px 0;
    border: 1px solid #ccc;
    border-radius: 25px;
    font-size: 14px;
    background-color: #f9f9f9;
}
.login-container input:focus {
    border-color: #823c49;
    outline: none;
    box-shadow: 0 0 4px rgba(130, 60, 73, 0.3);
}
.login-container button {
    width: 100%;
    padding: 12px;
    background-color: #823c49;
    color: white;
    border: none;
    border-radius: 25px;
    font-size: 16px;
    cursor: pointer;
    margin-top: 15px;
    transition: 0.3s;
}
.login-container button:hover { 
    background-color: #a55267; 
}
.register-link { 
    text-align: center; 
    margin-top: 15px; 
}
.register-link a { 
    color: #823c49; 
    text-decoration: none; 
}
.register-link a:hover { 
    text-decoration: underline; 
}
.forgot-link {
    text-align: right;
    margin-top: 6px;
}
.forgot-link a {
    color: #823c49;
    font-size: 13px;
    text-decoration: none;
}
.forgot-link a:hover {
    text-decoration: underline;
}
.error-message {
    color: #b3261e;
    text-align: center;
    margin-bottom: 15px;
    font-weight: bold;
}
.success-message {
    color: #1d6f42;
    text-align: center;
    margin-bottom: 15px;
    font-weight: bold;
}
</style>
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<div class="login-container">
<h2>Login</h2>

<?php
if ($message != "") {
    $cls = ($messageType === "success") ? "success-message" : "error-message";
    echo "<div class='" . $cls . "'>" . htmlspecialchars($message) . "</div>";
}
?>

<form method="post">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <div class="forgot-link"><a href="forgot_password.php">Forgot Password?</a></div>
    <button type="submit">Login</button>

    <div class="register-link">
        <p>Don't have an account? <a href="rform.php">Register here</a></p>
    </div>
</form>

</div>

</body>
</html>

