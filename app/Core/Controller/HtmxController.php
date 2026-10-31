<?php

namespace Leantime\Core\Controller;

use BadMethodCallException;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Events\Htmx\HtmxEvent;
use Leantime\Core\Events\Htmx\HtmxEvents;
use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\Language;
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

        /** @var Template $tpl */
        public Language $language,

    ) {
        self::dispatchEvent('begin');

        $this->incomingRequest = $incomingRequest;
        $this->tpl = $tpl;
        $this->response = app()->make(Response::class);

        // initialize
        if (method_exists($this, 'init')) {
            app()->call([$this, 'init']);
        }

        if (! isset(static::$view)) {
            throw new LogicException('HTMX Controllers must include the "$view" static property');
        }

        self::dispatchEvent('end', $this);
    }

    /**
     * Queue a client (HTMX) event on the HX-Trigger response header.
     *
     * @param  HtmxEvent|string  $eventName  An HtmxEvent enum case, an EmitsClientEvent class name
     *                                       (TicketUpdated::class) or a raw event name.
     */
    public function setHTMXEvent(HtmxEvent|string $eventName): void
    {
        $this->headers['HX-Trigger'] ??= [];
        $this->headers['HX-Trigger'][] = HtmxEvents::name($eventName);
    }

    /**
     * Queue one or more client (HTMX) events on the HX-Trigger response header.
     *
     * Domain data events usually need no call here: dispatching an EmitsClientEvent domain event
     * during an htmx request queues it automatically. Use this for UI commands (HtmxUiEvents) and
     * for responses that should announce a change no domain event covers.
     *
     * @param  HtmxEvent|string  ...$events  HtmxEvent enum cases, EmitsClientEvent class names or raw names.
     */
    public function emit(HtmxEvent|string ...$events): void
    {
        foreach ($events as $event) {
            $this->setHTMXEvent($event);
        }
    }

    /**
     * Gets the response
     *
     **/
    public function getResponse($fragment): Response
    {
        $this->response = $this->withQueuedHeaders(
            $this->tpl->displayFragment($this::$view, $fragment ?? '')
        );

        return $this->response;
    }

    /**
     * Execute an action on the controller. An action that builds its own Response
     * (displayRaw, emptyResponse, new Response, ...) still gets the queued headers.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed A Response, or the fragment key for getResponse()
     */
    public function callAction($method, $parameters)
    {
        $result = $this->{$method}($parameters);

        if ($result instanceof Response) {
            return $this->withQueuedHeaders($result);
        }

        return $result;
    }

    /**
     * Put the headers queued on this controller and on the template onto a response.
     *
     * HX-Trigger events from the response itself, the controller and the template are merged
     * (setting each bag separately would let the last one overwrite the others) and sent once.
     * A JSON HX-Trigger value set by hand is left alone, since it can't be merged with a list
     * of names.
     */
    private function withQueuedHeaders(Response $response): Response
    {
        $templateHeaders = $this->tpl->getHeaders();

        foreach ([$this->headers, $templateHeaders] as $headerBag) {
            foreach ($headerBag as $key => $value) {
                if ($key === 'HX-Trigger') {
                    continue;
                }
                $response->headers->set($key, is_array($value) ? implode(',', $value) : $value);
            }
        }

        $existingTrigger = trim((string) $response->headers->get('HX-Trigger', ''));
        if (str_starts_with($existingTrigger, '{')) {
            return $response;
        }

        $triggerEvents = array_merge(
            self::triggerNames($existingTrigger),
            self::triggerNames($this->headers['HX-Trigger'] ?? []),
            self::triggerNames($templateHeaders['HX-Trigger'] ?? [])
        );

        if (! empty($triggerEvents)) {
            $response->headers->set('HX-Trigger', HtmxEvents::triggerHeader($triggerEvents));
        }

        return $response;
    }

    /**
     * The event names in a queued HX-Trigger value: a list of names, or a comma-separated string
     * (set with setResponseHeader() or straight on a header bag).
     *
     * @param  array<int, mixed>|string  $value
     * @return array<int, mixed>
     */
    private static function triggerNames(array|string $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), fn (string $name) => $name !== ''));
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
