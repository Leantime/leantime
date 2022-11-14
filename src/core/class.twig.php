<?php

/**
 * Twig class
 */

namespace leantime\core {

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
            $this->setContext($language);
            $this->setFuncs($template);
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
         * set twig global context
         *
         * @access private
         *
         * @return void
         */
        private function setContext($language)
        {
            $context = [
                'session' => $_SESSION,
                'user' => '',
                'language' => [
                    'direction' => '',
                    'code' => '',
                ],
                'site' => [
                    'base_url' => BASE_URL,
                ]
            ];

            $context = self::dispatch_filter('twig_global_context', $context);

            foreach ($context as $varName => $value) {
                $this->twig->addGlobal($varName, $value);
            }
        }

        /**
         * create twig functions
         *
         * @access private
         *
         * @return void
         */
        private function setFuncs($template)
        {
            $functions = [];

            $functions[] = new TwigFunction(
                'dispatchEvent',
                function ($hookname, $payload = []) use ($template) {
                    $template->dispatchTplEvent($hookname, $payload);
                }
            );

            $functions[] = new TwigFunction(
                'dispatchFilter',
                function ($hookname, $payload = [], $available_params = []) use ($template) {
                    return $template->dispatchTplFilter($hookname, $payload, $available_params);
                }
            );

            $functions[] = new TwigFunction(
                '__',
                function ($index) use ($template) {
                    return $template->__($index);
                }
            );

            $functions[] = new TwigFunction(
                'displayNotification',
                function () use ($template) {
                    return $template->displayNotification();
                }
            );

            $functions[] = new TwigFunction(
                'getSvg',
                function ($filename) {
                    $filepath = ROOT."/images/svg/$filename";
                    if (!file_exists($filepath) || !str_ends_with($filepath, '.svg')) {
                        return '';
                    }

                    return file_get_contents($filepath);
                }
            );

            $functions[] = new TwigFunction(
                'sf',
                function ($format, ...$values) {
                    return sprintf($format ?? '', ...$values);
                }
            );

            $functions[] = new TwigFunction(
                'time',
                function () {
                    return time();
                }
            );

            $functions[] = new TwigFunction(
                'includeRoute',
                function ($route) {
                    return $this->frontcontroller->includeAction($route);
                }
            );

            foreach ($functions as $function) {
                $this->twig->addFunction($function);
            }
        }
    }
}
