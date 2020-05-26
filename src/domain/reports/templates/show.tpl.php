<?php
	$states = $this->get('states');
    $projectProgress = $this->get('projectProgress');
    $projectProgress = $this->get('projectProgress');
    $sprintBurndown = $this->get('sprintBurndown');
    $backlogBurndown = $this->get('backlogBurndown');
    $efforts = $this->get('efforts');
    $statusLabels = $this->get('statusLabels');
    $fullReport = $this->get('fullReport');
    $fullReportLatest = $this->get('fullReportLatest');


?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-chart-bar"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-8">
                <h5><?php $this->e($_SESSION["currentProjectClient"]." // ". $_SESSION['currentProjectName']); ?></h5>
                <h1><?php echo $this->__("headlines.reports"); ?></h1>
            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-lg-8">

                <div class="row" id="yourToDoContainer">
                    <div class="col-md-12">

                            <h5 class="subtitle"><?=$this->__("subtitles.summary")?> <?php if($fullReportLatest!= false){?>(<?=$this->getFormattedDateString($fullReportLatest['date']) ?>)<?php } ?> </h5>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="boxedHighlight">

                                        <span class="headline"><?=$this->__("label.planned_hours")?></span>
                                        <span class="value"><?php if($fullReportLatest !== false && $fullReportLatest['sum_planned_hours'] != null) echo $fullReportLatest['sum_planned_hours']; else echo 0; ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="boxedHighlight">


                                        <span class="headline"><?=$this->__("label.estimated_hours_remaining")?></span>
                                        <span class="value"><?php if($fullReportLatest !== false && $fullReportLatest['sum_estremaining_hours'] != null) echo $fullReportLatest['sum_estremaining_hours']; else echo 0; ?></span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="boxedHighlight">


                                        <span class="headline"><?=$this->__("label.booked_hours")?></span>
                                        <span class="value"><?php if($fullReportLatest !== false && $fullReportLatest['sum_logged_hours'] != null) echo $fullReportLatest['sum_logged_hours']; else echo 0; ?></span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="boxedHighlight">
                                        <span class="headline"><?=$this->__("label.open_todos")?></span>
                                        <span class="value">
                                            <?php
                                                if($fullReportLatest !== false) {
                                                    echo($fullReportLatest['sum_open_todos'] + $fullReportLatest['sum_progres_todos']);
                                                }else{
                                                    echo 0;
                                                }
                                                ?></span>
                                    </div>
                                </div>


                            </div>

                            <?php if($this->get('allSprints') !== false) { ?>
                                <h5 class="subtitle"><?=$this->__("subtitles.sprint_burndown")?></h5>
                                <br />
                                <span class="pull-left">
                                <?php  if($this->get('allSprints') !== false && count($this->get('allSprints'))  > 0) {?>
                                    <select data-placeholder="<?=$this->__("input.placeholders.filter_by_sprint") ?>" title="<?=$this->__("input.placeholders.filter_by_sprint") ?>" name="sprint" class="mainSprintSelector" onchange="location.href='<?=BASE_URL ?>/reports/show?sprint='+jQuery(this).val()" id="sprintSelect">

                                        <option value="" ><?=$this->__("input.placeholders.filter_by_sprint") ?></option>
                                        <?php
                                        $dates = "";
                                        foreach($this->get('allSprints') as $sprintRow){ 	?>

                                            <?php echo"<option value='".$sprintRow->id."'";

                                            if($this->get("currentSprint") !== false && $sprintRow->id == $this->get("currentSprint")) {
                                                echo " selected='selected' ";

                                                $dates = sprintf($this->__("label.date_from_date_to"), $this->getFormattedDateString($sprintRow->startDate), $this->getFormattedDateString($sprintRow->endDate));
                                            }
                                            echo ">";
                                            $this->e($sprintRow->name);
                                            echo "</option>";
                                            ?>

                                        <?php } 	?>
                                    </select>
                                <?php } ?>
                            </span>

                                <div class="pull-right">
                                    <div class="btn-group mt-1 mx-auto" role="group">
                                        <a href="javascript:void(0)" id="NumChartButtonSprint" class="btn btn-sm btn-secondary active chartButtons"><?=$this->__("label.num_tickets")?></a>
                                        <a href="javascript:void(0)" id="EffortChartButtonSprint" class="btn btn-sm btn-secondary chartButtons"><?=$this->__("label.effort")?></a>
                                        <a href="javascript:void(0)" id="HourlyChartButtonSprint" class="btn btn-sm btn-secondary chartButtons"><?=$this->__("label.hours")?></a>
                                    </div>

                                </div>

                                <div style="width:100%; height:350px;">
                                    <canvas id="sprintBurndown"></canvas>
                                </div>


                            <?php } ?>

                        <div class="clearall"></div>
                        <br />
                        <br />
                        <h5 class="subtitle"><?=$this->__("subtitles.cummulative_flow")?></h5>

                        <div class="pull-right">
                            <div class="btn-group mt-1 mx-auto" role="group">
                                <a href="javascript:void(0)" id="NumChartButtonBacklog" class="btn btn-sm btn-secondary active backlogChartButtons"><?=$this->__("label.num_tickets")?></a>
                                <a href="javascript:void(0)" id="EffortChartButtonBacklog" class="btn btn-sm btn-secondary backlogChartButtons"><?=$this->__("label.effort")?></a>
                                <a href="javascript:void(0)" id="HourlyChartButtonBacklog" class="btn btn-sm btn-secondary backlogChartButtons"><?=$this->__("label.hours")?></a>
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

                        <h5 class="subtitle"><?=$this->__("subtitles.project_progress")?></h5>

                        <div id="canvas-holder" style="width:100%; height:250px;">
                            <canvas id="chart-area" ></canvas>
                        </div>
                        <br /><br />
                    </div>
                </div>
                <div class="row" id="milestoneProgressContainer">
                    <div class="col-md-12">
                        <h5 class="subtitle"><?=$this->__("headline.milestones") ?></h5>
                        <ul class="sortableTicketList" >
                            <?php
                            if(count($this->get('milestones')) == 0){
                                echo"<div class='center'><br /><h4>".$this->__("headlines.no_milestones")."</h4>
                                ".$this->__("text.milestones_help_organize_projects")."<br /><br /><a href='".BASE_URL."/tickets/roadmap'>".$this->__("links.goto_milestones")."</a>";
                            }
                            ?>
                            <?php foreach($this->get('milestones') as $row){

                                if($row->editTo == "0000-00-00 00:00:00") {
                                    $date = $this->__("text.no_date_defined");
                                }else {
                                    $date = new DateTime($row->editTo);
                                    $date= $date->format($this->__("language.dateformat"));
                                }

                                    ?>
                                    <li class="ui-state-default" id="milestone_<?php echo $row->id; ?>" >
                                        <div class="ticketBox fixed">

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <strong><a href="<?=BASE_URL ?>/tickets/editMilestone/<?php echo $row->id;?>" class="milestoneModal"><?php $this->e($row->headline); ?></a></strong>
                                                </div>
                                            </div>
                                            <div class="row">

                                                <div class="col-md-7">
                                                    <?=$this->__("label.due") ?>
                                                    <?php echo $date; ?>
                                                </div>
                                                <div class="col-md-5" style="text-align:right">
                                                    <?=sprintf($this->__("text.percent_complete"), $row->percentDone)?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row->percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row->percentDone; ?>%">
                                                            <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $row->percentDone)?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php
                            } ?>

                        </ul>
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

       <?php if($sprintBurndown !== false){ ?>

           var sprintBurndownChart = leantime.dashboardController.initBurndown([<?php foreach($sprintBurndown as $value) echo "'".$value['date']."',"; ?>], [<?php foreach($sprintBurndown as $value) echo "'".round($value['plannedNum'], 2)."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ]);
           leantime.dashboardController.initChartButtonClick('HourlyChartButtonSprint', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedHours']."',"; ?>], [ <?php foreach($sprintBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; };  ?> ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('EffortChartButtonSprint', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedEffort']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; };  ?> ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('NumChartButtonSprint', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedNum']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ], sprintBurndownChart);

       <?php } ?>

       <?php if($backlogBurndown !== false){ ?>

           var statusBurnupNum = [];

           <?php

                echo "
                statusBurnupNum['open'] = {
                    'label': 'Open',
                    'data':
                    [";
                        foreach($backlogBurndown as $value)  { if($value['open']['actualNum'] !== '') echo "'".$value['open']['actualNum']."',"; };
                echo"]};";

               echo "
               statusBurnupNum['progress'] = {
                            'label': 'Progress',
                            'data':
                            [";
               foreach($backlogBurndown as $value)  { if($value['progress']['actualNum'] !== '') echo "'".$value['progress']['actualNum']."',"; };
               echo"]};";

               echo "
               statusBurnupNum['done'] = {
                                    'label': 'Done',
                                    'data':
                                    [";
               foreach($backlogBurndown as $value)  { if($value['done']['actualNum'] !== '') echo "'".$value['done']['actualNum']."',"; };
               echo"]};";

           ?>

           var backlogBurndown = leantime.dashboardController.initBacklogBurndown([<?php foreach($backlogBurndown as $value) echo "'".$value['date']."',"; ?>], statusBurnupNum);


           var statusBurnupEffort = [];

           <?php

           echo " 
           statusBurnupEffort['open'] = {
                        'label': 'Open',
                        'data':
                        [";
           foreach($backlogBurndown as $value)  { if($value['open']['actualEffort'] !== '') echo "'".$value['open']['actualEffort']."',"; };
           echo"]};";

           echo " 
           statusBurnupEffort['progress'] = {
                                'label': 'Progress',
                                'data':
                                [";
           foreach($backlogBurndown as $value)  { if($value['progress']['actualEffort'] !== '') echo "'".$value['progress']['actualEffort']."',"; };
           echo"]};";

           echo " 
           statusBurnupEffort['done'] = {
                                        'label': 'Done',
                                        'data':
                                        [";
           foreach($backlogBurndown as $value)  { if($value['done']['actualEffort'] !== '') echo "'".$value['done']['actualEffort']."',"; };
           echo"]};";

           ?>

       var statusBurnupHours = [];

       <?php

       echo " 
       statusBurnupHours['open'] = {
                        'label': 'Open',
                        'data':
                        [";
       foreach($backlogBurndown as $value)  { if($value['open']['actualHours'] !== '') echo "'".$value['open']['actualHours']."',"; };
       echo"]};";

       echo " 
       statusBurnupHours['progress'] = {
                                'label': 'Progress',
                                'data':
                                [";
       foreach($backlogBurndown as $value)  { if($value['progress']['actualHours'] !== '') echo "'".$value['progress']['actualHours']."',"; };
       echo"]};";

       echo " 
       statusBurnupHours['done'] = {
                                        'label': 'Done',
                                        'data':
                                        [";
       foreach($backlogBurndown as $value)  { if($value['done']['actualHours'] !== '') echo "'".$value['done']['actualHours']."',"; };
       echo"]};";

       ?>

           leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButtonBacklog', statusBurnupHours, '<?=$this->__('label.hours') ?>', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('EffortChartButtonBacklog', statusBurnupEffort, '<?=$this->__('label.effort') ?>', backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('NumChartButtonBacklog', statusBurnupNum, '<?=$this->__('label.num_tickets') ?>', backlogBurndown);

       <?php } ?>


    });

</script>