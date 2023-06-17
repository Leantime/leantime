<?php

defined('RESTRICTED') or die('Restricted access');
$allProjects = $this->get("allProjects");
$projectStatusLabels = $this->get("projectStatusLabels");


//All states >0 (<1 is archive)
$numberofColumns = count($this->get('projectStatusLabels'));
$size = floor((100 / $numberofColumns) * 100) / 100;

?>

<div class="pageheader">
    <div class="pageicon"><i class="fa-solid fa-layer-group"></i></div>
    <div class="pagetitle">
        <h5><?php $this->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']); ?></h5>

        <h1>Program: <?=$_SESSION['currentProjectName'] ?>
        </h1>
    </div>
</div><!--pageheader-->

<div class="maincontent">
    <div class="maincontentinner">
        <?php echo $this->displayNotification(); ?>

        <div class="row">
            <div class="col-md-4">
                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>

                    <a href="<?=BASE_URL ?>/projects/newProject?parent=<?=$_SESSION['currentProject']?>" class="btn btn-primary"><span
                                class="far fa-plus"></span> Add Project</a>

                    <?php }
                ?>
            </div>

            <div class="col-md-4 center">

            </div>
            <div class="col-md-4">
                <div class="pull-right">

                </div>
            </div>

        </div>

        <div class="clearfix"></div>
        <?php if (count($this->get('allProjects')) > 0) { ?>
            <div id="sortableProjectKanban" class="sortableTicketList">

                <div class="row-fluid">

                    <?php foreach ($this->get('projectStatusLabels') as $key => $statusRow) {?>
                    <div class="column" style="width:<?=$size?>%;">

                        <h4 class="widgettitle widgettitle title-primary title-border-<?php echo $statusRow['class']; ?>">
                            <?php if ($login::userIsAtLeast($roles::$manager)) { ?>
                                <a href="<?=BASE_URL ?>/setting/editBoxLabel?module=ticketLabels&label=<?=$key?>"
                                   class="editLabelModal editHeadline"><i class="fas fa-edit"></i></a>
                            <?php } ?>
                            <?php $this->e($statusRow["name"]); ?>
                        </h4>

                        <div class="contentInner status_<?=$key?>">

                            <?php foreach ($this->get('allProjects') as $project) { ?>
                                <?php if ($project->state == $key) { ?>
                                    <div class="ticketBox projectBox moveable" id="item_<?php echo $project->id; ?>">

                                        <div class="row " id="projectProgressContainer">
                                                <div class="col-md-12">
                                                    <div class="row" style="padding-bottom:10px;">
                                                        <div class="col-md-12">
                                                            <div class="projectAvatar">
                                                                <img src="<?=BASE_URL?>/api/projects?projectAvatar=<?=$project->id ?>"/>
                                                            </div>
                                                            <small><?php $this->e($project->clientName)?></small>
                                                            <h4>
                                                                <a href="<?=BASE_URL?>/dashboard/show?projectId=<?=$project->id?>"><?php $this->e($project->name)?></a>
                                                            </h4>
                                                        </div>

                                                    </div>

                                                    <div class="row">

                                                        <div class="col-md-7">
                                                            <?=$this->__("subtitles.project_progress") ?>
                                                        </div>
                                                        <div class="col-md-5" style="text-align:right">
                                                            <?=sprintf($this->__("text.percent_complete"), round($project->progress['percent']))?>
                                                        </div>
                                                    </div>
                                                    <div class="progress">
                                                        <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?=round($project->progress['percent']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=round($project->progress['percent']); ?>%">
                                                            <span class="sr-only"><?=round($project->progress['percent']); ?>% Complete</span>
                                                        </div>
                                                    </div>
                                                    <br />

                                                    <?php if ($project->lastUpdate !== false) {?>
                                                        <div class="lastStatus">
                                                            <div class="commentStatus-<?=$this->escape($project->lastUpdate['status']); ?>">
                                                                <h4 class="">
                                                                    <?php printf(
                                                                        $this->__('text.report_written_on'),
                                                                        $this->getFormattedDateString($project->lastUpdate['date']),
                                                                        $this->getFormattedTimeString($project->lastUpdate['date'])
                                                                    ); ?>

                                                                </h4>

                                                                <div class="text" id="commentText-<?=$project->lastUpdate['id']?>"><?php echo $this->escapeMinimal($project->lastUpdate['text']); ?></div>

                                                            </div>

                                                        </div>
                                                        <div class="clearall"></div>
                                                        <br />
                                                    <?php } ?>
                                                </div>
                                            </div>
                                        <div class="center">
                                                <a class="showMoreLink" href="javascript:void(0);"  onclick="jQuery('#moreInfo-<?=$project->id?>').toggle('fast')"><?=$this->__("links.read_more") ?></a>

                                            </div>
                                        <div id="moreInfo-<?=$project->id?>" style="display:none;">
                                                <div class="row  padding-md">
                                                    <div class="col-md-12">
                                                        <div class="row">
                                                            <div class="col-md-6 border-bottom">
                                                                <h5><?=$this->__("label.open_todos") ?></h5>
                                                            </div>
                                                            <div class="col-md-6 border-bottom">
                                                                <?php
                                                                if ($project->report !== false) {
                                                                    echo($project->report['sum_open_todos'] + $project->report['sum_progres_todos']);
                                                                } else {
                                                                    echo 0;
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 border-bottom">
                                                                <h5><?=$this->__("label.planned_hours") ?></h5>
                                                            </div>
                                                            <div class="col-md-6 border-bottom">
                                                                <?php if ($project->report !== false && $project->report['sum_planned_hours'] != null) {
                                                                    echo $project->report['sum_planned_hours'];
                                                                } else {
                                                                    echo 0;
                                                                } ?>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 border-bottom">
                                                                <h5><?=$this->__("label.estimated_hours_remaining") ?></h5>
                                                            </div>
                                                            <div class="col-md-6 border-bottom">
                                                                <?php if ($project->report !== false && $project->report['sum_estremaining_hours'] != null) {
                                                                    echo $project->report['sum_estremaining_hours'];
                                                                } else {
                                                                    echo 0;
                                                                } ?>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="col-md-6 border-bottom">
                                                                <h5><?=$this->__("label.booked_hours") ?></h5>
                                                            </div>
                                                            <div class="col-md-6 border-bottom">
                                                                <?php if ($project->report !== false && $project->report['sum_logged_hours'] != null) {
                                                                    echo $project->report['sum_logged_hours'];
                                                                } else {
                                                                    echo 0;
                                                                } ?>
                                                            </div>
                                                        </div>
                                                        <br />
                                                    </div>

                                                </div>

                                                <div class="row" id="milestoneProgressContainer">
                                                    <div class="col-md-12">
                                                        <h5 class="subtitle" style="font-size:14px;"><?=$this->__("headline.milestones") ?></h5>
                                                        <ul class="sortableTicketList" >
                                                            <?php
                                                            if (count($project->milestones) == 0) {
                                                                echo"<div class='center'><br /><h4>" . $this->__("headlines.no_milestones") . "</h4>
                                            " . $this->__("text.milestones_help_organize_projects") . "<br /><br />";
                                                            }
                                                            ?>
                                                            <?php foreach ($project->milestones as $row) {
                                                                $percent = 0;


                                                                if ($row->editTo == "0000-00-00 00:00:00") {
                                                                    $date = $this->__("text.no_date_defined");
                                                                } else {
                                                                    $date = new DateTime($row->editTo);
                                                                    $date = $date->format($this->__("language.dateformat"));
                                                                }
                                                                if ($row->percentDone < 100 || $date >= new DateTime()) {
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
                                                                <?php }
                                                            } ?>

                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>

                                    </div>
                                <?php } ?>
                            <?php } ?>

                        </div>

                    </div>

                    <?php } ?>

                </div>
            </div>
            <div class="clearfix"></div>

        <?php } else { ?>
            <br/><br/>
            <div class='center'>
                <div style='width:50%' class='svgContainer'>
                    <?php echo file_get_contents(ROOT . "/images/svg/undraw_new_ideas_jdea.svg"); ?>
                </div>

                No Projects Yet
            </div>

        <?php } ?>






    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function () {


        leantime.projectsController.setUpKanbanColumns();

        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
        var statusList = [<?php foreach ($projectStatusLabels as $key => $statusRow) {
            echo "'" . $key . "',";
                              }?>];
            leantime.projectsController.initProjectsKanban(statusList);

        <?php } else { ?>
            leantime.generalController.makeInputReadonly(".maincontentinner");

        <?php } ?>


    });

</script>
