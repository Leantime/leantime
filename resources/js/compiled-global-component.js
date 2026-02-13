// Global components — all expose globals
import { DateTime, Duration, Interval, Info, Settings } from 'luxon';
window.luxon = { DateTime, Duration, Interval, Info, Settings };

import moment from 'moment';
window.moment = moment;

import '../../public/assets/js/libs/jquery.form.js';

import { createPopper } from '@popperjs/core';
window.Popper = { createPopper };

import tippy from 'tippy.js';
window.tippy = tippy;

import '../../public/assets/js/libs/slimselect.min.js';

import confetti from 'canvas-confetti';
window.confetti = confetti;

import '../../public/assets/js/libs/jquery.nyroModal/js/jquery.nyroModal.custom.js';
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

import 'gridstack/dist/gridstack-all.js';
import 'jstree';
import '@assuradeurengilde/fontawesome-iconpicker';
import 'leader-line/leader-line.min.js';
import '../../public/assets/js/libs/simple-color-picker-master/jquery.simple-color-picker.js';
import '../../public/assets/js/libs/emojipicker/vanillaEmojiPicker.js';

import mermaid from 'mermaid';
window.mermaid = mermaid;

import { marked } from 'marked';
window.marked = marked;
