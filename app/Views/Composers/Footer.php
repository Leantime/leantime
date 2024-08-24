<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Controller\Composer;

class Footer extends Composer
{
    public static array $views = [
        'global::sections.footer',
    ];

    protected AppSettings $settings;

    /**
     * @param AppSettings $settings
     *
     * @return void
     */
    public function init(AppSettings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function with(): array
    {
        return [
            'version' => $this->settings->appVersion,
        ];
    }
}
