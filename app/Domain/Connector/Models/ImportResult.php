<?php

namespace Leantime\Domain\Connector\Models;

/**
 * Outcome of one import run: how many rows were saved and which rows failed, and why.
 *
 * Row numbers are spreadsheet row numbers (the header is row 1, the first record row 2), so
 * the person importing can find the row in their file.
 */
class ImportResult
{
    /** Rows that were saved. */
    public int $imported = 0;

    /** @var array<int, array{row: int, message: string}> Rows that were not saved. */
    public array $failures = [];

    /**
     * Records a row that was saved.
     */
    public function addImported(): void
    {
        $this->imported++;
    }

    /**
     * Records a row that was not saved.
     *
     * @param  int  $row  Spreadsheet row number (header = 1).
     * @param  string  $message  Why the row was not saved, ready to show to the user.
     */
    public function addFailure(int $row, string $message): void
    {
        $this->failures[] = ['row' => $row, 'message' => $message];
    }

    /**
     * Whether every row was saved.
     */
    public function isSuccessful(): bool
    {
        return $this->failures === [];
    }

    /**
     * Number of rows that were not saved.
     */
    public function failedCount(): int
    {
        return count($this->failures);
    }
}
