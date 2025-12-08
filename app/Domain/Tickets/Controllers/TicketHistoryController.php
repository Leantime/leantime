<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Tickets\Models\TicketHistoryModel;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Domain\Setting\Services\Setting as SettingService;

class TicketHistoryController extends Controller
{
    private TicketHistoryModel $ticketHistoryModel;
    private Auth $authService;
    private SettingService $settingsService;

    public function init(
        TicketHistoryModel $ticketHistoryModel,
        Auth $authService,
        SettingService $settingsService
    ): void {
        $this->ticketHistoryModel = $ticketHistoryModel;
        $this->authService = $authService;
        $this->settingsService = $settingsService;
    }

    public function logStatusChange(): JsonResponse
    {
        if (!$this->authService->loggedIn()) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'Unauthorized'
            ], 401);
        }

        $ticketId = $_POST['ticketId'] ?? null;
        $oldStatus = $_POST['oldStatus'] ?? '';
        $newStatus = $_POST['newStatus'] ?? '';
        $oldStatusText = $_POST['oldStatusText'] ?? '';
        $newStatusText = $_POST['newStatusText'] ?? '';
        $user = $_POST['user'] ?? $this->authService->getUserName();
        $detailsAttributeId = $_POST['detailsAttributeId'] ?? '';

        if (!$ticketId) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'No ticket ID provided'
            ], 400);
        }

        try {
            $insertId = $this->ticketHistoryModel->addStatusChange(
                $ticketId,
                $oldStatus,
                $newStatus,
                $oldStatusText,
                $newStatusText,
                $user,
                $detailsAttributeId
            );

            if ($insertId) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'Status change logged successfully',
                    'id' => $insertId
                ]);
            } else {
                return new JsonResponse([
                    'success' => false, 
                    'error' => 'Failed to save status change'
                ], 500);
            }
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getStatusChanges(): Response
    {
        $ticketId = $_GET['ticketId'] ?? null;

        if (!$ticketId) {
            return new Response(
                json_encode(['success' => false, 'error' => 'No ticket ID provided']),
                400,
                ['Content-Type' => 'application/json']
            );
        }

        try {
            $changes = $this->ticketHistoryModel->getStatusChangesByTicket($ticketId);
            error_log(print_r($changes, true));

            if (empty($changes)) {
                $html = '<p style="color: #999; padding: 10px;">No status changes yet.</p>';
            } else {
                $html = '<ul style="list-style: none; padding: 0; margin: 0;">';
                foreach ($changes as $change) {
                    $detailsAttributeId = $change['detailsAttributeId'];
                    if($detailsAttributeId === 'priority') {
                        $attributeLabel = 'Priority';
                    } elseif($detailsAttributeId === 'storypoints') {
                        $attributeLabel = 'Effort';
                    } elseif($detailsAttributeId === 'editorId') {
                        $attributeLabel = 'Assigned to';
                    } elseif($detailsAttributeId === 'deadline') {
                        $attributeLabel = 'Due Date';
                    } elseif($detailsAttributeId === 'dueTime') {
                        $attributeLabel = 'Due Date Time';
                    } else {
                        $attributeLabel = 'Status';
                    }
                    $html .= '<li style="padding: 8px 0; border-bottom: 1px solid #eee;">';
                    $html .= '<strong>' . htmlspecialchars($change['changedBy']) . '</strong> changed '. $attributeLabel . ' on ';
                    $html .= '<span style="color: #666; font-size: 0.9em;">' . date('d.m.Y H:i', strtotime($change['changedAt'])) . '</span><br>';
                    $html .= '<span style="color: #999;">' . htmlspecialchars($change['oldStatus']) . '</span>';
                    $html .= ' <i class="fa fa-arrow-right" style="color: #999; font-size: 0.8em;"></i> ';
                    $html .= '<span style="color: #28a745; font-weight: bold;">' . htmlspecialchars($change['newStatusText']) . '</span>';
                    $html .= '</li>';
                }
                $html .= '</ul>';
            }

            return new Response($html, 200, ['Content-Type' => 'text/html']);
            
        } catch (\Exception $e) {
            return new Response(
                '<p style="color: red;">Error loading status changes: ' . htmlspecialchars($e->getMessage()) . '</p>',
                500,
                ['Content-Type' => 'text/html']
            );
        }
    }
}