<?php

header('Content-Type: application/json');
require_once "utils.php";
require_once "setup.php";
require_once "display.php";
require_once "log.php";
$headers = array_change_key_case(getallheaders(), CASE_UPPER);

$configFile = $GLOBALS['CONFIG_FILE'] ?? __DIR__."/../secret/config.json";
$dataDir = $GLOBALS['DATA_DIR'] ?? __DIR__."/../secret/";
$schedule = $GLOBALS['SCHEDULE'] ?? __DIR__."/../management/schedule.json";
$now = $GLOBALS['FORCE_DATE'] ?? new DateTime();
$noticeDir = $GLOBALS['NOTICE_DIR'] ?? __DIR__."/../management/notices/";
$imgDir = $GLOBALS['IMG_DIR'] ?? __DIR__."/../images/";
$logFile = $GLOBALS['LOG_FILE'] ?? "C:/Xampp/Apache/logs/trmnl.log";
$uri = $_SERVER['REQUEST_URI'] ?? $_SERVER['PHP_SELF'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);
$path = preg_replace('#^/trmnl/api#', '', $path);
$body = $GLOBALS['__php_input'] ?? file_get_contents("php://input");

if ($path == "/setup") {
    doSetup($headers, $configFile);

} elseif ($path == "/display") {
    doDisplay($headers, $configFile, $dataDir, $schedule, $now, $noticeDir, $imgDir);

} elseif ($path == "/log") {
    doLog($headers, $logFile, $body);

} else {
    return(bailOut(404, "Endpoint not found"));
}
