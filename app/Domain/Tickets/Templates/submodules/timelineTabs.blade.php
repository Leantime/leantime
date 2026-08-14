@php
    use Leantime\Core\Controller\Frontcontroller;

    if (!function_exists('findActive')) {
        function findActive($route): string
        {
            if (str_contains(Frontcontroller::getCurrentRoute(), $route)) {
                return 'active';
            }
            return '';
        }
    }
@endphp

<div class="lt-tabs lt-tabs--floating lt-tabs--links hideOnPrint">
    <nav class="lt-tabs-group" aria-label="{{ __('links.timeline') }}">
    <ul>
        <li class="{{ findActive('roadmap') }}">
            <a href="{{ BASE_URL }}/tickets/roadmap{{ $searchParams }}" preload="mouseover">
                {!! __('links.timeline') !!}
            </a>
        </li>
        <li class="{{ findActive('showAllMilestones') }}">
            <a href="{{ BASE_URL }}/tickets/showAllMilestones{{ $searchParams }}" preload="mouseover">
                {!! __('links.table') !!}
            </a>
        </li>
        <li class="{{ findActive('Calendar') }}">
            <a href="{{ BASE_URL }}/tickets/showProjectCalendar{{ $searchParams }}" preload="mouseover">
                {!! __('links.calendar') !!}
            </a>
        </li>
    </ul>
    </nav>
</div>
