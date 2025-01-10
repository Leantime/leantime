// Essential libraries that need to be globally available
import jQuery from 'jquery';
import 'jquery-ui-dist/jquery-ui.js'
jQuery.noConflict();
window.jQuery = jQuery;

import htmx from "htmx.org";
window.htmx = htmx
htmx.config.defaultSettleDelay = 0;

import confetti from 'canvas-confetti';
window.confetti = confetti;

import tippy from "tippy.js";
window.tippy = tippy;

import {DateTime} from "luxon";
import "chartjs-adapter-luxon";
window.DateTime = DateTime;

import instanceInfo from './instance-info.module.mjs';
import snippets from "../support/snippets.module.mjs";
import dateHelper from '../support/dateHelper.module.mjs';
import handleAsyncResponse from './handleAsyncResponse.module.mjs';
import getLatestGrowl from "./getLatestGrowl.module.mjs";


import { addToGlobalScope } from "./leantimeScope.mjs"
addToGlobalScope({
    dateHelper: dateHelper,
    getLatestGrowl: getLatestGrowl,
    handleAsyncResponse: handleAsyncResponse,
    instanceInfo: instanceInfo,
    snippets: snippets,
});


