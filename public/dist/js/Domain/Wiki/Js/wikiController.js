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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Wiki/Js/wikiController"],{

/***/ "./app/Domain/Wiki/Js/wikiController.js":
/*!**********************************************!*\
  !*** ./app/Domain/Wiki/Js/wikiController.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   initTree: () => (/* binding */ initTree)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);


//Functions
var initTree = function initTree(id, selectedId) {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(id).jstree({
    "core": {
      "expand_selected_onload": true,
      "themes": {
        "dots": false
      }
    },
    "state": {
      "key": "tree_state"
    },
    "types": {
      "default": {
        "icon": "far fa-file-alt"
      }
    },
    "plugins": ["wholerow", "types", "state"]
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(id).on("ready.jstree", function (e, data) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).jstree("deselect_all");
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).jstree("select_node", "treenode_" + selectedId + "", true);
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).jstree("save_state");
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(id).on('activate_node.jstree', function (e, data) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).jstree("save_state");
    if (data == undefined || data.node == undefined || data.node.id == undefined) {
      return;
    }
    window.location.href = data.node.a_attr.href;
  });
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initTree: initTree
});

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Wiki/Js/wikiController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});