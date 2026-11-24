<?php

namespace Leantime\Domain\Oidc\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Lets a plugin refuse an OIDC sign-in before Leantime looks up, creates or signs in the account.
 *
 * The payload is the refusal: null to let the sign-in continue, or the message to show on the
 * login page. A listener that refuses returns its message; one that has nothing to say returns
 * the payload unchanged, so an earlier refusal stands. AdvancedAuth uses this for its
 * "Restrict to specific domain" setting.
 *
 *     EventDispatcher::add_filter_listener(OidcSignInFilter::class,
 *         fn (?string $refusal, OidcSignInFilter $filter) => $refusal ?? $myCheck($filter->email));
 */
final class OidcSignInFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  string|null  $refusal  Null to allow; the message shown on the login page to refuse.
     * @param  string  $email  The email address the provider signed in, which the account is matched by.
     * @param  array<string, mixed>  $claims  The provider's claims (id_token / userinfo).
     */
    public function __construct(
        public ?string $refusal,
        public readonly string $email,
        public readonly array $claims = [],
    ) {}

    /**
     * The refusal threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->refusal;
    }
}
