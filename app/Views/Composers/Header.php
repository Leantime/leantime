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
        // Batch-preload all theme settings in a single query on first load.
        // This populates SettingCache's in-memory tier so all subsequent
        // getSetting() calls from getActive(), getColorMode(), etc. are instant.
        $this->themeCore->preloadUserSettings();

        $theme = $this->themeCore->getActive();
        $colorMode = $this->themeCore->getColorMode();
        $colorScheme = $this->themeCore->getColorScheme();
        $themeFont = $this->themeCore->getFont();

        // Set colors to use
        if (! session()->exists('companysettings.sitename')) {
            $sitename = $this->settingsRepo->getSetting('companysettings.sitename');
            if ($sitename !== false) {
                session(['companysettings.sitename' => $sitename]);
            } else {
                session(['companysettings.sitename' => $this->config->sitename]);
            }
        }

        $backgroundOpacity = 0.1;
        if ($this->themeCore->getBackgroundType() == 'image') {
            $backgroundOpacity = 1;
        }

        return [
            'sitename' => session('companysettings.sitename') ?? '',
            'primaryColor' => $this->themeCore->getPrimaryColor(),
            'theme' => $theme,
            'version' => $this->appSettings->appVersion ?? '',
            'themeScripts' => [
                $this->themeCore->getJsUrl(),
                $this->themeCore->getCustomJsUrl(),
            ],
            'themeColorMode' => $colorMode,
            'themeColorScheme' => $colorScheme,
            'themeFont' => $themeFont,
            'themeStyles' => [
                [
                    'id' => 'themeStyleSheet',
                    'url' => $this->themeCore->getStyleUrl(),
                ],
                [
                    'url' => $this->themeCore->getCustomStyleUrl(),
                ],
            ],
            'accents' => [
                $this->themeCore->getPrimaryColor(),
                $this->themeCore->getSecondaryColor(),
                false,  // accent3 uses CSS default
                false,  // accent4 uses CSS default
            ],
            'themeBg' => $this->themeCore->getBackgroundImage(),
            'themeBgCss' => self::toCssString((string) $this->themeCore->getBackgroundImage()),
            'themeOpacity' => $backgroundOpacity,
            'themeType' => $this->themeCore->getBackgroundType(),
        ];
    }

    /**
     * Encode a value as a double-quoted CSS string literal safe to print inside a <style> element.
     *
     * The background image URL is user/admin controlled and printed into raw <style> text, where
     * HTML escaping does not apply and FILTER_SANITIZE_URL keeps quotes, parentheses, ";" and "<".
     * Every character outside a conservative URL-safe set is emitted as a CSS hex escape, so the
     * value can neither close the url("...") string nor the <style> element.
     *
     * @param  string  $value  The raw value (e.g. a URL).
     * @return string The quoted CSS string, e.g. "https://x/bg.png".
     */
    public static function toCssString(string $value): string
    {
        $escaped = preg_replace_callback(
            '/[^A-Za-z0-9\/:._\-~?&=%#+,@!$*]/u',
            fn (array $match) => '\\'.dechex((int) mb_ord($match[0], 'UTF-8')).' ',
            $value
        );

        return '"'.($escaped ?? '').'"';
    }
}
