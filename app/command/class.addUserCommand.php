<?php

namespace leantime\command;

use leantime\domain\repositories\clients;
use leantime\domain\repositories\users;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Completion\CompletionInput;
use Symfony\Component\Console\Output\OutputInterface;
use leantime\domain\models\auth\roles;
use Symfony\Component\Console\Style\SymfonyStyle;

class addUserCommand extends Command
{
    protected static $defaultName = 'user:add';
    protected static $defaultDescription = 'Add a new User';

    protected function configure()
    {
        parent::configure();
        $this->addOption('email', null, InputOption::VALUE_REQUIRED, "User's Email")
            ->addOption('password', null, InputOption::VALUE_REQUIRED, "User's Password")
            ->addOption(
                'role',
                null,
                InputOption::VALUE_REQUIRED,
                "User's Role",
                function (CompletionInput $input) {
                    return array_values(roles::getRoles());
                }
            )
            ->addOption('client-id', null, InputOption::VALUE_OPTIONAL, "Id of The Client to Assign the User To", null)
            ->addOption('first-name', null, InputOption::VALUE_OPTIONAL, "User's First name", "")
            ->addOption('last-name', null, InputOption::VALUE_OPTIONAL, "User's Last Name", "")
            ->addOption('phone', null, InputOption::VALUE_OPTIONAL, "User's Phone", "");
    }

    /**
     * Execute the command
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @return int 0 if everything went fine, or an exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ! defined('BASE_URL') && define('BASE_URL', "");
        ! defined('CURRENT_URL') && define('CURRENT_URL', "");
        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email');

        if ($email === null) {
            $io->error("Email is Required \"--email\"");
            return Command::INVALID;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error("Email is Invalid");
            return Command::INVALID;
        }

        $password = $input->getOption('password');

        if ($password === null) {
            $io->error("Password is Required \"--password\"");
            return Command::INVALID;
        }

        $role = $input->getOption('role');

        if ($role === null) {
            $io->error("Role is Required \"--role\"");
            return Command::INVALID;
        }

        if (!in_array($role, array_values(roles::getRoles()))) {
            $io->error("Role is Invalid");
            return Command::INVALID;
        }

        $clientId = $input->getOption('client-id');

        if ($clientId === null) {
            $clientsRepository = app()->make(clients::class);
            $clients = $clientsRepository->getAll();
            if (sizeof($clients) < 1) {
                $io->error("No clients found, cannot add user");
                return Command::FAILURE;
            }
            $clientId = $clients[0]["id"];
        }

        $firstName = $input->getOption("first-name");
        $lastName = $input->getOption("last-name");
        $phone = $input->getOption("phone");

        $user = array(
            "user" => $email,
            "password" => $password,
            "role" => array_search($role, roles::getRoles()),
            "clientId" => $clientId,
            "firstname" => $firstName,
            "lastname" => $lastName,
            "phone" => $phone
        );

        try {
            $usersRepo = app()->make(users::class);

            if ($usersRepo->usernameExist($email)) {
                $io->error("User Already Exists");
                return Command::INVALID;
            }

            $userId = $usersRepo->addUser($user);

            if (!$userId) {
                $io->error("Failed to Add User");
                return Command::FAILURE;
            }
        } catch (Exception $ex) {
            $io->error($ex);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
