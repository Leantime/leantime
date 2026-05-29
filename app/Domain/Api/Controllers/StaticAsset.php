<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Api\Contracts\StaticAssetType;
use Leantime\Domain\Api\Services\Api as ApiService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class StaticAsset extends Controller
{
    use DispatchesEvents;

    private Environment $config;

    private ApiService $apiService;

    /**
     * init - initialize private variables
     */
    public function init(Environment $config, ApiService $apiService): void
    {
        $this->config = $config;
        $this->apiService = $apiService;
    }

    /**
     * Displays the static asset by path.
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function get(array $params): Response
    {
        $debug = (bool) $this->config->get('debug', false);

        $asset = $this->apiService->resolveStaticAsset($this->incomingRequest->getPathInfo(), $debug);

        if ($asset === false) {
            return new Response('', 404);
        }

        /** @var StaticAssetType $type */
        $type = $asset['type'];

        return tap(
            new BinaryFileResponse($asset['path']),
            function (BinaryFileResponse $response) use ($type, $debug) {
                $response->headers->set('Content-Type', StaticAssetType::getMimeTypeByExtension($type));
                $response->headers->set('Content-length', filesize(
                    ($filepath = $response->getFile()) instanceof \SplFileInfo ? $filepath->getPathname() : $filepath
                ));

                if (in_array(true, [! $this->incomingRequest->query->has('id'), $debug])) {
                    return;
                }

                $response->headers->set('Cache-Control', 'public, max-age=86500, immutable');
                $response->headers->set('Pragma', 'public');
            }
        );
    }
}
