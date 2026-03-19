<?php

require_once __DIR__ . '/BaseTest.php';
require_once __DIR__ . '/../../api/setup.php';
require_once __DIR__ . '/../../api/display.php';
require_once __DIR__ . '/../../api/log.php';
require_once __DIR__ . '/../../api/utils.php';

class IndexTest extends BaseTest
{
    private function runIndexWithPath(string $path, array $headers, ?string $body = null): array
    {
        $_SERVER = [];
        foreach ($headers as $key => $value) {
            $_SERVER['HTTP_' . strtoupper(str_replace('-', '_', $key))] = $value;
        }

        $_SERVER['REQUEST_URI'] = $path;
        $_SERVER['PHP_SELF'] = $path;
        $GLOBALS['CONFIG_FILE'] = $this->testConfigFile;
        $GLOBALS['DATA_DIR'] = dirname($this->testConfigFile)."/";
        $GLOBALS['LOG_FILE'] = $this->testLogFile;

        if ($body !== null) {
            $GLOBALS['__php_input'] = $body;
        }

        ob_start();
        include __DIR__ . '/../../api/index.php';
        $output = ob_get_clean();
        return json_decode($output, true);
    }

    public function testSetupEndpoint(): void
    {
        $headers = ['ID' => 'AA:BB:CC:DD:EE:FF'];
        $response = $this->runIndexWithPath('/setup', $headers);
        $this->assertEquals(200, $response['status']);
        $this->assertEquals('device1', $response['friendly_id']);
        $this->assertEquals('key1', $response['api_key']);
    }

    public function testDisplayEndpoint(): void
    {
        $headers = ['ACCESS-TOKEN' => 'key1',
                    'FW-VERSION' => '1.2.3',
                    'RSSI' => '-42',
                    'BATTERY_VOLTAGE' => '4.1'];
        $response = $this->runIndexWithPath('/display', $headers);
        $this->assertEquals(0, $response['status']);
        $this->assertEquals('180', $response['refresh_rate']);
        $this->assertEquals('https://mrcdata.dide.ic.ac.uk/trmnl/images/setup-logo.png', $response['image_url']);

        $txtfile = dirname($this->testConfigFile)."/device1.txt";
        $this->assertFileExists($txtfile);
        $contents = file_get_contents($txtfile);
        $this->assertStringContainsString('battery=4.1', $contents);
        $this->assertStringContainsString('firmware=1.2.3', $contents);
        $this->assertStringContainsString('rssi=-42', $contents);

    }

    public function testLogEndpoint(): void
    {
        $headers = ['ID' => 'AA:BB:CC:DD:EE:FF', 'ACCESS-TOKEN' => 'key1'];
        $payload = ['event' => 'test', 'value' => 123];
        $response = $this->runIndexWithPath('/log', $headers, json_encode($payload));
        $this->assertEquals(200, $response['status']);
        $lines = file($this->testLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(1, $lines);
        $entry = json_decode($lines[0], true);
        $this->assertEquals('AA:BB:CC:DD:EE:FF', $entry['mac']);
        $this->assertEquals('key1', $entry['token']);
        $this->assertEquals($payload, $entry['payload']);
    }

    public function testUnknownEndpoint(): void
    {
        $headers = [];
        $response = $this->runIndexWithPath('/foobar', $headers);
        $this->assertEquals(404, $response['status']);
        $this->assertEquals('Endpoint not found', $response['error']);
    }
}
