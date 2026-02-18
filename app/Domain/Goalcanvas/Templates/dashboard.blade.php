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

<div class="pageheader">
    <div class="pageicon"><span class='fa {{ $canvasIcon }}'></span></div>
    <div class="pagetitle">
        <h5>{{ session("currentProjectClient") . " // " . session("currentProjectName") }}</h5>

        <h1>{{ __("headline.goal.dashboardboard") }} //
            @if (count($allCanvas) > 0)
                <x-global::elements.link-dropdown label="All Goal Groups" triggerClass="header-title-dropdown">
                    @if ($login::userIsAtLeast($roles::$editor))
                        <li><a href="#/goalcanvas/bigRock">{!! __("links.icon.create_new_board") !!}</a></li>
                    @endif
                    <li class="border"></li>
                    @foreach ($allCanvas as $canvasRow)
                        <li><a href="{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}">{{ $canvasRow['title'] }}</a></li>
                    @endforeach
                </x-global::elements.link-dropdown>
            @endif
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">

<div class="tw:grid tw:grid-cols-[2fr_1fr_1fr_1fr] tw:gap-4 tw:mb-5">
    <div>
        <div class="bigNumberBox tw:py-7 tw:px-4">
            <h2>Progress: {{ round($goalStats['avgPercentComplete']) }}%</h2>

            <div class="progress tw:mt-1">
                <div class="progress-bar progress-bar-success" role="progressbar"
                     aria-valuenow="{{ round($goalStats['avgPercentComplete']) }}" aria-valuemin="0" aria-valuemax="100"
                     style="width: {{ $goalStats['avgPercentComplete'] }}%">
                    <span class="sr-only">{{ sprintf(__("text.percent_complete"), round($goalStats['avgPercentComplete'])) }}</span>
                </div>
            </div>
        </div>
    </div>
    <div>
        <div class="bigNumberBox priority-border-4">
            <h2>On Track</h2>
            <span class="content">{{ $goalStats['goalsOnTrack'] }}</span>
        </div>
    </div>
    <div>
        <div class="bigNumberBox priority-border-3">
            <h2>At Risk</h2>
            <span class="content">{{ $goalStats['goalsAtRisk'] }}</span>
        </div>
    </div>
    <div>
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
                <x-global::button link="#/goalcanvas/editCanvasItem?type=goal&canvasId={{ $canvasRow["id"] }}" type="primary" class="tw:float-right" icon="fa fa-plus">Create New Goal</x-global::button>

                <h5 class='subtitle'><a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow["id"] }}'>{{ $canvasRow["title"] }}</a></h5>
            </div>
            <div class="tw:border-b tw:border-solid tw:border-[var(--main-border-color)] tw:mb-5">
                @php
                $canvasSvc = app()->make(Goalcanvas::class);
                $canvasItems = $canvasSvc->getCanvasItemsById($canvasRow["id"]);
                @endphp
                <div id="sortableCanvasKanban-{{ $canvasRow['id'] }}" class="sortableTicketList disabled tw:pt-4">
                    <div class="tw:grid tw:md:grid-cols-3 tw:gap-4">
                                @if (!is_countable($canvasItems) || count($canvasItems) == 0)
                                    <div class="tw:col-span-3">No goals on this board yet. Open the <a href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow["id"] }}'>board</a> to start adding goals</div>
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
                                        <div>
                                            <div class="ticketBox" id="item_{{ $row["id"] }}">
                                                        @if ($login::userIsAtLeast($roles::$editor))
                                                            <x-global::elements.dropdown class="tw:float-right">
                                                                <li><a href="#/goalcanvas/editCanvasItem/{{ $row["id"] }}" class="goalCanvasModal" data="item_{{ $row["id"] }}">{!! __("links.edit_canvas_item") !!}</a></li>
                                                                <li><a href="#/goalcanvas/delCanvasItem/{{ $row["id"] }}" class="delete goalCanvasModal" data="item_{{ $row["id"] }}">{!! __("links.delete_canvas_item") !!}</a></li>
                                                            </x-global::elements.dropdown>
                                                        @endif

                                                        <h4>
                                                            <strong>Goal:</strong>
                                                            <a href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}" class="goalCanvasModal" data="item_{{ $row['id'] }}">
                                                                {{ $row["title"] }}
                                                            </a>
                                                        </h4>
                                                        <br />
                                                        <strong>Metric:</strong> {{$row["description"] }}
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

                                                        <div class="tw:text-center">
                                                            <small>{{ sprintf(__("text.percent_complete"), $percentDone) }}</small>
                                                        </div>
                                                        <div class="progress tw:mb-0">
                                                            <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $percentDone }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $percentDone }}%">
                                                                <span class="sr-only">{{ sprintf(__("text.percent_complete"), $percentDone) }}</span>
                                                            </div>
                                                        </div>
                                                        <div class="tw:grid tw:grid-cols-3 tw:pb-0">
                                                            <div>
                                                                <small>Start:<br />{{ $metricTypeFront . $row["startValue"] . $metricTypeBack }}</small>
                                                            </div>
                                                            <div class="tw:text-center">
                                                                <small>{{ __('label.current') }}:<br />{{ $metricTypeFront . $row["currentValue"] . $metricTypeBack }}</small>
                                                            </div>
                                                            <div class="tw:text-right">
                                                                <small>{{ __('label.goal') }}:<br />{{ $metricTypeFront . $row["endValue"] . $metricTypeBack }}</small>
                                                            </div>
                                                        </div>

                                                        <div class="clearfix tw:pb-2"></div>

                                                        @if (!empty($statusLabels))
                                                            <div class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
                                                                <a class="dropdown-toggle f-left status label-{{ $row['status'] != "" ? $statusLabels[$row['status']]['dropdown'] : "" }}" href="javascript:void(0);" role="button" id="statusDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="text">{{ $row['status'] != "" ? $statusLabels[$row['status']]['title'] : "" }}</span> <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" aria-labelledby="statusDropdownMenuLink{{ $row['id'] }}">
                                                                    <li class="nav-header border">{{ __("dropdown.choose_status") }}</li>
                                                                    @foreach ($statusLabels as $key => $data)
                                                                        @if ($data['active'] || true)
                                                                            <li class='dropdown-item'>
                                                                                <a href="javascript:void(0);" class="label-{{ $data['dropdown'] }}" data-label='{{ $data["title"] }}' data-value="{{ $row['id'] . "/" . $key }}" id="ticketStatusChange{{ $row['id'] . $key }}">{!! $data['title'] !!}</a>
                                                                            </li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif

                                                        @if (!empty($relatesLabels))
                                                            <div class="dropdown ticketDropdown relatesDropdown colorized show firstDropdown">
                                                                <a class="dropdown-toggle f-left relates label-{{ $relatesLabels[$row['relates']]['dropdown'] }}" href="javascript:void(0);" role="button" id="relatesDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                    <span class="text">{{ $relatesLabels[$row['relates']]['title'] }}</span> <i class="fa fa-caret-down" aria-hidden="true"></i>
                                                                </a>
                                                                <ul class="dropdown-menu" aria-labelledby="relatesDropdownMenuLink{{ $row['id'] }}">
                                                                    <li class="nav-header border">{{ __("dropdown.choose_relates") }}</li>
                                                                    @foreach ($relatesLabels as $key => $data)
                                                                        @if ($data['active'] || true)
                                                                            <li class='dropdown-item'>
                                                                                <a href="javascript:void(0);" class="label-{{ $data['dropdown'] }}" data-label='{{ $data["title"] }}' data-value="{{ $row['id'] . "/" . $key }}" id="ticketRelatesChange{{ $row['id'] . $key }}">{!! $data['title'] !!}</a>
                                                                            </li>
                                                                        @endif
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif

                                                        <div class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                            <a class="dropdown-toggle f-left" href="javascript:void(0);" role="button" id="userDropdownMenuLink{{ $row['id'] }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <span class="text">
                                                                    @if ($row["authorFirstname"] != "")
                                                                        <span id='userImage{{ $row['id'] }}'>
                                                                            <img src='{{ BASE_URL }}/api/users?profileImage={{ $row['author'] }}' width='25' class="tw:align-middle"/>
                                                                        </span>
                                                                        <span id='user{{ $row['id'] }}'></span>
                                                                    @else
                                                                        <span id='userImage{{ $row['id'] }}'>
                                                                            <img src='{{ BASE_URL }}/api/users?profileImage=false' width='25' class="tw:align-middle"/>
                                                                        </span>
                                                                        <span id='user{{ $row['id'] }}'></span>
                                                                    @endif
                                                                </span>
                                                            </a>
                                                            <ul class="dropdown-menu" aria-labelledby="userDropdownMenuLink{{ $row['id'] }}">
                                                                <li class="nav-header border">{{ __("dropdown.choose_user") }}</li>
                                                                @foreach ($users as $user)
                                                                    <li class='dropdown-item'>
                                                                        <a href='javascript:void(0);' data-label='{{ sprintf(__("text.full_name"), $tpl->escape($user["firstname"]), $tpl->escape($user['lastname'])) }}' data-value='{{ $row['id'] . "_" . $user['id'] . "_" . $user['profileId'] }}' id='userStatusChange{{ $row['id'] . $user['id'] }}'>
                                                                            <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}' width='25' class="tw:align-middle tw:mr-1"/>
                                                                            {{ sprintf(__("text.full_name"), $tpl->escape($user["firstname"]), $tpl->escape($user['lastname'])) }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>

                                                        <div class="right tw:mr-2.5">
                                                            <span class="fas fa-comments"></span>
                                                            <small>{{ $nbcomments }}</small>
                                                        </div>

                                                @if ($row['milestoneHeadline'] != '')
                                                    <br/>
                                                    <div hx-trigger="load" hx-indicator=".htmx-indicator" hx-target="this" hx-swap="innerHTML" hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
                                                        <div class="htmx-indicator">
                                                            {{ __("label.loading_milestone") }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
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
    <div class='tw:text-center'>
        <div class='svgContainer'>
            {!! file_get_contents(ROOT . "/dist/images/svg/undraw_design_data_khdb.svg") !!}
        </div>
        <h3>{{ __("headlines.goal.analysis") }}</h3>
        <br>{!! __("text.goal.helper_content") !!}

        @if ($login::userIsAtLeast($roles::$editor))
            <br><br>
            <x-global::button link="javascript:void(0)" type="primary" class="addCanvasLink">
                {!! __("links.icon.create_new_board") !!}
            </x-global::button>
        @endif
    </div>
@endif

@if (!empty($disclaimer) && count($allCanvas) > 0)
    <small class="tw:text-center">{{ $disclaimer }}</small>
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
