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
        'refresh_rate' => '1800'
        ],
        'key2' => [
        'friendly_id' => 'device2',
        'refresh_rate' => '1800'
        ]
        ];
        $this->assertEquals($expected, $devices);
    }

    public function testDoSetupSuccess(): void
    {
        $headers = ['ID' => 'aa:bb:cc:dd:ee:ff'];
        ob_start();
        doSetup($headers, $this->testConfigFile);
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('key1', $response['api_key']);
        $this->assertEquals('device1', $response['friendly_id']);
        $this->assertEquals('empty_state', $response['filename']);
    }

    public function testDoSetupMissingID(): void
    {
        $headers = [];
        ob_start();
        doSetup($headers, $this->testConfigFile);
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertEquals(400, $response['status']);
        $this->assertEquals('Missing ID header', $response['error']);
    }

    public function testDoSetupDeviceNotFound(): void
    {
        $headers = ['ID' => 'FF:FF:FF:FF:FF:FF'];
        ob_start();
        doSetup($headers, $this->testConfigFile);
        $output = ob_get_clean();
        $response = json_decode($output, true);
        $this->assertEquals(404, $response['status']);
        $this->assertEquals('Device not found', $response['error']);
    }
}
