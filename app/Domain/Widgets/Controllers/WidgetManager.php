<?php

namespace Leantime\Domain\Widgets\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Setting\Repositories\Setting;
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
         * @var SettingRepository $settingRepo
         */
        private Setting $settingRepo;

        /**
         * @var WidgetService $widgetService
         */
        private Widgets $widgetService;

        /**
         * Initializes the object.
         *
         * @param Setting $settingRepo   The setting repository object.
         * @param Widgets $widgetService The widget service object.
         * @return void
         */
        public function init(Setting $settingRepo, Widgets $widgetService)
        {
            $this->settingRepo = $settingRepo;
            $this->widgetService = $widgetService;

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        }

        /**
         * Returns an HTTP response.
         *
         * @param array $params An array of parameters.
         * @return HttpFoundation\Response The HTTP response.
         */
        public function get(array $params): HttpFoundation\Response
        {
            $availableWidgets = $this->widgetService->getAll();
            $activeWidgets = $this->widgetService->getActiveWidgets(session("userdata.id"));

            $this->tpl->assign("availableWidgets", $availableWidgets);
            $this->tpl->assign("activeWidgets", $activeWidgets);

            return $this->tpl->displayPartial('widgets.widgetManager');
        }

        /**
         * Posts data and returns an HTTP response.
         *
         * @param array $params An array of parameters.
         * @return HttpFoundation\Response|null The HTTP response, or null if the parameters are invalid.
         */
        public function post(array $params): HttpFoundation\Response
        {
            if (isset($params['action']) && isset($params['data']) && $params['action'] == 'saveGrid' && $params['data'] != '') {
                $this->settingRepo->saveSetting("usersettings." . session("userdata.id") . ".dashboardGrid", serialize($params['data']));
            }
            return new \Symfony\Component\HttpFoundation\Response();
        }
    }

}
