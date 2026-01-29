<?php

namespace Leantime\Domain\Install\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Configuration\AppSettings as AppSettingCore;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Setting\Services\SettingCache;
use PDO;
use PDOException;

class Install
{
    use DispatchesEvents;

    public string $name;

    public int $id;

    /**
     * Laravel database connection interface
     */
    private ConnectionInterface $connection;

    /**
     * Laravel database manager for creating connections
     */
    private DatabaseManager $dbManager;

    /**
     * db update scripts listed out by version number with leading zeros A.BB.CC => ABBCC
     */
    private array $dbUpdates = [
        20004,
        20100,
        20101,
        20102,
        20103,
        20104,
        20105,
        20106,
        20107,
        20108,
        20109,
        20110,
        20111,
        20112,
        20113,
        20114,
        20115,
        20116,
        20117,
        20118,
        20120,
        20121,
        20122,
        20401,
        20402,
        20405,
        20406,
        20407,
        30002,
        30003,
        30400,
        30408,
        30409,
        30410,
        30411,
        30412,
        30413,
    ];

    /**
     * config object, passed into constructor
     */
    private Environment|string $config;

    /**
     * appSettings object, passed into constructor
     */
    private string|AppSettingCore $settings;

    /**
     * __construct - get database connection using Laravel's database manager
     */
    public function __construct(
        Environment $config,
        AppSettingCore $settings,
        DatabaseManager $dbManager
    ) {
        // Some scripts might take a long time to execute. Set timeout to 5minutes
        ini_set('max_execution_time', 300);

        $this->config = $config;
        $this->settings = $settings;
        $this->dbManager = $dbManager;

        // Use Laravel's database connection for consistency with the rest of the application
        try {
            $this->connection = $this->dbManager->connection('mysql');
        } catch (\Exception $e) {
            Log::error('Failed to establish database connection during installation: '.$e->getMessage());
            // During installation, we may need to create a temporary connection without database selection
            $this->createTemporaryConnection();
        }
    }

    /**
     * returns current database object
     */
    public function getDBObject(): ?PDO
    {
        return $this->connection?->getPdo();
    }

    /**
     * Create a temporary database connection for installation purposes
     * This is used when the main database connection fails during installation
     */
    private function createTemporaryConnection(): void
    {
        try {
            // Create a temporary connection without selecting a specific database
            $config = [
                'driver' => 'mysql',
                'host' => $this->config->dbHost,
                'port' => $this->config->dbPort ?? 3306,
                'username' => $this->config->dbUser,
                'password' => $this->config->dbPassword,
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'options' => [
                    PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,sql_mode="NO_ENGINE_SUBSTITUTION"',
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ],
            ];

            // Use Laravel's DB manager to create a temporary connection
            config(['database.connections.install_temp' => $config]);
            $this->connection = $this->dbManager->connection('install_temp');
        } catch (\Exception $e) {
            Log::error('Failed to create temporary database connection: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * checkIfInstalled checks if zp user table exists (and assumes that leantime is installed)
     */
    public function checkIfInstalled(): bool
    {

        if (Cache::has('isInstalled') && Cache::get('isInstalled') === true) {
            return true;
        }

        try {

            // Use Laravel's database connection
            $this->connection->statement('USE `'.$this->config->dbDatabase.'`');

            $count = $this->connection->table('zp_user')->count();

            Cache::set('isInstalled', true);

            return true;
        } catch (PDOException $e) {

            Cache::forget('isInstalled');

            Log::error($e);

            return false;
        }
    }

    /**
     * setupDB installs database
     *
     * @param  array  $values  Form values for admin user and company information
     * @param  string  $db
     */
    public function setupDB(array $values, $db = ''): bool
    {

        $sql = $this->sqlPrep();

        try {
            // Use Laravel's database connection
            $dbName = $db ?: $this->config->dbDatabase;
            $this->connection->statement('USE `'.$dbName.'`');

            $pwReset = Str::random(32);
            session()->put('pwReset', $pwReset);

            // Use PDO for multi-statement SQL with parameter binding
            // We need to use PDO directly because Laravel's statement() method
            // may not handle multi-statement SQL properly
            $pdo = $this->connection->getPdo();
            $stmn = $pdo->prepare($sql);

            $stmn->bindValue(':email', $values['email'], PDO::PARAM_STR);
            $stmn->bindValue(':firstname', $values['firstname'], PDO::PARAM_STR);
            $stmn->bindValue(':lastname', $values['lastname'], PDO::PARAM_STR);
            $stmn->bindValue(':dbVersion', $this->settings->dbVersion, PDO::PARAM_STR);
            $stmn->bindValue(':company', $values['company'], PDO::PARAM_STR);
            $stmn->bindValue(':createdOn', date('Y-m-d H:i:s'), PDO::PARAM_STR);
            $stmn->bindValue(':pwReset', $pwReset, PDO::PARAM_STR);

            $stmn->execute();

            /** @noinspection PhpStatementHasEmptyBodyInspection */
            while ($stmn->nextRowset()) {/* https://bugs.php.net/bug.php?id=61613 */
            }

            return true;
        } catch (PDOException $e) {
            Log::error($e);

            return false;
        }
    }

    /**
     * updateDB main entry point to update the db based on version number. Executes all missing db update scripts
     *
     * @throws BindingResolutionException
     */
    public function updateDB(): array|bool
    {

        // Forget all the versions we think we know and start fresh
        session()->forget('db-version');
        $settingsCacheService = app()->make(SettingCache::class);
        $settingsCacheService->forget('db-version');

        $errors = [];

        $this->connection->statement('USE `'.$this->config->dbDatabase.'`');

        $versionArray = explode('.', $this->settings->dbVersion);
        if (is_array($versionArray) && count($versionArray) == 3) {
            $major = $versionArray[0];
            $minor = str_pad($versionArray[1], 2, '0', STR_PAD_LEFT);
            $patch = str_pad($versionArray[2], 2, '0', STR_PAD_LEFT);
            $newDBVersion = $major.$minor.$patch;
        } else {
            $errors[0] = 'Problem identifying the version number';

            return $errors;
        }

        $setting = app()->make(Setting::class);
        $dbVersion = $setting->getSetting('db-version');
        $currentDBVersion = 0;
        if ($dbVersion) {
            $versionArray = explode('.', $dbVersion);
            if (is_array($versionArray) && count($versionArray) == 3) {
                $major = $versionArray[0];
                $minor = str_pad($versionArray[1], 2, '0', STR_PAD_LEFT);
                $patch = str_pad($versionArray[2], 2, '0', STR_PAD_LEFT);
                $currentDBVersion = $major.$minor.$patch;
            } else {
                $errors[0] = 'Problem identifying the version number';

                return $errors;
            }
        }

        if ($currentDBVersion == $newDBVersion) {

            session()->forget('isUpdated');
            session()->forget('dbVersion');

            return true;
        }

        // Find all update functions that need to be executed
        foreach ($this->dbUpdates as $updateVersion) {
            if ($currentDBVersion < $updateVersion) {
                $functionName = 'update_sql_'.$updateVersion;

                $result = $this->$functionName();

                if ($result !== true) {
                    $errors = array_merge($errors, $result);
                } else {
                    // Update version number in db
                    try {

                        $settingsService = app()->make(\Leantime\Domain\Setting\Services\Setting::class);
                        $settingsService->saveSetting('db-version', $this->convert_version($updateVersion));

                        // $stmn = $this->database->prepare("INSERT INTO zp_settings (`key`, `value`) VALUES ('db-version', '".$this->settings->dbVersion."') ON DUPLICATE KEY UPDATE `value` = '".$this->settings->dbVersion."'");
                        // $stmn->execute();

                        $currentDBVersion = $updateVersion;
                    } catch (PDOException $e) {
                        Log::error($e);
                        Log::error($e->getTraceAsString());

                        return ['There was a problem updating the database'];
                    }
                }

                if (count($errors) > 0) {
                    return $errors;
                }
            }
        }

        session()->forget('isUpdated');
        session()->forget('dbVersion');

        return true;
    }

    private function convert_version($inputVersion): string
    {
        // $inputVersion is in the format of 10000 and needs to be converted to 1.0.0
        $versionString = str_pad((string) $inputVersion, 5, '0', STR_PAD_LEFT);

        $major = intval(substr($versionString, 0, -4));
        $minor = intval(substr($versionString, -4, 2));
        $patch = intval(substr($versionString, -2));

        return $major.'.'.$minor.'.'.$patch;
    }

    /**
     * sqlPrep - returns all the create table statements
     */
    private function sqlPrep(): string
    {

        $sql = "
                CREATE TABLE `zp_calendar` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `userId` int(11) DEFAULT NULL,
                    `dateFrom` datetime DEFAULT NULL,
                    `dateTo` datetime DEFAULT NULL,
                    `description` text,
                    `kind` varchar(255) DEFAULT NULL,
                    `allDay` varchar(10) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_calendar_userId_dateFrom_dateTo` (`userId`, `dateFrom`, `dateTo`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_canvas` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `title` varchar(255) DEFAULT NULL,
                    `author` int(10) DEFAULT NULL,
                    `created` datetime DEFAULT NULL,
                    `projectId` INT NULL,
                    `type` VARCHAR(45) NULL,
                    `description` TEXT,
                    `color` VARCHAR(50) DEFAULT 'ocean',
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ProjectIdType` (`projectId` ASC, `type` ASC),
                    KEY `idx_canvas_type_id` (`type`, `id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_canvas_items` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `description` MEDIUMTEXT,
                    `assumptions` text,
                    `data` MEDIUMTEXT,
                    `conclusion` text,
                    `box` varchar(255) DEFAULT NULL,
                    `author` int(11) DEFAULT NULL,
                    `created` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    `canvasId` int(11) DEFAULT NULL,
                    `sortindex` int(11) DEFAULT NULL,
                    `status` varchar(255) DEFAULT NULL,
                    `relates` varchar(255) DEFAULT NULL,
                    `milestoneId` VARCHAR(255) NULL,
                    `title` varchar(255) NULL,
                    `parent` int NULL,
                    `featured` int NULL,
                    `tags` text NULL,
                    `kpi` INT NULL DEFAULT NULL,
                    `data1` MEDIUMTEXT NULL DEFAULT NULL,
                    `data2` MEDIUMTEXT NULL DEFAULT NULL,
                    `data3` MEDIUMTEXT NULL DEFAULT NULL,
                    `data4` MEDIUMTEXT NULL DEFAULT NULL,
                    `data5` MEDIUMTEXT NULL DEFAULT NULL,
                    `startDate` DATETIME NULL DEFAULT NULL,
                    `endDate` DATETIME NULL DEFAULT NULL,
                    `setting` TEXT NULL DEFAULT NULL,
                    `metricType` VARCHAR(45) DEFAULT NULL,
                    `startValue` double(10,2) NULL DEFAULT NULL,
                    `currentValue` double(10,2) NULL DEFAULT NULL,
                    `endValue` double(10,2) NULL DEFAULT NULL,
                    `impact` INT NULL DEFAULT NULL,
                    `effort` INT NULL DEFAULT NULL,
                    `probability` INT NULL DEFAULT NULL,
                    `action` TEXT NULL DEFAULT NULL,
                    `assignedTo` INT NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `CanvasLookUp` (`canvasId` ASC, `box` ASC),
                    KEY `idx_canvas_items_box_milestoneId` (`box`, `milestoneId`),
                    KEY `idx_canvas_items_box_status_author` (`box`, `status`, `author`),
                    KEY `idx_canvas_items_parent_title` (`parent`, `title`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_approvals` (
                    `id` int auto_increment,
                    `module` varchar(100) NULL,
                    `entityId` int NULL,
                    `requestorId` int NULL,
                    `approverId` int NULL,
                    `approvalStatus` int NULL,
                    `requestedOn` datetime NULL,
                    `lastStatusChange` datetime NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_clients` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(200) DEFAULT NULL,
                    `street` varchar(200) DEFAULT NULL,
                    `zip` int(10) DEFAULT NULL,
                    `city` varchar(50) DEFAULT NULL,
                    `state` varchar(50) DEFAULT NULL,
                    `country` varchar(50) DEFAULT NULL,
                    `phone` varchar(50) DEFAULT NULL,
                    `internet` varchar(200) DEFAULT NULL,
                    `published` int(1) DEFAULT NULL,
                    `age` int(3) DEFAULT NULL,
                    `email` varchar(255) DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                INSERT INTO `zp_clients`(`id`,`name`,`street`,`zip`,`city`,`state`,`country`,`phone`,`internet`,`published`,`age`,`email`) VALUES (1,:company,'',0,'','','','','',NULL,NULL,'');

                CREATE TABLE `zp_comment` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `module` varchar(200) DEFAULT NULL,
                    `userId` int(11) DEFAULT NULL,
                    `commentParent` int(11) DEFAULT NULL,
                    `date` datetime DEFAULT NULL,
                    `moduleId` int(11) DEFAULT NULL,
                    `text` text,
                    `status` varchar(50) null,
                    PRIMARY KEY (`id`),
                    KEY `idx_comment_moduleId_module_commentParent` (`moduleId`, `module`, `commentParent`),
                    KEY `idx_comment_userId_module` (`userId`, `module`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_file` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `module` enum('project','ticket','client','user','lead','export','private') DEFAULT NULL,
                    `moduleId` int(11) DEFAULT NULL,
                    `userId` int(11) DEFAULT NULL,
                    `extension` varchar(10) DEFAULT NULL,
                    `encName` varchar(255) DEFAULT NULL,
                    `realName` varchar(255) DEFAULT NULL,
                    `date` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_file_module_moduleId_userId` (`module`, `moduleId`, `userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_gcallinks` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `userId` int(255) DEFAULT NULL,
                    `url` text,
                    `name` varchar(255) DEFAULT NULL,
                    `colorClass` varchar(100) DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_gcallinks_userId` (`userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_note` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `userId` int(11) DEFAULT NULL,
                    `title` varchar(255) DEFAULT NULL,
                    `description` text,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_projects` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `name` varchar(100) DEFAULT NULL,
                    `clientId` int(100) DEFAULT NULL,
                    `details` text,
                    `state` int(2) DEFAULT NULL,
                    `hourBudget` varchar(255) NOT NULL,
                    `dollarBudget` int(11) DEFAULT NULL,
                    `active` int(11) DEFAULT NULL,
                    `menuType` MEDIUMTEXT DEFAULT NULL,
                    `psettings` MEDIUMTEXT NULL,
                    `parent` INT(11) NULL,
                    `type` VARCHAR(45) NULL,
                    `start` DATETIME NULL,
                    `end` DATETIME NULL,
                    `created` DATETIME NULL,
                    `modified` DATETIME NULL,
                    `avatar` MEDIUMTEXT NULL ,
                    `cover` MEDIUMTEXT NULL,
                    `sortIndex` INT(11) NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_punch_clock` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `userId` int(11) NOT NULL,
                    `minutes` int(11) DEFAULT NULL,
                    `hours` int(11) DEFAULT NULL,
                    `punchIn` int(11) DEFAULT NULL,
                    PRIMARY KEY (`id`,`userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_read` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `module` enum('ticket','message') DEFAULT NULL,
                    `moduleId` int(11) DEFAULT NULL,
                    `userId` int(11) DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_relationuserproject` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `userId` int(11) DEFAULT NULL,
                    `projectId` int(11) DEFAULT NULL,
                    `wage` int(11) DEFAULT NULL,
                    `projectRole` varchar(20),
                    PRIMARY KEY (`id`),
                    KEY zp_relationuserproject_projectId_index (`projectId`),
                    KEY zp_relationuserproject_userId_index  (`userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_tickethistory` (
                    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                    `userId` int(11) DEFAULT NULL,
                    `ticketId` int(11) DEFAULT NULL,
                    `changeType` varchar(255) DEFAULT NULL,
                    `changeValue` varchar(150) DEFAULT NULL,
                    `dateModified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_tickets` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `projectId` int(11) DEFAULT NULL,
                    `headline` varchar(255) DEFAULT NULL,
                    `description` text,
                    `acceptanceCriteria` text,
                    `date` datetime DEFAULT NULL,
                    `dateToFinish` datetime DEFAULT NULL,
                    `priority` varchar(60) DEFAULT NULL,
                    `status` int(2) DEFAULT NULL,
                    `userId` int(11) DEFAULT NULL,
                    `os` varchar(30) DEFAULT NULL,
                    `browser` varchar(30) DEFAULT NULL,
                    `resolution` varchar(30) DEFAULT NULL,
                    `component` varchar(100) DEFAULT NULL,
                    `version` varchar(20) DEFAULT NULL,
                    `url` varchar(100) DEFAULT NULL,
                    `dependingTicketId` int(100) DEFAULT NULL,
                    `editFrom` datetime DEFAULT NULL,
                    `editTo` datetime DEFAULT NULL,
                    `editorId` varchar(75) DEFAULT NULL,
                    `planHours` float DEFAULT NULL,
                    `hourRemaining` float DEFAULT NULL,
                    `type` varchar(255) DEFAULT NULL,
                    `production` int(1) DEFAULT '0',
                    `staging` int(1) DEFAULT '0',
                    `storypoints` float DEFAULT NULL,
                    `sprint` int(100) DEFAULT NULL,
                    `sortindex` bigint(20) DEFAULT NULL,
                    `kanbanSortIndex` bigint(20) DEFAULT NULL,
                    `tags` varchar(255) DEFAULT NULL,
                    `milestoneid` INT NULL,
                    `leancanvasitemid` INT NULL,
                    `retrospectiveid` INT NULL,
                    `ideaid` INT NULL,
                    `zp_ticketscol` VARCHAR(45) NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `ProjectUserId` (`projectId`,`userId`),
                    KEY `StatusSprint` (`status`,`sprint`),
                    KEY `Sorting` (`sortindex`),
                    KEY `idx_tickets_editorId` (`editorId`),
                    KEY `idx_tickets_milestoneid` (`milestoneid`),
                    KEY `idx_tickets_editFrom` (`editFrom`),
                    KEY `idx_tickets_editTo` (`editTo`),
                    KEY `idx_tickets_dateToFinish` (`dateToFinish`),
                    KEY `idx_tickets_modified` (`modified`),
                    KEY `idx_tickets_projectId_status` (`projectId`, `status`),
                    KEY `idx_tickets_projectId_type` (`projectId`, `type`),
                    KEY `idx_tickets_status_type` (`status`, `type`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_timesheets` (
                    `id` int(255) NOT NULL AUTO_INCREMENT,
                    `userId` int(11) DEFAULT NULL,
                    `ticketId` int(11) DEFAULT NULL,
                    `workDate` datetime DEFAULT NULL,
                    `hours` float DEFAULT NULL,
                    `description` text,
                    `kind` varchar(175) DEFAULT NULL,
                    `invoicedEmpl` int(2) DEFAULT NULL,
                    `invoicedComp` int(2) DEFAULT NULL,
                    `invoicedEmplDate` datetime DEFAULT NULL,
                    `invoicedCompDate` datetime DEFAULT NULL,
                    `rate` varchar(255) DEFAULT NULL,
                    `paid` int(2) DEFAULT NULL,
                    `paidDate` datetime DEFAULT NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `Unique` (`userId`,`ticketId`,`workDate`,`kind`),
                    KEY `idx_timesheets_ticketId` (`ticketId`),
                    KEY `idx_timesheets_userId_workDate` (`userId`, `workDate`),
                    KEY `idx_timesheets_ticketId_workDate` (`ticketId`, `workDate`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_user` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `username` varchar(175) NOT NULL,
                    `password` varchar(255) NOT NULL DEFAULT '',
                    `firstname` varchar(100) NOT NULL,
                    `lastname` varchar(100) NOT NULL,
                    `phone` varchar(25) NOT NULL,
                    `profileId` varchar(100) NOT NULL DEFAULT '',
                    `lastlogin` datetime DEFAULT NULL,
                    `status` varchar(1) NOT NULL DEFAULT 'A',
                    `expires` DATETIME DEFAULT NULL,
                    `role` varchar(200) NOT NULL,
                    `session` varchar(100) DEFAULT NULL,
                    `sessiontime` varchar(50) DEFAULT NULL,
                    `wage` int(11) DEFAULT NULL,
                    `hours` int(11) DEFAULT NULL,
                    `description` text,
                    `clientId` int(11) DEFAULT NULL,
                    `notifications` int(2) DEFAULT NULL,
                    `pwReset` varchar(100) DEFAULT NULL,
                    `pwResetExpiration` datetime DEFAULT NULL,
                    `pwResetCount` INT(5) DEFAULT NULL,
                    `forcePwReset` TINYINT DEFAULT NULL,
                    `lastpwd_change` DATETIME DEFAULT NULL,
                    `settings` TEXT NULL,
                    `twoFAEnabled` tinyint(1) DEFAULT '0',
                    `twoFASecret` varchar(200) DEFAULT NULL,
                    `createdOn` DATETIME DEFAULT NULL,
                    `source` varchar(200) DEFAULT NULL,
                    `jobTitle` VARCHAR(200) NULL,
                    `jobLevel` VARCHAR(50) NULL,
                    `department` VARCHAR(200) NULL,
                    `modified` DATETIME DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `username` (`username`),
                    KEY `idx_user_clientId` (`clientId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                INSERT INTO `zp_user`(`id`,`username`,`firstname`,`lastname`,`phone`,`profileId`,`lastlogin`,`lastpwd_change`,`status`,`expires`,`role`,`session`,`sessiontime`,`wage`,`hours`,`description`,`clientId`, `notifications`, `createdOn`, `pwReset`)
                VALUES (1,:email,:firstname,:lastname,'','',NULL,0,'i',NULL,'50','','',0,0,NULL,0,1, :createdOn, :pwReset);

                CREATE TABLE `zp_sprints` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `projectId` INT NULL,
                    `name` VARCHAR(45) NULL,
                    `startDate` DATETIME NULL,
                    `endDate` DATETIME NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    KEY `idx_sprints_projectId_startDate_endDate` (`projectId`, `startDate`, `endDate`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_stats` (
                    `sprintId` INT NULL,
                    `projectId` INT NULL,
                    `date` DATETIME NULL,
                    `sum_todos` INT NULL,
                    `sum_open_todos` INT NULL,
                    `sum_progres_todos` INT NULL,
                    `sum_closed_todos` INT NULL,
                    `sum_planned_hours` FLOAT NULL,
                    `sum_estremaining_hours` FLOAT NULL,
                    `sum_logged_hours` FLOAT NULL,
                    `sum_points` INT NULL,
                    `sum_points_done` INT NULL,
                    `sum_points_progress` INT NULL,
                    `sum_points_open` INT NULL,
                    `sum_todos_xs` INT NULL,
                    `sum_todos_s` INT NULL,
                    `sum_todos_m` INT NULL,
                    `sum_todos_l` INT NULL,
                    `sum_todos_xl` INT NULL,
                    `sum_todos_xxl` INT NULL,
                    `sum_todos_none` INT NULL,
                    `tickets` TEXT NULL,
                    `daily_avg_hours_booked_todo` FLOAT NULL,
                    `daily_avg_hours_booked_point` FLOAT NULL,
                    `daily_avg_hours_planned_todo` FLOAT NULL,
                    `daily_avg_hours_planned_point` FLOAT NULL,
                    `daily_avg_hours_remaining_point` FLOAT NULL,
                    `daily_avg_hours_remaining_todo` FLOAT NULL,
                    `sum_teammembers` INT NULL,
                    INDEX `projectId` (`projectId` ASC, `sprintId` ASC),
                    KEY `idx_stats_projectId_sprintId_date` (`projectId`, `sprintId`, `date`),
                    KEY `idx_stats_sprintId_date` (`sprintId`, `date`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_settings` (
                    `key` VARCHAR(175) NOT NULL,
                    `value` MEDIUMTEXT NULL,
                    PRIMARY KEY (`key`),
                    KEY `idx_settings_key` (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                INSERT INTO zp_settings (`key`, `value`) VALUES ('db-version', :dbVersion);
                INSERT INTO zp_settings (`key`, `value`) VALUES ('companysettings.telemetry.active', 'true');

                CREATE TABLE `zp_audit` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `userId` INT NULL,
                    `projectId` INT NULL,
                    `action` VARCHAR(45) NULL,
                    `entity` VARCHAR(45) NULL,
                    `entityId` INT NULL,
                    `values` TEXT NULL,
                    `date` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    KEY `projectId` (`projectId` ASC),
                    KEY `projectAction` (`projectId` ASC, `action` ASC),
                    KEY `projectEntityEntityId` (`projectId` ASC, `entity` ASC, `entityId` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_queue` (
                    `msghash` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
                    `channel` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `userId` int(11) NOT NULL,
                    `subject` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                    `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
                    `thedate` datetime NOT NULL,
                    `projectId` int(11) NOT NULL,
                    PRIMARY KEY (`msghash`),
                    KEY `projectId` (`projectId`),
                    KEY `userId` (`userId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_plugins` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `name` VARCHAR(45) NULL,
                    `enabled` TINYINT NULL,
                    `description` VARCHAR(255) NULL,
                    `version` VARCHAR(45) NULL,
                    `installdate` DATETIME NULL,
                    `foldername` VARCHAR(45),
                    `homepage` VARCHAR(255) NULL,
                    `authors` VARCHAR(255) NULL,
                    `license` TEXT NULL DEFAULT NULL,
                    `format` VARCHAR(45) NULL DEFAULT NULL,
                    PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_notifications` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `userId` INT NOT NULL,
                    `read` INT NULL,
                    `type` VARCHAR(45) NULL,
                    `module` VARCHAR(45) NULL,
                    `moduleId` INT NULL,
                    `datetime` DATETIME NULL,
                    `url` VARCHAR(255) NULL,
                    `authorId` INT NULL,
                    `message` TEXT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `userId` (`userId` ASC),
                    INDEX `userId,datetime` (`userId` ASC, `datetime` DESC),
                    INDEX `userId,read` (`userId` ASC, `read` DESC)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_entity_relationship` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `entityA` INT NULL,
                    `entityAType` VARCHAR(45) NULL,
                    `entityB` INT NULL,
                    `entityBType` VARCHAR(45) NULL,
                    `relationship` VARCHAR(45) NULL,
                    `createdOn` DATETIME NULL,
                    `createdBy` INT NULL,
                    `meta` TEXT NULL,
                    PRIMARY KEY (`id`),
                    INDEX `entityA` (`entityA` ASC, `entityAType` ASC, `relationship` ASC),
                    INDEX `entityB` (`entityB` ASC, `entityBType` ASC, `relationship` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_integration` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `providerId` VARCHAR(45) NULL,
                    `method` VARCHAR(45) NULL,
                    `entity` VARCHAR(45) NULL,
                    `fields` TEXT NULL,
                    `schedule` VARCHAR(45) NULL,
                    `notes` VARCHAR(45) NULL,
                    `auth` TEXT NULL,
                    `meta` VARCHAR(45) NULL,
                    `createdOn` DATETIME NULL,
                    `createdBy` INT NULL,
                    `lastSync` VARCHAR(45) NULL,
                    PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_reactions` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `userId` INT NULL,
                    `moduleId` INT NULL,
                    `module` VARCHAR(45) NULL,
                    `reaction` VARCHAR(45) NULL,
                    `date` DATETIME NULL,
                    PRIMARY KEY (`id`),
                    INDEX `entity` (`moduleId` ASC, `module` ASC, `reaction` ASC),
                    INDEX `user` (`userId` ASC, `moduleId` ASC, `module` ASC, `reaction` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_access_tokens` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `tokenable_id` bigint unsigned NOT NULL,
                    `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                    `last_used_at` timestamp NULL DEFAULT NULL,
                    `expires_at` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
                    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_jobs` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `attempts` tinyint unsigned NOT NULL,
                    `reserved_at` int unsigned DEFAULT NULL,
                    `available_at` int unsigned NOT NULL,
                    `created_at` int unsigned NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `zp_jobs_queue_index` (`queue`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE IF NOT EXISTS `zp_recurring_patterns` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `entityId` INT NOT NULL,
                    `module` VARCHAR(50) NOT NULL,
                    `type` VARCHAR(50) NOT NULL,
                    `trigger` VARCHAR(50) NOT NULL,
                    `interval` INT NOT NULL DEFAULT 1,
                    `weekDays` TEXT NULL,
                    `monthDay` INT NULL,
                    `months` TEXT NULL,
                    `action` VARCHAR(20) NOT NULL DEFAULT 'reset',
                    `lastProcessed` DATETIME NULL,
                    `nextProcessingDate` DATETIME NULL,
                    `enabled` TINYINT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (`id`),
                    INDEX `entityId` (`entityId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            ";

        return $sql;
    }

    /**
     * update_sql_20004 - database update sql for V2.0.4
     * - Updates all tables and db to utf8mb4
     * - converts 255 index to be smaller
     *
     * @noinspection SqlResolve - A lot of tables don't exist anymore, so this will not resolve. Keeping the update script for backwards compatibility
     */
    private function update_sql_20004(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_wiki_articles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_submodulerights` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_canvas` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_wiki_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_tickethistory` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_gcallinks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_message` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_note` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_timesheets` MODIFY kind VARCHAR(175);',
            'ALTER TABLE `zp_timesheets` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_roles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_projects` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_modulerights` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_wiki_comments` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_punch_clock` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_clients` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_account` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_sprints` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_lead` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_user` MODIFY username VARCHAR(175);',
            'ALTER TABLE `zp_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_settings` MODIFY `key` VARCHAR(175);',
            'ALTER TABLE `zp_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_comment` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_stats` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_tickets` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_canvas_items` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_dashboard_widgets` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_file` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_action_tabs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_relationuserproject` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_calendar` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_read` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_wiki` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20100(): bool|array
    {

        $errors = [];

        $sql = [
            'UPDATE `zp_user` SET role = 50 WHERE role = 2;',
            'UPDATE `zp_user` SET role = 10 WHERE role = 3;',
            'UPDATE `zp_user` SET role = 20 WHERE role = 4;',
            'UPDATE `zp_user` SET role = 40 WHERE role = 5;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20101(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_comment` CHANGE COLUMN `module` `module` VARCHAR(200) NULL DEFAULT NULL ;',
            'ALTER TABLE `zp_stats`
                    ADD COLUMN `sum_teammembers` INT(11) NULL DEFAULT NULL AFTER `daily_avg_hours_remaining_todo`,
                    CHANGE COLUMN `sum_planned_hours` `sum_planned_hours` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `sum_logged_hours` `sum_logged_hours` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `sum_estremaining_hours` `sum_estremaining_hours` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_booked_todo` `daily_avg_hours_booked_todo` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_booked_point` `daily_avg_hours_booked_point` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_planned_todo` `daily_avg_hours_planned_todo` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_planned_point` `daily_avg_hours_planned_point` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_remaining_point` `daily_avg_hours_remaining_point` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_remaining_todo` `daily_avg_hours_remaining_todo` FLOAT NULL DEFAULT NULL ;',
            'CREATE TABLE `zp_audit` (
                      `id` INT NOT NULL AUTO_INCREMENT,
                      `userId` INT NULL,
                      `projectId` INT NULL,
                      `action` VARCHAR(45) NULL,
                      `entity` VARCHAR(45) NULL,
                      `entityId` INT NULL,
                      `values` TEXT NULL,
                      `date` DATETIME NULL,
                      PRIMARY KEY (`id`),
                      KEY `projectId` (`projectId` ASC),
                      KEY `projectAction` (`projectId` ASC, `action` ASC),
                      KEY `projectEntityEntityId` (`projectId` ASC, `entity` ASC, `entityId` ASC)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20102(): bool|array
    {
        $errors = [];

        $sql = [
            "ALTER TABLE `zp_user` add COLUMN `twoFAEnabled` tinyint(1) DEFAULT '0'",
            'ALTER TABLE `zp_user` add COLUMN `twoFASecret` varchar(200) DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20103(): bool|array
    {
        $errors = [];

        $sql = [
            'ALTER TABLE `zp_tickets` CHANGE COLUMN `planHours` `planHours` FLOAT NULL DEFAULT NULL',
            'ALTER TABLE `zp_tickets` CHANGE COLUMN `hourRemaining` `hourRemaining` FLOAT NULL DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20104(): bool|array
    {
        $errors = [];

        $sql = [
            'ALTER TABLE `zp_user` ADD COLUMN `pwResetCount` INT(5) NULL AFTER `pwResetExpiration`',
            'ALTER TABLE `zp_user` ADD COLUMN `forcePwReset` TINYINT NULL AFTER `pwResetCount`',
            'ALTER TABLE `zp_user` ADD COLUMN `createdOn` DATETIME NULL AFTER `twoFASecret`',
            'ALTER TABLE `zp_user` CHANGE COLUMN `lastpwd_change` `lastpwd_change` DATETIME NULL DEFAULT NULL AFTER `forcePwReset`',
            'ALTER TABLE `zp_user` CHANGE COLUMN `expires` `expires` DATETIME NULL DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20105(): bool|array
    {
        $errors = [];

        $sql = [
            'ALTER TABLE `zp_projects` ADD COLUMN `psettings` MEDIUMTEXT NULL AFTER `active`',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20106(): bool|array
    {
        $errors = [];

        $sql = [
            'ALTER TABLE `zp_user` ADD COLUMN `source` varchar(200) DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20107(): bool|array
    {
        $errors = [];

        $sql = [
            "INSERT INTO zp_settings (`key`, `value`) VALUES ('companysettings.telemetry.active', 'true')",
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20108(): bool|array
    {
        $errors = [];

        $sql = [
            'alter table zp_relationuserproject add `projectRole` varchar(20) null',
            'create index zp_relationuserproject_projectId_index on zp_relationuserproject (projectId)',
            'create index zp_relationuserproject_userId_index on zp_relationuserproject (userId)',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20109(): bool|array
    {

        $errors = [];

        $sql = [
            'CREATE TABLE IF NOT EXISTS `zp_queue` (
                               `msghash` varchar(50) NOT NULL,
                                `channel` varchar(255),
                               `userId` int(11) NOT NULL,
                                `subject` varchar(255),
                               `message` text NOT NULL,
                               `thedate` datetime NOT NULL,
                               `projectId` int(11) NOT NULL,
                               PRIMARY KEY (`msghash`),
                               KEY `projectId` (`projectId`),
                               KEY `userId` (`userId`)
			   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    private function update_sql_20110(): bool|array
    {

        $errors = [];

        $sql = [
            'alter table zp_canvas_items add tags text null',
            'alter table zp_canvas_items add title varchar(255) null',
            'alter table zp_canvas_items add parent int null',
            'alter table zp_canvas_items add featured int null',
            'create table zp_approvals
                (
                    id               int auto_increment,
                    module           varchar(100) null,
                    entityId         int          null,
                    requestorId      int          null,
                    approverId       int          null,
                    approvalStatus   int          null,
                    requestedOn      datetime     null,
                    lastStatusChange datetime     null,
                    constraint zp_approvals_pk
                        primary key (id)
                )',
            'alter table zp_comment add status varchar(50) null',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    /*         * *
     * update_sql_20111 - Update database for new Canvas
     *
     * @access private
     * @return bool|array    Success of database update or array of errors
     */

    private function update_sql_20111(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE zp_projects ADD menuType MEDIUMTEXT null',
            "UPDATE zp_projects SET menuType = '".MenuRepository::DEFAULT_MENU."'",
            'ALTER TABLE zp_canvas_items ADD relates VARCHAR(255) null',
            'UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id '.
            "SET zp_canvas_items.status = 'draft' WHERE zp_canvas_items.status = 'danger' AND zp_canvas.type = 'leancanvas'",
            'UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id '.
            "SET zp_canvas_items.status = 'valid' WHERE zp_canvas_items.status = 'sucess' AND zp_canvas.type = 'leancanvas'",
            'UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id '.
            "SET zp_canvas_items.status = 'invalid' WHERE zp_canvas_items.status = 'info' AND zp_canvas.type = 'leancanvas'",
            "UPDATE zp_canvas SET zp_canvas.type = 'retroscanvas' WHERE zp_canvas.type = 'retrospective'",
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    /*         * *
     * update_sql_20112 - Update database for new Canvas
     *
     * @access private
     * @return bool|array    Success of database update or array of errors
     */

    private function update_sql_20112(): bool|array
    {

        $errors = [];

        $sql = [
            'CREATE TABLE `zp_plugins` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `name` VARCHAR(45) NULL,
                  `enabled` TINYINT NULL,
                  `description` VARCHAR(255) NULL,
                  `version` VARCHAR(45) NULL,
                  `installdate` DATETIME NULL,
                  `foldername` VARCHAR(45) NULL,
                  `homepage` VARCHAR(255) NULL,
                  `authors` VARCHAR(255) NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'ALTER TABLE `zp_timesheets` ADD COLUMN `paid` SMALLINT NULL AFTER `rate`, ADD COLUMN `paidDate` DATETIME NULL AFTER `paid`;',
            'DROP TABLE IF EXISTS zp_account, zp_action_tabs, zp_dashboard_widgets, zp_lead, zp_message, zp_modulerights, zp_roles, zp_submodulerights, zp_wiki, zp_wiki_articles, zp_wiki_categories, zp_wiki_comments;',
            'CREATE TABLE `zp_notifications` (
                  `id` INT NOT NULL AUTO_INCREMENT,
                  `userId` INT NOT NULL,
                  `read` INT NULL,
                  `type` VARCHAR(45) NULL,
                  `module` VARCHAR(45) NULL,
                  `moduleId` INT NULL,
                  `datetime` DATETIME NULL,
                  `url` VARCHAR(255) NULL,
                  `authorId` INT NULL,
                  `message` TEXT NULL,
                  PRIMARY KEY (`id`),
                  INDEX `userId` (`userId` ASC),
                  INDEX `userId,datetime` (`userId` ASC, `datetime` DESC),
                  INDEX `userId,read` (`userId` ASC, `read` DESC)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    /* * *
     * update_sql_20113 - Create onboarding setting for first time installs
     *
     * @access private
     * @return bool|array    Success of database update or array of errors
     */

    private function update_sql_20113(): bool|array
    {

        $errors = [];

        $sql = [
            "INSERT INTO zp_settings (`key`, `value`) VALUES ('companysettings.completedOnboarding', 'true') ON DUPLICATE KEY UPDATE `value` = 'true'",
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20114(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_projects`
                ADD COLUMN `type` VARCHAR(45) NULL,
                ADD COLUMN `start` DATETIME NULL,
                ADD COLUMN `end` DATETIME NULL,
                ADD COLUMN `created` DATETIME NULL,
                ADD COLUMN `modified` DATETIME NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20115(): bool|array
    {

        $errors = [];

        $sql = [
            ' CREATE TABLE `zp_entity_relationships` (
                        `id` INT NOT NULL AUTO_INCREMENT,
                        `enitityA` INT NULL,
                        `entityAType` VARCHAR(45) NULL,
                        `entityB` INT NULL,
                        `entityBType` VARCHAR(45) NULL,
                        `relationship` VARCHAR(45) NULL,
                        `createdOn` DATETIME NULL,
                        `createdBy` INT NULL,
                        `meta` TEXT NULL,
                        PRIMARY KEY (`id`),
                        INDEX `entityA` (`enitityA` ASC, `entityAType` ASC, `relationship` ASC),
                        INDEX `entityB` (`entityB` ASC, `entityBType` ASC, `relationship` ASC)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            "UPDATE `zp_tickets` SET milestoneid = dependingTicketId , dependingTicketId = '' WHERE type <> 'subtask' AND dependingTicketId > 0",
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20116(): bool|array
    {

        $errors = [];

        $sql = [
            ' CREATE TABLE `zp_reactions` (
                      `id` INT NOT NULL AUTO_INCREMENT,
                      `userId` INT NULL,
                      `moduleId` INT NULL,
                      `module` VARCHAR(45) NULL,
                      `reaction` VARCHAR(45) NULL,
                      `date` DATETIME NULL,
                      PRIMARY KEY (`id`),
                      INDEX `entity` (`moduleId` ASC, `module` ASC, `reaction` ASC),
                      INDEX `user` (`userId` ASC, `moduleId` ASC, `module` ASC, `reaction` ASC)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'ALTER TABLE `zp_projects`
                ADD COLUMN `avatar` MEDIUMTEXT NULL AFTER `modified`,
                ADD COLUMN `cover` MEDIUMTEXT NULL AFTER `avatar`;',

        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20117(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_projects`
                ADD COLUMN `parent` INT(11) NULL;',

            'ALTER TABLE `zp_projects`
                ADD COLUMN `sortIndex` INT(11) NULL;',

            'ALTER TABLE `zp_user`
                ADD COLUMN `jobTitle` VARCHAR(200) NULL ,
                ADD COLUMN `jobLevel` VARCHAR(50) NULL ,
                ADD COLUMN `department` VARCHAR(200) NULL ;',

        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20118(): bool|array
    {

        $errors = [];

        $sql = [

            'UPDATE `zp_projects` SET parent = null;',

            'UPDATE `zp_projects` SET start = null, end = null;',

            'ALTER TABLE `zp_projects`
                CHANGE COLUMN `parent` `parent` INT(11) NULL DEFAULT NULL,
                CHANGE COLUMN `type` `type` VARCHAR(45) NULL DEFAULT NULL ;',

        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20120(): bool|array
    {

        $errors = [];

        $sql = [

            'ALTER TABLE `zp_canvas_items`
                ADD COLUMN `kpi` INT NULL DEFAULT NULL AFTER `tags`,
                ADD COLUMN `data1` TEXT NULL DEFAULT NULL AFTER `kpi`,
                ADD COLUMN `data2` TEXT NULL DEFAULT NULL AFTER `data1`,
                ADD COLUMN `data3` TEXT NULL DEFAULT NULL AFTER `data2`,
                ADD COLUMN `data4` TEXT NULL DEFAULT NULL AFTER `data3`,
                ADD COLUMN `data5` TEXT NULL DEFAULT NULL AFTER `data4`,
                ADD COLUMN `startDate` DATETIME NULL DEFAULT NULL AFTER `data5`,
                ADD COLUMN `endDate` DATETIME NULL DEFAULT NULL AFTER `startDate`,
                ADD COLUMN `setting` TEXT NULL DEFAULT NULL AFTER `endDate`,
                ADD COLUMN `metricType` VARCHAR(45) NULL DEFAULT NULL AFTER `setting`,
                ADD COLUMN `startValue` double(10,2) NULL DEFAULT NULL AFTER `metricType`,
                ADD COLUMN `currentValue` double(10,2) NULL DEFAULT NULL AFTER `startValue`,
                ADD COLUMN `endValue` double(10,2) NULL DEFAULT NULL AFTER `currentValue`,
                ADD COLUMN `impact` INT NULL DEFAULT NULL AFTER `endValue`,
                ADD COLUMN `effort` INT NULL DEFAULT NULL AFTER `impact`,
                ADD COLUMN `probability` INT NULL DEFAULT NULL AFTER `effort`,
                ADD COLUMN `action` TEXT NULL DEFAULT NULL AFTER `probability`,
                ADD COLUMN `assignedTo` INT NULL DEFAULT NULL AFTER `action`;',
            "UPDATE zp_canvas_items SET
                    currentValue = CAST(CASE WHEN `data` = '' THEN 0 ELSE `data` END AS DECIMAL(10,2)),
                    endValue = CAST(CASE WHEN `conclusion` = '' THEN 0 ELSE `conclusion` END AS DECIMAL(10,2)),
                    title = description,
                    description = assumptions
                    WHERE box = 'goal';",
            'ALTER TABLE `zp_canvas_items`
                ADD INDEX `CanvasLookUp` (`canvasId` ASC, `box` ASC);',

        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20121(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_canvas`
                    ADD COLUMN `description` TEXT NULL DEFAULT NULL AFTER `type`;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20122(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_settings`
                    CHANGE COLUMN `value` `value` MEDIUMTEXT NULL DEFAULT NULL ;',
            'ALTER TABLE `zp_canvas_items`
                    CHANGE COLUMN `description` `description` MEDIUMTEXT NULL DEFAULT NULL ,
                    CHANGE COLUMN `data` `data` MEDIUMTEXT NULL DEFAULT NULL;',
            'ALTER TABLE `zp_user`
                    ADD COLUMN `modified` DATETIME NULL DEFAULT NULL;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20401(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_plugins`
                ADD COLUMN `license` TEXT NULL DEFAULT NULL,
                ADD COLUMN `format` VARCHAR(45) NULL DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20402(): bool|array
    {
        $errors = [];

        $sql = [
            'ALTER TABLE `zp_plugins`
                ADD COLUMN `format` VARCHAR(45) NULL DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                $errors[] = "$statement Failed: {$e->getMessage()}";
            }
        }

        return count($errors) ? $errors : true;
    }

    /**
     * Install script did not include medium text updates. Run again
     */
    public function update_sql_20405(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_settings`
                    CHANGE COLUMN `value` `value` MEDIUMTEXT NULL DEFAULT NULL ;',
            'ALTER TABLE `zp_canvas_items`
                    CHANGE COLUMN `description` `description` MEDIUMTEXT NULL DEFAULT NULL ,
                    CHANGE COLUMN `data` `data` MEDIUMTEXT NULL DEFAULT NULL;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    /**
     * Install script did not include medium text updates. Run again
     */
    public function update_sql_20406(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_canvas_items`
                    CHANGE COLUMN `data1` `data1` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data2` `data2` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data3` `data3` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data4` `data4` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data5` `data5` MEDIUMTEXT NULL DEFAULT NULL;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_20407(): bool|array
    {

        $errors = [];

        $sql = [
            'CREATE TABLE IF NOT EXISTS `zp_integration` (
                      `id` INT NOT NULL AUTO_INCREMENT,
                      `providerId` VARCHAR(45) NULL,
                      `method` VARCHAR(45) NULL,
                      `entity` VARCHAR(45) NULL,
                      `fields` TEXT NULL,
                      `schedule` VARCHAR(45) NULL,
                      `notes` VARCHAR(45) NULL,
                      `auth` TEXT NULL,
                      `meta` VARCHAR(45) NULL,
                      `createdOn` DATETIME NULL,
                      `createdBy` INT NULL,
                      PRIMARY KEY (`id`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
            'ALTER TABLE `zp_integration`
                    ADD COLUMN `lastSync` DATETIME NULL DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                array_push($errors, $statement.' Failed:'.$e->getMessage());
            }
        }

        if (count($errors) > 0) {
            return $errors;
        } else {
            return true;
        }
    }

    public function update_sql_30002(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_plugins` ADD COLUMN `license` TEXT NULL DEFAULT NULL',
            'ALTER TABLE `zp_plugins` ADD COLUMN `format` VARCHAR(45) NULL DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
            }
        }

        return true;
    }

    public function update_sql_30003(): bool|array
    {

        $errors = [];

        $sql = [
            'ALTER TABLE `zp_canvas` ADD COLUMN `modified` datetime NULL DEFAULT NULL',
            'ALTER TABLE `zp_clients` ADD COLUMN `modified` datetime NULL DEFAULT NULL',
            'ALTER TABLE `zp_sprints` ADD COLUMN `modified` datetime NULL DEFAULT NULL',
            'ALTER TABLE `zp_projects` ADD COLUMN `modified` datetime NULL DEFAULT NULL',
            'ALTER TABLE `zp_timesheets` ADD COLUMN `modified` datetime NULL DEFAULT NULL',
            'ALTER TABLE `zp_tickets` ADD COLUMN `modified` datetime NULL DEFAULT NULL',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
            }
        }

        return true;
    }

    public function update_sql_30400(): bool|array
    {

        $errors = [];

        $sql = [
            'CREATE TABLE `zp_access_tokens` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `tokenable_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `tokenable_id` bigint unsigned NOT NULL,
                    `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `abilities` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                    `last_used_at` timestamp NULL DEFAULT NULL,
                    `expires_at` timestamp NULL DEFAULT NULL,
                    `created_at` timestamp NULL DEFAULT NULL,
                    `updated_at` timestamp NULL DEFAULT NULL,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
                    KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'CREATE TABLE `zp_jobs` (
                    `id` bigint unsigned NOT NULL AUTO_INCREMENT,
                    `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
                    `attempts` tinyint unsigned NOT NULL,
                    `reserved_at` int unsigned DEFAULT NULL,
                    `available_at` int unsigned NOT NULL,
                    `created_at` int unsigned NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `zp_jobs_queue_index` (`queue`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;',
            'CREATE TABLE IF NOT EXISTS `zp_recurring_patterns` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `entityId` INT NOT NULL,
                    `module` VARCHAR(50) NOT NULL,
                    `type` VARCHAR(50) NOT NULL,
                    `trigger` VARCHAR(50) NOT NULL,
                    `interval` INT NOT NULL DEFAULT 1,
                    `weekDays` TEXT NULL,
                    `monthDay` INT NULL,
                    `months` TEXT NULL,
                    `action` VARCHAR(20) NOT NULL DEFAULT "reset",
                    `lastProcessed` DATETIME NULL,
                    `nextProcessingDate` DATETIME NULL,
                    `enabled` TINYINT(1) NOT NULL DEFAULT 1,
                    PRIMARY KEY (`id`),
                    INDEX `entityId` (`entityId`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
            }
        }

        return true;
    }

    public function update_sql_30408(): bool|array
    {

        $errors = [];

        $sql = [
            'INSERT INTO zp_settings (`key`, `value`)
                    SELECT
                        CONCAT("user.", `id`, ".firstLoginComplete") AS `key`,
                        1 AS `value`
                    FROM zp_user;',

        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
            }
        }

        return true;
    }

    /**
     * Database performance optimization - add indexes for frequently used queries
     */
    public function update_sql_30409(): bool|array
    {
        $errors = [];

        // High Priority Indexes - Critical Performance Impact
        $sql = [
            // Individual column indexes for tickets table
            'CREATE INDEX idx_tickets_editorId ON zp_tickets (editorId)',
            'CREATE INDEX idx_tickets_milestoneid ON zp_tickets (milestoneid)',
            'CREATE INDEX idx_tickets_editFrom ON zp_tickets (editFrom)',
            'CREATE INDEX idx_tickets_editTo ON zp_tickets (editTo)',
            'CREATE INDEX idx_tickets_dateToFinish ON zp_tickets (dateToFinish)',
            'CREATE INDEX idx_tickets_modified ON zp_tickets (modified)',

            // Medium Priority - Combined indexes for common filter patterns
            'CREATE INDEX idx_tickets_projectId_status ON zp_tickets (projectId, status)',
            'CREATE INDEX idx_tickets_projectId_type ON zp_tickets (projectId, type)',

            // User and project related indexes
            'CREATE INDEX idx_user_clientId ON zp_user (clientId)',
            'CREATE INDEX idx_projects_clientId ON zp_projects (clientId)',
            'CREATE INDEX idx_projects_state ON zp_projects (state)',

            // Timesheets performance
            'CREATE INDEX idx_timesheets_ticketId ON zp_timesheets (ticketId)',

            // Lower Priority - Additional combinations
            'CREATE INDEX idx_tickets_status_type ON zp_tickets (status, type)',
            'CREATE INDEX idx_tickethistory_ticketId_dateModified ON zp_tickethistory (ticketId, dateModified)',

            // Phase 2: Canvas, Comments, Files, Notifications, Calendar, Sprints Performance
            // Canvas and Canvas Items - High Priority for Ideas, Goals, Wiki
            'CREATE INDEX idx_canvas_projectId_type ON zp_canvas (projectId, type)',
            'CREATE INDEX idx_canvas_type_id ON zp_canvas (type, id)',
            'CREATE INDEX idx_canvas_items_canvasId_box ON zp_canvas_items (canvasId, box)',
            'CREATE INDEX idx_canvas_items_box_milestoneId ON zp_canvas_items (box, milestoneId)',
            'CREATE INDEX idx_canvas_items_box_status_author ON zp_canvas_items (box, status, author)',
            'CREATE INDEX idx_canvas_items_parent_title ON zp_canvas_items (parent, title)',

            // Notifications - High Priority for user interactions
            'CREATE INDEX idx_notifications_userId_read_datetime ON zp_notifications (userId, `read`, datetime)',

            // Timesheets - Additional High Priority indexes
            'CREATE INDEX idx_timesheets_userId_workDate ON zp_timesheets (userId, workDate)',
            'CREATE INDEX idx_timesheets_ticketId_workDate ON zp_timesheets (ticketId, workDate)',

            // Calendar - High Priority for scheduling
            'CREATE INDEX idx_calendar_userId_dateFrom_dateTo ON zp_calendar (userId, dateFrom, dateTo)',
            'CREATE INDEX idx_gcallinks_userId ON zp_gcallinks (userId)',

            // Comments - Medium Priority for threaded discussions
            'CREATE INDEX idx_comment_moduleId_module_commentParent ON zp_comment (moduleId, module, commentParent)',
            'CREATE INDEX idx_comment_userId_module ON zp_comment (userId, module)',

            // Files - Medium Priority for file access
            'CREATE INDEX idx_file_module_moduleId_userId ON zp_file (module, moduleId, userId)',

            // Sprints - Medium Priority for project management
            'CREATE INDEX idx_sprints_projectId_startDate_endDate ON zp_sprints (projectId, startDate, endDate)',

            // Settings - For project configuration lookups
            'CREATE INDEX idx_settings_key ON zp_settings (`key`)',

            // Stats table for reports (if exists) - Lower Priority
            'CREATE INDEX idx_stats_projectId_sprintId_date ON zp_stats (projectId, sprintId, date)',
            'CREATE INDEX idx_stats_sprintId_date ON zp_stats (sprintId, date)',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed:'.$e->getMessage());
                Log::error($e);
                // Don't fail the entire migration for duplicate indexes
                if (! str_contains($e->getMessage(), 'Duplicate key name')) {
                    array_push($errors, $statement.' Failed:'.$e->getMessage());
                }
            }
        }

        return count($errors) ? $errors : true;
    }

    /**
     * Migration 30410: Ensure zp_entity_relationship table exists with correct schema
     *
     * This migration must handle all possible database states from different upgrade paths:
     * - State A: Only plural table (zp_entity_relationships) exists with typo column (enitityA)
     * - State B: Only singular table (zp_entity_relationship) exists with correct column (entityA)
     * - State C: Only singular table exists with typo column (enitityA)
     * - State D: Both tables exist (from failed previous migrations)
     * - State E: Neither table exists (fresh install edge case)
     *
     * @return bool Always returns true to prevent blocking subsequent migrations
     */
    public function update_sql_30410(): bool|array
    {
        $pluralTable = 'zp_entity_relationships';
        $singularTable = 'zp_entity_relationship';

        try {
            $pluralExists = $this->tableExistsForMigration($pluralTable);
            $singularExists = $this->tableExistsForMigration($singularTable);

            // Case D: Both tables exist - merge data from plural into singular, then drop plural
            if ($pluralExists && $singularExists) {
                $this->mergeEntityRelationshipTables($pluralTable, $singularTable);
            }
            // Case A: Only plural exists - rename to singular
            elseif ($pluralExists && ! $singularExists) {
                $this->connection->statement("RENAME TABLE `{$pluralTable}` TO `{$singularTable}`");
            }
            // Case E: Neither exists - create singular with correct schema
            elseif (! $pluralExists && ! $singularExists) {
                $this->createEntityRelationshipTable();

                return true;
            }
            // Case B/C: Only singular exists - continue to fix column/index if needed

            // Fix column typo if present (handles Case A after rename, Case C, Case D after merge)
            $this->fixEntityAColumnTypo($singularTable);

            // Ensure correct index exists
            $this->ensureEntityAIndex($singularTable);

        } catch (\Exception $e) {
            Log::error('Migration 30410: '.$e->getMessage());
            // Don't fail the migration - log and continue
        }

        return true;
    }

    /**
     * Check if a table exists in the current database
     */
    private function tableExistsForMigration(string $tableName): bool
    {
        $result = $this->connection->select(
            'SELECT COUNT(*) as cnt FROM information_schema.tables
             WHERE table_schema = DATABASE() AND table_name = ?',
            [$tableName]
        );

        return $result[0]->cnt > 0;
    }

    /**
     * Check if a column exists in a table
     */
    private function columnExistsForMigration(string $tableName, string $columnName): bool
    {
        $result = $this->connection->select(
            'SELECT COUNT(*) as cnt FROM information_schema.columns
             WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [$tableName, $columnName]
        );

        return $result[0]->cnt > 0;
    }

    /**
     * Check if an index exists on a table
     */
    private function indexExistsForMigration(string $tableName, string $indexName): bool
    {
        $result = $this->connection->select(
            'SELECT COUNT(*) as cnt FROM information_schema.statistics
             WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ?',
            [$tableName, $indexName]
        );

        return $result[0]->cnt > 0;
    }

    /**
     * Merge data from plural table into singular table, then drop plural
     */
    private function mergeEntityRelationshipTables(string $sourceTable, string $targetTable): void
    {
        try {
            // Determine column names in source table (may have typo)
            $sourceHasTypo = $this->columnExistsForMigration($sourceTable, 'enitityA');
            $sourceEntityAColumn = $sourceHasTypo ? 'enitityA' : 'entityA';

            // Determine column names in target table (may have typo)
            $targetHasTypo = $this->columnExistsForMigration($targetTable, 'enitityA');
            $targetEntityAColumn = $targetHasTypo ? 'enitityA' : 'entityA';

            // Insert data from source into target, ignoring duplicates
            $this->connection->statement("
                INSERT IGNORE INTO `{$targetTable}` (
                    `{$targetEntityAColumn}`, `entityAType`, `entityB`, `entityBType`,
                    `relationship`, `createdOn`, `createdBy`, `meta`
                )
                SELECT
                    `{$sourceEntityAColumn}`, `entityAType`, `entityB`, `entityBType`,
                    `relationship`, `createdOn`, `createdBy`, `meta`
                FROM `{$sourceTable}`
            ");

            // Drop the source (plural) table
            $this->connection->statement("DROP TABLE `{$sourceTable}`");

        } catch (\Exception $e) {
            Log::error('Migration 30410: Failed to merge tables: '.$e->getMessage());
        }
    }

    /**
     * Create the entity_relationship table with correct schema
     */
    private function createEntityRelationshipTable(): void
    {
        $this->connection->statement('
            CREATE TABLE `zp_entity_relationship` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `entityA` INT NULL,
                `entityAType` VARCHAR(45) NULL,
                `entityB` INT NULL,
                `entityBType` VARCHAR(45) NULL,
                `relationship` VARCHAR(45) NULL,
                `createdOn` DATETIME NULL,
                `createdBy` INT NULL,
                `meta` TEXT NULL,
                PRIMARY KEY (`id`),
                INDEX `entityA` (`entityA` ASC, `entityAType` ASC, `relationship` ASC),
                INDEX `entityB` (`entityB` ASC, `entityBType` ASC, `relationship` ASC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
    }

    /**
     * Fix the entityA column typo if it exists
     */
    private function fixEntityAColumnTypo(string $tableName): void
    {
        try {
            if ($this->columnExistsForMigration($tableName, 'enitityA')) {
                $this->connection->statement(
                    "ALTER TABLE `{$tableName}` CHANGE COLUMN `enitityA` `entityA` INT NULL"
                );
            }
        } catch (\Exception $e) {
            Log::error("Migration 30410: Failed to fix column typo in {$tableName}: ".$e->getMessage());
        }
    }

    /**
     * Ensure the entityA index exists with correct columns
     */
    private function ensureEntityAIndex(string $tableName): void
    {
        try {
            // Drop the old index if it exists (it may reference the wrong column)
            if ($this->indexExistsForMigration($tableName, 'entityA')) {
                $this->connection->statement("ALTER TABLE `{$tableName}` DROP INDEX `entityA`");
            }

            // Create the index with correct columns
            $this->connection->statement(
                "ALTER TABLE `{$tableName}` ADD INDEX `entityA` (`entityA` ASC, `entityAType` ASC, `relationship` ASC)"
            );
        } catch (\Exception $e) {
            Log::error("Migration 30410: Failed to ensure entityA index on {$tableName}: ".$e->getMessage());
        }
    }

    public function update_sql_30411(): bool|array
    {
        $errors = [];

        $sql = [
            // Add color column to zp_canvas for notebook color support
            'ALTER TABLE `zp_canvas` ADD COLUMN `color` VARCHAR(50) DEFAULT "ocean" AFTER `description`;',
        ];

        foreach ($sql as $statement) {
            try {
                $this->connection->statement($statement);
            } catch (\Exception $e) {
                Log::error($statement.' Failed: '.$e->getMessage());
                Log::error($e);
                // Don't fail for duplicate column
                if (! str_contains($e->getMessage(), 'Duplicate column name')) {
                    array_push($errors, $statement.' Failed: '.$e->getMessage());
                }
            }
        }

        return count($errors) ? $errors : true;
    }

    /**
     * Migration 30412: Safety net for zp_entity_relationship table
     *
     * This migration acts as a final safety net for users who may have had issues
     * with migration 30410. It performs the same checks and fixes using the shared
     * helper methods to ensure the table is in the correct state.
     *
     * Since 30410 is now robust and idempotent, this migration will typically
     * find everything already correct and simply return true.
     *
     * @return bool Always returns true to prevent blocking subsequent migrations
     */
    public function update_sql_30412(): bool|array
    {
        $pluralTable = 'zp_entity_relationships';
        $singularTable = 'zp_entity_relationship';

        try {
            $pluralExists = $this->tableExistsForMigration($pluralTable);
            $singularExists = $this->tableExistsForMigration($singularTable);

            // Handle any remaining plural table issues
            if ($pluralExists && $singularExists) {
                $this->mergeEntityRelationshipTables($pluralTable, $singularTable);
            } elseif ($pluralExists && ! $singularExists) {
                $this->connection->statement("RENAME TABLE `{$pluralTable}` TO `{$singularTable}`");
            } elseif (! $pluralExists && ! $singularExists) {
                $this->createEntityRelationshipTable();

                return true;
            }

            // Ensure column and index are correct
            $this->fixEntityAColumnTypo($singularTable);
            $this->ensureEntityAIndex($singularTable);

        } catch (\Exception $e) {
            Log::error('Migration 30412: '.$e->getMessage());
        }

        return true;
    }

    /**
     * Migration 30413: Final fix for zp_entity_relationship table
     *
     * This migration ensures all users get the entity_relationship table fix,
     * including those whose DB version was already recorded as 30412+ due to
     * previous failed or partial migrations.
     *
     * Since all the helper methods are idempotent, this will simply verify
     * the table is correct (doing nothing if already fixed) or apply the fix
     * if still needed.
     *
     * @return bool Always returns true to prevent blocking subsequent migrations
     */
    public function update_sql_30413(): bool|array
    {
        $pluralTable = 'zp_entity_relationships';
        $singularTable = 'zp_entity_relationship';

        try {
            $pluralExists = $this->tableExistsForMigration($pluralTable);
            $singularExists = $this->tableExistsForMigration($singularTable);

            // Handle any remaining plural table issues
            if ($pluralExists && $singularExists) {
                $this->mergeEntityRelationshipTables($pluralTable, $singularTable);
            } elseif ($pluralExists && ! $singularExists) {
                $this->connection->statement("RENAME TABLE `{$pluralTable}` TO `{$singularTable}`");
            } elseif (! $pluralExists && ! $singularExists) {
                $this->createEntityRelationshipTable();

                return true;
            }

            // Ensure column and index are correct
            $this->fixEntityAColumnTypo($singularTable);
            $this->ensureEntityAIndex($singularTable);

        } catch (\Exception $e) {
            Log::error('Migration 30413: '.$e->getMessage());
        }

        return true;
    }
}
