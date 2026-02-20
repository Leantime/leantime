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

@include('global::components.stageflow.styles')

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
                    @dispatchEvent('logicmodel.headerActions', ['canvasId' => $currentCanvas])
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

        @if (count($allCanvas) > 0)
            {{-- Status filter bar --}}
            <div style="display:flex; justify-content:flex-end; align-items:center; margin-bottom:8px;">
                @if (!empty($statusLabels))
                    @php
                        if ($filter['status'] != 'all' && !isset($statusLabels[$filter['status']])) { $filter['status'] = 'all'; }
                        $statusFilterLabel = $filter['status'] == 'all'
                            ? '<i class="fas fa-filter"></i> ' . $tpl->__('status.all')
                            : '<i class="fas fa-fw ' . $statusLabels[$filter['status']]['icon'] . '"></i> ' . $statusLabels[$filter['status']]['title'];
                    @endphp
                    <div class="btn-group viewDropDown">
                        <button class="btn btn-default dropdown-toggle" data-toggle="dropdown">{!! $statusFilterLabel !!}</button>
                        <ul class="dropdown-menu">
                            <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status=all" @if ($filter['status'] == 'all') class="active" @endif><i class="fas fa-globe"></i> {{ $tpl->__('status.all') }}</a></li>
                            @foreach ($statusLabels as $key => $data)
                                <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status={{ $key }}" @if ($filter['status'] == $key) class="active" @endif><i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>

            @dispatchEvent('logicmodel.beforeStageFlow', ['canvasId' => $currentCanvas, 'canvasItems' => $canvasItems])

            {{-- ── Five-Stage Flow ──────────────────────────────────── --}}
            <div class="sf-flow" id="logicModelBoard">
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
                        $isFirst = ($num === array_key_first($stages));
                    @endphp

                    <x-global::stageflow.card
                        :stageKey="$boxKey"
                        :stageNum="$num"
                        :color="$stage['color']"
                        :bgColor="$stage['bg']"
                        :icon="$stage['icon']"
                        :title="$tpl->__($stage['title'])"
                        :subtitle="$tpl->__($stage['subtitle'])"
                        :active="$isFirst"
                        :itemCount="$itemCount"
                        :focusLabel="$tpl->__('logicmodel.current_focus')"
                    >
                        <x-slot:headerExtra>
                            @dispatchEvent('logicmodel.afterStageHeader', ['stageNum' => $num, 'stage' => $stage, 'canvasId' => $currentCanvas, 'stageItems' => $stageItems])
                        </x-slot:headerExtra>

                        <x-slot:beforeBody>
                            @dispatchEvent('logicmodel.beforeStageBody', ['stageNum' => $num, 'stage' => $stage, 'canvasId' => $currentCanvas])
                        </x-slot:beforeBody>

                        @foreach ($stageItems as $row)
                            @php
                                $commentsRepo = app()->make(Comments::class);
                                $nbcomments = $commentsRepo->countComments(moduleId: $row['id']);
                                $statusColor = isset($statusLabels[$row['status']]) ? $statusLabels[$row['status']]['color'] : 'grey';
                            @endphp

                            <x-global::stageflow.item
                                :itemId="$row['id']"
                                :title="$row['description']"
                                :description="$row['conclusion'] != '' ? $tpl->convertRelativePaths($row['conclusion']) : ''"
                                :editUrl="'#/' . $canvasName . 'canvas/editCanvasItem/' . $row['id']"
                                :deleteUrl="'#/' . $canvasName . 'canvas/delCanvasItem/' . $row['id']"
                                :commentUrl="'#/' . $canvasName . 'canvas/editCanvasComment/' . $row['id']"
                                :commentCount="$nbcomments"
                                :avatarUrl="$row['authorFirstname'] != '' ? BASE_URL . '/api/users?profileImage=' . $row['author'] : ''"
                                :dotColor="$statusColor"
                                :canEdit="$login::userIsAtLeast($roles::$editor)"
                            >
                                @dispatchEvent('logicmodel.itemCardFooter', ['item' => $row, 'canvasId' => $currentCanvas])
                            </x-global::stageflow.item>
                        @endforeach

                        @if ($itemCount === 0)
                            <div class="sf-empty">
                                <i class="fa {{ $stage['icon'] }} sf-empty-icon" style="color: {{ $stage['color'] }};"></i>
                                {{ $tpl->__('text.no_items_yet') }}
                            </div>
                        @endif

                        @if ($login::userIsAtLeast($roles::$editor))
                            <a class="sf-add" href="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $boxKey }}">
                                <i class="fa fa-plus"></i> {{ $tpl->__('logicmodel.add_' . $stage['key']) }}
                            </a>
                        @endif
                    </x-global::stageflow.card>
                @endforeach
            </div>

            <div class="clearfix"></div>
        @endif

        {{-- ── No Board Yet ─────────────────────────────────────── --}}
        @if (count($allCanvas) == 0)
            <br /><br />
            <div class="center">
                <div class="svgContainer">
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                </div>
                <h3>{{ $tpl->__('headlines.logicmodel.analysis') }}</h3>
                <br />{!! $tpl->__('text.logicmodel.helper_content') !!}
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

        {{-- Plugin panel containers (filled by HTMX when plugin is active) --}}
        <div id="templateSelectorContainer"></div>
        <div id="snapshotListContainer"></div>

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

    });
</script>

@endsection
