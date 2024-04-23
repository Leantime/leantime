<?php

namespace Leantime\Command;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Domain\Clients\Repositories\Clients;
use Leantime\Domain\Users\Repositories\Users;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Leantime\Domain\Auth\Models\Roles;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Attribute\AsCommand;

/**
 * Class AddUserCommand
 *
 * This command adds a new user.
 */
#[AsCommand(
    name: 'user:add',
    description: 'Add a new user',
)]
class AddUserCommand extends Command
{
    /**
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        $this->addOption(name: 'email', mode: InputOption::VALUE_REQUIRED, description: "User's Email")
            ->addOption(name: 'password', mode: InputOption::VALUE_REQUIRED, description: "User's Password")
            ->addOption(
                'role',
                null,
                InputOption::VALUE_REQUIRED,
                "User's Role",
                array_values(Roles::getRoles()),
            )
            ->addOption(name: 'client-id', mode: InputOption::VALUE_OPTIONAL, description: "Id of The Client to Assign the User To")
            ->addOption(name: 'first-name', mode: InputOption::VALUE_OPTIONAL, description: "User's First name")
            ->addOption(name: 'last-name', mode: InputOption::VALUE_OPTIONAL, description: "User's Last Name")
            ->addOption(name: 'phone', mode: InputOption::VALUE_OPTIONAL, description: "User's Phone");
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int 0 if everything went fine, or an exit code.
     *
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        !defined('BASE_URL') && define('BASE_URL', "");
        !defined('CURRENT_URL') && define('CURRENT_URL', "");

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

        if (!in_array($role, array_values(Roles::getRoles()))) {
            $io->error("Role is Invalid");
            return Command::INVALID;
        }

        $clientId = $input->getOption('client-id');
        if ($clientId === null) {
            $clientsRepository = app()->make(Clients::class);
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
            "role" => array_search($role, Roles::getRoles()),
            "clientId" => $clientId,
            "firstname" => $firstName,
            "lastname" => $lastName,
            "phone" => $phone,
            "status" => "A"
        );

        try {
            $usersRepo = app()->make(Users::class);

            if ($usersRepo->usernameExist($email)) {
                $io->error("User Already Exists");
                return Command::INVALID;
            }

            $userId = $usersRepo->addUser($user);
            if (!$userId) {
                $io->error("Failed to Add User");
                return Command::FAILURE;
            }
        } catch (\Exception $ex) {
            $io->error($ex);

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
