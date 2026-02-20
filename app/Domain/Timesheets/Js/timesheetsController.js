leantime.timesheetsController = (function () {
    var closeModal = false;

    var initTimesheetsTable = function (groupBy) {
        jQuery(document).ready(function () {
            var allTimesheets = jQuery("#allTimesheetsTable").DataTable({
                "language": {
                    "decimal": leantime.i18n.__("datatables.decimal"),
                    "emptyTable": leantime.i18n.__("datatables.emptyTable"),
                    "info": leantime.i18n.__("datatables.info"),
                    "infoEmpty": leantime.i18n.__("datatables.infoEmpty"),
                    "infoFiltered": leantime.i18n.__("datatables.infoFiltered"),
                    "infoPostFix": leantime.i18n.__("datatables.infoPostFix"),
                    "thousands": leantime.i18n.__("datatables.thousands"),
                    "lengthMenu": leantime.i18n.__("datatables.lengthMenu"),
                    "loadingRecords": leantime.i18n.__("datatables.loadingRecords"),
                    "processing": leantime.i18n.__("datatables.processing"),
                    "search": leantime.i18n.__("datatables.search"),
                    "zeroRecords": leantime.i18n.__("datatables.zeroRecords"),
                    "paginate": {
                        "first": leantime.i18n.__("datatables.first"),
                        "last": leantime.i18n.__("datatables.last"),
                        "next": leantime.i18n.__("datatables.next"),
                        "previous": leantime.i18n.__("datatables.previous"),
                    },
                    "aria": {
                        "sortAscending": leantime.i18n.__("datatables.sortAscending"),
                        "sortDescending": leantime.i18n.__("datatables.sortDescending"),
                    },
                    "buttons": {
                        colvis: leantime.i18n.__("datatables.buttons.colvis"),
                        csv: leantime.i18n.__("datatables.buttons.download")
                    }

                },
                "columnDefs": [
                    {
                        "targets": 5,
                        "searchable": false
                    }
                ],
                "dom": '<"top">rt<"bottom"ilp><"clear">',
                "searching": true,
                "stateSave": true,
                "displayLength": 100,

            });

            if (jQuery.fn.dataTable && jQuery.fn.dataTable.Buttons) {
                var buttons = new jQuery.fn.dataTable.Buttons(allTimesheets, {
                    buttons: [
                        {
                            extend: 'csvHtml5',
                            title: leantime.i18n.__("label.filename_fileexport"),
                            charset: 'utf-8',
                            bom: true,
                            footer: true,
                            exportOptions: {
                                orthogonal: 'export',
                                columns: ':visible',
                                format: {
                                    body: function (data, row, column, node) {
                                        if (typeof window.leantime !== 'undefined' &&
                                            typeof window.leantime.timesheetsExport !== 'undefined' &&
                                            typeof window.leantime.timesheetsExport.resolveCell === 'function') {
                                            return window.leantime.timesheetsExport.resolveCell(jQuery(node), data);
                                        }

                                        if (typeof jQuery(node).data('order') !== 'undefined') {
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
                }
            }

            jQuery('#allTimesheetsTable').on('column-visibility.dt', function (e, settings, column, state) {
                allTimesheets.draw(false);
            });

            if (typeof leantime.timesheetSearch !== 'undefined') {
                leantime.timesheetSearch.init(allTimesheets);
            }
        });
    };

    var initEditTimeModal = function () {
        var canvasoptions = {
            sizes: {
                minW: 700,
                maxW: 1000,
                minH: 1000,
            },
            resizable: false,
            autoSizable: false,
            callbacks: {
                beforeShowCont: function () {
                    jQuery(".showDialogOnLoad").show();

                    if (closeModal === true) {
                        closeModal = false;
                        location.reload();
                    }
                },
                beforeClose: function () {
                    location.reload();
                }
            },
            titleFromIframe: true
        };

        jQuery(document).on('click', '.editTimeModal', function (e) {
            e.preventDefault();
            jQuery(this).nyroModal(canvasoptions);
        });
    };


    var formatHours = function (hours) {
        var hoursFormat = jQuery('.timesheetTable').data('hours-format') ||
            jQuery('#allTimesheetsTable').data('hours-format') ||
            'decimal';

        hours = parseFloat(hours) || 0;

        if (hoursFormat === 'decimal') {
            return hours.toFixed(2);
        }

        return formatHoursHuman(hours);
    };

    var formatHoursHuman = function (hours) {
        var totalMinutes = Math.round(hours * 60);

        if (totalMinutes === 0) {
            return '0m';
        }

        var minutesInWeek = 40 * 60;
        var minutesInDay = 8 * 60;

        var weeks = Math.floor(totalMinutes / minutesInWeek);
        totalMinutes = totalMinutes % minutesInWeek;

        var days = Math.floor(totalMinutes / minutesInDay);
        totalMinutes = totalMinutes % minutesInDay;

        var wholeHours = Math.floor(totalMinutes / 60);
        var minutes = totalMinutes % 60;

        var parts = [];

        if (weeks > 0) {
            parts.push(weeks + 'w');
        }

        if (days > 0) {
            parts.push(days + 'd');
        }

        if (wholeHours > 0) {
            parts.push(wholeHours + 'h');
        }

        if (minutes > 0) {
            parts.push(minutes + 'm');
        }

        return parts.join(' ');
    };

    return {
        initTimesheetsTable: initTimesheetsTable,
        initEditTimeModal: initEditTimeModal,
        formatHours: formatHours,
    };
})();
