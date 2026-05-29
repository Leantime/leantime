<?php

namespace Leantime\Domain\Ideas\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Ideas\Services\Ideas as IdeaService;

class IdeaDialog extends Controller
{
    private IdeaService $ideaService;

    /**
     * init - initialize private variables
     */
    public function init(IdeaService $ideaService): void
    {
        $this->ideaService = $ideaService;
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
                $this->ideaService->removeIdeaComment($commentId);
                $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success', 'ideacomment_deleted');
            }

            // Delete milestone relationship
            if (isset($params['removeMilestone']) === true) {
                $this->ideaService->removeMilestone((int) $params['id']);
                $this->tpl->setNotification($this->language->__('notifications.milestone_detached'), 'success');
            }

            $canvasItem = $this->ideaService->getIdeaItem((int) $params['id']);
            $comments = $this->ideaService->getIdeaComments('idea', $canvasItem['id']);
            $this->tpl->assign('numComments', $this->ideaService->countIdeaComments('ideas', $canvasItem['id']));

        } else {

            $type = $params['type'] ?? 'idea';

            $canvasItem = $this->ideaService->getIdeaItem(null, $type);

            $comments = [];
        }

        $this->tpl->assign('comments', $comments);

        $this->tpl->assign('milestones', $this->ideaService->getProjectMilestones((int) session('currentProject')));
        $this->tpl->assign('canvasTypes', $this->ideaService->getCanvasTypes());
        $this->tpl->assign('canvasItem', $canvasItem);
        $this->tpl->assign('currentCanvas', (int) session('currentIdeaCanvas'));

        return $this->tpl->displayPartial('ideas.ideaDialog');
    }

    /**
     * post - handle post requests
     */
    public function post($params)
    {

        if (isset($params['comment']) === true) {
            if ($params['text'] != '') {
                $this->ideaService->addIdeaComment(
                    $params['text'],
                    (int) $_GET['id'],
                    $params['father'],
                    (int) session('currentProject'),
                    (int) session('userdata.id')
                );

                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');

                return Frontcontroller::redirect(BASE_URL.'/ideas/ideaDialog/'.(int) $_GET['id']);
            }
        }

        $id = $_GET['id'] ?? null;

        // changeItem is set for new or edited item changes.
        if (isset($params['changeItem'])) {
            if (isset($params['itemId']) && $params['itemId'] != '') {
                if (isset($params['description']) === true) {
                    $currentCanvasId = (int) ($params['canvasId'] ?? session('currentIdeaCanvas'));

                    $input = [
                        'box' => $params['box'],
                        'description' => $params['description'],
                        'status' => $params['status'],
                        'data' => $params['data'],
                        'tags' => $params['tags'],
                        'itemId' => $params['itemId'],
                        'canvasId' => $currentCanvasId,
                        'milestoneId' => $params['milestoneId'],
                        'newMilestone' => $params['newMilestone'] ?? '',
                        'existingMilestone' => $params['existingMilestone'] ?? '',
                    ];

                    $this->ideaService->updateIdeaItem(
                        $input,
                        (int) session('currentProject'),
                        (int) session('userdata.id')
                    );

                    $comments = $this->ideaService->getIdeaComments('leancanvasitem', (int) $params['itemId']);
                    $this->tpl->assign(
                        'numComments',
                        $this->ideaService->countIdeaComments('leancanvasitem', (int) $params['itemId'])
                    );
                    $this->tpl->assign('comments', $comments);

                    $this->tpl->setNotification($this->language->__('notification.idea_edited'), 'success');

                    return Frontcontroller::redirect(BASE_URL.'/ideas/ideaDialog/'.(int) $params['itemId']);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

                    return Frontcontroller::redirect(BASE_URL.'/ideas/ideaDialog/');
                }
            } else {
                if (isset($_POST['description']) === true) {
                    $currentCanvasId = (int) ($params['canvasId'] ?? session('currentIdeaCanvas'));

                    $input = [
                        'box' => $params['box'],
                        'description' => $params['description'],
                        'status' => $params['status'],
                        'data' => $params['data'],
                        'canvasId' => $currentCanvasId,
                    ];

                    $id = $this->ideaService->createIdeaItem(
                        $input,
                        (int) session('currentProject'),
                        (int) session('userdata.id')
                    );

                    $this->tpl->setNotification($this->language->__('notification.idea_created'), 'success', 'idea_created');

                    return Frontcontroller::redirect(BASE_URL.'/ideas/ideaDialog/'.(int) $id);
                } else {
                    $this->tpl->setNotification($this->language->__('notification.please_enter_title'), 'error');

                    return Frontcontroller::redirect(BASE_URL.'/ideas/ideaDialog/');
                }
            }

        }

        $canvasItem = $this->ideaService->getRawIdeaItem($id !== null ? (int) $id : null);

        $this->tpl->assign('canvasTypes', $this->ideaService->getCanvasTypes());
        $this->tpl->assign('canvasItem', $canvasItem);

        return $this->tpl->displayPartial('ideas.ideaDialog');
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
