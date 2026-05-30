<?php

namespace Leantime\Core\Http\Responses;

use Leantime\Core\Http\Responses\Contracts\LeantimeResponseInterface;
use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;

/**
 * Response type for avatar/profile images served by the domain image controllers
 * (Users\Controllers\ProfileImage, Projects\Controllers\ProjectImage).
 *
 * The source is either a generated SVG avatar, an already-built file Response (an
 * uploaded image streamed by the file service), or a filesystem path. Controllers
 * return `new ImageResponse($source)` and Laravel renders it via toResponse().
 */
class ImageResponse implements LeantimeResponseInterface
{
    /**
     * @param  SVG|Response|string  $image  Generated SVG, a pre-built file Response, or a filesystem path
     */
    public function __construct(private SVG|Response|string $image) {}

    /**
     * Build the cacheable image response.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toResponse($request): Response
    {
        if ($this->image instanceof SVG) {
            return $this->withCacheHeaders(new Response($this->image->toXMLString()), 'image/svg+xml');
        }

        if ($this->image instanceof Response) {
            return $this->image;
        }

        return $this->withCacheHeaders(new Response(file_get_contents($this->image)), 'application/octet-stream');
    }

    /**
     * Applies the public, 24h cache headers shared by every image variant.
     */
    private function withCacheHeaders(Response $response, string $contentType): Response
    {
        $response->headers->set('Content-type', $contentType);
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'max-age=86400');

        return $response;
    }
}
