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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/support/replaceSVGColors.module"],{

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

/***/ }),

/***/ "./public/assets/js/app/support/replaceSVGColors.module.mjs":
/*!******************************************************************!*\
  !*** ./public/assets/js/app/support/replaceSVGColors.module.mjs ***!
  \******************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");


var replaceSVGColors = function replaceSVGColors() {
  jquery__WEBPACK_IMPORTED_MODULE_1__(document).ready(function () {
    if (_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.companyColor == '#1b75bb') {
      return;
    }
    jquery__WEBPACK_IMPORTED_MODULE_1__('svg').children().each(function () {
      if (jquery__WEBPACK_IMPORTED_MODULE_1__(this).attr('fill') == '#1b75bb') {
        jquery__WEBPACK_IMPORTED_MODULE_1__(this).attr('fill', _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.companyColor);
      }
    });
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (replaceSVGColors);

/***/ }),

/***/ "./public/assets/js/app/support/snippets.module.mjs":
/*!**********************************************************!*\
  !*** ./public/assets/js/app/support/snippets.module.mjs ***!
  \**********************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");
/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");


var copyUrl = function copyUrl(field) {
  // Get the text field
  var copyText = document.getElementById(field);

  // Select the text field
  copyText.select();
  copyText.setSelectionRange(0, 99999); // For mobile devices

  // Copy the text inside the text field
  navigator.clipboard.writeText(copyText.value);

  // Alert the copied text
  jQuery.growl({
    message: i18n__WEBPACK_IMPORTED_MODULE_0__.__("short_notifications.url_copied"),
    style: "success"
  });
};
var copyToClipboard = function copyToClipboard(content) {
  navigator.clipboard.writeText(content);

  // Alert the copied text
  jQuery.growl({
    message: i18n__WEBPACK_IMPORTED_MODULE_0__.__("short_notifications.url_copied"),
    style: "success"
  });
};
var initConfettiClick = function initConfettiClick() {
  jQuery(".confetti").click(function () {
    confetti.start();
  });
};
var accordionToggle = function accordionToggle(id) {
  var currentLink = jQuery("#accordion_toggle_" + id).find("i.fa").first();
  var submenuName = 'accordion_content-' + id;
  var submenuState = "closed";
  if (currentLink.hasClass("fa-angle-right")) {
    currentLink.removeClass("fa-angle-right");
    currentLink.addClass("fa-angle-down");
    jQuery('#accordion_content-' + id).slideDown("fast");
    submenuState = "open";
  } else {
    currentLink.removeClass("fa-angle-down");
    currentLink.addClass("fa-angle-right");
    jQuery('#accordion_content-' + id).slideUp("fast");
    submenuState = "closed";
  }
  jQuery.ajax({
    type: 'PATCH',
    url: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/submenu',
    data: {
      submenu: submenuName,
      state: submenuState
    }
  });
};
var toggleTheme = function toggleTheme(theme) {
  var themeUrl = jQuery("#themeStyleSheet").attr("href");
  if (theme == "light") {
    themeUrl = themeUrl.replace("dark.css", "light.css");
    jQuery("#themeStyleSheet").attr("href", themeUrl);
  } else if (theme == "dark") {
    themeUrl = themeUrl.replace("light.css", "dark.css");
    jQuery("#themeStyleSheet").attr("href", themeUrl);
  }
};
var toggleFont = function toggleFont(font) {
  jQuery("#fontStyleSetter").html(":root { --primary-font-family: '" + font + "', 'Helvetica Neue', Helvetica, sans-serif; }");
};
var toggleColors = function toggleColors(accent1, accent2) {
  jQuery("#colorSchemeSetter").html(":root { --accent1: " + accent1 + "; --accent2: " + accent2 + "}");
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  copyUrl: copyUrl,
  copyToClipboard: copyToClipboard,
  initConfettiClick: initConfettiClick,
  accordionToggle: accordionToggle,
  toggleTheme: toggleTheme,
  toggleFont: toggleFont,
  toggleColors: toggleColors
});

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./public/assets/js/app/support/replaceSVGColors.module.mjs"), __webpack_exec__("./public/assets/js/app/support/snippets.module.mjs")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});