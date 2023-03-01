<?php
    $allProjects = $this->get('allProjects');
    $clients = $this->get('clients');
    $currentClient = $this->get("currentClient");

?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-briefcase"></span></div>
    <div class="pagetitle">
        <div class="row">
            <div class="col-lg-4">
                <h5><?php $this->__("headlines.projects"); ?></h5>
                <h1><?php echo $this->__("headlines.my_portfolio"); ?></h1>
            </div>
            <div class="col-lg-4" style="text-align:right;padding-top:15px">

            </div>
        </div>
    </div>
</div>

<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $this->displayNotification(); ?>
        <div class="row">
            <div class="col-md-4">

            </div>
            <div class="col-md-4">
                <div class="center">
                    <form>
                        <?php if (count($clients) > 0) { ?>
                        <select id="client" name="client" class="mainSprintSelector" onchange="form.submit();">
                            <option value="" <?php if ($currentClient == "") {
                                echo " selected='selected' ";
                                             } ?>><?=$this->__("headline.all_clients"); ?></option>
                            <?php foreach ($clients as $key => $value) {
                                echo "<option value='" . $key . "'";
                                if ($currentClient == $key) {
                                    echo " selected='selected' ";
                                }
                                echo">" . $this->escape($value) . "</option>";
                            }
                            ?>
                        </select>
                        <?php } ?>
                    </form>
                </div>
            </div>
            <div class="col-md-4">

            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <br />
            </div>
        </div>

        <div class="row">


                <?php if (is_array($allProjects) && count($allProjects) == 0) {
                    echo "<div class='col-md-12'><br /><br /><div class='center'>";
                    echo"<div style='width:30%' class='svgContainer'>";
                        echo file_get_contents(ROOT . "/images/svg/undraw_a_moment_to_relax_bbpa.svg");
                        echo $this->__('notifications.not_assigned_to_any_project');
                    if ($login::userIsAtLeast($roles::$manager)) {
                        echo"<br /><br /><a href='" . BASE_URL . "/projects/newProject' class='btn btn-primary'>" . $this->__('link.new_project') . "</a>";
                    }
                        echo"</div></div>";
                }?>
                <?php foreach ($allProjects as $project) { ?>
                <div class="col-md-3">
                    <div class="projectBox">

                        <div class="row " id="projectProgressContainer">
                            <div class="col-md-12">
                                <small><?php $this->e($project['clientName'])?></small>
                                <h4>
                                    <a href="<?=BASE_URL?>/dashboard/show?projectId=<?=$project['id']?>"><?php $this->e($project['name'])?></a>
                                </h4>
                                <br />
                                <div class="row">

                                    <div class="col-md-7">
                                        <?=$this->__("subtitles.project_progress") ?>
                                    </div>
                                    <div class="col-md-5" style="text-align:right">
                                        <?=sprintf($this->__("text.percent_complete"), round($project['progress']['percent']))?>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?=round($project['progress']['percent']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=round($project['progress']['percent']); ?>%">
                                        <span class="sr-only"><?=round($project['progress']['percent']); ?>% Complete</span>
                                    </div>
                                </div>
                                <br />

                                <?php if ($project['lastUpdate'] !== false) {?>
                                    <div class="lastStatus">
                                        <div class="commentStatus-<?=$this->escape($project['lastUpdate']['status']); ?>">
                                            <h4 class="">
                                                <?php printf(
                                                    $this->__('text.report_written_on'),
                                                    $this->getFormattedDateString($project['lastUpdate']['date']),
                                                    $this->getFormattedTimeString($project['lastUpdate']['date'])
                                                ); ?>

                                            </h4>

                                            <div class="text" id="commentText-<?=$project['lastUpdate']['id']?>"><?php echo $this->escapeMinimal($project['lastUpdate']['text']); ?></div>

                                        </div>

                                    </div>
                                    <div class="clearall"></div>
                                    <br />
                                <?php } ?>
                            </div>
                        </div>
                        <div class="center">
                            <a class="showMoreLink" href="javascript:void(0);"  onclick="jQuery('#moreInfo-<?=$project['id']?>').toggle('fast')"><?=$this->__("links.read_more") ?></a>

                        </div>
                        <div id="moreInfo-<?=$project['id']?>" style="display:none;">
                            <div class="row  padding-md">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6 border-bottom">
                                            <h5><?=$this->__("label.open_todos") ?></h5>
                                        </div>
                                        <div class="col-md-6 border-bottom">
                                            <?php
                                            if ($project['report'] !== false) {
                                                echo($project['report']['sum_open_todos'] + $project['report']['sum_progres_todos']);
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
                                            <?php if ($project['report'] !== false && $project['report']['sum_planned_hours'] != null) {
                                                echo $project['report']['sum_planned_hours'];
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
                                            <?php if ($project['report'] !== false && $project['report']['sum_estremaining_hours'] != null) {
                                                echo $project['report']['sum_estremaining_hours'];
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
                                            <?php if ($project['report'] !== false && $project['report']['sum_logged_hours'] != null) {
                                                echo $project['report']['sum_logged_hours'];
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
                                        if (count($project['milestones']) == 0) {
                                            echo"<div class='center'><br /><h4>" . $this->__("headlines.no_milestones") . "</h4>
                                            " . $this->__("text.milestones_help_organize_projects") . "<br /><br />";
                                        }
                                        ?>
                                        <?php foreach ($project['milestones'] as $row) {
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
                </div>

                <?php }?>

        </div>
    </div>
</div>


