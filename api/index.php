<?php

header('Content-Type: application/json');
require_once "utils.php";
require_once "setup.php";
require_once "display.php";
require_once "log.php";
$headers = array_change_key_case(getallheaders(), CASE_UPPER);
$configFile = $GLOBALS['CONFIG_FILE'] ?? "../secret/config.json";
$dataDir = $GLOBALS['DATA_DIR'] ?? "../secret/";
$logFile = $GLOBALS['LOG_FILE'] ?? "C:/Xampp/Apache/logs/trmnl.log";
$uri = $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
$path = preg_replace('#^/trmnl/api#', '', $path);
$body = $GLOBALS['__php_input'] ?? file_get_contents("php://input");
if ($path == "/setup") {
    doSetup($headers, $configFile);
} elseif ($path == "/display") {
    doDisplay($headers, $configFile, $dataDir);
} elseif ($path == "/log") {
    doLog($headers, $logFile, $body);
} else {
    return(bailOut(404, "Endpoint not found"));
}
