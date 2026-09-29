<?php

namespace Leantime\Domain\Users\Exceptions;

use Leantime\Core\Exceptions\LeantimeException;

/**
 * The personal webhook setting (URL + opt-in) could not be persisted, so none of
 * the submitted notification preferences were saved.
 *
 * Keeps LeantimeException's HTTP 500 / JSON-RPC -32603. The fixed message is
 * client-safe and never carries the webhook URL, whose path and query commonly
 * hold its secret; no previous exception is chained for the same reason (DB
 * exception messages embed the bound values).
 */
class WebhookSettingNotSavedException extends LeantimeException
{
    public function __construct()
    {
        parent::__construct('Personal webhook setting could not be saved.');
    }
}
