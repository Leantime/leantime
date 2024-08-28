import jQuery from "jquery";
import i18n from 'i18n';

export const initDates = function () {
    jQuery(".projectDateFrom, .projectDateTo").datepicker(
        {
            dateFormat:  i18n.__("language.dateformat"),
            dayNames: i18n.__("language.dayNames").split(","),
            dayNamesMin:  i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: i18n.__("language.dayNamesShort").split(","),
            monthNames: i18n.__("language.monthNames").split(","),
            currentText: i18n.__("language.currentText"),
            closeText: i18n.__("language.closeText"),
            buttonText: i18n.__("language.buttonText"),
            isRTL: i18n.__("language.isRTL") === "true" ? 1 : 0,
            nextText: i18n.__("language.nextText"),
            prevText: i18n.__("language.prevText"),
            weekHeader: i18n.__("language.weekHeader"),
        }
    );
};

export const initClientTabs = function () {
    jQuery('.clientTabs').tabs();
};

export const initClientTable = function () {
    jQuery(document).ready(function () {

        var size = 100;

        var allProjects = jQuery("#allClientsTable").DataTable({
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

// Make public what you want to have public, everything else is private
export default {
    initDates: initDates,
    initClientTabs: initClientTabs,
    initClientTable: initClientTable
};
