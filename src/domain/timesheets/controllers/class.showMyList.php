<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;

    class showMyList
    {

        public function __construct() {

            $this->tpl = new core\template();
            $this->timesheetService = new services\timesheets();
            $_SESSION['lastPage'] = BASE_URL."/timesheets/showMyList";
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {


            $projectFilter =  $_SESSION['currentProject'];
            $dateFrom = mktime(0, 0, 0, date("m"), '1', date("Y"));
            $dateTo = mktime(0, 0, 0, date("m"), date("t"), date("Y"));
            $dateFrom = date("Y-m-d 00:00:00", $dateFrom);
            $dateTo = date("Y-m-d 00:00:00", $dateTo);
            $kind = 'all';
            $language = new core\language();

            if (isset($_POST['kind']) && $_POST['kind'] != '') {

                $kind = ($_POST['kind']);

            }

            if (isset($_POST['dateFrom']) && $_POST['dateFrom'] != '') {

                $dateFrom =  $language->getISODateString($_POST['dateFrom']);

            }

            if (isset($_POST['dateTo']) && $_POST['dateTo'] != '') {

                $dateTo =  $language->getISODateString($_POST['dateTo']);

            }

            $this->tpl->assign('dateFrom', $dateFrom);
            $this->tpl->assign('dateTo', $dateTo);
            $this->tpl->assign('actKind', $kind);
            $this->tpl->assign('kind', $this->timesheetService->getLoggableHourTypes());
            $this->tpl->assign('allTimesheets', $this->timesheetService->getAll(-1, $kind, $dateFrom, $dateTo, $_SESSION['userdata']['id'], 0, 0));

            $this->tpl->display('timesheets.showMyList');


        }

    }

}
