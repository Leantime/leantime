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
            if (!session()->exists("userdata")->settings["modals"]) {
                session(["userdata.settings.modals" => array()]);
            }

            if (isset($params['module']) && $params['module'] != "") {
                $filteredInput = htmlspecialchars($params['module']);

                if (!session()->exists("userdata.settings.modals." . $filteredInput)) {
                    session(["userdata.settings.modals." . $filteredInput => 1]);
                }

                return $this->tpl->displayPartial('help.' . $filteredInput);
            }
        }
    }
}
