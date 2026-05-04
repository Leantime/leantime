<?php

namespace Leantime\Domain\Oneonone\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Oneonone\Repositories\Oneonone as OneononeRepo;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Oneonone service - business logic for Weekly 1:1 Employee Sheet.
 *
 * Permission model:
 *  - Employees can read & contribute to sessions where they are the employee.
 *  - Managers (role >= manager) can manage sessions where they are the manager.
 *  - Admins/Owners can manage all sessions.
 *
 * @api
 */
class Oneonone
{
    use DispatchesEvents;

    public function __construct(
        private OneononeRepo $repo,
        private UserRepository $userRepo,
    ) {}

    // -- Permissions --------------------------------------------------------

    /** Whether the current session user may view this 1:1 session. */
    public function canViewSession(array $session): bool
    {
        $userId = (int) (session('userdata.id') ?? 0);
        if ($userId === 0) {
            return false;
        }

        if (Auth::userIsAtLeast(Roles::$admin)) {
            return true;
        }

        return ((int) ($session['employeeId'] ?? 0)) === $userId
            || ((int) ($session['managerId'] ?? 0)) === $userId;
    }

    /** Whether the current session user may edit this 1:1 session. */
    public function canEditSession(array $session): bool
    {
        $userId = (int) (session('userdata.id') ?? 0);
        if ($userId === 0) {
            return false;
        }

        if (Auth::userIsAtLeast(Roles::$admin)) {
            return true;
        }

        // Only the manager who owns the session, or the employee themselves,
        // may edit content. Other team members cannot.
        return ((int) ($session['managerId'] ?? 0)) === $userId
            || ((int) ($session['employeeId'] ?? 0)) === $userId;
    }

    // -- Sessions -----------------------------------------------------------

    /**
     * Get all 1:1 sessions for the current user (as an employee).
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMySessions(): array
    {
        $userId = (int) (session('userdata.id') ?? 0);

        return $this->repo->getSessionsForEmployee($userId);
    }

    /**
     * Get all 1:1 sessions managed by the current user.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTeamSessions(): array
    {
        $userId = (int) (session('userdata.id') ?? 0);
        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return [];
        }

        return $this->repo->getSessionsForManager($userId);
    }

    /**
     * Build a team dashboard summary for the current manager.
     *
     * Returns one row per direct report (employee with at least one session),
     * with counts and the latest session details.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getTeamDashboard(): array
    {
        $userId = (int) (session('userdata.id') ?? 0);
        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return [];
        }

        $sessions = $this->repo->getSessionsForManager($userId);
        $byEmployee = [];

        foreach ($sessions as $session) {
            $eid = (int) ($session['employeeId'] ?? 0);
            if ($eid === 0) {
                continue;
            }

            if (! isset($byEmployee[$eid])) {
                $byEmployee[$eid] = [
                    'employeeId' => $eid,
                    'firstname' => $session['employeeFirstname'] ?? '',
                    'lastname' => $session['employeeLastname'] ?? '',
                    'profileId' => $session['employeeProfileId'] ?? null,
                    'jobTitle' => $session['employeeJobTitle'] ?? '',
                    'sessionCount' => 0,
                    'completedCount' => 0,
                    'lastSession' => null,
                    'nextSession' => null,
                ];
            }

            $byEmployee[$eid]['sessionCount']++;
            if (($session['status'] ?? '') === 'completed') {
                $byEmployee[$eid]['completedCount']++;
            }

            // sessions are pre-sorted desc; first one we see for an employee is the most recent
            if ($byEmployee[$eid]['lastSession'] === null) {
                $byEmployee[$eid]['lastSession'] = $session;
            }

            // track upcoming scheduled session (in the future)
            $isFuture = false;
            if (! empty($session['meetingDate'])) {
                try {
                    $isFuture = dtHelper()->parseDbDateTime((string) $session['meetingDate'])->isFuture();
                } catch (\Exception $e) {
                    $isFuture = false;
                }
            }
            if ($isFuture && ($session['status'] ?? '') === 'scheduled') {
                $byEmployee[$eid]['nextSession'] = $session;
            }
        }

        return array_values($byEmployee);
    }

    /** Get a single session by id (returns null if not found or no access). */
    public function getSession(int $id): ?array
    {
        $session = $this->repo->getSession($id);
        if ($session === null) {
            return null;
        }
        if (! $this->canViewSession($session)) {
            return null;
        }

        return $session;
    }

    /**
     * Schedule a new 1:1 session.
     *
     * Required values: employeeId, meetingDate.
     * The current user becomes the manager unless explicitly overridden by an admin.
     *
     * @param  array<string, mixed>  $values
     *
     * @api
     */
    public function scheduleSession(array $values): int|false
    {
        $userId = (int) (session('userdata.id') ?? 0);
        if ($userId === 0) {
            return false;
        }

        if (! Auth::userIsAtLeast(Roles::$manager)) {
            return false;
        }

        $employeeId = (int) ($values['employeeId'] ?? 0);
        if ($employeeId === 0) {
            return false;
        }

        $employee = $this->userRepo->getUser($employeeId);
        if ($employee === false) {
            return false;
        }

        $meetingDate = $this->normalizeUserDateTimeForDb($values['meetingDate'] ?? null);
        if ($meetingDate === null) {
            return false;
        }

        $managerId = $userId;
        if (Auth::userIsAtLeast(Roles::$admin) && ! empty($values['managerId'])) {
            $managerId = (int) $values['managerId'];
            // Validate the admin-supplied manager exists.
            if ($this->userRepo->getUser($managerId) === false) {
                return false;
            }
        }

        // Reject self-1:1s at the service boundary (UI also filters,
        // but API/RPC paths must enforce too).
        if ($employeeId === $managerId) {
            return false;
        }

        $payload = [
            'employeeId' => $employeeId,
            'managerId' => $managerId,
            'meetingDate' => $meetingDate,
            'title' => $this->sanitizeString($values['title'] ?? null, 255),
            'mood' => $this->sanitizeMood($values['mood'] ?? null),
            'status' => 'scheduled',
            'notes' => null,
            'summary' => null,
        ];

        try {
            $id = $this->repo->addSession($payload);
        } catch (\Throwable $e) {
            Log::error($e);

            return false;
        }

        self::dispatch_event('oneonone_scheduled', ['sessionId' => $id, 'session' => $payload]);

        return $id;
    }

    /**
     * Update an existing session.
     *
     * @param  array<string, mixed>  $values
     *
     * @api
     */
    public function updateSession(int $id, array $values): bool
    {
        $session = $this->repo->getSession($id);
        if ($session === null || ! $this->canEditSession($session)) {
            return false;
        }

        $update = [];

        if (array_key_exists('meetingDate', $values) && trim((string) $values['meetingDate']) !== '') {
            $meetingDate = $this->normalizeUserDateTimeForDb($values['meetingDate']);
            if ($meetingDate !== null) {
                $update['meetingDate'] = $meetingDate;
            }
        }

        if (array_key_exists('title', $values)) {
            $update['title'] = $this->sanitizeString($values['title'], 255);
        }

        if (array_key_exists('mood', $values)) {
            $update['mood'] = $this->sanitizeMood($values['mood']);
        }

        if (array_key_exists('status', $values)) {
            $status = (string) $values['status'];
            if (isset($this->repo->sessionStatuses[$status])) {
                $update['status'] = $status;
            }
        }

        if (array_key_exists('notes', $values)) {
            // Only managers can edit private notes.
            if ((int) ($session['managerId'] ?? 0) === (int) (session('userdata.id') ?? 0)
                || Auth::userIsAtLeast(Roles::$admin)
            ) {
                $update['notes'] = (string) $values['notes'];
            }
        }

        if (array_key_exists('summary', $values)) {
            $update['summary'] = (string) $values['summary'];
        }

        if ($update === []) {
            return false;
        }

        try {
            $ok = $this->repo->updateSession($id, $update);
        } catch (\Throwable $e) {
            Log::error($e);

            return false;
        }

        if ($ok) {
            self::dispatch_event('oneonone_updated', ['sessionId' => $id]);
        }

        return $ok;
    }

    /**
     * Delete a session and all its items.
     *
     * @api
     */
    public function deleteSession(int $id): bool
    {
        $session = $this->repo->getSession($id);
        if ($session === null) {
            return false;
        }

        // Only the owning manager or an admin may delete a session.
        $userId = (int) (session('userdata.id') ?? 0);
        if (! Auth::userIsAtLeast(Roles::$admin)
            && (int) ($session['managerId'] ?? 0) !== $userId
        ) {
            return false;
        }

        $ok = false;
        try {
            $ok = $this->repo->deleteSession($id);
        } catch (\Throwable $e) {
            Log::error($e);

            return false;
        }

        if ($ok) {
            self::dispatch_event('oneonone_deleted', ['sessionId' => $id]);
        }

        return $ok;
    }

    // -- Items --------------------------------------------------------------

    /**
     * Get items for a session, grouped by type.
     *
     * @return array<string, array<int, array<string, mixed>>>
     */
    public function getItemsGrouped(int $sessionId): array
    {
        $session = $this->repo->getSession($sessionId);
        if ($session === null || ! $this->canViewSession($session)) {
            return [];
        }

        $items = $this->repo->getItemsForSession($sessionId);
        $grouped = [];
        foreach (array_keys($this->repo->itemTypes) as $type) {
            $grouped[$type] = [];
        }

        foreach ($items as $item) {
            $type = (string) ($item['type'] ?? 'talking_point');
            if (! isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $item;
        }

        return $grouped;
    }

    /**
     * Add an item to a session.
     *
     * @param  array<string, mixed>  $values
     */
    public function addItem(int $sessionId, array $values): int|false
    {
        $session = $this->repo->getSession($sessionId);
        if ($session === null || ! $this->canEditSession($session)) {
            return false;
        }

        $userId = (int) (session('userdata.id') ?? 0);
        $type = (string) ($values['type'] ?? 'talking_point');
        if (! isset($this->repo->itemTypes[$type])) {
            $type = 'talking_point';
        }

        $content = trim((string) ($values['content'] ?? ''));
        if ($content === '') {
            return false;
        }
        // Hard cap content length to prevent abuse / matches schema TEXT bounds.
        $content = function_exists('mb_substr') ? mb_substr($content, 0, 5000) : substr($content, 0, 5000);

        $payload = [
            'sessionId' => $sessionId,
            'type' => $type,
            'author' => $userId,
            'content' => $content,
            'status' => 'open',
            'sortIndex' => (int) ($values['sortIndex'] ?? 0),
        ];

        if (! empty($values['assignedTo'])) {
            $assignedTo = (int) $values['assignedTo'];
            // Validate user exists to avoid orphaned references.
            if ($assignedTo > 0 && $this->userRepo->getUser($assignedTo) !== false) {
                $payload['assignedTo'] = $assignedTo;
            }
        }

        if (! empty($values['dueDate'])) {
            $due = $this->normalizeUserDateTimeForDb($values['dueDate']);
            if ($due !== null) {
                $payload['dueDate'] = $due;
            }
        }

        if (! empty($values['linkedTicketId'])) {
            $payload['linkedTicketId'] = (int) $values['linkedTicketId'];
        }

        try {
            $id = $this->repo->addItem($payload);
        } catch (\Throwable $e) {
            Log::error($e);

            return false;
        }

        self::dispatch_event('oneonone_item_added', ['sessionId' => $sessionId, 'itemId' => $id]);

        return $id;
    }

    /**
     * Update an item.
     *
     * @param  array<string, mixed>  $values
     */
    public function updateItem(int $id, array $values): bool
    {
        $item = $this->repo->getItem($id);
        if ($item === null) {
            return false;
        }

        $session = $this->repo->getSession((int) $item['sessionId']);
        if ($session === null || ! $this->canEditSession($session)) {
            return false;
        }

        $update = [];

        if (array_key_exists('content', $values)) {
            $content = trim((string) $values['content']);
            if ($content !== '') {
                $update['content'] = function_exists('mb_substr') ? mb_substr($content, 0, 5000) : substr($content, 0, 5000);
            }
        }

        if (array_key_exists('status', $values)) {
            $status = (string) $values['status'];
            if (in_array($status, ['open', 'done', 'discussed'], true)) {
                $update['status'] = $status;
            }
        }

        if (array_key_exists('type', $values)) {
            $type = (string) $values['type'];
            if (isset($this->repo->itemTypes[$type])) {
                $update['type'] = $type;
            }
        }

        if (array_key_exists('assignedTo', $values)) {
            if ($values['assignedTo'] === '' || $values['assignedTo'] === null) {
                $update['assignedTo'] = null;
            } else {
                $assignedTo = (int) $values['assignedTo'];
                // Validate user exists; ignore the field if not.
                if ($assignedTo > 0 && $this->userRepo->getUser($assignedTo) !== false) {
                    $update['assignedTo'] = $assignedTo;
                }
            }
        }

        if (array_key_exists('dueDate', $values)) {
            if ($values['dueDate'] === '' || $values['dueDate'] === null) {
                $update['dueDate'] = null;
            } else {
                $due = $this->normalizeUserDateTimeForDb($values['dueDate']);
                if ($due !== null) {
                    $update['dueDate'] = $due;
                }
            }
        }

        if (array_key_exists('sortIndex', $values)) {
            $update['sortIndex'] = (int) $values['sortIndex'];
        }

        if ($update === []) {
            return false;
        }

        try {
            return $this->repo->updateItem($id, $update);
        } catch (\Throwable $e) {
            Log::error($e);

            return false;
        }
    }

    /** Toggle an item's done/open status (for action items and talking points). */
    public function toggleItem(int $id): bool
    {
        $item = $this->repo->getItem($id);
        if ($item === null) {
            return false;
        }

        $session = $this->repo->getSession((int) $item['sessionId']);
        if ($session === null || ! $this->canEditSession($session)) {
            return false;
        }

        $current = (string) ($item['status'] ?? 'open');
        $next = $current === 'open' ? 'done' : 'open';

        try {
            return $this->repo->updateItem($id, ['status' => $next]);
        } catch (\Throwable $e) {
            Log::error($e);

            return false;
        }
    }

    /** Delete an item. */
    public function deleteItem(int $id): bool
    {
        $item = $this->repo->getItem($id);
        if ($item === null) {
            return false;
        }

        $session = $this->repo->getSession((int) $item['sessionId']);
        if ($session === null || ! $this->canEditSession($session)) {
            return false;
        }

        try {
            return $this->repo->deleteItem($id);
        } catch (\Throwable $e) {
            Log::error($e);

            return false;
        }
    }

    /**
     * Get open action items assigned to the current user.
     *
     * @api
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMyOpenActionItems(): array
    {
        $userId = (int) (session('userdata.id') ?? 0);
        if ($userId === 0) {
            return [];
        }

        $items = $this->repo->getOpenActionItemsForUser($userId);
        if ($items === []) {
            return [];
        }

        // Enforce session visibility: an assignee may have lost access to a
        // session (e.g. role change). Only return items whose parent session
        // is currently visible to the user. Cache permission per session id
        // to avoid N+1 calls when many items belong to the same session.
        $allowed = [];
        $filtered = [];
        foreach ($items as $item) {
            $sessionId = (int) ($item['sessionId'] ?? 0);
            if ($sessionId === 0) {
                continue;
            }

            if (! array_key_exists($sessionId, $allowed)) {
                $session = [
                    'employeeId' => $item['employeeId'] ?? null,
                    'managerId' => $item['managerId'] ?? null,
                ];
                $allowed[$sessionId] = $this->canViewSession($session);
            }

            if ($allowed[$sessionId]) {
                $filtered[] = $item;
            }
        }

        return $filtered;
    }

    // -- Helpers ------------------------------------------------------------

    private function sanitizeString(mixed $value, int $maxLen): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }
        if (function_exists('mb_substr')) {
            return mb_substr($value, 0, $maxLen);
        }

        return substr($value, 0, $maxLen);
    }

    private function sanitizeMood(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = (string) $value;
        if ($value === '') {
            return null;
        }

        return isset($this->repo->moodValues[$value]) ? $value : null;
    }

    /**
     * Convert a user-supplied datetime string into the `Y-m-d H:i:s` UTC format
     * used in the database. Accepts the ISO-like value emitted by
     * `<input type="datetime-local">` (`Y-m-d\TH:i`) as well as common user-format
     * date/time strings. Returns null when the input cannot be parsed safely.
     */
    private function normalizeUserDateTimeForDb(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $userTz = session('usersettings.timezone') ?? 'UTC';

        // Fast path: HTML5 datetime-local format, e.g. "2024-01-15T14:30".
        try {
            $iso = CarbonImmutable::createFromFormat('Y-m-d\TH:i', $value, $userTz);
            if ($iso !== false) {
                return $iso->setTimezone('UTC')->format('Y-m-d H:i:s');
            }
        } catch (\Throwable $e) {
            // fall through
        }

        // Try the user's locale date/time format via dtHelper.
        try {
            return dtHelper()->parseUserDateTime($value)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            // fall through to a final permissive parse
        }

        try {
            return CarbonImmutable::parse($value, $userTz)
                ->setTimezone('UTC')
                ->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            Log::error($e);

            return null;
        }
    }
}
