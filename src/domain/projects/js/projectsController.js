leantime.projectsController = (function () {

    //Variables


    //Constructor
    (function () {
        jQuery(document).ready(
            function () {

            }
        );

    })();

    //Functions

    var initDates = function () {

        jQuery(".projectDateFrom, .projectDateTo").datepicker(
            {
                dateFormat:  leantime.i18n.__("language.jsdateformat"),
                dayNames: leantime.i18n.__("language.dayNames").split(","),
                dayNamesMin:  leantime.i18n.__("language.dayNamesMin").split(","),
                dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
                monthNames: leantime.i18n.__("language.monthNames").split(","),
                currentText: leantime.i18n.__("language.currentText"),
                closeText: leantime.i18n.__("language.closeText"),
                buttonText: leantime.i18n.__("language.buttonText"),
                isRTL: JSON.parse(leantime.i18n.__("language.isRTL")),
                nextText: leantime.i18n.__("language.nextText"),
                prevText: leantime.i18n.__("language.prevText"),
                weekHeader: leantime.i18n.__("language.weekHeader"),
            }
        );
    };

    var initProjectTabs = function () {
        jQuery('.projectTabs').tabs();
    };

    var initDuplicateProjectModal = function () {

        var regularModelConfig = {
            sizes: {
                minW: 450,
                minH: 350
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                afterShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    initDates();
                    jQuery(".duplicateProjectModal, .formModal").nyroModal(regularModelConfig);
                },
                beforeClose: function () {
                    location.reload();
                }
            }
        };

        jQuery(".duplicateProjectModal").nyroModal(regularModelConfig);

    };

    var initProgressBar = function (percentage) {

        jQuery("#progressbar").progressbar({
            value: percentage
        });

    };

    var initProjectsEditor = function () {

        jQuery('textarea.projectTinymce').tinymce(
            {
                // General options
                width: "98%",
                skin_url: leantime.appUrl+'/css/tinymceSkin/oxide',
                content_css: leantime.appUrl+'/css/tinymceSkin/oxide/content.css',
                height:"300",
                content_style: "img { max-width: 100%; }",
                plugins : "autolink,link,image,lists,pagebreak,table,save,insertdatetime,preview,media,searchreplace,print,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,template,advlist",
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
                        '/files/showAll&modalPopUp=true',
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

    var initProjectTable = function () {

        jQuery(document).ready(function() {

            var size = 100;

            var allProjects = jQuery("#allProjectsTable").DataTable({
                "language": {
                    "decimal":        leantime.i18n.__("datatables.decimal"),
                    "emptyTable":     leantime.i18n.__("datatables.emptyTable"),
                    "info":           leantime.i18n.__("datatables.info"),
                    "infoEmpty":      leantime.i18n.__("datatables.infoEmpty"),
                    "infoFiltered":   leantime.i18n.__("datatables.infoFiltered"),
                    "infoPostFix":    leantime.i18n.__("datatables.infoPostFix"),
                    "thousands":      leantime.i18n.__("datatables.thousands"),
                    "lengthMenu":     leantime.i18n.__("datatables.lengthMenu"),
                    "loadingRecords": leantime.i18n.__("datatables.loadingRecords"),
                    "processing":     leantime.i18n.__("datatables.processing"),
                    "search":         leantime.i18n.__("datatables.search"),
                    "zeroRecords":    leantime.i18n.__("datatables.zeroRecords"),
                    "paginate": {
                        "first":      leantime.i18n.__("datatables.first"),
                        "last":       leantime.i18n.__("datatables.last"),
                        "next":       leantime.i18n.__("datatables.next"),
                        "previous":   leantime.i18n.__("datatables.previous"),
                    },
                    "aria": {
                        "sortAscending":  leantime.i18n.__("datatables.sortAscending"),
                        "sortDescending":leantime.i18n.__("datatables.sortDescending"),
                    }

                },
                "dom": '<"top">rt<"bottom"ilp><"clear">',
                "searching": false,
                "displayLength":100
            });

        });

    };

    // Make public what you want to have public, everything else is private
    return {
        initDates:initDates,
        initProjectTabs:initProjectTabs,
        initProgressBar:initProgressBar,
        initProjectTable:initProjectTable,
        initProjectsEditor:initProjectsEditor,
        initDuplicateProjectModal:initDuplicateProjectModal
    };
})();
