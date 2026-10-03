<?php

namespace Unit\app\Domain\Status;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Leantime\Core\Http\HttpKernel;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Domain\Status\Controllers\Health;

/**
 * GET /health (#865): 200 {"status":"ok"} when the db answers, 503 {"status":"error"} when it
 * doesn't, and never anything else in the body. HttpKernel::isHealthCheck() decides which
 * requests skip the middleware pipeline.
 */
class HealthTest extends \Unit\TestCase
{
    private function healthWithDb(?\Throwable $failure): Health
    {
        $connection = $this->createMock(Connection::class);
        $selectCall = $connection->expects($this->once())->method('select')->with('select 1');

        if ($failure !== null) {
            $selectCall->willThrowException($failure);
        } else {
            $selectCall->willReturn([['1' => 1]]);
        }

        $db = $this->createMock(DatabaseManager::class);
        $db->method('connection')->willReturn($connection);

        return new Health($db);
    }

    public function test_returns_ok_when_database_is_reachable(): void
    {
        $response = $this->healthWithDb(null)->get();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['status' => 'ok'], json_decode($response->getContent(), true));
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_returns_503_without_details_when_database_fails(): void
    {
        $response = $this->healthWithDb(new \PDOException('SQLSTATE[HY000] [2002] secret-db-host refused'))->get();

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame(['status' => 'error'], json_decode($response->getContent(), true));
        $this->assertStringNotContainsString('secret-db-host', $response->getContent());
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: bool}>
     */
    public static function probeRequests(): array
    {
        return [
            'GET /health' => ['/health', 'GET', true],
            'HEAD /health' => ['/health', 'HEAD', true],
            'GET /health with query' => ['/health?x=1', 'GET', true],
            'POST /health' => ['/health', 'POST', false],
            'GET /health/extra' => ['/health/extra', 'GET', false],
            'GET /healthz' => ['/healthz', 'GET', false],
            'GET /dashboard/home' => ['/dashboard/home', 'GET', false],
        ];
    }

    /**
     * @dataProvider probeRequests
     */
    public function test_is_health_check_matches_only_the_probe_route(string $uri, string $method, bool $expected): void
    {
        $request = new IncomingRequest([], [], [], [], [], [
            'REQUEST_URI' => $uri,
            'REQUEST_METHOD' => $method,
            'SCRIPT_NAME' => '/index.php',
            'SCRIPT_FILENAME' => APP_ROOT.'/public/index.php',
            'PHP_SELF' => '/index.php',
            'HTTP_HOST' => 'localhost',
        ]);

        $this->assertSame($expected, HttpKernel::isHealthCheck($request));
    }
}
