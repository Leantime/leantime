<?php

namespace leantime\core\migrations {

	use leantime\core\migration;

	class db_20104 extends migration {

		public function get_sql(){
			return [
                "ALTER TABLE `zp_user` ADD COLUMN `pwResetCount` INT(5) NULL AFTER `pwResetExpiration`",
                "ALTER TABLE `zp_user` ADD COLUMN `forcePwReset` TINYINT NULL AFTER `pwResetCount`",
                "ALTER TABLE `zp_user` ADD COLUMN `createdOn` DATETIME NULL AFTER `twoFASecret`",
                "ALTER TABLE `zp_user` CHANGE COLUMN `lastpwd_change` `lastpwd_change` DATETIME NULL DEFAULT NULL AFTER `forcePwReset`",
                "ALTER TABLE `zp_user` CHANGE COLUMN `expires` `expires` DATETIME NULL DEFAULT NULL",
            ];
		}
	}
}
