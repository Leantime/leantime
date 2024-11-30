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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Clients/Js/clientsController"],{

/***/ "./app/Domain/Clients/Js/clientsController.js":
/*!****************************************************!*\
  !*** ./app/Domain/Clients/Js/clientsController.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   initClientTable: () => (/* binding */ initClientTable),
/* harmony export */   initClientTabs: () => (/* binding */ initClientTabs),
/* harmony export */   initDates: () => (/* binding */ initDates)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(i18n__WEBPACK_IMPORTED_MODULE_1__);


var initDates = function initDates() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".projectDateFrom, .projectDateTo").datepicker({
    dateFormat: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("language.dateformat"),
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
var initClientTabs = function initClientTabs() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.clientTabs').tabs();
};
var initClientTable = function initClientTable() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    var size = 100;
    var allProjects = jquery__WEBPACK_IMPORTED_MODULE_0___default()("#allClientsTable").DataTable({
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

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initDates: initDates,
  initClientTabs: initClientTabs,
  initClientTable: initClientTable
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Clients/Js/clientsController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});