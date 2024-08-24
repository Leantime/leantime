<?php

namespace Leantime\Domain\Install\Repositories {

    use Illuminate\Contracts\Container\BindingResolutionException;
    use Leantime\Core\Configuration\AppSettings as AppSettingCore;
    use Leantime\Core\Configuration\Environment;
    use Leantime\Core\Events\DispatchesEvents as EventhelperCore;
    use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
    use Leantime\Domain\Setting\Repositories\Setting;
    use PDO;
    use PDOException;

    /**
     *
     */
    class Install
    {
        use EventhelperCore;

        /**
         * @access public
         * @var string
         */
        public string $name;

        /**
         * @access public
         * @var int
         */
        public int $id;

        /**
         * database pdo object
         * @access private
         * @var PDO|null
         */
        private ?PDO $database = null;

        /**
         * database username
         * @access private
         * @var string
         */
        private mixed $user = '';

        /**
         * ddatabase password
         * @access private
         * @var string
         */
        private mixed $password = '';

        /**
         * database host
         * @access private
         * @var string
         */
        private mixed $host = '';

        /**
         * database port
         * @access private
         * @var string
         */
        private mixed $port = '3306';

        /**
         * db update scripts listed out by version number with leading zeros A.BB.CC => ABBCC
         * @access private
         * @var array
         */
        private array $dbUpdates = array(
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
        );

        /**
         * config object, passed into constructor
         * @access private
         * @var string|Environment
         */
        private Environment|string $config;

        /**
         * appSettings object, passed into constructor
         * @access private
         * @var string|AppSettingCore
         */
        private string|AppSettingCore $settings;

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct(Environment $config, AppSettingCore $settings)
        {
            //Some scripts might take a long time to execute. Set timeout to 5minutes
            ini_set('max_execution_time', 300);

            $this->config = $config;
            $this->settings = $settings;

            $this->user = $this->config->dbUser;
            $this->password = $this->config->dbPassword;
            $this->host = $this->config->dbHost;
            $this->port = $this->config->dbPort ?? 3306;

            try {
                $driver_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,sql_mode="NO_ENGINE_SUBSTITUTION"');
                $this->database = new PDO(
                    'mysql:host=' . $this->host . ';port=' . $this->port,
                    $this->user,
                    $this->password,
                    $driver_options
                );
                $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            } catch (PDOException $e) {
                report($e);
                echo $e->getMessage();
            }
        }

        /**
         * returns current database object
         *
         * @access public
         * @return PDO|null
         */
        public function getDBObject(): PDO|null
        {
            return $this->database;
        }

        /**
         * checkIfInstalled checks if zp user table exists (and assumes that leantime is installed)
         *
         * @access public
         * @return bool
         */
        public function checkIfInstalled(): bool
        {

            try {
                $this->database->query("Use `" . $this->config->dbDatabase . "`;");

                $stmn = $this->database->prepare("SELECT COUNT(*) FROM zp_user");

                $stmn->execute();
                $values = $stmn->fetchAll();

                $stmn->closeCursor();

                return true;
            } catch (PDOException $e) {
                return false;
            }
        }

        /**
         * @param $dbName
         * @return bool
         */
        public function createDB($dbName): bool
        {

            try {
                $stmn = $this->database->prepare("CREATE SCHEMA :schemaName ;");
                $stmn->bindValue(':dbName', $dbName, PDO::PARAM_STR);

                $stmn->execute();

                $stmn->closeCursor();

                return true;
            } catch (PDOException $e) {
                report($e);
                return false;
            }
        }

        /**
         * setupDB installs database
         *
         * @param array  $values Form values for admin user and company information
         * @param string $db
         * @return bool
         * @access public
         */
        public function setupDB(array $values, $db = ''): bool
        {

            $sql = $this->sqlPrep();

            try {
                if ($db == null) {
                    $this->database->query("Use `" . $this->config->dbDatabase . "`;");
                } else {
                    $this->database->query("Use `" . $db . "`;");
                }

                $stmn = $this->database->prepare($sql);
                $stmn->bindValue(':email', $values["email"], PDO::PARAM_STR);
                $stmn->bindValue(':password', password_hash($values['password'], PASSWORD_DEFAULT), PDO::PARAM_STR);
                $stmn->bindValue(':firstname', $values["firstname"], PDO::PARAM_STR);
                $stmn->bindValue(':lastname', $values["lastname"], PDO::PARAM_STR);
                $stmn->bindValue(':dbVersion', $this->settings->dbVersion, PDO::PARAM_STR);
                $stmn->bindValue(':company', $values["company"], PDO::PARAM_STR);

                $stmn->execute();

                /** @noinspection PhpStatementHasEmptyBodyInspection */
                while ($stmn->nextRowset()) {/* https://bugs.php.net/bug.php?id=61613 */
                }

                return true;
            } catch (PDOException $e) {
                report($e);
                return false;
            }
        }

        /**
         * updateDB main entry point to update the db based on version number. Executes all missing db update scripts
         *
         * @access public
         * @return bool|array
         * @throws BindingResolutionException
         */
        public function updateDB(): array|bool
        {

            $errors = array();

            $this->database->query("Use `" . $this->config->dbDatabase . "`;");

            $versionArray = explode(".", $this->settings->dbVersion);
            if (is_array($versionArray) && count($versionArray) == 3) {
                $major = $versionArray[0];
                $minor = str_pad($versionArray[1], 2, "0", STR_PAD_LEFT);
                $patch = str_pad($versionArray[2], 2, "0", STR_PAD_LEFT);
                $newDBVersion = $major . $minor . $patch;
            } else {
                $errors[0] = "Problem identifying the version number";
                return $errors;
            }

            $setting = app()->make(Setting::class);
            $dbVersion = $setting->getSetting("db-version");
            $currentDBVersion = 0;
            if ($dbVersion) {
                $versionArray = explode(".", $dbVersion);
                if (is_array($versionArray) && count($versionArray) == 3) {
                    $major = $versionArray[0];
                    $minor = str_pad($versionArray[1], 2, "0", STR_PAD_LEFT);
                    $patch = str_pad($versionArray[2], 2, "0", STR_PAD_LEFT);
                    $currentDBVersion = $major . $minor . $patch;
                } else {
                    $errors[0] = "Problem identifying the version number";
                    return $errors;
                }
            }

            if ($currentDBVersion == $newDBVersion) {

                session()->forget("isUpdated");
                session()->forget("dbVersion");

                return true;
            }

            //Find all update functions that need to be executed
            foreach ($this->dbUpdates as $updateVersion) {
                if ($currentDBVersion < $updateVersion) {
                    $functionName = "update_sql_" . $updateVersion;

                    $result = $this->$functionName();

                    if ($result !== true) {
                        $errors = array_merge($errors, $result);
                    } else {
                        //Update version number in db
                        try {
                            $stmn = $this->database->prepare("INSERT INTO zp_settings (`key`, `value`) VALUES ('db-version', '" . $this->settings->dbVersion . "') ON DUPLICATE KEY UPDATE `value` = '" . $this->settings->dbVersion . "'");
                            $stmn->execute();

                            $currentDBVersion = $updateVersion;
                        } catch (PDOException $e) {
                            report($e);
                            report($e->getTraceAsString());
                            return array("There was a problem updating the database");
                        }
                    }

                    if (count($errors) > 0) {
                        return $errors;
                    }
                }
            }

            session()->forget("isUpdated");
            session()->forget("dbVersion");

            return true;
        }

        /**
         * sqlPrep - returns all the create table statements
         *
         * @access private
         * @return string
         */
        private function sqlPrep(): string
        {


            $gettingStartedDescription = '<h2>Essentials</h2>
                             <ul class="tox-checklist" style="list-style-type: none;">
                             <li>Explore your <a href="dashboard/home" target="_blank" rel="noopener">personal dashboard&nbsp;</a></li>
                             <li>Create your first To-do under "My Todos"</li>
                             <li>Drag and Drop your To-Do to the Calendar</li>
                             </ul>
                             <p>&nbsp;</p>
                             <h2>Your first Project</h2>
                             <ul class="tox-checklist" style="list-style-type: none;">
                             <li>Go to your "<a href="projects/showMy" target="_blank" rel="noopener">Project Hub</a>" and open a project</li>
                             <li>Check the Project Checklist and learn what is needed to run a project</li>
                             <li>Head to "<a href="strategy/showBoards" target="_blank" rel="noopener">Blueprints</a>" and create a project value canvas</li>
                             <li>Next create a <a href="goalcanvas/dashboard" target="_blank" rel="noopener">Goal</a> for your project</li>
                             <li>Now create a&nbsp;<a href="tickets/roadmap" target="_blank" rel="noopener"> milestone </a>&nbsp;representing a large part of your project</li>
                             <li>Create <a href="tickets/showKanban" target="_blank" rel="noopener">to-dos</a> and assign them to milestones</li>
                             </ul>
                             <p>&nbsp;</p>
                             <h2>Working with your Team</h2>
                             <ul class="tox-checklist" style="list-style-type: none;">
                             <li>Go to your <a href="dashboard/show" target="_blank" rel="noopener">project dashboard</a> and invite a team member</li>
                             <li>Open a To-Do, create a new comment and mention a team member using the "@" sign</li>
                             </ul>
                             ';

            $sql = "
                CREATE TABLE `zp_calendar` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `userId` int(11) DEFAULT NULL,
                  `dateFrom` datetime DEFAULT NULL,
                  `dateTo` datetime DEFAULT NULL,
                  `description` text,
                  `kind` varchar(255) DEFAULT NULL,
                  `allDay` varchar(10) DEFAULT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_canvas` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) DEFAULT NULL,
                  `author` int(10) DEFAULT NULL,
                  `created` datetime DEFAULT NULL,
                  `projectId` INT NULL,
                  `type` VARCHAR(45) NULL,
                  `description` TEXT,
                  `modified` datetime DEFAULT NULL,
                  PRIMARY KEY (`id`),
                  KEY `ProjectIdType` (`projectId` ASC, `type` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                insert  into `zp_canvas`(`id`,`title`,`author`,`created`, `projectId`, `type`) values (1,'Lean Canvas',1,'2015-11-13 13:03:46', 3, 'leancanvas');

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
                  KEY `CanvasLookUp` (`canvasId` ASC, `box` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_approvals`
                (
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

                insert  into `zp_clients`(`id`,`name`,`street`,`zip`,`city`,`state`,`country`,`phone`,`internet`,`published`,`age`,`email`) values (1,:company,'',0,'','','','','',NULL,NULL,'');

                CREATE TABLE `zp_comment` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `module` varchar(200) DEFAULT NULL,
                  `userId` int(11) DEFAULT NULL,
                  `commentParent` int(11) DEFAULT NULL,
                  `date` datetime DEFAULT NULL,
                  `moduleId` int(11) DEFAULT NULL,
                  `text` text,
                  `status` varchar(50) null,
                  PRIMARY KEY (`id`)
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
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_gcallinks` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `userId` int(255) DEFAULT NULL,
                  `url` text,
                  `name` varchar(255) DEFAULT NULL,
                  `colorClass` varchar(100) DEFAULT NULL,
                  PRIMARY KEY (`id`)
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

                insert  into `zp_projects`(`id`,`name`,`clientId`,`details`,`state`,`hourBudget`,`dollarBudget`,`active`, `menuType`, `psettings`) values (3,'Leantime Onboarding',1,'<p>This is your first project to get you started</p>',0,'0',0,NULL, '" . MenuRepository::DEFAULT_MENU . "',NULL);

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

                insert  into `zp_relationuserproject`(`id`,`userId`,`projectId`,`wage`) values (9,20,3,NULL),(8,18,3,NULL),(7,19,3,NULL),(6,1,3,NULL);

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
                  KEY `Sorting` (`sortindex`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                insert  into `zp_tickets`(`id`,`projectId`,`headline`,`description`,`acceptanceCriteria`,`date`,`dateToFinish`,`priority`,`status`,`userId`,`os`,`browser`,`resolution`,`component`,`version`,`url`,`milestoneid`,`editFrom`,`editTo`,`editorId`,`planHours`,`hourRemaining`,`type`,`production`,`staging`,`storypoints`,`sprint`,`sortindex`,`kanbanSortIndex`) values
                (9,3,'Getting Started with Leantime', '" . $gettingStartedDescription . "','','" . date("Y-m-d") . "','" . date("Y-m-d") . "',2,3,1,NULL,NULL,NULL,NULL,'',NULL,NULL,'1969-12-31 00:00:00','1969-12-31 00:00:00',1,0,0,'Story',0,0,0,0,NULL,NULL);

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
                  UNIQUE KEY `Unique` (`userId`,`ticketId`,`workDate`,`kind`)
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
                  UNIQUE KEY `username` (`username`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                insert  into `zp_user`(`id`,`username`,`password`,`firstname`,`lastname`,`phone`,`profileId`,`lastlogin`,`lastpwd_change`,`status`,`expires`,`role`,`session`,`sessiontime`,`wage`,`hours`,`description`,`clientId`, `notifications`, `createdOn`)
                values (1,:email,:password,:firstname,:lastname,'','',NULL,0,'a',NULL,'50','','',0,0,NULL,0,1, NOW());

                CREATE TABLE `zp_sprints` (
                    `id` INT NOT NULL AUTO_INCREMENT,
                    `projectId` INT NULL,
                    `name` VARCHAR(45) NULL,
                    `startDate` DATETIME NULL,
                    `endDate` DATETIME NULL,
                    `modified` datetime DEFAULT NULL,
                    PRIMARY KEY (`id`)
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
                    INDEX `projectId` (`projectId` ASC, `sprintId` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_settings` (
                    `key` VARCHAR(175) NOT NULL,
                    `value` MEDIUMTEXT NULL,
                    PRIMARY KEY (`key`)
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
            ";

            return $sql;
        }

        /**
         * update_sql_20004 - database update sql for V2.0.4
         * - Updates all tables and db to utf8mb4
         * - converts 255 index to be smaller
         *
         * @access public
         * @return bool|array
         * @noinspection SqlResolve - A lot of tables don't exist anymore, so this will not resolve. Keeping the update script for backwards compatibility
         */
        private function update_sql_20004(): bool|array
        {

            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_wiki_articles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_submodulerights` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_canvas` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_wiki_categories` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_tickethistory` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_gcallinks` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_message` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_note` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_timesheets` MODIFY kind VARCHAR(175);",
                "ALTER TABLE `zp_timesheets` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_roles` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_projects` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_modulerights` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_wiki_comments` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_punch_clock` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_clients` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_account` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_sprints` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_lead` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_user` MODIFY username VARCHAR(175);",
                "ALTER TABLE `zp_user` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_settings` MODIFY `key` VARCHAR(175);",
                "ALTER TABLE `zp_settings` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_comment` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_stats` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_tickets` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_canvas_items` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_dashboard_widgets` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_file` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_action_tabs` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_relationuserproject` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_calendar` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_read` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_wiki` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }


            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }


        /**
         * @return bool|array
         */
        private function update_sql_20100(): bool|array
        {

            $errors = array();

            $sql = array(
                "UPDATE `zp_user` SET role = 50 WHERE role = 2;",
                "UPDATE `zp_user` SET role = 10 WHERE role = 3;",
                "UPDATE `zp_user` SET role = 20 WHERE role = 4;",
                "UPDATE `zp_user` SET role = 40 WHERE role = 5;",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        private function update_sql_20101(): bool|array
        {


            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_comment` CHANGE COLUMN `module` `module` VARCHAR(200) NULL DEFAULT NULL ;",
                "ALTER TABLE `zp_stats`
                    ADD COLUMN `sum_teammembers` INT(11) NULL DEFAULT NULL AFTER `daily_avg_hours_remaining_todo`,
                    CHANGE COLUMN `sum_planned_hours` `sum_planned_hours` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `sum_logged_hours` `sum_logged_hours` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `sum_estremaining_hours` `sum_estremaining_hours` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_booked_todo` `daily_avg_hours_booked_todo` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_booked_point` `daily_avg_hours_booked_point` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_planned_todo` `daily_avg_hours_planned_todo` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_planned_point` `daily_avg_hours_planned_point` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_remaining_point` `daily_avg_hours_remaining_point` FLOAT NULL DEFAULT NULL ,
                    CHANGE COLUMN `daily_avg_hours_remaining_todo` `daily_avg_hours_remaining_todo` FLOAT NULL DEFAULT NULL ;",
                "CREATE TABLE `zp_audit` (
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        private function update_sql_20102(): bool|array
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_user` add COLUMN `twoFAEnabled` tinyint(1) DEFAULT '0'",
                "ALTER TABLE `zp_user` add COLUMN `twoFASecret` varchar(200) DEFAULT NULL",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        private function update_sql_20103(): bool|array
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_tickets` CHANGE COLUMN `planHours` `planHours` FLOAT NULL DEFAULT NULL",
                "ALTER TABLE `zp_tickets` CHANGE COLUMN `hourRemaining` `hourRemaining` FLOAT NULL DEFAULT NULL",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        /**
         * @return bool|array
         */
        private function update_sql_20104(): bool|array
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_user` ADD COLUMN `pwResetCount` INT(5) NULL AFTER `pwResetExpiration`",
                "ALTER TABLE `zp_user` ADD COLUMN `forcePwReset` TINYINT NULL AFTER `pwResetCount`",
                "ALTER TABLE `zp_user` ADD COLUMN `createdOn` DATETIME NULL AFTER `twoFASecret`",
                "ALTER TABLE `zp_user` CHANGE COLUMN `lastpwd_change` `lastpwd_change` DATETIME NULL DEFAULT NULL AFTER `forcePwReset`",
                "ALTER TABLE `zp_user` CHANGE COLUMN `expires` `expires` DATETIME NULL DEFAULT NULL",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        /**
         * @return bool|array
         */
        private function update_sql_20105(): bool|array
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_projects` ADD COLUMN `psettings` MEDIUMTEXT NULL AFTER `active`",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        /**
         * @return bool|array
         */
        private function update_sql_20106(): bool|array
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_user` ADD COLUMN `source` varchar(200) DEFAULT NULL",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        /**
         * @return bool|array
         */
        private function update_sql_20107(): bool|array
        {
            $errors = array();

            $sql = array(
                "INSERT INTO zp_settings (`key`, `value`) VALUES ('companysettings.telemetry.active', 'true')",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        /**
         * @return bool|array
         */
        private function update_sql_20108(): bool|array
        {
            $errors = array();

            $sql = array(
                "alter table zp_relationuserproject add `projectRole` varchar(20) null",
                "create index zp_relationuserproject_projectId_index on zp_relationuserproject (projectId)",
                "create index zp_relationuserproject_userId_index on zp_relationuserproject (userId)",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        /**
         * @return bool|array
         */
        private function update_sql_20109(): bool|array
        {

            $errors = array();

            $sql = array(
                "CREATE TABLE IF NOT EXISTS `zp_queue` (
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
			   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        /**
         * @return bool|array
         */
        private function update_sql_20110(): bool|array
        {

            $errors = array();

            $sql = array(
                "alter table zp_canvas_items add tags text null",
                "alter table zp_canvas_items add title varchar(255) null",
                "alter table zp_canvas_items add parent int null",
                "alter table zp_canvas_items add featured int null",
                "create table zp_approvals
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
                )",
                "alter table zp_comment add status varchar(50) null",
            );

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
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

        /**
         * @return bool|array
         */
        private function update_sql_20111(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE zp_projects ADD menuType MEDIUMTEXT null",
                "UPDATE zp_projects SET menuType = '" . MenuRepository::DEFAULT_MENU . "'",
                "ALTER TABLE zp_canvas_items ADD relates VARCHAR(255) null",
                "UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id " .
                    "SET zp_canvas_items.status = 'draft' WHERE zp_canvas_items.status = 'danger' AND zp_canvas.type = 'leancanvas'",
                "UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id " .
                    "SET zp_canvas_items.status = 'valid' WHERE zp_canvas_items.status = 'sucess' AND zp_canvas.type = 'leancanvas'",
                "UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id " .
                    "SET zp_canvas_items.status = 'invalid' WHERE zp_canvas_items.status = 'info' AND zp_canvas.type = 'leancanvas'",
                "UPDATE zp_canvas SET zp_canvas.type = 'retroscanvas' WHERE zp_canvas.type = 'retrospective'",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
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

        /**
         * @return bool|array
         */
        private function update_sql_20112(): bool|array
        {

            $errors = array();

            $sql = [
                "CREATE TABLE `zp_plugins` (
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
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
                "ALTER TABLE `zp_timesheets` ADD COLUMN `paid` SMALLINT NULL AFTER `rate`, ADD COLUMN `paidDate` DATETIME NULL AFTER `paid`;",
                "DROP TABLE IF EXISTS zp_account, zp_action_tabs, zp_dashboard_widgets, zp_lead, zp_message, zp_modulerights, zp_roles, zp_submodulerights, zp_wiki, zp_wiki_articles, zp_wiki_categories, zp_wiki_comments;",
                "CREATE TABLE `zp_notifications` (
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
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
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

        /**
         * @return bool|array
         */
        private function update_sql_20113(): bool|array
        {

            $errors = array();

            $sql = [
                "INSERT INTO zp_settings (`key`, `value`) VALUES ('companysettings.completedOnboarding', 'true') ON DUPLICATE KEY UPDATE `value` = 'true'",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        public function update_sql_20114(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_projects`
                ADD COLUMN `type` VARCHAR(45) NULL,
                ADD COLUMN `start` DATETIME NULL,
                ADD COLUMN `end` DATETIME NULL,
                ADD COLUMN `created` DATETIME NULL,
                ADD COLUMN `modified` DATETIME NULL",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        public function update_sql_20115(): bool|array
        {

            $errors = array();

            $sql = [
                " CREATE TABLE `zp_entity_relationships` (
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
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "UPDATE `zp_tickets` SET milestoneid = dependingTicketId , dependingTicketId = '' WHERE type <> 'subtask' AND dependingTicketId > 0",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        public function update_sql_20116(): bool|array
        {

            $errors = array();

            $sql = [
                " CREATE TABLE `zp_reactions` (
                      `id` INT NOT NULL AUTO_INCREMENT,
                      `userId` INT NULL,
                      `moduleId` INT NULL,
                      `module` VARCHAR(45) NULL,
                      `reaction` VARCHAR(45) NULL,
                      `date` DATETIME NULL,
                      PRIMARY KEY (`id`),
                      INDEX `entity` (`moduleId` ASC, `module` ASC, `reaction` ASC),
                      INDEX `user` (`userId` ASC, `moduleId` ASC, `module` ASC, `reaction` ASC)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "ALTER TABLE `zp_projects`
                ADD COLUMN `avatar` MEDIUMTEXT NULL AFTER `modified`,
                ADD COLUMN `cover` MEDIUMTEXT NULL AFTER `avatar`;",

            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        public function update_sql_20117(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_projects`
                ADD COLUMN `parent` INT(11) NULL;",

                "ALTER TABLE `zp_projects`
                ADD COLUMN `sortIndex` INT(11) NULL;",

                "ALTER TABLE `zp_user`
                ADD COLUMN `jobTitle` VARCHAR(200) NULL ,
                ADD COLUMN `jobLevel` VARCHAR(50) NULL ,
                ADD COLUMN `department` VARCHAR(200) NULL ;",

            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }


        /**
         * @return bool|array
         */
        public function update_sql_20118(): bool|array
        {

            $errors = array();

            $sql = [

                "UPDATE `zp_projects` SET parent = null;",

                "UPDATE `zp_projects` SET start = null, end = null;",

                "ALTER TABLE `zp_projects`
                CHANGE COLUMN `parent` `parent` INT(11) NULL DEFAULT NULL,
                CHANGE COLUMN `type` `type` VARCHAR(45) NULL DEFAULT NULL ;",

            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        public function update_sql_20120(): bool|array
        {

            $errors = array();

            $sql = [

                "ALTER TABLE `zp_canvas_items`
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
                ADD COLUMN `assignedTo` INT NULL DEFAULT NULL AFTER `action`;",
                "UPDATE zp_canvas_items SET
                    currentValue = CAST(IF(`data` = '', 0, `data`) AS DECIMAL(10,2)),
                    endValue = CAST(IF(`conclusion` = '', 0, `conclusion`) AS DECIMAL(10,2)),
                    title = description,
                    description = assumptions
                    WHERE box = 'goal';",
                "ALTER TABLE `zp_canvas_items`
                ADD INDEX `CanvasLookUp` (`canvasId` ASC, `box` ASC);",

            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }


        /**
         * @return bool|array
         */
        public function update_sql_20121(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_canvas`
                    ADD COLUMN `description` TEXT NULL DEFAULT NULL AFTER `type`;",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        public function update_sql_20122(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_settings`
                    CHANGE COLUMN `value` `value` MEDIUMTEXT NULL DEFAULT NULL ;",
                "ALTER TABLE `zp_canvas_items`
                    CHANGE COLUMN `description` `description` MEDIUMTEXT NULL DEFAULT NULL ,
                    CHANGE COLUMN `data` `data` MEDIUMTEXT NULL DEFAULT NULL;",
                "ALTER TABLE `zp_user`
                    ADD COLUMN `modified` DATETIME NULL DEFAULT NULL;",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }
            }

            if (count($errors) > 0) {
                return $errors;
            } else {
                return true;
            }
        }

        /**
         * @return bool|array
         */
        public function update_sql_20401(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_plugins`
                ADD COLUMN `license` TEXT NULL DEFAULT NULL,
                ADD COLUMN `format` VARCHAR(45) NULL DEFAULT NULL",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
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
                "ALTER TABLE `zp_plugins`
                ADD COLUMN `format` VARCHAR(45) NULL DEFAULT NULL",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, "$statement Failed: {$e->getMessage()}");
                }
            }

            return count($errors) ? $errors : true;
        }

        /**
         * Install script did not include medium text updates. Run again
         * @return bool|array
         */
        public function update_sql_20405(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_settings`
                    CHANGE COLUMN `value` `value` MEDIUMTEXT NULL DEFAULT NULL ;",
                "ALTER TABLE `zp_canvas_items`
                    CHANGE COLUMN `description` `description` MEDIUMTEXT NULL DEFAULT NULL ,
                    CHANGE COLUMN `data` `data` MEDIUMTEXT NULL DEFAULT NULL;",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
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
         * @return bool|array
         */
        public function update_sql_20406(): bool|array
        {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_canvas_items`
                    CHANGE COLUMN `data1` `data1` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data2` `data2` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data3` `data3` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data4` `data4` MEDIUMTEXT NULL DEFAULT NULL,
                    CHANGE COLUMN `data5` `data5` MEDIUMTEXT NULL DEFAULT NULL;",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
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

            $errors = array();

            $sql = [
                "CREATE TABLE IF NOT EXISTS `zp_integration` (
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
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
                "ALTER TABLE `zp_integration`
                    ADD COLUMN `lastSync` DATETIME NULL DEFAULT NULL",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
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

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_plugins` ADD COLUMN `license` TEXT NULL DEFAULT NULL",
                "ALTER TABLE `zp_plugins` ADD COLUMN `format` VARCHAR(45) NULL DEFAULT NULL",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    //Just swallow your pride
                    //One day we'll get ALTER IF EXISTS
                }
            }

            return true;
        }

        public function update_sql_30003(): bool|array {

            $errors = array();

            $sql = [
                "ALTER TABLE `zp_canvas` ADD COLUMN `modified` datetime NULL DEFAULT NULL",
                "ALTER TABLE `zp_clients` ADD COLUMN `modified` datetime NULL DEFAULT NULL",
                "ALTER TABLE `zp_sprints` ADD COLUMN `modified` datetime NULL DEFAULT NULL",
                "ALTER TABLE `zp_projects` ADD COLUMN `modified` datetime NULL DEFAULT NULL",
                "ALTER TABLE `zp_timesheets` ADD COLUMN `modified` datetime NULL DEFAULT NULL",
                "ALTER TABLE `zp_tickets` ADD COLUMN `modified` datetime NULL DEFAULT NULL",
            ];

            foreach ($sql as $statement) {
                try {
                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();
                } catch (PDOException $e) {
                    //Just swallow your pride
                    //One day we'll get ALTER IF EXISTS
                }
            }


            return true;
        }
    }
}
