<?php

namespace Leantime\Command;

use Illuminate\Contracts\Container\BindingResolutionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Leantime\Domain\Install\Repositories\Install;
use Exception;
use Leantime\Domain\Users\Repositories\Users;
use Symfony\Component\Console\Attribute\AsCommand;
use Aws\S3\Exception\S3Exception;
use Aws\S3;
use Leantime\Core\AppSettings;
use Leantime\Core\Environment;

use function PHPUnit\Framework\directoryExists;


#[AsCommand(
    name: 'db:backup',
    description: 'Backs up database',
)]
class backupDbCommand extends Command
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
     * @return int 0 if everything went fine, or an exit code.
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $install = app()->make(Install::class);
        $config = app()->make(Environment::class);

        $io = new SymfonyStyle($input, $output);


        $date = new \DateTime();
        $backupFile = $config->dbDatabase . '_' .  $date->format("Y-m-d") . '.sql';
        $backupPath = APP_ROOT."/".$config->dbBackupPath . $backupFile;

        if(!is_dir(APP_ROOT."/".$config->dbBackupPath)){
           $result = mkdir(APP_ROOT."/".$config->dbBackupPath);
        }

        $output = array();
        exec("mysqldump --column-statistics=0 --user={$config->dbUser} --password={$config->dbPassword} --host={$config->dbHost} {$config->dbDatabase} --port={$config->dbPort} --result-file={$backupPath} 2>&1", $output, $worked);

        switch ($worked) {
            case 0:
                chmod(APP_ROOT . '/' . $config->userFilePath, 0755);
                $io->text("Success, database was backedup successfully");
                return Command::SUCCESS;
                break;
            case 1:
                $io->text("There was an issue backing up the database");
                return Command::FAILURE;
                break;
            case 2:
                $io->text("There was an issue backing up the database");
                return Command::FAILURE;
                break;
        }

        return Command::FAILURE;

    }
}
