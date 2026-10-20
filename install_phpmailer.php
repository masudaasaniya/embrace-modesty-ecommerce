<?php
/**
 * PHPMailer Installer Script
 * Run this once to download PHPMailer files
 */

$phpmailerDir = __DIR__ . '/PHPMailer';
if (!is_dir($phpmailerDir)) {
    mkdir($phpmailerDir, 0755, true);
}

// PHPMailer source URLs (using jsDelivr CDN)
$files = [
    'https://cdn.jsdelivr.net/npm/phpmailer/phpmailer@6.8.0/src/PHPMailer.php' => 'PHPMailer.php',
    'https://cdn.jsdelivr.net/npm/phpmailer/phpmailer@6.8.0/src/SMTP.php' => 'SMTP.php',
    'https://cdn.jsdelivr.net/npm/phpmailer/phpmailer@6.8.0/src/Exception.php' => 'Exception.php'
];

foreach ($files as $url => $filename) {
    $content = file_get_contents($url);
    if ($content !== false) {
        file_put_contents($phpmailerDir . '/' . $filename, $content);
        echo "Downloaded: $filename\n";
    } else {
        echo "Failed to download: $filename\n";
    }
}

echo "\nPHPMailer installation complete!\n";

