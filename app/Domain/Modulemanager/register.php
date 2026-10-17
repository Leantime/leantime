<?php

/**
 * The core features a project can switch on or off (Project settings → Modules).
 *
 * To-Dos, Milestones, the project dashboard and comments are always on: too much else hangs off
 * them. Plugins add their own modules with Registration::registerModule().
 */

use Leantime\Core\Modules\Module;
use Leantime\Core\Modules\ModuleRegistry;

$moduleRegistry = app()->make(ModuleRegistry::class);

$moduleRegistry->register(new Module(
    id: 'goalcanvas',
    title: 'modules.goalcanvas.title',
    description: 'modules.goalcanvas.description',
    icon: 'fa fa-fw fa-bullseye',
    routes: ['goalcanvas', 'api.goalcanvas'],
    permissionDomain: 'goals',
));

$moduleRegistry->register(new Module(
    id: 'ideas',
    title: 'modules.ideas.title',
    description: 'modules.ideas.description',
    icon: 'fa fa-fw fa-lightbulb',
    routes: ['ideas'],
    permissionDomain: 'ideas',
));

// Routes only: the blueprints.* permissions also guard the older canvas types (Logic Model,
// whiteboards), which must keep working when the Blueprints boards are off. That is also why
// /api/canvas (those older types) is not claimed, but the boards' own /api/blueprints is.
$moduleRegistry->register(new Module(
    id: 'blueprints',
    title: 'modules.blueprints.title',
    description: 'modules.blueprints.description',
    icon: 'fa fa-fw fa-compass-drafting',
    routes: ['blueprints', 'api.blueprints'],
));

$moduleRegistry->register(new Module(
    id: 'wiki',
    title: 'modules.wiki.title',
    description: 'modules.wiki.description',
    icon: 'fa fa-fw fa-book',
    routes: ['wiki'],
    permissionDomain: 'wiki',
));

// The file library only: files.* also guards ticket attachments and images pasted into the
// editor, which stay available.
$moduleRegistry->register(new Module(
    id: 'files',
    title: 'modules.files.title',
    description: 'modules.files.description',
    icon: 'fa fa-fw fa-file',
    routes: ['files.browse', 'files.showAll'],
));

$moduleRegistry->register(new Module(
    id: 'reports',
    title: 'modules.reports.title',
    description: 'modules.reports.description',
    icon: 'fa fa-fw fa-chart-bar',
    routes: ['reports'],
    permissionDomain: 'reports',
));
