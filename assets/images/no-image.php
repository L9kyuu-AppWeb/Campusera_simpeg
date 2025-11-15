<?php
// Generate a simple placeholder image
header('Content-Type: image/png');

// Create a 200x200 transparent image
$image = imagecreate(200, 200);

// Allocate colors
$background = imagecolorallocate($image, 200, 200, 200); // Light gray
$text_color = imagecolorallocate($image, 100, 100, 100); // Dark gray

// Fill the background
imagefill($image, 0, 0, $background);

// Add text
$text = 'No Image';
$font_size = 5;
$text_width = imagefontwidth($font_size) * strlen($text);
$x = (200 - $text_width) / 2;
$y = (200 - imagefontheight($font_size)) / 2;

imagestring($image, $font_size, $x, $y, $text, $text_color);

// Output the image
imagepng($image);

// Free up memory
imagedestroy($image);
?>