<?php

declare(strict_types=1);

namespace Leantime\Domain\Comments\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Comments\Services\Comments as CommentService;

/**
 * HTMX Controller for managing comment reactions (toggle, get).
 */
class Reactions extends HtmxController
{
    protected static string $view = 'comments::partials.reactions';

    private CommentService $commentService;

    public function init(CommentService $commentService): void
    {
        $this->commentService = $commentService;
    }

    /**
     * Toggle a reaction on a comment.
     */
    public function toggle(): void
    {
        $commentId = (int) $this->incomingRequest->query->get('commentId', 0);
        $reaction = (string) $this->incomingRequest->request->get('reaction', '');
        $userId = (int) session('userdata.id');

        if (! $commentId || ! $reaction || ! $userId) {
            $this->assignEmptyReactions();

            return;
        }

        if (! $this->commentService->toggleCommentReaction($userId, $commentId, $reaction)) {
            $this->assignEmptyReactions();

            return;
        }

        $this->loadReactions($commentId, $userId);
    }

    /**
     * Get reactions for a comment.
     */
    public function get(): void
    {
        $commentId = (int) $this->incomingRequest->query->get('commentId', 0);
        $userId = (int) session('userdata.id');

        if (! $commentId) {
            $this->assignEmptyReactions();

            return;
        }

        $this->loadReactions($commentId, $userId);
    }

    /**
     * Assign reaction data for a comment to the template.
     */
    private function loadReactions(int $commentId, int $userId): void
    {
        $reactionData = $this->commentService->getCommentReactions($commentId, $userId);

        $this->tpl->assign('reactions', $reactionData['reactions']);
        $this->tpl->assign('commentId', $commentId);
        $this->tpl->assign('userReactions', $reactionData['userReactions']);
    }

    /**
     * Assign the empty reaction state to the template.
     */
    private function assignEmptyReactions(): void
    {
        $this->tpl->assign('reactions', []);
        $this->tpl->assign('commentId', 0);
        $this->tpl->assign('userReactions', []);
    }
}
