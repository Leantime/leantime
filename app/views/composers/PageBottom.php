<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;

class PageBottom extends Composer
{
    public static $views = [
        'global::sections.pageBottom',
    ];

    protected \Leantime\Core\AppSettings $settings;

    public function init(\Leantime\Core\AppSettings $settings)
    {
        $this->settings = $settings;
    }

    public function with()
    {
        return [
            'version' => $this->settings->appVersion,
            'runCron' => isset($_SESSION['do_cron']),
            'loggedIn' => isset($_SESSION['userdata']),
        ];
    }
}
