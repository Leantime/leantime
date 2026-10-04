<?php

namespace Unit\app\Domain\Auth\Services;

use Illuminate\Session\SessionManager;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Auth\Repositories\AccessTokenRepository;
use Leantime\Domain\Auth\Repositories\Auth as AuthRepository;
use Leantime\Domain\Auth\Services\Auth as AuthService;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Users\Repositories\Users as UserRepository;
use Unit\TestCase;

/**
 * Unit tests for the pure/business logic extracted into the Auth service during
 * the thin-controller refactor (resolveSafeRedirect, shouldHideLoginForm,
 * checkPasswordStrength, resetPassword).
 */
class AuthServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Auth service with mocked dependencies. The Environment and
     * Setting repository can be overridden so config/setting driven behavior can
     * be exercised.
     */
    private function makeService(
        ?EnvironmentCore $config = null,
        ?SettingRepository $settingsRepo = null,
        ?AuthRepository $authRepo = null
    ): AuthService {
        return new AuthService(
            $config ?? $this->make(EnvironmentCore::class),
            $this->make(SessionManager::class),
            $this->make(LanguageCore::class),
            $settingsRepo ?? $this->make(SettingRepository::class),
            $authRepo ?? $this->make(AuthRepository::class),
            $this->make(UserRepository::class),
            $this->make(AccessTokenRepository::class),
        );
    }

    public function test_resolve_safe_redirect_defaults_to_dashboard(): void
    {
        $service = $this->makeService();

        $this->assertSame(BASE_URL.'/dashboard/home', $service->resolveSafeRedirect(null));
        $this->assertSame(BASE_URL.'/dashboard/home', $service->resolveSafeRedirect(''));
        $this->assertSame(BASE_URL.'/dashboard/home', $service->resolveSafeRedirect('/'));
    }

    public function test_resolve_safe_redirect_allows_internal_path(): void
    {
        $service = $this->makeService();

        $this->assertSame(BASE_URL.'/tickets/showAll', $service->resolveSafeRedirect('tickets/showAll'));
    }

    public function test_resolve_safe_redirect_blocks_external_url(): void
    {
        $service = $this->makeService();

        // An absolute external URL is a valid URL, so it is rejected and the
        // default dashboard target is returned instead.
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect('https://evil.example.com')
        );
    }

    public function test_resolve_safe_redirect_allows_same_origin_absolute_url(): void
    {
        $service = $this->makeService();

        // Same-origin absolute URL — must be treated the same as a relative
        // path by stripping the BASE_URL prefix. This is the exact scenario
        // the maintainer flagged: the login form often submits a full
        // absolute URL in the redirectUrl hidden field.
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect(BASE_URL.'/dashboard/home')
        );
    }

    public function test_resolve_safe_redirect_allows_same_origin_absolute_url_with_deep_path(): void
    {
        $service = $this->makeService();

        $this->assertSame(
            BASE_URL.'/tickets/showAll',
            $service->resolveSafeRedirect(BASE_URL.'/tickets/showAll')
        );
    }

    public function test_resolve_safe_redirect_allows_url_encoded_same_origin_absolute_url(): void
    {
        $service = $this->makeService();

        // URL-encoded same-origin absolute URL — rawurldecode is called first,
        // then BASE_URL is stripped.
        $this->assertSame(
            BASE_URL.'/tickets/showAll',
            $service->resolveSafeRedirect(urlencode(BASE_URL.'/tickets/showAll'))
        );
    }

    public function test_resolve_safe_redirect_rejects_external_url_disguised_with_base_url_prefix(): void
    {
        $service = $this->makeService();

        // An external URL whose path happens to start with the same characters
        // as BASE_URL — str_starts_with won't match because the scheme+host
        // differ. This gets rejected as external.
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect('https://evil.example.com/'.BASE_URL.'/dashboard/home')
        );
    }

    public function test_resolve_safe_redirect_rejects_host_prefix_without_a_boundary(): void
    {
        $service = $this->makeService();

        // The real prefix hazard: a host that merely *begins* with our host, e.g.
        // BASE_URL https://host vs https://hostile.example.com. A bare
        // str_starts_with($url, BASE_URL) strips the prefix and rewrites this into the
        // bogus internal path /ile.example.com/pwn instead of rejecting it outright.
        // Stripping only on a boundary (end, '/', '?', '#') keeps it external.
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect(BASE_URL.'ile.example.com/pwn')
        );
    }

    public function test_resolve_safe_redirect_returns_dashboard_for_base_url_itself(): void
    {
        $service = $this->makeService();

        // Exactly BASE_URL (with and without a trailing slash) has no path to go to —
        // it must fall back to the dashboard rather than the bare app root.
        $this->assertSame(BASE_URL.'/dashboard/home', $service->resolveSafeRedirect(BASE_URL));
        $this->assertSame(BASE_URL.'/dashboard/home', $service->resolveSafeRedirect(BASE_URL.'/'));
    }

    public function test_resolve_safe_redirect_blocks_logout_including_variants(): void
    {
        $service = $this->makeService();

        // Redirecting to logout right after login is a forced-logout loop. An exact
        // string match on '/auth/logout' is walkable with a trailing slash, a query
        // string or different casing, so the normalized path is what gets compared.
        foreach ([
            '/auth/logout',
            '/auth/logout/',
            'auth/logout',
            '/auth/logout?next=/dashboard/home',
            '/auth/logout#x',
            '/AUTH/logout',
            BASE_URL.'/auth/logout',
        ] as $variant) {
            $this->assertSame(
                BASE_URL.'/dashboard/home',
                $service->resolveSafeRedirect($variant),
                sprintf('logout variant "%s" must not be an accepted redirect target', $variant)
            );
        }
    }

    public function test_resolve_safe_redirect_strips_control_characters(): void
    {
        $service = $this->makeService();

        // Encoded CR/LF must never reach the Location header, and leading whitespace
        // must not be usable to pad a protocol-relative URL past the '//' guard.
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect('%09//evil.example.com')
        );
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect(' //evil.example.com')
        );
        $this->assertStringNotContainsString(
            "\r",
            $service->resolveSafeRedirect('tickets/showAll%0d%0aSet-Cookie:x')
        );
        $this->assertStringNotContainsString(
            "\n",
            $service->resolveSafeRedirect('tickets/showAll%0d%0aSet-Cookie:x')
        );
    }

    public function test_resolve_safe_redirect_preserves_plus_in_query_strings(): void
    {
        $service = $this->makeService();

        // rawurldecode (not urldecode) is used precisely so a '+' in a query string
        // survives instead of silently becoming a space.
        $this->assertSame(
            BASE_URL.'/tickets/showAll?searchTerm=a+b',
            $service->resolveSafeRedirect('tickets/showAll?searchTerm=a+b')
        );
    }

    public function test_resolve_safe_redirect_rejects_protocol_relative_url(): void
    {
        $service = $this->makeService();

        // Protocol-relative URL (//attacker.com) — FILTER_VALIDATE_URL
        // treats these as valid URLs, so they are correctly rejected
        // and the default dashboard redirect is returned.
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect('//attacker.com')
        );
    }

    public function test_resolve_safe_redirect_rejects_backslash_protocol_trick(): void
    {
        $service = $this->makeService();

        // Backslash variant (\/\/attacker.com) — some parsers treat
        // this as a protocol-relative URL. Verify it is rejected.
        $this->assertSame(
            BASE_URL.'/dashboard/home',
            $service->resolveSafeRedirect('\/\/attacker.com')
        );
    }

    public function test_check_password_strength_rejects_weak_and_accepts_strong(): void
    {
        $service = $this->makeService();

        $this->assertFalse($service->checkPasswordStrength('weak'));
        $this->assertFalse($service->checkPasswordStrength('alllowercase1!'));
        $this->assertFalse($service->checkPasswordStrength('NoNumber!!'));
        $this->assertFalse($service->checkPasswordStrength('NoSpecial123'));
        $this->assertFalse($service->checkPasswordStrength('Aa1!aaa')); // 7 chars
        $this->assertTrue($service->checkPasswordStrength('StrongPass1!'));
    }

    public function test_reset_password_reports_mismatch(): void
    {
        $service = $this->makeService();

        $this->assertSame('mismatch', $service->resetPassword('', '', 'hash'));
        $this->assertSame('mismatch', $service->resetPassword('StrongPass1!', 'Different1!', 'hash'));
    }

    public function test_reset_password_reports_weak(): void
    {
        $service = $this->makeService();

        $this->assertSame('weak', $service->resetPassword('weak', 'weak', 'hash'));
    }

    public function test_reset_password_success_and_error_map_to_repository(): void
    {
        $successRepo = $this->make(AuthRepository::class, [
            'changePW' => fn () => true,
        ]);
        $this->assertSame('success', $this->makeService(null, null, $successRepo)
            ->resetPassword('StrongPass1!', 'StrongPass1!', 'hash'));

        $failRepo = $this->make(AuthRepository::class, [
            'changePW' => fn () => false,
        ]);
        $this->assertSame('error', $this->makeService(null, null, $failRepo)
            ->resetPassword('StrongPass1!', 'StrongPass1!', 'hash'));
    }

    public function test_should_hide_login_form_when_setting_on(): void
    {
        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => 'on',
        ]);

        $this->assertTrue($this->makeService(null, $settingsRepo)->shouldHideLoginForm());
    }

    public function test_should_hide_login_form_falls_back_to_config(): void
    {
        $config = new EnvironmentCore;
        $config->set('disableLoginForm', true);

        $settingsRepo = $this->make(SettingRepository::class, [
            'getSetting' => fn () => false,
        ]);

        $this->assertTrue($this->makeService($config, $settingsRepo)->shouldHideLoginForm());

        $config2 = new EnvironmentCore;
        $config2->set('disableLoginForm', false);

        $this->assertFalse($this->makeService($config2, $settingsRepo)->shouldHideLoginForm());
    }

    public function test_login_input_placeholder_depends_on_ldap(): void
    {
        $ldapConfig = new EnvironmentCore;
        $ldapConfig->set('useLdap', true);
        $this->assertSame(
            'input.placeholders.enter_email_or_username',
            $this->makeService($ldapConfig)->getLoginInputPlaceholder()
        );

        $noLdapConfig = new EnvironmentCore;
        $noLdapConfig->set('useLdap', false);
        $this->assertSame(
            'input.placeholders.enter_email',
            $this->makeService($noLdapConfig)->getLoginInputPlaceholder()
        );
    }

    /**
     * The login page caches the logged-out localization (server default timezone and formats) in the
     * session. Logging in must drop that cache so the user's own timezone is loaded; otherwise
     * calendars and the server disagree on the timezone and dropped events land hours off.
     */
    public function test_set_user_session_clears_the_logged_out_localization_cache(): void
    {
        session(['localization.cached' => true, 'usersettings.timezone' => null]);

        $authRepo = $this->make(AuthRepository::class, ['updateUserSession' => fn () => true]);

        $this->makeService(authRepo: $authRepo)->setUserSession([
            'id' => 1,
            'firstname' => 'Test',
            'username' => 'test@leantime.io',
            'password' => 'hash',
            'profileId' => 0,
            'clientId' => 0,
            'role' => 20,
            'settings' => '',
            'twoFAEnabled' => false,
            'twoFASecret' => '',
            'createdOn' => '2026-01-01 00:00:00',
            'modified' => '2026-01-01 00:00:00',
        ]);

        $this->assertFalse(session()->has('localization.cached'));
    }

    /**
     * Behind a reverse proxy REMOTE_ADDR is the proxy; the failed-login log must carry the client's
     * address from the trusted X-Forwarded-For header instead (#3779).
     */
    public function test_failed_login_log_records_the_client_ip_behind_a_trusted_proxy(): void
    {
        $previousProxies = \Illuminate\Http\Request::getTrustedProxies();
        $previousHeaders = \Illuminate\Http\Request::getTrustedHeaderSet();

        try {
            \Illuminate\Http\Request::setTrustedProxies(['10.0.0.0/8'], \Illuminate\Http\Request::HEADER_X_FORWARDED_FOR);
            $request = \Illuminate\Http\Request::create('/auth/login', 'POST', [], [], [], [
                'REMOTE_ADDR' => '10.0.0.5',
                'HTTP_X_FORWARDED_FOR' => '203.0.113.7',
            ]);
            app()->instance('request', $request);

            \Illuminate\Support\Facades\Log::shouldReceive('info')
                ->once()
                ->with(\Mockery::on(fn (string $message) => str_contains($message, '[203.0.113.7]') && str_contains($message, 'someone@example.com')));

            $logFailedLogin = new \ReflectionMethod(AuthService::class, 'logFailedLogin');
            $logFailedLogin->invoke($this->makeService(), 'someone@example.com');
        } finally {
            \Illuminate\Http\Request::setTrustedProxies($previousProxies, $previousHeaders);
        }
    }
}
