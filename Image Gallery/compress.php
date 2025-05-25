<?php
if (!isset($_GET['img'])) exit('No image specified.');

$imgName = basename($_GET['img']);
$imgPath = './' . $imgName;

if (!file_exists($imgPath)) exit('Image not found.');

$info = getimagesize($imgPath);
header("Content-Type: " . $info['mime']);

switch ($info['mime']) {
    case 'image/jpeg':
        $image = imagecreatefromjpeg($imgPath);
        imagejpeg($image, NULL, 60); // 60% quality
        break;
    case 'image/png':
        $image = imagecreatefrompng($imgPath);
        imagejpeg($image, NULL, 60); // convert to jpeg with compression
        break;
    case 'image/webp':
        $image = imagecreatefromwebp($imgPath);
        imagejpeg($image, NULL, 60);
        break;
    default:
        readfile($imgPath); // fallback if unsupported
        break;
}

if (isset($image)) {
    imagedestroy($image);
}
?>
