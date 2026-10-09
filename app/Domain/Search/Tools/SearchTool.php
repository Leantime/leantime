<?php

namespace Leantime\Domain\Search\Tools;

use Illuminate\Support\Str;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;
use Laravel\Mcp\Server\Tools\ToolInputSchema;
use Laravel\Mcp\Server\Tools\ToolResult;
use Leantime\Domain\Search\Models\SearchResult;
use Leantime\Domain\Search\Services\Search;

/**
 * Global search across every entity type the user may see, for AI clients.
 *
 * Thin wrapper over the Search service: the same providers, access scope and filters as the
 * header search. Results carry an "open" URL that goes through /search/open, so the link
 * works from any project context.
 */
#[IsReadOnly]
class SearchTool extends Tool
{
    private const DEFAULT_LIMIT = 10;

    private const MAX_LIMIT = 50;

    public function __construct(private Search $searchService) {}

    public function name(): string
    {
        return 'search';
    }

    public function description(): string
    {
        return 'Global search across to-dos, projects, docs (wiki), ideas, goals, blueprints, comments, files and people (and plugin types such as notes). '
            .'Use this first when you do not know what kind of entity you are looking for, or to find anything by keyword. '
            .'Closed projects and archived to-dos are never returned. Results respect the current user\'s project access. '
            .'Returns results grouped by type with ids you can pass to the type-specific tools (findTasks, getTask, getProject, …).';
    }

    public function schema(ToolInputSchema $schema): ToolInputSchema
    {
        return $schema
            ->string('term')->description('Search words (at least 2 characters). Every word must match.')->required()
            ->raw('types', ['type' => 'array', 'items' => ['type' => 'string'], 'description' => 'Restrict to result types, e.g. ["tickets","wiki"]. Available: '.implode(', ', array_keys($this->searchService->getProviders())).'. Omit for all.'])
            ->integer('projectId')->description('Only results from this project.')
            ->string('modifiedAfter')->description('Only results changed on or after this date (YYYY-MM-DD, user timezone).')
            ->string('modifiedBefore')->description('Only results changed on or before this date (YYYY-MM-DD, user timezone).')
            ->raw('mine', ['type' => 'boolean', 'description' => 'Only items the current user authored or owns.'])
            ->integer('limit')->description('Maximum results per type. Default '.self::DEFAULT_LIMIT.', max '.self::MAX_LIMIT.'.');
    }

    /**
     * Handle the tool request.
     */
    public function handle(array $arguments): ToolResult
    {
        $term = trim((string) ($arguments['term'] ?? ''));
        if (mb_strlen($term) < 2) {
            return ToolResult::error('The search term must be at least 2 characters long.');
        }

        $limit = max(1, min(self::MAX_LIMIT, (int) ($arguments['limit'] ?? self::DEFAULT_LIMIT)));

        $providers = $this->searchService->getProviders();
        $requestedTypes = is_array($arguments['types'] ?? null) ? array_map('strval', $arguments['types']) : [];
        $unknownTypes = array_diff($requestedTypes, array_keys($providers));
        if ($unknownTypes !== []) {
            return ToolResult::error('Unknown result types: '.implode(', ', $unknownTypes).'. Available: '.implode(', ', array_keys($providers)).'.');
        }
        $types = $requestedTypes !== [] ? $requestedTypes : array_keys($providers);

        $filters = [
            'projectId' => (int) ($arguments['projectId'] ?? 0),
            'from' => (string) ($arguments['modifiedAfter'] ?? ''),
            'to' => (string) ($arguments['modifiedBefore'] ?? ''),
            'mine' => ! empty($arguments['mine']),
        ];

        $response = "## Search results for '".Str::sanitizeForLLM($term, true)."'\n";
        $total = 0;

        foreach ($types as $type) {
            $results = $this->searchService->search($term, $type, $limit, 0, $filters);
            if ($results === []) {
                continue;
            }

            $total += count($results);
            $response .= "\n### ".$providers[$type]->label()." ({$type})\n";

            foreach ($results as $result) {
                $response .= Str::toMarkdown($this->describe($result), 4)."\n";
            }
        }

        if ($total === 0) {
            return ToolResult::text("No results found for '".Str::sanitizeForLLM($term, true)."'.");
        }

        return ToolResult::text($response);
    }

    /**
     * Flat, LLM-safe description of one hit.
     *
     * @return array<string, mixed>
     */
    private function describe(SearchResult $result): array
    {
        $description = [
            'type' => $result->type,
            'id' => $result->id,
            'title' => Str::sanitizeForLLM($result->title, true),
        ];

        if ($result->badge !== '') {
            $description['kind'] = Str::sanitizeForLLM($result->badge, true);
        }
        if ($result->snippet !== '') {
            $description['excerpt'] = Str::sanitizeForLLM($result->snippet, true);
        }
        if ($result->projectId !== null) {
            $description['projectId'] = $result->projectId;
        }
        if ($result->projectName !== '') {
            $description['project'] = Str::sanitizeForLLM($result->projectName, true);
        }
        if ($result->modified) {
            $description['modified'] = $result->modified;
        }

        $description['open'] = BASE_URL.'/search/open?type='.rawurlencode($result->type).'&id='.$result->id;

        return $description;
    }
}
