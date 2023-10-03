<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;

/**
 *
 */

/**
 *
 */
class Entry extends Composer
{
    public static array $views = [
        'global::layouts.entry',
    ];

    /**
     * @return array|string[]
     */
    /**
     * @return array|string[]
     */
    public function with(): array
    {
        return [
            'logoPath' => $_SESSION['companysettings.logoPath'] ?? '',
        ];
    }
}
