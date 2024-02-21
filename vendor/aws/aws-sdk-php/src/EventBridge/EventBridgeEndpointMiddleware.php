<?php
namespace Aws\EventBridge;

use Aws\CommandInterface;
use Aws\Endpoint\EndpointProvider;
use Aws\Endpoint\PartitionEndpointProvider;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;

/**
 * Reroutes an eventbridge request to the proper endpoint
 * @internal
 */
class EventBridgeEndpointMiddleware
{
    private $nextHandler;
    private $region;
    private $config;
    private $endpointProvider;
    private $isCustomEndpoint;

    /**
     * Provide the URI scheme of the client sending requests.
     * @param EndpointProvider $endpointProvider
     * @return callable
     */
    public static function wrap($region, $config, $endpointProvider, $isCustomEndpoint)
    {
        return function (callable $handler) use (
            $region,
            $config,
            $endpointProvider,
            $isCustomEndpoint
        ) {
            return new self(
                $handler,
                $region,
                $config,
                $endpointProvider,
                $isCustomEndpoint
            );
        };
    }

    public function __construct(
        callable $nextHandler,
        $region,
        $config,
        $endpointProvider,
        $isCustomEndpoint
    ) {
        $this->nextHandler = $nextHandler;
        $this->region = $region;
        $this->config = $config;
        $this->endpointProvider = is_null($endpointProvider)
            ? PartitionEndpointProvider::defaultProvider()
            : $endpointProvider;
        $this->isCustomEndpoint = $isCustomEndpoint;
    }

    public function __invoke(CommandInterface $cmd, RequestInterface $req) {
        $sigV4aCommands = ['PutEvents'];
        if (in_array($cmd->getName(), $sigV4aCommands)) {
            if (isset($cmd['EndpointId'])) {
                $endpointID = $cmd['EndpointId'];
                $this->validateEndpointId($endpointID);
                if (!$this->isCustomEndpoint) {
                    $dnsSuffix = $this->endpointProvider
                        ->getPartition($this->region, 'eventbridge')
                        ->getDnsSuffix();
                    $newUri = "{$endpointID}.endpoint.events.{$dnsSuffix}";
                    $oldUri = $req->getUri();
                    $req = $req->withUri($oldUri->withHost($newUri));
                }
                $cmd['@context']['signature_version'] = 'v4a';
            }
        }
        $f = $this->nextHandler;
        return $f($cmd, $req);
    }

    protected static function isValidHostLabel($string)
    {
        if (empty($string) || strlen($string) > 63) {
            return false;
        }
        if ($value = preg_match("/^[a-zA-Z0-9-.]+$/", $string)) {
            return true;
        }
        return false;
    }

    /**
     * @param $endpointID
     * @param CommandInterface $cmd
     */
    private function validateEndpointId($endpointID)
    {
        if (empty($endpointID)) {
            throw new \InvalidArgumentException("EventId must be a non-empty string");
        }
        if (!self::isValidHostLabel($endpointID)) {
            throw new InvalidArgumentException("EventId must be a valid host");
        }
        if ($this->config['use_fips_endpoint']) {
            throw new InvalidArgumentException(
                "EventId is currently not compatible with FIPS pseudo regions"
            );
        }
        if ($this->config['dual_stack']) {
            throw new InvalidArgumentException(
                "EventId is currently not compatible with dualstack"
            );
        }
    }
}
