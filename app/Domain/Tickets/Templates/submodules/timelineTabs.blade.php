@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $searchParams = $tpl->get('searchParams');
@endphp

<x-globals::navigation.tabs sticky>
    <x-globals::navigation.tab
        :label="__('links.timeline')"
        :href="BASE_URL . '/tickets/roadmap' . $searchParams"
        :active="str_contains($currentRoute, 'roadmap')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        :label="__('links.table')"
        :href="BASE_URL . '/tickets/showAllMilestones' . $searchParams"
        :active="str_contains($currentRoute, 'showAllMilestones')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        :label="__('links.calendar')"
        :href="BASE_URL . '/tickets/showProjectCalendar' . $searchParams"
        :active="str_contains($currentRoute, 'Calendar')"
        preload="mouseover"
    />
</x-globals::navigation.tabs>
