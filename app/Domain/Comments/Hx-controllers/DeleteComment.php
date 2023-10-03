<?php

use Leantime\Domain\Tickets\Services\Tickets;

/**
 *
 */

/**
 *
 */
class DeleteComment extends HtmxController
{
    public static string $view = 'Comments::components.reply';

    /**
     * @param Tickets $tickets
     * @return void
     */
    public function init(
        Tickets $tickets,
    ): void {
    }

    /**
     * @param IncomingRequest $incomingRequest
     * @return void
     */
    public function post(IncomingRequest $incomingRequest): void
    {
        $this->tickets->deleteComment($incomingRequest->get('id'));

        echo '';
    }
}
