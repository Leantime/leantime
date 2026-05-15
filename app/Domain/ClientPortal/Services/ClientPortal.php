<?php

namespace Leantime\Domain\ClientPortal\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\ClientPortal\Models\ClientRequest;
use Leantime\Domain\ClientPortal\Models\ClientRequestResponse;
use Leantime\Domain\ClientPortal\Repositories\ClientPortal as ClientPortalRepo;
use Leantime\Domain\Notifications\Services\Notifications as NotificationsService;

/**
 * ClientPortal service — business logic for the client-facing project portal.
 */
class ClientPortal
{
    use DispatchesEvents;

    public function __construct(
        private ClientPortalRepo $repo,
        private NotificationsService $notificationsService,
    ) {}

    /**
     * Get all projects for the logged-in client user, enriched with progress + milestone counts.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getProjectsForClient(int $userId): array
    {
        if ($userId === 0) {
            return [];
        }

        $projects = $this->repo->getProjectsForClient($userId);

        foreach ($projects as &$project) {
            $progress = $this->repo->getProjectProgress((int) $project['id']);
            $milestones = $this->repo->getMilestones((int) $project['id']);

            $project['progress']         = $progress;
            $project['percent']          = $progress['total'] > 0
                ? (int) round(($progress['done'] / $progress['total']) * 100)
                : 0;
            $project['milestoneTotal']   = count($milestones);
            $project['milestoneDone']    = count(array_filter($milestones, fn ($m) => (int) ($m['status'] ?? 1) === 0));
            $project['nextMilestone']    = $this->findNextMilestone($milestones);
        }

        return $projects;
    }

    /**
     * Get full project detail: progress, milestones, team contacts, and requests.
     *
     * @return array<string, mixed>|null  null if project not found or client has no access
     */
    public function getProjectDetail(int $projectId, int $userId): ?array
    {
        if (! $this->canAccessProject($projectId)) {
            return null;
        }

        $project    = $this->repo->getProject($projectId);
        $progress   = $this->repo->getProjectProgress($projectId);
        $milestones = $this->repo->getMilestones($projectId);
        $contacts   = $this->repo->getTeamContacts($projectId);
        $requests   = $this->repo->getRequestsForProject($projectId);

        // Attach response to each request
        foreach ($requests as &$req) {
            $req['response']  = $this->repo->getResponseForRequest((int) $req['id']);
            $req['responses'] = $this->repo->getResponsesForRequest((int) $req['id']);
        }

        $percent = $progress['total'] > 0
            ? (int) round(($progress['done'] / $progress['total']) * 100)
            : 0;

        return [
            'project'    => $project,
            'progress'   => $progress,
            'percent'    => $percent,
            'milestones' => $milestones,
            'contacts'   => $contacts,
            'requests'   => $requests,
        ];
    }

    /**
     * Submit a new client request. Notifies all TL/CM on the project.
     *
     * @return int|false  New request ID on success, false on failure.
     */
    public function submitRequest(array $data, ?array $uploadedFile): int|false
    {
        $req = new ClientRequest();
        $req->projectId    = (int) ($data['projectId'] ?? 0);
        $req->clientUserId = (int) session('userdata.id');
        $req->title        = trim((string) ($data['title'] ?? ''));
        $req->description  = trim((string) ($data['description'] ?? ''));

        if ($req->title === '' || $req->projectId === 0) {
            return false;
        }

        // Authorization: the client may only submit to projects in their own org.
        if (! $this->canAccessProject($req->projectId)) {
            return false;
        }

        if (! empty($uploadedFile) && $uploadedFile['error'] === UPLOAD_ERR_OK) {
            $stored = $this->storeUploadedFile($uploadedFile);
            if ($stored === null) {
                return false;
            }
            $req->filePath = $stored;
        }

        try {
            $requestId = $this->repo->createRequest($req);
        } catch (\Throwable $e) {
            Log::error('ClientPortal: failed to create request — ' . $e->getMessage());

            return false;
        }

        $this->notifyTeam($req->projectId, $requestId, $req->title, 'new_request');

        return $requestId;
    }

    /**
     * Save a TL/CM response to a client request.
     *
     * @return bool
     */
    public function respondToRequest(array $data, ?array $uploadedFile): bool
    {
        $requestId = (int) ($data['requestId'] ?? 0);

        if ($requestId === 0) {
            return false;
        }

        $request = $this->repo->getRequest($requestId);

        if ($request === null) {
            return false;
        }

        // The client has accepted this request — it's closed, no more responses allowed.
        if (($request['status'] ?? '') === 'accepted') {
            return false;
        }

        // Authorization: the responder must be assigned to the request's project
        // (admins/owners may respond to any request).
        $responderId = (int) session('userdata.id');
        $role        = session('userdata.role');
        $isAdmin     = in_array($role, [Roles::$admin, Roles::$owner], true);

        if (! $isAdmin && ! $this->repo->isUserAssignedToProject($responderId, (int) $request['projectId'])) {
            return false;
        }

        // Reject non-http(s) links — driveLink is rendered into an href, so a
        // javascript: / data: URI would be a stored XSS vector.
        $driveLink = trim((string) ($data['driveLink'] ?? ''));
        if ($driveLink !== '' && preg_match('#^https?://#i', $driveLink) !== 1) {
            return false;
        }

        $resp = new ClientRequestResponse();
        $resp->requestId           = $requestId;
        $resp->respondedByUserId   = $responderId;
        $resp->driveLink           = $driveLink ?: null;
        $resp->notes               = trim((string) ($data['notes'] ?? '')) ?: null;

        if (! empty($uploadedFile) && $uploadedFile['error'] === UPLOAD_ERR_OK) {
            $stored = $this->storeUploadedFile($uploadedFile);
            if ($stored === null) {
                return false;
            }
            $resp->documentPath = $stored;
        }

        try {
            $this->repo->createResponse($resp);
            $this->repo->markRequestReviewed($requestId);
        } catch (\Throwable $e) {
            Log::error('ClientPortal: failed to save response — ' . $e->getMessage());

            return false;
        }

        // Notify the client who submitted the request
        $clientId = (int) $request['clientUserId'];
        $this->notificationsService->addNotifications([[
            'userId'   => $clientId,
            'type'     => 'info',
            'module'   => 'clientportal',
            'moduleId' => $requestId,
            'message'  => __('clientportal.notifications.request_responded'),
            'datetime' => date('Y-m-d H:i:s'),
            'url'      => '/clientportal/showProject/' . $request['projectId'],
            'authorId' => (int) session('userdata.id'),
        ]]);

        return true;
    }

    /**
     * Record a client's review decision (accepted / rejected / changes_requested)
     * on a request that the TL/CM has already responded to.
     *
     * Reject and changes_requested re-open the request so the TL/CM can submit
     * another response. Accept locks the request as 'accepted'.
     *
     * @api
     */
    public function submitClientReview(int $requestId, string $action, ?string $reason): bool
    {
        $allowed = ['accepted', 'rejected', 'changes_requested'];
        if (! in_array($action, $allowed, true)) {
            return false;
        }

        $reason = $reason !== null ? trim($reason) : null;

        // A reason is required for any non-accept decision.
        if ($action !== 'accepted' && ($reason === null || $reason === '')) {
            return false;
        }

        $request = $this->repo->getRequest($requestId);
        if ($request === null) {
            return false;
        }

        // Authorization: ONLY the client user who originally submitted the
        // request may review the response. Other clients in the same org —
        // even on the same project — cannot decide on someone else's request.
        // Admin/owner may review on behalf for testing/support.
        $userId   = (int) session('userdata.id');
        $role     = session('userdata.role');
        $isAdmin  = in_array($role, [Roles::$admin, Roles::$owner], true);
        $sameUser = (int) $request['clientUserId'] === $userId;

        if (! $isAdmin && ! $sameUser) {
            return false;
        }

        // Only requests currently in 'reviewed' state (TL/CM has responded and
        // the client hasn't decided yet) may be reviewed.
        if (($request['status'] ?? '') !== 'reviewed') {
            return false;
        }

        $stored = $action === 'accepted' ? null : $reason;
        if (! $this->repo->recordClientReview($requestId, $action, $stored)) {
            return false;
        }

        // Notify the latest responder so they know the client has acted.
        $latestResponse = $this->repo->getResponseForRequest($requestId);
        $notifyUserId   = $latestResponse ? (int) ($latestResponse['respondedByUserId'] ?? 0) : 0;

        if ($notifyUserId > 0 && $notifyUserId !== $userId) {
            $messageKey = match ($action) {
                'accepted'          => 'clientportal.notifications.review_accepted',
                'rejected'          => 'clientportal.notifications.review_rejected',
                'changes_requested' => 'clientportal.notifications.review_changes_requested',
            };

            $this->notificationsService->addNotifications([[
                'userId'   => $notifyUserId,
                'type'     => 'info',
                'module'   => 'clientportal',
                'moduleId' => $requestId,
                'message'  => __($messageKey),
                'datetime' => date('Y-m-d H:i:s'),
                'url'      => '/clientportal/adminRequests?projectId=' . (int) $request['projectId'],
                'authorId' => $userId,
            ]]);
        }

        return true;
    }

    /**
     * Check if the currently logged-in client user can access a given project.
     * Access is granted when the project belongs to the user's client org.
     */
    public function canAccessProject(int $projectId): bool
    {
        $userId   = (int) session('userdata.id');
        $clientId = (int) session('userdata.clientId');

        if ($userId === 0 || $clientId === 0) {
            return false;
        }

        return $this->repo->isProjectInClientOrg($clientId, $projectId);
    }

    /**
     * Check whether the current user may view the request list for a project.
     * Clients: project must belong to their org. TL/CM: must be assigned to it.
     * Admins/owners: always allowed.
     */
    public function canAccessProjectRequests(int $projectId): bool
    {
        $userId = (int) session('userdata.id');
        $role   = session('userdata.role');

        if ($userId === 0 || $projectId === 0) {
            return false;
        }

        if (in_array($role, [Roles::$admin, Roles::$owner], true)) {
            return true;
        }

        if ($role === Roles::$commenter) {
            return $this->canAccessProject($projectId);
        }

        if (in_array($role, [Roles::$teamlead, Roles::$manager], true)) {
            return $this->repo->isUserAssignedToProject($userId, $projectId);
        }

        return false;
    }

    /**
     * Get requests (with responses) visible to the current TL/CM for a project.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getRequestsForProject(int $projectId): array
    {
        $requests = $this->repo->getRequestsForProject($projectId);

        foreach ($requests as &$req) {
            $req['response']  = $this->repo->getResponseForRequest((int) $req['id']);
            $req['responses'] = $this->repo->getResponsesForRequest((int) $req['id']);
        }

        return $requests;
    }

    /**
     * Get all client requests visible to the current TL/CM/Admin, with responses attached.
     *
     * @api
     * @return array<int, array<string, mixed>>
     */
    public function getAllRequests(int $filterProjectId = 0): array
    {
        $userId  = (int) session('userdata.id');
        $role    = session('userdata.role');
        $isAdmin = in_array($role, ['admin', 'owner'], true);

        $requests = $this->repo->getAllRequests($userId, $isAdmin, $filterProjectId);

        foreach ($requests as &$req) {
            $req['response']  = $this->repo->getResponseForRequest((int) $req['id']);
            $req['responses'] = $this->repo->getResponsesForRequest((int) $req['id']);
        }

        return $requests;
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Find the first incomplete milestone (status != 0).
     *
     * @param  array<int, array<string, mixed>>  $milestones
     * @return array<string, mixed>|null
     */
    private function findNextMilestone(array $milestones): ?array
    {
        foreach ($milestones as $m) {
            if ((int) ($m['status'] ?? 1) !== 0) {
                return $m;
            }
        }

        return null;
    }

    /**
     * Allowed upload extensions for client request / response attachments.
     * Anything not on this list is rejected — the directory is web-served, so
     * executable types (php, phtml, etc.) must never be accepted.
     */
    private const ALLOWED_UPLOAD_EXTENSIONS = [
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv',
        'png', 'jpg', 'jpeg', 'gif', 'webp',
    ];

    /**
     * Store an uploaded file in the client-requests uploads directory.
     * Returns the web-accessible relative path, or null if the file was
     * rejected (disallowed extension) or could not be stored.
     */
    private function storeUploadedFile(array $file): ?string
    {
        $ext = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        if ($ext === '' || ! in_array($ext, self::ALLOWED_UPLOAD_EXTENSIONS, true)) {
            Log::warning('ClientPortal: rejected upload with disallowed extension "' . $ext . '"');

            return null;
        }

        $uploadDir = public_path('userfiles/client-requests');

        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Defense in depth: ensure the upload dir cannot execute PHP even if a
        // bad file somehow lands there.
        $htaccess = $uploadDir . DIRECTORY_SEPARATOR . '.htaccess';
        if (! file_exists($htaccess)) {
            file_put_contents(
                $htaccess,
                "php_flag engine off\n"
                . "<FilesMatch \"\\.(php|phtml|php3|php4|php5|php7|phps|pht|phar)$\">\n"
                . "    Require all denied\n"
                . "</FilesMatch>\n"
            );
        }

        $safeName = bin2hex(random_bytes(16)) . '.' . $ext;
        $dest     = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

        if (! move_uploaded_file($file['tmp_name'], $dest)) {
            Log::error('ClientPortal: failed to move uploaded file to ' . $dest);

            return null;
        }

        return 'userfiles/client-requests/' . $safeName;
    }

    /**
     * Notify all TL/CM on a project about a new client request.
     */
    private function notifyTeam(int $projectId, int $requestId, string $title, string $eventKey): void
    {
        $teamIds = $this->repo->getTeamContactIds($projectId);

        if (empty($teamIds)) {
            return;
        }

        $actorId = (int) session('userdata.id');
        $message = sprintf(__('clientportal.notifications.' . $eventKey), $title);
        $url     = '/clientportal/showProject/' . $projectId;
        $now     = date('Y-m-d H:i:s');

        $notifications = array_map(fn ($uid) => [
            'userId'   => $uid,
            'type'     => 'info',
            'module'   => 'clientportal',
            'moduleId' => $requestId,
            'message'  => $message,
            'datetime' => $now,
            'url'      => $url,
            'authorId' => $actorId,
        ], array_filter($teamIds, fn ($uid) => $uid !== $actorId));

        if (! empty($notifications)) {
            $this->notificationsService->addNotifications($notifications);
        }
    }
}
