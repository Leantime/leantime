<?php

namespace Leantime\Domain\Widgets\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Setting\Repositories\Setting;
    use Leantime\Domain\Sprints\Services\Sprints as SprintService;
    use Leantime\Domain\Sprints\Models\Sprints as SprintModel;
    use DateTime;
    use DateInterval;
    use Leantime\Domain\Auth\Services\Auth;

    /**
     *
     */
    class WidgetManager extends Controller
    {
        private Setting $settingRepo;

        /**
         * constructor - initialize private variables
         *
         * @access public
         *
         */
        public function init(Setting $settingRepo)
        {
            $this->settingRepo = $settingRepo;

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor]);
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {


            $this->tpl->displayPartial('widgets.widget-manager');
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            if (isset($params['action']) && isset($params['data']) && $params['action'] == 'saveGrid' && $params['data'] != '') {
                $this->settingRepo->saveSetting("usersettings." . $_SESSION['userdata']['id'] . ".dashboardGrid", serialize($params['data']));
                return;
            }
        }
    }

}
