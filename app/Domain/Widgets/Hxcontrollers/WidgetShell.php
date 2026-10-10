<?php

namespace Leantime\Domain\Widgets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Widgets\Services\Widgets as WidgetService;

/**
 * Renders one dashboard widget's grid item (header, ⋮ menu, lazy content) so the widget manager can
 * add a widget to the grid with the same markup the dashboard renders.
 */
class WidgetShell extends HtmxController
{
    protected static string $view = 'widgets::partials.widgetShell';

    private WidgetService $widgetService;

    /**
     * Initializes dependencies.
     *
     * @param  WidgetService  $widgetService  The widget registry.
     */
    public function init(WidgetService $widgetService): void
    {
        $this->widgetService = $widgetService;
    }

    /**
     * Assigns the widget named by the `id` query parameter; an unknown id renders nothing.
     */
    public function get(): void
    {
        $widgetId = (string) $this->incomingRequest->query('id', '');
        $availableWidgets = $this->widgetService->getAll();

        $this->tpl->assign('widget', $availableWidgets[$widgetId] ?? null);
    }
}
