<?php

namespace Leantime\Core\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as BaseVerifier;

/**
 * Verifies CSRF tokens on state-changing requests.
 *
 * Extends Laravel's built-in CSRF middleware. The token is read from
 * the session and checked against the `_token` POST field or the
 * `X-CSRF-TOKEN` header (used by HTMX via hx-headers on the body tag).
 *
 * Routes that use their own authentication (API keys, webhooks, cron)
 * are excluded since they don't rely on browser sessions.
 */
class VerifyCsrfToken extends BaseVerifier
{
    /**
     * Routes excluded from CSRF verification.
     *
     * API endpoints use API-key / Sanctum auth, not session cookies.
     * Cron and webhook endpoints are server-to-server calls.
     *
     * @var array<int, string>
     */
    protected $except = [
        'api/*',
        'cron/*',
        'webhook/*',
        'install',
        'install/*',
    ];
}
