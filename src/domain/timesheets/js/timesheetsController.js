leantime.timesheetsController = (function () {


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
                        url: '/api/timer'
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
                        url: '/api/timer'
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

    // Make public what you want to have public, everything else is private
    return {

    };
})();
