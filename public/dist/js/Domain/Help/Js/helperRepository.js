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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Help/Js/helperRepository"],{

/***/ "./app/Domain/Help/Js/helperRepository.js":
/*!************************************************!*\
  !*** ./app/Domain/Help/Js/helperRepository.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   startingTour: () => (/* binding */ startingTour),
/* harmony export */   stopTour: () => (/* binding */ stopTour),
/* harmony export */   updateUserModalSettings: () => (/* binding */ updateUserModalSettings)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! js/app/core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");


var updateUserModalSettings = function updateUserModalSettings(module) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/users',
    data: {
      settings: module,
      patchModalSettings: 1
    }
  }).done(function () {
    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
  });
};
var startingTour = function startingTour() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/sessions',
    data: {
      tourActive: 1
    }
  }).done(function () {
    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
  });
};
var stopTour = function stopTour() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/sessions',
    data: {
      tourActive: 0
    }
  }).done(function () {
    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  updateUserModalSettings: updateUserModalSettings,
  startingTour: startingTour,
  stopTour: stopTour
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

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Help/Js/helperRepository.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});