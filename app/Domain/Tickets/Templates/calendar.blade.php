@extends($layout)

@section('content')

    <?php
$milestones = $tpl->get('milestones');
if (!session()->exists("usersettings.submenuToggle.myProjectCalendarView")) {
    session(["usersettings.submenuToggle.myProjectCalendarView" => "dayGridMonth"]);
}

?>
    @displayNotification()
    @include("tickets::includes.timelineHeader")

<div class="maincontent">
    @include("tickets::includes.timelineTabs")
    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                <?php
                $tpl->dispatchTplEvent('filters.afterLefthandSectionOpen');
                ?>

                <div class="row p-2">
                    @include("tickets::includes.ticketNewBtn")
                    @include("tickets::includes.ticketFilter")
                </div>

                <?php
                $tpl->dispatchTplEvent('filters.beforeLefthandSectionClose');
                ?>
            </div>
            <div class="col-md-4 p-2">
                <div class="fc-center center" id="calendarTitle" style="padding-top:5px;">
                    <h2>..</h2>
                </div>
            </div>
            <div class="col-md-4 p-2">

                <x-global::forms.button 
                    type="button" 
                    class="fc-next-button right" 
                    style="margin-right:5px;"
                    content-role='secondary'
                >
                    <span class="fc-icon fc-icon-chevron-right"></span>
                </x-global::forms.button>

                <x-global::forms.button 
                    type="button" 
                    class="fc-prev-button right" 
                    style="margin-right:5px;"
                    content-role='secondary'
                >
                    <span class="fc-icon fc-icon-chevron-left"></span>
                </x-global::forms.button>

                <x-global::forms.button 
                    type="button" 
                    class="fc-today-button right" 
                    style="margin-right:5px;"
                    content-role='secondary'
                >
                    today
                </x-global::forms.button>


                <x-global::forms.select class="right" id="my-select" style="margin-right:5px;">
                    <x-global::forms.select.select-option 
                        value="timeGridDay" 
                        :selected="session('usersettings.submenuToggle.myProjectCalendarView') == 'timeGridDay'">
                        Day
                    </x-global::forms.select.select-option>

                    <x-global::forms.select.select-option 
                        value="timeGridWeek" 
                        :selected="session('usersettings.submenuToggle.myProjectCalendarView') == 'timeGridWeek'">
                        Week
                    </x-global::forms.select.select-option>

                    <x-global::forms.select.select-option 
                        value="dayGridMonth" 
                        :selected="session('usersettings.submenuToggle.myProjectCalendarView') == 'dayGridMonth'">
                        Month
                    </x-global::forms.select.select-option>

                    <x-global::forms.select.select-option 
                        value="multiMonthYear" 
                        :selected="session('usersettings.submenuToggle.myProjectCalendarView') == 'multiMonthYear'">
                        Year
                    </x-global::forms.select.select-option>
                </x-global::forms.select>
            

            </div>

        </div>
        <div class="calendar-wrapper">
            <div id="calendar"></div>
        </div>

    </div>
</div>

<script type="module">

    import "@mix('/js/Domain/Tickets/Js/ticketsController.js')"
    import "@mix('/js/Domain/Calendar/Js/calendarController.js')"
    jQuery(document).ready(function(){

        <?php if (isset($_GET['showMilestoneModal'])) {
            if ($_GET['showMilestoneModal'] == "") {
                $modalUrl = "";
            } else {
                $modalUrl = "/" . (int)$_GET['showMilestoneModal'];
            }
            ?>

            ticketsController.openMilestoneModalManually("{{ BASE_URL }}/tickets/editMilestone<?php echo $modalUrl; ?>");
            window.history.pushState({},document.title, '{{ BASE_URL }}/tickets/roadmap');

        <?php } ?>


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

        calendarController.initTicketsCalendar(
            document.getElementById('calendar'),
            '<?=session("usersettings.submenuToggle.myProjectCalendarView") ?>',
            events
        );
    });

</script>
@endsection
