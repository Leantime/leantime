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

    var initMyListEditable = function () {
        // Toggle new entry row
        jQuery("#toggleAddHours").click(function () {
            var $row = jQuery(".newEntryRow");
            $row.toggle();
            if ($row.is(":visible")) {
                jQuery("#newEntryDate").focus();
            }
        });

        // Chosen.js for project and ticket selectors
        jQuery(".project-select").chosen();
        jQuery(".ticket-select").chosen();

        // Project filters ticket list
        jQuery(".project-select").change(function () {
            jQuery(".ticket-select").removeAttr("selected");
            jQuery(".ticket-select").val("");
            jQuery(".ticket-select").trigger("liszt:updated");

            jQuery(".ticket-select option").show();
            jQuery("#ticketSelect .chosen-results li").show();
            var selectedValue = jQuery(this).find("option:selected").val();
            if (selectedValue) {
                jQuery(".ticket-select option").not(".project_" + selectedValue).not('[value=""]').hide();
                jQuery("#ticketSelect .chosen-results li").not(".project_" + selectedValue).hide();
            }
            jQuery(".ticket-select").chosen("destroy").chosen();
        });

        // Selecting a ticket auto-selects its project
        jQuery(".ticket-select").change(function () {
            var selectedValue = jQuery(this).find("option:selected").attr("data-value");
            if (selectedValue) {
                jQuery(".project-select option[value=" + selectedValue + "]").attr("selected", "selected");
                jQuery(".project-select").trigger("liszt:updated");
                jQuery(".ticket-select").chosen("destroy").chosen();
            }
        });

        // Date picker for new entry row
        jQuery("#newEntryDate").datepicker({
            dateFormat: leantime.dateHelper.getFormatFromSettings("dateformat", "jquery"),
            dayNames: leantime.i18n.__("language.dayNames").split(","),
            dayNamesMin: leantime.i18n.__("language.dayNamesMin").split(","),
            dayNamesShort: leantime.i18n.__("language.dayNamesShort").split(","),
            monthNames: leantime.i18n.__("language.monthNames").split(","),
            monthNamesShort: leantime.i18n.__("language.monthNamesShort").split(","),
            currentText: leantime.i18n.__("language.currentText"),
            closeText: leantime.i18n.__("language.closeText"),
            buttonText: leantime.i18n.__("language.buttonText"),
            isRTL: leantime.i18n.__("language.isRTL") === "true" ? 1 : 0,
            nextText: leantime.i18n.__("language.nextText"),
            prevText: leantime.i18n.__("language.prevText"),
            weekHeader: leantime.i18n.__("language.weekHeader"),
            firstDay: 1,
            onSelect: function (dateText, inst) {
                updateNewEntryInputName();
            }
        });

        // Update the hidden name on the new entry hours input when date changes
        function updateNewEntryInputName() {
            var dateVal = jQuery("#newEntryDate").val();
            var kindVal = jQuery(".kind-select").val() || "GENERAL_BILLABLE";

            if (dateVal) {
                var dateObj = jQuery("#newEntryDate").datepicker("getDate");
                var timestamp = Math.floor(dateObj.getTime() / 1000);
                jQuery("#newEntryHours").attr("name", "new|" + kindVal + "|" + dateVal + "|" + timestamp);
            } else {
                jQuery("#newEntryHours").attr("name", "new|" + kindVal + "|false|false");
            }
        }

        // Update name when kind changes
        jQuery(".kind-select").change(function () {
            updateNewEntryInputName();
        });

        // Live total calculation
        function updateTotal() {
            var total = 0;
            jQuery("#myTimesheetList .hourCell").each(function () {
                var val = parseFloat(jQuery(this).val());
                if (!isNaN(val)) {
                    total = Math.round((total + val) * 100) / 100;
                }
            });
            jQuery("#listTotalHours").text(total);
        }

        jQuery("#myTimesheetList").on("change keyup", ".hourCell", function () {
            updateTotal();
        });
    };

    // Make public what you want to have public, everything else is private
    return {
        initTimesheetsTable:initTimesheetsTable,
        initEditTimeModal:initEditTimeModal,
        initMyListEditable:initMyListEditable,
    };
})();
