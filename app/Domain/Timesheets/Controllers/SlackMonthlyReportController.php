<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Services\SlackMonthlyReportService;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

class SlackMonthlyReportController extends Controller
{
    private SlackMonthlyReportService $slackReportService;
    private SettingRepository $settingRepository;

    public function __construct(SlackMonthlyReportService $slackReportService, SettingRepository $settingRepository)
    {
        $this->slackReportService = $slackReportService;
        $this->settingRepository = $settingRepository;
    }

   
public function sendCsvFromProfilesThatHaveTickboxTrue(): Response
{
    $userId = session('userdata.id');
    error_log("Preparing to send Slack monthly report for user ID: {$userId}");
    $allProfiles = $this->slackReportService->getProfilesWithEnabledAutoExport($userId);

    $this->slackReportService->sendMonthlyReportToSlack($allProfiles);

    return new Response('Slack monthly report sent', Response::HTTP_OK);
}
}