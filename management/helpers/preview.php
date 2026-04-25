<?php
  require_once 'phpqrcode.php';

  function justify($x, $j, $w) {
    if ($j == "l") return $x;
    else if ($j == "c") return (int) ($x - ($w / 2));
    else return $x - $w;
  }

  function getColour($image, $col) {
    if ($col == "black") return imagecolorallocate($image, 0, 0, 0);
    if ($col == "dgrey") return imagecolorallocate($image, 85, 85, 85);
    if ($col == "lgrey") return imagecolorallocate($image, 170, 170, 170);
    return imagecolorallocate($image, 255, 255, 255);
  }
    

  function drawText($image, $x, $y, $size, $font, $just, $col, $text) {
    $font = __DIR__."/../fonts/".$font.".ttf";
    $bbox = imagettfbbox($size, 0, $font, $text);
    $x = justify($x, $just, abs($bbox[2] - $bbox[0]));
    $col = getColour($image, $col);
    imagettftext($image, $size, 0, $x, $y, $col, $font, $text);
  }

  function drawQR($image, $x, $y, $size, $just, $col, $text) {
    if ($size < 2) $size = 2;
    else if ($size > 8) $size = 8;
    ob_start();
    QRcode::png($text, null, QR_ECLEVEL_L, $size, 0);
    $qrImage = imagecreatefromstring(ob_get_clean());
    $qrW = imagesx($qrImage);
    $qrH = imagesy($qrImage);
    $qrTrue = imagecreatetruecolor($qrW, $qrH);
    imagealphablending($qrTrue, false);
    imagesavealpha($qrTrue, true);
    imagecopy($qrTrue, $qrImage, 0, 0, 0, 0, $qrW, $qrH);
    imagedestroy($qrImage);
    $col = getColour($qrTrue, $col);
    $r = ($col >> 16) & 0xFF;
    $g = ($col >> 8) & 0xFF;
    $b = $col & 0xFF;
    imagefilter($qrTrue, IMG_FILTER_COLORIZE, $r, $g, $b);
    $x = justify($x, $just, $qrW);
    imagecopy($image, $qrTrue, $x, $y, 0, 0, $qrW, $qrH);
    imagedestroy($qrTrue);
  }

  function drawImage($image, $x, $y, $just, $imgData, $wid) {
    $imgData = str_replace('data:image/png;base64,', '', $imgData);
    $imgData = base64_decode($imgData);
    $img = imagecreatefromstring($imgData);
    if ($img !== false) {
      $imgW = imagesx($img);
      $imgH = imagesy($img);
      $newW = $imgW;
      $newH = $imgH;
      $scale = $wid / $imgW;
      $newW = $wid;
      $newH = (int) ($imgH * $scale);
      $x = justify($x, $just, $newW);
      imagecopyresampled($image, $img, $x, $y, 0, 0, $newW, $newH, $imgW, $imgH);
      imagedestroy($img);
    }
  }

  function doPreview(array $data) {
    $bg = $data['back'];
    $image = imagecreatefrompng(__DIR__."/../backgrounds/".$bg.".png");
    $rows = $data['elements'];
    foreach ($rows as $el) {
      if ($el['typ'] == "t") {
        drawText($image, $el['x'], $el['y'], $el['s'], $el['f'], $el['j'], $el['c'], $el['t']);
      } else if ($el['typ'] == "q") {
        drawQR($image, $el['x'], $el['y'], $el['s'], $el['j'], $el['c'], $el['t']);
      } else if (($el['typ'] == "i") && (!empty($el['i']))) {
        drawImage($image, $el['x'], $el['y'], $el['j'], $el['i'], $el['s']);
      }
    }

    return $image;
  }

  if (realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    if (isset($_GET['file'])) {
      $filename = basename($_GET['file']);
      $jsonPath = __DIR__. "/../notices/{$filename}.json";
      $data = json_decode(file_get_contents($jsonPath), true);
    } else {
      $input = file_get_contents('php://input');
      $data = json_decode($input, true);
    }
    $image = doPreview($data);
    header("Content-Type: image/png");
    imagepng($image);
    imagedestroy($image);
  }

?>