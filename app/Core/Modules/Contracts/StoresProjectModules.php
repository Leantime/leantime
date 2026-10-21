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
     * The stored module switches of several projects in one read, for filters over rows that span
     * projects.
     *
     * @param  array<int, int>  $projectIds
     * @return array<int, array<string, bool>> Project id => (module id => enabled), with an entry for
     *                                         every existing project asked for.
     */
    public function getModuleOverridesForProjects(array $projectIds): array;

    /**
     * The stored module switches of every project that has any, for queries that span all projects
     * (search excludes switched-off projects in SQL).
     *
     * @return array<int, array<string, bool>> Project id => (module id => enabled).
     */
    public function getAllModuleOverrides(): array;

    /**
     * Change the stored module switches of a project atomically: read, apply $change, write, with
     * the project row locked in between, so two admins saving at once can't undo each other.
     *
     * @param  callable(array<string, bool>): array<string, bool>  $change  Gets the stored switches, returns the new ones.
     */
    public function updateModuleOverrides(int $projectId, callable $change): void;
}
