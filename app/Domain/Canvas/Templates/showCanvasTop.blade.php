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
        @if(count($allCanvas) > 0)
            <x-global::subjectSwitcher
                :parent="__('headline.' . $canvasName . '.board')"
                :current="$canvasTitle">
                @if($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/{{ $canvasName }}canvas/boardDialog">{!! __('links.icon.create_new_board') !!}</a></li>
                @endif
                <li class="border"></li>
                @foreach($allCanvas as $canvasRow)
                    <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/{{ $canvasRow['id'] }}">{{ e($canvasRow['title']) }}</a></li>
                @endforeach
            </x-global::subjectSwitcher>
        @else
            <h1>{!! __("headline.$canvasName.board") !!}</h1>
        @endif
    </div>
    @if(count($allCanvas) > 0)
        <div class="pageheader-right">
            <x-global::actions.dropdown variant="header-menu">
                @if($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/{{ $canvasName }}canvas/boardDialog/{{ $currentCanvas }}" class="editCanvasLink ">{!! __('links.icon.edit') !!}</a></li>
                @endif
                <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/export/{{ $currentCanvas }}">{!! __('links.icon.export') !!}</a></li>
                <li><a href="javascript:window.print();">{!! __('links.icon.print') !!}</a></li>
                @if($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/{{ $canvasName }}canvas/delCanvas/{{ $currentCanvas }}" class="delete">{!! __('links.icon.delete') !!}</a></li>
                @endif

            </x-global::actions.dropdown>
        </div>
    @endif
</div><!--pageheader-->

<div class="maincontent">
    <x-global::navigation.view-tabs>
        <x-slot:actions>
            @if($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
                <x-global::forms.button tag="a" link="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $elementName }}"
                   contentRole="primary" id="{{ $elementName }}">{!! __('links.add_new_canvas_item' . $canvasName) !!}</x-global::forms.button>
            @endif
            @if(count($allCanvas) > 0 && ! empty($statusLabels))
                <x-global::actions.dropdown variant="filter">
                    <x-slot:trigger class="btn-link">
                        @if($filter['status'] == 'all')
                            <i class="fas fa-filter"></i> {!! __('status.all') !!} {!! __('links.view') !!}
                        @else
                            <i class="fas fa-fw {!! __($statusLabels[$filter['status']]['icon']) !!}"></i> {{ $statusLabels[$filter['status']]['title'] }} {!! __('links.view') !!}
                        @endif
                    </x-slot:trigger>
                    <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status=all" @if($filter['status'] == 'all') class="active" @endif><i class="fas fa-globe"></i> {!! __('status.all') !!}</a></li>
                    @foreach($statusLabels as $key => $data)
                        <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status={{ $key }}" @if($filter['status'] == $key) class="active" @endif><i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}</a></li>
                    @endforeach
                </x-global::actions.dropdown>
            @endif
            @if(count($allCanvas) > 0 && ! empty($relatesLabels))
                <x-global::actions.dropdown variant="filter">
                    <x-slot:trigger class="btn-link">
                        @if($filter['relates'] == 'all')
                            <i class="fas fa-fw fa-globe"></i> {!! __('relates.all') !!} {!! __('links.view') !!}
                        @else
                            <i class="fas fa-fw {!! __($relatesLabels[$filter['relates']]['icon']) !!}"></i> {{ $relatesLabels[$filter['relates']]['title'] }} {!! __('links.view') !!}
                        @endif
                    </x-slot:trigger>
                    <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates=all" @if($filter['relates'] == 'all') class="active" @endif><i class="fas fa-globe"></i> {!! __('relates.all') !!}</a></li>
                    @foreach($relatesLabels as $key => $data)
                        <li><a href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates={{ $key }}" @if($filter['relates'] == $key) class="active" @endif><i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}</a></li>
                    @endforeach
                </x-global::actions.dropdown>
            @endif
        </x-slot:actions>
    </x-global::navigation.view-tabs>

    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

