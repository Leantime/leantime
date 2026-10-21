<?php

namespace Leantime\Core\UI;

use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Db\Db as DbCore;

/**
 * Per-user UI state: which accordions are open, sidebar widths, a calendar's last view …
 *
 * Each state is its own row in zp_settings ("usersetting.{userId}.ui.{name}"), written with a
 * single atomic upsert. Nothing goes through the session, so parallel requests can't overwrite
 * each other's states and a saved state shows on the very next page load (JSON-RPC requests,
 * which save most states, never persist the session).
 *
 * Reads load all of the user's states once per request (one query). States saved before this
 * store existed live in the serialized "usersetting.{userId}.submenuToggle" setting; they are
 * read as a fallback, so earlier choices carry over.
 */
class UiState
{
    /** zp_settings.key is VARCHAR(175). */
    private const MAX_KEY_LENGTH = 175;

    /** @var array<string, string>|null The loaded user's states, keyed by stored name. */
    private ?array $states = null;

    private ?int $loadedFor = null;

    /**
     * @param  ConnectionInterface|null  $db  The connection to use; resolved on first use when null
     *                                        (connecting can fail before install, and unit tests
     *                                        have no database).
     */
    public function __construct(private ?ConnectionInterface $db = null) {}

    /**
     * A state of the current user, or $default when it was never saved.
     */
    public function get(string $name, ?string $default = null): ?string
    {
        return $this->all()[$this->storedName($name)] ?? $default;
    }

    /**
     * Save a state of the current user. One row, one statement: concurrent saves of other states
     * can't drop it.
     */
    public function set(string $name, string $value): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            return;
        }

        $storedName = $this->storedName($name);
        $db = $this->connection();
        if ($db === null) {
            return;
        }
        $db->table('zp_settings')->upsert(
            [['key' => $this->prefix($userId).$storedName, 'value' => $value]],
            ['key'],
            ['value']
        );

        if ($this->loadedFor === $userId && $this->states !== null) {
            $this->states[$storedName] = $value;
        }
    }

    /**
     * All states of the current user, keyed by stored name.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $userId = $this->userId();
        if ($userId === null) {
            return [];
        }

        if ($this->states !== null && $this->loadedFor === $userId) {
            return $this->states;
        }

        $this->loadedFor = $userId;
        $this->states = $this->load($userId);

        return $this->states;
    }

    /**
     * @return array<string, string>
     */
    private function load(int $userId): array
    {
        try {
            $legacyKey = 'usersetting.'.$userId.'.submenuToggle';
            $prefix = $this->prefix($userId);

            $db = $this->connection();
            if ($db === null) {
                return [];
            }
            $rows = $db->table('zp_settings')
                ->where('key', $legacyKey)
                // The prefix is letters, digits and dots only: no LIKE wildcards to escape.
                ->orWhere('key', 'like', $prefix.'%')
                ->get(['key', 'value']);
        } catch (\Throwable $e) {
            // Before install / during an update the table may not be usable: no states then.
            Log::warning('UI states could not be loaded: '.$e->getMessage());

            return [];
        }

        $states = [];
        foreach ($rows as $row) {
            if ($row->key !== $legacyKey) {
                continue;
            }
            $legacy = safe_unserialize((string) $row->value, []);
            // The old store wrote through a dot-notation session path, so a name with dots
            // ("section.part") was saved nested: flatten it back to the dotted name.
            foreach (is_array($legacy) ? Arr::dot($legacy) : [] as $name => $value) {
                if (is_scalar($value)) {
                    $states[$this->storedName((string) $name)] = (string) $value;
                }
            }
        }

        // Rows written by this store win over the legacy blob.
        foreach ($rows as $row) {
            if ($row->key !== $legacyKey && str_starts_with($row->key, $prefix)) {
                $states[substr($row->key, strlen($prefix))] = (string) $row->value;
            }
        }

        return $states;
    }

    private function connection(): ?ConnectionInterface
    {
        if ($this->db !== null) {
            return $this->db;
        }

        try {
            return $this->db = app(DbCore::class)->getConnection();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function prefix(int $userId): string
    {
        return 'usersetting.'.$userId.'.ui.';
    }

    /**
     * The name as stored in the key. zp_settings.key compares case- and accent-insensitively on
     * MySQL (utf8mb4_unicode_ci), so "Foo" and "foo" would share a row: everything outside
     * [a-z0-9_-] is escaped ("~" + hex), which keeps distinct names distinct under any collation.
     * Names that would make the key too long are shortened to a stable hash.
     */
    private function storedName(string $name): string
    {
        $encoded = preg_replace_callback('/[^a-z0-9_-]/', fn ($m) => '~'.bin2hex($m[0]), $name);

        // Longest prefix for a realistic user id ("usersetting.9999999999.ui.").
        $room = self::MAX_KEY_LENGTH - 26;

        return strlen($encoded) <= $room ? $encoded : substr($encoded, 0, $room - 41).'~'.sha1($name);
    }

    private function userId(): ?int
    {
        $userId = session('userdata.id');

        return is_numeric($userId) && (int) $userId > 0 ? (int) $userId : null;
    }
}
