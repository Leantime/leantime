<?php

namespace leantime\core;

use leantime\base\eventhelpers;
use PDO;

class dbcall
{
    use eventhelpers;

    /**
     * @var string
     * @access private
     */
    private $callname;

    /**
     * @var array
     * @access private
     */
    private $args;

    /**
     * constructor
     *
     * @param string $callname - usually the caller function name, gives events/filters context
     * @param array $args - usually the value of func_get_args(), gives events/filters values to work with
     */
    public function __construct($callname, $args)
    {
        $this->args = $args;
        $this->callname = $callname;
        $this->db = db::getInstance();
    }

    /**
     * prepares sql for entry; wrapper for PDO\prepare()
     *
     * @param string $sql
     * @param array $args - additional arguments to pass along to prepare function
     */
    public function prepare($sql, $args = []) {
        $sql = self::dispatch_filter(
            "{$this->callname}.sql",
            $sql,
            $this->getArgs(['prepareArgs' => $args])
        );

        $this->stmn = $this->db->database->prepare($sql, $args);
    }

    /**
     * binds values for search/replace of sql; wrapper for PDO\bindValue()
     *
     * @param string $needle - placeholder to replace
     * @param string $replace - value to replace with
     * @param mixed $type - type of value being replaced
     */
    public function bindValue($needle, $replace, $type = PDO::PARAM_STR)
    {
        $replace = self::dispatch_filter("{$this->callname}.binding.$needle", $replace, $this->getArgs());

        $this->stmn->bindValue($needle, $replace, $type);
    }

    /**
     * executes the sql call - uses \PDO
     *
     * @param string $fetchtype - the type of fetch to do (optional)
     *
     * @return mixed
     */
    public function execute($fetchtype = null)
    {
        self::dispatch_event("{$this->callname}.beforeExecute", $this->getArgs());

        $this->stmn = self::dispatch_filter("{$this->callname}.stmn", $this->stmn, $this->getArgs());
        $this->fetchtype = self::dispatch_filter("{$this->callname}.fetchtype", $fetchtype, $this->getArgs());

        $values = $this->stmn->execute();
        if (is_string($fetchtype) && !empty($fetchtype)) {
            $values = $this->stmn->$fetchtype();
        }
        $this->stmn->closeCursor();

        self::dispatch_event("{$this->callname}.afterExecute", $this->getArgs());

        return self::dispatch_filter('return', $values, $this->getArgs());
    }

    /**
     * Gets the arguments to pass along to events/filter
     *
     * @param array $additions - any other additional parameters to include
     *
     * @return array
     */
    private function getArgs($additions = [])
    {
        $args = array_merge($this->args, ['self' => $this]);

        if (!empty($additions)) {
            $args = array_merge($args, $additions);
        }

        self::dispatch_filter("{$this->callname}.args", $args);

        return $args;
    }
}
