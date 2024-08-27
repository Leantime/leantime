<?php

declare(strict_types=1);

namespace Leantime\Core\Http;

/**
 *
 */
class HtmxRequest extends IncomingRequest
{
    /**
     * @param array                $query      The GET parameters
     * @param array                $request    The POST parameters
     * @param array                $attributes The request attributes (parameters parsed from the PATH_INFO, ...)
     * @param array                $cookies    The COOKIE parameters
     * @param array                $files      The FILES parameters
     * @param array                $server     The SERVER parameters
     * @param string|resource|null $content    The raw body data
     */
    public function __construct(array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null)
    {
        parent::__construct($query, $request, $attributes, $cookies, $files, $server, $content);

        if (! str_starts_with($path = $this->getPathInfo(), '/hx/')) {
            return;
        }

        $this->setRequestDest(substr($path, 3));
    }

    /**
     * Get HTMX request information
     *
     * @return array[string|bool]
     */
    public function getHtmxRequestVars(): array
    {
        return [
            'boosted' => $this->isBoosted(),
            'referrer' => $this->getReferrer(),
            'isHistoryRestoreRequest' => $this->isHistoryRestoreRequest(),
            'prompt' => $this->getPromptResponse(),
            'target' => $this->getTarget(),
            'triggerName' => $this->getTriggerName(),
            'triggerId' => $this->getTriggerId(),
        ];
    }

    /**
     * Indicates that the request is via an element using hx-boost
     *
     * @return bool
     */
    public function isBoosted(): bool
    {
        return filter_var(
            $this->headers->get('Hx-Boost', 'false'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * The Current URL of the browser when the htmx request was made.
     *
     * @return string
     */
    public function getReferrer(): string
    {
        return $this->headers->get('Hx-Current-URL', '');
    }

    /**
     * Indicates if the request is for history restoration after a miss in the local history cache
     *
     * @return bool
     */
    public function isHistoryRestoreRequest(): bool
    {
        return filter_var(
            $this->headers->get('Hx-History-Restore-Request', 'false'),
            FILTER_VALIDATE_BOOLEAN
        );
    }

    /**
     * The user response to an hx-prompt.
     *
     * @return string
     */
    public function getPromptResponse(): string
    {
        return $this->headers->get('Hx-Prompt', '');
    }

    /**
     * The id of the target element if it exists.
     *
     * @return string
     */
    public function getTarget(): string
    {
        return $this->headers->get('Hx-Target', '');
    }

    /**
     * The name of the triggered element if it exists.
     *
     * @return string
     */
    public function getTriggerName(): string
    {
        return $this->headers->get('Hx-Trigger-Name');
    }

    /**
     * The id of the triggered element if it exists.
     *
     * @return string
     */
    public function getTriggerId(): string
    {
        return $this->headers->get('Hx-Trigger', '');
    }
}
