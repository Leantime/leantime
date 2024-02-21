<?php

namespace kamermans\OAuth2\Exception;

use GuzzleHttp\Exception\TransferException;

class ReauthorizationRequestException extends ReauthorizationException
{
    public function __construct($message, TransferException $guzzleException)
    {
        parent::__construct($message, 0, $guzzleException);
    }

    /**
     * Get the Guzzle Exception that was thrown while trying to reauthorize.
     *
     * @return GuzzleHttp\Exception\TransferException
     */
    public function getGuzzleException()
    {
        return $this->getPrevious();
    }
}
