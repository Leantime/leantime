<?php

namespace Leantime\Domain\Plugins\Models;

use Leantime\Domain\Plugins\Contracts\PluginDisplayStrategy;

/**
 *
 */
class InstalledPlugin implements PluginDisplayStrategy
{
    public ?int $id;
    public string $name;
    public bool $enabled;
    public string $description;
    public string $version;
    public string $imageUrl = '';
    public string $installdate;
    public string $foldername;
    public string $homepage;
    public string|array $authors;

    public ?string $format;

    public ?string $license;

    public ?string $type;

    public ?bool $installed;

    public function getCardDesc(): string
    {
        return $this->description ??= '';
    }

    /**
     * @return array
     */
    public function getMetadataLinks(): array
    {
        $links = [];

        if (! empty($plugin->authors)) {
            $author = is_array($plugin->authors) ? $plugin->authors[0] : $plugin->authors;
            $links[] = [
                'prefix' => __('text.by'),
                'link' => "mailto:{$author->email}",
                'text' => $author->name,
            ];
        }

        if (! empty($plugin->version)) {
            $links[] = [
                'prefix' => __('text.version'),
                'text' => $plugin->version,
            ];
        }

        if (! empty($plugin->homepage)) {
            $links[] = [
                'link' => $plugin->homepage,
                'text' => __('text.visit_site'),
            ];
        }

        return $links;
    }

    /**
     * @return string
     */
    public function getControlsView(): string
    {
        return 'plugins::partials.installed.plugincontrols';
    }

    /**
     * @return string
     */
    public function getPluginImageData(): string
    {
        if (! empty($this->imageUrl) && $this->imageUrl != "false") {
            return $this->imageUrl;
        }

        if (file_exists($image = APP_ROOT . '/app/Plugins/' . str_replace(".", '', $this->foldername) . '/screenshot.png')) {
            // Read image path, convert to base64 encoding
            $imageData = base64_encode(file_get_contents($image));
            return 'data: ' . mime_content_type($image) . ';base64,' . $imageData;
        }

        $image = APP_ROOT . "/public/dist/images/svg/undraw_search_app_oso2.svg";
        $imageData = base64_encode(file_get_contents($image));
        return 'data: ' . mime_content_type($image) . ';base64,' . $imageData;
    }
}
