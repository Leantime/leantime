<?php

namespace leantime\command;

use leantime\domain\repositories\setting;
use leantime\domain\services\cron;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Output\OutputInterface;
use leantime\domain\models\auth\roles;
use Symfony\Component\Console\Style\SymfonyStyle;

class runCronCommand extends Command
{
    protected static $defaultName = 'cron:run';
    protected static $defaultDescription = 'Runs the cronjob';

    protected function configure()
    {
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $io = new SymfonyStyle($input, $output);

        try {

            $cron = new cron();
            $result = $cron->runCron();

            if (!$result) {
                $io->error("Cron not executed. Not enough time elapsed");
                return Command::FAILURE;
            }

        } catch (Exception $ex) {
            $io->error($ex);
            return Command::FAILURE;
        }

        $io->success("Cron executed successfully");
        return Command::SUCCESS;
    }
}
