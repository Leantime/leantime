<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Models;

/**
 * A budget line — a chunk of money allocated to one project inside a program.
 *
 * `spent` is optional and may be null when the provider has no way to derive
 * actuals. Consumers rendering a "% spent" affordance should hide it when null
 * rather than showing a misleading zero.
 */
final class BudgetLine
{
    public function __construct(
        public readonly int $itemId,
        public readonly int $projectId,
        public readonly string $label,
        public readonly float $budgeted,
        public readonly ?float $spent,
        public readonly ?string $color,
    ) {}
}
