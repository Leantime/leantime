<?php

namespace Leantime\Core\Auth\OAuth\Models;

use Laravel\Passport\AuthCode as PassportAuthCode;

/**
 * Passport's AuthCode model on Leantime's `zp_oauth_auth_codes` table (all Leantime tables carry the zp_ prefix).
 */
class AuthCode extends PassportAuthCode
{
    /**
     * @var string
     */
    protected $table = 'zp_oauth_auth_codes';
}
