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

    $tabs = [
        [
            'url'      => $boardTabs['kanban']['url'] . $searchParams,
            'label'    => __('links.kanban'),
            'isActive' => str_contains($currentRoute, $boardTabs['kanban']['active']),
        ],
        [
            'url'      => $boardTabs['table']['url'] . $searchParams,
            'label'    => __('links.table'),
            'isActive' => str_contains($currentRoute, $boardTabs['table']['active']),
        ],
        [
            'url'      => $boardTabs['list']['url'] . $searchParams,
            'label'    => __('links.list'),
            'isActive' => str_contains($currentRoute, $boardTabs['list']['active']),
        ],
    ];

    $navLabel = implode(' / ', array_map(fn($tab) => strip_tags((string) $tab['label']), $tabs));
@endphp

@pushOnce('styles')
    <link rel="stylesheet" href="{{ BASE_URL }}/assets/css/components/ticket-board-tabs.css" />
@endPushOnce

<div class="maincontentinner tabs board-tabs-bar hideOnPrint">
    <nav aria-label="{{ $navLabel }}">
        <ul>
            @foreach ($tabs as $tab)
                <li class="{{ $tab['isActive'] ? 'active' : '' }}">
                    <a href="{{ $tab['url'] }}"
                       @if ($tab['isActive']) aria-current="page" @endif
                       preload="mouseover">
                        {!! $tab['label'] !!}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</div>
