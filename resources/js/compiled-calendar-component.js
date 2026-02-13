// Calendar — FullCalendar with plugins (global UMD builds)
// These need to be imported via direct file paths because the packages'
// exports maps don't expose the .global.min.js files to Vite's resolver.
import '../../node_modules/ical.js/build/ical.min.js';
import '../../node_modules/fullcalendar/index.global.min.js';
import '../../node_modules/@fullcalendar/icalendar/index.global.min.js';
import '../../node_modules/@fullcalendar/google-calendar/index.global.min.js';
import '../../node_modules/@fullcalendar/luxon3/index.global.min.js';
