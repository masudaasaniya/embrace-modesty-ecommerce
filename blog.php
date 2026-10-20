<?php
session_start();

// Feedback form handling
$feedbackMessage = "";
$feedbackType = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_feedback'])) {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validation
    $errors = array();
    
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($message)) {
        $errors[] = "Message is required";
    }
    
    if (empty($errors)) {
        // Connect to database
        $conn = mysqli_connect("localhost", "root", "", "23bca090");
        
        if ($conn) {
            // Use prepared statement for security
            $stmt = $conn->prepare("INSERT INTO feedback (name, email, rating, message) VALUES (?, ?, 5, ?)");
            $defaultRating = 5;
            $stmt->bind_param("sss", $name, $email, $message);
            
            if ($stmt->execute()) {
                $feedbackMessage = "Thank you for your feedback!";
                $feedbackType = "success";
            } else {
                $feedbackMessage = "Error submitting feedback. Please try again.";
                $feedbackType = "error";
            }
            
            $stmt->close();
            mysqli_close($conn);
        } else {
            $feedbackMessage = "Database connection error.";
            $feedbackType = "error";
        }
    } else {
        $feedbackMessage = implode("<br>", $errors);
        $feedbackType = "error";
    }
}

// Fetch latest feedback messages (latest 3)
$recentFeedback = array();
$conn = mysqli_connect("localhost", "root", "", "23bca090");
if ($conn) {
    $result = mysqli_query($conn, "SELECT * FROM feedback ORDER BY created_at DESC LIMIT 3");
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            $recentFeedback[] = $row;
        }
    }
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Blog - HAYA COLLECTION</title>
<link rel="icon" type="image/x-icon" href="skaff1.jpg">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans:wght@400;600;700&display=swap">
<link rel="stylesheet" href="assets/css/blog.css">
<link rel="stylesheet" href="/php%20e-com/style.css">
<style>
/* Feedback Form Styles */
.feedback-section {
    max-width: 800px;
    margin: 50px auto;
    padding: 30px;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 8px 30px rgba(130, 60, 73, 0.15);
}

.feedback-section h2 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 28px;
    margin-bottom: 20px;
    text-align: center;
}

.feedback-form .form-group {
    margin-bottom: 20px;
}

.feedback-form label {
    display: block;
    margin-bottom: 8px;
    color: #333;
    font-weight: 600;
}

.feedback-form input,
.feedback-form textarea {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease;
    font-family: inherit;
}

.feedback-form input:focus,
.feedback-form textarea:focus {
    outline: none;
    border-color: #823c49;
}

.feedback-form textarea {
    min-height: 120px;
    resize: vertical;
}

.feedback-form .submit-btn {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #823c49 0%, #a64d5c 100%);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.feedback-form .submit-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(122, 62, 62, 0.3);
}

.feedback-message {
    padding: 15px;
    border-radius: 10px;
    margin-bottom: 20px;
    text-align: center;
}

.feedback-message.success {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.feedback-message.error {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Recent Feedback Styles */
.recent-feedback {
    max-width: 800px;
    margin: 40px auto;
    padding: 30px;
    background: #f8ecef;
    border-radius: 15px;
}

.recent-feedback h3 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 24px;
    margin-bottom: 20px;
    text-align: center;
}

.feedback-item {
    background: #fff;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 15px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
}

.feedback-item:last-child {
    margin-bottom: 0;
}

.feedback-item .feedback-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.feedback-item .feedback-name {
    font-weight: 600;
    color: #333;
    font-size: 16px;
}

.feedback-item .feedback-date {
    color: #888;
    font-size: 12px;
}

.feedback-item .feedback-message-text {
    color: #555;
    line-height: 1.6;
}

.no-feedback {
    text-align: center;
    color: #888;
    padding: 20px;
}
</style>
</head>
<body>

<header class="header">
<h1><span class="brand">HAYA</span><small style="display:block;font-size:12px;letter-spacing:3px;font-family:Poppins,sans-serif;color:#7A3E3E;margin-top:-6px;">Collection</small></h1>
<div class="nav-right">
<nav>
<ul>
<li><a href="homepage2.php">Home</a></li>
<li><a href="shoppage2.php">Shop</a></li>
<li><a href="shoppage2.php">Collection</a></li>
<li><a href="blog.php">Blog</a></li>
<li><a href="blogpage.php">Contact</a></li>
<?php
if(isset($_SESSION['usertype'])){
    if($_SESSION['usertype'] === 'admin'){
        echo '<li><a href="myaccount2.php" class="icon-btn">👤</a></li>';
    } else {
        echo '<li><a href="myaccount.php" class="icon-btn">👤</a></li>';
    }
} else {
    echo '<li><a href="loginform.php" class="icon-btn">👤</a></li>';
}
?>
</ul>
</nav>
</div>
</header>

<section class="hero-banner">
<h1>Modest Fashion Journal</h1>
<p>Practical hijab styling advice, color ideas, and simple beauty techniques curated for everyday modest wear.</p>
</section>

<!-- START: Latest From Haya Collection Section -->
<section id="embrace-modesty-section" class="blog-cards-section">
    <h2 style="font-family: 'Playfair Display', serif; color: #7A3E3E; font-size: 30px; margin: 0 0 22px; text-align: center; position: relative; display: inline-block; padding-bottom: 8px; width: 100%;">
        Latest From Haya Collection
        <span style="position: absolute; left: 0; right: 0; bottom: 0; margin: auto; width: 70%; height: 2px; background: #C9A86C;"></span>
    </h2>
    
    <div class="blog-cards-grid">
        
        <!-- Blog Card 1: 6 Easy Hijab Hacks -->
        <article class="blog-card-item">
            <div class="blog-card-image">
                <img src="haks.jpeg" alt="6 Easy Hijab Hacks">
            </div>
            <div class="blog-card-content">
                <h3>6 Easy Hijab Hacks</h3>
                <p>Simple and practical hijab styling hacks every modest fashion lover should know.</p>
                <a href="#" class="blog-read-more">Read More &rarr;</a>
            </div>
        </article>

        <!-- Blog Card 2: Face Shapes -->
        <article class="blog-card-item">
            <div class="blog-card-image">
                <img src="tone7.jpeg" alt="Face Shapes">
            </div>
            <div class="blog-card-content">
                <h3>Face Shapes</h3>
                <p>Learn how different hijab styles complement different face shapes.</p>
                <a href="#" class="blog-read-more">Read More &rarr;</a>
            </div>
        </article>

        <!-- Blog Card 3: Colour Theory (with 3-image grid) -->
        <article class="blog-card-item">
            <div class="blog-card-image-grid">
                <div class="image-grid-item"><img src="skaff19.jpeg" alt="Colour Theory 1"></div>
                <div class="image-grid-item"><img src="skaff21.jpeg" alt="Colour Theory 2"></div>
                <div class="image-grid-item"><img src="skaff20.jpeg" alt="Colour Theory 3"></div>
            </div>
            <div class="blog-card-content">
                <h3>Colour Theory</h3>
                <p>Discover the art of color coordination for your modest wardrobe.</p>
                <a href="#" class="blog-read-more">Read More &rarr;</a>
            </div>
        </article>

    </div>
</section>

<!-- Scoped CSS for Blog Cards Section Only -->
<style>
.blog-cards-section {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
    font-family: 'Poppins', sans-serif;
}

.blog-cards-section .blog-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, minmax(0, 1fr));
    gap: 22px;
    align-items: stretch;
}

.blog-cards-section .blog-card-item {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(130, 60, 73, 0.14);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    display: flex;
    flex-direction: column;
}

.blog-cards-section .blog-card-item:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(130, 60, 73, 0.2);
}

.blog-cards-section .blog-card-image {
    width: 100%;
    height: 220px;
    overflow: hidden;
}

.blog-cards-section .blog-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.blog-cards-section .blog-card-image-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 4px;
    padding: 4px;
    background: #f0dfe2;
}

.blog-cards-section .blog-card-image-grid .image-grid-item {
    position: relative;
    width: 100%;
    height: 106px;
    overflow: hidden;
}

.blog-cards-section .blog-card-image-grid .image-grid-item:first-child {
    border-radius: 10px 0 0 0;
}

.blog-cards-section .blog-card-image-grid .image-grid-item:last-child {
    border-radius: 0 10px 0 0;
}

.blog-cards-section .blog-card-image-grid img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.blog-cards-section .blog-card-content {
    padding: 16px;
    flex: 1;
    display: flex;
    flex-direction: column;
}

.blog-cards-section .blog-card-content h3 {
    margin: 0 0 9px;
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 23px;
}

.blog-cards-section .blog-card-content p {
    margin: 0 0 16px;
    color: #5f5658;
    line-height: 1.6;
    font-size: 14px;
    flex: 1;
}

.blog-cards-section .blog-read-more {
    display: inline-block;
    background: #7A3E3E;
    color: #fff;
    border-radius: 6px;
    padding: 10px 16px;
    font-weight: 700;
    font-size: 14px;
    text-decoration: none;
    align-self: flex-start;
    transition: background 0.25s ease;
}

.blog-cards-section .blog-read-more:hover {
    background: #5a2d2d;
}

/* Responsive Styles */
@media (max-width: 1024px) {
    .blog-cards-section .blog-cards-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }
    .blog-cards-section .blog-cards-grid .blog-card-item:last-child {
        grid-column: span 2;
    }
}
@media (max-width: 768px) {
    .blog-cards-section .blog-cards-grid {
        grid-template-columns: 1fr;
    }
    .blog-cards-section .blog-cards-grid .blog-card-item:last-child {
        grid-column: span 1;
    }
    .blog-cards-section h2 {
        font-size: 26px !important;
    }
}
</style>
<!-- END: Latest From Embrace Modesty Section -->

<main class="container">
<h2 class="section-title">Latest Blog Guides</h2>
<section class="blog-grid">
<article class="blog-card">
<img src="blog1.jpg" alt="6 Easy Hijab Hacks">
<div class="blog-card-body">
<h3>6 Easy Hijab Hacks</h3>
<p>Quick styling tricks to keep your hijab secure, neat, and comfortable from morning to evening.</p>
<a class="btn-read" href="blog-detail/hijab-hacks.php">Read More →</a>
</div>
</article>

<article class="blog-card">
<img src="home.jpg" alt="Hijab for Different Face Shapes">
<div class="blog-card-body">
<h3>Hijab for Different Face Shapes</h3>
<p>Find flattering wraps and drapes for round, oval, square, and heart face structures.</p>
<a class="btn-read" href="blog-detail/face-shape-hijab.php">Read More →</a>
</div>
</article>

<article class="blog-card">
<img src="color.jpg" alt="Hijab Color Theory">
<div class="blog-card-body">
<h3>Hijab Color Theory</h3>
<p>Choose shades that complement your undertone using a simple skin-tone based color framework.</p>
<a class="btn-read" href="blog-detail/hijab-color-theory.php">Read More →</a>
</div>
</article>
</section>

<!-- Feedback Form Section -->
<div class="feedback-section">
    <h2>Leave Your Feedback</h2>
    
    <?php if (!empty($feedbackMessage)): ?>
        <div class="feedback-message <?php echo $feedbackType; ?>">
            <?php echo $feedbackMessage; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" class="feedback-form">
        <div class="form-group">
            <label for="name">Name *</label>
            <input type="text" id="name" name="name" required placeholder="Enter your name">
        </div>
        
        <div class="form-group">
            <label for="email">Email *</label>
            <input type="email" id="email" name="email" required placeholder="Enter your email">
        </div>
        
        <div class="form-group">
            <label for="message">Message *</label>
            <textarea id="message" name="message" required placeholder="Share your feedback with us..."></textarea>
        </div>
        
        <button type="submit" name="submit_feedback" class="submit-btn">Submit Feedback</button>
    </form>
</div>

<!-- Recent Feedback Section -->
<?php if (!empty($recentFeedback)): ?>
<div class="recent-feedback">
    <h3>Recent Feedback</h3>
    
    <?php foreach ($recentFeedback as $fb): ?>
    <div class="feedback-item">
        <div class="feedback-header">
            <span class="feedback-name"><?php echo htmlspecialchars($fb['name']); ?></span>
            <span class="feedback-date"><?php echo date('d M Y', strtotime($fb['created_at'])); ?></span>
        </div>
        <div class="feedback-message-text">
            <?php echo nl2br(htmlspecialchars($fb['message'])); ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</main>

<?php include 'includes/global_footer.php'; ?>

</body>
</html>

