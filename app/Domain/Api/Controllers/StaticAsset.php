<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Support\Str;
use Leantime\Core\Controller;
use Leantime\Core\Environment;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Core\Eventhelpers;
use Leantime\Domain\Api\Services\Api as ApiService;
use Leantime\Domain\Api\Contracts\StaticAssetType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 *
 */
class StaticAsset extends Controller
{
    use Eventhelpers;

    private Environment $config;
    private ApiService $apiService;

    /**
     * init - initialize private variables
     *
     * @access public
     */
    public function init(
        Environment $config,
        ApiService $apiService,
    ) {
        $this->config = $config;
        $this->apiService = $apiService;
    }

    /**
     * Displays the static asset by path.
     *
     * @access public
     */
    public function get()
    {
        $fullpath = Str::of($this->incomingRequest->getPathInfo())
            ->replaceFirst('/api/static-asset/', APP_ROOT . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR)
            ->replace('/', DIRECTORY_SEPARATOR)
            ->lower();

        // Check if it's a static asset
        if (! defined($constant = StaticAssetType::class . '::' . $fullpath->afterLast('.')->upper())) {
            if ($this->config->get('debug', false)) {
                throw new BadRequestHttpException();
            }

            return new Response('', 404);
        }

        /** @var StaticAssetType $type */
        $type = constant($constant);

        if (! $correctPath = $this->apiService->getCaseCorrectPathFromManifest($fullpath)) {
            if ($this->config->get('debug', false)) {
                throw new NotFoundHttpException();
            }

            return new Response('', 404);
        }

        return tap(
            new BinaryFileResponse($correctPath),
            function (BinaryFileResponse $response) use ($type) {
                $response->headers->set('Content-Type', StaticAssetType::getMimeTypeByExtension($type));
                $response->headers->set('Content-length', filesize(
                    ($filepath = $response->getFile()) instanceof \SplFileInfo ? $filepath->getPathname() : $filepath
                ));

                if (in_array(true, [
                    ! $this->incomingRequest->query->has('id'),
                    $this->config->get('debug', false),
                ])) {
                    return;
                }

                $response->headers->set('Cache-Control', 'public, max-age=86500, immutable');
                $response->headers->set('Pragma', 'public');
            }
        );
    }
}
