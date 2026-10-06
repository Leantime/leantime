<?php

namespace Leantime\Domain\Files\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a file was uploaded and stored against a module entity.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class FileUploaded implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $fileId  The id of the stored file row.
     * @param  string  $module  The module the file belongs to (ticket, project, ...).
     * @param  int|null  $moduleId  The id of the entity the file belongs to.
     * @param  string  $extension  The file extension.
     * @param  int  $size  The file size in bytes.
     */
    public function __construct(
        public readonly int $fileId,
        public readonly string $module,
        public readonly ?int $moduleId,
        public readonly string $extension,
        public readonly int $size,
    ) {}
}
