<?php
  $file = __DIR__ . '/../schedule.json';
  if (!file_exists($file)) {
    echo json_encode([]);
    exit;
  }

  header('Content-Type: application/json');
  echo file_get_contents($file);
?>