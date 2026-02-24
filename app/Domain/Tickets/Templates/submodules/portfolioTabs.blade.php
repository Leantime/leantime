@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
@endphp

<div class="maincontentinner tabs">
    <ul>
        <li class="{{ str_contains($currentRoute, 'roadmapAll') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/roadmapAll" preload="mouseover">
                {{ __('links.timeline') }}
            </a>
        </li>
        <li class="{{ str_contains($currentRoute, 'showAllMilestonesOverview') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/showAllMilestonesOverview" preload="mouseover">
                {{ __('links.table') }}
            </a>
        </li>
    </ul>
</div>
