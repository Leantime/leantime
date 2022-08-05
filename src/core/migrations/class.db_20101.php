<?php

namespace leantime\core\migrations {

	use leantime\core\migration;

	class db_20101 extends migration {

		public function get_sql(){
			return [
                "UPDATE `zp_user` SET role = 50 WHERE role = 2;",
                "UPDATE `zp_user` SET role = 10 WHERE role = 3;",
                "UPDATE `zp_user` SET role = 20 WHERE role = 4;",
                "UPDATE `zp_user` SET role = 40 WHERE role = 5;",
            ];
		}
	}
}
