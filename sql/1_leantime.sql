# ************************************************************
# Sequel Pro SQL dump
# Version 4096
#
# http://www.sequelpro.com/
# http://code.google.com/p/sequel-pro/
#
# Database: leantimedemo
# Generation Time: 2015-03-01 16:52:55 +0000
# ************************************************************


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


# Dump of table zp_account
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_account`;

CREATE TABLE `zp_account` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `projectId` int(11) DEFAULT NULL,
  `name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `host` varchar(255) DEFAULT NULL,
  `kind` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_account` WRITE;
/*!40000 ALTER TABLE `zp_account` DISABLE KEYS */;

INSERT INTO `zp_account` (`id`, `projectId`, `name`, `username`, `password`, `host`, `kind`)
VALUES
	(1,91,'h3MkVvoyFtlfWvk7LFHFQA==','ZMKwhVOSaRhae8heMAJJZQ==','hgxuB0xnr3M8nDhIIcs3zg==','hN4l3JWY2vJw8OOM+i4/47qS0vRAFCC23lH4GGLGC2c=','n4W/wyEMX1ZbtGOVFIZHCA=='),
	(2,94,'g3ehdICUy53d4xLMqOHaSZm7uqOxIU90Zy806e2p6XY=','rJ+oieTgruvx2tb5JNmidg==','UFaByaJtFjem0aTLuIOsVQ==','T9j0h652/cRqd5LOF3WMt9cBs1qbDYzs2Ew/rLedptA=','nQhaTwM38GrxhzGgQrPoDQ=='),
	(3,1,'HH/Y1hNLthvNmCbV16FaTA==','UFaByaJtFjem0aTLuIOsVQ==','UFaByaJtFjem0aTLuIOsVQ==','v9imDLwNV8tGS9vX30067LfztBPl1/oHRdzFc9zWHBI=','Q6d1tdPzoTxsyLOTZM2BwA=='),
	(4,1,'hwQNmN9XKqv5y5wNUsg/Ow==','UFaByaJtFjem0aTLuIOsVQ==','UFaByaJtFjem0aTLuIOsVQ==','zxyRFEtL6BxV3IFI/vwEQrdQOThG9qUXgiN0BvgYaWU=','Q6d1tdPzoTxsyLOTZM2BwA==');

/*!40000 ALTER TABLE `zp_account` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_accounts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_accounts`;

CREATE TABLE `zp_accounts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `profileImg` varchar(100) NOT NULL,
  `lastlogin` datetime DEFAULT NULL,
  `lastpwd_change` int(11) DEFAULT NULL,
  `status` varchar(1) NOT NULL DEFAULT 'A',
  `expires` int(11) DEFAULT NULL,
  `role` varchar(200) NOT NULL,
  `session` varchar(100) DEFAULT NULL,
  `sessiontime` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table zp_action_tabs
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_action_tabs`;

CREATE TABLE `zp_action_tabs` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `action` text,
  `tab` varchar(255) DEFAULT NULL,
  `tabRights` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;



# Dump of table zp_calendar
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_calendar`;

CREATE TABLE `zp_calendar` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` int(255) DEFAULT NULL,
  `dateFrom` datetime DEFAULT NULL,
  `dateTo` datetime DEFAULT NULL,
  `description` text,
  `kind` varchar(255) DEFAULT NULL,
  `allDay` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_clientfiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_clientfiles`;

CREATE TABLE `zp_clientfiles` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `clientId` int(11) DEFAULT NULL,
  `encName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `realName` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_bin;



# Dump of table zp_clients
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_clients`;

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
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `zp_clients` WRITE;
/*!40000 ALTER TABLE `zp_clients` DISABLE KEYS */;

INSERT INTO `zp_clients` (`id`, `name`, `street`, `zip`, `city`, `state`, `country`, `phone`, `internet`, `published`, `age`, `email`)
VALUES
	(1,'Company XYZ','',0,'','','','','',NULL,NULL,''),
	(2,'','',0,'','','','','',0,NULL,'');

/*!40000 ALTER TABLE `zp_clients` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_comment
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_comment`;

CREATE TABLE `zp_comment` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module` enum('project','ticket','client','user','lead') DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  `commentParent` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `moduleId` int(11) DEFAULT NULL,
  `text` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_comment` WRITE;
/*!40000 ALTER TABLE `zp_comment` DISABLE KEYS */;

INSERT INTO `zp_comment` (`id`, `module`, `userId`, `commentParent`, `date`, `moduleId`, `text`)
VALUES
	(1,'ticket',1,0,'2015-02-27 11:16:49',1,'Here is a comment'),
	(2,'project',18,0,'2015-02-27 11:44:37',1,'Discussion about a project here'),
	(3,'project',19,2,'2015-02-27 12:27:39',1,'This is a very good point'),
	(4,'ticket',1,0,'2015-03-01 08:45:04',1,'test');

/*!40000 ALTER TABLE `zp_comment` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_dashboard_widgets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_dashboard_widgets`;

CREATE TABLE `zp_dashboard_widgets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `value` text CHARACTER SET latin1,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_general_ci ROW_FORMAT=COMPACT;



# Dump of table zp_file
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_file`;

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
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table zp_gCalLinks
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_gCalLinks`;

CREATE TABLE `zp_gCalLinks` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` int(255) DEFAULT NULL,
  `url` text,
  `name` varchar(255) DEFAULT NULL,
  `colorClass` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_lead
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_lead`;

CREATE TABLE `zp_lead` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `status` enum('lead','opportunity','client') DEFAULT NULL,
  `refSource` varchar(100) DEFAULT NULL,
  `refValue` varchar(255) DEFAULT NULL,
  `potentialMoney` int(11) DEFAULT NULL,
  `actualMoney` int(11) DEFAULT NULL,
  `clientId` int(11) DEFAULT NULL,
  `proposal` text,
  `creatorId` int(11) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_lead` WRITE;
/*!40000 ALTER TABLE `zp_lead` DISABLE KEYS */;

INSERT INTO `zp_lead` (`id`, `name`, `status`, `refSource`, `refValue`, `potentialMoney`, `actualMoney`, `clientId`, `proposal`, `creatorId`, `date`)
VALUES
	(1,'Another Company','lead','4','This is a great lead',500000,0,2,NULL,1,'2015-02-27 11:23:32');

/*!40000 ALTER TABLE `zp_lead` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_menu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_menu`;

CREATE TABLE `zp_menu` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `module` varchar(255) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `parent` int(255) DEFAULT '0',
  `icon` varchar(255) DEFAULT NULL,
  `link` text,
  `inTopNav` int(2) DEFAULT NULL,
  `orderNum` int(10) DEFAULT NULL,
  `application` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_menu` WRITE;
/*!40000 ALTER TABLE `zp_menu` DISABLE KEYS */;

INSERT INTO `zp_menu` (`id`, `name`, `module`, `action`, `parent`, `icon`, `link`, `inTopNav`, `orderNum`, `application`)
VALUES
	(2,'Tickets','tickets','showAll',0,'iconfa-pushpin','index.php?act=tickets.showAll',1,NULL,'general'),
	(3,'Projects','projects','showAll',0,'iconfa-bar-chart','index.php?act=projects.showAll',1,NULL,'general'),
	(4,'Clients','clients','showAll',0,'iconfa-group','index.php?act=clients.showAll',1,NULL,'general'),
	(5,'Leads','leads','showAll',0,'iconfa-signal','index.php?act=leads.showAll',1,7,'general'),
	(7,'Files','files','showAll',0,' iconfa-picture',NULL,NULL,NULL,NULL),
	(9,'Timesheets','timesheets','showAll',0,'iconfa-table',NULL,NULL,NULL,NULL),
	(10,'Admin','setting','editSettings',0,'iconfa-cogs','index.php?act=setting.editSettings',1,NULL,'general'),
	(36,'All Tickets','tickets','showAll',2,'',NULL,NULL,NULL,NULL),
	(37,'Add Ticket','tickets','newTicket',2,'',NULL,NULL,NULL,NULL),
	(38,'All Projects','projects','showAll',3,'',NULL,NULL,NULL,NULL),
	(39,'Add Project','projects','newProject',3,'',NULL,NULL,NULL,NULL),
	(40,'Show All Menus','setting','showAllMenu',10,'',NULL,NULL,NULL,NULL),
	(41,'Show All Roles','setting','showAllRoles',10,'',NULL,NULL,NULL,NULL),
	(42,'Module Permissions','setting','setModuleRights',10,'',NULL,NULL,NULL,NULL),
	(43,'Submodule Permissions','setting','showAllSubmodules',10,'',NULL,NULL,NULL,NULL),
	(44,'All Leads','leads','showAll',5,'',NULL,NULL,NULL,NULL),
	(45,'Add Lead','leads','addLead',5,'',NULL,NULL,NULL,NULL),
	(46,'Lead Statistics','leads','statistics',5,'',NULL,NULL,NULL,NULL),
	(48,'Add Tile','dashboard','addWidget',10,'',NULL,NULL,NULL,NULL),
	(50,'Add User','users','newUser',10,'',NULL,NULL,NULL,NULL),
	(51,'All Users','users','showAll',10,'',NULL,NULL,NULL,NULL),
	(53,'All Clients','clients','showAll',4,'',NULL,NULL,NULL,NULL),
	(54,'Add Client','clients','newClient',4,'',NULL,NULL,NULL,NULL),
	(55,'Wiki','wiki','showAll',0,' icon-question-sign',NULL,NULL,5,NULL);

/*!40000 ALTER TABLE `zp_menu` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_message
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_message`;

CREATE TABLE `zp_message` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `to_id` varchar(50) DEFAULT NULL,
  `from_id` int(11) DEFAULT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `content` text,
  `date_sent` datetime DEFAULT NULL,
  `last_message` int(1) DEFAULT '0',
  `read` int(1) DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table zp_moduleRights
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_moduleRights`;

CREATE TABLE `zp_moduleRights` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `module` text,
  `roleIds` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_moduleRights` WRITE;
/*!40000 ALTER TABLE `zp_moduleRights` DISABLE KEYS */;

INSERT INTO `zp_moduleRights` (`id`, `module`, `roleIds`)
VALUES
	(49109,'accounts/class.deleteAccounts.php','2,5'),
	(49110,'accounts/class.editAccounts.php','2,5'),
	(49111,'accounts/class.showAccounts.php','2,5'),
	(49112,'calendar/class.addEvent.php','2,4,5,3'),
	(49113,'calendar/class.delEvent.php','2,4,5,3'),
	(49114,'calendar/class.delGCal.php','2,4,5,3'),
	(49115,'calendar/class.editEvent.php','2,4,5,3'),
	(49116,'calendar/class.editGCal.php','2,4,5,3'),
	(49117,'calendar/class.importGCal.php','2,4,5,3'),
	(49118,'calendar/class.showAllGCals.php','2,4,5,3'),
	(49119,'calendar/class.showMyCalendar.php','2,4,5,3'),
	(49120,'clients/class.delClient.php','2'),
	(49121,'clients/class.editClient.php','2'),
	(49122,'clients/class.newClient.php','2'),
	(49123,'clients/class.showAll.php','2'),
	(49124,'clients/class.showClient.php','2'),
	(49125,'comments/class.showAll.php','2,4,5,3'),
	(49126,'dashboard/class.addWidget.php','2,4,5'),
	(49127,'dashboard/class.show.php','2,4,5,3'),
	(49128,'dashboard/class.widgets.php','2,4,5,3'),
	(49129,'files/class.showAll.php','2,4,5,3'),
	(49130,'general/class.footer.php','2'),
	(49131,'general/class.header.php','2'),
	(49132,'general/class.main.php','2'),
	(49133,'general/class.menu.php','2'),
	(49134,'general/class.mobileHeader.php','2'),
	(49135,'general/class.mobileLogin.php','2'),
	(49136,'general/class.mobileMenu.php','2'),
	(49137,'general/class.publicMenu.php','2'),
	(49138,'general/class.showMenu.php','2'),
	(49139,'leads/class.addLead.php','2,5'),
	(49140,'leads/class.addLeadContact.php','2,5'),
	(49141,'leads/class.addReferralSource.php','2,5'),
	(49142,'leads/class.convertToUser.php','2,5'),
	(49143,'leads/class.deleteLead.php','2,5'),
	(49144,'leads/class.editLead.php','2,5'),
	(49145,'leads/class.showAll.php','2,5'),
	(49146,'leads/class.showLead.php','2,5'),
	(49147,'leads/class.statistics.php','2,5'),
	(49148,'messages/class.compose.php','2,4,5,3'),
	(49149,'messages/class.showAll.php','2,4,5,3'),
	(49150,'projects/class.delProject.php','2,4,5,3'),
	(49151,'projects/class.editAccount.php','2,4,5,3'),
	(49152,'projects/class.editProject.php','2,4,5'),
	(49153,'projects/class.newProject.php','2,4,5'),
	(49154,'projects/class.showAll.php','2,4,5,3'),
	(49155,'projects/class.showProject.php','2,4,5,3'),
	(49156,'setting/class.addMenu.php','2'),
	(49157,'setting/class.delMenu.php','2'),
	(49158,'setting/class.delRole.php','2'),
	(49159,'setting/class.delSystemOrg.php','2'),
	(49160,'setting/class.editMenu.php','2'),
	(49161,'setting/class.editRole.php','2'),
	(49162,'setting/class.editSettings.php','2'),
	(49163,'setting/class.editSystemOrg.php','2'),
	(49164,'setting/class.editTabRights.php','2'),
	(49165,'setting/class.menuUser.php','2'),
	(49166,'setting/class.newRole.php','2'),
	(49167,'setting/class.newSystemOrg.php','2'),
	(49168,'setting/class.setModuleRights.php','2'),
	(49169,'setting/class.showAllMenu.php','2'),
	(49170,'setting/class.showAllRoles.php','2'),
	(49171,'setting/class.showAllSubmodules.php','2'),
	(49172,'setting/class.showAllSystemOrg.php','2'),
	(49173,'setting/class.userMenu.php','2'),
	(49174,'tickets/class.delTicket.php','2,5'),
	(49175,'tickets/class.editTicket.php','2,4,5,3'),
	(49176,'tickets/class.newTicket.php','2,4,5,3'),
	(49177,'tickets/class.showAll.php','2,4,5,3'),
	(49178,'tickets/class.showMy.php','2,4,5,3'),
	(49179,'tickets/class.showTicket.php','2,4,5,3'),
	(49180,'timesheets/class.addTime.php','2,4,5'),
	(49181,'timesheets/class.delTime.php','2,4,5'),
	(49182,'timesheets/class.editTime.php','2,4,5'),
	(49183,'timesheets/class.showAll.php','2,5'),
	(49184,'timesheets/class.showMy.php','2,4,5'),
	(49185,'users/class.delUser.php','2'),
	(49186,'users/class.editOwn.php','2,4,3'),
	(49187,'users/class.editUser.php','2'),
	(49188,'users/class.newUser.php','2'),
	(49189,'users/class.showAll.php','2'),
	(49190,'users/class.showUser.php','2,3'),
	(49191,'wiki/class.delArticle.php','2,4,5,3'),
	(49192,'wiki/class.editArticle.php','2,4,5,3'),
	(49193,'wiki/class.newArticle.php','2,4,5,3'),
	(49194,'wiki/class.newCategory.php','2,4,5,3'),
	(49195,'wiki/class.showAll.php','2,4,5,3'),
	(49196,'wiki/class.showArticle.php','2,4,5,3');

/*!40000 ALTER TABLE `zp_moduleRights` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_note
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_note`;

CREATE TABLE `zp_note` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_note` WRITE;
/*!40000 ALTER TABLE `zp_note` DISABLE KEYS */;

INSERT INTO `zp_note` (`id`, `userId`, `title`, `description`)
VALUES
	(1,19,'DONT FORGET','Item that must not be forgotten...');

/*!40000 ALTER TABLE `zp_note` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_persons
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_persons`;

CREATE TABLE `zp_persons` (
  `wpd_person_id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'auto id vorher contact_id',
  `wpd_person_account_id` int(11) DEFAULT NULL COMMENT 'account id zu table account',
  `wpd_person_tid` varchar(1) DEFAULT 'n' COMMENT 'vorher: contact_tid',
  `wpd_person_private` tinyint(4) NOT NULL DEFAULT '0',
  `wpd_person_name_family` varchar(64) DEFAULT NULL COMMENT 'Familienname',
  `wpd_person_name_given` varchar(64) DEFAULT NULL COMMENT 'Vorname',
  `wpd_person_name_middle` varchar(64) DEFAULT NULL COMMENT 'weitere Vornamen',
  `wpd_person_title2` varchar(64) DEFAULT NULL COMMENT 'Weitere Titel',
  `wpd_person_title3` varchar(64) DEFAULT NULL,
  `wpd_person_name_prefix` int(11) DEFAULT NULL COMMENT 'Anrede ID FK tbl jhd_person_prefixes',
  `wpd_person_title` int(11) DEFAULT NULL COMMENT 'Akademischer Titel. FK for tbl jhd_person_titles',
  `wpd_person_name_addon_id` int(11) NOT NULL DEFAULT '0' COMMENT 'Name Zusatz wie von oder zu bei Adelstitel etc  FK for tbl jhd_person_name_addon',
  `wpd_person_birthday` date DEFAULT NULL COMMENT 'Geburtstag',
  `wpd_person_jpegphoto` longblob COMMENT 'Foto der Person',
  `wpd_person_note` mediumblob,
  `wpd_person_owner` int(11) NOT NULL DEFAULT '0',
  `wpd_person_creator` int(11) DEFAULT NULL,
  `wpd_person_created` datetime DEFAULT NULL,
  `wpd_person_modifier` int(11) DEFAULT NULL,
  `wpd_person_modified` datetime DEFAULT NULL,
  PRIMARY KEY (`wpd_person_id`),
  KEY `jhd_person_account_id` (`wpd_person_account_id`),
  KEY `jhd_person_tid` (`wpd_person_tid`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COMMENT='Einzigartige Personendaten';



# Dump of table zp_project_accounts
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_project_accounts`;

CREATE TABLE `zp_project_accounts` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL,
  `kind` varchar(50) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `password` varchar(100) DEFAULT NULL,
  `host` varchar(100) DEFAULT NULL,
  `projectId` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_project_comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_project_comments`;

CREATE TABLE `zp_project_comments` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `text` text,
  `datetime` datetime DEFAULT NULL,
  `userId` int(100) DEFAULT NULL,
  `projectId` int(100) DEFAULT NULL,
  `commentParent` int(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_project_files
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_project_files`;

CREATE TABLE `zp_project_files` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `encName` varchar(50) DEFAULT NULL,
  `realName` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `ticketId` int(255) DEFAULT NULL,
  `userId` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_projects
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_projects`;

CREATE TABLE `zp_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `clientId` int(100) DEFAULT NULL,
  `details` text,
  `state` int(2) DEFAULT NULL,
  `hourBudget` varchar(255) NOT NULL,
  `dollarBudget` int(11) DEFAULT NULL,
  `active` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `zp_projects` WRITE;
/*!40000 ALTER TABLE `zp_projects` DISABLE KEYS */;

INSERT INTO `zp_projects` (`id`, `name`, `clientId`, `details`, `state`, `hourBudget`, `dollarBudget`, `active`)
VALUES
	(1,'System 2.0',1,'<p>This is the new project</p>',NULL,'100',1000,NULL);

/*!40000 ALTER TABLE `zp_projects` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_punch_clock
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_punch_clock`;

CREATE TABLE `zp_punch_clock` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `minutes` int(11) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `punchout` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;



# Dump of table zp_read
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_read`;

CREATE TABLE `zp_read` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `module` enum('ticket','message') DEFAULT NULL,
  `moduleId` int(11) DEFAULT NULL,
  `userId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_read` WRITE;
/*!40000 ALTER TABLE `zp_read` DISABLE KEYS */;

INSERT INTO `zp_read` (`id`, `module`, `moduleId`, `userId`)
VALUES
	(1,'ticket',1,1),
	(2,'ticket',1,18),
	(3,'ticket',1,19);

/*!40000 ALTER TABLE `zp_read` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_referralSource
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_referralSource`;

CREATE TABLE `zp_referralSource` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_referralSource` WRITE;
/*!40000 ALTER TABLE `zp_referralSource` DISABLE KEYS */;

INSERT INTO `zp_referralSource` (`id`, `alias`, `title`)
VALUES
	(1,'bni','BNI'),
	(2,'social-media','Social Media'),
	(3,'website','Website'),
	(4,'friends-family','Friends & Family'),
	(5,'client','Client'),
	(6,'print','Print Advertising'),
	(7,'event','Event Advertising'),
	(10,'adwords','AdWords'),
	(11,'other','Other'),
	(12,'tv','TV Advertising'),
	(13,'sales-rep','Sales Representative '),
	(14,'rfp','RFP');

/*!40000 ALTER TABLE `zp_referralSource` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_relationUserProject
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_relationUserProject`;

CREATE TABLE `zp_relationUserProject` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `projectId` int(11) DEFAULT NULL,
  `wage` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `zp_relationUserProject` WRITE;
/*!40000 ALTER TABLE `zp_relationUserProject` DISABLE KEYS */;

INSERT INTO `zp_relationUserProject` (`id`, `userId`, `projectId`, `wage`)
VALUES
	(1,1,1,NULL),
	(2,19,1,NULL),
	(3,18,1,NULL);

/*!40000 ALTER TABLE `zp_relationUserProject` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_roles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_roles`;

CREATE TABLE `zp_roles` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `roleName` varchar(255) NOT NULL,
  `roleDescription` varchar(255) DEFAULT NULL,
  `sysOrg` int(255) DEFAULT NULL,
  `template` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_roles` WRITE;
/*!40000 ALTER TABLE `zp_roles` DISABLE KEYS */;

INSERT INTO `zp_roles` (`id`, `roleName`, `roleDescription`, `sysOrg`, `template`)
VALUES
	(2,'admin','Administrators',14,'zypro'),
	(3,'user','Clients',14,'zypro'),
	(4,'developer','Developer',14,'zypro'),
	(5,'manager','Manager',14,'zypro');

/*!40000 ALTER TABLE `zp_roles` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_rolesDefaultMenu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_rolesDefaultMenu`;

CREATE TABLE `zp_rolesDefaultMenu` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `roleId` int(255) DEFAULT NULL,
  `menuId` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_rolesDefaultMenu` WRITE;
/*!40000 ALTER TABLE `zp_rolesDefaultMenu` DISABLE KEYS */;

INSERT INTO `zp_rolesDefaultMenu` (`id`, `roleId`, `menuId`)
VALUES
	(2512,1,11),
	(2513,1,12),
	(2514,1,1),
	(2515,1,14),
	(2516,1,13),
	(2517,1,21),
	(2518,1,17),
	(2519,1,6),
	(2520,1,19),
	(2521,1,25),
	(2522,1,24),
	(2523,1,26),
	(2524,1,15),
	(2525,1,8),
	(2526,1,9),
	(2527,1,10),
	(2528,1,23),
	(2529,1,16),
	(2530,1,2),
	(2531,1,3),
	(2532,1,5),
	(2533,1,4),
	(2534,1,18),
	(2535,1,20),
	(2536,1,28),
	(2537,1,27),
	(2597,2,6),
	(2598,2,32),
	(2599,2,31),
	(2600,2,1),
	(2601,2,19),
	(2602,2,30),
	(2603,2,3),
	(2604,2,29),
	(2613,4,6),
	(2614,4,32),
	(2615,4,31),
	(2616,4,1),
	(2617,4,19),
	(2618,4,30),
	(2619,4,3),
	(2620,4,29),
	(2621,3,6),
	(2622,3,32),
	(2623,3,31),
	(2624,3,1),
	(2625,3,19),
	(2626,3,30),
	(2627,3,3),
	(2628,3,29),
	(2645,5,3);

/*!40000 ALTER TABLE `zp_rolesDefaultMenu` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_submoduleRights
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_submoduleRights`;

CREATE TABLE `zp_submoduleRights` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `alias` varchar(155) DEFAULT NULL,
  `title` varchar(155) DEFAULT NULL,
  `module` varchar(100) DEFAULT NULL,
  `submodule` varchar(150) DEFAULT NULL,
  `roleIds` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_submoduleRights` WRITE;
/*!40000 ALTER TABLE `zp_submoduleRights` DISABLE KEYS */;

INSERT INTO `zp_submoduleRights` (`id`, `alias`, `title`, `module`, `submodule`, `roleIds`)
VALUES
	(1,'comments-generalComment','COMMENTS','comments','generalComment.sub.php','2,4,5,3'),
	(2,'dashboard-calendar','CALENDAR','dashboard','calendar.sub.php','2,4,5,3'),
	(3,'dashboard-escalatingTickets','ESCALATING_TICKETS','dashboard','escalatingTickets.sub.php','2,4,5'),
	(4,'dashboard-hotLeads','HOT_LEADS','dashboard','hotLeads.sub.php','2,4,5'),
	(5,'dashboard-myHours','MY_HOURS','dashboard','myHours.sub.php','2,4,5'),
	(6,'dashboard-myProjects','MY_PROJECTS','dashboard','myProjects.sub.php','2,4,5'),
	(7,'dashboard-myTickets','MY_TICKETS','dashboard','myTickets.sub.php','2,4,5,3'),
	(8,'dashboard-notes','NOTES','dashboard','notes.sub.php','2,4,5,3'),
	(9,'dashboard-projectsProgress','PROJECT_PROGRESS','dashboard','projectsProgress.sub.php','2,4,5,3'),
	(10,'dashboard-statistics','STATISTICS','dashboard','statistics.sub.php','2,4,5'),
	(11,'dashboard-supportInfo','SUPPORT_INFO','dashboard','supportInfo.sub.php','2,4,5,3'),
	(12,'dashboard-timeTracker','','dashboard','timeTracker.sub.php','2,4,5'),
	(13,'projects-budgeting','BUDGETING','projects','budgeting.sub.php','2,5'),
	(14,'projects-tickets','TICKETS','projects','tickets.sub.php','2,4,5,3'),
	(15,'projects-timesheet','TIMESHEET','projects','timesheet.sub.php','2,4,5'),
	(16,'tickets-assignUsers','ASSIGN_USERS','tickets','assignUsers.sub.php',NULL),
	(17,'tickets-attachments','FILES','tickets','attachments.sub.php','2,4,5,3'),
	(18,'tickets-comments','COMMENTS','tickets','comments.sub.php','2,4,5,3'),
	(19,'tickets-technicalDetails','TECHNICAL_DETAILS','tickets','technicalDetails.sub.php','2,4,5,3'),
	(20,'tickets-ticketDetails','TICKET_DETAILS','tickets','ticketDetails.sub.php','2,4,5,3'),
	(21,'tickets-ticketHistory','TICKET_HISTORY','tickets','ticketHistory.sub.php','2,4,5'),
	(22,'tickets-timesheet','TIMESHEET','tickets','timesheet.sub.php','2,4,5');

/*!40000 ALTER TABLE `zp_submoduleRights` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_system_organisations
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_system_organisations`;

CREATE TABLE `zp_system_organisations` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `alias` varchar(100) DEFAULT NULL,
  `name` tinytext,
  `modules` text,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_system_organisations` WRITE;
/*!40000 ALTER TABLE `zp_system_organisations` DISABLE KEYS */;

INSERT INTO `zp_system_organisations` (`id`, `alias`, `name`, `modules`)
VALUES
	(14,'users','Users','accounts,calendar,clients,comments,dashboard,files,general,leads,messages,projects,setting,tickets,timesheets,users,wiki,'),
	(15,'administrator','Administrator','accounts,calendar,clients,dashboard,general,messages,projects,setting,tickets,timesheets,users,wiki,');

/*!40000 ALTER TABLE `zp_system_organisations` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_ticket_comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_ticket_comments`;

CREATE TABLE `zp_ticket_comments` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `text` text,
  `userId` int(255) DEFAULT NULL,
  `ticketId` int(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `commentParent` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_ticketfiles
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_ticketfiles`;

CREATE TABLE `zp_ticketfiles` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `encName` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `realName` varchar(40) CHARACTER SET utf8 DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `ticketId` int(255) DEFAULT NULL,
  `userId` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;



# Dump of table zp_ticketHistory
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_ticketHistory`;

CREATE TABLE `zp_ticketHistory` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `ticketId` int(11) DEFAULT NULL,
  `changeType` varchar(255) DEFAULT NULL,
  `changeValue` varchar(150) DEFAULT NULL,
  `dateModified` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_ticketHistory` WRITE;
/*!40000 ALTER TABLE `zp_ticketHistory` DISABLE KEYS */;

INSERT INTO `zp_ticketHistory` (`id`, `userId`, `ticketId`, `changeType`, `changeValue`, `dateModified`)
VALUES
	(1,18,1,'deadline','2016-01-01','2015-02-27 11:42:40'),
	(2,18,1,'fromDate','2016-02-01','2015-02-27 11:42:40'),
	(3,18,1,'toDate','2016-01-15','2015-02-27 11:42:40'),
	(4,18,1,'planHours','15','2015-02-27 11:42:40');

/*!40000 ALTER TABLE `zp_ticketHistory` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_tickets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_tickets`;

CREATE TABLE `zp_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `projectId` int(11) DEFAULT NULL,
  `headline` varchar(255) DEFAULT NULL,
  `description` text,
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
  `planHours` int(10) DEFAULT '0',
  `type` varchar(255) DEFAULT NULL,
  `production` int(1) DEFAULT '0',
  `staging` int(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `ProjectUserId` (`projectId`,`userId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `zp_tickets` WRITE;
/*!40000 ALTER TABLE `zp_tickets` DISABLE KEYS */;

INSERT INTO `zp_tickets` (`id`, `projectId`, `headline`, `description`, `date`, `dateToFinish`, `priority`, `status`, `userId`, `os`, `browser`, `resolution`, `component`, `version`, `url`, `dependingTicketId`, `editFrom`, `editTo`, `editorId`, `planHours`, `type`, `production`, `staging`)
VALUES
	(1,1,'I need to manage','<p>This is a test story</p>','2015-02-27 00:00:00','2016-01-01 00:00:00','3',3,1,'NOT_SPECIFIED','NOT_SPECIFIED','NOT_SPECIFIED',NULL,'','',0,'2016-02-01 00:00:00','2016-01-15 00:00:00','1,18',15,'Story',0,0);

/*!40000 ALTER TABLE `zp_tickets` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_timesheets
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_timesheets`;

CREATE TABLE `zp_timesheets` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `userId` int(255) DEFAULT NULL,
  `ticketId` int(255) DEFAULT NULL,
  `workDate` datetime DEFAULT NULL,
  `hours` float DEFAULT NULL,
  `description` text,
  `kind` varchar(255) DEFAULT NULL,
  `invoicedEmpl` int(2) DEFAULT NULL,
  `invoicedComp` int(2) DEFAULT NULL,
  `invoicedEmplDate` datetime DEFAULT NULL,
  `invoicedCompDate` datetime DEFAULT NULL,
  `rate` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

LOCK TABLES `zp_timesheets` WRITE;
/*!40000 ALTER TABLE `zp_timesheets` DISABLE KEYS */;

INSERT INTO `zp_timesheets` (`id`, `userId`, `ticketId`, `workDate`, `hours`, `description`, `kind`, `invoicedEmpl`, `invoicedComp`, `invoicedEmplDate`, `invoicedCompDate`, `rate`)
VALUES
	(1,1,1,'2015-02-27 00:00:00',2,'I was working on it here','DEVELOPMENT',0,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','25'),
	(2,18,1,'2015-02-19 00:00:00',3,'I was working on it too','DEVELOPMENT',0,0,'0000-00-00 00:00:00','0000-00-00 00:00:00','');

/*!40000 ALTER TABLE `zp_timesheets` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_user
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_user`;

CREATE TABLE `zp_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL DEFAULT '',
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `profileId` varchar(100) NOT NULL DEFAULT '',
  `lastlogin` datetime DEFAULT NULL,
  `lastpwd_change` int(11) DEFAULT NULL,
  `status` varchar(1) NOT NULL DEFAULT 'A',
  `expires` int(11) DEFAULT NULL,
  `role` varchar(200) NOT NULL,
  `session` varchar(100) DEFAULT NULL,
  `sessiontime` varchar(50) DEFAULT NULL,
  `wage` int(11) DEFAULT NULL,
  `hours` int(11) DEFAULT NULL,
  `description` text,
  `clientId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_user` WRITE;
/*!40000 ALTER TABLE `zp_user` DISABLE KEYS */;

INSERT INTO `zp_user` (`id`, `username`, `password`, `firstname`, `lastname`, `phone`, `profileId`, `lastlogin`, `lastpwd_change`, `status`, `expires`, `role`, `session`, `sessiontime`, `wage`, `hours`, `description`, `clientId`)
VALUES
	(1,'admin@admin.com','$P$B0OR9d1MJdyN/nU8K74Qub11mkL81K.','Admin','Admin','','50','2015-03-01 08:52:27',1301699624,'a',NULL,'2','ae7a6e8accaa81a4943d5bf9e31fa6fb-e5cc65862e1226232fafb723105a008e','1425228747',25,0,NULL,0),
	(18,'developer@developer.com','$P$B0OR9d1MJdyN/nU8K74Qub11mkL81K.','Developer','Developer','','','2015-02-27 08:44:40',NULL,'A',NULL,'4','','1425066280',NULL,NULL,NULL,NULL),
	(19,'client@client.com','$P$B0OR9d1MJdyN/nU8K74Qub11mkL81K.','Client','Client','','','2015-02-27 10:01:59',NULL,'A',NULL,'3','','1425070919',NULL,NULL,NULL,NULL),
	(20,'manager@manager.com','$P$B0OR9d1MJdyN/nU8K74Qub11mkL81K.','Manager','Manager','','','2015-02-27 09:11:20',NULL,'A',NULL,'5','','1425067880',NULL,NULL,NULL,2);

/*!40000 ALTER TABLE `zp_user` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_usermenu
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_usermenu`;

CREATE TABLE `zp_usermenu` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `menuId` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;



# Dump of table zp_widget
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_widget`;

CREATE TABLE `zp_widget` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `submoduleAlias` varchar(255) DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_widget` WRITE;
/*!40000 ALTER TABLE `zp_widget` DISABLE KEYS */;

INSERT INTO `zp_widget` (`id`, `submoduleAlias`, `title`)
VALUES
	(1,'dashboard-calendar','My Calendar'),
	(3,'dashboard-myTickets','My Tickets'),
	(8,'dashboard-projectsProgress','Project Progress'),
	(9,'dashboard-notes','Notes'),
	(10,'dashboard-supportInfo','Support Information'),
	(11,'dashboard-statistics','Statistics'),
	(12,'dashboard-myProjects','My Projects'),
	(13,'dashboard-myHours','My Hours'),
	(14,'dashboard-hotLeads','Hot Leads');

/*!40000 ALTER TABLE `zp_widget` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_widgetRelation
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_widgetRelation`;

CREATE TABLE `zp_widgetRelation` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `userId` int(11) DEFAULT NULL,
  `widgetId` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

LOCK TABLES `zp_widgetRelation` WRITE;
/*!40000 ALTER TABLE `zp_widgetRelation` DISABLE KEYS */;

INSERT INTO `zp_widgetRelation` (`id`, `userId`, `widgetId`)
VALUES
	(168,1,1),
	(169,1,3),
	(170,1,8),
	(171,1,9),
	(172,1,10),
	(173,1,11),
	(174,1,12),
	(175,1,13),
	(176,1,14),
	(214,18,1),
	(215,18,3),
	(216,18,9),
	(217,20,1),
	(218,20,3),
	(219,20,9),
	(220,19,1),
	(221,19,3),
	(222,19,9);

/*!40000 ALTER TABLE `zp_widgetRelation` ENABLE KEYS */;
UNLOCK TABLES;


# Dump of table zp_wiki
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_wiki`;

CREATE TABLE `zp_wiki` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `headline` text,
  `text` text,
  `tags` text,
  `authorId` int(255) DEFAULT NULL,
  `category` varchar(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_wiki_categories
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_wiki_categories`;

CREATE TABLE `zp_wiki_categories` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_wiki_comments
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_wiki_comments`;

CREATE TABLE `zp_wiki_comments` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `text` text,
  `userId` int(255) DEFAULT NULL,
  `articleId` int(255) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `commentParent` int(100) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;



# Dump of table zp_wiki_files
# ------------------------------------------------------------

DROP TABLE IF EXISTS `zp_wiki_files`;

CREATE TABLE `zp_wiki_files` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `encName` varchar(50) DEFAULT NULL,
  `realName` varchar(50) DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `articleId` int(255) DEFAULT NULL,
  `userId` int(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;




/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;
/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
