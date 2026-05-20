<?php

namespace Leantime\Domain\Worktracker\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Worktracker\Services\WorkTracker as WorkTrackerService;
use Symfony\Component\HttpFoundation\Response;

/**
 * REST-ish JSON controller for the WorkTracker module.
 *
 * Lives at /worktracker/api (NOT /api/worktracker) on purpose — Leantime
 * treats anything under /api/ as an external-integration endpoint that
 * requires an API key (see Core\Middleware\AuthCheck::authenticateWeb
 * and Http\IncomingRequest::$apiEndpoints). Our navbar widget and
 * dashboard JS authenticate via the user's session cookie, so we route
 * through a normal module controller path instead.
 *
 * Routes (resolved by Leantime's Frontcontroller using the HTTP method
 * as the action name on a 2-segment URL):
 *   GET    /worktracker/api  → get()    — current timer status
 *   POST   /worktracker/api  → post()   — start a session
 *   PATCH  /worktracker/api  → patch()  — stop a session (optionally with end screenshot)
 *   DELETE /worktracker/api  → delete() — cancel an orphan session (no screenshot needed)
 */
class Api extends Controller
{
    private WorkTrackerService $workTrackerService;

    public function init(WorkTrackerService $workTrackerService): void
    {
        $this->workTrackerService = $workTrackerService;
    }

    /**
     * GET /worktracker/api
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
     * POST /worktracker/api
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
     * PATCH /worktracker/api
     * Stops an active work session.
     *
     * JSON body: { "session_id": 101, "screenshot": "<base64 string>" }
     */
    public function patch(array $params): Response
    {
        Auth::authOrRedirect([Roles::$editor, Roles::$manager, Roles::$admin, Roles::$owner], true);

        $userId     = (int) session('userdata.id');
        $body       = $this->jsonBody();
        $sessionId  = isset($body['session_id']) ? (int) $body['session_id'] : 0;
        $screenshot = $body['screenshot'] ?? '';

        if ($sessionId <= 0) {
            return $this->tpl->displayJson(['success' => false, 'message' => 'session_id is required.'], 422);
        }

        $result   = $this->workTrackerService->stopSession($sessionId, $userId, $screenshot);
        $httpCode = $result['success'] ? 200 : 404;

        return $this->tpl->displayJson($result, $httpCode);
    }

    /**
     * DELETE /worktracker/api
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
