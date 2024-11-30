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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Widgets/Js/widgetController"],{

/***/ "./app/Domain/Widgets/Js/widgetController.js":
/*!***************************************************!*\
  !*** ./app/Domain/Widgets/Js/widgetController.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   initGrid: () => (/* binding */ initGrid),
/* harmony export */   resizeWidget: () => (/* binding */ resizeWidget),
/* harmony export */   widgetController: () => (/* binding */ widgetController)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var gridstack__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! gridstack */ "./node_modules/gridstack/dist/gridstack.js");
/* harmony import */ var js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! js/app/core/instance-info.module */ "./public/assets/js/app/core/instance-info.module.mjs");
/* provided dependency */ var htmx = __webpack_require__(/*! htmx.org */ "./node_modules/htmx.org/dist/htmx.esm.js");
function _toConsumableArray(r) { return _arrayWithoutHoles(r) || _iterableToArray(r) || _unsupportedIterableToArray(r) || _nonIterableSpread(); }
function _nonIterableSpread() { throw new TypeError("Invalid attempt to spread non-iterable instance.\nIn order to be iterable, non-array objects must have a [Symbol.iterator]() method."); }
function _unsupportedIterableToArray(r, a) { if (r) { if ("string" == typeof r) return _arrayLikeToArray(r, a); var t = {}.toString.call(r).slice(8, -1); return "Object" === t && r.constructor && (t = r.constructor.name), "Map" === t || "Set" === t ? Array.from(r) : "Arguments" === t || /^(?:Ui|I)nt(?:8|16|32)(?:Clamped)?Array$/.test(t) ? _arrayLikeToArray(r, a) : void 0; } }
function _iterableToArray(r) { if ("undefined" != typeof Symbol && null != r[Symbol.iterator] || null != r["@@iterator"]) return Array.from(r); }
function _arrayWithoutHoles(r) { if (Array.isArray(r)) return _arrayLikeToArray(r); }
function _arrayLikeToArray(r, a) { (null == a || a > r.length) && (a = r.length); for (var e = 0, n = Array(a); e < a; e++) n[e] = r[e]; return n; }



var grid = [];
var findAvailablePosition = function findAvailablePosition(widget, grid) {
  var x = widget.gridX || 0;
  var y = widget.gridY || 0;
  var width = widget.gridWidth || 2;
  var height = widget.gridHeight || 2;

  // Try the preferred position first
  if (grid.willItFit({
    x: x,
    y: y,
    width: width,
    height: height
  })) {
    return {
      x: x,
      y: y
    };
  }

  // If preferred position is occupied, find next available spot
  var maxY = Math.max.apply(Math, _toConsumableArray(grid.engine.nodes.map(function (n) {
    return n.y + n.h;
  })).concat([0]));

  // Try positions from top to bottom
  for (var newY = 0; newY <= maxY + 1; newY++) {
    for (var newX = 0; newX <= 12 - width; newX++) {
      if (grid.willItFit({
        newX: newX,
        newY: newY,
        width: width,
        height: height
      })) {
        return {
          x: newX,
          y: newY
        };
      }
    }
  }
  return {
    x: 0,
    y: maxY + 1
  }; // Fallback to bottom
};
gridstack__WEBPACK_IMPORTED_MODULE_1__.GridStack.renderCB = function (el, w) {
  if (w.content) {
    // Using DOMPurify to sanitize content if available
    if (typeof DOMPurify !== 'undefined') {
      el.innerHTML = DOMPurify.sanitize(w.content);
    }
  }
};
var initGrid = function initGrid() {
  grid = gridstack__WEBPACK_IMPORTED_MODULE_1__.GridStack.init({
    margin: '0px 15px 15px 0px',
    handle: ".grid-handler-top",
    minRow: 2,
    cellHeight: '30px',
    "float": false,
    animate: false,
    disableOneColumnMode: true,
    draggable: {
      handle: '.grid-handler-top',
      appendTo: 'body',
      scroll: true,
      scrollSensitivity: 20,
      scrollSpeed: 10
    },
    lazyLoad: false,
    disableAutoScroll: false
  });
  grid.on('dragstop', function (event, item) {
    saveGrid();
  });
  grid.on('resizestop', function (Event, item) {
    saveGrid();
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".grid-stack-item").each(function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).find(".removeWidget").click(function () {
      removeWidget(jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).closest(".grid-stack-item")[0]);
    });
    jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).find(".fitContent").click(function () {
      resizeWidget(jquery__WEBPACK_IMPORTED_MODULE_0___default()(this).closest(".grid-stack-item")[0]);
    });
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(document).ready(function () {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()("#gridBoard").css("opacity", 1);
  });
};
var saveGrid = function saveGrid() {
  var items = grid.save();

  // Sort items by Y position first, then X position
  items.sort(function (a, b) {
    return a.y === b.y ? a.x - b.x : a.y - b.y;
  });
  var visibilityData = null;
  if (arguments.length > 0 && arguments[0].action === "toggleWidget") {
    visibilityData = {
      widgetId: arguments[0].widgetId,
      visible: arguments[0].visible
    };
  }
  items.forEach(function (item) {
    //get hx links
    var htmxElement = jquery__WEBPACK_IMPORTED_MODULE_0___default()(item.content).find("[hx-get]").first();
    item.id = htmxElement.attr("id");
    item.widgetUrl = htmxElement.attr("hx-get");
    item.widgetTrigger = htmxElement.attr("hx-trigger");
    if (item.x == undefined) {
      item.x = 0;
    }
    item.gridX = item.x;
    if (item.y == undefined) {
      item.y = 0;
    }
    item.gridY = item.y;
    if (item.w == undefined) {
      item.w = 1;
    }
    item.gridWidth = item.w;
    if (item.h == undefined) {
      item.h = 1;
    }
    item.gridHeight = item.h;
    item.content = '';
  });
  jquery__WEBPACK_IMPORTED_MODULE_0___default().post(js_app_core_instance_info_module__WEBPACK_IMPORTED_MODULE_2__.appUrl + "/widgets/widgetManager", {
    action: "saveGrid",
    data: items,
    visibilityData: visibilityData
  }, function (data, status) {});
};
var removeWidget = function removeWidget(el) {
  el.remove();
  grid.removeWidget(el, true);
  saveGrid();
};
var resizeWidget = function resizeWidget(el) {
  var grid = document.querySelector('.grid-stack').gridstack;
  grid.resizeToContent(el, false);
  saveGrid();
};
var toggleWidgetVisibility = function toggleWidgetVisibility(id, element, widget) {
  var grid = document.querySelector('.grid-stack').gridstack;
  var visible = jquery__WEBPACK_IMPORTED_MODULE_0___default()(element).is(":checked");

  // Find the next available position
  var position = findAvailablePosition(widget, grid);
  if (!visible) {
    removeWidget(jquery__WEBPACK_IMPORTED_MODULE_0___default()("#" + id).closest(".grid-stack-item")[0]);
  } else {
    // Create the widget structure using DOM methods
    var widgetNode = document.createElement('div');
    widgetNode.className = 'grid-stack-item';

    // Create the content container
    var contentDiv = document.createElement('div');
    contentDiv.className = "grid-stack-item-content tw-p-none ".concat(widget.widgetBackground == "default" ? "maincontentinner" : widget.background);

    // Set the inner structure
    contentDiv.innerHTML = buildWidget(widget);
    widgetNode.appendChild(contentDiv);

    // Add to grid and make it a widget
    grid.el.appendChild(widgetNode);
    grid.makeWidget(widgetNode, {
      x: widget.gridX || 0,
      y: widget.gridY || 50,
      w: widget.gridWidth || 2,
      h: widget.gridHeight || 2
    });

    // Initialize HTMX
    htmx.process(widgetNode);
    saveGrid({
      action: "toggleWidget",
      widgetId: id,
      visible: visible
    });
  }
};
var buildWidget = function buildWidget(widget) {
  return '<div class="widgetInner">' + '        <div class="' + (widget.widgetBackground == "default" ? "tw-pb-l" : "") + '">\n' + '            <div class="stickyHeader" style="padding:15px; height:50px;  width:100%;">\n' + '               <div class="grid-handler-top tw-h-[40px] tw-cursor-grab tw-float-left tw-mr-sm">\n' + '                    <i class="fa-solid fa-grip-vertical"></i>\n' + '                </div>\n' + '           ' + (widget.name != '' ? '<h5 class="subtitle tw-pb-m tw-float-left tw-mr-sm">' + widget.name + '</h5>' : '') + '\n' + '            <div class="inlineDropDownContainer tw-float-right">\n' + '                <a href="javascript:void(0);" class="dropdown-toggle ticketDropDown editHeadline" data-toggle="dropdown">\n' + '                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>\n' + '                </a>\n' + '                <ul class="dropdown-menu">\n' + '                    <li><a href="javascript:void(0)" class="fitContent"><i class="fa-solid fa-up-right-and-down-left-from-center"></i> Resize to fit content</a></li>\n' + '                        <li><a href="javascript:void(0)" class="removeWidget"><i class="fa fa-eye-slash"></i> Hide</a></li>\n' + '                </ul>\n' + '            </div>\n' + '\n' + '        </div>\n' + ' <div class="widgetContent tw-px-l">\n' + '             <div hx-get="' + widget.widgetUrl + '" hx-trigger="' + widget.widgetTrigger + '" id="' + widget.id + '"></div>\n' + '        </div>\n' + '       </div>\n' + '        <div class="clear"></div>\n' + '    </div>\n';
};

// Make public what you want to have public, everything else is private
var widgetController = {
  resizeWidget: resizeWidget,
  removeWidget: removeWidget,
  saveGrid: saveGrid,
  initGrid: initGrid,
  toggleWidgetVisibility: toggleWidgetVisibility
};

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-base-impl.js":
/*!*****************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-base-impl.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDBaseImplement: () => (/* binding */ DDBaseImplement)
/* harmony export */ });
/**
 * dd-base-impl.ts 11.1.1
 * Copyright (c) 2021-2024  Alain Dumesny - see GridStack root license
 */
class DDBaseImplement {
    constructor() {
        /** @internal */
        this._eventRegister = {};
    }
    /** returns the enable state, but you have to call enable()/disable() to change (as other things need to happen) */
    get disabled() { return this._disabled; }
    on(event, callback) {
        this._eventRegister[event] = callback;
    }
    off(event) {
        delete this._eventRegister[event];
    }
    enable() {
        this._disabled = false;
    }
    disable() {
        this._disabled = true;
    }
    destroy() {
        delete this._eventRegister;
    }
    triggerEvent(eventName, event) {
        if (!this.disabled && this._eventRegister && this._eventRegister[eventName])
            return this._eventRegister[eventName](event);
    }
}
//# sourceMappingURL=dd-base-impl.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-draggable.js":
/*!*****************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-draggable.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDDraggable: () => (/* binding */ DDDraggable)
/* harmony export */ });
/* harmony import */ var _dd_manager__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dd-manager */ "./node_modules/gridstack/dist/dd-manager.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils */ "./node_modules/gridstack/dist/utils.js");
/* harmony import */ var _dd_base_impl__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./dd-base-impl */ "./node_modules/gridstack/dist/dd-base-impl.js");
/* harmony import */ var _dd_touch__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./dd-touch */ "./node_modules/gridstack/dist/dd-touch.js");
/**
 * dd-draggable.ts 11.1.1
 * Copyright (c) 2021-2024  Alain Dumesny - see GridStack root license
 */




// make sure we are not clicking on known object that handles mouseDown
const skipMouseDown = 'input,textarea,button,select,option,[contenteditable="true"],.ui-resizable-handle';
// let count = 0; // TEST
class DDDraggable extends _dd_base_impl__WEBPACK_IMPORTED_MODULE_2__.DDBaseImplement {
    constructor(el, option = {}) {
        super();
        this.el = el;
        this.option = option;
        /** @internal */
        this.dragTransform = {
            xScale: 1,
            yScale: 1,
            xOffset: 0,
            yOffset: 0
        };
        // get the element that is actually supposed to be dragged by
        const handleName = option?.handle?.substring(1);
        const n = el.gridstackNode;
        this.dragEls = !handleName || el.classList.contains(handleName) ? [el] : (n?.subGrid ? [el.querySelector(option.handle) || el] : Array.from(el.querySelectorAll(option.handle)));
        if (this.dragEls.length === 0) {
            this.dragEls = [el];
        }
        // create var event binding so we can easily remove and still look like TS methods (unlike anonymous functions)
        this._mouseDown = this._mouseDown.bind(this);
        this._mouseMove = this._mouseMove.bind(this);
        this._mouseUp = this._mouseUp.bind(this);
        this._keyEvent = this._keyEvent.bind(this);
        this.enable();
    }
    on(event, callback) {
        super.on(event, callback);
    }
    off(event) {
        super.off(event);
    }
    enable() {
        if (this.disabled === false)
            return;
        super.enable();
        this.dragEls.forEach(dragEl => {
            dragEl.addEventListener('mousedown', this._mouseDown);
            if (_dd_touch__WEBPACK_IMPORTED_MODULE_3__.isTouch) {
                dragEl.addEventListener('touchstart', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.touchstart);
                dragEl.addEventListener('pointerdown', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.pointerdown);
                // dragEl.style.touchAction = 'none'; // not needed unlike pointerdown doc comment
            }
        });
        this.el.classList.remove('ui-draggable-disabled');
    }
    disable(forDestroy = false) {
        if (this.disabled === true)
            return;
        super.disable();
        this.dragEls.forEach(dragEl => {
            dragEl.removeEventListener('mousedown', this._mouseDown);
            if (_dd_touch__WEBPACK_IMPORTED_MODULE_3__.isTouch) {
                dragEl.removeEventListener('touchstart', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.touchstart);
                dragEl.removeEventListener('pointerdown', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.pointerdown);
            }
        });
        if (!forDestroy)
            this.el.classList.add('ui-draggable-disabled');
    }
    destroy() {
        if (this.dragTimeout)
            window.clearTimeout(this.dragTimeout);
        delete this.dragTimeout;
        if (this.mouseDownEvent)
            this._mouseUp(this.mouseDownEvent);
        this.disable(true);
        delete this.el;
        delete this.helper;
        delete this.option;
        super.destroy();
    }
    updateOption(opts) {
        Object.keys(opts).forEach(key => this.option[key] = opts[key]);
        return this;
    }
    /** @internal call when mouse goes down before a dragstart happens */
    _mouseDown(e) {
        // don't let more than one widget handle mouseStart
        if (_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.mouseHandled)
            return;
        if (e.button !== 0)
            return true; // only left click
        // make sure we are not clicking on known object that handles mouseDown, or ones supplied by the user
        if (!this.dragEls.find(el => el === e.target) && e.target.closest(skipMouseDown))
            return true;
        if (this.option.cancel) {
            if (e.target.closest(this.option.cancel))
                return true;
        }
        this.mouseDownEvent = e;
        delete this.dragging;
        delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement;
        delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement;
        // document handler so we can continue receiving moves as the item is 'fixed' position, and capture=true so WE get a first crack
        document.addEventListener('mousemove', this._mouseMove, { capture: true, passive: true }); // true=capture, not bubble
        document.addEventListener('mouseup', this._mouseUp, true);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_3__.isTouch) {
            e.currentTarget.addEventListener('touchmove', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.touchmove);
            e.currentTarget.addEventListener('touchend', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.touchend);
        }
        e.preventDefault();
        // preventDefault() prevents blur event which occurs just after mousedown event.
        // if an editable content has focus, then blur must be call
        if (document.activeElement)
            document.activeElement.blur();
        _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.mouseHandled = true;
        return true;
    }
    /** @internal method to call actual drag event */
    _callDrag(e) {
        if (!this.dragging)
            return;
        const ev = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.initEvent(e, { target: this.el, type: 'drag' });
        if (this.option.drag) {
            this.option.drag(ev, this.ui());
        }
        this.triggerEvent('drag', ev);
    }
    /** @internal called when the main page (after successful mousedown) receives a move event to drag the item around the screen */
    _mouseMove(e) {
        // console.log(`${count++} move ${e.x},${e.y}`)
        const s = this.mouseDownEvent;
        this.lastDrag = e;
        if (this.dragging) {
            this._dragFollow(e);
            // delay actual grid handling drag until we pause for a while if set
            if (_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.pauseDrag) {
                const pause = Number.isInteger(_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.pauseDrag) ? _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.pauseDrag : 100;
                if (this.dragTimeout)
                    window.clearTimeout(this.dragTimeout);
                this.dragTimeout = window.setTimeout(() => this._callDrag(e), pause);
            }
            else {
                this._callDrag(e);
            }
        }
        else if (Math.abs(e.x - s.x) + Math.abs(e.y - s.y) > 3) {
            /**
             * don't start unless we've moved at least 3 pixels
             */
            this.dragging = true;
            _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement = this;
            // if we're dragging an actual grid item, set the current drop as the grid (to detect enter/leave)
            const grid = this.el.gridstackNode?.grid;
            if (grid) {
                _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement = grid.el.ddElement.ddDroppable;
            }
            else {
                delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement;
            }
            this.helper = this._createHelper();
            this._setupHelperContainmentStyle();
            this.dragTransform = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.getValuesFromTransformedElement(this.helperContainment);
            this.dragOffset = this._getDragOffset(e, this.el, this.helperContainment);
            this._setupHelperStyle(e);
            const ev = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.initEvent(e, { target: this.el, type: 'dragstart' });
            if (this.option.start) {
                this.option.start(ev, this.ui());
            }
            this.triggerEvent('dragstart', ev);
            // now track keyboard events to cancel or rotate
            document.addEventListener('keydown', this._keyEvent);
        }
        // e.preventDefault(); // passive = true. OLD: was needed otherwise we get text sweep text selection as we drag around
        return true;
    }
    /** @internal call when the mouse gets released to drop the item at current location */
    _mouseUp(e) {
        document.removeEventListener('mousemove', this._mouseMove, true);
        document.removeEventListener('mouseup', this._mouseUp, true);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_3__.isTouch) {
            e.currentTarget.removeEventListener('touchmove', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.touchmove, true);
            e.currentTarget.removeEventListener('touchend', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.touchend, true);
        }
        if (this.dragging) {
            delete this.dragging;
            delete this.el.gridstackNode?._origRotate;
            document.removeEventListener('keydown', this._keyEvent);
            // reset the drop target if dragging over ourself (already parented, just moving during stop callback below)
            if (_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement?.el === this.el.parentElement) {
                delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement;
            }
            this.helperContainment.style.position = this.parentOriginStylePosition || null;
            if (this.helper !== this.el)
                this.helper.remove(); // hide now
            this._removeHelperStyle();
            const ev = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.initEvent(e, { target: this.el, type: 'dragstop' });
            if (this.option.stop) {
                this.option.stop(ev); // NOTE: destroy() will be called when removing item, so expect NULL ptr after!
            }
            this.triggerEvent('dragstop', ev);
            // call the droppable method to receive the item
            if (_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement) {
                _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement.drop(e);
            }
        }
        delete this.helper;
        delete this.mouseDownEvent;
        delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement;
        delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement;
        delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.mouseHandled;
        e.preventDefault();
    }
    /** @internal call when keys are being pressed - use Esc to cancel, R to rotate */
    _keyEvent(e) {
        const n = this.el.gridstackNode;
        if (!n?.grid)
            return;
        const grid = n.grid;
        if (e.key === 'Escape') {
            if (n._origRotate) {
                n._orig = n._origRotate;
                delete n._origRotate;
            }
            grid.engine.restoreInitial();
            this._mouseUp(this.mouseDownEvent);
        }
        else if (e.key === 'r' || e.key === 'R') {
            if (!_utils__WEBPACK_IMPORTED_MODULE_1__.Utils.canBeRotated(n))
                return;
            n._origRotate = n._origRotate || { ...n._orig }; // store the real orig size in case we Esc after doing rotation
            delete n._moving; // force rotate to happen (move waits for >50% coverage otherwise)
            grid.setAnimation(false) // immediate rotate so _getDragOffset() gets the right dom size below
                .rotate(n.el, { top: -this.dragOffset.offsetTop, left: -this.dragOffset.offsetLeft })
                .setAnimation();
            n._moving = true;
            this.dragOffset = this._getDragOffset(this.lastDrag, n.el, this.helperContainment);
            this.helper.style.width = this.dragOffset.width + 'px';
            this.helper.style.height = this.dragOffset.height + 'px';
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.swap(n._orig, 'w', 'h');
            delete n._rect;
            this._mouseMove(this.lastDrag);
        }
    }
    /** @internal create a clone copy (or user defined method) of the original drag item if set */
    _createHelper() {
        let helper = this.el;
        if (typeof this.option.helper === 'function') {
            helper = this.option.helper(this.el);
        }
        else if (this.option.helper === 'clone') {
            helper = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneNode(this.el);
        }
        if (!document.body.contains(helper)) {
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.appendTo(helper, this.option.appendTo === 'parent' ? this.el.parentElement : this.option.appendTo);
        }
        this.dragElementOriginStyle = DDDraggable.originStyleProp.map(prop => this.el.style[prop]);
        return helper;
    }
    /** @internal set the fix position of the dragged item */
    _setupHelperStyle(e) {
        this.helper.classList.add('ui-draggable-dragging');
        // TODO: set all at once with style.cssText += ... ? https://stackoverflow.com/questions/3968593
        const style = this.helper.style;
        style.pointerEvents = 'none'; // needed for over items to get enter/leave
        // style.cursor = 'move'; //  TODO: can't set with pointerEvents=none ! (done in CSS as well)
        style.width = this.dragOffset.width + 'px';
        style.height = this.dragOffset.height + 'px';
        style.willChange = 'left, top';
        style.position = 'fixed'; // let us drag between grids by not clipping as parent .grid-stack is position: 'relative'
        this._dragFollow(e); // now position it
        style.transition = 'none'; // show up instantly
        setTimeout(() => {
            if (this.helper) {
                style.transition = null; // recover animation
            }
        }, 0);
        return this;
    }
    /** @internal restore back the original style before dragging */
    _removeHelperStyle() {
        this.helper.classList.remove('ui-draggable-dragging');
        const node = this.helper?.gridstackNode;
        // don't bother restoring styles if we're gonna remove anyway...
        if (!node?._isAboutToRemove && this.dragElementOriginStyle) {
            const helper = this.helper;
            // don't animate, otherwise we animate offseted when switching back to 'absolute' from 'fixed'.
            // TODO: this also removes resizing animation which doesn't have this issue, but others.
            // Ideally both would animate ('move' would immediately restore 'absolute' and adjust coordinate to match,
            // then trigger a delay (repaint) to restore to final dest with animate) but then we need to make sure 'resizestop'
            // is called AFTER 'transitionend' event is received (see https://github.com/gridstack/gridstack.js/issues/2033)
            const transition = this.dragElementOriginStyle['transition'] || null;
            helper.style.transition = this.dragElementOriginStyle['transition'] = 'none'; // can't be NULL #1973
            DDDraggable.originStyleProp.forEach(prop => helper.style[prop] = this.dragElementOriginStyle[prop] || null);
            setTimeout(() => helper.style.transition = transition, 50); // recover animation from saved vars after a pause (0 isn't enough #1973)
        }
        delete this.dragElementOriginStyle;
        return this;
    }
    /** @internal updates the top/left position to follow the mouse */
    _dragFollow(e) {
        const containmentRect = { left: 0, top: 0 };
        // if (this.helper.style.position === 'absolute') { // we use 'fixed'
        //   const { left, top } = this.helperContainment.getBoundingClientRect();
        //   containmentRect = { left, top };
        // }
        const style = this.helper.style;
        const offset = this.dragOffset;
        style.left = (e.clientX + offset.offsetLeft - containmentRect.left) * this.dragTransform.xScale + 'px';
        style.top = (e.clientY + offset.offsetTop - containmentRect.top) * this.dragTransform.yScale + 'px';
    }
    /** @internal */
    _setupHelperContainmentStyle() {
        this.helperContainment = this.helper.parentElement;
        if (this.helper.style.position !== 'fixed') {
            this.parentOriginStylePosition = this.helperContainment.style.position;
            if (getComputedStyle(this.helperContainment).position.match(/static/)) {
                this.helperContainment.style.position = 'relative';
            }
        }
        return this;
    }
    /** @internal */
    _getDragOffset(event, el, parent) {
        // in case ancestor has transform/perspective css properties that change the viewpoint
        let xformOffsetX = 0;
        let xformOffsetY = 0;
        if (parent) {
            xformOffsetX = this.dragTransform.xOffset;
            xformOffsetY = this.dragTransform.yOffset;
        }
        const targetOffset = el.getBoundingClientRect();
        return {
            left: targetOffset.left,
            top: targetOffset.top,
            offsetLeft: -event.clientX + targetOffset.left - xformOffsetX,
            offsetTop: -event.clientY + targetOffset.top - xformOffsetY,
            width: targetOffset.width * this.dragTransform.xScale,
            height: targetOffset.height * this.dragTransform.yScale
        };
    }
    /** @internal TODO: set to public as called by DDDroppable! */
    ui() {
        const containmentEl = this.el.parentElement;
        const containmentRect = containmentEl.getBoundingClientRect();
        const offset = this.helper.getBoundingClientRect();
        return {
            position: {
                top: (offset.top - containmentRect.top) * this.dragTransform.yScale,
                left: (offset.left - containmentRect.left) * this.dragTransform.xScale
            }
            /* not used by GridStack for now...
            helper: [this.helper], //The object arr representing the helper that's being dragged.
            offset: { top: offset.top, left: offset.left } // Current offset position of the helper as { top, left } object.
            */
        };
    }
}
/** @internal properties we change during dragging, and restore back */
DDDraggable.originStyleProp = ['width', 'height', 'transform', 'transform-origin', 'transition', 'pointerEvents', 'position', 'left', 'top', 'minWidth', 'willChange'];

//# sourceMappingURL=dd-draggable.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-droppable.js":
/*!*****************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-droppable.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDDroppable: () => (/* binding */ DDDroppable)
/* harmony export */ });
/* harmony import */ var _dd_manager__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dd-manager */ "./node_modules/gridstack/dist/dd-manager.js");
/* harmony import */ var _dd_base_impl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dd-base-impl */ "./node_modules/gridstack/dist/dd-base-impl.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils */ "./node_modules/gridstack/dist/utils.js");
/* harmony import */ var _dd_touch__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./dd-touch */ "./node_modules/gridstack/dist/dd-touch.js");
/**
 * dd-droppable.ts 11.1.1
 * Copyright (c) 2021-2024  Alain Dumesny - see GridStack root license
 */




// let count = 0; // TEST
class DDDroppable extends _dd_base_impl__WEBPACK_IMPORTED_MODULE_1__.DDBaseImplement {
    constructor(el, option = {}) {
        super();
        this.el = el;
        this.option = option;
        // create var event binding so we can easily remove and still look like TS methods (unlike anonymous functions)
        this._mouseEnter = this._mouseEnter.bind(this);
        this._mouseLeave = this._mouseLeave.bind(this);
        this.enable();
        this._setupAccept();
    }
    on(event, callback) {
        super.on(event, callback);
    }
    off(event) {
        super.off(event);
    }
    enable() {
        if (this.disabled === false)
            return;
        super.enable();
        this.el.classList.add('ui-droppable');
        this.el.classList.remove('ui-droppable-disabled');
        this.el.addEventListener('mouseenter', this._mouseEnter);
        this.el.addEventListener('mouseleave', this._mouseLeave);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_3__.isTouch) {
            this.el.addEventListener('pointerenter', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.pointerenter);
            this.el.addEventListener('pointerleave', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.pointerleave);
        }
    }
    disable(forDestroy = false) {
        if (this.disabled === true)
            return;
        super.disable();
        this.el.classList.remove('ui-droppable');
        if (!forDestroy)
            this.el.classList.add('ui-droppable-disabled');
        this.el.removeEventListener('mouseenter', this._mouseEnter);
        this.el.removeEventListener('mouseleave', this._mouseLeave);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_3__.isTouch) {
            this.el.removeEventListener('pointerenter', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.pointerenter);
            this.el.removeEventListener('pointerleave', _dd_touch__WEBPACK_IMPORTED_MODULE_3__.pointerleave);
        }
    }
    destroy() {
        this.disable(true);
        this.el.classList.remove('ui-droppable');
        this.el.classList.remove('ui-droppable-disabled');
        super.destroy();
    }
    updateOption(opts) {
        Object.keys(opts).forEach(key => this.option[key] = opts[key]);
        this._setupAccept();
        return this;
    }
    /** @internal called when the cursor enters our area - prepare for a possible drop and track leaving */
    _mouseEnter(e) {
        // console.log(`${count++} Enter ${this.el.id || (this.el as GridHTMLElement).gridstack.opts.id}`); // TEST
        if (!_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement)
            return;
        if (!this._canDrop(_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement.el))
            return;
        e.preventDefault();
        e.stopPropagation();
        // make sure when we enter this, that the last one gets a leave FIRST to correctly cleanup as we don't always do
        if (_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement && _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement !== this) {
            _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement._mouseLeave(e, true); // calledByEnter = true
        }
        _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement = this;
        const ev = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.initEvent(e, { target: this.el, type: 'dropover' });
        if (this.option.over) {
            this.option.over(ev, this._ui(_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement));
        }
        this.triggerEvent('dropover', ev);
        this.el.classList.add('ui-droppable-over');
        // console.log('tracking'); // TEST
    }
    /** @internal called when the item is leaving our area, stop tracking if we had moving item */
    _mouseLeave(e, calledByEnter = false) {
        // console.log(`${count++} Leave ${this.el.id || (this.el as GridHTMLElement).gridstack.opts.id}`); // TEST
        if (!_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement || _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement !== this)
            return;
        e.preventDefault();
        e.stopPropagation();
        const ev = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.initEvent(e, { target: this.el, type: 'dropout' });
        if (this.option.out) {
            this.option.out(ev, this._ui(_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement));
        }
        this.triggerEvent('dropout', ev);
        if (_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement === this) {
            delete _dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dropElement;
            // console.log('not tracking'); // TEST
            // if we're still over a parent droppable, send it an enter as we don't get one from leaving nested children
            if (!calledByEnter) {
                let parentDrop;
                let parent = this.el.parentElement;
                while (!parentDrop && parent) {
                    parentDrop = parent.ddElement?.ddDroppable;
                    parent = parent.parentElement;
                }
                if (parentDrop) {
                    parentDrop._mouseEnter(e);
                }
            }
        }
    }
    /** item is being dropped on us - called by the drag mouseup handler - this calls the client drop event */
    drop(e) {
        e.preventDefault();
        const ev = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.initEvent(e, { target: this.el, type: 'drop' });
        if (this.option.drop) {
            this.option.drop(ev, this._ui(_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement));
        }
        this.triggerEvent('drop', ev);
    }
    /** @internal true if element matches the string/method accept option */
    _canDrop(el) {
        return el && (!this.accept || this.accept(el));
    }
    /** @internal */
    _setupAccept() {
        if (!this.option.accept)
            return this;
        if (typeof this.option.accept === 'string') {
            this.accept = (el) => el.classList.contains(this.option.accept) || el.matches(this.option.accept);
        }
        else {
            this.accept = this.option.accept;
        }
        return this;
    }
    /** @internal */
    _ui(drag) {
        return {
            draggable: drag.el,
            ...drag.ui()
        };
    }
}
//# sourceMappingURL=dd-droppable.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-element.js":
/*!***************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-element.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDElement: () => (/* binding */ DDElement)
/* harmony export */ });
/* harmony import */ var _dd_resizable__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dd-resizable */ "./node_modules/gridstack/dist/dd-resizable.js");
/* harmony import */ var _dd_draggable__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dd-draggable */ "./node_modules/gridstack/dist/dd-draggable.js");
/* harmony import */ var _dd_droppable__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./dd-droppable */ "./node_modules/gridstack/dist/dd-droppable.js");
/**
 * dd-elements.ts 11.1.1
 * Copyright (c) 2021-2024 Alain Dumesny - see GridStack root license
 */



class DDElement {
    static init(el) {
        if (!el.ddElement) {
            el.ddElement = new DDElement(el);
        }
        return el.ddElement;
    }
    constructor(el) {
        this.el = el;
    }
    on(eventName, callback) {
        if (this.ddDraggable && ['drag', 'dragstart', 'dragstop'].indexOf(eventName) > -1) {
            this.ddDraggable.on(eventName, callback);
        }
        else if (this.ddDroppable && ['drop', 'dropover', 'dropout'].indexOf(eventName) > -1) {
            this.ddDroppable.on(eventName, callback);
        }
        else if (this.ddResizable && ['resizestart', 'resize', 'resizestop'].indexOf(eventName) > -1) {
            this.ddResizable.on(eventName, callback);
        }
        return this;
    }
    off(eventName) {
        if (this.ddDraggable && ['drag', 'dragstart', 'dragstop'].indexOf(eventName) > -1) {
            this.ddDraggable.off(eventName);
        }
        else if (this.ddDroppable && ['drop', 'dropover', 'dropout'].indexOf(eventName) > -1) {
            this.ddDroppable.off(eventName);
        }
        else if (this.ddResizable && ['resizestart', 'resize', 'resizestop'].indexOf(eventName) > -1) {
            this.ddResizable.off(eventName);
        }
        return this;
    }
    setupDraggable(opts) {
        if (!this.ddDraggable) {
            this.ddDraggable = new _dd_draggable__WEBPACK_IMPORTED_MODULE_1__.DDDraggable(this.el, opts);
        }
        else {
            this.ddDraggable.updateOption(opts);
        }
        return this;
    }
    cleanDraggable() {
        if (this.ddDraggable) {
            this.ddDraggable.destroy();
            delete this.ddDraggable;
        }
        return this;
    }
    setupResizable(opts) {
        if (!this.ddResizable) {
            this.ddResizable = new _dd_resizable__WEBPACK_IMPORTED_MODULE_0__.DDResizable(this.el, opts);
        }
        else {
            this.ddResizable.updateOption(opts);
        }
        return this;
    }
    cleanResizable() {
        if (this.ddResizable) {
            this.ddResizable.destroy();
            delete this.ddResizable;
        }
        return this;
    }
    setupDroppable(opts) {
        if (!this.ddDroppable) {
            this.ddDroppable = new _dd_droppable__WEBPACK_IMPORTED_MODULE_2__.DDDroppable(this.el, opts);
        }
        else {
            this.ddDroppable.updateOption(opts);
        }
        return this;
    }
    cleanDroppable() {
        if (this.ddDroppable) {
            this.ddDroppable.destroy();
            delete this.ddDroppable;
        }
        return this;
    }
}
//# sourceMappingURL=dd-element.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-gridstack.js":
/*!*****************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-gridstack.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDGridStack: () => (/* binding */ DDGridStack)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils */ "./node_modules/gridstack/dist/utils.js");
/* harmony import */ var _dd_manager__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dd-manager */ "./node_modules/gridstack/dist/dd-manager.js");
/* harmony import */ var _dd_element__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./dd-element */ "./node_modules/gridstack/dist/dd-element.js");
/**
 * dd-gridstack.ts 11.1.1
 * Copyright (c) 2021-2024 Alain Dumesny - see GridStack root license
 */



// let count = 0; // TEST
/**
 * HTML Native Mouse and Touch Events Drag and Drop functionality.
 */
class DDGridStack {
    resizable(el, opts, key, value) {
        this._getDDElements(el).forEach(dEl => {
            if (opts === 'disable' || opts === 'enable') {
                dEl.ddResizable && dEl.ddResizable[opts](); // can't create DD as it requires options for setupResizable()
            }
            else if (opts === 'destroy') {
                dEl.ddResizable && dEl.cleanResizable();
            }
            else if (opts === 'option') {
                dEl.setupResizable({ [key]: value });
            }
            else {
                const n = dEl.el.gridstackNode;
                const grid = n.grid;
                let handles = dEl.el.getAttribute('gs-resize-handles') || grid.opts.resizable.handles || 'e,s,se';
                if (handles === 'all')
                    handles = 'n,e,s,w,se,sw,ne,nw';
                // NOTE: keep the resize handles as e,w don't have enough space (10px) to show resize corners anyway. limit during drag instead
                // restrict vertical resize if height is done to match content anyway... odd to have it spring back
                // if (Utils.shouldSizeToContent(n, true)) {
                //   const doE = handles.indexOf('e') !== -1;
                //   const doW = handles.indexOf('w') !== -1;
                //   handles = doE ? (doW ? 'e,w' : 'e') : (doW ? 'w' : '');
                // }
                const autoHide = !grid.opts.alwaysShowResizeHandle;
                dEl.setupResizable({
                    ...grid.opts.resizable,
                    ...{ handles, autoHide },
                    ...{
                        start: opts.start,
                        stop: opts.stop,
                        resize: opts.resize
                    }
                });
            }
        });
        return this;
    }
    draggable(el, opts, key, value) {
        this._getDDElements(el).forEach(dEl => {
            if (opts === 'disable' || opts === 'enable') {
                dEl.ddDraggable && dEl.ddDraggable[opts](); // can't create DD as it requires options for setupDraggable()
            }
            else if (opts === 'destroy') {
                dEl.ddDraggable && dEl.cleanDraggable();
            }
            else if (opts === 'option') {
                dEl.setupDraggable({ [key]: value });
            }
            else {
                const grid = dEl.el.gridstackNode.grid;
                dEl.setupDraggable({
                    ...grid.opts.draggable,
                    ...{
                        // containment: (grid.parentGridNode && grid.opts.dragOut === false) ? grid.el.parentElement : (grid.opts.draggable.containment || null),
                        start: opts.start,
                        stop: opts.stop,
                        drag: opts.drag
                    }
                });
            }
        });
        return this;
    }
    dragIn(el, opts) {
        this._getDDElements(el).forEach(dEl => dEl.setupDraggable(opts));
        return this;
    }
    droppable(el, opts, key, value) {
        if (typeof opts.accept === 'function' && !opts._accept) {
            opts._accept = opts.accept;
            opts.accept = (el) => opts._accept(el);
        }
        this._getDDElements(el).forEach(dEl => {
            if (opts === 'disable' || opts === 'enable') {
                dEl.ddDroppable && dEl.ddDroppable[opts]();
            }
            else if (opts === 'destroy') {
                if (dEl.ddDroppable) { // error to call destroy if not there
                    dEl.cleanDroppable();
                }
            }
            else if (opts === 'option') {
                dEl.setupDroppable({ [key]: value });
            }
            else {
                dEl.setupDroppable(opts);
            }
        });
        return this;
    }
    /** true if element is droppable */
    isDroppable(el) {
        return !!(el?.ddElement?.ddDroppable && !el.ddElement.ddDroppable.disabled);
    }
    /** true if element is draggable */
    isDraggable(el) {
        return !!(el?.ddElement?.ddDraggable && !el.ddElement.ddDraggable.disabled);
    }
    /** true if element is draggable */
    isResizable(el) {
        return !!(el?.ddElement?.ddResizable && !el.ddElement.ddResizable.disabled);
    }
    on(el, name, callback) {
        this._getDDElements(el).forEach(dEl => dEl.on(name, (event) => {
            callback(event, _dd_manager__WEBPACK_IMPORTED_MODULE_1__.DDManager.dragElement ? _dd_manager__WEBPACK_IMPORTED_MODULE_1__.DDManager.dragElement.el : event.target, _dd_manager__WEBPACK_IMPORTED_MODULE_1__.DDManager.dragElement ? _dd_manager__WEBPACK_IMPORTED_MODULE_1__.DDManager.dragElement.helper : null);
        }));
        return this;
    }
    off(el, name) {
        this._getDDElements(el).forEach(dEl => dEl.off(name));
        return this;
    }
    /** @internal returns a list of DD elements, creating them on the fly by default */
    _getDDElements(els, create = true) {
        const hosts = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.getElements(els);
        if (!hosts.length)
            return [];
        const list = hosts.map(e => e.ddElement || (create ? _dd_element__WEBPACK_IMPORTED_MODULE_2__.DDElement.init(e) : null));
        if (!create) {
            list.filter(d => d);
        } // remove nulls
        return list;
    }
}
//# sourceMappingURL=dd-gridstack.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-manager.js":
/*!***************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-manager.js ***!
  \***************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDManager: () => (/* binding */ DDManager)
/* harmony export */ });
/**
 * dd-manager.ts 11.1.1
 * Copyright (c) 2021-2024 Alain Dumesny - see GridStack root license
 */
/**
 * globals that are shared across Drag & Drop instances
 */
class DDManager {
}
//# sourceMappingURL=dd-manager.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-resizable-handle.js":
/*!************************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-resizable-handle.js ***!
  \************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDResizableHandle: () => (/* binding */ DDResizableHandle)
/* harmony export */ });
/* harmony import */ var _dd_touch__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dd-touch */ "./node_modules/gridstack/dist/dd-touch.js");
/**
 * dd-resizable-handle.ts 11.1.1
 * Copyright (c) 2021-2024  Alain Dumesny - see GridStack root license
 */

class DDResizableHandle {
    constructor(host, dir, option) {
        this.host = host;
        this.dir = dir;
        this.option = option;
        /** @internal true after we've moved enough pixels to start a resize */
        this.moving = false;
        // create var event binding so we can easily remove and still look like TS methods (unlike anonymous functions)
        this._mouseDown = this._mouseDown.bind(this);
        this._mouseMove = this._mouseMove.bind(this);
        this._mouseUp = this._mouseUp.bind(this);
        this._keyEvent = this._keyEvent.bind(this);
        this._init();
    }
    /** @internal */
    _init() {
        const el = this.el = document.createElement('div');
        el.classList.add('ui-resizable-handle');
        el.classList.add(`${DDResizableHandle.prefix}${this.dir}`);
        el.style.zIndex = '100';
        el.style.userSelect = 'none';
        this.host.appendChild(this.el);
        this.el.addEventListener('mousedown', this._mouseDown);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_0__.isTouch) {
            this.el.addEventListener('touchstart', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.touchstart);
            this.el.addEventListener('pointerdown', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.pointerdown);
            // this.el.style.touchAction = 'none'; // not needed unlike pointerdown doc comment
        }
        return this;
    }
    /** call this when resize handle needs to be removed and cleaned up */
    destroy() {
        if (this.moving)
            this._mouseUp(this.mouseDownEvent);
        this.el.removeEventListener('mousedown', this._mouseDown);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_0__.isTouch) {
            this.el.removeEventListener('touchstart', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.touchstart);
            this.el.removeEventListener('pointerdown', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.pointerdown);
        }
        this.host.removeChild(this.el);
        delete this.el;
        delete this.host;
        return this;
    }
    /** @internal called on mouse down on us: capture move on the entire document (mouse might not stay on us) until we release the mouse */
    _mouseDown(e) {
        this.mouseDownEvent = e;
        document.addEventListener('mousemove', this._mouseMove, { capture: true, passive: true }); // capture, not bubble
        document.addEventListener('mouseup', this._mouseUp, true);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_0__.isTouch) {
            this.el.addEventListener('touchmove', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.touchmove);
            this.el.addEventListener('touchend', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.touchend);
        }
        e.stopPropagation();
        e.preventDefault();
    }
    /** @internal */
    _mouseMove(e) {
        const s = this.mouseDownEvent;
        if (this.moving) {
            this._triggerEvent('move', e);
        }
        else if (Math.abs(e.x - s.x) + Math.abs(e.y - s.y) > 2) {
            // don't start unless we've moved at least 3 pixels
            this.moving = true;
            this._triggerEvent('start', this.mouseDownEvent);
            this._triggerEvent('move', e);
            // now track keyboard events to cancel
            document.addEventListener('keydown', this._keyEvent);
        }
        e.stopPropagation();
        // e.preventDefault(); passive = true
    }
    /** @internal */
    _mouseUp(e) {
        if (this.moving) {
            this._triggerEvent('stop', e);
            document.removeEventListener('keydown', this._keyEvent);
        }
        document.removeEventListener('mousemove', this._mouseMove, true);
        document.removeEventListener('mouseup', this._mouseUp, true);
        if (_dd_touch__WEBPACK_IMPORTED_MODULE_0__.isTouch) {
            this.el.removeEventListener('touchmove', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.touchmove);
            this.el.removeEventListener('touchend', _dd_touch__WEBPACK_IMPORTED_MODULE_0__.touchend);
        }
        delete this.moving;
        delete this.mouseDownEvent;
        e.stopPropagation();
        e.preventDefault();
    }
    /** @internal call when keys are being pressed - use Esc to cancel */
    _keyEvent(e) {
        if (e.key === 'Escape') {
            this.host.gridstackNode?.grid?.engine.restoreInitial();
            this._mouseUp(this.mouseDownEvent);
        }
    }
    /** @internal */
    _triggerEvent(name, event) {
        if (this.option[name])
            this.option[name](event);
        return this;
    }
}
/** @internal */
DDResizableHandle.prefix = 'ui-resizable-';

//# sourceMappingURL=dd-resizable-handle.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-resizable.js":
/*!*****************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-resizable.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDResizable: () => (/* binding */ DDResizable)
/* harmony export */ });
/* harmony import */ var _dd_resizable_handle__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dd-resizable-handle */ "./node_modules/gridstack/dist/dd-resizable-handle.js");
/* harmony import */ var _dd_base_impl__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./dd-base-impl */ "./node_modules/gridstack/dist/dd-base-impl.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./utils */ "./node_modules/gridstack/dist/utils.js");
/* harmony import */ var _dd_manager__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./dd-manager */ "./node_modules/gridstack/dist/dd-manager.js");
/**
 * dd-resizable.ts 11.1.1
 * Copyright (c) 2021-2024  Alain Dumesny - see GridStack root license
 */




class DDResizable extends _dd_base_impl__WEBPACK_IMPORTED_MODULE_1__.DDBaseImplement {
    // have to be public else complains for HTMLElementExtendOpt ?
    constructor(el, option = {}) {
        super();
        this.el = el;
        this.option = option;
        /** @internal */
        this.rectScale = { x: 1, y: 1 };
        /** @internal */
        this._ui = () => {
            const containmentEl = this.el.parentElement;
            const containmentRect = containmentEl.getBoundingClientRect();
            const newRect = {
                width: this.originalRect.width,
                height: this.originalRect.height + this.scrolled,
                left: this.originalRect.left,
                top: this.originalRect.top - this.scrolled
            };
            const rect = this.temporalRect || newRect;
            return {
                position: {
                    left: (rect.left - containmentRect.left) * this.rectScale.x,
                    top: (rect.top - containmentRect.top) * this.rectScale.y
                },
                size: {
                    width: rect.width * this.rectScale.x,
                    height: rect.height * this.rectScale.y
                }
                /* Gridstack ONLY needs position set above... keep around in case.
                element: [this.el], // The object representing the element to be resized
                helper: [], // TODO: not support yet - The object representing the helper that's being resized
                originalElement: [this.el],// we don't wrap here, so simplify as this.el //The object representing the original element before it is wrapped
                originalPosition: { // The position represented as { left, top } before the resizable is resized
                  left: this.originalRect.left - containmentRect.left,
                  top: this.originalRect.top - containmentRect.top
                },
                originalSize: { // The size represented as { width, height } before the resizable is resized
                  width: this.originalRect.width,
                  height: this.originalRect.height
                }
                */
            };
        };
        // create var event binding so we can easily remove and still look like TS methods (unlike anonymous functions)
        this._mouseOver = this._mouseOver.bind(this);
        this._mouseOut = this._mouseOut.bind(this);
        this.enable();
        this._setupAutoHide(this.option.autoHide);
        this._setupHandlers();
    }
    on(event, callback) {
        super.on(event, callback);
    }
    off(event) {
        super.off(event);
    }
    enable() {
        super.enable();
        this.el.classList.remove('ui-resizable-disabled');
        this._setupAutoHide(this.option.autoHide);
    }
    disable() {
        super.disable();
        this.el.classList.add('ui-resizable-disabled');
        this._setupAutoHide(false);
    }
    destroy() {
        this._removeHandlers();
        this._setupAutoHide(false);
        delete this.el;
        super.destroy();
    }
    updateOption(opts) {
        const updateHandles = (opts.handles && opts.handles !== this.option.handles);
        const updateAutoHide = (opts.autoHide && opts.autoHide !== this.option.autoHide);
        Object.keys(opts).forEach(key => this.option[key] = opts[key]);
        if (updateHandles) {
            this._removeHandlers();
            this._setupHandlers();
        }
        if (updateAutoHide) {
            this._setupAutoHide(this.option.autoHide);
        }
        return this;
    }
    /** @internal turns auto hide on/off */
    _setupAutoHide(auto) {
        if (auto) {
            this.el.classList.add('ui-resizable-autohide');
            // use mouseover and not mouseenter to get better performance and track for nested cases
            this.el.addEventListener('mouseover', this._mouseOver);
            this.el.addEventListener('mouseout', this._mouseOut);
        }
        else {
            this.el.classList.remove('ui-resizable-autohide');
            this.el.removeEventListener('mouseover', this._mouseOver);
            this.el.removeEventListener('mouseout', this._mouseOut);
            if (_dd_manager__WEBPACK_IMPORTED_MODULE_3__.DDManager.overResizeElement === this) {
                delete _dd_manager__WEBPACK_IMPORTED_MODULE_3__.DDManager.overResizeElement;
            }
        }
        return this;
    }
    /** @internal */
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    _mouseOver(e) {
        // console.log(`${count++} pre-enter ${(this.el as GridItemHTMLElement).gridstackNode._id}`)
        // already over a child, ignore. Ideally we just call e.stopPropagation() but see https://github.com/gridstack/gridstack.js/issues/2018
        if (_dd_manager__WEBPACK_IMPORTED_MODULE_3__.DDManager.overResizeElement || _dd_manager__WEBPACK_IMPORTED_MODULE_3__.DDManager.dragElement)
            return;
        _dd_manager__WEBPACK_IMPORTED_MODULE_3__.DDManager.overResizeElement = this;
        // console.log(`${count++} enter ${(this.el as GridItemHTMLElement).gridstackNode._id}`)
        this.el.classList.remove('ui-resizable-autohide');
    }
    /** @internal */
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    _mouseOut(e) {
        // console.log(`${count++} pre-leave ${(this.el as GridItemHTMLElement).gridstackNode._id}`)
        if (_dd_manager__WEBPACK_IMPORTED_MODULE_3__.DDManager.overResizeElement !== this)
            return;
        delete _dd_manager__WEBPACK_IMPORTED_MODULE_3__.DDManager.overResizeElement;
        // console.log(`${count++} leave ${(this.el as GridItemHTMLElement).gridstackNode._id}`)
        this.el.classList.add('ui-resizable-autohide');
    }
    /** @internal */
    _setupHandlers() {
        this.handlers = this.option.handles.split(',')
            .map(dir => dir.trim())
            .map(dir => new _dd_resizable_handle__WEBPACK_IMPORTED_MODULE_0__.DDResizableHandle(this.el, dir, {
            start: (event) => {
                this._resizeStart(event);
            },
            stop: (event) => {
                this._resizeStop(event);
            },
            move: (event) => {
                this._resizing(event, dir);
            }
        }));
        return this;
    }
    /** @internal */
    _resizeStart(event) {
        this.sizeToContent = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.shouldSizeToContent(this.el.gridstackNode, true); // strick true only and not number
        this.originalRect = this.el.getBoundingClientRect();
        this.scrollEl = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.getScrollElement(this.el);
        this.scrollY = this.scrollEl.scrollTop;
        this.scrolled = 0;
        this.startEvent = event;
        this._setupHelper();
        this._applyChange();
        const ev = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.initEvent(event, { type: 'resizestart', target: this.el });
        if (this.option.start) {
            this.option.start(ev, this._ui());
        }
        this.el.classList.add('ui-resizable-resizing');
        this.triggerEvent('resizestart', ev);
        return this;
    }
    /** @internal */
    _resizing(event, dir) {
        this.scrolled = this.scrollEl.scrollTop - this.scrollY;
        this.temporalRect = this._getChange(event, dir);
        this._applyChange();
        const ev = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.initEvent(event, { type: 'resize', target: this.el });
        if (this.option.resize) {
            this.option.resize(ev, this._ui());
        }
        this.triggerEvent('resize', ev);
        return this;
    }
    /** @internal */
    _resizeStop(event) {
        const ev = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.initEvent(event, { type: 'resizestop', target: this.el });
        if (this.option.stop) {
            this.option.stop(ev); // Note: ui() not used by gridstack so don't pass
        }
        this.el.classList.remove('ui-resizable-resizing');
        this.triggerEvent('resizestop', ev);
        this._cleanHelper();
        delete this.startEvent;
        delete this.originalRect;
        delete this.temporalRect;
        delete this.scrollY;
        delete this.scrolled;
        return this;
    }
    /** @internal */
    _setupHelper() {
        this.elOriginStyleVal = DDResizable._originStyleProp.map(prop => this.el.style[prop]);
        this.parentOriginStylePosition = this.el.parentElement.style.position;
        const parent = this.el.parentElement;
        const dragTransform = _utils__WEBPACK_IMPORTED_MODULE_2__.Utils.getValuesFromTransformedElement(parent);
        this.rectScale = {
            x: dragTransform.xScale,
            y: dragTransform.yScale
        };
        if (getComputedStyle(this.el.parentElement).position.match(/static/)) {
            this.el.parentElement.style.position = 'relative';
        }
        this.el.style.position = 'absolute';
        this.el.style.opacity = '0.8';
        return this;
    }
    /** @internal */
    _cleanHelper() {
        DDResizable._originStyleProp.forEach((prop, i) => {
            this.el.style[prop] = this.elOriginStyleVal[i] || null;
        });
        this.el.parentElement.style.position = this.parentOriginStylePosition || null;
        return this;
    }
    /** @internal */
    _getChange(event, dir) {
        const oEvent = this.startEvent;
        const newRect = {
            width: this.originalRect.width,
            height: this.originalRect.height + this.scrolled,
            left: this.originalRect.left,
            top: this.originalRect.top - this.scrolled
        };
        const offsetX = event.clientX - oEvent.clientX;
        const offsetY = this.sizeToContent ? 0 : event.clientY - oEvent.clientY; // prevent vert resize
        let moveLeft;
        let moveUp;
        if (dir.indexOf('e') > -1) {
            newRect.width += offsetX;
        }
        else if (dir.indexOf('w') > -1) {
            newRect.width -= offsetX;
            newRect.left += offsetX;
            moveLeft = true;
        }
        if (dir.indexOf('s') > -1) {
            newRect.height += offsetY;
        }
        else if (dir.indexOf('n') > -1) {
            newRect.height -= offsetY;
            newRect.top += offsetY;
            moveUp = true;
        }
        const constrain = this._constrainSize(newRect.width, newRect.height, moveLeft, moveUp);
        if (Math.round(newRect.width) !== Math.round(constrain.width)) { // round to ignore slight round-off errors
            if (dir.indexOf('w') > -1) {
                newRect.left += newRect.width - constrain.width;
            }
            newRect.width = constrain.width;
        }
        if (Math.round(newRect.height) !== Math.round(constrain.height)) {
            if (dir.indexOf('n') > -1) {
                newRect.top += newRect.height - constrain.height;
            }
            newRect.height = constrain.height;
        }
        return newRect;
    }
    /** @internal constrain the size to the set min/max values */
    _constrainSize(oWidth, oHeight, moveLeft, moveUp) {
        const o = this.option;
        const maxWidth = (moveLeft ? o.maxWidthMoveLeft : o.maxWidth) || Number.MAX_SAFE_INTEGER;
        const minWidth = o.minWidth / this.rectScale.x || oWidth;
        const maxHeight = (moveUp ? o.maxHeightMoveUp : o.maxHeight) || Number.MAX_SAFE_INTEGER;
        const minHeight = o.minHeight / this.rectScale.y || oHeight;
        const width = Math.min(maxWidth, Math.max(minWidth, oWidth));
        const height = Math.min(maxHeight, Math.max(minHeight, oHeight));
        return { width, height };
    }
    /** @internal */
    _applyChange() {
        let containmentRect = { left: 0, top: 0, width: 0, height: 0 };
        if (this.el.style.position === 'absolute') {
            const containmentEl = this.el.parentElement;
            const { left, top } = containmentEl.getBoundingClientRect();
            containmentRect = { left, top, width: 0, height: 0 };
        }
        if (!this.temporalRect)
            return this;
        Object.keys(this.temporalRect).forEach(key => {
            const value = this.temporalRect[key];
            const scaleReciprocal = key === 'width' || key === 'left' ? this.rectScale.x : key === 'height' || key === 'top' ? this.rectScale.y : 1;
            this.el.style[key] = (value - containmentRect[key]) * scaleReciprocal + 'px';
        });
        return this;
    }
    /** @internal */
    _removeHandlers() {
        this.handlers.forEach(handle => handle.destroy());
        delete this.handlers;
        return this;
    }
}
/** @internal */
DDResizable._originStyleProp = ['width', 'height', 'position', 'left', 'top', 'opacity', 'zIndex'];

//# sourceMappingURL=dd-resizable.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/dd-touch.js":
/*!*************************************************!*\
  !*** ./node_modules/gridstack/dist/dd-touch.js ***!
  \*************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   isTouch: () => (/* binding */ isTouch),
/* harmony export */   pointerdown: () => (/* binding */ pointerdown),
/* harmony export */   pointerenter: () => (/* binding */ pointerenter),
/* harmony export */   pointerleave: () => (/* binding */ pointerleave),
/* harmony export */   touchend: () => (/* binding */ touchend),
/* harmony export */   touchmove: () => (/* binding */ touchmove),
/* harmony export */   touchstart: () => (/* binding */ touchstart)
/* harmony export */ });
/* harmony import */ var _dd_manager__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./dd-manager */ "./node_modules/gridstack/dist/dd-manager.js");
/**
 * touch.ts 11.1.1
 * Copyright (c) 2021-2024 Alain Dumesny - see GridStack root license
 */

/**
 * Detect touch support - Windows Surface devices and other touch devices
 * should we use this instead ? (what we had for always showing resize handles)
 * /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)
 */
const isTouch = typeof window !== 'undefined' && typeof document !== 'undefined' &&
    ('ontouchstart' in document
        || 'ontouchstart' in window
        // || !!window.TouchEvent // true on Windows 10 Chrome desktop so don't use this
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        || (window.DocumentTouch && document instanceof window.DocumentTouch)
        || navigator.maxTouchPoints > 0
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        || navigator.msMaxTouchPoints > 0);
// interface TouchCoord {x: number, y: number};
class DDTouch {
}
/**
* Get the x,y position of a touch event
*/
// function getTouchCoords(e: TouchEvent): TouchCoord {
//   return {
//     x: e.changedTouches[0].pageX,
//     y: e.changedTouches[0].pageY
//   };
// }
/**
 * Simulate a mouse event based on a corresponding touch event
 * @param {Object} e A touch event
 * @param {String} simulatedType The corresponding mouse event
 */
function simulateMouseEvent(e, simulatedType) {
    // Ignore multi-touch events
    if (e.touches.length > 1)
        return;
    // Prevent "Ignored attempt to cancel a touchmove event with cancelable=false" errors
    if (e.cancelable)
        e.preventDefault();
    const touch = e.changedTouches[0], simulatedEvent = document.createEvent('MouseEvents');
    // Initialize the simulated mouse event using the touch event's coordinates
    simulatedEvent.initMouseEvent(simulatedType, // type
    true, // bubbles
    true, // cancelable
    window, // view
    1, // detail
    touch.screenX, // screenX
    touch.screenY, // screenY
    touch.clientX, // clientX
    touch.clientY, // clientY
    false, // ctrlKey
    false, // altKey
    false, // shiftKey
    false, // metaKey
    0, // button
    null // relatedTarget
    );
    // Dispatch the simulated event to the target element
    e.target.dispatchEvent(simulatedEvent);
}
/**
 * Simulate a mouse event based on a corresponding Pointer event
 * @param {Object} e A pointer event
 * @param {String} simulatedType The corresponding mouse event
 */
function simulatePointerMouseEvent(e, simulatedType) {
    // Prevent "Ignored attempt to cancel a touchmove event with cancelable=false" errors
    if (e.cancelable)
        e.preventDefault();
    const simulatedEvent = document.createEvent('MouseEvents');
    // Initialize the simulated mouse event using the touch event's coordinates
    simulatedEvent.initMouseEvent(simulatedType, // type
    true, // bubbles
    true, // cancelable
    window, // view
    1, // detail
    e.screenX, // screenX
    e.screenY, // screenY
    e.clientX, // clientX
    e.clientY, // clientY
    false, // ctrlKey
    false, // altKey
    false, // shiftKey
    false, // metaKey
    0, // button
    null // relatedTarget
    );
    // Dispatch the simulated event to the target element
    e.target.dispatchEvent(simulatedEvent);
}
/**
 * Handle the touchstart events
 * @param {Object} e The widget element's touchstart event
 */
function touchstart(e) {
    // Ignore the event if another widget is already being handled
    if (DDTouch.touchHandled)
        return;
    DDTouch.touchHandled = true;
    // Simulate the mouse events
    // simulateMouseEvent(e, 'mouseover');
    // simulateMouseEvent(e, 'mousemove');
    simulateMouseEvent(e, 'mousedown');
}
/**
 * Handle the touchmove events
 * @param {Object} e The document's touchmove event
 */
function touchmove(e) {
    // Ignore event if not handled by us
    if (!DDTouch.touchHandled)
        return;
    simulateMouseEvent(e, 'mousemove');
}
/**
 * Handle the touchend events
 * @param {Object} e The document's touchend event
 */
function touchend(e) {
    // Ignore event if not handled
    if (!DDTouch.touchHandled)
        return;
    // cancel delayed leave event when we release on ourself which happens BEFORE we get this!
    if (DDTouch.pointerLeaveTimeout) {
        window.clearTimeout(DDTouch.pointerLeaveTimeout);
        delete DDTouch.pointerLeaveTimeout;
    }
    const wasDragging = !!_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement;
    // Simulate the mouseup event
    simulateMouseEvent(e, 'mouseup');
    // simulateMouseEvent(event, 'mouseout');
    // If the touch interaction did not move, it should trigger a click
    if (!wasDragging) {
        simulateMouseEvent(e, 'click');
    }
    // Unset the flag to allow other widgets to inherit the touch event
    DDTouch.touchHandled = false;
}
/**
 * Note we don't get touchenter/touchleave (which are deprecated)
 * see https://stackoverflow.com/questions/27908339/js-touch-equivalent-for-mouseenter
 * so instead of PointerEvent to still get enter/leave and send the matching mouse event.
 */
function pointerdown(e) {
    // console.log("pointer down")
    if (e.pointerType === 'mouse')
        return;
    e.target.releasePointerCapture(e.pointerId); // <- Important!
}
function pointerenter(e) {
    // ignore the initial one we get on pointerdown on ourself
    if (!_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement) {
        // console.log('pointerenter ignored');
        return;
    }
    // console.log('pointerenter');
    if (e.pointerType === 'mouse')
        return;
    simulatePointerMouseEvent(e, 'mouseenter');
}
function pointerleave(e) {
    // ignore the leave on ourself we get before releasing the mouse over ourself
    // by delaying sending the event and having the up event cancel us
    if (!_dd_manager__WEBPACK_IMPORTED_MODULE_0__.DDManager.dragElement) {
        // console.log('pointerleave ignored');
        return;
    }
    if (e.pointerType === 'mouse')
        return;
    DDTouch.pointerLeaveTimeout = window.setTimeout(() => {
        delete DDTouch.pointerLeaveTimeout;
        // console.log('pointerleave delayed');
        simulatePointerMouseEvent(e, 'mouseleave');
    }, 10);
}
//# sourceMappingURL=dd-touch.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/gridstack-engine.js":
/*!*********************************************************!*\
  !*** ./node_modules/gridstack/dist/gridstack-engine.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   GridStackEngine: () => (/* binding */ GridStackEngine)
/* harmony export */ });
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./utils */ "./node_modules/gridstack/dist/utils.js");
/**
 * gridstack-engine.ts 11.1.1
 * Copyright (c) 2021-2024  Alain Dumesny - see GridStack root license
 */

/**
 * Defines the GridStack engine that does most no DOM grid manipulation.
 * See GridStack methods and vars for descriptions.
 *
 * NOTE: values should not be modified directly - call the main GridStack API instead
 */
class GridStackEngine {
    constructor(opts = {}) {
        this.addedNodes = [];
        this.removedNodes = [];
        this.defaultColumn = 12;
        this.column = opts.column || this.defaultColumn;
        if (this.column > this.defaultColumn)
            this.defaultColumn = this.column;
        this.maxRow = opts.maxRow;
        this._float = opts.float;
        this.nodes = opts.nodes || [];
        this.onChange = opts.onChange;
    }
    batchUpdate(flag = true, doPack = true) {
        if (!!this.batchMode === flag)
            return this;
        this.batchMode = flag;
        if (flag) {
            this._prevFloat = this._float;
            this._float = true; // let things go anywhere for now... will restore and possibly reposition later
            this.cleanNodes();
            this.saveInitial(); // since begin update (which is called multiple times) won't do this
        }
        else {
            this._float = this._prevFloat;
            delete this._prevFloat;
            if (doPack)
                this._packNodes();
            this._notify();
        }
        return this;
    }
    // use entire row for hitting area (will use bottom reverse sorted first) if we not actively moving DOWN and didn't already skip
    _useEntireRowArea(node, nn) {
        return (!this.float || this.batchMode && !this._prevFloat) && !this._hasLocked && (!node._moving || node._skipDown || nn.y <= node.y);
    }
    /** @internal fix collision on given 'node', going to given new location 'nn', with optional 'collide' node already found.
     * return true if we moved. */
    _fixCollisions(node, nn = node, collide, opt = {}) {
        this.sortNodes(-1); // from last to first, so recursive collision move items in the right order
        collide = collide || this.collide(node, nn); // REAL area collide for swap and skip if none...
        if (!collide)
            return false;
        // swap check: if we're actively moving in gravity mode, see if we collide with an object the same size
        if (node._moving && !opt.nested && !this.float) {
            if (this.swap(node, collide))
                return true;
        }
        // during while() collisions MAKE SURE to check entire row so larger items don't leap frog small ones (push them all down starting last in grid)
        let area = nn;
        if (!this._loading && this._useEntireRowArea(node, nn)) {
            area = { x: 0, w: this.column, y: nn.y, h: nn.h };
            collide = this.collide(node, area, opt.skip); // force new hit
        }
        let didMove = false;
        const newOpt = { nested: true, pack: false };
        let counter = 0;
        while (collide = collide || this.collide(node, area, opt.skip)) { // could collide with more than 1 item... so repeat for each
            if (counter++ > this.nodes.length * 2) {
                throw new Error("Infinite collide check");
            }
            let moved;
            // if colliding with a locked item OR loading (move after) OR moving down with top gravity (and collide could move up) -> skip past the collide,
            // but remember that skip down so we only do this once (and push others otherwise).
            if (collide.locked || this._loading || node._moving && !node._skipDown && nn.y > node.y && !this.float &&
                // can take space we had, or before where we're going
                (!this.collide(collide, { ...collide, y: node.y }, node) || !this.collide(collide, { ...collide, y: nn.y - collide.h }, node))) {
                node._skipDown = (node._skipDown || nn.y > node.y);
                const newNN = { ...nn, y: collide.y + collide.h, ...newOpt };
                // pretent we moved to where we are now so we can continue any collision checks #2492
                moved = this._loading && _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.samePos(node, newNN) ? true : this.moveNode(node, newNN);
                if ((collide.locked || this._loading) && moved) {
                    _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos(nn, node); // moving after lock become our new desired location
                }
                else if (!collide.locked && moved && opt.pack) {
                    // we moved after and will pack: do it now and keep the original drop location, but past the old collide to see what else we might push way
                    this._packNodes();
                    nn.y = collide.y + collide.h;
                    _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos(node, nn);
                }
                didMove = didMove || moved;
            }
            else {
                // move collide down *after* where we will be, ignoring where we are now (don't collide with us)
                moved = this.moveNode(collide, { ...collide, y: nn.y + nn.h, skip: node, ...newOpt });
            }
            if (!moved)
                return didMove; // break inf loop if we couldn't move after all (ex: maxRow, fixed)
            collide = undefined;
        }
        return didMove;
    }
    /** return the nodes that intercept the given node. Optionally a different area can be used, as well as a second node to skip */
    collide(skip, area = skip, skip2) {
        const skipId = skip._id;
        const skip2Id = skip2?._id;
        return this.nodes.find(n => n._id !== skipId && n._id !== skip2Id && _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.isIntercepted(n, area));
    }
    collideAll(skip, area = skip, skip2) {
        const skipId = skip._id;
        const skip2Id = skip2?._id;
        return this.nodes.filter(n => n._id !== skipId && n._id !== skip2Id && _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.isIntercepted(n, area));
    }
    /** does a pixel coverage collision based on where we started, returning the node that has the most coverage that is >50% mid line */
    directionCollideCoverage(node, o, collides) {
        if (!o.rect || !node._rect)
            return;
        const r0 = node._rect; // where started
        const r = { ...o.rect }; // where we are
        // update dragged rect to show where it's coming from (above or below, etc...)
        if (r.y > r0.y) {
            r.h += r.y - r0.y;
            r.y = r0.y;
        }
        else {
            r.h += r0.y - r.y;
        }
        if (r.x > r0.x) {
            r.w += r.x - r0.x;
            r.x = r0.x;
        }
        else {
            r.w += r0.x - r.x;
        }
        let collide;
        let overMax = 0.5; // need >50%
        for (let n of collides) {
            if (n.locked || !n._rect) {
                break;
            }
            const r2 = n._rect; // overlapping target
            let yOver = Number.MAX_VALUE, xOver = Number.MAX_VALUE;
            // depending on which side we started from, compute the overlap % of coverage
            // (ex: from above/below we only compute the max horizontal line coverage)
            if (r0.y < r2.y) { // from above
                yOver = ((r.y + r.h) - r2.y) / r2.h;
            }
            else if (r0.y + r0.h > r2.y + r2.h) { // from below
                yOver = ((r2.y + r2.h) - r.y) / r2.h;
            }
            if (r0.x < r2.x) { // from the left
                xOver = ((r.x + r.w) - r2.x) / r2.w;
            }
            else if (r0.x + r0.w > r2.x + r2.w) { // from the right
                xOver = ((r2.x + r2.w) - r.x) / r2.w;
            }
            const over = Math.min(xOver, yOver);
            if (over > overMax) {
                overMax = over;
                collide = n;
            }
        }
        o.collide = collide; // save it so we don't have to find it again
        return collide;
    }
    /** does a pixel coverage returning the node that has the most coverage by area */
    /*
    protected collideCoverage(r: GridStackPosition, collides: GridStackNode[]): {collide: GridStackNode, over: number} {
      const collide: GridStackNode;
      const overMax = 0;
      collides.forEach(n => {
        if (n.locked || !n._rect) return;
        const over = Utils.areaIntercept(r, n._rect);
        if (over > overMax) {
          overMax = over;
          collide = n;
        }
      });
      return {collide, over: overMax};
    }
    */
    /** called to cache the nodes pixel rectangles used for collision detection during drag */
    cacheRects(w, h, top, right, bottom, left) {
        this.nodes.forEach(n => n._rect = {
            y: n.y * h + top,
            x: n.x * w + left,
            w: n.w * w - left - right,
            h: n.h * h - top - bottom
        });
        return this;
    }
    /** called to possibly swap between 2 nodes (same size or column, not locked, touching), returning true if successful */
    swap(a, b) {
        if (!b || b.locked || !a || a.locked)
            return false;
        function _doSwap() {
            const x = b.x, y = b.y;
            b.x = a.x;
            b.y = a.y; // b -> a position
            if (a.h != b.h) {
                a.x = x;
                a.y = b.y + b.h; // a -> goes after b
            }
            else if (a.w != b.w) {
                a.x = b.x + b.w;
                a.y = y; // a -> goes after b
            }
            else {
                a.x = x;
                a.y = y; // a -> old b position
            }
            a._dirty = b._dirty = true;
            return true;
        }
        let touching; // remember if we called it (vs undefined)
        // same size and same row or column, and touching
        if (a.w === b.w && a.h === b.h && (a.x === b.x || a.y === b.y) && (touching = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.isTouching(a, b)))
            return _doSwap();
        if (touching === false)
            return; // IFF ran test and fail, bail out
        // check for taking same columns (but different height) and touching
        if (a.w === b.w && a.x === b.x && (touching || (touching = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.isTouching(a, b)))) {
            if (b.y < a.y) {
                const t = a;
                a = b;
                b = t;
            } // swap a <-> b vars so a is first
            return _doSwap();
        }
        if (touching === false)
            return;
        // check if taking same row (but different width) and touching
        if (a.h === b.h && a.y === b.y && (touching || (touching = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.isTouching(a, b)))) {
            if (b.x < a.x) {
                const t = a;
                a = b;
                b = t;
            } // swap a <-> b vars so a is first
            return _doSwap();
        }
        return false;
    }
    isAreaEmpty(x, y, w, h) {
        const nn = { x: x || 0, y: y || 0, w: w || 1, h: h || 1 };
        return !this.collide(nn);
    }
    /** re-layout grid items to reclaim any empty space - optionally keeping the sort order exactly the same ('list' mode) vs truly finding an empty spaces */
    compact(layout = 'compact', doSort = true) {
        if (this.nodes.length === 0)
            return this;
        if (doSort)
            this.sortNodes();
        const wasBatch = this.batchMode;
        if (!wasBatch)
            this.batchUpdate();
        const wasColumnResize = this._inColumnResize;
        if (!wasColumnResize)
            this._inColumnResize = true; // faster addNode()
        const copyNodes = this.nodes;
        this.nodes = []; // pretend we have no nodes to conflict layout to start with...
        copyNodes.forEach((n, index, list) => {
            let after;
            if (!n.locked) {
                n.autoPosition = true;
                if (layout === 'list' && index)
                    after = list[index - 1];
            }
            this.addNode(n, false, after); // 'false' for add event trigger
        });
        if (!wasColumnResize)
            delete this._inColumnResize;
        if (!wasBatch)
            this.batchUpdate(false);
        return this;
    }
    /** enable/disable floating widgets (default: `false`) See [example](http://gridstackjs.com/demo/float.html) */
    set float(val) {
        if (this._float === val)
            return;
        this._float = val || false;
        if (!val) {
            this._packNodes()._notify();
        }
    }
    /** float getter method */
    get float() { return this._float || false; }
    /** sort the nodes array from first to last, or reverse. Called during collision/placement to force an order */
    sortNodes(dir = 1) {
        this.nodes = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.sort(this.nodes, dir);
        return this;
    }
    /** @internal called to top gravity pack the items back OR revert back to original Y positions when floating */
    _packNodes() {
        if (this.batchMode) {
            return this;
        }
        this.sortNodes(); // first to last
        if (this.float) {
            // restore original Y pos
            this.nodes.forEach(n => {
                if (n._updating || n._orig === undefined || n.y === n._orig.y)
                    return;
                let newY = n.y;
                while (newY > n._orig.y) {
                    --newY;
                    const collide = this.collide(n, { x: n.x, y: newY, w: n.w, h: n.h });
                    if (!collide) {
                        n._dirty = true;
                        n.y = newY;
                    }
                }
            });
        }
        else {
            // top gravity pack
            this.nodes.forEach((n, i) => {
                if (n.locked)
                    return;
                while (n.y > 0) {
                    const newY = i === 0 ? 0 : n.y - 1;
                    const canBeMoved = i === 0 || !this.collide(n, { x: n.x, y: newY, w: n.w, h: n.h });
                    if (!canBeMoved)
                        break;
                    // Note: must be dirty (from last position) for GridStack::OnChange CB to update positions
                    // and move items back. The user 'change' CB should detect changes from the original
                    // starting position instead.
                    n._dirty = (n.y !== newY);
                    n.y = newY;
                }
            });
        }
        return this;
    }
    /**
     * given a random node, makes sure it's coordinates/values are valid in the current grid
     * @param node to adjust
     * @param resizing if out of bound, resize down or move into the grid to fit ?
     */
    prepareNode(node, resizing) {
        node._id = node._id ?? GridStackEngine._idSeq++;
        // make sure USER supplied id are unique in our list, else assign a new one as it will create issues during load/update/etc...
        const id = node.id;
        if (id) {
            let count = 1; // append nice _n rather than some random number
            while (this.nodes.find(n => n.id === node.id && n !== node)) {
                node.id = id + '_' + (count++);
            }
        }
        // if we're missing position, have the grid position us automatically (before we set them to 0,0)
        if (node.x === undefined || node.y === undefined || node.x === null || node.y === null) {
            node.autoPosition = true;
        }
        // assign defaults for missing required fields
        const defaults = { x: 0, y: 0, w: 1, h: 1 };
        _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.defaults(node, defaults);
        if (!node.autoPosition) {
            delete node.autoPosition;
        }
        if (!node.noResize) {
            delete node.noResize;
        }
        if (!node.noMove) {
            delete node.noMove;
        }
        _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.sanitizeMinMax(node);
        // check for NaN (in case messed up strings were passed. can't do parseInt() || defaults.x above as 0 is valid #)
        if (typeof node.x == 'string') {
            node.x = Number(node.x);
        }
        if (typeof node.y == 'string') {
            node.y = Number(node.y);
        }
        if (typeof node.w == 'string') {
            node.w = Number(node.w);
        }
        if (typeof node.h == 'string') {
            node.h = Number(node.h);
        }
        if (isNaN(node.x)) {
            node.x = defaults.x;
            node.autoPosition = true;
        }
        if (isNaN(node.y)) {
            node.y = defaults.y;
            node.autoPosition = true;
        }
        if (isNaN(node.w)) {
            node.w = defaults.w;
        }
        if (isNaN(node.h)) {
            node.h = defaults.h;
        }
        this.nodeBoundFix(node, resizing);
        return node;
    }
    /** part2 of preparing a node to fit inside our grid - checks for x,y,w from grid dimensions */
    nodeBoundFix(node, resizing) {
        const before = node._orig || _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos({}, node);
        if (node.maxW && node.w) {
            node.w = Math.min(node.w, node.maxW);
        }
        if (node.maxH && node.h) {
            node.h = Math.min(node.h, node.maxH);
        }
        if (node.minW && node.w && node.minW <= this.column) {
            node.w = Math.max(node.w, node.minW);
        }
        if (node.minH && node.h) {
            node.h = Math.max(node.h, node.minH);
        }
        // if user loaded a larger than allowed widget for current # of columns,
        // remember it's position & width so we can restore back (1 -> 12 column) #1655 #1985
        // IFF we're not in the middle of column resizing!
        const saveOrig = (node.x || 0) + (node.w || 1) > this.column;
        if (saveOrig && this.column < this.defaultColumn && !this._inColumnResize && node._id && this.findCacheLayout(node, this.defaultColumn) === -1) {
            const copy = { ...node }; // need _id + positions
            if (copy.autoPosition || copy.x === undefined) {
                delete copy.x;
                delete copy.y;
            }
            else
                copy.x = Math.min(this.defaultColumn - 1, copy.x);
            copy.w = Math.min(this.defaultColumn, copy.w || 1);
            this.cacheOneLayout(copy, this.defaultColumn);
        }
        if (node.w > this.column) {
            node.w = this.column;
        }
        else if (node.w < 1) {
            node.w = 1;
        }
        if (this.maxRow && node.h > this.maxRow) {
            node.h = this.maxRow;
        }
        else if (node.h < 1) {
            node.h = 1;
        }
        if (node.x < 0) {
            node.x = 0;
        }
        if (node.y < 0) {
            node.y = 0;
        }
        if (node.x + node.w > this.column) {
            if (resizing) {
                node.w = this.column - node.x;
            }
            else {
                node.x = this.column - node.w;
            }
        }
        if (this.maxRow && node.y + node.h > this.maxRow) {
            if (resizing) {
                node.h = this.maxRow - node.y;
            }
            else {
                node.y = this.maxRow - node.h;
            }
        }
        if (!_utils__WEBPACK_IMPORTED_MODULE_0__.Utils.samePos(node, before)) {
            node._dirty = true;
        }
        return this;
    }
    /** returns a list of modified nodes from their original values */
    getDirtyNodes(verify) {
        // compare original x,y,w,h instead as _dirty can be a temporary state
        if (verify) {
            return this.nodes.filter(n => n._dirty && !_utils__WEBPACK_IMPORTED_MODULE_0__.Utils.samePos(n, n._orig));
        }
        return this.nodes.filter(n => n._dirty);
    }
    /** @internal call this to call onChange callback with dirty nodes so DOM can be updated */
    _notify(removedNodes) {
        if (this.batchMode || !this.onChange)
            return this;
        const dirtyNodes = (removedNodes || []).concat(this.getDirtyNodes());
        this.onChange(dirtyNodes);
        return this;
    }
    /** @internal remove dirty and last tried info */
    cleanNodes() {
        if (this.batchMode)
            return this;
        this.nodes.forEach(n => {
            delete n._dirty;
            delete n._lastTried;
        });
        return this;
    }
    /** @internal called to save initial position/size to track real dirty state.
     * Note: should be called right after we call change event (so next API is can detect changes)
     * as well as right before we start move/resize/enter (so we can restore items to prev values) */
    saveInitial() {
        this.nodes.forEach(n => {
            n._orig = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos({}, n);
            delete n._dirty;
        });
        this._hasLocked = this.nodes.some(n => n.locked);
        return this;
    }
    /** @internal restore all the nodes back to initial values (called when we leave) */
    restoreInitial() {
        this.nodes.forEach(n => {
            if (_utils__WEBPACK_IMPORTED_MODULE_0__.Utils.samePos(n, n._orig))
                return;
            _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos(n, n._orig);
            n._dirty = true;
        });
        this._notify();
        return this;
    }
    /** find the first available empty spot for the given node width/height, updating the x,y attributes. return true if found.
     * optionally you can pass your own existing node list and column count, otherwise defaults to that engine data.
     * Optionally pass a widget to start search AFTER, meaning the order will remain the same but possibly have empty slots we skipped
     */
    findEmptyPosition(node, nodeList = this.nodes, column = this.column, after) {
        const start = after ? after.y * column + (after.x + after.w) : 0;
        let found = false;
        for (let i = start; !found; ++i) {
            const x = i % column;
            const y = Math.floor(i / column);
            if (x + node.w > column) {
                continue;
            }
            const box = { x, y, w: node.w, h: node.h };
            if (!nodeList.find(n => _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.isIntercepted(box, n))) {
                if (node.x !== x || node.y !== y)
                    node._dirty = true;
                node.x = x;
                node.y = y;
                delete node.autoPosition;
                found = true;
            }
        }
        return found;
    }
    /** call to add the given node to our list, fixing collision and re-packing */
    addNode(node, triggerAddEvent = false, after) {
        const dup = this.nodes.find(n => n._id === node._id);
        if (dup)
            return dup; // prevent inserting twice! return it instead.
        // skip prepareNode if we're in middle of column resize (not new) but do check for bounds!
        this._inColumnResize ? this.nodeBoundFix(node) : this.prepareNode(node);
        delete node._temporaryRemoved;
        delete node._removeDOM;
        let skipCollision;
        if (node.autoPosition && this.findEmptyPosition(node, this.nodes, this.column, after)) {
            delete node.autoPosition; // found our slot
            skipCollision = true;
        }
        this.nodes.push(node);
        if (triggerAddEvent) {
            this.addedNodes.push(node);
        }
        if (!skipCollision)
            this._fixCollisions(node);
        if (!this.batchMode) {
            this._packNodes()._notify();
        }
        return node;
    }
    removeNode(node, removeDOM = true, triggerEvent = false) {
        if (!this.nodes.find(n => n._id === node._id)) {
            // TEST console.log(`Error: GridStackEngine.removeNode() node._id=${node._id} not found!`)
            return this;
        }
        if (triggerEvent) { // we wait until final drop to manually track removed items (rather than during drag)
            this.removedNodes.push(node);
        }
        if (removeDOM)
            node._removeDOM = true; // let CB remove actual HTML (used to set _id to null, but then we loose layout info)
        // don't use 'faster' .splice(findIndex(),1) in case node isn't in our list, or in multiple times.
        this.nodes = this.nodes.filter(n => n._id !== node._id);
        if (!node._isAboutToRemove)
            this._packNodes(); // if dragged out, no need to relayout as already done...
        this._notify([node]);
        return this;
    }
    removeAll(removeDOM = true, triggerEvent = true) {
        delete this._layouts;
        if (!this.nodes.length)
            return this;
        removeDOM && this.nodes.forEach(n => n._removeDOM = true); // let CB remove actual HTML (used to set _id to null, but then we loose layout info)
        const removedNodes = this.nodes;
        this.removedNodes = triggerEvent ? removedNodes : [];
        this.nodes = [];
        return this._notify(removedNodes);
    }
    /** checks if item can be moved (layout constrain) vs moveNode(), returning true if was able to move.
     * In more complicated cases (maxRow) it will attempt at moving the item and fixing
     * others in a clone first, then apply those changes if still within specs. */
    moveNodeCheck(node, o) {
        // if (node.locked) return false;
        if (!this.changedPosConstrain(node, o))
            return false;
        o.pack = true;
        // simpler case: move item directly...
        if (!this.maxRow) {
            return this.moveNode(node, o);
        }
        // complex case: create a clone with NO maxRow (will check for out of bounds at the end)
        let clonedNode;
        const clone = new GridStackEngine({
            column: this.column,
            float: this.float,
            nodes: this.nodes.map(n => {
                if (n._id === node._id) {
                    clonedNode = { ...n };
                    return clonedNode;
                }
                return { ...n };
            })
        });
        if (!clonedNode)
            return false;
        // check if we're covering 50% collision and could move, while still being under maxRow or at least not making it worse
        // (case where widget was somehow added past our max #2449)
        const canMove = clone.moveNode(clonedNode, o) && clone.getRow() <= Math.max(this.getRow(), this.maxRow);
        // else check if we can force a swap (float=true, or different shapes) on non-resize
        if (!canMove && !o.resizing && o.collide) {
            const collide = o.collide.el.gridstackNode; // find the source node the clone collided with at 50%
            if (this.swap(node, collide)) { // swaps and mark dirty
                this._notify();
                return true;
            }
        }
        if (!canMove)
            return false;
        // if clone was able to move, copy those mods over to us now instead of caller trying to do this all over!
        // Note: we can't use the list directly as elements and other parts point to actual node, so copy content
        clone.nodes.filter(n => n._dirty).forEach(c => {
            const n = this.nodes.find(a => a._id === c._id);
            if (!n)
                return;
            _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos(n, c);
            n._dirty = true;
        });
        this._notify();
        return true;
    }
    /** return true if can fit in grid height constrain only (always true if no maxRow) */
    willItFit(node) {
        delete node._willFitPos;
        if (!this.maxRow)
            return true;
        // create a clone with NO maxRow and check if still within size
        const clone = new GridStackEngine({
            column: this.column,
            float: this.float,
            nodes: this.nodes.map(n => { return { ...n }; })
        });
        const n = { ...node }; // clone node so we don't mod any settings on it but have full autoPosition and min/max as well! #1687
        this.cleanupNode(n);
        delete n.el;
        delete n._id;
        delete n.content;
        delete n.grid;
        clone.addNode(n);
        if (clone.getRow() <= this.maxRow) {
            node._willFitPos = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos({}, n);
            return true;
        }
        return false;
    }
    /** true if x,y or w,h are different after clamping to min/max */
    changedPosConstrain(node, p) {
        // first make sure w,h are set for caller
        p.w = p.w || node.w;
        p.h = p.h || node.h;
        if (node.x !== p.x || node.y !== p.y)
            return true;
        // check constrained w,h
        if (node.maxW) {
            p.w = Math.min(p.w, node.maxW);
        }
        if (node.maxH) {
            p.h = Math.min(p.h, node.maxH);
        }
        if (node.minW) {
            p.w = Math.max(p.w, node.minW);
        }
        if (node.minH) {
            p.h = Math.max(p.h, node.minH);
        }
        return (node.w !== p.w || node.h !== p.h);
    }
    /** return true if the passed in node was actually moved (checks for no-op and locked) */
    moveNode(node, o) {
        if (!node || /*node.locked ||*/ !o)
            return false;
        let wasUndefinedPack;
        if (o.pack === undefined && !this.batchMode) {
            wasUndefinedPack = o.pack = true;
        }
        // constrain the passed in values and check if we're still changing our node
        if (typeof o.x !== 'number') {
            o.x = node.x;
        }
        if (typeof o.y !== 'number') {
            o.y = node.y;
        }
        if (typeof o.w !== 'number') {
            o.w = node.w;
        }
        if (typeof o.h !== 'number') {
            o.h = node.h;
        }
        const resizing = (node.w !== o.w || node.h !== o.h);
        const nn = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos({}, node, true); // get min/max out first, then opt positions next
        _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos(nn, o);
        this.nodeBoundFix(nn, resizing);
        _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos(o, nn);
        if (!o.forceCollide && _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.samePos(node, o))
            return false;
        const prevPos = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos({}, node);
        // check if we will need to fix collision at our new location
        const collides = this.collideAll(node, nn, o.skip);
        let needToMove = true;
        if (collides.length) {
            const activeDrag = node._moving && !o.nested;
            // check to make sure we actually collided over 50% surface area while dragging
            let collide = activeDrag ? this.directionCollideCoverage(node, o, collides) : collides[0];
            // if we're enabling creation of sub-grids on the fly, see if we're covering 80% of either one, if we didn't already do that
            if (activeDrag && collide && node.grid?.opts?.subGridDynamic && !node.grid._isTemp) {
                const over = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.areaIntercept(o.rect, collide._rect);
                const a1 = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.area(o.rect);
                const a2 = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.area(collide._rect);
                const perc = over / (a1 < a2 ? a1 : a2);
                if (perc > .8) {
                    collide.grid.makeSubGrid(collide.el, undefined, node);
                    collide = undefined;
                }
            }
            if (collide) {
                needToMove = !this._fixCollisions(node, nn, collide, o); // check if already moved...
            }
            else {
                needToMove = false; // we didn't cover >50% for a move, skip...
                if (wasUndefinedPack)
                    delete o.pack;
            }
        }
        // now move (to the original ask vs the collision version which might differ) and repack things
        if (needToMove && !_utils__WEBPACK_IMPORTED_MODULE_0__.Utils.samePos(node, nn)) {
            node._dirty = true;
            _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.copyPos(node, nn);
        }
        if (o.pack) {
            this._packNodes()
                ._notify();
        }
        return !_utils__WEBPACK_IMPORTED_MODULE_0__.Utils.samePos(node, prevPos); // pack might have moved things back
    }
    getRow() {
        return this.nodes.reduce((row, n) => Math.max(row, n.y + n.h), 0);
    }
    beginUpdate(node) {
        if (!node._updating) {
            node._updating = true;
            delete node._skipDown;
            if (!this.batchMode)
                this.saveInitial();
        }
        return this;
    }
    endUpdate() {
        const n = this.nodes.find(n => n._updating);
        if (n) {
            delete n._updating;
            delete n._skipDown;
        }
        return this;
    }
    /** saves a copy of the largest column layout (eg 12 even when rendering oneColumnMode) so we don't loose orig layout,
     * returning a list of widgets for serialization */
    save(saveElement = true, saveCB) {
        // use the highest layout for any saved info so we can have full detail on reload #1849
        const len = this._layouts?.length;
        const layout = len && this.column !== (len - 1) ? this._layouts[len - 1] : null;
        const list = [];
        this.sortNodes();
        this.nodes.forEach(n => {
            const wl = layout?.find(l => l._id === n._id);
            // use layout info fields instead if set
            const w = { ...n, ...(wl || {}) };
            _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.removeInternalForSave(w, !saveElement);
            if (saveCB)
                saveCB(n, w);
            list.push(w);
        });
        return list;
    }
    /** @internal called whenever a node is added or moved - updates the cached layouts */
    layoutsNodesChange(nodes) {
        if (!this._layouts || this._inColumnResize)
            return this;
        // remove smaller layouts - we will re-generate those on the fly... larger ones need to update
        this._layouts.forEach((layout, column) => {
            if (!layout || column === this.column)
                return this;
            if (column < this.column) {
                this._layouts[column] = undefined;
            }
            else {
                // we save the original x,y,w (h isn't cached) to see what actually changed to propagate better.
                // NOTE: we don't need to check against out of bound scaling/moving as that will be done when using those cache values. #1785
                const ratio = column / this.column;
                nodes.forEach(node => {
                    if (!node._orig)
                        return; // didn't change (newly added ?)
                    const n = layout.find(l => l._id === node._id);
                    if (!n)
                        return; // no cache for new nodes. Will use those values.
                    // Y changed, push down same amount
                    // TODO: detect doing item 'swaps' will help instead of move (especially in 1 column mode)
                    if (n.y >= 0 && node.y !== node._orig.y) {
                        n.y += (node.y - node._orig.y);
                    }
                    // X changed, scale from new position
                    if (node.x !== node._orig.x) {
                        n.x = Math.round(node.x * ratio);
                    }
                    // width changed, scale from new width
                    if (node.w !== node._orig.w) {
                        n.w = Math.round(node.w * ratio);
                    }
                    // ...height always carries over from cache
                });
            }
        });
        return this;
    }
    /**
     * @internal Called to scale the widget width & position up/down based on the column change.
     * Note we store previous layouts (especially original ones) to make it possible to go
     * from say 12 -> 1 -> 12 and get back to where we were.
     *
     * @param prevColumn previous number of columns
     * @param column  new column number
     * @param layout specify the type of re-layout that will happen (position, size, etc...).
     * Note: items will never be outside of the current column boundaries. default (moveScale). Ignored for 1 column
     */
    columnChanged(prevColumn, column, layout = 'moveScale') {
        if (!this.nodes.length || !column || prevColumn === column)
            return this;
        // in this mode no layout is done whatsoever, up to the caller to handle it
        if (layout === 'none')
            return this;
        // simpler shortcuts layouts
        const doCompact = layout === 'compact' || layout === 'list';
        if (doCompact) {
            this.sortNodes(1); // sort with original layout once and only once (new column will affect order otherwise)
        }
        // cache the current layout in case they want to go back (like 12 -> 1 -> 12) as it requires original data IFF we're sizing down (see below)
        if (column < prevColumn)
            this.cacheLayout(this.nodes, prevColumn);
        this.batchUpdate(); // do this EARLY as it will call saveInitial() so we can detect where we started for _dirty and collision
        let newNodes = [];
        let nodes = doCompact ? this.nodes : _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.sort(this.nodes, -1); // current column reverse sorting so we can insert last to front (limit collision)
        // see if we have cached previous layout IFF we are going up in size (restore) otherwise always
        // generate next size down from where we are (looks more natural as you gradually size down).
        if (column > prevColumn && this._layouts) {
            const cacheNodes = this._layouts[column] || [];
            // ...if not, start with the largest layout (if not already there) as down-scaling is more accurate
            // by pretending we came from that larger column by assigning those values as starting point
            const lastIndex = this._layouts.length - 1;
            if (!cacheNodes.length && prevColumn !== lastIndex && this._layouts[lastIndex]?.length) {
                prevColumn = lastIndex;
                this._layouts[lastIndex].forEach(cacheNode => {
                    const n = nodes.find(n => n._id === cacheNode._id);
                    if (n) {
                        // still current, use cache info positions
                        if (!doCompact && !cacheNode.autoPosition) {
                            n.x = cacheNode.x ?? n.x;
                            n.y = cacheNode.y ?? n.y;
                        }
                        n.w = cacheNode.w ?? n.w;
                        if (cacheNode.x == undefined || cacheNode.y === undefined)
                            n.autoPosition = true;
                    }
                });
            }
            // if we found cache re-use those nodes that are still current
            cacheNodes.forEach(cacheNode => {
                const j = nodes.findIndex(n => n._id === cacheNode._id);
                if (j !== -1) {
                    const n = nodes[j];
                    // still current, use cache info positions
                    if (doCompact) {
                        n.w = cacheNode.w; // only w is used, and don't trim the list
                        return;
                    }
                    if (cacheNode.autoPosition || isNaN(cacheNode.x) || isNaN(cacheNode.y)) {
                        this.findEmptyPosition(cacheNode, newNodes);
                    }
                    if (!cacheNode.autoPosition) {
                        n.x = cacheNode.x ?? n.x;
                        n.y = cacheNode.y ?? n.y;
                        n.w = cacheNode.w ?? n.w;
                        newNodes.push(n);
                    }
                    nodes.splice(j, 1);
                }
            });
        }
        // much simpler layout that just compacts
        if (doCompact) {
            this.compact(layout, false);
        }
        else {
            // ...and add any extra non-cached ones
            if (nodes.length) {
                if (typeof layout === 'function') {
                    layout(column, prevColumn, newNodes, nodes);
                }
                else {
                    const ratio = doCompact ? 1 : column / prevColumn;
                    const move = (layout === 'move' || layout === 'moveScale');
                    const scale = (layout === 'scale' || layout === 'moveScale');
                    nodes.forEach(node => {
                        // NOTE: x + w could be outside of the grid, but addNode() below will handle that
                        node.x = (column === 1 ? 0 : (move ? Math.round(node.x * ratio) : Math.min(node.x, column - 1)));
                        node.w = ((column === 1 || prevColumn === 1) ? 1 : scale ? (Math.round(node.w * ratio) || 1) : (Math.min(node.w, column)));
                        newNodes.push(node);
                    });
                    nodes = [];
                }
            }
            // finally re-layout them in reverse order (to get correct placement)
            newNodes = _utils__WEBPACK_IMPORTED_MODULE_0__.Utils.sort(newNodes, -1);
            this._inColumnResize = true; // prevent cache update
            this.nodes = []; // pretend we have no nodes to start with (add() will use same structures) to simplify layout
            newNodes.forEach(node => {
                this.addNode(node, false); // 'false' for add event trigger
                delete node._orig; // make sure the commit doesn't try to restore things back to original
            });
        }
        this.nodes.forEach(n => delete n._orig); // clear _orig before batch=false so it doesn't handle float=true restore
        this.batchUpdate(false, !doCompact);
        delete this._inColumnResize;
        return this;
    }
    /**
     * call to cache the given layout internally to the given location so we can restore back when column changes size
     * @param nodes list of nodes
     * @param column corresponding column index to save it under
     * @param clear if true, will force other caches to be removed (default false)
     */
    cacheLayout(nodes, column, clear = false) {
        const copy = [];
        nodes.forEach((n, i) => {
            // make sure we have an id in case this is new layout, else re-use id already set
            if (n._id === undefined) {
                const existing = n.id ? this.nodes.find(n2 => n2.id === n.id) : undefined; // find existing node using users id
                n._id = existing?._id ?? GridStackEngine._idSeq++;
            }
            copy[i] = { x: n.x, y: n.y, w: n.w, _id: n._id }; // only thing we change is x,y,w and id to find it back
        });
        this._layouts = clear ? [] : this._layouts || []; // use array to find larger quick
        this._layouts[column] = copy;
        return this;
    }
    /**
     * call to cache the given node layout internally to the given location so we can restore back when column changes size
     * @param node single node to cache
     * @param column corresponding column index to save it under
     */
    cacheOneLayout(n, column) {
        n._id = n._id ?? GridStackEngine._idSeq++;
        const l = { x: n.x, y: n.y, w: n.w, _id: n._id };
        if (n.autoPosition || n.x === undefined) {
            delete l.x;
            delete l.y;
            if (n.autoPosition)
                l.autoPosition = true;
        }
        this._layouts = this._layouts || [];
        this._layouts[column] = this._layouts[column] || [];
        const index = this.findCacheLayout(n, column);
        if (index === -1)
            this._layouts[column].push(l);
        else
            this._layouts[column][index] = l;
        return this;
    }
    findCacheLayout(n, column) {
        return this._layouts?.[column]?.findIndex(l => l._id === n._id) ?? -1;
    }
    removeNodeFromLayoutCache(n) {
        if (!this._layouts) {
            return;
        }
        for (let i = 0; i < this._layouts.length; i++) {
            const index = this.findCacheLayout(n, i);
            if (index !== -1) {
                this._layouts[i].splice(index, 1);
            }
        }
    }
    /** called to remove all internal values but the _id */
    cleanupNode(node) {
        for (const prop in node) {
            if (prop[0] === '_' && prop !== '_id')
                delete node[prop];
        }
        return this;
    }
}
/** @internal unique global internal _id counter */
GridStackEngine._idSeq = 0;

//# sourceMappingURL=gridstack-engine.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/gridstack.js":
/*!**************************************************!*\
  !*** ./node_modules/gridstack/dist/gridstack.js ***!
  \**************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   DDGridStack: () => (/* reexport safe */ _dd_gridstack__WEBPACK_IMPORTED_MODULE_3__.DDGridStack),
/* harmony export */   GridStack: () => (/* binding */ GridStack),
/* harmony export */   GridStackEngine: () => (/* reexport safe */ _gridstack_engine__WEBPACK_IMPORTED_MODULE_0__.GridStackEngine),
/* harmony export */   Utils: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_1__.Utils),
/* harmony export */   gridDefaults: () => (/* reexport safe */ _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults),
/* harmony export */   obsolete: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_1__.obsolete),
/* harmony export */   obsoleteAttr: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_1__.obsoleteAttr),
/* harmony export */   obsoleteOpts: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_1__.obsoleteOpts),
/* harmony export */   obsoleteOptsDel: () => (/* reexport safe */ _utils__WEBPACK_IMPORTED_MODULE_1__.obsoleteOptsDel)
/* harmony export */ });
/* harmony import */ var _gridstack_engine__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./gridstack-engine */ "./node_modules/gridstack/dist/gridstack-engine.js");
/* harmony import */ var _utils__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./utils */ "./node_modules/gridstack/dist/utils.js");
/* harmony import */ var _types__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./types */ "./node_modules/gridstack/dist/types.js");
/* harmony import */ var _dd_gridstack__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./dd-gridstack */ "./node_modules/gridstack/dist/dd-gridstack.js");
/* harmony import */ var _dd_touch__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./dd-touch */ "./node_modules/gridstack/dist/dd-touch.js");
/* harmony import */ var _dd_manager__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./dd-manager */ "./node_modules/gridstack/dist/dd-manager.js");
/*!
 * GridStack 11.1.1
 * https://gridstackjs.com/
 *
 * Copyright (c) 2021-2024  Alain Dumesny
 * see root license https://github.com/gridstack/gridstack.js/tree/master/LICENSE
 */



/*
 * and include D&D by default
 * TODO: while we could generate a gridstack-static.js at smaller size - saves about 31k (41k -> 72k)
 * I don't know how to generate the DD only code at the remaining 31k to delay load as code depends on Gridstack.ts
 * also it caused loading issues in prod - see https://github.com/gridstack/gridstack.js/issues/2039
 */



const dd = new _dd_gridstack__WEBPACK_IMPORTED_MODULE_3__.DDGridStack;
// export all dependent file as well to make it easier for users to just import the main file




/**
 * Main gridstack class - you will need to call `GridStack.init()` first to initialize your grid.
 * Note: your grid elements MUST have the following classes for the CSS layout to work:
 * @example
 * <div class="grid-stack">
 *   <div class="grid-stack-item">
 *     <div class="grid-stack-item-content">Item 1</div>
 *   </div>
 * </div>
 */
class GridStack {
    /**
     * initializing the HTML element, or selector string, into a grid will return the grid. Calling it again will
     * simply return the existing instance (ignore any passed options). There is also an initAll() version that support
     * multiple grids initialization at once. Or you can use addGrid() to create the entire grid from JSON.
     * @param options grid options (optional)
     * @param elOrString element or CSS selector (first one used) to convert to a grid (default to '.grid-stack' class selector)
     *
     * @example
     * const grid = GridStack.init();
     *
     * Note: the HTMLElement (of type GridHTMLElement) will store a `gridstack: GridStack` value that can be retrieve later
     * const grid = document.querySelector('.grid-stack').gridstack;
     */
    static init(options = {}, elOrString = '.grid-stack') {
        if (typeof document === 'undefined')
            return null; // temp workaround SSR
        const el = GridStack.getGridElement(elOrString);
        if (!el) {
            if (typeof elOrString === 'string') {
                console.error('GridStack.initAll() no grid was found with selector "' + elOrString + '" - element missing or wrong selector ?' +
                    '\nNote: ".grid-stack" is required for proper CSS styling and drag/drop, and is the default selector.');
            }
            else {
                console.error('GridStack.init() no grid element was passed.');
            }
            return null;
        }
        if (!el.gridstack) {
            el.gridstack = new GridStack(el, _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneDeep(options));
        }
        return el.gridstack;
    }
    /**
     * Will initialize a list of elements (given a selector) and return an array of grids.
     * @param options grid options (optional)
     * @param selector elements selector to convert to grids (default to '.grid-stack' class selector)
     *
     * @example
     * const grids = GridStack.initAll();
     * grids.forEach(...)
     */
    static initAll(options = {}, selector = '.grid-stack') {
        const grids = [];
        if (typeof document === 'undefined')
            return grids; // temp workaround SSR
        GridStack.getGridElements(selector).forEach(el => {
            if (!el.gridstack) {
                el.gridstack = new GridStack(el, _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneDeep(options));
            }
            grids.push(el.gridstack);
        });
        if (grids.length === 0) {
            console.error('GridStack.initAll() no grid was found with selector "' + selector + '" - element missing or wrong selector ?' +
                '\nNote: ".grid-stack" is required for proper CSS styling and drag/drop, and is the default selector.');
        }
        return grids;
    }
    /**
     * call to create a grid with the given options, including loading any children from JSON structure. This will call GridStack.init(), then
     * grid.load() on any passed children (recursively). Great alternative to calling init() if you want entire grid to come from
     * JSON serialized data, including options.
     * @param parent HTML element parent to the grid
     * @param opt grids options used to initialize the grid, and list of children
     */
    static addGrid(parent, opt = {}) {
        if (!parent)
            return null;
        let el = parent;
        if (el.gridstack) {
            // already a grid - set option and load data
            const grid = el.gridstack;
            if (opt)
                grid.opts = { ...grid.opts, ...opt };
            if (opt.children !== undefined)
                grid.load(opt.children);
            return grid;
        }
        // create the grid element, but check if the passed 'parent' already has grid styling and should be used instead
        const parentIsGrid = parent.classList.contains('grid-stack');
        if (!parentIsGrid || GridStack.addRemoveCB) {
            if (GridStack.addRemoveCB) {
                el = GridStack.addRemoveCB(parent, opt, true, true);
            }
            else {
                el = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.createDiv(['grid-stack', opt.class], parent);
            }
        }
        // create grid class and load any children
        const grid = GridStack.init(opt, el);
        return grid;
    }
    /** call this method to register your engine instead of the default one.
     * See instead `GridStackOptions.engineClass` if you only need to
     * replace just one instance.
     */
    static registerEngine(engineClass) {
        GridStack.engineClass = engineClass;
    }
    /** @internal create placeholder DIV as needed */
    get placeholder() {
        if (!this._placeholder) {
            const placeholderChild = document.createElement('div'); // child so padding match item-content
            placeholderChild.className = 'placeholder-content';
            if (this.opts.placeholderText) {
                placeholderChild.textContent = this.opts.placeholderText;
            }
            this._placeholder = document.createElement('div');
            this._placeholder.classList.add(this.opts.placeholderClass, _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.itemClass, this.opts.itemClass);
            this.placeholder.appendChild(placeholderChild);
        }
        return this._placeholder;
    }
    /**
     * Construct a grid item from the given element and options
     * @param el the HTML element tied to this grid after it's been initialized
     * @param opts grid options - public for classes to access, but use methods to modify!
     */
    constructor(el, opts = {}) {
        this.el = el;
        this.opts = opts;
        /** time to wait for animation (if enabled) to be done so content sizing can happen */
        this.animationDelay = 300 + 10;
        /** @internal */
        this._gsEventHandler = {};
        /** @internal extra row added when dragging at the bottom of the grid */
        this._extraDragRow = 0;
        /** @internal meant to store the scale of the active grid */
        this.dragTransform = { xScale: 1, yScale: 1, xOffset: 0, yOffset: 0 };
        el.gridstack = this;
        this.opts = opts = opts || {}; // handles null/undefined/0
        if (!el.classList.contains('grid-stack')) {
            this.el.classList.add('grid-stack');
        }
        // if row property exists, replace minRow and maxRow instead
        if (opts.row) {
            opts.minRow = opts.maxRow = opts.row;
            delete opts.row;
        }
        const rowAttr = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-row'));
        // flag only valid in sub-grids (handled by parent, not here)
        if (opts.column === 'auto') {
            delete opts.column;
        }
        // save original setting so we can restore on save
        if (opts.alwaysShowResizeHandle !== undefined) {
            opts._alwaysShowResizeHandle = opts.alwaysShowResizeHandle;
        }
        let bk = opts.columnOpts?.breakpoints;
        // LEGACY: oneColumnMode stuff changed in v10.x - check if user explicitly set something to convert over
        const oldOpts = opts;
        if (oldOpts.oneColumnModeDomSort) {
            delete oldOpts.oneColumnModeDomSort;
            console.log('warning: Gridstack oneColumnModeDomSort no longer supported. Use GridStackOptions.columnOpts instead.');
        }
        if (oldOpts.oneColumnSize || oldOpts.disableOneColumnMode === false) {
            const oneSize = oldOpts.oneColumnSize || 768;
            delete oldOpts.oneColumnSize;
            delete oldOpts.disableOneColumnMode;
            opts.columnOpts = opts.columnOpts || {};
            bk = opts.columnOpts.breakpoints = opts.columnOpts.breakpoints || [];
            let oneColumn = bk.find(b => b.c === 1);
            if (!oneColumn) {
                oneColumn = { c: 1, w: oneSize };
                bk.push(oneColumn, { c: 12, w: oneSize + 1 });
            }
            else
                oneColumn.w = oneSize;
        }
        //...end LEGACY
        // cleanup responsive opts (must have columnWidth | breakpoints) then sort breakpoints by size (so we can match during resize)
        const resp = opts.columnOpts;
        if (resp) {
            if (!resp.columnWidth && !resp.breakpoints?.length) {
                delete opts.columnOpts;
                bk = undefined;
            }
            else {
                resp.columnMax = resp.columnMax || 12;
            }
        }
        if (bk?.length > 1)
            bk.sort((a, b) => (b.w || 0) - (a.w || 0));
        // elements DOM attributes override any passed options (like CSS style) - merge the two together
        const defaults = {
            ..._utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneDeep(_types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults),
            column: _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-column')) || _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.column,
            minRow: rowAttr ? rowAttr : _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-min-row')) || _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.minRow,
            maxRow: rowAttr ? rowAttr : _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-max-row')) || _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.maxRow,
            staticGrid: _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toBool(el.getAttribute('gs-static')) || _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.staticGrid,
            draggable: {
                handle: (opts.handleClass ? '.' + opts.handleClass : (opts.handle ? opts.handle : '')) || _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.draggable.handle,
            },
            removableOptions: {
                accept: opts.itemClass || _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.removableOptions.accept,
                decline: _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.removableOptions.decline
            },
        };
        if (el.getAttribute('gs-animate')) { // default to true, but if set to false use that instead
            defaults.animate = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toBool(el.getAttribute('gs-animate'));
        }
        opts = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.defaults(opts, defaults);
        this._initMargin(); // part of settings defaults...
        // Now check if we're loading into 1 column mode FIRST so we don't do un-necessary work (like cellHeight = width / 12 then go 1 column)
        this.checkDynamicColumn();
        this.el.classList.add('gs-' + opts.column);
        if (opts.rtl === 'auto') {
            opts.rtl = (el.style.direction === 'rtl');
        }
        if (opts.rtl) {
            this.el.classList.add('grid-stack-rtl');
        }
        // check if we're been nested, and if so update our style and keep pointer around (used during save)
        const parentGridItem = this.el.closest('.' + _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.itemClass);
        const parentNode = parentGridItem?.gridstackNode;
        if (parentNode) {
            parentNode.subGrid = this;
            this.parentGridNode = parentNode;
            this.el.classList.add('grid-stack-nested');
            parentNode.el.classList.add('grid-stack-sub-grid');
        }
        this._isAutoCellHeight = (opts.cellHeight === 'auto');
        if (this._isAutoCellHeight || opts.cellHeight === 'initial') {
            // make the cell content square initially (will use resize/column event to keep it square)
            this.cellHeight(undefined, false);
        }
        else {
            // append unit if any are set
            if (typeof opts.cellHeight == 'number' && opts.cellHeightUnit && opts.cellHeightUnit !== _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.cellHeightUnit) {
                opts.cellHeight = opts.cellHeight + opts.cellHeightUnit;
                delete opts.cellHeightUnit;
            }
            this.cellHeight(opts.cellHeight, false);
        }
        // see if we need to adjust auto-hide
        if (opts.alwaysShowResizeHandle === 'mobile') {
            opts.alwaysShowResizeHandle = _dd_touch__WEBPACK_IMPORTED_MODULE_4__.isTouch;
        }
        this._styleSheetClass = 'gs-id-' + _gridstack_engine__WEBPACK_IMPORTED_MODULE_0__.GridStackEngine._idSeq++;
        this.el.classList.add(this._styleSheetClass);
        this._setStaticClass();
        const engineClass = opts.engineClass || GridStack.engineClass || _gridstack_engine__WEBPACK_IMPORTED_MODULE_0__.GridStackEngine;
        this.engine = new engineClass({
            column: this.getColumn(),
            float: opts.float,
            maxRow: opts.maxRow,
            onChange: (cbNodes) => {
                let maxH = 0;
                this.engine.nodes.forEach(n => { maxH = Math.max(maxH, n.y + n.h); });
                cbNodes.forEach(n => {
                    const el = n.el;
                    if (!el)
                        return;
                    if (n._removeDOM) {
                        if (el)
                            el.remove();
                        delete n._removeDOM;
                    }
                    else {
                        this._writePosAttr(el, n);
                    }
                });
                this._updateStyles(false, maxH); // false = don't recreate, just append if need be
            }
        });
        // create initial global styles BEFORE loading children so resizeToContent margin can be calculated correctly
        this._updateStyles(false, 0);
        if (opts.auto) {
            this.batchUpdate(); // prevent in between re-layout #1535 TODO: this only set float=true, need to prevent collision check...
            this.engine._loading = true; // loading collision check
            this.getGridItems().forEach(el => this._prepareElement(el));
            delete this.engine._loading;
            this.batchUpdate(false);
        }
        // load any passed in children as well, which overrides any DOM layout done above
        if (opts.children) {
            const children = opts.children;
            delete opts.children;
            if (children.length)
                this.load(children); // don't load empty
        }
        // if (this.engine.nodes.length) this._updateStyles(); // update based on # of children. done in engine onChange CB
        this.setAnimation();
        // dynamic grids require pausing during drag to detect over to nest vs push
        if (opts.subGridDynamic && !_dd_manager__WEBPACK_IMPORTED_MODULE_5__.DDManager.pauseDrag)
            _dd_manager__WEBPACK_IMPORTED_MODULE_5__.DDManager.pauseDrag = true;
        if (opts.draggable?.pause !== undefined)
            _dd_manager__WEBPACK_IMPORTED_MODULE_5__.DDManager.pauseDrag = opts.draggable.pause;
        this._setupRemoveDrop();
        this._setupAcceptWidget();
        this._updateResizeEvent();
    }
    /**
     * add a new widget and returns it.
     *
     * Widget will be always placed even if result height is more than actual grid height.
     * You need to use `willItFit()` before calling addWidget for additional check.
     * See also `makeWidget(el)` for DOM element.
     *
     * @example
     * const grid = GridStack.init();
     * grid.addWidget({w: 3, content: 'hello'});
     *
     * @param w GridStackWidget definition. used MakeWidget(el) if you have dom element instead.
     * @param options widget position/size options (optional, and ignore if first param is already option) - see GridStackWidget
     */
    addWidget(w) {
        if (typeof w === 'string') {
            console.error('V11: GridStack.addWidget() does not support string anymore. see #2736');
            return;
        }
        if (w.ELEMENT_NODE) {
            console.error('V11: GridStack.addWidget() does not support HTMLElement anymore. use makeWidget()');
            return this.makeWidget(w);
        }
        let el;
        let node = w;
        node.grid = this;
        if (node?.el) {
            el = node.el; // re-use element stored in the node
        }
        else if (GridStack.addRemoveCB) {
            el = GridStack.addRemoveCB(this.el, w, true, false);
        }
        else {
            el = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.createWidgetDivs(this.opts.itemClass, node);
        }
        if (!el)
            return;
        // if the caller ended up initializing the widget in addRemoveCB, or we stared with one already, skip the rest
        node = el.gridstackNode;
        if (node && el.parentElement === this.el && this.engine.nodes.find(n => n._id === node._id))
            return el;
        // Tempting to initialize the passed in opt with default and valid values, but this break knockout demos
        // as the actual value are filled in when _prepareElement() calls el.getAttribute('gs-xyz') before adding the node.
        // So make sure we load any DOM attributes that are not specified in passed in options (which override)
        const domAttr = this._readAttr(el);
        _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.defaults(w, domAttr);
        this.engine.prepareNode(w);
        // this._writeAttr(el, w); why write possibly incorrect values back when makeWidget() will ?
        this.el.appendChild(el);
        this.makeWidget(el, w);
        return el;
    }
    /**
     * Convert an existing gridItem element into a sub-grid with the given (optional) options, else inherit them
     * from the parent's subGrid options.
     * @param el gridItem element to convert
     * @param ops (optional) sub-grid options, else default to node, then parent settings, else defaults
     * @param nodeToAdd (optional) node to add to the newly created sub grid (used when dragging over existing regular item)
     * @param saveContent if true (default) the html inside .grid-stack-content will be saved to child widget
     * @returns newly created grid
     */
    makeSubGrid(el, ops, nodeToAdd, saveContent = true) {
        let node = el.gridstackNode;
        if (!node) {
            node = this.makeWidget(el).gridstackNode;
        }
        if (node.subGrid?.el)
            return node.subGrid; // already done
        // find the template subGrid stored on a parent as fallback...
        let subGridTemplate; // eslint-disable-next-line @typescript-eslint/no-this-alias
        let grid = this;
        while (grid && !subGridTemplate) {
            subGridTemplate = grid.opts?.subGridOpts;
            grid = grid.parentGridNode?.grid;
        }
        //... and set the create options
        ops = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneDeep({
            // by default sub-grid inherit from us | parent, other than id, children, etc...
            ...this.opts, id: undefined, children: undefined, column: 'auto', columnOpts: undefined, layout: 'list', subGridOpts: undefined,
            ...(subGridTemplate || {}),
            ...(ops || node.subGridOpts || {})
        });
        node.subGridOpts = ops;
        // if column special case it set, remember that flag and set default
        let autoColumn;
        if (ops.column === 'auto') {
            autoColumn = true;
            ops.column = Math.max(node.w || 1, nodeToAdd?.w || 1);
            delete ops.columnOpts; // driven by parent
        }
        // if we're converting an existing full item, move over the content to be the first sub item in the new grid
        let content = node.el.querySelector('.grid-stack-item-content');
        let newItem;
        let newItemOpt;
        if (saveContent) {
            this._removeDD(node.el); // remove D&D since it's set on content div
            newItemOpt = { ...node, x: 0, y: 0 };
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.removeInternalForSave(newItemOpt);
            delete newItemOpt.subGridOpts;
            if (node.content) {
                newItemOpt.content = node.content;
                delete node.content;
            }
            if (GridStack.addRemoveCB) {
                newItem = GridStack.addRemoveCB(this.el, newItemOpt, true, false);
            }
            else {
                newItem = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.createDiv(['grid-stack-item']);
                newItem.appendChild(content);
                content = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.createDiv(['grid-stack-item-content'], node.el);
            }
            this._prepareDragDropByNode(node); // ... and restore original D&D
        }
        // if we're adding an additional item, make the container large enough to have them both
        if (nodeToAdd) {
            const w = autoColumn ? ops.column : node.w;
            const h = node.h + nodeToAdd.h;
            const style = node.el.style;
            style.transition = 'none'; // show up instantly so we don't see scrollbar with nodeToAdd
            this.update(node.el, { w, h });
            setTimeout(() => style.transition = null); // recover animation
        }
        const subGrid = node.subGrid = GridStack.addGrid(content, ops);
        if (nodeToAdd?._moving)
            subGrid._isTemp = true; // prevent re-nesting as we add over
        if (autoColumn)
            subGrid._autoColumn = true;
        // add the original content back as a child of hte newly created grid
        if (saveContent) {
            subGrid.makeWidget(newItem, newItemOpt);
        }
        // now add any additional node
        if (nodeToAdd) {
            if (nodeToAdd._moving) {
                // create an artificial event even for the just created grid to receive this item
                window.setTimeout(() => _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.simulateMouseEvent(nodeToAdd._event, 'mouseenter', subGrid.el), 0);
            }
            else {
                subGrid.makeWidget(node.el, node);
            }
        }
        // if sizedToContent, we need to re-calc the size of ourself
        this.resizeToContentCheck(false, node);
        return subGrid;
    }
    /**
     * called when an item was converted into a nested grid to accommodate a dragged over item, but then item leaves - return back
     * to the original grid-item. Also called to remove empty sub-grids when last item is dragged out (since re-creating is simple)
     */
    removeAsSubGrid(nodeThatRemoved) {
        const pGrid = this.parentGridNode?.grid;
        if (!pGrid)
            return;
        pGrid.batchUpdate();
        pGrid.removeWidget(this.parentGridNode.el, true, true);
        this.engine.nodes.forEach(n => {
            // migrate any children over and offsetting by our location
            n.x += this.parentGridNode.x;
            n.y += this.parentGridNode.y;
            pGrid.makeWidget(n.el, n);
        });
        pGrid.batchUpdate(false);
        if (this.parentGridNode)
            delete this.parentGridNode.subGrid;
        delete this.parentGridNode;
        // create an artificial event for the original grid now that this one is gone (got a leave, but won't get enter)
        if (nodeThatRemoved) {
            window.setTimeout(() => _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.simulateMouseEvent(nodeThatRemoved._event, 'mouseenter', pGrid.el), 0);
        }
    }
    /**
     * saves the current layout returning a list of widgets for serialization which might include any nested grids.
     * @param saveContent if true (default) the latest html inside .grid-stack-content will be saved to GridStackWidget.content field, else it will
     * be removed.
     * @param saveGridOpt if true (default false), save the grid options itself, so you can call the new GridStack.addGrid()
     * to recreate everything from scratch. GridStackOptions.children would then contain the widget list instead.
     * @param saveCB callback for each node -> widget, so application can insert additional data to be saved into the widget data structure.
     * @returns list of widgets or full grid option, including .children list of widgets
     */
    save(saveContent = true, saveGridOpt = false, saveCB = GridStack.saveCB) {
        // return copied GridStackWidget (with optionally .el) we can modify at will...
        const list = this.engine.save(saveContent, saveCB);
        // check for HTML content and nested grids
        list.forEach(n => {
            if (saveContent && n.el && !n.subGrid && !saveCB) { // sub-grid are saved differently, not plain content
                const itemContent = n.el.querySelector('.grid-stack-item-content');
                n.content = itemContent?.innerHTML;
                if (!n.content)
                    delete n.content;
            }
            else {
                if (!saveContent && !saveCB) {
                    delete n.content;
                }
                // check for nested grid
                if (n.subGrid?.el) {
                    const listOrOpt = n.subGrid.save(saveContent, saveGridOpt, saveCB);
                    n.subGridOpts = (saveGridOpt ? listOrOpt : { children: listOrOpt });
                    delete n.subGrid;
                }
            }
            delete n.el;
        });
        // check if save entire grid options (needed for recursive) + children...
        if (saveGridOpt) {
            const o = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneDeep(this.opts);
            // delete default values that will be recreated on launch
            if (o.marginBottom === o.marginTop && o.marginRight === o.marginLeft && o.marginTop === o.marginRight) {
                o.margin = o.marginTop;
                delete o.marginTop;
                delete o.marginRight;
                delete o.marginBottom;
                delete o.marginLeft;
            }
            if (o.rtl === (this.el.style.direction === 'rtl')) {
                o.rtl = 'auto';
            }
            if (this._isAutoCellHeight) {
                o.cellHeight = 'auto';
            }
            if (this._autoColumn) {
                o.column = 'auto';
            }
            const origShow = o._alwaysShowResizeHandle;
            delete o._alwaysShowResizeHandle;
            if (origShow !== undefined) {
                o.alwaysShowResizeHandle = origShow;
            }
            else {
                delete o.alwaysShowResizeHandle;
            }
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.removeInternalAndSame(o, _types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults);
            o.children = list;
            return o;
        }
        return list;
    }
    /**
     * load the widgets from a list. This will call update() on each (matching by id) or add/remove widgets that are not there.
     *
     * @param layout list of widgets definition to update/create
     * @param addAndRemove boolean (default true) or callback method can be passed to control if and how missing widgets can be added/removed, giving
     * the user control of insertion.
     *
     * @example
     * see http://gridstackjs.com/demo/serialization.html
     */
    load(items, addRemove = GridStack.addRemoveCB || true) {
        items = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneDeep(items); // so we can mod
        const column = this.getColumn();
        // make sure size 1x1 (default) is present as it may need to override current sizes
        items.forEach(n => { n.w = n.w || 1; n.h = n.h || 1; });
        // sort items. those without coord will be appended last
        items = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.sort(items);
        // if we're loading a layout into for example 1 column and items don't fit, make sure to save
        // the original wanted layout so we can scale back up correctly #1471
        let maxColumn = 0;
        items.forEach(n => { maxColumn = Math.max(maxColumn, (n.x || 0) + n.w); });
        if (maxColumn > this.engine.defaultColumn)
            this.engine.defaultColumn = maxColumn;
        if (maxColumn > column) {
            this._ignoreLayoutsNodeChange = true; // skip layout update
            this.engine.cacheLayout(items, maxColumn, true);
        }
        // if given a different callback, temporally set it as global option so creating will use it
        const prevCB = GridStack.addRemoveCB;
        if (typeof (addRemove) === 'function')
            GridStack.addRemoveCB = addRemove;
        const removed = [];
        this.batchUpdate();
        // if we are loading from empty temporarily remove animation
        const blank = !this.engine.nodes.length;
        if (blank)
            this.setAnimation(false);
        // see if any items are missing from new layout and need to be removed first
        if (!blank && addRemove) {
            const copyNodes = [...this.engine.nodes]; // don't loop through array you modify
            copyNodes.forEach(n => {
                if (!n.id)
                    return;
                const item = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.find(items, n.id);
                if (!item) {
                    if (GridStack.addRemoveCB)
                        GridStack.addRemoveCB(this.el, n, false, false);
                    removed.push(n); // batch keep track
                    this.removeWidget(n.el, true, false);
                }
            });
        }
        // now add/update the widgets - starting with removing items in the new layout we will reposition
        // to reduce collision and add no-coord ones at next available spot
        this.engine._loading = true; // help with collision
        const updateNodes = [];
        this.engine.nodes = this.engine.nodes.filter(n => {
            if (_utils__WEBPACK_IMPORTED_MODULE_1__.Utils.find(items, n.id)) {
                updateNodes.push(n);
                return false;
            } // remove if found from list
            return true;
        });
        items.forEach(w => {
            const item = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.find(updateNodes, w.id);
            if (item) {
                // if item sizes to content, re-use the exiting height so it's a better guess at the final size (same if width doesn't change)
                if (_utils__WEBPACK_IMPORTED_MODULE_1__.Utils.shouldSizeToContent(item))
                    w.h = item.h;
                // check if missing coord, in which case find next empty slot with new (or old if missing) sizes
                this.engine.nodeBoundFix(w);
                if (w.autoPosition || w.x === undefined || w.y === undefined) {
                    w.w = w.w || item.w;
                    w.h = w.h || item.h;
                    this.engine.findEmptyPosition(w);
                }
                // add back to current list BUT force a collision check if it 'appears' we didn't change to make sure we don't overlap others now
                this.engine.nodes.push(item);
                if (_utils__WEBPACK_IMPORTED_MODULE_1__.Utils.samePos(item, w) && this.engine.nodes.length > 1) {
                    this.moveNode(item, { ...w, forceCollide: true });
                    _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.copyPos(w, item); // use possily updated values before update() is called next (no-op since already moved)
                }
                this.update(item.el, w);
                if (w.subGridOpts?.children) { // update any sub grid as well
                    const sub = item.el.querySelector('.grid-stack');
                    if (sub && sub.gridstack) {
                        sub.gridstack.load(w.subGridOpts.children); // TODO: support updating grid options ?
                    }
                }
            }
            else if (addRemove) {
                this.addWidget(w);
            }
        });
        delete this.engine._loading; // done loading
        this.engine.removedNodes = removed;
        this.batchUpdate(false);
        // after commit, clear that flag
        delete this._ignoreLayoutsNodeChange;
        prevCB ? GridStack.addRemoveCB = prevCB : delete GridStack.addRemoveCB;
        // delay adding animation back
        if (blank && this.opts?.animate)
            this.setAnimation(this.opts.animate, true);
        return this;
    }
    /**
     * use before calling a bunch of `addWidget()` to prevent un-necessary relayouts in between (more efficient)
     * and get a single event callback. You will see no changes until `batchUpdate(false)` is called.
     */
    batchUpdate(flag = true) {
        this.engine.batchUpdate(flag);
        if (!flag) {
            this._updateContainerHeight();
            this._triggerRemoveEvent();
            this._triggerAddEvent();
            this._triggerChangeEvent();
        }
        return this;
    }
    /**
     * Gets current cell height.
     */
    getCellHeight(forcePixel = false) {
        if (this.opts.cellHeight && this.opts.cellHeight !== 'auto' &&
            (!forcePixel || !this.opts.cellHeightUnit || this.opts.cellHeightUnit === 'px')) {
            return this.opts.cellHeight;
        }
        // do rem/em/cm/mm to px conversion
        if (this.opts.cellHeightUnit === 'rem') {
            return this.opts.cellHeight * parseFloat(getComputedStyle(document.documentElement).fontSize);
        }
        if (this.opts.cellHeightUnit === 'em') {
            return this.opts.cellHeight * parseFloat(getComputedStyle(this.el).fontSize);
        }
        if (this.opts.cellHeightUnit === 'cm') {
            // 1cm = 96px/2.54. See https://www.w3.org/TR/css-values-3/#absolute-lengths
            return this.opts.cellHeight * (96 / 2.54);
        }
        if (this.opts.cellHeightUnit === 'mm') {
            return this.opts.cellHeight * (96 / 2.54) / 10;
        }
        // else get first cell height
        const el = this.el.querySelector('.' + this.opts.itemClass);
        if (el) {
            const h = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-h')) || 1; // since we don't write 1 anymore
            return Math.round(el.offsetHeight / h);
        }
        // else do entire grid and # of rows (but doesn't work if min-height is the actual constrain)
        const rows = parseInt(this.el.getAttribute('gs-current-row'));
        return rows ? Math.round(this.el.getBoundingClientRect().height / rows) : this.opts.cellHeight;
    }
    /**
     * Update current cell height - see `GridStackOptions.cellHeight` for format.
     * This method rebuilds an internal CSS style sheet.
     * Note: You can expect performance issues if call this method too often.
     *
     * @param val the cell height. If not passed (undefined), cells content will be made square (match width minus margin),
     * if pass 0 the CSS will be generated by the application instead.
     * @param update (Optional) if false, styles will not be updated
     *
     * @example
     * grid.cellHeight(100); // same as 100px
     * grid.cellHeight('70px');
     * grid.cellHeight(grid.cellWidth() * 1.2);
     */
    cellHeight(val, update = true) {
        // if not called internally, check if we're changing mode
        if (update && val !== undefined) {
            if (this._isAutoCellHeight !== (val === 'auto')) {
                this._isAutoCellHeight = (val === 'auto');
                this._updateResizeEvent();
            }
        }
        if (val === 'initial' || val === 'auto') {
            val = undefined;
        }
        // make item content be square
        if (val === undefined) {
            const marginDiff = -this.opts.marginRight - this.opts.marginLeft
                + this.opts.marginTop + this.opts.marginBottom;
            val = this.cellWidth() + marginDiff;
        }
        const data = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(val);
        if (this.opts.cellHeightUnit === data.unit && this.opts.cellHeight === data.h) {
            return this;
        }
        this.opts.cellHeightUnit = data.unit;
        this.opts.cellHeight = data.h;
        this.resizeToContentCheck();
        if (update) {
            this._updateStyles(true); // true = force re-create for current # of rows
        }
        return this;
    }
    /** Gets current cell width. */
    cellWidth() {
        return this._widthOrContainer() / this.getColumn();
    }
    /** return our expected width (or parent) , and optionally of window for dynamic column check */
    _widthOrContainer(forBreakpoint = false) {
        // use `offsetWidth` or `clientWidth` (no scrollbar) ?
        // https://stackoverflow.com/questions/21064101/understanding-offsetwidth-clientwidth-scrollwidth-and-height-respectively
        return forBreakpoint && this.opts.columnOpts?.breakpointForWindow ? window.innerWidth : (this.el.clientWidth || this.el.parentElement.clientWidth || window.innerWidth);
    }
    /** checks for dynamic column count for our current size, returning true if changed */
    checkDynamicColumn() {
        const resp = this.opts.columnOpts;
        if (!resp || (!resp.columnWidth && !resp.breakpoints?.length))
            return false;
        const column = this.getColumn();
        let newColumn = column;
        const w = this._widthOrContainer(true);
        if (resp.columnWidth) {
            newColumn = Math.min(Math.round(w / resp.columnWidth) || 1, resp.columnMax);
        }
        else {
            // find the closest breakpoint (already sorted big to small) that matches
            newColumn = resp.columnMax;
            let i = 0;
            while (i < resp.breakpoints.length && w <= resp.breakpoints[i].w) {
                newColumn = resp.breakpoints[i++].c || column;
            }
        }
        if (newColumn !== column) {
            const bk = resp.breakpoints?.find(b => b.c === newColumn);
            this.column(newColumn, bk?.layout || resp.layout);
            return true;
        }
        return false;
    }
    /**
     * re-layout grid items to reclaim any empty space. Options are:
     * 'list' keep the widget left->right order the same, even if that means leaving an empty slot if things don't fit
     * 'compact' might re-order items to fill any empty space
     *
     * doSort - 'false' to let you do your own sorting ahead in case you need to control a different order. (default to sort)
     */
    compact(layout = 'compact', doSort = true) {
        this.engine.compact(layout, doSort);
        this._triggerChangeEvent();
        return this;
    }
    /**
     * set the number of columns in the grid. Will update existing widgets to conform to new number of columns,
     * as well as cache the original layout so you can revert back to previous positions without loss.
     * Requires `gridstack-extra.css` or `gridstack-extra.min.css` for [2-11],
     * else you will need to generate correct CSS (see https://github.com/gridstack/gridstack.js#change-grid-columns)
     * @param column - Integer > 0 (default 12).
     * @param layout specify the type of re-layout that will happen (position, size, etc...).
     * Note: items will never be outside of the current column boundaries. default ('moveScale'). Ignored for 1 column
     */
    column(column, layout = 'moveScale') {
        if (!column || column < 1 || this.opts.column === column)
            return this;
        const oldColumn = this.getColumn();
        this.opts.column = column;
        if (!this.engine)
            return this; // called in constructor, noting else to do
        this.engine.column = column;
        this.el.classList.remove('gs-' + oldColumn);
        this.el.classList.add('gs-' + column);
        // update the items now, checking if we have a custom children layout
        /*const newChildren = this.opts.columnOpts?.breakpoints?.find(r => r.c === column)?.children;
        if (newChildren) this.load(newChildren);
        else*/ this.engine.columnChanged(oldColumn, column, layout);
        if (this._isAutoCellHeight)
            this.cellHeight();
        this.resizeToContentCheck(true); // wait for width resizing
        // and trigger our event last...
        this._ignoreLayoutsNodeChange = true; // skip layout update
        this._triggerChangeEvent();
        delete this._ignoreLayoutsNodeChange;
        return this;
    }
    /**
     * get the number of columns in the grid (default 12)
     */
    getColumn() { return this.opts.column; }
    /** returns an array of grid HTML elements (no placeholder) - used to iterate through our children in DOM order */
    getGridItems() {
        return Array.from(this.el.children)
            .filter((el) => el.matches('.' + this.opts.itemClass) && !el.matches('.' + this.opts.placeholderClass));
    }
    /**
     * Destroys a grid instance. DO NOT CALL any methods or access any vars after this as it will free up members.
     * @param removeDOM if `false` grid and items HTML elements will not be removed from the DOM (Optional. Default `true`).
     */
    destroy(removeDOM = true) {
        if (!this.el)
            return; // prevent multiple calls
        this.offAll();
        this._updateResizeEvent(true);
        this.setStatic(true, false); // permanently removes DD but don't set CSS class (we're going away)
        this.setAnimation(false);
        if (!removeDOM) {
            this.removeAll(removeDOM);
            this.el.classList.remove(this._styleSheetClass);
            this.el.removeAttribute('gs-current-row');
        }
        else {
            this.el.parentNode.removeChild(this.el);
        }
        this._removeStylesheet();
        if (this.parentGridNode)
            delete this.parentGridNode.subGrid;
        delete this.parentGridNode;
        delete this.opts;
        delete this._placeholder;
        delete this.engine;
        delete this.el.gridstack; // remove circular dependency that would prevent a freeing
        delete this.el;
        return this;
    }
    /**
     * enable/disable floating widgets (default: `false`) See [example](http://gridstackjs.com/demo/float.html)
     */
    float(val) {
        if (this.opts.float !== val) {
            this.opts.float = this.engine.float = val;
            this._triggerChangeEvent();
        }
        return this;
    }
    /**
     * get the current float mode
     */
    getFloat() {
        return this.engine.float;
    }
    /**
     * Get the position of the cell under a pixel on screen.
     * @param position the position of the pixel to resolve in
     * absolute coordinates, as an object with top and left properties
     * @param useDocRelative if true, value will be based on document position vs parent position (Optional. Default false).
     * Useful when grid is within `position: relative` element
     *
     * Returns an object with properties `x` and `y` i.e. the column and row in the grid.
     */
    getCellFromPixel(position, useDocRelative = false) {
        const box = this.el.getBoundingClientRect();
        // console.log(`getBoundingClientRect left: ${box.left} top: ${box.top} w: ${box.w} h: ${box.h}`)
        let containerPos;
        if (useDocRelative) {
            containerPos = { top: box.top + document.documentElement.scrollTop, left: box.left };
            // console.log(`getCellFromPixel scrollTop: ${document.documentElement.scrollTop}`)
        }
        else {
            containerPos = { top: this.el.offsetTop, left: this.el.offsetLeft };
            // console.log(`getCellFromPixel offsetTop: ${containerPos.left} offsetLeft: ${containerPos.top}`)
        }
        const relativeLeft = position.left - containerPos.left;
        const relativeTop = position.top - containerPos.top;
        const columnWidth = (box.width / this.getColumn());
        const rowHeight = (box.height / parseInt(this.el.getAttribute('gs-current-row')));
        return { x: Math.floor(relativeLeft / columnWidth), y: Math.floor(relativeTop / rowHeight) };
    }
    /** returns the current number of rows, which will be at least `minRow` if set */
    getRow() {
        return Math.max(this.engine.getRow(), this.opts.minRow);
    }
    /**
     * Checks if specified area is empty.
     * @param x the position x.
     * @param y the position y.
     * @param w the width of to check
     * @param h the height of to check
     */
    isAreaEmpty(x, y, w, h) {
        return this.engine.isAreaEmpty(x, y, w, h);
    }
    /**
     * If you add elements to your grid by hand (or have some framework creating DOM), you have to tell gridstack afterwards to make them widgets.
     * If you want gridstack to add the elements for you, use `addWidget()` instead.
     * Makes the given element a widget and returns it.
     * @param els widget or single selector to convert.
     * @param options widget definition to use instead of reading attributes or using default sizing values
     *
     * @example
     * const grid = GridStack.init();
     * grid.el.innerHtml = '<div id="1" gs-w="3"></div><div id="2"></div>';
     * grid.makeWidget('1');
     * grid.makeWidget('2', {w:2, content: 'hello'});
     */
    makeWidget(els, options) {
        const el = GridStack.getElement(els);
        if (!el)
            return;
        if (!el.parentElement)
            this.el.appendChild(el);
        this._prepareElement(el, true, options);
        const node = el.gridstackNode;
        this._updateContainerHeight();
        // see if there is a sub-grid to create
        if (node.subGridOpts) {
            this.makeSubGrid(el, node.subGridOpts, undefined, false); // node.subGrid will be used as option in method, no need to pass
        }
        // if we're adding an item into 1 column make sure
        // we don't override the larger 12 column layout that was already saved. #1985
        if (this.opts.column === 1) {
            this._ignoreLayoutsNodeChange = true;
        }
        this._triggerAddEvent();
        this._triggerChangeEvent();
        delete this._ignoreLayoutsNodeChange;
        return el;
    }
    on(name, callback) {
        // check for array of names being passed instead
        if (name.indexOf(' ') !== -1) {
            const names = name.split(' ');
            names.forEach(name => this.on(name, callback));
            return this;
        }
        // native CustomEvent handlers - cash the generic handlers so we can easily remove
        if (name === 'change' || name === 'added' || name === 'removed' || name === 'enable' || name === 'disable') {
            const noData = (name === 'enable' || name === 'disable');
            if (noData) {
                this._gsEventHandler[name] = (event) => callback(event);
            }
            else {
                this._gsEventHandler[name] = (event) => { if (event.detail)
                    callback(event, event.detail); };
            }
            this.el.addEventListener(name, this._gsEventHandler[name]);
        }
        else if (name === 'drag' || name === 'dragstart' || name === 'dragstop' || name === 'resizestart' || name === 'resize'
            || name === 'resizestop' || name === 'dropped' || name === 'resizecontent') {
            // drag&drop stop events NEED to be call them AFTER we update node attributes so handle them ourself.
            // do same for start event to make it easier...
            this._gsEventHandler[name] = callback;
        }
        else {
            console.error('GridStack.on(' + name + ') event not supported');
        }
        return this;
    }
    /**
     * unsubscribe from the 'on' event GridStackEvent
     * @param name of the event (see possible values) or list of names space separated
     */
    off(name) {
        // check for array of names being passed instead
        if (name.indexOf(' ') !== -1) {
            const names = name.split(' ');
            names.forEach(name => this.off(name));
            return this;
        }
        if (name === 'change' || name === 'added' || name === 'removed' || name === 'enable' || name === 'disable') {
            // remove native CustomEvent handlers
            if (this._gsEventHandler[name]) {
                this.el.removeEventListener(name, this._gsEventHandler[name]);
            }
        }
        delete this._gsEventHandler[name];
        return this;
    }
    /** remove all event handlers */
    offAll() {
        Object.keys(this._gsEventHandler).forEach((key) => this.off(key));
        return this;
    }
    /**
     * Removes widget from the grid.
     * @param el  widget or selector to modify
     * @param removeDOM if `false` DOM element won't be removed from the tree (Default? true).
     * @param triggerEvent if `false` (quiet mode) element will not be added to removed list and no 'removed' callbacks will be called (Default? true).
     */
    removeWidget(els, removeDOM = true, triggerEvent = true) {
        if (!els) {
            console.error('Error: GridStack.removeWidget(undefined) called');
            return this;
        }
        GridStack.getElements(els).forEach(el => {
            if (el.parentElement && el.parentElement !== this.el)
                return; // not our child!
            let node = el.gridstackNode;
            // For Meteor support: https://github.com/gridstack/gridstack.js/pull/272
            if (!node) {
                node = this.engine.nodes.find(n => el === n.el);
            }
            if (!node)
                return;
            if (removeDOM && GridStack.addRemoveCB) {
                GridStack.addRemoveCB(this.el, node, false, false);
            }
            // remove our DOM data (circular link) and drag&drop permanently
            delete el.gridstackNode;
            this._removeDD(el);
            this.engine.removeNode(node, removeDOM, triggerEvent);
            if (removeDOM && el.parentElement) {
                el.remove(); // in batch mode engine.removeNode doesn't call back to remove DOM
            }
        });
        if (triggerEvent) {
            this._triggerRemoveEvent();
            this._triggerChangeEvent();
        }
        return this;
    }
    /**
     * Removes all widgets from the grid.
     * @param removeDOM if `false` DOM elements won't be removed from the tree (Default? `true`).
     * @param triggerEvent if `false` (quiet mode) element will not be added to removed list and no 'removed' callbacks will be called (Default? true).
     */
    removeAll(removeDOM = true, triggerEvent = true) {
        // always remove our DOM data (circular link) before list gets emptied and drag&drop permanently
        this.engine.nodes.forEach(n => {
            if (removeDOM && GridStack.addRemoveCB) {
                GridStack.addRemoveCB(this.el, n, false, false);
            }
            delete n.el.gridstackNode;
            if (!this.opts.staticGrid)
                this._removeDD(n.el);
        });
        this.engine.removeAll(removeDOM, triggerEvent);
        if (triggerEvent)
            this._triggerRemoveEvent();
        return this;
    }
    /**
     * Toggle the grid animation state.  Toggles the `grid-stack-animate` class.
     * @param doAnimate if true the grid will animate.
     * @param delay if true setting will be set on next event loop.
     */
    setAnimation(doAnimate = this.opts.animate, delay) {
        if (delay) {
            // delay, but check to make sure grid (opt) is still around
            setTimeout(() => { if (this.opts)
                this.setAnimation(doAnimate); });
        }
        else if (doAnimate) {
            this.el.classList.add('grid-stack-animate');
        }
        else {
            this.el.classList.remove('grid-stack-animate');
        }
        return this;
    }
    /** @internal */
    hasAnimationCSS() { return this.el.classList.contains('grid-stack-animate'); }
    /**
     * Toggle the grid static state, which permanently removes/add Drag&Drop support, unlike disable()/enable() that just turns it off/on.
     * Also toggle the grid-stack-static class.
     * @param val if true the grid become static.
     * @param updateClass true (default) if css class gets updated
     * @param recurse true (default) if sub-grids also get updated
     */
    setStatic(val, updateClass = true, recurse = true) {
        if (!!this.opts.staticGrid === val)
            return this;
        val ? this.opts.staticGrid = true : delete this.opts.staticGrid;
        this._setupRemoveDrop();
        this._setupAcceptWidget();
        this.engine.nodes.forEach(n => {
            this._prepareDragDropByNode(n); // either delete or init Drag&drop
            if (n.subGrid && recurse)
                n.subGrid.setStatic(val, updateClass, recurse);
        });
        if (updateClass) {
            this._setStaticClass();
        }
        return this;
    }
    /**
     * Updates widget position/size and other info. Note: if you need to call this on all nodes, use load() instead which will update what changed.
     * @param els  widget or selector of objects to modify (note: setting the same x,y for multiple items will be indeterministic and likely unwanted)
     * @param opt new widget options (x,y,w,h, etc..). Only those set will be updated.
     */
    update(els, opt) {
        // support legacy call for now ?
        if (arguments.length > 2) {
            console.warn('gridstack.ts: `update(el, x, y, w, h)` is deprecated. Use `update(el, {x, w, content, ...})`. It will be removed soon');
            // eslint-disable-next-line prefer-rest-params
            const a = arguments;
            let i = 1;
            opt = { x: a[i++], y: a[i++], w: a[i++], h: a[i++] };
            return this.update(els, opt);
        }
        GridStack.getElements(els).forEach(el => {
            const n = el?.gridstackNode;
            if (!n)
                return;
            const w = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.cloneDeep(opt); // make a copy we can modify in case they re-use it or multiple items
            this.engine.nodeBoundFix(w);
            delete w.autoPosition;
            // move/resize widget if anything changed
            const keys = ['x', 'y', 'w', 'h'];
            let m;
            if (keys.some(k => w[k] !== undefined && w[k] !== n[k])) {
                m = {};
                keys.forEach(k => {
                    m[k] = (w[k] !== undefined) ? w[k] : n[k];
                    delete w[k];
                });
            }
            // for a move as well IFF there is any min/max fields set
            if (!m && (w.minW || w.minH || w.maxW || w.maxH)) {
                m = {}; // will use node position but validate values
            }
            // check for content changing
            if (w.content !== undefined) {
                const itemContent = el.querySelector('.grid-stack-item-content');
                if (itemContent && itemContent.textContent !== w.content) {
                    n.content = w.content;
                    GridStack.renderCB(itemContent, w);
                    // restore any sub-grid back
                    if (n.subGrid?.el) {
                        itemContent.appendChild(n.subGrid.el);
                        if (!n.subGrid.opts.styleInHead)
                            n.subGrid._updateStyles(true); // force create
                    }
                }
                delete w.content;
            }
            // any remaining fields are assigned, but check for dragging changes, resize constrain
            let changed = false;
            let ddChanged = false;
            for (const key in w) {
                if (key[0] !== '_' && n[key] !== w[key]) {
                    n[key] = w[key];
                    changed = true;
                    ddChanged = ddChanged || (!this.opts.staticGrid && (key === 'noResize' || key === 'noMove' || key === 'locked'));
                }
            }
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.sanitizeMinMax(n);
            // finally move the widget and update attr
            if (m) {
                const widthChanged = (m.w !== undefined && m.w !== n.w);
                this.moveNode(n, m);
                if (widthChanged && n.subGrid) {
                    // if we're animating the client size hasn't changed yet, so force a change (not exact size)
                    n.subGrid.onResize(this.hasAnimationCSS() ? n.w : undefined);
                }
                else {
                    this.resizeToContentCheck(widthChanged, n);
                }
                delete n._orig; // clear out original position now that we moved #2669
            }
            if (m || changed) {
                this._writeAttr(el, n);
            }
            if (ddChanged) {
                this._prepareDragDropByNode(n);
            }
        });
        return this;
    }
    moveNode(n, m) {
        const wasUpdating = n._updating;
        if (!wasUpdating)
            this.engine.cleanNodes().beginUpdate(n);
        this.engine.moveNode(n, m);
        this._updateContainerHeight();
        if (!wasUpdating) {
            this._triggerChangeEvent();
            this.engine.endUpdate();
        }
    }
    /**
     * Updates widget height to match the content height to avoid v-scrollbar or dead space.
     * Note: this assumes only 1 child under resizeToContentParent='.grid-stack-item-content' (sized to gridItem minus padding) that is at the entire content size wanted.
     * @param el grid item element
     * @param useNodeH set to true if GridStackNode.h should be used instead of actual container height when we don't need to wait for animation to finish to get actual DOM heights
     */
    resizeToContent(el) {
        if (!el)
            return;
        el.classList.remove('size-to-content-max');
        if (!el.clientHeight)
            return; // 0 when hidden, skip
        const n = el.gridstackNode;
        if (!n)
            return;
        const grid = n.grid;
        if (!grid || el.parentElement !== grid.el)
            return; // skip if we are not inside a grid
        const cell = grid.getCellHeight(true);
        if (!cell)
            return;
        let height = n.h ? n.h * cell : el.clientHeight; // getBoundingClientRect().height seem to flicker back and forth
        let item;
        if (n.resizeToContentParent)
            item = el.querySelector(n.resizeToContentParent);
        if (!item)
            item = el.querySelector(GridStack.resizeToContentParent);
        if (!item)
            return;
        const padding = el.clientHeight - item.clientHeight; // full - available height to our child (minus border, padding...)
        const itemH = n.h ? n.h * cell - padding : item.clientHeight; // calculated to what cellHeight is or will become (rather than actual to prevent waiting for animation to finish)
        let wantedH;
        if (n.subGrid) {
            // sub-grid - use their actual row count * their cell height, BUT append any content outside of the grid (eg: above text)
            wantedH = n.subGrid.getRow() * n.subGrid.getCellHeight(true);
            const subRec = n.subGrid.el.getBoundingClientRect();
            const parentRec = n.subGrid.el.parentElement.getBoundingClientRect();
            wantedH += subRec.top - parentRec.top;
        }
        else if (n.subGridOpts?.children?.length) {
            // not sub-grid just yet (case above) wait until we do
            return;
        }
        else {
            // NOTE: clientHeight & getBoundingClientRect() is undefined for text and other leaf nodes. use <div> container!
            const child = item.firstElementChild;
            if (!child) {
                console.error(`Error: GridStack.resizeToContent() widget id:${n.id} '${GridStack.resizeToContentParent}'.firstElementChild is null, make sure to have a div like container. Skipping sizing.`);
                return;
            }
            wantedH = child.getBoundingClientRect().height || itemH;
        }
        if (itemH === wantedH)
            return;
        height += wantedH - itemH;
        let h = Math.ceil(height / cell);
        // check for min/max and special sizing
        const softMax = Number.isInteger(n.sizeToContent) ? n.sizeToContent : 0;
        if (softMax && h > softMax) {
            h = softMax;
            el.classList.add('size-to-content-max'); // get v-scroll back
        }
        if (n.minH && h < n.minH)
            h = n.minH;
        else if (n.maxH && h > n.maxH)
            h = n.maxH;
        if (h !== n.h) {
            grid._ignoreLayoutsNodeChange = true;
            grid.moveNode(n, { h });
            delete grid._ignoreLayoutsNodeChange;
        }
    }
    /** call the user resize (so they can do extra work) else our build in version */
    resizeToContentCBCheck(el) {
        if (GridStack.resizeToContentCB)
            GridStack.resizeToContentCB(el);
        else
            this.resizeToContent(el);
    }
    /** rotate (by swapping w & h) the passed in node - called when user press 'r' during dragging
     * @param els  widget or selector of objects to modify
     * @param relative optional pixel coord relative to upper/left corner to rotate around (will keep that cell under cursor)
     */
    rotate(els, relative) {
        GridStack.getElements(els).forEach(el => {
            const n = el.gridstackNode;
            if (!_utils__WEBPACK_IMPORTED_MODULE_1__.Utils.canBeRotated(n))
                return;
            const rot = { w: n.h, h: n.w, minH: n.minW, minW: n.minH, maxH: n.maxW, maxW: n.maxH };
            // if given an offset, adjust x/y by column/row bounds when user presses 'r' during dragging
            if (relative) {
                const pivotX = relative.left > 0 ? Math.floor(relative.left / this.cellWidth()) : 0;
                const pivotY = relative.top > 0 ? Math.floor(relative.top / this.opts.cellHeight) : 0;
                rot.x = n.x + pivotX - (n.h - (pivotY + 1));
                rot.y = (n.y + pivotY) - pivotX;
            }
            Object.keys(rot).forEach(k => { if (rot[k] === undefined)
                delete rot[k]; });
            const _orig = n._orig;
            this.update(el, rot);
            n._orig = _orig; // restore as move() will delete it
        });
        return this;
    }
    /**
     * Updates the margins which will set all 4 sides at once - see `GridStackOptions.margin` for format options (CSS string format of 1,2,4 values or single number).
     * @param value margin value
     */
    margin(value) {
        const isMultiValue = (typeof value === 'string' && value.split(' ').length > 1);
        // check if we can skip re-creating our CSS file... won't check if multi values (too much hassle)
        if (!isMultiValue) {
            const data = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(value);
            if (this.opts.marginUnit === data.unit && this.opts.margin === data.h)
                return;
        }
        // re-use existing margin handling
        this.opts.margin = value;
        this.opts.marginTop = this.opts.marginBottom = this.opts.marginLeft = this.opts.marginRight = undefined;
        this._initMargin();
        this._updateStyles(true); // true = force re-create
        return this;
    }
    /** returns current margin number value (undefined if 4 sides don't match) */
    getMargin() { return this.opts.margin; }
    /**
     * Returns true if the height of the grid will be less than the vertical
     * constraint. Always returns true if grid doesn't have height constraint.
     * @param node contains x,y,w,h,auto-position options
     *
     * @example
     * if (grid.willItFit(newWidget)) {
     *   grid.addWidget(newWidget);
     * } else {
     *   alert('Not enough free space to place the widget');
     * }
     */
    willItFit(node) {
        // support legacy call for now
        if (arguments.length > 1) {
            console.warn('gridstack.ts: `willItFit(x,y,w,h,autoPosition)` is deprecated. Use `willItFit({x, y,...})`. It will be removed soon');
            // eslint-disable-next-line prefer-rest-params
            const a = arguments;
            let i = 0, w = { x: a[i++], y: a[i++], w: a[i++], h: a[i++], autoPosition: a[i++] };
            return this.willItFit(w);
        }
        return this.engine.willItFit(node);
    }
    /** @internal */
    _triggerChangeEvent() {
        if (this.engine.batchMode)
            return this;
        const elements = this.engine.getDirtyNodes(true); // verify they really changed
        if (elements && elements.length) {
            if (!this._ignoreLayoutsNodeChange) {
                this.engine.layoutsNodesChange(elements);
            }
            this._triggerEvent('change', elements);
        }
        this.engine.saveInitial(); // we called, now reset initial values & dirty flags
        return this;
    }
    /** @internal */
    _triggerAddEvent() {
        if (this.engine.batchMode)
            return this;
        if (this.engine.addedNodes?.length) {
            if (!this._ignoreLayoutsNodeChange) {
                this.engine.layoutsNodesChange(this.engine.addedNodes);
            }
            // prevent added nodes from also triggering 'change' event (which is called next)
            this.engine.addedNodes.forEach(n => { delete n._dirty; });
            const addedNodes = [...this.engine.addedNodes];
            this.engine.addedNodes = [];
            this._triggerEvent('added', addedNodes);
        }
        return this;
    }
    /** @internal */
    _triggerRemoveEvent() {
        if (this.engine.batchMode)
            return this;
        if (this.engine.removedNodes?.length) {
            const removedNodes = [...this.engine.removedNodes];
            this.engine.removedNodes = [];
            this._triggerEvent('removed', removedNodes);
        }
        return this;
    }
    /** @internal */
    _triggerEvent(type, data) {
        const event = data ? new CustomEvent(type, { bubbles: false, detail: data }) : new Event(type);
        this.el.dispatchEvent(event);
        return this;
    }
    /** @internal called to delete the current dynamic style sheet used for our layout */
    _removeStylesheet() {
        if (this._styles) {
            const styleLocation = this.opts.styleInHead ? undefined : this.el.parentNode;
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.removeStylesheet(this._styleSheetClass, styleLocation);
            delete this._styles;
        }
        return this;
    }
    /** @internal updated/create the CSS styles for row based layout and initial margin setting */
    _updateStyles(forceUpdate = false, maxH) {
        // call to delete existing one if we change cellHeight / margin
        if (forceUpdate) {
            this._removeStylesheet();
        }
        if (maxH === undefined)
            maxH = this.getRow();
        this._updateContainerHeight();
        // if user is telling us they will handle the CSS themselves by setting heights to 0. Do we need this opts really ??
        if (this.opts.cellHeight === 0) {
            return this;
        }
        const cellHeight = this.opts.cellHeight;
        const cellHeightUnit = this.opts.cellHeightUnit;
        const prefix = `.${this._styleSheetClass} > .${this.opts.itemClass}`;
        // create one as needed
        if (!this._styles) {
            // insert style to parent (instead of 'head' by default) to support WebComponent
            const styleLocation = this.opts.styleInHead ? undefined : this.el.parentNode;
            this._styles = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.createStylesheet(this._styleSheetClass, styleLocation, {
                nonce: this.opts.nonce,
            });
            if (!this._styles)
                return this;
            this._styles._max = 0;
            // these are done once only
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, prefix, `height: ${cellHeight}${cellHeightUnit}`);
            // content margins
            const top = this.opts.marginTop + this.opts.marginUnit;
            const bottom = this.opts.marginBottom + this.opts.marginUnit;
            const right = this.opts.marginRight + this.opts.marginUnit;
            const left = this.opts.marginLeft + this.opts.marginUnit;
            const content = `${prefix} > .grid-stack-item-content`;
            const placeholder = `.${this._styleSheetClass} > .grid-stack-placeholder > .placeholder-content`;
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, content, `top: ${top}; right: ${right}; bottom: ${bottom}; left: ${left};`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, placeholder, `top: ${top}; right: ${right}; bottom: ${bottom}; left: ${left};`);
            // resize handles offset (to match margin)
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-n`, `top: ${top};`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-s`, `bottom: ${bottom}`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-ne`, `right: ${right}`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-e`, `right: ${right}`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-se`, `right: ${right}; bottom: ${bottom}`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-nw`, `left: ${left}`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-w`, `left: ${left}`);
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix} > .ui-resizable-sw`, `left: ${left}; bottom: ${bottom}`);
        }
        // now update the height specific fields
        maxH = maxH || this._styles._max;
        if (maxH > this._styles._max) {
            const getHeight = (rows) => (cellHeight * rows) + cellHeightUnit;
            for (let i = this._styles._max + 1; i <= maxH; i++) { // start at 1
                _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix}[gs-y="${i}"]`, `top: ${getHeight(i)}`);
                _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.addCSSRule(this._styles, `${prefix}[gs-h="${i + 1}"]`, `height: ${getHeight(i + 1)}`); // start at 2
            }
            this._styles._max = maxH;
        }
        return this;
    }
    /** @internal */
    _updateContainerHeight() {
        if (!this.engine || this.engine.batchMode)
            return this;
        const parent = this.parentGridNode;
        let row = this.getRow() + this._extraDragRow; // this checks for minRow already
        const cellHeight = this.opts.cellHeight;
        const unit = this.opts.cellHeightUnit;
        if (!cellHeight)
            return this;
        // check for css min height (non nested grid). TODO: support mismatch, say: min % while unit is px.
        if (!parent) {
            const cssMinHeight = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(getComputedStyle(this.el)['minHeight']);
            if (cssMinHeight.h > 0 && cssMinHeight.unit === unit) {
                const minRow = Math.floor(cssMinHeight.h / cellHeight);
                if (row < minRow) {
                    row = minRow;
                }
            }
        }
        this.el.setAttribute('gs-current-row', String(row));
        this.el.style.removeProperty('min-height');
        this.el.style.removeProperty('height');
        if (row) {
            // nested grids have 'insert:0' to fill the space of parent by default, but we may be taller so use min-height for possible scrollbars
            this.el.style[parent ? 'minHeight' : 'height'] = row * cellHeight + unit;
        }
        // if we're a nested grid inside an sizeToContent item, tell it to resize itself too
        if (parent && !parent.grid.engine.batchMode && _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.shouldSizeToContent(parent)) {
            parent.grid.resizeToContentCBCheck(parent.el);
        }
        return this;
    }
    /** @internal */
    _prepareElement(el, triggerAddEvent = false, node) {
        node = node || this._readAttr(el);
        el.gridstackNode = node;
        node.el = el;
        node.grid = this;
        node = this.engine.addNode(node, triggerAddEvent);
        // write the dom sizes and class
        this._writeAttr(el, node);
        el.classList.add(_types__WEBPACK_IMPORTED_MODULE_2__.gridDefaults.itemClass, this.opts.itemClass);
        const sizeToContent = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.shouldSizeToContent(node);
        sizeToContent ? el.classList.add('size-to-content') : el.classList.remove('size-to-content');
        if (sizeToContent)
            this.resizeToContentCheck(false, node);
        this._prepareDragDropByNode(node);
        return this;
    }
    /** @internal call to write position x,y,w,h attributes back to element */
    _writePosAttr(el, n) {
        if (n.x !== undefined && n.x !== null) {
            el.setAttribute('gs-x', String(n.x));
        }
        if (n.y !== undefined && n.y !== null) {
            el.setAttribute('gs-y', String(n.y));
        }
        n.w > 1 ? el.setAttribute('gs-w', String(n.w)) : el.removeAttribute('gs-w');
        n.h > 1 ? el.setAttribute('gs-h', String(n.h)) : el.removeAttribute('gs-h');
        return this;
    }
    /** @internal call to write any default attributes back to element */
    _writeAttr(el, node) {
        if (!node)
            return this;
        this._writePosAttr(el, node);
        const attrs /*: GridStackWidget but strings */ = {
            // autoPosition: 'gs-auto-position', // no need to write out as already in node and doesn't affect CSS
            noResize: 'gs-no-resize',
            noMove: 'gs-no-move',
            locked: 'gs-locked',
            id: 'gs-id',
        };
        for (const key in attrs) {
            if (node[key]) { // 0 is valid for x,y only but done above already and not in list anyway
                el.setAttribute(attrs[key], String(node[key]));
            }
            else {
                el.removeAttribute(attrs[key]);
            }
        }
        return this;
    }
    /** @internal call to read any default attributes from element */
    _readAttr(el, clearDefaultAttr = true) {
        const n = {};
        n.x = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-x'));
        n.y = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-y'));
        n.w = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-w'));
        n.h = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-h'));
        n.autoPosition = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toBool(el.getAttribute('gs-auto-position'));
        n.noResize = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toBool(el.getAttribute('gs-no-resize'));
        n.noMove = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toBool(el.getAttribute('gs-no-move'));
        n.locked = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toBool(el.getAttribute('gs-locked'));
        n.id = el.getAttribute('gs-id');
        // read but never written out
        n.maxW = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-max-w'));
        n.minW = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-min-w'));
        n.maxH = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-max-h'));
        n.minH = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.toNumber(el.getAttribute('gs-min-h'));
        // v8.x optimization to reduce un-needed attr that don't render or are default CSS
        if (clearDefaultAttr) {
            if (n.w === 1)
                el.removeAttribute('gs-w');
            if (n.h === 1)
                el.removeAttribute('gs-h');
            if (n.maxW)
                el.removeAttribute('gs-max-w');
            if (n.minW)
                el.removeAttribute('gs-min-w');
            if (n.maxH)
                el.removeAttribute('gs-max-h');
            if (n.minH)
                el.removeAttribute('gs-min-h');
        }
        // remove any key not found (null or false which is default)
        for (const key in n) {
            if (!n.hasOwnProperty(key))
                return;
            if (!n[key] && n[key] !== 0) { // 0 can be valid value (x,y only really)
                delete n[key];
            }
        }
        return n;
    }
    /** @internal */
    _setStaticClass() {
        const classes = ['grid-stack-static'];
        if (this.opts.staticGrid) {
            this.el.classList.add(...classes);
            this.el.setAttribute('gs-static', 'true');
        }
        else {
            this.el.classList.remove(...classes);
            this.el.removeAttribute('gs-static');
        }
        return this;
    }
    /**
     * called when we are being resized - check if the one Column Mode needs to be turned on/off
     * and remember the prev columns we used, or get our count from parent, as well as check for cellHeight==='auto' (square)
     * or `sizeToContent` gridItem options.
     */
    onResize(clientWidth = this.el?.clientWidth) {
        if (!clientWidth)
            return; // return if we're gone or no size yet (will get called again)
        if (this.prevWidth === clientWidth)
            return; // no-op
        this.prevWidth = clientWidth;
        // console.log('onResize ', clientWidth);
        this.batchUpdate();
        // see if we're nested and take our column count from our parent....
        let columnChanged = false;
        if (this._autoColumn && this.parentGridNode) {
            if (this.opts.column !== this.parentGridNode.w) {
                this.column(this.parentGridNode.w, this.opts.layout || 'list');
                columnChanged = true;
            }
        }
        else {
            // else check for dynamic column
            columnChanged = this.checkDynamicColumn();
        }
        // make the cells content square again
        if (this._isAutoCellHeight)
            this.cellHeight();
        // update any nested grids, or items size
        this.engine.nodes.forEach(n => {
            if (n.subGrid)
                n.subGrid.onResize();
        });
        if (!this._skipInitialResize)
            this.resizeToContentCheck(columnChanged); // wait for anim of column changed (DOM reflow before we can size correctly)
        delete this._skipInitialResize;
        this.batchUpdate(false);
        return this;
    }
    /** resizes content for given node (or all) if shouldSizeToContent() is true */
    resizeToContentCheck(delay = false, n = undefined) {
        if (!this.engine)
            return; // we've been deleted in between!
        // update any gridItem height with sizeToContent, but wait for DOM $animation_speed to settle if we changed column count
        // TODO: is there a way to know what the final (post animation) size of the content will be so we can animate the column width and height together rather than sequentially ?
        if (delay && this.hasAnimationCSS())
            return setTimeout(() => this.resizeToContentCheck(false, n), this.animationDelay);
        if (n) {
            if (_utils__WEBPACK_IMPORTED_MODULE_1__.Utils.shouldSizeToContent(n))
                this.resizeToContentCBCheck(n.el);
        }
        else if (this.engine.nodes.some(n => _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.shouldSizeToContent(n))) {
            const nodes = [...this.engine.nodes]; // in case order changes while resizing one
            this.batchUpdate();
            nodes.forEach(n => {
                if (_utils__WEBPACK_IMPORTED_MODULE_1__.Utils.shouldSizeToContent(n))
                    this.resizeToContentCBCheck(n.el);
            });
            this.batchUpdate(false);
        }
        // call this regardless of shouldSizeToContent because widget might need to stretch to take available space after a resize
        if (this._gsEventHandler['resizecontent'])
            this._gsEventHandler['resizecontent'](null, n ? [n] : this.engine.nodes);
    }
    /** add or remove the grid element size event handler */
    _updateResizeEvent(forceRemove = false) {
        // only add event if we're not nested (parent will call us) and we're auto sizing cells or supporting dynamic column (i.e. doing work)
        // or supporting new sizeToContent option.
        const trackSize = !this.parentGridNode && (this._isAutoCellHeight || this.opts.sizeToContent || this.opts.columnOpts
            || this.engine.nodes.find(n => n.sizeToContent));
        if (!forceRemove && trackSize && !this.resizeObserver) {
            this._sizeThrottle = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.throttle(() => this.onResize(), this.opts.cellHeightThrottle);
            this.resizeObserver = new ResizeObserver(() => this._sizeThrottle());
            this.resizeObserver.observe(this.el);
            this._skipInitialResize = true; // makeWidget will originally have called on startup
        }
        else if ((forceRemove || !trackSize) && this.resizeObserver) {
            this.resizeObserver.disconnect();
            delete this.resizeObserver;
            delete this._sizeThrottle;
        }
        return this;
    }
    /** @internal convert a potential selector into actual element */
    static getElement(els = '.grid-stack-item') { return _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.getElement(els); }
    /** @internal */
    static getElements(els = '.grid-stack-item') { return _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.getElements(els); }
    /** @internal */
    static getGridElement(els) { return GridStack.getElement(els); }
    /** @internal */
    static getGridElements(els) { return _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.getElements(els); }
    /** @internal initialize margin top/bottom/left/right and units */
    _initMargin() {
        let data;
        let margin = 0;
        // support passing multiple values like CSS (ex: '5px 10px 0 20px')
        let margins = [];
        if (typeof this.opts.margin === 'string') {
            margins = this.opts.margin.split(' ');
        }
        if (margins.length === 2) { // top/bot, left/right like CSS
            this.opts.marginTop = this.opts.marginBottom = margins[0];
            this.opts.marginLeft = this.opts.marginRight = margins[1];
        }
        else if (margins.length === 4) { // Clockwise like CSS
            this.opts.marginTop = margins[0];
            this.opts.marginRight = margins[1];
            this.opts.marginBottom = margins[2];
            this.opts.marginLeft = margins[3];
        }
        else {
            data = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(this.opts.margin);
            this.opts.marginUnit = data.unit;
            margin = this.opts.margin = data.h;
        }
        // see if top/bottom/left/right need to be set as well
        if (this.opts.marginTop === undefined) {
            this.opts.marginTop = margin;
        }
        else {
            data = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(this.opts.marginTop);
            this.opts.marginTop = data.h;
            delete this.opts.margin;
        }
        if (this.opts.marginBottom === undefined) {
            this.opts.marginBottom = margin;
        }
        else {
            data = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(this.opts.marginBottom);
            this.opts.marginBottom = data.h;
            delete this.opts.margin;
        }
        if (this.opts.marginRight === undefined) {
            this.opts.marginRight = margin;
        }
        else {
            data = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(this.opts.marginRight);
            this.opts.marginRight = data.h;
            delete this.opts.margin;
        }
        if (this.opts.marginLeft === undefined) {
            this.opts.marginLeft = margin;
        }
        else {
            data = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.parseHeight(this.opts.marginLeft);
            this.opts.marginLeft = data.h;
            delete this.opts.margin;
        }
        this.opts.marginUnit = data.unit; // in case side were spelled out, use those units instead...
        if (this.opts.marginTop === this.opts.marginBottom && this.opts.marginLeft === this.opts.marginRight && this.opts.marginTop === this.opts.marginRight) {
            this.opts.margin = this.opts.marginTop; // makes it easier to check for no-ops in setMargin()
        }
        return this;
    }
    /* ===========================================================================================
     * drag&drop methods that used to be stubbed out and implemented in dd-gridstack.ts
     * but caused loading issues in prod - see https://github.com/gridstack/gridstack.js/issues/2039
     * ===========================================================================================
     */
    /** get the global (but static to this code) DD implementation */
    static getDD() {
        return dd;
    }
    /**
     * call to setup dragging in from the outside (say toolbar), by specifying the class selection and options.
     * Called during GridStack.init() as options, but can also be called directly (last param are used) in case the toolbar
     * is dynamically create and needs to be set later.
     * @param dragIn string selector (ex: '.sidebar-item') or list of dom elements
     * @param dragInOptions options - see DDDragOpt. (default: {handle: '.grid-stack-item-content', appendTo: 'body'}
     * @param widgets GridStackWidget def to assign to each element which defines what to create on drop
     * @param root optional root which defaults to document (for shadow dom pass the parent HTMLDocument)
     */
    static setupDragIn(dragIn, dragInOptions, widgets, root = document) {
        if (dragInOptions?.pause !== undefined) {
            _dd_manager__WEBPACK_IMPORTED_MODULE_5__.DDManager.pauseDrag = dragInOptions.pause;
        }
        dragInOptions = { appendTo: 'body', helper: 'clone', ...(dragInOptions || {}) }; // default to handle:undefined = drag by the whole item
        const els = (typeof dragIn === 'string') ? _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.getElements(dragIn, root) : dragIn;
        els.forEach((el, i) => {
            if (!dd.isDraggable(el))
                dd.dragIn(el, dragInOptions);
            if (widgets?.[i])
                el.gridstackNode = widgets[i];
        });
    }
    /**
     * Enables/Disables dragging by the user of specific grid element. If you want all items, and have it affect future items, use enableMove() instead. No-op for static grids.
     * IF you are looking to prevent an item from moving (due to being pushed around by another during collision) use locked property instead.
     * @param els widget or selector to modify.
     * @param val if true widget will be draggable, assuming the parent grid isn't noMove or static.
     */
    movable(els, val) {
        if (this.opts.staticGrid)
            return this; // can't move a static grid!
        GridStack.getElements(els).forEach(el => {
            const n = el.gridstackNode;
            if (!n)
                return;
            val ? delete n.noMove : n.noMove = true;
            this._prepareDragDropByNode(n); // init DD if need be, and adjust
        });
        return this;
    }
    /**
     * Enables/Disables user resizing of specific grid element. If you want all items, and have it affect future items, use enableResize() instead. No-op for static grids.
     * @param els  widget or selector to modify
     * @param val  if true widget will be resizable, assuming the parent grid isn't noResize or static.
     */
    resizable(els, val) {
        if (this.opts.staticGrid)
            return this; // can't resize a static grid!
        GridStack.getElements(els).forEach(el => {
            const n = el.gridstackNode;
            if (!n)
                return;
            val ? delete n.noResize : n.noResize = true;
            this._prepareDragDropByNode(n); // init DD if need be, and adjust
        });
        return this;
    }
    /**
     * Temporarily disables widgets moving/resizing.
     * If you want a more permanent way (which freezes up resources) use `setStatic(true)` instead.
     * Note: no-op for static grid
     * This is a shortcut for:
     * @example
     *  grid.enableMove(false);
     *  grid.enableResize(false);
     * @param recurse true (default) if sub-grids also get updated
     */
    disable(recurse = true) {
        if (this.opts.staticGrid)
            return;
        this.enableMove(false, recurse);
        this.enableResize(false, recurse);
        this._triggerEvent('disable');
        return this;
    }
    /**
     * Re-enables widgets moving/resizing - see disable().
     * Note: no-op for static grid.
     * This is a shortcut for:
     * @example
     *  grid.enableMove(true);
     *  grid.enableResize(true);
     * @param recurse true (default) if sub-grids also get updated
     */
    enable(recurse = true) {
        if (this.opts.staticGrid)
            return;
        this.enableMove(true, recurse);
        this.enableResize(true, recurse);
        this._triggerEvent('enable');
        return this;
    }
    /**
     * Enables/disables widget moving. No-op for static grids, and locally defined items still overrule
     * @param recurse true (default) if sub-grids also get updated
     */
    enableMove(doEnable, recurse = true) {
        if (this.opts.staticGrid)
            return this; // can't move a static grid!
        doEnable ? delete this.opts.disableDrag : this.opts.disableDrag = true; // FIRST before we update children as grid overrides #1658
        this.engine.nodes.forEach(n => {
            this._prepareDragDropByNode(n);
            if (n.subGrid && recurse)
                n.subGrid.enableMove(doEnable, recurse);
        });
        return this;
    }
    /**
     * Enables/disables widget resizing. No-op for static grids.
     * @param recurse true (default) if sub-grids also get updated
     */
    enableResize(doEnable, recurse = true) {
        if (this.opts.staticGrid)
            return this; // can't size a static grid!
        doEnable ? delete this.opts.disableResize : this.opts.disableResize = true; // FIRST before we update children as grid overrides #1658
        this.engine.nodes.forEach(n => {
            this._prepareDragDropByNode(n);
            if (n.subGrid && recurse)
                n.subGrid.enableResize(doEnable, recurse);
        });
        return this;
    }
    /** @internal removes any drag&drop present (called during destroy) */
    _removeDD(el) {
        dd.draggable(el, 'destroy').resizable(el, 'destroy');
        if (el.gridstackNode) {
            delete el.gridstackNode._initDD; // reset our DD init flag
        }
        delete el.ddElement;
        return this;
    }
    /** @internal called to add drag over to support widgets being added externally */
    _setupAcceptWidget() {
        // check if we need to disable things
        if (this.opts.staticGrid || (!this.opts.acceptWidgets && !this.opts.removable)) {
            dd.droppable(this.el, 'destroy');
            return this;
        }
        // vars shared across all methods
        let cellHeight, cellWidth;
        const onDrag = (event, el, helper) => {
            helper = helper || el;
            const node = helper.gridstackNode;
            if (!node)
                return;
            // if the element is being dragged from outside, scale it down to match the grid's scale
            // and slightly adjust its position relative to the mouse
            if (!node.grid?.el) {
                // this scales the helper down
                helper.style.transform = `scale(${1 / this.dragTransform.xScale},${1 / this.dragTransform.yScale})`;
                // this makes it so that the helper is well positioned relative to the mouse after scaling
                const helperRect = helper.getBoundingClientRect();
                helper.style.left = helperRect.x + (this.dragTransform.xScale - 1) * (event.clientX - helperRect.x) / this.dragTransform.xScale + 'px';
                helper.style.top = helperRect.y + (this.dragTransform.yScale - 1) * (event.clientY - helperRect.y) / this.dragTransform.yScale + 'px';
                helper.style.transformOrigin = `0px 0px`;
            }
            let { top, left } = helper.getBoundingClientRect();
            const rect = this.el.getBoundingClientRect();
            left -= rect.left;
            top -= rect.top;
            const ui = {
                position: {
                    top: top * this.dragTransform.xScale,
                    left: left * this.dragTransform.yScale
                }
            };
            if (node._temporaryRemoved) {
                node.x = Math.max(0, Math.round(left / cellWidth));
                node.y = Math.max(0, Math.round(top / cellHeight));
                delete node.autoPosition;
                this.engine.nodeBoundFix(node);
                // don't accept *initial* location if doesn't fit #1419 (locked drop region, or can't grow), but maybe try if it will go somewhere
                if (!this.engine.willItFit(node)) {
                    node.autoPosition = true; // ignore x,y and try for any slot...
                    if (!this.engine.willItFit(node)) {
                        dd.off(el, 'drag'); // stop calling us
                        return; // full grid or can't grow
                    }
                    if (node._willFitPos) {
                        // use the auto position instead #1687
                        _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.copyPos(node, node._willFitPos);
                        delete node._willFitPos;
                    }
                }
                // re-use the existing node dragging method
                this._onStartMoving(helper, event, ui, node, cellWidth, cellHeight);
            }
            else {
                // re-use the existing node dragging that does so much of the collision detection
                this._dragOrResize(helper, event, ui, node, cellWidth, cellHeight);
            }
        };
        dd.droppable(this.el, {
            accept: (el) => {
                const node = el.gridstackNode || this._readAttr(el, false);
                // set accept drop to true on ourself (which we ignore) so we don't get "can't drop" icon in HTML5 mode while moving
                if (node?.grid === this)
                    return true;
                if (!this.opts.acceptWidgets)
                    return false;
                // check for accept method or class matching
                let canAccept = true;
                if (typeof this.opts.acceptWidgets === 'function') {
                    canAccept = this.opts.acceptWidgets(el);
                }
                else {
                    const selector = (this.opts.acceptWidgets === true ? '.grid-stack-item' : this.opts.acceptWidgets);
                    canAccept = el.matches(selector);
                }
                // finally check to make sure we actually have space left #1571 #2633
                if (canAccept && node && this.opts.maxRow) {
                    const n = { w: node.w, h: node.h, minW: node.minW, minH: node.minH }; // only width/height matters and autoPosition
                    canAccept = this.engine.willItFit(n);
                }
                return canAccept;
            }
        })
            /**
             * entering our grid area
             */
            .on(this.el, 'dropover', (event, el, helper) => {
            // console.log(`over ${this.el.gridstack.opts.id} ${count++}`); // TEST
            let node = helper?.gridstackNode || el.gridstackNode;
            // ignore drop enter on ourself (unless we temporarily removed) which happens on a simple drag of our item
            if (node?.grid === this && !node._temporaryRemoved) {
                // delete node._added; // reset this to track placeholder again in case we were over other grid #1484 (dropout doesn't always clear)
                return false; // prevent parent from receiving msg (which may be a grid as well)
            }
            // fix #1578 when dragging fast, we may not get a leave on the previous grid so force one now
            if (node?.grid && node.grid !== this && !node._temporaryRemoved) {
                // console.log('dropover without leave'); // TEST
                const otherGrid = node.grid;
                otherGrid._leave(el, helper);
            }
            helper = helper || el;
            // cache cell dimensions (which don't change), position can animate if we removed an item in otherGrid that affects us...
            cellWidth = this.cellWidth();
            cellHeight = this.getCellHeight(true);
            // sidebar items: load any element attributes if we don't have a node
            if (!node) {
                if (helper.hasAttribute('gridstacknode')) {
                    try {
                        node = JSON.parse(helper.getAttribute('gridstacknode'));
                    }
                    catch (error) {
                        console.error("Gridstack dropover: Bad JSON format: ", helper.getAttribute('gridstacknode'));
                    }
                    helper.removeAttribute('gridstacknode');
                }
                if (!node)
                    node = this._readAttr(helper); // used to pass false for #2354, but now we clone top level node
            }
            if (!node.grid) { // sidebar item
                if (!node.el)
                    node = { ...node }; // clone first time we're coming from sidebar (since 'clone' doesn't copy vars)
                node._isExternal = true;
                helper.gridstackNode = node;
            }
            // calculate the grid size based on element outer size
            const w = node.w || Math.round(helper.offsetWidth / cellWidth) || 1;
            const h = node.h || Math.round(helper.offsetHeight / cellHeight) || 1;
            // if the item came from another grid, make a copy and save the original info in case we go back there
            if (node.grid && node.grid !== this) {
                // copy the node original values (min/max/id/etc...) but override width/height/other flags which are this grid specific
                // console.log('dropover cloning node'); // TEST
                if (!el._gridstackNodeOrig)
                    el._gridstackNodeOrig = node; // shouldn't have multiple nested!
                el.gridstackNode = node = { ...node, w, h, grid: this };
                delete node.x;
                delete node.y;
                this.engine.cleanupNode(node)
                    .nodeBoundFix(node);
                // restore some internal fields we need after clearing them all
                node._initDD =
                    node._isExternal = // DOM needs to be re-parented on a drop
                        node._temporaryRemoved = true; // so it can be inserted onDrag below
            }
            else {
                node.w = w;
                node.h = h;
                node._temporaryRemoved = true; // so we can insert it
            }
            // clear any marked for complete removal (Note: don't check _isAboutToRemove as that is cleared above - just do it)
            GridStack._itemRemoving(node.el, false);
            dd.on(el, 'drag', onDrag);
            // make sure this is called at least once when going fast #1578
            onDrag(event, el, helper);
            return false; // prevent parent from receiving msg (which may be a grid as well)
        })
            /**
             * Leaving our grid area...
             */
            .on(this.el, 'dropout', (event, el, helper) => {
            // console.log(`out ${this.el.gridstack.opts.id} ${count++}`); // TEST
            const node = helper?.gridstackNode || el.gridstackNode;
            if (!node)
                return false;
            // fix #1578 when dragging fast, we might get leave after other grid gets enter (which calls us to clean)
            // so skip this one if we're not the active grid really..
            if (!node.grid || node.grid === this) {
                this._leave(el, helper);
                // if we were created as temporary nested grid, go back to before state
                if (this._isTemp) {
                    this.removeAsSubGrid(node);
                }
            }
            return false; // prevent parent from receiving msg (which may be grid as well)
        })
            /**
             * end - releasing the mouse
             */
            .on(this.el, 'drop', (event, el, helper) => {
            const node = helper?.gridstackNode || el.gridstackNode;
            // ignore drop on ourself from ourself that didn't come from the outside - dragend will handle the simple move instead
            if (node?.grid === this && !node._isExternal)
                return false;
            const wasAdded = !!this.placeholder.parentElement; // skip items not actually added to us because of constrains, but do cleanup #1419
            const wasSidebar = el !== helper;
            this.placeholder.remove();
            // disable animation when replacing a placeholder (already positioned) with actual content
            const noAnim = wasAdded && this.opts.animate;
            if (noAnim)
                this.setAnimation(false);
            // notify previous grid of removal
            // console.log('drop delete _gridstackNodeOrig') // TEST
            const origNode = el._gridstackNodeOrig;
            delete el._gridstackNodeOrig;
            if (wasAdded && origNode?.grid && origNode.grid !== this) {
                const oGrid = origNode.grid;
                oGrid.engine.removeNodeFromLayoutCache(origNode);
                oGrid.engine.removedNodes.push(origNode);
                oGrid._triggerRemoveEvent()._triggerChangeEvent();
                // if it's an empty sub-grid that got auto-created, nuke it
                if (oGrid.parentGridNode && !oGrid.engine.nodes.length && oGrid.opts.subGridDynamic) {
                    oGrid.removeAsSubGrid();
                }
            }
            if (!node)
                return false;
            // use existing placeholder node as it's already in our list with drop location
            if (wasAdded) {
                this.engine.cleanupNode(node); // removes all internal _xyz values
                node.grid = this;
            }
            delete node.grid?._isTemp;
            dd.off(el, 'drag');
            // if we made a copy insert that instead of the original (sidebar item)
            if (helper !== el) {
                helper.remove();
                el = helper;
            }
            else {
                el.remove(); // reduce flicker as we change depth here, and size further down
            }
            this._removeDD(el);
            if (!wasAdded)
                return false;
            const subGrid = node.subGrid?.el?.gridstack; // set when actual sub-grid present
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.copyPos(node, this._readAttr(this.placeholder)); // placeholder values as moving VERY fast can throw things off #1578
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.removePositioningStyles(el);
            // give the user a chance to alter the widget that will get inserted if new sidebar item
            if (wasSidebar && (node.content || node.subGridOpts || GridStack.addRemoveCB)) {
                delete node.el;
                el = this.addWidget(node);
            }
            else {
                this._prepareElement(el, true, node);
                this.el.appendChild(el);
                // resizeToContent is skipped in _prepareElement() until node is visible (clientHeight=0) so call it now
                this.resizeToContentCheck(false, node);
                if (subGrid) {
                    subGrid.parentGridNode = node;
                    if (!subGrid.opts.styleInHead)
                        subGrid._updateStyles(true); // re-create sub-grid styles now that we've moved
                }
                this._updateContainerHeight();
            }
            this.engine.addedNodes.push(node);
            this._triggerAddEvent();
            this._triggerChangeEvent();
            this.engine.endUpdate();
            if (this._gsEventHandler['dropped']) {
                this._gsEventHandler['dropped']({ ...event, type: 'dropped' }, origNode && origNode.grid ? origNode : undefined, node);
            }
            // delay adding animation back
            if (noAnim)
                this.setAnimation(this.opts.animate, true);
            return false; // prevent parent from receiving msg (which may be grid as well)
        });
        return this;
    }
    /** @internal mark item for removal */
    static _itemRemoving(el, remove) {
        if (!el)
            return;
        const node = el ? el.gridstackNode : undefined;
        if (!node?.grid || el.classList.contains(node.grid.opts.removableOptions.decline))
            return;
        remove ? node._isAboutToRemove = true : delete node._isAboutToRemove;
        remove ? el.classList.add('grid-stack-item-removing') : el.classList.remove('grid-stack-item-removing');
    }
    /** @internal called to setup a trash drop zone if the user specifies it */
    _setupRemoveDrop() {
        if (typeof this.opts.removable !== 'string')
            return this;
        const trashEl = document.querySelector(this.opts.removable);
        if (!trashEl)
            return this;
        // only register ONE static drop-over/dropout callback for the 'trash', and it will
        // update the passed in item and parent grid because the '.trash' is a shared resource anyway,
        // and Native DD only has 1 event CB (having a list and technically a per grid removableOptions complicates things greatly)
        if (!this.opts.staticGrid && !dd.isDroppable(trashEl)) {
            dd.droppable(trashEl, this.opts.removableOptions)
                .on(trashEl, 'dropover', (event, el) => GridStack._itemRemoving(el, true))
                .on(trashEl, 'dropout', (event, el) => GridStack._itemRemoving(el, false));
        }
        return this;
    }
    /** @internal prepares the element for drag&drop */
    _prepareDragDropByNode(node) {
        const el = node.el;
        const noMove = node.noMove || this.opts.disableDrag;
        const noResize = node.noResize || this.opts.disableResize;
        // check for disabled grid first
        if (this.opts.staticGrid || (noMove && noResize)) {
            if (node._initDD) {
                this._removeDD(el); // nukes everything instead of just disable, will add some styles back next
                delete node._initDD;
            }
            el.classList.add('ui-draggable-disabled', 'ui-resizable-disabled'); // add styles one might depend on #1435
            return this;
        }
        if (!node._initDD) {
            // variables used/cashed between the 3 start/move/end methods, in addition to node passed above
            let cellWidth;
            let cellHeight;
            /** called when item starts moving/resizing */
            const onStartMoving = (event, ui) => {
                // trigger any 'dragstart' / 'resizestart' manually
                if (this._gsEventHandler[event.type]) {
                    this._gsEventHandler[event.type](event, event.target);
                }
                cellWidth = this.cellWidth();
                cellHeight = this.getCellHeight(true); // force pixels for calculations
                this._onStartMoving(el, event, ui, node, cellWidth, cellHeight);
            };
            /** called when item is being dragged/resized */
            const dragOrResize = (event, ui) => {
                this._dragOrResize(el, event, ui, node, cellWidth, cellHeight);
            };
            /** called when the item stops moving/resizing */
            const onEndMoving = (event) => {
                this.placeholder.remove();
                delete node._moving;
                delete node._event;
                delete node._lastTried;
                const widthChanged = node.w !== node._orig.w;
                // if the item has moved to another grid, we're done here
                const target = event.target;
                if (!target.gridstackNode || target.gridstackNode.grid !== this)
                    return;
                node.el = target;
                if (node._isAboutToRemove) {
                    const grid = el.gridstackNode.grid;
                    if (grid._gsEventHandler[event.type]) {
                        grid._gsEventHandler[event.type](event, target);
                    }
                    grid.engine.nodes.push(node); // temp add it back so we can proper remove it next
                    grid.removeWidget(el, true, true);
                }
                else {
                    _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.removePositioningStyles(target);
                    if (node._temporaryRemoved) {
                        // got removed - restore item back to before dragging position
                        _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.copyPos(node, node._orig); // @ts-ignore
                        this._writePosAttr(target, node);
                        this.engine.addNode(node);
                    }
                    else {
                        // move to new placeholder location
                        this._writePosAttr(target, node);
                    }
                    if (this._gsEventHandler[event.type]) {
                        this._gsEventHandler[event.type](event, target);
                    }
                }
                // @ts-ignore
                this._extraDragRow = 0; // @ts-ignore
                this._updateContainerHeight(); // @ts-ignore
                this._triggerChangeEvent();
                this.engine.endUpdate();
                if (event.type === 'resizestop') {
                    if (Number.isInteger(node.sizeToContent))
                        node.sizeToContent = node.h; // new soft limit
                    this.resizeToContentCheck(widthChanged, node); // wait for width animation if changed
                }
            };
            dd.draggable(el, {
                start: onStartMoving,
                stop: onEndMoving,
                drag: dragOrResize
            }).resizable(el, {
                start: onStartMoving,
                stop: onEndMoving,
                resize: dragOrResize
            });
            node._initDD = true; // we've set DD support now
        }
        // finally fine tune move vs resize by disabling any part...
        dd.draggable(el, noMove ? 'disable' : 'enable')
            .resizable(el, noResize ? 'disable' : 'enable');
        return this;
    }
    /** @internal handles actual drag/resize start */
    _onStartMoving(el, event, ui, node, cellWidth, cellHeight) {
        this.engine.cleanNodes()
            .beginUpdate(node);
        // @ts-ignore
        this._writePosAttr(this.placeholder, node);
        this.el.appendChild(this.placeholder);
        this.placeholder.gridstackNode = node;
        // console.log('_onStartMoving placeholder') // TEST
        // if the element is inside a grid, it has already been scaled
        // we can use that as a scale reference
        if (node.grid?.el) {
            this.dragTransform = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.getValuesFromTransformedElement(el);
        }
        // if the element is being dragged from outside (not from any grid)
        // we use the grid as the transformation reference, since the helper is not subject to transformation
        else if (this.placeholder && this.placeholder.closest('.grid-stack')) {
            const gridEl = this.placeholder.closest('.grid-stack');
            this.dragTransform = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.getValuesFromTransformedElement(gridEl);
        }
        // Fallback
        else {
            this.dragTransform = {
                xScale: 1,
                xOffset: 0,
                yScale: 1,
                yOffset: 0,
            };
        }
        node.el = this.placeholder;
        node._lastUiPosition = ui.position;
        node._prevYPix = ui.position.top;
        node._moving = (event.type === 'dragstart'); // 'dropover' are not initially moving so they can go exactly where they enter (will push stuff out of the way)
        delete node._lastTried;
        if (event.type === 'dropover' && node._temporaryRemoved) {
            // console.log('engine.addNode x=' + node.x); // TEST
            this.engine.addNode(node); // will add, fix collisions, update attr and clear _temporaryRemoved
            node._moving = true; // AFTER, mark as moving object (wanted fix location before)
        }
        // set the min/max resize info taking into account the column count and position (so we don't resize outside the grid)
        this.engine.cacheRects(cellWidth, cellHeight, this.opts.marginTop, this.opts.marginRight, this.opts.marginBottom, this.opts.marginLeft);
        if (event.type === 'resizestart') {
            const colLeft = this.getColumn() - node.x;
            const rowLeft = (this.opts.maxRow || Number.MAX_SAFE_INTEGER) - node.y;
            dd.resizable(el, 'option', 'minWidth', cellWidth * Math.min(node.minW || 1, colLeft))
                .resizable(el, 'option', 'minHeight', cellHeight * Math.min(node.minH || 1, rowLeft))
                .resizable(el, 'option', 'maxWidth', cellWidth * Math.min(node.maxW || Number.MAX_SAFE_INTEGER, colLeft))
                .resizable(el, 'option', 'maxWidthMoveLeft', cellWidth * Math.min(node.maxW || Number.MAX_SAFE_INTEGER, node.x + node.w))
                .resizable(el, 'option', 'maxHeight', cellHeight * Math.min(node.maxH || Number.MAX_SAFE_INTEGER, rowLeft))
                .resizable(el, 'option', 'maxHeightMoveUp', cellHeight * Math.min(node.maxH || Number.MAX_SAFE_INTEGER, node.y + node.h));
        }
    }
    /** @internal handles actual drag/resize */
    _dragOrResize(el, event, ui, node, cellWidth, cellHeight) {
        const p = { ...node._orig }; // could be undefined (_isExternal) which is ok (drag only set x,y and w,h will default to node value)
        let resizing;
        let mLeft = this.opts.marginLeft, mRight = this.opts.marginRight, mTop = this.opts.marginTop, mBottom = this.opts.marginBottom;
        // if margins (which are used to pass mid point by) are large relative to cell height/width, reduce them down #1855
        const mHeight = Math.round(cellHeight * 0.1), mWidth = Math.round(cellWidth * 0.1);
        mLeft = Math.min(mLeft, mWidth);
        mRight = Math.min(mRight, mWidth);
        mTop = Math.min(mTop, mHeight);
        mBottom = Math.min(mBottom, mHeight);
        if (event.type === 'drag') {
            if (node._temporaryRemoved)
                return; // handled by dropover
            const distance = ui.position.top - node._prevYPix;
            node._prevYPix = ui.position.top;
            if (this.opts.draggable.scroll !== false) {
                _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.updateScrollPosition(el, ui.position, distance);
            }
            // get new position taking into account the margin in the direction we are moving! (need to pass mid point by margin)
            const left = ui.position.left + (ui.position.left > node._lastUiPosition.left ? -mRight : mLeft);
            const top = ui.position.top + (ui.position.top > node._lastUiPosition.top ? -mBottom : mTop);
            p.x = Math.round(left / cellWidth);
            p.y = Math.round(top / cellHeight);
            // @ts-ignore// if we're at the bottom hitting something else, grow the grid so cursor doesn't leave when trying to place below others
            const prev = this._extraDragRow;
            if (this.engine.collide(node, p)) {
                const row = this.getRow();
                let extra = Math.max(0, (p.y + node.h) - row);
                if (this.opts.maxRow && row + extra > this.opts.maxRow) {
                    extra = Math.max(0, this.opts.maxRow - row);
                } // @ts-ignore
                this._extraDragRow = extra; // @ts-ignore
            }
            else
                this._extraDragRow = 0; // @ts-ignore
            if (this._extraDragRow !== prev)
                this._updateContainerHeight();
            if (node.x === p.x && node.y === p.y)
                return; // skip same
            // DON'T skip one we tried as we might have failed because of coverage <50% before
            // if (node._lastTried && node._lastTried.x === x && node._lastTried.y === y) return;
        }
        else if (event.type === 'resize') {
            if (p.x < 0)
                return;
            // Scrolling page if needed
            _utils__WEBPACK_IMPORTED_MODULE_1__.Utils.updateScrollResize(event, el, cellHeight);
            // get new size
            p.w = Math.round((ui.size.width - mLeft) / cellWidth);
            p.h = Math.round((ui.size.height - mTop) / cellHeight);
            if (node.w === p.w && node.h === p.h)
                return;
            if (node._lastTried && node._lastTried.w === p.w && node._lastTried.h === p.h)
                return; // skip one we tried (but failed)
            // if we size on left/top side this might move us, so get possible new position as well
            const left = ui.position.left + mLeft;
            const top = ui.position.top + mTop;
            p.x = Math.round(left / cellWidth);
            p.y = Math.round(top / cellHeight);
            resizing = true;
        }
        node._event = event;
        node._lastTried = p; // set as last tried (will nuke if we go there)
        const rect = {
            x: ui.position.left + mLeft,
            y: ui.position.top + mTop,
            w: (ui.size ? ui.size.width : node.w * cellWidth) - mLeft - mRight,
            h: (ui.size ? ui.size.height : node.h * cellHeight) - mTop - mBottom
        };
        if (this.engine.moveNodeCheck(node, { ...p, cellWidth, cellHeight, rect, resizing })) {
            node._lastUiPosition = ui.position;
            this.engine.cacheRects(cellWidth, cellHeight, mTop, mRight, mBottom, mLeft);
            delete node._skipDown;
            if (resizing && node.subGrid)
                node.subGrid.onResize();
            this._extraDragRow = 0; // @ts-ignore
            this._updateContainerHeight();
            const target = event.target; // @ts-ignore
            this._writePosAttr(target, node);
            if (this._gsEventHandler[event.type]) {
                this._gsEventHandler[event.type](event, target);
            }
        }
    }
    /** @internal called when item leaving our area by either cursor dropout event
     * or shape is outside our boundaries. remove it from us, and mark temporary if this was
     * our item to start with else restore prev node values from prev grid it came from.
     */
    _leave(el, helper) {
        helper = helper || el;
        const node = helper.gridstackNode;
        if (!node)
            return;
        // remove the scale of the helper on leave
        helper.style.transform = helper.style.transformOrigin = null;
        dd.off(el, 'drag'); // no need to track while being outside
        // this gets called when cursor leaves and shape is outside, so only do this once
        if (node._temporaryRemoved)
            return;
        node._temporaryRemoved = true;
        this.engine.removeNode(node); // remove placeholder as well, otherwise it's a sign node is not in our list, which is a bigger issue
        node.el = node._isExternal && helper ? helper : el; // point back to real item being dragged
        if (node._isExternal)
            this.engine.cleanupNode(node);
        if (this.opts.removable === true) { // boolean vs a class string
            // item leaving us and we are supposed to remove on leave (no need to drag onto trash) mark it so
            GridStack._itemRemoving(el, true);
        }
        // finally if item originally came from another grid, but left us, restore things back to prev info
        if (el._gridstackNodeOrig) {
            // console.log('leave delete _gridstackNodeOrig') // TEST
            el.gridstackNode = el._gridstackNodeOrig;
            delete el._gridstackNodeOrig;
        }
        else if (node._isExternal) {
            // item came from outside restore all nodes back to original
            this.engine.restoreInitial();
        }
    }
    // legacy method removed
    commit() { (0,_utils__WEBPACK_IMPORTED_MODULE_1__.obsolete)(this, this.batchUpdate(false), 'commit', 'batchUpdate', '5.2'); return this; }
}
/**
 * callback to create the content of widgets so the app can control how to store and restore it
 * By default this lib will do 'el.textContent = w.content' forcing text only support for avoiding potential XSS issues.
 */
GridStack.renderCB = (el, w) => { if (el && w?.content)
    el.textContent = w.content; };
/** parent class for sizing content. defaults to '.grid-stack-item-content' */
GridStack.resizeToContentParent = '.grid-stack-item-content';
/** scoping so users can call GridStack.Utils.sort() for example */
GridStack.Utils = _utils__WEBPACK_IMPORTED_MODULE_1__.Utils;
/** scoping so users can call new GridStack.Engine(12) for example */
GridStack.Engine = _gridstack_engine__WEBPACK_IMPORTED_MODULE_0__.GridStackEngine;
GridStack.GDRev = '11.1.1';

//# sourceMappingURL=gridstack.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/types.js":
/*!**********************************************!*\
  !*** ./node_modules/gridstack/dist/types.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   gridDefaults: () => (/* binding */ gridDefaults)
/* harmony export */ });
/**
 * types.ts 11.1.1
 * Copyright (c) 2021-2024 Alain Dumesny - see GridStack root license
 */
// default values for grid options - used during init and when saving out
const gridDefaults = {
    alwaysShowResizeHandle: 'mobile',
    animate: true,
    auto: true,
    cellHeight: 'auto',
    cellHeightThrottle: 100,
    cellHeightUnit: 'px',
    column: 12,
    draggable: { handle: '.grid-stack-item-content', appendTo: 'body', scroll: true },
    handle: '.grid-stack-item-content',
    itemClass: 'grid-stack-item',
    margin: 10,
    marginUnit: 'px',
    maxRow: 0,
    minRow: 0,
    placeholderClass: 'grid-stack-placeholder',
    placeholderText: '',
    removableOptions: { accept: 'grid-stack-item', decline: 'grid-stack-non-removable' },
    resizable: { handles: 'se' },
    rtl: 'auto',
    // **** same as not being set ****
    // disableDrag: false,
    // disableResize: false,
    // float: false,
    // handleClass: null,
    // removable: false,
    // staticGrid: false,
    // styleInHead: false,
    //removable
};
//# sourceMappingURL=types.js.map

/***/ }),

/***/ "./node_modules/gridstack/dist/utils.js":
/*!**********************************************!*\
  !*** ./node_modules/gridstack/dist/utils.js ***!
  \**********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   Utils: () => (/* binding */ Utils),
/* harmony export */   obsolete: () => (/* binding */ obsolete),
/* harmony export */   obsoleteAttr: () => (/* binding */ obsoleteAttr),
/* harmony export */   obsoleteOpts: () => (/* binding */ obsoleteOpts),
/* harmony export */   obsoleteOptsDel: () => (/* binding */ obsoleteOptsDel)
/* harmony export */ });
/* harmony import */ var _gridstack__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./gridstack */ "./node_modules/gridstack/dist/gridstack.js");
/**
 * utils.ts 11.1.1
 * Copyright (c) 2021-2024 Alain Dumesny - see GridStack root license
 */

/** checks for obsolete method names */
// eslint-disable-next-line
function obsolete(self, f, oldName, newName, rev) {
    const wrapper = (...args) => {
        console.warn('gridstack.js: Function `' + oldName + '` is deprecated in ' + rev + ' and has been replaced ' +
            'with `' + newName + '`. It will be **removed** in a future release');
        return f.apply(self, args);
    };
    wrapper.prototype = f.prototype;
    return wrapper;
}
/** checks for obsolete grid options (can be used for any fields, but msg is about options) */
function obsoleteOpts(opts, oldName, newName, rev) {
    if (opts[oldName] !== undefined) {
        opts[newName] = opts[oldName];
        console.warn('gridstack.js: Option `' + oldName + '` is deprecated in ' + rev + ' and has been replaced with `' +
            newName + '`. It will be **removed** in a future release');
    }
}
/** checks for obsolete grid options which are gone */
function obsoleteOptsDel(opts, oldName, rev, info) {
    if (opts[oldName] !== undefined) {
        console.warn('gridstack.js: Option `' + oldName + '` is deprecated in ' + rev + info);
    }
}
/** checks for obsolete Jquery element attributes */
function obsoleteAttr(el, oldName, newName, rev) {
    const oldAttr = el.getAttribute(oldName);
    if (oldAttr !== null) {
        el.setAttribute(newName, oldAttr);
        console.warn('gridstack.js: attribute `' + oldName + '`=' + oldAttr + ' is deprecated on this object in ' + rev + ' and has been replaced with `' +
            newName + '`. It will be **removed** in a future release');
    }
}
/**
 * Utility methods
 */
class Utils {
    /** convert a potential selector into actual list of html elements. optional root which defaults to document (for shadow dom) */
    static getElements(els, root = document) {
        if (typeof els === 'string') {
            const doc = ('getElementById' in root) ? root : undefined;
            // Note: very common for people use to id='1,2,3' which is only legal as HTML5 id, but not CSS selectors
            // so if we start with a number, assume it's an id and just return that one item...
            // see https://github.com/gridstack/gridstack.js/issues/2234#issuecomment-1523796562
            if (doc && !isNaN(+els[0])) { // start with digit
                const el = doc.getElementById(els);
                return el ? [el] : [];
            }
            let list = root.querySelectorAll(els);
            if (!list.length && els[0] !== '.' && els[0] !== '#') {
                list = root.querySelectorAll('.' + els);
                if (!list.length) {
                    list = root.querySelectorAll('#' + els);
                }
            }
            return Array.from(list);
        }
        return [els];
    }
    /** convert a potential selector into actual single element. optional root which defaults to document (for shadow dom) */
    static getElement(els, root = document) {
        if (typeof els === 'string') {
            const doc = ('getElementById' in root) ? root : undefined;
            if (!els.length)
                return null;
            if (doc && els[0] === '#') {
                return doc.getElementById(els.substring(1));
            }
            if (els[0] === '#' || els[0] === '.' || els[0] === '[') {
                return root.querySelector(els);
            }
            // if we start with a digit, assume it's an id (error calling querySelector('#1')) as class are not valid CSS
            if (doc && !isNaN(+els[0])) { // start with digit
                return doc.getElementById(els);
            }
            // finally try string, then id, then class
            let el = root.querySelector(els);
            if (doc && !el) {
                el = doc.getElementById(els);
            }
            if (!el) {
                el = root.querySelector('.' + els);
            }
            return el;
        }
        return els;
    }
    /** create the default grid item divs, and content possibly lazy loaded calling GridStack.renderCB */
    static createWidgetDivs(itemClass, n) {
        const el = Utils.createDiv(['grid-stack-item', itemClass]);
        const cont = Utils.createDiv(['grid-stack-item-content'], el);
        const lazyLoad = n.lazyLoad || n.grid?.opts?.lazyLoad && n.lazyLoad !== false;
        if (lazyLoad) {
            if (!n.visibleObservable) {
                n.visibleObservable = new IntersectionObserver(([entry]) => {
                    if (entry.isIntersecting) {
                        n.visibleObservable?.disconnect();
                        delete n.visibleObservable;
                        _gridstack__WEBPACK_IMPORTED_MODULE_0__.GridStack.renderCB(cont, n);
                    }
                });
                window.setTimeout(() => n.visibleObservable?.observe(el)); // wait until callee sets position attributes
            }
        }
        else
            _gridstack__WEBPACK_IMPORTED_MODULE_0__.GridStack.renderCB(cont, n);
        return el;
    }
    /** create a div with the given classes */
    static createDiv(classes, parent) {
        const el = document.createElement('div');
        classes.forEach(c => { if (c)
            el.classList.add(c); });
        parent?.appendChild(el);
        return el;
    }
    /** true if we should resize to content. strict=true when only 'sizeToContent:true' and not a number which lets user adjust */
    static shouldSizeToContent(n, strict = false) {
        return n?.grid && (strict ?
            (n.sizeToContent === true || (n.grid.opts.sizeToContent === true && n.sizeToContent === undefined)) :
            (!!n.sizeToContent || (n.grid.opts.sizeToContent && n.sizeToContent !== false)));
    }
    /** returns true if a and b overlap */
    static isIntercepted(a, b) {
        return !(a.y >= b.y + b.h || a.y + a.h <= b.y || a.x + a.w <= b.x || a.x >= b.x + b.w);
    }
    /** returns true if a and b touch edges or corners */
    static isTouching(a, b) {
        return Utils.isIntercepted(a, { x: b.x - 0.5, y: b.y - 0.5, w: b.w + 1, h: b.h + 1 });
    }
    /** returns the area a and b overlap */
    static areaIntercept(a, b) {
        const x0 = (a.x > b.x) ? a.x : b.x;
        const x1 = (a.x + a.w < b.x + b.w) ? a.x + a.w : b.x + b.w;
        if (x1 <= x0)
            return 0; // no overlap
        const y0 = (a.y > b.y) ? a.y : b.y;
        const y1 = (a.y + a.h < b.y + b.h) ? a.y + a.h : b.y + b.h;
        if (y1 <= y0)
            return 0; // no overlap
        return (x1 - x0) * (y1 - y0);
    }
    /** returns the area */
    static area(a) {
        return a.w * a.h;
    }
    /**
     * Sorts array of nodes
     * @param nodes array to sort
     * @param dir 1 for ascending, -1 for descending (optional)
     **/
    static sort(nodes, dir = 1) {
        const und = 10000;
        return nodes.sort((a, b) => {
            const diffY = dir * ((a.y ?? und) - (b.y ?? und));
            if (diffY === 0)
                return dir * ((a.x ?? und) - (b.x ?? und));
            return diffY;
        });
    }
    /** find an item by id */
    static find(nodes, id) {
        return id ? nodes.find(n => n.id === id) : undefined;
    }
    /**
     * creates a style sheet with style id under given parent
     * @param id will set the 'gs-style-id' attribute to that id
     * @param parent to insert the stylesheet as first child,
     * if none supplied it will be appended to the document head instead.
     */
    static createStylesheet(id, parent, options) {
        const style = document.createElement('style');
        const nonce = options?.nonce;
        if (nonce)
            style.nonce = nonce;
        style.setAttribute('type', 'text/css');
        style.setAttribute('gs-style-id', id);
        // eslint-disable-next-line @typescript-eslint/no-explicit-any
        if (style.styleSheet) { // TODO: only CSSImportRule have that and different beast ??
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            style.styleSheet.cssText = '';
        }
        else {
            style.appendChild(document.createTextNode('')); // WebKit hack
        }
        if (!parent) {
            // default to head
            parent = document.getElementsByTagName('head')[0];
            parent.appendChild(style);
        }
        else {
            parent.insertBefore(style, parent.firstChild);
        }
        return style.sheet;
    }
    /** removed the given stylesheet id */
    static removeStylesheet(id, parent) {
        const target = parent || document;
        const el = target.querySelector('STYLE[gs-style-id=' + id + ']');
        if (el && el.parentNode)
            el.remove();
    }
    /** inserts a CSS rule */
    static addCSSRule(sheet, selector, rules) {
        if (typeof sheet.addRule === 'function') {
            sheet.addRule(selector, rules);
        }
        else if (typeof sheet.insertRule === 'function') {
            sheet.insertRule(`${selector}{${rules}}`);
        }
    }
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    static toBool(v) {
        if (typeof v === 'boolean') {
            return v;
        }
        if (typeof v === 'string') {
            v = v.toLowerCase();
            return !(v === '' || v === 'no' || v === 'false' || v === '0');
        }
        return Boolean(v);
    }
    static toNumber(value) {
        return (value === null || value.length === 0) ? undefined : Number(value);
    }
    static parseHeight(val) {
        let h;
        let unit = 'px';
        if (typeof val === 'string') {
            if (val === 'auto' || val === '')
                h = 0;
            else {
                const match = val.match(/^(-[0-9]+\.[0-9]+|[0-9]*\.[0-9]+|-[0-9]+|[0-9]+)(px|em|rem|vh|vw|%|cm|mm)?$/);
                if (!match) {
                    throw new Error(`Invalid height val = ${val}`);
                }
                unit = match[2] || 'px';
                h = parseFloat(match[1]);
            }
        }
        else {
            h = val;
        }
        return { h, unit };
    }
    /** copies unset fields in target to use the given default sources values */
    // eslint-disable-next-line
    static defaults(target, ...sources) {
        sources.forEach(source => {
            for (const key in source) {
                if (!source.hasOwnProperty(key))
                    return;
                if (target[key] === null || target[key] === undefined) {
                    target[key] = source[key];
                }
                else if (typeof source[key] === 'object' && typeof target[key] === 'object') {
                    // property is an object, recursively add it's field over... #1373
                    this.defaults(target[key], source[key]);
                }
            }
        });
        return target;
    }
    /** given 2 objects return true if they have the same values. Checks for Object {} having same fields and values (just 1 level down) */
    static same(a, b) {
        if (typeof a !== 'object')
            return a == b;
        if (typeof a !== typeof b)
            return false;
        // else we have object, check just 1 level deep for being same things...
        if (Object.keys(a).length !== Object.keys(b).length)
            return false;
        for (const key in a) {
            if (a[key] !== b[key])
                return false;
        }
        return true;
    }
    /** copies over b size & position (GridStackPosition), and optionally min/max as well */
    static copyPos(a, b, doMinMax = false) {
        if (b.x !== undefined)
            a.x = b.x;
        if (b.y !== undefined)
            a.y = b.y;
        if (b.w !== undefined)
            a.w = b.w;
        if (b.h !== undefined)
            a.h = b.h;
        if (doMinMax) {
            if (b.minW)
                a.minW = b.minW;
            if (b.minH)
                a.minH = b.minH;
            if (b.maxW)
                a.maxW = b.maxW;
            if (b.maxH)
                a.maxH = b.maxH;
        }
        return a;
    }
    /** true if a and b has same size & position */
    static samePos(a, b) {
        return a && b && a.x === b.x && a.y === b.y && (a.w || 1) === (b.w || 1) && (a.h || 1) === (b.h || 1);
    }
    /** given a node, makes sure it's min/max are valid */
    static sanitizeMinMax(node) {
        // remove 0, undefine, null
        if (!node.minW) {
            delete node.minW;
        }
        if (!node.minH) {
            delete node.minH;
        }
        if (!node.maxW) {
            delete node.maxW;
        }
        if (!node.maxH) {
            delete node.maxH;
        }
    }
    /** removes field from the first object if same as the second objects (like diffing) and internal '_' for saving */
    static removeInternalAndSame(a, b) {
        if (typeof a !== 'object' || typeof b !== 'object')
            return;
        for (let key in a) {
            const aVal = a[key];
            const bVal = b[key];
            if (key[0] === '_' || aVal === bVal) {
                delete a[key];
            }
            else if (aVal && typeof aVal === 'object' && bVal !== undefined) {
                Utils.removeInternalAndSame(aVal, bVal);
                if (!Object.keys(aVal).length) {
                    delete a[key];
                }
            }
        }
    }
    /** removes internal fields '_' and default values for saving */
    static removeInternalForSave(n, removeEl = true) {
        for (let key in n) {
            if (key[0] === '_' || n[key] === null || n[key] === undefined)
                delete n[key];
        }
        delete n.grid;
        if (removeEl)
            delete n.el;
        // delete default values (will be re-created on read)
        if (!n.autoPosition)
            delete n.autoPosition;
        if (!n.noResize)
            delete n.noResize;
        if (!n.noMove)
            delete n.noMove;
        if (!n.locked)
            delete n.locked;
        if (n.w === 1 || n.w === n.minW)
            delete n.w;
        if (n.h === 1 || n.h === n.minH)
            delete n.h;
    }
    /** return the closest parent (or itself) matching the given class */
    // static closestUpByClass(el: HTMLElement, name: string): HTMLElement {
    //   while (el) {
    //     if (el.classList.contains(name)) return el;
    //     el = el.parentElement
    //   }
    //   return null;
    // }
    /** delay calling the given function for given delay, preventing new calls from happening while waiting */
    static throttle(func, delay) {
        let isWaiting = false;
        return (...args) => {
            if (!isWaiting) {
                isWaiting = true;
                setTimeout(() => { func(...args); isWaiting = false; }, delay);
            }
        };
    }
    static removePositioningStyles(el) {
        const style = el.style;
        if (style.position) {
            style.removeProperty('position');
        }
        if (style.left) {
            style.removeProperty('left');
        }
        if (style.top) {
            style.removeProperty('top');
        }
        if (style.width) {
            style.removeProperty('width');
        }
        if (style.height) {
            style.removeProperty('height');
        }
    }
    /** @internal returns the passed element if scrollable, else the closest parent that will, up to the entire document scrolling element */
    static getScrollElement(el) {
        if (!el)
            return document.scrollingElement || document.documentElement; // IE support
        const style = getComputedStyle(el);
        const overflowRegex = /(auto|scroll)/;
        if (overflowRegex.test(style.overflow + style.overflowY)) {
            return el;
        }
        else {
            return this.getScrollElement(el.parentElement);
        }
    }
    /** @internal */
    static updateScrollPosition(el, position, distance) {
        // is widget in view?
        const rect = el.getBoundingClientRect();
        const innerHeightOrClientHeight = (window.innerHeight || document.documentElement.clientHeight);
        if (rect.top < 0 ||
            rect.bottom > innerHeightOrClientHeight) {
            // set scrollTop of first parent that scrolls
            // if parent is larger than el, set as low as possible
            // to get entire widget on screen
            const offsetDiffDown = rect.bottom - innerHeightOrClientHeight;
            const offsetDiffUp = rect.top;
            const scrollEl = this.getScrollElement(el);
            if (scrollEl !== null) {
                const prevScroll = scrollEl.scrollTop;
                if (rect.top < 0 && distance < 0) {
                    // moving up
                    if (el.offsetHeight > innerHeightOrClientHeight) {
                        scrollEl.scrollTop += distance;
                    }
                    else {
                        scrollEl.scrollTop += Math.abs(offsetDiffUp) > Math.abs(distance) ? distance : offsetDiffUp;
                    }
                }
                else if (distance > 0) {
                    // moving down
                    if (el.offsetHeight > innerHeightOrClientHeight) {
                        scrollEl.scrollTop += distance;
                    }
                    else {
                        scrollEl.scrollTop += offsetDiffDown > distance ? distance : offsetDiffDown;
                    }
                }
                // move widget y by amount scrolled
                position.top += scrollEl.scrollTop - prevScroll;
            }
        }
    }
    /**
     * @internal Function used to scroll the page.
     *
     * @param event `MouseEvent` that triggers the resize
     * @param el `HTMLElement` that's being resized
     * @param distance Distance from the V edges to start scrolling
     */
    static updateScrollResize(event, el, distance) {
        const scrollEl = this.getScrollElement(el);
        const height = scrollEl.clientHeight;
        // #1727 event.clientY is relative to viewport, so must compare this against position of scrollEl getBoundingClientRect().top
        // #1745 Special situation if scrollEl is document 'html': here browser spec states that
        // clientHeight is height of viewport, but getBoundingClientRect() is rectangle of html element;
        // this discrepancy arises because in reality scrollbar is attached to viewport, not html element itself.
        const offsetTop = (scrollEl === this.getScrollElement()) ? 0 : scrollEl.getBoundingClientRect().top;
        const pointerPosY = event.clientY - offsetTop;
        const top = pointerPosY < distance;
        const bottom = pointerPosY > height - distance;
        if (top) {
            // This also can be done with a timeout to keep scrolling while the mouse is
            // in the scrolling zone. (will have smoother behavior)
            scrollEl.scrollBy({ behavior: 'smooth', top: pointerPosY - distance });
        }
        else if (bottom) {
            scrollEl.scrollBy({ behavior: 'smooth', top: distance - (height - pointerPosY) });
        }
    }
    /** single level clone, returning a new object with same top fields. This will share sub objects and arrays */
    static clone(obj) {
        if (obj === null || obj === undefined || typeof (obj) !== 'object') {
            return obj;
        }
        // return Object.assign({}, obj);
        if (obj instanceof Array) {
            // eslint-disable-next-line @typescript-eslint/no-explicit-any
            return [...obj];
        }
        return { ...obj };
    }
    /**
     * Recursive clone version that returns a full copy, checking for nested objects and arrays ONLY.
     * Note: this will use as-is any key starting with double __ (and not copy inside) some lib have circular dependencies.
     */
    static cloneDeep(obj) {
        // list of fields we will skip during cloneDeep (nested objects, other internal)
        const skipFields = ['parentGrid', 'el', 'grid', 'subGrid', 'engine'];
        // return JSON.parse(JSON.stringify(obj)); // doesn't work with date format ?
        const ret = Utils.clone(obj);
        for (const key in ret) {
            // NOTE: we don't support function/circular dependencies so skip those properties for now...
            if (ret.hasOwnProperty(key) && typeof (ret[key]) === 'object' && key.substring(0, 2) !== '__' && !skipFields.find(k => k === key)) {
                ret[key] = Utils.cloneDeep(obj[key]);
            }
        }
        return ret;
    }
    /** deep clone the given HTML node, removing teh unique id field */
    static cloneNode(el) {
        const node = el.cloneNode(true);
        node.removeAttribute('id');
        return node;
    }
    static appendTo(el, parent) {
        let parentNode;
        if (typeof parent === 'string') {
            parentNode = Utils.getElement(parent);
        }
        else {
            parentNode = parent;
        }
        if (parentNode) {
            parentNode.appendChild(el);
        }
    }
    // public static setPositionRelative(el: HTMLElement): void {
    //   if (!(/^(?:r|a|f)/).test(getComputedStyle(el).position)) {
    //     el.style.position = "relative";
    //   }
    // }
    static addElStyles(el, styles) {
        if (styles instanceof Object) {
            for (const s in styles) {
                if (styles.hasOwnProperty(s)) {
                    if (Array.isArray(styles[s])) {
                        // support fallback value
                        styles[s].forEach(val => {
                            el.style[s] = val;
                        });
                    }
                    else {
                        el.style[s] = styles[s];
                    }
                }
            }
        }
    }
    static initEvent(e, info) {
        const evt = { type: info.type };
        const obj = {
            button: 0,
            which: 0,
            buttons: 1,
            bubbles: true,
            cancelable: true,
            target: info.target ? info.target : e.target
        };
        ['altKey', 'ctrlKey', 'metaKey', 'shiftKey'].forEach(p => evt[p] = e[p]); // keys
        ['pageX', 'pageY', 'clientX', 'clientY', 'screenX', 'screenY'].forEach(p => evt[p] = e[p]); // point info
        return { ...evt, ...obj };
    }
    /** copies the MouseEvent properties and sends it as another event to the given target */
    static simulateMouseEvent(e, simulatedType, target) {
        const simulatedEvent = document.createEvent('MouseEvents');
        simulatedEvent.initMouseEvent(simulatedType, // type
        true, // bubbles
        true, // cancelable
        window, // view
        1, // detail
        e.screenX, // screenX
        e.screenY, // screenY
        e.clientX, // clientX
        e.clientY, // clientY
        e.ctrlKey, // ctrlKey
        e.altKey, // altKey
        e.shiftKey, // shiftKey
        e.metaKey, // metaKey
        0, // button
        e.target // relatedTarget
        );
        (target || e.target).dispatchEvent(simulatedEvent);
    }
    /**
     * defines an element that is used to get the offset and scale from grid transforms
     * returns the scale and offsets from said element
    */
    static getValuesFromTransformedElement(parent) {
        const transformReference = document.createElement('div');
        Utils.addElStyles(transformReference, {
            opacity: '0',
            position: 'fixed',
            top: 0 + 'px',
            left: 0 + 'px',
            width: '1px',
            height: '1px',
            zIndex: '-999999',
        });
        parent.appendChild(transformReference);
        const transformValues = transformReference.getBoundingClientRect();
        parent.removeChild(transformReference);
        transformReference.remove();
        return {
            xScale: 1 / transformValues.width,
            yScale: 1 / transformValues.height,
            xOffset: transformValues.left,
            yOffset: transformValues.top,
        };
    }
    /** swap the given object 2 field values */
    static swap(o, a, b) {
        if (!o)
            return;
        const tmp = o[a];
        o[a] = o[b];
        o[b] = tmp;
    }
    /** returns true if event is inside the given element rectangle */
    // Note: Safari Mac has null event.relatedTarget which causes #1684 so check if DragEvent is inside the coordinates instead
    //    this.el.contains(event.relatedTarget as HTMLElement)
    // public static inside(e: MouseEvent, el: HTMLElement): boolean {
    //   // srcElement, toElement, target: all set to placeholder when leaving simple grid, so we can't use that (Chrome)
    //   const target: HTMLElement = e.relatedTarget || (e as any).fromElement;
    //   if (!target) {
    //     const { bottom, left, right, top } = el.getBoundingClientRect();
    //     return (e.x < right && e.x > left && e.y < bottom && e.y > top);
    //   }
    //   return el.contains(target);
    // }
    /** true if the item can be rotated (checking for prop, not space available) */
    static canBeRotated(n) {
        return !(!n || n.w === n.h || n.locked || n.noResize || n.grid?.opts.disableResize || (n.minW && n.minW === n.maxW) || (n.minH && n.minH === n.maxH));
    }
}
//# sourceMappingURL=utils.js.map

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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Widgets/Js/widgetController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});