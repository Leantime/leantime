<?php

namespace Leantime\Domain\Dashboard\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Projects\Services\Projects;

class ProjectUpdates extends HtmxController
{
    protected static string $view = 'dashboard::components.project-updates';

    private Projects $projectService;

    private CommentService $commentService;

    /**
     * Controller constructor
     */
    public function init(Projects $projectService, CommentService $commentService): void
    {
        $this->commentService = $commentService;
        $this->projectService = $projectService;
    }

    public function get($params): void
    {

        $id = (int) ($params['id']);
        $comments = app()->make(abstract: CommentRepository::class);

        //Delete comment
        if (isset($_GET['delComment']) === true) {
            $commentId = (int) ($_GET['delComment']);

            $comments->deleteComment($commentId);
        }

        $this->fetchComments($id);
    }

    public function post($params): void
    {
        // Manage Post comment

        $currentProjectId = $this->projectService->getCurrentProjectId() ?? $_GET['id'];
        $project = $this->projectService->getProject($currentProjectId);

        if ($project && $this->commentService->addComment($_POST, 'project', $currentProjectId)) {
            $this->fetchComments($currentProjectId);
        }
    }

    public function fetchComments($project_id)
    {
        $comments = $this->commentService->getComments('project', $project_id);
        $this->tpl->assign('comments', $comments);
        $this->tpl->assign('project_id', $project_id);
        $this->tpl->assign('numComments', $this->commentService->countComments('project', $project_id));
    }
}
