<?php

/**
 * Repository
 */

namespace leantime\domain\repositories\connector {

    use leantime\domain\models\connector\fieldTypes;

    class leantimeEntities
    {

         public array $availableLeantimeEntities = array();

         public function __construct() {

             //TODO: there's gotta be a better way to manage these fields using the tickets model we already have.
             $this->availableLeantimeEntities = array(
                "tickets" => array(
                    "name" => "To-Dos",
                    "fields" => array(
                        "id" =>             ["name" => "id", "accepts" => fieldTypes::$int, "default" => 0],
                        "headline" =>       ["name" => "headline", "accepts" => fieldTypes::$shortString, "default" => ''],
                        "description" =>    ["name" => "description", "accepts" => fieldTypes::$text, "default" => ''],
                        "type" =>           ["name" => "type", "accepts" => fieldTypes::$shortString, "restrict" => array("bug", "task", "story"), "default" => ''],
                        "editorId" =>       ["name" => "Editor", "accepts" => fieldTypes::$email, "default" => ""],
                        "priority" =>       ["name" => "Priority", "accepts" => fieldTypes::$shortString, "restrict" => array("high"), "default" => ''],
                        "date" =>           ["name" => "Created On", "accepts" => fieldTypes::$dateTime, "default" => ""],
                        "dateToFinish" =>   ["name" => "Due Date", "accepts" => fieldTypes::$dateTime, "default" => ""],
                        "status" =>         ["name" => "Status", "accepts" => fieldTypes::$shortString, "restrict" => array()],
                        "storypoints" =>    ["name" => "Effort", "accepts" => fieldTypes::$shortString, "restrict" => array("xxl", "xl")],
                        "hourRemaining" =>  ["name" => "Hours Remaining", "accepts" => fieldTypes::$int, "default" => ""],
                        "planHours" =>      ["name" => "Plan Hours", "accepts" => fieldTypes::$int, "default" => ""],
                        "sprint" =>         ["name" => "Sprint", "accepts" => fieldTypes::$shortString, "default" => ""],
                        "tags" =>           ["name" => "tags", "accepts" => fieldTypes::$text, "default" => ""],
                        "editFrom" =>       ["name" => "Edit From", "accepts" => fieldTypes::$dateTime, "default" => ""],
                        "editTo" =>         ["name" => "Edit To", "accepts" => fieldTypes::$dateTime, "default" => ""],
                        "milestoneid" =>   ["name" => "Milestone", "accepts" => fieldTypes::$shortString, "default" => ""],
                        "projectName" =>    ["name" => "Project", "accepts" => fieldTypes::$shortString, "default" => ""],
                    )
                ),
                "projects" => array(
                    "name" => "Projects",
                    "fields" => array(
                        "name" => ["name" => "Project Name"],
                        'details' => ["name" => "Details"],
                        'clientId' => ["name" => "ClientId"],
                        'hourBudget' => ["name" => "Hour Budget"],
                        'assignedUsers' => ["name" => "Assigned Users"],
                        'dollarBudget' => ["name" => "Dollar Budget"],
                        'psettings' => ["name" => "Permission Settings"],
                        'start' => ["name" => "Start Date"],
                        'end' => ["name" => "End Date"],
                        ),
                ),
                 "users" => array(
                     "name" => "Users",
                     "fields" => array(
                         "firstname" => ["name" => "First Name"],
                         "lastname" => ["name" => "Last Name"],
                         "phone" => ["name" => "Phone"],
                         "username" => ["name" => "Email"],
                         "role" => ["name" => "Role"],
                         "clientId" => ["name" => "ClientId"],
                         "password" => ["name" => "Password"],
                         "jobTitle" => ["name" => "Job Title"],
                         "jobLevel" => ["name" => "Job Level"],
                         "department" => ["name" => "Department"],
                         "sendInvite" => ["name" => "Send Invite"],
                     )
                 ),
                 "ideas" => array(
                     "name" => "Ideas",
                     "fields" => array(
                         "description" => ["name" => "Description"],
                         "data" => ["name" => "Data"],
                         "author" => ["name" => "Author"],
                         "status" => ["name" => "Status"],
                         "canvasId" => ["name" => "CanvasId"],
                         "milestoneId" => ["name" => "MilestoneId"],
                     )),
                "goals" => array("name" => "Goals",
                                "field" => array(
                                    'box' => "goal",
                                    'title' => ["name" => "title"], //required
                                    'description' => ["name" => "Description"],
                                    'status' =>  ["name" => "Status"],
                                    'relates' => ["name" => "Relates"],
                                    'startValue' => ["name" => "Start Value"], //required
                                    'currentValue' => ["name" => "Current Value"], //required
                                    'canvasId' => ["name" => "CanvasId"], //required
                                    'endValue' => ["name" => "End Value"], //required
                                    'kpi' => ["name" => "KPI"],
                                    'startDate' => ["name" => "Start Date"],
                                    'endDate' => ["name" => "End Date"],
                                    'setting' => ["name" => "Setting"],
                                    'metricType' =>  ["name" => "Metric Type"], //should be number percent or currency
                                    'assignedTo' => ["name" => "Assigned To"],
                                    'parent' => ["name" => "Parent"],
                                )),
                "milestones" => array(
                    "name" => "Milestones",
                    "fields" => array(
                 "id" =>             ["name" => "id", "accepts" => fieldTypes::$int, "default" => 0],
                 "headline" =>       ["name" => "headline", "accepts" => fieldTypes::$shortString, "default" => ''],
                 "description" =>    ["name" => "description", "accepts" => fieldTypes::$text, "default" => ''],
                 "editorId" =>       ["name" => "Editor", "accepts" => fieldTypes::$email, "default" => ""],
                 "priority" =>       ["name" => "Priority", "accepts" => fieldTypes::$shortString, "restrict" => array("high"), "default" => ''],
                 "status" =>         ["name" => "Status", "accepts" => fieldTypes::$shortString, "restrict" => array()],
                 "storypoints" =>    ["name" => "Effort", "accepts" => fieldTypes::$shortString, "restrict" => array("xxl", "xl")],
                 "hourRemaining" =>  ["name" => "Hours Remaining", "accepts" => fieldTypes::$int, "default" => ""],
                 "planHours" =>      ["name" => "Plan Hours", "accepts" => fieldTypes::$int, "default" => ""],
                 "sprint" =>         ["name" => "Sprint", "accepts" => fieldTypes::$shortString, "default" => ""],
                 "tags" =>           ["name" => "tags", "accepts" => fieldTypes::$text, "default" => ""],
                 "editFrom" =>       ["name" => "Edit From", "accepts" => fieldTypes::$dateTime, "default" => ""],
                 "editTo" =>         ["name" => "Edit To", "accepts" => fieldTypes::$dateTime, "default" => ""],
                 "projectName" =>    ["name" => "Project", "accepts" => fieldTypes::$shortString, "default" => ""],
             ))

            );
        }
    }
}
