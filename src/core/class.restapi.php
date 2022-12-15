<?php
/**
 * Generates an API
 */

namespace leantime\core;

class restapi
{

    /**
     * __call - executes api call
     *
     * @param string $service - service method name
     * @param array $arguments - [$request_method, $function, $parameters]
     *
     * @return void
     */
    public function __call(string $service, array $arguments): void
    {

        header('Content-Type: application/json; charset=utf-8');

        $servicename = "leantime\\domain\\services\\$service";
        $methodName = $arguments['function'];
        $requestBody = $arguments['parameters'];
        $requestMethod = $arguments['request_method'];

        if (!class_exists($servicename)) {
            $this->returnError("Service doesn't exist");
        }

        if (!is_subclass_of($servicename, 'leantime\\core\\service')) {
            $this->returnError("Service is not supported yet.");
        }

        if (!method_exists($servicename, $methodName)) {
            $this->returnError("Method doesn't exist");
        }

        $methodParams = $this->getMethodParameters($servicename, $methodName);
        $preparedParams = $this->prepareParameters($requestBody, $methodParams);

        // can be null
        try {
            $return_value = new $servicename->$requestMethod($methodName, $preparedParams);
        } catch (Error $e) {
            $this->returnError($e);
        }

        if ($return_value !== null) {
            if (!settype($return_value, 'array')) {
                $return_value = [$return_value];
            }
        }

        $this->returnResponse($return_value, $requestMethod);
    }

    /**
     * Gets the Method Parameters
     *
     * @param string $servicename
     * @param string $methodname
     *
     * @return \ReflectionParameter[]
     */
    private function getMethodParameters(string $servicename, string $methodname): array
    {
        $reflectionParameters = (new \ReflectionClass($servicename))
            ->getMethod($methodname)
            ->getParameters();

        return $reflectionParameters;
    }

    /**
     * Checks request params
     *
     * @param array $params
     * @param \ReflectionParameter[] $methodParams
     *
     * @return array
     */
    private function prepareParameters(array $params, array $methodParams): array
    {
        $filtered_parameters = [];

        // matches params, params that don't match are ignored
        foreach ($methodParams as $methodparam) {
            $required = $methodparam->isDefaultValueAvailable() ? false : true;
            $position = $methodparam->getPosition();
            $name = $methodparam->name;
            $type = $methodparam->getType();

            // check if param is there
            if (!in_array($name, array_keys($params))) {
                if ($required) {
                    $this->returnError("Required Parameter Missing: $name");
                }

                $filtered_parameters[$position] = $methodparam->getDefaultValue();
                continue;
            }

            // check if type is correct or can be correct
            if ($methodparam->hasType()) {
                if ($type == gettype($params[$name])) {
                    $filtered_parameters[$position] = $params[$name];
                    continue;
                }

                if (settype($params[$name], $type)) {
                    $filtered_parameters[$position] = $params[$name];
                    continue;
                }

                $this->returnError("Incorrect Type on Parameter: $name");
            }

            $filtered_parameters[$position] = $params[$name];
        }

        // make sure its in the right order
        ksort($filtered_parameters);

        return $filtered_parameters;
    }

    /**
     * Echos the return response
     *
     * @param array|null $returnValue
     * @param string $requestMethod
     *
     * @return void
     */
    private function returnResponse(array|null $returnValue, string $requestMethod): void
    {
        http_response_code(200);
        echo json_encode([
            'message' => "$requestMethod request was successful",
            'response' => $returnValue
        ]);
    }

    /**
     * Return error response
     *
     * @param string $errorMessage
     * @param int $httpResponseCode
     *
     * @return void
     */
    private function returnError(string $errorMessage, int $httpResponseCode = 500): void
    {
        http_response_code($httpResponseCode);
        echo json_encode([
            'error' => $errorMessage
        ]);
        exit;
    }

}
