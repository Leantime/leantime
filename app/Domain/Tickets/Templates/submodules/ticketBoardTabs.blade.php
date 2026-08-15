@php
    use Leantime\Core\Controller\Frontcontroller;

    if (!function_exists('findActive')) {
        function findActive($route): bool
        {
            return str_contains(Frontcontroller::getCurrentRoute(), $route);
        }
    }

    // Program boards inject their own kanban/table/list URLs + the route fragments used to
    // highlight the active tab. Per-project views fall back to the core /tickets/* routes.
    $boardTabs = $boardTabs ?? [
        'kanban' => ['url' => BASE_URL . '/tickets/showKanban', 'active' => 'Kanban'],
        'table'  => ['url' => BASE_URL . '/tickets/showAll',    'active' => 'showAll'],
        'list'   => ['url' => BASE_URL . '/tickets/showList',   'active' => 'showList'],
    ];

    $plainKanban = strip_tags(__('links.kanban'));
    $plainTable  = strip_tags(__('links.table'));
    $plainList   = strip_tags(__('links.list'));
    $navLabel    = trim("{$plainKanban} / {$plainTable} / {$plainList}");
@endphp

<link rel="stylesheet" href="{{ BASE_URL }}/assets/css/components/ticket-board-tabs.css" />

<div class="maincontentinner tabs board-tabs-bar hideOnPrint">
    <nav aria-label="{{ $navLabel }}">
        <ul>
            <li class="{{ findActive($boardTabs['kanban']['active']) ? 'active' : '' }}">
                <a href="{{ $boardTabs['kanban']['url'] }}{{ $searchParams }}" preload="mouseover">
                    {!! __('links.kanban') !!}
                </a>
            </li>
            <li class="{{ findActive($boardTabs['table']['active']) ? 'active' : '' }}">
                <a href="{{ $boardTabs['table']['url'] }}{{ $searchParams }}" preload="mouseover">
                    {!! __('links.table') !!}
                </a>
            </li>
            <li class="{{ findActive($boardTabs['list']['active']) ? 'active' : '' }}">
                <a href="{{ $boardTabs['list']['url'] }}{{ $searchParams }}" preload="mouseover">
                    {!! __('links.list') !!}
                </a>
            </li>
        </ul>
    </nav>
</div>
