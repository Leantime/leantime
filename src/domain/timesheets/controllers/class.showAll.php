<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class showAll
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            $tpl = new core\template();
            $timesheetsRepo = new repositories\timesheets();
            $_SESSION['lastPage'] = BASE_URL."/timesheets/showAll";
            $language = new core\language();

            //Only admins and employees

            $projects = new repositories\projects();
            $helper = new core\helper();


            if (isset($_POST['saveInvoice']) === true) {

                $invEmpl = '';
                $invComp = '';

                if (isset($_POST['invoicedEmpl']) === true) {
                    $invEmpl = $_POST['invoicedEmpl'];
                }

                if (isset($_POST['invoicedComp']) === true) {
                    $invComp = $_POST['invoicedComp'];
                }

                $timesheetsRepo->updateInvoices($invEmpl, $invComp);


            }


            $invEmplCheck = '0';
            $invCompCheck = '0';

            $projectFilter =  $_SESSION['currentProject'];
            $dateFromMk = mktime(0, 0, 0, date("m"), '1', date("Y"));
            $dateToMk = mktime(0, 0, 0, date("m"), date("t"), date("Y"));

            $dateFrom = date("Y-m-d", $dateFromMk);
            $dateTo = date("Y-m-d", $dateToMk);
            $kind = 'all';
            $userId = 'all';

            if (isset($_POST['kind']) && $_POST['kind'] != '') {

                $kind = ($_POST['kind']);

            }

            if (isset($_POST['userId']) && $_POST['userId'] != '') {

                $userId = ($_POST['userId']);

            }

            if (isset($_POST['dateFrom']) && $_POST['dateFrom'] != '') {

                $dateFrom = $language->getISODateString($_POST['dateFrom']);

            }

            if (isset($_POST['dateTo']) && $_POST['dateTo'] != '') {

                $dateTo = $language->getISODateString($_POST['dateTo']);

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
                $timesheetsRepo->export($values);
            }

            $user = new repositories\projects();
            $employees = $user->getUsersAssignedToProject($_SESSION['currentProject']);

            $tpl->assign('employeeFilter', $userId);
            $tpl->assign('employees', $employees);
            $tpl->assign('dateFrom', $dateFrom);
            $tpl->assign('dateTo', $dateTo);

            $tpl->assign('actKind', $kind);
            $tpl->assign('kind', $timesheetsRepo->kind);
            $tpl->assign('invComp', $invCompCheck);
            $tpl->assign('invEmpl', $invEmplCheck);
            $tpl->assign('helper', $helper);
            $tpl->assign('projectFilter', $projectFilter);
            $tpl->assign('allTimesheets', $timesheetsRepo->getAll($projectFilter, $kind, $dateFrom, $dateTo, $userId, $invEmplCheck, $invCompCheck));

            $tpl->display('timesheets.showAll');


        }

    }

}
