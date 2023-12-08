<?php

namespace Leantime\Domain\Plugins\Models;

use Leantime\Domain\Plugins\Contracts\PluginDisplayStrategy;

/**
 *
 */
class MarketplacePlugin implements PluginDisplayStrategy
{
    public string $identifier;
    public string $name;
    public string $excerpt;
    public string $description;
    public string $imageUrl;
    public array|string $authors;
    public string $version;
    public string $marketplaceUrl;
    public ?string $price;
    public ?string $license;
    public ?string $rating;
    public string $marketplaceId;

    public function getCardDesc(): string
    {
        return $this->excerpt;
    }

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

        return $links;
    }

    public function getControlsView(): string
    {
        return 'plugins::partials.marketplace.plugincontrols';
    }

    public function getPluginImageData(): string
    {
        static $defaultImage;
        $defaultImage ??= 'data: '
            . mime_content_type($imageUrl = APP_ROOT . "/public/dist/images/svg/undraw_search_app_oso2.svg")
            . ';base64,' . base64_encode(file_get_contents($imageUrl));

        return ! empty($this->imageUrl) ? $this->imageUrl : $defaultImage;
    }
}
