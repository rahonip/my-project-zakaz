//create watermark
//$sType output format jpeg,jpg,gif,png
//$sfWatermark path to 24b – png
function SetWatermark($rImg, $sType, $sfWatermark = 'watermark.png'){
    $iDelta = 5;
    $xImg = imagesx($rImg);
    $yImg = imagesy($rImg);

    $r = imagecreatefrompng($sfWatermark);
    $x = imagesx($r);
    $y = imagesy($r);

    $xDest = $xImg – ($x + $iDelta);
    $yDest = $yImg – ($y + $iDelta);
    imageAlphaBlending($rImg,TRUE);
    imagecopy($rImg,$r, $xDest,$yDest, 0,0, $x,$y);
    if('png' == $sType) imagepng($rImg);
    if('jpeg' == $sType || 'jpg' == $sType) imagejpeg($rImg);
    if('gif' == $sType) imagegif($rImg);
    imagedestroy($r);
    imagedestroy($rImg);
}
