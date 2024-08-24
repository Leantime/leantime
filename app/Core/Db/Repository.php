<?php

namespace Leantime\Core\Db;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Events\DispatchesEvents;
use PDO;
use PDOStatement;
use ReflectionClass;
use ReflectionProperty;

/**
 * Repository
 *
 * @package    leantime
 * @subpackage core
 */
abstract class Repository
{
    use DispatchesEvents;

    /**
     * @var string
     */
    protected string $entity;

    /**
     * @var string
     */
    protected string $model;

    /**
     * dbcall - creates a new dbcall object
     *
     * @param array $args - usually the value of func_get_args(), gives events/filters values to work with
     * @return object
     */
    protected function dbcall(array ...$args): object
    {
        return new class ($args, $this) {
            /**
             * @var PDOStatement
             */
            private PDOStatement $stmn;

            /**
             * @var array
             */
            private array $args;

            /**
             * @var Repository
             */
            private Repository $caller_class;

            /**
             * @var db
             */
            private Db $Db;
            /**
             * @var \Closure|mixed|object|null
             */
            private mixed $db;

            /**
             * constructor
             *
             * @param array      $args         - usually the value of func_get_args(), gives events/filters values to work with
             * @param Repository $caller_class - the class object that was called
             */
            public function __construct(array $args, Repository $caller_class)
            {
                $this->args = $args;
                $this->caller_class = $caller_class;
                $this->db = app()->make(Db::class);
            }

            /**
             * prepares sql for entry; wrapper for PDO\prepare()
             *
             * @param string $sql
             * @param array  $args - additional arguments to pass along to prepare function
             */
            public function prepare(string $sql, array $args = []): void
            {
                $sql = $this->caller_class::dispatch_filter(
                    "sql",
                    $sql,
                    $this->getArgs(['prepareArgs' => $args]),
                    4
                );

                $this->stmn = $this->db->database->prepare($sql, $args);
            }

            /**
             * binds values for search/replace of sql; wrapper for PDO\bindValue()
             *
             * @param string $needle  - placeholder to replace
             * @param string $replace - value to replace with
             * @param int    $type    - type of value being replaced
             */
            public function bindValue(string $needle, mixed $replace, int $type = PDO::PARAM_STR): void
            {
                $replace = $this->caller_class::dispatch_filter(
                    'binding.' . str_replace(':', '', $needle),
                    $replace,
                    $this->getArgs(),
                    4
                );

                $this->stmn->bindValue($needle, $replace, $type);
            }

            /**
             * executes the sql call - uses \PDO
             * @return mixed
             */
            public function lastInsertId(): mixed
            {
                return $this->db->database->lastInsertId();
            }

            /**
             * executes the sql call - uses \PDO
             *
             * @param $mode
             * @param $class
             * @return bool
             */
            public function setFetchMode($mode, $class): bool
            {
                return $this->stmn->setFetchMode($mode, $class);
            }

            /**
             * Gets the arguments to pass along to events/filter
             *
             * @param array $additions - any other additional parameters to include
             *
             * @return array
             */
            private function getArgs(array $additions = []): array
            {
                $args = array_merge($this->args, ['self' => $this]);

                if (!empty($additions)) {
                    $args = array_merge($args, $additions);
                }

                $this->caller_class::dispatch_filter("args", $args, [], 5);

                return $args;
            }

            /**
             * executes the sql call - uses \PDO
             *
             * @param string    $method
             * @param $arguments
             * @return mixed
             */
            public function __call(string $method, $arguments): mixed
            {
                if (!isset($this->stmn)) {
                    throw new \Error("You must run the 'prepare' method first!");
                }

                if (!in_array($method, ['execute', 'fetch', 'fetchAll'])) {
                    throw new \Error("Method does not exist");
                }

                $this->caller_class::dispatch_event("beforeExecute", $this->getArgs(), 4);

                $this->stmn = $this->caller_class::dispatch_filter("stmn", $this->stmn, $this->getArgs(), 4);
                $method = $this->caller_class::dispatch_filter("method", $method, $this->getArgs(), 4);

                $values = $this->stmn->execute();

                if (in_array($method, ['fetch', 'fetchAll'])) {
                    $values = $this->stmn->$method();
                }

                $this->stmn->closeCursor();

                $this->caller_class::dispatch_event("afterExecute", $this->getArgs(), 4);

                return $this->caller_class::dispatch_filter('return', $values, $this->getArgs(), 4);
            }
        };
    }

    /**
     * patch - updates a record in the database
     *
     * @param int   $id     - the id of the record to update
     * @param array $params - the parameters to update
     * @return bool
     */
    public function patch(int $id, array $params): bool
    {
        if ($this->entity == '') {
            report("Patch not implemented for this entity");
            return false;
        }

        $sql = "UPDATE zp_" . $this->entity . " SET ";

        foreach ($params as $key => $value) {
            $sql .= "" . Db::sanitizeToColumnString($key) . "=:" . Db::sanitizeToColumnString($key) . ", ";
        }

        $sql .= "id=:id WHERE id=:id LIMIT 1";

        $call = $this->dbcall(func_get_args());

        $call->prepare($sql);

        $call->bindValue(':id', $id, PDO::PARAM_STR);

        foreach ($params as $key => $value) {
            $call->bindValue(':' . Db::sanitizeToColumnString($key), $value, PDO::PARAM_STR);
        }

        return $call->execute();
    }

    /**
     * @param object $objectToInsert
     * @return false|int
     * @throws \ReflectionException
     */
    public function insert(object $objectToInsert): false|int
    {

        if ($this->entity == '') {
            report("Insert not implemented for this entity");
            return false;
        }

        $sql = "INSERT INTO zp_" . $this->entity . " (";

        $sqlArr = array();
        foreach ($objectToInsert as $key => $value) {
            if ($this->getFieldAttribute($objectToInsert, $key)) {
                $sqlArr[] = "`" . Db::sanitizeToColumnString($key) . "`";
            }
        }
        $sql .= implode(",", $sqlArr);

        $sql .= ") VALUES (";

        $sqlArr2 = array();
        foreach ($objectToInsert as $key => $value) {
            if ($this->getFieldAttribute($objectToInsert, $key)) {
                $sqlArr2[] = ":" . Db::sanitizeToColumnString($key) . "";
            }
        }
        $sql .= implode(",", $sqlArr2);

        $sql .= ")";

        $call = $this->dbcall(func_get_args());

        $call->prepare($sql);

        foreach ($objectToInsert as $key => $value) {
            if ($this->getFieldAttribute($objectToInsert, $key)) {
                $call->bindValue(':' . Db::sanitizeToColumnString($key), $value, PDO::PARAM_STR);
            }
        }

        $call->execute();

        return $call->lastInsertId();
    }

    /**
     * delete - deletes a record from the database
     *
     * @param int $id - the id of the record to delete
     */
    public function delete(int $id): void
    {
    }

    /**
     * get - gets a record from the database
     *
     * @param int $id - the id of the record to get
     * @return mixed
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function get(int $id): mixed
    {
        if ($this->entity == '' || $this->model == '') {
            report("Get not implemented for this entity");
            return false;
        }

        $sql = "SELECT ";

        $entityModel = app()->make($this->model);
        $dbFields = $this->getDbFields($this->model);

        $sql .= implode(",", $dbFields);

        $sql .= " FROM zp_" . $this->entity . " WHERE id = :id ";

        $call = $this->dbcall(func_get_args());

        $call->prepare($sql);

        $call->bindValue(':id', $id, PDO::PARAM_STR);

        $call->execute();

        $call->setFetchMode(PDO::FETCH_CLASS, $this->model);

        return $call->fetch();
    }


    /**
     * getFieldAttribute - gets the field attribute for a given property
     *
     * @param object|string $class     - the class to get the attribute from
     * @param string        $property  - the property to get the attribute from
     * @param bool          $includeId - whether or not to include the id attribute
     * @return array|false
     * @throws \ReflectionException
     */
    protected function getFieldAttribute(object|string $class, string $property, bool $includeId = false): array|false
    {
        //Don't create or update id attributes
        if ($includeId === false && $property == "id") {
            return false;
        }

        $property = new ReflectionProperty($class, $property);

        $attributes = $property->getAttributes();
        foreach ($attributes as $attribute) {
            $name = $attribute->getName();
            if (str_contains($name, "DbColumn")) {
                return $attribute->getArguments();
            }
        }

        return false;
    }

    /**
     * getDbFields - gets the database fields for a given class
     *
     * @param object|string $class - the class to get the fields from
     * @return array
     * @throws \ReflectionException
     */
    protected function getDbFields(object|string $class): array
    {
        $property = new ReflectionClass($class);

        $properties = $property->getProperties();

        $propertyArray = array();
        foreach ($properties as $property) {
            if ($this->getFieldAttribute($class, $property->getName(), true)) {
                $propertyArray[] = $property->getName();
            }
        }

        return $propertyArray;
    }
}
