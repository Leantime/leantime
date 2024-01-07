<?php

namespace Leantime\Domain\Help\Controllers {

    use Leantime\Core\Controller;

    /**
     *
     */
    class ShowOnboardingDialog extends Controller
    {
        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            //show modals only once per session
            if (!isset($_SESSION['userdata']['settings']["modals"])) {
                $_SESSION['userdata']['settings']["modals"] = array();
            }

            if (isset($params['module']) && $params['module'] != "") {
                $filteredInput = htmlspecialchars($params['module']);

                if (!isset($_SESSION['userdata']['settings']["modals"][$filteredInput])) {
                    $_SESSION['userdata']['settings']["modals"][$filteredInput] = 1;
                }

                return $this->tpl->displayPartial('help.' . $filteredInput);
            }
        }
    }
}
