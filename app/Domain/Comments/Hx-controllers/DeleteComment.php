<?php

use Leantime\Core\HtmxController;
use Leantime\Core\IncomingRequest;
use Leantime\Domain\Tickets\Services\Tickets;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class DeleteComment extends HTMXController
{
    public static string $view = 'Comments::components.reply';

    /**
     * @param Tickets $tickets
     * @return void
     */
    public function init(
        Tickets $tickets,
    ): void {
        $this->tickets = $tickets;
    }

    /**
     * @param IncomingRequest $incomingRequest
     * @return Response
     */
    public function post(IncomingRequest $incomingRequest): Response
    {
        $this->tickets->deleteComment($incomingRequest->get('id'));

        return new Response();
    }
}
