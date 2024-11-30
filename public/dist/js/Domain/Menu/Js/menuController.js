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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Menu/Js/menuController"],{

/***/ "./app/Domain/Menu/Js/menuController.js":
/*!**********************************************!*\
  !*** ./app/Domain/Menu/Js/menuController.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   initLeftMenuHamburgerButton: () => (/* binding */ initLeftMenuHamburgerButton),
/* harmony export */   initProjectSelector: () => (/* binding */ initProjectSelector),
/* harmony export */   toggleProjectDropDownList: () => (/* binding */ toggleProjectDropDownList),
/* harmony export */   toggleSubmenu: () => (/* binding */ toggleSubmenu),
/* harmony export */   updateGroupDropdownSetting: () => (/* binding */ updateGroupDropdownSetting)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var _menuRepository__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./menuRepository */ "./app/Domain/Menu/Js/menuRepository.js");



var toggleSubmenu = function toggleSubmenu(submenuName) {
  if (submenuName === "") {
    return;
  }
  var submenuDisplay = jquery__WEBPACK_IMPORTED_MODULE_0___default()('#submenu-' + submenuName).css('display');
  var submenuState = '';
  if (submenuDisplay == 'none') {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#submenu-' + submenuName).css('display', 'block');
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#submenu-icon-' + submenuName).removeClass('fa-angle-right');
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#submenu-icon-' + submenuName).addClass('fa-angle-down');
    submenuState = 'open';
  } else {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#submenu-' + submenuName).css('display', 'none');
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#submenu-icon-' + submenuName).removeClass('fa-angle-down');
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#submenu-icon-' + submenuName).addClass('fa-angle-right');
    submenuState = 'closed';
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/submenu',
    data: {
      submenu: submenuName,
      state: submenuState
    }
  });
};
var initProjectSelector = function initProjectSelector() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).on('click', '.projectselector.dropdown-menu', function (e) {
    e.stopPropagation();
  });
  var currentTab = localStorage.getItem("currentMenuTab");
  var activeTabIndex;
  if (typeof currentTab === 'undefined') {
    activeTabIndex = 0;
  } else {
    activeTabIndex = jquery__WEBPACK_IMPORTED_MODULE_0___default()('.projectSelectorTabs').find('a[href="#' + currentTab + '"]').parent().index();
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.projectSelectorTabs').tabs({
    create: function create(event, ui) {},
    activate: function activate(event, ui) {
      localStorage.setItem("currentMenuTab", ui.newPanel[0].id);
    },
    load: function load() {},
    enable: function enable() {},
    active: activeTabIndex
  });
};
var initLeftMenuHamburgerButton = function initLeftMenuHamburgerButton() {
  var newWidth = 68;
  if (window.innerWidth < 576) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(".mainwrapper").removeClass("menuopen");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(".mainwrapper").addClass("menuclosed");
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.barmenu').click(function () {
    if (jquery__WEBPACK_IMPORTED_MODULE_0___default()(".mainwrapper").hasClass('menuopen')) {
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(".mainwrapper").removeClass("menuopen");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(".mainwrapper").addClass("menuclosed");

      //If it doesn't have the class open, the user wants it to be open.
      (0,_menuRepository__WEBPACK_IMPORTED_MODULE_2__.updateUserMenuSettings)("closed");
    } else {
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(".mainwrapper").removeClass("menuclosed");
      jquery__WEBPACK_IMPORTED_MODULE_0___default()(".mainwrapper").addClass("menuopen");

      //If it doesn't have the class open, the user wants it to be open.
      (0,_menuRepository__WEBPACK_IMPORTED_MODULE_2__.updateUserMenuSettings)("open");
    }
  });
};
var toggleProjectDropDownList = function toggleProjectDropDownList(id) {
  var set = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : "";
  var prefix = arguments.length > 2 ? arguments[2] : undefined;
  //toggler-ID (link to click on open/close)
  //dropdown-ID (dropdown to open/close)

  //Part 1 allow devs to set open/closed state.
  //This means we need to do the opposite of what the current state is.
  if (set === "closed") {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).removeClass("closed");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).removeClass("open");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).addClass("open");
  } else if (set === "open") {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).removeClass("open");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).removeClass("closed");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).addClass("closed");
  }

  //Part 2
  //Do the toggle. If the link has the class open, we need to close it.
  if (jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).hasClass("open")) {
    //Update class on link
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).removeClass("open");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).addClass("closed");

    //Update icon on link
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).find("i").removeClass("fa-angle-down");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).find("i").addClass("fa-angle-right");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-projectSelectorlist-group-" + id).addClass("closed");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-projectSelectorlist-group-" + id).removeClass("open");
    updateGroupDropdownSetting(id, "closed", prefix);
  } else {
    //Update class on link
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).removeClass("closed");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).addClass("open");

    //Update icon on link
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).find("i").removeClass("fa-angle-right");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-toggler-" + id).find("i").addClass("fa-angle-down");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-projectSelectorlist-group-" + id).addClass("open");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + prefix + "-projectSelectorlist-group-" + id).removeClass("closed");
    updateGroupDropdownSetting(id, "open", prefix);
  }
};
var updateGroupDropdownSetting = function updateGroupDropdownSetting(ID, state, prefix) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_1__.appUrl + '/api/submenu',
    data: {
      submenu: prefix + "-projectSelectorlist-group-" + ID,
      state: state
    }
  });
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  toggleSubmenu: toggleSubmenu,
  initProjectSelector: initProjectSelector,
  initLeftMenuHamburgerButton: initLeftMenuHamburgerButton,
  updateGroupDropdownSetting: updateGroupDropdownSetting,
  toggleProjectDropDownList: toggleProjectDropDownList
});

/***/ }),

/***/ "./app/Domain/Menu/Js/menuRepository.js":
/*!**********************************************!*\
  !*** ./app/Domain/Menu/Js/menuRepository.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   updateUserMenuSettings: () => (/* binding */ updateUserMenuSettings)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);

var updateUserMenuSettings = function updateUserMenuSettings(menuStateValue) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default().ajax({
    type: 'PATCH',
    url: leantime.appUrl + '/api/sessions',
    data: {
      menuState: menuStateValue
    }
  }).done(function () {});
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  updateUserMenuSettings: updateUserMenuSettings
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Menu/Js/menuController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});