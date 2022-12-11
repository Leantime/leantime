<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\core\controller;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class editTime extends controller
    {

        private $timesheetsRepo;
        private $projects;
        private $tickets;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init()
        {

            $this->timesheetsRepo = new repositories\timesheets();
            $this->projects = new repositories\projects();
            $this->tickets = new repositories\tickets();

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
            if(auth::userIsAtLeast(roles::$editor)) {

                if (isset($_GET['id']) === true) {

                    $id = ($_GET['id']);

                    $timesheet = $this->timesheetsRepo->getTimesheet($id);

                    $values = array(
                        'id' => $id,
                        'userId' => $timesheet['userId'],
                        'ticket' => $timesheet['ticketId'],
                        'project' => $timesheet['projectId'],
                        'date' => $timesheet['workDate'],
                        'kind' => $timesheet['kind'],
                        'hours' => $timesheet['hours'],
                        'description' => $timesheet['description'],
                        'invoicedEmpl' => $timesheet['invoicedEmpl'],
                        'invoicedComp' => $timesheet['invoicedComp'],
                        'invoicedEmplDate' => $timesheet['invoicedEmplDate'],
                        'invoicedCompDate' => $timesheet['invoicedCompDate'],
                        'paid' => $timesheet['paid'],
                        'paidDate' => $timesheet['paidDate']
                    );

                    if(auth::userIsAtLeast(roles::$manager) || $_SESSION['userdata']['id'] == $values['userId']) {

                        if (isset($_POST['saveForm']) === true) {

                            if (isset($_POST['tickets']) && $_POST['tickets'] != '') {

                                $values['project'] = (int) $_POST['projects'];
                                $values['ticket'] = (int) $_POST['tickets'];

                            }

                            if (isset($_POST['kind']) && $_POST['kind'] != '') {

                                $values['kind'] = ($_POST['kind']);

                            }

                            if (isset($_POST['date']) && $_POST['date'] != '') {

                                $timestamp = date_create_from_format($this->language->__("language.dateformat"), $_POST['date']);

                                $values['date'] = $this->language->getISODateString($_POST['date']);

                            }

                            if (isset($_POST['hours']) && $_POST['hours'] != '') {

                                $values['hours'] = (float)($_POST['hours']);

                            }

                            if (isset($_POST['description']) && $_POST['description'] != '') {

                                $values['description'] = ($_POST['description']);

                            }

                            if(auth::userIsAtLeast(roles::$manager)){

                                if (isset($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] != '') {

                                    if ($_POST['invoicedEmpl'] == 'on') {

                                        $values['invoicedEmpl'] = 1;

                                    }

                                    if (isset($_POST['invoicedEmplDate']) && $_POST['invoicedEmplDate'] != '') {

                                        $values['invoicedEmplDate'] = $this->language->getISODateString($_POST['invoicedEmplDate']);


                                    } else {

                                        $values['invoicedEmplDate'] = date("Y-m-d");

                                    }

                                } else {

                                    $values['invoicedEmpl'] = 0;
                                    $values['invoicedEmplDate'] = '';

                                }

                                if (isset($_POST['invoicedComp']) && $_POST['invoicedComp'] != '') {

                                    if ($_POST['invoicedComp'] == 'on') {

                                        $values['invoicedComp'] = 1;

                                    }

                                    if (isset($_POST['invoicedCompDate']) && $_POST['invoicedCompDate'] != '') {

                                        $values['invoicedCompDate'] = $this->language->getISODateString($_POST['invoicedCompDate']);

                                    } else {

                                        $values['invoicedCompDate'] = date("Y-m-d");

                                    }

                                } else {

                                    $values['invoicedComp'] = 0;
                                    $values['invoicedCompDate'] = '';

                                }

                                if (isset($_POST['paid']) && $_POST['paid'] != '') {


                                    if ($_POST['paid'] == 'on') {

                                        $values['paid'] = 1;

                                    }

                                    if (isset($_POST['paidDate']) && $_POST['paidDate'] != '') {

                                        $values['paidDate'] = $this->language->getISODateString($_POST['paidDate']);

                                    } else {

                                        $values['paidDate'] = date("Y-m-d");

                                    }


                                } else {

                                    $values['paid'] = 0;
                                    $values['paidDate'] = '';

                                }

                            }


                            if ($values['ticket'] != '' && $values['project'] != '') {

                                if ($values['kind'] != '') {

                                    if ($values['date'] != '') {

                                        if ($values['hours'] != '' && $values['hours'] > 0) {

                                            $this->timesheetsRepo->updateTime($values);
                                            $this->tpl->setNotification('notifications.time_logged_success', 'success');

                                            $timesheetUpdated = $this->timesheetsRepo->getTimesheet($id);

                                            $values = array(
                                                'id' => $id,
                                                'userId' => $timesheetUpdated['userId'],
                                                'ticket' => $timesheetUpdated['ticketId'],
                                                'project' => $timesheetUpdated['projectId'],
                                                'date' => $timesheetUpdated['workDate'],
                                                'kind' => $timesheetUpdated['kind'],
                                                'hours' => $timesheetUpdated['hours'],
                                                'description' => $timesheetUpdated['description'],
                                                'invoicedEmpl' => $timesheetUpdated['invoicedEmpl'],
                                                'invoicedComp' => $timesheetUpdated['invoicedComp'],
                                                'invoicedEmplDate' => $timesheetUpdated['invoicedEmplDate'],
                                                'invoicedCompDate' => $timesheetUpdated['invoicedCompDate'],
                                                'paid' => $timesheetUpdated['paid'],
                                                'paidDate' => $timesheetUpdated['paidDate']
                                            );

                                        } else {

                                            $this->tpl->setNotification('notifications.time_logged_error_no_hours', 'error');


                                        }


                                    } else {
                                        $this->tpl->setNotification('notifications.time_logged_error_no_date', 'error');

                                    }

                                } else {

                                    $this->tpl->setNotification('notifications.time_logged_error_no_kind', 'error');

                                }

                            } else {

                                $this->tpl->setNotification('notifications.time_logged_error_no_ticket', 'error');

                            }

                        }


                        $this->tpl->assign('values', $values);

                        $this->tpl->assign('info', $info);
                        $this->tpl->assign('allProjects', $this->projects->getAll());
                        $this->tpl->assign('allTickets', $this->tickets->getAll());
                        $this->tpl->assign('kind', $this->timesheetsRepo->kind);
                        $this->tpl->displayPartial('timesheets.editTime');

                    } else {

                        $this->tpl->displayPartial('errors.error403');

                    }
                } else {
                    $this->tpl->displayPartial('errors.error403');
                }


            } else {

                $this->tpl->displayPartial('errors.error403');

            }

        }

    }
}
