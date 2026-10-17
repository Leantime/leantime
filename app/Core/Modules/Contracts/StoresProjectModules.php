<?php

namespace Leantime\Core\Modules\Contracts;

/**
 * Persistence for a project's module switches.
 *
 * Only the choices that differ from a module's default are stored, so a module added later (or a
 * newly installed plugin) needs no backfill. Implemented by the Projects repository, which keeps
 * them in the `zp_projects.metadata` JSON column.
 *
 * Deliberately narrow: the permission engine reads this on every project-scoped check, so the
 * implementation must not pull the Projects service (and its dependency graph) in.
 */
interface StoresProjectModules
{
    /**
     * The stored module switches of a project.
     *
     * @return array<string, bool> Module id => enabled. Empty when the project has none or doesn't exist.
     */
    public function getModuleOverrides(int $projectId): array;

    /**
     * Replace the stored module switches of a project.
     *
     * @param  array<string, bool>  $overrides  Module id => enabled.
     */
    public function saveModuleOverrides(int $projectId, array $overrides): void;
}
