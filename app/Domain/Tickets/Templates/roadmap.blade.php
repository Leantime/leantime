@extends($layout)

@section('content')

<?php
$milestones = $tpl->get('milestones');
$timelineTasks = $tpl->get("timelineTasks");

echo $tpl->displayNotification();

$roadmapView = session("usersettings.views.roadmap", "Month");
?>
<?php $tpl->displaySubmodule('tickets-timelineHeader') ?>

<div class="maincontent">

    <?php $tpl->displaySubmodule('tickets-timelineTabs') ?>

    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                <?php
                $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');

                $tpl->displaySubmodule('tickets-ticketNewBtn');
                $tpl->displaySubmodule('tickets-ticketFilter');

                $tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
                ?>
            </div>
            <div class="col-md-4">
            </div>
            <div class="col-md-4">
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
                </div>

            </div>
        </div>

        <?php
        if (
            (is_array($timelineTasks) && count($timelineTasks) == 0) ||
            $timelineTasks == false
        ) {
            echo"<div class='empty' id='emptySprint' style='text-align:center;'>";
            echo"<div style='width:30%' class='svgContainer'>";
            echo file_get_contents(ROOT . "/dist/images/svg/undraw_adjustments_p22m.svg");
            echo"</div>";
            echo"
            <h4>" . $tpl->__("headlines.no_tickets") . "<br /></h4></div>";
        }
        ?>
        <div class="gantt-wrapper">
            <svg id="gantt"></svg>
        </div>

    </div>
</div>

<script type="text/javascript">

    jQuery(document).ready(function(){


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


    <?php


    if (count($timelineTasks) > 0) {?>
        var tasks = [

            <?php
            $lastMilestoneSortIndex = array();

            //Set sort index first format: 0.0
            //Sort is milestone sorting first with the milestone sort id as first index
            //Then sort by task as second index

            foreach ($timelineTasks as $mlst) {
                if ($mlst->type == "milestone") {
                    $lastMilestoneSortIndex[$mlst->id] = $mlst->sortIndex != '' ? $mlst->sortIndex : 999;
                }
            }

            foreach ($timelineTasks as $mlst) {
                $headline = $tpl->__('label.' . strtolower($mlst->type)) . ": " . $mlst->headline;
                if ($mlst->type == "milestone") {
                    $headline .= " (" . format($mlst->percentDone)->decimal() . "% Done)";
                }

                $color = "#8D99A6";
                if ($mlst->type == "milestone") {
                    $color = $mlst->tags;
                }

                $sortIndex = 0;



                //Item is milestone itself, set first index + .0
                if ($mlst->type == "milestone") {
                    $sortIndex = $lastMilestoneSortIndex[$mlst->id] . ".000";
                } else {
                    //If it has a milestone dependency, add milestone index
                    if($mlst->dependingTicketId > 0){
                        $sortIndex = ($lastMilestoneSortIndex[$mlst->dependingTicketId] ?? "999") . "." . str_pad(($mlst->sortIndex ?? 999), 3, 0, STR_PAD_LEFT);
                    }else if ($mlst->milestoneid > 0) {
                        $sortIndex = ($lastMilestoneSortIndex[$mlst->milestoneid] ?? "999" ). "." . str_pad(($mlst->sortIndex ?? 999), 3, 0, STR_PAD_LEFT);
                    } else {
                        $sortIndex = "999" . "." . str_pad(($mlst->sortIndex ?? 999), 3, 0, STR_PAD_LEFT);
                    }
                }

                $dependencyList = array();

                //Show subtask dependencies only within tasks
                //Avvoid double arrow
                if ($mlst->dependingTicketId != 0) {
                    $dependencyList[] = $mlst->dependingTicketId;
                } else if ($mlst->milestoneid != 0) {
                    $dependencyList[] = $mlst->milestoneid;
                }

                echo"{
                    id :'" . $mlst->id . "',
                    name :" . json_encode($headline) . ",
                    start :'" . (($mlst->editFrom != '0000-00-00 00:00:00' && !str_starts_with(
                        $mlst->editFrom,
                        '1969-12-31'
                    )) ? $mlst->editFrom :  date('Y-m-d', strtotime("+1 day", time()))) . "',
                    end :'" . (($mlst->editTo != '0000-00-00 00:00:00' && !str_starts_with($mlst->editTo, '1969-12-31')) ? $mlst->editTo :  date('Y-m-d', strtotime("+1 week", time()))) . "',
                    progress :'" . format($mlst->percentDone)->decimal() . "',
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

@endsection
