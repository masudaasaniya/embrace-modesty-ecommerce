<?php
/**
 * Instagram Section Component
 * Include this on any page to display Instagram feed
 * 
 * Usage: include 'includes/instagram_section.php';
 * 
 * Note: This uses local static images as placeholder.
 * To connect to Instagram API, you would need to:
 * 1. Register a Facebook Developer account
 * 2. Create an Instagram Basic Display API app
 * 3. Get access token and implement the API calls
 */

// Default local Instagram images
$instaImages = array(
    'insta1.jpg',
    'insta2.jpg', 
    'insta3.jpg',
    'insta4.jpg',
    'insta5.jpg'
);

// Function to check if image exists
function instaImageExists($image) {
    $paths = array(
        $image,
        'images/' . $image,
        'assets/' . $image,
        '../' . $image,
        './' . $image
    );
    
    foreach ($paths as $path) {
        if (file_exists($path) && is_file($path)) {
            return $path;
        }
    }
    return false;
}

// Get available images
$availableImages = array();
foreach ($instaImages as $img) {
    $found = instaImageExists($img);
    if ($found) {
        $availableImages[] = $found;
    }
}

// Fallback placeholder images (using existing product images)
if (count($availableImages) < 5) {
    $fallbackImages = array('skaff1.jpg', 'burkha1.jpeg', 'cap1.jpg', 'skaff5.jpg', 'burkha5.jpeg');
    foreach ($fallbackImages as $img) {
        if (count($availableImages) >= 5) break;
        if (!in_array($img, $availableImages) && file_exists($img)) {
            $availableImages[] = $img;
        }
    }
}
?>

<!-- Instagram Follow Section -->
<section class="insta-follow-section" style="
    background: linear-gradient(to bottom, #f8ecef, #f6e9ea);
    padding: 50px 20px;
    text-align: center;
">
    <div class="insta-container" style="
        max-width: 1200px;
        margin: 0 auto;
    ">
        <!-- Section Title -->
        <h2 style="
            font-family: 'Playfair Display', serif;
            font-size: 28px;
            color: #2D2D2D;
            margin-bottom: 10px;
            font-weight: 600;
        ">FOLLOW ON INSTAGRAM</h2>
        
        <p style="
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        ">@haya.collection</p>
        
        <!-- Instagram Images Grid -->
        <div class="insta-gallery" style="
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 15px;
        ">
            <?php if (count($availableImages) > 0): ?>
                <?php foreach ($availableImages as $index => $imagePath): ?>
                    <?php
                        $fallbackSvg = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150"><rect fill="#f8ecef" width="150" height="150"/><text x="75" y="75" text-anchor="middle" fill="#823c49" font-size="12">Image ' . ($index + 1) . '</text></svg>';
                        $fallbackDataUri = 'data:image/svg+xml;utf8,' . rawurlencode($fallbackSvg);
                    ?>
                    <a href="https://instagram.com/haya.collection" target="_blank" 
                       class="insta-image-link"
                       style="
                           display: block;
                           width: 150px;
                           height: 150px;
                           overflow: hidden;
                           border-radius: 8px;
                           position: relative;
                           transition: transform 0.3s ease, box-shadow 0.3s ease;
                       ">
                         <img src="<?php echo htmlspecialchars($imagePath); ?>" 
                             alt="Instagram <?php echo $index + 1; ?>"
                             style="
                                width: 100%;
                                height: 100%;
                                object-fit: cover;
                                transition: transform 0.3s ease;
                              "
                             onerror='this.onerror=null; this.src=<?php echo json_encode($fallbackDataUri); ?>;'
                        >
                        <!-- Instagram Overlay -->
                        <div class="insta-overlay" style="
                            position: absolute;
                            top: 0;
                            left: 0;
                            width: 100%;
                            height: 100%;
                            background: rgba(130, 60, 73, 0.7);
                            display: flex;
                            align-items: center;
                            justify-content: center;
                            opacity: 0;
                            transition: opacity 0.3s ease;
                        ">
                            <i class="fab fa-instagram" style="color: #fff; font-size: 30px;"></i>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback if no images found -->
                <?php for ($i = 1; $i <= 5; $i++): ?>
                    <div class="insta-placeholder" style="
                        width: 150px;
                        height: 150px;
                        background: #fff;
                        border-radius: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-direction: column;
                        border: 2px dashed #e8e4e4;
                    ">
                        <i class="fab fa-instagram" style="color: #823c49; font-size: 30px; margin-bottom: 5px;"></i>
                        <span style="color: #999; font-size: 11px;">Photo <?php echo $i; ?></span>
                    </div>
                <?php endfor; ?>
            <?php endif; ?>
        </div>
        
        <!-- Follow Button -->
        <a href="https://instagram.com/haya.collection" 
           target="_blank"
           style="
               display: inline-block;
               margin-top: 30px;
               padding: 14px 35px;
               background: #823c49;
               color: #fff;
               text-decoration: none;
               border-radius: 4px;
               font-weight: 500;
               font-size: 14px;
               letter-spacing: 1px;
               text-transform: uppercase;
               transition: background 0.3s ease;
           ">
            Follow @haya.collection
        </a>
    </div>
</section>

<style>
/* Instagram Section Responsive Styles */
.insta-follow-section {
    margin-top: 40px;
}

.insta-image-link:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    text-decoration: none;
}

.insta-image-link:hover .insta-overlay {
    opacity: 1 !important;
}

.insta-image-link:hover img {
    transform: scale(1.1);
}

@media (max-width: 768px) {
    .insta-gallery {
        gap: 10px !important;
    }
    
    .insta-image-link,
    .insta-placeholder {
        width: 100px !important;
        height: 100px !important;
    }
    
    .insta-follow-section h2 {
        font-size: 22px !important;
    }
}

@media (max-width: 480px) {
    .insta-image-link,
    .insta-placeholder {
        width: 80px !important;
        height: 80px !important;
    }
    
    .insta-gallery {
        gap: 8px !important;
    }
}
</style>

