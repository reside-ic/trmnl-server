<?php

function getApiKeyTable(
    string $configFile = "../secret/config.json"
): array {
    $config = json_decode(file_get_contents($configFile), true);
    $data = [];
    foreach ($config as $friendly_id => $device) {
        $data[$device['api_key']] = ['friendly_id' => $device['friendly_id'],
                                     'refresh_rate' => $device['refresh_rate']];
    }
    return $data;
}


function doDisplay(
    $headers,
    string $configFile = "../secret/config.json"
) {

    $devices = getApiKeyTable($configFile);
    $mac = $headers['ID'] ?? null;
    $token = $headers['ACCESS-TOKEN'] ?? null;
    $fw = $headers['FW-VERSION'] ?? null;
    if ((!$token) || (!isset($devices[$token]))) {
        return(bailOut(500, "Device not found"));
    }

    $dev = $devices[$token];

    $image_url = "https://mrcdata.dide.ic.ac.uk/trmnl/images/setup-logo.png";

    echo json_encode([
    "status" => 0,
    "image_url" => $image_url,
    "filename" => date("c"),
    "update_firmware" => false,
    "firmware_url" => null,
    "refresh_rate" => $dev["refresh_rate"],
    "reset_firmware" => false
    ]);
}
