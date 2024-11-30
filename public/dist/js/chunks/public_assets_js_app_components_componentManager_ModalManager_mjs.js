"use strict";
(self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["public_assets_js_app_components_componentManager_ModalManager_mjs"],{

/***/ "./public/assets/js/app/components/componentManager/BaseComponentManager.mjs":
/*!***********************************************************************************!*\
  !*** ./public/assets/js/app/components/componentManager/BaseComponentManager.mjs ***!
  \***********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   BaseComponentManager: () => (/* binding */ BaseComponentManager)
/* harmony export */ });
/* harmony import */ var htmx_org__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! htmx.org */ "./node_modules/htmx.org/dist/htmx.esm.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }

var BaseComponentManager = /*#__PURE__*/function () {
  function BaseComponentManager() {
    _classCallCheck(this, BaseComponentManager);
    this.instances = new Map();
    this.setupHtmxListeners();
  }
  return _createClass(BaseComponentManager, [{
    key: "setupHtmxListeners",
    value: function setupHtmxListeners() {
      var _this = this;
      htmx_org__WEBPACK_IMPORTED_MODULE_0__["default"].on('htmx:beforeCleanupElement', function (evt) {
        _this.cleanupElements(evt.detail.element);
      });
    }
  }, {
    key: "cleanupElements",
    value: function cleanupElements(parentElement) {
      var _this2 = this;
      if (!parentElement) {
        console.warn('Attempted to cleanup undefined parent element');
        return;
      }
      var elements = this.findElements(parentElement);
      elements === null || elements === void 0 || elements.forEach(function (element) {
        _this2.destroyInstance(element);
      });
    }
  }, {
    key: "initializeComponent",
    value: function initializeComponent(element) {
      var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      if (this.instances.has(element)) {
        console.warn("".concat(this.constructor.name, ": Component already initialized for element:"), element);
        return this.instances.get(element);
      }
      try {
        var instance = this.createInstance(element, config);
        if (instance) {
          this.instances.set(element, instance);
          this.dispatchEvent('component:initialized', {
            element: element,
            instance: instance
          });
        }
        return instance;
      } catch (error) {
        console.error("".concat(this.constructor.name, ": Failed to initialize component:"), error);
        return null;
      }
    }
  }, {
    key: "destroyInstance",
    value: function destroyInstance(element) {
      if (!element) return;
      var instance = this.instances.get(element);
      if (instance) {
        this.dispatchEvent('component:beforeDestroy', {
          element: element,
          instance: instance
        });
        this.cleanup(instance);
        this.instances["delete"](element);
        this.dispatchEvent('component:destroyed', {
          element: element
        });
      }
    }
  }, {
    key: "getInstance",
    value: function getInstance(element) {
      return this.instances.get(element);
    }

    // Methods to be implemented by child classes
  }, {
    key: "findElements",
    value: function findElements(parentElement) {
      throw new Error('findElements must be implemented by child class');
    }
  }, {
    key: "createInstance",
    value: function createInstance(element, config) {
      throw new Error('createInstance must be implemented by child class');
    }
  }, {
    key: "cleanup",
    value: function cleanup(instance) {
      throw new Error('cleanup must be implemented by child class');
    }

    // Event handling
  }, {
    key: "dispatchEvent",
    value: function dispatchEvent(eventName, detail) {
      var event = new CustomEvent(eventName, {
        detail: detail,
        bubbles: true,
        cancelable: true
      });
      document.dispatchEvent(event);
    }
  }]);
}();

/***/ }),

/***/ "./public/assets/js/app/components/componentManager/ModalManager.mjs":
/*!***************************************************************************!*\
  !*** ./public/assets/js/app/components/componentManager/ModalManager.mjs ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   modalManager: () => (/* binding */ modalManager)
/* harmony export */ });
/* harmony import */ var _BaseComponentManager_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./BaseComponentManager.mjs */ "./public/assets/js/app/components/componentManager/BaseComponentManager.mjs");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }
function ownKeys(e, r) { var t = Object.keys(e); if (Object.getOwnPropertySymbols) { var o = Object.getOwnPropertySymbols(e); r && (o = o.filter(function (r) { return Object.getOwnPropertyDescriptor(e, r).enumerable; })), t.push.apply(t, o); } return t; }
function _objectSpread(e) { for (var r = 1; r < arguments.length; r++) { var t = null != arguments[r] ? arguments[r] : {}; r % 2 ? ownKeys(Object(t), !0).forEach(function (r) { _defineProperty(e, r, t[r]); }) : Object.getOwnPropertyDescriptors ? Object.defineProperties(e, Object.getOwnPropertyDescriptors(t)) : ownKeys(Object(t)).forEach(function (r) { Object.defineProperty(e, r, Object.getOwnPropertyDescriptor(t, r)); }); } return e; }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _classCallCheck(a, n) { if (!(a instanceof n)) throw new TypeError("Cannot call a class as a function"); }
function _defineProperties(e, r) { for (var t = 0; t < r.length; t++) { var o = r[t]; o.enumerable = o.enumerable || !1, o.configurable = !0, "value" in o && (o.writable = !0), Object.defineProperty(e, _toPropertyKey(o.key), o); } }
function _createClass(e, r, t) { return r && _defineProperties(e.prototype, r), t && _defineProperties(e, t), Object.defineProperty(e, "prototype", { writable: !1 }), e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }
function _callSuper(t, o, e) { return o = _getPrototypeOf(o), _possibleConstructorReturn(t, _isNativeReflectConstruct() ? Reflect.construct(o, e || [], _getPrototypeOf(t).constructor) : o.apply(t, e)); }
function _possibleConstructorReturn(t, e) { if (e && ("object" == _typeof(e) || "function" == typeof e)) return e; if (void 0 !== e) throw new TypeError("Derived constructors may only return object or undefined"); return _assertThisInitialized(t); }
function _assertThisInitialized(e) { if (void 0 === e) throw new ReferenceError("this hasn't been initialised - super() hasn't been called"); return e; }
function _isNativeReflectConstruct() { try { var t = !Boolean.prototype.valueOf.call(Reflect.construct(Boolean, [], function () {})); } catch (t) {} return (_isNativeReflectConstruct = function _isNativeReflectConstruct() { return !!t; })(); }
function _getPrototypeOf(t) { return _getPrototypeOf = Object.setPrototypeOf ? Object.getPrototypeOf.bind() : function (t) { return t.__proto__ || Object.getPrototypeOf(t); }, _getPrototypeOf(t); }
function _inherits(t, e) { if ("function" != typeof e && null !== e) throw new TypeError("Super expression must either be null or a function"); t.prototype = Object.create(e && e.prototype, { constructor: { value: t, writable: !0, configurable: !0 } }), Object.defineProperty(t, "prototype", { writable: !1 }), e && _setPrototypeOf(t, e); }
function _setPrototypeOf(t, e) { return _setPrototypeOf = Object.setPrototypeOf ? Object.setPrototypeOf.bind() : function (t, e) { return t.__proto__ = e, t; }, _setPrototypeOf(t, e); }

var ModalManager = /*#__PURE__*/function (_BaseComponentManager) {
  function ModalManager() {
    var _this;
    _classCallCheck(this, ModalManager);
    _this = _callSuper(this, ModalManager);
    _this.modalStack = [];
    _this.setupKeyboardListener();
    return _this;
  }
  _inherits(ModalManager, _BaseComponentManager);
  return _createClass(ModalManager, [{
    key: "findElements",
    value: function findElements(parentElement) {
      try {
        return (parentElement === null || parentElement === void 0 ? void 0 : parentElement.querySelectorAll('[data-component="modal"]')) || [];
      } catch (error) {
        console.error('Error finding modal elements:', error);
        return [];
      }
    }
  }, {
    key: "createInstance",
    value: function createInstance(element) {
      var _this2 = this;
      var config = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : {};
      var defaultConfig = _objectSpread({
        closeOnEscape: true,
        closeOnBackdrop: true,
        onOpen: function onOpen() {},
        onClose: function onClose() {}
      }, config);
      var modal = {
        element: element,
        config: defaultConfig,
        isOpen: false,
        zIndex: 1000 + this.modalStack.length,
        open: function open() {
          modal.isOpen = true;
          element.style.display = 'block';
          element.style.zIndex = modal.zIndex;
          _this2.modalStack.push(modal);
          modal.config.onOpen();
          _this2.dispatchEvent('modal:opened', {
            modal: modal
          });
        },
        close: function close() {
          modal.isOpen = false;
          element.style.display = 'none';
          var index = _this2.modalStack.indexOf(modal);
          if (index > -1) {
            _this2.modalStack.splice(index, 1);
          }
          modal.config.onClose();
          _this2.dispatchEvent('modal:closed', {
            modal: modal
          });
        }
      };
      this.setupModalListeners(modal);
      return modal;
    }
  }, {
    key: "setupModalListeners",
    value: function setupModalListeners(modal) {
      var closeButton = modal.element.querySelector('[data-modal-close]');
      if (closeButton) {
        closeButton.addEventListener('click', function () {
          return modal.close();
        });
      }
      if (modal.config.closeOnBackdrop) {
        modal.element.addEventListener('click', function (e) {
          if (e.target === modal.element) {
            modal.close();
          }
        });
      }
    }
  }, {
    key: "setupKeyboardListener",
    value: function setupKeyboardListener() {
      var _this3 = this;
      document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
          var topModal = _this3.modalStack[_this3.modalStack.length - 1];
          if (topModal !== null && topModal !== void 0 && topModal.config.closeOnEscape) {
            topModal.close();
          }
        }
      });
    }
  }, {
    key: "cleanup",
    value: function cleanup(instance) {
      if (!instance) {
        console.warn('Attempted to cleanup undefined modal instance');
        return;
      }
      instance.close();
    }
  }, {
    key: "closeAll",
    value: function closeAll() {
      _toConsumableArray(this.modalStack).forEach(function (modal) {
        return modal.close();
      });
    }
  }]);
}(_BaseComponentManager_mjs__WEBPACK_IMPORTED_MODULE_0__.BaseComponentManager);
var modalManager = new ModalManager();
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (modalManager);

/***/ })

}]);