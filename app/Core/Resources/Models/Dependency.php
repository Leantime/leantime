<?php

declare(strict_types=1);

namespace Leantime\Core\Resources\Models;

/**
 * A dependency — an external commitment a program needs to succeed
 * (partnership, facility, grant, supplier, regulatory approval — things
 * outside your direct control).
 *
 * `confirmed` distinguishes "we have this locked in" from "we're still
 * counting on this happening" — the primary board-level risk signal.
 *
 * The four nullable trailing fields are optional context that surface in
 * the stakeholder report's Dependencies section when populated. Older
 * canvas items without these fields still hydrate cleanly (they just
 * render without owner/due-date/notes annotations).
 */
final class Dependency
{
    public function __construct(
        public readonly int $itemId,
        public readonly string $partnerName,
        public readonly string $type,
        public readonly bool $confirmed,
        public readonly ?string $owner = null,
        public readonly ?string $dueDate = null,
        public readonly ?string $notes = null,
        public readonly ?string $lastModified = null,
    ) {}
}
