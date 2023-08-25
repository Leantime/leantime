<?php

namespace leantime\views\composers;

use leantime\core\Composer;

class Entry extends Composer
{
    public static $views = [
        'global::layouts.entry',
    ];

    public function with()
    {
        return [
            'logoPath' => $_SESSION['companysettings.logoPath'] ?? '',
        ];
    }
}
