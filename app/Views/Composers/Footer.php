<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\UI\Composer;

class Footer extends Composer
{
    public static array $views = [
        'global::sections.footer',
    ];

    protected AppSettings $settings;

    public function init(AppSettings $settings): void
    {
        $this->settings = $settings;
    }

    public function with(): array
    {
        return [
            'version' => $this->settings->appVersion,
        ];
    }
}
