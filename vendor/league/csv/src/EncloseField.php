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

use InvalidArgumentException;
use php_user_filter;
use function array_map;
use function in_array;
use function str_replace;
use function strcspn;
use function stream_bucket_append;
use function stream_bucket_make_writeable;
use function stream_filter_register;
use function stream_get_filters;
use function strlen;

/**
 * A stream filter to improve enclosure character usage.
 *
 * @see https://tools.ietf.org/html/rfc4180#section-2
 * @see https://bugs.php.net/bug.php?id=38301
 */
class EncloseField extends php_user_filter
{
    const FILTERNAME = 'convert.league.csv.enclosure';

    /** Default sequence. */
    protected string $sequence;
    /** Characters that triggers enclosure in PHP. */
    protected static string $force_enclosure = "\n\r\t ";

    /**
     * Static method to return the stream filter filtername.
     */
    public static function getFiltername(): string
    {
        return self::FILTERNAME;
    }

    /**
     * Static method to register the class as a stream filter.
     */
    public static function register(): void
    {
        if (!in_array(self::FILTERNAME, stream_get_filters(), true)) {
            stream_filter_register(self::FILTERNAME, self::class);
        }
    }

    /**
     * Static method to add the stream filter to a {@link Writer} object.
     *
     * @throws InvalidArgumentException if the sequence is malformed
     * @throws Exception
     */
    public static function addTo(Writer $csv, string $sequence): Writer
    {
        self::register();

        if (!self::isValidSequence($sequence)) {
            throw new InvalidArgumentException('The sequence must contain at least one character to force enclosure');
        }

        return $csv
            ->addFormatter(fn (array $record): array => array_map(fn (?string $value): string => $sequence.$value, $record))
            ->addStreamFilter(self::FILTERNAME, ['sequence' => $sequence]);
    }

    /**
     * Filter type and sequence parameters.
     *
     * The sequence to force enclosure MUST contains one of the following character ("\n\r\t ")
     */
    protected static function isValidSequence(string $sequence): bool
    {
        return strlen($sequence) != strcspn($sequence, self::$force_enclosure);
    }

    public function onCreate(): bool
    {
        return isset($this->params['sequence'])
            && self::isValidSequence($this->params['sequence']);
    }

    /**
     * @param resource $in
     * @param resource $out
     * @param int      $consumed
     * @param bool     $closing
     */
    public function filter($in, $out, &$consumed, $closing): int
    {
        while (null !== ($bucket = stream_bucket_make_writeable($in))) {
            $bucket->data = str_replace($this->params['sequence'], '', $bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return PSFS_PASS_ON;
    }
}
