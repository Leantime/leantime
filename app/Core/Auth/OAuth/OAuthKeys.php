<?php

namespace Leantime\Core\Auth\OAuth;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Contracts\Encryption\StringEncrypter;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use RuntimeException;

/**
 * The RSA key pair that signs OAuth access tokens. It needs no setup.
 *
 * Resolution order:
 *  1. LEAN_OAUTH_PRIVATE_KEY / LEAN_OAUTH_PUBLIC_KEY (PEM text or a path to a PEM file), when set.
 *  2. The pair stored in zp_settings (private key encrypted with the app encrypter).
 *  3. Neither: generate a pair and store it.
 *
 * The database is the default store because it is the one place every deployment already
 * persists and shares: the Docker image only persists storage/logs, cloud runs on several nodes,
 * and every cloud workspace gets its own pair this way.
 */
class OAuthKeys
{
    public const SETTING_KEY = 'oauth.signingKeys';

    private const KEY_BITS = 2048;

    /**
     * @var array{private: string, public: string, source: string, createdAt: ?string}|null
     */
    private ?array $resolvedKeys = null;

    public function __construct(
        private Environment $config,
        private DatabaseManager $db,
        private StringEncrypter $encrypter,
    ) {}

    /**
     * The active key pair, generating and storing one on first use.
     *
     * @return array{private: string, public: string, source: string, createdAt: ?string}
     *
     * @throws RuntimeException When no key pair can be loaded or generated.
     */
    public function keys(): array
    {
        return $this->resolvedKeys ??= $this->fromEnvironment()
            ?? $this->fromDatabase()
            ?? $this->generateAndStore();
    }

    /**
     * The secret that encrypts authorization code and refresh token payloads (league encrypts them,
     * and they carry the user id). Derived from the signing private key, which is random and unique
     * per installation (or set in the environment). The app encrypter's key is not used: it comes
     * from LEAN_SESSION_PASSWORD, which has a public default, and anyone who knows it could rewrite
     * the user id in a refresh token.
     */
    public function tokenEncryptionKey(): string
    {
        // Trimmed, so the same key yields the same secret whether it comes from the database (PEM
        // with OpenSSL's trailing newline) or from the environment after `oauth:keys --export`.
        return hash_hmac('sha256', 'leantime-oauth-payload-encryption', trim($this->keys()['private']));
    }

    /**
     * Where the active keys come from, their fingerprint and when they were made (for `oauth:keys`).
     *
     * @return array{source: string, fingerprint: string, createdAt: ?string}
     */
    public function status(): array
    {
        $keys = $this->keys();

        return [
            'source' => $keys['source'],
            'fingerprint' => self::fingerprint($keys['public']),
            'createdAt' => $keys['createdAt'],
        ];
    }

    /**
     * Replace the stored key pair with a new one. Every token signed with the old key stops
     * working, so callers must also revoke the issued tokens.
     *
     * @return array{private: string, public: string, source: string, createdAt: ?string}
     *
     * @throws RuntimeException When the keys come from the environment (nothing to rotate here).
     */
    public function rotate(): array
    {
        if ($this->fromEnvironment() !== null) {
            throw new RuntimeException('The OAuth keys come from LEAN_OAUTH_PRIVATE_KEY / LEAN_OAUTH_PUBLIC_KEY. Replace them there.');
        }

        $this->db->connection()->table('zp_settings')->where('key', self::SETTING_KEY)->delete();
        $this->resolvedKeys = null;

        return $this->keys();
    }

    /**
     * Short, stable fingerprint of a public key (sha256 of the PEM, first 16 hex chars).
     */
    public static function fingerprint(string $publicKey): string
    {
        return substr(hash('sha256', trim($publicKey)), 0, 16);
    }

    /**
     * @return array{private: string, public: string, source: string, createdAt: ?string}|null
     */
    private function fromEnvironment(): ?array
    {
        $privateKey = $this->readPem((string) $this->config->get('oauthPrivateKey', ''));
        $publicKey = $this->readPem((string) $this->config->get('oauthPublicKey', ''));

        if ($privateKey === '' && $publicKey === '') {
            return null;
        }

        if ($privateKey === '' || $publicKey === '') {
            throw new RuntimeException('Set both LEAN_OAUTH_PRIVATE_KEY and LEAN_OAUTH_PUBLIC_KEY, or neither.');
        }

        return ['private' => $privateKey, 'public' => $publicKey, 'source' => 'environment', 'createdAt' => null];
    }

    /**
     * PEM text as-is (with literal "\n" sequences turned into newlines, as env files often carry
     * them), or the contents of the file a path points to.
     */
    private function readPem(string $value): string
    {
        $value = trim($value);

        if ($value === '') {
            return '';
        }

        if (str_contains($value, '-----BEGIN')) {
            return str_replace('\\n', "\n", $value);
        }

        if (is_file($value) && is_readable($value)) {
            return trim((string) file_get_contents($value));
        }

        throw new RuntimeException('OAuth key is neither a PEM key nor a readable file: '.$value);
    }

    /**
     * @return array{private: string, public: string, source: string, createdAt: ?string}|null
     */
    private function fromDatabase(): ?array
    {
        $storedValue = $this->db->connection()->table('zp_settings')
            ->where('key', self::SETTING_KEY)
            ->value('value');

        if (! is_string($storedValue) || $storedValue === '') {
            return null;
        }

        $stored = json_decode($storedValue, true);

        if (! is_array($stored) || empty($stored['private']) || empty($stored['public'])) {
            Log::warning('OAuth: the stored signing keys are unreadable; generating a new pair. Connected apps have to reconnect.');

            return $this->replaceUnreadable($storedValue);
        }

        try {
            $privateKey = $this->encrypter->decryptString($stored['private']);
        } catch (DecryptException) {
            // LEAN_SESSION_PASSWORD changed since the keys were stored. Start over rather than
            // break: existing connections need a reconnect, nothing else is affected.
            Log::warning('OAuth: the stored signing key can no longer be decrypted (was LEAN_SESSION_PASSWORD changed?); generating a new pair. Connected apps have to reconnect.');

            return $this->replaceUnreadable($storedValue);
        }

        return [
            'private' => $privateKey,
            'public' => $stored['public'],
            'source' => 'database',
            'createdAt' => $stored['createdAt'] ?? null,
        ];
    }

    /**
     * Swap an unreadable stored value for a fresh pair. Compare-and-swap on the old value so two
     * requests hitting this at once end up with the same new pair.
     *
     * @return array{private: string, public: string, source: string, createdAt: ?string}
     */
    private function replaceUnreadable(string $unreadableValue): array
    {
        $this->db->connection()->table('zp_settings')
            ->where('key', self::SETTING_KEY)
            ->where('value', $unreadableValue)
            ->update(['value' => $this->encodeForStorage($this->generatePair())]);

        return $this->fromDatabase() ?? throw new RuntimeException('OAuth signing keys could not be stored.');
    }

    /**
     * Generate a pair and store it. Insert-if-absent, then read back, so when two requests race
     * on the very first OAuth call both use whichever pair was stored first.
     *
     * @return array{private: string, public: string, source: string, createdAt: ?string}
     */
    private function generateAndStore(): array
    {
        $this->db->connection()->table('zp_settings')->insertOrIgnore([
            'key' => self::SETTING_KEY,
            'value' => $this->encodeForStorage($this->generatePair()),
        ]);

        return $this->fromDatabase() ?? throw new RuntimeException('OAuth signing keys could not be stored.');
    }

    /**
     * @return array{private: string, public: string}
     */
    private function generatePair(): array
    {
        $keyResource = openssl_pkey_new([
            'private_key_bits' => self::KEY_BITS,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if ($keyResource === false || ! openssl_pkey_export($keyResource, $privateKey)) {
            throw new RuntimeException('Could not generate the OAuth signing key (is the PHP openssl extension configured?).');
        }

        $details = openssl_pkey_get_details($keyResource);

        if ($details === false || empty($details['key'])) {
            throw new RuntimeException('Could not read the generated OAuth public key.');
        }

        return ['private' => $privateKey, 'public' => $details['key']];
    }

    /**
     * @param  array{private: string, public: string}  $pair
     */
    private function encodeForStorage(array $pair): string
    {
        return (string) json_encode([
            'private' => $this->encrypter->encryptString($pair['private']),
            'public' => $pair['public'],
            'createdAt' => gmdate('Y-m-d H:i:s'),
        ]);
    }
}
