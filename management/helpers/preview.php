<?php
  if (isset($_GET['file'])) {
    $filename = basename($_GET['file']);
    $jsonPath = __DIR__. "/../notices/{$filename}.json";
    $data = json_decode(file_get_contents($jsonPath), true);
  } else {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
  }

  $bg = $data['back'];
  $image = imagecreatefrompng(__DIR__."/../backgrounds/".$bg.".png");
  $black      = imagecolorallocate($image, 0, 0, 0);
  $dark_gray  = imagecolorallocate($image, 85, 85, 85);
  $light_gray = imagecolorallocate($image, 170, 170, 170);
  $white      = imagecolorallocate($image, 255, 255, 255);
  $rows = $data['elements'];
  foreach ($rows as $el) {
    if ($el['c'] == "black") $col = $black;
    else if ($el['c'] == "dgrey") $col = $dark_gray;
    else if ($el['c'] == "lgrey") $col = $light_gray;
    else if ($el['c'] == "white") $col = $white;
    $font = __DIR__."/../fonts/".$el['f'].".ttf";
    $x = $el['x'];
    if ($el['j'] != "l") {
      $bbox = imagettfbbox($el['s'], 0, $font, $el['t']);
      $width  = $bbox[2] - $bbox[0];
      if ($el['j'] == 'c') $x = $x - (int)($width / 2);
      else $x = $x - $width;
    }
    imagettftext($image, $el['s'], 0, $x, $el['y'], $col, $font, $el['t']);
  }

  header("Content-Type: image/png");
  imagepng($image);
  imagedestroy($image);
?>