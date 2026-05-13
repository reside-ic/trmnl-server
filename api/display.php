<?php

require_once __DIR__ . '/../management/helpers/preview.php';

// Latest firmware version and its filename in /firmware.
// If the version a TRMNL reports is different from this
// (either greater or less), then we flash it with this.

$LATEST_FW_VERSION = "1.8.3";
$LATEST_FW_FILE = "fw-1.8.3-wes.bin";

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

function getScheduledImage($device, $page, $schedule, $now, $noticeDir, $imgDir) {
  $def = "https://mrcdata.dide.ic.ac.uk/trmnl/images/setup-logo.png";
  $rows = json_decode(file_get_contents($schedule), true);

  foreach ($rows as $row) {
    $from = new DateTime($row['from']);
    $to = new DateTime($row['to']);
    if ($now >= $from && $now <= $to && in_array($device, $row['devices'])) {
      if ($page >= count($row['notices'])) $page = 0;
      $notice = $row['notices'][$page];
      $page++;
      $imgFile = $imgDir.$notice.'.png';
      $noticeFile = basename($notice);
      $noticeJson = $noticeDir.$noticeFile.".json";
      $regenerate = (!file_exists($imgFile));
      if (!$regenerate) {
        $pngDate = filemtime($imgFile);
        $jsonDate = filemtime($noticeJson);
        if ($jsonDate > $pngDate) {
          $regenerate = true;
        }
      }

      if ($regenerate) {
        $data = json_decode(file_get_contents($noticeJson), true);
        $img = doPreview($data);
        imagepng($img, $imgFile);
        imagedestroy($img);
      }
      return ["https://mrcdata.dide.ic.ac.uk/trmnl/images/" . $notice . ".png", $page];
    }
  }
  return [$def, 0];
}

function doDisplay(
    $headers,
    string $configFile = "../secret/config.json",
    string $dataDir = "../secret/",
    string $schedule = __DIR__."/../management/schedule.json",
    DateTime $now = new DateTime(),
    string $noticeDir = __DIR__ . '/../management/notices/',
    string $imgDir = __DIR__ . '/../images/'
) {
    global $LATEST_FW_VERSION, $LATEST_FW_FILE;

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
    $friendly_id = $dev['friendly_id'];
    $conf_file = $dataDir.$friendly_id.".txt";
    $page = 0;
    if (file_exists($conf_file)) {
      $data = file($conf_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      foreach ($data as $line) {
        list($key, $value) = explode('=', $line, 2);
        if ($key == "page") $page = intval($value);
      }
    }

    list($image_url, $page) = getScheduledImage(
      $friendly_id, $page,
      $schedule, $now, $noticeDir, $imgDir);

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
    $update_fw = ($fw != $LATEST_FW_VERSION);
    $fw_url = ($update_fw) ? "https://mrcdata.dide.ic.ac.uk/trmnl/firmware/".$LATEST_FW_FILE : null;

    echo json_encode([
      "status" => 0,
      "image_url" => $image_url,
      "filename" => date("c"),
      "update_firmware" => $update_fw,
      "firmware_url" => $fw_url,
      "refresh_rate" => $dev["refresh_rate"],
      "reset_firmware" => false
    ]);
}
