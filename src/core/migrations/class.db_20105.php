<?php

namespace leantime\core\migrations {

	use leantime\core\migration;

	class db_20105 extends migration {

		public function get_sql(){
			return [
                "ALTER TABLE `zp_projects` ADD COLUMN `psettings` MEDIUMTEXT NULL AFTER `active`"
            ];
		}
	}
}
