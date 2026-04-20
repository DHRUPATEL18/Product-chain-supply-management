<?php
session_start();

// Generate random captcha text
$captcha_text = '';
for ($i = 0; $i < 5; $i++) {
    $captcha_text .= chr(rand(65, 90)); // Generate random uppercase letters
}

// Store captcha text in session
$_SESSION['captcha_text'] = $captcha_text;

// Create image
$width = 120;
$height = 40;
$image = imagecreate($width, $height);

// Colors
$bg_color = imagecolorallocate($image, 240, 240, 240);
$text_color = imagecolorallocate($image, 0, 0, 0);
$line_color = imagecolorallocate($image, 200, 200, 200);

// Fill background
imagefill($image, 0, 0, $bg_color);

// Add some random lines for security
for ($i = 0; $i < 5; $i++) {
    imageline($image, rand(0, $width), rand(0, $height), rand(0, $width), rand(0, $height), $line_color);
}

// Add captcha text
$font_size = 5;
$x = ($width - strlen($captcha_text) * imagefontwidth($font_size)) / 2;
$y = ($height - imagefontheight($font_size)) / 2;

imagestring($image, $font_size, $x, $y, $captcha_text, $text_color);

// Add some noise dots
for ($i = 0; $i < 50; $i++) {
    imagesetpixel($image, rand(0, $width), rand(0, $height), $text_color);
}

// Output image
header('Content-Type: image/png');
imagepng($image);
imagedestroy($image);
?>