<?php

namespace Leantime\Views\Composers;

use Leantime\Core\AppSettings;
use Leantime\Core\Composer;

/**
 *
 */

/**
 *
 */
class Footer extends Composer
{
    public static $views = [
        'global::sections.footer',
    ];

    protected $settings;

    /**
     * @param AppSettings $settings
     * @return void
     */
    public function init(AppSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function with()
    {
        return [
            'version' => $this->settings->appVersion,
        ];
    }
}
