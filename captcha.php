<?php
session_start();
header("Content-type: image/png");

$nbr1 = rand(1, 9);
$nbr2 = rand(1, 9);

$_SESSION['captcha_code'] = ($nbr1 + $nbr2);

// From http://www.webcheatsheet.com/PHP/create_captcha_protection.php

//Set the image width and height 
$width = 200; 
$height = 50;  

//Create the image resource 
$image = ImageCreate($width, $height);  

//We are making three colors, white, black and gray
/* 
$white = ImageColorAllocate($image, 255, 255, 255); 
$black = ImageColorAllocate($image, 0, 0, 0); 
*/
$grey = ImageColorAllocate($image, 204, 204, 204);

$colors = array("red" => array(255, 0, 0), "green" => array(0, 255, 0), "blue" => array(0,0,244),
				"black" => array(0,0,0),"white" => array(255,255,255), 
				"orange" => array(237,127,16), "cyan" => array(43,250,250));
$background_color = array("white", "black", "blue", "orange", "green", "red");
$font_color = array("black", "white", "orange", "blue", "red", "green");

$c = rand(0, count($font_color) - 1);

$b = $background_color[$c];
$f = $font_color[$c];

$bck = imagecolorallocate($image, $colors[$b][0],$colors[$b][1],$colors[$b][2]);
$fnt = imagecolorallocate($image, $colors[$f][0],$colors[$f][1],$colors[$f][2]);
//Make the background black 
ImageFill($image, 0, 0, $bck); 

$size = rand(16,30);
Imagettftext($image, $size, rand(-50, 50), rand(10, ($width/2) - $size), rand($size + 10, $height- $size), $fnt, "./font/comic.ttf", $nbr1); 

$size = rand(16,30);
Imagettftext($image, $size, rand(-50, 50), rand($width/2, $width - 15), rand($size + 10, $height- $size), $fnt, "./font/comic.ttf", $nbr2); 

$grey = ImageColorAllocate($image, 204, 204, 204); 

//ImageRectangle($image,0,0,$width-10,$height-10,$grey); 
imageline($image, 0, $height/2, $width, $height/2, $grey); 
imageline($image, $width/2, 0, $width/2, $height, $grey); 
$nbrLines = rand(2,4);

for($i = 0 ; $i <= $nbrLines ; $i++)
{
	$b = rand(0,1) == 0;
	
	if($b)
	{
		$x1 = 0;
		$y1 = rand(0, $height);
	}
	else
	{
		$x1 = rand(0, $width);
		$y1 = 0;
	}
	
	$b = rand(0,1) == 0;

	if($b)
	{
		$x2 = $width;
		$y2 = rand(0, $height);
	}
	else
	{
		$x2 = rand(0,$width);
		$y2 = $height;
	}
	imageline($image, $x1,$y1,$x2,$y2,$fnt);
}
	
imagepng($image);
imagedestroy($image)
?>