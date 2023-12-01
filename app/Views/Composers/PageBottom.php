<?php

namespace Leantime\Views\Composers;

use Leantime\Core\AppSettings;
use Leantime\Core\Composer;
use Leantime\Core\Environment;

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

    protected Environment $environment;

    /**
     * @param AppSettings $settings
     * @return void
     */
    public function init(AppSettings $settings, Environment $environment): void
    {
        $this->settings = $settings;
        $this->environment = $environment;
    }

    /**
     * @return array
     */
    public function with(): array
    {
        return [
            'version' => $this->settings->appVersion,
            'poorMansCron' => $this->environment->get('poorMansCron'),
            'runCron' => isset($_SESSION['do_cron']),
            'loggedIn' => isset($_SESSION['userdata']),
        ];
    }
}
