<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../api/setup.php';
require_once __DIR__ . '/../../api/utils.php';
require_once __DIR__ . '/BaseTest.php';

class SetupTest extends BaseTest
{
    public function testGetSetupDeviceTable(): void
    {
        $devices = getSetupDeviceTable($this->testConfigFile);
        $expected = [
        'AA:BB:CC:DD:EE:FF' => [
        'api_key' => 'key1',
        'friendly_id' => 'device1'
        ],
        '11:22:33:44:55:66' => [
        'api_key' => 'key2',
        'friendly_id' => 'device2'
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
