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
class PageBottom extends Composer
{
    public static $views = [
        'global::sections.pageBottom',
    ];

    protected AppSettings $settings;

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
            'runCron' => isset($_SESSION['do_cron']),
            'loggedIn' => isset($_SESSION['userdata']),
        ];
    }
}
