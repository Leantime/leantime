<?php

namespace Leantime\Domain\Search\Providers;

use Leantime\Core\Auth\Permissions\PermissionService;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Repositories\Search as SearchRepository;
use Leantime\Domain\Users\Permissions\UsersPermissions;

/**
 * People directory. Needs the global users.view capability (admins and owners by default);
 * the provider is simply unavailable to everyone else.
 */
class UsersProvider implements SearchProvider
{
    public function __construct(
        private SearchRepository $searchRepository,
        private PermissionService $permissions,
    ) {}

    public function key(): string
    {
        return 'users';
    }

    public function label(): string
    {
        return __('search.type.users');
    }

    public function icon(): string
    {
        return 'fa-solid fa-users';
    }

    /**
     * The domain's view capability, checked against the user's global role: search spans every
     * accessible project, so a per-project custom role cannot be applied per hit.
     */
    public function available(): bool
    {
        return $this->permissions->currentUserCan(UsersPermissions::VIEW, null, true);
    }

    /**
     * @return SearchResult[]
     */
    public function search(SearchQuery $query): array
    {
        if (! $this->available()) {
            return [];
        }

        $results = [];

        foreach ($this->searchRepository->searchUsers($query) as $row) {
            $name = trim(($row['firstname'] ?? '').' '.($row['lastname'] ?? ''));
            $role = implode(' · ', array_filter([(string) ($row['jobTitle'] ?? ''), (string) ($row['department'] ?? '')]));

            $results[] = new SearchResult(
                type: $this->key(),
                id: (int) $row['id'],
                title: $name !== '' ? $name : (string) $row['username'],
                snippet: (string) $row['username'],
                icon: 'fa-regular fa-user',
                badge: $role,
                projectId: null,
                projectName: (string) ($row['clientName'] ?? ''),
                modified: $row['modified'] ?? null,
            );
        }

        return $results;
    }

    public function resolveTarget(int $id): ?SearchTarget
    {
        if (! $this->available() || ! $this->searchRepository->userExists($id)) {
            return null;
        }

        return new SearchTarget(url: '/users/editUser/'.$id);
    }
}
