<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Api\Services\I18n as I18nService;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class I18n
 *
 * This class handles attaching the language file to JavaScript.
 */
class I18n extends Controller
{
    private I18nService $i18nService;

    /**
     * init - initialize private variables
     */
    public function init(I18nService $i18nService): void
    {
        $this->i18nService = $i18nService;
    }

    /**
     * Attach the language file to javascript
     *
     * @todo refactor to remove user timezone and timeformat and move to user settings
     *
     * @param  array  $params  or body of the request.
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {
        $response = new Response(
            $this->i18nService->buildJsDictionary(),
            200
        );

        $response->headers->set('Content-Type', 'application/javascript');
        $response->headers->set('Pragma', 'public');

        // Disable cache for this file since datetime format settings is stored in here as well.
        // Need to find a better cache busting option for this.
        // $response->headers->set("Cache-Control", 'max-age=86400');

        return $response;
    }
}
