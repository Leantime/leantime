<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
    Endpoint for saving/loading user filter preferences for timesheet reports
 */
class SaveFilterPreferences extends Controller
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
     GET - Load all saved filter preferences for current user
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

            // Get all saved preferences for this user
            $settingKey = "user.{$userId}.timesheetFilters";
            $preferences = $this->settingRepo->getSetting($settingKey);

            if ($preferences) {
                if (is_string($preferences)) {
                    $preferences = json_decode($preferences, true);
                }

                return $this->jsonResponse([
                    'status' => 'success',
                    'preferences' => $preferences ?: []
                ]);
            }

            return $this->jsonResponse([
                'status' => 'success',
                'preferences' => [],
                'message' => 'No saved preferences found'
            ]);

        } catch (\Exception $e) {
            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to load preferences'
            ], 500);
        }
    }

    /**
      POST - Save a new filter preference or update existing one
     */
    public function post(array $params): Response
    {
        error_log('[Profiles] POST request received');
        try {
            $userId = session('userdata.id');
            error_log('[Profiles] User ID: ' . ($userId ?? 'NULL'));

            if (!$userId) {
                error_log('[Profiles] User not authenticated');
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'User not authenticated'
                ], 401);
            }

            // Get JSON payload
            $input = json_decode(file_get_contents('php://input'), true);
            error_log('[Profiles] POST payload: ' . json_encode($input));

            if (!$input) {
                error_log('[Profiles] Invalid JSON payload');
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid JSON payload'
                ], 400);
            }

            $action = $input['action'] ?? 'save'; // save, delete, or load
            $preferenceName = $input['name'] ?? '';
            error_log('[Profiles] Action: ' . $action . ', Name: ' . $preferenceName);

            // Get all existing preferences
            $settingKey = "user.{$userId}.timesheetFilters";
            error_log('[Profiles] Setting key: ' . $settingKey);
            $allPreferences = $this->settingRepo->getSetting($settingKey);
            error_log('[Profiles] Existing preferences loaded from DB');

            if ($allPreferences && is_string($allPreferences)) {
                $allPreferences = json_decode($allPreferences, true);
            }

            if (!is_array($allPreferences)) {
                $allPreferences = [];
            }

            // Handle different actions
            error_log('[Profiles] Processing action: ' . $action);
            if ($action === 'save') {
                error_log('[Profiles] SAVE action - validating preference name');
                // Validate preference name
                if (empty($preferenceName) || strlen($preferenceName) > 100) {
                    error_log('[Profiles] Invalid preference name');
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Invalid preference name'
                    ], 400);
                }

                // Get filter data
                $filterData = $input['filters'] ?? [];
                error_log('[Profiles] Filter data keys: ' . json_encode(array_keys($filterData)));

                // Validate filter data
                if (!is_array($filterData)) {
                    error_log('[Profiles] Filter data is not an array');
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Filter data must be an object'
                    ], 400);
                }

                // Save preference
                error_log('[Profiles] Creating preference object');
                $allPreferences[$preferenceName] = [
                    'name' => $preferenceName,
                    'filters' => $filterData,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'updatedAt' => date('Y-m-d H:i:s')
                ];

                // Update in database
                error_log('[Profiles] Saving to database, total profiles: ' . count($allPreferences));
                $this->settingRepo->saveSetting($settingKey, json_encode($allPreferences));
                error_log('[Profiles] Successfully saved to database');

                return $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Preference saved successfully',
                    'preference' => $allPreferences[$preferenceName]
                ]);

            } elseif ($action === 'delete') {
                error_log('[Profiles] DELETE action');
                // Delete preference
                if (isset($allPreferences[$preferenceName])) {
                    error_log('[Profiles] Preference found, deleting: ' . $preferenceName);
                    unset($allPreferences[$preferenceName]);
                    $this->settingRepo->saveSetting($settingKey, json_encode($allPreferences));
                    error_log('[Profiles] Successfully deleted from database');

                    return $this->jsonResponse([
                        'status' => 'success',
                        'message' => 'Preference deleted successfully'
                    ]);
                } else {
                    error_log('[Profiles] Preference not found: ' . $preferenceName);
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Preference not found'
                    ], 404);
                }

            } elseif ($action === 'load') {
                error_log('[Profiles] LOAD action');
                // Load specific preference
                if (isset($allPreferences[$preferenceName])) {
                    error_log('[Profiles] Preference found: ' . $preferenceName);
                    return $this->jsonResponse([
                        'status' => 'success',
                        'preference' => $allPreferences[$preferenceName]
                    ]);
                } else {
                    error_log('[Profiles] Preference not found: ' . $preferenceName);
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Preference not found'
                    ], 404);
                }
            } else {
                error_log('[Profiles] Invalid action: ' . $action);
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid action'
                ], 400);
            }

        } catch (\Exception $e) {
            error_log('[Profiles] POST error: ' . $e->getMessage());
            error_log('[Profiles] Stack trace: ' . $e->getTraceAsString());

            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to process request'
            ], 500);
        }
    }

    /**
      Helper method to return JSON response
     */
    private function jsonResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }
}
