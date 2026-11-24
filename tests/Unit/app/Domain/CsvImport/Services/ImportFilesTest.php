<?php

namespace Unit\app\Domain\CsvImport\Services;

use Leantime\Domain\CsvImport\Models\ImportTable;
use Leantime\Domain\CsvImport\Services\ImportFiles;
use Unit\TestCase;

/**
 * Uploaded import files are kept on the local disk for a day.
 */
class ImportFilesTest extends TestCase
{
    private function table(): ImportTable
    {
        return new ImportTable(format: 'csv', headers: ['Title'], rows: [['Title' => 'Café', ImportTable::ROW_KEY => '2']], delimiter: ',', encoding: 'UTF-8');
    }

    public function test_a_stored_file_and_its_table_can_be_read_back_and_removed(): void
    {
        $upload = tempnam(sys_get_temp_dir(), 'lt-upload');
        file_put_contents($upload, "Title\nCafé\n");
        $files = new ImportFiles;

        $id = $files->store($upload, $this->table());

        $this->assertTrue(ImportFiles::isId($id));
        $this->assertStringStartsWith(storage_path('app/imports'), $files->sourcePath($id), 'kept on the local disk, never the S3 disk');
        $this->assertSame("Title\nCafé\n", file_get_contents($files->sourcePath($id)), 'the original is kept, to read it again');
        $this->assertSame($this->table()->toArray(), $files->table($id)?->toArray());

        $files->delete($id);
        $this->assertNull($files->table($id));
        $this->assertFileDoesNotExist($files->sourcePath($id));
        @unlink($upload);
    }

    public function test_files_older_than_a_day_are_removed_by_the_cleanup(): void
    {
        $upload = tempnam(sys_get_temp_dir(), 'lt-upload');
        $files = new ImportFiles;
        $old = $files->store($upload, $this->table());
        $new = $files->store($upload, $this->table());
        touch($files->sourcePath($old), time() - 25 * 3600);
        touch(storage_path('app/imports/'.$old.'.json'), time() - 25 * 3600);

        $this->assertGreaterThanOrEqual(2, $files->deleteExpired());

        $this->assertNull($files->table($old));
        $this->assertNotNull($files->table($new));
        $files->delete($new);
        @unlink($upload);
    }

    public function test_ids_that_are_not_uuids_never_become_paths(): void
    {
        $files = new ImportFiles;

        $this->assertFalse(ImportFiles::isId('../../config/.env'));
        $this->assertNull($files->table('../../config/.env'));

        $this->expectException(\InvalidArgumentException::class);
        $files->sourcePath('../../config/.env');
    }
}
