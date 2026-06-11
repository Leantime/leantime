<?php

namespace Leantime\Domain\ContentTemplates\Services;

use Leantime\Domain\ContentTemplates\Contracts\Applier;
use Leantime\Domain\ContentTemplates\Models\ContentTemplate;
use Symfony\Component\Yaml\Yaml;

/**
 * Loads and indexes content templates from one or more library directories.
 *
 * Library layout convention (per directory root):
 *
 *     <root>/<appliesTo>/<key>.yaml
 *
 * e.g.  Library/logicmodel/education-k12.yaml
 *
 * Plugins can register additional roots via registerLibraryRoot(); the registry
 * scans the union of all registered roots on first access and caches the result
 * for the request.
 *
 * Appliers are registered separately (one per appliesTo). Resolving an applier
 * for a given template tells callers which service to dispatch the apply to.
 */
class ContentTemplateRegistry
{
    /** @var list<string> Absolute paths to library directories. */
    private array $libraryRoots = [];

    /** @var array<string, array<string, ContentTemplate>>|null Cache: appliesTo → key → template. */
    private ?array $templates = null;

    /** @var array<string, Applier> appliesTo → applier instance. */
    private array $appliers = [];

    public function __construct()
    {
        $coreLibrary = APP_ROOT.'/app/Domain/ContentTemplates/Library';
        if (is_dir($coreLibrary)) {
            $this->libraryRoots[] = $coreLibrary;
        }
    }

    /**
     * Register an additional library root. Idempotent — duplicate paths are ignored.
     *
     * @param  string  $absolutePath  Directory containing per-appliesTo subdirectories of .yaml templates.
     */
    public function registerLibraryRoot(string $absolutePath): void
    {
        $absolutePath = rtrim($absolutePath, DIRECTORY_SEPARATOR);
        if ($absolutePath === '' || in_array($absolutePath, $this->libraryRoots, true)) {
            return;
        }
        $this->libraryRoots[] = $absolutePath;
        $this->templates = null; // invalidate cache
    }

    /**
     * Register an applier for an appliesTo value. Overwrites if already registered.
     */
    public function registerApplier(string $appliesTo, Applier $applier): void
    {
        $this->appliers[$appliesTo] = $applier;
    }

    /**
     * Resolve an applier for an appliesTo value.
     *
     * First tries an explicit binding. If none, falls back to scanning the
     * registered appliers for one whose supports() returns true for the
     * given appliesTo — letting the catch-all CanvasItemsApplier handle
     * any non-wiki canvas type that hasn't been explicitly bound.
     */
    public function applierFor(string $appliesTo): ?Applier
    {
        if (isset($this->appliers[$appliesTo])) {
            return $this->appliers[$appliesTo];
        }
        foreach ($this->appliers as $candidate) {
            if ($candidate->supports($appliesTo)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Get a single template by (appliesTo, key).
     */
    public function get(string $appliesTo, string $key): ?ContentTemplate
    {
        $all = $this->loadAll();

        return $all[$appliesTo][$key] ?? null;
    }

    /**
     * @return array<string, ContentTemplate> All templates for the given appliesTo, keyed by template key.
     */
    public function forAppliesTo(string $appliesTo): array
    {
        $all = $this->loadAll();

        return $all[$appliesTo] ?? [];
    }

    /**
     * @return array<string, array<string, ContentTemplate>> All templates across all roots, by appliesTo → key.
     */
    public function all(): array
    {
        return $this->loadAll();
    }

    /**
     * Scan all registered library roots, parse YAML, build the index.
     *
     * Later roots override earlier ones on (appliesTo, key) collision, so a
     * plugin can override a core template with the same key if needed.
     *
     * @return array<string, array<string, ContentTemplate>>
     */
    private function loadAll(): array
    {
        if ($this->templates !== null) {
            return $this->templates;
        }

        $index = [];

        foreach ($this->libraryRoots as $root) {
            if (! is_dir($root)) {
                continue;
            }

            // Each direct subdirectory of a root is an appliesTo bucket.
            foreach ((array) glob($root.'/*', GLOB_ONLYDIR) as $appliesDir) {
                $appliesTo = basename((string) $appliesDir);

                foreach ((array) glob($appliesDir.'/*.yaml') as $yamlFile) {
                    $template = $this->loadYaml((string) $yamlFile);
                    if ($template === null || ! $template->isUsable()) {
                        continue;
                    }
                    // Force appliesTo to match the directory the file lives in,
                    // so a misnamed YAML can't claim a different bucket.
                    if ($template->appliesTo !== $appliesTo) {
                        $template = new ContentTemplate(
                            key: $template->key,
                            title: $template->title,
                            description: $template->description,
                            appliesTo: $appliesTo,
                            sector: $template->sector,
                            icon: $template->icon,
                            author: $template->author,
                            version: $template->version,
                            license: $template->license,
                            payload: $template->payload,
                        );
                    }
                    $index[$appliesTo][$template->key] = $template;
                }
            }
        }

        return $this->templates = $index;
    }

    /**
     * Parse a single YAML file into a ContentTemplate. Returns null on failure.
     */
    private function loadYaml(string $path): ?ContentTemplate
    {
        try {
            $data = Yaml::parseFile($path);
        } catch (\Throwable $e) {
            return null;
        }
        if (! is_array($data)) {
            return null;
        }

        return ContentTemplate::fromArray($data);
    }
}
