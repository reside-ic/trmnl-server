<?php

function doLog(
    array $headers,
    string $logFile = "C:/Xampp/Apache/logs/trmnl.log",
    ?string $body = null
) {
    if ($body === null) {
        $body = file_get_contents("php://input");
    }
    $json = json_decode($body, true);
    $entry = [
    "time" => date("c"),
    "mac" => $headers['ID'] ?? null,
    "token" => $headers['ACCESS-TOKEN'] ?? null,
    "payload" => $json ?? $body
    ];
    file_put_contents($logFile, json_encode($entry) . PHP_EOL, FILE_APPEND);
    echo(json_encode(["status" => 200]));
}
