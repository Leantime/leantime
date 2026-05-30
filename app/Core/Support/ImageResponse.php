<?php

namespace Leantime\Core\Support;

use SVG\SVG;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds HTTP responses for avatar/profile images served by the domain image
 * controllers (Users\Controllers\ProfileImage, Projects\Controllers\ProjectImage).
 *
 * The image source is either an SVG object (generated avatar), an already-built
 * Response (an uploaded file streamed by the file service) or a filesystem path.
 * This used to live on the heavy Api\Services\Api service; it is a pure formatter
 * with no dependencies, so it belongs in Core where any domain controller can use
 * it without dragging in the Api service's repositories.
 */
class ImageResponse
{
    /**
     * Build a cacheable image response from an avatar source.
     *
     * @param  SVG|Response|string  $image  Generated SVG, a pre-built file Response, or a filesystem path
     */
    public static function make(SVG|Response|string $image): Response
    {
        if ($image instanceof SVG) {
            $response = new Response($image->toXMLString());
            $response->headers->set('Content-type', 'image/svg+xml');
            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'max-age=86400');

            return $response;
        }

        if ($image instanceof Response) {
            return $image;
        }

        $response = new Response(file_get_contents($image));
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'max-age=86400');

        return $response;
    }
}
