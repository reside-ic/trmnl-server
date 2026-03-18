<?php

require_once __DIR__ . '/../../api/log.php';
require_once __DIR__ . '/BaseTest.php';

class LogTest extends BaseTest
{
    public function testDoLogWritesEntry(): void
    {
        $headers = [
        'ID' => 'AA:BB:CC:DD:EE:FF',
        'ACCESS-TOKEN' => 'key1'
        ];

        $payload = ['event' => 'test', 'value' => 123];
        ob_start();
        doLog($headers, $this->testLogFile, json_encode($payload));
        ob_get_clean();

        $lines = file($this->testLogFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $this->assertCount(1, $lines);
        $entry = json_decode($lines[0], true);
        $this->assertEquals('AA:BB:CC:DD:EE:FF', $entry['mac']);
        $this->assertEquals('key1', $entry['token']);
        $this->assertEquals($payload, $entry['payload']);
    }
}
