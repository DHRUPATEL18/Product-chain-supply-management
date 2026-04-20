<?php
session_start();

// Generate random 5-character captcha text (A-Z, 0-9 avoiding similar chars)
$alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captchaText = '';
for ($i = 0; $i < 5; $i++) {
    $captchaText .= $alphabet[random_int(0, strlen($alphabet) - 1)];
}

// Store in session for later validation
$_SESSION['captcha_text'] = $captchaText;

// Output SVG (no GD dependency)
header('Content-Type: image/svg+xml');

// Simple noise generator
$width = 140;
$height = 48;
$bgColor = '#F0F0F0';
$textColor = '#111111';
$noiseColor = '#C8C8C8';

// Slight per-character offsets and rotation
$charSpacing = 24; // space between chars
$startX = 16;
$baseY = 32;

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>";
echo "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$width}\" height=\"{$height}\" viewBox=\"0 0 {$width} {$height}\">";
echo "<rect x=\"0\" y=\"0\" width=\"{$width}\" height=\"{$height}\" fill=\"{$bgColor}\"/>";

// Noise lines
for ($i = 0; $i < 6; $i++) {
    $x1 = random_int(0, $width);
    $y1 = random_int(0, $height);
    $x2 = random_int(0, $width);
    $y2 = random_int(0, $height);
    $opacity = 0.6;
    echo "<line x1=\"{$x1}\" y1=\"{$y1}\" x2=\"{$x2}\" y2=\"{$y2}\" stroke=\"{$noiseColor}\" stroke-width=\"1\" opacity=\"{$opacity}\"/>";
}

// Text with slight transforms
for ($i = 0; $i < strlen($captchaText); $i++) {
    $char = htmlspecialchars($captchaText[$i], ENT_QUOTES, 'UTF-8');
    $x = $startX + $i * $charSpacing + random_int(-2, 2);
    $y = $baseY + random_int(-2, 2);
    $rotate = random_int(-18, 18);
    echo "<g transform=\"translate({$x},{$y}) rotate({$rotate})\">";
    echo "<text x=\"0\" y=\"0\" fill=\"{$textColor}\" font-family=\"'Segoe UI', Tahoma, sans-serif\" font-size=\"26\" font-weight=\"700\">{$char}</text>";
    echo "</g>";
}

// Noise dots
for ($i = 0; $i < 60; $i++) {
    $cx = random_int(0, $width);
    $cy = random_int(0, $height);
    $r = random_int(1, 2);
    echo "<circle cx=\"{$cx}\" cy=\"{$cy}\" r=\"{$r}\" fill=\"{$noiseColor}\" opacity=\"0.6\"/>";
}

echo "</svg>";
exit;
?>