@php
    $canvasTitle = '';
    $allCanvas = $allCanvas ?? [];
    $canvasIcon = $canvasIcon ?? '';
    $canvasTypes = $canvasTypes ?? [];
    $statusLabels = $statusLabels ?? [];
    $relatesLabels = $relatesLabels ?? [];
    $dataLabels = $dataLabels ?? [];
    $disclaimer = $disclaimer ?? '';
    $canvasItems = $canvasItems ?? [];

    $filter['status'] = $_GET['filter_status'] ?? (session('filter_status') ?? 'all');
    session(['filter_status' => $filter['status']]);
    $filter['relates'] = $_GET['filter_relates'] ?? (session('filter_relates') ?? 'all');
    session(['filter_relates' => $filter['relates']]);

    // get canvas title
    foreach ($allCanvas as $canvasRow) {
        if ($canvasRow['id'] == ($currentCanvas ?? '')) {
            $canvasTitle = $canvasRow['title'];
            break;
        }
    }

    $tpl->assign('canvasTitle', $canvasTitle);
@endphp

<style>
    .canvas-row { margin-left: 0px; margin-right: 0px;}
    .canvas-title-only { border-radius: var(--box-radius-small); }
    h4.canvas-element-title-empty { background: white !important; border-color: white !important; }
    div.canvas-element-center-middle { text-align: center; }
</style>

<div class="pageheader">
    <div class="pageicon"><span class='fa {{ $canvasIcon }}'></span></div>
    <div class="pagetitle">
        <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>
        @if(count($allCanvas) > 0)
            <span class="dropdown dropdownWrapper headerEditDropdown">
                <a href="javascript:void(0)" class="dropdown-toggle btn btn-transparent" data-toggle="dropdown"><i class="fa-solid fa-ellipsis-v"></i></a>
                <ul class="dropdown-menu editCanvasDropdown">
                    @if($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/{{ $canvasName }}canvas/boardDialog/{{ $currentCanvas }}" class="editCanvasLink ">{!! __('links.icon.edit') !!}</a></li>
                    @endif
                    <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/export/{{ $currentCanvas }}">{!! __('links.icon.export') !!}</a></li>
                    <li><a href="javascript:window.print();">{!! __('links.icon.print') !!}</a></li>
                    @if($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/{{ $canvasName }}canvas/delCanvas/{{ $currentCanvas }}" class="delete">{!! __('links.icon.delete') !!}</a></li>
                    @endif
                </ul>
            </span>
        @endif
        <h1>{!! __("headline.$canvasName.board") !!} //
            @if(count($allCanvas) > 0)
                <span class="dropdown dropdownWrapper">
                    <a href="javascript:void(0);" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                        {{ $canvasTitle }}&nbsp;<i class="fa fa-caret-down"></i>
                    </a>

                    <ul class="dropdown-menu canvasSelector">
                        @if($login::userIsAtLeast($roles::$editor))
                            <li><a href="#/{{ $canvasName }}canvas/boardDialog">{!! __('links.icon.create_new_board') !!}</a></li>
                        @endif
                        <li class="border"></li>
                        @foreach($allCanvas as $canvasRow)
                            <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/{{ $canvasRow['id'] }}">{{ e($canvasRow['title']) }}</a></li>
                        @endforeach
                    </ul>
                </span>
            @endif
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="row">
            <div class="col-md-3">

                @if($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                    <a href="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $elementName }}"
                       class="btn btn-primary" id="{{ $elementName }}">{!! __('links.add_new_canvas_item' . $canvasName) !!}</a>
                @endif

            </div>

            <div class="col-md-6 center">

            </div>

            <div class="col-md-3">
                <div class="pull-right">
                    <div class="btn-group viewDropDown">
                        @if(count($allCanvas) > 0 && ! empty($statusLabels))
                            @if($filter['status'] == 'all')
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-filter"></i> {!! __('status.all') !!} {!! __('links.view') !!}</button>
                            @else
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw {!! __($statusLabels[$filter['status']]['icon']) !!}"></i> {{ $statusLabels[$filter['status']]['title'] }} {!! __('links.view') !!}</button>
                            @endif
                            <ul class="dropdown-menu">
                                <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status=all" @if($filter['status'] == 'all') class="active" @endif><i class="fas fa-globe"></i> {!! __('status.all') !!}</a></li>
                                @foreach($statusLabels as $key => $data)
                                    <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status={{ $key }}" @if($filter['status'] == $key) class="active" @endif><i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                    <div class="btn-group viewDropDown">
                        @if(count($allCanvas) > 0 && ! empty($relatesLabels))
                            @if($filter['relates'] == 'all')
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw fa-globe"></i> {!! __('relates.all') !!} {!! __('links.view') !!}</button>
                            @else
                                <button class="btn dropdown-toggle" data-toggle="dropdown"><i class="fas fa-fw {!! __($relatesLabels[$filter['relates']]['icon']) !!}"></i> {{ $relatesLabels[$filter['relates']]['title'] }} {!! __('links.view') !!}</button>
                            @endif
                            <ul class="dropdown-menu">
                                <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates=all" @if($filter['relates'] == 'all') class="active" @endif><i class="fas fa-globe"></i> {!! __('relates.all') !!}</a></li>
                                @foreach($relatesLabels as $key => $data)
                                    <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates={{ $key }}" @if($filter['relates'] == $key) class="active" @endif><i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}</a></li>
                                @endforeach
                            </ul>
                        @endif
                    </div>

                </div>
            </div>

        </div>

        <div class="clearfix"></div>
