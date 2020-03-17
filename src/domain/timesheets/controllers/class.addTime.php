<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;

    class addTime
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
            if(core\login::userIsAtLeast("developer")) {

                $projects = new repositories\projects();
                $helper = new core\helper();
                $tickets = new repositories\tickets();
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
                    'invoicedCompDate' => ''
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

                        $values['date'] = ($helper->timestamp2date($_POST['date'], 4));

                    }

                    if (isset($_POST['hours']) && $_POST['hours'] != '') {

                        $values['hours'] = ($_POST['hours']);

                    }

                    if (isset($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] != '') {

                        if ($_POST['invoicedEmpl'] == 'on') {

                            $values['invoicedEmpl'] = 1;

                        }

                        if (isset($_POST['invoicedEmplDate']) && $_POST['invoicedEmplDate'] != '') {

                            $values['invoicedEmplDate'] = ($helper->timestamp2date($_POST['invoicedEmplDate'], 4));

                        }

                    }

                    if (isset($_POST['invoicedComp']) && $_POST['invoicedComp'] != '') {

                        if(core\login::userIsAtLeast("clientManager")) {

                            if ($_POST['invoicedComp'] == 'on') {

                                $values['invoicedComp'] = 1;

                            }

                            if (isset($_POST['invoicedCompDate']) && $_POST['invoicedCompDate'] != '') {

                                $values['invoicedCompDate'] = ($helper->timestamp2date($_POST['invoicedCompDate'], 4));

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

                                    $timesheetsRepo->addTime($values);
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

                        $values['date'] = $helper->timestamp2date($values['date'], 2);
                        $values['invoicedCompDate'] = $helper->timestamp2date($values['invoicedCompDate'], 2);
                        $values['invoicedEmplDate'] = $helper->timestamp2date($values['invoicedEmplDate'], 2);


                        $tpl->assign('values', $values);

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
                            'invoicedCompDate' => ''
                        );

                        $tpl->assign('values', $values);

                    }

                }

                $tpl->assign('info', $info);
                $tpl->assign('allProjects', $projects->getAll());
                $tpl->assign('allTickets', $tickets->getAll());
                $tpl->assign('kind', $timesheetsRepo->kind);
                $tpl->display('timesheets.addTime');

            } else {

                $tpl->display('general.error');

            }

        }

    }
}

