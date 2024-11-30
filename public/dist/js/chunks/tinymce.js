(self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["tinymce"],{

/***/ "./node_modules/tinymce/plugins/advlist/index.js":
/*!*******************************************************!*\
  !*** ./node_modules/tinymce/plugins/advlist/index.js ***!
  \*******************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "advlist" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/advlist')
//   ES2015:
//     import 'tinymce/plugins/advlist'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/advlist/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/advlist/plugin.js":
/*!********************************************************!*\
  !*** ./node_modules/tinymce/plugins/advlist/plugin.js ***!
  \********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$1 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var applyListFormat = function (editor, listName, styleValue) {
      var cmd = listName === 'UL' ? 'InsertUnorderedList' : 'InsertOrderedList';
      editor.execCommand(cmd, false, styleValue === false ? null : { 'list-style-type': styleValue });
    };

    var register$1 = function (editor) {
      editor.addCommand('ApplyUnorderedListStyle', function (ui, value) {
        applyListFormat(editor, 'UL', value['list-style-type']);
      });
      editor.addCommand('ApplyOrderedListStyle', function (ui, value) {
        applyListFormat(editor, 'OL', value['list-style-type']);
      });
    };

    var global = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var getNumberStyles = function (editor) {
      var styles = editor.getParam('advlist_number_styles', 'default,lower-alpha,lower-greek,lower-roman,upper-alpha,upper-roman');
      return styles ? styles.split(/[ ,]/) : [];
    };
    var getBulletStyles = function (editor) {
      var styles = editor.getParam('advlist_bullet_styles', 'default,circle,square');
      return styles ? styles.split(/[ ,]/) : [];
    };

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var isChildOfBody = function (editor, elm) {
      return editor.$.contains(editor.getBody(), elm);
    };
    var isTableCellNode = function (node) {
      return node && /^(TH|TD)$/.test(node.nodeName);
    };
    var isListNode = function (editor) {
      return function (node) {
        return node && /^(OL|UL|DL)$/.test(node.nodeName) && isChildOfBody(editor, node);
      };
    };
    var getSelectedStyleType = function (editor) {
      var listElm = editor.dom.getParent(editor.selection.getNode(), 'ol,ul');
      var style = editor.dom.getStyle(listElm, 'listStyleType');
      return Optional.from(style);
    };

    var findIndex = function (list, predicate) {
      for (var index = 0; index < list.length; index++) {
        var element = list[index];
        if (predicate(element)) {
          return index;
        }
      }
      return -1;
    };
    var styleValueToText = function (styleValue) {
      return styleValue.replace(/\-/g, ' ').replace(/\b\w/g, function (chr) {
        return chr.toUpperCase();
      });
    };
    var isWithinList = function (editor, e, nodeName) {
      var tableCellIndex = findIndex(e.parents, isTableCellNode);
      var parents = tableCellIndex !== -1 ? e.parents.slice(0, tableCellIndex) : e.parents;
      var lists = global.grep(parents, isListNode(editor));
      return lists.length > 0 && lists[0].nodeName === nodeName;
    };
    var makeSetupHandler = function (editor, nodeName) {
      return function (api) {
        var nodeChangeHandler = function (e) {
          api.setActive(isWithinList(editor, e, nodeName));
        };
        editor.on('NodeChange', nodeChangeHandler);
        return function () {
          return editor.off('NodeChange', nodeChangeHandler);
        };
      };
    };
    var addSplitButton = function (editor, id, tooltip, cmd, nodeName, styles) {
      editor.ui.registry.addSplitButton(id, {
        tooltip: tooltip,
        icon: nodeName === 'OL' ? 'ordered-list' : 'unordered-list',
        presets: 'listpreview',
        columns: 3,
        fetch: function (callback) {
          var items = global.map(styles, function (styleValue) {
            var iconStyle = nodeName === 'OL' ? 'num' : 'bull';
            var iconName = styleValue === 'disc' || styleValue === 'decimal' ? 'default' : styleValue;
            var itemValue = styleValue === 'default' ? '' : styleValue;
            var displayText = styleValueToText(styleValue);
            return {
              type: 'choiceitem',
              value: itemValue,
              icon: 'list-' + iconStyle + '-' + iconName,
              text: displayText
            };
          });
          callback(items);
        },
        onAction: function () {
          return editor.execCommand(cmd);
        },
        onItemAction: function (_splitButtonApi, value) {
          applyListFormat(editor, nodeName, value);
        },
        select: function (value) {
          var listStyleType = getSelectedStyleType(editor);
          return listStyleType.map(function (listStyle) {
            return value === listStyle;
          }).getOr(false);
        },
        onSetup: makeSetupHandler(editor, nodeName)
      });
    };
    var addButton = function (editor, id, tooltip, cmd, nodeName, _styles) {
      editor.ui.registry.addToggleButton(id, {
        active: false,
        tooltip: tooltip,
        icon: nodeName === 'OL' ? 'ordered-list' : 'unordered-list',
        onSetup: makeSetupHandler(editor, nodeName),
        onAction: function () {
          return editor.execCommand(cmd);
        }
      });
    };
    var addControl = function (editor, id, tooltip, cmd, nodeName, styles) {
      if (styles.length > 1) {
        addSplitButton(editor, id, tooltip, cmd, nodeName, styles);
      } else {
        addButton(editor, id, tooltip, cmd, nodeName);
      }
    };
    var register = function (editor) {
      addControl(editor, 'numlist', 'Numbered list', 'InsertOrderedList', 'OL', getNumberStyles(editor));
      addControl(editor, 'bullist', 'Bullet list', 'InsertUnorderedList', 'UL', getBulletStyles(editor));
    };

    function Plugin () {
      global$1.add('advlist', function (editor) {
        if (editor.hasPlugin('lists')) {
          register(editor);
          register$1(editor);
        } else {
          console.error('Please use the Lists plugin together with the Advanced List plugin.');
        }
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/autolink/index.js":
/*!********************************************************!*\
  !*** ./node_modules/tinymce/plugins/autolink/index.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "autolink" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/autolink')
//   ES2015:
//     import 'tinymce/plugins/autolink'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/autolink/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/autolink/plugin.js":
/*!*********************************************************!*\
  !*** ./node_modules/tinymce/plugins/autolink/plugin.js ***!
  \*********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$1 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var checkRange = function (str, substr, start) {
      return substr === '' || str.length >= substr.length && str.substr(start, start + substr.length) === substr;
    };
    var contains = function (str, substr) {
      return str.indexOf(substr) !== -1;
    };
    var startsWith = function (str, prefix) {
      return checkRange(str, prefix, 0);
    };

    var global = tinymce.util.Tools.resolve('tinymce.Env');

    var link = function () {
      return /(?:[A-Za-z][A-Za-z\d.+-]{0,14}:\/\/(?:[-.~*+=!&;:'%@?^${}(),\w]+@)?|www\.|[-;:&=+$,.\w]+@)[A-Za-z\d-]+(?:\.[A-Za-z\d-]+)*(?::\d+)?(?:\/(?:[-.~*+=!;:'%@$(),\/\w]*[-~*+=%@$()\/\w])?)?(?:\?(?:[-.~*+=!&;:'%@?^${}(),\/\w]+))?(?:#(?:[-.~*+=!&;:'%@?^${}(),\/\w]+))?/g;
    };

    var defaultLinkPattern = new RegExp('^' + link().source + '$', 'i');
    var getAutoLinkPattern = function (editor) {
      return editor.getParam('autolink_pattern', defaultLinkPattern);
    };
    var getDefaultLinkTarget = function (editor) {
      return editor.getParam('default_link_target', false);
    };
    var getDefaultLinkProtocol = function (editor) {
      return editor.getParam('link_default_protocol', 'http', 'string');
    };

    var rangeEqualsBracketOrSpace = function (rangeString) {
      return /^[(\[{ \u00a0]$/.test(rangeString);
    };
    var isTextNode = function (node) {
      return node.nodeType === 3;
    };
    var isElement = function (node) {
      return node.nodeType === 1;
    };
    var handleBracket = function (editor) {
      return parseCurrentLine(editor, -1);
    };
    var handleSpacebar = function (editor) {
      return parseCurrentLine(editor, 0);
    };
    var handleEnter = function (editor) {
      return parseCurrentLine(editor, -1);
    };
    var scopeIndex = function (container, index) {
      if (index < 0) {
        index = 0;
      }
      if (isTextNode(container)) {
        var len = container.data.length;
        if (index > len) {
          index = len;
        }
      }
      return index;
    };
    var setStart = function (rng, container, offset) {
      if (!isElement(container) || container.hasChildNodes()) {
        rng.setStart(container, scopeIndex(container, offset));
      } else {
        rng.setStartBefore(container);
      }
    };
    var setEnd = function (rng, container, offset) {
      if (!isElement(container) || container.hasChildNodes()) {
        rng.setEnd(container, scopeIndex(container, offset));
      } else {
        rng.setEndAfter(container);
      }
    };
    var hasProtocol = function (url) {
      return /^([A-Za-z][A-Za-z\d.+-]*:\/\/)|mailto:/.test(url);
    };
    var isPunctuation = function (char) {
      return /[?!,.;:]/.test(char);
    };
    var parseCurrentLine = function (editor, endOffset) {
      var end, endContainer, bookmark, text, prev, len, rngText;
      var autoLinkPattern = getAutoLinkPattern(editor);
      var defaultLinkTarget = getDefaultLinkTarget(editor);
      if (editor.dom.getParent(editor.selection.getNode(), 'a[href]') !== null) {
        return;
      }
      var rng = editor.selection.getRng().cloneRange();
      if (rng.startOffset < 5) {
        prev = rng.endContainer.previousSibling;
        if (!prev) {
          if (!rng.endContainer.firstChild || !rng.endContainer.firstChild.nextSibling) {
            return;
          }
          prev = rng.endContainer.firstChild.nextSibling;
        }
        len = prev.length;
        setStart(rng, prev, len);
        setEnd(rng, prev, len);
        if (rng.endOffset < 5) {
          return;
        }
        end = rng.endOffset;
        endContainer = prev;
      } else {
        endContainer = rng.endContainer;
        if (!isTextNode(endContainer) && endContainer.firstChild) {
          while (!isTextNode(endContainer) && endContainer.firstChild) {
            endContainer = endContainer.firstChild;
          }
          if (isTextNode(endContainer)) {
            setStart(rng, endContainer, 0);
            setEnd(rng, endContainer, endContainer.nodeValue.length);
          }
        }
        if (rng.endOffset === 1) {
          end = 2;
        } else {
          end = rng.endOffset - 1 - endOffset;
        }
      }
      var start = end;
      do {
        setStart(rng, endContainer, end >= 2 ? end - 2 : 0);
        setEnd(rng, endContainer, end >= 1 ? end - 1 : 0);
        end -= 1;
        rngText = rng.toString();
      } while (!rangeEqualsBracketOrSpace(rngText) && end - 2 >= 0);
      if (rangeEqualsBracketOrSpace(rng.toString())) {
        setStart(rng, endContainer, end);
        setEnd(rng, endContainer, start);
        end += 1;
      } else if (rng.startOffset === 0) {
        setStart(rng, endContainer, 0);
        setEnd(rng, endContainer, start);
      } else {
        setStart(rng, endContainer, end);
        setEnd(rng, endContainer, start);
      }
      text = rng.toString();
      if (isPunctuation(text.charAt(text.length - 1))) {
        setEnd(rng, endContainer, start - 1);
      }
      text = rng.toString().trim();
      var matches = text.match(autoLinkPattern);
      var protocol = getDefaultLinkProtocol(editor);
      if (matches) {
        var url = matches[0];
        if (startsWith(url, 'www.')) {
          url = protocol + '://' + url;
        } else if (contains(url, '@') && !hasProtocol(url)) {
          url = 'mailto:' + url;
        }
        bookmark = editor.selection.getBookmark();
        editor.selection.setRng(rng);
        editor.execCommand('createlink', false, url);
        if (defaultLinkTarget !== false) {
          editor.dom.setAttrib(editor.selection.getNode(), 'target', defaultLinkTarget);
        }
        editor.selection.moveToBookmark(bookmark);
        editor.nodeChanged();
      }
    };
    var setup = function (editor) {
      var autoUrlDetectState;
      editor.on('keydown', function (e) {
        if (e.keyCode === 13) {
          return handleEnter(editor);
        }
      });
      if (global.browser.isIE()) {
        editor.on('focus', function () {
          if (!autoUrlDetectState) {
            autoUrlDetectState = true;
            try {
              editor.execCommand('AutoUrlDetect', false, true);
            } catch (ex) {
            }
          }
        });
        return;
      }
      editor.on('keypress', function (e) {
        if (e.keyCode === 41 || e.keyCode === 93 || e.keyCode === 125) {
          return handleBracket(editor);
        }
      });
      editor.on('keyup', function (e) {
        if (e.keyCode === 32) {
          return handleSpacebar(editor);
        }
      });
    };

    function Plugin () {
      global$1.add('autolink', function (editor) {
        setup(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/autoresize/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/tinymce/plugins/autoresize/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "autoresize" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/autoresize')
//   ES2015:
//     import 'tinymce/plugins/autoresize'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/autoresize/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/autoresize/plugin.js":
/*!***********************************************************!*\
  !*** ./node_modules/tinymce/plugins/autoresize/plugin.js ***!
  \***********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var hasOwnProperty = Object.hasOwnProperty;
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };

    var global$2 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var global$1 = tinymce.util.Tools.resolve('tinymce.Env');

    var global = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var fireResizeEditor = function (editor) {
      return editor.fire('ResizeEditor');
    };

    var getAutoResizeMinHeight = function (editor) {
      return editor.getParam('min_height', editor.getElement().offsetHeight, 'number');
    };
    var getAutoResizeMaxHeight = function (editor) {
      return editor.getParam('max_height', 0, 'number');
    };
    var getAutoResizeOverflowPadding = function (editor) {
      return editor.getParam('autoresize_overflow_padding', 1, 'number');
    };
    var getAutoResizeBottomMargin = function (editor) {
      return editor.getParam('autoresize_bottom_margin', 50, 'number');
    };
    var shouldAutoResizeOnInit = function (editor) {
      return editor.getParam('autoresize_on_init', true, 'boolean');
    };

    var isFullscreen = function (editor) {
      return editor.plugins.fullscreen && editor.plugins.fullscreen.isFullscreen();
    };
    var wait = function (editor, oldSize, times, interval, callback) {
      global.setEditorTimeout(editor, function () {
        resize(editor, oldSize);
        if (times--) {
          wait(editor, oldSize, times, interval, callback);
        } else if (callback) {
          callback();
        }
      }, interval);
    };
    var toggleScrolling = function (editor, state) {
      var body = editor.getBody();
      if (body) {
        body.style.overflowY = state ? '' : 'hidden';
        if (!state) {
          body.scrollTop = 0;
        }
      }
    };
    var parseCssValueToInt = function (dom, elm, name, computed) {
      var value = parseInt(dom.getStyle(elm, name, computed), 10);
      return isNaN(value) ? 0 : value;
    };
    var shouldScrollIntoView = function (trigger) {
      if ((trigger === null || trigger === void 0 ? void 0 : trigger.type.toLowerCase()) === 'setcontent') {
        var setContentEvent = trigger;
        return setContentEvent.selection === true || setContentEvent.paste === true;
      } else {
        return false;
      }
    };
    var resize = function (editor, oldSize, trigger) {
      var dom = editor.dom;
      var doc = editor.getDoc();
      if (!doc) {
        return;
      }
      if (isFullscreen(editor)) {
        toggleScrolling(editor, true);
        return;
      }
      var docEle = doc.documentElement;
      var resizeBottomMargin = getAutoResizeBottomMargin(editor);
      var resizeHeight = getAutoResizeMinHeight(editor);
      var marginTop = parseCssValueToInt(dom, docEle, 'margin-top', true);
      var marginBottom = parseCssValueToInt(dom, docEle, 'margin-bottom', true);
      var contentHeight = docEle.offsetHeight + marginTop + marginBottom + resizeBottomMargin;
      if (contentHeight < 0) {
        contentHeight = 0;
      }
      var containerHeight = editor.getContainer().offsetHeight;
      var contentAreaHeight = editor.getContentAreaContainer().offsetHeight;
      var chromeHeight = containerHeight - contentAreaHeight;
      if (contentHeight + chromeHeight > getAutoResizeMinHeight(editor)) {
        resizeHeight = contentHeight + chromeHeight;
      }
      var maxHeight = getAutoResizeMaxHeight(editor);
      if (maxHeight && resizeHeight > maxHeight) {
        resizeHeight = maxHeight;
        toggleScrolling(editor, true);
      } else {
        toggleScrolling(editor, false);
      }
      if (resizeHeight !== oldSize.get()) {
        var deltaSize = resizeHeight - oldSize.get();
        dom.setStyle(editor.getContainer(), 'height', resizeHeight + 'px');
        oldSize.set(resizeHeight);
        fireResizeEditor(editor);
        if (global$1.browser.isSafari() && global$1.mac) {
          var win = editor.getWin();
          win.scrollTo(win.pageXOffset, win.pageYOffset);
        }
        if (editor.hasFocus() && shouldScrollIntoView(trigger)) {
          editor.selection.scrollIntoView();
        }
        if (global$1.webkit && deltaSize < 0) {
          resize(editor, oldSize, trigger);
        }
      }
    };
    var setup = function (editor, oldSize) {
      editor.on('init', function () {
        var overflowPadding = getAutoResizeOverflowPadding(editor);
        var dom = editor.dom;
        dom.setStyles(editor.getDoc().documentElement, { height: 'auto' });
        dom.setStyles(editor.getBody(), {
          'paddingLeft': overflowPadding,
          'paddingRight': overflowPadding,
          'min-height': 0
        });
      });
      editor.on('NodeChange SetContent keyup FullscreenStateChanged ResizeContent', function (e) {
        resize(editor, oldSize, e);
      });
      if (shouldAutoResizeOnInit(editor)) {
        editor.on('init', function () {
          wait(editor, oldSize, 20, 100, function () {
            wait(editor, oldSize, 5, 1000);
          });
        });
      }
    };

    var register = function (editor, oldSize) {
      editor.addCommand('mceAutoResize', function () {
        resize(editor, oldSize);
      });
    };

    function Plugin () {
      global$2.add('autoresize', function (editor) {
        if (!has(editor.settings, 'resize')) {
          editor.settings.resize = false;
        }
        if (!editor.inline) {
          var oldSize = Cell(0);
          register(editor, oldSize);
          setup(editor, oldSize);
        }
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/autosave/index.js":
/*!********************************************************!*\
  !*** ./node_modules/tinymce/plugins/autosave/index.js ***!
  \********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "autosave" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/autosave')
//   ES2015:
//     import 'tinymce/plugins/autosave'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/autosave/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/autosave/plugin.js":
/*!*********************************************************!*\
  !*** ./node_modules/tinymce/plugins/autosave/plugin.js ***!
  \*********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$4 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var eq = function (t) {
      return function (a) {
        return t === a;
      };
    };
    var isUndefined = eq(undefined);

    var global$3 = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var global$2 = tinymce.util.Tools.resolve('tinymce.util.LocalStorage');

    var global$1 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var fireRestoreDraft = function (editor) {
      return editor.fire('RestoreDraft');
    };
    var fireStoreDraft = function (editor) {
      return editor.fire('StoreDraft');
    };
    var fireRemoveDraft = function (editor) {
      return editor.fire('RemoveDraft');
    };

    var parse = function (timeString, defaultTime) {
      var multiples = {
        s: 1000,
        m: 60000
      };
      var toParse = timeString || defaultTime;
      var parsedTime = /^(\d+)([ms]?)$/.exec('' + toParse);
      return (parsedTime[2] ? multiples[parsedTime[2]] : 1) * parseInt(toParse, 10);
    };

    var shouldAskBeforeUnload = function (editor) {
      return editor.getParam('autosave_ask_before_unload', true);
    };
    var getAutoSavePrefix = function (editor) {
      var location = document.location;
      return editor.getParam('autosave_prefix', 'tinymce-autosave-{path}{query}{hash}-{id}-').replace(/{path}/g, location.pathname).replace(/{query}/g, location.search).replace(/{hash}/g, location.hash).replace(/{id}/g, editor.id);
    };
    var shouldRestoreWhenEmpty = function (editor) {
      return editor.getParam('autosave_restore_when_empty', false);
    };
    var getAutoSaveInterval = function (editor) {
      return parse(editor.getParam('autosave_interval'), '30s');
    };
    var getAutoSaveRetention = function (editor) {
      return parse(editor.getParam('autosave_retention'), '20m');
    };

    var isEmpty = function (editor, html) {
      if (isUndefined(html)) {
        return editor.dom.isEmpty(editor.getBody());
      } else {
        var trimmedHtml = global$1.trim(html);
        if (trimmedHtml === '') {
          return true;
        } else {
          var fragment = new DOMParser().parseFromString(trimmedHtml, 'text/html');
          return editor.dom.isEmpty(fragment);
        }
      }
    };
    var hasDraft = function (editor) {
      var time = parseInt(global$2.getItem(getAutoSavePrefix(editor) + 'time'), 10) || 0;
      if (new Date().getTime() - time > getAutoSaveRetention(editor)) {
        removeDraft(editor, false);
        return false;
      }
      return true;
    };
    var removeDraft = function (editor, fire) {
      var prefix = getAutoSavePrefix(editor);
      global$2.removeItem(prefix + 'draft');
      global$2.removeItem(prefix + 'time');
      if (fire !== false) {
        fireRemoveDraft(editor);
      }
    };
    var storeDraft = function (editor) {
      var prefix = getAutoSavePrefix(editor);
      if (!isEmpty(editor) && editor.isDirty()) {
        global$2.setItem(prefix + 'draft', editor.getContent({
          format: 'raw',
          no_events: true
        }));
        global$2.setItem(prefix + 'time', new Date().getTime().toString());
        fireStoreDraft(editor);
      }
    };
    var restoreDraft = function (editor) {
      var prefix = getAutoSavePrefix(editor);
      if (hasDraft(editor)) {
        editor.setContent(global$2.getItem(prefix + 'draft'), { format: 'raw' });
        fireRestoreDraft(editor);
      }
    };
    var startStoreDraft = function (editor) {
      var interval = getAutoSaveInterval(editor);
      global$3.setEditorInterval(editor, function () {
        storeDraft(editor);
      }, interval);
    };
    var restoreLastDraft = function (editor) {
      editor.undoManager.transact(function () {
        restoreDraft(editor);
        removeDraft(editor);
      });
      editor.focus();
    };

    var get = function (editor) {
      return {
        hasDraft: function () {
          return hasDraft(editor);
        },
        storeDraft: function () {
          return storeDraft(editor);
        },
        restoreDraft: function () {
          return restoreDraft(editor);
        },
        removeDraft: function (fire) {
          return removeDraft(editor, fire);
        },
        isEmpty: function (html) {
          return isEmpty(editor, html);
        }
      };
    };

    var global = tinymce.util.Tools.resolve('tinymce.EditorManager');

    var setup = function (editor) {
      editor.editorManager.on('BeforeUnload', function (e) {
        var msg;
        global$1.each(global.get(), function (editor) {
          if (editor.plugins.autosave) {
            editor.plugins.autosave.storeDraft();
          }
          if (!msg && editor.isDirty() && shouldAskBeforeUnload(editor)) {
            msg = editor.translate('You have unsaved changes are you sure you want to navigate away?');
          }
        });
        if (msg) {
          e.preventDefault();
          e.returnValue = msg;
        }
      });
    };

    var makeSetupHandler = function (editor) {
      return function (api) {
        api.setDisabled(!hasDraft(editor));
        var editorEventCallback = function () {
          return api.setDisabled(!hasDraft(editor));
        };
        editor.on('StoreDraft RestoreDraft RemoveDraft', editorEventCallback);
        return function () {
          return editor.off('StoreDraft RestoreDraft RemoveDraft', editorEventCallback);
        };
      };
    };
    var register = function (editor) {
      startStoreDraft(editor);
      editor.ui.registry.addButton('restoredraft', {
        tooltip: 'Restore last draft',
        icon: 'restore-draft',
        onAction: function () {
          restoreLastDraft(editor);
        },
        onSetup: makeSetupHandler(editor)
      });
      editor.ui.registry.addMenuItem('restoredraft', {
        text: 'Restore last draft',
        icon: 'restore-draft',
        onAction: function () {
          restoreLastDraft(editor);
        },
        onSetup: makeSetupHandler(editor)
      });
    };

    function Plugin () {
      global$4.add('autosave', function (editor) {
        setup(editor);
        register(editor);
        editor.on('init', function () {
          if (shouldRestoreWhenEmpty(editor) && editor.dom.isEmpty(editor.getBody())) {
            restoreDraft(editor);
          }
        });
        return get(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/codesample/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/tinymce/plugins/codesample/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "codesample" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/codesample')
//   ES2015:
//     import 'tinymce/plugins/codesample'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/codesample/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/codesample/plugin.js":
/*!***********************************************************!*\
  !*** ./node_modules/tinymce/plugins/codesample/plugin.js ***!
  \***********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$2 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var get$1 = function (xs, i) {
      return i >= 0 && i < xs.length ? Optional.some(xs[i]) : Optional.none();
    };
    var head = function (xs) {
      return get$1(xs, 0);
    };

    var someIf = function (b, a) {
      return b ? Optional.some(a) : Optional.none();
    };

    var global$1 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

    var isCodeSample = function (elm) {
      return elm && elm.nodeName === 'PRE' && elm.className.indexOf('language-') !== -1;
    };
    var trimArg = function (predicateFn) {
      return function (arg1, arg2) {
        return predicateFn(arg2);
      };
    };

    var Global = typeof window !== 'undefined' ? window : Function('return this;')();

    var exports$1 = {}, module = { exports: exports$1 }, global = {};
    (function (define, exports, module, require) {
      var oldprism = window.Prism;
      window.Prism = { manual: true };
      (function (global, factory) {
        typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() : typeof define === 'function' && define.amd ? define(factory) : (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.EphoxContactWrapper = factory());
      }(this, function () {
        var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof global !== 'undefined' ? global : typeof self !== 'undefined' ? self : {};
        var prismCore = { exports: {} };
        (function (module) {
          var _self = typeof window !== 'undefined' ? window : typeof WorkerGlobalScope !== 'undefined' && self instanceof WorkerGlobalScope ? self : {};
          var Prism = function (_self) {
            var lang = /(?:^|\s)lang(?:uage)?-([\w-]+)(?=\s|$)/i;
            var uniqueId = 0;
            var plainTextGrammar = {};
            var _ = {
              manual: _self.Prism && _self.Prism.manual,
              disableWorkerMessageHandler: _self.Prism && _self.Prism.disableWorkerMessageHandler,
              util: {
                encode: function encode(tokens) {
                  if (tokens instanceof Token) {
                    return new Token(tokens.type, encode(tokens.content), tokens.alias);
                  } else if (Array.isArray(tokens)) {
                    return tokens.map(encode);
                  } else {
                    return tokens.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/\u00a0/g, ' ');
                  }
                },
                type: function (o) {
                  return Object.prototype.toString.call(o).slice(8, -1);
                },
                objId: function (obj) {
                  if (!obj['__id']) {
                    Object.defineProperty(obj, '__id', { value: ++uniqueId });
                  }
                  return obj['__id'];
                },
                clone: function deepClone(o, visited) {
                  visited = visited || {};
                  var clone;
                  var id;
                  switch (_.util.type(o)) {
                  case 'Object':
                    id = _.util.objId(o);
                    if (visited[id]) {
                      return visited[id];
                    }
                    clone = {};
                    visited[id] = clone;
                    for (var key in o) {
                      if (o.hasOwnProperty(key)) {
                        clone[key] = deepClone(o[key], visited);
                      }
                    }
                    return clone;
                  case 'Array':
                    id = _.util.objId(o);
                    if (visited[id]) {
                      return visited[id];
                    }
                    clone = [];
                    visited[id] = clone;
                    o.forEach(function (v, i) {
                      clone[i] = deepClone(v, visited);
                    });
                    return clone;
                  default:
                    return o;
                  }
                },
                getLanguage: function (element) {
                  while (element) {
                    var m = lang.exec(element.className);
                    if (m) {
                      return m[1].toLowerCase();
                    }
                    element = element.parentElement;
                  }
                  return 'none';
                },
                setLanguage: function (element, language) {
                  element.className = element.className.replace(RegExp(lang.source, 'gi'), '');
                  element.classList.add('language-' + language);
                },
                currentScript: function () {
                  if (typeof document === 'undefined') {
                    return null;
                  }
                  if ('currentScript' in document && 1 < 2) {
                    return document.currentScript;
                  }
                  try {
                    throw new Error();
                  } catch (err) {
                    var src = (/at [^(\r\n]*\((.*):[^:]+:[^:]+\)$/i.exec(err.stack) || [])[1];
                    if (src) {
                      var scripts = document.getElementsByTagName('script');
                      for (var i in scripts) {
                        if (scripts[i].src == src) {
                          return scripts[i];
                        }
                      }
                    }
                    return null;
                  }
                },
                isActive: function (element, className, defaultActivation) {
                  var no = 'no-' + className;
                  while (element) {
                    var classList = element.classList;
                    if (classList.contains(className)) {
                      return true;
                    }
                    if (classList.contains(no)) {
                      return false;
                    }
                    element = element.parentElement;
                  }
                  return !!defaultActivation;
                }
              },
              languages: {
                plain: plainTextGrammar,
                plaintext: plainTextGrammar,
                text: plainTextGrammar,
                txt: plainTextGrammar,
                extend: function (id, redef) {
                  var lang = _.util.clone(_.languages[id]);
                  for (var key in redef) {
                    lang[key] = redef[key];
                  }
                  return lang;
                },
                insertBefore: function (inside, before, insert, root) {
                  root = root || _.languages;
                  var grammar = root[inside];
                  var ret = {};
                  for (var token in grammar) {
                    if (grammar.hasOwnProperty(token)) {
                      if (token == before) {
                        for (var newToken in insert) {
                          if (insert.hasOwnProperty(newToken)) {
                            ret[newToken] = insert[newToken];
                          }
                        }
                      }
                      if (!insert.hasOwnProperty(token)) {
                        ret[token] = grammar[token];
                      }
                    }
                  }
                  var old = root[inside];
                  root[inside] = ret;
                  _.languages.DFS(_.languages, function (key, value) {
                    if (value === old && key != inside) {
                      this[key] = ret;
                    }
                  });
                  return ret;
                },
                DFS: function DFS(o, callback, type, visited) {
                  visited = visited || {};
                  var objId = _.util.objId;
                  for (var i in o) {
                    if (o.hasOwnProperty(i)) {
                      callback.call(o, i, o[i], type || i);
                      var property = o[i];
                      var propertyType = _.util.type(property);
                      if (propertyType === 'Object' && !visited[objId(property)]) {
                        visited[objId(property)] = true;
                        DFS(property, callback, null, visited);
                      } else if (propertyType === 'Array' && !visited[objId(property)]) {
                        visited[objId(property)] = true;
                        DFS(property, callback, i, visited);
                      }
                    }
                  }
                }
              },
              plugins: {},
              highlightAll: function (async, callback) {
                _.highlightAllUnder(document, async, callback);
              },
              highlightAllUnder: function (container, async, callback) {
                var env = {
                  callback: callback,
                  container: container,
                  selector: 'code[class*="language-"], [class*="language-"] code, code[class*="lang-"], [class*="lang-"] code'
                };
                _.hooks.run('before-highlightall', env);
                env.elements = Array.prototype.slice.apply(env.container.querySelectorAll(env.selector));
                _.hooks.run('before-all-elements-highlight', env);
                for (var i = 0, element; element = env.elements[i++];) {
                  _.highlightElement(element, async === true, env.callback);
                }
              },
              highlightElement: function (element, async, callback) {
                var language = _.util.getLanguage(element);
                var grammar = _.languages[language];
                _.util.setLanguage(element, language);
                var parent = element.parentElement;
                if (parent && parent.nodeName.toLowerCase() === 'pre') {
                  _.util.setLanguage(parent, language);
                }
                var code = element.textContent;
                var env = {
                  element: element,
                  language: language,
                  grammar: grammar,
                  code: code
                };
                function insertHighlightedCode(highlightedCode) {
                  env.highlightedCode = highlightedCode;
                  _.hooks.run('before-insert', env);
                  env.element.innerHTML = env.highlightedCode;
                  _.hooks.run('after-highlight', env);
                  _.hooks.run('complete', env);
                  callback && callback.call(env.element);
                }
                _.hooks.run('before-sanity-check', env);
                parent = env.element.parentElement;
                if (parent && parent.nodeName.toLowerCase() === 'pre' && !parent.hasAttribute('tabindex')) {
                  parent.setAttribute('tabindex', '0');
                }
                if (!env.code) {
                  _.hooks.run('complete', env);
                  callback && callback.call(env.element);
                  return;
                }
                _.hooks.run('before-highlight', env);
                if (!env.grammar) {
                  insertHighlightedCode(_.util.encode(env.code));
                  return;
                }
                if (async && _self.Worker) {
                  var worker = new Worker(_.filename);
                  worker.onmessage = function (evt) {
                    insertHighlightedCode(evt.data);
                  };
                  worker.postMessage(JSON.stringify({
                    language: env.language,
                    code: env.code,
                    immediateClose: true
                  }));
                } else {
                  insertHighlightedCode(_.highlight(env.code, env.grammar, env.language));
                }
              },
              highlight: function (text, grammar, language) {
                var env = {
                  code: text,
                  grammar: grammar,
                  language: language
                };
                _.hooks.run('before-tokenize', env);
                if (!env.grammar) {
                  throw new Error('The language "' + env.language + '" has no grammar.');
                }
                env.tokens = _.tokenize(env.code, env.grammar);
                _.hooks.run('after-tokenize', env);
                return Token.stringify(_.util.encode(env.tokens), env.language);
              },
              tokenize: function (text, grammar) {
                var rest = grammar.rest;
                if (rest) {
                  for (var token in rest) {
                    grammar[token] = rest[token];
                  }
                  delete grammar.rest;
                }
                var tokenList = new LinkedList();
                addAfter(tokenList, tokenList.head, text);
                matchGrammar(text, tokenList, grammar, tokenList.head, 0);
                return toArray(tokenList);
              },
              hooks: {
                all: {},
                add: function (name, callback) {
                  var hooks = _.hooks.all;
                  hooks[name] = hooks[name] || [];
                  hooks[name].push(callback);
                },
                run: function (name, env) {
                  var callbacks = _.hooks.all[name];
                  if (!callbacks || !callbacks.length) {
                    return;
                  }
                  for (var i = 0, callback; callback = callbacks[i++];) {
                    callback(env);
                  }
                }
              },
              Token: Token
            };
            _self.Prism = _;
            function Token(type, content, alias, matchedStr) {
              this.type = type;
              this.content = content;
              this.alias = alias;
              this.length = (matchedStr || '').length | 0;
            }
            Token.stringify = function stringify(o, language) {
              if (typeof o == 'string') {
                return o;
              }
              if (Array.isArray(o)) {
                var s = '';
                o.forEach(function (e) {
                  s += stringify(e, language);
                });
                return s;
              }
              var env = {
                type: o.type,
                content: stringify(o.content, language),
                tag: 'span',
                classes: [
                  'token',
                  o.type
                ],
                attributes: {},
                language: language
              };
              var aliases = o.alias;
              if (aliases) {
                if (Array.isArray(aliases)) {
                  Array.prototype.push.apply(env.classes, aliases);
                } else {
                  env.classes.push(aliases);
                }
              }
              _.hooks.run('wrap', env);
              var attributes = '';
              for (var name in env.attributes) {
                attributes += ' ' + name + '="' + (env.attributes[name] || '').replace(/"/g, '&quot;') + '"';
              }
              return '<' + env.tag + ' class="' + env.classes.join(' ') + '"' + attributes + '>' + env.content + '</' + env.tag + '>';
            };
            function matchPattern(pattern, pos, text, lookbehind) {
              pattern.lastIndex = pos;
              var match = pattern.exec(text);
              if (match && lookbehind && match[1]) {
                var lookbehindLength = match[1].length;
                match.index += lookbehindLength;
                match[0] = match[0].slice(lookbehindLength);
              }
              return match;
            }
            function matchGrammar(text, tokenList, grammar, startNode, startPos, rematch) {
              for (var token in grammar) {
                if (!grammar.hasOwnProperty(token) || !grammar[token]) {
                  continue;
                }
                var patterns = grammar[token];
                patterns = Array.isArray(patterns) ? patterns : [patterns];
                for (var j = 0; j < patterns.length; ++j) {
                  if (rematch && rematch.cause == token + ',' + j) {
                    return;
                  }
                  var patternObj = patterns[j];
                  var inside = patternObj.inside;
                  var lookbehind = !!patternObj.lookbehind;
                  var greedy = !!patternObj.greedy;
                  var alias = patternObj.alias;
                  if (greedy && !patternObj.pattern.global) {
                    var flags = patternObj.pattern.toString().match(/[imsuy]*$/)[0];
                    patternObj.pattern = RegExp(patternObj.pattern.source, flags + 'g');
                  }
                  var pattern = patternObj.pattern || patternObj;
                  for (var currentNode = startNode.next, pos = startPos; currentNode !== tokenList.tail; pos += currentNode.value.length, currentNode = currentNode.next) {
                    if (rematch && pos >= rematch.reach) {
                      break;
                    }
                    var str = currentNode.value;
                    if (tokenList.length > text.length) {
                      return;
                    }
                    if (str instanceof Token) {
                      continue;
                    }
                    var removeCount = 1;
                    var match;
                    if (greedy) {
                      match = matchPattern(pattern, pos, text, lookbehind);
                      if (!match || match.index >= text.length) {
                        break;
                      }
                      var from = match.index;
                      var to = match.index + match[0].length;
                      var p = pos;
                      p += currentNode.value.length;
                      while (from >= p) {
                        currentNode = currentNode.next;
                        p += currentNode.value.length;
                      }
                      p -= currentNode.value.length;
                      pos = p;
                      if (currentNode.value instanceof Token) {
                        continue;
                      }
                      for (var k = currentNode; k !== tokenList.tail && (p < to || typeof k.value === 'string'); k = k.next) {
                        removeCount++;
                        p += k.value.length;
                      }
                      removeCount--;
                      str = text.slice(pos, p);
                      match.index -= pos;
                    } else {
                      match = matchPattern(pattern, 0, str, lookbehind);
                      if (!match) {
                        continue;
                      }
                    }
                    var from = match.index;
                    var matchStr = match[0];
                    var before = str.slice(0, from);
                    var after = str.slice(from + matchStr.length);
                    var reach = pos + str.length;
                    if (rematch && reach > rematch.reach) {
                      rematch.reach = reach;
                    }
                    var removeFrom = currentNode.prev;
                    if (before) {
                      removeFrom = addAfter(tokenList, removeFrom, before);
                      pos += before.length;
                    }
                    removeRange(tokenList, removeFrom, removeCount);
                    var wrapped = new Token(token, inside ? _.tokenize(matchStr, inside) : matchStr, alias, matchStr);
                    currentNode = addAfter(tokenList, removeFrom, wrapped);
                    if (after) {
                      addAfter(tokenList, currentNode, after);
                    }
                    if (removeCount > 1) {
                      var nestedRematch = {
                        cause: token + ',' + j,
                        reach: reach
                      };
                      matchGrammar(text, tokenList, grammar, currentNode.prev, pos, nestedRematch);
                      if (rematch && nestedRematch.reach > rematch.reach) {
                        rematch.reach = nestedRematch.reach;
                      }
                    }
                  }
                }
              }
            }
            function LinkedList() {
              var head = {
                value: null,
                prev: null,
                next: null
              };
              var tail = {
                value: null,
                prev: head,
                next: null
              };
              head.next = tail;
              this.head = head;
              this.tail = tail;
              this.length = 0;
            }
            function addAfter(list, node, value) {
              var next = node.next;
              var newNode = {
                value: value,
                prev: node,
                next: next
              };
              node.next = newNode;
              next.prev = newNode;
              list.length++;
              return newNode;
            }
            function removeRange(list, node, count) {
              var next = node.next;
              for (var i = 0; i < count && next !== list.tail; i++) {
                next = next.next;
              }
              node.next = next;
              next.prev = node;
              list.length -= i;
            }
            function toArray(list) {
              var array = [];
              var node = list.head.next;
              while (node !== list.tail) {
                array.push(node.value);
                node = node.next;
              }
              return array;
            }
            if (!_self.document) {
              if (!_self.addEventListener) {
                return _;
              }
              if (!_.disableWorkerMessageHandler) {
                _self.addEventListener('message', function (evt) {
                  var message = JSON.parse(evt.data);
                  var lang = message.language;
                  var code = message.code;
                  var immediateClose = message.immediateClose;
                  _self.postMessage(_.highlight(code, _.languages[lang], lang));
                  if (immediateClose) {
                    _self.close();
                  }
                }, false);
              }
              return _;
            }
            var script = _.util.currentScript();
            if (script) {
              _.filename = script.src;
              if (script.hasAttribute('data-manual')) {
                _.manual = true;
              }
            }
            function highlightAutomaticallyCallback() {
              if (!_.manual) {
                _.highlightAll();
              }
            }
            if (!_.manual) {
              var readyState = document.readyState;
              if (readyState === 'loading' || readyState === 'interactive' && script && script.defer) {
                document.addEventListener('DOMContentLoaded', highlightAutomaticallyCallback);
              } else {
                if (window.requestAnimationFrame) {
                  window.requestAnimationFrame(highlightAutomaticallyCallback);
                } else {
                  window.setTimeout(highlightAutomaticallyCallback, 16);
                }
              }
            }
            return _;
          }(_self);
          if (module.exports) {
            module.exports = Prism;
          }
          if (typeof commonjsGlobal !== 'undefined') {
            commonjsGlobal.Prism = Prism;
          }
        }(prismCore));
        Prism.languages.clike = {
          'comment': [
            {
              pattern: /(^|[^\\])\/\*[\s\S]*?(?:\*\/|$)/,
              lookbehind: true,
              greedy: true
            },
            {
              pattern: /(^|[^\\:])\/\/.*/,
              lookbehind: true,
              greedy: true
            }
          ],
          'string': {
            pattern: /(["'])(?:\\(?:\r\n|[\s\S])|(?!\1)[^\\\r\n])*\1/,
            greedy: true
          },
          'class-name': {
            pattern: /(\b(?:class|extends|implements|instanceof|interface|new|trait)\s+|\bcatch\s+\()[\w.\\]+/i,
            lookbehind: true,
            inside: { 'punctuation': /[.\\]/ }
          },
          'keyword': /\b(?:break|catch|continue|do|else|finally|for|function|if|in|instanceof|new|null|return|throw|try|while)\b/,
          'boolean': /\b(?:false|true)\b/,
          'function': /\b\w+(?=\()/,
          'number': /\b0x[\da-f]+\b|(?:\b\d+(?:\.\d*)?|\B\.\d+)(?:e[+-]?\d+)?/i,
          'operator': /[<>]=?|[!=]=?=?|--?|\+\+?|&&?|\|\|?|[?*/~^%]/,
          'punctuation': /[{}[\];(),.:]/
        };
        (function (Prism) {
          function getPlaceholder(language, index) {
            return '___' + language.toUpperCase() + index + '___';
          }
          Object.defineProperties(Prism.languages['markup-templating'] = {}, {
            buildPlaceholders: {
              value: function (env, language, placeholderPattern, replaceFilter) {
                if (env.language !== language) {
                  return;
                }
                var tokenStack = env.tokenStack = [];
                env.code = env.code.replace(placeholderPattern, function (match) {
                  if (typeof replaceFilter === 'function' && !replaceFilter(match)) {
                    return match;
                  }
                  var i = tokenStack.length;
                  var placeholder;
                  while (env.code.indexOf(placeholder = getPlaceholder(language, i)) !== -1) {
                    ++i;
                  }
                  tokenStack[i] = match;
                  return placeholder;
                });
                env.grammar = Prism.languages.markup;
              }
            },
            tokenizePlaceholders: {
              value: function (env, language) {
                if (env.language !== language || !env.tokenStack) {
                  return;
                }
                env.grammar = Prism.languages[language];
                var j = 0;
                var keys = Object.keys(env.tokenStack);
                function walkTokens(tokens) {
                  for (var i = 0; i < tokens.length; i++) {
                    if (j >= keys.length) {
                      break;
                    }
                    var token = tokens[i];
                    if (typeof token === 'string' || token.content && typeof token.content === 'string') {
                      var k = keys[j];
                      var t = env.tokenStack[k];
                      var s = typeof token === 'string' ? token : token.content;
                      var placeholder = getPlaceholder(language, k);
                      var index = s.indexOf(placeholder);
                      if (index > -1) {
                        ++j;
                        var before = s.substring(0, index);
                        var middle = new Prism.Token(language, Prism.tokenize(t, env.grammar), 'language-' + language, t);
                        var after = s.substring(index + placeholder.length);
                        var replacement = [];
                        if (before) {
                          replacement.push.apply(replacement, walkTokens([before]));
                        }
                        replacement.push(middle);
                        if (after) {
                          replacement.push.apply(replacement, walkTokens([after]));
                        }
                        if (typeof token === 'string') {
                          tokens.splice.apply(tokens, [
                            i,
                            1
                          ].concat(replacement));
                        } else {
                          token.content = replacement;
                        }
                      }
                    } else if (token.content) {
                      walkTokens(token.content);
                    }
                  }
                  return tokens;
                }
                walkTokens(env.tokens);
              }
            }
          });
        }(Prism));
        Prism.languages.c = Prism.languages.extend('clike', {
          'comment': {
            pattern: /\/\/(?:[^\r\n\\]|\\(?:\r\n?|\n|(?![\r\n])))*|\/\*[\s\S]*?(?:\*\/|$)/,
            greedy: true
          },
          'string': {
            pattern: /"(?:\\(?:\r\n|[\s\S])|[^"\\\r\n])*"/,
            greedy: true
          },
          'class-name': {
            pattern: /(\b(?:enum|struct)\s+(?:__attribute__\s*\(\([\s\S]*?\)\)\s*)?)\w+|\b[a-z]\w*_t\b/,
            lookbehind: true
          },
          'keyword': /\b(?:_Alignas|_Alignof|_Atomic|_Bool|_Complex|_Generic|_Imaginary|_Noreturn|_Static_assert|_Thread_local|__attribute__|asm|auto|break|case|char|const|continue|default|do|double|else|enum|extern|float|for|goto|if|inline|int|long|register|return|short|signed|sizeof|static|struct|switch|typedef|typeof|union|unsigned|void|volatile|while)\b/,
          'function': /\b[a-z_]\w*(?=\s*\()/i,
          'number': /(?:\b0x(?:[\da-f]+(?:\.[\da-f]*)?|\.[\da-f]+)(?:p[+-]?\d+)?|(?:\b\d+(?:\.\d*)?|\B\.\d+)(?:e[+-]?\d+)?)[ful]{0,4}/i,
          'operator': />>=?|<<=?|->|([-+&|:])\1|[?:~]|[-+*/%&|^!=<>]=?/
        });
        Prism.languages.insertBefore('c', 'string', {
          'char': {
            pattern: /'(?:\\(?:\r\n|[\s\S])|[^'\\\r\n]){0,32}'/,
            greedy: true
          }
        });
        Prism.languages.insertBefore('c', 'string', {
          'macro': {
            pattern: /(^[\t ]*)#\s*[a-z](?:[^\r\n\\/]|\/(?!\*)|\/\*(?:[^*]|\*(?!\/))*\*\/|\\(?:\r\n|[\s\S]))*/im,
            lookbehind: true,
            greedy: true,
            alias: 'property',
            inside: {
              'string': [
                {
                  pattern: /^(#\s*include\s*)<[^>]+>/,
                  lookbehind: true
                },
                Prism.languages.c['string']
              ],
              'char': Prism.languages.c['char'],
              'comment': Prism.languages.c['comment'],
              'macro-name': [
                {
                  pattern: /(^#\s*define\s+)\w+\b(?!\()/i,
                  lookbehind: true
                },
                {
                  pattern: /(^#\s*define\s+)\w+\b(?=\()/i,
                  lookbehind: true,
                  alias: 'function'
                }
              ],
              'directive': {
                pattern: /^(#\s*)[a-z]+/,
                lookbehind: true,
                alias: 'keyword'
              },
              'directive-hash': /^#/,
              'punctuation': /##|\\(?=[\r\n])/,
              'expression': {
                pattern: /\S[\s\S]*/,
                inside: Prism.languages.c
              }
            }
          }
        });
        Prism.languages.insertBefore('c', 'function', { 'constant': /\b(?:EOF|NULL|SEEK_CUR|SEEK_END|SEEK_SET|__DATE__|__FILE__|__LINE__|__TIMESTAMP__|__TIME__|__func__|stderr|stdin|stdout)\b/ });
        delete Prism.languages.c['boolean'];
        (function (Prism) {
          var keyword = /\b(?:alignas|alignof|asm|auto|bool|break|case|catch|char|char16_t|char32_t|char8_t|class|co_await|co_return|co_yield|compl|concept|const|const_cast|consteval|constexpr|constinit|continue|decltype|default|delete|do|double|dynamic_cast|else|enum|explicit|export|extern|final|float|for|friend|goto|if|import|inline|int|int16_t|int32_t|int64_t|int8_t|long|module|mutable|namespace|new|noexcept|nullptr|operator|override|private|protected|public|register|reinterpret_cast|requires|return|short|signed|sizeof|static|static_assert|static_cast|struct|switch|template|this|thread_local|throw|try|typedef|typeid|typename|uint16_t|uint32_t|uint64_t|uint8_t|union|unsigned|using|virtual|void|volatile|wchar_t|while)\b/;
          var modName = /\b(?!<keyword>)\w+(?:\s*\.\s*\w+)*\b/.source.replace(/<keyword>/g, function () {
            return keyword.source;
          });
          Prism.languages.cpp = Prism.languages.extend('c', {
            'class-name': [
              {
                pattern: RegExp(/(\b(?:class|concept|enum|struct|typename)\s+)(?!<keyword>)\w+/.source.replace(/<keyword>/g, function () {
                  return keyword.source;
                })),
                lookbehind: true
              },
              /\b[A-Z]\w*(?=\s*::\s*\w+\s*\()/,
              /\b[A-Z_]\w*(?=\s*::\s*~\w+\s*\()/i,
              /\b\w+(?=\s*<(?:[^<>]|<(?:[^<>]|<[^<>]*>)*>)*>\s*::\s*\w+\s*\()/
            ],
            'keyword': keyword,
            'number': {
              pattern: /(?:\b0b[01']+|\b0x(?:[\da-f']+(?:\.[\da-f']*)?|\.[\da-f']+)(?:p[+-]?[\d']+)?|(?:\b[\d']+(?:\.[\d']*)?|\B\.[\d']+)(?:e[+-]?[\d']+)?)[ful]{0,4}/i,
              greedy: true
            },
            'operator': />>=?|<<=?|->|--|\+\+|&&|\|\||[?:~]|<=>|[-+*/%&|^!=<>]=?|\b(?:and|and_eq|bitand|bitor|not|not_eq|or|or_eq|xor|xor_eq)\b/,
            'boolean': /\b(?:false|true)\b/
          });
          Prism.languages.insertBefore('cpp', 'string', {
            'module': {
              pattern: RegExp(/(\b(?:import|module)\s+)/.source + '(?:' + /"(?:\\(?:\r\n|[\s\S])|[^"\\\r\n])*"|<[^<>\r\n]*>/.source + '|' + /<mod-name>(?:\s*:\s*<mod-name>)?|:\s*<mod-name>/.source.replace(/<mod-name>/g, function () {
                return modName;
              }) + ')'),
              lookbehind: true,
              greedy: true,
              inside: {
                'string': /^[<"][\s\S]+/,
                'operator': /:/,
                'punctuation': /\./
              }
            },
            'raw-string': {
              pattern: /R"([^()\\ ]{0,16})\([\s\S]*?\)\1"/,
              alias: 'string',
              greedy: true
            }
          });
          Prism.languages.insertBefore('cpp', 'keyword', {
            'generic-function': {
              pattern: /\b(?!operator\b)[a-z_]\w*\s*<(?:[^<>]|<[^<>]*>)*>(?=\s*\()/i,
              inside: {
                'function': /^\w+/,
                'generic': {
                  pattern: /<[\s\S]+/,
                  alias: 'class-name',
                  inside: Prism.languages.cpp
                }
              }
            }
          });
          Prism.languages.insertBefore('cpp', 'operator', {
            'double-colon': {
              pattern: /::/,
              alias: 'punctuation'
            }
          });
          Prism.languages.insertBefore('cpp', 'class-name', {
            'base-clause': {
              pattern: /(\b(?:class|struct)\s+\w+\s*:\s*)[^;{}"'\s]+(?:\s+[^;{}"'\s]+)*(?=\s*[;{])/,
              lookbehind: true,
              greedy: true,
              inside: Prism.languages.extend('cpp', {})
            }
          });
          Prism.languages.insertBefore('inside', 'double-colon', { 'class-name': /\b[a-z_]\w*\b(?!\s*::)/i }, Prism.languages.cpp['base-clause']);
        }(Prism));
        (function (Prism) {
          function replace(pattern, replacements) {
            return pattern.replace(/<<(\d+)>>/g, function (m, index) {
              return '(?:' + replacements[+index] + ')';
            });
          }
          function re(pattern, replacements, flags) {
            return RegExp(replace(pattern, replacements), flags || '');
          }
          function nested(pattern, depthLog2) {
            for (var i = 0; i < depthLog2; i++) {
              pattern = pattern.replace(/<<self>>/g, function () {
                return '(?:' + pattern + ')';
              });
            }
            return pattern.replace(/<<self>>/g, '[^\\s\\S]');
          }
          var keywordKinds = {
            type: 'bool byte char decimal double dynamic float int long object sbyte short string uint ulong ushort var void',
            typeDeclaration: 'class enum interface record struct',
            contextual: 'add alias and ascending async await by descending from(?=\\s*(?:\\w|$)) get global group into init(?=\\s*;) join let nameof not notnull on or orderby partial remove select set unmanaged value when where with(?=\\s*{)',
            other: 'abstract as base break case catch checked const continue default delegate do else event explicit extern finally fixed for foreach goto if implicit in internal is lock namespace new null operator out override params private protected public readonly ref return sealed sizeof stackalloc static switch this throw try typeof unchecked unsafe using virtual volatile while yield'
          };
          function keywordsToPattern(words) {
            return '\\b(?:' + words.trim().replace(/ /g, '|') + ')\\b';
          }
          var typeDeclarationKeywords = keywordsToPattern(keywordKinds.typeDeclaration);
          var keywords = RegExp(keywordsToPattern(keywordKinds.type + ' ' + keywordKinds.typeDeclaration + ' ' + keywordKinds.contextual + ' ' + keywordKinds.other));
          var nonTypeKeywords = keywordsToPattern(keywordKinds.typeDeclaration + ' ' + keywordKinds.contextual + ' ' + keywordKinds.other);
          var nonContextualKeywords = keywordsToPattern(keywordKinds.type + ' ' + keywordKinds.typeDeclaration + ' ' + keywordKinds.other);
          var generic = nested(/<(?:[^<>;=+\-*/%&|^]|<<self>>)*>/.source, 2);
          var nestedRound = nested(/\((?:[^()]|<<self>>)*\)/.source, 2);
          var name = /@?\b[A-Za-z_]\w*\b/.source;
          var genericName = replace(/<<0>>(?:\s*<<1>>)?/.source, [
            name,
            generic
          ]);
          var identifier = replace(/(?!<<0>>)<<1>>(?:\s*\.\s*<<1>>)*/.source, [
            nonTypeKeywords,
            genericName
          ]);
          var array = /\[\s*(?:,\s*)*\]/.source;
          var typeExpressionWithoutTuple = replace(/<<0>>(?:\s*(?:\?\s*)?<<1>>)*(?:\s*\?)?/.source, [
            identifier,
            array
          ]);
          var tupleElement = replace(/[^,()<>[\];=+\-*/%&|^]|<<0>>|<<1>>|<<2>>/.source, [
            generic,
            nestedRound,
            array
          ]);
          var tuple = replace(/\(<<0>>+(?:,<<0>>+)+\)/.source, [tupleElement]);
          var typeExpression = replace(/(?:<<0>>|<<1>>)(?:\s*(?:\?\s*)?<<2>>)*(?:\s*\?)?/.source, [
            tuple,
            identifier,
            array
          ]);
          var typeInside = {
            'keyword': keywords,
            'punctuation': /[<>()?,.:[\]]/
          };
          var character = /'(?:[^\r\n'\\]|\\.|\\[Uux][\da-fA-F]{1,8})'/.source;
          var regularString = /"(?:\\.|[^\\"\r\n])*"/.source;
          var verbatimString = /@"(?:""|\\[\s\S]|[^\\"])*"(?!")/.source;
          Prism.languages.csharp = Prism.languages.extend('clike', {
            'string': [
              {
                pattern: re(/(^|[^$\\])<<0>>/.source, [verbatimString]),
                lookbehind: true,
                greedy: true
              },
              {
                pattern: re(/(^|[^@$\\])<<0>>/.source, [regularString]),
                lookbehind: true,
                greedy: true
              }
            ],
            'class-name': [
              {
                pattern: re(/(\busing\s+static\s+)<<0>>(?=\s*;)/.source, [identifier]),
                lookbehind: true,
                inside: typeInside
              },
              {
                pattern: re(/(\busing\s+<<0>>\s*=\s*)<<1>>(?=\s*;)/.source, [
                  name,
                  typeExpression
                ]),
                lookbehind: true,
                inside: typeInside
              },
              {
                pattern: re(/(\busing\s+)<<0>>(?=\s*=)/.source, [name]),
                lookbehind: true
              },
              {
                pattern: re(/(\b<<0>>\s+)<<1>>/.source, [
                  typeDeclarationKeywords,
                  genericName
                ]),
                lookbehind: true,
                inside: typeInside
              },
              {
                pattern: re(/(\bcatch\s*\(\s*)<<0>>/.source, [identifier]),
                lookbehind: true,
                inside: typeInside
              },
              {
                pattern: re(/(\bwhere\s+)<<0>>/.source, [name]),
                lookbehind: true
              },
              {
                pattern: re(/(\b(?:is(?:\s+not)?|as)\s+)<<0>>/.source, [typeExpressionWithoutTuple]),
                lookbehind: true,
                inside: typeInside
              },
              {
                pattern: re(/\b<<0>>(?=\s+(?!<<1>>|with\s*\{)<<2>>(?:\s*[=,;:{)\]]|\s+(?:in|when)\b))/.source, [
                  typeExpression,
                  nonContextualKeywords,
                  name
                ]),
                inside: typeInside
              }
            ],
            'keyword': keywords,
            'number': /(?:\b0(?:x[\da-f_]*[\da-f]|b[01_]*[01])|(?:\B\.\d+(?:_+\d+)*|\b\d+(?:_+\d+)*(?:\.\d+(?:_+\d+)*)?)(?:e[-+]?\d+(?:_+\d+)*)?)(?:[dflmu]|lu|ul)?\b/i,
            'operator': />>=?|<<=?|[-=]>|([-+&|])\1|~|\?\?=?|[-+*/%&|^!=<>]=?/,
            'punctuation': /\?\.?|::|[{}[\];(),.:]/
          });
          Prism.languages.insertBefore('csharp', 'number', {
            'range': {
              pattern: /\.\./,
              alias: 'operator'
            }
          });
          Prism.languages.insertBefore('csharp', 'punctuation', {
            'named-parameter': {
              pattern: re(/([(,]\s*)<<0>>(?=\s*:)/.source, [name]),
              lookbehind: true,
              alias: 'punctuation'
            }
          });
          Prism.languages.insertBefore('csharp', 'class-name', {
            'namespace': {
              pattern: re(/(\b(?:namespace|using)\s+)<<0>>(?:\s*\.\s*<<0>>)*(?=\s*[;{])/.source, [name]),
              lookbehind: true,
              inside: { 'punctuation': /\./ }
            },
            'type-expression': {
              pattern: re(/(\b(?:default|sizeof|typeof)\s*\(\s*(?!\s))(?:[^()\s]|\s(?!\s)|<<0>>)*(?=\s*\))/.source, [nestedRound]),
              lookbehind: true,
              alias: 'class-name',
              inside: typeInside
            },
            'return-type': {
              pattern: re(/<<0>>(?=\s+(?:<<1>>\s*(?:=>|[({]|\.\s*this\s*\[)|this\s*\[))/.source, [
                typeExpression,
                identifier
              ]),
              inside: typeInside,
              alias: 'class-name'
            },
            'constructor-invocation': {
              pattern: re(/(\bnew\s+)<<0>>(?=\s*[[({])/.source, [typeExpression]),
              lookbehind: true,
              inside: typeInside,
              alias: 'class-name'
            },
            'generic-method': {
              pattern: re(/<<0>>\s*<<1>>(?=\s*\()/.source, [
                name,
                generic
              ]),
              inside: {
                'function': re(/^<<0>>/.source, [name]),
                'generic': {
                  pattern: RegExp(generic),
                  alias: 'class-name',
                  inside: typeInside
                }
              }
            },
            'type-list': {
              pattern: re(/\b((?:<<0>>\s+<<1>>|record\s+<<1>>\s*<<5>>|where\s+<<2>>)\s*:\s*)(?:<<3>>|<<4>>|<<1>>\s*<<5>>|<<6>>)(?:\s*,\s*(?:<<3>>|<<4>>|<<6>>))*(?=\s*(?:where|[{;]|=>|$))/.source, [
                typeDeclarationKeywords,
                genericName,
                name,
                typeExpression,
                keywords.source,
                nestedRound,
                /\bnew\s*\(\s*\)/.source
              ]),
              lookbehind: true,
              inside: {
                'record-arguments': {
                  pattern: re(/(^(?!new\s*\()<<0>>\s*)<<1>>/.source, [
                    genericName,
                    nestedRound
                  ]),
                  lookbehind: true,
                  greedy: true,
                  inside: Prism.languages.csharp
                },
                'keyword': keywords,
                'class-name': {
                  pattern: RegExp(typeExpression),
                  greedy: true,
                  inside: typeInside
                },
                'punctuation': /[,()]/
              }
            },
            'preprocessor': {
              pattern: /(^[\t ]*)#.*/m,
              lookbehind: true,
              alias: 'property',
              inside: {
                'directive': {
                  pattern: /(#)\b(?:define|elif|else|endif|endregion|error|if|line|nullable|pragma|region|undef|warning)\b/,
                  lookbehind: true,
                  alias: 'keyword'
                }
              }
            }
          });
          var regularStringOrCharacter = regularString + '|' + character;
          var regularStringCharacterOrComment = replace(/\/(?![*/])|\/\/[^\r\n]*[\r\n]|\/\*(?:[^*]|\*(?!\/))*\*\/|<<0>>/.source, [regularStringOrCharacter]);
          var roundExpression = nested(replace(/[^"'/()]|<<0>>|\(<<self>>*\)/.source, [regularStringCharacterOrComment]), 2);
          var attrTarget = /\b(?:assembly|event|field|method|module|param|property|return|type)\b/.source;
          var attr = replace(/<<0>>(?:\s*\(<<1>>*\))?/.source, [
            identifier,
            roundExpression
          ]);
          Prism.languages.insertBefore('csharp', 'class-name', {
            'attribute': {
              pattern: re(/((?:^|[^\s\w>)?])\s*\[\s*)(?:<<0>>\s*:\s*)?<<1>>(?:\s*,\s*<<1>>)*(?=\s*\])/.source, [
                attrTarget,
                attr
              ]),
              lookbehind: true,
              greedy: true,
              inside: {
                'target': {
                  pattern: re(/^<<0>>(?=\s*:)/.source, [attrTarget]),
                  alias: 'keyword'
                },
                'attribute-arguments': {
                  pattern: re(/\(<<0>>*\)/.source, [roundExpression]),
                  inside: Prism.languages.csharp
                },
                'class-name': {
                  pattern: RegExp(identifier),
                  inside: { 'punctuation': /\./ }
                },
                'punctuation': /[:,]/
              }
            }
          });
          var formatString = /:[^}\r\n]+/.source;
          var mInterpolationRound = nested(replace(/[^"'/()]|<<0>>|\(<<self>>*\)/.source, [regularStringCharacterOrComment]), 2);
          var mInterpolation = replace(/\{(?!\{)(?:(?![}:])<<0>>)*<<1>>?\}/.source, [
            mInterpolationRound,
            formatString
          ]);
          var sInterpolationRound = nested(replace(/[^"'/()]|\/(?!\*)|\/\*(?:[^*]|\*(?!\/))*\*\/|<<0>>|\(<<self>>*\)/.source, [regularStringOrCharacter]), 2);
          var sInterpolation = replace(/\{(?!\{)(?:(?![}:])<<0>>)*<<1>>?\}/.source, [
            sInterpolationRound,
            formatString
          ]);
          function createInterpolationInside(interpolation, interpolationRound) {
            return {
              'interpolation': {
                pattern: re(/((?:^|[^{])(?:\{\{)*)<<0>>/.source, [interpolation]),
                lookbehind: true,
                inside: {
                  'format-string': {
                    pattern: re(/(^\{(?:(?![}:])<<0>>)*)<<1>>(?=\}$)/.source, [
                      interpolationRound,
                      formatString
                    ]),
                    lookbehind: true,
                    inside: { 'punctuation': /^:/ }
                  },
                  'punctuation': /^\{|\}$/,
                  'expression': {
                    pattern: /[\s\S]+/,
                    alias: 'language-csharp',
                    inside: Prism.languages.csharp
                  }
                }
              },
              'string': /[\s\S]+/
            };
          }
          Prism.languages.insertBefore('csharp', 'string', {
            'interpolation-string': [
              {
                pattern: re(/(^|[^\\])(?:\$@|@\$)"(?:""|\\[\s\S]|\{\{|<<0>>|[^\\{"])*"/.source, [mInterpolation]),
                lookbehind: true,
                greedy: true,
                inside: createInterpolationInside(mInterpolation, mInterpolationRound)
              },
              {
                pattern: re(/(^|[^@\\])\$"(?:\\.|\{\{|<<0>>|[^\\"{])*"/.source, [sInterpolation]),
                lookbehind: true,
                greedy: true,
                inside: createInterpolationInside(sInterpolation, sInterpolationRound)
              }
            ],
            'char': {
              pattern: RegExp(character),
              greedy: true
            }
          });
          Prism.languages.dotnet = Prism.languages.cs = Prism.languages.csharp;
        }(Prism));
        (function (Prism) {
          var string = /(?:"(?:\\(?:\r\n|[\s\S])|[^"\\\r\n])*"|'(?:\\(?:\r\n|[\s\S])|[^'\\\r\n])*')/;
          Prism.languages.css = {
            'comment': /\/\*[\s\S]*?\*\//,
            'atrule': {
              pattern: RegExp('@[\\w-](?:' + /[^;{\s"']|\s+(?!\s)/.source + '|' + string.source + ')*?' + /(?:;|(?=\s*\{))/.source),
              inside: {
                'rule': /^@[\w-]+/,
                'selector-function-argument': {
                  pattern: /(\bselector\s*\(\s*(?![\s)]))(?:[^()\s]|\s+(?![\s)])|\((?:[^()]|\([^()]*\))*\))+(?=\s*\))/,
                  lookbehind: true,
                  alias: 'selector'
                },
                'keyword': {
                  pattern: /(^|[^\w-])(?:and|not|only|or)(?![\w-])/,
                  lookbehind: true
                }
              }
            },
            'url': {
              pattern: RegExp('\\burl\\((?:' + string.source + '|' + /(?:[^\\\r\n()"']|\\[\s\S])*/.source + ')\\)', 'i'),
              greedy: true,
              inside: {
                'function': /^url/i,
                'punctuation': /^\(|\)$/,
                'string': {
                  pattern: RegExp('^' + string.source + '$'),
                  alias: 'url'
                }
              }
            },
            'selector': {
              pattern: RegExp('(^|[{}\\s])[^{}\\s](?:[^{};"\'\\s]|\\s+(?![\\s{])|' + string.source + ')*(?=\\s*\\{)'),
              lookbehind: true
            },
            'string': {
              pattern: string,
              greedy: true
            },
            'property': {
              pattern: /(^|[^-\w\xA0-\uFFFF])(?!\s)[-_a-z\xA0-\uFFFF](?:(?!\s)[-\w\xA0-\uFFFF])*(?=\s*:)/i,
              lookbehind: true
            },
            'important': /!important\b/i,
            'function': {
              pattern: /(^|[^-a-z0-9])[-a-z0-9]+(?=\()/i,
              lookbehind: true
            },
            'punctuation': /[(){};:,]/
          };
          Prism.languages.css['atrule'].inside.rest = Prism.languages.css;
          var markup = Prism.languages.markup;
          if (markup) {
            markup.tag.addInlined('style', 'css');
            markup.tag.addAttribute('style', 'css');
          }
        }(Prism));
        (function (Prism) {
          var keywords = /\b(?:abstract|assert|boolean|break|byte|case|catch|char|class|const|continue|default|do|double|else|enum|exports|extends|final|finally|float|for|goto|if|implements|import|instanceof|int|interface|long|module|native|new|non-sealed|null|open|opens|package|permits|private|protected|provides|public|record(?!\s*[(){}[\]<>=%~.:,;?+\-*/&|^])|requires|return|sealed|short|static|strictfp|super|switch|synchronized|this|throw|throws|to|transient|transitive|try|uses|var|void|volatile|while|with|yield)\b/;
          var classNamePrefix = /(?:[a-z]\w*\s*\.\s*)*(?:[A-Z]\w*\s*\.\s*)*/.source;
          var className = {
            pattern: RegExp(/(^|[^\w.])/.source + classNamePrefix + /[A-Z](?:[\d_A-Z]*[a-z]\w*)?\b/.source),
            lookbehind: true,
            inside: {
              'namespace': {
                pattern: /^[a-z]\w*(?:\s*\.\s*[a-z]\w*)*(?:\s*\.)?/,
                inside: { 'punctuation': /\./ }
              },
              'punctuation': /\./
            }
          };
          Prism.languages.java = Prism.languages.extend('clike', {
            'string': {
              pattern: /(^|[^\\])"(?:\\.|[^"\\\r\n])*"/,
              lookbehind: true,
              greedy: true
            },
            'class-name': [
              className,
              {
                pattern: RegExp(/(^|[^\w.])/.source + classNamePrefix + /[A-Z]\w*(?=\s+\w+\s*[;,=()]|\s*(?:\[[\s,]*\]\s*)?::\s*new\b)/.source),
                lookbehind: true,
                inside: className.inside
              },
              {
                pattern: RegExp(/(\b(?:class|enum|extends|implements|instanceof|interface|new|record|throws)\s+)/.source + classNamePrefix + /[A-Z]\w*\b/.source),
                lookbehind: true,
                inside: className.inside
              }
            ],
            'keyword': keywords,
            'function': [
              Prism.languages.clike.function,
              {
                pattern: /(::\s*)[a-z_]\w*/,
                lookbehind: true
              }
            ],
            'number': /\b0b[01][01_]*L?\b|\b0x(?:\.[\da-f_p+-]+|[\da-f_]+(?:\.[\da-f_p+-]+)?)\b|(?:\b\d[\d_]*(?:\.[\d_]*)?|\B\.\d[\d_]*)(?:e[+-]?\d[\d_]*)?[dfl]?/i,
            'operator': {
              pattern: /(^|[^.])(?:<<=?|>>>?=?|->|--|\+\+|&&|\|\||::|[?:~]|[-+*/%&|^!=<>]=?)/m,
              lookbehind: true
            },
            'constant': /\b[A-Z][A-Z_\d]+\b/
          });
          Prism.languages.insertBefore('java', 'string', {
            'triple-quoted-string': {
              pattern: /"""[ \t]*[\r\n](?:(?:"|"")?(?:\\.|[^"\\]))*"""/,
              greedy: true,
              alias: 'string'
            },
            'char': {
              pattern: /'(?:\\.|[^'\\\r\n]){1,6}'/,
              greedy: true
            }
          });
          Prism.languages.insertBefore('java', 'class-name', {
            'annotation': {
              pattern: /(^|[^.])@\w+(?:\s*\.\s*\w+)*/,
              lookbehind: true,
              alias: 'punctuation'
            },
            'generics': {
              pattern: /<(?:[\w\s,.?]|&(?!&)|<(?:[\w\s,.?]|&(?!&)|<(?:[\w\s,.?]|&(?!&)|<(?:[\w\s,.?]|&(?!&))*>)*>)*>)*>/,
              inside: {
                'class-name': className,
                'keyword': keywords,
                'punctuation': /[<>(),.:]/,
                'operator': /[?&|]/
              }
            },
            'import': [
              {
                pattern: RegExp(/(\bimport\s+)/.source + classNamePrefix + /(?:[A-Z]\w*|\*)(?=\s*;)/.source),
                lookbehind: true,
                inside: {
                  'namespace': className.inside.namespace,
                  'punctuation': /\./,
                  'operator': /\*/,
                  'class-name': /\w+/
                }
              },
              {
                pattern: RegExp(/(\bimport\s+static\s+)/.source + classNamePrefix + /(?:\w+|\*)(?=\s*;)/.source),
                lookbehind: true,
                alias: 'static',
                inside: {
                  'namespace': className.inside.namespace,
                  'static': /\b\w+$/,
                  'punctuation': /\./,
                  'operator': /\*/,
                  'class-name': /\w+/
                }
              }
            ],
            'namespace': {
              pattern: RegExp(/(\b(?:exports|import(?:\s+static)?|module|open|opens|package|provides|requires|to|transitive|uses|with)\s+)(?!<keyword>)[a-z]\w*(?:\.[a-z]\w*)*\.?/.source.replace(/<keyword>/g, function () {
                return keywords.source;
              })),
              lookbehind: true,
              inside: { 'punctuation': /\./ }
            }
          });
        }(Prism));
        Prism.languages.javascript = Prism.languages.extend('clike', {
          'class-name': [
            Prism.languages.clike['class-name'],
            {
              pattern: /(^|[^$\w\xA0-\uFFFF])(?!\s)[_$A-Z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*(?=\.(?:constructor|prototype))/,
              lookbehind: true
            }
          ],
          'keyword': [
            {
              pattern: /((?:^|\})\s*)catch\b/,
              lookbehind: true
            },
            {
              pattern: /(^|[^.]|\.\.\.\s*)\b(?:as|assert(?=\s*\{)|async(?=\s*(?:function\b|\(|[$\w\xA0-\uFFFF]|$))|await|break|case|class|const|continue|debugger|default|delete|do|else|enum|export|extends|finally(?=\s*(?:\{|$))|for|from(?=\s*(?:['"]|$))|function|(?:get|set)(?=\s*(?:[#\[$\w\xA0-\uFFFF]|$))|if|implements|import|in|instanceof|interface|let|new|null|of|package|private|protected|public|return|static|super|switch|this|throw|try|typeof|undefined|var|void|while|with|yield)\b/,
              lookbehind: true
            }
          ],
          'function': /#?(?!\s)[_$a-zA-Z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*(?=\s*(?:\.\s*(?:apply|bind|call)\s*)?\()/,
          'number': {
            pattern: RegExp(/(^|[^\w$])/.source + '(?:' + (/NaN|Infinity/.source + '|' + /0[bB][01]+(?:_[01]+)*n?/.source + '|' + /0[oO][0-7]+(?:_[0-7]+)*n?/.source + '|' + /0[xX][\dA-Fa-f]+(?:_[\dA-Fa-f]+)*n?/.source + '|' + /\d+(?:_\d+)*n/.source + '|' + /(?:\d+(?:_\d+)*(?:\.(?:\d+(?:_\d+)*)?)?|\.\d+(?:_\d+)*)(?:[Ee][+-]?\d+(?:_\d+)*)?/.source) + ')' + /(?![\w$])/.source),
            lookbehind: true
          },
          'operator': /--|\+\+|\*\*=?|=>|&&=?|\|\|=?|[!=]==|<<=?|>>>?=?|[-+*/%&|^!=<>]=?|\.{3}|\?\?=?|\?\.?|[~:]/
        });
        Prism.languages.javascript['class-name'][0].pattern = /(\b(?:class|extends|implements|instanceof|interface|new)\s+)[\w.\\]+/;
        Prism.languages.insertBefore('javascript', 'keyword', {
          'regex': {
            pattern: RegExp(/((?:^|[^$\w\xA0-\uFFFF."'\])\s]|\b(?:return|yield))\s*)/.source + /\//.source + '(?:' + /(?:\[(?:[^\]\\\r\n]|\\.)*\]|\\.|[^/\\\[\r\n])+\/[dgimyus]{0,7}/.source + '|' + /(?:\[(?:[^[\]\\\r\n]|\\.|\[(?:[^[\]\\\r\n]|\\.|\[(?:[^[\]\\\r\n]|\\.)*\])*\])*\]|\\.|[^/\\\[\r\n])+\/[dgimyus]{0,7}v[dgimyus]{0,7}/.source + ')' + /(?=(?:\s|\/\*(?:[^*]|\*(?!\/))*\*\/)*(?:$|[\r\n,.;:})\]]|\/\/))/.source),
            lookbehind: true,
            greedy: true,
            inside: {
              'regex-source': {
                pattern: /^(\/)[\s\S]+(?=\/[a-z]*$)/,
                lookbehind: true,
                alias: 'language-regex',
                inside: Prism.languages.regex
              },
              'regex-delimiter': /^\/|\/$/,
              'regex-flags': /^[a-z]+$/
            }
          },
          'function-variable': {
            pattern: /#?(?!\s)[_$a-zA-Z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*(?=\s*[=:]\s*(?:async\s*)?(?:\bfunction\b|(?:\((?:[^()]|\([^()]*\))*\)|(?!\s)[_$a-zA-Z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*)\s*=>))/,
            alias: 'function'
          },
          'parameter': [
            {
              pattern: /(function(?:\s+(?!\s)[_$a-zA-Z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*)?\s*\(\s*)(?!\s)(?:[^()\s]|\s+(?![\s)])|\([^()]*\))+(?=\s*\))/,
              lookbehind: true,
              inside: Prism.languages.javascript
            },
            {
              pattern: /(^|[^$\w\xA0-\uFFFF])(?!\s)[_$a-z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*(?=\s*=>)/i,
              lookbehind: true,
              inside: Prism.languages.javascript
            },
            {
              pattern: /(\(\s*)(?!\s)(?:[^()\s]|\s+(?![\s)])|\([^()]*\))+(?=\s*\)\s*=>)/,
              lookbehind: true,
              inside: Prism.languages.javascript
            },
            {
              pattern: /((?:\b|\s|^)(?!(?:as|async|await|break|case|catch|class|const|continue|debugger|default|delete|do|else|enum|export|extends|finally|for|from|function|get|if|implements|import|in|instanceof|interface|let|new|null|of|package|private|protected|public|return|set|static|super|switch|this|throw|try|typeof|undefined|var|void|while|with|yield)(?![$\w\xA0-\uFFFF]))(?:(?!\s)[_$a-zA-Z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*\s*)\(\s*|\]\s*\(\s*)(?!\s)(?:[^()\s]|\s+(?![\s)])|\([^()]*\))+(?=\s*\)\s*\{)/,
              lookbehind: true,
              inside: Prism.languages.javascript
            }
          ],
          'constant': /\b[A-Z](?:[A-Z_]|\dx?)*\b/
        });
        Prism.languages.insertBefore('javascript', 'string', {
          'hashbang': {
            pattern: /^#!.*/,
            greedy: true,
            alias: 'comment'
          },
          'template-string': {
            pattern: /`(?:\\[\s\S]|\$\{(?:[^{}]|\{(?:[^{}]|\{[^}]*\})*\})+\}|(?!\$\{)[^\\`])*`/,
            greedy: true,
            inside: {
              'template-punctuation': {
                pattern: /^`|`$/,
                alias: 'string'
              },
              'interpolation': {
                pattern: /((?:^|[^\\])(?:\\{2})*)\$\{(?:[^{}]|\{(?:[^{}]|\{[^}]*\})*\})+\}/,
                lookbehind: true,
                inside: {
                  'interpolation-punctuation': {
                    pattern: /^\$\{|\}$/,
                    alias: 'punctuation'
                  },
                  rest: Prism.languages.javascript
                }
              },
              'string': /[\s\S]+/
            }
          },
          'string-property': {
            pattern: /((?:^|[,{])[ \t]*)(["'])(?:\\(?:\r\n|[\s\S])|(?!\2)[^\\\r\n])*\2(?=\s*:)/m,
            lookbehind: true,
            greedy: true,
            alias: 'property'
          }
        });
        Prism.languages.insertBefore('javascript', 'operator', {
          'literal-property': {
            pattern: /((?:^|[,{])[ \t]*)(?!\s)[_$a-zA-Z\xA0-\uFFFF](?:(?!\s)[$\w\xA0-\uFFFF])*(?=\s*:)/m,
            lookbehind: true,
            alias: 'property'
          }
        });
        if (Prism.languages.markup) {
          Prism.languages.markup.tag.addInlined('script', 'javascript');
          Prism.languages.markup.tag.addAttribute(/on(?:abort|blur|change|click|composition(?:end|start|update)|dblclick|error|focus(?:in|out)?|key(?:down|up)|load|mouse(?:down|enter|leave|move|out|over|up)|reset|resize|scroll|select|slotchange|submit|unload|wheel)/.source, 'javascript');
        }
        Prism.languages.js = Prism.languages.javascript;
        Prism.languages.markup = {
          'comment': {
            pattern: /<!--(?:(?!<!--)[\s\S])*?-->/,
            greedy: true
          },
          'prolog': {
            pattern: /<\?[\s\S]+?\?>/,
            greedy: true
          },
          'doctype': {
            pattern: /<!DOCTYPE(?:[^>"'[\]]|"[^"]*"|'[^']*')+(?:\[(?:[^<"'\]]|"[^"]*"|'[^']*'|<(?!!--)|<!--(?:[^-]|-(?!->))*-->)*\]\s*)?>/i,
            greedy: true,
            inside: {
              'internal-subset': {
                pattern: /(^[^\[]*\[)[\s\S]+(?=\]>$)/,
                lookbehind: true,
                greedy: true,
                inside: null
              },
              'string': {
                pattern: /"[^"]*"|'[^']*'/,
                greedy: true
              },
              'punctuation': /^<!|>$|[[\]]/,
              'doctype-tag': /^DOCTYPE/i,
              'name': /[^\s<>'"]+/
            }
          },
          'cdata': {
            pattern: /<!\[CDATA\[[\s\S]*?\]\]>/i,
            greedy: true
          },
          'tag': {
            pattern: /<\/?(?!\d)[^\s>\/=$<%]+(?:\s(?:\s*[^\s>\/=]+(?:\s*=\s*(?:"[^"]*"|'[^']*'|[^\s'">=]+(?=[\s>]))|(?=[\s/>])))+)?\s*\/?>/,
            greedy: true,
            inside: {
              'tag': {
                pattern: /^<\/?[^\s>\/]+/,
                inside: {
                  'punctuation': /^<\/?/,
                  'namespace': /^[^\s>\/:]+:/
                }
              },
              'special-attr': [],
              'attr-value': {
                pattern: /=\s*(?:"[^"]*"|'[^']*'|[^\s'">=]+)/,
                inside: {
                  'punctuation': [
                    {
                      pattern: /^=/,
                      alias: 'attr-equals'
                    },
                    {
                      pattern: /^(\s*)["']|["']$/,
                      lookbehind: true
                    }
                  ]
                }
              },
              'punctuation': /\/?>/,
              'attr-name': {
                pattern: /[^\s>\/]+/,
                inside: { 'namespace': /^[^\s>\/:]+:/ }
              }
            }
          },
          'entity': [
            {
              pattern: /&[\da-z]{1,8};/i,
              alias: 'named-entity'
            },
            /&#x?[\da-f]{1,8};/i
          ]
        };
        Prism.languages.markup['tag'].inside['attr-value'].inside['entity'] = Prism.languages.markup['entity'];
        Prism.languages.markup['doctype'].inside['internal-subset'].inside = Prism.languages.markup;
        Prism.hooks.add('wrap', function (env) {
          if (env.type === 'entity') {
            env.attributes['title'] = env.content.replace(/&amp;/, '&');
          }
        });
        Object.defineProperty(Prism.languages.markup.tag, 'addInlined', {
          value: function addInlined(tagName, lang) {
            var includedCdataInside = {};
            includedCdataInside['language-' + lang] = {
              pattern: /(^<!\[CDATA\[)[\s\S]+?(?=\]\]>$)/i,
              lookbehind: true,
              inside: Prism.languages[lang]
            };
            includedCdataInside['cdata'] = /^<!\[CDATA\[|\]\]>$/i;
            var inside = {
              'included-cdata': {
                pattern: /<!\[CDATA\[[\s\S]*?\]\]>/i,
                inside: includedCdataInside
              }
            };
            inside['language-' + lang] = {
              pattern: /[\s\S]+/,
              inside: Prism.languages[lang]
            };
            var def = {};
            def[tagName] = {
              pattern: RegExp(/(<__[^>]*>)(?:<!\[CDATA\[(?:[^\]]|\](?!\]>))*\]\]>|(?!<!\[CDATA\[)[\s\S])*?(?=<\/__>)/.source.replace(/__/g, function () {
                return tagName;
              }), 'i'),
              lookbehind: true,
              greedy: true,
              inside: inside
            };
            Prism.languages.insertBefore('markup', 'cdata', def);
          }
        });
        Object.defineProperty(Prism.languages.markup.tag, 'addAttribute', {
          value: function (attrName, lang) {
            Prism.languages.markup.tag.inside['special-attr'].push({
              pattern: RegExp(/(^|["'\s])/.source + '(?:' + attrName + ')' + /\s*=\s*(?:"[^"]*"|'[^']*'|[^\s'">=]+(?=[\s>]))/.source, 'i'),
              lookbehind: true,
              inside: {
                'attr-name': /^[^\s=]+/,
                'attr-value': {
                  pattern: /=[\s\S]+/,
                  inside: {
                    'value': {
                      pattern: /(^=\s*(["']|(?!["'])))\S[\s\S]*(?=\2$)/,
                      lookbehind: true,
                      alias: [
                        lang,
                        'language-' + lang
                      ],
                      inside: Prism.languages[lang]
                    },
                    'punctuation': [
                      {
                        pattern: /^=/,
                        alias: 'attr-equals'
                      },
                      /"|'/
                    ]
                  }
                }
              }
            });
          }
        });
        Prism.languages.html = Prism.languages.markup;
        Prism.languages.mathml = Prism.languages.markup;
        Prism.languages.svg = Prism.languages.markup;
        Prism.languages.xml = Prism.languages.extend('markup', {});
        Prism.languages.ssml = Prism.languages.xml;
        Prism.languages.atom = Prism.languages.xml;
        Prism.languages.rss = Prism.languages.xml;
        (function (Prism) {
          var comment = /\/\*[\s\S]*?\*\/|\/\/.*|#(?!\[).*/;
          var constant = [
            {
              pattern: /\b(?:false|true)\b/i,
              alias: 'boolean'
            },
            {
              pattern: /(::\s*)\b[a-z_]\w*\b(?!\s*\()/i,
              greedy: true,
              lookbehind: true
            },
            {
              pattern: /(\b(?:case|const)\s+)\b[a-z_]\w*(?=\s*[;=])/i,
              greedy: true,
              lookbehind: true
            },
            /\b(?:null)\b/i,
            /\b[A-Z_][A-Z0-9_]*\b(?!\s*\()/
          ];
          var number = /\b0b[01]+(?:_[01]+)*\b|\b0o[0-7]+(?:_[0-7]+)*\b|\b0x[\da-f]+(?:_[\da-f]+)*\b|(?:\b\d+(?:_\d+)*\.?(?:\d+(?:_\d+)*)?|\B\.\d+)(?:e[+-]?\d+)?/i;
          var operator = /<?=>|\?\?=?|\.{3}|\??->|[!=]=?=?|::|\*\*=?|--|\+\+|&&|\|\||<<|>>|[?~]|[/^|%*&<>.+-]=?/;
          var punctuation = /[{}\[\](),:;]/;
          Prism.languages.php = {
            'delimiter': {
              pattern: /\?>$|^<\?(?:php(?=\s)|=)?/i,
              alias: 'important'
            },
            'comment': comment,
            'variable': /\$+(?:\w+\b|(?=\{))/,
            'package': {
              pattern: /(namespace\s+|use\s+(?:function\s+)?)(?:\\?\b[a-z_]\w*)+\b(?!\\)/i,
              lookbehind: true,
              inside: { 'punctuation': /\\/ }
            },
            'class-name-definition': {
              pattern: /(\b(?:class|enum|interface|trait)\s+)\b[a-z_]\w*(?!\\)\b/i,
              lookbehind: true,
              alias: 'class-name'
            },
            'function-definition': {
              pattern: /(\bfunction\s+)[a-z_]\w*(?=\s*\()/i,
              lookbehind: true,
              alias: 'function'
            },
            'keyword': [
              {
                pattern: /(\(\s*)\b(?:array|bool|boolean|float|int|integer|object|string)\b(?=\s*\))/i,
                alias: 'type-casting',
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /([(,?]\s*)\b(?:array(?!\s*\()|bool|callable|(?:false|null)(?=\s*\|)|float|int|iterable|mixed|object|self|static|string)\b(?=\s*\$)/i,
                alias: 'type-hint',
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /(\)\s*:\s*(?:\?\s*)?)\b(?:array(?!\s*\()|bool|callable|(?:false|null)(?=\s*\|)|float|int|iterable|mixed|never|object|self|static|string|void)\b/i,
                alias: 'return-type',
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /\b(?:array(?!\s*\()|bool|float|int|iterable|mixed|object|string|void)\b/i,
                alias: 'type-declaration',
                greedy: true
              },
              {
                pattern: /(\|\s*)(?:false|null)\b|\b(?:false|null)(?=\s*\|)/i,
                alias: 'type-declaration',
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /\b(?:parent|self|static)(?=\s*::)/i,
                alias: 'static-context',
                greedy: true
              },
              {
                pattern: /(\byield\s+)from\b/i,
                lookbehind: true
              },
              /\bclass\b/i,
              {
                pattern: /((?:^|[^\s>:]|(?:^|[^-])>|(?:^|[^:]):)\s*)\b(?:abstract|and|array|as|break|callable|case|catch|clone|const|continue|declare|default|die|do|echo|else|elseif|empty|enddeclare|endfor|endforeach|endif|endswitch|endwhile|enum|eval|exit|extends|final|finally|fn|for|foreach|function|global|goto|if|implements|include|include_once|instanceof|insteadof|interface|isset|list|match|namespace|never|new|or|parent|print|private|protected|public|readonly|require|require_once|return|self|static|switch|throw|trait|try|unset|use|var|while|xor|yield|__halt_compiler)\b/i,
                lookbehind: true
              }
            ],
            'argument-name': {
              pattern: /([(,]\s*)\b[a-z_]\w*(?=\s*:(?!:))/i,
              lookbehind: true
            },
            'class-name': [
              {
                pattern: /(\b(?:extends|implements|instanceof|new(?!\s+self|\s+static))\s+|\bcatch\s*\()\b[a-z_]\w*(?!\\)\b/i,
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /(\|\s*)\b[a-z_]\w*(?!\\)\b/i,
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /\b[a-z_]\w*(?!\\)\b(?=\s*\|)/i,
                greedy: true
              },
              {
                pattern: /(\|\s*)(?:\\?\b[a-z_]\w*)+\b/i,
                alias: 'class-name-fully-qualified',
                greedy: true,
                lookbehind: true,
                inside: { 'punctuation': /\\/ }
              },
              {
                pattern: /(?:\\?\b[a-z_]\w*)+\b(?=\s*\|)/i,
                alias: 'class-name-fully-qualified',
                greedy: true,
                inside: { 'punctuation': /\\/ }
              },
              {
                pattern: /(\b(?:extends|implements|instanceof|new(?!\s+self\b|\s+static\b))\s+|\bcatch\s*\()(?:\\?\b[a-z_]\w*)+\b(?!\\)/i,
                alias: 'class-name-fully-qualified',
                greedy: true,
                lookbehind: true,
                inside: { 'punctuation': /\\/ }
              },
              {
                pattern: /\b[a-z_]\w*(?=\s*\$)/i,
                alias: 'type-declaration',
                greedy: true
              },
              {
                pattern: /(?:\\?\b[a-z_]\w*)+(?=\s*\$)/i,
                alias: [
                  'class-name-fully-qualified',
                  'type-declaration'
                ],
                greedy: true,
                inside: { 'punctuation': /\\/ }
              },
              {
                pattern: /\b[a-z_]\w*(?=\s*::)/i,
                alias: 'static-context',
                greedy: true
              },
              {
                pattern: /(?:\\?\b[a-z_]\w*)+(?=\s*::)/i,
                alias: [
                  'class-name-fully-qualified',
                  'static-context'
                ],
                greedy: true,
                inside: { 'punctuation': /\\/ }
              },
              {
                pattern: /([(,?]\s*)[a-z_]\w*(?=\s*\$)/i,
                alias: 'type-hint',
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /([(,?]\s*)(?:\\?\b[a-z_]\w*)+(?=\s*\$)/i,
                alias: [
                  'class-name-fully-qualified',
                  'type-hint'
                ],
                greedy: true,
                lookbehind: true,
                inside: { 'punctuation': /\\/ }
              },
              {
                pattern: /(\)\s*:\s*(?:\?\s*)?)\b[a-z_]\w*(?!\\)\b/i,
                alias: 'return-type',
                greedy: true,
                lookbehind: true
              },
              {
                pattern: /(\)\s*:\s*(?:\?\s*)?)(?:\\?\b[a-z_]\w*)+\b(?!\\)/i,
                alias: [
                  'class-name-fully-qualified',
                  'return-type'
                ],
                greedy: true,
                lookbehind: true,
                inside: { 'punctuation': /\\/ }
              }
            ],
            'constant': constant,
            'function': {
              pattern: /(^|[^\\\w])\\?[a-z_](?:[\w\\]*\w)?(?=\s*\()/i,
              lookbehind: true,
              inside: { 'punctuation': /\\/ }
            },
            'property': {
              pattern: /(->\s*)\w+/,
              lookbehind: true
            },
            'number': number,
            'operator': operator,
            'punctuation': punctuation
          };
          var string_interpolation = {
            pattern: /\{\$(?:\{(?:\{[^{}]+\}|[^{}]+)\}|[^{}])+\}|(^|[^\\{])\$+(?:\w+(?:\[[^\r\n\[\]]+\]|->\w+)?)/,
            lookbehind: true,
            inside: Prism.languages.php
          };
          var string = [
            {
              pattern: /<<<'([^']+)'[\r\n](?:.*[\r\n])*?\1;/,
              alias: 'nowdoc-string',
              greedy: true,
              inside: {
                'delimiter': {
                  pattern: /^<<<'[^']+'|[a-z_]\w*;$/i,
                  alias: 'symbol',
                  inside: { 'punctuation': /^<<<'?|[';]$/ }
                }
              }
            },
            {
              pattern: /<<<(?:"([^"]+)"[\r\n](?:.*[\r\n])*?\1;|([a-z_]\w*)[\r\n](?:.*[\r\n])*?\2;)/i,
              alias: 'heredoc-string',
              greedy: true,
              inside: {
                'delimiter': {
                  pattern: /^<<<(?:"[^"]+"|[a-z_]\w*)|[a-z_]\w*;$/i,
                  alias: 'symbol',
                  inside: { 'punctuation': /^<<<"?|[";]$/ }
                },
                'interpolation': string_interpolation
              }
            },
            {
              pattern: /`(?:\\[\s\S]|[^\\`])*`/,
              alias: 'backtick-quoted-string',
              greedy: true
            },
            {
              pattern: /'(?:\\[\s\S]|[^\\'])*'/,
              alias: 'single-quoted-string',
              greedy: true
            },
            {
              pattern: /"(?:\\[\s\S]|[^\\"])*"/,
              alias: 'double-quoted-string',
              greedy: true,
              inside: { 'interpolation': string_interpolation }
            }
          ];
          Prism.languages.insertBefore('php', 'variable', {
            'string': string,
            'attribute': {
              pattern: /#\[(?:[^"'\/#]|\/(?![*/])|\/\/.*$|#(?!\[).*$|\/\*(?:[^*]|\*(?!\/))*\*\/|"(?:\\[\s\S]|[^\\"])*"|'(?:\\[\s\S]|[^\\'])*')+\](?=\s*[a-z$#])/im,
              greedy: true,
              inside: {
                'attribute-content': {
                  pattern: /^(#\[)[\s\S]+(?=\]$)/,
                  lookbehind: true,
                  inside: {
                    'comment': comment,
                    'string': string,
                    'attribute-class-name': [
                      {
                        pattern: /([^:]|^)\b[a-z_]\w*(?!\\)\b/i,
                        alias: 'class-name',
                        greedy: true,
                        lookbehind: true
                      },
                      {
                        pattern: /([^:]|^)(?:\\?\b[a-z_]\w*)+/i,
                        alias: [
                          'class-name',
                          'class-name-fully-qualified'
                        ],
                        greedy: true,
                        lookbehind: true,
                        inside: { 'punctuation': /\\/ }
                      }
                    ],
                    'constant': constant,
                    'number': number,
                    'operator': operator,
                    'punctuation': punctuation
                  }
                },
                'delimiter': {
                  pattern: /^#\[|\]$/,
                  alias: 'punctuation'
                }
              }
            }
          });
          Prism.hooks.add('before-tokenize', function (env) {
            if (!/<\?/.test(env.code)) {
              return;
            }
            var phpPattern = /<\?(?:[^"'/#]|\/(?![*/])|("|')(?:\\[\s\S]|(?!\1)[^\\])*\1|(?:\/\/|#(?!\[))(?:[^?\n\r]|\?(?!>))*(?=$|\?>|[\r\n])|#\[|\/\*(?:[^*]|\*(?!\/))*(?:\*\/|$))*?(?:\?>|$)/g;
            Prism.languages['markup-templating'].buildPlaceholders(env, 'php', phpPattern);
          });
          Prism.hooks.add('after-tokenize', function (env) {
            Prism.languages['markup-templating'].tokenizePlaceholders(env, 'php');
          });
        }(Prism));
        Prism.languages.python = {
          'comment': {
            pattern: /(^|[^\\])#.*/,
            lookbehind: true,
            greedy: true
          },
          'string-interpolation': {
            pattern: /(?:f|fr|rf)(?:("""|''')[\s\S]*?\1|("|')(?:\\.|(?!\2)[^\\\r\n])*\2)/i,
            greedy: true,
            inside: {
              'interpolation': {
                pattern: /((?:^|[^{])(?:\{\{)*)\{(?!\{)(?:[^{}]|\{(?!\{)(?:[^{}]|\{(?!\{)(?:[^{}])+\})+\})+\}/,
                lookbehind: true,
                inside: {
                  'format-spec': {
                    pattern: /(:)[^:(){}]+(?=\}$)/,
                    lookbehind: true
                  },
                  'conversion-option': {
                    pattern: /![sra](?=[:}]$)/,
                    alias: 'punctuation'
                  },
                  rest: null
                }
              },
              'string': /[\s\S]+/
            }
          },
          'triple-quoted-string': {
            pattern: /(?:[rub]|br|rb)?("""|''')[\s\S]*?\1/i,
            greedy: true,
            alias: 'string'
          },
          'string': {
            pattern: /(?:[rub]|br|rb)?("|')(?:\\.|(?!\1)[^\\\r\n])*\1/i,
            greedy: true
          },
          'function': {
            pattern: /((?:^|\s)def[ \t]+)[a-zA-Z_]\w*(?=\s*\()/g,
            lookbehind: true
          },
          'class-name': {
            pattern: /(\bclass\s+)\w+/i,
            lookbehind: true
          },
          'decorator': {
            pattern: /(^[\t ]*)@\w+(?:\.\w+)*/m,
            lookbehind: true,
            alias: [
              'annotation',
              'punctuation'
            ],
            inside: { 'punctuation': /\./ }
          },
          'keyword': /\b(?:_(?=\s*:)|and|as|assert|async|await|break|case|class|continue|def|del|elif|else|except|exec|finally|for|from|global|if|import|in|is|lambda|match|nonlocal|not|or|pass|print|raise|return|try|while|with|yield)\b/,
          'builtin': /\b(?:__import__|abs|all|any|apply|ascii|basestring|bin|bool|buffer|bytearray|bytes|callable|chr|classmethod|cmp|coerce|compile|complex|delattr|dict|dir|divmod|enumerate|eval|execfile|file|filter|float|format|frozenset|getattr|globals|hasattr|hash|help|hex|id|input|int|intern|isinstance|issubclass|iter|len|list|locals|long|map|max|memoryview|min|next|object|oct|open|ord|pow|property|range|raw_input|reduce|reload|repr|reversed|round|set|setattr|slice|sorted|staticmethod|str|sum|super|tuple|type|unichr|unicode|vars|xrange|zip)\b/,
          'boolean': /\b(?:False|None|True)\b/,
          'number': /\b0(?:b(?:_?[01])+|o(?:_?[0-7])+|x(?:_?[a-f0-9])+)\b|(?:\b\d+(?:_\d+)*(?:\.(?:\d+(?:_\d+)*)?)?|\B\.\d+(?:_\d+)*)(?:e[+-]?\d+(?:_\d+)*)?j?(?!\w)/i,
          'operator': /[-+%=]=?|!=|:=|\*\*?=?|\/\/?=?|<[<=>]?|>[=>]?|[&|^~]/,
          'punctuation': /[{}[\];(),.:]/
        };
        Prism.languages.python['string-interpolation'].inside['interpolation'].inside.rest = Prism.languages.python;
        Prism.languages.py = Prism.languages.python;
        (function (Prism) {
          Prism.languages.ruby = Prism.languages.extend('clike', {
            'comment': {
              pattern: /#.*|^=begin\s[\s\S]*?^=end/m,
              greedy: true
            },
            'class-name': {
              pattern: /(\b(?:class|module)\s+|\bcatch\s+\()[\w.\\]+|\b[A-Z_]\w*(?=\s*\.\s*new\b)/,
              lookbehind: true,
              inside: { 'punctuation': /[.\\]/ }
            },
            'keyword': /\b(?:BEGIN|END|alias|and|begin|break|case|class|def|define_method|defined|do|each|else|elsif|end|ensure|extend|for|if|in|include|module|new|next|nil|not|or|prepend|private|protected|public|raise|redo|require|rescue|retry|return|self|super|then|throw|undef|unless|until|when|while|yield)\b/,
            'operator': /\.{2,3}|&\.|===|<?=>|[!=]?~|(?:&&|\|\||<<|>>|\*\*|[+\-*/%<>!^&|=])=?|[?:]/,
            'punctuation': /[(){}[\].,;]/
          });
          Prism.languages.insertBefore('ruby', 'operator', {
            'double-colon': {
              pattern: /::/,
              alias: 'punctuation'
            }
          });
          var interpolation = {
            pattern: /((?:^|[^\\])(?:\\{2})*)#\{(?:[^{}]|\{[^{}]*\})*\}/,
            lookbehind: true,
            inside: {
              'content': {
                pattern: /^(#\{)[\s\S]+(?=\}$)/,
                lookbehind: true,
                inside: Prism.languages.ruby
              },
              'delimiter': {
                pattern: /^#\{|\}$/,
                alias: 'punctuation'
              }
            }
          };
          delete Prism.languages.ruby.function;
          var percentExpression = '(?:' + [
            /([^a-zA-Z0-9\s{(\[<=])(?:(?!\1)[^\\]|\\[\s\S])*\1/.source,
            /\((?:[^()\\]|\\[\s\S]|\((?:[^()\\]|\\[\s\S])*\))*\)/.source,
            /\{(?:[^{}\\]|\\[\s\S]|\{(?:[^{}\\]|\\[\s\S])*\})*\}/.source,
            /\[(?:[^\[\]\\]|\\[\s\S]|\[(?:[^\[\]\\]|\\[\s\S])*\])*\]/.source,
            /<(?:[^<>\\]|\\[\s\S]|<(?:[^<>\\]|\\[\s\S])*>)*>/.source
          ].join('|') + ')';
          var symbolName = /(?:"(?:\\.|[^"\\\r\n])*"|(?:\b[a-zA-Z_]\w*|[^\s\0-\x7F]+)[?!]?|\$.)/.source;
          Prism.languages.insertBefore('ruby', 'keyword', {
            'regex-literal': [
              {
                pattern: RegExp(/%r/.source + percentExpression + /[egimnosux]{0,6}/.source),
                greedy: true,
                inside: {
                  'interpolation': interpolation,
                  'regex': /[\s\S]+/
                }
              },
              {
                pattern: /(^|[^/])\/(?!\/)(?:\[[^\r\n\]]+\]|\\.|[^[/\\\r\n])+\/[egimnosux]{0,6}(?=\s*(?:$|[\r\n,.;})#]))/,
                lookbehind: true,
                greedy: true,
                inside: {
                  'interpolation': interpolation,
                  'regex': /[\s\S]+/
                }
              }
            ],
            'variable': /[@$]+[a-zA-Z_]\w*(?:[?!]|\b)/,
            'symbol': [
              {
                pattern: RegExp(/(^|[^:]):/.source + symbolName),
                lookbehind: true,
                greedy: true
              },
              {
                pattern: RegExp(/([\r\n{(,][ \t]*)/.source + symbolName + /(?=:(?!:))/.source),
                lookbehind: true,
                greedy: true
              }
            ],
            'method-definition': {
              pattern: /(\bdef\s+)\w+(?:\s*\.\s*\w+)?/,
              lookbehind: true,
              inside: {
                'function': /\b\w+$/,
                'keyword': /^self\b/,
                'class-name': /^\w+/,
                'punctuation': /\./
              }
            }
          });
          Prism.languages.insertBefore('ruby', 'string', {
            'string-literal': [
              {
                pattern: RegExp(/%[qQiIwWs]?/.source + percentExpression),
                greedy: true,
                inside: {
                  'interpolation': interpolation,
                  'string': /[\s\S]+/
                }
              },
              {
                pattern: /("|')(?:#\{[^}]+\}|#(?!\{)|\\(?:\r\n|[\s\S])|(?!\1)[^\\#\r\n])*\1/,
                greedy: true,
                inside: {
                  'interpolation': interpolation,
                  'string': /[\s\S]+/
                }
              },
              {
                pattern: /<<[-~]?([a-z_]\w*)[\r\n](?:.*[\r\n])*?[\t ]*\1/i,
                alias: 'heredoc-string',
                greedy: true,
                inside: {
                  'delimiter': {
                    pattern: /^<<[-~]?[a-z_]\w*|\b[a-z_]\w*$/i,
                    inside: {
                      'symbol': /\b\w+/,
                      'punctuation': /^<<[-~]?/
                    }
                  },
                  'interpolation': interpolation,
                  'string': /[\s\S]+/
                }
              },
              {
                pattern: /<<[-~]?'([a-z_]\w*)'[\r\n](?:.*[\r\n])*?[\t ]*\1/i,
                alias: 'heredoc-string',
                greedy: true,
                inside: {
                  'delimiter': {
                    pattern: /^<<[-~]?'[a-z_]\w*'|\b[a-z_]\w*$/i,
                    inside: {
                      'symbol': /\b\w+/,
                      'punctuation': /^<<[-~]?'|'$/
                    }
                  },
                  'string': /[\s\S]+/
                }
              }
            ],
            'command-literal': [
              {
                pattern: RegExp(/%x/.source + percentExpression),
                greedy: true,
                inside: {
                  'interpolation': interpolation,
                  'command': {
                    pattern: /[\s\S]+/,
                    alias: 'string'
                  }
                }
              },
              {
                pattern: /`(?:#\{[^}]+\}|#(?!\{)|\\(?:\r\n|[\s\S])|[^\\`#\r\n])*`/,
                greedy: true,
                inside: {
                  'interpolation': interpolation,
                  'command': {
                    pattern: /[\s\S]+/,
                    alias: 'string'
                  }
                }
              }
            ]
          });
          delete Prism.languages.ruby.string;
          Prism.languages.insertBefore('ruby', 'number', {
            'builtin': /\b(?:Array|Bignum|Binding|Class|Continuation|Dir|Exception|FalseClass|File|Fixnum|Float|Hash|IO|Integer|MatchData|Method|Module|NilClass|Numeric|Object|Proc|Range|Regexp|Stat|String|Struct|Symbol|TMS|Thread|ThreadGroup|Time|TrueClass)\b/,
            'constant': /\b[A-Z][A-Z0-9_]*(?:[?!]|\b)/
          });
          Prism.languages.rb = Prism.languages.ruby;
        }(Prism));
        var Prism$1 = prismCore.exports;
        var prismjs = { boltExport: Prism$1 };
        return prismjs;
      }));
      var prism = window.Prism;
      window.Prism = oldprism;
      return prism;
    }(undefined, exports$1, module));
    var Prism$1 = module.exports.boltExport;

    var getLanguages$1 = function (editor) {
      return editor.getParam('codesample_languages');
    };
    var useGlobalPrismJS = function (editor) {
      return editor.getParam('codesample_global_prismjs', false, 'boolean');
    };

    var get = function (editor) {
      return Global.Prism && useGlobalPrismJS(editor) ? Global.Prism : Prism$1;
    };

    var getSelectedCodeSample = function (editor) {
      var node = editor.selection ? editor.selection.getNode() : null;
      return someIf(isCodeSample(node), node);
    };
    var insertCodeSample = function (editor, language, code) {
      editor.undoManager.transact(function () {
        var node = getSelectedCodeSample(editor);
        code = global$1.DOM.encode(code);
        return node.fold(function () {
          editor.insertContent('<pre id="__new" class="language-' + language + '">' + code + '</pre>');
          editor.selection.select(editor.$('#__new').removeAttr('id')[0]);
        }, function (n) {
          editor.dom.setAttrib(n, 'class', 'language-' + language);
          n.innerHTML = code;
          get(editor).highlightElement(n);
          editor.selection.select(n);
        });
      });
    };
    var getCurrentCode = function (editor) {
      var node = getSelectedCodeSample(editor);
      return node.fold(constant(''), function (n) {
        return n.textContent;
      });
    };

    var getLanguages = function (editor) {
      var defaultLanguages = [
        {
          text: 'HTML/XML',
          value: 'markup'
        },
        {
          text: 'JavaScript',
          value: 'javascript'
        },
        {
          text: 'CSS',
          value: 'css'
        },
        {
          text: 'PHP',
          value: 'php'
        },
        {
          text: 'Ruby',
          value: 'ruby'
        },
        {
          text: 'Python',
          value: 'python'
        },
        {
          text: 'Java',
          value: 'java'
        },
        {
          text: 'C',
          value: 'c'
        },
        {
          text: 'C#',
          value: 'csharp'
        },
        {
          text: 'C++',
          value: 'cpp'
        }
      ];
      var customLanguages = getLanguages$1(editor);
      return customLanguages ? customLanguages : defaultLanguages;
    };
    var getCurrentLanguage = function (editor, fallback) {
      var node = getSelectedCodeSample(editor);
      return node.fold(function () {
        return fallback;
      }, function (n) {
        var matches = n.className.match(/language-(\w+)/);
        return matches ? matches[1] : fallback;
      });
    };

    var open = function (editor) {
      var languages = getLanguages(editor);
      var defaultLanguage = head(languages).fold(constant(''), function (l) {
        return l.value;
      });
      var currentLanguage = getCurrentLanguage(editor, defaultLanguage);
      var currentCode = getCurrentCode(editor);
      editor.windowManager.open({
        title: 'Insert/Edit Code Sample',
        size: 'large',
        body: {
          type: 'panel',
          items: [
            {
              type: 'selectbox',
              name: 'language',
              label: 'Language',
              items: languages
            },
            {
              type: 'textarea',
              name: 'code',
              label: 'Code view'
            }
          ]
        },
        buttons: [
          {
            type: 'cancel',
            name: 'cancel',
            text: 'Cancel'
          },
          {
            type: 'submit',
            name: 'save',
            text: 'Save',
            primary: true
          }
        ],
        initialData: {
          language: currentLanguage,
          code: currentCode
        },
        onSubmit: function (api) {
          var data = api.getData();
          insertCodeSample(editor, data.language, data.code);
          api.close();
        }
      });
    };

    var register$1 = function (editor) {
      editor.addCommand('codesample', function () {
        var node = editor.selection.getNode();
        if (editor.selection.isCollapsed() || isCodeSample(node)) {
          open(editor);
        } else {
          editor.formatter.toggle('code');
        }
      });
    };

    var setup = function (editor) {
      var $ = editor.$;
      editor.on('PreProcess', function (e) {
        $('pre[contenteditable=false]', e.node).filter(trimArg(isCodeSample)).each(function (idx, elm) {
          var $elm = $(elm), code = elm.textContent;
          $elm.attr('class', $.trim($elm.attr('class')));
          $elm.removeAttr('contentEditable');
          $elm.empty().append($('<code></code>').each(function () {
            this.textContent = code;
          }));
        });
      });
      editor.on('SetContent', function () {
        var unprocessedCodeSamples = $('pre').filter(trimArg(isCodeSample)).filter(function (idx, elm) {
          return elm.contentEditable !== 'false';
        });
        if (unprocessedCodeSamples.length) {
          editor.undoManager.transact(function () {
            unprocessedCodeSamples.each(function (idx, elm) {
              $(elm).find('br').each(function (idx, elm) {
                elm.parentNode.replaceChild(editor.getDoc().createTextNode('\n'), elm);
              });
              elm.contentEditable = 'false';
              elm.innerHTML = editor.dom.encode(elm.textContent);
              get(editor).highlightElement(elm);
              elm.className = $.trim(elm.className);
            });
          });
        }
      });
    };

    var isCodeSampleSelection = function (editor) {
      var node = editor.selection.getStart();
      return editor.dom.is(node, 'pre[class*="language-"]');
    };
    var register = function (editor) {
      var onAction = function () {
        return editor.execCommand('codesample');
      };
      editor.ui.registry.addToggleButton('codesample', {
        icon: 'code-sample',
        tooltip: 'Insert/edit code sample',
        onAction: onAction,
        onSetup: function (api) {
          var nodeChangeHandler = function () {
            api.setActive(isCodeSampleSelection(editor));
          };
          editor.on('NodeChange', nodeChangeHandler);
          return function () {
            return editor.off('NodeChange', nodeChangeHandler);
          };
        }
      });
      editor.ui.registry.addMenuItem('codesample', {
        text: 'Code sample...',
        icon: 'code-sample',
        onAction: onAction
      });
    };

    function Plugin () {
      global$2.add('codesample', function (editor) {
        setup(editor);
        register(editor);
        register$1(editor);
        editor.on('dblclick', function (ev) {
          if (isCodeSample(ev.target)) {
            open(editor);
          }
        });
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/directionality/index.js":
/*!**************************************************************!*\
  !*** ./node_modules/tinymce/plugins/directionality/index.js ***!
  \**************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "directionality" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/directionality')
//   ES2015:
//     import 'tinymce/plugins/directionality'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/directionality/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/directionality/plugin.js":
/*!***************************************************************!*\
  !*** ./node_modules/tinymce/plugins/directionality/plugin.js ***!
  \***************************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType$1 = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var isString = isType$1('string');
    var isBoolean = isSimpleType('boolean');
    var isNullable = function (a) {
      return a === null || a === undefined;
    };
    var isNonNullable = function (a) {
      return !isNullable(a);
    };
    var isFunction = isSimpleType('function');
    var isNumber = isSimpleType('number');

    var noop = function () {
    };
    var compose1 = function (fbc, fab) {
      return function (a) {
        return fbc(fab(a));
      };
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var filter = function (xs, pred) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          r.push(x);
        }
      }
      return r;
    };

    var DOCUMENT = 9;
    var DOCUMENT_FRAGMENT = 11;
    var ELEMENT = 1;
    var TEXT = 3;

    var fromHtml = function (html, scope) {
      var doc = scope || document;
      var div = doc.createElement('div');
      div.innerHTML = html;
      if (!div.hasChildNodes() || div.childNodes.length > 1) {
        console.error('HTML does not have a single root node', html);
        throw new Error('HTML must have a single root node');
      }
      return fromDom(div.childNodes[0]);
    };
    var fromTag = function (tag, scope) {
      var doc = scope || document;
      var node = doc.createElement(tag);
      return fromDom(node);
    };
    var fromText = function (text, scope) {
      var doc = scope || document;
      var node = doc.createTextNode(text);
      return fromDom(node);
    };
    var fromDom = function (node) {
      if (node === null || node === undefined) {
        throw new Error('Node cannot be null or undefined');
      }
      return { dom: node };
    };
    var fromPoint = function (docElm, x, y) {
      return Optional.from(docElm.dom.elementFromPoint(x, y)).map(fromDom);
    };
    var SugarElement = {
      fromHtml: fromHtml,
      fromTag: fromTag,
      fromText: fromText,
      fromDom: fromDom,
      fromPoint: fromPoint
    };

    var is = function (element, selector) {
      var dom = element.dom;
      if (dom.nodeType !== ELEMENT) {
        return false;
      } else {
        var elem = dom;
        if (elem.matches !== undefined) {
          return elem.matches(selector);
        } else if (elem.msMatchesSelector !== undefined) {
          return elem.msMatchesSelector(selector);
        } else if (elem.webkitMatchesSelector !== undefined) {
          return elem.webkitMatchesSelector(selector);
        } else if (elem.mozMatchesSelector !== undefined) {
          return elem.mozMatchesSelector(selector);
        } else {
          throw new Error('Browser lacks native selectors');
        }
      }
    };

    typeof window !== 'undefined' ? window : Function('return this;')();

    var name = function (element) {
      var r = element.dom.nodeName;
      return r.toLowerCase();
    };
    var type = function (element) {
      return element.dom.nodeType;
    };
    var isType = function (t) {
      return function (element) {
        return type(element) === t;
      };
    };
    var isElement = isType(ELEMENT);
    var isText = isType(TEXT);
    var isDocument = isType(DOCUMENT);
    var isDocumentFragment = isType(DOCUMENT_FRAGMENT);
    var isTag = function (tag) {
      return function (e) {
        return isElement(e) && name(e) === tag;
      };
    };

    var owner = function (element) {
      return SugarElement.fromDom(element.dom.ownerDocument);
    };
    var documentOrOwner = function (dos) {
      return isDocument(dos) ? dos : owner(dos);
    };
    var parent = function (element) {
      return Optional.from(element.dom.parentNode).map(SugarElement.fromDom);
    };
    var children$2 = function (element) {
      return map(element.dom.childNodes, SugarElement.fromDom);
    };

    var rawSet = function (dom, key, value) {
      if (isString(value) || isBoolean(value) || isNumber(value)) {
        dom.setAttribute(key, value + '');
      } else {
        console.error('Invalid call to Attribute.set. Key ', key, ':: Value ', value, ':: Element ', dom);
        throw new Error('Attribute value was not simple');
      }
    };
    var set = function (element, key, value) {
      rawSet(element.dom, key, value);
    };
    var remove = function (element, key) {
      element.dom.removeAttribute(key);
    };

    var isShadowRoot = function (dos) {
      return isDocumentFragment(dos) && isNonNullable(dos.dom.host);
    };
    var supported = isFunction(Element.prototype.attachShadow) && isFunction(Node.prototype.getRootNode);
    var getRootNode = supported ? function (e) {
      return SugarElement.fromDom(e.dom.getRootNode());
    } : documentOrOwner;
    var getShadowRoot = function (e) {
      var r = getRootNode(e);
      return isShadowRoot(r) ? Optional.some(r) : Optional.none();
    };
    var getShadowHost = function (e) {
      return SugarElement.fromDom(e.dom.host);
    };

    var inBody = function (element) {
      var dom = isText(element) ? element.dom.parentNode : element.dom;
      if (dom === undefined || dom === null || dom.ownerDocument === null) {
        return false;
      }
      var doc = dom.ownerDocument;
      return getShadowRoot(SugarElement.fromDom(dom)).fold(function () {
        return doc.body.contains(dom);
      }, compose1(inBody, getShadowHost));
    };

    var ancestor$1 = function (scope, predicate, isRoot) {
      var element = scope.dom;
      var stop = isFunction(isRoot) ? isRoot : never;
      while (element.parentNode) {
        element = element.parentNode;
        var el = SugarElement.fromDom(element);
        if (predicate(el)) {
          return Optional.some(el);
        } else if (stop(el)) {
          break;
        }
      }
      return Optional.none();
    };

    var ancestor = function (scope, selector, isRoot) {
      return ancestor$1(scope, function (e) {
        return is(e, selector);
      }, isRoot);
    };

    var isSupported = function (dom) {
      return dom.style !== undefined && isFunction(dom.style.getPropertyValue);
    };

    var get = function (element, property) {
      var dom = element.dom;
      var styles = window.getComputedStyle(dom);
      var r = styles.getPropertyValue(property);
      return r === '' && !inBody(element) ? getUnsafeProperty(dom, property) : r;
    };
    var getUnsafeProperty = function (dom, property) {
      return isSupported(dom) ? dom.style.getPropertyValue(property) : '';
    };

    var getDirection = function (element) {
      return get(element, 'direction') === 'rtl' ? 'rtl' : 'ltr';
    };

    var children$1 = function (scope, predicate) {
      return filter(children$2(scope), predicate);
    };

    var children = function (scope, selector) {
      return children$1(scope, function (e) {
        return is(e, selector);
      });
    };

    var getParentElement = function (element) {
      return parent(element).filter(isElement);
    };
    var getNormalizedBlock = function (element, isListItem) {
      var normalizedElement = isListItem ? ancestor(element, 'ol,ul') : Optional.some(element);
      return normalizedElement.getOr(element);
    };
    var isListItem = isTag('li');
    var setDir = function (editor, dir) {
      var selectedBlocks = editor.selection.getSelectedBlocks();
      if (selectedBlocks.length > 0) {
        each(selectedBlocks, function (block) {
          var blockElement = SugarElement.fromDom(block);
          var isBlockElementListItem = isListItem(blockElement);
          var normalizedBlock = getNormalizedBlock(blockElement, isBlockElementListItem);
          var normalizedBlockParent = getParentElement(normalizedBlock);
          normalizedBlockParent.each(function (parent) {
            var parentDirection = getDirection(parent);
            if (parentDirection !== dir) {
              set(normalizedBlock, 'dir', dir);
            } else if (getDirection(normalizedBlock) !== dir) {
              remove(normalizedBlock, 'dir');
            }
            if (isBlockElementListItem) {
              var listItems = children(normalizedBlock, 'li[dir]');
              each(listItems, function (listItem) {
                return remove(listItem, 'dir');
              });
            }
          });
        });
        editor.nodeChanged();
      }
    };

    var register$1 = function (editor) {
      editor.addCommand('mceDirectionLTR', function () {
        setDir(editor, 'ltr');
      });
      editor.addCommand('mceDirectionRTL', function () {
        setDir(editor, 'rtl');
      });
    };

    var getNodeChangeHandler = function (editor, dir) {
      return function (api) {
        var nodeChangeHandler = function (e) {
          var element = SugarElement.fromDom(e.element);
          api.setActive(getDirection(element) === dir);
        };
        editor.on('NodeChange', nodeChangeHandler);
        return function () {
          return editor.off('NodeChange', nodeChangeHandler);
        };
      };
    };
    var register = function (editor) {
      editor.ui.registry.addToggleButton('ltr', {
        tooltip: 'Left to right',
        icon: 'ltr',
        onAction: function () {
          return editor.execCommand('mceDirectionLTR');
        },
        onSetup: getNodeChangeHandler(editor, 'ltr')
      });
      editor.ui.registry.addToggleButton('rtl', {
        tooltip: 'Right to left',
        icon: 'rtl',
        onAction: function () {
          return editor.execCommand('mceDirectionRTL');
        },
        onSetup: getNodeChangeHandler(editor, 'rtl')
      });
    };

    function Plugin () {
      global.add('directionality', function (editor) {
        register$1(editor);
        register(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/emoticons/index.js":
/*!*********************************************************!*\
  !*** ./node_modules/tinymce/plugins/emoticons/index.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "emoticons" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/emoticons')
//   ES2015:
//     import 'tinymce/plugins/emoticons'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/emoticons/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/emoticons/js/emojis.js":
/*!*************************************************************!*\
  !*** ./node_modules/tinymce/plugins/emoticons/js/emojis.js ***!
  \*************************************************************/
/***/ (() => {

// Source: npm package: emojilib, file:emojis.json
window.tinymce.Resource.add("tinymce.plugins.emoticons", {
  grinning: {
    keywords: [ "face", "smile", "happy", "joy", ":D", "grin" ],
    char: "\ud83d\ude00",
    fitzpatrick_scale: false,
    category: "people"
  },
  grimacing: {
    keywords: [ "face", "grimace", "teeth" ],
    char: "\ud83d\ude2c",
    fitzpatrick_scale: false,
    category: "people"
  },
  grin: {
    keywords: [ "face", "happy", "smile", "joy", "kawaii" ],
    char: "\ud83d\ude01",
    fitzpatrick_scale: false,
    category: "people"
  },
  joy: {
    keywords: [ "face", "cry", "tears", "weep", "happy", "happytears", "haha" ],
    char: "\ud83d\ude02",
    fitzpatrick_scale: false,
    category: "people"
  },
  rofl: {
    keywords: [ "face", "rolling", "floor", "laughing", "lol", "haha" ],
    char: "\ud83e\udd23",
    fitzpatrick_scale: false,
    category: "people"
  },
  partying: {
    keywords: [ "face", "celebration", "woohoo" ],
    char: "\ud83e\udd73",
    fitzpatrick_scale: false,
    category: "people"
  },
  smiley: {
    keywords: [ "face", "happy", "joy", "haha", ":D", ":)", "smile", "funny" ],
    char: "\ud83d\ude03",
    fitzpatrick_scale: false,
    category: "people"
  },
  smile: {
    keywords: [ "face", "happy", "joy", "funny", "haha", "laugh", "like", ":D", ":)" ],
    char: "\ud83d\ude04",
    fitzpatrick_scale: false,
    category: "people"
  },
  sweat_smile: {
    keywords: [ "face", "hot", "happy", "laugh", "sweat", "smile", "relief" ],
    char: "\ud83d\ude05",
    fitzpatrick_scale: false,
    category: "people"
  },
  laughing: {
    keywords: [ "happy", "joy", "lol", "satisfied", "haha", "face", "glad", "XD", "laugh" ],
    char: "\ud83d\ude06",
    fitzpatrick_scale: false,
    category: "people"
  },
  innocent: {
    keywords: [ "face", "angel", "heaven", "halo" ],
    char: "\ud83d\ude07",
    fitzpatrick_scale: false,
    category: "people"
  },
  wink: {
    keywords: [ "face", "happy", "mischievous", "secret", ";)", "smile", "eye" ],
    char: "\ud83d\ude09",
    fitzpatrick_scale: false,
    category: "people"
  },
  blush: {
    keywords: [ "face", "smile", "happy", "flushed", "crush", "embarrassed", "shy", "joy" ],
    char: "\ud83d\ude0a",
    fitzpatrick_scale: false,
    category: "people"
  },
  slightly_smiling_face: {
    keywords: [ "face", "smile" ],
    char: "\ud83d\ude42",
    fitzpatrick_scale: false,
    category: "people"
  },
  upside_down_face: {
    keywords: [ "face", "flipped", "silly", "smile" ],
    char: "\ud83d\ude43",
    fitzpatrick_scale: false,
    category: "people"
  },
  relaxed: {
    keywords: [ "face", "blush", "massage", "happiness" ],
    char: "\u263a\ufe0f",
    fitzpatrick_scale: false,
    category: "people"
  },
  yum: {
    keywords: [ "happy", "joy", "tongue", "smile", "face", "silly", "yummy", "nom", "delicious", "savouring" ],
    char: "\ud83d\ude0b",
    fitzpatrick_scale: false,
    category: "people"
  },
  relieved: {
    keywords: [ "face", "relaxed", "phew", "massage", "happiness" ],
    char: "\ud83d\ude0c",
    fitzpatrick_scale: false,
    category: "people"
  },
  heart_eyes: {
    keywords: [ "face", "love", "like", "affection", "valentines", "infatuation", "crush", "heart" ],
    char: "\ud83d\ude0d",
    fitzpatrick_scale: false,
    category: "people"
  },
  smiling_face_with_three_hearts: {
    keywords: [ "face", "love", "like", "affection", "valentines", "infatuation", "crush", "hearts", "adore" ],
    char: "\ud83e\udd70",
    fitzpatrick_scale: false,
    category: "people"
  },
  kissing_heart: {
    keywords: [ "face", "love", "like", "affection", "valentines", "infatuation", "kiss" ],
    char: "\ud83d\ude18",
    fitzpatrick_scale: false,
    category: "people"
  },
  kissing: {
    keywords: [ "love", "like", "face", "3", "valentines", "infatuation", "kiss" ],
    char: "\ud83d\ude17",
    fitzpatrick_scale: false,
    category: "people"
  },
  kissing_smiling_eyes: {
    keywords: [ "face", "affection", "valentines", "infatuation", "kiss" ],
    char: "\ud83d\ude19",
    fitzpatrick_scale: false,
    category: "people"
  },
  kissing_closed_eyes: {
    keywords: [ "face", "love", "like", "affection", "valentines", "infatuation", "kiss" ],
    char: "\ud83d\ude1a",
    fitzpatrick_scale: false,
    category: "people"
  },
  stuck_out_tongue_winking_eye: {
    keywords: [ "face", "prank", "childish", "playful", "mischievous", "smile", "wink", "tongue" ],
    char: "\ud83d\ude1c",
    fitzpatrick_scale: false,
    category: "people"
  },
  zany: {
    keywords: [ "face", "goofy", "crazy" ],
    char: "\ud83e\udd2a",
    fitzpatrick_scale: false,
    category: "people"
  },
  raised_eyebrow: {
    keywords: [ "face", "distrust", "scepticism", "disapproval", "disbelief", "surprise" ],
    char: "\ud83e\udd28",
    fitzpatrick_scale: false,
    category: "people"
  },
  monocle: {
    keywords: [ "face", "stuffy", "wealthy" ],
    char: "\ud83e\uddd0",
    fitzpatrick_scale: false,
    category: "people"
  },
  stuck_out_tongue_closed_eyes: {
    keywords: [ "face", "prank", "playful", "mischievous", "smile", "tongue" ],
    char: "\ud83d\ude1d",
    fitzpatrick_scale: false,
    category: "people"
  },
  stuck_out_tongue: {
    keywords: [ "face", "prank", "childish", "playful", "mischievous", "smile", "tongue" ],
    char: "\ud83d\ude1b",
    fitzpatrick_scale: false,
    category: "people"
  },
  money_mouth_face: {
    keywords: [ "face", "rich", "dollar", "money" ],
    char: "\ud83e\udd11",
    fitzpatrick_scale: false,
    category: "people"
  },
  nerd_face: {
    keywords: [ "face", "nerdy", "geek", "dork" ],
    char: "\ud83e\udd13",
    fitzpatrick_scale: false,
    category: "people"
  },
  sunglasses: {
    keywords: [ "face", "cool", "smile", "summer", "beach", "sunglass" ],
    char: "\ud83d\ude0e",
    fitzpatrick_scale: false,
    category: "people"
  },
  star_struck: {
    keywords: [ "face", "smile", "starry", "eyes", "grinning" ],
    char: "\ud83e\udd29",
    fitzpatrick_scale: false,
    category: "people"
  },
  clown_face: {
    keywords: [ "face" ],
    char: "\ud83e\udd21",
    fitzpatrick_scale: false,
    category: "people"
  },
  cowboy_hat_face: {
    keywords: [ "face", "cowgirl", "hat" ],
    char: "\ud83e\udd20",
    fitzpatrick_scale: false,
    category: "people"
  },
  hugs: {
    keywords: [ "face", "smile", "hug" ],
    char: "\ud83e\udd17",
    fitzpatrick_scale: false,
    category: "people"
  },
  smirk: {
    keywords: [ "face", "smile", "mean", "prank", "smug", "sarcasm" ],
    char: "\ud83d\ude0f",
    fitzpatrick_scale: false,
    category: "people"
  },
  no_mouth: {
    keywords: [ "face", "hellokitty" ],
    char: "\ud83d\ude36",
    fitzpatrick_scale: false,
    category: "people"
  },
  neutral_face: {
    keywords: [ "indifference", "meh", ":|", "neutral" ],
    char: "\ud83d\ude10",
    fitzpatrick_scale: false,
    category: "people"
  },
  expressionless: {
    keywords: [ "face", "indifferent", "-_-", "meh", "deadpan" ],
    char: "\ud83d\ude11",
    fitzpatrick_scale: false,
    category: "people"
  },
  unamused: {
    keywords: [ "indifference", "bored", "straight face", "serious", "sarcasm", "unimpressed", "skeptical", "dubious", "side_eye" ],
    char: "\ud83d\ude12",
    fitzpatrick_scale: false,
    category: "people"
  },
  roll_eyes: {
    keywords: [ "face", "eyeroll", "frustrated" ],
    char: "\ud83d\ude44",
    fitzpatrick_scale: false,
    category: "people"
  },
  thinking: {
    keywords: [ "face", "hmmm", "think", "consider" ],
    char: "\ud83e\udd14",
    fitzpatrick_scale: false,
    category: "people"
  },
  lying_face: {
    keywords: [ "face", "lie", "pinocchio" ],
    char: "\ud83e\udd25",
    fitzpatrick_scale: false,
    category: "people"
  },
  hand_over_mouth: {
    keywords: [ "face", "whoops", "shock", "surprise" ],
    char: "\ud83e\udd2d",
    fitzpatrick_scale: false,
    category: "people"
  },
  shushing: {
    keywords: [ "face", "quiet", "shhh" ],
    char: "\ud83e\udd2b",
    fitzpatrick_scale: false,
    category: "people"
  },
  symbols_over_mouth: {
    keywords: [ "face", "swearing", "cursing", "cussing", "profanity", "expletive" ],
    char: "\ud83e\udd2c",
    fitzpatrick_scale: false,
    category: "people"
  },
  exploding_head: {
    keywords: [ "face", "shocked", "mind", "blown" ],
    char: "\ud83e\udd2f",
    fitzpatrick_scale: false,
    category: "people"
  },
  flushed: {
    keywords: [ "face", "blush", "shy", "flattered" ],
    char: "\ud83d\ude33",
    fitzpatrick_scale: false,
    category: "people"
  },
  disappointed: {
    keywords: [ "face", "sad", "upset", "depressed", ":(" ],
    char: "\ud83d\ude1e",
    fitzpatrick_scale: false,
    category: "people"
  },
  worried: {
    keywords: [ "face", "concern", "nervous", ":(" ],
    char: "\ud83d\ude1f",
    fitzpatrick_scale: false,
    category: "people"
  },
  angry: {
    keywords: [ "mad", "face", "annoyed", "frustrated" ],
    char: "\ud83d\ude20",
    fitzpatrick_scale: false,
    category: "people"
  },
  rage: {
    keywords: [ "angry", "mad", "hate", "despise" ],
    char: "\ud83d\ude21",
    fitzpatrick_scale: false,
    category: "people"
  },
  pensive: {
    keywords: [ "face", "sad", "depressed", "upset" ],
    char: "\ud83d\ude14",
    fitzpatrick_scale: false,
    category: "people"
  },
  confused: {
    keywords: [ "face", "indifference", "huh", "weird", "hmmm", ":/" ],
    char: "\ud83d\ude15",
    fitzpatrick_scale: false,
    category: "people"
  },
  slightly_frowning_face: {
    keywords: [ "face", "frowning", "disappointed", "sad", "upset" ],
    char: "\ud83d\ude41",
    fitzpatrick_scale: false,
    category: "people"
  },
  frowning_face: {
    keywords: [ "face", "sad", "upset", "frown" ],
    char: "\u2639",
    fitzpatrick_scale: false,
    category: "people"
  },
  persevere: {
    keywords: [ "face", "sick", "no", "upset", "oops" ],
    char: "\ud83d\ude23",
    fitzpatrick_scale: false,
    category: "people"
  },
  confounded: {
    keywords: [ "face", "confused", "sick", "unwell", "oops", ":S" ],
    char: "\ud83d\ude16",
    fitzpatrick_scale: false,
    category: "people"
  },
  tired_face: {
    keywords: [ "sick", "whine", "upset", "frustrated" ],
    char: "\ud83d\ude2b",
    fitzpatrick_scale: false,
    category: "people"
  },
  weary: {
    keywords: [ "face", "tired", "sleepy", "sad", "frustrated", "upset" ],
    char: "\ud83d\ude29",
    fitzpatrick_scale: false,
    category: "people"
  },
  pleading: {
    keywords: [ "face", "begging", "mercy" ],
    char: "\ud83e\udd7a",
    fitzpatrick_scale: false,
    category: "people"
  },
  triumph: {
    keywords: [ "face", "gas", "phew", "proud", "pride" ],
    char: "\ud83d\ude24",
    fitzpatrick_scale: false,
    category: "people"
  },
  open_mouth: {
    keywords: [ "face", "surprise", "impressed", "wow", "whoa", ":O" ],
    char: "\ud83d\ude2e",
    fitzpatrick_scale: false,
    category: "people"
  },
  scream: {
    keywords: [ "face", "munch", "scared", "omg" ],
    char: "\ud83d\ude31",
    fitzpatrick_scale: false,
    category: "people"
  },
  fearful: {
    keywords: [ "face", "scared", "terrified", "nervous", "oops", "huh" ],
    char: "\ud83d\ude28",
    fitzpatrick_scale: false,
    category: "people"
  },
  cold_sweat: {
    keywords: [ "face", "nervous", "sweat" ],
    char: "\ud83d\ude30",
    fitzpatrick_scale: false,
    category: "people"
  },
  hushed: {
    keywords: [ "face", "woo", "shh" ],
    char: "\ud83d\ude2f",
    fitzpatrick_scale: false,
    category: "people"
  },
  frowning: {
    keywords: [ "face", "aw", "what" ],
    char: "\ud83d\ude26",
    fitzpatrick_scale: false,
    category: "people"
  },
  anguished: {
    keywords: [ "face", "stunned", "nervous" ],
    char: "\ud83d\ude27",
    fitzpatrick_scale: false,
    category: "people"
  },
  cry: {
    keywords: [ "face", "tears", "sad", "depressed", "upset", ":'(" ],
    char: "\ud83d\ude22",
    fitzpatrick_scale: false,
    category: "people"
  },
  disappointed_relieved: {
    keywords: [ "face", "phew", "sweat", "nervous" ],
    char: "\ud83d\ude25",
    fitzpatrick_scale: false,
    category: "people"
  },
  drooling_face: {
    keywords: [ "face" ],
    char: "\ud83e\udd24",
    fitzpatrick_scale: false,
    category: "people"
  },
  sleepy: {
    keywords: [ "face", "tired", "rest", "nap" ],
    char: "\ud83d\ude2a",
    fitzpatrick_scale: false,
    category: "people"
  },
  sweat: {
    keywords: [ "face", "hot", "sad", "tired", "exercise" ],
    char: "\ud83d\ude13",
    fitzpatrick_scale: false,
    category: "people"
  },
  hot: {
    keywords: [ "face", "feverish", "heat", "red", "sweating" ],
    char: "\ud83e\udd75",
    fitzpatrick_scale: false,
    category: "people"
  },
  cold: {
    keywords: [ "face", "blue", "freezing", "frozen", "frostbite", "icicles" ],
    char: "\ud83e\udd76",
    fitzpatrick_scale: false,
    category: "people"
  },
  sob: {
    keywords: [ "face", "cry", "tears", "sad", "upset", "depressed" ],
    char: "\ud83d\ude2d",
    fitzpatrick_scale: false,
    category: "people"
  },
  dizzy_face: {
    keywords: [ "spent", "unconscious", "xox", "dizzy" ],
    char: "\ud83d\ude35",
    fitzpatrick_scale: false,
    category: "people"
  },
  astonished: {
    keywords: [ "face", "xox", "surprised", "poisoned" ],
    char: "\ud83d\ude32",
    fitzpatrick_scale: false,
    category: "people"
  },
  zipper_mouth_face: {
    keywords: [ "face", "sealed", "zipper", "secret" ],
    char: "\ud83e\udd10",
    fitzpatrick_scale: false,
    category: "people"
  },
  nauseated_face: {
    keywords: [ "face", "vomit", "gross", "green", "sick", "throw up", "ill" ],
    char: "\ud83e\udd22",
    fitzpatrick_scale: false,
    category: "people"
  },
  sneezing_face: {
    keywords: [ "face", "gesundheit", "sneeze", "sick", "allergy" ],
    char: "\ud83e\udd27",
    fitzpatrick_scale: false,
    category: "people"
  },
  vomiting: {
    keywords: [ "face", "sick" ],
    char: "\ud83e\udd2e",
    fitzpatrick_scale: false,
    category: "people"
  },
  mask: {
    keywords: [ "face", "sick", "ill", "disease" ],
    char: "\ud83d\ude37",
    fitzpatrick_scale: false,
    category: "people"
  },
  face_with_thermometer: {
    keywords: [ "sick", "temperature", "thermometer", "cold", "fever" ],
    char: "\ud83e\udd12",
    fitzpatrick_scale: false,
    category: "people"
  },
  face_with_head_bandage: {
    keywords: [ "injured", "clumsy", "bandage", "hurt" ],
    char: "\ud83e\udd15",
    fitzpatrick_scale: false,
    category: "people"
  },
  woozy: {
    keywords: [ "face", "dizzy", "intoxicated", "tipsy", "wavy" ],
    char: "\ud83e\udd74",
    fitzpatrick_scale: false,
    category: "people"
  },
  sleeping: {
    keywords: [ "face", "tired", "sleepy", "night", "zzz" ],
    char: "\ud83d\ude34",
    fitzpatrick_scale: false,
    category: "people"
  },
  zzz: {
    keywords: [ "sleepy", "tired", "dream" ],
    char: "\ud83d\udca4",
    fitzpatrick_scale: false,
    category: "people"
  },
  poop: {
    keywords: [ "hankey", "shitface", "fail", "turd", "shit" ],
    char: "\ud83d\udca9",
    fitzpatrick_scale: false,
    category: "people"
  },
  smiling_imp: {
    keywords: [ "devil", "horns" ],
    char: "\ud83d\ude08",
    fitzpatrick_scale: false,
    category: "people"
  },
  imp: {
    keywords: [ "devil", "angry", "horns" ],
    char: "\ud83d\udc7f",
    fitzpatrick_scale: false,
    category: "people"
  },
  japanese_ogre: {
    keywords: [ "monster", "red", "mask", "halloween", "scary", "creepy", "devil", "demon", "japanese", "ogre" ],
    char: "\ud83d\udc79",
    fitzpatrick_scale: false,
    category: "people"
  },
  japanese_goblin: {
    keywords: [ "red", "evil", "mask", "monster", "scary", "creepy", "japanese", "goblin" ],
    char: "\ud83d\udc7a",
    fitzpatrick_scale: false,
    category: "people"
  },
  skull: {
    keywords: [ "dead", "skeleton", "creepy", "death" ],
    char: "\ud83d\udc80",
    fitzpatrick_scale: false,
    category: "people"
  },
  ghost: {
    keywords: [ "halloween", "spooky", "scary" ],
    char: "\ud83d\udc7b",
    fitzpatrick_scale: false,
    category: "people"
  },
  alien: {
    keywords: [ "UFO", "paul", "weird", "outer_space" ],
    char: "\ud83d\udc7d",
    fitzpatrick_scale: false,
    category: "people"
  },
  robot: {
    keywords: [ "computer", "machine", "bot" ],
    char: "\ud83e\udd16",
    fitzpatrick_scale: false,
    category: "people"
  },
  smiley_cat: {
    keywords: [ "animal", "cats", "happy", "smile" ],
    char: "\ud83d\ude3a",
    fitzpatrick_scale: false,
    category: "people"
  },
  smile_cat: {
    keywords: [ "animal", "cats", "smile" ],
    char: "\ud83d\ude38",
    fitzpatrick_scale: false,
    category: "people"
  },
  joy_cat: {
    keywords: [ "animal", "cats", "haha", "happy", "tears" ],
    char: "\ud83d\ude39",
    fitzpatrick_scale: false,
    category: "people"
  },
  heart_eyes_cat: {
    keywords: [ "animal", "love", "like", "affection", "cats", "valentines", "heart" ],
    char: "\ud83d\ude3b",
    fitzpatrick_scale: false,
    category: "people"
  },
  smirk_cat: {
    keywords: [ "animal", "cats", "smirk" ],
    char: "\ud83d\ude3c",
    fitzpatrick_scale: false,
    category: "people"
  },
  kissing_cat: {
    keywords: [ "animal", "cats", "kiss" ],
    char: "\ud83d\ude3d",
    fitzpatrick_scale: false,
    category: "people"
  },
  scream_cat: {
    keywords: [ "animal", "cats", "munch", "scared", "scream" ],
    char: "\ud83d\ude40",
    fitzpatrick_scale: false,
    category: "people"
  },
  crying_cat_face: {
    keywords: [ "animal", "tears", "weep", "sad", "cats", "upset", "cry" ],
    char: "\ud83d\ude3f",
    fitzpatrick_scale: false,
    category: "people"
  },
  pouting_cat: {
    keywords: [ "animal", "cats" ],
    char: "\ud83d\ude3e",
    fitzpatrick_scale: false,
    category: "people"
  },
  palms_up: {
    keywords: [ "hands", "gesture", "cupped", "prayer" ],
    char: "\ud83e\udd32",
    fitzpatrick_scale: true,
    category: "people"
  },
  raised_hands: {
    keywords: [ "gesture", "hooray", "yea", "celebration", "hands" ],
    char: "\ud83d\ude4c",
    fitzpatrick_scale: true,
    category: "people"
  },
  clap: {
    keywords: [ "hands", "praise", "applause", "congrats", "yay" ],
    char: "\ud83d\udc4f",
    fitzpatrick_scale: true,
    category: "people"
  },
  wave: {
    keywords: [ "hands", "gesture", "goodbye", "solong", "farewell", "hello", "hi", "palm" ],
    char: "\ud83d\udc4b",
    fitzpatrick_scale: true,
    category: "people"
  },
  call_me_hand: {
    keywords: [ "hands", "gesture" ],
    char: "\ud83e\udd19",
    fitzpatrick_scale: true,
    category: "people"
  },
  "+1": {
    keywords: [ "thumbsup", "yes", "awesome", "good", "agree", "accept", "cool", "hand", "like" ],
    char: "\ud83d\udc4d",
    fitzpatrick_scale: true,
    category: "people"
  },
  "-1": {
    keywords: [ "thumbsdown", "no", "dislike", "hand" ],
    char: "\ud83d\udc4e",
    fitzpatrick_scale: true,
    category: "people"
  },
  facepunch: {
    keywords: [ "angry", "violence", "fist", "hit", "attack", "hand" ],
    char: "\ud83d\udc4a",
    fitzpatrick_scale: true,
    category: "people"
  },
  fist: {
    keywords: [ "fingers", "hand", "grasp" ],
    char: "\u270a",
    fitzpatrick_scale: true,
    category: "people"
  },
  fist_left: {
    keywords: [ "hand", "fistbump" ],
    char: "\ud83e\udd1b",
    fitzpatrick_scale: true,
    category: "people"
  },
  fist_right: {
    keywords: [ "hand", "fistbump" ],
    char: "\ud83e\udd1c",
    fitzpatrick_scale: true,
    category: "people"
  },
  v: {
    keywords: [ "fingers", "ohyeah", "hand", "peace", "victory", "two" ],
    char: "\u270c",
    fitzpatrick_scale: true,
    category: "people"
  },
  ok_hand: {
    keywords: [ "fingers", "limbs", "perfect", "ok", "okay" ],
    char: "\ud83d\udc4c",
    fitzpatrick_scale: true,
    category: "people"
  },
  raised_hand: {
    keywords: [ "fingers", "stop", "highfive", "palm", "ban" ],
    char: "\u270b",
    fitzpatrick_scale: true,
    category: "people"
  },
  raised_back_of_hand: {
    keywords: [ "fingers", "raised", "backhand" ],
    char: "\ud83e\udd1a",
    fitzpatrick_scale: true,
    category: "people"
  },
  open_hands: {
    keywords: [ "fingers", "butterfly", "hands", "open" ],
    char: "\ud83d\udc50",
    fitzpatrick_scale: true,
    category: "people"
  },
  muscle: {
    keywords: [ "arm", "flex", "hand", "summer", "strong", "biceps" ],
    char: "\ud83d\udcaa",
    fitzpatrick_scale: true,
    category: "people"
  },
  pray: {
    keywords: [ "please", "hope", "wish", "namaste", "highfive" ],
    char: "\ud83d\ude4f",
    fitzpatrick_scale: true,
    category: "people"
  },
  foot: {
    keywords: [ "kick", "stomp" ],
    char: "\ud83e\uddb6",
    fitzpatrick_scale: true,
    category: "people"
  },
  leg: {
    keywords: [ "kick", "limb" ],
    char: "\ud83e\uddb5",
    fitzpatrick_scale: true,
    category: "people"
  },
  handshake: {
    keywords: [ "agreement", "shake" ],
    char: "\ud83e\udd1d",
    fitzpatrick_scale: false,
    category: "people"
  },
  point_up: {
    keywords: [ "hand", "fingers", "direction", "up" ],
    char: "\u261d",
    fitzpatrick_scale: true,
    category: "people"
  },
  point_up_2: {
    keywords: [ "fingers", "hand", "direction", "up" ],
    char: "\ud83d\udc46",
    fitzpatrick_scale: true,
    category: "people"
  },
  point_down: {
    keywords: [ "fingers", "hand", "direction", "down" ],
    char: "\ud83d\udc47",
    fitzpatrick_scale: true,
    category: "people"
  },
  point_left: {
    keywords: [ "direction", "fingers", "hand", "left" ],
    char: "\ud83d\udc48",
    fitzpatrick_scale: true,
    category: "people"
  },
  point_right: {
    keywords: [ "fingers", "hand", "direction", "right" ],
    char: "\ud83d\udc49",
    fitzpatrick_scale: true,
    category: "people"
  },
  fu: {
    keywords: [ "hand", "fingers", "rude", "middle", "flipping" ],
    char: "\ud83d\udd95",
    fitzpatrick_scale: true,
    category: "people"
  },
  raised_hand_with_fingers_splayed: {
    keywords: [ "hand", "fingers", "palm" ],
    char: "\ud83d\udd90",
    fitzpatrick_scale: true,
    category: "people"
  },
  love_you: {
    keywords: [ "hand", "fingers", "gesture" ],
    char: "\ud83e\udd1f",
    fitzpatrick_scale: true,
    category: "people"
  },
  metal: {
    keywords: [ "hand", "fingers", "evil_eye", "sign_of_horns", "rock_on" ],
    char: "\ud83e\udd18",
    fitzpatrick_scale: true,
    category: "people"
  },
  crossed_fingers: {
    keywords: [ "good", "lucky" ],
    char: "\ud83e\udd1e",
    fitzpatrick_scale: true,
    category: "people"
  },
  vulcan_salute: {
    keywords: [ "hand", "fingers", "spock", "star trek" ],
    char: "\ud83d\udd96",
    fitzpatrick_scale: true,
    category: "people"
  },
  writing_hand: {
    keywords: [ "lower_left_ballpoint_pen", "stationery", "write", "compose" ],
    char: "\u270d",
    fitzpatrick_scale: true,
    category: "people"
  },
  selfie: {
    keywords: [ "camera", "phone" ],
    char: "\ud83e\udd33",
    fitzpatrick_scale: true,
    category: "people"
  },
  nail_care: {
    keywords: [ "beauty", "manicure", "finger", "fashion", "nail" ],
    char: "\ud83d\udc85",
    fitzpatrick_scale: true,
    category: "people"
  },
  lips: {
    keywords: [ "mouth", "kiss" ],
    char: "\ud83d\udc44",
    fitzpatrick_scale: false,
    category: "people"
  },
  tooth: {
    keywords: [ "teeth", "dentist" ],
    char: "\ud83e\uddb7",
    fitzpatrick_scale: false,
    category: "people"
  },
  tongue: {
    keywords: [ "mouth", "playful" ],
    char: "\ud83d\udc45",
    fitzpatrick_scale: false,
    category: "people"
  },
  ear: {
    keywords: [ "face", "hear", "sound", "listen" ],
    char: "\ud83d\udc42",
    fitzpatrick_scale: true,
    category: "people"
  },
  nose: {
    keywords: [ "smell", "sniff" ],
    char: "\ud83d\udc43",
    fitzpatrick_scale: true,
    category: "people"
  },
  eye: {
    keywords: [ "face", "look", "see", "watch", "stare" ],
    char: "\ud83d\udc41",
    fitzpatrick_scale: false,
    category: "people"
  },
  eyes: {
    keywords: [ "look", "watch", "stalk", "peek", "see" ],
    char: "\ud83d\udc40",
    fitzpatrick_scale: false,
    category: "people"
  },
  brain: {
    keywords: [ "smart", "intelligent" ],
    char: "\ud83e\udde0",
    fitzpatrick_scale: false,
    category: "people"
  },
  bust_in_silhouette: {
    keywords: [ "user", "person", "human" ],
    char: "\ud83d\udc64",
    fitzpatrick_scale: false,
    category: "people"
  },
  busts_in_silhouette: {
    keywords: [ "user", "person", "human", "group", "team" ],
    char: "\ud83d\udc65",
    fitzpatrick_scale: false,
    category: "people"
  },
  speaking_head: {
    keywords: [ "user", "person", "human", "sing", "say", "talk" ],
    char: "\ud83d\udde3",
    fitzpatrick_scale: false,
    category: "people"
  },
  baby: {
    keywords: [ "child", "boy", "girl", "toddler" ],
    char: "\ud83d\udc76",
    fitzpatrick_scale: true,
    category: "people"
  },
  child: {
    keywords: [ "gender-neutral", "young" ],
    char: "\ud83e\uddd2",
    fitzpatrick_scale: true,
    category: "people"
  },
  boy: {
    keywords: [ "man", "male", "guy", "teenager" ],
    char: "\ud83d\udc66",
    fitzpatrick_scale: true,
    category: "people"
  },
  girl: {
    keywords: [ "female", "woman", "teenager" ],
    char: "\ud83d\udc67",
    fitzpatrick_scale: true,
    category: "people"
  },
  adult: {
    keywords: [ "gender-neutral", "person" ],
    char: "\ud83e\uddd1",
    fitzpatrick_scale: true,
    category: "people"
  },
  man: {
    keywords: [ "mustache", "father", "dad", "guy", "classy", "sir", "moustache" ],
    char: "\ud83d\udc68",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman: {
    keywords: [ "female", "girls", "lady" ],
    char: "\ud83d\udc69",
    fitzpatrick_scale: true,
    category: "people"
  },
  blonde_woman: {
    keywords: [ "woman", "female", "girl", "blonde", "person" ],
    char: "\ud83d\udc71\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  blonde_man: {
    keywords: [ "man", "male", "boy", "blonde", "guy", "person" ],
    char: "\ud83d\udc71",
    fitzpatrick_scale: true,
    category: "people"
  },
  bearded_person: {
    keywords: [ "person", "bewhiskered" ],
    char: "\ud83e\uddd4",
    fitzpatrick_scale: true,
    category: "people"
  },
  older_adult: {
    keywords: [ "human", "elder", "senior", "gender-neutral" ],
    char: "\ud83e\uddd3",
    fitzpatrick_scale: true,
    category: "people"
  },
  older_man: {
    keywords: [ "human", "male", "men", "old", "elder", "senior" ],
    char: "\ud83d\udc74",
    fitzpatrick_scale: true,
    category: "people"
  },
  older_woman: {
    keywords: [ "human", "female", "women", "lady", "old", "elder", "senior" ],
    char: "\ud83d\udc75",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_with_gua_pi_mao: {
    keywords: [ "male", "boy", "chinese" ],
    char: "\ud83d\udc72",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_with_headscarf: {
    keywords: [ "female", "hijab", "mantilla", "tichel" ],
    char: "\ud83e\uddd5",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_with_turban: {
    keywords: [ "female", "indian", "hinduism", "arabs", "woman" ],
    char: "\ud83d\udc73\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_with_turban: {
    keywords: [ "male", "indian", "hinduism", "arabs" ],
    char: "\ud83d\udc73",
    fitzpatrick_scale: true,
    category: "people"
  },
  policewoman: {
    keywords: [ "woman", "police", "law", "legal", "enforcement", "arrest", "911", "female" ],
    char: "\ud83d\udc6e\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  policeman: {
    keywords: [ "man", "police", "law", "legal", "enforcement", "arrest", "911" ],
    char: "\ud83d\udc6e",
    fitzpatrick_scale: true,
    category: "people"
  },
  construction_worker_woman: {
    keywords: [ "female", "human", "wip", "build", "construction", "worker", "labor", "woman" ],
    char: "\ud83d\udc77\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  construction_worker_man: {
    keywords: [ "male", "human", "wip", "guy", "build", "construction", "worker", "labor" ],
    char: "\ud83d\udc77",
    fitzpatrick_scale: true,
    category: "people"
  },
  guardswoman: {
    keywords: [ "uk", "gb", "british", "female", "royal", "woman" ],
    char: "\ud83d\udc82\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  guardsman: {
    keywords: [ "uk", "gb", "british", "male", "guy", "royal" ],
    char: "\ud83d\udc82",
    fitzpatrick_scale: true,
    category: "people"
  },
  female_detective: {
    keywords: [ "human", "spy", "detective", "female", "woman" ],
    char: "\ud83d\udd75\ufe0f\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  male_detective: {
    keywords: [ "human", "spy", "detective" ],
    char: "\ud83d\udd75",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_health_worker: {
    keywords: [ "doctor", "nurse", "therapist", "healthcare", "woman", "human" ],
    char: "\ud83d\udc69\u200d\u2695\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_health_worker: {
    keywords: [ "doctor", "nurse", "therapist", "healthcare", "man", "human" ],
    char: "\ud83d\udc68\u200d\u2695\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_farmer: {
    keywords: [ "rancher", "gardener", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83c\udf3e",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_farmer: {
    keywords: [ "rancher", "gardener", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83c\udf3e",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_cook: {
    keywords: [ "chef", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83c\udf73",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_cook: {
    keywords: [ "chef", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83c\udf73",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_student: {
    keywords: [ "graduate", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83c\udf93",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_student: {
    keywords: [ "graduate", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83c\udf93",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_singer: {
    keywords: [ "rockstar", "entertainer", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83c\udfa4",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_singer: {
    keywords: [ "rockstar", "entertainer", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83c\udfa4",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_teacher: {
    keywords: [ "instructor", "professor", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83c\udfeb",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_teacher: {
    keywords: [ "instructor", "professor", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83c\udfeb",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_factory_worker: {
    keywords: [ "assembly", "industrial", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83c\udfed",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_factory_worker: {
    keywords: [ "assembly", "industrial", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83c\udfed",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_technologist: {
    keywords: [ "coder", "developer", "engineer", "programmer", "software", "woman", "human", "laptop", "computer" ],
    char: "\ud83d\udc69\u200d\ud83d\udcbb",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_technologist: {
    keywords: [ "coder", "developer", "engineer", "programmer", "software", "man", "human", "laptop", "computer" ],
    char: "\ud83d\udc68\u200d\ud83d\udcbb",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_office_worker: {
    keywords: [ "business", "manager", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83d\udcbc",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_office_worker: {
    keywords: [ "business", "manager", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83d\udcbc",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_mechanic: {
    keywords: [ "plumber", "woman", "human", "wrench" ],
    char: "\ud83d\udc69\u200d\ud83d\udd27",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_mechanic: {
    keywords: [ "plumber", "man", "human", "wrench" ],
    char: "\ud83d\udc68\u200d\ud83d\udd27",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_scientist: {
    keywords: [ "biologist", "chemist", "engineer", "physicist", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83d\udd2c",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_scientist: {
    keywords: [ "biologist", "chemist", "engineer", "physicist", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83d\udd2c",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_artist: {
    keywords: [ "painter", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83c\udfa8",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_artist: {
    keywords: [ "painter", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83c\udfa8",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_firefighter: {
    keywords: [ "fireman", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83d\ude92",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_firefighter: {
    keywords: [ "fireman", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83d\ude92",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_pilot: {
    keywords: [ "aviator", "plane", "woman", "human" ],
    char: "\ud83d\udc69\u200d\u2708\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_pilot: {
    keywords: [ "aviator", "plane", "man", "human" ],
    char: "\ud83d\udc68\u200d\u2708\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_astronaut: {
    keywords: [ "space", "rocket", "woman", "human" ],
    char: "\ud83d\udc69\u200d\ud83d\ude80",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_astronaut: {
    keywords: [ "space", "rocket", "man", "human" ],
    char: "\ud83d\udc68\u200d\ud83d\ude80",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_judge: {
    keywords: [ "justice", "court", "woman", "human" ],
    char: "\ud83d\udc69\u200d\u2696\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_judge: {
    keywords: [ "justice", "court", "man", "human" ],
    char: "\ud83d\udc68\u200d\u2696\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_superhero: {
    keywords: [ "woman", "female", "good", "heroine", "superpowers" ],
    char: "\ud83e\uddb8\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_superhero: {
    keywords: [ "man", "male", "good", "hero", "superpowers" ],
    char: "\ud83e\uddb8\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_supervillain: {
    keywords: [ "woman", "female", "evil", "bad", "criminal", "heroine", "superpowers" ],
    char: "\ud83e\uddb9\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_supervillain: {
    keywords: [ "man", "male", "evil", "bad", "criminal", "hero", "superpowers" ],
    char: "\ud83e\uddb9\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  mrs_claus: {
    keywords: [ "woman", "female", "xmas", "mother christmas" ],
    char: "\ud83e\udd36",
    fitzpatrick_scale: true,
    category: "people"
  },
  santa: {
    keywords: [ "festival", "man", "male", "xmas", "father christmas" ],
    char: "\ud83c\udf85",
    fitzpatrick_scale: true,
    category: "people"
  },
  sorceress: {
    keywords: [ "woman", "female", "mage", "witch" ],
    char: "\ud83e\uddd9\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  wizard: {
    keywords: [ "man", "male", "mage", "sorcerer" ],
    char: "\ud83e\uddd9\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_elf: {
    keywords: [ "woman", "female" ],
    char: "\ud83e\udddd\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_elf: {
    keywords: [ "man", "male" ],
    char: "\ud83e\udddd\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_vampire: {
    keywords: [ "woman", "female" ],
    char: "\ud83e\udddb\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_vampire: {
    keywords: [ "man", "male", "dracula" ],
    char: "\ud83e\udddb\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_zombie: {
    keywords: [ "woman", "female", "undead", "walking dead" ],
    char: "\ud83e\udddf\u200d\u2640\ufe0f",
    fitzpatrick_scale: false,
    category: "people"
  },
  man_zombie: {
    keywords: [ "man", "male", "dracula", "undead", "walking dead" ],
    char: "\ud83e\udddf\u200d\u2642\ufe0f",
    fitzpatrick_scale: false,
    category: "people"
  },
  woman_genie: {
    keywords: [ "woman", "female" ],
    char: "\ud83e\uddde\u200d\u2640\ufe0f",
    fitzpatrick_scale: false,
    category: "people"
  },
  man_genie: {
    keywords: [ "man", "male" ],
    char: "\ud83e\uddde\u200d\u2642\ufe0f",
    fitzpatrick_scale: false,
    category: "people"
  },
  mermaid: {
    keywords: [ "woman", "female", "merwoman", "ariel" ],
    char: "\ud83e\udddc\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  merman: {
    keywords: [ "man", "male", "triton" ],
    char: "\ud83e\udddc\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_fairy: {
    keywords: [ "woman", "female" ],
    char: "\ud83e\uddda\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_fairy: {
    keywords: [ "man", "male" ],
    char: "\ud83e\uddda\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  angel: {
    keywords: [ "heaven", "wings", "halo" ],
    char: "\ud83d\udc7c",
    fitzpatrick_scale: true,
    category: "people"
  },
  pregnant_woman: {
    keywords: [ "baby" ],
    char: "\ud83e\udd30",
    fitzpatrick_scale: true,
    category: "people"
  },
  breastfeeding: {
    keywords: [ "nursing", "baby" ],
    char: "\ud83e\udd31",
    fitzpatrick_scale: true,
    category: "people"
  },
  princess: {
    keywords: [ "girl", "woman", "female", "blond", "crown", "royal", "queen" ],
    char: "\ud83d\udc78",
    fitzpatrick_scale: true,
    category: "people"
  },
  prince: {
    keywords: [ "boy", "man", "male", "crown", "royal", "king" ],
    char: "\ud83e\udd34",
    fitzpatrick_scale: true,
    category: "people"
  },
  bride_with_veil: {
    keywords: [ "couple", "marriage", "wedding", "woman", "bride" ],
    char: "\ud83d\udc70",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_in_tuxedo: {
    keywords: [ "couple", "marriage", "wedding", "groom" ],
    char: "\ud83e\udd35",
    fitzpatrick_scale: true,
    category: "people"
  },
  running_woman: {
    keywords: [ "woman", "walking", "exercise", "race", "running", "female" ],
    char: "\ud83c\udfc3\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  running_man: {
    keywords: [ "man", "walking", "exercise", "race", "running" ],
    char: "\ud83c\udfc3",
    fitzpatrick_scale: true,
    category: "people"
  },
  walking_woman: {
    keywords: [ "human", "feet", "steps", "woman", "female" ],
    char: "\ud83d\udeb6\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  walking_man: {
    keywords: [ "human", "feet", "steps" ],
    char: "\ud83d\udeb6",
    fitzpatrick_scale: true,
    category: "people"
  },
  dancer: {
    keywords: [ "female", "girl", "woman", "fun" ],
    char: "\ud83d\udc83",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_dancing: {
    keywords: [ "male", "boy", "fun", "dancer" ],
    char: "\ud83d\udd7a",
    fitzpatrick_scale: true,
    category: "people"
  },
  dancing_women: {
    keywords: [ "female", "bunny", "women", "girls" ],
    char: "\ud83d\udc6f",
    fitzpatrick_scale: false,
    category: "people"
  },
  dancing_men: {
    keywords: [ "male", "bunny", "men", "boys" ],
    char: "\ud83d\udc6f\u200d\u2642\ufe0f",
    fitzpatrick_scale: false,
    category: "people"
  },
  couple: {
    keywords: [ "pair", "people", "human", "love", "date", "dating", "like", "affection", "valentines", "marriage" ],
    char: "\ud83d\udc6b",
    fitzpatrick_scale: false,
    category: "people"
  },
  two_men_holding_hands: {
    keywords: [ "pair", "couple", "love", "like", "bromance", "friendship", "people", "human" ],
    char: "\ud83d\udc6c",
    fitzpatrick_scale: false,
    category: "people"
  },
  two_women_holding_hands: {
    keywords: [ "pair", "friendship", "couple", "love", "like", "female", "people", "human" ],
    char: "\ud83d\udc6d",
    fitzpatrick_scale: false,
    category: "people"
  },
  bowing_woman: {
    keywords: [ "woman", "female", "girl" ],
    char: "\ud83d\ude47\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  bowing_man: {
    keywords: [ "man", "male", "boy" ],
    char: "\ud83d\ude47",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_facepalming: {
    keywords: [ "man", "male", "boy", "disbelief" ],
    char: "\ud83e\udd26\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_facepalming: {
    keywords: [ "woman", "female", "girl", "disbelief" ],
    char: "\ud83e\udd26\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_shrugging: {
    keywords: [ "woman", "female", "girl", "confused", "indifferent", "doubt" ],
    char: "\ud83e\udd37",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_shrugging: {
    keywords: [ "man", "male", "boy", "confused", "indifferent", "doubt" ],
    char: "\ud83e\udd37\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  tipping_hand_woman: {
    keywords: [ "female", "girl", "woman", "human", "information" ],
    char: "\ud83d\udc81",
    fitzpatrick_scale: true,
    category: "people"
  },
  tipping_hand_man: {
    keywords: [ "male", "boy", "man", "human", "information" ],
    char: "\ud83d\udc81\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  no_good_woman: {
    keywords: [ "female", "girl", "woman", "nope" ],
    char: "\ud83d\ude45",
    fitzpatrick_scale: true,
    category: "people"
  },
  no_good_man: {
    keywords: [ "male", "boy", "man", "nope" ],
    char: "\ud83d\ude45\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  ok_woman: {
    keywords: [ "women", "girl", "female", "pink", "human", "woman" ],
    char: "\ud83d\ude46",
    fitzpatrick_scale: true,
    category: "people"
  },
  ok_man: {
    keywords: [ "men", "boy", "male", "blue", "human", "man" ],
    char: "\ud83d\ude46\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  raising_hand_woman: {
    keywords: [ "female", "girl", "woman" ],
    char: "\ud83d\ude4b",
    fitzpatrick_scale: true,
    category: "people"
  },
  raising_hand_man: {
    keywords: [ "male", "boy", "man" ],
    char: "\ud83d\ude4b\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  pouting_woman: {
    keywords: [ "female", "girl", "woman" ],
    char: "\ud83d\ude4e",
    fitzpatrick_scale: true,
    category: "people"
  },
  pouting_man: {
    keywords: [ "male", "boy", "man" ],
    char: "\ud83d\ude4e\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  frowning_woman: {
    keywords: [ "female", "girl", "woman", "sad", "depressed", "discouraged", "unhappy" ],
    char: "\ud83d\ude4d",
    fitzpatrick_scale: true,
    category: "people"
  },
  frowning_man: {
    keywords: [ "male", "boy", "man", "sad", "depressed", "discouraged", "unhappy" ],
    char: "\ud83d\ude4d\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  haircut_woman: {
    keywords: [ "female", "girl", "woman" ],
    char: "\ud83d\udc87",
    fitzpatrick_scale: true,
    category: "people"
  },
  haircut_man: {
    keywords: [ "male", "boy", "man" ],
    char: "\ud83d\udc87\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  massage_woman: {
    keywords: [ "female", "girl", "woman", "head" ],
    char: "\ud83d\udc86",
    fitzpatrick_scale: true,
    category: "people"
  },
  massage_man: {
    keywords: [ "male", "boy", "man", "head" ],
    char: "\ud83d\udc86\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  woman_in_steamy_room: {
    keywords: [ "female", "woman", "spa", "steamroom", "sauna" ],
    char: "\ud83e\uddd6\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  man_in_steamy_room: {
    keywords: [ "male", "man", "spa", "steamroom", "sauna" ],
    char: "\ud83e\uddd6\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "people"
  },
  couple_with_heart_woman_man: {
    keywords: [ "pair", "love", "like", "affection", "human", "dating", "valentines", "marriage" ],
    char: "\ud83d\udc91",
    fitzpatrick_scale: false,
    category: "people"
  },
  couple_with_heart_woman_woman: {
    keywords: [ "pair", "love", "like", "affection", "human", "dating", "valentines", "marriage" ],
    char: "\ud83d\udc69\u200d\u2764\ufe0f\u200d\ud83d\udc69",
    fitzpatrick_scale: false,
    category: "people"
  },
  couple_with_heart_man_man: {
    keywords: [ "pair", "love", "like", "affection", "human", "dating", "valentines", "marriage" ],
    char: "\ud83d\udc68\u200d\u2764\ufe0f\u200d\ud83d\udc68",
    fitzpatrick_scale: false,
    category: "people"
  },
  couplekiss_man_woman: {
    keywords: [ "pair", "valentines", "love", "like", "dating", "marriage" ],
    char: "\ud83d\udc8f",
    fitzpatrick_scale: false,
    category: "people"
  },
  couplekiss_woman_woman: {
    keywords: [ "pair", "valentines", "love", "like", "dating", "marriage" ],
    char: "\ud83d\udc69\u200d\u2764\ufe0f\u200d\ud83d\udc8b\u200d\ud83d\udc69",
    fitzpatrick_scale: false,
    category: "people"
  },
  couplekiss_man_man: {
    keywords: [ "pair", "valentines", "love", "like", "dating", "marriage" ],
    char: "\ud83d\udc68\u200d\u2764\ufe0f\u200d\ud83d\udc8b\u200d\ud83d\udc68",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_woman_boy: {
    keywords: [ "home", "parents", "child", "mom", "dad", "father", "mother", "people", "human" ],
    char: "\ud83d\udc6a",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_woman_girl: {
    keywords: [ "home", "parents", "people", "human", "child" ],
    char: "\ud83d\udc68\u200d\ud83d\udc69\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_woman_girl_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_woman_boy_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc69\u200d\ud83d\udc66\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_woman_girl_girl: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_woman_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_woman_girl: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_woman_girl_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_woman_boy_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d\udc66\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_woman_girl_girl: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_man_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_man_girl: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_man_girl_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d\udc67\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_man_boy_boy: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d\udc66\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_man_girl_girl: {
    keywords: [ "home", "parents", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc68\u200d\ud83d\udc67\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_boy: {
    keywords: [ "home", "parent", "people", "human", "child" ],
    char: "\ud83d\udc69\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_girl: {
    keywords: [ "home", "parent", "people", "human", "child" ],
    char: "\ud83d\udc69\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_girl_boy: {
    keywords: [ "home", "parent", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_boy_boy: {
    keywords: [ "home", "parent", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc66\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_woman_girl_girl: {
    keywords: [ "home", "parent", "people", "human", "children" ],
    char: "\ud83d\udc69\u200d\ud83d\udc67\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_boy: {
    keywords: [ "home", "parent", "people", "human", "child" ],
    char: "\ud83d\udc68\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_girl: {
    keywords: [ "home", "parent", "people", "human", "child" ],
    char: "\ud83d\udc68\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_girl_boy: {
    keywords: [ "home", "parent", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc67\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_boy_boy: {
    keywords: [ "home", "parent", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc66\u200d\ud83d\udc66",
    fitzpatrick_scale: false,
    category: "people"
  },
  family_man_girl_girl: {
    keywords: [ "home", "parent", "people", "human", "children" ],
    char: "\ud83d\udc68\u200d\ud83d\udc67\u200d\ud83d\udc67",
    fitzpatrick_scale: false,
    category: "people"
  },
  yarn: {
    keywords: [ "ball", "crochet", "knit" ],
    char: "\ud83e\uddf6",
    fitzpatrick_scale: false,
    category: "people"
  },
  thread: {
    keywords: [ "needle", "sewing", "spool", "string" ],
    char: "\ud83e\uddf5",
    fitzpatrick_scale: false,
    category: "people"
  },
  coat: {
    keywords: [ "jacket" ],
    char: "\ud83e\udde5",
    fitzpatrick_scale: false,
    category: "people"
  },
  labcoat: {
    keywords: [ "doctor", "experiment", "scientist", "chemist" ],
    char: "\ud83e\udd7c",
    fitzpatrick_scale: false,
    category: "people"
  },
  womans_clothes: {
    keywords: [ "fashion", "shopping_bags", "female" ],
    char: "\ud83d\udc5a",
    fitzpatrick_scale: false,
    category: "people"
  },
  tshirt: {
    keywords: [ "fashion", "cloth", "casual", "shirt", "tee" ],
    char: "\ud83d\udc55",
    fitzpatrick_scale: false,
    category: "people"
  },
  jeans: {
    keywords: [ "fashion", "shopping" ],
    char: "\ud83d\udc56",
    fitzpatrick_scale: false,
    category: "people"
  },
  necktie: {
    keywords: [ "shirt", "suitup", "formal", "fashion", "cloth", "business" ],
    char: "\ud83d\udc54",
    fitzpatrick_scale: false,
    category: "people"
  },
  dress: {
    keywords: [ "clothes", "fashion", "shopping" ],
    char: "\ud83d\udc57",
    fitzpatrick_scale: false,
    category: "people"
  },
  bikini: {
    keywords: [ "swimming", "female", "woman", "girl", "fashion", "beach", "summer" ],
    char: "\ud83d\udc59",
    fitzpatrick_scale: false,
    category: "people"
  },
  kimono: {
    keywords: [ "dress", "fashion", "women", "female", "japanese" ],
    char: "\ud83d\udc58",
    fitzpatrick_scale: false,
    category: "people"
  },
  lipstick: {
    keywords: [ "female", "girl", "fashion", "woman" ],
    char: "\ud83d\udc84",
    fitzpatrick_scale: false,
    category: "people"
  },
  kiss: {
    keywords: [ "face", "lips", "love", "like", "affection", "valentines" ],
    char: "\ud83d\udc8b",
    fitzpatrick_scale: false,
    category: "people"
  },
  footprints: {
    keywords: [ "feet", "tracking", "walking", "beach" ],
    char: "\ud83d\udc63",
    fitzpatrick_scale: false,
    category: "people"
  },
  flat_shoe: {
    keywords: [ "ballet", "slip-on", "slipper" ],
    char: "\ud83e\udd7f",
    fitzpatrick_scale: false,
    category: "people"
  },
  high_heel: {
    keywords: [ "fashion", "shoes", "female", "pumps", "stiletto" ],
    char: "\ud83d\udc60",
    fitzpatrick_scale: false,
    category: "people"
  },
  sandal: {
    keywords: [ "shoes", "fashion", "flip flops" ],
    char: "\ud83d\udc61",
    fitzpatrick_scale: false,
    category: "people"
  },
  boot: {
    keywords: [ "shoes", "fashion" ],
    char: "\ud83d\udc62",
    fitzpatrick_scale: false,
    category: "people"
  },
  mans_shoe: {
    keywords: [ "fashion", "male" ],
    char: "\ud83d\udc5e",
    fitzpatrick_scale: false,
    category: "people"
  },
  athletic_shoe: {
    keywords: [ "shoes", "sports", "sneakers" ],
    char: "\ud83d\udc5f",
    fitzpatrick_scale: false,
    category: "people"
  },
  hiking_boot: {
    keywords: [ "backpacking", "camping", "hiking" ],
    char: "\ud83e\udd7e",
    fitzpatrick_scale: false,
    category: "people"
  },
  socks: {
    keywords: [ "stockings", "clothes" ],
    char: "\ud83e\udde6",
    fitzpatrick_scale: false,
    category: "people"
  },
  gloves: {
    keywords: [ "hands", "winter", "clothes" ],
    char: "\ud83e\udde4",
    fitzpatrick_scale: false,
    category: "people"
  },
  scarf: {
    keywords: [ "neck", "winter", "clothes" ],
    char: "\ud83e\udde3",
    fitzpatrick_scale: false,
    category: "people"
  },
  womans_hat: {
    keywords: [ "fashion", "accessories", "female", "lady", "spring" ],
    char: "\ud83d\udc52",
    fitzpatrick_scale: false,
    category: "people"
  },
  tophat: {
    keywords: [ "magic", "gentleman", "classy", "circus" ],
    char: "\ud83c\udfa9",
    fitzpatrick_scale: false,
    category: "people"
  },
  billed_hat: {
    keywords: [ "cap", "baseball" ],
    char: "\ud83e\udde2",
    fitzpatrick_scale: false,
    category: "people"
  },
  rescue_worker_helmet: {
    keywords: [ "construction", "build" ],
    char: "\u26d1",
    fitzpatrick_scale: false,
    category: "people"
  },
  mortar_board: {
    keywords: [ "school", "college", "degree", "university", "graduation", "cap", "hat", "legal", "learn", "education" ],
    char: "\ud83c\udf93",
    fitzpatrick_scale: false,
    category: "people"
  },
  crown: {
    keywords: [ "king", "kod", "leader", "royalty", "lord" ],
    char: "\ud83d\udc51",
    fitzpatrick_scale: false,
    category: "people"
  },
  school_satchel: {
    keywords: [ "student", "education", "bag", "backpack" ],
    char: "\ud83c\udf92",
    fitzpatrick_scale: false,
    category: "people"
  },
  luggage: {
    keywords: [ "packing", "travel" ],
    char: "\ud83e\uddf3",
    fitzpatrick_scale: false,
    category: "people"
  },
  pouch: {
    keywords: [ "bag", "accessories", "shopping" ],
    char: "\ud83d\udc5d",
    fitzpatrick_scale: false,
    category: "people"
  },
  purse: {
    keywords: [ "fashion", "accessories", "money", "sales", "shopping" ],
    char: "\ud83d\udc5b",
    fitzpatrick_scale: false,
    category: "people"
  },
  handbag: {
    keywords: [ "fashion", "accessory", "accessories", "shopping" ],
    char: "\ud83d\udc5c",
    fitzpatrick_scale: false,
    category: "people"
  },
  briefcase: {
    keywords: [ "business", "documents", "work", "law", "legal", "job", "career" ],
    char: "\ud83d\udcbc",
    fitzpatrick_scale: false,
    category: "people"
  },
  eyeglasses: {
    keywords: [ "fashion", "accessories", "eyesight", "nerdy", "dork", "geek" ],
    char: "\ud83d\udc53",
    fitzpatrick_scale: false,
    category: "people"
  },
  dark_sunglasses: {
    keywords: [ "face", "cool", "accessories" ],
    char: "\ud83d\udd76",
    fitzpatrick_scale: false,
    category: "people"
  },
  goggles: {
    keywords: [ "eyes", "protection", "safety" ],
    char: "\ud83e\udd7d",
    fitzpatrick_scale: false,
    category: "people"
  },
  ring: {
    keywords: [ "wedding", "propose", "marriage", "valentines", "diamond", "fashion", "jewelry", "gem", "engagement" ],
    char: "\ud83d\udc8d",
    fitzpatrick_scale: false,
    category: "people"
  },
  closed_umbrella: {
    keywords: [ "weather", "rain", "drizzle" ],
    char: "\ud83c\udf02",
    fitzpatrick_scale: false,
    category: "people"
  },
  dog: {
    keywords: [ "animal", "friend", "nature", "woof", "puppy", "pet", "faithful" ],
    char: "\ud83d\udc36",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cat: {
    keywords: [ "animal", "meow", "nature", "pet", "kitten" ],
    char: "\ud83d\udc31",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  mouse: {
    keywords: [ "animal", "nature", "cheese_wedge", "rodent" ],
    char: "\ud83d\udc2d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  hamster: {
    keywords: [ "animal", "nature" ],
    char: "\ud83d\udc39",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  rabbit: {
    keywords: [ "animal", "nature", "pet", "spring", "magic", "bunny" ],
    char: "\ud83d\udc30",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  fox_face: {
    keywords: [ "animal", "nature", "face" ],
    char: "\ud83e\udd8a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  bear: {
    keywords: [ "animal", "nature", "wild" ],
    char: "\ud83d\udc3b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  panda_face: {
    keywords: [ "animal", "nature", "panda" ],
    char: "\ud83d\udc3c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  koala: {
    keywords: [ "animal", "nature" ],
    char: "\ud83d\udc28",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  tiger: {
    keywords: [ "animal", "cat", "danger", "wild", "nature", "roar" ],
    char: "\ud83d\udc2f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  lion: {
    keywords: [ "animal", "nature" ],
    char: "\ud83e\udd81",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cow: {
    keywords: [ "beef", "ox", "animal", "nature", "moo", "milk" ],
    char: "\ud83d\udc2e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  pig: {
    keywords: [ "animal", "oink", "nature" ],
    char: "\ud83d\udc37",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  pig_nose: {
    keywords: [ "animal", "oink" ],
    char: "\ud83d\udc3d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  frog: {
    keywords: [ "animal", "nature", "croak", "toad" ],
    char: "\ud83d\udc38",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  squid: {
    keywords: [ "animal", "nature", "ocean", "sea" ],
    char: "\ud83e\udd91",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  octopus: {
    keywords: [ "animal", "creature", "ocean", "sea", "nature", "beach" ],
    char: "\ud83d\udc19",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  shrimp: {
    keywords: [ "animal", "ocean", "nature", "seafood" ],
    char: "\ud83e\udd90",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  monkey_face: {
    keywords: [ "animal", "nature", "circus" ],
    char: "\ud83d\udc35",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  gorilla: {
    keywords: [ "animal", "nature", "circus" ],
    char: "\ud83e\udd8d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  see_no_evil: {
    keywords: [ "monkey", "animal", "nature", "haha" ],
    char: "\ud83d\ude48",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  hear_no_evil: {
    keywords: [ "animal", "monkey", "nature" ],
    char: "\ud83d\ude49",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  speak_no_evil: {
    keywords: [ "monkey", "animal", "nature", "omg" ],
    char: "\ud83d\ude4a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  monkey: {
    keywords: [ "animal", "nature", "banana", "circus" ],
    char: "\ud83d\udc12",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  chicken: {
    keywords: [ "animal", "cluck", "nature", "bird" ],
    char: "\ud83d\udc14",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  penguin: {
    keywords: [ "animal", "nature" ],
    char: "\ud83d\udc27",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  bird: {
    keywords: [ "animal", "nature", "fly", "tweet", "spring" ],
    char: "\ud83d\udc26",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  baby_chick: {
    keywords: [ "animal", "chicken", "bird" ],
    char: "\ud83d\udc24",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  hatching_chick: {
    keywords: [ "animal", "chicken", "egg", "born", "baby", "bird" ],
    char: "\ud83d\udc23",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  hatched_chick: {
    keywords: [ "animal", "chicken", "baby", "bird" ],
    char: "\ud83d\udc25",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  duck: {
    keywords: [ "animal", "nature", "bird", "mallard" ],
    char: "\ud83e\udd86",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  eagle: {
    keywords: [ "animal", "nature", "bird" ],
    char: "\ud83e\udd85",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  owl: {
    keywords: [ "animal", "nature", "bird", "hoot" ],
    char: "\ud83e\udd89",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  bat: {
    keywords: [ "animal", "nature", "blind", "vampire" ],
    char: "\ud83e\udd87",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  wolf: {
    keywords: [ "animal", "nature", "wild" ],
    char: "\ud83d\udc3a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  boar: {
    keywords: [ "animal", "nature" ],
    char: "\ud83d\udc17",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  horse: {
    keywords: [ "animal", "brown", "nature" ],
    char: "\ud83d\udc34",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  unicorn: {
    keywords: [ "animal", "nature", "mystical" ],
    char: "\ud83e\udd84",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  honeybee: {
    keywords: [ "animal", "insect", "nature", "bug", "spring", "honey" ],
    char: "\ud83d\udc1d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  bug: {
    keywords: [ "animal", "insect", "nature", "worm" ],
    char: "\ud83d\udc1b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  butterfly: {
    keywords: [ "animal", "insect", "nature", "caterpillar" ],
    char: "\ud83e\udd8b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  snail: {
    keywords: [ "slow", "animal", "shell" ],
    char: "\ud83d\udc0c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  beetle: {
    keywords: [ "animal", "insect", "nature", "ladybug" ],
    char: "\ud83d\udc1e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  ant: {
    keywords: [ "animal", "insect", "nature", "bug" ],
    char: "\ud83d\udc1c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  grasshopper: {
    keywords: [ "animal", "cricket", "chirp" ],
    char: "\ud83e\udd97",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  spider: {
    keywords: [ "animal", "arachnid" ],
    char: "\ud83d\udd77",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  scorpion: {
    keywords: [ "animal", "arachnid" ],
    char: "\ud83e\udd82",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  crab: {
    keywords: [ "animal", "crustacean" ],
    char: "\ud83e\udd80",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  snake: {
    keywords: [ "animal", "evil", "nature", "hiss", "python" ],
    char: "\ud83d\udc0d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  lizard: {
    keywords: [ "animal", "nature", "reptile" ],
    char: "\ud83e\udd8e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  "t-rex": {
    keywords: [ "animal", "nature", "dinosaur", "tyrannosaurus", "extinct" ],
    char: "\ud83e\udd96",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sauropod: {
    keywords: [ "animal", "nature", "dinosaur", "brachiosaurus", "brontosaurus", "diplodocus", "extinct" ],
    char: "\ud83e\udd95",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  turtle: {
    keywords: [ "animal", "slow", "nature", "tortoise" ],
    char: "\ud83d\udc22",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  tropical_fish: {
    keywords: [ "animal", "swim", "ocean", "beach", "nemo" ],
    char: "\ud83d\udc20",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  fish: {
    keywords: [ "animal", "food", "nature" ],
    char: "\ud83d\udc1f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  blowfish: {
    keywords: [ "animal", "nature", "food", "sea", "ocean" ],
    char: "\ud83d\udc21",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dolphin: {
    keywords: [ "animal", "nature", "fish", "sea", "ocean", "flipper", "fins", "beach" ],
    char: "\ud83d\udc2c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  shark: {
    keywords: [ "animal", "nature", "fish", "sea", "ocean", "jaws", "fins", "beach" ],
    char: "\ud83e\udd88",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  whale: {
    keywords: [ "animal", "nature", "sea", "ocean" ],
    char: "\ud83d\udc33",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  whale2: {
    keywords: [ "animal", "nature", "sea", "ocean" ],
    char: "\ud83d\udc0b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  crocodile: {
    keywords: [ "animal", "nature", "reptile", "lizard", "alligator" ],
    char: "\ud83d\udc0a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  leopard: {
    keywords: [ "animal", "nature" ],
    char: "\ud83d\udc06",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  zebra: {
    keywords: [ "animal", "nature", "stripes", "safari" ],
    char: "\ud83e\udd93",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  tiger2: {
    keywords: [ "animal", "nature", "roar" ],
    char: "\ud83d\udc05",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  water_buffalo: {
    keywords: [ "animal", "nature", "ox", "cow" ],
    char: "\ud83d\udc03",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  ox: {
    keywords: [ "animal", "cow", "beef" ],
    char: "\ud83d\udc02",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cow2: {
    keywords: [ "beef", "ox", "animal", "nature", "moo", "milk" ],
    char: "\ud83d\udc04",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  deer: {
    keywords: [ "animal", "nature", "horns", "venison" ],
    char: "\ud83e\udd8c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dromedary_camel: {
    keywords: [ "animal", "hot", "desert", "hump" ],
    char: "\ud83d\udc2a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  camel: {
    keywords: [ "animal", "nature", "hot", "desert", "hump" ],
    char: "\ud83d\udc2b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  giraffe: {
    keywords: [ "animal", "nature", "spots", "safari" ],
    char: "\ud83e\udd92",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  elephant: {
    keywords: [ "animal", "nature", "nose", "th", "circus" ],
    char: "\ud83d\udc18",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  rhinoceros: {
    keywords: [ "animal", "nature", "horn" ],
    char: "\ud83e\udd8f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  goat: {
    keywords: [ "animal", "nature" ],
    char: "\ud83d\udc10",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  ram: {
    keywords: [ "animal", "sheep", "nature" ],
    char: "\ud83d\udc0f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sheep: {
    keywords: [ "animal", "nature", "wool", "shipit" ],
    char: "\ud83d\udc11",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  racehorse: {
    keywords: [ "animal", "gamble", "luck" ],
    char: "\ud83d\udc0e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  pig2: {
    keywords: [ "animal", "nature" ],
    char: "\ud83d\udc16",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  rat: {
    keywords: [ "animal", "mouse", "rodent" ],
    char: "\ud83d\udc00",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  mouse2: {
    keywords: [ "animal", "nature", "rodent" ],
    char: "\ud83d\udc01",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  rooster: {
    keywords: [ "animal", "nature", "chicken" ],
    char: "\ud83d\udc13",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  turkey: {
    keywords: [ "animal", "bird" ],
    char: "\ud83e\udd83",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dove: {
    keywords: [ "animal", "bird" ],
    char: "\ud83d\udd4a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dog2: {
    keywords: [ "animal", "nature", "friend", "doge", "pet", "faithful" ],
    char: "\ud83d\udc15",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  poodle: {
    keywords: [ "dog", "animal", "101", "nature", "pet" ],
    char: "\ud83d\udc29",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cat2: {
    keywords: [ "animal", "meow", "pet", "cats" ],
    char: "\ud83d\udc08",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  rabbit2: {
    keywords: [ "animal", "nature", "pet", "magic", "spring" ],
    char: "\ud83d\udc07",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  chipmunk: {
    keywords: [ "animal", "nature", "rodent", "squirrel" ],
    char: "\ud83d\udc3f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  hedgehog: {
    keywords: [ "animal", "nature", "spiny" ],
    char: "\ud83e\udd94",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  raccoon: {
    keywords: [ "animal", "nature" ],
    char: "\ud83e\udd9d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  llama: {
    keywords: [ "animal", "nature", "alpaca" ],
    char: "\ud83e\udd99",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  hippopotamus: {
    keywords: [ "animal", "nature" ],
    char: "\ud83e\udd9b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  kangaroo: {
    keywords: [ "animal", "nature", "australia", "joey", "hop", "marsupial" ],
    char: "\ud83e\udd98",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  badger: {
    keywords: [ "animal", "nature", "honey" ],
    char: "\ud83e\udda1",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  swan: {
    keywords: [ "animal", "nature", "bird" ],
    char: "\ud83e\udda2",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  peacock: {
    keywords: [ "animal", "nature", "peahen", "bird" ],
    char: "\ud83e\udd9a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  parrot: {
    keywords: [ "animal", "nature", "bird", "pirate", "talk" ],
    char: "\ud83e\udd9c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  lobster: {
    keywords: [ "animal", "nature", "bisque", "claws", "seafood" ],
    char: "\ud83e\udd9e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  mosquito: {
    keywords: [ "animal", "nature", "insect", "malaria" ],
    char: "\ud83e\udd9f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  paw_prints: {
    keywords: [ "animal", "tracking", "footprints", "dog", "cat", "pet", "feet" ],
    char: "\ud83d\udc3e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dragon: {
    keywords: [ "animal", "myth", "nature", "chinese", "green" ],
    char: "\ud83d\udc09",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dragon_face: {
    keywords: [ "animal", "myth", "nature", "chinese", "green" ],
    char: "\ud83d\udc32",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cactus: {
    keywords: [ "vegetable", "plant", "nature" ],
    char: "\ud83c\udf35",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  christmas_tree: {
    keywords: [ "festival", "vacation", "december", "xmas", "celebration" ],
    char: "\ud83c\udf84",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  evergreen_tree: {
    keywords: [ "plant", "nature" ],
    char: "\ud83c\udf32",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  deciduous_tree: {
    keywords: [ "plant", "nature" ],
    char: "\ud83c\udf33",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  palm_tree: {
    keywords: [ "plant", "vegetable", "nature", "summer", "beach", "mojito", "tropical" ],
    char: "\ud83c\udf34",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  seedling: {
    keywords: [ "plant", "nature", "grass", "lawn", "spring" ],
    char: "\ud83c\udf31",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  herb: {
    keywords: [ "vegetable", "plant", "medicine", "weed", "grass", "lawn" ],
    char: "\ud83c\udf3f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  shamrock: {
    keywords: [ "vegetable", "plant", "nature", "irish", "clover" ],
    char: "\u2618",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  four_leaf_clover: {
    keywords: [ "vegetable", "plant", "nature", "lucky", "irish" ],
    char: "\ud83c\udf40",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  bamboo: {
    keywords: [ "plant", "nature", "vegetable", "panda", "pine_decoration" ],
    char: "\ud83c\udf8d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  tanabata_tree: {
    keywords: [ "plant", "nature", "branch", "summer" ],
    char: "\ud83c\udf8b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  leaves: {
    keywords: [ "nature", "plant", "tree", "vegetable", "grass", "lawn", "spring" ],
    char: "\ud83c\udf43",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  fallen_leaf: {
    keywords: [ "nature", "plant", "vegetable", "leaves" ],
    char: "\ud83c\udf42",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  maple_leaf: {
    keywords: [ "nature", "plant", "vegetable", "ca", "fall" ],
    char: "\ud83c\udf41",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  ear_of_rice: {
    keywords: [ "nature", "plant" ],
    char: "\ud83c\udf3e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  hibiscus: {
    keywords: [ "plant", "vegetable", "flowers", "beach" ],
    char: "\ud83c\udf3a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sunflower: {
    keywords: [ "nature", "plant", "fall" ],
    char: "\ud83c\udf3b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  rose: {
    keywords: [ "flowers", "valentines", "love", "spring" ],
    char: "\ud83c\udf39",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  wilted_flower: {
    keywords: [ "plant", "nature", "flower" ],
    char: "\ud83e\udd40",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  tulip: {
    keywords: [ "flowers", "plant", "nature", "summer", "spring" ],
    char: "\ud83c\udf37",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  blossom: {
    keywords: [ "nature", "flowers", "yellow" ],
    char: "\ud83c\udf3c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cherry_blossom: {
    keywords: [ "nature", "plant", "spring", "flower" ],
    char: "\ud83c\udf38",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  bouquet: {
    keywords: [ "flowers", "nature", "spring" ],
    char: "\ud83d\udc90",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  mushroom: {
    keywords: [ "plant", "vegetable" ],
    char: "\ud83c\udf44",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  chestnut: {
    keywords: [ "food", "squirrel" ],
    char: "\ud83c\udf30",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  jack_o_lantern: {
    keywords: [ "halloween", "light", "pumpkin", "creepy", "fall" ],
    char: "\ud83c\udf83",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  shell: {
    keywords: [ "nature", "sea", "beach" ],
    char: "\ud83d\udc1a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  spider_web: {
    keywords: [ "animal", "insect", "arachnid", "silk" ],
    char: "\ud83d\udd78",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  earth_americas: {
    keywords: [ "globe", "world", "USA", "international" ],
    char: "\ud83c\udf0e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  earth_africa: {
    keywords: [ "globe", "world", "international" ],
    char: "\ud83c\udf0d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  earth_asia: {
    keywords: [ "globe", "world", "east", "international" ],
    char: "\ud83c\udf0f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  full_moon: {
    keywords: [ "nature", "yellow", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf15",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  waning_gibbous_moon: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep", "waxing_gibbous_moon" ],
    char: "\ud83c\udf16",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  last_quarter_moon: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf17",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  waning_crescent_moon: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf18",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  new_moon: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf11",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  waxing_crescent_moon: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf12",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  first_quarter_moon: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf13",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  waxing_gibbous_moon: {
    keywords: [ "nature", "night", "sky", "gray", "twilight", "planet", "space", "evening", "sleep" ],
    char: "\ud83c\udf14",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  new_moon_with_face: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf1a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  full_moon_with_face: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf1d",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  first_quarter_moon_with_face: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf1b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  last_quarter_moon_with_face: {
    keywords: [ "nature", "twilight", "planet", "space", "night", "evening", "sleep" ],
    char: "\ud83c\udf1c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sun_with_face: {
    keywords: [ "nature", "morning", "sky" ],
    char: "\ud83c\udf1e",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  crescent_moon: {
    keywords: [ "night", "sleep", "sky", "evening", "magic" ],
    char: "\ud83c\udf19",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  star: {
    keywords: [ "night", "yellow" ],
    char: "\u2b50",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  star2: {
    keywords: [ "night", "sparkle", "awesome", "good", "magic" ],
    char: "\ud83c\udf1f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dizzy: {
    keywords: [ "star", "sparkle", "shoot", "magic" ],
    char: "\ud83d\udcab",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sparkles: {
    keywords: [ "stars", "shine", "shiny", "cool", "awesome", "good", "magic" ],
    char: "\u2728",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  comet: {
    keywords: [ "space" ],
    char: "\u2604",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sunny: {
    keywords: [ "weather", "nature", "brightness", "summer", "beach", "spring" ],
    char: "\u2600\ufe0f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sun_behind_small_cloud: {
    keywords: [ "weather" ],
    char: "\ud83c\udf24",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  partly_sunny: {
    keywords: [ "weather", "nature", "cloudy", "morning", "fall", "spring" ],
    char: "\u26c5",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sun_behind_large_cloud: {
    keywords: [ "weather" ],
    char: "\ud83c\udf25",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sun_behind_rain_cloud: {
    keywords: [ "weather" ],
    char: "\ud83c\udf26",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cloud: {
    keywords: [ "weather", "sky" ],
    char: "\u2601\ufe0f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cloud_with_rain: {
    keywords: [ "weather" ],
    char: "\ud83c\udf27",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cloud_with_lightning_and_rain: {
    keywords: [ "weather", "lightning" ],
    char: "\u26c8",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cloud_with_lightning: {
    keywords: [ "weather", "thunder" ],
    char: "\ud83c\udf29",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  zap: {
    keywords: [ "thunder", "weather", "lightning bolt", "fast" ],
    char: "\u26a1",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  fire: {
    keywords: [ "hot", "cook", "flame" ],
    char: "\ud83d\udd25",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  boom: {
    keywords: [ "bomb", "explode", "explosion", "collision", "blown" ],
    char: "\ud83d\udca5",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  snowflake: {
    keywords: [ "winter", "season", "cold", "weather", "christmas", "xmas" ],
    char: "\u2744\ufe0f",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  cloud_with_snow: {
    keywords: [ "weather" ],
    char: "\ud83c\udf28",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  snowman: {
    keywords: [ "winter", "season", "cold", "weather", "christmas", "xmas", "frozen", "without_snow" ],
    char: "\u26c4",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  snowman_with_snow: {
    keywords: [ "winter", "season", "cold", "weather", "christmas", "xmas", "frozen" ],
    char: "\u2603",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  wind_face: {
    keywords: [ "gust", "air" ],
    char: "\ud83c\udf2c",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  dash: {
    keywords: [ "wind", "air", "fast", "shoo", "fart", "smoke", "puff" ],
    char: "\ud83d\udca8",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  tornado: {
    keywords: [ "weather", "cyclone", "twister" ],
    char: "\ud83c\udf2a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  fog: {
    keywords: [ "weather" ],
    char: "\ud83c\udf2b",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  open_umbrella: {
    keywords: [ "weather", "spring" ],
    char: "\u2602",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  umbrella: {
    keywords: [ "rainy", "weather", "spring" ],
    char: "\u2614",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  droplet: {
    keywords: [ "water", "drip", "faucet", "spring" ],
    char: "\ud83d\udca7",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  sweat_drops: {
    keywords: [ "water", "drip", "oops" ],
    char: "\ud83d\udca6",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  ocean: {
    keywords: [ "sea", "water", "wave", "nature", "tsunami", "disaster" ],
    char: "\ud83c\udf0a",
    fitzpatrick_scale: false,
    category: "animals_and_nature"
  },
  green_apple: {
    keywords: [ "fruit", "nature" ],
    char: "\ud83c\udf4f",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  apple: {
    keywords: [ "fruit", "mac", "school" ],
    char: "\ud83c\udf4e",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  pear: {
    keywords: [ "fruit", "nature", "food" ],
    char: "\ud83c\udf50",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  tangerine: {
    keywords: [ "food", "fruit", "nature", "orange" ],
    char: "\ud83c\udf4a",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  lemon: {
    keywords: [ "fruit", "nature" ],
    char: "\ud83c\udf4b",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  banana: {
    keywords: [ "fruit", "food", "monkey" ],
    char: "\ud83c\udf4c",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  watermelon: {
    keywords: [ "fruit", "food", "picnic", "summer" ],
    char: "\ud83c\udf49",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  grapes: {
    keywords: [ "fruit", "food", "wine" ],
    char: "\ud83c\udf47",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  strawberry: {
    keywords: [ "fruit", "food", "nature" ],
    char: "\ud83c\udf53",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  melon: {
    keywords: [ "fruit", "nature", "food" ],
    char: "\ud83c\udf48",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cherries: {
    keywords: [ "food", "fruit" ],
    char: "\ud83c\udf52",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  peach: {
    keywords: [ "fruit", "nature", "food" ],
    char: "\ud83c\udf51",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  pineapple: {
    keywords: [ "fruit", "nature", "food" ],
    char: "\ud83c\udf4d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  coconut: {
    keywords: [ "fruit", "nature", "food", "palm" ],
    char: "\ud83e\udd65",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  kiwi_fruit: {
    keywords: [ "fruit", "food" ],
    char: "\ud83e\udd5d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  mango: {
    keywords: [ "fruit", "food", "tropical" ],
    char: "\ud83e\udd6d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  avocado: {
    keywords: [ "fruit", "food" ],
    char: "\ud83e\udd51",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  broccoli: {
    keywords: [ "fruit", "food", "vegetable" ],
    char: "\ud83e\udd66",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  tomato: {
    keywords: [ "fruit", "vegetable", "nature", "food" ],
    char: "\ud83c\udf45",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  eggplant: {
    keywords: [ "vegetable", "nature", "food", "aubergine" ],
    char: "\ud83c\udf46",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cucumber: {
    keywords: [ "fruit", "food", "pickle" ],
    char: "\ud83e\udd52",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  carrot: {
    keywords: [ "vegetable", "food", "orange" ],
    char: "\ud83e\udd55",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  hot_pepper: {
    keywords: [ "food", "spicy", "chilli", "chili" ],
    char: "\ud83c\udf36",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  potato: {
    keywords: [ "food", "tuber", "vegatable", "starch" ],
    char: "\ud83e\udd54",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  corn: {
    keywords: [ "food", "vegetable", "plant" ],
    char: "\ud83c\udf3d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  leafy_greens: {
    keywords: [ "food", "vegetable", "plant", "bok choy", "cabbage", "kale", "lettuce" ],
    char: "\ud83e\udd6c",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  sweet_potato: {
    keywords: [ "food", "nature" ],
    char: "\ud83c\udf60",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  peanuts: {
    keywords: [ "food", "nut" ],
    char: "\ud83e\udd5c",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  honey_pot: {
    keywords: [ "bees", "sweet", "kitchen" ],
    char: "\ud83c\udf6f",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  croissant: {
    keywords: [ "food", "bread", "french" ],
    char: "\ud83e\udd50",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  bread: {
    keywords: [ "food", "wheat", "breakfast", "toast" ],
    char: "\ud83c\udf5e",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  baguette_bread: {
    keywords: [ "food", "bread", "french" ],
    char: "\ud83e\udd56",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  bagel: {
    keywords: [ "food", "bread", "bakery", "schmear" ],
    char: "\ud83e\udd6f",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  pretzel: {
    keywords: [ "food", "bread", "twisted" ],
    char: "\ud83e\udd68",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cheese: {
    keywords: [ "food", "chadder" ],
    char: "\ud83e\uddc0",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  egg: {
    keywords: [ "food", "chicken", "breakfast" ],
    char: "\ud83e\udd5a",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  bacon: {
    keywords: [ "food", "breakfast", "pork", "pig", "meat" ],
    char: "\ud83e\udd53",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  steak: {
    keywords: [ "food", "cow", "meat", "cut", "chop", "lambchop", "porkchop" ],
    char: "\ud83e\udd69",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  pancakes: {
    keywords: [ "food", "breakfast", "flapjacks", "hotcakes" ],
    char: "\ud83e\udd5e",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  poultry_leg: {
    keywords: [ "food", "meat", "drumstick", "bird", "chicken", "turkey" ],
    char: "\ud83c\udf57",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  meat_on_bone: {
    keywords: [ "good", "food", "drumstick" ],
    char: "\ud83c\udf56",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  bone: {
    keywords: [ "skeleton" ],
    char: "\ud83e\uddb4",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  fried_shrimp: {
    keywords: [ "food", "animal", "appetizer", "summer" ],
    char: "\ud83c\udf64",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  fried_egg: {
    keywords: [ "food", "breakfast", "kitchen", "egg" ],
    char: "\ud83c\udf73",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  hamburger: {
    keywords: [ "meat", "fast food", "beef", "cheeseburger", "mcdonalds", "burger king" ],
    char: "\ud83c\udf54",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  fries: {
    keywords: [ "chips", "snack", "fast food" ],
    char: "\ud83c\udf5f",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  stuffed_flatbread: {
    keywords: [ "food", "flatbread", "stuffed", "gyro" ],
    char: "\ud83e\udd59",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  hotdog: {
    keywords: [ "food", "frankfurter" ],
    char: "\ud83c\udf2d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  pizza: {
    keywords: [ "food", "party" ],
    char: "\ud83c\udf55",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  sandwich: {
    keywords: [ "food", "lunch", "bread" ],
    char: "\ud83e\udd6a",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  canned_food: {
    keywords: [ "food", "soup" ],
    char: "\ud83e\udd6b",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  spaghetti: {
    keywords: [ "food", "italian", "noodle" ],
    char: "\ud83c\udf5d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  taco: {
    keywords: [ "food", "mexican" ],
    char: "\ud83c\udf2e",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  burrito: {
    keywords: [ "food", "mexican" ],
    char: "\ud83c\udf2f",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  green_salad: {
    keywords: [ "food", "healthy", "lettuce" ],
    char: "\ud83e\udd57",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  shallow_pan_of_food: {
    keywords: [ "food", "cooking", "casserole", "paella" ],
    char: "\ud83e\udd58",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  ramen: {
    keywords: [ "food", "japanese", "noodle", "chopsticks" ],
    char: "\ud83c\udf5c",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  stew: {
    keywords: [ "food", "meat", "soup" ],
    char: "\ud83c\udf72",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  fish_cake: {
    keywords: [ "food", "japan", "sea", "beach", "narutomaki", "pink", "swirl", "kamaboko", "surimi", "ramen" ],
    char: "\ud83c\udf65",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  fortune_cookie: {
    keywords: [ "food", "prophecy" ],
    char: "\ud83e\udd60",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  sushi: {
    keywords: [ "food", "fish", "japanese", "rice" ],
    char: "\ud83c\udf63",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  bento: {
    keywords: [ "food", "japanese", "box" ],
    char: "\ud83c\udf71",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  curry: {
    keywords: [ "food", "spicy", "hot", "indian" ],
    char: "\ud83c\udf5b",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  rice_ball: {
    keywords: [ "food", "japanese" ],
    char: "\ud83c\udf59",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  rice: {
    keywords: [ "food", "china", "asian" ],
    char: "\ud83c\udf5a",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  rice_cracker: {
    keywords: [ "food", "japanese" ],
    char: "\ud83c\udf58",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  oden: {
    keywords: [ "food", "japanese" ],
    char: "\ud83c\udf62",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  dango: {
    keywords: [ "food", "dessert", "sweet", "japanese", "barbecue", "meat" ],
    char: "\ud83c\udf61",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  shaved_ice: {
    keywords: [ "hot", "dessert", "summer" ],
    char: "\ud83c\udf67",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  ice_cream: {
    keywords: [ "food", "hot", "dessert" ],
    char: "\ud83c\udf68",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  icecream: {
    keywords: [ "food", "hot", "dessert", "summer" ],
    char: "\ud83c\udf66",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  pie: {
    keywords: [ "food", "dessert", "pastry" ],
    char: "\ud83e\udd67",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cake: {
    keywords: [ "food", "dessert" ],
    char: "\ud83c\udf70",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cupcake: {
    keywords: [ "food", "dessert", "bakery", "sweet" ],
    char: "\ud83e\uddc1",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  moon_cake: {
    keywords: [ "food", "autumn" ],
    char: "\ud83e\udd6e",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  birthday: {
    keywords: [ "food", "dessert", "cake" ],
    char: "\ud83c\udf82",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  custard: {
    keywords: [ "dessert", "food" ],
    char: "\ud83c\udf6e",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  candy: {
    keywords: [ "snack", "dessert", "sweet", "lolly" ],
    char: "\ud83c\udf6c",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  lollipop: {
    keywords: [ "food", "snack", "candy", "sweet" ],
    char: "\ud83c\udf6d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  chocolate_bar: {
    keywords: [ "food", "snack", "dessert", "sweet" ],
    char: "\ud83c\udf6b",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  popcorn: {
    keywords: [ "food", "movie theater", "films", "snack" ],
    char: "\ud83c\udf7f",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  dumpling: {
    keywords: [ "food", "empanada", "pierogi", "potsticker" ],
    char: "\ud83e\udd5f",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  doughnut: {
    keywords: [ "food", "dessert", "snack", "sweet", "donut" ],
    char: "\ud83c\udf69",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cookie: {
    keywords: [ "food", "snack", "oreo", "chocolate", "sweet", "dessert" ],
    char: "\ud83c\udf6a",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  milk_glass: {
    keywords: [ "beverage", "drink", "cow" ],
    char: "\ud83e\udd5b",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  beer: {
    keywords: [ "relax", "beverage", "drink", "drunk", "party", "pub", "summer", "alcohol", "booze" ],
    char: "\ud83c\udf7a",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  beers: {
    keywords: [ "relax", "beverage", "drink", "drunk", "party", "pub", "summer", "alcohol", "booze" ],
    char: "\ud83c\udf7b",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  clinking_glasses: {
    keywords: [ "beverage", "drink", "party", "alcohol", "celebrate", "cheers", "wine", "champagne", "toast" ],
    char: "\ud83e\udd42",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  wine_glass: {
    keywords: [ "drink", "beverage", "drunk", "alcohol", "booze" ],
    char: "\ud83c\udf77",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  tumbler_glass: {
    keywords: [ "drink", "beverage", "drunk", "alcohol", "liquor", "booze", "bourbon", "scotch", "whisky", "glass", "shot" ],
    char: "\ud83e\udd43",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cocktail: {
    keywords: [ "drink", "drunk", "alcohol", "beverage", "booze", "mojito" ],
    char: "\ud83c\udf78",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  tropical_drink: {
    keywords: [ "beverage", "cocktail", "summer", "beach", "alcohol", "booze", "mojito" ],
    char: "\ud83c\udf79",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  champagne: {
    keywords: [ "drink", "wine", "bottle", "celebration" ],
    char: "\ud83c\udf7e",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  sake: {
    keywords: [ "wine", "drink", "drunk", "beverage", "japanese", "alcohol", "booze" ],
    char: "\ud83c\udf76",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  tea: {
    keywords: [ "drink", "bowl", "breakfast", "green", "british" ],
    char: "\ud83c\udf75",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  cup_with_straw: {
    keywords: [ "drink", "soda" ],
    char: "\ud83e\udd64",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  coffee: {
    keywords: [ "beverage", "caffeine", "latte", "espresso" ],
    char: "\u2615",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  baby_bottle: {
    keywords: [ "food", "container", "milk" ],
    char: "\ud83c\udf7c",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  salt: {
    keywords: [ "condiment", "shaker" ],
    char: "\ud83e\uddc2",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  spoon: {
    keywords: [ "cutlery", "kitchen", "tableware" ],
    char: "\ud83e\udd44",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  fork_and_knife: {
    keywords: [ "cutlery", "kitchen" ],
    char: "\ud83c\udf74",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  plate_with_cutlery: {
    keywords: [ "food", "eat", "meal", "lunch", "dinner", "restaurant" ],
    char: "\ud83c\udf7d",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  bowl_with_spoon: {
    keywords: [ "food", "breakfast", "cereal", "oatmeal", "porridge" ],
    char: "\ud83e\udd63",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  takeout_box: {
    keywords: [ "food", "leftovers" ],
    char: "\ud83e\udd61",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  chopsticks: {
    keywords: [ "food" ],
    char: "\ud83e\udd62",
    fitzpatrick_scale: false,
    category: "food_and_drink"
  },
  soccer: {
    keywords: [ "sports", "football" ],
    char: "\u26bd",
    fitzpatrick_scale: false,
    category: "activity"
  },
  basketball: {
    keywords: [ "sports", "balls", "NBA" ],
    char: "\ud83c\udfc0",
    fitzpatrick_scale: false,
    category: "activity"
  },
  football: {
    keywords: [ "sports", "balls", "NFL" ],
    char: "\ud83c\udfc8",
    fitzpatrick_scale: false,
    category: "activity"
  },
  baseball: {
    keywords: [ "sports", "balls" ],
    char: "\u26be",
    fitzpatrick_scale: false,
    category: "activity"
  },
  softball: {
    keywords: [ "sports", "balls" ],
    char: "\ud83e\udd4e",
    fitzpatrick_scale: false,
    category: "activity"
  },
  tennis: {
    keywords: [ "sports", "balls", "green" ],
    char: "\ud83c\udfbe",
    fitzpatrick_scale: false,
    category: "activity"
  },
  volleyball: {
    keywords: [ "sports", "balls" ],
    char: "\ud83c\udfd0",
    fitzpatrick_scale: false,
    category: "activity"
  },
  rugby_football: {
    keywords: [ "sports", "team" ],
    char: "\ud83c\udfc9",
    fitzpatrick_scale: false,
    category: "activity"
  },
  flying_disc: {
    keywords: [ "sports", "frisbee", "ultimate" ],
    char: "\ud83e\udd4f",
    fitzpatrick_scale: false,
    category: "activity"
  },
  "8ball": {
    keywords: [ "pool", "hobby", "game", "luck", "magic" ],
    char: "\ud83c\udfb1",
    fitzpatrick_scale: false,
    category: "activity"
  },
  golf: {
    keywords: [ "sports", "business", "flag", "hole", "summer" ],
    char: "\u26f3",
    fitzpatrick_scale: false,
    category: "activity"
  },
  golfing_woman: {
    keywords: [ "sports", "business", "woman", "female" ],
    char: "\ud83c\udfcc\ufe0f\u200d\u2640\ufe0f",
    fitzpatrick_scale: false,
    category: "activity"
  },
  golfing_man: {
    keywords: [ "sports", "business" ],
    char: "\ud83c\udfcc",
    fitzpatrick_scale: true,
    category: "activity"
  },
  ping_pong: {
    keywords: [ "sports", "pingpong" ],
    char: "\ud83c\udfd3",
    fitzpatrick_scale: false,
    category: "activity"
  },
  badminton: {
    keywords: [ "sports" ],
    char: "\ud83c\udff8",
    fitzpatrick_scale: false,
    category: "activity"
  },
  goal_net: {
    keywords: [ "sports" ],
    char: "\ud83e\udd45",
    fitzpatrick_scale: false,
    category: "activity"
  },
  ice_hockey: {
    keywords: [ "sports" ],
    char: "\ud83c\udfd2",
    fitzpatrick_scale: false,
    category: "activity"
  },
  field_hockey: {
    keywords: [ "sports" ],
    char: "\ud83c\udfd1",
    fitzpatrick_scale: false,
    category: "activity"
  },
  lacrosse: {
    keywords: [ "sports", "ball", "stick" ],
    char: "\ud83e\udd4d",
    fitzpatrick_scale: false,
    category: "activity"
  },
  cricket: {
    keywords: [ "sports" ],
    char: "\ud83c\udfcf",
    fitzpatrick_scale: false,
    category: "activity"
  },
  ski: {
    keywords: [ "sports", "winter", "cold", "snow" ],
    char: "\ud83c\udfbf",
    fitzpatrick_scale: false,
    category: "activity"
  },
  skier: {
    keywords: [ "sports", "winter", "snow" ],
    char: "\u26f7",
    fitzpatrick_scale: false,
    category: "activity"
  },
  snowboarder: {
    keywords: [ "sports", "winter" ],
    char: "\ud83c\udfc2",
    fitzpatrick_scale: true,
    category: "activity"
  },
  person_fencing: {
    keywords: [ "sports", "fencing", "sword" ],
    char: "\ud83e\udd3a",
    fitzpatrick_scale: false,
    category: "activity"
  },
  women_wrestling: {
    keywords: [ "sports", "wrestlers" ],
    char: "\ud83e\udd3c\u200d\u2640\ufe0f",
    fitzpatrick_scale: false,
    category: "activity"
  },
  men_wrestling: {
    keywords: [ "sports", "wrestlers" ],
    char: "\ud83e\udd3c\u200d\u2642\ufe0f",
    fitzpatrick_scale: false,
    category: "activity"
  },
  woman_cartwheeling: {
    keywords: [ "gymnastics" ],
    char: "\ud83e\udd38\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  man_cartwheeling: {
    keywords: [ "gymnastics" ],
    char: "\ud83e\udd38\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  woman_playing_handball: {
    keywords: [ "sports" ],
    char: "\ud83e\udd3e\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  man_playing_handball: {
    keywords: [ "sports" ],
    char: "\ud83e\udd3e\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  ice_skate: {
    keywords: [ "sports" ],
    char: "\u26f8",
    fitzpatrick_scale: false,
    category: "activity"
  },
  curling_stone: {
    keywords: [ "sports" ],
    char: "\ud83e\udd4c",
    fitzpatrick_scale: false,
    category: "activity"
  },
  skateboard: {
    keywords: [ "board" ],
    char: "\ud83d\udef9",
    fitzpatrick_scale: false,
    category: "activity"
  },
  sled: {
    keywords: [ "sleigh", "luge", "toboggan" ],
    char: "\ud83d\udef7",
    fitzpatrick_scale: false,
    category: "activity"
  },
  bow_and_arrow: {
    keywords: [ "sports" ],
    char: "\ud83c\udff9",
    fitzpatrick_scale: false,
    category: "activity"
  },
  fishing_pole_and_fish: {
    keywords: [ "food", "hobby", "summer" ],
    char: "\ud83c\udfa3",
    fitzpatrick_scale: false,
    category: "activity"
  },
  boxing_glove: {
    keywords: [ "sports", "fighting" ],
    char: "\ud83e\udd4a",
    fitzpatrick_scale: false,
    category: "activity"
  },
  martial_arts_uniform: {
    keywords: [ "judo", "karate", "taekwondo" ],
    char: "\ud83e\udd4b",
    fitzpatrick_scale: false,
    category: "activity"
  },
  rowing_woman: {
    keywords: [ "sports", "hobby", "water", "ship", "woman", "female" ],
    char: "\ud83d\udea3\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  rowing_man: {
    keywords: [ "sports", "hobby", "water", "ship" ],
    char: "\ud83d\udea3",
    fitzpatrick_scale: true,
    category: "activity"
  },
  climbing_woman: {
    keywords: [ "sports", "hobby", "woman", "female", "rock" ],
    char: "\ud83e\uddd7\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  climbing_man: {
    keywords: [ "sports", "hobby", "man", "male", "rock" ],
    char: "\ud83e\uddd7\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  swimming_woman: {
    keywords: [ "sports", "exercise", "human", "athlete", "water", "summer", "woman", "female" ],
    char: "\ud83c\udfca\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  swimming_man: {
    keywords: [ "sports", "exercise", "human", "athlete", "water", "summer" ],
    char: "\ud83c\udfca",
    fitzpatrick_scale: true,
    category: "activity"
  },
  woman_playing_water_polo: {
    keywords: [ "sports", "pool" ],
    char: "\ud83e\udd3d\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  man_playing_water_polo: {
    keywords: [ "sports", "pool" ],
    char: "\ud83e\udd3d\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  woman_in_lotus_position: {
    keywords: [ "woman", "female", "meditation", "yoga", "serenity", "zen", "mindfulness" ],
    char: "\ud83e\uddd8\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  man_in_lotus_position: {
    keywords: [ "man", "male", "meditation", "yoga", "serenity", "zen", "mindfulness" ],
    char: "\ud83e\uddd8\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  surfing_woman: {
    keywords: [ "sports", "ocean", "sea", "summer", "beach", "woman", "female" ],
    char: "\ud83c\udfc4\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  surfing_man: {
    keywords: [ "sports", "ocean", "sea", "summer", "beach" ],
    char: "\ud83c\udfc4",
    fitzpatrick_scale: true,
    category: "activity"
  },
  bath: {
    keywords: [ "clean", "shower", "bathroom" ],
    char: "\ud83d\udec0",
    fitzpatrick_scale: true,
    category: "activity"
  },
  basketball_woman: {
    keywords: [ "sports", "human", "woman", "female" ],
    char: "\u26f9\ufe0f\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  basketball_man: {
    keywords: [ "sports", "human" ],
    char: "\u26f9",
    fitzpatrick_scale: true,
    category: "activity"
  },
  weight_lifting_woman: {
    keywords: [ "sports", "training", "exercise", "woman", "female" ],
    char: "\ud83c\udfcb\ufe0f\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  weight_lifting_man: {
    keywords: [ "sports", "training", "exercise" ],
    char: "\ud83c\udfcb",
    fitzpatrick_scale: true,
    category: "activity"
  },
  biking_woman: {
    keywords: [ "sports", "bike", "exercise", "hipster", "woman", "female" ],
    char: "\ud83d\udeb4\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  biking_man: {
    keywords: [ "sports", "bike", "exercise", "hipster" ],
    char: "\ud83d\udeb4",
    fitzpatrick_scale: true,
    category: "activity"
  },
  mountain_biking_woman: {
    keywords: [ "transportation", "sports", "human", "race", "bike", "woman", "female" ],
    char: "\ud83d\udeb5\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  mountain_biking_man: {
    keywords: [ "transportation", "sports", "human", "race", "bike" ],
    char: "\ud83d\udeb5",
    fitzpatrick_scale: true,
    category: "activity"
  },
  horse_racing: {
    keywords: [ "animal", "betting", "competition", "gambling", "luck" ],
    char: "\ud83c\udfc7",
    fitzpatrick_scale: true,
    category: "activity"
  },
  business_suit_levitating: {
    keywords: [ "suit", "business", "levitate", "hover", "jump" ],
    char: "\ud83d\udd74",
    fitzpatrick_scale: true,
    category: "activity"
  },
  trophy: {
    keywords: [ "win", "award", "contest", "place", "ftw", "ceremony" ],
    char: "\ud83c\udfc6",
    fitzpatrick_scale: false,
    category: "activity"
  },
  running_shirt_with_sash: {
    keywords: [ "play", "pageant" ],
    char: "\ud83c\udfbd",
    fitzpatrick_scale: false,
    category: "activity"
  },
  medal_sports: {
    keywords: [ "award", "winning" ],
    char: "\ud83c\udfc5",
    fitzpatrick_scale: false,
    category: "activity"
  },
  medal_military: {
    keywords: [ "award", "winning", "army" ],
    char: "\ud83c\udf96",
    fitzpatrick_scale: false,
    category: "activity"
  },
  "1st_place_medal": {
    keywords: [ "award", "winning", "first" ],
    char: "\ud83e\udd47",
    fitzpatrick_scale: false,
    category: "activity"
  },
  "2nd_place_medal": {
    keywords: [ "award", "second" ],
    char: "\ud83e\udd48",
    fitzpatrick_scale: false,
    category: "activity"
  },
  "3rd_place_medal": {
    keywords: [ "award", "third" ],
    char: "\ud83e\udd49",
    fitzpatrick_scale: false,
    category: "activity"
  },
  reminder_ribbon: {
    keywords: [ "sports", "cause", "support", "awareness" ],
    char: "\ud83c\udf97",
    fitzpatrick_scale: false,
    category: "activity"
  },
  rosette: {
    keywords: [ "flower", "decoration", "military" ],
    char: "\ud83c\udff5",
    fitzpatrick_scale: false,
    category: "activity"
  },
  ticket: {
    keywords: [ "event", "concert", "pass" ],
    char: "\ud83c\udfab",
    fitzpatrick_scale: false,
    category: "activity"
  },
  tickets: {
    keywords: [ "sports", "concert", "entrance" ],
    char: "\ud83c\udf9f",
    fitzpatrick_scale: false,
    category: "activity"
  },
  performing_arts: {
    keywords: [ "acting", "theater", "drama" ],
    char: "\ud83c\udfad",
    fitzpatrick_scale: false,
    category: "activity"
  },
  art: {
    keywords: [ "design", "paint", "draw", "colors" ],
    char: "\ud83c\udfa8",
    fitzpatrick_scale: false,
    category: "activity"
  },
  circus_tent: {
    keywords: [ "festival", "carnival", "party" ],
    char: "\ud83c\udfaa",
    fitzpatrick_scale: false,
    category: "activity"
  },
  woman_juggling: {
    keywords: [ "juggle", "balance", "skill", "multitask" ],
    char: "\ud83e\udd39\u200d\u2640\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  man_juggling: {
    keywords: [ "juggle", "balance", "skill", "multitask" ],
    char: "\ud83e\udd39\u200d\u2642\ufe0f",
    fitzpatrick_scale: true,
    category: "activity"
  },
  microphone: {
    keywords: [ "sound", "music", "PA", "sing", "talkshow" ],
    char: "\ud83c\udfa4",
    fitzpatrick_scale: false,
    category: "activity"
  },
  headphones: {
    keywords: [ "music", "score", "gadgets" ],
    char: "\ud83c\udfa7",
    fitzpatrick_scale: false,
    category: "activity"
  },
  musical_score: {
    keywords: [ "treble", "clef", "compose" ],
    char: "\ud83c\udfbc",
    fitzpatrick_scale: false,
    category: "activity"
  },
  musical_keyboard: {
    keywords: [ "piano", "instrument", "compose" ],
    char: "\ud83c\udfb9",
    fitzpatrick_scale: false,
    category: "activity"
  },
  drum: {
    keywords: [ "music", "instrument", "drumsticks", "snare" ],
    char: "\ud83e\udd41",
    fitzpatrick_scale: false,
    category: "activity"
  },
  saxophone: {
    keywords: [ "music", "instrument", "jazz", "blues" ],
    char: "\ud83c\udfb7",
    fitzpatrick_scale: false,
    category: "activity"
  },
  trumpet: {
    keywords: [ "music", "brass" ],
    char: "\ud83c\udfba",
    fitzpatrick_scale: false,
    category: "activity"
  },
  guitar: {
    keywords: [ "music", "instrument" ],
    char: "\ud83c\udfb8",
    fitzpatrick_scale: false,
    category: "activity"
  },
  violin: {
    keywords: [ "music", "instrument", "orchestra", "symphony" ],
    char: "\ud83c\udfbb",
    fitzpatrick_scale: false,
    category: "activity"
  },
  clapper: {
    keywords: [ "movie", "film", "record" ],
    char: "\ud83c\udfac",
    fitzpatrick_scale: false,
    category: "activity"
  },
  video_game: {
    keywords: [ "play", "console", "PS4", "controller" ],
    char: "\ud83c\udfae",
    fitzpatrick_scale: false,
    category: "activity"
  },
  space_invader: {
    keywords: [ "game", "arcade", "play" ],
    char: "\ud83d\udc7e",
    fitzpatrick_scale: false,
    category: "activity"
  },
  dart: {
    keywords: [ "game", "play", "bar", "target", "bullseye" ],
    char: "\ud83c\udfaf",
    fitzpatrick_scale: false,
    category: "activity"
  },
  game_die: {
    keywords: [ "dice", "random", "tabletop", "play", "luck" ],
    char: "\ud83c\udfb2",
    fitzpatrick_scale: false,
    category: "activity"
  },
  chess_pawn: {
    keywords: [ "expendable" ],
    char: "\u265f",
    fitzpatrick_scale: false,
    category: "activity"
  },
  slot_machine: {
    keywords: [ "bet", "gamble", "vegas", "fruit machine", "luck", "casino" ],
    char: "\ud83c\udfb0",
    fitzpatrick_scale: false,
    category: "activity"
  },
  jigsaw: {
    keywords: [ "interlocking", "puzzle", "piece" ],
    char: "\ud83e\udde9",
    fitzpatrick_scale: false,
    category: "activity"
  },
  bowling: {
    keywords: [ "sports", "fun", "play" ],
    char: "\ud83c\udfb3",
    fitzpatrick_scale: false,
    category: "activity"
  },
  red_car: {
    keywords: [ "red", "transportation", "vehicle" ],
    char: "\ud83d\ude97",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  taxi: {
    keywords: [ "uber", "vehicle", "cars", "transportation" ],
    char: "\ud83d\ude95",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  blue_car: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude99",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  bus: {
    keywords: [ "car", "vehicle", "transportation" ],
    char: "\ud83d\ude8c",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  trolleybus: {
    keywords: [ "bart", "transportation", "vehicle" ],
    char: "\ud83d\ude8e",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  racing_car: {
    keywords: [ "sports", "race", "fast", "formula", "f1" ],
    char: "\ud83c\udfce",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  police_car: {
    keywords: [ "vehicle", "cars", "transportation", "law", "legal", "enforcement" ],
    char: "\ud83d\ude93",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  ambulance: {
    keywords: [ "health", "911", "hospital" ],
    char: "\ud83d\ude91",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  fire_engine: {
    keywords: [ "transportation", "cars", "vehicle" ],
    char: "\ud83d\ude92",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  minibus: {
    keywords: [ "vehicle", "car", "transportation" ],
    char: "\ud83d\ude90",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  truck: {
    keywords: [ "cars", "transportation" ],
    char: "\ud83d\ude9a",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  articulated_lorry: {
    keywords: [ "vehicle", "cars", "transportation", "express" ],
    char: "\ud83d\ude9b",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  tractor: {
    keywords: [ "vehicle", "car", "farming", "agriculture" ],
    char: "\ud83d\ude9c",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  kick_scooter: {
    keywords: [ "vehicle", "kick", "razor" ],
    char: "\ud83d\udef4",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  motorcycle: {
    keywords: [ "race", "sports", "fast" ],
    char: "\ud83c\udfcd",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  bike: {
    keywords: [ "sports", "bicycle", "exercise", "hipster" ],
    char: "\ud83d\udeb2",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  motor_scooter: {
    keywords: [ "vehicle", "vespa", "sasha" ],
    char: "\ud83d\udef5",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  rotating_light: {
    keywords: [ "police", "ambulance", "911", "emergency", "alert", "error", "pinged", "law", "legal" ],
    char: "\ud83d\udea8",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  oncoming_police_car: {
    keywords: [ "vehicle", "law", "legal", "enforcement", "911" ],
    char: "\ud83d\ude94",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  oncoming_bus: {
    keywords: [ "vehicle", "transportation" ],
    char: "\ud83d\ude8d",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  oncoming_automobile: {
    keywords: [ "car", "vehicle", "transportation" ],
    char: "\ud83d\ude98",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  oncoming_taxi: {
    keywords: [ "vehicle", "cars", "uber" ],
    char: "\ud83d\ude96",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  aerial_tramway: {
    keywords: [ "transportation", "vehicle", "ski" ],
    char: "\ud83d\udea1",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  mountain_cableway: {
    keywords: [ "transportation", "vehicle", "ski" ],
    char: "\ud83d\udea0",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  suspension_railway: {
    keywords: [ "vehicle", "transportation" ],
    char: "\ud83d\ude9f",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  railway_car: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude83",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  train: {
    keywords: [ "transportation", "vehicle", "carriage", "public", "travel" ],
    char: "\ud83d\ude8b",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  monorail: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude9d",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  bullettrain_side: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude84",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  bullettrain_front: {
    keywords: [ "transportation", "vehicle", "speed", "fast", "public", "travel" ],
    char: "\ud83d\ude85",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  light_rail: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude88",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  mountain_railway: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude9e",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  steam_locomotive: {
    keywords: [ "transportation", "vehicle", "train" ],
    char: "\ud83d\ude82",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  train2: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude86",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  metro: {
    keywords: [ "transportation", "blue-square", "mrt", "underground", "tube" ],
    char: "\ud83d\ude87",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  tram: {
    keywords: [ "transportation", "vehicle" ],
    char: "\ud83d\ude8a",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  station: {
    keywords: [ "transportation", "vehicle", "public" ],
    char: "\ud83d\ude89",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  flying_saucer: {
    keywords: [ "transportation", "vehicle", "ufo" ],
    char: "\ud83d\udef8",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  helicopter: {
    keywords: [ "transportation", "vehicle", "fly" ],
    char: "\ud83d\ude81",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  small_airplane: {
    keywords: [ "flight", "transportation", "fly", "vehicle" ],
    char: "\ud83d\udee9",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  airplane: {
    keywords: [ "vehicle", "transportation", "flight", "fly" ],
    char: "\u2708\ufe0f",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  flight_departure: {
    keywords: [ "airport", "flight", "landing" ],
    char: "\ud83d\udeeb",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  flight_arrival: {
    keywords: [ "airport", "flight", "boarding" ],
    char: "\ud83d\udeec",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  sailboat: {
    keywords: [ "ship", "summer", "transportation", "water", "sailing" ],
    char: "\u26f5",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  motor_boat: {
    keywords: [ "ship" ],
    char: "\ud83d\udee5",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  speedboat: {
    keywords: [ "ship", "transportation", "vehicle", "summer" ],
    char: "\ud83d\udea4",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  ferry: {
    keywords: [ "boat", "ship", "yacht" ],
    char: "\u26f4",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  passenger_ship: {
    keywords: [ "yacht", "cruise", "ferry" ],
    char: "\ud83d\udef3",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  rocket: {
    keywords: [ "launch", "ship", "staffmode", "NASA", "outer space", "outer_space", "fly" ],
    char: "\ud83d\ude80",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  artificial_satellite: {
    keywords: [ "communication", "gps", "orbit", "spaceflight", "NASA", "ISS" ],
    char: "\ud83d\udef0",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  seat: {
    keywords: [ "sit", "airplane", "transport", "bus", "flight", "fly" ],
    char: "\ud83d\udcba",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  canoe: {
    keywords: [ "boat", "paddle", "water", "ship" ],
    char: "\ud83d\udef6",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  anchor: {
    keywords: [ "ship", "ferry", "sea", "boat" ],
    char: "\u2693",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  construction: {
    keywords: [ "wip", "progress", "caution", "warning" ],
    char: "\ud83d\udea7",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  fuelpump: {
    keywords: [ "gas station", "petroleum" ],
    char: "\u26fd",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  busstop: {
    keywords: [ "transportation", "wait" ],
    char: "\ud83d\ude8f",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  vertical_traffic_light: {
    keywords: [ "transportation", "driving" ],
    char: "\ud83d\udea6",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  traffic_light: {
    keywords: [ "transportation", "signal" ],
    char: "\ud83d\udea5",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  checkered_flag: {
    keywords: [ "contest", "finishline", "race", "gokart" ],
    char: "\ud83c\udfc1",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  ship: {
    keywords: [ "transportation", "titanic", "deploy" ],
    char: "\ud83d\udea2",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  ferris_wheel: {
    keywords: [ "photo", "carnival", "londoneye" ],
    char: "\ud83c\udfa1",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  roller_coaster: {
    keywords: [ "carnival", "playground", "photo", "fun" ],
    char: "\ud83c\udfa2",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  carousel_horse: {
    keywords: [ "photo", "carnival" ],
    char: "\ud83c\udfa0",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  building_construction: {
    keywords: [ "wip", "working", "progress" ],
    char: "\ud83c\udfd7",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  foggy: {
    keywords: [ "photo", "mountain" ],
    char: "\ud83c\udf01",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  tokyo_tower: {
    keywords: [ "photo", "japanese" ],
    char: "\ud83d\uddfc",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  factory: {
    keywords: [ "building", "industry", "pollution", "smoke" ],
    char: "\ud83c\udfed",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  fountain: {
    keywords: [ "photo", "summer", "water", "fresh" ],
    char: "\u26f2",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  rice_scene: {
    keywords: [ "photo", "japan", "asia", "tsukimi" ],
    char: "\ud83c\udf91",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  mountain: {
    keywords: [ "photo", "nature", "environment" ],
    char: "\u26f0",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  mountain_snow: {
    keywords: [ "photo", "nature", "environment", "winter", "cold" ],
    char: "\ud83c\udfd4",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  mount_fuji: {
    keywords: [ "photo", "mountain", "nature", "japanese" ],
    char: "\ud83d\uddfb",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  volcano: {
    keywords: [ "photo", "nature", "disaster" ],
    char: "\ud83c\udf0b",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  japan: {
    keywords: [ "nation", "country", "japanese", "asia" ],
    char: "\ud83d\uddfe",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  camping: {
    keywords: [ "photo", "outdoors", "tent" ],
    char: "\ud83c\udfd5",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  tent: {
    keywords: [ "photo", "camping", "outdoors" ],
    char: "\u26fa",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  national_park: {
    keywords: [ "photo", "environment", "nature" ],
    char: "\ud83c\udfde",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  motorway: {
    keywords: [ "road", "cupertino", "interstate", "highway" ],
    char: "\ud83d\udee3",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  railway_track: {
    keywords: [ "train", "transportation" ],
    char: "\ud83d\udee4",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  sunrise: {
    keywords: [ "morning", "view", "vacation", "photo" ],
    char: "\ud83c\udf05",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  sunrise_over_mountains: {
    keywords: [ "view", "vacation", "photo" ],
    char: "\ud83c\udf04",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  desert: {
    keywords: [ "photo", "warm", "saharah" ],
    char: "\ud83c\udfdc",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  beach_umbrella: {
    keywords: [ "weather", "summer", "sunny", "sand", "mojito" ],
    char: "\ud83c\udfd6",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  desert_island: {
    keywords: [ "photo", "tropical", "mojito" ],
    char: "\ud83c\udfdd",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  city_sunrise: {
    keywords: [ "photo", "good morning", "dawn" ],
    char: "\ud83c\udf07",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  city_sunset: {
    keywords: [ "photo", "evening", "sky", "buildings" ],
    char: "\ud83c\udf06",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  cityscape: {
    keywords: [ "photo", "night life", "urban" ],
    char: "\ud83c\udfd9",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  night_with_stars: {
    keywords: [ "evening", "city", "downtown" ],
    char: "\ud83c\udf03",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  bridge_at_night: {
    keywords: [ "photo", "sanfrancisco" ],
    char: "\ud83c\udf09",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  milky_way: {
    keywords: [ "photo", "space", "stars" ],
    char: "\ud83c\udf0c",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  stars: {
    keywords: [ "night", "photo" ],
    char: "\ud83c\udf20",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  sparkler: {
    keywords: [ "stars", "night", "shine" ],
    char: "\ud83c\udf87",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  fireworks: {
    keywords: [ "photo", "festival", "carnival", "congratulations" ],
    char: "\ud83c\udf86",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  rainbow: {
    keywords: [ "nature", "happy", "unicorn_face", "photo", "sky", "spring" ],
    char: "\ud83c\udf08",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  houses: {
    keywords: [ "buildings", "photo" ],
    char: "\ud83c\udfd8",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  european_castle: {
    keywords: [ "building", "royalty", "history" ],
    char: "\ud83c\udff0",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  japanese_castle: {
    keywords: [ "photo", "building" ],
    char: "\ud83c\udfef",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  stadium: {
    keywords: [ "photo", "place", "sports", "concert", "venue" ],
    char: "\ud83c\udfdf",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  statue_of_liberty: {
    keywords: [ "american", "newyork" ],
    char: "\ud83d\uddfd",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  house: {
    keywords: [ "building", "home" ],
    char: "\ud83c\udfe0",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  house_with_garden: {
    keywords: [ "home", "plant", "nature" ],
    char: "\ud83c\udfe1",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  derelict_house: {
    keywords: [ "abandon", "evict", "broken", "building" ],
    char: "\ud83c\udfda",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  office: {
    keywords: [ "building", "bureau", "work" ],
    char: "\ud83c\udfe2",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  department_store: {
    keywords: [ "building", "shopping", "mall" ],
    char: "\ud83c\udfec",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  post_office: {
    keywords: [ "building", "envelope", "communication" ],
    char: "\ud83c\udfe3",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  european_post_office: {
    keywords: [ "building", "email" ],
    char: "\ud83c\udfe4",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  hospital: {
    keywords: [ "building", "health", "surgery", "doctor" ],
    char: "\ud83c\udfe5",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  bank: {
    keywords: [ "building", "money", "sales", "cash", "business", "enterprise" ],
    char: "\ud83c\udfe6",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  hotel: {
    keywords: [ "building", "accomodation", "checkin" ],
    char: "\ud83c\udfe8",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  convenience_store: {
    keywords: [ "building", "shopping", "groceries" ],
    char: "\ud83c\udfea",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  school: {
    keywords: [ "building", "student", "education", "learn", "teach" ],
    char: "\ud83c\udfeb",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  love_hotel: {
    keywords: [ "like", "affection", "dating" ],
    char: "\ud83c\udfe9",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  wedding: {
    keywords: [ "love", "like", "affection", "couple", "marriage", "bride", "groom" ],
    char: "\ud83d\udc92",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  classical_building: {
    keywords: [ "art", "culture", "history" ],
    char: "\ud83c\udfdb",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  church: {
    keywords: [ "building", "religion", "christ" ],
    char: "\u26ea",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  mosque: {
    keywords: [ "islam", "worship", "minaret" ],
    char: "\ud83d\udd4c",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  synagogue: {
    keywords: [ "judaism", "worship", "temple", "jewish" ],
    char: "\ud83d\udd4d",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  kaaba: {
    keywords: [ "mecca", "mosque", "islam" ],
    char: "\ud83d\udd4b",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  shinto_shrine: {
    keywords: [ "temple", "japan", "kyoto" ],
    char: "\u26e9",
    fitzpatrick_scale: false,
    category: "travel_and_places"
  },
  watch: {
    keywords: [ "time", "accessories" ],
    char: "\u231a",
    fitzpatrick_scale: false,
    category: "objects"
  },
  iphone: {
    keywords: [ "technology", "apple", "gadgets", "dial" ],
    char: "\ud83d\udcf1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  calling: {
    keywords: [ "iphone", "incoming" ],
    char: "\ud83d\udcf2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  computer: {
    keywords: [ "technology", "laptop", "screen", "display", "monitor" ],
    char: "\ud83d\udcbb",
    fitzpatrick_scale: false,
    category: "objects"
  },
  keyboard: {
    keywords: [ "technology", "computer", "type", "input", "text" ],
    char: "\u2328",
    fitzpatrick_scale: false,
    category: "objects"
  },
  desktop_computer: {
    keywords: [ "technology", "computing", "screen" ],
    char: "\ud83d\udda5",
    fitzpatrick_scale: false,
    category: "objects"
  },
  printer: {
    keywords: [ "paper", "ink" ],
    char: "\ud83d\udda8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  computer_mouse: {
    keywords: [ "click" ],
    char: "\ud83d\uddb1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  trackball: {
    keywords: [ "technology", "trackpad" ],
    char: "\ud83d\uddb2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  joystick: {
    keywords: [ "game", "play" ],
    char: "\ud83d\udd79",
    fitzpatrick_scale: false,
    category: "objects"
  },
  clamp: {
    keywords: [ "tool" ],
    char: "\ud83d\udddc",
    fitzpatrick_scale: false,
    category: "objects"
  },
  minidisc: {
    keywords: [ "technology", "record", "data", "disk", "90s" ],
    char: "\ud83d\udcbd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  floppy_disk: {
    keywords: [ "oldschool", "technology", "save", "90s", "80s" ],
    char: "\ud83d\udcbe",
    fitzpatrick_scale: false,
    category: "objects"
  },
  cd: {
    keywords: [ "technology", "dvd", "disk", "disc", "90s" ],
    char: "\ud83d\udcbf",
    fitzpatrick_scale: false,
    category: "objects"
  },
  dvd: {
    keywords: [ "cd", "disk", "disc" ],
    char: "\ud83d\udcc0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  vhs: {
    keywords: [ "record", "video", "oldschool", "90s", "80s" ],
    char: "\ud83d\udcfc",
    fitzpatrick_scale: false,
    category: "objects"
  },
  camera: {
    keywords: [ "gadgets", "photography" ],
    char: "\ud83d\udcf7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  camera_flash: {
    keywords: [ "photography", "gadgets" ],
    char: "\ud83d\udcf8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  video_camera: {
    keywords: [ "film", "record" ],
    char: "\ud83d\udcf9",
    fitzpatrick_scale: false,
    category: "objects"
  },
  movie_camera: {
    keywords: [ "film", "record" ],
    char: "\ud83c\udfa5",
    fitzpatrick_scale: false,
    category: "objects"
  },
  film_projector: {
    keywords: [ "video", "tape", "record", "movie" ],
    char: "\ud83d\udcfd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  film_strip: {
    keywords: [ "movie" ],
    char: "\ud83c\udf9e",
    fitzpatrick_scale: false,
    category: "objects"
  },
  telephone_receiver: {
    keywords: [ "technology", "communication", "dial" ],
    char: "\ud83d\udcde",
    fitzpatrick_scale: false,
    category: "objects"
  },
  phone: {
    keywords: [ "technology", "communication", "dial", "telephone" ],
    char: "\u260e\ufe0f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  pager: {
    keywords: [ "bbcall", "oldschool", "90s" ],
    char: "\ud83d\udcdf",
    fitzpatrick_scale: false,
    category: "objects"
  },
  fax: {
    keywords: [ "communication", "technology" ],
    char: "\ud83d\udce0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  tv: {
    keywords: [ "technology", "program", "oldschool", "show", "television" ],
    char: "\ud83d\udcfa",
    fitzpatrick_scale: false,
    category: "objects"
  },
  radio: {
    keywords: [ "communication", "music", "podcast", "program" ],
    char: "\ud83d\udcfb",
    fitzpatrick_scale: false,
    category: "objects"
  },
  studio_microphone: {
    keywords: [ "sing", "recording", "artist", "talkshow" ],
    char: "\ud83c\udf99",
    fitzpatrick_scale: false,
    category: "objects"
  },
  level_slider: {
    keywords: [ "scale" ],
    char: "\ud83c\udf9a",
    fitzpatrick_scale: false,
    category: "objects"
  },
  control_knobs: {
    keywords: [ "dial" ],
    char: "\ud83c\udf9b",
    fitzpatrick_scale: false,
    category: "objects"
  },
  compass: {
    keywords: [ "magnetic", "navigation", "orienteering" ],
    char: "\ud83e\udded",
    fitzpatrick_scale: false,
    category: "objects"
  },
  stopwatch: {
    keywords: [ "time", "deadline" ],
    char: "\u23f1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  timer_clock: {
    keywords: [ "alarm" ],
    char: "\u23f2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  alarm_clock: {
    keywords: [ "time", "wake" ],
    char: "\u23f0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  mantelpiece_clock: {
    keywords: [ "time" ],
    char: "\ud83d\udd70",
    fitzpatrick_scale: false,
    category: "objects"
  },
  hourglass_flowing_sand: {
    keywords: [ "oldschool", "time", "countdown" ],
    char: "\u23f3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  hourglass: {
    keywords: [ "time", "clock", "oldschool", "limit", "exam", "quiz", "test" ],
    char: "\u231b",
    fitzpatrick_scale: false,
    category: "objects"
  },
  satellite: {
    keywords: [ "communication", "future", "radio", "space" ],
    char: "\ud83d\udce1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  battery: {
    keywords: [ "power", "energy", "sustain" ],
    char: "\ud83d\udd0b",
    fitzpatrick_scale: false,
    category: "objects"
  },
  electric_plug: {
    keywords: [ "charger", "power" ],
    char: "\ud83d\udd0c",
    fitzpatrick_scale: false,
    category: "objects"
  },
  bulb: {
    keywords: [ "light", "electricity", "idea" ],
    char: "\ud83d\udca1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  flashlight: {
    keywords: [ "dark", "camping", "sight", "night" ],
    char: "\ud83d\udd26",
    fitzpatrick_scale: false,
    category: "objects"
  },
  candle: {
    keywords: [ "fire", "wax" ],
    char: "\ud83d\udd6f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  fire_extinguisher: {
    keywords: [ "quench" ],
    char: "\ud83e\uddef",
    fitzpatrick_scale: false,
    category: "objects"
  },
  wastebasket: {
    keywords: [ "bin", "trash", "rubbish", "garbage", "toss" ],
    char: "\ud83d\uddd1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  oil_drum: {
    keywords: [ "barrell" ],
    char: "\ud83d\udee2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  money_with_wings: {
    keywords: [ "dollar", "bills", "payment", "sale" ],
    char: "\ud83d\udcb8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  dollar: {
    keywords: [ "money", "sales", "bill", "currency" ],
    char: "\ud83d\udcb5",
    fitzpatrick_scale: false,
    category: "objects"
  },
  yen: {
    keywords: [ "money", "sales", "japanese", "dollar", "currency" ],
    char: "\ud83d\udcb4",
    fitzpatrick_scale: false,
    category: "objects"
  },
  euro: {
    keywords: [ "money", "sales", "dollar", "currency" ],
    char: "\ud83d\udcb6",
    fitzpatrick_scale: false,
    category: "objects"
  },
  pound: {
    keywords: [ "british", "sterling", "money", "sales", "bills", "uk", "england", "currency" ],
    char: "\ud83d\udcb7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  moneybag: {
    keywords: [ "dollar", "payment", "coins", "sale" ],
    char: "\ud83d\udcb0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  credit_card: {
    keywords: [ "money", "sales", "dollar", "bill", "payment", "shopping" ],
    char: "\ud83d\udcb3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  gem: {
    keywords: [ "blue", "ruby", "diamond", "jewelry" ],
    char: "\ud83d\udc8e",
    fitzpatrick_scale: false,
    category: "objects"
  },
  balance_scale: {
    keywords: [ "law", "fairness", "weight" ],
    char: "\u2696",
    fitzpatrick_scale: false,
    category: "objects"
  },
  toolbox: {
    keywords: [ "tools", "diy", "fix", "maintainer", "mechanic" ],
    char: "\ud83e\uddf0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  wrench: {
    keywords: [ "tools", "diy", "ikea", "fix", "maintainer" ],
    char: "\ud83d\udd27",
    fitzpatrick_scale: false,
    category: "objects"
  },
  hammer: {
    keywords: [ "tools", "build", "create" ],
    char: "\ud83d\udd28",
    fitzpatrick_scale: false,
    category: "objects"
  },
  hammer_and_pick: {
    keywords: [ "tools", "build", "create" ],
    char: "\u2692",
    fitzpatrick_scale: false,
    category: "objects"
  },
  hammer_and_wrench: {
    keywords: [ "tools", "build", "create" ],
    char: "\ud83d\udee0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  pick: {
    keywords: [ "tools", "dig" ],
    char: "\u26cf",
    fitzpatrick_scale: false,
    category: "objects"
  },
  nut_and_bolt: {
    keywords: [ "handy", "tools", "fix" ],
    char: "\ud83d\udd29",
    fitzpatrick_scale: false,
    category: "objects"
  },
  gear: {
    keywords: [ "cog" ],
    char: "\u2699",
    fitzpatrick_scale: false,
    category: "objects"
  },
  brick: {
    keywords: [ "bricks" ],
    char: "\ud83e\uddf1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  chains: {
    keywords: [ "lock", "arrest" ],
    char: "\u26d3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  magnet: {
    keywords: [ "attraction", "magnetic" ],
    char: "\ud83e\uddf2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  gun: {
    keywords: [ "violence", "weapon", "pistol", "revolver" ],
    char: "\ud83d\udd2b",
    fitzpatrick_scale: false,
    category: "objects"
  },
  bomb: {
    keywords: [ "boom", "explode", "explosion", "terrorism" ],
    char: "\ud83d\udca3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  firecracker: {
    keywords: [ "dynamite", "boom", "explode", "explosion", "explosive" ],
    char: "\ud83e\udde8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  hocho: {
    keywords: [ "knife", "blade", "cutlery", "kitchen", "weapon" ],
    char: "\ud83d\udd2a",
    fitzpatrick_scale: false,
    category: "objects"
  },
  dagger: {
    keywords: [ "weapon" ],
    char: "\ud83d\udde1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  crossed_swords: {
    keywords: [ "weapon" ],
    char: "\u2694",
    fitzpatrick_scale: false,
    category: "objects"
  },
  shield: {
    keywords: [ "protection", "security" ],
    char: "\ud83d\udee1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  smoking: {
    keywords: [ "kills", "tobacco", "cigarette", "joint", "smoke" ],
    char: "\ud83d\udeac",
    fitzpatrick_scale: false,
    category: "objects"
  },
  skull_and_crossbones: {
    keywords: [ "poison", "danger", "deadly", "scary", "death", "pirate", "evil" ],
    char: "\u2620",
    fitzpatrick_scale: false,
    category: "objects"
  },
  coffin: {
    keywords: [ "vampire", "dead", "die", "death", "rip", "graveyard", "cemetery", "casket", "funeral", "box" ],
    char: "\u26b0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  funeral_urn: {
    keywords: [ "dead", "die", "death", "rip", "ashes" ],
    char: "\u26b1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  amphora: {
    keywords: [ "vase", "jar" ],
    char: "\ud83c\udffa",
    fitzpatrick_scale: false,
    category: "objects"
  },
  crystal_ball: {
    keywords: [ "disco", "party", "magic", "circus", "fortune_teller" ],
    char: "\ud83d\udd2e",
    fitzpatrick_scale: false,
    category: "objects"
  },
  prayer_beads: {
    keywords: [ "dhikr", "religious" ],
    char: "\ud83d\udcff",
    fitzpatrick_scale: false,
    category: "objects"
  },
  nazar_amulet: {
    keywords: [ "bead", "charm" ],
    char: "\ud83e\uddff",
    fitzpatrick_scale: false,
    category: "objects"
  },
  barber: {
    keywords: [ "hair", "salon", "style" ],
    char: "\ud83d\udc88",
    fitzpatrick_scale: false,
    category: "objects"
  },
  alembic: {
    keywords: [ "distilling", "science", "experiment", "chemistry" ],
    char: "\u2697",
    fitzpatrick_scale: false,
    category: "objects"
  },
  telescope: {
    keywords: [ "stars", "space", "zoom", "science", "astronomy" ],
    char: "\ud83d\udd2d",
    fitzpatrick_scale: false,
    category: "objects"
  },
  microscope: {
    keywords: [ "laboratory", "experiment", "zoomin", "science", "study" ],
    char: "\ud83d\udd2c",
    fitzpatrick_scale: false,
    category: "objects"
  },
  hole: {
    keywords: [ "embarrassing" ],
    char: "\ud83d\udd73",
    fitzpatrick_scale: false,
    category: "objects"
  },
  pill: {
    keywords: [ "health", "medicine", "doctor", "pharmacy", "drug" ],
    char: "\ud83d\udc8a",
    fitzpatrick_scale: false,
    category: "objects"
  },
  syringe: {
    keywords: [ "health", "hospital", "drugs", "blood", "medicine", "needle", "doctor", "nurse" ],
    char: "\ud83d\udc89",
    fitzpatrick_scale: false,
    category: "objects"
  },
  dna: {
    keywords: [ "biologist", "genetics", "life" ],
    char: "\ud83e\uddec",
    fitzpatrick_scale: false,
    category: "objects"
  },
  microbe: {
    keywords: [ "amoeba", "bacteria", "germs" ],
    char: "\ud83e\udda0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  petri_dish: {
    keywords: [ "bacteria", "biology", "culture", "lab" ],
    char: "\ud83e\uddeb",
    fitzpatrick_scale: false,
    category: "objects"
  },
  test_tube: {
    keywords: [ "chemistry", "experiment", "lab", "science" ],
    char: "\ud83e\uddea",
    fitzpatrick_scale: false,
    category: "objects"
  },
  thermometer: {
    keywords: [ "weather", "temperature", "hot", "cold" ],
    char: "\ud83c\udf21",
    fitzpatrick_scale: false,
    category: "objects"
  },
  broom: {
    keywords: [ "cleaning", "sweeping", "witch" ],
    char: "\ud83e\uddf9",
    fitzpatrick_scale: false,
    category: "objects"
  },
  basket: {
    keywords: [ "laundry" ],
    char: "\ud83e\uddfa",
    fitzpatrick_scale: false,
    category: "objects"
  },
  toilet_paper: {
    keywords: [ "roll" ],
    char: "\ud83e\uddfb",
    fitzpatrick_scale: false,
    category: "objects"
  },
  label: {
    keywords: [ "sale", "tag" ],
    char: "\ud83c\udff7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  bookmark: {
    keywords: [ "favorite", "label", "save" ],
    char: "\ud83d\udd16",
    fitzpatrick_scale: false,
    category: "objects"
  },
  toilet: {
    keywords: [ "restroom", "wc", "washroom", "bathroom", "potty" ],
    char: "\ud83d\udebd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  shower: {
    keywords: [ "clean", "water", "bathroom" ],
    char: "\ud83d\udebf",
    fitzpatrick_scale: false,
    category: "objects"
  },
  bathtub: {
    keywords: [ "clean", "shower", "bathroom" ],
    char: "\ud83d\udec1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  soap: {
    keywords: [ "bar", "bathing", "cleaning", "lather" ],
    char: "\ud83e\uddfc",
    fitzpatrick_scale: false,
    category: "objects"
  },
  sponge: {
    keywords: [ "absorbing", "cleaning", "porous" ],
    char: "\ud83e\uddfd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  lotion_bottle: {
    keywords: [ "moisturizer", "sunscreen" ],
    char: "\ud83e\uddf4",
    fitzpatrick_scale: false,
    category: "objects"
  },
  key: {
    keywords: [ "lock", "door", "password" ],
    char: "\ud83d\udd11",
    fitzpatrick_scale: false,
    category: "objects"
  },
  old_key: {
    keywords: [ "lock", "door", "password" ],
    char: "\ud83d\udddd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  couch_and_lamp: {
    keywords: [ "read", "chill" ],
    char: "\ud83d\udecb",
    fitzpatrick_scale: false,
    category: "objects"
  },
  sleeping_bed: {
    keywords: [ "bed", "rest" ],
    char: "\ud83d\udecc",
    fitzpatrick_scale: true,
    category: "objects"
  },
  bed: {
    keywords: [ "sleep", "rest" ],
    char: "\ud83d\udecf",
    fitzpatrick_scale: false,
    category: "objects"
  },
  door: {
    keywords: [ "house", "entry", "exit" ],
    char: "\ud83d\udeaa",
    fitzpatrick_scale: false,
    category: "objects"
  },
  bellhop_bell: {
    keywords: [ "service" ],
    char: "\ud83d\udece",
    fitzpatrick_scale: false,
    category: "objects"
  },
  teddy_bear: {
    keywords: [ "plush", "stuffed" ],
    char: "\ud83e\uddf8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  framed_picture: {
    keywords: [ "photography" ],
    char: "\ud83d\uddbc",
    fitzpatrick_scale: false,
    category: "objects"
  },
  world_map: {
    keywords: [ "location", "direction" ],
    char: "\ud83d\uddfa",
    fitzpatrick_scale: false,
    category: "objects"
  },
  parasol_on_ground: {
    keywords: [ "weather", "summer" ],
    char: "\u26f1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  moyai: {
    keywords: [ "rock", "easter island", "moai" ],
    char: "\ud83d\uddff",
    fitzpatrick_scale: false,
    category: "objects"
  },
  shopping: {
    keywords: [ "mall", "buy", "purchase" ],
    char: "\ud83d\udecd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  shopping_cart: {
    keywords: [ "trolley" ],
    char: "\ud83d\uded2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  balloon: {
    keywords: [ "party", "celebration", "birthday", "circus" ],
    char: "\ud83c\udf88",
    fitzpatrick_scale: false,
    category: "objects"
  },
  flags: {
    keywords: [ "fish", "japanese", "koinobori", "carp", "banner" ],
    char: "\ud83c\udf8f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  ribbon: {
    keywords: [ "decoration", "pink", "girl", "bowtie" ],
    char: "\ud83c\udf80",
    fitzpatrick_scale: false,
    category: "objects"
  },
  gift: {
    keywords: [ "present", "birthday", "christmas", "xmas" ],
    char: "\ud83c\udf81",
    fitzpatrick_scale: false,
    category: "objects"
  },
  confetti_ball: {
    keywords: [ "festival", "party", "birthday", "circus" ],
    char: "\ud83c\udf8a",
    fitzpatrick_scale: false,
    category: "objects"
  },
  tada: {
    keywords: [ "party", "congratulations", "birthday", "magic", "circus", "celebration" ],
    char: "\ud83c\udf89",
    fitzpatrick_scale: false,
    category: "objects"
  },
  dolls: {
    keywords: [ "japanese", "toy", "kimono" ],
    char: "\ud83c\udf8e",
    fitzpatrick_scale: false,
    category: "objects"
  },
  wind_chime: {
    keywords: [ "nature", "ding", "spring", "bell" ],
    char: "\ud83c\udf90",
    fitzpatrick_scale: false,
    category: "objects"
  },
  crossed_flags: {
    keywords: [ "japanese", "nation", "country", "border" ],
    char: "\ud83c\udf8c",
    fitzpatrick_scale: false,
    category: "objects"
  },
  izakaya_lantern: {
    keywords: [ "light", "paper", "halloween", "spooky" ],
    char: "\ud83c\udfee",
    fitzpatrick_scale: false,
    category: "objects"
  },
  red_envelope: {
    keywords: [ "gift" ],
    char: "\ud83e\udde7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  email: {
    keywords: [ "letter", "postal", "inbox", "communication" ],
    char: "\u2709\ufe0f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  envelope_with_arrow: {
    keywords: [ "email", "communication" ],
    char: "\ud83d\udce9",
    fitzpatrick_scale: false,
    category: "objects"
  },
  incoming_envelope: {
    keywords: [ "email", "inbox" ],
    char: "\ud83d\udce8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  "e-mail": {
    keywords: [ "communication", "inbox" ],
    char: "\ud83d\udce7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  love_letter: {
    keywords: [ "email", "like", "affection", "envelope", "valentines" ],
    char: "\ud83d\udc8c",
    fitzpatrick_scale: false,
    category: "objects"
  },
  postbox: {
    keywords: [ "email", "letter", "envelope" ],
    char: "\ud83d\udcee",
    fitzpatrick_scale: false,
    category: "objects"
  },
  mailbox_closed: {
    keywords: [ "email", "communication", "inbox" ],
    char: "\ud83d\udcea",
    fitzpatrick_scale: false,
    category: "objects"
  },
  mailbox: {
    keywords: [ "email", "inbox", "communication" ],
    char: "\ud83d\udceb",
    fitzpatrick_scale: false,
    category: "objects"
  },
  mailbox_with_mail: {
    keywords: [ "email", "inbox", "communication" ],
    char: "\ud83d\udcec",
    fitzpatrick_scale: false,
    category: "objects"
  },
  mailbox_with_no_mail: {
    keywords: [ "email", "inbox" ],
    char: "\ud83d\udced",
    fitzpatrick_scale: false,
    category: "objects"
  },
  package: {
    keywords: [ "mail", "gift", "cardboard", "box", "moving" ],
    char: "\ud83d\udce6",
    fitzpatrick_scale: false,
    category: "objects"
  },
  postal_horn: {
    keywords: [ "instrument", "music" ],
    char: "\ud83d\udcef",
    fitzpatrick_scale: false,
    category: "objects"
  },
  inbox_tray: {
    keywords: [ "email", "documents" ],
    char: "\ud83d\udce5",
    fitzpatrick_scale: false,
    category: "objects"
  },
  outbox_tray: {
    keywords: [ "inbox", "email" ],
    char: "\ud83d\udce4",
    fitzpatrick_scale: false,
    category: "objects"
  },
  scroll: {
    keywords: [ "documents", "ancient", "history", "paper" ],
    char: "\ud83d\udcdc",
    fitzpatrick_scale: false,
    category: "objects"
  },
  page_with_curl: {
    keywords: [ "documents", "office", "paper" ],
    char: "\ud83d\udcc3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  bookmark_tabs: {
    keywords: [ "favorite", "save", "order", "tidy" ],
    char: "\ud83d\udcd1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  receipt: {
    keywords: [ "accounting", "expenses" ],
    char: "\ud83e\uddfe",
    fitzpatrick_scale: false,
    category: "objects"
  },
  bar_chart: {
    keywords: [ "graph", "presentation", "stats" ],
    char: "\ud83d\udcca",
    fitzpatrick_scale: false,
    category: "objects"
  },
  chart_with_upwards_trend: {
    keywords: [ "graph", "presentation", "stats", "recovery", "business", "economics", "money", "sales", "good", "success" ],
    char: "\ud83d\udcc8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  chart_with_downwards_trend: {
    keywords: [ "graph", "presentation", "stats", "recession", "business", "economics", "money", "sales", "bad", "failure" ],
    char: "\ud83d\udcc9",
    fitzpatrick_scale: false,
    category: "objects"
  },
  page_facing_up: {
    keywords: [ "documents", "office", "paper", "information" ],
    char: "\ud83d\udcc4",
    fitzpatrick_scale: false,
    category: "objects"
  },
  date: {
    keywords: [ "calendar", "schedule" ],
    char: "\ud83d\udcc5",
    fitzpatrick_scale: false,
    category: "objects"
  },
  calendar: {
    keywords: [ "schedule", "date", "planning" ],
    char: "\ud83d\udcc6",
    fitzpatrick_scale: false,
    category: "objects"
  },
  spiral_calendar: {
    keywords: [ "date", "schedule", "planning" ],
    char: "\ud83d\uddd3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  card_index: {
    keywords: [ "business", "stationery" ],
    char: "\ud83d\udcc7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  card_file_box: {
    keywords: [ "business", "stationery" ],
    char: "\ud83d\uddc3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  ballot_box: {
    keywords: [ "election", "vote" ],
    char: "\ud83d\uddf3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  file_cabinet: {
    keywords: [ "filing", "organizing" ],
    char: "\ud83d\uddc4",
    fitzpatrick_scale: false,
    category: "objects"
  },
  clipboard: {
    keywords: [ "stationery", "documents" ],
    char: "\ud83d\udccb",
    fitzpatrick_scale: false,
    category: "objects"
  },
  spiral_notepad: {
    keywords: [ "memo", "stationery" ],
    char: "\ud83d\uddd2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  file_folder: {
    keywords: [ "documents", "business", "office" ],
    char: "\ud83d\udcc1",
    fitzpatrick_scale: false,
    category: "objects"
  },
  open_file_folder: {
    keywords: [ "documents", "load" ],
    char: "\ud83d\udcc2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  card_index_dividers: {
    keywords: [ "organizing", "business", "stationery" ],
    char: "\ud83d\uddc2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  newspaper_roll: {
    keywords: [ "press", "headline" ],
    char: "\ud83d\uddde",
    fitzpatrick_scale: false,
    category: "objects"
  },
  newspaper: {
    keywords: [ "press", "headline" ],
    char: "\ud83d\udcf0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  notebook: {
    keywords: [ "stationery", "record", "notes", "paper", "study" ],
    char: "\ud83d\udcd3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  closed_book: {
    keywords: [ "read", "library", "knowledge", "textbook", "learn" ],
    char: "\ud83d\udcd5",
    fitzpatrick_scale: false,
    category: "objects"
  },
  green_book: {
    keywords: [ "read", "library", "knowledge", "study" ],
    char: "\ud83d\udcd7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  blue_book: {
    keywords: [ "read", "library", "knowledge", "learn", "study" ],
    char: "\ud83d\udcd8",
    fitzpatrick_scale: false,
    category: "objects"
  },
  orange_book: {
    keywords: [ "read", "library", "knowledge", "textbook", "study" ],
    char: "\ud83d\udcd9",
    fitzpatrick_scale: false,
    category: "objects"
  },
  notebook_with_decorative_cover: {
    keywords: [ "classroom", "notes", "record", "paper", "study" ],
    char: "\ud83d\udcd4",
    fitzpatrick_scale: false,
    category: "objects"
  },
  ledger: {
    keywords: [ "notes", "paper" ],
    char: "\ud83d\udcd2",
    fitzpatrick_scale: false,
    category: "objects"
  },
  books: {
    keywords: [ "literature", "library", "study" ],
    char: "\ud83d\udcda",
    fitzpatrick_scale: false,
    category: "objects"
  },
  open_book: {
    keywords: [ "book", "read", "library", "knowledge", "literature", "learn", "study" ],
    char: "\ud83d\udcd6",
    fitzpatrick_scale: false,
    category: "objects"
  },
  safety_pin: {
    keywords: [ "diaper" ],
    char: "\ud83e\uddf7",
    fitzpatrick_scale: false,
    category: "objects"
  },
  link: {
    keywords: [ "rings", "url" ],
    char: "\ud83d\udd17",
    fitzpatrick_scale: false,
    category: "objects"
  },
  paperclip: {
    keywords: [ "documents", "stationery" ],
    char: "\ud83d\udcce",
    fitzpatrick_scale: false,
    category: "objects"
  },
  paperclips: {
    keywords: [ "documents", "stationery" ],
    char: "\ud83d\udd87",
    fitzpatrick_scale: false,
    category: "objects"
  },
  scissors: {
    keywords: [ "stationery", "cut" ],
    char: "\u2702\ufe0f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  triangular_ruler: {
    keywords: [ "stationery", "math", "architect", "sketch" ],
    char: "\ud83d\udcd0",
    fitzpatrick_scale: false,
    category: "objects"
  },
  straight_ruler: {
    keywords: [ "stationery", "calculate", "length", "math", "school", "drawing", "architect", "sketch" ],
    char: "\ud83d\udccf",
    fitzpatrick_scale: false,
    category: "objects"
  },
  abacus: {
    keywords: [ "calculation" ],
    char: "\ud83e\uddee",
    fitzpatrick_scale: false,
    category: "objects"
  },
  pushpin: {
    keywords: [ "stationery", "mark", "here" ],
    char: "\ud83d\udccc",
    fitzpatrick_scale: false,
    category: "objects"
  },
  round_pushpin: {
    keywords: [ "stationery", "location", "map", "here" ],
    char: "\ud83d\udccd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  triangular_flag_on_post: {
    keywords: [ "mark", "milestone", "place" ],
    char: "\ud83d\udea9",
    fitzpatrick_scale: false,
    category: "objects"
  },
  white_flag: {
    keywords: [ "losing", "loser", "lost", "surrender", "give up", "fail" ],
    char: "\ud83c\udff3",
    fitzpatrick_scale: false,
    category: "objects"
  },
  black_flag: {
    keywords: [ "pirate" ],
    char: "\ud83c\udff4",
    fitzpatrick_scale: false,
    category: "objects"
  },
  rainbow_flag: {
    keywords: [ "flag", "rainbow", "pride", "gay", "lgbt", "glbt", "queer", "homosexual", "lesbian", "bisexual", "transgender" ],
    char: "\ud83c\udff3\ufe0f\u200d\ud83c\udf08",
    fitzpatrick_scale: false,
    category: "objects"
  },
  closed_lock_with_key: {
    keywords: [ "security", "privacy" ],
    char: "\ud83d\udd10",
    fitzpatrick_scale: false,
    category: "objects"
  },
  lock: {
    keywords: [ "security", "password", "padlock" ],
    char: "\ud83d\udd12",
    fitzpatrick_scale: false,
    category: "objects"
  },
  unlock: {
    keywords: [ "privacy", "security" ],
    char: "\ud83d\udd13",
    fitzpatrick_scale: false,
    category: "objects"
  },
  lock_with_ink_pen: {
    keywords: [ "security", "secret" ],
    char: "\ud83d\udd0f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  pen: {
    keywords: [ "stationery", "writing", "write" ],
    char: "\ud83d\udd8a",
    fitzpatrick_scale: false,
    category: "objects"
  },
  fountain_pen: {
    keywords: [ "stationery", "writing", "write" ],
    char: "\ud83d\udd8b",
    fitzpatrick_scale: false,
    category: "objects"
  },
  black_nib: {
    keywords: [ "pen", "stationery", "writing", "write" ],
    char: "\u2712\ufe0f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  memo: {
    keywords: [ "write", "documents", "stationery", "pencil", "paper", "writing", "legal", "exam", "quiz", "test", "study", "compose" ],
    char: "\ud83d\udcdd",
    fitzpatrick_scale: false,
    category: "objects"
  },
  pencil2: {
    keywords: [ "stationery", "write", "paper", "writing", "school", "study" ],
    char: "\u270f\ufe0f",
    fitzpatrick_scale: false,
    category: "objects"
  },
  crayon: {
    keywords: [ "drawing", "creativity" ],
    char: "\ud83d\udd8d",
    fitzpatrick_scale: false,
    category: "objects"
  },
  paintbrush: {
    keywords: [ "drawing", "creativity", "art" ],
    char: "\ud83d\udd8c",
    fitzpatrick_scale: false,
    category: "objects"
  },
  mag: {
    keywords: [ "search", "zoom", "find", "detective" ],
    char: "\ud83d\udd0d",
    fitzpatrick_scale: false,
    category: "objects"
  },
  mag_right: {
    keywords: [ "search", "zoom", "find", "detective" ],
    char: "\ud83d\udd0e",
    fitzpatrick_scale: false,
    category: "objects"
  },
  heart: {
    keywords: [ "love", "like", "valentines" ],
    char: "\u2764\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  orange_heart: {
    keywords: [ "love", "like", "affection", "valentines" ],
    char: "\ud83e\udde1",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  yellow_heart: {
    keywords: [ "love", "like", "affection", "valentines" ],
    char: "\ud83d\udc9b",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  green_heart: {
    keywords: [ "love", "like", "affection", "valentines" ],
    char: "\ud83d\udc9a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  blue_heart: {
    keywords: [ "love", "like", "affection", "valentines" ],
    char: "\ud83d\udc99",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  purple_heart: {
    keywords: [ "love", "like", "affection", "valentines" ],
    char: "\ud83d\udc9c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_heart: {
    keywords: [ "evil" ],
    char: "\ud83d\udda4",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  broken_heart: {
    keywords: [ "sad", "sorry", "break", "heart", "heartbreak" ],
    char: "\ud83d\udc94",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heavy_heart_exclamation: {
    keywords: [ "decoration", "love" ],
    char: "\u2763",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  two_hearts: {
    keywords: [ "love", "like", "affection", "valentines", "heart" ],
    char: "\ud83d\udc95",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  revolving_hearts: {
    keywords: [ "love", "like", "affection", "valentines" ],
    char: "\ud83d\udc9e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heartbeat: {
    keywords: [ "love", "like", "affection", "valentines", "pink", "heart" ],
    char: "\ud83d\udc93",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heartpulse: {
    keywords: [ "like", "love", "affection", "valentines", "pink" ],
    char: "\ud83d\udc97",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  sparkling_heart: {
    keywords: [ "love", "like", "affection", "valentines" ],
    char: "\ud83d\udc96",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  cupid: {
    keywords: [ "love", "like", "heart", "affection", "valentines" ],
    char: "\ud83d\udc98",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  gift_heart: {
    keywords: [ "love", "valentines" ],
    char: "\ud83d\udc9d",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heart_decoration: {
    keywords: [ "purple-square", "love", "like" ],
    char: "\ud83d\udc9f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  peace_symbol: {
    keywords: [ "hippie" ],
    char: "\u262e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  latin_cross: {
    keywords: [ "christianity" ],
    char: "\u271d",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  star_and_crescent: {
    keywords: [ "islam" ],
    char: "\u262a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  om: {
    keywords: [ "hinduism", "buddhism", "sikhism", "jainism" ],
    char: "\ud83d\udd49",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  wheel_of_dharma: {
    keywords: [ "hinduism", "buddhism", "sikhism", "jainism" ],
    char: "\u2638",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  star_of_david: {
    keywords: [ "judaism" ],
    char: "\u2721",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  six_pointed_star: {
    keywords: [ "purple-square", "religion", "jewish", "hexagram" ],
    char: "\ud83d\udd2f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  menorah: {
    keywords: [ "hanukkah", "candles", "jewish" ],
    char: "\ud83d\udd4e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  yin_yang: {
    keywords: [ "balance" ],
    char: "\u262f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  orthodox_cross: {
    keywords: [ "suppedaneum", "religion" ],
    char: "\u2626",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  place_of_worship: {
    keywords: [ "religion", "church", "temple", "prayer" ],
    char: "\ud83d\uded0",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  ophiuchus: {
    keywords: [ "sign", "purple-square", "constellation", "astrology" ],
    char: "\u26ce",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  aries: {
    keywords: [ "sign", "purple-square", "zodiac", "astrology" ],
    char: "\u2648",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  taurus: {
    keywords: [ "purple-square", "sign", "zodiac", "astrology" ],
    char: "\u2649",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  gemini: {
    keywords: [ "sign", "zodiac", "purple-square", "astrology" ],
    char: "\u264a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  cancer: {
    keywords: [ "sign", "zodiac", "purple-square", "astrology" ],
    char: "\u264b",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  leo: {
    keywords: [ "sign", "purple-square", "zodiac", "astrology" ],
    char: "\u264c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  virgo: {
    keywords: [ "sign", "zodiac", "purple-square", "astrology" ],
    char: "\u264d",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  libra: {
    keywords: [ "sign", "purple-square", "zodiac", "astrology" ],
    char: "\u264e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  scorpius: {
    keywords: [ "sign", "zodiac", "purple-square", "astrology", "scorpio" ],
    char: "\u264f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  sagittarius: {
    keywords: [ "sign", "zodiac", "purple-square", "astrology" ],
    char: "\u2650",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  capricorn: {
    keywords: [ "sign", "zodiac", "purple-square", "astrology" ],
    char: "\u2651",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  aquarius: {
    keywords: [ "sign", "purple-square", "zodiac", "astrology" ],
    char: "\u2652",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  pisces: {
    keywords: [ "purple-square", "sign", "zodiac", "astrology" ],
    char: "\u2653",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  id: {
    keywords: [ "purple-square", "words" ],
    char: "\ud83c\udd94",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  atom_symbol: {
    keywords: [ "science", "physics", "chemistry" ],
    char: "\u269b",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u7a7a: {
    keywords: [ "kanji", "japanese", "chinese", "empty", "sky", "blue-square" ],
    char: "\ud83c\ude33",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u5272: {
    keywords: [ "cut", "divide", "chinese", "kanji", "pink-square" ],
    char: "\ud83c\ude39",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  radioactive: {
    keywords: [ "nuclear", "danger" ],
    char: "\u2622",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  biohazard: {
    keywords: [ "danger" ],
    char: "\u2623",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  mobile_phone_off: {
    keywords: [ "mute", "orange-square", "silence", "quiet" ],
    char: "\ud83d\udcf4",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  vibration_mode: {
    keywords: [ "orange-square", "phone" ],
    char: "\ud83d\udcf3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u6709: {
    keywords: [ "orange-square", "chinese", "have", "kanji" ],
    char: "\ud83c\ude36",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u7121: {
    keywords: [ "nothing", "chinese", "kanji", "japanese", "orange-square" ],
    char: "\ud83c\ude1a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u7533: {
    keywords: [ "chinese", "japanese", "kanji", "orange-square" ],
    char: "\ud83c\ude38",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u55b6: {
    keywords: [ "japanese", "opening hours", "orange-square" ],
    char: "\ud83c\ude3a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u6708: {
    keywords: [ "chinese", "month", "moon", "japanese", "orange-square", "kanji" ],
    char: "\ud83c\ude37\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  eight_pointed_black_star: {
    keywords: [ "orange-square", "shape", "polygon" ],
    char: "\u2734\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  vs: {
    keywords: [ "words", "orange-square" ],
    char: "\ud83c\udd9a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  accept: {
    keywords: [ "ok", "good", "chinese", "kanji", "agree", "yes", "orange-circle" ],
    char: "\ud83c\ude51",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_flower: {
    keywords: [ "japanese", "spring" ],
    char: "\ud83d\udcae",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  ideograph_advantage: {
    keywords: [ "chinese", "kanji", "obtain", "get", "circle" ],
    char: "\ud83c\ude50",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  secret: {
    keywords: [ "privacy", "chinese", "sshh", "kanji", "red-circle" ],
    char: "\u3299\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  congratulations: {
    keywords: [ "chinese", "kanji", "japanese", "red-circle" ],
    char: "\u3297\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u5408: {
    keywords: [ "japanese", "chinese", "join", "kanji", "red-square" ],
    char: "\ud83c\ude34",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u6e80: {
    keywords: [ "full", "chinese", "japanese", "red-square", "kanji" ],
    char: "\ud83c\ude35",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u7981: {
    keywords: [ "kanji", "japanese", "chinese", "forbidden", "limit", "restricted", "red-square" ],
    char: "\ud83c\ude32",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  a: {
    keywords: [ "red-square", "alphabet", "letter" ],
    char: "\ud83c\udd70\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  b: {
    keywords: [ "red-square", "alphabet", "letter" ],
    char: "\ud83c\udd71\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  ab: {
    keywords: [ "red-square", "alphabet" ],
    char: "\ud83c\udd8e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  cl: {
    keywords: [ "alphabet", "words", "red-square" ],
    char: "\ud83c\udd91",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  o2: {
    keywords: [ "alphabet", "red-square", "letter" ],
    char: "\ud83c\udd7e\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  sos: {
    keywords: [ "help", "red-square", "words", "emergency", "911" ],
    char: "\ud83c\udd98",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  no_entry: {
    keywords: [ "limit", "security", "privacy", "bad", "denied", "stop", "circle" ],
    char: "\u26d4",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  name_badge: {
    keywords: [ "fire", "forbid" ],
    char: "\ud83d\udcdb",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  no_entry_sign: {
    keywords: [ "forbid", "stop", "limit", "denied", "disallow", "circle" ],
    char: "\ud83d\udeab",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  x: {
    keywords: [ "no", "delete", "remove", "cancel", "red" ],
    char: "\u274c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  o: {
    keywords: [ "circle", "round" ],
    char: "\u2b55",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  stop_sign: {
    keywords: [ "stop" ],
    char: "\ud83d\uded1",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  anger: {
    keywords: [ "angry", "mad" ],
    char: "\ud83d\udca2",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  hotsprings: {
    keywords: [ "bath", "warm", "relax" ],
    char: "\u2668\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  no_pedestrians: {
    keywords: [ "rules", "crossing", "walking", "circle" ],
    char: "\ud83d\udeb7",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  do_not_litter: {
    keywords: [ "trash", "bin", "garbage", "circle" ],
    char: "\ud83d\udeaf",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  no_bicycles: {
    keywords: [ "cyclist", "prohibited", "circle" ],
    char: "\ud83d\udeb3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  "non-potable_water": {
    keywords: [ "drink", "faucet", "tap", "circle" ],
    char: "\ud83d\udeb1",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  underage: {
    keywords: [ "18", "drink", "pub", "night", "minor", "circle" ],
    char: "\ud83d\udd1e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  no_mobile_phones: {
    keywords: [ "iphone", "mute", "circle" ],
    char: "\ud83d\udcf5",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  exclamation: {
    keywords: [ "heavy_exclamation_mark", "danger", "surprise", "punctuation", "wow", "warning" ],
    char: "\u2757",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  grey_exclamation: {
    keywords: [ "surprise", "punctuation", "gray", "wow", "warning" ],
    char: "\u2755",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  question: {
    keywords: [ "doubt", "confused" ],
    char: "\u2753",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  grey_question: {
    keywords: [ "doubts", "gray", "huh", "confused" ],
    char: "\u2754",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  bangbang: {
    keywords: [ "exclamation", "surprise" ],
    char: "\u203c\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  interrobang: {
    keywords: [ "wat", "punctuation", "surprise" ],
    char: "\u2049\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  100: {
    keywords: [ "score", "perfect", "numbers", "century", "exam", "quiz", "test", "pass", "hundred" ],
    char: "\ud83d\udcaf",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  low_brightness: {
    keywords: [ "sun", "afternoon", "warm", "summer" ],
    char: "\ud83d\udd05",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  high_brightness: {
    keywords: [ "sun", "light" ],
    char: "\ud83d\udd06",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  trident: {
    keywords: [ "weapon", "spear" ],
    char: "\ud83d\udd31",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  fleur_de_lis: {
    keywords: [ "decorative", "scout" ],
    char: "\u269c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  part_alternation_mark: {
    keywords: [ "graph", "presentation", "stats", "business", "economics", "bad" ],
    char: "\u303d\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  warning: {
    keywords: [ "exclamation", "wip", "alert", "error", "problem", "issue" ],
    char: "\u26a0\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  children_crossing: {
    keywords: [ "school", "warning", "danger", "sign", "driving", "yellow-diamond" ],
    char: "\ud83d\udeb8",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  beginner: {
    keywords: [ "badge", "shield" ],
    char: "\ud83d\udd30",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  recycle: {
    keywords: [ "arrow", "environment", "garbage", "trash" ],
    char: "\u267b\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  u6307: {
    keywords: [ "chinese", "point", "green-square", "kanji" ],
    char: "\ud83c\ude2f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  chart: {
    keywords: [ "green-square", "graph", "presentation", "stats" ],
    char: "\ud83d\udcb9",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  sparkle: {
    keywords: [ "stars", "green-square", "awesome", "good", "fireworks" ],
    char: "\u2747\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  eight_spoked_asterisk: {
    keywords: [ "star", "sparkle", "green-square" ],
    char: "\u2733\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  negative_squared_cross_mark: {
    keywords: [ "x", "green-square", "no", "deny" ],
    char: "\u274e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_check_mark: {
    keywords: [ "green-square", "ok", "agree", "vote", "election", "answer", "tick" ],
    char: "\u2705",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  diamond_shape_with_a_dot_inside: {
    keywords: [ "jewel", "blue", "gem", "crystal", "fancy" ],
    char: "\ud83d\udca0",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  cyclone: {
    keywords: [ "weather", "swirl", "blue", "cloud", "vortex", "spiral", "whirlpool", "spin", "tornado", "hurricane", "typhoon" ],
    char: "\ud83c\udf00",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  loop: {
    keywords: [ "tape", "cassette" ],
    char: "\u27bf",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  globe_with_meridians: {
    keywords: [ "earth", "international", "world", "internet", "interweb", "i18n" ],
    char: "\ud83c\udf10",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  m: {
    keywords: [ "alphabet", "blue-circle", "letter" ],
    char: "\u24c2\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  atm: {
    keywords: [ "money", "sales", "cash", "blue-square", "payment", "bank" ],
    char: "\ud83c\udfe7",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  sa: {
    keywords: [ "japanese", "blue-square", "katakana" ],
    char: "\ud83c\ude02\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  passport_control: {
    keywords: [ "custom", "blue-square" ],
    char: "\ud83d\udec2",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  customs: {
    keywords: [ "passport", "border", "blue-square" ],
    char: "\ud83d\udec3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  baggage_claim: {
    keywords: [ "blue-square", "airport", "transport" ],
    char: "\ud83d\udec4",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  left_luggage: {
    keywords: [ "blue-square", "travel" ],
    char: "\ud83d\udec5",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  wheelchair: {
    keywords: [ "blue-square", "disabled", "a11y", "accessibility" ],
    char: "\u267f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  no_smoking: {
    keywords: [ "cigarette", "blue-square", "smell", "smoke" ],
    char: "\ud83d\udead",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  wc: {
    keywords: [ "toilet", "restroom", "blue-square" ],
    char: "\ud83d\udebe",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  parking: {
    keywords: [ "cars", "blue-square", "alphabet", "letter" ],
    char: "\ud83c\udd7f\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  potable_water: {
    keywords: [ "blue-square", "liquid", "restroom", "cleaning", "faucet" ],
    char: "\ud83d\udeb0",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  mens: {
    keywords: [ "toilet", "restroom", "wc", "blue-square", "gender", "male" ],
    char: "\ud83d\udeb9",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  womens: {
    keywords: [ "purple-square", "woman", "female", "toilet", "loo", "restroom", "gender" ],
    char: "\ud83d\udeba",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  baby_symbol: {
    keywords: [ "orange-square", "child" ],
    char: "\ud83d\udebc",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  restroom: {
    keywords: [ "blue-square", "toilet", "refresh", "wc", "gender" ],
    char: "\ud83d\udebb",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  put_litter_in_its_place: {
    keywords: [ "blue-square", "sign", "human", "info" ],
    char: "\ud83d\udeae",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  cinema: {
    keywords: [ "blue-square", "record", "film", "movie", "curtain", "stage", "theater" ],
    char: "\ud83c\udfa6",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  signal_strength: {
    keywords: [ "blue-square", "reception", "phone", "internet", "connection", "wifi", "bluetooth", "bars" ],
    char: "\ud83d\udcf6",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  koko: {
    keywords: [ "blue-square", "here", "katakana", "japanese", "destination" ],
    char: "\ud83c\ude01",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  ng: {
    keywords: [ "blue-square", "words", "shape", "icon" ],
    char: "\ud83c\udd96",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  ok: {
    keywords: [ "good", "agree", "yes", "blue-square" ],
    char: "\ud83c\udd97",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  up: {
    keywords: [ "blue-square", "above", "high" ],
    char: "\ud83c\udd99",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  cool: {
    keywords: [ "words", "blue-square" ],
    char: "\ud83c\udd92",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  new: {
    keywords: [ "blue-square", "words", "start" ],
    char: "\ud83c\udd95",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  free: {
    keywords: [ "blue-square", "words" ],
    char: "\ud83c\udd93",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  zero: {
    keywords: [ "0", "numbers", "blue-square", "null" ],
    char: "0\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  one: {
    keywords: [ "blue-square", "numbers", "1" ],
    char: "1\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  two: {
    keywords: [ "numbers", "2", "prime", "blue-square" ],
    char: "2\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  three: {
    keywords: [ "3", "numbers", "prime", "blue-square" ],
    char: "3\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  four: {
    keywords: [ "4", "numbers", "blue-square" ],
    char: "4\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  five: {
    keywords: [ "5", "numbers", "blue-square", "prime" ],
    char: "5\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  six: {
    keywords: [ "6", "numbers", "blue-square" ],
    char: "6\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  seven: {
    keywords: [ "7", "numbers", "blue-square", "prime" ],
    char: "7\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  eight: {
    keywords: [ "8", "blue-square", "numbers" ],
    char: "8\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  nine: {
    keywords: [ "blue-square", "numbers", "9" ],
    char: "9\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  keycap_ten: {
    keywords: [ "numbers", "10", "blue-square" ],
    char: "\ud83d\udd1f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  asterisk: {
    keywords: [ "star", "keycap" ],
    char: "*\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  1234: {
    keywords: [ "numbers", "blue-square" ],
    char: "\ud83d\udd22",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  eject_button: {
    keywords: [ "blue-square" ],
    char: "\u23cf\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_forward: {
    keywords: [ "blue-square", "right", "direction", "play" ],
    char: "\u25b6\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  pause_button: {
    keywords: [ "pause", "blue-square" ],
    char: "\u23f8",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  next_track_button: {
    keywords: [ "forward", "next", "blue-square" ],
    char: "\u23ed",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  stop_button: {
    keywords: [ "blue-square" ],
    char: "\u23f9",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  record_button: {
    keywords: [ "blue-square" ],
    char: "\u23fa",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  play_or_pause_button: {
    keywords: [ "blue-square", "play", "pause" ],
    char: "\u23ef",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  previous_track_button: {
    keywords: [ "backward" ],
    char: "\u23ee",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  fast_forward: {
    keywords: [ "blue-square", "play", "speed", "continue" ],
    char: "\u23e9",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  rewind: {
    keywords: [ "play", "blue-square" ],
    char: "\u23ea",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  twisted_rightwards_arrows: {
    keywords: [ "blue-square", "shuffle", "music", "random" ],
    char: "\ud83d\udd00",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  repeat: {
    keywords: [ "loop", "record" ],
    char: "\ud83d\udd01",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  repeat_one: {
    keywords: [ "blue-square", "loop" ],
    char: "\ud83d\udd02",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_backward: {
    keywords: [ "blue-square", "left", "direction" ],
    char: "\u25c0\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_up_small: {
    keywords: [ "blue-square", "triangle", "direction", "point", "forward", "top" ],
    char: "\ud83d\udd3c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_down_small: {
    keywords: [ "blue-square", "direction", "bottom" ],
    char: "\ud83d\udd3d",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_double_up: {
    keywords: [ "blue-square", "direction", "top" ],
    char: "\u23eb",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_double_down: {
    keywords: [ "blue-square", "direction", "bottom" ],
    char: "\u23ec",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_right: {
    keywords: [ "blue-square", "next" ],
    char: "\u27a1\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_left: {
    keywords: [ "blue-square", "previous", "back" ],
    char: "\u2b05\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_up: {
    keywords: [ "blue-square", "continue", "top", "direction" ],
    char: "\u2b06\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_down: {
    keywords: [ "blue-square", "direction", "bottom" ],
    char: "\u2b07\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_upper_right: {
    keywords: [ "blue-square", "point", "direction", "diagonal", "northeast" ],
    char: "\u2197\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_lower_right: {
    keywords: [ "blue-square", "direction", "diagonal", "southeast" ],
    char: "\u2198\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_lower_left: {
    keywords: [ "blue-square", "direction", "diagonal", "southwest" ],
    char: "\u2199\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_upper_left: {
    keywords: [ "blue-square", "point", "direction", "diagonal", "northwest" ],
    char: "\u2196\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_up_down: {
    keywords: [ "blue-square", "direction", "way", "vertical" ],
    char: "\u2195\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  left_right_arrow: {
    keywords: [ "shape", "direction", "horizontal", "sideways" ],
    char: "\u2194\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrows_counterclockwise: {
    keywords: [ "blue-square", "sync", "cycle" ],
    char: "\ud83d\udd04",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_right_hook: {
    keywords: [ "blue-square", "return", "rotate", "direction" ],
    char: "\u21aa\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  leftwards_arrow_with_hook: {
    keywords: [ "back", "return", "blue-square", "undo", "enter" ],
    char: "\u21a9\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_heading_up: {
    keywords: [ "blue-square", "direction", "top" ],
    char: "\u2934\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrow_heading_down: {
    keywords: [ "blue-square", "direction", "bottom" ],
    char: "\u2935\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  hash: {
    keywords: [ "symbol", "blue-square", "twitter" ],
    char: "#\ufe0f\u20e3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  information_source: {
    keywords: [ "blue-square", "alphabet", "letter" ],
    char: "\u2139\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  abc: {
    keywords: [ "blue-square", "alphabet" ],
    char: "\ud83d\udd24",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  abcd: {
    keywords: [ "blue-square", "alphabet" ],
    char: "\ud83d\udd21",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  capital_abcd: {
    keywords: [ "alphabet", "words", "blue-square" ],
    char: "\ud83d\udd20",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  symbols: {
    keywords: [ "blue-square", "music", "note", "ampersand", "percent", "glyphs", "characters" ],
    char: "\ud83d\udd23",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  musical_note: {
    keywords: [ "score", "tone", "sound" ],
    char: "\ud83c\udfb5",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  notes: {
    keywords: [ "music", "score" ],
    char: "\ud83c\udfb6",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  wavy_dash: {
    keywords: [ "draw", "line", "moustache", "mustache", "squiggle", "scribble" ],
    char: "\u3030\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  curly_loop: {
    keywords: [ "scribble", "draw", "shape", "squiggle" ],
    char: "\u27b0",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heavy_check_mark: {
    keywords: [ "ok", "nike", "answer", "yes", "tick" ],
    char: "\u2714\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  arrows_clockwise: {
    keywords: [ "sync", "cycle", "round", "repeat" ],
    char: "\ud83d\udd03",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heavy_plus_sign: {
    keywords: [ "math", "calculation", "addition", "more", "increase" ],
    char: "\u2795",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heavy_minus_sign: {
    keywords: [ "math", "calculation", "subtract", "less" ],
    char: "\u2796",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heavy_division_sign: {
    keywords: [ "divide", "math", "calculation" ],
    char: "\u2797",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heavy_multiplication_x: {
    keywords: [ "math", "calculation" ],
    char: "\u2716\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  infinity: {
    keywords: [ "forever" ],
    char: "\u267e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  heavy_dollar_sign: {
    keywords: [ "money", "sales", "payment", "currency", "buck" ],
    char: "\ud83d\udcb2",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  currency_exchange: {
    keywords: [ "money", "sales", "dollar", "travel" ],
    char: "\ud83d\udcb1",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  copyright: {
    keywords: [ "ip", "license", "circle", "law", "legal" ],
    char: "\xa9\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  registered: {
    keywords: [ "alphabet", "circle" ],
    char: "\xae\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  tm: {
    keywords: [ "trademark", "brand", "law", "legal" ],
    char: "\u2122\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  end: {
    keywords: [ "words", "arrow" ],
    char: "\ud83d\udd1a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  back: {
    keywords: [ "arrow", "words", "return" ],
    char: "\ud83d\udd19",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  on: {
    keywords: [ "arrow", "words" ],
    char: "\ud83d\udd1b",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  top: {
    keywords: [ "words", "blue-square" ],
    char: "\ud83d\udd1d",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  soon: {
    keywords: [ "arrow", "words" ],
    char: "\ud83d\udd1c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  ballot_box_with_check: {
    keywords: [ "ok", "agree", "confirm", "black-square", "vote", "election", "yes", "tick" ],
    char: "\u2611\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  radio_button: {
    keywords: [ "input", "old", "music", "circle" ],
    char: "\ud83d\udd18",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_circle: {
    keywords: [ "shape", "round" ],
    char: "\u26aa",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_circle: {
    keywords: [ "shape", "button", "round" ],
    char: "\u26ab",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  red_circle: {
    keywords: [ "shape", "error", "danger" ],
    char: "\ud83d\udd34",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  large_blue_circle: {
    keywords: [ "shape", "icon", "button" ],
    char: "\ud83d\udd35",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  small_orange_diamond: {
    keywords: [ "shape", "jewel", "gem" ],
    char: "\ud83d\udd38",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  small_blue_diamond: {
    keywords: [ "shape", "jewel", "gem" ],
    char: "\ud83d\udd39",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  large_orange_diamond: {
    keywords: [ "shape", "jewel", "gem" ],
    char: "\ud83d\udd36",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  large_blue_diamond: {
    keywords: [ "shape", "jewel", "gem" ],
    char: "\ud83d\udd37",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  small_red_triangle: {
    keywords: [ "shape", "direction", "up", "top" ],
    char: "\ud83d\udd3a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_small_square: {
    keywords: [ "shape", "icon" ],
    char: "\u25aa\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_small_square: {
    keywords: [ "shape", "icon" ],
    char: "\u25ab\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_large_square: {
    keywords: [ "shape", "icon", "button" ],
    char: "\u2b1b",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_large_square: {
    keywords: [ "shape", "icon", "stone", "button" ],
    char: "\u2b1c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  small_red_triangle_down: {
    keywords: [ "shape", "direction", "bottom" ],
    char: "\ud83d\udd3b",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_medium_square: {
    keywords: [ "shape", "button", "icon" ],
    char: "\u25fc\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_medium_square: {
    keywords: [ "shape", "stone", "icon" ],
    char: "\u25fb\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_medium_small_square: {
    keywords: [ "icon", "shape", "button" ],
    char: "\u25fe",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_medium_small_square: {
    keywords: [ "shape", "stone", "icon", "button" ],
    char: "\u25fd",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_square_button: {
    keywords: [ "shape", "input", "frame" ],
    char: "\ud83d\udd32",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  white_square_button: {
    keywords: [ "shape", "input" ],
    char: "\ud83d\udd33",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  speaker: {
    keywords: [ "sound", "volume", "silence", "broadcast" ],
    char: "\ud83d\udd08",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  sound: {
    keywords: [ "volume", "speaker", "broadcast" ],
    char: "\ud83d\udd09",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  loud_sound: {
    keywords: [ "volume", "noise", "noisy", "speaker", "broadcast" ],
    char: "\ud83d\udd0a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  mute: {
    keywords: [ "sound", "volume", "silence", "quiet" ],
    char: "\ud83d\udd07",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  mega: {
    keywords: [ "sound", "speaker", "volume" ],
    char: "\ud83d\udce3",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  loudspeaker: {
    keywords: [ "volume", "sound" ],
    char: "\ud83d\udce2",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  bell: {
    keywords: [ "sound", "notification", "christmas", "xmas", "chime" ],
    char: "\ud83d\udd14",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  no_bell: {
    keywords: [ "sound", "volume", "mute", "quiet", "silent" ],
    char: "\ud83d\udd15",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  black_joker: {
    keywords: [ "poker", "cards", "game", "play", "magic" ],
    char: "\ud83c\udccf",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  mahjong: {
    keywords: [ "game", "play", "chinese", "kanji" ],
    char: "\ud83c\udc04",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  spades: {
    keywords: [ "poker", "cards", "suits", "magic" ],
    char: "\u2660\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clubs: {
    keywords: [ "poker", "cards", "magic", "suits" ],
    char: "\u2663\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  hearts: {
    keywords: [ "poker", "cards", "magic", "suits" ],
    char: "\u2665\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  diamonds: {
    keywords: [ "poker", "cards", "magic", "suits" ],
    char: "\u2666\ufe0f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  flower_playing_cards: {
    keywords: [ "game", "sunset", "red" ],
    char: "\ud83c\udfb4",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  thought_balloon: {
    keywords: [ "bubble", "cloud", "speech", "thinking", "dream" ],
    char: "\ud83d\udcad",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  right_anger_bubble: {
    keywords: [ "caption", "speech", "thinking", "mad" ],
    char: "\ud83d\uddef",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  speech_balloon: {
    keywords: [ "bubble", "words", "message", "talk", "chatting" ],
    char: "\ud83d\udcac",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  left_speech_bubble: {
    keywords: [ "words", "message", "talk", "chatting" ],
    char: "\ud83d\udde8",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock1: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd50",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock2: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd51",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock3: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd52",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock4: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd53",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock5: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd54",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock6: {
    keywords: [ "time", "late", "early", "schedule", "dawn", "dusk" ],
    char: "\ud83d\udd55",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock7: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd56",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock8: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd57",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock9: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd58",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock10: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd59",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock11: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd5a",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock12: {
    keywords: [ "time", "noon", "midnight", "midday", "late", "early", "schedule" ],
    char: "\ud83d\udd5b",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock130: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd5c",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock230: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd5d",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock330: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd5e",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock430: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd5f",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock530: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd60",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock630: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd61",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock730: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd62",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock830: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd63",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock930: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd64",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock1030: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd65",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock1130: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd66",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  clock1230: {
    keywords: [ "time", "late", "early", "schedule" ],
    char: "\ud83d\udd67",
    fitzpatrick_scale: false,
    category: "symbols"
  },
  afghanistan: {
    keywords: [ "af", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  aland_islands: {
    keywords: [ "\xc5land", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddfd",
    fitzpatrick_scale: false,
    category: "flags"
  },
  albania: {
    keywords: [ "al", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  algeria: {
    keywords: [ "dz", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde9\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  american_samoa: {
    keywords: [ "american", "ws", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  andorra: {
    keywords: [ "ad", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  angola: {
    keywords: [ "ao", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  anguilla: {
    keywords: [ "ai", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  antarctica: {
    keywords: [ "aq", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddf6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  antigua_barbuda: {
    keywords: [ "antigua", "barbuda", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  argentina: {
    keywords: [ "ar", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  armenia: {
    keywords: [ "am", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  aruba: {
    keywords: [ "aw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  australia: {
    keywords: [ "au", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  austria: {
    keywords: [ "at", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  azerbaijan: {
    keywords: [ "az", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bahamas: {
    keywords: [ "bs", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bahrain: {
    keywords: [ "bh", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bangladesh: {
    keywords: [ "bd", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  barbados: {
    keywords: [ "bb", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\udde7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  belarus: {
    keywords: [ "by", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  belgium: {
    keywords: [ "be", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  belize: {
    keywords: [ "bz", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  benin: {
    keywords: [ "bj", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddef",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bermuda: {
    keywords: [ "bm", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bhutan: {
    keywords: [ "bt", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bolivia: {
    keywords: [ "bo", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  caribbean_netherlands: {
    keywords: [ "bonaire", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bosnia_herzegovina: {
    keywords: [ "bosnia", "herzegovina", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  botswana: {
    keywords: [ "bw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  brazil: {
    keywords: [ "br", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  british_indian_ocean_territory: {
    keywords: [ "british", "indian", "ocean", "territory", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  british_virgin_islands: {
    keywords: [ "british", "virgin", "islands", "bvi", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfb\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  brunei: {
    keywords: [ "bn", "darussalam", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  bulgaria: {
    keywords: [ "bg", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  burkina_faso: {
    keywords: [ "burkina", "faso", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  burundi: {
    keywords: [ "bi", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cape_verde: {
    keywords: [ "cabo", "verde", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddfb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cambodia: {
    keywords: [ "kh", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cameroon: {
    keywords: [ "cm", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  canada: {
    keywords: [ "ca", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  canary_islands: {
    keywords: [ "canary", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cayman_islands: {
    keywords: [ "cayman", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  central_african_republic: {
    keywords: [ "central", "african", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  chad: {
    keywords: [ "td", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  chile: {
    keywords: [ "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cn: {
    keywords: [ "china", "chinese", "prc", "flag", "country", "nation", "banner" ],
    char: "\ud83c\udde8\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  christmas_island: {
    keywords: [ "christmas", "island", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddfd",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cocos_islands: {
    keywords: [ "cocos", "keeling", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  colombia: {
    keywords: [ "co", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  comoros: {
    keywords: [ "km", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  congo_brazzaville: {
    keywords: [ "congo", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  congo_kinshasa: {
    keywords: [ "congo", "democratic", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cook_islands: {
    keywords: [ "cook", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  costa_rica: {
    keywords: [ "costa", "rica", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  croatia: {
    keywords: [ "hr", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udded\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cuba: {
    keywords: [ "cu", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  curacao: {
    keywords: [ "cura\xe7ao", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cyprus: {
    keywords: [ "cy", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  czech_republic: {
    keywords: [ "cz", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  denmark: {
    keywords: [ "dk", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde9\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  djibouti: {
    keywords: [ "dj", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde9\ud83c\uddef",
    fitzpatrick_scale: false,
    category: "flags"
  },
  dominica: {
    keywords: [ "dm", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde9\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  dominican_republic: {
    keywords: [ "dominican", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde9\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  ecuador: {
    keywords: [ "ec", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddea\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  egypt: {
    keywords: [ "eg", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddea\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  el_salvador: {
    keywords: [ "el", "salvador", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddfb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  equatorial_guinea: {
    keywords: [ "equatorial", "gn", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  eritrea: {
    keywords: [ "er", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddea\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  estonia: {
    keywords: [ "ee", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddea\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  ethiopia: {
    keywords: [ "et", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddea\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  eu: {
    keywords: [ "european", "union", "flag", "banner" ],
    char: "\ud83c\uddea\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  falkland_islands: {
    keywords: [ "falkland", "islands", "malvinas", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddeb\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  faroe_islands: {
    keywords: [ "faroe", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddeb\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  fiji: {
    keywords: [ "fj", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddeb\ud83c\uddef",
    fitzpatrick_scale: false,
    category: "flags"
  },
  finland: {
    keywords: [ "fi", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddeb\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  fr: {
    keywords: [ "banner", "flag", "nation", "france", "french", "country" ],
    char: "\ud83c\uddeb\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  french_guiana: {
    keywords: [ "french", "guiana", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  french_polynesia: {
    keywords: [ "french", "polynesia", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  french_southern_territories: {
    keywords: [ "french", "southern", "territories", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  gabon: {
    keywords: [ "ga", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  gambia: {
    keywords: [ "gm", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  georgia: {
    keywords: [ "ge", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  de: {
    keywords: [ "german", "nation", "flag", "country", "banner" ],
    char: "\ud83c\udde9\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  ghana: {
    keywords: [ "gh", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  gibraltar: {
    keywords: [ "gi", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  greece: {
    keywords: [ "gr", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  greenland: {
    keywords: [ "gl", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  grenada: {
    keywords: [ "gd", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  guadeloupe: {
    keywords: [ "gp", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf5",
    fitzpatrick_scale: false,
    category: "flags"
  },
  guam: {
    keywords: [ "gu", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  guatemala: {
    keywords: [ "gt", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  guernsey: {
    keywords: [ "gg", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  guinea: {
    keywords: [ "gn", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  guinea_bissau: {
    keywords: [ "gw", "bissau", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  guyana: {
    keywords: [ "gy", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  haiti: {
    keywords: [ "ht", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udded\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  honduras: {
    keywords: [ "hn", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udded\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  hong_kong: {
    keywords: [ "hong", "kong", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udded\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  hungary: {
    keywords: [ "hu", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udded\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  iceland: {
    keywords: [ "is", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  india: {
    keywords: [ "in", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  indonesia: {
    keywords: [ "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  iran: {
    keywords: [ "iran,", "islamic", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  iraq: {
    keywords: [ "iq", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  ireland: {
    keywords: [ "ie", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  isle_of_man: {
    keywords: [ "isle", "man", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  israel: {
    keywords: [ "il", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  it: {
    keywords: [ "italy", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddee\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  cote_divoire: {
    keywords: [ "ivory", "coast", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  jamaica: {
    keywords: [ "jm", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddef\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  jp: {
    keywords: [ "japanese", "nation", "flag", "country", "banner" ],
    char: "\ud83c\uddef\ud83c\uddf5",
    fitzpatrick_scale: false,
    category: "flags"
  },
  jersey: {
    keywords: [ "je", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddef\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  jordan: {
    keywords: [ "jo", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddef\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  kazakhstan: {
    keywords: [ "kz", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  kenya: {
    keywords: [ "ke", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  kiribati: {
    keywords: [ "ki", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  kosovo: {
    keywords: [ "xk", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfd\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  kuwait: {
    keywords: [ "kw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  kyrgyzstan: {
    keywords: [ "kg", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  laos: {
    keywords: [ "lao", "democratic", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  latvia: {
    keywords: [ "lv", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddfb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  lebanon: {
    keywords: [ "lb", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\udde7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  lesotho: {
    keywords: [ "ls", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  liberia: {
    keywords: [ "lr", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  libya: {
    keywords: [ "ly", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  liechtenstein: {
    keywords: [ "li", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  lithuania: {
    keywords: [ "lt", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  luxembourg: {
    keywords: [ "lu", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  macau: {
    keywords: [ "macao", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  macedonia: {
    keywords: [ "macedonia,", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  madagascar: {
    keywords: [ "mg", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  malawi: {
    keywords: [ "mw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  malaysia: {
    keywords: [ "my", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  maldives: {
    keywords: [ "mv", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddfb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  mali: {
    keywords: [ "ml", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  malta: {
    keywords: [ "mt", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  marshall_islands: {
    keywords: [ "marshall", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  martinique: {
    keywords: [ "mq", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  mauritania: {
    keywords: [ "mr", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  mauritius: {
    keywords: [ "mu", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  mayotte: {
    keywords: [ "yt", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfe\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  mexico: {
    keywords: [ "mx", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddfd",
    fitzpatrick_scale: false,
    category: "flags"
  },
  micronesia: {
    keywords: [ "micronesia,", "federated", "states", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddeb\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  moldova: {
    keywords: [ "moldova,", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  monaco: {
    keywords: [ "mc", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  mongolia: {
    keywords: [ "mn", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  montenegro: {
    keywords: [ "me", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  montserrat: {
    keywords: [ "ms", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  morocco: {
    keywords: [ "ma", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  mozambique: {
    keywords: [ "mz", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  myanmar: {
    keywords: [ "mm", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  namibia: {
    keywords: [ "na", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  nauru: {
    keywords: [ "nr", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  nepal: {
    keywords: [ "np", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddf5",
    fitzpatrick_scale: false,
    category: "flags"
  },
  netherlands: {
    keywords: [ "nl", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  new_caledonia: {
    keywords: [ "new", "caledonia", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  new_zealand: {
    keywords: [ "new", "zealand", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  nicaragua: {
    keywords: [ "ni", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  niger: {
    keywords: [ "ne", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  nigeria: {
    keywords: [ "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  niue: {
    keywords: [ "nu", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  norfolk_island: {
    keywords: [ "norfolk", "island", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  northern_mariana_islands: {
    keywords: [ "northern", "mariana", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf2\ud83c\uddf5",
    fitzpatrick_scale: false,
    category: "flags"
  },
  north_korea: {
    keywords: [ "north", "korea", "nation", "flag", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddf5",
    fitzpatrick_scale: false,
    category: "flags"
  },
  norway: {
    keywords: [ "no", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf3\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  oman: {
    keywords: [ "om_symbol", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf4\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  pakistan: {
    keywords: [ "pk", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  palau: {
    keywords: [ "pw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  palestinian_territories: {
    keywords: [ "palestine", "palestinian", "territories", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  panama: {
    keywords: [ "pa", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  papua_new_guinea: {
    keywords: [ "papua", "new", "guinea", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  paraguay: {
    keywords: [ "py", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  peru: {
    keywords: [ "pe", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  philippines: {
    keywords: [ "ph", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  pitcairn_islands: {
    keywords: [ "pitcairn", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  poland: {
    keywords: [ "pl", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  portugal: {
    keywords: [ "pt", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  puerto_rico: {
    keywords: [ "puerto", "rico", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  qatar: {
    keywords: [ "qa", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf6\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  reunion: {
    keywords: [ "r\xe9union", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf7\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  romania: {
    keywords: [ "ro", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf7\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  ru: {
    keywords: [ "russian", "federation", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf7\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  rwanda: {
    keywords: [ "rw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf7\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  st_barthelemy: {
    keywords: [ "saint", "barth\xe9lemy", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde7\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  st_helena: {
    keywords: [ "saint", "helena", "ascension", "tristan", "cunha", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  st_kitts_nevis: {
    keywords: [ "saint", "kitts", "nevis", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  st_lucia: {
    keywords: [ "saint", "lucia", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  st_pierre_miquelon: {
    keywords: [ "saint", "pierre", "miquelon", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf5\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  st_vincent_grenadines: {
    keywords: [ "saint", "vincent", "grenadines", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfb\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  samoa: {
    keywords: [ "ws", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfc\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  san_marino: {
    keywords: [ "san", "marino", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  sao_tome_principe: {
    keywords: [ "sao", "tome", "principe", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  saudi_arabia: {
    keywords: [ "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  senegal: {
    keywords: [ "sn", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  serbia: {
    keywords: [ "rs", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf7\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  seychelles: {
    keywords: [ "sc", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  sierra_leone: {
    keywords: [ "sierra", "leone", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  singapore: {
    keywords: [ "sg", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  sint_maarten: {
    keywords: [ "sint", "maarten", "dutch", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddfd",
    fitzpatrick_scale: false,
    category: "flags"
  },
  slovakia: {
    keywords: [ "sk", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  slovenia: {
    keywords: [ "si", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  solomon_islands: {
    keywords: [ "solomon", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\udde7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  somalia: {
    keywords: [ "so", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  south_africa: {
    keywords: [ "south", "africa", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddff\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  south_georgia_south_sandwich_islands: {
    keywords: [ "south", "georgia", "sandwich", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddec\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  kr: {
    keywords: [ "south", "korea", "nation", "flag", "country", "banner" ],
    char: "\ud83c\uddf0\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  south_sudan: {
    keywords: [ "south", "sd", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  es: {
    keywords: [ "spain", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddea\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  sri_lanka: {
    keywords: [ "sri", "lanka", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf1\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  sudan: {
    keywords: [ "sd", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\udde9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  suriname: {
    keywords: [ "sr", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  swaziland: {
    keywords: [ "sz", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  sweden: {
    keywords: [ "se", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  switzerland: {
    keywords: [ "ch", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde8\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  syria: {
    keywords: [ "syrian", "arab", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf8\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  taiwan: {
    keywords: [ "tw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  tajikistan: {
    keywords: [ "tj", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddef",
    fitzpatrick_scale: false,
    category: "flags"
  },
  tanzania: {
    keywords: [ "tanzania,", "united", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  thailand: {
    keywords: [ "th", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  timor_leste: {
    keywords: [ "timor", "leste", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddf1",
    fitzpatrick_scale: false,
    category: "flags"
  },
  togo: {
    keywords: [ "tg", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  tokelau: {
    keywords: [ "tk", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddf0",
    fitzpatrick_scale: false,
    category: "flags"
  },
  tonga: {
    keywords: [ "to", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddf4",
    fitzpatrick_scale: false,
    category: "flags"
  },
  trinidad_tobago: {
    keywords: [ "trinidad", "tobago", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddf9",
    fitzpatrick_scale: false,
    category: "flags"
  },
  tunisia: {
    keywords: [ "tn", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  tr: {
    keywords: [ "turkey", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddf7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  turkmenistan: {
    keywords: [ "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  turks_caicos_islands: {
    keywords: [ "turks", "caicos", "islands", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\udde8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  tuvalu: {
    keywords: [ "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddf9\ud83c\uddfb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  uganda: {
    keywords: [ "ug", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfa\ud83c\uddec",
    fitzpatrick_scale: false,
    category: "flags"
  },
  ukraine: {
    keywords: [ "ua", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfa\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  united_arab_emirates: {
    keywords: [ "united", "arab", "emirates", "flag", "nation", "country", "banner" ],
    char: "\ud83c\udde6\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  uk: {
    keywords: [ "united", "kingdom", "great", "britain", "northern", "ireland", "flag", "nation", "country", "banner", "british", "UK", "english", "england", "union jack" ],
    char: "\ud83c\uddec\ud83c\udde7",
    fitzpatrick_scale: false,
    category: "flags"
  },
  england: {
    keywords: [ "flag", "english" ],
    char: "\ud83c\udff4\udb40\udc67\udb40\udc62\udb40\udc65\udb40\udc6e\udb40\udc67\udb40\udc7f",
    fitzpatrick_scale: false,
    category: "flags"
  },
  scotland: {
    keywords: [ "flag", "scottish" ],
    char: "\ud83c\udff4\udb40\udc67\udb40\udc62\udb40\udc73\udb40\udc63\udb40\udc74\udb40\udc7f",
    fitzpatrick_scale: false,
    category: "flags"
  },
  wales: {
    keywords: [ "flag", "welsh" ],
    char: "\ud83c\udff4\udb40\udc67\udb40\udc62\udb40\udc77\udb40\udc6c\udb40\udc73\udb40\udc7f",
    fitzpatrick_scale: false,
    category: "flags"
  },
  us: {
    keywords: [ "united", "states", "america", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfa\ud83c\uddf8",
    fitzpatrick_scale: false,
    category: "flags"
  },
  us_virgin_islands: {
    keywords: [ "virgin", "islands", "us", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfb\ud83c\uddee",
    fitzpatrick_scale: false,
    category: "flags"
  },
  uruguay: {
    keywords: [ "uy", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfa\ud83c\uddfe",
    fitzpatrick_scale: false,
    category: "flags"
  },
  uzbekistan: {
    keywords: [ "uz", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfa\ud83c\uddff",
    fitzpatrick_scale: false,
    category: "flags"
  },
  vanuatu: {
    keywords: [ "vu", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfb\ud83c\uddfa",
    fitzpatrick_scale: false,
    category: "flags"
  },
  vatican_city: {
    keywords: [ "vatican", "city", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfb\ud83c\udde6",
    fitzpatrick_scale: false,
    category: "flags"
  },
  venezuela: {
    keywords: [ "ve", "bolivarian", "republic", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfb\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  vietnam: {
    keywords: [ "viet", "nam", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfb\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  wallis_futuna: {
    keywords: [ "wallis", "futuna", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfc\ud83c\uddeb",
    fitzpatrick_scale: false,
    category: "flags"
  },
  western_sahara: {
    keywords: [ "western", "sahara", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddea\ud83c\udded",
    fitzpatrick_scale: false,
    category: "flags"
  },
  yemen: {
    keywords: [ "ye", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddfe\ud83c\uddea",
    fitzpatrick_scale: false,
    category: "flags"
  },
  zambia: {
    keywords: [ "zm", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddff\ud83c\uddf2",
    fitzpatrick_scale: false,
    category: "flags"
  },
  zimbabwe: {
    keywords: [ "zw", "flag", "nation", "country", "banner" ],
    char: "\ud83c\uddff\ud83c\uddfc",
    fitzpatrick_scale: false,
    category: "flags"
  },
  united_nations: {
    keywords: [ "un", "flag", "banner" ],
    char: "\ud83c\uddfa\ud83c\uddf3",
    fitzpatrick_scale: false,
    category: "flags"
  },
  pirate_flag: {
    keywords: [ "skull", "crossbones", "flag", "banner" ],
    char: "\ud83c\udff4\u200d\u2620\ufe0f",
    fitzpatrick_scale: false,
    category: "flags"
  }
});

/***/ }),

/***/ "./node_modules/tinymce/plugins/emoticons/plugin.js":
/*!**********************************************************!*\
  !*** ./node_modules/tinymce/plugins/emoticons/plugin.js ***!
  \**********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$3 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var eq = function (t) {
      return function (a) {
        return t === a;
      };
    };
    var isNull = eq(null);

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var exists = function (xs, pred) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          return true;
        }
      }
      return false;
    };
    var map$1 = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each$1 = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var last = function (fn, rate) {
      var timer = null;
      var cancel = function () {
        if (!isNull(timer)) {
          clearTimeout(timer);
          timer = null;
        }
      };
      var throttle = function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          args[_i] = arguments[_i];
        }
        cancel();
        timer = setTimeout(function () {
          timer = null;
          fn.apply(null, args);
        }, rate);
      };
      return {
        cancel: cancel,
        throttle: throttle
      };
    };

    var insertEmoticon = function (editor, ch) {
      editor.insertContent(ch);
    };

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };

    var keys = Object.keys;
    var hasOwnProperty = Object.hasOwnProperty;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };
    var map = function (obj, f) {
      return tupleMap(obj, function (x, i) {
        return {
          k: i,
          v: f(x, i)
        };
      });
    };
    var tupleMap = function (obj, f) {
      var r = {};
      each(obj, function (x, i) {
        var tuple = f(x, i);
        r[tuple.k] = tuple.v;
      });
      return r;
    };
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };

    var shallow = function (old, nu) {
      return nu;
    };
    var baseMerge = function (merger) {
      return function () {
        var objects = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          objects[_i] = arguments[_i];
        }
        if (objects.length === 0) {
          throw new Error('Can\'t merge zero objects');
        }
        var ret = {};
        for (var j = 0; j < objects.length; j++) {
          var curObject = objects[j];
          for (var key in curObject) {
            if (has(curObject, key)) {
              ret[key] = merger(ret[key], curObject[key]);
            }
          }
        }
        return ret;
      };
    };
    var merge = baseMerge(shallow);

    var singleton = function (doRevoke) {
      var subject = Cell(Optional.none());
      var revoke = function () {
        return subject.get().each(doRevoke);
      };
      var clear = function () {
        revoke();
        subject.set(Optional.none());
      };
      var isSet = function () {
        return subject.get().isSome();
      };
      var get = function () {
        return subject.get();
      };
      var set = function (s) {
        revoke();
        subject.set(Optional.some(s));
      };
      return {
        clear: clear,
        isSet: isSet,
        get: get,
        set: set
      };
    };
    var value = function () {
      var subject = singleton(noop);
      var on = function (f) {
        return subject.get().each(f);
      };
      return __assign(__assign({}, subject), { on: on });
    };

    var checkRange = function (str, substr, start) {
      return substr === '' || str.length >= substr.length && str.substr(start, start + substr.length) === substr;
    };
    var contains = function (str, substr) {
      return str.indexOf(substr) !== -1;
    };
    var startsWith = function (str, prefix) {
      return checkRange(str, prefix, 0);
    };

    var global$2 = tinymce.util.Tools.resolve('tinymce.Resource');

    var global$1 = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var global = tinymce.util.Tools.resolve('tinymce.util.Promise');

    var DEFAULT_ID = 'tinymce.plugins.emoticons';
    var getEmoticonDatabase = function (editor) {
      return editor.getParam('emoticons_database', 'emojis', 'string');
    };
    var getEmoticonDatabaseUrl = function (editor, pluginUrl) {
      var database = getEmoticonDatabase(editor);
      return editor.getParam('emoticons_database_url', pluginUrl + '/js/' + database + editor.suffix + '.js', 'string');
    };
    var getEmoticonDatabaseId = function (editor) {
      return editor.getParam('emoticons_database_id', DEFAULT_ID, 'string');
    };
    var getAppendedEmoticons = function (editor) {
      return editor.getParam('emoticons_append', {}, 'object');
    };
    var getEmotionsImageUrl = function (editor) {
      return editor.getParam('emoticons_images_url', 'https://twemoji.maxcdn.com/v/13.0.1/72x72/', 'string');
    };

    var ALL_CATEGORY = 'All';
    var categoryNameMap = {
      symbols: 'Symbols',
      people: 'People',
      animals_and_nature: 'Animals and Nature',
      food_and_drink: 'Food and Drink',
      activity: 'Activity',
      travel_and_places: 'Travel and Places',
      objects: 'Objects',
      flags: 'Flags',
      user: 'User Defined'
    };
    var translateCategory = function (categories, name) {
      return has(categories, name) ? categories[name] : name;
    };
    var getUserDefinedEmoticons = function (editor) {
      var userDefinedEmoticons = getAppendedEmoticons(editor);
      return map(userDefinedEmoticons, function (value) {
        return __assign({
          keywords: [],
          category: 'user'
        }, value);
      });
    };
    var initDatabase = function (editor, databaseUrl, databaseId) {
      var categories = value();
      var all = value();
      var emojiImagesUrl = getEmotionsImageUrl(editor);
      var getEmoji = function (lib) {
        if (startsWith(lib.char, '<img')) {
          return lib.char.replace(/src="([^"]+)"/, function (match, url) {
            return 'src="' + emojiImagesUrl + url + '"';
          });
        } else {
          return lib.char;
        }
      };
      var processEmojis = function (emojis) {
        var cats = {};
        var everything = [];
        each(emojis, function (lib, title) {
          var entry = {
            title: title,
            keywords: lib.keywords,
            char: getEmoji(lib),
            category: translateCategory(categoryNameMap, lib.category)
          };
          var current = cats[entry.category] !== undefined ? cats[entry.category] : [];
          cats[entry.category] = current.concat([entry]);
          everything.push(entry);
        });
        categories.set(cats);
        all.set(everything);
      };
      editor.on('init', function () {
        global$2.load(databaseId, databaseUrl).then(function (emojis) {
          var userEmojis = getUserDefinedEmoticons(editor);
          processEmojis(merge(emojis, userEmojis));
        }, function (err) {
          console.log('Failed to load emoticons: ' + err);
          categories.set({});
          all.set([]);
        });
      });
      var listCategory = function (category) {
        if (category === ALL_CATEGORY) {
          return listAll();
        }
        return categories.get().bind(function (cats) {
          return Optional.from(cats[category]);
        }).getOr([]);
      };
      var listAll = function () {
        return all.get().getOr([]);
      };
      var listCategories = function () {
        return [ALL_CATEGORY].concat(keys(categories.get().getOr({})));
      };
      var waitForLoad = function () {
        if (hasLoaded()) {
          return global.resolve(true);
        } else {
          return new global(function (resolve, reject) {
            var numRetries = 15;
            var interval = global$1.setInterval(function () {
              if (hasLoaded()) {
                global$1.clearInterval(interval);
                resolve(true);
              } else {
                numRetries--;
                if (numRetries < 0) {
                  console.log('Could not load emojis from url: ' + databaseUrl);
                  global$1.clearInterval(interval);
                  reject(false);
                }
              }
            }, 100);
          });
        }
      };
      var hasLoaded = function () {
        return categories.isSet() && all.isSet();
      };
      return {
        listCategories: listCategories,
        hasLoaded: hasLoaded,
        waitForLoad: waitForLoad,
        listAll: listAll,
        listCategory: listCategory
      };
    };

    var emojiMatches = function (emoji, lowerCasePattern) {
      return contains(emoji.title.toLowerCase(), lowerCasePattern) || exists(emoji.keywords, function (k) {
        return contains(k.toLowerCase(), lowerCasePattern);
      });
    };
    var emojisFrom = function (list, pattern, maxResults) {
      var matches = [];
      var lowerCasePattern = pattern.toLowerCase();
      var reachedLimit = maxResults.fold(function () {
        return never;
      }, function (max) {
        return function (size) {
          return size >= max;
        };
      });
      for (var i = 0; i < list.length; i++) {
        if (pattern.length === 0 || emojiMatches(list[i], lowerCasePattern)) {
          matches.push({
            value: list[i].char,
            text: list[i].title,
            icon: list[i].char
          });
          if (reachedLimit(matches.length)) {
            break;
          }
        }
      }
      return matches;
    };

    var patternName = 'pattern';
    var open = function (editor, database) {
      var initialState = {
        pattern: '',
        results: emojisFrom(database.listAll(), '', Optional.some(300))
      };
      var currentTab = Cell(ALL_CATEGORY);
      var scan = function (dialogApi) {
        var dialogData = dialogApi.getData();
        var category = currentTab.get();
        var candidates = database.listCategory(category);
        var results = emojisFrom(candidates, dialogData[patternName], category === ALL_CATEGORY ? Optional.some(300) : Optional.none());
        dialogApi.setData({ results: results });
      };
      var updateFilter = last(function (dialogApi) {
        scan(dialogApi);
      }, 200);
      var searchField = {
        label: 'Search',
        type: 'input',
        name: patternName
      };
      var resultsField = {
        type: 'collection',
        name: 'results'
      };
      var getInitialState = function () {
        var body = {
          type: 'tabpanel',
          tabs: map$1(database.listCategories(), function (cat) {
            return {
              title: cat,
              name: cat,
              items: [
                searchField,
                resultsField
              ]
            };
          })
        };
        return {
          title: 'Emoticons',
          size: 'normal',
          body: body,
          initialData: initialState,
          onTabChange: function (dialogApi, details) {
            currentTab.set(details.newTabName);
            updateFilter.throttle(dialogApi);
          },
          onChange: updateFilter.throttle,
          onAction: function (dialogApi, actionData) {
            if (actionData.name === 'results') {
              insertEmoticon(editor, actionData.value);
              dialogApi.close();
            }
          },
          buttons: [{
              type: 'cancel',
              text: 'Close',
              primary: true
            }]
        };
      };
      var dialogApi = editor.windowManager.open(getInitialState());
      dialogApi.focus(patternName);
      if (!database.hasLoaded()) {
        dialogApi.block('Loading emoticons...');
        database.waitForLoad().then(function () {
          dialogApi.redial(getInitialState());
          updateFilter.throttle(dialogApi);
          dialogApi.focus(patternName);
          dialogApi.unblock();
        }).catch(function (_err) {
          dialogApi.redial({
            title: 'Emoticons',
            body: {
              type: 'panel',
              items: [{
                  type: 'alertbanner',
                  level: 'error',
                  icon: 'warning',
                  text: '<p>Could not load emoticons</p>'
                }]
            },
            buttons: [{
                type: 'cancel',
                text: 'Close',
                primary: true
              }],
            initialData: {
              pattern: '',
              results: []
            }
          });
          dialogApi.focus(patternName);
          dialogApi.unblock();
        });
      }
    };

    var register$1 = function (editor, database) {
      editor.addCommand('mceEmoticons', function () {
        return open(editor, database);
      });
    };

    var setup = function (editor) {
      editor.on('PreInit', function () {
        editor.parser.addAttributeFilter('data-emoticon', function (nodes) {
          each$1(nodes, function (node) {
            node.attr('data-mce-resize', 'false');
            node.attr('data-mce-placeholder', '1');
          });
        });
      });
    };

    var init = function (editor, database) {
      editor.ui.registry.addAutocompleter('emoticons', {
        ch: ':',
        columns: 'auto',
        minChars: 2,
        fetch: function (pattern, maxResults) {
          return database.waitForLoad().then(function () {
            var candidates = database.listAll();
            return emojisFrom(candidates, pattern, Optional.some(maxResults));
          });
        },
        onAction: function (autocompleteApi, rng, value) {
          editor.selection.setRng(rng);
          editor.insertContent(value);
          autocompleteApi.hide();
        }
      });
    };

    var register = function (editor) {
      var onAction = function () {
        return editor.execCommand('mceEmoticons');
      };
      editor.ui.registry.addButton('emoticons', {
        tooltip: 'Emoticons',
        icon: 'emoji',
        onAction: onAction
      });
      editor.ui.registry.addMenuItem('emoticons', {
        text: 'Emoticons...',
        icon: 'emoji',
        onAction: onAction
      });
    };

    function Plugin () {
      global$3.add('emoticons', function (editor, pluginUrl) {
        var databaseUrl = getEmoticonDatabaseUrl(editor, pluginUrl);
        var databaseId = getEmoticonDatabaseId(editor);
        var database = initDatabase(editor, databaseUrl, databaseId);
        register$1(editor, database);
        register(editor);
        init(editor, database);
        setup(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/fullscreen/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/tinymce/plugins/fullscreen/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "fullscreen" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/fullscreen')
//   ES2015:
//     import 'tinymce/plugins/fullscreen'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/fullscreen/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/fullscreen/plugin.js":
/*!***********************************************************!*\
  !*** ./node_modules/tinymce/plugins/fullscreen/plugin.js ***!
  \***********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var global$3 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var get$5 = function (fullscreenState) {
      return {
        isFullscreen: function () {
          return fullscreenState.get() !== null;
        }
      };
    };

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType$1 = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var isString = isType$1('string');
    var isArray = isType$1('array');
    var isBoolean = isSimpleType('boolean');
    var isNullable = function (a) {
      return a === null || a === undefined;
    };
    var isNonNullable = function (a) {
      return !isNullable(a);
    };
    var isFunction = isSimpleType('function');
    var isNumber = isSimpleType('number');

    var noop = function () {
    };
    var compose = function (fa, fb) {
      return function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          args[_i] = arguments[_i];
        }
        return fa(fb.apply(null, args));
      };
    };
    var compose1 = function (fbc, fab) {
      return function (a) {
        return fbc(fab(a));
      };
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    function curry(fn) {
      var initialArgs = [];
      for (var _i = 1; _i < arguments.length; _i++) {
        initialArgs[_i - 1] = arguments[_i];
      }
      return function () {
        var restArgs = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          restArgs[_i] = arguments[_i];
        }
        var all = initialArgs.concat(restArgs);
        return fn.apply(null, all);
      };
    }
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };

    var singleton = function (doRevoke) {
      var subject = Cell(Optional.none());
      var revoke = function () {
        return subject.get().each(doRevoke);
      };
      var clear = function () {
        revoke();
        subject.set(Optional.none());
      };
      var isSet = function () {
        return subject.get().isSome();
      };
      var get = function () {
        return subject.get();
      };
      var set = function (s) {
        revoke();
        subject.set(Optional.some(s));
      };
      return {
        clear: clear,
        isSet: isSet,
        get: get,
        set: set
      };
    };
    var unbindable = function () {
      return singleton(function (s) {
        return s.unbind();
      });
    };
    var value = function () {
      var subject = singleton(noop);
      var on = function (f) {
        return subject.get().each(f);
      };
      return __assign(__assign({}, subject), { on: on });
    };

    var nativePush = Array.prototype.push;
    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each$1 = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var filter$1 = function (xs, pred) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          r.push(x);
        }
      }
      return r;
    };
    var findUntil = function (xs, pred, until) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          return Optional.some(x);
        } else if (until(x, i)) {
          break;
        }
      }
      return Optional.none();
    };
    var find$1 = function (xs, pred) {
      return findUntil(xs, pred, never);
    };
    var flatten = function (xs) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; ++i) {
        if (!isArray(xs[i])) {
          throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
        }
        nativePush.apply(r, xs[i]);
      }
      return r;
    };
    var bind$3 = function (xs, f) {
      return flatten(map(xs, f));
    };
    var get$4 = function (xs, i) {
      return i >= 0 && i < xs.length ? Optional.some(xs[i]) : Optional.none();
    };
    var head = function (xs) {
      return get$4(xs, 0);
    };
    var findMap = function (arr, f) {
      for (var i = 0; i < arr.length; i++) {
        var r = f(arr[i], i);
        if (r.isSome()) {
          return r;
        }
      }
      return Optional.none();
    };

    var keys = Object.keys;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };

    var contains = function (str, substr) {
      return str.indexOf(substr) !== -1;
    };

    var isSupported$1 = function (dom) {
      return dom.style !== undefined && isFunction(dom.style.getPropertyValue);
    };

    var fromHtml = function (html, scope) {
      var doc = scope || document;
      var div = doc.createElement('div');
      div.innerHTML = html;
      if (!div.hasChildNodes() || div.childNodes.length > 1) {
        console.error('HTML does not have a single root node', html);
        throw new Error('HTML must have a single root node');
      }
      return fromDom(div.childNodes[0]);
    };
    var fromTag = function (tag, scope) {
      var doc = scope || document;
      var node = doc.createElement(tag);
      return fromDom(node);
    };
    var fromText = function (text, scope) {
      var doc = scope || document;
      var node = doc.createTextNode(text);
      return fromDom(node);
    };
    var fromDom = function (node) {
      if (node === null || node === undefined) {
        throw new Error('Node cannot be null or undefined');
      }
      return { dom: node };
    };
    var fromPoint = function (docElm, x, y) {
      return Optional.from(docElm.dom.elementFromPoint(x, y)).map(fromDom);
    };
    var SugarElement = {
      fromHtml: fromHtml,
      fromTag: fromTag,
      fromText: fromText,
      fromDom: fromDom,
      fromPoint: fromPoint
    };

    typeof window !== 'undefined' ? window : Function('return this;')();

    var DOCUMENT = 9;
    var DOCUMENT_FRAGMENT = 11;
    var ELEMENT = 1;
    var TEXT = 3;

    var type = function (element) {
      return element.dom.nodeType;
    };
    var isType = function (t) {
      return function (element) {
        return type(element) === t;
      };
    };
    var isElement = isType(ELEMENT);
    var isText = isType(TEXT);
    var isDocument = isType(DOCUMENT);
    var isDocumentFragment = isType(DOCUMENT_FRAGMENT);

    var cached = function (f) {
      var called = false;
      var r;
      return function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          args[_i] = arguments[_i];
        }
        if (!called) {
          called = true;
          r = f.apply(null, args);
        }
        return r;
      };
    };

    var DeviceType = function (os, browser, userAgent, mediaMatch) {
      var isiPad = os.isiOS() && /ipad/i.test(userAgent) === true;
      var isiPhone = os.isiOS() && !isiPad;
      var isMobile = os.isiOS() || os.isAndroid();
      var isTouch = isMobile || mediaMatch('(pointer:coarse)');
      var isTablet = isiPad || !isiPhone && isMobile && mediaMatch('(min-device-width:768px)');
      var isPhone = isiPhone || isMobile && !isTablet;
      var iOSwebview = browser.isSafari() && os.isiOS() && /safari/i.test(userAgent) === false;
      var isDesktop = !isPhone && !isTablet && !iOSwebview;
      return {
        isiPad: constant(isiPad),
        isiPhone: constant(isiPhone),
        isTablet: constant(isTablet),
        isPhone: constant(isPhone),
        isTouch: constant(isTouch),
        isAndroid: os.isAndroid,
        isiOS: os.isiOS,
        isWebView: constant(iOSwebview),
        isDesktop: constant(isDesktop)
      };
    };

    var firstMatch = function (regexes, s) {
      for (var i = 0; i < regexes.length; i++) {
        var x = regexes[i];
        if (x.test(s)) {
          return x;
        }
      }
      return undefined;
    };
    var find = function (regexes, agent) {
      var r = firstMatch(regexes, agent);
      if (!r) {
        return {
          major: 0,
          minor: 0
        };
      }
      var group = function (i) {
        return Number(agent.replace(r, '$' + i));
      };
      return nu$2(group(1), group(2));
    };
    var detect$3 = function (versionRegexes, agent) {
      var cleanedAgent = String(agent).toLowerCase();
      if (versionRegexes.length === 0) {
        return unknown$2();
      }
      return find(versionRegexes, cleanedAgent);
    };
    var unknown$2 = function () {
      return nu$2(0, 0);
    };
    var nu$2 = function (major, minor) {
      return {
        major: major,
        minor: minor
      };
    };
    var Version = {
      nu: nu$2,
      detect: detect$3,
      unknown: unknown$2
    };

    var detectBrowser$1 = function (browsers, userAgentData) {
      return findMap(userAgentData.brands, function (uaBrand) {
        var lcBrand = uaBrand.brand.toLowerCase();
        return find$1(browsers, function (browser) {
          var _a;
          return lcBrand === ((_a = browser.brand) === null || _a === void 0 ? void 0 : _a.toLowerCase());
        }).map(function (info) {
          return {
            current: info.name,
            version: Version.nu(parseInt(uaBrand.version, 10), 0)
          };
        });
      });
    };

    var detect$2 = function (candidates, userAgent) {
      var agent = String(userAgent).toLowerCase();
      return find$1(candidates, function (candidate) {
        return candidate.search(agent);
      });
    };
    var detectBrowser = function (browsers, userAgent) {
      return detect$2(browsers, userAgent).map(function (browser) {
        var version = Version.detect(browser.versionRegexes, userAgent);
        return {
          current: browser.name,
          version: version
        };
      });
    };
    var detectOs = function (oses, userAgent) {
      return detect$2(oses, userAgent).map(function (os) {
        var version = Version.detect(os.versionRegexes, userAgent);
        return {
          current: os.name,
          version: version
        };
      });
    };

    var normalVersionRegex = /.*?version\/\ ?([0-9]+)\.([0-9]+).*/;
    var checkContains = function (target) {
      return function (uastring) {
        return contains(uastring, target);
      };
    };
    var browsers = [
      {
        name: 'Edge',
        versionRegexes: [/.*?edge\/ ?([0-9]+)\.([0-9]+)$/],
        search: function (uastring) {
          return contains(uastring, 'edge/') && contains(uastring, 'chrome') && contains(uastring, 'safari') && contains(uastring, 'applewebkit');
        }
      },
      {
        name: 'Chrome',
        brand: 'Chromium',
        versionRegexes: [
          /.*?chrome\/([0-9]+)\.([0-9]+).*/,
          normalVersionRegex
        ],
        search: function (uastring) {
          return contains(uastring, 'chrome') && !contains(uastring, 'chromeframe');
        }
      },
      {
        name: 'IE',
        versionRegexes: [
          /.*?msie\ ?([0-9]+)\.([0-9]+).*/,
          /.*?rv:([0-9]+)\.([0-9]+).*/
        ],
        search: function (uastring) {
          return contains(uastring, 'msie') || contains(uastring, 'trident');
        }
      },
      {
        name: 'Opera',
        versionRegexes: [
          normalVersionRegex,
          /.*?opera\/([0-9]+)\.([0-9]+).*/
        ],
        search: checkContains('opera')
      },
      {
        name: 'Firefox',
        versionRegexes: [/.*?firefox\/\ ?([0-9]+)\.([0-9]+).*/],
        search: checkContains('firefox')
      },
      {
        name: 'Safari',
        versionRegexes: [
          normalVersionRegex,
          /.*?cpu os ([0-9]+)_([0-9]+).*/
        ],
        search: function (uastring) {
          return (contains(uastring, 'safari') || contains(uastring, 'mobile/')) && contains(uastring, 'applewebkit');
        }
      }
    ];
    var oses = [
      {
        name: 'Windows',
        search: checkContains('win'),
        versionRegexes: [/.*?windows\ nt\ ?([0-9]+)\.([0-9]+).*/]
      },
      {
        name: 'iOS',
        search: function (uastring) {
          return contains(uastring, 'iphone') || contains(uastring, 'ipad');
        },
        versionRegexes: [
          /.*?version\/\ ?([0-9]+)\.([0-9]+).*/,
          /.*cpu os ([0-9]+)_([0-9]+).*/,
          /.*cpu iphone os ([0-9]+)_([0-9]+).*/
        ]
      },
      {
        name: 'Android',
        search: checkContains('android'),
        versionRegexes: [/.*?android\ ?([0-9]+)\.([0-9]+).*/]
      },
      {
        name: 'OSX',
        search: checkContains('mac os x'),
        versionRegexes: [/.*?mac\ os\ x\ ?([0-9]+)_([0-9]+).*/]
      },
      {
        name: 'Linux',
        search: checkContains('linux'),
        versionRegexes: []
      },
      {
        name: 'Solaris',
        search: checkContains('sunos'),
        versionRegexes: []
      },
      {
        name: 'FreeBSD',
        search: checkContains('freebsd'),
        versionRegexes: []
      },
      {
        name: 'ChromeOS',
        search: checkContains('cros'),
        versionRegexes: [/.*?chrome\/([0-9]+)\.([0-9]+).*/]
      }
    ];
    var PlatformInfo = {
      browsers: constant(browsers),
      oses: constant(oses)
    };

    var edge = 'Edge';
    var chrome = 'Chrome';
    var ie = 'IE';
    var opera = 'Opera';
    var firefox = 'Firefox';
    var safari = 'Safari';
    var unknown$1 = function () {
      return nu$1({
        current: undefined,
        version: Version.unknown()
      });
    };
    var nu$1 = function (info) {
      var current = info.current;
      var version = info.version;
      var isBrowser = function (name) {
        return function () {
          return current === name;
        };
      };
      return {
        current: current,
        version: version,
        isEdge: isBrowser(edge),
        isChrome: isBrowser(chrome),
        isIE: isBrowser(ie),
        isOpera: isBrowser(opera),
        isFirefox: isBrowser(firefox),
        isSafari: isBrowser(safari)
      };
    };
    var Browser = {
      unknown: unknown$1,
      nu: nu$1,
      edge: constant(edge),
      chrome: constant(chrome),
      ie: constant(ie),
      opera: constant(opera),
      firefox: constant(firefox),
      safari: constant(safari)
    };

    var windows = 'Windows';
    var ios = 'iOS';
    var android = 'Android';
    var linux = 'Linux';
    var osx = 'OSX';
    var solaris = 'Solaris';
    var freebsd = 'FreeBSD';
    var chromeos = 'ChromeOS';
    var unknown = function () {
      return nu({
        current: undefined,
        version: Version.unknown()
      });
    };
    var nu = function (info) {
      var current = info.current;
      var version = info.version;
      var isOS = function (name) {
        return function () {
          return current === name;
        };
      };
      return {
        current: current,
        version: version,
        isWindows: isOS(windows),
        isiOS: isOS(ios),
        isAndroid: isOS(android),
        isOSX: isOS(osx),
        isLinux: isOS(linux),
        isSolaris: isOS(solaris),
        isFreeBSD: isOS(freebsd),
        isChromeOS: isOS(chromeos)
      };
    };
    var OperatingSystem = {
      unknown: unknown,
      nu: nu,
      windows: constant(windows),
      ios: constant(ios),
      android: constant(android),
      linux: constant(linux),
      osx: constant(osx),
      solaris: constant(solaris),
      freebsd: constant(freebsd),
      chromeos: constant(chromeos)
    };

    var detect$1 = function (userAgent, userAgentDataOpt, mediaMatch) {
      var browsers = PlatformInfo.browsers();
      var oses = PlatformInfo.oses();
      var browser = userAgentDataOpt.bind(function (userAgentData) {
        return detectBrowser$1(browsers, userAgentData);
      }).orThunk(function () {
        return detectBrowser(browsers, userAgent);
      }).fold(Browser.unknown, Browser.nu);
      var os = detectOs(oses, userAgent).fold(OperatingSystem.unknown, OperatingSystem.nu);
      var deviceType = DeviceType(os, browser, userAgent, mediaMatch);
      return {
        browser: browser,
        os: os,
        deviceType: deviceType
      };
    };
    var PlatformDetection = { detect: detect$1 };

    var mediaMatch = function (query) {
      return window.matchMedia(query).matches;
    };
    var platform = cached(function () {
      return PlatformDetection.detect(navigator.userAgent, Optional.from(navigator.userAgentData), mediaMatch);
    });
    var detect = function () {
      return platform();
    };

    var is = function (element, selector) {
      var dom = element.dom;
      if (dom.nodeType !== ELEMENT) {
        return false;
      } else {
        var elem = dom;
        if (elem.matches !== undefined) {
          return elem.matches(selector);
        } else if (elem.msMatchesSelector !== undefined) {
          return elem.msMatchesSelector(selector);
        } else if (elem.webkitMatchesSelector !== undefined) {
          return elem.webkitMatchesSelector(selector);
        } else if (elem.mozMatchesSelector !== undefined) {
          return elem.mozMatchesSelector(selector);
        } else {
          throw new Error('Browser lacks native selectors');
        }
      }
    };
    var bypassSelector = function (dom) {
      return dom.nodeType !== ELEMENT && dom.nodeType !== DOCUMENT && dom.nodeType !== DOCUMENT_FRAGMENT || dom.childElementCount === 0;
    };
    var all$1 = function (selector, scope) {
      var base = scope === undefined ? document : scope.dom;
      return bypassSelector(base) ? [] : map(base.querySelectorAll(selector), SugarElement.fromDom);
    };

    var eq = function (e1, e2) {
      return e1.dom === e2.dom;
    };

    var owner = function (element) {
      return SugarElement.fromDom(element.dom.ownerDocument);
    };
    var documentOrOwner = function (dos) {
      return isDocument(dos) ? dos : owner(dos);
    };
    var parent = function (element) {
      return Optional.from(element.dom.parentNode).map(SugarElement.fromDom);
    };
    var parents = function (element, isRoot) {
      var stop = isFunction(isRoot) ? isRoot : never;
      var dom = element.dom;
      var ret = [];
      while (dom.parentNode !== null && dom.parentNode !== undefined) {
        var rawParent = dom.parentNode;
        var p = SugarElement.fromDom(rawParent);
        ret.push(p);
        if (stop(p) === true) {
          break;
        } else {
          dom = rawParent;
        }
      }
      return ret;
    };
    var siblings$2 = function (element) {
      var filterSelf = function (elements) {
        return filter$1(elements, function (x) {
          return !eq(element, x);
        });
      };
      return parent(element).map(children).map(filterSelf).getOr([]);
    };
    var children = function (element) {
      return map(element.dom.childNodes, SugarElement.fromDom);
    };

    var isShadowRoot = function (dos) {
      return isDocumentFragment(dos) && isNonNullable(dos.dom.host);
    };
    var supported = isFunction(Element.prototype.attachShadow) && isFunction(Node.prototype.getRootNode);
    var isSupported = constant(supported);
    var getRootNode = supported ? function (e) {
      return SugarElement.fromDom(e.dom.getRootNode());
    } : documentOrOwner;
    var getShadowRoot = function (e) {
      var r = getRootNode(e);
      return isShadowRoot(r) ? Optional.some(r) : Optional.none();
    };
    var getShadowHost = function (e) {
      return SugarElement.fromDom(e.dom.host);
    };
    var getOriginalEventTarget = function (event) {
      if (isSupported() && isNonNullable(event.target)) {
        var el = SugarElement.fromDom(event.target);
        if (isElement(el) && isOpenShadowHost(el)) {
          if (event.composed && event.composedPath) {
            var composedPath = event.composedPath();
            if (composedPath) {
              return head(composedPath);
            }
          }
        }
      }
      return Optional.from(event.target);
    };
    var isOpenShadowHost = function (element) {
      return isNonNullable(element.dom.shadowRoot);
    };

    var inBody = function (element) {
      var dom = isText(element) ? element.dom.parentNode : element.dom;
      if (dom === undefined || dom === null || dom.ownerDocument === null) {
        return false;
      }
      var doc = dom.ownerDocument;
      return getShadowRoot(SugarElement.fromDom(dom)).fold(function () {
        return doc.body.contains(dom);
      }, compose1(inBody, getShadowHost));
    };
    var getBody = function (doc) {
      var b = doc.dom.body;
      if (b === null || b === undefined) {
        throw new Error('Body is not available yet');
      }
      return SugarElement.fromDom(b);
    };

    var rawSet = function (dom, key, value) {
      if (isString(value) || isBoolean(value) || isNumber(value)) {
        dom.setAttribute(key, value + '');
      } else {
        console.error('Invalid call to Attribute.set. Key ', key, ':: Value ', value, ':: Element ', dom);
        throw new Error('Attribute value was not simple');
      }
    };
    var set = function (element, key, value) {
      rawSet(element.dom, key, value);
    };
    var get$3 = function (element, key) {
      var v = element.dom.getAttribute(key);
      return v === null ? undefined : v;
    };
    var remove = function (element, key) {
      element.dom.removeAttribute(key);
    };

    var internalSet = function (dom, property, value) {
      if (!isString(value)) {
        console.error('Invalid call to CSS.set. Property ', property, ':: Value ', value, ':: Element ', dom);
        throw new Error('CSS value must be a string: ' + value);
      }
      if (isSupported$1(dom)) {
        dom.style.setProperty(property, value);
      }
    };
    var setAll = function (element, css) {
      var dom = element.dom;
      each(css, function (v, k) {
        internalSet(dom, k, v);
      });
    };
    var get$2 = function (element, property) {
      var dom = element.dom;
      var styles = window.getComputedStyle(dom);
      var r = styles.getPropertyValue(property);
      return r === '' && !inBody(element) ? getUnsafeProperty(dom, property) : r;
    };
    var getUnsafeProperty = function (dom, property) {
      return isSupported$1(dom) ? dom.style.getPropertyValue(property) : '';
    };

    var mkEvent = function (target, x, y, stop, prevent, kill, raw) {
      return {
        target: target,
        x: x,
        y: y,
        stop: stop,
        prevent: prevent,
        kill: kill,
        raw: raw
      };
    };
    var fromRawEvent = function (rawEvent) {
      var target = SugarElement.fromDom(getOriginalEventTarget(rawEvent).getOr(rawEvent.target));
      var stop = function () {
        return rawEvent.stopPropagation();
      };
      var prevent = function () {
        return rawEvent.preventDefault();
      };
      var kill = compose(prevent, stop);
      return mkEvent(target, rawEvent.clientX, rawEvent.clientY, stop, prevent, kill, rawEvent);
    };
    var handle = function (filter, handler) {
      return function (rawEvent) {
        if (filter(rawEvent)) {
          handler(fromRawEvent(rawEvent));
        }
      };
    };
    var binder = function (element, event, filter, handler, useCapture) {
      var wrapped = handle(filter, handler);
      element.dom.addEventListener(event, wrapped, useCapture);
      return { unbind: curry(unbind, element, event, wrapped, useCapture) };
    };
    var bind$2 = function (element, event, filter, handler) {
      return binder(element, event, filter, handler, false);
    };
    var unbind = function (element, event, handler, useCapture) {
      element.dom.removeEventListener(event, handler, useCapture);
    };

    var filter = always;
    var bind$1 = function (element, event, handler) {
      return bind$2(element, event, filter, handler);
    };

    var r = function (left, top) {
      var translate = function (x, y) {
        return r(left + x, top + y);
      };
      return {
        left: left,
        top: top,
        translate: translate
      };
    };
    var SugarPosition = r;

    var get$1 = function (_DOC) {
      var doc = _DOC !== undefined ? _DOC.dom : document;
      var x = doc.body.scrollLeft || doc.documentElement.scrollLeft;
      var y = doc.body.scrollTop || doc.documentElement.scrollTop;
      return SugarPosition(x, y);
    };

    var get = function (_win) {
      var win = _win === undefined ? window : _win;
      if (detect().browser.isFirefox()) {
        return Optional.none();
      } else {
        return Optional.from(win['visualViewport']);
      }
    };
    var bounds = function (x, y, width, height) {
      return {
        x: x,
        y: y,
        width: width,
        height: height,
        right: x + width,
        bottom: y + height
      };
    };
    var getBounds = function (_win) {
      var win = _win === undefined ? window : _win;
      var doc = win.document;
      var scroll = get$1(SugarElement.fromDom(doc));
      return get(win).fold(function () {
        var html = win.document.documentElement;
        var width = html.clientWidth;
        var height = html.clientHeight;
        return bounds(scroll.left, scroll.top, width, height);
      }, function (visualViewport) {
        return bounds(Math.max(visualViewport.pageLeft, scroll.left), Math.max(visualViewport.pageTop, scroll.top), visualViewport.width, visualViewport.height);
      });
    };
    var bind = function (name, callback, _win) {
      return get(_win).map(function (visualViewport) {
        var handler = function (e) {
          return callback(fromRawEvent(e));
        };
        visualViewport.addEventListener(name, handler);
        return {
          unbind: function () {
            return visualViewport.removeEventListener(name, handler);
          }
        };
      }).getOrThunk(function () {
        return { unbind: noop };
      });
    };

    var global$2 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

    var global$1 = tinymce.util.Tools.resolve('tinymce.Env');

    var global = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var fireFullscreenStateChanged = function (editor, state) {
      editor.fire('FullscreenStateChanged', { state: state });
      editor.fire('ResizeEditor');
    };

    var getFullscreenNative = function (editor) {
      return editor.getParam('fullscreen_native', false, 'boolean');
    };

    var getFullscreenRoot = function (editor) {
      var elem = SugarElement.fromDom(editor.getElement());
      return getShadowRoot(elem).map(getShadowHost).getOrThunk(function () {
        return getBody(owner(elem));
      });
    };
    var getFullscreenElement = function (root) {
      if (root.fullscreenElement !== undefined) {
        return root.fullscreenElement;
      } else if (root.msFullscreenElement !== undefined) {
        return root.msFullscreenElement;
      } else if (root.webkitFullscreenElement !== undefined) {
        return root.webkitFullscreenElement;
      } else {
        return null;
      }
    };
    var getFullscreenchangeEventName = function () {
      if (document.fullscreenElement !== undefined) {
        return 'fullscreenchange';
      } else if (document.msFullscreenElement !== undefined) {
        return 'MSFullscreenChange';
      } else if (document.webkitFullscreenElement !== undefined) {
        return 'webkitfullscreenchange';
      } else {
        return 'fullscreenchange';
      }
    };
    var requestFullscreen = function (sugarElem) {
      var elem = sugarElem.dom;
      if (elem.requestFullscreen) {
        elem.requestFullscreen();
      } else if (elem.msRequestFullscreen) {
        elem.msRequestFullscreen();
      } else if (elem.webkitRequestFullScreen) {
        elem.webkitRequestFullScreen();
      }
    };
    var exitFullscreen = function (sugarDoc) {
      var doc = sugarDoc.dom;
      if (doc.exitFullscreen) {
        doc.exitFullscreen();
      } else if (doc.msExitFullscreen) {
        doc.msExitFullscreen();
      } else if (doc.webkitCancelFullScreen) {
        doc.webkitCancelFullScreen();
      }
    };
    var isFullscreenElement = function (elem) {
      return elem.dom === getFullscreenElement(owner(elem).dom);
    };

    var ancestors$1 = function (scope, predicate, isRoot) {
      return filter$1(parents(scope, isRoot), predicate);
    };
    var siblings$1 = function (scope, predicate) {
      return filter$1(siblings$2(scope), predicate);
    };

    var all = function (selector) {
      return all$1(selector);
    };
    var ancestors = function (scope, selector, isRoot) {
      return ancestors$1(scope, function (e) {
        return is(e, selector);
      }, isRoot);
    };
    var siblings = function (scope, selector) {
      return siblings$1(scope, function (e) {
        return is(e, selector);
      });
    };

    var attr = 'data-ephox-mobile-fullscreen-style';
    var siblingStyles = 'display:none!important;';
    var ancestorPosition = 'position:absolute!important;';
    var ancestorStyles = 'top:0!important;left:0!important;margin:0!important;padding:0!important;width:100%!important;height:100%!important;overflow:visible!important;';
    var bgFallback = 'background-color:rgb(255,255,255)!important;';
    var isAndroid = global$1.os.isAndroid();
    var matchColor = function (editorBody) {
      var color = get$2(editorBody, 'background-color');
      return color !== undefined && color !== '' ? 'background-color:' + color + '!important' : bgFallback;
    };
    var clobberStyles = function (dom, container, editorBody) {
      var gatherSiblings = function (element) {
        return siblings(element, '*:not(.tox-silver-sink)');
      };
      var clobber = function (clobberStyle) {
        return function (element) {
          var styles = get$3(element, 'style');
          var backup = styles === undefined ? 'no-styles' : styles.trim();
          if (backup === clobberStyle) {
            return;
          } else {
            set(element, attr, backup);
            setAll(element, dom.parseStyle(clobberStyle));
          }
        };
      };
      var ancestors$1 = ancestors(container, '*');
      var siblings$1 = bind$3(ancestors$1, gatherSiblings);
      var bgColor = matchColor(editorBody);
      each$1(siblings$1, clobber(siblingStyles));
      each$1(ancestors$1, clobber(ancestorPosition + ancestorStyles + bgColor));
      var containerStyles = isAndroid === true ? '' : ancestorPosition;
      clobber(containerStyles + ancestorStyles + bgColor)(container);
    };
    var restoreStyles = function (dom) {
      var clobberedEls = all('[' + attr + ']');
      each$1(clobberedEls, function (element) {
        var restore = get$3(element, attr);
        if (restore !== 'no-styles') {
          setAll(element, dom.parseStyle(restore));
        } else {
          remove(element, 'style');
        }
        remove(element, attr);
      });
    };

    var DOM = global$2.DOM;
    var getScrollPos = function () {
      return getBounds(window);
    };
    var setScrollPos = function (pos) {
      return window.scrollTo(pos.x, pos.y);
    };
    var viewportUpdate = get().fold(function () {
      return {
        bind: noop,
        unbind: noop
      };
    }, function (visualViewport) {
      var editorContainer = value();
      var resizeBinder = unbindable();
      var scrollBinder = unbindable();
      var refreshScroll = function () {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
      };
      var refreshVisualViewport = function () {
        window.requestAnimationFrame(function () {
          editorContainer.on(function (container) {
            return setAll(container, {
              top: visualViewport.offsetTop + 'px',
              left: visualViewport.offsetLeft + 'px',
              height: visualViewport.height + 'px',
              width: visualViewport.width + 'px'
            });
          });
        });
      };
      var update = global.throttle(function () {
        refreshScroll();
        refreshVisualViewport();
      }, 50);
      var bind$1 = function (element) {
        editorContainer.set(element);
        update();
        resizeBinder.set(bind('resize', update));
        scrollBinder.set(bind('scroll', update));
      };
      var unbind = function () {
        editorContainer.on(function () {
          resizeBinder.clear();
          scrollBinder.clear();
        });
        editorContainer.clear();
      };
      return {
        bind: bind$1,
        unbind: unbind
      };
    });
    var toggleFullscreen = function (editor, fullscreenState) {
      var body = document.body;
      var documentElement = document.documentElement;
      var editorContainer = editor.getContainer();
      var editorContainerS = SugarElement.fromDom(editorContainer);
      var fullscreenRoot = getFullscreenRoot(editor);
      var fullscreenInfo = fullscreenState.get();
      var editorBody = SugarElement.fromDom(editor.getBody());
      var isTouch = global$1.deviceType.isTouch();
      var editorContainerStyle = editorContainer.style;
      var iframe = editor.iframeElement;
      var iframeStyle = iframe.style;
      var handleClasses = function (handler) {
        handler(body, 'tox-fullscreen');
        handler(documentElement, 'tox-fullscreen');
        handler(editorContainer, 'tox-fullscreen');
        getShadowRoot(editorContainerS).map(function (root) {
          return getShadowHost(root).dom;
        }).each(function (host) {
          handler(host, 'tox-fullscreen');
          handler(host, 'tox-shadowhost');
        });
      };
      var cleanup = function () {
        if (isTouch) {
          restoreStyles(editor.dom);
        }
        handleClasses(DOM.removeClass);
        viewportUpdate.unbind();
        Optional.from(fullscreenState.get()).each(function (info) {
          return info.fullscreenChangeHandler.unbind();
        });
      };
      if (!fullscreenInfo) {
        var fullscreenChangeHandler = bind$1(owner(fullscreenRoot), getFullscreenchangeEventName(), function (_evt) {
          if (getFullscreenNative(editor)) {
            if (!isFullscreenElement(fullscreenRoot) && fullscreenState.get() !== null) {
              toggleFullscreen(editor, fullscreenState);
            }
          }
        });
        var newFullScreenInfo = {
          scrollPos: getScrollPos(),
          containerWidth: editorContainerStyle.width,
          containerHeight: editorContainerStyle.height,
          containerTop: editorContainerStyle.top,
          containerLeft: editorContainerStyle.left,
          iframeWidth: iframeStyle.width,
          iframeHeight: iframeStyle.height,
          fullscreenChangeHandler: fullscreenChangeHandler
        };
        if (isTouch) {
          clobberStyles(editor.dom, editorContainerS, editorBody);
        }
        iframeStyle.width = iframeStyle.height = '100%';
        editorContainerStyle.width = editorContainerStyle.height = '';
        handleClasses(DOM.addClass);
        viewportUpdate.bind(editorContainerS);
        editor.on('remove', cleanup);
        fullscreenState.set(newFullScreenInfo);
        if (getFullscreenNative(editor)) {
          requestFullscreen(fullscreenRoot);
        }
        fireFullscreenStateChanged(editor, true);
      } else {
        fullscreenInfo.fullscreenChangeHandler.unbind();
        if (getFullscreenNative(editor) && isFullscreenElement(fullscreenRoot)) {
          exitFullscreen(owner(fullscreenRoot));
        }
        iframeStyle.width = fullscreenInfo.iframeWidth;
        iframeStyle.height = fullscreenInfo.iframeHeight;
        editorContainerStyle.width = fullscreenInfo.containerWidth;
        editorContainerStyle.height = fullscreenInfo.containerHeight;
        editorContainerStyle.top = fullscreenInfo.containerTop;
        editorContainerStyle.left = fullscreenInfo.containerLeft;
        cleanup();
        setScrollPos(fullscreenInfo.scrollPos);
        fullscreenState.set(null);
        fireFullscreenStateChanged(editor, false);
        editor.off('remove', cleanup);
      }
    };

    var register$1 = function (editor, fullscreenState) {
      editor.addCommand('mceFullScreen', function () {
        toggleFullscreen(editor, fullscreenState);
      });
    };

    var makeSetupHandler = function (editor, fullscreenState) {
      return function (api) {
        api.setActive(fullscreenState.get() !== null);
        var editorEventCallback = function (e) {
          return api.setActive(e.state);
        };
        editor.on('FullscreenStateChanged', editorEventCallback);
        return function () {
          return editor.off('FullscreenStateChanged', editorEventCallback);
        };
      };
    };
    var register = function (editor, fullscreenState) {
      var onAction = function () {
        return editor.execCommand('mceFullScreen');
      };
      editor.ui.registry.addToggleMenuItem('fullscreen', {
        text: 'Fullscreen',
        icon: 'fullscreen',
        shortcut: 'Meta+Shift+F',
        onAction: onAction,
        onSetup: makeSetupHandler(editor, fullscreenState)
      });
      editor.ui.registry.addToggleButton('fullscreen', {
        tooltip: 'Fullscreen',
        icon: 'fullscreen',
        onAction: onAction,
        onSetup: makeSetupHandler(editor, fullscreenState)
      });
    };

    function Plugin () {
      global$3.add('fullscreen', function (editor) {
        var fullscreenState = Cell(null);
        if (editor.inline) {
          return get$5(fullscreenState);
        }
        register$1(editor, fullscreenState);
        register(editor, fullscreenState);
        editor.addShortcut('Meta+Shift+F', '', 'mceFullScreen');
        return get$5(fullscreenState);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/image/index.js":
/*!*****************************************************!*\
  !*** ./node_modules/tinymce/plugins/image/index.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "image" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/image')
//   ES2015:
//     import 'tinymce/plugins/image'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/image/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/image/plugin.js":
/*!******************************************************!*\
  !*** ./node_modules/tinymce/plugins/image/plugin.js ***!
  \******************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$6 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var eq = function (t) {
      return function (a) {
        return t === a;
      };
    };
    var isString = isType('string');
    var isObject = isType('object');
    var isArray = isType('array');
    var isNull = eq(null);
    var isBoolean = isSimpleType('boolean');
    var isNullable = function (a) {
      return a === null || a === undefined;
    };
    var isNonNullable = function (a) {
      return !isNullable(a);
    };
    var isFunction = isSimpleType('function');
    var isNumber = isSimpleType('number');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var keys = Object.keys;
    var hasOwnProperty = Object.hasOwnProperty;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };
    var objAcc = function (r) {
      return function (x, i) {
        r[i] = x;
      };
    };
    var internalFilter = function (obj, pred, onTrue, onFalse) {
      var r = {};
      each(obj, function (x, i) {
        (pred(x, i) ? onTrue : onFalse)(x, i);
      });
      return r;
    };
    var filter = function (obj, pred) {
      var t = {};
      internalFilter(obj, pred, objAcc(t), noop);
      return t;
    };
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };
    var hasNonNullableKey = function (obj, key) {
      return has(obj, key) && obj[key] !== undefined && obj[key] !== null;
    };

    var nativePush = Array.prototype.push;
    var flatten = function (xs) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; ++i) {
        if (!isArray(xs[i])) {
          throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
        }
        nativePush.apply(r, xs[i]);
      }
      return r;
    };
    var get = function (xs, i) {
      return i >= 0 && i < xs.length ? Optional.some(xs[i]) : Optional.none();
    };
    var head = function (xs) {
      return get(xs, 0);
    };
    var findMap = function (arr, f) {
      for (var i = 0; i < arr.length; i++) {
        var r = f(arr[i], i);
        if (r.isSome()) {
          return r;
        }
      }
      return Optional.none();
    };

    typeof window !== 'undefined' ? window : Function('return this;')();

    var rawSet = function (dom, key, value) {
      if (isString(value) || isBoolean(value) || isNumber(value)) {
        dom.setAttribute(key, value + '');
      } else {
        console.error('Invalid call to Attribute.set. Key ', key, ':: Value ', value, ':: Element ', dom);
        throw new Error('Attribute value was not simple');
      }
    };
    var set = function (element, key, value) {
      rawSet(element.dom, key, value);
    };
    var remove = function (element, key) {
      element.dom.removeAttribute(key);
    };

    var fromHtml = function (html, scope) {
      var doc = scope || document;
      var div = doc.createElement('div');
      div.innerHTML = html;
      if (!div.hasChildNodes() || div.childNodes.length > 1) {
        console.error('HTML does not have a single root node', html);
        throw new Error('HTML must have a single root node');
      }
      return fromDom(div.childNodes[0]);
    };
    var fromTag = function (tag, scope) {
      var doc = scope || document;
      var node = doc.createElement(tag);
      return fromDom(node);
    };
    var fromText = function (text, scope) {
      var doc = scope || document;
      var node = doc.createTextNode(text);
      return fromDom(node);
    };
    var fromDom = function (node) {
      if (node === null || node === undefined) {
        throw new Error('Node cannot be null or undefined');
      }
      return { dom: node };
    };
    var fromPoint = function (docElm, x, y) {
      return Optional.from(docElm.dom.elementFromPoint(x, y)).map(fromDom);
    };
    var SugarElement = {
      fromHtml: fromHtml,
      fromTag: fromTag,
      fromText: fromText,
      fromDom: fromDom,
      fromPoint: fromPoint
    };

    var global$5 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

    var global$4 = tinymce.util.Tools.resolve('tinymce.util.Promise');

    var global$3 = tinymce.util.Tools.resolve('tinymce.util.URI');

    var global$2 = tinymce.util.Tools.resolve('tinymce.util.XHR');

    var hasDimensions = function (editor) {
      return editor.getParam('image_dimensions', true, 'boolean');
    };
    var hasAdvTab = function (editor) {
      return editor.getParam('image_advtab', false, 'boolean');
    };
    var hasUploadTab = function (editor) {
      return editor.getParam('image_uploadtab', true, 'boolean');
    };
    var getPrependUrl = function (editor) {
      return editor.getParam('image_prepend_url', '', 'string');
    };
    var getClassList = function (editor) {
      return editor.getParam('image_class_list');
    };
    var hasDescription = function (editor) {
      return editor.getParam('image_description', true, 'boolean');
    };
    var hasImageTitle = function (editor) {
      return editor.getParam('image_title', false, 'boolean');
    };
    var hasImageCaption = function (editor) {
      return editor.getParam('image_caption', false, 'boolean');
    };
    var getImageList = function (editor) {
      return editor.getParam('image_list', false);
    };
    var hasUploadUrl = function (editor) {
      return isNonNullable(editor.getParam('images_upload_url'));
    };
    var hasUploadHandler = function (editor) {
      return isNonNullable(editor.getParam('images_upload_handler'));
    };
    var showAccessibilityOptions = function (editor) {
      return editor.getParam('a11y_advanced_options', false, 'boolean');
    };
    var isAutomaticUploadsEnabled = function (editor) {
      return editor.getParam('automatic_uploads', true, 'boolean');
    };

    var parseIntAndGetMax = function (val1, val2) {
      return Math.max(parseInt(val1, 10), parseInt(val2, 10));
    };
    var getImageSize = function (url) {
      return new global$4(function (callback) {
        var img = document.createElement('img');
        var done = function (dimensions) {
          img.onload = img.onerror = null;
          if (img.parentNode) {
            img.parentNode.removeChild(img);
          }
          callback(dimensions);
        };
        img.onload = function () {
          var width = parseIntAndGetMax(img.width, img.clientWidth);
          var height = parseIntAndGetMax(img.height, img.clientHeight);
          var dimensions = {
            width: width,
            height: height
          };
          done(global$4.resolve(dimensions));
        };
        img.onerror = function () {
          done(global$4.reject('Failed to get image dimensions for: ' + url));
        };
        var style = img.style;
        style.visibility = 'hidden';
        style.position = 'fixed';
        style.bottom = style.left = '0px';
        style.width = style.height = 'auto';
        document.body.appendChild(img);
        img.src = url;
      });
    };
    var removePixelSuffix = function (value) {
      if (value) {
        value = value.replace(/px$/, '');
      }
      return value;
    };
    var addPixelSuffix = function (value) {
      if (value.length > 0 && /^[0-9]+$/.test(value)) {
        value += 'px';
      }
      return value;
    };
    var mergeMargins = function (css) {
      if (css.margin) {
        var splitMargin = String(css.margin).split(' ');
        switch (splitMargin.length) {
        case 1:
          css['margin-top'] = css['margin-top'] || splitMargin[0];
          css['margin-right'] = css['margin-right'] || splitMargin[0];
          css['margin-bottom'] = css['margin-bottom'] || splitMargin[0];
          css['margin-left'] = css['margin-left'] || splitMargin[0];
          break;
        case 2:
          css['margin-top'] = css['margin-top'] || splitMargin[0];
          css['margin-right'] = css['margin-right'] || splitMargin[1];
          css['margin-bottom'] = css['margin-bottom'] || splitMargin[0];
          css['margin-left'] = css['margin-left'] || splitMargin[1];
          break;
        case 3:
          css['margin-top'] = css['margin-top'] || splitMargin[0];
          css['margin-right'] = css['margin-right'] || splitMargin[1];
          css['margin-bottom'] = css['margin-bottom'] || splitMargin[2];
          css['margin-left'] = css['margin-left'] || splitMargin[1];
          break;
        case 4:
          css['margin-top'] = css['margin-top'] || splitMargin[0];
          css['margin-right'] = css['margin-right'] || splitMargin[1];
          css['margin-bottom'] = css['margin-bottom'] || splitMargin[2];
          css['margin-left'] = css['margin-left'] || splitMargin[3];
        }
        delete css.margin;
      }
      return css;
    };
    var createImageList = function (editor, callback) {
      var imageList = getImageList(editor);
      if (isString(imageList)) {
        global$2.send({
          url: imageList,
          success: function (text) {
            callback(JSON.parse(text));
          }
        });
      } else if (isFunction(imageList)) {
        imageList(callback);
      } else {
        callback(imageList);
      }
    };
    var waitLoadImage = function (editor, data, imgElm) {
      var selectImage = function () {
        imgElm.onload = imgElm.onerror = null;
        if (editor.selection) {
          editor.selection.select(imgElm);
          editor.nodeChanged();
        }
      };
      imgElm.onload = function () {
        if (!data.width && !data.height && hasDimensions(editor)) {
          editor.dom.setAttribs(imgElm, {
            width: String(imgElm.clientWidth),
            height: String(imgElm.clientHeight)
          });
        }
        selectImage();
      };
      imgElm.onerror = selectImage;
    };
    var blobToDataUri = function (blob) {
      return new global$4(function (resolve, reject) {
        var reader = new FileReader();
        reader.onload = function () {
          resolve(reader.result);
        };
        reader.onerror = function () {
          reject(reader.error.message);
        };
        reader.readAsDataURL(blob);
      });
    };
    var isPlaceholderImage = function (imgElm) {
      return imgElm.nodeName === 'IMG' && (imgElm.hasAttribute('data-mce-object') || imgElm.hasAttribute('data-mce-placeholder'));
    };
    var isSafeImageUrl = function (editor, src) {
      return global$3.isDomSafe(src, 'img', editor.settings);
    };

    var DOM = global$5.DOM;
    var getHspace = function (image) {
      if (image.style.marginLeft && image.style.marginRight && image.style.marginLeft === image.style.marginRight) {
        return removePixelSuffix(image.style.marginLeft);
      } else {
        return '';
      }
    };
    var getVspace = function (image) {
      if (image.style.marginTop && image.style.marginBottom && image.style.marginTop === image.style.marginBottom) {
        return removePixelSuffix(image.style.marginTop);
      } else {
        return '';
      }
    };
    var getBorder = function (image) {
      if (image.style.borderWidth) {
        return removePixelSuffix(image.style.borderWidth);
      } else {
        return '';
      }
    };
    var getAttrib = function (image, name) {
      if (image.hasAttribute(name)) {
        return image.getAttribute(name);
      } else {
        return '';
      }
    };
    var getStyle = function (image, name) {
      return image.style[name] ? image.style[name] : '';
    };
    var hasCaption = function (image) {
      return image.parentNode !== null && image.parentNode.nodeName === 'FIGURE';
    };
    var updateAttrib = function (image, name, value) {
      if (value === '') {
        image.removeAttribute(name);
      } else {
        image.setAttribute(name, value);
      }
    };
    var wrapInFigure = function (image) {
      var figureElm = DOM.create('figure', { class: 'image' });
      DOM.insertAfter(figureElm, image);
      figureElm.appendChild(image);
      figureElm.appendChild(DOM.create('figcaption', { contentEditable: 'true' }, 'Caption'));
      figureElm.contentEditable = 'false';
    };
    var removeFigure = function (image) {
      var figureElm = image.parentNode;
      DOM.insertAfter(image, figureElm);
      DOM.remove(figureElm);
    };
    var toggleCaption = function (image) {
      if (hasCaption(image)) {
        removeFigure(image);
      } else {
        wrapInFigure(image);
      }
    };
    var normalizeStyle = function (image, normalizeCss) {
      var attrValue = image.getAttribute('style');
      var value = normalizeCss(attrValue !== null ? attrValue : '');
      if (value.length > 0) {
        image.setAttribute('style', value);
        image.setAttribute('data-mce-style', value);
      } else {
        image.removeAttribute('style');
      }
    };
    var setSize = function (name, normalizeCss) {
      return function (image, name, value) {
        if (image.style[name]) {
          image.style[name] = addPixelSuffix(value);
          normalizeStyle(image, normalizeCss);
        } else {
          updateAttrib(image, name, value);
        }
      };
    };
    var getSize = function (image, name) {
      if (image.style[name]) {
        return removePixelSuffix(image.style[name]);
      } else {
        return getAttrib(image, name);
      }
    };
    var setHspace = function (image, value) {
      var pxValue = addPixelSuffix(value);
      image.style.marginLeft = pxValue;
      image.style.marginRight = pxValue;
    };
    var setVspace = function (image, value) {
      var pxValue = addPixelSuffix(value);
      image.style.marginTop = pxValue;
      image.style.marginBottom = pxValue;
    };
    var setBorder = function (image, value) {
      var pxValue = addPixelSuffix(value);
      image.style.borderWidth = pxValue;
    };
    var setBorderStyle = function (image, value) {
      image.style.borderStyle = value;
    };
    var getBorderStyle = function (image) {
      return getStyle(image, 'borderStyle');
    };
    var isFigure = function (elm) {
      return elm.nodeName === 'FIGURE';
    };
    var isImage = function (elm) {
      return elm.nodeName === 'IMG';
    };
    var getIsDecorative = function (image) {
      return DOM.getAttrib(image, 'alt').length === 0 && DOM.getAttrib(image, 'role') === 'presentation';
    };
    var getAlt = function (image) {
      if (getIsDecorative(image)) {
        return '';
      } else {
        return getAttrib(image, 'alt');
      }
    };
    var defaultData = function () {
      return {
        src: '',
        alt: '',
        title: '',
        width: '',
        height: '',
        class: '',
        style: '',
        caption: false,
        hspace: '',
        vspace: '',
        border: '',
        borderStyle: '',
        isDecorative: false
      };
    };
    var getStyleValue = function (normalizeCss, data) {
      var image = document.createElement('img');
      updateAttrib(image, 'style', data.style);
      if (getHspace(image) || data.hspace !== '') {
        setHspace(image, data.hspace);
      }
      if (getVspace(image) || data.vspace !== '') {
        setVspace(image, data.vspace);
      }
      if (getBorder(image) || data.border !== '') {
        setBorder(image, data.border);
      }
      if (getBorderStyle(image) || data.borderStyle !== '') {
        setBorderStyle(image, data.borderStyle);
      }
      return normalizeCss(image.getAttribute('style'));
    };
    var create = function (normalizeCss, data) {
      var image = document.createElement('img');
      write(normalizeCss, __assign(__assign({}, data), { caption: false }), image);
      setAlt(image, data.alt, data.isDecorative);
      if (data.caption) {
        var figure = DOM.create('figure', { class: 'image' });
        figure.appendChild(image);
        figure.appendChild(DOM.create('figcaption', { contentEditable: 'true' }, 'Caption'));
        figure.contentEditable = 'false';
        return figure;
      } else {
        return image;
      }
    };
    var read = function (normalizeCss, image) {
      return {
        src: getAttrib(image, 'src'),
        alt: getAlt(image),
        title: getAttrib(image, 'title'),
        width: getSize(image, 'width'),
        height: getSize(image, 'height'),
        class: getAttrib(image, 'class'),
        style: normalizeCss(getAttrib(image, 'style')),
        caption: hasCaption(image),
        hspace: getHspace(image),
        vspace: getVspace(image),
        border: getBorder(image),
        borderStyle: getStyle(image, 'borderStyle'),
        isDecorative: getIsDecorative(image)
      };
    };
    var updateProp = function (image, oldData, newData, name, set) {
      if (newData[name] !== oldData[name]) {
        set(image, name, newData[name]);
      }
    };
    var setAlt = function (image, alt, isDecorative) {
      if (isDecorative) {
        DOM.setAttrib(image, 'role', 'presentation');
        var sugarImage = SugarElement.fromDom(image);
        set(sugarImage, 'alt', '');
      } else {
        if (isNull(alt)) {
          var sugarImage = SugarElement.fromDom(image);
          remove(sugarImage, 'alt');
        } else {
          var sugarImage = SugarElement.fromDom(image);
          set(sugarImage, 'alt', alt);
        }
        if (DOM.getAttrib(image, 'role') === 'presentation') {
          DOM.setAttrib(image, 'role', '');
        }
      }
    };
    var updateAlt = function (image, oldData, newData) {
      if (newData.alt !== oldData.alt || newData.isDecorative !== oldData.isDecorative) {
        setAlt(image, newData.alt, newData.isDecorative);
      }
    };
    var normalized = function (set, normalizeCss) {
      return function (image, name, value) {
        set(image, value);
        normalizeStyle(image, normalizeCss);
      };
    };
    var write = function (normalizeCss, newData, image) {
      var oldData = read(normalizeCss, image);
      updateProp(image, oldData, newData, 'caption', function (image, _name, _value) {
        return toggleCaption(image);
      });
      updateProp(image, oldData, newData, 'src', updateAttrib);
      updateProp(image, oldData, newData, 'title', updateAttrib);
      updateProp(image, oldData, newData, 'width', setSize('width', normalizeCss));
      updateProp(image, oldData, newData, 'height', setSize('height', normalizeCss));
      updateProp(image, oldData, newData, 'class', updateAttrib);
      updateProp(image, oldData, newData, 'style', normalized(function (image, value) {
        return updateAttrib(image, 'style', value);
      }, normalizeCss));
      updateProp(image, oldData, newData, 'hspace', normalized(setHspace, normalizeCss));
      updateProp(image, oldData, newData, 'vspace', normalized(setVspace, normalizeCss));
      updateProp(image, oldData, newData, 'border', normalized(setBorder, normalizeCss));
      updateProp(image, oldData, newData, 'borderStyle', normalized(setBorderStyle, normalizeCss));
      updateAlt(image, oldData, newData);
    };

    var normalizeCss$1 = function (editor, cssText) {
      var css = editor.dom.styles.parse(cssText);
      var mergedCss = mergeMargins(css);
      var compressed = editor.dom.styles.parse(editor.dom.styles.serialize(mergedCss));
      return editor.dom.styles.serialize(compressed);
    };
    var getSelectedImage = function (editor) {
      var imgElm = editor.selection.getNode();
      var figureElm = editor.dom.getParent(imgElm, 'figure.image');
      if (figureElm) {
        return editor.dom.select('img', figureElm)[0];
      }
      if (imgElm && (imgElm.nodeName !== 'IMG' || isPlaceholderImage(imgElm))) {
        return null;
      }
      return imgElm;
    };
    var splitTextBlock = function (editor, figure) {
      var dom = editor.dom;
      var textBlockElements = filter(editor.schema.getTextBlockElements(), function (_, parentElm) {
        return !editor.schema.isValidChild(parentElm, 'figure');
      });
      var textBlock = dom.getParent(figure.parentNode, function (node) {
        return hasNonNullableKey(textBlockElements, node.nodeName);
      }, editor.getBody());
      if (textBlock) {
        return dom.split(textBlock, figure);
      } else {
        return figure;
      }
    };
    var readImageDataFromSelection = function (editor) {
      var image = getSelectedImage(editor);
      return image ? read(function (css) {
        return normalizeCss$1(editor, css);
      }, image) : defaultData();
    };
    var insertImageAtCaret = function (editor, data) {
      var elm = create(function (css) {
        return normalizeCss$1(editor, css);
      }, data);
      editor.dom.setAttrib(elm, 'data-mce-id', '__mcenew');
      editor.focus();
      editor.selection.setContent(elm.outerHTML);
      var insertedElm = editor.dom.select('*[data-mce-id="__mcenew"]')[0];
      editor.dom.setAttrib(insertedElm, 'data-mce-id', null);
      if (isFigure(insertedElm)) {
        var figure = splitTextBlock(editor, insertedElm);
        editor.selection.select(figure);
      } else {
        editor.selection.select(insertedElm);
      }
    };
    var syncSrcAttr = function (editor, image) {
      editor.dom.setAttrib(image, 'src', image.getAttribute('src'));
    };
    var deleteImage = function (editor, image) {
      if (image) {
        var elm = editor.dom.is(image.parentNode, 'figure.image') ? image.parentNode : image;
        editor.dom.remove(elm);
        editor.focus();
        editor.nodeChanged();
        if (editor.dom.isEmpty(editor.getBody())) {
          editor.setContent('');
          editor.selection.setCursorLocation();
        }
      }
    };
    var writeImageDataToSelection = function (editor, data) {
      var image = getSelectedImage(editor);
      write(function (css) {
        return normalizeCss$1(editor, css);
      }, data, image);
      syncSrcAttr(editor, image);
      if (isFigure(image.parentNode)) {
        var figure = image.parentNode;
        splitTextBlock(editor, figure);
        editor.selection.select(image.parentNode);
      } else {
        editor.selection.select(image);
        waitLoadImage(editor, data, image);
      }
    };
    var sanitizeImageData = function (editor, data) {
      var src = data.src;
      return __assign(__assign({}, data), { src: isSafeImageUrl(editor, src) ? src : '' });
    };
    var insertOrUpdateImage = function (editor, partialData) {
      var image = getSelectedImage(editor);
      if (image) {
        var selectedImageData = read(function (css) {
          return normalizeCss$1(editor, css);
        }, image);
        var data = __assign(__assign({}, selectedImageData), partialData);
        var sanitizedData = sanitizeImageData(editor, data);
        if (data.src) {
          writeImageDataToSelection(editor, sanitizedData);
        } else {
          deleteImage(editor, image);
        }
      } else if (partialData.src) {
        insertImageAtCaret(editor, __assign(__assign({}, defaultData()), partialData));
      }
    };

    var deep = function (old, nu) {
      var bothObjects = isObject(old) && isObject(nu);
      return bothObjects ? deepMerge(old, nu) : nu;
    };
    var baseMerge = function (merger) {
      return function () {
        var objects = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          objects[_i] = arguments[_i];
        }
        if (objects.length === 0) {
          throw new Error('Can\'t merge zero objects');
        }
        var ret = {};
        for (var j = 0; j < objects.length; j++) {
          var curObject = objects[j];
          for (var key in curObject) {
            if (has(curObject, key)) {
              ret[key] = merger(ret[key], curObject[key]);
            }
          }
        }
        return ret;
      };
    };
    var deepMerge = baseMerge(deep);

    var isNotEmpty = function (s) {
      return s.length > 0;
    };

    var global$1 = tinymce.util.Tools.resolve('tinymce.util.ImageUploader');

    var global = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var getValue = function (item) {
      return isString(item.value) ? item.value : '';
    };
    var getText = function (item) {
      if (isString(item.text)) {
        return item.text;
      } else if (isString(item.title)) {
        return item.title;
      } else {
        return '';
      }
    };
    var sanitizeList = function (list, extractValue) {
      var out = [];
      global.each(list, function (item) {
        var text = getText(item);
        if (item.menu !== undefined) {
          var items = sanitizeList(item.menu, extractValue);
          out.push({
            text: text,
            items: items
          });
        } else {
          var value = extractValue(item);
          out.push({
            text: text,
            value: value
          });
        }
      });
      return out;
    };
    var sanitizer = function (extractor) {
      if (extractor === void 0) {
        extractor = getValue;
      }
      return function (list) {
        if (list) {
          return Optional.from(list).map(function (list) {
            return sanitizeList(list, extractor);
          });
        } else {
          return Optional.none();
        }
      };
    };
    var sanitize = function (list) {
      return sanitizer(getValue)(list);
    };
    var isGroup = function (item) {
      return has(item, 'items');
    };
    var findEntryDelegate = function (list, value) {
      return findMap(list, function (item) {
        if (isGroup(item)) {
          return findEntryDelegate(item.items, value);
        } else if (item.value === value) {
          return Optional.some(item);
        } else {
          return Optional.none();
        }
      });
    };
    var findEntry = function (optList, value) {
      return optList.bind(function (list) {
        return findEntryDelegate(list, value);
      });
    };
    var ListUtils = {
      sanitizer: sanitizer,
      sanitize: sanitize,
      findEntry: findEntry
    };

    var makeTab$2 = function (_info) {
      return {
        title: 'Advanced',
        name: 'advanced',
        items: [
          {
            type: 'input',
            label: 'Style',
            name: 'style'
          },
          {
            type: 'grid',
            columns: 2,
            items: [
              {
                type: 'input',
                label: 'Vertical space',
                name: 'vspace',
                inputMode: 'numeric'
              },
              {
                type: 'input',
                label: 'Horizontal space',
                name: 'hspace',
                inputMode: 'numeric'
              },
              {
                type: 'input',
                label: 'Border width',
                name: 'border',
                inputMode: 'numeric'
              },
              {
                type: 'listbox',
                name: 'borderstyle',
                label: 'Border style',
                items: [
                  {
                    text: 'Select...',
                    value: ''
                  },
                  {
                    text: 'Solid',
                    value: 'solid'
                  },
                  {
                    text: 'Dotted',
                    value: 'dotted'
                  },
                  {
                    text: 'Dashed',
                    value: 'dashed'
                  },
                  {
                    text: 'Double',
                    value: 'double'
                  },
                  {
                    text: 'Groove',
                    value: 'groove'
                  },
                  {
                    text: 'Ridge',
                    value: 'ridge'
                  },
                  {
                    text: 'Inset',
                    value: 'inset'
                  },
                  {
                    text: 'Outset',
                    value: 'outset'
                  },
                  {
                    text: 'None',
                    value: 'none'
                  },
                  {
                    text: 'Hidden',
                    value: 'hidden'
                  }
                ]
              }
            ]
          }
        ]
      };
    };
    var AdvTab = { makeTab: makeTab$2 };

    var collect = function (editor) {
      var urlListSanitizer = ListUtils.sanitizer(function (item) {
        return editor.convertURL(item.value || item.url, 'src');
      });
      var futureImageList = new global$4(function (completer) {
        createImageList(editor, function (imageList) {
          completer(urlListSanitizer(imageList).map(function (items) {
            return flatten([
              [{
                  text: 'None',
                  value: ''
                }],
              items
            ]);
          }));
        });
      });
      var classList = ListUtils.sanitize(getClassList(editor));
      var hasAdvTab$1 = hasAdvTab(editor);
      var hasUploadTab$1 = hasUploadTab(editor);
      var hasUploadUrl$1 = hasUploadUrl(editor);
      var hasUploadHandler$1 = hasUploadHandler(editor);
      var image = readImageDataFromSelection(editor);
      var hasDescription$1 = hasDescription(editor);
      var hasImageTitle$1 = hasImageTitle(editor);
      var hasDimensions$1 = hasDimensions(editor);
      var hasImageCaption$1 = hasImageCaption(editor);
      var hasAccessibilityOptions = showAccessibilityOptions(editor);
      var automaticUploads = isAutomaticUploadsEnabled(editor);
      var prependURL = Optional.some(getPrependUrl(editor)).filter(function (preUrl) {
        return isString(preUrl) && preUrl.length > 0;
      });
      return futureImageList.then(function (imageList) {
        return {
          image: image,
          imageList: imageList,
          classList: classList,
          hasAdvTab: hasAdvTab$1,
          hasUploadTab: hasUploadTab$1,
          hasUploadUrl: hasUploadUrl$1,
          hasUploadHandler: hasUploadHandler$1,
          hasDescription: hasDescription$1,
          hasImageTitle: hasImageTitle$1,
          hasDimensions: hasDimensions$1,
          hasImageCaption: hasImageCaption$1,
          prependURL: prependURL,
          hasAccessibilityOptions: hasAccessibilityOptions,
          automaticUploads: automaticUploads
        };
      });
    };

    var makeItems = function (info) {
      var imageUrl = {
        name: 'src',
        type: 'urlinput',
        filetype: 'image',
        label: 'Source'
      };
      var imageList = info.imageList.map(function (items) {
        return {
          name: 'images',
          type: 'listbox',
          label: 'Image list',
          items: items
        };
      });
      var imageDescription = {
        name: 'alt',
        type: 'input',
        label: 'Alternative description',
        disabled: info.hasAccessibilityOptions && info.image.isDecorative
      };
      var imageTitle = {
        name: 'title',
        type: 'input',
        label: 'Image title'
      };
      var imageDimensions = {
        name: 'dimensions',
        type: 'sizeinput'
      };
      var isDecorative = {
        type: 'label',
        label: 'Accessibility',
        items: [{
            name: 'isDecorative',
            type: 'checkbox',
            label: 'Image is decorative'
          }]
      };
      var classList = info.classList.map(function (items) {
        return {
          name: 'classes',
          type: 'listbox',
          label: 'Class',
          items: items
        };
      });
      var caption = {
        type: 'label',
        label: 'Caption',
        items: [{
            type: 'checkbox',
            name: 'caption',
            label: 'Show caption'
          }]
      };
      var getDialogContainerType = function (useColumns) {
        return useColumns ? {
          type: 'grid',
          columns: 2
        } : { type: 'panel' };
      };
      return flatten([
        [imageUrl],
        imageList.toArray(),
        info.hasAccessibilityOptions && info.hasDescription ? [isDecorative] : [],
        info.hasDescription ? [imageDescription] : [],
        info.hasImageTitle ? [imageTitle] : [],
        info.hasDimensions ? [imageDimensions] : [],
        [__assign(__assign({}, getDialogContainerType(info.classList.isSome() && info.hasImageCaption)), {
            items: flatten([
              classList.toArray(),
              info.hasImageCaption ? [caption] : []
            ])
          })]
      ]);
    };
    var makeTab$1 = function (info) {
      return {
        title: 'General',
        name: 'general',
        items: makeItems(info)
      };
    };
    var MainTab = {
      makeTab: makeTab$1,
      makeItems: makeItems
    };

    var makeTab = function (_info) {
      var items = [{
          type: 'dropzone',
          name: 'fileinput'
        }];
      return {
        title: 'Upload',
        name: 'upload',
        items: items
      };
    };
    var UploadTab = { makeTab: makeTab };

    var createState = function (info) {
      return {
        prevImage: ListUtils.findEntry(info.imageList, info.image.src),
        prevAlt: info.image.alt,
        open: true
      };
    };
    var fromImageData = function (image) {
      return {
        src: {
          value: image.src,
          meta: {}
        },
        images: image.src,
        alt: image.alt,
        title: image.title,
        dimensions: {
          width: image.width,
          height: image.height
        },
        classes: image.class,
        caption: image.caption,
        style: image.style,
        vspace: image.vspace,
        border: image.border,
        hspace: image.hspace,
        borderstyle: image.borderStyle,
        fileinput: [],
        isDecorative: image.isDecorative
      };
    };
    var toImageData = function (data, removeEmptyAlt) {
      return {
        src: data.src.value,
        alt: data.alt.length === 0 && removeEmptyAlt ? null : data.alt,
        title: data.title,
        width: data.dimensions.width,
        height: data.dimensions.height,
        class: data.classes,
        style: data.style,
        caption: data.caption,
        hspace: data.hspace,
        vspace: data.vspace,
        border: data.border,
        borderStyle: data.borderstyle,
        isDecorative: data.isDecorative
      };
    };
    var addPrependUrl2 = function (info, srcURL) {
      if (!/^(?:[a-zA-Z]+:)?\/\//.test(srcURL)) {
        return info.prependURL.bind(function (prependUrl) {
          if (srcURL.substring(0, prependUrl.length) !== prependUrl) {
            return Optional.some(prependUrl + srcURL);
          }
          return Optional.none();
        });
      }
      return Optional.none();
    };
    var addPrependUrl = function (info, api) {
      var data = api.getData();
      addPrependUrl2(info, data.src.value).each(function (srcURL) {
        api.setData({
          src: {
            value: srcURL,
            meta: data.src.meta
          }
        });
      });
    };
    var formFillFromMeta2 = function (info, data, meta) {
      if (info.hasDescription && isString(meta.alt)) {
        data.alt = meta.alt;
      }
      if (info.hasAccessibilityOptions) {
        data.isDecorative = meta.isDecorative || data.isDecorative || false;
      }
      if (info.hasImageTitle && isString(meta.title)) {
        data.title = meta.title;
      }
      if (info.hasDimensions) {
        if (isString(meta.width)) {
          data.dimensions.width = meta.width;
        }
        if (isString(meta.height)) {
          data.dimensions.height = meta.height;
        }
      }
      if (isString(meta.class)) {
        ListUtils.findEntry(info.classList, meta.class).each(function (entry) {
          data.classes = entry.value;
        });
      }
      if (info.hasImageCaption) {
        if (isBoolean(meta.caption)) {
          data.caption = meta.caption;
        }
      }
      if (info.hasAdvTab) {
        if (isString(meta.style)) {
          data.style = meta.style;
        }
        if (isString(meta.vspace)) {
          data.vspace = meta.vspace;
        }
        if (isString(meta.border)) {
          data.border = meta.border;
        }
        if (isString(meta.hspace)) {
          data.hspace = meta.hspace;
        }
        if (isString(meta.borderstyle)) {
          data.borderstyle = meta.borderstyle;
        }
      }
    };
    var formFillFromMeta = function (info, api) {
      var data = api.getData();
      var meta = data.src.meta;
      if (meta !== undefined) {
        var newData = deepMerge({}, data);
        formFillFromMeta2(info, newData, meta);
        api.setData(newData);
      }
    };
    var calculateImageSize = function (helpers, info, state, api) {
      var data = api.getData();
      var url = data.src.value;
      var meta = data.src.meta || {};
      if (!meta.width && !meta.height && info.hasDimensions) {
        if (isNotEmpty(url)) {
          helpers.imageSize(url).then(function (size) {
            if (state.open) {
              api.setData({ dimensions: size });
            }
          }).catch(function (e) {
            return console.error(e);
          });
        } else {
          api.setData({
            dimensions: {
              width: '',
              height: ''
            }
          });
        }
      }
    };
    var updateImagesDropdown = function (info, state, api) {
      var data = api.getData();
      var image = ListUtils.findEntry(info.imageList, data.src.value);
      state.prevImage = image;
      api.setData({
        images: image.map(function (entry) {
          return entry.value;
        }).getOr('')
      });
    };
    var changeSrc = function (helpers, info, state, api) {
      addPrependUrl(info, api);
      formFillFromMeta(info, api);
      calculateImageSize(helpers, info, state, api);
      updateImagesDropdown(info, state, api);
    };
    var changeImages = function (helpers, info, state, api) {
      var data = api.getData();
      var image = ListUtils.findEntry(info.imageList, data.images);
      image.each(function (img) {
        var updateAlt = data.alt === '' || state.prevImage.map(function (image) {
          return image.text === data.alt;
        }).getOr(false);
        if (updateAlt) {
          if (img.value === '') {
            api.setData({
              src: img,
              alt: state.prevAlt
            });
          } else {
            api.setData({
              src: img,
              alt: img.text
            });
          }
        } else {
          api.setData({ src: img });
        }
      });
      state.prevImage = image;
      changeSrc(helpers, info, state, api);
    };
    var calcVSpace = function (css) {
      var matchingTopBottom = css['margin-top'] && css['margin-bottom'] && css['margin-top'] === css['margin-bottom'];
      return matchingTopBottom ? removePixelSuffix(String(css['margin-top'])) : '';
    };
    var calcHSpace = function (css) {
      var matchingLeftRight = css['margin-right'] && css['margin-left'] && css['margin-right'] === css['margin-left'];
      return matchingLeftRight ? removePixelSuffix(String(css['margin-right'])) : '';
    };
    var calcBorderWidth = function (css) {
      return css['border-width'] ? removePixelSuffix(String(css['border-width'])) : '';
    };
    var calcBorderStyle = function (css) {
      return css['border-style'] ? String(css['border-style']) : '';
    };
    var calcStyle = function (parseStyle, serializeStyle, css) {
      return serializeStyle(parseStyle(serializeStyle(css)));
    };
    var changeStyle2 = function (parseStyle, serializeStyle, data) {
      var css = mergeMargins(parseStyle(data.style));
      var dataCopy = deepMerge({}, data);
      dataCopy.vspace = calcVSpace(css);
      dataCopy.hspace = calcHSpace(css);
      dataCopy.border = calcBorderWidth(css);
      dataCopy.borderstyle = calcBorderStyle(css);
      dataCopy.style = calcStyle(parseStyle, serializeStyle, css);
      return dataCopy;
    };
    var changeStyle = function (helpers, api) {
      var data = api.getData();
      var newData = changeStyle2(helpers.parseStyle, helpers.serializeStyle, data);
      api.setData(newData);
    };
    var changeAStyle = function (helpers, info, api) {
      var data = deepMerge(fromImageData(info.image), api.getData());
      var style = getStyleValue(helpers.normalizeCss, toImageData(data, false));
      api.setData({ style: style });
    };
    var changeFileInput = function (helpers, info, state, api) {
      var data = api.getData();
      api.block('Uploading image');
      head(data.fileinput).fold(function () {
        api.unblock();
      }, function (file) {
        var blobUri = URL.createObjectURL(file);
        var finalize = function () {
          api.unblock();
          URL.revokeObjectURL(blobUri);
        };
        var updateSrcAndSwitchTab = function (url) {
          api.setData({
            src: {
              value: url,
              meta: {}
            }
          });
          api.showTab('general');
          changeSrc(helpers, info, state, api);
        };
        blobToDataUri(file).then(function (dataUrl) {
          var blobInfo = helpers.createBlobCache(file, blobUri, dataUrl);
          if (info.automaticUploads) {
            helpers.uploadImage(blobInfo).then(function (result) {
              updateSrcAndSwitchTab(result.url);
              finalize();
            }).catch(function (err) {
              finalize();
              helpers.alertErr(err);
            });
          } else {
            helpers.addToBlobCache(blobInfo);
            updateSrcAndSwitchTab(blobInfo.blobUri());
            api.unblock();
          }
        });
      });
    };
    var changeHandler = function (helpers, info, state) {
      return function (api, evt) {
        if (evt.name === 'src') {
          changeSrc(helpers, info, state, api);
        } else if (evt.name === 'images') {
          changeImages(helpers, info, state, api);
        } else if (evt.name === 'alt') {
          state.prevAlt = api.getData().alt;
        } else if (evt.name === 'style') {
          changeStyle(helpers, api);
        } else if (evt.name === 'vspace' || evt.name === 'hspace' || evt.name === 'border' || evt.name === 'borderstyle') {
          changeAStyle(helpers, info, api);
        } else if (evt.name === 'fileinput') {
          changeFileInput(helpers, info, state, api);
        } else if (evt.name === 'isDecorative') {
          if (api.getData().isDecorative) {
            api.disable('alt');
          } else {
            api.enable('alt');
          }
        }
      };
    };
    var closeHandler = function (state) {
      return function () {
        state.open = false;
      };
    };
    var makeDialogBody = function (info) {
      if (info.hasAdvTab || info.hasUploadUrl || info.hasUploadHandler) {
        var tabPanel = {
          type: 'tabpanel',
          tabs: flatten([
            [MainTab.makeTab(info)],
            info.hasAdvTab ? [AdvTab.makeTab(info)] : [],
            info.hasUploadTab && (info.hasUploadUrl || info.hasUploadHandler) ? [UploadTab.makeTab(info)] : []
          ])
        };
        return tabPanel;
      } else {
        var panel = {
          type: 'panel',
          items: MainTab.makeItems(info)
        };
        return panel;
      }
    };
    var makeDialog = function (helpers) {
      return function (info) {
        var state = createState(info);
        return {
          title: 'Insert/Edit Image',
          size: 'normal',
          body: makeDialogBody(info),
          buttons: [
            {
              type: 'cancel',
              name: 'cancel',
              text: 'Cancel'
            },
            {
              type: 'submit',
              name: 'save',
              text: 'Save',
              primary: true
            }
          ],
          initialData: fromImageData(info.image),
          onSubmit: helpers.onSubmit(info),
          onChange: changeHandler(helpers, info, state),
          onClose: closeHandler(state)
        };
      };
    };
    var submitHandler = function (editor) {
      return function (info) {
        return function (api) {
          var data = deepMerge(fromImageData(info.image), api.getData());
          editor.execCommand('mceUpdateImage', false, toImageData(data, info.hasAccessibilityOptions));
          editor.editorUpload.uploadImagesAuto();
          api.close();
        };
      };
    };
    var imageSize = function (editor) {
      return function (url) {
        if (!isSafeImageUrl(editor, url)) {
          return global$4.resolve({
            width: '',
            height: ''
          });
        } else {
          return getImageSize(editor.documentBaseURI.toAbsolute(url)).then(function (dimensions) {
            return {
              width: String(dimensions.width),
              height: String(dimensions.height)
            };
          });
        }
      };
    };
    var createBlobCache = function (editor) {
      return function (file, blobUri, dataUrl) {
        return editor.editorUpload.blobCache.create({
          blob: file,
          blobUri: blobUri,
          name: file.name ? file.name.replace(/\.[^\.]+$/, '') : null,
          filename: file.name,
          base64: dataUrl.split(',')[1]
        });
      };
    };
    var addToBlobCache = function (editor) {
      return function (blobInfo) {
        editor.editorUpload.blobCache.add(blobInfo);
      };
    };
    var alertErr = function (editor) {
      return function (message) {
        editor.windowManager.alert(message);
      };
    };
    var normalizeCss = function (editor) {
      return function (cssText) {
        return normalizeCss$1(editor, cssText);
      };
    };
    var parseStyle = function (editor) {
      return function (cssText) {
        return editor.dom.parseStyle(cssText);
      };
    };
    var serializeStyle = function (editor) {
      return function (stylesArg, name) {
        return editor.dom.serializeStyle(stylesArg, name);
      };
    };
    var uploadImage = function (editor) {
      return function (blobInfo) {
        return global$1(editor).upload([blobInfo], false).then(function (results) {
          if (results.length === 0) {
            return global$4.reject('Failed to upload image');
          } else if (results[0].status === false) {
            return global$4.reject(results[0].error.message);
          } else {
            return results[0];
          }
        });
      };
    };
    var Dialog = function (editor) {
      var helpers = {
        onSubmit: submitHandler(editor),
        imageSize: imageSize(editor),
        addToBlobCache: addToBlobCache(editor),
        createBlobCache: createBlobCache(editor),
        alertErr: alertErr(editor),
        normalizeCss: normalizeCss(editor),
        parseStyle: parseStyle(editor),
        serializeStyle: serializeStyle(editor),
        uploadImage: uploadImage(editor)
      };
      var open = function () {
        collect(editor).then(makeDialog(helpers)).then(editor.windowManager.open);
      };
      return { open: open };
    };

    var register$1 = function (editor) {
      editor.addCommand('mceImage', Dialog(editor).open);
      editor.addCommand('mceUpdateImage', function (_ui, data) {
        editor.undoManager.transact(function () {
          return insertOrUpdateImage(editor, data);
        });
      });
    };

    var hasImageClass = function (node) {
      var className = node.attr('class');
      return className && /\bimage\b/.test(className);
    };
    var toggleContentEditableState = function (state) {
      return function (nodes) {
        var i = nodes.length;
        var toggleContentEditable = function (node) {
          node.attr('contenteditable', state ? 'true' : null);
        };
        while (i--) {
          var node = nodes[i];
          if (hasImageClass(node)) {
            node.attr('contenteditable', state ? 'false' : null);
            global.each(node.getAll('figcaption'), toggleContentEditable);
          }
        }
      };
    };
    var setup = function (editor) {
      editor.on('PreInit', function () {
        editor.parser.addNodeFilter('figure', toggleContentEditableState(true));
        editor.serializer.addNodeFilter('figure', toggleContentEditableState(false));
      });
    };

    var register = function (editor) {
      editor.ui.registry.addToggleButton('image', {
        icon: 'image',
        tooltip: 'Insert/edit image',
        onAction: Dialog(editor).open,
        onSetup: function (buttonApi) {
          buttonApi.setActive(isNonNullable(getSelectedImage(editor)));
          return editor.selection.selectorChangedWithUnbind('img:not([data-mce-object],[data-mce-placeholder]),figure.image', buttonApi.setActive).unbind;
        }
      });
      editor.ui.registry.addMenuItem('image', {
        icon: 'image',
        text: 'Image...',
        onAction: Dialog(editor).open
      });
      editor.ui.registry.addContextMenu('image', {
        update: function (element) {
          return isFigure(element) || isImage(element) && !isPlaceholderImage(element) ? ['image'] : [];
        }
      });
    };

    function Plugin () {
      global$6.add('image', function (editor) {
        setup(editor);
        register(editor);
        register$1(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/imagetools/index.js":
/*!**********************************************************!*\
  !*** ./node_modules/tinymce/plugins/imagetools/index.js ***!
  \**********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "imagetools" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/imagetools')
//   ES2015:
//     import 'tinymce/plugins/imagetools'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/imagetools/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/imagetools/plugin.js":
/*!***********************************************************!*\
  !*** ./node_modules/tinymce/plugins/imagetools/plugin.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var global$5 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var global$4 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var isArray = isType('array');
    var isNullable = function (a) {
      return a === null || a === undefined;
    };
    var isNonNullable = function (a) {
      return !isNullable(a);
    };
    var isFunction = isSimpleType('function');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var exports$1 = {}, module = { exports: exports$1 };
    (function (define, exports, module, require) {
      (function (global, factory) {
        typeof exports === 'object' && typeof module !== 'undefined' ? module.exports = factory() : typeof define === 'function' && define.amd ? define(factory) : (global = typeof globalThis !== 'undefined' ? globalThis : global || self, global.EphoxContactWrapper = factory());
      }(this, function () {
        var commonjsGlobal = typeof globalThis !== 'undefined' ? globalThis : typeof window !== 'undefined' ? window : typeof __webpack_require__.g !== 'undefined' ? __webpack_require__.g : typeof self !== 'undefined' ? self : {};
        var promise = { exports: {} };
        (function (module) {
          (function (root) {
            var setTimeoutFunc = setTimeout;
            function noop() {
            }
            function bind(fn, thisArg) {
              return function () {
                fn.apply(thisArg, arguments);
              };
            }
            function Promise(fn) {
              if (typeof this !== 'object')
                throw new TypeError('Promises must be constructed via new');
              if (typeof fn !== 'function')
                throw new TypeError('not a function');
              this._state = 0;
              this._handled = false;
              this._value = undefined;
              this._deferreds = [];
              doResolve(fn, this);
            }
            function handle(self, deferred) {
              while (self._state === 3) {
                self = self._value;
              }
              if (self._state === 0) {
                self._deferreds.push(deferred);
                return;
              }
              self._handled = true;
              Promise._immediateFn(function () {
                var cb = self._state === 1 ? deferred.onFulfilled : deferred.onRejected;
                if (cb === null) {
                  (self._state === 1 ? resolve : reject)(deferred.promise, self._value);
                  return;
                }
                var ret;
                try {
                  ret = cb(self._value);
                } catch (e) {
                  reject(deferred.promise, e);
                  return;
                }
                resolve(deferred.promise, ret);
              });
            }
            function resolve(self, newValue) {
              try {
                if (newValue === self)
                  throw new TypeError('A promise cannot be resolved with itself.');
                if (newValue && (typeof newValue === 'object' || typeof newValue === 'function')) {
                  var then = newValue.then;
                  if (newValue instanceof Promise) {
                    self._state = 3;
                    self._value = newValue;
                    finale(self);
                    return;
                  } else if (typeof then === 'function') {
                    doResolve(bind(then, newValue), self);
                    return;
                  }
                }
                self._state = 1;
                self._value = newValue;
                finale(self);
              } catch (e) {
                reject(self, e);
              }
            }
            function reject(self, newValue) {
              self._state = 2;
              self._value = newValue;
              finale(self);
            }
            function finale(self) {
              if (self._state === 2 && self._deferreds.length === 0) {
                Promise._immediateFn(function () {
                  if (!self._handled) {
                    Promise._unhandledRejectionFn(self._value);
                  }
                });
              }
              for (var i = 0, len = self._deferreds.length; i < len; i++) {
                handle(self, self._deferreds[i]);
              }
              self._deferreds = null;
            }
            function Handler(onFulfilled, onRejected, promise) {
              this.onFulfilled = typeof onFulfilled === 'function' ? onFulfilled : null;
              this.onRejected = typeof onRejected === 'function' ? onRejected : null;
              this.promise = promise;
            }
            function doResolve(fn, self) {
              var done = false;
              try {
                fn(function (value) {
                  if (done)
                    return;
                  done = true;
                  resolve(self, value);
                }, function (reason) {
                  if (done)
                    return;
                  done = true;
                  reject(self, reason);
                });
              } catch (ex) {
                if (done)
                  return;
                done = true;
                reject(self, ex);
              }
            }
            Promise.prototype['catch'] = function (onRejected) {
              return this.then(null, onRejected);
            };
            Promise.prototype.then = function (onFulfilled, onRejected) {
              var prom = new this.constructor(noop);
              handle(this, new Handler(onFulfilled, onRejected, prom));
              return prom;
            };
            Promise.all = function (arr) {
              var args = Array.prototype.slice.call(arr);
              return new Promise(function (resolve, reject) {
                if (args.length === 0)
                  return resolve([]);
                var remaining = args.length;
                function res(i, val) {
                  try {
                    if (val && (typeof val === 'object' || typeof val === 'function')) {
                      var then = val.then;
                      if (typeof then === 'function') {
                        then.call(val, function (val) {
                          res(i, val);
                        }, reject);
                        return;
                      }
                    }
                    args[i] = val;
                    if (--remaining === 0) {
                      resolve(args);
                    }
                  } catch (ex) {
                    reject(ex);
                  }
                }
                for (var i = 0; i < args.length; i++) {
                  res(i, args[i]);
                }
              });
            };
            Promise.resolve = function (value) {
              if (value && typeof value === 'object' && value.constructor === Promise) {
                return value;
              }
              return new Promise(function (resolve) {
                resolve(value);
              });
            };
            Promise.reject = function (value) {
              return new Promise(function (resolve, reject) {
                reject(value);
              });
            };
            Promise.race = function (values) {
              return new Promise(function (resolve, reject) {
                for (var i = 0, len = values.length; i < len; i++) {
                  values[i].then(resolve, reject);
                }
              });
            };
            Promise._immediateFn = typeof setImmediate === 'function' ? function (fn) {
              setImmediate(fn);
            } : function (fn) {
              setTimeoutFunc(fn, 0);
            };
            Promise._unhandledRejectionFn = function _unhandledRejectionFn(err) {
              if (typeof console !== 'undefined' && console) {
                console.warn('Possible Unhandled Promise Rejection:', err);
              }
            };
            Promise._setImmediateFn = function _setImmediateFn(fn) {
              Promise._immediateFn = fn;
            };
            Promise._setUnhandledRejectionFn = function _setUnhandledRejectionFn(fn) {
              Promise._unhandledRejectionFn = fn;
            };
            if (module.exports) {
              module.exports = Promise;
            } else if (!root.Promise) {
              root.Promise = Promise;
            }
          }(commonjsGlobal));
        }(promise));
        var promisePolyfill = promise.exports;
        var Global = function () {
          if (typeof window !== 'undefined') {
            return window;
          } else {
            return Function('return this;')();
          }
        }();
        var promisePolyfill_1 = { boltExport: Global.Promise || promisePolyfill };
        return promisePolyfill_1;
      }));
    }(undefined, exports$1, module));
    var Promise$1 = module.exports.boltExport;

    var create$1 = function (width, height) {
      return resize(document.createElement('canvas'), width, height);
    };
    var clone = function (canvas) {
      var tCanvas = create$1(canvas.width, canvas.height);
      var ctx = get2dContext(tCanvas);
      ctx.drawImage(canvas, 0, 0);
      return tCanvas;
    };
    var get2dContext = function (canvas) {
      return canvas.getContext('2d');
    };
    var resize = function (canvas, width, height) {
      canvas.width = width;
      canvas.height = height;
      return canvas;
    };

    var getWidth = function (image) {
      return image.naturalWidth || image.width;
    };
    var getHeight = function (image) {
      return image.naturalHeight || image.height;
    };

    var imageToBlob$2 = function (image) {
      var src = image.src;
      if (src.indexOf('data:') === 0) {
        return dataUriToBlob(src);
      }
      return anyUriToBlob(src);
    };
    var blobToImage$1 = function (blob) {
      return new Promise$1(function (resolve, reject) {
        var blobUrl = URL.createObjectURL(blob);
        var image = new Image();
        var removeListeners = function () {
          image.removeEventListener('load', loaded);
          image.removeEventListener('error', error);
        };
        var loaded = function () {
          removeListeners();
          resolve(image);
        };
        var error = function () {
          removeListeners();
          reject('Unable to load data of type ' + blob.type + ': ' + blobUrl);
        };
        image.addEventListener('load', loaded);
        image.addEventListener('error', error);
        image.src = blobUrl;
        if (image.complete) {
          setTimeout(loaded, 0);
        }
      });
    };
    var anyUriToBlob = function (url) {
      return new Promise$1(function (resolve, reject) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', url, true);
        xhr.responseType = 'blob';
        xhr.onload = function () {
          if (this.status === 200) {
            resolve(this.response);
          }
        };
        xhr.onerror = function () {
          var _this = this;
          var corsError = function () {
            var obj = new Error('No access to download image');
            obj.code = 18;
            obj.name = 'SecurityError';
            return obj;
          };
          var genericError = function () {
            return new Error('Error ' + _this.status + ' downloading image');
          };
          reject(this.status === 0 ? corsError() : genericError());
        };
        xhr.send();
      });
    };
    var dataUriToBlobSync = function (uri) {
      var data = uri.split(',');
      var matches = /data:([^;]+)/.exec(data[0]);
      if (!matches) {
        return Optional.none();
      }
      var mimetype = matches[1];
      var base64 = data[1];
      var sliceSize = 1024;
      var byteCharacters = atob(base64);
      var bytesLength = byteCharacters.length;
      var slicesCount = Math.ceil(bytesLength / sliceSize);
      var byteArrays = new Array(slicesCount);
      for (var sliceIndex = 0; sliceIndex < slicesCount; ++sliceIndex) {
        var begin = sliceIndex * sliceSize;
        var end = Math.min(begin + sliceSize, bytesLength);
        var bytes = new Array(end - begin);
        for (var offset = begin, i = 0; offset < end; ++i, ++offset) {
          bytes[i] = byteCharacters[offset].charCodeAt(0);
        }
        byteArrays[sliceIndex] = new Uint8Array(bytes);
      }
      return Optional.some(new Blob(byteArrays, { type: mimetype }));
    };
    var dataUriToBlob = function (uri) {
      return new Promise$1(function (resolve, reject) {
        dataUriToBlobSync(uri).fold(function () {
          reject('uri is not base64: ' + uri);
        }, resolve);
      });
    };
    var canvasToBlob = function (canvas, type, quality) {
      type = type || 'image/png';
      if (isFunction(HTMLCanvasElement.prototype.toBlob)) {
        return new Promise$1(function (resolve, reject) {
          canvas.toBlob(function (blob) {
            if (blob) {
              resolve(blob);
            } else {
              reject();
            }
          }, type, quality);
        });
      } else {
        return dataUriToBlob(canvas.toDataURL(type, quality));
      }
    };
    var canvasToDataURL = function (canvas, type, quality) {
      type = type || 'image/png';
      return canvas.toDataURL(type, quality);
    };
    var blobToCanvas = function (blob) {
      return blobToImage$1(blob).then(function (image) {
        revokeImageUrl(image);
        var canvas = create$1(getWidth(image), getHeight(image));
        var context = get2dContext(canvas);
        context.drawImage(image, 0, 0);
        return canvas;
      });
    };
    var blobToDataUri = function (blob) {
      return new Promise$1(function (resolve) {
        var reader = new FileReader();
        reader.onloadend = function () {
          resolve(reader.result);
        };
        reader.readAsDataURL(blob);
      });
    };
    var revokeImageUrl = function (image) {
      URL.revokeObjectURL(image.src);
    };

    var blobToImage = function (blob) {
      return blobToImage$1(blob);
    };
    var imageToBlob$1 = function (image) {
      return imageToBlob$2(image);
    };

    var nativeIndexOf = Array.prototype.indexOf;
    var rawIndexOf = function (ts, t) {
      return nativeIndexOf.call(ts, t);
    };
    var contains = function (xs, x) {
      return rawIndexOf(xs, x) > -1;
    };
    var each$1 = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var filter = function (xs, pred) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          r.push(x);
        }
      }
      return r;
    };
    var foldl = function (xs, f, acc) {
      each$1(xs, function (x, i) {
        acc = f(acc, x, i);
      });
      return acc;
    };
    var findUntil = function (xs, pred, until) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          return Optional.some(x);
        } else if (until(x, i)) {
          break;
        }
      }
      return Optional.none();
    };
    var find = function (xs, pred) {
      return findUntil(xs, pred, never);
    };
    var forall = function (xs, pred) {
      for (var i = 0, len = xs.length; i < len; ++i) {
        var x = xs[i];
        if (pred(x, i) !== true) {
          return false;
        }
      }
      return true;
    };

    var keys = Object.keys;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };

    var generate = function (cases) {
      if (!isArray(cases)) {
        throw new Error('cases must be an array');
      }
      if (cases.length === 0) {
        throw new Error('there must be at least one case');
      }
      var constructors = [];
      var adt = {};
      each$1(cases, function (acase, count) {
        var keys$1 = keys(acase);
        if (keys$1.length !== 1) {
          throw new Error('one and only one name per case');
        }
        var key = keys$1[0];
        var value = acase[key];
        if (adt[key] !== undefined) {
          throw new Error('duplicate key detected:' + key);
        } else if (key === 'cata') {
          throw new Error('cannot have a case named cata (sorry)');
        } else if (!isArray(value)) {
          throw new Error('case arguments must be an array');
        }
        constructors.push(key);
        adt[key] = function () {
          var args = [];
          for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
          }
          var argLength = args.length;
          if (argLength !== value.length) {
            throw new Error('Wrong number of arguments to case ' + key + '. Expected ' + value.length + ' (' + value + '), got ' + argLength);
          }
          var match = function (branches) {
            var branchKeys = keys(branches);
            if (constructors.length !== branchKeys.length) {
              throw new Error('Wrong number of arguments to match. Expected: ' + constructors.join(',') + '\nActual: ' + branchKeys.join(','));
            }
            var allReqd = forall(constructors, function (reqKey) {
              return contains(branchKeys, reqKey);
            });
            if (!allReqd) {
              throw new Error('Not all branches were specified when using match. Specified: ' + branchKeys.join(', ') + '\nRequired: ' + constructors.join(', '));
            }
            return branches[key].apply(null, args);
          };
          return {
            fold: function () {
              var foldArgs = [];
              for (var _i = 0; _i < arguments.length; _i++) {
                foldArgs[_i] = arguments[_i];
              }
              if (foldArgs.length !== cases.length) {
                throw new Error('Wrong number of arguments to fold. Expected ' + cases.length + ', got ' + foldArgs.length);
              }
              var target = foldArgs[count];
              return target.apply(null, args);
            },
            match: match,
            log: function (label) {
              console.log(label, {
                constructors: constructors,
                constructor: key,
                params: args
              });
            }
          };
        };
      });
      return adt;
    };
    var Adt = { generate: generate };

    Adt.generate([
      {
        bothErrors: [
          'error1',
          'error2'
        ]
      },
      {
        firstError: [
          'error1',
          'value2'
        ]
      },
      {
        secondError: [
          'value1',
          'error2'
        ]
      },
      {
        bothValues: [
          'value1',
          'value2'
        ]
      }
    ]);

    var create = function (getCanvas, blob, uri) {
      var initialType = blob.type;
      var getType = constant(initialType);
      var toBlob = function () {
        return Promise$1.resolve(blob);
      };
      var toDataURL = constant(uri);
      var toBase64 = function () {
        return uri.split(',')[1];
      };
      var toAdjustedBlob = function (type, quality) {
        return getCanvas.then(function (canvas) {
          return canvasToBlob(canvas, type, quality);
        });
      };
      var toAdjustedDataURL = function (type, quality) {
        return getCanvas.then(function (canvas) {
          return canvasToDataURL(canvas, type, quality);
        });
      };
      var toAdjustedBase64 = function (type, quality) {
        return toAdjustedDataURL(type, quality).then(function (dataurl) {
          return dataurl.split(',')[1];
        });
      };
      var toCanvas = function () {
        return getCanvas.then(clone);
      };
      return {
        getType: getType,
        toBlob: toBlob,
        toDataURL: toDataURL,
        toBase64: toBase64,
        toAdjustedBlob: toAdjustedBlob,
        toAdjustedDataURL: toAdjustedDataURL,
        toAdjustedBase64: toAdjustedBase64,
        toCanvas: toCanvas
      };
    };
    var fromBlob = function (blob) {
      return blobToDataUri(blob).then(function (uri) {
        return create(blobToCanvas(blob), blob, uri);
      });
    };
    var fromCanvas = function (canvas, type) {
      return canvasToBlob(canvas, type).then(function (blob) {
        return create(Promise$1.resolve(canvas), blob, canvas.toDataURL());
      });
    };

    var ceilWithPrecision = function (num, precision) {
      if (precision === void 0) {
        precision = 2;
      }
      var mul = Math.pow(10, precision);
      var upper = Math.round(num * mul);
      return Math.ceil(upper / mul);
    };
    var rotate$2 = function (ir, angle) {
      return ir.toCanvas().then(function (canvas) {
        return applyRotate(canvas, ir.getType(), angle);
      });
    };
    var applyRotate = function (image, type, angle) {
      var degrees = angle < 0 ? 360 + angle : angle;
      var rad = degrees * Math.PI / 180;
      var width = image.width;
      var height = image.height;
      var sin = Math.sin(rad);
      var cos = Math.cos(rad);
      var newWidth = ceilWithPrecision(Math.abs(width * cos) + Math.abs(height * sin));
      var newHeight = ceilWithPrecision(Math.abs(width * sin) + Math.abs(height * cos));
      var canvas = create$1(newWidth, newHeight);
      var context = get2dContext(canvas);
      context.translate(newWidth / 2, newHeight / 2);
      context.rotate(rad);
      context.drawImage(image, -width / 2, -height / 2);
      return fromCanvas(canvas, type);
    };
    var flip$2 = function (ir, axis) {
      return ir.toCanvas().then(function (canvas) {
        return applyFlip(canvas, ir.getType(), axis);
      });
    };
    var applyFlip = function (image, type, axis) {
      var canvas = create$1(image.width, image.height);
      var context = get2dContext(canvas);
      if (axis === 'v') {
        context.scale(1, -1);
        context.drawImage(image, 0, -canvas.height);
      } else {
        context.scale(-1, 1);
        context.drawImage(image, -canvas.width, 0);
      }
      return fromCanvas(canvas, type);
    };

    var flip$1 = function (ir, axis) {
      return flip$2(ir, axis);
    };
    var rotate$1 = function (ir, angle) {
      return rotate$2(ir, angle);
    };

    var sendRequest = function (url, headers, withCredentials) {
      if (withCredentials === void 0) {
        withCredentials = false;
      }
      return new Promise$1(function (resolve) {
        var xhr = new XMLHttpRequest();
        xhr.onreadystatechange = function () {
          if (xhr.readyState === 4) {
            resolve({
              status: xhr.status,
              blob: xhr.response
            });
          }
        };
        xhr.open('GET', url, true);
        xhr.withCredentials = withCredentials;
        each(headers, function (value, key) {
          xhr.setRequestHeader(key, value);
        });
        xhr.responseType = 'blob';
        xhr.send();
      });
    };
    var readBlobText = function (blob) {
      return new Promise$1(function (resolve, reject) {
        var reader = new FileReader();
        reader.onload = function () {
          resolve(reader.result);
        };
        reader.onerror = function (e) {
          reject(e);
        };
        reader.readAsText(blob);
      });
    };
    var parseJson = function (text) {
      try {
        return Optional.some(JSON.parse(text));
      } catch (ex) {
        return Optional.none();
      }
    };

    var friendlyHttpErrors = [
      {
        code: 404,
        message: 'Could not find Image Proxy'
      },
      {
        code: 403,
        message: 'Rejected request'
      },
      {
        code: 0,
        message: 'Incorrect Image Proxy URL'
      }
    ];
    var friendlyServiceErrors = [
      {
        type: 'not_found',
        message: 'Failed to load image.'
      },
      {
        type: 'key_missing',
        message: 'The request did not include an api key.'
      },
      {
        type: 'key_not_found',
        message: 'The provided api key could not be found.'
      },
      {
        type: 'domain_not_trusted',
        message: 'The api key is not valid for the request origins.'
      }
    ];
    var traverseJson = function (json, path) {
      var value = foldl(path, function (result, key) {
        return isNonNullable(result) ? result[key] : undefined;
      }, json);
      return Optional.from(value);
    };
    var isServiceErrorCode = function (code, blob) {
      return (blob === null || blob === void 0 ? void 0 : blob.type) === 'application/json' && (code === 400 || code === 403 || code === 404 || code === 500);
    };
    var getHttpErrorMsg = function (status) {
      var message = find(friendlyHttpErrors, function (error) {
        return status === error.code;
      }).fold(constant('Unknown ImageProxy error'), function (error) {
        return error.message;
      });
      return 'ImageProxy HTTP error: ' + message;
    };
    var handleHttpError = function (status) {
      var message = getHttpErrorMsg(status);
      return Promise$1.reject(message);
    };
    var getServiceErrorMsg = function (type) {
      return find(friendlyServiceErrors, function (error) {
        return error.type === type;
      }).fold(constant('Unknown service error'), function (error) {
        return error.message;
      });
    };
    var getServiceError = function (text) {
      var serviceError = parseJson(text);
      var errorMsg = serviceError.bind(function (err) {
        return traverseJson(err, [
          'error',
          'type'
        ]).map(getServiceErrorMsg);
      }).getOr('Invalid JSON in service error message');
      return 'ImageProxy Service error: ' + errorMsg;
    };
    var handleServiceError = function (blob) {
      return readBlobText(blob).then(function (text) {
        var serviceError = getServiceError(text);
        return Promise$1.reject(serviceError);
      });
    };
    var handleServiceErrorResponse = function (status, blob) {
      return isServiceErrorCode(status, blob) ? handleServiceError(blob) : handleHttpError(status);
    };

    var appendApiKey = function (url, apiKey) {
      var separator = url.indexOf('?') === -1 ? '?' : '&';
      if (/[?&]apiKey=/.test(url)) {
        return url;
      } else {
        return url + separator + 'apiKey=' + encodeURIComponent(apiKey);
      }
    };
    var isError = function (status) {
      return status < 200 || status >= 300;
    };
    var requestServiceBlob = function (url, apiKey) {
      var headers = {
        'Content-Type': 'application/json;charset=UTF-8',
        'tiny-api-key': apiKey
      };
      return sendRequest(appendApiKey(url, apiKey), headers).then(function (result) {
        return isError(result.status) ? handleServiceErrorResponse(result.status, result.blob) : Promise$1.resolve(result.blob);
      });
    };
    var requestBlob = function (url, withCredentials) {
      return sendRequest(url, {}, withCredentials).then(function (result) {
        return isError(result.status) ? handleHttpError(result.status) : Promise$1.resolve(result.blob);
      });
    };
    var getUrl = function (url, apiKey, withCredentials) {
      if (withCredentials === void 0) {
        withCredentials = false;
      }
      return apiKey ? requestServiceBlob(url, apiKey) : requestBlob(url, withCredentials);
    };

    var blobToImageResult = function (blob) {
      return fromBlob(blob);
    };

    var ELEMENT = 1;

    var fromHtml = function (html, scope) {
      var doc = scope || document;
      var div = doc.createElement('div');
      div.innerHTML = html;
      if (!div.hasChildNodes() || div.childNodes.length > 1) {
        console.error('HTML does not have a single root node', html);
        throw new Error('HTML must have a single root node');
      }
      return fromDom(div.childNodes[0]);
    };
    var fromTag = function (tag, scope) {
      var doc = scope || document;
      var node = doc.createElement(tag);
      return fromDom(node);
    };
    var fromText = function (text, scope) {
      var doc = scope || document;
      var node = doc.createTextNode(text);
      return fromDom(node);
    };
    var fromDom = function (node) {
      if (node === null || node === undefined) {
        throw new Error('Node cannot be null or undefined');
      }
      return { dom: node };
    };
    var fromPoint = function (docElm, x, y) {
      return Optional.from(docElm.dom.elementFromPoint(x, y)).map(fromDom);
    };
    var SugarElement = {
      fromHtml: fromHtml,
      fromTag: fromTag,
      fromText: fromText,
      fromDom: fromDom,
      fromPoint: fromPoint
    };

    var is = function (element, selector) {
      var dom = element.dom;
      if (dom.nodeType !== ELEMENT) {
        return false;
      } else {
        var elem = dom;
        if (elem.matches !== undefined) {
          return elem.matches(selector);
        } else if (elem.msMatchesSelector !== undefined) {
          return elem.msMatchesSelector(selector);
        } else if (elem.webkitMatchesSelector !== undefined) {
          return elem.webkitMatchesSelector(selector);
        } else if (elem.mozMatchesSelector !== undefined) {
          return elem.mozMatchesSelector(selector);
        } else {
          throw new Error('Browser lacks native selectors');
        }
      }
    };

    typeof window !== 'undefined' ? window : Function('return this;')();

    var child$1 = function (scope, predicate) {
      var pred = function (node) {
        return predicate(SugarElement.fromDom(node));
      };
      var result = find(scope.dom.childNodes, pred);
      return result.map(SugarElement.fromDom);
    };

    var child = function (scope, selector) {
      return child$1(scope, function (e) {
        return is(e, selector);
      });
    };

    var global$3 = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var global$2 = tinymce.util.Tools.resolve('tinymce.util.Promise');

    var global$1 = tinymce.util.Tools.resolve('tinymce.util.URI');

    var getToolbarItems = function (editor) {
      return editor.getParam('imagetools_toolbar', 'rotateleft rotateright flipv fliph editimage imageoptions');
    };
    var getProxyUrl = function (editor) {
      return editor.getParam('imagetools_proxy');
    };
    var getCorsHosts = function (editor) {
      return editor.getParam('imagetools_cors_hosts', [], 'string[]');
    };
    var getCredentialsHosts = function (editor) {
      return editor.getParam('imagetools_credentials_hosts', [], 'string[]');
    };
    var getFetchImage = function (editor) {
      return Optional.from(editor.getParam('imagetools_fetch_image', null, 'function'));
    };
    var getApiKey = function (editor) {
      return editor.getParam('api_key', editor.getParam('imagetools_api_key', '', 'string'), 'string');
    };
    var getUploadTimeout = function (editor) {
      return editor.getParam('images_upload_timeout', 30000, 'number');
    };
    var shouldReuseFilename = function (editor) {
      return editor.getParam('images_reuse_filename', false, 'boolean');
    };

    var getImageSize = function (img) {
      var width, height;
      var isPxValue = function (value) {
        return /^[0-9\.]+px$/.test(value);
      };
      width = img.style.width;
      height = img.style.height;
      if (width || height) {
        if (isPxValue(width) && isPxValue(height)) {
          return {
            w: parseInt(width, 10),
            h: parseInt(height, 10)
          };
        }
        return null;
      }
      width = img.width;
      height = img.height;
      if (width && height) {
        return {
          w: parseInt(width, 10),
          h: parseInt(height, 10)
        };
      }
      return null;
    };
    var setImageSize = function (img, size) {
      var width, height;
      if (size) {
        width = img.style.width;
        height = img.style.height;
        if (width || height) {
          img.style.width = size.w + 'px';
          img.style.height = size.h + 'px';
          img.removeAttribute('data-mce-style');
        }
        width = img.width;
        height = img.height;
        if (width || height) {
          img.setAttribute('width', String(size.w));
          img.setAttribute('height', String(size.h));
        }
      }
    };
    var getNaturalImageSize = function (img) {
      return {
        w: img.naturalWidth,
        h: img.naturalHeight
      };
    };

    var count = 0;
    var getFigureImg = function (elem) {
      return child(SugarElement.fromDom(elem), 'img');
    };
    var isFigure = function (editor, elem) {
      return editor.dom.is(elem, 'figure');
    };
    var isImage = function (editor, imgNode) {
      return editor.dom.is(imgNode, 'img:not([data-mce-object],[data-mce-placeholder])');
    };
    var getEditableImage = function (editor, node) {
      var isEditable = function (imgNode) {
        return isImage(editor, imgNode) && (isLocalImage(editor, imgNode) || isCorsImage(editor, imgNode) || isNonNullable(getProxyUrl(editor)));
      };
      if (isFigure(editor, node)) {
        return getFigureImg(node).bind(function (img) {
          return isEditable(img.dom) ? Optional.some(img.dom) : Optional.none();
        });
      } else {
        return isEditable(node) ? Optional.some(node) : Optional.none();
      }
    };
    var displayError = function (editor, error) {
      editor.notificationManager.open({
        text: error,
        type: 'error'
      });
    };
    var getSelectedImage = function (editor) {
      var elem = editor.selection.getNode();
      var figureElm = editor.dom.getParent(elem, 'figure.image');
      if (figureElm !== null && isFigure(editor, figureElm)) {
        return getFigureImg(figureElm);
      } else if (isImage(editor, elem)) {
        return Optional.some(SugarElement.fromDom(elem));
      } else {
        return Optional.none();
      }
    };
    var extractFilename = function (editor, url, group) {
      var m = url.match(/(?:\/|^)(([^\/\?]+)\.(?:[a-z0-9.]+))(?:\?|$)/i);
      return isNonNullable(m) ? editor.dom.encode(m[group]) : null;
    };
    var createId = function () {
      return 'imagetools' + count++;
    };
    var isLocalImage = function (editor, img) {
      var url = img.src;
      return url.indexOf('data:') === 0 || url.indexOf('blob:') === 0 || new global$1(url).host === editor.documentBaseURI.host;
    };
    var isCorsImage = function (editor, img) {
      return global$4.inArray(getCorsHosts(editor), new global$1(img.src).host) !== -1;
    };
    var isCorsWithCredentialsImage = function (editor, img) {
      return global$4.inArray(getCredentialsHosts(editor), new global$1(img.src).host) !== -1;
    };
    var defaultFetchImage = function (editor, img) {
      if (isCorsImage(editor, img)) {
        return getUrl(img.src, null, isCorsWithCredentialsImage(editor, img));
      }
      if (!isLocalImage(editor, img)) {
        var proxyUrl = getProxyUrl(editor);
        var src = proxyUrl + (proxyUrl.indexOf('?') === -1 ? '?' : '&') + 'url=' + encodeURIComponent(img.src);
        var apiKey = getApiKey(editor);
        return getUrl(src, apiKey, false);
      }
      return imageToBlob$1(img);
    };
    var imageToBlob = function (editor, img) {
      return getFetchImage(editor).fold(function () {
        return defaultFetchImage(editor, img);
      }, function (customFetchImage) {
        return customFetchImage(img);
      });
    };
    var findBlob = function (editor, img) {
      var blobInfo = editor.editorUpload.blobCache.getByUri(img.src);
      if (blobInfo) {
        return global$2.resolve(blobInfo.blob());
      }
      return imageToBlob(editor, img);
    };
    var startTimedUpload = function (editor, imageUploadTimerState) {
      var imageUploadTimer = global$3.setEditorTimeout(editor, function () {
        editor.editorUpload.uploadImagesAuto();
      }, getUploadTimeout(editor));
      imageUploadTimerState.set(imageUploadTimer);
    };
    var cancelTimedUpload = function (imageUploadTimerState) {
      global$3.clearTimeout(imageUploadTimerState.get());
    };
    var updateSelectedImage = function (editor, origBlob, ir, uploadImmediately, imageUploadTimerState, selectedImage, size) {
      return ir.toBlob().then(function (blob) {
        var uri, name, filename, blobInfo;
        var blobCache = editor.editorUpload.blobCache;
        uri = selectedImage.src;
        var useFilename = origBlob.type === blob.type;
        if (shouldReuseFilename(editor)) {
          blobInfo = blobCache.getByUri(uri);
          if (isNonNullable(blobInfo)) {
            uri = blobInfo.uri();
            name = blobInfo.name();
            filename = blobInfo.filename();
          } else {
            name = extractFilename(editor, uri, 2);
            filename = extractFilename(editor, uri, 1);
          }
        }
        blobInfo = blobCache.create({
          id: createId(),
          blob: blob,
          base64: ir.toBase64(),
          uri: uri,
          name: name,
          filename: useFilename ? filename : undefined
        });
        blobCache.add(blobInfo);
        editor.undoManager.transact(function () {
          var imageLoadedHandler = function () {
            editor.$(selectedImage).off('load', imageLoadedHandler);
            editor.nodeChanged();
            if (uploadImmediately) {
              editor.editorUpload.uploadImagesAuto();
            } else {
              cancelTimedUpload(imageUploadTimerState);
              startTimedUpload(editor, imageUploadTimerState);
            }
          };
          editor.$(selectedImage).on('load', imageLoadedHandler);
          if (size) {
            editor.$(selectedImage).attr({
              width: size.w,
              height: size.h
            });
          }
          editor.$(selectedImage).attr({ src: blobInfo.blobUri() }).removeAttr('data-mce-src');
        });
        return blobInfo;
      });
    };
    var selectedImageOperation = function (editor, imageUploadTimerState, fn, size) {
      return function () {
        var imgOpt = getSelectedImage(editor);
        return imgOpt.fold(function () {
          displayError(editor, 'Could not find selected image');
        }, function (img) {
          return editor._scanForImages().then(function () {
            return findBlob(editor, img.dom);
          }).then(function (blob) {
            return blobToImageResult(blob).then(fn).then(function (imageResult) {
              return updateSelectedImage(editor, blob, imageResult, false, imageUploadTimerState, img.dom, size);
            });
          }).catch(function (error) {
            displayError(editor, error);
          });
        });
      };
    };
    var rotate = function (editor, imageUploadTimerState, angle) {
      return function () {
        var imgOpt = getSelectedImage(editor);
        var flippedSize = imgOpt.map(function (img) {
          var size = getImageSize(img.dom);
          return size ? {
            w: size.h,
            h: size.w
          } : null;
        }).getOrNull();
        return selectedImageOperation(editor, imageUploadTimerState, function (imageResult) {
          return rotate$1(imageResult, angle);
        }, flippedSize)();
      };
    };
    var flip = function (editor, imageUploadTimerState, axis) {
      return function () {
        return selectedImageOperation(editor, imageUploadTimerState, function (imageResult) {
          return flip$1(imageResult, axis);
        })();
      };
    };
    var handleDialogBlob = function (editor, imageUploadTimerState, img, originalSize, blob) {
      return blobToImage(blob).then(function (newImage) {
        var newSize = getNaturalImageSize(newImage);
        if (originalSize.w !== newSize.w || originalSize.h !== newSize.h) {
          if (getImageSize(img)) {
            setImageSize(img, newSize);
          }
        }
        URL.revokeObjectURL(newImage.src);
        return blob;
      }).then(blobToImageResult).then(function (imageResult) {
        return updateSelectedImage(editor, blob, imageResult, true, imageUploadTimerState, img);
      });
    };

    var saveState = 'save-state';
    var disable = 'disable';
    var enable = 'enable';

    var createState = function (blob) {
      return {
        blob: blob,
        url: URL.createObjectURL(blob)
      };
    };
    var makeOpen = function (editor, imageUploadTimerState) {
      return function () {
        var getLoadedSpec = function (currentState) {
          return {
            title: 'Edit Image',
            size: 'large',
            body: {
              type: 'panel',
              items: [{
                  type: 'imagetools',
                  name: 'imagetools',
                  label: 'Edit Image',
                  currentState: currentState
                }]
            },
            buttons: [
              {
                type: 'cancel',
                name: 'cancel',
                text: 'Cancel'
              },
              {
                type: 'submit',
                name: 'save',
                text: 'Save',
                primary: true,
                disabled: true
              }
            ],
            onSubmit: function (api) {
              var blob = api.getData().imagetools.blob;
              originalImgOpt.each(function (originalImg) {
                originalSizeOpt.each(function (originalSize) {
                  handleDialogBlob(editor, imageUploadTimerState, originalImg.dom, originalSize, blob);
                });
              });
              api.close();
            },
            onCancel: noop,
            onAction: function (api, details) {
              switch (details.name) {
              case saveState:
                if (details.value) {
                  api.enable('save');
                } else {
                  api.disable('save');
                }
                break;
              case disable:
                api.disable('save');
                api.disable('cancel');
                break;
              case enable:
                api.enable('cancel');
                break;
              }
            }
          };
        };
        var originalImgOpt = getSelectedImage(editor);
        var originalSizeOpt = originalImgOpt.map(function (origImg) {
          return getNaturalImageSize(origImg.dom);
        });
        originalImgOpt.each(function (img) {
          getEditableImage(editor, img.dom).each(function (_) {
            findBlob(editor, img.dom).then(function (blob) {
              var state = createState(blob);
              editor.windowManager.open(getLoadedSpec(state));
            });
          });
        });
      };
    };

    var register$2 = function (editor, imageUploadTimerState) {
      global$4.each({
        mceImageRotateLeft: rotate(editor, imageUploadTimerState, -90),
        mceImageRotateRight: rotate(editor, imageUploadTimerState, 90),
        mceImageFlipVertical: flip(editor, imageUploadTimerState, 'v'),
        mceImageFlipHorizontal: flip(editor, imageUploadTimerState, 'h'),
        mceEditImage: makeOpen(editor, imageUploadTimerState)
      }, function (fn, cmd) {
        editor.addCommand(cmd, fn);
      });
    };

    var setup = function (editor, imageUploadTimerState, lastSelectedImageState) {
      editor.on('NodeChange', function (e) {
        var lastSelectedImage = lastSelectedImageState.get();
        var selectedImage = getEditableImage(editor, e.element);
        if (lastSelectedImage && !selectedImage.exists(function (img) {
            return lastSelectedImage.src === img.src;
          })) {
          cancelTimedUpload(imageUploadTimerState);
          editor.editorUpload.uploadImagesAuto();
          lastSelectedImageState.set(null);
        }
        selectedImage.each(lastSelectedImageState.set);
      });
    };

    var register$1 = function (editor) {
      var changeHandlers = [];
      var cmd = function (command) {
        return function () {
          return editor.execCommand(command);
        };
      };
      var isEditableImage = function () {
        return getSelectedImage(editor).exists(function (element) {
          return getEditableImage(editor, element.dom).isSome();
        });
      };
      var onSetup = function (api) {
        var handler = function (isEditableImage) {
          return api.setDisabled(!isEditableImage);
        };
        handler(isEditableImage());
        changeHandlers = changeHandlers.concat([handler]);
        return function () {
          changeHandlers = filter(changeHandlers, function (h) {
            return h !== handler;
          });
        };
      };
      editor.on('NodeChange', function () {
        var isEditable = isEditableImage();
        each$1(changeHandlers, function (handler) {
          return handler(isEditable);
        });
      });
      editor.ui.registry.addButton('rotateleft', {
        tooltip: 'Rotate counterclockwise',
        icon: 'rotate-left',
        onAction: cmd('mceImageRotateLeft'),
        onSetup: onSetup
      });
      editor.ui.registry.addButton('rotateright', {
        tooltip: 'Rotate clockwise',
        icon: 'rotate-right',
        onAction: cmd('mceImageRotateRight'),
        onSetup: onSetup
      });
      editor.ui.registry.addButton('flipv', {
        tooltip: 'Flip vertically',
        icon: 'flip-vertically',
        onAction: cmd('mceImageFlipVertical'),
        onSetup: onSetup
      });
      editor.ui.registry.addButton('fliph', {
        tooltip: 'Flip horizontally',
        icon: 'flip-horizontally',
        onAction: cmd('mceImageFlipHorizontal'),
        onSetup: onSetup
      });
      editor.ui.registry.addButton('editimage', {
        tooltip: 'Edit image',
        icon: 'edit-image',
        onAction: cmd('mceEditImage'),
        onSetup: onSetup
      });
      editor.ui.registry.addButton('imageoptions', {
        tooltip: 'Image options',
        icon: 'image',
        onAction: cmd('mceImage')
      });
      editor.ui.registry.addContextMenu('imagetools', {
        update: function (element) {
          return getEditableImage(editor, element).map(function (_) {
            return {
              text: 'Edit image',
              icon: 'edit-image',
              onAction: cmd('mceEditImage')
            };
          }).toArray();
        }
      });
    };

    var register = function (editor) {
      editor.ui.registry.addContextToolbar('imagetools', {
        items: getToolbarItems(editor),
        predicate: function (elem) {
          return getEditableImage(editor, elem).isSome();
        },
        position: 'node',
        scope: 'node'
      });
    };

    function Plugin () {
      global$5.add('imagetools', function (editor) {
        var imageUploadTimerState = Cell(0);
        var lastSelectedImageState = Cell(null);
        register$2(editor, imageUploadTimerState);
        register$1(editor);
        register(editor);
        setup(editor, imageUploadTimerState, lastSelectedImageState);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/link/index.js":
/*!****************************************************!*\
  !*** ./node_modules/tinymce/plugins/link/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "link" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/link')
//   ES2015:
//     import 'tinymce/plugins/link'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/link/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/link/plugin.js":
/*!*****************************************************!*\
  !*** ./node_modules/tinymce/plugins/link/plugin.js ***!
  \*****************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$7 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var global$6 = tinymce.util.Tools.resolve('tinymce.util.VK');

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var eq = function (t) {
      return function (a) {
        return t === a;
      };
    };
    var isString = isType('string');
    var isArray = isType('array');
    var isNull = eq(null);
    var isBoolean = isSimpleType('boolean');
    var isFunction = isSimpleType('function');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var tripleEquals = function (a, b) {
      return a === b;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var nativeIndexOf = Array.prototype.indexOf;
    var nativePush = Array.prototype.push;
    var rawIndexOf = function (ts, t) {
      return nativeIndexOf.call(ts, t);
    };
    var contains = function (xs, x) {
      return rawIndexOf(xs, x) > -1;
    };
    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each$1 = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var foldl = function (xs, f, acc) {
      each$1(xs, function (x, i) {
        acc = f(acc, x, i);
      });
      return acc;
    };
    var flatten = function (xs) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; ++i) {
        if (!isArray(xs[i])) {
          throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
        }
        nativePush.apply(r, xs[i]);
      }
      return r;
    };
    var bind = function (xs, f) {
      return flatten(map(xs, f));
    };
    var findMap = function (arr, f) {
      for (var i = 0; i < arr.length; i++) {
        var r = f(arr[i], i);
        if (r.isSome()) {
          return r;
        }
      }
      return Optional.none();
    };

    var is = function (lhs, rhs, comparator) {
      if (comparator === void 0) {
        comparator = tripleEquals;
      }
      return lhs.exists(function (left) {
        return comparator(left, rhs);
      });
    };
    var cat = function (arr) {
      var r = [];
      var push = function (x) {
        r.push(x);
      };
      for (var i = 0; i < arr.length; i++) {
        arr[i].each(push);
      }
      return r;
    };
    var someIf = function (b, a) {
      return b ? Optional.some(a) : Optional.none();
    };

    var assumeExternalTargets = function (editor) {
      var externalTargets = editor.getParam('link_assume_external_targets', false);
      if (isBoolean(externalTargets) && externalTargets) {
        return 1;
      } else if (isString(externalTargets) && (externalTargets === 'http' || externalTargets === 'https')) {
        return externalTargets;
      }
      return 0;
    };
    var hasContextToolbar = function (editor) {
      return editor.getParam('link_context_toolbar', false, 'boolean');
    };
    var getLinkList = function (editor) {
      return editor.getParam('link_list');
    };
    var getDefaultLinkTarget = function (editor) {
      return editor.getParam('default_link_target');
    };
    var getTargetList = function (editor) {
      return editor.getParam('target_list', true);
    };
    var getRelList = function (editor) {
      return editor.getParam('rel_list', [], 'array');
    };
    var getLinkClassList = function (editor) {
      return editor.getParam('link_class_list', [], 'array');
    };
    var shouldShowLinkTitle = function (editor) {
      return editor.getParam('link_title', true, 'boolean');
    };
    var allowUnsafeLinkTarget = function (editor) {
      return editor.getParam('allow_unsafe_link_target', false, 'boolean');
    };
    var useQuickLink = function (editor) {
      return editor.getParam('link_quicklink', false, 'boolean');
    };
    var getDefaultLinkProtocol = function (editor) {
      return editor.getParam('link_default_protocol', 'http', 'string');
    };

    var global$5 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var getValue = function (item) {
      return isString(item.value) ? item.value : '';
    };
    var getText = function (item) {
      if (isString(item.text)) {
        return item.text;
      } else if (isString(item.title)) {
        return item.title;
      } else {
        return '';
      }
    };
    var sanitizeList = function (list, extractValue) {
      var out = [];
      global$5.each(list, function (item) {
        var text = getText(item);
        if (item.menu !== undefined) {
          var items = sanitizeList(item.menu, extractValue);
          out.push({
            text: text,
            items: items
          });
        } else {
          var value = extractValue(item);
          out.push({
            text: text,
            value: value
          });
        }
      });
      return out;
    };
    var sanitizeWith = function (extracter) {
      if (extracter === void 0) {
        extracter = getValue;
      }
      return function (list) {
        return Optional.from(list).map(function (list) {
          return sanitizeList(list, extracter);
        });
      };
    };
    var sanitize = function (list) {
      return sanitizeWith(getValue)(list);
    };
    var createUi = function (name, label) {
      return function (items) {
        return {
          name: name,
          type: 'listbox',
          label: label,
          items: items
        };
      };
    };
    var ListOptions = {
      sanitize: sanitize,
      sanitizeWith: sanitizeWith,
      createUi: createUi,
      getValue: getValue
    };

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };

    var keys = Object.keys;
    var hasOwnProperty = Object.hasOwnProperty;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };
    var objAcc = function (r) {
      return function (x, i) {
        r[i] = x;
      };
    };
    var internalFilter = function (obj, pred, onTrue, onFalse) {
      var r = {};
      each(obj, function (x, i) {
        (pred(x, i) ? onTrue : onFalse)(x, i);
      });
      return r;
    };
    var filter = function (obj, pred) {
      var t = {};
      internalFilter(obj, pred, objAcc(t), noop);
      return t;
    };
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };
    var hasNonNullableKey = function (obj, key) {
      return has(obj, key) && obj[key] !== undefined && obj[key] !== null;
    };

    var global$4 = tinymce.util.Tools.resolve('tinymce.dom.TreeWalker');

    var global$3 = tinymce.util.Tools.resolve('tinymce.util.URI');

    var isAnchor = function (elm) {
      return elm && elm.nodeName.toLowerCase() === 'a';
    };
    var isLink = function (elm) {
      return isAnchor(elm) && !!getHref(elm);
    };
    var collectNodesInRange = function (rng, predicate) {
      if (rng.collapsed) {
        return [];
      } else {
        var contents = rng.cloneContents();
        var walker = new global$4(contents.firstChild, contents);
        var elements = [];
        var current = contents.firstChild;
        do {
          if (predicate(current)) {
            elements.push(current);
          }
        } while (current = walker.next());
        return elements;
      }
    };
    var hasProtocol = function (url) {
      return /^\w+:/i.test(url);
    };
    var getHref = function (elm) {
      var href = elm.getAttribute('data-mce-href');
      return href ? href : elm.getAttribute('href');
    };
    var applyRelTargetRules = function (rel, isUnsafe) {
      var rules = ['noopener'];
      var rels = rel ? rel.split(/\s+/) : [];
      var toString = function (rels) {
        return global$5.trim(rels.sort().join(' '));
      };
      var addTargetRules = function (rels) {
        rels = removeTargetRules(rels);
        return rels.length > 0 ? rels.concat(rules) : rules;
      };
      var removeTargetRules = function (rels) {
        return rels.filter(function (val) {
          return global$5.inArray(rules, val) === -1;
        });
      };
      var newRels = isUnsafe ? addTargetRules(rels) : removeTargetRules(rels);
      return newRels.length > 0 ? toString(newRels) : '';
    };
    var trimCaretContainers = function (text) {
      return text.replace(/\uFEFF/g, '');
    };
    var getAnchorElement = function (editor, selectedElm) {
      selectedElm = selectedElm || editor.selection.getNode();
      if (isImageFigure(selectedElm)) {
        return editor.dom.select('a[href]', selectedElm)[0];
      } else {
        return editor.dom.getParent(selectedElm, 'a[href]');
      }
    };
    var getAnchorText = function (selection, anchorElm) {
      var text = anchorElm ? anchorElm.innerText || anchorElm.textContent : selection.getContent({ format: 'text' });
      return trimCaretContainers(text);
    };
    var hasLinks = function (elements) {
      return global$5.grep(elements, isLink).length > 0;
    };
    var hasLinksInSelection = function (rng) {
      return collectNodesInRange(rng, isLink).length > 0;
    };
    var isOnlyTextSelected = function (editor) {
      var inlineTextElements = editor.schema.getTextInlineElements();
      var isElement = function (elm) {
        return elm.nodeType === 1 && !isAnchor(elm) && !has(inlineTextElements, elm.nodeName.toLowerCase());
      };
      var elements = collectNodesInRange(editor.selection.getRng(), isElement);
      return elements.length === 0;
    };
    var isImageFigure = function (elm) {
      return elm && elm.nodeName === 'FIGURE' && /\bimage\b/i.test(elm.className);
    };
    var getLinkAttrs = function (data) {
      var attrs = [
        'title',
        'rel',
        'class',
        'target'
      ];
      return foldl(attrs, function (acc, key) {
        data[key].each(function (value) {
          acc[key] = value.length > 0 ? value : null;
        });
        return acc;
      }, { href: data.href });
    };
    var handleExternalTargets = function (href, assumeExternalTargets) {
      if ((assumeExternalTargets === 'http' || assumeExternalTargets === 'https') && !hasProtocol(href)) {
        return assumeExternalTargets + '://' + href;
      }
      return href;
    };
    var applyLinkOverrides = function (editor, linkAttrs) {
      var newLinkAttrs = __assign({}, linkAttrs);
      if (!(getRelList(editor).length > 0) && allowUnsafeLinkTarget(editor) === false) {
        var newRel = applyRelTargetRules(newLinkAttrs.rel, newLinkAttrs.target === '_blank');
        newLinkAttrs.rel = newRel ? newRel : null;
      }
      if (Optional.from(newLinkAttrs.target).isNone() && getTargetList(editor) === false) {
        newLinkAttrs.target = getDefaultLinkTarget(editor);
      }
      newLinkAttrs.href = handleExternalTargets(newLinkAttrs.href, assumeExternalTargets(editor));
      return newLinkAttrs;
    };
    var updateLink = function (editor, anchorElm, text, linkAttrs) {
      text.each(function (text) {
        if (has(anchorElm, 'innerText')) {
          anchorElm.innerText = text;
        } else {
          anchorElm.textContent = text;
        }
      });
      editor.dom.setAttribs(anchorElm, linkAttrs);
      editor.selection.select(anchorElm);
    };
    var createLink = function (editor, selectedElm, text, linkAttrs) {
      if (isImageFigure(selectedElm)) {
        linkImageFigure(editor, selectedElm, linkAttrs);
      } else {
        text.fold(function () {
          editor.execCommand('mceInsertLink', false, linkAttrs);
        }, function (text) {
          editor.insertContent(editor.dom.createHTML('a', linkAttrs, editor.dom.encode(text)));
        });
      }
    };
    var linkDomMutation = function (editor, attachState, data) {
      var selectedElm = editor.selection.getNode();
      var anchorElm = getAnchorElement(editor, selectedElm);
      var linkAttrs = applyLinkOverrides(editor, getLinkAttrs(data));
      editor.undoManager.transact(function () {
        if (data.href === attachState.href) {
          attachState.attach();
        }
        if (anchorElm) {
          editor.focus();
          updateLink(editor, anchorElm, data.text, linkAttrs);
        } else {
          createLink(editor, selectedElm, data.text, linkAttrs);
        }
      });
    };
    var unlinkSelection = function (editor) {
      var dom = editor.dom, selection = editor.selection;
      var bookmark = selection.getBookmark();
      var rng = selection.getRng().cloneRange();
      var startAnchorElm = dom.getParent(rng.startContainer, 'a[href]', editor.getBody());
      var endAnchorElm = dom.getParent(rng.endContainer, 'a[href]', editor.getBody());
      if (startAnchorElm) {
        rng.setStartBefore(startAnchorElm);
      }
      if (endAnchorElm) {
        rng.setEndAfter(endAnchorElm);
      }
      selection.setRng(rng);
      editor.execCommand('unlink');
      selection.moveToBookmark(bookmark);
    };
    var unlinkDomMutation = function (editor) {
      editor.undoManager.transact(function () {
        var node = editor.selection.getNode();
        if (isImageFigure(node)) {
          unlinkImageFigure(editor, node);
        } else {
          unlinkSelection(editor);
        }
        editor.focus();
      });
    };
    var unwrapOptions = function (data) {
      var cls = data.class, href = data.href, rel = data.rel, target = data.target, text = data.text, title = data.title;
      return filter({
        class: cls.getOrNull(),
        href: href,
        rel: rel.getOrNull(),
        target: target.getOrNull(),
        text: text.getOrNull(),
        title: title.getOrNull()
      }, function (v, _k) {
        return isNull(v) === false;
      });
    };
    var sanitizeData = function (editor, data) {
      var href = data.href;
      return __assign(__assign({}, data), { href: global$3.isDomSafe(href, 'a', editor.settings) ? href : '' });
    };
    var link = function (editor, attachState, data) {
      var sanitizedData = sanitizeData(editor, data);
      editor.hasPlugin('rtc', true) ? editor.execCommand('createlink', false, unwrapOptions(sanitizedData)) : linkDomMutation(editor, attachState, sanitizedData);
    };
    var unlink = function (editor) {
      editor.hasPlugin('rtc', true) ? editor.execCommand('unlink') : unlinkDomMutation(editor);
    };
    var unlinkImageFigure = function (editor, fig) {
      var img = editor.dom.select('img', fig)[0];
      if (img) {
        var a = editor.dom.getParents(img, 'a[href]', fig)[0];
        if (a) {
          a.parentNode.insertBefore(img, a);
          editor.dom.remove(a);
        }
      }
    };
    var linkImageFigure = function (editor, fig, attrs) {
      var img = editor.dom.select('img', fig)[0];
      if (img) {
        var a = editor.dom.create('a', attrs);
        img.parentNode.insertBefore(a, img);
        a.appendChild(img);
      }
    };

    var isListGroup = function (item) {
      return hasNonNullableKey(item, 'items');
    };
    var findTextByValue = function (value, catalog) {
      return findMap(catalog, function (item) {
        if (isListGroup(item)) {
          return findTextByValue(value, item.items);
        } else {
          return someIf(item.value === value, item);
        }
      });
    };
    var getDelta = function (persistentText, fieldName, catalog, data) {
      var value = data[fieldName];
      var hasPersistentText = persistentText.length > 0;
      return value !== undefined ? findTextByValue(value, catalog).map(function (i) {
        return {
          url: {
            value: i.value,
            meta: {
              text: hasPersistentText ? persistentText : i.text,
              attach: noop
            }
          },
          text: hasPersistentText ? persistentText : i.text
        };
      }) : Optional.none();
    };
    var findCatalog = function (catalogs, fieldName) {
      if (fieldName === 'link') {
        return catalogs.link;
      } else if (fieldName === 'anchor') {
        return catalogs.anchor;
      } else {
        return Optional.none();
      }
    };
    var init = function (initialData, linkCatalog) {
      var persistentData = {
        text: initialData.text,
        title: initialData.title
      };
      var getTitleFromUrlChange = function (url) {
        return someIf(persistentData.title.length <= 0, Optional.from(url.meta.title).getOr(''));
      };
      var getTextFromUrlChange = function (url) {
        return someIf(persistentData.text.length <= 0, Optional.from(url.meta.text).getOr(url.value));
      };
      var onUrlChange = function (data) {
        var text = getTextFromUrlChange(data.url);
        var title = getTitleFromUrlChange(data.url);
        if (text.isSome() || title.isSome()) {
          return Optional.some(__assign(__assign({}, text.map(function (text) {
            return { text: text };
          }).getOr({})), title.map(function (title) {
            return { title: title };
          }).getOr({})));
        } else {
          return Optional.none();
        }
      };
      var onCatalogChange = function (data, change) {
        var catalog = findCatalog(linkCatalog, change.name).getOr([]);
        return getDelta(persistentData.text, change.name, catalog, data);
      };
      var onChange = function (getData, change) {
        var name = change.name;
        if (name === 'url') {
          return onUrlChange(getData());
        } else if (contains([
            'anchor',
            'link'
          ], name)) {
          return onCatalogChange(getData(), change);
        } else if (name === 'text' || name === 'title') {
          persistentData[name] = getData()[name];
          return Optional.none();
        } else {
          return Optional.none();
        }
      };
      return { onChange: onChange };
    };
    var DialogChanges = {
      init: init,
      getDelta: getDelta
    };

    var global$2 = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var global$1 = tinymce.util.Tools.resolve('tinymce.util.Promise');

    var delayedConfirm = function (editor, message, callback) {
      var rng = editor.selection.getRng();
      global$2.setEditorTimeout(editor, function () {
        editor.windowManager.confirm(message, function (state) {
          editor.selection.setRng(rng);
          callback(state);
        });
      });
    };
    var tryEmailTransform = function (data) {
      var url = data.href;
      var suggestMailTo = url.indexOf('@') > 0 && url.indexOf('/') === -1 && url.indexOf('mailto:') === -1;
      return suggestMailTo ? Optional.some({
        message: 'The URL you entered seems to be an email address. Do you want to add the required mailto: prefix?',
        preprocess: function (oldData) {
          return __assign(__assign({}, oldData), { href: 'mailto:' + url });
        }
      }) : Optional.none();
    };
    var tryProtocolTransform = function (assumeExternalTargets, defaultLinkProtocol) {
      return function (data) {
        var url = data.href;
        var suggestProtocol = assumeExternalTargets === 1 && !hasProtocol(url) || assumeExternalTargets === 0 && /^\s*www(\.|\d\.)/i.test(url);
        return suggestProtocol ? Optional.some({
          message: 'The URL you entered seems to be an external link. Do you want to add the required ' + defaultLinkProtocol + ':// prefix?',
          preprocess: function (oldData) {
            return __assign(__assign({}, oldData), { href: defaultLinkProtocol + '://' + url });
          }
        }) : Optional.none();
      };
    };
    var preprocess = function (editor, data) {
      return findMap([
        tryEmailTransform,
        tryProtocolTransform(assumeExternalTargets(editor), getDefaultLinkProtocol(editor))
      ], function (f) {
        return f(data);
      }).fold(function () {
        return global$1.resolve(data);
      }, function (transform) {
        return new global$1(function (callback) {
          delayedConfirm(editor, transform.message, function (state) {
            callback(state ? transform.preprocess(data) : data);
          });
        });
      });
    };
    var DialogConfirms = { preprocess: preprocess };

    var getAnchors = function (editor) {
      var anchorNodes = editor.dom.select('a:not([href])');
      var anchors = bind(anchorNodes, function (anchor) {
        var id = anchor.name || anchor.id;
        return id ? [{
            text: id,
            value: '#' + id
          }] : [];
      });
      return anchors.length > 0 ? Optional.some([{
          text: 'None',
          value: ''
        }].concat(anchors)) : Optional.none();
    };
    var AnchorListOptions = { getAnchors: getAnchors };

    var getClasses = function (editor) {
      var list = getLinkClassList(editor);
      if (list.length > 0) {
        return ListOptions.sanitize(list);
      }
      return Optional.none();
    };
    var ClassListOptions = { getClasses: getClasses };

    var global = tinymce.util.Tools.resolve('tinymce.util.XHR');

    var parseJson = function (text) {
      try {
        return Optional.some(JSON.parse(text));
      } catch (err) {
        return Optional.none();
      }
    };
    var getLinks = function (editor) {
      var extractor = function (item) {
        return editor.convertURL(item.value || item.url, 'href');
      };
      var linkList = getLinkList(editor);
      return new global$1(function (callback) {
        if (isString(linkList)) {
          global.send({
            url: linkList,
            success: function (text) {
              return callback(parseJson(text));
            },
            error: function (_) {
              return callback(Optional.none());
            }
          });
        } else if (isFunction(linkList)) {
          linkList(function (output) {
            return callback(Optional.some(output));
          });
        } else {
          callback(Optional.from(linkList));
        }
      }).then(function (optItems) {
        return optItems.bind(ListOptions.sanitizeWith(extractor)).map(function (items) {
          if (items.length > 0) {
            var noneItem = [{
                text: 'None',
                value: ''
              }];
            return noneItem.concat(items);
          } else {
            return items;
          }
        });
      });
    };
    var LinkListOptions = { getLinks: getLinks };

    var getRels = function (editor, initialTarget) {
      var list = getRelList(editor);
      if (list.length > 0) {
        var isTargetBlank_1 = is(initialTarget, '_blank');
        var enforceSafe = allowUnsafeLinkTarget(editor) === false;
        var safeRelExtractor = function (item) {
          return applyRelTargetRules(ListOptions.getValue(item), isTargetBlank_1);
        };
        var sanitizer = enforceSafe ? ListOptions.sanitizeWith(safeRelExtractor) : ListOptions.sanitize;
        return sanitizer(list);
      }
      return Optional.none();
    };
    var RelOptions = { getRels: getRels };

    var fallbacks = [
      {
        text: 'Current window',
        value: ''
      },
      {
        text: 'New window',
        value: '_blank'
      }
    ];
    var getTargets = function (editor) {
      var list = getTargetList(editor);
      if (isArray(list)) {
        return ListOptions.sanitize(list).orThunk(function () {
          return Optional.some(fallbacks);
        });
      } else if (list === false) {
        return Optional.none();
      }
      return Optional.some(fallbacks);
    };
    var TargetOptions = { getTargets: getTargets };

    var nonEmptyAttr = function (dom, elem, name) {
      var val = dom.getAttrib(elem, name);
      return val !== null && val.length > 0 ? Optional.some(val) : Optional.none();
    };
    var extractFromAnchor = function (editor, anchor) {
      var dom = editor.dom;
      var onlyText = isOnlyTextSelected(editor);
      var text = onlyText ? Optional.some(getAnchorText(editor.selection, anchor)) : Optional.none();
      var url = anchor ? Optional.some(dom.getAttrib(anchor, 'href')) : Optional.none();
      var target = anchor ? Optional.from(dom.getAttrib(anchor, 'target')) : Optional.none();
      var rel = nonEmptyAttr(dom, anchor, 'rel');
      var linkClass = nonEmptyAttr(dom, anchor, 'class');
      var title = nonEmptyAttr(dom, anchor, 'title');
      return {
        url: url,
        text: text,
        title: title,
        target: target,
        rel: rel,
        linkClass: linkClass
      };
    };
    var collect = function (editor, linkNode) {
      return LinkListOptions.getLinks(editor).then(function (links) {
        var anchor = extractFromAnchor(editor, linkNode);
        return {
          anchor: anchor,
          catalogs: {
            targets: TargetOptions.getTargets(editor),
            rels: RelOptions.getRels(editor, anchor.target),
            classes: ClassListOptions.getClasses(editor),
            anchor: AnchorListOptions.getAnchors(editor),
            link: links
          },
          optNode: Optional.from(linkNode),
          flags: { titleEnabled: shouldShowLinkTitle(editor) }
        };
      });
    };
    var DialogInfo = { collect: collect };

    var handleSubmit = function (editor, info) {
      return function (api) {
        var data = api.getData();
        if (!data.url.value) {
          unlink(editor);
          api.close();
          return;
        }
        var getChangedValue = function (key) {
          return Optional.from(data[key]).filter(function (value) {
            return !is(info.anchor[key], value);
          });
        };
        var changedData = {
          href: data.url.value,
          text: getChangedValue('text'),
          target: getChangedValue('target'),
          rel: getChangedValue('rel'),
          class: getChangedValue('linkClass'),
          title: getChangedValue('title')
        };
        var attachState = {
          href: data.url.value,
          attach: data.url.meta !== undefined && data.url.meta.attach ? data.url.meta.attach : noop
        };
        DialogConfirms.preprocess(editor, changedData).then(function (pData) {
          link(editor, attachState, pData);
        });
        api.close();
      };
    };
    var collectData = function (editor) {
      var anchorNode = getAnchorElement(editor);
      return DialogInfo.collect(editor, anchorNode);
    };
    var getInitialData = function (info, defaultTarget) {
      var anchor = info.anchor;
      var url = anchor.url.getOr('');
      return {
        url: {
          value: url,
          meta: { original: { value: url } }
        },
        text: anchor.text.getOr(''),
        title: anchor.title.getOr(''),
        anchor: url,
        link: url,
        rel: anchor.rel.getOr(''),
        target: anchor.target.or(defaultTarget).getOr(''),
        linkClass: anchor.linkClass.getOr('')
      };
    };
    var makeDialog = function (settings, onSubmit, editor) {
      var urlInput = [{
          name: 'url',
          type: 'urlinput',
          filetype: 'file',
          label: 'URL'
        }];
      var displayText = settings.anchor.text.map(function () {
        return {
          name: 'text',
          type: 'input',
          label: 'Text to display'
        };
      }).toArray();
      var titleText = settings.flags.titleEnabled ? [{
          name: 'title',
          type: 'input',
          label: 'Title'
        }] : [];
      var defaultTarget = Optional.from(getDefaultLinkTarget(editor));
      var initialData = getInitialData(settings, defaultTarget);
      var catalogs = settings.catalogs;
      var dialogDelta = DialogChanges.init(initialData, catalogs);
      var body = {
        type: 'panel',
        items: flatten([
          urlInput,
          displayText,
          titleText,
          cat([
            catalogs.anchor.map(ListOptions.createUi('anchor', 'Anchors')),
            catalogs.rels.map(ListOptions.createUi('rel', 'Rel')),
            catalogs.targets.map(ListOptions.createUi('target', 'Open link in...')),
            catalogs.link.map(ListOptions.createUi('link', 'Link list')),
            catalogs.classes.map(ListOptions.createUi('linkClass', 'Class'))
          ])
        ])
      };
      return {
        title: 'Insert/Edit Link',
        size: 'normal',
        body: body,
        buttons: [
          {
            type: 'cancel',
            name: 'cancel',
            text: 'Cancel'
          },
          {
            type: 'submit',
            name: 'save',
            text: 'Save',
            primary: true
          }
        ],
        initialData: initialData,
        onChange: function (api, _a) {
          var name = _a.name;
          dialogDelta.onChange(api.getData, { name: name }).each(function (newData) {
            api.setData(newData);
          });
        },
        onSubmit: onSubmit
      };
    };
    var open$1 = function (editor) {
      var data = collectData(editor);
      data.then(function (info) {
        var onSubmit = handleSubmit(editor, info);
        return makeDialog(info, onSubmit, editor);
      }).then(function (spec) {
        editor.windowManager.open(spec);
      });
    };

    var appendClickRemove = function (link, evt) {
      document.body.appendChild(link);
      link.dispatchEvent(evt);
      document.body.removeChild(link);
    };
    var open = function (url) {
      var link = document.createElement('a');
      link.target = '_blank';
      link.href = url;
      link.rel = 'noreferrer noopener';
      var evt = document.createEvent('MouseEvents');
      evt.initMouseEvent('click', true, true, window, 0, 0, 0, 0, 0, false, false, false, false, 0, null);
      appendClickRemove(link, evt);
    };

    var getLink = function (editor, elm) {
      return editor.dom.getParent(elm, 'a[href]');
    };
    var getSelectedLink = function (editor) {
      return getLink(editor, editor.selection.getStart());
    };
    var hasOnlyAltModifier = function (e) {
      return e.altKey === true && e.shiftKey === false && e.ctrlKey === false && e.metaKey === false;
    };
    var gotoLink = function (editor, a) {
      if (a) {
        var href = getHref(a);
        if (/^#/.test(href)) {
          var targetEl = editor.$(href);
          if (targetEl.length) {
            editor.selection.scrollIntoView(targetEl[0], true);
          }
        } else {
          open(a.href);
        }
      }
    };
    var openDialog = function (editor) {
      return function () {
        open$1(editor);
      };
    };
    var gotoSelectedLink = function (editor) {
      return function () {
        gotoLink(editor, getSelectedLink(editor));
      };
    };
    var setupGotoLinks = function (editor) {
      editor.on('click', function (e) {
        var link = getLink(editor, e.target);
        if (link && global$6.metaKeyPressed(e)) {
          e.preventDefault();
          gotoLink(editor, link);
        }
      });
      editor.on('keydown', function (e) {
        var link = getSelectedLink(editor);
        if (link && e.keyCode === 13 && hasOnlyAltModifier(e)) {
          e.preventDefault();
          gotoLink(editor, link);
        }
      });
    };
    var toggleState = function (editor, toggler) {
      editor.on('NodeChange', toggler);
      return function () {
        return editor.off('NodeChange', toggler);
      };
    };
    var toggleActiveState = function (editor) {
      return function (api) {
        var updateState = function () {
          return api.setActive(!editor.mode.isReadOnly() && getAnchorElement(editor, editor.selection.getNode()) !== null);
        };
        updateState();
        return toggleState(editor, updateState);
      };
    };
    var toggleEnabledState = function (editor) {
      return function (api) {
        var updateState = function () {
          return api.setDisabled(getAnchorElement(editor, editor.selection.getNode()) === null);
        };
        updateState();
        return toggleState(editor, updateState);
      };
    };
    var toggleUnlinkState = function (editor) {
      return function (api) {
        var hasLinks$1 = function (parents) {
          return hasLinks(parents) || hasLinksInSelection(editor.selection.getRng());
        };
        var parents = editor.dom.getParents(editor.selection.getStart());
        api.setDisabled(!hasLinks$1(parents));
        return toggleState(editor, function (e) {
          return api.setDisabled(!hasLinks$1(e.parents));
        });
      };
    };

    var register = function (editor) {
      editor.addCommand('mceLink', function () {
        if (useQuickLink(editor)) {
          editor.fire('contexttoolbar-show', { toolbarKey: 'quicklink' });
        } else {
          openDialog(editor)();
        }
      });
    };

    var setup = function (editor) {
      editor.addShortcut('Meta+K', '', function () {
        editor.execCommand('mceLink');
      });
    };

    var setupButtons = function (editor) {
      editor.ui.registry.addToggleButton('link', {
        icon: 'link',
        tooltip: 'Insert/edit link',
        onAction: openDialog(editor),
        onSetup: toggleActiveState(editor)
      });
      editor.ui.registry.addButton('openlink', {
        icon: 'new-tab',
        tooltip: 'Open link',
        onAction: gotoSelectedLink(editor),
        onSetup: toggleEnabledState(editor)
      });
      editor.ui.registry.addButton('unlink', {
        icon: 'unlink',
        tooltip: 'Remove link',
        onAction: function () {
          return unlink(editor);
        },
        onSetup: toggleUnlinkState(editor)
      });
    };
    var setupMenuItems = function (editor) {
      editor.ui.registry.addMenuItem('openlink', {
        text: 'Open link',
        icon: 'new-tab',
        onAction: gotoSelectedLink(editor),
        onSetup: toggleEnabledState(editor)
      });
      editor.ui.registry.addMenuItem('link', {
        icon: 'link',
        text: 'Link...',
        shortcut: 'Meta+K',
        onAction: openDialog(editor)
      });
      editor.ui.registry.addMenuItem('unlink', {
        icon: 'unlink',
        text: 'Remove link',
        onAction: function () {
          return unlink(editor);
        },
        onSetup: toggleUnlinkState(editor)
      });
    };
    var setupContextMenu = function (editor) {
      var inLink = 'link unlink openlink';
      var noLink = 'link';
      editor.ui.registry.addContextMenu('link', {
        update: function (element) {
          return hasLinks(editor.dom.getParents(element, 'a')) ? inLink : noLink;
        }
      });
    };
    var setupContextToolbars = function (editor) {
      var collapseSelectionToEnd = function (editor) {
        editor.selection.collapse(false);
      };
      var onSetupLink = function (buttonApi) {
        var node = editor.selection.getNode();
        buttonApi.setDisabled(!getAnchorElement(editor, node));
        return noop;
      };
      var getLinkText = function (value) {
        var anchor = getAnchorElement(editor);
        var onlyText = isOnlyTextSelected(editor);
        if (!anchor && onlyText) {
          var text = getAnchorText(editor.selection, anchor);
          return Optional.some(text.length > 0 ? text : value);
        } else {
          return Optional.none();
        }
      };
      editor.ui.registry.addContextForm('quicklink', {
        launch: {
          type: 'contextformtogglebutton',
          icon: 'link',
          tooltip: 'Link',
          onSetup: toggleActiveState(editor)
        },
        label: 'Link',
        predicate: function (node) {
          return !!getAnchorElement(editor, node) && hasContextToolbar(editor);
        },
        initValue: function () {
          var elm = getAnchorElement(editor);
          return !!elm ? getHref(elm) : '';
        },
        commands: [
          {
            type: 'contextformtogglebutton',
            icon: 'link',
            tooltip: 'Link',
            primary: true,
            onSetup: function (buttonApi) {
              var node = editor.selection.getNode();
              buttonApi.setActive(!!getAnchorElement(editor, node));
              return toggleActiveState(editor)(buttonApi);
            },
            onAction: function (formApi) {
              var value = formApi.getValue();
              var text = getLinkText(value);
              var attachState = {
                href: value,
                attach: noop
              };
              link(editor, attachState, {
                href: value,
                text: text,
                title: Optional.none(),
                rel: Optional.none(),
                target: Optional.none(),
                class: Optional.none()
              });
              collapseSelectionToEnd(editor);
              formApi.hide();
            }
          },
          {
            type: 'contextformbutton',
            icon: 'unlink',
            tooltip: 'Remove link',
            onSetup: onSetupLink,
            onAction: function (formApi) {
              unlink(editor);
              formApi.hide();
            }
          },
          {
            type: 'contextformbutton',
            icon: 'new-tab',
            tooltip: 'Open link',
            onSetup: onSetupLink,
            onAction: function (formApi) {
              gotoSelectedLink(editor)();
              formApi.hide();
            }
          }
        ]
      });
    };

    function Plugin () {
      global$7.add('link', function (editor) {
        setupButtons(editor);
        setupMenuItems(editor);
        setupContextMenu(editor);
        setupContextToolbars(editor);
        setupGotoLinks(editor);
        register(editor);
        setup(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/lists/index.js":
/*!*****************************************************!*\
  !*** ./node_modules/tinymce/plugins/lists/index.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "lists" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/lists')
//   ES2015:
//     import 'tinymce/plugins/lists'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/lists/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/lists/plugin.js":
/*!******************************************************!*\
  !*** ./node_modules/tinymce/plugins/lists/plugin.js ***!
  \******************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$7 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType$1 = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var isString = isType$1('string');
    var isObject = isType$1('object');
    var isArray = isType$1('array');
    var isBoolean = isSimpleType('boolean');
    var isFunction = isSimpleType('function');
    var isNumber = isSimpleType('number');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var tripleEquals = function (a, b) {
      return a === b;
    };
    var not = function (f) {
      return function (t) {
        return !f(t);
      };
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var nativeSlice = Array.prototype.slice;
    var nativePush = Array.prototype.push;
    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each$1 = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var filter$1 = function (xs, pred) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          r.push(x);
        }
      }
      return r;
    };
    var groupBy = function (xs, f) {
      if (xs.length === 0) {
        return [];
      } else {
        var wasType = f(xs[0]);
        var r = [];
        var group = [];
        for (var i = 0, len = xs.length; i < len; i++) {
          var x = xs[i];
          var type = f(x);
          if (type !== wasType) {
            r.push(group);
            group = [];
          }
          wasType = type;
          group.push(x);
        }
        if (group.length !== 0) {
          r.push(group);
        }
        return r;
      }
    };
    var foldl = function (xs, f, acc) {
      each$1(xs, function (x, i) {
        acc = f(acc, x, i);
      });
      return acc;
    };
    var findUntil = function (xs, pred, until) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          return Optional.some(x);
        } else if (until(x, i)) {
          break;
        }
      }
      return Optional.none();
    };
    var find$1 = function (xs, pred) {
      return findUntil(xs, pred, never);
    };
    var flatten = function (xs) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; ++i) {
        if (!isArray(xs[i])) {
          throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
        }
        nativePush.apply(r, xs[i]);
      }
      return r;
    };
    var bind = function (xs, f) {
      return flatten(map(xs, f));
    };
    var reverse = function (xs) {
      var r = nativeSlice.call(xs, 0);
      r.reverse();
      return r;
    };
    var get$1 = function (xs, i) {
      return i >= 0 && i < xs.length ? Optional.some(xs[i]) : Optional.none();
    };
    var head = function (xs) {
      return get$1(xs, 0);
    };
    var last = function (xs) {
      return get$1(xs, xs.length - 1);
    };
    var findMap = function (arr, f) {
      for (var i = 0; i < arr.length; i++) {
        var r = f(arr[i], i);
        if (r.isSome()) {
          return r;
        }
      }
      return Optional.none();
    };

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };
    function __spreadArray(to, from, pack) {
      if (pack || arguments.length === 2)
        for (var i = 0, l = from.length, ar; i < l; i++) {
          if (ar || !(i in from)) {
            if (!ar)
              ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
          }
        }
      return to.concat(ar || Array.prototype.slice.call(from));
    }

    var cached = function (f) {
      var called = false;
      var r;
      return function () {
        var args = [];
        for (var _i = 0; _i < arguments.length; _i++) {
          args[_i] = arguments[_i];
        }
        if (!called) {
          called = true;
          r = f.apply(null, args);
        }
        return r;
      };
    };

    var DeviceType = function (os, browser, userAgent, mediaMatch) {
      var isiPad = os.isiOS() && /ipad/i.test(userAgent) === true;
      var isiPhone = os.isiOS() && !isiPad;
      var isMobile = os.isiOS() || os.isAndroid();
      var isTouch = isMobile || mediaMatch('(pointer:coarse)');
      var isTablet = isiPad || !isiPhone && isMobile && mediaMatch('(min-device-width:768px)');
      var isPhone = isiPhone || isMobile && !isTablet;
      var iOSwebview = browser.isSafari() && os.isiOS() && /safari/i.test(userAgent) === false;
      var isDesktop = !isPhone && !isTablet && !iOSwebview;
      return {
        isiPad: constant(isiPad),
        isiPhone: constant(isiPhone),
        isTablet: constant(isTablet),
        isPhone: constant(isPhone),
        isTouch: constant(isTouch),
        isAndroid: os.isAndroid,
        isiOS: os.isiOS,
        isWebView: constant(iOSwebview),
        isDesktop: constant(isDesktop)
      };
    };

    var firstMatch = function (regexes, s) {
      for (var i = 0; i < regexes.length; i++) {
        var x = regexes[i];
        if (x.test(s)) {
          return x;
        }
      }
      return undefined;
    };
    var find = function (regexes, agent) {
      var r = firstMatch(regexes, agent);
      if (!r) {
        return {
          major: 0,
          minor: 0
        };
      }
      var group = function (i) {
        return Number(agent.replace(r, '$' + i));
      };
      return nu$2(group(1), group(2));
    };
    var detect$3 = function (versionRegexes, agent) {
      var cleanedAgent = String(agent).toLowerCase();
      if (versionRegexes.length === 0) {
        return unknown$2();
      }
      return find(versionRegexes, cleanedAgent);
    };
    var unknown$2 = function () {
      return nu$2(0, 0);
    };
    var nu$2 = function (major, minor) {
      return {
        major: major,
        minor: minor
      };
    };
    var Version = {
      nu: nu$2,
      detect: detect$3,
      unknown: unknown$2
    };

    var detectBrowser$1 = function (browsers, userAgentData) {
      return findMap(userAgentData.brands, function (uaBrand) {
        var lcBrand = uaBrand.brand.toLowerCase();
        return find$1(browsers, function (browser) {
          var _a;
          return lcBrand === ((_a = browser.brand) === null || _a === void 0 ? void 0 : _a.toLowerCase());
        }).map(function (info) {
          return {
            current: info.name,
            version: Version.nu(parseInt(uaBrand.version, 10), 0)
          };
        });
      });
    };

    var detect$2 = function (candidates, userAgent) {
      var agent = String(userAgent).toLowerCase();
      return find$1(candidates, function (candidate) {
        return candidate.search(agent);
      });
    };
    var detectBrowser = function (browsers, userAgent) {
      return detect$2(browsers, userAgent).map(function (browser) {
        var version = Version.detect(browser.versionRegexes, userAgent);
        return {
          current: browser.name,
          version: version
        };
      });
    };
    var detectOs = function (oses, userAgent) {
      return detect$2(oses, userAgent).map(function (os) {
        var version = Version.detect(os.versionRegexes, userAgent);
        return {
          current: os.name,
          version: version
        };
      });
    };

    var contains$1 = function (str, substr) {
      return str.indexOf(substr) !== -1;
    };
    var blank = function (r) {
      return function (s) {
        return s.replace(r, '');
      };
    };
    var trim = blank(/^\s+|\s+$/g);
    var isNotEmpty = function (s) {
      return s.length > 0;
    };
    var isEmpty$1 = function (s) {
      return !isNotEmpty(s);
    };

    var normalVersionRegex = /.*?version\/\ ?([0-9]+)\.([0-9]+).*/;
    var checkContains = function (target) {
      return function (uastring) {
        return contains$1(uastring, target);
      };
    };
    var browsers = [
      {
        name: 'Edge',
        versionRegexes: [/.*?edge\/ ?([0-9]+)\.([0-9]+)$/],
        search: function (uastring) {
          return contains$1(uastring, 'edge/') && contains$1(uastring, 'chrome') && contains$1(uastring, 'safari') && contains$1(uastring, 'applewebkit');
        }
      },
      {
        name: 'Chrome',
        brand: 'Chromium',
        versionRegexes: [
          /.*?chrome\/([0-9]+)\.([0-9]+).*/,
          normalVersionRegex
        ],
        search: function (uastring) {
          return contains$1(uastring, 'chrome') && !contains$1(uastring, 'chromeframe');
        }
      },
      {
        name: 'IE',
        versionRegexes: [
          /.*?msie\ ?([0-9]+)\.([0-9]+).*/,
          /.*?rv:([0-9]+)\.([0-9]+).*/
        ],
        search: function (uastring) {
          return contains$1(uastring, 'msie') || contains$1(uastring, 'trident');
        }
      },
      {
        name: 'Opera',
        versionRegexes: [
          normalVersionRegex,
          /.*?opera\/([0-9]+)\.([0-9]+).*/
        ],
        search: checkContains('opera')
      },
      {
        name: 'Firefox',
        versionRegexes: [/.*?firefox\/\ ?([0-9]+)\.([0-9]+).*/],
        search: checkContains('firefox')
      },
      {
        name: 'Safari',
        versionRegexes: [
          normalVersionRegex,
          /.*?cpu os ([0-9]+)_([0-9]+).*/
        ],
        search: function (uastring) {
          return (contains$1(uastring, 'safari') || contains$1(uastring, 'mobile/')) && contains$1(uastring, 'applewebkit');
        }
      }
    ];
    var oses = [
      {
        name: 'Windows',
        search: checkContains('win'),
        versionRegexes: [/.*?windows\ nt\ ?([0-9]+)\.([0-9]+).*/]
      },
      {
        name: 'iOS',
        search: function (uastring) {
          return contains$1(uastring, 'iphone') || contains$1(uastring, 'ipad');
        },
        versionRegexes: [
          /.*?version\/\ ?([0-9]+)\.([0-9]+).*/,
          /.*cpu os ([0-9]+)_([0-9]+).*/,
          /.*cpu iphone os ([0-9]+)_([0-9]+).*/
        ]
      },
      {
        name: 'Android',
        search: checkContains('android'),
        versionRegexes: [/.*?android\ ?([0-9]+)\.([0-9]+).*/]
      },
      {
        name: 'OSX',
        search: checkContains('mac os x'),
        versionRegexes: [/.*?mac\ os\ x\ ?([0-9]+)_([0-9]+).*/]
      },
      {
        name: 'Linux',
        search: checkContains('linux'),
        versionRegexes: []
      },
      {
        name: 'Solaris',
        search: checkContains('sunos'),
        versionRegexes: []
      },
      {
        name: 'FreeBSD',
        search: checkContains('freebsd'),
        versionRegexes: []
      },
      {
        name: 'ChromeOS',
        search: checkContains('cros'),
        versionRegexes: [/.*?chrome\/([0-9]+)\.([0-9]+).*/]
      }
    ];
    var PlatformInfo = {
      browsers: constant(browsers),
      oses: constant(oses)
    };

    var edge = 'Edge';
    var chrome = 'Chrome';
    var ie = 'IE';
    var opera = 'Opera';
    var firefox = 'Firefox';
    var safari = 'Safari';
    var unknown$1 = function () {
      return nu$1({
        current: undefined,
        version: Version.unknown()
      });
    };
    var nu$1 = function (info) {
      var current = info.current;
      var version = info.version;
      var isBrowser = function (name) {
        return function () {
          return current === name;
        };
      };
      return {
        current: current,
        version: version,
        isEdge: isBrowser(edge),
        isChrome: isBrowser(chrome),
        isIE: isBrowser(ie),
        isOpera: isBrowser(opera),
        isFirefox: isBrowser(firefox),
        isSafari: isBrowser(safari)
      };
    };
    var Browser = {
      unknown: unknown$1,
      nu: nu$1,
      edge: constant(edge),
      chrome: constant(chrome),
      ie: constant(ie),
      opera: constant(opera),
      firefox: constant(firefox),
      safari: constant(safari)
    };

    var windows = 'Windows';
    var ios = 'iOS';
    var android = 'Android';
    var linux = 'Linux';
    var osx = 'OSX';
    var solaris = 'Solaris';
    var freebsd = 'FreeBSD';
    var chromeos = 'ChromeOS';
    var unknown = function () {
      return nu({
        current: undefined,
        version: Version.unknown()
      });
    };
    var nu = function (info) {
      var current = info.current;
      var version = info.version;
      var isOS = function (name) {
        return function () {
          return current === name;
        };
      };
      return {
        current: current,
        version: version,
        isWindows: isOS(windows),
        isiOS: isOS(ios),
        isAndroid: isOS(android),
        isOSX: isOS(osx),
        isLinux: isOS(linux),
        isSolaris: isOS(solaris),
        isFreeBSD: isOS(freebsd),
        isChromeOS: isOS(chromeos)
      };
    };
    var OperatingSystem = {
      unknown: unknown,
      nu: nu,
      windows: constant(windows),
      ios: constant(ios),
      android: constant(android),
      linux: constant(linux),
      osx: constant(osx),
      solaris: constant(solaris),
      freebsd: constant(freebsd),
      chromeos: constant(chromeos)
    };

    var detect$1 = function (userAgent, userAgentDataOpt, mediaMatch) {
      var browsers = PlatformInfo.browsers();
      var oses = PlatformInfo.oses();
      var browser = userAgentDataOpt.bind(function (userAgentData) {
        return detectBrowser$1(browsers, userAgentData);
      }).orThunk(function () {
        return detectBrowser(browsers, userAgent);
      }).fold(Browser.unknown, Browser.nu);
      var os = detectOs(oses, userAgent).fold(OperatingSystem.unknown, OperatingSystem.nu);
      var deviceType = DeviceType(os, browser, userAgent, mediaMatch);
      return {
        browser: browser,
        os: os,
        deviceType: deviceType
      };
    };
    var PlatformDetection = { detect: detect$1 };

    var mediaMatch = function (query) {
      return window.matchMedia(query).matches;
    };
    var platform = cached(function () {
      return PlatformDetection.detect(navigator.userAgent, Optional.from(navigator.userAgentData), mediaMatch);
    });
    var detect = function () {
      return platform();
    };

    var compareDocumentPosition = function (a, b, match) {
      return (a.compareDocumentPosition(b) & match) !== 0;
    };
    var documentPositionContainedBy = function (a, b) {
      return compareDocumentPosition(a, b, Node.DOCUMENT_POSITION_CONTAINED_BY);
    };

    var ELEMENT = 1;

    var fromHtml = function (html, scope) {
      var doc = scope || document;
      var div = doc.createElement('div');
      div.innerHTML = html;
      if (!div.hasChildNodes() || div.childNodes.length > 1) {
        console.error('HTML does not have a single root node', html);
        throw new Error('HTML must have a single root node');
      }
      return fromDom(div.childNodes[0]);
    };
    var fromTag = function (tag, scope) {
      var doc = scope || document;
      var node = doc.createElement(tag);
      return fromDom(node);
    };
    var fromText = function (text, scope) {
      var doc = scope || document;
      var node = doc.createTextNode(text);
      return fromDom(node);
    };
    var fromDom = function (node) {
      if (node === null || node === undefined) {
        throw new Error('Node cannot be null or undefined');
      }
      return { dom: node };
    };
    var fromPoint = function (docElm, x, y) {
      return Optional.from(docElm.dom.elementFromPoint(x, y)).map(fromDom);
    };
    var SugarElement = {
      fromHtml: fromHtml,
      fromTag: fromTag,
      fromText: fromText,
      fromDom: fromDom,
      fromPoint: fromPoint
    };

    var is$2 = function (element, selector) {
      var dom = element.dom;
      if (dom.nodeType !== ELEMENT) {
        return false;
      } else {
        var elem = dom;
        if (elem.matches !== undefined) {
          return elem.matches(selector);
        } else if (elem.msMatchesSelector !== undefined) {
          return elem.msMatchesSelector(selector);
        } else if (elem.webkitMatchesSelector !== undefined) {
          return elem.webkitMatchesSelector(selector);
        } else if (elem.mozMatchesSelector !== undefined) {
          return elem.mozMatchesSelector(selector);
        } else {
          throw new Error('Browser lacks native selectors');
        }
      }
    };

    var eq = function (e1, e2) {
      return e1.dom === e2.dom;
    };
    var regularContains = function (e1, e2) {
      var d1 = e1.dom;
      var d2 = e2.dom;
      return d1 === d2 ? false : d1.contains(d2);
    };
    var ieContains = function (e1, e2) {
      return documentPositionContainedBy(e1.dom, e2.dom);
    };
    var contains = function (e1, e2) {
      return detect().browser.isIE() ? ieContains(e1, e2) : regularContains(e1, e2);
    };
    var is$1 = is$2;

    var global$6 = tinymce.util.Tools.resolve('tinymce.dom.RangeUtils');

    var global$5 = tinymce.util.Tools.resolve('tinymce.dom.TreeWalker');

    var global$4 = tinymce.util.Tools.resolve('tinymce.util.VK');

    var keys = Object.keys;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };
    var objAcc = function (r) {
      return function (x, i) {
        r[i] = x;
      };
    };
    var internalFilter = function (obj, pred, onTrue, onFalse) {
      var r = {};
      each(obj, function (x, i) {
        (pred(x, i) ? onTrue : onFalse)(x, i);
      });
      return r;
    };
    var filter = function (obj, pred) {
      var t = {};
      internalFilter(obj, pred, objAcc(t), noop);
      return t;
    };

    typeof window !== 'undefined' ? window : Function('return this;')();

    var name = function (element) {
      var r = element.dom.nodeName;
      return r.toLowerCase();
    };
    var type = function (element) {
      return element.dom.nodeType;
    };
    var isType = function (t) {
      return function (element) {
        return type(element) === t;
      };
    };
    var isElement = isType(ELEMENT);
    var isTag = function (tag) {
      return function (e) {
        return isElement(e) && name(e) === tag;
      };
    };

    var rawSet = function (dom, key, value) {
      if (isString(value) || isBoolean(value) || isNumber(value)) {
        dom.setAttribute(key, value + '');
      } else {
        console.error('Invalid call to Attribute.set. Key ', key, ':: Value ', value, ':: Element ', dom);
        throw new Error('Attribute value was not simple');
      }
    };
    var setAll = function (element, attrs) {
      var dom = element.dom;
      each(attrs, function (v, k) {
        rawSet(dom, k, v);
      });
    };
    var clone$1 = function (element) {
      return foldl(element.dom.attributes, function (acc, attr) {
        acc[attr.name] = attr.value;
        return acc;
      }, {});
    };

    var parent = function (element) {
      return Optional.from(element.dom.parentNode).map(SugarElement.fromDom);
    };
    var children = function (element) {
      return map(element.dom.childNodes, SugarElement.fromDom);
    };
    var child = function (element, index) {
      var cs = element.dom.childNodes;
      return Optional.from(cs[index]).map(SugarElement.fromDom);
    };
    var firstChild = function (element) {
      return child(element, 0);
    };
    var lastChild = function (element) {
      return child(element, element.dom.childNodes.length - 1);
    };

    var before$1 = function (marker, element) {
      var parent$1 = parent(marker);
      parent$1.each(function (v) {
        v.dom.insertBefore(element.dom, marker.dom);
      });
    };
    var append$1 = function (parent, element) {
      parent.dom.appendChild(element.dom);
    };

    var before = function (marker, elements) {
      each$1(elements, function (x) {
        before$1(marker, x);
      });
    };
    var append = function (parent, elements) {
      each$1(elements, function (x) {
        append$1(parent, x);
      });
    };

    var remove = function (element) {
      var dom = element.dom;
      if (dom.parentNode !== null) {
        dom.parentNode.removeChild(dom);
      }
    };

    var clone = function (original, isDeep) {
      return SugarElement.fromDom(original.dom.cloneNode(isDeep));
    };
    var deep = function (original) {
      return clone(original, true);
    };
    var shallowAs = function (original, tag) {
      var nu = SugarElement.fromTag(tag);
      var attributes = clone$1(original);
      setAll(nu, attributes);
      return nu;
    };
    var mutate = function (original, tag) {
      var nu = shallowAs(original, tag);
      before$1(original, nu);
      var children$1 = children(original);
      append(nu, children$1);
      remove(original);
      return nu;
    };

    var global$3 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

    var global$2 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var matchNodeName = function (name) {
      return function (node) {
        return node && node.nodeName.toLowerCase() === name;
      };
    };
    var matchNodeNames = function (regex) {
      return function (node) {
        return node && regex.test(node.nodeName);
      };
    };
    var isTextNode = function (node) {
      return node && node.nodeType === 3;
    };
    var isListNode = matchNodeNames(/^(OL|UL|DL)$/);
    var isOlUlNode = matchNodeNames(/^(OL|UL)$/);
    var isOlNode = matchNodeName('ol');
    var isListItemNode = matchNodeNames(/^(LI|DT|DD)$/);
    var isDlItemNode = matchNodeNames(/^(DT|DD)$/);
    var isTableCellNode = matchNodeNames(/^(TH|TD)$/);
    var isBr = matchNodeName('br');
    var isFirstChild = function (node) {
      return node.parentNode.firstChild === node;
    };
    var isTextBlock = function (editor, node) {
      return node && !!editor.schema.getTextBlockElements()[node.nodeName];
    };
    var isBlock = function (node, blockElements) {
      return node && node.nodeName in blockElements;
    };
    var isBogusBr = function (dom, node) {
      if (!isBr(node)) {
        return false;
      }
      return dom.isBlock(node.nextSibling) && !isBr(node.previousSibling);
    };
    var isEmpty = function (dom, elm, keepBookmarks) {
      var empty = dom.isEmpty(elm);
      if (keepBookmarks && dom.select('span[data-mce-type=bookmark]', elm).length > 0) {
        return false;
      }
      return empty;
    };
    var isChildOfBody = function (dom, elm) {
      return dom.isChildOf(elm, dom.getRoot());
    };

    var shouldIndentOnTab = function (editor) {
      return editor.getParam('lists_indent_on_tab', true);
    };
    var getForcedRootBlock = function (editor) {
      var block = editor.getParam('forced_root_block', 'p');
      if (block === false) {
        return '';
      } else if (block === true) {
        return 'p';
      } else {
        return block;
      }
    };
    var getForcedRootBlockAttrs = function (editor) {
      return editor.getParam('forced_root_block_attrs', {});
    };

    var createTextBlock = function (editor, contentNode) {
      var dom = editor.dom;
      var blockElements = editor.schema.getBlockElements();
      var fragment = dom.createFragment();
      var blockName = getForcedRootBlock(editor);
      var node, textBlock, hasContentNode;
      if (blockName) {
        textBlock = dom.create(blockName);
        if (textBlock.tagName === blockName.toUpperCase()) {
          dom.setAttribs(textBlock, getForcedRootBlockAttrs(editor));
        }
        if (!isBlock(contentNode.firstChild, blockElements)) {
          fragment.appendChild(textBlock);
        }
      }
      if (contentNode) {
        while (node = contentNode.firstChild) {
          var nodeName = node.nodeName;
          if (!hasContentNode && (nodeName !== 'SPAN' || node.getAttribute('data-mce-type') !== 'bookmark')) {
            hasContentNode = true;
          }
          if (isBlock(node, blockElements)) {
            fragment.appendChild(node);
            textBlock = null;
          } else {
            if (blockName) {
              if (!textBlock) {
                textBlock = dom.create(blockName);
                fragment.appendChild(textBlock);
              }
              textBlock.appendChild(node);
            } else {
              fragment.appendChild(node);
            }
          }
        }
      }
      if (!blockName) {
        fragment.appendChild(dom.create('br'));
      } else {
        if (!hasContentNode) {
          textBlock.appendChild(dom.create('br', { 'data-mce-bogus': '1' }));
        }
      }
      return fragment;
    };

    var DOM$2 = global$3.DOM;
    var splitList = function (editor, list, li) {
      var removeAndKeepBookmarks = function (targetNode) {
        global$2.each(bookmarks, function (node) {
          targetNode.parentNode.insertBefore(node, li.parentNode);
        });
        DOM$2.remove(targetNode);
      };
      var bookmarks = DOM$2.select('span[data-mce-type="bookmark"]', list);
      var newBlock = createTextBlock(editor, li);
      var tmpRng = DOM$2.createRng();
      tmpRng.setStartAfter(li);
      tmpRng.setEndAfter(list);
      var fragment = tmpRng.extractContents();
      for (var node = fragment.firstChild; node; node = node.firstChild) {
        if (node.nodeName === 'LI' && editor.dom.isEmpty(node)) {
          DOM$2.remove(node);
          break;
        }
      }
      if (!editor.dom.isEmpty(fragment)) {
        DOM$2.insertAfter(fragment, list);
      }
      DOM$2.insertAfter(newBlock, list);
      if (isEmpty(editor.dom, li.parentNode)) {
        removeAndKeepBookmarks(li.parentNode);
      }
      DOM$2.remove(li);
      if (isEmpty(editor.dom, list)) {
        DOM$2.remove(list);
      }
    };

    var isDescriptionDetail = isTag('dd');
    var isDescriptionTerm = isTag('dt');
    var outdentDlItem = function (editor, item) {
      if (isDescriptionDetail(item)) {
        mutate(item, 'dt');
      } else if (isDescriptionTerm(item)) {
        parent(item).each(function (dl) {
          return splitList(editor, dl.dom, item.dom);
        });
      }
    };
    var indentDlItem = function (item) {
      if (isDescriptionTerm(item)) {
        mutate(item, 'dd');
      }
    };
    var dlIndentation = function (editor, indentation, dlItems) {
      if (indentation === 'Indent') {
        each$1(dlItems, indentDlItem);
      } else {
        each$1(dlItems, function (item) {
          return outdentDlItem(editor, item);
        });
      }
    };

    var getNormalizedPoint = function (container, offset) {
      if (isTextNode(container)) {
        return {
          container: container,
          offset: offset
        };
      }
      var node = global$6.getNode(container, offset);
      if (isTextNode(node)) {
        return {
          container: node,
          offset: offset >= container.childNodes.length ? node.data.length : 0
        };
      } else if (node.previousSibling && isTextNode(node.previousSibling)) {
        return {
          container: node.previousSibling,
          offset: node.previousSibling.data.length
        };
      } else if (node.nextSibling && isTextNode(node.nextSibling)) {
        return {
          container: node.nextSibling,
          offset: 0
        };
      }
      return {
        container: container,
        offset: offset
      };
    };
    var normalizeRange = function (rng) {
      var outRng = rng.cloneRange();
      var rangeStart = getNormalizedPoint(rng.startContainer, rng.startOffset);
      outRng.setStart(rangeStart.container, rangeStart.offset);
      var rangeEnd = getNormalizedPoint(rng.endContainer, rng.endOffset);
      outRng.setEnd(rangeEnd.container, rangeEnd.offset);
      return outRng;
    };

    var global$1 = tinymce.util.Tools.resolve('tinymce.dom.DomQuery');

    var getParentList = function (editor, node) {
      var selectionStart = node || editor.selection.getStart(true);
      return editor.dom.getParent(selectionStart, 'OL,UL,DL', getClosestListRootElm(editor, selectionStart));
    };
    var isParentListSelected = function (parentList, selectedBlocks) {
      return parentList && selectedBlocks.length === 1 && selectedBlocks[0] === parentList;
    };
    var findSubLists = function (parentList) {
      return filter$1(parentList.querySelectorAll('ol,ul,dl'), isListNode);
    };
    var getSelectedSubLists = function (editor) {
      var parentList = getParentList(editor);
      var selectedBlocks = editor.selection.getSelectedBlocks();
      if (isParentListSelected(parentList, selectedBlocks)) {
        return findSubLists(parentList);
      } else {
        return filter$1(selectedBlocks, function (elm) {
          return isListNode(elm) && parentList !== elm;
        });
      }
    };
    var findParentListItemsNodes = function (editor, elms) {
      var listItemsElms = global$2.map(elms, function (elm) {
        var parentLi = editor.dom.getParent(elm, 'li,dd,dt', getClosestListRootElm(editor, elm));
        return parentLi ? parentLi : elm;
      });
      return global$1.unique(listItemsElms);
    };
    var getSelectedListItems = function (editor) {
      var selectedBlocks = editor.selection.getSelectedBlocks();
      return filter$1(findParentListItemsNodes(editor, selectedBlocks), isListItemNode);
    };
    var getSelectedDlItems = function (editor) {
      return filter$1(getSelectedListItems(editor), isDlItemNode);
    };
    var getClosestListRootElm = function (editor, elm) {
      var parentTableCell = editor.dom.getParents(elm, 'TD,TH');
      return parentTableCell.length > 0 ? parentTableCell[0] : editor.getBody();
    };
    var findLastParentListNode = function (editor, elm) {
      var parentLists = editor.dom.getParents(elm, 'ol,ul', getClosestListRootElm(editor, elm));
      return last(parentLists);
    };
    var getSelectedLists = function (editor) {
      var firstList = findLastParentListNode(editor, editor.selection.getStart());
      var subsequentLists = filter$1(editor.selection.getSelectedBlocks(), isOlUlNode);
      return firstList.toArray().concat(subsequentLists);
    };
    var getSelectedListRoots = function (editor) {
      var selectedLists = getSelectedLists(editor);
      return getUniqueListRoots(editor, selectedLists);
    };
    var getUniqueListRoots = function (editor, lists) {
      var listRoots = map(lists, function (list) {
        return findLastParentListNode(editor, list).getOr(list);
      });
      return global$1.unique(listRoots);
    };

    var is = function (lhs, rhs, comparator) {
      if (comparator === void 0) {
        comparator = tripleEquals;
      }
      return lhs.exists(function (left) {
        return comparator(left, rhs);
      });
    };
    var lift2 = function (oa, ob, f) {
      return oa.isSome() && ob.isSome() ? Optional.some(f(oa.getOrDie(), ob.getOrDie())) : Optional.none();
    };

    var fromElements = function (elements, scope) {
      var doc = scope || document;
      var fragment = doc.createDocumentFragment();
      each$1(elements, function (element) {
        fragment.appendChild(element.dom);
      });
      return SugarElement.fromDom(fragment);
    };

    var fireListEvent = function (editor, action, element) {
      return editor.fire('ListMutation', {
        action: action,
        element: element
      });
    };

    var isSupported = function (dom) {
      return dom.style !== undefined && isFunction(dom.style.getPropertyValue);
    };

    var internalSet = function (dom, property, value) {
      if (!isString(value)) {
        console.error('Invalid call to CSS.set. Property ', property, ':: Value ', value, ':: Element ', dom);
        throw new Error('CSS value must be a string: ' + value);
      }
      if (isSupported(dom)) {
        dom.style.setProperty(property, value);
      }
    };
    var set = function (element, property, value) {
      var dom = element.dom;
      internalSet(dom, property, value);
    };

    var joinSegment = function (parent, child) {
      append$1(parent.item, child.list);
    };
    var joinSegments = function (segments) {
      for (var i = 1; i < segments.length; i++) {
        joinSegment(segments[i - 1], segments[i]);
      }
    };
    var appendSegments = function (head$1, tail) {
      lift2(last(head$1), head(tail), joinSegment);
    };
    var createSegment = function (scope, listType) {
      var segment = {
        list: SugarElement.fromTag(listType, scope),
        item: SugarElement.fromTag('li', scope)
      };
      append$1(segment.list, segment.item);
      return segment;
    };
    var createSegments = function (scope, entry, size) {
      var segments = [];
      for (var i = 0; i < size; i++) {
        segments.push(createSegment(scope, entry.listType));
      }
      return segments;
    };
    var populateSegments = function (segments, entry) {
      for (var i = 0; i < segments.length - 1; i++) {
        set(segments[i].item, 'list-style-type', 'none');
      }
      last(segments).each(function (segment) {
        setAll(segment.list, entry.listAttributes);
        setAll(segment.item, entry.itemAttributes);
        append(segment.item, entry.content);
      });
    };
    var normalizeSegment = function (segment, entry) {
      if (name(segment.list) !== entry.listType) {
        segment.list = mutate(segment.list, entry.listType);
      }
      setAll(segment.list, entry.listAttributes);
    };
    var createItem = function (scope, attr, content) {
      var item = SugarElement.fromTag('li', scope);
      setAll(item, attr);
      append(item, content);
      return item;
    };
    var appendItem = function (segment, item) {
      append$1(segment.list, item);
      segment.item = item;
    };
    var writeShallow = function (scope, cast, entry) {
      var newCast = cast.slice(0, entry.depth);
      last(newCast).each(function (segment) {
        var item = createItem(scope, entry.itemAttributes, entry.content);
        appendItem(segment, item);
        normalizeSegment(segment, entry);
      });
      return newCast;
    };
    var writeDeep = function (scope, cast, entry) {
      var segments = createSegments(scope, entry, entry.depth - cast.length);
      joinSegments(segments);
      populateSegments(segments, entry);
      appendSegments(cast, segments);
      return cast.concat(segments);
    };
    var composeList = function (scope, entries) {
      var cast = foldl(entries, function (cast, entry) {
        return entry.depth > cast.length ? writeDeep(scope, cast, entry) : writeShallow(scope, cast, entry);
      }, []);
      return head(cast).map(function (segment) {
        return segment.list;
      });
    };

    var isList = function (el) {
      return is$1(el, 'OL,UL');
    };
    var hasFirstChildList = function (el) {
      return firstChild(el).exists(isList);
    };
    var hasLastChildList = function (el) {
      return lastChild(el).exists(isList);
    };

    var isIndented = function (entry) {
      return entry.depth > 0;
    };
    var isSelected = function (entry) {
      return entry.isSelected;
    };
    var cloneItemContent = function (li) {
      var children$1 = children(li);
      var content = hasLastChildList(li) ? children$1.slice(0, -1) : children$1;
      return map(content, deep);
    };
    var createEntry = function (li, depth, isSelected) {
      return parent(li).filter(isElement).map(function (list) {
        return {
          depth: depth,
          dirty: false,
          isSelected: isSelected,
          content: cloneItemContent(li),
          itemAttributes: clone$1(li),
          listAttributes: clone$1(list),
          listType: name(list)
        };
      });
    };

    var indentEntry = function (indentation, entry) {
      switch (indentation) {
      case 'Indent':
        entry.depth++;
        break;
      case 'Outdent':
        entry.depth--;
        break;
      case 'Flatten':
        entry.depth = 0;
      }
      entry.dirty = true;
    };

    var cloneListProperties = function (target, source) {
      target.listType = source.listType;
      target.listAttributes = __assign({}, source.listAttributes);
    };
    var cleanListProperties = function (entry) {
      entry.listAttributes = filter(entry.listAttributes, function (_value, key) {
        return key !== 'start';
      });
    };
    var closestSiblingEntry = function (entries, start) {
      var depth = entries[start].depth;
      var matches = function (entry) {
        return entry.depth === depth && !entry.dirty;
      };
      var until = function (entry) {
        return entry.depth < depth;
      };
      return findUntil(reverse(entries.slice(0, start)), matches, until).orThunk(function () {
        return findUntil(entries.slice(start + 1), matches, until);
      });
    };
    var normalizeEntries = function (entries) {
      each$1(entries, function (entry, i) {
        closestSiblingEntry(entries, i).fold(function () {
          if (entry.dirty) {
            cleanListProperties(entry);
          }
        }, function (matchingEntry) {
          return cloneListProperties(entry, matchingEntry);
        });
      });
      return entries;
    };

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var parseItem = function (depth, itemSelection, selectionState, item) {
      return firstChild(item).filter(isList).fold(function () {
        itemSelection.each(function (selection) {
          if (eq(selection.start, item)) {
            selectionState.set(true);
          }
        });
        var currentItemEntry = createEntry(item, depth, selectionState.get());
        itemSelection.each(function (selection) {
          if (eq(selection.end, item)) {
            selectionState.set(false);
          }
        });
        var childListEntries = lastChild(item).filter(isList).map(function (list) {
          return parseList(depth, itemSelection, selectionState, list);
        }).getOr([]);
        return currentItemEntry.toArray().concat(childListEntries);
      }, function (list) {
        return parseList(depth, itemSelection, selectionState, list);
      });
    };
    var parseList = function (depth, itemSelection, selectionState, list) {
      return bind(children(list), function (element) {
        var parser = isList(element) ? parseList : parseItem;
        var newDepth = depth + 1;
        return parser(newDepth, itemSelection, selectionState, element);
      });
    };
    var parseLists = function (lists, itemSelection) {
      var selectionState = Cell(false);
      var initialDepth = 0;
      return map(lists, function (list) {
        return {
          sourceList: list,
          entries: parseList(initialDepth, itemSelection, selectionState, list)
        };
      });
    };

    var outdentedComposer = function (editor, entries) {
      var normalizedEntries = normalizeEntries(entries);
      return map(normalizedEntries, function (entry) {
        var content = fromElements(entry.content);
        return SugarElement.fromDom(createTextBlock(editor, content.dom));
      });
    };
    var indentedComposer = function (editor, entries) {
      var normalizedEntries = normalizeEntries(entries);
      return composeList(editor.contentDocument, normalizedEntries).toArray();
    };
    var composeEntries = function (editor, entries) {
      return bind(groupBy(entries, isIndented), function (entries) {
        var groupIsIndented = head(entries).exists(isIndented);
        return groupIsIndented ? indentedComposer(editor, entries) : outdentedComposer(editor, entries);
      });
    };
    var indentSelectedEntries = function (entries, indentation) {
      each$1(filter$1(entries, isSelected), function (entry) {
        return indentEntry(indentation, entry);
      });
    };
    var getItemSelection = function (editor) {
      var selectedListItems = map(getSelectedListItems(editor), SugarElement.fromDom);
      return lift2(find$1(selectedListItems, not(hasFirstChildList)), find$1(reverse(selectedListItems), not(hasFirstChildList)), function (start, end) {
        return {
          start: start,
          end: end
        };
      });
    };
    var listIndentation = function (editor, lists, indentation) {
      var entrySets = parseLists(lists, getItemSelection(editor));
      each$1(entrySets, function (entrySet) {
        indentSelectedEntries(entrySet.entries, indentation);
        var composedLists = composeEntries(editor, entrySet.entries);
        each$1(composedLists, function (composedList) {
          fireListEvent(editor, indentation === 'Indent' ? 'IndentList' : 'OutdentList', composedList.dom);
        });
        before(entrySet.sourceList, composedLists);
        remove(entrySet.sourceList);
      });
    };

    var selectionIndentation = function (editor, indentation) {
      var lists = map(getSelectedListRoots(editor), SugarElement.fromDom);
      var dlItems = map(getSelectedDlItems(editor), SugarElement.fromDom);
      var isHandled = false;
      if (lists.length || dlItems.length) {
        var bookmark = editor.selection.getBookmark();
        listIndentation(editor, lists, indentation);
        dlIndentation(editor, indentation, dlItems);
        editor.selection.moveToBookmark(bookmark);
        editor.selection.setRng(normalizeRange(editor.selection.getRng()));
        editor.nodeChanged();
        isHandled = true;
      }
      return isHandled;
    };
    var indentListSelection = function (editor) {
      return selectionIndentation(editor, 'Indent');
    };
    var outdentListSelection = function (editor) {
      return selectionIndentation(editor, 'Outdent');
    };
    var flattenListSelection = function (editor) {
      return selectionIndentation(editor, 'Flatten');
    };

    var global = tinymce.util.Tools.resolve('tinymce.dom.BookmarkManager');

    var DOM$1 = global$3.DOM;
    var createBookmark = function (rng) {
      var bookmark = {};
      var setupEndPoint = function (start) {
        var container = rng[start ? 'startContainer' : 'endContainer'];
        var offset = rng[start ? 'startOffset' : 'endOffset'];
        if (container.nodeType === 1) {
          var offsetNode = DOM$1.create('span', { 'data-mce-type': 'bookmark' });
          if (container.hasChildNodes()) {
            offset = Math.min(offset, container.childNodes.length - 1);
            if (start) {
              container.insertBefore(offsetNode, container.childNodes[offset]);
            } else {
              DOM$1.insertAfter(offsetNode, container.childNodes[offset]);
            }
          } else {
            container.appendChild(offsetNode);
          }
          container = offsetNode;
          offset = 0;
        }
        bookmark[start ? 'startContainer' : 'endContainer'] = container;
        bookmark[start ? 'startOffset' : 'endOffset'] = offset;
      };
      setupEndPoint(true);
      if (!rng.collapsed) {
        setupEndPoint();
      }
      return bookmark;
    };
    var resolveBookmark = function (bookmark) {
      var restoreEndPoint = function (start) {
        var node;
        var nodeIndex = function (container) {
          var node = container.parentNode.firstChild, idx = 0;
          while (node) {
            if (node === container) {
              return idx;
            }
            if (node.nodeType !== 1 || node.getAttribute('data-mce-type') !== 'bookmark') {
              idx++;
            }
            node = node.nextSibling;
          }
          return -1;
        };
        var container = node = bookmark[start ? 'startContainer' : 'endContainer'];
        var offset = bookmark[start ? 'startOffset' : 'endOffset'];
        if (!container) {
          return;
        }
        if (container.nodeType === 1) {
          offset = nodeIndex(container);
          container = container.parentNode;
          DOM$1.remove(node);
          if (!container.hasChildNodes() && DOM$1.isBlock(container)) {
            container.appendChild(DOM$1.create('br'));
          }
        }
        bookmark[start ? 'startContainer' : 'endContainer'] = container;
        bookmark[start ? 'startOffset' : 'endOffset'] = offset;
      };
      restoreEndPoint(true);
      restoreEndPoint();
      var rng = DOM$1.createRng();
      rng.setStart(bookmark.startContainer, bookmark.startOffset);
      if (bookmark.endContainer) {
        rng.setEnd(bookmark.endContainer, bookmark.endOffset);
      }
      return normalizeRange(rng);
    };

    var listToggleActionFromListName = function (listName) {
      switch (listName) {
      case 'UL':
        return 'ToggleUlList';
      case 'OL':
        return 'ToggleOlList';
      case 'DL':
        return 'ToggleDLList';
      }
    };

    var isCustomList = function (list) {
      return /\btox\-/.test(list.className);
    };
    var listState = function (editor, listName, activate) {
      var nodeChangeHandler = function (e) {
        var inList = findUntil(e.parents, isListNode, isTableCellNode).filter(function (list) {
          return list.nodeName === listName && !isCustomList(list);
        }).isSome();
        activate(inList);
      };
      var parents = editor.dom.getParents(editor.selection.getNode());
      nodeChangeHandler({ parents: parents });
      editor.on('NodeChange', nodeChangeHandler);
      return function () {
        return editor.off('NodeChange', nodeChangeHandler);
      };
    };

    var updateListStyle = function (dom, el, detail) {
      var type = detail['list-style-type'] ? detail['list-style-type'] : null;
      dom.setStyle(el, 'list-style-type', type);
    };
    var setAttribs = function (elm, attrs) {
      global$2.each(attrs, function (value, key) {
        elm.setAttribute(key, value);
      });
    };
    var updateListAttrs = function (dom, el, detail) {
      setAttribs(el, detail['list-attributes']);
      global$2.each(dom.select('li', el), function (li) {
        setAttribs(li, detail['list-item-attributes']);
      });
    };
    var updateListWithDetails = function (dom, el, detail) {
      updateListStyle(dom, el, detail);
      updateListAttrs(dom, el, detail);
    };
    var removeStyles = function (dom, element, styles) {
      global$2.each(styles, function (style) {
        var _a;
        return dom.setStyle(element, (_a = {}, _a[style] = '', _a));
      });
    };
    var getEndPointNode = function (editor, rng, start, root) {
      var container = rng[start ? 'startContainer' : 'endContainer'];
      var offset = rng[start ? 'startOffset' : 'endOffset'];
      if (container.nodeType === 1) {
        container = container.childNodes[Math.min(offset, container.childNodes.length - 1)] || container;
      }
      if (!start && isBr(container.nextSibling)) {
        container = container.nextSibling;
      }
      while (container.parentNode !== root) {
        if (isTextBlock(editor, container)) {
          return container;
        }
        if (/^(TD|TH)$/.test(container.parentNode.nodeName)) {
          return container;
        }
        container = container.parentNode;
      }
      return container;
    };
    var getSelectedTextBlocks = function (editor, rng, root) {
      var textBlocks = [];
      var dom = editor.dom;
      var startNode = getEndPointNode(editor, rng, true, root);
      var endNode = getEndPointNode(editor, rng, false, root);
      var block;
      var siblings = [];
      for (var node = startNode; node; node = node.nextSibling) {
        siblings.push(node);
        if (node === endNode) {
          break;
        }
      }
      global$2.each(siblings, function (node) {
        if (isTextBlock(editor, node)) {
          textBlocks.push(node);
          block = null;
          return;
        }
        if (dom.isBlock(node) || isBr(node)) {
          if (isBr(node)) {
            dom.remove(node);
          }
          block = null;
          return;
        }
        var nextSibling = node.nextSibling;
        if (global.isBookmarkNode(node)) {
          if (isListNode(nextSibling) || isTextBlock(editor, nextSibling) || !nextSibling && node.parentNode === root) {
            block = null;
            return;
          }
        }
        if (!block) {
          block = dom.create('p');
          node.parentNode.insertBefore(block, node);
          textBlocks.push(block);
        }
        block.appendChild(node);
      });
      return textBlocks;
    };
    var hasCompatibleStyle = function (dom, sib, detail) {
      var sibStyle = dom.getStyle(sib, 'list-style-type');
      var detailStyle = detail ? detail['list-style-type'] : '';
      detailStyle = detailStyle === null ? '' : detailStyle;
      return sibStyle === detailStyle;
    };
    var applyList = function (editor, listName, detail) {
      var rng = editor.selection.getRng();
      var listItemName = 'LI';
      var root = getClosestListRootElm(editor, editor.selection.getStart(true));
      var dom = editor.dom;
      if (dom.getContentEditable(editor.selection.getNode()) === 'false') {
        return;
      }
      listName = listName.toUpperCase();
      if (listName === 'DL') {
        listItemName = 'DT';
      }
      var bookmark = createBookmark(rng);
      var selectedTextBlocks = getSelectedTextBlocks(editor, rng, root);
      global$2.each(selectedTextBlocks, function (block) {
        var listBlock;
        var sibling = block.previousSibling;
        var parent = block.parentNode;
        if (!isListItemNode(parent)) {
          if (sibling && isListNode(sibling) && sibling.nodeName === listName && hasCompatibleStyle(dom, sibling, detail)) {
            listBlock = sibling;
            block = dom.rename(block, listItemName);
            sibling.appendChild(block);
          } else {
            listBlock = dom.create(listName);
            block.parentNode.insertBefore(listBlock, block);
            listBlock.appendChild(block);
            block = dom.rename(block, listItemName);
          }
          removeStyles(dom, block, [
            'margin',
            'margin-right',
            'margin-bottom',
            'margin-left',
            'margin-top',
            'padding',
            'padding-right',
            'padding-bottom',
            'padding-left',
            'padding-top'
          ]);
          updateListWithDetails(dom, listBlock, detail);
          mergeWithAdjacentLists(editor.dom, listBlock);
        }
      });
      editor.selection.setRng(resolveBookmark(bookmark));
    };
    var isValidLists = function (list1, list2) {
      return list1 && list2 && isListNode(list1) && list1.nodeName === list2.nodeName;
    };
    var hasSameListStyle = function (dom, list1, list2) {
      var targetStyle = dom.getStyle(list1, 'list-style-type', true);
      var style = dom.getStyle(list2, 'list-style-type', true);
      return targetStyle === style;
    };
    var hasSameClasses = function (elm1, elm2) {
      return elm1.className === elm2.className;
    };
    var shouldMerge = function (dom, list1, list2) {
      return isValidLists(list1, list2) && hasSameListStyle(dom, list1, list2) && hasSameClasses(list1, list2);
    };
    var mergeWithAdjacentLists = function (dom, listBlock) {
      var sibling, node;
      sibling = listBlock.nextSibling;
      if (shouldMerge(dom, listBlock, sibling)) {
        while (node = sibling.firstChild) {
          listBlock.appendChild(node);
        }
        dom.remove(sibling);
      }
      sibling = listBlock.previousSibling;
      if (shouldMerge(dom, listBlock, sibling)) {
        while (node = sibling.lastChild) {
          listBlock.insertBefore(node, listBlock.firstChild);
        }
        dom.remove(sibling);
      }
    };
    var updateList$1 = function (editor, list, listName, detail) {
      if (list.nodeName !== listName) {
        var newList = editor.dom.rename(list, listName);
        updateListWithDetails(editor.dom, newList, detail);
        fireListEvent(editor, listToggleActionFromListName(listName), newList);
      } else {
        updateListWithDetails(editor.dom, list, detail);
        fireListEvent(editor, listToggleActionFromListName(listName), list);
      }
    };
    var toggleMultipleLists = function (editor, parentList, lists, listName, detail) {
      var parentIsList = isListNode(parentList);
      if (parentIsList && parentList.nodeName === listName && !hasListStyleDetail(detail)) {
        flattenListSelection(editor);
      } else {
        applyList(editor, listName, detail);
        var bookmark = createBookmark(editor.selection.getRng());
        var allLists = parentIsList ? __spreadArray([parentList], lists, true) : lists;
        global$2.each(allLists, function (elm) {
          updateList$1(editor, elm, listName, detail);
        });
        editor.selection.setRng(resolveBookmark(bookmark));
      }
    };
    var hasListStyleDetail = function (detail) {
      return 'list-style-type' in detail;
    };
    var toggleSingleList = function (editor, parentList, listName, detail) {
      if (parentList === editor.getBody()) {
        return;
      }
      if (parentList) {
        if (parentList.nodeName === listName && !hasListStyleDetail(detail) && !isCustomList(parentList)) {
          flattenListSelection(editor);
        } else {
          var bookmark = createBookmark(editor.selection.getRng());
          updateListWithDetails(editor.dom, parentList, detail);
          var newList = editor.dom.rename(parentList, listName);
          mergeWithAdjacentLists(editor.dom, newList);
          editor.selection.setRng(resolveBookmark(bookmark));
          applyList(editor, listName, detail);
          fireListEvent(editor, listToggleActionFromListName(listName), newList);
        }
      } else {
        applyList(editor, listName, detail);
        fireListEvent(editor, listToggleActionFromListName(listName), parentList);
      }
    };
    var toggleList = function (editor, listName, _detail) {
      var parentList = getParentList(editor);
      var selectedSubLists = getSelectedSubLists(editor);
      var detail = isObject(_detail) ? _detail : {};
      if (selectedSubLists.length > 0) {
        toggleMultipleLists(editor, parentList, selectedSubLists, listName, detail);
      } else {
        toggleSingleList(editor, parentList, listName, detail);
      }
    };

    var DOM = global$3.DOM;
    var normalizeList = function (dom, list) {
      var parentNode = list.parentNode;
      if (parentNode.nodeName === 'LI' && parentNode.firstChild === list) {
        var sibling = parentNode.previousSibling;
        if (sibling && sibling.nodeName === 'LI') {
          sibling.appendChild(list);
          if (isEmpty(dom, parentNode)) {
            DOM.remove(parentNode);
          }
        } else {
          DOM.setStyle(parentNode, 'listStyleType', 'none');
        }
      }
      if (isListNode(parentNode)) {
        var sibling = parentNode.previousSibling;
        if (sibling && sibling.nodeName === 'LI') {
          sibling.appendChild(list);
        }
      }
    };
    var normalizeLists = function (dom, element) {
      var lists = global$2.grep(dom.select('ol,ul', element));
      global$2.each(lists, function (list) {
        normalizeList(dom, list);
      });
    };

    var findNextCaretContainer = function (editor, rng, isForward, root) {
      var node = rng.startContainer;
      var offset = rng.startOffset;
      if (isTextNode(node) && (isForward ? offset < node.data.length : offset > 0)) {
        return node;
      }
      var nonEmptyBlocks = editor.schema.getNonEmptyElements();
      if (node.nodeType === 1) {
        node = global$6.getNode(node, offset);
      }
      var walker = new global$5(node, root);
      if (isForward) {
        if (isBogusBr(editor.dom, node)) {
          walker.next();
        }
      }
      while (node = walker[isForward ? 'next' : 'prev2']()) {
        if (node.nodeName === 'LI' && !node.hasChildNodes()) {
          return node;
        }
        if (nonEmptyBlocks[node.nodeName]) {
          return node;
        }
        if (isTextNode(node) && node.data.length > 0) {
          return node;
        }
      }
    };
    var hasOnlyOneBlockChild = function (dom, elm) {
      var childNodes = elm.childNodes;
      return childNodes.length === 1 && !isListNode(childNodes[0]) && dom.isBlock(childNodes[0]);
    };
    var unwrapSingleBlockChild = function (dom, elm) {
      if (hasOnlyOneBlockChild(dom, elm)) {
        dom.remove(elm.firstChild, true);
      }
    };
    var moveChildren = function (dom, fromElm, toElm) {
      var node;
      var targetElm = hasOnlyOneBlockChild(dom, toElm) ? toElm.firstChild : toElm;
      unwrapSingleBlockChild(dom, fromElm);
      if (!isEmpty(dom, fromElm, true)) {
        while (node = fromElm.firstChild) {
          targetElm.appendChild(node);
        }
      }
    };
    var mergeLiElements = function (dom, fromElm, toElm) {
      var listNode;
      var ul = fromElm.parentNode;
      if (!isChildOfBody(dom, fromElm) || !isChildOfBody(dom, toElm)) {
        return;
      }
      if (isListNode(toElm.lastChild)) {
        listNode = toElm.lastChild;
      }
      if (ul === toElm.lastChild) {
        if (isBr(ul.previousSibling)) {
          dom.remove(ul.previousSibling);
        }
      }
      var node = toElm.lastChild;
      if (node && isBr(node) && fromElm.hasChildNodes()) {
        dom.remove(node);
      }
      if (isEmpty(dom, toElm, true)) {
        dom.$(toElm).empty();
      }
      moveChildren(dom, fromElm, toElm);
      if (listNode) {
        toElm.appendChild(listNode);
      }
      var contains$1 = contains(SugarElement.fromDom(toElm), SugarElement.fromDom(fromElm));
      var nestedLists = contains$1 ? dom.getParents(fromElm, isListNode, toElm) : [];
      dom.remove(fromElm);
      each$1(nestedLists, function (list) {
        if (isEmpty(dom, list) && list !== dom.getRoot()) {
          dom.remove(list);
        }
      });
    };
    var mergeIntoEmptyLi = function (editor, fromLi, toLi) {
      editor.dom.$(toLi).empty();
      mergeLiElements(editor.dom, fromLi, toLi);
      editor.selection.setCursorLocation(toLi, 0);
    };
    var mergeForward = function (editor, rng, fromLi, toLi) {
      var dom = editor.dom;
      if (dom.isEmpty(toLi)) {
        mergeIntoEmptyLi(editor, fromLi, toLi);
      } else {
        var bookmark = createBookmark(rng);
        mergeLiElements(dom, fromLi, toLi);
        editor.selection.setRng(resolveBookmark(bookmark));
      }
    };
    var mergeBackward = function (editor, rng, fromLi, toLi) {
      var bookmark = createBookmark(rng);
      mergeLiElements(editor.dom, fromLi, toLi);
      var resolvedBookmark = resolveBookmark(bookmark);
      editor.selection.setRng(resolvedBookmark);
    };
    var backspaceDeleteFromListToListCaret = function (editor, isForward) {
      var dom = editor.dom, selection = editor.selection;
      var selectionStartElm = selection.getStart();
      var root = getClosestListRootElm(editor, selectionStartElm);
      var li = dom.getParent(selection.getStart(), 'LI', root);
      if (li) {
        var ul = li.parentNode;
        if (ul === editor.getBody() && isEmpty(dom, ul)) {
          return true;
        }
        var rng_1 = normalizeRange(selection.getRng());
        var otherLi_1 = dom.getParent(findNextCaretContainer(editor, rng_1, isForward, root), 'LI', root);
        if (otherLi_1 && otherLi_1 !== li) {
          editor.undoManager.transact(function () {
            if (isForward) {
              mergeForward(editor, rng_1, otherLi_1, li);
            } else {
              if (isFirstChild(li)) {
                outdentListSelection(editor);
              } else {
                mergeBackward(editor, rng_1, li, otherLi_1);
              }
            }
          });
          return true;
        } else if (!otherLi_1) {
          if (!isForward && rng_1.startOffset === 0 && rng_1.endOffset === 0) {
            editor.undoManager.transact(function () {
              flattenListSelection(editor);
            });
            return true;
          }
        }
      }
      return false;
    };
    var removeBlock = function (dom, block, root) {
      var parentBlock = dom.getParent(block.parentNode, dom.isBlock, root);
      dom.remove(block);
      if (parentBlock && dom.isEmpty(parentBlock)) {
        dom.remove(parentBlock);
      }
    };
    var backspaceDeleteIntoListCaret = function (editor, isForward) {
      var dom = editor.dom;
      var selectionStartElm = editor.selection.getStart();
      var root = getClosestListRootElm(editor, selectionStartElm);
      var block = dom.getParent(selectionStartElm, dom.isBlock, root);
      if (block && dom.isEmpty(block)) {
        var rng = normalizeRange(editor.selection.getRng());
        var otherLi_2 = dom.getParent(findNextCaretContainer(editor, rng, isForward, root), 'LI', root);
        if (otherLi_2) {
          editor.undoManager.transact(function () {
            removeBlock(dom, block, root);
            mergeWithAdjacentLists(dom, otherLi_2.parentNode);
            editor.selection.select(otherLi_2, true);
            editor.selection.collapse(isForward);
          });
          return true;
        }
      }
      return false;
    };
    var backspaceDeleteCaret = function (editor, isForward) {
      return backspaceDeleteFromListToListCaret(editor, isForward) || backspaceDeleteIntoListCaret(editor, isForward);
    };
    var backspaceDeleteRange = function (editor) {
      var selectionStartElm = editor.selection.getStart();
      var root = getClosestListRootElm(editor, selectionStartElm);
      var startListParent = editor.dom.getParent(selectionStartElm, 'LI,DT,DD', root);
      if (startListParent || getSelectedListItems(editor).length > 0) {
        editor.undoManager.transact(function () {
          editor.execCommand('Delete');
          normalizeLists(editor.dom, editor.getBody());
        });
        return true;
      }
      return false;
    };
    var backspaceDelete = function (editor, isForward) {
      return editor.selection.isCollapsed() ? backspaceDeleteCaret(editor, isForward) : backspaceDeleteRange(editor);
    };
    var setup$1 = function (editor) {
      editor.on('keydown', function (e) {
        if (e.keyCode === global$4.BACKSPACE) {
          if (backspaceDelete(editor, false)) {
            e.preventDefault();
          }
        } else if (e.keyCode === global$4.DELETE) {
          if (backspaceDelete(editor, true)) {
            e.preventDefault();
          }
        }
      });
    };

    var get = function (editor) {
      return {
        backspaceDelete: function (isForward) {
          backspaceDelete(editor, isForward);
        }
      };
    };

    var updateList = function (editor, update) {
      var parentList = getParentList(editor);
      editor.undoManager.transact(function () {
        if (isObject(update.styles)) {
          editor.dom.setStyles(parentList, update.styles);
        }
        if (isObject(update.attrs)) {
          each(update.attrs, function (v, k) {
            return editor.dom.setAttrib(parentList, k, v);
          });
        }
      });
    };

    var parseAlphabeticBase26 = function (str) {
      var chars = reverse(trim(str).split(''));
      var values = map(chars, function (char, i) {
        var charValue = char.toUpperCase().charCodeAt(0) - 'A'.charCodeAt(0) + 1;
        return Math.pow(26, i) * charValue;
      });
      return foldl(values, function (sum, v) {
        return sum + v;
      }, 0);
    };
    var composeAlphabeticBase26 = function (value) {
      value--;
      if (value < 0) {
        return '';
      } else {
        var remainder = value % 26;
        var quotient = Math.floor(value / 26);
        var rest = composeAlphabeticBase26(quotient);
        var char = String.fromCharCode('A'.charCodeAt(0) + remainder);
        return rest + char;
      }
    };
    var isUppercase = function (str) {
      return /^[A-Z]+$/.test(str);
    };
    var isLowercase = function (str) {
      return /^[a-z]+$/.test(str);
    };
    var isNumeric = function (str) {
      return /^[0-9]+$/.test(str);
    };
    var deduceListType = function (start) {
      if (isNumeric(start)) {
        return 2;
      } else if (isUppercase(start)) {
        return 0;
      } else if (isLowercase(start)) {
        return 1;
      } else if (isEmpty$1(start)) {
        return 3;
      } else {
        return 4;
      }
    };
    var parseStartValue = function (start) {
      switch (deduceListType(start)) {
      case 2:
        return Optional.some({
          listStyleType: Optional.none(),
          start: start
        });
      case 0:
        return Optional.some({
          listStyleType: Optional.some('upper-alpha'),
          start: parseAlphabeticBase26(start).toString()
        });
      case 1:
        return Optional.some({
          listStyleType: Optional.some('lower-alpha'),
          start: parseAlphabeticBase26(start).toString()
        });
      case 3:
        return Optional.some({
          listStyleType: Optional.none(),
          start: ''
        });
      case 4:
        return Optional.none();
      }
    };
    var parseDetail = function (detail) {
      var start = parseInt(detail.start, 10);
      if (is(detail.listStyleType, 'upper-alpha')) {
        return composeAlphabeticBase26(start);
      } else if (is(detail.listStyleType, 'lower-alpha')) {
        return composeAlphabeticBase26(start).toLowerCase();
      } else {
        return detail.start;
      }
    };

    var open = function (editor) {
      var currentList = getParentList(editor);
      if (!isOlNode(currentList)) {
        return;
      }
      editor.windowManager.open({
        title: 'List Properties',
        body: {
          type: 'panel',
          items: [{
              type: 'input',
              name: 'start',
              label: 'Start list at number',
              inputMode: 'numeric'
            }]
        },
        initialData: {
          start: parseDetail({
            start: editor.dom.getAttrib(currentList, 'start', '1'),
            listStyleType: Optional.some(editor.dom.getStyle(currentList, 'list-style-type'))
          })
        },
        buttons: [
          {
            type: 'cancel',
            name: 'cancel',
            text: 'Cancel'
          },
          {
            type: 'submit',
            name: 'save',
            text: 'Save',
            primary: true
          }
        ],
        onSubmit: function (api) {
          var data = api.getData();
          parseStartValue(data.start).each(function (detail) {
            editor.execCommand('mceListUpdate', false, {
              attrs: { start: detail.start === '1' ? '' : detail.start },
              styles: { 'list-style-type': detail.listStyleType.getOr('') }
            });
          });
          api.close();
        }
      });
    };

    var queryListCommandState = function (editor, listName) {
      return function () {
        var parentList = getParentList(editor);
        return parentList && parentList.nodeName === listName;
      };
    };
    var registerDialog = function (editor) {
      editor.addCommand('mceListProps', function () {
        open(editor);
      });
    };
    var register$2 = function (editor) {
      editor.on('BeforeExecCommand', function (e) {
        var cmd = e.command.toLowerCase();
        if (cmd === 'indent') {
          indentListSelection(editor);
        } else if (cmd === 'outdent') {
          outdentListSelection(editor);
        }
      });
      editor.addCommand('InsertUnorderedList', function (ui, detail) {
        toggleList(editor, 'UL', detail);
      });
      editor.addCommand('InsertOrderedList', function (ui, detail) {
        toggleList(editor, 'OL', detail);
      });
      editor.addCommand('InsertDefinitionList', function (ui, detail) {
        toggleList(editor, 'DL', detail);
      });
      editor.addCommand('RemoveList', function () {
        flattenListSelection(editor);
      });
      registerDialog(editor);
      editor.addCommand('mceListUpdate', function (ui, detail) {
        if (isObject(detail)) {
          updateList(editor, detail);
        }
      });
      editor.addQueryStateHandler('InsertUnorderedList', queryListCommandState(editor, 'UL'));
      editor.addQueryStateHandler('InsertOrderedList', queryListCommandState(editor, 'OL'));
      editor.addQueryStateHandler('InsertDefinitionList', queryListCommandState(editor, 'DL'));
    };

    var setupTabKey = function (editor) {
      editor.on('keydown', function (e) {
        if (e.keyCode !== global$4.TAB || global$4.metaKeyPressed(e)) {
          return;
        }
        editor.undoManager.transact(function () {
          if (e.shiftKey ? outdentListSelection(editor) : indentListSelection(editor)) {
            e.preventDefault();
          }
        });
      });
    };
    var setup = function (editor) {
      if (shouldIndentOnTab(editor)) {
        setupTabKey(editor);
      }
      setup$1(editor);
    };

    var register$1 = function (editor) {
      var exec = function (command) {
        return function () {
          return editor.execCommand(command);
        };
      };
      if (!editor.hasPlugin('advlist')) {
        editor.ui.registry.addToggleButton('numlist', {
          icon: 'ordered-list',
          active: false,
          tooltip: 'Numbered list',
          onAction: exec('InsertOrderedList'),
          onSetup: function (api) {
            return listState(editor, 'OL', api.setActive);
          }
        });
        editor.ui.registry.addToggleButton('bullist', {
          icon: 'unordered-list',
          active: false,
          tooltip: 'Bullet list',
          onAction: exec('InsertUnorderedList'),
          onSetup: function (api) {
            return listState(editor, 'UL', api.setActive);
          }
        });
      }
    };

    var register = function (editor) {
      var listProperties = {
        text: 'List properties...',
        icon: 'ordered-list',
        onAction: function () {
          return editor.execCommand('mceListProps');
        },
        onSetup: function (api) {
          return listState(editor, 'OL', function (active) {
            return api.setDisabled(!active);
          });
        }
      };
      editor.ui.registry.addMenuItem('listprops', listProperties);
      editor.ui.registry.addContextMenu('lists', {
        update: function (node) {
          var parentList = getParentList(editor, node);
          return isOlNode(parentList) ? ['listprops'] : [];
        }
      });
    };

    function Plugin () {
      global$7.add('lists', function (editor) {
        if (editor.hasPlugin('rtc', true) === false) {
          setup(editor);
          register$2(editor);
        } else {
          registerDialog(editor);
        }
        register$1(editor);
        register(editor);
        return get(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/media/index.js":
/*!*****************************************************!*\
  !*** ./node_modules/tinymce/plugins/media/index.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "media" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/media')
//   ES2015:
//     import 'tinymce/plugins/media'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/media/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/media/plugin.js":
/*!******************************************************!*\
  !*** ./node_modules/tinymce/plugins/media/plugin.js ***!
  \******************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$9 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isString = isType('string');
    var isObject = isType('object');
    var isArray = isType('array');
    var isNullable = function (a) {
      return a === null || a === undefined;
    };
    var isNonNullable = function (a) {
      return !isNullable(a);
    };

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var nativePush = Array.prototype.push;
    var each$1 = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var flatten = function (xs) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; ++i) {
        if (!isArray(xs[i])) {
          throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
        }
        nativePush.apply(r, xs[i]);
      }
      return r;
    };

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var keys = Object.keys;
    var hasOwnProperty = Object.hasOwnProperty;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };
    var get$1 = function (obj, key) {
      return has(obj, key) ? Optional.from(obj[key]) : Optional.none();
    };
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };

    var getScripts = function (editor) {
      return editor.getParam('media_scripts');
    };
    var getAudioTemplateCallback = function (editor) {
      return editor.getParam('audio_template_callback');
    };
    var getVideoTemplateCallback = function (editor) {
      return editor.getParam('video_template_callback');
    };
    var hasLiveEmbeds = function (editor) {
      return editor.getParam('media_live_embeds', true);
    };
    var shouldFilterHtml = function (editor) {
      return editor.getParam('media_filter_html', true);
    };
    var getUrlResolver = function (editor) {
      return editor.getParam('media_url_resolver');
    };
    var hasAltSource = function (editor) {
      return editor.getParam('media_alt_source', true);
    };
    var hasPoster = function (editor) {
      return editor.getParam('media_poster', true);
    };
    var hasDimensions = function (editor) {
      return editor.getParam('media_dimensions', true);
    };

    var global$8 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var global$7 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

    var global$6 = tinymce.util.Tools.resolve('tinymce.html.SaxParser');

    var getVideoScriptMatch = function (prefixes, src) {
      if (prefixes) {
        for (var i = 0; i < prefixes.length; i++) {
          if (src.indexOf(prefixes[i].filter) !== -1) {
            return prefixes[i];
          }
        }
      }
    };

    var DOM$1 = global$7.DOM;
    var trimPx = function (value) {
      return value.replace(/px$/, '');
    };
    var getEphoxEmbedData = function (attrs) {
      var style = attrs.map.style;
      var styles = style ? DOM$1.parseStyle(style) : {};
      return {
        type: 'ephox-embed-iri',
        source: attrs.map['data-ephox-embed-iri'],
        altsource: '',
        poster: '',
        width: get$1(styles, 'max-width').map(trimPx).getOr(''),
        height: get$1(styles, 'max-height').map(trimPx).getOr('')
      };
    };
    var htmlToData = function (prefixes, html) {
      var isEphoxEmbed = Cell(false);
      var data = {};
      global$6({
        validate: false,
        allow_conditional_comments: true,
        start: function (name, attrs) {
          if (isEphoxEmbed.get()) ; else if (has(attrs.map, 'data-ephox-embed-iri')) {
            isEphoxEmbed.set(true);
            data = getEphoxEmbedData(attrs);
          } else {
            if (!data.source && name === 'param') {
              data.source = attrs.map.movie;
            }
            if (name === 'iframe' || name === 'object' || name === 'embed' || name === 'video' || name === 'audio') {
              if (!data.type) {
                data.type = name;
              }
              data = global$8.extend(attrs.map, data);
            }
            if (name === 'script') {
              var videoScript = getVideoScriptMatch(prefixes, attrs.map.src);
              if (!videoScript) {
                return;
              }
              data = {
                type: 'script',
                source: attrs.map.src,
                width: String(videoScript.width),
                height: String(videoScript.height)
              };
            }
            if (name === 'source') {
              if (!data.source) {
                data.source = attrs.map.src;
              } else if (!data.altsource) {
                data.altsource = attrs.map.src;
              }
            }
            if (name === 'img' && !data.poster) {
              data.poster = attrs.map.src;
            }
          }
        }
      }).parse(html);
      data.source = data.source || data.src || data.data;
      data.altsource = data.altsource || '';
      data.poster = data.poster || '';
      return data;
    };

    var guess = function (url) {
      var mimes = {
        mp3: 'audio/mpeg',
        m4a: 'audio/x-m4a',
        wav: 'audio/wav',
        mp4: 'video/mp4',
        webm: 'video/webm',
        ogg: 'video/ogg',
        swf: 'application/x-shockwave-flash'
      };
      var fileEnd = url.toLowerCase().split('.').pop();
      var mime = mimes[fileEnd];
      return mime ? mime : '';
    };

    var global$5 = tinymce.util.Tools.resolve('tinymce.html.Schema');

    var global$4 = tinymce.util.Tools.resolve('tinymce.html.Writer');

    var DOM = global$7.DOM;
    var addPx = function (value) {
      return /^[0-9.]+$/.test(value) ? value + 'px' : value;
    };
    var setAttributes = function (attrs, updatedAttrs) {
      each(updatedAttrs, function (val, name) {
        var value = '' + val;
        if (attrs.map[name]) {
          var i = attrs.length;
          while (i--) {
            var attr = attrs[i];
            if (attr.name === name) {
              if (value) {
                attrs.map[name] = value;
                attr.value = value;
              } else {
                delete attrs.map[name];
                attrs.splice(i, 1);
              }
            }
          }
        } else if (value) {
          attrs.push({
            name: name,
            value: value
          });
          attrs.map[name] = value;
        }
      });
    };
    var updateEphoxEmbed = function (data, attrs) {
      var style = attrs.map.style;
      var styleMap = style ? DOM.parseStyle(style) : {};
      styleMap['max-width'] = addPx(data.width);
      styleMap['max-height'] = addPx(data.height);
      setAttributes(attrs, { style: DOM.serializeStyle(styleMap) });
    };
    var sources = [
      'source',
      'altsource'
    ];
    var updateHtml = function (html, data, updateAll) {
      var writer = global$4();
      var isEphoxEmbed = Cell(false);
      var sourceCount = 0;
      var hasImage;
      global$6({
        validate: false,
        allow_conditional_comments: true,
        comment: function (text) {
          writer.comment(text);
        },
        cdata: function (text) {
          writer.cdata(text);
        },
        text: function (text, raw) {
          writer.text(text, raw);
        },
        start: function (name, attrs, empty) {
          if (isEphoxEmbed.get()) ; else if (has(attrs.map, 'data-ephox-embed-iri')) {
            isEphoxEmbed.set(true);
            updateEphoxEmbed(data, attrs);
          } else {
            switch (name) {
            case 'video':
            case 'object':
            case 'embed':
            case 'img':
            case 'iframe':
              if (data.height !== undefined && data.width !== undefined) {
                setAttributes(attrs, {
                  width: data.width,
                  height: data.height
                });
              }
              break;
            }
            if (updateAll) {
              switch (name) {
              case 'video':
                setAttributes(attrs, {
                  poster: data.poster,
                  src: ''
                });
                if (data.altsource) {
                  setAttributes(attrs, { src: '' });
                }
                break;
              case 'iframe':
                setAttributes(attrs, { src: data.source });
                break;
              case 'source':
                if (sourceCount < 2) {
                  setAttributes(attrs, {
                    src: data[sources[sourceCount]],
                    type: data[sources[sourceCount] + 'mime']
                  });
                  if (!data[sources[sourceCount]]) {
                    return;
                  }
                }
                sourceCount++;
                break;
              case 'img':
                if (!data.poster) {
                  return;
                }
                hasImage = true;
                break;
              }
            }
          }
          writer.start(name, attrs, empty);
        },
        end: function (name) {
          if (!isEphoxEmbed.get()) {
            if (name === 'video' && updateAll) {
              for (var index = 0; index < 2; index++) {
                if (data[sources[index]]) {
                  var attrs = [];
                  attrs.map = {};
                  if (sourceCount <= index) {
                    setAttributes(attrs, {
                      src: data[sources[index]],
                      type: data[sources[index] + 'mime']
                    });
                    writer.start('source', attrs, true);
                  }
                }
              }
            }
            if (data.poster && name === 'object' && updateAll && !hasImage) {
              var imgAttrs = [];
              imgAttrs.map = {};
              setAttributes(imgAttrs, {
                src: data.poster,
                width: data.width,
                height: data.height
              });
              writer.start('img', imgAttrs, true);
            }
          }
          writer.end(name);
        }
      }, global$5({})).parse(html);
      return writer.getContent();
    };

    var urlPatterns = [
      {
        regex: /youtu\.be\/([\w\-_\?&=.]+)/i,
        type: 'iframe',
        w: 560,
        h: 314,
        url: 'www.youtube.com/embed/$1',
        allowFullscreen: true
      },
      {
        regex: /youtube\.com(.+)v=([^&]+)(&([a-z0-9&=\-_]+))?/i,
        type: 'iframe',
        w: 560,
        h: 314,
        url: 'www.youtube.com/embed/$2?$4',
        allowFullscreen: true
      },
      {
        regex: /youtube.com\/embed\/([a-z0-9\?&=\-_]+)/i,
        type: 'iframe',
        w: 560,
        h: 314,
        url: 'www.youtube.com/embed/$1',
        allowFullscreen: true
      },
      {
        regex: /vimeo\.com\/([0-9]+)/,
        type: 'iframe',
        w: 425,
        h: 350,
        url: 'player.vimeo.com/video/$1?title=0&byline=0&portrait=0&color=8dc7dc',
        allowFullscreen: true
      },
      {
        regex: /vimeo\.com\/(.*)\/([0-9]+)/,
        type: 'iframe',
        w: 425,
        h: 350,
        url: 'player.vimeo.com/video/$2?title=0&amp;byline=0',
        allowFullscreen: true
      },
      {
        regex: /maps\.google\.([a-z]{2,3})\/maps\/(.+)msid=(.+)/,
        type: 'iframe',
        w: 425,
        h: 350,
        url: 'maps.google.com/maps/ms?msid=$2&output=embed"',
        allowFullscreen: false
      },
      {
        regex: /dailymotion\.com\/video\/([^_]+)/,
        type: 'iframe',
        w: 480,
        h: 270,
        url: 'www.dailymotion.com/embed/video/$1',
        allowFullscreen: true
      },
      {
        regex: /dai\.ly\/([^_]+)/,
        type: 'iframe',
        w: 480,
        h: 270,
        url: 'www.dailymotion.com/embed/video/$1',
        allowFullscreen: true
      }
    ];
    var getProtocol = function (url) {
      var protocolMatches = url.match(/^(https?:\/\/|www\.)(.+)$/i);
      if (protocolMatches && protocolMatches.length > 1) {
        return protocolMatches[1] === 'www.' ? 'https://' : protocolMatches[1];
      } else {
        return 'https://';
      }
    };
    var getUrl = function (pattern, url) {
      var protocol = getProtocol(url);
      var match = pattern.regex.exec(url);
      var newUrl = protocol + pattern.url;
      var _loop_1 = function (i) {
        newUrl = newUrl.replace('$' + i, function () {
          return match[i] ? match[i] : '';
        });
      };
      for (var i = 0; i < match.length; i++) {
        _loop_1(i);
      }
      return newUrl.replace(/\?$/, '');
    };
    var matchPattern = function (url) {
      var patterns = urlPatterns.filter(function (pattern) {
        return pattern.regex.test(url);
      });
      if (patterns.length > 0) {
        return global$8.extend({}, patterns[0], { url: getUrl(patterns[0], url) });
      } else {
        return null;
      }
    };

    var getIframeHtml = function (data) {
      var allowFullscreen = data.allowfullscreen ? ' allowFullscreen="1"' : '';
      return '<iframe src="' + data.source + '" width="' + data.width + '" height="' + data.height + '"' + allowFullscreen + '></iframe>';
    };
    var getFlashHtml = function (data) {
      var html = '<object data="' + data.source + '" width="' + data.width + '" height="' + data.height + '" type="application/x-shockwave-flash">';
      if (data.poster) {
        html += '<img src="' + data.poster + '" width="' + data.width + '" height="' + data.height + '" />';
      }
      html += '</object>';
      return html;
    };
    var getAudioHtml = function (data, audioTemplateCallback) {
      if (audioTemplateCallback) {
        return audioTemplateCallback(data);
      } else {
        return '<audio controls="controls" src="' + data.source + '">' + (data.altsource ? '\n<source src="' + data.altsource + '"' + (data.altsourcemime ? ' type="' + data.altsourcemime + '"' : '') + ' />\n' : '') + '</audio>';
      }
    };
    var getVideoHtml = function (data, videoTemplateCallback) {
      if (videoTemplateCallback) {
        return videoTemplateCallback(data);
      } else {
        return '<video width="' + data.width + '" height="' + data.height + '"' + (data.poster ? ' poster="' + data.poster + '"' : '') + ' controls="controls">\n' + '<source src="' + data.source + '"' + (data.sourcemime ? ' type="' + data.sourcemime + '"' : '') + ' />\n' + (data.altsource ? '<source src="' + data.altsource + '"' + (data.altsourcemime ? ' type="' + data.altsourcemime + '"' : '') + ' />\n' : '') + '</video>';
      }
    };
    var getScriptHtml = function (data) {
      return '<script src="' + data.source + '"></script>';
    };
    var dataToHtml = function (editor, dataIn) {
      var data = global$8.extend({}, dataIn);
      if (!data.source) {
        global$8.extend(data, htmlToData(getScripts(editor), data.embed));
        if (!data.source) {
          return '';
        }
      }
      if (!data.altsource) {
        data.altsource = '';
      }
      if (!data.poster) {
        data.poster = '';
      }
      data.source = editor.convertURL(data.source, 'source');
      data.altsource = editor.convertURL(data.altsource, 'source');
      data.sourcemime = guess(data.source);
      data.altsourcemime = guess(data.altsource);
      data.poster = editor.convertURL(data.poster, 'poster');
      var pattern = matchPattern(data.source);
      if (pattern) {
        data.source = pattern.url;
        data.type = pattern.type;
        data.allowfullscreen = pattern.allowFullscreen;
        data.width = data.width || String(pattern.w);
        data.height = data.height || String(pattern.h);
      }
      if (data.embed) {
        return updateHtml(data.embed, data, true);
      } else {
        var videoScript = getVideoScriptMatch(getScripts(editor), data.source);
        if (videoScript) {
          data.type = 'script';
          data.width = String(videoScript.width);
          data.height = String(videoScript.height);
        }
        var audioTemplateCallback = getAudioTemplateCallback(editor);
        var videoTemplateCallback = getVideoTemplateCallback(editor);
        data.width = data.width || '300';
        data.height = data.height || '150';
        global$8.each(data, function (value, key) {
          data[key] = editor.dom.encode('' + value);
        });
        if (data.type === 'iframe') {
          return getIframeHtml(data);
        } else if (data.sourcemime === 'application/x-shockwave-flash') {
          return getFlashHtml(data);
        } else if (data.sourcemime.indexOf('audio') !== -1) {
          return getAudioHtml(data, audioTemplateCallback);
        } else if (data.type === 'script') {
          return getScriptHtml(data);
        } else {
          return getVideoHtml(data, videoTemplateCallback);
        }
      }
    };

    var isMediaElement = function (element) {
      return element.hasAttribute('data-mce-object') || element.hasAttribute('data-ephox-embed-iri');
    };
    var setup$2 = function (editor) {
      editor.on('click keyup touchend', function () {
        var selectedNode = editor.selection.getNode();
        if (selectedNode && editor.dom.hasClass(selectedNode, 'mce-preview-object')) {
          if (editor.dom.getAttrib(selectedNode, 'data-mce-selected')) {
            selectedNode.setAttribute('data-mce-selected', '2');
          }
        }
      });
      editor.on('ObjectSelected', function (e) {
        var objectType = e.target.getAttribute('data-mce-object');
        if (objectType === 'script') {
          e.preventDefault();
        }
      });
      editor.on('ObjectResized', function (e) {
        var target = e.target;
        if (target.getAttribute('data-mce-object')) {
          var html = target.getAttribute('data-mce-html');
          if (html) {
            html = unescape(html);
            target.setAttribute('data-mce-html', escape(updateHtml(html, {
              width: String(e.width),
              height: String(e.height)
            })));
          }
        }
      });
    };

    var global$3 = tinymce.util.Tools.resolve('tinymce.util.Promise');

    var cache = {};
    var embedPromise = function (data, dataToHtml, handler) {
      return new global$3(function (res, rej) {
        var wrappedResolve = function (response) {
          if (response.html) {
            cache[data.source] = response;
          }
          return res({
            url: data.source,
            html: response.html ? response.html : dataToHtml(data)
          });
        };
        if (cache[data.source]) {
          wrappedResolve(cache[data.source]);
        } else {
          handler({ url: data.source }, wrappedResolve, rej);
        }
      });
    };
    var defaultPromise = function (data, dataToHtml) {
      return global$3.resolve({
        html: dataToHtml(data),
        url: data.source
      });
    };
    var loadedData = function (editor) {
      return function (data) {
        return dataToHtml(editor, data);
      };
    };
    var getEmbedHtml = function (editor, data) {
      var embedHandler = getUrlResolver(editor);
      return embedHandler ? embedPromise(data, loadedData(editor), embedHandler) : defaultPromise(data, loadedData(editor));
    };
    var isCached = function (url) {
      return has(cache, url);
    };

    var extractMeta = function (sourceInput, data) {
      return get$1(data, sourceInput).bind(function (mainData) {
        return get$1(mainData, 'meta');
      });
    };
    var getValue = function (data, metaData, sourceInput) {
      return function (prop) {
        var _a;
        var getFromData = function () {
          return get$1(data, prop);
        };
        var getFromMetaData = function () {
          return get$1(metaData, prop);
        };
        var getNonEmptyValue = function (c) {
          return get$1(c, 'value').bind(function (v) {
            return v.length > 0 ? Optional.some(v) : Optional.none();
          });
        };
        var getFromValueFirst = function () {
          return getFromData().bind(function (child) {
            return isObject(child) ? getNonEmptyValue(child).orThunk(getFromMetaData) : getFromMetaData().orThunk(function () {
              return Optional.from(child);
            });
          });
        };
        var getFromMetaFirst = function () {
          return getFromMetaData().orThunk(function () {
            return getFromData().bind(function (child) {
              return isObject(child) ? getNonEmptyValue(child) : Optional.from(child);
            });
          });
        };
        return _a = {}, _a[prop] = (prop === sourceInput ? getFromValueFirst() : getFromMetaFirst()).getOr(''), _a;
      };
    };
    var getDimensions = function (data, metaData) {
      var dimensions = {};
      get$1(data, 'dimensions').each(function (dims) {
        each$1([
          'width',
          'height'
        ], function (prop) {
          get$1(metaData, prop).orThunk(function () {
            return get$1(dims, prop);
          }).each(function (value) {
            return dimensions[prop] = value;
          });
        });
      });
      return dimensions;
    };
    var unwrap = function (data, sourceInput) {
      var metaData = sourceInput ? extractMeta(sourceInput, data).getOr({}) : {};
      var get = getValue(data, metaData, sourceInput);
      return __assign(__assign(__assign(__assign(__assign({}, get('source')), get('altsource')), get('poster')), get('embed')), getDimensions(data, metaData));
    };
    var wrap = function (data) {
      var wrapped = __assign(__assign({}, data), {
        source: { value: get$1(data, 'source').getOr('') },
        altsource: { value: get$1(data, 'altsource').getOr('') },
        poster: { value: get$1(data, 'poster').getOr('') }
      });
      each$1([
        'width',
        'height'
      ], function (prop) {
        get$1(data, prop).each(function (value) {
          var dimensions = wrapped.dimensions || {};
          dimensions[prop] = value;
          wrapped.dimensions = dimensions;
        });
      });
      return wrapped;
    };
    var handleError = function (editor) {
      return function (error) {
        var errorMessage = error && error.msg ? 'Media embed handler error: ' + error.msg : 'Media embed handler threw unknown error.';
        editor.notificationManager.open({
          type: 'error',
          text: errorMessage
        });
      };
    };
    var snippetToData = function (editor, embedSnippet) {
      return htmlToData(getScripts(editor), embedSnippet);
    };
    var getEditorData = function (editor) {
      var element = editor.selection.getNode();
      var snippet = isMediaElement(element) ? editor.serializer.serialize(element, { selection: true }) : '';
      return __assign({ embed: snippet }, htmlToData(getScripts(editor), snippet));
    };
    var addEmbedHtml = function (api, editor) {
      return function (response) {
        if (isString(response.url) && response.url.trim().length > 0) {
          var html = response.html;
          var snippetData = snippetToData(editor, html);
          var nuData = __assign(__assign({}, snippetData), {
            source: response.url,
            embed: html
          });
          api.setData(wrap(nuData));
        }
      };
    };
    var selectPlaceholder = function (editor, beforeObjects) {
      var afterObjects = editor.dom.select('*[data-mce-object]');
      for (var i = 0; i < beforeObjects.length; i++) {
        for (var y = afterObjects.length - 1; y >= 0; y--) {
          if (beforeObjects[i] === afterObjects[y]) {
            afterObjects.splice(y, 1);
          }
        }
      }
      editor.selection.select(afterObjects[0]);
    };
    var handleInsert = function (editor, html) {
      var beforeObjects = editor.dom.select('*[data-mce-object]');
      editor.insertContent(html);
      selectPlaceholder(editor, beforeObjects);
      editor.nodeChanged();
    };
    var submitForm = function (prevData, newData, editor) {
      newData.embed = updateHtml(newData.embed, newData);
      if (newData.embed && (prevData.source === newData.source || isCached(newData.source))) {
        handleInsert(editor, newData.embed);
      } else {
        getEmbedHtml(editor, newData).then(function (response) {
          handleInsert(editor, response.html);
        }).catch(handleError(editor));
      }
    };
    var showDialog = function (editor) {
      var editorData = getEditorData(editor);
      var currentData = Cell(editorData);
      var initialData = wrap(editorData);
      var handleSource = function (prevData, api) {
        var serviceData = unwrap(api.getData(), 'source');
        if (prevData.source !== serviceData.source) {
          addEmbedHtml(win, editor)({
            url: serviceData.source,
            html: ''
          });
          getEmbedHtml(editor, serviceData).then(addEmbedHtml(win, editor)).catch(handleError(editor));
        }
      };
      var handleEmbed = function (api) {
        var data = unwrap(api.getData());
        var dataFromEmbed = snippetToData(editor, data.embed);
        api.setData(wrap(dataFromEmbed));
      };
      var handleUpdate = function (api, sourceInput) {
        var data = unwrap(api.getData(), sourceInput);
        var embed = dataToHtml(editor, data);
        api.setData(wrap(__assign(__assign({}, data), { embed: embed })));
      };
      var mediaInput = [{
          name: 'source',
          type: 'urlinput',
          filetype: 'media',
          label: 'Source'
        }];
      var sizeInput = !hasDimensions(editor) ? [] : [{
          type: 'sizeinput',
          name: 'dimensions',
          label: 'Constrain proportions',
          constrain: true
        }];
      var generalTab = {
        title: 'General',
        name: 'general',
        items: flatten([
          mediaInput,
          sizeInput
        ])
      };
      var embedTextarea = {
        type: 'textarea',
        name: 'embed',
        label: 'Paste your embed code below:'
      };
      var embedTab = {
        title: 'Embed',
        items: [embedTextarea]
      };
      var advancedFormItems = [];
      if (hasAltSource(editor)) {
        advancedFormItems.push({
          name: 'altsource',
          type: 'urlinput',
          filetype: 'media',
          label: 'Alternative source URL'
        });
      }
      if (hasPoster(editor)) {
        advancedFormItems.push({
          name: 'poster',
          type: 'urlinput',
          filetype: 'image',
          label: 'Media poster (Image URL)'
        });
      }
      var advancedTab = {
        title: 'Advanced',
        name: 'advanced',
        items: advancedFormItems
      };
      var tabs = [
        generalTab,
        embedTab
      ];
      if (advancedFormItems.length > 0) {
        tabs.push(advancedTab);
      }
      var body = {
        type: 'tabpanel',
        tabs: tabs
      };
      var win = editor.windowManager.open({
        title: 'Insert/Edit Media',
        size: 'normal',
        body: body,
        buttons: [
          {
            type: 'cancel',
            name: 'cancel',
            text: 'Cancel'
          },
          {
            type: 'submit',
            name: 'save',
            text: 'Save',
            primary: true
          }
        ],
        onSubmit: function (api) {
          var serviceData = unwrap(api.getData());
          submitForm(currentData.get(), serviceData, editor);
          api.close();
        },
        onChange: function (api, detail) {
          switch (detail.name) {
          case 'source':
            handleSource(currentData.get(), api);
            break;
          case 'embed':
            handleEmbed(api);
            break;
          case 'dimensions':
          case 'altsource':
          case 'poster':
            handleUpdate(api, detail.name);
            break;
          }
          currentData.set(unwrap(api.getData()));
        },
        initialData: initialData
      });
    };

    var get = function (editor) {
      var showDialog$1 = function () {
        showDialog(editor);
      };
      return { showDialog: showDialog$1 };
    };

    var register$1 = function (editor) {
      var showDialog$1 = function () {
        showDialog(editor);
      };
      editor.addCommand('mceMedia', showDialog$1);
    };

    var global$2 = tinymce.util.Tools.resolve('tinymce.html.Node');

    var global$1 = tinymce.util.Tools.resolve('tinymce.Env');

    var global = tinymce.util.Tools.resolve('tinymce.html.DomParser');

    var sanitize = function (editor, html) {
      if (shouldFilterHtml(editor) === false) {
        return html;
      }
      var writer = global$4();
      var blocked;
      global$6({
        validate: false,
        allow_conditional_comments: false,
        comment: function (text) {
          if (!blocked) {
            writer.comment(text);
          }
        },
        cdata: function (text) {
          if (!blocked) {
            writer.cdata(text);
          }
        },
        text: function (text, raw) {
          if (!blocked) {
            writer.text(text, raw);
          }
        },
        start: function (name, attrs, empty) {
          blocked = true;
          if (name === 'script' || name === 'noscript' || name === 'svg') {
            return;
          }
          for (var i = attrs.length - 1; i >= 0; i--) {
            var attrName = attrs[i].name;
            if (attrName.indexOf('on') === 0) {
              delete attrs.map[attrName];
              attrs.splice(i, 1);
            }
            if (attrName === 'style') {
              attrs[i].value = editor.dom.serializeStyle(editor.dom.parseStyle(attrs[i].value), name);
            }
          }
          writer.start(name, attrs, empty);
          blocked = false;
        },
        end: function (name) {
          if (blocked) {
            return;
          }
          writer.end(name);
        }
      }, global$5({})).parse(html);
      return writer.getContent();
    };

    var isLiveEmbedNode = function (node) {
      var name = node.name;
      return name === 'iframe' || name === 'video' || name === 'audio';
    };
    var getDimension = function (node, styles, dimension, defaultValue) {
      if (defaultValue === void 0) {
        defaultValue = null;
      }
      var value = node.attr(dimension);
      if (isNonNullable(value)) {
        return value;
      } else if (!has(styles, dimension)) {
        return defaultValue;
      } else {
        return null;
      }
    };
    var setDimensions = function (node, previewNode, styles) {
      var useDefaults = previewNode.name === 'img' || node.name === 'video';
      var defaultWidth = useDefaults ? '300' : null;
      var fallbackHeight = node.name === 'audio' ? '30' : '150';
      var defaultHeight = useDefaults ? fallbackHeight : null;
      previewNode.attr({
        width: getDimension(node, styles, 'width', defaultWidth),
        height: getDimension(node, styles, 'height', defaultHeight)
      });
    };
    var appendNodeContent = function (editor, nodeName, previewNode, html) {
      var newNode = global({
        forced_root_block: false,
        validate: false
      }, editor.schema).parse(html, { context: nodeName });
      while (newNode.firstChild) {
        previewNode.append(newNode.firstChild);
      }
    };
    var createPlaceholderNode = function (editor, node) {
      var name = node.name;
      var placeHolder = new global$2('img', 1);
      placeHolder.shortEnded = true;
      retainAttributesAndInnerHtml(editor, node, placeHolder);
      setDimensions(node, placeHolder, {});
      placeHolder.attr({
        'style': node.attr('style'),
        'src': global$1.transparentSrc,
        'data-mce-object': name,
        'class': 'mce-object mce-object-' + name
      });
      return placeHolder;
    };
    var createPreviewNode = function (editor, node) {
      var name = node.name;
      var previewWrapper = new global$2('span', 1);
      previewWrapper.attr({
        'contentEditable': 'false',
        'style': node.attr('style'),
        'data-mce-object': name,
        'class': 'mce-preview-object mce-object-' + name
      });
      retainAttributesAndInnerHtml(editor, node, previewWrapper);
      var styles = editor.dom.parseStyle(node.attr('style'));
      var previewNode = new global$2(name, 1);
      setDimensions(node, previewNode, styles);
      previewNode.attr({
        src: node.attr('src'),
        style: node.attr('style'),
        class: node.attr('class')
      });
      if (name === 'iframe') {
        previewNode.attr({
          allowfullscreen: node.attr('allowfullscreen'),
          frameborder: '0'
        });
      } else {
        var attrs = [
          'controls',
          'crossorigin',
          'currentTime',
          'loop',
          'muted',
          'poster',
          'preload'
        ];
        each$1(attrs, function (attrName) {
          previewNode.attr(attrName, node.attr(attrName));
        });
        var sanitizedHtml = previewWrapper.attr('data-mce-html');
        if (isNonNullable(sanitizedHtml)) {
          appendNodeContent(editor, name, previewNode, unescape(sanitizedHtml));
        }
      }
      var shimNode = new global$2('span', 1);
      shimNode.attr('class', 'mce-shim');
      previewWrapper.append(previewNode);
      previewWrapper.append(shimNode);
      return previewWrapper;
    };
    var retainAttributesAndInnerHtml = function (editor, sourceNode, targetNode) {
      var attribs = sourceNode.attributes;
      var ai = attribs.length;
      while (ai--) {
        var attrName = attribs[ai].name;
        var attrValue = attribs[ai].value;
        if (attrName !== 'width' && attrName !== 'height' && attrName !== 'style') {
          if (attrName === 'data' || attrName === 'src') {
            attrValue = editor.convertURL(attrValue, attrName);
          }
          targetNode.attr('data-mce-p-' + attrName, attrValue);
        }
      }
      var innerHtml = sourceNode.firstChild && sourceNode.firstChild.value;
      if (innerHtml) {
        targetNode.attr('data-mce-html', escape(sanitize(editor, innerHtml)));
        targetNode.firstChild = null;
      }
    };
    var isPageEmbedWrapper = function (node) {
      var nodeClass = node.attr('class');
      return nodeClass && /\btiny-pageembed\b/.test(nodeClass);
    };
    var isWithinEmbedWrapper = function (node) {
      while (node = node.parent) {
        if (node.attr('data-ephox-embed-iri') || isPageEmbedWrapper(node)) {
          return true;
        }
      }
      return false;
    };
    var placeHolderConverter = function (editor) {
      return function (nodes) {
        var i = nodes.length;
        var node;
        var videoScript;
        while (i--) {
          node = nodes[i];
          if (!node.parent) {
            continue;
          }
          if (node.parent.attr('data-mce-object')) {
            continue;
          }
          if (node.name === 'script') {
            videoScript = getVideoScriptMatch(getScripts(editor), node.attr('src'));
            if (!videoScript) {
              continue;
            }
          }
          if (videoScript) {
            if (videoScript.width) {
              node.attr('width', videoScript.width.toString());
            }
            if (videoScript.height) {
              node.attr('height', videoScript.height.toString());
            }
          }
          if (isLiveEmbedNode(node) && hasLiveEmbeds(editor) && global$1.ceFalse) {
            if (!isWithinEmbedWrapper(node)) {
              node.replace(createPreviewNode(editor, node));
            }
          } else {
            if (!isWithinEmbedWrapper(node)) {
              node.replace(createPlaceholderNode(editor, node));
            }
          }
        }
      };
    };

    var setup$1 = function (editor) {
      editor.on('preInit', function () {
        var specialElements = editor.schema.getSpecialElements();
        global$8.each('video audio iframe object'.split(' '), function (name) {
          specialElements[name] = new RegExp('</' + name + '[^>]*>', 'gi');
        });
        var boolAttrs = editor.schema.getBoolAttrs();
        global$8.each('webkitallowfullscreen mozallowfullscreen allowfullscreen'.split(' '), function (name) {
          boolAttrs[name] = {};
        });
        editor.parser.addNodeFilter('iframe,video,audio,object,embed,script', placeHolderConverter(editor));
        editor.serializer.addAttributeFilter('data-mce-object', function (nodes, name) {
          var i = nodes.length;
          var node;
          var realElm;
          var ai;
          var attribs;
          var innerHtml;
          var innerNode;
          var realElmName;
          var className;
          while (i--) {
            node = nodes[i];
            if (!node.parent) {
              continue;
            }
            realElmName = node.attr(name);
            realElm = new global$2(realElmName, 1);
            if (realElmName !== 'audio' && realElmName !== 'script') {
              className = node.attr('class');
              if (className && className.indexOf('mce-preview-object') !== -1) {
                realElm.attr({
                  width: node.firstChild.attr('width'),
                  height: node.firstChild.attr('height')
                });
              } else {
                realElm.attr({
                  width: node.attr('width'),
                  height: node.attr('height')
                });
              }
            }
            realElm.attr({ style: node.attr('style') });
            attribs = node.attributes;
            ai = attribs.length;
            while (ai--) {
              var attrName = attribs[ai].name;
              if (attrName.indexOf('data-mce-p-') === 0) {
                realElm.attr(attrName.substr(11), attribs[ai].value);
              }
            }
            if (realElmName === 'script') {
              realElm.attr('type', 'text/javascript');
            }
            innerHtml = node.attr('data-mce-html');
            if (innerHtml) {
              innerNode = new global$2('#text', 3);
              innerNode.raw = true;
              innerNode.value = sanitize(editor, unescape(innerHtml));
              realElm.append(innerNode);
            }
            node.replace(realElm);
          }
        });
      });
      editor.on('SetContent', function () {
        editor.$('span.mce-preview-object').each(function (index, elm) {
          var $elm = editor.$(elm);
          if ($elm.find('span.mce-shim').length === 0) {
            $elm.append('<span class="mce-shim"></span>');
          }
        });
      });
    };

    var setup = function (editor) {
      editor.on('ResolveName', function (e) {
        var name;
        if (e.target.nodeType === 1 && (name = e.target.getAttribute('data-mce-object'))) {
          e.name = name;
        }
      });
    };

    var register = function (editor) {
      var onAction = function () {
        return editor.execCommand('mceMedia');
      };
      editor.ui.registry.addToggleButton('media', {
        tooltip: 'Insert/edit media',
        icon: 'embed',
        onAction: onAction,
        onSetup: function (buttonApi) {
          var selection = editor.selection;
          buttonApi.setActive(isMediaElement(selection.getNode()));
          return selection.selectorChangedWithUnbind('img[data-mce-object],span[data-mce-object],div[data-ephox-embed-iri]', buttonApi.setActive).unbind;
        }
      });
      editor.ui.registry.addMenuItem('media', {
        icon: 'embed',
        text: 'Media...',
        onAction: onAction
      });
    };

    function Plugin () {
      global$9.add('media', function (editor) {
        register$1(editor);
        register(editor);
        setup(editor);
        setup$1(editor);
        setup$2(editor);
        return get(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/noneditable/index.js":
/*!***********************************************************!*\
  !*** ./node_modules/tinymce/plugins/noneditable/index.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "noneditable" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/noneditable')
//   ES2015:
//     import 'tinymce/plugins/noneditable'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/noneditable/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/noneditable/plugin.js":
/*!************************************************************!*\
  !*** ./node_modules/tinymce/plugins/noneditable/plugin.js ***!
  \************************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$1 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var global = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var getNonEditableClass = function (editor) {
      return editor.getParam('noneditable_noneditable_class', 'mceNonEditable');
    };
    var getEditableClass = function (editor) {
      return editor.getParam('noneditable_editable_class', 'mceEditable');
    };
    var getNonEditableRegExps = function (editor) {
      var nonEditableRegExps = editor.getParam('noneditable_regexp', []);
      if (nonEditableRegExps && nonEditableRegExps.constructor === RegExp) {
        return [nonEditableRegExps];
      } else {
        return nonEditableRegExps;
      }
    };

    var hasClass = function (checkClassName) {
      return function (node) {
        return (' ' + node.attr('class') + ' ').indexOf(checkClassName) !== -1;
      };
    };
    var replaceMatchWithSpan = function (editor, content, cls) {
      return function (match) {
        var args = arguments, index = args[args.length - 2];
        var prevChar = index > 0 ? content.charAt(index - 1) : '';
        if (prevChar === '"') {
          return match;
        }
        if (prevChar === '>') {
          var findStartTagIndex = content.lastIndexOf('<', index);
          if (findStartTagIndex !== -1) {
            var tagHtml = content.substring(findStartTagIndex, index);
            if (tagHtml.indexOf('contenteditable="false"') !== -1) {
              return match;
            }
          }
        }
        return '<span class="' + cls + '" data-mce-content="' + editor.dom.encode(args[0]) + '">' + editor.dom.encode(typeof args[1] === 'string' ? args[1] : args[0]) + '</span>';
      };
    };
    var convertRegExpsToNonEditable = function (editor, nonEditableRegExps, e) {
      var i = nonEditableRegExps.length, content = e.content;
      if (e.format === 'raw') {
        return;
      }
      while (i--) {
        content = content.replace(nonEditableRegExps[i], replaceMatchWithSpan(editor, content, getNonEditableClass(editor)));
      }
      e.content = content;
    };
    var setup = function (editor) {
      var contentEditableAttrName = 'contenteditable';
      var editClass = ' ' + global.trim(getEditableClass(editor)) + ' ';
      var nonEditClass = ' ' + global.trim(getNonEditableClass(editor)) + ' ';
      var hasEditClass = hasClass(editClass);
      var hasNonEditClass = hasClass(nonEditClass);
      var nonEditableRegExps = getNonEditableRegExps(editor);
      editor.on('PreInit', function () {
        if (nonEditableRegExps.length > 0) {
          editor.on('BeforeSetContent', function (e) {
            convertRegExpsToNonEditable(editor, nonEditableRegExps, e);
          });
        }
        editor.parser.addAttributeFilter('class', function (nodes) {
          var i = nodes.length, node;
          while (i--) {
            node = nodes[i];
            if (hasEditClass(node)) {
              node.attr(contentEditableAttrName, 'true');
            } else if (hasNonEditClass(node)) {
              node.attr(contentEditableAttrName, 'false');
            }
          }
        });
        editor.serializer.addAttributeFilter(contentEditableAttrName, function (nodes) {
          var i = nodes.length, node;
          while (i--) {
            node = nodes[i];
            if (!hasEditClass(node) && !hasNonEditClass(node)) {
              continue;
            }
            if (nonEditableRegExps.length > 0 && node.attr('data-mce-content')) {
              node.name = '#text';
              node.type = 3;
              node.raw = true;
              node.value = node.attr('data-mce-content');
            } else {
              node.attr(contentEditableAttrName, null);
            }
          }
        });
      });
    };

    function Plugin () {
      global$1.add('noneditable', function (editor) {
        setup(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/paste/index.js":
/*!*****************************************************!*\
  !*** ./node_modules/tinymce/plugins/paste/index.js ***!
  \*****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "paste" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/paste')
//   ES2015:
//     import 'tinymce/plugins/paste'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/paste/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/paste/plugin.js":
/*!******************************************************!*\
  !*** ./node_modules/tinymce/plugins/paste/plugin.js ***!
  \******************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var global$b = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var hasProPlugin = function (editor) {
      if (editor.hasPlugin('powerpaste', true)) {
        if (typeof window.console !== 'undefined' && window.console.log) {
          window.console.log('PowerPaste is incompatible with Paste plugin! Remove \'paste\' from the \'plugins\' option.');
        }
        return true;
      } else {
        return false;
      }
    };

    var get = function (clipboard) {
      return { clipboard: clipboard };
    };

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var isArray = isType('array');
    var isNullable = function (a) {
      return a === null || a === undefined;
    };
    var isNonNullable = function (a) {
      return !isNullable(a);
    };
    var isFunction = isSimpleType('function');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from$1 = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from$1
    };

    var nativeSlice = Array.prototype.slice;
    var nativePush = Array.prototype.push;
    var exists = function (xs, pred) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          return true;
        }
      }
      return false;
    };
    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var filter$1 = function (xs, pred) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          r.push(x);
        }
      }
      return r;
    };
    var foldl = function (xs, f, acc) {
      each(xs, function (x, i) {
        acc = f(acc, x, i);
      });
      return acc;
    };
    var flatten = function (xs) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; ++i) {
        if (!isArray(xs[i])) {
          throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
        }
        nativePush.apply(r, xs[i]);
      }
      return r;
    };
    var bind = function (xs, f) {
      return flatten(map(xs, f));
    };
    var from = isFunction(Array.from) ? Array.from : function (x) {
      return nativeSlice.call(x);
    };

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };

    var singleton = function (doRevoke) {
      var subject = Cell(Optional.none());
      var revoke = function () {
        return subject.get().each(doRevoke);
      };
      var clear = function () {
        revoke();
        subject.set(Optional.none());
      };
      var isSet = function () {
        return subject.get().isSome();
      };
      var get = function () {
        return subject.get();
      };
      var set = function (s) {
        revoke();
        subject.set(Optional.some(s));
      };
      return {
        clear: clear,
        isSet: isSet,
        get: get,
        set: set
      };
    };
    var value = function () {
      var subject = singleton(noop);
      var on = function (f) {
        return subject.get().each(f);
      };
      return __assign(__assign({}, subject), { on: on });
    };

    var checkRange = function (str, substr, start) {
      return substr === '' || str.length >= substr.length && str.substr(start, start + substr.length) === substr;
    };
    var startsWith = function (str, prefix) {
      return checkRange(str, prefix, 0);
    };
    var endsWith = function (str, suffix) {
      return checkRange(str, suffix, str.length - suffix.length);
    };
    var repeat = function (s, count) {
      return count <= 0 ? '' : new Array(count + 1).join(s);
    };

    var global$a = tinymce.util.Tools.resolve('tinymce.Env');

    var global$9 = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var global$8 = tinymce.util.Tools.resolve('tinymce.util.Promise');

    var global$7 = tinymce.util.Tools.resolve('tinymce.util.VK');

    var firePastePreProcess = function (editor, html, internal, isWordHtml) {
      return editor.fire('PastePreProcess', {
        content: html,
        internal: internal,
        wordContent: isWordHtml
      });
    };
    var firePastePostProcess = function (editor, node, internal, isWordHtml) {
      return editor.fire('PastePostProcess', {
        node: node,
        internal: internal,
        wordContent: isWordHtml
      });
    };
    var firePastePlainTextToggle = function (editor, state) {
      return editor.fire('PastePlainTextToggle', { state: state });
    };
    var firePaste = function (editor, ieFake) {
      return editor.fire('paste', { ieFake: ieFake });
    };

    var global$6 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var shouldBlockDrop = function (editor) {
      return editor.getParam('paste_block_drop', false);
    };
    var shouldPasteDataImages = function (editor) {
      return editor.getParam('paste_data_images', false);
    };
    var shouldFilterDrop = function (editor) {
      return editor.getParam('paste_filter_drop', true);
    };
    var getPreProcess = function (editor) {
      return editor.getParam('paste_preprocess');
    };
    var getPostProcess = function (editor) {
      return editor.getParam('paste_postprocess');
    };
    var getWebkitStyles = function (editor) {
      return editor.getParam('paste_webkit_styles');
    };
    var shouldRemoveWebKitStyles = function (editor) {
      return editor.getParam('paste_remove_styles_if_webkit', true);
    };
    var shouldMergeFormats = function (editor) {
      return editor.getParam('paste_merge_formats', true);
    };
    var isSmartPasteEnabled = function (editor) {
      return editor.getParam('smart_paste', true);
    };
    var isPasteAsTextEnabled = function (editor) {
      return editor.getParam('paste_as_text', false);
    };
    var getRetainStyleProps = function (editor) {
      return editor.getParam('paste_retain_style_properties');
    };
    var getWordValidElements = function (editor) {
      var defaultValidElements = '-strong/b,-em/i,-u,-span,-p,-ol,-ul,-li,-h1,-h2,-h3,-h4,-h5,-h6,' + '-p/div,-a[href|name],sub,sup,strike,br,del,table[width],tr,' + 'td[colspan|rowspan|width],th[colspan|rowspan|width],thead,tfoot,tbody';
      return editor.getParam('paste_word_valid_elements', defaultValidElements);
    };
    var shouldConvertWordFakeLists = function (editor) {
      return editor.getParam('paste_convert_word_fake_lists', true);
    };
    var shouldUseDefaultFilters = function (editor) {
      return editor.getParam('paste_enable_default_filters', true);
    };
    var getValidate = function (editor) {
      return editor.getParam('validate');
    };
    var getAllowHtmlDataUrls = function (editor) {
      return editor.getParam('allow_html_data_urls', false, 'boolean');
    };
    var getPasteDataImages = function (editor) {
      return editor.getParam('paste_data_images', false, 'boolean');
    };
    var getImagesDataImgFilter = function (editor) {
      return editor.getParam('images_dataimg_filter');
    };
    var getImagesReuseFilename = function (editor) {
      return editor.getParam('images_reuse_filename');
    };
    var getForcedRootBlock = function (editor) {
      return editor.getParam('forced_root_block');
    };
    var getForcedRootBlockAttrs = function (editor) {
      return editor.getParam('forced_root_block_attrs');
    };
    var getTabSpaces = function (editor) {
      return editor.getParam('paste_tab_spaces', 4, 'number');
    };
    var getAllowedImageFileTypes = function (editor) {
      var defaultImageFileTypes = 'jpeg,jpg,jpe,jfi,jif,jfif,png,gif,bmp,webp';
      return global$6.explode(editor.getParam('images_file_types', defaultImageFileTypes, 'string'));
    };

    var internalMimeType = 'x-tinymce/html';
    var internalMark = '<!-- ' + internalMimeType + ' -->';
    var mark = function (html) {
      return internalMark + html;
    };
    var unmark = function (html) {
      return html.replace(internalMark, '');
    };
    var isMarked = function (html) {
      return html.indexOf(internalMark) !== -1;
    };
    var internalHtmlMime = constant(internalMimeType);

    var hasOwnProperty = Object.hasOwnProperty;
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };

    var global$5 = tinymce.util.Tools.resolve('tinymce.html.Entities');

    var isPlainText = function (text) {
      return !/<(?:\/?(?!(?:div|p|br|span)>)\w+|(?:(?!(?:span style="white-space:\s?pre;?">)|br\s?\/>))\w+\s[^>]+)>/i.test(text);
    };
    var toBRs = function (text) {
      return text.replace(/\r?\n/g, '<br>');
    };
    var openContainer = function (rootTag, rootAttrs) {
      var attrs = [];
      var tag = '<' + rootTag;
      if (typeof rootAttrs === 'object') {
        for (var key in rootAttrs) {
          if (has(rootAttrs, key)) {
            attrs.push(key + '="' + global$5.encodeAllRaw(rootAttrs[key]) + '"');
          }
        }
        if (attrs.length) {
          tag += ' ' + attrs.join(' ');
        }
      }
      return tag + '>';
    };
    var toBlockElements = function (text, rootTag, rootAttrs) {
      var blocks = text.split(/\n\n/);
      var tagOpen = openContainer(rootTag, rootAttrs);
      var tagClose = '</' + rootTag + '>';
      var paragraphs = global$6.map(blocks, function (p) {
        return p.split(/\n/).join('<br />');
      });
      var stitch = function (p) {
        return tagOpen + p + tagClose;
      };
      return paragraphs.length === 1 ? paragraphs[0] : global$6.map(paragraphs, stitch).join('');
    };
    var convert = function (text, rootTag, rootAttrs) {
      return rootTag ? toBlockElements(text, rootTag === true ? 'p' : rootTag, rootAttrs) : toBRs(text);
    };

    var global$4 = tinymce.util.Tools.resolve('tinymce.html.DomParser');

    var global$3 = tinymce.util.Tools.resolve('tinymce.html.Serializer');

    var nbsp = '\xA0';

    var global$2 = tinymce.util.Tools.resolve('tinymce.html.Node');

    var global$1 = tinymce.util.Tools.resolve('tinymce.html.Schema');

    var isRegExp = function (val) {
      return val.constructor === RegExp;
    };
    var filter = function (content, items) {
      global$6.each(items, function (v) {
        if (isRegExp(v)) {
          content = content.replace(v, '');
        } else {
          content = content.replace(v[0], v[1]);
        }
      });
      return content;
    };
    var innerText = function (html) {
      var schema = global$1();
      var domParser = global$4({}, schema);
      var text = '';
      var shortEndedElements = schema.getShortEndedElements();
      var ignoreElements = global$6.makeMap('script noscript style textarea video audio iframe object', ' ');
      var blockElements = schema.getBlockElements();
      var walk = function (node) {
        var name = node.name, currentNode = node;
        if (name === 'br') {
          text += '\n';
          return;
        }
        if (name === 'wbr') {
          return;
        }
        if (shortEndedElements[name]) {
          text += ' ';
        }
        if (ignoreElements[name]) {
          text += ' ';
          return;
        }
        if (node.type === 3) {
          text += node.value;
        }
        if (!node.shortEnded) {
          if (node = node.firstChild) {
            do {
              walk(node);
            } while (node = node.next);
          }
        }
        if (blockElements[name] && currentNode.next) {
          text += '\n';
          if (name === 'p') {
            text += '\n';
          }
        }
      };
      html = filter(html, [/<!\[[^\]]+\]>/g]);
      walk(domParser.parse(html));
      return text;
    };
    var trimHtml = function (html) {
      var trimSpaces = function (all, s1, s2) {
        if (!s1 && !s2) {
          return ' ';
        }
        return nbsp;
      };
      html = filter(html, [
        /^[\s\S]*<body[^>]*>\s*|\s*<\/body[^>]*>[\s\S]*$/ig,
        /<!--StartFragment-->|<!--EndFragment-->/g,
        [
          /( ?)<span class="Apple-converted-space">\u00a0<\/span>( ?)/g,
          trimSpaces
        ],
        /<br class="Apple-interchange-newline">/g,
        /<br>$/i
      ]);
      return html;
    };
    var createIdGenerator = function (prefix) {
      var count = 0;
      return function () {
        return prefix + count++;
      };
    };
    var getImageMimeType = function (ext) {
      var lowerExt = ext.toLowerCase();
      var mimeOverrides = {
        jpg: 'jpeg',
        jpe: 'jpeg',
        jfi: 'jpeg',
        jif: 'jpeg',
        jfif: 'jpeg',
        pjpeg: 'jpeg',
        pjp: 'jpeg',
        svg: 'svg+xml'
      };
      return global$6.hasOwn(mimeOverrides, lowerExt) ? 'image/' + mimeOverrides[lowerExt] : 'image/' + lowerExt;
    };

    var isWordContent = function (content) {
      return /<font face="Times New Roman"|class="?Mso|style="[^"]*\bmso-|style='[^']*\bmso-|w:WordDocument/i.test(content) || /class="OutlineElement/.test(content) || /id="?docs\-internal\-guid\-/.test(content);
    };
    var isNumericList = function (text) {
      var found = false;
      var patterns = [
        /^[IVXLMCD]+\.[ \u00a0]/,
        /^[ivxlmcd]+\.[ \u00a0]/,
        /^[a-z]{1,2}[\.\)][ \u00a0]/,
        /^[A-Z]{1,2}[\.\)][ \u00a0]/,
        /^[0-9]+\.[ \u00a0]/,
        /^[\u3007\u4e00\u4e8c\u4e09\u56db\u4e94\u516d\u4e03\u516b\u4e5d]+\.[ \u00a0]/,
        /^[\u58f1\u5f10\u53c2\u56db\u4f0d\u516d\u4e03\u516b\u4e5d\u62fe]+\.[ \u00a0]/
      ];
      text = text.replace(/^[\u00a0 ]+/, '');
      global$6.each(patterns, function (pattern) {
        if (pattern.test(text)) {
          found = true;
          return false;
        }
      });
      return found;
    };
    var isBulletList = function (text) {
      return /^[\s\u00a0]*[\u2022\u00b7\u00a7\u25CF]\s*/.test(text);
    };
    var convertFakeListsToProperLists = function (node) {
      var currentListNode, prevListNode, lastLevel = 1;
      var getText = function (node) {
        var txt = '';
        if (node.type === 3) {
          return node.value;
        }
        if (node = node.firstChild) {
          do {
            txt += getText(node);
          } while (node = node.next);
        }
        return txt;
      };
      var trimListStart = function (node, regExp) {
        if (node.type === 3) {
          if (regExp.test(node.value)) {
            node.value = node.value.replace(regExp, '');
            return false;
          }
        }
        if (node = node.firstChild) {
          do {
            if (!trimListStart(node, regExp)) {
              return false;
            }
          } while (node = node.next);
        }
        return true;
      };
      var removeIgnoredNodes = function (node) {
        if (node._listIgnore) {
          node.remove();
          return;
        }
        if (node = node.firstChild) {
          do {
            removeIgnoredNodes(node);
          } while (node = node.next);
        }
      };
      var convertParagraphToLi = function (paragraphNode, listName, start) {
        var level = paragraphNode._listLevel || lastLevel;
        if (level !== lastLevel) {
          if (level < lastLevel) {
            if (currentListNode) {
              currentListNode = currentListNode.parent.parent;
            }
          } else {
            prevListNode = currentListNode;
            currentListNode = null;
          }
        }
        if (!currentListNode || currentListNode.name !== listName) {
          prevListNode = prevListNode || currentListNode;
          currentListNode = new global$2(listName, 1);
          if (start > 1) {
            currentListNode.attr('start', '' + start);
          }
          paragraphNode.wrap(currentListNode);
        } else {
          currentListNode.append(paragraphNode);
        }
        paragraphNode.name = 'li';
        if (level > lastLevel && prevListNode) {
          prevListNode.lastChild.append(currentListNode);
        }
        lastLevel = level;
        removeIgnoredNodes(paragraphNode);
        trimListStart(paragraphNode, /^\u00a0+/);
        trimListStart(paragraphNode, /^\s*([\u2022\u00b7\u00a7\u25CF]|\w+\.)/);
        trimListStart(paragraphNode, /^\u00a0+/);
      };
      var elements = [];
      var child = node.firstChild;
      while (typeof child !== 'undefined' && child !== null) {
        elements.push(child);
        child = child.walk();
        if (child !== null) {
          while (typeof child !== 'undefined' && child.parent !== node) {
            child = child.walk();
          }
        }
      }
      for (var i = 0; i < elements.length; i++) {
        node = elements[i];
        if (node.name === 'p' && node.firstChild) {
          var nodeText = getText(node);
          if (isBulletList(nodeText)) {
            convertParagraphToLi(node, 'ul');
            continue;
          }
          if (isNumericList(nodeText)) {
            var matches = /([0-9]+)\./.exec(nodeText);
            var start = 1;
            if (matches) {
              start = parseInt(matches[1], 10);
            }
            convertParagraphToLi(node, 'ol', start);
            continue;
          }
          if (node._listLevel) {
            convertParagraphToLi(node, 'ul', 1);
            continue;
          }
          currentListNode = null;
        } else {
          prevListNode = currentListNode;
          currentListNode = null;
        }
      }
    };
    var filterStyles = function (editor, validStyles, node, styleValue) {
      var outputStyles = {};
      var styles = editor.dom.parseStyle(styleValue);
      global$6.each(styles, function (value, name) {
        switch (name) {
        case 'mso-list':
          var matches = /\w+ \w+([0-9]+)/i.exec(styleValue);
          if (matches) {
            node._listLevel = parseInt(matches[1], 10);
          }
          if (/Ignore/i.test(value) && node.firstChild) {
            node._listIgnore = true;
            node.firstChild._listIgnore = true;
          }
          break;
        case 'horiz-align':
          name = 'text-align';
          break;
        case 'vert-align':
          name = 'vertical-align';
          break;
        case 'font-color':
        case 'mso-foreground':
          name = 'color';
          break;
        case 'mso-background':
        case 'mso-highlight':
          name = 'background';
          break;
        case 'font-weight':
        case 'font-style':
          if (value !== 'normal') {
            outputStyles[name] = value;
          }
          return;
        case 'mso-element':
          if (/^(comment|comment-list)$/i.test(value)) {
            node.remove();
            return;
          }
          break;
        }
        if (name.indexOf('mso-comment') === 0) {
          node.remove();
          return;
        }
        if (name.indexOf('mso-') === 0) {
          return;
        }
        if (getRetainStyleProps(editor) === 'all' || validStyles && validStyles[name]) {
          outputStyles[name] = value;
        }
      });
      if (/(bold)/i.test(outputStyles['font-weight'])) {
        delete outputStyles['font-weight'];
        node.wrap(new global$2('b', 1));
      }
      if (/(italic)/i.test(outputStyles['font-style'])) {
        delete outputStyles['font-style'];
        node.wrap(new global$2('i', 1));
      }
      var outputStyle = editor.dom.serializeStyle(outputStyles, node.name);
      if (outputStyle) {
        return outputStyle;
      }
      return null;
    };
    var filterWordContent = function (editor, content) {
      var validStyles;
      var retainStyleProperties = getRetainStyleProps(editor);
      if (retainStyleProperties) {
        validStyles = global$6.makeMap(retainStyleProperties.split(/[, ]/));
      }
      content = filter(content, [
        /<br class="?Apple-interchange-newline"?>/gi,
        /<b[^>]+id="?docs-internal-[^>]*>/gi,
        /<!--[\s\S]+?-->/gi,
        /<(!|script[^>]*>.*?<\/script(?=[>\s])|\/?(\?xml(:\w+)?|img|meta|link|style|\w:\w+)(?=[\s\/>]))[^>]*>/gi,
        [
          /<(\/?)s>/gi,
          '<$1strike>'
        ],
        [
          /&nbsp;/gi,
          nbsp
        ],
        [
          /<span\s+style\s*=\s*"\s*mso-spacerun\s*:\s*yes\s*;?\s*"\s*>([\s\u00a0]*)<\/span>/gi,
          function (str, spaces) {
            return spaces.length > 0 ? spaces.replace(/./, ' ').slice(Math.floor(spaces.length / 2)).split('').join(nbsp) : '';
          }
        ]
      ]);
      var validElements = getWordValidElements(editor);
      var schema = global$1({
        valid_elements: validElements,
        valid_children: '-li[p]'
      });
      global$6.each(schema.elements, function (rule) {
        if (!rule.attributes.class) {
          rule.attributes.class = {};
          rule.attributesOrder.push('class');
        }
        if (!rule.attributes.style) {
          rule.attributes.style = {};
          rule.attributesOrder.push('style');
        }
      });
      var domParser = global$4({}, schema);
      domParser.addAttributeFilter('style', function (nodes) {
        var i = nodes.length, node;
        while (i--) {
          node = nodes[i];
          node.attr('style', filterStyles(editor, validStyles, node, node.attr('style')));
          if (node.name === 'span' && node.parent && !node.attributes.length) {
            node.unwrap();
          }
        }
      });
      domParser.addAttributeFilter('class', function (nodes) {
        var i = nodes.length, node, className;
        while (i--) {
          node = nodes[i];
          className = node.attr('class');
          if (/^(MsoCommentReference|MsoCommentText|msoDel)$/i.test(className)) {
            node.remove();
          }
          node.attr('class', null);
        }
      });
      domParser.addNodeFilter('del', function (nodes) {
        var i = nodes.length;
        while (i--) {
          nodes[i].remove();
        }
      });
      domParser.addNodeFilter('a', function (nodes) {
        var i = nodes.length, node, href, name;
        while (i--) {
          node = nodes[i];
          href = node.attr('href');
          name = node.attr('name');
          if (href && href.indexOf('#_msocom_') !== -1) {
            node.remove();
            continue;
          }
          if (href && href.indexOf('file://') === 0) {
            href = href.split('#')[1];
            if (href) {
              href = '#' + href;
            }
          }
          if (!href && !name) {
            node.unwrap();
          } else {
            if (name && !/^_?(?:toc|edn|ftn)/i.test(name)) {
              node.unwrap();
              continue;
            }
            node.attr({
              href: href,
              name: name
            });
          }
        }
      });
      var rootNode = domParser.parse(content);
      if (shouldConvertWordFakeLists(editor)) {
        convertFakeListsToProperLists(rootNode);
      }
      content = global$3({ validate: getValidate(editor) }, schema).serialize(rootNode);
      return content;
    };
    var preProcess$1 = function (editor, content) {
      return shouldUseDefaultFilters(editor) ? filterWordContent(editor, content) : content;
    };

    var preProcess = function (editor, html) {
      var parser = global$4({}, editor.schema);
      parser.addNodeFilter('meta', function (nodes) {
        global$6.each(nodes, function (node) {
          node.remove();
        });
      });
      var fragment = parser.parse(html, {
        forced_root_block: false,
        isRootContent: true
      });
      return global$3({ validate: getValidate(editor) }, editor.schema).serialize(fragment);
    };
    var processResult = function (content, cancelled) {
      return {
        content: content,
        cancelled: cancelled
      };
    };
    var postProcessFilter = function (editor, html, internal, isWordHtml) {
      var tempBody = editor.dom.create('div', { style: 'display:none' }, html);
      var postProcessArgs = firePastePostProcess(editor, tempBody, internal, isWordHtml);
      return processResult(postProcessArgs.node.innerHTML, postProcessArgs.isDefaultPrevented());
    };
    var filterContent = function (editor, content, internal, isWordHtml) {
      var preProcessArgs = firePastePreProcess(editor, content, internal, isWordHtml);
      var filteredContent = preProcess(editor, preProcessArgs.content);
      if (editor.hasEventListeners('PastePostProcess') && !preProcessArgs.isDefaultPrevented()) {
        return postProcessFilter(editor, filteredContent, internal, isWordHtml);
      } else {
        return processResult(filteredContent, preProcessArgs.isDefaultPrevented());
      }
    };
    var process = function (editor, html, internal) {
      var isWordHtml = isWordContent(html);
      var content = isWordHtml ? preProcess$1(editor, html) : html;
      return filterContent(editor, content, internal, isWordHtml);
    };

    var pasteHtml$1 = function (editor, html) {
      editor.insertContent(html, {
        merge: shouldMergeFormats(editor),
        paste: true
      });
      return true;
    };
    var isAbsoluteUrl = function (url) {
      return /^https?:\/\/[\w\-\/+=.,!;:&%@^~(){}?#]+$/i.test(url);
    };
    var isImageUrl = function (editor, url) {
      return isAbsoluteUrl(url) && exists(getAllowedImageFileTypes(editor), function (type) {
        return endsWith(url.toLowerCase(), '.' + type.toLowerCase());
      });
    };
    var createImage = function (editor, url, pasteHtmlFn) {
      editor.undoManager.extra(function () {
        pasteHtmlFn(editor, url);
      }, function () {
        editor.insertContent('<img src="' + url + '">');
      });
      return true;
    };
    var createLink = function (editor, url, pasteHtmlFn) {
      editor.undoManager.extra(function () {
        pasteHtmlFn(editor, url);
      }, function () {
        editor.execCommand('mceInsertLink', false, url);
      });
      return true;
    };
    var linkSelection = function (editor, html, pasteHtmlFn) {
      return editor.selection.isCollapsed() === false && isAbsoluteUrl(html) ? createLink(editor, html, pasteHtmlFn) : false;
    };
    var insertImage = function (editor, html, pasteHtmlFn) {
      return isImageUrl(editor, html) ? createImage(editor, html, pasteHtmlFn) : false;
    };
    var smartInsertContent = function (editor, html) {
      global$6.each([
        linkSelection,
        insertImage,
        pasteHtml$1
      ], function (action) {
        return action(editor, html, pasteHtml$1) !== true;
      });
    };
    var insertContent = function (editor, html, pasteAsText) {
      if (pasteAsText || isSmartPasteEnabled(editor) === false) {
        pasteHtml$1(editor, html);
      } else {
        smartInsertContent(editor, html);
      }
    };

    var isCollapsibleWhitespace = function (c) {
      return ' \f\t\x0B'.indexOf(c) !== -1;
    };
    var isNewLineChar = function (c) {
      return c === '\n' || c === '\r';
    };
    var isNewline = function (text, idx) {
      return idx < text.length && idx >= 0 ? isNewLineChar(text[idx]) : false;
    };
    var normalizeWhitespace = function (editor, text) {
      var tabSpace = repeat(' ', getTabSpaces(editor));
      var normalizedText = text.replace(/\t/g, tabSpace);
      var result = foldl(normalizedText, function (acc, c) {
        if (isCollapsibleWhitespace(c) || c === nbsp) {
          if (acc.pcIsSpace || acc.str === '' || acc.str.length === normalizedText.length - 1 || isNewline(normalizedText, acc.str.length + 1)) {
            return {
              pcIsSpace: false,
              str: acc.str + nbsp
            };
          } else {
            return {
              pcIsSpace: true,
              str: acc.str + ' '
            };
          }
        } else {
          return {
            pcIsSpace: isNewLineChar(c),
            str: acc.str + c
          };
        }
      }, {
        pcIsSpace: false,
        str: ''
      });
      return result.str;
    };

    var doPaste = function (editor, content, internal, pasteAsText) {
      var args = process(editor, content, internal);
      if (args.cancelled === false) {
        insertContent(editor, args.content, pasteAsText);
      }
    };
    var pasteHtml = function (editor, html, internalFlag) {
      var internal = internalFlag ? internalFlag : isMarked(html);
      doPaste(editor, unmark(html), internal, false);
    };
    var pasteText = function (editor, text) {
      var encodedText = editor.dom.encode(text).replace(/\r\n/g, '\n');
      var normalizedText = normalizeWhitespace(editor, encodedText);
      var html = convert(normalizedText, getForcedRootBlock(editor), getForcedRootBlockAttrs(editor));
      doPaste(editor, html, false, true);
    };
    var getDataTransferItems = function (dataTransfer) {
      var items = {};
      var mceInternalUrlPrefix = 'data:text/mce-internal,';
      if (dataTransfer) {
        if (dataTransfer.getData) {
          var legacyText = dataTransfer.getData('Text');
          if (legacyText && legacyText.length > 0) {
            if (legacyText.indexOf(mceInternalUrlPrefix) === -1) {
              items['text/plain'] = legacyText;
            }
          }
        }
        if (dataTransfer.types) {
          for (var i = 0; i < dataTransfer.types.length; i++) {
            var contentType = dataTransfer.types[i];
            try {
              items[contentType] = dataTransfer.getData(contentType);
            } catch (ex) {
              items[contentType] = '';
            }
          }
        }
      }
      return items;
    };
    var getClipboardContent = function (editor, clipboardEvent) {
      return getDataTransferItems(clipboardEvent.clipboardData || editor.getDoc().dataTransfer);
    };
    var hasContentType = function (clipboardContent, mimeType) {
      return mimeType in clipboardContent && clipboardContent[mimeType].length > 0;
    };
    var hasHtmlOrText = function (content) {
      return hasContentType(content, 'text/html') || hasContentType(content, 'text/plain');
    };
    var parseDataUri = function (uri) {
      var matches = /data:([^;]+);base64,([a-z0-9\+\/=]+)/i.exec(uri);
      if (matches) {
        return {
          type: matches[1],
          data: decodeURIComponent(matches[2])
        };
      } else {
        return {
          type: null,
          data: null
        };
      }
    };
    var isValidDataUriImage = function (editor, imgElm) {
      var filter = getImagesDataImgFilter(editor);
      return filter ? filter(imgElm) : true;
    };
    var extractFilename = function (editor, str) {
      var m = str.match(/([\s\S]+?)(?:\.[a-z0-9.]+)$/i);
      return isNonNullable(m) ? editor.dom.encode(m[1]) : null;
    };
    var uniqueId = createIdGenerator('mceclip');
    var pasteImage = function (editor, imageItem) {
      var _a = parseDataUri(imageItem.uri), base64 = _a.data, type = _a.type;
      var id = uniqueId();
      var file = imageItem.blob;
      var img = new Image();
      img.src = imageItem.uri;
      if (isValidDataUriImage(editor, img)) {
        var blobCache = editor.editorUpload.blobCache;
        var blobInfo = void 0;
        var existingBlobInfo = blobCache.getByData(base64, type);
        if (!existingBlobInfo) {
          var useFileName = getImagesReuseFilename(editor) && isNonNullable(file.name);
          var name_1 = useFileName ? extractFilename(editor, file.name) : id;
          var filename = useFileName ? file.name : undefined;
          blobInfo = blobCache.create(id, file, base64, name_1, filename);
          blobCache.add(blobInfo);
        } else {
          blobInfo = existingBlobInfo;
        }
        pasteHtml(editor, '<img src="' + blobInfo.blobUri() + '">', false);
      } else {
        pasteHtml(editor, '<img src="' + imageItem.uri + '">', false);
      }
    };
    var isClipboardEvent = function (event) {
      return event.type === 'paste';
    };
    var isDataTransferItem = function (item) {
      return isNonNullable(item.getAsFile);
    };
    var readFilesAsDataUris = function (items) {
      return global$8.all(map(items, function (item) {
        return new global$8(function (resolve) {
          var blob = isDataTransferItem(item) ? item.getAsFile() : item;
          var reader = new window.FileReader();
          reader.onload = function () {
            resolve({
              blob: blob,
              uri: reader.result
            });
          };
          reader.readAsDataURL(blob);
        });
      }));
    };
    var isImage = function (editor) {
      var allowedExtensions = getAllowedImageFileTypes(editor);
      return function (file) {
        return startsWith(file.type, 'image/') && exists(allowedExtensions, function (extension) {
          return getImageMimeType(extension) === file.type;
        });
      };
    };
    var getImagesFromDataTransfer = function (editor, dataTransfer) {
      var items = dataTransfer.items ? bind(from(dataTransfer.items), function (item) {
        return item.kind === 'file' ? [item.getAsFile()] : [];
      }) : [];
      var files = dataTransfer.files ? from(dataTransfer.files) : [];
      return filter$1(items.length > 0 ? items : files, isImage(editor));
    };
    var pasteImageData = function (editor, e, rng) {
      var dataTransfer = isClipboardEvent(e) ? e.clipboardData : e.dataTransfer;
      if (getPasteDataImages(editor) && dataTransfer) {
        var images = getImagesFromDataTransfer(editor, dataTransfer);
        if (images.length > 0) {
          e.preventDefault();
          readFilesAsDataUris(images).then(function (fileResults) {
            if (rng) {
              editor.selection.setRng(rng);
            }
            each(fileResults, function (result) {
              pasteImage(editor, result);
            });
          });
          return true;
        }
      }
      return false;
    };
    var isBrokenAndroidClipboardEvent = function (e) {
      var clipboardData = e.clipboardData;
      return navigator.userAgent.indexOf('Android') !== -1 && clipboardData && clipboardData.items && clipboardData.items.length === 0;
    };
    var isKeyboardPasteEvent = function (e) {
      return global$7.metaKeyPressed(e) && e.keyCode === 86 || e.shiftKey && e.keyCode === 45;
    };
    var registerEventHandlers = function (editor, pasteBin, pasteFormat) {
      var keyboardPasteEvent = value();
      var keyboardPastePressed = value();
      var keyboardPastePlainTextState;
      editor.on('keyup', keyboardPastePressed.clear);
      editor.on('keydown', function (e) {
        var removePasteBinOnKeyUp = function (e) {
          if (isKeyboardPasteEvent(e) && !e.isDefaultPrevented()) {
            pasteBin.remove();
          }
        };
        if (isKeyboardPasteEvent(e) && !e.isDefaultPrevented()) {
          keyboardPastePlainTextState = e.shiftKey && e.keyCode === 86;
          if (keyboardPastePlainTextState && global$a.webkit && navigator.userAgent.indexOf('Version/') !== -1) {
            return;
          }
          e.stopImmediatePropagation();
          keyboardPasteEvent.set(e);
          keyboardPastePressed.set(true);
          if (global$a.ie && keyboardPastePlainTextState) {
            e.preventDefault();
            firePaste(editor, true);
            return;
          }
          pasteBin.remove();
          pasteBin.create();
          editor.once('keyup', removePasteBinOnKeyUp);
          editor.once('paste', function () {
            editor.off('keyup', removePasteBinOnKeyUp);
          });
        }
      });
      var insertClipboardContent = function (editor, clipboardContent, isKeyBoardPaste, plainTextMode, internal) {
        var content;
        if (hasContentType(clipboardContent, 'text/html')) {
          content = clipboardContent['text/html'];
        } else {
          content = pasteBin.getHtml();
          internal = internal ? internal : isMarked(content);
          if (pasteBin.isDefaultContent(content)) {
            plainTextMode = true;
          }
        }
        content = trimHtml(content);
        pasteBin.remove();
        var isPlainTextHtml = internal === false && isPlainText(content);
        var isAbsoluteUrl$1 = isAbsoluteUrl(content);
        if (!content.length || isPlainTextHtml && !isAbsoluteUrl$1) {
          plainTextMode = true;
        }
        if (plainTextMode || isAbsoluteUrl$1) {
          if (hasContentType(clipboardContent, 'text/plain') && isPlainTextHtml) {
            content = clipboardContent['text/plain'];
          } else {
            content = innerText(content);
          }
        }
        if (pasteBin.isDefaultContent(content)) {
          if (!isKeyBoardPaste) {
            editor.windowManager.alert('Please use Ctrl+V/Cmd+V keyboard shortcuts to paste contents.');
          }
          return;
        }
        if (plainTextMode) {
          pasteText(editor, content);
        } else {
          pasteHtml(editor, content, internal);
        }
      };
      var getLastRng = function () {
        return pasteBin.getLastRng() || editor.selection.getRng();
      };
      editor.on('paste', function (e) {
        var isKeyboardPaste = keyboardPasteEvent.isSet() || keyboardPastePressed.isSet();
        if (isKeyboardPaste) {
          keyboardPasteEvent.clear();
        }
        var clipboardContent = getClipboardContent(editor, e);
        var plainTextMode = pasteFormat.get() === 'text' || keyboardPastePlainTextState;
        var internal = hasContentType(clipboardContent, internalHtmlMime());
        keyboardPastePlainTextState = false;
        if (e.isDefaultPrevented() || isBrokenAndroidClipboardEvent(e)) {
          pasteBin.remove();
          return;
        }
        if (!hasHtmlOrText(clipboardContent) && pasteImageData(editor, e, getLastRng())) {
          pasteBin.remove();
          return;
        }
        if (!isKeyboardPaste) {
          e.preventDefault();
        }
        if (global$a.ie && (!isKeyboardPaste || e.ieFake) && !hasContentType(clipboardContent, 'text/html')) {
          pasteBin.create();
          editor.dom.bind(pasteBin.getEl(), 'paste', function (e) {
            e.stopPropagation();
          });
          editor.getDoc().execCommand('Paste', false, null);
          clipboardContent['text/html'] = pasteBin.getHtml();
        }
        if (hasContentType(clipboardContent, 'text/html')) {
          e.preventDefault();
          if (!internal) {
            internal = isMarked(clipboardContent['text/html']);
          }
          insertClipboardContent(editor, clipboardContent, isKeyboardPaste, plainTextMode, internal);
        } else {
          global$9.setEditorTimeout(editor, function () {
            insertClipboardContent(editor, clipboardContent, isKeyboardPaste, plainTextMode, internal);
          }, 0);
        }
      });
    };
    var registerEventsAndFilters = function (editor, pasteBin, pasteFormat) {
      registerEventHandlers(editor, pasteBin, pasteFormat);
      var src;
      editor.parser.addNodeFilter('img', function (nodes, name, args) {
        var isPasteInsert = function (args) {
          return args.data && args.data.paste === true;
        };
        var remove = function (node) {
          if (!node.attr('data-mce-object') && src !== global$a.transparentSrc) {
            node.remove();
          }
        };
        var isWebKitFakeUrl = function (src) {
          return src.indexOf('webkit-fake-url') === 0;
        };
        var isDataUri = function (src) {
          return src.indexOf('data:') === 0;
        };
        if (!getPasteDataImages(editor) && isPasteInsert(args)) {
          var i = nodes.length;
          while (i--) {
            src = nodes[i].attr('src');
            if (!src) {
              continue;
            }
            if (isWebKitFakeUrl(src)) {
              remove(nodes[i]);
            } else if (!getAllowHtmlDataUrls(editor) && isDataUri(src)) {
              remove(nodes[i]);
            }
          }
        }
      });
    };

    var getPasteBinParent = function (editor) {
      return global$a.ie && editor.inline ? document.body : editor.getBody();
    };
    var isExternalPasteBin = function (editor) {
      return getPasteBinParent(editor) !== editor.getBody();
    };
    var delegatePasteEvents = function (editor, pasteBinElm, pasteBinDefaultContent) {
      if (isExternalPasteBin(editor)) {
        editor.dom.bind(pasteBinElm, 'paste keyup', function (_e) {
          if (!isDefault(editor, pasteBinDefaultContent)) {
            editor.fire('paste');
          }
        });
      }
    };
    var create = function (editor, lastRngCell, pasteBinDefaultContent) {
      var dom = editor.dom, body = editor.getBody();
      lastRngCell.set(editor.selection.getRng());
      var pasteBinElm = editor.dom.add(getPasteBinParent(editor), 'div', {
        'id': 'mcepastebin',
        'class': 'mce-pastebin',
        'contentEditable': true,
        'data-mce-bogus': 'all',
        'style': 'position: fixed; top: 50%; width: 10px; height: 10px; overflow: hidden; opacity: 0'
      }, pasteBinDefaultContent);
      if (global$a.ie || global$a.gecko) {
        dom.setStyle(pasteBinElm, 'left', dom.getStyle(body, 'direction', true) === 'rtl' ? 65535 : -65535);
      }
      dom.bind(pasteBinElm, 'beforedeactivate focusin focusout', function (e) {
        e.stopPropagation();
      });
      delegatePasteEvents(editor, pasteBinElm, pasteBinDefaultContent);
      pasteBinElm.focus();
      editor.selection.select(pasteBinElm, true);
    };
    var remove = function (editor, lastRngCell) {
      if (getEl(editor)) {
        var pasteBinClone = void 0;
        var lastRng = lastRngCell.get();
        while (pasteBinClone = editor.dom.get('mcepastebin')) {
          editor.dom.remove(pasteBinClone);
          editor.dom.unbind(pasteBinClone);
        }
        if (lastRng) {
          editor.selection.setRng(lastRng);
        }
      }
      lastRngCell.set(null);
    };
    var getEl = function (editor) {
      return editor.dom.get('mcepastebin');
    };
    var getHtml = function (editor) {
      var copyAndRemove = function (toElm, fromElm) {
        toElm.appendChild(fromElm);
        editor.dom.remove(fromElm, true);
      };
      var pasteBinClones = global$6.grep(getPasteBinParent(editor).childNodes, function (elm) {
        return elm.id === 'mcepastebin';
      });
      var pasteBinElm = pasteBinClones.shift();
      global$6.each(pasteBinClones, function (pasteBinClone) {
        copyAndRemove(pasteBinElm, pasteBinClone);
      });
      var dirtyWrappers = editor.dom.select('div[id=mcepastebin]', pasteBinElm);
      for (var i = dirtyWrappers.length - 1; i >= 0; i--) {
        var cleanWrapper = editor.dom.create('div');
        pasteBinElm.insertBefore(cleanWrapper, dirtyWrappers[i]);
        copyAndRemove(cleanWrapper, dirtyWrappers[i]);
      }
      return pasteBinElm ? pasteBinElm.innerHTML : '';
    };
    var isDefaultContent = function (pasteBinDefaultContent, content) {
      return content === pasteBinDefaultContent;
    };
    var isPasteBin = function (elm) {
      return elm && elm.id === 'mcepastebin';
    };
    var isDefault = function (editor, pasteBinDefaultContent) {
      var pasteBinElm = getEl(editor);
      return isPasteBin(pasteBinElm) && isDefaultContent(pasteBinDefaultContent, pasteBinElm.innerHTML);
    };
    var PasteBin = function (editor) {
      var lastRng = Cell(null);
      var pasteBinDefaultContent = '%MCEPASTEBIN%';
      return {
        create: function () {
          return create(editor, lastRng, pasteBinDefaultContent);
        },
        remove: function () {
          return remove(editor, lastRng);
        },
        getEl: function () {
          return getEl(editor);
        },
        getHtml: function () {
          return getHtml(editor);
        },
        getLastRng: lastRng.get,
        isDefault: function () {
          return isDefault(editor, pasteBinDefaultContent);
        },
        isDefaultContent: function (content) {
          return isDefaultContent(pasteBinDefaultContent, content);
        }
      };
    };

    var Clipboard = function (editor, pasteFormat) {
      var pasteBin = PasteBin(editor);
      editor.on('PreInit', function () {
        return registerEventsAndFilters(editor, pasteBin, pasteFormat);
      });
      return {
        pasteFormat: pasteFormat,
        pasteHtml: function (html, internalFlag) {
          return pasteHtml(editor, html, internalFlag);
        },
        pasteText: function (text) {
          return pasteText(editor, text);
        },
        pasteImageData: function (e, rng) {
          return pasteImageData(editor, e, rng);
        },
        getDataTransferItems: getDataTransferItems,
        hasHtmlOrText: hasHtmlOrText,
        hasContentType: hasContentType
      };
    };

    var togglePlainTextPaste = function (editor, clipboard) {
      if (clipboard.pasteFormat.get() === 'text') {
        clipboard.pasteFormat.set('html');
        firePastePlainTextToggle(editor, false);
      } else {
        clipboard.pasteFormat.set('text');
        firePastePlainTextToggle(editor, true);
      }
      editor.focus();
    };

    var register$2 = function (editor, clipboard) {
      editor.addCommand('mceTogglePlainTextPaste', function () {
        togglePlainTextPaste(editor, clipboard);
      });
      editor.addCommand('mceInsertClipboardContent', function (ui, value) {
        if (value.content) {
          clipboard.pasteHtml(value.content, value.internal);
        }
        if (value.text) {
          clipboard.pasteText(value.text);
        }
      });
    };

    var hasWorkingClipboardApi = function (clipboardData) {
      return global$a.iOS === false && typeof (clipboardData === null || clipboardData === void 0 ? void 0 : clipboardData.setData) === 'function';
    };
    var setHtml5Clipboard = function (clipboardData, html, text) {
      if (hasWorkingClipboardApi(clipboardData)) {
        try {
          clipboardData.clearData();
          clipboardData.setData('text/html', html);
          clipboardData.setData('text/plain', text);
          clipboardData.setData(internalHtmlMime(), html);
          return true;
        } catch (e) {
          return false;
        }
      } else {
        return false;
      }
    };
    var setClipboardData = function (evt, data, fallback, done) {
      if (setHtml5Clipboard(evt.clipboardData, data.html, data.text)) {
        evt.preventDefault();
        done();
      } else {
        fallback(data.html, done);
      }
    };
    var fallback = function (editor) {
      return function (html, done) {
        var markedHtml = mark(html);
        var outer = editor.dom.create('div', {
          'contenteditable': 'false',
          'data-mce-bogus': 'all'
        });
        var inner = editor.dom.create('div', { contenteditable: 'true' }, markedHtml);
        editor.dom.setStyles(outer, {
          position: 'fixed',
          top: '0',
          left: '-3000px',
          width: '1000px',
          overflow: 'hidden'
        });
        outer.appendChild(inner);
        editor.dom.add(editor.getBody(), outer);
        var range = editor.selection.getRng();
        inner.focus();
        var offscreenRange = editor.dom.createRng();
        offscreenRange.selectNodeContents(inner);
        editor.selection.setRng(offscreenRange);
        global$9.setTimeout(function () {
          editor.selection.setRng(range);
          outer.parentNode.removeChild(outer);
          done();
        }, 0);
      };
    };
    var getData = function (editor) {
      return {
        html: editor.selection.getContent({ contextual: true }),
        text: editor.selection.getContent({ format: 'text' })
      };
    };
    var isTableSelection = function (editor) {
      return !!editor.dom.getParent(editor.selection.getStart(), 'td[data-mce-selected],th[data-mce-selected]', editor.getBody());
    };
    var hasSelectedContent = function (editor) {
      return !editor.selection.isCollapsed() || isTableSelection(editor);
    };
    var cut = function (editor) {
      return function (evt) {
        if (hasSelectedContent(editor)) {
          setClipboardData(evt, getData(editor), fallback(editor), function () {
            if (global$a.browser.isChrome() || global$a.browser.isFirefox()) {
              var rng_1 = editor.selection.getRng();
              global$9.setEditorTimeout(editor, function () {
                editor.selection.setRng(rng_1);
                editor.execCommand('Delete');
              }, 0);
            } else {
              editor.execCommand('Delete');
            }
          });
        }
      };
    };
    var copy = function (editor) {
      return function (evt) {
        if (hasSelectedContent(editor)) {
          setClipboardData(evt, getData(editor), fallback(editor), noop);
        }
      };
    };
    var register$1 = function (editor) {
      editor.on('cut', cut(editor));
      editor.on('copy', copy(editor));
    };

    var global = tinymce.util.Tools.resolve('tinymce.dom.RangeUtils');

    var getCaretRangeFromEvent = function (editor, e) {
      return global.getCaretRangeFromPoint(e.clientX, e.clientY, editor.getDoc());
    };
    var isPlainTextFileUrl = function (content) {
      var plainTextContent = content['text/plain'];
      return plainTextContent ? plainTextContent.indexOf('file://') === 0 : false;
    };
    var setFocusedRange = function (editor, rng) {
      editor.focus();
      editor.selection.setRng(rng);
    };
    var setup$2 = function (editor, clipboard, draggingInternallyState) {
      if (shouldBlockDrop(editor)) {
        editor.on('dragend dragover draggesture dragdrop drop drag', function (e) {
          e.preventDefault();
          e.stopPropagation();
        });
      }
      if (!shouldPasteDataImages(editor)) {
        editor.on('drop', function (e) {
          var dataTransfer = e.dataTransfer;
          if (dataTransfer && dataTransfer.files && dataTransfer.files.length > 0) {
            e.preventDefault();
          }
        });
      }
      editor.on('drop', function (e) {
        var rng = getCaretRangeFromEvent(editor, e);
        if (e.isDefaultPrevented() || draggingInternallyState.get()) {
          return;
        }
        var dropContent = clipboard.getDataTransferItems(e.dataTransfer);
        var internal = clipboard.hasContentType(dropContent, internalHtmlMime());
        if ((!clipboard.hasHtmlOrText(dropContent) || isPlainTextFileUrl(dropContent)) && clipboard.pasteImageData(e, rng)) {
          return;
        }
        if (rng && shouldFilterDrop(editor)) {
          var content_1 = dropContent['mce-internal'] || dropContent['text/html'] || dropContent['text/plain'];
          if (content_1) {
            e.preventDefault();
            global$9.setEditorTimeout(editor, function () {
              editor.undoManager.transact(function () {
                if (dropContent['mce-internal']) {
                  editor.execCommand('Delete');
                }
                setFocusedRange(editor, rng);
                content_1 = trimHtml(content_1);
                if (!dropContent['text/html']) {
                  clipboard.pasteText(content_1);
                } else {
                  clipboard.pasteHtml(content_1, internal);
                }
              });
            });
          }
        }
      });
      editor.on('dragstart', function (_e) {
        draggingInternallyState.set(true);
      });
      editor.on('dragover dragend', function (e) {
        if (shouldPasteDataImages(editor) && draggingInternallyState.get() === false) {
          e.preventDefault();
          setFocusedRange(editor, getCaretRangeFromEvent(editor, e));
        }
        if (e.type === 'dragend') {
          draggingInternallyState.set(false);
        }
      });
    };

    var setup$1 = function (editor) {
      var plugin = editor.plugins.paste;
      var preProcess = getPreProcess(editor);
      if (preProcess) {
        editor.on('PastePreProcess', function (e) {
          preProcess.call(plugin, plugin, e);
        });
      }
      var postProcess = getPostProcess(editor);
      if (postProcess) {
        editor.on('PastePostProcess', function (e) {
          postProcess.call(plugin, plugin, e);
        });
      }
    };

    var addPreProcessFilter = function (editor, filterFunc) {
      editor.on('PastePreProcess', function (e) {
        e.content = filterFunc(editor, e.content, e.internal, e.wordContent);
      });
    };
    var addPostProcessFilter = function (editor, filterFunc) {
      editor.on('PastePostProcess', function (e) {
        filterFunc(editor, e.node);
      });
    };
    var removeExplorerBrElementsAfterBlocks = function (editor, html) {
      if (!isWordContent(html)) {
        return html;
      }
      var blockElements = [];
      global$6.each(editor.schema.getBlockElements(), function (block, blockName) {
        blockElements.push(blockName);
      });
      var explorerBlocksRegExp = new RegExp('(?:<br>&nbsp;[\\s\\r\\n]+|<br>)*(<\\/?(' + blockElements.join('|') + ')[^>]*>)(?:<br>&nbsp;[\\s\\r\\n]+|<br>)*', 'g');
      html = filter(html, [[
          explorerBlocksRegExp,
          '$1'
        ]]);
      html = filter(html, [
        [
          /<br><br>/g,
          '<BR><BR>'
        ],
        [
          /<br>/g,
          ' '
        ],
        [
          /<BR><BR>/g,
          '<br>'
        ]
      ]);
      return html;
    };
    var removeWebKitStyles = function (editor, content, internal, isWordHtml) {
      if (isWordHtml || internal) {
        return content;
      }
      var webKitStylesSetting = getWebkitStyles(editor);
      var webKitStyles;
      if (shouldRemoveWebKitStyles(editor) === false || webKitStylesSetting === 'all') {
        return content;
      }
      if (webKitStylesSetting) {
        webKitStyles = webKitStylesSetting.split(/[, ]/);
      }
      if (webKitStyles) {
        var dom_1 = editor.dom, node_1 = editor.selection.getNode();
        content = content.replace(/(<[^>]+) style="([^"]*)"([^>]*>)/gi, function (all, before, value, after) {
          var inputStyles = dom_1.parseStyle(dom_1.decode(value));
          var outputStyles = {};
          if (webKitStyles === 'none') {
            return before + after;
          }
          for (var i = 0; i < webKitStyles.length; i++) {
            var inputValue = inputStyles[webKitStyles[i]], currentValue = dom_1.getStyle(node_1, webKitStyles[i], true);
            if (/color/.test(webKitStyles[i])) {
              inputValue = dom_1.toHex(inputValue);
              currentValue = dom_1.toHex(currentValue);
            }
            if (currentValue !== inputValue) {
              outputStyles[webKitStyles[i]] = inputValue;
            }
          }
          var outputStyle = dom_1.serializeStyle(outputStyles, 'span');
          if (outputStyle) {
            return before + ' style="' + outputStyle + '"' + after;
          }
          return before + after;
        });
      } else {
        content = content.replace(/(<[^>]+) style="([^"]*)"([^>]*>)/gi, '$1$3');
      }
      content = content.replace(/(<[^>]+) data-mce-style="([^"]+)"([^>]*>)/gi, function (all, before, value, after) {
        return before + ' style="' + value + '"' + after;
      });
      return content;
    };
    var removeUnderlineAndFontInAnchor = function (editor, root) {
      editor.$('a', root).find('font,u').each(function (i, node) {
        editor.dom.remove(node, true);
      });
    };
    var setup = function (editor) {
      if (global$a.webkit) {
        addPreProcessFilter(editor, removeWebKitStyles);
      }
      if (global$a.ie) {
        addPreProcessFilter(editor, removeExplorerBrElementsAfterBlocks);
        addPostProcessFilter(editor, removeUnderlineAndFontInAnchor);
      }
    };

    var makeSetupHandler = function (editor, clipboard) {
      return function (api) {
        api.setActive(clipboard.pasteFormat.get() === 'text');
        var pastePlainTextToggleHandler = function (e) {
          return api.setActive(e.state);
        };
        editor.on('PastePlainTextToggle', pastePlainTextToggleHandler);
        return function () {
          return editor.off('PastePlainTextToggle', pastePlainTextToggleHandler);
        };
      };
    };
    var register = function (editor, clipboard) {
      var onAction = function () {
        return editor.execCommand('mceTogglePlainTextPaste');
      };
      editor.ui.registry.addToggleButton('pastetext', {
        active: false,
        icon: 'paste-text',
        tooltip: 'Paste as text',
        onAction: onAction,
        onSetup: makeSetupHandler(editor, clipboard)
      });
      editor.ui.registry.addToggleMenuItem('pastetext', {
        text: 'Paste as text',
        icon: 'paste-text',
        onAction: onAction,
        onSetup: makeSetupHandler(editor, clipboard)
      });
    };

    function Plugin () {
      global$b.add('paste', function (editor) {
        if (hasProPlugin(editor) === false) {
          var draggingInternallyState = Cell(false);
          var pasteFormat = Cell(isPasteAsTextEnabled(editor) ? 'text' : 'html');
          var clipboard = Clipboard(editor, pasteFormat);
          setup(editor);
          register(editor, clipboard);
          register$2(editor, clipboard);
          setup$1(editor);
          register$1(editor);
          setup$2(editor, clipboard, draggingInternallyState);
          return get(clipboard);
        }
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/save/index.js":
/*!****************************************************!*\
  !*** ./node_modules/tinymce/plugins/save/index.js ***!
  \****************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "save" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/save')
//   ES2015:
//     import 'tinymce/plugins/save'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/save/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/save/plugin.js":
/*!*****************************************************!*\
  !*** ./node_modules/tinymce/plugins/save/plugin.js ***!
  \*****************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global$2 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var global$1 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

    var global = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var enableWhenDirty = function (editor) {
      return editor.getParam('save_enablewhendirty', true);
    };
    var hasOnSaveCallback = function (editor) {
      return !!editor.getParam('save_onsavecallback');
    };
    var hasOnCancelCallback = function (editor) {
      return !!editor.getParam('save_oncancelcallback');
    };

    var displayErrorMessage = function (editor, message) {
      editor.notificationManager.open({
        text: message,
        type: 'error'
      });
    };
    var save = function (editor) {
      var formObj = global$1.DOM.getParent(editor.id, 'form');
      if (enableWhenDirty(editor) && !editor.isDirty()) {
        return;
      }
      editor.save();
      if (hasOnSaveCallback(editor)) {
        editor.execCallback('save_onsavecallback', editor);
        editor.nodeChanged();
        return;
      }
      if (formObj) {
        editor.setDirty(false);
        if (!formObj.onsubmit || formObj.onsubmit()) {
          if (typeof formObj.submit === 'function') {
            formObj.submit();
          } else {
            displayErrorMessage(editor, 'Error: Form submit field collision.');
          }
        }
        editor.nodeChanged();
      } else {
        displayErrorMessage(editor, 'Error: No form element found.');
      }
    };
    var cancel = function (editor) {
      var h = global.trim(editor.startContent);
      if (hasOnCancelCallback(editor)) {
        editor.execCallback('save_oncancelcallback', editor);
        return;
      }
      editor.resetContent(h);
    };

    var register$1 = function (editor) {
      editor.addCommand('mceSave', function () {
        save(editor);
      });
      editor.addCommand('mceCancel', function () {
        cancel(editor);
      });
    };

    var stateToggle = function (editor) {
      return function (api) {
        var handler = function () {
          api.setDisabled(enableWhenDirty(editor) && !editor.isDirty());
        };
        handler();
        editor.on('NodeChange dirty', handler);
        return function () {
          return editor.off('NodeChange dirty', handler);
        };
      };
    };
    var register = function (editor) {
      editor.ui.registry.addButton('save', {
        icon: 'save',
        tooltip: 'Save',
        disabled: true,
        onAction: function () {
          return editor.execCommand('mceSave');
        },
        onSetup: stateToggle(editor)
      });
      editor.ui.registry.addButton('cancel', {
        icon: 'cancel',
        tooltip: 'Cancel',
        disabled: true,
        onAction: function () {
          return editor.execCommand('mceCancel');
        },
        onSetup: stateToggle(editor)
      });
      editor.addShortcut('Meta+S', '', 'mceSave');
    };

    function Plugin () {
      global$2.add('save', function (editor) {
        register(editor);
        register$1(editor);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/searchreplace/index.js":
/*!*************************************************************!*\
  !*** ./node_modules/tinymce/plugins/searchreplace/index.js ***!
  \*************************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "searchreplace" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/searchreplace')
//   ES2015:
//     import 'tinymce/plugins/searchreplace'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/searchreplace/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/searchreplace/plugin.js":
/*!**************************************************************!*\
  !*** ./node_modules/tinymce/plugins/searchreplace/plugin.js ***!
  \**************************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var global$3 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType$1 = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var isString = isType$1('string');
    var isArray = isType$1('array');
    var isBoolean = isSimpleType('boolean');
    var isNumber = isSimpleType('number');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var punctuationStr = '[!-#%-*,-\\/:;?@\\[-\\]_{}\xA1\xAB\xB7\xBB\xBF;\xB7\u055A-\u055F\u0589\u058A\u05BE\u05C0\u05C3\u05C6\u05F3\u05F4\u0609\u060A\u060C\u060D\u061B\u061E\u061F\u066A-\u066D\u06D4\u0700-\u070D\u07F7-\u07F9\u0830-\u083E\u085E\u0964\u0965\u0970\u0DF4\u0E4F\u0E5A\u0E5B\u0F04-\u0F12\u0F3A-\u0F3D\u0F85\u0FD0-\u0FD4\u0FD9\u0FDA\u104A-\u104F\u10FB\u1361-\u1368\u1400\u166D\u166E\u169B\u169C\u16EB-\u16ED\u1735\u1736\u17D4-\u17D6\u17D8-\u17DA\u1800-\u180A\u1944\u1945\u1A1E\u1A1F\u1AA0-\u1AA6\u1AA8-\u1AAD\u1B5A-\u1B60\u1BFC-\u1BFF\u1C3B-\u1C3F\u1C7E\u1C7F\u1CD3\u2010-\u2027\u2030-\u2043\u2045-\u2051\u2053-\u205E\u207D\u207E\u208D\u208E\u3008\u3009\u2768-\u2775\u27C5\u27C6\u27E6-\u27EF\u2983-\u2998\u29D8-\u29DB\u29FC\u29FD\u2CF9-\u2CFC\u2CFE\u2CFF\u2D70\u2E00-\u2E2E\u2E30\u2E31\u3001-\u3003\u3008-\u3011\u3014-\u301F\u3030\u303D\u30A0\u30FB\uA4FE\uA4FF\uA60D-\uA60F\uA673\uA67E\uA6F2-\uA6F7\uA874-\uA877\uA8CE\uA8CF\uA8F8-\uA8FA\uA92E\uA92F\uA95F\uA9C1-\uA9CD\uA9DE\uA9DF\uAA5C-\uAA5F\uAADE\uAADF\uABEB\uFD3E\uFD3F\uFE10-\uFE19\uFE30-\uFE52\uFE54-\uFE61\uFE63\uFE68\uFE6A\uFE6B\uFF01-\uFF03\uFF05-\uFF0A\uFF0C-\uFF0F\uFF1A\uFF1B\uFF1F\uFF20\uFF3B-\uFF3D\uff3f\uFF5B\uFF5D\uFF5F-\uFF65]';

    var punctuation$1 = constant(punctuationStr);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var punctuation = punctuation$1;

    var global$2 = tinymce.util.Tools.resolve('tinymce.Env');

    var global$1 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var nativeSlice = Array.prototype.slice;
    var nativePush = Array.prototype.push;
    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var eachr = function (xs, f) {
      for (var i = xs.length - 1; i >= 0; i--) {
        var x = xs[i];
        f(x, i);
      }
    };
    var groupBy = function (xs, f) {
      if (xs.length === 0) {
        return [];
      } else {
        var wasType = f(xs[0]);
        var r = [];
        var group = [];
        for (var i = 0, len = xs.length; i < len; i++) {
          var x = xs[i];
          var type = f(x);
          if (type !== wasType) {
            r.push(group);
            group = [];
          }
          wasType = type;
          group.push(x);
        }
        if (group.length !== 0) {
          r.push(group);
        }
        return r;
      }
    };
    var foldl = function (xs, f, acc) {
      each(xs, function (x, i) {
        acc = f(acc, x, i);
      });
      return acc;
    };
    var flatten = function (xs) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; ++i) {
        if (!isArray(xs[i])) {
          throw new Error('Arr.flatten item ' + i + ' was not an array, input: ' + xs);
        }
        nativePush.apply(r, xs[i]);
      }
      return r;
    };
    var bind = function (xs, f) {
      return flatten(map(xs, f));
    };
    var sort = function (xs, comparator) {
      var copy = nativeSlice.call(xs, 0);
      copy.sort(comparator);
      return copy;
    };

    var hasOwnProperty = Object.hasOwnProperty;
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };

    typeof window !== 'undefined' ? window : Function('return this;')();

    var DOCUMENT = 9;
    var DOCUMENT_FRAGMENT = 11;
    var ELEMENT = 1;
    var TEXT = 3;

    var type = function (element) {
      return element.dom.nodeType;
    };
    var isType = function (t) {
      return function (element) {
        return type(element) === t;
      };
    };
    var isText$1 = isType(TEXT);

    var rawSet = function (dom, key, value) {
      if (isString(value) || isBoolean(value) || isNumber(value)) {
        dom.setAttribute(key, value + '');
      } else {
        console.error('Invalid call to Attribute.set. Key ', key, ':: Value ', value, ':: Element ', dom);
        throw new Error('Attribute value was not simple');
      }
    };
    var set = function (element, key, value) {
      rawSet(element.dom, key, value);
    };

    var compareDocumentPosition = function (a, b, match) {
      return (a.compareDocumentPosition(b) & match) !== 0;
    };
    var documentPositionPreceding = function (a, b) {
      return compareDocumentPosition(a, b, Node.DOCUMENT_POSITION_PRECEDING);
    };

    var fromHtml = function (html, scope) {
      var doc = scope || document;
      var div = doc.createElement('div');
      div.innerHTML = html;
      if (!div.hasChildNodes() || div.childNodes.length > 1) {
        console.error('HTML does not have a single root node', html);
        throw new Error('HTML must have a single root node');
      }
      return fromDom(div.childNodes[0]);
    };
    var fromTag = function (tag, scope) {
      var doc = scope || document;
      var node = doc.createElement(tag);
      return fromDom(node);
    };
    var fromText = function (text, scope) {
      var doc = scope || document;
      var node = doc.createTextNode(text);
      return fromDom(node);
    };
    var fromDom = function (node) {
      if (node === null || node === undefined) {
        throw new Error('Node cannot be null or undefined');
      }
      return { dom: node };
    };
    var fromPoint = function (docElm, x, y) {
      return Optional.from(docElm.dom.elementFromPoint(x, y)).map(fromDom);
    };
    var SugarElement = {
      fromHtml: fromHtml,
      fromTag: fromTag,
      fromText: fromText,
      fromDom: fromDom,
      fromPoint: fromPoint
    };

    var bypassSelector = function (dom) {
      return dom.nodeType !== ELEMENT && dom.nodeType !== DOCUMENT && dom.nodeType !== DOCUMENT_FRAGMENT || dom.childElementCount === 0;
    };
    var all = function (selector, scope) {
      var base = scope === undefined ? document : scope.dom;
      return bypassSelector(base) ? [] : map(base.querySelectorAll(selector), SugarElement.fromDom);
    };

    var parent = function (element) {
      return Optional.from(element.dom.parentNode).map(SugarElement.fromDom);
    };
    var children = function (element) {
      return map(element.dom.childNodes, SugarElement.fromDom);
    };
    var spot = function (element, offset) {
      return {
        element: element,
        offset: offset
      };
    };
    var leaf = function (element, offset) {
      var cs = children(element);
      return cs.length > 0 && offset < cs.length ? spot(cs[offset], 0) : spot(element, offset);
    };

    var before = function (marker, element) {
      var parent$1 = parent(marker);
      parent$1.each(function (v) {
        v.dom.insertBefore(element.dom, marker.dom);
      });
    };
    var append = function (parent, element) {
      parent.dom.appendChild(element.dom);
    };
    var wrap = function (element, wrapper) {
      before(element, wrapper);
      append(wrapper, element);
    };

    var NodeValue = function (is, name) {
      var get = function (element) {
        if (!is(element)) {
          throw new Error('Can only get ' + name + ' value of a ' + name + ' node');
        }
        return getOption(element).getOr('');
      };
      var getOption = function (element) {
        return is(element) ? Optional.from(element.dom.nodeValue) : Optional.none();
      };
      var set = function (element, value) {
        if (!is(element)) {
          throw new Error('Can only set raw ' + name + ' value of a ' + name + ' node');
        }
        element.dom.nodeValue = value;
      };
      return {
        get: get,
        getOption: getOption,
        set: set
      };
    };

    var api = NodeValue(isText$1, 'text');
    var get$1 = function (element) {
      return api.get(element);
    };

    var descendants = function (scope, selector) {
      return all(selector, scope);
    };

    var global = tinymce.util.Tools.resolve('tinymce.dom.TreeWalker');

    var isSimpleBoundary = function (dom, node) {
      return dom.isBlock(node) || has(dom.schema.getShortEndedElements(), node.nodeName);
    };
    var isContentEditableFalse = function (dom, node) {
      return dom.getContentEditable(node) === 'false';
    };
    var isContentEditableTrueInCef = function (dom, node) {
      return dom.getContentEditable(node) === 'true' && dom.getContentEditableParent(node.parentNode) === 'false';
    };
    var isHidden = function (dom, node) {
      return !dom.isBlock(node) && has(dom.schema.getWhiteSpaceElements(), node.nodeName);
    };
    var isBoundary = function (dom, node) {
      return isSimpleBoundary(dom, node) || isContentEditableFalse(dom, node) || isHidden(dom, node) || isContentEditableTrueInCef(dom, node);
    };
    var isText = function (node) {
      return node.nodeType === 3;
    };
    var nuSection = function () {
      return {
        sOffset: 0,
        fOffset: 0,
        elements: []
      };
    };
    var toLeaf = function (node, offset) {
      return leaf(SugarElement.fromDom(node), offset);
    };
    var walk = function (dom, walkerFn, startNode, callbacks, endNode, skipStart) {
      if (skipStart === void 0) {
        skipStart = true;
      }
      var next = skipStart ? walkerFn(false) : startNode;
      while (next) {
        var isCefNode = isContentEditableFalse(dom, next);
        if (isCefNode || isHidden(dom, next)) {
          var stopWalking = isCefNode ? callbacks.cef(next) : callbacks.boundary(next);
          if (stopWalking) {
            break;
          } else {
            next = walkerFn(true);
            continue;
          }
        } else if (isSimpleBoundary(dom, next)) {
          if (callbacks.boundary(next)) {
            break;
          }
        } else if (isText(next)) {
          callbacks.text(next);
        }
        if (next === endNode) {
          break;
        } else {
          next = walkerFn(false);
        }
      }
    };
    var collectTextToBoundary = function (dom, section, node, rootNode, forwards) {
      if (isBoundary(dom, node)) {
        return;
      }
      var rootBlock = dom.getParent(rootNode, dom.isBlock);
      var walker = new global(node, rootBlock);
      var walkerFn = forwards ? walker.next.bind(walker) : walker.prev.bind(walker);
      walk(dom, walkerFn, node, {
        boundary: always,
        cef: always,
        text: function (next) {
          if (forwards) {
            section.fOffset += next.length;
          } else {
            section.sOffset += next.length;
          }
          section.elements.push(SugarElement.fromDom(next));
        }
      });
    };
    var collect = function (dom, rootNode, startNode, endNode, callbacks, skipStart) {
      if (skipStart === void 0) {
        skipStart = true;
      }
      var walker = new global(startNode, rootNode);
      var sections = [];
      var current = nuSection();
      collectTextToBoundary(dom, current, startNode, rootNode, false);
      var finishSection = function () {
        if (current.elements.length > 0) {
          sections.push(current);
          current = nuSection();
        }
        return false;
      };
      walk(dom, walker.next.bind(walker), startNode, {
        boundary: finishSection,
        cef: function (node) {
          finishSection();
          if (callbacks) {
            sections.push.apply(sections, callbacks.cef(node));
          }
          return false;
        },
        text: function (next) {
          current.elements.push(SugarElement.fromDom(next));
          if (callbacks) {
            callbacks.text(next, current);
          }
        }
      }, endNode, skipStart);
      if (endNode) {
        collectTextToBoundary(dom, current, endNode, rootNode, true);
      }
      finishSection();
      return sections;
    };
    var collectRangeSections = function (dom, rng) {
      var start = toLeaf(rng.startContainer, rng.startOffset);
      var startNode = start.element.dom;
      var end = toLeaf(rng.endContainer, rng.endOffset);
      var endNode = end.element.dom;
      return collect(dom, rng.commonAncestorContainer, startNode, endNode, {
        text: function (node, section) {
          if (node === endNode) {
            section.fOffset += node.length - end.offset;
          } else if (node === startNode) {
            section.sOffset += start.offset;
          }
        },
        cef: function (node) {
          var sections = bind(descendants(SugarElement.fromDom(node), '*[contenteditable=true]'), function (e) {
            var ceTrueNode = e.dom;
            return collect(dom, ceTrueNode, ceTrueNode);
          });
          return sort(sections, function (a, b) {
            return documentPositionPreceding(a.elements[0].dom, b.elements[0].dom) ? 1 : -1;
          });
        }
      }, false);
    };
    var fromRng = function (dom, rng) {
      return rng.collapsed ? [] : collectRangeSections(dom, rng);
    };
    var fromNode = function (dom, node) {
      var rng = dom.createRng();
      rng.selectNode(node);
      return fromRng(dom, rng);
    };
    var fromNodes = function (dom, nodes) {
      return bind(nodes, function (node) {
        return fromNode(dom, node);
      });
    };

    var find$2 = function (text, pattern, start, finish) {
      if (start === void 0) {
        start = 0;
      }
      if (finish === void 0) {
        finish = text.length;
      }
      var regex = pattern.regex;
      regex.lastIndex = start;
      var results = [];
      var match;
      while (match = regex.exec(text)) {
        var matchedText = match[pattern.matchIndex];
        var matchStart = match.index + match[0].indexOf(matchedText);
        var matchFinish = matchStart + matchedText.length;
        if (matchFinish > finish) {
          break;
        }
        results.push({
          start: matchStart,
          finish: matchFinish
        });
        regex.lastIndex = matchFinish;
      }
      return results;
    };
    var extract = function (elements, matches) {
      var nodePositions = foldl(elements, function (acc, element) {
        var content = get$1(element);
        var start = acc.last;
        var finish = start + content.length;
        var positions = bind(matches, function (match, matchIdx) {
          if (match.start < finish && match.finish > start) {
            return [{
                element: element,
                start: Math.max(start, match.start) - start,
                finish: Math.min(finish, match.finish) - start,
                matchId: matchIdx
              }];
          } else {
            return [];
          }
        });
        return {
          results: acc.results.concat(positions),
          last: finish
        };
      }, {
        results: [],
        last: 0
      }).results;
      return groupBy(nodePositions, function (position) {
        return position.matchId;
      });
    };

    var find$1 = function (pattern, sections) {
      return bind(sections, function (section) {
        var elements = section.elements;
        var content = map(elements, get$1).join('');
        var positions = find$2(content, pattern, section.sOffset, content.length - section.fOffset);
        return extract(elements, positions);
      });
    };
    var mark = function (matches, replacementNode) {
      eachr(matches, function (match, idx) {
        eachr(match, function (pos) {
          var wrapper = SugarElement.fromDom(replacementNode.cloneNode(false));
          set(wrapper, 'data-mce-index', idx);
          var textNode = pos.element.dom;
          if (textNode.length === pos.finish && pos.start === 0) {
            wrap(pos.element, wrapper);
          } else {
            if (textNode.length !== pos.finish) {
              textNode.splitText(pos.finish);
            }
            var matchNode = textNode.splitText(pos.start);
            wrap(SugarElement.fromDom(matchNode), wrapper);
          }
        });
      });
    };
    var findAndMark = function (dom, pattern, node, replacementNode) {
      var textSections = fromNode(dom, node);
      var matches = find$1(pattern, textSections);
      mark(matches, replacementNode);
      return matches.length;
    };
    var findAndMarkInSelection = function (dom, pattern, selection, replacementNode) {
      var bookmark = selection.getBookmark();
      var nodes = dom.select('td[data-mce-selected],th[data-mce-selected]');
      var textSections = nodes.length > 0 ? fromNodes(dom, nodes) : fromRng(dom, selection.getRng());
      var matches = find$1(pattern, textSections);
      mark(matches, replacementNode);
      selection.moveToBookmark(bookmark);
      return matches.length;
    };

    var getElmIndex = function (elm) {
      var value = elm.getAttribute('data-mce-index');
      if (typeof value === 'number') {
        return '' + value;
      }
      return value;
    };
    var markAllMatches = function (editor, currentSearchState, pattern, inSelection) {
      var marker = editor.dom.create('span', { 'data-mce-bogus': 1 });
      marker.className = 'mce-match-marker';
      var node = editor.getBody();
      done(editor, currentSearchState, false);
      if (inSelection) {
        return findAndMarkInSelection(editor.dom, pattern, editor.selection, marker);
      } else {
        return findAndMark(editor.dom, pattern, node, marker);
      }
    };
    var unwrap = function (node) {
      var parentNode = node.parentNode;
      if (node.firstChild) {
        parentNode.insertBefore(node.firstChild, node);
      }
      node.parentNode.removeChild(node);
    };
    var findSpansByIndex = function (editor, index) {
      var spans = [];
      var nodes = global$1.toArray(editor.getBody().getElementsByTagName('span'));
      if (nodes.length) {
        for (var i = 0; i < nodes.length; i++) {
          var nodeIndex = getElmIndex(nodes[i]);
          if (nodeIndex === null || !nodeIndex.length) {
            continue;
          }
          if (nodeIndex === index.toString()) {
            spans.push(nodes[i]);
          }
        }
      }
      return spans;
    };
    var moveSelection = function (editor, currentSearchState, forward) {
      var searchState = currentSearchState.get();
      var testIndex = searchState.index;
      var dom = editor.dom;
      forward = forward !== false;
      if (forward) {
        if (testIndex + 1 === searchState.count) {
          testIndex = 0;
        } else {
          testIndex++;
        }
      } else {
        if (testIndex - 1 === -1) {
          testIndex = searchState.count - 1;
        } else {
          testIndex--;
        }
      }
      dom.removeClass(findSpansByIndex(editor, searchState.index), 'mce-match-marker-selected');
      var spans = findSpansByIndex(editor, testIndex);
      if (spans.length) {
        dom.addClass(findSpansByIndex(editor, testIndex), 'mce-match-marker-selected');
        editor.selection.scrollIntoView(spans[0]);
        return testIndex;
      }
      return -1;
    };
    var removeNode = function (dom, node) {
      var parent = node.parentNode;
      dom.remove(node);
      if (dom.isEmpty(parent)) {
        dom.remove(parent);
      }
    };
    var escapeSearchText = function (text, wholeWord) {
      var escapedText = text.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, '\\$&').replace(/\s/g, '[^\\S\\r\\n\\uFEFF]');
      var wordRegex = '(' + escapedText + ')';
      return wholeWord ? '(?:^|\\s|' + punctuation() + ')' + wordRegex + ('(?=$|\\s|' + punctuation() + ')') : wordRegex;
    };
    var find = function (editor, currentSearchState, text, matchCase, wholeWord, inSelection) {
      var selection = editor.selection;
      var escapedText = escapeSearchText(text, wholeWord);
      var isForwardSelection = selection.isForward();
      var pattern = {
        regex: new RegExp(escapedText, matchCase ? 'g' : 'gi'),
        matchIndex: 1
      };
      var count = markAllMatches(editor, currentSearchState, pattern, inSelection);
      if (global$2.browser.isSafari()) {
        selection.setRng(selection.getRng(), isForwardSelection);
      }
      if (count) {
        var newIndex = moveSelection(editor, currentSearchState, true);
        currentSearchState.set({
          index: newIndex,
          count: count,
          text: text,
          matchCase: matchCase,
          wholeWord: wholeWord,
          inSelection: inSelection
        });
      }
      return count;
    };
    var next = function (editor, currentSearchState) {
      var index = moveSelection(editor, currentSearchState, true);
      currentSearchState.set(__assign(__assign({}, currentSearchState.get()), { index: index }));
    };
    var prev = function (editor, currentSearchState) {
      var index = moveSelection(editor, currentSearchState, false);
      currentSearchState.set(__assign(__assign({}, currentSearchState.get()), { index: index }));
    };
    var isMatchSpan = function (node) {
      var matchIndex = getElmIndex(node);
      return matchIndex !== null && matchIndex.length > 0;
    };
    var replace = function (editor, currentSearchState, text, forward, all) {
      var searchState = currentSearchState.get();
      var currentIndex = searchState.index;
      var currentMatchIndex, nextIndex = currentIndex;
      forward = forward !== false;
      var node = editor.getBody();
      var nodes = global$1.grep(global$1.toArray(node.getElementsByTagName('span')), isMatchSpan);
      for (var i = 0; i < nodes.length; i++) {
        var nodeIndex = getElmIndex(nodes[i]);
        var matchIndex = currentMatchIndex = parseInt(nodeIndex, 10);
        if (all || matchIndex === searchState.index) {
          if (text.length) {
            nodes[i].firstChild.nodeValue = text;
            unwrap(nodes[i]);
          } else {
            removeNode(editor.dom, nodes[i]);
          }
          while (nodes[++i]) {
            matchIndex = parseInt(getElmIndex(nodes[i]), 10);
            if (matchIndex === currentMatchIndex) {
              removeNode(editor.dom, nodes[i]);
            } else {
              i--;
              break;
            }
          }
          if (forward) {
            nextIndex--;
          }
        } else if (currentMatchIndex > currentIndex) {
          nodes[i].setAttribute('data-mce-index', String(currentMatchIndex - 1));
        }
      }
      currentSearchState.set(__assign(__assign({}, searchState), {
        count: all ? 0 : searchState.count - 1,
        index: nextIndex
      }));
      if (forward) {
        next(editor, currentSearchState);
      } else {
        prev(editor, currentSearchState);
      }
      return !all && currentSearchState.get().count > 0;
    };
    var done = function (editor, currentSearchState, keepEditorSelection) {
      var startContainer, endContainer;
      var searchState = currentSearchState.get();
      var nodes = global$1.toArray(editor.getBody().getElementsByTagName('span'));
      for (var i = 0; i < nodes.length; i++) {
        var nodeIndex = getElmIndex(nodes[i]);
        if (nodeIndex !== null && nodeIndex.length) {
          if (nodeIndex === searchState.index.toString()) {
            if (!startContainer) {
              startContainer = nodes[i].firstChild;
            }
            endContainer = nodes[i].firstChild;
          }
          unwrap(nodes[i]);
        }
      }
      currentSearchState.set(__assign(__assign({}, searchState), {
        index: -1,
        count: 0,
        text: ''
      }));
      if (startContainer && endContainer) {
        var rng = editor.dom.createRng();
        rng.setStart(startContainer, 0);
        rng.setEnd(endContainer, endContainer.data.length);
        if (keepEditorSelection !== false) {
          editor.selection.setRng(rng);
        }
        return rng;
      }
    };
    var hasNext = function (editor, currentSearchState) {
      return currentSearchState.get().count > 1;
    };
    var hasPrev = function (editor, currentSearchState) {
      return currentSearchState.get().count > 1;
    };

    var get = function (editor, currentState) {
      var done$1 = function (keepEditorSelection) {
        return done(editor, currentState, keepEditorSelection);
      };
      var find$1 = function (text, matchCase, wholeWord, inSelection) {
        if (inSelection === void 0) {
          inSelection = false;
        }
        return find(editor, currentState, text, matchCase, wholeWord, inSelection);
      };
      var next$1 = function () {
        return next(editor, currentState);
      };
      var prev$1 = function () {
        return prev(editor, currentState);
      };
      var replace$1 = function (text, forward, all) {
        return replace(editor, currentState, text, forward, all);
      };
      return {
        done: done$1,
        find: find$1,
        next: next$1,
        prev: prev$1,
        replace: replace$1
      };
    };

    var singleton = function (doRevoke) {
      var subject = Cell(Optional.none());
      var revoke = function () {
        return subject.get().each(doRevoke);
      };
      var clear = function () {
        revoke();
        subject.set(Optional.none());
      };
      var isSet = function () {
        return subject.get().isSome();
      };
      var get = function () {
        return subject.get();
      };
      var set = function (s) {
        revoke();
        subject.set(Optional.some(s));
      };
      return {
        clear: clear,
        isSet: isSet,
        get: get,
        set: set
      };
    };
    var value = function () {
      var subject = singleton(noop);
      var on = function (f) {
        return subject.get().each(f);
      };
      return __assign(__assign({}, subject), { on: on });
    };

    var open = function (editor, currentSearchState) {
      var dialogApi = value();
      editor.undoManager.add();
      var selectedText = global$1.trim(editor.selection.getContent({ format: 'text' }));
      var updateButtonStates = function (api) {
        var updateNext = hasNext(editor, currentSearchState) ? api.enable : api.disable;
        updateNext('next');
        var updatePrev = hasPrev(editor, currentSearchState) ? api.enable : api.disable;
        updatePrev('prev');
      };
      var updateSearchState = function (api) {
        var data = api.getData();
        var current = currentSearchState.get();
        currentSearchState.set(__assign(__assign({}, current), {
          matchCase: data.matchcase,
          wholeWord: data.wholewords,
          inSelection: data.inselection
        }));
      };
      var disableAll = function (api, disable) {
        var buttons = [
          'replace',
          'replaceall',
          'prev',
          'next'
        ];
        var toggle = disable ? api.disable : api.enable;
        each(buttons, toggle);
      };
      var notFoundAlert = function (api) {
        editor.windowManager.alert('Could not find the specified string.', function () {
          api.focus('findtext');
        });
      };
      var focusButtonIfRequired = function (api, name) {
        if (global$2.browser.isSafari() && global$2.deviceType.isTouch() && (name === 'find' || name === 'replace' || name === 'replaceall')) {
          api.focus(name);
        }
      };
      var reset = function (api) {
        done(editor, currentSearchState, false);
        disableAll(api, true);
        updateButtonStates(api);
      };
      var doFind = function (api) {
        var data = api.getData();
        var last = currentSearchState.get();
        if (!data.findtext.length) {
          reset(api);
          return;
        }
        if (last.text === data.findtext && last.matchCase === data.matchcase && last.wholeWord === data.wholewords) {
          next(editor, currentSearchState);
        } else {
          var count = find(editor, currentSearchState, data.findtext, data.matchcase, data.wholewords, data.inselection);
          if (count <= 0) {
            notFoundAlert(api);
          }
          disableAll(api, count === 0);
        }
        updateButtonStates(api);
      };
      var initialState = currentSearchState.get();
      var initialData = {
        findtext: selectedText,
        replacetext: '',
        wholewords: initialState.wholeWord,
        matchcase: initialState.matchCase,
        inselection: initialState.inSelection
      };
      var spec = {
        title: 'Find and Replace',
        size: 'normal',
        body: {
          type: 'panel',
          items: [
            {
              type: 'bar',
              items: [
                {
                  type: 'input',
                  name: 'findtext',
                  placeholder: 'Find',
                  maximized: true,
                  inputMode: 'search'
                },
                {
                  type: 'button',
                  name: 'prev',
                  text: 'Previous',
                  icon: 'action-prev',
                  disabled: true,
                  borderless: true
                },
                {
                  type: 'button',
                  name: 'next',
                  text: 'Next',
                  icon: 'action-next',
                  disabled: true,
                  borderless: true
                }
              ]
            },
            {
              type: 'input',
              name: 'replacetext',
              placeholder: 'Replace with',
              inputMode: 'search'
            }
          ]
        },
        buttons: [
          {
            type: 'menu',
            name: 'options',
            icon: 'preferences',
            tooltip: 'Preferences',
            align: 'start',
            items: [
              {
                type: 'togglemenuitem',
                name: 'matchcase',
                text: 'Match case'
              },
              {
                type: 'togglemenuitem',
                name: 'wholewords',
                text: 'Find whole words only'
              },
              {
                type: 'togglemenuitem',
                name: 'inselection',
                text: 'Find in selection'
              }
            ]
          },
          {
            type: 'custom',
            name: 'find',
            text: 'Find',
            primary: true
          },
          {
            type: 'custom',
            name: 'replace',
            text: 'Replace',
            disabled: true
          },
          {
            type: 'custom',
            name: 'replaceall',
            text: 'Replace all',
            disabled: true
          }
        ],
        initialData: initialData,
        onChange: function (api, details) {
          if (details.name === 'findtext' && currentSearchState.get().count > 0) {
            reset(api);
          }
        },
        onAction: function (api, details) {
          var data = api.getData();
          switch (details.name) {
          case 'find':
            doFind(api);
            break;
          case 'replace':
            if (!replace(editor, currentSearchState, data.replacetext)) {
              reset(api);
            } else {
              updateButtonStates(api);
            }
            break;
          case 'replaceall':
            replace(editor, currentSearchState, data.replacetext, true, true);
            reset(api);
            break;
          case 'prev':
            prev(editor, currentSearchState);
            updateButtonStates(api);
            break;
          case 'next':
            next(editor, currentSearchState);
            updateButtonStates(api);
            break;
          case 'matchcase':
          case 'wholewords':
          case 'inselection':
            updateSearchState(api);
            reset(api);
            break;
          }
          focusButtonIfRequired(api, details.name);
        },
        onSubmit: function (api) {
          doFind(api);
          focusButtonIfRequired(api, 'find');
        },
        onClose: function () {
          editor.focus();
          done(editor, currentSearchState);
          editor.undoManager.add();
        }
      };
      dialogApi.set(editor.windowManager.open(spec, { inline: 'toolbar' }));
    };

    var register$1 = function (editor, currentSearchState) {
      editor.addCommand('SearchReplace', function () {
        open(editor, currentSearchState);
      });
    };

    var showDialog = function (editor, currentSearchState) {
      return function () {
        open(editor, currentSearchState);
      };
    };
    var register = function (editor, currentSearchState) {
      editor.ui.registry.addMenuItem('searchreplace', {
        text: 'Find and replace...',
        shortcut: 'Meta+F',
        onAction: showDialog(editor, currentSearchState),
        icon: 'search'
      });
      editor.ui.registry.addButton('searchreplace', {
        tooltip: 'Find and replace',
        onAction: showDialog(editor, currentSearchState),
        icon: 'search'
      });
      editor.shortcuts.add('Meta+F', '', showDialog(editor, currentSearchState));
    };

    function Plugin () {
      global$3.add('searchreplace', function (editor) {
        var currentSearchState = Cell({
          index: -1,
          count: 0,
          text: '',
          matchCase: false,
          wholeWord: false,
          inSelection: false
        });
        register$1(editor, currentSearchState);
        register(editor, currentSearchState);
        return get(editor, currentSearchState);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/textcolor/index.js":
/*!*********************************************************!*\
  !*** ./node_modules/tinymce/plugins/textcolor/index.js ***!
  \*********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "textcolor" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/textcolor')
//   ES2015:
//     import 'tinymce/plugins/textcolor'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/textcolor/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/textcolor/plugin.js":
/*!**********************************************************!*\
  !*** ./node_modules/tinymce/plugins/textcolor/plugin.js ***!
  \**********************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var global = tinymce.util.Tools.resolve('tinymce.PluginManager');

    function Plugin () {
      global.add('textcolor', function () {
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/textpattern/index.js":
/*!***********************************************************!*\
  !*** ./node_modules/tinymce/plugins/textpattern/index.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "textpattern" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/textpattern')
//   ES2015:
//     import 'tinymce/plugins/textpattern'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/textpattern/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/textpattern/plugin.js":
/*!************************************************************!*\
  !*** ./node_modules/tinymce/plugins/textpattern/plugin.js ***!
  \************************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var global$5 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var __assign = function () {
      __assign = Object.assign || function __assign(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
          s = arguments[i];
          for (var p in s)
            if (Object.prototype.hasOwnProperty.call(s, p))
              t[p] = s[p];
        }
        return t;
      };
      return __assign.apply(this, arguments);
    };
    function __spreadArray(to, from, pack) {
      if (pack || arguments.length === 2)
        for (var i = 0, l = from.length, ar; i < l; i++) {
          if (ar || !(i in from)) {
            if (!ar)
              ar = Array.prototype.slice.call(from, 0, i);
            ar[i] = from[i];
          }
        }
      return to.concat(ar || Array.prototype.slice.call(from));
    }

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isString = isType('string');
    var isObject = isType('object');
    var isArray = isType('array');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var die = function (msg) {
      return function () {
        throw new Error(msg);
      };
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var nativeSlice = Array.prototype.slice;
    var nativeIndexOf = Array.prototype.indexOf;
    var rawIndexOf = function (ts, t) {
      return nativeIndexOf.call(ts, t);
    };
    var contains = function (xs, x) {
      return rawIndexOf(xs, x) > -1;
    };
    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var eachr = function (xs, f) {
      for (var i = xs.length - 1; i >= 0; i--) {
        var x = xs[i];
        f(x, i);
      }
    };
    var filter = function (xs, pred) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          r.push(x);
        }
      }
      return r;
    };
    var foldr = function (xs, f, acc) {
      eachr(xs, function (x, i) {
        acc = f(acc, x, i);
      });
      return acc;
    };
    var foldl = function (xs, f, acc) {
      each(xs, function (x, i) {
        acc = f(acc, x, i);
      });
      return acc;
    };
    var findUntil = function (xs, pred, until) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          return Optional.some(x);
        } else if (until(x, i)) {
          break;
        }
      }
      return Optional.none();
    };
    var find = function (xs, pred) {
      return findUntil(xs, pred, never);
    };
    var forall = function (xs, pred) {
      for (var i = 0, len = xs.length; i < len; ++i) {
        var x = xs[i];
        if (pred(x, i) !== true) {
          return false;
        }
      }
      return true;
    };
    var sort = function (xs, comparator) {
      var copy = nativeSlice.call(xs, 0);
      copy.sort(comparator);
      return copy;
    };
    var get$1 = function (xs, i) {
      return i >= 0 && i < xs.length ? Optional.some(xs[i]) : Optional.none();
    };
    var head = function (xs) {
      return get$1(xs, 0);
    };

    var keys = Object.keys;
    var hasOwnProperty = Object.hasOwnProperty;
    var has = function (obj, key) {
      return hasOwnProperty.call(obj, key);
    };

    var generate$1 = function (cases) {
      if (!isArray(cases)) {
        throw new Error('cases must be an array');
      }
      if (cases.length === 0) {
        throw new Error('there must be at least one case');
      }
      var constructors = [];
      var adt = {};
      each(cases, function (acase, count) {
        var keys$1 = keys(acase);
        if (keys$1.length !== 1) {
          throw new Error('one and only one name per case');
        }
        var key = keys$1[0];
        var value = acase[key];
        if (adt[key] !== undefined) {
          throw new Error('duplicate key detected:' + key);
        } else if (key === 'cata') {
          throw new Error('cannot have a case named cata (sorry)');
        } else if (!isArray(value)) {
          throw new Error('case arguments must be an array');
        }
        constructors.push(key);
        adt[key] = function () {
          var args = [];
          for (var _i = 0; _i < arguments.length; _i++) {
            args[_i] = arguments[_i];
          }
          var argLength = args.length;
          if (argLength !== value.length) {
            throw new Error('Wrong number of arguments to case ' + key + '. Expected ' + value.length + ' (' + value + '), got ' + argLength);
          }
          var match = function (branches) {
            var branchKeys = keys(branches);
            if (constructors.length !== branchKeys.length) {
              throw new Error('Wrong number of arguments to match. Expected: ' + constructors.join(',') + '\nActual: ' + branchKeys.join(','));
            }
            var allReqd = forall(constructors, function (reqKey) {
              return contains(branchKeys, reqKey);
            });
            if (!allReqd) {
              throw new Error('Not all branches were specified when using match. Specified: ' + branchKeys.join(', ') + '\nRequired: ' + constructors.join(', '));
            }
            return branches[key].apply(null, args);
          };
          return {
            fold: function () {
              var foldArgs = [];
              for (var _i = 0; _i < arguments.length; _i++) {
                foldArgs[_i] = arguments[_i];
              }
              if (foldArgs.length !== cases.length) {
                throw new Error('Wrong number of arguments to fold. Expected ' + cases.length + ', got ' + foldArgs.length);
              }
              var target = foldArgs[count];
              return target.apply(null, args);
            },
            match: match,
            log: function (label) {
              console.log(label, {
                constructors: constructors,
                constructor: key,
                params: args
              });
            }
          };
        };
      });
      return adt;
    };
    var Adt = { generate: generate$1 };

    Adt.generate([
      {
        bothErrors: [
          'error1',
          'error2'
        ]
      },
      {
        firstError: [
          'error1',
          'value2'
        ]
      },
      {
        secondError: [
          'value1',
          'error2'
        ]
      },
      {
        bothValues: [
          'value1',
          'value2'
        ]
      }
    ]);
    var partition = function (results) {
      var errors = [];
      var values = [];
      each(results, function (result) {
        result.fold(function (err) {
          errors.push(err);
        }, function (value) {
          values.push(value);
        });
      });
      return {
        errors: errors,
        values: values
      };
    };

    var value = function (o) {
      var or = function (_opt) {
        return value(o);
      };
      var orThunk = function (_f) {
        return value(o);
      };
      var map = function (f) {
        return value(f(o));
      };
      var mapError = function (_f) {
        return value(o);
      };
      var each = function (f) {
        f(o);
      };
      var bind = function (f) {
        return f(o);
      };
      var fold = function (_, onValue) {
        return onValue(o);
      };
      var exists = function (f) {
        return f(o);
      };
      var forall = function (f) {
        return f(o);
      };
      var toOptional = function () {
        return Optional.some(o);
      };
      return {
        isValue: always,
        isError: never,
        getOr: constant(o),
        getOrThunk: constant(o),
        getOrDie: constant(o),
        or: or,
        orThunk: orThunk,
        fold: fold,
        map: map,
        mapError: mapError,
        each: each,
        bind: bind,
        exists: exists,
        forall: forall,
        toOptional: toOptional
      };
    };
    var error$1 = function (message) {
      var getOrThunk = function (f) {
        return f();
      };
      var getOrDie = function () {
        return die(String(message))();
      };
      var or = identity;
      var orThunk = function (f) {
        return f();
      };
      var map = function (_f) {
        return error$1(message);
      };
      var mapError = function (f) {
        return error$1(f(message));
      };
      var bind = function (_f) {
        return error$1(message);
      };
      var fold = function (onError, _) {
        return onError(message);
      };
      return {
        isValue: never,
        isError: always,
        getOr: identity,
        getOrThunk: getOrThunk,
        getOrDie: getOrDie,
        or: or,
        orThunk: orThunk,
        fold: fold,
        map: map,
        mapError: mapError,
        each: noop,
        bind: bind,
        exists: never,
        forall: always,
        toOptional: Optional.none
      };
    };
    var fromOption = function (opt, err) {
      return opt.fold(function () {
        return error$1(err);
      }, value);
    };
    var Result = {
      value: value,
      error: error$1,
      fromOption: fromOption
    };

    var isInlinePattern = function (pattern) {
      return pattern.type === 'inline-command' || pattern.type === 'inline-format';
    };
    var isBlockPattern = function (pattern) {
      return pattern.type === 'block-command' || pattern.type === 'block-format';
    };
    var sortPatterns = function (patterns) {
      return sort(patterns, function (a, b) {
        if (a.start.length === b.start.length) {
          return 0;
        }
        return a.start.length > b.start.length ? -1 : 1;
      });
    };
    var normalizePattern = function (pattern) {
      var err = function (message) {
        return Result.error({
          message: message,
          pattern: pattern
        });
      };
      var formatOrCmd = function (name, onFormat, onCommand) {
        if (pattern.format !== undefined) {
          var formats = void 0;
          if (isArray(pattern.format)) {
            if (!forall(pattern.format, isString)) {
              return err(name + ' pattern has non-string items in the `format` array');
            }
            formats = pattern.format;
          } else if (isString(pattern.format)) {
            formats = [pattern.format];
          } else {
            return err(name + ' pattern has non-string `format` parameter');
          }
          return Result.value(onFormat(formats));
        } else if (pattern.cmd !== undefined) {
          if (!isString(pattern.cmd)) {
            return err(name + ' pattern has non-string `cmd` parameter');
          }
          return Result.value(onCommand(pattern.cmd, pattern.value));
        } else {
          return err(name + ' pattern is missing both `format` and `cmd` parameters');
        }
      };
      if (!isObject(pattern)) {
        return err('Raw pattern is not an object');
      }
      if (!isString(pattern.start)) {
        return err('Raw pattern is missing `start` parameter');
      }
      if (pattern.end !== undefined) {
        if (!isString(pattern.end)) {
          return err('Inline pattern has non-string `end` parameter');
        }
        if (pattern.start.length === 0 && pattern.end.length === 0) {
          return err('Inline pattern has empty `start` and `end` parameters');
        }
        var start_1 = pattern.start;
        var end_1 = pattern.end;
        if (end_1.length === 0) {
          end_1 = start_1;
          start_1 = '';
        }
        return formatOrCmd('Inline', function (format) {
          return {
            type: 'inline-format',
            start: start_1,
            end: end_1,
            format: format
          };
        }, function (cmd, value) {
          return {
            type: 'inline-command',
            start: start_1,
            end: end_1,
            cmd: cmd,
            value: value
          };
        });
      } else if (pattern.replacement !== undefined) {
        if (!isString(pattern.replacement)) {
          return err('Replacement pattern has non-string `replacement` parameter');
        }
        if (pattern.start.length === 0) {
          return err('Replacement pattern has empty `start` parameter');
        }
        return Result.value({
          type: 'inline-command',
          start: '',
          end: pattern.start,
          cmd: 'mceInsertContent',
          value: pattern.replacement
        });
      } else {
        if (pattern.start.length === 0) {
          return err('Block pattern has empty `start` parameter');
        }
        return formatOrCmd('Block', function (formats) {
          return {
            type: 'block-format',
            start: pattern.start,
            format: formats[0]
          };
        }, function (command, commandValue) {
          return {
            type: 'block-command',
            start: pattern.start,
            cmd: command,
            value: commandValue
          };
        });
      }
    };
    var denormalizePattern = function (pattern) {
      if (pattern.type === 'block-command') {
        return {
          start: pattern.start,
          cmd: pattern.cmd,
          value: pattern.value
        };
      } else if (pattern.type === 'block-format') {
        return {
          start: pattern.start,
          format: pattern.format
        };
      } else if (pattern.type === 'inline-command') {
        if (pattern.cmd === 'mceInsertContent' && pattern.start === '') {
          return {
            start: pattern.end,
            replacement: pattern.value
          };
        } else {
          return {
            start: pattern.start,
            end: pattern.end,
            cmd: pattern.cmd,
            value: pattern.value
          };
        }
      } else if (pattern.type === 'inline-format') {
        return {
          start: pattern.start,
          end: pattern.end,
          format: pattern.format.length === 1 ? pattern.format[0] : pattern.format
        };
      }
    };
    var createPatternSet = function (patterns) {
      return {
        inlinePatterns: filter(patterns, isInlinePattern),
        blockPatterns: sortPatterns(filter(patterns, isBlockPattern))
      };
    };

    var get = function (patternsState) {
      var setPatterns = function (newPatterns) {
        var normalized = partition(map(newPatterns, normalizePattern));
        if (normalized.errors.length > 0) {
          var firstError = normalized.errors[0];
          throw new Error(firstError.message + ':\n' + JSON.stringify(firstError.pattern, null, 2));
        }
        patternsState.set(createPatternSet(normalized.values));
      };
      var getPatterns = function () {
        return __spreadArray(__spreadArray([], map(patternsState.get().inlinePatterns, denormalizePattern), true), map(patternsState.get().blockPatterns, denormalizePattern), true);
      };
      return {
        setPatterns: setPatterns,
        getPatterns: getPatterns
      };
    };

    var Global = typeof window !== 'undefined' ? window : Function('return this;')();

    var error = function () {
      var args = [];
      for (var _i = 0; _i < arguments.length; _i++) {
        args[_i] = arguments[_i];
      }
      var console = Global.console;
      if (console) {
        if (console.error) {
          console.error.apply(console, args);
        } else {
          console.log.apply(console, args);
        }
      }
    };
    var defaultPatterns = [
      {
        start: '*',
        end: '*',
        format: 'italic'
      },
      {
        start: '**',
        end: '**',
        format: 'bold'
      },
      {
        start: '#',
        format: 'h1'
      },
      {
        start: '##',
        format: 'h2'
      },
      {
        start: '###',
        format: 'h3'
      },
      {
        start: '####',
        format: 'h4'
      },
      {
        start: '#####',
        format: 'h5'
      },
      {
        start: '######',
        format: 'h6'
      },
      {
        start: '1. ',
        cmd: 'InsertOrderedList'
      },
      {
        start: '* ',
        cmd: 'InsertUnorderedList'
      },
      {
        start: '- ',
        cmd: 'InsertUnorderedList'
      }
    ];
    var getPatternSet = function (editor) {
      var patterns = editor.getParam('textpattern_patterns', defaultPatterns, 'array');
      if (!isArray(patterns)) {
        error('The setting textpattern_patterns should be an array');
        return {
          inlinePatterns: [],
          blockPatterns: []
        };
      }
      var normalized = partition(map(patterns, normalizePattern));
      each(normalized.errors, function (err) {
        return error(err.message, err.pattern);
      });
      return createPatternSet(normalized.values);
    };
    var getForcedRootBlock = function (editor) {
      var block = editor.getParam('forced_root_block', 'p');
      if (block === false) {
        return '';
      } else if (block === true) {
        return 'p';
      } else {
        return block;
      }
    };

    var global$4 = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var global$3 = tinymce.util.Tools.resolve('tinymce.util.VK');

    var zeroWidth = '\uFEFF';
    var nbsp = '\xA0';

    var global$2 = tinymce.util.Tools.resolve('tinymce.util.Tools');

    var global$1 = tinymce.util.Tools.resolve('tinymce.dom.DOMUtils');

    var global = tinymce.util.Tools.resolve('tinymce.dom.TextSeeker');

    var point = function (container, offset) {
      return {
        container: container,
        offset: offset
      };
    };

    var isText = function (node) {
      return node.nodeType === Node.TEXT_NODE;
    };
    var cleanEmptyNodes = function (dom, node, isRoot) {
      if (node && dom.isEmpty(node) && !isRoot(node)) {
        var parent_1 = node.parentNode;
        dom.remove(node);
        cleanEmptyNodes(dom, parent_1, isRoot);
      }
    };
    var deleteRng = function (dom, rng, isRoot, clean) {
      if (clean === void 0) {
        clean = true;
      }
      var startParent = rng.startContainer.parentNode;
      var endParent = rng.endContainer.parentNode;
      rng.deleteContents();
      if (clean && !isRoot(rng.startContainer)) {
        if (isText(rng.startContainer) && rng.startContainer.data.length === 0) {
          dom.remove(rng.startContainer);
        }
        if (isText(rng.endContainer) && rng.endContainer.data.length === 0) {
          dom.remove(rng.endContainer);
        }
        cleanEmptyNodes(dom, startParent, isRoot);
        if (startParent !== endParent) {
          cleanEmptyNodes(dom, endParent, isRoot);
        }
      }
    };
    var isBlockFormatName = function (name, formatter) {
      var formatSet = formatter.get(name);
      return isArray(formatSet) && head(formatSet).exists(function (format) {
        return has(format, 'block');
      });
    };
    var isReplacementPattern = function (pattern) {
      return pattern.start.length === 0;
    };
    var getParentBlock = function (editor, rng) {
      var parentBlockOpt = Optional.from(editor.dom.getParent(rng.startContainer, editor.dom.isBlock));
      if (getForcedRootBlock(editor) === '') {
        return parentBlockOpt.orThunk(function () {
          return Optional.some(editor.getBody());
        });
      } else {
        return parentBlockOpt;
      }
    };

    var DOM = global$1.DOM;
    var alwaysNext = function (startNode) {
      return function (node) {
        return startNode === node ? -1 : 0;
      };
    };
    var isBoundary = function (dom) {
      return function (node) {
        return dom.isBlock(node) || contains([
          'BR',
          'IMG',
          'HR',
          'INPUT'
        ], node.nodeName) || dom.getContentEditable(node) === 'false';
      };
    };
    var textBefore = function (node, offset, rootNode) {
      if (isText(node) && offset >= 0) {
        return Optional.some(point(node, offset));
      } else {
        var textSeeker = global(DOM);
        return Optional.from(textSeeker.backwards(node, offset, alwaysNext(node), rootNode)).map(function (prev) {
          return point(prev.container, prev.container.data.length);
        });
      }
    };
    var textAfter = function (node, offset, rootNode) {
      if (isText(node) && offset >= node.length) {
        return Optional.some(point(node, offset));
      } else {
        var textSeeker = global(DOM);
        return Optional.from(textSeeker.forwards(node, offset, alwaysNext(node), rootNode)).map(function (prev) {
          return point(prev.container, 0);
        });
      }
    };
    var scanLeft = function (node, offset, rootNode) {
      if (!isText(node)) {
        return Optional.none();
      }
      var text = node.textContent;
      if (offset >= 0 && offset <= text.length) {
        return Optional.some(point(node, offset));
      } else {
        var textSeeker = global(DOM);
        return Optional.from(textSeeker.backwards(node, offset, alwaysNext(node), rootNode)).bind(function (prev) {
          var prevText = prev.container.data;
          return scanLeft(prev.container, offset + prevText.length, rootNode);
        });
      }
    };
    var scanRight = function (node, offset, rootNode) {
      if (!isText(node)) {
        return Optional.none();
      }
      var text = node.textContent;
      if (offset <= text.length) {
        return Optional.some(point(node, offset));
      } else {
        var textSeeker = global(DOM);
        return Optional.from(textSeeker.forwards(node, offset, alwaysNext(node), rootNode)).bind(function (next) {
          return scanRight(next.container, offset - text.length, rootNode);
        });
      }
    };
    var repeatLeft = function (dom, node, offset, process, rootNode) {
      var search = global(dom, isBoundary(dom));
      return Optional.from(search.backwards(node, offset, process, rootNode));
    };

    var generatePath = function (root, node, offset) {
      if (isText(node) && (offset < 0 || offset > node.data.length)) {
        return [];
      }
      var p = [offset];
      var current = node;
      while (current !== root && current.parentNode) {
        var parent_1 = current.parentNode;
        for (var i = 0; i < parent_1.childNodes.length; i++) {
          if (parent_1.childNodes[i] === current) {
            p.push(i);
            break;
          }
        }
        current = parent_1;
      }
      return current === root ? p.reverse() : [];
    };
    var generatePathRange = function (root, startNode, startOffset, endNode, endOffset) {
      var start = generatePath(root, startNode, startOffset);
      var end = generatePath(root, endNode, endOffset);
      return {
        start: start,
        end: end
      };
    };
    var resolvePath = function (root, path) {
      var nodePath = path.slice();
      var offset = nodePath.pop();
      var resolvedNode = foldl(nodePath, function (optNode, index) {
        return optNode.bind(function (node) {
          return Optional.from(node.childNodes[index]);
        });
      }, Optional.some(root));
      return resolvedNode.bind(function (node) {
        if (isText(node) && (offset < 0 || offset > node.data.length)) {
          return Optional.none();
        } else {
          return Optional.some({
            node: node,
            offset: offset
          });
        }
      });
    };
    var resolvePathRange = function (root, range) {
      return resolvePath(root, range.start).bind(function (_a) {
        var startNode = _a.node, startOffset = _a.offset;
        return resolvePath(root, range.end).map(function (_a) {
          var endNode = _a.node, endOffset = _a.offset;
          var rng = document.createRange();
          rng.setStart(startNode, startOffset);
          rng.setEnd(endNode, endOffset);
          return rng;
        });
      });
    };
    var generatePathRangeFromRange = function (root, range) {
      return generatePathRange(root, range.startContainer, range.startOffset, range.endContainer, range.endOffset);
    };

    var stripPattern = function (dom, block, pattern) {
      var firstTextNode = textAfter(block, 0, block);
      firstTextNode.each(function (spot) {
        var node = spot.container;
        scanRight(node, pattern.start.length, block).each(function (end) {
          var rng = dom.createRng();
          rng.setStart(node, 0);
          rng.setEnd(end.container, end.offset);
          deleteRng(dom, rng, function (e) {
            return e === block;
          });
        });
      });
    };
    var applyPattern$1 = function (editor, match) {
      var dom = editor.dom;
      var pattern = match.pattern;
      var rng = resolvePathRange(dom.getRoot(), match.range).getOrDie('Unable to resolve path range');
      getParentBlock(editor, rng).each(function (block) {
        if (pattern.type === 'block-format') {
          if (isBlockFormatName(pattern.format, editor.formatter)) {
            editor.undoManager.transact(function () {
              stripPattern(editor.dom, block, pattern);
              editor.formatter.apply(pattern.format);
            });
          }
        } else if (pattern.type === 'block-command') {
          editor.undoManager.transact(function () {
            stripPattern(editor.dom, block, pattern);
            editor.execCommand(pattern.cmd, false, pattern.value);
          });
        }
      });
      return true;
    };
    var findPattern$1 = function (patterns, text) {
      var nuText = text.replace(nbsp, ' ');
      return find(patterns, function (pattern) {
        return text.indexOf(pattern.start) === 0 || nuText.indexOf(pattern.start) === 0;
      });
    };
    var findPatterns$1 = function (editor, patterns) {
      var dom = editor.dom;
      var rng = editor.selection.getRng();
      return getParentBlock(editor, rng).filter(function (block) {
        var forcedRootBlock = getForcedRootBlock(editor);
        var matchesForcedRootBlock = forcedRootBlock === '' && dom.is(block, 'body') || dom.is(block, forcedRootBlock);
        return block !== null && matchesForcedRootBlock;
      }).bind(function (block) {
        var blockText = block.textContent;
        var matchedPattern = findPattern$1(patterns, blockText);
        return matchedPattern.map(function (pattern) {
          if (global$2.trim(blockText).length === pattern.start.length) {
            return [];
          }
          return [{
              pattern: pattern,
              range: generatePathRange(dom.getRoot(), block, 0, block, 0)
            }];
        });
      }).getOr([]);
    };
    var applyMatches$1 = function (editor, matches) {
      if (matches.length === 0) {
        return;
      }
      var bookmark = editor.selection.getBookmark();
      each(matches, function (match) {
        return applyPattern$1(editor, match);
      });
      editor.selection.moveToBookmark(bookmark);
    };

    var unique = 0;
    var generate = function (prefix) {
      var date = new Date();
      var time = date.getTime();
      var random = Math.floor(Math.random() * 1000000000);
      unique++;
      return prefix + '_' + random + unique + String(time);
    };

    var checkRange = function (str, substr, start) {
      return substr === '' || str.length >= substr.length && str.substr(start, start + substr.length) === substr;
    };
    var endsWith = function (str, suffix) {
      return checkRange(str, suffix, str.length - suffix.length);
    };

    var newMarker = function (dom, id) {
      return dom.create('span', {
        'data-mce-type': 'bookmark',
        id: id
      });
    };
    var rangeFromMarker = function (dom, marker) {
      var rng = dom.createRng();
      rng.setStartAfter(marker.start);
      rng.setEndBefore(marker.end);
      return rng;
    };
    var createMarker = function (dom, markerPrefix, pathRange) {
      var rng = resolvePathRange(dom.getRoot(), pathRange).getOrDie('Unable to resolve path range');
      var startNode = rng.startContainer;
      var endNode = rng.endContainer;
      var textEnd = rng.endOffset === 0 ? endNode : endNode.splitText(rng.endOffset);
      var textStart = rng.startOffset === 0 ? startNode : startNode.splitText(rng.startOffset);
      return {
        prefix: markerPrefix,
        end: textEnd.parentNode.insertBefore(newMarker(dom, markerPrefix + '-end'), textEnd),
        start: textStart.parentNode.insertBefore(newMarker(dom, markerPrefix + '-start'), textStart)
      };
    };
    var removeMarker = function (dom, marker, isRoot) {
      cleanEmptyNodes(dom, dom.get(marker.prefix + '-end'), isRoot);
      cleanEmptyNodes(dom, dom.get(marker.prefix + '-start'), isRoot);
    };

    var matchesPattern = function (dom, block, patternContent) {
      return function (element, offset) {
        var text = element.data;
        var searchText = text.substring(0, offset);
        var startEndIndex = searchText.lastIndexOf(patternContent.charAt(patternContent.length - 1));
        var startIndex = searchText.lastIndexOf(patternContent);
        if (startIndex !== -1) {
          return startIndex + patternContent.length;
        } else if (startEndIndex !== -1) {
          return startEndIndex + 1;
        } else {
          return -1;
        }
      };
    };
    var findPatternStartFromSpot = function (dom, pattern, block, spot) {
      var startPattern = pattern.start;
      var startSpot = repeatLeft(dom, spot.container, spot.offset, matchesPattern(dom, block, startPattern), block);
      return startSpot.bind(function (spot) {
        if (spot.offset >= startPattern.length) {
          var rng = dom.createRng();
          rng.setStart(spot.container, spot.offset - startPattern.length);
          rng.setEnd(spot.container, spot.offset);
          return Optional.some(rng);
        } else {
          var offset = spot.offset - startPattern.length;
          return scanLeft(spot.container, offset, block).map(function (nextSpot) {
            var rng = dom.createRng();
            rng.setStart(nextSpot.container, nextSpot.offset);
            rng.setEnd(spot.container, spot.offset);
            return rng;
          }).filter(function (rng) {
            return rng.toString() === startPattern;
          }).orThunk(function () {
            return findPatternStartFromSpot(dom, pattern, block, point(spot.container, 0));
          });
        }
      });
    };
    var findPatternStart = function (dom, pattern, node, offset, block, requireGap) {
      if (requireGap === void 0) {
        requireGap = false;
      }
      if (pattern.start.length === 0 && !requireGap) {
        var rng = dom.createRng();
        rng.setStart(node, offset);
        rng.setEnd(node, offset);
        return Optional.some(rng);
      }
      return textBefore(node, offset, block).bind(function (spot) {
        var start = findPatternStartFromSpot(dom, pattern, block, spot);
        return start.bind(function (startRange) {
          if (requireGap) {
            if (startRange.endContainer === spot.container && startRange.endOffset === spot.offset) {
              return Optional.none();
            } else if (spot.offset === 0 && startRange.endContainer.textContent.length === startRange.endOffset) {
              return Optional.none();
            }
          }
          return Optional.some(startRange);
        });
      });
    };
    var findPattern = function (editor, block, details) {
      var dom = editor.dom;
      var root = dom.getRoot();
      var pattern = details.pattern;
      var endNode = details.position.container;
      var endOffset = details.position.offset;
      return scanLeft(endNode, endOffset - details.pattern.end.length, block).bind(function (spot) {
        var endPathRng = generatePathRange(root, spot.container, spot.offset, endNode, endOffset);
        if (isReplacementPattern(pattern)) {
          return Optional.some({
            matches: [{
                pattern: pattern,
                startRng: endPathRng,
                endRng: endPathRng
              }],
            position: spot
          });
        } else {
          var resultsOpt = findPatternsRec(editor, details.remainingPatterns, spot.container, spot.offset, block);
          var results_1 = resultsOpt.getOr({
            matches: [],
            position: spot
          });
          var pos = results_1.position;
          var start = findPatternStart(dom, pattern, pos.container, pos.offset, block, resultsOpt.isNone());
          return start.map(function (startRng) {
            var startPathRng = generatePathRangeFromRange(root, startRng);
            return {
              matches: results_1.matches.concat([{
                  pattern: pattern,
                  startRng: startPathRng,
                  endRng: endPathRng
                }]),
              position: point(startRng.startContainer, startRng.startOffset)
            };
          });
        }
      });
    };
    var findPatternsRec = function (editor, patterns, node, offset, block) {
      var dom = editor.dom;
      return textBefore(node, offset, dom.getRoot()).bind(function (endSpot) {
        var rng = dom.createRng();
        rng.setStart(block, 0);
        rng.setEnd(node, offset);
        var text = rng.toString();
        for (var i = 0; i < patterns.length; i++) {
          var pattern = patterns[i];
          if (!endsWith(text, pattern.end)) {
            continue;
          }
          var patternsWithoutCurrent = patterns.slice();
          patternsWithoutCurrent.splice(i, 1);
          var result = findPattern(editor, block, {
            pattern: pattern,
            remainingPatterns: patternsWithoutCurrent,
            position: endSpot
          });
          if (result.isSome()) {
            return result;
          }
        }
        return Optional.none();
      });
    };
    var applyPattern = function (editor, pattern, patternRange) {
      editor.selection.setRng(patternRange);
      if (pattern.type === 'inline-format') {
        each(pattern.format, function (format) {
          editor.formatter.apply(format);
        });
      } else {
        editor.execCommand(pattern.cmd, false, pattern.value);
      }
    };
    var applyReplacementPattern = function (editor, pattern, marker, isRoot) {
      var markerRange = rangeFromMarker(editor.dom, marker);
      deleteRng(editor.dom, markerRange, isRoot);
      applyPattern(editor, pattern, markerRange);
    };
    var applyPatternWithContent = function (editor, pattern, startMarker, endMarker, isRoot) {
      var dom = editor.dom;
      var markerEndRange = rangeFromMarker(dom, endMarker);
      var markerStartRange = rangeFromMarker(dom, startMarker);
      deleteRng(dom, markerStartRange, isRoot);
      deleteRng(dom, markerEndRange, isRoot);
      var patternMarker = {
        prefix: startMarker.prefix,
        start: startMarker.end,
        end: endMarker.start
      };
      var patternRange = rangeFromMarker(dom, patternMarker);
      applyPattern(editor, pattern, patternRange);
    };
    var addMarkers = function (dom, matches) {
      var markerPrefix = generate('mce_textpattern');
      var matchesWithEnds = foldr(matches, function (acc, match) {
        var endMarker = createMarker(dom, markerPrefix + ('_end' + acc.length), match.endRng);
        return acc.concat([__assign(__assign({}, match), { endMarker: endMarker })]);
      }, []);
      return foldr(matchesWithEnds, function (acc, match) {
        var idx = matchesWithEnds.length - acc.length - 1;
        var startMarker = isReplacementPattern(match.pattern) ? match.endMarker : createMarker(dom, markerPrefix + ('_start' + idx), match.startRng);
        return acc.concat([__assign(__assign({}, match), { startMarker: startMarker })]);
      }, []);
    };
    var findPatterns = function (editor, patterns, space) {
      var rng = editor.selection.getRng();
      if (rng.collapsed === false) {
        return [];
      }
      return getParentBlock(editor, rng).bind(function (block) {
        var offset = rng.startOffset - (space ? 1 : 0);
        return findPatternsRec(editor, patterns, rng.startContainer, offset, block);
      }).fold(function () {
        return [];
      }, function (result) {
        return result.matches;
      });
    };
    var applyMatches = function (editor, matches) {
      if (matches.length === 0) {
        return;
      }
      var dom = editor.dom;
      var bookmark = editor.selection.getBookmark();
      var matchesWithMarkers = addMarkers(dom, matches);
      each(matchesWithMarkers, function (match) {
        var block = dom.getParent(match.startMarker.start, dom.isBlock);
        var isRoot = function (node) {
          return node === block;
        };
        if (isReplacementPattern(match.pattern)) {
          applyReplacementPattern(editor, match.pattern, match.endMarker, isRoot);
        } else {
          applyPatternWithContent(editor, match.pattern, match.startMarker, match.endMarker, isRoot);
        }
        removeMarker(dom, match.endMarker, isRoot);
        removeMarker(dom, match.startMarker, isRoot);
      });
      editor.selection.moveToBookmark(bookmark);
    };

    var handleEnter = function (editor, patternSet) {
      if (!editor.selection.isCollapsed()) {
        return false;
      }
      var inlineMatches = findPatterns(editor, patternSet.inlinePatterns, false);
      var blockMatches = findPatterns$1(editor, patternSet.blockPatterns);
      if (blockMatches.length > 0 || inlineMatches.length > 0) {
        editor.undoManager.add();
        editor.undoManager.extra(function () {
          editor.execCommand('mceInsertNewLine');
        }, function () {
          editor.insertContent(zeroWidth, { preserve_zwsp: true });
          applyMatches(editor, inlineMatches);
          applyMatches$1(editor, blockMatches);
          var range = editor.selection.getRng();
          var spot = textBefore(range.startContainer, range.startOffset, editor.dom.getRoot());
          editor.execCommand('mceInsertNewLine');
          spot.each(function (s) {
            var node = s.container;
            if (node.data.charAt(s.offset - 1) === zeroWidth) {
              node.deleteData(s.offset - 1, 1);
              cleanEmptyNodes(editor.dom, node.parentNode, function (e) {
                return e === editor.dom.getRoot();
              });
            }
          });
        });
        return true;
      }
      return false;
    };
    var handleInlineKey = function (editor, patternSet) {
      var inlineMatches = findPatterns(editor, patternSet.inlinePatterns, true);
      if (inlineMatches.length > 0) {
        editor.undoManager.transact(function () {
          applyMatches(editor, inlineMatches);
        });
      }
    };
    var checkKeyEvent = function (codes, event, predicate) {
      for (var i = 0; i < codes.length; i++) {
        if (predicate(codes[i], event)) {
          return true;
        }
      }
      return false;
    };
    var checkKeyCode = function (codes, event) {
      return checkKeyEvent(codes, event, function (code, event) {
        return code === event.keyCode && global$3.modifierPressed(event) === false;
      });
    };
    var checkCharCode = function (chars, event) {
      return checkKeyEvent(chars, event, function (chr, event) {
        return chr.charCodeAt(0) === event.charCode;
      });
    };

    var setup = function (editor, patternsState) {
      var charCodes = [
        ',',
        '.',
        ';',
        ':',
        '!',
        '?'
      ];
      var keyCodes = [32];
      editor.on('keydown', function (e) {
        if (e.keyCode === 13 && !global$3.modifierPressed(e)) {
          if (handleEnter(editor, patternsState.get())) {
            e.preventDefault();
          }
        }
      }, true);
      editor.on('keyup', function (e) {
        if (checkKeyCode(keyCodes, e)) {
          handleInlineKey(editor, patternsState.get());
        }
      });
      editor.on('keypress', function (e) {
        if (checkCharCode(charCodes, e)) {
          global$4.setEditorTimeout(editor, function () {
            handleInlineKey(editor, patternsState.get());
          });
        }
      });
    };

    function Plugin () {
      global$5.add('textpattern', function (editor) {
        var patternsState = Cell(getPatternSet(editor));
        setup(editor, patternsState);
        return get(patternsState);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tinymce/plugins/visualchars/index.js":
/*!***********************************************************!*\
  !*** ./node_modules/tinymce/plugins/visualchars/index.js ***!
  \***********************************************************/
/***/ ((__unused_webpack_module, __unused_webpack_exports, __webpack_require__) => {

// Exports the "visualchars" plugin for usage with module loaders
// Usage:
//   CommonJS:
//     require('tinymce/plugins/visualchars')
//   ES2015:
//     import 'tinymce/plugins/visualchars'
__webpack_require__(/*! ./plugin.js */ "./node_modules/tinymce/plugins/visualchars/plugin.js");

/***/ }),

/***/ "./node_modules/tinymce/plugins/visualchars/plugin.js":
/*!************************************************************!*\
  !*** ./node_modules/tinymce/plugins/visualchars/plugin.js ***!
  \************************************************************/
/***/ (() => {

/**
 * Copyright (c) Tiny Technologies, Inc. All rights reserved.
 * Licensed under the LGPL or a commercial license.
 * For LGPL see License.txt in the project root for license information.
 * For commercial licenses see https://www.tiny.cloud/
 *
 * Version: 5.10.9 (2023-11-15)
 */
(function () {
    'use strict';

    var Cell = function (initial) {
      var value = initial;
      var get = function () {
        return value;
      };
      var set = function (v) {
        value = v;
      };
      return {
        get: get,
        set: set
      };
    };

    var global$1 = tinymce.util.Tools.resolve('tinymce.PluginManager');

    var get$2 = function (toggleState) {
      var isEnabled = function () {
        return toggleState.get();
      };
      return { isEnabled: isEnabled };
    };

    var fireVisualChars = function (editor, state) {
      return editor.fire('VisualChars', { state: state });
    };

    var typeOf = function (x) {
      var t = typeof x;
      if (x === null) {
        return 'null';
      } else if (t === 'object' && (Array.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'Array')) {
        return 'array';
      } else if (t === 'object' && (String.prototype.isPrototypeOf(x) || x.constructor && x.constructor.name === 'String')) {
        return 'string';
      } else {
        return t;
      }
    };
    var isType$1 = function (type) {
      return function (value) {
        return typeOf(value) === type;
      };
    };
    var isSimpleType = function (type) {
      return function (value) {
        return typeof value === type;
      };
    };
    var isString = isType$1('string');
    var isBoolean = isSimpleType('boolean');
    var isNumber = isSimpleType('number');

    var noop = function () {
    };
    var constant = function (value) {
      return function () {
        return value;
      };
    };
    var identity = function (x) {
      return x;
    };
    var never = constant(false);
    var always = constant(true);

    var none = function () {
      return NONE;
    };
    var NONE = function () {
      var call = function (thunk) {
        return thunk();
      };
      var id = identity;
      var me = {
        fold: function (n, _s) {
          return n();
        },
        isSome: never,
        isNone: always,
        getOr: id,
        getOrThunk: call,
        getOrDie: function (msg) {
          throw new Error(msg || 'error: getOrDie called on none.');
        },
        getOrNull: constant(null),
        getOrUndefined: constant(undefined),
        or: id,
        orThunk: call,
        map: none,
        each: noop,
        bind: none,
        exists: never,
        forall: always,
        filter: function () {
          return none();
        },
        toArray: function () {
          return [];
        },
        toString: constant('none()')
      };
      return me;
    }();
    var some = function (a) {
      var constant_a = constant(a);
      var self = function () {
        return me;
      };
      var bind = function (f) {
        return f(a);
      };
      var me = {
        fold: function (n, s) {
          return s(a);
        },
        isSome: always,
        isNone: never,
        getOr: constant_a,
        getOrThunk: constant_a,
        getOrDie: constant_a,
        getOrNull: constant_a,
        getOrUndefined: constant_a,
        or: self,
        orThunk: self,
        map: function (f) {
          return some(f(a));
        },
        each: function (f) {
          f(a);
        },
        bind: bind,
        exists: bind,
        forall: bind,
        filter: function (f) {
          return f(a) ? me : NONE;
        },
        toArray: function () {
          return [a];
        },
        toString: function () {
          return 'some(' + a + ')';
        }
      };
      return me;
    };
    var from = function (value) {
      return value === null || value === undefined ? NONE : some(value);
    };
    var Optional = {
      some: some,
      none: none,
      from: from
    };

    var map = function (xs, f) {
      var len = xs.length;
      var r = new Array(len);
      for (var i = 0; i < len; i++) {
        var x = xs[i];
        r[i] = f(x, i);
      }
      return r;
    };
    var each$1 = function (xs, f) {
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        f(x, i);
      }
    };
    var filter = function (xs, pred) {
      var r = [];
      for (var i = 0, len = xs.length; i < len; i++) {
        var x = xs[i];
        if (pred(x, i)) {
          r.push(x);
        }
      }
      return r;
    };

    var keys = Object.keys;
    var each = function (obj, f) {
      var props = keys(obj);
      for (var k = 0, len = props.length; k < len; k++) {
        var i = props[k];
        var x = obj[i];
        f(x, i);
      }
    };

    typeof window !== 'undefined' ? window : Function('return this;')();

    var TEXT = 3;

    var type = function (element) {
      return element.dom.nodeType;
    };
    var value = function (element) {
      return element.dom.nodeValue;
    };
    var isType = function (t) {
      return function (element) {
        return type(element) === t;
      };
    };
    var isText = isType(TEXT);

    var rawSet = function (dom, key, value) {
      if (isString(value) || isBoolean(value) || isNumber(value)) {
        dom.setAttribute(key, value + '');
      } else {
        console.error('Invalid call to Attribute.set. Key ', key, ':: Value ', value, ':: Element ', dom);
        throw new Error('Attribute value was not simple');
      }
    };
    var set = function (element, key, value) {
      rawSet(element.dom, key, value);
    };
    var get$1 = function (element, key) {
      var v = element.dom.getAttribute(key);
      return v === null ? undefined : v;
    };
    var remove$3 = function (element, key) {
      element.dom.removeAttribute(key);
    };

    var read = function (element, attr) {
      var value = get$1(element, attr);
      return value === undefined || value === '' ? [] : value.split(' ');
    };
    var add$2 = function (element, attr, id) {
      var old = read(element, attr);
      var nu = old.concat([id]);
      set(element, attr, nu.join(' '));
      return true;
    };
    var remove$2 = function (element, attr, id) {
      var nu = filter(read(element, attr), function (v) {
        return v !== id;
      });
      if (nu.length > 0) {
        set(element, attr, nu.join(' '));
      } else {
        remove$3(element, attr);
      }
      return false;
    };

    var supports = function (element) {
      return element.dom.classList !== undefined;
    };
    var get = function (element) {
      return read(element, 'class');
    };
    var add$1 = function (element, clazz) {
      return add$2(element, 'class', clazz);
    };
    var remove$1 = function (element, clazz) {
      return remove$2(element, 'class', clazz);
    };

    var add = function (element, clazz) {
      if (supports(element)) {
        element.dom.classList.add(clazz);
      } else {
        add$1(element, clazz);
      }
    };
    var cleanClass = function (element) {
      var classList = supports(element) ? element.dom.classList : get(element);
      if (classList.length === 0) {
        remove$3(element, 'class');
      }
    };
    var remove = function (element, clazz) {
      if (supports(element)) {
        var classList = element.dom.classList;
        classList.remove(clazz);
      } else {
        remove$1(element, clazz);
      }
      cleanClass(element);
    };

    var fromHtml = function (html, scope) {
      var doc = scope || document;
      var div = doc.createElement('div');
      div.innerHTML = html;
      if (!div.hasChildNodes() || div.childNodes.length > 1) {
        console.error('HTML does not have a single root node', html);
        throw new Error('HTML must have a single root node');
      }
      return fromDom(div.childNodes[0]);
    };
    var fromTag = function (tag, scope) {
      var doc = scope || document;
      var node = doc.createElement(tag);
      return fromDom(node);
    };
    var fromText = function (text, scope) {
      var doc = scope || document;
      var node = doc.createTextNode(text);
      return fromDom(node);
    };
    var fromDom = function (node) {
      if (node === null || node === undefined) {
        throw new Error('Node cannot be null or undefined');
      }
      return { dom: node };
    };
    var fromPoint = function (docElm, x, y) {
      return Optional.from(docElm.dom.elementFromPoint(x, y)).map(fromDom);
    };
    var SugarElement = {
      fromHtml: fromHtml,
      fromTag: fromTag,
      fromText: fromText,
      fromDom: fromDom,
      fromPoint: fromPoint
    };

    var charMap = {
      '\xA0': 'nbsp',
      '\xAD': 'shy'
    };
    var charMapToRegExp = function (charMap, global) {
      var regExp = '';
      each(charMap, function (_value, key) {
        regExp += key;
      });
      return new RegExp('[' + regExp + ']', global ? 'g' : '');
    };
    var charMapToSelector = function (charMap) {
      var selector = '';
      each(charMap, function (value) {
        if (selector) {
          selector += ',';
        }
        selector += 'span.mce-' + value;
      });
      return selector;
    };
    var regExp = charMapToRegExp(charMap);
    var regExpGlobal = charMapToRegExp(charMap, true);
    var selector = charMapToSelector(charMap);
    var nbspClass = 'mce-nbsp';

    var wrapCharWithSpan = function (value) {
      return '<span data-mce-bogus="1" class="mce-' + charMap[value] + '">' + value + '</span>';
    };

    var isMatch = function (n) {
      var value$1 = value(n);
      return isText(n) && value$1 !== undefined && regExp.test(value$1);
    };
    var filterDescendants = function (scope, predicate) {
      var result = [];
      var dom = scope.dom;
      var children = map(dom.childNodes, SugarElement.fromDom);
      each$1(children, function (x) {
        if (predicate(x)) {
          result = result.concat([x]);
        }
        result = result.concat(filterDescendants(x, predicate));
      });
      return result;
    };
    var findParentElm = function (elm, rootElm) {
      while (elm.parentNode) {
        if (elm.parentNode === rootElm) {
          return elm;
        }
        elm = elm.parentNode;
      }
    };
    var replaceWithSpans = function (text) {
      return text.replace(regExpGlobal, wrapCharWithSpan);
    };

    var isWrappedNbsp = function (node) {
      return node.nodeName.toLowerCase() === 'span' && node.classList.contains('mce-nbsp-wrap');
    };
    var show = function (editor, rootElm) {
      var nodeList = filterDescendants(SugarElement.fromDom(rootElm), isMatch);
      each$1(nodeList, function (n) {
        var parent = n.dom.parentNode;
        if (isWrappedNbsp(parent)) {
          add(SugarElement.fromDom(parent), nbspClass);
        } else {
          var withSpans = replaceWithSpans(editor.dom.encode(value(n)));
          var div = editor.dom.create('div', null, withSpans);
          var node = void 0;
          while (node = div.lastChild) {
            editor.dom.insertAfter(node, n.dom);
          }
          editor.dom.remove(n.dom);
        }
      });
    };
    var hide = function (editor, rootElm) {
      var nodeList = editor.dom.select(selector, rootElm);
      each$1(nodeList, function (node) {
        if (isWrappedNbsp(node)) {
          remove(SugarElement.fromDom(node), nbspClass);
        } else {
          editor.dom.remove(node, true);
        }
      });
    };
    var toggle = function (editor) {
      var body = editor.getBody();
      var bookmark = editor.selection.getBookmark();
      var parentNode = findParentElm(editor.selection.getNode(), body);
      parentNode = parentNode !== undefined ? parentNode : body;
      hide(editor, parentNode);
      show(editor, parentNode);
      editor.selection.moveToBookmark(bookmark);
    };

    var applyVisualChars = function (editor, toggleState) {
      fireVisualChars(editor, toggleState.get());
      var body = editor.getBody();
      if (toggleState.get() === true) {
        show(editor, body);
      } else {
        hide(editor, body);
      }
    };
    var toggleVisualChars = function (editor, toggleState) {
      toggleState.set(!toggleState.get());
      var bookmark = editor.selection.getBookmark();
      applyVisualChars(editor, toggleState);
      editor.selection.moveToBookmark(bookmark);
    };

    var register$1 = function (editor, toggleState) {
      editor.addCommand('mceVisualChars', function () {
        toggleVisualChars(editor, toggleState);
      });
    };

    var isEnabledByDefault = function (editor) {
      return editor.getParam('visualchars_default_state', false);
    };
    var hasForcedRootBlock = function (editor) {
      return editor.getParam('forced_root_block') !== false;
    };

    var setup$1 = function (editor, toggleState) {
      editor.on('init', function () {
        applyVisualChars(editor, toggleState);
      });
    };

    var global = tinymce.util.Tools.resolve('tinymce.util.Delay');

    var setup = function (editor, toggleState) {
      var debouncedToggle = global.debounce(function () {
        toggle(editor);
      }, 300);
      if (hasForcedRootBlock(editor)) {
        editor.on('keydown', function (e) {
          if (toggleState.get() === true) {
            e.keyCode === 13 ? toggle(editor) : debouncedToggle();
          }
        });
      }
      editor.on('remove', debouncedToggle.stop);
    };

    var toggleActiveState = function (editor, enabledStated) {
      return function (api) {
        api.setActive(enabledStated.get());
        var editorEventCallback = function (e) {
          return api.setActive(e.state);
        };
        editor.on('VisualChars', editorEventCallback);
        return function () {
          return editor.off('VisualChars', editorEventCallback);
        };
      };
    };
    var register = function (editor, toggleState) {
      var onAction = function () {
        return editor.execCommand('mceVisualChars');
      };
      editor.ui.registry.addToggleButton('visualchars', {
        tooltip: 'Show invisible characters',
        icon: 'visualchars',
        onAction: onAction,
        onSetup: toggleActiveState(editor, toggleState)
      });
      editor.ui.registry.addToggleMenuItem('visualchars', {
        text: 'Show invisible characters',
        icon: 'visualchars',
        onAction: onAction,
        onSetup: toggleActiveState(editor, toggleState)
      });
    };

    function Plugin () {
      global$1.add('visualchars', function (editor) {
        var toggleState = Cell(isEnabledByDefault(editor));
        register$1(editor, toggleState);
        register(editor, toggleState);
        setup(editor, toggleState);
        setup$1(editor, toggleState);
        return get$2(toggleState);
      });
    }

    Plugin();

}());


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/caret_position/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Remove css classes
 *
 */
const removeClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.remove(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
  if (!el) return -1;
  amongst = amongst || el.nodeName;
  var i = 0;
  while (el = el.previousElementSibling) {
    if (el.matches(amongst)) {
      i++;
    }
  }
  return i;
};

/**
 * Plugin: "dropdown_input" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;

  /**
   * Moves the caret to the specified index.
   *
   * The input must be moved by leaving it in place and moving the
   * siblings, due to the fact that focus cannot be restored once lost
   * on mobile webkit devices
   *
   */
  self.hook('instead', 'setCaret', new_pos => {
    if (self.settings.mode === 'single' || !self.control.contains(self.control_input)) {
      new_pos = self.items.length;
    } else {
      new_pos = Math.max(0, Math.min(self.items.length, new_pos));
      if (new_pos != self.caretPos && !self.isPending) {
        self.controlChildren().forEach((child, j) => {
          if (j < new_pos) {
            self.control_input.insertAdjacentElement('beforebegin', child);
          } else {
            self.control.appendChild(child);
          }
        });
      }
    }
    self.caretPos = new_pos;
  });
  self.hook('instead', 'moveCaret', direction => {
    if (!self.isFocused) return;

    // move caret before or after selected items
    const last_active = self.getLastActive(direction);
    if (last_active) {
      const idx = nodeIndex(last_active);
      self.setCaret(direction > 0 ? idx + 1 : idx);
      self.setActiveItem();
      removeClasses(last_active, 'last-active');

      // move caret left or right of current position
    } else {
      self.setCaret(self.caretPos + direction);
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js":
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/change_listener/plugin.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Plugin: "change_listener" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  addEvent(this.input, 'change', () => {
    this.sync();
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js":
/*!*****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/checkbox_options/plugin.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */
const hash_key = value => {
  if (typeof value === 'undefined' || value === null) return null;
  return get_hash(value);
};
const get_hash = value => {
  if (typeof value === 'boolean') return value ? '1' : '0';
  return value + '';
};

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "checkbox_options" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  var self = this;
  var orig_onOptionSelect = self.onOptionSelect;
  self.settings.hideSelected = false;
  const cbOptions = Object.assign({
    // so that the user may add different ones as well
    className: "tomselect-checkbox",
    // the following default to the historic plugin's values
    checkedClassNames: undefined,
    uncheckedClassNames: undefined
  }, userOptions);
  var UpdateChecked = function UpdateChecked(checkbox, toCheck) {
    if (toCheck) {
      checkbox.checked = true;
      if (cbOptions.uncheckedClassNames) {
        checkbox.classList.remove(...cbOptions.uncheckedClassNames);
      }
      if (cbOptions.checkedClassNames) {
        checkbox.classList.add(...cbOptions.checkedClassNames);
      }
    } else {
      checkbox.checked = false;
      if (cbOptions.checkedClassNames) {
        checkbox.classList.remove(...cbOptions.checkedClassNames);
      }
      if (cbOptions.uncheckedClassNames) {
        checkbox.classList.add(...cbOptions.uncheckedClassNames);
      }
    }
  };

  // update the checkbox for an option
  var UpdateCheckbox = function UpdateCheckbox(option) {
    setTimeout(() => {
      var checkbox = option.querySelector('input.' + cbOptions.className);
      if (checkbox instanceof HTMLInputElement) {
        UpdateChecked(checkbox, option.classList.contains('selected'));
      }
    }, 1);
  };

  // add checkbox to option template
  self.hook('after', 'setupTemplates', () => {
    var orig_render_option = self.settings.render.option;
    self.settings.render.option = (data, escape_html) => {
      var rendered = getDom(orig_render_option.call(self, data, escape_html));
      var checkbox = document.createElement('input');
      if (cbOptions.className) {
        checkbox.classList.add(cbOptions.className);
      }
      checkbox.addEventListener('click', function (evt) {
        preventDefault(evt);
      });
      checkbox.type = 'checkbox';
      const hashed = hash_key(data[self.settings.valueField]);
      UpdateChecked(checkbox, !!(hashed && self.items.indexOf(hashed) > -1));
      rendered.prepend(checkbox);
      return rendered;
    };
  });

  // uncheck when item removed
  self.on('item_remove', value => {
    var option = self.getOption(value);
    if (option) {
      // if dropdown hasn't been opened yet, the option won't exist
      option.classList.remove('selected'); // selected class won't be removed yet
      UpdateCheckbox(option);
    }
  });

  // check when item added
  self.on('item_add', value => {
    var option = self.getOption(value);
    if (option) {
      // if dropdown hasn't been opened yet, the option won't exist
      UpdateCheckbox(option);
    }
  });

  // remove items when selected option is clicked
  self.hook('instead', 'onOptionSelect', (evt, option) => {
    if (option.classList.contains('selected')) {
      option.classList.remove('selected');
      self.removeItem(option.dataset.value);
      self.refreshOptions();
      preventDefault(evt, true);
      return;
    }
    orig_onOptionSelect.call(self, evt, option);
    UpdateCheckbox(option);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js":
/*!*************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/clear_button/plugin.js ***!
  \*************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "dropdown_header" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    className: 'clear-button',
    title: 'Clear All',
    html: data => {
      return `<div class="${data.className}" title="${data.title}">&#10799;</div>`;
    }
  }, userOptions);
  self.on('initialize', () => {
    var button = getDom(options.html(options));
    button.addEventListener('click', evt => {
      if (self.isLocked) return;
      self.clear();
      if (self.settings.mode === 'single' && self.settings.allowEmptyOption) {
        self.addItem('');
      }
      evt.preventDefault();
      evt.stopPropagation();
    });
    self.control.appendChild(button);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js":
/*!**********************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/drag_drop/plugin.js ***!
  \**********************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Set attributes of an element
 *
 */
const setAttr = (el, attrs) => {
  iterate(attrs, (val, attr) => {
    if (val == null) {
      el.removeAttribute(attr);
    } else {
      el.setAttribute(attr, '' + val);
    }
  });
};

/**
 * Plugin: "drag_drop" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

const insertAfter = (referenceNode, newNode) => {
  var _referenceNode$parent;
  (_referenceNode$parent = referenceNode.parentNode) == null || _referenceNode$parent.insertBefore(newNode, referenceNode.nextSibling);
};
const insertBefore = (referenceNode, newNode) => {
  var _referenceNode$parent2;
  (_referenceNode$parent2 = referenceNode.parentNode) == null || _referenceNode$parent2.insertBefore(newNode, referenceNode);
};
const isBefore = (referenceNode, newNode) => {
  do {
    var _newNode;
    newNode = (_newNode = newNode) == null ? void 0 : _newNode.previousElementSibling;
    if (referenceNode == newNode) {
      return true;
    }
  } while (newNode && newNode.previousElementSibling);
  return false;
};
function plugin () {
  var self = this;
  if (self.settings.mode !== 'multi') return;
  var orig_lock = self.lock;
  var orig_unlock = self.unlock;
  let sortable = true;
  let drag_item;

  /**
   * Add draggable attribute to item
   */
  self.hook('after', 'setupTemplates', () => {
    var orig_render_item = self.settings.render.item;
    self.settings.render.item = (data, escape) => {
      const item = getDom(orig_render_item.call(self, data, escape));
      setAttr(item, {
        'draggable': 'true'
      });

      // prevent doc_mousedown (see tom-select.ts)
      const mousedown = evt => {
        if (!sortable) preventDefault(evt);
        evt.stopPropagation();
      };
      const dragStart = evt => {
        drag_item = item;
        setTimeout(() => {
          item.classList.add('ts-dragging');
        }, 0);
      };
      const dragOver = evt => {
        evt.preventDefault();
        item.classList.add('ts-drag-over');
        moveitem(item, drag_item);
      };
      const dragLeave = () => {
        item.classList.remove('ts-drag-over');
      };
      const moveitem = (targetitem, dragitem) => {
        if (dragitem === undefined) return;
        if (isBefore(dragitem, item)) {
          insertAfter(targetitem, dragitem);
        } else {
          insertBefore(targetitem, dragitem);
        }
      };
      const dragend = () => {
        var _drag_item;
        document.querySelectorAll('.ts-drag-over').forEach(el => el.classList.remove('ts-drag-over'));
        (_drag_item = drag_item) == null || _drag_item.classList.remove('ts-dragging');
        drag_item = undefined;
        var values = [];
        self.control.querySelectorAll(`[data-value]`).forEach(el => {
          if (el.dataset.value) {
            let value = el.dataset.value;
            if (value) {
              values.push(value);
            }
          }
        });
        self.setValue(values);
      };
      addEvent(item, 'mousedown', mousedown);
      addEvent(item, 'dragstart', dragStart);
      addEvent(item, 'dragenter', dragOver);
      addEvent(item, 'dragover', dragOver);
      addEvent(item, 'dragleave', dragLeave);
      addEvent(item, 'dragend', dragend);
      return item;
    };
  });
  self.hook('instead', 'lock', () => {
    sortable = false;
    return orig_lock.call(self);
  });
  self.hook('instead', 'unlock', () => {
    sortable = true;
    return orig_unlock.call(self);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js":
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/dropdown_header/plugin.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "dropdown_header" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    title: 'Untitled',
    headerClass: 'dropdown-header',
    titleRowClass: 'dropdown-header-title',
    labelClass: 'dropdown-header-label',
    closeClass: 'dropdown-header-close',
    html: data => {
      return '<div class="' + data.headerClass + '">' + '<div class="' + data.titleRowClass + '">' + '<span class="' + data.labelClass + '">' + data.title + '</span>' + '<a class="' + data.closeClass + '">&times;</a>' + '</div>' + '</div>';
    }
  }, userOptions);
  self.on('initialize', () => {
    var header = getDom(options.html(options));
    var close_link = header.querySelector('.' + options.closeClass);
    if (close_link) {
      close_link.addEventListener('click', evt => {
        preventDefault(evt, true);
        self.close();
      });
    }
    self.dropdown.insertBefore(header, self.dropdown.firstChild);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/dropdown_input/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

const KEY_ESC = 27;
const KEY_TAB = 9;
 // ctrl key or apple key for ma

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.add(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Plugin: "dropdown_input" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  const self = this;
  self.settings.shouldOpen = true; // make sure the input is shown even if there are no options to display in the dropdown

  self.hook('before', 'setup', () => {
    self.focus_node = self.control;
    addClasses(self.control_input, 'dropdown-input');
    const div = getDom('<div class="dropdown-input-wrap">');
    div.append(self.control_input);
    self.dropdown.insertBefore(div, self.dropdown.firstChild);

    // set a placeholder in the select control
    const placeholder = getDom('<input class="items-placeholder" tabindex="-1" />');
    placeholder.placeholder = self.settings.placeholder || '';
    self.control.append(placeholder);
  });
  self.on('initialize', () => {
    // set tabIndex on control to -1, otherwise [shift+tab] will put focus right back on control_input
    self.control_input.addEventListener('keydown', evt => {
      //addEvent(self.control_input,'keydown' as const,(evt:KeyboardEvent) =>{
      switch (evt.keyCode) {
        case KEY_ESC:
          if (self.isOpen) {
            preventDefault(evt, true);
            self.close();
          }
          self.clearActiveItems();
          return;
        case KEY_TAB:
          self.focus_node.tabIndex = -1;
          break;
      }
      return self.onKeyDown.call(self, evt);
    });
    self.on('blur', () => {
      self.focus_node.tabIndex = self.isDisabled ? -1 : self.tabIndex;
    });

    // give the control_input focus when the dropdown is open
    self.on('dropdown_open', () => {
      self.control_input.focus();
    });

    // prevent onBlur from closing when focus is on the control_input
    const orig_onBlur = self.onBlur;
    self.hook('instead', 'onBlur', evt => {
      if (evt && evt.relatedTarget == self.control_input) return;
      return orig_onBlur.call(self);
    });
    addEvent(self.control_input, 'blur', () => self.onBlur());

    // return focus to control to allow further keyboard input
    self.hook('before', 'close', () => {
      if (!self.isOpen) return;
      self.focus_node.focus({
        preventScroll: true
      });
    });
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/input_autogrow/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Plugin: "input_autogrow" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  self.on('initialize', () => {
    var test_input = document.createElement('span');
    var control = self.control_input;
    test_input.style.cssText = 'position:absolute; top:-99999px; left:-99999px; width:auto; padding:0; white-space:pre; ';
    self.wrapper.appendChild(test_input);
    var transfer_styles = ['letterSpacing', 'fontSize', 'fontFamily', 'fontWeight', 'textTransform'];
    for (const style_name of transfer_styles) {
      // @ts-ignore TS7015 https://stackoverflow.com/a/50506154/697576
      test_input.style[style_name] = control.style[style_name];
    }

    /**
     * Set the control width
     *
     */
    var resize = () => {
      test_input.textContent = control.value;
      control.style.width = test_input.clientWidth + 'px';
    };
    resize();
    self.on('update item_add item_remove', resize);
    addEvent(control, 'input', resize);
    addEvent(control, 'keyup', resize);
    addEvent(control, 'blur', resize);
    addEvent(control, 'update', resize);
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js":
/*!****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/no_active_items/plugin.js ***!
  \****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "no_active_items" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  this.hook('instead', 'setActiveItem', () => {});
  this.hook('instead', 'selectAll', () => {});
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js":
/*!********************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/no_backspace_delete/plugin.js ***!
  \********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "input_autogrow" (Tom Select)
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  var orig_deleteSelection = self.deleteSelection;
  this.hook('instead', 'deleteSelection', evt => {
    if (self.activeItems.length) {
      return orig_deleteSelection.call(self, evt);
    }
    return false;
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js":
/*!*****************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/optgroup_columns/plugin.js ***!
  \*****************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

const KEY_LEFT = 37;
const KEY_RIGHT = 39;
 // ctrl key or apple key for ma

/**
 * Get the closest node to the evt.target matching the selector
 * Stops at wrapper
 *
 */
const parentMatch = (target, selector, wrapper) => {
  while (target && target.matches) {
    if (target.matches(selector)) {
      return target;
    }
    target = target.parentNode;
  }
};

/**
 * Get the index of an element amongst sibling nodes of the same type
 *
 */
const nodeIndex = (el, amongst) => {
  if (!el) return -1;
  amongst = amongst || el.nodeName;
  var i = 0;
  while (el = el.previousElementSibling) {
    if (el.matches(amongst)) {
      i++;
    }
  }
  return i;
};

/**
 * Plugin: "optgroup_columns" (Tom Select.js)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  var self = this;
  var orig_keydown = self.onKeyDown;
  self.hook('instead', 'onKeyDown', evt => {
    var index, option, options, optgroup;
    if (!self.isOpen || !(evt.keyCode === KEY_LEFT || evt.keyCode === KEY_RIGHT)) {
      return orig_keydown.call(self, evt);
    }
    self.ignoreHover = true;
    optgroup = parentMatch(self.activeOption, '[data-group]');
    index = nodeIndex(self.activeOption, '[data-selectable]');
    if (!optgroup) {
      return;
    }
    if (evt.keyCode === KEY_LEFT) {
      optgroup = optgroup.previousSibling;
    } else {
      optgroup = optgroup.nextSibling;
    }
    if (!optgroup) {
      return;
    }
    options = optgroup.querySelectorAll('[data-selectable]');
    option = options[Math.min(options.length - 1, index)];
    if (option) {
      self.setActiveOption(option);
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js":
/*!**************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/remove_button/plugin.js ***!
  \**************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Escapes a string for use within HTML.
 *
 */
const escape_html = str => {
  return (str + '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
};

/**
 * Prevent default
 *
 */
const preventDefault = (evt, stop = false) => {
  if (evt) {
    evt.preventDefault();
    if (stop) {
      evt.stopPropagation();
    }
  }
};

/**
 * Add event helper
 *
 */
const addEvent = (target, type, callback, options) => {
  target.addEventListener(type, callback, options);
};

/**
 * Return a dom element from either a dom query string, jQuery object, a dom element or html string
 * https://stackoverflow.com/questions/494143/creating-a-new-dom-element-from-an-html-string-using-built-in-dom-methods-or-pro/35385518#35385518
 *
 * param query should be {}
 */
const getDom = query => {
  if (query.jquery) {
    return query[0];
  }
  if (query instanceof HTMLElement) {
    return query;
  }
  if (isHtmlString(query)) {
    var tpl = document.createElement('template');
    tpl.innerHTML = query.trim(); // Never return a text node of whitespace as the result
    return tpl.content.firstChild;
  }
  return document.querySelector(query);
};
const isHtmlString = arg => {
  if (typeof arg === 'string' && arg.indexOf('<') > -1) {
    return true;
  }
  return false;
};

/**
 * Plugin: "remove_button" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const options = Object.assign({
    label: '&times;',
    title: 'Remove',
    className: 'remove',
    append: true
  }, userOptions);

  //options.className = 'remove-single';
  var self = this;

  // override the render method to add remove button to each item
  if (!options.append) {
    return;
  }
  var html = '<a href="javascript:void(0)" class="' + options.className + '" tabindex="-1" title="' + escape_html(options.title) + '">' + options.label + '</a>';
  self.hook('after', 'setupTemplates', () => {
    var orig_render_item = self.settings.render.item;
    self.settings.render.item = (data, escape) => {
      var item = getDom(orig_render_item.call(self, data, escape));
      var close_button = getDom(html);
      item.appendChild(close_button);
      addEvent(close_button, 'mousedown', evt => {
        preventDefault(evt, true);
      });
      addEvent(close_button, 'click', evt => {
        if (self.isLocked) return;

        // propagating will trigger the dropdown to show for single mode
        preventDefault(evt, true);
        if (self.isLocked) return;
        if (!self.shouldDelete([item], evt)) return;
        self.removeItem(item);
        self.refreshOptions(false);
        self.inputState();
      });
      return item;
    };
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js":
/*!*********************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/restore_on_backspace/plugin.js ***!
  \*********************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Plugin: "restore_on_backspace" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin (userOptions) {
  const self = this;
  const options = Object.assign({
    text: option => {
      return option[self.settings.labelField];
    }
  }, userOptions);
  self.on('item_remove', function (value) {
    if (!self.isFocused) {
      return;
    }
    if (self.control_input.value.trim() === '') {
      var option = self.options[value];
      if (option) {
        self.setTextboxValue(options.text.call(self, option));
      }
    }
  });
}


//# sourceMappingURL=plugin.js.map


/***/ }),

/***/ "./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js":
/*!***************************************************************************!*\
  !*** ./node_modules/tom-select/dist/esm/plugins/virtual_scroll/plugin.js ***!
  \***************************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

"use strict";
__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (/* binding */ plugin)
/* harmony export */ });
/**
* Tom Select v2.4.1
* Licensed under the Apache License, Version 2.0 (the "License");
*/

/**
 * Converts a scalar to its best string representation
 * for hash keys and HTML attribute values.
 *
 * Transformations:
 *   'str'     -> 'str'
 *   null      -> ''
 *   undefined -> ''
 *   true      -> '1'
 *   false     -> '0'
 *   0         -> '0'
 *   1         -> '1'
 *
 */

/**
 * Iterates over arrays and hashes.
 *
 * ```
 * iterate(this.items, function(item, id) {
 *    // invoked for each item
 * });
 * ```
 *
 */
const iterate = (object, callback) => {
  if (Array.isArray(object)) {
    object.forEach(callback);
  } else {
    for (var key in object) {
      if (object.hasOwnProperty(key)) {
        callback(object[key], key);
      }
    }
  }
};

/**
 * Add css classes
 *
 */
const addClasses = (elmts, ...classes) => {
  var norm_classes = classesArray(classes);
  elmts = castAsArray(elmts);
  elmts.map(el => {
    norm_classes.map(cls => {
      el.classList.add(cls);
    });
  });
};

/**
 * Return arguments
 *
 */
const classesArray = args => {
  var classes = [];
  iterate(args, _classes => {
    if (typeof _classes === 'string') {
      _classes = _classes.trim().split(/[\t\n\f\r\s]/);
    }
    if (Array.isArray(_classes)) {
      classes = classes.concat(_classes);
    }
  });
  return classes.filter(Boolean);
};

/**
 * Create an array from arg if it's not already an array
 *
 */
const castAsArray = arg => {
  if (!Array.isArray(arg)) {
    arg = [arg];
  }
  return arg;
};

/**
 * Plugin: "restore_on_backspace" (Tom Select)
 * Copyright (c) contributors
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not use this
 * file except in compliance with the License. You may obtain a copy of the License at:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software distributed under
 * the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF
 * ANY KIND, either express or implied. See the License for the specific language
 * governing permissions and limitations under the License.
 *
 */

function plugin () {
  const self = this;
  const orig_canLoad = self.canLoad;
  const orig_clearActiveOption = self.clearActiveOption;
  const orig_loadCallback = self.loadCallback;
  var pagination = {};
  var dropdown_content;
  var loading_more = false;
  var load_more_opt;
  var default_values = [];
  if (!self.settings.shouldLoadMore) {
    // return true if additional results should be loaded
    self.settings.shouldLoadMore = () => {
      const scroll_percent = dropdown_content.clientHeight / (dropdown_content.scrollHeight - dropdown_content.scrollTop);
      if (scroll_percent > 0.9) {
        return true;
      }
      if (self.activeOption) {
        var selectable = self.selectable();
        var index = Array.from(selectable).indexOf(self.activeOption);
        if (index >= selectable.length - 2) {
          return true;
        }
      }
      return false;
    };
  }
  if (!self.settings.firstUrl) {
    throw 'virtual_scroll plugin requires a firstUrl() method';
  }

  // in order for virtual scrolling to work,
  // options need to be ordered the same way they're returned from the remote data source
  self.settings.sortField = [{
    field: '$order'
  }, {
    field: '$score'
  }];

  // can we load more results for given query?
  const canLoadMore = query => {
    if (typeof self.settings.maxOptions === 'number' && dropdown_content.children.length >= self.settings.maxOptions) {
      return false;
    }
    if (query in pagination && pagination[query]) {
      return true;
    }
    return false;
  };
  const clearFilter = (option, value) => {
    if (self.items.indexOf(value) >= 0 || default_values.indexOf(value) >= 0) {
      return true;
    }
    return false;
  };

  // set the next url that will be
  self.setNextUrl = (value, next_url) => {
    pagination[value] = next_url;
  };

  // getUrl() to be used in settings.load()
  self.getUrl = query => {
    if (query in pagination) {
      const next_url = pagination[query];
      pagination[query] = false;
      return next_url;
    }

    // if the user goes back to a previous query
    // we need to load the first page again
    self.clearPagination();
    return self.settings.firstUrl.call(self, query);
  };

  // clear pagination
  self.clearPagination = () => {
    pagination = {};
  };

  // don't clear the active option (and cause unwanted dropdown scroll)
  // while loading more results
  self.hook('instead', 'clearActiveOption', () => {
    if (loading_more) {
      return;
    }
    return orig_clearActiveOption.call(self);
  });

  // override the canLoad method
  self.hook('instead', 'canLoad', query => {
    // first time the query has been seen
    if (!(query in pagination)) {
      return orig_canLoad.call(self, query);
    }
    return canLoadMore(query);
  });

  // wrap the load
  self.hook('instead', 'loadCallback', (options, optgroups) => {
    if (!loading_more) {
      self.clearOptions(clearFilter);
    } else if (load_more_opt) {
      const first_option = options[0];
      if (first_option !== undefined) {
        load_more_opt.dataset.value = first_option[self.settings.valueField];
      }
    }
    orig_loadCallback.call(self, options, optgroups);
    loading_more = false;
  });

  // add templates to dropdown
  //	loading_more if we have another url in the queue
  //	no_more_results if we don't have another url in the queue
  self.hook('after', 'refreshOptions', () => {
    const query = self.lastValue;
    var option;
    if (canLoadMore(query)) {
      option = self.render('loading_more', {
        query: query
      });
      if (option) {
        option.setAttribute('data-selectable', ''); // so that navigating dropdown with [down] keypresses can navigate to this node
        load_more_opt = option;
      }
    } else if (query in pagination && !dropdown_content.querySelector('.no-results')) {
      option = self.render('no_more_results', {
        query: query
      });
    }
    if (option) {
      addClasses(option, self.settings.optionClass);
      dropdown_content.append(option);
    }
  });

  // add scroll listener and default templates
  self.on('initialize', () => {
    default_values = Object.keys(self.options);
    dropdown_content = self.dropdown_content;

    // default templates
    self.settings.render = Object.assign({}, {
      loading_more: () => {
        return `<div class="loading-more-results">Loading more results ... </div>`;
      },
      no_more_results: () => {
        return `<div class="no-more-results">No more results</div>`;
      }
    }, self.settings.render);

    // watch dropdown content scroll position
    dropdown_content.addEventListener('scroll', () => {
      if (!self.settings.shouldLoadMore.call(self)) {
        return;
      }

      // !important: this will get checked again in load() but we still need to check here otherwise loading_more will be set to true
      if (!canLoadMore(self.lastValue)) {
        return;
      }

      // don't call load() too much
      if (loading_more) return;
      loading_more = true;
      self.load.call(self, self.lastValue);
    });
  });
}


//# sourceMappingURL=plugin.js.map


/***/ })

}]);