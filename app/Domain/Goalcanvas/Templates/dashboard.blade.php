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
            if ($canvasRow['id'] == $currentCanvas) {
                $canvasTitle = $canvasRow['title'];
                $canvasId = $canvasRow['id'];
                break;
            }
        }

    @endphp

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
        <div class="pageicon"><span class='fa {{ $canvasIcon }}'></span></div>
        <div class="pagetitle">
            <h5>{{ session('currentProjectClient') . ' // ' . session('currentProjectName') }}</h5>

            <h1>{{ __('headline.goal.dashboardboard') }} //
                @if (count($allCanvas) > 0)
                    <x-global::actions.dropdown label-text='All Goal Groups' contentRole="accent" position="bottom">
                        <x-slot:menu>
                            @if ($login::userIsAtLeast($roles::$editor))
                                <x-global::actions.dropdown.item variant="link" href="#/goalcanvas/bigRock">
                                    {!! __('links.icon.create_new_board') !!}
                                </x-global::actions.dropdown.item>
                                <x-global::actions.dropdown.item variant="border" />
                            @endif
                            @foreach ($allCanvas as $canvasRow)
                                <x-global::actions.dropdown.item variant="link"
                                    href="{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}">
                                    {{ $tpl->escape($canvasRow['title']) }}
                                </x-global::actions.dropdown.item>
                            @endforeach
                        </x-slot:menu>
                    </x-global::actions.dropdown>
                @endif
            </h1>
        </div>
    </div><!--pageheader-->

    <div class="maincontent">

        <div class="row" style="margin-bottom:20px; ">
            <div class="col-md-4">
                <div class="bigNumberBox" style="padding: 29px 15px;">
                    <h2>Progress: {{ round($goalStats['avgPercentComplete']) }}%</h2>

                    <div class="progress" style="margin-top:5px;">
                        <div class="progress-bar progress-bar-success" role="progressbar"
                            aria-valuenow="{{ round($goalStats['avgPercentComplete']) }}" aria-valuemin="0"
                            aria-valuemax="100" style="width: {{ $goalStats['avgPercentComplete'] }}%">
                            <span
                                class="sr-only">{{ sprintf(__('text.percent_complete'), round($goalStats['avgPercentComplete'])) }}</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-2"></div>
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
            <div class="col-md-2">
                <div class="bigNumberBox priority-border-1">
                    <h2>Miss</h2>
                    <span class="content">{{ $goalStats['goalsMiss'] }}</span>
                </div>
            </div>
        </div>

        <div class="maincontentinner">
            <div class="row">
                <div class="col-md-6"></div>
            </div>
            @if (count($allCanvas) > 0)
                @foreach ($allCanvas as $canvasRow)
                    <div class="row">
                        <div class="col-md-12">
                            <a href='#/goalcanvas/editCanvasItem?type=goal&canvasId={{ $canvasRow['id'] }}'
                                class='btn btn-primary pull-right'><i class="fa fa-plus"></i> Create New Goal</a>

                            <h5 class='subtitle'><a
                                    href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}'>{{ $tpl->escape($canvasRow['title']) }}</a>
                            </h5>
                        </div>
                    </div>
                    <div class="row" style="border-bottom:1px solid var(--main-border-color); margin-bottom:20px">
                        @php
                            $canvasSvc = app()->make(Goalcanvas::class);
                            $canvasItems = $canvasSvc->getCanvasItemsById($canvasRow['id']);
                        @endphp
                        <div id="sortableCanvasKanban-{{ $canvasRow['id'] }}" class="sortableTicketList disabled col-md-12"
                            style="padding-top:15px;">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        @if (!is_countable($canvasItems) || count($canvasItems) == 0)
                                            <div class='col-md-12'>No goals on this board yet. Open the <a
                                                    href='{{ BASE_URL }}/goalcanvas/showCanvas/{{ $canvasRow['id'] }}'>board</a>
                                                to start adding goals</div>
                                        @endif
                                        @foreach ($canvasItems as $row)
                                            @php
                                                $filterStatus = $filter['status'] ?? 'all';
                                                $filterRelates = $filter['relates'] ?? 'all';
                                            @endphp
                                            @if (
                                                $row['box'] === $elementName &&
                                                    ($filterStatus == 'all' || $filterStatus == $row['status']) &&
                                                    ($filterRelates == 'all' || $filterRelates == $row['relates']))
                                                @php
                                                    $comments = app()->make(Comments::class);
                                                    $nbcomments = $comments->countComments(moduleId: $row['id']);
                                                @endphp
                                                <div class="col-md-4">
                                                    <div class="ticketBox" id="item_{{ $row['id'] }}">
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="inlineDropDownContainer" style="float:right;">
                                                                    @if ($login::userIsAtLeast($roles::$editor))
                                                                        &nbsp;&nbsp;&nbsp;
                                                                        <x-global::content.context-menu>
                                                                            <li class="nav-header">
                                                                                {{ __('subtitles.edit') }}</li>
                                                                            <x-global::actions.dropdown.item variant="link"
                                                                                href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
                                                                                class="goalCanvasModal"
                                                                                data="item_{{ $row['id'] }}">
                                                                                {!! __('links.edit_canvas_item') !!}
                                                                            </x-global::actions.dropdown.item>
                                                                            <x-global::actions.dropdown.item variant="link"
                                                                                href="#/goalcanvas/delCanvasItem/{{ $row['id'] }}"
                                                                                class="delete goalCanvasModal"
                                                                                data="item_{{ $row['id'] }}">
                                                                                {!! __('links.delete_canvas_item') !!}
                                                                            </x-global::actions.dropdown.item>
                                                                        </x-global::content.context-menu>
                                                                    @endif
                                                                </div>

                                                                <h4>
                                                                    <strong>Goal:</strong>
                                                                    <a href="#/goalcanvas/editCanvasItem/{{ $row['id'] }}"
                                                                        class="goalCanvasModal"
                                                                        data="item_{{ $row['id'] }}">
                                                                        {{ $tpl->e($row['title']) }}
                                                                    </a>
                                                                </h4>
                                                                <br />
                                                                <strong>Metric:</strong>
                                                                {{ $tpl->escape($row['description']) }}
                                                                <br /><br />

                                                                @php
                                                                    $percentDone = $row['goalProgress'];
                                                                    $metricTypeFront = '';
                                                                    $metricTypeBack = '';
                                                                    if ($row['metricType'] == 'percent') {
                                                                        $metricTypeBack = '%';
                                                                    } elseif ($row['metricType'] == 'currency') {
                                                                        $metricTypeFront = __('language.currency');
                                                                    }
                                                                @endphp

                                                                <div class="row">
                                                                    <div class="col-md-4"></div>
                                                                    <div class="col-md4 center">
                                                                        <small>{{ sprintf(__('text.percent_complete'), $percentDone) }}</small>
                                                                    </div>
                                                                    <div class="col-md-4"></div>
                                                                </div>
                                                                <div class="progress" style="margin-bottom:0px;">
                                                                    <div class="progress-bar progress-bar-success"
                                                                        role="progressbar"
                                                                        aria-valuenow="{{ $percentDone }}"
                                                                        aria-valuemin="0" aria-valuemax="100"
                                                                        style="width: {{ $percentDone }}%">
                                                                        <span
                                                                            class="sr-only">{{ sprintf(__('text.percent_complete'), $percentDone) }}</span>
                                                                    </div>
                                                                </div>
                                                                <div class="row" style="padding-bottom:0px;">
                                                                    <div class="col-md-4">
                                                                        <small>Start:<br />{{ $metricTypeFront . $row['startValue'] . $metricTypeBack }}</small>
                                                                    </div>
                                                                    <div class="col-md-4 center">
                                                                        <small>{{ __('label.current') }}:<br />{{ $metricTypeFront . $row['currentValue'] . $metricTypeBack }}</small>
                                                                    </div>
                                                                    <div class="col-md-4" style="text-align:right">
                                                                        <small>{{ __('label.goal') }}:<br />{{ $metricTypeFront . $row['endValue'] . $metricTypeBack }}</small>
                                                                    </div>
                                                                </div>

                                                                <div class="clearfix" style="padding-bottom: 8px;"></div>

                                                                @if (!empty($statusLabels))
                                                                    <div
                                                                        class="dropdown ticketDropdown statusDropdown colorized show firstDropdown">
                                                                        <x-global::actions.dropdown
                                                                            label-text="<span class='text'>{{ $row['status'] != '' ? $statusLabels[$row['status']]['title'] : '' }}</span> <i class='fa fa-caret-down' aria-hidden='true'></i>"
                                                                            contentRole="link" position="bottom"
                                                                            align="start"
                                                                            class="ticketDropdown statusDropdown colorized show firstDropdown f-left status label-{{ $row['status'] != '' ? $statusLabels[$row['status']]['dropdown'] : '' }}"
                                                                            id="statusDropdownMenuLink{{ $row['id'] }}">

                                                                            <x-slot:menu>
                                                                                <!-- Menu Header -->
                                                                                <li class="nav-header border">
                                                                                    {{ __('dropdown.choose_status') }}</li>

                                                                                <!-- Dynamic Status Menu Items -->
                                                                                @foreach ($statusLabels as $key => $data)
                                                                                    @if ($data['active'] || true)
                                                                                        <x-global::actions.dropdown.item
                                                                                            variant="link"
                                                                                            href="javascript:void(0);"
                                                                                            class="label-{{ $data['dropdown'] }}"
                                                                                            :data-label="$data['title']"
                                                                                            :data-value="$row['id'] .
                                                                                                '/' .
                                                                                                $key"
                                                                                            :id="'ticketStatusChange' .
                                                                                                $row['id'] .
                                                                                                $key">
                                                                                            {{ $data['title'] }}
                                                                                        </x-global::actions.dropdown.item>
                                                                                    @endif
                                                                                @endforeach
                                                                            </x-slot:menu>

                                                                        </x-global::actions.dropdown>


                                                                    </div>
                                                                @endif

                                                                @if (!empty($relatesLabels))
                                                                    <div
                                                                        class="dropdown ticketDropdown relatesDropdown colorized show firstDropdown">
                                                                        <x-global::actions.dropdown
                                                                            label-text="<span class='text'>{{ $relatesLabels[$row['relates']]['title'] }}</span> <i class='fa fa-caret-down' aria-hidden='true'></i>"
                                                                            contentRole="link" position="bottom"
                                                                            align="start"
                                                                            class="f-left relates label-{{ $relatesLabels[$row['relates']]['dropdown'] }}"
                                                                            id="relatesDropdownMenuLink{{ $row['id'] }}">

                                                                            <x-slot:menu>
                                                                                <!-- Menu Header -->
                                                                                <li class="nav-header border">
                                                                                    {{ __('dropdown.choose_relates') }}
                                                                                </li>

                                                                                <!-- Dynamic Relates Menu Items -->
                                                                                @foreach ($relatesLabels as $key => $data)
                                                                                    @if ($data['active'] || true)
                                                                                        <x-global::actions.dropdown.item
                                                                                            variant="link"
                                                                                            href="javascript:void(0);"
                                                                                            class="label-{{ $data['dropdown'] }}"
                                                                                            :data-label="$data['title']"
                                                                                            :data-value="$row['id'] .
                                                                                                '/' .
                                                                                                $key"
                                                                                            :id="'ticketRelatesChange' .
                                                                                                $row['id'] .
                                                                                                $key">
                                                                                            {{ $data['title'] }}
                                                                                        </x-global::actions.dropdown.item>
                                                                                    @endif
                                                                                @endforeach
                                                                            </x-slot:menu>

                                                                        </x-global::actions.dropdown>

                                                                    </div>
                                                                @endif

                                                                <div
                                                                    class="dropdown ticketDropdown userDropdown noBg show right lastDropdown dropRight">
                                                                    @php
                                                                        // Determine the label content dynamically
                                                                        $labelContent = '<span class="text">';

                                                                        if ($row['authorFirstname'] != '') {
                                                                            $labelContent .=
                                                                                "
                                                                            <span id='userImage{$row['id']}'>
                                                                                <img src='" .
                                                                                BASE_URL .
                                                                                "/api/users?profileImage={$row['author']}' width='25' style='vertical-align: middle;' />
                                                                            </span>
                                                                            <span id='user{$row['id']}'></span>";
                                                                        } else {
                                                                            $labelContent .=
                                                                                "
                                                                            <span id='userImage{$row['id']}'>
                                                                                <img src='" .
                                                                                BASE_URL .
                                                                                "/api/users?profileImage=false' width='25' style='vertical-align: middle;' />
                                                                            </span>
                                                                            <span id='user{$row['id']}'></span>";
                                                                        }

                                                                        $labelContent .= '</span>';
                                                                    @endphp

                                                                    <x-global::actions.dropdown :label-text="$labelContent"
                                                                        contentRole="link" position="bottom"
                                                                        align="start" class="f-left"
                                                                        id="userDropdownMenuLink{{ $row['id'] }}">

                                                                        <x-slot:menu>
                                                                            <!-- Menu Header -->
                                                                            <li class="nav-header border">
                                                                                {{ __('dropdown.choose_user') }}</li>

                                                                            <!-- Dynamic User Menu Items -->
                                                                            @foreach ($users as $user)
                                                                                <x-global::actions.dropdown.item
                                                                                    variant="link"
                                                                                    href="javascript:void(0);"
                                                                                    :data-label="sprintf(
                                                                                        __('text.full_name'),
                                                                                        $tpl->escape(
                                                                                            $user['firstname'],
                                                                                        ),
                                                                                        $tpl->escape($user['lastname']),
                                                                                    )" :data-value="$row['id'] .
                                                                                        '_' .
                                                                                        $user['id'] .
                                                                                        '_' .
                                                                                        $user['profileId']"
                                                                                    :id="'userStatusChange' .
                                                                                        $row['id'] .
                                                                                        $user['id']">
                                                                                    <img src='{{ BASE_URL }}/api/users?profileImage={{ $user['id'] }}'
                                                                                        width='25'
                                                                                        style='vertical-align: middle; margin-right:5px;' />
                                                                                    {{ sprintf(__('text.full_name'), $tpl->escape($user['firstname']), $tpl->escape($user['lastname'])) }}
                                                                                </x-global::actions.dropdown.item>
                                                                            @endforeach
                                                                        </x-slot:menu>

                                                                    </x-global::actions.dropdown>

                                                                </div>

                                                                <div class="right" style="margin-right:10px;">
                                                                    <a href="{{ BASE_URL }}/goalcanvas/editCanvasComment/{{ $row['id'] }}"
                                                                        class="goalCanvasModal"
                                                                        data="item_{{ $row['id'] }}"
                                                                        @if ($nbcomments == 0) style="color: grey;" @endif>
                                                                        <span class="fas fa-comments"></span>
                                                                    </a>
                                                                    <small>{{ $nbcomments }}</small>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        @if ($row['milestoneHeadline'] != '')
                                                            <br />
                                                            <div hx-trigger="load" hx-indicator=".htmx-indicator"
                                                                hx-get="{{ BASE_URL }}/hx/tickets/milestones/showCard?milestoneId={{ $row['milestoneId'] }}">
                                                                <div class="htmx-indicator">
                                                                    {{ __('label.loading_milestone') }}
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
                    {!! file_get_contents(ROOT . '/dist/images/svg/undraw_design_data_khdb.svg') !!}
                </div>
                <h3>{{ __('headlines.goal.analysis') }}</h3>
                <br>{!! __('text.goal.helper_content') !!}

                @if ($login::userIsAtLeast($roles::$editor))
                    <br><br>
                    <a href='javascript:void(0)' class='addCanvasLink btn btn-primary'>
                        {!! __('links.icon.create_new_board') !!}
                    </a>
                @endif
            </div>
        @endif

        @if (!empty($disclaimer) && count($allCanvas) > 0)
            <small class="align-center">{{ $disclaimer }}</small>
        @endif

        {{-- {!! $tpl->viewFactory->make($tpl->getTemplatePath('canvas', 'modals'), $__data)->render() !!} --}}

    </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function() {
            if (jQuery('#searchCanvas').length > 0) {
                new SlimSelect({
                    select: '#searchCanvas'
                });
            }

            leantime.goalCanvasController.setRowHeights();
            leantime.canvasController.setCanvasName('goal');
            leantime.canvasController.initFilterBar();

            @if ($login::userIsAtLeast($roles::$editor))
                leantime.canvasController.initUserDropdown('goalcanvas');
                leantime.canvasController.initStatusDropdown('goalcanvas');
                leantime.canvasController.initRelatesDropdown('goalcanvas');
            @else
                leantime.authController.makeInputReadonly(".maincontentinner");
            @endif

});
</script>

@endsection
