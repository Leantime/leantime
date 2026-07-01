<?php

namespace Leantime\Domain\Auth\Listeners;

/**
 * Renders the Personal Access Tokens tab content in the user account settings page.
 *
 * Loads via HTMX: the tab panel contains an hx-get that fetches the token list
 * from the Auth HxController on first reveal.
 */
class ShowPersonalTokenContent
{
    /**
     * Render the tab content panel with HTMX lazy-loading.
     */
    public function handle(mixed $payload): void
    {
        echo '<div id="personalTokens"
            hx-get="'.BASE_URL.'/hx/auth/personalTokens"
            hx-trigger="load"
            hx-swap="innerHTML">
        </div>';
    }
}
