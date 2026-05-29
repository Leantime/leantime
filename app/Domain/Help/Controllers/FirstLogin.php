<?php

namespace Leantime\Domain\Help\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Help\Services\Helper;
use Symfony\Component\HttpFoundation\Response;

class FirstLogin extends Controller
{
    private Helper $helperService;

    /**
     * Injects the Helper service.
     */
    public function init(Helper $helperService): void
    {
        $this->helperService = $helperService;
    }

    /**
     * get - handle get requests
     *
     * Renders the appropriate first-login onboarding step partial.
     *
     * @param  array  $params  Request parameters.
     */
    public function get($params): Response
    {
        $step = $this->helperService->resolveFirstLoginStep($_GET['step'] ?? null);

        if ($step['isEnd']) {
            return $this->tpl->displayPartial($step['template']);
        }

        $this->tpl->assign('currentStep', $step['key']);
        $this->tpl->assign('nextStep', $step['next']);

        return $this->tpl->displayPartial($step['template']);
    }

    /**
     * post - handle post requests
     *
     * Delegates onboarding step handling to the Helper service and redirects
     * to the resolved next step.
     *
     * @param  array  $params  Request parameters.
     */
    public function post($params): Response
    {
        $result = $this->helperService->handleFirstLoginStep($params);

        if (! $result['valid']) {
            return Frontcontroller::redirect(BASE_URL.'/help/firstLogin');
        }

        return Frontcontroller::redirect(BASE_URL.'/help/firstLogin?step='.$result['next']);
    }
}
