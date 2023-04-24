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
                        "userId" =>         ["name" => "Author", "accepts" => fieldTypes::$email, "default" => ""],
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
                "projects" => array("name" => "Projects"),
                "users" => array("name" => "Users"),
                "ideas" => array("name" => "Ideas"),
                "goals" => array("name" => "Goals"),
                "milestones" => array("name" => "Milestones")

            );
        }
    }
}
