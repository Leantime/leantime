<?php

namespace leantime\base;

use leantime\core\template;
use leantime\core\events;
use leantime\core\language;

class controller {

    use eventhelpers;

    protected template $tpl;
    protected language $language;

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     * @param $method the method to be initialized
     * @param $params parameters or body of the request
     */
    public function __construct($method, $params)
    {
        self::dispatch_event('begin');

        $this->tpl = new template();
        $this->language = new language();

        // initialize
        $this->executeActions($method, $params);

        self::dispatch_event('end', $this);
    }

    /**
     * Allows hooking into all controllers with events
     *
     * @access private
     *
     * @param string $method
     * @param array|object $params
     *
     * @return void
     */
    private function executeActions($method, $params): void
    {
        $available_params = [
            'controller' => $this,
            'method' => $method,
            'params' => $params
        ];

        self::dispatch_event('before_init', $available_params);
        $this->init();

        self::dispatch_event('before_action', $available_params);
        if (method_exists($this, $method)) {
            $this->$method($params);
        } else {
            $this->run();
        }
    }

    /**
     * Extended Controller version of __construct()
     *
     * @access protected
     *
     * @return void
     */
    protected function init()
    {

    }

    /**
     * Default function for all request types unless otherwise specified
     *
     * @access protected
     *
     * @return void
     */
    protected function run()
    {

    }

}
