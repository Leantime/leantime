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
return (self["webpackChunkleantime"] = self["webpackChunkleantime"] || []).push([["/js/Domain/Comments/Js/commentsController"],{

/***/ "./app/Domain/Comments/Js/commentsController.js":
/*!******************************************************!*\
  !*** ./app/Domain/Comments/Js/commentsController.js ***!
  \******************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   enableCommenterForms: () => (/* binding */ enableCommenterForms),
/* harmony export */   toggleCommentBoxes: () => (/* binding */ toggleCommentBoxes)
/* harmony export */ });
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_0___default = /*#__PURE__*/__webpack_require__.n(jquery__WEBPACK_IMPORTED_MODULE_0__);
/* harmony import */ var js_app_components_editors_module__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! js/app/components/editors.module */ "./public/assets/js/app/components/editors.module.mjs");


var enableCommenterForms = function enableCommenterForms() {
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commentBox").show();

  //Hide reply comment boxes
  jquery__WEBPACK_IMPORTED_MODULE_0___default()("#comments .replies .commentBox").hide();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".deleteComment, .replyButton").show();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commentReply .tinymceSimple").tinymce().getBody().setAttribute('contenteditable', "true");
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commentReply .tox-editor-header").show();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commenterFields input").prop("readonly", false);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commenterFields input").prop("disabled", false);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commenterFields textarea").prop("readonly", false);
  jquery__WEBPACK_IMPORTED_MODULE_0___default()(".commenterFields textarea").prop("disabled", false);
};
var toggleCommentBoxes = function toggleCommentBoxes(id) {
  if (id == 0) {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#mainToggler').hide();
  } else {
    jquery__WEBPACK_IMPORTED_MODULE_0___default()('#mainToggler').show();
  }
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.commentBox textarea').remove();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('.commentBox').hide('fast', function () {});
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#comment' + id + ' .commentReply').prepend('<textarea rows="5" cols="75" name="text" class="tinymceSimple"></textarea>');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#comment' + id).removeClass('hidden');
  (0,js_app_components_editors_module__WEBPACK_IMPORTED_MODULE_1__.initSimpleEditor)();
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#comment' + id + '').show('fast');
  jquery__WEBPACK_IMPORTED_MODULE_0___default()('#father').val(id);
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  enableCommenterForms: enableCommenterForms,
  toggleCommentBoxes: toggleCommentBoxes
});

/***/ }),

/***/ "i18n":
/*!***************************************!*\
  !*** external "window.leantime.i18n" ***!
  \***************************************/
/***/ ((module) => {

module.exports = __WEBPACK_EXTERNAL_MODULE_i18n__;

/***/ }),

/***/ "./public/assets/js/app/components/editors.module.mjs":
/*!************************************************************!*\
  !*** ./public/assets/js/app/components/editors.module.mjs ***!
  \************************************************************/
/***/ ((__unused_webpack___webpack_module__, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__),
/* harmony export */   editorSetup: () => (/* binding */ editorSetup),
/* harmony export */   filePickerCallback: () => (/* binding */ filePickerCallback),
/* harmony export */   imageUploadHandler: () => (/* binding */ imageUploadHandler),
/* harmony export */   initComplexEditor: () => (/* binding */ initComplexEditor),
/* harmony export */   initNotesEditor: () => (/* binding */ initNotesEditor),
/* harmony export */   initSimpleEditor: () => (/* binding */ initSimpleEditor)
/* harmony export */ });
/* harmony import */ var _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../core/instance-info.module.mjs */ "./public/assets/js/app/core/instance-info.module.mjs");
/* harmony import */ var i18n__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! i18n */ "i18n");
/* harmony import */ var jquery__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! jquery */ "./node_modules/jquery/dist/jquery.js");
function _typeof(o) { "@babel/helpers - typeof"; return _typeof = "function" == typeof Symbol && "symbol" == typeof Symbol.iterator ? function (o) { return typeof o; } : function (o) { return o && "function" == typeof Symbol && o.constructor === Symbol && o !== Symbol.prototype ? "symbol" : typeof o; }, _typeof(o); }
function _defineProperty(e, r, t) { return (r = _toPropertyKey(r)) in e ? Object.defineProperty(e, r, { value: t, enumerable: !0, configurable: !0, writable: !0 }) : e[r] = t, e; }
function _toPropertyKey(t) { var i = _toPrimitive(t, "string"); return "symbol" == _typeof(i) ? i : i + ""; }
function _toPrimitive(t, r) { if ("object" != _typeof(t) || !t) return t; var e = t[Symbol.toPrimitive]; if (void 0 !== e) { var i = e.call(t, r || "default"); if ("object" != _typeof(i)) return i; throw new TypeError("@@toPrimitive must return a primitive value."); } return ("string" === r ? String : Number)(t); }



var markDownTextPatterns = [{
  start: '*',
  end: '*',
  format: 'italic'
}, {
  start: '_',
  end: '_',
  format: 'italic'
}, {
  start: '**',
  end: '**',
  format: 'bold'
}, {
  start: '__',
  end: '__',
  format: 'bold'
}, {
  start: '~~',
  end: '~~',
  format: 'bold'
}, {
  start: '#',
  format: 'h1'
}, {
  start: '##',
  format: 'h2'
}, {
  start: '###',
  format: 'h3'
}, {
  start: '####',
  format: 'h4'
}, {
  start: '#####',
  format: 'h5'
}, {
  start: '######',
  format: 'h6'
},
// The following text patterns require the `lists` plugin
{
  start: '* ',
  cmd: 'InsertUnorderedList'
}, {
  start: '- ',
  cmd: 'InsertUnorderedList'
}, {
  start: '1. ',
  cmd: 'InsertOrderedList',
  value: {
    'list-style-type': 'decimal'
  }
}, {
  start: '1) ',
  cmd: 'InsertOrderedList',
  value: {
    'list-style-type': 'decimal'
  }
}, {
  start: 'a. ',
  cmd: 'InsertOrderedList',
  value: {
    'list-style-type': 'lower-alpha'
  }
}, {
  start: 'a) ',
  cmd: 'InsertOrderedList',
  value: {
    'list-style-type': 'lower-alpha'
  }
}, {
  start: 'i. ',
  cmd: 'InsertOrderedList',
  value: {
    'list-style-type': 'lower-roman'
  }
}, {
  start: 'i) ',
  cmd: 'InsertOrderedList',
  value: {
    'list-style-type': 'lower-roman'
  }
}, {
  start: '[ ] ',
  cmd: 'InsertChecklist'
}, {
  start: '>',
  format: 'blockquote'
}, {
  start: '`',
  end: '`',
  format: 'code'
}, {
  start: '```',
  end: '```',
  format: 'pre'
}, {
  start: '~',
  end: '~',
  cmd: 'createLink'
}, {
  start: '---',
  replacement: '<hr/>'
}, {
  start: '--',
  replacement: '—'
}, {
  start: '-',
  replacement: '—'
}, {
  start: '(c)',
  replacement: '©'
}];
var mentionsConfig = {
  delimiter: '@',
  delay: 20,
  source: function source(query, process, delimiter) {
    // Do your ajax call
    // When using multiple delimiters you can alter the query depending on the delimiter used
    if (delimiter === '@') {
      jquery__WEBPACK_IMPORTED_MODULE_2__.getJSON(_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/api/users', {
        projectUsersAccess: 'current',
        query: query
      }, function (data) {
        //call process to show the result
        var users = [];
        for (var i = 0; i < data.length; i++) {
          users[i] = {
            "name": data[i].firstname + " " + data[i].lastname,
            "id": data[i].id,
            "email": data[i].username
          };
        }
        process(users);
      });
    }
  },
  highlighter: function highlighter(text) {
    //make matched block italic
    return text.replace(new RegExp('(' + this.query + ')', 'ig'), function ($1, match) {
      return '<strong>' + match + '</strong>';
    });
  },
  insert: function insert(item) {
    return '<a class="userMention" data-tagged-user-id="' + item.id + '" href="javascript:void(0)"><img src="' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/api/users?profileImage=' + item.id + '" alt="' + item.name + ' Image"/>' + item.name.trim() + '</a>&nbsp;';
  },
  // The default value is 10 (cf. https://github.com/StevenDevooght/tinyMCE-mention?tab=readme-ov-file#items)
  items: 10
};
var imageUploadHandler = function imageUploadHandler(blobInfo, success, failure) {
  var xhr, formData;
  xhr = new XMLHttpRequest();
  xhr.withCredentials = false;
  xhr.open('POST', _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/api/files');
  xhr.onload = function () {
    var json;
    if (xhr.status < 200 || xhr.status >= 300) {
      failure('HTTP Error: ' + xhr.status);
      return;
    }
    success(xhr.responseText);
  };
  formData = new FormData();
  formData.append('file', blobInfo.blob());
  xhr.send(formData);
};
var filePickerCallback = function filePickerCallback(callback, value, meta) {
  window.filePickerCallback = callback;
  var shortOptions = {
    afterShowCont: function afterShowCont() {
      jquery__WEBPACK_IMPORTED_MODULE_2__(".fileModal").nyroModal({
        callbacks: shortOptions
      });
    }
  };
  jquery__WEBPACK_IMPORTED_MODULE_2__.nmManual(_core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/files/showAll?modalPopUp=true', {
    stack: true,
    callbacks: shortOptions,
    sizes: {
      minW: 500,
      minH: 500
    }
  });
  jquery__WEBPACK_IMPORTED_MODULE_2__.nmTop().elts.cont.css("zIndex", "1000010");
  jquery__WEBPACK_IMPORTED_MODULE_2__.nmTop().elts.bg.css("zIndex", "1000010");
  jquery__WEBPACK_IMPORTED_MODULE_2__.nmTop().elts.load.css("zIndex", "1000010");
  jquery__WEBPACK_IMPORTED_MODULE_2__.nmTop().elts.all.find('.nyroModalCloseButton').css("zIndex", "1000010");
};
var editorSetup = function editorSetup(editor, callback) {
  editor.on('change', function () {
    editor.save();
  });
  editor.on("blur", function () {
    editor.save();
    if (callback === 'function') {
      callback();
    }
  });
  editor.on('init', function (e) {
    var confettiElement = editor.getDoc().getElementsByClassName("confetti");
    if (confettiElement && confettiElement.length > 0) {
      confettiElement[0].addEventListener("click", function () {
        confetti.start();
      });
    }

    //&& !editor.plugins.autosave.hasDraft()
    if (editor.getContent() === '') {
      editor.setContent("<p class='tinyPlaceholder'>" + i18n__WEBPACK_IMPORTED_MODULE_1__.__('placeholder.type_slash') + "</p>");
    }
  });

  //and remove it on focus
  editor.on('focus', function () {
    var placeholder = editor.getDoc().getElementsByClassName("tinyPlaceholder");
    if (placeholder.length > 0) {
      while (placeholder[0]) {
        placeholder[0].parentNode.removeChild(placeholder[0]);
      }
    }
  });
  editor.on("submit", function () {
    var placeholder = editor.getDoc().getElementsByClassName("tinyPlaceholder");
    if (placeholder.length > 0) {
      while (placeholder[0]) {
        placeholder[0].remove();
      }
      editor.save();
    }
  });
};
var skin_url = _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/dist/css/libs/tinymceSkin/oxide';
var content_css = _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/theme/' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.theme + '/css/' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.colorScheme + '.css,' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/dist/css/editor.' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.version + '.min.css';
var initSimpleEditor = function initSimpleEditor(callback, specificId) {
  var selector = specificId ? "#".concat(specificId) : 'textarea.tinymceSimple';
  jquery__WEBPACK_IMPORTED_MODULE_2__(selector).tinymce({
    // General options
    width: "100%",
    skin_url: skin_url,
    content_css: content_css,
    content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
    plugins: "autosave,imagetools,shortlink,checklist,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advlist,mention,slashcommands,textpattern",
    toolbar: "bold italic strikethrough | link unlink image | checklist bullist numlist | emoticons",
    toolbar_location: 'bottom',
    autosave_prefix: 'leantime-simpleEditor-autosave-{path}{query}-{id}-',
    autosave_restore_when_empty: true,
    autosave_retention: '120m',
    autosave_interval: '10s',
    autosave_ask_before_unload: false,
    branding: false,
    statusbar: false,
    convert_urls: true,
    paste_data_images: true,
    menubar: false,
    relative_urls: true,
    document_base_url: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + "/",
    default_link_target: '_blank',
    table_appearance_options: false,
    mentions: mentionsConfig,
    textpattern_patterns: markDownTextPatterns,
    images_upload_handler: imageUploadHandler,
    file_picker_callback: filePickerCallback,
    setup: editorSetup
  });
};
var initComplexEditor = function initComplexEditor(specificId) {
  var entityId = jquery__WEBPACK_IMPORTED_MODULE_2__("input[name=id]").val();
  var height = window.innerHeight - 50 - 205;
  var selector = specificId ? "#".concat(specificId) : 'textarea.complexEditor';
  jquery__WEBPACK_IMPORTED_MODULE_2__(selector).tinymce({
    // General options
    width: "100%",
    skin_url: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/dist/css/libs/tinymceSkin/oxide',
    content_css: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/theme/' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.theme + '/css/' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.colorScheme + '.css,' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/dist/css/editor.' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.version + '.min.css',
    content_style: "html {text-align:center;} body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
    plugins: "autosave,imagetools,embed,autoresize,shortlink,checklist,bettertable,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advancedTemplate,advlist,codesample,mention,slashcommands,textpattern",
    toolbar: "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media embed emoticons | checklist bullist numlist | table  | codesample | advancedTemplate | restoredraft",
    autosave_prefix: 'leantime-complexEditor-autosave-{path}{query}-{id}-' + entityId,
    autosave_restore_when_empty: true,
    autosave_retention: '120m',
    autosave_interval: '10s',
    autosave_ask_before_unload: false,
    branding: false,
    statusbar: false,
    convert_urls: true,
    menubar: false,
    resizable: true,
    templates: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + "/wiki/templates",
    body_class: 'mce-content-body',
    paste_data_images: true,
    relative_urls: true,
    document_base_url: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + "/",
    table_appearance_options: false,
    min_height: 400,
    max_height: height,
    default_link_target: '_blank',
    codesample_global_prismjs: true,
    codesample_languages: [{
      text: 'HTML/XML',
      value: 'markup'
    }, {
      text: 'JavaScript',
      value: 'javascript'
    }, {
      text: 'CSS',
      value: 'css'
    }, {
      text: 'PHP',
      value: 'php'
    }, {
      text: 'Ruby',
      value: 'ruby'
    }, {
      text: 'Rust',
      value: 'rust'
    }, {
      text: 'SQL',
      value: 'sql'
    }, {
      text: 'Python',
      value: 'python'
    }, {
      text: 'Java',
      value: 'java'
    }, {
      text: 'Swift',
      value: 'swift'
    }, {
      text: 'Objective C',
      value: 'objectivec'
    }, {
      text: 'Go',
      value: 'go'
    }, {
      text: 'C',
      value: 'c'
    }, {
      text: 'C#',
      value: 'csharp'
    }, {
      text: 'C++',
      value: 'cpp'
    }],
    mentions: mentionsConfig,
    textpattern_patterns: markDownTextPatterns,
    images_upload_handler: imageUploadHandler,
    file_picker_callback: filePickerCallback,
    setup: editorSetup
  });
};
var initNotesEditor = function initNotesEditor(callback, specificId) {
  var entityId = jquery__WEBPACK_IMPORTED_MODULE_2__("input[name=id]").val();
  var height = window.innerHeight - 50 - 205;
  var selector = specificId ? "#".concat(specificId) : 'textarea.notesEditor';
  jquery__WEBPACK_IMPORTED_MODULE_2__(selector).tinymce(_defineProperty(_defineProperty(_defineProperty(_defineProperty({
    // General options
    width: "100%",
    skin_url: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/dist/css/libs/tinymceSkin/oxide',
    content_css: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/theme/' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.theme + '/css/' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.colorScheme + '.css,' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + '/dist/css/editor.' + _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.version + '.min.css',
    content_style: "html {text-align:center;} body.mce-content-body{ font-size:14px; color:var(--secondary-font-color); max-width:none;} img { max-width: 100%; }",
    plugins: "autosave,imagetools,embed,autoresize,shortlink,checklist,bettertable,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advancedTemplate,advlist,codesample,mention,slashcommands,textpattern",
    toolbar: "link image table emoticons | checklist bullist | advancedTemplate | restoredraft",
    toolbar_location: 'bottom',
    autosave_prefix: 'leantime-complexEditor-autosave-{path}{query}-{id}-' + entityId,
    autosave_restore_when_empty: true,
    autosave_retention: '120m',
    autosave_interval: '10s',
    autosave_ask_before_unload: false,
    branding: false,
    statusbar: false,
    convert_urls: true,
    menubar: false,
    resizable: true,
    templates: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + "/wiki/templates",
    body_class: 'mce-content-body',
    paste_data_images: true,
    relative_urls: true,
    document_base_url: _core_instance_info_module_mjs__WEBPACK_IMPORTED_MODULE_0__.appUrl + "/",
    table_appearance_options: false,
    min_height: 400,
    default_link_target: '_blank',
    codesample_global_prismjs: true,
    codesample_languages: [{
      text: 'HTML/XML',
      value: 'markup'
    }, {
      text: 'JavaScript',
      value: 'javascript'
    }, {
      text: 'CSS',
      value: 'css'
    }, {
      text: 'PHP',
      value: 'php'
    }, {
      text: 'Ruby',
      value: 'ruby'
    }, {
      text: 'Rust',
      value: 'rust'
    }, {
      text: 'SQL',
      value: 'sql'
    }, {
      text: 'Python',
      value: 'python'
    }, {
      text: 'Java',
      value: 'java'
    }, {
      text: 'Swift',
      value: 'swift'
    }, {
      text: 'Objective C',
      value: 'objectivec'
    }, {
      text: 'Go',
      value: 'go'
    }, {
      text: 'C',
      value: 'c'
    }, {
      text: 'C#',
      value: 'csharp'
    }, {
      text: 'C++',
      value: 'cpp'
    }],
    textpattern_patterns: markDownTextPatterns,
    mentions: mentionsConfig
  }, "textpattern_patterns", markDownTextPatterns), "images_upload_handler", imageUploadHandler), "file_picker_callback", filePickerCallback), "setup", function setup(editor) {
    editorSetup(editor);
    editor.on("blur", function () {
      editor.save();
      callback();
    });
  }));
};

// Make public what you want to have public, everything else is private
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({
  initSimpleEditor: initSimpleEditor,
  initComplexEditor: initComplexEditor,
  initNotesEditor: initNotesEditor
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
/******/ __webpack_require__.O(0, ["js/vendor"], () => (__webpack_exec__("./app/Domain/Comments/Js/commentsController.js")));
/******/ var __webpack_exports__ = __webpack_require__.O();
/******/ return __webpack_exports__;
/******/ }
]);
});