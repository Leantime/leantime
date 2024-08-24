<?php

namespace Leantime\Command;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\AppSettings;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class UpdateLeantime
 */
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
        $this->addOption(name: "skipDbBackup", mode: InputOption::VALUE_NONE);
    }

    /**
     * Executes the update process.
     *
     * @param InputInterface  $input  The input interface object.
     * @param OutputInterface $output The output interface object.
     *
     * @return int 0 if everything went fine, or an exit code.
     *
     * @throws BindingResolutionException|\Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appSettings = app()->make(AppSettings::class);

        $io = new SymfonyStyle($input, $output);

        $currentVersion = $appSettings->appVersion;
        $io->text("Starting the updater");
        $io->text("Your current version is: v" . $currentVersion);

        // Get Latest Version
        $url = 'https://github.com/leantime/leantime/releases/latest';
        $io->text("Checking latest version on Github...");

        // Create stream context to follow redirects
        $context = stream_context_create(
            array(
                'http' => array(
                    'method' => 'GET',
                    'header' => 'Accept: application/json',
                    'follow_location' => true,
                ),
            ),
        );

        // Use file_get_contents() with HTTP context to fetch url
        $result = file_get_contents($url, false, $context);
        $jsonResponse = json_decode($result, true);
        $latestVersion = $jsonResponse['tag_name'] ?? null;

        $io->text("The current Leantime version is: " . $latestVersion);

        // Build download URL
        if ("v" . $currentVersion == $latestVersion) {
            $io->text("You are on the most up to date version");
        }

        $skipBackup = $input->getOption("skipDbBackup");

        if ($skipBackup === false) {
            $backUp = new ArrayInput([
                // The command name is passed as first argument
                'command' => 'db:backup',
            ]);

            $this->getApplication()->doRun($backUp, $output);
        }

        // Build download URL
        $io->text("Downloading latest version...");
        $downloadUrl = "https://github.com/leantime/leantime/releases/download/" . $latestVersion . "/Leantime-" . $latestVersion . ".zip";
        $file = file_get_contents($downloadUrl);

        $zipFile = APP_ROOT . "/cache/latest.zip";
        file_put_contents($zipFile, $file);

        $io->text("Extracting Archive...");

        $zip = new \ZipArchive();
        $zip->open($zipFile);
        $zip->extractTo(APP_ROOT . "/cache/");
        $zip->close();

        exec("cp -r " . APP_ROOT . "/cache/leantime/* " . APP_ROOT . "/");

        $io->text("Clean Up");
        rmdir(APP_ROOT . "/cache/leantime");

        $io->success("Update applied Successfully");

        return Command::SUCCESS;
    }
}
