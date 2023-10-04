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
    /**
     * @var array|string[]
     */
    public static array $views = [
        'global::sections.pageBottom',
    ];

    protected AppSettings $settings;

    /**
     * @param AppSettings $settings
     * @return void
     */
    public function init(AppSettings $settings): void
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function with(): array
    {
        return [
            'version' => $this->settings->appVersion,
            'runCron' => isset($_SESSION['do_cron']),
            'loggedIn' => isset($_SESSION['userdata']),
        ];
    }
}
