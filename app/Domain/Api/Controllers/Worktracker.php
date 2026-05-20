<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Worktracker\Services\WorkTracker as WorkTrackerService;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST API controller for the WorkTracker module.
 *
 * Routes (resolved by Leantime's Frontcontroller):
 *   GET    /api/worktracker  → get()    — current timer status
 *   POST   /api/worktracker  → post()   — start a session
 *   PATCH  /api/worktracker  → patch()  — stop a session (with end screenshot)
 *   DELETE /api/worktracker  → delete() — cancel an orphan session (no screenshot needed)
 */
class Worktracker extends Controller
{
    private WorkTrackerService $workTrackerService;

    public function init(WorkTrackerService $workTrackerService): void
    {
        $this->workTrackerService = $workTrackerService;
    }

    /**
     * GET /api/worktracker
     * Returns the current timer status for the authenticated user.
     */
    public function get(array $params): Response
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $userId = (int) session('userdata.id');
        $status = $this->workTrackerService->getTimerStatus($userId);

        if ($status['running']) {
            $status['elapsed_formatted'] = WorkTrackerService::formatDuration($status['elapsed_seconds']);
        }

        return $this->tpl->displayJson($status);
    }

    /**
     * POST /api/worktracker
     * Starts a new work session.
     *
     * JSON body: { "screenshot": "<base64 string>" }
     */
    public function post(array $params): Response
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $userId     = (int) session('userdata.id');
        $body       = $this->jsonBody();
        $screenshot = $body['screenshot'] ?? '';

        if (empty($screenshot)) {
            return $this->tpl->displayJson(['success' => false, 'message' => 'screenshot field is required.'], 422);
        }

        $result = $this->workTrackerService->startSession($userId, $screenshot);

        $httpCode = $result['success'] ? 201 : 409;

        return $this->tpl->displayJson($result, $httpCode);
    }

    /**
     * PATCH /api/worktracker
     * Stops an active work session.
     *
     * JSON body: { "session_id": 101, "screenshot": "<base64 string>" }
     */
    public function patch(array $params): Response
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $userId    = (int) session('userdata.id');
        $body      = $this->jsonBody();
        $sessionId = isset($body['session_id']) ? (int) $body['session_id'] : 0;
        $screenshot = $body['screenshot'] ?? '';

        if ($sessionId <= 0) {
            return $this->tpl->displayJson(['success' => false, 'message' => 'session_id is required.'], 422);
        }

        $result   = $this->workTrackerService->stopSession($sessionId, $userId, $screenshot);
        $httpCode = $result['success'] ? 200 : 404;

        return $this->tpl->displayJson($result, $httpCode);
    }

    /**
     * DELETE /api/worktracker
     * Cancels an orphaned running session without saving a stop screenshot.
     *
     * JSON body: { "session_id": 101 }
     */
    public function delete(array $params): Response
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $userId    = (int) session('userdata.id');
        $body      = $this->jsonBody();
        $sessionId = isset($body['session_id']) ? (int) $body['session_id'] : 0;

        if ($sessionId <= 0) {
            return $this->tpl->displayJson(['success' => false, 'message' => 'session_id is required.'], 422);
        }

        $result   = $this->workTrackerService->cancelSession($sessionId, $userId);
        $httpCode = $result['success'] ? 200 : 404;

        return $this->tpl->displayJson($result, $httpCode);
    }

    /**
     * Decode the raw JSON request body into an associative array.
     */
    private function jsonBody(): array
    {
        $raw = $this->incomingRequest->getContent();

        if (empty($raw)) {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }
}
