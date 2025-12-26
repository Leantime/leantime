<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Services\SlackMonthlyReportService;
use Symfony\Component\HttpFoundation\Response;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Core\Controller\Frontcontroller;


class SlackMonthlyReportController extends Controller
{
    private SlackMonthlyReportService $slackReportService;
    private SettingRepository $settingRepository;

    public function __construct(SlackMonthlyReportService $slackReportService, SettingRepository $settingRepository)
    {
        $this->slackReportService = $slackReportService;
        $this->settingRepository = $settingRepository;
    }

   
public function sendCsvFromAllProfiles(): Response
{
    $userId = session('userdata.id');
    $allProfiles = $this->slackReportService->getAllProfiles($userId);

    $this->slackReportService->sendMonthlyReportToSlack($allProfiles);

    return Frontcontroller::redirect(BASE_URL.'/timesheets/showAll');
}

public function sendCsvFromProfilesThatHaveTickboxTrue(): Response
{
    if(Auth::userIsAtLeast(Roles::$admin)) {
        $userId = session('userdata.id');
        $profilesWithEnabledAutoExport = $this->slackReportService->getProfilesWithEnabledAutoExport($userId);

        $this->slackReportService->sendMonthlyReportToSlack($profilesWithEnabledAutoExport);

        return Frontcontroller::redirect(BASE_URL.'/timesheets/showAll');
    }
    return Frontcontroller::redirect(BASE_URL.'/timesheets/showAll');
}
}