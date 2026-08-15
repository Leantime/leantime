@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();

    // Program boards inject their own kanban/table/list URLs + the route fragments used to
    // highlight the active tab. Per-project views fall back to the core /tickets/* routes.
    $boardTabs = $boardTabs ?? [
        'kanban' => ['url' => BASE_URL . '/tickets/showKanban', 'active' => 'Kanban'],
        'table'  => ['url' => BASE_URL . '/tickets/showAll',    'active' => 'showAll'],
        'list'   => ['url' => BASE_URL . '/tickets/showList',   'active' => 'showList'],
    ];

    $isKanban = str_contains($currentRoute, $boardTabs['kanban']['active']);
    $isTable  = str_contains($currentRoute, $boardTabs['table']['active']);
    $isList   = str_contains($currentRoute, $boardTabs['list']['active']);

    $plainKanban = strip_tags(__('links.kanban'));
    $plainTable  = strip_tags(__('links.table'));
    $plainList   = strip_tags(__('links.list'));
    $navLabel    = trim("{$plainKanban} / {$plainTable} / {$plainList}");
@endphp

@pushOnce('styles')
    <link rel="stylesheet" href="{{ BASE_URL }}/assets/css/components/ticket-board-tabs.css" />
@endPushOnce

<div class="maincontentinner tabs board-tabs-bar hideOnPrint">
    <nav aria-label="{{ $navLabel }}">
        <ul>
            <li class="{{ $isKanban ? 'active' : '' }}">
                <a href="{{ $boardTabs['kanban']['url'] }}{{ $searchParams }}"
                   @if ($isKanban) aria-current="page" @endif
                   preload="mouseover">
                    {!! __('links.kanban') !!}
                </a>
            </li>
            <li class="{{ $isTable ? 'active' : '' }}">
                <a href="{{ $boardTabs['table']['url'] }}{{ $searchParams }}"
                   @if ($isTable) aria-current="page" @endif
                   preload="mouseover">
                    {!! __('links.table') !!}
                </a>
            </li>
            <li class="{{ $isList ? 'active' : '' }}">
                <a href="{{ $boardTabs['list']['url'] }}{{ $searchParams }}"
                   @if ($isList) aria-current="page" @endif
                   preload="mouseover">
                    {!! __('links.list') !!}
                </a>
            </li>
        </ul>
    </nav>
</div>
