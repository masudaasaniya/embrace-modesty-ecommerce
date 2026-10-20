<?php
/**
 * Admin Panel Header
 * Haya Collection E-Commerce
 */

require_once dirname(__FILE__) . '/../config/database.php';
require_once dirname(__FILE__) . '/functions.php';

// Check admin login
requireAdminLogin();

// Get admin data
$admin = getAdminData($_SESSION['admin_id']);
$siteName = getSetting('site_name');
$adminBaseUrl = '/php-ecom/admin/';
if (substr($adminBaseUrl, -1) !== '/') {
    $adminBaseUrl .= '/';
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Admin Panel'; ?> - <?php echo htmlspecialchars($siteName); ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 64 64'%3E%3Crect width='64' height='64' rx='12' fill='%23823c49'/%3E%3Ctext x='50%25' y='58%25' dominant-baseline='middle' text-anchor='middle' font-size='34' fill='white' font-family='Arial'%3EEM%3C/text%3E%3C/svg%3E">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.7.2/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #823c49;
            --secondary-color: #a55267;
            --light-bg: #f8ecef;
            --dark-text: #333;
            --sidebar-width: 260px;
        }
        
        body {
            font-family: 'Open Sans', sans-serif;
            background-color: #f5f5f5;
        }
        
        .brand {
            font-family: 'Playfair Display', serif;
            color: var(--primary-color);
        }
        
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, var(--primary-color) 0%, #6d2f3d 100%);
            color: white;
            overflow-y: auto;
            z-index: 1000;
        }
        
        .sidebar-brand {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-brand h3 {
            font-family: 'Playfair Display', serif;
            margin: 0;
            font-size: 22px;
        }
        
        .sidebar-menu {
            padding: 15px 0;
        }
        
        .sidebar-menu-item {
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            border-left: 3px solid transparent;
        }
        
        .sidebar-menu-item:hover,
        .sidebar-menu-item.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
        }
        
        .sidebar-menu-item i {
            width: 25px;
            margin-right: 10px;
        }
        
        .sidebar-submenu {
            padding-left: 45px;
            display: none;
        }
        
        .sidebar-submenu.show {
            display: block;
        }
        
        .sidebar-submenu-item {
            padding: 8px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            display: block;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .sidebar-submenu-item:hover {
            color: white;
        }
        
        .main-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
        }
        
        .top-header {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .top-header h4 {
            margin: 0;
            color: var(--primary-color);
            font-family: 'Playfair Display', serif;
        }
        
        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-info .name {
            font-weight: 600;
            color: var(--dark-text);
        }
        
        .user-info .role {
            font-size: 12px;
            color: #666;
        }
        
        .content-wrapper {
            padding: 30px;
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
        }
        
        .card-header {
            background: white;
            border-bottom: 2px solid var(--light-bg);
            padding: 20px 25px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .card-body {
            padding: 25px;
        }
        
        .btn-primary {
            background: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .table th {
            color: var(--primary-color);
            font-weight: 600;
            border-bottom: 2px solid var(--light-bg);
        }
        
        .alert-success {
            background: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }
        
        .alert-danger {
            background: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }
        
        .badge-active {
            background: #28a745;
            color: white;
        }
        
        .badge-inactive {
            background: #dc3545;
            color: white;
        }
        
        .badge-pending {
            background: #ffc107;
            color: #333;
        }
        
        .badge-confirmed {
            background: #17a2b8;
            color: white;
        }
        
        .badge-shipped {
            background: #6610f2;
            color: white;
        }
        
        .badge-delivered {
            background: #28a745;
            color: white;
        }
        
        .badge-cancelled {
            background: #dc3545;
            color: white;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin: 10px 0;
        }
        
        .stat-label {
            color: #666;
            font-size: 14px;
        }
        
        .page-breadcrumb {
            margin-bottom: 20px;
        }
        
        .page-breadcrumb a {
            color: var(--primary-color);
            text-decoration: none;
        }
        
        .page-breadcrumb span {
            color: #666;
        }
    </style>
<link rel="stylesheet" href="../responsive.css">
</head>
<body>

<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <h3><i class="fas fa-gem me-2"></i>Haya<br>Collection</h3>
    </div>
    
    <nav class="sidebar-menu">
        <a href="<?php echo $adminBaseUrl; ?>dashboard.php" class="sidebar-menu-item <?php echo $currentPage == 'dashboard' ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
        
        <a href="#categoriesMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('categories')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-tags"></i> Categories
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('categories')) ? 'show' : ''; ?>" id="categoriesMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/categories/index.php" class="sidebar-submenu-item">All Categories</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/categories/add.php" class="sidebar-submenu-item">Add Category</a>
        </div>
        
        <a href="#productsMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('products')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-box"></i> Products
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('products')) ? 'show' : ''; ?>" id="productsMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/products/index.php" class="sidebar-submenu-item">All Products</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/products/add.php" class="sidebar-submenu-item">Add Product</a>
        </div>
        
        <a href="#ordersMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('orders')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-shopping-cart"></i> Orders
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('orders')) ? 'show' : ''; ?>" id="ordersMenu">
            <a href="<?php echo $adminBaseUrl; ?>orders.php" class="sidebar-submenu-item">Quick Update</a>
            <a href="<?php echo $adminBaseUrl; ?>order-list.php" class="sidebar-submenu-item">Order List</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/orders/index.php" class="sidebar-submenu-item">All Orders</a>
        </div>
        
        <a href="#customersMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('customers')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-users"></i> Customers
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('customers')) ? 'show' : ''; ?>" id="customersMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/customers/index.php" class="sidebar-submenu-item">All Customers</a>
        </div>
        
        <a href="#paymentsMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('payments')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-credit-card"></i> Payments
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('payments')) ? 'show' : ''; ?>" id="paymentsMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/payments/index.php" class="sidebar-submenu-item">Payment Records</a>
        </div>
        
        <a href="#reportsMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('reports')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-chart-bar"></i> Reports
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('reports')) ? 'show' : ''; ?>" id="reportsMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/reports/index.php" class="sidebar-submenu-item">Sales Reports</a>
        </div>
        
        <a href="<?php echo $adminBaseUrl; ?>modules/feedback/index.php" class="sidebar-menu-item <?php echo $currentPage == 'feedback' ? 'active' : ''; ?>">
            <i class="fas fa-comments"></i> Feedback
        </a>
        
        <a href="#bannersMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('banners')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-images"></i> Banners
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('banners')) ? 'show' : ''; ?>" id="bannersMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/banners/index.php" class="sidebar-submenu-item">All Banners</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/banners/add.php" class="sidebar-submenu-item">Add Banner</a>
        </div>
        
        <a href="#couponsMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('coupons')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-ticket-alt"></i> Coupons
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('coupons')) ? 'show' : ''; ?>" id="couponsMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/coupons/index.php" class="sidebar-submenu-item">All Coupons</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/coupons/add.php" class="sidebar-submenu-item">Add Coupon</a>
        </div>
        
        <a href="#contentMenu" class="sidebar-menu-item <?php echo in_array($currentPage, array('content')) ? 'active' : ''; ?>" data-bs-toggle="collapse">
            <i class="fas fa-file-alt"></i> Content
            <i class="fas fa-chevron-down ms-auto"></i>
        </a>
        <div class="sidebar-submenu <?php echo in_array($currentPage, array('content')) ? 'show' : ''; ?>" id="contentMenu">
            <a href="<?php echo $adminBaseUrl; ?>modules/content/about.php" class="sidebar-submenu-item">About Us</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/content/contact.php" class="sidebar-submenu-item">Contact Info</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/content/faq.php" class="sidebar-submenu-item">FAQ</a>
            <a href="<?php echo $adminBaseUrl; ?>modules/content/pages.php" class="sidebar-submenu-item">Privacy & Terms</a>
        </div>
        
        <a href="<?php echo $adminBaseUrl; ?>modules/settings/index.php" class="sidebar-menu-item <?php echo $currentPage == 'settings' ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Settings
        </a>
        
        <a href="<?php echo $adminBaseUrl; ?>logout.php" class="sidebar-menu-item">
            <i class="fas fa-sign-out-alt"></i> Logout
        </a>
    </nav>
</aside>

<!-- Main Content -->
<main class="main-content">
    <!-- Top Header -->
    <header class="top-header">
        <h4><i class="fas fa-gem me-2"></i><?php echo isset($pageTitle) ? $pageTitle : 'Admin Panel'; ?></h4>
        
        <div class="user-dropdown">
            <div class="user-info">
                <div class="name"><?php echo htmlspecialchars($admin['username']); ?></div>
                <!-- <div class="role"><?php echo ucfirst(htmlspecialchars($admin['role'])); ?></div> -->
            </div>
            <div class="user-avatar">
                <i class="fas fa-user-circle fa-2x" style="color: var(--primary-color);"></i>
            </div>
        </div>
    </header>
    
    <!-- Content Wrapper -->
    <div class="content-wrapper">


