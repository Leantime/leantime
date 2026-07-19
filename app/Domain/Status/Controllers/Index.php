<?php

namespace Leantime\Domain\Status\Controllers;

use Leantime\Core\Configuration\AppSettings;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Http\IncomingRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Public, unauthenticated instance status / discovery endpoint — GET /status.
 *
 * The mobile app calls this at connect time to discover which login methods the
 * instance offers (password / ldap / oidc), so it can show the right affordances
 * — notably the OIDC "Sign in with SSO" redirect, which stays dormant in the app
 * until a backend advertises it here. See the mobile client's
 * connectionService::fetchPublicStatus and
 * docs/backend-mobile-auth-bridge-plan.md for the contract.
 *
 * SECURITY — this endpoint is UNAUTHENTICATED, so it returns ONLY the safe,
 * minimal tier: auth methods, the core version + instance name, provider labels,
 * and the min app version. It deliberately does NOT list installed plugins,
 * plugin versions, or the db version — an unauthenticated inventory of those is a
 * recon gift (CVE matching). Those stay behind auth (the authenticated
 * mobileStatus). Keep any `publicStatus` filter additions to this safe tier.
 *
 * Route 'status.index' is allow-listed public in AuthCheck.
 */
class Index extends Controller
{
    /**
     * Keys that must NEVER appear in the unauthenticated response, even if a
     * publicStatus filter (or a future edit) adds them — a recon-risk inventory.
     * Stripped after the filter runs, as defense in depth.
     */
    private const SENSITIVE_KEYS = ['plugins', 'pluginVersions', 'installedPlugins', 'dbVersion', 'db_version'];

    private Environment $config;

    private AppSettings $appSettings;

    private IncomingRequest $request;

    /**
     * init - inject config, app settings, and the incoming request.
     */
    public function init(Environment $config, AppSettings $appSettings, IncomingRequest $request): void
    {
        $this->config = $config;
        $this->appSettings = $appSettings;
        $this->request = $request;
    }

    /**
     * Return the public discovery payload: enabled auth methods, the OIDC login
     * URL (when enabled), instance name, core version, and min app version — the
     * safe unauthenticated tier only. Never plugin inventory / versions / db
     * version (see the class-level security note).
     */
    public function get(array $params): Response
    {
        $oidcEnabled = (bool) $this->config->oidcEnable;
        $ldapEnabled = $this->config->useLdap === true && extension_loaded('ldap');

        // password is always available; ldap/oidc only when configured.
        $authMethods = ['password'];
        if ($ldapEnabled) {
            $authMethods[] = 'ldap';
        }
        if ($oidcEnabled) {
            $authMethods[] = 'oidc';
        }

        $payload = [
            'mobileAuthEnabled' => true,
            'instanceName' => (string) ($this->config->sitename ?: 'Leantime'),
            'version' => $this->appSettings->appVersion,
            'minAppVersion' => null,
            'authMethods' => $authMethods,
            'ssoProviders' => [],
        ];

        if ($oidcEnabled) {
            // Generic-OIDC login initiation URL (core). The app opens this in the
            // system auth browser; the mobile branch is triggered by its own query
            // params (see Oidc\Controllers\Login).
            $payload['oidcLoginUrl'] = $this->request->getSchemeAndHttpHost().'/oidc/login';
        }

        // AdvancedAuth (or other plugins) can append named SSO providers to the
        // PUBLIC-safe payload (labels + login URLs only) via this filter, without
        // core knowing about them. Filter handlers MUST preserve the public-safe
        // contract — never add secrets, plugin inventory, or versions here.
        $payload = self::dispatchFilter('publicStatus', $payload, ['request' => $this->request]);

        // Defense in depth: this endpoint is unauthenticated, so strip any
        // known-sensitive keys a misbehaving filter (or a future edit) might have
        // added. The recon-risk inventory must NEVER reach an unauthenticated
        // caller, even if a plugin gets the contract wrong.
        foreach (self::SENSITIVE_KEYS as $sensitive) {
            unset($payload[$sensitive]);
        }

        return new JsonResponse($payload);
    }
}
