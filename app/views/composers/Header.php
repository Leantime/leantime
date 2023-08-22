<?php

namespace leantime\views\composers;

use leantime\core\Composer;
use leantime\domain\repositories\setting;
use leantime\core\environment;
use leantime\core\appSettings;
use leantime\core\theme;

class Header extends Composer
{
    public static $views = [
        'global::sections.header',
    ];

    private setting $settingsRepo;
    private environment $config;
    private appSettings $appSettings;
    private theme $themeCore;

    public function init(
        setting $settingsRepo,
        environment $config,
        appSettings $appSettings,
        theme $themeCore
    ) {
        $this->settingsRepo = $settingsRepo;
        $this->config = $config;
        $this->appSettings = $appSettings;
        $this->themeCore = $themeCore;
    }

    public function with()
    {
        return [
            'sitename' => $_SESSION['companysettings.sitename'] ?? '',
            'primaryColor' => $_SESSION['companysettings.primarycolor'] ?? '',
            'theme' => $this->themeCore->getActive() ?? 'default',
            'version' => $this->appSettings->appVersion ?? '',
            'themeScripts' => [
                $this->themeCore->getJsUrl() ?? '',
                $this->themeCore->getCustomJsUrl() ?? '',
            ],
            'themeStyles' => [
                [
                    'id' => 'themeStyleSheet',
                    'url' => $this->themeCore->getStyleUrl() ?? '',
                ],
                [
                    'url' => $this->themeCore->getCustomStyleUrl() ?? '',
                ],
            ],
            'accents' => [
                $_SESSION['companysettings.primarycolor'] ?? '',
                $_SESSION['companysettings.secondarycolor'] ?? '',
            ],
        ];
    }
}
