<?php

namespace Leantime\Domain\Help\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Core\Providers\Frontcontroller;
    use Leantime\Domain\Help\Services\Helper;

    class ShowOnboardingDialog extends Controller
    {

        protected Helper $helpService;

        public function init(Helper $helpService) {
            $this->helpService = $helpService;

        }
        /**
         * get - handle get requests
         */
        public function get($params)
        {

            // show modals only once per session
            if (! session()->exists('usersettings.modals')) {
                session(['usersettings.modals' => []]);
            }

            if (isset($params['module']) && $params['module'] != '') {
                $filteredInput = htmlspecialchars($params['module']);

                if (! session()->exists('usersettings.modals.'.$filteredInput)) {
                    session(['usersettings.modals.'.$filteredInput => 1]);
                }

                return $this->tpl->displayPartial('help.'.$filteredInput);
            }

            if (isset($params['route']) && $params['route'] != '') {
                $filteredInput = htmlspecialchars($params['route']);

                $modal = $this->helpService->getHelperModalByRoute($filteredInput);

                if (! session()->exists('usersettings.modals.'.$modal['template'])) {
                    session(['usersettings.modals.'.$modal['template'] => 1]);
                }

                return $this->tpl->displayPartial('help.'.$modal['template']);
            }

        }
    }
}
