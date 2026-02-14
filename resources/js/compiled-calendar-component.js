// Calendar — FullCalendar with plugins (ESM imports)
// UMD .global.min.js builds don't set window globals when processed by Vite,
// so we import the ESM versions and expose FullCalendar as a global namespace.
import '../../node_modules/ical.js/build/ical.min.js';
import * as FullCalendar from 'fullcalendar';
import * as ICalendarPlugin from '@fullcalendar/icalendar';
import * as GoogleCalendarPlugin from '@fullcalendar/google-calendar';
import * as Luxon3Plugin from '@fullcalendar/luxon3';

// Expose FullCalendar on window for inline scripts (e.g. calendarController.js)
window.FullCalendar = Object.assign(
    {},
    FullCalendar,
    ICalendarPlugin,
    GoogleCalendarPlugin,
    Luxon3Plugin
);
