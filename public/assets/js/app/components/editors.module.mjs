import { appUrl, theme, colorScheme, version } from '../core/instance-info.module.mjs';
import jQuery from 'jquery';


/* Import TinyMCE */
import tinymce from 'tinymce';

import 'tinymce/jquery.tinymce.js';

/* Default icons are required for TinyMCE 5.3 or above */
import 'tinymce/icons/default/index.js';

/* A theme is also required */
import 'tinymce/themes/silver/index.js';


/* Import plugins */
import 'tinymce/plugins/autolink/index.js';
import 'tinymce/plugins/link/index.js';
import 'tinymce/plugins/textcolor/index.js';
import 'tinymce/plugins/image/index.js';
import 'tinymce/plugins/imagetools/index.js';
import 'tinymce/plugins/lists/index.js';
import 'tinymce/plugins/save/index.js';
import 'tinymce/plugins/autosave/index.js';
import 'tinymce/plugins/media/index.js';
import 'tinymce/plugins/searchreplace/index.js';
import 'tinymce/plugins/paste/index.js';
import 'tinymce/plugins/directionality/index.js';
import 'tinymce/plugins/fullscreen/index.js';
import 'tinymce/plugins/noneditable/index.js';
import 'tinymce/plugins/visualchars/index.js';
import 'tinymce/plugins/emoticons/index.js';
import 'tinymce/plugins/emoticons/js/emojis.js';
import 'tinymce/plugins/advlist/index.js';
import 'tinymce/plugins/autoresize/index.js';
import 'tinymce/plugins/codesample/index.js';
import 'tinymce/plugins/textpattern/index.js';

import "js/libs/tinymce-plugins/checklist/index.js"
import "js/libs/tinymce-plugins/shortlink/index.js"
import "js/libs/tinymce-plugins/table/plugin.js"
import "js/libs/tinymce-plugins/bettertable/index.js"
import "js/libs/tinymce-plugins/collapsibleheaders/index.js"
import "js/libs/tinymce-plugins/embed/index.js"
import "js/libs/tinymce-plugins/slashcommands/slashcommands.js"
import "js/libs/tinymce-plugins/mention/plugin.js"
import "js/libs/tinymce-plugins/advancedTemplate/plugin.js"


const markDownTextPatterns = [
    {start: '*', end: '*', format: 'italic'},
    {start: '_', end: '_', format: 'italic'},

    {start: '**', end: '**', format: 'bold'},
    {start: '__', end: '__', format: 'bold'},

    {start: '~~', end: '~~', format: 'bold'},

    {start: '#', format: 'h1'},
    {start: '##', format: 'h2'},
    {start: '###', format: 'h3'},
    {start: '####', format: 'h4'},
    {start: '#####', format: 'h5'},
    {start: '######', format: 'h6'},

    // The following text patterns require the `lists` plugin
    {start: '* ', cmd: 'InsertUnorderedList'},
    {start: '- ', cmd: 'InsertUnorderedList'},
    {start: '1. ', cmd: 'InsertOrderedList', value: { 'list-style-type': 'decimal' }},
    {start: '1) ', cmd: 'InsertOrderedList', value: { 'list-style-type': 'decimal' }},
    {start: 'a. ', cmd: 'InsertOrderedList', value: { 'list-style-type': 'lower-alpha' }},
    {start: 'a) ', cmd: 'InsertOrderedList', value: { 'list-style-type': 'lower-alpha' }},
    {start: 'i. ', cmd: 'InsertOrderedList', value: { 'list-style-type': 'lower-roman' }},
    {start: 'i) ', cmd: 'InsertOrderedList', value: { 'list-style-type': 'lower-roman' }},
    {start: '[ ] ', cmd: 'InsertChecklist' },

    {start: '>', format: 'blockquote' },

    {start: '`', end: '`', format: 'code' },

    {start: '```', end: '```', format: 'pre' },

    {start: '~', end: '~', cmd: 'createLink'},


    {start: '---', replacement: '<hr/>'},
    {start: '--', replacement: '—'},
    {start: '-', replacement: '—'},
    {start: '(c)', replacement: '©'}
];

const mentionsConfig = {
    delimiter: '@',
    delay: 20,
    source: function (query, process, delimiter) {
        // Do your ajax call
        // When using multiple delimiters you can alter the query depending on the delimiter used
        if (delimiter === '@') {
            jQuery.getJSON(appUrl + '/api/users', {
                projectUsersAccess: 'current',
                query: query
            }, function (data) {
                //call process to show the result
                let users = [];
                for (let i = 0; i < data.length; i++) {
                    users[i] = {
                        "name": data[i].firstname + " " + data[i].lastname,
                        "id":  data[i].id,
                        "email": data[i].username
                    };
                }
                process(users);
            });
        }

    },
    highlighter: function (text) {
        //make matched block italic
        return text.replace(new RegExp('(' + this.query + ')', 'ig'), function ($1, match) {
            return '<strong>' + match + '</strong>';
        });
    },
    insert: function (item) {
        return '<a class="userMention" data-tagged-user-id="' + item.id + '" href="javascript:void(0)"><img src="' + appUrl + '/api/users?profileImage=' + item.id + '" alt="' + item.name + ' Image"/>' + item.name.trim() + '</a>&nbsp;';
    },
    // The default value is 10 (cf. https://github.com/StevenDevooght/tinyMCE-mention?tab=readme-ov-file#items)
    items: 10
};

export const imageUploadHandler = function(blobInfo, success, failure) {
    var xhr, formData;

    xhr = new XMLHttpRequest();
    xhr.withCredentials = false;
    xhr.open('POST', appUrl + '/api/files');

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

export const filePickerCallback = function (callback, value, meta) {
    window.filePickerCallback = callback;

    var shortOptions = {
        afterShowCont: function () {
            jQuery(".fileModal").nyroModal({callbacks:shortOptions});

        }
    };

    jQuery.nmManual(
        appUrl + '/files/showAll?modalPopUp=true',
        {
            stack: true,
            callbacks: shortOptions,
            sizes: {
                minW: 500,
                minH: 500,
            }
        }
    );
    jQuery.nmTop().elts.cont.css("zIndex", "1000010");
    jQuery.nmTop().elts.bg.css("zIndex", "1000010");
    jQuery.nmTop().elts.load.css("zIndex", "1000010");
    jQuery.nmTop().elts.all.find('.nyroModalCloseButton').css("zIndex", "1000010");
}

export const editorSetup = function(editor, callback) {
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
        if (editor.getContent() === '' ) {
            editor.setContent("<p class='tinyPlaceholder'>" + leantime.i18n.__('placeholder.type_slash') + "</p>");
        }
    });

    //and remove it on focus
    editor.on('focus',function () {
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
}

var skin_url = appUrl
    + '/dist/css/libs/tinymceSkin/oxide';

var content_css = appUrl
    + '/theme/'
    + theme
    + '/css/'
    + colorScheme
    + '.css,'
    + appUrl
    + '/dist/css/editor.'
    + version
    + '.min.css';

export const initSimpleEditor = function (callback, specificId) {
    const selector = specificId ?
        `#${specificId}` :
        'textarea.tinymceSimple';

    jQuery(selector).tinymce({
        // General options
        width: "100%",
        skin_url: skin_url,
        content_css: content_css,
        content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
        plugins : "autosave,imagetools,shortlink,checklist,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advlist,mention,slashcommands,textpattern",
        toolbar : "bold italic strikethrough | link unlink image | checklist bullist numlist | emoticons",
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
        menubar:false,
        relative_urls : true,
        document_base_url : appUrl + "/",
        default_link_target: '_blank',
        table_appearance_options: false,
        mentions: mentionsConfig,
        textpattern_patterns: markDownTextPatterns,
        images_upload_handler: imageUploadHandler,
        file_picker_callback: filePickerCallback,
        setup: editorSetup
    });
};

export const initComplexEditor = function (specificId) {

    var entityId = jQuery("input[name=id]").val();
    var height = window.innerHeight - 50 - 205;

    const selector = specificId ?
        `#${specificId}` :
        'textarea.complexEditor';

    jQuery(selector).tinymce({
        // General options
        width: "100%",
        skin_url: appUrl
            + '/dist/css/libs/tinymceSkin/oxide',
        content_css: appUrl
            + '/theme/'
            + theme
            + '/css/'
            + colorScheme
            + '.css,'
            + appUrl
            + '/dist/css/editor.'
            + version
            + '.min.css',
        content_style: "html {text-align:center;} body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
        plugins : "autosave,imagetools,embed,autoresize,shortlink,checklist,bettertable,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advancedTemplate,advlist,codesample,mention,slashcommands,textpattern",
        toolbar : "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media embed emoticons | checklist bullist numlist | table  | codesample | advancedTemplate | restoredraft",
        autosave_prefix: 'leantime-complexEditor-autosave-{path}{query}-{id}-'+entityId,
        autosave_restore_when_empty: true,
        autosave_retention: '120m',
        autosave_interval: '10s',
        autosave_ask_before_unload: false,
        branding: false,
        statusbar: false,
        convert_urls: true,
        menubar:false,
        resizable: true,
        templates : appUrl + "/wiki/templates",
        body_class: 'mce-content-body',
        paste_data_images: true,
        relative_urls : true,
        document_base_url: appUrl + "/",
        table_appearance_options: false,
        min_height: 400,
        max_height: height,
        default_link_target: '_blank',
        codesample_global_prismjs: true,
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Ruby', value: 'ruby' },
            { text: 'Rust', value: 'rust' },
            { text: 'SQL', value: 'sql' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'Swift', value: 'swift' },
            { text: 'Objective C', value: 'objectivec' },
            { text: 'Go', value: 'go' },
            { text: 'C', value: 'c' },
            { text: 'C#', value: 'csharp' },
            { text: 'C++', value: 'cpp' }
        ],
        mentions: mentionsConfig,
        textpattern_patterns: markDownTextPatterns,
        images_upload_handler: imageUploadHandler,
        file_picker_callback: filePickerCallback,
        setup: editorSetup
    });
};

export const initNotesEditor = function (callback, specificId) {
    var entityId = jQuery("input[name=id]").val();
    var height = window.innerHeight - 50 - 205;

    const selector = specificId ?
        `#${specificId}` :
        'textarea.notesEditor';

    jQuery(selector).tinymce({
        // General options
        width: "100%",
        skin_url: appUrl
            + '/dist/css/libs/tinymceSkin/oxide',
        content_css: appUrl
            + '/theme/'
            + theme
            + '/css/'
            + colorScheme
            + '.css,'
            + appUrl
            + '/dist/css/editor.'
            + version
            + '.min.css',
        content_style: "html {text-align:center;} body.mce-content-body{ font-size:14px; color:var(--secondary-font-color); max-width:none;} img { max-width: 100%; }",
        plugins : "autosave,imagetools,embed,autoresize,shortlink,checklist,bettertable,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advancedTemplate,advlist,codesample,mention,slashcommands,textpattern",
        toolbar : "link image table emoticons | checklist bullist | advancedTemplate | restoredraft",
        toolbar_location: 'bottom',
        autosave_prefix: 'leantime-complexEditor-autosave-{path}{query}-{id}-'+entityId,
        autosave_restore_when_empty: true,
        autosave_retention: '120m',
        autosave_interval: '10s',
        autosave_ask_before_unload: false,
        branding: false,
        statusbar: false,
        convert_urls: true,
        menubar:false,
        resizable: true,
        templates : appUrl + "/wiki/templates",
        body_class: 'mce-content-body',
        paste_data_images: true,
        relative_urls : true,
        document_base_url: appUrl + "/",
        table_appearance_options: false,
        min_height: 400,
        default_link_target: '_blank',
        codesample_global_prismjs: true,
        codesample_languages: [
            { text: 'HTML/XML', value: 'markup' },
            { text: 'JavaScript', value: 'javascript' },
            { text: 'CSS', value: 'css' },
            { text: 'PHP', value: 'php' },
            { text: 'Ruby', value: 'ruby' },
            { text: 'Rust', value: 'rust' },
            { text: 'SQL', value: 'sql' },
            { text: 'Python', value: 'python' },
            { text: 'Java', value: 'java' },
            { text: 'Swift', value: 'swift' },
            { text: 'Objective C', value: 'objectivec' },
            { text: 'Go', value: 'go' },
            { text: 'C', value: 'c' },
            { text: 'C#', value: 'csharp' },
            { text: 'C++', value: 'cpp' }
        ],
        textpattern_patterns: markDownTextPatterns,
        mentions: mentionsConfig,
        textpattern_patterns: markDownTextPatterns,
        images_upload_handler: imageUploadHandler,
        file_picker_callback: filePickerCallback,
        setup: function(editor) {
            editorSetup(editor);

            editor.on("blur", function () {
                editor.save();
                callback();
            });
        },
    });
};

// Make public what you want to have public, everything else is private
export const editors = {
    initSimpleEditor: initSimpleEditor,
    initComplexEditor: initComplexEditor,
    initNotesEditor: initNotesEditor
};

export default editors;