leantime.timesheetsController = (function () {
    var closeModal = false;

    var initTimesheetsTable = function (groupBy) {
        jQuery(document).ready(function () {
            var allTimesheets = jQuery("#allTimesheetsTable").DataTable({
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
                    },
                    "buttons": {
                        colvis: leantime.i18n.__("datatables.buttons.colvis"),
                        csv: leantime.i18n.__("datatables.buttons.download")
                    }

                },
                "dom": '<"top">rt<"bottom"ilp><"clear">',
                // CUSTOM: Enable searching for modern search component
                "searching": true,
                "stateSave": true,
                "displayLength":100,

            });

            if (jQuery.fn.dataTable && jQuery.fn.dataTable.Buttons) {
                var buttons = new jQuery.fn.dataTable.Buttons(allTimesheets, {
                    buttons: [
                        {
                            extend: 'csvHtml5',
                            title: leantime.i18n.__("label.filename_fileexport"),
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
                        },
                        {
                            extend: 'colvis',
                            columns: ':not(.noVis)'
                        }
                    ]
                });

                var buttonsContainer = buttons.container();

                try {
                    if (jQuery('#tableButtons').length) {
                        jQuery('#tableButtons').empty().append(buttonsContainer);
                    } else {
                        jQuery(allTimesheets.table().container())
                            .find('.dataTables_length')
                            .append(buttonsContainer);
                    }
                } catch (e) {
                    // Swallow exception to avoid breaking rendering if Buttons fails
                }
            }

            jQuery('# reallTimesheetsTable').on('column-visibility.dt', function ( e, settings, column, state ) {
                allTimesheets.draw(false);
            });
            
            // CUSTOM: Initialize modern search component
            if (typeof leantime.timesheetSearch !== 'undefined') {
                leantime.timesheetSearch.init(allTimesheets);
            }
        });
    };

    var initEditTimeModal = function () {
        var canvasoptions = {
            sizes: {
                minW:  700,
                minH: 1000,
            },
            resizable: true,
            autoSizable: true,
            callbacks: {
                beforeShowCont: function () {
                    jQuery(".showDialogOnLoad").show();
                    if (closeModal === true) {
                        closeModal = false;
                        location.reload();
                    }
                },
                afterShowCont: function () {
                    jQuery(".editTimeModal").nyroModal(canvasoptions);
                },
                beforeClose: function () {
                    location.reload();
                }
            },
            titleFromIframe: true

        };

        jQuery(".editTimeModal").nyroModal(canvasoptions);
    };

    // Make public what you want to have public, everything else is private
    return {
        initTimesheetsTable:initTimesheetsTable,
        initEditTimeModal:initEditTimeModal,
    };
})();
