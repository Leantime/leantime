<?php

namespace Leantime\Domain\CsvImport\Services;

use Leantime\Core\Language;
use Leantime\Domain\CsvImport\Exceptions\ImportFileException;
use Leantime\Domain\CsvImport\Models\ImportTable;

/**
 * Turns the cells of a file into an ImportTable: finds the heading row, names every column
 * and builds the data rows.
 *
 * - Heading row: exports often start with a title or details about the export (Planner puts
 *   the plan name above its table). The heading row is the first of the opening rows that
 *   fills most of the table's width with text. The person can pick another one.
 * - Columns: only columns with a heading or a value become columns, so a heading far to the
 *   right doesn't turn every empty column before it into one.
 * - Headings: invisible characters are removed, a column with data but no heading is named
 *   by its letter ("Column F"), and repeated headings get a number ("Labels (2)"), so Jira's
 *   repeated Labels, Sprint and Comment columns can all be mapped.
 * - Cells: trimmed, including byte order marks, zero-width and non-breaking spaces at either
 *   end. Characters inside a value are kept (joiners belong to emoji and some scripts).
 * - Rows: empty rows are skipped; every row keeps its sheet row number for messages.
 */
class TableBuilder
{
    /** How many opening rows are searched for the headings. */
    public const HEADER_SCAN_ROWS = 20;

    public function __construct(private Language $language) {}

    /**
     * Builds the table.
     *
     * @param  array<int, array<int, string>>  $grid  Cells by sheet row number (1 = first row) and column index.
     * @param  string  $format  'csv' or 'xlsx'.
     * @param  int|null  $headerRow  Row the headings are on; null to find it.
     * @param  int  $maxCells  Most cells the table may hold (rows times columns).
     *
     * @throws ImportFileException When there are no headings or no rows below them, or the
     *                             table would hold more than $maxCells cells.
     */
    public function build(array $grid, string $format, ?int $headerRow = null, int $maxCells = FileReader::MAX_CELLS): ImportTable
    {
        $grid = $this->clean($grid);

        if ($grid === []) {
            throw new ImportFileException($this->language->__('notification.import_file_empty'));
        }

        if ($headerRow === null || ! isset($grid[$headerRow])) {
            $headerRow = $this->findHeaderRow($grid);
        }

        $columns = [];
        $dataRows = 0;
        foreach ($grid as $rowNumber => $cells) {
            if ($rowNumber >= $headerRow) {
                $columns += array_fill_keys(array_keys($cells), true);
                $dataRows += $rowNumber > $headerRow ? 1 : 0;
            }
        }
        ksort($columns);

        if ($dataRows * count($columns) > $maxCells) {
            throw new ImportFileException(sprintf(
                $this->language->__('notification.import_file_too_large'),
                number_format($maxCells)
            ));
        }

        $headers = $this->headers($grid[$headerRow], array_keys($columns));

        $rows = [];
        foreach ($grid as $rowNumber => $cells) {
            if ($rowNumber <= $headerRow) {
                continue;
            }

            $row = [];
            foreach ($headers as $column => $heading) {
                $row[$heading] = $cells[$column] ?? '';
            }
            $row[ImportTable::ROW_KEY] = (string) $rowNumber;
            $rows[] = $row;
        }

        if ($rows === []) {
            throw new ImportFileException($this->language->__('notification.import_file_no_rows'));
        }

        // Detection can't always tell a sparse heading row from a title above the table, so the
        // preview says when rows were left above the headings and the person can change it.
        $rowsAbove = count(array_filter(array_keys($grid), fn (int $rowNumber) => $rowNumber < $headerRow));

        return new ImportTable(format: $format, headers: array_values($headers), rows: $rows, headerRow: $headerRow, rowsAbove: $rowsAbove);
    }

    /**
     * Rows that appear in the file, without empty cells and fully empty rows, in row order.
     *
     * @param  array<int, array<int, string>>  $grid
     * @return array<int, array<int, string>>
     */
    private function clean(array $grid): array
    {
        $clean = [];
        foreach ($grid as $rowNumber => $cells) {
            $kept = [];
            foreach ($cells as $column => $cell) {
                $cell = $this->trimCell((string) $cell);
                if ($cell !== '') {
                    $kept[(int) $column] = $cell;
                }
            }
            if ($kept !== []) {
                ksort($kept);
                $clean[(int) $rowNumber] = $kept;
            }
        }
        ksort($clean);

        return $clean;
    }

    /**
     * Trims a cell, including the invisible characters spreadsheets leave at either end: byte
     * order marks, zero-width spaces and non-breaking spaces. The inside is kept as it is.
     */
    private function trimCell(string $text): string
    {
        return preg_replace('/^[\s\x{FEFF}\x{200B}\x{00A0}]+|[\s\x{FEFF}\x{200B}\x{00A0}]+$/u', '', $text) ?? trim($text);
    }

    /**
     * A heading without byte order marks or zero-width spaces anywhere in it, and with plain
     * spaces for non-breaking ones, so "Due\u{00A0}date" matches "Due date".
     */
    private function cleanHeading(string $text): string
    {
        $text = str_replace(["\u{FEFF}", "\u{200B}"], '', $text);

        return trim(str_replace("\u{00A0}", ' ', $text));
    }

    /**
     * The first opening row that fills at least 60% of the widest opening row with mostly
     * text. A title or "Plan name: Website" above the table fills only a cell or two.
     *
     * @param  array<int, array<int, string>>  $grid  Cleaned rows.
     */
    private function findHeaderRow(array $grid): int
    {
        $opening = array_slice($grid, 0, self::HEADER_SCAN_ROWS, true);
        $widest = max(array_map('count', $opening));
        $needed = max(1, (int) ceil($widest * 0.6));

        foreach ($opening as $rowNumber => $cells) {
            $numbers = count(array_filter($cells, fn (string $cell) => is_numeric($cell)));
            if (count($cells) >= $needed && $numbers <= count($cells) / 2) {
                return $rowNumber;
            }
        }

        return (int) array_key_first($grid);
    }

    /**
     * One unique heading per column.
     *
     * The mapping step separates a column from its Leantime field with "|", so a "|" in a
     * heading becomes "/".
     *
     * A heading is never the row number's key, and a number is never given twice: for
     * "Labels, Labels, Labels (2)" the second column becomes "Labels (2)" and the third
     * "Labels (2) (2)".
     *
     * @param  array<int, string>  $cells  The heading row.
     * @param  array<int, int>  $columns  Column indexes that hold a heading or a value.
     * @return array<int, string> Heading per column index.
     */
    private function headers(array $cells, array $columns): array
    {
        $headers = [];
        $taken = [mb_strtolower(ImportTable::ROW_KEY) => true];
        foreach ($columns as $column) {
            $name = str_replace('|', '/', $this->cleanHeading($cells[$column] ?? ''));
            if ($name === '') {
                $name = sprintf($this->language->__('label.import_column_letter'), self::columnLetter($column));
            }

            $unique = $name;
            for ($number = 2; isset($taken[mb_strtolower($unique)]); $number++) {
                $unique = $name.' ('.$number.')';
            }
            $taken[mb_strtolower($unique)] = true;

            $headers[$column] = $unique;
        }

        return $headers;
    }

    /**
     * Spreadsheet column letter for a column index (0 = A, 26 = AA).
     */
    public static function columnLetter(int $column): string
    {
        $letter = '';
        for ($n = $column + 1; $n > 0; $n = intdiv($n - 1, 26)) {
            $letter = chr(65 + ($n - 1) % 26).$letter;
        }

        return $letter;
    }
}
