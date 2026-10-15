@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $tabs = [
        ['url' => BASE_URL.'/tickets/roadmap'.$searchParams, 'label' => __('links.timeline'), 'active' => str_contains($currentRoute, 'roadmap')],
        ['url' => BASE_URL.'/tickets/showAllMilestones'.$searchParams, 'label' => __('links.table'), 'active' => str_contains($currentRoute, 'showAllMilestones')],
        ['url' => BASE_URL.'/tickets/showProjectCalendar'.$searchParams, 'label' => __('links.calendar'), 'active' => str_contains($currentRoute, 'Calendar')],
    ];
@endphp

{{-- New / Filter sit on the right of the view bar, exactly like the To-Do board (ticketBoardTabs).
     Guarded on $searchCriteria so the nav stays safe if it is ever reused without the filter context. --}}
<x-global::navigation.view-tabs :tabs="$tabs" :label="trim(strip_tags(__('links.timeline')))">
    @isset($searchCriteria)
        <x-slot:actions>
            @dispatchEvent('filters.afterLefthandSectionOpen')
            @include('tickets::submodules.ticketNewBtn')
            @include('tickets::submodules.ticketFilter')
            @isset($roadmapView)
                @include('tickets::partials.ganttTimeframe')
            @endisset
            @dispatchEvent('filters.beforeLefthandSectionClose')
            @if ($tableButtons ?? false)
                {{-- The table is this page's content, so its Export / Columns live in the bar too
                     (DataTables appends them to #tableButtons). --}}
                @dispatchEvent('filters.afterRighthandSectionOpen')
                <div id="tableButtons" class="tableButtons"></div>
                @dispatchEvent('filters.beforeRighthandSectionClose')
            @endif
        </x-slot:actions>
    @endisset
</x-global::navigation.view-tabs>
