<?php

use Leantime\Core\Events\EventDispatcher;
use Leantime\Domain\WorkStructure\Services\StructureRegistry;

/**
 * Seed the built-in "Project" work structure on boot.
 *
 * Runs once (idempotent) — StructureRegistry::register() checks for duplicates.
 */
EventDispatcher::add_event_listener(
    'leantime.core.middleware.loadplugins.handle.after_plugins_loaded',
    function () {
        try {
            /** @var StructureRegistry $registry */
            $registry = app()->make(StructureRegistry::class);

            $registry->register(
                'Project',
                'system',
                [
                    ['typeKey' => 'milestone', 'label' => 'Milestone', 'domainReference' => 'Leantime\\Domain\\Tickets', 'sortOrder' => 1],
                    ['typeKey' => 'task', 'label' => 'Task', 'domainReference' => 'Leantime\\Domain\\Tickets', 'sortOrder' => 2],
                    ['typeKey' => 'goal', 'label' => 'Goal', 'domainReference' => 'Leantime\\Domain\\Goalcanvas', 'sortOrder' => 3],
                ],
                [
                    ['fromTypeKey' => 'task', 'toTypeKey' => 'milestone', 'relationshipType' => 'belongs_to'],
                    ['fromTypeKey' => 'milestone', 'toTypeKey' => 'goal', 'relationshipType' => 'measures'],
                ]
            );
        } catch (\Exception $e) {
            // Tables may not exist yet during install — degrade gracefully
        }
    }
);
