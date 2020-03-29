<?php

/**
 * Database Class - Very simple abstraction layer for pdo connection
 *
 */

namespace leantime\core;

use \PDO;

class db
{

    /**
     * @access private
     * @var    string database host default: localhost
     */
    private $host='';

    /**
     * @access private
     * @var    string username for db
     */
    private $user='';

    /**
     * @access private
     * @var    string password for db
     */
    private $password='';


    private $databaseName='';


    public $database='';
    /**
     * @access private
     * @var    pdo object
     */
    private static $instance='';

    /**
     * @access private
     * @var    string sql query-String
     */
    private $sql='';

    /**
     * @access private
     * @var    connection database connection
     */
    private $connection='';

    /**
     * @access public
     * @var    object query Result
     */
    public $result = '';

    /**
     * @access public
     * @var    integer number of rows (CAUTION: Limited numrows with SQL LIMIT)
     */
    public $counter = null;

    /**
     * __construct - connect to database and select db
     *
     * @return object
     */
    private function __construct()
    {

            //Get configuration-object for connection-details
            $config = new config();

            $this->user = $config->dbUser;
            $this->password = $config->dbPassword;
            $this->databaseName = $config->dbDatabase;
            $this->host= $config->dbHost;

        try{

            $driver_options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4,sql_mode="NO_ENGINE_SUBSTITUTION"' );
            $this->database = new PDO('mysql:host=' . $this->host . ';dbname='. $this->databaseName .'', $this->user, $this->password, $driver_options);
            $this->database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        }catch(\PDOException $e){

            echo "No database connection, check your database credentials in your configuration file.";

            exit();

        }

    }

    public static function getInstance()
    {

        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;

    }


    /**
     * Count - True counter of results
     *
     * @access public
     * @return integer (
     */
    
    public function count()
    {

        if($this->counter===null ) {
            
            $this->counter=$this->result->fetchColumn();;

        }

        return $this->counter;
    }
    
    
    /**
     * dbFetchRow - get one Dataset row and masks html
     *
     * @access public
     * @return array Dataset
     */
    public function dbFetchRow()
    {
                    
        $row = $this->result->fetch(PDO::FETCH_ASSOC);

        if(is_array($row)) {
            $row = array_map('htmlspecialchars', $row);
            return $row;
                    
        }


    }

    /**
     * dbFetchResults - Fetch all results and return array and masks html
     *
     * @access public
     * @return array
     */
    public function dbFetchResults()
    {

        $i=0;
                
        //Get results and build an array (...better to handle in Templates)
        while($array[$i] = $this->result->fetch(PDO::FETCH_ASSOC)) {

            $array[$i] = array_map('htmlspecialchars', $array[$i]);

            $i++;

        }

        array_pop($array);

        return $array;

    

    }

    /**
     * dbFetchRowUnmasked - get one Dataset row without masking html
     *
     * @access public
     * @return array Dataset
     */
    public function dbFetchRowUnmasked()
    {
            
        $row = $this->result->fetch(PDO::FETCH_ASSOC);

        if(is_array($row)) {

            return $row;
                    
        }

        
    }

    /**
     * dbFetchResultsUnmasked - Fetch all results and return array without masking html
     *
     * @access public
     * @return array
     */
    public function dbFetchResultsUnmasked()
    {
        
        $i=0;
                
        //Get results and build an array (...better to handle in Templates)
        while($array[$i] = $this->result->fetch(PDO::FETCH_ASSOC)) {
            $i++;

        }

        array_pop($array);

        return $array;

        

    }
    
    
    
    public function getErrorMessage($err)
    {
        
        print_r($err);
                $trace = '<table border="0">';
        foreach ($err->getTrace() as $a => $b) {
            foreach ($b as $c => $d) {
                if ($c == 'args') {
                    foreach ($d as $e => $f) {
                        $trace .= '<tr><td><b>' . strval($a) . '#</b></td><td align="right"><u>args:</u></td> <td><u>' . $e . '</u>:</td><td><i>' . $f . '</i></td></tr>';
                    }
                } else {
                    $trace .= '<tr><td><b>' . strval($a) . '#</b></td><td align="right"><u>' . $c . '</u>:</td><td></td><td><i>' . $d . '</i></td>';
                }
            }
        }
         $trace .= '</table>';
          echo '<br /><br /><br /><font face="Verdana"><center><fieldset style="width: 66%; border: 1px solid white; background: white;"><legend><b>[</b>PHP PDO Error ' . strval($err->getCode()) . '<b>]</b></legend> <table border="0"><tr><td align="right"><b><u>Message:</u></b></td><td><i>' . $err->getMessage() . '</i></td></tr><tr><td align="right"><b><u>Code:</u></b></td><td><i>' . strval($err->getCode()) . '</i></td></tr><tr><td align="right"><b><u>File:</u></b></td><td><i>' . $err->getFile() . '</i></td></tr><tr><td align="right"><b><u>Line:</u></b></td><td><i>' . strval($err->getLine()) . '</i></td></tr><tr><td align="right"><b><u>Trace:</u></b></td><td><br /><br />' . $trace . '</td></tr></table></fieldset></center></font>';
    }

    
    public function hasResults()
    {
        
        $row = $this->result->fetch(PDO::FETCH_ASSOC);

        if(is_array($row)) {
                
            return true;
            
        }else{
            return false;
        }
        
    }

    /**
     * This function will generate a pdo binding string (":editors0,:editors1,:editors2,:editors3") to be used in a PDO
     * query that uses the IN() clause, to assist in proper PDO array bindings to avoid SQL injection.
     *
     * A counted for loop is user rather than foreach with a key to avoid issues if the array passed has any
     * arbitrary keys
     *
     * @param $name string
     * @param $count int
     * @return string
     */
    public static function arrayToPdoBindingString($name, $count)
    {
        $bindingStatement = "";
        for ($i = 0; $i < $count; $i++) {
            $bindingStatement .= ":" . $name . $i;
            if ($i != $count-1) {
                $bindingStatement .= ",";
            }
        }

        return $bindingStatement;
    }

    /**
     * Sanitizes a string to only contain letters, numbers and underscore.
     * Used for patch statements with variable column keys values
     *
     *
     * @param $name string
     * @return string
     */
    public static function sanitizeToColumnString($string) {
        return preg_replace("/[^a-zA-Z0-9_]/", "", $string);
    }

}
