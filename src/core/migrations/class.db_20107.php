<?php

namespace leantime\core\migrations {

	use leantime\core\migration;

	class db_20107 extends migration {

		public function get_sql(){
			return [
                "INSERT INTO zp_settings (`key`, `value`) VALUES ('companysettings.telemetry.active', 'true')"
            ];
		}
	}
}
