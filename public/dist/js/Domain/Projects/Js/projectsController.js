"use strict";
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory(require("window.leantime.i18n"));
	else if(typeof define === 'function' && define.amd)
		define(["window.leantime.i18n"], factory);
	else {
		var a = typeof exports === 'object' ? factory(require("window.leantime.i18n")) : factory(root["window.leantime.i18n"]);
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(self, (__WEBPACK_EXTERNAL_MODULE_i18n__) => {
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Projects/Js/projectsController"],{

/***/ "./app/Domain/Projects/Js/projectsController.js":
/*!******************************************************!*\
  !*** ./app/Domain/Projects/Js/projectsController.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addToDoStatus: () => (/* binding */ addToDoStatus),
/* harmony export */   clearCroppie: () => (/* binding */ clearCroppie),
/* harmony export */   countTickets: () => (/* binding */ countTickets),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   favoriteProject: () => (/* binding */ favoriteProject),
/* harmony export */   initDates: () => (/* binding */ initDates),
/* harmony export */   initGanttChart: () => (/* binding */ initGanttChart),
/* harmony export */   initProgressBar: () => (/* binding */ initProgressBar),
/* harmony export */   initProjectTable: () => (/* binding */ initProjectTable),
/* harmony export */   initProjectTabs: () => (/* binding */ initProjectTabs),
/* harmony export */   initProjectsKanban: () => (/* binding */ initProjectsKanban),
/* harmony export */   initSelectFields: () => (/* binding */ initSelectFields),
/* harmony export */   initTodoStatusSortable: () => (/* binding */ initTodoStatusSortable),
/* harmony export */   readURL: () => (/* binding */ readURL),
/* harmony export */   removeStatus: () => (/* binding */ removeStatus),
/* harmony export */   saveCroppie: () => (/* binding */ saveCroppie),
/* harmony export */   setUpKanbanColumns: () => (/* binding */ setUpKanbanColumns)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var js_app_components_dates_dateHelper_module__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! js/app/components/dates/dateHelper.module */ "./public/assets/js/app/components/dates/dateHelper.module.mjs");
/* harmony import */ var domain_Users_Js_usersRepository__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! domain/Users/Js/usersRepository */ "./app/Domain/Users/Js/usersRepository.js");
/* harmony import */ var domain_Tickets_Js_ticketsRepository__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! domain/Tickets/Js/ticketsRepository */ "./app/Domain/Tickets/Js/ticketsRepository.js");
/* harmony import */ var domain_Reactions_Js_reactionsController__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! domain/Reactions/Js/reactionsController */ "./app/Domain/Reactions/Js/reactionsController.js");
/* harmony import */ var js_libs_simpleGantt_frappe_gantt__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! js/libs/simpleGantt/frappe-gantt */ "./public/assets/js/libs/simpleGantt/frappe-gantt.js");








var countTickets = function countTickets() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableTicketKanban .column").each(function () {
    var counting = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).find('.moveable').length;
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).find(' .count').text(counting);
  });
};
var initDates = function initDates() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".projectDateFrom, .projectDateTo").datepicker({
    dateFormat: (0,js_app_components_dates_dateHelper_module__WEBPACK_IMPORTED_MODULE_3__.getFormatFromSettings)("dateformat", "jquery"),
    dayNames: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.dayNames").split(","),
    dayNamesMin: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.dayNamesMin").split(","),
    dayNamesShort: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.dayNamesShort").split(","),
    monthNames: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.monthNames").split(","),
    currentText: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.currentText"),
    closeText: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.closeText"),
    buttonText: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.buttonText"),
    isRTL: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.isRTL") === "true" ? 1 : 0,
    nextText: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.nextText"),
    prevText: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.prevText"),
    weekHeader: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.weekHeader")
  });
};
var initProjectTabs = function initProjectTabs() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.projectTabs').tabs();
};
var initProgressBar = function initProgressBar(percentage) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#progressbar").progressbar({
    value: percentage
  });
};
var initProjectTable = function initProjectTable() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    var size = 100;
    var allProjects = jquery__WEBPACK_IMPORTED_MODULE_0___default()("#allProjectsTable").DataTable({
      "language": {
        "decimal": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.decimal"),
        "emptyTable": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.emptyTable"),
        "info": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.info"),
        "infoEmpty": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.infoEmpty"),
        "infoFiltered": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.infoFiltered"),
        "infoPostFix": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.infoPostFix"),
        "thousands": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.thousands"),
        "lengthMenu": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.lengthMenu"),
        "loadingRecords": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.loadingRecords"),
        "processing": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.processing"),
        "search": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.search"),
        "zeroRecords": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.zeroRecords"),
        "paginate": {
          "first": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.first"),
          "last": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.last"),
          "next": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.next"),
          "previous": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.previous")
        },
        "aria": {
          "sortAscending": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.sortAscending"),
          "sortDescending": i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.sortDescending")
        }
      },
      "dom": '<"top">rt<"bottom"ilp><"clear">',
      "searching": false,
      "displayLength": 100
    });
  });
};
var initTodoStatusSortable = function initTodoStatusSortable(element) {
  var sortCounter = 1;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).find("input.sorter").each(function (index) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).val(sortCounter);
    sortCounter++;
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).sortable({
    stop: function stop(event, ui) {
      sortCounter = 1;
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).find("input.sorter").each(function (index) {
        jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).val(sortCounter);
        sortCounter++;
      });
    }
  });
};
var initSelectFields = function initSelectFields() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#todosettings select.colorChosen").on('chosen:ready', function (e, params) {
      var id = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).attr('id').replace("-", "_");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + id + "_chosen a span").removeClass();
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + id + "_chosen a span").addClass(params.selected);
    }).chosen({
      disable_search_threshold: 10
    });
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#todosettings select.colorChosen").on('change', function (evt, params) {
      var id = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).attr('id').replace("-", "_");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + id + "_chosen a span").removeClass();
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + id + "_chosen a span").addClass(params.selected);
    });
  });
};
var removeStatus = function removeStatus(id) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#todostatus-" + id).parent().remove();
};
var addToDoStatus = function addToDoStatus(id) {
  var highestKey = -1;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#todosettings ul .statusList").each(function () {
    var keyInt = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).find('.labelKey').val();
    if (parseInt(keyInt) >= parseInt(highestKey)) {
      highestKey = keyInt;
    }
  });
  var newKey = parseInt(highestKey) + 1;
  var statusCopy = jquery__WEBPACK_IMPORTED_MODULE_0___default()(".newStatusTpl").clone();
  statusCopy.html(function (i, oldHTML) {
    return updatedContent = oldHTML.replaceAll('XXNEWKEYXX', newKey);
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#todoStatusList').append("<li>" + statusCopy.html() + "</li>");
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#todosettings select.colorChosen").chosen("destroy");
  initSelectFields();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#todoStatusList").sortable("destroy");
  initTodoStatusSortable("#todoStatusList");
};
var readURL = function readURL(input) {
  clearCroppie();
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    var profileImg = jquery__WEBPACK_IMPORTED_MODULE_0___default()('#projectAvatar');
    reader.onload = function (e) {
      //profileImg.attr('src', e.currentTarget.result);

      _uploadResult = profileImg.croppie({
        enableExif: true,
        viewport: {
          width: 200,
          height: 200,
          type: 'rectangle'
        },
        boundary: {
          width: 250,
          height: 250
        }
      });
      _uploadResult.croppie('bind', {
        url: e.currentTarget.result
      });
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("#previousImage").hide();
    };
    reader.readAsDataURL(input.files[0]);
  }
};
var clearCroppie = function clearCroppie() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#profileImg').croppie('destroy');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#previousImage").show();
};
var saveCroppie = function saveCroppie() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#save-picture').addClass('running');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#profileImg').attr('src', js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/images/loaders/loader28.gif');
  _uploadResult.croppie('result', {
    type: "blob",
    circle: false
  }).then(function (result) {
    var formData = new FormData();
    formData.append('file', result);
    jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
      type: 'POST',
      url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/api/projects',
      data: formData,
      processData: false,
      contentType: false,
      success: function success(resp) {
        jquery__WEBPACK_IMPORTED_MODULE_0___default()('#save-picture').removeClass('running');
        location.reload();
      },
      error: function error(err) {
        console.log(err);
      }
    });
  });
};
var initGanttChart = function initGanttChart(projects, viewMode, readonly) {
  function htmlEntities(str) {
    return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    if (readonly === false) {
      var gantt_chart = new js_libs_simpleGantt_frappe_gantt__WEBPACK_IMPORTED_MODULE_7__["default"]("#gantt", projects, {
        header_height: 55,
        column_width: 20,
        step: 24,
        view_modes: ['Day', 'Week', 'Month'],
        bar_height: 40,
        static_progress_indicator: true,
        bar_corner_radius: 5,
        arrow_curve: 5,
        padding: 20,
        view_mode: 'Month',
        date_format: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.momentJSDate"),
        language: 'en',
        // or 'es', 'it', 'ru', 'ptBr', 'fr', 'tr', 'zh'
        additional_rows: 5,
        custom_popup_html: function custom_popup_html(project) {
          // the task object will contain the updated
          // dates and progress value
          var end_date = project._end;
          var popUpHTML = '<div class="details-container" style="min-width:600px;"> ';
          if (project.projectName !== undefined) {
            popUpHTML += '<h3><b>' + project.name + '</b></h3>';
          }
          popUpHTML += '<h4>' + htmlEntities(project.name) + '</a></h4><br /> ';
          popUpHTML += '</div>';
          return popUpHTML;
        },
        on_click: function on_click(project) {},
        on_date_change: function on_date_change(project, start, end) {
          var idParts = project.id.split("-");
          var entityId = 0;
          var entityType = "";
          if (idParts.length > 1) {
            if (idParts[0] == "ticket") {
              entityId = idParts[1];
              entityType = "ticket";
            } else if (idParts[0] == "pgm") {
              entityId = idParts[1];
              entityType = "project";
            }
          } else {
            entityId = idParts;
          }
          if (entityType == "ticket") {
            (0,domain_Tickets_Js_ticketsRepository__WEBPACK_IMPORTED_MODULE_5__.updateMilestoneDates)(entityId, start, end, project._index);
          } else {
            jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
              type: 'PATCH',
              url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/api/projects',
              data: {
                id: entityId,
                start: start,
                end: end,
                sortIndex: project._index
              }
            }).done(function () {
              //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
            });
          }
        },
        on_sort_change: function on_sort_change(projects) {
          var statusPostData = {
            action: "ganttSort",
            payload: {}
          };
          for (var i = 0; i < projects.length; i++) {
            statusPostData.payload[projects[i].id] = projects[i]._index;
          }

          // POST to server using $.post or $.ajax
          jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
            type: 'POST',
            url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/api/projects',
            data: statusPostData
          });
        },
        on_progress_change: function on_progress_change(project, progress) {},
        on_view_change: function on_view_change(mode) {
          (0,domain_Users_Js_usersRepository__WEBPACK_IMPORTED_MODULE_4__.updateUserViewSettings)("projectGantt", mode);
        },
        on_popup_show: function on_popup_show(project) {}
      });
    } else {
      var gantt_chart = new js_libs_simpleGantt_frappe_gantt__WEBPACK_IMPORTED_MODULE_7__["default"]("#gantt", projects, {
        readonlyGantt: true,
        resizing: false,
        progress: false,
        is_draggable: false,
        custom_popup_html: function custom_popup_html(project) {
          // the task object will contain the updated
          // dates and progress value
          var end_date = task._end;
          var popUpHTML = '<div class="details-container" style="min-width:600px;"> ';
          if (task.projectName !== undefined) {
            popUpHTML += '<h3><b>' + project.name + '</b></h3>';
          }
          popUpHTML += '<h4>' + htmlEntities(project.name) + '</a></h4><br /> ';
          popUpHTML += '</div>';
          return popUpHTML;
        },
        on_click: function on_click(project) {},
        on_date_change: function on_date_change(project, start, end) {},
        on_progress_change: function on_progress_change(project, progress) {},
        on_view_change: function on_view_change(mode) {
          (0,domain_Users_Js_usersRepository__WEBPACK_IMPORTED_MODULE_4__.updateUserViewSettings)("projectGantt", mode);
        }
      });
    }
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#ganttTimeControl").on("click", "a", function () {
      var $btn = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this);
      var mode = $btn.attr("data-value");
      gantt_chart.change_view_mode(mode);
      $btn.parent().parent().find('a').removeClass('active');
      $btn.addClass('active');
      var label = $btn.text();
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(".viewText").text(label);
    });
    gantt_chart.change_view_mode(viewMode);
  });
};
var setUpKanbanColumns = function setUpKanbanColumns() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    countTickets();
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(".filterBar .row-fluid").css("opacity", "1");
    var height = jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").height() - 250;
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableProjectKanban .column .contentInner").each(function () {
      if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() > height) {
        height = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height();
      }
    });
    height = height + 50;
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableProjectKanban .column .contentInner").css("min-height", height);
  });
};
var initProjectsKanban = function initProjectsKanban(statusList) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableProjectKanban .projectBox").hover(function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).css("background", "var(--kanban-card-hover)");
  }, function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).css("background", "var(--kanban-card-bg)");
  });
  var position_updated = false;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableProjectKanban .contentInner").sortable({
    connectWith: ".contentInner",
    items: "> .moveable",
    tolerance: 'intersect',
    placeholder: "ui-state-highlight",
    forcePlaceholderSize: true,
    cancel: ".portlet-toggle,:input,a,input",
    distance: 25,
    start: function start(event, ui) {
      ui.item.addClass('tilt');
      tilt_direction(ui.item);
    },
    stop: function stop(event, ui) {
      ui.item.removeClass("tilt");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").unbind('mousemove', ui.item.data("move_handler"));
      ui.item.removeData("move_handler");
      countTickets();
      var statusPostData = {
        action: "sortIndex",
        payload: {},
        handler: ui.item[0].id
      };
      for (var i = 0; i < statusList.length; i++) {
        if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(".contentInner.status_" + statusList[i]).length) {
          statusPostData.payload[statusList[i]] = jquery__WEBPACK_IMPORTED_MODULE_0___default()(".contentInner.status_" + statusList[i]).sortable('serialize');
        }
      }

      // POST to server using $.post or $.ajax
      jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
        type: 'POST',
        url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/api/projects',
        data: statusPostData
      });
    }
  });
  function tilt_direction(item) {
    var left_pos = item.position().left,
      move_handler = function move_handler(e) {
        if (e.pageX >= left_pos) {
          item.addClass("right");
          item.removeClass("left");
        } else {
          item.addClass("left");
          item.removeClass("right");
        }
        left_pos = e.pageX;
      };
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").bind("mousemove", move_handler);
    item.data("move_handler", move_handler);
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all").find(".portlet-header").addClass("ui-widget-header ui-corner-all").prepend("<span class='ui-icon ui-icon-minusthick portlet-toggle'></span>");
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".portlet-toggle").click(function () {
    var icon = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this);
    icon.toggleClass("ui-icon-minusthick ui-icon-plusthick");
    icon.closest(".portlet").find(".portlet-content").toggle();
  });
};
var favoriteProject = function favoriteProject(id, element) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).addClass("go");
  if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).hasClass("isFavorite")) {
    (0,domain_Reactions_Js_reactionsController__WEBPACK_IMPORTED_MODULE_6__.removeReaction)('project', id, 'favorite', function () {
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).find("i").removeClass("fa-solid").addClass("fa-regular");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).removeClass("isFavorite");
    });
  } else {
    (0,domain_Reactions_Js_reactionsController__WEBPACK_IMPORTED_MODULE_6__.addReactions)('project', id, 'favorite', function () {
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).find("i").removeClass("fa-regular").addClass("fa-solid");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).addClass("isFavorite");
    });
  }
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initDates: initDates,
  initProjectTabs: initProjectTabs,
  initProgressBar: initProgressBar,
  initProjectTable: initProjectTable,
  initTodoStatusSortable: initTodoStatusSortable,
  initSelectFields: initSelectFields,
  removeStatus: removeStatus,
  addToDoStatus: addToDoStatus,
  saveCroppie: saveCroppie,
  clearCroppie: clearCroppie,
  readURL: readURL,
  initGanttChart: initGanttChart,
  setUpKanbanColumns: setUpKanbanColumns,
  initProjectsKanban: initProjectsKanban,
  favoriteProject: favoriteProject
});

/***/ }),

/***/ "./app/Domain/Reactions/Js/reactionsController.js":
/*!********************************************************!*\
  !*** ./app/Domain/Reactions/Js/reactionsController.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   addReactions: () => (/* binding */ addReactions),
/* harmony export */   reactionsController: () => (/* binding */ reactionsController),
/* harmony export */   removeReaction: () => (/* binding */ removeReaction)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");


var addReactions = function addReactions(module, moduleId, reaction, clb) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'POST',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/reactions',
    data: {
      'action': 'add',
      'module': module,
      'moduleId': moduleId,
      'reaction': reaction
    }
  }).done(function () {
    clb();
  });
};
var removeReaction = function removeReaction(module, moduleId, reaction, clb) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'POST',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/reactions',
    data: {
      'action': 'remove',
      'module': module,
      'moduleId': moduleId,
      'reaction': reaction
    }
  }).done(function () {
    clb();
  });
};
var reactionsController = {
  addReactions: addReactions,
  removeReaction: removeReaction
};

/***/ }),

/***/ "./app/Domain/Tickets/Js/ticketsRepository.js":
/*!****************************************************!*\
  !*** ./app/Domain/Tickets/Js/ticketsRepository.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   updateDueDates: () => (/* binding */ updateDueDates),
/* harmony export */   updateEditFromDates: () => (/* binding */ updateEditFromDates),
/* harmony export */   updateEditToDates: () => (/* binding */ updateEditToDates),
/* harmony export */   updateMilestoneDates: () => (/* binding */ updateMilestoneDates),
/* harmony export */   updatePlannedHours: () => (/* binding */ updatePlannedHours),
/* harmony export */   updateRemainingHours: () => (/* binding */ updateRemainingHours)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var js_app_components_dates_dateHelper_module__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/app/components/dates/dateHelper.module */ "./public/assets/js/app/components/dates/dateHelper.module.mjs");
/* harmony import */ var luxon__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! luxon */ "./node_modules/luxon/src/luxon.js");




var updateMilestoneDates = function updateMilestoneDates(id, start, end, sortIndex) {
  var userDateFormat = (0,js_app_components_dates_dateHelper_module__WEBPACK_IMPORTED_MODULE_2__.getFormatFromSettings)("dateformat", "luxon");
  var userTimeFormat = (0,js_app_components_dates_dateHelper_module__WEBPACK_IMPORTED_MODULE_2__.getFormatFromSettings)("timeformat", "luxon");
  var editFrom = luxon__WEBPACK_IMPORTED_MODULE_3__.DateTime.fromSQL(start).toFormat(userDateFormat);
  var timeFrom = luxon__WEBPACK_IMPORTED_MODULE_3__.DateTime.fromSQL(start).toFormat(userTimeFormat);
  var editTo = luxon__WEBPACK_IMPORTED_MODULE_3__.DateTime.fromSQL(end).toFormat(userDateFormat);
  var timeTo = luxon__WEBPACK_IMPORTED_MODULE_3__.DateTime.fromSQL(end).toFormat(userTimeFormat);
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/tickets',
    data: {
      id: id,
      editFrom: editFrom,
      editTo: editTo,
      timeFrom: timeFrom,
      timeTo: timeTo,
      sortIndex: sortIndex
    }
  }).done(function () {
    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
  });
};
var updateRemainingHours = function updateRemainingHours(id, remaining, callbackSuccess) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/tickets',
    data: {
      id: id,
      hourRemaining: remaining
    }
  }).done(function () {
    callbackSuccess();
  });
};
var updatePlannedHours = function updatePlannedHours(id, planhours, callbackSuccess) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/tickets',
    data: {
      id: id,
      planHours: planhours
    }
  }).done(function () {
    callbackSuccess();
  });
};
var updateDueDates = function updateDueDates(id, date, callbackSuccess) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/tickets',
    data: {
      id: id,
      dateToFinish: date
    }
  }).done(function () {
    callbackSuccess();
  });
};
var updateEditFromDates = function updateEditFromDates(id, date, callbackSuccess) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/tickets',
    data: {
      id: id,
      editFrom: date
    }
  }).done(function () {
    callbackSuccess();
  });
};
var updateEditToDates = function updateEditToDates(id, date, callbackSuccess) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/tickets',
    data: {
      id: id,
      editTo: date
    }
  }).done(function () {
    callbackSuccess();
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  updateMilestoneDates: updateMilestoneDates,
  updateRemainingHours: updateRemainingHours,
  updatePlannedHours: updatePlannedHours,
  updateDueDates: updateDueDates,
  updateEditFromDates: updateEditFromDates,
  updateEditToDates: updateEditToDates
});

/***/ }),

/***/ "./app/Domain/Users/Js/usersRepository.js":
/*!************************************************!*\
  !*** ./app/Domain/Users/Js/usersRepository.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   saveUserPhoto: () => (/* binding */ saveUserPhoto),
/* harmony export */   updateUserViewSettings: () => (/* binding */ updateUserViewSettings)
/* harmony export */ });
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_1__);


var saveUserPhoto = function saveUserPhoto(photo) {
  var formData = new FormData();
  formData.append('file', photo);
  jquery__WEBPACK_IMPORTED_MODULE_1___default().ajax({
    type: 'POST',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/api/users',
    data: formData,
    processData: false,
    contentType: false,
    success: function success(resp) {
      jquery__WEBPACK_IMPORTED_MODULE_1___default()('#save-picture').removeClass('running');
      location.reload();
    },
    error: function error(err) {
      console.log(err);
    }
  });
};
var updateUserViewSettings = function updateUserViewSettings(module, value) {
  jquery__WEBPACK_IMPORTED_MODULE_1___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/api/users',
    data: {
      patchViewSettings: module,
      value: value
    }
  }).done(function () {
    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
  });
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  saveUserPhoto: saveUserPhoto,
  updateUserViewSettings: updateUserViewSettings
});

/***/ }),

/***/ "./public/assets/js/libs/simpleGantt/frappe-gantt.js":
/*!***********************************************************!*\
  !*** ./public/assets/js/libs/simpleGantt/frappe-gantt.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
function _createForOfIteratorHelper(r, e) { var t = "undefined" != typeof Symbol && r[Symbol.iterator] || r["@@iterator"]; if (!t) { if (Array.isArray(r) || (t = _unsupportedIterableToArray(r)) || e && r && "number" == typeof r.length) { t && (r = t); var _n = 0, F = function F() {}; return { s: F, n: function n() { return _n >= r.length ? { done: !0 } : { done: !1, value: r[_n++] }; }, e: function e(r) { throw r; }, f: F }; } throw new TypeError("Invalid attempt to iterate non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); } var o, a = !0, u = !1; return { s: function s() { t = t.call(r); }, n: function n() { var r = t.next(); return a = r.done, r; }, e: function e(r) { u = !0, o = r; }, f: function f() { try { a || null == t["return"] || t["return"](); } finally { if (u) throw o; } } }; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _construct(t, e, r) { if (_isNativeReflectConstruct()) return Reflect.construct.apply(null, arguments); var o = [null]; o.push.apply(o, e); var p = new (t.bind.apply(t, o))(); return r && _setPrototypeOf(p, r.prototype), p; }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ((function () {
  'use strict';

  var YEAR = 'year';
  var MONTH = 'month';
  var DAY = 'day';
  var HOUR = 'hour';
  var MINUTE = 'minute';
  var SECOND = 'second';
  var MILLISECOND = 'millisecond';
  var date_utils = {
    parse: function parse(date) {
      var date_separator = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : '-';
      var time_separator = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : /[.:]/;
      if (date instanceof Date) {
        return date;
      }
      if (typeof date === 'string') {
        var date_parts, time_parts;
        var parts = date.split(' ');
        date_parts = parts[0].split(date_separator).map(function (val) {
          return parseInt(val, 10);
        });
        time_parts = parts[1] && parts[1].split(time_separator);

        // month is 0 indexed
        date_parts[1] = date_parts[1] - 1;
        var vals = date_parts;
        if (time_parts && time_parts.length) {
          if (time_parts.length == 4) {
            time_parts[3] = '0.' + time_parts[3];
            time_parts[3] = parseFloat(time_parts[3]) * 1000;
          }
          vals = vals.concat(time_parts);
        }
        return _construct(Date, _toConsumableArray(vals));
      }
    },
    to_string: function to_string(date) {
      var with_time = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
      if (!(date instanceof Date)) {
        throw new TypeError('Invalid argument type');
      }
      var vals = this.get_date_values(date).map(function (val, i) {
        if (i === 1) {
          // add 1 for month
          val = val + 1;
        }
        if (i === 6) {
          return padStart(val + '', 3, '0');
        }
        return padStart(val + '', 2, '0');
      });
      var date_string = "".concat(vals[0], "-").concat(vals[1], "-").concat(vals[2]);
      var time_string = "".concat(vals[3], ":").concat(vals[4], ":").concat(vals[5], ".").concat(vals[6]);
      return date_string + (with_time ? ' ' + time_string : '');
    },
    format: function format(date) {
      var format_string = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : 'YYYY-MM-DD HH:mm:ss.SSS';
      var lang = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'en';
      var dateTimeFormat = new Intl.DateTimeFormat(lang, {
        month: 'long'
      });
      var month_name = dateTimeFormat.format(date);
      var month_name_capitalized = month_name.charAt(0).toUpperCase() + month_name.slice(1);
      var values = this.get_date_values(date).map(function (d) {
        return padStart(d, 2, 0);
      });
      var format_map = {
        YYYY: values[0],
        MM: padStart(+values[1] + 1, 2, 0),
        DD: values[2],
        HH: values[3],
        mm: values[4],
        ss: values[5],
        SSS: values[6],
        D: values[2],
        MMMM: month_name_capitalized,
        MMM: month_name_capitalized
      };
      var str = format_string;
      var formatted_values = [];
      Object.keys(format_map).sort(function (a, b) {
        return b.length - a.length;
      }) // big string first
      .forEach(function (key) {
        if (str.includes(key)) {
          str = str.replace(key, "$".concat(formatted_values.length));
          formatted_values.push(format_map[key]);
        }
      });
      formatted_values.forEach(function (value, i) {
        str = str.replace("$".concat(i), value);
      });
      return str;
    },
    diff: function diff(date_a, date_b) {
      var scale = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : DAY;
      var milliseconds, seconds, hours, minutes, days, months, years;
      milliseconds = date_a - date_b;
      seconds = milliseconds / 1000;
      minutes = seconds / 60;
      hours = minutes / 60;
      days = hours / 24;
      months = days / 30;
      years = months / 12;
      if (!scale.endsWith('s')) {
        scale += 's';
      }
      return Math.floor({
        milliseconds: milliseconds,
        seconds: seconds,
        minutes: minutes,
        hours: hours,
        days: days,
        months: months,
        years: years
      }[scale]);
    },
    today: function today() {
      var vals = this.get_date_values(new Date()).slice(0, 3);
      return _construct(Date, _toConsumableArray(vals));
    },
    now: function now() {
      return new Date();
    },
    add: function add(date, qty, scale) {
      qty = parseInt(qty, 10);
      var vals = [date.getFullYear() + (scale === YEAR ? qty : 0), date.getMonth() + (scale === MONTH ? qty : 0), date.getDate() + (scale === DAY ? qty : 0), date.getHours() + (scale === HOUR ? qty : 0), date.getMinutes() + (scale === MINUTE ? qty : 0), date.getSeconds() + (scale === SECOND ? qty : 0), date.getMilliseconds() + (scale === MILLISECOND ? qty : 0)];
      return _construct(Date, vals);
    },
    start_of: function start_of(date, scale) {
      var scores = _defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty(_defineProperty({}, YEAR, 6), MONTH, 5), DAY, 4), HOUR, 3), MINUTE, 2), SECOND, 1), MILLISECOND, 0);
      function should_reset(_scale) {
        var max_score = scores[scale];
        return scores[_scale] <= max_score;
      }
      var vals = [date.getFullYear(), should_reset(YEAR) ? 0 : date.getMonth(), should_reset(MONTH) ? 1 : date.getDate(), should_reset(DAY) ? 0 : date.getHours(), should_reset(HOUR) ? 0 : date.getMinutes(), should_reset(MINUTE) ? 0 : date.getSeconds(), should_reset(SECOND) ? 0 : date.getMilliseconds()];
      return _construct(Date, vals);
    },
    clone: function clone(date) {
      return _construct(Date, _toConsumableArray(this.get_date_values(date)));
    },
    get_date_values: function get_date_values(date) {
      return [date.getFullYear(), date.getMonth(), date.getDate(), date.getHours(), date.getMinutes(), date.getSeconds(), date.getMilliseconds()];
    },
    get_days_in_month: function get_days_in_month(date) {
      var month = date.getMonth();
      var year = date.getFullYear();
      return new Date(year, month + 1, 0).getDate();
    }
  };

  // https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/String/padStart
  function padStart(str, targetLength, padString) {
    str = str + '';
    targetLength = targetLength >> 0;
    padString = String(typeof padString !== 'undefined' ? padString : ' ');
    if (str.length > targetLength) {
      return String(str);
    } else {
      targetLength = targetLength - str.length;
      if (targetLength > padString.length) {
        padString += padString.repeat(targetLength / padString.length);
      }
      return padString.slice(0, targetLength) + String(str);
    }
  }
  function $(expr, con) {
    return typeof expr === 'string' ? (con || document).querySelector(expr) : expr || null;
  }
  function createSVG(tag, attrs) {
    var elem = document.createElementNS('http://www.w3.org/2000/svg', tag);
    for (var attr in attrs) {
      if (attr === 'append_to') {
        var parent = attrs.append_to;
        parent.appendChild(elem);
      } else if (attr === 'innerHTML') {
        elem.innerHTML = attrs.innerHTML;
      } else if (attr === 'clipPath') {
        elem.setAttribute('clip-path', 'url(#' + attrs[attr] + ')');
      } else {
        elem.setAttribute(attr, attrs[attr]);
      }
    }
    return elem;
  }

  /* Animations don't work well in firefox. Removing.
   function animateSVG(svgElement, attr, from, to) {
      const animatedSvgElement = getAnimationElement(svgElement, attr, from, to);
       if (animatedSvgElement === svgElement) {
          // triggered 2nd time programmatically
          // trigger artificial click event
          const event = document.createEvent('HTMLEvents');
          event.initEvent('click', true, true);
          event.eventName = 'click';
          animatedSvgElement.dispatchEvent(event);
      }
  }
   function getAnimationElement(
      svgElement,
      attr,
      from,
      to,
      dur = '0.4s',
      begin = '0.1s'
  ) {
      const animEl = svgElement.querySelector('animate');
      if (animEl) {
          $.attr(animEl, {
              attributeName: attr,
              from,
              to,
              dur,
              begin: 'click + ' + begin, // artificial click
          });
          return svgElement;
      }
       const animateElement = createSVG('animate', {
          attributeName: attr,
          from,
          to,
          dur,
          begin,
          calcMode: 'spline',
          values: from + ';' + to,
          keyTimes: '0; 1',
          keySplines: cubic_bezier('ease-out'),
      });
      svgElement.appendChild(animateElement);
       return svgElement;
  }
   function cubic_bezier(name) {
      return {
          ease: '.25 .1 .25 1',
          linear: '0 0 1 1',
          'ease-in': '.42 0 1 1',
          'ease-out': '0 0 .58 1',
          'ease-in-out': '.42 0 .58 1',
      }[name];
  }
  */

  $.on = function (element, event, selector, callback) {
    if (!callback) {
      callback = selector;
      $.bind(element, event, callback);
    } else {
      $.delegate(element, event, selector, callback);
    }
  };
  $.off = function (element, event, handler) {
    element.removeEventListener(event, handler);
  };
  $.bind = function (element, event, callback) {
    event.split(/\s+/).forEach(function (event) {
      element.addEventListener(event, callback);
    });
  };
  $.delegate = function (element, event, selector, callback) {
    element.addEventListener(event, function (e) {
      var delegatedTarget = e.target.closest(selector);
      if (delegatedTarget) {
        e.delegatedTarget = delegatedTarget;
        callback.call(this, e, delegatedTarget);
      }
    });
  };
  $.closest = function (selector, element) {
    if (!element) return null;
    if (element.matches(selector)) {
      return element;
    }
    return $.closest(selector, element.parentNode);
  };
  $.attr = function (element, attr, value) {
    if (!value && typeof attr === 'string') {
      return element.getAttribute(attr);
    }
    if (_typeof(attr) === 'object') {
      for (var key in attr) {
        $.attr(element, key, attr[key]);
      }
      return;
    }
    element.setAttribute(attr, value);
  };
  var Bar = /*#__PURE__*/function () {
    function Bar(gantt, task) {
      _classCallCheck(this, Bar);
      this.set_defaults(gantt, task);
      this.prepare();
      this.draw();
      this.bind();
    }
    return _createClass(Bar, [{
      key: "set_defaults",
      value: function set_defaults(gantt, task) {
        this.action_completed = false;
        this.gantt = gantt;
        this.task = task;
      }
    }, {
      key: "prepare",
      value: function prepare() {
        this.prepare_values();
        this.prepare_helpers();
      }
    }, {
      key: "prepare_values",
      value: function prepare_values() {
        this.invalid = this.task.invalid;
        this.height = this.task.bar_height;
        this.image_size = this.gantt.options.bar_height - 16;
        this.x = this.compute_x();
        this.y = this.compute_y();
        this.corner_radius = this.gantt.options.bar_corner_radius;
        this.duration = date_utils.diff(this.task._end, this.task._start, 'hour') / this.gantt.options.step;
        this.width = this.gantt.options.column_width * this.duration;
        this.progress_width = this.gantt.options.column_width * this.duration * (this.task.progress / 100) || 0;
        this.group = createSVG('g', {
          "class": 'bar-wrapper ' + (this.task.custom_class || ''),
          'data-id': this.task.id
        });
        this.bar_group = createSVG('g', {
          "class": 'bar-group',
          append_to: this.group
        });
        this.handle_group = createSVG('g', {
          "class": 'handle-group',
          append_to: this.group
        });
      }
    }, {
      key: "prepare_helpers",
      value: function prepare_helpers() {
        SVGElement.prototype.getX = function () {
          return +this.getAttribute('x');
        };
        SVGElement.prototype.getY = function () {
          return +this.getAttribute('y');
        };
        SVGElement.prototype.getWidth = function () {
          return +this.getAttribute('width');
        };
        SVGElement.prototype.getHeight = function () {
          return +this.getAttribute('height');
        };
        SVGElement.prototype.getEndX = function () {
          return this.getX() + this.getWidth();
        };
      }
    }, {
      key: "draw",
      value: function draw() {
        this.draw_bar();
        if (this.gantt.options.progress) {
          this.draw_progress_bar();
        }
        this.draw_label();
        if (this.task.thumbnail) {
          this.draw_thumbnail();
        }
        if (this.gantt.options.resizing) {
          this.draw_resize_handles();
        }
      }
    }, {
      key: "draw_bar",
      value: function draw_bar() {
        this.$bar = createSVG('rect', {
          x: this.x,
          y: this.y,
          width: this.width,
          height: this.height,
          rx: this.corner_radius,
          ry: this.corner_radius,
          fill: this.task.bg_color,
          "class": 'bar',
          append_to: this.bar_group
        });

        //animateSVG(this.$bar, 'width', 0, this.width);

        if (this.invalid) {
          this.$bar.classList.add('bar-invalid');
        }
      }
    }, {
      key: "draw_progress_bar",
      value: function draw_progress_bar() {
        if (this.invalid) return;
        this.$bar_progress = createSVG('rect', {
          x: this.x,
          y: this.y,
          width: this.progress_width,
          height: this.height,
          rx: this.corner_radius,
          ry: this.corner_radius,
          "class": 'bar-progress',
          append_to: this.bar_group
        });

        //animateSVG(this.$bar_progress, 'width', 0, this.progress_width);
      }
    }, {
      key: "draw_label",
      value: function draw_label() {
        var _this2 = this;
        var _this = this;
        var x_coord = void 0;
        var padding = 5;
        if (this.task.thumbnail) {
          x_coord = this.x + this.image_size + padding;
        } else {
          x_coord = this.x + 5;
        }
        createSVG('text', {
          x: x_coord,
          y: this.y + this.height / 2,
          innerHTML: this.task.name,
          "class": 'bar-label',
          append_to: this.bar_group
        });
        // labels get BBox in the next tick
        requestAnimationFrame(function () {
          return _this2.update_label_position();
        });
      }
    }, {
      key: "draw_thumbnail",
      value: function draw_thumbnail() {
        var x_offset = 15,
          y_offset = (this.height - this.image_size) / 2;
        var defs = void 0,
          clipPath = void 0;
        defs = createSVG('defs', {
          append_to: this.bar_group
        });
        createSVG('rect', {
          id: 'rect_' + this.task.id,
          x: this.x + x_offset,
          y: this.y + y_offset,
          width: this.image_size,
          height: this.image_size,
          rx: '15',
          "class": 'img_mask',
          append_to: defs
        });
        clipPath = createSVG('clipPath', {
          id: 'clip_' + this.task.id,
          append_to: defs
        });
        createSVG('use', {
          href: '#rect_' + this.task.id,
          append_to: clipPath
        });
        createSVG('image', {
          x: this.x + x_offset,
          y: this.y + y_offset,
          width: this.image_size,
          height: this.image_size,
          "class": 'bar-img',
          href: this.task.thumbnail,
          clipPath: 'clip_' + this.task.id,
          append_to: this.bar_group
        });
      }
    }, {
      key: "draw_resize_handles",
      value: function draw_resize_handles() {
        if (this.invalid) return;
        var bar = this.$bar;
        var handle_width = 8;
        createSVG('rect', {
          x: bar.getX() + bar.getWidth() - 9,
          y: bar.getY() + 1,
          width: handle_width,
          height: this.height - 2,
          rx: this.corner_radius,
          ry: this.corner_radius,
          "class": 'handle right',
          append_to: this.handle_group
        });
        createSVG('rect', {
          x: bar.getX() + 1,
          y: bar.getY() + 1,
          width: handle_width,
          height: this.height - 2,
          rx: this.corner_radius,
          ry: this.corner_radius,
          "class": 'handle left',
          append_to: this.handle_group
        });
        if (this.task.progress && this.task.progress < 100) {
          this.$handle_progress = createSVG('polygon', {
            points: this.get_progress_polygon_points().join(','),
            "class": 'handle progress',
            append_to: this.handle_group
          });
        }
      }
    }, {
      key: "get_progress_polygon_points",
      value: function get_progress_polygon_points() {
        var bar_progress = this.$bar_progress;
        return [bar_progress.getEndX() - 5, bar_progress.getY() + bar_progress.getHeight(), bar_progress.getEndX() + 5, bar_progress.getY() + bar_progress.getHeight(), bar_progress.getEndX(), bar_progress.getY() + bar_progress.getHeight() - 8.66];
      }
    }, {
      key: "bind",
      value: function bind() {
        if (this.invalid) return;
        this.setup_click_event();
      }
    }, {
      key: "setup_click_event",
      value: function setup_click_event() {
        var _this3 = this;
        $.on(this.group, 'focus ' + this.gantt.options.popup_trigger, function (e) {
          if (_this3.action_completed) {
            // just finished a move action, wait for a few seconds
            return;
          }
          _this3.show_popup();
          _this3.gantt.unselect_all();
          _this3.group.classList.add('active');
        });
        $.on(this.group, 'dblclick', function (e) {
          if (_this3.action_completed) {
            // just finished a move action, wait for a few seconds
            return;
          }
          _this3.gantt.trigger_event('click', [_this3.task]);
        });
      }
    }, {
      key: "show_popup",
      value: function show_popup() {
        if (this.gantt.bar_being_dragged) return;
        var start_date = date_utils.format(this.task._start, 'MMM D', this.gantt.options.language);
        var end_date = date_utils.format(date_utils.add(this.task._end, -1, 'second'), 'MMM D', this.gantt.options.language);
        var subtitle = start_date + ' - ' + end_date;
        this.gantt.show_popup({
          target_element: this.$bar,
          title: this.task.name,
          subtitle: subtitle,
          task: this.task
        });
      }
    }, {
      key: "update_bar_position",
      value: function update_bar_position(_ref) {
        var _this4 = this;
        var _ref$x = _ref.x,
          x = _ref$x === void 0 ? null : _ref$x,
          _ref$width = _ref.width,
          width = _ref$width === void 0 ? null : _ref$width,
          _ref$y = _ref.y,
          y = _ref$y === void 0 ? null : _ref$y;
        var bar = this.$bar;
        if (x) {
          // get all x values of parent task
          var xs = this.task.dependencies.map(function (dep) {
            return _this4.gantt.get_bar(dep).$bar.getX();
          });
          // child task must not go before parent
          var valid_x = xs.reduce(function (prev, curr) {
            return x >= curr;
          }, x);
          if (!valid_x) {
            width = null;
            return;
          }
          this.update_attr(bar, 'x', x);
        }
        if (width && width >= this.gantt.options.column_width / 4) {
          this.update_attr(bar, 'width', width);
        }
        if (y) {
          this.update_attr(bar, 'y', y);
        }
        this.update_label_position();
        if (this.gantt.options.resizing) {
          this.update_handle_position();
        }
        this.update_progressbar_position();
        this.update_handle_position();
        this.update_arrow_position();
      }
    }, {
      key: "update_label_position_on_horizontal_scroll",
      value: function update_label_position_on_horizontal_scroll(_ref2) {
        var x = _ref2.x,
          sx = _ref2.sx;
        var container = document.querySelector('.gantt-container');
        var label = this.group.querySelector('.bar-label');
        var img = this.group.querySelector('.bar-img') || '';
        var img_mask = this.bar_group.querySelector('.img_mask') || '';
        var barWidthLimit = this.$bar.getX() + this.$bar.getWidth();
        var newLabelX = label.getX() + x;
        var newImgX = img && img.getX() + x || 0;
        var imgWidth = img && img.getBBox().width + 7 || 7;
        var labelEndX = newLabelX + label.getBBox().width + 7;
        var viewportCentral = sx + container.clientWidth / 2;
        if (label.classList.contains('big')) return;
        if (labelEndX < barWidthLimit && x > 0 && labelEndX < viewportCentral) {
          label.setAttribute('x', newLabelX);
          if (img) {
            img.setAttribute('x', newImgX);
            img_mask.setAttribute('x', newImgX);
          }
        } else if (newLabelX - imgWidth > this.$bar.getX() && x < 0 && labelEndX > viewportCentral) {
          label.setAttribute('x', newLabelX);
          if (img) {
            img.setAttribute('x', newImgX);
            img_mask.setAttribute('x', newImgX);
          }
        }
      }
    }, {
      key: "date_changed",
      value: function date_changed() {
        var changed = false;
        var _this$compute_start_e = this.compute_start_end_date(),
          new_start_date = _this$compute_start_e.new_start_date,
          new_end_date = _this$compute_start_e.new_end_date;
        if (Number(this.task._start) !== Number(new_start_date)) {
          changed = true;
          this.task._start = new_start_date;
        }
        if (Number(this.task._end) !== Number(new_end_date)) {
          changed = true;
          this.task._end = new_end_date;
        }
        if (!changed) return;
        this.gantt.trigger_event('date_change', [this.task, date_utils.format(new_start_date, "YYYY-MM-DD HH:mm:ss"), date_utils.format(date_utils.add(new_end_date, -1, 'second'), "YYYY-MM-DD HH:mm:ss")]);
      }
    }, {
      key: "sort_changed",
      value: function sort_changed() {
        this.gantt.trigger_event('sort_change', [this.gantt.tasks]);
      }
    }, {
      key: "progress_changed",
      value: function progress_changed() {
        var new_progress = this.compute_progress();
        this.task.progress = new_progress;
        this.gantt.trigger_event('progress_change', [this.task, new_progress]);
      }
    }, {
      key: "set_action_completed",
      value: function set_action_completed() {
        var _this5 = this;
        this.action_completed = true;
        setTimeout(function () {
          return _this5.action_completed = false;
        }, 1000);
      }
    }, {
      key: "compute_start_end_date",
      value: function compute_start_end_date() {
        var bar = this.$bar;
        var x_in_units = bar.getX() / this.gantt.options.column_width;
        var new_start_date = date_utils.add(this.gantt.gantt_start, x_in_units * this.gantt.options.step, 'hour');
        var width_in_units = bar.getWidth() / this.gantt.options.column_width;
        var new_end_date = date_utils.add(new_start_date, width_in_units * this.gantt.options.step, 'hour');
        return {
          new_start_date: new_start_date,
          new_end_date: new_end_date
        };
      }
    }, {
      key: "compute_progress",
      value: function compute_progress() {
        var progress = this.$bar_progress.getWidth() / this.$bar.getWidth() * 100;
        return parseInt(progress, 10);
      }
    }, {
      key: "compute_x",
      value: function compute_x() {
        var _this$gantt$options = this.gantt.options,
          step = _this$gantt$options.step,
          column_width = _this$gantt$options.column_width;
        var task_start = this.task._start;
        var gantt_start = this.gantt.gantt_start;
        var diff = date_utils.diff(task_start, gantt_start, 'hour');
        var x = diff / step * column_width;
        return x;
      }
    }, {
      key: "compute_y",
      value: function compute_y() {
        return this.gantt.options.header_height + this.gantt.options.padding + this.task._index * (this.height + this.gantt.options.padding);
      }
    }, {
      key: "get_snap_position",
      value: function get_snap_position(dx) {
        var odx = dx,
          rem,
          position;
        if (this.gantt.view_is('Week')) {
          rem = dx % (this.gantt.options.column_width / 7);
          position = odx - rem + (rem < this.gantt.options.column_width / 14 ? 0 : this.gantt.options.column_width / 7);
        } else if (this.gantt.view_is('Month')) {
          rem = dx % (this.gantt.options.column_width / 30);
          position = odx - rem + (rem < this.gantt.options.column_width / 60 ? 0 : this.gantt.options.column_width / 30);
        } else if (this.gantt.view_is('Day')) {
          rem = dx % (this.gantt.options.column_width / 24);
          position = odx - rem + (rem < this.gantt.options.column_width / 48 ? 0 : this.gantt.options.column_width / 24);
        } else {
          rem = dx % this.gantt.options.column_width;
          position = odx - rem + (rem < this.gantt.options.column_width / 2 ? 0 : this.gantt.options.column_width);
        }
        return position;
      }
    }, {
      key: "update_attr",
      value: function update_attr(element, attr, value) {
        value = +value;
        if (!isNaN(value)) {
          element.setAttribute(attr, value);
        }
        return element;
      }
    }, {
      key: "update_progressbar_position",
      value: function update_progressbar_position() {
        if (this.invalid) return;
        this.$bar_progress.setAttribute('x', this.$bar.getX());
        this.$bar_progress.setAttribute('y', this.$bar.getY());
        this.$bar_progress.setAttribute('width', this.$bar.getWidth() * (this.task.progress / 100));
      }
    }, {
      key: "update_label_position",
      value: function update_label_position() {
        var img_mask = this.bar_group.querySelector('.img_mask') || '';
        var bar = this.$bar,
          label = this.group.querySelector('.bar-label'),
          img = this.group.querySelector('.bar-img');
        var padding = 5;
        var x_offset_label_img = this.image_size + 10;
        var y_offset_label_img = (bar.getHeight() - this.image_size) / 2;
        if (label.getBBox().width > bar.getWidth()) {
          label.classList.add('big');
          if (img) {
            img.setAttribute('x', bar.getX() + bar.getWidth() + padding);
            img_mask.setAttribute('x', bar.getX() + bar.getWidth() + padding);
            label.setAttribute('x', bar.getX() + bar.getWidth() + x_offset_label_img);
            img.setAttribute('y', bar.getY() + y_offset_label_img);
            img_mask.setAttribute('y', bar.getY() + y_offset_label_img);
            label.setAttribute('y', bar.getY() + bar.getHeight() / 2);
          } else {
            label.setAttribute('x', bar.getX() + bar.getWidth() + padding);
          }
        } else {
          label.classList.remove('big');
          if (img) {
            img.setAttribute('x', bar.getX() + padding);
            img_mask.setAttribute('x', bar.getX() + padding);
            label.setAttribute('x', bar.getX() + x_offset_label_img);
            img.setAttribute('y', bar.getY() + y_offset_label_img);
            img_mask.setAttribute('y', bar.getY() + y_offset_label_img);
            label.setAttribute('y', bar.getY() + bar.getHeight() / 2);
          } else {
            label.setAttribute('x', bar.getX() + padding);
            label.setAttribute('y', bar.getY() + bar.getHeight() / 2);
          }
        }
      }
    }, {
      key: "update_handle_position",
      value: function update_handle_position() {
        if (this.invalid) return;
        var bar = this.$bar;
        this.handle_group.querySelector('.handle.left').setAttribute('x', bar.getX() + 1);
        this.handle_group.querySelector('.handle.left').setAttribute('y', bar.getY() + 1);
        this.handle_group.querySelector('.handle.right').setAttribute('x', bar.getEndX() - 9);
        this.handle_group.querySelector('.handle.right').setAttribute('y', bar.getY() + 1);
        var handle = this.group.querySelector('.handle.progress');
        handle && handle.setAttribute('points', this.get_progress_polygon_points());
      }
    }, {
      key: "update_arrow_position",
      value: function update_arrow_position() {
        this.arrows = this.arrows || [];
        var _iterator = _createForOfIteratorHelper(this.arrows),
          _step;
        try {
          for (_iterator.s(); !(_step = _iterator.n()).done;) {
            var arrow = _step.value;
            arrow.update();
          }
        } catch (err) {
          _iterator.e(err);
        } finally {
          _iterator.f();
        }
      }
    }]);
  }();
  var Arrow = /*#__PURE__*/function () {
    function Arrow(gantt, from_task, to_task) {
      _classCallCheck(this, Arrow);
      this.gantt = gantt;
      this.from_task = from_task;
      this.to_task = to_task;
      this.calculate_path();
      this.draw();
    }
    return _createClass(Arrow, [{
      key: "calculate_path",
      value: function calculate_path() {
        var _this7 = this;
        var start_x = this.from_task.$bar.getX() + this.from_task.$bar.getWidth() / 2;
        var condition = function condition() {
          return _this7.to_task.$bar.getX() < start_x + _this7.gantt.options.padding && start_x > _this7.from_task.$bar.getX() + _this7.gantt.options.padding;
        };
        while (condition()) {
          start_x -= 10;
        }
        var start_y = this.from_task.$bar.getY() + this.gantt.options.bar_height;
        var end_x = this.to_task.$bar.getX() - this.gantt.options.padding / 2;
        var end_y = this.to_task.$bar.getY() + this.gantt.options.bar_height / 2;
        var from_is_below_to = this.from_task.$bar.getY() > this.to_task.$bar.getY();
        var curve = this.gantt.options.arrow_curve;
        var clockwise = from_is_below_to ? 1 : 0;
        var curve_y = from_is_below_to ? -curve : curve;
        var offset = from_is_below_to ? end_y + this.gantt.options.arrow_curve : end_y - this.gantt.options.arrow_curve;
        this.path = "\n            M ".concat(start_x, " ").concat(start_y, "\n            V ").concat(offset, "\n            a ").concat(curve, " ").concat(curve, " 0 0 ").concat(clockwise, " ").concat(curve, " ").concat(curve_y, "\n            L ").concat(end_x, " ").concat(end_y, "\n            m -5 -5\n            l 5 5\n            l -5 5");
        if (this.to_task.$bar.getX() < this.from_task.$bar.getX() + this.gantt.options.padding) {
          var down_1 = this.gantt.options.padding / 2 - curve;
          var down_2 = this.to_task.$bar.getY() + this.to_task.$bar.getHeight() / 2 - curve_y;
          var left = this.to_task.$bar.getX() - this.gantt.options.padding;
          this.path = "\n                M ".concat(start_x, " ").concat(start_y, "\n                v ").concat(down_1, "\n                a ").concat(curve, " ").concat(curve, " 0 0 1 -").concat(curve, " ").concat(curve, "\n                H ").concat(left, "\n                a ").concat(curve, " ").concat(curve, " 0 0 ").concat(clockwise, " -").concat(curve, " ").concat(curve_y, "\n                V ").concat(down_2, "\n                a ").concat(curve, " ").concat(curve, " 0 0 ").concat(clockwise, " ").concat(curve, " ").concat(curve_y, "\n                L ").concat(end_x, " ").concat(end_y, "\n                m -5 -5\n                l 5 5\n                l -5 5");
        }
      }
    }, {
      key: "draw",
      value: function draw() {
        this.element = createSVG('path', {
          d: this.path,
          'data-from': this.from_task.task.id,
          'data-to': this.to_task.task.id
        });
      }
    }, {
      key: "update",
      value: function update() {
        this.calculate_path();
        this.element.setAttribute('d', this.path);
      }
    }]);
  }();
  var Popup = /*#__PURE__*/function () {
    function Popup(parent, custom_html, gantt) {
      _classCallCheck(this, Popup);
      this.parent = parent;
      this.custom_html = custom_html;
      this.make();
      this.gantt = gantt;
    }
    return _createClass(Popup, [{
      key: "make",
      value: function make() {
        this.parent.innerHTML = "\n            <div class=\"title\"></div>\n            <div class=\"subtitle\"></div>\n            <div class=\"pointer\"></div>\n        ";
        this.hide();
        this.title = this.parent.querySelector('.title');
        this.subtitle = this.parent.querySelector('.subtitle');
        this.pointer = this.parent.querySelector('.pointer');
      }
    }, {
      key: "show",
      value: function show(options) {
        if (!options.target_element) {
          throw new Error('target_element is required to show popup');
        }
        if (!options.position) {
          options.position = 'left';
        }
        var target_element = options.target_element;
        if (this.custom_html) {
          var html = this.custom_html(options.task);
          html += '<div class="pointer"></div>';
          this.parent.innerHTML = html;
          this.pointer = this.parent.querySelector('.pointer');
        } else {
          // set data
          this.title.innerHTML = options.title;
          this.subtitle.innerHTML = options.subtitle;
          this.parent.style.width = this.parent.clientWidth + 'px';
        }

        // set position
        var position_meta;
        if (target_element instanceof HTMLElement) {
          position_meta = target_element.getBoundingClientRect();
        } else if (target_element instanceof SVGElement) {
          position_meta = options.target_element.getBBox();
        }
        if (options.position === 'left') {
          this.parent.style.left = position_meta.x + (position_meta.width + 10) + 'px';
          this.parent.style.top = position_meta.y + 'px';
          this.pointer.style.transform = 'rotateZ(90deg)';
          this.pointer.style.left = '-7px';
          this.pointer.style.top = '2px';
        }

        // show
        this.parent.style.opacity = 1;
        this.gantt.trigger_event('popup_show', options.task);
      }
    }, {
      key: "hide",
      value: function hide() {
        this.parent.style.opacity = 0;
        this.parent.style.left = 0;
        this.parent.style.top = '-1000px'; //Moving pop up out of frame
      }
    }]);
  }();
  var VIEW_MODE = {
    QUARTER_DAY: 'Quarter Day',
    HALF_DAY: 'Half Day',
    DAY: 'Day',
    WEEK: 'Week',
    MONTH: 'Month',
    YEAR: 'Year'
  };
  var Gantt = /*#__PURE__*/function () {
    function Gantt(wrapper, tasks, options) {
      _classCallCheck(this, Gantt);
      this.setup_wrapper(wrapper);
      this.setup_options(options);
      this.setup_tasks(tasks);
      // initialize with default view mode
      this.change_view_mode();
      this.bind_events();
    }
    return _createClass(Gantt, [{
      key: "setup_wrapper",
      value: function setup_wrapper(element) {
        var svg_element, wrapper_element;

        // CSS Selector is passed
        if (typeof element === 'string') {
          element = document.querySelector(element);
        }

        // get the SVGElement
        if (element instanceof HTMLElement) {
          wrapper_element = element;
          svg_element = element.querySelector('svg');
        } else if (element instanceof SVGElement) {
          svg_element = element;
        } else {
          throw new TypeError('Frappé Gantt only supports usage of a string CSS selector,' + " HTML DOM element or SVG DOM element for the 'element' parameter");
        }

        // svg element
        if (!svg_element) {
          // create it
          this.$svg = createSVG('svg', {
            append_to: wrapper_element,
            "class": 'gantt'
          });
        } else {
          this.$svg = svg_element;
          this.$svg.classList.add('gantt');
        }

        // wrapper element
        this.$container = document.createElement('div');
        this.$container.classList.add('gantt-container');
        var parent_element = this.$svg.parentElement;
        parent_element.appendChild(this.$container);
        this.$container.appendChild(this.$svg);

        // popup wrapper
        this.popup_wrapper = document.createElement('div');
        this.popup_wrapper.classList.add('popup-wrapper');
        this.$container.appendChild(this.popup_wrapper);
      }
    }, {
      key: "setup_options",
      value: function setup_options(options) {
        var default_options = {
          header_height: 55,
          column_width: 30,
          step: 24,
          view_modes: _toConsumableArray(Object.values(VIEW_MODE)),
          bar_height: 20,
          bar_corner_radius: 3,
          arrow_curve: 5,
          padding: 25,
          resizing: true,
          progress: true,
          is_draggable: true,
          view_mode: 'Day',
          date_format: 'YYYY-MM-DD',
          popup_trigger: 'click',
          custom_popup_html: null,
          additional_rows: 0,
          //Add empty rows to fill screen
          language: 'en',
          sortable: true
        };
        this.options = Object.assign({}, default_options, options);
      }
    }, {
      key: "setup_tasks",
      value: function setup_tasks(tasks) {
        var _this8 = this;
        // prepare tasks

        tasks.sort(function (a, b) {
          if (a.sortIndex < b.sortIndex) {
            return -1;
          }
          if (a.sortIndex > b.sortIndex) {
            return 1;
          }

          // names must be equal
          return 0;
        });
        this.tasks = tasks.map(function (task, i) {
          // convert to Date objects
          task._start = date_utils.parse(task.start);
          task._end = date_utils.parse(task.end);

          // make task invalid if duration too large
          if (date_utils.diff(task._end, task._start, 'year') > 10) {
            task.end = null;
          }

          // cache index
          task._index = i;

          // invalid dates
          if (!task.start && !task.end) {
            var today = date_utils.today();
            task._start = today;
            task._end = date_utils.add(today, 2, 'day');
          }
          if (!task.start && task.end) {
            task._start = date_utils.add(task._end, -2, 'day');
          }
          if (task.start && !task.end) {
            task._end = date_utils.add(task._start, 2, 'day');
          }

          // if hours is not set, assume the last day is full day
          // e.g: 2018-09-09 becomes 2018-09-09 23:59:59
          var task_end_values = date_utils.get_date_values(task._end);
          if (task_end_values.slice(3).every(function (d) {
            return d === 0;
          })) {
            task._end = date_utils.add(task._end, 24, 'hour');
          }

          // invalid flag
          if (!task.start || !task.end) {
            task.invalid = true;
          }

          // dependencies
          if (typeof task.dependencies === 'string' || !task.dependencies) {
            var deps = [];
            if (task.dependencies) {
              deps = task.dependencies.split(',').map(function (d) {
                return d.trim();
              }).filter(function (d) {
                return d;
              });
            }
            task.dependencies = deps;
          }

          // uids
          if (!task.id) {
            task.id = generate_id(task);
          }
          if (!task.bg_color) {
            task.bg_color = "#ccc";
          }
          if (!task.bar_height) {
            task.bar_height = _this8.options.bar_height;
          }
          return task;
        });
        this.setup_dependencies();
      }
    }, {
      key: "setup_dependencies",
      value: function setup_dependencies() {
        this.dependency_map = {};
        var _iterator2 = _createForOfIteratorHelper(this.tasks),
          _step2;
        try {
          for (_iterator2.s(); !(_step2 = _iterator2.n()).done;) {
            var t = _step2.value;
            var _iterator3 = _createForOfIteratorHelper(t.dependencies),
              _step3;
            try {
              for (_iterator3.s(); !(_step3 = _iterator3.n()).done;) {
                var d = _step3.value;
                this.dependency_map[d] = this.dependency_map[d] || [];
                this.dependency_map[d].push(t.id);
              }
            } catch (err) {
              _iterator3.e(err);
            } finally {
              _iterator3.f();
            }
          }
        } catch (err) {
          _iterator2.e(err);
        } finally {
          _iterator2.f();
        }
      }
    }, {
      key: "refresh",
      value: function refresh(tasks) {
        this.setup_tasks(tasks);
        this.change_view_mode();
      }
    }, {
      key: "change_view_mode",
      value: function change_view_mode() {
        var mode = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : this.options.view_mode;
        this.update_view_scale(mode);
        this.setup_dates();
        this.render();
        // fire viewmode_change event
        this.trigger_event('view_change', [mode]);
      }
    }, {
      key: "update_view_scale",
      value: function update_view_scale(view_mode) {
        this.options.view_mode = view_mode;
        if (view_mode === VIEW_MODE.DAY) {
          this.options.step = 24;
          this.options.column_width = 38;
        } else if (view_mode === VIEW_MODE.HALF_DAY) {
          this.options.step = 24 / 2;
          this.options.column_width = 38;
        } else if (view_mode === VIEW_MODE.QUARTER_DAY) {
          this.options.step = 24 / 4;
          this.options.column_width = 38;
        } else if (view_mode === VIEW_MODE.WEEK) {
          this.options.step = 24 * 7;
          this.options.column_width = 140;
        } else if (view_mode === VIEW_MODE.MONTH) {
          this.options.step = 24 * 30;
          this.options.column_width = 120;
        } else if (view_mode === VIEW_MODE.YEAR) {
          this.options.step = 24 * 365;
          this.options.column_width = 120;
        }
      }
    }, {
      key: "setup_dates",
      value: function setup_dates() {
        this.setup_gantt_dates();
        this.setup_date_values();
      }
    }, {
      key: "setup_gantt_dates",
      value: function setup_gantt_dates() {
        this.gantt_start = this.gantt_end = null;
        var _iterator4 = _createForOfIteratorHelper(this.tasks),
          _step4;
        try {
          for (_iterator4.s(); !(_step4 = _iterator4.n()).done;) {
            var task = _step4.value;
            // set global start and end date
            if (!this.gantt_start || task._start < this.gantt_start) {
              this.gantt_start = task._start;
            }
            if (!this.gantt_end || task._end > this.gantt_end) {
              this.gantt_end = task._end;
            }
          }
        } catch (err) {
          _iterator4.e(err);
        } finally {
          _iterator4.f();
        }
        this.gantt_start = date_utils.start_of(this.gantt_start, 'day');
        this.gantt_end = date_utils.start_of(this.gantt_end, 'day');

        // add date padding on both sides
        if (this.view_is([VIEW_MODE.QUARTER_DAY, VIEW_MODE.HALF_DAY, VIEW_MODE.DAY])) {
          this.gantt_start = date_utils.add(this.gantt_start, -7, 'day');
          this.gantt_end = date_utils.add(this.gantt_end, 31, 'day');
        } else if (this.view_is(VIEW_MODE.WEEK)) {
          var tempDate = date_utils.add(this.gantt_start, -3, 'month');
          this.gantt_start = date_utils.start_of(tempDate, "month");
          this.gantt_end = date_utils.add(this.gantt_end, 1, 'year');
        } else if (this.view_is(VIEW_MODE.MONTH)) {
          var _tempDate = date_utils.add(this.gantt_start, -3, 'month');
          this.gantt_start = date_utils.start_of(_tempDate, "month");
          this.gantt_start = date_utils.start_of(_tempDate, "month");
          this.gantt_end = date_utils.add(this.gantt_end, 2, 'year');
        } else if (this.view_is(VIEW_MODE.YEAR)) {
          var _tempDate2 = date_utils.add(this.gantt_start, -2, 'year');
          this.gantt_start = date_utils.start_of(_tempDate2, "month");
          this.gantt_end = date_utils.add(this.gantt_end, 2, 'year');
        }
      }
    }, {
      key: "setup_date_values",
      value: function setup_date_values() {
        this.dates = [];
        var cur_date = null;
        while (cur_date === null || cur_date < this.gantt_end) {
          if (!cur_date) {
            cur_date = date_utils.clone(this.gantt_start);
          } else {
            if (this.view_is(VIEW_MODE.YEAR)) {
              cur_date = date_utils.add(cur_date, 1, 'year');
            } else if (this.view_is(VIEW_MODE.WEEK)) {
              cur_date = date_utils.add(cur_date, 7, 'day');
            } else if (this.view_is(VIEW_MODE.MONTH)) {
              cur_date = date_utils.add(cur_date, 1, 'month');
            } else {
              //Step is set to 24 hours
              cur_date = date_utils.add(cur_date, this.options.step, 'hour');
            }
          }
          this.dates.push(cur_date);
        }
      }
    }, {
      key: "bind_events",
      value: function bind_events() {
        this.bind_grid_click();
        this.bind_bar_events();
      }
    }, {
      key: "render",
      value: function render() {
        this.clear();
        this.setup_layers();
        this.make_grid();
        this.make_dates();
        this.make_bars();
        this.make_arrows();
        this.map_arrows_on_bars();
        this.set_width();
        this.set_scroll_position();
      }
    }, {
      key: "setup_layers",
      value: function setup_layers() {
        this.layers = {};
        var layers = ['grid', 'date', 'arrow', 'progress', 'bar', 'details'];
        // make group layers
        for (var _i = 0, _layers = layers; _i < _layers.length; _i++) {
          var layer = _layers[_i];
          this.layers[layer] = createSVG('g', {
            "class": layer,
            append_to: this.$svg
          });
        }
      }
    }, {
      key: "make_grid",
      value: function make_grid() {
        this.make_grid_background();
        this.make_grid_rows();
        this.make_grid_header();
        this.make_grid_ticks();
        this.make_grid_highlights();
      }
    }, {
      key: "make_grid_background",
      value: function make_grid_background() {
        var grid_width = this.dates.length * this.options.column_width;
        var grid_height = this.options.header_height + this.options.padding + (this.options.bar_height + this.options.padding) * (this.tasks.length + this.options.additional_rows);
        createSVG('rect', {
          x: 0,
          y: 0,
          width: grid_width,
          height: grid_height,
          "class": 'grid-background',
          append_to: this.layers.grid
        });
        $.attr(this.$svg, {
          height: grid_height + this.options.padding + 100,
          width: '100%'
        });
      }
    }, {
      key: "make_grid_rows",
      value: function make_grid_rows() {
        var rows_layer = createSVG('g', {
          append_to: this.layers.grid
        });
        var lines_layer = createSVG('g', {
          append_to: this.layers.grid
        });
        var row_width = this.dates.length * this.options.column_width;
        var row_height = this.options.bar_height + this.options.padding;
        var row_y = this.options.header_height + this.options.padding / 2;
        var numRows = this.tasks.length + this.options.additional_rows;
        for (var i = 0; i < numRows; i++) {
          createSVG('rect', {
            x: 0,
            y: row_y,
            width: row_width,
            height: row_height,
            "class": 'grid-row',
            append_to: rows_layer
          });
          createSVG('line', {
            x1: 0,
            y1: row_y + row_height,
            x2: row_width,
            y2: row_y + row_height,
            "class": 'row-line',
            append_to: lines_layer
          });
          /*
          createSVG('text', {
              x: 10,
              y: row_y+20,
              innerHTML: "test",
              class: '',
              append_to: rows_layer,
          });
             */

          row_y += this.options.bar_height + this.options.padding;
        }
      }
    }, {
      key: "make_grid_header",
      value: function make_grid_header() {
        var header_width = this.dates.length * this.options.column_width;
        var header_height = this.options.header_height + 10;
        createSVG('rect', {
          x: 0,
          y: 0,
          width: header_width,
          height: header_height,
          "class": 'grid-header',
          append_to: this.layers.grid
        });
        createSVG('rect', {
          x: 0,
          y: header_height / 2 - 8,
          width: header_width,
          height: 1,
          "class": 'grid-header-line',
          append_to: this.layers.grid
        });
      }
    }, {
      key: "make_grid_ticks",
      value: function make_grid_ticks() {
        var tick_x = 0;
        var tick_y = this.options.header_height + this.options.padding / 2;
        var tick_height = (this.options.bar_height + this.options.padding) * (this.tasks.length + this.options.additional_rows);
        var _iterator5 = _createForOfIteratorHelper(this.dates),
          _step5;
        try {
          for (_iterator5.s(); !(_step5 = _iterator5.n()).done;) {
            var date = _step5.value;
            var tick_class = 'tick';
            // thick tick for monday
            if (this.view_is(VIEW_MODE.DAY) && date.getDate() === 1) {
              tick_class += ' thick';
            }
            // thick tick for first week
            if (this.view_is(VIEW_MODE.WEEK) && date.getDate() >= 1 && date.getDate() < 8) {
              tick_class += ' thick';
            }
            // thick ticks for quarters
            if (this.view_is(VIEW_MODE.MONTH) && date.getMonth() % 3 === 0) {
              tick_class += ' thick';
            }
            createSVG('path', {
              d: "M ".concat(tick_x, " ").concat(tick_y, " v ").concat(tick_height),
              "class": tick_class + " date-" + date.getDate() + " month-" + (date.getMonth() + 1),
              append_to: this.layers.grid
            });
            if (this.view_is(VIEW_MODE.MONTH)) {
              tick_x += this.options.column_width / 30 * date_utils.get_days_in_month(date);
            } else {
              tick_x += this.options.column_width;
            }
          }
        } catch (err) {
          _iterator5.e(err);
        } finally {
          _iterator5.f();
        }
      }
    }, {
      key: "make_grid_highlights",
      value: function make_grid_highlights() {
        // highlight today's date
        if (this.view_is(VIEW_MODE.DAY)) {
          var x = date_utils.diff(date_utils.today(), this.gantt_start, 'hour') / this.options.step * this.options.column_width;
          var y = 0;
          var width = this.options.column_width;
          var height = (this.options.bar_height + this.options.padding) * (this.tasks.length + this.options.additional_rows) + this.options.header_height + this.options.padding / 2;
          createSVG('rect', {
            x: x,
            y: y,
            width: width,
            height: height,
            "class": 'today-highlight',
            append_to: this.layers.grid
          });
        } else if (this.view_is(VIEW_MODE.WEEK) || this.view_is(VIEW_MODE.MONTH)) {
          //var start = new Date(this.gantt_start.getFullYear(), this.gantt_start.getMonth(), 1);

          var _x = date_utils.diff(date_utils.today(), this.gantt_start, 'hour') / this.options.step * this.options.column_width;
          var _y = this.options.header_height + this.options.padding / 2;
          var _width = 2;
          var _height = (this.options.bar_height + this.options.padding) * (this.tasks.length + this.options.additional_rows) + this.options.padding / 2;
          createSVG('rect', {
            x: _x,
            y: _y,
            width: _width,
            height: _height,
            "class": 'today-highlight',
            append_to: this.layers.grid
          });
          createSVG('circle', {
            cx: _x + 1,
            cy: _y - 8,
            r: 10,
            "class": 'today-highlight-label-bg',
            append_to: this.layers.grid
          });
          createSVG('text', {
            x: _x + 1,
            y: _y - 4,
            innerHTML: date_utils.today().getDate(),
            "class": 'today-highlight-label',
            append_to: this.layers.grid
          });
        }
      }
    }, {
      key: "make_dates",
      value: function make_dates() {
        var _iterator6 = _createForOfIteratorHelper(this.get_dates_to_draw()),
          _step6;
        try {
          for (_iterator6.s(); !(_step6 = _iterator6.n()).done;) {
            var date = _step6.value;
            createSVG('text', {
              x: date.lower_x,
              y: date.lower_y,
              innerHTML: date.lower_text,
              "class": 'lower-text',
              append_to: this.layers.date
            });
            if (date.upper_text) {
              var $upper_text = createSVG('text', {
                x: date.upper_x,
                y: date.upper_y,
                innerHTML: date.upper_text,
                "class": 'upper-text',
                append_to: this.layers.date
              });

              // remove out-of-bound dates
              if ($upper_text.getBBox().x2 > this.layers.grid.getBBox().width) {
                $upper_text.remove();
              }
            }
          }
        } catch (err) {
          _iterator6.e(err);
        } finally {
          _iterator6.f();
        }
      }
    }, {
      key: "get_dates_to_draw",
      value: function get_dates_to_draw() {
        var _this9 = this;
        var last_date = null;
        var dates = this.dates.map(function (date, i) {
          var d = _this9.get_date_info(date, last_date, i);
          last_date = date;
          return d;
        });
        return dates;
      }
    }, {
      key: "get_date_info",
      value: function get_date_info(date, last_date, i) {
        if (!last_date) {
          last_date = date_utils.add(date, 1, 'year');
        }
        var date_text = {
          'Quarter Day_lower': date_utils.format(date, 'HH', this.options.language),
          'Half Day_lower': date_utils.format(date, 'HH', this.options.language),
          Day_lower: date.getDate() !== last_date.getDate() ? date_utils.format(date, 'D', this.options.language) : '',
          Week_lower: date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'D MMM', this.options.language) : date_utils.format(date, 'D', this.options.language),
          Month_lower: date_utils.format(date, 'MMMM', this.options.language),
          Year_lower: date_utils.format(date, 'YYYY', this.options.language),
          'Quarter Day_upper': date.getDate() !== last_date.getDate() ? date_utils.format(date, 'D MMM', this.options.language) : '',
          'Half Day_upper': date.getDate() !== last_date.getDate() ? date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'D MMM', this.options.language) : date_utils.format(date, 'D', this.options.language) : '',
          Day_upper: date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'MMMM', this.options.language) : '',
          Week_upper: date.getMonth() !== last_date.getMonth() ? date_utils.format(date, 'MMMM', this.options.language) : '',
          Month_upper: date.getFullYear() !== last_date.getFullYear() ? date_utils.format(date, 'YYYY', this.options.language) : '',
          Year_upper: date.getFullYear() !== last_date.getFullYear() ? date_utils.format(date, 'YYYY', this.options.language) : ''
        };
        var base_pos = {
          x: i * this.options.column_width,
          lower_y: this.options.header_height - 4,
          upper_y: this.options.header_height - 34
        };
        var x_pos = {
          'Quarter Day_lower': this.options.column_width * 4 / 2,
          'Quarter Day_upper': 0,
          'Half Day_lower': this.options.column_width * 2 / 2,
          'Half Day_upper': 0,
          Day_lower: this.options.column_width / 2,
          Day_upper: this.options.column_width * 30 / 2,
          Week_lower: 0,
          Week_upper: this.options.column_width * 4 / 2,
          Month_lower: this.options.column_width / 2,
          Month_upper: this.options.column_width * 12 / 2,
          Year_lower: this.options.column_width / 2,
          Year_upper: this.options.column_width * 30 / 2
        };
        return {
          upper_text: date_text["".concat(this.options.view_mode, "_upper")],
          lower_text: date_text["".concat(this.options.view_mode, "_lower")],
          upper_x: base_pos.x + x_pos["".concat(this.options.view_mode, "_upper")],
          upper_y: base_pos.upper_y - 2,
          lower_x: base_pos.x + x_pos["".concat(this.options.view_mode, "_lower")],
          lower_y: base_pos.lower_y - 10
        };
      }
    }, {
      key: "make_bars",
      value: function make_bars() {
        var _this10 = this;
        this.bars = this.tasks.map(function (task) {
          var bar = new Bar(_this10, task);
          _this10.layers.bar.appendChild(bar.group);
          return bar;
        });
      }
    }, {
      key: "make_arrows",
      value: function make_arrows() {
        var _this11 = this;
        this.arrows = [];
        var _iterator7 = _createForOfIteratorHelper(this.tasks),
          _step7;
        try {
          var _loop = function _loop() {
            var task = _step7.value;
            var arrows = [];
            arrows = task.dependencies.map(function (task_id) {
              var dependency = _this11.get_task(task_id);
              if (!dependency) return;
              var arrow = new Arrow(_this11, _this11.bars[dependency._index],
              // from_task
              _this11.bars[task._index] // to_task
              );
              _this11.layers.arrow.appendChild(arrow.element);
              return arrow;
            }).filter(Boolean); // filter falsy values
            _this11.arrows = _this11.arrows.concat(arrows);
          };
          for (_iterator7.s(); !(_step7 = _iterator7.n()).done;) {
            _loop();
          }
        } catch (err) {
          _iterator7.e(err);
        } finally {
          _iterator7.f();
        }
      }
    }, {
      key: "map_arrows_on_bars",
      value: function map_arrows_on_bars() {
        var _this12 = this;
        var _iterator8 = _createForOfIteratorHelper(this.bars),
          _step8;
        try {
          var _loop2 = function _loop2() {
            var bar = _step8.value;
            bar.arrows = _this12.arrows.filter(function (arrow) {
              return arrow.from_task.task.id === bar.task.id || arrow.to_task.task.id === bar.task.id;
            });
          };
          for (_iterator8.s(); !(_step8 = _iterator8.n()).done;) {
            _loop2();
          }
        } catch (err) {
          _iterator8.e(err);
        } finally {
          _iterator8.f();
        }
      }
    }, {
      key: "set_width",
      value: function set_width() {
        var cur_width = this.$svg.getBoundingClientRect().width;
        var actual_width = this.$svg.querySelector('.grid .grid-row').getAttribute('width');
        if (cur_width < actual_width) {
          this.$svg.setAttribute('width', actual_width);
        }
      }
    }, {
      key: "set_scroll_position",
      value: function set_scroll_position() {
        var parent_element = this.$svg.parentElement;
        if (!parent_element) return;
        var hours_before_first_task = date_utils.diff(date_utils.today(), this.gantt_start, 'hour');
        var scroll_pos = hours_before_first_task / this.options.step * this.options.column_width - 2 * this.options.column_width;
        parent_element.parentElement.scrollLeft = scroll_pos;
      }
    }, {
      key: "bind_grid_click",
      value: function bind_grid_click() {
        var _this13 = this;
        $.on(this.$svg, this.options.popup_trigger, '.grid-row, .grid-header', function () {
          _this13.unselect_all();
          _this13.hide_popup();
        });
      }
    }, {
      key: "sort_bars",
      value: function sort_bars() {
        var changed_bars = [];
        if (!this.bars) {
          return changed_bars;
        }
        this.bars = this.bars.sort(function (b0, b1) {
          return b0.$bar.getY() - b1.$bar.getY();
        });
        this.tasks = this.bars.map(function (b, i) {
          var task = b.task;
          if (task._index !== i) {
            changed_bars.push(b);
          }
          task._index = i;
          return task;
        });
        return changed_bars;
      }
    }, {
      key: "bind_bar_events",
      value: function bind_bar_events() {
        var _this14 = this;
        var is_dragging = false;
        var x_on_start = 0;
        var y_on_start = 0;
        var is_resizing_left = false;
        var is_resizing_right = false;
        var parent_bar_id = null;
        var bars = []; // instanceof Bars, the dragged bar and its children
        var min_y = this.options.header_height;
        var max_y = this.options.header_height + this.tasks.length * (this.options.bar_height + this.options.padding);
        this.bar_being_dragged = null; // instanceof dragged bar

        function action_in_progress() {
          return is_dragging || is_resizing_left || is_resizing_right;
        }
        $.on(this.$svg, 'mousedown', '.bar-wrapper, .handle', function (e, element) {
          var bar_wrapper = $.closest('.bar-wrapper', element);
          if (element.classList.contains('left')) {
            is_resizing_left = true;
          } else if (element.classList.contains('right')) {
            is_resizing_right = true;
          } else if (element.classList.contains('bar-wrapper')) {
            is_dragging = true;
          }
          bar_wrapper.classList.add('active');
          x_on_start = e.offsetX;
          y_on_start = e.offsetY;
          parent_bar_id = bar_wrapper.getAttribute('data-id');
          var ids = [parent_bar_id].concat(_toConsumableArray(_this14.get_all_dependent_tasks(parent_bar_id)));
          bars = ids.map(function (id) {
            var bar = _this14.get_bar(id);
            if (parent_bar_id === id) {
              _this14.bar_being_dragged = bar;
            }
            var $bar = bar.$bar;
            $bar.ox = $bar.getX();
            $bar.oy = $bar.getY();
            $bar.owidth = $bar.getWidth();
            $bar.finaldx = 0;
            $bar.finaldy = 0;
            return bar;
          });
        });
        $.on(this.$svg, 'mousemove', function (e) {
          if (!action_in_progress()) return;
          var dx = e.offsetX - x_on_start;
          var dy = e.offsetY - y_on_start;
          _this14.hide_popup();

          // update the dragged bar
          var bar_being_dragged = _this14.bar_being_dragged;
          bar_being_dragged.$bar.finaldx = _this14.get_snap_position(dx);
          if (is_resizing_left) {
            bar_being_dragged.update_bar_position({
              x: bar_being_dragged.$bar.ox + bar_being_dragged.$bar.finaldx,
              width: bar_being_dragged.$bar.owidth - bar_being_dragged.$bar.finaldx
            });
          } else if (is_resizing_right) {
            bar_being_dragged.update_bar_position({
              width: bar_being_dragged.$bar.owidth + bar_being_dragged.$bar.finaldx
            });
          } else if (is_dragging) {
            var y = bar_being_dragged.$bar.oy + dy;
            if (y < min_y) {
              y = min_y;
            } else if (y > max_y) {
              y = max_y;
            }
            bar_being_dragged.update_bar_position({
              x: bar_being_dragged.$bar.ox + bar_being_dragged.$bar.finaldx,
              y: _this14.options.sortable ? y : null
            });
          }

          // update children
          bars.forEach(function (bar) {
            if (bar.task.id === parent_bar_id) {
              return;
            }
            var $bar = bar.$bar;
            $bar.finaldx = _this14.get_snap_position(dx);
            _this14.hide_popup();
            if (is_resizing_left) {
              bar.update_bar_position({
                x: $bar.ox + $bar.finaldx
              });
            } else if (is_dragging) {
              bar.update_bar_position({
                x: $bar.ox + $bar.finaldx
              });
            }
          });

          // update y pos
          if (_this14.options.sortable && is_dragging && Math.abs(dy - bar_being_dragged.$bar.finaldy) > bar_being_dragged.height) {
            _this14.sort_bars().map(function (bar) {
              var y = bar.compute_y();
              if (bar.task.id === parent_bar_id) {
                bar.$bar.finaldy = y - bar.$bar.oy;
                return;
              }
              bar.update_bar_position({
                y: y
              });
            });
          }
        });
        document.addEventListener('mouseup', function (e) {
          var dy = e.offsetY - y_on_start;
          if (is_dragging || is_resizing_left || is_resizing_right) {
            bars.forEach(function (bar) {
              bar.group.classList.remove('active');
              var $bar = bar.$bar;
              if ($bar.finaldx) {
                bar.date_changed();
                bar.set_action_completed();
              }
            });
            var $bar = _this14.bar_being_dragged.$bar;
            if (_this14.options.sortable && dy !== $bar.finaldy) {
              _this14.bar_being_dragged.update_bar_position({
                y: $bar.oy + $bar.finaldy
              });
              _this14.bar_being_dragged.sort_changed();
            }
          }
          _this14.bar_being_dragged = null;
          is_dragging = false;
          is_resizing_left = false;
          is_resizing_right = false;
        });
        $.on(this.$container, 'scroll', function (e) {
          var elements = document.querySelectorAll('.bar-wrapper');
          var localBars = [];
          var ids = [];
          var dx = void 0;
          _this6.layers.date.setAttribute('transform', 'translate(0,' + e.currentTarget.scrollTop + ')');
          if (x_on_scroll_start) {
            dx = e.currentTarget.scrollLeft - x_on_scroll_start;
          }
          Array.prototype.forEach.call(elements, function (el, i) {
            ids.push(el.getAttribute('data-id'));
          });
          if (dx) {
            localBars = ids.map(function (id) {
              return _this6.get_bar(id);
            });
            localBars.forEach(function (bar) {
              bar.update_label_position_on_horizontal_scroll({
                x: dx,
                sx: e.currentTarget.scrollLeft
              });
            });
          }
          x_on_scroll_start = e.currentTarget.scrollLeft;
        });
        this.bind_bar_progress();
      }
    }, {
      key: "bind_bar_progress",
      value: function bind_bar_progress() {
        var _this15 = this;
        var x_on_start = 0;
        var y_on_start = 0;
        var is_resizing = null;
        var bar = null;
        var $bar_progress = null;
        var $bar = null;
        if (this.options.static_progress_indicator == false) {
          $.on(this.$svg, 'mousedown', '.handle.progress', function (e, handle) {
            is_resizing = true;
            x_on_start = e.offsetX;
            y_on_start = e.offsetY;
            var $bar_wrapper = $.closest('.bar-wrapper', handle);
            var id = $bar_wrapper.getAttribute('data-id');
            bar = _this15.get_bar(id);
            $bar_progress = bar.$bar_progress;
            $bar = bar.$bar;
            $bar_progress.finaldx = 0;
            $bar_progress.owidth = $bar_progress.getWidth();
            $bar_progress.min_dx = -$bar_progress.getWidth();
            $bar_progress.max_dx = $bar.getWidth() - $bar_progress.getWidth();
          });
          $.on(this.$svg, 'mousemove', function (e) {
            if (!is_resizing) return;
            var dx = e.offsetX - x_on_start;
            e.offsetY - y_on_start;
            if (dx > $bar_progress.max_dx) {
              dx = $bar_progress.max_dx;
            }
            if (dx < $bar_progress.min_dx) {
              dx = $bar_progress.min_dx;
            }
            var $handle = bar.$handle_progress;
            $.attr($bar_progress, 'width', $bar_progress.owidth + dx);
            $.attr($handle, 'points', bar.get_progress_polygon_points());
            $bar_progress.finaldx = dx;
          });
          $.on(this.$svg, 'mouseup', function () {
            is_resizing = false;
            if (!($bar_progress && $bar_progress.finaldx)) return;
            bar.progress_changed();
            bar.set_action_completed();
          });
        }
      }
    }, {
      key: "get_all_dependent_tasks",
      value: function get_all_dependent_tasks(task_id) {
        var _this16 = this;
        var out = [];
        var to_process = [task_id];
        while (to_process.length) {
          var deps = to_process.reduce(function (acc, curr) {
            acc = acc.concat(_this16.dependency_map[curr]);
            return acc;
          }, []);
          out = out.concat(deps);
          to_process = deps.filter(function (d) {
            return !to_process.includes(d);
          });
        }
        return out.filter(Boolean);
      }
    }, {
      key: "get_snap_position",
      value: function get_snap_position(dx) {
        var odx = dx,
          rem,
          position;
        if (this.view_is(VIEW_MODE.WEEK)) {
          rem = dx % (this.options.column_width / 7);
          position = odx - rem + (rem < this.options.column_width / 14 ? 0 : this.options.column_width / 7);
        } else if (this.view_is(VIEW_MODE.MONTH)) {
          rem = dx % (this.options.column_width / 30);
          position = odx - rem + (rem < this.options.column_width / 60 ? 0 : this.options.column_width / 30);
        } else if (this.view_is(VIEW_MODE.DAY)) {
          rem = dx % (this.options.column_width / 24);
          position = odx - rem + (rem < this.options.column_width / 48 ? 0 : this.options.column_width / 24);
        } else {
          rem = dx % this.options.column_width;
          position = odx - rem + (rem < this.options.column_width / 2 ? 0 : this.options.column_width);
        }
        return position;
      }
    }, {
      key: "unselect_all",
      value: function unselect_all() {
        _toConsumableArray(this.$svg.querySelectorAll('.bar-wrapper')).forEach(function (el) {
          el.classList.remove('active');
        });
      }
    }, {
      key: "view_is",
      value: function view_is(modes) {
        var _this17 = this;
        if (typeof modes === 'string') {
          return this.options.view_mode === modes;
        }
        if (Array.isArray(modes)) {
          return modes.some(function (mode) {
            return _this17.options.view_mode === mode;
          });
        }
        return false;
      }
    }, {
      key: "get_task",
      value: function get_task(id) {
        return this.tasks.find(function (task) {
          return task.id === id;
        });
      }
    }, {
      key: "get_bar",
      value: function get_bar(id) {
        return this.bars.find(function (bar) {
          return bar.task.id === id;
        });
      }
    }, {
      key: "show_popup",
      value: function show_popup(options) {
        if (!this.popup) {
          this.popup = new Popup(this.popup_wrapper, this.options.custom_popup_html, this);
        }
        this.popup.show(options);
      }
    }, {
      key: "hide_popup",
      value: function hide_popup() {
        this.popup && this.popup.hide();
      }
    }, {
      key: "trigger_event",
      value: function trigger_event(event, args) {
        if (this.options['on_' + event]) {
          this.options['on_' + event].apply(null, args);
        }
      }

      /**
       * Gets the oldest starting date from the list of tasks
       *
       * @returns Date
       * @memberof Gantt
       */
    }, {
      key: "get_oldest_starting_date",
      value: function get_oldest_starting_date() {
        return this.tasks.map(function (task) {
          return task._start;
        }).reduce(function (prev_date, cur_date) {
          return cur_date <= prev_date ? cur_date : prev_date;
        });
      }

      /**
       * Clear all elements from the parent svg element
       *
       * @memberof Gantt
       */
    }, {
      key: "clear",
      value: function clear() {
        this.$svg.innerHTML = '';
      }
    }]);
  }();
  Gantt.VIEW_MODE = VIEW_MODE;
  function generate_id(task) {
    return task.name + '_' + Math.random().toString(36).slice(2, 12);
  }
  return Gantt;
})());

/***/ }),

/***/ "i18n":
/*!***************************************!*\
  !*** external "window.leantime.i18n" ***!
  \***************************************/
/***/ ((module) => {

module.exports = __WEBPACK_EXTERNAL_MODULE_i18n__;

/***/ }),

/***/ "./public/assets/js/app/components/dates/dateHelper.module.mjs":
/*!*********************************************************************!*\
  !*** ./public/assets/js/app/components/dates/dateHelper.module.mjs ***!
  \*********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   getFormatFromSettings: () => (/* binding */ getFormatFromSettings),
/* harmony export */   mapFormat: () => (/* binding */ mapFormat)
/* harmony export */ });
var phpFormatSeed = {
  'A': 'A',
  // Uppercase Ante meridiem and Post meridiem: AM or PM
  'a': 'a',
  // Lowercase Ante meridiem and Post meridiem: am or pm
  'B': 'B',
  // Swatch Internet Time. There's no JavaScript equivalent
  'c': 'c',
  // ISO 8601 date: 2004-02-12T15:19:21+00:00
  'D': 'D',
  // Textual representation of a day - Mon through Sun
  'd': 'd',
  // Day of the month, 2 digits with leading zeros: 01 to 31
  'e': 'e',
  // Timezone identifier (deprecated in Moment.js)
  'F': 'F',
  // Full month name, e.g. January
  'G': 'G',
  // 24-hour format of an hour without leading zeros: 0 to 23
  'g': 'g',
  // 12-hour format of an hour without leading zeros: 1 to 12
  'H': 'H',
  // 24-hour format of an hour with leading zeros: 00 to 23
  'h': 'h',
  // 12-hour format of an hour with leading zeros: 01 to 12
  'I': 'I',
  // Whether or not the date is in daylight saving time: 1 if Daylight Saving Time, 0 otherwise.
  'i': 'i',
  // Minutes with leading zeros: 00 to 59
  'j': 'j',
  // Day of the month without leading zeros: 1 to 31
  'L': 'L',
  // Whether it's a leap year: 1 if it is a leap year, 0 otherwise.
  'l': 'l',
  // A full textual representation of the day of the week: Sunday through Saturday
  'm': 'm',
  // Numeric representation of a month, with leading zero: 01 through 12
  'M': 'M',
  // A short textual representation of a month, three letters: Jan through Dec
  'N': 'N',
  // ISO-8601 numeric representation of the day of the week: 1 (for Monday) through 7 (for Sunday)
  'n': 'n',
  // Numeric representation of a month, without leading zeros: 1 through 12
  'O': 'O',
  // Difference to Greenwich time (GMT) in hours: Example: +0200
  'o': 'o',
  // ISO-8601 year number
  'P': 'P',
  // Difference to Greenwich time (GMT) with colon between hours and minutes: Example: +02:00
  'r': 'r',
  // » RFC 2822 formatted date: Example: Thu, 21 Dec 2000 16:01:07 +0200
  'S': 'S',
  // English ordinal suffix for the day of the month, 2 characters: st, nd, rd or th. Works well with j
  's': 's',
  // Seconds, with leading zeros: 00 through 59
  'T': 'T',
  // Timezone abbreviation: Examples: EST, MDT, PDT ...
  't': 't',
  // Number of days in the given month: 28 through 31
  'U': 'U',
  // The seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
  'u': 'u',
  // Microseconds: Example: 654321
  'v': 'v',
  // Milliseconds (added in PHP 7.0.0). Example: 654
  'W': 'W',
  // ISO-8601 week number of year, weeks starting on Monday: Example: 42 (the 42nd week in the year)
  'w': 'w',
  // Numeric representation of the day of the week: 0 (for Sunday) through 6 (for Saturday)
  'Z': 'Z',
  // Timezone offset in seconds. The offset for timezones west of UTC is always negative, and for those east of UTC is always positive.
  'z': 'z',
  // The day of the year (starting from 0): 0 through 365
  'Y': 'Y' // A full numeric representation of a year, 4 digits: Examples: 1999 or 2003
};
var target = {
  "jquery": {},
  "luxon": {},
  "flatpickr": {}
};
target.jquery = {
  'd': 'dd',
  // Day of month with leading zeroes
  'D': 'D',
  // Short day name
  'j': 'd',
  // Day of month with no leading zeroes
  'l': 'DD',
  // Full day name
  'N': '',
  // jQuery DatePicker does not have an ISO-8601 numeric representation of the day of the week
  'S': '',
  // jQuery DatePicker does not support ordinal suffixes for the day of the month
  'w': '',
  // jQuery DatePicker does not have a numeric day of week,
  'z': 'o',
  // Day of the year
  'W': '',
  // jQuery DatePicker does not have ISO-8601 week number of year
  'F': 'MM',
  // Full month name
  'm': 'mm',
  // Month of the year, leading zero
  'M': 'M',
  // Short month name
  'n': 'm',
  // Month of the year without leading zero
  't': '',
  // jQuery DatePicker does not have number of days in the given month
  'L': '',
  // jQuery DatePicker does not have leap year detection
  'o': 'yy',
  // ISO-8601 year number - can be approximated with four digit year
  'Y': 'yy',
  // Year, four digits
  'y': 'y',
  // Year, two digits
  'a': '',
  // jQuery DatePicker does not have lowercase ante meridiem and post meridiem
  'A': '',
  // jQuery DatePicker does not have uppercase ante meridiem and post meridiem
  'B': '',
  // jQuery DatePicker does not support Swatch Internet Time
  'g': '',
  // jQuery DatePicker does not support 12-hour format without leading zero
  'G': '',
  // jQuery DatePicker does not support 24-hour format without leading zero
  'h': '',
  // jQuery DatePicker does not support 12-hour format with leading zero
  'H': '',
  // jQuery DatePicker does not support 24-hour format with leading zero,
  'i': '',
  // jQuery DatePicker does not support minutes with leading zero
  's': '',
  // jQuery DatePicker does not support seconds with leading zero
  'u': '',
  // jQuery DatePicker does not support microseconds
  'e': '',
  // jQuery DatePicker does not support timezone identifiers
  'I': '',
  // jQuery DatePicker does not support whether or not the date is in daylight saving time
  'O': '',
  // jQuery DatePicker does not support difference to Greenwich time
  'P': '',
  // jQuery DatePicker does not have difference to Greenwich time
  'T': '',
  // jQuery DatePicker does not support timezone abbreviation
  'Z': '',
  // jQuery DatePicker does not support timezone offset in seconds
  'c': '',
  // jQuery DatePicker does not support ISO 8601 dates
  'r': '',
  // jQuery DatePicker does not support RFC 2822 dates
  'U': '@' // Unix timestamp - seconds since January 1 1970 00:00:00 GMT
};
target.flatpickr = {
  'd': 'd',
  // Day of the month, two digits with leading zeros
  'D': 'D',
  // A textual representation of a day, abbreviated (Mon through Sun)
  'j': 'j',
  // Day of the month without leading zeros
  'l': 'l',
  // A full textual representation of the day of the week (Sunday through Saturday)
  'N': '',
  // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
  'S': 'J',
  // English ordinal suffix for the day of the month, 2 characters.
  'w': 'w',
  // Numeric representation of the day of the week (0 for Sunday through 6 for Saturday). Converted to ISO (1 for Monday through 7 for Sunday)
  'z': '',
  // Day of the year (0 through 365)
  'W': '',
  // ISO-8601 week number of year, weeks starting on Monday
  'F': 'F',
  // A full textual representation of a month (January through December)
  'm': 'm',
  // Numeric representation of a month, with leading zeros (01 through 12)
  'M': 'M',
  // A short textual representation of a month (Jan through Dec)
  'n': 'n',
  // Numeric representation of a month, without leading zeros (1 through 12)
  't': '',
  // Number of days in the given month. No equivalent in Luxon.
  'L': '',
  // Whether it's a leap year (1 if it is a leap year, 0 otherwise). No equivalent in Luxon.
  'o': '',
  // ISO-8601 year number
  'Y': 'Y',
  // A full numeric representation of a year, 4 digits
  'y': 'y',
  // A two digit representation of a year
  'a': 'K',
  // Lowercase Ante meridiem and Post meridiem (am or pm)
  'A': 'K',
  // Uppercase Ante meridiem and Post meridiem (AM or PM)
  'B': '',
  // Swatch Internet time (000 through 999). No equivalent in Luxon.
  'g': '',
  // 12-hour format of an hour without leading zeros (1 through 12)
  'G': 'G',
  // 24-hour format of an hour without leading zeros (0 through 23)
  'h': 'h',
  // 12-hour format of an hour with leading zeros (01 through 12)
  'H': 'H',
  // 24-hour format of an hour with leading zeros (00 through 23)
  'i': 'i',
  // Minutes with leading zeros (00 to 59)
  's': 'S',
  // Seconds with leading zeros (00 through 59)
  'u': '',
  // Microseconds (up to 999), mapped to milliseconds
  'v': '',
  // Milliseconds (added in PHP 7.0.0). No equivalent in Luxon, so we map it to the same as 'u'
  'e': '',
  // Timezone identifier (e.g., UTC, GMT, Atlantic/Azores)
  'I': '',
  // Whether or not the date is in daylight saving time. No equivalent in Luxon.
  'O': '',
  // Difference to Greenwich time (GMT) without colon between hours and minutes (+0200)
  'P': '',
  // Difference to Greenwich time (GMT) with colon between hours and minutes (+02:00)
  'T': '',
  // Timezone (e.g., EST, MDT). Mapped to the closest thing in Luxon, display in the user's locale.
  'Z': '',
  // Timezone offset in seconds. The offset for time zones west of UTC is always negative, and for those east of UTC is always positive. No equivalent in Luxon.
  'c': '',
  // ISO 8601 formatted date.
  'r': '',
  // RFC2822 formatted date.
  'U': 'U' // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
};
target.luxon = {
  'd': 'dd',
  // Day of the month, two digits with leading zeros
  'D': 'ccc',
  // A textual representation of a day, abbreviated (Mon through Sun)
  'j': 'd',
  // Day of the month without leading zeros
  'l': 'cccc',
  // A full textual representation of the day of the week (Sunday through Saturday)
  'N': 'c',
  // ISO-8601 numeric representation of the day of the week (1 for Monday through 7 for Sunday)
  'S': '',
  // English ordinal suffix for the day of the month, 2 characters. No equivalent in Luxon.
  'w': 'c',
  // Numeric representation of the day of the week (0 for Sunday through 6 for Saturday). Converted to ISO (1 for Monday through 7 for Sunday)
  'z': 'o',
  // Day of the year (0 through 365)
  'W': 'WW',
  // ISO-8601 week number of year, weeks starting on Monday
  'F': 'MMMM',
  // A full textual representation of a month (January through December)
  'm': 'LL',
  // Numeric representation of a month, with leading zeros (01 through 12)
  'M': 'LLL',
  // A short textual representation of a month (Jan through Dec)
  'n': 'L',
  // Numeric representation of a month, without leading zeros (1 through 12)
  't': '',
  // Number of days in the given month. No equivalent in Luxon.
  'L': '',
  // Whether it's a leap year (1 if it is a leap year, 0 otherwise). No equivalent in Luxon.
  'o': 'kk',
  // ISO-8601 year number
  'Y': 'yyyy',
  // A full numeric representation of a year, 4 digits
  'y': 'yy',
  // A two digit representation of a year
  'a': 'a',
  // Lowercase Ante meridiem and Post meridiem (am or pm)
  'A': 'a',
  // Uppercase Ante meridiem and Post meridiem (AM or PM)
  'B': '',
  // Swatch Internet time (000 through 999). No equivalent in Luxon.
  'g': 'h',
  // 12-hour format of an hour without leading zeros (1 through 12)
  'G': 'H',
  // 24-hour format of an hour without leading zeros (0 through 23)
  'h': 'hh',
  // 12-hour format of an hour with leading zeros (01 through 12)
  'H': 'HH',
  // 24-hour format of an hour with leading zeros (00 through 23)
  'i': 'mm',
  // Minutes with leading zeros (00 to 59)
  's': 'ss',
  // Seconds with leading zeros (00 through 59)
  'u': 'SSS',
  // Microseconds (up to 999), mapped to milliseconds
  'v': 'SSS',
  // Milliseconds (added in PHP 7.0.0). No equivalent in Luxon, so we map it to the same as 'u'
  'e': 'z',
  // Timezone identifier (e.g., UTC, GMT, Atlantic/Azores)
  'I': '',
  // Whether or not the date is in daylight saving time. No equivalent in Luxon.
  'O': 'ZZ',
  // Difference to Greenwich time (GMT) without colon between hours and minutes (+0200)
  'P': 'ZZ',
  // Difference to Greenwich time (GMT) with colon between hours and minutes (+02:00)
  'T': 'ZZZ',
  // Timezone (e.g., EST, MDT). Mapped to the closest thing in Luxon, display in the user's locale.
  'Z': '',
  // Timezone offset in seconds. The offset for time zones west of UTC is always negative, and for those east of UTC is always positive. No equivalent in Luxon.
  'c': '',
  // ISO 8601 formatted date. No equivalent in Luxon.
  'r': '',
  // RFC2822 formatted date. No equivalent in Luxon.
  'U': 'X' // Seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
};
var mapFormat = function mapFormat(inputPhpFormat, targetFormat) {
  var mapAgainstFormat = target[targetFormat];
  var mappedFormat = "";
  inputPhpFormat.split('').forEach(function (character) {
    if (mapAgainstFormat[character] !== undefined) {
      mappedFormat = mappedFormat + "" + mapAgainstFormat[character];
    } else {
      mappedFormat = mappedFormat + "" + character;
    }
  });
  return mappedFormat;
};
var getFormatFromSettings = function getFormatFromSettings(formattingKey, targetFormat) {
  var format = leantime.i18n.__("language." + formattingKey);
  return mapFormat(format, targetFormat);
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  mapFormat: mapFormat,
  getFormatFromSettings: getFormatFromSettings
});

/***/ }),

/***/ "./public/assets/js/app/core/instance-info.module.mjs":
/*!************************************************************!*\
  !*** ./public/assets/js/app/core/instance-info.module.mjs ***!
  \************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   appUrl: () => (/* binding */ appUrl),
/* harmony export */   colorScheme: () => (/* binding */ colorScheme),
/* harmony export */   companyColor: () => (/* binding */ companyColor),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   theme: () => (/* binding */ theme),
/* harmony export */   version: () => (/* binding */ version)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");

var companyColor = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=theme-color]').attr('content');
var colorScheme = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=color-scheme]').attr('content');
var theme = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=theme]').attr('content');
var appUrl = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=identifier-URL]').attr('content');
var version = jquery__WEBPACK_IMPORTED_MODULE_0__('meta[name=leantime-version]').attr('content');
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  companyColor: companyColor,
  colorScheme: colorScheme,
  theme: theme,
  appUrl: appUrl,
  version: version
});

/***/ }),

/***/ "./node_modules/luxon/src/datetime.js":
/*!********************************************!*\
  !*** ./node_modules/luxon/src/datetime.js ***!
  \********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ DateTime),
/* harmony export */   friendlyDateTime: () => (/* binding */ friendlyDateTime)
/* harmony export */ });
/* harmony import */ var _duration_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./duration.js */ "./node_modules/luxon/src/duration.js");
/* harmony import */ var _interval_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./interval.js */ "./node_modules/luxon/src/interval.js");
/* harmony import */ var _settings_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./settings.js */ "./node_modules/luxon/src/settings.js");
/* harmony import */ var _info_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./info.js */ "./node_modules/luxon/src/info.js");
/* harmony import */ var _impl_formatter_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./impl/formatter.js */ "./node_modules/luxon/src/impl/formatter.js");
/* harmony import */ var _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./zones/fixedOffsetZone.js */ "./node_modules/luxon/src/zones/fixedOffsetZone.js");
/* harmony import */ var _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./impl/locale.js */ "./node_modules/luxon/src/impl/locale.js");
/* harmony import */ var _impl_util_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./impl/util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./impl/zoneUtil.js */ "./node_modules/luxon/src/impl/zoneUtil.js");
/* harmony import */ var _impl_diff_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./impl/diff.js */ "./node_modules/luxon/src/impl/diff.js");
/* harmony import */ var _impl_regexParser_js__WEBPACK_IMPORTED_MODULE_10__ = __webpack_require__(/*! ./impl/regexParser.js */ "./node_modules/luxon/src/impl/regexParser.js");
/* harmony import */ var _impl_tokenParser_js__WEBPACK_IMPORTED_MODULE_11__ = __webpack_require__(/*! ./impl/tokenParser.js */ "./node_modules/luxon/src/impl/tokenParser.js");
/* harmony import */ var _impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__ = __webpack_require__(/*! ./impl/conversions.js */ "./node_modules/luxon/src/impl/conversions.js");
/* harmony import */ var _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__ = __webpack_require__(/*! ./impl/formats.js */ "./node_modules/luxon/src/impl/formats.js");
/* harmony import */ var _errors_js__WEBPACK_IMPORTED_MODULE_14__ = __webpack_require__(/*! ./errors.js */ "./node_modules/luxon/src/errors.js");
/* harmony import */ var _impl_invalid_js__WEBPACK_IMPORTED_MODULE_15__ = __webpack_require__(/*! ./impl/invalid.js */ "./node_modules/luxon/src/impl/invalid.js");

















const INVALID = "Invalid DateTime";
const MAX_DATE = 8.64e15;

function unsupportedZone(zone) {
  return new _impl_invalid_js__WEBPACK_IMPORTED_MODULE_15__["default"]("unsupported zone", `the zone "${zone.name}" is not supported`);
}

// we cache week data on the DT object and this intermediates the cache
/**
 * @param {DateTime} dt
 */
function possiblyCachedWeekData(dt) {
  if (dt.weekData === null) {
    dt.weekData = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.gregorianToWeek)(dt.c);
  }
  return dt.weekData;
}

/**
 * @param {DateTime} dt
 */
function possiblyCachedLocalWeekData(dt) {
  if (dt.localWeekData === null) {
    dt.localWeekData = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.gregorianToWeek)(
      dt.c,
      dt.loc.getMinDaysInFirstWeek(),
      dt.loc.getStartOfWeek()
    );
  }
  return dt.localWeekData;
}

// clone really means, "make a new object with these modifications". all "setters" really use this
// to create a new object while only changing some of the properties
function clone(inst, alts) {
  const current = {
    ts: inst.ts,
    zone: inst.zone,
    c: inst.c,
    o: inst.o,
    loc: inst.loc,
    invalid: inst.invalid,
  };
  return new DateTime({ ...current, ...alts, old: current });
}

// find the right offset a given local time. The o input is our guess, which determines which
// offset we'll pick in ambiguous cases (e.g. there are two 3 AMs b/c Fallback DST)
function fixOffset(localTS, o, tz) {
  // Our UTC time is just a guess because our offset is just a guess
  let utcGuess = localTS - o * 60 * 1000;

  // Test whether the zone matches the offset for this ts
  const o2 = tz.offset(utcGuess);

  // If so, offset didn't change and we're done
  if (o === o2) {
    return [utcGuess, o];
  }

  // If not, change the ts by the difference in the offset
  utcGuess -= (o2 - o) * 60 * 1000;

  // If that gives us the local time we want, we're done
  const o3 = tz.offset(utcGuess);
  if (o2 === o3) {
    return [utcGuess, o2];
  }

  // If it's different, we're in a hole time. The offset has changed, but the we don't adjust the time
  return [localTS - Math.min(o2, o3) * 60 * 1000, Math.max(o2, o3)];
}

// convert an epoch timestamp into a calendar object with the given offset
function tsToObj(ts, offset) {
  ts += offset * 60 * 1000;

  const d = new Date(ts);

  return {
    year: d.getUTCFullYear(),
    month: d.getUTCMonth() + 1,
    day: d.getUTCDate(),
    hour: d.getUTCHours(),
    minute: d.getUTCMinutes(),
    second: d.getUTCSeconds(),
    millisecond: d.getUTCMilliseconds(),
  };
}

// convert a calendar object to a epoch timestamp
function objToTS(obj, offset, zone) {
  return fixOffset((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.objToLocalTS)(obj), offset, zone);
}

// create a new DT instance by adding a duration, adjusting for DSTs
function adjustTime(inst, dur) {
  const oPre = inst.o,
    year = inst.c.year + Math.trunc(dur.years),
    month = inst.c.month + Math.trunc(dur.months) + Math.trunc(dur.quarters) * 3,
    c = {
      ...inst.c,
      year,
      month,
      day:
        Math.min(inst.c.day, (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.daysInMonth)(year, month)) +
        Math.trunc(dur.days) +
        Math.trunc(dur.weeks) * 7,
    },
    millisToAdd = _duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromObject({
      years: dur.years - Math.trunc(dur.years),
      quarters: dur.quarters - Math.trunc(dur.quarters),
      months: dur.months - Math.trunc(dur.months),
      weeks: dur.weeks - Math.trunc(dur.weeks),
      days: dur.days - Math.trunc(dur.days),
      hours: dur.hours,
      minutes: dur.minutes,
      seconds: dur.seconds,
      milliseconds: dur.milliseconds,
    }).as("milliseconds"),
    localTS = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.objToLocalTS)(c);

  let [ts, o] = fixOffset(localTS, oPre, inst.zone);

  if (millisToAdd !== 0) {
    ts += millisToAdd;
    // that could have changed the offset by going over a DST, but we want to keep the ts the same
    o = inst.zone.offset(ts);
  }

  return { ts, o };
}

// helper useful in turning the results of parsing into real dates
// by handling the zone options
function parseDataToDateTime(parsed, parsedZone, opts, format, text, specificOffset) {
  const { setZone, zone } = opts;
  if ((parsed && Object.keys(parsed).length !== 0) || parsedZone) {
    const interpretationZone = parsedZone || zone,
      inst = DateTime.fromObject(parsed, {
        ...opts,
        zone: interpretationZone,
        specificOffset,
      });
    return setZone ? inst : inst.setZone(zone);
  } else {
    return DateTime.invalid(
      new _impl_invalid_js__WEBPACK_IMPORTED_MODULE_15__["default"]("unparsable", `the input "${text}" can't be parsed as ${format}`)
    );
  }
}

// if you want to output a technical format (e.g. RFC 2822), this helper
// helps handle the details
function toTechFormat(dt, format, allowZ = true) {
  return dt.isValid
    ? _impl_formatter_js__WEBPACK_IMPORTED_MODULE_4__["default"].create(_impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].create("en-US"), {
        allowZ,
        forceSimple: true,
      }).formatDateTimeFromString(dt, format)
    : null;
}

function toISODate(o, extended) {
  const longFormat = o.c.year > 9999 || o.c.year < 0;
  let c = "";
  if (longFormat && o.c.year >= 0) c += "+";
  c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.year, longFormat ? 6 : 4);

  if (extended) {
    c += "-";
    c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.month);
    c += "-";
    c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.day);
  } else {
    c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.month);
    c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.day);
  }
  return c;
}

function toISOTime(
  o,
  extended,
  suppressSeconds,
  suppressMilliseconds,
  includeOffset,
  extendedZone
) {
  let c = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.hour);
  if (extended) {
    c += ":";
    c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.minute);
    if (o.c.millisecond !== 0 || o.c.second !== 0 || !suppressSeconds) {
      c += ":";
    }
  } else {
    c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.minute);
  }

  if (o.c.millisecond !== 0 || o.c.second !== 0 || !suppressSeconds) {
    c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.second);

    if (o.c.millisecond !== 0 || !suppressMilliseconds) {
      c += ".";
      c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(o.c.millisecond, 3);
    }
  }

  if (includeOffset) {
    if (o.isOffsetFixed && o.offset === 0 && !extendedZone) {
      c += "Z";
    } else if (o.o < 0) {
      c += "-";
      c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(Math.trunc(-o.o / 60));
      c += ":";
      c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(Math.trunc(-o.o % 60));
    } else {
      c += "+";
      c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(Math.trunc(o.o / 60));
      c += ":";
      c += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.padStart)(Math.trunc(o.o % 60));
    }
  }

  if (extendedZone) {
    c += "[" + o.zone.ianaName + "]";
  }
  return c;
}

// defaults for unspecified units in the supported calendars
const defaultUnitValues = {
    month: 1,
    day: 1,
    hour: 0,
    minute: 0,
    second: 0,
    millisecond: 0,
  },
  defaultWeekUnitValues = {
    weekNumber: 1,
    weekday: 1,
    hour: 0,
    minute: 0,
    second: 0,
    millisecond: 0,
  },
  defaultOrdinalUnitValues = {
    ordinal: 1,
    hour: 0,
    minute: 0,
    second: 0,
    millisecond: 0,
  };

// Units in the supported calendars, sorted by bigness
const orderedUnits = ["year", "month", "day", "hour", "minute", "second", "millisecond"],
  orderedWeekUnits = [
    "weekYear",
    "weekNumber",
    "weekday",
    "hour",
    "minute",
    "second",
    "millisecond",
  ],
  orderedOrdinalUnits = ["year", "ordinal", "hour", "minute", "second", "millisecond"];

// standardize case and plurality in units
function normalizeUnit(unit) {
  const normalized = {
    year: "year",
    years: "year",
    month: "month",
    months: "month",
    day: "day",
    days: "day",
    hour: "hour",
    hours: "hour",
    minute: "minute",
    minutes: "minute",
    quarter: "quarter",
    quarters: "quarter",
    second: "second",
    seconds: "second",
    millisecond: "millisecond",
    milliseconds: "millisecond",
    weekday: "weekday",
    weekdays: "weekday",
    weeknumber: "weekNumber",
    weeksnumber: "weekNumber",
    weeknumbers: "weekNumber",
    weekyear: "weekYear",
    weekyears: "weekYear",
    ordinal: "ordinal",
  }[unit.toLowerCase()];

  if (!normalized) throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidUnitError(unit);

  return normalized;
}

function normalizeUnitWithLocalWeeks(unit) {
  switch (unit.toLowerCase()) {
    case "localweekday":
    case "localweekdays":
      return "localWeekday";
    case "localweeknumber":
    case "localweeknumbers":
      return "localWeekNumber";
    case "localweekyear":
    case "localweekyears":
      return "localWeekYear";
    default:
      return normalizeUnit(unit);
  }
}

// cache offsets for zones based on the current timestamp when this function is
// first called. When we are handling a datetime from components like (year,
// month, day, hour) in a time zone, we need a guess about what the timezone
// offset is so that we can convert into a UTC timestamp. One way is to find the
// offset of now in the zone. The actual date may have a different offset (for
// example, if we handle a date in June while we're in December in a zone that
// observes DST), but we can check and adjust that.
//
// When handling many dates, calculating the offset for now every time is
// expensive. It's just a guess, so we can cache the offset to use even if we
// are right on a time change boundary (we'll just correct in the other
// direction). Using a timestamp from first read is a slight optimization for
// handling dates close to the current date, since those dates will usually be
// in the same offset (we could set the timestamp statically, instead). We use a
// single timestamp for all zones to make things a bit more predictable.
//
// This is safe for quickDT (used by local() and utc()) because we don't fill in
// higher-order units from tsNow (as we do in fromObject, this requires that
// offset is calculated from tsNow).
function guessOffsetForZone(zone) {
  if (!zoneOffsetGuessCache[zone]) {
    if (zoneOffsetTs === undefined) {
      zoneOffsetTs = _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].now();
    }

    zoneOffsetGuessCache[zone] = zone.offset(zoneOffsetTs);
  }
  return zoneOffsetGuessCache[zone];
}

// this is a dumbed down version of fromObject() that runs about 60% faster
// but doesn't do any validation, makes a bunch of assumptions about what units
// are present, and so on.
function quickDT(obj, opts) {
  const zone = (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_8__.normalizeZone)(opts.zone, _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone);
  if (!zone.isValid) {
    return DateTime.invalid(unsupportedZone(zone));
  }

  const loc = _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromObject(opts);

  let ts, o;

  // assume we have the higher-order units
  if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(obj.year)) {
    for (const u of orderedUnits) {
      if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(obj[u])) {
        obj[u] = defaultUnitValues[u];
      }
    }

    const invalid = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.hasInvalidGregorianData)(obj) || (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.hasInvalidTimeData)(obj);
    if (invalid) {
      return DateTime.invalid(invalid);
    }

    const offsetProvis = guessOffsetForZone(zone);
    [ts, o] = objToTS(obj, offsetProvis, zone);
  } else {
    ts = _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].now();
  }

  return new DateTime({ ts, zone, loc, o });
}

function diffRelative(start, end, opts) {
  const round = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(opts.round) ? true : opts.round,
    format = (c, unit) => {
      c = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.roundTo)(c, round || opts.calendary ? 0 : 2, true);
      const formatter = end.loc.clone(opts).relFormatter(opts);
      return formatter.format(c, unit);
    },
    differ = (unit) => {
      if (opts.calendary) {
        if (!end.hasSame(start, unit)) {
          return end.startOf(unit).diff(start.startOf(unit), unit).get(unit);
        } else return 0;
      } else {
        return end.diff(start, unit).get(unit);
      }
    };

  if (opts.unit) {
    return format(differ(opts.unit), opts.unit);
  }

  for (const unit of opts.units) {
    const count = differ(unit);
    if (Math.abs(count) >= 1) {
      return format(count, unit);
    }
  }
  return format(start > end ? -0 : 0, opts.units[opts.units.length - 1]);
}

function lastOpts(argList) {
  let opts = {},
    args;
  if (argList.length > 0 && typeof argList[argList.length - 1] === "object") {
    opts = argList[argList.length - 1];
    args = Array.from(argList).slice(0, argList.length - 1);
  } else {
    args = Array.from(argList);
  }
  return [opts, args];
}

/**
 * Timestamp to use for cached zone offset guesses (exposed for test)
 */
let zoneOffsetTs;
/**
 * Cache for zone offset guesses (exposed for test).
 *
 * This optimizes quickDT via guessOffsetForZone to avoid repeated calls of
 * zone.offset().
 */
let zoneOffsetGuessCache = {};

/**
 * A DateTime is an immutable data structure representing a specific date and time and accompanying methods. It contains class and instance methods for creating, parsing, interrogating, transforming, and formatting them.
 *
 * A DateTime comprises of:
 * * A timestamp. Each DateTime instance refers to a specific millisecond of the Unix epoch.
 * * A time zone. Each instance is considered in the context of a specific zone (by default the local system's zone).
 * * Configuration properties that effect how output strings are formatted, such as `locale`, `numberingSystem`, and `outputCalendar`.
 *
 * Here is a brief overview of the most commonly used functionality it provides:
 *
 * * **Creation**: To create a DateTime from its components, use one of its factory class methods: {@link DateTime.local}, {@link DateTime.utc}, and (most flexibly) {@link DateTime.fromObject}. To create one from a standard string format, use {@link DateTime.fromISO}, {@link DateTime.fromHTTP}, and {@link DateTime.fromRFC2822}. To create one from a custom string format, use {@link DateTime.fromFormat}. To create one from a native JS date, use {@link DateTime.fromJSDate}.
 * * **Gregorian calendar and time**: To examine the Gregorian properties of a DateTime individually (i.e as opposed to collectively through {@link DateTime#toObject}), use the {@link DateTime#year}, {@link DateTime#month},
 * {@link DateTime#day}, {@link DateTime#hour}, {@link DateTime#minute}, {@link DateTime#second}, {@link DateTime#millisecond} accessors.
 * * **Week calendar**: For ISO week calendar attributes, see the {@link DateTime#weekYear}, {@link DateTime#weekNumber}, and {@link DateTime#weekday} accessors.
 * * **Configuration** See the {@link DateTime#locale} and {@link DateTime#numberingSystem} accessors.
 * * **Transformation**: To transform the DateTime into other DateTimes, use {@link DateTime#set}, {@link DateTime#reconfigure}, {@link DateTime#setZone}, {@link DateTime#setLocale}, {@link DateTime.plus}, {@link DateTime#minus}, {@link DateTime#endOf}, {@link DateTime#startOf}, {@link DateTime#toUTC}, and {@link DateTime#toLocal}.
 * * **Output**: To convert the DateTime to other representations, use the {@link DateTime#toRelative}, {@link DateTime#toRelativeCalendar}, {@link DateTime#toJSON}, {@link DateTime#toISO}, {@link DateTime#toHTTP}, {@link DateTime#toObject}, {@link DateTime#toRFC2822}, {@link DateTime#toString}, {@link DateTime#toLocaleString}, {@link DateTime#toFormat}, {@link DateTime#toMillis} and {@link DateTime#toJSDate}.
 *
 * There's plenty others documented below. In addition, for more information on subtler topics like internationalization, time zones, alternative calendars, validity, and so on, see the external documentation.
 */
class DateTime {
  /**
   * @access private
   */
  constructor(config) {
    const zone = config.zone || _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone;

    let invalid =
      config.invalid ||
      (Number.isNaN(config.ts) ? new _impl_invalid_js__WEBPACK_IMPORTED_MODULE_15__["default"]("invalid input") : null) ||
      (!zone.isValid ? unsupportedZone(zone) : null);
    /**
     * @access private
     */
    this.ts = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(config.ts) ? _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].now() : config.ts;

    let c = null,
      o = null;
    if (!invalid) {
      const unchanged = config.old && config.old.ts === this.ts && config.old.zone.equals(zone);

      if (unchanged) {
        [c, o] = [config.old.c, config.old.o];
      } else {
        // If an offset has been passed and we have not been called from
        // clone(), we can trust it and avoid the offset calculation.
        const ot = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isNumber)(config.o) && !config.old ? config.o : zone.offset(this.ts);
        c = tsToObj(this.ts, ot);
        invalid = Number.isNaN(c.year) ? new _impl_invalid_js__WEBPACK_IMPORTED_MODULE_15__["default"]("invalid input") : null;
        c = invalid ? null : c;
        o = invalid ? null : ot;
      }
    }

    /**
     * @access private
     */
    this._zone = zone;
    /**
     * @access private
     */
    this.loc = config.loc || _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].create();
    /**
     * @access private
     */
    this.invalid = invalid;
    /**
     * @access private
     */
    this.weekData = null;
    /**
     * @access private
     */
    this.localWeekData = null;
    /**
     * @access private
     */
    this.c = c;
    /**
     * @access private
     */
    this.o = o;
    /**
     * @access private
     */
    this.isLuxonDateTime = true;
  }

  // CONSTRUCT

  /**
   * Create a DateTime for the current instant, in the system's time zone.
   *
   * Use Settings to override these default values if needed.
   * @example DateTime.now().toISO() //~> now in the ISO format
   * @return {DateTime}
   */
  static now() {
    return new DateTime({});
  }

  /**
   * Create a local DateTime
   * @param {number} [year] - The calendar year. If omitted (as in, call `local()` with no arguments), the current time will be used
   * @param {number} [month=1] - The month, 1-indexed
   * @param {number} [day=1] - The day of the month, 1-indexed
   * @param {number} [hour=0] - The hour of the day, in 24-hour time
   * @param {number} [minute=0] - The minute of the hour, meaning a number between 0 and 59
   * @param {number} [second=0] - The second of the minute, meaning a number between 0 and 59
   * @param {number} [millisecond=0] - The millisecond of the second, meaning a number between 0 and 999
   * @example DateTime.local()                                  //~> now
   * @example DateTime.local({ zone: "America/New_York" })      //~> now, in US east coast time
   * @example DateTime.local(2017)                              //~> 2017-01-01T00:00:00
   * @example DateTime.local(2017, 3)                           //~> 2017-03-01T00:00:00
   * @example DateTime.local(2017, 3, 12, { locale: "fr" })     //~> 2017-03-12T00:00:00, with a French locale
   * @example DateTime.local(2017, 3, 12, 5)                    //~> 2017-03-12T05:00:00
   * @example DateTime.local(2017, 3, 12, 5, { zone: "utc" })   //~> 2017-03-12T05:00:00, in UTC
   * @example DateTime.local(2017, 3, 12, 5, 45)                //~> 2017-03-12T05:45:00
   * @example DateTime.local(2017, 3, 12, 5, 45, 10)            //~> 2017-03-12T05:45:10
   * @example DateTime.local(2017, 3, 12, 5, 45, 10, 765)       //~> 2017-03-12T05:45:10.765
   * @return {DateTime}
   */
  static local() {
    const [opts, args] = lastOpts(arguments),
      [year, month, day, hour, minute, second, millisecond] = args;
    return quickDT({ year, month, day, hour, minute, second, millisecond }, opts);
  }

  /**
   * Create a DateTime in UTC
   * @param {number} [year] - The calendar year. If omitted (as in, call `utc()` with no arguments), the current time will be used
   * @param {number} [month=1] - The month, 1-indexed
   * @param {number} [day=1] - The day of the month
   * @param {number} [hour=0] - The hour of the day, in 24-hour time
   * @param {number} [minute=0] - The minute of the hour, meaning a number between 0 and 59
   * @param {number} [second=0] - The second of the minute, meaning a number between 0 and 59
   * @param {number} [millisecond=0] - The millisecond of the second, meaning a number between 0 and 999
   * @param {Object} options - configuration options for the DateTime
   * @param {string} [options.locale] - a locale to set on the resulting DateTime instance
   * @param {string} [options.outputCalendar] - the output calendar to set on the resulting DateTime instance
   * @param {string} [options.numberingSystem] - the numbering system to set on the resulting DateTime instance
   * @param {string} [options.weekSettings] - the week settings to set on the resulting DateTime instance
   * @example DateTime.utc()                                              //~> now
   * @example DateTime.utc(2017)                                          //~> 2017-01-01T00:00:00Z
   * @example DateTime.utc(2017, 3)                                       //~> 2017-03-01T00:00:00Z
   * @example DateTime.utc(2017, 3, 12)                                   //~> 2017-03-12T00:00:00Z
   * @example DateTime.utc(2017, 3, 12, 5)                                //~> 2017-03-12T05:00:00Z
   * @example DateTime.utc(2017, 3, 12, 5, 45)                            //~> 2017-03-12T05:45:00Z
   * @example DateTime.utc(2017, 3, 12, 5, 45, { locale: "fr" })          //~> 2017-03-12T05:45:00Z with a French locale
   * @example DateTime.utc(2017, 3, 12, 5, 45, 10)                        //~> 2017-03-12T05:45:10Z
   * @example DateTime.utc(2017, 3, 12, 5, 45, 10, 765, { locale: "fr" }) //~> 2017-03-12T05:45:10.765Z with a French locale
   * @return {DateTime}
   */
  static utc() {
    const [opts, args] = lastOpts(arguments),
      [year, month, day, hour, minute, second, millisecond] = args;

    opts.zone = _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_5__["default"].utcInstance;
    return quickDT({ year, month, day, hour, minute, second, millisecond }, opts);
  }

  /**
   * Create a DateTime from a JavaScript Date object. Uses the default zone.
   * @param {Date} date - a JavaScript Date object
   * @param {Object} options - configuration options for the DateTime
   * @param {string|Zone} [options.zone='local'] - the zone to place the DateTime into
   * @return {DateTime}
   */
  static fromJSDate(date, options = {}) {
    const ts = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isDate)(date) ? date.valueOf() : NaN;
    if (Number.isNaN(ts)) {
      return DateTime.invalid("invalid input");
    }

    const zoneToUse = (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_8__.normalizeZone)(options.zone, _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone);
    if (!zoneToUse.isValid) {
      return DateTime.invalid(unsupportedZone(zoneToUse));
    }

    return new DateTime({
      ts: ts,
      zone: zoneToUse,
      loc: _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromObject(options),
    });
  }

  /**
   * Create a DateTime from a number of milliseconds since the epoch (meaning since 1 January 1970 00:00:00 UTC). Uses the default zone.
   * @param {number} milliseconds - a number of milliseconds since 1970 UTC
   * @param {Object} options - configuration options for the DateTime
   * @param {string|Zone} [options.zone='local'] - the zone to place the DateTime into
   * @param {string} [options.locale] - a locale to set on the resulting DateTime instance
   * @param {string} options.outputCalendar - the output calendar to set on the resulting DateTime instance
   * @param {string} options.numberingSystem - the numbering system to set on the resulting DateTime instance
   * @param {string} options.weekSettings - the week settings to set on the resulting DateTime instance
   * @return {DateTime}
   */
  static fromMillis(milliseconds, options = {}) {
    if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isNumber)(milliseconds)) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError(
        `fromMillis requires a numerical input, but received a ${typeof milliseconds} with value ${milliseconds}`
      );
    } else if (milliseconds < -MAX_DATE || milliseconds > MAX_DATE) {
      // this isn't perfect because we can still end up out of range because of additional shifting, but it's a start
      return DateTime.invalid("Timestamp out of range");
    } else {
      return new DateTime({
        ts: milliseconds,
        zone: (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_8__.normalizeZone)(options.zone, _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone),
        loc: _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromObject(options),
      });
    }
  }

  /**
   * Create a DateTime from a number of seconds since the epoch (meaning since 1 January 1970 00:00:00 UTC). Uses the default zone.
   * @param {number} seconds - a number of seconds since 1970 UTC
   * @param {Object} options - configuration options for the DateTime
   * @param {string|Zone} [options.zone='local'] - the zone to place the DateTime into
   * @param {string} [options.locale] - a locale to set on the resulting DateTime instance
   * @param {string} options.outputCalendar - the output calendar to set on the resulting DateTime instance
   * @param {string} options.numberingSystem - the numbering system to set on the resulting DateTime instance
   * @param {string} options.weekSettings - the week settings to set on the resulting DateTime instance
   * @return {DateTime}
   */
  static fromSeconds(seconds, options = {}) {
    if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isNumber)(seconds)) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError("fromSeconds requires a numerical input");
    } else {
      return new DateTime({
        ts: seconds * 1000,
        zone: (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_8__.normalizeZone)(options.zone, _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone),
        loc: _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromObject(options),
      });
    }
  }

  /**
   * Create a DateTime from a JavaScript object with keys like 'year' and 'hour' with reasonable defaults.
   * @param {Object} obj - the object to create the DateTime from
   * @param {number} obj.year - a year, such as 1987
   * @param {number} obj.month - a month, 1-12
   * @param {number} obj.day - a day of the month, 1-31, depending on the month
   * @param {number} obj.ordinal - day of the year, 1-365 or 366
   * @param {number} obj.weekYear - an ISO week year
   * @param {number} obj.weekNumber - an ISO week number, between 1 and 52 or 53, depending on the year
   * @param {number} obj.weekday - an ISO weekday, 1-7, where 1 is Monday and 7 is Sunday
   * @param {number} obj.localWeekYear - a week year, according to the locale
   * @param {number} obj.localWeekNumber - a week number, between 1 and 52 or 53, depending on the year, according to the locale
   * @param {number} obj.localWeekday - a weekday, 1-7, where 1 is the first and 7 is the last day of the week, according to the locale
   * @param {number} obj.hour - hour of the day, 0-23
   * @param {number} obj.minute - minute of the hour, 0-59
   * @param {number} obj.second - second of the minute, 0-59
   * @param {number} obj.millisecond - millisecond of the second, 0-999
   * @param {Object} opts - options for creating this DateTime
   * @param {string|Zone} [opts.zone='local'] - interpret the numbers in the context of a particular zone. Can take any value taken as the first argument to setZone()
   * @param {string} [opts.locale='system\'s locale'] - a locale to set on the resulting DateTime instance
   * @param {string} opts.outputCalendar - the output calendar to set on the resulting DateTime instance
   * @param {string} opts.numberingSystem - the numbering system to set on the resulting DateTime instance
   * @param {string} opts.weekSettings - the week settings to set on the resulting DateTime instance
   * @example DateTime.fromObject({ year: 1982, month: 5, day: 25}).toISODate() //=> '1982-05-25'
   * @example DateTime.fromObject({ year: 1982 }).toISODate() //=> '1982-01-01'
   * @example DateTime.fromObject({ hour: 10, minute: 26, second: 6 }) //~> today at 10:26:06
   * @example DateTime.fromObject({ hour: 10, minute: 26, second: 6 }, { zone: 'utc' }),
   * @example DateTime.fromObject({ hour: 10, minute: 26, second: 6 }, { zone: 'local' })
   * @example DateTime.fromObject({ hour: 10, minute: 26, second: 6 }, { zone: 'America/New_York' })
   * @example DateTime.fromObject({ weekYear: 2016, weekNumber: 2, weekday: 3 }).toISODate() //=> '2016-01-13'
   * @example DateTime.fromObject({ localWeekYear: 2022, localWeekNumber: 1, localWeekday: 1 }, { locale: "en-US" }).toISODate() //=> '2021-12-26'
   * @return {DateTime}
   */
  static fromObject(obj, opts = {}) {
    obj = obj || {};
    const zoneToUse = (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_8__.normalizeZone)(opts.zone, _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone);
    if (!zoneToUse.isValid) {
      return DateTime.invalid(unsupportedZone(zoneToUse));
    }

    const loc = _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromObject(opts);
    const normalized = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.normalizeObject)(obj, normalizeUnitWithLocalWeeks);
    const { minDaysInFirstWeek, startOfWeek } = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.usesLocalWeekValues)(normalized, loc);

    const tsNow = _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].now(),
      offsetProvis = !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(opts.specificOffset)
        ? opts.specificOffset
        : zoneToUse.offset(tsNow),
      containsOrdinal = !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.ordinal),
      containsGregorYear = !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.year),
      containsGregorMD = !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.month) || !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.day),
      containsGregor = containsGregorYear || containsGregorMD,
      definiteWeekDef = normalized.weekYear || normalized.weekNumber;

    // cases:
    // just a weekday -> this week's instance of that weekday, no worries
    // (gregorian data or ordinal) + (weekYear or weekNumber) -> error
    // (gregorian month or day) + ordinal -> error
    // otherwise just use weeks or ordinals or gregorian, depending on what's specified

    if ((containsGregor || containsOrdinal) && definiteWeekDef) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.ConflictingSpecificationError(
        "Can't mix weekYear/weekNumber units with year/month/day or ordinals"
      );
    }

    if (containsGregorMD && containsOrdinal) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.ConflictingSpecificationError("Can't mix ordinal dates with month/day");
    }

    const useWeekData = definiteWeekDef || (normalized.weekday && !containsGregor);

    // configure ourselves to deal with gregorian dates or week stuff
    let units,
      defaultValues,
      objNow = tsToObj(tsNow, offsetProvis);
    if (useWeekData) {
      units = orderedWeekUnits;
      defaultValues = defaultWeekUnitValues;
      objNow = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.gregorianToWeek)(objNow, minDaysInFirstWeek, startOfWeek);
    } else if (containsOrdinal) {
      units = orderedOrdinalUnits;
      defaultValues = defaultOrdinalUnitValues;
      objNow = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.gregorianToOrdinal)(objNow);
    } else {
      units = orderedUnits;
      defaultValues = defaultUnitValues;
    }

    // set default values for missing stuff
    let foundFirst = false;
    for (const u of units) {
      const v = normalized[u];
      if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(v)) {
        foundFirst = true;
      } else if (foundFirst) {
        normalized[u] = defaultValues[u];
      } else {
        normalized[u] = objNow[u];
      }
    }

    // make sure the values we have are in range
    const higherOrderInvalid = useWeekData
        ? (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.hasInvalidWeekData)(normalized, minDaysInFirstWeek, startOfWeek)
        : containsOrdinal
        ? (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.hasInvalidOrdinalData)(normalized)
        : (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.hasInvalidGregorianData)(normalized),
      invalid = higherOrderInvalid || (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.hasInvalidTimeData)(normalized);

    if (invalid) {
      return DateTime.invalid(invalid);
    }

    // compute the actual time
    const gregorian = useWeekData
        ? (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.weekToGregorian)(normalized, minDaysInFirstWeek, startOfWeek)
        : containsOrdinal
        ? (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.ordinalToGregorian)(normalized)
        : normalized,
      [tsFinal, offsetFinal] = objToTS(gregorian, offsetProvis, zoneToUse),
      inst = new DateTime({
        ts: tsFinal,
        zone: zoneToUse,
        o: offsetFinal,
        loc,
      });

    // gregorian data + weekday serves only to validate
    if (normalized.weekday && containsGregor && obj.weekday !== inst.weekday) {
      return DateTime.invalid(
        "mismatched weekday",
        `you can't specify both a weekday of ${normalized.weekday} and a date of ${inst.toISO()}`
      );
    }

    if (!inst.isValid) {
      return DateTime.invalid(inst.invalid);
    }

    return inst;
  }

  /**
   * Create a DateTime from an ISO 8601 string
   * @param {string} text - the ISO string
   * @param {Object} opts - options to affect the creation
   * @param {string|Zone} [opts.zone='local'] - use this zone if no offset is specified in the input string itself. Will also convert the time to this zone
   * @param {boolean} [opts.setZone=false] - override the zone with a fixed-offset zone specified in the string itself, if it specifies one
   * @param {string} [opts.locale='system's locale'] - a locale to set on the resulting DateTime instance
   * @param {string} [opts.outputCalendar] - the output calendar to set on the resulting DateTime instance
   * @param {string} [opts.numberingSystem] - the numbering system to set on the resulting DateTime instance
   * @param {string} [opts.weekSettings] - the week settings to set on the resulting DateTime instance
   * @example DateTime.fromISO('2016-05-25T09:08:34.123')
   * @example DateTime.fromISO('2016-05-25T09:08:34.123+06:00')
   * @example DateTime.fromISO('2016-05-25T09:08:34.123+06:00', {setZone: true})
   * @example DateTime.fromISO('2016-05-25T09:08:34.123', {zone: 'utc'})
   * @example DateTime.fromISO('2016-W05-4')
   * @return {DateTime}
   */
  static fromISO(text, opts = {}) {
    const [vals, parsedZone] = (0,_impl_regexParser_js__WEBPACK_IMPORTED_MODULE_10__.parseISODate)(text);
    return parseDataToDateTime(vals, parsedZone, opts, "ISO 8601", text);
  }

  /**
   * Create a DateTime from an RFC 2822 string
   * @param {string} text - the RFC 2822 string
   * @param {Object} opts - options to affect the creation
   * @param {string|Zone} [opts.zone='local'] - convert the time to this zone. Since the offset is always specified in the string itself, this has no effect on the interpretation of string, merely the zone the resulting DateTime is expressed in.
   * @param {boolean} [opts.setZone=false] - override the zone with a fixed-offset zone specified in the string itself, if it specifies one
   * @param {string} [opts.locale='system's locale'] - a locale to set on the resulting DateTime instance
   * @param {string} opts.outputCalendar - the output calendar to set on the resulting DateTime instance
   * @param {string} opts.numberingSystem - the numbering system to set on the resulting DateTime instance
   * @param {string} opts.weekSettings - the week settings to set on the resulting DateTime instance
   * @example DateTime.fromRFC2822('25 Nov 2016 13:23:12 GMT')
   * @example DateTime.fromRFC2822('Fri, 25 Nov 2016 13:23:12 +0600')
   * @example DateTime.fromRFC2822('25 Nov 2016 13:23 Z')
   * @return {DateTime}
   */
  static fromRFC2822(text, opts = {}) {
    const [vals, parsedZone] = (0,_impl_regexParser_js__WEBPACK_IMPORTED_MODULE_10__.parseRFC2822Date)(text);
    return parseDataToDateTime(vals, parsedZone, opts, "RFC 2822", text);
  }

  /**
   * Create a DateTime from an HTTP header date
   * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.3.1
   * @param {string} text - the HTTP header date
   * @param {Object} opts - options to affect the creation
   * @param {string|Zone} [opts.zone='local'] - convert the time to this zone. Since HTTP dates are always in UTC, this has no effect on the interpretation of string, merely the zone the resulting DateTime is expressed in.
   * @param {boolean} [opts.setZone=false] - override the zone with the fixed-offset zone specified in the string. For HTTP dates, this is always UTC, so this option is equivalent to setting the `zone` option to 'utc', but this option is included for consistency with similar methods.
   * @param {string} [opts.locale='system's locale'] - a locale to set on the resulting DateTime instance
   * @param {string} opts.outputCalendar - the output calendar to set on the resulting DateTime instance
   * @param {string} opts.numberingSystem - the numbering system to set on the resulting DateTime instance
   * @param {string} opts.weekSettings - the week settings to set on the resulting DateTime instance
   * @example DateTime.fromHTTP('Sun, 06 Nov 1994 08:49:37 GMT')
   * @example DateTime.fromHTTP('Sunday, 06-Nov-94 08:49:37 GMT')
   * @example DateTime.fromHTTP('Sun Nov  6 08:49:37 1994')
   * @return {DateTime}
   */
  static fromHTTP(text, opts = {}) {
    const [vals, parsedZone] = (0,_impl_regexParser_js__WEBPACK_IMPORTED_MODULE_10__.parseHTTPDate)(text);
    return parseDataToDateTime(vals, parsedZone, opts, "HTTP", opts);
  }

  /**
   * Create a DateTime from an input string and format string.
   * Defaults to en-US if no locale has been specified, regardless of the system's locale. For a table of tokens and their interpretations, see [here](https://moment.github.io/luxon/#/parsing?id=table-of-tokens).
   * @param {string} text - the string to parse
   * @param {string} fmt - the format the string is expected to be in (see the link below for the formats)
   * @param {Object} opts - options to affect the creation
   * @param {string|Zone} [opts.zone='local'] - use this zone if no offset is specified in the input string itself. Will also convert the DateTime to this zone
   * @param {boolean} [opts.setZone=false] - override the zone with a zone specified in the string itself, if it specifies one
   * @param {string} [opts.locale='en-US'] - a locale string to use when parsing. Will also set the DateTime to this locale
   * @param {string} opts.numberingSystem - the numbering system to use when parsing. Will also set the resulting DateTime to this numbering system
   * @param {string} opts.weekSettings - the week settings to set on the resulting DateTime instance
   * @param {string} opts.outputCalendar - the output calendar to set on the resulting DateTime instance
   * @return {DateTime}
   */
  static fromFormat(text, fmt, opts = {}) {
    if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(text) || (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(fmt)) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError("fromFormat requires an input string and a format");
    }

    const { locale = null, numberingSystem = null } = opts,
      localeToUse = _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromOpts({
        locale,
        numberingSystem,
        defaultToEN: true,
      }),
      [vals, parsedZone, specificOffset, invalid] = (0,_impl_tokenParser_js__WEBPACK_IMPORTED_MODULE_11__.parseFromTokens)(localeToUse, text, fmt);
    if (invalid) {
      return DateTime.invalid(invalid);
    } else {
      return parseDataToDateTime(vals, parsedZone, opts, `format ${fmt}`, text, specificOffset);
    }
  }

  /**
   * @deprecated use fromFormat instead
   */
  static fromString(text, fmt, opts = {}) {
    return DateTime.fromFormat(text, fmt, opts);
  }

  /**
   * Create a DateTime from a SQL date, time, or datetime
   * Defaults to en-US if no locale has been specified, regardless of the system's locale
   * @param {string} text - the string to parse
   * @param {Object} opts - options to affect the creation
   * @param {string|Zone} [opts.zone='local'] - use this zone if no offset is specified in the input string itself. Will also convert the DateTime to this zone
   * @param {boolean} [opts.setZone=false] - override the zone with a zone specified in the string itself, if it specifies one
   * @param {string} [opts.locale='en-US'] - a locale string to use when parsing. Will also set the DateTime to this locale
   * @param {string} opts.numberingSystem - the numbering system to use when parsing. Will also set the resulting DateTime to this numbering system
   * @param {string} opts.weekSettings - the week settings to set on the resulting DateTime instance
   * @param {string} opts.outputCalendar - the output calendar to set on the resulting DateTime instance
   * @example DateTime.fromSQL('2017-05-15')
   * @example DateTime.fromSQL('2017-05-15 09:12:34')
   * @example DateTime.fromSQL('2017-05-15 09:12:34.342')
   * @example DateTime.fromSQL('2017-05-15 09:12:34.342+06:00')
   * @example DateTime.fromSQL('2017-05-15 09:12:34.342 America/Los_Angeles')
   * @example DateTime.fromSQL('2017-05-15 09:12:34.342 America/Los_Angeles', { setZone: true })
   * @example DateTime.fromSQL('2017-05-15 09:12:34.342', { zone: 'America/Los_Angeles' })
   * @example DateTime.fromSQL('09:12:34.342')
   * @return {DateTime}
   */
  static fromSQL(text, opts = {}) {
    const [vals, parsedZone] = (0,_impl_regexParser_js__WEBPACK_IMPORTED_MODULE_10__.parseSQL)(text);
    return parseDataToDateTime(vals, parsedZone, opts, "SQL", text);
  }

  /**
   * Create an invalid DateTime.
   * @param {string} reason - simple string of why this DateTime is invalid. Should not contain parameters or anything else data-dependent.
   * @param {string} [explanation=null] - longer explanation, may include parameters and other useful debugging information
   * @return {DateTime}
   */
  static invalid(reason, explanation = null) {
    if (!reason) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError("need to specify a reason the DateTime is invalid");
    }

    const invalid = reason instanceof _impl_invalid_js__WEBPACK_IMPORTED_MODULE_15__["default"] ? reason : new _impl_invalid_js__WEBPACK_IMPORTED_MODULE_15__["default"](reason, explanation);

    if (_settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].throwOnInvalid) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidDateTimeError(invalid);
    } else {
      return new DateTime({ invalid });
    }
  }

  /**
   * Check if an object is an instance of DateTime. Works across context boundaries
   * @param {object} o
   * @return {boolean}
   */
  static isDateTime(o) {
    return (o && o.isLuxonDateTime) || false;
  }

  /**
   * Produce the format string for a set of options
   * @param formatOpts
   * @param localeOpts
   * @returns {string}
   */
  static parseFormatForOpts(formatOpts, localeOpts = {}) {
    const tokenList = (0,_impl_tokenParser_js__WEBPACK_IMPORTED_MODULE_11__.formatOptsToTokens)(formatOpts, _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromObject(localeOpts));
    return !tokenList ? null : tokenList.map((t) => (t ? t.val : null)).join("");
  }

  /**
   * Produce the the fully expanded format token for the locale
   * Does NOT quote characters, so quoted tokens will not round trip correctly
   * @param fmt
   * @param localeOpts
   * @returns {string}
   */
  static expandFormat(fmt, localeOpts = {}) {
    const expanded = (0,_impl_tokenParser_js__WEBPACK_IMPORTED_MODULE_11__.expandMacroTokens)(_impl_formatter_js__WEBPACK_IMPORTED_MODULE_4__["default"].parseFormat(fmt), _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromObject(localeOpts));
    return expanded.map((t) => t.val).join("");
  }

  static resetCache() {
    zoneOffsetTs = undefined;
    zoneOffsetGuessCache = {};
  }

  // INFO

  /**
   * Get the value of unit.
   * @param {string} unit - a unit such as 'minute' or 'day'
   * @example DateTime.local(2017, 7, 4).get('month'); //=> 7
   * @example DateTime.local(2017, 7, 4).get('day'); //=> 4
   * @return {number}
   */
  get(unit) {
    return this[unit];
  }

  /**
   * Returns whether the DateTime is valid. Invalid DateTimes occur when:
   * * The DateTime was created from invalid calendar information, such as the 13th month or February 30
   * * The DateTime was created by an operation on another invalid date
   * @type {boolean}
   */
  get isValid() {
    return this.invalid === null;
  }

  /**
   * Returns an error code if this DateTime is invalid, or null if the DateTime is valid
   * @type {string}
   */
  get invalidReason() {
    return this.invalid ? this.invalid.reason : null;
  }

  /**
   * Returns an explanation of why this DateTime became invalid, or null if the DateTime is valid
   * @type {string}
   */
  get invalidExplanation() {
    return this.invalid ? this.invalid.explanation : null;
  }

  /**
   * Get the locale of a DateTime, such 'en-GB'. The locale is used when formatting the DateTime
   *
   * @type {string}
   */
  get locale() {
    return this.isValid ? this.loc.locale : null;
  }

  /**
   * Get the numbering system of a DateTime, such 'beng'. The numbering system is used when formatting the DateTime
   *
   * @type {string}
   */
  get numberingSystem() {
    return this.isValid ? this.loc.numberingSystem : null;
  }

  /**
   * Get the output calendar of a DateTime, such 'islamic'. The output calendar is used when formatting the DateTime
   *
   * @type {string}
   */
  get outputCalendar() {
    return this.isValid ? this.loc.outputCalendar : null;
  }

  /**
   * Get the time zone associated with this DateTime.
   * @type {Zone}
   */
  get zone() {
    return this._zone;
  }

  /**
   * Get the name of the time zone.
   * @type {string}
   */
  get zoneName() {
    return this.isValid ? this.zone.name : null;
  }

  /**
   * Get the year
   * @example DateTime.local(2017, 5, 25).year //=> 2017
   * @type {number}
   */
  get year() {
    return this.isValid ? this.c.year : NaN;
  }

  /**
   * Get the quarter
   * @example DateTime.local(2017, 5, 25).quarter //=> 2
   * @type {number}
   */
  get quarter() {
    return this.isValid ? Math.ceil(this.c.month / 3) : NaN;
  }

  /**
   * Get the month (1-12).
   * @example DateTime.local(2017, 5, 25).month //=> 5
   * @type {number}
   */
  get month() {
    return this.isValid ? this.c.month : NaN;
  }

  /**
   * Get the day of the month (1-30ish).
   * @example DateTime.local(2017, 5, 25).day //=> 25
   * @type {number}
   */
  get day() {
    return this.isValid ? this.c.day : NaN;
  }

  /**
   * Get the hour of the day (0-23).
   * @example DateTime.local(2017, 5, 25, 9).hour //=> 9
   * @type {number}
   */
  get hour() {
    return this.isValid ? this.c.hour : NaN;
  }

  /**
   * Get the minute of the hour (0-59).
   * @example DateTime.local(2017, 5, 25, 9, 30).minute //=> 30
   * @type {number}
   */
  get minute() {
    return this.isValid ? this.c.minute : NaN;
  }

  /**
   * Get the second of the minute (0-59).
   * @example DateTime.local(2017, 5, 25, 9, 30, 52).second //=> 52
   * @type {number}
   */
  get second() {
    return this.isValid ? this.c.second : NaN;
  }

  /**
   * Get the millisecond of the second (0-999).
   * @example DateTime.local(2017, 5, 25, 9, 30, 52, 654).millisecond //=> 654
   * @type {number}
   */
  get millisecond() {
    return this.isValid ? this.c.millisecond : NaN;
  }

  /**
   * Get the week year
   * @see https://en.wikipedia.org/wiki/ISO_week_date
   * @example DateTime.local(2014, 12, 31).weekYear //=> 2015
   * @type {number}
   */
  get weekYear() {
    return this.isValid ? possiblyCachedWeekData(this).weekYear : NaN;
  }

  /**
   * Get the week number of the week year (1-52ish).
   * @see https://en.wikipedia.org/wiki/ISO_week_date
   * @example DateTime.local(2017, 5, 25).weekNumber //=> 21
   * @type {number}
   */
  get weekNumber() {
    return this.isValid ? possiblyCachedWeekData(this).weekNumber : NaN;
  }

  /**
   * Get the day of the week.
   * 1 is Monday and 7 is Sunday
   * @see https://en.wikipedia.org/wiki/ISO_week_date
   * @example DateTime.local(2014, 11, 31).weekday //=> 4
   * @type {number}
   */
  get weekday() {
    return this.isValid ? possiblyCachedWeekData(this).weekday : NaN;
  }

  /**
   * Returns true if this date is on a weekend according to the locale, false otherwise
   * @returns {boolean}
   */
  get isWeekend() {
    return this.isValid && this.loc.getWeekendDays().includes(this.weekday);
  }

  /**
   * Get the day of the week according to the locale.
   * 1 is the first day of the week and 7 is the last day of the week.
   * If the locale assigns Sunday as the first day of the week, then a date which is a Sunday will return 1,
   * @returns {number}
   */
  get localWeekday() {
    return this.isValid ? possiblyCachedLocalWeekData(this).weekday : NaN;
  }

  /**
   * Get the week number of the week year according to the locale. Different locales assign week numbers differently,
   * because the week can start on different days of the week (see localWeekday) and because a different number of days
   * is required for a week to count as the first week of a year.
   * @returns {number}
   */
  get localWeekNumber() {
    return this.isValid ? possiblyCachedLocalWeekData(this).weekNumber : NaN;
  }

  /**
   * Get the week year according to the locale. Different locales assign week numbers (and therefor week years)
   * differently, see localWeekNumber.
   * @returns {number}
   */
  get localWeekYear() {
    return this.isValid ? possiblyCachedLocalWeekData(this).weekYear : NaN;
  }

  /**
   * Get the ordinal (meaning the day of the year)
   * @example DateTime.local(2017, 5, 25).ordinal //=> 145
   * @type {number|DateTime}
   */
  get ordinal() {
    return this.isValid ? (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.gregorianToOrdinal)(this.c).ordinal : NaN;
  }

  /**
   * Get the human readable short month name, such as 'Oct'.
   * Defaults to the system's locale if no locale has been specified
   * @example DateTime.local(2017, 10, 30).monthShort //=> Oct
   * @type {string}
   */
  get monthShort() {
    return this.isValid ? _info_js__WEBPACK_IMPORTED_MODULE_3__["default"].months("short", { locObj: this.loc })[this.month - 1] : null;
  }

  /**
   * Get the human readable long month name, such as 'October'.
   * Defaults to the system's locale if no locale has been specified
   * @example DateTime.local(2017, 10, 30).monthLong //=> October
   * @type {string}
   */
  get monthLong() {
    return this.isValid ? _info_js__WEBPACK_IMPORTED_MODULE_3__["default"].months("long", { locObj: this.loc })[this.month - 1] : null;
  }

  /**
   * Get the human readable short weekday, such as 'Mon'.
   * Defaults to the system's locale if no locale has been specified
   * @example DateTime.local(2017, 10, 30).weekdayShort //=> Mon
   * @type {string}
   */
  get weekdayShort() {
    return this.isValid ? _info_js__WEBPACK_IMPORTED_MODULE_3__["default"].weekdays("short", { locObj: this.loc })[this.weekday - 1] : null;
  }

  /**
   * Get the human readable long weekday, such as 'Monday'.
   * Defaults to the system's locale if no locale has been specified
   * @example DateTime.local(2017, 10, 30).weekdayLong //=> Monday
   * @type {string}
   */
  get weekdayLong() {
    return this.isValid ? _info_js__WEBPACK_IMPORTED_MODULE_3__["default"].weekdays("long", { locObj: this.loc })[this.weekday - 1] : null;
  }

  /**
   * Get the UTC offset of this DateTime in minutes
   * @example DateTime.now().offset //=> -240
   * @example DateTime.utc().offset //=> 0
   * @type {number}
   */
  get offset() {
    return this.isValid ? +this.o : NaN;
  }

  /**
   * Get the short human name for the zone's current offset, for example "EST" or "EDT".
   * Defaults to the system's locale if no locale has been specified
   * @type {string}
   */
  get offsetNameShort() {
    if (this.isValid) {
      return this.zone.offsetName(this.ts, {
        format: "short",
        locale: this.locale,
      });
    } else {
      return null;
    }
  }

  /**
   * Get the long human name for the zone's current offset, for example "Eastern Standard Time" or "Eastern Daylight Time".
   * Defaults to the system's locale if no locale has been specified
   * @type {string}
   */
  get offsetNameLong() {
    if (this.isValid) {
      return this.zone.offsetName(this.ts, {
        format: "long",
        locale: this.locale,
      });
    } else {
      return null;
    }
  }

  /**
   * Get whether this zone's offset ever changes, as in a DST.
   * @type {boolean}
   */
  get isOffsetFixed() {
    return this.isValid ? this.zone.isUniversal : null;
  }

  /**
   * Get whether the DateTime is in a DST.
   * @type {boolean}
   */
  get isInDST() {
    if (this.isOffsetFixed) {
      return false;
    } else {
      return (
        this.offset > this.set({ month: 1, day: 1 }).offset ||
        this.offset > this.set({ month: 5 }).offset
      );
    }
  }

  /**
   * Get those DateTimes which have the same local time as this DateTime, but a different offset from UTC
   * in this DateTime's zone. During DST changes local time can be ambiguous, for example
   * `2023-10-29T02:30:00` in `Europe/Berlin` can have offset `+01:00` or `+02:00`.
   * This method will return both possible DateTimes if this DateTime's local time is ambiguous.
   * @returns {DateTime[]}
   */
  getPossibleOffsets() {
    if (!this.isValid || this.isOffsetFixed) {
      return [this];
    }
    const dayMs = 86400000;
    const minuteMs = 60000;
    const localTS = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.objToLocalTS)(this.c);
    const oEarlier = this.zone.offset(localTS - dayMs);
    const oLater = this.zone.offset(localTS + dayMs);

    const o1 = this.zone.offset(localTS - oEarlier * minuteMs);
    const o2 = this.zone.offset(localTS - oLater * minuteMs);
    if (o1 === o2) {
      return [this];
    }
    const ts1 = localTS - o1 * minuteMs;
    const ts2 = localTS - o2 * minuteMs;
    const c1 = tsToObj(ts1, o1);
    const c2 = tsToObj(ts2, o2);
    if (
      c1.hour === c2.hour &&
      c1.minute === c2.minute &&
      c1.second === c2.second &&
      c1.millisecond === c2.millisecond
    ) {
      return [clone(this, { ts: ts1 }), clone(this, { ts: ts2 })];
    }
    return [this];
  }

  /**
   * Returns true if this DateTime is in a leap year, false otherwise
   * @example DateTime.local(2016).isInLeapYear //=> true
   * @example DateTime.local(2013).isInLeapYear //=> false
   * @type {boolean}
   */
  get isInLeapYear() {
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isLeapYear)(this.year);
  }

  /**
   * Returns the number of days in this DateTime's month
   * @example DateTime.local(2016, 2).daysInMonth //=> 29
   * @example DateTime.local(2016, 3).daysInMonth //=> 31
   * @type {number}
   */
  get daysInMonth() {
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.daysInMonth)(this.year, this.month);
  }

  /**
   * Returns the number of days in this DateTime's year
   * @example DateTime.local(2016).daysInYear //=> 366
   * @example DateTime.local(2013).daysInYear //=> 365
   * @type {number}
   */
  get daysInYear() {
    return this.isValid ? (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.daysInYear)(this.year) : NaN;
  }

  /**
   * Returns the number of weeks in this DateTime's year
   * @see https://en.wikipedia.org/wiki/ISO_week_date
   * @example DateTime.local(2004).weeksInWeekYear //=> 53
   * @example DateTime.local(2013).weeksInWeekYear //=> 52
   * @type {number}
   */
  get weeksInWeekYear() {
    return this.isValid ? (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.weeksInWeekYear)(this.weekYear) : NaN;
  }

  /**
   * Returns the number of weeks in this DateTime's local week year
   * @example DateTime.local(2020, 6, {locale: 'en-US'}).weeksInLocalWeekYear //=> 52
   * @example DateTime.local(2020, 6, {locale: 'de-DE'}).weeksInLocalWeekYear //=> 53
   * @type {number}
   */
  get weeksInLocalWeekYear() {
    return this.isValid
      ? (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.weeksInWeekYear)(
          this.localWeekYear,
          this.loc.getMinDaysInFirstWeek(),
          this.loc.getStartOfWeek()
        )
      : NaN;
  }

  /**
   * Returns the resolved Intl options for this DateTime.
   * This is useful in understanding the behavior of formatting methods
   * @param {Object} opts - the same options as toLocaleString
   * @return {Object}
   */
  resolvedLocaleOptions(opts = {}) {
    const { locale, numberingSystem, calendar } = _impl_formatter_js__WEBPACK_IMPORTED_MODULE_4__["default"].create(
      this.loc.clone(opts),
      opts
    ).resolvedOptions(this);
    return { locale, numberingSystem, outputCalendar: calendar };
  }

  // TRANSFORM

  /**
   * "Set" the DateTime's zone to UTC. Returns a newly-constructed DateTime.
   *
   * Equivalent to {@link DateTime#setZone}('utc')
   * @param {number} [offset=0] - optionally, an offset from UTC in minutes
   * @param {Object} [opts={}] - options to pass to `setZone()`
   * @return {DateTime}
   */
  toUTC(offset = 0, opts = {}) {
    return this.setZone(_zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_5__["default"].instance(offset), opts);
  }

  /**
   * "Set" the DateTime's zone to the host's local zone. Returns a newly-constructed DateTime.
   *
   * Equivalent to `setZone('local')`
   * @return {DateTime}
   */
  toLocal() {
    return this.setZone(_settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone);
  }

  /**
   * "Set" the DateTime's zone to specified zone. Returns a newly-constructed DateTime.
   *
   * By default, the setter keeps the underlying time the same (as in, the same timestamp), but the new instance will report different local times and consider DSTs when making computations, as with {@link DateTime#plus}. You may wish to use {@link DateTime#toLocal} and {@link DateTime#toUTC} which provide simple convenience wrappers for commonly used zones.
   * @param {string|Zone} [zone='local'] - a zone identifier. As a string, that can be any IANA zone supported by the host environment, or a fixed-offset name of the form 'UTC+3', or the strings 'local' or 'utc'. You may also supply an instance of a {@link DateTime#Zone} class.
   * @param {Object} opts - options
   * @param {boolean} [opts.keepLocalTime=false] - If true, adjust the underlying time so that the local time stays the same, but in the target zone. You should rarely need this.
   * @return {DateTime}
   */
  setZone(zone, { keepLocalTime = false, keepCalendarTime = false } = {}) {
    zone = (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_8__.normalizeZone)(zone, _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultZone);
    if (zone.equals(this.zone)) {
      return this;
    } else if (!zone.isValid) {
      return DateTime.invalid(unsupportedZone(zone));
    } else {
      let newTS = this.ts;
      if (keepLocalTime || keepCalendarTime) {
        const offsetGuess = zone.offset(this.ts);
        const asObj = this.toObject();
        [newTS] = objToTS(asObj, offsetGuess, zone);
      }
      return clone(this, { ts: newTS, zone });
    }
  }

  /**
   * "Set" the locale, numberingSystem, or outputCalendar. Returns a newly-constructed DateTime.
   * @param {Object} properties - the properties to set
   * @example DateTime.local(2017, 5, 25).reconfigure({ locale: 'en-GB' })
   * @return {DateTime}
   */
  reconfigure({ locale, numberingSystem, outputCalendar } = {}) {
    const loc = this.loc.clone({ locale, numberingSystem, outputCalendar });
    return clone(this, { loc });
  }

  /**
   * "Set" the locale. Returns a newly-constructed DateTime.
   * Just a convenient alias for reconfigure({ locale })
   * @example DateTime.local(2017, 5, 25).setLocale('en-GB')
   * @return {DateTime}
   */
  setLocale(locale) {
    return this.reconfigure({ locale });
  }

  /**
   * "Set" the values of specified units. Returns a newly-constructed DateTime.
   * You can only set units with this method; for "setting" metadata, see {@link DateTime#reconfigure} and {@link DateTime#setZone}.
   *
   * This method also supports setting locale-based week units, i.e. `localWeekday`, `localWeekNumber` and `localWeekYear`.
   * They cannot be mixed with ISO-week units like `weekday`.
   * @param {Object} values - a mapping of units to numbers
   * @example dt.set({ year: 2017 })
   * @example dt.set({ hour: 8, minute: 30 })
   * @example dt.set({ weekday: 5 })
   * @example dt.set({ year: 2005, ordinal: 234 })
   * @return {DateTime}
   */
  set(values) {
    if (!this.isValid) return this;

    const normalized = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.normalizeObject)(values, normalizeUnitWithLocalWeeks);
    const { minDaysInFirstWeek, startOfWeek } = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.usesLocalWeekValues)(normalized, this.loc);

    const settingWeekStuff =
        !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.weekYear) ||
        !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.weekNumber) ||
        !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.weekday),
      containsOrdinal = !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.ordinal),
      containsGregorYear = !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.year),
      containsGregorMD = !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.month) || !(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.day),
      containsGregor = containsGregorYear || containsGregorMD,
      definiteWeekDef = normalized.weekYear || normalized.weekNumber;

    if ((containsGregor || containsOrdinal) && definiteWeekDef) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.ConflictingSpecificationError(
        "Can't mix weekYear/weekNumber units with year/month/day or ordinals"
      );
    }

    if (containsGregorMD && containsOrdinal) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.ConflictingSpecificationError("Can't mix ordinal dates with month/day");
    }

    let mixed;
    if (settingWeekStuff) {
      mixed = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.weekToGregorian)(
        { ...(0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.gregorianToWeek)(this.c, minDaysInFirstWeek, startOfWeek), ...normalized },
        minDaysInFirstWeek,
        startOfWeek
      );
    } else if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.ordinal)) {
      mixed = (0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.ordinalToGregorian)({ ...(0,_impl_conversions_js__WEBPACK_IMPORTED_MODULE_12__.gregorianToOrdinal)(this.c), ...normalized });
    } else {
      mixed = { ...this.toObject(), ...normalized };

      // if we didn't set the day but we ended up on an overflow date,
      // use the last day of the right month
      if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(normalized.day)) {
        mixed.day = Math.min((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.daysInMonth)(mixed.year, mixed.month), mixed.day);
      }
    }

    const [ts, o] = objToTS(mixed, this.o, this.zone);
    return clone(this, { ts, o });
  }

  /**
   * Add a period of time to this DateTime and return the resulting DateTime
   *
   * Adding hours, minutes, seconds, or milliseconds increases the timestamp by the right number of milliseconds. Adding days, months, or years shifts the calendar, accounting for DSTs and leap years along the way. Thus, `dt.plus({ hours: 24 })` may result in a different time than `dt.plus({ days: 1 })` if there's a DST shift in between.
   * @param {Duration|Object|number} duration - The amount to add. Either a Luxon Duration, a number of milliseconds, the object argument to Duration.fromObject()
   * @example DateTime.now().plus(123) //~> in 123 milliseconds
   * @example DateTime.now().plus({ minutes: 15 }) //~> in 15 minutes
   * @example DateTime.now().plus({ days: 1 }) //~> this time tomorrow
   * @example DateTime.now().plus({ days: -1 }) //~> this time yesterday
   * @example DateTime.now().plus({ hours: 3, minutes: 13 }) //~> in 3 hr, 13 min
   * @example DateTime.now().plus(Duration.fromObject({ hours: 3, minutes: 13 })) //~> in 3 hr, 13 min
   * @return {DateTime}
   */
  plus(duration) {
    if (!this.isValid) return this;
    const dur = _duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromDurationLike(duration);
    return clone(this, adjustTime(this, dur));
  }

  /**
   * Subtract a period of time to this DateTime and return the resulting DateTime
   * See {@link DateTime#plus}
   * @param {Duration|Object|number} duration - The amount to subtract. Either a Luxon Duration, a number of milliseconds, the object argument to Duration.fromObject()
   @return {DateTime}
   */
  minus(duration) {
    if (!this.isValid) return this;
    const dur = _duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromDurationLike(duration).negate();
    return clone(this, adjustTime(this, dur));
  }

  /**
   * "Set" this DateTime to the beginning of a unit of time.
   * @param {string} unit - The unit to go to the beginning of. Can be 'year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', or 'millisecond'.
   * @param {Object} opts - options
   * @param {boolean} [opts.useLocaleWeeks=false] - If true, use weeks based on the locale, i.e. use the locale-dependent start of the week
   * @example DateTime.local(2014, 3, 3).startOf('month').toISODate(); //=> '2014-03-01'
   * @example DateTime.local(2014, 3, 3).startOf('year').toISODate(); //=> '2014-01-01'
   * @example DateTime.local(2014, 3, 3).startOf('week').toISODate(); //=> '2014-03-03', weeks always start on Mondays
   * @example DateTime.local(2014, 3, 3, 5, 30).startOf('day').toISOTime(); //=> '00:00.000-05:00'
   * @example DateTime.local(2014, 3, 3, 5, 30).startOf('hour').toISOTime(); //=> '05:00:00.000-05:00'
   * @return {DateTime}
   */
  startOf(unit, { useLocaleWeeks = false } = {}) {
    if (!this.isValid) return this;

    const o = {},
      normalizedUnit = _duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].normalizeUnit(unit);
    switch (normalizedUnit) {
      case "years":
        o.month = 1;
      // falls through
      case "quarters":
      case "months":
        o.day = 1;
      // falls through
      case "weeks":
      case "days":
        o.hour = 0;
      // falls through
      case "hours":
        o.minute = 0;
      // falls through
      case "minutes":
        o.second = 0;
      // falls through
      case "seconds":
        o.millisecond = 0;
        break;
      case "milliseconds":
        break;
      // no default, invalid units throw in normalizeUnit()
    }

    if (normalizedUnit === "weeks") {
      if (useLocaleWeeks) {
        const startOfWeek = this.loc.getStartOfWeek();
        const { weekday } = this;
        if (weekday < startOfWeek) {
          o.weekNumber = this.weekNumber - 1;
        }
        o.weekday = startOfWeek;
      } else {
        o.weekday = 1;
      }
    }

    if (normalizedUnit === "quarters") {
      const q = Math.ceil(this.month / 3);
      o.month = (q - 1) * 3 + 1;
    }

    return this.set(o);
  }

  /**
   * "Set" this DateTime to the end (meaning the last millisecond) of a unit of time
   * @param {string} unit - The unit to go to the end of. Can be 'year', 'quarter', 'month', 'week', 'day', 'hour', 'minute', 'second', or 'millisecond'.
   * @param {Object} opts - options
   * @param {boolean} [opts.useLocaleWeeks=false] - If true, use weeks based on the locale, i.e. use the locale-dependent start of the week
   * @example DateTime.local(2014, 3, 3).endOf('month').toISO(); //=> '2014-03-31T23:59:59.999-05:00'
   * @example DateTime.local(2014, 3, 3).endOf('year').toISO(); //=> '2014-12-31T23:59:59.999-05:00'
   * @example DateTime.local(2014, 3, 3).endOf('week').toISO(); // => '2014-03-09T23:59:59.999-05:00', weeks start on Mondays
   * @example DateTime.local(2014, 3, 3, 5, 30).endOf('day').toISO(); //=> '2014-03-03T23:59:59.999-05:00'
   * @example DateTime.local(2014, 3, 3, 5, 30).endOf('hour').toISO(); //=> '2014-03-03T05:59:59.999-05:00'
   * @return {DateTime}
   */
  endOf(unit, opts) {
    return this.isValid
      ? this.plus({ [unit]: 1 })
          .startOf(unit, opts)
          .minus(1)
      : this;
  }

  // OUTPUT

  /**
   * Returns a string representation of this DateTime formatted according to the specified format string.
   * **You may not want this.** See {@link DateTime#toLocaleString} for a more flexible formatting tool. For a table of tokens and their interpretations, see [here](https://moment.github.io/luxon/#/formatting?id=table-of-tokens).
   * Defaults to en-US if no locale has been specified, regardless of the system's locale.
   * @param {string} fmt - the format string
   * @param {Object} opts - opts to override the configuration options on this DateTime
   * @example DateTime.now().toFormat('yyyy LLL dd') //=> '2017 Apr 22'
   * @example DateTime.now().setLocale('fr').toFormat('yyyy LLL dd') //=> '2017 avr. 22'
   * @example DateTime.now().toFormat('yyyy LLL dd', { locale: "fr" }) //=> '2017 avr. 22'
   * @example DateTime.now().toFormat("HH 'hours and' mm 'minutes'") //=> '20 hours and 55 minutes'
   * @return {string}
   */
  toFormat(fmt, opts = {}) {
    return this.isValid
      ? _impl_formatter_js__WEBPACK_IMPORTED_MODULE_4__["default"].create(this.loc.redefaultToEN(opts)).formatDateTimeFromString(this, fmt)
      : INVALID;
  }

  /**
   * Returns a localized string representing this date. Accepts the same options as the Intl.DateTimeFormat constructor and any presets defined by Luxon, such as `DateTime.DATE_FULL` or `DateTime.TIME_SIMPLE`.
   * The exact behavior of this method is browser-specific, but in general it will return an appropriate representation
   * of the DateTime in the assigned locale.
   * Defaults to the system's locale if no locale has been specified
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat
   * @param formatOpts {Object} - Intl.DateTimeFormat constructor options and configuration options
   * @param {Object} opts - opts to override the configuration options on this DateTime
   * @example DateTime.now().toLocaleString(); //=> 4/20/2017
   * @example DateTime.now().setLocale('en-gb').toLocaleString(); //=> '20/04/2017'
   * @example DateTime.now().toLocaleString(DateTime.DATE_FULL); //=> 'April 20, 2017'
   * @example DateTime.now().toLocaleString(DateTime.DATE_FULL, { locale: 'fr' }); //=> '28 août 2022'
   * @example DateTime.now().toLocaleString(DateTime.TIME_SIMPLE); //=> '11:32 AM'
   * @example DateTime.now().toLocaleString(DateTime.DATETIME_SHORT); //=> '4/20/2017, 11:32 AM'
   * @example DateTime.now().toLocaleString({ weekday: 'long', month: 'long', day: '2-digit' }); //=> 'Thursday, April 20'
   * @example DateTime.now().toLocaleString({ weekday: 'short', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }); //=> 'Thu, Apr 20, 11:27 AM'
   * @example DateTime.now().toLocaleString({ hour: '2-digit', minute: '2-digit', hourCycle: 'h23' }); //=> '11:32'
   * @return {string}
   */
  toLocaleString(formatOpts = _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATE_SHORT, opts = {}) {
    return this.isValid
      ? _impl_formatter_js__WEBPACK_IMPORTED_MODULE_4__["default"].create(this.loc.clone(opts), formatOpts).formatDateTime(this)
      : INVALID;
  }

  /**
   * Returns an array of format "parts", meaning individual tokens along with metadata. This is allows callers to post-process individual sections of the formatted output.
   * Defaults to the system's locale if no locale has been specified
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat/formatToParts
   * @param opts {Object} - Intl.DateTimeFormat constructor options, same as `toLocaleString`.
   * @example DateTime.now().toLocaleParts(); //=> [
   *                                   //=>   { type: 'day', value: '25' },
   *                                   //=>   { type: 'literal', value: '/' },
   *                                   //=>   { type: 'month', value: '05' },
   *                                   //=>   { type: 'literal', value: '/' },
   *                                   //=>   { type: 'year', value: '1982' }
   *                                   //=> ]
   */
  toLocaleParts(opts = {}) {
    return this.isValid
      ? _impl_formatter_js__WEBPACK_IMPORTED_MODULE_4__["default"].create(this.loc.clone(opts), opts).formatDateTimeParts(this)
      : [];
  }

  /**
   * Returns an ISO 8601-compliant string representation of this DateTime
   * @param {Object} opts - options
   * @param {boolean} [opts.suppressMilliseconds=false] - exclude milliseconds from the format if they're 0
   * @param {boolean} [opts.suppressSeconds=false] - exclude seconds from the format if they're 0
   * @param {boolean} [opts.includeOffset=true] - include the offset, such as 'Z' or '-04:00'
   * @param {boolean} [opts.extendedZone=false] - add the time zone format extension
   * @param {string} [opts.format='extended'] - choose between the basic and extended format
   * @example DateTime.utc(1983, 5, 25).toISO() //=> '1982-05-25T00:00:00.000Z'
   * @example DateTime.now().toISO() //=> '2017-04-22T20:47:05.335-04:00'
   * @example DateTime.now().toISO({ includeOffset: false }) //=> '2017-04-22T20:47:05.335'
   * @example DateTime.now().toISO({ format: 'basic' }) //=> '20170422T204705.335-0400'
   * @return {string}
   */
  toISO({
    format = "extended",
    suppressSeconds = false,
    suppressMilliseconds = false,
    includeOffset = true,
    extendedZone = false,
  } = {}) {
    if (!this.isValid) {
      return null;
    }

    const ext = format === "extended";

    let c = toISODate(this, ext);
    c += "T";
    c += toISOTime(this, ext, suppressSeconds, suppressMilliseconds, includeOffset, extendedZone);
    return c;
  }

  /**
   * Returns an ISO 8601-compliant string representation of this DateTime's date component
   * @param {Object} opts - options
   * @param {string} [opts.format='extended'] - choose between the basic and extended format
   * @example DateTime.utc(1982, 5, 25).toISODate() //=> '1982-05-25'
   * @example DateTime.utc(1982, 5, 25).toISODate({ format: 'basic' }) //=> '19820525'
   * @return {string}
   */
  toISODate({ format = "extended" } = {}) {
    if (!this.isValid) {
      return null;
    }

    return toISODate(this, format === "extended");
  }

  /**
   * Returns an ISO 8601-compliant string representation of this DateTime's week date
   * @example DateTime.utc(1982, 5, 25).toISOWeekDate() //=> '1982-W21-2'
   * @return {string}
   */
  toISOWeekDate() {
    return toTechFormat(this, "kkkk-'W'WW-c");
  }

  /**
   * Returns an ISO 8601-compliant string representation of this DateTime's time component
   * @param {Object} opts - options
   * @param {boolean} [opts.suppressMilliseconds=false] - exclude milliseconds from the format if they're 0
   * @param {boolean} [opts.suppressSeconds=false] - exclude seconds from the format if they're 0
   * @param {boolean} [opts.includeOffset=true] - include the offset, such as 'Z' or '-04:00'
   * @param {boolean} [opts.extendedZone=true] - add the time zone format extension
   * @param {boolean} [opts.includePrefix=false] - include the `T` prefix
   * @param {string} [opts.format='extended'] - choose between the basic and extended format
   * @example DateTime.utc().set({ hour: 7, minute: 34 }).toISOTime() //=> '07:34:19.361Z'
   * @example DateTime.utc().set({ hour: 7, minute: 34, seconds: 0, milliseconds: 0 }).toISOTime({ suppressSeconds: true }) //=> '07:34Z'
   * @example DateTime.utc().set({ hour: 7, minute: 34 }).toISOTime({ format: 'basic' }) //=> '073419.361Z'
   * @example DateTime.utc().set({ hour: 7, minute: 34 }).toISOTime({ includePrefix: true }) //=> 'T07:34:19.361Z'
   * @return {string}
   */
  toISOTime({
    suppressMilliseconds = false,
    suppressSeconds = false,
    includeOffset = true,
    includePrefix = false,
    extendedZone = false,
    format = "extended",
  } = {}) {
    if (!this.isValid) {
      return null;
    }

    let c = includePrefix ? "T" : "";
    return (
      c +
      toISOTime(
        this,
        format === "extended",
        suppressSeconds,
        suppressMilliseconds,
        includeOffset,
        extendedZone
      )
    );
  }

  /**
   * Returns an RFC 2822-compatible string representation of this DateTime
   * @example DateTime.utc(2014, 7, 13).toRFC2822() //=> 'Sun, 13 Jul 2014 00:00:00 +0000'
   * @example DateTime.local(2014, 7, 13).toRFC2822() //=> 'Sun, 13 Jul 2014 00:00:00 -0400'
   * @return {string}
   */
  toRFC2822() {
    return toTechFormat(this, "EEE, dd LLL yyyy HH:mm:ss ZZZ", false);
  }

  /**
   * Returns a string representation of this DateTime appropriate for use in HTTP headers. The output is always expressed in GMT.
   * Specifically, the string conforms to RFC 1123.
   * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec3.html#sec3.3.1
   * @example DateTime.utc(2014, 7, 13).toHTTP() //=> 'Sun, 13 Jul 2014 00:00:00 GMT'
   * @example DateTime.utc(2014, 7, 13, 19).toHTTP() //=> 'Sun, 13 Jul 2014 19:00:00 GMT'
   * @return {string}
   */
  toHTTP() {
    return toTechFormat(this.toUTC(), "EEE, dd LLL yyyy HH:mm:ss 'GMT'");
  }

  /**
   * Returns a string representation of this DateTime appropriate for use in SQL Date
   * @example DateTime.utc(2014, 7, 13).toSQLDate() //=> '2014-07-13'
   * @return {string}
   */
  toSQLDate() {
    if (!this.isValid) {
      return null;
    }
    return toISODate(this, true);
  }

  /**
   * Returns a string representation of this DateTime appropriate for use in SQL Time
   * @param {Object} opts - options
   * @param {boolean} [opts.includeZone=false] - include the zone, such as 'America/New_York'. Overrides includeOffset.
   * @param {boolean} [opts.includeOffset=true] - include the offset, such as 'Z' or '-04:00'
   * @param {boolean} [opts.includeOffsetSpace=true] - include the space between the time and the offset, such as '05:15:16.345 -04:00'
   * @example DateTime.utc().toSQL() //=> '05:15:16.345'
   * @example DateTime.now().toSQL() //=> '05:15:16.345 -04:00'
   * @example DateTime.now().toSQL({ includeOffset: false }) //=> '05:15:16.345'
   * @example DateTime.now().toSQL({ includeZone: false }) //=> '05:15:16.345 America/New_York'
   * @return {string}
   */
  toSQLTime({ includeOffset = true, includeZone = false, includeOffsetSpace = true } = {}) {
    let fmt = "HH:mm:ss.SSS";

    if (includeZone || includeOffset) {
      if (includeOffsetSpace) {
        fmt += " ";
      }
      if (includeZone) {
        fmt += "z";
      } else if (includeOffset) {
        fmt += "ZZ";
      }
    }

    return toTechFormat(this, fmt, true);
  }

  /**
   * Returns a string representation of this DateTime appropriate for use in SQL DateTime
   * @param {Object} opts - options
   * @param {boolean} [opts.includeZone=false] - include the zone, such as 'America/New_York'. Overrides includeOffset.
   * @param {boolean} [opts.includeOffset=true] - include the offset, such as 'Z' or '-04:00'
   * @param {boolean} [opts.includeOffsetSpace=true] - include the space between the time and the offset, such as '05:15:16.345 -04:00'
   * @example DateTime.utc(2014, 7, 13).toSQL() //=> '2014-07-13 00:00:00.000 Z'
   * @example DateTime.local(2014, 7, 13).toSQL() //=> '2014-07-13 00:00:00.000 -04:00'
   * @example DateTime.local(2014, 7, 13).toSQL({ includeOffset: false }) //=> '2014-07-13 00:00:00.000'
   * @example DateTime.local(2014, 7, 13).toSQL({ includeZone: true }) //=> '2014-07-13 00:00:00.000 America/New_York'
   * @return {string}
   */
  toSQL(opts = {}) {
    if (!this.isValid) {
      return null;
    }

    return `${this.toSQLDate()} ${this.toSQLTime(opts)}`;
  }

  /**
   * Returns a string representation of this DateTime appropriate for debugging
   * @return {string}
   */
  toString() {
    return this.isValid ? this.toISO() : INVALID;
  }

  /**
   * Returns a string representation of this DateTime appropriate for the REPL.
   * @return {string}
   */
  [Symbol.for("nodejs.util.inspect.custom")]() {
    if (this.isValid) {
      return `DateTime { ts: ${this.toISO()}, zone: ${this.zone.name}, locale: ${this.locale} }`;
    } else {
      return `DateTime { Invalid, reason: ${this.invalidReason} }`;
    }
  }

  /**
   * Returns the epoch milliseconds of this DateTime. Alias of {@link DateTime#toMillis}
   * @return {number}
   */
  valueOf() {
    return this.toMillis();
  }

  /**
   * Returns the epoch milliseconds of this DateTime.
   * @return {number}
   */
  toMillis() {
    return this.isValid ? this.ts : NaN;
  }

  /**
   * Returns the epoch seconds of this DateTime.
   * @return {number}
   */
  toSeconds() {
    return this.isValid ? this.ts / 1000 : NaN;
  }

  /**
   * Returns the epoch seconds (as a whole number) of this DateTime.
   * @return {number}
   */
  toUnixInteger() {
    return this.isValid ? Math.floor(this.ts / 1000) : NaN;
  }

  /**
   * Returns an ISO 8601 representation of this DateTime appropriate for use in JSON.
   * @return {string}
   */
  toJSON() {
    return this.toISO();
  }

  /**
   * Returns a BSON serializable equivalent to this DateTime.
   * @return {Date}
   */
  toBSON() {
    return this.toJSDate();
  }

  /**
   * Returns a JavaScript object with this DateTime's year, month, day, and so on.
   * @param opts - options for generating the object
   * @param {boolean} [opts.includeConfig=false] - include configuration attributes in the output
   * @example DateTime.now().toObject() //=> { year: 2017, month: 4, day: 22, hour: 20, minute: 49, second: 42, millisecond: 268 }
   * @return {Object}
   */
  toObject(opts = {}) {
    if (!this.isValid) return {};

    const base = { ...this.c };

    if (opts.includeConfig) {
      base.outputCalendar = this.outputCalendar;
      base.numberingSystem = this.loc.numberingSystem;
      base.locale = this.loc.locale;
    }
    return base;
  }

  /**
   * Returns a JavaScript Date equivalent to this DateTime.
   * @return {Date}
   */
  toJSDate() {
    return new Date(this.isValid ? this.ts : NaN);
  }

  // COMPARE

  /**
   * Return the difference between two DateTimes as a Duration.
   * @param {DateTime} otherDateTime - the DateTime to compare this one to
   * @param {string|string[]} [unit=['milliseconds']] - the unit or array of units (such as 'hours' or 'days') to include in the duration.
   * @param {Object} opts - options that affect the creation of the Duration
   * @param {string} [opts.conversionAccuracy='casual'] - the conversion system to use
   * @example
   * var i1 = DateTime.fromISO('1982-05-25T09:45'),
   *     i2 = DateTime.fromISO('1983-10-14T10:30');
   * i2.diff(i1).toObject() //=> { milliseconds: 43807500000 }
   * i2.diff(i1, 'hours').toObject() //=> { hours: 12168.75 }
   * i2.diff(i1, ['months', 'days']).toObject() //=> { months: 16, days: 19.03125 }
   * i2.diff(i1, ['months', 'days', 'hours']).toObject() //=> { months: 16, days: 19, hours: 0.75 }
   * @return {Duration}
   */
  diff(otherDateTime, unit = "milliseconds", opts = {}) {
    if (!this.isValid || !otherDateTime.isValid) {
      return _duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].invalid("created by diffing an invalid DateTime");
    }

    const durOpts = { locale: this.locale, numberingSystem: this.numberingSystem, ...opts };

    const units = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.maybeArray)(unit).map(_duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].normalizeUnit),
      otherIsLater = otherDateTime.valueOf() > this.valueOf(),
      earlier = otherIsLater ? this : otherDateTime,
      later = otherIsLater ? otherDateTime : this,
      diffed = (0,_impl_diff_js__WEBPACK_IMPORTED_MODULE_9__["default"])(earlier, later, units, durOpts);

    return otherIsLater ? diffed.negate() : diffed;
  }

  /**
   * Return the difference between this DateTime and right now.
   * See {@link DateTime#diff}
   * @param {string|string[]} [unit=['milliseconds']] - the unit or units units (such as 'hours' or 'days') to include in the duration
   * @param {Object} opts - options that affect the creation of the Duration
   * @param {string} [opts.conversionAccuracy='casual'] - the conversion system to use
   * @return {Duration}
   */
  diffNow(unit = "milliseconds", opts = {}) {
    return this.diff(DateTime.now(), unit, opts);
  }

  /**
   * Return an Interval spanning between this DateTime and another DateTime
   * @param {DateTime} otherDateTime - the other end point of the Interval
   * @return {Interval}
   */
  until(otherDateTime) {
    return this.isValid ? _interval_js__WEBPACK_IMPORTED_MODULE_1__["default"].fromDateTimes(this, otherDateTime) : this;
  }

  /**
   * Return whether this DateTime is in the same unit of time as another DateTime.
   * Higher-order units must also be identical for this function to return `true`.
   * Note that time zones are **ignored** in this comparison, which compares the **local** calendar time. Use {@link DateTime#setZone} to convert one of the dates if needed.
   * @param {DateTime} otherDateTime - the other DateTime
   * @param {string} unit - the unit of time to check sameness on
   * @param {Object} opts - options
   * @param {boolean} [opts.useLocaleWeeks=false] - If true, use weeks based on the locale, i.e. use the locale-dependent start of the week; only the locale of this DateTime is used
   * @example DateTime.now().hasSame(otherDT, 'day'); //~> true if otherDT is in the same current calendar day
   * @return {boolean}
   */
  hasSame(otherDateTime, unit, opts) {
    if (!this.isValid) return false;

    const inputMs = otherDateTime.valueOf();
    const adjustedToZone = this.setZone(otherDateTime.zone, { keepLocalTime: true });
    return (
      adjustedToZone.startOf(unit, opts) <= inputMs && inputMs <= adjustedToZone.endOf(unit, opts)
    );
  }

  /**
   * Equality check
   * Two DateTimes are equal if and only if they represent the same millisecond, have the same zone and location, and are both valid.
   * To compare just the millisecond values, use `+dt1 === +dt2`.
   * @param {DateTime} other - the other DateTime
   * @return {boolean}
   */
  equals(other) {
    return (
      this.isValid &&
      other.isValid &&
      this.valueOf() === other.valueOf() &&
      this.zone.equals(other.zone) &&
      this.loc.equals(other.loc)
    );
  }

  /**
   * Returns a string representation of a this time relative to now, such as "in two days". Can only internationalize if your
   * platform supports Intl.RelativeTimeFormat. Rounds down by default.
   * @param {Object} options - options that affect the output
   * @param {DateTime} [options.base=DateTime.now()] - the DateTime to use as the basis to which this time is compared. Defaults to now.
   * @param {string} [options.style="long"] - the style of units, must be "long", "short", or "narrow"
   * @param {string|string[]} options.unit - use a specific unit or array of units; if omitted, or an array, the method will pick the best unit. Use an array or one of "years", "quarters", "months", "weeks", "days", "hours", "minutes", or "seconds"
   * @param {boolean} [options.round=true] - whether to round the numbers in the output.
   * @param {number} [options.padding=0] - padding in milliseconds. This allows you to round up the result if it fits inside the threshold. Don't use in combination with {round: false} because the decimal output will include the padding.
   * @param {string} options.locale - override the locale of this DateTime
   * @param {string} options.numberingSystem - override the numberingSystem of this DateTime. The Intl system may choose not to honor this
   * @example DateTime.now().plus({ days: 1 }).toRelative() //=> "in 1 day"
   * @example DateTime.now().setLocale("es").toRelative({ days: 1 }) //=> "dentro de 1 día"
   * @example DateTime.now().plus({ days: 1 }).toRelative({ locale: "fr" }) //=> "dans 23 heures"
   * @example DateTime.now().minus({ days: 2 }).toRelative() //=> "2 days ago"
   * @example DateTime.now().minus({ days: 2 }).toRelative({ unit: "hours" }) //=> "48 hours ago"
   * @example DateTime.now().minus({ hours: 36 }).toRelative({ round: false }) //=> "1.5 days ago"
   */
  toRelative(options = {}) {
    if (!this.isValid) return null;
    const base = options.base || DateTime.fromObject({}, { zone: this.zone }),
      padding = options.padding ? (this < base ? -options.padding : options.padding) : 0;
    let units = ["years", "months", "days", "hours", "minutes", "seconds"];
    let unit = options.unit;
    if (Array.isArray(options.unit)) {
      units = options.unit;
      unit = undefined;
    }
    return diffRelative(base, this.plus(padding), {
      ...options,
      numeric: "always",
      units,
      unit,
    });
  }

  /**
   * Returns a string representation of this date relative to today, such as "yesterday" or "next month".
   * Only internationalizes on platforms that supports Intl.RelativeTimeFormat.
   * @param {Object} options - options that affect the output
   * @param {DateTime} [options.base=DateTime.now()] - the DateTime to use as the basis to which this time is compared. Defaults to now.
   * @param {string} options.locale - override the locale of this DateTime
   * @param {string} options.unit - use a specific unit; if omitted, the method will pick the unit. Use one of "years", "quarters", "months", "weeks", or "days"
   * @param {string} options.numberingSystem - override the numberingSystem of this DateTime. The Intl system may choose not to honor this
   * @example DateTime.now().plus({ days: 1 }).toRelativeCalendar() //=> "tomorrow"
   * @example DateTime.now().setLocale("es").plus({ days: 1 }).toRelative() //=> ""mañana"
   * @example DateTime.now().plus({ days: 1 }).toRelativeCalendar({ locale: "fr" }) //=> "demain"
   * @example DateTime.now().minus({ days: 2 }).toRelativeCalendar() //=> "2 days ago"
   */
  toRelativeCalendar(options = {}) {
    if (!this.isValid) return null;

    return diffRelative(options.base || DateTime.fromObject({}, { zone: this.zone }), this, {
      ...options,
      numeric: "auto",
      units: ["years", "months", "days"],
      calendary: true,
    });
  }

  /**
   * Return the min of several date times
   * @param {...DateTime} dateTimes - the DateTimes from which to choose the minimum
   * @return {DateTime} the min DateTime, or undefined if called with no argument
   */
  static min(...dateTimes) {
    if (!dateTimes.every(DateTime.isDateTime)) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError("min requires all arguments be DateTimes");
    }
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.bestBy)(dateTimes, (i) => i.valueOf(), Math.min);
  }

  /**
   * Return the max of several date times
   * @param {...DateTime} dateTimes - the DateTimes from which to choose the maximum
   * @return {DateTime} the max DateTime, or undefined if called with no argument
   */
  static max(...dateTimes) {
    if (!dateTimes.every(DateTime.isDateTime)) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError("max requires all arguments be DateTimes");
    }
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.bestBy)(dateTimes, (i) => i.valueOf(), Math.max);
  }

  // MISC

  /**
   * Explain how a string would be parsed by fromFormat()
   * @param {string} text - the string to parse
   * @param {string} fmt - the format the string is expected to be in (see description)
   * @param {Object} options - options taken by fromFormat()
   * @return {Object}
   */
  static fromFormatExplain(text, fmt, options = {}) {
    const { locale = null, numberingSystem = null } = options,
      localeToUse = _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromOpts({
        locale,
        numberingSystem,
        defaultToEN: true,
      });
    return (0,_impl_tokenParser_js__WEBPACK_IMPORTED_MODULE_11__.explainFromTokens)(localeToUse, text, fmt);
  }

  /**
   * @deprecated use fromFormatExplain instead
   */
  static fromStringExplain(text, fmt, options = {}) {
    return DateTime.fromFormatExplain(text, fmt, options);
  }

  /**
   * Build a parser for `fmt` using the given locale. This parser can be passed
   * to {@link DateTime.fromFormatParser} to a parse a date in this format. This
   * can be used to optimize cases where many dates need to be parsed in a
   * specific format.
   *
   * @param {String} fmt - the format the string is expected to be in (see
   * description)
   * @param {Object} options - options used to set locale and numberingSystem
   * for parser
   * @returns {TokenParser} - opaque object to be used
   */
  static buildFormatParser(fmt, options = {}) {
    const { locale = null, numberingSystem = null } = options,
      localeToUse = _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromOpts({
        locale,
        numberingSystem,
        defaultToEN: true,
      });
    return new _impl_tokenParser_js__WEBPACK_IMPORTED_MODULE_11__.TokenParser(localeToUse, fmt);
  }

  /**
   * Create a DateTime from an input string and format parser.
   *
   * The format parser must have been created with the same locale as this call.
   *
   * @param {String} text - the string to parse
   * @param {TokenParser} formatParser - parser from {@link DateTime.buildFormatParser}
   * @param {Object} opts - options taken by fromFormat()
   * @returns {DateTime}
   */
  static fromFormatParser(text, formatParser, opts = {}) {
    if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(text) || (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isUndefined)(formatParser)) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError(
        "fromFormatParser requires an input string and a format parser"
      );
    }
    const { locale = null, numberingSystem = null } = opts,
      localeToUse = _impl_locale_js__WEBPACK_IMPORTED_MODULE_6__["default"].fromOpts({
        locale,
        numberingSystem,
        defaultToEN: true,
      });

    if (!localeToUse.equals(formatParser.locale)) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError(
        `fromFormatParser called with a locale of ${localeToUse}, ` +
          `but the format parser was created for ${formatParser.locale}`
      );
    }

    const { result, zone, specificOffset, invalidReason } = formatParser.explainFromTokens(text);

    if (invalidReason) {
      return DateTime.invalid(invalidReason);
    } else {
      return parseDataToDateTime(
        result,
        zone,
        opts,
        `format ${formatParser.format}`,
        text,
        specificOffset
      );
    }
  }

  // FORMAT PRESETS

  /**
   * {@link DateTime#toLocaleString} format like 10/14/1983
   * @type {Object}
   */
  static get DATE_SHORT() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATE_SHORT;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Oct 14, 1983'
   * @type {Object}
   */
  static get DATE_MED() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATE_MED;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Fri, Oct 14, 1983'
   * @type {Object}
   */
  static get DATE_MED_WITH_WEEKDAY() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATE_MED_WITH_WEEKDAY;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'October 14, 1983'
   * @type {Object}
   */
  static get DATE_FULL() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATE_FULL;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Tuesday, October 14, 1983'
   * @type {Object}
   */
  static get DATE_HUGE() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATE_HUGE;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30 AM'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get TIME_SIMPLE() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_SIMPLE;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30:23 AM'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get TIME_WITH_SECONDS() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_WITH_SECONDS;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30:23 AM EDT'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get TIME_WITH_SHORT_OFFSET() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_WITH_SHORT_OFFSET;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30:23 AM Eastern Daylight Time'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get TIME_WITH_LONG_OFFSET() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_WITH_LONG_OFFSET;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30', always 24-hour.
   * @type {Object}
   */
  static get TIME_24_SIMPLE() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_24_SIMPLE;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30:23', always 24-hour.
   * @type {Object}
   */
  static get TIME_24_WITH_SECONDS() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_24_WITH_SECONDS;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30:23 EDT', always 24-hour.
   * @type {Object}
   */
  static get TIME_24_WITH_SHORT_OFFSET() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_24_WITH_SHORT_OFFSET;
  }

  /**
   * {@link DateTime#toLocaleString} format like '09:30:23 Eastern Daylight Time', always 24-hour.
   * @type {Object}
   */
  static get TIME_24_WITH_LONG_OFFSET() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.TIME_24_WITH_LONG_OFFSET;
  }

  /**
   * {@link DateTime#toLocaleString} format like '10/14/1983, 9:30 AM'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_SHORT() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_SHORT;
  }

  /**
   * {@link DateTime#toLocaleString} format like '10/14/1983, 9:30:33 AM'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_SHORT_WITH_SECONDS() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_SHORT_WITH_SECONDS;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Oct 14, 1983, 9:30 AM'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_MED() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_MED;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Oct 14, 1983, 9:30:33 AM'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_MED_WITH_SECONDS() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_MED_WITH_SECONDS;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Fri, 14 Oct 1983, 9:30 AM'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_MED_WITH_WEEKDAY() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_MED_WITH_WEEKDAY;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'October 14, 1983, 9:30 AM EDT'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_FULL() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_FULL;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'October 14, 1983, 9:30:33 AM EDT'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_FULL_WITH_SECONDS() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_FULL_WITH_SECONDS;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Friday, October 14, 1983, 9:30 AM Eastern Daylight Time'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_HUGE() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_HUGE;
  }

  /**
   * {@link DateTime#toLocaleString} format like 'Friday, October 14, 1983, 9:30:33 AM Eastern Daylight Time'. Only 12-hour if the locale is.
   * @type {Object}
   */
  static get DATETIME_HUGE_WITH_SECONDS() {
    return _impl_formats_js__WEBPACK_IMPORTED_MODULE_13__.DATETIME_HUGE_WITH_SECONDS;
  }
}

/**
 * @private
 */
function friendlyDateTime(dateTimeish) {
  if (DateTime.isDateTime(dateTimeish)) {
    return dateTimeish;
  } else if (dateTimeish && dateTimeish.valueOf && (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_7__.isNumber)(dateTimeish.valueOf())) {
    return DateTime.fromJSDate(dateTimeish);
  } else if (dateTimeish && typeof dateTimeish === "object") {
    return DateTime.fromObject(dateTimeish);
  } else {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_14__.InvalidArgumentError(
      `Unknown datetime argument: ${dateTimeish}, of type ${typeof dateTimeish}`
    );
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/duration.js":
/*!********************************************!*\
  !*** ./node_modules/luxon/src/duration.js ***!
  \********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   accurateMatrix: () => (/* binding */ accurateMatrix),
/* harmony export */   casualMatrix: () => (/* binding */ casualMatrix),
/* harmony export */   daysInMonthAccurate: () => (/* binding */ daysInMonthAccurate),
/* harmony export */   daysInYearAccurate: () => (/* binding */ daysInYearAccurate),
/* harmony export */   "default": () => (/* binding */ Duration),
/* harmony export */   lowOrderMatrix: () => (/* binding */ lowOrderMatrix)
/* harmony export */ });
/* harmony import */ var _errors_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./errors.js */ "./node_modules/luxon/src/errors.js");
/* harmony import */ var _impl_formatter_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./impl/formatter.js */ "./node_modules/luxon/src/impl/formatter.js");
/* harmony import */ var _impl_invalid_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./impl/invalid.js */ "./node_modules/luxon/src/impl/invalid.js");
/* harmony import */ var _impl_locale_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./impl/locale.js */ "./node_modules/luxon/src/impl/locale.js");
/* harmony import */ var _impl_regexParser_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./impl/regexParser.js */ "./node_modules/luxon/src/impl/regexParser.js");
/* harmony import */ var _impl_util_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./impl/util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _settings_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./settings.js */ "./node_modules/luxon/src/settings.js");
/* harmony import */ var _datetime_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./datetime.js */ "./node_modules/luxon/src/datetime.js");









const INVALID = "Invalid Duration";

// unit conversion constants
const lowOrderMatrix = {
    weeks: {
      days: 7,
      hours: 7 * 24,
      minutes: 7 * 24 * 60,
      seconds: 7 * 24 * 60 * 60,
      milliseconds: 7 * 24 * 60 * 60 * 1000,
    },
    days: {
      hours: 24,
      minutes: 24 * 60,
      seconds: 24 * 60 * 60,
      milliseconds: 24 * 60 * 60 * 1000,
    },
    hours: { minutes: 60, seconds: 60 * 60, milliseconds: 60 * 60 * 1000 },
    minutes: { seconds: 60, milliseconds: 60 * 1000 },
    seconds: { milliseconds: 1000 },
  },
  casualMatrix = {
    years: {
      quarters: 4,
      months: 12,
      weeks: 52,
      days: 365,
      hours: 365 * 24,
      minutes: 365 * 24 * 60,
      seconds: 365 * 24 * 60 * 60,
      milliseconds: 365 * 24 * 60 * 60 * 1000,
    },
    quarters: {
      months: 3,
      weeks: 13,
      days: 91,
      hours: 91 * 24,
      minutes: 91 * 24 * 60,
      seconds: 91 * 24 * 60 * 60,
      milliseconds: 91 * 24 * 60 * 60 * 1000,
    },
    months: {
      weeks: 4,
      days: 30,
      hours: 30 * 24,
      minutes: 30 * 24 * 60,
      seconds: 30 * 24 * 60 * 60,
      milliseconds: 30 * 24 * 60 * 60 * 1000,
    },

    ...lowOrderMatrix,
  },
  daysInYearAccurate = 146097.0 / 400,
  daysInMonthAccurate = 146097.0 / 4800,
  accurateMatrix = {
    years: {
      quarters: 4,
      months: 12,
      weeks: daysInYearAccurate / 7,
      days: daysInYearAccurate,
      hours: daysInYearAccurate * 24,
      minutes: daysInYearAccurate * 24 * 60,
      seconds: daysInYearAccurate * 24 * 60 * 60,
      milliseconds: daysInYearAccurate * 24 * 60 * 60 * 1000,
    },
    quarters: {
      months: 3,
      weeks: daysInYearAccurate / 28,
      days: daysInYearAccurate / 4,
      hours: (daysInYearAccurate * 24) / 4,
      minutes: (daysInYearAccurate * 24 * 60) / 4,
      seconds: (daysInYearAccurate * 24 * 60 * 60) / 4,
      milliseconds: (daysInYearAccurate * 24 * 60 * 60 * 1000) / 4,
    },
    months: {
      weeks: daysInMonthAccurate / 7,
      days: daysInMonthAccurate,
      hours: daysInMonthAccurate * 24,
      minutes: daysInMonthAccurate * 24 * 60,
      seconds: daysInMonthAccurate * 24 * 60 * 60,
      milliseconds: daysInMonthAccurate * 24 * 60 * 60 * 1000,
    },
    ...lowOrderMatrix,
  };

// units ordered by size
const orderedUnits = [
  "years",
  "quarters",
  "months",
  "weeks",
  "days",
  "hours",
  "minutes",
  "seconds",
  "milliseconds",
];

const reverseUnits = orderedUnits.slice(0).reverse();

// clone really means "create another instance just like this one, but with these changes"
function clone(dur, alts, clear = false) {
  // deep merge for vals
  const conf = {
    values: clear ? alts.values : { ...dur.values, ...(alts.values || {}) },
    loc: dur.loc.clone(alts.loc),
    conversionAccuracy: alts.conversionAccuracy || dur.conversionAccuracy,
    matrix: alts.matrix || dur.matrix,
  };
  return new Duration(conf);
}

function durationToMillis(matrix, vals) {
  let sum = vals.milliseconds ?? 0;
  for (const unit of reverseUnits.slice(1)) {
    if (vals[unit]) {
      sum += vals[unit] * matrix[unit]["milliseconds"];
    }
  }
  return sum;
}

// NB: mutates parameters
function normalizeValues(matrix, vals) {
  // the logic below assumes the overall value of the duration is positive
  // if this is not the case, factor is used to make it so
  const factor = durationToMillis(matrix, vals) < 0 ? -1 : 1;

  orderedUnits.reduceRight((previous, current) => {
    if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.isUndefined)(vals[current])) {
      if (previous) {
        const previousVal = vals[previous] * factor;
        const conv = matrix[current][previous];

        // if (previousVal < 0):
        // lower order unit is negative (e.g. { years: 2, days: -2 })
        // normalize this by reducing the higher order unit by the appropriate amount
        // and increasing the lower order unit
        // this can never make the higher order unit negative, because this function only operates
        // on positive durations, so the amount of time represented by the lower order unit cannot
        // be larger than the higher order unit
        // else:
        // lower order unit is positive (e.g. { years: 2, days: 450 } or { years: -2, days: 450 })
        // in this case we attempt to convert as much as possible from the lower order unit into
        // the higher order one
        //
        // Math.floor takes care of both of these cases, rounding away from 0
        // if previousVal < 0 it makes the absolute value larger
        // if previousVal >= it makes the absolute value smaller
        const rollUp = Math.floor(previousVal / conv);
        vals[current] += rollUp * factor;
        vals[previous] -= rollUp * conv * factor;
      }
      return current;
    } else {
      return previous;
    }
  }, null);

  // try to convert any decimals into smaller units if possible
  // for example for { years: 2.5, days: 0, seconds: 0 } we want to get { years: 2, days: 182, hours: 12 }
  orderedUnits.reduce((previous, current) => {
    if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.isUndefined)(vals[current])) {
      if (previous) {
        const fraction = vals[previous] % 1;
        vals[previous] -= fraction;
        vals[current] += fraction * matrix[previous][current];
      }
      return current;
    } else {
      return previous;
    }
  }, null);
}

// Remove all properties with a value of 0 from an object
function removeZeroes(vals) {
  const newVals = {};
  for (const [key, value] of Object.entries(vals)) {
    if (value !== 0) {
      newVals[key] = value;
    }
  }
  return newVals;
}

/**
 * A Duration object represents a period of time, like "2 months" or "1 day, 1 hour". Conceptually, it's just a map of units to their quantities, accompanied by some additional configuration and methods for creating, parsing, interrogating, transforming, and formatting them. They can be used on their own or in conjunction with other Luxon types; for example, you can use {@link DateTime#plus} to add a Duration object to a DateTime, producing another DateTime.
 *
 * Here is a brief overview of commonly used methods and getters in Duration:
 *
 * * **Creation** To create a Duration, use {@link Duration.fromMillis}, {@link Duration.fromObject}, or {@link Duration.fromISO}.
 * * **Unit values** See the {@link Duration#years}, {@link Duration#months}, {@link Duration#weeks}, {@link Duration#days}, {@link Duration#hours}, {@link Duration#minutes}, {@link Duration#seconds}, {@link Duration#milliseconds} accessors.
 * * **Configuration** See  {@link Duration#locale} and {@link Duration#numberingSystem} accessors.
 * * **Transformation** To create new Durations out of old ones use {@link Duration#plus}, {@link Duration#minus}, {@link Duration#normalize}, {@link Duration#set}, {@link Duration#reconfigure}, {@link Duration#shiftTo}, and {@link Duration#negate}.
 * * **Output** To convert the Duration into other representations, see {@link Duration#as}, {@link Duration#toISO}, {@link Duration#toFormat}, and {@link Duration#toJSON}
 *
 * There's are more methods documented below. In addition, for more information on subtler topics like internationalization and validity, see the external documentation.
 */
class Duration {
  /**
   * @private
   */
  constructor(config) {
    const accurate = config.conversionAccuracy === "longterm" || false;
    let matrix = accurate ? accurateMatrix : casualMatrix;

    if (config.matrix) {
      matrix = config.matrix;
    }

    /**
     * @access private
     */
    this.values = config.values;
    /**
     * @access private
     */
    this.loc = config.loc || _impl_locale_js__WEBPACK_IMPORTED_MODULE_3__["default"].create();
    /**
     * @access private
     */
    this.conversionAccuracy = accurate ? "longterm" : "casual";
    /**
     * @access private
     */
    this.invalid = config.invalid || null;
    /**
     * @access private
     */
    this.matrix = matrix;
    /**
     * @access private
     */
    this.isLuxonDuration = true;
  }

  /**
   * Create Duration from a number of milliseconds.
   * @param {number} count of milliseconds
   * @param {Object} opts - options for parsing
   * @param {string} [opts.locale='en-US'] - the locale to use
   * @param {string} opts.numberingSystem - the numbering system to use
   * @param {string} [opts.conversionAccuracy='casual'] - the conversion system to use
   * @return {Duration}
   */
  static fromMillis(count, opts) {
    return Duration.fromObject({ milliseconds: count }, opts);
  }

  /**
   * Create a Duration from a JavaScript object with keys like 'years' and 'hours'.
   * If this object is empty then a zero milliseconds duration is returned.
   * @param {Object} obj - the object to create the DateTime from
   * @param {number} obj.years
   * @param {number} obj.quarters
   * @param {number} obj.months
   * @param {number} obj.weeks
   * @param {number} obj.days
   * @param {number} obj.hours
   * @param {number} obj.minutes
   * @param {number} obj.seconds
   * @param {number} obj.milliseconds
   * @param {Object} [opts=[]] - options for creating this Duration
   * @param {string} [opts.locale='en-US'] - the locale to use
   * @param {string} opts.numberingSystem - the numbering system to use
   * @param {string} [opts.conversionAccuracy='casual'] - the preset conversion system to use
   * @param {string} [opts.matrix=Object] - the custom conversion system to use
   * @return {Duration}
   */
  static fromObject(obj, opts = {}) {
    if (obj == null || typeof obj !== "object") {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidArgumentError(
        `Duration.fromObject: argument expected to be an object, got ${
          obj === null ? "null" : typeof obj
        }`
      );
    }

    return new Duration({
      values: (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.normalizeObject)(obj, Duration.normalizeUnit),
      loc: _impl_locale_js__WEBPACK_IMPORTED_MODULE_3__["default"].fromObject(opts),
      conversionAccuracy: opts.conversionAccuracy,
      matrix: opts.matrix,
    });
  }

  /**
   * Create a Duration from DurationLike.
   *
   * @param {Object | number | Duration} durationLike
   * One of:
   * - object with keys like 'years' and 'hours'.
   * - number representing milliseconds
   * - Duration instance
   * @return {Duration}
   */
  static fromDurationLike(durationLike) {
    if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.isNumber)(durationLike)) {
      return Duration.fromMillis(durationLike);
    } else if (Duration.isDuration(durationLike)) {
      return durationLike;
    } else if (typeof durationLike === "object") {
      return Duration.fromObject(durationLike);
    } else {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidArgumentError(
        `Unknown duration argument ${durationLike} of type ${typeof durationLike}`
      );
    }
  }

  /**
   * Create a Duration from an ISO 8601 duration string.
   * @param {string} text - text to parse
   * @param {Object} opts - options for parsing
   * @param {string} [opts.locale='en-US'] - the locale to use
   * @param {string} opts.numberingSystem - the numbering system to use
   * @param {string} [opts.conversionAccuracy='casual'] - the preset conversion system to use
   * @param {string} [opts.matrix=Object] - the preset conversion system to use
   * @see https://en.wikipedia.org/wiki/ISO_8601#Durations
   * @example Duration.fromISO('P3Y6M1W4DT12H30M5S').toObject() //=> { years: 3, months: 6, weeks: 1, days: 4, hours: 12, minutes: 30, seconds: 5 }
   * @example Duration.fromISO('PT23H').toObject() //=> { hours: 23 }
   * @example Duration.fromISO('P5Y3M').toObject() //=> { years: 5, months: 3 }
   * @return {Duration}
   */
  static fromISO(text, opts) {
    const [parsed] = (0,_impl_regexParser_js__WEBPACK_IMPORTED_MODULE_4__.parseISODuration)(text);
    if (parsed) {
      return Duration.fromObject(parsed, opts);
    } else {
      return Duration.invalid("unparsable", `the input "${text}" can't be parsed as ISO 8601`);
    }
  }

  /**
   * Create a Duration from an ISO 8601 time string.
   * @param {string} text - text to parse
   * @param {Object} opts - options for parsing
   * @param {string} [opts.locale='en-US'] - the locale to use
   * @param {string} opts.numberingSystem - the numbering system to use
   * @param {string} [opts.conversionAccuracy='casual'] - the preset conversion system to use
   * @param {string} [opts.matrix=Object] - the conversion system to use
   * @see https://en.wikipedia.org/wiki/ISO_8601#Times
   * @example Duration.fromISOTime('11:22:33.444').toObject() //=> { hours: 11, minutes: 22, seconds: 33, milliseconds: 444 }
   * @example Duration.fromISOTime('11:00').toObject() //=> { hours: 11, minutes: 0, seconds: 0 }
   * @example Duration.fromISOTime('T11:00').toObject() //=> { hours: 11, minutes: 0, seconds: 0 }
   * @example Duration.fromISOTime('1100').toObject() //=> { hours: 11, minutes: 0, seconds: 0 }
   * @example Duration.fromISOTime('T1100').toObject() //=> { hours: 11, minutes: 0, seconds: 0 }
   * @return {Duration}
   */
  static fromISOTime(text, opts) {
    const [parsed] = (0,_impl_regexParser_js__WEBPACK_IMPORTED_MODULE_4__.parseISOTimeOnly)(text);
    if (parsed) {
      return Duration.fromObject(parsed, opts);
    } else {
      return Duration.invalid("unparsable", `the input "${text}" can't be parsed as ISO 8601`);
    }
  }

  /**
   * Create an invalid Duration.
   * @param {string} reason - simple string of why this datetime is invalid. Should not contain parameters or anything else data-dependent
   * @param {string} [explanation=null] - longer explanation, may include parameters and other useful debugging information
   * @return {Duration}
   */
  static invalid(reason, explanation = null) {
    if (!reason) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidArgumentError("need to specify a reason the Duration is invalid");
    }

    const invalid = reason instanceof _impl_invalid_js__WEBPACK_IMPORTED_MODULE_2__["default"] ? reason : new _impl_invalid_js__WEBPACK_IMPORTED_MODULE_2__["default"](reason, explanation);

    if (_settings_js__WEBPACK_IMPORTED_MODULE_6__["default"].throwOnInvalid) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidDurationError(invalid);
    } else {
      return new Duration({ invalid });
    }
  }

  /**
   * @private
   */
  static normalizeUnit(unit) {
    const normalized = {
      year: "years",
      years: "years",
      quarter: "quarters",
      quarters: "quarters",
      month: "months",
      months: "months",
      week: "weeks",
      weeks: "weeks",
      day: "days",
      days: "days",
      hour: "hours",
      hours: "hours",
      minute: "minutes",
      minutes: "minutes",
      second: "seconds",
      seconds: "seconds",
      millisecond: "milliseconds",
      milliseconds: "milliseconds",
    }[unit ? unit.toLowerCase() : unit];

    if (!normalized) throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidUnitError(unit);

    return normalized;
  }

  /**
   * Check if an object is a Duration. Works across context boundaries
   * @param {object} o
   * @return {boolean}
   */
  static isDuration(o) {
    return (o && o.isLuxonDuration) || false;
  }

  /**
   * Get  the locale of a Duration, such 'en-GB'
   * @type {string}
   */
  get locale() {
    return this.isValid ? this.loc.locale : null;
  }

  /**
   * Get the numbering system of a Duration, such 'beng'. The numbering system is used when formatting the Duration
   *
   * @type {string}
   */
  get numberingSystem() {
    return this.isValid ? this.loc.numberingSystem : null;
  }

  /**
   * Returns a string representation of this Duration formatted according to the specified format string. You may use these tokens:
   * * `S` for milliseconds
   * * `s` for seconds
   * * `m` for minutes
   * * `h` for hours
   * * `d` for days
   * * `w` for weeks
   * * `M` for months
   * * `y` for years
   * Notes:
   * * Add padding by repeating the token, e.g. "yy" pads the years to two digits, "hhhh" pads the hours out to four digits
   * * Tokens can be escaped by wrapping with single quotes.
   * * The duration will be converted to the set of units in the format string using {@link Duration#shiftTo} and the Durations's conversion accuracy setting.
   * @param {string} fmt - the format string
   * @param {Object} opts - options
   * @param {boolean} [opts.floor=true] - floor numerical values
   * @example Duration.fromObject({ years: 1, days: 6, seconds: 2 }).toFormat("y d s") //=> "1 6 2"
   * @example Duration.fromObject({ years: 1, days: 6, seconds: 2 }).toFormat("yy dd sss") //=> "01 06 002"
   * @example Duration.fromObject({ years: 1, days: 6, seconds: 2 }).toFormat("M S") //=> "12 518402000"
   * @return {string}
   */
  toFormat(fmt, opts = {}) {
    // reverse-compat since 1.2; we always round down now, never up, and we do it by default
    const fmtOpts = {
      ...opts,
      floor: opts.round !== false && opts.floor !== false,
    };
    return this.isValid
      ? _impl_formatter_js__WEBPACK_IMPORTED_MODULE_1__["default"].create(this.loc, fmtOpts).formatDurationFromString(this, fmt)
      : INVALID;
  }

  /**
   * Returns a string representation of a Duration with all units included.
   * To modify its behavior, use `listStyle` and any Intl.NumberFormat option, though `unitDisplay` is especially relevant.
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Intl/NumberFormat/NumberFormat#options
   * @param {Object} opts - Formatting options. Accepts the same keys as the options parameter of the native `Intl.NumberFormat` constructor, as well as `listStyle`.
   * @param {string} [opts.listStyle='narrow'] - How to format the merged list. Corresponds to the `style` property of the options parameter of the native `Intl.ListFormat` constructor.
   * @example
   * ```js
   * var dur = Duration.fromObject({ days: 1, hours: 5, minutes: 6 })
   * dur.toHuman() //=> '1 day, 5 hours, 6 minutes'
   * dur.toHuman({ listStyle: "long" }) //=> '1 day, 5 hours, and 6 minutes'
   * dur.toHuman({ unitDisplay: "short" }) //=> '1 day, 5 hr, 6 min'
   * ```
   */
  toHuman(opts = {}) {
    if (!this.isValid) return INVALID;

    const l = orderedUnits
      .map((unit) => {
        const val = this.values[unit];
        if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.isUndefined)(val)) {
          return null;
        }
        return this.loc
          .numberFormatter({ style: "unit", unitDisplay: "long", ...opts, unit: unit.slice(0, -1) })
          .format(val);
      })
      .filter((n) => n);

    return this.loc
      .listFormatter({ type: "conjunction", style: opts.listStyle || "narrow", ...opts })
      .format(l);
  }

  /**
   * Returns a JavaScript object with this Duration's values.
   * @example Duration.fromObject({ years: 1, days: 6, seconds: 2 }).toObject() //=> { years: 1, days: 6, seconds: 2 }
   * @return {Object}
   */
  toObject() {
    if (!this.isValid) return {};
    return { ...this.values };
  }

  /**
   * Returns an ISO 8601-compliant string representation of this Duration.
   * @see https://en.wikipedia.org/wiki/ISO_8601#Durations
   * @example Duration.fromObject({ years: 3, seconds: 45 }).toISO() //=> 'P3YT45S'
   * @example Duration.fromObject({ months: 4, seconds: 45 }).toISO() //=> 'P4MT45S'
   * @example Duration.fromObject({ months: 5 }).toISO() //=> 'P5M'
   * @example Duration.fromObject({ minutes: 5 }).toISO() //=> 'PT5M'
   * @example Duration.fromObject({ milliseconds: 6 }).toISO() //=> 'PT0.006S'
   * @return {string}
   */
  toISO() {
    // we could use the formatter, but this is an easier way to get the minimum string
    if (!this.isValid) return null;

    let s = "P";
    if (this.years !== 0) s += this.years + "Y";
    if (this.months !== 0 || this.quarters !== 0) s += this.months + this.quarters * 3 + "M";
    if (this.weeks !== 0) s += this.weeks + "W";
    if (this.days !== 0) s += this.days + "D";
    if (this.hours !== 0 || this.minutes !== 0 || this.seconds !== 0 || this.milliseconds !== 0)
      s += "T";
    if (this.hours !== 0) s += this.hours + "H";
    if (this.minutes !== 0) s += this.minutes + "M";
    if (this.seconds !== 0 || this.milliseconds !== 0)
      // this will handle "floating point madness" by removing extra decimal places
      // https://stackoverflow.com/questions/588004/is-floating-point-math-broken
      s += (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.roundTo)(this.seconds + this.milliseconds / 1000, 3) + "S";
    if (s === "P") s += "T0S";
    return s;
  }

  /**
   * Returns an ISO 8601-compliant string representation of this Duration, formatted as a time of day.
   * Note that this will return null if the duration is invalid, negative, or equal to or greater than 24 hours.
   * @see https://en.wikipedia.org/wiki/ISO_8601#Times
   * @param {Object} opts - options
   * @param {boolean} [opts.suppressMilliseconds=false] - exclude milliseconds from the format if they're 0
   * @param {boolean} [opts.suppressSeconds=false] - exclude seconds from the format if they're 0
   * @param {boolean} [opts.includePrefix=false] - include the `T` prefix
   * @param {string} [opts.format='extended'] - choose between the basic and extended format
   * @example Duration.fromObject({ hours: 11 }).toISOTime() //=> '11:00:00.000'
   * @example Duration.fromObject({ hours: 11 }).toISOTime({ suppressMilliseconds: true }) //=> '11:00:00'
   * @example Duration.fromObject({ hours: 11 }).toISOTime({ suppressSeconds: true }) //=> '11:00'
   * @example Duration.fromObject({ hours: 11 }).toISOTime({ includePrefix: true }) //=> 'T11:00:00.000'
   * @example Duration.fromObject({ hours: 11 }).toISOTime({ format: 'basic' }) //=> '110000.000'
   * @return {string}
   */
  toISOTime(opts = {}) {
    if (!this.isValid) return null;

    const millis = this.toMillis();
    if (millis < 0 || millis >= 86400000) return null;

    opts = {
      suppressMilliseconds: false,
      suppressSeconds: false,
      includePrefix: false,
      format: "extended",
      ...opts,
      includeOffset: false,
    };

    const dateTime = _datetime_js__WEBPACK_IMPORTED_MODULE_7__["default"].fromMillis(millis, { zone: "UTC" });
    return dateTime.toISOTime(opts);
  }

  /**
   * Returns an ISO 8601 representation of this Duration appropriate for use in JSON.
   * @return {string}
   */
  toJSON() {
    return this.toISO();
  }

  /**
   * Returns an ISO 8601 representation of this Duration appropriate for use in debugging.
   * @return {string}
   */
  toString() {
    return this.toISO();
  }

  /**
   * Returns a string representation of this Duration appropriate for the REPL.
   * @return {string}
   */
  [Symbol.for("nodejs.util.inspect.custom")]() {
    if (this.isValid) {
      return `Duration { values: ${JSON.stringify(this.values)} }`;
    } else {
      return `Duration { Invalid, reason: ${this.invalidReason} }`;
    }
  }

  /**
   * Returns an milliseconds value of this Duration.
   * @return {number}
   */
  toMillis() {
    if (!this.isValid) return NaN;

    return durationToMillis(this.matrix, this.values);
  }

  /**
   * Returns an milliseconds value of this Duration. Alias of {@link toMillis}
   * @return {number}
   */
  valueOf() {
    return this.toMillis();
  }

  /**
   * Make this Duration longer by the specified amount. Return a newly-constructed Duration.
   * @param {Duration|Object|number} duration - The amount to add. Either a Luxon Duration, a number of milliseconds, the object argument to Duration.fromObject()
   * @return {Duration}
   */
  plus(duration) {
    if (!this.isValid) return this;

    const dur = Duration.fromDurationLike(duration),
      result = {};

    for (const k of orderedUnits) {
      if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.hasOwnProperty)(dur.values, k) || (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.hasOwnProperty)(this.values, k)) {
        result[k] = dur.get(k) + this.get(k);
      }
    }

    return clone(this, { values: result }, true);
  }

  /**
   * Make this Duration shorter by the specified amount. Return a newly-constructed Duration.
   * @param {Duration|Object|number} duration - The amount to subtract. Either a Luxon Duration, a number of milliseconds, the object argument to Duration.fromObject()
   * @return {Duration}
   */
  minus(duration) {
    if (!this.isValid) return this;

    const dur = Duration.fromDurationLike(duration);
    return this.plus(dur.negate());
  }

  /**
   * Scale this Duration by the specified amount. Return a newly-constructed Duration.
   * @param {function} fn - The function to apply to each unit. Arity is 1 or 2: the value of the unit and, optionally, the unit name. Must return a number.
   * @example Duration.fromObject({ hours: 1, minutes: 30 }).mapUnits(x => x * 2) //=> { hours: 2, minutes: 60 }
   * @example Duration.fromObject({ hours: 1, minutes: 30 }).mapUnits((x, u) => u === "hours" ? x * 2 : x) //=> { hours: 2, minutes: 30 }
   * @return {Duration}
   */
  mapUnits(fn) {
    if (!this.isValid) return this;
    const result = {};
    for (const k of Object.keys(this.values)) {
      result[k] = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.asNumber)(fn(this.values[k], k));
    }
    return clone(this, { values: result }, true);
  }

  /**
   * Get the value of unit.
   * @param {string} unit - a unit such as 'minute' or 'day'
   * @example Duration.fromObject({years: 2, days: 3}).get('years') //=> 2
   * @example Duration.fromObject({years: 2, days: 3}).get('months') //=> 0
   * @example Duration.fromObject({years: 2, days: 3}).get('days') //=> 3
   * @return {number}
   */
  get(unit) {
    return this[Duration.normalizeUnit(unit)];
  }

  /**
   * "Set" the values of specified units. Return a newly-constructed Duration.
   * @param {Object} values - a mapping of units to numbers
   * @example dur.set({ years: 2017 })
   * @example dur.set({ hours: 8, minutes: 30 })
   * @return {Duration}
   */
  set(values) {
    if (!this.isValid) return this;

    const mixed = { ...this.values, ...(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.normalizeObject)(values, Duration.normalizeUnit) };
    return clone(this, { values: mixed });
  }

  /**
   * "Set" the locale and/or numberingSystem.  Returns a newly-constructed Duration.
   * @example dur.reconfigure({ locale: 'en-GB' })
   * @return {Duration}
   */
  reconfigure({ locale, numberingSystem, conversionAccuracy, matrix } = {}) {
    const loc = this.loc.clone({ locale, numberingSystem });
    const opts = { loc, matrix, conversionAccuracy };
    return clone(this, opts);
  }

  /**
   * Return the length of the duration in the specified unit.
   * @param {string} unit - a unit such as 'minutes' or 'days'
   * @example Duration.fromObject({years: 1}).as('days') //=> 365
   * @example Duration.fromObject({years: 1}).as('months') //=> 12
   * @example Duration.fromObject({hours: 60}).as('days') //=> 2.5
   * @return {number}
   */
  as(unit) {
    return this.isValid ? this.shiftTo(unit).get(unit) : NaN;
  }

  /**
   * Reduce this Duration to its canonical representation in its current units.
   * Assuming the overall value of the Duration is positive, this means:
   * - excessive values for lower-order units are converted to higher-order units (if possible, see first and second example)
   * - negative lower-order units are converted to higher order units (there must be such a higher order unit, otherwise
   *   the overall value would be negative, see third example)
   * - fractional values for higher-order units are converted to lower-order units (if possible, see fourth example)
   *
   * If the overall value is negative, the result of this method is equivalent to `this.negate().normalize().negate()`.
   * @example Duration.fromObject({ years: 2, days: 5000 }).normalize().toObject() //=> { years: 15, days: 255 }
   * @example Duration.fromObject({ days: 5000 }).normalize().toObject() //=> { days: 5000 }
   * @example Duration.fromObject({ hours: 12, minutes: -45 }).normalize().toObject() //=> { hours: 11, minutes: 15 }
   * @example Duration.fromObject({ years: 2.5, days: 0, hours: 0 }).normalize().toObject() //=> { years: 2, days: 182, hours: 12 }
   * @return {Duration}
   */
  normalize() {
    if (!this.isValid) return this;
    const vals = this.toObject();
    normalizeValues(this.matrix, vals);
    return clone(this, { values: vals }, true);
  }

  /**
   * Rescale units to its largest representation
   * @example Duration.fromObject({ milliseconds: 90000 }).rescale().toObject() //=> { minutes: 1, seconds: 30 }
   * @return {Duration}
   */
  rescale() {
    if (!this.isValid) return this;
    const vals = removeZeroes(this.normalize().shiftToAll().toObject());
    return clone(this, { values: vals }, true);
  }

  /**
   * Convert this Duration into its representation in a different set of units.
   * @example Duration.fromObject({ hours: 1, seconds: 30 }).shiftTo('minutes', 'milliseconds').toObject() //=> { minutes: 60, milliseconds: 30000 }
   * @return {Duration}
   */
  shiftTo(...units) {
    if (!this.isValid) return this;

    if (units.length === 0) {
      return this;
    }

    units = units.map((u) => Duration.normalizeUnit(u));

    const built = {},
      accumulated = {},
      vals = this.toObject();
    let lastUnit;

    for (const k of orderedUnits) {
      if (units.indexOf(k) >= 0) {
        lastUnit = k;

        let own = 0;

        // anything we haven't boiled down yet should get boiled to this unit
        for (const ak in accumulated) {
          own += this.matrix[ak][k] * accumulated[ak];
          accumulated[ak] = 0;
        }

        // plus anything that's already in this unit
        if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.isNumber)(vals[k])) {
          own += vals[k];
        }

        // only keep the integer part for now in the hopes of putting any decimal part
        // into a smaller unit later
        const i = Math.trunc(own);
        built[k] = i;
        accumulated[k] = (own * 1000 - i * 1000) / 1000;

        // otherwise, keep it in the wings to boil it later
      } else if ((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.isNumber)(vals[k])) {
        accumulated[k] = vals[k];
      }
    }

    // anything leftover becomes the decimal for the last unit
    // lastUnit must be defined since units is not empty
    for (const key in accumulated) {
      if (accumulated[key] !== 0) {
        built[lastUnit] +=
          key === lastUnit ? accumulated[key] : accumulated[key] / this.matrix[lastUnit][key];
      }
    }

    normalizeValues(this.matrix, built);
    return clone(this, { values: built }, true);
  }

  /**
   * Shift this Duration to all available units.
   * Same as shiftTo("years", "months", "weeks", "days", "hours", "minutes", "seconds", "milliseconds")
   * @return {Duration}
   */
  shiftToAll() {
    if (!this.isValid) return this;
    return this.shiftTo(
      "years",
      "months",
      "weeks",
      "days",
      "hours",
      "minutes",
      "seconds",
      "milliseconds"
    );
  }

  /**
   * Return the negative of this Duration.
   * @example Duration.fromObject({ hours: 1, seconds: 30 }).negate().toObject() //=> { hours: -1, seconds: -30 }
   * @return {Duration}
   */
  negate() {
    if (!this.isValid) return this;
    const negated = {};
    for (const k of Object.keys(this.values)) {
      negated[k] = this.values[k] === 0 ? 0 : -this.values[k];
    }
    return clone(this, { values: negated }, true);
  }

  /**
   * Get the years.
   * @type {number}
   */
  get years() {
    return this.isValid ? this.values.years || 0 : NaN;
  }

  /**
   * Get the quarters.
   * @type {number}
   */
  get quarters() {
    return this.isValid ? this.values.quarters || 0 : NaN;
  }

  /**
   * Get the months.
   * @type {number}
   */
  get months() {
    return this.isValid ? this.values.months || 0 : NaN;
  }

  /**
   * Get the weeks
   * @type {number}
   */
  get weeks() {
    return this.isValid ? this.values.weeks || 0 : NaN;
  }

  /**
   * Get the days.
   * @type {number}
   */
  get days() {
    return this.isValid ? this.values.days || 0 : NaN;
  }

  /**
   * Get the hours.
   * @type {number}
   */
  get hours() {
    return this.isValid ? this.values.hours || 0 : NaN;
  }

  /**
   * Get the minutes.
   * @type {number}
   */
  get minutes() {
    return this.isValid ? this.values.minutes || 0 : NaN;
  }

  /**
   * Get the seconds.
   * @return {number}
   */
  get seconds() {
    return this.isValid ? this.values.seconds || 0 : NaN;
  }

  /**
   * Get the milliseconds.
   * @return {number}
   */
  get milliseconds() {
    return this.isValid ? this.values.milliseconds || 0 : NaN;
  }

  /**
   * Returns whether the Duration is invalid. Invalid durations are returned by diff operations
   * on invalid DateTimes or Intervals.
   * @return {boolean}
   */
  get isValid() {
    return this.invalid === null;
  }

  /**
   * Returns an error code if this Duration became invalid, or null if the Duration is valid
   * @return {string}
   */
  get invalidReason() {
    return this.invalid ? this.invalid.reason : null;
  }

  /**
   * Returns an explanation of why this Duration became invalid, or null if the Duration is valid
   * @type {string}
   */
  get invalidExplanation() {
    return this.invalid ? this.invalid.explanation : null;
  }

  /**
   * Equality check
   * Two Durations are equal iff they have the same units and the same values for each unit.
   * @param {Duration} other
   * @return {boolean}
   */
  equals(other) {
    if (!this.isValid || !other.isValid) {
      return false;
    }

    if (!this.loc.equals(other.loc)) {
      return false;
    }

    function eq(v1, v2) {
      // Consider 0 and undefined as equal
      if (v1 === undefined || v1 === 0) return v2 === undefined || v2 === 0;
      return v1 === v2;
    }

    for (const u of orderedUnits) {
      if (!eq(this.values[u], other.values[u])) {
        return false;
      }
    }
    return true;
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/errors.js":
/*!******************************************!*\
  !*** ./node_modules/luxon/src/errors.js ***!
  \******************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   ConflictingSpecificationError: () => (/* binding */ ConflictingSpecificationError),
/* harmony export */   InvalidArgumentError: () => (/* binding */ InvalidArgumentError),
/* harmony export */   InvalidDateTimeError: () => (/* binding */ InvalidDateTimeError),
/* harmony export */   InvalidDurationError: () => (/* binding */ InvalidDurationError),
/* harmony export */   InvalidIntervalError: () => (/* binding */ InvalidIntervalError),
/* harmony export */   InvalidUnitError: () => (/* binding */ InvalidUnitError),
/* harmony export */   ZoneIsAbstractError: () => (/* binding */ ZoneIsAbstractError)
/* harmony export */ });
// these aren't really private, but nor are they really useful to document

/**
 * @private
 */
class LuxonError extends Error {}

/**
 * @private
 */
class InvalidDateTimeError extends LuxonError {
  constructor(reason) {
    super(`Invalid DateTime: ${reason.toMessage()}`);
  }
}

/**
 * @private
 */
class InvalidIntervalError extends LuxonError {
  constructor(reason) {
    super(`Invalid Interval: ${reason.toMessage()}`);
  }
}

/**
 * @private
 */
class InvalidDurationError extends LuxonError {
  constructor(reason) {
    super(`Invalid Duration: ${reason.toMessage()}`);
  }
}

/**
 * @private
 */
class ConflictingSpecificationError extends LuxonError {}

/**
 * @private
 */
class InvalidUnitError extends LuxonError {
  constructor(unit) {
    super(`Invalid unit ${unit}`);
  }
}

/**
 * @private
 */
class InvalidArgumentError extends LuxonError {}

/**
 * @private
 */
class ZoneIsAbstractError extends LuxonError {
  constructor() {
    super("Zone is an abstract class");
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/conversions.js":
/*!****************************************************!*\
  !*** ./node_modules/luxon/src/impl/conversions.js ***!
  \****************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   dayOfWeek: () => (/* binding */ dayOfWeek),
/* harmony export */   gregorianToOrdinal: () => (/* binding */ gregorianToOrdinal),
/* harmony export */   gregorianToWeek: () => (/* binding */ gregorianToWeek),
/* harmony export */   hasInvalidGregorianData: () => (/* binding */ hasInvalidGregorianData),
/* harmony export */   hasInvalidOrdinalData: () => (/* binding */ hasInvalidOrdinalData),
/* harmony export */   hasInvalidTimeData: () => (/* binding */ hasInvalidTimeData),
/* harmony export */   hasInvalidWeekData: () => (/* binding */ hasInvalidWeekData),
/* harmony export */   isoWeekdayToLocal: () => (/* binding */ isoWeekdayToLocal),
/* harmony export */   ordinalToGregorian: () => (/* binding */ ordinalToGregorian),
/* harmony export */   usesLocalWeekValues: () => (/* binding */ usesLocalWeekValues),
/* harmony export */   weekToGregorian: () => (/* binding */ weekToGregorian)
/* harmony export */ });
/* harmony import */ var _util_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _invalid_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./invalid.js */ "./node_modules/luxon/src/impl/invalid.js");
/* harmony import */ var _errors_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../errors.js */ "./node_modules/luxon/src/errors.js");




const nonLeapLadder = [0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334],
  leapLadder = [0, 31, 60, 91, 121, 152, 182, 213, 244, 274, 305, 335];

function unitOutOfRange(unit, value) {
  return new _invalid_js__WEBPACK_IMPORTED_MODULE_1__["default"](
    "unit out of range",
    `you specified ${value} (of type ${typeof value}) as a ${unit}, which is invalid`
  );
}

function dayOfWeek(year, month, day) {
  const d = new Date(Date.UTC(year, month - 1, day));

  if (year < 100 && year >= 0) {
    d.setUTCFullYear(d.getUTCFullYear() - 1900);
  }

  const js = d.getUTCDay();

  return js === 0 ? 7 : js;
}

function computeOrdinal(year, month, day) {
  return day + ((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isLeapYear)(year) ? leapLadder : nonLeapLadder)[month - 1];
}

function uncomputeOrdinal(year, ordinal) {
  const table = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isLeapYear)(year) ? leapLadder : nonLeapLadder,
    month0 = table.findIndex((i) => i < ordinal),
    day = ordinal - table[month0];
  return { month: month0 + 1, day };
}

function isoWeekdayToLocal(isoWeekday, startOfWeek) {
  return ((isoWeekday - startOfWeek + 7) % 7) + 1;
}

/**
 * @private
 */

function gregorianToWeek(gregObj, minDaysInFirstWeek = 4, startOfWeek = 1) {
  const { year, month, day } = gregObj,
    ordinal = computeOrdinal(year, month, day),
    weekday = isoWeekdayToLocal(dayOfWeek(year, month, day), startOfWeek);

  let weekNumber = Math.floor((ordinal - weekday + 14 - minDaysInFirstWeek) / 7),
    weekYear;

  if (weekNumber < 1) {
    weekYear = year - 1;
    weekNumber = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.weeksInWeekYear)(weekYear, minDaysInFirstWeek, startOfWeek);
  } else if (weekNumber > (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.weeksInWeekYear)(year, minDaysInFirstWeek, startOfWeek)) {
    weekYear = year + 1;
    weekNumber = 1;
  } else {
    weekYear = year;
  }

  return { weekYear, weekNumber, weekday, ...(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.timeObject)(gregObj) };
}

function weekToGregorian(weekData, minDaysInFirstWeek = 4, startOfWeek = 1) {
  const { weekYear, weekNumber, weekday } = weekData,
    weekdayOfJan4 = isoWeekdayToLocal(dayOfWeek(weekYear, 1, minDaysInFirstWeek), startOfWeek),
    yearInDays = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.daysInYear)(weekYear);

  let ordinal = weekNumber * 7 + weekday - weekdayOfJan4 - 7 + minDaysInFirstWeek,
    year;

  if (ordinal < 1) {
    year = weekYear - 1;
    ordinal += (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.daysInYear)(year);
  } else if (ordinal > yearInDays) {
    year = weekYear + 1;
    ordinal -= (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.daysInYear)(weekYear);
  } else {
    year = weekYear;
  }

  const { month, day } = uncomputeOrdinal(year, ordinal);
  return { year, month, day, ...(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.timeObject)(weekData) };
}

function gregorianToOrdinal(gregData) {
  const { year, month, day } = gregData;
  const ordinal = computeOrdinal(year, month, day);
  return { year, ordinal, ...(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.timeObject)(gregData) };
}

function ordinalToGregorian(ordinalData) {
  const { year, ordinal } = ordinalData;
  const { month, day } = uncomputeOrdinal(year, ordinal);
  return { year, month, day, ...(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.timeObject)(ordinalData) };
}

/**
 * Check if local week units like localWeekday are used in obj.
 * If so, validates that they are not mixed with ISO week units and then copies them to the normal week unit properties.
 * Modifies obj in-place!
 * @param obj the object values
 */
function usesLocalWeekValues(obj, loc) {
  const hasLocaleWeekData =
    !(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.localWeekday) ||
    !(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.localWeekNumber) ||
    !(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.localWeekYear);
  if (hasLocaleWeekData) {
    const hasIsoWeekData =
      !(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.weekday) || !(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.weekNumber) || !(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.weekYear);

    if (hasIsoWeekData) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_2__.ConflictingSpecificationError(
        "Cannot mix locale-based week fields with ISO-based week fields"
      );
    }
    if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.localWeekday)) obj.weekday = obj.localWeekday;
    if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.localWeekNumber)) obj.weekNumber = obj.localWeekNumber;
    if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(obj.localWeekYear)) obj.weekYear = obj.localWeekYear;
    delete obj.localWeekday;
    delete obj.localWeekNumber;
    delete obj.localWeekYear;
    return {
      minDaysInFirstWeek: loc.getMinDaysInFirstWeek(),
      startOfWeek: loc.getStartOfWeek(),
    };
  } else {
    return { minDaysInFirstWeek: 4, startOfWeek: 1 };
  }
}

function hasInvalidWeekData(obj, minDaysInFirstWeek = 4, startOfWeek = 1) {
  const validYear = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isInteger)(obj.weekYear),
    validWeek = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(
      obj.weekNumber,
      1,
      (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.weeksInWeekYear)(obj.weekYear, minDaysInFirstWeek, startOfWeek)
    ),
    validWeekday = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(obj.weekday, 1, 7);

  if (!validYear) {
    return unitOutOfRange("weekYear", obj.weekYear);
  } else if (!validWeek) {
    return unitOutOfRange("week", obj.weekNumber);
  } else if (!validWeekday) {
    return unitOutOfRange("weekday", obj.weekday);
  } else return false;
}

function hasInvalidOrdinalData(obj) {
  const validYear = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isInteger)(obj.year),
    validOrdinal = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(obj.ordinal, 1, (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.daysInYear)(obj.year));

  if (!validYear) {
    return unitOutOfRange("year", obj.year);
  } else if (!validOrdinal) {
    return unitOutOfRange("ordinal", obj.ordinal);
  } else return false;
}

function hasInvalidGregorianData(obj) {
  const validYear = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isInteger)(obj.year),
    validMonth = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(obj.month, 1, 12),
    validDay = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(obj.day, 1, (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.daysInMonth)(obj.year, obj.month));

  if (!validYear) {
    return unitOutOfRange("year", obj.year);
  } else if (!validMonth) {
    return unitOutOfRange("month", obj.month);
  } else if (!validDay) {
    return unitOutOfRange("day", obj.day);
  } else return false;
}

function hasInvalidTimeData(obj) {
  const { hour, minute, second, millisecond } = obj;
  const validHour =
      (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(hour, 0, 23) ||
      (hour === 24 && minute === 0 && second === 0 && millisecond === 0),
    validMinute = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(minute, 0, 59),
    validSecond = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(second, 0, 59),
    validMillisecond = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.integerBetween)(millisecond, 0, 999);

  if (!validHour) {
    return unitOutOfRange("hour", hour);
  } else if (!validMinute) {
    return unitOutOfRange("minute", minute);
  } else if (!validSecond) {
    return unitOutOfRange("second", second);
  } else if (!validMillisecond) {
    return unitOutOfRange("millisecond", millisecond);
  } else return false;
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/diff.js":
/*!*********************************************!*\
  !*** ./node_modules/luxon/src/impl/diff.js ***!
  \*********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* export default binding */ __WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _duration_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../duration.js */ "./node_modules/luxon/src/duration.js");


function dayDiff(earlier, later) {
  const utcDayStart = (dt) => dt.toUTC(0, { keepLocalTime: true }).startOf("day").valueOf(),
    ms = utcDayStart(later) - utcDayStart(earlier);
  return Math.floor(_duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromMillis(ms).as("days"));
}

function highOrderDiffs(cursor, later, units) {
  const differs = [
    ["years", (a, b) => b.year - a.year],
    ["quarters", (a, b) => b.quarter - a.quarter + (b.year - a.year) * 4],
    ["months", (a, b) => b.month - a.month + (b.year - a.year) * 12],
    [
      "weeks",
      (a, b) => {
        const days = dayDiff(a, b);
        return (days - (days % 7)) / 7;
      },
    ],
    ["days", dayDiff],
  ];

  const results = {};
  const earlier = cursor;
  let lowestOrder, highWater;

  /* This loop tries to diff using larger units first.
     If we overshoot, we backtrack and try the next smaller unit.
     "cursor" starts out at the earlier timestamp and moves closer and closer to "later"
     as we use smaller and smaller units.
     highWater keeps track of where we would be if we added one more of the smallest unit,
     this is used later to potentially convert any difference smaller than the smallest higher order unit
     into a fraction of that smallest higher order unit
  */
  for (const [unit, differ] of differs) {
    if (units.indexOf(unit) >= 0) {
      lowestOrder = unit;

      results[unit] = differ(cursor, later);
      highWater = earlier.plus(results);

      if (highWater > later) {
        // we overshot the end point, backtrack cursor by 1
        results[unit]--;
        cursor = earlier.plus(results);

        // if we are still overshooting now, we need to backtrack again
        // this happens in certain situations when diffing times in different zones,
        // because this calculation ignores time zones
        if (cursor > later) {
          // keep the "overshot by 1" around as highWater
          highWater = cursor;
          // backtrack cursor by 1
          results[unit]--;
          cursor = earlier.plus(results);
        }
      } else {
        cursor = highWater;
      }
    }
  }

  return [cursor, results, highWater, lowestOrder];
}

/* harmony default export */ function __WEBPACK_DEFAULT_EXPORT__(earlier, later, units, opts) {
  let [cursor, results, highWater, lowestOrder] = highOrderDiffs(earlier, later, units);

  const remainingMillis = later - cursor;

  const lowerOrderUnits = units.filter(
    (u) => ["hours", "minutes", "seconds", "milliseconds"].indexOf(u) >= 0
  );

  if (lowerOrderUnits.length === 0) {
    if (highWater < later) {
      highWater = cursor.plus({ [lowestOrder]: 1 });
    }

    if (highWater !== cursor) {
      results[lowestOrder] = (results[lowestOrder] || 0) + remainingMillis / (highWater - cursor);
    }
  }

  const duration = _duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromObject(results, opts);

  if (lowerOrderUnits.length > 0) {
    return _duration_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromMillis(remainingMillis, opts)
      .shiftTo(...lowerOrderUnits)
      .plus(duration);
  } else {
    return duration;
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/digits.js":
/*!***********************************************!*\
  !*** ./node_modules/luxon/src/impl/digits.js ***!
  \***********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   digitRegex: () => (/* binding */ digitRegex),
/* harmony export */   parseDigits: () => (/* binding */ parseDigits),
/* harmony export */   resetDigitRegexCache: () => (/* binding */ resetDigitRegexCache)
/* harmony export */ });
const numberingSystems = {
  arab: "[\u0660-\u0669]",
  arabext: "[\u06F0-\u06F9]",
  bali: "[\u1B50-\u1B59]",
  beng: "[\u09E6-\u09EF]",
  deva: "[\u0966-\u096F]",
  fullwide: "[\uFF10-\uFF19]",
  gujr: "[\u0AE6-\u0AEF]",
  hanidec: "[〇|一|二|三|四|五|六|七|八|九]",
  khmr: "[\u17E0-\u17E9]",
  knda: "[\u0CE6-\u0CEF]",
  laoo: "[\u0ED0-\u0ED9]",
  limb: "[\u1946-\u194F]",
  mlym: "[\u0D66-\u0D6F]",
  mong: "[\u1810-\u1819]",
  mymr: "[\u1040-\u1049]",
  orya: "[\u0B66-\u0B6F]",
  tamldec: "[\u0BE6-\u0BEF]",
  telu: "[\u0C66-\u0C6F]",
  thai: "[\u0E50-\u0E59]",
  tibt: "[\u0F20-\u0F29]",
  latn: "\\d",
};

const numberingSystemsUTF16 = {
  arab: [1632, 1641],
  arabext: [1776, 1785],
  bali: [6992, 7001],
  beng: [2534, 2543],
  deva: [2406, 2415],
  fullwide: [65296, 65303],
  gujr: [2790, 2799],
  khmr: [6112, 6121],
  knda: [3302, 3311],
  laoo: [3792, 3801],
  limb: [6470, 6479],
  mlym: [3430, 3439],
  mong: [6160, 6169],
  mymr: [4160, 4169],
  orya: [2918, 2927],
  tamldec: [3046, 3055],
  telu: [3174, 3183],
  thai: [3664, 3673],
  tibt: [3872, 3881],
};

const hanidecChars = numberingSystems.hanidec.replace(/[\[|\]]/g, "").split("");

function parseDigits(str) {
  let value = parseInt(str, 10);
  if (isNaN(value)) {
    value = "";
    for (let i = 0; i < str.length; i++) {
      const code = str.charCodeAt(i);

      if (str[i].search(numberingSystems.hanidec) !== -1) {
        value += hanidecChars.indexOf(str[i]);
      } else {
        for (const key in numberingSystemsUTF16) {
          const [min, max] = numberingSystemsUTF16[key];
          if (code >= min && code <= max) {
            value += code - min;
          }
        }
      }
    }
    return parseInt(value, 10);
  } else {
    return value;
  }
}

// cache of {numberingSystem: {append: regex}}
let digitRegexCache = {};
function resetDigitRegexCache() {
  digitRegexCache = {};
}

function digitRegex({ numberingSystem }, append = "") {
  const ns = numberingSystem || "latn";

  if (!digitRegexCache[ns]) {
    digitRegexCache[ns] = {};
  }
  if (!digitRegexCache[ns][append]) {
    digitRegexCache[ns][append] = new RegExp(`${numberingSystems[ns]}${append}`);
  }

  return digitRegexCache[ns][append];
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/english.js":
/*!************************************************!*\
  !*** ./node_modules/luxon/src/impl/english.js ***!
  \************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   eraForDateTime: () => (/* binding */ eraForDateTime),
/* harmony export */   eras: () => (/* binding */ eras),
/* harmony export */   erasLong: () => (/* binding */ erasLong),
/* harmony export */   erasNarrow: () => (/* binding */ erasNarrow),
/* harmony export */   erasShort: () => (/* binding */ erasShort),
/* harmony export */   formatRelativeTime: () => (/* binding */ formatRelativeTime),
/* harmony export */   formatString: () => (/* binding */ formatString),
/* harmony export */   meridiemForDateTime: () => (/* binding */ meridiemForDateTime),
/* harmony export */   meridiems: () => (/* binding */ meridiems),
/* harmony export */   monthForDateTime: () => (/* binding */ monthForDateTime),
/* harmony export */   months: () => (/* binding */ months),
/* harmony export */   monthsLong: () => (/* binding */ monthsLong),
/* harmony export */   monthsNarrow: () => (/* binding */ monthsNarrow),
/* harmony export */   monthsShort: () => (/* binding */ monthsShort),
/* harmony export */   weekdayForDateTime: () => (/* binding */ weekdayForDateTime),
/* harmony export */   weekdays: () => (/* binding */ weekdays),
/* harmony export */   weekdaysLong: () => (/* binding */ weekdaysLong),
/* harmony export */   weekdaysNarrow: () => (/* binding */ weekdaysNarrow),
/* harmony export */   weekdaysShort: () => (/* binding */ weekdaysShort)
/* harmony export */ });
/* harmony import */ var _formats_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./formats.js */ "./node_modules/luxon/src/impl/formats.js");
/* harmony import */ var _util_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./util.js */ "./node_modules/luxon/src/impl/util.js");



function stringify(obj) {
  return JSON.stringify(obj, Object.keys(obj).sort());
}

/**
 * @private
 */

const monthsLong = [
  "January",
  "February",
  "March",
  "April",
  "May",
  "June",
  "July",
  "August",
  "September",
  "October",
  "November",
  "December",
];

const monthsShort = [
  "Jan",
  "Feb",
  "Mar",
  "Apr",
  "May",
  "Jun",
  "Jul",
  "Aug",
  "Sep",
  "Oct",
  "Nov",
  "Dec",
];

const monthsNarrow = ["J", "F", "M", "A", "M", "J", "J", "A", "S", "O", "N", "D"];

function months(length) {
  switch (length) {
    case "narrow":
      return [...monthsNarrow];
    case "short":
      return [...monthsShort];
    case "long":
      return [...monthsLong];
    case "numeric":
      return ["1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12"];
    case "2-digit":
      return ["01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12"];
    default:
      return null;
  }
}

const weekdaysLong = [
  "Monday",
  "Tuesday",
  "Wednesday",
  "Thursday",
  "Friday",
  "Saturday",
  "Sunday",
];

const weekdaysShort = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];

const weekdaysNarrow = ["M", "T", "W", "T", "F", "S", "S"];

function weekdays(length) {
  switch (length) {
    case "narrow":
      return [...weekdaysNarrow];
    case "short":
      return [...weekdaysShort];
    case "long":
      return [...weekdaysLong];
    case "numeric":
      return ["1", "2", "3", "4", "5", "6", "7"];
    default:
      return null;
  }
}

const meridiems = ["AM", "PM"];

const erasLong = ["Before Christ", "Anno Domini"];

const erasShort = ["BC", "AD"];

const erasNarrow = ["B", "A"];

function eras(length) {
  switch (length) {
    case "narrow":
      return [...erasNarrow];
    case "short":
      return [...erasShort];
    case "long":
      return [...erasLong];
    default:
      return null;
  }
}

function meridiemForDateTime(dt) {
  return meridiems[dt.hour < 12 ? 0 : 1];
}

function weekdayForDateTime(dt, length) {
  return weekdays(length)[dt.weekday - 1];
}

function monthForDateTime(dt, length) {
  return months(length)[dt.month - 1];
}

function eraForDateTime(dt, length) {
  return eras(length)[dt.year < 0 ? 0 : 1];
}

function formatRelativeTime(unit, count, numeric = "always", narrow = false) {
  const units = {
    years: ["year", "yr."],
    quarters: ["quarter", "qtr."],
    months: ["month", "mo."],
    weeks: ["week", "wk."],
    days: ["day", "day", "days"],
    hours: ["hour", "hr."],
    minutes: ["minute", "min."],
    seconds: ["second", "sec."],
  };

  const lastable = ["hours", "minutes", "seconds"].indexOf(unit) === -1;

  if (numeric === "auto" && lastable) {
    const isDay = unit === "days";
    switch (count) {
      case 1:
        return isDay ? "tomorrow" : `next ${units[unit][0]}`;
      case -1:
        return isDay ? "yesterday" : `last ${units[unit][0]}`;
      case 0:
        return isDay ? "today" : `this ${units[unit][0]}`;
      default: // fall through
    }
  }

  const isInPast = Object.is(count, -0) || count < 0,
    fmtValue = Math.abs(count),
    singular = fmtValue === 1,
    lilUnits = units[unit],
    fmtUnit = narrow
      ? singular
        ? lilUnits[1]
        : lilUnits[2] || lilUnits[1]
      : singular
      ? units[unit][0]
      : unit;
  return isInPast ? `${fmtValue} ${fmtUnit} ago` : `in ${fmtValue} ${fmtUnit}`;
}

function formatString(knownFormat) {
  // these all have the offsets removed because we don't have access to them
  // without all the intl stuff this is backfilling
  const filtered = (0,_util_js__WEBPACK_IMPORTED_MODULE_1__.pick)(knownFormat, [
      "weekday",
      "era",
      "year",
      "month",
      "day",
      "hour",
      "minute",
      "second",
      "timeZoneName",
      "hourCycle",
    ]),
    key = stringify(filtered),
    dateTimeHuge = "EEEE, LLLL d, yyyy, h:mm a";
  switch (key) {
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATE_SHORT):
      return "M/d/yyyy";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATE_MED):
      return "LLL d, yyyy";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATE_MED_WITH_WEEKDAY):
      return "EEE, LLL d, yyyy";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATE_FULL):
      return "LLLL d, yyyy";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATE_HUGE):
      return "EEEE, LLLL d, yyyy";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_SIMPLE):
      return "h:mm a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_WITH_SECONDS):
      return "h:mm:ss a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_WITH_SHORT_OFFSET):
      return "h:mm a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_WITH_LONG_OFFSET):
      return "h:mm a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_24_SIMPLE):
      return "HH:mm";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_24_WITH_SECONDS):
      return "HH:mm:ss";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_24_WITH_SHORT_OFFSET):
      return "HH:mm";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.TIME_24_WITH_LONG_OFFSET):
      return "HH:mm";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_SHORT):
      return "M/d/yyyy, h:mm a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_MED):
      return "LLL d, yyyy, h:mm a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_FULL):
      return "LLLL d, yyyy, h:mm a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_HUGE):
      return dateTimeHuge;
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_SHORT_WITH_SECONDS):
      return "M/d/yyyy, h:mm:ss a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_MED_WITH_SECONDS):
      return "LLL d, yyyy, h:mm:ss a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_MED_WITH_WEEKDAY):
      return "EEE, d LLL yyyy, h:mm a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_FULL_WITH_SECONDS):
      return "LLLL d, yyyy, h:mm:ss a";
    case stringify(_formats_js__WEBPACK_IMPORTED_MODULE_0__.DATETIME_HUGE_WITH_SECONDS):
      return "EEEE, LLLL d, yyyy, h:mm:ss a";
    default:
      return dateTimeHuge;
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/formats.js":
/*!************************************************!*\
  !*** ./node_modules/luxon/src/impl/formats.js ***!
  \************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DATETIME_FULL: () => (/* binding */ DATETIME_FULL),
/* harmony export */   DATETIME_FULL_WITH_SECONDS: () => (/* binding */ DATETIME_FULL_WITH_SECONDS),
/* harmony export */   DATETIME_HUGE: () => (/* binding */ DATETIME_HUGE),
/* harmony export */   DATETIME_HUGE_WITH_SECONDS: () => (/* binding */ DATETIME_HUGE_WITH_SECONDS),
/* harmony export */   DATETIME_MED: () => (/* binding */ DATETIME_MED),
/* harmony export */   DATETIME_MED_WITH_SECONDS: () => (/* binding */ DATETIME_MED_WITH_SECONDS),
/* harmony export */   DATETIME_MED_WITH_WEEKDAY: () => (/* binding */ DATETIME_MED_WITH_WEEKDAY),
/* harmony export */   DATETIME_SHORT: () => (/* binding */ DATETIME_SHORT),
/* harmony export */   DATETIME_SHORT_WITH_SECONDS: () => (/* binding */ DATETIME_SHORT_WITH_SECONDS),
/* harmony export */   DATE_FULL: () => (/* binding */ DATE_FULL),
/* harmony export */   DATE_HUGE: () => (/* binding */ DATE_HUGE),
/* harmony export */   DATE_MED: () => (/* binding */ DATE_MED),
/* harmony export */   DATE_MED_WITH_WEEKDAY: () => (/* binding */ DATE_MED_WITH_WEEKDAY),
/* harmony export */   DATE_SHORT: () => (/* binding */ DATE_SHORT),
/* harmony export */   TIME_24_SIMPLE: () => (/* binding */ TIME_24_SIMPLE),
/* harmony export */   TIME_24_WITH_LONG_OFFSET: () => (/* binding */ TIME_24_WITH_LONG_OFFSET),
/* harmony export */   TIME_24_WITH_SECONDS: () => (/* binding */ TIME_24_WITH_SECONDS),
/* harmony export */   TIME_24_WITH_SHORT_OFFSET: () => (/* binding */ TIME_24_WITH_SHORT_OFFSET),
/* harmony export */   TIME_SIMPLE: () => (/* binding */ TIME_SIMPLE),
/* harmony export */   TIME_WITH_LONG_OFFSET: () => (/* binding */ TIME_WITH_LONG_OFFSET),
/* harmony export */   TIME_WITH_SECONDS: () => (/* binding */ TIME_WITH_SECONDS),
/* harmony export */   TIME_WITH_SHORT_OFFSET: () => (/* binding */ TIME_WITH_SHORT_OFFSET)
/* harmony export */ });
/**
 * @private
 */

const n = "numeric",
  s = "short",
  l = "long";

const DATE_SHORT = {
  year: n,
  month: n,
  day: n,
};

const DATE_MED = {
  year: n,
  month: s,
  day: n,
};

const DATE_MED_WITH_WEEKDAY = {
  year: n,
  month: s,
  day: n,
  weekday: s,
};

const DATE_FULL = {
  year: n,
  month: l,
  day: n,
};

const DATE_HUGE = {
  year: n,
  month: l,
  day: n,
  weekday: l,
};

const TIME_SIMPLE = {
  hour: n,
  minute: n,
};

const TIME_WITH_SECONDS = {
  hour: n,
  minute: n,
  second: n,
};

const TIME_WITH_SHORT_OFFSET = {
  hour: n,
  minute: n,
  second: n,
  timeZoneName: s,
};

const TIME_WITH_LONG_OFFSET = {
  hour: n,
  minute: n,
  second: n,
  timeZoneName: l,
};

const TIME_24_SIMPLE = {
  hour: n,
  minute: n,
  hourCycle: "h23",
};

const TIME_24_WITH_SECONDS = {
  hour: n,
  minute: n,
  second: n,
  hourCycle: "h23",
};

const TIME_24_WITH_SHORT_OFFSET = {
  hour: n,
  minute: n,
  second: n,
  hourCycle: "h23",
  timeZoneName: s,
};

const TIME_24_WITH_LONG_OFFSET = {
  hour: n,
  minute: n,
  second: n,
  hourCycle: "h23",
  timeZoneName: l,
};

const DATETIME_SHORT = {
  year: n,
  month: n,
  day: n,
  hour: n,
  minute: n,
};

const DATETIME_SHORT_WITH_SECONDS = {
  year: n,
  month: n,
  day: n,
  hour: n,
  minute: n,
  second: n,
};

const DATETIME_MED = {
  year: n,
  month: s,
  day: n,
  hour: n,
  minute: n,
};

const DATETIME_MED_WITH_SECONDS = {
  year: n,
  month: s,
  day: n,
  hour: n,
  minute: n,
  second: n,
};

const DATETIME_MED_WITH_WEEKDAY = {
  year: n,
  month: s,
  day: n,
  weekday: s,
  hour: n,
  minute: n,
};

const DATETIME_FULL = {
  year: n,
  month: l,
  day: n,
  hour: n,
  minute: n,
  timeZoneName: s,
};

const DATETIME_FULL_WITH_SECONDS = {
  year: n,
  month: l,
  day: n,
  hour: n,
  minute: n,
  second: n,
  timeZoneName: s,
};

const DATETIME_HUGE = {
  year: n,
  month: l,
  day: n,
  weekday: l,
  hour: n,
  minute: n,
  timeZoneName: l,
};

const DATETIME_HUGE_WITH_SECONDS = {
  year: n,
  month: l,
  day: n,
  weekday: l,
  hour: n,
  minute: n,
  second: n,
  timeZoneName: l,
};


/***/ }),

/***/ "./node_modules/luxon/src/impl/formatter.js":
/*!**************************************************!*\
  !*** ./node_modules/luxon/src/impl/formatter.js ***!
  \**************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Formatter)
/* harmony export */ });
/* harmony import */ var _english_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./english.js */ "./node_modules/luxon/src/impl/english.js");
/* harmony import */ var _formats_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./formats.js */ "./node_modules/luxon/src/impl/formats.js");
/* harmony import */ var _util_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./util.js */ "./node_modules/luxon/src/impl/util.js");




function stringifyTokens(splits, tokenToString) {
  let s = "";
  for (const token of splits) {
    if (token.literal) {
      s += token.val;
    } else {
      s += tokenToString(token.val);
    }
  }
  return s;
}

const macroTokenToFormatOpts = {
  D: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATE_SHORT,
  DD: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATE_MED,
  DDD: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATE_FULL,
  DDDD: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATE_HUGE,
  t: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_SIMPLE,
  tt: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_WITH_SECONDS,
  ttt: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_WITH_SHORT_OFFSET,
  tttt: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_WITH_LONG_OFFSET,
  T: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_24_SIMPLE,
  TT: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_24_WITH_SECONDS,
  TTT: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_24_WITH_SHORT_OFFSET,
  TTTT: _formats_js__WEBPACK_IMPORTED_MODULE_1__.TIME_24_WITH_LONG_OFFSET,
  f: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_SHORT,
  ff: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_MED,
  fff: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_FULL,
  ffff: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_HUGE,
  F: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_SHORT_WITH_SECONDS,
  FF: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_MED_WITH_SECONDS,
  FFF: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_FULL_WITH_SECONDS,
  FFFF: _formats_js__WEBPACK_IMPORTED_MODULE_1__.DATETIME_HUGE_WITH_SECONDS,
};

/**
 * @private
 */

class Formatter {
  static create(locale, opts = {}) {
    return new Formatter(locale, opts);
  }

  static parseFormat(fmt) {
    // white-space is always considered a literal in user-provided formats
    // the " " token has a special meaning (see unitForToken)

    let current = null,
      currentFull = "",
      bracketed = false;
    const splits = [];
    for (let i = 0; i < fmt.length; i++) {
      const c = fmt.charAt(i);
      if (c === "'") {
        if (currentFull.length > 0) {
          splits.push({ literal: bracketed || /^\s+$/.test(currentFull), val: currentFull });
        }
        current = null;
        currentFull = "";
        bracketed = !bracketed;
      } else if (bracketed) {
        currentFull += c;
      } else if (c === current) {
        currentFull += c;
      } else {
        if (currentFull.length > 0) {
          splits.push({ literal: /^\s+$/.test(currentFull), val: currentFull });
        }
        currentFull = c;
        current = c;
      }
    }

    if (currentFull.length > 0) {
      splits.push({ literal: bracketed || /^\s+$/.test(currentFull), val: currentFull });
    }

    return splits;
  }

  static macroTokenToFormatOpts(token) {
    return macroTokenToFormatOpts[token];
  }

  constructor(locale, formatOpts) {
    this.opts = formatOpts;
    this.loc = locale;
    this.systemLoc = null;
  }

  formatWithSystemDefault(dt, opts) {
    if (this.systemLoc === null) {
      this.systemLoc = this.loc.redefaultToSystem();
    }
    const df = this.systemLoc.dtFormatter(dt, { ...this.opts, ...opts });
    return df.format();
  }

  dtFormatter(dt, opts = {}) {
    return this.loc.dtFormatter(dt, { ...this.opts, ...opts });
  }

  formatDateTime(dt, opts) {
    return this.dtFormatter(dt, opts).format();
  }

  formatDateTimeParts(dt, opts) {
    return this.dtFormatter(dt, opts).formatToParts();
  }

  formatInterval(interval, opts) {
    const df = this.dtFormatter(interval.start, opts);
    return df.dtf.formatRange(interval.start.toJSDate(), interval.end.toJSDate());
  }

  resolvedOptions(dt, opts) {
    return this.dtFormatter(dt, opts).resolvedOptions();
  }

  num(n, p = 0) {
    // we get some perf out of doing this here, annoyingly
    if (this.opts.forceSimple) {
      return (0,_util_js__WEBPACK_IMPORTED_MODULE_2__.padStart)(n, p);
    }

    const opts = { ...this.opts };

    if (p > 0) {
      opts.padTo = p;
    }

    return this.loc.numberFormatter(opts).format(n);
  }

  formatDateTimeFromString(dt, fmt) {
    const knownEnglish = this.loc.listingMode() === "en",
      useDateTimeFormatter = this.loc.outputCalendar && this.loc.outputCalendar !== "gregory",
      string = (opts, extract) => this.loc.extract(dt, opts, extract),
      formatOffset = (opts) => {
        if (dt.isOffsetFixed && dt.offset === 0 && opts.allowZ) {
          return "Z";
        }

        return dt.isValid ? dt.zone.formatOffset(dt.ts, opts.format) : "";
      },
      meridiem = () =>
        knownEnglish
          ? _english_js__WEBPACK_IMPORTED_MODULE_0__.meridiemForDateTime(dt)
          : string({ hour: "numeric", hourCycle: "h12" }, "dayperiod"),
      month = (length, standalone) =>
        knownEnglish
          ? _english_js__WEBPACK_IMPORTED_MODULE_0__.monthForDateTime(dt, length)
          : string(standalone ? { month: length } : { month: length, day: "numeric" }, "month"),
      weekday = (length, standalone) =>
        knownEnglish
          ? _english_js__WEBPACK_IMPORTED_MODULE_0__.weekdayForDateTime(dt, length)
          : string(
              standalone ? { weekday: length } : { weekday: length, month: "long", day: "numeric" },
              "weekday"
            ),
      maybeMacro = (token) => {
        const formatOpts = Formatter.macroTokenToFormatOpts(token);
        if (formatOpts) {
          return this.formatWithSystemDefault(dt, formatOpts);
        } else {
          return token;
        }
      },
      era = (length) =>
        knownEnglish ? _english_js__WEBPACK_IMPORTED_MODULE_0__.eraForDateTime(dt, length) : string({ era: length }, "era"),
      tokenToString = (token) => {
        // Where possible: https://cldr.unicode.org/translation/date-time/date-time-symbols
        switch (token) {
          // ms
          case "S":
            return this.num(dt.millisecond);
          case "u":
          // falls through
          case "SSS":
            return this.num(dt.millisecond, 3);
          // seconds
          case "s":
            return this.num(dt.second);
          case "ss":
            return this.num(dt.second, 2);
          // fractional seconds
          case "uu":
            return this.num(Math.floor(dt.millisecond / 10), 2);
          case "uuu":
            return this.num(Math.floor(dt.millisecond / 100));
          // minutes
          case "m":
            return this.num(dt.minute);
          case "mm":
            return this.num(dt.minute, 2);
          // hours
          case "h":
            return this.num(dt.hour % 12 === 0 ? 12 : dt.hour % 12);
          case "hh":
            return this.num(dt.hour % 12 === 0 ? 12 : dt.hour % 12, 2);
          case "H":
            return this.num(dt.hour);
          case "HH":
            return this.num(dt.hour, 2);
          // offset
          case "Z":
            // like +6
            return formatOffset({ format: "narrow", allowZ: this.opts.allowZ });
          case "ZZ":
            // like +06:00
            return formatOffset({ format: "short", allowZ: this.opts.allowZ });
          case "ZZZ":
            // like +0600
            return formatOffset({ format: "techie", allowZ: this.opts.allowZ });
          case "ZZZZ":
            // like EST
            return dt.zone.offsetName(dt.ts, { format: "short", locale: this.loc.locale });
          case "ZZZZZ":
            // like Eastern Standard Time
            return dt.zone.offsetName(dt.ts, { format: "long", locale: this.loc.locale });
          // zone
          case "z":
            // like America/New_York
            return dt.zoneName;
          // meridiems
          case "a":
            return meridiem();
          // dates
          case "d":
            return useDateTimeFormatter ? string({ day: "numeric" }, "day") : this.num(dt.day);
          case "dd":
            return useDateTimeFormatter ? string({ day: "2-digit" }, "day") : this.num(dt.day, 2);
          // weekdays - standalone
          case "c":
            // like 1
            return this.num(dt.weekday);
          case "ccc":
            // like 'Tues'
            return weekday("short", true);
          case "cccc":
            // like 'Tuesday'
            return weekday("long", true);
          case "ccccc":
            // like 'T'
            return weekday("narrow", true);
          // weekdays - format
          case "E":
            // like 1
            return this.num(dt.weekday);
          case "EEE":
            // like 'Tues'
            return weekday("short", false);
          case "EEEE":
            // like 'Tuesday'
            return weekday("long", false);
          case "EEEEE":
            // like 'T'
            return weekday("narrow", false);
          // months - standalone
          case "L":
            // like 1
            return useDateTimeFormatter
              ? string({ month: "numeric", day: "numeric" }, "month")
              : this.num(dt.month);
          case "LL":
            // like 01, doesn't seem to work
            return useDateTimeFormatter
              ? string({ month: "2-digit", day: "numeric" }, "month")
              : this.num(dt.month, 2);
          case "LLL":
            // like Jan
            return month("short", true);
          case "LLLL":
            // like January
            return month("long", true);
          case "LLLLL":
            // like J
            return month("narrow", true);
          // months - format
          case "M":
            // like 1
            return useDateTimeFormatter
              ? string({ month: "numeric" }, "month")
              : this.num(dt.month);
          case "MM":
            // like 01
            return useDateTimeFormatter
              ? string({ month: "2-digit" }, "month")
              : this.num(dt.month, 2);
          case "MMM":
            // like Jan
            return month("short", false);
          case "MMMM":
            // like January
            return month("long", false);
          case "MMMMM":
            // like J
            return month("narrow", false);
          // years
          case "y":
            // like 2014
            return useDateTimeFormatter ? string({ year: "numeric" }, "year") : this.num(dt.year);
          case "yy":
            // like 14
            return useDateTimeFormatter
              ? string({ year: "2-digit" }, "year")
              : this.num(dt.year.toString().slice(-2), 2);
          case "yyyy":
            // like 0012
            return useDateTimeFormatter
              ? string({ year: "numeric" }, "year")
              : this.num(dt.year, 4);
          case "yyyyyy":
            // like 000012
            return useDateTimeFormatter
              ? string({ year: "numeric" }, "year")
              : this.num(dt.year, 6);
          // eras
          case "G":
            // like AD
            return era("short");
          case "GG":
            // like Anno Domini
            return era("long");
          case "GGGGG":
            return era("narrow");
          case "kk":
            return this.num(dt.weekYear.toString().slice(-2), 2);
          case "kkkk":
            return this.num(dt.weekYear, 4);
          case "W":
            return this.num(dt.weekNumber);
          case "WW":
            return this.num(dt.weekNumber, 2);
          case "n":
            return this.num(dt.localWeekNumber);
          case "nn":
            return this.num(dt.localWeekNumber, 2);
          case "ii":
            return this.num(dt.localWeekYear.toString().slice(-2), 2);
          case "iiii":
            return this.num(dt.localWeekYear, 4);
          case "o":
            return this.num(dt.ordinal);
          case "ooo":
            return this.num(dt.ordinal, 3);
          case "q":
            // like 1
            return this.num(dt.quarter);
          case "qq":
            // like 01
            return this.num(dt.quarter, 2);
          case "X":
            return this.num(Math.floor(dt.ts / 1000));
          case "x":
            return this.num(dt.ts);
          default:
            return maybeMacro(token);
        }
      };

    return stringifyTokens(Formatter.parseFormat(fmt), tokenToString);
  }

  formatDurationFromString(dur, fmt) {
    const tokenToField = (token) => {
        switch (token[0]) {
          case "S":
            return "millisecond";
          case "s":
            return "second";
          case "m":
            return "minute";
          case "h":
            return "hour";
          case "d":
            return "day";
          case "w":
            return "week";
          case "M":
            return "month";
          case "y":
            return "year";
          default:
            return null;
        }
      },
      tokenToString = (lildur) => (token) => {
        const mapped = tokenToField(token);
        if (mapped) {
          return this.num(lildur.get(mapped), token.length);
        } else {
          return token;
        }
      },
      tokens = Formatter.parseFormat(fmt),
      realTokens = tokens.reduce(
        (found, { literal, val }) => (literal ? found : found.concat(val)),
        []
      ),
      collapsed = dur.shiftTo(...realTokens.map(tokenToField).filter((t) => t));
    return stringifyTokens(tokens, tokenToString(collapsed));
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/invalid.js":
/*!************************************************!*\
  !*** ./node_modules/luxon/src/impl/invalid.js ***!
  \************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Invalid)
/* harmony export */ });
class Invalid {
  constructor(reason, explanation) {
    this.reason = reason;
    this.explanation = explanation;
  }

  toMessage() {
    if (this.explanation) {
      return `${this.reason}: ${this.explanation}`;
    } else {
      return this.reason;
    }
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/locale.js":
/*!***********************************************!*\
  !*** ./node_modules/luxon/src/impl/locale.js ***!
  \***********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Locale)
/* harmony export */ });
/* harmony import */ var _util_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _english_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./english.js */ "./node_modules/luxon/src/impl/english.js");
/* harmony import */ var _settings_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../settings.js */ "./node_modules/luxon/src/settings.js");
/* harmony import */ var _datetime_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../datetime.js */ "./node_modules/luxon/src/datetime.js");
/* harmony import */ var _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../zones/IANAZone.js */ "./node_modules/luxon/src/zones/IANAZone.js");






// todo - remap caching

let intlLFCache = {};
function getCachedLF(locString, opts = {}) {
  const key = JSON.stringify([locString, opts]);
  let dtf = intlLFCache[key];
  if (!dtf) {
    dtf = new Intl.ListFormat(locString, opts);
    intlLFCache[key] = dtf;
  }
  return dtf;
}

let intlDTCache = {};
function getCachedDTF(locString, opts = {}) {
  const key = JSON.stringify([locString, opts]);
  let dtf = intlDTCache[key];
  if (!dtf) {
    dtf = new Intl.DateTimeFormat(locString, opts);
    intlDTCache[key] = dtf;
  }
  return dtf;
}

let intlNumCache = {};
function getCachedINF(locString, opts = {}) {
  const key = JSON.stringify([locString, opts]);
  let inf = intlNumCache[key];
  if (!inf) {
    inf = new Intl.NumberFormat(locString, opts);
    intlNumCache[key] = inf;
  }
  return inf;
}

let intlRelCache = {};
function getCachedRTF(locString, opts = {}) {
  const { base, ...cacheKeyOpts } = opts; // exclude `base` from the options
  const key = JSON.stringify([locString, cacheKeyOpts]);
  let inf = intlRelCache[key];
  if (!inf) {
    inf = new Intl.RelativeTimeFormat(locString, opts);
    intlRelCache[key] = inf;
  }
  return inf;
}

let sysLocaleCache = null;
function systemLocale() {
  if (sysLocaleCache) {
    return sysLocaleCache;
  } else {
    sysLocaleCache = new Intl.DateTimeFormat().resolvedOptions().locale;
    return sysLocaleCache;
  }
}

let weekInfoCache = {};
function getCachedWeekInfo(locString) {
  let data = weekInfoCache[locString];
  if (!data) {
    const locale = new Intl.Locale(locString);
    // browsers currently implement this as a property, but spec says it should be a getter function
    data = "getWeekInfo" in locale ? locale.getWeekInfo() : locale.weekInfo;
    weekInfoCache[locString] = data;
  }
  return data;
}

function parseLocaleString(localeStr) {
  // I really want to avoid writing a BCP 47 parser
  // see, e.g. https://github.com/wooorm/bcp-47
  // Instead, we'll do this:

  // a) if the string has no -u extensions, just leave it alone
  // b) if it does, use Intl to resolve everything
  // c) if Intl fails, try again without the -u

  // private subtags and unicode subtags have ordering requirements,
  // and we're not properly parsing this, so just strip out the
  // private ones if they exist.
  const xIndex = localeStr.indexOf("-x-");
  if (xIndex !== -1) {
    localeStr = localeStr.substring(0, xIndex);
  }

  const uIndex = localeStr.indexOf("-u-");
  if (uIndex === -1) {
    return [localeStr];
  } else {
    let options;
    let selectedStr;
    try {
      options = getCachedDTF(localeStr).resolvedOptions();
      selectedStr = localeStr;
    } catch (e) {
      const smaller = localeStr.substring(0, uIndex);
      options = getCachedDTF(smaller).resolvedOptions();
      selectedStr = smaller;
    }

    const { numberingSystem, calendar } = options;
    return [selectedStr, numberingSystem, calendar];
  }
}

function intlConfigString(localeStr, numberingSystem, outputCalendar) {
  if (outputCalendar || numberingSystem) {
    if (!localeStr.includes("-u-")) {
      localeStr += "-u";
    }

    if (outputCalendar) {
      localeStr += `-ca-${outputCalendar}`;
    }

    if (numberingSystem) {
      localeStr += `-nu-${numberingSystem}`;
    }
    return localeStr;
  } else {
    return localeStr;
  }
}

function mapMonths(f) {
  const ms = [];
  for (let i = 1; i <= 12; i++) {
    const dt = _datetime_js__WEBPACK_IMPORTED_MODULE_3__["default"].utc(2009, i, 1);
    ms.push(f(dt));
  }
  return ms;
}

function mapWeekdays(f) {
  const ms = [];
  for (let i = 1; i <= 7; i++) {
    const dt = _datetime_js__WEBPACK_IMPORTED_MODULE_3__["default"].utc(2016, 11, 13 + i);
    ms.push(f(dt));
  }
  return ms;
}

function listStuff(loc, length, englishFn, intlFn) {
  const mode = loc.listingMode();

  if (mode === "error") {
    return null;
  } else if (mode === "en") {
    return englishFn(length);
  } else {
    return intlFn(length);
  }
}

function supportsFastNumbers(loc) {
  if (loc.numberingSystem && loc.numberingSystem !== "latn") {
    return false;
  } else {
    return (
      loc.numberingSystem === "latn" ||
      !loc.locale ||
      loc.locale.startsWith("en") ||
      new Intl.DateTimeFormat(loc.intl).resolvedOptions().numberingSystem === "latn"
    );
  }
}

/**
 * @private
 */

class PolyNumberFormatter {
  constructor(intl, forceSimple, opts) {
    this.padTo = opts.padTo || 0;
    this.floor = opts.floor || false;

    const { padTo, floor, ...otherOpts } = opts;

    if (!forceSimple || Object.keys(otherOpts).length > 0) {
      const intlOpts = { useGrouping: false, ...opts };
      if (opts.padTo > 0) intlOpts.minimumIntegerDigits = opts.padTo;
      this.inf = getCachedINF(intl, intlOpts);
    }
  }

  format(i) {
    if (this.inf) {
      const fixed = this.floor ? Math.floor(i) : i;
      return this.inf.format(fixed);
    } else {
      // to match the browser's numberformatter defaults
      const fixed = this.floor ? Math.floor(i) : (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.roundTo)(i, 3);
      return (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.padStart)(fixed, this.padTo);
    }
  }
}

/**
 * @private
 */

class PolyDateFormatter {
  constructor(dt, intl, opts) {
    this.opts = opts;
    this.originalZone = undefined;

    let z = undefined;
    if (this.opts.timeZone) {
      // Don't apply any workarounds if a timeZone is explicitly provided in opts
      this.dt = dt;
    } else if (dt.zone.type === "fixed") {
      // UTC-8 or Etc/UTC-8 are not part of tzdata, only Etc/GMT+8 and the like.
      // That is why fixed-offset TZ is set to that unless it is:
      // 1. Representing offset 0 when UTC is used to maintain previous behavior and does not become GMT.
      // 2. Unsupported by the browser:
      //    - some do not support Etc/
      //    - < Etc/GMT-14, > Etc/GMT+12, and 30-minute or 45-minute offsets are not part of tzdata
      const gmtOffset = -1 * (dt.offset / 60);
      const offsetZ = gmtOffset >= 0 ? `Etc/GMT+${gmtOffset}` : `Etc/GMT${gmtOffset}`;
      if (dt.offset !== 0 && _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_4__["default"].create(offsetZ).valid) {
        z = offsetZ;
        this.dt = dt;
      } else {
        // Not all fixed-offset zones like Etc/+4:30 are present in tzdata so
        // we manually apply the offset and substitute the zone as needed.
        z = "UTC";
        this.dt = dt.offset === 0 ? dt : dt.setZone("UTC").plus({ minutes: dt.offset });
        this.originalZone = dt.zone;
      }
    } else if (dt.zone.type === "system") {
      this.dt = dt;
    } else if (dt.zone.type === "iana") {
      this.dt = dt;
      z = dt.zone.name;
    } else {
      // Custom zones can have any offset / offsetName so we just manually
      // apply the offset and substitute the zone as needed.
      z = "UTC";
      this.dt = dt.setZone("UTC").plus({ minutes: dt.offset });
      this.originalZone = dt.zone;
    }

    const intlOpts = { ...this.opts };
    intlOpts.timeZone = intlOpts.timeZone || z;
    this.dtf = getCachedDTF(intl, intlOpts);
  }

  format() {
    if (this.originalZone) {
      // If we have to substitute in the actual zone name, we have to use
      // formatToParts so that the timezone can be replaced.
      return this.formatToParts()
        .map(({ value }) => value)
        .join("");
    }
    return this.dtf.format(this.dt.toJSDate());
  }

  formatToParts() {
    const parts = this.dtf.formatToParts(this.dt.toJSDate());
    if (this.originalZone) {
      return parts.map((part) => {
        if (part.type === "timeZoneName") {
          const offsetName = this.originalZone.offsetName(this.dt.ts, {
            locale: this.dt.locale,
            format: this.opts.timeZoneName,
          });
          return {
            ...part,
            value: offsetName,
          };
        } else {
          return part;
        }
      });
    }
    return parts;
  }

  resolvedOptions() {
    return this.dtf.resolvedOptions();
  }
}

/**
 * @private
 */
class PolyRelFormatter {
  constructor(intl, isEnglish, opts) {
    this.opts = { style: "long", ...opts };
    if (!isEnglish && (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.hasRelative)()) {
      this.rtf = getCachedRTF(intl, opts);
    }
  }

  format(count, unit) {
    if (this.rtf) {
      return this.rtf.format(count, unit);
    } else {
      return _english_js__WEBPACK_IMPORTED_MODULE_1__.formatRelativeTime(unit, count, this.opts.numeric, this.opts.style !== "long");
    }
  }

  formatToParts(count, unit) {
    if (this.rtf) {
      return this.rtf.formatToParts(count, unit);
    } else {
      return [];
    }
  }
}

const fallbackWeekSettings = {
  firstDay: 1,
  minimalDays: 4,
  weekend: [6, 7],
};

/**
 * @private
 */

class Locale {
  static fromOpts(opts) {
    return Locale.create(
      opts.locale,
      opts.numberingSystem,
      opts.outputCalendar,
      opts.weekSettings,
      opts.defaultToEN
    );
  }

  static create(locale, numberingSystem, outputCalendar, weekSettings, defaultToEN = false) {
    const specifiedLocale = locale || _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultLocale;
    // the system locale is useful for human-readable strings but annoying for parsing/formatting known formats
    const localeR = specifiedLocale || (defaultToEN ? "en-US" : systemLocale());
    const numberingSystemR = numberingSystem || _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultNumberingSystem;
    const outputCalendarR = outputCalendar || _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultOutputCalendar;
    const weekSettingsR = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.validateWeekSettings)(weekSettings) || _settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].defaultWeekSettings;
    return new Locale(localeR, numberingSystemR, outputCalendarR, weekSettingsR, specifiedLocale);
  }

  static resetCache() {
    sysLocaleCache = null;
    intlDTCache = {};
    intlNumCache = {};
    intlRelCache = {};
  }

  static fromObject({ locale, numberingSystem, outputCalendar, weekSettings } = {}) {
    return Locale.create(locale, numberingSystem, outputCalendar, weekSettings);
  }

  constructor(locale, numbering, outputCalendar, weekSettings, specifiedLocale) {
    const [parsedLocale, parsedNumberingSystem, parsedOutputCalendar] = parseLocaleString(locale);

    this.locale = parsedLocale;
    this.numberingSystem = numbering || parsedNumberingSystem || null;
    this.outputCalendar = outputCalendar || parsedOutputCalendar || null;
    this.weekSettings = weekSettings;
    this.intl = intlConfigString(this.locale, this.numberingSystem, this.outputCalendar);

    this.weekdaysCache = { format: {}, standalone: {} };
    this.monthsCache = { format: {}, standalone: {} };
    this.meridiemCache = null;
    this.eraCache = {};

    this.specifiedLocale = specifiedLocale;
    this.fastNumbersCached = null;
  }

  get fastNumbers() {
    if (this.fastNumbersCached == null) {
      this.fastNumbersCached = supportsFastNumbers(this);
    }

    return this.fastNumbersCached;
  }

  listingMode() {
    const isActuallyEn = this.isEnglish();
    const hasNoWeirdness =
      (this.numberingSystem === null || this.numberingSystem === "latn") &&
      (this.outputCalendar === null || this.outputCalendar === "gregory");
    return isActuallyEn && hasNoWeirdness ? "en" : "intl";
  }

  clone(alts) {
    if (!alts || Object.getOwnPropertyNames(alts).length === 0) {
      return this;
    } else {
      return Locale.create(
        alts.locale || this.specifiedLocale,
        alts.numberingSystem || this.numberingSystem,
        alts.outputCalendar || this.outputCalendar,
        (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.validateWeekSettings)(alts.weekSettings) || this.weekSettings,
        alts.defaultToEN || false
      );
    }
  }

  redefaultToEN(alts = {}) {
    return this.clone({ ...alts, defaultToEN: true });
  }

  redefaultToSystem(alts = {}) {
    return this.clone({ ...alts, defaultToEN: false });
  }

  months(length, format = false) {
    return listStuff(this, length, _english_js__WEBPACK_IMPORTED_MODULE_1__.months, () => {
      const intl = format ? { month: length, day: "numeric" } : { month: length },
        formatStr = format ? "format" : "standalone";
      if (!this.monthsCache[formatStr][length]) {
        this.monthsCache[formatStr][length] = mapMonths((dt) => this.extract(dt, intl, "month"));
      }
      return this.monthsCache[formatStr][length];
    });
  }

  weekdays(length, format = false) {
    return listStuff(this, length, _english_js__WEBPACK_IMPORTED_MODULE_1__.weekdays, () => {
      const intl = format
          ? { weekday: length, year: "numeric", month: "long", day: "numeric" }
          : { weekday: length },
        formatStr = format ? "format" : "standalone";
      if (!this.weekdaysCache[formatStr][length]) {
        this.weekdaysCache[formatStr][length] = mapWeekdays((dt) =>
          this.extract(dt, intl, "weekday")
        );
      }
      return this.weekdaysCache[formatStr][length];
    });
  }

  meridiems() {
    return listStuff(
      this,
      undefined,
      () => _english_js__WEBPACK_IMPORTED_MODULE_1__.meridiems,
      () => {
        // In theory there could be aribitrary day periods. We're gonna assume there are exactly two
        // for AM and PM. This is probably wrong, but it's makes parsing way easier.
        if (!this.meridiemCache) {
          const intl = { hour: "numeric", hourCycle: "h12" };
          this.meridiemCache = [_datetime_js__WEBPACK_IMPORTED_MODULE_3__["default"].utc(2016, 11, 13, 9), _datetime_js__WEBPACK_IMPORTED_MODULE_3__["default"].utc(2016, 11, 13, 19)].map(
            (dt) => this.extract(dt, intl, "dayperiod")
          );
        }

        return this.meridiemCache;
      }
    );
  }

  eras(length) {
    return listStuff(this, length, _english_js__WEBPACK_IMPORTED_MODULE_1__.eras, () => {
      const intl = { era: length };

      // This is problematic. Different calendars are going to define eras totally differently. What I need is the minimum set of dates
      // to definitely enumerate them.
      if (!this.eraCache[length]) {
        this.eraCache[length] = [_datetime_js__WEBPACK_IMPORTED_MODULE_3__["default"].utc(-40, 1, 1), _datetime_js__WEBPACK_IMPORTED_MODULE_3__["default"].utc(2017, 1, 1)].map((dt) =>
          this.extract(dt, intl, "era")
        );
      }

      return this.eraCache[length];
    });
  }

  extract(dt, intlOpts, field) {
    const df = this.dtFormatter(dt, intlOpts),
      results = df.formatToParts(),
      matching = results.find((m) => m.type.toLowerCase() === field);
    return matching ? matching.value : null;
  }

  numberFormatter(opts = {}) {
    // this forcesimple option is never used (the only caller short-circuits on it, but it seems safer to leave)
    // (in contrast, the rest of the condition is used heavily)
    return new PolyNumberFormatter(this.intl, opts.forceSimple || this.fastNumbers, opts);
  }

  dtFormatter(dt, intlOpts = {}) {
    return new PolyDateFormatter(dt, this.intl, intlOpts);
  }

  relFormatter(opts = {}) {
    return new PolyRelFormatter(this.intl, this.isEnglish(), opts);
  }

  listFormatter(opts = {}) {
    return getCachedLF(this.intl, opts);
  }

  isEnglish() {
    return (
      this.locale === "en" ||
      this.locale.toLowerCase() === "en-us" ||
      new Intl.DateTimeFormat(this.intl).resolvedOptions().locale.startsWith("en-us")
    );
  }

  getWeekSettings() {
    if (this.weekSettings) {
      return this.weekSettings;
    } else if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.hasLocaleWeekInfo)()) {
      return fallbackWeekSettings;
    } else {
      return getCachedWeekInfo(this.locale);
    }
  }

  getStartOfWeek() {
    return this.getWeekSettings().firstDay;
  }

  getMinDaysInFirstWeek() {
    return this.getWeekSettings().minimalDays;
  }

  getWeekendDays() {
    return this.getWeekSettings().weekend;
  }

  equals(other) {
    return (
      this.locale === other.locale &&
      this.numberingSystem === other.numberingSystem &&
      this.outputCalendar === other.outputCalendar
    );
  }

  toString() {
    return `Locale(${this.locale}, ${this.numberingSystem}, ${this.outputCalendar})`;
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/regexParser.js":
/*!****************************************************!*\
  !*** ./node_modules/luxon/src/impl/regexParser.js ***!
  \****************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   parseHTTPDate: () => (/* binding */ parseHTTPDate),
/* harmony export */   parseISODate: () => (/* binding */ parseISODate),
/* harmony export */   parseISODuration: () => (/* binding */ parseISODuration),
/* harmony export */   parseISOTimeOnly: () => (/* binding */ parseISOTimeOnly),
/* harmony export */   parseRFC2822Date: () => (/* binding */ parseRFC2822Date),
/* harmony export */   parseSQL: () => (/* binding */ parseSQL)
/* harmony export */ });
/* harmony import */ var _util_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _english_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./english.js */ "./node_modules/luxon/src/impl/english.js");
/* harmony import */ var _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../zones/fixedOffsetZone.js */ "./node_modules/luxon/src/zones/fixedOffsetZone.js");
/* harmony import */ var _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../zones/IANAZone.js */ "./node_modules/luxon/src/zones/IANAZone.js");





/*
 * This file handles parsing for well-specified formats. Here's how it works:
 * Two things go into parsing: a regex to match with and an extractor to take apart the groups in the match.
 * An extractor is just a function that takes a regex match array and returns a { year: ..., month: ... } object
 * parse() does the work of executing the regex and applying the extractor. It takes multiple regex/extractor pairs to try in sequence.
 * Extractors can take a "cursor" representing the offset in the match to look at. This makes it easy to combine extractors.
 * combineExtractors() does the work of combining them, keeping track of the cursor through multiple extractions.
 * Some extractions are super dumb and simpleParse and fromStrings help DRY them.
 */

const ianaRegex = /[A-Za-z_+-]{1,256}(?::?\/[A-Za-z0-9_+-]{1,256}(?:\/[A-Za-z0-9_+-]{1,256})?)?/;

function combineRegexes(...regexes) {
  const full = regexes.reduce((f, r) => f + r.source, "");
  return RegExp(`^${full}$`);
}

function combineExtractors(...extractors) {
  return (m) =>
    extractors
      .reduce(
        ([mergedVals, mergedZone, cursor], ex) => {
          const [val, zone, next] = ex(m, cursor);
          return [{ ...mergedVals, ...val }, zone || mergedZone, next];
        },
        [{}, null, 1]
      )
      .slice(0, 2);
}

function parse(s, ...patterns) {
  if (s == null) {
    return [null, null];
  }

  for (const [regex, extractor] of patterns) {
    const m = regex.exec(s);
    if (m) {
      return extractor(m);
    }
  }
  return [null, null];
}

function simpleParse(...keys) {
  return (match, cursor) => {
    const ret = {};
    let i;

    for (i = 0; i < keys.length; i++) {
      ret[keys[i]] = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(match[cursor + i]);
    }
    return [ret, null, cursor + i];
  };
}

// ISO and SQL parsing
const offsetRegex = /(?:(Z)|([+-]\d\d)(?::?(\d\d))?)/;
const isoExtendedZone = `(?:${offsetRegex.source}?(?:\\[(${ianaRegex.source})\\])?)?`;
const isoTimeBaseRegex = /(\d\d)(?::?(\d\d)(?::?(\d\d)(?:[.,](\d{1,30}))?)?)?/;
const isoTimeRegex = RegExp(`${isoTimeBaseRegex.source}${isoExtendedZone}`);
const isoTimeExtensionRegex = RegExp(`(?:T${isoTimeRegex.source})?`);
const isoYmdRegex = /([+-]\d{6}|\d{4})(?:-?(\d\d)(?:-?(\d\d))?)?/;
const isoWeekRegex = /(\d{4})-?W(\d\d)(?:-?(\d))?/;
const isoOrdinalRegex = /(\d{4})-?(\d{3})/;
const extractISOWeekData = simpleParse("weekYear", "weekNumber", "weekDay");
const extractISOOrdinalData = simpleParse("year", "ordinal");
const sqlYmdRegex = /(\d{4})-(\d\d)-(\d\d)/; // dumbed-down version of the ISO one
const sqlTimeRegex = RegExp(
  `${isoTimeBaseRegex.source} ?(?:${offsetRegex.source}|(${ianaRegex.source}))?`
);
const sqlTimeExtensionRegex = RegExp(`(?: ${sqlTimeRegex.source})?`);

function int(match, pos, fallback) {
  const m = match[pos];
  return (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(m) ? fallback : (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(m);
}

function extractISOYmd(match, cursor) {
  const item = {
    year: int(match, cursor),
    month: int(match, cursor + 1, 1),
    day: int(match, cursor + 2, 1),
  };

  return [item, null, cursor + 3];
}

function extractISOTime(match, cursor) {
  const item = {
    hours: int(match, cursor, 0),
    minutes: int(match, cursor + 1, 0),
    seconds: int(match, cursor + 2, 0),
    milliseconds: (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseMillis)(match[cursor + 3]),
  };

  return [item, null, cursor + 4];
}

function extractISOOffset(match, cursor) {
  const local = !match[cursor] && !match[cursor + 1],
    fullOffset = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.signedOffset)(match[cursor + 1], match[cursor + 2]),
    zone = local ? null : _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"].instance(fullOffset);
  return [{}, zone, cursor + 3];
}

function extractIANAZone(match, cursor) {
  const zone = match[cursor] ? _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_3__["default"].create(match[cursor]) : null;
  return [{}, zone, cursor + 1];
}

// ISO time parsing

const isoTimeOnly = RegExp(`^T?${isoTimeBaseRegex.source}$`);

// ISO duration parsing

const isoDuration =
  /^-?P(?:(?:(-?\d{1,20}(?:\.\d{1,20})?)Y)?(?:(-?\d{1,20}(?:\.\d{1,20})?)M)?(?:(-?\d{1,20}(?:\.\d{1,20})?)W)?(?:(-?\d{1,20}(?:\.\d{1,20})?)D)?(?:T(?:(-?\d{1,20}(?:\.\d{1,20})?)H)?(?:(-?\d{1,20}(?:\.\d{1,20})?)M)?(?:(-?\d{1,20})(?:[.,](-?\d{1,20}))?S)?)?)$/;

function extractISODuration(match) {
  const [s, yearStr, monthStr, weekStr, dayStr, hourStr, minuteStr, secondStr, millisecondsStr] =
    match;

  const hasNegativePrefix = s[0] === "-";
  const negativeSeconds = secondStr && secondStr[0] === "-";

  const maybeNegate = (num, force = false) =>
    num !== undefined && (force || (num && hasNegativePrefix)) ? -num : num;

  return [
    {
      years: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseFloating)(yearStr)),
      months: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseFloating)(monthStr)),
      weeks: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseFloating)(weekStr)),
      days: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseFloating)(dayStr)),
      hours: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseFloating)(hourStr)),
      minutes: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseFloating)(minuteStr)),
      seconds: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseFloating)(secondStr), secondStr === "-0"),
      milliseconds: maybeNegate((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseMillis)(millisecondsStr), negativeSeconds),
    },
  ];
}

// These are a little braindead. EDT *should* tell us that we're in, say, America/New_York
// and not just that we're in -240 *right now*. But since I don't think these are used that often
// I'm just going to ignore that
const obsOffsets = {
  GMT: 0,
  EDT: -4 * 60,
  EST: -5 * 60,
  CDT: -5 * 60,
  CST: -6 * 60,
  MDT: -6 * 60,
  MST: -7 * 60,
  PDT: -7 * 60,
  PST: -8 * 60,
};

function fromStrings(weekdayStr, yearStr, monthStr, dayStr, hourStr, minuteStr, secondStr) {
  const result = {
    year: yearStr.length === 2 ? (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.untruncateYear)((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(yearStr)) : (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(yearStr),
    month: _english_js__WEBPACK_IMPORTED_MODULE_1__.monthsShort.indexOf(monthStr) + 1,
    day: (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(dayStr),
    hour: (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(hourStr),
    minute: (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(minuteStr),
  };

  if (secondStr) result.second = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseInteger)(secondStr);
  if (weekdayStr) {
    result.weekday =
      weekdayStr.length > 3
        ? _english_js__WEBPACK_IMPORTED_MODULE_1__.weekdaysLong.indexOf(weekdayStr) + 1
        : _english_js__WEBPACK_IMPORTED_MODULE_1__.weekdaysShort.indexOf(weekdayStr) + 1;
  }

  return result;
}

// RFC 2822/5322
const rfc2822 =
  /^(?:(Mon|Tue|Wed|Thu|Fri|Sat|Sun),\s)?(\d{1,2})\s(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)\s(\d{2,4})\s(\d\d):(\d\d)(?::(\d\d))?\s(?:(UT|GMT|[ECMP][SD]T)|([Zz])|(?:([+-]\d\d)(\d\d)))$/;

function extractRFC2822(match) {
  const [
      ,
      weekdayStr,
      dayStr,
      monthStr,
      yearStr,
      hourStr,
      minuteStr,
      secondStr,
      obsOffset,
      milOffset,
      offHourStr,
      offMinuteStr,
    ] = match,
    result = fromStrings(weekdayStr, yearStr, monthStr, dayStr, hourStr, minuteStr, secondStr);

  let offset;
  if (obsOffset) {
    offset = obsOffsets[obsOffset];
  } else if (milOffset) {
    offset = 0;
  } else {
    offset = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.signedOffset)(offHourStr, offMinuteStr);
  }

  return [result, new _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"](offset)];
}

function preprocessRFC2822(s) {
  // Remove comments and folding whitespace and replace multiple-spaces with a single space
  return s
    .replace(/\([^()]*\)|[\n\t]/g, " ")
    .replace(/(\s\s+)/g, " ")
    .trim();
}

// http date

const rfc1123 =
    /^(Mon|Tue|Wed|Thu|Fri|Sat|Sun), (\d\d) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) (\d{4}) (\d\d):(\d\d):(\d\d) GMT$/,
  rfc850 =
    /^(Monday|Tuesday|Wednesday|Thursday|Friday|Saturday|Sunday), (\d\d)-(Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)-(\d\d) (\d\d):(\d\d):(\d\d) GMT$/,
  ascii =
    /^(Mon|Tue|Wed|Thu|Fri|Sat|Sun) (Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec) ( \d|\d\d) (\d\d):(\d\d):(\d\d) (\d{4})$/;

function extractRFC1123Or850(match) {
  const [, weekdayStr, dayStr, monthStr, yearStr, hourStr, minuteStr, secondStr] = match,
    result = fromStrings(weekdayStr, yearStr, monthStr, dayStr, hourStr, minuteStr, secondStr);
  return [result, _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"].utcInstance];
}

function extractASCII(match) {
  const [, weekdayStr, monthStr, dayStr, hourStr, minuteStr, secondStr, yearStr] = match,
    result = fromStrings(weekdayStr, yearStr, monthStr, dayStr, hourStr, minuteStr, secondStr);
  return [result, _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"].utcInstance];
}

const isoYmdWithTimeExtensionRegex = combineRegexes(isoYmdRegex, isoTimeExtensionRegex);
const isoWeekWithTimeExtensionRegex = combineRegexes(isoWeekRegex, isoTimeExtensionRegex);
const isoOrdinalWithTimeExtensionRegex = combineRegexes(isoOrdinalRegex, isoTimeExtensionRegex);
const isoTimeCombinedRegex = combineRegexes(isoTimeRegex);

const extractISOYmdTimeAndOffset = combineExtractors(
  extractISOYmd,
  extractISOTime,
  extractISOOffset,
  extractIANAZone
);
const extractISOWeekTimeAndOffset = combineExtractors(
  extractISOWeekData,
  extractISOTime,
  extractISOOffset,
  extractIANAZone
);
const extractISOOrdinalDateAndTime = combineExtractors(
  extractISOOrdinalData,
  extractISOTime,
  extractISOOffset,
  extractIANAZone
);
const extractISOTimeAndOffset = combineExtractors(
  extractISOTime,
  extractISOOffset,
  extractIANAZone
);

/*
 * @private
 */

function parseISODate(s) {
  return parse(
    s,
    [isoYmdWithTimeExtensionRegex, extractISOYmdTimeAndOffset],
    [isoWeekWithTimeExtensionRegex, extractISOWeekTimeAndOffset],
    [isoOrdinalWithTimeExtensionRegex, extractISOOrdinalDateAndTime],
    [isoTimeCombinedRegex, extractISOTimeAndOffset]
  );
}

function parseRFC2822Date(s) {
  return parse(preprocessRFC2822(s), [rfc2822, extractRFC2822]);
}

function parseHTTPDate(s) {
  return parse(
    s,
    [rfc1123, extractRFC1123Or850],
    [rfc850, extractRFC1123Or850],
    [ascii, extractASCII]
  );
}

function parseISODuration(s) {
  return parse(s, [isoDuration, extractISODuration]);
}

const extractISOTimeOnly = combineExtractors(extractISOTime);

function parseISOTimeOnly(s) {
  return parse(s, [isoTimeOnly, extractISOTimeOnly]);
}

const sqlYmdWithTimeExtensionRegex = combineRegexes(sqlYmdRegex, sqlTimeExtensionRegex);
const sqlTimeCombinedRegex = combineRegexes(sqlTimeRegex);

const extractISOTimeOffsetAndIANAZone = combineExtractors(
  extractISOTime,
  extractISOOffset,
  extractIANAZone
);

function parseSQL(s) {
  return parse(
    s,
    [sqlYmdWithTimeExtensionRegex, extractISOYmdTimeAndOffset],
    [sqlTimeCombinedRegex, extractISOTimeOffsetAndIANAZone]
  );
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/tokenParser.js":
/*!****************************************************!*\
  !*** ./node_modules/luxon/src/impl/tokenParser.js ***!
  \****************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   TokenParser: () => (/* binding */ TokenParser),
/* harmony export */   expandMacroTokens: () => (/* binding */ expandMacroTokens),
/* harmony export */   explainFromTokens: () => (/* binding */ explainFromTokens),
/* harmony export */   formatOptsToTokens: () => (/* binding */ formatOptsToTokens),
/* harmony export */   parseFromTokens: () => (/* binding */ parseFromTokens)
/* harmony export */ });
/* harmony import */ var _util_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _formatter_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./formatter.js */ "./node_modules/luxon/src/impl/formatter.js");
/* harmony import */ var _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../zones/fixedOffsetZone.js */ "./node_modules/luxon/src/zones/fixedOffsetZone.js");
/* harmony import */ var _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../zones/IANAZone.js */ "./node_modules/luxon/src/zones/IANAZone.js");
/* harmony import */ var _datetime_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ../datetime.js */ "./node_modules/luxon/src/datetime.js");
/* harmony import */ var _digits_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./digits.js */ "./node_modules/luxon/src/impl/digits.js");
/* harmony import */ var _errors_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ../errors.js */ "./node_modules/luxon/src/errors.js");








const MISSING_FTP = "missing Intl.DateTimeFormat.formatToParts support";

function intUnit(regex, post = (i) => i) {
  return { regex, deser: ([s]) => post((0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.parseDigits)(s)) };
}

const NBSP = String.fromCharCode(160);
const spaceOrNBSP = `[ ${NBSP}]`;
const spaceOrNBSPRegExp = new RegExp(spaceOrNBSP, "g");

function fixListRegex(s) {
  // make dots optional and also make them literal
  // make space and non breakable space characters interchangeable
  return s.replace(/\./g, "\\.?").replace(spaceOrNBSPRegExp, spaceOrNBSP);
}

function stripInsensitivities(s) {
  return s
    .replace(/\./g, "") // ignore dots that were made optional
    .replace(spaceOrNBSPRegExp, " ") // interchange space and nbsp
    .toLowerCase();
}

function oneOf(strings, startIndex) {
  if (strings === null) {
    return null;
  } else {
    return {
      regex: RegExp(strings.map(fixListRegex).join("|")),
      deser: ([s]) =>
        strings.findIndex((i) => stripInsensitivities(s) === stripInsensitivities(i)) + startIndex,
    };
  }
}

function offset(regex, groups) {
  return { regex, deser: ([, h, m]) => (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.signedOffset)(h, m), groups };
}

function simple(regex) {
  return { regex, deser: ([s]) => s };
}

function escapeToken(value) {
  return value.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, "\\$&");
}

/**
 * @param token
 * @param {Locale} loc
 */
function unitForToken(token, loc) {
  const one = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc),
    two = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{2}"),
    three = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{3}"),
    four = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{4}"),
    six = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{6}"),
    oneOrTwo = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{1,2}"),
    oneToThree = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{1,3}"),
    oneToSix = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{1,6}"),
    oneToNine = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{1,9}"),
    twoToFour = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{2,4}"),
    fourToSix = (0,_digits_js__WEBPACK_IMPORTED_MODULE_5__.digitRegex)(loc, "{4,6}"),
    literal = (t) => ({ regex: RegExp(escapeToken(t.val)), deser: ([s]) => s, literal: true }),
    unitate = (t) => {
      if (token.literal) {
        return literal(t);
      }
      switch (t.val) {
        // era
        case "G":
          return oneOf(loc.eras("short"), 0);
        case "GG":
          return oneOf(loc.eras("long"), 0);
        // years
        case "y":
          return intUnit(oneToSix);
        case "yy":
          return intUnit(twoToFour, _util_js__WEBPACK_IMPORTED_MODULE_0__.untruncateYear);
        case "yyyy":
          return intUnit(four);
        case "yyyyy":
          return intUnit(fourToSix);
        case "yyyyyy":
          return intUnit(six);
        // months
        case "M":
          return intUnit(oneOrTwo);
        case "MM":
          return intUnit(two);
        case "MMM":
          return oneOf(loc.months("short", true), 1);
        case "MMMM":
          return oneOf(loc.months("long", true), 1);
        case "L":
          return intUnit(oneOrTwo);
        case "LL":
          return intUnit(two);
        case "LLL":
          return oneOf(loc.months("short", false), 1);
        case "LLLL":
          return oneOf(loc.months("long", false), 1);
        // dates
        case "d":
          return intUnit(oneOrTwo);
        case "dd":
          return intUnit(two);
        // ordinals
        case "o":
          return intUnit(oneToThree);
        case "ooo":
          return intUnit(three);
        // time
        case "HH":
          return intUnit(two);
        case "H":
          return intUnit(oneOrTwo);
        case "hh":
          return intUnit(two);
        case "h":
          return intUnit(oneOrTwo);
        case "mm":
          return intUnit(two);
        case "m":
          return intUnit(oneOrTwo);
        case "q":
          return intUnit(oneOrTwo);
        case "qq":
          return intUnit(two);
        case "s":
          return intUnit(oneOrTwo);
        case "ss":
          return intUnit(two);
        case "S":
          return intUnit(oneToThree);
        case "SSS":
          return intUnit(three);
        case "u":
          return simple(oneToNine);
        case "uu":
          return simple(oneOrTwo);
        case "uuu":
          return intUnit(one);
        // meridiem
        case "a":
          return oneOf(loc.meridiems(), 0);
        // weekYear (k)
        case "kkkk":
          return intUnit(four);
        case "kk":
          return intUnit(twoToFour, _util_js__WEBPACK_IMPORTED_MODULE_0__.untruncateYear);
        // weekNumber (W)
        case "W":
          return intUnit(oneOrTwo);
        case "WW":
          return intUnit(two);
        // weekdays
        case "E":
        case "c":
          return intUnit(one);
        case "EEE":
          return oneOf(loc.weekdays("short", false), 1);
        case "EEEE":
          return oneOf(loc.weekdays("long", false), 1);
        case "ccc":
          return oneOf(loc.weekdays("short", true), 1);
        case "cccc":
          return oneOf(loc.weekdays("long", true), 1);
        // offset/zone
        case "Z":
        case "ZZ":
          return offset(new RegExp(`([+-]${oneOrTwo.source})(?::(${two.source}))?`), 2);
        case "ZZZ":
          return offset(new RegExp(`([+-]${oneOrTwo.source})(${two.source})?`), 2);
        // we don't support ZZZZ (PST) or ZZZZZ (Pacific Standard Time) in parsing
        // because we don't have any way to figure out what they are
        case "z":
          return simple(/[a-z_+-/]{1,256}?/i);
        // this special-case "token" represents a place where a macro-token expanded into a white-space literal
        // in this case we accept any non-newline white-space
        case " ":
          return simple(/[^\S\n\r]/);
        default:
          return literal(t);
      }
    };

  const unit = unitate(token) || {
    invalidReason: MISSING_FTP,
  };

  unit.token = token;

  return unit;
}

const partTypeStyleToTokenVal = {
  year: {
    "2-digit": "yy",
    numeric: "yyyyy",
  },
  month: {
    numeric: "M",
    "2-digit": "MM",
    short: "MMM",
    long: "MMMM",
  },
  day: {
    numeric: "d",
    "2-digit": "dd",
  },
  weekday: {
    short: "EEE",
    long: "EEEE",
  },
  dayperiod: "a",
  dayPeriod: "a",
  hour12: {
    numeric: "h",
    "2-digit": "hh",
  },
  hour24: {
    numeric: "H",
    "2-digit": "HH",
  },
  minute: {
    numeric: "m",
    "2-digit": "mm",
  },
  second: {
    numeric: "s",
    "2-digit": "ss",
  },
  timeZoneName: {
    long: "ZZZZZ",
    short: "ZZZ",
  },
};

function tokenForPart(part, formatOpts, resolvedOpts) {
  const { type, value } = part;

  if (type === "literal") {
    const isSpace = /^\s+$/.test(value);
    return {
      literal: !isSpace,
      val: isSpace ? " " : value,
    };
  }

  const style = formatOpts[type];

  // The user might have explicitly specified hour12 or hourCycle
  // if so, respect their decision
  // if not, refer back to the resolvedOpts, which are based on the locale
  let actualType = type;
  if (type === "hour") {
    if (formatOpts.hour12 != null) {
      actualType = formatOpts.hour12 ? "hour12" : "hour24";
    } else if (formatOpts.hourCycle != null) {
      if (formatOpts.hourCycle === "h11" || formatOpts.hourCycle === "h12") {
        actualType = "hour12";
      } else {
        actualType = "hour24";
      }
    } else {
      // tokens only differentiate between 24 hours or not,
      // so we do not need to check hourCycle here, which is less supported anyways
      actualType = resolvedOpts.hour12 ? "hour12" : "hour24";
    }
  }
  let val = partTypeStyleToTokenVal[actualType];
  if (typeof val === "object") {
    val = val[style];
  }

  if (val) {
    return {
      literal: false,
      val,
    };
  }

  return undefined;
}

function buildRegex(units) {
  const re = units.map((u) => u.regex).reduce((f, r) => `${f}(${r.source})`, "");
  return [`^${re}$`, units];
}

function match(input, regex, handlers) {
  const matches = input.match(regex);

  if (matches) {
    const all = {};
    let matchIndex = 1;
    for (const i in handlers) {
      if ((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.hasOwnProperty)(handlers, i)) {
        const h = handlers[i],
          groups = h.groups ? h.groups + 1 : 1;
        if (!h.literal && h.token) {
          all[h.token.val[0]] = h.deser(matches.slice(matchIndex, matchIndex + groups));
        }
        matchIndex += groups;
      }
    }
    return [matches, all];
  } else {
    return [matches, {}];
  }
}

function dateTimeFromMatches(matches) {
  const toField = (token) => {
    switch (token) {
      case "S":
        return "millisecond";
      case "s":
        return "second";
      case "m":
        return "minute";
      case "h":
      case "H":
        return "hour";
      case "d":
        return "day";
      case "o":
        return "ordinal";
      case "L":
      case "M":
        return "month";
      case "y":
        return "year";
      case "E":
      case "c":
        return "weekday";
      case "W":
        return "weekNumber";
      case "k":
        return "weekYear";
      case "q":
        return "quarter";
      default:
        return null;
    }
  };

  let zone = null;
  let specificOffset;
  if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(matches.z)) {
    zone = _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_3__["default"].create(matches.z);
  }

  if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(matches.Z)) {
    if (!zone) {
      zone = new _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"](matches.Z);
    }
    specificOffset = matches.Z;
  }

  if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(matches.q)) {
    matches.M = (matches.q - 1) * 3 + 1;
  }

  if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(matches.h)) {
    if (matches.h < 12 && matches.a === 1) {
      matches.h += 12;
    } else if (matches.h === 12 && matches.a === 0) {
      matches.h = 0;
    }
  }

  if (matches.G === 0 && matches.y) {
    matches.y = -matches.y;
  }

  if (!(0,_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(matches.u)) {
    matches.S = (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.parseMillis)(matches.u);
  }

  const vals = Object.keys(matches).reduce((r, k) => {
    const f = toField(k);
    if (f) {
      r[f] = matches[k];
    }

    return r;
  }, {});

  return [vals, zone, specificOffset];
}

let dummyDateTimeCache = null;

function getDummyDateTime() {
  if (!dummyDateTimeCache) {
    dummyDateTimeCache = _datetime_js__WEBPACK_IMPORTED_MODULE_4__["default"].fromMillis(1555555555555);
  }

  return dummyDateTimeCache;
}

function maybeExpandMacroToken(token, locale) {
  if (token.literal) {
    return token;
  }

  const formatOpts = _formatter_js__WEBPACK_IMPORTED_MODULE_1__["default"].macroTokenToFormatOpts(token.val);
  const tokens = formatOptsToTokens(formatOpts, locale);

  if (tokens == null || tokens.includes(undefined)) {
    return token;
  }

  return tokens;
}

function expandMacroTokens(tokens, locale) {
  return Array.prototype.concat(...tokens.map((t) => maybeExpandMacroToken(t, locale)));
}

/**
 * @private
 */

class TokenParser {
  constructor(locale, format) {
    this.locale = locale;
    this.format = format;
    this.tokens = expandMacroTokens(_formatter_js__WEBPACK_IMPORTED_MODULE_1__["default"].parseFormat(format), locale);
    this.units = this.tokens.map((t) => unitForToken(t, locale));
    this.disqualifyingUnit = this.units.find((t) => t.invalidReason);

    if (!this.disqualifyingUnit) {
      const [regexString, handlers] = buildRegex(this.units);
      this.regex = RegExp(regexString, "i");
      this.handlers = handlers;
    }
  }

  explainFromTokens(input) {
    if (!this.isValid) {
      return { input, tokens: this.tokens, invalidReason: this.invalidReason };
    } else {
      const [rawMatches, matches] = match(input, this.regex, this.handlers),
        [result, zone, specificOffset] = matches
          ? dateTimeFromMatches(matches)
          : [null, null, undefined];
      if ((0,_util_js__WEBPACK_IMPORTED_MODULE_0__.hasOwnProperty)(matches, "a") && (0,_util_js__WEBPACK_IMPORTED_MODULE_0__.hasOwnProperty)(matches, "H")) {
        throw new _errors_js__WEBPACK_IMPORTED_MODULE_6__.ConflictingSpecificationError(
          "Can't include meridiem when specifying 24-hour format"
        );
      }
      return {
        input,
        tokens: this.tokens,
        regex: this.regex,
        rawMatches,
        matches,
        result,
        zone,
        specificOffset,
      };
    }
  }

  get isValid() {
    return !this.disqualifyingUnit;
  }

  get invalidReason() {
    return this.disqualifyingUnit ? this.disqualifyingUnit.invalidReason : null;
  }
}

function explainFromTokens(locale, input, format) {
  const parser = new TokenParser(locale, format);
  return parser.explainFromTokens(input);
}

function parseFromTokens(locale, input, format) {
  const { result, zone, specificOffset, invalidReason } = explainFromTokens(locale, input, format);
  return [result, zone, specificOffset, invalidReason];
}

function formatOptsToTokens(formatOpts, locale) {
  if (!formatOpts) {
    return null;
  }

  const formatter = _formatter_js__WEBPACK_IMPORTED_MODULE_1__["default"].create(locale, formatOpts);
  const df = formatter.dtFormatter(getDummyDateTime());
  const parts = df.formatToParts();
  const resolvedOpts = df.resolvedOptions();
  return parts.map((p) => tokenForPart(p, formatOpts, resolvedOpts));
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/util.js":
/*!*********************************************!*\
  !*** ./node_modules/luxon/src/impl/util.js ***!
  \*********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   asNumber: () => (/* binding */ asNumber),
/* harmony export */   bestBy: () => (/* binding */ bestBy),
/* harmony export */   daysInMonth: () => (/* binding */ daysInMonth),
/* harmony export */   daysInYear: () => (/* binding */ daysInYear),
/* harmony export */   floorMod: () => (/* binding */ floorMod),
/* harmony export */   formatOffset: () => (/* binding */ formatOffset),
/* harmony export */   hasLocaleWeekInfo: () => (/* binding */ hasLocaleWeekInfo),
/* harmony export */   hasOwnProperty: () => (/* binding */ hasOwnProperty),
/* harmony export */   hasRelative: () => (/* binding */ hasRelative),
/* harmony export */   integerBetween: () => (/* binding */ integerBetween),
/* harmony export */   isDate: () => (/* binding */ isDate),
/* harmony export */   isInteger: () => (/* binding */ isInteger),
/* harmony export */   isLeapYear: () => (/* binding */ isLeapYear),
/* harmony export */   isNumber: () => (/* binding */ isNumber),
/* harmony export */   isString: () => (/* binding */ isString),
/* harmony export */   isUndefined: () => (/* binding */ isUndefined),
/* harmony export */   maybeArray: () => (/* binding */ maybeArray),
/* harmony export */   normalizeObject: () => (/* binding */ normalizeObject),
/* harmony export */   objToLocalTS: () => (/* binding */ objToLocalTS),
/* harmony export */   padStart: () => (/* binding */ padStart),
/* harmony export */   parseFloating: () => (/* binding */ parseFloating),
/* harmony export */   parseInteger: () => (/* binding */ parseInteger),
/* harmony export */   parseMillis: () => (/* binding */ parseMillis),
/* harmony export */   parseZoneInfo: () => (/* binding */ parseZoneInfo),
/* harmony export */   pick: () => (/* binding */ pick),
/* harmony export */   roundTo: () => (/* binding */ roundTo),
/* harmony export */   signedOffset: () => (/* binding */ signedOffset),
/* harmony export */   timeObject: () => (/* binding */ timeObject),
/* harmony export */   untruncateYear: () => (/* binding */ untruncateYear),
/* harmony export */   validateWeekSettings: () => (/* binding */ validateWeekSettings),
/* harmony export */   weeksInWeekYear: () => (/* binding */ weeksInWeekYear)
/* harmony export */ });
/* harmony import */ var _errors_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../errors.js */ "./node_modules/luxon/src/errors.js");
/* harmony import */ var _settings_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../settings.js */ "./node_modules/luxon/src/settings.js");
/* harmony import */ var _conversions_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./conversions.js */ "./node_modules/luxon/src/impl/conversions.js");
/*
  This is just a junk drawer, containing anything used across multiple classes.
  Because Luxon is small(ish), this should stay small and we won't worry about splitting
  it up into, say, parsingUtil.js and basicUtil.js and so on. But they are divided up by feature area.
*/





/**
 * @private
 */

// TYPES

function isUndefined(o) {
  return typeof o === "undefined";
}

function isNumber(o) {
  return typeof o === "number";
}

function isInteger(o) {
  return typeof o === "number" && o % 1 === 0;
}

function isString(o) {
  return typeof o === "string";
}

function isDate(o) {
  return Object.prototype.toString.call(o) === "[object Date]";
}

// CAPABILITIES

function hasRelative() {
  try {
    return typeof Intl !== "undefined" && !!Intl.RelativeTimeFormat;
  } catch (e) {
    return false;
  }
}

function hasLocaleWeekInfo() {
  try {
    return (
      typeof Intl !== "undefined" &&
      !!Intl.Locale &&
      ("weekInfo" in Intl.Locale.prototype || "getWeekInfo" in Intl.Locale.prototype)
    );
  } catch (e) {
    return false;
  }
}

// OBJECTS AND ARRAYS

function maybeArray(thing) {
  return Array.isArray(thing) ? thing : [thing];
}

function bestBy(arr, by, compare) {
  if (arr.length === 0) {
    return undefined;
  }
  return arr.reduce((best, next) => {
    const pair = [by(next), next];
    if (!best) {
      return pair;
    } else if (compare(best[0], pair[0]) === best[0]) {
      return best;
    } else {
      return pair;
    }
  }, null)[1];
}

function pick(obj, keys) {
  return keys.reduce((a, k) => {
    a[k] = obj[k];
    return a;
  }, {});
}

function hasOwnProperty(obj, prop) {
  return Object.prototype.hasOwnProperty.call(obj, prop);
}

function validateWeekSettings(settings) {
  if (settings == null) {
    return null;
  } else if (typeof settings !== "object") {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidArgumentError("Week settings must be an object");
  } else {
    if (
      !integerBetween(settings.firstDay, 1, 7) ||
      !integerBetween(settings.minimalDays, 1, 7) ||
      !Array.isArray(settings.weekend) ||
      settings.weekend.some((v) => !integerBetween(v, 1, 7))
    ) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidArgumentError("Invalid week settings");
    }
    return {
      firstDay: settings.firstDay,
      minimalDays: settings.minimalDays,
      weekend: Array.from(settings.weekend),
    };
  }
}

// NUMBERS AND STRINGS

function integerBetween(thing, bottom, top) {
  return isInteger(thing) && thing >= bottom && thing <= top;
}

// x % n but takes the sign of n instead of x
function floorMod(x, n) {
  return x - n * Math.floor(x / n);
}

function padStart(input, n = 2) {
  const isNeg = input < 0;
  let padded;
  if (isNeg) {
    padded = "-" + ("" + -input).padStart(n, "0");
  } else {
    padded = ("" + input).padStart(n, "0");
  }
  return padded;
}

function parseInteger(string) {
  if (isUndefined(string) || string === null || string === "") {
    return undefined;
  } else {
    return parseInt(string, 10);
  }
}

function parseFloating(string) {
  if (isUndefined(string) || string === null || string === "") {
    return undefined;
  } else {
    return parseFloat(string);
  }
}

function parseMillis(fraction) {
  // Return undefined (instead of 0) in these cases, where fraction is not set
  if (isUndefined(fraction) || fraction === null || fraction === "") {
    return undefined;
  } else {
    const f = parseFloat("0." + fraction) * 1000;
    return Math.floor(f);
  }
}

function roundTo(number, digits, towardZero = false) {
  const factor = 10 ** digits,
    rounder = towardZero ? Math.trunc : Math.round;
  return rounder(number * factor) / factor;
}

// DATE BASICS

function isLeapYear(year) {
  return year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0);
}

function daysInYear(year) {
  return isLeapYear(year) ? 366 : 365;
}

function daysInMonth(year, month) {
  const modMonth = floorMod(month - 1, 12) + 1,
    modYear = year + (month - modMonth) / 12;

  if (modMonth === 2) {
    return isLeapYear(modYear) ? 29 : 28;
  } else {
    return [31, null, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][modMonth - 1];
  }
}

// convert a calendar object to a local timestamp (epoch, but with the offset baked in)
function objToLocalTS(obj) {
  let d = Date.UTC(
    obj.year,
    obj.month - 1,
    obj.day,
    obj.hour,
    obj.minute,
    obj.second,
    obj.millisecond
  );

  // for legacy reasons, years between 0 and 99 are interpreted as 19XX; revert that
  if (obj.year < 100 && obj.year >= 0) {
    d = new Date(d);
    // set the month and day again, this is necessary because year 2000 is a leap year, but year 100 is not
    // so if obj.year is in 99, but obj.day makes it roll over into year 100,
    // the calculations done by Date.UTC are using year 2000 - which is incorrect
    d.setUTCFullYear(obj.year, obj.month - 1, obj.day);
  }
  return +d;
}

// adapted from moment.js: https://github.com/moment/moment/blob/000ac1800e620f770f4eb31b5ae908f6167b0ab2/src/lib/units/week-calendar-utils.js
function firstWeekOffset(year, minDaysInFirstWeek, startOfWeek) {
  const fwdlw = (0,_conversions_js__WEBPACK_IMPORTED_MODULE_2__.isoWeekdayToLocal)((0,_conversions_js__WEBPACK_IMPORTED_MODULE_2__.dayOfWeek)(year, 1, minDaysInFirstWeek), startOfWeek);
  return -fwdlw + minDaysInFirstWeek - 1;
}

function weeksInWeekYear(weekYear, minDaysInFirstWeek = 4, startOfWeek = 1) {
  const weekOffset = firstWeekOffset(weekYear, minDaysInFirstWeek, startOfWeek);
  const weekOffsetNext = firstWeekOffset(weekYear + 1, minDaysInFirstWeek, startOfWeek);
  return (daysInYear(weekYear) - weekOffset + weekOffsetNext) / 7;
}

function untruncateYear(year) {
  if (year > 99) {
    return year;
  } else return year > _settings_js__WEBPACK_IMPORTED_MODULE_1__["default"].twoDigitCutoffYear ? 1900 + year : 2000 + year;
}

// PARSING

function parseZoneInfo(ts, offsetFormat, locale, timeZone = null) {
  const date = new Date(ts),
    intlOpts = {
      hourCycle: "h23",
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
    };

  if (timeZone) {
    intlOpts.timeZone = timeZone;
  }

  const modified = { timeZoneName: offsetFormat, ...intlOpts };

  const parsed = new Intl.DateTimeFormat(locale, modified)
    .formatToParts(date)
    .find((m) => m.type.toLowerCase() === "timezonename");
  return parsed ? parsed.value : null;
}

// signedOffset('-5', '30') -> -330
function signedOffset(offHourStr, offMinuteStr) {
  let offHour = parseInt(offHourStr, 10);

  // don't || this because we want to preserve -0
  if (Number.isNaN(offHour)) {
    offHour = 0;
  }

  const offMin = parseInt(offMinuteStr, 10) || 0,
    offMinSigned = offHour < 0 || Object.is(offHour, -0) ? -offMin : offMin;
  return offHour * 60 + offMinSigned;
}

// COERCION

function asNumber(value) {
  const numericValue = Number(value);
  if (typeof value === "boolean" || value === "" || Number.isNaN(numericValue))
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.InvalidArgumentError(`Invalid unit value ${value}`);
  return numericValue;
}

function normalizeObject(obj, normalizer) {
  const normalized = {};
  for (const u in obj) {
    if (hasOwnProperty(obj, u)) {
      const v = obj[u];
      if (v === undefined || v === null) continue;
      normalized[normalizer(u)] = asNumber(v);
    }
  }
  return normalized;
}

/**
 * Returns the offset's value as a string
 * @param {number} ts - Epoch milliseconds for which to get the offset
 * @param {string} format - What style of offset to return.
 *                          Accepts 'narrow', 'short', or 'techie'. Returning '+6', '+06:00', or '+0600' respectively
 * @return {string}
 */
function formatOffset(offset, format) {
  const hours = Math.trunc(Math.abs(offset / 60)),
    minutes = Math.trunc(Math.abs(offset % 60)),
    sign = offset >= 0 ? "+" : "-";

  switch (format) {
    case "short":
      return `${sign}${padStart(hours, 2)}:${padStart(minutes, 2)}`;
    case "narrow":
      return `${sign}${hours}${minutes > 0 ? `:${minutes}` : ""}`;
    case "techie":
      return `${sign}${padStart(hours, 2)}${padStart(minutes, 2)}`;
    default:
      throw new RangeError(`Value format ${format} is out of range for property format`);
  }
}

function timeObject(obj) {
  return pick(obj, ["hour", "minute", "second", "millisecond"]);
}


/***/ }),

/***/ "./node_modules/luxon/src/impl/zoneUtil.js":
/*!*************************************************!*\
  !*** ./node_modules/luxon/src/impl/zoneUtil.js ***!
  \*************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   normalizeZone: () => (/* binding */ normalizeZone)
/* harmony export */ });
/* harmony import */ var _zone_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../zone.js */ "./node_modules/luxon/src/zone.js");
/* harmony import */ var _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../zones/IANAZone.js */ "./node_modules/luxon/src/zones/IANAZone.js");
/* harmony import */ var _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../zones/fixedOffsetZone.js */ "./node_modules/luxon/src/zones/fixedOffsetZone.js");
/* harmony import */ var _zones_invalidZone_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ../zones/invalidZone.js */ "./node_modules/luxon/src/zones/invalidZone.js");
/* harmony import */ var _util_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _zones_systemZone_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ../zones/systemZone.js */ "./node_modules/luxon/src/zones/systemZone.js");
/**
 * @private
 */









function normalizeZone(input, defaultZone) {
  let offset;
  if ((0,_util_js__WEBPACK_IMPORTED_MODULE_4__.isUndefined)(input) || input === null) {
    return defaultZone;
  } else if (input instanceof _zone_js__WEBPACK_IMPORTED_MODULE_0__["default"]) {
    return input;
  } else if ((0,_util_js__WEBPACK_IMPORTED_MODULE_4__.isString)(input)) {
    const lowered = input.toLowerCase();
    if (lowered === "default") return defaultZone;
    else if (lowered === "local" || lowered === "system") return _zones_systemZone_js__WEBPACK_IMPORTED_MODULE_5__["default"].instance;
    else if (lowered === "utc" || lowered === "gmt") return _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"].utcInstance;
    else return _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"].parseSpecifier(lowered) || _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_1__["default"].create(input);
  } else if ((0,_util_js__WEBPACK_IMPORTED_MODULE_4__.isNumber)(input)) {
    return _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_2__["default"].instance(input);
  } else if (typeof input === "object" && "offset" in input && typeof input.offset === "function") {
    // This is dumb, but the instanceof check above doesn't seem to really work
    // so we're duck checking it
    return input;
  } else {
    return new _zones_invalidZone_js__WEBPACK_IMPORTED_MODULE_3__["default"](input);
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/info.js":
/*!****************************************!*\
  !*** ./node_modules/luxon/src/info.js ***!
  \****************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Info)
/* harmony export */ });
/* harmony import */ var _datetime_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./datetime.js */ "./node_modules/luxon/src/datetime.js");
/* harmony import */ var _settings_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./settings.js */ "./node_modules/luxon/src/settings.js");
/* harmony import */ var _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./impl/locale.js */ "./node_modules/luxon/src/impl/locale.js");
/* harmony import */ var _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./zones/IANAZone.js */ "./node_modules/luxon/src/zones/IANAZone.js");
/* harmony import */ var _impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./impl/zoneUtil.js */ "./node_modules/luxon/src/impl/zoneUtil.js");
/* harmony import */ var _impl_util_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./impl/util.js */ "./node_modules/luxon/src/impl/util.js");








/**
 * The Info class contains static methods for retrieving general time and date related data. For example, it has methods for finding out if a time zone has a DST, for listing the months in any supported locale, and for discovering which of Luxon features are available in the current environment.
 */
class Info {
  /**
   * Return whether the specified zone contains a DST.
   * @param {string|Zone} [zone='local'] - Zone to check. Defaults to the environment's local zone.
   * @return {boolean}
   */
  static hasDST(zone = _settings_js__WEBPACK_IMPORTED_MODULE_1__["default"].defaultZone) {
    const proto = _datetime_js__WEBPACK_IMPORTED_MODULE_0__["default"].now().setZone(zone).set({ month: 12 });

    return !zone.isUniversal && proto.offset !== proto.set({ month: 6 }).offset;
  }

  /**
   * Return whether the specified zone is a valid IANA specifier.
   * @param {string} zone - Zone to check
   * @return {boolean}
   */
  static isValidIANAZone(zone) {
    return _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_3__["default"].isValidZone(zone);
  }

  /**
   * Converts the input into a {@link Zone} instance.
   *
   * * If `input` is already a Zone instance, it is returned unchanged.
   * * If `input` is a string containing a valid time zone name, a Zone instance
   *   with that name is returned.
   * * If `input` is a string that doesn't refer to a known time zone, a Zone
   *   instance with {@link Zone#isValid} == false is returned.
   * * If `input is a number, a Zone instance with the specified fixed offset
   *   in minutes is returned.
   * * If `input` is `null` or `undefined`, the default zone is returned.
   * @param {string|Zone|number} [input] - the value to be converted
   * @return {Zone}
   */
  static normalizeZone(input) {
    return (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_4__.normalizeZone)(input, _settings_js__WEBPACK_IMPORTED_MODULE_1__["default"].defaultZone);
  }

  /**
   * Get the weekday on which the week starts according to the given locale.
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @param {string} [opts.locObj=null] - an existing locale object to use
   * @returns {number} the start of the week, 1 for Monday through 7 for Sunday
   */
  static getStartOfWeek({ locale = null, locObj = null } = {}) {
    return (locObj || _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale)).getStartOfWeek();
  }

  /**
   * Get the minimum number of days necessary in a week before it is considered part of the next year according
   * to the given locale.
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @param {string} [opts.locObj=null] - an existing locale object to use
   * @returns {number}
   */
  static getMinimumDaysInFirstWeek({ locale = null, locObj = null } = {}) {
    return (locObj || _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale)).getMinDaysInFirstWeek();
  }

  /**
   * Get the weekdays, which are considered the weekend according to the given locale
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @param {string} [opts.locObj=null] - an existing locale object to use
   * @returns {number[]} an array of weekdays, 1 for Monday through 7 for Sunday
   */
  static getWeekendWeekdays({ locale = null, locObj = null } = {}) {
    // copy the array, because we cache it internally
    return (locObj || _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale)).getWeekendDays().slice();
  }

  /**
   * Return an array of standalone month names.
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat
   * @param {string} [length='long'] - the length of the month representation, such as "numeric", "2-digit", "narrow", "short", "long"
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @param {string} [opts.numberingSystem=null] - the numbering system
   * @param {string} [opts.locObj=null] - an existing locale object to use
   * @param {string} [opts.outputCalendar='gregory'] - the calendar
   * @example Info.months()[0] //=> 'January'
   * @example Info.months('short')[0] //=> 'Jan'
   * @example Info.months('numeric')[0] //=> '1'
   * @example Info.months('short', { locale: 'fr-CA' } )[0] //=> 'janv.'
   * @example Info.months('numeric', { locale: 'ar' })[0] //=> '١'
   * @example Info.months('long', { outputCalendar: 'islamic' })[0] //=> 'Rabiʻ I'
   * @return {Array}
   */
  static months(
    length = "long",
    { locale = null, numberingSystem = null, locObj = null, outputCalendar = "gregory" } = {}
  ) {
    return (locObj || _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale, numberingSystem, outputCalendar)).months(length);
  }

  /**
   * Return an array of format month names.
   * Format months differ from standalone months in that they're meant to appear next to the day of the month. In some languages, that
   * changes the string.
   * See {@link Info#months}
   * @param {string} [length='long'] - the length of the month representation, such as "numeric", "2-digit", "narrow", "short", "long"
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @param {string} [opts.numberingSystem=null] - the numbering system
   * @param {string} [opts.locObj=null] - an existing locale object to use
   * @param {string} [opts.outputCalendar='gregory'] - the calendar
   * @return {Array}
   */
  static monthsFormat(
    length = "long",
    { locale = null, numberingSystem = null, locObj = null, outputCalendar = "gregory" } = {}
  ) {
    return (locObj || _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale, numberingSystem, outputCalendar)).months(length, true);
  }

  /**
   * Return an array of standalone week names.
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat
   * @param {string} [length='long'] - the length of the weekday representation, such as "narrow", "short", "long".
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @param {string} [opts.numberingSystem=null] - the numbering system
   * @param {string} [opts.locObj=null] - an existing locale object to use
   * @example Info.weekdays()[0] //=> 'Monday'
   * @example Info.weekdays('short')[0] //=> 'Mon'
   * @example Info.weekdays('short', { locale: 'fr-CA' })[0] //=> 'lun.'
   * @example Info.weekdays('short', { locale: 'ar' })[0] //=> 'الاثنين'
   * @return {Array}
   */
  static weekdays(length = "long", { locale = null, numberingSystem = null, locObj = null } = {}) {
    return (locObj || _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale, numberingSystem, null)).weekdays(length);
  }

  /**
   * Return an array of format week names.
   * Format weekdays differ from standalone weekdays in that they're meant to appear next to more date information. In some languages, that
   * changes the string.
   * See {@link Info#weekdays}
   * @param {string} [length='long'] - the length of the month representation, such as "narrow", "short", "long".
   * @param {Object} opts - options
   * @param {string} [opts.locale=null] - the locale code
   * @param {string} [opts.numberingSystem=null] - the numbering system
   * @param {string} [opts.locObj=null] - an existing locale object to use
   * @return {Array}
   */
  static weekdaysFormat(
    length = "long",
    { locale = null, numberingSystem = null, locObj = null } = {}
  ) {
    return (locObj || _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale, numberingSystem, null)).weekdays(length, true);
  }

  /**
   * Return an array of meridiems.
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @example Info.meridiems() //=> [ 'AM', 'PM' ]
   * @example Info.meridiems({ locale: 'my' }) //=> [ 'နံနက်', 'ညနေ' ]
   * @return {Array}
   */
  static meridiems({ locale = null } = {}) {
    return _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale).meridiems();
  }

  /**
   * Return an array of eras, such as ['BC', 'AD']. The locale can be specified, but the calendar system is always Gregorian.
   * @param {string} [length='short'] - the length of the era representation, such as "short" or "long".
   * @param {Object} opts - options
   * @param {string} [opts.locale] - the locale code
   * @example Info.eras() //=> [ 'BC', 'AD' ]
   * @example Info.eras('long') //=> [ 'Before Christ', 'Anno Domini' ]
   * @example Info.eras('long', { locale: 'fr' }) //=> [ 'avant Jésus-Christ', 'après Jésus-Christ' ]
   * @return {Array}
   */
  static eras(length = "short", { locale = null } = {}) {
    return _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].create(locale, null, "gregory").eras(length);
  }

  /**
   * Return the set of available features in this environment.
   * Some features of Luxon are not available in all environments. For example, on older browsers, relative time formatting support is not available. Use this function to figure out if that's the case.
   * Keys:
   * * `relative`: whether this environment supports relative time formatting
   * * `localeWeek`: whether this environment supports different weekdays for the start of the week based on the locale
   * @example Info.features() //=> { relative: false, localeWeek: true }
   * @return {Object}
   */
  static features() {
    return { relative: (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.hasRelative)(), localeWeek: (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.hasLocaleWeekInfo)() };
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/interval.js":
/*!********************************************!*\
  !*** ./node_modules/luxon/src/interval.js ***!
  \********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Interval)
/* harmony export */ });
/* harmony import */ var _datetime_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./datetime.js */ "./node_modules/luxon/src/datetime.js");
/* harmony import */ var _duration_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./duration.js */ "./node_modules/luxon/src/duration.js");
/* harmony import */ var _settings_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./settings.js */ "./node_modules/luxon/src/settings.js");
/* harmony import */ var _errors_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./errors.js */ "./node_modules/luxon/src/errors.js");
/* harmony import */ var _impl_invalid_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./impl/invalid.js */ "./node_modules/luxon/src/impl/invalid.js");
/* harmony import */ var _impl_formatter_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./impl/formatter.js */ "./node_modules/luxon/src/impl/formatter.js");
/* harmony import */ var _impl_formats_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./impl/formats.js */ "./node_modules/luxon/src/impl/formats.js");








const INVALID = "Invalid Interval";

// checks if the start is equal to or before the end
function validateStartEnd(start, end) {
  if (!start || !start.isValid) {
    return Interval.invalid("missing or invalid start");
  } else if (!end || !end.isValid) {
    return Interval.invalid("missing or invalid end");
  } else if (end < start) {
    return Interval.invalid(
      "end before start",
      `The end of an interval must be after its start, but you had start=${start.toISO()} and end=${end.toISO()}`
    );
  } else {
    return null;
  }
}

/**
 * An Interval object represents a half-open interval of time, where each endpoint is a {@link DateTime}. Conceptually, it's a container for those two endpoints, accompanied by methods for creating, parsing, interrogating, comparing, transforming, and formatting them.
 *
 * Here is a brief overview of the most commonly used methods and getters in Interval:
 *
 * * **Creation** To create an Interval, use {@link Interval.fromDateTimes}, {@link Interval.after}, {@link Interval.before}, or {@link Interval.fromISO}.
 * * **Accessors** Use {@link Interval#start} and {@link Interval#end} to get the start and end.
 * * **Interrogation** To analyze the Interval, use {@link Interval#count}, {@link Interval#length}, {@link Interval#hasSame}, {@link Interval#contains}, {@link Interval#isAfter}, or {@link Interval#isBefore}.
 * * **Transformation** To create other Intervals out of this one, use {@link Interval#set}, {@link Interval#splitAt}, {@link Interval#splitBy}, {@link Interval#divideEqually}, {@link Interval.merge}, {@link Interval.xor}, {@link Interval#union}, {@link Interval#intersection}, or {@link Interval#difference}.
 * * **Comparison** To compare this Interval to another one, use {@link Interval#equals}, {@link Interval#overlaps}, {@link Interval#abutsStart}, {@link Interval#abutsEnd}, {@link Interval#engulfs}
 * * **Output** To convert the Interval into other representations, see {@link Interval#toString}, {@link Interval#toLocaleString}, {@link Interval#toISO}, {@link Interval#toISODate}, {@link Interval#toISOTime}, {@link Interval#toFormat}, and {@link Interval#toDuration}.
 */
class Interval {
  /**
   * @private
   */
  constructor(config) {
    /**
     * @access private
     */
    this.s = config.start;
    /**
     * @access private
     */
    this.e = config.end;
    /**
     * @access private
     */
    this.invalid = config.invalid || null;
    /**
     * @access private
     */
    this.isLuxonInterval = true;
  }

  /**
   * Create an invalid Interval.
   * @param {string} reason - simple string of why this Interval is invalid. Should not contain parameters or anything else data-dependent
   * @param {string} [explanation=null] - longer explanation, may include parameters and other useful debugging information
   * @return {Interval}
   */
  static invalid(reason, explanation = null) {
    if (!reason) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_3__.InvalidArgumentError("need to specify a reason the Interval is invalid");
    }

    const invalid = reason instanceof _impl_invalid_js__WEBPACK_IMPORTED_MODULE_4__["default"] ? reason : new _impl_invalid_js__WEBPACK_IMPORTED_MODULE_4__["default"](reason, explanation);

    if (_settings_js__WEBPACK_IMPORTED_MODULE_2__["default"].throwOnInvalid) {
      throw new _errors_js__WEBPACK_IMPORTED_MODULE_3__.InvalidIntervalError(invalid);
    } else {
      return new Interval({ invalid });
    }
  }

  /**
   * Create an Interval from a start DateTime and an end DateTime. Inclusive of the start but not the end.
   * @param {DateTime|Date|Object} start
   * @param {DateTime|Date|Object} end
   * @return {Interval}
   */
  static fromDateTimes(start, end) {
    const builtStart = (0,_datetime_js__WEBPACK_IMPORTED_MODULE_0__.friendlyDateTime)(start),
      builtEnd = (0,_datetime_js__WEBPACK_IMPORTED_MODULE_0__.friendlyDateTime)(end);

    const validateError = validateStartEnd(builtStart, builtEnd);

    if (validateError == null) {
      return new Interval({
        start: builtStart,
        end: builtEnd,
      });
    } else {
      return validateError;
    }
  }

  /**
   * Create an Interval from a start DateTime and a Duration to extend to.
   * @param {DateTime|Date|Object} start
   * @param {Duration|Object|number} duration - the length of the Interval.
   * @return {Interval}
   */
  static after(start, duration) {
    const dur = _duration_js__WEBPACK_IMPORTED_MODULE_1__["default"].fromDurationLike(duration),
      dt = (0,_datetime_js__WEBPACK_IMPORTED_MODULE_0__.friendlyDateTime)(start);
    return Interval.fromDateTimes(dt, dt.plus(dur));
  }

  /**
   * Create an Interval from an end DateTime and a Duration to extend backwards to.
   * @param {DateTime|Date|Object} end
   * @param {Duration|Object|number} duration - the length of the Interval.
   * @return {Interval}
   */
  static before(end, duration) {
    const dur = _duration_js__WEBPACK_IMPORTED_MODULE_1__["default"].fromDurationLike(duration),
      dt = (0,_datetime_js__WEBPACK_IMPORTED_MODULE_0__.friendlyDateTime)(end);
    return Interval.fromDateTimes(dt.minus(dur), dt);
  }

  /**
   * Create an Interval from an ISO 8601 string.
   * Accepts `<start>/<end>`, `<start>/<duration>`, and `<duration>/<end>` formats.
   * @param {string} text - the ISO string to parse
   * @param {Object} [opts] - options to pass {@link DateTime#fromISO} and optionally {@link Duration#fromISO}
   * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
   * @return {Interval}
   */
  static fromISO(text, opts) {
    const [s, e] = (text || "").split("/", 2);
    if (s && e) {
      let start, startIsValid;
      try {
        start = _datetime_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromISO(s, opts);
        startIsValid = start.isValid;
      } catch (e) {
        startIsValid = false;
      }

      let end, endIsValid;
      try {
        end = _datetime_js__WEBPACK_IMPORTED_MODULE_0__["default"].fromISO(e, opts);
        endIsValid = end.isValid;
      } catch (e) {
        endIsValid = false;
      }

      if (startIsValid && endIsValid) {
        return Interval.fromDateTimes(start, end);
      }

      if (startIsValid) {
        const dur = _duration_js__WEBPACK_IMPORTED_MODULE_1__["default"].fromISO(e, opts);
        if (dur.isValid) {
          return Interval.after(start, dur);
        }
      } else if (endIsValid) {
        const dur = _duration_js__WEBPACK_IMPORTED_MODULE_1__["default"].fromISO(s, opts);
        if (dur.isValid) {
          return Interval.before(end, dur);
        }
      }
    }
    return Interval.invalid("unparsable", `the input "${text}" can't be parsed as ISO 8601`);
  }

  /**
   * Check if an object is an Interval. Works across context boundaries
   * @param {object} o
   * @return {boolean}
   */
  static isInterval(o) {
    return (o && o.isLuxonInterval) || false;
  }

  /**
   * Returns the start of the Interval
   * @type {DateTime}
   */
  get start() {
    return this.isValid ? this.s : null;
  }

  /**
   * Returns the end of the Interval
   * @type {DateTime}
   */
  get end() {
    return this.isValid ? this.e : null;
  }

  /**
   * Returns whether this Interval's end is at least its start, meaning that the Interval isn't 'backwards'.
   * @type {boolean}
   */
  get isValid() {
    return this.invalidReason === null;
  }

  /**
   * Returns an error code if this Interval is invalid, or null if the Interval is valid
   * @type {string}
   */
  get invalidReason() {
    return this.invalid ? this.invalid.reason : null;
  }

  /**
   * Returns an explanation of why this Interval became invalid, or null if the Interval is valid
   * @type {string}
   */
  get invalidExplanation() {
    return this.invalid ? this.invalid.explanation : null;
  }

  /**
   * Returns the length of the Interval in the specified unit.
   * @param {string} unit - the unit (such as 'hours' or 'days') to return the length in.
   * @return {number}
   */
  length(unit = "milliseconds") {
    return this.isValid ? this.toDuration(...[unit]).get(unit) : NaN;
  }

  /**
   * Returns the count of minutes, hours, days, months, or years included in the Interval, even in part.
   * Unlike {@link Interval#length} this counts sections of the calendar, not periods of time, e.g. specifying 'day'
   * asks 'what dates are included in this interval?', not 'how many days long is this interval?'
   * @param {string} [unit='milliseconds'] - the unit of time to count.
   * @param {Object} opts - options
   * @param {boolean} [opts.useLocaleWeeks=false] - If true, use weeks based on the locale, i.e. use the locale-dependent start of the week; this operation will always use the locale of the start DateTime
   * @return {number}
   */
  count(unit = "milliseconds", opts) {
    if (!this.isValid) return NaN;
    const start = this.start.startOf(unit, opts);
    let end;
    if (opts?.useLocaleWeeks) {
      end = this.end.reconfigure({ locale: start.locale });
    } else {
      end = this.end;
    }
    end = end.startOf(unit, opts);
    return Math.floor(end.diff(start, unit).get(unit)) + (end.valueOf() !== this.end.valueOf());
  }

  /**
   * Returns whether this Interval's start and end are both in the same unit of time
   * @param {string} unit - the unit of time to check sameness on
   * @return {boolean}
   */
  hasSame(unit) {
    return this.isValid ? this.isEmpty() || this.e.minus(1).hasSame(this.s, unit) : false;
  }

  /**
   * Return whether this Interval has the same start and end DateTimes.
   * @return {boolean}
   */
  isEmpty() {
    return this.s.valueOf() === this.e.valueOf();
  }

  /**
   * Return whether this Interval's start is after the specified DateTime.
   * @param {DateTime} dateTime
   * @return {boolean}
   */
  isAfter(dateTime) {
    if (!this.isValid) return false;
    return this.s > dateTime;
  }

  /**
   * Return whether this Interval's end is before the specified DateTime.
   * @param {DateTime} dateTime
   * @return {boolean}
   */
  isBefore(dateTime) {
    if (!this.isValid) return false;
    return this.e <= dateTime;
  }

  /**
   * Return whether this Interval contains the specified DateTime.
   * @param {DateTime} dateTime
   * @return {boolean}
   */
  contains(dateTime) {
    if (!this.isValid) return false;
    return this.s <= dateTime && this.e > dateTime;
  }

  /**
   * "Sets" the start and/or end dates. Returns a newly-constructed Interval.
   * @param {Object} values - the values to set
   * @param {DateTime} values.start - the starting DateTime
   * @param {DateTime} values.end - the ending DateTime
   * @return {Interval}
   */
  set({ start, end } = {}) {
    if (!this.isValid) return this;
    return Interval.fromDateTimes(start || this.s, end || this.e);
  }

  /**
   * Split this Interval at each of the specified DateTimes
   * @param {...DateTime} dateTimes - the unit of time to count.
   * @return {Array}
   */
  splitAt(...dateTimes) {
    if (!this.isValid) return [];
    const sorted = dateTimes
        .map(_datetime_js__WEBPACK_IMPORTED_MODULE_0__.friendlyDateTime)
        .filter((d) => this.contains(d))
        .sort((a, b) => a.toMillis() - b.toMillis()),
      results = [];
    let { s } = this,
      i = 0;

    while (s < this.e) {
      const added = sorted[i] || this.e,
        next = +added > +this.e ? this.e : added;
      results.push(Interval.fromDateTimes(s, next));
      s = next;
      i += 1;
    }

    return results;
  }

  /**
   * Split this Interval into smaller Intervals, each of the specified length.
   * Left over time is grouped into a smaller interval
   * @param {Duration|Object|number} duration - The length of each resulting interval.
   * @return {Array}
   */
  splitBy(duration) {
    const dur = _duration_js__WEBPACK_IMPORTED_MODULE_1__["default"].fromDurationLike(duration);

    if (!this.isValid || !dur.isValid || dur.as("milliseconds") === 0) {
      return [];
    }

    let { s } = this,
      idx = 1,
      next;

    const results = [];
    while (s < this.e) {
      const added = this.start.plus(dur.mapUnits((x) => x * idx));
      next = +added > +this.e ? this.e : added;
      results.push(Interval.fromDateTimes(s, next));
      s = next;
      idx += 1;
    }

    return results;
  }

  /**
   * Split this Interval into the specified number of smaller intervals.
   * @param {number} numberOfParts - The number of Intervals to divide the Interval into.
   * @return {Array}
   */
  divideEqually(numberOfParts) {
    if (!this.isValid) return [];
    return this.splitBy(this.length() / numberOfParts).slice(0, numberOfParts);
  }

  /**
   * Return whether this Interval overlaps with the specified Interval
   * @param {Interval} other
   * @return {boolean}
   */
  overlaps(other) {
    return this.e > other.s && this.s < other.e;
  }

  /**
   * Return whether this Interval's end is adjacent to the specified Interval's start.
   * @param {Interval} other
   * @return {boolean}
   */
  abutsStart(other) {
    if (!this.isValid) return false;
    return +this.e === +other.s;
  }

  /**
   * Return whether this Interval's start is adjacent to the specified Interval's end.
   * @param {Interval} other
   * @return {boolean}
   */
  abutsEnd(other) {
    if (!this.isValid) return false;
    return +other.e === +this.s;
  }

  /**
   * Returns true if this Interval fully contains the specified Interval, specifically if the intersect (of this Interval and the other Interval) is equal to the other Interval; false otherwise.
   * @param {Interval} other
   * @return {boolean}
   */
  engulfs(other) {
    if (!this.isValid) return false;
    return this.s <= other.s && this.e >= other.e;
  }

  /**
   * Return whether this Interval has the same start and end as the specified Interval.
   * @param {Interval} other
   * @return {boolean}
   */
  equals(other) {
    if (!this.isValid || !other.isValid) {
      return false;
    }

    return this.s.equals(other.s) && this.e.equals(other.e);
  }

  /**
   * Return an Interval representing the intersection of this Interval and the specified Interval.
   * Specifically, the resulting Interval has the maximum start time and the minimum end time of the two Intervals.
   * Returns null if the intersection is empty, meaning, the intervals don't intersect.
   * @param {Interval} other
   * @return {Interval}
   */
  intersection(other) {
    if (!this.isValid) return this;
    const s = this.s > other.s ? this.s : other.s,
      e = this.e < other.e ? this.e : other.e;

    if (s >= e) {
      return null;
    } else {
      return Interval.fromDateTimes(s, e);
    }
  }

  /**
   * Return an Interval representing the union of this Interval and the specified Interval.
   * Specifically, the resulting Interval has the minimum start time and the maximum end time of the two Intervals.
   * @param {Interval} other
   * @return {Interval}
   */
  union(other) {
    if (!this.isValid) return this;
    const s = this.s < other.s ? this.s : other.s,
      e = this.e > other.e ? this.e : other.e;
    return Interval.fromDateTimes(s, e);
  }

  /**
   * Merge an array of Intervals into a equivalent minimal set of Intervals.
   * Combines overlapping and adjacent Intervals.
   * @param {Array} intervals
   * @return {Array}
   */
  static merge(intervals) {
    const [found, final] = intervals
      .sort((a, b) => a.s - b.s)
      .reduce(
        ([sofar, current], item) => {
          if (!current) {
            return [sofar, item];
          } else if (current.overlaps(item) || current.abutsStart(item)) {
            return [sofar, current.union(item)];
          } else {
            return [sofar.concat([current]), item];
          }
        },
        [[], null]
      );
    if (final) {
      found.push(final);
    }
    return found;
  }

  /**
   * Return an array of Intervals representing the spans of time that only appear in one of the specified Intervals.
   * @param {Array} intervals
   * @return {Array}
   */
  static xor(intervals) {
    let start = null,
      currentCount = 0;
    const results = [],
      ends = intervals.map((i) => [
        { time: i.s, type: "s" },
        { time: i.e, type: "e" },
      ]),
      flattened = Array.prototype.concat(...ends),
      arr = flattened.sort((a, b) => a.time - b.time);

    for (const i of arr) {
      currentCount += i.type === "s" ? 1 : -1;

      if (currentCount === 1) {
        start = i.time;
      } else {
        if (start && +start !== +i.time) {
          results.push(Interval.fromDateTimes(start, i.time));
        }

        start = null;
      }
    }

    return Interval.merge(results);
  }

  /**
   * Return an Interval representing the span of time in this Interval that doesn't overlap with any of the specified Intervals.
   * @param {...Interval} intervals
   * @return {Array}
   */
  difference(...intervals) {
    return Interval.xor([this].concat(intervals))
      .map((i) => this.intersection(i))
      .filter((i) => i && !i.isEmpty());
  }

  /**
   * Returns a string representation of this Interval appropriate for debugging.
   * @return {string}
   */
  toString() {
    if (!this.isValid) return INVALID;
    return `[${this.s.toISO()} – ${this.e.toISO()})`;
  }

  /**
   * Returns a string representation of this Interval appropriate for the REPL.
   * @return {string}
   */
  [Symbol.for("nodejs.util.inspect.custom")]() {
    if (this.isValid) {
      return `Interval { start: ${this.s.toISO()}, end: ${this.e.toISO()} }`;
    } else {
      return `Interval { Invalid, reason: ${this.invalidReason} }`;
    }
  }

  /**
   * Returns a localized string representing this Interval. Accepts the same options as the
   * Intl.DateTimeFormat constructor and any presets defined by Luxon, such as
   * {@link DateTime.DATE_FULL} or {@link DateTime.TIME_SIMPLE}. The exact behavior of this method
   * is browser-specific, but in general it will return an appropriate representation of the
   * Interval in the assigned locale. Defaults to the system's locale if no locale has been
   * specified.
   * @see https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/DateTimeFormat
   * @param {Object} [formatOpts=DateTime.DATE_SHORT] - Either a DateTime preset or
   * Intl.DateTimeFormat constructor options.
   * @param {Object} opts - Options to override the configuration of the start DateTime.
   * @example Interval.fromISO('2022-11-07T09:00Z/2022-11-08T09:00Z').toLocaleString(); //=> 11/7/2022 – 11/8/2022
   * @example Interval.fromISO('2022-11-07T09:00Z/2022-11-08T09:00Z').toLocaleString(DateTime.DATE_FULL); //=> November 7 – 8, 2022
   * @example Interval.fromISO('2022-11-07T09:00Z/2022-11-08T09:00Z').toLocaleString(DateTime.DATE_FULL, { locale: 'fr-FR' }); //=> 7–8 novembre 2022
   * @example Interval.fromISO('2022-11-07T17:00Z/2022-11-07T19:00Z').toLocaleString(DateTime.TIME_SIMPLE); //=> 6:00 – 8:00 PM
   * @example Interval.fromISO('2022-11-07T17:00Z/2022-11-07T19:00Z').toLocaleString({ weekday: 'short', month: 'short', day: '2-digit', hour: '2-digit', minute: '2-digit' }); //=> Mon, Nov 07, 6:00 – 8:00 p
   * @return {string}
   */
  toLocaleString(formatOpts = _impl_formats_js__WEBPACK_IMPORTED_MODULE_6__.DATE_SHORT, opts = {}) {
    return this.isValid
      ? _impl_formatter_js__WEBPACK_IMPORTED_MODULE_5__["default"].create(this.s.loc.clone(opts), formatOpts).formatInterval(this)
      : INVALID;
  }

  /**
   * Returns an ISO 8601-compliant string representation of this Interval.
   * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
   * @param {Object} opts - The same options as {@link DateTime#toISO}
   * @return {string}
   */
  toISO(opts) {
    if (!this.isValid) return INVALID;
    return `${this.s.toISO(opts)}/${this.e.toISO(opts)}`;
  }

  /**
   * Returns an ISO 8601-compliant string representation of date of this Interval.
   * The time components are ignored.
   * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
   * @return {string}
   */
  toISODate() {
    if (!this.isValid) return INVALID;
    return `${this.s.toISODate()}/${this.e.toISODate()}`;
  }

  /**
   * Returns an ISO 8601-compliant string representation of time of this Interval.
   * The date components are ignored.
   * @see https://en.wikipedia.org/wiki/ISO_8601#Time_intervals
   * @param {Object} opts - The same options as {@link DateTime#toISO}
   * @return {string}
   */
  toISOTime(opts) {
    if (!this.isValid) return INVALID;
    return `${this.s.toISOTime(opts)}/${this.e.toISOTime(opts)}`;
  }

  /**
   * Returns a string representation of this Interval formatted according to the specified format
   * string. **You may not want this.** See {@link Interval#toLocaleString} for a more flexible
   * formatting tool.
   * @param {string} dateFormat - The format string. This string formats the start and end time.
   * See {@link DateTime#toFormat} for details.
   * @param {Object} opts - Options.
   * @param {string} [opts.separator =  ' – '] - A separator to place between the start and end
   * representations.
   * @return {string}
   */
  toFormat(dateFormat, { separator = " – " } = {}) {
    if (!this.isValid) return INVALID;
    return `${this.s.toFormat(dateFormat)}${separator}${this.e.toFormat(dateFormat)}`;
  }

  /**
   * Return a Duration representing the time spanned by this interval.
   * @param {string|string[]} [unit=['milliseconds']] - the unit or units (such as 'hours' or 'days') to include in the duration.
   * @param {Object} opts - options that affect the creation of the Duration
   * @param {string} [opts.conversionAccuracy='casual'] - the conversion system to use
   * @example Interval.fromDateTimes(dt1, dt2).toDuration().toObject() //=> { milliseconds: 88489257 }
   * @example Interval.fromDateTimes(dt1, dt2).toDuration('days').toObject() //=> { days: 1.0241812152777778 }
   * @example Interval.fromDateTimes(dt1, dt2).toDuration(['hours', 'minutes']).toObject() //=> { hours: 24, minutes: 34.82095 }
   * @example Interval.fromDateTimes(dt1, dt2).toDuration(['hours', 'minutes', 'seconds']).toObject() //=> { hours: 24, minutes: 34, seconds: 49.257 }
   * @example Interval.fromDateTimes(dt1, dt2).toDuration('seconds').toObject() //=> { seconds: 88489.257 }
   * @return {Duration}
   */
  toDuration(unit, opts) {
    if (!this.isValid) {
      return _duration_js__WEBPACK_IMPORTED_MODULE_1__["default"].invalid(this.invalidReason);
    }
    return this.e.diff(this.s, unit, opts);
  }

  /**
   * Run mapFn on the interval start and end, returning a new Interval from the resulting DateTimes
   * @param {function} mapFn
   * @return {Interval}
   * @example Interval.fromDateTimes(dt1, dt2).mapEndpoints(endpoint => endpoint.toUTC())
   * @example Interval.fromDateTimes(dt1, dt2).mapEndpoints(endpoint => endpoint.plus({ hours: 2 }))
   */
  mapEndpoints(mapFn) {
    return Interval.fromDateTimes(mapFn(this.s), mapFn(this.e));
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/luxon.js":
/*!*****************************************!*\
  !*** ./node_modules/luxon/src/luxon.js ***!
  \*****************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DateTime: () => (/* reexport safe */ _datetime_js__WEBPACK_IMPORTED_MODULE_0__["default"]),
/* harmony export */   Duration: () => (/* reexport safe */ _duration_js__WEBPACK_IMPORTED_MODULE_1__["default"]),
/* harmony export */   FixedOffsetZone: () => (/* reexport safe */ _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_5__["default"]),
/* harmony export */   IANAZone: () => (/* reexport safe */ _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_6__["default"]),
/* harmony export */   Info: () => (/* reexport safe */ _info_js__WEBPACK_IMPORTED_MODULE_3__["default"]),
/* harmony export */   Interval: () => (/* reexport safe */ _interval_js__WEBPACK_IMPORTED_MODULE_2__["default"]),
/* harmony export */   InvalidZone: () => (/* reexport safe */ _zones_invalidZone_js__WEBPACK_IMPORTED_MODULE_7__["default"]),
/* harmony export */   Settings: () => (/* reexport safe */ _settings_js__WEBPACK_IMPORTED_MODULE_9__["default"]),
/* harmony export */   SystemZone: () => (/* reexport safe */ _zones_systemZone_js__WEBPACK_IMPORTED_MODULE_8__["default"]),
/* harmony export */   VERSION: () => (/* binding */ VERSION),
/* harmony export */   Zone: () => (/* reexport safe */ _zone_js__WEBPACK_IMPORTED_MODULE_4__["default"])
/* harmony export */ });
/* harmony import */ var _datetime_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./datetime.js */ "./node_modules/luxon/src/datetime.js");
/* harmony import */ var _duration_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./duration.js */ "./node_modules/luxon/src/duration.js");
/* harmony import */ var _interval_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./interval.js */ "./node_modules/luxon/src/interval.js");
/* harmony import */ var _info_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./info.js */ "./node_modules/luxon/src/info.js");
/* harmony import */ var _zone_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./zone.js */ "./node_modules/luxon/src/zone.js");
/* harmony import */ var _zones_fixedOffsetZone_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./zones/fixedOffsetZone.js */ "./node_modules/luxon/src/zones/fixedOffsetZone.js");
/* harmony import */ var _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./zones/IANAZone.js */ "./node_modules/luxon/src/zones/IANAZone.js");
/* harmony import */ var _zones_invalidZone_js__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./zones/invalidZone.js */ "./node_modules/luxon/src/zones/invalidZone.js");
/* harmony import */ var _zones_systemZone_js__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./zones/systemZone.js */ "./node_modules/luxon/src/zones/systemZone.js");
/* harmony import */ var _settings_js__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./settings.js */ "./node_modules/luxon/src/settings.js");











const VERSION = "3.5.0";




/***/ }),

/***/ "./node_modules/luxon/src/settings.js":
/*!********************************************!*\
  !*** ./node_modules/luxon/src/settings.js ***!
  \********************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Settings)
/* harmony export */ });
/* harmony import */ var _zones_systemZone_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./zones/systemZone.js */ "./node_modules/luxon/src/zones/systemZone.js");
/* harmony import */ var _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./zones/IANAZone.js */ "./node_modules/luxon/src/zones/IANAZone.js");
/* harmony import */ var _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./impl/locale.js */ "./node_modules/luxon/src/impl/locale.js");
/* harmony import */ var _datetime_js__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./datetime.js */ "./node_modules/luxon/src/datetime.js");
/* harmony import */ var _impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./impl/zoneUtil.js */ "./node_modules/luxon/src/impl/zoneUtil.js");
/* harmony import */ var _impl_util_js__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./impl/util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _impl_digits_js__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./impl/digits.js */ "./node_modules/luxon/src/impl/digits.js");









let now = () => Date.now(),
  defaultZone = "system",
  defaultLocale = null,
  defaultNumberingSystem = null,
  defaultOutputCalendar = null,
  twoDigitCutoffYear = 60,
  throwOnInvalid,
  defaultWeekSettings = null;

/**
 * Settings contains static getters and setters that control Luxon's overall behavior. Luxon is a simple library with few options, but the ones it does have live here.
 */
class Settings {
  /**
   * Get the callback for returning the current timestamp.
   * @type {function}
   */
  static get now() {
    return now;
  }

  /**
   * Set the callback for returning the current timestamp.
   * The function should return a number, which will be interpreted as an Epoch millisecond count
   * @type {function}
   * @example Settings.now = () => Date.now() + 3000 // pretend it is 3 seconds in the future
   * @example Settings.now = () => 0 // always pretend it's Jan 1, 1970 at midnight in UTC time
   */
  static set now(n) {
    now = n;
  }

  /**
   * Set the default time zone to create DateTimes in. Does not affect existing instances.
   * Use the value "system" to reset this value to the system's time zone.
   * @type {string}
   */
  static set defaultZone(zone) {
    defaultZone = zone;
  }

  /**
   * Get the default time zone object currently used to create DateTimes. Does not affect existing instances.
   * The default value is the system's time zone (the one set on the machine that runs this code).
   * @type {Zone}
   */
  static get defaultZone() {
    return (0,_impl_zoneUtil_js__WEBPACK_IMPORTED_MODULE_4__.normalizeZone)(defaultZone, _zones_systemZone_js__WEBPACK_IMPORTED_MODULE_0__["default"].instance);
  }

  /**
   * Get the default locale to create DateTimes with. Does not affect existing instances.
   * @type {string}
   */
  static get defaultLocale() {
    return defaultLocale;
  }

  /**
   * Set the default locale to create DateTimes with. Does not affect existing instances.
   * @type {string}
   */
  static set defaultLocale(locale) {
    defaultLocale = locale;
  }

  /**
   * Get the default numbering system to create DateTimes with. Does not affect existing instances.
   * @type {string}
   */
  static get defaultNumberingSystem() {
    return defaultNumberingSystem;
  }

  /**
   * Set the default numbering system to create DateTimes with. Does not affect existing instances.
   * @type {string}
   */
  static set defaultNumberingSystem(numberingSystem) {
    defaultNumberingSystem = numberingSystem;
  }

  /**
   * Get the default output calendar to create DateTimes with. Does not affect existing instances.
   * @type {string}
   */
  static get defaultOutputCalendar() {
    return defaultOutputCalendar;
  }

  /**
   * Set the default output calendar to create DateTimes with. Does not affect existing instances.
   * @type {string}
   */
  static set defaultOutputCalendar(outputCalendar) {
    defaultOutputCalendar = outputCalendar;
  }

  /**
   * @typedef {Object} WeekSettings
   * @property {number} firstDay
   * @property {number} minimalDays
   * @property {number[]} weekend
   */

  /**
   * @return {WeekSettings|null}
   */
  static get defaultWeekSettings() {
    return defaultWeekSettings;
  }

  /**
   * Allows overriding the default locale week settings, i.e. the start of the week, the weekend and
   * how many days are required in the first week of a year.
   * Does not affect existing instances.
   *
   * @param {WeekSettings|null} weekSettings
   */
  static set defaultWeekSettings(weekSettings) {
    defaultWeekSettings = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_5__.validateWeekSettings)(weekSettings);
  }

  /**
   * Get the cutoff year for whether a 2-digit year string is interpreted in the current or previous century. Numbers higher than the cutoff will be considered to mean 19xx and numbers lower or equal to the cutoff will be considered 20xx.
   * @type {number}
   */
  static get twoDigitCutoffYear() {
    return twoDigitCutoffYear;
  }

  /**
   * Set the cutoff year for whether a 2-digit year string is interpreted in the current or previous century. Numbers higher than the cutoff will be considered to mean 19xx and numbers lower or equal to the cutoff will be considered 20xx.
   * @type {number}
   * @example Settings.twoDigitCutoffYear = 0 // all 'yy' are interpreted as 20th century
   * @example Settings.twoDigitCutoffYear = 99 // all 'yy' are interpreted as 21st century
   * @example Settings.twoDigitCutoffYear = 50 // '49' -> 2049; '50' -> 1950
   * @example Settings.twoDigitCutoffYear = 1950 // interpreted as 50
   * @example Settings.twoDigitCutoffYear = 2050 // ALSO interpreted as 50
   */
  static set twoDigitCutoffYear(cutoffYear) {
    twoDigitCutoffYear = cutoffYear % 100;
  }

  /**
   * Get whether Luxon will throw when it encounters invalid DateTimes, Durations, or Intervals
   * @type {boolean}
   */
  static get throwOnInvalid() {
    return throwOnInvalid;
  }

  /**
   * Set whether Luxon will throw when it encounters invalid DateTimes, Durations, or Intervals
   * @type {boolean}
   */
  static set throwOnInvalid(t) {
    throwOnInvalid = t;
  }

  /**
   * Reset Luxon's global caches. Should only be necessary in testing scenarios.
   * @return {void}
   */
  static resetCaches() {
    _impl_locale_js__WEBPACK_IMPORTED_MODULE_2__["default"].resetCache();
    _zones_IANAZone_js__WEBPACK_IMPORTED_MODULE_1__["default"].resetCache();
    _datetime_js__WEBPACK_IMPORTED_MODULE_3__["default"].resetCache();
    (0,_impl_digits_js__WEBPACK_IMPORTED_MODULE_6__.resetDigitRegexCache)();
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/zone.js":
/*!****************************************!*\
  !*** ./node_modules/luxon/src/zone.js ***!
  \****************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ Zone)
/* harmony export */ });
/* harmony import */ var _errors_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./errors.js */ "./node_modules/luxon/src/errors.js");


/**
 * @interface
 */
class Zone {
  /**
   * The type of zone
   * @abstract
   * @type {string}
   */
  get type() {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }

  /**
   * The name of this zone.
   * @abstract
   * @type {string}
   */
  get name() {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }

  /**
   * The IANA name of this zone.
   * Defaults to `name` if not overwritten by a subclass.
   * @abstract
   * @type {string}
   */
  get ianaName() {
    return this.name;
  }

  /**
   * Returns whether the offset is known to be fixed for the whole year.
   * @abstract
   * @type {boolean}
   */
  get isUniversal() {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }

  /**
   * Returns the offset's common name (such as EST) at the specified timestamp
   * @abstract
   * @param {number} ts - Epoch milliseconds for which to get the name
   * @param {Object} opts - Options to affect the format
   * @param {string} opts.format - What style of offset to return. Accepts 'long' or 'short'.
   * @param {string} opts.locale - What locale to return the offset name in.
   * @return {string}
   */
  offsetName(ts, opts) {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }

  /**
   * Returns the offset's value as a string
   * @abstract
   * @param {number} ts - Epoch milliseconds for which to get the offset
   * @param {string} format - What style of offset to return.
   *                          Accepts 'narrow', 'short', or 'techie'. Returning '+6', '+06:00', or '+0600' respectively
   * @return {string}
   */
  formatOffset(ts, format) {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }

  /**
   * Return the offset in minutes for this zone at the specified timestamp.
   * @abstract
   * @param {number} ts - Epoch milliseconds for which to compute the offset
   * @return {number}
   */
  offset(ts) {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }

  /**
   * Return whether this Zone is equal to another zone
   * @abstract
   * @param {Zone} otherZone - the zone to compare
   * @return {boolean}
   */
  equals(otherZone) {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }

  /**
   * Return whether this Zone is valid.
   * @abstract
   * @type {boolean}
   */
  get isValid() {
    throw new _errors_js__WEBPACK_IMPORTED_MODULE_0__.ZoneIsAbstractError();
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/zones/IANAZone.js":
/*!**************************************************!*\
  !*** ./node_modules/luxon/src/zones/IANAZone.js ***!
  \**************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ IANAZone)
/* harmony export */ });
/* harmony import */ var _impl_util_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../impl/util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _zone_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../zone.js */ "./node_modules/luxon/src/zone.js");



let dtfCache = {};
function makeDTF(zone) {
  if (!dtfCache[zone]) {
    dtfCache[zone] = new Intl.DateTimeFormat("en-US", {
      hour12: false,
      timeZone: zone,
      year: "numeric",
      month: "2-digit",
      day: "2-digit",
      hour: "2-digit",
      minute: "2-digit",
      second: "2-digit",
      era: "short",
    });
  }
  return dtfCache[zone];
}

const typeToPos = {
  year: 0,
  month: 1,
  day: 2,
  era: 3,
  hour: 4,
  minute: 5,
  second: 6,
};

function hackyOffset(dtf, date) {
  const formatted = dtf.format(date).replace(/\u200E/g, ""),
    parsed = /(\d+)\/(\d+)\/(\d+) (AD|BC),? (\d+):(\d+):(\d+)/.exec(formatted),
    [, fMonth, fDay, fYear, fadOrBc, fHour, fMinute, fSecond] = parsed;
  return [fYear, fMonth, fDay, fadOrBc, fHour, fMinute, fSecond];
}

function partsOffset(dtf, date) {
  const formatted = dtf.formatToParts(date);
  const filled = [];
  for (let i = 0; i < formatted.length; i++) {
    const { type, value } = formatted[i];
    const pos = typeToPos[type];

    if (type === "era") {
      filled[pos] = value;
    } else if (!(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.isUndefined)(pos)) {
      filled[pos] = parseInt(value, 10);
    }
  }
  return filled;
}

let ianaZoneCache = {};
/**
 * A zone identified by an IANA identifier, like America/New_York
 * @implements {Zone}
 */
class IANAZone extends _zone_js__WEBPACK_IMPORTED_MODULE_1__["default"] {
  /**
   * @param {string} name - Zone name
   * @return {IANAZone}
   */
  static create(name) {
    if (!ianaZoneCache[name]) {
      ianaZoneCache[name] = new IANAZone(name);
    }
    return ianaZoneCache[name];
  }

  /**
   * Reset local caches. Should only be necessary in testing scenarios.
   * @return {void}
   */
  static resetCache() {
    ianaZoneCache = {};
    dtfCache = {};
  }

  /**
   * Returns whether the provided string is a valid specifier. This only checks the string's format, not that the specifier identifies a known zone; see isValidZone for that.
   * @param {string} s - The string to check validity on
   * @example IANAZone.isValidSpecifier("America/New_York") //=> true
   * @example IANAZone.isValidSpecifier("Sport~~blorp") //=> false
   * @deprecated For backward compatibility, this forwards to isValidZone, better use `isValidZone()` directly instead.
   * @return {boolean}
   */
  static isValidSpecifier(s) {
    return this.isValidZone(s);
  }

  /**
   * Returns whether the provided string identifies a real zone
   * @param {string} zone - The string to check
   * @example IANAZone.isValidZone("America/New_York") //=> true
   * @example IANAZone.isValidZone("Fantasia/Castle") //=> false
   * @example IANAZone.isValidZone("Sport~~blorp") //=> false
   * @return {boolean}
   */
  static isValidZone(zone) {
    if (!zone) {
      return false;
    }
    try {
      new Intl.DateTimeFormat("en-US", { timeZone: zone }).format();
      return true;
    } catch (e) {
      return false;
    }
  }

  constructor(name) {
    super();
    /** @private **/
    this.zoneName = name;
    /** @private **/
    this.valid = IANAZone.isValidZone(name);
  }

  /**
   * The type of zone. `iana` for all instances of `IANAZone`.
   * @override
   * @type {string}
   */
  get type() {
    return "iana";
  }

  /**
   * The name of this zone (i.e. the IANA zone name).
   * @override
   * @type {string}
   */
  get name() {
    return this.zoneName;
  }

  /**
   * Returns whether the offset is known to be fixed for the whole year:
   * Always returns false for all IANA zones.
   * @override
   * @type {boolean}
   */
  get isUniversal() {
    return false;
  }

  /**
   * Returns the offset's common name (such as EST) at the specified timestamp
   * @override
   * @param {number} ts - Epoch milliseconds for which to get the name
   * @param {Object} opts - Options to affect the format
   * @param {string} opts.format - What style of offset to return. Accepts 'long' or 'short'.
   * @param {string} opts.locale - What locale to return the offset name in.
   * @return {string}
   */
  offsetName(ts, { format, locale }) {
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.parseZoneInfo)(ts, format, locale, this.name);
  }

  /**
   * Returns the offset's value as a string
   * @override
   * @param {number} ts - Epoch milliseconds for which to get the offset
   * @param {string} format - What style of offset to return.
   *                          Accepts 'narrow', 'short', or 'techie'. Returning '+6', '+06:00', or '+0600' respectively
   * @return {string}
   */
  formatOffset(ts, format) {
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.formatOffset)(this.offset(ts), format);
  }

  /**
   * Return the offset in minutes for this zone at the specified timestamp.
   * @override
   * @param {number} ts - Epoch milliseconds for which to compute the offset
   * @return {number}
   */
  offset(ts) {
    const date = new Date(ts);

    if (isNaN(date)) return NaN;

    const dtf = makeDTF(this.name);
    let [year, month, day, adOrBc, hour, minute, second] = dtf.formatToParts
      ? partsOffset(dtf, date)
      : hackyOffset(dtf, date);

    if (adOrBc === "BC") {
      year = -Math.abs(year) + 1;
    }

    // because we're using hour12 and https://bugs.chromium.org/p/chromium/issues/detail?id=1025564&can=2&q=%2224%3A00%22%20datetimeformat
    const adjustedHour = hour === 24 ? 0 : hour;

    const asUTC = (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.objToLocalTS)({
      year,
      month,
      day,
      hour: adjustedHour,
      minute,
      second,
      millisecond: 0,
    });

    let asTS = +date;
    const over = asTS % 1000;
    asTS -= over >= 0 ? over : 1000 + over;
    return (asUTC - asTS) / (60 * 1000);
  }

  /**
   * Return whether this Zone is equal to another zone
   * @override
   * @param {Zone} otherZone - the zone to compare
   * @return {boolean}
   */
  equals(otherZone) {
    return otherZone.type === "iana" && otherZone.name === this.name;
  }

  /**
   * Return whether this Zone is valid.
   * @override
   * @type {boolean}
   */
  get isValid() {
    return this.valid;
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/zones/fixedOffsetZone.js":
/*!*********************************************************!*\
  !*** ./node_modules/luxon/src/zones/fixedOffsetZone.js ***!
  \*********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ FixedOffsetZone)
/* harmony export */ });
/* harmony import */ var _impl_util_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../impl/util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _zone_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../zone.js */ "./node_modules/luxon/src/zone.js");



let singleton = null;

/**
 * A zone with a fixed offset (meaning no DST)
 * @implements {Zone}
 */
class FixedOffsetZone extends _zone_js__WEBPACK_IMPORTED_MODULE_1__["default"] {
  /**
   * Get a singleton instance of UTC
   * @return {FixedOffsetZone}
   */
  static get utcInstance() {
    if (singleton === null) {
      singleton = new FixedOffsetZone(0);
    }
    return singleton;
  }

  /**
   * Get an instance with a specified offset
   * @param {number} offset - The offset in minutes
   * @return {FixedOffsetZone}
   */
  static instance(offset) {
    return offset === 0 ? FixedOffsetZone.utcInstance : new FixedOffsetZone(offset);
  }

  /**
   * Get an instance of FixedOffsetZone from a UTC offset string, like "UTC+6"
   * @param {string} s - The offset string to parse
   * @example FixedOffsetZone.parseSpecifier("UTC+6")
   * @example FixedOffsetZone.parseSpecifier("UTC+06")
   * @example FixedOffsetZone.parseSpecifier("UTC-6:00")
   * @return {FixedOffsetZone}
   */
  static parseSpecifier(s) {
    if (s) {
      const r = s.match(/^utc(?:([+-]\d{1,2})(?::(\d{2}))?)?$/i);
      if (r) {
        return new FixedOffsetZone((0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.signedOffset)(r[1], r[2]));
      }
    }
    return null;
  }

  constructor(offset) {
    super();
    /** @private **/
    this.fixed = offset;
  }

  /**
   * The type of zone. `fixed` for all instances of `FixedOffsetZone`.
   * @override
   * @type {string}
   */
  get type() {
    return "fixed";
  }

  /**
   * The name of this zone.
   * All fixed zones' names always start with "UTC" (plus optional offset)
   * @override
   * @type {string}
   */
  get name() {
    return this.fixed === 0 ? "UTC" : `UTC${(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.formatOffset)(this.fixed, "narrow")}`;
  }

  /**
   * The IANA name of this zone, i.e. `Etc/UTC` or `Etc/GMT+/-nn`
   *
   * @override
   * @type {string}
   */
  get ianaName() {
    if (this.fixed === 0) {
      return "Etc/UTC";
    } else {
      return `Etc/GMT${(0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.formatOffset)(-this.fixed, "narrow")}`;
    }
  }

  /**
   * Returns the offset's common name at the specified timestamp.
   *
   * For fixed offset zones this equals to the zone name.
   * @override
   */
  offsetName() {
    return this.name;
  }

  /**
   * Returns the offset's value as a string
   * @override
   * @param {number} ts - Epoch milliseconds for which to get the offset
   * @param {string} format - What style of offset to return.
   *                          Accepts 'narrow', 'short', or 'techie'. Returning '+6', '+06:00', or '+0600' respectively
   * @return {string}
   */
  formatOffset(ts, format) {
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.formatOffset)(this.fixed, format);
  }

  /**
   * Returns whether the offset is known to be fixed for the whole year:
   * Always returns true for all fixed offset zones.
   * @override
   * @type {boolean}
   */
  get isUniversal() {
    return true;
  }

  /**
   * Return the offset in minutes for this zone at the specified timestamp.
   *
   * For fixed offset zones, this is constant and does not depend on a timestamp.
   * @override
   * @return {number}
   */
  offset() {
    return this.fixed;
  }

  /**
   * Return whether this Zone is equal to another zone (i.e. also fixed and same offset)
   * @override
   * @param {Zone} otherZone - the zone to compare
   * @return {boolean}
   */
  equals(otherZone) {
    return otherZone.type === "fixed" && otherZone.fixed === this.fixed;
  }

  /**
   * Return whether this Zone is valid:
   * All fixed offset zones are valid.
   * @override
   * @type {boolean}
   */
  get isValid() {
    return true;
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/zones/invalidZone.js":
/*!*****************************************************!*\
  !*** ./node_modules/luxon/src/zones/invalidZone.js ***!
  \*****************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ InvalidZone)
/* harmony export */ });
/* harmony import */ var _zone_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../zone.js */ "./node_modules/luxon/src/zone.js");


/**
 * A zone that failed to parse. You should never need to instantiate this.
 * @implements {Zone}
 */
class InvalidZone extends _zone_js__WEBPACK_IMPORTED_MODULE_0__["default"] {
  constructor(zoneName) {
    super();
    /**  @private */
    this.zoneName = zoneName;
  }

  /** @override **/
  get type() {
    return "invalid";
  }

  /** @override **/
  get name() {
    return this.zoneName;
  }

  /** @override **/
  get isUniversal() {
    return false;
  }

  /** @override **/
  offsetName() {
    return null;
  }

  /** @override **/
  formatOffset() {
    return "";
  }

  /** @override **/
  offset() {
    return NaN;
  }

  /** @override **/
  equals() {
    return false;
  }

  /** @override **/
  get isValid() {
    return false;
  }
}


/***/ }),

/***/ "./node_modules/luxon/src/zones/systemZone.js":
/*!****************************************************!*\
  !*** ./node_modules/luxon/src/zones/systemZone.js ***!
  \****************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ SystemZone)
/* harmony export */ });
/* harmony import */ var _impl_util_js__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../impl/util.js */ "./node_modules/luxon/src/impl/util.js");
/* harmony import */ var _zone_js__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../zone.js */ "./node_modules/luxon/src/zone.js");



let singleton = null;

/**
 * Represents the local zone for this JavaScript environment.
 * @implements {Zone}
 */
class SystemZone extends _zone_js__WEBPACK_IMPORTED_MODULE_1__["default"] {
  /**
   * Get a singleton instance of the local zone
   * @return {SystemZone}
   */
  static get instance() {
    if (singleton === null) {
      singleton = new SystemZone();
    }
    return singleton;
  }

  /** @override **/
  get type() {
    return "system";
  }

  /** @override **/
  get name() {
    return new Intl.DateTimeFormat().resolvedOptions().timeZone;
  }

  /** @override **/
  get isUniversal() {
    return false;
  }

  /** @override **/
  offsetName(ts, { format, locale }) {
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.parseZoneInfo)(ts, format, locale);
  }

  /** @override **/
  formatOffset(ts, format) {
    return (0,_impl_util_js__WEBPACK_IMPORTED_MODULE_0__.formatOffset)(this.offset(ts), format);
  }

  /** @override **/
  offset(ts) {
    return -new Date(ts).getTimezoneOffset();
  }

  /** @override **/
  equals(otherZone) {
    return otherZone.type === "system";
  }

  /** @override **/
  get isValid() {
    return true;
  }
}


/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Projects/Js/projectsController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});