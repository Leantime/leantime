<?php
defined('RESTRICTED') or die('Restricted access');
foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
$milestones = $tpl->get('milestones');

?>

<?php
if (isset($_SESSION['userdata']['settings']['views']['roadmap'])) {
    $roadmapView = $_SESSION['userdata']['settings']['views']['roadmap'];
} else {
    $roadmapView = "Month";
}
?>
<div class="pageheader">
    <div class="pageicon"><span class="fa fa-sliders"></span></div>
    <div class="pagetitle">
        <h5><?php $tpl->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']); ?></h5>
        <h1><?=$tpl->__("headline.milestones"); ?></h1>
    </div>
</div><!--pageheader-->


<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <div class="row">
            <div class="col-md-6">
                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <a href="<?=BASE_URL ?>/tickets/editMilestone" class="milestoneModal btn btn-primary"><?=$tpl->__("links.add_milestone"); ?></a>
                <?php } ?>
            </div>
            <div class="col-md-6">
                <div class="pull-right">

                    <div class="btn-group dropRight">

                        <?php
                            $currentView = "";
                        if ($roadmapView == 'Day') {
                            $currentView = $tpl->__("buttons.day");
                        } elseif ($roadmapView == 'Week') {
                            $currentView = $tpl->__("buttons.week");
                        } elseif ($roadmapView == 'Month') {
                            $currentView = $tpl->__("buttons.month");
                        }
                        ?>
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$tpl->__("buttons.timeframe"); ?>: <span class="viewText"><?=$currentView; ?></span><span class="caret"></span></button>
                        <ul class="dropdown-menu" id="ganttTimeControl">
                            <li><a href="javascript:void(0);" data-value="Day" class="<?php if ($roadmapView == 'Day') {
                                echo "active";
                                                                                      }?>"> <?=$tpl->__("buttons.day"); ?></a></li>
                            <li><a href="javascript:void(0);" data-value="Week" class="<?php if ($roadmapView == 'Week') {
                                echo "active";
                                                                                       }?>"><?=$tpl->__("buttons.week"); ?></a></li>
                            <li><a href="javascript:void(0);" data-value="Month" class="<?php if ($roadmapView == 'Month') {
                                echo "active";
                                                                                        }?>"><?=$tpl->__("buttons.month"); ?></a></li>
                        </ul>
                    </div>

                    <div class="btn-group viewDropDown">
                        <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$tpl->__("links.gantt_view") ?> <?=$tpl->__("links.view") ?></button>
                        <ul class="dropdown-menu">
                            <li><a href="<?=BASE_URL ?>/tickets/roadmap" class="active"><?=$tpl->__("links.gantt_view") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/tickets/showAllMilestones" ><?=$tpl->__("links.table") ?></a></li>
                            <li><a href="<?=BASE_URL ?>/tickets/showProjectCalendar"><?=$tpl->__("links.calendar_view") ?></a></li>
                        </ul>
                    </div>

                    <div class="pull-left btn-group" style="margin-right:10px;">
                        <form action="" method="get" id="searchForm">
                            <label class="pull-right" for="includeTasks">&nbsp;<?=$tpl->__('label.showTasks'); ?></label>
                            <input type="hidden" name="submitIncludeTasks" value="1" />
                            <input type="checkbox" class="js-switch" id="includeTasks" name="includeTasks" onChange="this.form.submit();" <?php if ($tpl->get('includeTasks') === true) {
                                echo "checked='checked'";
                                                                                                                                          } ?>/>
                        </form>
                    </div>

                </div>

            </div>
        </div>

        <?php
        if (count($milestones) == 0) {
            echo"<div class='empty' id='emptySprint' style='text-align:center;'>";
            echo"<div style='width:30%' class='svgContainer'>";
            echo file_get_contents(ROOT . "/dist/images/svg/undraw_adjustments_p22m.svg");
            echo"</div>";
            echo"
            <h4>" . $tpl->__("headlines.no_milestones") . "<br/>

            <br />
            <a href=\"" . BASE_URL . "/tickets/editMilestone\" class=\"milestoneModal addCanvasLink btn btn-primary\">" . $tpl->__("links.add_milestone") . "</a></h4></div>";
        }
        ?>
        <div class="gantt-wrapper">
            <svg id="gantt"></svg>
        </div>

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){

    leantime.ticketsController.initModals();

    <?php if (isset($_GET['showMilestoneModal'])) {
        if ($_GET['showMilestoneModal'] == "") {
            $modalUrl = "";
        } else {
            $modalUrl = "/" . (int)$_GET['showMilestoneModal'];
        }
        ?>

        leantime.ticketsController.openMilestoneModalManually("<?=BASE_URL ?>/tickets/editMilestone<?php echo $modalUrl; ?>");
        window.history.pushState({},document.title, '<?=BASE_URL ?>/tickets/roadmap');

    <?php } ?>


});

    <?php if (count($milestones) > 0) {?>
        var tasks = [

            <?php
            $lastMilestoneSortIndex = array();
            //Set sort index first
            foreach ($milestones as $mlst) {
                if ($mlst->type == "milestone") {
                    $lastMilestoneSortIndex[$mlst->id] = $mlst->sortIndex;
                }
            }
            foreach ($milestones as $mlst) {
                $headline = $tpl->__('label.' . strtolower($mlst->type)) . ": " . $mlst->headline;
                if ($mlst->type == "milestone") {
                    $headline .= " (" . $mlst->percentDone . "% Done)";
                }

                $color = "#8D99A6";
                if ($mlst->type == "milestone") {
                    $color = $mlst->tags;
                }

                $sortIndex = 0;

                if ($mlst->sortIndex != '' && is_numeric($mlst->sortIndex)) {
                    if ($mlst->type == "milestone") {
                        $sortIndex = $lastMilestoneSortIndex[$mlst->id] . ".0";
                    } else {
                        if ($mlst->milestoneid != 0) {
                            $sortIndex = $lastMilestoneSortIndex[$mlst->milestoneid] . "." . $mlst->sortIndex;
                        } else {
                            $sortIndex = "0" . "." . $mlst->sortIndex;
                        }
                    }
                }

                $dependencyList = array();
                if ($mlst->milestoneid != 0) {
                    $dependencyList[] = $mlst->milestoneid;
                }

                if ($mlst->dependingTicketId != 0) {
                    $dependencyList[] = $mlst->dependingTicketId;
                }

                echo"{
                    id :'" . $mlst->id . "',
                    name :" . json_encode($headline) . ",
                    start :'" . (($mlst->editFrom != '0000-00-00 00:00:00' && substr($mlst->editFrom, 0, 10) != '1969-12-31') ? $mlst->editFrom :  date('Y-m-d', strtotime("+1 day", time()))) . "',
                    end :'" . (($mlst->editTo != '0000-00-00 00:00:00' && substr($mlst->editTo, 0, 10) != '1969-12-31') ? $mlst->editTo :  date('Y-m-d', strtotime("+1 week", time()))) . "',
                    progress :'" . $mlst->percentDone . "',
                    dependencies :'" . implode(",", $dependencyList) . "',
                    custom_class :'',
                    type: '" . strtolower($mlst->type) . "',
                    bg_color: '" . $color . "',
                    thumbnail: '" . BASE_URL . "/api/users?profileImage=" . $mlst->editorId . "',
                    sortIndex: " . $sortIndex . "

                },";
            }
            ?>
        ];





        <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
        leantime.ticketsController.initGanttChart(tasks, '<?=$roadmapView; ?>', false);
        <?php } else { ?>
        leantime.ticketsController.initGanttChart(tasks, '<?=$roadmapView; ?>', true);
        <?php } ?>

    <?php } ?>


</script>
