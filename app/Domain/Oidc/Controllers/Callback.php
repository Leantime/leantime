<?php

namespace Leantime\Domain\Oidc\Controllers;

use Illuminate\Support\Facades\Log;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Oidc\Services\Oidc as OidcService;
use Symfony\Component\HttpFoundation\Response;

class Callback extends Controller
{
    /**
     * Longest provider error description shown to the user; anything longer is cut off.
     */
    private const MAX_ERROR_DESCRIPTION_LENGTH = 300;

    private OidcService $oidc;

    /**
     * Initializes dependencies.
     */
    public function init(OidcService $oidc): void
    {
        $this->oidc = $oidc;
    }

    /**
     * Handles the redirect back from the OIDC provider.
     *
     * On success the provider sends `code` + `state`. When it refuses the authorization request
     * (missing PKCE, unknown scope, denied consent …) it sends `error` + `error_description`
     * instead (RFC 6749 4.1.2.1). Every failure ends on the login page with a readable message.
     *
     * @param  array  $params  Request parameters
     */
    public function get(array $params): Response
    {
        $providerError = $this->incomingRequest->query('error');
        $code = $this->incomingRequest->query('code');
        $state = $this->incomingRequest->query('state');

        if (is_string($providerError) && $providerError !== '') {
            $providerErrorDescription = $this->incomingRequest->query('error_description');
            $providerErrorDescription = is_string($providerErrorDescription) ? $providerErrorDescription : '';

            Log::warning('OIDC: the provider returned an error to the callback', [
                'error' => $providerError,
                'error_description' => $providerErrorDescription,
            ]);

            $shownError = trim($providerError.': '.mb_substr($providerErrorDescription, 0, self::MAX_ERROR_DESCRIPTION_LENGTH), ': ');

            return $this->backToLogin(sprintf($this->language->__('oidc.error.providerError'), $shownError));
        }

        if (! is_string($code) || $code === '' || ! is_string($state) || $state === '') {
            Log::warning('OIDC: callback reached without an authorization code or state');

            return $this->backToLogin($this->language->__('oidc.error.missingCode'));
        }

        try {
            return $this->oidc->callback($code, $state);
        } catch (\Exception $e) {
            return $this->backToLogin($e->getMessage());
        }
    }

    /**
     * Shows the message on the login page. The login page renders notifications as raw HTML and
     * these messages can carry text from the provider or the URL, so they are escaped here.
     */
    private function backToLogin(string $message): Response
    {
        $this->tpl->setNotification(htmlspecialchars($message, ENT_QUOTES), 'danger', 'oidc_error');

        return Frontcontroller::redirect(BASE_URL.'/auth/login');
    }
}
