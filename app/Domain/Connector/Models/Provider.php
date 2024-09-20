<?php

namespace Leantime\Domain\Connector\Models;

class Provider
{
    //Unique identifier of provider
    /**
     * @var
     */
    public $id;

    //Friendly name
    /**
     * @var string
     */
    public string $name;

    /**
     * @var string
     */
    public string $description;

    //Image to show in UI
    /**
     * @var string
     */
    public string $image;

    //Entities available to sync/import as part of this provider
    //This should be a list of strings with the exact entity name as they appear in the provider api
    //project, issue, epic, ticket or similar
    /**
     * @var array
     */
    public array $availableEntities = [];

    /**
     * @var array
     */
    public array $availableMethods = []; //import and/or sync

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

    public array $stepDetails = [
        'connect' => [
            'title'    => 'Connect',
            'position' => 1,
        ],
        'entity' => [
            'title'    => 'Entity Mapping',
            'position' => 2,
        ],
        'fields' => [
            'title'    => 'Field Matching',
            'position' => 3,
        ],
        'sync' => [
            'title'    => 'Synchonize',
            'position' => 4,
        ],
        'parse' => [
            'title'    => 'Validate',
            'position' => 5,
        ],
        'import' => [
            'title'    => 'Import',
            'position' => 6,
        ],
    ];

    public array $button = [
        'url'  => '',
        'text' => '',
    ];

    public function __construct()
    {
    }
}
