<?php

namespace Leantime\Domain\Blueprints\Services;

use Leantime\Domain\Blueprints\Models\CanvasTemplate;
use Symfony\Component\Yaml\Yaml;

class TemplateRegistry
{
    /** @var array<string, CanvasTemplate|null> */
    private array $templates = [];

    private string $definitionsPath;

    public function __construct()
    {
        $this->definitionsPath = APP_ROOT.'/app/Domain/Blueprints/Templates/definitions';
    }

    /**
     * @param  string  $slug  Canvas type slug (e.g., "swot", "lean")
     */
    public function get(string $slug): ?CanvasTemplate
    {
        $slug = strtolower(trim($slug));

        if (array_key_exists($slug, $this->templates)) {
            return $this->templates[$slug];
        }

        $path = $this->definitionsPath.'/'.$slug.'.yaml';
        if (! file_exists($path)) {
            $this->templates[$slug] = null;

            return null;
        }

        $data = Yaml::parseFile($path);
        $template = new CanvasTemplate($data);
        $this->templates[$slug] = $template;

        return $template;
    }

    /**
     * @return array<string, CanvasTemplate>
     */
    public function all(): array
    {
        $this->loadAll();

        return array_filter($this->templates);
    }

    /**
     * @return list<string>
     */
    public function slugs(): array
    {
        return array_keys($this->all());
    }

    /**
     * @param  string  $dbType  Database type value (e.g., "swotcanvas")
     */
    public function getByDatabaseType(string $dbType): ?CanvasTemplate
    {
        $slug = str_replace('canvas', '', $dbType);

        return $this->get($slug);
    }

    private function loadAll(): void
    {
        $files = glob($this->definitionsPath.'/*.yaml');
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            $slug = basename($file, '.yaml');
            if (! array_key_exists($slug, $this->templates)) {
                $this->get($slug);
            }
        }
    }
}
