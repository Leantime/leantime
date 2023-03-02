<?php

namespace leantime\core;

use PDO;
use PDOStatement;

abstract class repository
{
    use eventhelpers;

    protected function dbcall(array $args)
    {
        return new class ($args, $this) {
    
            private PDOStatement $stmn;

            /**
             * @var array
             * @access private
             */
            private array $args;

            /**
             * @var repository
             * @access private
             */
            private repository $caller_class;

            /**
             * @var db
             * @access private
             */
            private db $db;

            /**
             * constructor
             *
             * @param array $args - usually the value of func_get_args(), gives events/filters values to work with
             * @param object $caller_class - the class object that was called
             */
            public function __construct(array $args, repository $caller_class)
            {
                $this->args = $args;
                $this->caller_class = $caller_class;
                $this->db = db::getInstance();
            }

            /**
             * prepares sql for entry; wrapper for PDO\prepare()
             *
             * @param string $sql
             * @param array $args - additional arguments to pass along to prepare function
             */
            public function prepare($sql, $args = []): void
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
             * @param string $needle - placeholder to replace
             * @param string $replace - value to replace with
             * @param mixed $type - type of value being replaced
             */
            public function bindValue($needle, $replace, $type = PDO::PARAM_STR): void
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
             * Gets the arguments to pass along to events/filter
             *
             * @param array $additions - any other additional parameters to include
             *
             * @return array
             */
            private function getArgs($additions = []): array
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
             * @param string $fetchtype - the type of fetch to do (optional)
             *
             * @return mixed
             */
            public function __call(string $method, $arguments): mixed
            {
                if (!isset($this->stmn)) {
                    throw new Error("You must run the 'prepare' method first!");
                }

                if (!in_array($method, ['execute', 'fetch', 'fetchAll'])) {
                    throw new Error("Method does not exist");
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
}
