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
    <nav class="lt-tabs-group" aria-label="{{ trim(strip_tags(__('links.timeline'))) }}">
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

    {{-- New / Filter live on the right of the nav bar, exactly like the To-Do
         board (ticketBoardTabs). They used to sit inside .maincontentinner in a
         bootstrap .row, which is why the Filter button rendered as flat text
         here but as a white pill on the boards: the pill styling comes from
         `.lt-tabs .lt-tabs-actions .btn-link`, and outside the band it never
         applied. Guarded on $searchCriteria so the nav stays safe if it is ever
         reused without the filter context. --}}
    @isset($searchCriteria)
        <div class="lt-tabs-actions">
            @dispatchEvent('filters.afterLefthandSectionOpen')
            @include('tickets::submodules.ticketNewBtn')
            @include('tickets::submodules.ticketFilter')
            @dispatchEvent('filters.beforeLefthandSectionClose')
        </div>
    @endisset
</div>
