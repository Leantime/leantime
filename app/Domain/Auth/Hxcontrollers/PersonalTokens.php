<?php

namespace Leantime\Domain\Auth\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Auth\Services\AccessToken;

/**
 * HxController for Personal Access Token management.
 *
 * Provides HTMX endpoints for creating, listing, and revoking
 * personal access tokens from the user settings page.
 */
class PersonalTokens extends HtmxController
{
    protected static string $view = 'auth::partials.tokens';

    private AccessToken $tokenService;

    /**
     * Initialize the controller with dependencies.
     */
    public function init(AccessToken $tokenService): void
    {
        $this->tokenService = $tokenService;
    }

    /**
     * List all tokens for the current user.
     */
    public function get(): void
    {
        $tokens = $this->tokenService->getUserTokens(session('userdata.id'));
        $this->tpl->assign('tokens', $tokens);
    }

    /**
     * Create a new personal access token.
     *
     * @return mixed
     */
    public function create()
    {
        $name = $this->incomingRequest->request->get('name');

        if (empty($name)) {
            $this->tpl->setNotification(__('notifications.token_name_required'), 'error');

            return $this->tpl->emptyResponse(400);
        }

        $token = $this->tokenService->createToken(
            session('userdata.id'),
            $name
        );

        $this->tpl->setNotification(__('notifications.token_created'), 'success');

        // Return the token value in a modal for one-time display
        $this->tpl->assign('newToken', $token->token);

        return $this->tpl->displayPartial('auth.token-created');
    }

    /**
     * Delete a personal access token.
     */
    public function delete(): void
    {
        $id = $this->incomingRequest->get('id');

        if (! $this->tokenService->deleteToken((int) $id)) {
            $this->tpl->setNotification(__('notifications.token_not_found'), 'error');

            return;
        }

        $this->tpl->setNotification(__('notifications.token_deleted'), 'success');

        $this->get();
    }
}
