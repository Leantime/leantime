<?php

namespace Leantime\Domain\Oauth\Controllers;

use Leantime\Core\Http\IncomingRequest;
use Leantime\Core\UI\Template;
use Leantime\Domain\Oauth\Services\OAuthServer;
use Symfony\Component\HttpFoundation\Response;

/**
 * The consent step of the OAuth flow.
 *
 *  - GET  /oauth/authorize        show "<App> wants to access your Leantime account"
 *  - POST /oauth/authorize        approve: redirect back to the app with a code
 *  - POST /oauth/authorize/deny   deny: redirect back with access_denied
 *
 * These are ordinary web routes, so AuthCheck has already signed the user in (password, SSO and
 * two-factor) before any of this runs; it remembers the authorize URL so every sign-in path comes
 * back here.
 */
class Authorize
{
    public function __construct(
        private OAuthServer $oauthServer,
        private Template $tpl,
    ) {}

    public function show(IncomingRequest $request): Response
    {
        $consent = $this->oauthServer->startAuthorization($request, (int) session('userdata.id'));

        if ($consent instanceof Response) {
            return $consent;
        }

        $this->tpl->assign('clientName', $consent['clientName']);
        $this->tpl->assign('redirectHost', $consent['redirectHost']);
        $this->tpl->assign('scopes', $consent['scopes']);
        $this->tpl->assign('authToken', $consent['authToken']);
        $this->tpl->assign('userName', trim(session('userdata.name') ?? ''));
        $this->tpl->assign('userEmail', session('userdata.mail') ?? '');

        $response = $this->tpl->display('oauth.authorize', 'entry');

        // Never let another site frame the consent screen (clickjacking an "Allow" click).
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy', "frame-ancestors 'none'");
        $response->headers->set('Cache-Control', 'no-store');

        return $response;
    }

    public function approve(IncomingRequest $request): Response
    {
        return $this->complete($request, approved: true);
    }

    public function deny(IncomingRequest $request): Response
    {
        return $this->complete($request, approved: false);
    }

    private function complete(IncomingRequest $request, bool $approved): Response
    {
        $postedAuthToken = $request->request->get('auth_token');

        $response = $this->oauthServer->completeAuthorization(
            is_string($postedAuthToken) ? $postedAuthToken : '',
            (int) session('userdata.id'),
            $approved
        );

        if ($response !== null) {
            return $response;
        }

        $this->tpl->assign('clientName', '');
        $this->tpl->assign('expired', true);

        return $this->tpl->display('oauth.authorize', 'entry', 400);
    }
}
