<?php

namespace Unit\app\Domain\Tags\Services;

use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Blueprints\Repositories\Blueprints as CanvaRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tags\Services\Tags as TagService;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;
use Unit\TestCase;

/**
 * Unit tests for the project-access authorization added to Tags::getTags when the
 * /api/tags REST controller (which forced session('currentProject')) was retired in
 * favour of the JSON-RPC entry point Tags.Tags.getTags.
 */
class TagsServiceTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        session(['userdata.id' => 1]);
    }

    private function makeService(
        ProjectRepository $projectRepo,
        ?TicketRepository $ticketRepo = null,
        ?CanvaRepository $canvasRepo = null,
    ): TagService {
        return new TagService(
            $projectRepo,
            $canvasRepo ?? $this->make(CanvaRepository::class, ['getTags' => fn () => []]),
            $ticketRepo ?? $this->make(TicketRepository::class, ['getTags' => fn () => []]),
        );
    }

    public function test_get_tags_throws_and_does_not_query_when_user_cannot_access_project(): void
    {
        $queryCalls = 0;
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => false,
        ]);
        $ticketRepo = $this->make(TicketRepository::class, [
            'getTags' => function () use (&$queryCalls) {
                $queryCalls++;

                return [];
            },
        ]);

        $thrown = null;
        try {
            $this->makeService($projectRepo, $ticketRepo)->getTags(99, '');
        } catch (AuthorizationException $e) {
            $thrown = $e;
        }

        // No access must be a distinct, thrown signal -- NOT an empty array (which means "no matching tags").
        $this->assertInstanceOf(AuthorizationException::class, $thrown, 'A user must not read tags for a project they cannot access');
        $this->assertSame(0, $queryCalls, 'Unauthorized request must not even query the tag tables');
    }

    public function test_get_tags_returns_filtered_tags_for_accessible_project(): void
    {
        $projectRepo = $this->make(ProjectRepository::class, [
            'isUserAssignedToProject' => fn () => true,
        ]);
        $ticketRepo = $this->make(TicketRepository::class, [
            'getTags' => fn () => [['tags' => 'backend,frontend']],
        ]);
        $canvasRepo = $this->make(CanvaRepository::class, [
            'getTags' => fn () => [['tags' => 'design']],
        ]);

        $result = $this->makeService($projectRepo, $ticketRepo, $canvasRepo)->getTags(5, 'end');
        sort($result);

        // "backend" and "frontend" both contain "end"; "design" does not.
        $this->assertSame(['backend', 'frontend'], $result);
    }
}
