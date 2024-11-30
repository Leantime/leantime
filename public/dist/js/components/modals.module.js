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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/components/modals.module"],{

/***/ "./public/assets/js/app/components/modals.module.mjs":
/*!***********************************************************!*\
  !*** ./public/assets/js/app/components/modals.module.mjs ***!
  \***********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var htmx_org__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! htmx.org */ "./node_modules/htmx.org/dist/htmx.esm.js");




var setCustomModalCallback = function setCustomModalCallback(callback) {
  if (typeof callback === 'function') {
    window.globalModalCallback = callback;
  }
};
var checksumhUrl = function checksumhUrl(s) {
  return s.split("").reduce(function (a, b) {
    a = (a << 5) - a + b.charCodeAt(0);
    return a & a;
  }, 0);
};
var removeHash = function removeHash() {
  history.pushState("", document.title, window.location.pathname + window.location.search);
};
var getModalUrl = function getModalUrl() {
  var url = window.location.hash.substring(1);
  var urlParts = url.split("/");
  if (urlParts.length > 2 && urlParts[1] !== "tab") {
    return url;
  }
  return false;
};
var openPageModal = function openPageModal(url) {
  jquery__WEBPACK_IMPORTED_MODULE_1__("#modal-wrapper #main-page-modal .modal-loader").show();
  jquery__WEBPACK_IMPORTED_MODULE_1__("#modal-wrapper #main-page-modal .modal-content-loader").removeClass("htmx-request");
  jquery__WEBPACK_IMPORTED_MODULE_1__("#modal-wrapper #main-page-modal .modal-box-content").html("");
  htmx_org__WEBPACK_IMPORTED_MODULE_2__["default"].find("#modal-wrapper #main-page-modal").showModal();
  var baseUrl = _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl.replace(/\/$/, '');
  htmx_org__WEBPACK_IMPORTED_MODULE_2__["default"].ajax('GET', baseUrl + url, {
    event: "trigger-modal",
    target: '#modal-wrapper #main-page-modal .modal-box-content',
    swap: 'innerHTML',
    headers: {
      "Is-Modal": true
    }
  }).then(function (e) {
    history.pushState(null, '', "#" + url);
    jquery__WEBPACK_IMPORTED_MODULE_1__("#modal-wrapper #main-page-modal .modal-loader").hide();
    jquery__WEBPACK_IMPORTED_MODULE_1__("#modal-wrapper #main-page-modal .modal-loader").removeClass("htmx-request");
    htmx_org__WEBPACK_IMPORTED_MODULE_2__["default"].find("#modal-wrapper #main-page-modal").addEventListener("close", function () {
      removeHash();
    });
  });
};
var openHashUrlModal = function openHashUrlModal() {
  var modalUrl = getModalUrl();
  if (modalUrl !== false) {
    openPageModal(modalUrl);
  }
};

/**
 * Closes a dialog.
 *
 * @function closeModal
 * @description Closes a dialog using jQuery.
 * @returns {void}
 */
var closeModal = function closeModal() {
  removeHash();
  htmx_org__WEBPACK_IMPORTED_MODULE_2__["default"].find("#modal-wrapper #main-page-modal").close();
};
var initModalLoader = function initModalLoader() {
  window.addEventListener("HTMX.closemodal", closeModal);

  //Open page url modal on page load and hash change
  jquery__WEBPACK_IMPORTED_MODULE_1__(document).ready(function () {
    window.addEventListener("hashchange", openHashUrlModal);
    openHashUrlModal();
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  openPageModal: openPageModal,
  setCustomModalCallback: setCustomModalCallback,
  closeModal: closeModal,
  initModalLoader: initModalLoader
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./public/assets/js/app/components/modals.module.mjs")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});