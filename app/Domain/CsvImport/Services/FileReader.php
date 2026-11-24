<?php

namespace Leantime\Domain\CsvImport\Services;

use League\Csv\Reader;
use Leantime\Core\Language;
use Leantime\Domain\CsvImport\Exceptions\ImportFileException;
use Leantime\Domain\CsvImport\Models\ImportTable;
use Shuchkin\SimpleXLSX;

/**
 * Reads an uploaded file into an ImportTable: the Read step of an import.
 *
 * The format comes from the file's content, not its name: an .xlsx renamed to .csv is still
 * read as Excel, and an old .xls gets a message instead of garbage.
 *
 * - CSV (and TSV/TXT): the encoding (UTF-8, UTF-16 with BOM, Windows-1252) is converted to
 *   UTF-8 and the separator (comma, semicolon, tab, pipe) is detected.
 * - Excel (.xlsx): read with SimpleXLSX, cell by cell, so a sheet formatted across whole
 *   columns isn't padded to its declared size. Date cells become dates.
 *
 * Both are capped (unpacked size and number of cells) so a small upload can't exhaust the
 * server's memory. TableBuilder then finds the headings and builds the rows.
 */
class FileReader
{
    /** Most bytes an Excel file may unpack to. */
    public const MAX_UNPACKED_BYTES = 50 * 1024 * 1024;

    /** Most cells (filled or not, up to the last filled one in each row) a file may hold. */
    public const MAX_CELLS = 500000;

    /** Separators tried for CSV files, most common first. */
    private const DELIMITERS = [',', ';', "\t", '|'];

    /** How many records of a CSV file the separator is detected from. */
    private const DELIMITER_SAMPLE_RECORDS = 20;

    /** Sheet states Excel hides from the person ("veryHidden" can only be shown with VBA). */
    private const HIDDEN_SHEET_STATES = ['hidden', 'veryHidden'];

    /**
     * @param  int  $maxCells  Most cells a file may hold (lower in tests).
     * @param  int  $maxUnpackedBytes  Most bytes an Excel file may unpack to (lower in tests).
     */
    public function __construct(
        private TableBuilder $tableBuilder,
        private Language $language,
        private int $maxCells = self::MAX_CELLS,
        private int $maxUnpackedBytes = self::MAX_UNPACKED_BYTES,
    ) {}

    /**
     * Reads a file into a table.
     *
     * @param  string  $path  The uploaded file.
     * @param  int|null  $sheetIndex  Excel sheet to read; null for the first visible sheet with data.
     * @param  int|null  $headerRow  Row the headings are on; null to find it.
     *
     * @throws ImportFileException When the file can't be read; the message is for the person.
     */
    public function read(string $path, ?int $sheetIndex = null, ?int $headerRow = null): ImportTable
    {
        $start = (string) @file_get_contents($path, false, null, 0, 8);

        if (str_starts_with($start, "\xD0\xCF\x11\xE0")) {
            throw new ImportFileException($this->language->__('notification.import_file_xls'));
        }

        if (str_starts_with($start, "PK\x03\x04")) {
            return $this->readXlsx($path, $sheetIndex, $headerRow);
        }

        return $this->readCsv($path, $headerRow);
    }

    /**
     * Reads a CSV, TSV or text file.
     */
    private function readCsv(string $path, ?int $headerRow): ImportTable
    {
        $raw = (string) file_get_contents($path);
        [$text, $encoding] = $this->toUtf8($raw);

        if (trim($text) === '') {
            throw new ImportFileException($this->language->__('notification.import_file_empty'));
        }

        $delimiter = $this->detectDelimiter($text);

        $csv = Reader::createFromString($text);
        $csv->setDelimiter($delimiter);
        $csv->setEscape('');

        $grid = [];
        $cells = 0;
        foreach ($csv->getRecords() as $index => $record) {
            $cells += count($record);
            if ($cells > $this->maxCells) {
                throw $this->tooLarge();
            }
            $grid[$index + 1] = array_map(fn ($cell) => (string) $cell, array_values($record));
        }

        $table = $this->tableBuilder->build($grid, 'csv', $headerRow, $this->maxCells);
        $table->delimiter = $delimiter;
        $table->encoding = $encoding;

        return $table;
    }

    /**
     * Reads one sheet of an Excel workbook.
     */
    private function readXlsx(string $path, ?int $sheetIndex, ?int $headerRow): ImportTable
    {
        $this->assertWorkbookIsSafe($path);

        $xlsx = SimpleXLSX::parseFile($path);
        if ($xlsx === false) {
            throw new ImportFileException($this->language->__('notification.import_file_unreadable'));
        }

        // Dates come as "2026-12-05 09:30:00", wall-clock time as typed in Excel (no time zone).
        $xlsx->setDateTimeFormat('Y-m-d H:i:s');

        $sheetNames = [];
        foreach ($xlsx->sheetNames() as $index => $name) {
            $state = (string) ($xlsx->sheetMeta($index)['state'] ?? '');
            if (! in_array($state, self::HIDDEN_SHEET_STATES, true)) {
                $sheetNames[(int) $index] = (string) $name;
            }
        }

        if ($sheetIndex === null || ! isset($sheetNames[$sheetIndex])) {
            $sheetIndex = $this->firstSheetWithData($xlsx, array_keys($sheetNames));
        }

        $grid = $this->sheetGrid($xlsx, $sheetIndex);
        $table = $this->tableBuilder->build($grid, 'xlsx', $headerRow, $this->maxCells);
        $table->sheetNames = $sheetNames;
        $table->sheetIndex = $sheetIndex;

        return $table;
    }

    /**
     * Refuses archives that aren't workbooks or unpack to more than the byte limit.
     *
     * Checked before SimpleXLSX unpacks anything, from the sizes in the archive's directory.
     */
    private function assertWorkbookIsSafe(string $path): void
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw new ImportFileException($this->language->__('notification.import_file_unreadable'));
        }

        try {
            if ($zip->locateName('xl/workbook.xml') === false) {
                throw new ImportFileException($this->language->__('notification.import_file_not_workbook'));
            }

            $unpacked = 0;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $unpacked += (int) ($zip->statIndex($i)['size'] ?? 0);
                if ($unpacked > $this->maxUnpackedBytes) {
                    throw $this->tooLarge();
                }
            }
        } finally {
            $zip->close();
        }
    }

    /**
     * The first sheet with a cell that holds a value, or the first sheet.
     *
     * A sheet can have rows only because cells were formatted or cleared; those rows don't count.
     *
     * @param  array<int, int>  $sheetIndexes  Visible sheets.
     */
    private function firstSheetWithData(SimpleXLSX $xlsx, array $sheetIndexes): int
    {
        foreach ($sheetIndexes as $index) {
            $sheet = $xlsx->worksheet($index);
            if ($sheet === false || ! isset($sheet->sheetData->row)) {
                continue;
            }

            foreach ($sheet->sheetData->row as $row) {
                foreach ($row->c as $cell) {
                    if ($this->cellText($xlsx, $cell) !== '') {
                        return $index;
                    }
                }
            }
        }

        return $sheetIndexes[0] ?? 0;
    }

    /**
     * The filled cells of a sheet, by sheet row number (1 = first row) and column index.
     *
     * @return array<int, array<int, string>>
     */
    private function sheetGrid(SimpleXLSX $xlsx, int $sheetIndex): array
    {
        $sheet = $xlsx->worksheet($sheetIndex);
        if ($sheet === false || ! isset($sheet->sheetData->row)) {
            return [];
        }

        $grid = [];
        $cells = 0;
        $rowNumber = 0;
        foreach ($sheet->sheetData->row as $row) {
            // Rows without an r attribute follow the previous one.
            $rowNumber = isset($row['r']) ? (int) $row['r'] : $rowNumber + 1;
            $column = -1;
            foreach ($row->c as $cell) {
                $index = $xlsx->getIndex((string) $cell['r']);
                $column = $index[0] > -1 ? $index[0] : $column + 1;

                $value = $this->cellText($xlsx, $cell);
                if ($value === '') {
                    continue;
                }

                if (++$cells > $this->maxCells) {
                    throw $this->tooLarge();
                }
                $grid[$rowNumber][$column] = $value;
            }
        }

        return $grid;
    }

    /**
     * A cell value as text.
     *
     * A date cell whose number format shows no time becomes a plain date (2026-12-05), so a due
     * date stays a date. A date-time cell keeps its time, midnight included, and text is kept
     * as typed.
     */
    private function cellText(SimpleXLSX $xlsx, \SimpleXMLElement $cell): string
    {
        $value = $xlsx->value($cell);
        if (is_bool($value)) {
            return $value ? 'TRUE' : 'FALSE';
        }

        $text = trim((string) $value);

        if (preg_match('/^(\d{4}-\d{2}-\d{2}) 00:00:00$/', $text, $date) === 1 && $this->isDateOnlyCell($xlsx, $cell)) {
            return $date[1];
        }

        return $text;
    }

    /**
     * Whether a cell is a number formatted as a date without a time.
     */
    private function isDateOnlyCell(SimpleXLSX $xlsx, \SimpleXMLElement $cell): bool
    {
        $type = (string) $cell['t'];
        if ($type !== '' && $type !== 'n') {
            return false;
        }

        $format = (string) ($xlsx->cellFormats[(int) $cell['s']]['format'] ?? '');

        // Quoted text and [colour]/[locale] sections aren't part of the date pattern.
        $pattern = (string) preg_replace('/"[^"]*"|\[[^\]]*\]/', '', $format);

        return $pattern !== '' && preg_match('/[hHsS]|AM\/PM|A\/P/i', $pattern) !== 1;
    }

    /**
     * Converts a CSV file's bytes to UTF-8.
     *
     * @return array{0: string, 1: string} The text and the encoding it was read as.
     */
    private function toUtf8(string $raw): array
    {
        if (str_starts_with($raw, "\xEF\xBB\xBF")) {
            return [substr($raw, 3), 'UTF-8'];
        }

        if (str_starts_with($raw, "\xFF\xFE")) {
            return [(string) mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16LE'), 'UTF-16LE'];
        }

        if (str_starts_with($raw, "\xFE\xFF")) {
            return [(string) mb_convert_encoding(substr($raw, 2), 'UTF-8', 'UTF-16BE'), 'UTF-16BE'];
        }

        if (mb_check_encoding($raw, 'UTF-8')) {
            return [$raw, 'UTF-8'];
        }

        // Not UTF-8: older Excel versions save CSV in the Windows code page.
        return [(string) mb_convert_encoding($raw, 'UTF-8', 'Windows-1252'), 'Windows-1252'];
    }

    /**
     * Finds the separator: the candidate that splits the first records into the same number of
     * columns most consistently, preferring more columns.
     */
    private function detectDelimiter(string $text): string
    {
        $records = $this->delimiterCounts($text);

        $best = ',';
        $bestScore = [0.0, 0];
        foreach (self::DELIMITERS as $delimiter) {
            $counts = array_values(array_filter(array_column($records, $delimiter), fn (int $count) => $count > 0));
            if ($counts === []) {
                continue;
            }

            $frequency = array_count_values($counts);
            arsort($frequency);
            $mode = (int) array_key_first($frequency);
            $score = [$frequency[$mode] / max(count($records), 1), $mode];

            if ($score > $bestScore) {
                $best = $delimiter;
                $bestScore = $score;
            }
        }

        return $best;
    }

    /**
     * How often each candidate separator appears in each of the first records.
     *
     * Records are read like a CSV reader reads them: separators and line breaks inside quoted
     * cells don't count, so a description over several lines stays one record.
     *
     * @return array<int, array<string, int>> Per record: separator => count.
     */
    private function delimiterCounts(string $text): array
    {
        $empty = array_fill_keys(self::DELIMITERS, 0);
        $records = [];
        $counts = $empty;
        $filled = false;
        $quoted = false;
        $length = strlen($text);

        for ($i = 0; $i < $length; $i++) {
            $char = $text[$i];

            if ($char === '"') {
                $quoted = ! $quoted;
                $filled = true;

                continue;
            }

            if ($quoted) {
                continue;
            }

            if ($char === "\n" || $char === "\r") {
                if ($filled) {
                    $records[] = $counts;
                    if (count($records) >= self::DELIMITER_SAMPLE_RECORDS) {
                        return $records;
                    }
                }
                $counts = $empty;
                $filled = false;

                continue;
            }

            if (isset($counts[$char])) {
                $counts[$char]++;
            }
            if ($char !== ' ' && $char !== "\t") {
                $filled = true;
            }
        }

        if ($filled) {
            $records[] = $counts;
        }

        return $records;
    }

    /**
     * The message for a file over the size or cell limit.
     */
    private function tooLarge(): ImportFileException
    {
        return new ImportFileException(sprintf(
            $this->language->__('notification.import_file_too_large'),
            number_format($this->maxCells)
        ));
    }
}
