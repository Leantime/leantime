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
use Iterator;
use JsonSerializable;
use SplFileObject;
use function array_combine;
use function array_filter;
use function array_pad;
use function array_slice;
use function array_unique;
use function count;
use function is_array;
use function iterator_count;
use function iterator_to_array;
use function mb_strlen;
use function mb_substr;
use function strlen;
use function substr;
use const STREAM_FILTER_READ;

/**
 * A class to parse and read records from a CSV document.
 */
class Reader extends AbstractCsv implements TabularDataReader, JsonSerializable
{
    protected const STREAM_FILTER_MODE = STREAM_FILTER_READ;

    protected ?int $header_offset = null;
    protected int $nb_records = -1;
    protected bool $is_empty_records_included = false;
    /** @var array<string> header record. */
    protected array $header = [];

    public static function createFromPath(string $path, string $open_mode = 'r', $context = null)
    {
        return parent::createFromPath($path, $open_mode, $context);
    }

    protected function resetProperties(): void
    {
        $this->nb_records = -1;
        $this->header = [];
    }

    /** Returns the header offset. */
    public function getHeaderOffset(): ?int
    {
        return $this->header_offset;
    }

    public function getHeader(): array
    {
        if (null === $this->header_offset) {
            return $this->header;
        }

        if ([] !== $this->header) {
            return $this->header;
        }

        $this->header = $this->setHeader($this->header_offset);

        return $this->header;
    }

    /**
     * Determine the CSV record header.
     *
     * @throws Exception If the header offset is set and no record is found or is the empty array
     *
     * @return array<string>
     */
    protected function setHeader(int $offset): array
    {
        $header = $this->seekRow($offset);
        if (in_array($header, [[], [null]], true)) {
            throw SyntaxError::dueToHeaderNotFound($offset);
        }

        if (0 !== $offset) {
            return $header;
        }

        $header = $this->removeBOM($header, mb_strlen($this->getInputBOM()), $this->enclosure);
        if ([''] === $header) {
            throw SyntaxError::dueToHeaderNotFound($offset);
        }

        return $header;
    }

    /** Returns the row at a given offset. */
    protected function seekRow(int $offset): array
    {
        foreach ($this->getDocument() as $index => $record) {
            if ($offset === $index) {
                return $record;
            }
        }

        return [];
    }

    /**
     * Returns the document as an Iterator.
     */
    protected function getDocument(): Iterator
    {
        $this->document->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD);
        $this->document->setCsvControl($this->delimiter, $this->enclosure, $this->escape);
        $this->document->rewind();

        return $this->document;
    }

    /**
     * Strip the BOM sequence from a record.
     *
     * @param string[] $record
     *
     * @return array<string>
     */
    protected function removeBOM(array $record, int $bom_length, string $enclosure): array
    {
        if (0 === $bom_length) {
            return $record;
        }

        $record[0] = mb_substr($record[0], $bom_length);
        if ($enclosure.$enclosure != substr($record[0].$record[0], strlen($record[0]) - 1, 2)) {
            return $record;
        }

        $record[0] = substr($record[0], 1, -1);

        return $record;
    }

    public function fetchColumnByName(string $name): Iterator
    {
        return ResultSet::createFromTabularDataReader($this)->fetchColumnByName($name);
    }

    public function fetchColumnByOffset(int $offset = 0): Iterator
    {
        return ResultSet::createFromTabularDataReader($this)->fetchColumnByOffset($offset);
    }

    public function fetchColumn($index = 0): Iterator
    {
        return ResultSet::createFromTabularDataReader($this)->fetchColumn($index);
    }

    public function fetchOne(int $nth_record = 0): array
    {
        return ResultSet::createFromTabularDataReader($this)->fetchOne($nth_record);
    }

    public function fetchPairs($offset_index = 0, $value_index = 1): Iterator
    {
        return ResultSet::createFromTabularDataReader($this)->fetchPairs($offset_index, $value_index);
    }

    public function count(): int
    {
        if (-1 === $this->nb_records) {
            $this->nb_records = iterator_count($this->getRecords());
        }

        return $this->nb_records;
    }

    public function getIterator(): Iterator
    {
        return $this->getRecords();
    }

    public function jsonSerialize(): array
    {
        return iterator_to_array($this->getRecords(), false);
    }

    public function getRecords(array $header = []): Iterator
    {
        $header = $this->computeHeader($header);
        $normalized = fn ($record): bool => is_array($record) && ($this->is_empty_records_included || $record != [null]);

        $bom = '';
        if (!$this->is_input_bom_included) {
            $bom = $this->getInputBOM();
        }

        $document = $this->getDocument();
        $records = $this->stripBOM(new CallbackFilterIterator($document, $normalized), $bom);
        if (null !== $this->header_offset) {
            $records = new CallbackFilterIterator($records, fn (array $record, int $offset): bool => $offset !== $this->header_offset);
        }

        if ($this->is_empty_records_included) {
            return $this->combineHeader(new MapIterator(
                $records,
                fn (array $record): array => ([null] === $record) ? [] : $record
            ), $header);
        }

        return $this->combineHeader($records, $header);
    }

    /**
     * Returns the header to be used for iteration.
     *
     * @param string[] $header
     *
     * @throws Exception If the header contains non unique column name
     *
     * @return array<string>
     */
    protected function computeHeader(array $header)
    {
        if ([] === $header) {
            $header = $this->getHeader();
        }

        if ($header !== ($filtered_header = array_filter($header, 'is_string'))) {
            throw SyntaxError::dueToInvalidHeaderColumnNames();
        }

        if ($header !== array_unique($filtered_header)) {
            throw SyntaxError::dueToDuplicateHeaderColumnNames($header);
        }

        return $header;
    }

    /**
     * Combine the CSV header to each record if present.
     *
     * @param string[] $header
     */
    protected function combineHeader(Iterator $iterator, array $header): Iterator
    {
        if ([] === $header) {
            return $iterator;
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

        return new MapIterator($iterator, $mapper);
    }

    /**
     * Strip the BOM sequence from the returned records if necessary.
     */
    protected function stripBOM(Iterator $iterator, string $bom): Iterator
    {
        if ('' === $bom) {
            return $iterator;
        }

        $bom_length = mb_strlen($bom);
        $mapper = function (array $record, int $index) use ($bom_length): array {
            if (0 !== $index) {
                return $record;
            }

            $record = $this->removeBOM($record, $bom_length, $this->enclosure);
            if ([''] === $record) {
                return [null];
            }

            return $record;
        };

        return new CallbackFilterIterator(
            new MapIterator($iterator, $mapper),
            fn (array $record): bool => $this->is_empty_records_included || $record != [null]
        );
    }

    /**
     * Selects the record to be used as the CSV header.
     *
     * Because the header is represented as an array, to be valid
     * a header MUST contain only unique string value.
     *
     * @param int|null $offset the header record offset
     *
     * @throws Exception if the offset is a negative integer
     *
     * @return static
     */
    public function setHeaderOffset(?int $offset): self
    {
        if ($offset === $this->header_offset) {
            return $this;
        }

        if (null !== $offset && 0 > $offset) {
            throw InvalidArgument::dueToInvalidHeaderOffset($offset, __METHOD__);
        }

        $this->header_offset = $offset;
        $this->resetProperties();

        return $this;
    }

    /**
     * Enable skipping empty records.
     */
    public function skipEmptyRecords(): self
    {
        if ($this->is_empty_records_included) {
            $this->is_empty_records_included = false;
            $this->nb_records = -1;
        }

        return $this;
    }

    /**
     * Disable skipping empty records.
     */
    public function includeEmptyRecords(): self
    {
        if (!$this->is_empty_records_included) {
            $this->is_empty_records_included = true;
            $this->nb_records = -1;
        }

        return $this;
    }

    /**
     * Tells whether empty records are skipped by the instance.
     */
    public function isEmptyRecordsIncluded(): bool
    {
        return $this->is_empty_records_included;
    }
}
