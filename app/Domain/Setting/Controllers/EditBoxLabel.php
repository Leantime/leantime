<?php

namespace Leantime\Domain\Setting\Controllers {

    use Leantime\Core\Controller\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Auth\Services\Auth;
    use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
    use Leantime\Domain\Leancanvas\Repositories\Leancanvas as LeancanvaRepository;
    use Leantime\Domain\Retroscanvas\Repositories\Retroscanvas as RetroscanvaRepository;
    use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

    /**
     *
     */
    class EditBoxLabel extends Controller
    {
        private TicketRepository $ticketsRepo;
        private SettingRepository $settingsRepo;
        private LeancanvaRepository $canvasRepo;
        private RetroscanvaRepository $retroRepo;
        private IdeaRepository $ideaRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         *
         */
        public function init(
            TicketRepository $ticketsRepo,
            SettingRepository $settingsRepo,
            LeancanvaRepository $canvasRepo,
            RetroscanvaRepository $retroRepo,
            IdeaRepository $ideaRepo
        ) {
            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager]);

            $this->ticketsRepo = $ticketsRepo;
            $this->settingsRepo = $settingsRepo;
            $this->canvasRepo = $canvasRepo;
            $this->retroRepo = $retroRepo;
            $this->ideaRepo = $ideaRepo;
        }

        /**
         * get - handle get requests
         *
         * @access public
         *
         */
        public function get($params)
        {

            if (Auth::userIsAtLeast(Roles::$manager)) {
                $currentLabel = "";

                if (isset($params['module']) && isset($params['label'])) {
                    //Move to settings service
                    if ($params['module'] == "ticketlabels") {
                        $stateLabels = $this->ticketsRepo->getStateLabels();
                        $currentLabel = $stateLabels[$params['label']]["name"];
                    }

                    if ($params['module'] == "retrolabels") {
                        $stateLabels = $this->retroRepo->getCanvasLabels();
                        $currentLabel = $stateLabels[$params['label']];
                    }

                    if ($params['module'] == "researchlabels") {
                        $stateLabels = $this->canvasRepo->getCanvasLabels();
                        $currentLabel = $stateLabels[$params['label']];
                    }

                    if ($params['module'] == "idealabels") {
                        $stateLabels = $this->ideaRepo->getCanvasLabels();
                        $currentLabel = $stateLabels[$params['label']]["name"];
                    }
                }

                $this->tpl->assign('currentLabel', $currentLabel);

                return $this->tpl->displayPartial('setting.editBoxDialog');
            } else {
                return $this->tpl->display('errors.error403');
            }
        }

        /**
         * post - handle post requests
         *
         * @access public
         *
         */
        public function post($params)
        {
            //If ID is set its an update
            $sanitizedString = '';
            if (isset($_GET['module']) && isset($_GET['label'])) {
                $sanitizedString = strip_tags($params['newLabel']);

                //Move to settings service
                if ($_GET['module'] == "ticketlabels") {
                    $currentStateLabels = $this->ticketsRepo->getStateLabels();

                    $statusKey = filter_var($_GET['label'], FILTER_SANITIZE_NUMBER_INT);

                    if (isset($currentStateLabels[$statusKey]) && is_array($currentStateLabels[$statusKey])) {
                        $currentStateLabels[$statusKey]['name'] = $sanitizedString;

                        session()->forget("projectsettings.ticketlabels");
                        $this->settingsRepo->saveSetting(
                            "projectsettings." . session("currentProject") . ".ticketlabels",
                            serialize($currentStateLabels)
                        );
                    }
                }

                if ($_GET['module'] == "retrolabels") {
                    $stateLabels = $this->retroRepo->getCanvasLabels();
                    $stateLabels[$_GET['label']] = $sanitizedString;
                    session()->forget("projectsettings.retrolabels");
                    $this->settingsRepo->saveSetting(
                        "projectsettings." . session("currentProject") . ".retrolabels",
                        serialize($stateLabels)
                    );
                }

                if ($_GET['module'] == "researchlabels") {
                    $stateLabels = $this->canvasRepo->getCanvasLabels();
                    $stateLabels[$_GET['label']] = $sanitizedString;
                    session()->forget("projectsettings.researchlabels");
                    $this->settingsRepo->saveSetting(
                        "projectsettings." . session("currentProject") . ".researchlabels",
                        serialize($stateLabels)
                    );
                }

                if ($_GET['module'] == "idealabels") {
                    $stateLabels = $this->ideaRepo->getCanvasLabels();
                    $newStateLabels = array();
                    foreach ($stateLabels as $key => $label) {
                        $newStateLabels[$key] = $label["name"];
                    }
                    $newStateLabels[$_GET['label']] = $sanitizedString;

                    session()->forget("projectsettings.idealabels");
                    $this->settingsRepo->saveSetting(
                        "projectsettings." . session("currentProject") . ".idealabels",
                        serialize($newStateLabels)
                    );
                }

                $this->tpl->setNotification($this->language->__("notifications.label_changed_successfully"), "success");
            }

            $this->tpl->assign('currentLabel', $sanitizedString);
            return $this->tpl->displayPartial('setting.editBoxDialog');
        }

        /**
         * put - handle put requests
         *
         * @access public
         *
         */
        public function put($params)
        {
        }

        /**
         * delete - handle delete requests
         *
         * @access public
         *
         */
        public function delete($params)
        {
        }
    }
}
