<?php

namespace leantime\core\migrations {

	use leantime\core\migration;

	class db_20102 extends migration {

		public function get_sql(){
			return [
                "ALTER TABLE `zp_user` add COLUMN `twoFAEnabled` tinyint(1) DEFAULT '0'",
                "ALTER TABLE `zp_user` add COLUMN `twoFASecret` varchar(200) DEFAULT NULL"
            ];
		}
	}
}
