<?php

namespace leantime\core;

use eventhelpers;

abstract class service {
    use eventhelpers;

    private array $getMethods = [];
    private array $postMethods = [];
    private array $putMethods = [];
    private array $deleteMethods = [];

    public function get(string $method, array $params): mixed
    {
        if (!in_array($method, $this->getMethods)) {
            throw new Error("This endpoint does not support GET requests");
        }

        return $this->$method($params);
    }

    public function post(string $method, array $params): mixed
    {
        if (!in_array($method, $this->postMethods)) {
            throw new Error("This endpoint does not support POST requests");
        }

        return $this->$method($params);
    }

    public function put(string $method, array $params): mixed
    {
        if (!in_array($method, $this->putMethods)) {
            throw new Error("This endpoint does not support PUT requests");
        }

        return $this->$method($params);
    }

    public function delete(string $method, array $params): mixed
    {
        if (!in_array($method, $this->deleteMethods)) {
            throw new Error("This endpoint does not support DELETE requests");
        }

        return $this->$method($params);
    }

    protected function setGet(string $functionName): void
    {
        if (in_array($functionName, $this->getMethods)) {
            return;
        }

        $this->getMethods[] = $functionName;
    }

    protected function setPost(string $functionName): void
    {
        if (in_array($functionName, $this->postMethods)) {
            return;
        }

        $this->postMethods[] = $functionName;
    }

    protected function setPut(string $functionName): void
    {
        if (in_array($functionName, $this->putMethods)) {
            return;
        }

        $this->putMethods[] = $functionName;
    }

    protected function setDelete(string $functionName): void
    {
        if (in_array($functionName, $this->deleteMethods)) {
            return;
        }

        $this->deleteMethods[] = $functionName;
    }
}
