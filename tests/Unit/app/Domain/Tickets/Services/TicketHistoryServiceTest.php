<?php

namespace Unit\app\Domain\Tickets\Services;

use Carbon\CarbonImmutable;
use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Core\Configuration\Environment as EnvironmentCore;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Tickets\Models\TicketHistoryEntry;
use Leantime\Domain\Tickets\Models\Tickets as TicketModel;
use Leantime\Domain\Tickets\Permissions\TicketsPermissions;
use Leantime\Domain\Tickets\Repositories\TicketHistory as TicketHistoryRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Leantime\Domain\Tickets\Services\TicketHistory as TicketHistoryService;
use Unit\TestCase;

/**
 * Ticket history: authorization against the ticket's own project (fail closed) and the
 * formatting of recorded raw values into display values.
 */
class TicketHistoryServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        session(['usersettings.timezone' => 'UTC']);
        session(['usersettings.language' => 'en-US']);
        session(['usersettings.date_format' => 'Y-m-d']);
        session(['usersettings.time_format' => 'H:i']);
        session(['userdata' => ['id' => 1, 'role' => 'editor']]);

        app()->instance(EnvironmentCore::class, $this->make(EnvironmentCore::class, [
            'defaultTimezone' => 'UTC',
            'language' => 'en-US',
        ]));
        app()->instance(LanguageCore::class, $this->fakeLanguage());

        CarbonImmutable::mixin(new CarbonMacros('UTC', 'en-US', 'Y-m-d', 'H:i'));
    }

    public function test_missing_ticket_returns_false(): void
    {
        $service = $this->buildService(ticket: false, rows: []);

        $this->assertFalse($service->getTicketHistory(5));
    }

    public function test_invalid_ticket_id_returns_false_without_lookup(): void
    {
        $service = $this->buildService(ticket: false, rows: [], getTicket: function () {
            throw new \RuntimeException('must not look up a ticket for id 0');
        });

        $this->assertFalse($service->getTicketHistory(0));
    }

    public function test_ticket_in_a_project_the_user_cannot_view_returns_false(): void
    {
        $checks = [];
        $service = $this->buildService(
            ticket: $this->ticketIn(7),
            rows: [$this->row(1, 'headline', 'Secret')],
            allowedProjects: [9],
            checks: $checks,
            getTicketChanges: function () {
                throw new \RuntimeException('must not read history when access is denied');
            },
        );

        $this->assertFalse($service->getTicketHistory(5));
        // Authorized against the ticket's real project, not the session project.
        $this->assertSame([[TicketsPermissions::VIEW, 7]], $checks);
    }

    public function test_entries_are_newest_first_with_previous_values(): void
    {
        $service = $this->buildService(ticket: $this->ticketIn(7), rows: [
            $this->row(1, 'headline', 'First title', '2026-01-01 10:00:00'),
            $this->row(2, 'headline', 'Second title', '2026-01-02 10:00:00'),
            $this->row(3, 'headline', 'Third title', '2026-01-03 10:00:00'),
        ]);

        $entries = $service->getTicketHistory(5);

        $this->assertIsArray($entries);
        $this->assertCount(3, $entries);
        $this->assertContainsOnlyInstancesOf(TicketHistoryEntry::class, $entries);

        $this->assertSame(3, $entries[0]->id);
        $this->assertSame('Second title', $entries[0]->oldValue);
        $this->assertSame('Third title', $entries[0]->newValue);
        $this->assertSame('Headline', $entries[0]->fieldLabel);
        $this->assertSame('Jane Doe', $entries[0]->userName);

        // The first recorded change has no known previous value.
        $this->assertNull($entries[2]->oldValue);
        $this->assertSame('First title', $entries[2]->newValue);
    }

    public function test_status_priority_type_and_editor_values_are_mapped_to_labels(): void
    {
        $service = $this->buildService(ticket: $this->ticketIn(7), rows: [
            $this->row(1, 'status', '3'),
            $this->row(2, 'status', '0'),
            $this->row(3, 'priority', '2'),
            $this->row(4, 'type', 'bug'),
            $this->row(5, 'editors', '42'),
            $this->row(6, 'editors', '0'),
        ]);

        $entries = $service->getTicketHistory(5);
        $byId = [];
        foreach ($entries as $entry) {
            $byId[$entry->id] = $entry;
        }

        $this->assertSame('New', $byId[2]->oldValue);
        $this->assertSame('Done', $byId[2]->newValue);
        $this->assertSame('High', $byId[3]->newValue);
        $this->assertSame('Bug', $byId[4]->newValue);
        $this->assertSame('Bob Builder', $byId[5]->newValue);
        $this->assertSame('Bob Builder', $byId[6]->oldValue);
        $this->assertSame('Not assigned', $byId[6]->newValue);
    }

    public function test_truncated_history_keeps_the_previous_value_of_the_first_retained_change(): void
    {
        // Exactly MAX_ENTRIES rows: the oldest retained change of each field has an older,
        // non-retained predecessor that must still provide its "from" value.
        $rows = [$this->row(101, 'status', '0', '2026-01-01 09:00:00')];
        for ($i = 1; $i < TicketHistoryService::MAX_ENTRIES; $i++) {
            $rows[] = $this->row(101 + $i, 'headline', 'Title '.$i, '2026-01-02 10:00:00');
        }

        $lookups = [];
        $service = $this->buildService(
            ticket: $this->ticketIn(7),
            rows: $rows,
            getLatestChangeBefore: function (int $ticketId, string $field, string $beforeDate, int $beforeId) use (&$lookups): ?array {
                $lookups[] = [$ticketId, $field, $beforeDate, $beforeId];

                return match ($field) {
                    'status' => $this->row(50, 'status', '3', '2025-12-01 10:00:00'),
                    'headline' => $this->row(60, 'headline', 'Original title', '2025-12-02 10:00:00'),
                    default => null,
                };
            },
        );

        $entries = $service->getTicketHistory(5);

        $this->assertCount(TicketHistoryService::MAX_ENTRIES, $entries);

        // Predecessors are looked up relative to the oldest retained row.
        $this->assertContains([5, 'status', '2026-01-01 09:00:00', 101], $lookups);
        $this->assertContains([5, 'headline', '2026-01-01 09:00:00', 101], $lookups);

        $oldestEntry = $entries[count($entries) - 1];
        $this->assertSame('status', $oldestEntry->field);
        $this->assertSame('New', $oldestEntry->oldValue);
        $this->assertSame('Done', $oldestEntry->newValue);

        $firstHeadlineEntry = $entries[count($entries) - 2];
        $this->assertSame('Original title', $firstHeadlineEntry->oldValue);
        $this->assertSame('Title 1', $firstHeadlineEntry->newValue);
    }

    public function test_dates_are_formatted_for_the_user(): void
    {
        $service = $this->buildService(ticket: $this->ticketIn(7), rows: [
            $this->row(1, 'deadline', '2026-03-04 15:30:00'),
        ]);

        $entries = $service->getTicketHistory(5);

        $this->assertSame('2026-03-04 15:30', $entries[0]->newValue);
        $this->assertSame('Due Date', $entries[0]->fieldLabel);
    }

    public function test_description_changes_do_not_expose_the_content(): void
    {
        $service = $this->buildService(ticket: $this->ticketIn(7), rows: [
            $this->row(1, 'description', '<p>old <script>alert(1)</script></p>'),
            $this->row(2, 'description', '<p>new body</p>'),
        ]);

        $entries = $service->getTicketHistory(5);

        foreach ($entries as $entry) {
            $this->assertTrue($entry->isDescriptionChange);
            $this->assertNull($entry->oldValue);
            $this->assertNull($entry->newValue);
        }
    }

    public function test_project_moves_only_name_projects_the_user_can_view(): void
    {
        $service = $this->buildService(ticket: $this->ticketIn(7), rows: [
            $this->row(1, 'project', '9'),
            $this->row(2, 'project', '7'),
        ]);

        $entries = $service->getTicketHistory(5);

        // Project 9 is not visible to the user (getProject() returns false) -> shown by id only.
        $this->assertSame('#9', $entries[0]->oldValue);
        $this->assertSame('Visible project', $entries[0]->newValue);
    }

    public function test_deleted_user_is_shown_as_unknown(): void
    {
        $row = $this->row(1, 'headline', 'Title');
        $row['firstname'] = null;
        $row['lastname'] = null;

        $service = $this->buildService(ticket: $this->ticketIn(7), rows: [$row]);

        $this->assertSame('Unknown user', $service->getTicketHistory(5)[0]->userName);
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @param  array<int, int>  $allowedProjects
     * @param  array<int, array{0: string, 1: int|null}>  $checks
     */
    private function buildService(
        TicketModel|false $ticket,
        array $rows,
        array $allowedProjects = [7],
        array &$checks = [],
        ?\Closure $getTicket = null,
        ?\Closure $getTicketChanges = null,
        ?\Closure $getLatestChangeBefore = null,
    ): TicketHistoryService {
        $ticketRepository = $this->make(TicketRepository::class, [
            'getTicket' => $getTicket ?? fn () => $ticket,
            'getStateLabels' => fn () => [
                3 => ['name' => 'New'],
                4 => ['name' => 'In Progress'],
                0 => ['name' => 'Done'],
            ],
        ]);

        $historyRepository = $this->make(TicketHistoryRepository::class, [
            'getTicketChanges' => $getTicketChanges ?? fn () => $rows,
            'getLatestChangeBefore' => $getLatestChangeBefore ?? function () {
                throw new \RuntimeException('must not look up older changes for an untruncated history');
            },
            'getUserNames' => fn (array $ids) => array_intersect_key([42 => 'Bob Builder'], array_flip($ids)),
        ]);

        $projectService = $this->make(ProjectService::class, [
            'getProject' => fn (int $id) => $id === 7 ? ['id' => 7, 'name' => 'Visible project'] : false,
        ]);

        $service = new TicketHistoryService(
            ticketHistoryRepo: $historyRepository,
            ticketRepository: $ticketRepository,
            projectService: $projectService,
            language: $this->fakeLanguage(),
        );

        $service->setPermissionService($this->make(PermissionService::class, [
            'currentUserCan' => function (string $key, ?int $projectId = null) use ($allowedProjects, &$checks): bool {
                $checks[] = [$key, $projectId];

                return in_array($projectId, $allowedProjects, true);
            },
        ]));

        return $service;
    }

    private function fakeLanguage(): LanguageCore
    {
        $translations = [
            'language.dateformat' => 'Y-m-d',
            'language.timeformat' => 'H:i',
            'label.headline' => 'Headline',
            'label.due_date' => 'Due Date',
            'label.bug' => 'Bug',
            'label.not_assigned_to_user' => 'Not assigned',
            'label.history_unknown_user' => 'Unknown user',
        ];

        $language = $this->createMock(LanguageCore::class);
        $language->method('__')->willReturnCallback(fn (string $key) => $translations[$key] ?? $key);

        return $language;
    }

    private function ticketIn(int $projectId): TicketModel
    {
        return $this->make(TicketModel::class, ['id' => 5, 'projectId' => $projectId, 'headline' => 'T5']);
    }

    /**
     * A raw history row as returned by TicketHistoryRepository::getTicketChanges().
     *
     * @return array<string, mixed>
     */
    private function row(int $id, string $changeType, string $changeValue, string $dateModified = '2026-01-01 10:00:00'): array
    {
        return [
            'id' => $id,
            'userId' => 1,
            'changeType' => $changeType,
            'changeValue' => $changeValue,
            'dateModified' => $dateModified,
            'firstname' => 'Jane',
            'lastname' => 'Doe',
        ];
    }
}
