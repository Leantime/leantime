<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Http;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Domain\Plugins\Services\Plugins;
use Symfony\Component\Console\Attribute\AsCommand;
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
    description: 'Updates Leantime to the latest version from Github',
)]
class UpdateLeantime extends Command
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption(name: 'skipDbBackup', mode: InputOption::VALUE_NONE);
    }

    /**
     * Executes the update process.
     *
     * @param  InputInterface  $input  The input interface object.
     * @param  OutputInterface  $output  The output interface object.
     * @return int 0 if everything went fine, or an exit code.
     *
     * @throws BindingResolutionException|\Throwable
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $appSettings = app()->make(AppSettings::class);

        $io = new SymfonyStyle($input, $output);

        $currentVersion = $appSettings->appVersion;
        $io->text('Starting the updater');

        // Check Versions  + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + */
        $io->section('Version Check');
        $io->text('Your current version is: v'.$currentVersion);
        $url = 'https://github.com/leantime/leantime/releases/latest';
        $io->text('Checking latest version on Github...');

        // Create stream context to follow redirects
        $context = stream_context_create(
            [
                'http' => [
                    'method' => 'GET',
                    'header' => 'Accept: application/json',
                    'follow_location' => true,
                ],
            ],
        );

        // Use file_get_contents() with HTTP context to fetch url
        $result = file_get_contents($url, false, $context);
        $jsonResponse = json_decode($result, true);
        $latestVersion = $jsonResponse['tag_name'] ?? null;

        $io->text('The latest Leantime version is: '.$latestVersion);

        // Build download URL
        if (version_compare($currentVersion, ltrim($latestVersion, 'v'), '>=')) {
            $io->success('You are on the most up to date version');

            return self::SUCCESS;
        }

        // Backup DB + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + */
        $io->section('Database Backup');
        $skipBackup = $input->getOption('skipDbBackup');

        if ($skipBackup === false) {
            $backUp = new ArrayInput([
                // The command name is passed as first argument
                'command' => 'db:backup',
            ]);

            $this->getApplication()->doRun($backUp, $output);
        }

        // Download and extract + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + */
        $io->section('Download & Extract');

        $io->text('Downloading latest version...');
        $downloadUrl = 'https://github.com/leantime/leantime/releases/download/'.$latestVersion.'/Leantime-'.$latestVersion.'.zip';
        $zipFile = storage_path('/framework/cache/latest.zip');
        Http::sink($zipFile)->get($downloadUrl);

        $io->text('Extracting Archive...');

        $zip = new \ZipArchive;
        $zip->open($zipFile);
        $zip->extractTo(storage_path('/framework/cache/leantime'));
        $zip->close();

        // Disable Plugins + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + */
        // If we got here everything is ready to go and we just need to move the files.
        // Let's disable plugins
        $io->section('Disabling Plugins');

        /** @var Plugins $plugins */
        $plugins = app()->make(Plugins::class);
        $enabledPlugins = $plugins->getAllPlugins(enabledOnly: true);
        foreach ($enabledPlugins as $plugin) {
            if ($plugin->type != 'system' && isset($plugin->id)) {
                $plugins->disablePlugin($plugin->id);
                $io->text($plugin->name.': Disabled');
            }
        }

        $io->success('Plugins disabled successfully');

        // Apllying Update + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + */
        $io->section('Applying Update');
        exec('cp -r '.storage_path('/framework/cache/leantime').'/* '.APP_ROOT.'/');
        $io->success('Files were updated');

        // Clear Cache + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + */
        $io->section('Clearing Cache');

        exec('rm -rf "'.APP_ROOT.'/bootstrap/cache/*.php"');
        exec('rm -rf "'.APP_ROOT.'/storage/framework/cache/leantime"');
        exec('rm -rf "'.APP_ROOT.'/storage/framework/cache/latest.zip"');
        exec('rm -rf "'.APP_ROOT.'/storage/framework/composerPaths.php"');
        exec('rm -rf "'.APP_ROOT.'/storage/framework/viewPaths.php"');
        exec('rm -rf "'.APP_ROOT.'/storage/framework/cache/*.php"');
        exec('rm -rf "'.APP_ROOT.'/storage/framework/views/*.php"');

        $io->success('Clearing Cache Complete');

        // Enable Plugins + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + + */
        $io->section('Re-enabling Plugins');
        foreach ($enabledPlugins as $plugin) {
            if ($plugin->type != 'system' && isset($plugin->id)) {
                $plugins->enablePlugin($plugin->id);
                $io->text($plugin->name.': Enabled');
            }
        }

        $io->success('Plugins were enabled');

        $io->section('Summary');
        $io->success('Update applied Successfully');

        return Command::SUCCESS;
    }
}
