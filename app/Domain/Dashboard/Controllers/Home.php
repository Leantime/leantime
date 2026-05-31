<?php

namespace Leantime\Domain\Dashboard\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Setting\Services\Setting;
use Leantime\Domain\Widgets\Services\Widgets;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class Home extends Controller
{
    private Setting $settingsSvc;

    private Widgets $widgetService;

    public function init(
        Setting $settingsSvc,
        Widgets $widgetService
    ): void {

        $this->settingsSvc = $settingsSvc;
        $this->widgetService = $widgetService;

        session(['lastPage' => BASE_URL.'/dashboard/home']);
    }

    /**
     * @throws BindingResolutionException
     */
    public function get(array $params): Response
    {
        // Debug param to reset dashboard
        if (isset($params['resetDashboard']) === true) {
            $this->widgetService->resetDashboard(session('userdata.id'));
        }

        $dashboardGrid = $this->widgetService->getActiveWidgets(session('userdata.id'));
        $this->tpl->assign('dashboardGrid', $dashboardGrid);

        $completedOnboarding = $this->settingsSvc->onboardingHandler();
        if ($completedOnboarding instanceof RedirectResponse) {
            return $completedOnboarding;
        }

        $this->tpl->assign('completedOnboarding', $completedOnboarding);

        return $this->tpl->display('dashboard.home');
    }

    /**
     * @throws BindingResolutionException
     */
    public function post($params): Response
    {
        // Handle saving dashboard grid layout
        if (isset($params['action']) && $params['action'] === 'saveGrid' &&
            isset($params['data']) && $params['data'] !== '') {
            $this->widgetService->saveGrid($params['data'], session('userdata.id'));

            return new Response;
        }

        return Frontcontroller::redirect(BASE_URL.'/dashboard/home');
    }
}
