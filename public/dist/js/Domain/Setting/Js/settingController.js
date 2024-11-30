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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Setting/Js/settingController"],{

/***/ "./app/Domain/Setting/Js/settingController.js":
/*!****************************************************!*\
  !*** ./app/Domain/Setting/Js/settingController.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   clearCroppie: () => (/* binding */ clearCroppie),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   readURL: () => (/* binding */ readURL),
/* harmony export */   saveCroppie: () => (/* binding */ saveCroppie)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var _settingService__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./settingService */ "./app/Domain/Setting/Js/settingService.js");
/* harmony import */ var js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/app/core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");



var _uploadResult;
var readURL = function readURL(input) {
  clearCroppie();
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    var profileImg = jquery__WEBPACK_IMPORTED_MODULE_0___default()('#logoImg');
    reader.onload = function (e) {
      //profileImg.attr('src', e.currentTarget.result);

      this._uploadResult = profileImg.croppie({
        enableExif: true,
        enforceBoundary: false,
        viewport: {
          width: 220,
          height: 40,
          type: 'square'
        },
        boundary: {
          width: 400,
          height: 200
        }
      });
      this._uploadResult.croppie('bind', {
        url: e.currentTarget.result
      });
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("#previousImage").hide();
    };
    reader.readAsDataURL(input.files[0]);
  }
};
var clearCroppie = function clearCroppie() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#logoImg').croppie('destroy');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#previousImage").show();
};
var saveCroppie = function saveCroppie() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#save-logo').addClass('running');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#logoImg').attr('src', js_app_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/images/loaders/loader28.gif');
  _uploadResult.croppie('result', {
    type: "blob",
    circle: false,
    size: "original",
    quality: 1
  }).then(function (result) {
    (0,_settingService__WEBPACK_IMPORTED_MODULE_1__.saveLogo)(result);
  });
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  readURL: readURL,
  clearCroppie: clearCroppie,
  saveCroppie: saveCroppie
});

/***/ }),

/***/ "./app/Domain/Setting/Js/settingRepository.js":
/*!****************************************************!*\
  !*** ./app/Domain/Setting/Js/settingRepository.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   saveLogo: () => (/* binding */ saveLogo)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");


var saveLogo = function saveLogo(photo) {
  var formData = new FormData();
  formData.append('file', photo);
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'POST',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/setting',
    data: formData,
    processData: false,
    contentType: false,
    success: function success(resp) {
      jquery__WEBPACK_IMPORTED_MODULE_0___default()('#save-logo').removeClass('running');
      location.reload();
    },
    error: function error(err) {
      console.log(err);
    }
  });
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  saveLogo: saveLogo
});

/***/ }),

/***/ "./app/Domain/Setting/Js/settingService.js":
/*!*************************************************!*\
  !*** ./app/Domain/Setting/Js/settingService.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   saveLogo: () => (/* binding */ saveLogo)
/* harmony export */ });
/* harmony import */ var _settingRepository__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./settingRepository */ "./app/Domain/Setting/Js/settingRepository.js");

var saveLogo = _settingRepository__WEBPACK_IMPORTED_MODULE_0__.saveLogo;
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  saveLogo: saveLogo
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Setting/Js/settingController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});