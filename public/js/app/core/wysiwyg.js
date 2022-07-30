/*
 * 	Additional function for wysiwyg.html
 *	Written by ThemePixels	
 *	http://themepixels.com/
 *
 *	Built for Shamcey Metro Admin Template
 *  http://themeforest.net/category/site-templates/admin-templates
 */

jQuery(document).ready(function() {

    //Simple wysiwyg, Use for comments and short interactions
    jQuery('textarea.tinymceSimple').tinymce(
        {
            // General options
            width: "98%",
            skin_url: leantime.appUrl+'/css/libs/tinymceSkin/oxide',
            content_css: leantime.appUrl+'/css/libs/tinymceSkin/oxide/content.css',
            height:"150",
            content_style: "img { max-width: 100%; }",
            plugins : "emoticons,autolink,link,image,lists,table,save,preview,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist",
            toolbar : "bold italic strikethrough |  link unlink image | bullist numlist | emoticons",
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


    //Complex editor, use for main entitiy derscriptions, documents etc.
    jQuery('textarea.complexEditor').tinymce(
        {
            // General options
            width: "98%",
            skin_url: leantime.appUrl+'/css/libs/tinymceSkin/oxide',
            content_css: leantime.appUrl+'/css/libs/tinymceSkin/oxide/content.css',
            height:"700",
            content_style: "body.mce-content-body{ font-size:14px; } img { max-width: 100%; }",
            plugins : "emoticons,autolink,link,image,lists,table,save,preview,media,searchreplace,paste,directionality,fullscreen,noneditable,visualchars,template,advlist",
            toolbar : "bold italic strikethrough | formatselect forecolor | alignleft aligncenter alignright | link unlink image media | bullist numlist | table | template | emoticons",
            branding: true,
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

});
