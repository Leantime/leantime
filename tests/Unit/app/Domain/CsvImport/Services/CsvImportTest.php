<?php

namespace Unit\app\Domain\CsvImport\Services;

use Leantime\Core\Language;
use Leantime\Domain\Connector\Models\Integration;
use Leantime\Domain\Connector\Services\Integrations;
use Leantime\Domain\CsvImport\Services\CsvImport;
use Leantime\Domain\CsvImport\Services\FileReader;
use Leantime\Domain\CsvImport\Services\ImportFiles;
use Leantime\Domain\CsvImport\Services\TableBuilder;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Unit\TestCase;

/**
 * Unit tests for the CSV upload processing extracted from the
 * CsvImport Upload controller into the CsvImport service.
 */
class CsvImportTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    private ?string $tmpFile = null;

    protected function _after(): void
    {
        if ($this->tmpFile !== null && file_exists($this->tmpFile)) {
            unlink($this->tmpFile);
        }

        $this->tmpFile = null;
    }

    /**
     * Write the given CSV content to a temp file and wrap it in an UploadedFile.
     */
    private function makeCsvUpload(string $content): UploadedFile
    {
        $this->tmpFile = tempnam(sys_get_temp_dir(), 'csvimport_test_');
        file_put_contents($this->tmpFile, $content);

        return new UploadedFile($this->tmpFile, 'import.csv', 'text/csv', null, true);
    }

    /**
     * Build the service with a mocked Integrations dependency and the real reader and storage.
     */
    private function makeService(Integrations $integrationService): CsvImport
    {
        $language = $this->make(Language::class, ['__' => fn (string $index, string $default = '') => $index.'|%s']);

        return new CsvImport($integrationService, new FileReader(new TableBuilder($language), $language), new ImportFiles);
    }

    public function test_process_upload_keeps_the_rows_and_builds_the_integration_from_the_headings(): void
    {
        $captured = null;
        $integrationService = $this->make(Integrations::class, [
            'create' => function (object|array $object) use (&$captured) {
                $captured = $object;

                return 77;
            },
        ]);

        $csv = "name,email,role\nAlice,alice@example.com,admin\nBob,bob@example.com,editor\n";
        $service = $this->makeService($integrationService);

        $id = $service->processUpload($this->makeCsvUpload($csv));

        $this->assertSame(77, $id);

        // Headings are stored as JSON: a heading may contain a comma.
        $this->assertInstanceOf(Integration::class, $captured);
        $this->assertSame('["name","email","role"]', $captured->fields);

        // The rows live in a file named by the session, not in the session itself.
        $this->assertTrue(ImportFiles::isId(session(CsvImport::SESSION_FILE)));
        $this->assertNull(session('csv_records'));
        $records = $service->geValues();
        $this->assertCount(2, $records);
        $this->assertSame('Alice', $records[0]['name']);
        $this->assertSame('editor', $records[1]['role']);
        $this->assertSame(['name', 'email', 'role'], $service->getFields());
    }

    public function test_a_new_upload_replaces_the_previous_one(): void
    {
        $service = $this->makeService($this->make(Integrations::class, ['create' => fn () => 1]));

        $service->processUpload($this->makeCsvUpload("col\nfirst\n"));
        $first = session(CsvImport::SESSION_FILE);
        $service->processUpload($this->makeCsvUpload("col\nsecond\nthird\n"));

        $this->assertNull((new ImportFiles)->table($first), 'the earlier upload is removed');
        $this->assertSame(['second', 'third'], array_column($service->geValues(), 'col'));
    }

    public function test_the_person_can_read_the_upload_again_with_another_heading_row(): void
    {
        $patched = null;
        $service = $this->makeService($this->make(Integrations::class, [
            'create' => fn () => 5,
            'patch' => function (int $id, array $params) use (&$patched) {
                $patched = [$id, $params];

                return true;
            },
        ]));

        $service->processUpload($this->makeCsvUpload("Report,Q4\nTitle,Owner\nOne,Ada\n"));
        $table = $service->rereadUpload(5, null, 2);

        $this->assertSame(['Title', 'Owner'], $table->headers);
        $this->assertSame([5, ['fields' => '["Title","Owner"]']], $patched, 'the mapping step offers the new headings');
    }
}
