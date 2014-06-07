<?php 
header("Content-type:image/png");
$num=$_GET['randNum'];
//$num=200465;
$img=imagecreate(100,25);
imagecolorallocate($img,220,200,200);

for($i=0;$i<100;$i++){
	$color=imagecolorallocate($img,rand(20,255),rand(150,255),rand(150,255));
	imagesetpixel($img,rand(0,40),rand(0,20),$color);
	}
$color=imagecolorallocate($img,rand(0,90),rand(200,900),rand(0,90));
imagestring($img,4,2,3,$num,$color);
imagepng($img);
imagedestroy($img);
?>
