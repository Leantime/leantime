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
    use Ramsey\Uuid\Uuid;

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
        private repositories\ideas $ideaRepository;
        private repositories\canvas $canvasRepository;
        private repositories\goalcanvas $goalRepository;

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
            $this->ideaRepository = new repositories\ideas();
            $this->goalRepository = new repositories\goalcanvas();
            $this->canvasRepository = new repositories\canvas();
        }

        private function getProjectIdbyName($allProjects, $projectName)
        {
            foreach ($allProjects as $project) {
                if ($project['name'] == $projectName) {
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
                if (isset($params["step"]) && $params["step"] == "connect") {

                    //This should handle connection UI
                    $provider->connect();

                }


                //Choose Entities to sync
                if (isset($params["step"]) && $params["step"] == "entity") {

                    $this->tpl->assign("providerEntities", $provider->getEntities());
                    $this->tpl->assign("leantimeEntities", $this->leantimeEntities->availableLeantimeEntities);

                    $this->tpl->display('connector.integrationEntity');
                }

                //Choose fields to map
                //Choose Entities to sync
                if (isset($params["step"]) && $params["step"] == "fields") {

                    $entity = $_POST['leantimeEntities'];
                    $_SESSION['currentImportEntity'] = $entity;

                    $currentIntegration->entity = $entity;
                    $flags = array();
                    if ($entity == "tickets") {
                        $flags[] = "If you do not have an Editor (User) email/ID field then all imported entities will be assigned to you.";
                        $flags[] = "It is compulsory to have a Source Field matching to Headline and Project";
                    } else if ($entity == "projects") {
                        $flags[] = "If there are no Assigned Users, the project will be assigned to you.";
                        $flags[] = "If there are more than one assigned users, ensure that it is a comma separated list.";
                        $flags[] = "If you decide to set permissions settings they have to be one of the following types: all, clients or restricted.";
                        $flags[] = "It is compulsory to have a Source Field matching to Project Name and ClientId.";
                    } else if ($entity == "users") {
                        //TODO add flags for users
                        $flags[] = "It is compulsory to have a Source Field matching to First Name, Role, Email, and Send Invite.";
                        $flags[] = "The Send Invite field should be either Yes/No or else the user will not be imported. If Send Invite is set to No the user will have to reset the password on their own.";
                        $flags[] = "Roles have to be one of the following values 'readonly', 'commenter', 'editor', 'manager', 'admin', 'owner'.";

                    } else if ($entity == "ideas") {
                        //TODO add flags for ideas
                        $flags[] = "It is compulsory to have a Source Field matching to Description, Data, and CanvasId.";
                        $flags[] = "If you do not have an Author field then you will be assigned as the Author.";
                    } else if ($entity == "goals") {
                        //TODO add flags for goals
                        $flags[] = "It is compulsory to have a Source Field matching to Title, CanvasId, Start Value, Current Value, and End Value.";
                    } else if ($entity == "milestones") {
                        //TODO add flags for milestones
                        $flags[] = "If you do not have an Editor (User) email/ID field then all imported entities will be assigned to you.";
                        $flags[] = "It is compulsory to have a Source Field matching to Headline and Project";
                        $flags[] = "It is compulsory to have a Edit From and Edit To column for each Milestone";
                    }

                    $this->integrationService->patch($currentIntegration->id, array("entity" => $entity));

                    if (isset($currentIntegration->fields) && $currentIntegration->fields != '') {
                        $this->tpl->assign("providerFields", explode(",", $currentIntegration->fields));
                    } else {
                        $this->tpl->assign("providerFields", $provider->getFields());
                    }
                    $this->tpl->assign("flags", $flags);
                    $this->tpl->assign("leantimeFields", $this->leantimeEntities->availableLeantimeEntities[$entity]['fields']);
                    $this->tpl->display('connector.integrationFields');
                }

                if (isset($params["step"]) && $params["step"] == "sync") {
                    //TODO UI to show sync schedule/options

                    $this->tpl->display('connector.integrationSync');
                }

                if (isset($params["step"]) && $params["step"] == "confirm") {
                    //confirm and store in DB
                    //TODO WRITE the LOGIC to send the values array to the database, will either come here or in import IF

                    $this->values = unserialize($_SESSION['serValues']);
                    $this->fields = unserialize($_SESSION['serFields']);

                    $finalMappings = array();
                    foreach ($this->fields as $field) {
                        array_push($finalMappings, $field['sourceField']);
                        array_push($finalMappings, $field['leantimeField']);
                    }

                    if ($_SESSION['currentImportEntity'] == "tickets") {
                        foreach ($this->values as $row) {

                            $ticket = array();

                            for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {

                                $ticket[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                            }
                            $ticket['editorId'] = $row['editorId'];
                            $ticket['projectId'] = $row['projectId'];
                            if (!isset($ticket['status'])) {
                                $ticket['status'] = 3;
                            }
                            $this->ticketService->addTicket($ticket);
                        }
                    } else if ($_SESSION['currentImportEntity'] == "projects") {
                        $psettings = array('all', 'clients', 'restricted');
                        foreach ($this->values as $row) {
                            $values = array();
                            for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                                if ($finalMappings[$i + 1] == 'psettings') {
                                    if (!in_array($row[$finalMappings[$i]], $psettings)) {
                                        $row[$finalMappings[$i]] = 'restricted';
                                    }
                                }
                                $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                            }
                            $this->projectService->addProject($values);
                        }

                    } else if ($_SESSION['currentImportEntity'] == "users") {
                        //TODO add  users
                        foreach ($this->values as $row) {
                            $values = array();
                            for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                                if ($finalMappings[$i + 1] != 'sendInvite') {
                                    $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                                }
                            }
                            $values["notifications"] = 1;
                            $values['source'] = "csvImport"; //TODO: will have to change when other integrations are added
                            if ($row['sendInvite']) {
                                $this->userService->createUserInvite($values);
                            } else {
                                $values['status'] = 'a';
                                if (!$values['password']) {
                                    $tempPasswordVar = Uuid::uuid4()->toString();
                                    $values['password'] = $tempPasswordVar;
                                }
                                $this->userService->addUser($values);
                            }

                        }
                    } else if ($_SESSION['currentImportEntity'] == "ideas") {
                        //TODO add ideas
                        foreach ($this->values as $row) {
                            $values = array();
                            for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                                    $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                            }
                            $this->ideaRepository->addCanvasItem($values);

                        }
                    } else if ($_SESSION['currentImportEntity'] == "goals") {
                        foreach ($this->values as $row) {
                            $values = array();
                            for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                                $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                            }
                            $this->goalRepository->addCanvasItem($values);

                        }
                    } else if ($_SESSION['currentImportEntity'] == "milestones") {
                        //TODO add milestones
                        foreach ($this->values as $row) {

                            $ticket = array();

                            for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {

                                $ticket[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                            }
                            $ticket['editorId'] = $row['editorId'];
                            $ticket['projectId'] = $row['projectId'];
                            if (!isset($ticket['status'])) {
                                $ticket['status'] = 3;
                            }
                            $ticket['type'] = 'milestone';
                            $this->ticketService->addTicket($ticket);
                        }
                    }

                    //display stored successfully message
                    $this->tpl->display('connector.integrationConfirm');
                }


                if (isset($params["step"]) && $params["step"] == "import") {

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

                if ($_SESSION['currentImportEntity'] == "tickets") {

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

                    if ($headlineFlag) {
                        $flags[] = "You must have a headline column";
                    }

                    if ($matchingSourceField) {

                        foreach ($this->values as &$row) {
                            if (strpos($row[$matchingSourceField], '@') !== false) {

                                $id = $this->userService->getUserByEmail($row[$matchingSourceField])['id'];
                                if ($id) {
                                    $row['editorId'] = $id;
                                } else {
                                    $flags[] = $row[$matchingSourceField] . " " . "is not a valid User";
                                }
                            } else {
                                $flags[] = "The Author/userId column must contain only valid User emails";
                                break;
                            }
                        }
                    } else {
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

                } else if ($_SESSION['currentImportEntity'] == "projects") {
                    $matchingProjectNameSourceField = '';
                    $matchingClientIdSourceField = '';
                    $matchingUsersSourceField = '';

                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === 'name') {
                            $matchingProjectNameSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'clientId') {
                            $matchingClientIdSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'assignedUsers') {
                            $matchingUsersSourceField = $item['sourceField'];
                        }

                    }
                    if ($matchingUsersSourceField) {
                        foreach ($this->values as $row) {
                            $emails = explode(",", $row[$matchingUsersSourceField]);
                            $users = array();

                            foreach ($emails as $email) {
                                $user = $this->userService->getUserByEmail(trim($email));
                                if ($user) {
                                    $users[] = $user;
                                } else {
                                    $flags[] = $email . " is not a valid user.";
                                }
                            }
                            $row[$matchingUsersSourceField] = $users;
                        }
                    }

                    if (!$matchingProjectNameSourceField) {
                        $flags[] = "You must have a column with Project Names";
                    }
                    if (!$matchingClientIdSourceField) {
                        $flags[] = "You must have a column with ClientId";
                    }
                } else if ($_SESSION['currentImportEntity'] == "users") {
                    //TODO add import logic and validation
                    $matchingUsernameSourceField = '';
                    $matchingRoleSourceField = '';
                    $matchingSendInviteSourceField = "";
                    $matchingFirstNameSourceField = "";

                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === 'firstname') {
                            $matchingFirstNameSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'username') {
                            $matchingUsernameSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'role') {
                            $matchingRoleSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'sendInvite') {
                            $matchingSendInviteSourceField = $item['sourceField'];
                        }
                    }
                    $roles = array(5 => 'readonly',      //prev: none
                        10 => 'commenter',    //prev: client
                        20 => 'editor',       //prev: developer
                        30 => 'manager',      //prev: clientmanager
                        40 => 'admin',        //prev: manager
                        50 => 'owner',);

                    if ($matchingSendInviteSourceField && $matchingUsernameSourceField && $matchingRoleSourceField && $matchingFirstNameSourceField) {
                        foreach ($this->values as &$row) {
                            if (strtolower($row[$matchingSendInviteSourceField]) == "yes") {
                                $row['sendInvite'] = true;
                            } elseif (strtolower($row[$matchingSendInviteSourceField]) == "no") {
                                $row['sendInvite'] = false;

                            } else {
                                $flags[] = "The sendInvite column must contain only yes or no";
                            }
                            if (str_contains($row[$matchingUsernameSourceField], '@')) {
                                if ($this->userService->getUserByEmail(trim($row[$matchingUsernameSourceField]))) {
                                    $flags[] = $row[$matchingUsernameSourceField] . " is already a user.";
                                }
                            } else {
                                $flags[] = "The Username column must contain only valid emails";
                            }
                            $rolesKey = array_search(strtolower(trim($row[$matchingRoleSourceField])), $roles);
                            if (!$rolesKey) {
                                $flags[] = $row[$matchingRoleSourceField] . " is not a valid role.";
                            } else {
                                $row[$matchingRoleSourceField] = $rolesKey;
                            }

                        }
                    }
                    if (!$matchingSendInviteSourceField) {
                        $flags[] = "You must have a column specifying if the user should receive an invite.";
                    }
                    if (!$matchingUsernameSourceField) {
                        $flags[] = "You must have a column with Emails.";
                    }
                    if (!$matchingRoleSourceField) {
                        $flags[] = "You must have a column with Roles.";
                    }
                    if (!$matchingFirstNameSourceField) {
                        $flags[] = "You must have a column with First Names.";
                    }

                } else if ($_SESSION['currentImportEntity'] == "ideas") {
                    //TODO add import logic and validation

                    $matchingAuthorSourceField = '';
                    $matchingCanvasIdSourceField = '';
                    $matchingDataSourceField = '';
                    $matchingDescriptionSourceField = '';

                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === 'author') {
                            $matchingAuthorSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'canvasId') {
                            $matchingCanvasIdSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'description') {
                            $matchingDescriptionSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'data') {
                            $matchingDataSourceField = $item['sourceField'];
                        }
                    }

                    if ($matchingAuthorSourceField) {

                        foreach ($this->values as &$row) {
                            if (str_contains($row[$matchingAuthorSourceField], '@')) {

                                $id = $this->userService->getUserByEmail($row[$matchingAuthorSourceField])['id'];
                                if ($id) {
                                    $row['author'] = $id;
                                } else {
                                    $flags[] = $row[$matchingAuthorSourceField] . " " . "is not a valid User";
                                }
                            } else {
                                $flags[] = "The Author column must contain only valid User emails";
                                break;
                            }
                        }
                    } else {
                        $id = $_SESSION['userdata']['id'];
                        foreach ($this->values as &$row) {
                            $row['author'] = $id;
                        }
                    }
                    if($matchingCanvasIdSourceField){
                        if(!$this->canvasRepository->getSingleCanvas($row[$matchingCanvasIdSourceField])){
                            $flags[] = $row[$matchingCanvasIdSourceField] . " " . "is not a valid Canvas.";
                        }
                    }
                    if (!$matchingDataSourceField) {
                        $flags[] = "You must have a column matching to Data.";
                    }
                    if (!$matchingDescriptionSourceField) {
                        $flags[] = "You must have a column matching to Description.";
                    }
                    if (!$matchingCanvasIdSourceField) {
                        $flags[] = "You must have a column matching to CanvasId.";
                    }

                } else if ($_SESSION['currentImportEntity'] == "goals") {
                    //TODO add import logic and validation
                    $matchingStartValueSourceField = '';
                    $matchingCurrentValueSourceField = '';
                    $matchingCanvasIdSourceField = '';
                    $matchingEndValueSourceField = '';
                    $matchingTitleSourceField ='';

                    foreach ($this->fields as $item) {
                        if ($item['leantimeField'] === 'title') {
                            $matchingTitleSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'canvasId') {
                            $matchingCanvasIdSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'currentValue') {
                            $matchingCurrentValueSourceField = $item['sourceField'];
                        }
                        if ($item['leantimeField'] === 'startValue') {
                            $matchingStartValueSourceField = $item['sourceField'];
                        }
                        if($item['leantimeField'] === 'endValue'){
                            $matchingEndValueSourceField = $item['sourceField'];
                        }
                    }

                    if($matchingCanvasIdSourceField){
                        if(!$this->canvasRepository->getSingleCanvas($row[$matchingCanvasIdSourceField])){
                            $flags[] = $row[$matchingCanvasIdSourceField] . " " . "is not a valid Canvas.";
                        }
                    }

                    if(!$matchingTitleSourceField){
                        $flags[] = "You must have a column matching to Title.";
                    }
                    if(!$matchingCanvasIdSourceField){
                        $flags[] = "You must have a column matching to CanvasId.";
                    }
                    if(!$matchingCurrentValueSourceField){
                        $flags[] = "You must have a column matching to CurrentValue.";
                    }
                    if(!$matchingStartValueSourceField){
                        $flags[] = "You must have a column matching to StartValue.";
                    }
                    if(!$matchingEndValueSourceField){
                        $flags[] = "You must have a column matching to EndValue.";
                    }

                } else if ($_SESSION['currentImportEntity'] == "milestones") {
                    //TODO add import logic and validation
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

                    if ($headlineFlag) {
                        $flags[] = "You must have a headline column";
                    }

                    if ($matchingSourceField) {

                        foreach ($this->values as &$row) {
                            if (str_contains($row[$matchingSourceField], '@')) {

                                $id = $this->userService->getUserByEmail($row[$matchingSourceField])['id'];
                                if ($id) {
                                    $row['editorId'] = $id;
                                } else {
                                    $flags[] = $row[$matchingSourceField] . " " . "is not a valid User";
                                }
                            } else {
                                $flags[] = "The Author/userId column must contain only valid User emails";
                                break;
                            }
                        }
                    } else {
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
//
                            else {
                                $row['projectId'] = $projectId;
                            }
                        }

                    } else {
                        $flags[] = "You must have a column matching to a valid Project.";
                    }

                    $matchingEndDateSourceField = '';
                    $matchingStartDateSourceField = '';
                    foreach ($this->fields as $field){
                        if ($field['leantimeField'] === 'editFrom') {
                            $matchingStartDateSourceField = $field['sourceField'];
                        }
                        if($field['leantimeField'] === 'editTo'){
                            $matchingEndDateSourceField = $field['sourceField'];
                        }
                    }
                    if(!$matchingEndDateSourceField){
                        $flags[] = "You must have a column matching to a valid Edit To.";
                    }
                    if(!$matchingStartDateSourceField){
                        $flags[] = "You must have a column matching to a valid Edit From.";
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
