<?php

namespace Leantime\Core;

use Error;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Template;
use Leantime\Core\Events;
use Leantime\Core\Language;
use Illuminate\Support\Str;
use LogicException;

/**
 * HtmxController Class - Base class For all htmx controllers
 *
 * @package    leantime
 * @subpackage core
 */
abstract class HtmxController
{
    use Eventhelpers;

    protected IncomingRequest $incomingRequest;
    protected Template $tpl;

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     * @param IncomingRequest $incomingRequest The request to be initialized.
     * @param template        $tpl             The template to be initialized.
     * @throws BindingResolutionException
     */
    public function __construct(
        IncomingRequest $incomingRequest,
        template $tpl
    ) {
        self::dispatch_event('begin');

        $this->incomingRequest = $incomingRequest;
        $this->tpl = $tpl;

        // initialize
        $this->executeActions();

        self::dispatch_event('end', $this);
    }

    /**
     * Allows hooking into all controllers with events
     *
     * @access private
     *
     * @return void
     * @throws BindingResolutionException
     */
    private function executeActions(): void
    {
        self::dispatch_event('before_init', ['controller' => $this]);
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        self::dispatch_event('before_action', ['controller' => $this]);

        if (! property_exists($this, 'view')) {
            throw new LogicException('HTMX Controllers must include the "$view" static property');
        }

        $action = Str::camel($this->incomingRequest->query->get('id', 'run'));

        if (! method_exists($this, $action)) {
            throw new Error("Method $action doesn't exist.");
        }

        $fragment = $this->$action();


        $this->tpl->displayFragment($this::$view, $fragment ?? '');
    }
}
