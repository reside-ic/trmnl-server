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
        'friendly_id' => 'device1',
        'refresh_rate' => '180'
        ],
        'key2' => [
        'friendly_id' => 'device2',
        'refresh_rate' => '360'
        ]
        ];
        $this->assertEquals($expected, $devices);
    }

    public function testDoDisplaySuccess(): void
    {
        $headers = ['ACCESS-TOKEN' => 'key2'];
        ob_start();
        doDisplay($headers, $this->testConfigFile);
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertEquals(0, $response['status']);
        $this->assertEquals('360', $response['refresh_rate']);
    }

    public function testDoSisplayDeviceNotFound(): void
    {
        $headers = ['ACCESS-TOKEN' => 'potato'];
        ob_start();
        doDisplay($headers, $this->testConfigFile);
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertEquals(500, $response['status']);
        $this->assertEquals('Device not found', $response['error']);
    }
}
