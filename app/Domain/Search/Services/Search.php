<?php

namespace Leantime\Domain\Search\Services;

use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Modules\ProjectModules;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Search\Contracts\SearchProvider;
use Leantime\Domain\Search\Models\SearchQuery;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Models\SearchTarget;
use Leantime\Domain\Search\Providers\BlueprintsProvider;
use Leantime\Domain\Search\Providers\CommentsProvider;
use Leantime\Domain\Search\Providers\FilesProvider;
use Leantime\Domain\Search\Providers\GoalsProvider;
use Leantime\Domain\Search\Providers\IdeasProvider;
use Leantime\Domain\Search\Providers\ProjectsProvider;
use Leantime\Domain\Search\Providers\TicketsProvider;
use Leantime\Domain\Search\Providers\UsersProvider;
use Leantime\Domain\Search\Providers\WikiProvider;

/**
 * Global search across entity types. Owns the provider registry, the access scope and the
 * shared filter vocabulary; providers own their queries.
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
        WikiProvider::class,
        IdeasProvider::class,
        GoalsProvider::class,
        BlueprintsProvider::class,
        CommentsProvider::class,
        FilesProvider::class,
        UsersProvider::class,
    ];

    /**
     * Project module each provider's results belong to (Project settings -> Modules), where the
     * key differs from the module id. Other keys are looked up as module ids as they are, so a
     * plugin provider keyed by its plugin id follows its module; keys that aren't modules are
     * searched everywhere.
     */
    private const MODULE_BY_PROVIDER = [
        'goals' => 'goalcanvas',
        // The Files module only hides the file library; attachments stay searchable.
        'files' => null,
    ];

    /**
     * @var array<string, SearchProvider>|null
     */
    private ?array $providers = null;

    public function __construct(
        private ProjectService $projectService,
        private ProjectModules $projectModules,
    ) {}

    /**
     * Providers available to the session user, keyed by their key, core first, then
     * plugin-registered ones.
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
            if ($provider instanceof SearchProvider && $provider->available()) {
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

        if (! $query->isSearchable()) {
            return [];
        }

        $groups = [];
        foreach ($this->getProviders() as $key => $provider) {
            $hits = $provider->search($this->withoutSwitchedOffProjects($query, $key));
            if ($hits !== []) {
                $groups[$key] = $hits;
            }
        }

        return $groups;
    }

    /**
     * Paged results for one entity type (the full results page loads one panel per type).
     *
     * @param  array<string, mixed>  $filters  Normalized filters, see normalizeFilters().
     * @return SearchResult[]
     *
     * @throws InvalidArgumentException When $type is not an available provider.
     *
     * @api
     */
    public function search(string $term, string $type, int $limit = 20, int $offset = 0, array $filters = []): array
    {
        $provider = $this->getProvider($type);

        if ($provider === null) {
            throw new InvalidArgumentException('Unknown search type: '.$type);
        }

        $query = $this->buildQuery($term, $limit, $offset, self::normalizeFilters($filters));

        if (! $query->isSearchable()) {
            return [];
        }

        return $provider->search($this->withoutSwitchedOffProjects($query, $type));
    }

    /**
     * Where a result opens; null when the type is unknown, the entity does not exist or its
     * project has the type's module switched off.
     *
     * Not an @api method, so JSON-RPC never dispatches it; the Open controller is its only
     * caller and authorizes against the returned project before revealing anything.
     */
    public function resolveTarget(string $type, int $id): ?SearchTarget
    {
        $target = $this->getProvider($type)?->resolveTarget($id);

        if ($target === null || $target->projectId === null) {
            return $target;
        }

        $moduleId = self::moduleFor($type);

        if ($moduleId !== null && ! $this->projectModules->isEnabled($moduleId, $target->projectId)) {
            return null;
        }

        return $target;
    }

    /**
     * Leave out the projects that have the provider's module switched off.
     */
    private function withoutSwitchedOffProjects(SearchQuery $query, string $providerKey): SearchQuery
    {
        $moduleId = self::moduleFor($providerKey);

        if ($moduleId === null) {
            return $query;
        }

        return $query->withoutProjects($this->projectModules->switchedOffProjectIds($moduleId));
    }

    /**
     * The module a provider's results belong to; null when none.
     */
    private static function moduleFor(string $providerKey): ?string
    {
        if (array_key_exists($providerKey, self::MODULE_BY_PROVIDER)) {
            return self::MODULE_BY_PROVIDER[$providerKey];
        }

        return $providerKey;
    }

    /**
     * Validate raw filter input (query string or RPC params) into the vocabulary providers
     * understand. Unknown keys are dropped, bad values ignored.
     *
     * - projectId: positive int
     * - from / to: ISO dates (Y-m-d) in the user's timezone, returned as UTC database
     *   timestamps covering the whole day
     * - mine: truthy flag
     *
     * @param  array<string, mixed>  $raw
     * @return array<string, mixed>
     */
    public static function normalizeFilters(array $raw): array
    {
        $filters = [];

        $projectId = (int) ($raw['projectId'] ?? 0);
        if ($projectId > 0) {
            $filters['projectId'] = $projectId;
        }

        $from = self::dayBoundaryToUtc((string) ($raw['from'] ?? ''), 'start');
        if ($from !== null) {
            $filters['from'] = $from;
        }

        $to = self::dayBoundaryToUtc((string) ($raw['to'] ?? ''), 'end');
        if ($to !== null) {
            $filters['to'] = $to;
        }

        if (filter_var($raw['mine'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $filters['mine'] = true;
        }

        return $filters;
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

        $accessibleProjectIds = Roles::seesAllProjects(session('userdata.role'))
            ? null
            : $this->projectService->getAccessibleProjectIds($userId);

        return new SearchQuery($term, $userId, $accessibleProjectIds, $limit, $offset, $filters);
    }

    /**
     * Convert an ISO date typed in the user's timezone to the UTC timestamp of that day's
     * start or end, or null when the value is not a date.
     */
    private static function dayBoundaryToUtc(string $isoDate, string $boundary): ?string
    {
        if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $isoDate, $parts) !== 1) {
            return null;
        }

        // Carbon silently overflows "2026-13-45"; reject impossible dates up front.
        if (! checkdate((int) $parts[2], (int) $parts[3], (int) $parts[1])) {
            return null;
        }

        $timezone = session('usersettings.timezone') ?: app()->make(Environment::class)->defaultTimezone;

        try {
            $day = CarbonImmutable::createFromFormat('!Y-m-d', $isoDate, $timezone);
        } catch (\Throwable) {
            return null;
        }

        if ($day === null) {
            return null;
        }

        $moment = $boundary === 'end' ? $day->endOfDay() : $day->startOfDay();

        return $moment->setTimezone('UTC')->format('Y-m-d H:i:s');
    }
}
