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
        
                $allUserProfiles = $slackReportService->getAllProfilesWithEnabledAutoExport();


            if (empty($allUserProfiles)) {
                $io->warning('No profiles with auto-export enabled found.');
                return Command::SUCCESS;
            }

            $totalUsers = count($allUserProfiles);
            $io->success("Found {$totalUsers} user(s) with enabled profiles.");

        foreach ($allUserProfiles as $userProfile) {
            $userId = $userProfile['user_id'];
            $userName = $userProfile['user_name'];
            $profiles = $userProfile['profiles'];
            
            $profileCount = count($profiles);
            $io->writeln("Processing {$profileCount} profile(s) for {$userName} (ID: {$userId})...");
                        $io->writeln('DEBUG: Profiles for user ' . $userId . ': ' . json_encode($profiles, JSON_PRETTY_PRINT));

            
            $slackReportService->sendMonthlyReportToSlack($profiles);
        }

        $io->success('✓ Monthly reports sent successfully!');
        
        return Command::SUCCESS;

    } catch (\Exception $e) {
        $io->error('✗ Error: ' . $e->getMessage());
        
        return Command::FAILURE;
    }
}
}