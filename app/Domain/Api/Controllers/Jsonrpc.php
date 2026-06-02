<?php

/**
 * Generates an JSON-RPC 2.0 API
 */

namespace Leantime\Domain\Api\Controllers;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Exceptions\Contracts\LeantimeExceptionInterface;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Core\Http\Responses\JsonRpcErrorResponse;
use Leantime\Core\Http\Responses\JsonRpcResponse;
use Leantime\Core\Plugins\Attributes\RequiresPlugin;
use Leantime\Core\Plugins\Plugins as CorePlugins;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Response;

class Jsonrpc extends Controller
{
    /**
     * @var array - holds json data from request body
     */
    private array $json_data = [];

    /**
     * init - initialize private variables or events to happen before route execution
     */
    public function init(): void {}

    /**
     * Handles post requests
     *
     * @param  array  $params  - value of $_POST
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function post(array $params): Response
    {

        // Remove act from params array
        if (isset($params['act'])) {
            unset($params['act']);
        }

        // If params is empty, maybe it was in the body, get body
        if (empty($params)) {

            try {
                $params = $this->getJsonFromBody();
            } catch (MissingParameterException $e) {
                Log::error($e);

                return $this->returnMethodNotFound('Could not get any parameters from body');
            } catch (\JsonException $e) {
                Log::error($e);

                return $this->returnParseError('Could not parse JSON. Error '.$e->getMessage());
            }

        }

        // params['params'] could be array (single value) or json object
        if (isset($params['params'])) {
            if (! is_array($params['params'])) {
                $params['params'] = json_decode($params['params'], JSON_OBJECT_AS_ARRAY);
            }
        }

        return $this->executeApiRequest($params);
    }

    /**
     * Handles get requests
     *
     * @param  array  $params  - value of $_GET
     *
     * @throws BindingResolutionException
     * @throws \ReflectionException
     */
    public function get(array $params): Response
    {
        if (! isset($params['method'])) {
            return $this->returnInvalidRequest('Method name required');
        }

        /**
         * Decode params
         *
         * @see https://www.jsonrpc.org/historical/json-rpc-over-http.html#get
         */
        if (isset($params['params'])) {
            $paramsDecoded = base64_decode(urldecode($params['params']));
        } else {
            $paramsDecoded = [];
        }

        $params = [
            'method' => $params['method'],
            'params' => $paramsDecoded,
            'id' => $params['id'] ?? null,
            'jsonrpc' => $params['jsonrpc'] ?? '',
        ];

        $params['params'] = json_decode($params['params'], JSON_OBJECT_AS_ARRAY);

        // check if decode failed
        if ($params == null) {
            return $this->returnParseError('JSON is invalid and was not able to be parsed');
        }

        return $this->executeApiRequest($params);
    }

    private function getJsonFromBody(): array
    {

        if ($this->incomingRequest->server('REQUEST_METHOD') === 'POST'
            && empty($_POST)
            && $this->incomingRequest->getContent() !== null
            && $this->incomingRequest->getContent() !== false
            && $this->incomingRequest->getContent() !== '') {

            $bodyContent = json_decode(
                json: $this->incomingRequest->getContent(),
                associative: JSON_OBJECT_AS_ARRAY,
                flags: JSON_THROW_ON_ERROR
            );

            return $bodyContent;
        }

        throw new MissingParameterException('Could not get JSON from body or form fields');
    }

    /**
     * Handles patch requests
     */
    public function patch(): Response
    {
        return $this->returnInvalidRequest('The JSON-RPC API only supports POST/GET requests');
    }

    /**
     * Handles delete requests
     */
    public function delete(): Response
    {
        return $this->returnInvalidRequest('The JSON-RPC API only supports POST/GET requests');
    }

    /**
     * executes api call
     *
     * @param  array  $params  - request body
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

            return new JsonResponse(array_map(
                function ($requestParams) {
                    return json_decode($this->executeApiRequest($requestParams)->getContent());
                },
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

        $domainServiceNamespace = app()->getNamespace()."Domain\\$moduleName\\Services\\$serviceName";
        $pluginServiceNamespace = app()->getNamespace()."Plugins\\$moduleName\\Services\\$serviceName";

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

        // Only allow methods explicitly marked with @api annotation
        if (! $this->isApiMethod($serviceName, $methodName)) {
            return $this->returnMethodNotFound("Method is not available via API: $methodName", $id);
        }

        // Enforce plugin-gated methods. Methods or classes carrying #[RequiresPlugin('Name')]
        // refuse to dispatch when the named plugin is disabled — return a JSON-RPC error
        // with HTTP 200 body, mirroring the returnMethodNotFound pattern above.
        $requiredPlugin = $this->getRequiredPlugin($serviceName, $methodName);
        if ($requiredPlugin !== null && ! app()->make(CorePlugins::class)->isPluginEnabled($requiredPlugin)) {
            return $this->returnError(
                "Plugin '$requiredPlugin' is required but not enabled.",
                -32004,
                null,
                $id
            );
        }

        if ($jsonRpcVer == null) {
            return $this->returnInvalidRequest('You must include a "jsonrpc" parameter with a value of "2.0"', $id);
        }

        if ($jsonRpcVer !== '2.0') {
            return $this->returnInvalidRequest('Leantime only supports JSON-RPC version 2.0', $id);
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
        } catch (\Throwable $e) {
            // Leantime exceptions carry a client-safe code/message/data and map to a precise
            // JSON-RPC error. Anything else is an unexpected failure that must be logged and
            // collapsed to a generic server error so internal detail never reaches the caller.
            if (! $e instanceof LeantimeExceptionInterface) {
                Log::error($e);
            }

            // A notification (no id) must not be responded to, even on failure, per the
            // JSON-RPC 2.0 spec — mirror the success path's empty 200.
            if ($id === null) {
                return new Response('', Response::HTTP_OK);
            }

            return JsonRpcErrorResponse::fromException($e, $id)->toResponse($this->incomingRequest);
        }

        // Convert objects to associative arrays for JSON serialization, but pass
        // scalars and arrays through as-is. The previous `settype($var, 'array')`
        // coerced scalars to `[$scalar]`, which broke RPC methods returning ints
        // (e.g., addTicket returning a new ticket ID).
        if ($method_response !== null && is_object($method_response)) {
            $method_response = (array) $method_response;
        }

        return $this->returnResponse($method_response, $id);
    }

    /**
     * Parses the method string
     *
     * @param  string  $methodstring  - leantime.rpc.service.method
     *
     * @throws Exception
     */
    private function parseMethodString(string $methodstring): array
    {
        if (empty($methodstring)) {
            throw new Exception('Must include method');
        }

        if (! str_starts_with($methodstring, 'leantime.rpc.')) {
            throw new Exception("Method string doesn't start with \"leantime.rpc.\"");
        }

        // method parameter breakdown
        // 00000000.111.22222222.3333333333333.444444444444
        // leantime.rpc.{module}.{servicename}.{methodname}
        $methodStringPieces = explode('.', $methodstring);

        if (count($methodStringPieces) !== 4 && count($methodStringPieces) !== 5) {
            throw new Exception('Method is case sensitive and must follow the following naming convention: "leantime.rpc.{domain}.{servicename}.{methodname}"');
        }

        if (count($methodStringPieces) === 4) {
            return [
                'module' => $methodStringPieces[2],
                'service' => $methodStringPieces[2],
                'method' => $methodStringPieces[3],
            ];
        }

        if (count($methodStringPieces) === 5) {
            return [
                'module' => $methodStringPieces[2],
                'service' => $methodStringPieces[3],
                'method' => $methodStringPieces[4],
            ];
        }

    }

    /**
     * Checks if a service method is marked with the @api annotation.
     *
     * @param  string  $serviceName  Fully qualified class name
     * @param  string  $methodName  Method name
     * @return bool True if the method has an @api docblock tag
     */
    private function isApiMethod(string $serviceName, string $methodName): bool
    {
        try {
            $reflection = new ReflectionMethod($serviceName, $methodName);
            $docComment = $reflection->getDocComment();

            if ($docComment === false) {
                return false;
            }

            return (bool) preg_match('/@api\b/', $docComment);
        } catch (\ReflectionException $e) {
            return false;
        }
    }

    /**
     * Resolve the plugin name a method (or its declaring class) requires, if any.
     *
     * Looks for the RequiresPlugin attribute on the method first, then the class.
     * Method-level wins over class-level.
     *
     * @return string|null The required plugin folder name, or null if not gated
     */
    private function getRequiredPlugin(string $serviceName, string $methodName): ?string
    {
        try {
            $method = new ReflectionMethod($serviceName, $methodName);
            $attrs = $method->getAttributes(RequiresPlugin::class);
            if (! empty($attrs)) {
                return $attrs[0]->newInstance()->pluginName;
            }

            $class = new ReflectionClass($serviceName);
            $classAttrs = $class->getAttributes(RequiresPlugin::class);
            if (! empty($classAttrs)) {
                return $classAttrs[0]->newInstance()->pluginName;
            }
        } catch (\ReflectionException $e) {
            return null;
        }

        return null;
    }

    /**
     * Gets the Method Parameters
     *
     *
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
     *
     *
     * @throws Exception
     */
    private function prepareParameters(array $params, array $methodParams): array
    {
        $filtered_parameters = [];

        // matches params, params that don't match are ignored
        foreach ($methodParams as $methodParam) {
            $required = ! $methodParam->isDefaultValueAvailable();
            $position = $methodParam->getPosition();
            $name = $methodParam->name;
            $type = $methodParam->getType();

            // check if param is there
            if (! in_array($name, array_keys($params))) {
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
                    Log::error($e);
                    throw new \Exception("Could not cast parameter: $name. See server logs for more details.");
                }
            }

            if (! isset($filtered_parameters[$position])) {
                $filtered_parameters[$position] = $params[$name];
            }
        }

        // make sure it is in the right order
        ksort($filtered_parameters);

        return $filtered_parameters;
    }

    /**
     * Echos the return response.
     *
     * @param  mixed  $returnValue  The return value from the RPC method. Widened from
     *                              `?array` because the upstream `settype` coercion that
     *                              wrapped scalars into single-element arrays was removed
     *                              (it broke methods returning ints — e.g. addTicket's
     *                              new ticket id was being delivered as [id]). Per the
     *                              JSON-RPC 2.0 spec §5, `result` MAY be any JSON value;
     *                              caller code in `executeRPC` already casts objects to
     *                              associative arrays before reaching here, so in practice
     *                              this is array|scalar|null.
     *
     * @see https://jsonrpc.org/specification#response_object
     */
    private function returnResponse(mixed $returnValue, int|string|null $id = null): Response
    {
        return (new JsonRpcResponse($returnValue, $id))->toResponse($this->incomingRequest);
    }

    /**
     * Return error response
     *
     * @see https://jsonrpc.org/specification#error_object
     */
    private function returnError(string $errorMessage, int $errorcode, mixed $additional_info = null, int|string|null $id = 0): Response
    {
        // Protocol-level callers (parse / invalid-request / method-not-found / invalid-params)
        // may pass their own thrown exception for context; surface only its message. Service-
        // level exceptions never reach here — they go through JsonRpcErrorResponse::fromException().
        $data = $additional_info instanceof \Throwable ? $additional_info->getMessage() : $additional_info;

        return (new JsonRpcErrorResponse($errorcode, $errorMessage, $data, $id))->toResponse($this->incomingRequest);
    }

    /**
     * Returns a parse error
     *
     * @see https://jsonrpc.org/specification#error_object
     */
    private function returnParseError(mixed $additional_info = null, int|string|null $id = 0): Response
    {
        return $this->returnError('Parse error', -32700, $additional_info, $id);
    }

    /**
     * Returns an invalid request error
     *
     * @see https://jsonrpc.org/specification#error_object
     */
    private function returnInvalidRequest(mixed $additional_info = null, int|string|null $id = 0): Response
    {
        return $this->returnError('Invalid Request', -32600, $additional_info, $id);
    }

    /**
     * Returns a method not found error
     *
     * @see https://jsonrpc.org/specification#error_object
     */
    private function returnMethodNotFound(mixed $additional_info = null, int|string|null $id = 0): Response
    {
        return $this->returnError('Method not found', -32601, $additional_info, $id);
    }

    /**
     * Returns an invalid parameters error
     *
     * @see https://jsonrpc.org/specification#error_object
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
     * @param  mixed|null  $additional_info
     */
    private function returnServerError(mixed $additional_info, int|string|null $id = 0): Response
    {
        return $this->returnError('Server error', -32000, $additional_info, $id);
    }
}
