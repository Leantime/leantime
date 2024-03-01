<?php

namespace Leantime\Domain\Timesheets\Controllers;

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
class EditTime extends Controller
{
    private TimesheetRepository $timesheetsRepo;
    private ProjectRepository $projects;
    private TicketRepository $tickets;

    /**
     * init - initialize private variables
     *
     * @param TimesheetRepository $timesheetsRepo
     * @param ProjectRepository   $projects
     * @param TicketRepository    $tickets
     *
     * @return void
     */
    public function init(
        TimesheetRepository $timesheetsRepo,
        ProjectRepository $projects,
        TicketRepository $tickets
    ) {
        $this->timesheetsRepo = $timesheetsRepo;
        $this->projects = $projects;
        $this->tickets = $tickets;
    }

    /**
     * run - display template and edit data
     *
     * @return Response
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $info = '';
        //Only admins and employees
        if (Auth::userIsAtLeast(Roles::$editor)) {
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
                    'paidDate' => $timesheet['paidDate'],
                );

                if (Auth::userIsAtLeast(Roles::$manager) || $_SESSION['userdata']['id'] == $values['userId']) {
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

                            $values['date'] = format($_POST['date'])->isoDateMid();
                        }

                        if (isset($_POST['hours']) && $_POST['hours'] != '') {
                            $values['hours'] = (float)($_POST['hours']);
                        }

                        if (isset($_POST['description']) && $_POST['description'] != '') {
                            $values['description'] = ($_POST['description']);
                        }

                        if (Auth::userIsAtLeast(Roles::$manager)) {
                            if (isset($_POST['invoicedEmpl']) && $_POST['invoicedEmpl'] != '') {
                                if ($_POST['invoicedEmpl'] == 'on') {
                                    $values['invoicedEmpl'] = 1;
                                }

                                if (isset($_POST['invoicedEmplDate']) && $_POST['invoicedEmplDate'] != '') {
                                    $values['invoicedEmplDate'] = format($_POST['invoicedEmplDate'])->isoDateMid();
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
                                    $values['invoicedCompDate'] = format($_POST['invoicedCompDate'])->isoDateMid();
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
                                    $values['paidDate'] = format($_POST['paidDate'])->isoDateMid();
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
                                            'paidDate' => $timesheetUpdated['paidDate'],
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
                    return $this->tpl->displayPartial('timesheets.editTime');
                } else {
                    return $this->tpl->displayPartial('errors.error403');
                }
            } else {
                return $this->tpl->displayPartial('errors.error403');
            }
        } else {
            return $this->tpl->displayPartial('errors.error403');
        }
    }
}
