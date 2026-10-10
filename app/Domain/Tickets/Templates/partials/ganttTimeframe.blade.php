{{-- Timeframe (Day / Week / Month) of the gantt timelines, in the view bar's actions. ticketsController
     binds #ganttTimeControl and saves the choice to usersettings.views.roadmap. --}}
@php
    $roadmapView = $roadmapView ?? session('usersettings.views.roadmap', 'Month');
    $currentView = match ($roadmapView) {
        'Day' => __('buttons.day'),
        'Week' => __('buttons.week'),
        default => __('buttons.month'),
    };
@endphp
<x-global::actions.dropdown variant="filter" menu-id="ganttTimeControl" class="dropRight">
    <x-slot:trigger class="btn-link">
        {!! __('buttons.timeframe') !!}: <span class="viewText">{{ $currentView }}</span><span class="caret"></span>
    </x-slot:trigger>
    <li><a href="javascript:void(0);" data-value="Day" class="{{ $roadmapView == 'Day' ? 'active' : '' }}">{!! __('buttons.day') !!}</a></li>
    <li><a href="javascript:void(0);" data-value="Week" class="{{ $roadmapView == 'Week' ? 'active' : '' }}">{!! __('buttons.week') !!}</a></li>
    <li><a href="javascript:void(0);" data-value="Month" class="{{ $roadmapView == 'Month' ? 'active' : '' }}">{!! __('buttons.month') !!}</a></li>
</x-global::actions.dropdown>
