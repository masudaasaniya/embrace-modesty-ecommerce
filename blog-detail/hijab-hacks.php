<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>6 Easy Hijab Hacks - HAYA COLLECTION</title>
<link rel="icon" type="image/x-icon" href="../skaff1.jpg">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=Open+Sans:wght@400;600;700&display=swap">
<link rel="stylesheet" href="../assets/css/blog.css">
<link rel="stylesheet" href="/php%20e-com/style.css">
</head>
<body>

<header class="header">
<h1><span class="brand">HAYA</span><small style="display:block;font-size:12px;letter-spacing:3px;font-family:Poppins,sans-serif;color:#7A3E3E;margin-top:-6px;">Collection</small></h1>
<div class="nav-right">
<nav>
<ul>
<li><a href="../homepage2.php">Home</a></li>
<li><a href="../shoppage2.php">Shop</a></li>
<li><a href="../blog.php">Blog</a></li>
<li><a href="../feedback.php">Feedback</a></li>
<?php
if(isset($_SESSION['usertype'])){
    if($_SESSION['usertype'] === 'admin'){
        echo '<li><a href="../myaccount2.php" class="icon-btn">👤</a></li>';
    } else {
        echo '<li><a href="../myaccount.php" class="icon-btn">👤</a></li>';
    }
} else {
    echo '<li><a href="../loginform.php" class="icon-btn">👤</a></li>';
}
?>
</ul>
</nav>
</div>
</header>

<section class="hero-banner">
<h1>6 Easy Hijab Hacks</h1>
<p>Practical techniques for secure, elegant, and quick hijab styling.</p>
</section>

<!-- Back Button -->
<button onclick="history.back()" class="hijab-back-btn">← Back</button>

<main class="container detail-section hijab-hacks-page">



<!-- Video Grid -->
<section class="video-grid">
    <article class="video-card">
        <div class="video-wrapper" onclick="openVideoModal('../v1.mp4')">
            <video controls preload="metadata" poster="../upper.jpg">
                <source src="../v1.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="play-overlay"><span>&#9658;</span></div>
        </div>
        <div class="video-info">
            <h3>Hack 1: No-Slip Hijab Trick</h3>
            <p>Use a breathable underscarf and gently press the front edge into place before wrapping.</p>
        </div>
    </article>

    <article class="video-card">
        <div class="video-wrapper" onclick="openVideoModal('../v5.mp4')">
            <video controls preload="metadata" poster="../upper.jpg">
                <source src="../v5.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="play-overlay"><span>&#9658;</span></div>
        </div>
        <div class="video-info">
            <h3>Hack 2: Secure Pin Method</h3>
            <p>Use one magnet pin under the chin and one safety pin on the shoulder fold.</p>
        </div>
    </article>

    <article class="video-card">
        <div class="video-wrapper" onclick="openVideoModal('../v12.mp4')">
            <video controls preload="metadata" poster="../upper.jpg">
                <source src="../v12.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="play-overlay"><span>&#9658;</span></div>
        </div>
        <div class="video-info">
            <h3>Hack 3: Quick Everyday Style</h3>
            <p>Fold your scarf into a long rectangle and wrap once around the neck with one side longer.</p>
        </div>
    </article>

    <article class="video-card">
        <div class="video-wrapper" onclick="openVideoModal('../v9.mp4')">
            <video controls preload="metadata" poster="../upper.jpg">
                <source src="../v9.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="play-overlay"><span>&#9658;</span></div>
        </div>
        <div class="video-info">
            <h3>Hack 4: Volume Hijab Hack</h3>
            <p>Place a soft volumizing scrunchie under your underscarf at the crown.</p>
        </div>
    </article>

    <article class="video-card">
        <div class="video-wrapper" onclick="openVideoModal('../v3.mp4')">
            <video controls preload="metadata" poster="../upper.jpg">
                <source src="../v3.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="play-overlay"><span>&#9658;</span></div>
        </div>
        <div class="video-info">
            <h3>Hack 5: Under Cap Styling</h3>
            <p>Choose a cotton under cap that matches your scarf tone for better control.</p>
        </div>
    </article>

    <article class="video-card">
        <div class="video-wrapper" onclick="openVideoModal('../v4.mp4')">
            <video controls preload="metadata" poster="../upper.jpg">
                <source src="../v4.mp4" type="video/mp4">
                Your browser does not support the video tag.
            </video>
            <div class="play-overlay"><span>&#9658;</span></div>
        </div>
        <div class="video-info">
            <h3>Hack 6: Elegant Layered Wrap</h3>
            <p>Wrap with two visible layers near the cheek line for a polished look.</p>
        </div>
    </article>
</section>

<!-- Video Modal -->
<div id="videoModal" class="video-modal" onclick="closeVideoModal(event)">
    <div class="video-modal-content" onclick="event.stopPropagation()">
        <div class="video-modal-header">
            <button class="video-back-btn" onclick="closeVideoModal(event)">
                &#8592; Back
            </button>
            <button class="video-close-btn" onclick="closeVideoModal(event)">
                &times;
            </button>
        </div>
        <video id="modalVideo" controls>
            <source src="" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    </div>
</div>

<!-- Text Explanation List -->
<section class="explanation-section">
    <h2>Summary</h2>
    <ul class="explanation-list">
        <li><strong>No-Slip Hijab Trick:</strong> Keep your hijab secure all day with this simple technique.</li>
        <li><strong>Quick Everyday Style:</strong> A two-minute daily look that's perfect for busy mornings.</li>
        <li><strong>Volume Hijab Hack:</strong> Create balanced back volume without heavy layering.</li>
        <li><strong>Secure Pin Method:</strong> Stability with clean drape using strategic pin placement.</li>
        <li><strong>Under Cap Styling:</strong> Control baby hair and improve comfort for long wear.</li>
        <li><strong>Elegant Layered Wrap:</strong> A polished look suitable for special events.</li>
    </ul>
</section>

</main>

<!-- Scoped CSS for Hijab Hacks Page Only -->
<style>
.hijab-hacks-page {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

/* Back Button */
.hijab-hacks-page .hijab-back-btn {
    padding: 8px 16px;
    background: #6c4ab6;
    color: #fff;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    margin-bottom: 20px;
    font-size: 14px;
}

.hijab-hacks-page .hijab-back-btn:hover {
    background: #5635a3;
}

/* Page Header */
.hijab-hacks-page .page-header {
    text-align: center;
    margin-bottom: 30px;
}

.hijab-hacks-page .page-title {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 36px;
    margin: 0 0 10px;
}

.hijab-hacks-page .page-date {
    color: #888;
    font-size: 14px;
    margin: 0;
}

/* Video Grid */
.hijab-hacks-page .video-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 40px;
}

/* Video Card */
.hijab-hacks-page .video-card {
    background: #ffffff;
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(130, 60, 73, 0.14);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    cursor: pointer;
}

.hijab-hacks-page .video-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 14px 30px rgba(130, 60, 73, 0.2);
}

/* Video Wrapper */
.hijab-hacks-page .video-wrapper {
    width: 100%;
    height: 200px;
    overflow: hidden;
    background: #f0dfe2;
    position: relative;
}

.hijab-hacks-page .video-wrapper video {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

/* Play Overlay */
.hijab-hacks-page .play-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.hijab-hacks-page .video-card:hover .play-overlay {
    opacity: 1;
}

.hijab-hacks-page .play-overlay span {
    font-size: 50px;
    color: #fff;
    text-shadow: 0 2px 4px rgba(0,0,0,0.3);
}

/* Video Info */
.hijab-hacks-page .video-info {
    padding: 16px;
}

.hijab-hacks-page .video-info h3 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 18px;
    margin: 0 0 8px;
}

.hijab-hacks-page .video-info p {
    color: #5f5658;
    font-size: 14px;
    line-height: 1.5;
    margin: 0;
}

/* Video Modal */
.hijab-hacks-page .video-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.85);
    z-index: 9999;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.hijab-hacks-page .video-modal.active {
    display: flex;
    opacity: 1;
}

.hijab-hacks-page .video-modal-content {
    position: relative;
    width: 90%;
    max-width: 900px;
    background: #000;
    border-radius: 10px;
    overflow: hidden;
    transform: scale(0.9);
    transition: transform 0.3s ease;
}

.hijab-hacks-page .video-modal.active .video-modal-content {
    transform: scale(1);
}

.hijab-hacks-page .video-modal-content video {
    width: 100%;
    height: auto;
    display: block;
    aspect-ratio: 16 / 9;
}

/* Video Modal Header */
.hijab-hacks-page .video-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    padding: 15px 20px;
    background: linear-gradient(to bottom, rgba(0,0,0,0.7), transparent);
    z-index: 10001;
}

.hijab-hacks-page .video-back-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s ease;
    display: flex;
    align-items: center;
    gap: 5px;
}

.hijab-hacks-page .video-back-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.hijab-hacks-page .video-close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: #fff;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    font-size: 24px;
    cursor: pointer;
    transition: background 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

.hijab-hacks-page .video-close-btn:hover {
    background: rgba(255, 255, 255, 0.4);
}

/* Explanation Section */
.hijab-hacks-page .explanation-section {
    background: #ffffff;
    border-radius: 14px;
    padding: 24px;
    box-shadow: 0 8px 24px rgba(130, 60, 73, 0.14);
}

.hijab-hacks-page .explanation-section h2 {
    font-family: 'Playfair Display', serif;
    color: #7A3E3E;
    font-size: 24px;
    margin: 0 0 16px;
}

.hijab-hacks-page .explanation-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.hijab-hacks-page .explanation-list li {
    padding: 12px 0;
    border-bottom: 1px solid #f0dfe2;
    color: #5f5658;
    font-size: 15px;
    line-height: 1.6;
}

.hijab-hacks-page .explanation-list li:last-child {
    border-bottom: none;
}

.hijab-hacks-page .explanation-list li strong {
    color: #7A3E3E;
}

/* Responsive */
@media (max-width: 1024px) {
    .hijab-hacks-page .video-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .hijab-hacks-page .video-grid {
        grid-template-columns: 1fr;
    }
    .hijab-hacks-page .video-wrapper {
        height: 180px;
    }
    .hijab-hacks-page .page-title {
        font-size: 28px;
    }
    .hijab-hacks-page .video-modal-content {
        width: 95%;
    }
}
</style>

<!-- JavaScript for Video Modal -->
<script>
function openVideoModal(videoSrc) {
    var modal = document.getElementById('videoModal');
    var modalVideo = document.getElementById('modalVideo');
    modalVideo.src = videoSrc;
    modal.classList.add('active');
    modalVideo.play();
}

function closeVideoModal(event) {
    var modal = document.getElementById('videoModal');
    var modalVideo = document.getElementById('modalVideo');
    modal.classList.remove('active');
    modalVideo.pause();
    modalVideo.src = '';
}

// Close modal on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        var modal = document.getElementById('videoModal');
        if (modal.classList.contains('active')) {
            var modalVideo = document.getElementById('modalVideo');
            modal.classList.remove('active');
            modalVideo.pause();
            modalVideo.src = '';
        }
    }
});
</script>

<?php include '../includes/global_footer.php'; ?>

</body>
</html>



