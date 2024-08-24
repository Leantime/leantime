<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Container\BindingResolutionException;
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
     *
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
     *
     * @return Response
     *
     * @throws BindingResolutionException
     */
    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager, Roles::$editor], true);

        $info = '';
        //Only admins and employees
        if (Auth::userIsAtLeast(Roles::$editor)) {
            $values = array(
                'userId' => session("userdata.id"),
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

                if (!empty($_POST['kind'])) {
                    $values['kind'] = ($_POST['kind']);
                }

                if (!empty($_POST['date'])) {
                    $values['date'] = (new Carbon($_POST['date'], session("usersettings.timezone")))->setTimezone('UTC');
                }

                if (!empty($_POST['hours'])) {
                    $values['hours'] = ($_POST['hours']);
                }

                if (!empty($_POST['invoicedEmpl'])) {
                    if ($_POST['invoicedEmpl'] == 'on') {
                        $values['invoicedEmpl'] = 1;
                    }

                    if (!empty($_POST['invoicedEmplDate'])) {
                        $values['invoicedEmplDate'] = Carbon::now(session("usersettings.timezone"))->setTimezone('UTC');
                    }
                }

                if (!empty($_POST['invoicedComp'])) {
                    if (Auth::userIsAtLeast(Roles::$manager)) {
                        if ($_POST['invoicedComp'] == 'on') {
                            $values['invoicedComp'] = 1;
                        }

                        if (!empty($_POST['invoicedCompDate'])) {
                            $values['invoicedCompDate'] = Carbon::now(session("usersettings.timezone"))->setTimezone('UTC');
                        }
                    }
                }

                if (!empty($_POST['paid'])) {
                    if (Auth::userIsAtLeast(Roles::$manager)) {
                        if ($_POST['paid'] == 'on') {
                            $values['paid'] = 1;
                        }

                        if (!empty($_POST['paidDate'])) {
                            $values['paidDate'] = Carbon::now(session("usersettings.timezone"))->setTimezone('UTC');
                        }
                    }
                }


                if (!empty($_POST['description'])) {
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
                    $this->tpl->assign('values', $values);
                } elseif (isset($_POST['saveNew']) === true) {
                    $values = array(
                        'userId' => session("userdata.id"),
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
