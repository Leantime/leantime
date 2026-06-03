<?php

namespace Leantime\Domain\Ideas\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Comments\Permissions\CommentsPermissions;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Ideas\Permissions\IdeasPermissions;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * Ideas service - business logic for idea boards and idea items.
 */
class Ideas extends BaseService
{
    private IdeasRepository $ideasRepository;

    private CommentRepository $commentsRepository;

    private ProjectService $projectService;

    private TicketService $ticketService;

    private LanguageCore $language;

    /**
     * Constructor.
     */
    public function __construct(
        IdeasRepository $ideasRepository,
        CommentRepository $commentsRepository,
        ProjectService $projectService,
        TicketService $ticketService,
        LanguageCore $language
    ) {
        $this->ideasRepository = $ideasRepository;
        $this->commentsRepository = $commentsRepository;
        $this->projectService = $projectService;
        $this->ticketService = $ticketService;
        $this->language = $language;
    }

    /**
     * Resolve an idea board's owning project. Boards are zp_canvas rows (type 'idea').
     *
     * @param  int  $boardId  The board (canvas) id
     * @return int|null The board's project id, or null when the board does not exist
     */
    private function boardProjectId(int $boardId): ?int
    {
        // getSingleCanvas() returns a list of row arrays (not a flat row).
        $rows = $this->ideasRepository->getSingleCanvas($boardId);

        return isset($rows[0]['projectId']) ? (int) $rows[0]['projectId'] : null;
    }

    /**
     * Resolve an idea item's owning project (item -> its board/canvas -> project).
     *
     * The repository operates by item id with NO project scoping, and zp_canvas_items is shared
     * across all canvas types, so callers MUST fail closed on a null result before any read/write.
     *
     * @param  int  $itemId  The canvas item id
     * @return int|null The item's project id, or null when it does not resolve to an idea board
     */
    private function canvasItemProjectId(int $itemId): ?int
    {
        $item = $this->ideasRepository->getSingleCanvasItem($itemId);
        if (! is_array($item) || empty($item['canvasId'])) {
            return null;
        }

        return $this->boardProjectId((int) $item['canvasId']);
    }

    /**
     * Authorized JSON-RPC entry point: persist a new idea sort order.
     *
     * Requires editor+ and per-item project access (JSON-RPC has no controller
     * gate, and the repository sorts by item id with no project scoping).
     *
     * @param  array  $payload  List of { id, sortIndex } entries
     * @return bool True on success, false if unauthorized or the update failed
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::EDIT, entityScoped: true)]
    public function reorderIdeas(array $payload): bool
    {
        // Per-item project fence: reject the whole batch unless the user may edit EVERY item's
        // project (non-throwing can(), since a batch should fail gracefully). null project = the id
        // is not an idea item (shared zp_canvas_items) -> reject.
        foreach ($payload as $idea) {
            if (! isset($idea['id'])) {
                return false;
            }
            $projectId = $this->canvasItemProjectId((int) $idea['id']);
            if ($projectId === null || ! $this->can(IdeasPermissions::EDIT, $projectId)) {
                return false;
            }
        }

        return $this->ideasRepository->updateIdeaSorting($payload);
    }

    /**
     * Authorized JSON-RPC entry point: bulk status/sort update from the idea kanban.
     *
     * Requires editor+ and project access for every item in the (jQuery-sortable
     * serialized) payload.
     *
     * @param  array  $payload  Map of statusKey => "item[]=ID&item[]=ID2..."
     * @return bool True on success, false if unauthorized or the update failed
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::EDIT, entityScoped: true)]
    public function bulkUpdateStatus(array $payload): bool
    {
        // Per-item project fence over the jQuery-sortable serialized payload.
        foreach ($payload as $itemList) {
            foreach (explode('&', (string) $itemList) as $itemString) {
                // jQuery sortable serializes as "item[]=ID"; strip the prefix.
                $id = (int) substr($itemString, 7);
                if ($id <= 0) {
                    continue;
                }
                $projectId = $this->canvasItemProjectId($id);
                if ($projectId === null || ! $this->can(IdeasPermissions::EDIT, $projectId)) {
                    return false;
                }
            }
        }

        return $this->ideasRepository->bulkUpdateIdeaStatus($payload);
    }

    /**
     * Authorized JSON-RPC entry point: patch a single idea/canvas item.
     *
     * Requires editor+ and access to the item's project.
     *
     * @param  int  $id  The canvas item id
     * @param  array  $params  Fields to update
     * @return bool True on success, false if unauthorized or the update failed
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::EDIT, entityScoped: true)]
    public function patchIdeaItem(int $id, array $params): bool
    {
        // Fail closed: resolve the item's REAL project (shared zp_canvas_items) and require edit
        // there before patching — null means the id is not an idea item, so refuse.
        $projectId = $this->canvasItemProjectId($id);
        if ($projectId === null) {
            return false;
        }
        $this->authorize(IdeasPermissions::EDIT, $projectId);

        // Strip relocation/identity fields: patchCanvasItem updates any column it receives, so a
        // JSON-RPC caller could otherwise patch canvasId to move the item to another board/project
        // (bypassing the project scoping just authorized) or rewrite its id/author.
        unset($params['canvasId'], $params['id'], $params['author']);

        return $this->ideasRepository->patchCanvasItem($id, $params);
    }

    /**
     * Polls for new ideas in a project / board, normalizing dates for the API.
     *
     * @param  int|null  $projectId  Project to filter by, or null for all accessible projects.
     * @param  int|null  $board  Board to filter by, or null for all boards.
     * @return array<int, array<string, mixed>> List of ideas with ISO8601 dates.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, projectIdParam: 'projectId')]
    public function pollForNewIdeas(?int $projectId = null, ?int $board = null): array
    {
        $ideas = $this->ideasRepository->getAllIdeas($projectId, $board);

        foreach ($ideas as $key => $idea) {
            $ideas[$key] = $this->prepareDatesForApiResponse($idea);
        }

        return $ideas;
    }

    /**
     * Polls for updated ideas, appending the modified timestamp to the id for change detection.
     *
     * @param  int|null  $projectId  Project to filter by, or null for all accessible projects.
     * @param  int|null  $board  Board to filter by, or null for all boards.
     * @return array<int, array<string, mixed>> List of ideas with ISO8601 dates and composite ids.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, projectIdParam: 'projectId')]
    public function pollForUpdatedIdeas(?int $projectId = null, ?int $board = null): array
    {
        $ideas = $this->ideasRepository->getAllIdeas($projectId, $board);

        foreach ($ideas as $key => $idea) {
            $ideas[$key] = $this->prepareDatesForApiResponse($idea);
            $ideas[$key]['id'] = $idea['id'].'-'.$idea['modified'];
        }

        return $ideas;
    }

    /**
     * Normalizes a single idea's created/modified dates to ISO8601 Zulu strings.
     *
     * @param  array<string, mixed>  $idea  Raw idea row.
     * @return array<string, mixed> Idea with normalized dates.
     */
    private function prepareDatesForApiResponse(array $idea): array
    {
        if (dtHelper()->isValidDateString($idea['created'])) {
            $idea['created'] = dtHelper()->parseDbDateTime($idea['created'])->toIso8601ZuluString();
        } else {
            $idea['created'] = null;
        }

        if (dtHelper()->isValidDateString($idea['modified'])) {
            $idea['modified'] = dtHelper()->parseDbDateTime($idea['modified'])->toIso8601ZuluString();
        } else {
            $idea['modified'] = null;
        }

        return $idea;
    }

    /**
     * Returns all idea boards for a project.
     *
     * @param  int  $projectId  Project id.
     * @return array<int, array<string, mixed>> List of boards.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, projectIdParam: 'projectId')]
    public function getAllBoards(int $projectId): array
    {
        $allCanvas = $this->ideasRepository->getAllCanvas($projectId);

        return $allCanvas === false ? [] : $allCanvas;
    }

    /**
     * Returns a single idea board by id.
     *
     * @param  int  $id  Board id.
     * @return array<int, array<string, mixed>> Single board rows (matching repository shape).
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, entityScoped: true)]
    public function getBoard(int $id): array
    {
        $singleCanvas = $this->ideasRepository->getSingleCanvas($id);
        if (empty($singleCanvas)) {
            return [];
        }

        // IDOR fence: authorize VIEW against the board's real project (single-entity-by-id read).
        $this->authorize(IdeasPermissions::VIEW, isset($singleCanvas[0]['projectId']) ? (int) $singleCanvas[0]['projectId'] : null);

        return $singleCanvas;
    }

    /**
     * Returns the title of a single idea board, or an empty string if not found.
     *
     * @param  int  $id  Board id.
     * @return string Board title.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, entityScoped: true)]
    public function getBoardTitle(int $id): string
    {
        // Delegates to getBoard(), which performs the in-body VIEW authorize against the board's project.
        $singleCanvas = $this->getBoard($id);

        return $singleCanvas[0]['title'] ?? '';
    }

    /**
     * Returns the canvas items for a board.
     *
     * @param  int  $boardId  Board id.
     * @return array<int, array<string, mixed>> Canvas items.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, entityScoped: true)]
    public function getBoardItems(int $boardId): array
    {
        // IDOR fence: authorize VIEW against the board's real project before listing its items.
        $projectId = $this->boardProjectId($boardId);
        if ($projectId === null) {
            return [];
        }
        $this->authorize(IdeasPermissions::VIEW, $projectId);

        $items = $this->ideasRepository->getCanvasItemsById($boardId);

        return $items === false ? [] : $items;
    }

    /**
     * Returns the configured idea status labels.
     *
     * @return mixed Label structure (array keyed by status).
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW)]
    public function getBoardLabels(): mixed
    {
        return $this->ideasRepository->getCanvasLabels();
    }

    /**
     * Creates a new idea board, queuing a creation notification to project users.
     *
     * @param  string  $title  Board title.
     * @param  int  $projectId  Project the board belongs to.
     * @param  int  $authorId  Author user id.
     * @return int The new board id.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::CREATE, entityScoped: true)]
    public function createBoard(string $title, int $projectId, int $authorId): int
    {
        // A board belongs directly to $projectId; authorize CREATE there before writing.
        $this->authorize(IdeasPermissions::CREATE, $projectId);

        $values = [
            'title' => $title,
            'author' => $authorId,
            'projectId' => $projectId,
        ];

        $boardId = (int) $this->ideasRepository->addCanvas($values);

        $this->notifyBoardCreated($title, $projectId);

        return $boardId;
    }

    /**
     * Creates a new idea board from the board dialog, queuing the dialog-specific
     * creation notification to project users.
     *
     * This intentionally uses a different notification subject/message than
     * {@see self::createBoard()} to preserve the historical board-dialog behavior.
     *
     * @param  string  $title  Board title.
     * @param  int  $projectId  Project the board belongs to.
     * @param  int  $authorId  Author user id.
     * @return int The new board id.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::CREATE, entityScoped: true)]
    public function createBoardFromDialog(string $title, int $projectId, int $authorId): int
    {
        // A board belongs directly to $projectId; authorize CREATE there before writing.
        $this->authorize(IdeasPermissions::CREATE, $projectId);

        $values = [
            'title' => $title,
            'author' => $authorId,
            'projectId' => $projectId,
        ];

        $boardId = (int) $this->ideasRepository->addCanvas($values);

        $this->notifyBoardCreatedFromDialog($title, $projectId);

        return $boardId;
    }

    /**
     * Updates an idea board title.
     *
     * @param  int  $id  Board id.
     * @param  string  $title  New title.
     * @return mixed Result of the underlying update.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::EDIT, entityScoped: true)]
    public function updateBoard(int $id, string $title): mixed
    {
        // Fail closed: resolve the board's real project and require edit there before renaming.
        $projectId = $this->boardProjectId($id);
        if ($projectId === null) {
            return false;
        }
        $this->authorize(IdeasPermissions::EDIT, $projectId);

        return $this->ideasRepository->updateCanvas(['title' => $title, 'id' => $id]);
    }

    /**
     * Ensures at least one board exists for a project, creating a default one if none do.
     *
     * Returns the id of the newly created default board if one was created, or 0 if boards
     * already existed (no board created).
     *
     * @param  int  $projectId  Project id.
     * @param  int  $authorId  Author user id for the default board.
     * @param  array<int, array<string, mixed>>  $allBoards  Already-loaded boards for the project.
     * @return int Newly created board id, or 0 if none was created.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, projectIdParam: 'projectId')]
    public function ensureBoardExists(int $projectId, int $authorId, array $allBoards): int
    {
        if (count($allBoards) > 0) {
            return 0;
        }

        // Bootstrap only: the default board is created as a SIDE EFFECT of viewing a board-less
        // project, so it is VIEW-gated and writes straight to the repository — gating it CREATE
        // would 403 a readonly viewer (mirrors Wiki's getAllProjectWikis default-notebook bootstrap).
        $values = [
            'title' => $this->language->__('label.board'),
            'author' => $authorId,
            'projectId' => $projectId,
        ];

        return (int) $this->ideasRepository->addCanvas($values);
    }

    /**
     * Sends (queues) a board-creation notification to project users.
     *
     * @param  string  $title  Board title.
     * @param  int  $projectId  Project id.
     */
    private function notifyBoardCreated(string $title, int $projectId): void
    {
        $mailer = app()->make(MailerCore::class);
        $mailer->setContext('idea_board_created');
        $users = $this->projectService->getUsersToNotify($projectId);

        $mailer->setSubject($this->language->__('email_notifications.idea_board_created_subject'));
        $message = sprintf(
            $this->language->__('email_notifications.idea_board_created_message'),
            session('userdata.name'),
            "<a href='".CURRENT_URL."'>".strip_tags($title).'</a>.<br />'
        );
        $mailer->setHtml($message);

        $queue = app()->make(QueueRepository::class);
        $queue->queueMessageToUsers(
            $users,
            $message,
            $this->language->__('email_notifications.idea_board_created_subject'),
            $projectId
        );
    }

    /**
     * Sends (queues) the board-dialog-specific board-creation notification to project users.
     *
     * @param  string  $title  Board title.
     * @param  int  $projectId  Project id.
     */
    private function notifyBoardCreatedFromDialog(string $title, int $projectId): void
    {
        $mailer = app()->make(MailerCore::class);
        $users = $this->projectService->getUsersToNotify($projectId);

        $mailer->setSubject($this->language->__('notification.board_created'));
        $message = sprintf(
            $this->language->__('email_notifications.canvas_created_message'),
            session('userdata.name'),
            "<a href='".CURRENT_URL."'>".strip_tags($title).'</a>'
        );
        $mailer->setHtml($message);

        $queue = app()->make(QueueRepository::class);
        $queue->queueMessageToUsers(
            $users,
            $message,
            $this->language->__('notification.board_created'),
            $projectId
        );
    }

    /**
     * Loads a single idea item for display, normalizing the box value and
     * returning an empty-item default when no id is supplied.
     *
     * @param  int|null  $id  Idea item id, or null when creating a new item.
     * @param  string  $type  Default box/type for a new item.
     * @return array<string, mixed> Idea item (normalized) or empty-item default.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, entityScoped: true)]
    public function getIdeaItem(?int $id, string $type = 'idea'): array
    {
        if ($id === null) {
            return [
                'id' => '',
                'box' => $type,
                'tags' => '',
                'description' => '',
                'status' => 'idea',
                'assumptions' => '',
                'data' => '',
                'conclusion' => '',
                'milestoneHeadline' => '',
                'milestoneId' => '',
            ];
        }

        // IDOR fence (single-entity-by-id read): fetch the item ONCE, derive its project from the
        // owning board, authorize VIEW, then return the already-fetched row — no duplicate query.
        $canvasItem = $this->ideasRepository->getSingleCanvasItem($id);
        if (! is_array($canvasItem) || empty($canvasItem['canvasId'])) {
            return [];
        }

        $projectId = $this->boardProjectId((int) $canvasItem['canvasId']);
        if ($projectId === null) {
            return [];
        }
        $this->authorize(IdeasPermissions::VIEW, $projectId);

        if (isset($canvasItem['box']) && $canvasItem['box'] == '0') {
            $canvasItem['box'] = 'idea';
        }

        return $canvasItem;
    }

    /**
     * Returns a single raw idea item without the display normalization applied by
     * {@see self::getIdeaItem()}. A null id is coerced to 0 (no match), mirroring
     * the historical repository call.
     *
     * @param  int|null  $id  Idea item id.
     * @return mixed The idea item array, or false when not found.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, entityScoped: true)]
    public function getRawIdeaItem(?int $id): mixed
    {
        // IDOR fence (single-entity-by-id read): fetch the item ONCE, derive its project from the
        // owning board, authorize VIEW, then return the already-fetched row — no duplicate query.
        $canvasItem = $this->ideasRepository->getSingleCanvasItem((int) $id);
        if (! is_array($canvasItem) || empty($canvasItem['canvasId'])) {
            return false;
        }

        $projectId = $this->boardProjectId((int) $canvasItem['canvasId']);
        if ($projectId === null) {
            return false;
        }
        $this->authorize(IdeasPermissions::VIEW, $projectId);

        return $canvasItem;
    }

    /**
     * Returns the canvas type map (status keys to label keys).
     *
     * @return array<string, string> Canvas types.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW)]
    public function getCanvasTypes(): array
    {
        return $this->ideasRepository->canvasTypes;
    }

    /**
     * Creates a new idea item and queues a creation notification to project users.
     *
     * @param  array<string, mixed>  $input  Idea item input (box, description, status, data, canvasId, ...).
     * @param  int  $projectId  Project id for the notification.
     * @param  int  $authorId  Author user id.
     * @return int The new idea item id.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::CREATE, entityScoped: true)]
    public function createIdeaItem(array $input, int $projectId, int $authorId): int
    {
        // Authorize CREATE against the TARGET board's real project (resolved from canvasId; the
        // passed projectId is untrusted). FAIL CLOSED if the canvasId is not an idea board — never
        // fall back to the caller-supplied projectId, or a foreign/non-idea canvasId could be
        // created against the caller's own project.
        $boardProjectId = $this->boardProjectId((int) ($input['canvasId'] ?? 0));
        if ($boardProjectId === null) {
            return 0;
        }
        $this->authorize(IdeasPermissions::CREATE, $boardProjectId);

        // Normalize to the resolved board project so the notification below targets the correct
        // project's users even if a mismatched/forged projectId was supplied.
        $projectId = $boardProjectId;

        $canvasItem = [
            'box' => $input['box'],
            'author' => $authorId,
            'description' => $input['description'],
            'status' => $input['status'],
            'assumptions' => '',
            'data' => $input['data'],
            'conclusion' => '',
            'canvasId' => $input['canvasId'],
        ];

        $id = (int) $this->ideasRepository->addCanvasItem($canvasItem);
        $canvasItem['id'] = $id;

        $subject = $this->language->__('email_notifications.idea_created_subject');
        $actualLink = BASE_URL.'#/ideas/ideaDialog/'.$id;
        $message = sprintf(
            $this->language->__('email_notifications.idea_created_message'),
            session('userdata.name'),
            strip_tags($input['description'])
        );

        $notification = app()->make(NotificationModel::class);
        $notification->url = [
            'url' => $actualLink,
            'text' => $this->language->__('email_notifications.idea_created_subject'),
        ];
        $notification->entity = $canvasItem;
        $notification->module = 'ideas';
        $notification->action = 'created';
        $notification->projectId = $projectId;
        $notification->subject = $subject;
        $notification->authorId = $authorId;
        $notification->message = $message;

        $this->projectService->notifyProjectUsers($notification);

        return $id;
    }

    /**
     * Updates an existing idea item, optionally quick-adding or attaching a milestone,
     * then queues an edit notification to project users.
     *
     * @param  array<string, mixed>  $input  Idea item input including itemId and optional milestone fields.
     * @param  int  $projectId  Project id for the notification.
     * @param  int  $authorId  Author user id.
     * @return int The idea item id that was updated.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::EDIT, entityScoped: true)]
    public function updateIdeaItem(array $input, int $projectId, int $authorId): int
    {
        $itemId = (int) $input['itemId'];

        // Fail closed: resolve the EXISTING item's real project (shared zp_canvas_items) and require
        // edit there before writing — the passed projectId/canvasId are untrusted, so an editor in
        // project A cannot edit or relocate an item in project B.
        $existingProjectId = $this->canvasItemProjectId($itemId);
        if ($existingProjectId === null) {
            return 0;
        }
        $this->authorize(IdeasPermissions::EDIT, $existingProjectId);

        // The write persists $input['canvasId'], which can RELOCATE the item to another board. The
        // target must be an idea board the user can also edit, or the relocation is a cross-project
        // move. Fail closed if it's not an idea board; require edit on the target if it differs.
        $targetProjectId = $this->boardProjectId((int) ($input['canvasId'] ?? 0));
        if ($targetProjectId === null) {
            return 0;
        }
        if ($targetProjectId !== $existingProjectId) {
            $this->authorize(IdeasPermissions::EDIT, $targetProjectId);
        }

        // Normalize to the resolved board project for the notification below.
        $projectId = $targetProjectId;

        $canvasItem = [
            'box' => $input['box'],
            'author' => $authorId,
            'description' => $input['description'],
            'status' => $input['status'],
            'assumptions' => '',
            'data' => $input['data'],
            'conclusion' => '',
            'tags' => $input['tags'],
            'itemId' => $input['itemId'],
            'canvasId' => $input['canvasId'],
            'milestoneId' => $input['milestoneId'],
            'id' => $input['itemId'],
        ];

        if (isset($input['newMilestone']) && $input['newMilestone'] != '') {
            $milestone = [];
            $milestone['headline'] = $input['newMilestone'];
            $milestone['tags'] = '#ccc';
            $milestone['editFrom'] = dtHelper()->userNow()->formatDateForUser();
            $milestone['editTo'] = dtHelper()->userNow()->addDays(7)->formatDateForUser();
            $milestoneId = $this->ticketService->quickAddMilestone($milestone);
            if ($milestoneId !== false) {
                $canvasItem['milestoneId'] = $milestoneId;
            }
        }

        if (isset($input['existingMilestone']) && $input['existingMilestone'] != '') {
            $canvasItem['milestoneId'] = $input['existingMilestone'];
        }

        $this->ideasRepository->editCanvasItem($canvasItem);

        $subject = $this->language->__('email_notifications.idea_edited_subject');
        $actualLink = BASE_URL.'#/ideas/ideaDialog/'.$itemId;
        $message = sprintf(
            $this->language->__('notification.idea_edited'),
            session('userdata.name'),
            strip_tags($input['description'])
        );

        $notification = app()->make(NotificationModel::class);
        $notification->url = [
            'url' => $actualLink,
            'text' => $this->language->__('email_notifications.idea_edited_cta'),
        ];
        $notification->entity = $canvasItem;
        $notification->module = 'ideas';
        $notification->action = 'updated';
        $notification->projectId = $projectId;
        $notification->subject = $subject;
        $notification->authorId = $authorId;
        $notification->message = $message;

        $this->projectService->notifyProjectUsers($notification);

        return $itemId;
    }

    /**
     * Detaches a milestone from an idea item.
     *
     * @param  int  $ideaItemId  Idea item id.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::EDIT, entityScoped: true)]
    public function removeMilestone(int $ideaItemId): void
    {
        // Fail closed: resolve the item's real project and require edit there before detaching.
        $projectId = $this->canvasItemProjectId($ideaItemId);
        if ($projectId === null) {
            return;
        }
        $this->authorize(IdeasPermissions::EDIT, $projectId);

        $this->ideasRepository->patchCanvasItem($ideaItemId, ['milestoneId' => '']);
    }

    /**
     * Returns all milestones available to attach to an idea item, for the current project.
     *
     * @param  int  $projectId  Project id.
     * @return array<int, mixed>|false Milestones.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, projectIdParam: 'projectId')]
    public function getProjectMilestones(int $projectId): array|false
    {
        return $this->ticketService->getAllMilestones([
            'sprint' => '',
            'type' => 'milestone',
            'currentProject' => $projectId,
        ]);
    }

    /**
     * Adds a comment to an idea item and queues a notification to project users.
     *
     * @param  string  $text  Comment text.
     * @param  int  $ideaItemId  Idea item (module) id.
     * @param  int|string  $parentCommentId  Parent comment id ('father').
     * @param  int  $projectId  Project id for the notification.
     * @param  int  $authorId  Author user id.
     * @return false|string The new comment id, or false on failure.
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::CREATE, entityScoped: true)]
    public function addIdeaComment(string $text, int $ideaItemId, int|string $parentCommentId, int $projectId, int $authorId): false|string
    {
        // Commenting on an idea is a commenter+ capability in the idea's project (resolved from the
        // item; the passed projectId is untrusted). FAIL CLOSED if the id is not an idea item —
        // never fall back to the caller-supplied projectId.
        $itemProjectId = $this->canvasItemProjectId($ideaItemId);
        if ($itemProjectId === null) {
            return false;
        }
        $this->authorize(CommentsPermissions::CREATE, $itemProjectId);

        $values = [
            'text' => $text,
            'date' => date('Y-m-d H:i:s'),
            'userId' => $authorId,
            'moduleId' => $ideaItemId,
            'commentParent' => $parentCommentId,
        ];

        $commentId = $this->commentsRepository->addComment($values, 'idea');
        $values['id'] = $commentId;

        $subject = $this->language->__('email_notifications.new_comment_idea_subject');
        $actualLink = BASE_URL.'#/ideas/ideaDialog/'.$ideaItemId;
        $message = sprintf(
            $this->language->__('email_notifications.new_comment_idea_message'),
            session('userdata.name')
        );

        $notification = app()->make(NotificationModel::class);
        $notification->url = [
            'url' => $actualLink,
            'text' => $this->language->__('email_notifications.new_comment_idea_cta'),
        ];
        $notification->entity = $values;
        $notification->module = 'comments';
        $notification->action = 'commented';
        $notification->projectId = $projectId;
        $notification->subject = $subject;
        $notification->authorId = $authorId;
        $notification->message = $message;

        $this->projectService->notifyProjectUsers($notification);

        return $commentId;
    }

    /**
     * Deletes a comment.
     *
     * @param  int  $commentId  Comment id.
     *
     * @api
     */
    #[RequiresPermission(CommentsPermissions::MODERATE, entityScoped: true)]
    public function removeIdeaComment(int $commentId): void
    {
        // Was an UNGATED delete-by-id (reachable via the ?delComment GET param) — any user could
        // delete any comment by id. Fence: the comment author may delete their own; otherwise
        // require moderate in the idea's project (resolved from the comment's idea item).
        $comment = $this->commentsRepository->getComment($commentId);
        if (! $comment) {
            return;
        }

        if ((int) ($comment['userId'] ?? 0) !== (int) session('userdata.id')) {
            // Non-author: require moderate in the idea's project. FAIL CLOSED if the comment's item
            // does not resolve to an idea board — never downgrade to a role-only moderate check.
            $projectId = $this->canvasItemProjectId((int) ($comment['moduleId'] ?? 0));
            if ($projectId === null) {
                return;
            }
            $this->authorize(CommentsPermissions::MODERATE, $projectId);
        }

        $this->commentsRepository->deleteComment($commentId);
    }

    /**
     * Returns the comments for a module/entity.
     *
     * @param  string  $module  Comment module key.
     * @param  int  $entityId  Entity id.
     * @return array<int, mixed>|false Comments.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, entityScoped: true)]
    public function getIdeaComments(string $module, int $entityId): array|false
    {
        // Fail closed: a null project means $entityId is not an idea item, so refuse rather than
        // authorize against null (role-only pass) and leak another project's comment thread by id.
        $projectId = $this->canvasItemProjectId($entityId);
        if ($projectId === null) {
            return [];
        }
        $this->authorize(IdeasPermissions::VIEW, $projectId);

        return $this->commentsRepository->getComments($module, $entityId);
    }

    /**
     * Returns the number of comments for a module/entity.
     *
     * @param  string  $module  Comment module key.
     * @param  int  $entityId  Entity id.
     * @return mixed Comment count.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::VIEW, entityScoped: true)]
    public function countIdeaComments(string $module, int $entityId): mixed
    {
        // Fail closed: a null project means $entityId is not an idea item.
        $projectId = $this->canvasItemProjectId($entityId);
        if ($projectId === null) {
            return 0;
        }
        $this->authorize(IdeasPermissions::VIEW, $projectId);

        return $this->commentsRepository->countComments($module, $entityId);
    }

    /**
     * Delete an idea board, fencing the operation against the board's project.
     *
     * Replaces the previous controller->repository direct delete (which only had a global-role
     * gate and no project scoping, so an editor in any project could delete another's board).
     *
     * @param  int  $id  Board (canvas) id.
     * @return bool True when deleted, false when the board does not resolve to a project.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::DELETE, entityScoped: true)]
    public function deleteCanvas(int $id): bool
    {
        $projectId = $this->boardProjectId($id);
        if ($projectId === null) {
            return false;
        }
        $this->authorize(IdeasPermissions::DELETE, $projectId);

        $this->ideasRepository->deleteCanvas($id);
        session()->forget('currentIdeaCanvas');

        return true;
    }

    /**
     * Delete an idea item, fencing the operation against the item's project.
     *
     * Replaces the previous controller->repository direct delete (id-only, no project scoping).
     *
     * @param  int  $id  Canvas item id.
     * @return bool True when deleted, false when the item does not resolve to a project.
     *
     * @api
     */
    #[RequiresPermission(IdeasPermissions::DELETE, entityScoped: true)]
    public function deleteCanvasItem(int $id): bool
    {
        $projectId = $this->canvasItemProjectId($id);
        if ($projectId === null) {
            return false;
        }
        $this->authorize(IdeasPermissions::DELETE, $projectId);

        $this->ideasRepository->delCanvasItem($id);

        return true;
    }
}
