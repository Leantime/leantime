<?php

namespace Leantime\Domain\Help\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Help\Services\Helper;

class ShowOnboardingDialog extends Controller
{
    protected Helper $helpService;

    /**
     * Injects the Helper service.
     */
    public function init(Helper $helpService): void
    {
        $this->helpService = $helpService;
    }

    /**
     * get - handle get requests
     *
     * Renders the onboarding modal partial for the requested module or route.
     * The "show once per session" bookkeeping and sanitization live in the service.
     *
     * @param  array  $params  Request parameters.
     */
    public function get($params)
    {
        if (isset($params['module']) && $params['module'] != '') {
            $template = $this->helpService->markModalSeenForModule($params['module']);

            return $this->tpl->displayPartial('help.'.$template);
        }

        if (isset($params['route']) && $params['route'] != '') {
            $template = $this->helpService->markModalSeenForRoute($params['route']);

            return $this->tpl->displayPartial('help.'.$template);
        }
    }
}
