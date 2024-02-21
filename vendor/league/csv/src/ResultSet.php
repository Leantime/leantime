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

use CallbackFilterIterator;
use Generator;
use Iterator;
use JsonSerializable;
use LimitIterator;
use function array_flip;
use function array_search;
use function is_string;
use function iterator_count;
use function iterator_to_array;

/**
 * Represents the result set of a {@link Reader} processed by a {@link Statement}.
 */
class ResultSet implements TabularDataReader, JsonSerializable
{
    /** The CSV records collection. */
    protected Iterator $records;
    /** @var array<string> The CSV records collection header. */
    protected array $header = [];

    public function __construct(Iterator $records, array $header)
    {
        $this->validateHeader($header);

        $this->records = $records;
        $this->header = $header;
    }

    /**
     * @throws SyntaxError if the header syntax is invalid
     */
    protected function validateHeader(array $header): void
    {
        if ($header !== ($filtered_header = array_filter($header, 'is_string'))) {
            throw SyntaxError::dueToInvalidHeaderColumnNames();
        }

        if ($header !== array_unique($filtered_header)) {
            throw SyntaxError::dueToDuplicateHeaderColumnNames($header);
        }
    }

    public function __destruct()
    {
        unset($this->records);
    }

    /**
     * Returns a new instance from an object implementing the TabularDataReader interface.
     */
    public static function createFromTabularDataReader(TabularDataReader $reader): self
    {
        return new self($reader->getRecords(), $reader->getHeader());
    }

    /**
     * Returns the header associated with the result set.
     *
     * @return array<string>
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    public function getIterator(): Iterator
    {
        return $this->getRecords();
    }

    public function getRecords(array $header = []): Iterator
    {
        $this->validateHeader($header);
        $records = $this->combineHeader($header);
        foreach ($records as $offset => $value) {
            yield $offset => $value;
        }
    }

    /**
     * Combine the header to each record if present.
     */
    protected function combineHeader(array $header): Iterator
    {
        if ($header === $this->header || [] === $header) {
            return $this->records;
        }

        $field_count = count($header);
        $mapper = static function (array $record) use ($header, $field_count): array {
            if (count($record) != $field_count) {
                $record = array_slice(array_pad($record, $field_count, null), 0, $field_count);
            }

            /** @var array<string|null> $assocRecord */
            $assocRecord = array_combine($header, $record);

            return $assocRecord;
        };

        return new MapIterator($this->records, $mapper);
    }

    public function count(): int
    {
        return iterator_count($this->records);
    }

    public function jsonSerialize(): array
    {
        return iterator_to_array($this->records, false);
    }

    public function fetchOne(int $nth_record = 0): array
    {
        if ($nth_record < 0) {
            throw InvalidArgument::dueToInvalidRecordOffset($nth_record, __METHOD__);
        }

        $iterator = new LimitIterator($this->records, $nth_record, 1);
        $iterator->rewind();

        $result = $iterator->current();
        if (!is_array($result)) {
            return [];
        }

        return $result;
    }

    /**
     * @throws Exception
     */
    public function fetchColumnByName(string $name): Iterator
    {
        return $this->yieldColumn(
            $this->getColumnIndexByValue($name, 'name', __METHOD__)
        );
    }

    /**
     * @throws Exception
     */
    public function fetchColumnByOffset(int $offset): Iterator
    {
        return $this->yieldColumn(
            $this->getColumnIndexByKey($offset, 'offset', __METHOD__)
        );
    }

    public function fetchColumn($index = 0): Iterator
    {
        return $this->yieldColumn(
            $this->getColumnIndex($index, 'offset', __METHOD__)
        );
    }

    /**
     * @param string|int $offset
     */
    protected function yieldColumn($offset): Generator
    {
        $iterator = new MapIterator(
            new CallbackFilterIterator($this->records, fn (array $record): bool => isset($record[$offset])),
            fn (array $record): string => $record[$offset]
        );

        foreach ($iterator as $key => $value) {
            yield $key => $value;
        }
    }

    /**
     * Filter a column name against the header if any.
     *
     * @param string|int $field the field name or the field index
     *
     * @throws InvalidArgument if the field is invalid or not found
     *
     * @return string|int
     */
    protected function getColumnIndex($field, string $type, string $method)
    {
        if (is_string($field)) {
            return $this->getColumnIndexByValue($field, $type, $method);
        }

        return $this->getColumnIndexByKey($field, $type, $method);
    }

    /**
     * Returns the selected column name.
     *
     * @throws InvalidArgument if the column is not found
     */
    protected function getColumnIndexByValue(string $value, string $type, string $method): string
    {
        if (false === array_search($value, $this->header, true)) {
            throw InvalidArgument::dueToInvalidColumnIndex($value, $type, $method);
        }

        return $value;
    }

    /**
     * Returns the selected column name according to its offset.
     *
     * @throws InvalidArgument if the field is invalid or not found
     *
     * @return int|string
     */
    protected function getColumnIndexByKey(int $index, string $type, string $method)
    {
        if ($index < 0) {
            throw InvalidArgument::dueToInvalidColumnIndex($index, $type, $method);
        }

        if ([] === $this->header) {
            return $index;
        }

        $value = array_search($index, array_flip($this->header), true);
        if (false === $value) {
            throw InvalidArgument::dueToInvalidColumnIndex($index, $type, $method);
        }

        return $value;
    }

    public function fetchPairs($offset_index = 0, $value_index = 1): Iterator
    {
        $offset = $this->getColumnIndex($offset_index, 'offset', __METHOD__);
        $value = $this->getColumnIndex($value_index, 'value', __METHOD__);

        $iterator = new MapIterator(
            new CallbackFilterIterator($this->records, fn (array $record): bool => isset($record[$offset])),
            fn (array $record): array => [$record[$offset], $record[$value] ?? null]
        );

        /** @var array{0:int|string, 1:string|null} $pair */
        foreach ($iterator as $pair) {
            yield $pair[0] => $pair[1];
        }
    }
}
