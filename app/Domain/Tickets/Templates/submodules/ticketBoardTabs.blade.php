@php
    use Leantime\Core\Controller\Frontcontroller;

    if (!function_exists('findActive')) {
        function findActive($route): bool
        {
            return str_contains(Frontcontroller::getCurrentRoute(), $route);
        }
    }

    // Program boards inject their own kanban/table/list URLs + the route fragments used to
    // highlight the active tab. Per-project views fall back to the core /tickets/* routes.
    $boardTabs = $boardTabs ?? [
        'kanban' => ['url' => BASE_URL . '/tickets/showKanban', 'active' => 'Kanban'],
        'table'  => ['url' => BASE_URL . '/tickets/showAll',    'active' => 'showAll'],
        'list'   => ['url' => BASE_URL . '/tickets/showList',   'active' => 'showList'],
    ];

    $plainKanban = strip_tags(__('links.kanban'));
    $plainTable  = strip_tags(__('links.table'));
    $plainList   = strip_tags(__('links.list'));
    $navLabel    = trim("{$plainKanban} / {$plainTable} / {$plainList}");
@endphp

<style>
.maincontentinner.tabs.board-tabs-bar {
    width: 100%;
    display: flex;
    align-items: center;
    padding: 7px 12px;
    margin-bottom: 10px;
    border-radius: var(--box-radius, 14px);
    background: linear-gradient(90deg, rgba(255, 255, 255, 0.92) 0%, rgba(255, 255, 255, 0.55) 45%, rgba(255, 255, 255, 0.22) 100%) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.4) !important;
    box-shadow: var(--large-shadow, 0 4px 12px rgba(0, 0, 0, 0.08));
}
.maincontentinner.tabs.board-tabs-bar ul {
    margin: 0;
    padding: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    list-style: none;
}
.maincontentinner.tabs.board-tabs-bar ul li {
    display: inline-flex;
    margin: 0;
    padding: 0;
    list-style: none;
}
.maincontentinner.tabs.board-tabs-bar ul li a {
    font-family: inherit;
    font-size: 13px;
    font-weight: 500;
    padding: 6px 14px;
    border-radius: 8px;
    line-height: 1.3;
    display: inline-flex;
    align-items: center;
    gap: 7px;
    color: var(--primary-font-color);
    text-decoration: none;
    transition: all 0.15s ease;
}
.maincontentinner.tabs.board-tabs-bar ul li a:hover {
    color: var(--primary-font-color);
    background: rgba(0, 0, 0, 0.04);
}
.maincontentinner.tabs.board-tabs-bar ul li.active a {
    color: var(--accent1, #007bb3);
    background: #fff;
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.12);
    font-weight: 600;
}
.maincontentinner.tabs.board-tabs-bar ul li.active a:hover {
    color: var(--accent1, #007bb3);
    background: #fff;
}
</style>

<div class="maincontentinner tabs board-tabs-bar hideOnPrint">
    <nav aria-label="{{ $navLabel }}">
        <ul>
            <li class="{{ findActive($boardTabs['kanban']['active']) ? 'active' : '' }}">
                <a href="{{ $boardTabs['kanban']['url'] }}{{ $searchParams }}" preload="mouseover">
                    {!! __('links.kanban') !!}
                </a>
            </li>
            <li class="{{ findActive($boardTabs['table']['active']) ? 'active' : '' }}">
                <a href="{{ $boardTabs['table']['url'] }}{{ $searchParams }}" preload="mouseover">
                    {!! __('links.table') !!}
                </a>
            </li>
            <li class="{{ findActive($boardTabs['list']['active']) ? 'active' : '' }}">
                <a href="{{ $boardTabs['list']['url'] }}{{ $searchParams }}" preload="mouseover">
                    {!! __('links.list') !!}
                </a>
            </li>
        </ul>
    </nav>
</div>
