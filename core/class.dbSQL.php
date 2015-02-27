<?php

/**
 * Database Class - Connection and query handling
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */

class dbSQL {

	/**
	 * @access private
	 * @var string database host default: localhost
	 */
	private $host='localhost';

	/**
	 * @access private
	 * @var string username for db
	 */
	private $user='';

	/**
	 * @access private
	 * @var string password for db
	 */
	private $password='';

	/**
	 * @access private
	 * @var string database on the server
	 */
	private $database='';

	/**
	 * @access private
	 * @var string sql query-String
	 */
	private $sql='';

	/**
	 * @access private
	 * @var connection database connection
	 */
	private $connection='';

	/**
	 * @access public
	 * @var object query Result
	 */
	public $result = '';

	/**
	 * @access public
	 * @var integer number of rows (CAUTION: Limited numrows with SQL LIMIT)
	 */
	public $counter = NULL;

	/**
	 * __construct - connect to database and select db
	 *
	 * @return object
	 */
	function __construct() {

		//Get configuration-object for connection-details
		$config = new config();

		$this->user = $config->dbUser;
		$this->password = $config->dbPassword;
		$this->database = $config->dbDatabase;
		$this->host= $config->dbHost;

		$this->connection = mysql_connect($this->host, $this->user, $this->password) or die(mysql_error());

		mysql_query("SET NAMES 'utf8'");

		mysql_query("SET CHARACTER SET 'utf8'");

		try{

			$this->connection;
				
			mysql_select_db($this->database, $this->connection);
				
			return true;

		}catch(Exception $e){

			echo $e->getMessage();

		}
			
	}

	/**
	 * dbQuery - fires SQL-query and checks results
	 *
	 * @access public
	 * @param $sql SQL-String
	 * @return object returns result-object
	 */
	public function dbQuery($sql) {

		try{

			$this->result = mysql_query($sql, $this->connection);

		}catch(Exception $e){

			echo $e->getMessage();

		}

		$this->counter=NULL;

		if(empty($this->result)) {

		throw new Exception('SQL-Error: Failed String: '.nl2br($sql).'<br /><br />MySQL says: '.mysql_error().'');

			return false;

		}else{

			return $this;

		}
	}

	/**
	 * Count - True counter of results
	 *
	 * @access public
	 * @return integer (
	 */
	public function count() {

		if($this->counter===NULL && is_resource($this->result)===true) {

			$this->counter=mysql_num_rows($this->result);

		}

		return $this->counter;
	}

	/**
	 * dbFetchRow - get one Dataset row and masks html
	 *
	 * @access public
	 * @return array Dataset
	 */
	public function dbFetchRow() {
			
		if(is_resource($this->result)===true){

			$row = mysql_fetch_assoc($this->result);

			if(is_array($row)){
				$row = array_map('htmlspecialchars', $row);
				return $row;
					
			}

		}else{
				
			throw new Exception('Es wurde kein query versendet');

		}

	}

	/**
	 * dbFetchResults - Fetch all results and return array and masks html
	 *
	 * @access public
	 * @return array
	 */
	public function dbFetchResults() {
			
		if(is_resource($this->result)===true){

			$i=0;
				
			//Get results and build an array (...better to handle in Templates)
			while($array[$i] = mysql_fetch_assoc($this->result)) {

				$array[$i] = array_map('htmlspecialchars', $array[$i]);

				$i++;

			}

			array_pop($array);

			return $array;

		}else{

			throw new Exception('DB: es wurde kein query versendet');

		}

	}

	/**
	 * dbFetchRowUnmasked - get one Dataset row without masking html
	 *
	 * @access public
	 * @return array Dataset
	 */
	public function dbFetchRowUnmasked() {
			
		if(is_resource($this->result)===true){

			$row = mysql_fetch_assoc($this->result);

			if(is_array($row)){

				return $row;
					
			}

		}else{
				
			throw new Exception('Es wurde kein query versendet');

		}

	}

	/**
	 * dbFetchResultsUnmasked - Fetch all results and return array without masking html
	 *
	 * @access public
	 * @return array
	 */
	public function dbFetchResultsUnmasked() {
			
		if(is_resource($this->result)===true){

			$i=0;
				
			//Get results and build an array (...better to handle in Templates)
			while($array[$i] = mysql_fetch_assoc($this->result)) {

				$i++;

			}

			array_pop($array);

			return $array;

		}else{

			throw new Exception('DB: es wurde kein query versendet');

		}

	}
	
	public function hasResults(){
		$row = mysql_fetch_assoc($this->result);

			if(is_array($row)){
				
				return true;
			
			}else{
				return false;
			}
		
	}

}

?>