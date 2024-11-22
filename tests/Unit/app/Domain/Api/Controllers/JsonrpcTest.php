<?php

namespace Tests\Unit\app\Domain\Api\Controllers;

use Leantime\Core\Template;
use Leantime\Domain\Api\Controllers\Jsonrpc;
use Unit\TestCase;

class JsonrpcTest extends TestCase
{
    private Jsonrpc $controller;

    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->template = $this->createMock(Template::class);
        $this->controller = new Jsonrpc($this->template);
    }

    public function testMethodStringParsing()
    {
        $params = [
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 1,
            'jsonrpc' => '2.0',
        ];

        $this->template->expects($this->once())
            ->method('displayJson')
            ->willReturnCallback(function ($response) {
                $this->assertArrayHasKey('jsonrpc', $response);
                $this->assertEquals('2.0', $response['jsonrpc']);

                return response()->json($response);
            });

        $this->controller->post($params);
    }

    public function testInvalidMethodString()
    {
        $params = [
            'method' => 'invalid.method.string',
            'params' => ['projectId' => 1],
            'id' => 1,
            'jsonrpc' => '2.0',
        ];

        $this->template->expects($this->once())
            ->method('displayJson')
            ->willReturnCallback(function ($response) {
                $this->assertArrayHasKey('error', $response);
                $this->assertEquals(-32602, $response['error']['code']);

                return response()->json($response);
            });

        $this->controller->post($params);
    }

    public function testMissingJsonRpcVersion()
    {
        $params = [
            'method' => 'leantime.rpc.Comments.pollComments',
            'params' => ['projectId' => 1],
            'id' => 1,
        ];

        $this->template->expects($this->once())
            ->method('displayJson')
            ->willReturnCallback(function ($response) {
                $this->assertArrayHasKey('error', $response);
                $this->assertEquals(-32600, $response['error']['code']);

                return response()->json($response);
            });

        $this->controller->post($params);
    }

    public function testBatchRequest()
    {
        $params = [
            [
                'method' => 'leantime.rpc.Comments.pollComments',
                'params' => ['projectId' => 1],
                'id' => 1,
                'jsonrpc' => '2.0',
            ],
            [
                'method' => 'leantime.rpc.Comments.pollComments',
                'params' => ['projectId' => 2],
                'id' => 2,
                'jsonrpc' => '2.0',
            ],
        ];

        $this->template->expects($this->once())
            ->method('displayJson')
            ->willReturnCallback(function ($response) {
                $this->assertIsArray($response);
                $this->assertCount(2, $response);

                return response()->json($response);
            });

        $this->controller->post($params);
    }
}
