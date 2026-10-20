<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Haya Collection - Footer</title>
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500;600;700&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* --- RESET & BASE --- */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #823c49;
            --secondary-color: #a64d5c;
            --text-dark: #2d2d2d;
            --text-light: #6b6b6b;
            --text-muted: #999999;
            --bg-light: #faf8f8;
            --border-light: #e8e4e4;
            --white: #ffffff;
            --gold-accent: #c9a86a;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--bg-light);
            line-height: 1.6;
        }

        a {
            text-decoration: none;
            color: inherit;
            transition: color 0.3s ease;
        }

        /* --- FOOTER MAIN --- */
        .footer-section {
            background: var(--white);
            padding: 80px 0 30px;
            border-top: 1px solid var(--border-light);
        }

        .footer-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .footer-grid {
            display: grid;
            grid-template-columns: 1.5fr 1fr 1.5fr 1fr;
            gap: 60px;
            margin-bottom: 60px;
        }

        /* --- BRAND SECTION --- */
        .footer-brand .logo {
            font-family: 'Playfair Display', serif;
            font-size: 32px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .footer-brand .logo span {
            display: block;
            font-size: 11px;
            letter-spacing: 5px;
            font-family: 'Poppins', sans-serif;
            font-weight: 400;
            color: var(--text-light);
            margin-top: 5px;
        }

        .footer-brand .description {
            font-size: 14px;
            color: var(--text-light);
            line-height: 1.8;
            margin-bottom: 25px;
            max-width: 280px;
        }

        .shop-collection-btn {
            display: inline-block;
            padding: 14px 32px;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: var(--white);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            border-radius: 4px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .shop-collection-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(130, 60, 73, 0.3);
        }

        /* --- QUICK LINKS --- */
        .footer-title {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 25px;
            position: relative;
        }

        .footer-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 40px;
            height: 2px;
            background: var(--primary-color);
        }

        .quick-links {
            list-style: none;
        }

        .quick-links li {
            margin-bottom: 14px;
        }

        .quick-links a {
            font-size: 14px;
            color: var(--text-light);
            position: relative;
            padding-left: 0;
            transition: all 0.3s ease;
        }

        .quick-links a:hover {
            color: var(--primary-color);
            padding-left: 8px;
        }

        .quick-links a::before {
            content: '\f105';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            left: 0;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .quick-links a:hover::before {
            opacity: 1;
        }

        /* --- NEWSLETTER SECTION --- */
        .newsletter-description {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 25px;
            line-height: 1.7;
        }

        .newsletter-form {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-group {
            position: relative;
        }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 500;
            color: var(--text-dark);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .form-group input {
            width: 100%;
            padding: 14px 16px;
            border: 1px solid var(--border-light);
            border-radius: 4px;
            font-size: 14px;
            font-family: 'Poppins', sans-serif;
            color: var(--text-dark);
            background: var(--white);
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(130, 60, 73, 0.1);
        }

        .form-group input::placeholder {
            color: var(--text-muted);
        }

        .subscribe-btn {
            padding: 14px 32px;
            background: var(--primary-color);
            color: var(--white);
            font-size: 13px;
            font-weight: 500;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .subscribe-btn:hover {
            background: var(--secondary-color);
            transform: translateY(-2px);
        }

        /* --- FOLLOW US SECTION --- */
        .follow-description {
            font-size: 14px;
            color: var(--text-light);
            margin-bottom: 25px;
        }

        .social-icons {
            display: flex;
            gap: 15px;
        }

        .social-link {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--bg-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text-dark);
            font-size: 18px;
            transition: all 0.3s ease;
        }

        .social-link:hover {
            background: var(--primary-color);
            color: var(--white);
            transform: translateY(-4px);
        }

        /* --- BOTTOM BAR --- */
        .footer-bottom {
            border-top: 1px solid var(--border-light);
            padding-top: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }

        .copyright {
            font-size: 13px;
            color: var(--text-muted);
        }

        .copyright a {
            color: var(--primary-color);
        }

        .payment-methods {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .payment-label {
            font-size: 13px;
            color: var(--text-muted);
        }

        .payment-icons {
            display: flex;
            gap: 10px;
        }

        .payment-icons i {
            font-size: 24px;
            color: var(--text-light);
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 992px) {
            .footer-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 40px;
            }
        }

        @media (max-width: 768px) {
            .footer-section {
                padding: 50px 0 25px;
            }

            .footer-grid {
                grid-template-columns: 1fr;
                gap: 40px;
            }

            .footer-brand .description {
                max-width: 100%;
            }

            .footer-bottom {
                flex-direction: column;
                text-align: center;
            }

            .payment-methods {
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .footer-brand .logo {
                font-size: 26px;
            }

            .social-icons {
                justify-content: flex-start;
            }

            .social-link {
                width: 40px;
                height: 40px;
                font-size: 16px;
            }
        }
    </style>
</head>
<body>
    <!-- FOOTER SECTION -->
    <footer class="footer-section">
        <div class="footer-container">
            <div class="footer-grid">
                <!-- Brand Section -->
                <div class="footer-brand">
                    <div class="logo">
                        Haya Collection
                        <span>COLLECTION</span>
                    </div>
                    <p class="description">
                        Discover elegant and modest fashion that celebrates femininity with grace. Our curated collection offers premium quality hijabs, abayas, and accessories for the modern Muslim woman.
                    </p>
                    <a href="shoppage2.php" class="shop-collection-btn">Shop The Collection</a>
                </div>

                <!-- Quick Links -->
                <div class="footer-quick-links">
                    <h4 class="footer-title">Quick Links</h4>
                    <ul class="quick-links">
                        <li><a href="homepage2.php">Home</a></li>
                        <li><a href="shoppage2.php">Shop</a></li>
                        <li><a href="blogpage.php">Blog</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#">FAQ</a></li>
                    </ul>
                </div>

                <!-- Newsletter / Install App -->
                <div class="footer-newsletter">
                    <h4 class="footer-title">Newsletter</h4>
                    <p class="newsletter-description">
                        Subscribe to receive updates, access to exclusive deals, and more.
                    </p>
                    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                        <div class="form-group">
                            <label for="firstName">First Name</label>
                            <input type="text" id="firstName" placeholder="Enter your first name" required>
                        </div>
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" placeholder="Enter your email address" required>
                        </div>
                        <button type="submit" class="subscribe-btn">Subscribe</button>
                    </form>
                </div>

                <!-- Follow Us -->
                <div class="footer-follow">
                    <h4 class="footer-title">Follow Us</h4>
                    <p class="follow-description">
                        Stay connected with us on social media for the latest updates and style inspiration.
                    </p>
                    <div class="social-icons">
                        <a href="#" class="social-link" title="Instagram">
                            <i class="fa-brands fa-instagram"></i>
                        </a>
                        <a href="#" class="social-link" title="Facebook">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="#" class="social-link" title="Pinterest">
                            <i class="fa-brands fa-pinterest-p"></i>
                        </a>
                        <a href="#" class="social-link" title="Twitter">
                            <i class="fa-brands fa-twitter"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="footer-bottom">
                <p class="copyright">
                    © 2024 <a href="#">Haya Collection</a>. All rights reserved.
                </p>
                <div class="payment-methods">
                    <span class="payment-label">We Accept:</span>
                    <div class="payment-icons">
                        <i class="fa-brands fa-cc-visa" title="Visa"></i>
                        <i class="fa-brands fa-cc-mastercard" title="Mastercard"></i>
                        <i class="fa-brands fa-cc-amex" title="American Express"></i>
                        <i class="fa-brands fa-cc-paypal" title="PayPal"></i>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>
