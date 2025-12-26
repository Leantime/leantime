<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Timesheets\Services\SlackMonthlyReportService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'reports:send-monthly',
    description: 'Send monthly Slack reports for profiles with auto-export enabled',
)]
class SendMonthlyReportsCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ! defined('BASE_URL') && define('BASE_URL', '');
        ! defined('CURRENT_URL') && define('CURRENT_URL', '');

        $io = new SymfonyStyle($input, $output);

        try {
            $io->writeln('[' . date('Y-m-d H:i:s') . '] Starting monthly reports job...');

            $slackReportService = app()->make(SlackMonthlyReportService::class);

            $io->writeln('Fetching profiles with auto-export enabled...');
            $profilesWithEnabledAutoExport = $slackReportService->getAllProfiles();

            if (empty($profilesWithEnabledAutoExport)) {
                $io->warning('No profiles with auto-export enabled found.');
                return Command::SUCCESS;
            }

            $userCount = count($profilesWithEnabledAutoExport);
            $io->success("Found {$userCount} user(s) with enabled profiles.");

            $io->writeln('Sending reports to Slack...');
            $slackReportService->sendAutomaticMonthlyReportToSlack($profilesWithEnabledAutoExport);

            $io->success('✓ Monthly reports sent successfully!');
            
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('✗ Error: ' . $e->getMessage());
            error_log('Monthly reports command failed: ' . $e->getMessage());
            error_log($e->getTraceAsString());
            
            return Command::FAILURE;
        }
    }
}