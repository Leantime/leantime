<?php

namespace Leantime\Command;

use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Leantime\Core\Mcp\Auth\McpAbilityCatalog;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Users\Repositories\Users;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'mcp:token:create',
    description: 'Create a scoped MCP bearer token for a user',
)]
class CreateMcpTokenCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addArgument('email', InputArgument::REQUIRED, 'User email to create the token for')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Token name', 'mcp-agent')
            ->addOption('preset', null, InputOption::VALUE_REQUIRED, 'Ability preset: '.implode(', ', array_keys(McpAbilityCatalog::presets())), 'read-only')
            ->addOption('ability', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Additional explicit abilities')
            ->addOption('expires-days', null, InputOption::VALUE_OPTIONAL, 'Optional expiration in days');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $email = (string) $input->getArgument('email');
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('A valid user email is required.');

            return Command::INVALID;
        }

        $preset = (string) $input->getOption('preset');
        $presetAbilities = McpAbilityCatalog::abilitiesForPreset($preset);
        if ($presetAbilities === []) {
            $io->error('Unknown preset. Available presets: '.implode(', ', array_keys(McpAbilityCatalog::presets())));

            return Command::INVALID;
        }

        $usersRepository = app()->make(Users::class);
        $user = $usersRepository->getUserByEmail($email, 'a');
        if ($user === false) {
            $io->error('Active user not found for email: '.$email);

            return Command::FAILURE;
        }

        $abilities = McpAbilityCatalog::normalize(array_merge(
            $presetAbilities,
            (array) $input->getOption('ability'),
        ));

        $expiresAt = null;
        $expiresDays = $input->getOption('expires-days');
        if ($expiresDays !== null && $expiresDays !== '') {
            if (! ctype_digit((string) $expiresDays) || (int) $expiresDays <= 0) {
                $io->error('--expires-days must be a positive integer.');

                return Command::INVALID;
            }

            $expiresAt = CarbonImmutable::now()->addDays((int) $expiresDays)->toDateTimeString();
        }

        $token = app()->make(AccessTokenRepository::class)->createToken(
            userId: (int) $user['id'],
            name: (string) $input->getOption('name'),
            abilities: $abilities,
            expiresAt: $expiresAt,
        );

        $io->success('MCP token created successfully. Store the plaintext token now; it will not be shown again.');
        $io->listing([
            'userId: '.$user['id'],
            'email: '.$email,
            'tokenId: '.$token['id'],
            'preset: '.$preset,
            'abilities: '.implode(', ', $abilities),
            'expiresAt: '.($expiresAt ?? 'never'),
            'token: '.$token['token'],
        ]);

        return Command::SUCCESS;
    }
}
