@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $searchParams = $tpl->get('searchParams');
@endphp

<x-globals::navigation.tabs sticky>
    <x-globals::navigation.tab
        :label="__('links.kanban')"
        :href="BASE_URL . '/tickets/showKanban' . $searchParams"
        :active="str_contains($currentRoute, 'Kanban')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        :label="__('links.table')"
        :href="BASE_URL . '/tickets/showAll' . $searchParams"
        :active="str_contains($currentRoute, 'showAll')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        :label="__('links.list')"
        :href="BASE_URL . '/tickets/showList' . $searchParams"
        :active="str_contains($currentRoute, 'showList')"
        preload="mouseover"
    />
</x-globals::navigation.tabs>
