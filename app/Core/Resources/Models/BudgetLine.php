<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Models;

/**
 * A budget line — a chunk of money allocated to one project inside a program.
 *
 * `spent` is a committed value: providers that don't track actuals pass 0.0.
 * We deliberately don't distinguish "unknown" from "zero" at the model level
 * because that ambiguity would leak into every consumer (report tile, UI, RPC
 * caller); providers that need to expose an "actuals unavailable" state
 * should surface it via their own domain, not through a nullable here.
 */
final class BudgetLine
{
    public function __construct(
        public readonly int $itemId,
        public readonly int $projectId,
        public readonly string $label,
        public readonly float $budgeted,
        public readonly float $spent,
        public readonly ?string $color,
    ) {}
}
