<?php

namespace Leantime\Core\Auth\OAuth\Models;

use Laravel\Passport\Client as PassportClient;

/**
 * Passport's Client model on Leantime's `zp_oauth_clients` table (all Leantime tables carry the zp_ prefix).
 */
class Client extends PassportClient
{
    /**
     * @var string
     */
    protected $table = 'zp_oauth_clients';
}
