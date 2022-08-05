<?php

namespace leantime\core {

	use PDO;
    use PDOException;

	class migration 
	{
		private $version;

		public function __construct(){
			$classname = get_class($this);
			$version = explode('_', $classname)[1];
			$this->version = $version;
		}

		public function get_version() 
		{
			return $this->version;
		}

		public function migrate() 
		{
			$errors = array();

            $sql = $this->get_sql();

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
            } else {
                return true;
            }
		}
	}
}