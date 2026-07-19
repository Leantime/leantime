<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Models;

/**
 * A dependency — an external partnership, facility, or supplier a program
 * needs to succeed. `confirmed` distinguishes "we have this locked in" from
 * "we're still counting on this happening" — a useful signal for the report's
 * needs-attention pass.
 */
final class Dependency
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $partnerName,
        public readonly string $type,
        public readonly bool $confirmed,
    ) {}
}
