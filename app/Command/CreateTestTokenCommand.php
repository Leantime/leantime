<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Users\Repositories\Users;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'test:create-token',
    description: 'Mint a Sanctum-style Bearer token for an existing user. Plugin-independent — does not require AdvancedAuth. Intended for CI + local testing of the JSON-RPC surface under Bearer auth.',
)]
class CreateTestTokenCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption('email', null, InputOption::VALUE_REQUIRED, 'Email of an existing user to mint a token for')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Token name (logged on the row, useful for traceability)', 'test-bearer')
            ->addOption('quiet-output', null, InputOption::VALUE_NONE, 'Print only the token (no decoration). Useful for shell capture in CI.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ! defined('BASE_URL') && define('BASE_URL', '');
        ! defined('CURRENT_URL') && define('CURRENT_URL', '');

        $io = new SymfonyStyle($input, $output);

        $email = $input->getOption('email');
        if ($email === null || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('A valid --email is required');

            return Command::INVALID;
        }

        try {
            $usersRepo = app()->make(Users::class);
            $user = $usersRepo->getUserByEmail($email);

            if (! $user || empty($user['id'])) {
                $io->error("No user found with email: {$email}");

                return Command::FAILURE;
            }

            $tokenRepo = app()->make(AccessTokenRepository::class);
            $result = $tokenRepo->createToken((int) $user['id'], (string) $input->getOption('name'));
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        if ($input->getOption('quiet-output')) {
            // Plain token only — pipe-safe.
            $output->write($result['token']);

            return Command::SUCCESS;
        }

        $io->success(sprintf(
            "Bearer token minted for user #%d (%s)\nToken id: %d\nToken:    %s\n\nUse with: Authorization: Bearer %s",
            $user['id'],
            $email,
            $result['id'],
            $result['token'],
            $result['token'],
        ));

        return Command::SUCCESS;
    }
}
