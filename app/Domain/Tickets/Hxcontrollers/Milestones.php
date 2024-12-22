<?php

namespace Leantime\Domain\Tickets\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Timesheets\Services\Timesheets;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;

class Milestones extends HtmxController
{
    protected static string $view = 'tickets::partials.milestoneCard';

    private Tickets $ticketService;
    private ProjectService $projectService;

    /**
     * Controller constructor
     *
     * @param  Timesheets  $timesheetService
     */
    public function init(Tickets $ticketService, ProjectService $projectService): void
    {
        $this->ticketService = $ticketService;
        $this->projectService = $projectService;
    }

    public function progress()
    {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams['milestoneId']);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams['milestoneId']);

        $this->tpl->assign('milestone', $milestone);
        $this->tpl->assign('percentDone', $percentDone);

        return 'progress';
    }

    public function showCard()
    {

        $getParams = $_GET;

        $milestone = $this->ticketService->getTicket($getParams['milestoneId']);
        $percentDone = $this->ticketService->getMilestoneProgress($getParams['milestoneId']);

        $this->tpl->assign('percentDone', $percentDone);
        $this->tpl->assign('milestone', $milestone);

    }

    public function post($params)
    {
        $result = $this->ticketService->quickAddMilestone($params);
        if (is_numeric($result)) {
            $params['id'] = $result;
            $this->tpl->setNotification($this->language->__('notification.milestone_created_successfully'), 'success');
            $subject = $this->language->__('email_notifications.milestone_created_subject');
            $actual_link = BASE_URL.'/tickets/editMilestone/'.$result;
            $message = sprintf($this->language->__('email_notifications.milestone_created_message'), session('userdata.name'));
            $notification = app()->make(NotificationModel::class);
            $notification->url = [
                'url' => $actual_link,
                'text' => $this->language->__('email_notifications.milestone_created_cta'),
            ];
            $notification->entity = $params;
            $notification->module = 'tickets';
            $notification->projectId = session('currentProject');
            $notification->subject = $subject;
            $notification->authorId = session('userdata.id');
            $notification->message = $message;
            
            $this->projectService->notifyProjectUsers($notification);
            $this->tpl->assign('name', $params['headline']);
            $this->tpl->assign('id', $params['id']);
            $this->tpl->assign('tags', $params['tags']);
            $this->tpl->assign('projectId', session('currentProject'));
            
            $response = $this->tpl->displayPartial('tickets::components.dropdown-item');
            return $response;
        } else {
            $this->tpl->setNotification($this->language->__('notification.saving_milestone_error'), 'error');
            // return Frontcontroller::redirect(BASE_URL.'/tickets/editMilestone/');
            // return $this->tpl->emptyResponse();
        }
    }
}
