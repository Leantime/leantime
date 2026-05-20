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
    name: 'mcp:token:revoke',
    description: 'Revoke an MCP bearer token by id',
)]
class RevokeMcpTokenCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('token-id', InputArgument::REQUIRED, 'Token id to revoke')
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Optional expected user email')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Show the token that would be revoked without deleting it')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Do not ask for confirmation');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $tokenId = $input->getArgument('token-id');

        if (! ctype_digit((string) $tokenId) || (int) $tokenId <= 0) {
            $io->error('token-id must be a positive integer.');

            return Command::INVALID;
        }

        $tokenRepository = app()->make(AccessTokenRepository::class);
        $token = $tokenRepository->findTokenById((int) $tokenId);
        if ($token === null) {
            $io->error('Token not found.');

            return Command::FAILURE;
        }

        if ($input->getOption('email')) {
            $usersRepository = app()->make(Users::class);
            $user = $usersRepository->getUserByEmail((string) $input->getOption('email'), 'a');
            if ($user === false || (int) $token['tokenable_id'] !== (int) $user['id']) {
                $io->error('Token does not belong to the provided active user email.');

                return Command::FAILURE;
            }
        }

        $abilities = json_decode((string) ($token['abilities'] ?? '[]'), true);
        if (! is_array($abilities)) {
            $abilities = ['<invalid-json>'];
        }

        $io->definitionList(
            ['Token ID' => (string) $token['id']],
            ['User ID' => (string) $token['tokenable_id']],
            ['Name' => (string) $token['name']],
            ['Abilities' => implode(', ', $abilities)],
            ['Expires At' => (string) ($token['expires_at'] ?? '')],
            ['Last Used At' => (string) ($token['last_used_at'] ?? '')],
        );

        if ($input->getOption('dry-run')) {
            $io->success('Dry run complete. Token was not revoked.');

            return Command::SUCCESS;
        }

        if (! $input->getOption('force') && ! $io->confirm('Revoke this MCP token?', false)) {
            $io->warning('Token revocation cancelled.');

            return Command::SUCCESS;
        }

        if (! $tokenRepository->deleteToken((int) $token['id'])) {
            $io->error('Token could not be revoked.');

            return Command::FAILURE;
        }

        $io->success('MCP token revoked successfully.');

        return Command::SUCCESS;
    }
}
