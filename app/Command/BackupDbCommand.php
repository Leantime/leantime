<?php

namespace Leantime\Command;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function PHPUnit\Framework\directoryExists;

/**
 * Class BackupDbCommand
 *
 * Command to back up the database.
 *
 * Usage:
 *   php bin/console db:backup
 */
#[AsCommand(
    name: 'db:backup',
    description: 'Backs up database',
)]
class BackupDbCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code.
     *
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $config = app()->make(Environment::class);

        $io = new SymfonyStyle($input, $output);


        $date = new \DateTime();
        $backupFile = $config->dbDatabase . '_' .  $date->format("Y-m-d") . '.sql';
        $backupPath = APP_ROOT . "/" . $config->dbBackupPath . $backupFile;

        if (!is_dir(APP_ROOT . "/" . $config->dbBackupPath)) {
            mkdir(APP_ROOT . "/" . $config->dbBackupPath);
        }

        $output = array();
        $cmd = sprintf(
            "mysqldump --column-statistics=0 --user=%s --password=%s --host=%s %s --port=%s --result-file=%s 2>&1",
            $config->dbUser,
            $config->dbPassword,
            $config->dbHost,
            $config->dbDatabase,
            $config->dbPort,
            $backupPath
        );
        exec($cmd, $output, $worked);

        switch ($worked) {
            case 0:
                chmod(APP_ROOT . '/' . $config->userFilePath, 0755);
                $io->success("Success, database was backedup successfully");
                return Command::SUCCESS;

            case 2:
            case 1:
                $io->error("There was an issue backing up the database");
                return Command::FAILURE;
        }

        return Command::FAILURE;
    }
}
