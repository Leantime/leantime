<?php

namespace Leantime\Domain\Comments\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\NotFoundException;
use Leantime\Core\Exceptions\ValidationException;
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

            // Canvas-family targets (wiki articles, ideas, *canvasitem). The notification path for
            // these only needs the item's project, so a minimal record is enough; without it the
            // comment was permission-checked and then silently discarded (#3756). A missing item
            // resolves to no project and stays null.
            if ($module === 'article' || $module === 'idea' || str_ends_with($module, 'canvasitem')) {
                $projectId = $this->commentRepository->resolveModuleProjectId($module, $entityId);

                return $projectId !== null ? ['id' => $entityId, 'projectId' => $projectId] : null;
            }
        } catch (\Throwable $e) {
            return null;
        }

        return null;
    }

    /**
     * Get the comments of an entity.
     *
     * Accepts the same module aliases as addComment() (e.g. "tickets"), so a comment written
     * through an alias can be read back through it.
     *
     * @param  string  $module  ticket, project, article, idea, {type}canvasitem (or an alias).
     * @param  int  $entityId  The entity id.
     * @param  int  $commentOrder  Sort order flag passed to the repository.
     * @param  int  $parent  Parent comment id (0 = top level).
     * @param  bool  $strict  Reject modules other than ticket, project, article, idea and {type}canvasitem
     *                        (API/MCP callers); web callers also read client and plugin modules.
     * @return false|array The comments.
     *
     * @throws ValidationException When $strict and the module is unknown, or the entity id is invalid.
     * @throws NotFoundException When a ticket/article/idea/canvas item with that id does not exist.
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::VIEW, entityScoped: true)]
    public function getComments($module, $entityId, int $commentOrder = 0, int $parent = 0, bool $strict = false): false|array
    {
        $module = $this->normalizeCommentModule($module);
        $isKnownModule = is_string($module) && $this->isResolvableCommentModule($module);

        if ($strict && ! $isKnownModule) {
            $message = "Unknown comment module '{$module}'. Expected one of: ticket, project, article, idea, {type}canvasitem.";

            throw new ValidationException(['module' => [$message]], $message);
        }

        if ($strict && (int) $entityId <= 0) {
            $message = 'entityId must be a positive id.';

            throw new ValidationException(['entityId' => [$message]], $message);
        }

        // A project id always "resolves" to itself, so check it really exists and is visible.
        if ($strict && $module === 'project' && ! $this->projectService->getProject((int) $entityId)) {
            throw new NotFoundException("Could not find project #{$entityId}, or you do not have access to it.");
        }

        // IDOR fence: comments are read by (module, entityId) with no project scoping in the repo,
        // so authorize VIEW against the host entity's REAL project — a foreign id can no longer leak
        // another project's comment thread over RPC. A null project (client/company-scoped target or
        // an unknown module) falls back to a session-scoped capability check (unchanged behavior).
        $projectId = $this->commentRepository->resolveModuleProjectId((string) $module, (int) $entityId);

        // A known entity type whose id does not resolve does not exist (or is another canvas type):
        // report that instead of an empty thread (#3704).
        if ($isKnownModule && $module !== 'project' && (int) $entityId > 0 && $projectId === null) {
            throw new NotFoundException("Could not find {$module} #{$entityId}, or you do not have access to it.");
        }

        $this->authorize(CommentsPermissions::VIEW, $projectId);

        return $this->commentRepository->getComments($module, $entityId, $parent, $commentOrder);
    }

    /**
     * Map common plural/alias spellings of a comment module to the stored module name.
     *
     * API clients naturally guess "tickets" or "projects"; those used to fail silently (#3704).
     * Goal comments are stored on the goal canvas item.
     */
    private function normalizeCommentModule(mixed $module): mixed
    {
        if (! is_string($module)) {
            return $module;
        }

        $aliases = [
            'tickets' => 'ticket',
            'task' => 'ticket',
            'tasks' => 'ticket',
            'projects' => 'project',
            'articles' => 'article',
            'ideas' => 'idea',
            'goal' => 'goalcanvasitem',
            'goalcanvas' => 'goalcanvasitem',
            'goals' => 'goalcanvasitem',
        ];

        $lowerModule = strtolower(trim($module));

        return $aliases[$lowerModule] ?? $module;
    }

    /**
     * Whether comments can be attached to this module without the caller supplying the entity.
     */
    private function isResolvableCommentModule(string $module): bool
    {
        return in_array($module, ['ticket', 'project', 'article', 'idea'], true) || str_ends_with($module, 'canvasitem');
    }

    /**
     * Add a comment to an entity (API entry point).
     *
     * The module is validated and the entity is ALWAYS loaded (and access-checked) server-side from
     * (module, entityId); a caller-supplied entity is ignored, so an RPC/MCP caller cannot attach a
     * comment to an arbitrary module/id by shipping a fake entity. An unknown module or a
     * missing/inaccessible entity raises a ValidationException / NotFoundException (JSON-RPC
     * -32602 / -32002) instead of returning a silent false (#3704).
     *
     * @param  array  $values  Comment values: text (required), father/parentId, status.
     * @param  string  $module  ticket, project, article, idea, {type}canvasitem (aliases such as "tickets" are accepted).
     * @param  int  $entityId  The id of the entity being commented on.
     * @param  mixed  $entity  Ignored; kept for backwards compatibility of the RPC signature.
     * @return bool True when the comment was stored; false when the comment text is empty or the write fails.
     *
     * @throws BindingResolutionException
     * @throws ValidationException When the module is unknown or the entity id is invalid.
     * @throws NotFoundException When the entity does not exist or is not accessible.
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::CREATE, entityScoped: true)]
    public function addComment($values, $module, $entityId, $entity = null): bool
    {
        $module = $this->normalizeCommentModule($module);

        if (! is_string($module) || ! $this->isResolvableCommentModule($module)) {
            $message = "Unknown comment module '".(is_scalar($module) ? $module : '')."'. Expected one of: ticket, project, article, idea, {type}canvasitem.";

            throw new ValidationException(['module' => [$message]], $message);
        }

        if ((int) $entityId <= 0) {
            $message = 'entityId must be a positive id.';

            throw new ValidationException(['entityId' => [$message]], $message);
        }

        $loadedEntity = $this->loadEntityForComment($module, (int) $entityId);
        if ($loadedEntity === null) {
            throw new NotFoundException("Could not find {$module} #{$entityId}, or you do not have access to it.");
        }

        return $this->storeComment($values, $module, (int) $entityId, $loadedEntity);
    }

    /**
     * Add a comment to an entity the calling controller has already loaded and authorized.
     *
     * Internal only (no @api, so not reachable over JSON-RPC): web controllers use it for modules
     * the API path does not resolve itself, such as client comments and plugin modules. Modules the
     * API path can resolve are still loaded server-side.
     *
     * @internal
     *
     * @param  array  $values  Comment values: text (required), father/parentId, status.
     * @param  string  $module  The comment module.
     * @param  int  $entityId  The id of the entity being commented on.
     * @param  mixed  $entity  The entity the controller loaded.
     * @return bool True when the comment was stored.
     *
     * @throws BindingResolutionException
     */
    public function addCommentToLoadedEntity(array $values, string $module, int $entityId, mixed $entity): bool
    {
        $module = (string) $this->normalizeCommentModule($module);

        if ($this->isResolvableCommentModule($module)) {
            return $this->addComment($values, $module, $entityId);
        }

        if ($entity === null || $entityId <= 0) {
            return false;
        }

        return $this->storeComment($values, $module, $entityId, $entity);
    }

    /**
     * Authorize against the entity's project and write the comment plus its notification.
     *
     * @param  array  $values  Comment values.
     * @param  string  $module  The (normalized) comment module.
     * @param  int  $entityId  The entity id.
     * @param  mixed  $entity  The trusted, loaded entity.
     * @return bool True when the comment was stored.
     *
     * @throws BindingResolutionException
     */
    private function storeComment(array $values, string $module, int $entityId, mixed $entity): bool
    {
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

        if (isset($values['text']) && $values['text'] != '' && isset($entity)) {
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

                // Notify the project the comment was AUTHORIZED against (resolved from the host
                // entity above), not the ambient session project: an RPC call from a browser whose
                // current project is A, commenting on an item in B, must not send B's comment to
                // A's members or webhooks. The session project is only a last-resort fallback.
                $entityProjectId = is_object($entity)
                    ? ($entity->projectId ?? 0)
                    : (is_array($entity) ? ($entity['projectId'] ?? $entity['id'] ?? 0) : 0);
                $notificationProjectId = (int) ($projectId ?? ($entityProjectId ?: session('currentProject')));

                $urlQueryParameter = str_contains($currentUrl, '?') ? '&' : '?';
                $notification->url = [
                    'url' => $currentUrl.$urlQueryParameter.'projectId='.$notificationProjectId,
                    'text' => $linkLabel,
                ];

                $notification->entity = $mapper;
                $notification->module = 'comments';
                $notification->action = 'commented';
                $notification->projectId = $notificationProjectId;
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
