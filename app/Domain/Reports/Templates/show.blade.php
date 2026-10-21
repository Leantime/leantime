@extends($layout)

@section('content')

<x-global::layout.page-header icon="fa fa-chart-bar" :eyebrow="session('currentProjectClient') . ' // ' . session('currentProjectName')">
    {!! __('headlines.reports') !!}
</x-global::layout.page-header>

<div class="maincontent">

    {{-- Same nav band as the status report tab (see project.blade.php): on the
         gradient between the two cards, not a bare <ul class="tabs-list"> — that
         class has no CSS, so it rendered with disc bullets inside the card. --}}
    <div class="lt-tabs lt-tabs--floating lt-tabs--links hideOnPrint">
        <nav class="lt-tabs-group" aria-label="{{ __('label.status_report_tab') }} / {{ __('label.delivery_metrics_tab') }}">
            <ul>
                <li><a href="{{ BASE_URL }}/reports/project" preload="mouseover">{{ __('label.status_report_tab') }}</a></li>
                <li class="active"><a href="{{ BASE_URL }}/reports/show">{{ __('label.delivery_metrics_tab') }}</a></li>
            </ul>
        </nav>
    </div>

    <x-global::layout.content-box>

        {!! $tpl->displayNotification() !!}

        <div class="row">
            <div class="col-lg-8">

                <div class="row" id="yourToDoContainer">
                    <div class="col-md-12">

                            <x-global::elements.section-header as="h5">{!! __('subtitles.summary') !!} @if ($fullReportLatest)({{ format($fullReportLatest['date'])->date() }})@endif </x-global::elements.section-header>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="boxedHighlight">

                                        <span class="headline">{!! __('label.planned_hours') !!}</span>
                                        <span class="value">@if ($fullReportLatest !== false && $fullReportLatest['sum_planned_hours'] != null){{ format($fullReportLatest['sum_planned_hours'])->decimal() }}@else{{ 0 }}@endif</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="boxedHighlight">


                                        <span class="headline">{!! __('label.estimated_hours_remaining') !!}</span>
                                        <span class="value">@if ($fullReportLatest !== false && $fullReportLatest['sum_estremaining_hours'] != null){{ format($fullReportLatest['sum_estremaining_hours'])->decimal() }}@else{{ 0 }}@endif</span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="boxedHighlight">


                                        <span class="headline">{!! __('label.booked_hours') !!}</span>
                                        <span class="value">@if ($fullReportLatest !== false && $fullReportLatest['sum_logged_hours'] != null){{ format($fullReportLatest['sum_logged_hours'])->decimal() }}@else{{ 0 }}@endif</span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="boxedHighlight">
                                        <span class="headline">{!! __('label.open_todos') !!}</span>
                                        <span class="value">
                                            {{-- Open To-Dos is a COUNT of tickets (SUM of CASE WHEN
                                                 status = X THEN 1 in the repo), so it's inherently
                                                 an integer. ->decimal() rendered "1" as "1.00" —
                                                 read as a broken chart value in the audit. --}}
                                            @if ($fullReportLatest !== false)
                                                {{ (int) ($fullReportLatest['sum_open_todos'] + $fullReportLatest['sum_progres_todos']) }}
                                            @else
                                                {{ 0 }}
                                            @endif
                                        </span>
                                    </div>
                                </div>


                            </div>

                            {{-- Hide the whole Sprint Burndown section when the project has
                                 no sprints. Previously the outer guard was just `!== false`, so an
                                 empty array (project without sprints) rendered the title + toggle
                                 buttons + empty canvas with no chart underneath — read as broken
                                 in the audit. --}}
                            @if ($allSprints !== false && count($allSprints) > 0)
                                <x-global::elements.section-header as="h5">{!! __('subtitles.sprint_burndown') !!}</x-global::elements.section-header>
                                <br />
                                <span class="pull-left">
                                @if (true)
                                    <x-global::forms.select data-placeholder="{{ __('input.placeholders.filter_by_sprint') }}" title="{{ __('input.placeholders.filter_by_sprint') }}" name="sprint" class="mainSprintSelector" onchange="location.href='{{ BASE_URL }}/reports/show?sprint='+jQuery(this).val()" id="sprintSelect">

                                        <option value="" >{!! __('input.placeholders.filter_by_sprint') !!}</option>
                                        @php $dates = ''; @endphp
                                        @foreach ($allSprints as $sprintRow)
                                            <option value="{{ $sprintRow->id }}"
                                                @if ($currentSprint !== false && $sprintRow->id == $currentSprint)
                                                    selected="selected"
                                                    @php $dates = sprintf(__('label.date_from_date_to'), format($sprintRow->startDate)->date(), format($sprintRow->endDate)->date()); @endphp
                                                @endif
                                            >{{ $tpl->escape($sprintRow->name) }}</option>
                                        @endforeach
                                    </x-global::forms.select>
                                @endif
                            </span>

                                <div class="pull-right">
                                    <x-global::forms.button-group segmented :label="__('label.chart_metric')">
                                        <x-global::forms.button inputType="button" aria-pressed="true" id="NumChartButtonSprint" class="active chartButtons" scale="s">{!! __('label.num_tickets') !!}</x-global::forms.button>
                                        <x-global::forms.button inputType="button" aria-pressed="false" id="EffortChartButtonSprint" class="chartButtons" scale="s">{!! __('label.effort') !!}</x-global::forms.button>
                                        <x-global::forms.button inputType="button" aria-pressed="false" id="HourlyChartButtonSprint" class="chartButtons" scale="s">{!! __('label.hours') !!}</x-global::forms.button>
                                    </x-global::forms.button-group>

                                </div>

                                <div style="width:100%; height:350px;">
                                    <canvas id="sprintBurndown"></canvas>
                                </div>


                            @endif

                        <div class="clearall"></div>
                        <br />
                        <br />
                        <x-global::elements.section-header as="h5">{!! __('subtitles.cummulative_flow') !!}</x-global::elements.section-header>

                        <div class="pull-right">
                            <x-global::forms.button-group segmented :label="__('label.chart_metric')">
                                <x-global::forms.button inputType="button" aria-pressed="true" id="NumChartButtonBacklog" class="active backlogChartButtons" scale="s">{!! __('label.num_tickets') !!}</x-global::forms.button>
                                <x-global::forms.button inputType="button" aria-pressed="false" id="EffortChartButtonBacklog" class="backlogChartButtons" scale="s">{!! __('label.effort') !!}</x-global::forms.button>
                                <x-global::forms.button inputType="button" aria-pressed="false" id="HourlyChartButtonBacklog" class="backlogChartButtons" scale="s">{!! __('label.hours') !!}</x-global::forms.button>
                            </x-global::forms.button-group>

                        </div>
                        <div style="width:100%; height:350px;">
                            <canvas id="backlogBurndown"></canvas>
                        </div>

                        <div class="clearall"></div>
                        <br />
                        <br />
                    </div>
                </div>

            </div>

            <div class="col-lg-4">

                <div class="row" id="projectProgressContainer">
                    <div class="col-md-12">

                        <x-global::elements.section-header as="h5">{!! __('subtitles.project_progress') !!}</x-global::elements.section-header>

                        <div id="canvas-holder" style="width:100%; height:250px;">
                            <canvas id="chart-area" ></canvas>
                        </div>
                        <br /><br />
                    </div>
                </div>
                <div class="row" id="milestoneProgressContainer">
                    <div class="col-md-12">
                        <x-global::elements.section-header as="h5">{!! __('headline.milestones') !!}</x-global::elements.section-header>
                        <ul class="sortableTicketList" >
                            @if (count($milestones) == 0)
                                <div class='center'><br /><h4>{!! __('headlines.no_milestones') !!}</h4>
                                {!! __('text.milestones_help_organize_projects') !!}<br /><br /><a href="{{ BASE_URL }}/tickets/roadmap">{!! __('links.goto_milestones') !!}</a>
                                </div>
                            @endif
                            @foreach ($milestones as $row)
                                    <li class="ui-state-default" id="milestone_{{ $row->id }}" >
                                        <x-global::elements.card compact class="ticketBox fixed">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <strong><a href="{{ BASE_URL }}/tickets/editMilestone/{{ $row->id }}" class="milestoneModal">{{ $tpl->escape($row->headline) }}</a></strong>
                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="col-md-7">
                                                    {!! __('label.due') !!}
                                                    {{ format($row->editTo)->date(__('text.no_date_defined')) }}
                                                </div>
                                                <div class="col-md-5" style="text-align:right">
                                                    {!! sprintf(__('text.percent_complete'), $row->percentDone) !!}
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="{{ $row->percentDone }}" aria-valuemin="0" aria-valuemax="100" style="width: {{ $row->percentDone }}%">
                                                            <span class="sr-only">{!! sprintf(__('text.percent_complete'), $row->percentDone) !!}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </x-global::elements.card>
                                    </li>
                            @endforeach

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </x-global::layout.content-box>
</div>

@once
@push('scripts')
<script type="text/javascript">

   jQuery(document).ready(function() {

       leantime.dashboardController.prepareHiddenDueDate();

       leantime.dashboardController.initProgressChart("chart-area", {{ round($projectProgress['percent']) }}, {{ round((100 - $projectProgress['percent'])) }});

       @if ($sprintBurndown !== false)
           var sprintBurndownChart = leantime.dashboardController.initBurndown([@foreach ($sprintBurndown as $value)'{{ $value['date'] }}',@endforeach], [@foreach ($sprintBurndown as $value)'{{ round($value['plannedNum'], 2) }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualNum'] !== '')'{{ $value['actualNum'] }}',@endif @endforeach ]);
           leantime.dashboardController.initChartButtonClick('HourlyChartButtonSprint', '{!! __('label.hours') !!}', [@foreach ($sprintBurndown as $value)'{{ $value['plannedHours'] }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualHours'] !== '')'{{ round($value['actualHours']) }}',@endif @endforeach ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('EffortChartButtonSprint', '{!! __('label.effort') !!}', [@foreach ($sprintBurndown as $value)'{{ $value['plannedEffort'] }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualEffort'] !== '')'{{ $value['actualEffort'] }}',@endif @endforeach ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('NumChartButtonSprint', '{!! __('label.num_tickets') !!}', [@foreach ($sprintBurndown as $value)'{{ $value['plannedNum'] }}',@endforeach], [ @foreach ($sprintBurndown as $value)@if ($value['actualNum'] !== '')'{{ $value['actualNum'] }}',@endif @endforeach ], sprintBurndownChart);

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

           leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButtonBacklog', statusBurnupHours, '{!! __('label.hours') !!}', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('EffortChartButtonBacklog', statusBurnupEffort, '{!! __('label.effort') !!}', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('NumChartButtonBacklog', statusBurnupNum, '{!! __('label.num_tickets') !!}', backlogBurndown);

       @endif


    });

</script>
@endpush
@endonce

@endsection
