<?php

namespace Leantime\Domain\ContentTemplates\Contracts;

use Leantime\Domain\ContentTemplates\Models\ContentTemplate;

/**
 * Applier — turns a ContentTemplate into rows in the target system.
 *
 * Registry holds DEFINITIONS; appliers know how to project those definitions
 * into the database for a specific target type (canvas items, wiki articles, …).
 * Each appliesTo value maps to exactly one Applier; appliers self-describe
 * which appliesTo values they handle via supports().
 */
interface Applier
{
    /**
     * @return bool true if this applier handles the given appliesTo value.
     */
    public function supports(string $appliesTo): bool;

    /**
     * Apply the template's content to the target.
     *
     * @param  int  $targetId  ID of the target object (canvas id, wiki id, …).
     * @param  ContentTemplate  $template  The template to project.
     * @param  array{
     *     userId?: int,
     *     mode?: 'add'|'replace',
     * }  $options  Apply-time options.
     *     userId  — author/creator id for inserted rows; falls back to session user.
     *     mode    — 'add' to keep existing content, 'replace' to delete first.
     *               Defaults to 'add'.
     * @return int Number of top-level entities created (canvas items, articles, …).
     */
    public function apply(int $targetId, ContentTemplate $template, array $options = []): int;
}
