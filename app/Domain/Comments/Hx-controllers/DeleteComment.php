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
    public static $view = 'Comments::components.reply';

    /**
     * @param Tickets $tickets
     * @return void
     */
    public function init(
        Tickets $tickets,
    ) {
    }

    /**
     * @param IncomingRequest $incomingRequest
     * @return void
     */
    public function post(IncomingRequest $incomingRequest)
    {
        $this->tickets->deleteComment($incomingRequest->get('id'));

        echo '';
    }
}
