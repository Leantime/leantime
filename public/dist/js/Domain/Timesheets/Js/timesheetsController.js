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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Timesheets/Js/timesheetsController"],{

/***/ "./app/Domain/Timesheets/Js/timesheetsController.js":
/*!**********************************************************!*\
  !*** ./app/Domain/Timesheets/Js/timesheetsController.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   initTimesheetsTable: () => (/* binding */ initTimesheetsTable)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(i18n__WEBPACK_IMPORTED_MODULE_1__);


var closeModal = false;
var initTimesheetsTable = function initTimesheetsTable(groupBy) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    var allTimesheets = jquery__WEBPACK_IMPORTED_MODULE_0___default()("#allTimesheetsTable").DataTable({
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
        },
        "buttons": {
          colvis: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.buttons.colvis"),
          csv: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("datatables.buttons.download")
        }
      },
      "dom": '<"top">rt<"bottom"ilp><"clear">',
      "searching": false,
      "stateSave": true,
      "displayLength": 100
    });
    var buttons = new (jquery__WEBPACK_IMPORTED_MODULE_0___default().fn).dataTable.Buttons(allTimesheets, {
      buttons: [{
        extend: 'csvHtml5',
        title: i18n__WEBPACK_IMPORTED_MODULE_1___default().__("label.filename_fileexport"),
        charset: 'utf-8',
        bom: true,
        exportOptions: {
          format: {
            body: function body(data, row, column, node) {
              if (typeof jquery__WEBPACK_IMPORTED_MODULE_0___default()(node).data('order') !== 'undefined') {
                data = jquery__WEBPACK_IMPORTED_MODULE_0___default()(node).data('order');
              }
              return data;
            }
          }
        }
      }, {
        extend: 'colvis',
        columns: ':not(.noVis)'
      }]
    }).container().appendTo(jquery__WEBPACK_IMPORTED_MODULE_0___default()('#tableButtons'));
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#allTimesheetsTable').on('column-visibility.dt', function (e, settings, column, state) {
      allTimesheets.draw(false);
    });
  });
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initTimesheetsTable: initTimesheetsTable
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Timesheets/Js/timesheetsController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});