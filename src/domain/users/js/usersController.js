leantime.usersController = (function () {

    // Variables (underscore for private variables)
    var publicThing = "not secret";
    var _privateThing = "secret";

    var _uploadResult;

    //Constructor
    (function () {

    })();

    //Functions

    var readURL = function (input) {

        clearCroppie();

        if (input.files && input.files[0]) {
            var reader = new FileReader();

            var profileImg = jQuery('#profileImg');
            reader.onload = function (e) {
                //profileImg.attr('src', e.currentTarget.result);

                _uploadResult = profileImg
                    .croppie(
                        {
                            enableExif: true,
                            viewport: {
                                width: 175,
                                height: 175,
                                type: 'circle'
                            },
                            boundary: {
                                width: 200,
                                height: 200
                            }
                        }
                    );

                _uploadResult.croppie(
                    'bind', {
                        url: e.currentTarget.result
                    }
                );

                jQuery("#previousImage").hide();
            };

            reader.readAsDataURL(input.files[0]);
        }
    };

    var clearCroppie = function () {
        jQuery('#profileImg').croppie('destroy');
        jQuery("#previousImage").show();
    };

    var saveCroppie = function () {

        jQuery('#save-picture').addClass('running');

        jQuery('#profileImg').attr('src', leantime.appUrl+'/images/loaders/loader28.gif');
        _uploadResult.croppie(
            'result', {
                type: "blob",
                circle: true
            }
        ).then(
            function (result) {
                    leantime.usersService.saveUserPhoto(result);
            }
        );
    };

    var initUserTable = function () {

        jQuery(document).ready(function() {

            var size = 100;

            var allUsersTable = jQuery("#allUsersTable").DataTable({
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
        readURL: readURL,
        clearCroppie: clearCroppie,
        saveCroppie: saveCroppie,
        initUserTable:initUserTable
    };
})();
