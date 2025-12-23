<?php

namespace Leantime\Domain\Timesheets\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Timesheets\Services\SlackMonthlyReportService;
use Symfony\Component\HttpFoundation\Response;

class SlackMonthlyReportController extends Controller
{
    private SlackMonthlyReportService $exportService;

    public function __construct(SlackMonthlyReportService $exportService)
    {
        $this->exportService = $exportService;
    }

    public function run(): Response
    {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager], true);
        return $this->exportService->exportCsv();
    }
}