<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class editTime
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

            $info = '';
            //Only admins and employees
            if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['role'] == 'employee') {


                if (isset($_GET['id']) === true) {

                    $projects = new repositories\projects();
                    $helper = new core\helper();
                    $tickets = new repositories\tickets();

                    $id = ($_GET['id']);

                    $timesheet = $timesheetsRepo->getTimesheet($id);


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
                        'invoicedCompDate' => $timesheet['invoicedCompDate']
                    );

                    if ($_SESSION['userdata']['role'] == 'admin' || $_SESSION['userdata']['id'] == $values['userId']) {

                        if (isset($_POST['save']) === true) {

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

                                $dateFormat = $values['date'];
                                $values['date'] = $helper->date2timestamp($_POST['date']); //($helper->timestamp2date($_POST['date'], 4));

                            }

                            if (isset($_POST['hours']) && $_POST['hours'] != '') {

                                $values['hours'] = ($_POST['hours']);

                            }

                            if (isset($_POST['description']) && $_POST['description'] != '') {

                                $values['description'] = ($_POST['description']);

                            }

                            if (isset($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] != '') {

                                if ($_POST['invoicedEmpl'] == 'on') {

                                    $values['invoicedEmpl'] = 1;

                                }

                                if (isset($_POST['invoicedEmplDate']) && $_POST['invoicedEmplDate'] != '') {

                                    $values['invoicedEmplDate'] = ($helper->timestamp2date($_POST['invoicedEmplDate'], 4));

                                } else {

                                    $values['invoicedEmplDate'] = date("Y-m-d");

                                }

                            } else {

                                $values['invoicedEmpl'] = 0;
                                $values['invoicedEmplDate'] = '';

                            }

                            if ($_SESSION['userdata']['role'] == 'admin') {

                                if (isset($_POST['invoicedComp']) && $_POST['invoicedComp'] != '') {


                                    if ($_POST['invoicedComp'] == 'on') {

                                        $values['invoicedComp'] = 1;

                                    }

                                    if (isset($_POST['invoicedCompDate']) && $_POST['invoicedCompDate'] != '') {

                                        $values['invoicedCompDate'] = ($helper->timestamp2date($_POST['invoicedCompDate'], 4));

                                    } else {

                                        $values['invoicedCompDate'] = date("Y-m-d");

                                    }


                                } else {

                                    $values['invoicedComp'] = 0;
                                    $values['invoicedCompDate'] = '';

                                }

                            }


                            if ($values['ticket'] != '' && $values['project'] != '') {

                                if ($values['kind'] != '') {

                                    if ($values['date'] != '') {

                                        if ($values['hours'] != '' && $values['hours'] > 0) {

                                            $timesheetsRepo->updateTime($values);
                                            $tpl->setNotification('SAVE_SUCCESS', 'success');
                                            $values['description'] = $_POST['description'];

                                        } else {

                                            $tpl->setNotification('NO_HOURS', 'error');


                                        }


                                    } else {
                                        $tpl->setNotification('NO_DATE', 'error');

                                    }

                                } else {

                                    $tpl->setNotification('NO_KIND', 'error');

                                }

                            } else {

                                $tpl->setNotification('NO_TICKET', 'error');

                            }


                        }

                        $values['date'] = $helper->timestamp2date($values['date'], 2);
                        $values['invoicedCompDate'] = $helper->timestamp2date($values['invoicedCompDate'], 2);
                        $values['invoicedEmplDate'] = $helper->timestamp2date($values['invoicedEmplDate'], 2);


                        if (isset($dateFormat)) {
                            $values['date'] = $dateFormat;
                        }
                        $tpl->assign('values', $values);

                        $tpl->assign('info', $info);
                        $tpl->assign('allProjects', $projects->getAll());
                        $tpl->assign('allTickets', $tickets->getAll());
                        $tpl->assign('kind', $timesheetsRepo->kind);
                        $tpl->display('timesheets.editTime');

                    } else {
                        $tpl->display('general.error');
                    }
                } else {
                    $tpl->display('general.error');
                }
            } else {

                $tpl->display('general.error');

            }

        }

    }
}
