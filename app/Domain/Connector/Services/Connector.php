<?php

namespace Leantime\Domain\Connector\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Language;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Connector\Models\ImportResult;
use Leantime\Domain\Connector\Repositories\LeantimeEntities;
use Leantime\Domain\Goalcanvas\Repositories\Goalcanvas;
use Leantime\Domain\Ideas\Repositories\Ideas;
use Leantime\Domain\Projects\Services\Projects;
use Leantime\Domain\Tickets\Services\Tickets;
use Leantime\Domain\Users\Services\Users;
use Ramsey\Uuid\Uuid;

class Connector
{
    /**
     * Key of a row's spreadsheet row number, when the provider knows it (a file whose headings
     * aren't on row 1, or with empty rows). Not a column: mapping never offers it.
     */
    public const ROW_KEY = '__sheetRow';

    /** Leantime fields several columns can be mapped to; their values are combined. */
    private const MULTI_COLUMN_FIELDS = ['tags'];

    /** @var array<int|string, int> Spreadsheet row number per record index of the rows being handled. */
    private array $sheetRows = [];

    /**
     * Leantime fields that hold a date or date-time.
     */
    private const DATE_FIELDS = ['date', 'dateToFinish', 'editFrom', 'editTo'];

    public function __construct(
        private Users $userService,
        private Projects $projectService,
        private Tickets $ticketService,
        private \Leantime\Domain\Tickets\Repositories\Tickets $ticketRepository,
        private Goalcanvas $goalCanvasRepo,
        private Ideas $ideaRepo,
        private Language $language,
        private ?ReviewedRows $reviewedRows = null,
    ) {
        $this->reviewedRows ??= new ReviewedRows;
    }

    public function getEntityFlags($entity)
    {
        $flags = [];
        if ($entity == 'tickets') {
            $flags[] = 'If you do not have an Editor (User) email/ID field then all imported entities will be assigned to you.';
            $flags[] = 'Headline and Project are required fields.';
        } else {
            if ($entity == 'projects') {
                $flags[] = 'If there are no Assigned Users, the project will be assigned to you.';
                $flags[] = 'If there are more than one assigned users, ensure that it is a comma separated list.';
                $flags[] = 'If you decide to set permissions settings they have to be one of the following types: all, clients or restricted.';
                $flags[] = 'Project Name and Client Id re required fields';
            } else {
                if ($entity == 'users') {
                    // TODO add flags for users
                    $flags[] = 'First Name, Role, Email, and Send Invite are required fields';
                    $flags[] = 'The Send Invite field should be either Yes/No or else the user will not be imported. If Send Invite is set to No the user will have to reset the password on their own.';
                    $flags[] = "Roles have to be one of the following values 'readonly', 'commenter', 'editor', 'manager', 'admin', 'owner'.";
                } else {
                    if ($entity == 'ideas') {
                        // TODO add flags for ideas
                        $flags[] = 'Description, Data, and CanvasId are required fields.';
                        $flags[] = 'If you do not have an Author field then you will be assigned as the Author.';
                    } else {
                        if ($entity == 'goals') {
                            // TODO add flags for goals
                            $flags[] = 'Title, CanvasId, Start Value, Current Value, and End Value are required fields';
                        } else {
                            if ($entity == 'milestones') {
                                // TODO add flags for milestones
                                $flags[] = 'If you do not have an Editor (User) email/ID field then all imported entities will be assigned to you.';
                                $flags[] = 'Headline, Edit From, Edit To and Project are required fields';
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
        $fields = [];
        foreach ($postParams as $fieldmapping) {
            // Checking if the field mapping is selected
            if (
                ! empty($fieldmapping)
                && strpos($fieldmapping, '|') !== false
            ) {
                $mappingParts = explode('|', $fieldmapping);
                $sourceField = $mappingParts[0];
                $leantimeField = $mappingParts[1];

                $fields[] = ['sourceField' => $sourceField, 'leantimeField' => $leantimeField];
            }
        }

        return $fields;
    }

    /** The entity being reviewed; stored with the reviewed rows so the import uses the same one. */
    private string $reviewedEntity = '';

    /**
     * Reviews the mapped rows of one import and stores them for the import step when nothing
     * is flagged.
     *
     * @param  array  $fields  Column mappings (sourceField, leantimeField).
     * @param  array  $values  Rows from the file.
     * @param  string  $entity  Entity key (tickets, projects, users, ideas, goals, milestones).
     * @return array|false The review flags, or false for an unknown entity.
     */
    public function parseValues($fields, $values, $entity)
    {
        $this->reviewedEntity = (string) $entity;
        $this->rememberSheetRows($values);

        $flags = match ($entity) {
            'tickets' => $this->parseTickets($fields, $values),
            'projects' => $this->parseProjects($fields, $values),
            'users' => $this->parseUsers($fields, $values),
            'ideas' => $this->parseIdeas($fields, $values),
            'goals' => $this->parseGoals($fields, $values),
            'milestones' => $this->parseMilestones($fields, $values),
            default => false,
        };

        if ($flags === false) {
            return false;
        }

        // A field that takes one value can't come from two columns; nothing is stored for import.
        $mappedTwice = $this->mappedTwiceFlags($fields, (string) $entity);
        if ($mappedTwice !== []) {
            $this->reviewedRows->forget();

            return array_merge($mappedTwice, $flags);
        }

        return $flags;
    }

    /**
     * Flags every field, other than the ones that combine values (Tags), that more than one
     * column is mapped to.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields
     * @param  string  $entity  The entity, for the fields' names.
     * @return array<int, string>
     */
    private function mappedTwiceFlags(array $fields, string $entity): array
    {
        $fieldNames = app()->make(LeantimeEntities::class)->availableLeantimeEntities[$entity]['fields'] ?? [];

        $columns = [];
        foreach ($fields as $item) {
            $columns[$item['leantimeField']][] = $item['sourceField'];
        }

        $flags = [];
        foreach ($columns as $leantimeField => $sourceFields) {
            if (count($sourceFields) > 1 && ! in_array($leantimeField, self::MULTI_COLUMN_FIELDS, true)) {
                $flags[] = sprintf(
                    $this->language->__('notification.import_mapped_twice'),
                    $fieldNames[$leantimeField]['name'] ?? $leantimeField,
                    implode(', ', $sourceFields)
                );
            }
        }

        return $flags;
    }

    /**
     * Remembers the spreadsheet row number of each record, for messages.
     *
     * @param  array<int|string, mixed>  $values  Rows, keyed as they will be looped over.
     */
    private function rememberSheetRows(array $values): void
    {
        $this->sheetRows = [];
        foreach ($values as $index => $row) {
            if (is_array($row) && isset($row[self::ROW_KEY]) && is_numeric($row[self::ROW_KEY])) {
                $this->sheetRows[$index] = (int) $row[self::ROW_KEY];
            }
        }
    }

    private function parseTickets($fields, $values)
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
            if ($item['leantimeField'] === 'headline') {
                $headlineFlag = false;
                break;
            }
        }

        if ($headlineFlag) {
            $flags[] = 'You must have a headline column';
        }

        if ($matchingSourceField) {
            foreach ($values as &$row) {
                if (strpos($row[$matchingSourceField], '@') !== false) {
                    $id = false;
                    if (isset($row[$matchingSourceField])) {
                        $id = $this->userService->getUserByEmail(trim($row[$matchingSourceField]))['id'] ?? false;

                    }
                    if ($id) {
                        $row['editorId'] = $id;
                    } else {
                        $flags[] = $row[$matchingSourceField].' '.'is not a valid User';
                    }
                } else {
                    $flags[] = 'The Author/userId column must contain only valid User emails';
                    break;
                }
            }
        } else {
            $id = session('userdata.id');
            foreach ($values as &$row) {
                $row['editorId'] = $id;
            }
        }

        // PROJECT Name Check
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
                if (! $projectId) {
                    $flags[] = $row[$matchingProjectSourceField].' '.'is not a valid Project';
                } else {
                    $row['projectId'] = $projectId;
                    $statusLabels = $this->ticketService->getStatusLabels($projectId);
                }
            }
        } else {
            $flags[] = 'You must have a column matching to a valid Project.';
        }

        // Status Field Mapping
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
                // An unresolved status stays null: a new ticket then gets its project's NEW status
                // and an existing ticket keeps its stored status (instead of being forced to 3,
                // which may not exist in a project with a custom status set).
                $row['status'] = $getStatus !== false ? $getStatus : null;
            }
        }

        // Every mapped date column is checked, not only the first one.
        array_push($flags, ...$this->dateFlags($fields, $values));

        // Type: only the to-do types Leantime has. Subtasks need a parent, which a file can't
        // name yet, so they can't be imported.
        $matchingTypeField = '';
        foreach ($fields as $item) {
            if ($item['leantimeField'] === 'type') {
                $matchingTypeField = $item['sourceField'];
                break;
            }
        }

        if ($matchingTypeField) {
            $validTypes = $this->importableTicketTypes();
            foreach ($values as $index => $typeRow) {
                $type = strtolower(trim((string) ($typeRow[$matchingTypeField] ?? '')));
                if ($type !== '' && ! in_array($type, $validTypes, true)) {
                    $flags[] = sprintf(
                        $this->language->__('notification.import_row_invalid_type'),
                        $this->rowNumber($index),
                        $typeRow[$matchingTypeField],
                        implode(', ', $validTypes)
                    );
                }
            }
        }

        // Assignees must be on the project's team, or the save fails at import time. Checking it
        // here shows the problem on the review step, before anything is written. Like the ticket
        // service, only a new assignee is checked: an update that keeps a to-do's assignee saves
        // even if that person has left the team since.
        $matchingIdField = '';
        foreach ($fields as $item) {
            if ($item['leantimeField'] === 'id') {
                $matchingIdField = $item['sourceField'];
                break;
            }
        }
        foreach ($values as $index => $teamRow) {
            $editorId = (int) ($teamRow['editorId'] ?? 0);
            $projectId = (int) ($teamRow['projectId'] ?? 0);
            if ($editorId <= 0 || $projectId <= 0) {
                continue;
            }

            if ($this->projectService->isUserAssignedToProject($editorId, $projectId)) {
                continue;
            }

            $ticketId = $matchingIdField !== '' ? trim((string) ($teamRow[$matchingIdField] ?? '')) : '';
            if (is_numeric($ticketId)) {
                $existing = $this->ticketRepository->getTicket((int) $ticketId);
                if ($existing && (int) ($existing->editorId ?? 0) === $editorId) {
                    continue;
                }
            }

            $flags[] = sprintf(
                $this->language->__('notification.import_row_assignee_not_on_team'),
                $this->rowNumber($index),
                $matchingSourceField !== '' ? (string) ($teamRow[$matchingSourceField] ?? $editorId) : (string) $editorId,
                $matchingProjectSourceField !== '' ? (string) ($teamRow[$matchingProjectSourceField] ?? $projectId) : (string) $projectId
            );
        }

        // Storypoints/Effort Field Validation
        // Only a fixed set of values is renderable (see Tickets repository efforts).
        // An out-of-range value (e.g. "4") imports fine but later crashes ticket/dashboard views.
        $matchingStorypointsField = '';
        foreach ($fields as $item) {
            if ($item['leantimeField'] === 'storypoints') {
                $matchingStorypointsField = $item['sourceField'];
                break;
            }
        }

        if ($matchingStorypointsField) {
            // Cast keys to strings: PHP coerces numeric-string array keys (e.g. '1')
            // to ints, which would break the strict comparison below against the
            // string CSV value.
            $validStorypoints = array_map('strval', array_keys($this->ticketService->getEffortLabels()));
            foreach ($values as &$row) {
                $storypoints = trim((string) ($row[$matchingStorypointsField] ?? ''));
                if ($storypoints !== '' && ! in_array($storypoints, $validStorypoints, true)) {
                    $flags[] = $matchingStorypointsField.': '.$storypoints.' '.'is not a valid story point value. Use one of: '.implode(', ', $validStorypoints);
                }
            }
        }

        $this->cacheSerializedFieldValues($fields, $values, $flags);

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
                $emails = explode(',', $row[$matchingUsersSourceField]);
                $users = [];

                foreach ($emails as $email) {
                    $user = $this->userService->getUserByEmail(trim($email));
                    if ($user) {
                        $users[] = $user;
                    } else {
                        $flags[] = $email.' is not a valid user.';
                    }
                }
                $row[$matchingUsersSourceField] = $users;
            }
        }

        if (! $matchingProjectNameSourceField) {
            $flags[] = 'You must have a column with Project Names';
        }
        if (! $matchingClientIdSourceField) {
            $flags[] = 'You must have a column with ClientId';
        }

        $this->cacheSerializedFieldValues($fields, $values, $flags);

        return $flags;
    }

    private function parseUsers($fields, $values)
    {
        $matchingUsernameSourceField = '';
        $matchingRoleSourceField = '';
        $matchingSendInviteSourceField = '';
        $matchingFirstNameSourceField = '';
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
                if (strtolower($row[$matchingSendInviteSourceField]) == 'yes') {
                    $row['sendInvite'] = true;
                } elseif (strtolower($row[$matchingSendInviteSourceField]) == 'no') {
                    $row['sendInvite'] = false;
                } else {
                    $flags[] = 'The sendInvite column must contain only yes or no';
                }
                if (str_contains($row[$matchingUsernameSourceField], '@')) {
                    $user = $this->userService->getUserByEmail(
                        email: trim($row[$matchingUsernameSourceField]),
                        status: ''
                    );
                    if ($user) {
                        $row['id'] = $user['id'];
                    }
                } else {
                    $flags[] = 'The Username column must contain only valid emails';
                }
                $rolesKey = array_search(
                    strtolower(trim($row[$matchingRoleSourceField])),
                    Roles::getRoles()
                );
                if (! $rolesKey) {
                    $flags[] = $row[$matchingRoleSourceField].' is not a valid role.';
                } else {
                    $row[$matchingRoleSourceField] = $rolesKey;
                }
            }
        }
        if (! $matchingSendInviteSourceField) {
            $flags[] = 'You must have a column specifying if the user should receive an invite.';
        }
        if (! $matchingUsernameSourceField) {
            $flags[] = 'You must have a column with Emails.';
        }
        if (! $matchingRoleSourceField) {
            $flags[] = 'You must have a column with Roles.';
        }
        if (! $matchingFirstNameSourceField) {
            $flags[] = 'You must have a column with First Names.';
        }

        $this->cacheSerializedFieldValues($fields, $values, $flags);

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
                        $flags[] = $row[$matchingAuthorSourceField].' '.'is not a valid User';
                    }
                } else {
                    $flags[] = 'The Author column must contain only valid User emails';
                    break;
                }
            }
        } else {
            $id = session('userdata.id');
            foreach ($values as &$row) {
                $row['author'] = $id;
            }
        }
        if ($matchingCanvasIdSourceField) {
            foreach ($values as &$row) {
                if (
                    ! $this->ideaRepo->getSingleCanvas(
                        $row[$matchingCanvasIdSourceField]
                    )
                ) {
                    $flags[] = $row[$matchingCanvasIdSourceField].' '.'is not a valid Canvas.';
                }
            }
        }
        if (! $matchingDataSourceField) {
            $flags[] = 'You must have a column matching to Data.';
        }
        if (! $matchingDescriptionSourceField) {
            $flags[] = 'You must have a column matching to Description.';
        }
        if (! $matchingCanvasIdSourceField) {
            $flags[] = 'You must have a column matching to CanvasId.';
        }

        $this->cacheSerializedFieldValues($fields, $values, $flags);

        return $flags;
    }

    private function parseGoals($fields, $values)
    {
        // TODO add import logic and validation
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
                    ! $this->goalCanvasRepo->getSingleCanvas(
                        $row[$matchingCanvasIdSourceField]
                    )
                ) {
                    $flags[] = $row[$matchingCanvasIdSourceField].' '.'is not a valid Canvas.';
                }
            }
        }

        if (! $matchingTitleSourceField) {
            $flags[] = 'You must have a column matching to Title.';
        }
        if (! $matchingCanvasIdSourceField) {
            $flags[] = 'You must have a column matching to CanvasId.';
        }
        if (! $matchingCurrentValueSourceField) {
            $flags[] = 'You must have a column matching to CurrentValue.';
        }
        if (! $matchingStartValueSourceField) {
            $flags[] = 'You must have a column matching to StartValue.';
        }
        if (! $matchingEndValueSourceField) {
            $flags[] = 'You must have a column matching to EndValue.';
        }

        $this->cacheSerializedFieldValues($fields, $values, $flags);

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
            if ($item['leantimeField'] === 'headline') {
                $headlineFlag = false;
                break;
            }
        }

        if ($headlineFlag) {
            $flags[] = 'You must have a headline column';
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
                        $flags[] = $row[$matchingSourceField].' '.'is not a valid User';
                    }
                } else {
                    $flags[] = 'The Author/userId column must contain only valid User emails';
                    break;
                }
            }
        } else {
            $id = session('userdata.id');
            foreach ($values as &$row) {
                $row['editorId'] = $id;
            }
        }

        // PROJECT Name Check
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
                if (! $projectId) {
                    $flags[] = $row[$matchingProjectSourceField].' '.'is not a valid Project';
                } //
                else {
                    $row['projectId'] = $projectId;
                }
            }
        } else {
            $flags[] = 'You must have a column matching to a valid Project.';
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
        if (! $matchingEndDateSourceField) {
            $flags[] = 'You must have a column matching to a valid Edit To.';
        }
        if (! $matchingStartDateSourceField) {
            $flags[] = 'You must have a column matching to a valid Edit From.';
        }

        array_push($flags, ...$this->dateFlags($fields, $values));

        $this->cacheSerializedFieldValues($fields, $values, $flags);

        return $flags;
    }

    /**
     * Imports the reviewed rows into the chosen Leantime entity.
     *
     * Every row is attempted. A row that can't be saved is reported with its spreadsheet row
     * number and the reason; it doesn't stop the rows after it.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows as returned by the parse step.
     * @param  string  $entity  Entity key (tickets, projects, users, ideas, goals, milestones).
     * @return ImportResult|false The outcome per row, or false for an unknown entity.
     */
    public function importValues(array $fields, array $values, string $entity): ImportResult|false
    {

        $finalMappings = [];
        foreach ($fields as $field) {
            array_push($finalMappings, $field['sourceField']);
            array_push($finalMappings, $field['leantimeField']);
        }

        $values = array_values($values);
        $this->rememberSheetRows($values);

        return match ($entity) {
            'tickets' => $this->importTickets($finalMappings, $values),
            'projects' => $this->importProjects($finalMappings, $values),
            'users' => $this->importUsers($finalMappings, $values),
            'ideas' => $this->importIdeas($finalMappings, $values),
            'goals' => $this->importGoals($finalMappings, $values),
            'milestones' => $this->importMilestones($finalMappings, $values),
            default => false,
        };
    }

    /**
     * Imports to-dos. A row with a numeric Id updates that to-do; any other row adds one.
     *
     * Columns that aren't mapped are left out of the save, so updating a to-do keeps its stored
     * values for them (updateTicket() preserves omitted fields).
     *
     * @param  array<int, string>  $finalMappings  Pairs of source column, Leantime field.
     * @param  array<int, array<string, mixed>>  $finalValues  Rows from the parse step.
     */
    private function importTickets(array $finalMappings, array $finalValues): ImportResult
    {
        $result = new ImportResult;

        foreach ($finalValues as $index => $row) {
            $ticket = $this->mapRow($finalMappings, $row);

            $ticket['editorId'] = $row['editorId'] ?? '';
            $ticket['projectId'] = $row['projectId'] ?? '';
            // Only send a status that was resolved to an id of the ticket's project. Without one,
            // addTicket() uses the project's NEW status and updateTicket() keeps the stored status
            // (omitted fields are preserved), so a re-import never fails on a default id.
            unset($ticket['status']);
            if (isset($row['status']) && $row['status'] !== '') {
                $ticket['status'] = $row['status'];
            }

            // The parse step only lets known types through; a blank cell or no Type column is a task.
            $ticket['type'] = strtolower(trim((string) ($ticket['type'] ?? ''))) ?: 'task';

            try {
                $ticket['date'] = dtHelper()->parseUserDateTime(trim((string) ($ticket['date'] ?? '')))->formatDateForUser();
            } catch (\Exception $e) {
                $ticket['date'] = dtHelper()->userNow()->formatDateForUser();
            }

            if (array_key_exists('dateToFinish', $ticket)) {
                try {
                    $ticket['dateToFinish'] = dtHelper()->parseUserDateTime(trim((string) $ticket['dateToFinish']))->formatDateForUser();
                } catch (\Exception $e) {
                    $ticket['dateToFinish'] = '';
                }
            }

            // Edit From / Edit To come from their own columns. Without a value they are left out:
            // a new to-do has no work dates, an existing one keeps its own.
            foreach (['editFrom', 'editTo'] as $workDate) {
                if (! array_key_exists($workDate, $ticket)) {
                    continue;
                }

                $workDateValue = $this->workDateValue((string) $ticket[$workDate]);
                if ($workDateValue === null) {
                    unset($ticket[$workDate]);
                } else {
                    $ticket[$workDate] = $workDateValue;
                }
            }

            try {
                if (isset($ticket['id']) && is_numeric($ticket['id'])) {
                    $saved = $this->ticketService->updateTicket($ticket);
                } else {
                    unset($ticket['id']);
                    $ticket['origin'] = 'import';
                    $saved = $this->ticketService->addTicket($ticket);
                }
            } catch (\Exception $e) {
                Log::error($e);
                $result->addFailure($this->rowNumber($index), $this->exceptionMessage($e));

                continue;
            }

            if (is_array($saved) || $saved === false) {
                $result->addFailure($this->rowNumber($index), $this->saveErrorMessage($saved));

                continue;
            }

            $result->addImported();
        }

        return $result;
    }

    /**
     * Imports projects. A row with a numeric Id edits that project; any other row adds one.
     *
     * @param  array<int, string>  $finalMappings  Pairs of source column, Leantime field.
     * @param  array<int, array<string, mixed>>  $finalValues  Rows from the parse step.
     */
    private function importProjects(array $finalMappings, array $finalValues): ImportResult
    {
        $result = new ImportResult;
        $psettings = ['all', 'clients', 'restricted'];

        foreach ($finalValues as $index => $row) {
            $values = $this->mapRow($finalMappings, $row);
            if (isset($values['psettings']) && ! in_array($values['psettings'], $psettings, true)) {
                $values['psettings'] = 'restricted';
            }

            try {
                if (isset($values['id']) && is_numeric($values['id'])) {
                    // editProject() returns nothing and doesn't report a missing project, so check first.
                    if ($this->projectService->getProject((int) $values['id']) === false) {
                        $result->addFailure($this->rowNumber($index), $this->idNotFoundMessage($values['id']));

                        continue;
                    }

                    $this->projectService->editProject($values, (int) $values['id']);
                    $saved = true;
                } else {
                    unset($values['id']);
                    $saved = $this->projectService->addProject($values);
                }
            } catch (\Exception $e) {
                Log::error($e);
                $result->addFailure($this->rowNumber($index), $this->exceptionMessage($e));

                continue;
            }

            $this->recordSave($result, $index, $saved);
        }

        return $result;
    }

    /**
     * Imports users: edits a user whose email already exists, otherwise invites or adds one.
     *
     * @param  array<int, string>  $finalMappings  Pairs of source column, Leantime field.
     * @param  array<int, array<string, mixed>>  $finalValues  Rows from the parse step.
     */
    private function importUsers(array $finalMappings, array $finalValues): ImportResult
    {
        $result = new ImportResult;

        foreach ($finalValues as $index => $row) {
            // sendInvite only decides how the user is created and id is resolved from the email
            // by the parse step; neither is a user value.
            $values = array_diff_key($this->mapRow($finalMappings, $row), array_flip(['sendInvite', 'id']));
            $values['notifications'] = 1;
            $values['source'] = 'csvImport'; // TODO: will have to change when other integrations are added

            try {
                if (isset($row['id']) && $row['id'] > 0) {
                    $currentUser = $this->userService->getUser($row['id']);
                    $currentUser['user'] = $values['user'];
                    foreach ($currentUser as $key => &$userValues) {
                        if (isset($values[$key])) {
                            $userValues = $values[$key];
                        }
                    }
                    unset($userValues);
                    $saved = $this->userService->editUser($currentUser, $row['id']);
                } elseif (isset($row['sendInvite']) && $row['sendInvite'] == true) {
                    $saved = $this->userService->createUserInvite($values);
                } else {
                    $values['status'] = 'a';
                    if (! isset($values['password'])) {
                        $values['password'] = Uuid::uuid4()->toString();
                    }
                    $saved = $this->userService->addUser($values);
                }
            } catch (\Exception $e) {
                Log::error($e);
                $result->addFailure($this->rowNumber($index), $this->exceptionMessage($e));

                continue;
            }

            $this->recordSave($result, $index, $saved);
        }

        return $result;
    }

    /**
     * Imports ideas. A row with an Id edits that idea; any other row adds one.
     *
     * @param  array<int, string>  $finalMappings  Pairs of source column, Leantime field.
     * @param  array<int, array<string, mixed>>  $finalValues  Rows from the parse step.
     */
    private function importIdeas(array $finalMappings, array $finalValues): ImportResult
    {
        $result = new ImportResult;

        foreach ($finalValues as $index => $row) {
            $values = $this->mapRow($finalMappings, $row);

            try {
                if (isset($values['itemId']) && is_numeric($values['itemId'])) {
                    // editCanvasItem() doesn't report a missing item, so check that the idea exists
                    // on the board the row names (the review checked that board is an idea board).
                    if (! $this->isItemOnBoard($this->ideaRepo->getSingleCanvasItem((int) $values['itemId']), $values)) {
                        $result->addFailure($this->rowNumber($index), $this->idNotFoundMessage($values['itemId']));

                        continue;
                    }

                    $this->ideaRepo->editCanvasItem($values);
                    $saved = true;
                } else {
                    unset($values['itemId']);
                    $saved = $this->ideaRepo->addCanvasItem($values);
                }
            } catch (\Exception $e) {
                Log::error($e);
                $result->addFailure($this->rowNumber($index), $this->exceptionMessage($e));

                continue;
            }

            $this->recordSave($result, $index, $saved);
        }

        return $result;
    }

    /**
     * Imports goals. A row with an Id edits that goal; any other row adds one.
     *
     * @param  array<int, string>  $finalMappings  Pairs of source column, Leantime field.
     * @param  array<int, array<string, mixed>>  $finalValues  Rows from the parse step.
     */
    private function importGoals(array $finalMappings, array $finalValues): ImportResult
    {
        $result = new ImportResult;

        foreach ($finalValues as $index => $row) {
            $values = $this->mapRow($finalMappings, $row);
            $values['box'] = 'goal';
            if (! isset($values['author'])) {
                $values['author'] = session('userdata.id');
            }

            try {
                if (isset($values['itemId']) && is_numeric($values['itemId'])) {
                    // editCanvasItem() doesn't report a missing goal, so check that the goal exists
                    // on the board the row names (the review checked that board is a goal board).
                    $existing = (int) $values['itemId'] > 0 ? $this->goalCanvasRepo->getSingleCanvasItem((int) $values['itemId']) : false;
                    if (! $this->isItemOnBoard($existing, $values) || ($existing['box'] ?? '') !== 'goal') {
                        $result->addFailure($this->rowNumber($index), $this->idNotFoundMessage($values['itemId']));

                        continue;
                    }

                    $this->goalCanvasRepo->editCanvasItem($values);
                    $saved = true;
                } else {
                    unset($values['itemId']);
                    $saved = $this->goalCanvasRepo->addCanvasItem($values);
                }
            } catch (\Exception $e) {
                Log::error($e);
                $result->addFailure($this->rowNumber($index), $this->exceptionMessage($e));

                continue;
            }

            $this->recordSave($result, $index, $saved);
        }

        return $result;
    }

    /**
     * Imports milestones. A row with a numeric Id updates that milestone; any other row adds one.
     *
     * @param  array<int, string>  $finalMappings  Pairs of source column, Leantime field.
     * @param  array<int, array<string, mixed>>  $finalValues  Rows from the parse step.
     */
    private function importMilestones(array $finalMappings, array $finalValues): ImportResult
    {
        $result = new ImportResult;

        foreach ($finalValues as $index => $row) {
            $ticket = $this->mapRow($finalMappings, $row);
            $ticket['editorId'] = $row['editorId'] ?? '';
            $ticket['projectId'] = $row['projectId'] ?? '';
            // No status column: leave it out so a new milestone gets the project's NEW status and an
            // existing one keeps its stored status.
            $ticket['type'] = 'milestone';

            try {
                if (isset($ticket['id']) && is_numeric($ticket['id'])) {
                    $saved = $this->ticketService->updateTicket($ticket);
                } else {
                    unset($ticket['id']);
                    $ticket['origin'] = 'import';
                    $saved = $this->ticketService->addTicket($ticket);
                }
            } catch (\Exception $e) {
                Log::error($e);
                $result->addFailure($this->rowNumber($index), $this->exceptionMessage($e));

                continue;
            }

            $this->recordSave($result, $index, $saved);
        }

        return $result;
    }

    /**
     * Builds the Leantime values of one row from the column mappings.
     *
     * @param  array<int, string>  $finalMappings  Pairs of source column, Leantime field.
     * @param  array<string, mixed>  $row  One row from the parse step.
     * @return array<string, mixed> Leantime field => cell value.
     */
    private function mapRow(array $finalMappings, array $row): array
    {
        $values = [];
        for ($i = 0; $i < count($finalMappings); $i = $i + 2) {
            $field = $finalMappings[$i + 1];
            $cell = $row[$finalMappings[$i]] ?? '';

            // Several columns mapped to Tags (Jira's repeated Labels) are combined.
            if (in_array($field, self::MULTI_COLUMN_FIELDS, true) && isset($values[$field])) {
                $values[$field] = implode(',', array_filter([trim((string) $values[$field]), trim((string) $cell)], fn ($v) => $v !== ''));

                continue;
            }

            $values[$field] = $cell;
        }

        return $values;
    }

    /**
     * Records a save as imported, or as failed when the service returned an error array or false.
     *
     * @param  int  $index  Zero-based record index.
     * @param  mixed  $saved  What the save call returned.
     */
    private function recordSave(ImportResult $result, int $index, mixed $saved): void
    {
        if (is_array($saved) || $saved === false) {
            $result->addFailure($this->rowNumber($index), $this->saveErrorMessage($saved));

            return;
        }

        $result->addImported();
    }

    /**
     * Turns a failed save's return value into a message for the person importing.
     *
     * The ticket service returns ['msg' => language key or text, 'type' => 'error'].
     *
     * @param  array|false  $saved  What the save call returned.
     */
    private function saveErrorMessage(array|false $saved): string
    {
        if (is_array($saved) && ! empty($saved['msg'])) {
            return $this->language->__((string) $saved['msg']);
        }

        return $this->language->__('notification.import_row_not_saved');
    }

    /**
     * Message for a row whose save threw.
     *
     * The services save first and notify afterwards, so an error can come after the row was
     * stored. The message says so, so the person checks before importing the row again.
     */
    private function exceptionMessage(\Exception $e): string
    {
        return sprintf($this->language->__('notification.import_row_save_uncertain'), $e->getMessage());
    }

    /**
     * Message for a row whose Id doesn't match an item that can be updated.
     */
    private function idNotFoundMessage(mixed $id): string
    {
        return sprintf($this->language->__('notification.import_row_id_not_found'), (string) $id);
    }

    /**
     * Whether a board item exists and belongs to the board the row names.
     *
     * @param  mixed  $item  The stored item, or false when there is none.
     * @param  array<string, mixed>  $values  The row's values.
     */
    private function isItemOnBoard(mixed $item, array $values): bool
    {
        return is_array($item)
            && isset($values['canvasId'])
            && (string) $item['canvasId'] === trim((string) $values['canvasId']);
    }

    /**
     * Flags every cell of a mapped date column that isn't a valid date.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows.
     * @return array<int, string> One message per invalid cell.
     */
    private function dateFlags(array $fields, array $values): array
    {
        $flags = [];

        foreach ($fields as $item) {
            if (! in_array($item['leantimeField'], self::DATE_FIELDS, true)) {
                continue;
            }

            $sourceField = $item['sourceField'];
            foreach ($values as $index => $dateRow) {
                $value = trim((string) ($dateRow[$sourceField] ?? ''));
                if ($value === '') {
                    continue;
                }

                try {
                    dtHelper()->parseUserDateTime($value);
                } catch (\Exception $e) {
                    $flags[] = sprintf(
                        $this->language->__('notification.import_row_invalid_date'),
                        $this->rowNumber($index),
                        $sourceField,
                        $value
                    );
                }
            }
        }

        return $flags;
    }

    /**
     * Prepares an Edit From / Edit To cell for the ticket service.
     *
     * A cell with a time keeps it (the parsed date-time is passed on). A date-only cell is
     * passed in the user's date format, so the service sets the start of the day for Edit From
     * and the end of the day for Edit To; an ISO date-only string would otherwise pick up the
     * current time of day.
     *
     * @param  string  $value  The cell value.
     * @return CarbonImmutable|string|null The value to save, or null for a blank or unreadable cell.
     */
    private function workDateValue(string $value): CarbonImmutable|string|null
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        try {
            $date = dtHelper()->parseUserDateTime($value);
        } catch (\Exception $e) {
            return null;
        }

        // A time looks like 09:00, or 090000 in compact ISO 8601 (20261125T090000Z).
        $hasTime = preg_match('/\d{1,2}:\d{2}|\d{8}T\d{4}/i', $value) === 1;

        return $hasTime ? $date : $date->formatDateForUser();
    }

    /**
     * To-do types a file can set: every ticket type except subtask, which needs a parent.
     *
     * @return array<int, string>
     */
    private function importableTicketTypes(): array
    {
        return array_values(array_diff($this->ticketService->getTicketTypes(), ['subtask']));
    }

    /**
     * Spreadsheet row number of a record: the row the provider recorded (ROW_KEY) or, without
     * one, headings on row 1 and the first record on row 2.
     *
     * @param  int|string  $index  Record index in the rows being handled.
     */
    private function rowNumber(int|string $index): int
    {
        return $this->sheetRows[$index] ?? (int) $index + 2;
    }

    /**
     * Stores the reviewed rows for the import step, with a token the import step claims once.
     *
     * Rows the review flagged are not stored (and an earlier clean review is cleared), so a
     * request straight to the import step can't import rows the review rejected.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows after the parse step.
     * @param  array<int, string>  $flags  What the review found; empty when the rows can be imported.
     */
    private function cacheSerializedFieldValues(array $fields, array $values, array $flags): void
    {
        if ($flags !== []) {
            $this->reviewedRows->forget();

            return;
        }

        $this->reviewedRows->store($fields, $values, $this->reviewedEntity);
    }
}
