<?php

namespace Leantime\Domain\Dashboard\Services;

use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Reactions\Models\Reactions;
use Leantime\Domain\Reactions\Services\Reactions as ReactionService;

/**
 * Dashboard service - business logic backing the project dashboard view.
 *
 * Wraps the comment and reaction data assembly the dashboard needs so the
 * controllers stay thin (param-read -> service call -> assign -> Response).
 *
 * @api
 */
class Dashboard
{
    /**
     * @param  CommentService  $commentService  Comment business logic (authorization-aware deletes)
     * @param  CommentRepository  $commentRepository  Comment data access (replies, counts)
     * @param  ReactionService  $reactionsService  Reaction business logic
     */
    public function __construct(
        private CommentService $commentService,
        private CommentRepository $commentRepository,
        private ReactionService $reactionsService,
    ) {}

    /**
     * Gets the top-level comments for a project with their replies attached.
     *
     * Mirrors the legacy dashboard behavior: top-level comments (parent 0)
     * each enriched with a 'replies' key holding their direct replies.
     *
     * @param  int  $projectId  The project to load comments for
     * @return array<int, array<string, mixed>> Top-level comments, each with a 'replies' array
     *
     * @api
     */
    public function getProjectCommentsWithReplies(int $projectId): array
    {
        if ($projectId <= 0) {
            throw new \InvalidArgumentException('A valid project id is required to load comments.');
        }

        $comments = $this->commentService->getComments('project', $projectId, 0);

        if (! is_array($comments)) {
            return [];
        }

        return array_map(function ($comment) {
            $comment['replies'] = $this->commentRepository->getReplies($comment['id']);

            return $comment;
        }, $comments);
    }

    /**
     * Counts all comments attached to a project.
     *
     * @param  int  $projectId  The project to count comments for
     * @return int The number of comments
     *
     * @api
     */
    public function countProjectComments(int $projectId): int
    {
        if ($projectId <= 0) {
            throw new \InvalidArgumentException('A valid project id is required to count comments.');
        }

        return (int) $this->commentRepository->countComments('project', $projectId);
    }

    /**
     * Deletes a project comment.
     *
     * Delegates to the comment service so its author/manager authorization
     * check is enforced (the legacy dashboard deleted directly with no check).
     *
     * @param  int  $commentId  The comment to delete
     * @return bool True if the comment was deleted
     *
     * @api
     */
    public function deleteProjectComment(int $commentId): bool
    {
        return $this->commentService->deleteComment($commentId);
    }

    /**
     * Adds a comment to a project.
     *
     * @param  array<string, mixed>  $values  The submitted comment values
     * @param  int  $projectId  The project the comment belongs to
     * @param  array<string, mixed>  $project  The loaded project entity
     * @return bool True if the comment was created
     *
     * @api
     */
    public function addProjectComment(array $values, int $projectId, array $project): bool
    {
        return $this->commentService->addComment($values, 'project', $projectId, $project);
    }

    /**
     * Determines whether a user has favorited a project.
     *
     * @param  int  $userId  The user to check
     * @param  int  $projectId  The project to check
     * @return bool True if the user has a favorite reaction on the project
     *
     * @api
     */
    public function userHasFavoritedProject(int $userId, int $projectId): bool
    {
        $userReaction = $this->reactionsService->getUserReactions($userId, 'project', $projectId, Reactions::$favorite);

        return $userReaction && is_array($userReaction) && count($userReaction) > 0;
    }

    /**
     * Builds the base URL used to delete a dashboard comment.
     *
     * Derives scheme/host/path from the current request URL and appends the
     * delComment query parameter, leaving the caller to suffix the comment id.
     *
     * @return string The delete-comment URL base (ends with 'delComment=')
     *
     * @api
     */
    public function buildDeleteCommentUrlBase(): string
    {
        $url = parse_url(CURRENT_URL);

        return $url['scheme'].'://'.$url['host'].$url['path'].'?delComment=';
    }
}
