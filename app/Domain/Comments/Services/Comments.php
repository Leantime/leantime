<?php

namespace Leantime\Domain\Comments\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Comments\Permissions\CommentsPermissions;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Reactions\Services\Reactions as ReactionsService;

/**
 * @api
 */
class Comments extends BaseService
{
    private CommentRepository $commentRepository;

    private ProjectService $projectService;

    private LanguageCore $language;

    private ReactionsService $reactionsService;

    public function __construct(
        CommentRepository $commentRepository,
        ProjectService $projectService,
        LanguageCore $language,
        ReactionsService $reactionsService
    ) {
        $this->commentRepository = $commentRepository;
        $this->projectService = $projectService;
        $this->language = $language;
        $this->reactionsService = $reactionsService;
    }

    /**
     * Resolve the entity object backing a comment when the caller didn't
     * pass one. Web controllers usually already have the entity loaded
     * before invoking addComment(); RPC callers don't, and shouldn't
     * have to pre-fetch the entire ticket just to leave a comment.
     */
    private function loadEntityForComment(string $module, int $entityId)
    {
        try {
            if ($module === 'ticket') {
                $ticketService = app()->make(\Leantime\Domain\Tickets\Services\Tickets::class);
                $ticket = $ticketService->getTicket($entityId);

                return $ticket ?: null;
            }
            if ($module === 'project') {
                $projectService = app()->make(\Leantime\Domain\Projects\Services\Projects::class);

                return $projectService->getProject($entityId) ?: null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * @api
     */
    #[RequiresPermission(CommentsPermissions::VIEW, entityScoped: true)]
    public function getComments($module, $entityId, int $commentOrder = 0, int $parent = 0): false|array
    {
        // IDOR fence: comments are read by (module, entityId) with no project scoping in the repo,
        // so authorize VIEW against the host entity's REAL project — a foreign id can no longer leak
        // another project's comment thread over RPC. A null project (client/company-scoped target or
        // an unknown module) falls back to a session-scoped capability check (unchanged behavior).
        $projectId = $this->commentRepository->resolveModuleProjectId((string) $module, (int) $entityId);
        $this->authorize(CommentsPermissions::VIEW, $projectId);

        return $this->commentRepository->getComments($module, $entityId, $parent, $commentOrder);
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::CREATE, entityScoped: true)]
    public function addComment($values, $module, $entityId, $entity = null): bool
    {
        // RPC callers (mobile) typically don't pre-load the entity — they
        // just know module + entityId. Load it server-side so they don't
        // have to ship a whole ticket payload over the wire just to comment.
        if ($entity === null && $module && $entityId) {
            $entity = $this->loadEntityForComment($module, (int) $entityId);
        }

        // Commenting is a commenter+ capability. Resolve the host entity's project so the
        // check is scoped to it (ticket -> projectId; project -> its own id), then authorize.
        $projectId = is_object($entity) && isset($entity->projectId)
            ? (int) $entity->projectId
            : ($module === 'project' ? (int) $entityId : null);

        // Fall back to resolving the host entity's project by (module, id) so canvas-family and
        // other targets (whose $entity may be an array or unloaded) are also project-fenced.
        if ($projectId === null) {
            $projectId = $this->commentRepository->resolveModuleProjectId((string) $module, (int) $entityId);
        }

        $this->authorize(CommentsPermissions::CREATE, $projectId);

        // Default father (parent comment id) to 0 if not provided. The
        // original code REQUIRED it via isset(), which forced every caller
        // to send a value even when there was no parent. 0 is the sentinel
        // for "top-level comment, no parent."
        if (! isset($values['father'])) {
            $values['father'] = $values['parentId'] ?? 0;
        }

        if (isset($values['text']) && $values['text'] != '' && isset($values['father']) && isset($module) && isset($entityId) && isset($entity)) {
            $mapper = [
                'text' => $values['text'],
                'date' => dtHelper()->dbNow()->formatDateTimeForDb(),
                'userId' => (session('userdata.id')),
                'moduleId' => $entityId,
                'commentParent' => ($values['father']),
                'status' => $values['status'] ?? '',
            ];

            $comment = $this->commentRepository->addComment($mapper, $module);

            if ($comment) {
                $mapper['id'] = $comment;

                $currentUrl = CURRENT_URL;

                switch ($module) {
                    case 'ticket':
                        $subject = sprintf($this->language->__('email_notifications.new_comment_todo_with_type_subject'), $this->language->__('label.'.strtolower($entity->type)), $entity->id, strip_tags($entity->headline));
                        $message = sprintf($this->language->__('email_notifications.new_comment_todo_with_type_message'), session('userdata.name'), $this->language->__('label.'.strtolower($entity->type)), strip_tags($entity->headline), strip_tags($values['text']));
                        $linkLabel = $this->language->__('email_notifications.new_comment_todo_cta');
                        $currentUrl = BASE_URL.'#/tickets/showTicket/'.$entity->id;
                        break;
                    case 'project':
                        $subject = sprintf($this->language->__('email_notifications.new_comment_project_subject'), $entityId, strip_tags($entity['name']));
                        $message = sprintf($this->language->__('email_notifications.new_comment_project_message'), session('userdata.name'), strip_tags($entity['name']));
                        $linkLabel = $this->language->__('email_notifications.new_comment_project_cta');
                        break;
                    default:
                        $subject = $this->language->__('email_notifications.new_comment_general_subject');
                        $message = sprintf($this->language->__('email_notifications.new_comment_general_message'), session('userdata.name'));
                        $linkLabel = $this->language->__('email_notifications.new_comment_general_cta');
                        break;
                }

                $notification = app()->make(Notification::class);

                $urlQueryParameter = str_contains($currentUrl, '?') ? '&' : '?';
                $notification->url = [
                    'url' => $currentUrl.$urlQueryParameter.'projectId='.session('currentProject'),
                    'text' => $linkLabel,
                ];

                $notification->entity = $mapper;
                $notification->module = 'comments';
                $notification->action = 'commented';
                // session('currentProject') is set when a user is browsing
                // a project on web; RPC callers (mobile) don't have that
                // session key populated, and the Notification model types
                // projectId as `int` (rejects null). Fall back to the
                // commented-on entity's project so we always have a real
                // integer.
                $entityProjectId = is_object($entity)
                    ? ($entity->projectId ?? 0)
                    : (is_array($entity) ? ($entity['projectId'] ?? $entity['id'] ?? 0) : 0);
                $notification->projectId = (int) (session('currentProject') ?? $entityProjectId);
                $notification->subject = $subject;
                $notification->authorId = session('userdata.id');
                $notification->message = $message;

                $this->projectService->notifyProjectUsers($notification);

                return true;
            }
        }

        return false;
    }

    /**
     * Checks whether the current user is authorized to modify a comment.
     * The caller must be the comment author or have at least manager role.
     *
     * @param  int  $commentId  The comment ID to check
     * @return bool True if authorized, false otherwise
     */
    private function canModifyComment(int $commentId): bool
    {
        $comment = $this->commentRepository->getComment($commentId);

        if (! $comment) {
            return false;
        }

        $currentUserId = session('userdata.id');

        // Comment author can always modify their own comment.
        if ((int) $comment['userId'] === (int) $currentUserId) {
            return true;
        }

        // Otherwise moderation (editing/deleting someone else's comment) is a manager+ capability,
        // scoped to the comment's OWN project so a manager in project A cannot moderate a comment on
        // an entity in project B by id. A null project (client/company-scoped or unknown module)
        // falls back to a session-scoped moderate check (unchanged behavior for those targets).
        $projectId = $this->commentRepository->resolveModuleProjectId(
            (string) ($comment['module'] ?? ''),
            (int) ($comment['moduleId'] ?? 0)
        );

        return $this->can(CommentsPermissions::MODERATE, $projectId);
    }

    /**
     * Edit a comment. The caller must be the comment author or a manager+.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::CREATE, entityScoped: true)]
    public function editComment($values, $id): bool
    {
        if (! $this->canModifyComment((int) $id)) {
            return false;
        }

        return $this->commentRepository->editComment($values['text'], $id);
    }

    /**
     * Delete a comment. The caller must be the comment author or a manager+.
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::CREATE, entityScoped: true)]
    public function deleteComment($commentId): bool
    {
        if (! $this->canModifyComment((int) $commentId)) {
            return false;
        }

        return $this->commentRepository->deleteComment($commentId);
    }

    /**
     * @param  ?int  $projectId  Project ID
     * @param  ?int  $moduleId  Id of the entity to pull comments from
     * @return array
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::VIEW, projectIdParam: 'projectId')]
    public function pollComments(?int $projectId = null, ?int $moduleId = null): array|false
    {

        $comments = $this->commentRepository->getAllAccountComments($projectId, $moduleId);

        foreach ($comments as $key => $comment) {
            if (dtHelper()->isValidDateString($comment['date'])) {
                $comments[$key]['date'] = dtHelper()->parseDbDateTime($comment['date'])->toIso8601ZuluString();
            } else {
                $comments[$key]['date'] = null;
            }
        }

        return $comments;
    }

    /**
     * Toggle a sentiment reaction on a comment for a given user.
     *
     * Enforces the domain rule that a user may only have one sentiment
     * reaction per comment: clicking the reaction the user already has
     * removes it (toggle off); clicking a different reaction removes any
     * existing reactions first, then adds the new one. Unknown reaction
     * types are rejected.
     *
     * @param  int  $userId  Ignored — reactions always act as the session user (kept for RPC
     *                       signature compatibility). See the in-body session pin.
     * @param  int  $commentId  The comment being reacted to
     * @param  string  $reaction  The reaction code (e.g. an emoji key)
     * @return bool True when the toggle was applied, false when the reaction
     *              type is unknown and nothing was changed
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::CREATE, entityScoped: true)]
    public function toggleCommentReaction(int $userId, int $commentId, string $reaction): bool
    {
        // Reactions act on behalf of the SESSION user only. Ignore any caller-supplied id so a
        // client cannot toggle reactions as another user (the $userId param is kept for RPC
        // signature compatibility but is not trusted).
        $userId = (int) session('userdata.id');

        // Validate reaction against known types
        if ($this->reactionsService->getReactionType($reaction) === false) {
            return false;
        }

        // IDOR fence: gate against the comment's OWN project (null -> session-scoped fallback), so
        // reactions can't be toggled on another project's comment by id. SOFT-deny (same false
        // return as a missing comment) rather than throw, so a denied cross-project comment is
        // indistinguishable from a non-existent one — no commentId existence oracle.
        $comment = $this->commentRepository->getComment($commentId);
        if (! $comment) {
            return false;
        }
        if (! $this->can(
            CommentsPermissions::CREATE,
            $this->commentRepository->resolveModuleProjectId(
                (string) ($comment['module'] ?? ''),
                (int) ($comment['moduleId'] ?? 0)
            )
        )) {
            return false;
        }

        // Check if user already has this exact reaction
        $existingSameReaction = $this->reactionsService->getUserReactions($userId, 'comment', $commentId, $reaction);

        if (! empty($existingSameReaction)) {
            // User clicked the same reaction - remove it (toggle off)
            $this->reactionsService->removeReaction($userId, 'comment', $commentId, $reaction);

            return true;
        }

        // User wants to add a reaction - first remove any existing reactions
        // (only one sentiment reaction allowed per user per comment)
        $allUserReactions = $this->reactionsService->getUserReactions($userId, 'comment', $commentId);
        if (is_array($allUserReactions)) {
            foreach ($allUserReactions as $existingReaction) {
                $this->reactionsService->removeReaction($userId, 'comment', $commentId, $existingReaction['reaction']);
            }
        }

        // Now add the new reaction
        $this->reactionsService->addReaction($userId, 'comment', $commentId, $reaction);

        return true;
    }

    /**
     * Build the reaction view data for a comment.
     *
     * Returns the grouped reactions (with user names for tooltips) plus a
     * flat list of the given user's reaction codes for the comment, ready
     * to be assigned to the template.
     *
     * @param  int  $commentId  The comment to load reactions for
     * @param  int  $userId  The current user id (0 when anonymous)
     * @return array{reactions: array, userReactions: list<string>} View data
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::VIEW, entityScoped: true)]
    public function getCommentReactions(int $commentId, int $userId): array
    {
        // IDOR fence: gate VIEW against the comment's OWN project before exposing reactor
        // identities/sentiment, closing the cross-project reaction-read leak by comment id (RPC +
        // Hx). SOFT-deny (same empty payload as a missing comment) rather than throw, so a denied
        // cross-project comment is indistinguishable from a non-existent one — no existence oracle.
        $comment = $this->commentRepository->getComment($commentId);
        if (! $comment) {
            return ['reactions' => [], 'userReactions' => []];
        }
        if (! $this->can(
            CommentsPermissions::VIEW,
            $this->commentRepository->resolveModuleProjectId(
                (string) ($comment['module'] ?? ''),
                (int) ($comment['moduleId'] ?? 0)
            )
        )) {
            return ['reactions' => [], 'userReactions' => []];
        }

        // Get reactions with user names for tooltips
        $reactionsWithUsers = $this->reactionsService->getEntityReactionsWithUsers('comment', $commentId);

        // Flatten the user's reactions for this comment into a list of codes
        $userReactionsList = [];
        if ($userId) {
            $userReactionsData = $this->reactionsService->getUserReactions($userId, 'comment', $commentId);
            if (is_array($userReactionsData)) {
                foreach ($userReactionsData as $r) {
                    $userReactionsList[] = $r['reaction'];
                }
            }
        }

        return [
            'reactions' => $reactionsWithUsers ?: [],
            'userReactions' => $userReactionsList,
        ];
    }
}
