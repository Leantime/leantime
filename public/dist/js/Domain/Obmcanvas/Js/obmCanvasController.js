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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Obmcanvas/Js/obmCanvasController"],{

/***/ "./app/Domain/Obmcanvas/Js/obmCanvasController.js":
/*!********************************************************!*\
  !*** ./app/Domain/Obmcanvas/Js/obmCanvasController.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   setRowHeights: () => (/* binding */ setRowHeights)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");




// To be set
var canvasName = 'obm';

// To be implemented
var setRowHeights = function setRowHeights() {
  var nbRows = 3;
  var rowHeight = jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").height() - 320 - 20 * nbRows;
  var firstRowHeight = rowHeight * 0.6666;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRow div.contentInner").each(function () {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() > firstRowHeight) {
      firstRowHeight = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() + 50;
    }
  });
  var firstRowHeightTop = firstRowHeight * 0.5;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRowTop div.contentInner").each(function () {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() > firstRowHeightTop) {
      firstRowHeightTop = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() + 50;
    }
  });
  var firstRowHeightBottom = firstRowHeight * 0.5;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRowBottom div.contentInner").each(function () {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() > firstRowHeightBottom) {
      firstRowHeightBottom = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() + 50;
    }
  });
  if (firstRowHeightTop + firstRowHeightBottom + 25 > firstRowHeight) {
    firstRowHeight = firstRowHeightTop + firstRowHeightBottom + 50;
  }
  var secondRowHeight = rowHeight * 0.333;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#secondRow div.contentInner").each(function () {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() > secondRowHeight) {
      secondRowHeight = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() + 50;
    }
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRow .column .contentInner").css("height", firstRowHeight);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRowTop div.contentInner").css("height", firstRowHeightTop);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRowBottom div.contentInner").css("height", firstRowHeightBottom);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#secondRow .column .contentInner").css("height", secondRowHeight);
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  setRowHeights: setRowHeights
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Obmcanvas/Js/obmCanvasController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});