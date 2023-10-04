<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Core\Environment;
use Leantime\Core\AppSettings;
use Leantime\Core\Theme;

/**
 *
 */

/**
 *
 */
class Header extends Composer
{
    public static array $views = [
        'global::sections.header',
    ];

    private Setting $SettingsRepo;
    private Environment $config;
    private AppSettings $AppSettings;
    private Theme $ThemeCore;
    private Theme $themeCore;
    private AppSettings $appSettings;
    private Setting $settingsRepo;

    /**
     * @param Setting     $settingsRepo
     * @param Environment $config
     * @param AppSettings $appSettings
     * @param Theme       $themeCore
     * @return void
     */
    public function init(
        Setting $settingsRepo,
        Environment $config,
        AppSettings $appSettings,
        Theme $themeCore
    ): void {
        $this->settingsRepo = $settingsRepo;
        $this->config = $config;
        $this->appSettings = $appSettings;
        $this->themeCore = $themeCore;
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
