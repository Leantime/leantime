<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Comments\Permissions\CommentsPermissions;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Search\Support\Highlighter;

/**
 * Comments on tickets, projects, wiki articles, ideas and canvas items. A comment opens
 * its host entity.
 */
class CommentsProvider implements SearchProvider
{
    public function __construct(
        private SearchRepository $searchRepository,
        private PermissionService $permissions,
    ) {}

    public function key(): string
    {
        return 'comments';
    }

    public function label(): string
    {
        return __('search.type.comments');
    }

    public function icon(): string
    {
        return 'fa-regular fa-comments';
    }

    /**
     * The domain's view capability, checked against the user's global role: search spans every
     * accessible project, so a per-project custom role cannot be applied per hit.
     */
    public function available(): bool
    {
        return $this->permissions->currentUserCan(CommentsPermissions::VIEW, null, true);
    }

    /**
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        $results = [];

        foreach ($this->searchRepository->searchComments($query) as $row) {
            $author = trim(($row['authorFirstname'] ?? '').' '.($row['authorLastname'] ?? ''));
            $hostTitle = Highlighter::snippet($row['hostTitle'] ?? null, [], 80);

            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: Highlighter::snippet($row['text'] ?? null, $query->tokens, 120),
                snippet: $hostTitle !== '' ? sprintf(__('search.comment_on'), $hostTitle) : '',
                icon: $this->icon(),
                badge: $author,
                projectId: (int) $row['projectId'],
                projectName: (string) ($row['projectName'] ?? ''),
                modified: $row['modified'] ?? null,
                modalPath: $this->modalPathFor((string) $row['module'], (int) $row['moduleId']),
            );
        }

        return $results;
    }

    public function resolveTarget(int $id): ?SearchTarget
    {
        $comment = $this->searchRepository->getCommentTarget($id, (int) session('userdata.id'));

        if ($comment === null) {
            return null;
        }

        $module = $comment['module'];
        $modalPath = $this->modalPathFor($module, $comment['moduleId']);

        $url = match (true) {
            $module === 'ticket' => '/dashboard/home#'.$modalPath,
            $module === 'project' => '/dashboard/show',
            $module === 'article' => '/wiki/show/'.$comment['moduleId'],
            $module === 'idea' => '/ideas/showBoards#'.$modalPath,
            $module === 'goalcanvasitem' => '/goalcanvas/dashboard#'.$modalPath,
            // Any other {slug}canvasitem is a strategy blueprint item: open its board with the item modal.
            $this->blueprintSlug($module) !== null && $comment['canvasId'] !== null => '/blueprints/'.$this->blueprintSlug($module).'/showCanvas/'.$comment['canvasId'].'#'.$modalPath,
            default => '/dashboard/show',
        };

        return new SearchTarget(url: $url, projectId: $comment['projectId']);
    }

    /**
     * Hash route of the host's modal when it has one.
     */
    private function modalPathFor(string $module, int $moduleId): ?string
    {
        $slug = $this->blueprintSlug($module);

        return match (true) {
            $module === 'ticket' => '/tickets/showTicket/'.$moduleId,
            $module === 'idea' => '/ideas/ideaDialog/'.$moduleId,
            $module === 'goalcanvasitem' => '/goalcanvas/editCanvasItem/'.$moduleId,
            $slug !== null => '/blueprints/'.$slug.'/editCanvasItem/'.$moduleId,
            default => null,
        };
    }

    /**
     * Blueprint slug for a "{slug}canvasitem" comment module (e.g. "swotcanvasitem" → "swot"),
     * null for every other module including goals, which have their own pages.
     */
    private function blueprintSlug(string $module): ?string
    {
        $suffix = 'canvasitem';

        if ($module === 'goalcanvasitem' || ! str_ends_with($module, $suffix) || strlen($module) <= strlen($suffix)) {
            return null;
        }

        return substr($module, 0, -strlen($suffix));
    }
}
