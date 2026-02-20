@extends($layout)
@section('content')

@php
    use Leantime\Domain\Logicmodelcanvas\Repositories\Logicmodelcanvas;
    use Leantime\Domain\Comments\Repositories\Comments;

    $canvasName = 'logicmodel';
    $allCanvas = $tpl->get('allCanvas');
    $canvasIcon = $tpl->get('canvasIcon');
    $canvasTypes = $tpl->get('canvasTypes');
    $statusLabels = $tpl->get('statusLabels');
    $relatesLabels = $tpl->get('relatesLabels');
    $dataLabels = $tpl->get('dataLabels');
    $disclaimer = $tpl->get('disclaimer');
    $canvasItems = $tpl->get('canvasItems');
    $currentCanvas = $tpl->get('currentCanvas');
    $users = $tpl->get('users');

    $filter['status'] = $_GET['filter_status'] ?? (session('filter_status') ?? 'all');
    $filter['relates'] = $_GET['filter_relates'] ?? (session('filter_relates') ?? 'all');

    $canvasTitle = '';
    foreach ($allCanvas as $canvasRow) {
        if ($canvasRow['id'] == $currentCanvas) {
            $canvasTitle = $canvasRow['title'];
            break;
        }
    }

    $stages = Logicmodelcanvas::STAGES;
@endphp

<style>
    /* ── Logic Model Board Layout ────────────────────────────────── */
    .lm-board {
        display: flex;
        align-items: flex-start;
        gap: 0;
        padding: 15px 0;
        overflow-x: auto;
    }

    /* Stage column */
    .lm-stage {
        flex: 1 1 0;
        min-width: 180px;
        border-radius: var(--box-radius);
        padding: 0;
        transition: transform 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94),
                    opacity 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94),
                    box-shadow 350ms cubic-bezier(0.25, 0.46, 0.45, 0.94);
        position: relative;
    }

    /* Active stage */
    .lm-stage--active {
        transform: scale(1);
        opacity: 1;
        box-shadow: var(--large-shadow);
        flex: 2.2 1 0;
        z-index: 2;
    }
    .lm-stage--active .lm-stage__header {
        border-bottom-width: 3px;
    }
    .lm-stage--active .lm-stage__body {
        padding: 12px;
    }
    .lm-stage--active .lm-card--full { display: block; }
    .lm-stage--active .lm-card--compact { display: none; }
    .lm-stage--active .lm-stage__add { display: block; }
    .lm-stage--active .lm-stage__focus-pill { display: inline-block; }
    .lm-stage--active .lm-stage__count { display: none; }

    /* Inactive stage */
    .lm-stage--inactive {
        transform: scale(0.955);
        opacity: 0.5;
        box-shadow: var(--min-shadow);
        cursor: pointer;
        z-index: 1;
    }
    .lm-stage--inactive:hover {
        opacity: 0.78;
        transform: scale(0.97);
        box-shadow: var(--regular-shadow);
    }
    .lm-stage--inactive .lm-stage__header {
        border-bottom-width: 2px;
        border-bottom-color: var(--layered-background) !important;
    }
    .lm-stage--inactive .lm-stage__body {
        padding: 8px 12px;
    }
    .lm-stage--inactive .lm-card--full { display: none; }
    .lm-stage--inactive .lm-card--compact { display: flex; }
    .lm-stage--inactive .lm-stage__add { display: none; }
    .lm-stage--inactive .lm-stage__focus-pill { display: none; }
    .lm-stage--inactive .lm-stage__count { display: inline-flex; }

    /* Stage header */
    .lm-stage__header {
        padding: 12px 14px 10px;
        border-bottom-style: solid;
        border-top-left-radius: var(--box-radius);
        border-top-right-radius: var(--box-radius);
    }
    .lm-stage__header h4 {
        margin: 0 0 2px;
        font-size: var(--font-size-m);
        font-weight: 600;
    }
    .lm-stage__header small {
        font-size: var(--font-size-xs);
        opacity: 0.7;
    }

    /* Focus pill */
    .lm-stage__focus-pill {
        display: none;
        font-size: 10px;
        padding: 2px 8px;
        border-radius: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-left: 8px;
        vertical-align: middle;
    }

    /* Count badge */
    .lm-stage__count {
        display: none;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: 11px;
        font-weight: 700;
        color: #fff;
        float: right;
        margin-top: 2px;
    }

    /* Body / items area */
    .lm-stage__body {
        min-height: 80px;
        border-bottom-left-radius: var(--box-radius);
        border-bottom-right-radius: var(--box-radius);
        background: #fff;
    }

    /* Full card (active stage) */
    .lm-card--full {
        display: none;
        margin-bottom: 10px;
    }
    .lm-card--full .ticketBox {
        margin-bottom: 8px;
    }

    /* Compact row (inactive stage) */
    .lm-card--compact {
        display: none;
        align-items: center;
        gap: 8px;
        padding: 4px 0;
        border-bottom: 1px solid var(--layered-background);
        font-size: var(--font-size-s);
    }
    .lm-card--compact:last-child {
        border-bottom: none;
    }
    .lm-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        flex-shrink: 0;
    }
    .lm-card--compact .lm-compact-title {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        color: var(--primary-font-color);
    }

    /* Add link */
    .lm-stage__add {
        display: none;
        padding-top: 8px;
    }

    /* Connector arrows between stages */
    .lm-connector {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        flex-shrink: 0;
        color: #ccc;
        font-size: 14px;
        align-self: stretch;
        padding-top: 50px;
    }

    /* Status color map */
    .lm-dot-blue { background: #5b93d3; }
    .lm-dot-orange { background: #e8a838; }
    .lm-dot-green { background: #66c066; }
    .lm-dot-red { background: #e25555; }

    /* Print styles */
    @media print {
        .lm-stage { opacity: 1 !important; transform: none !important; }
        .lm-card--full { display: block !important; }
        .lm-card--compact { display: none !important; }
        .lm-stage__add { display: none !important; }
        .lm-connector { color: #999; }
    }
</style>

{{-- ── Page Header ───────────────────────────────────────────── --}}
<div class="pageheader">
    <div class="pageicon"><span class="fas {{ $canvasIcon }}"></span></div>
    <div class="pagetitle">
        <h5>{!! e(session('currentProjectClient') . ' // ' . session('currentProjectName')) !!}</h5>
        @if (count($allCanvas) > 0)
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/{{ $canvasName }}canvas/boardDialog/{{ $currentCanvas }}" class="editCanvasLink">{!! $tpl->__('links.icon.edit') !!}</a></li>
                    @endif
                    <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/export/{{ $currentCanvas }}" hx-boost="false">{!! $tpl->__('links.icon.export') !!}</a></li>
                    <li><a href="javascript:window.print();">{!! $tpl->__('links.icon.print') !!}</a></li>
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/{{ $canvasName }}canvas/delCanvas/{{ $currentCanvas }}" class="delete">{!! $tpl->__('links.icon.delete') !!}</a></li>
                    @endif
                </ul>
            </span>
        @endif
        <h1>{{ $tpl->__('headline.logicmodel.board') }} //
            @if (count($allCanvas) > 0)
                <span class="dropdown dropdownWrapper">
                    <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                        {{ $tpl->escape($canvasTitle) }}&nbsp;<i class="fa fa-caret-down"></i>
                    </a>
                    <ul class="dropdown-menu canvasSelector">
                        @if ($login::userIsAtLeast($roles::$editor))
                            <li><a href="#/{{ $canvasName }}canvas/boardDialog">{!! $tpl->__('links.icon.create_new_board') !!}</a></li>
                        @endif
                        <li class="border"></li>
                        @foreach ($allCanvas as $canvasRow)
                            <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/{{ $canvasRow['id'] }}">{{ $tpl->escape($canvasRow['title']) }}</a></li>
                        @endforeach
                    </ul>
                </span>
            @endif
        </h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        {{-- Status filter bar --}}
        <div class="tw-flex tw-justify-end tw-items-center tw-mb-4">
            @if (count($allCanvas) > 0 && !empty($statusLabels))
                @php
                    if ($filter['status'] != 'all' && !isset($statusLabels[$filter['status']])) { $filter['status'] = 'all'; }
                    $statusFilterLabel = $filter['status'] == 'all'
                        ? '<i class="fas fa-filter"></i> ' . $tpl->__('status.all')
                        : '<i class="fas fa-fw ' . $statusLabels[$filter['status']]['icon'] . '"></i> ' . $statusLabels[$filter['status']]['title'];
                @endphp
                <div class="btn-group viewDropDown">
                    <button class="btn dropdown-toggle" data-toggle="dropdown">{!! $statusFilterLabel !!}</button>
                    <ul class="dropdown-menu">
                        <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status=all" @if ($filter['status'] == 'all') class="active" @endif><i class="fas fa-globe"></i> {{ $tpl->__('status.all') }}</a></li>
                        @foreach ($statusLabels as $key => $data)
                            <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status={{ $key }}" @if ($filter['status'] == $key) class="active" @endif><i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="clearfix"></div>

        @if (count($allCanvas) > 0)
            {{-- ── Five-Stage Pipeline ────────────────────────────── --}}
            <div class="lm-board" id="logicModelBoard">
                @foreach ($stages as $num => $stage)
                    @php
                        $boxKey = 'lm_' . $stage['key'];
                        $stageItems = array_filter($canvasItems, function ($item) use ($boxKey, $filter) {
                            if ($item['box'] !== $boxKey) return false;
                            if ($filter['status'] !== 'all' && $item['status'] !== $filter['status']) return false;
                            if ($filter['relates'] !== 'all' && $item['relates'] !== $filter['relates']) return false;
                            return true;
                        });
                        $itemCount = count($stageItems);
                    @endphp

                    {{-- Connector arrow (between stages, not before first) --}}
                    @if ($num > 1)
                        <div class="lm-connector">
                            <i class="fa fa-chevron-right"></i>
                        </div>
                    @endif

                    <div class="lm-stage" data-stage="{{ $boxKey }}" onclick="leantime.logicmodelCanvasController.focusStage('{{ $boxKey }}')">
                        {{-- Stage header --}}
                        <div class="lm-stage__header" style="background: {{ $stage['bg'] }}; border-bottom-color: {{ $stage['color'] }};">
                            <h4>
                                <i class="fas {{ $stage['icon'] }}" style="color: {{ $stage['color'] }};"></i>
                                {{ $tpl->__($stage['title']) }}
                                <span class="lm-stage__focus-pill" style="background: {{ $stage['color'] }}; color: #fff;">Current Focus</span>
                                <span class="lm-stage__count" style="background: {{ $stage['color'] }};">{{ $itemCount }}</span>
                            </h4>
                            <small>{{ $tpl->__($stage['subtitle']) }}</small>
                        </div>

                        {{-- Stage body --}}
                        <div class="lm-stage__body">
                            @foreach ($stageItems as $row)
                                @php
                                    $commentsRepo = app()->make(Comments::class);
                                    $nbcomments = $commentsRepo->countComments(moduleId: $row['id']);
                                    $statusColor = isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['color'] : 'grey';
                                @endphp

                                {{-- Full card (shown when active) --}}
                                <div class="lm-card--full">
                                    <div class="ticketBox" id="item_{{ $row['id'] }}">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="inlineDropDownContainer" style="float:right;">
                                                    @if ($login::userIsAtLeast($roles::$editor))
                                                        <a href="javascript:void(0)" class="dropdown-toggle ticketDropDown" data-toggle="dropdown">
                                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <li class="nav-header">{{ $tpl->__('subtitles.edit') }}</li>
                                                            <li><a href="#/{{ $canvasName }}canvas/editCanvasItem/{{ $row['id'] }}" data="item_{{ $row['id'] }}">{{ $tpl->__('links.edit_canvas_item') }}</a></li>
                                                            <li><a href="#/{{ $canvasName }}canvas/delCanvasItem/{{ $row['id'] }}" class="delete" data="item_{{ $row['id'] }}">{{ $tpl->__('links.delete_canvas_item') }}</a></li>
                                                        </ul>
                                                    @endif
                                                </div>

                                                <h4><a href="#/{{ $canvasName }}canvas/editCanvasItem/{{ $row['id'] }}" data="item_{{ $row['id'] }}">{{ $row['description'] }}</a></h4>

                                                @if ($row['conclusion'] != '')
                                                    <small>{!! $tpl->convertRelativePaths($row['conclusion']) !!}</small>
                                                @endif

                                                <div class="clearfix" style="padding-bottom: 8px;"></div>

                                                @if (!empty($statusLabels) && isset($statusLabels[$row['status']]))
                                                    <div class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
                                                        <a class="dropdown-toggle f-left status label-{{ $statusLabels[$row['status']]['dropdown'] }}"
                                                           href="javascript:void(0);" role="button"
                                                           id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                            <span class="text">{{ $statusLabels[$row['status']]['title'] }}</span> <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                        </a>
                                                        <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                                            <li class="nav-header border">{{ $tpl->__('dropdown.choose_status') }}</li>
                                                            @foreach ($statusLabels as $key => $data)
                                                                @if ($data['active'])
                                                                    <li class="dropdown-item">
                                                                        <a href="javascript:void(0);" class="label-{{ $data['dropdown'] }}" data-label="{{ $data['title'] }}" data-value="{{ $row['id'] . '/' . $key }}" id="ticketStatusChange{{ $row['id'] . $key }}">{{ $data['title'] }}</a>
                                                                    </li>
                                                                @endif
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @endif

                                                <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                    <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button"
                                                       id="userDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <span class="text">
                                                            @if ($row['authorFirstname'] != '')
                                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}" width="25" style="vertical-align: middle;" /></span>
                                                            @else
                                                                <span id="userImage{{ $row['id'] }}"><img src="{{ BASE_URL }}/api/users?profileImage=false" width="25" style="vertical-align: middle;" /></span>
                                                            @endif
                                                            <span id="user{{ $row['id'] }}"></span>
                                                        </span>
                                                    </a>
                                                    <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                                        <li class="nav-header border">{{ $tpl->__('dropdown.choose_user') }}</li>
                                                        @foreach ($users as $user)
                                                            <li class="dropdown-item">
                                                                <a href="javascript:void(0);"
                                                                   data-label="{{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}"
                                                                   data-value="{{ $row['id'] . '_' . $user['id'] . '_' . $user['profileId'] }}"
                                                                   id="userStatusChange{{ $row['id'] . $user['id'] }}">
                                                                    <img src="{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}" width="25" style="vertical-align: middle; margin-right:5px;" />
                                                                    {{ sprintf($tpl->__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                <div class="pull-right" style="margin-right:10px;">
                                                    <a href="#/{{ $canvasName }}canvas/editCanvasComment/{{ $row['id'] }}" class="commentCountLink" data="item_{{ $row['id'] }}">
                                                        <span class="fas fa-comments"></span>
                                                    </a>
                                                    <small>{{ $nbcomments }}</small>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Compact row (shown when inactive) --}}
                                <div class="lm-card--compact">
                                    <span class="lm-status-dot lm-dot-{{ $statusColor }}"></span>
                                    <span class="lm-compact-title">{{ $row['description'] }}</span>
                                </div>
                            @endforeach

                            {{-- Empty state --}}
                            @if ($itemCount === 0)
                                <div class="lm-card--full">
                                    <p style="color: #aaa; font-size: var(--font-size-s); text-align: center; padding: 15px 0;">
                                        {{ $tpl->__('label.no_items') ?? 'No items yet' }}
                                    </p>
                                </div>
                            @endif

                            {{-- Add item link (active stage only) --}}
                            @if ($login::userIsAtLeast($roles::$editor))
                                <div class="lm-stage__add">
                                    <a href="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $boxKey }}" id="{{ $boxKey }}" onclick="event.stopPropagation();">
                                        <i class="fas fa-plus"></i> {{ $tpl->__('links.add_new_canvas_item') }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="clearfix"></div>
        @endif

        {{-- ── Empty / Bottom Section ─────────────────────────────── --}}
        @if (count($allCanvas) > 0 && count($canvasItems) == 0)
            <br /><br />
            <div class="center">
                <div class="svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                </div>
                <h3>{{ $tpl->__('headlines.logicmodel.analysis') }}</h3>
                <br />{!! $tpl->__('text.logicmodel.helper_content') !!}
            </div>
        @endif

        @if (count($allCanvas) == 0)
            <br /><br />
            <div class="center">
                <div class="svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                </div>
                <h3>{{ $tpl->__('headlines.logicmodel.analysis') }}</h3>
                <br />{{ $tpl->__('text.logicmodel.helper_content') }}
                @if ($login::userIsAtLeast($roles::$editor))
                    <br /><br />
                    <a href="javascript:void(0)" class="addCanvasLink btn btn-primary">
                        {{ $tpl->__('links.icon.create_new_board') }}
                    </a>
                @endif
            </div>
        @endif

        @if (!empty($disclaimer) && count($allCanvas) > 0)
            <small class="center">{{ $disclaimer }}</small>
        @endif

        {!! $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'modals'), $__data)->render() !!}

    </div>
</div>

<script type="text/javascript">
    jQuery(document).ready(function () {

        if (jQuery('#searchCanvas').length > 0) {
            new SlimSelect({ select: '#searchCanvas' });
        }

        @if (isset($_GET['closeModal']))
            jQuery.nmTop().close();
        @endif

        leantime.logicmodelCanvasController.setRowHeights();
        leantime.canvasController.setCanvasName('logicmodel');
        leantime.canvasController.initFilterBar();

        @if ($login::userIsAtLeast($roles::$editor))
            leantime.canvasController.initCanvasLinks();
            leantime.canvasController.initUserDropdown();
            leantime.canvasController.initStatusDropdown();
            leantime.canvasController.initRelatesDropdown();
        @else
            leantime.authController.makeInputReadonly(".maincontentinner");
        @endif

        @if (isset($_GET['showModal']))
            @php
                if ($_GET['showModal'] == '') {
                    $modalUrl = '&type=' . array_key_first($canvasTypes);
                } else {
                    $modalUrl = '/' . (int) $_GET['showModal'];
                }
            @endphp
            leantime.canvasController.openModalManually("{{ BASE_URL }}/{{ $canvasName }}canvas/editCanvasItem{{ $modalUrl }}");
            window.history.pushState({}, document.title, '{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/');
        @endif

        // Initialize the board with first stage active
        leantime.logicmodelCanvasController.initBoard();
    });
</script>

@endsection
