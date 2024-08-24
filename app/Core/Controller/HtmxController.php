<?php

namespace Leantime\Core\Controller;

use Error;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Template;
use LogicException;
use Symfony\Component\HttpFoundation\Response;

/**
 * HtmxController Class - Base class For all htmx controllers
 *
 * @method string|null run() The fallback method to be initialized.
 */
abstract class HtmxController
{
    use DispatchesEvents;

    /** @var Response $response */
    protected Response $response;

    /** @var string $view */
    protected static string $view;

    /** @var array $headers */
    protected array $headers = [];

    /**
     * constructor - initialize private variables
     *
     * @param IncomingRequest $incomingRequest The request to be initialized.
     * @param Template        $tpl             The template to be initialized.
     * @throws BindingResolutionException
     */
    public function __construct(
        /** @var IncomingRequest $incomingRequest */
        protected IncomingRequest $incomingRequest,

        /** @var Template $tpl */
        protected Template $tpl,
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
     * @return void
     * @throws BindingResolutionException
     * @throws Error
     * @throws LogicException
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

        if (! method_exists($this, $action) && ! method_exists($this, 'run')) {
            throw new Error("Method $action doesn't exist and no fallback method.");
        }

        $fragment = method_exists($this, $action) ? $this->$action() : $this->run();

        $this->response = tap(
            $this->tpl->displayFragment($this::$view, $fragment ?? ''),
            function (Response $response): void {
                foreach ($this->headers as $key => $value) {
                    $response->headers->set($key, is_array($value) ? implode(',', $value) : $value);
                }
            },
        );
    }

    /**
     * Sets the response header to trigger an htmx event
     *
     * @param string $eventName
     * @return void
     **/
    public function setHTMXEvent(string $eventName): void
    {
        $this->headers['HX-Trigger'] ??= [];
        $this->headers['HX-Trigger'][] = $eventName;
    }

    /**
     * Gets the response
     *
     * @return Response
     **/
    public function getResponse(): Response
    {
        return $this->response;
    }
}

