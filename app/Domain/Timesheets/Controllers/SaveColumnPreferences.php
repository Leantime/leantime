<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * SaveColumnPreferences Controller
 * 
 * New additive endpoint for saving/loading user column preferences
 * Does not modify existing functionality - pure addition
 * 
 * @package Leantime\Domain\Timesheets\Controllers
 */
class SaveColumnPreferences extends Controller
{
    private SettingRepository $settingRepo;

    /**
     * Constructor
     */
    public function init(SettingRepository $settingRepo): void
    {
        $this->settingRepo = $settingRepo;
    }

    /**
     * GET - Load column preferences for current user
     * 
     * @param array $params
     * @return Response
     */
    public function get(array $params): Response
    {
        try {
            $userId = session('userdata.id');
            
            if (!$userId) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            $tableId = $_GET['tableId'] ?? 'allTimesheets';
            
            // Sanitize table ID (alphanumeric and underscore only)
            $tableId = preg_replace('/[^a-zA-Z0-9_]/', '', $tableId);

            // Build setting key
            $settingKey = "user.{$userId}.tableColumns.{$tableId}";

            // Load from settings
            $columnState = $this->settingRepo->getSetting($settingKey);

            if ($columnState) {
                // Decode JSON if stored as string
                if (is_string($columnState)) {
                    $columnState = json_decode($columnState, true);
                }

                return $this->jsonResponse([
                    'status' => 'success',
                    'columnState' => $columnState,
                    'tableId' => $tableId
                ]);
            }

            // No preferences found - return empty state
            return $this->jsonResponse([
                'status' => 'success',
                'columnState' => null,
                'tableId' => $tableId,
                'message' => 'No saved preferences found'
            ]);

        } catch (\Exception $e) {
            error_log('SaveColumnPreferences GET error: ' . $e->getMessage());
            
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to load preferences'
            ], 500);
        }
    }

    /**
     * POST - Save column preferences for current user
     * 
     * @param array $params
     * @return Response
     */
    public function post(array $params): Response
    {
        try {
            $userId = session('userdata.id');
            
            if (!$userId) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get JSON payload
            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid JSON payload'
                ], 400);
            }

            $tableId = $input['tableId'] ?? 'allTimesheets';
            $columnState = $input['columnState'] ?? [];

            // Sanitize table ID
            $tableId = preg_replace('/[^a-zA-Z0-9_]/', '', $tableId);

            // Validate column state is array
            if (!is_array($columnState)) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Column state must be an object'
                ], 400);
            }

            // Build setting key
            $settingKey = "user.{$userId}.tableColumns.{$tableId}";

            // Save to settings (as JSON string)
            $this->settingRepo->saveSetting($settingKey, json_encode($columnState));

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Preferences saved successfully',
                'tableId' => $tableId,
                'savedAt' => date('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            error_log('SaveColumnPreferences POST error: ' . $e->getMessage());
            
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to save preferences'
            ], 500);
        }
    }

    /**
     * Helper method to return JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }
}

