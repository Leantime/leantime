@extends($layout)

@section('content')

<?php
$states = $tpl->get('states');
$projectProgress = $tpl->get('projectProgress');
$projectProgress = $tpl->get('projectProgress');
$sprintBurndown = $tpl->get('sprintBurndown');
$backlogBurndown = $tpl->get('backlogBurndown');
$efforts = $tpl->get('efforts');
$statusLabels = $tpl->get('statusLabels');
$fullReport = $tpl->get('fullReport');
$fullReportLatest = $tpl->get('fullReportLatest');
?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-chart-bar"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h5><?php $tpl->e(session("currentProjectClient") . " // " . session("currentProjectName")); ?></h5>
                <h1>{{ __("headlines.reports") }}</h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        @displayNotification()

        <div class="row">
            <div class="col-lg-8">

                <div class="row" id="yourToDoContainer">
                    <div class="col-md-12">

                            <h5 class="subtitle"><?=$tpl->__("subtitles.summary")?> <?php if ($fullReportLatest) {
                                ?>(<?=format($fullReportLatest['date'])->date() ?>)<?php
                                                 } ?> </h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <x-global::elements.statistic
                                        stat-title="{{ $tpl->__('label.planned_hours') }}"
                                        stat-value="{{ $fullReportLatest !== false && $fullReportLatest['sum_planned_hours'] !== null ? $fullReportLatest['sum_planned_hours'] : 0 }}"
                                    />
                                </div>
                                <div class="col-md-3">
                                    <x-global::elements.statistic
                                        stat-title="{{ $tpl->__('label.estimated_hours_remaining') }}"
                                        stat-value="{{ $fullReportLatest !== false && $fullReportLatest['sum_estremaining_hours'] !== null ? $fullReportLatest['sum_estremaining_hours'] : 0 }}"
                                    />
                                </div>
                                <div class="col-md-3">
                                    <x-global::elements.statistic
                                        stat-title="{{ $tpl->__('label.booked_hours') }}"
                                        stat-value="{{ $fullReportLatest !== false && $fullReportLatest['sum_logged_hours'] !== null ? $fullReportLatest['sum_logged_hours'] : 0 }}"
                                    />
                                </div>

                                <div class="col-md-3">
                                    <x-global::elements.statistic
                                        stat-title="{{ $tpl->__('label.open_todos') }}"
                                        stat-value="{{ $fullReportLatest !== false ? $fullReportLatest['sum_open_todos'] + $fullReportLatest['sum_progres_todos'] : 0 }}"
                                    />
                                </div>
                            </div>

                            <?php if ($tpl->get('allSprints') !== false) { ?>
                                <h5 class="subtitle"><?=$tpl->__("subtitles.list_burndown")?></h5>
                                <br />
                                <span class="pull-left">
                                <?php  if ($tpl->get('allSprints') !== false && count($tpl->get('allSprints'))  > 0) {?>
                                    <x-global::forms.select
                                    data-placeholder="{!! __('input.placeholders.filter_by_list') !!}"
                                    title="{!! __('input.placeholders.filter_by_list') !!}"
                                    name="sprint"
                                    class="mainSprintSelector"
                                    onchange="location.href='{{ BASE_URL }}/reports/show?sprint='+jQuery(this).val()"
                                    id="sprintSelect"
                                >
                                    <x-global::forms.select.select-option value="">
                                        {!! __('input.placeholders.filter_by_list') !!}
                                    </x-global::forms.select.select-option>

                                    @php $dates = ""; @endphp

                                    @foreach ($tpl->get('allSprints') as $sprintRow)
                                        <x-global::forms.select.select-option :value="$sprintRow->id" :selected="$tpl->get('currentSprint') !== false && $sprintRow->id == $tpl->get('currentSprint')">
                                            {!! $tpl->escape($sprintRow->name) !!}
                                        </x-global::forms.select.select-option>

                                        @if ($tpl->get("currentSprint") !== false && $sprintRow->id == $tpl->get("currentSprint"))
                                            @php
                                                $dates = sprintf(
                                                    __('label.date_from_date_to'),
                                                    format($sprintRow->startDate)->date(),
                                                    format($sprintRow->endDate)->date()
                                                );
                                            @endphp
                                        @endif
                                    @endforeach
                                </x-global::forms.select>

                                <?php } ?>
                            </span>

                                <div class="pull-right">
                                    <div class="btn-group mt-1 mx-auto" role="group">
                                        <a href="javascript:void(0)" id="NumChartButtonSprint" class="btn btn-sm btn-secondary active chartButtons"><?=$tpl->__("label.num_tickets")?></a>
                                        <a href="javascript:void(0)" id="EffortChartButtonSprint" class="btn btn-sm btn-secondary chartButtons"><?=$tpl->__("label.effort")?></a>
                                        <a href="javascript:void(0)" id="HourlyChartButtonSprint" class="btn btn-sm btn-secondary chartButtons"><?=$tpl->__("label.hours")?></a>
                                    </div>

                                </div>

                                <div style="width:100%; height:350px;">
                                    <canvas id="sprintBurndown"></canvas>
                                </div>


                            <?php } ?>

                        <div class="clearall"></div>
                        <br />
                        <br />
                        <h5 class="subtitle"><?=$tpl->__("subtitles.cummulative_flow")?></h5>

                        <div class="pull-right">
                            <div class="btn-group mt-1 mx-auto" role="group">
                                <a href="javascript:void(0)" id="NumChartButtonBacklog" class="btn btn-sm btn-secondary active backlogChartButtons"><?=$tpl->__("label.num_tickets")?></a>
                                <a href="javascript:void(0)" id="EffortChartButtonBacklog" class="btn btn-sm btn-secondary backlogChartButtons"><?=$tpl->__("label.effort")?></a>
                                <a href="javascript:void(0)" id="HourlyChartButtonBacklog" class="btn btn-sm btn-secondary backlogChartButtons"><?=$tpl->__("label.hours")?></a>
                            </div>

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

                        <h5 class="subtitle"><?=$tpl->__("subtitles.project_progress")?></h5>

                        <div id="canvas-holder" style="width:100%; height:250px;">
                            <canvas id="chart-area" ></canvas>
                        </div>
                        <br /><br />
                    </div>
                </div>
                <div class="row" id="milestoneProgressContainer">
                    <div class="col-md-12">
                        <h5 class="subtitle"><?=$tpl->__("headline.milestones") ?></h5>

                        @if (count($milestones) == 0)
                            <div class="center">
                                <br />
                                <h4>{{ __('headlines.no_milestones') }}</h4>
                                {{ __('text.milestones_help_organize_projects') }}
                                <br /><br />
                                <a href="{{ BASE_URL }}/tickets/roadmap">{!! __('links.goto_milestones') !!}</a>
                            </div>
                        @endif

                        @foreach ($milestones as $row)
                            @if ($row->percentDone >= 100 && new \DateTime($row->editTo) < new \DateTime())
                                @break
                            @endif
                            <x-tickets::cards.milestone-card
                                :milestone="$row"
                            />
                        @endforeach

                    </div>
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

       leantime.dashboardController.initProgressChart("chart-area", <?php echo round($projectProgress['percent']); ?>, <?php echo round((100 - $projectProgress['percent'])); ?>);

       <?php if ($sprintBurndown !== false) { ?>
           var sprintBurndownChart = leantime.dashboardController.initBurndown([<?php foreach ($sprintBurndown as $value) {
                echo "'" . $value['date'] . "',";
                                                                                } ?>], [<?php foreach ($sprintBurndown as $value) {
    echo "'" . round($value['plannedNum'], 2) . "',";
                                                                                } ?>], [ <?php foreach ($sprintBurndown as $value) {
    if ($value['actualNum'] !== '') {
        echo "'" . $value['actualNum'] . "',";
    }
                                                                                }  ?> ]);
           leantime.dashboardController.initChartButtonClick('HourlyChartButtonSprint', '<?=$tpl->__('label.hours') ?>', [<?php foreach ($sprintBurndown as $value) {
                echo "'" . $value['plannedHours'] . "',";
                                                                                         } ?>], [ <?php foreach ($sprintBurndown as $value) {
    if ($value['actualHours'] !== '') {
        echo "'" . round($value['actualHours']) . "',";
    }
                                                                                         }  ?> ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('EffortChartButtonSprint', '<?=$tpl->__('label.effort') ?>', [<?php foreach ($sprintBurndown as $value) {
                echo "'" . $value['plannedEffort'] . "',";
                                                                                         } ?>], [ <?php foreach ($sprintBurndown as $value) {
    if ($value['actualEffort'] !== '') {
        echo "'" . $value['actualEffort'] . "',";
    }
                                                                                         }  ?> ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('NumChartButtonSprint', '<?=$tpl->__('label.num_tickets') ?>', [<?php foreach ($sprintBurndown as $value) {
                echo "'" . $value['plannedNum'] . "',";
                                                                                      } ?>], [ <?php foreach ($sprintBurndown as $value) {
    if ($value['actualNum'] !== '') {
        echo "'" . $value['actualNum'] . "',";
    }
                                                                                      }  ?> ], sprintBurndownChart);

       <?php } ?>

       <?php if ($backlogBurndown !== false) { ?>
           var statusBurnupNum = [];

            <?php

                echo "
                statusBurnupNum['open'] = {
                    'label': 'Open',
                    'data':
                    [";
            foreach ($backlogBurndown as $value) {
                if ($value['open']['actualNum'] !== '') {
                    echo "'" . $value['open']['actualNum'] . "',";
                }
            }
            echo"]};";

            echo "
               statusBurnupNum['progress'] = {
                            'label': 'Progress',
                            'data':
                            [";
            foreach ($backlogBurndown as $value) {
                if ($value['progress']['actualNum'] !== '') {
                    echo "'" . $value['progress']['actualNum'] . "',";
                }
            }
            echo"]};";

            echo "
               statusBurnupNum['done'] = {
                                    'label': 'Done',
                                    'data':
                                    [";
            foreach ($backlogBurndown as $value) {
                if ($value['done']['actualNum'] !== '') {
                    echo "'" . $value['done']['actualNum'] . "',";
                }
            }
            echo"]};";

            ?>

           var backlogBurndown = leantime.dashboardController.initBacklogBurndown([<?php foreach ($backlogBurndown as $value) {
                echo "'" . $value['date'] . "',";
                                                                                   } ?>], statusBurnupNum);


           var statusBurnupEffort = [];

            <?php

            echo "
           statusBurnupEffort['open'] = {
                        'label': 'Open',
                        'data':
                        [";
            foreach ($backlogBurndown as $value) {
                if ($value['open']['actualEffort'] !== '') {
                    echo "'" . $value['open']['actualEffort'] . "',";
                }
            }
            echo"]};";

            echo "
           statusBurnupEffort['progress'] = {
                                'label': 'Progress',
                                'data':
                                [";
            foreach ($backlogBurndown as $value) {
                if ($value['progress']['actualEffort'] !== '') {
                    echo "'" . $value['progress']['actualEffort'] . "',";
                }
            }
            echo"]};";

            echo "
           statusBurnupEffort['done'] = {
                                        'label': 'Done',
                                        'data':
                                        [";
            foreach ($backlogBurndown as $value) {
                if ($value['done']['actualEffort'] !== '') {
                    echo "'" . $value['done']['actualEffort'] . "',";
                }
            }
            echo"]};";

            ?>

       var statusBurnupHours = [];

            <?php

            echo "
       statusBurnupHours['open'] = {
                        'label': 'Open',
                        'data':
                        [";
            foreach ($backlogBurndown as $value) {
                if ($value['open']['actualHours'] !== '') {
                    echo "'" . $value['open']['actualHours'] . "',";
                }
            }
            echo"]};";

            echo "
       statusBurnupHours['progress'] = {
                                'label': 'Progress',
                                'data':
                                [";
            foreach ($backlogBurndown as $value) {
                if ($value['progress']['actualHours'] !== '') {
                    echo "'" . $value['progress']['actualHours'] . "',";
                }
            }
            echo"]};";

            echo "
       statusBurnupHours['done'] = {
                                        'label': 'Done',
                                        'data':
                                        [";
            foreach ($backlogBurndown as $value) {
                if ($value['done']['actualHours'] !== '') {
                    echo "'" . $value['done']['actualHours'] . "',";
                }
            }
            echo"]};";

            ?>

           leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButtonBacklog', statusBurnupHours, '<?=$tpl->__('label.hours') ?>', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('EffortChartButtonBacklog', statusBurnupEffort, '<?=$tpl->__('label.effort') ?>', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('NumChartButtonBacklog', statusBurnupNum, '<?=$tpl->__('label.num_tickets') ?>', backlogBurndown);

       <?php } ?>


    });

</script>

@endsection
