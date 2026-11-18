<?php

namespace Leantime\Core\Auth\OAuth\Models;

use Laravel\Passport\RefreshToken as PassportRefreshToken;

/**
 * Passport's RefreshToken model on Leantime's `zp_oauth_refresh_tokens` table (all Leantime tables carry the zp_ prefix).
 */
class RefreshToken extends PassportRefreshToken
{
    /**
     * @var string
     */
    protected $table = 'zp_oauth_refresh_tokens';
}
