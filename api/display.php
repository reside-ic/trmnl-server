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
        $data[$device['api_key']] = ['friendly_id' => $friendly_id];
    }
    return $data;
}

function getNextRefresh(?DateTime $now = null) : int {
  $now = $now ?? new DateTime('now');
  $weekday = (int) $now->format("N"); // 1 = Monday, 7 = Sunday
  $hour = (int) $now->format("G");    // hour 0..23

  // If it's between Friday 7pm, and Monday 7am, then wait til Monday 8am

  if ((($weekday == 5) && ($hour >= 19)) || 
       ($weekday > 5) ||
      (($weekday == 1) && ($hour < 8))) {
    $monday_8am = clone $now;
    while ((int)$monday_8am->format("N") != 1) $monday_8am->modify("+1 days");
    $monday_8am->setTime(8, 0, 0);
    return $monday_8am->getTimestamp() - $now->getTimestamp();
  }

  // If 7pm-midnight (on any remaining day)

  if ($hour >=19) {
    $next_morning = clone $now;
    $next_morning->modify("+1 day");
    $next_morning->setTime(8, 0, 0);
    return $next_morning->getTimestamp() - $now->getTimestamp();
  }

  // If before 8am on any remaining day

  if ($hour < 8) {
    $this_morning = clone $now;
    $this_morning->setTime(8, 0, 0);
    return $this_morning->getTimestamp() - $now->getTimestamp();
  }

  // Between 12pm and 2pm, do 5 minutes.

  if (($hour >=12) && ($hour <14)) return 5*60;

  // Else, every 15.

  return 15*60;
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

    $fp = fopen($conf_file, 'c');
    if (flock($fp, LOCK_EX)) {
      ftruncate($fp, 0);
      fwrite($fp, "last_refresh=".$now->format('Y-m-d H:i:s')."\n");
      fwrite($fp, "battery=".$battery."\n");
      fwrite($fp, "firmware=".$fw."\n");
      fwrite($fp, "rssi=".$rssi."\n");
      fwrite($fp, "page=".$page."\n");
      fflush($fp);
      flock($fp, LOCK_UN);
    }
    fclose($fp);
    $update_fw = ($fw != $LATEST_FW_VERSION);
    $fw_url = ($update_fw) ? "https://mrcdata.dide.ic.ac.uk/trmnl/firmware/".$LATEST_FW_FILE : null;
    $next_refresh = getNextRefresh(); 
    echo json_encode([
      "status" => 0,
      "image_url" => $image_url,
      "filename" => $now->format("c"),
      "update_firmware" => $update_fw,
      "firmware_url" => $fw_url,
      "refresh_rate" => $next_refresh,
      "reset_firmware" => false
    ]);
}
