<?php

namespace Leantime\Domain\CsvImport\Models;

use Leantime\Domain\Connector\Services\Connector;

/**
 * An uploaded file read into a table: headings, rows and what was detected on the way.
 *
 * Every file format ends up as one of these, so the import steps after reading (mapping,
 * review, import) don't depend on the format.
 */
class ImportTable
{
    /**
     * Key of the sheet row number in each row. It is not a column: mapping never offers it,
     * and messages use it so "Row 12" is row 12 in the person's spreadsheet.
     */
    public const ROW_KEY = Connector::ROW_KEY;

    /**
     * @param  string  $format  'csv' or 'xlsx'.
     * @param  array<int, string>  $headers  Column headings, unique and in file order.
     * @param  array<int, array<string, string>>  $rows  Data rows, heading => cell, each with ROW_KEY.
     * @param  int  $headerRow  Row the headings are on (1 = first row).
     * @param  string|null  $delimiter  Separator of a CSV file.
     * @param  string|null  $encoding  Encoding a CSV file was converted from ('UTF-8' when it was already).
     * @param  array<int, string>  $sheetNames  Visible sheets of an Excel file, by sheet index.
     * @param  int  $sheetIndex  Sheet the rows come from.
     * @param  int  $rowsAbove  Filled rows above the heading row, which aren't imported.
     */
    public function __construct(
        public string $format,
        public array $headers,
        public array $rows,
        public int $headerRow = 1,
        public ?string $delimiter = null,
        public ?string $encoding = null,
        public array $sheetNames = [],
        public int $sheetIndex = 0,
        public int $rowsAbove = 0,
    ) {}

    /**
     * Number of data rows.
     */
    public function rowCount(): int
    {
        return count($this->rows);
    }

    /**
     * The first rows, for the preview.
     *
     * @return array<int, array<string, string>>
     */
    public function preview(int $count = 5): array
    {
        return array_slice($this->rows, 0, $count);
    }

    /**
     * Name of the sheet the rows come from, or null for a CSV file.
     */
    public function sheetName(): ?string
    {
        return $this->sheetNames[$this->sheetIndex] ?? null;
    }

    /**
     * Plain array for storing the table between requests.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'format' => $this->format,
            'headers' => $this->headers,
            'rows' => $this->rows,
            'headerRow' => $this->headerRow,
            'delimiter' => $this->delimiter,
            'encoding' => $this->encoding,
            'sheetNames' => $this->sheetNames,
            'sheetIndex' => $this->sheetIndex,
            'rowsAbove' => $this->rowsAbove,
        ];
    }

    /**
     * Rebuilds a table stored with toArray().
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            format: (string) ($data['format'] ?? 'csv'),
            headers: array_values(array_map('strval', (array) ($data['headers'] ?? []))),
            rows: array_values((array) ($data['rows'] ?? [])),
            headerRow: (int) ($data['headerRow'] ?? 1),
            delimiter: isset($data['delimiter']) ? (string) $data['delimiter'] : null,
            encoding: isset($data['encoding']) ? (string) $data['encoding'] : null,
            sheetNames: array_map('strval', (array) ($data['sheetNames'] ?? [])),
            sheetIndex: (int) ($data['sheetIndex'] ?? 0),
            rowsAbove: (int) ($data['rowsAbove'] ?? 0),
        );
    }
}
