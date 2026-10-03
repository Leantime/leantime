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

    /**
     * Decimal hours as "1h 15m" (display only, #2004). Mirrors Format::hoursMinutes() in PHP.
     */
    var formatHoursMinutes = function (decimalHours) {
        var hours = parseFloat(decimalHours);
        if (isNaN(hours)) {
            return '';
        }

        var totalMinutes = Math.round(Math.abs(hours) * 60);
        var sign = (hours < 0 && totalMinutes > 0) ? '-' : '';
        var wholeHours = Math.floor(totalMinutes / 60);
        var minutes = String(totalMinutes % 60).padStart(2, '0');
        var pattern = leantime.i18n.__("text.hours_minutes_short") || '%sh %sm';

        return sign + pattern.replace('%s', wholeHours).replace('%s', minutes);
    };

    /**
     * Hover hint on the weekly grid: each hour input and row total shows its value as hours +
     * minutes. Uses the native title attribute so it follows live edits without re-initialising
     * tooltips. The footer total <td>s are deliberately left untouched (their markup is asserted
     * by acceptance tests).
     */
    var refreshHoursMinutesTitles = function (tableSelector) {
        var table = jQuery(tableSelector);

        table.find("input.hourCell").each(function () {
            jQuery(this).attr("title", formatHoursMinutes(jQuery(this).val()));
        });

        table.find(".rowSum strong").each(function () {
            jQuery(this).attr("title", formatHoursMinutes(jQuery(this).text()));
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        initTimesheetsTable:initTimesheetsTable,
        initEditTimeModal:initEditTimeModal,
        formatHoursMinutes:formatHoursMinutes,
        refreshHoursMinutesTitles:refreshHoursMinutesTitles,
    };
})();
