// Global components — all expose globals
import { DateTime, Duration, Interval, Info, Settings } from 'luxon';
window.luxon = { DateTime, Duration, Interval, Info, Settings };

import moment from 'moment';
window.moment = moment;

import flatpickr from 'flatpickr';
window.flatpickr = flatpickr;

import Sortable from 'sortablejs';
window.Sortable = Sortable;

import { createPopper } from '@popperjs/core';
window.Popper = { createPopper };

import tippy from 'tippy.js';
window.tippy = tippy;

import '../../public/assets/js/libs/slimselect.min.js';

import confetti from 'canvas-confetti';
window.confetti = confetti;

// nyroModal removed — replaced by modalManager.js (native <dialog>)
import '../../public/assets/js/libs/uppy/uppy.js';

import Croppie from 'croppie';
window.Croppie = Croppie;

import Packery from 'packery';
window.Packery = Packery;

import imagesLoaded from 'imagesloaded';
window.imagesLoaded = imagesLoaded;

import Shepherd from 'shepherd.js';
window.Shepherd = Shepherd;

import Isotope from 'isotope-layout';
window.Isotope = Isotope;

import GridStackAll from 'gridstack/dist/gridstack-all.js';
window.GridStack = GridStackAll.GridStack || GridStackAll;
import 'jstree';
import '@assuradeurengilde/fontawesome-iconpicker';
// LeaderLine uses `var LeaderLine = function(){...}()` — a plain script global.
// Vite wraps it as a module, making the var local. Use indirect eval to run
// the source in the global scope so `var LeaderLine` becomes `window.LeaderLine`.
import leaderLineSrc from 'leader-line/leader-line.min.js?raw';
(0, eval)(leaderLineSrc);
// simple-color-picker removed — replaced by native <input type="color">
import '../../public/assets/js/libs/emojipicker/vanillaEmojiPicker.js';

import mermaid from 'mermaid';
window.mermaid = mermaid;

import { marked } from 'marked';
window.marked = marked;
