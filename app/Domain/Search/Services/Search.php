<?php

namespace Leantime\Domain\Search\Services;

use InvalidArgumentException;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Providers\ProjectsProvider;
use Leantime\Domain\Search\Providers\TicketsProvider;

/**
 * Global search across entity types. Owns the provider registry and the access scope;
 * providers own their queries.
 *
 * The searching user is always the session user. The @api methods are reachable over
 * JSON-RPC and never accept a user id.
 */
class Search
{
    use DispatchesEvents;

    /**
     * @var class-string<SearchProvider>[]
     */
    private const CORE_PROVIDERS = [
        TicketsProvider::class,
        ProjectsProvider::class,
    ];

    /**
     * @var array<string, SearchProvider>|null
     */
    private ?array $providers = null;

    public function __construct(private ProjectService $projectService) {}

    /**
     * All providers keyed by their key, core first, then plugin-registered ones.
     *
     * Plugins hook `leantime.domain.search.services.search.getProviders.providers` and add
     * SearchProvider instances to the array.
     *
     * @return array<string, SearchProvider>
     */
    public function getProviders(): array
    {
        if ($this->providers !== null) {
            return $this->providers;
        }

        $providers = [];
        foreach (self::CORE_PROVIDERS as $class) {
            $provider = app()->make($class);
            $providers[$provider->key()] = $provider;
        }

        $providers = self::dispatch_filter('providers', $providers);

        $this->providers = [];
        foreach ($providers as $provider) {
            if ($provider instanceof SearchProvider) {
                $this->providers[$provider->key()] = $provider;
            }
        }

        return $this->providers;
    }

    public function getProvider(string $key): ?SearchProvider
    {
        return $this->getProviders()[$key] ?? null;
    }

    /**
     * Top hits per entity type for the header dropdown. One request, one small query per type.
     *
     * @return array<string, SearchResult[]> Keyed by provider key; types without hits are omitted.
     *
     * @api
     */
    public function quickSearch(string $term, int $perType = 4): array
    {
        $query = $this->buildQuery($term, $perType);

        if (! $query->isSearchable() || ! $query->hasProjectAccess()) {
            return [];
        }

        $groups = [];
        foreach ($this->getProviders() as $key => $provider) {
            $hits = $provider->search($query);
            if ($hits !== []) {
                $groups[$key] = $hits;
            }
        }

        return $groups;
    }

    /**
     * Paged results for one entity type (the full results page loads one panel per type).
     *
     * @param  array<string, mixed>  $filters  Provider filters, e.g. ['projectId' => 3].
     * @return SearchResult[]
     *
     * @throws InvalidArgumentException When $type is not a registered provider.
     *
     * @api
     */
    public function search(string $term, string $type, int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $provider = $this->getProvider($type);

        if ($provider === null) {
            throw new InvalidArgumentException('Unknown search type: '.$type);
        }

        $query = $this->buildQuery($term, $limit, $offset, $filters);

        if (! $query->isSearchable() || ! $query->hasProjectAccess()) {
            return [];
        }

        return $provider->search($query);
    }

    /**
     * Where a result opens; null when the type is unknown or the entity does not exist.
     */
    public function resolveTarget(string $type, int $id): ?SearchTarget
    {
        return $this->getProvider($type)?->resolveTarget($id);
    }

    /**
     * Build the query for the session user. Admins and owners search unrestricted;
     * everyone else is scoped to the open projects they can access.
     *
     * @param  array<string, mixed>  $filters
     */
    private function buildQuery(string $term, int $limit, int $offset = 0, array $filters = []): SearchQuery
    {
        $userId = (int) session('userdata.id');

        $accessibleProjectIds = Auth::userIsAtLeast(Roles::$admin, true)
            ? null
            : $this->projectService->getAccessibleProjectIds($userId);

        return new SearchQuery($term, $userId, $accessibleProjectIds, $limit, $offset, $filters);
    }
}
