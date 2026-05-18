<?php

namespace Leantime\Domain\ClientPortal\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\ClientPortal\Services\ClientPortal as ClientPortalService;

/**
 * Requests HxController — handles the client request form and TL/CM response form.
 *
 * GET  /hx/clientportal/requests/form?projectId=X       → show request submission form
 * POST /hx/clientportal/requests/submit                 → save new client request
 * GET  /hx/clientportal/requests/responseForm?id=X      → show response form for TL/CM
 * POST /hx/clientportal/requests/saveResponse           → save TL/CM response
 * GET  /hx/clientportal/requests/list?projectId=X       → reload request list partial
 */
class Requests extends HtmxController
{
    protected static string $view = 'clientportal::partials.requestList';

    private ClientPortalService $portalService;

    public function init(ClientPortalService $portalService): void
    {
        $this->portalService = $portalService;
    }

    /**
     * Whether the current user may act as a client portal user
     * (commenter role, or an admin/owner previewing the portal).
     */
    private function canActAsClient(): bool
    {
        return session('userdata.role') === Roles::$commenter
            || Auth::userIsAtLeast(Roles::$admin, true);
    }

    /**
     * Show the new-request submission form (client only).
     */
    public function form(): void
    {
        if (! $this->canActAsClient()) {
            $this->tpl->setNotification('errors.error403', 'error');

            return;
        }

        $projectId = (int) ($this->incomingRequest->query->get('projectId') ?? 0);
        $this->tpl->assign('projectId', $projectId);
        static::$view = 'clientportal::partials.requestForm';
    }

    /**
     * Save a new client request (POST).
     */
    public function submit(): void
    {
        if (! $this->canActAsClient()) {
            $this->tpl->setNotification('errors.error403', 'error');

            return;
        }

        $projectId = (int) ($this->incomingRequest->request->get('projectId') ?? 0);
        $data = [
            'projectId' => $projectId,
            'title' => $this->incomingRequest->request->get('title') ?? '',
            'description' => $this->incomingRequest->request->get('description') ?? '',
        ];

        $file = $_FILES['requestFile'] ?? null;
        $result = $this->portalService->submitRequest($data, $file ?: null);

        if ($result !== false) {
            $this->tpl->setNotification('clientportal.notifications.request_sent', 'success');
        } else {
            $this->tpl->setNotification('clientportal.notifications.request_failed', 'error');
        }

        $this->setHTMXEvent('clientportal_request_updated');

        // Re-render the request list for this project
        $requests = $this->portalService->getRequestsForProject($projectId);
        $this->tpl->assign('requests', $requests);
        $this->tpl->assign('projectId', $projectId);
        static::$view = 'clientportal::partials.requestList';
    }

    /**
     * Show the response form for TL/CM.
     */
    public function responseForm(): void
    {
        if (! Auth::userIsAtLeast(Roles::$teamlead, true)) {
            $this->tpl->setNotification('errors.error403', 'error');

            return;
        }

        $requestId = (int) ($this->incomingRequest->query->get('id') ?? 0);
        $fromAdmin = (bool) ($this->incomingRequest->query->get('fromAdmin') ?? false);
        $this->tpl->assign('requestId', $requestId);
        $this->tpl->assign('fromAdmin', $fromAdmin);
        static::$view = 'clientportal::partials.responseForm';
    }

    /**
     * Save a TL/CM response (POST).
     */
    public function saveResponse(): void
    {
        $role = session('userdata.role');
        $canRespond = in_array($role, [Roles::$teamlead, Roles::$manager, Roles::$admin, Roles::$owner], true);

        if (! $canRespond) {
            $this->tpl->setNotification('errors.error403', 'error');

            return;
        }

        $requestId = (int) ($this->incomingRequest->request->get('requestId') ?? 0);
        $data = [
            'requestId' => $requestId,
            'driveLink' => $this->incomingRequest->request->get('driveLink') ?? '',
            'notes' => $this->incomingRequest->request->get('notes') ?? '',
        ];

        $file = $_FILES['responseFile'] ?? null;
        $result = $this->portalService->respondToRequest($data, $file ?: null);

        if ($result) {
            $this->tpl->setNotification('clientportal.notifications.response_saved', 'success');
        } else {
            $this->tpl->setNotification('clientportal.notifications.response_failed', 'error');
        }

        $fromAdmin = (bool) ($this->incomingRequest->request->get('fromAdmin') ?? false);

        if ($fromAdmin) {
            // Admin page: trigger a full page refresh so the card updates
            $this->headers['HX-Refresh'] = 'true';
        } else {
            $this->setHTMXEvent('clientportal_request_updated');
        }

        static::$view = 'clientportal::partials.requestList';
    }

    /**
     * Save a client's review decision (accept / reject / request changes) on a
     * TL/CM response. Re-renders the request list afterwards.
     */
    public function submitReview(): void
    {
        if (! $this->canActAsClient()) {
            $this->tpl->setNotification('errors.error403', 'error');

            return;
        }

        $requestId = (int) ($this->incomingRequest->request->get('requestId') ?? 0);
        $action = (string) ($this->incomingRequest->request->get('action') ?? '');
        $reason = (string) ($this->incomingRequest->request->get('reason') ?? '');
        $projectId = (int) ($this->incomingRequest->request->get('projectId') ?? 0);

        $result = $this->portalService->submitClientReview($requestId, $action, $reason);

        if ($result) {
            $this->tpl->setNotification('clientportal.notifications.review_saved', 'success');
        } else {
            $this->tpl->setNotification('clientportal.notifications.review_failed', 'error');
        }

        $this->setHTMXEvent('clientportal_request_updated');

        $requests = $this->portalService->getRequestsForProject($projectId);
        $this->tpl->assign('requests', $requests);
        $this->tpl->assign('projectId', $projectId);
        static::$view = 'clientportal::partials.requestList';
    }

    /**
     * Reload the request list for a project.
     */
    public function list(): void
    {
        $projectId = (int) ($this->incomingRequest->query->get('projectId') ?? 0);

        if (! $this->portalService->canAccessProjectRequests($projectId)) {
            $this->tpl->setNotification('errors.error403', 'error');

            return;
        }

        $requests = $this->portalService->getRequestsForProject($projectId);

        $this->tpl->assign('requests', $requests);
        $this->tpl->assign('projectId', $projectId);
    }
}
