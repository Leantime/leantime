<?php

use leantime\domain\services\tickets;

class DeleteComment extends HtmxController
{
    public static $view = 'comments::components.reply';

    public function init(
        private tickets $tickets,
    ) {
    }

    public function post(IncomingRequest $incomingRequest)
    {
        $this->tickets->deleteComment($incomingRequest->get('id'));

        echo '';
    }
}
