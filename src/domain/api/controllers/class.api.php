<?php
/**
 * Generates an API
 */

class api extends controller
{

    /**
     *
     */
    public function __call($repo, $arguments)
    {
        $reponame = "leantime\\domain\\repositories\\$repo";

        if (!class_exists($reponame)) {
            throw new Error("Repository doesn't exist");
        }

        if (!method_exists($reponame, $arguments['function'])) {
            throw new Error("Method doesn't exist");
        }

        // can be null
        $return_value = new $reponame->{$arguments['function']}($parameters);

        switch ($arguments['request_method']){
            case 'GET':
                echo json_encode($return_value);
                break;
            // TODO: Add other types
        }
    }

}
