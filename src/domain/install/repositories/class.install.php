<?php


namespace leantime\domain\repositories {

    use Exception;
    use leantime\core\appSettings;
    use PDO;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use PDOException;
    use leantime\core;

    class install
    {
        /**
         * @access public
         * @var string
         */
        public $name;

        /**
         * @access public
         * @var int
         */
        public $id;

        /**
         * database pdo object
         * @access private
         * @var object
         */
        private $database = '';

        /**
         * database username
         * @access private
         * @var string
         */
        private $user = '';

        /**
         * ddatabase password
         * @access private
         * @var string
         */
        private $password = '';

        /**
         * database host
         * @access private
         * @var string
         */
        private $host = '';

        /**
         * database port
         * @access private
         * @var string
         */
        private $port = '3306';

        /**
         * db update scripts listed out by version number with leading zeros A.BB.CC => ABBCC
         * @access private
         * @var array
         */
        private $dbUpdates = array(
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
            20112
        );

        /**
         * config object, passed into constructor
         * @access private
         * @var string
         */
        private $config;

        /**
         * appSettings object, passed into constructor
         * @access private
         * @var string
         */
        private $settings;

        /**
         * __construct - get database connection
         *
         * @access public
         */
        public function __construct()
        {

            //Some scripts might take a long time to execute. Set timeout to 5minutes
            ini_set('max_execution_time', 300);

            $this->config = new core\config();
            $this->settings = new core\appSettings();

            $this->user = $this->config->dbUser;
            $this->password = $this->config->dbPassword;
            $this->host = $this->config->dbHost;
            $this->port = $this->config->dbPort ?? 3306;

            try {

                $driver_options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,sql_mode="NO_ENGINE_SUBSTITUTION"');
                $this->database = new PDO('mysql:host=' . $this->host . ';port=' . $this->port, $this->user, $this->password,
                    $driver_options);
                $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            } catch (PDOException $e) {

                error_log($e);
                echo $e->getMessage();

            }

        }

        /**
         * checkIfInstalled checks if zp user table exists (and assumes that leantime is installed)
         *
         * @access public
         * @return bool
         */
        public function checkIfInstalled()
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
         * setupDB installs database
         *
         * @param array     $values Form values for admin user and company information
         * @access public
         * @return bool | string
         */
        public function setupDB(array $values)
        {


            $sql = $this->sqlPrep();

            try {

                $this->database->query("Use `" . $this->config->dbDatabase . "`;");

                $stmn = $this->database->prepare($sql);
                $stmn->bindValue(':email', $values["email"], PDO::PARAM_STR);
                $stmn->bindValue(':password', $values["password"], PDO::PARAM_STR);
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
                error_log($e);
                return false;

            }

            return "Could not initialize transaction";

        }

        /**
         * updateDB main entry point to update the db based on version number. Executes all missing db update scripts
         *
         * @access public
         * @return bool|array
         */
        public function updateDB()
        {

            $errors = array();

            $this->database->query("Use `" . $this->config->dbDatabase . "`;");

            $versionArray = explode(".", $this->settings->dbVersion);
            if(is_array($versionArray) && count($versionArray) == 3) {

                $major = $versionArray[0];
                $minor = str_pad($versionArray[1], 2, "0", STR_PAD_LEFT);
                $patch = str_pad($versionArray[2], 2, "0", STR_PAD_LEFT);
                $newDBVersion = $major . $minor . $patch;

            }else{
                $errors[0] = "Problem identifying the version number";
                return $errors;

            }

            $setting = new setting();

            $dbVersion = $setting->getSetting("db-version");

            $currentDBVersion = 0;
            if ($dbVersion != false) {
                $versionArray = explode(".", $dbVersion);
                if(is_array($versionArray) && count($versionArray) == 3) {

                    $major = $versionArray[0];
                    $minor = str_pad($versionArray[1], 2, "0", STR_PAD_LEFT);
                    $patch = str_pad($versionArray[2], 2, "0", STR_PAD_LEFT);
                    $currentDBVersion = $major . $minor . $patch;


                }else{
                    $errors[0] = "Problem identifying the version number";
                    return $errors;
                }
            }

            if ($currentDBVersion == $newDBVersion) {
                return true;
            }

            //Find all update functions that need to be executed
            foreach ($this->dbUpdates as $updateVersion) {

                if ($currentDBVersion < $updateVersion) {

                    $functionName = "update_sql_" . $updateVersion;

                    $result = $this->$functionName();
                    $result = true;

                    if ($result !== true) {

                        $errors = array_merge($errors, $result);

                    }else{

                        //Update version number in db
                        try {
                            $stmn = $this->database->prepare("INSERT INTO zp_settings (`key`, `value`) VALUES ('db-version', '" . $this->settings->dbVersion . "') ON DUPLICATE KEY UPDATE `value` = '" . $this->settings->dbVersion . "'");
                            $stmn->execute();

                            $currentDBVersion = $updateVersion;

                        }catch(PDOException $e) {

                            error_log($e);
                            error_log($e->getTraceAsString());
                            return array("There was a problem updating the database");
                        }
                    }

                    if (count($errors) > 0) {
                        return $errors;
                    }

                }

            }

            return true;
        }

        /**
         * sqlPrep - returns all the create table statements
         *
         * @access private
         * @return string
         */
        private function sqlPrep()
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
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                CREATE TABLE `zp_canvas` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `title` varchar(255) DEFAULT NULL,
                  `author` int(10) DEFAULT NULL,
                  `created` datetime DEFAULT NULL,
                  `projectId` INT NULL,
                  `type` VARCHAR(45) NULL,
                  PRIMARY KEY (`id`),
                  KEY `ProjectIdType` (`projectId` ASC, `type` ASC)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                insert  into `zp_canvas`(`id`,`title`,`author`,`created`, `projectId`, `type`) values (1,'Lean Canvas',1,'2015-11-13 13:03:46', 3, 'leancanvas');

                CREATE TABLE `zp_canvas_items` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `description` text,
                  `assumptions` text,
                  `data` text,
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
                  PRIMARY KEY (`id`)
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
				  `menuType` MEDIUMTEXT DEFAULT '".repositories\menu::DEFAULT_MENU."',
                  `psettings` MEDIUMTEXT NULL,
                  PRIMARY KEY (`id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                insert  into `zp_projects`(`id`,`name`,`clientId`,`details`,`state`,`hourBudget`,`dollarBudget`,`active`,`psettings`) values (3,'Leantime Onboarding',1,'<p>This is you first project to get you started</p>',0,'0',0,NULL,NULL);

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
                  PRIMARY KEY (`id`),
                  KEY `ProjectUserId` (`projectId`,`userId`),
                  KEY `StatusSprint` (`status`,`sprint`),
                  KEY `Sorting` (`sortindex`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

                insert  into `zp_tickets`(`id`,`projectId`,`headline`,`description`,`acceptanceCriteria`,`date`,`dateToFinish`,`priority`,`status`,`userId`,`os`,`browser`,`resolution`,`component`,`version`,`url`,`dependingTicketId`,`editFrom`,`editTo`,`editorId`,`planHours`,`hourRemaining`,`type`,`production`,`staging`,`storypoints`,`sprint`,`sortindex`,`kanbanSortIndex`) values
                (9,3,'Getting Started with Leantime','Look around and make yourself familiar with the system. ','','2015-11-30 00:00:00','1969-12-31 00:00:00',NULL,3,1,NULL,NULL,NULL,NULL,'',NULL,NULL,'1969-12-31 00:00:00','1969-12-31 00:00:00',1,0,0,'Story',0,0,0,0,NULL,NULL);

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
                  `paidDate` DATETIME DEFAULT NULL
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
                    `value` TEXT NULL,
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
                  `homepage` VARCHAR(255) NULL AFTER,
                  `authors` VARCHAR(255) NULL AFTER
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
         */
        private function update_sql_20004()
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
                "ALTER TABLE `zp_wiki` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
            );

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }

            }


            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20100()
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

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20101() {


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

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20102()
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_user` add COLUMN `twoFAEnabled` tinyint(1) DEFAULT '0'",
                "ALTER TABLE `zp_user` add COLUMN `twoFASecret` varchar(200) DEFAULT NULL"
            );

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20103()
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_tickets` CHANGE COLUMN `planHours` `planHours` FLOAT NULL DEFAULT NULL",
                "ALTER TABLE `zp_tickets` CHANGE COLUMN `hourRemaining` `hourRemaining` FLOAT NULL DEFAULT NULL"
            );

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20104()
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

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20105()
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

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20106()
        {
            $errors = array();

            $sql = array(
                "ALTER TABLE `zp_user` ADD COLUMN `source` varchar(200) DEFAULT NULL"
            );

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20107()
        {
            $errors = array();

            $sql = array(
                "INSERT INTO zp_settings (`key`, `value`) VALUES ('companysettings.telemetry.active', 'true')"
            );

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {
                    array_push($errors, $statement . " Failed:" . $e->getMessage());
                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20108()
        {
            $errors = array();

            $sql = array(
                "alter table zp_relationuserproject add `projectRole` varchar(20) null",
                "create index zp_relationuserproject_projectId_index on zp_relationuserproject (projectId)",
                "create index zp_relationuserproject_userId_index on zp_relationuserproject (userId)"
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

        private function update_sql_20109() {

            $errors = array();

            $sql = array( "CREATE TABLE IF NOT EXISTS `zp_queue` (
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
			   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
                   );

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {

                    array_push($errors, $statement . " Failed:" . $e->getMessage());

                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        private function update_sql_20110() {

            $errors = array();

            $sql = array( "alter table zp_canvas_items add tags text null",
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
                "alter table zp_comment add status varchar(50) null"
            );

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {

                    array_push($errors, $statement . " Failed:" . $e->getMessage());

                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

		/***
		 * update_sql_20111 - Update database for new canvas
		 *
		 * @access private
		 * @return bool|array    Success of database update or array of errors
		 */
		private function update_sql_20111(): bool|array {

            $errors = array();

			$sql = [
                "ALTER TABLE zp_projects ADD menuType MEDIUMTEXT null",
				"UPDATE zp_projects SET menuType = '".repositories\menu::DEFAULT_MENU."'",
				"ALTER TABLE zp_canvas_items ADD relates VARCHAR(255) null",
				"UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id ".
				"SET zp_canvas_items.status = 'draft' WHERE zp_canvas_items.status = 'danger' AND zp_canvas.type = 'leancanvas'",
				"UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id ".
				"SET zp_canvas_items.status = 'valid' WHERE zp_canvas_items.status = 'sucess' AND zp_canvas.type = 'leancanvas'",
				"UPDATE zp_canvas_items INNER JOIN zp_canvas ON zp_canvas.id = zp_canvas_items.id ".
				"SET zp_canvas_items.status = 'invalid' WHERE zp_canvas_items.status = 'info' AND zp_canvas.type = 'leancanvas'",
				"UPDATE zp_canvas SET zp_canvas.type = 'retroscanvas' WHERE zp_canvas.type = 'retrospective'"
            ];

            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {

                    array_push($errors, $statement . " Failed:" . $e->getMessage());

                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }

        /***
         * update_sql_20111 - Update database for new canvas
         *
         * @access private
         * @return bool|array    Success of database update or array of errors
         */
        private function update_sql_20112(): bool|array {

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
                  `homepage` VARCHAR(255) NULL AFTER,
                  `authors` VARCHAR(255) NULL AFTER
                  PRIMARY KEY (`id`) ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;",
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
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
                ];



            foreach ($sql as $statement) {

                try {

                    $stmn = $this->database->prepare($statement);
                    $stmn->execute();

                } catch (PDOException $e) {

                    array_push($errors, $statement . " Failed:" . $e->getMessage());

                }

            }

            if(count($errors) > 0) {
                return $errors;
            }else{
                return true;
            }

        }


    }
}
