<?php

namespace Unit\app\Domain\Reminders;

use Illuminate\Database\SQLiteConnection;
use Leantime\Domain\Reminders\Repositories\Reminders as ReminderRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\SettingCache;

/**
 * An in-memory SQLite database with just enough of the tickets, projects, users, inbox, settings
 * and recurring-pattern tables for the reminder queries, plus helpers to add rows. Reminders add
 * no tables of their own.
 */
trait ReminderDatabase
{
    protected SQLiteConnection $db;

    protected function createReminderDatabase(): void
    {
        $this->db = new SQLiteConnection(new \PDO('sqlite::memory:'));

        $this->db->statement('CREATE TABLE zp_user (id INTEGER PRIMARY KEY, username VARCHAR(100), firstname VARCHAR(100), lastname VARCHAR(100), status VARCHAR(1), role VARCHAR(200), source VARCHAR(200), notifications INTEGER)');
        $this->db->statement('CREATE TABLE zp_projects (id INTEGER PRIMARY KEY, name VARCHAR(100), state INTEGER, "end" DATETIME, psettings TEXT)');
        $this->db->statement('CREATE TABLE zp_relationuserproject (id INTEGER PRIMARY KEY AUTOINCREMENT, userId INTEGER, projectId INTEGER, projectRole VARCHAR(20))');
        $this->db->statement('CREATE TABLE zp_tickets (id INTEGER PRIMARY KEY, projectId INTEGER, headline VARCHAR(255), type VARCHAR(255), status INTEGER, priority VARCHAR(60), dateToFinish DATETIME, editFrom DATETIME, editTo DATETIME, editorId VARCHAR(75), userId INTEGER)');
        $this->db->statement('CREATE TABLE zp_notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, userId INTEGER, "read" INTEGER, type VARCHAR(45), module VARCHAR(45), moduleId INTEGER, datetime DATETIME, url TEXT, message TEXT, authorId INTEGER)');
        $this->db->statement('CREATE TABLE zp_settings ("key" VARCHAR(175) PRIMARY KEY, value TEXT)');
        $this->db->statement('CREATE TABLE zp_recurring_patterns (id INTEGER PRIMARY KEY AUTOINCREMENT, entityId INTEGER, module VARCHAR(50), type VARCHAR(50), "trigger" VARCHAR(50), "interval" INTEGER DEFAULT 1, weekDays TEXT, enabled INTEGER DEFAULT 1)');
    }

    protected function repository(): ReminderRepository
    {
        $repository = (new \ReflectionClass(ReminderRepository::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(ReminderRepository::class, 'db'))->setValue($repository, $this->db);

        return $repository;
    }

    /** The real settings repository on this database. */
    protected function settingRepository(): SettingRepository
    {
        $repository = (new \ReflectionClass(SettingRepository::class))->newInstanceWithoutConstructor();
        (new \ReflectionProperty(SettingRepository::class, 'db'))->setValue($repository, $this->db);
        (new \ReflectionProperty(SettingRepository::class, 'cache'))->setValue($repository, new SettingCache);

        return $repository;
    }

    /** An inbox row, as the morning summary writes it. */
    protected function addSummaryRow(int $userId, string $datetimeUtc): void
    {
        $this->db->table('zp_notifications')->insert(['userId' => $userId, 'read' => 0, 'type' => 'morning_summary', 'module' => 'reminders', 'moduleId' => 0, 'datetime' => $datetimeUtc, 'url' => '', 'message' => '', 'authorId' => 0]);
    }

    protected function addUser(int $id, string $role = '20', string $status = 'a', ?string $source = null, int $notifications = 1): void
    {
        $this->db->table('zp_user')->insert(['id' => $id, 'username' => 'user'.$id.'@example.test', 'status' => $status, 'role' => $role, 'source' => $source, 'notifications' => $notifications]);
    }

    protected function addProject(int $id, ?int $state = null, ?string $end = null, string $name = 'Project'): void
    {
        $this->db->table('zp_projects')->insert(['id' => $id, 'name' => $name, 'state' => $state, 'end' => $end]);
    }

    protected function addMember(int $userId, int $projectId, string $projectRole = ''): void
    {
        $this->db->table('zp_relationuserproject')->insert(['userId' => $userId, 'projectId' => $projectId, 'projectRole' => $projectRole]);
    }

    /** @param  array<string, mixed>  $values */
    protected function addTicket(int $id, array $values): void
    {
        $this->db->table('zp_tickets')->insert($values + [
            'id' => $id,
            'projectId' => 1,
            'headline' => 'Task '.$id,
            'type' => 'task',
            'status' => 3,
            'priority' => '3',
            'dateToFinish' => null,
            'editFrom' => null,
            'editTo' => null,
            'editorId' => null,
            'userId' => 99,
        ]);
    }
}
