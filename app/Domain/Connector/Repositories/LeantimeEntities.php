<?php

/**
 * Repository
 */

namespace Leantime\Domain\Connector\Repositories {

    use Leantime\Domain\Connector\Models\FieldTypes;

    class LeantimeEntities
    {
        public array $availableLeantimeEntities = array();

        public function __construct()
        {

            //TODO: there's gotta be a better way to manage these fields using the tickets model we already have.
            $this->availableLeantimeEntities = array(
               "tickets" => array(
                   "name" => "To-Dos",
                   "fields" => array(
                       "id" =>             ["name" => "id", "accepts" => FieldTypes::$int, "default" => 0],
                       "headline" =>       ["name" => "headline", "accepts" => FieldTypes::$shortString, "default" => ''],
                       "description" =>    ["name" => "description", "accepts" => FieldTypes::$text, "default" => ''],
                       "type" =>           ["name" => "type", "accepts" => FieldTypes::$shortString, "restrict" => array("bug", "task", "story"), "default" => ''],
                       "userId" =>         ["name" => "Author", "accepts" => FieldTypes::$email, "default" => ""],
                       "editorId" =>       ["name" => "Editor", "accepts" => FieldTypes::$email, "default" => ""],
                       "priority" =>       ["name" => "Priority", "accepts" => FieldTypes::$shortString, "restrict" => array("high"), "default" => ''],
                       "date" =>           ["name" => "Created On", "accepts" => FieldTypes::$dateTime, "default" => ""],
                       "dateToFinish" =>   ["name" => "Due Date", "accepts" => FieldTypes::$dateTime, "default" => ""],
                       "status" =>         ["name" => "Status", "accepts" => FieldTypes::$shortString, "restrict" => array()],
                       "storypoints" =>    ["name" => "Effort", "accepts" => FieldTypes::$shortString, "restrict" => array("xxl", "xl")],
                       "hourRemaining" =>  ["name" => "Hours Remaining", "accepts" => FieldTypes::$int, "default" => ""],
                       "planHours" =>      ["name" => "Plan Hours", "accepts" => FieldTypes::$int, "default" => ""],
                       "sprint" =>         ["name" => "Sprint", "accepts" => FieldTypes::$shortString, "default" => ""],
                       "tags" =>           ["name" => "tags", "accepts" => FieldTypes::$text, "default" => ""],
                       "editFrom" =>       ["name" => "Edit From", "accepts" => FieldTypes::$dateTime, "default" => ""],
                       "editTo" =>         ["name" => "Edit To", "accepts" => FieldTypes::$dateTime, "default" => ""],
                       "milestoneid" =>   ["name" => "Milestone", "accepts" => FieldTypes::$shortString, "default" => ""],
                       "projectName" =>    ["name" => "Project", "accepts" => FieldTypes::$shortString, "default" => ""],
                   ),
               ),
               "projects" => array("name" => "Projects"),
               "users" => array("name" => "Users"),
               "ideas" => array("name" => "Ideas"),
               "goals" => array("name" => "Goals"),
               "milestones" => array("name" => "Milestones"),

            );
        }
    }
}
