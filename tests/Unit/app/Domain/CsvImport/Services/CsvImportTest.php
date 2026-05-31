<?php

namespace Unit\app\Domain\CsvImport\Services;

use Leantime\Domain\Connector\Models\Integration;
use Leantime\Domain\Connector\Services\Integrations;
use Leantime\Domain\CsvImport\Services\CsvImport;
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
     * Build the service with a mocked Integrations dependency.
     */
    private function makeService(Integrations $integrationService): CsvImport
    {
        return new CsvImport($integrationService);
    }

    public function test_process_upload_stores_records_and_builds_integration_from_header(): void
    {
        session()->forget('csv_records');

        $captured = null;
        $integrationService = $this->make(Integrations::class, [
            'create' => function (object|array $object) use (&$captured) {
                $captured = $object;

                return 77;
            },
        ]);

        $csv = "name,email,role\nAlice,alice@example.com,admin\nBob,bob@example.com,editor\n";
        $file = $this->makeCsvUpload($csv);

        $id = $this->makeService($integrationService)->processUpload($file);

        // Returns the integration id from the service.
        $this->assertSame(77, $id);

        // Integration model is built from the comma-joined header row.
        $this->assertInstanceOf(Integration::class, $captured);
        $this->assertSame('name,email,role', $captured->fields);

        // All data rows (excluding the header) are materialized into the session.
        $records = session('csv_records');
        $this->assertCount(2, $records);
        $this->assertSame('Alice', $records[0]['name']);
        $this->assertSame('alice@example.com', $records[0]['email']);
        $this->assertSame('Bob', $records[1]['name']);
        $this->assertSame('editor', $records[1]['role']);
    }

    public function test_process_upload_persists_all_rows_not_an_exhausted_iterator(): void
    {
        // Regression test for the latent iterator-exhaustion bug: the session
        // must contain the actual rows, not an empty set.
        session()->forget('csv_records');

        $integrationService = $this->make(Integrations::class, [
            'create' => fn () => 1,
        ]);

        $csv = "col\nfirst\nsecond\nthird\n";
        $file = $this->makeCsvUpload($csv);

        $this->makeService($integrationService)->processUpload($file);

        $records = session('csv_records');
        $this->assertCount(3, $records);
        $this->assertSame('first', $records[0]['col']);
        $this->assertSame('third', $records[2]['col']);
    }
}
