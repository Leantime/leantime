<?php

namespace Leantime\Domain\Users\Events;

use Leantime\Core\Events\Concerns\InteractsWithEvents;
use Leantime\Core\Events\Contracts\LeantimeEvent;

/**
 * Fired after a user changed their appearance settings (theme, color mode, color scheme or font). Carries the values saved.
 *
 * Introduced with the class-based event system, so it has no legacy string name.
 */
final class AppearanceUpdated implements LeantimeEvent
{
    use InteractsWithEvents;

    /**
     * @param  int  $userId  The user.
     * @param  string|null  $theme  The saved theme.
     * @param  string|null  $colorMode  The saved color mode (light/dark).
     * @param  string|null  $colorScheme  The saved color scheme.
     * @param  string|null  $font  The saved font.
     */
    public function __construct(
        public readonly int $userId,
        public readonly ?string $theme,
        public readonly ?string $colorMode,
        public readonly ?string $colorScheme,
        public readonly ?string $font,
    ) {}
}
