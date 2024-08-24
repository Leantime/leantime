<?php

namespace Leantime\Domain\Connector\Services {

    use Leantime\Domain\Auth\Models\Roles;
    use Leantime\Domain\Canvas\Repositories\Canvas;
    use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas;
    use Leantime\Domain\Ideas\Repositories\Ideas;
    use Leantime\Domain\Projects\Services\Projects;
    use Leantime\Domain\Tickets\Services\Tickets;
    use Leantime\Domain\Users\Services\Users;
    use Ramsey\Uuid\Uuid;

    /**
     *
     */
    class Connector
    {
        public function __construct(
            private Users $userService,
            private Canvas $canvasRepository,
            private Projects $projectService,
            private Tickets $ticketService,
            private \Leantime\Domain\Tickets\Repositories\Tickets $ticketRepository,
            private Goalcanvas $goalCanvasRepo,
            private Ideas $ideaRepo,
        ) {
        }

        public function getEntityFlags($entity)
        {
            $flags = array();
            if ($entity == "tickets") {
                $flags[] = "If you do not have an Editor (User) email/ID field then all imported entities will be assigned to you.";
                $flags[] = "Headline and Project are required fields.";
            } else {
                if ($entity == "projects") {
                    $flags[] = "If there are no Assigned Users, the project will be assigned to you.";
                    $flags[] = "If there are more than one assigned users, ensure that it is a comma separated list.";
                    $flags[] = "If you decide to set permissions settings they have to be one of the following types: all, clients or restricted.";
                    $flags[] = "Project Name and Client Id re required fields";
                } else {
                    if ($entity == "users") {
                        //TODO add flags for users
                        $flags[] = "First Name, Role, Email, and Send Invite are required fields";
                        $flags[] = "The Send Invite field should be either Yes/No or else the user will not be imported. If Send Invite is set to No the user will have to reset the password on their own.";
                        $flags[] = "Roles have to be one of the following values 'readonly', 'commenter', 'editor', 'manager', 'admin', 'owner'.";
                    } else {
                        if ($entity == "ideas") {
                            //TODO add flags for ideas
                            $flags[] = "Description, Data, and CanvasId are required fields.";
                            $flags[] = "If you do not have an Author field then you will be assigned as the Author.";
                        } else {
                            if ($entity == "goals") {
                                //TODO add flags for goals
                                $flags[] = "Title, CanvasId, Start Value, Current Value, and End Value are required fields";
                            } else {
                                if ($entity == "milestones") {
                                    //TODO add flags for milestones
                                    $flags[] = "If you do not have an Editor (User) email/ID field then all imported entities will be assigned to you.";
                                    $flags[] = "Headline, Edit From, Edit To and Project are required fields";
                                }
                            }
                        }
                    }
                }
            }

            return $flags;
        }


        public function getFieldMappings($postParams)
        {
            $fields = array();
            foreach ($postParams as $fieldmapping) {
                // Checking if the field mapping is selected
                if (
                    !empty($fieldmapping)
                    && strpos($fieldmapping, '|') !== false
                ) {
                        $mappingParts = explode("|", $fieldmapping);
                        $sourceField = $mappingParts[0];
                        $leantimeField = $mappingParts[1];

                        $fields[] = array("sourceField" => $sourceField, "leantimeField" => $leantimeField);
                }
            }

            return $fields;
        }

        public function parseValues($fields, $values, $entity)
        {
            if ($entity == "tickets") {
                return $this->parseTickets($fields, $values);
            } else if ($entity == "projects") {
                    return $this->parseProjects($fields, $values);
            } else if ($entity == "users") {
                return $this->parseUsers($fields, $values);
            } else if ($entity == "ideas") {
                return $this->parseIdeas($fields, $values);
            } else if ($entity == "goals") {
                return $this->parseGoals($fields, $values);
            } else if ($entity == "milestones") {
                return $this->parseMilestones($fields, $values);
            }

            return false;
        }

        private function parseTickets($fields, $values)
        {
            $matchingSourceField = '';
            $flags = array();

            foreach ($fields as $item) {
                if ($item['leantimeField'] === 'editorId') {
                    $matchingSourceField = $item['sourceField'];
                    break;
                }
            }

            $headlineFlag = true;

            foreach ($fields as $item) {
                if ($item['leantimeField'] === "headline") {
                    $headlineFlag = false;
                    break;
                }
            }

            if ($headlineFlag) {
                $flags[] = "You must have a headline column";
            }

            if ($matchingSourceField) {
                foreach ($values as &$row) {
                    if (strpos($row[$matchingSourceField], '@') !== false) {
                        $id = false;
                        if(isset($row[$matchingSourceField])){
                            $id = $this->userService->getUserByEmail(trim($row[$matchingSourceField]))["id"] ?? false;



                        }
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
                $id = session("userdata.id");
                foreach ($values as &$row) {
                    $row['editorId'] = $id;
                }
            }

            //PROJECT Name Check
            $matchingProjectSourceField = '';
            foreach ($fields as $item) {
                if ($item['leantimeField'] === 'projectName') {
                    $matchingProjectSourceField = $item['sourceField'];
                    break;
                }
            }

            if ($matchingProjectSourceField) {
                $allProjects = $this->projectService->getAllProjects();

                foreach ($values as &$row) {
                    $projectId = $this->projectService->getProjectIdByName(
                        $allProjects,
                        $row[$matchingProjectSourceField]
                    );
                    if (!$projectId) {
                        $flags[] = $row[$matchingProjectSourceField] . " " . "is not a valid Project";
                    } else {
                        $row['projectId'] = $projectId;
                        $statusLabels = $this->ticketService->getStatusLabels($projectId);
                    }
                }
            } else {
                $flags[] = "You must have a column matching to a valid Project.";
            }


            //Status Field Mapping
            $matchingStatusField = '';
            foreach ($fields as $item) {
                if ($item['leantimeField'] === 'status') {
                    $matchingStatusField = $item['sourceField'];
                    break;
                }
            }

            if ($matchingStatusField) {
                foreach ($values as &$row) {
                    $getStatus = $this->ticketRepository->getStatusIdByName($row[$matchingStatusField], $row['projectId'] ?? null);
                    if ($getStatus !== false) {
                        $row['status'] = $getStatus;
                    } else {
                        $row['status'] = 3;
                    }
                }
            }

            $this->cacheSerializedFieldValues($fields, $values);

            return $flags;
        }

        private function parseProjects($fields, $values)
        {
            $matchingProjectNameSourceField = '';
            $matchingClientIdSourceField = '';
            $matchingUsersSourceField = '';
            $flags = [];

            foreach ($fields as $item) {
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
                foreach ($values as $row) {
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

            $this->cacheSerializedFieldValues($fields, $values);

            return $flags;
        }

        private function parseUsers($fields, $values)
        {
            $matchingUsernameSourceField = '';
            $matchingRoleSourceField = '';
            $matchingSendInviteSourceField = "";
            $matchingFirstNameSourceField = "";
            $flags = [];

            foreach ($fields as $item) {
                if ($item['leantimeField'] === 'firstname') {
                    $matchingFirstNameSourceField = $item['sourceField'];
                }
                if ($item['leantimeField'] === 'user') {
                    $matchingUsernameSourceField = $item['sourceField'];
                }
                if ($item['leantimeField'] === 'role') {
                    $matchingRoleSourceField = $item['sourceField'];
                }
                if ($item['leantimeField'] === 'sendInvite') {
                    $matchingSendInviteSourceField = $item['sourceField'];
                }
            }

            if ($matchingSendInviteSourceField && $matchingUsernameSourceField && $matchingRoleSourceField && $matchingFirstNameSourceField) {
                foreach ($values as &$row) {
                    if (strtolower($row[$matchingSendInviteSourceField]) == "yes") {
                        $row['sendInvite'] = true;
                    } elseif (strtolower($row[$matchingSendInviteSourceField]) == "no") {
                        $row['sendInvite'] = false;
                    } else {
                        $flags[] = "The sendInvite column must contain only yes or no";
                    }
                    if (str_contains($row[$matchingUsernameSourceField], '@')) {
                        $user = $this->userService->getUserByEmail(
                            email: trim($row[$matchingUsernameSourceField]),
                            status: null
                        );
                        if ($user) {
                            $row['id'] = $user['id'];
                        }
                    } else {
                        $flags[] = "The Username column must contain only valid emails";
                    }
                    $rolesKey = array_search(
                        strtolower(trim($row[$matchingRoleSourceField])),
                        Roles::getRoles()
                    );
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

            $this->cacheSerializedFieldValues($fields, $values);

            return $flags;
        }

        private function parseIdeas($fields, $values)
        {

            $matchingAuthorSourceField = '';
            $matchingCanvasIdSourceField = '';
            $matchingDataSourceField = '';
            $matchingDescriptionSourceField = '';
            $flags = [];

            foreach ($fields as $item) {
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
                foreach ($values as &$row) {
                    if (str_contains($row[$matchingAuthorSourceField], '@')) {
                        $id = $this->userService->getUserByEmail(
                            $row[$matchingAuthorSourceField]
                        )['id'];
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
                $id = session("userdata.id");
                foreach ($values as &$row) {
                    $row['author'] = $id;
                }
            }
            if ($matchingCanvasIdSourceField) {
                foreach ($values as &$row) {
                    if (
                        !$this->ideaRepo->getSingleCanvas(
                            $row[$matchingCanvasIdSourceField]
                        )
                    ) {
                        $flags[] = $row[$matchingCanvasIdSourceField] . " " . "is not a valid Canvas.";
                    }
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

            $this->cacheSerializedFieldValues($fields, $values);

            return $flags;
        }

        private function parseGoals($fields, $values)
        {
            //TODO add import logic and validation
            $matchingStartValueSourceField = '';
            $matchingCurrentValueSourceField = '';
            $matchingCanvasIdSourceField = '';
            $matchingEndValueSourceField = '';
            $matchingTitleSourceField = '';
            $flags = [];

            foreach ($fields as $item) {
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
                if ($item['leantimeField'] === 'endValue') {
                    $matchingEndValueSourceField = $item['sourceField'];
                }
            }

            if ($matchingCanvasIdSourceField) {
                foreach ($values as &$row) {
                    if (
                        !$this->goalCanvasRepo->getSingleCanvas(
                            $row[$matchingCanvasIdSourceField]
                        )
                    ) {
                        $flags[] = $row[$matchingCanvasIdSourceField] . " " . "is not a valid Canvas.";
                    }
                }
            }

            if (!$matchingTitleSourceField) {
                $flags[] = "You must have a column matching to Title.";
            }
            if (!$matchingCanvasIdSourceField) {
                $flags[] = "You must have a column matching to CanvasId.";
            }
            if (!$matchingCurrentValueSourceField) {
                $flags[] = "You must have a column matching to CurrentValue.";
            }
            if (!$matchingStartValueSourceField) {
                $flags[] = "You must have a column matching to StartValue.";
            }
            if (!$matchingEndValueSourceField) {
                $flags[] = "You must have a column matching to EndValue.";
            }

            $this->cacheSerializedFieldValues($fields, $values);

            return $flags;
        }

        private function parseMilestones($fields, $values)
        {
            $matchingSourceField = '';
            $flags = [];

            foreach ($fields as $item) {
                if ($item['leantimeField'] === 'editorId') {
                    $matchingSourceField = $item['sourceField'];
                    break;
                }
            }

            $headlineFlag = true;

            foreach ($fields as $item) {
                if ($item['leantimeField'] === "headline") {
                    $headlineFlag = false;
                    break;
                }
            }

            if ($headlineFlag) {
                $flags[] = "You must have a headline column";
            }

            if ($matchingSourceField) {
                foreach ($values as &$row) {
                    if (str_contains($row[$matchingSourceField], '@')) {
                        $id = $this->userService->getUserByEmail(
                            $row[$matchingSourceField]
                        )['id'];
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
                $id = session("userdata.id");
                foreach ($values as &$row) {
                    $row['editorId'] = $id;
                }
            }

            //PROJECT Name Check
            $matchingProjectSourceField = '';

            foreach ($fields as $item) {
                if ($item['leantimeField'] === 'projectName') {
                    $matchingProjectSourceField = $item['sourceField'];
                    break;
                }
            }

            if ($matchingProjectSourceField) {
                $allProjects = $this->projectService->getAllProjects();

                foreach ($values as &$row) {
                    $projectId = $this->projectService->getProjectIdByName(
                        $allProjects,
                        $row[$matchingProjectSourceField]
                    );
                    if (!$projectId) {
                        $flags[] = $row[$matchingProjectSourceField] . " " . "is not a valid Project";
                    } //
                    else {
                        $row['projectId'] = $projectId;
                    }
                }
            } else {
                $flags[] = "You must have a column matching to a valid Project.";
            }

            $matchingEndDateSourceField = '';
            $matchingStartDateSourceField = '';
            foreach ($fields as $field) {
                if ($field['leantimeField'] === 'editFrom') {
                    $matchingStartDateSourceField = $field['sourceField'];
                }
                if ($field['leantimeField'] === 'editTo') {
                    $matchingEndDateSourceField = $field['sourceField'];
                }
            }
            if (!$matchingEndDateSourceField) {
                $flags[] = "You must have a column matching to a valid Edit To.";
            }
            if (!$matchingStartDateSourceField) {
                $flags[] = "You must have a column matching to a valid Edit From.";
            }

            $this->cacheSerializedFieldValues($fields, $values);

            return $flags;
        }

        public function importValues($fields, $values, $entity)
        {

            $finalMappings = array();
            foreach ($fields as $field) {
                array_push($finalMappings, $field['sourceField']);
                array_push($finalMappings, $field['leantimeField']);
            }

            if ($entity == "tickets") {
                return $this->importTickets($finalMappings, $values);
            } else if ($entity == "projects") {
                return $this->importProjects($finalMappings, $values);
            } else if ($entity == "users") {
                return $this->importUsers($finalMappings, $values);
            } else if ($entity == "ideas") {
                return $this->importIdeas($finalMappings, $values);
            } else if ($entity == "goals") {
                return $this->importGoals($finalMappings, $values);
            } else if ($entity == "milestones") {
                return $this->importMilestones($finalMappings, $values);
            }

            return false;
        }

        private function importTickets($finalMappings, $finalValues)
        {
            foreach ($finalValues as $row) {
                $ticket = array();

                for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                    $ticket[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                }
                $ticket['editorId'] = $row['editorId'] ?? '';
                $ticket['projectId'] = $row['projectId'] ?? '';
                $ticket['status'] = $row['status'] ?? 3;
                $ticket['type'] = $row['type'] ?? 'task';

                if (isset($ticket["id"]) && is_numeric($ticket["id"])) {
                    $this->ticketService->updateTicket($ticket);
                } else {
                    $this->ticketService->addTicket($ticket);
                }
            }
            return;
        }

        private function importProjects($finalMappings, $finalValues)
        {
            $psettings = array('all', 'clients', 'restricted');
            foreach ($finalValues as $row) {
                $values = array();
                for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                    if ($finalMappings[$i + 1] == 'psettings') {
                        if (!in_array($row[$finalMappings[$i]], $psettings)) {
                            $row[$finalMappings[$i]] = 'restricted';
                        }
                    }
                    $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                }
                if (isset($values["id"])) {
                    $this->projectService->editProject($values, $values["id"],);
                } else {
                    $this->projectService->addProject($values);
                }
            }
        }

        private function importUsers($finalMappings, $finalValues)
        {
            //TODO add  users
            foreach ($finalValues as $row) {
                $values = array();
                for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                    if ($finalMappings[$i + 1] != 'sendInvite' || $finalMappings[$i + 1] != 'id') {
                        $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                    }
                }
                $values["notifications"] = 1;
                $values['source'] = "csvImport"; //TODO: will have to change when other integrations are added
                if (isset($row['id']) && $row['id'] > 0) {
                    $currentUser = $this->userService->getUser($row['id']);
                    $currentUser['user'] = $values['user'];
                    foreach ($currentUser as $key => &$userValues) {
                        if (isset($values[$key])) {
                            $userValues = $values[$key];
                        }
                    }
                    $this->userService->editUser($currentUser, $row['id']);
                } else if (isset($row['sendInvite']) && $row['sendInvite'] == true) {
                    $this->userService->createUserInvite($values);
                } else {
                    $values['status'] = 'a';
                    if (!isset($values['password'])) {
                        $tempPasswordVar =  Uuid::uuid4()->toString();
                        $values['password'] = $tempPasswordVar;
                    }
                    $this->userService->addUser($values);
                }
            }
        }

        private function importIdeas($finalMappings, $finalValues)
        {

            foreach ($finalValues as $row) {
                $values = array();
                for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                    $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                }
                if (isset($values["itemId"])) {
                    $this->ideaRepo->editCanvasItem($values);
                } else {
                    $this->ideaRepo->addCanvasItem($values);
                }
            }
        }

        private function importGoals($finalMappings, $finalValues)
        {
            foreach ($finalValues as $row) {
                $values = array();
                for ($i = 0; $i < sizeof($finalMappings); $i = $i + 2) {
                    $values[$finalMappings[$i + 1]] = $row[$finalMappings[$i]];
                }
                $values["box"] = "goal";
                if (!isset($values['author'])) {
                    $values['author'] = session("userdata.id");
                }
                if (isset($values["itemId"])) {
                    $this->goalCanvasRepo->editCanvasItem($values);
                } else {
                    $this->goalCanvasRepo->addCanvasItem($values);
                }
            }
        }

        private function importMilestones($finalMappings, $finalValues)
        {
            //TODO add milestones
            foreach ($finalValues as $row) {
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
                if (isset($ticket["id"])) {
                    $this->ticketService->updateTicket($ticket);
                } else {
                    $this->ticketService->addTicket($ticket);
                }
            }
        }

        private function cacheSerializedFieldValues($fields, $values)
        {

            $serializedFields = serialize($fields);
            $serializedValues = serialize($values);

            session(["serFields" => $serializedFields]);
            session(["serValues" => $serializedValues]);
        }

    }
}
