<?php

namespace Leantime\Command;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Setting\Repositories\Setting;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 *
 */
class SaveSettingCommand extends Command
{
    protected static $defaultName = 'setting:save';
    protected static $defaultDescription = 'Saves a setting, will create it if it doesn\'t exist';

    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('key', null, InputOption::VALUE_REQUIRED, "Setting Key")
                ->addOption('value', null, InputOption::VALUE_REQUIRED, "Setting Value");
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
        define('BASE_URL', "");
        define('CURRENT_URL', "");
        $io = new SymfonyStyle($input, $output);
        $key = $input->getOption('key');
        $value = $input->getOption('value');

        if ($key == '') {
            $io->error("key parameter needs to be set");
            return Command::INVALID;
        }

        if ($value == '') {
            $io->error("value parameter needs to be set");
            return Command::INVALID;
        }

        try {
            $setting = app()->make(Setting::class);
            $result = $setting->saveSetting($key, $value);

            if (!$result) {
                $io->error("Failed to save setting");
                return Command::FAILURE;
            }
        } catch (\Exception $ex) {
            $io->error($ex);
            return Command::FAILURE;
        }

        $io->success("Saved Successfully");
        return Command::SUCCESS;
    }
}
