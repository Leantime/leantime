<?php
use Leantime\Core\Frontcontroller;

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$allProjects = $tpl->get('allProjects');
$clients = $tpl->get('clients');
$currentClient = $tpl->get("currentClient");
$currentClientName = $tpl->get("currentClientName");
$currentUrlPath = BASE_URL . "/" . str_replace(".", "/", Frontcontroller::getCurrentRoute());
?>


<div class="maincontent" style="margin-top:0px">

    <div class="">


        <div style="padding:10px 0px">

            <div class="center">
        <span style="font-size:44px; color:var(--main-action-color);">
            @php
                $date = new DateTime();
                if(!empty($_SESSION['usersettings.timezone'])){
                    $date->setTimezone(new DateTimeZone($_SESSION['usersettings.timezone']));
                }
                $date = $date->format(__("language.timeformat"));
            @endphp

            {{ $date }}
        </span><br />
                <span style="font-size:24px; color:var(--main-action-color);">
                {{ __("welcome_widget.hi") }} {{ $currentUser['firstname'] }}
            </span><br /><br />
            </div>

        <?php echo $tpl->displayNotification(); ?>

        <div class="row">
            <div class="col-md-12">

            <br />

            </div>
        </div>

        <div class="row">


                <?php if (is_array($allProjects) && count($allProjects) == 0) {
                    echo "<div class='col-md-12'><br /><br /><div class='center'>";
                    echo"<div style='width:30%' class='svgContainer'>";
                    echo file_get_contents(ROOT . "/dist/images/svg/undraw_a_moment_to_relax_bbpa.svg");
                    echo $tpl->__('notifications.not_assigned_to_any_project');
                    if ($login::userIsAtLeast($roles::$manager)) {
                        echo"<br /><br /><a href='" . BASE_URL . "/projects/newProject' class='btn btn-primary'>" . $tpl->__('link.new_project') . "</a>";
                    }
                    echo"</div></div>";
                }?>

            <?php if (count($clients) > 0) {?>
                //
                <span class="dropdown dropdownWrapper">
                <a href="javascript:void(0)" class="dropdown-toggle header-title-dropdown" data-toggle="dropdown">
                    <?php
                    if ($currentClientName != '') {
                        $tpl->e($currentClientName);
                    } else {
                        echo $tpl->__("headline.all_clients");
                    }
                    ?>
                    <i class="fa fa-caret-down"></i>
                </a>

                <ul class="dropdown-menu">
                    <li><a href="<?=$currentUrlPath ?>"><?=$tpl->__("headline.all_clients"); ?></a></li>
                    <?php foreach ($clients as $key => $value) {
                        echo "<li><a href='" . $currentUrlPath . "?client=" . $key . "'>" . $tpl->escape($value['name']) . "</a></li>";
                    }
                    ?>
                </ul>
            </span>
            <?php } ?>



                <?php foreach ($allProjects as $project) { ?>
                <div class="col-md-3">
                    <div class="projectBox">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="row" style="padding-bottom:10px;">
                                    <div class="col-md-9">
                                        <div class="projectAvatar">
                                            <img src="<?=BASE_URL?>/api/projects?projectAvatar=<?=$project['id'] ?>&v=<?=format($project['modified'])->timestamp() ?>"/>
                                        </div>
                                        <small><?php $tpl->e($project['clientName'])?></small>
                                        <h4>
                                            <a href="<?=BASE_URL?>/dashboard/show?projectId=<?=$project['id']?>"
                                               <?php if (strlen($project['details']) > 0) { ?>
                                            data-tippy-content="<?=$tpl->e(substr(strip_tags($project['details']), 0, 80)) ?>"
                                               <?php } ?>
                                            ><?php $tpl->e($project['name'])?></a>
                                        </h4>
                                    </div>
                                    <div class="col-md-3" style="text-align:right">
                                        <?php if ($project['status'] !== null && $project['status'] != '') {?>
                                            <span class="label label-<?php $tpl->e($project['status'])?>"><?=$tpl->__("label.project_status_" . $project['status']) ?></span><br />

                                        <?php } else { ?>
                                            <span class="label label-grey"><?=$tpl->__("label.no_status")?></span><br />
                                        <?php } ?>
                                    </div>
                                </div>
                                <div class="clearall"></div><br />

                                <?php if ($project['lastUpdate'] !== false) {?>
                                    <div class="lastStatus">
                                        <div class="commentStatus-<?=$tpl->escape($project['lastUpdate']['status']); ?>">
                                            <h4 class="">
                                                <?php printf(
                                                    $tpl->__('text.report_written_on'),
                                                    format($project['lastUpdate']['date'])->date(),
                                                    format($project['lastUpdate']['date'])->time()
                                                ); ?>

                                            </h4>

                                            <div class="text" id="commentText-<?=$project['lastUpdate']['id']?>"><?php echo $tpl->escapeMinimal($project['lastUpdate']['text']); ?></div>

                                        </div>

                                    </div>
                                    <div class="clearall"></div>
                                    <br />
                                <?php } ?>

                                <?php
                                //Removing for now due to performance impact on progress calculation.
                                /*
                                <div class="row">
                                    <div class="col-md-7">
                                        <?=$tpl->__("subtitles.project_progress") ?>
                                    </div>
                                    <div class="col-md-5" style="text-align:right">
                                        <?=sprintf($tpl->__("text.percent_complete"), round($project['progress']['percent']))?>
                                    </div>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?=round($project['progress']['percent']); ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=round($project['progress']['percent']); ?>%">
                                        <span class="sr-only"><?=round($project['progress']['percent']); ?>% Complete</span>
                                    </div>
                                </div>
                                <br />

                                */ ?>


                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">


                                <div class="team">
                                    <?php foreach ($project['team'] as $member) { ?>
                                        <div class="commentImage" style="margin-right:-10px;">
                                            <img
                                                style=""
                                                src="<?=BASE_URL ?>/api/users?profileImage=<?=$member['id']?>&v=<?=format($member['modified'])->timestamp()?>" data-tippy-content="<?=$member['firstname'] . ' ' . $member['lastname'] ?>" />
                                        </div>
                                    <?php } ?>
                                </div>
                                <div class="clearall"></div>
                            </div>
                        </div>



                        <?php /*
                        <div class="center">
                            <a class="showMoreLink" href="javascript:void(0);"  onclick="jQuery('#moreInfo-<?=$project['id']?>').toggle('fast')"><?=$tpl->__("links.read_more") ?></a>

                        </div>
 */ ?>
                        <div id="moreInfo-<?=$project['id']?>" style="display:none;">
                            <div class="row  padding-md">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6 border-bottom">
                                            <h5><?=$tpl->__("label.open_todos") ?></h5>
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
                                            <h5><?=$tpl->__("label.planned_hours") ?></h5>
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
                                            <h5><?=$tpl->__("label.estimated_hours_remaining") ?></h5>
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
                                            <h5><?=$tpl->__("label.booked_hours") ?></h5>
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
                                    <h5 class="subtitle" style="font-size:14px;"><?=$tpl->__("headline.milestones") ?></h5>
                                    <ul class="sortableTicketList" >
                                        <?php
                                        if (count($project['milestones']) == 0) {
                                            echo"<div class='center'><br /><h4>" . $tpl->__("headlines.no_milestones") . "</h4>
                                            " . $tpl->__("text.milestones_help_organize_projects") . "<br /><br />";
                                        }
                                        ?>
                                        <?php foreach ($project['milestones'] as $row) {
                                            $percent = 0;


                                            if ($row->editTo == "0000-00-00 00:00:00") {
                                                $date = $tpl->__("text.no_date_defined");
                                            } else {
                                                $date = new DateTime($row->editTo);
                                                $date = $date->format($tpl->__("language.dateformat"));
                                            }
                                            if ($row->percentDone < 100 || $date >= new DateTime()) {
                                                ?>
                                                <li class="ui-state-default" id="milestone_<?php echo $row->id; ?>" >
                                                    <div class="ticketBox fixed">

                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <strong><a href="<?=BASE_URL ?>/tickets/editMilestone/<?php echo $row->id;?>" class="milestoneModal"><?php $tpl->e($row->headline); ?></a></strong>
                                                            </div>
                                                        </div>
                                                        <div class="row">

                                                            <div class="col-md-7">
                                                                <?=$tpl->__("label.due") ?>
                                                                <?php echo $date; ?>
                                                            </div>
                                                            <div class="col-md-5" style="text-align:right">
                                                                <?=sprintf($tpl->__("text.percent_complete"), $row->percentDone)?>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-12">
                                                                <div class="progress">
                                                                    <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="<?php echo $row->percentDone; ?>" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $row->percentDone; ?>%">
                                                                        <span class="sr-only"><?=sprintf($tpl->__("text.percent_complete"), $row->percentDone)?></span>
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


