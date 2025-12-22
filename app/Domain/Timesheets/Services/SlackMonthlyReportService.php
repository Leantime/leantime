<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;


class SlackMonthlyReportService {
    private $webhookUrl;
    private TimesheetService $timesheetsService;

    public function __construct() {
        $this->$webhookUrl = env('SLACK_WEBHOOK_URL');
        $this->timesheetService = $timesheetsService;
    }

    public function exportCsv(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);

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
                format_hours($row['hours'], $hoursFormat),
                format_hours($row['planHours'], $hoursFormat),
                format_hours($diff, $hoursFormat),
                $row['headline'] ?? '',
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
    

}
