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
    public string $vendorDisplayName;
    public int $vendorId;
    public string $vendorEmail;
    public string $marketplaceUrl;
    public ?string $startingPrice;
    public ?array $pricingTiers;
    public ?string $license;
    public ?string $rating;
    public ?int $reviewCount;
    public array $reviews;
    public string $marketplaceId;
    public array $compatibility;
    public string $version;

    public function getCardDesc(): string
    {
        return $this->excerpt;
    }

    public function getMetadataLinks(): array
    {
        $links = [];

        if (! empty($this->vendorDisplayName) && (! empty($this->vendorId) || ! empty($this->vendorEmail))) {
            $vendor = [
                'prefix' => __('text.by'),
                'display' => $this->vendorDisplayName,
            ];

            $vendor['link'] = ! empty($this->vendorId) ? "/plugins/marketplace?" . http_build_query(['vendor_id' => $this->vendorId]) : "mailto:{$this->vendorEmail}";

            $links[] = $vendor;
        }

        if (! empty($this->startingPrice)) {
            $links[] = [
                'prefix' => __('text.starting_at'),
                'display' => $this->startingPrice,
            ];
        }

        if (! empty($this->rating)) {
            $links[] = [
                'prefix' => __('text.rating'),
                'display' => $this->rating,
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
