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
use Leantime\Domain\Notifications\Services\BulkNotifications;
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

    /**
     * What Leantime needs from a file of one entity, as the checklist of the matching step.
     * The required items are exactly the columns the review requires (requiredColumnFlags),
     * plus the project of to-dos and milestones, which can also be chosen for all rows.
     * Each item: the Leantime fields that satisfy it (any one of them), its name, whether the
     * import needs it, a one-line hint, and whether the project picker satisfies it.
     *
     * @param  string  $entity  Entity key.
     * @return array<int, array{fields: array<int, string>, label: string, required: bool, hint: string, picker: bool}>
     */
    public function getEntityGuide(string $entity): array
    {
        $items = match ($entity) {
            'tickets' => [
                [['headline'], true, 'text.import_guide_title'],
                [['projectName'], true, 'text.import_guide_project', null, true],
                [['editorId'], false, 'text.import_guide_assignee'],
                [['priority'], false, 'text.import_guide_priority'],
                [['status'], false, 'text.import_guide_status'],
                [['dateToFinish', 'editFrom', 'editTo', 'date'], false, 'text.import_guide_dates', 'label.import_guide_dates'],
                [['planHours', 'hourRemaining'], false, 'text.import_guide_hours', 'label.import_guide_hours'],
            ],
            'milestones' => [
                [['headline'], true, 'text.import_guide_title'],
                [['editFrom'], true, 'text.import_guide_dates'],
                [['editTo'], true, 'text.import_guide_dates'],
                [['projectName'], true, 'text.import_guide_project', null, true],
                [['editorId'], false, 'text.import_guide_assignee'],
            ],
            'projects' => [
                [['name'], true, 'text.import_guide_project_name'],
                [['clientId'], true, 'text.import_guide_client'],
                [['assignedUsers'], false, 'text.import_guide_assigned_users'],
                [['psettings'], false, 'text.import_guide_permissions'],
            ],
            'users' => [
                [['user'], true, 'text.import_guide_email'],
                [['firstname'], true, 'text.import_guide_first_name'],
                [['role'], true, 'text.import_guide_role'],
                [['sendInvite'], true, 'text.import_guide_send_invite'],
            ],
            'ideas' => [
                [['description'], true, 'text.import_guide_title'],
                [['data'], true, 'text.import_guide_idea_description'],
                [['canvasId'], true, 'text.import_guide_idea_board'],
                [['author'], false, 'text.import_guide_author'],
            ],
            'goals' => [
                [['title'], true, 'text.import_guide_title'],
                [['canvasId'], true, 'text.import_guide_goal_board'],
                [['startValue'], true, 'text.import_guide_goal_values'],
                [['currentValue'], true, 'text.import_guide_goal_values'],
                [['endValue'], true, 'text.import_guide_goal_values'],
            ],
            default => [],
        };

        return array_map(fn (array $item) => [
            'fields' => $item[0],
            'label' => isset($item[3]) ? $this->language->__($item[3]) : $this->fieldName($entity, $item[0][0]),
            'required' => $item[1],
            'hint' => $this->language->__($item[2]),
            'picker' => $item[4] ?? false,
        ], $items);
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

    /** The project chosen for new rows that don't name one, or null when none was chosen. */
    private ?int $defaultProjectId = null;

    /** @var array<int, string> Project names by id, for messages. */
    private array $projectNames = [];

    /** @var array<string, array<int, int>>|null Ids of active people by lower-case full name. */
    private ?array $peopleByName = null;

    /** @var array<string, int> Row number of each row message of the review, for listing them in row order. */
    private array $flagRows = [];

    /** @var array<int, array<string, array<int, string>>> Short messages per row number and column of the file. */
    private array $cellProblems = [];

    /** @var array<string, true> Review messages that are about one cell (shown in that cell). */
    private array $cellMessages = [];

    /** @var array<int|string, array<string, mixed>> The rows as the last review read them (ids resolved, values normalised). */
    private array $reviewedValues = [];

    /**
     * Reviews the mapped rows of one import and stores them for the import step when nothing
     * is flagged.
     *
     * @param  array  $fields  Column mappings (sourceField, leantimeField).
     * @param  array  $values  Rows from the file.
     * @param  string  $entity  Entity key (tickets, projects, users, ideas, goals, milestones).
     * @param  int|null  $defaultProjectId  Project for new to-dos and milestones whose row names none.
     * @return array|false The review flags, or false for an unknown entity.
     */
    public function parseValues($fields, $values, $entity, ?int $defaultProjectId = null)
    {
        $this->reviewedEntity = (string) $entity;
        $this->flagRows = [];
        $this->cellProblems = [];
        $this->cellMessages = [];
        $this->reviewedValues = [];
        $this->setDefaultProject($defaultProjectId);
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
        $flags = $this->inRowOrder(array_values($flags));

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

    /**
     * Reviews to-do rows: required columns, people, projects, statuses, types, priorities,
     * numbers, dates and team membership. Each problem is reported with its spreadsheet row.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows from the file.
     * @return array<int, string> What the review found; empty when the rows can be imported.
     */
    private function parseTickets($fields, $values)
    {
        $flags = $this->requiredColumnFlags($fields, 'tickets', ['headline']);

        $matchingSourceField = $this->columnFor($fields, 'editorId');
        $matchingIdField = $this->columnFor($fields, 'id');

        array_push($flags, ...$this->assigneeFlags($fields, $values, $matchingIdField));
        array_push($flags, ...$this->projectFlags($fields, $values, $matchingIdField));

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
            unset($row);
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
                    $flags[] = $this->cellFlag($matchingTypeField, 'notification.import_row_invalid_type', $this->rowNumber($index), $typeRow[$matchingTypeField], implode(', ', $validTypes));
                }
            }
        }

        // Assignees must be on the project's team, or the save fails at import time. Checking it
        // here shows the problem on the review step, before anything is written. Like the ticket
        // service, only a new assignee is checked: an update that keeps a to-do's assignee saves
        // even if that person has left the team since.
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

            $flags[] = $this->cellFlag(
                $matchingSourceField,
                'notification.import_row_assignee_not_on_team',
                $this->rowNumber($index),
                $matchingSourceField !== '' ? (string) ($teamRow[$matchingSourceField] ?? $editorId) : (string) $editorId,
                $this->projectNames[$projectId] ?? (string) $projectId
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
            foreach ($values as $index => $effortRow) {
                $storypoints = trim((string) ($effortRow[$matchingStorypointsField] ?? ''));
                if ($storypoints !== '' && ! in_array($storypoints, $validStorypoints, true)) {
                    $flags[] = $this->cellFlag($matchingStorypointsField, 'notification.import_row_invalid_effort', $this->rowNumber($index), $storypoints, implode(', ', $validStorypoints));
                }
            }
        }

        array_push($flags, ...$this->priorityFlags($fields, $values));
        array_push($flags, ...$this->numberFlags($fields, $values, 'tickets', ['planHours', 'hourRemaining']));

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
            foreach ($values as $index => $row) {
                $emails = explode(',', $row[$matchingUsersSourceField]);
                $users = [];

                foreach ($emails as $email) {
                    if (trim($email) === '') {
                        continue;
                    }
                    $user = $this->userService->getUserByEmail(trim($email));
                    if ($user) {
                        $users[] = $user;
                    } else {
                        $flags[] = $this->cellFlag($matchingUsersSourceField, 'notification.import_row_unknown_email', $this->rowNumber($index), trim($email));
                    }
                }
                $row[$matchingUsersSourceField] = $users;
            }
        }

        array_push($flags, ...$this->requiredColumnFlags($fields, 'projects', ['name', 'clientId']));

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
            foreach ($values as $index => &$row) {
                if (strtolower($row[$matchingSendInviteSourceField]) == 'yes') {
                    $row['sendInvite'] = true;
                } elseif (strtolower($row[$matchingSendInviteSourceField]) == 'no') {
                    $row['sendInvite'] = false;
                } else {
                    $flags[] = $this->cellFlag($matchingSendInviteSourceField, 'notification.import_row_yes_or_no', $this->rowNumber($index), $row[$matchingSendInviteSourceField], $this->fieldName('users', 'sendInvite'));
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
                    $flags[] = $this->cellFlag($matchingUsernameSourceField, 'notification.import_row_not_an_email', $this->rowNumber($index), $row[$matchingUsernameSourceField]);
                }
                $rolesKey = array_search(
                    strtolower(trim($row[$matchingRoleSourceField])),
                    Roles::getRoles()
                );
                if (! $rolesKey) {
                    $flags[] = $this->cellFlag($matchingRoleSourceField, 'notification.import_row_invalid_role', $this->rowNumber($index), $row[$matchingRoleSourceField], implode(', ', Roles::getRoles()));
                } else {
                    $row[$matchingRoleSourceField] = $rolesKey;
                }
            }
        }
        unset($row);
        array_push($flags, ...$this->requiredColumnFlags($fields, 'users', ['user', 'firstname', 'role', 'sendInvite']));

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
            foreach ($values as $index => &$row) {
                $person = trim((string) ($row[$matchingAuthorSourceField] ?? ''));
                $found = $person === '' ? (int) session('userdata.id') : $this->findPerson($person);
                if (is_int($found)) {
                    $row['author'] = $found;
                } else {
                    $flags[] = $this->cellFlag($matchingAuthorSourceField, $found, $this->rowNumber($index), $person);
                }
            }
            unset($row);
        } else {
            $id = session('userdata.id');
            foreach ($values as &$row) {
                $row['author'] = $id;
            }
        }
        if ($matchingCanvasIdSourceField) {
            foreach ($values as $index => $boardRow) {
                if (! $this->ideaRepo->getSingleCanvas($boardRow[$matchingCanvasIdSourceField])) {
                    $flags[] = $this->cellFlag($matchingCanvasIdSourceField, 'notification.import_row_unknown_board', $this->rowNumber($index), $boardRow[$matchingCanvasIdSourceField]);
                }
            }
        }
        array_push($flags, ...$this->requiredColumnFlags($fields, 'ideas', ['description', 'data', 'canvasId']));

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
            foreach ($values as $index => $boardRow) {
                if (! $this->goalCanvasRepo->getSingleCanvas($boardRow[$matchingCanvasIdSourceField])) {
                    $flags[] = $this->cellFlag($matchingCanvasIdSourceField, 'notification.import_row_unknown_board', $this->rowNumber($index), $boardRow[$matchingCanvasIdSourceField]);
                }
            }
        }

        array_push($flags, ...$this->requiredColumnFlags($fields, 'goals', ['title', 'canvasId', 'startValue', 'currentValue', 'endValue']));

        $this->cacheSerializedFieldValues($fields, $values, $flags);

        return $flags;
    }

    /**
     * Reviews milestone rows: required columns, people, projects and dates.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows from the file.
     * @return array<int, string> What the review found; empty when the rows can be imported.
     */
    private function parseMilestones($fields, $values)
    {
        $flags = $this->requiredColumnFlags($fields, 'milestones', ['headline', 'editFrom', 'editTo']);

        $matchingIdField = $this->columnFor($fields, 'id');
        array_push($flags, ...$this->assigneeFlags($fields, $values, $matchingIdField));
        array_push($flags, ...$this->projectFlags($fields, $values, $matchingIdField));

        array_push($flags, ...$this->dateFlags($fields, $values));

        $this->cacheSerializedFieldValues($fields, $values, $flags);

        return $flags;
    }

    /**
     * Imports the reviewed rows into the chosen Leantime entity.
     *
     * Every row is attempted. A row that can't be saved is reported with its spreadsheet row
     * number and the reason; it doesn't stop the rows after it. To-do notifications are held
     * while the rows are saved and sent as one summary per project afterwards (see
     * BulkNotifications), so an import doesn't notify the team once per row.
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

        // One summary per project instead of a notification per row (see BulkNotifications).
        return (new BulkNotifications)->hold(fn () => match ($entity) {
            'tickets' => $this->importTickets($finalMappings, $values),
            'projects' => $this->importProjects($finalMappings, $values),
            'users' => $this->importUsers($finalMappings, $values),
            'ideas' => $this->importIdeas($finalMappings, $values),
            'goals' => $this->importGoals($finalMappings, $values),
            'milestones' => $this->importMilestones($finalMappings, $values),
            default => false,
        });
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

            // The review sets these only where the row gives them; an update row without them keeps its own.
            unset($ticket['editorId'], $ticket['projectId']);
            foreach (['editorId', 'projectId'] as $key) {
                if (array_key_exists($key, $row)) {
                    $ticket[$key] = $row[$key];
                }
            }
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
                    // updateTicket() answers "not allowed" for an Id that doesn't exist, and would turn
                    // a milestone into a to-do; neither is what the row asked for.
                    if (! $this->isTicketToUpdate($ticket['id'], milestone: false)) {
                        $result->addFailure($this->rowNumber($index), $this->idNotFoundMessage($ticket['id']));

                        continue;
                    }

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
            // The review sets these only where the row gives them; an update row without them keeps its own.
            unset($ticket['editorId'], $ticket['projectId']);
            foreach (['editorId', 'projectId'] as $key) {
                if (array_key_exists($key, $row)) {
                    $ticket[$key] = $row[$key];
                }
            }
            // No status column: leave it out so a new milestone gets the project's NEW status and an
            // existing one keeps its stored status.
            $ticket['type'] = 'milestone';

            try {
                if (isset($ticket['id']) && is_numeric($ticket['id'])) {
                    if (! $this->isTicketToUpdate($ticket['id'], milestone: true)) {
                        $result->addFailure($this->rowNumber($index), $this->idNotFoundMessage($ticket['id']));

                        continue;
                    }

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
     * Whether an Id names a to-do (or, for a milestone import, a milestone) that the person
     * can see, so the row updates what it means to.
     *
     * @param  mixed  $id  The row's Id.
     * @param  bool  $milestone  True for a milestone import, false for a to-do import.
     */
    private function isTicketToUpdate(mixed $id, bool $milestone): bool
    {
        $existing = (int) $id > 0 ? $this->ticketService->getTicket((int) $id) : false;
        if (! $existing) {
            return false;
        }

        return ($existing->type === 'milestone') === $milestone;
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
    private function dateFlags(array $fields, array &$values): array
    {
        $flags = [];

        foreach ($fields as $item) {
            if (! in_array($item['leantimeField'], self::DATE_FIELDS, true)) {
                continue;
            }

            $sourceField = $item['sourceField'];
            foreach ($values as $index => &$dateRow) {
                $value = self::withoutFractionalSeconds(trim((string) ($dateRow[$sourceField] ?? '')));
                if ($value === '') {
                    continue;
                }
                // The import reads the same cell, so it gets the value that was checked.
                $dateRow[$sourceField] = $value;

                try {
                    dtHelper()->parseUserDateTime($value);
                } catch (\Exception $e) {
                    $flags[] = $this->cellFlag($sourceField, 'notification.import_row_invalid_date', $this->rowNumber($index), $sourceField, $value);
                }
            }
            unset($dateRow);
        }

        return $flags;
    }

    /**
     * A date-time without its fraction of a second ("2026-01-25T09:00:00.000Z" →
     * "2026-01-25T09:00:00Z"), which Trello and other tools write and Leantime's date reader
     * doesn't accept.
     *
     * @param  string  $value  A date cell.
     */
    public static function withoutFractionalSeconds(string $value): string
    {
        return preg_replace('/(T\d{2}:\d{2}:\d{2})\.\d+(?=Z|[+-]\d{2}:?\d{2}|$)/', '$1', $value) ?? $value;
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
     * Keeps the chosen project for rows that don't name one, if it exists.
     *
     * @param  int|null  $projectId  The chosen project, or null.
     */
    private function setDefaultProject(?int $projectId): void
    {
        $this->defaultProjectId = null;
        $this->projectNames = [];

        if ($projectId === null || $projectId <= 0) {
            return;
        }

        $project = $this->projectService->getProject($projectId);
        if (is_array($project) && isset($project['id'])) {
            $this->defaultProjectId = (int) $project['id'];
            $this->projectNames[$this->defaultProjectId] = (string) ($project['name'] ?? $projectId);
        }
    }

    /**
     * The file column matched to a Leantime field, or '' when none is.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  string  $leantimeField  The Leantime field.
     */
    private function columnFor(array $fields, string $leantimeField): string
    {
        foreach ($fields as $item) {
            if ($item['leantimeField'] === $leantimeField) {
                return (string) $item['sourceField'];
            }
        }

        return '';
    }

    /**
     * A review message in the person's language.
     *
     * @param  string  $key  Language key of the message.
     * @param  mixed  ...$args  Values for its placeholders.
     */
    private function flag(string $key, mixed ...$args): string
    {
        $message = sprintf($this->language->__($key), ...$args);

        // Row messages take the row number first; the review lists them in row order.
        if (str_starts_with($key, 'notification.import_row_') && is_int($args[0] ?? null)) {
            $this->flagRows[$message] = $args[0];
        }

        return $message;
    }

    /**
     * Puts the messages about the whole file first, then the row messages in row order, so all
     * problems of one row are listed together. Messages of one row keep their order.
     *
     * @param  array<int, string>  $flags  Review messages.
     * @return array<int, string>
     */
    private function inRowOrder(array $flags): array
    {
        $order = array_keys($flags);
        usort($order, fn (int $a, int $b) => [$this->flagRows[$flags[$a]] ?? 0, $a] <=> [$this->flagRows[$flags[$b]] ?? 0, $b]);

        return array_map(fn (int $key) => $flags[$key], $order);
    }

    /**
     * A row message about one cell. Besides the full message, the review page shows a short one
     * (the notification.import_cell_* twin of the key, same arguments) in that cell.
     *
     * @param  string  $column  The column of the file the cell is in; '' when there is none.
     * @param  string  $key  A notification.import_row_* key; its first argument is the row number.
     */
    private function cellFlag(string $column, string $key, mixed ...$args): string
    {
        $message = $this->flag($key, ...$args);

        if ($column !== '' && is_int($args[0] ?? null)) {
            $cellKey = str_replace('notification.import_row_', 'notification.import_cell_', $key);
            $this->cellProblems[$args[0]][$column][] = sprintf($this->language->__($cellKey), ...$args);
            $this->cellMessages[$message] = true;
        }

        return $message;
    }

    /**
     * What the last review found, for the review page: short messages per cell, and the
     * messages about the whole file or a row without a column of its own.
     *
     * @param  array<int, string>  $flags  The messages parseValues() returned.
     * @return array{cells: array<int, array<string, array<int, string>>>, general: array<int, string>}
     */
    public function reviewProblems(array $flags): array
    {
        return [
            'cells' => $this->cellProblems,
            'general' => array_values(array_filter(array_unique($flags), fn (string $flag) => ! isset($this->cellMessages[$flag]))),
        ];
    }

    /**
     * The rows as the last review read them: people, projects and statuses resolved to ids,
     * priorities and numbers normalised. Keyed like the rows given to parseValues().
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function reviewedValues(): array
    {
        return $this->reviewedValues;
    }

    /**
     * The name of a Leantime field as the matching step shows it.
     *
     * @param  string  $entity  Entity key.
     * @param  string  $field  Field key.
     */
    private function fieldName(string $entity, string $field): string
    {
        return app()->make(LeantimeEntities::class)->availableLeantimeEntities[$entity]['fields'][$field]['name'] ?? $field;
    }

    /**
     * Flags each required field that no column is matched to.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  string  $entity  Entity key.
     * @param  array<int, string>  $required  Required field keys.
     * @return array<int, string>
     */
    private function requiredColumnFlags(array $fields, string $entity, array $required): array
    {
        $flags = [];
        foreach ($required as $field) {
            if ($this->columnFor($fields, $field) === '') {
                $flags[] = $this->flag('notification.import_column_required', $this->fieldName($entity, $field));
            }
        }

        return $flags;
    }

    /**
     * Whether a row updates an existing item (its Id cell holds a number).
     *
     * @param  array<string, mixed>  $row  One row.
     * @param  string  $idColumn  The column matched to Id, or ''.
     */
    private function isUpdateRow(array $row, string $idColumn): bool
    {
        return $idColumn !== '' && is_numeric(trim((string) ($row[$idColumn] ?? '')));
    }

    /**
     * Sets each row's assignee from the column matched to it: an email address or a person's
     * full name. A new row with an empty cell is left unassigned; an update row with an empty
     * cell, or without the column, keeps its assignee. Without the column, new rows are
     * assigned to the person importing.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows; editorId is set on them.
     * @param  string  $idColumn  The column matched to Id, or ''.
     * @return array<int, string> A message for each person that can't be found.
     */
    private function assigneeFlags(array $fields, array &$values, string $idColumn): array
    {
        $column = $this->columnFor($fields, 'editorId');
        $flags = [];

        foreach ($values as $index => &$row) {
            unset($row['editorId']);
            $isUpdate = $this->isUpdateRow($row, $idColumn);

            if ($column === '') {
                if (! $isUpdate) {
                    $row['editorId'] = session('userdata.id');
                }

                continue;
            }

            $person = trim((string) ($row[$column] ?? ''));
            if ($person === '') {
                if (! $isUpdate) {
                    $row['editorId'] = '';
                }

                continue;
            }

            $found = $this->findPerson($person);
            if (is_int($found)) {
                $row['editorId'] = $found;
            } else {
                $flags[] = $this->cellFlag($column, $found, $this->rowNumber($index), $person);
            }
        }
        unset($row);

        return $flags;
    }

    /**
     * Finds an active person by email address or by full name.
     *
     * @param  string  $person  An email address or a full name.
     * @return int|string The person's id, or the language key of why no one matched.
     */
    private function findPerson(string $person): int|string
    {
        if (str_contains($person, '@')) {
            $user = $this->userService->getUserByEmail($person);

            return is_array($user) && isset($user['id']) ? (int) $user['id'] : 'notification.import_row_unknown_email';
        }

        $ids = $this->peopleByName()[$this->normalisedName($person)] ?? [];

        if (count($ids) === 1) {
            return $ids[0];
        }

        return $ids === [] ? 'notification.import_row_unknown_person' : 'notification.import_row_person_ambiguous';
    }

    /**
     * Active people by lower-case full name ("ada lovelace"), loaded once per review.
     *
     * @return array<string, array<int, int>>
     */
    private function peopleByName(): array
    {
        if ($this->peopleByName !== null) {
            return $this->peopleByName;
        }

        $this->peopleByName = [];
        foreach ($this->userService->getAll(true) ?: [] as $user) {
            $name = $this->normalisedName(($user['firstname'] ?? '').' '.($user['lastname'] ?? ''));
            if ($name !== '') {
                $this->peopleByName[$name][] = (int) $user['id'];
            }
        }

        return $this->peopleByName;
    }

    /**
     * A name in lower case with single spaces, for comparing.
     *
     * @param  string  $name  A name.
     */
    private function normalisedName(string $name): string
    {
        return mb_strtolower(preg_replace('/\s+/u', ' ', trim($name)) ?? '');
    }

    /**
     * Sets each row's project from the column matched to Project, or from the project chosen
     * on the matching step for new rows that don't name one. Update rows without a project
     * keep theirs.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows; projectId is set on them.
     * @param  string  $idColumn  The column matched to Id, or ''.
     * @return array<int, string>
     */
    private function projectFlags(array $fields, array &$values, string $idColumn): array
    {
        $column = $this->columnFor($fields, 'projectName');
        $allProjects = $column !== '' ? $this->projectService->getAllProjects() : [];
        $flags = [];
        $needsProject = false;

        foreach ($values as $index => &$row) {
            unset($row['projectId']);
            $name = $column !== '' ? trim((string) ($row[$column] ?? '')) : '';

            if ($name === '') {
                if ($this->isUpdateRow($row, $idColumn)) {
                    continue;
                }

                if ($this->defaultProjectId !== null) {
                    $row['projectId'] = $this->defaultProjectId;
                } elseif ($column !== '') {
                    $flags[] = $this->cellFlag($column, 'notification.import_row_project_empty', $this->rowNumber($index));
                } else {
                    $needsProject = true;
                }

                continue;
            }

            $projectId = $this->projectService->getProjectIdByName($allProjects, $name);
            if (! $projectId) {
                $flags[] = $this->cellFlag($column, 'notification.import_row_unknown_project', $this->rowNumber($index), $name);

                continue;
            }

            $row['projectId'] = (int) $projectId;
            $this->projectNames[(int) $projectId] = $name;
        }
        unset($row);

        if ($needsProject) {
            array_unshift($flags, $this->flag('notification.import_choose_project'));
        }

        return $flags;
    }

    /**
     * Turns priority names (Critical … Lowest) into Leantime's numbers and flags values that
     * are neither.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows; the priority cell is rewritten.
     * @return array<int, string>
     */
    private function priorityFlags(array $fields, array &$values): array
    {
        $column = $this->columnFor($fields, 'priority');
        if ($column === '') {
            return [];
        }

        $labels = $this->ticketService->getPriorityLabels();
        $byName = [];
        foreach ($labels as $number => $label) {
            $byName[mb_strtolower((string) $label)] = (string) $number;
        }

        $flags = [];
        foreach ($values as $index => &$row) {
            $priority = trim((string) ($row[$column] ?? ''));
            if ($priority === '' || isset($labels[$priority])) {
                continue;
            }

            if (isset($byName[mb_strtolower($priority)])) {
                $row[$column] = $byName[mb_strtolower($priority)];

                continue;
            }

            $choices = [];
            foreach ($labels as $number => $label) {
                $choices[] = $number.' '.$label;
            }
            $flags[] = $this->cellFlag($column, 'notification.import_row_invalid_priority', $this->rowNumber($index), $priority, implode(', ', $choices));
        }
        unset($row);

        return $flags;
    }

    /**
     * Reads number cells written with a decimal comma (1,5) or thousands separators
     * (1.234,5 or 1,234.5) and flags cells that aren't numbers.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int, array<string, mixed>>  $values  Rows; number cells are rewritten.
     * @param  string  $entity  Entity key, for the field names.
     * @param  array<int, string>  $numberFields  Field keys that hold numbers.
     * @return array<int, string>
     */
    private function numberFlags(array $fields, array &$values, string $entity, array $numberFields): array
    {
        $flags = [];
        foreach ($numberFields as $field) {
            $column = $this->columnFor($fields, $field);
            if ($column === '') {
                continue;
            }

            foreach ($values as $index => &$row) {
                $cell = trim((string) ($row[$column] ?? ''));
                if ($cell === '') {
                    continue;
                }

                $number = self::normalisedNumber($cell);
                if ($number === null) {
                    $flags[] = $this->cellFlag($column, 'notification.import_row_not_a_number', $this->rowNumber($index), $cell, $this->fieldName($entity, $field));
                } else {
                    $row[$column] = $number;
                }
            }
            unset($row);
        }

        return $flags;
    }

    /**
     * A number cell as a plain decimal ("1,5" → "1.5", "1.234,5" → "1234.5",
     * "1,234.5" → "1234.5"), or null when it isn't a number. A single comma is read as a
     * decimal comma.
     *
     * @param  string  $cell  The cell value.
     */
    public static function normalisedNumber(string $cell): ?string
    {
        $cell = str_replace([' ', "\u{00A0}", "\u{202F}"], '', trim($cell));

        if (preg_match('/^-?\d+(\.\d+)?$/', $cell) === 1) {
            return $cell;
        }
        if (preg_match('/^-?\d+,\d+$/', $cell) === 1) {
            return str_replace(',', '.', $cell);
        }
        if (preg_match('/^-?\d{1,3}(\.\d{3})+(,\d+)?$/', $cell) === 1) {
            return str_replace(['.', ','], ['', '.'], $cell);
        }
        if (preg_match('/^-?\d{1,3}(,\d{3})+(\.\d+)?$/', $cell) === 1) {
            return str_replace(',', '', $cell);
        }

        return null;
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
        $this->reviewedValues = $values;

        if ($flags !== []) {
            $this->reviewedRows->forget();

            return;
        }

        $this->reviewedRows->store($fields, $values, $this->reviewedEntity);
    }
}
