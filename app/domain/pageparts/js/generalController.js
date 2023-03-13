leantime.generalController = (function () {

    //Variables

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initPopOvers();
                _initLabelModals();
                _initSimpleEditor();
                initComplexEditor();

                if (jQuery('.login-alert .alert').text() !== '') {
                    jQuery('.login-alert').fadeIn();
                }
            }
        );

    })();

    var mentionsConfig = {
        delimiter: '@',
        delay: 20,
        source: function (query, process, delimiter) {
            // Do your ajax call
            // When using multiple delimiters you can alter the query depending on the delimiter used
            if (delimiter === '@') {
                jQuery.getJSON(leantime.appUrl + '/api/users?projectUsersAccess=current', function (data) {
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
        }
    };

    //Functions
    var _initPopOvers = function () {
        jQuery('.popoverlink').popover({trigger: 'hover'});
    };

    var _initLabelModals = function () {

        var editLabelModalConfig = {
            sizes: {
                minW: 400,
                minH: 200
            },
            callbacks: {
                afterShowCont: function () {

                    jQuery(".editLabelModal").nyroModal(editLabelModalConfig);
                },
                beforeClose: function () {
                    location.reload();
                }
            }
        };

        jQuery(".editLabelModal").nyroModal(editLabelModalConfig);

    };

    var _initSimpleEditor = function () {

        jQuery('textarea.tinymceSimple').tinymce(
            {
                // General options
                width: "100%",
                skin_url: leantime.appUrl + '/css/libs/tinymceSkin/oxide',
                content_css: leantime.appUrl + '/theme/' + leantime.theme + '/css/theme.css,' + leantime.appUrl + '/css/libs/tinymceSkin/oxide/content.css,' + leantime.appUrl + '/css/components/wysiwyg-overrides.css,' + leantime.appUrl + '/css/libs/roboto.css',
                content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                plugins : "imagetools,shortlink,checklist,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist,mention,slashcommands",
                toolbar : "bold italic strikethrough | link unlink image | checklist bullist numlist | emoticons",
                //autosave_prefix: 'leantime-simpleEditor-autosave-{path}{query}-{id}-',
                //autosave_restore_when_empty: true,
                //autosave_retention: '120m',
                //autosave_interval: '10s',
                //autosave_ask_before_unload: false,
                branding: false,
                statusbar: false,
                convert_urls: true,
                paste_data_images: true,
                menubar:false,
                relative_urls : true,
                document_base_url : leantime.appUrl + "/",
                default_link_target: '_blank',

                mentions: mentionsConfig,
                images_upload_handler: function (blobInfo, success, failure) {
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
                },
                file_picker_callback: function (callback, value, meta) {

                    window.filePickerCallback = callback;

                    var shortOptions = {
                        afterShowCont: function () {
                            jQuery(".fileModal").nyroModal({callbacks:shortOptions});

                        }
                    };

                    jQuery.nmManual(
                        leantime.appUrl + '/files/showAll&modalPopUp=true',
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

                },
                setup: function (editor) {
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

                            while(placeholder[0]) {
                                placeholder[0].parentNode.removeChild(placeholder[0]);
                            }
                        }

                    });

                    editor.on("submit", function(){

                        var placeholder = editor.getDoc().getElementsByClassName("tinyPlaceholder");

                        console.log(placeholder);

                        if (placeholder.length > 0) {

                            while(placeholder[0]) {
                                console.log(placeholder[0]);
                                placeholder[0].remove();
                            }
                            editor.save();

                            console.log(editor.getDoc());
                        }
                    });
                }
            }
        );
    };

    var initComplexEditor = function () {

            var entityId = jQuery("input[name=id]").val();

            jQuery('textarea.complexEditor').tinymce(
            {
                // General options
                width: "100%",
                skin_url: leantime.appUrl + '/css/libs/tinymceSkin/oxide',
                content_css: leantime.appUrl + '/theme/' + leantime.theme + '/css/theme.css,' + leantime.appUrl + '/css/libs/tinymceSkin/oxide/content.css,' + leantime.appUrl + '/css/components/wysiwyg-overrides.css,' + leantime.appUrl + '/css/libs/roboto.css',
                content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                plugins : "imagetools,embed,autoresize,shortlink,checklist,bettertable,table,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist,codesample,mention,slashcommands",
                toolbar : "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media embed emoticons | checklist bullist numlist | table  | codesample",
                //autosave_prefix: 'leantime-complexEditor-autosave-{path}{query}-{id}-'+entityId,
                //autosave_restore_when_empty: true,
                //autosave_retention: '120m',
                //autosave_interval: '10s',
                //autosave_ask_before_unload: false,
                branding: false,
                statusbar: false,
                convert_urls: true,
                menubar:false,
                resizable: true,
                paste_data_images: true,
                relative_urls : true,
                document_base_url: leantime.appUrl + "/",
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
                mentions: mentionsConfig,
                images_upload_handler: function (blobInfo, success, failure) {
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
                },
                file_picker_callback: function (callback, value, meta) {

                    window.filePickerCallback = callback;

                    var shortOptions = {
                        afterShowCont: function () {
                            jQuery(".fileModal").nyroModal({callbacks:shortOptions});

                        }
                    };

                    jQuery.nmManual(
                        leantime.appUrl + '/files/showAll&modalPopUp=true',
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

                },
                setup: function (editor) {
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

                            while(placeholder[0]) {
                                placeholder[0].parentNode.removeChild(placeholder[0]);
                            }

                        }

                    });

                    editor.on("submit", function(){

                        var placeholder = editor.getDoc().getElementsByClassName("tinyPlaceholder");
                        if (placeholder.length > 0) {

                            while(placeholder[0]) {
                                placeholder[0].parentNode.removeChild(placeholder[0]);
                            }
                            editor.save();
                        }
                    });
                }
            }
        );


    };

    var initFixedToolBarEditor = function () {

        tinymce.init(
            {
                // General options
                inline: true,
                fixed_toolbar_container: ".externalToolbar",
                width: "100%",
                skin_url: leantime.appUrl + '/css/libs/tinymceSkin/oxide',
                content_css: leantime.appUrl + '/theme/' + leantime.theme + '/css/theme.css,' + leantime.appUrl + '/css/libs/tinymceSkin/oxide/content.css,' + leantime.appUrl + '/css/components/wysiwyg-overrides.css,' + leantime.appUrl + '/css/libs/roboto.css',
                content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                height:"400",
                content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                plugins : "shortlink,checklist,table,bettertable,emoticons,autolink,image,lists,save,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist,codesample,mention",
                toolbar : "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media emoticons | checklist bullist numlist | table",
                branding: false,
                statusbar: true,
                convert_urls: false,

                selector: '.fixedToolbarEditor',
                menubar:false,
                resizable: true,
                paste_data_images: true,
                mentions: mentionsConfig,
                images_upload_handler: function (blobInfo, success, failure) {
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
                },
                file_picker_callback: function (callback, value, meta) {

                    window.filePickerCallback = callback;

                    var shortOptions = {
                        afterShowCont: function () {
                            jQuery(".fileModal").nyroModal({callbacks:shortOptions});

                        }
                    };

                    jQuery.nmManual(
                        leantime.appUrl + '/files/showAll&modalPopUp=true',
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
            }
        );
    }

    var makeInputReadonly = function (container) {

        if (typeof container === undefined) {
            container = "body";
        }

        jQuery(container).find("input").not(".filterBar input").prop("readonly", true);
        jQuery(container).find("input").not(".filterBar input").prop("disabled", true);

        jQuery(container).find("select").not(".filterBar select, .mainSprintSelector").prop("readonly", true);
        jQuery(container).find("select").not(".filterBar select, .mainSprintSelector").prop("disabled", true);

        jQuery(container).find("textarea").not(".filterBar textarea").prop("disabled", true);

        jQuery(container).find("a.delete").remove();

        jQuery(container).find(".quickAddLink").hide();

        if (jQuery(container).find(".complexEditor").length) {
            jQuery(container).find(".complexEditor").each(function (element) {

                jQuery(this).tinymce().getBody().setAttribute('contenteditable', "false");
            });
        }

        if (jQuery(container).find(".tinymceSimple").length) {
            jQuery(container).find(".tinymceSimple").each(function (element) {

                jQuery(this).tinymce().getBody().setAttribute('contenteditable', "false");
            });
        }

        jQuery(container).find(".tox-editor-header").hide();
        jQuery(container).find(".tox-statusbar").hide();

        jQuery(container).find(".ticketDropdown a").removeAttr("data-toggle");

        jQuery("#mainToggler").hide();
        jQuery(".commentBox").hide();
        jQuery(".deleteComment, .replyButton").hide();

        jQuery(container).find(".dropdown i").removeClass('fa-caret-down');


    };

    var enableCommenterForms = function () {

        jQuery(".commentBox").show();

        //Hide reply comment boxes
        jQuery("#comments .replies .commentBox").hide();
        jQuery(".deleteComment, .replyButton").show();

        jQuery(".commentReply .tinymceSimple").tinymce().getBody().setAttribute('contenteditable', "true");
        jQuery(".commentReply .tox-editor-header").show();
        jQuery(".commentBox input").prop("readonly", false);
        jQuery(".commentBox input").prop("disabled", false);

        jQuery(".commentBox textarea").prop("readonly", false);
        jQuery(".commentBox textarea").prop("disabled", false);

    };

    var copyUrl = function (field) {

        // Get the text field
        var copyText = document.getElementById(field);

        // Select the text field
        copyText.select();
        copyText.setSelectionRange(0, 99999); // For mobile devices

        // Copy the text inside the text field
        navigator.clipboard.writeText(copyText.value);

        // Alert the copied text
        jQuery.growl({message: leantime.i18n.__("short_notifications.url_copied"), style: "success"});

    };


    // Make public what you want to have public, everything else is private
    return {
        initSimpleEditor:_initSimpleEditor,
        initComplexEditor:initComplexEditor,
        makeInputReadonly:makeInputReadonly,
        enableCommenterForms:enableCommenterForms,
        initFixedToolBarEditor:initFixedToolBarEditor,
        copyUrl:copyUrl,
    };

})();
