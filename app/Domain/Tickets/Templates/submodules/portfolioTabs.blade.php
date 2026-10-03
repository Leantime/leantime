@php
    $currentRoute = \Leantime\Core\Controller\Frontcontroller::getCurrentRoute();
    $portfolioTabs = [
        'showMy' => BASE_URL.'/projects/showMy',
        'roadmapAll' => BASE_URL.'/tickets/roadmapAll',
        'showAllMilestonesOverview' => BASE_URL.'/tickets/showAllMilestonesOverview',
    ];
    $portfolioTabLabels = [
        'showMy' => __('menu.card'),
        'roadmapAll' => __('links.timeline'),
        'showAllMilestonesOverview' => __('links.table'),
    ];
@endphp

<div class="lt-tabs lt-tabs--floating lt-tabs--links hideOnPrint">
    <nav class="lt-tabs-group" aria-label="{{ trim(strip_tags(__('headlines.my_projects'))) }}">
    <ul>
        @foreach ($portfolioTabs as $tabRoute => $tabUrl)
            @php $isActiveTab = str_contains($currentRoute, $tabRoute); @endphp
            <li class="{{ $isActiveTab ? 'active' : '' }}">
                <a href="{{ $tabUrl }}"
                   @if ($isActiveTab) aria-current="page" @endif
                   preload="mouseover">
                    {!! $portfolioTabLabels[$tabRoute] !!}
                </a>
            </li>
        @endforeach
    </ul>
    </nav>
</div>
