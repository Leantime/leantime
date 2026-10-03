<?php

namespace Leantime\Domain\Reactions\Services;

use Leantime\Core\Domains\BaseService;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;

/**
 * Reactions service.
 *
 * Reactions are keyed by (module, moduleId) with no project column, so every @api entry point
 * resolves the reacted-on entity's REAL project server-side (project, ticket, comment, canvas
 * item) and requires the caller to be able to view it. Unknown modules fail closed.
 *
 * @api
 */
class Reactions extends BaseService
{
    /**
     * @var \Leantime\Domain\Reactions\Repositories\Reactions reactions repository
     *
     * @api
     */
    private \Leantime\Domain\Reactions\Repositories\Reactions $reactionsRepo;

    private CommentRepository $commentRepo;

    public function __construct(
        \Leantime\Domain\Reactions\Repositories\Reactions $reactionsRepo,
        CommentRepository $commentRepo
    ) {
        $this->reactionsRepo = $reactionsRepo;
        $this->commentRepo = $commentRepo;
    }

    /**
     * Resolve the project that owns the entity a reaction targets.
     *
     * Supports projects, tickets, comments (via the comment's own host entity) and the
     * canvas-family modules the comment repository knows (articles, ideas, *canvasitem).
     * Returns null for an unknown module or a missing entity — callers treat that as a denial.
     *
     * @param  string  $module  The reaction module.
     * @param  int  $moduleId  The entity id.
     * @return int|null The owning project id, or null when it cannot be resolved.
     */
    private function resolveProjectId(string $module, int $moduleId): ?int
    {
        if ($moduleId <= 0) {
            return null;
        }

        if ($module === 'comment') {
            $comment = $this->commentRepo->getComment($moduleId);
            if (! $comment) {
                return null;
            }

            $module = (string) ($comment['module'] ?? '');
            $moduleId = (int) ($comment['moduleId'] ?? 0);
        }

        if ($module === 'tickets') {
            $module = 'ticket';
        }

        return $this->commentRepo->resolveModuleProjectId($module, $moduleId);
    }

    /**
     * Whether the current user may see (and react to) the entity identified by module/id.
     * Fails closed when the owning project cannot be resolved.
     *
     * @param  string  $module  The reaction module.
     * @param  int  $moduleId  The entity id.
     */
    private function canAccessEntity(string $module, int $moduleId): bool
    {
        $projectId = $this->resolveProjectId($module, $moduleId);

        if ($projectId === null || $projectId <= 0) {
            return false;
        }

        return $this->can(ProjectsPermissions::VIEW, $projectId);
    }

    /**
     * Adds a reaction on behalf of the CURRENT (session) user.
     *
     * JSON-RPC entry point: derives the user from the session rather than
     * accepting a userId, so a caller cannot react as another user.
     *
     * @param  string  $module  The entity module (e.g. 'tickets')
     * @param  int  $moduleId  The entity id
     * @param  string  $reaction  The reaction key
     * @return bool True if the reaction was added
     *
     * @api
     */
    public function react(string $module, int $moduleId, string $reaction): bool
    {
        if (! $this->canAccessEntity($module, $moduleId)) {
            return false;
        }

        return $this->addReaction((int) session('userdata.id'), $module, $moduleId, $reaction);
    }

    /**
     * Removes a reaction on behalf of the CURRENT (session) user.
     *
     * JSON-RPC entry point: derives the user from the session rather than
     * accepting a userId, so a caller cannot remove another user's reaction.
     *
     * @param  string  $module  The entity module (e.g. 'tickets')
     * @param  int  $moduleId  The entity id
     * @param  string  $reaction  The reaction key
     * @return bool True if the reaction was removed
     *
     * @api
     */
    public function unreact(string $module, int $moduleId, string $reaction): bool
    {
        if (! $this->canAccessEntity($module, $moduleId)) {
            return false;
        }

        return $this->removeReaction((int) session('userdata.id'), $module, $moduleId, $reaction);
    }

    /**
     * addReaction - adds a reaction to an entity, checks if a user has already reacted the same way
     *
     * Not exposed via JSON-RPC: it accepts an arbitrary $userId. Use the
     * session-scoped react() wrapper for JSON-RPC callers.
     */
    public function addReaction(int $userId, string $module, int $moduleId, string $reaction): bool
    {
        if ($module == '' || $moduleId == '' || $userId == '' || $reaction == '') {
            return false;
        }

        // Check if user already reacted in that category
        $userReactions = $this->getUserReactions($userId, $module, $moduleId);

        $currentReactionType = $this->getReactionType($reaction);

        foreach ($userReactions as $previousReaction) {
            if ($this->getReactionType($previousReaction['reaction']) == $currentReactionType) {
                return false;
            }
        }

        return $this->reactionsRepo->addReaction($userId, $module, $moduleId, $reaction);
    }

    /**
     * getReactionType - returns the category/type of a given reaction
     *
     *
     *
     * @api
     */
    public function getReactionType(string $reaction): string|false
    {

        $types = \Leantime\Domain\Reactions\Models\Reactions::getReactions();

        foreach ($types as $reactionType => $reactionValues) {
            if (isset($reactionValues[$reaction])) {
                return $reactionType;
            }
        }

        return false;
    }

    /**
     * getGroupedEntityReactions - gets all reactions for a given entity grouped and counted by reactions
     *
     *
     * The caller must be able to view the entity's project; otherwise an empty list.
     *
     * @return array|false returns the array on success or false on failure
     *
     * @api
     */
    public function getGroupedEntityReactions(string $module, int $moduleId): array|false
    {
        if (! $this->canAccessEntity($module, $moduleId)) {
            return [];
        }

        return $this->reactionsRepo->getGroupedEntityReactions($module, $moduleId);
    }

    /**
     * getMyReactions - gets user reactions. Can be very broad or very targeted
     *
     *
     *
     * @internal Not exposed over JSON-RPC: $userId is caller-supplied. Service-internal use only.
     */
    public function getUserReactions(int $userId, string $module = '', ?int $moduleId = null, string $reaction = ''): array|false
    {

        return $this->reactionsRepo->getUserReactions($userId, $module, $moduleId, $reaction);
    }

    /**
     * removeReaction - removes a user's reaction from an entity
     *
     * Not exposed via JSON-RPC (accepts an arbitrary $userId). Use the
     * session-scoped unreact() wrapper for JSON-RPC callers.
     */
    public function removeReaction(int $userId, string $module, int $moduleId, string $reaction): bool
    {
        return $this->reactionsRepo->removeUserReaction($userId, $module, $moduleId, $reaction);
    }

    /**
     * getEntityReactionsWithUsers - gets all reactions for an entity with user names
     *
     * The caller must be able to view the entity's project; otherwise an empty list (reactor
     * names are not exposed across projects).
     *
     * @return array returns array grouped by reaction with user info
     *
     * @api
     */
    public function getEntityReactionsWithUsers(string $module, int $moduleId): array
    {
        if (! $this->canAccessEntity($module, $moduleId)) {
            return [];
        }

        return $this->reactionsRepo->getEntityReactionsWithUsers($module, $moduleId);
    }
}
