<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\UI\Composer;
use Leantime\Core\UI\Theme;
use Leantime\Domain\Setting\Repositories\Setting;

class Header extends Composer
{
    public static array $views = [
        'global::sections.header',
    ];

    private Environment $config;

    private Theme $themeCore;

    private AppSettings $appSettings;

    private Setting $settingsRepo;

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

    public function with(): array
    {
        $theme = $this->themeCore->getActive();
        $colorMode = $this->themeCore->getColorMode();
        $colorScheme = $this->themeCore->getColorScheme();
        $themeFont = $this->themeCore->getFont();
        $sitename = '';
        // Set colors to use
        if (! session()->exists('companysettings.sitename')) {
            $sitename = $this->settingsRepo->getSetting('companysettings.sitename');
            if ($sitename !== false) {
                session(['companysettings.sitename' => $sitename]);
            } else {
                session(['companysettings.sitename' => $this->config->sitename]);
            }
        }

        return [
            'sitename' => session('companysettings.sitename') ?? '',
            'primaryColor' => $this->themeCore->getPrimaryColor(),
            'theme' => $theme,
            'version' => $this->appSettings->appVersion ?? '',
            'themeScripts' => [
                $this->themeCore->getJsUrl() ?? '',
                $this->themeCore->getCustomJsUrl() ?? '',
            ],
            'themeColorMode' => $colorMode,
            'themeColorScheme' => $colorScheme,
            'themeFont' => $themeFont,
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
                $this->themeCore->getPrimaryColor(),
                $this->themeCore->getSecondaryColor(),
            ],
        ];
    }
}
