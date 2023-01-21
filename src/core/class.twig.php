<?php

/**
 * Twig class
 */

namespace leantime\core;

use leantime\base\eventhelpers;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use Performing\TwigComponents\Configuration;

class twig {
    use eventhelpers;

    /**
     * @access private
     *
     * @var \Twig\Loader\FilesystemLoader $loader
     */
    private FilesystemLoader $loader;

    /**
     * @access private
     *
     * @var \Twig\Environment $twig
     */
    private Environment $twig;

    /**
     * constructor
     */
    public function __construct($theme, $language, $template)
    {
        $this->frontcontroller = frontcontroller::getInstance(ROOT);
        $this->loader = $this->loaderInit($theme);
        $this->twig = $this->envInit();
        $this->template = $template;
        $this->setContext($language);
        self::dispatch_event('before_twig_extensions', [
            'loader' => $this->loader,
            'twig' => $this->twig
        ]);
        $this->initializeExtensions();
    }

    /**
     * get the twig environment
     *
     * @access public
     *
     * @return \Twig\Environment
     */
    public function getEnv()
    {
        return $this->twig;
    }

    /**
     * initialize twig
     *
     * @access private
     *
     * @return \Twig\Environment
     */
    private function envInit()
    {
        $twigCacheLocation = self::dispatch_filter('twig_cache_location', ROOT."/../src/twig/cache");

        return new Environment($this->loader, [
            'cache' => $twigCacheLocation
        ]);
    }

    /**
     * initialize loader
     *
     * @access private
     *
     * @return \Twig\Loader\FilesystemLoader
     */
    private function loaderInit($theme)
    {
        $twigDirectories = [];

        // get domain templates
        $domain ="../src/domain";
        $modules = array_diff(scandir(ROOT."/".$domain, SCANDIR_SORT_NONE), ['.', '..']);
        foreach ($modules as $module) {
            $dir = "../src/domain/$module/templates";

            if (is_dir(ROOT."/".$dir)) {
                $twigDirectories[] = $dir;
            }
        }

        // add theme templates
        $theme ="theme/".$theme->getActive()."/layout";
        if (is_dir(ROOT."/".$theme)) {
            $twigDirectories[] = $theme;
        }

        // get global partials
        $twigPartials ="../src/twig/partials";
        if (is_dir(ROOT."/".$twigPartials)) {
            $twigDirectories[] = $twigPartials;
        }

        return new FilesystemLoader($twigDirectories, ROOT);
    }

    /**
     * Twig Extensions
     *
     * @return void
     */
    private function initializeExtensions(): void
    {
        $extensions = array_diff(scandir(ROOT."/../src/twig/Extensions", SCANDIR_SORT_NONE), ['.', '..']);
        foreach ($extensions as $extension) {
            require_once ROOT."/../src/twig/Extensions/$extension";
            $this->twig->addExtension(new $extension($this));
        }
    }
}
