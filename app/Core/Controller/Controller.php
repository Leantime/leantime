<?php

namespace Leantime\Core\Controller;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Log\Logger;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
use Leantime\Core\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller Class - Base class For all controllers
 *
 * @package    leantime
 * @subpackage core
 */
abstract class Controller
{
    use DispatchesEvents;

    /**
     * @var Response
     */
    protected Response $response;

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     * @param IncomingRequest $incomingRequest The request to be initialized.
     * @param Template        $tpl             The template to be initialized.
     * @param Language        $language        The language to be initialized.
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
        $this->executeActions(
            $incomingRequest->getMethod(),
            $incomingRequest->getRequestParams()
        );

        self::dispatch_event('end', $this);
    }

    /**
     * Allows hooking into all controllers with events
     *
     * @access private
     *
     * @param string       $method
     * @param object|array $params
     *
     * @return void
     * @throws BindingResolutionException
     */
    private function executeActions(string $method, object|array $params): void
    {

        //HEAD execution is equal to GET. Server can handle the content response cutting for us.
        if(strtoupper($method) == "HEAD") {
            $method = "GET";
        }

        $available_params = [
            'controller' => $this,
            'method' => $method,
            'params' => $params,
        ];

        self::dispatch_event('before_init', $available_params);
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        self::dispatch_event('before_action', $available_params);

        if (method_exists($this, $method)) {
            $this->response = $this->$method($params);
        } elseif (method_exists($this, 'run')) {
            $this->response = $this->run();
        } else {
            Log::error('Method not found: ' . $method);
            Frontcontroller::redirect(BASE_URL . "/errors/error501", 307);
        }
    }

    /**
     * getResponse - returns the response
     *
     * @access public
     *
     * @return Response The response object.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
