@php
    $states = $tpl->get('states');
    $projectProgress = $tpl->get('projectProgress');
    $sprintBurndown = $tpl->get('sprintBurndown');
    $backlogBurndown = $tpl->get('backlogBurndown');
    $efforts = $tpl->get('efforts');
    $statusLabels = $tpl->get('statusLabels');
    $fullReport = $tpl->get('fullReport');
    $fullReportLatest = $tpl->get('fullReportLatest');
@endphp

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-chart-bar"></span></div>
    <div class="pagetitle">
        <h5>{{ $tpl->escape(session('currentProjectClient') . ' // ' . session('currentProjectName')) }}</h5>
        <h1>{{ $tpl->__('headlines.reports') }}</h1>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        {!! $tpl->displayNotification() !!}

        <div class="tw:grid tw:md:grid-cols-12 tw:gap-4">
            <div class="tw:md:col-span-8">

                <div id="yourToDoContainer">

                            <h5 class="subtitle">{{ $tpl->__('subtitles.summary') }} @if ($fullReportLatest)({{ format($fullReportLatest['date'])->date() }})@endif </h5>
                            <div class="tw:grid tw:md:grid-cols-4 tw:gap-4">
                                <div>
                                    <div class="boxedHighlight">
                                        <span class="headline">{{ $tpl->__('label.planned_hours') }}</span>
                                        <span class="value">{{ ($fullReportLatest !== false && $fullReportLatest['sum_planned_hours'] != null) ? format($fullReportLatest['sum_planned_hours'])->decimal() : 0 }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="boxedHighlight">
                                        <span class="headline">{{ $tpl->__('label.estimated_hours_remaining') }}</span>
                                        <span class="value">{{ ($fullReportLatest !== false && $fullReportLatest['sum_estremaining_hours'] != null) ? format($fullReportLatest['sum_estremaining_hours'])->decimal() : 0 }}</span>
                                    </div>
                                </div>
                                <div>
                                    <div class="boxedHighlight">
                                        <span class="headline">{{ $tpl->__('label.booked_hours') }}</span>
                                        <span class="value">{{ ($fullReportLatest !== false && $fullReportLatest['sum_logged_hours'] != null) ? format($fullReportLatest['sum_logged_hours'])->decimal() : 0 }}</span>
                                    </div>
                                </div>

                                <div>
                                    <div class="boxedHighlight">
                                        <span class="headline">{{ $tpl->__('label.open_todos') }}</span>
                                        <span class="value">{{ ($fullReportLatest !== false) ? format(($fullReportLatest['sum_open_todos'] + $fullReportLatest['sum_progres_todos']))->decimal() : 0 }}</span>
                                    </div>
                                </div>
                            </div>

                            @if ($tpl->get('allSprints') !== false)
                                <h5 class="subtitle">{{ $tpl->__('subtitles.sprint_burndown') }}</h5>
                                <br />
                                <span class="tw:float-left">
                                @if ($tpl->get('allSprints') !== false && count($tpl->get('allSprints')) > 0)
                                    <select data-placeholder="{{ $tpl->__('input.placeholders.filter_by_sprint') }}" title="{{ $tpl->__('input.placeholders.filter_by_sprint') }}" name="sprint" class="mainSprintSelector" onchange="location.href='{{ BASE_URL }}/reports/show?sprint='+jQuery(this).val()" id="sprintSelect">
                                        <option value="">{{ $tpl->__('input.placeholders.filter_by_sprint') }}</option>
                                        @php $dates = ''; @endphp
                                        @foreach ($tpl->get('allSprints') as $sprintRow)
                                            <option value="{{ $sprintRow->id }}"
                                                @if ($tpl->get('currentSprint') !== false && $sprintRow->id == $tpl->get('currentSprint'))
                                                    selected="selected"
                                                    @php $dates = sprintf($tpl->__('label.date_from_date_to'), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date()); @endphp
                                                @endif
                                            >{{ $tpl->escape($sprintRow->name) }}</option>
                                        @endforeach
                                    </select>
                                @endif
                            </span>

                                <div class="tw:float-right">
                                    <div class="btn-group mt-1 mx-auto" role="group">
                                        <x-global::button link="javascript:void(0)" type="secondary" size="sm" id="NumChartButtonSprint" class="active chartButtons">{{ $tpl->__('label.num_tickets') }}</x-global::button>
                                        <x-global::button link="javascript:void(0)" type="secondary" size="sm" id="EffortChartButtonSprint" class="chartButtons">{{ $tpl->__('label.effort') }}</x-global::button>
                                        <x-global::button link="javascript:void(0)" type="secondary" size="sm" id="HourlyChartButtonSprint" class="chartButtons">{{ $tpl->__('label.hours') }}</x-global::button>
                                    </div>
                                </div>

                                <div style="width:100%; height:350px;">
                                    <canvas id="sprintBurndown"></canvas>
                                </div>
                            @endif

                        <div class="clearall"></div>
                        <br />
                        <br />
                        <h5 class="subtitle">{{ $tpl->__('subtitles.cummulative_flow') }}</h5>

                        <div class="tw:float-right">
                            <div class="btn-group mt-1 mx-auto" role="group">
                                <x-global::button link="javascript:void(0)" type="secondary" size="sm" id="NumChartButtonBacklog" class="active backlogChartButtons">{{ $tpl->__('label.num_tickets') }}</x-global::button>
                                <x-global::button link="javascript:void(0)" type="secondary" size="sm" id="EffortChartButtonBacklog" class="backlogChartButtons">{{ $tpl->__('label.effort') }}</x-global::button>
                                <x-global::button link="javascript:void(0)" type="secondary" size="sm" id="HourlyChartButtonBacklog" class="backlogChartButtons">{{ $tpl->__('label.hours') }}</x-global::button>
                            </div>
                        </div>
                        <div style="width:100%; height:350px;">
                            <canvas id="backlogBurndown"></canvas>
                        </div>

                        <div class="clearall"></div>
                        <br />
                        <br />

            </div>

            <div class="tw:md:col-span-4">

                <div id="projectProgressContainer">

                        <h5 class="subtitle">{{ $tpl->__('subtitles.project_progress') }}</h5>

                        <div id="canvas-holder" style="width:100%; height:250px;">
                            <canvas id="chart-area"></canvas>
                        </div>
                        <br /><br />
                </div>
                <div id="milestoneProgressContainer">
                        <h5 class="subtitle">{{ $tpl->__('headline.milestones') }}</h5>
                        <ul class="sortableTicketList">
                            @if (count($tpl->get('milestones')) == 0)
                                <div class="tw:text-center"><br /><h4>{{ $tpl->__('headlines.no_milestones') }}</h4>
                                {{ $tpl->__('text.milestones_help_organize_projects') }}<br /><br /><a href="{{ BASE_URL }}/tickets/roadmap">{{ $tpl->__('links.goto_milestones') }}</a>
                            @endif
                            @foreach ($tpl->get('milestones') as $row)
                                    <li class="ui-state-default" id="milestone_{{ $row->id }}">
                                        <div class="ticketBox fixed">
                                            <strong><a href="{{ BASE_URL }}/tickets/editMilestone/{{ $row->id }}" class="milestoneModal">{{ $tpl->escape($row->headline) }}</a></strong>
                                            <div class="tw:flex tw:justify-between tw:items-center">
                                                <div>
                                                    {{ $tpl->__('label.due') }}
                                                    {{ format($row->editTo)->date($tpl->__('text.no_date_defined')) }}
                                                </div>
                                                <div style="text-align:right">
                                                    {{ sprintf($tpl->__('text.percent_complete'), $row->percentDone) }}
                                                </div>
                                            </div>
                                            <div class="progress">
                                                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $row->percentDone }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $row->percentDone }}%">
                                                    <span class="sr-only">{{ sprintf($tpl->__('text.percent_complete'), $row->percentDone) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                            @endforeach
                        </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

   jQuery(document).ready(function() {

       leantime.dashboardController.prepareHiddenDueDate();
       leantime.ticketsController.initEffortDropdown();
       leantime.ticketsController.initMilestoneDropdown();
       leantime.ticketsController.initStatusDropdown();

       leantime.dashboardController.initProgressChart("chart-area", {{ round($projectProgress['percent']) }}, {{ round((100 - $projectProgress['percent'])) }});

       @if ($sprintBurndown !== false)
           var sprintBurndownChart = leantime.dashboardController.initBurndown([@foreach ($sprintBurndown as $value)'{{ $value['date'] }}',@endforeach], [@foreach ($sprintBurndown as $value)'{{ round($value['plannedNum'], 2) }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualNum'] !== '')'{{ $value['actualNum'] }}',@endif @endforeach ]);
           leantime.dashboardController.initChartButtonClick('HourlyChartButtonSprint', '{{ $tpl->__('label.hours') }}', [@foreach ($sprintBurndown as $value)'{{ $value['plannedHours'] }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualHours'] !== '')'{{ round($value['actualHours']) }}',@endif @endforeach ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('EffortChartButtonSprint', '{{ $tpl->__('label.effort') }}', [@foreach ($sprintBurndown as $value)'{{ $value['plannedEffort'] }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualEffort'] !== '')'{{ $value['actualEffort'] }}',@endif @endforeach ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('NumChartButtonSprint', '{{ $tpl->__('label.num_tickets') }}', [@foreach ($sprintBurndown as $value)'{{ $value['plannedNum'] }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualNum'] !== '')'{{ $value['actualNum'] }}',@endif @endforeach ], sprintBurndownChart);
       @endif

       @if ($backlogBurndown !== false)
           var statusBurnupNum = [];

           statusBurnupNum['open'] = {
               'label': 'Open',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['open']['actualNum'] !== '')'{{ $value['open']['actualNum'] }}',@endif @endforeach]
           };

           statusBurnupNum['progress'] = {
               'label': 'Progress',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['progress']['actualNum'] !== '')'{{ $value['progress']['actualNum'] }}',@endif @endforeach]
           };

           statusBurnupNum['done'] = {
               'label': 'Done',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['done']['actualNum'] !== '')'{{ $value['done']['actualNum'] }}',@endif @endforeach]
           };

           var backlogBurndown = leantime.dashboardController.initBacklogBurndown([@foreach ($backlogBurndown as $value)'{{ $value['date'] }}',@endforeach], statusBurnupNum);


           var statusBurnupEffort = [];

           statusBurnupEffort['open'] = {
               'label': 'Open',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['open']['actualEffort'] !== '')'{{ $value['open']['actualEffort'] }}',@endif @endforeach]
           };

           statusBurnupEffort['progress'] = {
               'label': 'Progress',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['progress']['actualEffort'] !== '')'{{ $value['progress']['actualEffort'] }}',@endif @endforeach]
           };

           statusBurnupEffort['done'] = {
               'label': 'Done',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['done']['actualEffort'] !== '')'{{ $value['done']['actualEffort'] }}',@endif @endforeach]
           };

           var statusBurnupHours = [];

           statusBurnupHours['open'] = {
               'label': 'Open',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['open']['actualHours'] !== '')'{{ $value['open']['actualHours'] }}',@endif @endforeach]
           };

           statusBurnupHours['progress'] = {
               'label': 'Progress',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['progress']['actualHours'] !== '')'{{ $value['progress']['actualHours'] }}',@endif @endforeach]
           };

           statusBurnupHours['done'] = {
               'label': 'Done',
               'data': [@foreach ($backlogBurndown as $value)@if ($value['done']['actualHours'] !== '')'{{ $value['done']['actualHours'] }}',@endif @endforeach]
           };

           leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButtonBacklog', statusBurnupHours, '{{ $tpl->__('label.hours') }}', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('EffortChartButtonBacklog', statusBurnupEffort, '{{ $tpl->__('label.effort') }}', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('NumChartButtonBacklog', statusBurnupNum, '{{ $tpl->__('label.num_tickets') }}', backlogBurndown);
       @endif

    });

</script>
