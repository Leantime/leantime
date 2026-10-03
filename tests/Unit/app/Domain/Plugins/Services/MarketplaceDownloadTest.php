<?php

namespace Unit\app\Domain\Plugins\Services;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Leantime\Domain\Plugins\Services\Plugins;
use Unit\TestCase;

/**
 * The marketplace archive download sends license and instance headers. They must reach the
 * configured marketplace origin only, never a redirect target on another origin, and the
 * download must never leave https.
 */
class MarketplaceDownloadTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private const HEADERS = [
        'X-License-Key' => 'license-secret',
        'X-Instance-Id' => 'instance-1',
        'X-User-Count' => 5,
    ];

    private function download(string $url): \Illuminate\Http\Client\Response
    {
        /** @var Plugins $plugins */
        $plugins = $this->make(Plugins::class);
        $plugins->marketplaceUrl = 'https://marketplace.example.test';

        return (new \ReflectionMethod($plugins, 'downloadMarketplaceArchive'))->invoke($plugins, $url, self::HEADERS);
    }

    public function test_cross_origin_redirect_target_does_not_receive_marketplace_headers(): void
    {
        Http::fake([
            'https://marketplace.example.test/*' => Http::response('', 302, ['Location' => 'https://cdn.example.test/Notes.zip']),
            'https://cdn.example.test/*' => Http::response('zip-bytes', 200, ['Content-Type' => 'application/zip']),
        ]);

        $response = $this->download('https://marketplace.example.test/ltmp-api/download/notes/1.0');

        $this->assertSame('zip-bytes', $response->body());

        $recorded = Http::recorded();
        $this->assertCount(2, $recorded);

        /** @var Request $marketplaceRequest */
        $marketplaceRequest = $recorded[0][0];
        $this->assertSame('license-secret', $marketplaceRequest->header('X-License-Key')[0] ?? null);

        /** @var Request $cdnRequest */
        $cdnRequest = $recorded[1][0];
        $this->assertSame('https://cdn.example.test/Notes.zip', $cdnRequest->url());
        $this->assertFalse($cdnRequest->hasHeader('X-License-Key'));
        $this->assertFalse($cdnRequest->hasHeader('X-Instance-Id'));
        $this->assertFalse($cdnRequest->hasHeader('X-User-Count'));
    }

    public function test_same_origin_redirect_keeps_marketplace_headers(): void
    {
        Http::fake([
            'https://marketplace.example.test/ltmp-api/download/*' => Http::response('', 302, ['Location' => '/files/Notes.zip']),
            'https://marketplace.example.test/files/*' => Http::response('zip-bytes', 200),
        ]);

        $this->download('https://marketplace.example.test/ltmp-api/download/notes/1.0');

        $this->assertSame('license-secret', Http::recorded()[1][0]->header('X-License-Key')[0] ?? null);
    }

    public function test_redirect_to_plain_http_is_refused(): void
    {
        Http::fake([
            'https://marketplace.example.test/*' => Http::response('', 302, ['Location' => 'http://cdn.example.test/Notes.zip']),
            '*' => Http::response('zip-bytes', 200),
        ]);

        try {
            $this->download('https://marketplace.example.test/ltmp-api/download/notes/1.0');
            $this->fail('A redirect to http must be refused');
        } catch (\Exception $e) {
            $this->assertNotInstanceOf(\PHPUnit\Framework\AssertionFailedError::class, $e);
        }

        $this->assertCount(1, Http::recorded());
    }

    public function test_redirect_loop_is_cut_off(): void
    {
        Http::fake([
            '*' => Http::response('', 302, ['Location' => 'https://marketplace.example.test/again']),
        ]);

        $this->expectException(\Exception::class);

        $this->download('https://marketplace.example.test/start');
    }
}
