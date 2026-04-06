<?php

include "template.php";
$input = json_decode(file_get_contents('php://input'), true);
$name = basename($input['name']);
$path = __DIR__ . '/../notices/' . $name . '.json';
file_put_contents($path, json_encode($input, JSON_PRETTY_PRINT));
header('Content-Type: application/json');
echo populate_generic("notices", "json");

?>