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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Users/Js/usersController"],{

/***/ "./app/Domain/Users/Js/usersController.js":
/*!************************************************!*\
  !*** ./app/Domain/Users/Js/usersController.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   checkPWStrength: () => (/* binding */ checkPWStrength),
/* harmony export */   clearCroppie: () => (/* binding */ clearCroppie),
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   initUserTable: () => (/* binding */ initUserTable),
/* harmony export */   readURL: () => (/* binding */ readURL),
/* harmony export */   saveCroppie: () => (/* binding */ saveCroppie)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(i18n__WEBPACK_IMPORTED_MODULE_1__);
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var _usersService__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./usersService */ "./app/Domain/Users/Js/usersService.js");




var readURL = function readURL(input) {
  clearCroppie();
  if (input.files && input.files[0]) {
    var reader = new FileReader();
    var profileImg = jquery__WEBPACK_IMPORTED_MODULE_0___default()('#profileImg');
    reader.onload = function (e) {
      //profileImg.attr('src', e.currentTarget.result);

      _uploadResult = profileImg.croppie({
        enableExif: true,
        viewport: {
          width: 175,
          height: 175,
          type: 'circle'
        },
        boundary: {
          width: 200,
          height: 200
        }
      });
      _uploadResult.croppie('bind', {
        url: e.currentTarget.result
      });
      jquery__WEBPACK_IMPORTED_MODULE_0___default()("#previousImage").hide();
    };
    reader.readAsDataURL(input.files[0]);
  }
};
var clearCroppie = function clearCroppie() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#profileImg').croppie('destroy');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#previousImage").show();
};
var saveCroppie = function saveCroppie() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#save-picture').addClass('running');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#profileImg').attr('src', js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__.appUrl + '/images/loaders/loader28.gif');
  _uploadResult.croppie('result', {
    type: "blob",
    circle: true
  }).then(function (result) {
    (0,_usersService__WEBPACK_IMPORTED_MODULE_3__.saveUserPhoto)(result);
  });
};
var initUserTable = function initUserTable() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    var size = 100;
    var allUsersTable = jquery__WEBPACK_IMPORTED_MODULE_0___default()("#allUsersTable").DataTable({
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
var checkPWStrength = function checkPWStrength(pwField) {
  var timeout;

  // traversing the DOM and getting the input and span using their IDs

  var password = document.getElementById(pwField);
  var strengthBadge = document.getElementById('pwStrength');

  // The strong and weak password Regex pattern checker

  var strongPassword = new RegExp('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})');
  var mediumPassword = new RegExp('((?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{6,}))|((?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9])(?=.{8,}))');
  function StrengthChecker(PasswordParameter) {
    if (strongPassword.test(PasswordParameter)) {
      strengthBadge.style.backgroundColor = "#468847";
      strengthBadge.textContent = i18n__WEBPACK_IMPORTED_MODULE_1___default().__('label.strong');
    } else if (mediumPassword.test(PasswordParameter)) {
      strengthBadge.style.backgroundColor = '#f89406';
      strengthBadge.textContent = i18n__WEBPACK_IMPORTED_MODULE_1___default().__('label.medium');
    } else {
      strengthBadge.style.backgroundColor = '#b94a48';
      strengthBadge.textContent = i18n__WEBPACK_IMPORTED_MODULE_1___default().__('label.weak');
    }
  }
  password.addEventListener("input", function () {
    //The badge is hidden by default, so we show it

    strengthBadge.style.display = 'block';
    clearTimeout(timeout);
    timeout = setTimeout(function () {
      return StrengthChecker(password.value);
    }, 500);
    if (password.value.length !== 0) {
      strengthBadge.style.display != 'block';
    } else {
      strengthBadge.style.display = 'none';
    }
  });
};
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  readURL: readURL,
  clearCroppie: clearCroppie,
  saveCroppie: saveCroppie,
  initUserTable: initUserTable,
  checkPWStrength: checkPWStrength
});

/***/ }),

/***/ "./app/Domain/Users/Js/usersRepository.js":
/*!************************************************!*\
  !*** ./app/Domain/Users/Js/usersRepository.js ***!
  \************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   saveUserPhoto: () => (/* binding */ saveUserPhoto),
/* harmony export */   updateUserViewSettings: () => (/* binding */ updateUserViewSettings)
/* harmony export */ });
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_1___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_1__);


var saveUserPhoto = function saveUserPhoto(photo) {
  var formData = new FormData();
  formData.append('file', photo);
  jquery__WEBPACK_IMPORTED_MODULE_1___default().ajax({
    type: 'POST',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/api/users',
    data: formData,
    processData: false,
    contentType: false,
    success: function success(resp) {
      jquery__WEBPACK_IMPORTED_MODULE_1___default()('#save-picture').removeClass('running');
      location.reload();
    },
    error: function error(err) {
      console.log(err);
    }
  });
};
var updateUserViewSettings = function updateUserViewSettings(module, value) {
  jquery__WEBPACK_IMPORTED_MODULE_1___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/api/users',
    data: {
      patchViewSettings: module,
      value: value
    }
  }).done(function () {
    //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async
  });
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  saveUserPhoto: saveUserPhoto,
  updateUserViewSettings: updateUserViewSettings
});

/***/ }),

/***/ "./app/Domain/Users/Js/usersService.js":
/*!*********************************************!*\
  !*** ./app/Domain/Users/Js/usersService.js ***!
  \*********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   saveUserPhoto: () => (/* binding */ saveUserPhoto)
/* harmony export */ });
/* harmony import */ var _usersRepository__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./usersRepository */ "./app/Domain/Users/Js/usersRepository.js");

var saveUserPhoto = _usersRepository__WEBPACK_IMPORTED_MODULE_0__.saveUserPhoto;
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  saveUserPhoto: saveUserPhoto
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Users/Js/usersController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});