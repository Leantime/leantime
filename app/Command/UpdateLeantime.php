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
use Symfony\Component\Console\Input\ArrayInput;

use function PHPUnit\Framework\directoryExists;


#[AsCommand(
    name: 'system:update',
    description: 'Updates the system',
)]
class UpdateLeantime extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addOption(name:"skipDbBackup", mode:InputOption::VALUE_NONE);
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
        $appSettings = app()->make(AppSettings::class);

        $io = new SymfonyStyle($input, $output);


        $date = new \DateTime();
        $backupFile = $config->dbDatabase . '_' .  $date->format("Y-m-d") . '.sql';
        $backupPath = APP_ROOT."/".$config->dbBackupPath . $backupFile;

        $currentVersion = $appSettings->appVersion;
        $io->text("Starting the updater");
        $io->text("Your current version is: v".$currentVersion."");

        //Get Latest Version
        $url = 'https://github.com/leantime/leantime/releases/latest';

        $io->text("Checking latest version on Github...");
        //Create stream context to follow redirects
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Accept: application/json',
                    'follow_location' => true
                )
            )
        );

        //Use file_get_contents() with HTTP context to fetch url
        $result = file_get_contents($url, false, $context);

        $jsonResponse= json_decode($result, true);

        $latestVersion = $jsonResponse['tag_name'] ?? null;


        $io->text("The current Leantime version is: ".$latestVersion);

        //Build download URL
        if("v".$currentVersion == $latestVersion){
            $io->text("You are on the most up to date version");
            //return Command::SUCCESS;
        }

        $skipBackup = $input->getOption("skipDbBackup");

        if($skipBackup === false) {
            $backUp = new ArrayInput([
                // the command name is passed as first argument
                'command' => 'db:backup',
            ]);

            $return = $this->getApplication()->doRun($backUp, $output);
        }

        //Build download URL
        $io->text("Downloading latest version...");
        $downloadUrl = "https://github.com/leantime/leantime/releases/download/".$latestVersion."/Leantime-".$latestVersion.".zip";
        $file = file_get_contents($downloadUrl);

        $zipFile = APP_ROOT."/cache/latest.zip";
        file_put_contents($zipFile, $file);

        $io->text("Extracting Archive...");

        $zip = new \ZipArchive();
        $res = $zip->open($zipFile);
        $zip->extractTo( APP_ROOT."/cache/");
        $zip->close();

        exec("cp -r ".APP_ROOT."/cache/leantime/* ".APP_ROOT."/");

        $io->text("Clean Up");
        rmdir(APP_ROOT."/cache/leantime");

        $io->success("Update applied Successfully");

        return Command::SUCCESS;
















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
