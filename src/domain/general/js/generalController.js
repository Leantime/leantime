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

                if(jQuery('.login-alert .alert').text() !== ''){
                    jQuery('.login-alert').fadeIn();
                }
            }
        );

    })();

    //Functions
    var _initPopOvers = function() {
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
                width: "98%",
                skin_url: leantime.appUrl+'/css/libs/tinymceSkin/oxide',
                content_css: leantime.appUrl+'/css/themes/leantime-'+leantime.theme+'.css,'+leantime.appUrl+'/css/libs/tinymceSkin/oxide/content.css',height:"150",
                content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                plugins : "shortlink,checklist,table,emoticons,autolink,image,lists,save,preview,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist",
                toolbar : "bold italic strikethrough | link unlink image | checklist bullist numlist | emoticons",
                branding: true,
                statusbar: true,
                convert_urls: false,
                paste_data_images: true,
                menubar:false,
                images_upload_handler: function (blobInfo, success, failure) {
                    var xhr, formData;

                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', leantime.appUrl+'/api/files');

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
                        leantime.appUrl+'/files/showAll&modalPopUp=true',
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
    };

    var initComplexEditor = function () {

        jQuery('textarea.complexEditor').tinymce(
            {
                // General options
                width: "98%",
                skin_url: leantime.appUrl+'/css/libs/tinymceSkin/oxide',
                content_css: leantime.appUrl+'/css/themes/leantime-'+leantime.theme+'.css,'+leantime.appUrl+'/css/libs/tinymceSkin/oxide/content.css',
                height:"400",
                content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
                plugins : "shortlink,checklist,table,bettertable,emoticons,autolink,image,lists,save,preview,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist",
                toolbar : "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media emoticons | checklist bullist numlist | table | template",
                branding: false,
                statusbar: true,
                convert_urls: false,
                menubar:false,
                resizable: true,
                paste_data_images: true,

                images_upload_handler: function (blobInfo, success, failure) {
                    var xhr, formData;

                    xhr = new XMLHttpRequest();
                    xhr.withCredentials = false;
                    xhr.open('POST', leantime.appUrl+'/api/files');

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
                        leantime.appUrl+'/files/showAll&modalPopUp=true',
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


    };

    var makeInputReadonly = function (container) {

        if(typeof container === undefined) {
            container = "body";
        }

        jQuery(container).find("input").not(".filterBar input").prop("readonly", true);
        jQuery(container).find("input").not(".filterBar input").prop("disabled", true);

        jQuery(container).find("select").not(".filterBar select, .mainSprintSelector").prop("readonly", true);
        jQuery(container).find("select").not(".filterBar select, .mainSprintSelector").prop("disabled", true);

        jQuery(container).find("textarea").not(".filterBar textarea").prop("disabled", true);

        jQuery(container).find("a.delete").remove();

        jQuery(container).find(".quickAddLink").hide();

        if(jQuery(container).find(".complexEditor").length) {
            jQuery(container).find(".complexEditor").each(function(element){

                jQuery(this).tinymce().getBody().setAttribute('contenteditable', "false");
            });
        }

        if(jQuery(container).find(".tinymceSimple").length) {

            jQuery(container).find(".tinymceSimple").each(function(element){

                jQuery(this).tinymce().getBody().setAttribute('contenteditable', "false");
            });
        }

        jQuery(container).find(".tox-editor-header").hide();
        jQuery(container).find(".tox-statusbar").hide();

        jQuery(container).find(".ticketDropdown a").removeAttr("data-toggle");

        jQuery("#mainToggler").hide();
        jQuery(".commentBox").hide();
        jQuery(".deleteComment, .replyButton").hide();


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

    // Make public what you want to have public, everything else is private
    return {
        initSimpleEditor:_initSimpleEditor,
        initComplexEditor:initComplexEditor,
        makeInputReadonly:makeInputReadonly,
        enableCommenterForms:enableCommenterForms
    };

})();
