leantime.leanCanvasController = (function () {

    var closeModal = false;

    //Variables
    var canvasoptions = {
        sizes: {
            minW:  700,
            minH: 1000,
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

                jQuery(".canvasModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions);

                jQuery('textarea.researchTextEditor').tinymce(
                    {
                        // General options
                        width: "100%",
                        height:"200px",
                        skin_url: leantime.appUrl+'/css/tinymceSkin/oxide',
                        content_css: leantime.appUrl+'/css/tinymceSkin/oxide/content.css',
                        content_style: "img { max-width: 100%; }",
                        plugins : "autolink,link,textcolor,image,lists,pagebreak,table,save,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,advlist",
                        // Theme options
                        toolbar : "bold italic strikethrough | fontsizeselect forecolor | link unlink image | bullist | numlist | fullscreen",
                        branding: false,
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
        jQuery(".canvasModal, #commentForm, #commentForm .deleteComment, .leanCanvasMilestone .deleteMilestone").nyroModal(canvasoptions);
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

    var setCanvasHeights = function () {

        var maxHeight = 0;

        jQuery("#firstRow div.contentInner").each(function(){
            if(jQuery(this).height() > maxHeight){
                maxHeight = jQuery(this).height()+100;
            }
        });

        jQuery("#firstRow div.contentInner").css("height", maxHeight);

    };

    var setSimpleCanvasHeights = function () {

        var maxHeight = 0;

        var height = jQuery("html").height()-320;
        jQuery("#firstRow .column .contentInner").css("height", height);

    };



    var initFilterBar = function () {

        jQuery(window).bind("load", function () {
            jQuery(".loading").fadeOut();
            jQuery(".filterBar .row-fluid").css("opacity", "1");


        });

    };

    var initCanvasLinks = function () {

        jQuery(".addCanvasLink").click(function() {

            jQuery('#addCanvas').modal('show');

        });

        jQuery(".editCanvasLink").click(function() {

            jQuery('#editCanvas').modal('show');

        });

    };

    var initUserDropdown = function () {

        jQuery("body").on(
            "click", ".userDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 3) {

                    var canvasId = dataValue[0];
                    var userId = dataValue[1];
                    var profileImageId = dataValue[2];

                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl+'/api/leancanvas',
                            data:
                                {
                                    id : canvasId,
                                    author:userId
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#userDropdownMenuLink"+canvasId+" span.text span#userImage"+canvasId+" img").attr("src", leantime.appUrl+"/api/users?profileImage="+profileImageId);
                            jQuery("#userDropdownMenuLink"+canvasId+" span.text span#user"+canvasId).text(dataLabel);
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.user_updated"));
                        }
                    );

                }
            }
        );
    };

    var initStatusDropdown = function () {

        jQuery("body").on(
            "click", ".statusDropdown .dropdown-menu a", function () {

                var dataValue = jQuery(this).attr("data-value").split("_");
                var dataLabel = jQuery(this).attr('data-label');

                if (dataValue.length == 2) {

                    var canvasItemId = dataValue[0];
                    var status = dataValue[1];


                    jQuery.ajax(
                        {
                            type: 'PATCH',
                            url: leantime.appUrl+'/api/leancanvas',
                            data:
                                {
                                    id : canvasItemId,
                                    status:status
                                }
                        }
                    ).done(
                        function () {
                            jQuery("#statusDropdownMenuLink"+canvasItemId+" span.text").text(dataLabel);
                            jQuery("#statusDropdownMenuLink"+canvasItemId).removeClass().addClass("label-"+status+" dropdown-toggle f-left status ");
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.status_updated"));

                        }
                    );

                }
            }
        );

    };

    // Make public what you want to have public, everything else is private
    return {
        setCloseModal:setCloseModal,
        toggleMilestoneSelectors: toggleMilestoneSelectors,
        openModalManually:openModalManually,
        setCanvasHeights:setCanvasHeights,
        initFilterBar:initFilterBar,
        initCanvasLinks:initCanvasLinks,
        initUserDropdown:initUserDropdown,
        initStatusDropdown:initStatusDropdown,
        setSimpleCanvasHeights:setSimpleCanvasHeights
    };
})();
