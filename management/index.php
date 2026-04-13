<?php

function readDeviceFile($file) {
  $result = [];
  if (!file_exists($file)) return $result;
  $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  foreach ($lines as $line) {
    list($key, $value) = explode('=', $line, 2);
    $result[$key] = $value;
  }
  return $result;
}

for ($i = 1; $i <= 4; $i++) {
  $f = "../secret/device".$i.".txt";
  $info = readDeviceFile($f);
  $rows[] = [
    'device' => "device".$i,
    'last_refresh' => $info['last_refresh'],
    'battery' => $info['battery'],
    'rssi' => $info['rssi'],
    'page' => $info['page']
  ];
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>Reside TRMNL server</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
  </head>
  <body>
    <center>
    <img src="../images/reside-logo.png">
    <img src="../images/trmnl-logo.png">
    <img src="../images/reside-logo.png">
    </center>
    <hr/>
    <div class="container mt-4">
      <h3>Device Status</h3>
      <table class="table table-striped table-bordered">
        <thead class="table-dark">
          <tr>
            <th>Device</th>
            <th>Last Refresh (GMT)</th>
            <th>Page</th>
            <th>Battery</th>
            <th>WiFi (RSSI)</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['device']) ?></td>
              <td><?= htmlspecialchars($row['last_refresh']) ?></td>
              <td><?= htmlspecialchars($row['page']) ?></td>
              <td><?= htmlspecialchars($row['battery']) ?></td>
              <td><?= htmlspecialchars($row['rssi']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <div class="mt-3 d-flex justify-content-center gap-2">
        <a target="_scheduler" href="Scheduler.php" class="btn btn-primary me-2">Schedule Editor</a>
        <a target="_editor" href="PageEditor.php" class="btn btn-primary me-2">Page Editor</a>
      </div>
    </div>
  </body>
</html>

}

