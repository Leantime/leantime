<?php

namespace Leantime\Command;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Help\Services\Helper;
use Leantime\Domain\Install\Repositories\Install;
use Leantime\Domain\Users\Repositories\Users;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Represents the MigrateCommand class.
 */
#[AsCommand(
    name: 'db:migrate',
    description: 'Runs and pending Leantime Database Migrations',
)]
class MigrateCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->setAliases(['db:install', 'db:update']);

        $this->addOption('email', null, InputOption::VALUE_OPTIONAL, "User's Email", null)
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, "User's Password", null)
            ->addOption('company-name', null, InputOption::VALUE_OPTIONAL, 'Company Name', null)
            ->addOption('first-name', null, InputOption::VALUE_OPTIONAL, "User's First name", null)
            ->addOption('last-name', null, InputOption::VALUE_OPTIONAL, "User's Last Name", null);
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

        $install = app()->make(Install::class);
        $io = new SymfonyStyle($input, $output);
        $silent = $input->getOption('silent') === 'true';
        try {
            if (! $install->checkIfInstalled()) {
                if ($silent) {
                    $adminEmail = 'admin@leantime.io';
                    $setupConfig = [
                        'email' => 'admin@leantime.io',
                        'password' => '',
                        'firstname' => '',
                        'lastname' => '',
                        'company' => 'Leantime',
                    ];
                } else {
                    $email = $input->getOption('email');
                    $password = $input->getOption('password');
                    $companyName = $input->getOption('company-name');
                    $firstName = $input->getOption('first-name');
                    $lastName = $input->getOption('last-name');

                    $adminEmail = $email ?? $io->ask('Admin Email');
                    $adminPassword = $password ?? $io->askHidden('Admin Password');
                    $adminFirstName = $firstName ?? $io->ask('Admin First Name');
                    $adminLastName = $lastName ?? $io->ask('Admin Last Name');
                    $companyName = $companyName ?? $io->ask('Company Name');

                    $setupConfig = [
                        'email' => $adminEmail,
                        'password' => $adminPassword,
                        'firstname' => $adminFirstName,
                        'lastname' => $adminLastName,
                        'company' => $companyName,
                    ];
                }

                $io->text('Installing DB For First Time');
                $installStatus = $install->setupDB($setupConfig);

                if ($installStatus !== true) {
                    $io->text($installStatus);

                    return Command::FAILURE;
                }

                // Latest install via UI sends user to account set up flow.
                // Turning this off for console installs
                $usersRepo = app()->make(Users::class);
                $getAdminUser = $usersRepo->getUserByEmail($adminEmail, '');
                if ($getAdminUser !== false && is_array($getAdminUser)) {
                    $userId = $getAdminUser['id'];
                    $usersRepo->patchUser($userId, ['password' => $adminPassword, 'status' => 'a']);

                    $helperService = app()->make(Helper::class);
                }

                if ($silent) {
                    $usersRepo = app()->make(Users::class);
                    $userId = array_values($usersRepo->getUserByEmail($adminEmail))[0];
                    $usersRepo->deleteUser($userId);
                }

                $io->text('Successfully Installed DB');
            }
            $success = $install->updateDB();
            if ($success !== true) {
                throw new Exception('Migration Failed; See below'.PHP_EOL.implode(PHP_EOL, $success));
            }
        } catch (Exception $ex) {
            $io->error($ex);

            return Command::FAILURE;
        }

        $io->success('Database Successfully Migrated');

        return Command::SUCCESS;
    }
}
