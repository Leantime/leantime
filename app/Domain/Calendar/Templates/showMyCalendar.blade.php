@extends($layout)

@section('content')

<?php
if (!session()->exists("usersettings.submenuToggle.myCalendarView")) {
    session(["usersettings.submenuToggle.myCalendarView" => "dayGridMonth"]);
}
?>

<?php $tpl->dispatchTplEvent('beforePageHeaderOpen'); ?>
<div class="pageheader">
    <?php $tpl->dispatchTplEvent('afterPageHeaderOpen'); ?>
    <div class="pageicon"><span class="fa fa-calendar"></span></div>
    <div class="pagetitle">
        <h5>{{ __("headline.calendar") }}</h5>
        <h1>{{ __("headline.my_calendar") }}</h1>
    </div>
    <?php $tpl->dispatchTplEvent('beforePageHeaderClose'); ?>
</div><!--pageheader-->
<?php $tpl->dispatchTplEvent('afterPageHeaderClose'); ?>

@displayNotification()

<div class="maincontent">

    <div class="row">
        <div class="col-md-2">
            <div class="maincontentinner">
                <h5 class="subtitle pb-m">Calendars</h5>

                <ul class="simpleList">
                    <li><span class="indicatorCircle" style="background:var(--accent1)"></span>Events</li>
                    <li><span class="indicatorCircle" style="background:var(--accent2)"></span>Projects & Tasks</li>


                <?php foreach ($tpl->get('externalCalendars') as $calendars) { ?>
                    <li>
                        <div class="inlineDropDownContainer" style="float:right;">
                            <x-global::content.context-menu label-text="<i class='fa fa-ellipsis-h' aria-hidden='true'></i>" contentRole="link" position="bottom" align="start">
                                <x-global::actions.dropdown.item variant="link" href="#/calendar/editExternal/{{ $calendars['id'] }}">
                                    <i class="fa-solid fa-pen-to-square"></i> {!! __('links.edit_calendar') !!}
                                </x-global::actions.dropdown.item>
                                <x-global::actions.dropdown.item variant="link" href="#/calendar/delExternalCalendar/{{ $calendars['id'] }}" class="delete">
                                    <i class="fa fa-trash"></i> {!! __('links.delete_external_calendar') !!}
                                </x-global::actions.dropdown.item>
                            </x-global::content.context-menu>

                        </div>
                        <span class="indicatorCircle" style="background:<?=$calendars['colorClass'] ?>"></span><?=$calendars['name'] ?>

                    </li>
                <?php } ?>

                </ul>
                <hr />
                <a href="#/calendar/importGCal"><i class="fa-regular fa-calendar-plus"></i> Import Calendar</a>
            </div>
        </div>
        <div class="col-md-10">
            <div class="maincontentinner">
                <div class="row">
                    <div class="col-md-4">
                        <a href="#/calendar/addEvent" class="btn btn-primary formModal"><i class='fa fa-plus'></i> <?=$tpl->__('buttons.add_event')?></a>
                    </div>
                    <div class="col-md-4">
                        <div class="fc-center center" id="calendarTitle" style="padding-top:5px;">
                            <h2>..</h2>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <a href="#/calendar/export" class="btn btn-default right">Export</a>
                        <x-global::forms.button type="button" class="fc-next-button right" style="margin-right:5px;" content-role="secondary">
                            <span class="fc-icon fc-icon-chevron-right"></span>
                        </x-global::forms.button>

                        <x-global::forms.button type="button" class="fc-prev-button right" style="margin-right:5px;" content-role="secondary">
                            <span class="fc-icon fc-icon-chevron-left"></span>
                        </x-global::forms.button>

                        <x-global::forms.button type="button" class="fc-today-button right" style="margin-right:5px;" content-role="secondary">
                            today
                        </x-global::forms.button>



                        <x-global::forms.select
                        id="my-select"
                        class="right"
                        style="margin-right:5px;"
                    >
                        <x-global::forms.select.select-option
                            value="timeGridDay"
                            :selected="session('usersettings.submenuToggle.myCalendarView') == 'timeGridDay'"
                        >
                            Day
                        </x-global::forms.select.select-option>

                        <x-global::forms.select.select-option
                            value="timeGridWeek"
                            :selected="session('usersettings.submenuToggle.myCalendarView') == 'timeGridWeek'"
                        >
                            Week
                        </x-global::forms.select.select-option>

                        <x-global::forms.select.select-option
                            value="dayGridMonth"
                            :selected="session('usersettings.submenuToggle.myCalendarView') == 'dayGridMonth'"
                        >
                            Month
                        </x-global::forms.select.select-option>

                        <x-global::forms.select.select-option
                            value="multiMonthYear"
                            :selected="session('usersettings.submenuToggle.myCalendarView') == 'multiMonthYear'"
                        >
                            Year
                        </x-global::forms.select.select-option>
                    </x-global::forms.select>

                    </div>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>


</div>

<script type="module">

    leantime.moduleLoader.load("@mix('js/Domain/Calendar/Js/calendarController')").then(function(module){

            var eventSources = [];

            var events = {events: [
                        <?php foreach ($tpl->get('calendar') as $calendar) : ?>
                    {
                        title: <?php echo json_encode($calendar['title']); ?>,

                        start: new Date(<?php echo format($calendar['dateFrom'])->jsTimestamp() ?>),
                            <?php if (isset($calendar['dateTo'])) : ?>
                        end: new Date(<?php echo format($calendar['dateTo'])->jsTimestamp() ?>),
                        <?php endif; ?>
                            <?php if ((isset($calendar['allDay']) && $calendar['allDay'] === true)) : ?>
                        allDay: true,
                        <?php else : ?>
                        allDay: false,
                        <?php endif; ?>
                        entityId: <?php echo $calendar['id'] ?>,
                            <?php if (isset($calendar['eventType']) && $calendar['eventType'] == 'calendar') : ?>
                        url: '<?=CURRENT_URL ?>#/calendar/editEvent/<?php echo $calendar['id'] ?>',
                        backgroundColor: '<?= $calendar['backgroundColor'] ?? "var(--accent2)" ?>',
                        borderColor: '<?= $calendar['borderColor'] ?? "var(--accent2)" ?>',
                        entityType: "event",
                        <?php else : ?>
                        url: '<?=CURRENT_URL ?>#/tickets/showTicket/<?php echo $calendar['id'] ?>?projectId=<?php echo $calendar['projectId'] ?>',
                        backgroundColor: '<?= $calendar['backgroundColor'] ?? "var(--accent2)" ?>',
                        borderColor: '<?= $calendar['borderColor'] ?? "var(--accent2)" ?>',
                        entityType: "ticket",
                        <?php endif; ?>
                    },
                    <?php endforeach; ?>
                ]};

            eventSources.push(events);

            <?php
            $externalCalendars = $tpl->get("externalCalendars");

            foreach ($externalCalendars as $externalCalendar) { ?>
            eventSources.push(
                {
                    url: '{{ BASE_URL }}/calendar/externalCal/<?=$externalCalendar['id'] ?>',
                    format: 'ics',
                    color: '<?=$externalCalendar['colorClass'] ?>',
                    editable: false,
                }
            );

            <?php } ?>

            calendarController.initShowMyCalendar(
                document.getElementById('calendar'),
                eventSources,
                '<?=session("usersettings.submenuToggle.myCalendarView") ?>'
            );
    });

</script>

{{--<script type='text/javascript'>--}}

{{--    <?php $tpl->dispatchTplEvent('scripts.afterOpen'); ?>--}}


{{--    jQuery(document).ready(function() {--}}

{{--        //leantime.calendarController.initCalendar(events);--}}
{{--        leantime.calendarController.initExportModal();--}}

{{--    });--}}



{{--    document.addEventListener('DOMContentLoaded', function() {--}}
{{--        leantime.calendarController.initShowMyCalendar(--}}
{{--            document.getElementById('calendar'),--}}
{{--            eventSources,--}}
{{--            '<?=session("usersettings.submenuToggle.myCalendarView") ?>',--}}
{{--        );--}}
{{--    });--}}

{{--    <?php $tpl->dispatchTplEvent('scripts.beforeClose'); ?>--}}

{{--</script>--}}

@endsection
