// Gantt chart — Emboss (replaces frappe-gantt)
// The adapter converts Leantime's date-string task format to Emboss's
// day-offset Row format and wires events to existing persistence APIs.

// Leantime theme overrides for Emboss — imported here (not in main.css)
// because Tailwind v4 purges .emboss-* classes that only exist at runtime.
import '../../public/assets/css/components/gantt-overwrites.css';

import './embossAdapter.js';
