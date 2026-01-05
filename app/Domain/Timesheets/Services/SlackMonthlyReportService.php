<?php

namespace Leantime\Domain\Timesheets\Services;

use Symfony\Component\HttpFoundation\Response;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Leantime\Domain\Users\Services\Users as UserService;

class SlackMonthlyReportService {
    private string $webhookUrl;
    private TimesheetService $timesheetsService;
    private TicketService $ticketService;
    private SettingRepository $settingRepository;
    private UserService $userService;
    private \GuzzleHttp\Client $httpClient;

public function __construct(
    TimesheetService $timesheetsService,
    TicketService $ticketService,
    SettingRepository $settingRepository,
    \GuzzleHttp\Client $httpClient,
    UserService $userService
) {
    $this->webhookUrl = env('SLACK_WEBHOOK_URL', '');
    $this->timesheetsService = $timesheetsService;
    $this->ticketService = $ticketService;
    $this->settingRepository = $settingRepository;
    $this->httpClient = $httpClient;
    $this->userService = $userService;
}

    private function getFiltersFromRequest(): array
    {
        $kind = !empty($_POST['kind']) ? strip_tags($_POST['kind']) : 'all';
        $userId = !empty($_POST['userId']) ? intval(strip_tags($_POST['userId'])) : null;

        $dateFrom = dtHelper()->userNow()->startOfMonth()->setToDbTimezone();
        if (!empty($_POST['dateFrom'])) {
            $dateFrom = dtHelper()->parseUserDateTime($_POST['dateFrom'])->setToDbTimezone();
        }

        $dateTo = dtHelper()->userNow()->endOfMonth()->setToDbTimezone();
        if (!empty($_POST['dateTo'])) {
            $dateTo = dtHelper()->parseUserDateTime($_POST['dateTo'])->setToDbTimezone();
        }

        $invEmplCheck = (isset($_POST['invEmpl']) && $_POST['invEmpl'] === '1') ? '1' : '-1';
        $invCompCheck = (isset($_POST['invComp']) && $_POST['invComp'] == 'on') ? '1' : '0';
        $paidCheck = (isset($_POST['paid']) && $_POST['paid'] == 'on') ? '1' : '0';

        $projectFilter = -1;
        if (!empty($_POST['project'])) {
            $selectedProjects = $_POST['project'];
            if (is_array($selectedProjects)) {
                $selectedProjects = array_map(static fn ($value) => (int) $value, $selectedProjects);
                $selectedProjects = array_values(array_filter($selectedProjects, static fn ($value) => $value !== 0));
                $projectFilter = (in_array(-1, $selectedProjects, true) || empty($selectedProjects)) ? -1 : $selectedProjects;
            } else {
                $projectFilter = (int) strip_tags($selectedProjects);
            }
        }

        $ticketFilter = !empty($_POST['ticket']) ? (int) strip_tags($_POST['ticket']) : -1;
        $clientId = !empty($_POST['clientId']) ? (int) strip_tags($_POST['clientId']) : -1;

        $projectMismatch = false;
        if ($ticketFilter > 0) {
            $selectedTicket = $this->ticketService->getTicket($ticketFilter);
            if ($selectedTicket) {
                if (is_array($projectFilter)) {
                    $projectMismatch = !in_array((int) $selectedTicket->projectId, $projectFilter, true);
                } elseif ($selectedTicket->projectId != $projectFilter) {
                    $projectMismatch = true;
                }
            }
        }

        $resolvedProjectFilter = is_array($projectFilter) ? $projectFilter : (int) $projectFilter;
        $ticketParameter = (!$projectMismatch && !is_array($projectFilter) && $projectFilter != -1) ? ($ticketFilter ?: '-1') : '-1';

        return [
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'projectFilter' => $resolvedProjectFilter,
            'kind' => $kind,
            'userId' => $userId,
            'invEmplCheck' => $invEmplCheck,
            'invCompCheck' => $invCompCheck,
            'ticketParameter' => $ticketParameter,
            'paidCheck' => $paidCheck,
            'clientId' => $clientId
        ];
    }

    public function getUsersProfilesWithEnabledAutoExport(int $userId): array 
{
    $settingKey = "user.{$userId}.timesheetFilters";
    $preferences = $this->settingRepository->getSetting($settingKey);
    
    if (!$preferences) {
        return [];
    }
    
    if (is_string($preferences)) {
        $preferences = json_decode($preferences, true);
    }
    
    if (!is_array($preferences)) {
        return [];
    }
    
    $autoExportProfiles = [];
    foreach ($preferences as $name => $profile) {
        if (isset($profile['autoExport']) && $profile['autoExport'] === true) {
            $autoExportProfiles[$name] = $profile;
        }
    }
    error_log(print_r($autoExportProfiles, true));
    return $autoExportProfiles;
}

public function getAllProfilesWithEnabledAutoExport (): array {
    $allUsers = $this->userService->getAll();
    $allProfiles = [];

    foreach ($allUsers as $user) {
        $userId = $user['id'];
        $profiles = $this->getUsersProfilesWithEnabledAutoExport($userId);

        if (!empty($profiles)) {
            $allProfiles[] = [
                'user_id' => $userId,
                'user_name' => $user['firstname'] . ' ' . $user['lastname'],
                'profiles' => $profiles
            ];
        }
    }
    return $allProfiles;
}

public function getAllUsersProfiles(int $userId): array {
    $settingKey = "user.{$userId}.timesheetFilters";
    $preferences = $this->settingRepository->getSetting($settingKey);
    
    if (!$preferences) {
        return [];
    }
    
    if (is_string($preferences)) {
        $preferences = json_decode($preferences, true);
    }
    
    if (!is_array($preferences)) {
        return [];
    }
    
    return $preferences;
}

public function getAllProfiles(): array {
    $allUsers = $this->userService->getAll();
    $allProfiles = [];

    foreach ($allUsers as $user) {
        $userId = $user['id'];
        $profiles = $this->getAllUsersProfiles($userId);
        
        if (!empty($profiles)) {
            $allProfiles[] = [
                'user_id' => $userId,
                'user_name' => $user['firstname'] . ' ' . $user['lastname'], 
                'profiles' => $profiles
            ];
        }
    }
    return $allProfiles;
}

   public function sendMonthlyReportToSlack($profilesWithEnabledAutoExport): void
{
    foreach ($profilesWithEnabledAutoExport as $profileName => $profile) {
        $profileFilters = $profile['filters'] ?? [];
        $userId = $profileFilters['userId'] ?? null;
        if ($userId === 'all' || $userId === '') {
            $userId = null; 
        } elseif ($userId !== null) {
            $userId = (int)$userId;  
        }
        $filters = [
            'dateFrom' => isset($profileFilters['dateFrom']) ? dtHelper()->parseUserDateTime($profileFilters['dateFrom'])->setToDbTimezone() : dtHelper()->userNow()->startOfMonth()->setToDbTimezone(),
            'dateTo' => isset($profileFilters['dateTo']) ? dtHelper()->parseUserDateTime($profileFilters['dateTo'])->setToDbTimezone() : dtHelper()->userNow()->endOfMonth()->setToDbTimezone(),
            'projectFilter' => $profileFilters['projects'] ?? -1,
            'kind' => $profileFilters['kind'] ?? 'all',
            'userId' => $userId,
            'invEmplCheck' => $profileFilters['invEmpl'] ?? '-1',
            'invCompCheck' => $profileFilters['invComp'] ?? '0',
            'ticketParameter' => $profileFilters['ticketParameter'] ?? '-1',
            'paidCheck' => $profileFilters['paid'] ?? '0',
            'clientId' => $profileFilters['clientId'] ?? -1,
        ];

        $columnState = $profileFilters['columnState'] ?? [];
        $reportName = $profile['name'] ?? " ";

        $csvContent = $this->generateCsvString($filters, $columnState);

        $this->sendCsvToSlack($csvContent, $reportName);
    }
}

public function sendAutomaticMonthlyReportToSlack($profilesWithEnabledAutoExport): void
{
    foreach ($profilesWithEnabledAutoExport as $userProfileData) {
        $userId = $userProfileData['user_id'];
        $userName = $userProfileData['user_name'];
        
        foreach ($userProfileData['profiles'] as $profile) {
            $filters = [
                'dateFrom' => isset($profile['dateFrom']) 
                    ? dtHelper()->parseUserDateTime($profile['dateFrom'])->setToDbTimezone() 
                    : dtHelper()->userNow()->startOfMonth()->setToDbTimezone(),
                'dateTo' => isset($profile['dateTo']) 
                    ? dtHelper()->parseUserDateTime($profile['dateTo'])->setToDbTimezone() 
                    : dtHelper()->userNow()->endOfMonth()->setToDbTimezone(),
                'projectFilter' => $profile['projectFilter'] ?? -1,
                'kind' => $profile['kind'] ?? 'all',
                'userId' => $profile['userId'] ?? null,
                'invEmplCheck' => $profile['invEmplCheck'] ?? '-1',
                'invCompCheck' => $profile['invCompCheck'] ?? '0',
                'ticketParameter' => $profile['ticketParameter'] ?? '-1',
                'paidCheck' => $profile['paidCheck'] ?? '0',
                'clientId' => $profile['clientId'] ?? -1,
            ];

            $columnState = $profile['filters']['columnState'] ?? [];
            $reportName = $profile['name'] ?? " ";
            
            $csvContent = $this->generateCsvString($filters, $columnState);

            $this->sendCsvToSlack($csvContent, $userName);
        }
    }
}

private function sendCsvToSlack(string $csvContent, string $profileName): bool
{
    $slackBotToken = env('SLACK_BOT_TOKEN', '');
    
    if (empty($slackBotToken)) {
        return false;
    }

    $channelId = env('SLACK_CHANNEL_ID', '');
    
    if (empty($channelId)) {
        return false;
    }
    
    try {
        $filename = "timesheet_{$profileName}_" . date('Y-m-d') . ".csv";
                
        $getUploadUrlResponse = $this->httpClient->post('https://slack.com/api/files.getUploadURLExternal', [
            'headers' => [
                'Authorization' => "Bearer {$slackBotToken}",
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'filename' => $filename,
                'length' => strlen($csvContent)
            ]
        ]);

        $uploadUrlData = json_decode($getUploadUrlResponse->getBody()->getContents(), true);
        
        if (!isset($uploadUrlData['ok']) || !$uploadUrlData['ok']) {
            return false;
        }

        $uploadUrl = $uploadUrlData['upload_url'];
        $fileId = $uploadUrlData['file_id'];
                
        $uploadResponse = $this->httpClient->post($uploadUrl, [
            'body' => $csvContent,
            'headers' => [
                'Content-Type' => 'text/csv',
            ]
        ]);
        
        if ($uploadResponse->getStatusCode() !== 200) {
            return false;
        }
        
        $completeResponse = $this->httpClient->post('https://slack.com/api/files.completeUploadExternal', [
            'headers' => [
                'Authorization' => "Bearer {$slackBotToken}",
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'files' => [
                    [
                        'id' => $fileId,
                        'title' => "Timesheet Export - {$profileName}"
                    ]
                ],
                'channel_id' => $channelId,
                'initial_comment' => "Monthly Timesheet Report: {$profileName}"
            ]
        ]);

        $completeData = json_decode($completeResponse->getBody()->getContents(), true);
        
        if (isset($completeData['ok']) && $completeData['ok']) {
            return true;
        } else {
            return false;
        }
        
    } catch (\GuzzleHttp\Exception\GuzzleException $e) {
        report($e);
        return false;
    }
}

private function generateCsvString(array $filters, array $columnState = []): string
{
    $allTimesheets = $this->timesheetsService->getAll(
        $filters['dateFrom'],
        $filters['dateTo'],
        $filters['projectFilter'],
        $filters['kind'],
        $filters['userId'],
        $filters['invEmplCheck'],
        $filters['invCompCheck'],
        $filters['ticketParameter'],
        $filters['paidCheck'],
        $filters['clientId']
    );

    $userId = $filters['userId'] ?? session('userdata.id');
    $hoursFormat = 'decimal';
    if ($userId) {
        $settingsService = app()->make(\Leantime\Domain\Setting\Services\Setting::class);
        $hoursFormat = $settingsService->getSetting('usersettings.'.$userId.'.hours_format', 'decimal');
    }

    $hourTypes = $this->timesheetsService->getBookedHourTypes();

    $allColumns = [
        'id' => 'ID',
        'tickId' => 'Ticket ID',
        'date' => 'Date',
        'ticket' => 'Ticket',
        'hours' => 'Hours',
        'planHours' => 'Plan Hours',
        'difference' => 'Difference',
        'project' => 'Project',
        'client' => 'Client',
        'employee' => 'Employee',
        'type' => 'Type',
        'milestone' => 'Milestone',
        'tags' => 'Tags',
        'description' => 'Description',
        'invoiced' => 'Invoiced',
        'mgrApproval' => 'Invoiced Company',
        'paid' => 'Paid'
    ];

    if (empty($columnState)) {
        $columnState = array_fill_keys(array_keys($allColumns), true);
    }

    $activeColumns = array_filter($columnState, fn($value) => $value === true);
    $headers = array_intersect_key($allColumns, $activeColumns);

    $output = fopen('php://temp', 'w+');
    
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
    
    fputcsv($output, array_values($headers));

    foreach ($allTimesheets as $row) {
        $diff = $row['planHours'] - $row['hours'];
        
        $rowData = [
            'id' => $row['id'],
            'tickId' => $row['ticketId'],
            'date' => format($row['workDate'])->date(),
            'ticket' => $row['headline'] ?? '',
            'hours' => format_hours($row['hours'], $hoursFormat),
            'planHours' => format_hours($row['planHours'], $hoursFormat),
            'difference' => format_hours($diff, $hoursFormat),
            'project' => $row['name'] ?? '',
            'client' => $row['clientName'] ?? '',
            'employee' => ($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''),
            'type' => $hourTypes[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? '',
            'milestone' => $row['milestone'] ?? '',
            'tags' => $row['tags'] ?? '',
            'description' => $row['description'] ?? '',
            'invoiced' => $row['invoicedEmpl'] == '1' ? format($row['invoicedEmplDate'])->date() : '',
            'mgrApproval' => $row['invoicedComp'] == '1' ? format($row['invoicedCompDate'])->date() : '',
            'paid' => $row['paid'] == '1' ? format($row['paidDate'])->date() : ''
        ];

        $filteredRow = array_intersect_key($rowData, $activeColumns);
        fputcsv($output, array_values($filteredRow));
    }

    rewind($output);
    $csvContent = stream_get_contents($output);
    fclose($output);

    return $csvContent;
}
}