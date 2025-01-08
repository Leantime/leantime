import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';
import { saveUserPhoto } from './usersService';

let _croppieInstance = null;

export const readURL = function (input) {

    clearCroppie();

    if (input.files && input.files[0]) {
        var reader = new FileReader();

        var profileImg = jQuery('#profileImg');
        reader.onload = function (e) {
            //profileImg.attr('src', e.currentTarget.result);

            _croppieInstance = profileImg
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

                _croppieInstance.croppie(
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

export const clearCroppie = function () {
    jQuery('#profileImg').croppie('destroy');
    jQuery("#previousImage").show();
};

export const saveCroppie = function () {
    jQuery('#save-picture').addClass('running');
    jQuery('#profileImg').attr('src', appUrl + '/images/loaders/loader28.gif');
    _croppieInstance.croppie(
        'result',
        {
            type: "blob",
            circle: true
        }
    ).then(
        function (result) {
            saveUserPhoto(result);
        }
    );
};

export const initUserTable = function () {

    jQuery(document).ready(function () {

        var size = 100;

        var allUsersTable = jQuery("#allUsersTable").DataTable({
            "language": {
                "decimal":        i18n.__("datatables.decimal"),
                "emptyTable":     i18n.__("datatables.emptyTable"),
                "info":           i18n.__("datatables.info"),
                "infoEmpty":      i18n.__("datatables.infoEmpty"),
                "infoFiltered":   i18n.__("datatables.infoFiltered"),
                "infoPostFix":    i18n.__("datatables.infoPostFix"),
                "thousands":      i18n.__("datatables.thousands"),
                "lengthMenu":     i18n.__("datatables.lengthMenu"),
                "loadingRecords": i18n.__("datatables.loadingRecords"),
                "processing":     i18n.__("datatables.processing"),
                "search":         i18n.__("datatables.search"),
                "zeroRecords":    i18n.__("datatables.zeroRecords"),
                "paginate": {
                    "first":      i18n.__("datatables.first"),
                    "last":       i18n.__("datatables.last"),
                    "next":       i18n.__("datatables.next"),
                    "previous":   i18n.__("datatables.previous"),
                },
                "aria": {
                    "sortAscending":  i18n.__("datatables.sortAscending"),
                    "sortDescending":i18n.__("datatables.sortDescending"),
                }

            },
            "dom": '<"top">rt<"bottom"ilp><"clear">',
            "searching": false,
            "displayLength":100
        });

    });

};


export const checkPWStrength = function (pwField) {

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
            strengthBadge.style.backgroundColor = "#468847";
            strengthBadge.textContent = i18n.__('label.strong');
        } else if (mediumPassword.test(PasswordParameter)) {
            strengthBadge.style.backgroundColor = '#f89406';
            strengthBadge.textContent = i18n.__('label.medium');
        } else {
            strengthBadge.style.backgroundColor = '#b94a48';
            strengthBadge.textContent = i18n.__('label.weak');
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

export const usersController = {
    readURL: readURL,
    clearCroppie: clearCroppie,
    saveCroppie: saveCroppie,
    initUserTable: initUserTable,
    checkPWStrength: checkPWStrength,
};

export default usersController;
