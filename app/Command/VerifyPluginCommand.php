<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Checks that an installed plugin loads: includes its phar (if any) and register.php in a process
 * where no other user plugin is loaded, and reports any error.
 *
 * Run by the plugin updater in a fresh process right after an update (the running process still
 * holds the previous version's phar), which rolls the update back when this fails. The console
 * kernel does not preload user plugins for this command, so the result is this plugin's alone.
 */
#[AsCommand(
    name: 'plugin:verify',
    description: 'Checks that an installed plugin loads without errors',
)]
class VerifyPluginCommand extends Command
{
    /**
     * Declares the plugin folder argument and the --json option.
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addArgument('folder', InputArgument::REQUIRED, 'The plugin folder name, e.g. Notes');
        $this->addOption('json', null, InputOption::VALUE_NONE, 'Print one JSON line: {ok, error?, composer?} (used by the updater)');
    }

    /**
     * Loads the plugin's phar, register.php and routes.php and reports whether that worked.
     *
     * @return int 0 when the plugin loads, 1 otherwise.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $folder = (string) $input->getArgument('folder');
        $asJson = (bool) $input->getOption('json');

        if (preg_match('/^[A-Za-z0-9_-]+$/', $folder) !== 1) {
            return $this->failWith($output, $asJson, 'Invalid plugin folder name');
        }

        $pluginPath = APP_ROOT.'/app/Plugins/'.$folder;
        $pharPath = "phar://{$pluginPath}/{$folder}.phar";

        try {
            if (! is_dir($pluginPath)) {
                throw new \RuntimeException('Plugin folder '.$folder.' does not exist');
            }

            $base = $pluginPath;
            if (file_exists("{$pluginPath}/{$folder}.phar")) {
                include_once $pharPath;
                $base = $pharPath;
            }

            // The two files core loads from every enabled plugin: its listeners and its routes.
            foreach (['register.php', 'routes.php'] as $entryFile) {
                if (file_exists("{$base}/{$entryFile}")) {
                    include_once "{$base}/{$entryFile}";
                }
            }

            $composer = file_exists("{$base}/composer.json")
                ? json_decode((string) file_get_contents("{$base}/composer.json"), true)
                : null;
        } catch (\Throwable $e) {
            return $this->failWith($output, $asJson, $e->getMessage().' ('.$e->getFile().':'.$e->getLine().')');
        }

        if ($asJson) {
            $output->writeln((string) json_encode(['ok' => true, 'composer' => is_array($composer) ? $composer : null]));
        } else {
            $output->writeln($folder.' loads');
        }

        return self::SUCCESS;
    }

    /**
     * Prints a failure (plain or as the JSON result line) and returns the failure exit code.
     */
    private function failWith(OutputInterface $output, bool $asJson, string $error): int
    {
        $output->writeln($asJson ? (string) json_encode(['ok' => false, 'error' => $error]) : '<error>'.$error.'</error>');

        return self::FAILURE;
    }
}
