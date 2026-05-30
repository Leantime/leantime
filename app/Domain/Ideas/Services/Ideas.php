<?php

namespace Leantime\Domain\Ideas\Services;

use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mailer as MailerCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Comments\Repositories\Comments as CommentRepository;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeasRepository;
use Leantime\Domain\Notifications\Models\Notification as NotificationModel;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Queue\Repositories\Queue as QueueRepository;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;

/**
 * Ideas service - business logic for idea boards and idea items.
 */
class Ideas
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
     * Authorization helper: is the current user allowed to modify a canvas item?
     *
     * Resolves the item -> its canvas -> the canvas's project, then checks the
     * session user's project access. Used to gate the JSON-RPC idea mutators
     * (the underlying repository operates by item id with no project scoping).
     *
     * @param  int  $itemId  The canvas item id
     * @return bool True when the session user may modify the item
     */
    private function userCanAccessCanvasItem(int $itemId): bool
    {
        $item = (array) $this->ideasRepository->getSingleCanvasItem($itemId);
        if (empty($item['canvasId'])) {
            return false;
        }

        // getSingleCanvas() returns a list of row arrays (not a flat row),
        // matching the existing getBoardTitle() consumer.
        $canvas = $this->ideasRepository->getSingleCanvas((int) $item['canvasId']);
        if (empty($canvas[0]['projectId'])) {
            return false;
        }

        return $this->projectService->isUserAssignedToProject((int) session('userdata.id'), (int) $canvas[0]['projectId']);
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
    public function reorderIdeas(array $payload): bool
    {
        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return false;
        }

        foreach ($payload as $idea) {
            if (! isset($idea['id']) || ! $this->userCanAccessCanvasItem((int) $idea['id'])) {
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
    public function bulkUpdateStatus(array $payload): bool
    {
        if (! Auth::userIsAtLeast(Roles::$editor)) {
            return false;
        }

        foreach ($payload as $itemList) {
            foreach (explode('&', (string) $itemList) as $itemString) {
                // jQuery sortable serializes as "item[]=ID"; strip the prefix.
                $id = (int) substr($itemString, 7);
                if ($id > 0 && ! $this->userCanAccessCanvasItem($id)) {
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
    public function patchIdeaItem(int $id, array $params): bool
    {
        if (! Auth::userIsAtLeast(Roles::$editor) || ! $this->userCanAccessCanvasItem($id)) {
            return false;
        }

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
    public function getBoard(int $id): array
    {
        $singleCanvas = $this->ideasRepository->getSingleCanvas($id);

        return $singleCanvas === false ? [] : $singleCanvas;
    }

    /**
     * Returns the title of a single idea board, or an empty string if not found.
     *
     * @param  int  $id  Board id.
     * @return string Board title.
     *
     * @api
     */
    public function getBoardTitle(int $id): string
    {
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
    public function getBoardItems(int $boardId): array
    {
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
    public function createBoard(string $title, int $projectId, int $authorId): int
    {
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
    public function createBoardFromDialog(string $title, int $projectId, int $authorId): int
    {
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
    public function updateBoard(int $id, string $title): mixed
    {
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
    public function ensureBoardExists(int $projectId, int $authorId, array $allBoards): int
    {
        if (count($allBoards) > 0) {
            return 0;
        }

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

        $canvasItem = $this->ideasRepository->getSingleCanvasItem($id);

        if (is_array($canvasItem) && isset($canvasItem['box']) && $canvasItem['box'] == '0') {
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
    public function getRawIdeaItem(?int $id): mixed
    {
        return $this->ideasRepository->getSingleCanvasItem((int) $id);
    }

    /**
     * Returns the canvas type map (status keys to label keys).
     *
     * @return array<string, string> Canvas types.
     *
     * @api
     */
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
    public function createIdeaItem(array $input, int $projectId, int $authorId): int
    {
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
    public function updateIdeaItem(array $input, int $projectId, int $authorId): int
    {
        $itemId = (int) $input['itemId'];

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
    public function removeMilestone(int $ideaItemId): void
    {
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
    public function addIdeaComment(string $text, int $ideaItemId, int|string $parentCommentId, int $projectId, int $authorId): false|string
    {
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
    public function removeIdeaComment(int $commentId): void
    {
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
    public function getIdeaComments(string $module, int $entityId): array|false
    {
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
    public function countIdeaComments(string $module, int $entityId): mixed
    {
        return $this->commentsRepository->countComments($module, $entityId);
    }
}
