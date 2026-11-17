<?php

namespace Leantime\Core\Auth\OAuth\Models;

use Laravel\Passport\Token as PassportToken;

/**
 * Passport's Token model on Leantime's `zp_oauth_access_tokens` table (all Leantime tables carry the zp_ prefix).
 */
class Token extends PassportToken
{
    /**
     * @var string
     */
    protected $table = 'zp_oauth_access_tokens';
}
