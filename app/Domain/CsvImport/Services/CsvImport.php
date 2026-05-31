<?php

namespace Leantime\Domain\CsvImport\Services;

use League\Csv\Exception as CsvException;
use League\Csv\Reader;
use League\Csv\Statement;
use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Connector\Models\Entity;
use Leantime\Domain\Connector\Models\Integration;
use Leantime\Domain\Connector\Models\Provider;
use Leantime\Domain\Connector\Services\Integrations;
use Leantime\Domain\Connector\Services\ProviderIntegration;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

class CsvImport extends Provider implements ProviderIntegration
{
    private array $fields;

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
        'text' => 'Import CSV',
    ];

    /**
     * Constructor - initializes provider metadata and dependencies.
     *
     * @param  Integrations  $integrationService  Connector integrations service used to persist the integration.
     */
    public function __construct(private Integrations $integrationService)
    {

        $this->id = 'csv_importer';
        $this->name = 'CSV Import';
        $this->image = '/dist/images/svg/csv-icon.svg';
        $this->description = "Import data from a CSV file. To learn more about the CSV format, please visit our <a href='https://support.leantime.io/en/article/importing-data-via-csv-1v941gy' target='_blank'>documentation</a>";

        $this->methods[] = 'import, update';

        // CSVs can be anyting but are always one file.
        $this->entities = [
            'default' => [
                'name' => 'Sheet',
                'fields' => [],
            ],
        ];

        $this->button['url'] = BASE_URL.'/connector/integration?provider='.$this->id.'#/csvImport/upload';
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
        return session('csvImporter.headers') ?? [];
    }

    public function setFields(array $fields): void {}

    // Get available entities
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @return void
     */
    public function getValues(Entity $Entity): mixed
    {
        $integrationMeta = session('csvImporter.meta', '');

        if (empty($integrationMeta)) {
            return false;
        }

        $rows = safe_unserialize($integrationMeta, []);

        // Removing the first row if it contains headers
        // can be returned or dealt with later on for field matching
        if (count($rows) > 0) {
            $headers = array_shift($rows);
        }

        return $rows;
    }

    public function geValues()
    {

        return session('csv_records') ?? [];
    }

    /**
     * Parse an uploaded CSV file, store its records in the session and create a
     * Connector integration record built from the CSV header.
     *
     * Reads the uploaded CSV (with the first row as header), materializes all
     * records into the session under the `csv_records` key, builds an
     * Integration model whose `fields` are the comma separated header columns
     * and persists it via the Connector integrations service.
     *
     * @api
     *
     * @param  UploadedFile  $file  The uploaded CSV file.
     * @return int The id of the created integration record.
     *
     * @throws CsvException When the CSV cannot be parsed.
     */
    public function processUpload(UploadedFile $file): int
    {
        $csv = Reader::createFromPath($file->getRealPath(), 'r');
        $csv->setHeaderOffset(0);

        // Will throw a League\Csv\Exception if the CSV is malformed.
        $records = Statement::create()->process($csv);

        $header = $records->getHeader();

        $rows = [];
        foreach ($records as $record) {
            $rows[] = $record;
        }

        // Temporarily store the parsed records in the session for later steps.
        session(['csv_records' => $rows]);

        $integration = new Integration;
        $integration->fields = implode(',', $header);

        return (int) $this->integrationService->create($integration);
    }
}
