<?php

namespace Leantime\Domain\Install\Services;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Leantime\Core\Configuration\AppSettings;

/**
 * SchemaBuilder provides database-agnostic table creation for Leantime installation.
 *
 * This class uses Laravel's Schema Builder to create all database tables,
 * supporting both MySQL and PostgreSQL. It replaces the raw SQL DDL statements
 * that were previously MySQL-specific.
 */
class SchemaBuilder
{
    public function __construct(
        private AppSettings $appSettings
    ) {}

    /**
     * Create all database tables for a fresh Leantime installation.
     *
     * @throws \Exception If table creation fails
     */
    public function createAllTables(): void
    {
        $this->createCalendarTable();
        $this->createCanvasTable();
        $this->createCanvasItemsTable();
        $this->createApprovalsTable();
        $this->createClientsTable();
        $this->createCommentTable();
        $this->createFileTable();
        $this->createGcallinksTable();
        $this->createNoteTable();
        $this->createProjectsTable();
        $this->createPunchClockTable();
        $this->createReadTable();
        $this->createRelationUserProjectTable();
        $this->createTicketHistoryTable();
        $this->createTicketsTable();
        $this->createTimesheetsTable();
        $this->createUserTable();
        $this->createSprintsTable();
        $this->createStatsTable();
        $this->createSettingsTable();
        $this->createAuditTable();
        $this->createQueueTable();
        $this->createPluginsTable();
        $this->createNotificationsTable();
        $this->createEntityRelationshipTable();
        $this->createIntegrationTable();
        $this->createReactionsTable();
        $this->createAccessTokensTable();
        $this->createJobsTable();
        $this->createRecurringPatternsTable();
    }

    /**
     * Insert initial data after tables are created.
     *
     * @param  array  $values  Installation form values containing company, email, firstname, lastname
     * @param  string  $pwReset  Password reset token
     */
    public function insertInitialData(array $values, string $pwReset): void
    {
        // Insert initial client/company
        DB::table('zp_clients')->insert([
            'id' => 1,
            'name' => $values['company'],
            'street' => '',
            'zip' => 0,
            'city' => '',
            'state' => '',
            'country' => '',
            'phone' => '',
            'internet' => '',
            'published' => null,
            'age' => null,
            'email' => '',
        ]);

        // Insert initial admin user (status 'i' = inactive, requires password reset)
        DB::table('zp_user')->insert([
            'id' => 1,
            'username' => $values['email'],
            'password' => '',
            'firstname' => $values['firstname'],
            'lastname' => $values['lastname'],
            'phone' => '',
            'profileId' => '',
            'lastlogin' => null,
            'lastpwd_change' => null,
            'status' => 'i',
            'expires' => null,
            'role' => '50',
            'session' => '',
            'sessiontime' => '',
            'wage' => 0,
            'hours' => 0,
            'description' => null,
            'clientId' => 0,
            'notifications' => 1,
            'createdOn' => now(),
            'pwReset' => $pwReset,
        ]);

        // Insert initial settings
        DB::table('zp_settings')->insert([
            ['key' => 'db-version', 'value' => $this->appSettings->dbVersion],
            ['key' => 'companysettings.telemetry.active', 'value' => 'true'],
        ]);
    }

    /**
     * Create zp_calendar table.
     */
    private function createCalendarTable(): void
    {
        Schema::create('zp_calendar', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->dateTime('dateFrom')->nullable();
            $table->dateTime('dateTo')->nullable();
            $table->text('description')->nullable();
            $table->string('kind', 255)->nullable();
            $table->string('allDay', 10)->nullable();

            $table->index(['userId', 'dateFrom', 'dateTo'], 'idx_calendar_userId_dateFrom_dateTo');
        });
    }

    /**
     * Create zp_canvas table.
     */
    private function createCanvasTable(): void
    {
        Schema::create('zp_canvas', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255)->nullable();
            $table->integer('author')->nullable();
            $table->dateTime('created')->nullable();
            $table->integer('projectId')->nullable();
            $table->string('type', 45)->nullable();
            $table->text('description')->nullable();
            $table->string('color', 50)->default('ocean');
            $table->dateTime('modified')->nullable();

            $table->index(['projectId', 'type'], 'ProjectIdType');
            $table->index(['type', 'id'], 'idx_canvas_type_id');
        });
    }

    /**
     * Create zp_canvas_items table.
     */
    private function createCanvasItemsTable(): void
    {
        Schema::create('zp_canvas_items', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->text('assumptions')->nullable();
            $table->text('data')->nullable();
            $table->text('conclusion')->nullable();
            $table->string('box', 255)->nullable();
            $table->integer('author')->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('modified')->nullable();
            $table->integer('canvasId')->nullable();
            $table->integer('sortindex')->nullable();
            $table->string('status', 255)->nullable();
            $table->string('relates', 255)->nullable();
            $table->string('milestoneId', 255)->nullable();
            $table->string('title', 255)->nullable();
            $table->integer('parent')->nullable();
            $table->integer('featured')->nullable();
            $table->text('tags')->nullable();
            $table->integer('kpi')->nullable();
            $table->text('data1')->nullable();
            $table->text('data2')->nullable();
            $table->text('data3')->nullable();
            $table->text('data4')->nullable();
            $table->text('data5')->nullable();
            $table->dateTime('startDate')->nullable();
            $table->dateTime('endDate')->nullable();
            $table->text('setting')->nullable();
            $table->string('metricType', 45)->nullable();
            $table->decimal('startValue', 10, 2)->nullable();
            $table->decimal('currentValue', 10, 2)->nullable();
            $table->decimal('endValue', 10, 2)->nullable();
            $table->integer('impact')->nullable();
            $table->integer('effort')->nullable();
            $table->integer('probability')->nullable();
            $table->text('action')->nullable();
            $table->integer('assignedTo')->nullable();

            $table->index(['canvasId', 'box'], 'CanvasLookUp');
            $table->index(['box', 'milestoneId'], 'idx_canvas_items_box_milestoneId');
            $table->index(['box', 'status', 'author'], 'idx_canvas_items_box_status_author');
            $table->index(['parent', 'title'], 'idx_canvas_items_parent_title');
        });
    }

    /**
     * Create zp_approvals table.
     */
    private function createApprovalsTable(): void
    {
        Schema::create('zp_approvals', function (Blueprint $table) {
            $table->id();
            $table->string('module', 100)->nullable();
            $table->integer('entityId')->nullable();
            $table->integer('requestorId')->nullable();
            $table->integer('approverId')->nullable();
            $table->integer('approvalStatus')->nullable();
            $table->dateTime('requestedOn')->nullable();
            $table->dateTime('lastStatusChange')->nullable();
        });
    }

    /**
     * Create zp_clients table.
     */
    private function createClientsTable(): void
    {
        Schema::create('zp_clients', function (Blueprint $table) {
            $table->id();
            $table->string('name', 200)->nullable();
            $table->string('street', 200)->nullable();
            $table->integer('zip')->nullable();
            $table->string('city', 50)->nullable();
            $table->string('state', 50)->nullable();
            $table->string('country', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('internet', 200)->nullable();
            $table->integer('published')->nullable();
            $table->integer('age')->nullable();
            $table->string('email', 255)->nullable();
            $table->dateTime('modified')->nullable();
        });
    }

    /**
     * Create zp_comment table.
     */
    private function createCommentTable(): void
    {
        Schema::create('zp_comment', function (Blueprint $table) {
            $table->id();
            $table->string('module', 200)->nullable();
            $table->integer('userId')->nullable();
            $table->integer('commentParent')->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('moduleId')->nullable();
            $table->text('text')->nullable();
            $table->string('status', 50)->nullable();

            $table->index(['moduleId', 'module', 'commentParent'], 'idx_comment_moduleId_module_commentParent');
            $table->index(['userId', 'module'], 'idx_comment_userId_module');
        });
    }

    /**
     * Create zp_file table.
     *
     * Note: MySQL uses ENUM for module, but for cross-database compatibility
     * we use a string column with application-level validation.
     */
    private function createFileTable(): void
    {
        Schema::create('zp_file', function (Blueprint $table) {
            $table->id();
            // Using string instead of enum for PostgreSQL compatibility
            // Valid values: project, ticket, client, user, lead, export, private
            $table->string('module', 50)->nullable();
            $table->integer('moduleId')->nullable();
            $table->integer('userId')->nullable();
            $table->string('extension', 10)->nullable();
            $table->string('encName', 255)->nullable();
            $table->string('realName', 255)->nullable();
            $table->dateTime('date')->nullable();

            $table->index(['module', 'moduleId', 'userId'], 'idx_file_module_moduleId_userId');
        });
    }

    /**
     * Create zp_gcallinks table.
     */
    private function createGcallinksTable(): void
    {
        Schema::create('zp_gcallinks', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->text('url')->nullable();
            $table->string('name', 255)->nullable();
            $table->string('colorClass', 100)->nullable();

            $table->index(['userId'], 'idx_gcallinks_userId');
        });
    }

    /**
     * Create zp_note table.
     */
    private function createNoteTable(): void
    {
        Schema::create('zp_note', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->string('title', 255)->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Create zp_projects table.
     */
    private function createProjectsTable(): void
    {
        Schema::create('zp_projects', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->nullable();
            $table->integer('clientId')->nullable();
            $table->text('details')->nullable();
            $table->integer('state')->nullable();
            $table->string('hourBudget', 255)->default('');
            $table->integer('dollarBudget')->nullable();
            $table->integer('active')->nullable();
            $table->text('menuType')->nullable();
            $table->text('psettings')->nullable();
            $table->integer('parent')->nullable();
            $table->string('type', 45)->nullable();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->dateTime('created')->nullable();
            $table->dateTime('modified')->nullable();
            $table->text('avatar')->nullable();
            $table->text('cover')->nullable();
            $table->integer('sortIndex')->nullable();
        });
    }

    /**
     * Create zp_punch_clock table.
     *
     * Note: Has a composite primary key (id, userId).
     */
    private function createPunchClockTable(): void
    {
        Schema::create('zp_punch_clock', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->autoIncrement();
            $table->integer('userId');
            $table->integer('minutes')->nullable();
            $table->integer('hours')->nullable();
            $table->integer('punchIn')->nullable();

            // Composite primary key
            $table->primary(['id', 'userId']);
        });
    }

    /**
     * Create zp_read table.
     *
     * Note: MySQL uses ENUM for module, but for cross-database compatibility
     * we use a string column with application-level validation.
     */
    private function createReadTable(): void
    {
        Schema::create('zp_read', function (Blueprint $table) {
            $table->id();
            // Using string instead of enum for PostgreSQL compatibility
            // Valid values: ticket, message
            $table->string('module', 50)->nullable();
            $table->integer('moduleId')->nullable();
            $table->integer('userId')->nullable();
        });
    }

    /**
     * Create zp_relationuserproject table (junction table for user-project relationships).
     */
    private function createRelationUserProjectTable(): void
    {
        Schema::create('zp_relationuserproject', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->integer('projectId')->nullable();
            $table->integer('wage')->nullable();
            $table->string('projectRole', 20)->nullable();

            $table->index(['projectId'], 'zp_relationuserproject_projectId_index');
            $table->index(['userId'], 'zp_relationuserproject_userId_index');
        });
    }

    /**
     * Create zp_tickethistory table.
     */
    private function createTicketHistoryTable(): void
    {
        Schema::create('zp_tickethistory', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->integer('ticketId')->nullable();
            $table->string('changeType', 255)->nullable();
            $table->string('changeValue', 150)->nullable();
            $table->dateTime('dateModified')->nullable();
        });
    }

    /**
     * Create zp_tickets table.
     */
    private function createTicketsTable(): void
    {
        Schema::create('zp_tickets', function (Blueprint $table) {
            $table->id();
            $table->integer('projectId')->nullable();
            $table->string('headline', 255)->nullable();
            $table->text('description')->nullable();
            $table->text('acceptanceCriteria')->nullable();
            $table->dateTime('date')->nullable();
            $table->dateTime('dateToFinish')->nullable();
            $table->string('priority', 60)->nullable();
            $table->integer('status')->nullable();
            $table->integer('userId')->nullable();
            $table->string('os', 30)->nullable();
            $table->string('browser', 30)->nullable();
            $table->string('resolution', 30)->nullable();
            $table->string('component', 100)->nullable();
            $table->string('version', 20)->nullable();
            $table->string('url', 100)->nullable();
            $table->integer('dependingTicketId')->nullable();
            $table->dateTime('editFrom')->nullable();
            $table->dateTime('editTo')->nullable();
            $table->string('editorId', 75)->nullable();
            $table->float('planHours')->nullable();
            $table->float('hourRemaining')->nullable();
            $table->string('type', 255)->nullable();
            $table->integer('production')->default(0);
            $table->integer('staging')->default(0);
            $table->float('storypoints')->nullable();
            $table->integer('sprint')->nullable();
            $table->bigInteger('sortindex')->nullable();
            $table->bigInteger('kanbanSortIndex')->nullable();
            $table->string('tags', 255)->nullable();
            $table->integer('milestoneid')->nullable();
            $table->integer('leancanvasitemid')->nullable();
            $table->integer('retrospectiveid')->nullable();
            $table->integer('ideaid')->nullable();
            $table->string('zp_ticketscol', 45)->nullable();
            $table->dateTime('modified')->nullable();

            $table->index(['projectId', 'userId'], 'ProjectUserId');
            $table->index(['status', 'sprint'], 'StatusSprint');
            $table->index(['sortindex'], 'Sorting');
            $table->index(['editorId'], 'idx_tickets_editorId');
            $table->index(['milestoneid'], 'idx_tickets_milestoneid');
            $table->index(['editFrom'], 'idx_tickets_editFrom');
            $table->index(['editTo'], 'idx_tickets_editTo');
            $table->index(['dateToFinish'], 'idx_tickets_dateToFinish');
            $table->index(['modified'], 'idx_tickets_modified');
            $table->index(['projectId', 'status'], 'idx_tickets_projectId_status');
            $table->index(['projectId', 'type'], 'idx_tickets_projectId_type');
            $table->index(['status', 'type'], 'idx_tickets_status_type');
        });
    }

    /**
     * Create zp_timesheets table.
     */
    private function createTimesheetsTable(): void
    {
        Schema::create('zp_timesheets', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->integer('ticketId')->nullable();
            $table->dateTime('workDate')->nullable();
            $table->float('hours')->nullable();
            $table->text('description')->nullable();
            $table->string('kind', 175)->nullable();
            $table->integer('invoicedEmpl')->nullable();
            $table->integer('invoicedComp')->nullable();
            $table->dateTime('invoicedEmplDate')->nullable();
            $table->dateTime('invoicedCompDate')->nullable();
            $table->string('rate', 255)->nullable();
            $table->integer('paid')->nullable();
            $table->dateTime('paidDate')->nullable();
            $table->dateTime('modified')->nullable();

            $table->unique(['userId', 'ticketId', 'workDate', 'kind'], 'Unique');
            $table->index(['ticketId'], 'idx_timesheets_ticketId');
            $table->index(['userId', 'workDate'], 'idx_timesheets_userId_workDate');
            $table->index(['ticketId', 'workDate'], 'idx_timesheets_ticketId_workDate');
        });
    }

    /**
     * Create zp_user table.
     */
    private function createUserTable(): void
    {
        Schema::create('zp_user', function (Blueprint $table) {
            $table->id();
            $table->string('username', 175);
            $table->string('password', 255)->default('');
            $table->string('firstname', 100);
            $table->string('lastname', 100);
            $table->string('phone', 25)->default('');
            $table->string('profileId', 100)->default('');
            $table->dateTime('lastlogin')->nullable();
            $table->string('status', 1)->default('A');
            $table->dateTime('expires')->nullable();
            $table->string('role', 200);
            $table->string('session', 100)->nullable();
            $table->string('sessiontime', 50)->nullable();
            $table->integer('wage')->nullable();
            $table->integer('hours')->nullable();
            $table->text('description')->nullable();
            $table->integer('clientId')->nullable();
            $table->integer('notifications')->nullable();
            $table->string('pwReset', 100)->nullable();
            $table->dateTime('pwResetExpiration')->nullable();
            $table->integer('pwResetCount')->nullable();
            $table->tinyInteger('forcePwReset')->nullable();
            $table->dateTime('lastpwd_change')->nullable();
            $table->text('settings')->nullable();
            $table->tinyInteger('twoFAEnabled')->default(0);
            $table->string('twoFASecret', 200)->nullable();
            $table->dateTime('createdOn')->nullable();
            $table->string('source', 200)->nullable();
            $table->string('jobTitle', 200)->nullable();
            $table->string('jobLevel', 50)->nullable();
            $table->string('department', 200)->nullable();
            $table->dateTime('modified')->nullable();

            $table->unique(['username'], 'username');
            $table->index(['clientId'], 'idx_user_clientId');
        });
    }

    /**
     * Create zp_sprints table.
     */
    private function createSprintsTable(): void
    {
        Schema::create('zp_sprints', function (Blueprint $table) {
            $table->id();
            $table->integer('projectId')->nullable();
            $table->string('name', 45)->nullable();
            $table->dateTime('startDate')->nullable();
            $table->dateTime('endDate')->nullable();
            $table->dateTime('modified')->nullable();

            $table->index(['projectId', 'startDate', 'endDate'], 'idx_sprints_projectId_startDate_endDate');
        });
    }

    /**
     * Create zp_stats table.
     *
     * Note: This table has no primary key, only indexes.
     */
    private function createStatsTable(): void
    {
        Schema::create('zp_stats', function (Blueprint $table) {
            $table->integer('sprintId')->nullable();
            $table->integer('projectId')->nullable();
            $table->dateTime('date')->nullable();
            $table->integer('sum_todos')->nullable();
            $table->integer('sum_open_todos')->nullable();
            $table->integer('sum_progres_todos')->nullable();
            $table->integer('sum_closed_todos')->nullable();
            $table->float('sum_planned_hours')->nullable();
            $table->float('sum_estremaining_hours')->nullable();
            $table->float('sum_logged_hours')->nullable();
            $table->integer('sum_points')->nullable();
            $table->integer('sum_points_done')->nullable();
            $table->integer('sum_points_progress')->nullable();
            $table->integer('sum_points_open')->nullable();
            $table->integer('sum_todos_xs')->nullable();
            $table->integer('sum_todos_s')->nullable();
            $table->integer('sum_todos_m')->nullable();
            $table->integer('sum_todos_l')->nullable();
            $table->integer('sum_todos_xl')->nullable();
            $table->integer('sum_todos_xxl')->nullable();
            $table->integer('sum_todos_none')->nullable();
            $table->text('tickets')->nullable();
            $table->float('daily_avg_hours_booked_todo')->nullable();
            $table->float('daily_avg_hours_booked_point')->nullable();
            $table->float('daily_avg_hours_planned_todo')->nullable();
            $table->float('daily_avg_hours_planned_point')->nullable();
            $table->float('daily_avg_hours_remaining_point')->nullable();
            $table->float('daily_avg_hours_remaining_todo')->nullable();
            $table->integer('sum_teammembers')->nullable();

            $table->index(['projectId', 'sprintId'], 'projectId');
            $table->index(['projectId', 'sprintId', 'date'], 'idx_stats_projectId_sprintId_date');
            $table->index(['sprintId', 'date'], 'idx_stats_sprintId_date');
        });
    }

    /**
     * Create zp_settings table (key-value store).
     */
    private function createSettingsTable(): void
    {
        Schema::create('zp_settings', function (Blueprint $table) {
            $table->string('key', 175)->primary();
            $table->text('value')->nullable();

            $table->index(['key'], 'idx_settings_key');
        });
    }

    /**
     * Create zp_audit table.
     */
    private function createAuditTable(): void
    {
        Schema::create('zp_audit', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->integer('projectId')->nullable();
            $table->string('action', 45)->nullable();
            $table->string('entity', 45)->nullable();
            $table->integer('entityId')->nullable();
            $table->text('values')->nullable();
            $table->dateTime('date')->nullable();

            $table->index(['projectId'], 'projectId');
            $table->index(['projectId', 'action'], 'projectAction');
            $table->index(['projectId', 'entity', 'entityId'], 'projectEntityEntityId');
        });
    }

    /**
     * Create zp_queue table (message queue).
     */
    private function createQueueTable(): void
    {
        Schema::create('zp_queue', function (Blueprint $table) {
            $table->string('msghash', 50)->primary();
            $table->string('channel', 255)->nullable();
            $table->integer('userId');
            $table->string('subject', 255)->nullable();
            $table->text('message');
            $table->dateTime('thedate');
            $table->integer('projectId');

            $table->index(['projectId'], 'projectId');
            $table->index(['userId'], 'userId');
        });
    }

    /**
     * Create zp_plugins table.
     */
    private function createPluginsTable(): void
    {
        Schema::create('zp_plugins', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45)->nullable();
            $table->tinyInteger('enabled')->nullable();
            $table->string('description', 255)->nullable();
            $table->string('version', 45)->nullable();
            $table->dateTime('installdate')->nullable();
            $table->string('foldername', 45)->nullable();
            $table->string('homepage', 255)->nullable();
            $table->string('authors', 255)->nullable();
            $table->text('license')->nullable();
            $table->string('format', 45)->nullable();
        });
    }

    /**
     * Create zp_notifications table.
     */
    private function createNotificationsTable(): void
    {
        Schema::create('zp_notifications', function (Blueprint $table) {
            $table->id();
            $table->integer('userId');
            $table->integer('read')->nullable();
            $table->string('type', 45)->nullable();
            $table->string('module', 45)->nullable();
            $table->integer('moduleId')->nullable();
            $table->dateTime('datetime')->nullable();
            $table->string('url', 255)->nullable();
            $table->integer('authorId')->nullable();
            $table->text('message')->nullable();

            $table->index(['userId'], 'userId');
            $table->index(['userId', 'datetime'], 'userId,datetime');
            $table->index(['userId', 'read'], 'userId,read');
        });
    }

    /**
     * Create zp_entity_relationship table (polymorphic relationships).
     */
    private function createEntityRelationshipTable(): void
    {
        Schema::create('zp_entity_relationship', function (Blueprint $table) {
            $table->id();
            $table->integer('entityA')->nullable();
            $table->string('entityAType', 45)->nullable();
            $table->integer('entityB')->nullable();
            $table->string('entityBType', 45)->nullable();
            $table->string('relationship', 45)->nullable();
            $table->dateTime('createdOn')->nullable();
            $table->integer('createdBy')->nullable();
            $table->text('meta')->nullable();

            $table->index(['entityA', 'entityAType', 'relationship'], 'entityA');
            $table->index(['entityB', 'entityBType', 'relationship'], 'entityB');
        });
    }

    /**
     * Create zp_integration table.
     */
    private function createIntegrationTable(): void
    {
        Schema::create('zp_integration', function (Blueprint $table) {
            $table->id();
            $table->string('providerId', 45)->nullable();
            $table->string('method', 45)->nullable();
            $table->string('entity', 45)->nullable();
            $table->text('fields')->nullable();
            $table->string('schedule', 45)->nullable();
            $table->string('notes', 45)->nullable();
            $table->text('auth')->nullable();
            $table->string('meta', 45)->nullable();
            $table->dateTime('createdOn')->nullable();
            $table->integer('createdBy')->nullable();
            $table->string('lastSync', 45)->nullable();
        });
    }

    /**
     * Create zp_reactions table (emoji reactions).
     */
    private function createReactionsTable(): void
    {
        Schema::create('zp_reactions', function (Blueprint $table) {
            $table->id();
            $table->integer('userId')->nullable();
            $table->integer('moduleId')->nullable();
            $table->string('module', 45)->nullable();
            $table->string('reaction', 45)->nullable();
            $table->dateTime('date')->nullable();

            $table->index(['moduleId', 'module', 'reaction'], 'entity');
            $table->index(['userId', 'moduleId', 'module', 'reaction'], 'user');
        });
    }

    /**
     * Create zp_access_tokens table (Laravel Sanctum personal access tokens).
     */
    private function createAccessTokensTable(): void
    {
        Schema::create('zp_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('tokenable_type', 255);
            $table->unsignedBigInteger('tokenable_id');
            $table->string('name', 255);
            $table->string('token', 64);
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->unique(['token'], 'personal_access_tokens_token_unique');
            $table->index(['tokenable_type', 'tokenable_id'], 'personal_access_tokens_tokenable_type_tokenable_id_index');
        });
    }

    /**
     * Create zp_jobs table (Laravel job queue).
     */
    private function createJobsTable(): void
    {
        Schema::create('zp_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue', 255);
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');

            $table->index(['queue'], 'zp_jobs_queue_index');
        });
    }

    /**
     * Create zp_recurring_patterns table.
     */
    private function createRecurringPatternsTable(): void
    {
        Schema::create('zp_recurring_patterns', function (Blueprint $table) {
            $table->id();
            $table->integer('entityId');
            $table->string('module', 50);
            $table->string('type', 50);
            $table->string('trigger', 50);
            $table->integer('interval')->default(1);
            $table->text('weekDays')->nullable();
            $table->integer('monthDay')->nullable();
            $table->text('months')->nullable();
            $table->string('action', 20)->default('reset');
            $table->dateTime('lastProcessed')->nullable();
            $table->dateTime('nextProcessingDate')->nullable();
            $table->tinyInteger('enabled')->default(1);

            $table->index(['entityId'], 'entityId');
        });
    }
}
