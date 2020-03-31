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
    var_dump($fullReportLatest);
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

                            <h5 class="subtitle"><?=$this->__("subtitles.summary")?></h5>
                            <div class="row">
                                <div class="col-md-3">
                                    Planned Hours
                                    <?=$fullReportLatest['sum_planned_hours']; ?>
                                </div>
                                <div class="col-md-3">
                                    Estimated Remaining Hours
                                    <?=$fullReportLatest['sum_estremaining_hours']; ?>
                                </div>
                                <div class="col-md-3">
                                    Logged Hours
                                    <?=$fullReportLatest['sum_logged_hours']; ?>
                                </div>

                                <div class="col-md-3">
                                    Open To-Dos
                                    <?=$fullReportLatest['sum_open_todos']; ?>
                                </div>


                            </div>


                            <?php if($allSprints !== false) { ?>
                                <h5 class="subtitle"><?=$this->__("subtitles.sprint_burndown")?></h5>
                                <br />
                                <span class="currentSprint pull-left">
                                <?php  if($this->get('allSprints') !== false && count($this->get('allSprints'))  > 0) {?>
                                    <select data-placeholder="<?=$this->__("input.placeholders.filter_by_sprint") ?>" title="<?=$this->__("input.placeholders.filter_by_sprint") ?>" name="sprint" class="mainSprintSelector" onchange="location.href='<?=BASE_URL ?>/reports/show?sprint='+jQuery(this).val()" id="sprintSelect">

                                        <option value="" ><?=$this->__("input.placeholders.filter_by_sprint") ?></option>
                                        <?php
                                        $dates = "";
                                        foreach($this->get('allSprints') as $sprintRow){ 	?>

                                            <?php echo"<option value='".$sprintRow->id."'";

                                            if($this->get("currentSprint") !== false && $sprintRow->id == $this->get("currentSprint")) {
                                                echo " selected='selected' ";

                                                $dates = sprintf($this->__("label.date_from_date_to"), date($this->__("language.dateformat"), strtotime($sprintRow->startDate)), date($this->__("language.dateformat"), strtotime($sprintRow->endDate)));
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
                        <h5 class="subtitle">Backlog Burndown</h5>

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
                                $percent = 0;

                                if($row->allTicketsEffort != 0 ) {
                                    $percent = round(($row->doneTicketsEffort/$row->allTicketsEffort)*100);
                                }
                                if($row->editTo == "0000-00-00 00:00:00") {
                                    $date = $this->__("text.no_date_defined");
                                }else {
                                    $date = new DateTime($row->editTo);
                                    $date= $date->format($this->__("language.dateformat"));
                                }
                                if($percent < 100 || $date >= new DateTime()) {
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
                                                    <?=sprintf($this->__("text.percent_complete"), $percent)?>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $percent; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $percent; ?>%">
                                                            <span class="sr-only"><?=sprintf($this->__("text.percent_complete"), $percent)?></span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                <?php }
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

       leantime.dashboardController.initProgressChart(<?php echo round($projectProgress['percent']); ?>, <?php echo round((100 - $projectProgress['percent'])); ?>);

       <?php if($sprintBurndown !== false){ ?>

           var sprintBurndownChart = leantime.dashboardController.initBurndown([<?php foreach($sprintBurndown as $value) echo "'".$value['date']."',"; ?>], [<?php foreach($sprintBurndown as $value) echo "'".round($value['plannedNum'], 2)."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ]);
           leantime.dashboardController.initChartButtonClick('HourlyChartButtonSprint', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedHours']."',"; ?>], [ <?php foreach($sprintBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; };  ?> ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('EffortChartButtonSprint', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedEffort']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; };  ?> ], sprintBurndownChart);
           leantime.dashboardController.initChartButtonClick('NumChartButtonSprint', [<?php foreach($sprintBurndown as $value) echo "'".$value['plannedNum']."',"; ?>], [ <?php foreach($sprintBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ], sprintBurndownChart);

       <?php } ?>

       <?php if($backlogBurndown !== false){ ?>

           var backlogBurndown = leantime.dashboardController.initBacklogBurndown([<?php foreach($backlogBurndown as $value) echo "'".$value['date']."',"; ?>], [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ]);

           leantime.dashboardController.initBacklogChartButtonClick('HourlyChartButtonBacklog', [ <?php foreach($backlogBurndown as $value) { if($value['actualHours'] !== '') echo "'".round($value['actualHours'])."',"; };  ?> ], backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('EffortChartButtonBacklog', [ <?php foreach($backlogBurndown as $value)  { if($value['actualEffort'] !== '') echo "'".$value['actualEffort']."',"; };  ?> ], backlogBurndown);
           leantime.dashboardController.initBacklogChartButtonClick('NumChartButtonBacklog', [ <?php foreach($backlogBurndown as $value)  { if($value['actualNum'] !== '') echo "'".$value['actualNum']."',"; };  ?> ], backlogBurndown);

       <?php } ?>

       <?php if(isset($_SESSION['userdata']['settings']["modals"]["dashboard"]) === false || $_SESSION['userdata']['settings']["modals"]["dashboard"] == 0){  ?>

           leantime.helperController.showHelperModal("dashboard", 500, 700);

       <?php
            //Only show once per session
            $_SESSION['userdata']['settings']["modals"]["dashboard"] = 1;
       } ?>

    });

</script>