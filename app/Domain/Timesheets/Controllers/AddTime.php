<?php

namespace Leantime\Domain\Timesheets\Controllers {

    use Leantime\Core\Controller;
    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
    use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
    use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
    use Leantime\Domain\Auth\Services\Auth;
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class AddTime extends Controller
    {
        /**
         * @var TimesheetRepository $timesheetsRepo
         */
        private TimesheetRepository $timesheetsRepo;

        /**
         * @var ProjectRepository $projects
         */
        private ProjectRepository $projects;

        /**
         * @var TicketRepository $tickets
         */
        private TicketRepository $tickets;

        /**
         * init - initialize private variables
         *
         * @param TimesheetRepository $timesheetsRepo
         * @param ProjectRepository   $projects
         * @param TicketRepository    $tickets
         * @access public
         * @return void
         */
        public function init(
            TimesheetRepository $timesheetsRepo,
            ProjectRepository $projects,
            TicketRepository $tickets
        ): void {
            $this->timesheetsRepo = $timesheetsRepo;
            $this->projects = $projects;
            $this->tickets = $tickets;
        }

        /**
         * run - display template and edit data
         *
         * @access public
         * @return Response
         */
        public function run(): Response
        {

            Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

            $info = '';
            //Only admins and employees
            if (Auth::userIsAtLeast(Roles::$editor)) {
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
                    'paidDate' => '',
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
                        $values['date'] = $this->language->getISODateString($_POST['date'], "b");
                    }

                    if (isset($_POST['hours']) && $_POST['hours'] != '') {
                        $values['hours'] = ($_POST['hours']);
                    }

                    if (isset($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] != '') {
                        if ($_POST['invoicedEmpl'] == 'on') {
                            $values['invoicedEmpl'] = 1;
                        }

                        if (isset($_POST['invoicedEmplDate']) && $_POST['invoicedEmplDate'] != '') {
                            $values['invoicedEmplDate'] = $this->language->getISODateString($_POST['invoicedEmplDate'], "m");
                        }
                    }

                    if (isset($_POST['invoicedComp']) && $_POST['invoicedComp'] != '') {
                        if (Auth::userIsAtLeast(Roles::$manager)) {
                            if ($_POST['invoicedComp'] == 'on') {
                                $values['invoicedComp'] = 1;
                            }

                            if (isset($_POST['invoicedCompDate']) && $_POST['invoicedCompDate'] != '') {
                                $values['invoicedCompDate'] = $this->language->getISODateString($_POST['invoicedCompDate'], "m");
                            }
                        }
                    }

                    if (isset($_POST['paid']) && $_POST['paid'] != '') {
                        if (Auth::userIsAtLeast(Roles::$manager)) {
                            if ($_POST['paid'] == 'on') {
                                $values['paid'] = 1;
                            }

                            if (isset($_POST['paidDate']) && $_POST['paidDate'] != '') {
                                $values['paidDate'] = $this->language->getISODateString($_POST['paidDate'], "m");
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
                            'paidDate' => '',
                        );

                        $this->tpl->assign('values', $values);
                    }
                }

                $this->tpl->assign('info', $info);
                $this->tpl->assign('allProjects', $this->timesheetsRepo->getAll());
                $this->tpl->assign('allTickets', $this->timesheetsRepo->getAll());
                $this->tpl->assign('kind', $this->timesheetsRepo->kind);
                return $this->tpl->display('timesheets.addTime');
            } else {
                return $this->tpl->display('errors.error403', responseCode: 403);
            }
        }
    }
}
