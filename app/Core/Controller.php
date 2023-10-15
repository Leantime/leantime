<?php

namespace Leantime\Core;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Template;
use Leantime\Core\Events;
use Leantime\Core\Language;

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
     * @var template
     */
    protected Template $tpl;

    /**
     * @var language
     */
    protected Language $language;

    /**
     * @var IncomingRequest
     */
    protected IncomingRequest $incomingRequest;

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     * @param IncomingRequest $incomingRequest The request to be initialized.
     * @param template        $tpl             The template to be initialized.
     * @param language        $language        The language to be initialized.
     * @throws BindingResolutionException
     */
    public function __construct(
        IncomingRequest $incomingRequest,
        template $tpl,
        language $language
    ) {
        self::dispatch_event('begin');

        $this->incomingRequest = $incomingRequest;
        $this->tpl = $tpl;
        $this->language = $language;

        /**
         * This is placed here because the session isn't started yet when
         * AppSettings::loadSettings() is called.  Plus, this is mostly cosmetic/UI
         * change and should not apply to things such as date/times stored in the DB.
         * 
         * This also doesn't effect JS date-time any, only PHP calls for date-time, as
         * they use incompatible formats.
         */
        if (isset($_SESSION['userdata']) && isset($_SESSION['userdata']['id'])) {
            $userId = $_SESSION['userdata']['id'];

            if (isset($_SESSION['usersettings.' . $userId . '.timezone'])) {
                date_default_timezone_set($_SESSION['usersettings.' . $userId . '.timezone']);
            }
        }

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
            /**
             * @todo non GET requests should only be accessible from HTMX and API requests
             * if ($method !== 'get') && ! $incomingRequest instanceof HtmxRequest|ApiRequest) {
             *    self::redirect(BASE_URL . "/errors/error400", 400);
             * }
             */

            $this->$method($params);
        } else {
            $this->run();
        }
    }
}
