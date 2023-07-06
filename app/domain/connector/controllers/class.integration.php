<?php

namespace leantime\domain\controllers {

use leantime\core;
use leantime\core\controller;
use leantime\domain\models\auth\roles;
use leantime\domain\repositories;
use leantime\domain\services;
use leantime\domain\models;

use DateTime;
use DateInterval;
use leantime\domain\services\auth;

class integration extends controller
{
    private services\connector\providers $providerService;
    private repositories\connector\leantimeEntities $leantimeEntities;

    private services\connector\integrations $integrationService;

    private services\users $userService;
    private services\tickets $ticketService;

    private services\projects $projectService;

    private $values = array();
    private $fields = array();

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     */
    public function init()
    {
        auth::authOrRedirect([roles::$owner, roles::$admin, roles::$manager, roles::$editor]);

        $this->providerService = new services\connector\providers();
        $this->leantimeEntities = new repositories\connector\leantimeEntities();
        $this->integrationService = new services\connector\integrations();
        $this->userService = new services\users();
        $this->ticketService = new services\tickets();
        $this->projectService = new services\projects();
    }

    private function getProjectIdbyName($allProjects, $projectName){
        foreach ($allProjects as $project) {
            if($project['name'] == $projectName){
                return $project['id'];
            }
        }
        return false;
    }

    /**
     * run - handle post
     *
     * @access public
     *
     */
    public function run()
    {

        $params = $_REQUEST;

        if (isset($params["provider"])) {
            //New integration with provider
            //Get the provider
            $provider = $this->providerService->getProvider($params["provider"]);
            $this->tpl->assign("provider", $provider);


            $currentIntegration = new models\connector\integration();


            if (!isset($params["step"])) {
                $this->tpl->display('connector.newIntegration');
            }


            if (isset($params["integrationId"])) {
                $currentIntegration = $this->integrationService->get($params["integrationId"]);
                $this->tpl->assign("integrationId", $currentIntegration->id);
            }

            //Initiate connection
            if (isset($params["step"])  && $params["step"] == "connect") {

                //This should handle connection UI
                $provider->connect();

            }


            //Choose Entities to sync
            if (isset($params["step"])  && $params["step"] == "entity") {

                $this->tpl->assign("providerEntities", $provider->getEntities());
                $this->tpl->assign("leantimeEntities", $this->leantimeEntities->availableLeantimeEntities);

                //TODO UI to show entity picker/mapper
                $this->tpl->display('connector.integrationEntity');
            }

            //Choose fields to map
            //Choose Entities to sync
            if (isset($params["step"])  && $params["step"] == "fields") {

                $entity = $_POST['leantimeEntities'];
                $_SESSION['currentImportEntity'] = $entity;

                $currentIntegration->entity = $entity;
                $flags = array();
                if($entity == "tickets"){
                    $flags[] = "If you do not have an Editor (User) email/ID field then all imported entities will be assigned to you.";
                    $flags[] = "It is compulsory to have a Source Field matching to Headline and Project";
                }
                else if($entity == "projects"){
                    $flags[] = "If there are no Assigned Users, the project will be assigned to you.";
                    $flags[] = "If there are more than one assigned users, ensure that it is a comma separated list.";
                }


                $this->integrationService->patch($currentIntegration->id, array("entity" => $entity));

                //TODO UI to show field picker/mapper
                if(isset($currentIntegration->fields) && $currentIntegration->fields != '') {
                    $this->tpl->assign("providerFields", explode(",", $currentIntegration->fields));
                }else{
                    $this->tpl->assign("providerFields", $provider->getFields());
                }
                $this->tpl->assign("flags", $flags);
                $this->tpl->assign("leantimeFields", $this->leantimeEntities->availableLeantimeEntities[$entity]['fields']);
                $this->tpl->display('connector.integrationFields');
            }

            if (isset($params["step"])  && $params["step"] == "sync") {
                //TODO UI to show sync schedule/options

                $this->tpl->display('connector.integrationSync');
            }

            if (isset($params["step"])  && $params["step"] == "confirm") {
                //confirm and store in DB
                //TODO WRITE the LOGIC to send the values array to the database, will either come here or in import IF

                $this->values = unserialize($_SESSION['serValues']);
                $this->fields = unserialize($_SESSION['serFields']);

                $finalMappings = array();
                foreach ($this->fields as $field){
                    array_push($finalMappings, $field['sourceField']);
                    array_push($finalMappings, $field['leantimeField']);
                }

                foreach ($this->values as $row) {

                    $ticket = array();

                    for($i = 0; $i<sizeof($finalMappings); $i = $i+2) {

                        $ticket[$finalMappings[$i+1]] = $row[$finalMappings[$i]];
                    }
                    $ticket['editorId'] = $row['editorId'];
                    $ticket['projectId'] = $row['projectId'];
                    if(!isset($ticket['status'])){
                        $ticket['status'] = 3;
                    }
                    $this->ticketService->addTicket($ticket);
                }


                //display stored successfully message
                $this->tpl->display('connector.integrationConfirm');
            }


            if (isset($params["step"])  && $params["step"] == "import") {

                //TODO UI to show sync schedule/options

                //$entity = $currentIntegration->entity;

                $this->values = $provider->geValues();

                //Fetching the field matching and putting it in an array
                $this->fields = array();


                foreach ($_POST as $fieldmapping) {
                    // Checking if the field mapping is selected
                    if (!empty($fieldmapping) && strpos($fieldmapping, '|') !== false) //are we assuming everyone has PHP 8.0? {
                        $mappingParts = explode("|", $fieldmapping);

                        $sourceField = $mappingParts[0];
                        $leantimeField = $mappingParts[1];

                        $this->fields[] = array("sourceField" => $sourceField, "leantimeField" => $leantimeField);
                    }
                }

                $this->tpl->assign("values", $this->values);
                $this->tpl->assign("fields", $this->fields);

                $flags = array();

                if($_SESSION['currentImportEntity'] == "tickets"){

                    $matchingSourceField = '';
                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === 'editorId') {
                            $matchingSourceField = $item['sourceField'];
                            break;
                        }
                    }

                    $headlineFlag = true;

                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === "headline") {
                            $headlineFlag = false;
                            break;
                        }
                    }

                    if($headlineFlag) {
                        $flags[] = "You must have a headline column";
                    }

                    if ($matchingSourceField) {

                        foreach ($this->values as &$row) {
                            if (strpos($row[$matchingSourceField], '@') !== false) {

                                $id = $this->userService->getUserByEmail($row[$matchingSourceField])['id'];
                                if ($id) {
                                    $row['editorId'] = $id;
                                }
                                else {
                                    $flags[] = $row[$matchingSourceField] . " " . "is not a valid User";
                                }
                            }
                            else {
                                $flags[] = "The Author/userId column must contain only valid User emails";
                                break;
                            }
                        }
                    } else{
                        $id = $_SESSION['userdata']['id'];
                        foreach ($this->values as &$row) {
                            $row['editorId'] = $id;
                        }
                    }

                    //PROJECT Name Check
                    $matchingProjectSourceField = '';

                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === 'projectName') {
                            $matchingProjectSourceField = $item['sourceField'];
                            break;
                        }
                    }

                    if ($matchingProjectSourceField) {
                        $allProjects = $this->projectService->getAllProjects();

                        foreach ($this->values as &$row) {
                            $projectId = $this->getProjectIdbyName($allProjects, $row[$matchingProjectSourceField]);
                            if (!$projectId) {
                                $flags[] = $row[$matchingProjectSourceField] . " " . "is not a valid Project";
                            }
//                        if (!$this->projectService->isUserAssignedToProject($this->userService->getUserByEmail($row[$matchingSourceField]), $projectId)){
//                            $flags[] = $row[$matchingSourceField] . " " . "is not assigned to the project " . " " . $row[$matchingProjectSourceField];
//                        }
                            else {
                                $row['projectId'] = $projectId;
                            }
                        }

                    } else {
                        $flags[] = "You must have a column matching to a valid Project.";
                    }

                }

                else if($_SESSION['currentImportEntity'] == "projects"){
                    $matchingProjectNameSourceField = '';

                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === 'name') {
                            $matchingProjectNameSourceField = $item['sourceField'];
                            break;
                        }
                    }

                    if($matchingProjectNameSourceField){
                        $allProjects = $this->projectService->getAllProjects();

                        foreach ($this->values as $row){
                            $projectId = $this->getProjectIdbyName($allProjects, $row[$matchingProjectNameSourceField]);
                            if($projectId){
                                $flags[] = $row[$matchingProjectNameSourceField] . " " . "is an already existing project.";
                            }
                        }

                    } else{
                        $flags[] = "You must have a column with Project Names";
                    }
                }

                //show the imported data as confirmation

                $this->tpl->assign("flags", $flags);


                $serializedFields = serialize($this->fields);
                $serializedValues = serialize($this->values);

                $_SESSION['serFields'] = $serializedFields;
                $_SESSION['serValues'] = $serializedValues;


                // in the template display all the imported values as a table under Leantime field names
                //serialize the value array and send it over along with the fields serialization to the database writing controller

                $this->tpl->display('connector.integrationImport');
            }

        }
    }

}
