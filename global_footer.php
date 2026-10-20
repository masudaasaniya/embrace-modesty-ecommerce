<?php
/**
 * Global Footer Include
 * Include this file before </body> tag on all pages
 * 
 * Usage: <?php include 'includes/global_footer.php'; ?>
 */
?>

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

<!-- Global Footer -->
<footer class="site-footer">
    <div class="footer-content">
        <div class="container">
            <div class="footer-grid">
                <!-- Brand Section -->
                <div class="footer-brand">
                    <h3 class="footer-logo">Haya Collection</h3>
                    <p class="footer-description">
                        Discover elegant and modest fashion that celebrates femininity with grace. 
                        Our curated collection offers premium quality hijabs, abayas, and accessories 
                        for the modern Muslim woman.
                    </p>
                    <a href="shoppage2.php" class="btn-shop-collection">Shop The Collection</a>
                </div>

                <!-- Quick Links -->
                <div class="footer-column">
                    <h4 class="footer-heading">Quick Links</h4>
                    <ul class="footer-links">
                        <li><a href="homepage2.php"><i class="fas fa-chevron-right"></i> Home</a></li>
                        <li><a href="shoppage2.php"><i class="fas fa-chevron-right"></i> Shop</a></li>
                        <li><a href="blogpage.php"><i class="fas fa-chevron-right"></i> Blog</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> About Us</a></li>
                    </ul>
                </div>

                <!-- Useful Links -->
                <div class="footer-column">
                    <h4 class="footer-heading">Useful Links</h4>
                    <ul class="footer-links">
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Order Tracking</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> FAQs</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> About</a></li>
                        <li><a href="#"><i class="fas fa-chevron-right"></i> Contact</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="footer-column">
                    <h4 class="footer-heading">Follow Us</h4>
                    <p class="social-description">Stay connected with us on social media for the latest updates and style inspiration.</p>
<div class="social-icons">
                        <a href="../instagram.php" class="social-link" title="Instagram"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link" title="Pinterest"><i class="fab fa-pinterest-p"></i></a>
                        <a href="#" class="social-link" title="Twitter"><i class="fab fa-twitter"></i></a>
                    </div>
                </div>
            </div>

            <!-- Footer Bottom -->
            <div class="footer-bottom">
                <div class="copyright">
                    &copy; <?php echo date('Y'); ?> Haya Collection. All rights reserved.
                </div>
                <div class="payment-methods">
                    <span class="payment-label">We Accept:</span>
                    <div class="payment-icons">
                        <i class="fab fa-cc-visa" title="Visa"></i>
                        <i class="fab fa-cc-mastercard" title="Mastercard"></i>
                        <i class="fab fa-cc-amex" title="American Express"></i>
                        <i class="fab fa-cc-paypal" title="PayPal"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Global Footer Styles -->
<style>
/* ============================================
   GLOBAL FOOTER STYLES
   Matches website color palette
   ============================================ */

.site-footer {
    background: #2D2D2D;
    color: #fff;
    padding: 60px 0 25px;
    margin-top: 60px;
}

.footer-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.footer-grid {
    display: grid;
    grid-template-columns: 1.5fr 1fr 1fr 1fr;
    gap: 50px;
    margin-bottom: 40px;
}

/* Brand Section */
.footer-logo {
    font-family: 'Playfair Display', serif;
    font-size: 28px;
    font-weight: 700;
    color: #C9A86A;
    margin-bottom: 15px;
}

.footer-description {
    font-size: 14px;
    color: #bbb;
    line-height: 1.7;
    margin-bottom: 20px;
}

.btn-shop-collection {
    display: inline-block;
    padding: 12px 28px;
    background: #C9A86A;
    color: #fff;
    font-size: 13px;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 1px;
    border-radius: 4px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-shop-collection:hover {
    background: #9FB7A6;
    color: #fff;
    transform: translateY(-2px);
    text-decoration: none;
}

/* Footer Columns */
.footer-heading {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 600;
    color: #fff;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-heading::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 40px;
    height: 2px;
    background: #C9A86A;
}

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 12px;
}

.footer-links a {
    color: #bbb;
    font-size: 14px;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
}

.footer-links a:hover {
    color: #C9A86A;
    padding-left: 5px;
}

.footer-links a i {
    font-size: 10px;
    color: #C9A86A;
}

/* Social Section */
.social-description {
    font-size: 14px;
    color: #bbb;
    margin-bottom: 20px;
    line-height: 1.6;
}

.social-icons {
    display: flex;
    gap: 12px;
}

.social-link {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    background: rgba(255,255,255,0.1);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 18px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.social-link:hover {
    background: #C9A86A;
    color: #fff;
    transform: translateY(-3px);
    text-decoration: none;
}

/* Footer Bottom */
.footer-bottom {
    border-top: 1px solid rgba(255,255,255,0.1);
    padding-top: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.copyright {
    font-size: 13px;
    color: #888;
}

.payment-methods {
    display: flex;
    align-items: center;
    gap: 12px;
}

.payment-label {
    font-size: 13px;
    color: #888;
}

.payment-icons {
    display: flex;
    gap: 10px;
}

.payment-icons i {
    font-size: 22px;
    color: #888;
}

/* Responsive Styles */
@media (max-width: 992px) {
    .footer-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 35px;
    }
}

@media (max-width: 768px) {
    .site-footer {
        padding: 45px 0 20px;
    }
    
    .footer-grid {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .footer-brand {
        text-align: center;
    }
    
    .footer-logo {
        font-size: 24px;
    }
    
    .footer-description {
        max-width: 100%;
    }
    
    .btn-shop-collection {
        width: 100%;
        text-align: center;
    }
    
    .footer-heading {
        text-align: center;
    }
    
    .footer-heading::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .footer-links {
        text-align: center;
    }
    
    .footer-links a {
        justify-content: center;
    }
    
    .social-icons {
        justify-content: center;
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
    .footer-logo {
        font-size: 22px;
    }
    
    .social-link {
        width: 38px;
        height: 38px;
        font-size: 16px;
    }
    
    .btn-shop-collection {
        padding: 10px 20px;
        font-size: 12px;
    }
}
</style>

