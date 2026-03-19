<?php

function metrics($data_dir) {
  header('Content-Type: text/plain')

?>
# HELP trmnl_battery_volts Battery voltage of device
# TYPE trmnl_battery_volts gauge
# HELP trmnl_wifi_signal_dbm WiFi signal strength in dBm
# TYPE trmnl_wifi_signal_dbm gauge
# HELP trmnl_last_refresh_timestamp Last refresh time (unix timestamp)
# TYPE trmnl_last_refresh_timestamp gauge
<?php

  foreach (glob($data_dir."device*.txt") as $file) {
    $dev = basename($file, ".txt");
    $data = parse_ini_file($file);
    if ($data === false) continue;

    $battery = floatval($data['battery']);
    $rssi = intval($data['rssi']);
    $last_refresh = strtotime($data['last_refresh']);
    
    echo "trmnl_battery_volts{device=\"$dev\"} $battery\n";
    echo "trmnl_wifi_signal_dbm{device=\"$dev\"} $rssi\n";
    echo "trmnl_last_refresh_timestamp{device=\"$dev\"} $last_refresh\n";
  }
}

metrics("../secret/");

?>
