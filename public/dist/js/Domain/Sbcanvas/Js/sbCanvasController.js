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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Sbcanvas/Js/sbCanvasController"],{

/***/ "./app/Domain/Sbcanvas/Js/sbCanvasController.js":
/*!******************************************************!*\
  !*** ./app/Domain/Sbcanvas/Js/sbCanvasController.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

/* provided dependency */ var jQuery = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
leantime.sbCanvasController = function () {
  // To be set
  var canvasName = 'sb';

  // To be implemented
  var setRowHeights = function setRowHeights() {
    var stakeholderRowHeight = 0;
    jQuery("#stakeholderRow div.contentInner").each(function () {
      if (jQuery(this).height() > stakeholderRowHeight) {
        stakeholderRowHeight = jQuery(this).height() + 35;
      }
    });
    var financialsRowHeight = 0;
    jQuery("#financialsRow div.contentInner").each(function () {
      if (jQuery(this).height() > financialsRowHeight) {
        financialsRowHeight = jQuery(this).height() + 35;
      }
    });
    var culturechangeRowHeight = 0;
    jQuery("#culturechangeRow div.contentInner").each(function () {
      if (jQuery(this).height() > culturechangeRowHeight) {
        culturechangeRowHeight = jQuery(this).height() + 35;
      }
    });
    jQuery("#stakeholderRow .column .contentInner").css("height", stakeholderRowHeight);
    jQuery("#financialsRow .column .contentInner").css("height", financialsRowHeight);
    jQuery("#culturechangeRow .column .contentInner").css("height", culturechangeRowHeight);
  };

  // Make public what you want to have public, everything else is private
  return {
    setRowHeights: setRowHeights
  };
}();

/***/ })

},
/******/ __webpack_require__ => { // webpackRuntimeModules
/******/ var __webpack_exec__ = (moduleId) => (__webpack_require__(__webpack_require__.s = moduleId))
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Sbcanvas/Js/sbCanvasController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});