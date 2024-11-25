<?php

namespace Leantime\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;

#[AsCommand(
    name: 'translations:check-unused',
    description: 'Check for unused translation strings in the codebase',
)]
class CheckTranslations extends Command
{
    protected $signature = 'translations:check-unused
        {--debug : Show detailed debug information}
        {--export= : Export results to a file}
        {--exclude=vendor,node_modules,.git,storage,cache : Comma separated list of directories to exclude}';

    protected $description = 'Scan codebase for unused translation strings';

    protected $translations = [];
    protected $usedTranslations = [];

    public function handle()
    {
        $this->info('Scanning for translation strings...');

        // Parse the language file
        $this->parseLanguageFile();

        // Scan files for usage
        $this->scanFiles();

        // Generate report
        $this->generateReport();

        return Command::SUCCESS;
    }

    private function parseLanguageFile(): void
    {
        $langFile = app_path('Language/en-US.ini');
        if (!file_exists($langFile)) {
            $this->error('Language file not found: ' . $langFile);
            return;
        }

        $this->info('Parsing language file...');
        $this->translations = parse_ini_file($langFile);

        if ($this->option('debug')) {
            $this->info(sprintf('Found %d translation strings', count($this->translations)));
        }
    }

    private function scanFiles(): void
    {
        $excludeDirs = explode(',', $this->option('exclude'));

        if ($this->option('debug')) {
            $this->info('Excluding directories: ' . implode(', ', $excludeDirs));
        }

        $finder = new Finder();
        $finder->files()
            ->in(app_path())
            ->name('*.php')
            ->name('*.tpl.php')
            ->name('*.html')
            ->name('*.blade.php')
            ->name('*.js')
            ->notPath($excludeDirs);

        $progress = new ProgressBar($this->output, iterator_count($finder));
        $progress->setFormat('debug');
        $progress->start();

        foreach ($finder as $file) {
            $this->scanFileForTranslations($file);
            $progress->advance();
        }

        $progress->finish();
        $this->line('');
    }

    private function scanFileForTranslations($file): void
    {
        $content = file_get_contents($file->getRealPath());
        $filePath = $file->getRelativePathname();

        foreach ($this->translations as $key => $value) {
            // Search for various usage patterns
            $patterns = [
                preg_quote($key, '/'),                    // Direct key usage
                preg_quote("'$key'", '/'),                // Single quoted
                preg_quote("\"$key\"", '/'),              // Double quoted
                preg_quote('__("' . $key . '")', '/'),    // PHP translation function
                preg_quote("__('$key')", '/'),            // PHP translation function
                preg_quote('$tpl->__("' . $key . '")', '/'), // Template translation
                preg_quote('$tpl->__(\'' . $key . '\')', '/'), // Template translation
            ];

            if ($this->option('debug')) {
                $this->line("Scanning file: {$filePath}");
            }

            foreach ($patterns as $pattern) {
                if (preg_match('/' . $pattern . '/', $content)) {
                    if ($this->option('debug')) {
                        $this->line(" - Found usage of key: {$key}");
                    }
                    $this->usedTranslations[$key] = true;
                    break;
                }
            }
        }
    }

    private function generateReport(): void
    {
        $unusedTranslations = array_diff_key($this->translations, $this->usedTranslations);

        if (empty($unusedTranslations)) {
            $this->info('No unused translations found.');
            return;
        }

        $this->warn(sprintf('Found %d unused translations:', count($unusedTranslations)));

        foreach ($unusedTranslations as $key => $value) {
            $this->line("- $key");
        }

        if ($exportFile = $this->option('export')) {
            file_put_contents(
                $exportFile,
                json_encode($unusedTranslations, JSON_PRETTY_PRINT)
            );
            $this->info("Results exported to: $exportFile");
        }
    }
}
