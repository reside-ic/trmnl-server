<?php

include "template.php";
$path = getTemplateParam("notices", "Notice");
unlink($path);
header('Content-Type: application/json');
echo populate_generic("notices", "json");

?>