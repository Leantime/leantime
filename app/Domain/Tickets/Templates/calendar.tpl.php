<?php
defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) {
    $$var = $val; // necessary for blade refactor
}
$milestones = $tpl->get('milestones');
if (!session()->exists("usersettings.submenuToggle.myProjectCalendarView")) {
    session(["usersettings.submenuToggle.myProjectCalendarView" => "dayGridMonth"]);
}

echo $tpl->displayNotification();

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
                <div class="fc-center center" id="calendarTitle" style="padding-top:5px;">
                    <h2>..</h2>
                </div>
            </div>
            <div class="col-md-4">

                <button class="fc-next-button btn btn-default right" type="button" style="margin-right:5px;">
                    <span class="fc-icon fc-icon-chevron-right"></span>
                </button>
                <button class="fc-prev-button btn btn-default right" type="button" style="margin-right:5px;">
                    <span class="fc-icon fc-icon-chevron-left"></span>
                </button>

                <button class="fc-today-button btn btn-default right" style="margin-right:5px;">today</button>


                <select id="my-select" style="margin-right:5px;" class="right">
                    <option class="fc-timeGridDay-button fc-button fc-state-default fc-corner-right" value="timeGridDay" <?=session("usersettings.submenuToggle.myProjectCalendarView") == 'timeGridDay' ? "selected" : '' ?>>Day</option>
                    <option class="fc-timeGridWeek-button fc-button fc-state-default fc-corner-right" value="timeGridWeek" <?=session("usersettings.submenuToggle.myProjectCalendarView") == 'timeGridWeek' ? "selected" : '' ?>>Week</option>
                    <option class="fc-dayGridMonth-button fc-button fc-state-default fc-corner-right" value="dayGridMonth" <?=session("usersettings.submenuToggle.myProjectCalendarView") == 'dayGridMonth' ? "selected" : '' ?>>Month</option>
                    <option class="fc-multiMonthYear-button fc-button fc-state-default fc-corner-right" value="multiMonthYear" <?=session("usersettings.submenuToggle.myProjectCalendarView") == 'multiMonthYear' ? "selected" : '' ?>>Year</option>
                </select>

            </div>

        </div>
        <div class="calendar-wrapper">
            <div id="calendar"></div>
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








    var events = [
        <?php foreach ($milestones as $mlst) :
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
                $sortIndex = $mlst->sortIndex;
            }

            $dependencyList = array();
            if ($mlst->milestoneid != 0) {
                $dependencyList[] = $mlst->milestoneid;
            }

            if ($mlst->dependingTicketId != 0) {
                $dependencyList[] = $mlst->dependingTicketId;
            }


            ?>

        {

            title: <?php echo json_encode($headline); ?>,

            <?php if(dtHelper()->isValidDateString($mlst->dateToFinish)){ ?>
                start: new Date(<?php echo format($mlst->dateToFinish)->jsTimestamp() ?>),
                end: new Date(<?php echo format(dtHelper()->parseDbDateTime($mlst->dateToFinish)->addHour(1))->jsTimestamp() ?>),
            <?php } elseif(dtHelper()->isValidDateString($mlst->editFrom)){ ?>
                start: new Date(<?php echo format($mlst->editFrom)->jsTimestamp() ?>),
                end: new Date(<?php echo format($mlst->editTo)->jsTimestamp() ?>),
            <?php } ?>


            enitityId: <?php echo $mlst->id ?>,
            <?php if ($mlst->type == "milestone") { ?>
            url: '#/tickets/editMilestone/<?php echo $mlst->id ?>',
            color: '<?=$color?>',
            enitityType: "milestone",
            allDay: true,
            <?php } else { ?>
            url: '#/tickets/showTicket/<?php echo $mlst->id ?>',
            color: '<?=$color?>',
            enitityType: "ticket",
            allDay: false,
            <?php } ?>

        },
        <?php endforeach; ?>
    ];



    document.addEventListener('DOMContentLoaded', function () {
        leantime.calendarController.initTicketsCalendar(
            document.getElementById('calendar'),
            '<?=session("usersettings.submenuToggle.myProjectCalendarView") ?>',
            events,
        );
    });


</script>
