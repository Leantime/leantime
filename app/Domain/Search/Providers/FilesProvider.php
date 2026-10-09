<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Files\Permissions\FilesPermissions;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;

/**
 * Uploaded files by name. Project, ticket and wiki files follow the project scope; private
 * and user files are visible to their uploader only.
 */
class FilesProvider implements SearchProvider
{
    public function __construct(
        private SearchRepository $searchRepository,
        private PermissionService $permissions,
    ) {}

    public function key(): string
    {
        return 'files';
    }

    public function label(): string
    {
        return __('search.type.files');
    }

    public function icon(): string
    {
        return 'fa-regular fa-folder-open';
    }

    /**
     * The domain's view capability, checked against the user's global role: search spans every
     * accessible project, so a per-project custom role cannot be applied per hit.
     */
    public function available(): bool
    {
        return $this->permissions->currentUserCan(FilesPermissions::VIEW, null, true);
    }

    /**
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        $results = [];

        foreach ($this->searchRepository->searchFiles($query) as $row) {
            $projectId = $row['projectId'] !== null ? (int) $row['projectId'] : null;

            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: (string) $row['realName'],
                snippet: '',
                icon: $this->iconForExtension((string) ($row['extension'] ?? '')),
                badge: strtoupper((string) ($row['extension'] ?? '')),
                projectId: $projectId,
                projectName: $projectId !== null ? (string) ($row['projectName'] ?? '') : __('search.badge.private_file'),
                modified: $row['modified'] ?? null,
            );
        }

        return $results;
    }

    /**
     * Owner-only files (private/user) are authorized here; project files by the Open
     * controller through their project.
     */
    public function resolveTarget(int $id): ?SearchTarget
    {
        $file = $this->searchRepository->getFileTarget($id);

        if ($file === null) {
            return null;
        }

        if ($file['projectId'] === null) {
            $ownerOnly = in_array($file['module'], ['private', 'user'], true);
            if (! $ownerOnly || $file['userId'] !== (int) session('userdata.id')) {
                return null;
            }
        }

        $url = '/files/get?'.http_build_query([
            'module' => $file['module'],
            'encName' => $file['encName'],
            'ext' => $file['extension'],
            'realName' => $file['realName'],
        ]);

        return new SearchTarget(url: $url, projectId: $file['projectId']);
    }

    private function iconForExtension(string $extension): string
    {
        return match (strtolower($extension)) {
            'png', 'jpg', 'jpeg', 'gif', 'webp', 'svg' => 'fa-regular fa-image',
            'pdf' => 'fa-regular fa-file-pdf',
            'doc', 'docx' => 'fa-regular fa-file-word',
            'xls', 'xlsx', 'csv' => 'fa-regular fa-file-excel',
            'zip', 'gz', 'tar', 'rar', '7z' => 'fa-regular fa-file-zipper',
            default => 'fa-regular fa-file',
        };
    }
}
