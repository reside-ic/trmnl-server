<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../api/display.php';
require_once __DIR__ . '/../../api/utils.php';
require_once __DIR__ . '/BaseTest.php';

class DisplayTest extends BaseTest
{
    public function testApiKeyTable(): void
    {
        $devices = getApiKeyTable($this->testConfigFile);
        $expected = [
        'key1' => [
        'friendly_id' => 'device1'
        ],
        'key2' => [
        'friendly_id' => 'device2'
        ]
        ];
        $this->assertEquals($expected, $devices);
    }

    public function testDoDisplaySuccess(): void
    {
        $headers = ['ACCESS-TOKEN' => 'key2',
                    'BATTERY-VOLTAGE' => '4.01',
                    'FW-VERSION' => '2.3.4',
                    'RSSI' => '-43'];
        ob_start();
        $testFolder = dirname($this->testConfigFile)."/";
        doDisplay($headers, $this->testConfigFile, $testFolder, 
          $this->testScheduleFile, new DateTime("2026-04-01"), 
          $this->testNoticeDir, $this->testImageDir);

        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertEquals(0, $response['status']);

        $txtfile = $testFolder."device2.txt";
        $this->assertFileExists($txtfile);
        $contents = file_get_contents($txtfile);
        $this->assertStringContainsString('battery=4.01', $contents);
        $this->assertStringContainsString('firmware=2.3.4', $contents);
        $this->assertStringContainsString('rssi=-43', $contents);
    }

    public function testDoDisplayDeviceNotFound(): void
    {
        $headers = ['ACCESS-TOKEN' => 'potato'];
        ob_start();
        doDisplay($headers, $this->testConfigFile);
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertEquals(500, $response['status']);
        $this->assertEquals('Device not found', $response['error']);
    }

    public function testRefreshVals(): void
    {
        // Monday 9am - expected 900 seconds
        $this->assertEquals(900, getNextRefresh(new DateTime("2026-05-18 09:00:00")));

        // Tuesday 1pm - expected 300 seconds
        $this->assertEquals(300, getNextRefresh(new DateTime("2026-05-19 13:00:00")));

        // Wednesday 3pm - expected 900 seconds
        $this->assertEquals(900, getNextRefresh(new DateTime("2026-05-20 15:00:00")));

        // Friday 8pm - expected to be... 4 hours + 48 hours + 8 hours = 60.
        $this->assertEquals(60 * 3600, getNextRefresh(new DateTime("2026-05-22 20:00:00")));

        // Saturday 8pm - expected to be... 4 hours + 24 hours + 8 hours = 36.
        $this->assertEquals(36 * 3600, getNextRefresh(new DateTime("2026-05-23 20:00:00")));

        // Monday 1am - expected to be... 7 hours
        $this->assertEquals(7 * 3600, getNextRefresh(new DateTime("2026-05-18 01:00:00")));

    }
}
