<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Cache;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Clears every Leantime cache: compiled views, language files, bootstrap files, and the
 * per-domain data caches (settings and other cached values).
 *
 * Usage:
 *   php bin/leantime cache:clearAll
 */
#[AsCommand(
    name: 'cache:clearAll',
    description: 'Clear all caches',
)]
class ClearAll extends Command
{
    protected function configure(): void
    {
        parent::configure();
    }

    /**
     * Execute the command
     *
     *
     * @return int 0 if everything went fine, or an exit code.
     *
     * @throws BindingResolutionException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $config = app()->make(Environment::class);

        $io = new SymfonyStyle($input, $output);

        $io->section('Clearing all caches');

        $io->text('Clear Views');
        $this->call('view:clear');

        $io->text('Clear Installation Cache');
        Language::forgetCachedLanguageList();
        $languageList = app()->make(Language::class)->getLanguageList();
        $languageCodes = is_array($languageList) ? array_keys($languageList) : ['en-US'];
        foreach ($languageCodes as $languageCode) {
            Language::forgetCachedLanguage((string) $languageCode);
        }
        $this->components->info('cleared language file cache');

        // $this->call("cache:clear", ["installation"]);

        $io->text('Clear Bootstrap Cache');
        $commandOut = exec('rm -rf ./storage/framework/composerPaths.php');
        $this->components->info('composerPaths removed');
        $commandOut = exec('rm -rf ./storage/framework/viewPaths.php');
        $this->components->info('viewpaths removed');
        $commandOut = exec('rm -rf ./bootstrap/cache/*.php');
        $this->components->info('bootstrap cache cleared');

        // Data caches (settings and more) live in one directory per domain, keyed by app URL.
        // Without LEAN_APP_URL the web uses the request host and the CLI uses localhost, so
        // clearing only the CLI's own store left the site serving stale settings.
        $io->text('Clear Domain Data Caches');
        $cleared = $this->flushDomainCaches(storage_path('framework/cache'));
        $this->components->info("cleared {$cleared} domain data cache(s)");

        $io->success('All Caches cleared');

        return Command::SUCCESS;
    }

    /**
     * Empty the data directory of every per-domain file cache under $cacheRoot.
     *
     * Domain caches are named by get_domain_key() (an md5 hash), so only 32-hex-character
     * directories are touched; avatars, installation and anything else are left alone.
     *
     * @param  string  $cacheRoot  Usually storage/framework/cache.
     * @return int Number of domain caches emptied.
     */
    public function flushDomainCaches(string $cacheRoot): int
    {
        $files = new Filesystem;

        if (! $files->isDirectory($cacheRoot)) {
            return 0;
        }

        $cleared = 0;
        foreach ($files->directories($cacheRoot) as $directory) {
            if (preg_match('/^[a-f0-9]{32}$/', basename($directory)) !== 1) {
                continue;
            }

            if ($files->isDirectory($directory.'/data')) {
                $files->cleanDirectory($directory.'/data');
                $cleared++;
            }
        }

        return $cleared;
    }
}
