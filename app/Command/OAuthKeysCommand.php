<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Leantime\Core\Auth\OAuth\OAuthKeys;
use Leantime\Core\Auth\OAuth\OAuthTokenStore;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * The key pair that signs OAuth access tokens for MCP clients. Nothing needs to be done for it to
 * work: Leantime generates the pair on first use. This shows where the keys come from, rotates
 * them, or prints them for moving to environment-managed keys.
 */
#[AsCommand(
    name: 'oauth:keys',
    description: 'Show, rotate or export the key pair that signs OAuth tokens for MCP clients (Claude, ChatGPT, …).',
)]
class OAuthKeysCommand extends Command
{
    protected function configure(): void
    {
        parent::configure();

        $this->addOption('rotate', null, InputOption::VALUE_NONE, 'Generate a new key pair. Every connected app has to sign in again.')
            ->addOption('force', null, InputOption::VALUE_NONE, 'Rotate without asking for confirmation.')
            ->addOption('export', null, InputOption::VALUE_NONE, 'Print the key pair as LEAN_OAUTH_PRIVATE_KEY / LEAN_OAUTH_PUBLIC_KEY lines.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ! defined('BASE_URL') && define('BASE_URL', '');
        ! defined('CURRENT_URL') && define('CURRENT_URL', '');

        $io = new SymfonyStyle($input, $output);
        $oauthKeys = app(OAuthKeys::class);
        $tokenStore = app(OAuthTokenStore::class);

        try {
            if ($input->getOption('rotate')) {
                return $this->rotate($io, $input, $oauthKeys, $tokenStore);
            }

            if ($input->getOption('export')) {
                $keys = $oauthKeys->keys();
                $output->writeln('LEAN_OAUTH_PRIVATE_KEY="'.str_replace("\n", '\n', trim($keys['private'])).'"');
                $output->writeln('LEAN_OAUTH_PUBLIC_KEY="'.str_replace("\n", '\n', trim($keys['public'])).'"');

                return Command::SUCCESS;
            }

            $status = $oauthKeys->status();
            $io->definitionList(
                ['Source' => $status['source'] === 'environment' ? 'LEAN_OAUTH_PRIVATE_KEY / LEAN_OAUTH_PUBLIC_KEY' : 'database (generated automatically)'],
                ['Fingerprint' => $status['fingerprint']],
                ['Created' => $status['createdAt'] ?? '-'],
                ['Active access tokens' => (string) $tokenStore->countActiveAccessTokens()],
            );

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }
    }

    private function rotate(SymfonyStyle $io, InputInterface $input, OAuthKeys $oauthKeys, OAuthTokenStore $tokenStore): int
    {
        if (! $input->getOption('force')
            && ! $io->confirm('Rotating the key signs every connected app out (Claude, ChatGPT, …). Continue?', false)) {
            $io->writeln('Nothing changed.');

            return Command::SUCCESS;
        }

        $newKeys = $oauthKeys->rotate();
        $revokedTokens = $tokenStore->revokeAll();

        $io->success(sprintf(
            'New key pair %s is active. %d access token(s) revoked; connected apps sign in again on their next request.',
            OAuthKeys::fingerprint($newKeys['public']),
            $revokedTokens
        ));

        return Command::SUCCESS;
    }
}
