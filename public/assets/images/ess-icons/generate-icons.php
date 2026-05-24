<?php
/**
 * ESS Portal PWA Icon Generator
 * 
 * Run this script from command line or browser to generate PWA icons
 * Requires GD extension to be enabled
 * 
 * Usage: php generate-icons.php
 */

$sizes = [72, 96, 128, 144, 152, 180, 192, 384, 512];
$outputDir = __DIR__;

// Colors
$gradientStart = [99, 102, 241];  // #6366f1
$gradientEnd = [79, 70, 229];      // #4f46e5

foreach ($sizes as $size) {
    $image = imagecreatetruecolor($size, $size);
    
    // Enable alpha blending
    imagealphablending($image, true);
    imagesavealpha($image, true);
    
    // Create gradient background
    for ($y = 0; $y < $size; $y++) {
        $ratio = $y / $size;
        $r = (int)($gradientStart[0] + ($gradientEnd[0] - $gradientStart[0]) * $ratio);
        $g = (int)($gradientStart[1] + ($gradientEnd[1] - $gradientStart[1]) * $ratio);
        $b = (int)($gradientStart[2] + ($gradientEnd[2] - $gradientStart[2]) * $ratio);
        $color = imagecolorallocate($image, $r, $g, $b);
        imageline($image, 0, $y, $size, $y, $color);
    }
    
    // Draw rounded corners (simple approach)
    $cornerRadius = (int)($size * 0.15);
    $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
    
    // Top-left corner
    imagefilledarc($image, $cornerRadius, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, 180, 270, $transparent, IMG_ARC_PIE);
    // Top-right corner
    imagefilledarc($image, $size - $cornerRadius - 1, $cornerRadius, $cornerRadius * 2, $cornerRadius * 2, 270, 360, $transparent, IMG_ARC_PIE);
    // Bottom-left corner
    imagefilledarc($image, $cornerRadius, $size - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, 90, 180, $transparent, IMG_ARC_PIE);
    // Bottom-right corner
    imagefilledarc($image, $size - $cornerRadius - 1, $size - $cornerRadius - 1, $cornerRadius * 2, $cornerRadius * 2, 0, 90, $transparent, IMG_ARC_PIE);
    
    $white = imagecolorallocate($image, 255, 255, 255);
    $whiteAlpha = imagecolorallocatealpha($image, 255, 255, 255, 30);
    
    // Draw user icon (head - circle)
    $headCenterX = (int)($size * 0.5);
    $headCenterY = (int)($size * 0.35);
    $headRadius = (int)($size * 0.14);
    imagefilledellipse($image, $headCenterX, $headCenterY, $headRadius * 2, $headRadius * 2, $white);
    
    // Draw user icon (body - arc)
    $bodyCenterX = (int)($size * 0.5);
    $bodyCenterY = (int)($size * 0.85);
    $bodyWidth = (int)($size * 0.45);
    $bodyHeight = (int)($size * 0.4);
    imagefilledellipse($image, $bodyCenterX, $bodyCenterY, $bodyWidth, $bodyHeight, $white);
    
    // Add "ESS" text at bottom for larger sizes
    if ($size >= 128) {
        $fontSize = (int)($size * 0.08);
        $textX = (int)($size * 0.35);
        $textY = (int)($size * 0.92);
        imagestring($image, 5, $textX, $textY, "ESS", $whiteAlpha);
    }
    
    // Save the image
    $filename = $outputDir . '/icon-' . $size . 'x' . $size . '.png';
    imagepng($image, $filename);
    imagedestroy($image);
    
    echo "Generated: icon-{$size}x{$size}.png\n";
}

echo "\nAll icons generated successfully!\n";
