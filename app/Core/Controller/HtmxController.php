<?php

namespace Leantime\Core\Controller;

use http\Exception\BadMethodCallException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;
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

    protected Response $response;

    protected static string $view;

    protected array $headers = [];

    /**
     * constructor - initialize private variables
     *
     * @param  IncomingRequest  $incomingRequest  The request to be initialized.
     * @param  Template  $tpl  The template to be initialized.
     *
     * @throws BindingResolutionException
     */
    public function __construct(
        /** @var IncomingRequest $incomingRequest */
        protected IncomingRequest $incomingRequest,

        /** @var Template $tpl */
        public Template $tpl,

    ) {
        self::dispatchEvent('begin');

        $this->incomingRequest = $incomingRequest;
        $this->tpl = $tpl;
        $this->response = app()->make(Response::class);

        // initialize
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        if (! property_exists($this, 'view')) {
            throw new LogicException('HTMX Controllers must include the "$view" static property');
        }

        self::dispatchEvent('end', $this);
    }

    /**
     * Sets the response header to trigger an htmx event
     *
     **/
    public function setHTMXEvent(string $eventName): void
    {
        $this->headers['HX-Trigger'] ??= [];
        $this->headers['HX-Trigger'][] = $eventName;
    }

    /**
     * Gets the response
     *
     **/
    public function getResponse($fragment): Response
    {
        $this->response = tap(
            $this->tpl->displayFragment($this::$view, $fragment ?? ''),
            function (Response $response): void {
                foreach ($this->headers as $key => $value) {
                    $response->headers->set($key, is_array($value) ? implode(',', $value) : $value);
                }
            },
        );

        return $this->response;
    }

    /**
     * Execute an action on the controller.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        return $this->{$method}($parameters);
    }

    /**
     * Handle calls to missing methods on the controller.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     *
     * @throws \BadMethodCallException
     */
    public function __call($method, $parameters)
    {
        throw new BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
