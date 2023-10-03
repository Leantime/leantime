<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;
use Leantime\Core\AppSettings;
use Leantime\Core\Environment;
use Leantime\Core\Controller;
use Leantime\Core\Theme;
use Exception;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */

/**
 *
 */
class App extends Composer
{
    public static array $views = [
        'global::layouts.app',
        'global::layouts.entry',
    ];

    /**
     * @param Menu        $menuRepo
     * @param Setting     $settingsRepo
     * @param Theme       $themeCore
     * @param Environment $config
     * @return void
     */
    public function init(
        Menu $menuRepo,
        Setting $settingsRepo,
        Theme $themeCore,
        Environment $config
    ): void {
        $this->menuRepo = $menuRepo;
        $this->settingsRepo = $settingsRepo;
        $this->themeCore = $themeCore;
        $this->config = $config;
    }

    /**
     * @return array
     */
    /**
     * @return array
     */
    public function with(): array
    {
        if (! isset($_SESSION["userdata"]["id"])) {
            // This is a login session, we need to ensure the default theme and the default language (or the user's browser)
            if (isset($this->config->keepTheme) && $this->config->keepTheme && isset($_COOKIE['theme'])) {
                $theme = $_COOKIE['theme'];
            } else {
                $theme = $this->config->defaultTheme;
            }
        } else {
            // This is not a login session
            if (
                ! isset($_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"])
                || empty($_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"])
            ) {
                // User has a saved theme
                $theme = $this->settingsRepo->getSetting("usersettings." . $_SESSION["userdata"]["id"] . ".theme");
                if ($theme === false) {
                    if (isset($this->config->keepTheme) && $this->config->keepTheme && isset($_COOKIE['theme'])) {
                        $theme = $_COOKIE['theme'];
                    } else {
                        $theme = $this->config->defaultTheme;
                    }
                }
            } else {
                $theme = $_SESSION["usersettings." . $_SESSION["userdata"]["id"] . ".theme"];
            }

            $_SESSION['menuState'] = $this->menuRepo->getSubmenuState('mainMenu') ?: 'open';
        }

        try {
            $this->themeCore->setActive($theme);
        } catch (Exception $e) {
            error_log($e);
            echo "Could not set active theme";
        }


        // Set logo to use
        // Logos can be set via uploads
        // Theme ini


        //All Paths are relative
        //Priority 1 Theme Logo
        //Priority 2 company logo
        //Priority 3 config logo
        //Priority 4 theme logo

        $themeLogoPathSettings = $this->settingsRepo->getSetting("companysettings.$theme.logoPath");
        $companyLogoPathSettings = $this->settingsRepo->getSetting("companysettings.logoPath");
        $companyDefaultLogoConfig = $this->config->logoPath;
        $themeLogoPathIni = $this->themeCore->getLogoUrl();

        //Session Logo Path needs to be set here
        //Logo will be in there. Session will be renewed when new logo is updated or theme is changed
        if (isset($_SESSION["companysettings.logoPath"]) === false) {
            $logoPath = "";

            if (
                $themeLogoPathIni !== false
                && (file_exists(ROOT . $themeLogoPathIni) || str_starts_with($themeLogoPathIni, "http"))
            ) {
                $logoPath = $themeLogoPathIni;
            }

            if (
                $companyDefaultLogoConfig !== false
                && (file_exists(ROOT . $companyDefaultLogoConfig) || str_starts_with($companyDefaultLogoConfig, "http"))
            ) {
                $logoPath = $companyDefaultLogoConfig;
            }

            if (
                $companyLogoPathSettings !== false
                && (file_exists(ROOT . $companyLogoPathSettings) || str_starts_with($companyLogoPathSettings, "http"))
            ) {
                $logoPath = $companyLogoPathSettings;
            }

            if (
                $themeLogoPathSettings !== false
                && (file_exists(ROOT . $themeLogoPathSettings) || str_starts_with($themeLogoPathSettings, "http"))
            ) {
                $logoPath = $themeLogoPathSettings;
            }

            if (str_starts_with($logoPath, "http")) {
                $_SESSION["companysettings.logoPath"] = $logoPath;
            } else {
                $_SESSION["companysettings.logoPath"] = BASE_URL . $logoPath;
            }
        }

        // Set colors to use
        if (! isset($_SESSION["companysettings.primarycolor"])) {
            //new setting
            $primaryColor = $this->settingsRepo->getSetting("companysettings.$theme.primarycolor");
            if ($primaryColor !== false) {
                $_SESSION["companysettings.primarycolor"] = $primaryColor;
                $_SESSION["companysettings.secondarycolor"] = $primaryColor;
                $_SESSION["companysettings.$theme.primarycolor"] = $primaryColor;
                $_SESSION["companysettings.$theme.secondarycolor"] = $primaryColor;
            }

            $secondaryColor = $this->settingsRepo->getSetting("companysettings.$theme.secondarycolor");
            if ($secondaryColor !== false) {
                $_SESSION["companysettings.secondarycolor"] = $secondaryColor;
                $_SESSION["companysettings.$theme.secondarycolor"] = $secondaryColor;
            }
        }

        if (! isset($_SESSION["companysettings.primarycolor"])) {
            $_SESSION["companysettings.primarycolor"] = "#1b75bb";
            $_SESSION["companysettings.secondarycolor"] = "#81B1A8";
            $_SESSION["companysettings.$theme.primarycolor"] = "#1b75bb";
            $_SESSION["companysettings.$theme.secondarycolor"] = "#81B1A8";

            //Old setting
            $mainColor = $this->settingsRepo->getSetting("companysettings.mainColor");
            if ($mainColor !== false) {
                $_SESSION["companysettings.primarycolor"] = "#" . $mainColor;
                $_SESSION["companysettings.secondarycolor"] = "#" . $mainColor;
                $_SESSION["companysettings.$theme.primarycolor"] = "#" . $mainColor;
                $_SESSION["companysettings.$theme.secondarycolor"] = "#" . $mainColor;
            }

            //new setting
            $primaryColor = $this->settingsRepo->getSetting("companysettings.primarycolor");
            if ($primaryColor !== false) {
                $_SESSION["companysettings.primarycolor"] = $primaryColor;
                $_SESSION["companysettings.secondarycolor"] = $primaryColor;
                $_SESSION["companysettings.$theme.primarycolor"] = $primaryColor;
                $_SESSION["companysettings.$theme.secondarycolor"] = $primaryColor;
            }

            $secondaryColor = $this->settingsRepo->getSetting("companysettings.secondarycolor");
            if ($secondaryColor !== false) {
                $_SESSION["companysettings.secondarycolor"] = $secondaryColor;
                $_SESSION["companysettings.$theme.secondarycolor"] = $secondaryColor;
            }
        } else {
            if (! str_starts_with($_SESSION["companysettings.primarycolor"], "#")) {
                $_SESSION["companysettings.primarycolor"] = "#" . $_SESSION["companysettings.primarycolor"];
                $_SESSION["companysettings.secondarycolor"] = "#" . $_SESSION["companysettings.primarycolor"];
                $_SESSION["companysettings.$theme.primarycolor"] = "#" . $_SESSION["companysettings.primarycolor"];
                $_SESSION["companysettings.$theme.secondarycolor"] = "#" . $_SESSION["companysettings.primarycolor"];
            }
        }

        if (! isset($_SESSION["companysettings.sitename"])) {
            $sitename = $this->settingsRepo->getSetting("companysettings.sitename");
            if ($sitename !== false) {
                $_SESSION["companysettings.sitename"] = $sitename;
            } else {
                $_SESSION["companysettings.sitename"] = $this->config->sitename;
            }
        }

        return [];
    }
}
