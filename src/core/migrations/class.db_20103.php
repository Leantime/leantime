<?php

namespace leantime\core\migrations {

	use leantime\core\migration;

	class db_20103 extends migration {

		public function get_sql(){
			return [
                "ALTER TABLE `zp_tickets` CHANGE COLUMN `planHours` `planHours` FLOAT NULL DEFAULT NULL",
                "ALTER TABLE `zp_tickets` CHANGE COLUMN `hourRemaining` `hourRemaining` FLOAT NULL DEFAULT NULL"
            ];
		}
	}
}
