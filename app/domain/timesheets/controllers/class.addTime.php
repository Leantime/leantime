<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class addTime extends controller
    {
        private repositories\timesheets $timesheetsRepo;
        private repositories\projects $projects;
        private repositories\tickets $tickets;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(
            repositories\timesheets $timesheetsRepo,
            repositories\projects $projects,
            repositories\tickets $tickets
        ) {
            $this->timesheetsRepo = $timesheetsRepo;
            $this->projects = $projects;
            $this->tickets = $tickets;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor], true);

            $info = '';
            //Only admins and employees
            if (auth::userIsAtLeast(roles::$editor)) {
                $values = array(
                    'userId' => $_SESSION['userdata']['id'],
                    'ticket' => '',
                    'project' => '',
                    'date' => '',
                    'kind' => '',
                    'hours' => '',
                    'description' => '',
                    'invoicedEmpl' => '',
                    'invoicedComp' => '',
                    'invoicedEmplDate' => '',
                    'invoicedCompDate' => '',
                    'paid' => '',
                    'paidDate' => ''
                );

                if (isset($_POST['save']) === true || isset($_POST['saveNew']) === true) {
                    if (isset($_POST['tickets']) && $_POST['tickets'] != '') {
                        $temp = ($_POST['tickets']);

                        $tempArr = explode('|', $temp);

                        $values['project'] = $tempArr[0];
                        $values['ticket'] = $tempArr[1];
                    }

                    if (isset($_POST['kind']) && $_POST['kind'] != '') {
                        $values['kind'] = ($_POST['kind']);
                    }

                    if (isset($_POST['date']) && $_POST['date'] != '') {
                        $values['date'] = $this->language->getISODateString($_POST['date']);
                    }

                    if (isset($_POST['hours']) && $_POST['hours'] != '') {
                        $values['hours'] = ($_POST['hours']);
                    }

                    if (isset($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] != '') {
                        if ($_POST['invoicedEmpl'] == 'on') {
                            $values['invoicedEmpl'] = 1;
                        }

                        if (isset($_POST['invoicedEmplDate']) && $_POST['invoicedEmplDate'] != '') {
                            $values['invoicedEmplDate'] = $this->language->getISODateString($_POST['invoicedEmplDate']);
                        }
                    }

                    if (isset($_POST['invoicedComp']) && $_POST['invoicedComp'] != '') {
                        if (auth::userIsAtLeast(roles::$manager)) {
                            if ($_POST['invoicedComp'] == 'on') {
                                $values['invoicedComp'] = 1;
                            }

                            if (isset($_POST['invoicedCompDate']) && $_POST['invoicedCompDate'] != '') {
                                $values['invoicedCompDate'] = $this->language->getISODateString($_POST['invoicedCompDate']);
                            }
                        }
                    }

                    if (isset($_POST['paid']) && $_POST['paid'] != '') {
                        if (auth::userIsAtLeast(roles::$manager)) {
                            if ($_POST['paid'] == 'on') {
                                $values['paid'] = 1;
                            }

                            if (isset($_POST['paidDate']) && $_POST['paidDate'] != '') {
                                $values['paidDate'] = $this->language->getISODateString($_POST['paidDate']);
                            }
                        }
                    }


                    if (isset($_POST['description']) && $_POST['description'] != '') {
                        $values['description'] = ($_POST['description']);
                    }


                    if ($values['ticket'] != '' && $values['project'] != '') {
                        if ($values['kind'] != '') {
                            if ($values['date'] != '') {
                                if ($values['hours'] != '' && $values['hours'] > 0) {
                                    $this->timesheetsRepo->addTime($values);
                                    $info = 'TIME_SAVED';
                                } else {
                                    $info = 'NO_HOURS';
                                }
                            } else {
                                $info = 'NO_DATE';
                            }
                        } else {
                            $info = 'NO_KIND';
                        }
                    } else {
                        $info = 'NO_TICKET';
                    }

                    if (isset($_POST['save']) === true) {
                        $values['date'] = $this->language->getFormattedDateString($values['date']);
                        $values['invoicedCompDate'] = $this->language->getFormattedDateString($values['invoicedCompDate']);
                        $values['invoicedEmplDate'] = $this->language->getFormattedDateString($values['invoicedEmplDate']);
                        $values['paidDate'] = $this->language->getFormattedDateString($values['paidDate']);


                        $this->tpl->assign('values', $values);
                    } elseif (isset($_POST['saveNew']) === true) {
                        $values = array(
                            'userId' => $_SESSION['userdata']['id'],
                            'ticket' => '',
                            'project' => '',
                            'date' => '',
                            'kind' => '',
                            'hours' => '',
                            'description' => '',
                            'invoicedEmpl' => '',
                            'invoicedComp' => '',
                            'invoicedEmplDate' => '',
                            'invoicedCompDate' => '',
                            'paid' => '',
                            'paidDate' => ''
                        );

                        $this->tpl->assign('values', $values);
                    }
                }

                $this->tpl->assign('info', $info);
                $this->tpl->assign('allProjects', $this->timesheetsRepo->getAll());
                $this->tpl->assign('allTickets', $this->timesheetsRepo->getAll());
                $this->tpl->assign('kind', $this->timesheetsRepo->kind);
                $this->tpl->display('timesheets.addTime');
            } else {
                $this->tpl->display('errors.error403');
            }
        }
    }
}
