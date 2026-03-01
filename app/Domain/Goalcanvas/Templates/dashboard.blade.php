@extends($layout)
@section('content')

@php

use Leantime\Domain\Goalcanvas\Services\Goalcanvas;


$elementName = 'goal';

/**
 * showCanvasTop.inc template - Top part of the main canvas page
 *
 * Required variables:
 * - goal      Name of current canvas
 */

$canvasTitle = '';

//get canvas title
foreach ($allCanvas as $canvasRow) {
    if ($canvasRow["id"] == $currentCanvas) {
        $canvasTitle = $canvasRow["title"];
        $canvasId = $canvasRow["id"];
        break;
    }
}

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
        <h5>{{ session("currentProjectClient") . " // " . session("currentProjectName") }}</h5>

        <h1>{{ __("headline.goal.dashboardboard") }} //
            @if (count($allCanvas) > 0)
                <x-globals::actions.dropdown-menu variant="link" trailing-visual="arrow_drop_down" label="All Goal Groups" trigger-class="header-title-dropdown">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/goalcanvas/bigRock">{!! __("links.icon.create_new_board") !!}</a></li>
                    @endif
                    <li class="border"></li>
                    @foreach ($allCanvas as $canvasRow)
                        <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}">{{ $canvasRow['title'] }}</a></li>
                    @endforeach
                </x-globals::actions.dropdown-menu>
            @endif
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">

<div class="row tw:mb-8">
    <div class="col-md-5">
        <div class="bigNumberBox tw:py-7 tw:px-4">
            <h2>Progress: {{ round($goalStats['avgPercentComplete']) }}%</h2>

            <x-global::progress :value="round($goalStats['avgPercentComplete'])" :showLabel="false" class="tw:mt-1" />
        </div>
    </div>
    <div class="col-md-2">
        <div class="bigNumberBox priority-border-4">
            <h2>On Track</h2>
            <span class="content">{{ $goalStats['goalsOnTrack'] }}</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="bigNumberBox priority-border-3">
            <h2>At Risk</h2>
            <span class="content">{{ $goalStats['goalsAtRisk'] }}</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="bigNumberBox priority-border-1">
            <h2>Miss</h2>
            <span class="content">{{ $goalStats['goalsMiss'] }}</span>
        </div>
    </div>
</div>

<div class="maincontentinner">
    @if (count($allCanvas) > 0)
        @foreach ($allCanvas as $canvasRow)
            <div>
                @php $canvasRowId = $canvasRow['id']; @endphp
                <x-globals::forms.button :link="'#/goalcanvas/editCanvasItem?type=goal&canvasId=' . $canvasRowId" type="primary" class="pull-right" icon="add">Create New Goal</x-globals::forms.button>

                <h5 class='subtitle'><a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRowId }}'>{{ $canvasRow["title"] }}</a></h5>
            </div>
            <div class="tw:border-b tw:border-solid tw:border-[var(--main-border-color)] tw:mb-5">
                @php
                $canvasSvc = app()->make(Goalcanvas::class);
                $canvasItems = $canvasSvc->getCanvasItemsById($canvasRow["id"]);
                @endphp
                <div id="sortableCanvasKanban-{{ $canvasRow['id'] }}" class="sortableTicketList disabled tw:pt-4">
                    <div class="row">
                                @if (!is_countable($canvasItems) || count($canvasItems) == 0)
                                    <div class="col-md-12">No goals on this board yet. Open the <a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow["id"] }}'>board</a> to start adding goals</div>
                                @endif
                                @foreach ($canvasItems as $row)
                                    @php
                                    $filterStatus = $filter['status'] ?? 'all';
                                    $filterRelates = $filter['relates'] ?? 'all';
                                    @endphp
                                    @if ($row['box'] === $elementName && ($filterStatus == 'all' || $filterStatus == $row['status']) && ($filterRelates == 'all' || $filterRelates == $row['relates']))
                                        <div class="col-md-4">
                                            <x-globals::goals.goal-card :row="$row" :status-labels="$statusLabels" :relates-labels="$relatesLabels" :users="$users" :element-name="$elementName" />
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <br />
                </div>
            </div>
        @endforeach
    @endif
</div>



{{--
 * showCanvasBottom.blade.php template - Bottom part of the main canvas page
 *
 * Required variables:
 * - goal      Name of current canvas
--}}

@if (count($allCanvas) > 0)
    {{--  --}}
@else
    <br><br>
    <div class='center'>
        <div class='svgContainer'>
            {!! file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg") !!}
        </div>
        <h3>{{ __("headlines.goal.analysis") }}</h3>
        <br>{!! __("text.goal.helper_content") !!}

        @if ($login::userIsAtLeast($roles::$editor))
            <br><br>
            <x-globals::forms.button link="javascript:void(0)" type="primary" class="addCanvasLink">
                {!! __("links.icon.create_new_board") !!}
            </x-globals::forms.button>
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
jQuery(document).ready(function() {
    if(jQuery('#searchCanvas').length > 0) {
        new SlimSelect({ select: '#searchCanvas' });
    }

    leantime.goalCanvasController.setRowHeights();
    leantime.canvasController.setCanvasName('goal');
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
            $modalUrl = $_GET['showModal'] == ""
                ? "&type=" . array_key_first($canvasTypes)
                : "/" . (int)$_GET['showModal'];
        @endphp
        leantime.canvasController.openModalManually("{{ BASE_URL }}/goalcanvas/editCanvasItem{{ $modalUrl }}");
        window.history.pushState({}, document.title, '{{ BASE_URL }}/goalcanvas/showCanvas/');
    @endif
});
</script>

@endsection
