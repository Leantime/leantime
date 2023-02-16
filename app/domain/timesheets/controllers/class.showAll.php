<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class showAll extends controller
    {
        private $projects;
        private $timesheetsRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {

            $this->projects = new repositories\projects();
            $this->timesheetsRepo = new repositories\timesheets();
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager]);

            $_SESSION['lastPage'] = BASE_URL . "/timesheets/showAll";

            //Only admins and employees


            if (isset($_POST['saveInvoice']) === true) {
                $invEmpl = '';
                $invComp = '';
                $paid = '';

                if (isset($_POST['invoicedEmpl']) === true) {
                    $invEmpl = $_POST['invoicedEmpl'];
                }

                if (isset($_POST['invoicedComp']) === true) {
                    $invComp = $_POST['invoicedComp'];
                }

                if (isset($_POST['paid']) === true) {
                    $paid = $_POST['paid'];
                }

                $this->timesheetsRepo->updateInvoices($invEmpl, $invComp, $paid);
            }



            $invEmplCheck = '0';
            $invCompCheck = '0';

            $projectFilter =  "";
            $dateFromMk = mktime(0, 0, 0, date("m"), '1', date("Y"));
            $dateToMk = mktime(0, 0, 0, date("m"), date("t"), date("Y"));

            $dateFrom = date("Y-m-d", $dateFromMk);
            $dateTo = date("Y-m-d", $dateToMk);
            $kind = 'all';
            $userId = 'all';

            if (isset($_POST['kind']) && $_POST['kind'] != '') {
                $kind = strip_tags($_POST['kind']);
            }

            if (isset($_POST['userId']) && $_POST['userId'] != '') {
                $userId = strip_tags($_POST['userId']);
            }

            if (isset($_POST['dateFrom']) && $_POST['dateFrom'] != '') {
                $dateFrom = $this->language->getISODateString($_POST['dateFrom']);
            }

            if (isset($_POST['dateTo']) && $_POST['dateTo'] != '') {
                $dateTo = $this->language->getISODateString($_POST['dateTo']);
            }

            if (isset($_POST['invEmpl']) === true) {
                $invEmplCheck = $_POST['invEmpl'];

                if ($invEmplCheck == 'on') {
                    $invEmplCheck = '1';
                } else {
                    $invEmplCheck = '0';
                }
            } else {
                $invEmplCheck = '0';
            }

            if (isset($_POST['invComp']) === true) {
                $invCompCheck = ($_POST['invComp']);

                if ($invCompCheck == 'on') {
                    $invCompCheck = '1';
                } else {
                    $invCompCheck = '0';
                }
            } else {
                $invCompCheck = '0';
            }

            if (isset($_POST['paid']) === true) {
                $paidCheck = ($_POST['paid']);

                if ($paidCheck == 'on') {
                    $paidCheck = '1';
                } else {
                    $paidCheck = '0';
                }
            } else {
                $paidCheck = '0';
            }

            $projectFilter = $_SESSION['currentProject'];
            if (isset($_POST['project']) && $_POST['project'] != '') {
                $projectFilter = strip_tags($_POST['project']);
            }

            if (isset($_POST['export'])) {
                $values = array(
                    'project' => $projectFilter,
                    'kind' => $kind,
                    'userId' => $userId,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'invEmplCheck' => $invEmplCheck,
                    'invCompCheck' => $invCompCheck
                );
                $this->timesheetsRepo->export($values);
            }

            $user = new repositories\users();
            $employees = $user->getAll();

            $this->tpl->assign('employeeFilter', $userId);
            $this->tpl->assign('employees', $employees);
            $this->tpl->assign('dateFrom', $dateFrom);
            $this->tpl->assign('dateTo', $dateTo);

            $this->tpl->assign('actKind', $kind);
            $this->tpl->assign('kind', $this->timesheetsRepo->kind);
            $this->tpl->assign('invComp', $invCompCheck);
            $this->tpl->assign('invEmpl', $invEmplCheck);
            $this->tpl->assign('paid', $paidCheck);
            $this->tpl->assign('allProjects', $this->projects->getAll());
            $this->tpl->assign('projectFilter', $projectFilter);
            $this->tpl->assign('allTimesheets', $this->timesheetsRepo->getAll($projectFilter, $kind, $dateFrom, $dateTo, $userId, $invEmplCheck, $invCompCheck, '-1', $paidCheck));

            $this->tpl->display('timesheets.showAll');
        }
    }

}
