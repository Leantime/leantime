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

use Countable;
use Iterator;
use IteratorAggregate;

/**
 * Represents a Tabular data.
 *
 * @method Iterator fetchColumnByName(string $name)  returns a column from its name
 * @method Iterator fetchColumnByOffset(int $offset) returns a column from its offset
 */
interface TabularDataReader extends Countable, IteratorAggregate
{
    /**
     * Returns the number of records contained in the tabular data structure
     * excluding the header record.
     */
    public function count(): int;

    /**
     * Returns the tabular data records as an iterator object.
     *
     * Each record is represented as a simple array containing strings or null values.
     *
     * If the CSV document has a header record then each record is combined
     * to the header record and the header record is removed from the iterator.
     *
     * If the CSV document is inconsistent. Missing record fields are
     * filled with null values while extra record fields are strip from
     * the returned object.
     */
    public function getIterator(): Iterator;

    /**
     * Returns the header associated with the tabular data.
     *
     * The header must contains unique string or is an empty array
     * if no header was specified.
     *
     * @return array<string>
     */
    public function getHeader(): array;

    /**
     * Returns the tabular data records as an iterator object.
     *
     * Each record is represented as a simple array containing strings or null values.
     *
     * If the tabular data has a header record then each record is combined
     * to the header record and the header record is removed from the iterator.
     *
     * If the tabular data is inconsistent. Missing record fields are
     * filled with null values while extra record fields are strip from
     * the returned object.
     *
     * @param array<string> $header an optional header to use instead of the CSV document header
     */
    public function getRecords(array $header = []): Iterator;

    /**
     * Returns the nth record from the tabular data.
     *
     * By default if no index is provided the first record of the tabular data is returned
     *
     * @param int $nth_record the tabular data record offset
     *
     * @throws UnableToProcessCsv if argument is lesser than 0
     */
    public function fetchOne(int $nth_record = 0): array;

    /**
     * DEPRECATION WARNING! This class will be removed in the next major point release.
     *
     * @deprecated since version 9.8.0
     *
     * @see ::fetchColumnByName
     * @see ::fetchColumnByOffset
     *
     * Returns a single column from the next record of the tabular data.
     *
     * By default if no value is supplied the first column is fetch
     *
     * @param string|int $index CSV column index
     *
     * @throws UnableToProcessCsv if the column index is invalid or not found
     */
    public function fetchColumn($index = 0): Iterator;

    /**
     * Returns the next key-value pairs from the tabular data (first
     * column is the key, second column is the value).
     *
     * By default if no column index is provided:
     * - the first column is used to provide the keys
     * - the second column is used to provide the value
     *
     * @param string|int $offset_index The column index to serve as offset
     * @param string|int $value_index  The column index to serve as value
     *
     * @throws UnableToProcessCsv if the column index is invalid or not found
     */
    public function fetchPairs($offset_index = 0, $value_index = 1): Iterator;
}
