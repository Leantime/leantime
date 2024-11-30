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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Swotcanvas/Js/swotCanvasController"],{

/***/ "./app/Domain/Swotcanvas/Js/swotCanvasController.js":
/*!**********************************************************!*\
  !*** ./app/Domain/Swotcanvas/Js/swotCanvasController.js ***!
  \**********************************************************/
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


var canvasName = 'swot';
var setRowHeights = function setRowHeights() {
  var nbRows = 2;
  var rowHeight = jquery__WEBPACK_IMPORTED_MODULE_0___default()("html").height() - 320 - 20 * nbRows - 25;
  var firstRowHeight = rowHeight / nbRows;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRow div.contentInner").each(function () {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() > firstRowHeight) {
      firstRowHeight = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() + 50;
    }
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#firstRow .column .contentInner").css("height", firstRowHeight);
  var secondRowHeight = rowHeight / nbRows;
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#secondRow div.contentInner").each(function () {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() > secondRowHeight) {
      secondRowHeight = jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).height() + 50;
    }
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#secondRow .column .contentInner").css("height", secondRowHeight);
};
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

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Swotcanvas/Js/swotCanvasController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});