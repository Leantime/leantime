<?php

namespace Unit\app\Domain\Install;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Leantime\Core\Configuration\AppSettings;
use Leantime\Domain\Install\Repositories\Install;
use Unit\TestCase;

/**
 * Plugin updates used to insert a second zp_plugins row (new version, disabled) next to the
 * enabled one (stale version). update_sql_30530 merges them into one row per plugin folder.
 */
class UpdateSql30530Test extends TestCase
{
    private function connection(): Connection
    {
        config([
            'database.connections.plugin_rows' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => ''],
        ]);

        $connection = DB::connection('plugin_rows');
        $connection->statement('CREATE TABLE zp_plugins (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name VARCHAR(255), enabled INTEGER, description TEXT, version VARCHAR(50),
            installdate VARCHAR(50), foldername VARCHAR(255), homepage VARCHAR(255),
            authors TEXT, license TEXT, format VARCHAR(20)
        )');

        return $connection;
    }

    private function installer(Connection $connection): Install
    {
        $install = (new \ReflectionClass(Install::class))->newInstanceWithoutConstructor();
        foreach (['connection' => $connection, 'config' => app('config'), 'settings' => new AppSettings] as $property => $value) {
            (new \ReflectionProperty(Install::class, $property))->setValue($install, $value);
        }

        return $install;
    }

    private function addRow(Connection $connection, string $folder, bool $enabled, string $version, string $license): void
    {
        $connection->table('zp_plugins')->insert([
            'name' => 'leantime/'.strtolower($folder), 'enabled' => (int) $enabled, 'description' => '',
            'version' => $version, 'installdate' => '26-10-01', 'foldername' => $folder, 'homepage' => '',
            'authors' => '[]', 'license' => $license, 'format' => 'phar',
        ]);
    }

    public function test_duplicate_rows_merge_into_the_enabled_row_with_the_newest_version_and_license(): void
    {
        $connection = $this->connection();
        $this->addRow($connection, 'Notes', true, '1.4.2', 'old-key');   // id 1: the original, enabled
        $this->addRow($connection, 'Notes', false, '1.5.0', 'new-key');  // id 2: left by an update
        $this->addRow($connection, 'Pomodoro', true, '1.2.1', 'pomo');   // id 3: no duplicates

        $this->assertTrue($this->installer($connection)->update_sql_30530());

        $notes = $connection->table('zp_plugins')->where('foldername', 'Notes')->get();
        $this->assertCount(1, $notes);
        $this->assertSame(1, (int) $notes[0]->id);
        $this->assertSame(1, (int) $notes[0]->enabled);
        $this->assertSame('1.5.0', $notes[0]->version);
        $this->assertSame('new-key', $notes[0]->license);

        $pomodoro = $connection->table('zp_plugins')->where('foldername', 'Pomodoro')->first();
        $this->assertSame('1.2.1', $pomodoro->version);
        $this->assertSame('pomo', $pomodoro->license);
    }

    public function test_without_an_enabled_row_the_newest_row_is_kept(): void
    {
        $connection = $this->connection();
        $this->addRow($connection, 'Notes', false, '1.4.2', 'old-key');
        $this->addRow($connection, 'Notes', false, '1.5.0', '');

        $this->assertTrue($this->installer($connection)->update_sql_30530());

        $notes = $connection->table('zp_plugins')->where('foldername', 'Notes')->get();
        $this->assertCount(1, $notes);
        $this->assertSame(2, (int) $notes[0]->id);
        $this->assertSame('1.5.0', $notes[0]->version);
        // The newer row had no key: the newest non-empty one is kept.
        $this->assertSame('old-key', $notes[0]->license);
    }

    public function test_an_install_without_the_plugin_table_is_left_alone(): void
    {
        config(['database.connections.no_plugins' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);

        $this->assertTrue($this->installer(DB::connection('no_plugins'))->update_sql_30530());
    }

    /** "v2.0.0" is newer than "1.5.0" (a plain version_compare sorts it below). */
    public function test_a_leading_v_does_not_hide_the_newest_version(): void
    {
        $connection = $this->connection();
        $this->addRow($connection, 'Notes', true, '1.5.0', 'key');
        $this->addRow($connection, 'Notes', false, 'v2.0.0', 'key');

        $this->assertTrue($this->installer($connection)->update_sql_30530());

        $this->assertSame('v2.0.0', $connection->table('zp_plugins')->where('foldername', 'Notes')->value('version'));
    }
}
