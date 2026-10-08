<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Search\Support\Highlighter;

/**
 * Tasks, subtasks and milestones from zp_tickets.
 */
class TicketsProvider implements SearchProvider
{
    public function __construct(private SearchRepository $searchRepository) {}

    public function key(): string
    {
        return 'tickets';
    }

    public function label(): string
    {
        return __('search.type.tickets');
    }

    public function icon(): string
    {
        return 'fa-solid fa-list-check';
    }

    /**
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        $results = [];

        foreach ($this->searchRepository->searchTickets($query) as $row) {
            $type = (string) ($row['type'] ?? '');

            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: (string) $row['headline'],
                snippet: Highlighter::snippet($row['description'] ?? null, $query->tokens),
                icon: $this->iconForType($type),
                badge: $this->badgeForType($type),
                projectId: (int) $row['projectId'],
                projectName: (string) ($row['projectName'] ?? ''),
                modified: $row['modified'] ?? null,
                modalPath: $this->modalPathForType($type, (int) $row['id']),
            );
        }

        return $results;
    }

    public function resolveTarget(int $id): ?SearchTarget
    {
        $ticket = $this->searchRepository->getTicketTarget($id);

        if ($ticket === null) {
            return null;
        }

        $landingPage = $ticket['type'] === 'milestone' ? '/tickets/roadmap' : '/dashboard/home';

        return new SearchTarget(
            url: $landingPage.'#'.$this->modalPathForType($ticket['type'], $id),
            projectId: $ticket['projectId'],
        );
    }

    private function modalPathForType(string $type, int $id): string
    {
        return $type === 'milestone' ? '/tickets/editMilestone/'.$id : '/tickets/showTicket/'.$id;
    }

    private function iconForType(string $type): string
    {
        return match ($type) {
            'milestone' => 'fa-solid fa-flag',
            'subtask' => 'fa-solid fa-diagram-next',
            'bug' => 'fa-solid fa-bug',
            default => 'fa-solid fa-check',
        };
    }

    private function badgeForType(string $type): string
    {
        return match ($type) {
            'milestone' => __('search.badge.milestone'),
            'subtask' => __('search.badge.subtask'),
            default => '',
        };
    }
}
