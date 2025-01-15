
<?php

$canvasTitle = '';
$allCanvas = $allCanvas;
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

//get canvas title
foreach ($allCanvas as $canvasRow) {
    if ($canvasRow['id'] == $tpl->get('currentCanvas')) {
        $canvasTitle = $canvasRow['title'];
        break;
    }
}

$tpl->assign('canvasTitle', $canvasTitle);

?>
<style>
    .canvas-row {
        margin-left: 0px;
        margin-right: 0px;
    }

    .canvas-title-only {
        border-radius: var(--box-radius-small);
    }

    h4.canvas-element-title-empty {
        background: white !important;
        border-color: white !important;
    }

    div.canvas-element-center-middle {
        text-align: center;
    }
</style>

<div class="pageheader">
    <div class="pageicon"><span class='fa <?= $canvasIcon ?>'></span></div>
    <div class="pagetitle">
        <h5><?php $tpl->e(session('currentProjectClient') . ' // ' . session('currentProjectName')); ?></h5>
        <?php if (count($allCanvas) > 0) {?>

        <span class="dropdown dropdownWrapper headerEditDropdown">
            <x-global::content.context-menu>
                @if ($login::userIsAtLeast($roles::$editor))
                    <x-global::actions.dropdown.item 
                        href="#/{{ $canvasName }}canvas/boardDialog/{{ $currentCanvas}}">
                        {!! __('links.icon.edit') !!}
                    </x-global::actions.dropdown.item>
        
                    <x-global::actions.dropdown.item 
                        href="{{ url($canvasName . 'canvas/export/' . $tpl->get('currentCanvas')) }}">
                        {!! __('links.icon.export') !!}
                    </x-global::actions.dropdown.item>
        
                    <x-global::actions.dropdown.item 
                        href="#/{{ $canvasName }}canvas/delCanvas/{{ $currentCanvas}}" 
                        class="delete">
                        {!! __('links.icon.delete') !!}
                    </x-global::actions.dropdown.item>
                @endif
            </x-global::content.context-menu>
        </span>
        
        <?php } ?>
        <h1><?= $tpl->__("headline.$canvasName.board") ?> //
            <?php if (count($allCanvas) > 0) {?>
            <x-global::actions.dropdown label-text="{{ $canvasTitle }}&nbsp;<i class='fa fa-caret-down'></i>"
                contentRole="ghost" position="bottom" align="start">

                <x-slot:menu>
                    @if ($login::userIsAtLeast($roles::$editor))
                        <x-global::actions.dropdown.item variant="link" href="#/{{ $canvasName }}canvas/boardDialog">
                            {!! __('links.icon.create_new_board') !!}
                        </x-global::actions.dropdown.item>
                    @endif

                    <!-- Static Divider -->
                    <li class="border"></li>

                    <!-- Dynamic List of Canvas Items -->
                    @foreach ($allCanvas as $canvasRow)
                        <x-global::actions.dropdown.item variant="link"
                            href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas/{{ $canvasRow['id'] }}">
                            {!! $tpl->escape($canvasRow['title']) !!}
                        </x-global::actions.dropdown.item>
                    @endforeach
                </x-slot:menu>

            </x-global::actions.dropdown>


            <?php } ?>
        </h1>
    </div>
</div><!--pageheader-->


@displayNotification()

@if ($login::userIsAtLeast($roles::$editor) && count($canvasTypes) == 1 && count($allCanvas) > 0)
    <a href="#/{{ $canvasName }}canvas/editCanvasItem?type={{ $elementName }}" class="btn btn-primary"
        id="{{ $elementName }}">
        {!! __('links.add_new_canvas_item' . $canvasName) !!}
    </a>
@endif

</div>

<div class="col-md-6 center">

    <div class="col-md-3">
        <div class="pull-right">
            <div class="btn-group viewDropDown">

                @if (count($allCanvas) > 0 && !empty($statusLabels))
                    <x-global::actions.dropdown
                        label-text="<i class='fas fa-{{ $filter['status'] == 'all' ? 'filter' : $statusLabels[$filter['status']]['icon'] }}'></i> {{ $filter['status'] == 'all' ? __('status.all') : $statusLabels[$filter['status']]['title'] }} {!! __('links.view') !!}"
                        contentRole="ghost" position="bottom" align="start">

                        <x-slot:menu>
                            <!-- Menu Item for "All Status" -->
                            <x-global::actions.dropdown.item variant="link"
                                href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status=all"
                                :class="$filter['status'] == 'all' ? 'active' : ''">
                                <i class="fas fa-globe"></i> {{ __('status.all') }}
                            </x-global::actions.dropdown.item>

                            <!-- Dynamic Status Menu Items -->
                            @foreach ($statusLabels as $key => $data)
                                <x-global::actions.dropdown.item variant="link"
                                    href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_status={{ $key }}"
                                    :class="$filter['status'] == $key ? 'active' : ''">
                                    <i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}
                                </x-global::actions.dropdown.item>
                            @endforeach
                        </x-slot:menu>
                    </x-global::actions.dropdown>
                @endif

            </div>

            <div class="btn-group viewDropDown">
                @if (count($allCanvas) > 0 && !empty($relatesLabels))
                    <x-global::actions.dropdown
                        label-text="<i class='fas fa-fw {{ $filter['relates'] == 'all' ? 'fa-globe' : $relatesLabels[$filter['relates']]['icon'] }}'></i> {{ $filter['relates'] == 'all' ? __('relates.all') : $relatesLabels[$filter['relates']]['title'] }} {{ __('links.view') }}"
                        contentRole="link" position="bottom" align="start">

                        <x-slot:menu>
                            <!-- Menu Item for "All Relates" -->
                            <x-global::actions.dropdown.item variant="link"
                                href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates=all"
                                :class="$filter['relates'] == 'all' ? 'active' : ''">
                                <i class="fas fa-globe"></i> {{ __('relates.all') }}
                            </x-global::actions.dropdown.item>

                            <!-- Dynamic Relates Menu Items -->
                            @foreach ($relatesLabels as $key => $data)
                                <x-global::actions.dropdown.item variant="link"
                                    href="{{ BASE_URL }}/{{ $canvasName }}canvas/showCanvas?filter_relates={{ $key }}"
                                    :class="$filter['relates'] == $key ? 'active' : ''">
                                    <i class="fas fa-fw {{ $data['icon'] }}"></i> {{ $data['title'] }}
                                </x-global::actions.dropdown.item>
                            @endforeach
                        </x-slot:menu>
                    </x-global::actions.dropdown>
                @endif

            </div>

        </div>
    </div>

</div>

<div class="clearfix"></div>
