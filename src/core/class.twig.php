<?php

/**
 * Twig class
 */

namespace leantime\core;

use leantime\base\eventhelpers;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;
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
        $this->tempalte = $template;
        $this->setContext($language);
        self::dispatch_event('before_twig_extensions', [
            'loader' => $this->loader,
            'twig' => $this->twig
        ]);
        $this->compInit();
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
     * initialize components
     *
     * @access private
     *
     * @return void
     */
    private function compInit()
    {
        Configuration::make($this->twig)
            ->setTemplatesPath('../twig/components')
            ->setTemplatesExtension('twig')
            ->useCustomTags()
            ->setup();
    }

    /**
     * Twig Customizations
     *
     * @return \Twig\Extension\AbstractExtension
     */
    private function initializeCustomizations()
    {
        return new class($this) extends AbstractExtension
        {

            public function __construct($twig_parent)
            {
                $this->twig_parent = $twig_parent;
                $this->template = $twig_parent->template;
                $this->language = $twig_parent->language;
            }

            /**
             * Sets Globals for twig
             */
            public function getGlobals(): array
            {
                return $this->twig_parent::dispatch_filter(
                    'global_context' ,
                    [
                        'session' => $_SESSION,
                        'user' => '',
                        'language' => [
                            'direction' => '',
                            'code' => '',
                        ],
                        'site' => [
                            'base_url' => BASE_URL,
                        ]
                    ]
                );
            }

            /**
             * Returns the token parser instances to add to the existing list.
             *
             * @return \Twig\TokenParser\TokenParserInterface[]
             */
            public function getTokenParsers()
            {

            }

            /**
             * create twig functions
             *
             * @access private
             *
             * @return \Twig\TwigFunction[]
            */
            public function getFunctions()
            {
                return [
                    new TwigFunction(
                        'dispatchEvent',
                        function ($hookname, $payload = []) {
                            $this->template->dispatchTplEvent($hookname, $payload);
                        }
                    ),
                    new TwigFunction(
                        'dispatchFilter',
                        function ($hookname, $payload = [], $available_params = []) {
                            return $this->template->dispatchTplFilter($hookname, $payload, $available_params);
                        }
                    ),
                    new TwigFunction(
                        '__',
                        function ($index) {
                            return $this->template->__($index);
                        }
                    ),
                    new TwigFunction(
                        'displayNotification',
                        function () {
                            return $this->template->displayNotification();
                        }
                    ),
                    new TwigFunction(
                        'getSvg',
                        function ($filename) {
                            $filepath = ROOT."/images/svg/$filename";
                            if (!file_exists($filepath) || !str_ends_with($filepath, '.svg')) {
                                return '';
                            }

                            return file_get_contents($filepath);
                        }
                    ),
                    new TwigFunction(
                        'sf',
                        function ($hookname, $format, ...$values) {
                            $format = $this->template->dispatchTplFilter("$hookname.format", $format);
                            $values = $this->template->dispatchTplFilter("$hookname.values", $values);

                            return sprintf($format ?? '', ...$values);
                        }
                    ),
                    new TwigFunction(
                        'time',
                        function () {
                            return time();
                        }
                    ),
                    new TwigFunction(
                        'includeRoute',
                        function ($route) {
                            return $this->frontcontroller->includeAction($route);
                        }
                    )
                ];
            }
        };
    }
}
