<?php

namespace Leantime\Plugins\MotivationalQuotes\CsvImport\Services;

use Leantime\Core\Frontcontroller;
use Leantime\Domain\Connector\Models\Entity;
use Leantime\Domain\Connector\Models\Provider;
use Leantime\Domain\Connector\Services\ProviderIntegration;

/**
 *
 */
class CsvImport extends Provider implements ProviderIntegration
{
    private array $fields;
    /**
     * @var array|array[]
     */
    private array $entities;
    private array $methods;

    public function __construct()
    {


        $this->id = "csv_importer";
        $this->name = "CSV Import";
        $this->image = "/dist/images/doc.png";

        $this->methods[] = "import";

        //CSVs can be anyting but are always one file.
        $this->entities = array(
            "default" => array(
                "name" => "Sheet",
                "fields" => array(),
        ),
        );
    }

    //Logic to connect to provider goes here.
    //Needs to manage new connection as well as existing connections.
    //Should return bool so we can drive logic in the frontend.
    /**
     * @return void
     */
    public function connect(): void
    {


        //Connection done. Send to next step.
        //May just want to add a nextStep() method to provider model or so.
        Frontcontroller::redirect(BASE_URL . "/connector/integration?provider=" . $this->id . "#/csvImport/upload");
    }

    //Sync the entities from the db

    /**
     * @param Entity $Entity
     * @return true
     */
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
    /**
     * @return array|mixed
     */
    public function getFields(): mixed
    {
        return $_SESSION['csvImporter']['headers'] ?? array();
    }

    /**
     * @param array $fields
     * @return void
     */
    public function setFields(array $fields): void
    {

        //$_SESSION['csvImporter']['headers'] = json_encode($fields);
    }

    //Get available entities

    /**
     * @return array
     */
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
    public function getValues(Entity $Entity): void
    {
    }
}
