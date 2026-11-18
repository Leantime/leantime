<?php

namespace Leantime\Domain\Oauth\Hxcontrollers;

use Leantime\Core\Controller\HtmxController;
use Leantime\Domain\Oauth\Services\ConnectedApps as ConnectedAppsService;
use Symfony\Component\HttpFoundation\Response;

/**
 * The "Connected apps" list on the profile page, next to the personal access tokens.
 *
 *  - GET    /hx/oauth/connectedApps                       the list
 *  - DELETE /hx/oauth/connectedApps/disconnect/{clientId} disconnect one app, then the list
 */
class ConnectedApps extends HtmxController
{
    protected static string $view = 'oauth::partials.connectedApps';

    private ConnectedAppsService $connectedAppsService;

    public function init(ConnectedAppsService $connectedAppsService): void
    {
        $this->connectedAppsService = $connectedAppsService;
    }

    /**
     * List the signed-in user's connected apps.
     */
    public function get(): void
    {
        $this->tpl->assign('connectedApps', $this->connectedAppsService->getConnectedApps());
        $this->tpl->assign('mcpUrl', rtrim(BASE_URL, '/').'/mcp');
    }

    /**
     * Disconnect one app (DELETE only), then render the updated list.
     */
    public function disconnect(): ?Response
    {
        // State changes only on DELETE: the Frontcontroller routes any verb here, and GET skips the
        // cross-site origin check, so a link on another site could otherwise disconnect an app.
        if (! $this->incomingRequest->isMethod('DELETE')) {
            return $this->tpl->emptyResponse(405);
        }

        $clientId = (string) $this->incomingRequest->get('id');

        if ($this->connectedAppsService->disconnectApp($clientId)) {
            $this->tpl->setNotification(__('oauth.notification.disconnected'), 'success');
        } else {
            $this->tpl->setNotification(__('oauth.notification.disconnect_failed'), 'error');
        }

        $this->get();

        return null;
    }
}
