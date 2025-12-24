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

    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);
        return $this->slackReportService->exportCsv();
    }
    public function sendCsvFromProfilesThatHaveTickboxTrue () {
        
        
    }
}