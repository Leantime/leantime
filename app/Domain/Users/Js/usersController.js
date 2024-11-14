leantime.usersController = (function () {

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
                    'bind',
                    {
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

        jQuery('#profileImg').attr('src', leantime.appUrl + '/images/loaders/loader28.gif');
        _uploadResult.croppie(
            'result',
            {
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

        jQuery(document).ready(function () {

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

    var _initModals = function () {

        var userImportModalConfig = {
            sizes: {
                minW: 400,
                minH: 350
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                afterShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    jQuery(".userImportModal").nyroModal(userImportModalConfig);
                }
            }
        };

        jQuery(".userImportModal").nyroModal(userImportModalConfig);
    }

    var initUserEditModal = function () {

        var userEditModal = {
            sizes: {
                minW: 900,
                minH: 250
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                afterShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    jQuery(".userEditModal").nyroModal(userEditModal);
                },
                beforeClose: function () {

                    location.reload();
                },
            }
        };

        jQuery(".userEditModal").nyroModal(userEditModal);
    }

    var checkPWStrength = function (pwField) {

        let timeout;

        // traversing the DOM and getting the input and span using their IDs

        let password = document.getElementById(pwField)
        let strengthBadge = document.getElementById('pwStrength')

        // The strong and weak password Regex pattern checker

        let strongPassword = new RegExp('(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{8,})')
        let mediumPassword = new RegExp('((?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*[^A-Za-z0-9])(?=.{6,}))|((?=.*[a-z])(?=.*[A-Z])(?=.*[^A-Za-z0-9])(?=.{8,}))')

        function StrengthChecker(PasswordParameter)
        {
            if (strongPassword.test(PasswordParameter)) {
                strengthBadge.style.backgroundColor = "#107530";
                strengthBadge.textContent = leantime.i18n.__('label.strong');
            } else if (mediumPassword.test(PasswordParameter)) {
                strengthBadge.style.backgroundColor = '#C5850D';
                strengthBadge.textContent = leantime.i18n.__('label.medium');
            } else {
                strengthBadge.style.backgroundColor = '#CC6B6B';
                strengthBadge.textContent = leantime.i18n.__('label.weak');
            }
        }

        password.addEventListener("input", () => {

            //The badge is hidden by default, so we show it

            strengthBadge.style.display = 'block';
            clearTimeout(timeout);

            timeout = setTimeout(() => StrengthChecker(password.value), 500);

            if (password.value.length !== 0) {
                strengthBadge.style.display != 'block'
            } else {
                strengthBadge.style.display = 'none'
            }
        });

    }

    // Make public what you want to have public, everything else is private
    return {
        readURL: readURL,
        clearCroppie: clearCroppie,
        saveCroppie: saveCroppie,
        initUserTable:initUserTable,
        _initModals:_initModals,
        checkPWStrength:checkPWStrength,
        initUserEditModal:initUserEditModal
    };
})();
