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

<div class="maincontentinner tabs">
    <ul>
        <li class="{{ findActive('Kanban') }}">
            <a href="{{ BASE_URL }}/tickets/showKanban{{ $searchParams }}" preload="mouseover">
                {!! __('links.kanban') !!}
            </a>
        </li>
        <li class="{{ findActive('showAll') }}">
            <a href="{{ BASE_URL }}/tickets/showAll{{ $searchParams }}" preload="mouseover">
                {!! __('links.table') !!}
            </a>
        </li>
        <li class="{{ findActive('showList') }}">
            <a href="{{ BASE_URL }}/tickets/showList{{ $searchParams }}" preload="mouseover">
                {!! __('links.list') !!}
            </a>
        </li>
    </ul>
</div>
