<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Users\Repositories\Users;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mcp:token:list',
    description: 'List MCP bearer tokens for a user',
)]
class ListMcpTokensCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('email', InputArgument::REQUIRED, 'User email to list tokens for')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Optional token name filter');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = (string) $input->getArgument('email');

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('A valid user email is required.');

            return Command::INVALID;
        }

        $usersRepository = app()->make(Users::class);
        $user = $usersRepository->getUserByEmail($email, 'a');
        if ($user === false) {
            $io->error('Active user not found for email: '.$email);

            return Command::FAILURE;
        }

        $tokens = app()->make(AccessTokenRepository::class)->getAllTokensByUserId((int) $user['id'], $input->getOption('name')) ?? [];
        if ($tokens === []) {
            $io->warning('No tokens found.');

            return Command::SUCCESS;
        }

        $rows = array_map(static function (array $token): array {
            $abilities = json_decode((string) ($token['abilities'] ?? '[]'), true);
            if (! is_array($abilities)) {
                $abilities = ['<invalid-json>'];
            }

            return [
                'id' => $token['id'],
                'name' => $token['name'],
                'abilities' => implode(', ', $abilities),
                'expires_at' => $token['expires_at'] ?? '',
                'last_used_at' => $token['last_used_at'] ?? '',
                'created_at' => $token['created_at'] ?? '',
            ];
        }, $tokens);

        $io->table(['id', 'name', 'abilities', 'expires_at', 'last_used_at', 'created_at'], $rows);

        return Command::SUCCESS;
    }
}
