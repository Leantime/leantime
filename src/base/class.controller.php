<?php

namespace leantime\base;

use leantime\core\template;
use leantime\core\events;
use leantime\core\language;

class controller {

    protected $tpl;
    protected $language;

    /**
     * constructor - initialize private variables
     *
     * @access public
     * @param $method the method to be initialized
     * @param $params parameters or body of the request
     */
    public function __construct($method, $params)
    {
        events::dispatch_event('begin');

        $this->tpl = new template();
        $this->language = new language();

        // initialize
        $this->executeActions($method, $params);

        events::dispatch_event('end', $this);
    }

    /**
     * Allows hooking into all controllers with events
     */
    private function executeActions($method, $params): void
    {
        $available_params = [
            'controller' => $this,
            'method' => $method,
            'params' => $params
        ];

        events::dispatch_event('before_init', $available_params);
        $this->init();

        events::dispatch_event('before_action', $available_params);
        if (method_exists($this, $method)) {
            $this->$method($params);
        } else {
            $this->run($params);
        }
    }

    /**
     * Extended Controller version of __construct()
     */
    protected function init(): void
    {

    }

    /**
     * Default function for all request types unless otherwise specified
     */
    protected function run(): void
    {

    }

}
