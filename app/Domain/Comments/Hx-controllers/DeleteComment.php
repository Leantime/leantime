<?php

use Leantime\Domain\Tickets\Services\Tickets;

class DeleteComment extends HtmxController
{
    public static $view = 'Comments::components.reply';

    public function init(
        Tickets $tickets,
    ) {
    }

    public function post(IncomingRequest $incomingRequest)
    {
        $this->tickets->deleteComment($incomingRequest->get('id'));

        echo '';
    }
}
