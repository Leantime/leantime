<?php

namespace Leantime\Domain\Connector\Models;

class Provider
{
    // Unique identifier of provider
    public $id;

    // Friendly name
    public string $name;

    public string $description;

    // Image to show in UI
    public string $image;

    // Entities available to sync/import as part of this provider
    // This should be a list of strings with the exact entity name as they appear in the provider api
    // project, issue, epic, ticket or similar
    public array $availableEntities = [];

    public array $availableMethods = []; // import and/or sync

    /**
     * Define the steps for provider integration. Some steps may not be needed for some providers
     * (for example CSV does not need a sync)
     * Only used for status indicator. Controller does not check this.
     *
     * @var array|string[]
     */
    public array $steps = [
        'connect',
        'entity',
        'fields',
        'sync',
        'parse',
        'import',
    ];

    // Step titles are language keys.
    public array $stepDetails = [
        'connect' => [
            'title' => 'label.import_step_connect',
            'position' => 1,
        ],
        'entity' => [
            'title' => 'label.import_step_entity',
            'position' => 2,
        ],
        'fields' => [
            'title' => 'label.import_step_fields',
            'position' => 3,
        ],
        'sync' => [
            'title' => 'label.import_step_sync',
            'position' => 4,
        ],
        'parse' => [
            'title' => 'label.import_step_parse',
            'position' => 5,
        ],
        'import' => [
            'title' => 'label.import_step_import',
            'position' => 6,
        ],
    ];

    public array $button = [
        'url' => '',
        'text' => '',
    ];

    public function __construct() {}

    /**
     * The step before the given one in this provider's steps, or null for the first step.
     */
    public function previousStep(string $step): ?string
    {
        $steps = array_values($this->steps);
        $index = array_search($step, $steps, true);

        return $index === false || $index === 0 ? null : $steps[$index - 1];
    }

    /**
     * The page of one step of this provider's import. Providers with pages of their own (a file
     * upload) override it.
     *
     * @param  string  $step  A step of $steps.
     * @param  int|null  $integrationId  The integration being set up, if there is one.
     */
    public function stepUrl(string $step, ?int $integrationId = null): string
    {
        return BASE_URL.'/connector/integration/?provider='.$this->id.'&step='.$step
            .($integrationId !== null ? '&integrationId='.$integrationId : '');
    }
}
