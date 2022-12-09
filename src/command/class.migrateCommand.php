<?php

namespace leantime\command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use leantime\domain\repositories\install;
use Exception;
use leantime\domain\repositories\users;
use array_values;

class migrateCommand extends Command {

    /**
     * The name of the command (the part after "bin/demo").
     *
     * @var string
     */
    protected static $defaultName = 'db:migrate';

    /**
     * The command description shown when running "php bin/demo list".
     *
     * @var string
     */
    protected static $defaultDescription = 'Runs any Pending Database Migrations';

    protected function configure() {
        parent::configure();
        $this->addOption('silent', null, InputOption::VALUE_OPTIONAL, "Silently Handle Migrations. DOES NOT CREATE ADMIN ACCOUNT IF NEEDED", "false");
    }

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {
        define('BASE_URL', "");
        define('CURRENT_URL', "");

        $install = new install();
        $io = new SymfonyStyle($input, $output);
        $silent = $input->getOption("silent") === "true";
        try {
            if (!$install->checkIfInstalled()) {
                $adminEmail = $silent ? "admin@leantime.io" : $io->ask("Admin Email");
                $adminPassword = $silent ? "" : $io->askHidden("Admin Password");
                $adminFirstName = $silent ? "" : $io->ask("Admin First Name");
                $adminLastName = $silent ? "" : $io->ask("Admin Last Name");
                $companyName = $silent ? "Leantime" : $io->ask("Company Name");
                $setupConfig = array(
                    "email" => $adminEmail,
                    "password" => $adminPassword,
                    "firstname" => $adminFirstName,
                    "lastname" => $adminLastName,
                    "company" => $companyName
                );
                $io->text("Installing DB For First Time");
                $installStatus = $install->setupDB($setupConfig);
                if ($installStatus !== true) {
                    $io->text($installStatus);
                    return Command::FAILURE;
                }
                if ($silent) {
                    $usersRepo = new users();
                    $userId = array_values($usersRepo->getUserByEmail($adminEmail))[0];
                    $usersRepo->deleteUser($userId);
                }
                $io->text("Successfully Installed DB");
            }
            $success = $install->updateDB();
            if (!$success) {
                throw new Exception("Migration Failed; Please Check Logs");
            }
        } catch (Exception $ex) {
            $io->error($ex);
            return Command::FAILURE;
        }
        $io->text("Database Successfully Migrated");
        return Command::SUCCESS;
    }

}
