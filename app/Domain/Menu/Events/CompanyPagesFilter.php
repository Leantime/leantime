<?php

namespace Leantime\Domain\Menu\Events;

use Leantime\Core\Events\Concerns\InteractsWithFilters;
use Leantime\Core\Events\Contracts\LeantimeFilter;

/**
 * Filters the company pages the header's Company link can open, as [permission => path] in the
 * order the link prefers them. The link opens the first page whose permission the user holds, and
 * is hidden when they hold none. A plugin with a company page adds it here, so people whose only
 * company permission is that page's still get the link.
 */
final class CompanyPagesFilter implements LeantimeFilter
{
    use InteractsWithFilters;

    /**
     * @param  array<string, string>  $pages  Permission key => path, e.g. 'users.view' => '/users/showAll/'.
     */
    public function __construct(
        public array $pages = [],
    ) {}

    /**
     * The pages threaded through the pipeline.
     */
    public function payload(): mixed
    {
        return $this->pages;
    }
}
