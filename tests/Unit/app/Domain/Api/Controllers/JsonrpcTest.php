<?php

namespace Tests\Unit\app\Domain\Api\Controllers;

use Leantime\Core\Application;
use Leantime\Core\Language;
use Leantime\Core\Template;
use Leantime\Domain\Api\Controllers\Jsonrpc;
use Leantime\Core\Bootstrap\LoadConfig;
use Leantime\Core\Bootstrap\SetRequestForConsole;

class JsonrpcTest extends \Unit\TestCase
{
    private Jsonrpc $controller;

    private Template $template;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app = new Application(APP_ROOT);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);

        $this->app->boot();
        $this->app['view'] = $this->createMock(\Illuminate\View\Factory::class);
        $this->app['session'] = $this->createMock(\Illuminate\Session\SessionManager::class);
        $this->app->bootstrapWith([LoadConfig::class, SetRequestForConsole::class]);

        $this->template = $this->createMock(Template::class);
        $this->template->method('displayJson')->willReturn(response()->json([]));
        $language = $this->createMock(Language::class);
        $this->controller = new Jsonrpc($this->app['request'], $this->template, $language);
        $_SERVER['REQUEST_METHOD'] = 'post';
    }

    public function test_method_string_parsing()
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

    public function test_invalid_method_string()
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

    public function test_missing_json_rpc_version()
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

    public function test_batch_request()
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

        // Each batched call will call displayJson once and then the final invokation will combine the results of the
        // first 2 invocations to an array of 2 results
        $countInvocations = 0;
        $this->template->method('displayJson')
            ->willReturnCallback(function ($response) use (&$countInvocations) {
                $countInvocations++;
                $this->assertIsArray($response);

                if ($countInvocations < 3) {
                    // Each individual invocation will have the regular jsonrpc response which gets mapped to the main
                    // response
                    $this->assertCount(3, $response);
                }

                if ($countInvocations == 3) {
                    // The last invocation has 2 array items each being the result set of the jsonrpc call
                    $this->assertCount(2, $response);
                }

                return response()->json($response);
            });

        $this->controller->post($params);
    }
}
