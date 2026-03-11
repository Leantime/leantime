@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
@endphp

<x-globals::navigation.tabs sticky>
    <x-globals::navigation.tab
        icon="timeline"
        label="Timeline"
        :href="BASE_URL . '/tickets/roadmapAll'"
        :active="str_contains($currentRoute, 'roadmapAll')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        icon="table_rows"
        label="Table"
        :href="BASE_URL . '/tickets/showAllMilestonesOverview'"
        :active="str_contains($currentRoute, 'showAllMilestonesOverview')"
        preload="mouseover"
    />
</x-globals::navigation.tabs>
