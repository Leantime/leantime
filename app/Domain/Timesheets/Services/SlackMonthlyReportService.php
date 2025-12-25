<?php

namespace Leantime\Domain\Timesheets\Services;

use Symfony\Component\HttpFoundation\Response;
use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Domain\Tickets\Services\Tickets as TicketService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class SlackMonthlyReportService {
    private string $webhookUrl;
    private TimesheetService $timesheetsService;
    private TicketService $ticketService;
    private SettingRepository $settingRepository;

    private \GuzzleHttp\Client $httpClient;

public function __construct(
    TimesheetService $timesheetsService,
    TicketService $ticketService,
    SettingRepository $settingRepository,
    \GuzzleHttp\Client $httpClient
) {
    $this->webhookUrl = env('SLACK_WEBHOOK_URL', '');
    $this->timesheetsService = $timesheetsService;
    $this->ticketService = $ticketService;
    $this->settingRepository = $settingRepository;
    $this->httpClient = $httpClient;
}

    public function exportCsv(): Response
    {        
        $filters = $this->getFiltersFromRequest();
        
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

        $userId = session('userdata.id');
        $hoursFormat = 'decimal';
        if ($userId) {
            $settingsService = app()->make(\Leantime\Domain\Setting\Services\Setting::class);
            $hoursFormat = $settingsService->getSetting('usersettings.'.$userId.'.hours_format', 'decimal');
        }

        $hourTypes = $this->timesheetsService->getBookedHourTypes();

        $output = fopen('php://output', 'w');
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="timesheets_export_' . date('Y-m-d') . '.csv"');
        
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        fputcsv($output, [
            'ID', 'Ticket ID', 'Date', 'Hours', 'Plan Hours', 'Difference',
            'Ticket', 'Project', 'Client', 'Employee', 'Type', 'Milestone',
            'Tags', 'Description', 'Invoiced', 'Invoiced Company', 'Paid'
        ]);

        foreach ($allTimesheets as $row) {
            $diff = $row['planHours'] - $row['hours'];
            
            fputcsv($output, [
                $row['id'],
                $row['ticketId'],
                format($row['workDate'])->date(),
                $row['headline'] ?? '',
                format_hours($row['hours'], $hoursFormat),
                format_hours($row['planHours'], $hoursFormat),
                format_hours($diff, $hoursFormat),
                $row['name'] ?? '',
                $row['clientName'] ?? '',
                ($row['firstname'] ?? '') . ' ' . ($row['lastname'] ?? ''),
                $hourTypes[$row['kind'] ?? 'GENERAL_BILLABLE'] ?? '',
                $row['milestone'] ?? '',
                $row['tags'] ?? '',
                $row['description'] ?? '',
                $row['invoicedEmpl'] == '1' ? format($row['invoicedEmplDate'])->date() : '',
                $row['invoicedComp'] == '1' ? format($row['invoicedCompDate'])->date() : '',
                $row['paid'] == '1' ? format($row['paidDate'])->date() : ''
            ]);
        }

        fclose($output);
        exit();
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

    public function getProfilesWithEnabledAutoExport(int $userId): array 
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
    
    return $autoExportProfiles;
}

   public function sendMonthlyReportToSlack($profilesWithEnabledAutoExport): void
{
    foreach ($profilesWithEnabledAutoExport as $profileName => $profile) {
        $filters = [
            'dateFrom' => isset($profile['dateFrom']) ? dtHelper()->parseUserDateTime($profile['dateFrom'])->setToDbTimezone() : dtHelper()->userNow()->startOfMonth()->setToDbTimezone(),
            'dateTo' => isset($profile['dateTo']) ? dtHelper()->parseUserDateTime($profile['dateTo'])->setToDbTimezone() : dtHelper()->userNow()->endOfMonth()->setToDbTimezone(),
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

        $csvContent = $this->generateCsvString($filters, $columnState);

        $this->sendCsvToSlack($csvContent, $profileName);
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
            error_log("File upload failed with status: " . $uploadResponse->getStatusCode());
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