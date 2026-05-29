<?php

namespace Unit\app\Domain\Setting\Services;

use Leantime\Core\Files\Contracts\FileManagerInterface;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * Unit tests for the Setting service helpers extracted during the
 * thin-controller refactor (getProjectLabel).
 */
class SettingServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /**
     * Builds a real Setting service, allowing each dependency to be
     * overridden with a stub so we can observe the label resolution.
     */
    private function makeService(
        ?SettingRepository $settingsRepo = null,
        ?TicketRepository $ticketsRepo = null,
        ?IdeaRepository $ideaRepo = null,
    ): SettingService {
        return new SettingService(
            $settingsRepo ?? $this->make(SettingRepository::class),
            $this->makeEmpty(FileManagerInterface::class),
            $ticketsRepo ?? $this->make(TicketRepository::class),
            $ideaRepo ?? $this->make(IdeaRepository::class),
        );
    }

    public function test_get_project_label_reads_ticket_state_label_name(): void
    {
        $ticketsRepo = $this->make(TicketRepository::class, [
            'getStateLabels' => fn () => [
                3 => ['name' => 'In Progress'],
            ],
        ]);

        $label = $this->makeService(ticketsRepo: $ticketsRepo)->getProjectLabel('ticketlabels', 3, 1);

        $this->assertSame('In Progress', $label);
    }

    public function test_get_project_label_returns_empty_for_missing_ticket_label(): void
    {
        $ticketsRepo = $this->make(TicketRepository::class, [
            'getStateLabels' => fn () => [
                3 => ['name' => 'In Progress'],
            ],
        ]);

        $label = $this->makeService(ticketsRepo: $ticketsRepo)->getProjectLabel('ticketlabels', 99, 1);

        $this->assertSame('', $label);
    }

    public function test_get_project_label_reads_idea_label_name(): void
    {
        $ideaRepo = $this->make(IdeaRepository::class, [
            'getCanvasLabels' => fn () => [
                1 => ['name' => 'Backlog', 'class' => 'label-default'],
            ],
        ]);

        $label = $this->makeService(ideaRepo: $ideaRepo)->getProjectLabel('idealabels', 1, 1);

        $this->assertSame('Backlog', $label);
    }

    public function test_get_project_label_returns_empty_for_unknown_module(): void
    {
        $label = $this->makeService()->getProjectLabel('doesnotexist', 1, 1);

        $this->assertSame('', $label);
    }
}
