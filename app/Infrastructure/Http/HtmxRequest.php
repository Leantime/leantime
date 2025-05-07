<?php

declare(strict_types=1);

namespace Leantime\Infrastructure\Http;

use Leantime\Core\Http\IncomingRequest;

class HtmxRequest extends IncomingRequest
{
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
     */
    public function getReferrer(): string
    {
        return $this->headers->get('Hx-Current-URL', '');
    }

    /**
     * Indicates if the request is for history restoration after a miss in the local history cache
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
     */
    public function getPromptResponse(): string
    {
        return $this->headers->get('Hx-Prompt', '');
    }

    /**
     * The id of the target element if it exists.
     */
    public function getTarget(): string
    {
        return $this->headers->get('Hx-Target', '');
    }

    /**
     * The name of the triggered element if it exists.
     */
    public function getTriggerName(): string
    {
        return $this->headers->get('Hx-Trigger-Name');
    }

    /**
     * The id of the triggered element if it exists.
     */
    public function getTriggerId(): string
    {
        return $this->headers->get('Hx-Trigger', '');
    }
}
