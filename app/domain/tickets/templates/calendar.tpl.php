<?php
defined('RESTRICTED') or die('Restricted access');

foreach ($__data as $var => $val) $$var = $val; // necessary for blade refactor
$milestones = $tpl->get('milestones');
if (!isset($_SESSION['submenuToggle']["myCalendarView"])) {
    $_SESSION['submenuToggle']["myProjectCalendarView"] = "dayGridMonth";
}

?>

<div class="pageheader">
    <div class="pageicon"><span class="fa fa-sliders"></span></div>
    <div class="pagetitle">
        <h5><?php $tpl->e($_SESSION['currentProjectClient'] . " // " . $_SESSION['currentProjectName']); ?></h5>
        <h1><?=$tpl->__("headline.project_calendar"); ?></h1>
    </div>
</div><!--pageheader-->


<div class="maincontent">
    <div class="maincontentinner">

        <?php echo $tpl->displayNotification(); ?>

        <div class="row">
            <div class="col-md-4">
                <?php if ($login::userIsAtLeast($roles::$editor)) { ?>
                <a href="<?=BASE_URL ?>/tickets/editMilestone" class="milestoneModal btn btn-primary"><?=$tpl->__("links.add_milestone"); ?></a>
                <?php } ?>
            </div>
            <div class="col-md-4">
                <div class="fc-center center" id="calendarTitle" style="padding-top:5px;">
                    <h2>..</h2>
                </div>
            </div>
            <div class="col-md-4">


                <div class="btn-group viewDropDown pull-right">
                    <button class="btn dropdown-toggle" data-toggle="dropdown"><?=$tpl->__("links.calendar_view") ?> <?=$tpl->__("links.view") ?></button>
                    <ul class="dropdown-menu">\
                        <li><a href="<?=BASE_URL ?>/tickets/roadmap" ><?=$tpl->__("links.gantt_view") ?></a></li>
                        <li><a href="<?=BASE_URL ?>/tickets/showAllMilestones" ><?=$tpl->__("links.table") ?></a></li>
                        <li><a href="<?=BASE_URL ?>/tickets/showProjectCalendar" class="active"><?=$tpl->__("links.calendar_view") ?></a></li>
                    </ul>
                </div>




                <button class="fc-next-button btn btn-default right" type="button" style="margin-right:5px;">
                    <span class="fc-icon fc-icon-chevron-right"></span>
                </button>
                <button class="fc-prev-button btn btn-default right" type="button" style="margin-right:5px;">
                    <span class="fc-icon fc-icon-chevron-left"></span>
                </button>

                <button class="fc-today-button btn btn-default right" style="margin-right:5px;">today</button>


                <select id="my-select" style="margin-right:5px;" class="right">
                    <option class="fc-timeGridDay-button fc-button fc-state-default fc-corner-right" value="timeGridDay" <?=$_SESSION['submenuToggle']["myProjectCalendarView"] == 'timeGridDay' ? "selected" : '' ?>>Day</option>
                    <option class="fc-timeGridWeek-button fc-button fc-state-default fc-corner-right" value="timeGridWeek" <?=$_SESSION['submenuToggle']["myProjectCalendarView"] == 'timeGridWeek' ? "selected" : '' ?>>Week</option>
                    <option class="fc-dayGridMonth-button fc-button fc-state-default fc-corner-right" value="dayGridMonth" <?=$_SESSION['submenuToggle']["myProjectCalendarView"] == 'dayGridMonth' ? "selected" : '' ?>>Month</option>
                    <option class="fc-multiMonthYear-button fc-button fc-state-default fc-corner-right" value="multiMonthYear" <?=$_SESSION['submenuToggle']["myProjectCalendarView"] == 'multiMonthYear' ? "selected" : '' ?>>Year</option>
                </select>

                <div class="pull-right btn-group" style="margin-right:10px;">
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
        <div class="calendar-wrapper">
            <div id="calendar"></div>
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

            start: <?php echo "'" . (($mlst->editFrom != '0000-00-00 00:00:00' && substr($mlst->editFrom, 0, 10) != '1969-12-31') ? $mlst->editFrom :  date('Y-m-d', strtotime("+1 day", time()))) . "',"; ?>
            <?php if (isset($mlst->editTo)) : ?>
            end: <?php echo "'" . (($mlst->editTo != '0000-00-00 00:00:00' && substr($mlst->editTo, 0, 10) != '1969-12-31') ? $mlst->editTo :  date('Y-m-d', strtotime("+1 day", time()))) . "',"; ?>
            <?php endif; ?>
            enitityId: <?php echo $mlst->id ?>,
            <?php if ($mlst->type == "milestone") { ?>
            url: '<?=CURRENT_URL ?>#/tickets/editMilestone/<?php echo $mlst->id ?>',
            color: '<?=$color?>',
            enitityType: "milestone",
            allDay: true,
            <?php } else { ?>
            url: '<?=CURRENT_URL ?>#/tickets/showTicket/<?php echo $mlst->id ?>',
            color: '<?=$color?>',
            enitityType: "ticket",
            allDay: false,
            <?php } ?>

        },
        <?php endforeach; ?>
    ];



    document.addEventListener('DOMContentLoaded', function() {
        const heightWindow = jQuery("body").height() - 190;

        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
                height:heightWindow,
                initialView: '<?=$_SESSION['submenuToggle']["myProjectCalendarView"] ?>',
                events: events,
                editable: true,
                headerToolbar: false,

                nowIndicator: true,
                bootstrapFontAwesome: {
                    close: 'fa-times',
                    prev: 'fa-chevron-left',
                    next: 'fa-chevron-right',
                    prevYear: 'fa-angle-double-left',
                    nextYear: 'fa-angle-double-right'
                },
                eventDrop: function (event) {

                    jQuery.ajax({
                        type : 'PATCH',
                        url  : leantime.appUrl + '/api/tickets',
                        data : {
                            id: event.event.extendedProps.enitityId,
                            editFrom: event.event.startStr,
                            editTo: event.event.endStr
                        }
                    });
                },
                eventResize: function (event) {

                    jQuery.ajax({
                        type : 'PATCH',
                        url  : leantime.appUrl + '/api/tickets',
                        data : {
                            id: event.event.extendedProps.enitityId,
                            editFrom: event.event.startStr,
                            editTo: event.event.endStr
                        }
                    })

                },
                eventMouseEnter: function() {
                }
            }
        );
        calendar.setOption('locale', leantime.i18n.__("language.code"));
        calendar.render();
        calendar.scrollToTime( 100 );
        jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);

        jQuery('.fc-prev-button').click(function() {
            calendar.prev();
            calendar.getCurrentData()
            jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery('.fc-next-button').click(function() {
            calendar.next();
            jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery('.fc-today-button').click(function() {
            calendar.today();
            jQuery("#calendarTitle h2").text(calendar.getCurrentData().viewTitle);
        });
        jQuery("#my-select").on("change", function(e){

            calendar.changeView(jQuery("#my-select option:selected").val());

            jQuery.ajax({
                type : 'PATCH',
                url  : leantime.appUrl + '/api/submenu',
                data : {
                    submenu : "myProjectCalendarView",
                    state   : jQuery("#my-select option:selected").val()
                }
            });

        });
    });


</script>
