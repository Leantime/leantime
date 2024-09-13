<?php

namespace Leantime\Command;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Setting\Repositories\Setting;
use Symfony\Component\Console\Attribute\AsCommand;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SaveSettingCommand
 *
 * Command for saving a setting, will create it if it doesn't exist.
 */
#[AsCommand(
    name: 'setting:save',
    description: 'Saves a setting, will create it if it does not exist',
)]
class SaveSettingCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();
        $this->addOption('key', null, InputOption::VALUE_REQUIRED, 'Setting Key')
            ->addOption('value', null, InputOption::VALUE_REQUIRED, 'Setting Value');
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
        ! defined('BASE_URL') && define('BASE_URL', '');
        ! defined('CURRENT_URL') && define('CURRENT_URL', '');

        $io = new SymfonyStyle($input, $output);
        $key = $input->getOption('key');
        $value = $input->getOption('value');

        if ($key == '') {
            $io->error('key parameter needs to be set');

            return Command::INVALID;
        }

        if ($value == '') {
            $io->error('value parameter needs to be set');

            return Command::INVALID;
        }

        try {
            $setting = app()->make(Setting::class);
            $result = $setting->saveSetting($key, $value);

            if (! $result) {
                $io->error('Failed to save setting');

                return Command::FAILURE;
            }
        } catch (\Exception $ex) {
            $io->error($ex);

            return Command::FAILURE;
        }

        $io->success('Saved Successfully');

        return Command::SUCCESS;
    }
}
