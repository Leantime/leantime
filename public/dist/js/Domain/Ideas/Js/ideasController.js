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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Ideas/Js/ideasController"],{

/***/ "./app/Domain/Ideas/Js/ideasController.js":
/*!************************************************!*\
  !*** ./app/Domain/Ideas/Js/ideasController.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   initBoardControlModal: () => (/* binding */ initBoardControlModal),
/* harmony export */   initIdeaKanban: () => (/* binding */ initIdeaKanban),
/* harmony export */   initMasonryWall: () => (/* binding */ initMasonryWall),
/* harmony export */   initWallImageModals: () => (/* binding */ initWallImageModals),
/* harmony export */   setKanbanHeights: () => (/* binding */ setKanbanHeights)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/app/core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");



var initMasonryWall = function initMasonryWall() {
  var $grid = jquery__WEBPACK_IMPORTED_MODULE_0___default()('#ideaMason').packery({
    // options
    itemSelector: '.ticketBox',
    columnWidth: 260,
    isResizable: true
  });
  $grid.imagesLoaded().progress(function () {
    $grid.packery('layout');
  });
  var $items = $grid.find('.ticketBox').draggable({
    start: function start(event, ui) {
      ui.helper.addClass('tilt');
      tilt_direction(ui.helper);
    },
    stop: function stop(event, ui) {
      ui.helper.removeClass("tilt");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").unbind('mousemove', ui.helper.data("move_handler"));
      ui.helper.removeData("move_handler");
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
  // bind drag events to Packery
  $grid.packery('bindUIDraggableEvents', $items);
  function orderItems() {
    var ideaSort = [];
    var itemElems = $grid.packery('getItemElements');
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(itemElems).each(function (i, itemElem) {
      var sortIndex = i + 1;
      var ideaId = jquery__WEBPACK_IMPORTED_MODULE_0___default()(itemElem).attr("data-value");
      ideaSort.push({
        "id": ideaId,
        "sortIndex": sortIndex
      });
    });

    // POST to server using $.post or $.ajax
    jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
      type: 'POST',
      url: js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/api/ideas',
      data: {
        action: "ideaSort",
        payload: ideaSort
      }
    });
  }
  $grid.on('dragItemPositioned', orderItems);
};
var initBoardControlModal = function initBoardControlModal() {};
var initWallImageModals = function initWallImageModals() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.mainIdeaContent img').each(function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).wrap("<a href='" + jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).attr("src") + "' class='imageModal'></a>");
  });
};
var setKanbanHeights = function setKanbanHeights() {
  var maxHeight = 0;
  var height = jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").height() - 320;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableIdeaKanban .column .contentInner").css("height", height);
};
var initIdeaKanban = function initIdeaKanban(statusList) {
  // jQuery("#sortableIdeaKanban").disableSelection();
  console.log('update');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableIdeaKanban .ticketBox").hover(function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).css("background", "var(--kanban-card-hover)");
  }, function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).css("background", "var(--kanban-card-bg)");
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#sortableIdeaKanban .contentInner").sortable({
    connectWith: ".contentInner",
    items: "> .moveable",
    tolerance: 'pointer',
    placeholder: "ui-state-highlight",
    forcePlaceholderSize: true,
    cancel: ".portlet-toggle, .dropdown-toggle, .dropdown-menu, .inlineDropDownContainer, .dropdown-bottom",
    start: function start(event, ui) {
      ui.item.addClass('tilt');
      tilt_direction(ui.item);
    },
    stop: function stop(event, ui) {
      ui.item.removeClass("tilt");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").unbind('mousemove', ui.item.data("move_handler"));
      ui.item.removeData("move_handler");
    },
    update: function update(event, ui) {
      var statusPostData = {
        action: "statusUpdate",
        payload: {}
      };
      for (var i = 0; i < statusList.length; i++) {
        if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(".contentInner.status_" + statusList[i]).length) {
          statusPostData.payload[statusList[i]] = jquery__WEBPACK_IMPORTED_MODULE_0___default()(".contentInner.status_" + statusList[i]).sortable('serialize');
        }
      }

      // POST to server using $.post or $.ajax
      jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
        type: 'POST',
        url: js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/api/ideas',
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

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initMasonryWall: initMasonryWall,
  initBoardControlModal: initBoardControlModal,
  initWallImageModals: initWallImageModals,
  setKanbanHeights: setKanbanHeights,
  initIdeaKanban: initIdeaKanban
});

/***/ }),

/***/ "i18n":
/*!***************************************!*\
  !*** external "window.leantime.i18n" ***!
  \***************************************/
/***/ ((module) => {

module.exports = __WEBPACK_EXTERNAL_MODULE_i18n__;

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

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Ideas/Js/ideasController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});