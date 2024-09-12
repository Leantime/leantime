<?php

namespace Leantime\Core\Controller;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\UI\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller Class - Base class For all controllers
 */
abstract class Controller
{
    use DispatchesEvents;

    protected Response $response;

    /**
     * constructor - initialize private variables
     *
     *
     * @param  IncomingRequest  $incomingRequest  The request to be initialized.
     * @param  Template  $tpl  The template to be initialized.
     * @param  Language  $language  The language to be initialized.
     *
     * @throws BindingResolutionException
     */
    public function __construct(
        /** @var IncomingRequest */
        protected IncomingRequest $incomingRequest,

        /** @var Template */
        protected Template $tpl,

        /** @var Language */
        protected Language $language,
    ) {
        self::dispatch_event('begin');

        // initialize
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        self::dispatch_event('end', $this);
    }

    /**
     * getResponse - returns the response
     *
     *
     * @return Response The response object.
     */
    public function getResponse(): Response
    {
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
        throw new \BadMethodCallException(sprintf(
            'Method %s::%s does not exist.', static::class, $method
        ));
    }
}
