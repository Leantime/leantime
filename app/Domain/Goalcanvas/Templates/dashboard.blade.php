@extends($layout)
@section('content')

@php

use Leantime\Domain\Comments\Repositories\Comments;
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
    h4.canvas-element-title-empty { background: white !important; border-color: white !important; }
    div.canvas-element-center-middle { text-align: center; }
</style>

<x-global::layout.page-header icon="fa {{ $canvasIcon }}">
    @if (count($allCanvas) > 0)
        <x-slot:switcher>
            <x-global::subjectSwitcher
                :parent="__('headline.goal.dashboardboard')"
                current="All Goal Groups">
                @if ($login::userIsAtLeast($roles::$editor))
                    <li><a href="#/goalcanvas/bigRock">{!! __("links.icon.create_new_board") !!}</a></li>
                @endif
                <li class="border"></li>
                @foreach ($allCanvas as $canvasRow)
                    <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}">{{ $canvasRow['title'] }}</a></li>
                @endforeach
            </x-global::subjectSwitcher>
        </x-slot:switcher>
    @else
        {{ __("headline.goal.dashboardboard") }}
    @endif
</x-global::layout.page-header>

<div class="maincontent">

<div class="row" style="margin-bottom:20px; ">
    <div class="col-md-4">
        <div class="bigNumberBox" style="padding: 29px 15px;">
            <h2>{{ sprintf(__('label.goal_progress_percent'), round($goalStats['avgPercentComplete'])) }}</h2>

            <x-global::feedback.progress :value="$goalStats['avgPercentComplete']" style="margin-top:5px;" />
        </div>
    </div>
    <div class="col-md-2"></div>
    <div class="col-md-2">
        <div class="bigNumberBox priority-border-4">
            <h2>{{ __('label.project_status_green') }}</h2>
            <span class="content">{{ $goalStats['goalsOnTrack'] }}</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="bigNumberBox priority-border-3">
            <h2>{{ __('label.project_status_yellow') }}</h2>
            <span class="content">{{ $goalStats['goalsAtRisk'] }}</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="bigNumberBox priority-border-1">
            <h2>{{ __('label.goal_miss') }}</h2>
            <span class="content">{{ $goalStats['goalsMiss'] }}</span>
        </div>
    </div>
</div>

<x-global::layout.content-box>
    <div class="row">
        <div class="col-md-6"></div>
    </div>
    @if (count($allCanvas) > 0)
        @foreach ($allCanvas as $canvasRow)
            <div class="row">
                <div class="col-md-12">
                    <x-global::elements.section-header as="h5">
                        <a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow["id"] }}'>{{ $canvasRow["title"] }}</a>
                        <x-slot:actions>
                            <a href='#/goalcanvas/editCanvasItem?type=goal&canvasId={{ $canvasRow["id"] }}' class='btn btn-primary'><i class="fa fa-plus"></i> {{ __('links.create_new_goal') }}</a>
                        </x-slot:actions>
                    </x-global::elements.section-header>
                </div>
            </div>
            <div class="row" style="border-bottom:1px solid var(--main-border-color); margin-bottom:20px">
                @php
                $canvasSvc = app()->make(Goalcanvas::class);
                $canvasItems = $canvasSvc->getCanvasItemsById($canvasRow["id"]);
                @endphp
                <div id="sortableCanvasKanban-{{ $canvasRow['id'] }}" class="sortableTicketList disabled col-md-12" style="padding-top:15px;">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="row">
                                @if (!is_countable($canvasItems) || count($canvasItems) == 0)
                                    <div class='col-md-12'>{!! sprintf(__('text.goal_board_empty'), BASE_URL . '/goalcanvas/showCanvas/' . (int) $canvasRow["id"]) !!}</div>
                                @endif
                                @foreach ($canvasItems as $row)
                                    @php
                                    $filterStatus = $filter['status'] ?? 'all';
                                    $filterRelates = $filter['relates'] ?? 'all';
                                    @endphp
                                    @if ($row['box'] === $elementName && ($filterStatus == 'all' || $filterStatus == $row['status']) && ($filterRelates == 'all' || $filterRelates == $row['relates']))
                                        @php
                                        $comments = app()->make(Comments::class);
                                        $nbcomments = $comments->countComments(moduleId: $row['id']);
                                        @endphp
                                        <div class="col-md-4">
                                            <x-global::elements.card compact class="ticketBox" id="item_{{ $row['id'] }}">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        @if ($login::userIsAtLeast($roles::$editor))
                                                            <x-global::actions.dropdown style="float:right;">
                                                                <li class="nav-header">{{ __("subtitles.edit") }}</li>
                                                                <li><a href="#/goalcanvas/editCanvasItem/{{ $row["id"] }}" class="goalCanvasModal" data="item_{{ $row["id"] }}">{!! __("links.edit_canvas_item") !!}</a></li>
                                                                <li><a href="#/goalcanvas/delCanvasItem/{{ $row["id"] }}" class="delete goalCanvasModal" data="item_{{ $row["id"] }}">{!! __("links.delete_canvas_item") !!}</a></li>

                                                            </x-global::actions.dropdown>
                                                        @endif

                                                        <h4>
                                                            <strong>{{ __('label.goal_colon') }}</strong>
                                                            <a href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}" class="goalCanvasModal" data="item_{{ $row['id'] }}">
                                                                {{ $row["title"] }}
                                                            </a>
                                                        </h4>
                                                        <br />
                                                        <strong>{{ __('label.metric_colon') }}</strong> {{$row["description"] }}
                                                        <br /><br />

                                                        @php
                                                        $percentDone = $row["goalProgress"];
                                                        $metricTypeFront = '';
                                                        $metricTypeBack = '';
                                                        if ($row["metricType"] == "percent") {
                                                            $metricTypeBack = '%';
                                                        } elseif ($row["metricType"] == "currency") {
                                                            $metricTypeFront = __("language.currency");
                                                        }
                                                        @endphp

                                                        <div class="row">
                                                            <div class="col-md-4"></div>
                                                            <div class="col-md4 center">
                                                                <small>{{ sprintf(__("text.percent_complete"), $percentDone) }}</small>
                                                            </div>
                                                            <div class="col-md-4"></div>
                                                        </div>
                                                        <x-global::feedback.progress :value="$percentDone" />
                                                        <div class="row" style="padding-bottom:0px;">
                                                            <div class="col-md-4">
                                                                <small>{{ __('label.start_colon') }}<br />{{ $metricTypeFront . $row["startValue"] . $metricTypeBack }}</small>
                                                            </div>
                                                            <div class="col-md-4 center">
                                                                <small>{{ __('label.current') }}:<br />{{ $metricTypeFront . $row["currentValue"] . $metricTypeBack }}</small>
                                                            </div>
                                                            <div class="col-md-4" style="text-align:right">
                                                                <small>{{ __('label.goal') }}:<br />{{ $metricTypeFront . $row["endValue"] . $metricTypeBack }}</small>
                                                            </div>
                                                        </div>

                                                        <div class="clearfix" style="padding-bottom: 8px;"></div>

                                                        @if (!empty($statusLabels))
                                                            <x-blueprints::chip-label type="status" adapter="goal" :item-id="$row['id']" :value="$row['status'] ?? null" :labels="$statusLabels" />
                                                        @endif

                                                        @if (!empty($relatesLabels))
                                                            <x-blueprints::chip-label type="relates" adapter="goal" :item-id="$row['id']" :value="$row['relates'] ?? null" :labels="$relatesLabels" />
                                                        @endif

                                                        <x-global::elements.author-avatar class="dropRight" :user-id="$row['author']" :name="trim(($row['authorFirstname'] ?? '').' '.($row['authorLastname'] ?? ''))" />

                                                        <div class="right" style="margin-right:10px;">
                                                            <span class="fas fa-comments"></span>
                                                            <small>{{ $nbcomments }}</small>
                                                        </div>
                                                    </div>
                                                </div>

                                                @include('goalcanvas::partials.milestoneChips', ['milestones' => $row['milestones'] ?? []])
                                            </x-global::elements.card>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <br />
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    @endif
</x-global::layout.content-box>



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
            <a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                {{ __("links.icon.create_new_board") }}
            </a>
        @endif
    </div>
@endif

@if (!empty($disclaimer) && count($allCanvas) > 0)
    <small class="align-center">{{ $disclaimer }}</small>
@endif

{!! $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'modals'), $__data)->render() !!}

</div>
</div>

<script type="text/javascript">
jQuery(document).ready(function() {
    leantime.goalCanvasController.setRowHeights();
    leantime.canvasController.setCanvasName('goal');
    leantime.canvasController.initFilterBar();

    @if ($login::userIsAtLeast($roles::$editor))
        leantime.canvasController.initCanvasLinks();
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
