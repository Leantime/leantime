<?php

namespace Unit\app\Core\Http\Responses;

use Leantime\Core\Http\Responses\Contracts\LeantimeResponseInterface;
use Leantime\Core\Http\Responses\ImageResponse;
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * Unit tests for the ImageResponse response type used by the domain image controllers
 * (Users\Controllers\ProfileImage, Projects\Controllers\ProjectImage). It is returned
 * directly from controllers and converted by Laravel's router via the Responsable contract.
 */
class ImageResponseTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_it_is_a_leantime_response(): void
    {
        $this->assertInstanceOf(LeantimeResponseInterface::class, new ImageResponse('/tmp/x'));
    }

    public function test_to_response_renders_svg_with_cache_headers(): void
    {
        $svg = $this->make(SVG::class, [
            'toXMLString' => fn () => '<svg></svg>',
        ]);

        $response = (new ImageResponse($svg))->toResponse(null);

        $this->assertSame('<svg></svg>', $response->getContent());
        $this->assertSame('image/svg+xml', $response->headers->get('Content-type'));
        $this->assertStringContainsString('max-age=86400', $response->headers->get('Cache-Control'));
    }

    public function test_to_response_passes_through_an_existing_response(): void
    {
        $existing = new Response('already built');

        // An uploaded file is already a built Response; it must be returned untouched.
        $this->assertSame($existing, (new ImageResponse($existing))->toResponse(null));
    }
}
