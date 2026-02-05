<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class SaveFilterPreferences extends Controller
{
    private SettingRepository $settingRepo;

    public function init(SettingRepository $settingRepo): void
    {
        $this->settingRepo = $settingRepo;
    }

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

            $input = json_decode(file_get_contents('php://input'), true);

            if (!$input) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid JSON payload'
                ], 400);
            }

            $action = $input['action'] ?? 'save'; 
            $preferenceName = $input['name'] ?? '';

            $settingKey = "user.{$userId}.timesheetFilters";
            $allPreferences = $this->settingRepo->getSetting($settingKey);

            if ($allPreferences && is_string($allPreferences)) {
                $allPreferences = json_decode($allPreferences, true);
            }

            if (!is_array($allPreferences)) {
                $allPreferences = [];
            }

            if ($action === 'save') {
                if (empty($preferenceName) || strlen($preferenceName) > 100) {
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Invalid preference name'
                    ], 400);
                }

                $filterData = $input['filters'] ?? [];

                if (!is_array($filterData)) {
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Filter data must be an object'
                    ], 400);
                }

                $allPreferences[$preferenceName] = [
                    'name' => $preferenceName,
                    'filters' => $filterData,
                    'createdAt' => date('Y-m-d H:i:s'),
                    'updatedAt' => date('Y-m-d H:i:s')
                ];

                $this->settingRepo->saveSetting($settingKey, json_encode($allPreferences));

                return $this->jsonResponse([
                    'status' => 'success',
                    'message' => 'Preference saved successfully',
                    'preference' => $allPreferences[$preferenceName]
                ]);

            } elseif ($action === 'delete') {
                if (isset($allPreferences[$preferenceName])) {
                    unset($allPreferences[$preferenceName]);
                    $this->settingRepo->saveSetting($settingKey, json_encode($allPreferences));

                    return $this->jsonResponse([
                        'status' => 'success',
                        'message' => 'Preference deleted successfully'
                    ]);
                } else {
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Preference not found'
                    ], 404);
                }

            } elseif ($action === 'load') {
                if (isset($allPreferences[$preferenceName])) {
                    return $this->jsonResponse([
                        'status' => 'success',
                        'preference' => $allPreferences[$preferenceName]
                    ]);
                } else {
                    return $this->jsonResponse([
                        'status' => 'error',
                        'message' => 'Preference not found'
                    ], 404);
                }
            } 
            elseif ($action === 'setAutoExport') {
    $autoExport = $input['autoExport'] ?? false;
    
    if (!isset($allPreferences[$preferenceName])) {
        return $this->jsonResponse(['status' => 'error', 'message' => 'Profile not found'], 404);
    }
    
    $allPreferences[$preferenceName]['autoExport'] = $autoExport;
    $this->settingRepo->saveSetting($settingKey, json_encode($allPreferences));
    
    return $this->jsonResponse(['status' => 'success']);
}
            elseif ($action === 'setSlackProject') {
                $slackProjectId = $input['slackProjectId'] ?? '';

                if (!isset($allPreferences[$preferenceName])) {
                    return $this->jsonResponse(['status' => 'error', 'message' => 'Profile not found'], 404);
                }

                $allPreferences[$preferenceName]['slackProjectId'] = $slackProjectId;

                // If project is removed, also disable autoExport
                if (empty($slackProjectId)) {
                    $allPreferences[$preferenceName]['autoExport'] = false;
                }

                $this->settingRepo->saveSetting($settingKey, json_encode($allPreferences));

                return $this->jsonResponse(['status' => 'success']);
            }
            else {
                return $this->jsonResponse([
                    'status' => 'error',
                    'message' => 'Invalid action'
                ], 400);
            }

        } catch (\Exception $e) {

            return $this->jsonResponse([
                'status' => 'error',
                'message' => 'Failed to process request'
            ], 500);
        }
    }

    private function jsonResponse(array $data, int $statusCode = 200): JsonResponse
    {
        return new JsonResponse($data, $statusCode);
    }
}
