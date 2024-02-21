<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Csv;

use Throwable;

/**
 * InvalidArgument Exception.
 */
class InvalidArgument extends Exception
{
    /**
     * DEPRECATION WARNING! This class will be removed in the next major point release.
     *
     * @deprecated since version 9.7.0
     */
    public function __construct(string $message = '', int $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public static function dueToInvalidChunkSize(int $length, string $method): self
    {
        return new self($method.'() expects the length to be a positive integer '.$length.' given.');
    }

    public static function dueToInvalidHeaderFilename(string $filename): self
    {
        return new self('The filename `'.$filename.'` cannot contain the "/" and "\\" characters.');
    }

    public static function dueToInvalidDelimiterCharacter(string $delimiter, string $method): self
    {
        return new self($method.'() expects delimiter to be a single character; `'.$delimiter.'` given.');
    }

    public static function dueToInvalidEnclosureCharacter(string $enclosure, string $method): self
    {
        return new self($method.'() expects enclosure to be a single character; `'.$enclosure.'` given.');
    }

    public static function dueToInvalidEscapeCharacter(string $escape, string $method): self
    {
        return new self($method.'() expects escape to be a single character  or the empty string; `'.$escape.'` given.');
    }

    public static function dueToInvalidColumnCount(int $columns_count, string $method): self
    {
        return new self($method.'() expects the column count to be greater or equal to -1 '.$columns_count.' given.');
    }

    public static function dueToInvalidHeaderOffset(int $offset, string $method): self
    {
        return new self($method.'() expects header offset to be greater or equal to 0; `'.$offset.'` given.');
    }

    public static function dueToInvalidRecordOffset(int $offset, string $method): self
    {
        return new self($method.'() expects the submitted offset to be a positive integer or 0, '.$offset.' given');
    }

    /**
     * @param string|int $index
     */
    public static function dueToInvalidColumnIndex($index, string $type, string $method): self
    {
        return new self($method.'() expects the '.$type.' index to be a valid string or integer, `'.$index.'` given');
    }

    public static function dueToInvalidLimit(int $limit, string $method): self
    {
        return new self($method.'() expects the limit to be greater or equal to -1, '.$limit.' given.');
    }

    public static function dueToInvalidSeekingPosition(int $position, string $method): self
    {
        return new self($method.'() can\'t seek stream to negative line '.$position);
    }

    public static function dueToStreamFilterNotFound(string $filtername): self
    {
        return new self('unable to locate filter `'.$filtername.'`');
    }

    public static function dueToInvalidThreshold(int $threshold, string $method): self
    {
        return new self($method.'() expects threshold to be null or a valid integer greater or equal to 1');
    }
}
