<?php

namespace kamermans\OAuth2\Utils;

use GuzzleHttp\ClientInterface as G;

class Helper
{
    /**
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * @param resource|string|null|int|float|bool|StreamInterface|callable|\Iterator $resource Entity body data
     * @param array                                                                  $options  Additional options
     *
     * @return StreamInterface
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    public static function streamFor($resource = '', array $options = [])
    {
        // stream_for was used until GuzzleHttp\Psr7 v1.7.0
        if (function_exists('GuzzleHttp\Psr7\stream_for')) {
            return \GuzzleHttp\Psr7\stream_for($resource, $options);
        }

        return \GuzzleHttp\Psr7\Utils::streamFor($resource, $options);
    }

    public static function guzzleIs($operator, $version, $guzzle_version=null)
    {
        if ($guzzle_version === null) {
            $guzzle_version = (defined('GuzzleHttp\ClientInterface::VERSION')) ? G::VERSION : G::MAJOR_VERSION;
        }

        // version_compare considers 5.1.0 > 5.1, but I don't
        $guzzle_version = preg_replace('/(\.0+)+$/', '', $guzzle_version);
        $version = preg_replace('/(\.0+)+$/', '', $version);

        if ($operator === '~') {
            return self::fuzzyVersionCompare($version, $guzzle_version);
        }

        return version_compare($guzzle_version, $version, $operator);
    }

    private static function fuzzyVersionCompare($version, $guzzle_version)
    {
        $num_version_segments = substr_count($version, '.') + 1;
        $num_guzzle_segments = substr_count($guzzle_version, '.') + 1;

        if ($num_version_segments < $num_guzzle_segments) {
            // Shorten Guzzle version
            $guzzle_version = implode('.', array_slice(explode('.', $guzzle_version), 0, $num_version_segments));
        } elseif ($num_version_segments > $num_guzzle_segments) {
            // Shorten Test version
            $version = implode('.', array_slice(explode('.', $version), 0, $num_guzzle_segments));
        }

        return version_compare($guzzle_version, $version, '==');
    }
}
