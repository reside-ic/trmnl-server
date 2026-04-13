<?php

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    protected string $testConfigFile;
    protected string $testLogFile;
    protected string $testScheduleFile;
    protected string $testNoticeDir;
    protected string $testImageDir;


    protected function setUp(): void
    {
        $this->testConfigFile = __DIR__ . '/fixtures/config.json';
        if (!file_exists(dirname($this->testConfigFile))) {
            mkdir(dirname($this->testConfigFile), 0775, true);
        }

        $fixtureData = [
          'device1' => [
            'mac' => 'AA:BB:CC:DD:EE:FF',
            'api_key' => 'key1',
            'refresh_rate' => '180'
          ],
          'device2' => [
            'mac' => '11:22:33:44:55:66',
            'api_key' => 'key2',
            'refresh_rate' => '360'
          ]
        ];

        file_put_contents($this->testConfigFile, json_encode($fixtureData));

        $this->testLogFile = __DIR__ . '/fixtures/trmnl_test.log';
        if (file_exists($this->testLogFile)) {
            unlink($this->testLogFile);
        }

        $this->testNoticeDir = __DIR__. '/fixtures/notices/';
        mkdir($this->testNoticeDir, 0775, true);
        $this->testImageDir = __DIR__.'/fixtures/images/';
        mkdir($this->testImageDir, 0775, true);
        $this->testScheduleFile = __DIR__. '/fixtures/schedule.json';
        $scheduleData = [
          [
            "from" => "2026-03-01T00:00",
            "to" => "2026-04-26T23:59",
            "notices" => ["notice1", "notice2"],
            "devices" => ["device1", "device2"]
          ]
        ];
        file_put_contents($this->testScheduleFile, json_encode($scheduleData));

        $notice1 = [
          "name" => "Seminar",
          "back" => "ic-display",
          "elements" => [
            [
              "typ" => "t",
              "x" => 30,
              "y" => 130,
              "f" => "ImperialSansDisplay-Bold",
              "s" => 28,
              "t" => "Guest Seminar",
              "j" => "l",
              "c" => "black"
            ]
          ]
        ];

        $notice2 = [
          "name" => "Another Seminar",
          "back" => "ic-display",
          "elements" => [
            [
              "typ" => "t",
              "x" => 30,
              "y" => 160,
              "f" => "ImperialSansDisplay-Bold",
              "s" => 28,
              "t" => "Guest Seminar",
              "j" => "l",
              "c" => "black"
            ]
          ]
        ];

        file_put_contents($this->testNoticeDir."/notice1.json", json_encode($notice1));
        file_put_contents($this->testNoticeDir."/notice2.json", json_encode($notice2));
    }

    protected function tearDown(): void
    {
        @unlink($this->testConfigFile);
        @unlink($this->testLogFile);
        @unlink($this->testScheduleFile);
        @unlink($this->testImageDir."notice1.png");
        @unlink($this->testImageDir."notice2.png");
        @unlink($this->testNoticeDir."notice1.json");
        @unlink($this->testNoticeDir."notice2.json");
        @unlink(dirname($this->testConfigFile)."/device1.txt");
        @unlink(dirname($this->testConfigFile)."/device2.txt");
        @rmdir($this->testNoticeDir);
        @rmdir($this->testImageDir);

    }

  // Silence warning that this has no tests.
    public function testDummy(): void
    {
        $this->assertTrue(true);
    }
}
