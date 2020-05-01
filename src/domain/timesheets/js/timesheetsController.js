leantime.timesheetsController = (function () {
    var closeModal = false;

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                _initTicketTimers();
            });

    })();

    //Functions

    var _initTicketTimers = function () {

        jQuery(".punchIn").on(
            "click", function () {

                var ticketId = jQuery(this).attr("data-value");

                jQuery.ajax(
                    {
                        data:
                        {
                            ticketId : ticketId,
                            action:"start"
                        },
                        type: 'POST',
                        url: leantime.appUrl+'/api/timer'
                    }
                ).done(function(msg){

                    jQuery.jGrowl(leantime.i18n.__("short_notifications.timer_started"));

                });

                var currentdate = moment().format(leantime.i18n.__("language.jstimeformat"));

                jQuery(".timerContainer .punchIn").hide();
                jQuery("#timerContainer-"+ticketId+" .punchOut").show();
                jQuery(".timerContainer .working").show();
                jQuery("#timerContainer-"+ticketId+" .working").hide();
                jQuery("#timerContainer-"+ticketId+" span.time").text(currentdate);

            }
        );

        jQuery(".punchOut").on(
            "click", function () {

                var ticketId = jQuery(this).attr("data-value");

                // POST to server using $.post or $.ajax
                jQuery.ajax(
                    {
                        data:
                            {
                                ticketId : ticketId,
                                action:"stop"
                            },
                        type: 'POST',
                        url: leantime.appUrl+'/api/timer'
                    }
                ).done(
                    function (hoursLogged) {

                        if(hoursLogged == 0) {
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.not_enough_time_logged"));
                        }else{
                            jQuery.jGrowl(leantime.i18n.__("short_notifications.logged_x_hours").replace("%1$s", hoursLogged));
                        }

                    }
                );


                jQuery(".timerContainer .punchIn").show();
                jQuery(".timerContainer .punchOut").hide();
                jQuery(".timerContainer .working").hide();
                jQuery(".timerHeadMenu").hide("slow");

            }
        );
    };

    var initTimesheetsTable = function (groupBy) {

        jQuery(document).ready(function() {

            var size = 100;
            var columnIndex = false;



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
                        exportOptions: {
                            format: {
                                body: function ( data, row, column, node ) {
                                    if( typeof jQuery(node).data('order') !== 'undefined'){
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
            }).container().appendTo(jQuery('#tableButtons'));

            jQuery('#allTimesheetsTable').on( 'column-visibility.dt', function ( e, settings, column, state ) {
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
                beforeShowCont: function() {
                    jQuery(".showDialogOnLoad").show();
                    if(closeModal == true){
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
        initEditTimeModal:initEditTimeModal

    };
})();
