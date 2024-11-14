(function webpackUniversalModuleDefinition(root, factory) {
	if(typeof exports === 'object' && typeof module === 'object')
		module.exports = factory();
	else if(typeof define === 'function' && define.amd)
		define([], factory);
	else if(typeof exports === 'object')
		exports["SlimSelect"] = factory();
	else
		root["SlimSelect"] = factory();
})(window, function() {
return /******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};
/******/
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/
/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId]) {
/******/ 			return installedModules[moduleId].exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			i: moduleId,
/******/ 			l: false,
/******/ 			exports: {}
/******/ 		};
/******/
/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);
/******/
/******/ 		// Flag the module as loaded
/******/ 		module.l = true;
/******/
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/
/******/
/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;
/******/
/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;
/******/
/******/ 	// define getter function for harmony exports
/******/ 	__webpack_require__.d = function(exports, name, getter) {
/******/ 		if(!__webpack_require__.o(exports, name)) {
/******/ 			Object.defineProperty(exports, name, { enumerable: true, get: getter });
/******/ 		}
/******/ 	};
/******/
/******/ 	// define __esModule on exports
/******/ 	__webpack_require__.r = function(exports) {
/******/ 		if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 			Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 		}
/******/ 		Object.defineProperty(exports, '__esModule', { value: true });
/******/ 	};
/******/
/******/ 	// create a fake namespace object
/******/ 	// mode & 1: value is a module id, require it
/******/ 	// mode & 2: merge all properties of value into the ns
/******/ 	// mode & 4: return value when already ns object
/******/ 	// mode & 8|1: behave like require
/******/ 	__webpack_require__.t = function(value, mode) {
/******/ 		if(mode & 1) value = __webpack_require__(value);
/******/ 		if(mode & 8) return value;
/******/ 		if((mode & 4) && typeof value === 'object' && value && value.__esModule) return value;
/******/ 		var ns = Object.create(null);
/******/ 		__webpack_require__.r(ns);
/******/ 		Object.defineProperty(ns, 'default', { enumerable: true, value: value });
/******/ 		if(mode & 2 && typeof value != 'string') for(var key in value) __webpack_require__.d(ns, key, function(key) { return value[key]; }.bind(null, key));
/******/ 		return ns;
/******/ 	};
/******/
/******/ 	// getDefaultExport function for compatibility with non-harmony modules
/******/ 	__webpack_require__.n = function(module) {
/******/ 		var getter = module && module.__esModule ?
/******/ 			function getDefault() { return module['default']; } :
/******/ 			function getModuleExports() { return module; };
/******/ 		__webpack_require__.d(getter, 'a', getter);
/******/ 		return getter;
/******/ 	};
/******/
/******/ 	// Object.prototype.hasOwnProperty.call
/******/ 	__webpack_require__.o = function(object, property) { return Object.prototype.hasOwnProperty.call(object, property); };
/******/
/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";
/******/
/******/
/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(__webpack_require__.s = 2);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

exports.__esModule = true;
exports.kebabCase = exports.highlight = exports.isValueInArrayOfObjects = exports.debounce = exports.putContent = exports.ensureElementInView = exports.hasClassInTree = void 0;
function hasClassInTree(element, className) {
    function hasClass(e, c) {
        if (!(!c || !e || !e.classList || !e.classList.contains(c))) {
            return e;
        }
        return null;
    }
    function parentByClass(e, c) {
        if (!e || e === document) {
            return null;
        }
        else if (hasClass(e, c)) {
            return e;
        }
        else {
            return parentByClass(e.parentNode, c);
        }
    }
    return hasClass(element, className) || parentByClass(element, className);
}
exports.hasClassInTree = hasClassInTree;
function ensureElementInView(container, element) {
    var cTop = container.scrollTop + container.offsetTop;
    var cBottom = cTop + container.clientHeight;
    var eTop = element.offsetTop;
    var eBottom = eTop + element.clientHeight;
    if (eTop < cTop) {
        container.scrollTop -= (cTop - eTop);
    }
    else if (eBottom > cBottom) {
        container.scrollTop += (eBottom - cBottom);
    }
}
exports.ensureElementInView = ensureElementInView;
function putContent(el, currentPosition, isOpen) {
    var height = el.offsetHeight;
    var rect = el.getBoundingClientRect();
    var elemTop = (isOpen ? rect.top : rect.top - height);
    var elemBottom = (isOpen ? rect.bottom : rect.bottom + height);
    if (elemTop <= 0) {
        return 'below';
    }
    if (elemBottom >= window.innerHeight) {
        return 'above';
    }
    return (isOpen ? currentPosition : 'below');
}
exports.putContent = putContent;
function debounce(func, wait, immediate) {
    if (wait === void 0) { wait = 100; }
    if (immediate === void 0) { immediate = false; }
    var timeout;
    return function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
        }
        var context = self;
        var later = function () {
            timeout = null;
            if (!immediate) {
                func.apply(context, args);
            }
        };
        var callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) {
            func.apply(context, args);
        }
    };
}
exports.debounce = debounce;
function isValueInArrayOfObjects(selected, key, value) {
    if (!Array.isArray(selected)) {
        return selected[key] === value;
    }
    for (var _i = 0, selected_1 = selected; _i < selected_1.length; _i++) {
        var s = selected_1[_i];
        if (s && s[key] && s[key] === value) {
            return true;
        }
    }
    return false;
}
exports.isValueInArrayOfObjects = isValueInArrayOfObjects;
function highlight(str, search, className) {
    var completedString = str;
    var regex = new RegExp('(' + search.trim() + ')(?![^<]*>[^<>]*</)', 'i');
    if (!str.match(regex)) {
        return str;
    }
    var matchStartPosition = str.match(regex).index;
    var matchEndPosition = matchStartPosition + str.match(regex)[0].toString().length;
    var originalTextFoundByRegex = str.substring(matchStartPosition, matchEndPosition);
    completedString = completedString.replace(regex, "<mark class=\"".concat(className, "\">").concat(originalTextFoundByRegex, "</mark>"));
    return completedString;
}
exports.highlight = highlight;
function kebabCase(str) {
    var result = str.replace(/[A-Z\u00C0-\u00D6\u00D8-\u00DE]/g, function (match) { return '-' + match.toLowerCase(); });
    return (str[0] === str[0].toUpperCase())
        ? result.substring(1)
        : result;
}
exports.kebabCase = kebabCase;
(function () {
    var w = window;
    if (typeof w.CustomEvent === 'function') {
        return;
    }
    function CustomEvent(event, params) {
        params = params || { bubbles: false, cancelable: false, detail: undefined };
        var evt = document.createEvent('CustomEvent');
        evt.initCustomEvent(event, params.bubbles, params.cancelable, params.detail);
        return evt;
    }
    CustomEvent.prototype = w.Event.prototype;
    w.CustomEvent = CustomEvent;
})();


/***/ }),
/* 1 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

exports.__esModule = true;
exports.validateOption = exports.validateData = exports.Data = void 0;
var Data = (function () {
    function Data(info) {
        this.contentOpen = false;
        this.contentPosition = 'below';
        this.isOnChangeEnabled = true;
        this.main = info.main;
        this.searchValue = '';
        this.data = [];
        this.filtered = null;
        this.parseSelectData();
        this.setSelectedFromSelect();
    }
    Data.prototype.newOption = function (info) {
        return {
            id: (info.id ? info.id : String(Math.floor(Math.random() * 100000000))),
            value: (info.value ? info.value : ''),
            text: (info.text ? info.text : ''),
            innerHTML: (info.innerHTML ? info.innerHTML : ''),
            selected: (info.selected ? info.selected : false),
            display: (info.display !== undefined ? info.display : true),
            disabled: (info.disabled ? info.disabled : false),
            placeholder: (info.placeholder ? info.placeholder : false),
            "class": (info["class"] ? info["class"] : undefined),
            data: (info.data ? info.data : {}),
            mandatory: (info.mandatory ? info.mandatory : false)
        };
    };
    Data.prototype.add = function (data) {
        this.data.push({
            id: String(Math.floor(Math.random() * 100000000)),
            value: data.value,
            text: data.text,
            innerHTML: '',
            selected: false,
            display: true,
            disabled: false,
            placeholder: false,
            "class": undefined,
            mandatory: data.mandatory,
            data: {}
        });
    };
    Data.prototype.parseSelectData = function () {
        this.data = [];
        var nodes = this.main.select.element.childNodes;
        for (var _i = 0, nodes_1 = nodes; _i < nodes_1.length; _i++) {
            var n = nodes_1[_i];
            if (n.nodeName === 'OPTGROUP') {
                var node = n;
                var optgroup = {
                    label: node.label,
                    options: []
                };
                var options = n.childNodes;
                for (var _a = 0, options_1 = options; _a < options_1.length; _a++) {
                    var o = options_1[_a];
                    if (o.nodeName === 'OPTION') {
                        var option = this.pullOptionData(o);
                        optgroup.options.push(option);
                        if (option.placeholder && option.text.trim() !== '') {
                            this.main.config.placeholderText = option.text;
                        }
                    }
                }
                this.data.push(optgroup);
            }
            else if (n.nodeName === 'OPTION') {
                var option = this.pullOptionData(n);
                this.data.push(option);
                if (option.placeholder && option.text.trim() !== '') {
                    this.main.config.placeholderText = option.text;
                }
            }
        }
    };
    Data.prototype.pullOptionData = function (option) {
        return {
            id: (option.dataset ? option.dataset.id : false) || String(Math.floor(Math.random() * 100000000)),
            value: option.value,
            text: option.text,
            innerHTML: option.innerHTML,
            selected: option.selected,
            disabled: option.disabled,
            placeholder: option.dataset.placeholder === 'true',
            "class": option.className,
            style: option.style.cssText,
            data: option.dataset,
            mandatory: (option.dataset ? option.dataset.mandatory === 'true' : false)
        };
    };
    Data.prototype.setSelectedFromSelect = function () {
        if (this.main.config.isMultiple) {
            var options = this.main.select.element.options;
            var newSelected = [];
            for (var _i = 0, options_2 = options; _i < options_2.length; _i++) {
                var o = options_2[_i];
                if (o.selected) {
                    var newOption = this.getObjectFromData(o.value, 'value');
                    if (newOption && newOption.id) {
                        newSelected.push(newOption.id);
                    }
                }
            }
            this.setSelected(newSelected, 'id');
        }
        else {
            var element = this.main.select.element;
            if (element.selectedIndex !== -1) {
                var option = element.options[element.selectedIndex];
                var value = option.value;
                this.setSelected(value, 'value');
            }
        }
    };
    Data.prototype.setSelected = function (value, type) {
        if (type === void 0) { type = 'id'; }
        for (var _i = 0, _a = this.data; _i < _a.length; _i++) {
            var d = _a[_i];
            if (d.hasOwnProperty('label')) {
                if (d.hasOwnProperty('options')) {
                    var options = d.options;
                    if (options) {
                        for (var _b = 0, options_3 = options; _b < options_3.length; _b++) {
                            var o = options_3[_b];
                            if (o.placeholder) {
                                continue;
                            }
                            o.selected = this.shouldBeSelected(o, value, type);
                        }
                    }
                }
            }
            else {
                d.selected = this.shouldBeSelected(d, value, type);
            }
        }
    };
    Data.prototype.shouldBeSelected = function (option, value, type) {
        if (type === void 0) { type = 'id'; }
        if (Array.isArray(value)) {
            for (var _i = 0, value_1 = value; _i < value_1.length; _i++) {
                var v = value_1[_i];
                if (type in option && String(option[type]) === String(v)) {
                    return true;
                }
            }
        }
        else {
            if (type in option && String(option[type]) === String(value)) {
                return true;
            }
        }
        return false;
    };
    Data.prototype.getSelected = function () {
        var value = { text: '', placeholder: this.main.config.placeholderText };
        var values = [];
        for (var _i = 0, _a = this.data; _i < _a.length; _i++) {
            var d = _a[_i];
            if (d.hasOwnProperty('label')) {
                if (d.hasOwnProperty('options')) {
                    var options = d.options;
                    if (options) {
                        for (var _b = 0, options_4 = options; _b < options_4.length; _b++) {
                            var o = options_4[_b];
                            if (o.selected) {
                                if (!this.main.config.isMultiple) {
                                    value = o;
                                }
                                else {
                                    values.push(o);
                                }
                            }
                        }
                    }
                }
            }
            else {
                if (d.selected) {
                    if (!this.main.config.isMultiple) {
                        value = d;
                    }
                    else {
                        values.push(d);
                    }
                }
            }
        }
        if (this.main.config.isMultiple) {
            return values;
        }
        return value;
    };
    Data.prototype.addToSelected = function (value, type) {
        if (type === void 0) { type = 'id'; }
        if (this.main.config.isMultiple) {
            var values = [];
            var selected = this.getSelected();
            if (Array.isArray(selected)) {
                for (var _i = 0, selected_1 = selected; _i < selected_1.length; _i++) {
                    var s = selected_1[_i];
                    values.push(s[type]);
                }
            }
            values.push(value);
            this.setSelected(values, type);
        }
    };
    Data.prototype.removeFromSelected = function (value, type) {
        if (type === void 0) { type = 'id'; }
        if (this.main.config.isMultiple) {
            var values = [];
            var selected = this.getSelected();
            for (var _i = 0, selected_2 = selected; _i < selected_2.length; _i++) {
                var s = selected_2[_i];
                if (String(s[type]) !== String(value)) {
                    values.push(s[type]);
                }
            }
            this.setSelected(values, type);
        }
    };
    Data.prototype.onDataChange = function () {
        if (this.main.onChange && this.isOnChangeEnabled) {
            this.main.onChange(JSON.parse(JSON.stringify(this.getSelected())));
        }
    };
    Data.prototype.getObjectFromData = function (value, type) {
        if (type === void 0) { type = 'id'; }
        for (var _i = 0, _a = this.data; _i < _a.length; _i++) {
            var d = _a[_i];
            if (type in d && String(d[type]) === String(value)) {
                return d;
            }
            if (d.hasOwnProperty('options')) {
                var optgroupObject = d;
                if (optgroupObject.options) {
                    for (var _b = 0, _c = optgroupObject.options; _b < _c.length; _b++) {
                        var oo = _c[_b];
                        if (String(oo[type]) === String(value)) {
                            return oo;
                        }
                    }
                }
            }
        }
        return null;
    };
    Data.prototype.search = function (search) {
        this.searchValue = search;
        if (search.trim() === '') {
            this.filtered = null;
            return;
        }
        var searchFilter = this.main.config.searchFilter;
        var valuesArray = this.data.slice(0);
        search = search.trim();
        var filtered = valuesArray.map(function (obj) {
            if (obj.hasOwnProperty('options')) {
                var optgroupObj = obj;
                var options = [];
                if (optgroupObj.options) {
                    options = optgroupObj.options.filter(function (opt) {
                        return searchFilter(opt, search);
                    });
                }
                if (options.length !== 0) {
                    var optgroup = Object.assign({}, optgroupObj);
                    optgroup.options = options;
                    return optgroup;
                }
            }
            if (obj.hasOwnProperty('text')) {
                var optionObj = obj;
                if (searchFilter(optionObj, search)) {
                    return obj;
                }
            }
            return null;
        });
        this.filtered = filtered.filter(function (info) { return info; });
    };
    return Data;
}());
exports.Data = Data;
function validateData(data) {
    if (!data) {
        console.error('Data must be an array of objects');
        return false;
    }
    var isValid = false;
    var errorCount = 0;
    for (var _i = 0, data_1 = data; _i < data_1.length; _i++) {
        var d = data_1[_i];
        if (d.hasOwnProperty('label')) {
            if (d.hasOwnProperty('options')) {
                var optgroup = d;
                var options = optgroup.options;
                if (options) {
                    for (var _a = 0, options_5 = options; _a < options_5.length; _a++) {
                        var o = options_5[_a];
                        isValid = validateOption(o);
                        if (!isValid) {
                            errorCount++;
                        }
                    }
                }
            }
        }
        else {
            var option = d;
            isValid = validateOption(option);
            if (!isValid) {
                errorCount++;
            }
        }
    }
    return errorCount === 0;
}
exports.validateData = validateData;
function validateOption(option) {
    if (option.text === undefined) {
        console.error('Data object option must have at least have a text value. Check object: ' + JSON.stringify(option));
        return false;
    }
    return true;
}
exports.validateOption = validateOption;


/***/ }),
/* 2 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

exports.__esModule = true;
var config_1 = __webpack_require__(3);
var select_1 = __webpack_require__(4);
var slim_1 = __webpack_require__(5);
var data_1 = __webpack_require__(1);
var helper_1 = __webpack_require__(0);
var SlimSelect = (function () {
    function SlimSelect(info) {
        var _this = this;
        this.ajax = null;
        this.addable = null;
        this.beforeOnChange = null;
        this.onChange = null;
        this.beforeOpen = null;
        this.afterOpen = null;
        this.beforeClose = null;
        this.afterClose = null;
        this.windowScroll = (0, helper_1.debounce)(function (e) {
            if (_this.data.contentOpen) {
                if ((0, helper_1.putContent)(_this.slim.content, _this.data.contentPosition, _this.data.contentOpen) === 'above') {
                    _this.moveContentAbove();
                }
                else {
                    _this.moveContentBelow();
                }
            }
        });
        this.documentClick = function (e) {
            if (e.target && !(0, helper_1.hasClassInTree)(e.target, _this.config.id)) {
                _this.close();
            }
        };
        var selectElement = this.validate(info);
        if (selectElement.dataset.ssid) {
            this.destroy(selectElement.dataset.ssid);
        }
        if (info.ajax) {
            this.ajax = info.ajax;
        }
        if (info.addable) {
            this.addable = info.addable;
        }
        this.config = new config_1.Config({
            select: selectElement,
            isAjax: (info.ajax ? true : false),
            showSearch: info.showSearch,
            searchPlaceholder: info.searchPlaceholder,
            searchText: info.searchText,
            searchingText: info.searchingText,
            searchFocus: info.searchFocus,
            searchHighlight: info.searchHighlight,
            searchFilter: info.searchFilter,
            closeOnSelect: info.closeOnSelect,
            showContent: info.showContent,
            placeholderText: info.placeholder,
            allowDeselect: info.allowDeselect,
            allowDeselectOption: info.allowDeselectOption,
            hideSelectedOption: info.hideSelectedOption,
            deselectLabel: info.deselectLabel,
            isEnabled: info.isEnabled,
            valuesUseText: info.valuesUseText,
            showOptionTooltips: info.showOptionTooltips,
            selectByGroup: info.selectByGroup,
            limit: info.limit,
            timeoutDelay: info.timeoutDelay,
            addToBody: info.addToBody
        });
        this.select = new select_1.Select({
            select: selectElement,
            main: this
        });
        this.data = new data_1.Data({ main: this });
        this.slim = new slim_1.Slim({ main: this });
        if (this.select.element.parentNode) {
            this.select.element.parentNode.insertBefore(this.slim.container, this.select.element.nextSibling);
        }
        if (info.data) {
            this.setData(info.data);
        }
        else {
            this.render();
        }
        document.addEventListener('click', this.documentClick);
        if (this.config.showContent === 'auto') {
            window.addEventListener('scroll', this.windowScroll, false);
        }
        if (info.beforeOnChange) {
            this.beforeOnChange = info.beforeOnChange;
        }
        if (info.onChange) {
            this.onChange = info.onChange;
        }
        if (info.beforeOpen) {
            this.beforeOpen = info.beforeOpen;
        }
        if (info.afterOpen) {
            this.afterOpen = info.afterOpen;
        }
        if (info.beforeClose) {
            this.beforeClose = info.beforeClose;
        }
        if (info.afterClose) {
            this.afterClose = info.afterClose;
        }
        if (!this.config.isEnabled) {
            this.disable();
        }
    }
    SlimSelect.prototype.validate = function (info) {
        var select = (typeof info.select === 'string' ? document.querySelector(info.select) : info.select);
        if (!select) {
            throw new Error('Could not find select element');
        }
        if (select.tagName !== 'SELECT') {
            throw new Error('Element isnt of type select');
        }
        return select;
    };
    SlimSelect.prototype.selected = function () {
        if (this.config.isMultiple) {
            var selected = this.data.getSelected();
            var outputSelected = [];
            for (var _i = 0, selected_1 = selected; _i < selected_1.length; _i++) {
                var s = selected_1[_i];
                outputSelected.push(s.value);
            }
            return outputSelected;
        }
        else {
            var selected = this.data.getSelected();
            return (selected ? selected.value : '');
        }
    };
    SlimSelect.prototype.set = function (value, type, close, render) {
        if (type === void 0) { type = 'value'; }
        if (close === void 0) { close = true; }
        if (render === void 0) { render = true; }
        if (this.config.isMultiple && !Array.isArray(value)) {
            this.data.addToSelected(value, type);
        }
        else {
            this.data.setSelected(value, type);
        }
        this.select.setValue();
        this.data.onDataChange();
        this.render();
        if (this.config.hideSelectedOption && this.config.isMultiple && this.data.getSelected().length === this.data.data.length) {
            close = true;
        }
        if (close) {
            this.close();
        }
    };
    SlimSelect.prototype.setSelected = function (value, type, close, render) {
        if (type === void 0) { type = 'value'; }
        if (close === void 0) { close = true; }
        if (render === void 0) { render = true; }
        this.set(value, type, close, render);
    };
    SlimSelect.prototype.setData = function (data) {
        var isValid = (0, data_1.validateData)(data);
        if (!isValid) {
            console.error('Validation problem on: #' + this.select.element.id);
            return;
        }
        var newData = JSON.parse(JSON.stringify(data));
        var selected = this.data.getSelected();
        for (var i = 0; i < newData.length; i++) {
            if (!newData[i].value && !newData[i].placeholder) {
                newData[i].value = newData[i].text;
            }
        }
        if (this.config.isAjax && selected) {
            if (this.config.isMultiple) {
                var reverseSelected = selected.reverse();
                for (var _i = 0, reverseSelected_1 = reverseSelected; _i < reverseSelected_1.length; _i++) {
                    var r = reverseSelected_1[_i];
                    newData.unshift(r);
                }
            }
            else {
                newData.unshift(selected);
                for (var i = 0; i < newData.length; i++) {
                    if (!newData[i].placeholder && newData[i].value === selected.value && newData[i].text === selected.text) {
                        newData.splice(i, 1);
                    }
                }
                var hasPlaceholder = false;
                for (var i = 0; i < newData.length; i++) {
                    if (newData[i].placeholder) {
                        hasPlaceholder = true;
                    }
                }
                if (!hasPlaceholder) {
                    newData.unshift({ text: '', placeholder: true });
                }
            }
        }
        this.select.create(newData);
        this.data.parseSelectData();
        this.data.setSelectedFromSelect();
    };
    SlimSelect.prototype.addData = function (data) {
        var isValid = (0, data_1.validateData)([data]);
        if (!isValid) {
            console.error('Validation problem on: #' + this.select.element.id);
            return;
        }
        this.data.add(this.data.newOption(data));
        this.select.create(this.data.data);
        this.data.parseSelectData();
        this.data.setSelectedFromSelect();
        this.render();
    };
    SlimSelect.prototype.open = function () {
        var _this = this;
        if (!this.config.isEnabled) {
            return;
        }
        if (this.data.contentOpen) {
            return;
        }
        if (this.config.hideSelectedOption && this.config.isMultiple && this.data.getSelected().length === this.data.data.length) {
            return;
        }
        if (this.beforeOpen) {
            this.beforeOpen();
        }
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.plus.classList.add('ss-cross');
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.arrowIcon.arrow.classList.remove('arrow-down');
            this.slim.singleSelected.arrowIcon.arrow.classList.add('arrow-up');
        }
        this.slim[(this.config.isMultiple ? 'multiSelected' : 'singleSelected')].container.classList.add((this.data.contentPosition === 'above' ? this.config.openAbove : this.config.openBelow));
        if (this.config.addToBody) {
            var containerRect = this.slim.container.getBoundingClientRect();
            this.slim.content.style.top = (containerRect.top + containerRect.height + window.scrollY) + 'px';
            this.slim.content.style.left = (containerRect.left + window.scrollX) + 'px';
            this.slim.content.style.width = containerRect.width + 'px';
        }
        this.slim.content.classList.add(this.config.open);
        if (this.config.showContent.toLowerCase() === 'up') {
            this.moveContentAbove();
        }
        else if (this.config.showContent.toLowerCase() === 'down') {
            this.moveContentBelow();
        }
        else {
            if ((0, helper_1.putContent)(this.slim.content, this.data.contentPosition, this.data.contentOpen) === 'above') {
                this.moveContentAbove();
            }
            else {
                this.moveContentBelow();
            }
        }
        if (!this.config.isMultiple) {
            var selected = this.data.getSelected();
            if (selected) {
                var selectedId = selected.id;
                var selectedOption = this.slim.list.querySelector('[data-id="' + selectedId + '"]');
                if (selectedOption) {
                    (0, helper_1.ensureElementInView)(this.slim.list, selectedOption);
                }
            }
        }
        setTimeout(function () {
            _this.data.contentOpen = true;
            if (_this.config.searchFocus) {
                _this.slim.search.input.focus();
            }
            if (_this.afterOpen) {
                _this.afterOpen();
            }
        }, this.config.timeoutDelay);
    };
    SlimSelect.prototype.close = function () {
        var _this = this;
        if (!this.data.contentOpen) {
            return;
        }
        if (this.beforeClose) {
            this.beforeClose();
        }
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.openAbove);
            this.slim.multiSelected.container.classList.remove(this.config.openBelow);
            this.slim.multiSelected.plus.classList.remove('ss-cross');
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.openAbove);
            this.slim.singleSelected.container.classList.remove(this.config.openBelow);
            this.slim.singleSelected.arrowIcon.arrow.classList.add('arrow-down');
            this.slim.singleSelected.arrowIcon.arrow.classList.remove('arrow-up');
        }
        this.slim.content.classList.remove(this.config.open);
        this.data.contentOpen = false;
        this.search('');
        setTimeout(function () {
            _this.slim.content.removeAttribute('style');
            _this.data.contentPosition = 'below';
            if (_this.config.isMultiple && _this.slim.multiSelected) {
                _this.slim.multiSelected.container.classList.remove(_this.config.openAbove);
                _this.slim.multiSelected.container.classList.remove(_this.config.openBelow);
            }
            else if (_this.slim.singleSelected) {
                _this.slim.singleSelected.container.classList.remove(_this.config.openAbove);
                _this.slim.singleSelected.container.classList.remove(_this.config.openBelow);
            }
            _this.slim.search.input.blur();
            if (_this.afterClose) {
                _this.afterClose();
            }
        }, this.config.timeoutDelay);
    };
    SlimSelect.prototype.moveContentAbove = function () {
        var selectHeight = 0;
        if (this.config.isMultiple && this.slim.multiSelected) {
            selectHeight = this.slim.multiSelected.container.offsetHeight;
        }
        else if (this.slim.singleSelected) {
            selectHeight = this.slim.singleSelected.container.offsetHeight;
        }
        var contentHeight = this.slim.content.offsetHeight;
        var height = selectHeight + contentHeight - 1;
        this.slim.content.style.margin = '-' + height + 'px 0 0 0';
        this.slim.content.style.height = (height - selectHeight + 1) + 'px';
        this.slim.content.style.transformOrigin = 'center bottom';
        this.data.contentPosition = 'above';
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.openBelow);
            this.slim.multiSelected.container.classList.add(this.config.openAbove);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.openBelow);
            this.slim.singleSelected.container.classList.add(this.config.openAbove);
        }
    };
    SlimSelect.prototype.moveContentBelow = function () {
        this.data.contentPosition = 'below';
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.openAbove);
            this.slim.multiSelected.container.classList.add(this.config.openBelow);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.openAbove);
            this.slim.singleSelected.container.classList.add(this.config.openBelow);
        }
    };
    SlimSelect.prototype.enable = function () {
        this.config.isEnabled = true;
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.remove(this.config.disabled);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.remove(this.config.disabled);
        }
        this.select.triggerMutationObserver = false;
        this.select.element.disabled = false;
        this.slim.search.input.disabled = false;
        this.select.triggerMutationObserver = true;
    };
    SlimSelect.prototype.disable = function () {
        this.config.isEnabled = false;
        if (this.config.isMultiple && this.slim.multiSelected) {
            this.slim.multiSelected.container.classList.add(this.config.disabled);
        }
        else if (this.slim.singleSelected) {
            this.slim.singleSelected.container.classList.add(this.config.disabled);
        }
        this.select.triggerMutationObserver = false;
        this.select.element.disabled = true;
        this.slim.search.input.disabled = true;
        this.select.triggerMutationObserver = true;
    };
    SlimSelect.prototype.search = function (value) {
        if (this.data.searchValue === value) {
            return;
        }
        this.slim.search.input.value = value;
        if (this.config.isAjax) {
            var master_1 = this;
            this.config.isSearching = true;
            this.render();
            if (this.ajax) {
                this.ajax(value, function (info) {
                    master_1.config.isSearching = false;
                    if (Array.isArray(info)) {
                        info.unshift({ text: '', placeholder: true });
                        master_1.setData(info);
                        master_1.data.search(value);
                        master_1.render();
                    }
                    else if (typeof info === 'string') {
                        master_1.slim.options(info);
                    }
                    else {
                        master_1.render();
                    }
                });
            }
        }
        else {
            this.data.search(value);
            this.render();
        }
    };
    SlimSelect.prototype.setSearchText = function (text) {
        this.config.searchText = text;
    };
    SlimSelect.prototype.render = function () {
        if (this.config.isMultiple) {
            this.slim.values();
        }
        else {
            this.slim.placeholder();
            this.slim.deselect();
        }
        this.slim.options();
    };
    SlimSelect.prototype.destroy = function (id) {
        if (id === void 0) { id = null; }
        var slim = (id ? document.querySelector('.' + id + '.ss-main') : this.slim.container);
        var select = (id ? document.querySelector("[data-ssid=".concat(id, "]")) : this.select.element);
        if (!slim || !select) {
            return;
        }
        document.removeEventListener('click', this.documentClick);
        if (this.config.showContent === 'auto') {
            window.removeEventListener('scroll', this.windowScroll, false);
        }
        select.style.display = '';
        delete select.dataset.ssid;
        var el = select;
        el.slim = null;
        if (slim.parentElement) {
            slim.parentElement.removeChild(slim);
        }
        if (this.config.addToBody) {
            var slimContent = (id ? document.querySelector('.' + id + '.ss-content') : this.slim.content);
            if (!slimContent) {
                return;
            }
            document.body.removeChild(slimContent);
        }
    };
    return SlimSelect;
}());
exports["default"] = SlimSelect;


/***/ }),
/* 3 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

exports.__esModule = true;
exports.Config = void 0;
var Config = (function () {
    function Config(info) {
        this.id = '';
        this.isMultiple = false;
        this.isAjax = false;
        this.isSearching = false;
        this.showSearch = true;
        this.searchFocus = true;
        this.searchHighlight = false;
        this.closeOnSelect = true;
        this.showContent = 'auto';
        this.searchPlaceholder = 'Search';
        this.searchText = 'No Results';
        this.searchingText = 'Searching...';
        this.placeholderText = 'Select Value';
        this.allowDeselect = false;
        this.allowDeselectOption = false;
        this.hideSelectedOption = false;
        this.deselectLabel = 'x';
        this.isEnabled = true;
        this.valuesUseText = false;
        this.showOptionTooltips = false;
        this.selectByGroup = false;
        this.limit = 0;
        this.timeoutDelay = 200;
        this.addToBody = false;
        this.main = 'ss-main';
        this.singleSelected = 'ss-single-selected';
        this.arrow = 'ss-arrow';
        this.multiSelected = 'ss-multi-selected';
        this.add = 'ss-add';
        this.plus = 'ss-plus';
        this.values = 'ss-values';
        this.value = 'ss-value';
        this.valueText = 'ss-value-text';
        this.valueDelete = 'ss-value-delete';
        this.content = 'ss-content';
        this.open = 'ss-open';
        this.openAbove = 'ss-open-above';
        this.openBelow = 'ss-open-below';
        this.search = 'ss-search';
        this.searchHighlighter = 'ss-search-highlight';
        this.addable = 'ss-addable';
        this.list = 'ss-list';
        this.optgroup = 'ss-optgroup';
        this.optgroupLabel = 'ss-optgroup-label';
        this.optgroupLabelSelectable = 'ss-optgroup-label-selectable';
        this.option = 'ss-option';
        this.optionSelected = 'ss-option-selected';
        this.highlighted = 'ss-highlighted';
        this.disabled = 'ss-disabled';
        this.hide = 'ss-hide';
        this.id = 'ss-' + Math.floor(Math.random() * 100000);
        this.style = info.select.style.cssText;
        this["class"] = info.select.className.split(' ');
        this.isMultiple = info.select.multiple;
        this.isAjax = info.isAjax;
        this.showSearch = (info.showSearch === false ? false : true);
        this.searchFocus = (info.searchFocus === false ? false : true);
        this.searchHighlight = (info.searchHighlight === true ? true : false);
        this.closeOnSelect = (info.closeOnSelect === false ? false : true);
        if (info.showContent) {
            this.showContent = info.showContent;
        }
        this.isEnabled = (info.isEnabled === false ? false : true);
        if (info.searchPlaceholder) {
            this.searchPlaceholder = info.searchPlaceholder;
        }
        if (info.searchText) {
            this.searchText = info.searchText;
        }
        if (info.searchingText) {
            this.searchingText = info.searchingText;
        }
        if (info.placeholderText) {
            this.placeholderText = info.placeholderText;
        }
        this.allowDeselect = (info.allowDeselect === true ? true : false);
        this.allowDeselectOption = (info.allowDeselectOption === true ? true : false);
        this.hideSelectedOption = (info.hideSelectedOption === true ? true : false);
        if (info.deselectLabel) {
            this.deselectLabel = info.deselectLabel;
        }
        if (info.valuesUseText) {
            this.valuesUseText = info.valuesUseText;
        }
        if (info.showOptionTooltips) {
            this.showOptionTooltips = info.showOptionTooltips;
        }
        if (info.selectByGroup) {
            this.selectByGroup = info.selectByGroup;
        }
        if (info.limit) {
            this.limit = info.limit;
        }
        if (info.searchFilter) {
            this.searchFilter = info.searchFilter;
        }
        if (info.timeoutDelay != null) {
            this.timeoutDelay = info.timeoutDelay;
        }
        this.addToBody = (info.addToBody === true ? true : false);
    }
    Config.prototype.searchFilter = function (opt, search) {
        return opt.text.toLowerCase().indexOf(search.toLowerCase()) !== -1;
    };
    return Config;
}());
exports.Config = Config;


/***/ }),
/* 4 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

exports.__esModule = true;
exports.Select = void 0;
var helper_1 = __webpack_require__(0);
var Select = (function () {
    function Select(info) {
        this.triggerMutationObserver = true;
        this.element = info.select;
        this.main = info.main;
        if (this.element.disabled) {
            this.main.config.isEnabled = false;
        }
        this.addAttributes();
        this.addEventListeners();
        this.mutationObserver = null;
        this.addMutationObserver();
        var el = this.element;
        el.slim = info.main;
    }
    Select.prototype.setValue = function () {
        if (!this.main.data.getSelected()) {
            return;
        }
        if (this.main.config.isMultiple) {
            var selected = this.main.data.getSelected();
            var options = this.element.options;
            for (var _i = 0, options_1 = options; _i < options_1.length; _i++) {
                var o = options_1[_i];
                o.selected = false;
                for (var _a = 0, selected_1 = selected; _a < selected_1.length; _a++) {
                    var s = selected_1[_a];
                    if (s.value === o.value) {
                        o.selected = true;
                    }
                }
            }
        }
        else {
            var selected = this.main.data.getSelected();
            this.element.value = (selected ? selected.value : '');
        }
        this.main.data.isOnChangeEnabled = false;
        this.element.dispatchEvent(new CustomEvent('change', { bubbles: true }));
        this.main.data.isOnChangeEnabled = true;
    };
    Select.prototype.addAttributes = function () {
        this.element.tabIndex = -1;
        this.element.style.display = 'none';
        this.element.dataset.ssid = this.main.config.id;
        this.element.setAttribute('aria-hidden', 'true');
    };
    Select.prototype.addEventListeners = function () {
        var _this = this;
        this.element.addEventListener('change', function (e) {
            _this.main.data.setSelectedFromSelect();
            _this.main.render();
        });
    };
    Select.prototype.addMutationObserver = function () {
        var _this = this;
        if (this.main.config.isAjax) {
            return;
        }
        this.mutationObserver = new MutationObserver(function (mutations) {
            if (!_this.triggerMutationObserver) {
                return;
            }
            _this.main.data.parseSelectData();
            _this.main.data.setSelectedFromSelect();
            _this.main.render();
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === 'class') {
                    _this.main.slim.updateContainerDivClass(_this.main.slim.container);
                }
            });
        });
        this.observeMutationObserver();
    };
    Select.prototype.observeMutationObserver = function () {
        if (!this.mutationObserver) {
            return;
        }
        this.mutationObserver.observe(this.element, {
            attributes: true,
            childList: true,
            characterData: true
        });
    };
    Select.prototype.disconnectMutationObserver = function () {
        if (this.mutationObserver) {
            this.mutationObserver.disconnect();
        }
    };
    Select.prototype.create = function (data) {
        this.element.innerHTML = '';
        for (var _i = 0, data_1 = data; _i < data_1.length; _i++) {
            var d = data_1[_i];
            if (d.hasOwnProperty('options')) {
                var optgroupObject = d;
                var optgroupEl = document.createElement('optgroup');
                optgroupEl.label = optgroupObject.label;
                if (optgroupObject.options) {
                    for (var _a = 0, _b = optgroupObject.options; _a < _b.length; _a++) {
                        var oo = _b[_a];
                        optgroupEl.appendChild(this.createOption(oo));
                    }
                }
                this.element.appendChild(optgroupEl);
            }
            else {
                this.element.appendChild(this.createOption(d));
            }
        }
    };
    Select.prototype.createOption = function (info) {
        var optionEl = document.createElement('option');
        optionEl.value = info.value !== '' ? info.value : info.text;
        optionEl.innerHTML = info.innerHTML || info.text;
        if (info.selected) {
            optionEl.selected = info.selected;
        }
        if (info.display === false) {
            optionEl.style.display = 'none';
        }
        if (info.disabled) {
            optionEl.disabled = true;
        }
        if (info.placeholder) {
            optionEl.setAttribute('data-placeholder', 'true');
        }
        if (info.mandatory) {
            optionEl.setAttribute('data-mandatory', 'true');
        }
        if (info["class"]) {
            info["class"].split(' ').forEach(function (optionClass) {
                optionEl.classList.add(optionClass);
            });
        }
        if (info.data && typeof info.data === 'object') {
            Object.keys(info.data).forEach(function (key) {
                optionEl.setAttribute('data-' + (0, helper_1.kebabCase)(key), info.data[key]);
            });
        }
        return optionEl;
    };
    return Select;
}());
exports.Select = Select;


/***/ }),
/* 5 */
/***/ (function(module, exports, __webpack_require__) {

"use strict";

exports.__esModule = true;
exports.Slim = void 0;
var helper_1 = __webpack_require__(0);
var data_1 = __webpack_require__(1);
var Slim = (function () {
    function Slim(info) {
        this.main = info.main;
        this.container = this.containerDiv();
        this.content = this.contentDiv();
        this.search = this.searchDiv();
        this.list = this.listDiv();
        this.options();
        this.singleSelected = null;
        this.multiSelected = null;
        if (this.main.config.isMultiple) {
            this.multiSelected = this.multiSelectedDiv();
            if (this.multiSelected) {
                this.container.appendChild(this.multiSelected.container);
            }
        }
        else {
            this.singleSelected = this.singleSelectedDiv();
            this.container.appendChild(this.singleSelected.container);
        }
        if (this.main.config.addToBody) {
            this.content.classList.add(this.main.config.id);
            document.body.appendChild(this.content);
        }
        else {
            this.container.appendChild(this.content);
        }
        this.content.appendChild(this.search.container);
        this.content.appendChild(this.list);
    }
    Slim.prototype.containerDiv = function () {
        var container = document.createElement('div');
        container.style.cssText = this.main.config.style;
        this.updateContainerDivClass(container);
        return container;
    };
    Slim.prototype.updateContainerDivClass = function (container) {
        this.main.config["class"] = this.main.select.element.className.split(' ');
        container.className = '';
        container.classList.add(this.main.config.id);
        container.classList.add(this.main.config.main);
        for (var _i = 0, _a = this.main.config["class"]; _i < _a.length; _i++) {
            var c = _a[_i];
            if (c.trim() !== '') {
                container.classList.add(c);
            }
        }
    };
    Slim.prototype.singleSelectedDiv = function () {
        var _this = this;
        var container = document.createElement('div');
        container.classList.add(this.main.config.singleSelected);
        var placeholder = document.createElement('span');
        placeholder.classList.add('placeholder');
        container.appendChild(placeholder);
        var deselect = document.createElement('span');
        deselect.innerHTML = this.main.config.deselectLabel;
        deselect.classList.add('ss-deselect');
        deselect.onclick = function (e) {
            e.stopPropagation();
            if (!_this.main.config.isEnabled) {
                return;
            }
            _this.main.set('');
        };
        container.appendChild(deselect);
        var arrowContainer = document.createElement('span');
        arrowContainer.classList.add(this.main.config.arrow);
        var arrowIcon = document.createElement('span');
        arrowIcon.classList.add('arrow-down');
        arrowContainer.appendChild(arrowIcon);
        container.appendChild(arrowContainer);
        container.onclick = function () {
            if (!_this.main.config.isEnabled) {
                return;
            }
            _this.main.data.contentOpen ? _this.main.close() : _this.main.open();
        };
        return {
            container: container,
            placeholder: placeholder,
            deselect: deselect,
            arrowIcon: {
                container: arrowContainer,
                arrow: arrowIcon
            }
        };
    };
    Slim.prototype.placeholder = function () {
        var selected = this.main.data.getSelected();
        if (selected === null || (selected && selected.placeholder)) {
            var placeholder = document.createElement('span');
            placeholder.classList.add(this.main.config.disabled);
            placeholder.innerHTML = this.main.config.placeholderText;
            if (this.singleSelected) {
                this.singleSelected.placeholder.innerHTML = placeholder.outerHTML;
            }
        }
        else {
            var selectedValue = '';
            if (selected) {
                selectedValue = selected.innerHTML && this.main.config.valuesUseText !== true ? selected.innerHTML : selected.text;
            }
            if (this.singleSelected) {
                this.singleSelected.placeholder.innerHTML = (selected ? selectedValue : '');
            }
        }
    };
    Slim.prototype.deselect = function () {
        if (this.singleSelected) {
            if (!this.main.config.allowDeselect) {
                this.singleSelected.deselect.classList.add('ss-hide');
                return;
            }
            if (this.main.selected() === '') {
                this.singleSelected.deselect.classList.add('ss-hide');
            }
            else {
                this.singleSelected.deselect.classList.remove('ss-hide');
            }
        }
    };
    Slim.prototype.multiSelectedDiv = function () {
        var _this = this;
        var container = document.createElement('div');
        container.classList.add(this.main.config.multiSelected);
        var values = document.createElement('div');
        values.classList.add(this.main.config.values);
        container.appendChild(values);
        var add = document.createElement('div');
        add.classList.add(this.main.config.add);
        var plus = document.createElement('span');
        plus.classList.add(this.main.config.plus);
        plus.onclick = function (e) {
            if (_this.main.data.contentOpen) {
                _this.main.close();
                e.stopPropagation();
            }
        };
        add.appendChild(plus);
        container.appendChild(add);
        container.onclick = function (e) {
            if (!_this.main.config.isEnabled) {
                return;
            }
            var target = e.target;
            if (!target.classList.contains(_this.main.config.valueDelete)) {
                _this.main.data.contentOpen ? _this.main.close() : _this.main.open();
            }
        };
        return {
            container: container,
            values: values,
            add: add,
            plus: plus
        };
    };
    Slim.prototype.values = function () {
        if (!this.multiSelected) {
            return;
        }
        var currentNodes = this.multiSelected.values.childNodes;
        var selected = this.main.data.getSelected();
        var exists;
        var nodesToRemove = [];
        for (var _i = 0, currentNodes_1 = currentNodes; _i < currentNodes_1.length; _i++) {
            var c = currentNodes_1[_i];
            exists = true;
            for (var _a = 0, selected_1 = selected; _a < selected_1.length; _a++) {
                var s = selected_1[_a];
                if (String(s.id) === String(c.dataset.id)) {
                    exists = false;
                }
            }
            if (exists) {
                nodesToRemove.push(c);
            }
        }
        for (var _b = 0, nodesToRemove_1 = nodesToRemove; _b < nodesToRemove_1.length; _b++) {
            var n = nodesToRemove_1[_b];
            n.classList.add('ss-out');
            this.multiSelected.values.removeChild(n);
        }
        currentNodes = this.multiSelected.values.childNodes;
        for (var s = 0; s < selected.length; s++) {
            exists = false;
            for (var _c = 0, currentNodes_2 = currentNodes; _c < currentNodes_2.length; _c++) {
                var c = currentNodes_2[_c];
                if (String(selected[s].id) === String(c.dataset.id)) {
                    exists = true;
                }
            }
            if (!exists) {
                if (currentNodes.length === 0 || !HTMLElement.prototype.insertAdjacentElement) {
                    this.multiSelected.values.appendChild(this.valueDiv(selected[s]));
                }
                else if (s === 0) {
                    this.multiSelected.values.insertBefore(this.valueDiv(selected[s]), currentNodes[s]);
                }
                else {
                    currentNodes[s - 1].insertAdjacentElement('afterend', this.valueDiv(selected[s]));
                }
            }
        }
        if (selected.length === 0) {
            var placeholder = document.createElement('span');
            placeholder.classList.add(this.main.config.disabled);
            placeholder.innerHTML = this.main.config.placeholderText;
            this.multiSelected.values.innerHTML = placeholder.outerHTML;
        }
    };
    Slim.prototype.valueDiv = function (optionObj) {
        var _this = this;
        var value = document.createElement('div');
        value.classList.add(this.main.config.value);
        value.dataset.id = optionObj.id;
        var text = document.createElement('span');
        text.classList.add(this.main.config.valueText);
        text.innerHTML = (optionObj.innerHTML && this.main.config.valuesUseText !== true ? optionObj.innerHTML : optionObj.text);
        value.appendChild(text);
        if (!optionObj.mandatory) {
            var deleteSpan = document.createElement('span');
            deleteSpan.classList.add(this.main.config.valueDelete);
            deleteSpan.innerHTML = this.main.config.deselectLabel;
            deleteSpan.onclick = function (e) {
                e.preventDefault();
                e.stopPropagation();
                var shouldUpdate = false;
                if (!_this.main.beforeOnChange) {
                    shouldUpdate = true;
                }
                if (_this.main.beforeOnChange) {
                    var selected = _this.main.data.getSelected();
                    var currentValues = JSON.parse(JSON.stringify(selected));
                    for (var i = 0; i < currentValues.length; i++) {
                        if (currentValues[i].id === optionObj.id) {
                            currentValues.splice(i, 1);
                        }
                    }
                    var beforeOnchange = _this.main.beforeOnChange(currentValues);
                    if (beforeOnchange !== false) {
                        shouldUpdate = true;
                    }
                }
                if (shouldUpdate) {
                    _this.main.data.removeFromSelected(optionObj.id, 'id');
                    _this.main.render();
                    _this.main.select.setValue();
                    _this.main.data.onDataChange();
                }
            };
            value.appendChild(deleteSpan);
        }
        return value;
    };
    Slim.prototype.contentDiv = function () {
        var container = document.createElement('div');
        container.classList.add(this.main.config.content);
        return container;
    };
    Slim.prototype.searchDiv = function () {
        var _this = this;
        var container = document.createElement('div');
        var input = document.createElement('input');
        var addable = document.createElement('div');
        container.classList.add(this.main.config.search);
        var searchReturn = {
            container: container,
            input: input
        };
        if (!this.main.config.showSearch) {
            container.classList.add(this.main.config.hide);
            input.readOnly = true;
        }
        input.type = 'search';
        input.placeholder = this.main.config.searchPlaceholder;
        input.tabIndex = 0;
        input.setAttribute('aria-label', this.main.config.searchPlaceholder);
        input.setAttribute('autocapitalize', 'off');
        input.setAttribute('autocomplete', 'off');
        input.setAttribute('autocorrect', 'off');
        input.onclick = function (e) {
            setTimeout(function () {
                var target = e.target;
                if (target.value === '') {
                    _this.main.search('');
                }
            }, 10);
        };
        input.onkeydown = function (e) {
            if (e.key === 'ArrowUp') {
                _this.main.open();
                _this.highlightUp();
                e.preventDefault();
            }
            else if (e.key === 'ArrowDown') {
                _this.main.open();
                _this.highlightDown();
                e.preventDefault();
            }
            else if (e.key === 'Tab') {
                if (!_this.main.data.contentOpen) {
                    setTimeout(function () { _this.main.close(); }, _this.main.config.timeoutDelay);
                }
                else {
                    _this.main.close();
                }
            }
            else if (e.key === 'Enter') {
                e.preventDefault();
            }
        };
        input.onkeyup = function (e) {
            var target = e.target;
            if (e.key === 'Enter') {
                if (_this.main.addable && e.ctrlKey) {
                    addable.click();
                    e.preventDefault();
                    e.stopPropagation();
                    return;
                }
                var highlighted = _this.list.querySelector('.' + _this.main.config.highlighted);
                if (highlighted) {
                    highlighted.click();
                }
            }
            else if (e.key === 'ArrowUp' || e.key === 'ArrowDown') {
            }
            else if (e.key === 'Escape') {
                _this.main.close();
            }
            else {
                if (_this.main.config.showSearch && _this.main.data.contentOpen) {
                    _this.main.search(target.value);
                }
                else {
                    input.value = '';
                }
            }
            e.preventDefault();
            e.stopPropagation();
        };
        input.onfocus = function () { _this.main.open(); };
        container.appendChild(input);
        if (this.main.addable) {
            addable.classList.add(this.main.config.addable);
            addable.innerHTML = '+';
            addable.onclick = function (e) {
                if (_this.main.addable) {
                    e.preventDefault();
                    e.stopPropagation();
                    var inputValue = _this.search.input.value;
                    if (inputValue.trim() === '') {
                        _this.search.input.focus();
                        return;
                    }
                    var addableValue = _this.main.addable(inputValue);
                    var addableValueStr_1 = '';
                    if (!addableValue) {
                        return;
                    }
                    if (typeof addableValue === 'object') {
                        var validValue = (0, data_1.validateOption)(addableValue);
                        if (validValue) {
                            _this.main.addData(addableValue);
                            addableValueStr_1 = (addableValue.value ? addableValue.value : addableValue.text);
                        }
                    }
                    else {
                        _this.main.addData(_this.main.data.newOption({
                            text: addableValue,
                            value: addableValue
                        }));
                        addableValueStr_1 = addableValue;
                    }
                    _this.main.search('');
                    setTimeout(function () {
                        _this.main.set(addableValueStr_1, 'value', false, false);
                    }, 100);
                    if (_this.main.config.closeOnSelect) {
                        setTimeout(function () {
                            _this.main.close();
                        }, 100);
                    }
                }
            };
            container.appendChild(addable);
            searchReturn.addable = addable;
        }
        return searchReturn;
    };
    Slim.prototype.highlightUp = function () {
        var highlighted = this.list.querySelector('.' + this.main.config.highlighted);
        var prev = null;
        if (highlighted) {
            prev = highlighted.previousSibling;
            while (prev !== null) {
                if (prev.classList.contains(this.main.config.disabled)) {
                    prev = prev.previousSibling;
                    continue;
                }
                else {
                    break;
                }
            }
        }
        else {
            var allOptions = this.list.querySelectorAll('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
            prev = allOptions[allOptions.length - 1];
        }
        if (prev && prev.classList.contains(this.main.config.optgroupLabel)) {
            prev = null;
        }
        if (prev === null) {
            var parent_1 = highlighted.parentNode;
            if (parent_1.classList.contains(this.main.config.optgroup)) {
                if (parent_1.previousSibling) {
                    var prevNodes = parent_1.previousSibling.querySelectorAll('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
                    if (prevNodes.length) {
                        prev = prevNodes[prevNodes.length - 1];
                    }
                }
            }
        }
        if (prev) {
            if (highlighted) {
                highlighted.classList.remove(this.main.config.highlighted);
            }
            prev.classList.add(this.main.config.highlighted);
            (0, helper_1.ensureElementInView)(this.list, prev);
        }
    };
    Slim.prototype.highlightDown = function () {
        var highlighted = this.list.querySelector('.' + this.main.config.highlighted);
        var next = null;
        if (highlighted) {
            next = highlighted.nextSibling;
            while (next !== null) {
                if (next.classList.contains(this.main.config.disabled)) {
                    next = next.nextSibling;
                    continue;
                }
                else {
                    break;
                }
            }
        }
        else {
            next = this.list.querySelector('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
        }
        if (next === null && highlighted !== null) {
            var parent_2 = highlighted.parentNode;
            if (parent_2.classList.contains(this.main.config.optgroup)) {
                if (parent_2.nextSibling) {
                    var sibling = parent_2.nextSibling;
                    next = sibling.querySelector('.' + this.main.config.option + ':not(.' + this.main.config.disabled + ')');
                }
            }
        }
        if (next) {
            if (highlighted) {
                highlighted.classList.remove(this.main.config.highlighted);
            }
            next.classList.add(this.main.config.highlighted);
            (0, helper_1.ensureElementInView)(this.list, next);
        }
    };
    Slim.prototype.listDiv = function () {
        var list = document.createElement('div');
        list.classList.add(this.main.config.list);
        list.setAttribute('role', 'listbox');
        return list;
    };
    Slim.prototype.options = function (content) {
        if (content === void 0) { content = ''; }
        var data = this.main.data.filtered || this.main.data.data;
        this.list.innerHTML = '';
        if (content !== '') {
            var searching = document.createElement('div');
            searching.classList.add(this.main.config.option);
            searching.classList.add(this.main.config.disabled);
            searching.innerHTML = content;
            this.list.appendChild(searching);
            return;
        }
        if (this.main.config.isAjax && this.main.config.isSearching) {
            var searching = document.createElement('div');
            searching.classList.add(this.main.config.option);
            searching.classList.add(this.main.config.disabled);
            searching.innerHTML = this.main.config.searchingText;
            this.list.appendChild(searching);
            return;
        }
        if (data.length === 0) {
            var noResults = document.createElement('div');
            noResults.classList.add(this.main.config.option);
            noResults.classList.add(this.main.config.disabled);
            noResults.innerHTML = this.main.config.searchText;
            this.list.appendChild(noResults);
            return;
        }
        var _loop_1 = function (d) {
            if (d.hasOwnProperty('label')) {
                var item = d;
                var optgroupEl_1 = document.createElement('div');
                optgroupEl_1.classList.add(this_1.main.config.optgroup);
                var optgroupLabel = document.createElement('div');
                optgroupLabel.classList.add(this_1.main.config.optgroupLabel);
                if (this_1.main.config.selectByGroup && this_1.main.config.isMultiple) {
                    optgroupLabel.classList.add(this_1.main.config.optgroupLabelSelectable);
                }
                optgroupLabel.innerHTML = item.label;
                optgroupEl_1.appendChild(optgroupLabel);
                var options = item.options;
                if (options) {
                    for (var _a = 0, options_1 = options; _a < options_1.length; _a++) {
                        var o = options_1[_a];
                        optgroupEl_1.appendChild(this_1.option(o));
                    }
                    if (this_1.main.config.selectByGroup && this_1.main.config.isMultiple) {
                        var master_1 = this_1;
                        optgroupLabel.addEventListener('click', function (e) {
                            e.preventDefault();
                            e.stopPropagation();
                            for (var _i = 0, _a = optgroupEl_1.children; _i < _a.length; _i++) {
                                var childEl = _a[_i];
                                if (childEl.className.indexOf(master_1.main.config.option) !== -1) {
                                    childEl.click();
                                }
                            }
                        });
                    }
                }
                this_1.list.appendChild(optgroupEl_1);
            }
            else {
                this_1.list.appendChild(this_1.option(d));
            }
        };
        var this_1 = this;
        for (var _i = 0, data_2 = data; _i < data_2.length; _i++) {
            var d = data_2[_i];
            _loop_1(d);
        }
    };
    Slim.prototype.option = function (data) {
        if (data.placeholder) {
            var placeholder = document.createElement('div');
            placeholder.classList.add(this.main.config.option);
            placeholder.classList.add(this.main.config.hide);
            return placeholder;
        }
        var optionEl = document.createElement('div');
        optionEl.classList.add(this.main.config.option);
        optionEl.setAttribute('role', 'option');
        if (data["class"]) {
            data["class"].split(' ').forEach(function (dataClass) {
                optionEl.classList.add(dataClass);
            });
        }
        if (data.style) {
            optionEl.style.cssText = data.style;
        }
        var selected = this.main.data.getSelected();
        optionEl.dataset.id = data.id;
        if (this.main.config.searchHighlight && this.main.slim && data.innerHTML && this.main.slim.search.input.value.trim() !== '') {
            optionEl.innerHTML = (0, helper_1.highlight)(data.innerHTML, this.main.slim.search.input.value, this.main.config.searchHighlighter);
        }
        else if (data.innerHTML) {
            optionEl.innerHTML = data.innerHTML;
        }
        if (this.main.config.showOptionTooltips && optionEl.textContent) {
            optionEl.setAttribute('title', optionEl.textContent);
        }
        var master = this;
        optionEl.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var element = this;
            var elementID = element.dataset.id;
            if (data.selected === true && master.main.config.allowDeselectOption) {
                var shouldUpdate = false;
                if (!master.main.beforeOnChange || !master.main.config.isMultiple) {
                    shouldUpdate = true;
                }
                if (master.main.beforeOnChange && master.main.config.isMultiple) {
                    var selectedValues = master.main.data.getSelected();
                    var currentValues = JSON.parse(JSON.stringify(selectedValues));
                    for (var i = 0; i < currentValues.length; i++) {
                        if (currentValues[i].id === elementID) {
                            currentValues.splice(i, 1);
                        }
                    }
                    var beforeOnchange = master.main.beforeOnChange(currentValues);
                    if (beforeOnchange !== false) {
                        shouldUpdate = true;
                    }
                }
                if (shouldUpdate) {
                    if (master.main.config.isMultiple) {
                        master.main.data.removeFromSelected(elementID, 'id');
                        master.main.render();
                        master.main.select.setValue();
                        master.main.data.onDataChange();
                    }
                    else {
                        master.main.set('');
                    }
                }
            }
            else {
                if (data.disabled || data.selected) {
                    return;
                }
                if (master.main.config.limit && Array.isArray(selected) && master.main.config.limit <= selected.length) {
                    return;
                }
                if (master.main.beforeOnChange) {
                    var value = void 0;
                    var objectInfo = JSON.parse(JSON.stringify(master.main.data.getObjectFromData(elementID)));
                    objectInfo.selected = true;
                    if (master.main.config.isMultiple) {
                        value = JSON.parse(JSON.stringify(selected));
                        value.push(objectInfo);
                    }
                    else {
                        value = JSON.parse(JSON.stringify(objectInfo));
                    }
                    var beforeOnchange = master.main.beforeOnChange(value);
                    if (beforeOnchange !== false) {
                        master.main.set(elementID, 'id', master.main.config.closeOnSelect);
                    }
                }
                else {
                    master.main.set(elementID, 'id', master.main.config.closeOnSelect);
                }
            }
        });
        var isSelected = selected && (0, helper_1.isValueInArrayOfObjects)(selected, 'id', data.id);
        if (data.disabled || isSelected) {
            optionEl.onclick = null;
            if (!master.main.config.allowDeselectOption) {
                optionEl.classList.add(this.main.config.disabled);
            }
            if (master.main.config.hideSelectedOption) {
                optionEl.classList.add(this.main.config.hide);
            }
        }
        if (isSelected) {
            optionEl.classList.add(this.main.config.optionSelected);
        }
        else {
            optionEl.classList.remove(this.main.config.optionSelected);
        }
        return optionEl;
    };
    return Slim;
}());
exports.Slim = Slim;


/***/ })
/******/ ])["default"];
});