<?php

require_once __DIR__ . '/../../api/utils.php';
use PHPUnit\Framework\TestCase;

class UtilsTest extends TestCase
{
    public function testBailOutOutputsJson(): void
    {
        $obLevel = ob_get_level();
        ob_start();
        try {
            bailOut(400, "Bad request");
            $output = ob_get_clean();
        } catch (\Throwable $e) {
            if (ob_get_level() > $obLevel) {
                ob_end_clean();
            }
            throw $e;
        }

        $data = json_decode($output, true);
        $this->assertEquals(400, $data['status']);
        $this->assertEquals("Bad request", $data['error']);
    }

    public function testGetAllHeadersParsesServerVars(): void
    {
        $_SERVER = [
        'HTTP_ID' => 'AA:BB:CC',
        'HTTP_ACCESS_TOKEN' => 'key123',
        'HTTP_FW_VERSION' => '1.0',
        'NOT_A_HEADER' => 'ignore_me'
        ];

        $headers = getallheaders();
        $this->assertArrayHasKey('Id', $headers);
        $this->assertArrayHasKey('Access-Token', $headers);
        $this->assertArrayHasKey('Fw-Version', $headers);
        $this->assertEquals('AA:BB:CC', $headers['Id']);
        $this->assertEquals('key123', $headers['Access-Token']);
        $this->assertEquals('1.0', $headers['Fw-Version']);
        $this->assertArrayNotHasKey('NOT_A_HEADER', $headers);
    }
}
