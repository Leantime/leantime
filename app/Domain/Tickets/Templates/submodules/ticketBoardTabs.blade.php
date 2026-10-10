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
            'url' => $boardTabs[$key]['url'] . $searchParams,
            'label' => __($langKey),
            'active' => str_contains($currentRoute, $boardTabs[$key]['active']),
        ];
    }
@endphp

{{-- Board actions (New / Filter / Group By / Cards) sit on the right of the view bar, so the board
     needs no separate toolbar row. Guarded on $searchCriteria so the partial stays safe if the nav is
     ever reused without the board context. --}}
<x-global::navigation.view-tabs :tabs="$tabs">
    @isset($searchCriteria)
        <x-slot:actions>
            @dispatchEvent('filters.afterLefthandSectionOpen')
            @include('tickets::submodules.ticketNewBtn')
            @include('tickets::submodules.ticketFilter')
            @isset($kanbanView)
                @include('tickets::partials.kanbanViewMenu')
            @endisset
            @dispatchEvent('filters.beforeLefthandSectionClose')
        </x-slot:actions>
    @endisset
</x-global::navigation.view-tabs>
