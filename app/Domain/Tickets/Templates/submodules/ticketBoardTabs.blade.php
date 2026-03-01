@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $searchParams = $tpl->get('searchParams');
@endphp

<x-globals::navigation.tabs sticky>
    <x-globals::navigation.tab
        icon="view_column"
        label="Kanban"
        :href="BASE_URL . '/tickets/showKanban' . $searchParams"
        :active="str_contains($currentRoute, 'Kanban')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        icon="table_rows"
        label="Table"
        :href="BASE_URL . '/tickets/showAll' . $searchParams"
        :active="str_contains($currentRoute, 'showAll')"
        preload="mouseover"
    />
    <x-globals::navigation.tab
        icon="list"
        label="List"
        :href="BASE_URL . '/tickets/showList' . $searchParams"
        :active="str_contains($currentRoute, 'showList')"
        preload="mouseover"
    />
</x-globals::navigation.tabs>
