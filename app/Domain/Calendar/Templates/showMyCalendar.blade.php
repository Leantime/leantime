@php
    if (! session()->exists('usersettings.submenuToggle.myCalendarView')) {
        session(['usersettings.submenuToggle.myCalendarView' => 'dayGridMonth']);
    }
@endphp

@dispatchEvent('beforePageHeaderOpen')
<div class="pageheader">
    @dispatchEvent('afterPageHeaderOpen')
    <div class="pageicon"><span class="fa {{ $tpl->getModulePicture() }}"></span></div>
    <div class="pagetitle">
        <h5>{{ __('headline.calendar') }}</h5>
        <h1>{{ __('headline.my_calendar') }}</h1>
    </div>
    @dispatchEvent('beforePageHeaderClose')
</div>
@dispatchEvent('afterPageHeaderClose')

{!! $tpl->displayNotification() !!}

<div class="maincontent">

    <div class="row">
        <div class="col-md-2">
            <div class="maincontentinner">
                <h5 class="subtitle tw:pb-m">Calendars</h5>

                <ul class="simpleList">
                    <li><span class="indicatorCircle" style="background:var(--accent1)"></span>Events</li>
                    <li><span class="indicatorCircle" style="background:var(--accent2)"></span>Projects & Tasks</li>

                @foreach($tpl->get('externalCalendars') as $calendars)
                    <li>
                        @if(empty($calendars['managedByPlugin']))
                        <x-globals::actions.dropdown-menu style="float:right;" leading-visual="more_horiz">
                            <li>
                                <a href="#/calendar/editExternal/{{ $calendars['id'] }}"><x-global::elements.icon name="edit_square" /> {{ __('links.edit_calendar') }}</a>
                            </li>
                            <li><a href="#/calendar/delExternalCalendar/{{ $calendars['id'] }}" class="delete"><x-global::elements.icon name="delete" /> {{ __('links.delete_external_calendar') }}</a></li>
                        </x-globals::actions.dropdown-menu>
                        @endif
                        <span class="indicatorCircle" style="background:{{ $calendars['colorClass'] }}"></span>{{ $calendars['name'] }}

                    </li>
                @endforeach

                </ul>
                <hr />
                <a href="#/calendar/connectCalendar" class="formModal" style="display:block; margin-bottom:8px; margin-left:-5px;"><x-global::elements.icon name="calendar_add_on" style="width:16px;" /> {{ __('label.connect_calendar') }}</a>
                <a href="#/calendar/calendarSettings" class="formModal" style="margin-left:-5px;"><x-global::elements.icon name="settings" style="width:16px;" /> {{ __('label.calendar_settings') }}</a>
            </div>
        </div>
        <div class="col-md-10">
            <div class="maincontentinner">
                <div class="tw:flex tw:items-center tw:flex-wrap tw:gap-2 tw:mb-4">
                    <x-globals::forms.button link="#/calendar/addEvent" type="primary" formModal><x-global::elements.icon name="add" /> {{ __('buttons.add_event') }}</x-globals::forms.button>

                    <div class="tw:flex-1"></div>

                    <div id="calendarTitle" style="white-space:nowrap;">
                        <h2 style="margin:0; font-size:var(--font-size-xl); font-weight:600;">..</h2>
                    </div>

                    <div class="tw:flex-1"></div>

                    <x-globals::forms.select :bare="true" name="calendarView" id="my-select">
                        <option value="timeGridDay" {{ session('usersettings.submenuToggle.myCalendarView') == 'timeGridDay' ? 'selected' : '' }}>Day</option>
                        <option value="timeGridWeek" {{ session('usersettings.submenuToggle.myCalendarView') == 'timeGridWeek' ? 'selected' : '' }}>Week</option>
                        <option value="dayGridMonth" {{ session('usersettings.submenuToggle.myCalendarView') == 'dayGridMonth' ? 'selected' : '' }}>Month</option>
                        <option value="multiMonthYear" {{ session('usersettings.submenuToggle.myCalendarView') == 'multiMonthYear' ? 'selected' : '' }}>Year</option>
                    </x-globals::forms.select>

                    <button class="fc-today-button btn btn-default" type="button">today</button>

                    <div class="tw:flex tw:items-center tw:gap-1">
                        <button class="fc-prev-button btn btn-default" type="button">
                            <x-global::elements.icon name="chevron_left" />
                        </button>
                        <button class="fc-next-button btn btn-default" type="button">
                            <x-global::elements.icon name="chevron_right" />
                        </button>
                    </div>

                    <x-globals::forms.button link="#/calendar/export" type="secondary" formModal>Export</x-globals::forms.button>
                </div>
                <div id="calendar"></div>
            </div>
        </div>
    </div>


</div>


<script type="text/javascript">

    @dispatchEvent('scripts.afterOpen')


    jQuery(document).ready(function() {

        //leantime.calendarController.initCalendar(events);
        leantime.calendarController.initExportModal();

    });
    var eventSources = [];

    var events = {events: [
        @foreach($tpl->get('calendar') as $calendar)
        {
            title: {!! json_encode($calendar['title']) !!},

            start: new Date({{ format($calendar['dateFrom'])->jsTimestamp() }}),
            @if(isset($calendar['dateTo']))
            end: new Date({{ format($calendar['dateTo'])->jsTimestamp() }}),
            @endif
            @if(isset($calendar['allDay']) && $calendar['allDay'] === true)
            allDay: true,
            @else
            allDay: false,
            @endif
            enitityId: {{ $calendar['id'] }},
            @if(isset($calendar['eventType']) && $calendar['eventType'] == 'calendar')
            url: '{{ CURRENT_URL }}#/calendar/editEvent/{{ $calendar['id'] }}',
            backgroundColor: '{{ $calendar['backgroundColor'] ?? 'var(--accent2)' }}',
            borderColor: '{{ $calendar['borderColor'] ?? 'var(--accent2)' }}',
            enitityType: "event",
            dateContext: '{{ $calendar['dateContext'] ?? 'plan' }}',
            @else
            url: '{{ CURRENT_URL }}#/tickets/showTicket/{{ $calendar['id'] }}?projectId={{ $calendar['projectId'] }}',
            backgroundColor: '{{ $calendar['backgroundColor'] ?? 'var(--accent2)' }}',
            borderColor: '{{ $calendar['borderColor'] ?? 'var(--accent2)' }}',
            enitityType: "ticket",
            dateContext: '{{ $calendar['dateContext'] ?? 'edit' }}',
            @endif
        },
        @endforeach
    ]};

    eventSources.push(events);

    @php
        $externalCalendars = $tpl->get('externalCalendars');
    @endphp
    @foreach($externalCalendars as $externalCalendar)
        eventSources.push(
            {
                url: '{{ BASE_URL }}/calendar/externalCal/{{ $externalCalendar['id'] }}',
                format: 'ics',
                color: '{{ $externalCalendar['colorClass'] }}',
                editable: false,
            }
        );
    @endforeach


    document.addEventListener('DOMContentLoaded', function() {
        const heightWindow = jQuery("body").height() - 210;

        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: leantime.i18n.__("usersettings.timezone"),
                height: 'calc(100% - 40px)',
                stickyHeaderDates: true,
                initialView: '{{ session('usersettings.submenuToggle.myCalendarView') }}',
                eventSources:eventSources,
                editable: true,
                headerToolbar: false,
                dayHeaderFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "luxon"),
                eventTimeFormat: leantime.dateHelper.getFormatFromSettings("timeformat", "luxon"),
                slotLabelFormat: leantime.dateHelper.getFormatFromSettings("timeformat", "luxon"),
                views: {
                    timeGridDay: {

                    },
                    timeGridWeek: {

                    },
                    dayGridMonth: {
                        dayHeaderFormat: { weekday: 'short' },
                    },
                    multiMonthYear: {
                        showNonCurrentDates: true,
                        multiMonthTitleFormat: { month: 'long', year: 'numeric' },
                        dayHeaderFormat: { weekday: 'short' },
                    },
                    multiMonthOneMonth: {
                        type: 'multiMonth',
                        duration: {months: 1},
                        multiMonthTitleFormat: {month: 'long', year: 'numeric'},
                        dayHeaderFormat: {weekday: 'short'},
                    },
                    listWeek: {
                        listDayFormat: {weekday: 'long'},
                        listDaySideFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "luxon"),
                    }
                },
                nowIndicator: true,
                bootstrapFontAwesome: {
                    close: 'fa-times',
                    prev: 'fa-chevron-left',
                    next: 'fa-chevron-right',
                    prevYear: 'fa-angle-double-left',
                    nextYear: 'fa-angle-double-right'
                },
                eventDrop: function (event) {

                    if(event.event.extendedProps.enitityType == "ticket") {

                        let dataVal = {};

                        if(event.event.extendedProps.dateContext == "due") {

                            dataVal = {
                                id: event.event.extendedProps.enitityId,
                                dateToFinish: event.event.startStr
                            }

                        }else{
                            dataVal = {
                                id: event.event.extendedProps.enitityId,
                                editFrom: event.event.startStr,
                                editTo: event.event.endStr
                            }
                        }

                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/tickets',
                            data : dataVal
                        });

                    }else if(event.event.extendedProps.enitityType == "event") {

                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/calendar',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                dateFrom: event.event.startStr,
                                dateTo: event.event.endStr
                            }
                        })
                    }
                },
                eventResize: function (event) {

                    if(event.event.extendedProps.enitityType == "ticket") {

                        let dataVal = {};

                        if(event.event.extendedProps.dateContext == "due") {

                            dataVal = {
                                id: event.event.extendedProps.enitityId,
                                dateToFinish: event.event.startStr
                            }

                        }else{
                            dataVal = {
                                id: event.event.extendedProps.enitityId,
                                editFrom: event.event.startStr,
                                editTo: event.event.endStr
                            }
                        }

                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/tickets',
                            data : dataVal
                        });


                    }else if(event.event.extendedProps.enitityType == "event") {

                        jQuery.ajax({
                            type : 'PATCH',
                            url  : leantime.appUrl + '/api/calendar',
                            data : {
                                id: event.event.extendedProps.enitityId,
                                dateFrom: event.event.startStr,
                                dateTo: event.event.endStr
                            }
                        })
                    }

                },
                eventMouseEnter: function() {
                },

            }
            );
        calendar.setOption('locale', leantime.i18n.__("language.code"));
        calendar.render();
        calendar.scrollToTime( Date.now() );
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
                    submenu : "myCalendarView",
                    state   : jQuery("#my-select option:selected").val()
                }
            });

        });
    });

    @dispatchEvent('scripts.beforeClose')

</script>

<style type="text/css">
    .maincontent .maincontentinner {
        height:calc(100vh - 165px);
    }
</style>
