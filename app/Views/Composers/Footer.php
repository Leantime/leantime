<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;

class Footer extends Composer
{
    public static $views = [
        'global::sections.footer',
    ];

    protected $settings;

    public function init(\Leantime\Core\AppSettings $settings)
    {
        $this->settings = $settings;
    }

    public function with()
    {
        return [
            'version' => $this->settings->appVersion,
        ];
    }
}
