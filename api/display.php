<?php

require_once __DIR__ . '/../management/helpers/preview.php';

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

function getScheduledImage(
  $device,
  $page,
  string $schedule = __DIR__."/../management/schedule.json",
  DateTime $now = new DateTime()
) {

  $def = "https://mrcdata.dide.ic.ac.uk/trmnl/images/setup-logo.png";
  if (!file_exists($schedule)) {
    return [$def, 0];
  }
  $rows = json_decode(file_get_contents($schedule), true);

  foreach ($rows as $row) {
    $from = new DateTime($row['from']);
    $to = new DateTime($row['to']);
    if ($now >= $from && $now <= $to && in_array($device, $row['devices'])) {
      if ($page >= count($row['notices'])) $page = 0;
      $notice = $row['notices'][$page];
      $page++;
      $imgFile = __DIR__ . '/../images/' . $notice . '.png';
      if (!file_exists($imgFile)) {
        $noticeFile = basename($notice);
        $noticeJson = __DIR__ . '/../management/notices/'.$noticeFile.".json";
        $noticePng = __DIR__ . '/../images/'.$noticeFile.".png";
        $data = json_decode(file_get_contents($noticeJson), true);
        $img = doPreview($data);
        imagepng($img, $noticePng);
        imagedestroy($img);
      }
      return ["https://mrcdata.dide.ic.ac.uk/trmnl/images/" . $notice . ".png", $page];
    }
  }
  return [$def, $page];
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

    $conf_file = $dataDir.$dev['friendly_id'].".txt";
    $page = 0;
    if (file_exists($conf_file)) {
      $data = file($conf_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach ($data as $line) {
        list($key, $value) = explode('=', $line, 2);
        if ($key == "page") $page = $value;
      }
    }

    $image_url = "https://mrcdata.dide.ic.ac.uk/trmnl/images/setup-logo.png";
    $image_url = "https://mrcdata.dide.ic.ac.uk/trmnl/images/ic-back2.png";
    $image_url = "https://mrcdata.dide.ic.ac.uk/trmnl/images/out.png";
    list($image_url, $page) = getScheduledImage($dev['friendly_id'], $page);

    $dateTime = date('Y-m-d H:i:s');
    $fp = fopen($conf_file, 'c');
    if (flock($fp, LOCK_EX)) {
      ftruncate($fp, 0);
      fwrite($fp, "last_refresh=".$dateTime."\n");
      fwrite($fp, "battery=".$battery."\n");
      fwrite($fp, "firmware=".$fw."\n");
      fwrite($fp, "rssi=".$rssi."\n");
      fwrite($fp, "page=".$page."\n");
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
