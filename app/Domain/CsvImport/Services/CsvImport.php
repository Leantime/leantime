<?php

namespace  Leantime\Domain\CsvImport\Services;

use Leantime\Core\Controller\Frontcontroller;
use Leantime\Domain\Connector\Models\Entity;
use Leantime\Domain\Connector\Models\Provider;
use Leantime\Domain\Connector\Services\ProviderIntegration;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class CsvImport extends Provider implements ProviderIntegration
{
    private array $fields;
    /**
     * @var array|array[]
     */
    public array $entities;
    public array $methods;

    public array $steps = [
        "connect",
        "entity",
        "fields",
        "parse",
        "import",
    ];

    public array $button = array(
        "url" => '',
        "text" => 'Import CSV',
    );

    public function __construct()
    {

        $this->id = "csv_importer";
        $this->name = "CSV Import";
        $this->image = "/dist/images/svg/csv-icon.svg";
        $this->description = "Impport data from a CSV file. To learn more about the CSV format, please visit our <a href='https://support.leantime.io/en/article/importing-data-via-csv-1v941gy' target='_blank'>documentation</a>";

        $this->methods[] = "import, update";

        //CSVs can be anyting but are always one file.
        $this->entities = array(
            "default" => array(
                "name" => "Sheet",
                "fields" => array(),
            ),
        );

        $this->button["url"] = BASE_URL . "/connector/integration?provider=" . $this->id . "#/csvImport/upload";
    }

    //Logic to connect to provider goes here.
    //Needs to manage new connection as well as existing connections.
    //Should return bool so we can drive logic in the frontend.
    /**
     * @return Response
     */
    public function connect(): Response
    {
        //Connection done. Send to next step.
        //May just want to add a nextStep() method to provider model or so.
        return Frontcontroller::redirect(BASE_URL . "/connector/integration?provider=" . $this->id . "#/csvImport/upload");
    }

    //Sync the entities from the db

    /**
     * @param Entity $Entity
     * @return true
     */
    public function sync(Entity $Entity): bool
    {

        return true;
    }

    //Get available fields

    /**
     * @return array|mixed
     */
    public function getFields(): mixed
    {
        return session("csvImporter.headers") ?? array();
    }

    /**
     * @param array $fields
     * @return void
     */
    public function setFields(array $fields): void
    {


    }

    //Get available entities
    /**
     * @return array
     */
    public function getEntities(): array
    {
        return $this->entities;
    }

    /**
     * @param Entity $Entity
     * @return void
     */
    public function getValues(Entity $Entity): mixed
    {
        $integrationMeta = session("csvImporter.meta", '');

        if (empty($integrationMeta)) {
            return false;
        }

        $rows = unserialize($integrationMeta);

        // Removing the first row if it contains headers
        // can be returned or dealt with later on for field matching
        if (count($rows) > 0) {
            $headers = array_shift($rows);
        }
        return $rows;
    }

    public function geValues()
    {

        return session("csv_records") ?? [];
    }
}
