<?php

namespace leantime\core\migrations {

	use leantime\core\migration;

	class db_20106 extends migration {

		public function get_sql(){
			return [
                "ALTER TABLE `zp_user` ADD COLUMN `source` varchar(200) DEFAULT NULL"
            ];
		}
	}
}
