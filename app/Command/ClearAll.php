<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\Environment;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class BackupDbCommand
 *
 * Command to back up the database.
 *
 * Usage:
 *   php bin/console db:backup
 */
#[AsCommand(
    name: 'cache:clearAll',
    description: 'Backs up database',
)]
class ClearAll extends Command
{
    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * Execute the command
     *
     *
     * @return int 0 if everything went fine, or an exit code.
     *
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $config = app()->make(Environment::class);

        $io = new SymfonyStyle($input, $output);

        $io->section('Clearing all caches');

        $io->text('Clear Views');
        $this->call('view:clear');

        $io->text('Clear Installation Cache');
        Cache::store('installation')->forget('languages.lang_en-US');
        $this->components->info('cleared language file cache');

        //$this->call("cache:clear", ["installation"]);

        $io->text('Clear Bootstrap Cache');
        $commandOut = exec('rm -rf ./storage/framework/composerPaths.php');
        $this->components->info('composerPaths removed');
        $commandOut = exec('rm -rf ./storage/framework/viewPaths.php');
        $this->components->info('viewpaths removed');
        $commandOut = exec('rm -rf ./bootstrap/cache/*.php');
        $this->components->info('bootstrap cache cleared');

        $io->success('All Caches cleared');

        return Command::SUCCESS;
    }
}
