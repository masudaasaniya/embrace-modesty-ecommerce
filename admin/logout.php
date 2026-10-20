<?php
/**
 * Admin Logout Handler
 * Embrace Modesty E-Commerce
 */

require_once 'config/database.php';

// Destroy admin session
session_unset();
session_destroy();

// Redirect to login page
header('Location: index.php');
exit();

