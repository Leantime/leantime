@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
@endphp

<x-globals::navigation.tabs sticky>
    <x-globals::navigation.tab
        :label="__('links.timeline')"
        :href="BASE_URL . '/tickets/roadmapAll'"
        :active="str_contains($currentRoute, 'roadmapAll')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        :label="__('links.table')"
        :href="BASE_URL . '/tickets/showAllMilestonesOverview'"
        :active="str_contains($currentRoute, 'showAllMilestonesOverview')"
        preload="mouseover"
    />
</x-globals::navigation.tabs>
