<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

class EditMilestone extends Controller
{
    private TicketService $ticketService;

    private CommentService $commentsService;

    private ProjectService $projectService;

    /**
     * init - initialize private variables
     */
    public function init(
        TicketService $ticketService,
        CommentService $commentsService,
        ProjectService $projectService
    ): void {
        $this->ticketService = $ticketService;
        $this->commentsService = $commentsService;
        $this->projectService = $projectService;
    }

    /**
     * get - handle get requests
     */
    public function get($params)
    {
        if (isset($params['id'])) {
            // Delete comment
            if (isset($params['delComment']) === true) {
                $commentId = (int) ($params['delComment']);
                $this->commentsService->deleteComment($commentId);

                $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
            }

            $milestone = $this->ticketService->getMilestone((int) $params['id']);

            if ($milestone === false || ! isset($milestone->id)) {
                $this->tpl->setNotification($this->language->__('notifications.could_not_find_milestone'), 'error');

                return Frontcontroller::redirect(BASE_URL.'/tickets/roadmap/');
            }

            // Ensure this ticket belongs to the current project
            if (session('currentProject') != $milestone->projectId) {
                $this->projectService->changeCurrentSessionProject($milestone->projectId);

                return Frontcontroller::redirect(BASE_URL.'/tickets/editMilestone/'.$milestone->id);
            }

            $comments = $this->commentsService->getComments('ticket', $params['id']);
        } else {
            $milestone = $this->ticketService->getNewMilestone();
            $comments = [];
        }

        $allAssignedprojects = $this->projectService->getProjectsAssignedToUser(session('userdata.id'), 'open');
        $this->tpl->assign('allAssignedprojects', $allAssignedprojects);

        $this->tpl->assign('statusLabels', $this->ticketService->getStatusLabels());
        $this->tpl->assign('comments', $comments);

        $allProjectMilestones = $this->ticketService->getAllMilestones(['sprint' => '', 'type' => 'milestone', 'currentProject' => session('currentProject')]);
        $this->tpl->assign('milestones', $allProjectMilestones);
        $this->tpl->assign('users', $this->projectService->getUsersAssignedToProject(session('currentProject')));
        $this->tpl->assign('milestone', $milestone);

        return $this->tpl->displayPartial('tickets.milestoneDialog');
    }

    /**
     * post - handle post requests
     */
    public function post($params)
    {
        // If ID is set its an update
        if (isset($_GET['id']) && (int) $_GET['id'] > 0) {
            $params['id'] = (int) $_GET['id'];

            if (isset($params['comment']) === true) {
                $milestone = $this->ticketService->getMilestone($params['id']);

                if ($this->ticketService->addMilestoneComment($params, $milestone)) {
                    $this->tpl->setNotification($this->language->__('notifications.comment_added_successfully'), 'success');
                } else {
                    $this->tpl->setNotification($this->language->__('notifications.problem_saving_your_comment'), 'error');
                }

                return Frontcontroller::redirect(BASE_URL.'/tickets/editMilestone/'.$params['id']);
            }

            if (isset($params['headline']) === true) {
                if ($this->ticketService->updateMilestoneFromDialog($params)) {
                    $this->tpl->setNotification($this->language->__('notification.milestone_edited_successfully'), 'success');
                } else {
                    $this->tpl->setNotification($this->language->__('notification.saving_milestone_error'), 'error');
                }

                return Frontcontroller::redirect(BASE_URL.'/tickets/editMilestone/'.$params['id']);
            }

            return Frontcontroller::redirect(BASE_URL.'/tickets/editMilestone/'.$params['id']);
        }

        $result = $this->ticketService->createMilestoneFromDialog($params);

        if (is_numeric($result)) {
            $this->tpl->setNotification($this->language->__('notification.milestone_created_successfully'), 'success');

            return Frontcontroller::redirect(BASE_URL.'/tickets/editMilestone/'.$result);
        }

        $this->tpl->setNotification($this->language->__('notification.saving_milestone_error'), 'error');

        return Frontcontroller::redirect(BASE_URL.'/tickets/editMilestone/');
    }

    /**
     * put - handle put requests
     */
    public function put($params) {}

    /**
     * delete - handle delete requests
     */
    public function delete($params) {}
}
