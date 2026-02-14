@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $searchParams = $tpl->get('searchParams');
@endphp

<div class="maincontentinner tabs">
    <ul>
        <li class="{{ str_contains($currentRoute, 'roadmap') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/roadmap{{ $searchParams }}" preload="mouseover">
                {!! __('links.timeline') !!}
            </a>
        </li>
        <li class="{{ str_contains($currentRoute, 'showAllMilestones') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/showAllMilestones{{ $searchParams }}" preload="mouseover">
                {!! __('links.table') !!}
            </a>
        </li>
        <li class="{{ str_contains($currentRoute, 'Calendar') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/showProjectCalendar{{ $searchParams }}" preload="mouseover">
                {!! __('links.calendar') !!}
            </a>
        </li>
    </ul>
</div>
