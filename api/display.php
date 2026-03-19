<?php

function getApiKeyTable(
    string $configFile = "../secret/config.json"
): array {
    $config = json_decode(file_get_contents($configFile), true);
    $data = [];
    foreach ($config as $friendly_id => $device) {
        $data[$device['api_key']] = ['friendly_id' => $friendly_id,
                                     'refresh_rate' => $device['refresh_rate']];
    }
    return $data;
}


function doDisplay(
    $headers,
    string $configFile = "../secret/config.json",
    string $dataDir = "../secret/"
) {

    $devices = getApiKeyTable($configFile);
    $mac = $headers['ID'] ?? null;
    $token = $headers['ACCESS-TOKEN'] ?? null;
    $fw = $headers['FW-VERSION'] ?? null;
    $battery = $headers['BATTERY-VOLTAGE'] ?? null;
    $rssi = $headers['RSSI'] ?? null;

    if ((!$token) || (!isset($devices[$token]))) {
        return(bailOut(500, "Device not found"));
    }

    $dev = $devices[$token];

    $image_url = "https://mrcdata.dide.ic.ac.uk/trmnl/images/setup-logo.png";

    $dateTime = date('Y-m-d H:i:s');
    $fp = fopen($dataDir.$dev['friendly_id'].".txt", 'c');
    if (flock($fp, LOCK_EX)) {
      ftruncate($fp, 0);
      fwrite($fp, "last_refresh=".$dateTime."\n");
      fwrite($fp, "battery=".$battery."\n");
      fwrite($fp, "firmware=".$fw."\n");
      fwrite($fp, "rssi=".$rssi."\n");
      fflush($fp);
      flock($fp, LOCK_UN);
      fclose($fp);
    }

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
