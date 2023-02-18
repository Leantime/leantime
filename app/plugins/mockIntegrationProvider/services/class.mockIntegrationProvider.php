<?php

namespace leantime\plugins\services;

use leantime\core\frontcontroller;
use leantime\domain\models\connector\entity;
use leantime\domain\models\connector\provider;
use leantime\domain\services\connector\providerIntegration;


class mockIntegrationProvider extends provider implements providerIntegration {

    public function __construct() {


        $this->id= 1;
        $this->name = "Google Sheets";
        $this->image = "/images/doc.png";
        $this->methods[] = "import";
        $this->methods[] = "sync";

        //Unsure about this right now
        $this->entities[] = "project";
        $this->entities[] = "task";
        $this->entities[] = "milestone";
        $this->entities[] = "idea";


    }

    //Logic to connect to provider goes here.
    //Needs to manage new connection as well as existing connections.
    //Should return bool so we can drive logic in the frontend
    public function connect($name, $var) {


        //Connection done. Send to next step.
        //May just want to add a nextStep() method to provider model or so.
        frontcontroller::redirect(BASE_URL."/connector/integration?provider=".$this->id."&step=entity");
    }

    //Sync the entities from the db
    public function sync(entity $entity){

        return true;

    }

    //Get available vields
    public function getFields(){

    }

    //Get available entities
    public function getEntities(){

    }

    public function getValues(entity $entity){

    }


}
