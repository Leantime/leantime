<?php

/**
 * Repository
 */

namespace Leantime\Domain\Connector\Repositories {

    use Leantime\Domain\Connector\Models\FieldTypes;

    class LeantimeEntities
    {
        public array $availableLeantimeEntities = [];

        public function __construct()
        {

            // TODO: there's gotta be a better way to manage these fields using the tickets model we already have.
            $this->availableLeantimeEntities = [
                'tickets' => [
                    'name' => 'To-Dos',
                    'fields' => [
                        'id' => ['name' => 'Id', 'accepts' => fieldTypes::$int, 'default' => 0],
                        'headline' => ['name' => 'Title', 'accepts' => fieldTypes::$shortString, 'default' => ''],
                        'description' => ['name' => 'Description', 'accepts' => fieldTypes::$text, 'default' => ''],
                        'type' => ['name' => 'Type', 'accepts' => fieldTypes::$shortString, 'restrict' => ['bug', 'task', 'story'], 'default' => ''],
                        'editorId' => ['name' => 'Assigned To', 'accepts' => fieldTypes::$email, 'default' => ''],
                        'priority' => ['name' => 'Priority', 'accepts' => fieldTypes::$shortString, 'restrict' => ['high'], 'default' => ''],
                        'date' => ['name' => 'Created On', 'accepts' => fieldTypes::$dateTime, 'default' => ''],
                        'dateToFinish' => ['name' => 'Due Date', 'accepts' => fieldTypes::$dateTime, 'default' => ''],
                        'status' => ['name' => 'Status', 'accepts' => fieldTypes::$shortString, 'restrict' => []],
                        'storypoints' => ['name' => 'Effort', 'accepts' => fieldTypes::$shortString, 'restrict' => ['xxl', 'xl']],
                        'hourRemaining' => ['name' => 'Hours Remaining', 'accepts' => fieldTypes::$int, 'default' => ''],
                        'planHours' => ['name' => 'Plan Hours', 'accepts' => fieldTypes::$int, 'default' => ''],
                        'sprint' => ['name' => 'Sprint', 'accepts' => fieldTypes::$shortString, 'default' => ''],
                        'tags' => ['name' => 'Tags', 'accepts' => fieldTypes::$text, 'default' => ''],
                        'editFrom' => ['name' => 'Edit From', 'accepts' => fieldTypes::$dateTime, 'default' => ''],
                        'editTo' => ['name' => 'Edit To', 'accepts' => fieldTypes::$dateTime, 'default' => ''],
                        'milestoneid' => ['name' => 'Milestone', 'accepts' => fieldTypes::$shortString, 'default' => ''],
                        'projectName' => ['name' => 'Project', 'accepts' => fieldTypes::$shortString, 'default' => ''],
                    ],
                ],
                'projects' => [
                    'name' => 'Projects',
                    'fields' => [
                        'id' => ['name' => 'Id'],
                        'name' => ['name' => 'Project Name'],
                        'details' => ['name' => 'Details'],
                        'clientId' => ['name' => 'ClientId'],
                        'hourBudget' => ['name' => 'Hour Budget'],
                        'assignedUsers' => ['name' => 'Assigned Users'],
                        'dollarBudget' => ['name' => 'Dollar Budget'],
                        'psettings' => ['name' => 'Permission Settings'],
                        'start' => ['name' => 'Start Date'],
                        'end' => ['name' => 'End Date'],
                    ],
                ],
                'users' => [
                    'name' => 'Users',
                    'fields' => [
                        'firstname' => ['name' => 'First Name'],
                        'lastname' => ['name' => 'Last Name'],
                        'phone' => ['name' => 'Phone'],
                        'user' => ['name' => 'Email'],
                        'role' => ['name' => 'Role'],
                        'clientId' => ['name' => 'ClientId'],
                        'password' => ['name' => 'Password'],
                        'jobTitle' => ['name' => 'Job Title'],
                        'jobLevel' => ['name' => 'Job Level'],
                        'department' => ['name' => 'Department'],
                        'sendInvite' => ['name' => 'Send Invite'],
                    ],
                ],
                'ideas' => [
                    'name' => 'Ideas',
                    'fields' => [
                        'itemId' => ['name' => 'Id'],
                        'description' => ['name' => 'Title'],
                        'data' => ['name' => 'Description'],
                        'author' => ['name' => 'Author'],
                        'status' => ['name' => 'Status'],
                        'canvasId' => ['name' => 'CanvasId'],
                        'milestoneId' => ['name' => 'MilestoneId'],
                    ],
                ],
                'goals' => [
                    'name' => 'Goals',
                    'fields' => [
                        'itemId' => ['name' => 'Id'],
                        'title' => ['name' => 'Title'], // required
                        'description' => ['name' => 'Metric'],
                        'status' => ['name' => 'Status'],
                        'relates' => ['name' => 'Relates'],
                        'startValue' => ['name' => 'Start Value'], // required
                        'currentValue' => ['name' => 'Current Value'], // required
                        'canvasId' => ['name' => 'CanvasId'], // required
                        'endValue' => ['name' => 'End Value'], // required
                        'kpi' => ['name' => 'Strategy KPI'],
                        'startDate' => ['name' => 'Start Date'],
                        'endDate' => ['name' => 'End Date'],
                        'setting' => ['name' => 'Setting'],
                        'metricType' => ['name' => 'Metric Type'], // should be number percent or currency
                        'assignedTo' => ['name' => 'Assigned To'],
                        'parent' => ['name' => 'Parent'],
                    ],
                ],
                'milestones' => [
                    'name' => 'Milestones',
                    'fields' => [
                        'id' => ['name' => 'id', 'accepts' => fieldTypes::$int, 'default' => 0],
                        'headline' => ['name' => 'Title', 'accepts' => fieldTypes::$shortString, 'default' => ''],
                        'description' => ['name' => 'Description', 'accepts' => fieldTypes::$text, 'default' => ''],
                        'editorId' => ['name' => 'Assigned To', 'accepts' => fieldTypes::$email, 'default' => ''],
                        'priority' => ['name' => 'Priority', 'accepts' => fieldTypes::$shortString, 'restrict' => ['high'], 'default' => ''],
                        'status' => ['name' => 'Status', 'accepts' => fieldTypes::$shortString, 'restrict' => []],
                        'storypoints' => ['name' => 'Effort', 'accepts' => fieldTypes::$shortString, 'restrict' => ['xxl', 'xl']],
                        'hourRemaining' => ['name' => 'Hours Remaining', 'accepts' => fieldTypes::$int, 'default' => ''],
                        'planHours' => ['name' => 'Plan Hours', 'accepts' => fieldTypes::$int, 'default' => ''],
                        'sprint' => ['name' => 'Sprint', 'accepts' => fieldTypes::$shortString, 'default' => ''],
                        'tags' => ['name' => 'T ags', 'accepts' => fieldTypes::$text, 'default' => ''],
                        'editFrom' => ['name' => 'Edit From', 'accepts' => fieldTypes::$dateTime, 'default' => ''],
                        'editTo' => ['name' => 'Edit To', 'accepts' => fieldTypes::$dateTime, 'default' => ''],
                        'projectName' => ['name' => 'Project', 'accepts' => fieldTypes::$shortString, 'default' => ''],
                    ],
                ],

            ];
        }
    }
}
