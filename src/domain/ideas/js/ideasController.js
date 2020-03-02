leantime.ideasController = (function () {

    var closeModal = false;

    //Variables
    var canvasoptions = {
        sizes: {
            minW:  700,
            minH: 650,
        },
        resizable: true,
        autoSizable: true,
        callbacks: {
            beforeShowCont: function() {
                jQuery(".showDialogOnLoad").show();
                if(closeModal == true){
                    closeModal = false;
                    location.reload();
                }
            },
            afterShowCont: function () {

                jQuery(".ideaModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions);
                jQuery('textarea.tinymce').tinymce(
                    {
                        // General options
                        width: "98%",
                        height:"200",
                        content_style: "img { max-width: 100%; }",
                        plugins : "autolink,link,textcolor,image,lists,pagebreak,table,save,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,advlist",
                        // Theme options
                        toolbar : "bold,italic,strikethrough,|,fontsizeselect,forecolor,|,link,unlink,image,|,bullist,|,fullscreen",
                        branding: false,
                        menubar:false,
                        statusbar: false,
                        convert_urls: false,
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
                        file_browser_callback: function (field_name, url, type, win) {

                            window.tinyMceUploadFieldname = field_name;

                            var shortOptions = {
                                afterShowCont: function () {
                                    jQuery(".fileModal").nyroModal({callbacks:shortOptions});

                                }
                            };


                            jQuery.nmManual(
                                leantime.appUrl+'/files/showAll&modalPopUp=true',
                                {
                                    stack: true,
                                    callbacks: shortOptions
                                }
                            );
                            jQuery.nmTop().elts.cont.css("zIndex", "1000010");
                            jQuery.nmTop().elts.bg.css("zIndex", "1000010");
                            jQuery.nmTop().elts.load.css("zIndex", "1000010");
                            jQuery.nmTop().elts.all.find('.nyroModalCloseButton').css("zIndex", "1000010");

                        }
                    }
                );

            },
            beforeClose: function () {
                location.reload();
            }
        },
        titleFromIframe: true

    };


    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initModals();
            }
        );

    })();

    //Functions

    var _initModals = function () {
        jQuery(".ideaModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions);
    };

    var openModalManually = function (url) {
        jQuery.nmManual(url, canvasoptions);
    };


    var toggleMilestoneSelectors = function (trigger) {
        if(trigger == 'existing') {
            jQuery('#newMilestone, #milestoneSelectors').hide('fast');
            jQuery('#existingMilestone').show();
            _initModals();

        }
        if(trigger == 'new') {
            jQuery('#newMilestone').show();
            jQuery('#existingMilestone, #milestoneSelectors').hide('fast');
            _initModals();
        }

        if(trigger == 'hide') {
            jQuery('#newMilestone, #existingMilestone').hide('fast');
            jQuery('#milestoneSelectors').show('fast');
        }
    };

    var setCloseModal = function() {
        closeModal = true;
    };

    // Make public what you want to have public, everything else is private
    return {
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually:openModalManually
    };
})();
