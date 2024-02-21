<?php
namespace Aws\S3;

use Aws\Api\Service;
use Aws\CommandInterface;
use GuzzleHttp\Psr7;
use InvalidArgumentException;
use Psr\Http\Message\RequestInterface;

/**
 * Apply required or optional checksums to requests before sending.
 *
 * IMPORTANT: This middleware must be added after the "build" step.
 *
 * @internal
 */
class ApplyChecksumMiddleware
{
    use CalculatesChecksumTrait;
    private static $sha256 = [
        'PutObject',
        'UploadPart',
    ];

    /** @var Service */
    private $api;

    private $nextHandler;

    /**
     * Create a middleware wrapper function.
     *
     * @param Service $api
     * @return callable
     */
    public static function wrap(Service $api)
    {
        return function (callable $handler) use ($api) {
            return new self($handler, $api);
        };
    }

    public function __construct(callable $nextHandler, Service $api)
    {
        $this->api = $api;
        $this->nextHandler = $nextHandler;
    }

    public function __invoke(
        CommandInterface $command,
        RequestInterface $request
    ) {
        $next = $this->nextHandler;
        $name = $command->getName();
        $body = $request->getBody();

        $op = $this->api->getOperation($command->getName());

        $checksumInfo = isset($op['httpChecksum'])
            ? $op['httpChecksum']
            : [];
        $checksumMemberName = array_key_exists('requestAlgorithmMember', $checksumInfo)
            ? $checksumInfo['requestAlgorithmMember']
            : "";
        $requestedAlgorithm = isset($command[$checksumMemberName])
            ? $command[$checksumMemberName]
            : null;
        if (!empty($checksumMemberName) && !empty($requestedAlgorithm)) {
            $requestedAlgorithm = strtolower($requestedAlgorithm);
            $checksumMember = $op->getInput()->getMember($checksumMemberName);
            $supportedAlgorithms = isset($checksumMember['enum'])
                ? array_map('strtolower', $checksumMember['enum'])
                : null;
            if (is_array($supportedAlgorithms)
                && in_array($requestedAlgorithm, $supportedAlgorithms)
            ) {
                $headerName = "x-amz-checksum-{$requestedAlgorithm}";
                $encoded = $this->getEncodedValue($requestedAlgorithm, $body);
                if (!$request->hasHeader($headerName)) {
                    $request = $request->withHeader($headerName, $encoded);
                }
            } else {
                throw new InvalidArgumentException(
                    "Unsupported algorithm supplied for input variable {$checksumMemberName}."
                    . "  Supported checksums for this operation include: "
                    . implode(", ", $supportedAlgorithms) . "."
                );
            }
            return $next($command, $request);
        }

        if (!empty($checksumInfo)) {
        //if the checksum member is absent, check if it's required
        $checksumRequired = isset($checksumInfo['requestChecksumRequired'])
            ? $checksumInfo['requestChecksumRequired']
            : null;
            if (!empty($checksumRequired) && !$request->hasHeader('Content-MD5')) {
                // Set the content MD5 header for operations that require it.
                $request = $request->withHeader(
                    'Content-MD5',
                    base64_encode(Psr7\Utils::hash($body, 'md5', true))
                );
                return $next($command, $request);
            }
        }

        if (in_array($name, self::$sha256) && $command['ContentSHA256']) {
            // Set the content hash header if provided in the parameters.
            $request = $request->withHeader(
                'X-Amz-Content-Sha256',
                $command['ContentSHA256']
            );
        }

        return $next($command, $request);
    }
}
