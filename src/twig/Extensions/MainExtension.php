<?php

namespace TwigExtensions;

use Twig\Extension\AbstractExtension;

class MainExtension extends AbstractExtension
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
}
