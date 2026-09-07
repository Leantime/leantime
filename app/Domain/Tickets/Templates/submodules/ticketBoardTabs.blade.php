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

    $tabs = [];
    foreach (['kanban' => 'links.kanban', 'table' => 'links.table', 'list' => 'links.list'] as $key => $langKey) {
        $tabs[] = [
            'url'      => $boardTabs[$key]['url'] . $searchParams,
            'label'    => __($langKey),
            'isActive' => str_contains($currentRoute, $boardTabs[$key]['active']),
        ];
    }

    // links.* carry a Font Awesome icon (e.g. "<i class='fas fa-columns'></i> Kanban"), which
    // is what the tab body wants but not the accessible name — an unstripped label puts escaped
    // markup into the attribute. Strip tags for aria-label, keep the markup in the link. #3748
    $navLabel = implode(' / ', array_map(fn ($tab) => trim(strip_tags((string) $tab['label'])), $tabs));
@endphp

<div class="lt-tabs lt-tabs--floating lt-tabs--links hideOnPrint">
    <nav class="lt-tabs-group" aria-label="{{ $navLabel }}">
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

    {{-- Board actions (New / Filter / Group By) live on the right of the nav bar
         — like the report's period picker — so the bar is balanced and the board
         needs no separate toolbar row below it. Guarded on $searchCriteria so the
         partial stays safe if the nav is ever reused without the board context. --}}
    @isset($searchCriteria)
        <div class="lt-tabs-actions">
            @dispatchEvent('filters.afterLefthandSectionOpen')
            @include('tickets::submodules.ticketNewBtn')
            @include('tickets::submodules.ticketFilter')
            @dispatchEvent('filters.beforeLefthandSectionClose')
        </div>
    @endisset
</div>
