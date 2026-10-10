{{--
    tickets::portfolio-tabs — the My Projects views (Card / Timeline / Table) with optional page actions
    on the right (the timeline's Client and Timeframe filters).
--}}
@php
    $currentRoute = \Leantime\Core\Controller\Frontcontroller::getCurrentRoute();
    $portfolioViews = [
        'showMy' => ['url' => BASE_URL.'/projects/showMy', 'label' => __('menu.card')],
        'roadmapAll' => ['url' => BASE_URL.'/tickets/roadmapAll', 'label' => __('links.timeline')],
        'showAllMilestonesOverview' => ['url' => BASE_URL.'/tickets/showAllMilestonesOverview', 'label' => __('links.table')],
    ];
    $tabs = [];
    foreach ($portfolioViews as $route => $view) {
        $tabs[] = $view + ['active' => str_contains($currentRoute, $route)];
    }
@endphp
<x-global::navigation.view-tabs :tabs="$tabs" :label="trim(strip_tags(__('headlines.my_projects')))">
    @isset($actions)
        <x-slot:actions>{{ $actions }}</x-slot:actions>
    @endisset
</x-global::navigation.view-tabs>
