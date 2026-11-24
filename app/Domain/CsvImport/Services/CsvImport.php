<?php

namespace Leantime\Domain\CsvImport\Services;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Connector\Models\Entity;
use Leantime\Domain\Connector\Models\Integration;
use Leantime\Domain\Connector\Models\Provider;
use Leantime\Domain\Connector\Services\Integrations;
use Leantime\Domain\Connector\Services\ProviderIntegration;
use Leantime\Domain\CsvImport\Exceptions\ImportFileException;
use Leantime\Domain\CsvImport\Models\ImportTable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class CsvImport extends Provider implements ProviderIntegration
{
    /**
     * @var array|array[]
     */
    public array $entities;

    public array $methods;

    public array $steps = [
        'connect',
        'entity',
        'fields',
        'parse',
        'import',
    ];

    public array $button = [
        'url' => '',
        'text' => 'Import CSV or Excel',
    ];

    /**
     * Constructor - initializes provider metadata and dependencies.
     *
     * @param  Integrations  $integrationService  Connector integrations service used to persist the integration.
     */
    /** Session key of the current upload's file id. */
    public const SESSION_FILE = 'csvImporter.fileId';

    /** The import docs page. */
    public const DOCS_URL = 'https://docs.leantime.io/using-leantime/import-csv';

    public function __construct(
        private Integrations $integrationService,
        private FileReader $fileReader,
        private ImportFiles $importFiles,
    ) {

        $this->id = 'csv_importer';
        $this->name = 'CSV & Excel Import';
        $this->image = '/dist/images/svg/csv-icon.svg';
        $this->description = "Import data from a CSV or Excel file. To learn more, see our <a href='".self::DOCS_URL."' target='_blank'>documentation</a>";

        $this->methods[] = 'import, update';

        // CSVs can be anyting but are always one file.
        $this->entities = [
            'default' => [
                'name' => 'Sheet',
                'fields' => [],
            ],
        ];

        $this->button['url'] = BASE_URL.'/connector/integration?provider='.$this->id.'#/csvImport/upload';

        // The first step of a file import is the upload.
        $this->stepDetails['connect']['title'] = 'label.import_step_upload';
    }

    // Logic to connect to provider goes here.
    // Needs to manage new connection as well as existing connections.
    // Should return bool so we can drive logic in the frontend.
    public function connect(): Response
    {
        // Connection done. Send to next step.
        // May just want to add a nextStep() method to provider model or so.
        return Frontcontroller::redirect(BASE_URL.'/connector/integration?provider='.$this->id.'#/csvImport/upload');
    }

    // Sync the entities from the db

    /**
     * @return true
     */
    public function sync(Entity $Entity): bool
    {

        return true;
    }

    // Get available fields

    /**
     * @return array|mixed
     */
    public function getFields(): mixed
    {
        return $this->currentTable()->headers ?? [];
    }

    public function setFields(array $fields): void {}

    // Get available entities
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * The rows of the current upload (a CSV or Excel file is one entity, its sheet).
     *
     * @return array<int, array<string, string>>|false
     */
    public function getValues(Entity $Entity): mixed
    {
        return $this->currentTable()->rows ?? false;
    }

    /**
     * The rows of the current upload, each with its sheet row number (ImportTable::ROW_KEY).
     *
     * @return array<int, array<string, string>>
     */
    public function geValues()
    {
        return $this->currentTable()->rows ?? [];
    }

    /**
     * The table of the current upload, or null when there is none (or it was cleaned up).
     *
     * @api
     */
    public function currentTable(): ?ImportTable
    {
        $id = session(self::SESSION_FILE);

        return ImportFiles::isId($id) ? $this->importFiles->table($id) : null;
    }

    /**
     * Reads an uploaded file, keeps it for the next steps and creates the Connector
     * integration record with its headings.
     *
     * Any format FileReader reads works: CSV, TSV, text and Excel (.xlsx). The upload replaces
     * the session's previous one.
     *
     * @api
     *
     * @param  UploadedFile  $file  The uploaded file.
     * @return int The id of the created integration record.
     *
     * @throws ImportFileException When the file can't be read; the message is for the person.
     */
    public function processUpload(UploadedFile $file): int
    {
        $table = $this->fileReader->read($file->getRealPath());

        $previous = session(self::SESSION_FILE);
        if (ImportFiles::isId($previous)) {
            $this->importFiles->delete($previous);
        }

        session([self::SESSION_FILE => $this->importFiles->store($file->getRealPath(), $table)]);

        $integration = new Integration;
        $integration->fields = $this->encodeFields($table->headers);

        return (int) $this->integrationService->create($integration);
    }

    /**
     * Reads the current upload again with another sheet or heading row.
     *
     * @api
     *
     * @param  int  $integrationId  The upload's integration record, whose headings are updated.
     * @param  int|null  $sheetIndex  Excel sheet to read; null keeps the detected one.
     * @param  int|null  $headerRow  Row the headings are on; null to find it.
     *
     * @throws ImportFileException When there is no upload or that sheet or row can't be used.
     */
    public function rereadUpload(int $integrationId, ?int $sheetIndex, ?int $headerRow): ImportTable
    {
        $id = session(self::SESSION_FILE);
        if (! ImportFiles::isId($id) || ! is_file($this->importFiles->sourcePath($id))) {
            throw new ImportFileException(__('notification.import_file_gone'));
        }

        $table = $this->fileReader->read($this->importFiles->sourcePath($id), $sheetIndex, $headerRow);
        $this->importFiles->saveTable($id, $table);
        $this->integrationService->patch($integrationId, ['fields' => $this->encodeFields($table->headers)]);

        return $table;
    }

    /**
     * Headings as stored on the integration record: JSON, because headings may contain commas.
     *
     * @param  array<int, string>  $headers
     */
    private function encodeFields(array $headers): string
    {
        return json_encode(array_values($headers), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    }
}
