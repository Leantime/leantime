<?php

/**
 * Generates an JSON-RPC 2.0 API
 */

namespace Leantime\Domain\Api\Controllers;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Str;
use Leantime\Core\Controller\Controller;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Jsonrpc extends Controller
{
    /**
     * @var array $json_data - holds json data from request body
     */
    private array $json_data = [];

    /**
     * init - initialize private variables or events to happen before route execution
     *
     * @return void
     */
    public function init(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST)) {
            $this->json_data = json_decode(file_get_contents('php://input'), JSON_OBJECT_AS_ARRAY);
        }
    }

    /**
     * Handles post requests
     *
     * @param array $params - value of $_POST
     *
     * @return Response
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function post(array $params): Response
    {
        if (empty($params)) {
            $params = $this->json_data;
        }

        // params['params'] could be array (single value) or json object
        if (isset($params['params'])) {
            if (!is_array($params['params'])) {
                $params['params'] = json_decode($params['params'], JSON_OBJECT_AS_ARRAY);
            }
        }

        return $this->executeApiRequest($params);
    }

    /**
     * Handles get requests
     *
     * @param array $params - value of $_GET
     *
     * @return Response
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function get(array $params): Response
    {
        if (!isset($params['method'])) {
            return $this->returnInvalidRequest("Method name required");
        }

        /**
         * Decode params
         *
         * @see https://www.jsonrpc.org/historical/json-rpc-over-http.html#get
         */
        if (isset($params['params'])) {
            $paramsDecoded = base64_decode(urldecode($params['params']));
        } else {
            $paramsDecoded = array();
        }

        $params = array(
            "method" => $params['method'],
            "params" => $paramsDecoded,
            "id" => $params["id"] ?? null,
            "jsonrpc" => $params["jsonrpc"] ?? '',
        );

        $params["params"] = json_decode($params['params'], JSON_OBJECT_AS_ARRAY);

        // check if decode failed
        if ($params == null) {
            return $this->returnParseError('JSON is invalid and was not able to be parsed');
        }

        return $this->executeApiRequest($params);
    }

    /**
     * Handles patch requests
     *
     * @return Response
     */
    public function patch(): Response
    {
        return $this->returnInvalidRequest('The JSON-RPC API only supports POST/GET requests');
    }

    /**
     * Handles delete requests
     *
     * @return Response
     */
    public function delete(): Response
    {
        return $this->returnInvalidRequest('The JSON-RPC API only supports POST/GET requests');
    }

    /**
     * executes api call
     *
     * @param array $params - request body
     *
     * @return Response
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    private function executeApiRequest(array $params): Response
    {
        /**
         * checks to see if array keys are incremented, if so, assume it's a batch request
         *
         * @see https://jsonrpc.org/specification#batch
         */
        if (array_keys($params) == range(0, count($params) - 1)) {
            return $this->tpl->displayJson(array_map(
                fn ($requestParams) => json_decode($this->executeApiRequest($requestParams)->getContent()),
                $params
            ));
        }

        $id = $params['id'] ?? null;

        try {
            $methodparts = $this->parseMethodString($params['method'] ?? '');
        } catch (Exception $e) {
            return $this->returnInvalidParams($e, $id);
        }

        $jsonRpcVer = $params['jsonrpc'] ?? null;

        $moduleName = Str::studly($methodparts['module']);
        $serviceName = Str::studly($methodparts['service']);

        $domainServiceNamespace = app()->getNamespace() . "Domain\\$moduleName\\Services\\$serviceName";
        $pluginServiceNamespace = app()->getNamespace() . "Plugins\\$moduleName\\Services\\$serviceName";

        $methodName = Str::camel($methodparts['method']);

        $paramsFromRequest = $params['params'] ?? [];

        if (class_exists($domainServiceNamespace)) {
            $serviceName = $domainServiceNamespace;
        } elseif (class_exists($pluginServiceNamespace)) {
            $serviceName = $pluginServiceNamespace;
        } else {
            return $this->returnMethodNotFound("Service doesn't exist: $serviceName", $id);
        }

        if (! method_exists($serviceName, $methodName)) {
            return $this->returnMethodNotFound("Method doesn't exist: $methodName", $id);
        }

        //Check method attributes
        //TODO: Check if method is available for api

        if ($jsonRpcVer == null) {
            return $this->returnInvalidRequest("You must include a \"jsonrpc\" parameter with a value of \"2.0\"", $id);
        }

        if ($jsonRpcVer !== '2.0') {
            return $this->returnInvalidRequest("Leantime only supports JSON-RPC version 2.0", $id);
        }

        try {
            $methodParams = $this->getMethodParameters($serviceName, $methodName);
        } catch (\ReflectionException $e) {
            return $this->returnServerError("Error getting parameters: $e", $id);
        }

        try {
            $preparedParams = $this->prepareParameters($paramsFromRequest, $methodParams);
        } catch (Exception $e) {
            return $this->returnInvalidParams($e, $id);
        }

        // can be null
        try {
            $method_response = app()->make($serviceName)->$methodName(...$preparedParams);
        } catch (Exception $e) {
            return $this->returnServerError($e, $id);
        }

        if ($method_response !== null) {
            if (! settype($method_response, 'array')) {
                $method_response = [$method_response];
            }
        }

        return $this->returnResponse($method_response, $id);
    }

    /**
     * Parses the method string
     *
     * @param string $methodstring - leantime.rpc.service.method
     *
     * @return array
     *
     * @throws Exception
     */
    private function parseMethodString(string $methodstring): array
    {
        if (empty($methodstring)) {
            throw new Exception('Must include method');
        }

        if (!str_starts_with($methodstring, "leantime.rpc.")) {
            throw new Exception("Method string doesn't start with \"leantime.rpc.\"");
        }

        //method parameter breakdown
        //00000000.111.22222222.3333333333333.444444444444
        //leantime.rpc.{module}.{servicename}.{methodname}
        $methodStringPieces = explode('.', $methodstring);

        if (count($methodStringPieces) !== 4 && count($methodStringPieces) !== 5) {
            throw new Exception("Method is case sensitive and must follow the following naming convention: \"leantime.rpc.{domain}.{servicename}.{methodname}\"");
        }

        if (count($methodStringPieces) === 4){
            return [
                'module' => $methodStringPieces[2],
                'service' => $methodStringPieces[2],
                'method' => $methodStringPieces[3],
            ];
        }

        if (count($methodStringPieces) === 5){
            return [
                'module' => $methodStringPieces[2],
                'service' => $methodStringPieces[3],
                'method' => $methodStringPieces[4],
            ];
        }

    }

    /**
     * Gets the Method Parameters
     *
     * @param string $servicename
     * @param string $methodname
     *
     * @return array
     *
     * @throws \ReflectionException
     */
    private function getMethodParameters(string $servicename, string $methodname): array
    {
        return (new ReflectionClass($servicename))
            ->getMethod($methodname)
            ->getParameters();
    }

    /**
     * Checks request params
     *
     * @param array $params
     * @param array $methodParams
     *
     * @return array
     *
     * @throws Exception
     */
    private function prepareParameters(array $params, array $methodParams): array
    {
        $filtered_parameters = [];

        // matches params, params that don't match are ignored
        foreach ($methodParams as $methodParam) {
            $required = !$methodParam->isDefaultValueAvailable();
            $position = $methodParam->getPosition();
            $name = $methodParam->name;
            $type = $methodParam->getType();

            // check if param is there
            if (!in_array($name, array_keys($params))) {
                if ($required) {
                    throw new Exception("Required Parameter Missing: $name");
                }

                $filtered_parameters[$position] = $methodParam->getDefaultValue();
                continue;
            }

            // check if type is correct or can be correct
            if ($methodParam->hasType()) {
                if (in_array($type, [gettype($params[$name]), 'mixed'])) {
                    $filtered_parameters[$position] = $params[$name];
                    continue;
                }

                if ($params[$name] === null && ! $type->allowsNull()) {
                    throw new Exception("Parameter $name can't be null");
                }

                try {
                    $filtered_parameters[$position] = cast($params[$name], $type->getName());
                } catch (\Throwable $e) {
                    report($e);
                    throw new \Exception("Could not cast parameter: $name. See server logs for more details.");
                }
            }

            if (!isset($filtered_parameters[$position])) {
                $filtered_parameters[$position] = $params[$name];
            }
        }

        // make sure it is in the right order
        ksort($filtered_parameters);

        return $filtered_parameters;
    }

    /**
     * Echos the return response
     *
     * @see https://jsonrpc.org/specification#response_object
     *
     * @param array|null  $returnValue
     * @param string|null $id
     *
     * @return Response
     */
    private function returnResponse(array|null $returnValue, string $id = null): Response
    {
        /**
         * No IDs imply notification and MUST not be responded to
         *
         * @see https://jsonrpc.org/specification#notification
         */
        if ($id == null) {
            return new Response();
        }

        return $this->tpl->displayJson([
            'jsonrpc' => '2.0',
            'result' => $returnValue,
            'id' => $id,
        ]);
    }

    /**
     * Return error response
     *
     * @see https://jsonrpc.org/specification#error_object
     *
     * @param string          $errorMessage
     * @param int             $errorcode
     * @param mixed|null      $additional_info
     * @param int|string|null $id
     *
     * @return Response
     */
    private function returnError(string $errorMessage, int $errorcode, mixed $additional_info = null, int|string|null $id = 0): Response
    {

        //TODO: And FYI. json_encode cannot encode throwable. https://github.com/pmjones/throwable-properties
        return $this->tpl->displayJson([
            'jsonrpc' => '2.0',
            'error' => [
                'code' => $errorcode,
                'message' => $errorMessage,
                'data' => $additional_info instanceof \Throwable ? $additional_info->getMessage() : $additional_info,
            ],
            'id' => $id,
        ]);
    }

    /**
     * Returns a parse error
     *
     * @see https://jsonrpc.org/specification#error_object
     *
     * @param mixed|null      $additional_info
     * @param int|string|null $id
     *
     * @return Response
     */
    private function returnParseError(mixed $additional_info = null, int|string|null $id = 0): Response
    {
        return $this->returnError('Parse error', -32700, $additional_info, $id);
    }

    /**
     * Returns an invalid request error
     *
     * @see https://jsonrpc.org/specification#error_object
     *
     * @param mixed|null      $additional_info
     * @param int|string|null $id
     *
     * @return Response
     */
    private function returnInvalidRequest(mixed $additional_info = null, int|string|null $id = 0): Response
    {
        return $this->returnError('Invalid Request', -32600, $additional_info, $id);
    }

    /**
     * Returns a method not found error
     *
     * @see https://jsonrpc.org/specification#error_object
     *
     * @param mixed|null      $additional_info
     * @param int|string|null $id
     *
     * @return Response
     */
    private function returnMethodNotFound(mixed $additional_info = null, int|string|null $id = 0): Response
    {
        return $this->returnError('Method not found', -32601, $additional_info, $id);
    }

    /**
     * Returns an invalid parameters error
     *
     * @see https://jsonrpc.org/specification#error_object
     *
     * @param mixed|null      $additional_info
     * @param int|string|null $id
     *
     * @return Response
     */
    private function returnInvalidParams(mixed $additional_info = null, int|string|null $id = 0): Response
    {
        return $this->returnError('Invalid params', -32602, $additional_info, $id);
    }

    /**
     * Returns a server error
     *
     * @see https://jsonrpc.org/specification#error_object
     *
     * @param mixed|null      $additional_info
     * @param int|string|null $id
     *
     * @return Response
     */
    private function returnServerError(mixed $additional_info, int|string|null $id = 0): Response
    {
        return $this->returnError('Server error', -32000, $additional_info, $id);
    }
}
