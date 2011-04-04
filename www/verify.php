<?php
session_start();

$new_string = "O";
while (strpos($new_string,"O") !== false || strpos($new_string,"0") !== false || strpos($new_string,"6") !== false || strpos($new_string,"G") !== false || strpos($new_string,"S") !== false || strpos($new_string,"5") !== false) {
	srand((double)microtime()*1000000); /*The seed for the random number*/
	$string = md5(rand(0,9999)); 
	$new_string = strtoupper(substr($string, 17, 5));
}

/*creates the new string. */
$_SESSION['stv18bg'] = $new_string;

$font = imageloadfont('fonts/borringlesson.gdf');

$im = imagecreate(300,60);
$bgColor = imagecolorallocate($im, 30, 30, 30);
$fgColor = imagecolorallocate($im,   255,  255, 255);

imagefill($im, 0, 0, $bgColor);
$randColor = ImageColorAllocate($im, rand(150,200), rand(150,200), rand(150,200));

imagestring($im, $font, 40, 15, $new_string, $fgColor);

header('Content-Type: image/png');
imagepng($im);
imagedestroy($im);
exit();

?>