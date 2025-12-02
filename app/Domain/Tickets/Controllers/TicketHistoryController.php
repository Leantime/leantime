<?php

namespace Leantime\Domain\Tickets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Tickets\Models\TicketHistoryModel;
use Leantime\Domain\Auth\Services\Auth;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class TicketHistoryController extends Controller
{
    private TicketHistoryModel $ticketHistoryModel;
    private Auth $authService;

    public function init(
        TicketHistoryModel $ticketHistoryModel,
        Auth $authService
    ): void {
        $this->ticketHistoryModel = $ticketHistoryModel;
        $this->authService = $authService;
    }

    /**
     * Log status change - called via AJAX
     */
    public function logStatusChange(): JsonResponse
    {
        // Check if user is logged in
        if (!$this->authService->loggedIn()) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'Unauthorized'
            ], 401);
        }

        // Get POST data
        $ticketId = $_POST['ticketId'] ?? null;
        $oldStatus = $_POST['oldStatus'] ?? '';
        $newStatus = $_POST['newStatus'] ?? '';
        $oldStatusText = $_POST['oldStatusText'] ?? '';
        $newStatusText = $_POST['newStatusText'] ?? '';
        $user = $_POST['user'] ?? $this->authService->getUserName();
        $detailsAttributeId = $_POST['detailsAttributeId'] ?? '';

        // Validate ticket ID
        if (!$ticketId) {
            return new JsonResponse([
                'success' => false, 
                'error' => 'No ticket ID provided'
            ], 400);
        }

        try {
            // Save to database
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

    /**
     * Get status changes for a ticket - called via AJAX
     */
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
                    } else {
                        $attributeLabel = 'Status';
                    }
                    $html .= '<li style="padding: 8px 0; border-bottom: 1px solid #eee;">';
                    $html .= '<strong>' . htmlspecialchars($change['changedBy']) . '</strong> changed '. $attributeLabel . ' on ';
                    $html .= '<span style="color: #666; font-size: 0.9em;">' . date('d.m.Y H:i', strtotime($change['changedAt'])) . '</span><br>';
                    $html .= '<span style="color: #999;">' . htmlspecialchars($change['oldStatusText']) . '</span>';
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