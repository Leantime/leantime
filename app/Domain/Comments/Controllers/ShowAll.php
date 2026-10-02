<?php

namespace Leantime\Domain\Comments\Controllers;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Symfony\Component\HttpFoundation\Response;

class ShowAll extends Controller
{
    private CommentService $commentService;

    private $module;

    private $id;

    private $entity;

    /**
     * init - initialize private variables
     *
     * @throws Exception
     */
    public function init(
        CommentService $commentService
    ): void {
        $this->commentService = $commentService;
    }

    /**
     * @throws Exception
     */
    public function get($params): Response
    {
        if (! isset($params['module'], $params['entitiyId'], $params['entity'])) {
            throw new Exception('comments module needs to be initialized with module, entity id and entity');
        }

        $this->module = $params['module'];
        $this->id = $params['entitiyId'];
        $this->entity = $params['entity'];

        $comments = $this->commentService->getComments($this->module, $this->id);

        $this->tpl->assign('numComments', count($comments));
        $this->tpl->assign('comments', $comments);

        return $this->tpl->displayPartial('comments.showAll');
    }

    /**
     * @throws BindingResolutionException
     */
    public function post($params): Response
    {
        // Delete comment (POST only: it changes data)
        if (isset($params['delComment']) === true) {
            if ($this->commentService->deleteComment((int) $params['delComment'])) {
                $this->tpl->setNotification($this->language->__('notifications.comment_deleted'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.comment_deleted_error'), 'error');
            }

            return Frontcontroller::redirect(BASE_URL.'/tickets/showTicket/'.(int) ($params['entitiyId'] ?? 0));
        }

        if (isset($params['comment']) === true) {
            if ($this->commentService->addComment($_POST, $this->module, $this->id, $this->entity)) {
                $this->tpl->setNotification($this->language->__('notifications.comment_create_success'), 'success');
            } else {
                $this->tpl->setNotification($this->language->__('notifications.comment_create_error'), 'error');
            }
        }

        return new Response;
    }
}
