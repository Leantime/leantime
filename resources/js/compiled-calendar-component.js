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

// Register add-on plugins globally so all Calendar instances can use them.
// The `fullcalendar` bundle already registers core plugins (daygrid, timegrid,
// list, etc.) but Luxon3, iCalendar and Google Calendar are separate packages.
[ICalendarPlugin, GoogleCalendarPlugin, Luxon3Plugin].forEach(function (mod) {
    if (mod.default && mod.default.id) {
        window.FullCalendar.globalPlugins.push(mod.default);
    }
});
