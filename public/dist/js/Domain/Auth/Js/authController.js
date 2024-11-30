"use strict";
(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else {
		var a = factory();
		for(var i in a) (typeof exports === 'object' ? exports : root)[i] = a[i];
	}
})(self, () => {
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Auth/Js/authController"],{

/***/ "./app/Domain/Auth/Js/authController.js":
/*!**********************************************!*\
  !*** ./app/Domain/Auth/Js/authController.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   makeInputReadonly: () => (/* binding */ makeInputReadonly)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }

var makeInputReadonly = function makeInputReadonly(container) {
  if (_typeof(container) === undefined) {
    container = "body";
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find("input").not(".filterBar input").prop("readonly", true);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find("input").not(".filterBar input").prop("disabled", true);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find("select").not(".filterBar select, .mainSprintSelector").prop("readonly", true);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find("select").not(".filterBar select, .mainSprintSelector").prop("disabled", true);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find("textarea").not(".filterBar textarea").prop("disabled", true);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find("a.delete").remove();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".quickAddLink").hide();
  if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".complexEditor").length) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".complexEditor").each(function (element) {
      if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).tinymce()) {
        jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).tinymce().getBody().setAttribute('contenteditable', "false");
      }
    });
  }
  if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".tinymceSimple").length) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".tinymceSimple").each(function (element) {
      if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).tinymce()) {
        jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).tinymce().getBody().setAttribute('contenteditable', "false");
      }
    });
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".tox-editor-header").hide();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".tox-statusbar").hide();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".ticketDropdown a").removeAttr("data-toggle");
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#mainToggler").hide();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commentBox").hide();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".deleteComment, .replyButton").hide();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(container).find(".dropdown i").removeClass('fa-caret-down');
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  makeInputReadonly: makeInputReadonly
});

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Auth/Js/authController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});