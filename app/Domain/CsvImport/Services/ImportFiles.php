<?php

namespace Leantime\Domain\CsvImport\Services;

use Illuminate\Support\Facades\Log;
use Leantime\Domain\CsvImport\Models\ImportTable;
use Ramsey\Uuid\Uuid;

/**
 * Keeps an uploaded import file and its table between the steps of an import.
 *
 * Files live on the server's own disk (storage/app/imports), never on the file storage disk:
 * with S3 storage on, they would otherwise travel to S3 and back on every step. They are
 * temporary: an upload replaces the previous one of the same session, and files older than a
 * day are removed by the daily cleanup.
 *
 * Each upload has an id (a UUID). The original file is kept so the person can pick another
 * sheet or heading row without uploading again.
 */
class ImportFiles
{
    /** Hours an import file is kept. */
    public const KEEP_HOURS = 24;

    /**
     * Stores an uploaded file and its table, and returns its id.
     *
     * @param  string  $uploadedPath  The file as uploaded (PHP's temporary file).
     */
    public function store(string $uploadedPath, ImportTable $table): string
    {
        $this->ensureDirectory();

        $id = Uuid::uuid4()->toString();
        if (! copy($uploadedPath, $this->sourcePath($id))) {
            throw new \RuntimeException('Could not store the import file.');
        }
        $this->saveTable($id, $table);

        return $id;
    }

    /**
     * Replaces the table of a stored file (another sheet or heading row was picked).
     */
    public function saveTable(string $id, ImportTable $table): void
    {
        $this->ensureDirectory();
        file_put_contents($this->tablePath($id), json_encode($table->toArray(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR), LOCK_EX);
    }

    /**
     * The table of a stored file, or null when it is gone (cleaned up or never stored).
     */
    public function table(string $id): ?ImportTable
    {
        if (! self::isId($id) || ! is_file($this->tablePath($id))) {
            return null;
        }

        try {
            $data = json_decode((string) file_get_contents($this->tablePath($id)), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Unreadable import table '.$id);

            return null;
        }

        return is_array($data) ? ImportTable::fromArray($data) : null;
    }

    /**
     * Path of the original upload.
     */
    public function sourcePath(string $id): string
    {
        return $this->directory().'/'.$this->checkedId($id).'.upload';
    }

    /**
     * Removes a stored file and its table.
     */
    public function delete(string $id): void
    {
        if (! self::isId($id)) {
            return;
        }

        foreach ([$this->sourcePath($id), $this->tablePath($id)] as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    /**
     * Removes files older than KEEP_HOURS. Run daily.
     *
     * @return int Number of files removed.
     */
    public function deleteExpired(): int
    {
        $removed = 0;
        $cutoff = time() - self::KEEP_HOURS * 3600;

        foreach (glob($this->directory().'/*') ?: [] as $path) {
            if (is_file($path) && filemtime($path) < $cutoff && @unlink($path)) {
                $removed++;
            }
        }

        return $removed;
    }

    /**
     * Whether a value is a file id (guards the paths built from it).
     */
    public static function isId(mixed $id): bool
    {
        return is_string($id) && Uuid::isValid($id);
    }

    private function tablePath(string $id): string
    {
        return $this->directory().'/'.$this->checkedId($id).'.json';
    }

    private function checkedId(string $id): string
    {
        if (! self::isId($id)) {
            throw new \InvalidArgumentException('Not an import file id.');
        }

        return $id;
    }

    private function directory(): string
    {
        return storage_path('app/imports');
    }

    private function ensureDirectory(): void
    {
        if (! is_dir($this->directory())) {
            mkdir($this->directory(), 0775, true);
        }
    }
}
