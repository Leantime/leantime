<?php

namespace Unit\app\Core\Support;

use Leantime\Core\Support\ImageResponse;
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;
use Unit\TestCase;

/**
 * Unit tests for the image response builder relocated from Api\Services\Api to Core
 * so the domain image controllers (Users\Controllers\ProfileImage,
 * Projects\Controllers\ProjectImage) can format avatar responses without depending
 * on the Api service.
 */
class ImageResponseTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    public function test_make_renders_svg_with_cache_headers(): void
    {
        $svg = $this->make(SVG::class, [
            'toXMLString' => fn () => '<svg></svg>',
        ]);

        $response = ImageResponse::make($svg);

        $this->assertSame('<svg></svg>', $response->getContent());
        $this->assertSame('image/svg+xml', $response->headers->get('Content-type'));
        $this->assertStringContainsString('max-age=86400', $response->headers->get('Cache-Control'));
    }

    public function test_make_passes_through_an_existing_response(): void
    {
        $existing = new Response('already built');

        // An uploaded file is already a built Response; it must be returned untouched.
        $this->assertSame($existing, ImageResponse::make($existing));
    }
}
