<?php
session_start();
$orderNumber = isset($_SESSION['last_order_number']) ? $_SESSION['last_order_number'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Placed Successfully - Haya Collection</title>
    <link rel="icon" type="image/x-icon" href="/php%20e-com/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #fdf5f6 0%, #f8ecef 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(122, 62, 62, 0.15);
            padding: 50px;
            text-align: center;
            max-width: 500px;
            margin: 20px;
        }
        .success-icon {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            animation: scaleIn 0.5s ease-out;
        }
        @keyframes scaleIn {
            0% { transform: scale(0); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .success-icon i {
            font-size: 50px;
            color: white;
        }
        .success-title {
            color: #7a3e3e;
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        .success-message {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .order-number {
            background: #f8ecef;
            padding: 15px 25px;
            border-radius: 10px;
            display: inline-block;
            margin-bottom: 30px;
        }
        .order-number span {
            color: #7a3e3e;
            font-weight: 700;
            font-size: 18px;
        }
        .btn-shop {
            background: #7a3e3e;
            color: white;
            padding: 15px 40px;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        .btn-shop:hover {
            background: #623232;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(122, 62, 62, 0.3);
            color: white;
        }
        .features {
            margin-top: 40px;
            padding-top: 30px;
            border-top: 1px solid #eee;
        }
        .feature-item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #666;
        }
        .feature-item i {
            color: #7a3e3e;
        }
        @media (max-width: 480px) {
            .success-card {
                padding: 30px 20px;
            }
            .success-title {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

    <div class="success-card">
        <div class="success-icon">
            <i class="fas fa-check"></i>
        </div>
        
        <h1 class="success-title">Order Placed Successfully!</h1>
        
        <p class="success-message">
            Thank you for shopping with Haya Collection.<br>
            Your order has been confirmed and will be delivered soon.
        </p>
        
        <?php if (!empty($orderNumber)): ?>
        <div class="order-number">
            Order Number: <span><?php echo htmlspecialchars($orderNumber); ?></span>
        </div>
        <?php endif; ?>
        
        <a href="shoppage2.php" class="btn-shop">
            Continue Shopping
        </a>
        
        <div class="features">
            <div class="feature-item">
                <i class="fas fa-truck"></i>
                <span>Free Delivery on Orders above ₹500</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-undo"></i>
                <span>Easy 7-Day Returns</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-headset"></i>
                <span>24/7 Customer Support</span>
            </div>
        </div>
    </div>

</body>
</html>

