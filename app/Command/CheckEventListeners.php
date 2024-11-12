<?php

namespace Leantime\Command;

use DigitalJoeCo\Leantime\Documentor\Documentor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Leantime\Core\Events\EventDispatcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Finder\Finder;

#[AsCommand(
    name: 'event:check-listeners',
    description: 'Validate event listener paths against available events',
)]
class CheckEventListeners extends Command
{
    protected $signature = 'event:check-listeners {--debug : Show detailed debug information} {--clear-cache : Clear the event cache}';

    protected $description = 'Check if all registered event listeners match existing events';

    protected $events = [];

    protected $listeners = [];

    protected $cacheFile = 'storage/event_cache.json';

    public function handle()
    {
        ini_set('memory_limit', '1024M'); // Increase memory limit to 1GB

        $this->info('Scanning for event contexts and dispatched events...');
        if ($this->option('clear-cache')) {
            $this->clearCache();
        }

        if ($this->loadFromCache()) {
            $this->info('Loaded events from cache.');
        } else {
            $this->scanForEventContexts();
            $this->storeToCache();
        }

        $this->scanForListeners();
        $this->validateListeners();

        return Command::SUCCESS;
    }

    private function scanForEventContexts(): void
    {
        $files = File::allFiles(app_path());
        $finder = new Finder;
        $finder->files()
            ->in('app')
            ->notPath('vendor')
            ->notPath('Plugins/*/vendor')
            ->name('*.php');

        $documentor = new Documentor($this->output);
        $documentor->relative = 'app';

        $progress_bar = new ProgressBar($this->output, \iterator_count($finder));
        ProgressBar::setFormatDefinition('custom', ' %current%/%max% -- %message% (%filename%)');

        $progress_bar->setFormat('custom');

        $progress_bar->setMessage('Finding Events');
        $progress_bar->start();

        foreach ($finder as $file) {

            $progress_bar->setMessage('Processing file…');
            $progress_bar->setMessage($file->getPathname(), 'filename');

            $this->parseFileForEventContexts($file, $documentor);

            $progress_bar->advance();

        }

        $progress_bar->setMessage('Completed parsing files');
        $progress_bar->finish();
    }

    private function parseFileForEventContexts($file, Documentor $documentor): void
    {
        try {
            if ($this->option('debug')) {
                //$this->info("Parsing file: {$file->getPathname()}");
            }

            $documentor->parse($file);

            foreach ($documentor->get_hooks() as $hook) {
                $context = 'Leantime.'.$hook->get_hook();
                if ($this->option('debug')) {
                    $this->info("Found event: {$context}");
                }

                if (! in_array($context, $this->events)) {
                    $this->events[] = $context;
                }

            }

        } catch (\Exception $e) {
            $this->error("Failed to parse file: {$file->getPathname()} - ".$e->getMessage());
        }
    }

    private function scanForListeners(): void
    {
        $registries = EventDispatcher::get_registries();
        $this->listeners = array_merge($registries['events'], $registries['filters']);
    }

    private function loadFromCache(): bool
    {
        if (file_exists($this->cacheFile)) {
            $this->events = json_decode(file_get_contents($this->cacheFile), true);

            return true;
        }

        return false;
    }

    private function storeToCache(): void
    {
        file_put_contents($this->cacheFile, json_encode($this->events));
    }

    private function clearCache(): void
    {
        if (file_exists($this->cacheFile)) {
            unlink($this->cacheFile);
            $this->info('Cache cleared.');
        }
    }

    private function validateListeners(): void
    {
        $normalizedEvents = array_map('strtolower', $this->events);
        $unmatchedListeners = [];

        foreach ($this->listeners as $listener) {
            $listener = strtolower($listener);
            if (! $this->matchesAnyEvent($listener, $normalizedEvents)) {
                $unmatchedListeners[] = $listener;
            }
        }

        if (! empty($unmatchedListeners)) {
            $this->warn('Unmatched listeners found:');
            foreach ($unmatchedListeners as $listener) {
                $this->line("- $listener");
            }
        } else {
            $this->info('All listeners have corresponding events.');
        }
    }

    private function matchesAnyEvent(string $listener, array $events): bool
    {

        preg_match_all('/\{RGX:(.*?):RGX\}/', $listener, $regexMatches);

        $key = strtr($listener, [
            ...collect($regexMatches[0] ?? [])->mapWithKeys(fn ($match, $i) => [$match => "REGEX_MATCH_$i"])->toArray(),
            '*' => 'RANDOM_STRING',
            '?' => 'RANDOM_CHARACTER',
        ]);

        // escape the non regex characters
        $pattern = preg_quote($key, '/');

        $pattern = strtr($pattern, [
            'RANDOM_STRING' => '.*?', // 0 or more (lazy) - asterisk (*)
            'RANDOM_CHARACTER' => '.', // 1 character - question mark (?)
            ...collect($regexMatches[1] ?? [])->mapWithKeys(fn ($match, $i) => ["REGEX_MATCH_$i" => $match])->toArray(),
        ]);

        foreach ($events as $event) {
            if (preg_match("/^$pattern$/", $event)) {
                return true;
            }
        }

        return false;
    }
}
