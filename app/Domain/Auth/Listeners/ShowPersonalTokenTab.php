<?php

namespace Leantime\Domain\Auth\Listeners;

/**
 * Injects the Personal Access Tokens tab into the user account settings page.
 */
class ShowPersonalTokenTab
{
    /**
     * Render the tab navigation item.
     */
    public function handle(mixed $payload): void
    {
        echo '<li><a href="#personalTokens"><i class="fa-solid fa-key"></i> '.__('tabs.personal_access_tokens').'</a></li>';
    }
}
