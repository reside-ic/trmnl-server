<?php

function getSetupDeviceTable(
    string $configFile = "../secret/config.json"
): array {

    $config = json_decode(file_get_contents($configFile), true);
    $data = [];

    foreach ($config as $friendly_id => $device) {
        $data[$device['mac']] = [
        'api_key' => $device['api_key'],
        'friendly_id' => $device['friendly_id']
        ];
    }
    return $data;
}

function doSetup(
    $headers,
    string $configFile = "../secret/config.json"
) {

    $devices = getSetupDeviceTable($configFile);
    $mac = $headers['ID'] ?? null;
    if (!$mac) {
        return(bailOut(400, "Missing ID header"));
    }
    $mac = strtoupper($mac);
    if (!isset($devices[$mac])) {
        return(bailOut(404, "Device not found"));
    }
    $device = $devices[$mac];

    echo json_encode([
    "status" => 200,
    "api_key" => $device["api_key"],
    "friendly_id" => $device["friendly_id"],
    "image_url" => "https://mrcdata.dide.ic.ac.uk/trmnl/images/setup-logo.png",
    "filename" => "empty_state",
    "message" => "Setup successful"
    ]);
}
