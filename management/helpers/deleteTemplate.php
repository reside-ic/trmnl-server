<?php

include "template.php";
$path = getTemplateParam("templates", "Template");
unlink($path);
header('Content-Type: application/json');
echo populate_generic("templates", "json");

?>