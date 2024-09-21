leantime.editorController = (function () {

    var markDownTextPatterns = [
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

    var mentionsConfig = {
        delimiter: '@',
        delay: 20,
        source: function (query, process, delimiter) {
            // Do your ajax call
            // When using multiple delimiters you can alter the query depending on the delimiter used
            if (delimiter === '@') {
                jQuery.getJSON(leantime.appUrl + '/api/users', {
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
            return '<a class="userMention" data-tagged-user-id="' + item.id + '" href="javascript:void(0)"><img src="' + leantime.appUrl + '/api/users?profileImage=' + item.id + '" alt="' + item.name + ' Image"/>' + item.name.trim() + '</a>&nbsp;';
        },
        // The default value is 10 (cf. https://github.com/StevenDevooght/tinyMCE-mention?tab=readme-ov-file#items)
        items: 10
    };

    var imageUploadHandler = function(blobInfo, success, failure) {
        var xhr, formData;

        xhr = new XMLHttpRequest();
        xhr.withCredentials = false;
        xhr.open('POST', leantime.appUrl + '/api/files');

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

    var filePickerCallback = function (callback, value, meta) {
        window.filePickerCallback = callback;

        var shortOptions = {
            afterShowCont: function () {
                jQuery(".fileModal").nyroModal({callbacks:shortOptions});

            }
        };

        jQuery.nmManual(
            leantime.appUrl + '/files/showAll?modalPopUp=true',
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

    var editorSetup = function(editor, callback) {

        editor.on('change', function () {

        });

        editor.on('init', function (e) {

            var confettiElement = editor.getDoc().getElementsByClassName("confetti");

            if (confettiElement && confettiElement.length > 0) {
                confettiElement[0].addEventListener("click", function () {
                    confetti.start();
                });
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

    var skin_url = leantime.appUrl + '/dist/css/libs/tinymceSkin/oxide';
    var content_css = leantime.appUrl + '/theme/' + leantime.theme + '/css/'+ leantime.colorScheme +'.css,'
        + leantime.appUrl + '/dist/css/editor.' + leantime.version + '.min.css'

    var initSimpleEditor = function (callback) {

        jQuery('textarea.tinymceSimple').tinymce(
            {
                // General options
                width: "100%",
                skin_url: skin_url,
                content_css: content_css,
                content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                plugins : "autosave,imagetools,shortlink,checklist,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advlist,mention,slashcommands,textpattern",
                toolbar : "bold italic strikethrough | link unlink image | checklist bullist numlist | emoticons",
                toolbar_location: 'bottom',
                branding: false,
                statusbar: false,
                convert_urls: true,
                paste_data_images: true,
                menubar:false,
                relative_urls : true,
                document_base_url : leantime.appUrl + "/",
                default_link_target: '_blank',
                table_appearance_options: false,
                mentions: mentionsConfig,
                textpattern_patterns: markDownTextPatterns,
                images_upload_handler: imageUploadHandler,
                file_picker_callback: filePickerCallback,
                setup: editorSetup
            }
        );
    };

    var initComplexEditor = function () {

        var entityId = jQuery("input[name=id]").val();

        if(entityId == undefined) {
            entityId = "new";
        }

        let projectId = 'none';
        if(window.leantime.currentProject){
            projectId = window.leantime.currentProject;
        }

        //modal is 50px from top. Always
        //Then reduce headline, save button range padding from modal
        var height = window.innerHeight - 50 - 205;


        jQuery('textarea.complexEditor').tinymce(
            {
                // General options
                width: "100%",
                skin_url: leantime.appUrl + '/dist/css/libs/tinymceSkin/oxide',
                content_css: leantime.appUrl + '/theme/' + leantime.theme + '/css/'+ leantime.colorScheme +'.css,'
                    + leantime.appUrl + '/dist/css/editor.' + leantime.version + '.min.css',
                content_style: "html {text-align:center;} body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                plugins : "autosave,imagetools,embed,autoresize,shortlink,checklist,bettertable,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advancedTemplate,advlist,codesample,mention,slashcommands,textpattern",
                toolbar : "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media embed emoticons | checklist bullist numlist | table  | codesample | advancedTemplate | restoredraft",
                autosave_prefix: 'leantime-complexEditor-autosave-{path}{query}-{id}-'+projectId+'-'+entityId+'-',
                autosave_restore_when_empty: true,
                autosave_retention: '120m',
                autosave_interval: '10s',
                autosave_ask_before_unload: false,
                branding: false,
                statusbar: false,
                convert_urls: true,
                menubar:false,
                resizable: true,
                templates : leantime.appUrl + "/wiki/templates",
                body_class: 'mce-content-body',
                paste_data_images: true,
                relative_urls : true,
                document_base_url: leantime.appUrl + "/",
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
            }
        );


    };

    var initNotesEditor = function (callback) {

        var entityId = jQuery("input[name=id]").val();

        if(entityId == undefined) {
            entityId = "new";
        }

        let projectId = 'none';
        if(window.leantime.currentProject){
            projectId = window.leantime.currentProject;
        }

        //modal is 50px from top. Always
        //Then reduce headline, save button range padding from modal
        var height = window.innerHeight - 50 - 205;

        jQuery('textarea.notesEditor').tinymce(
            {
                // General options
                width: "100%",
                skin_url: leantime.appUrl + '/dist/css/libs/tinymceSkin/oxide',
                content_css: leantime.appUrl + '/theme/' + leantime.theme + '/css/'+ leantime.colorScheme +'.css,'
                    + leantime.appUrl + '/dist/css/editor.' + leantime.version + '.min.css',
                content_style: "html {text-align:center;} body.mce-content-body{ font-size:14px; color:var(--secondary-font-color); max-width:none;} img { max-width: 100%; }",
                plugins : "autosave,imagetools,embed,autoresize,shortlink,checklist,bettertable,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,advancedTemplate,advlist,codesample,mention,slashcommands,textpattern",
                toolbar : "link image table emoticons | checklist bullist | advancedTemplate | restoredraft",
                toolbar_location: 'bottom',
                autosave_prefix: 'leantime-complexEditor-autosave-{path}{query}-{id}-'+projectId+'-'+entityId+'-',
                autosave_restore_when_empty: true,
                autosave_retention: '120m',
                autosave_interval: '10s',
                autosave_ask_before_unload: false,
                branding: false,
                statusbar: false,
                convert_urls: true,
                menubar:false,
                resizable: true,
                templates : leantime.appUrl + "/wiki/templates",
                body_class: 'mce-content-body',
                paste_data_images: true,
                relative_urls : true,
                document_base_url: leantime.appUrl + "/",
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

            }
        );
    };

    // Make public what you want to have public, everything else is private
    return {
        initSimpleEditor:initSimpleEditor,
        initComplexEditor:initComplexEditor,
        initNotesEditor:initNotesEditor
    };

})();
