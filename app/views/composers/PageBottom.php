<?php

namespace leantime\views\composers;

use leantime\core\Composer;

class PageBottom extends Composer
{
    public static $views = [
        'global::sections.pageBottom',
    ];

    protected \leantime\core\appSettings $settings;

    public function init(\leantime\core\appSettings $settings)
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
