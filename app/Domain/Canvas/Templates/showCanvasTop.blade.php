@php
    $canvasTitle = '';
    $allCanvas = $tpl->get('allCanvas');
    $canvasIcon = $tpl->get('canvasIcon');
    $canvasTypes = $tpl->get('canvasTypes');
    $statusLabels = $statusLabels ?? $tpl->get('statusLabels');
    $relatesLabels = $relatesLabels ?? $tpl->get('relatesLabels');
    $dataLabels = $tpl->get('dataLabels');
    $disclaimer = $tpl->get('disclaimer');
    $canvasItems = $tpl->get('canvasItems');

    $filter['status'] = $_GET['filter_status'] ?? (session('filter_status') ?? 'all');
    session(['filter_status' => $filter['status']]);
    $filter['relates'] = $_GET['filter_relates'] ?? (session('filter_relates') ?? 'all');
    session(['filter_relates' => $filter['relates']]);

    foreach ($tpl->get('allCanvas') as $canvasRow) {
        if ($canvasRow['id'] == $tpl->get('currentCanvas')) {
            $canvasTitle = $canvasRow['title'];
            break;
        }
    }

    $tpl->assign('canvasTitle', $canvasTitle);
@endphp

<style>
  .canvas-row { margin-left: 0px; margin-right: 0px;}
  .canvas-title-only { border-radius: var(--box-radius-small); }
  h4.canvas-element-title-empty { background: var(--color-bg-card) !important; border-color: var(--color-bg-card) !important; }
  div.canvas-element-center-middle { text-align: center; }
</style>

<div class="pageheader">
    <div class="pageicon"><x-global::elements.icon :name="$canvasIcon" /></div>
    <div class="pagetitle">
        <h5>{!! e(session('currentProjectClient') . ' // ' . session('currentProjectName')) !!}</h5>
        @if (count($allCanvas) > 0)
            <x-globals::actions.dropdown-menu container-class="headerEditDropdown">
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/{{ $canvasName }}canvas/boardDialog/{{ $tpl->get('currentCanvas') }}" class="editCanvasLink">{!! $tpl->__('links.icon.edit') !!}</a></li>
                @endif
                <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/export/{{ $tpl->get('currentCanvas') }}" hx-boost="false">{!! $tpl->__('links.icon.export') !!}</a></li>
                <li><a href="javascript:window.print();">{!! $tpl->__('links.icon.print') !!}</a></li>
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/{{ $canvasName }}canvas/delCanvas/{{ $tpl->get('currentCanvas') }}" class="delete">{!! $tpl->__('links.icon.delete') !!}</a></li>
                @endif
            </x-globals::actions.dropdown-menu>
        @endif
        <h1>{{ $tpl->__("headline.$canvasName.board") }} //
            @if (count($allCanvas) > 0)
                <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" :label="$tpl->escape($canvasTitle)" trigger-class="header-title-dropdown">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/{{ $canvasName }}canvas/boardDialog">{!! $tpl->__('links.icon.create_new_board') !!}</a></li>
                    @endif
                    <li class="border"></li>
                    @foreach ($tpl->get('allCanvas') as $canvasRow)
                        <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/{{ $canvasRow['id'] }}">{{ $tpl->escape($canvasRow['title']) }}</a></li>
                    @endforeach
                </x-globals::actions.dropdown-menu>
            @endif
        </h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:flex tw:justify-between tw:items-center">
            <div>
                @if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                    <x-globals::forms.button link="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $elementName }}" type="primary" id="{{ $elementName }}">{!! $tpl->__('links.add_new_canvas_item' . $canvasName) !!}</x-globals::forms.button>
                @endif
            </div>

            <div></div>

            <div class="pull-right">
                @if (count($allCanvas) > 0 && ! empty($statusLabels))
                    @php
                        if ($filter['status'] != 'all' && !isset($statusLabels[$filter['status']])) { $filter['status'] = 'all'; }
                        $statusFilterLabel = $filter['status'] == 'all'
                            ? '<x-global::elements.icon name="filter_list" /> ' . $tpl->__('status.all')
                            : '<span class="material-symbols-outlined" style="font-size:inherit;vertical-align:middle;">' . $statusLabels[$filter['status']]['icon'] . '</span> ' . $statusLabels[$filter['status']]['title'];
                    @endphp
                    <x-globals::actions.dropdown-menu variant="button" :label="$statusFilterLabel" content-role="default">
                        <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status=all" @if ($filter['status'] == 'all') class="active" @endif><x-global::elements.icon name="language" /> {{ $tpl->__('status.all') }}</a></li>
                        @foreach ($statusLabels as $key => $data)
                            <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status={{ $key }}" @if ($filter['status'] == $key) class="active" @endif><x-global::elements.icon :name="$data['icon']" /> {{ $data['title'] }}</a></li>
                        @endforeach
                    </x-globals::actions.dropdown-menu>
                @endif

                @if (count($allCanvas) > 0 && ! empty($relatesLabels))
                    @php
                        if ($filter['relates'] != 'all' && !isset($relatesLabels[$filter['relates']])) { $filter['relates'] = 'all'; }
                        $relatesFilterLabel = $filter['relates'] == 'all'
                            ? '<x-global::elements.icon name="language" /> ' . $tpl->__('relates.all')
                            : '<span class="material-symbols-outlined" style="font-size:inherit;vertical-align:middle;">' . $relatesLabels[$filter['relates']]['icon'] . '</span> ' . $relatesLabels[$filter['relates']]['title'];
                    @endphp
                    <x-globals::actions.dropdown-menu variant="button" :label="$relatesFilterLabel" content-role="default">
                        <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates=all" @if ($filter['relates'] == 'all') class="active" @endif><x-global::elements.icon name="language" /> {{ $tpl->__('relates.all') }}</a></li>
                        @foreach ($relatesLabels as $key => $data)
                            <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates={{ $key }}" @if ($filter['relates'] == $key) class="active" @endif><x-global::elements.icon :name="$data['icon']" /> {{ $data['title'] }}</a></li>
                        @endforeach
                    </x-globals::actions.dropdown-menu>
                @endif

            </div>
        </div>

        <div class="clearfix"></div>
