<?php
include "template.php";
$path = getTemplateParam("notices", "Notice");
$json = json_decode(file_get_contents($path), true);
echo json_encode($json, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
?>