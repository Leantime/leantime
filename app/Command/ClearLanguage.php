<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
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
    name: 'language:clear',
    description: 'Backs up database',
)]
class ClearLanguage extends Command
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
        $language = app()->make(Language::class);

        $io = new SymfonyStyle($input, $output);

        $io->section('Clearing Language Cache');

        $langList = $language->getLanguageList();

        if ($langList) {
            foreach ($langList as $key => $lang) {
                Cache::store('installation')->
                $result = Cache::store('installation')->forget('languages.lang_'.$key);
                if ($result) {
                    $this->components->info('Cleared: '.$key);
                } else {
                    $this->components->warn('Failed to clear: '.$key);
                }

            }
        }

        $this->components->info('cleared language file cache');

        return Command::SUCCESS;
    }
}
