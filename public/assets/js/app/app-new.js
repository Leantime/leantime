function addToGlobalScope(object) {
    window.leantime = {
        ...(window.leantime || {}),
        ...object,
    };
}

/* HTMX */
import htmx from 'htmx.org';
htmx.config.defaultSettleDelay=0;
window.htmx = htmx;
require('htmx-ext-head-support');


/* jQuery */
import jQuery from 'jquery';
jQuery.noConflict();
window.jQuery = jQuery;

import 'jquery-ui-dist/jquery-ui';

// TODO: Replace this dependency with https://github.com/leantime/choices
import 'chosen-js/chosen.jquery';

// TODO: Replace this dependency with https://github.com/leantime/choices
import 'js/libs/jquery.tagsinput.min';
import 'croppie/croppie';
import 'packery/dist/packery.pkgd';
import 'imagesloaded/imagesloaded.pkgd';
import 'jstree/dist/jstree';
import 'js/libs/bootstrap-dropdown';
import '@assuradeurengilde/fontawesome-iconpicker/dist/js/fontawesome-iconpicker';
import 'js/libs/simple-color-picker-master/jquery.simple-color-picker';

import moment from 'moment';
window.moment = moment;

/*
 * Uppy
 * TODO: Replace with node module (probably upgrade to version 4)
 */
import 'js/libs/uppy/uppy';

/* isotope */
import Isotope from 'isotope-layout';
import jQueryBridget from 'jquery-bridget';
jQueryBridget('isotope', Isotope, jQuery);

/* LeaderLine */
import LeaderLine from 'leader-line';
window.LeaderLine = LeaderLine;

/* Tippy */
import tippy from 'tippy.js';
window.tippy = tippy;


/* Datatables */
import 'datatables.net';
import 'datatables.net-rowgroup';
import 'datatables.net-rowreorder';
import 'datatables.net-buttons';

/* Chart.js */
import 'chartjs-adapter-luxon';


/* Core */
import modals from './core/modals.module.mjs';

import dateHelper from './core/dateHelper.module.mjs';
import editorController from './core/editors.module';
import getLatestGrowl from './core/getLatestGrowl.module.mjs';
import handleAsyncResponse from './core/handleAsyncResponse.module.mjs';
import instanceInfo from './core/instance-info.module.mjs';

import onDocumentReady from './core/on-document-ready.module.mjs';
import replaceSVGColors from './core/replaceSVGColors.module.mjs';
import snippets from './core/snippets.module';

import selects from './core/selects.module';
import datePickers from './core/datePickers.module.mjs';

addToGlobalScope({
    dateHelper: dateHelper,
    editorController: editorController,
    getLatestGrowl: getLatestGrowl,
    handleAsyncResponse: handleAsyncResponse,
    instanceInfo: instanceInfo,
    modals: modals,
    replaceSVGColors: replaceSVGColors,
    snippets: snippets,
    selects:selects,
    datePickers:datePickers
});

jQuery(document).ready(onDocumentReady);
window.addEventListener("HTMX.ShowNotification", getLatestGrowl);

/* Domain */
import usersService from 'domain/Users/Js/usersService';
import wikiController from 'domain/Wiki/Js/wikiController';
import authController from 'domain/Auth/Js/authController';
import menuController from 'domain/Menu/Js/menuController';
import menuRepository from 'domain/Menu/Js/menuRepository';
import helperRepository from 'domain/Help/Js/helperRepository';
import helperController from 'domain/Help/Js/helperController';
import ideasController from 'domain/Ideas/Js/ideasController';
import usersController from 'domain/Users/Js/usersController';
import usersRepository from 'domain/Users/Js/usersRepository';
import settingService from 'domain/Setting/Js/settingService';
import canvasController from 'domain/Canvas/Js/canvasController';
import Widgetcontroller from 'domain/Widgets/Js/Widgetcontroller';
import clientsController from 'domain/Clients/Js/clientsController';
import ticketsController from 'domain/Tickets/Js/ticketsController';
import ticketsRepository from 'domain/Tickets/Js/ticketsRepository';
import settingRepository from 'domain/Setting/Js/settingRepository';
import settingController from 'domain/Setting/Js/settingController';
import cpCanvasController from 'domain/Cpcanvas/Js/cpCanvasController';
import projectsController from 'domain/Projects/Js/projectsController';
import emCanvasController from 'domain/Emcanvas/Js/emCanvasController';
import sqCanvasController from 'domain/Sqcanvas/Js/sqCanvasController';
import commentsComponent from 'domain/Comments/Js/commentsComponent';
import smCanvasController from 'domain/Smcanvas/Js/smCanvasController';
import calendarController from 'domain/Calendar/Js/calendarController';
import sbCanvasController from 'domain/Sbcanvas/Js/sbCanvasController';
import eaCanvasController from 'domain/Eacanvas/Js/eaCanvasController';
import reactionsController from 'domain/Reactions/Js/reactionsController';
import dbmCanvasController from 'domain/Dbmcanvas/Js/dbmCanvasController';
import lbmCanvasController from 'domain/Lbmcanvas/Js/lbmCanvasController';
import dashboardController from 'domain/Dashboard/Js/dashboardController';
import obmCanvasController from 'domain/Obmcanvas/Js/obmCanvasController';
import timesheetsController from 'domain/Timesheets/Js/timesheetsController';
import swotCanvasController from 'domain/Swotcanvas/Js/swotCanvasController';
import leanCanvasController from 'domain/Leancanvas/Js/leanCanvasController';
import goalCanvasController from 'domain/Goalcanvas/Js/goalCanvasController';
import valueCanvasController from 'domain/Valuecanvas/Js/valueCanvasController';
import risksCanvasController from 'domain/Riskscanvas/Js/risksCanvasController';
import retroCanvasController from 'domain/Retroscanvas/Js/retroCanvasController';
import minempathyCanvasController from 'domain/Minempathycanvas/Js/risksCanvasController';
import insightsCanvasController from 'domain/Insightscanvas/Js/insightsCanvasController';

addToGlobalScope({
    usersService: usersService,
    wikiController: wikiController,
    authController: authController,
    menuController: menuController,
    menuRepository: menuRepository,
    helperRepository: helperRepository,
    helperController: helperController,
    ideasController: ideasController,
    usersController: usersController,
    usersRepository: usersRepository,
    settingService: settingService,
    canvasController: canvasController,
    widgetController: Widgetcontroller,
    clientsController: clientsController,
    ticketsController: ticketsController,
    ticketsRepository: ticketsRepository,
    settingRepository: settingRepository,
    settingController: settingController,
    cpCanvasController: cpCanvasController,
    projectsController: projectsController,
    emCanvasController: emCanvasController,
    sqCanvasController: sqCanvasController,
    commentsComponent: commentsComponent,
    smCanvasController: smCanvasController,
    calendarController: calendarController,
    sbCanvasController: sbCanvasController,
    eaCanvasController: eaCanvasController,
    reactionsController: reactionsController,
    dbmCanvasController: dbmCanvasController,
    lbmCanvasController: lbmCanvasController,
    dashboardController: dashboardController,
    obmCanvasController: obmCanvasController,
    timesheetsController: timesheetsController,
    swotCanvasController: swotCanvasController,
    leanCanvasController: leanCanvasController,
    goalCanvasController: goalCanvasController,
    valueCanvasController: valueCanvasController,
    risksCanvasController: risksCanvasController,
    retroCanvasController: retroCanvasController,
    minempathyCanvasController: minempathyCanvasController,
    insightsCanvasController: insightsCanvasController,
});
