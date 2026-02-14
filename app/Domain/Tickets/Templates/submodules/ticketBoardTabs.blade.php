@php
    use Leantime\Core\Controller\Frontcontroller;

    $currentRoute = Frontcontroller::getCurrentRoute();
    $searchParams = $tpl->get('searchParams');
@endphp

<div class="maincontentinner tabs">
    <ul>
        <li class="{{ str_contains($currentRoute, 'Kanban') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/showKanban{{ $searchParams }}" preload="mouseover">
                {!! __('links.kanban') !!}
            </a>
        </li>
        <li class="{{ str_contains($currentRoute, 'showAll') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/showAll{{ $searchParams }}" preload="mouseover">
                {!! __('links.table') !!}
            </a>
        </li>
        <li class="{{ str_contains($currentRoute, 'showList') ? 'active' : '' }}">
            <a href="{{ BASE_URL }}/tickets/showList{{ $searchParams }}" preload="mouseover">
                {!! __('links.list') !!}
            </a>
        </li>
    </ul>
</div>
