<?php

namespace leantime\plugins\services;

use leantime\core\frontcontroller;
use leantime\domain\models\connector\entity;
use leantime\domain\models\connector\provider;
use leantime\domain\services\connector\providerIntegration;


class csvImport extends provider implements providerIntegration {

    private array $fields;

    public function __construct() {


        $this->id= "csv_importer";
        $this->name = "CSV Import";
        $this->image = "/images/doc.png";

        $this->methods[] = "import";

        //CSVs can be anyting but are always one file.
        $this->entities = array(
            "default" => array(
                "name" => "Sheet",
                "fields" => array())
        );

    }

    //Logic to connect to provider goes here.
    //Needs to manage new connection as well as existing connections.
    //Should return bool so we can drive logic in the frontend.
    public function connect() {


        //Connection done. Send to next step.
        //May just want to add a nextStep() method to provider model or so.
        frontcontroller::redirect(BASE_URL."/connector/integration?provider=".$this->id."#/csvImport/upload");
    }

    //Sync the entities from the db
    public function sync(entity $entity){

        return true;

    }

    //Get available fields
    public function getFields(){
        return $_SESSION['csvImporter']['headers'] ?? array();
    }

    public function setFields(array $fields){

        //$_SESSION['csvImporter']['headers'] = json_encode($fields);

    }

    //Get available entities
    public function getEntities(){
        return $this->entities;
    }

    public function getValues(entity $entity){

    }


}
