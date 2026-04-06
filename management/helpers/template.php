<?php

function getTemplateParam($folder, $thing) {
  global $_GET;
  if (!isset($_GET['name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No '.$thing.' specified']);
    exit;
  }

  $name = basename($_GET['name']); // strips slashes, etc.
  $path = __DIR__ . '/../'. $folder. '/' . $name . '.json';

  if (!file_exists($path)) {
    print_r($path);
    http_response_code(404);
    echo json_encode(['error' => $thing.' not found']);
    exit;
  }
   
  return $path;
}

function tidy($f) {
  $info = pathinfo($f);
  return basename($f, ".".$info['extension']);
}

function populate_generic($folder, $extension) {
  $f = __DIR__."/../".$folder."/*.".$extension;
  $files = glob($f);
  $files = array_map('tidy', $files);
  sort($files, SORT_NATURAL | SORT_FLAG_CASE);
  return json_encode($files);
}
?>