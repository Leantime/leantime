<?php

namespace leantime\views\composers;

use leantime\core\Composer;

class Footer extends Composer
{
    public static $views = [
        'global::sections.footer',
    ];

    protected $settings;

    public function init(\leantime\core\appSettings $settings)
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
