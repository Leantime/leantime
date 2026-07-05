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

    // Program boards inject their own kanban/table/list URLs + the route fragments used to
    // highlight the active tab. Per-project views fall back to the core /tickets/* routes.
    $boardTabs = $boardTabs ?? [
        'kanban' => ['url' => BASE_URL . '/tickets/showKanban', 'active' => 'Kanban'],
        'table'  => ['url' => BASE_URL . '/tickets/showAll',    'active' => 'showAll'],
        'list'   => ['url' => BASE_URL . '/tickets/showList',   'active' => 'showList'],
    ];
@endphp

<div class="maincontentinner tabs">
    <ul>
        <li class="{{ findActive($boardTabs['kanban']['active']) }}">
            <a href="{{ $boardTabs['kanban']['url'] }}{{ $searchParams }}" preload="mouseover">
                {!! __('links.kanban') !!}
            </a>
        </li>
        <li class="{{ findActive($boardTabs['table']['active']) }}">
            <a href="{{ $boardTabs['table']['url'] }}{{ $searchParams }}" preload="mouseover">
                {!! __('links.table') !!}
            </a>
        </li>
        <li class="{{ findActive($boardTabs['list']['active']) }}">
            <a href="{{ $boardTabs['list']['url'] }}{{ $searchParams }}" preload="mouseover">
                {!! __('links.list') !!}
            </a>
        </li>
    </ul>
</div>
