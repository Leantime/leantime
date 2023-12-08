<?php

namespace Leantime\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Leantime\Core\Template;
use Leantime\Core\Language;
use Symfony\Component\HttpFoundation\Response;

/**
 * Controller Class - Base class For all controllers
 *
 * @package    leantime
 * @subpackage core
 */
abstract class Controller
{
    use Eventhelpers;

    /**
     * @var Template
     */
    protected Template $tpl;

    /**
     * @var Language
     */
    protected Language $language;

    /**
     * @var IncomingRequest
     */
    protected IncomingRequest $incomingRequest;

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
        IncomingRequest $incomingRequest,
        Template $tpl,
        Language $language
    ) {
        self::dispatch_event('begin');

        $this->incomingRequest = $incomingRequest;
        $this->tpl = $tpl;
        $this->language = $language;

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
            throw new HttpResponseException(Frontcontroller::redirect(BASE_URL . "/errors/error501", 501));
        }
    }

    public function getResponse(): Response
    {
        return $this->response;
    }
}
