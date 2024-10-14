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
     * Retrieves the metadata links for the plugin.
     *
     * The metadata links include author's email, author's name, plugin version, and homepage URL.
     * If the authors are not empty, the email of the first author is included as a link.
     * If the version is not empty, the plugin version is included as a link.
     * If the homepage is not empty, the homepage URL is included as a link.
     *
     * @return array An array of metadata links.
     */
    public function getMetadataLinks(): array
    {
        $links = [];

        if (! empty($this->authors) && (is_array($this->authors) || is_object($this->authors))) {
            $author = is_array($this->authors) ? $this->authors[0] : $this->authors;

            if(is_object($author)){
                $links[] = [
                    'prefix' => __('text.by'),
                    'link' => "mailto:{$author->email}",
                    'text' => $author->name,
                ];
            }
        }

        if (! empty($this->version)) {
            $links[] = [
                'prefix' => __('text.version'),
                'text' => $this->version,
            ];
        }

        if (! empty($this->homepage)) {
            $links[] = [
                'link' => $this->homepage,
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
