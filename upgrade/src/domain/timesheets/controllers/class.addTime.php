<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\models\auth\roles;
    use leantime\domain\repositories;
    use leantime\domain\services\auth;

    class addTime
    {

        /**
         * run - display template and edit data
         *
         * @access public
         */
        public function run()
        {

            auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor], true);

            $tpl = new core\template();
            $timesheetsRepo = new repositories\timesheets();
            $language = new core\language();

            $info = '';
            //Only admins and employees
            if(auth::userIsAtLeast(roles::$editor)) {

                $projects = new repositories\projects();
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

                        $values['date'] = $language->getISODateString($_POST['date']);

                    }

                    if (isset($_POST['hours']) && $_POST['hours'] != '') {

                        $values['hours'] = ($_POST['hours']);

                    }

                    if (isset($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] != '') {

                        if ($_POST['invoicedEmpl'] == 'on') {

                            $values['invoicedEmpl'] = 1;

                        }

                        if (isset($_POST['invoicedEmplDate']) && $_POST['invoicedEmplDate'] != '') {

                            $values['invoicedEmplDate'] = $language->getISODateString($_POST['invoicedEmplDate']);

                        }

                    }

                    if (isset($_POST['invoicedComp']) && $_POST['invoicedComp'] != '') {

                        if(auth::userIsAtLeast(roles::$manager)) {

                            if ($_POST['invoicedComp'] == 'on') {

                                $values['invoicedComp'] = 1;

                            }

                            if (isset($_POST['invoicedCompDate']) && $_POST['invoicedCompDate'] != '') {

                                $values['invoicedCompDate'] = $language->getISODateString($_POST['invoicedCompDate']);

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

                        $values['date'] = $language->getFormattedDateString($values['date']);
                        $values['invoicedCompDate'] = $language->getFormattedDateString($values['invoicedCompDate']);
                        $values['invoicedEmplDate'] = $language->getFormattedDateString($values['invoicedEmplDate']);


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

