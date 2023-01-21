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
        define('BASE_URL', "");
        define('CURRENT_URL', "");
        $io = new SymfonyStyle($input, $output);
        $email = $input->getOption('email');
        $password = $input->getOption('password');
        $clientId = $input->getOption('client-id');
        $firstName = $input->getOption("first-name");
        $lastName = $input->getOption("last-name");
        $phone = $input->getOption("phone");
        $role = $input->getOption('role');
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error("Email is Invalid");
            return Command::INVALID;
        }
        if (!in_array($role, array_values(roles::getRoles()))) {
            $io->error("Role is Invalid");
            return Command::INVALID;
        }
        if ($clientId === null) {
            $clientsRepository = new clients();
            $clients = $clientsRepository->getAll();
            if (sizeof($clients) < 1) {
                $io->error("No clients found, cannot add user");
                return Command::FAILURE;
            }
            $clientId = $clients[0]["id"];
        }
        $user = array(
            "user" => $email,
            "password" => password_hash($password, PASSWORD_DEFAULT),
            "role" => array_search($role, roles::getRoles()),
            "clientId" => $clientId,
            "firstname" => $firstName,
            "lastname" => $lastName,
            "phone" => $phone
        );
        try {
            $usersRepo = new users();
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
