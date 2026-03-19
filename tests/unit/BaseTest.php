<?php

use PHPUnit\Framework\TestCase;

class BaseTest extends TestCase
{
    protected string $testConfigFile;
    protected string $testLogFile;

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
    }

    protected function tearDown(): void
    {
        @unlink($this->testConfigFile);
        @unlink($this->testLogFile);
    }

  // Silence warning that this has no tests.
    public function testDummy(): void
    {
        $this->assertTrue(true);
    }
}
