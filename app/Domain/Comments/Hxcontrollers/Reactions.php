<?php

namespace Leantime\Domain\Comments\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Reactions\Services\Reactions as ReactionsService;

class Reactions extends HtmxController
{
    protected static string $view = 'comments::partials.reactions';

    private ReactionsService $reactionsService;

    public function init(ReactionsService $reactionsService): void
    {
        $this->reactionsService = $reactionsService;
    }

    /**
     * Toggle a reaction on a comment.
     */
    public function toggle(): void
    {
        $commentId = (int) ($_GET['commentId'] ?? 0);
        $reaction = $_POST['reaction'] ?? '';
        $userId = session('userdata.id');

        if (! $commentId || ! $reaction || ! $userId) {
            $this->tpl->assign('reactions', []);
            $this->tpl->assign('commentId', 0);
            $this->tpl->assign('userReactions', []);

            return;
        }

        // Check if user already has this exact reaction
        $userReactions = $this->reactionsService->getUserReactions($userId, 'comment', $commentId, $reaction);

        if (! empty($userReactions)) {
            // User clicked the same reaction - remove it (toggle off)
            $this->reactionsService->removeReaction($userId, 'comment', $commentId, $reaction);
        } else {
            // User wants to add a reaction - first remove any existing reactions
            // (only one sentiment reaction allowed per user per comment)
            $allUserReactions = $this->reactionsService->getUserReactions($userId, 'comment', $commentId);
            foreach ($allUserReactions as $existingReaction) {
                $this->reactionsService->removeReaction($userId, 'comment', $commentId, $existingReaction['reaction']);
            }
            // Now add the new reaction
            $this->reactionsService->addReaction($userId, 'comment', $commentId, $reaction);
        }

        $this->loadReactions($commentId, $userId);
    }

    /**
     * Get reactions for a comment.
     */
    public function get(): void
    {
        $commentId = (int) ($_GET['commentId'] ?? 0);
        $userId = session('userdata.id');

        if (! $commentId) {
            $this->tpl->assign('reactions', []);
            $this->tpl->assign('commentId', 0);
            $this->tpl->assign('userReactions', []);

            return;
        }

        $this->loadReactions($commentId, $userId);
    }

    /**
     * Load reactions data for template.
     */
    private function loadReactions(int $commentId, ?int $userId): void
    {
        // Get reactions with user names for tooltips
        $reactionsWithUsers = $this->reactionsService->getEntityReactionsWithUsers('comment', $commentId);

        // Get user's reactions for this comment
        $userReactionsList = [];
        if ($userId) {
            $userReactionsData = $this->reactionsService->getUserReactions($userId, 'comment', $commentId);
            foreach ($userReactionsData as $r) {
                $userReactionsList[] = $r['reaction'];
            }
        }

        $this->tpl->assign('reactions', $reactionsWithUsers ?: []);
        $this->tpl->assign('commentId', $commentId);
        $this->tpl->assign('userReactions', $userReactionsList);
    }
}
