import jQuery from 'jquery';
import i18n from 'i18n';

let closeModal = false;

export const initTimesheetsTable = function (groupBy) {
    jQuery(document).ready(function () {
        var allTimesheets = jQuery("#allTimesheetsTable").DataTable({
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
                },
                "buttons": {
                    colvis: i18n.__("datatables.buttons.colvis"),
                    csv: i18n.__("datatables.buttons.download")
                }

            },
            "dom": '<"top">rt<"bottom"ilp><"clear">',
            "searching": false,
            "stateSave": true,
            "displayLength":100,

        });

        var buttons = new jQuery.fn.dataTable.Buttons(allTimesheets, {
            buttons: [
                {
                    extend: 'csvHtml5',
                    title: i18n.__("label.filename_fileexport"),
                    charset: 'utf-8',
                    bom: true,
                    exportOptions: {
                        format: {
                            body: function ( data, row, column, node ) {
                                if ( typeof jQuery(node).data('order') !== 'undefined') {
                                    data = jQuery(node).data('order');
                                }
                                return data;
                            }
                        }
                    }
            }, {
                extend: 'colvis',
                columns: ':not(.noVis)'
            }
            ]
        }).container().appendTo(jQuery('#tableButtons'));

        jQuery('#allTimesheetsTable').on('column-visibility.dt', function ( e, settings, column, state ) {
            allTimesheets.draw(false);
        });
    });
};

// Make public what you want to have public, everything else is private

export const timesheetsController = {
    initTimesheetsTable: initTimesheetsTable
}