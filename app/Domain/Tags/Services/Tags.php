<?php

namespace Leantime\Domain\Tags\Services;

use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Blueprints\Repositories\Blueprints as CanvaRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

/**
 * @api
 */
class Tags
{
    private ProjectRepository $projectRepository;

    private CanvaRepository $canvasRepository;

    private TicketRepository $ticketRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        CanvaRepository $canvasRepository,
        TicketRepository $ticketRepository
    ) {
        $this->projectRepository = $projectRepository;
        $this->canvasRepository = $canvasRepository;
        $this->ticketRepository = $ticketRepository;
    }

    /**
     * Returns the tag autocomplete suggestions for a project, filtered by $term.
     *
     * The JSON-RPC endpoint has no controller-level project gate (the retired
     * Api\Controllers\Tags forced session('currentProject'), but a JSON-RPC caller
     * can pass any projectId). isUserAssignedToProject() is the full access check —
     * it allows admins/owners, org-wide ("all") and client-level projects, and
     * directly assigned users — so this both preserves legitimate access and prevents
     * cross-project tag enumeration.
     *
     * @param  int  $projectId  The project to read tags from
     * @param  string  $term  Substring to filter tag suggestions by
     * @return array Matching tag strings (an empty array means no matches, NOT no access)
     *
     * @throws AuthorizationException If the user cannot access the project (distinct from a no-match empty result)
     *
     * @api
     */
    public function getTags(int $projectId, string $term): array
    {
        if (! $this->projectRepository->isUserAssignedToProject((int) session('userdata.id'), $projectId)) {
            throw new AuthorizationException('You do not have access to this project\'s tags.');
        }

        $tags = [];

        $ticketTags = $this->ticketRepository->getTags($projectId);
        $tags = $this->explodeAndMergeTags($ticketTags, $tags);

        $canvasTags = $this->canvasRepository->getTags($projectId);
        $tags = $this->explodeAndMergeTags($canvasTags, $tags);
        $unique = array_unique($tags);

        $tagArray = [];
        foreach ($unique as $tag) {
            if (str_contains($tag, strip_tags($term))) {
                $tagArray[] = $tag;
            }
        }

        return $tagArray;
    }

    /**
     * Splits comma-separated tag strings from DB rows and merges them into a flat list.
     *
     * @param  iterable  $dbTagValues  Rows each containing a 'tags' CSV string
     * @param  array  $mergeInto  Accumulator to merge the split tags into
     */
    private function explodeAndMergeTags($dbTagValues, array $mergeInto): array
    {
        foreach ($dbTagValues as $tagGroup) {
            if (isset($tagGroup['tags']) && $tagGroup['tags'] != null) {
                $tagArray = explode(',', $tagGroup['tags']);
                $mergeInto = array_merge($tagArray, $mergeInto);
            }
        }

        return $mergeInto;
    }
}
