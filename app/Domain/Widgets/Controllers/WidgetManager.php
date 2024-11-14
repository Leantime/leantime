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
         * @var SettingRepository
         */
        private Setting $settingRepo;

        /**
         * @var WidgetService
         */
        private Widgets $widgetService;

        /**
         * Initializes the object.
         *
         * @param  Setting  $settingRepo  The setting repository object.
         * @param  Widgets  $widgetService  The widget service object.
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
         * @return HttpFoundation\Response|null The HTTP response, or null if the parameters are invalid.
         */
        public function post(array $params): HttpFoundation\Response
        {
            if (isset($params['action'])) {
                switch ($params['action']) {
                    case 'saveGrid':
                        if (isset($params['data']) && $params['data'] != '') {

                            $this->widgetService->saveGrid($params['data'], session('userdata.id'));

                            if (isset($params['visibilityData']) && $params['visibilityData'] !== null) {
                                if ($params['visibilityData']['visible']) {
                                    $this->widgetService->markWidgetAsSeen(
                                        session('userdata.id'),
                                        $params['visibilityData']['widgetId']
                                    );
                                }
                            }
                        }
                        break;
                }
            }

            return new \Symfony\Component\HttpFoundation\Response;
        }
    }

}
