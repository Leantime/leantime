<?php

namespace Leantime\Views\Composers;

use Leantime\Core\Composer;
use Leantime\Core\Environment;
use Leantime\Core\Theme;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Setting\Repositories\Setting;

/**
 *
 */
class Entry extends Composer
{
    public static array $views = [
        'global::layouts.entry',
    ];

    public function init(
        Theme $themeCore,
        Environment $config
    ): void {
        $this->themeCore = $themeCore;
        $this->config = $config;
    }

    /**
     * @return array|string[]
     */
    public function with(): array
    {

        $this->themeCore->getActive();

        return [
            'logoPath' =>  $this->themeCore->getLogoUrl(),
        ];
    }
}
