<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Leantime\Core\Auth\Permissions\PermissionRegistry;
use Leantime\Core\Auth\Permissions\PermissionRepository;
use Leantime\Core\Auth\Permissions\PermissionSeeder;
use Leantime\Core\Auth\Permissions\PermissionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Discovers every domain/plugin permission declaration and syncs the `domain.action`
 * vocabulary into the database.
 *
 * Usage:
 *   php bin/leantime permissions:sync            # upsert the vocabulary
 *   php bin/leantime permissions:sync --seed     # also (re)grant built-in role defaults
 *   php bin/leantime permissions:sync --prune    # also remove permissions no longer declared
 */
#[AsCommand(
    name: 'permissions:sync',
    description: 'Sync the discovered permission vocabulary into the database',
)]
class SyncPermissionsCommand extends Command
{
    public function __construct(
        private readonly PermissionSeeder $seeder,
        private readonly PermissionRepository $repo,
        private readonly PermissionService $permissions,
        private readonly PermissionRegistry $registry,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this->addOption('seed', null, InputOption::VALUE_NONE, 'Also (re)grant the built-in roles their default permissions');
        $this->addOption('prune', null, InputOption::VALUE_NONE, 'Remove permissions that are no longer declared in code');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Re-glob providers from disk first — the discovered-provider list is cached
        // cross-request outside debug mode, so a stale cache (e.g. predating a newly-shipped
        // domain's permission class) would otherwise hide its permissions from the sync. This
        // also makes the command a reliable recovery step after such a cache goes stale.
        $this->registry->flush();

        $keys = $this->seeder->syncDiscoveredPermissions();
        $io->success(count($keys).' permissions synced.');

        if ($input->getOption('prune')) {
            $pruned = $this->repo->pruneOrphanPermissions($keys);
            $io->info($pruned.' orphaned permission(s) pruned.');
        }

        if ($input->getOption('seed')) {
            $this->seeder->seedBuiltInRoles();
            $io->info('Built-in role defaults seeded.');
        }

        $this->permissions->flushCache();

        return Command::SUCCESS;
    }
}
