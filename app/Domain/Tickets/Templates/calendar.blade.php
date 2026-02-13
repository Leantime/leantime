@php
    $milestones = $tpl->get('milestones');
    if (!session()->exists('usersettings.submenuToggle.myProjectCalendarView')) {
        session(['usersettings.submenuToggle.myProjectCalendarView' => 'dayGridMonth']);
    }
@endphp

{!! $tpl->displayNotification() !!}

@php $tpl->displaySubmodule('tickets-timelineHeader') @endphp

<div class="maincontent">
    @php $tpl->displaySubmodule('tickets-timelineTabs') @endphp
    <div class="maincontentinner">

        <div class="row">
            <div class="col-md-4">
                @dispatchEvent('filters.afterLefthandSectionOpen')
                @php
                    $tpl->displaySubmodule('tickets-ticketNewBtn');
                    $tpl->displaySubmodule('tickets-ticketFilter');
                @endphp
                @dispatchEvent('filters.beforeLefthandSectionClose')
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
                    <option class="fc-timeGridDay-button fc-button fc-state-default fc-corner-right" value="timeGridDay" {{ session('usersettings.submenuToggle.myProjectCalendarView') == 'timeGridDay' ? 'selected' : '' }}>Day</option>
                    <option class="fc-timeGridWeek-button fc-button fc-state-default fc-corner-right" value="timeGridWeek" {{ session('usersettings.submenuToggle.myProjectCalendarView') == 'timeGridWeek' ? 'selected' : '' }}>Week</option>
                    <option class="fc-dayGridMonth-button fc-button fc-state-default fc-corner-right" value="dayGridMonth" {{ session('usersettings.submenuToggle.myProjectCalendarView') == 'dayGridMonth' ? 'selected' : '' }}>Month</option>
                    <option class="fc-multiMonthYear-button fc-button fc-state-default fc-corner-right" value="multiMonthYear" {{ session('usersettings.submenuToggle.myProjectCalendarView') == 'multiMonthYear' ? 'selected' : '' }}>Year</option>
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

    @if(isset($_GET['showMilestoneModal']))
        @php
            $modalUrl = $_GET['showMilestoneModal'] == '' ? '' : '/' . (int)$_GET['showMilestoneModal'];
        @endphp
        leantime.ticketsController.openMilestoneModalManually("{{ BASE_URL }}/tickets/editMilestone{{ $modalUrl }}");
        window.history.pushState({},document.title, '{{ BASE_URL }}/tickets/roadmap');
    @endif

    });

    var events = [
        @foreach($milestones as $mlst)
            @php
                $headline = __('label.' . strtolower($mlst->type)) . ': ' . $mlst->headline;
                if ($mlst->type == 'milestone') {
                    $headline .= ' (' . $mlst->percentDone . '% Done)';
                }

                $color = '#8D99A6';
                if ($mlst->type == 'milestone') {
                    $color = $mlst->tags;
                }
            @endphp
        {
            title: {!! json_encode($headline) !!},

            @if(dtHelper()->isValidDateString($mlst->dateToFinish))
                start: new Date({{ format($mlst->dateToFinish)->jsTimestamp() }}),
                end: new Date({{ format(dtHelper()->parseDbDateTime($mlst->dateToFinish)->addHour(1))->jsTimestamp() }}),
            @elseif(dtHelper()->isValidDateString($mlst->editFrom))
                start: new Date({{ format($mlst->editFrom)->jsTimestamp() }}),
                end: new Date({{ format($mlst->editTo)->jsTimestamp() }}),
            @endif

            enitityId: {{ $mlst->id }},
            @if($mlst->type == 'milestone')
            url: '#/tickets/editMilestone/{{ $mlst->id }}',
            color: '{{ $color }}',
            enitityType: "milestone",
            allDay: true,
            @else
            url: '#/tickets/showTicket/{{ $mlst->id }}',
            color: '{{ $color }}',
            enitityType: "ticket",
            allDay: false,
            @endif
        },
        @endforeach
    ];

    document.addEventListener('DOMContentLoaded', function() {
        const heightWindow = jQuery("body").height() - 190;

        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
                timeZone: leantime.i18n.__("usersettings.timezone"),
                height:heightWindow,
                initialView: '{{ session('usersettings.submenuToggle.myProjectCalendarView') }}',
                events: events,
                editable: true,
                headerToolbar: false,
                dayHeaderFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "luxon"),
                eventTimeFormat: leantime.dateHelper.getFormatFromSettings("timeformat", "luxon"),
                slotLabelFormat: leantime.dateHelper.getFormatFromSettings("timeformat", "luxon"),
                views: {
                    timeGridDay: {},
                    timeGridWeek: {},
                    dayGridMonth: {
                        dayHeaderFormat: { weekday: 'short' },
                    },
                    multiMonthYear: {
                        showNonCurrentDates: true,
                        multiMonthTitleFormat: { month: 'long', year: 'numeric' },
                        dayHeaderFormat: { weekday: 'short' },
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
