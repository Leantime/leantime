<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\Carbon;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Timesheets\Repositories\Timesheets as TimesheetRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class EditTime extends Controller
{
    private TimesheetRepository $timesheetsRepo;
    private ProjectRepository $projects;
    private TicketRepository $tickets;

    // This is the date we get back from the database, when no date has been sat. This is somewhat a hack and should
    // be looked into.
    const EMPTY_DATE = '0000-00-00 00:00:00';

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
        // Only admins and employees
        if (Auth::userIsAtLeast(Roles::$editor)) {
            if (isset($_GET['id']) === true) {
                $id = ($_GET['id']);

                $timesheet = $this->timesheetsRepo->getTimesheet($id);

                // Date validation.
                $timesheet['invoicedEmplDate'] = $timesheet['invoicedEmplDate'] == self::EMPTY_DATE ? 'now' : $timesheet['invoicedEmplDate'];
                $timesheet['invoicedCompDate'] = $timesheet['invoicedCompDate'] == self::EMPTY_DATE ? 'now' : $timesheet['invoicedCompDate'];
                $timesheet['paidDate'] = $timesheet['paidDate'] == self::EMPTY_DATE ? 'now' : $timesheet['paidDate'];

                $values = array(
                    'id' => $id,
                    'userId' => $timesheet['userId'],
                    'ticket' => $timesheet['ticketId'],
                    'project' => $timesheet['projectId'],
                    'date' => new Carbon($timesheet['workDate'], 'UTC'),
                    'kind' => $timesheet['kind'],
                    'hours' => $timesheet['hours'],
                    'description' => $timesheet['description'],
                    'invoicedEmpl' => $timesheet['invoicedEmpl'],
                    'invoicedComp' => $timesheet['invoicedComp'],
                    'invoicedEmplDate' => new Carbon($timesheet['invoicedEmplDate'], 'UTC'),
                    'invoicedCompDate' => new Carbon($timesheet['invoicedCompDate'], 'UTC'),
                    'paid' => $timesheet['paid'],
                    'paidDate' => new Carbon($timesheet['paidDate'], 'UTC'),
                );

                if (Auth::userIsAtLeast(Roles::$manager) || session("userdata.id") == $values['userId']) {
                    if (isset($_POST['saveForm']) === true) {
                        if (!empty($_POST['tickets'])) {
                            $values['project'] = (int) $_POST['projects'];
                            $values['ticket'] = (int) $_POST['tickets'];
                        }

                        if (!empty($_POST['kind'])) {
                            $values['kind'] = ($_POST['kind']);
                        }

                        if (!empty($_POST['date'])) {
                            $date = dtHelper()->parseUserDateTime($_POST['date'], "start")->formatDateTimeForDb();
                            $values['date'] = $date;
                        }

                        if (!empty($_POST['hours'])) {
                            $values['hours'] = (float)($_POST['hours']);
                        }

                        if (!empty($_POST['description'])) {
                            $values['description'] = ($_POST['description']);
                        }

                        if (Auth::userIsAtLeast(Roles::$manager)) {
                            if (!empty($_POST['invoicedEmpl'])) {
                                if ($_POST['invoicedEmpl'] == 'on') {
                                    $values['invoicedEmpl'] = 1;
                                }

                                if (!empty($_POST['invoicedEmplDate'])) {
                                    $date = dtHelper()->parseUserDateTime($_POST['invoicedEmplDate'], "start")->formatDateTimeForDb();
                                    $values['invoicedEmplDate'] = $date;
                                } else {
                                    $values['invoicedEmplDate'] = dtHelper()->userNow()->formatDateTimeForDb();
                                }
                            } else {
                                $values['invoicedEmpl'] = 0;
                                $values['invoicedEmplDate'] = '';
                            }

                            if (!empty($_POST['invoicedComp'])) {
                                if ($_POST['invoicedComp'] == 'on') {
                                    $values['invoicedComp'] = 1;
                                }

                                if (!empty($_POST['invoicedCompDate'])) {
                                    $date = dtHelper()->parseUserDateTime($_POST['invoicedCompDate'], "start")->formatDateTimeForDb();
                                    $values['invoicedCompDate'] = $date;
                                } else {
                                    $values['invoicedCompDate'] = dtHelper()->userNow()->formatDateTimeForDb();
                                }
                            } else {
                                $values['invoicedComp'] = 0;
                                $values['invoicedCompDate'] = '';
                            }

                            if (!empty($_POST['paid'])) {
                                if ($_POST['paid'] == 'on') {
                                    $values['paid'] = 1;
                                }

                                if (!empty($_POST['paidDate'])) {
                                    $date = dtHelper()->parseUserDateTime($_POST['paidDate'], "start")->formatDateTimeForDb();
                                    $date->setTimezone('UTC');
                                    $values['paidDate'] = $date;
                                } else {
                                    $values['paidDate'] = dtHelper()->userNow()->formatDateTimeForDb();
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

                                        // Date validation.
                                        $timesheetUpdated['invoicedEmplDate'] = $timesheetUpdated['invoicedEmplDate'] == self::EMPTY_DATE ? 'now' : $timesheetUpdated['invoicedEmplDate'];
                                        $timesheetUpdated['invoicedCompDate'] = $timesheetUpdated['invoicedCompDate'] == self::EMPTY_DATE ? 'now' : $timesheetUpdated['invoicedCompDate'];
                                        $timesheetUpdated['paidDate'] = $timesheetUpdated['paidDate'] == self::EMPTY_DATE ? 'now' : $timesheetUpdated['paidDate'];

                                        $values = array(
                                            'id' => $id,
                                            'userId' => $timesheetUpdated['userId'],
                                            'ticket' => $timesheetUpdated['ticketId'],
                                            'project' => $timesheetUpdated['projectId'],
                                            'date' => new Carbon($timesheetUpdated['workDate'], 'UTC'),
                                            'kind' => $timesheetUpdated['kind'],
                                            'hours' => $timesheetUpdated['hours'],
                                            'description' => $timesheetUpdated['description'],
                                            'invoicedEmpl' => $timesheetUpdated['invoicedEmpl'],
                                            'invoicedComp' => $timesheetUpdated['invoicedComp'],
                                            'invoicedEmplDate' => new Carbon($timesheetUpdated['invoicedEmplDate'], 'UTC'),
                                            'invoicedCompDate' => new Carbon($timesheetUpdated['invoicedCompDate'], 'UTC'),
                                            'paid' => $timesheetUpdated['paid'],
                                            'paidDate' => new Carbon($timesheetUpdated['paidDate'], 'UTC'),
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
