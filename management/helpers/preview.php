<?php
  require_once 'phpqrcode.php';

  header("Content-Type: image/png");
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
  $rows = $data['elements'];
  foreach ($rows as $el) {
    if ($el['c'] == "black") $col = imagecolorallocate($image, 0, 0, 0);
    else if ($el['c'] == "dgrey") $col = imagecolorallocate($image, 85, 85, 85);
    else if ($el['c'] == "lgrey") $col = imagecolorallocate($image, 170, 170, 170);
    else if ($el['c'] == "white") $col = imagecolorallocate($image, 255, 255, 255);
    $font = __DIR__."/../fonts/".$el['f'].".ttf";
    $x = $el['x'];
    if ($el['typ'] == "t") {
      if ($el['j'] != "l") {
        $bbox = imagettfbbox($el['s'], 0, $font, $el['t']);
        $width  = $bbox[2] - $bbox[0];
        if ($el['j'] == 'c') $x = $x - (int)($width / 2);
        else $x = $x - $width;
      }
      imagettftext($image, $el['s'], 0, $x, $el['y'], $col, $font, $el['t']);
      continue;
    }
    if ($el['typ'] == "q") {
      $size = $el['s'];
      if ($size < 2) $size = 2;
      else if ($size > 8) $size = 8;

      ob_start();
      QRcode::png($el['t'], null, QR_ECLEVEL_L, $size, 0);
      $qrImage = imagecreatefromstring(ob_get_clean());
      $qrW = imagesx($qrImage);
      $qrH = imagesy($qrImage);
      $qrTrue = imagecreatetruecolor($qrW, $qrH);
      imagealphablending($qrTrue, false);
      imagesavealpha($qrTrue, true);
      imagecopy($qrTrue, $qrImage, 0, 0, 0, 0, $qrW, $qrH);
      imagedestroy($qrImage);
      if ($el['c'] == 'black') $col = imagecolorallocate($qrTrue, 0, 0, 0);
      else if ($el['c'] == 'dgrey') $col = imagecolorallocate($qrTrue, 85, 85, 85);
      else if ($el['c'] == 'lgrey') $col = imagecolorallocate($qrTrue, 170, 170, 170);
      else if ($el['c'] == 'white') $col = imagecolorallocate($qrTrue, 255, 255, 255);
      $r = ($col >> 16) & 0xFF;
      $g = ($col >> 8) & 0xFF;
      $b = $col & 0xFF;
      imagefilter($qrTrue, IMG_FILTER_COLORIZE, $r, $g, $b);
    
      if ($el['j'] != "l") {
        if ($el['j'] == "c") $x -= (int)($qrW / 2);
        else $x -= $qrW;
      }
      imagecopy($image, $qrTrue, $x, $el['y'], 0, 0, $qrW, $qrH);
      imagedestroy($qrTrue);
      continue;
    }
  }

  imagepng($image);
  imagedestroy($image);
?>