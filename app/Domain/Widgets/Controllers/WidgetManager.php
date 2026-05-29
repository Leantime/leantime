<?php

namespace Leantime\Domain\Widgets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Widgets\Services\Widgets;
use Symfony\Component\HttpFoundation;

/**
 * Class WidgetManager
 *
 * This class represents a widget manager.
 */
class WidgetManager extends Controller
{
    /**
     * @var WidgetService
     */
    private Widgets $widgetService;

    /**
     * Initializes the object.
     *
     * @param  Widgets  $widgetService  The widget service object.
     * @return void
     */
    public function init(Widgets $widgetService)
    {
        $this->widgetService = $widgetService;

        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
    }

    /**
     * Returns an HTTP response.
     *
     * @param  array  $params  An array of parameters.
     * @return HttpFoundation\Response The HTTP response.
     */
    public function get(array $params): HttpFoundation\Response
    {
        $availableWidgets = $this->widgetService->getAll();
        $activeWidgets = $this->widgetService->getActiveWidgets(session('userdata.id'));
        $newWidgets = $this->widgetService->getNewWidgets(session('userdata.id'));

        $this->tpl->assign('availableWidgets', $availableWidgets);
        $this->tpl->assign('activeWidgets', $activeWidgets);
        $this->tpl->assign('newWidgets', $newWidgets);

        return $this->tpl->displayPartial('widgets.widgetManager');
    }

    /**
     * Posts data and returns an HTTP response.
     *
     * @param  array  $params  An array of parameters.
     * @return HttpFoundation\Response The HTTP response.
     */
    public function post(array $params): HttpFoundation\Response
    {
        if (isset($params['action'])) {
            switch ($params['action']) {
                case 'saveGrid':
                    if (isset($params['data']) && $params['data'] != '') {
                        $this->widgetService->saveGridForUser(
                            $params['data'],
                            session('userdata.id'),
                            $params['visibilityData'] ?? null
                        );
                    }
                    break;
            }
        }

        return new \Symfony\Component\HttpFoundation\Response;
    }
}
