leantime.timesheetsController = (function () {
    var closeModal = false;

    var initTimesheetsTable = function (groupBy) {
        document.addEventListener('DOMContentLoaded', function () {
            var tableEl = document.querySelector("#allTimesheetsTable");
            if (!tableEl) return;

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
                "searching": false,
                "stateSave": true,
                "displayLength":100,

            });

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
                                    if ( typeof node.dataset.order !== 'undefined') {
                                        data = node.dataset.order;
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
            });

            var tableButtonsEl = document.querySelector('#tableButtons');
            if (tableButtonsEl) {
                tableButtonsEl.appendChild(buttons.container()[0]);
            }

            jQuery('#allTimesheetsTable').on('column-visibility.dt', function ( e, settings, column, state ) {
                allTimesheets.draw(false);
            });
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
                    document.querySelectorAll(".showDialogOnLoad").forEach(function (el) {
                        el.style.display = '';
                    });
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
