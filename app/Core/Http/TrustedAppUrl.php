<?php

namespace Leantime\Core\Http;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;

/**
 * TrustedAppUrl - the absolute application URL that is safe to put into emails.
 *
 * BASE_URL follows the Host header of the current request when LEAN_APP_URL is empty. That is
 * fine for in-app navigation but not for links that leave the request, such as emails: a client
 * can send any Host header, and a link built from it can point anywhere.
 *
 * Precedence:
 *  1. LEAN_APP_URL (config appUrl) when set — always wins.
 *  2. The stored setting {@see self::SETTING_KEY}, learned once from the installer or from the
 *     first owner/admin login, or saved by an admin (`setting:save`).
 *  3. Unknown (null).
 *
 * A learned value is never overwritten automatically. To change it set LEAN_APP_URL, or run
 * `php bin/leantime setting:save --key=system.trustedAppUrl --value=https://pm.example.com`.
 */
class TrustedAppUrl
{
    public const SETTING_KEY = 'system.trustedAppUrl';

    public const WARNING_MISSING = 'missing';

    public const WARNING_MISMATCH = 'mismatch';

    public function __construct(
        private Environment $config,
        private SettingRepository $settingsRepo,
    ) {}

    /**
     * The trusted absolute app URL without trailing slash, or null when none is known.
     */
    public function get(): ?string
    {
        $configuredUrl = self::normalize((string) $this->config->get('appUrl', ''));
        if ($configuredUrl !== null) {
            return $configuredUrl;
        }

        return $this->getStored();
    }

    /**
     * Whether LEAN_APP_URL is set (as opposed to a learned or missing URL).
     */
    public function isConfigured(): bool
    {
        return self::normalize((string) $this->config->get('appUrl', '')) !== null;
    }

    /**
     * Base for links in ordinary notification emails: the trusted URL when known, BASE_URL otherwise.
     *
     * Only for links that carry no secret. Secret-bearing links (password reset, invites) must use
     * {@see self::get()} and not be sent when it returns null.
     */
    public function forLinks(): string
    {
        return $this->get() ?? self::currentBaseUrl();
    }

    /**
     * Rewrites a URL built from BASE_URL so it starts with the trusted URL instead.
     *
     * URLs that don't start with BASE_URL (external links, already trusted links) are returned
     * unchanged, as is everything when no trusted URL is known.
     *
     * @param  string  $url  absolute URL, typically BASE_URL.'/some/path' or CURRENT_URL
     */
    public function rebase(string $url): string
    {
        $trustedUrl = $this->get();
        $currentBaseUrl = self::currentBaseUrl();

        if ($trustedUrl === null || $currentBaseUrl === '' || $currentBaseUrl === $trustedUrl) {
            return $url;
        }

        if ($url === $currentBaseUrl) {
            return $trustedUrl;
        }

        $remainder = substr($url, strlen($currentBaseUrl));
        $startsWithBase = str_starts_with($url, $currentBaseUrl)
            && in_array(substr($remainder, 0, 1), ['/', '#', '?'], true);

        if (! $startsWithBase) {
            return $url;
        }

        return $trustedUrl.$remainder;
    }

    /**
     * Records the URL the installation was performed on, unless a URL is already known.
     */
    public function learnFromInstall(Request $request): void
    {
        $this->learnFromRequest($request, 'installation');
    }

    /**
     * Records the request URL at an owner/admin login, unless a URL is already known.
     *
     * Logins by lower roles never teach the URL. When a stored URL exists and the admin signed in
     * on a different host, the stored value is kept and the difference is logged.
     *
     * @param  mixed  $role  the user's role (numeric key or role name)
     */
    public function learnFromAdminLogin(mixed $role, Request $request): void
    {
        if (! self::isAdminRole($role)) {
            return;
        }

        $this->learnFromRequest($request, 'administrator login');
    }

    /**
     * Which warning, if any, an owner/admin should see about the app URL on this request.
     *
     * @param  mixed  $role  the current user's role (numeric key or role name)
     * @return string|null self::WARNING_MISSING, self::WARNING_MISMATCH or null
     */
    public function adminWarning(mixed $role, Request $request): ?string
    {
        if (! self::isAdminRole($role) || $this->isConfigured()) {
            return null;
        }

        $storedUrl = $this->getStored();
        if ($storedUrl === null) {
            return self::WARNING_MISSING;
        }

        $requestUrl = self::urlFromRequest($request);
        if ($requestUrl !== null && $requestUrl !== $storedUrl) {
            return self::WARNING_MISMATCH;
        }

        return null;
    }

    /**
     * The scheme+host of a request as a URL, or null when it can't be trusted.
     *
     * Requests carrying X-Forwarded-Host from a client that is not a trusted proxy are rejected,
     * as are malformed hosts.
     */
    public static function urlFromRequest(Request $request): ?string
    {
        if ($request->headers->has('X-Forwarded-Host') && ! $request->isFromTrustedProxy()) {
            return null;
        }

        try {
            $requestUrl = $request->getSchemeAndHttpHost();
        } catch (\Throwable) {
            // Symfony throws SuspiciousOperationException for invalid Host headers
            return null;
        }

        return self::normalize($requestUrl);
    }

    /**
     * Normalizes an absolute http(s) URL: lower-case scheme and host, no trailing slash.
     *
     * @return string|null null when the value is not a usable absolute http(s) URL
     */
    public static function normalize(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }

        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);
        if (! in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        if (isset($parts['user']) || isset($parts['pass']) || isset($parts['query']) || isset($parts['fragment'])) {
            return null;
        }

        $host = strtolower($parts['host']);
        $isHostname = filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) !== false;
        $isIp = filter_var(trim($host, '[]'), FILTER_VALIDATE_IP) !== false;
        if (! $isHostname && ! $isIp) {
            return null;
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $path = rtrim($parts['path'] ?? '', '/');

        return $scheme.'://'.$host.$port.$path;
    }

    /**
     * Stores the request URL when nothing is known yet; logs when it differs from the stored one.
     */
    private function learnFromRequest(Request $request, string $reason): void
    {
        if ($this->isConfigured()) {
            return;
        }

        $requestUrl = self::urlFromRequest($request);
        if ($requestUrl === null) {
            return;
        }

        $storedUrl = $this->getStored();

        if ($storedUrl === null) {
            $this->settingsRepo->saveSetting(self::SETTING_KEY, $requestUrl);
            Log::info('Recorded '.$requestUrl.' as the application URL for links in emails ('.$reason.'). Set LEAN_APP_URL to change it.');

            return;
        }

        if ($storedUrl !== $requestUrl) {
            Log::info('Signed in on '.$requestUrl.' but email links use the recorded application URL '.$storedUrl.'. Set LEAN_APP_URL if the public URL has changed.');
        }
    }

    private function getStored(): ?string
    {
        $storedUrl = $this->settingsRepo->getSetting(self::SETTING_KEY, false);

        if (! is_string($storedUrl)) {
            return null;
        }

        return self::normalize($storedUrl);
    }

    private static function isAdminRole(mixed $role): bool
    {
        $roleLevel = Roles::getRoleLevel($role);
        $adminLevel = Roles::getRoleLevel(Roles::$admin);

        return $roleLevel !== false && $adminLevel !== false && $roleLevel >= $adminLevel;
    }

    private static function currentBaseUrl(): string
    {
        return defined('BASE_URL') ? rtrim((string) BASE_URL, '/') : '';
    }
}
