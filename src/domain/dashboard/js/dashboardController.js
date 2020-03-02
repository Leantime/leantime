leantime.dashboardController = (function () {

    // Variables (underscore for private variables)
    var  chartColors = {
        red: 'rgb(201,48,44)',
        orange: 'rgb(255, 159, 64)',
        yellow: 'rgb(255, 205, 86)',
        green: 'rgb(90,182,90)',
        blue: 'rgb(54, 162, 235)',
        purple: 'rgb(153, 102, 255)',
        grey: 'rgb(201, 203, 207)'
    };

    var _burndownConfig = '';
    var _burndownChart = '';
    var _progressChart = '';

    //Constructor
    (function () {
        jQuery(document).ready(
            function () {
                initTicketTimers();
            });
    })();

    //Functions

    var initProgressChart = function (complete, incomplete ) {
        var config = {
            type: 'doughnut',

            data: {
                datasets: [{
                    data: [
                        complete,
                        incomplete

                    ],
                    backgroundColor: [
                            leantime.dashboardController.chartColors.green,
                            leantime.dashboardController.chartColors.grey,

                        ],
                    label: 'Project Done'
                }],
                labels: [
                            complete+'% Done',
                            incomplete+'% Open'
                        ]
            },
            options: {
                maintainAspectRatio : false,
                responsive: true,

                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false,
                    text: ''
                },
                animation: {
                    animateScale: true,
                    animateRotate: true
                }
            }
        };

        var ctx = document.getElementById('chart-area').getContext('2d');
        _progressChart = new Chart(ctx, config);
    };

    var initBurndown = function (labels, plannedData, actualData) {

        var MONTHS = labels;
        var config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ideal',
                        backgroundColor: leantime.dashboardController.chartColors.blue,
                        borderColor: leantime.dashboardController.chartColors.blue,
                        data: plannedData,
                        fill: false,
                        lineTension: 0,
                },
                    {
                        label: 'Actual',
                        backgroundColor: leantime.dashboardController.chartColors.red,
                        borderColor: leantime.dashboardController.chartColors.red,
                        data: actualData,
                        fill: false,
                        lineTension: 0,
                }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio : false,

                title: {
                    display: false,
                    text: 'Line Chart'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                legend: {
                    position: 'bottom',
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Day of the Sprint'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Effort'
                        },
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        };

        var ctx2 = document.getElementById('sprintBurndown').getContext('2d');
        _burndownChart = new Chart(ctx2, config);

    };

    var initChartButtonClick = function (id, plannedData, actualData) {

        jQuery("#"+id).click(
            function (event) {

                _burndownChart.data.datasets[0].data = plannedData;
                _burndownChart.data.datasets[1].data = actualData;
                _burndownChart.options.scales.yAxes[0].scaleLabel.labelString = "Open To-Dos";
                jQuery("#NumChartButton, #EffortChartButton, #HourlyChartButton").removeClass('active');
                jQuery(this).addClass('active');
                _burndownChart.update();

            }
        );

    };

    var initBacklogBurndown = function (labels, actualData) {

        var MONTHS = labels;
        var config = {
            type: 'line',
            data: {
                labels: labels,
                datasets: [

                    {
                        label: 'Open To-Dos',
                        backgroundColor: leantime.dashboardController.chartColors.red,
                        borderColor: leantime.dashboardController.chartColors.red,
                        data: actualData,
                        fill: false,
                        lineTension: 0,
                        pointRadius:0,

                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio : false,

                title: {
                    display: false,
                    text: 'Line Chart'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                hover: {
                    mode: 'nearest',
                    intersect: true
                },
                legend: {
                    position: 'bottom',
                },
                elements: {
                    point: {
                        pointStyle: "line",
                        radius:"0"
                    }
                },
                scales: {
                    xAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: 'Date'
                        }
                    }],
                    yAxes: [{
                        display: true,
                        scaleLabel: {
                            display: true,
                            labelString: '# of open To-Dos'
                        },
                        ticks: {
                            beginAtZero:true
                        }
                    }]
                }
            }
        };

        var ctx2 = document.getElementById('backlogBurndown').getContext('2d');
        _burndownChart = new Chart(ctx2, config);

    };

    var initBacklogChartButtonClick = function (id, actualData) {

        jQuery("#"+id).click(
            function (event) {

                _burndownChart.data.datasets[0].data = actualData;
                _burndownChart.options.scales.yAxes[0].scaleLabel.labelString = "Open To-Dos";
                jQuery("#NumChartButton, #EffortChartButton, #HourlyChartButton").removeClass('active');
                jQuery(this).addClass('active');
                _burndownChart.update();

            }
        );

    };

    var initTicketTimers = function () {
        jQuery(".punchIn").on(
            "click", function () {

                var ticketId = jQuery(this).attr("value");

                // POST to server using $.post or $.ajax
                jQuery.ajax(
                    {
                        data: "ticketId="+ticketId,
                        type: 'POST',
                        url: leantime.appUrl+'/tickets/showAll&raw=true&punchIn=true'
                    }
                ).done(function(msg){
                    jQuery.jGrowl("Timer started!");
                });
                var currentdate = new Date();

                var datetime = currentdate.getHours() + ":" + currentdate.getMinutes() + " ";

                jQuery(".timerContainer .punchIn").hide();
                jQuery("#timerContainer-"+ticketId+" .punchOut").show();
                jQuery(".timerContainer .working").show();
                jQuery("#timerContainer-"+ticketId+" .working").hide();
                jQuery("#timerContainer-"+ticketId+" span.time").text(datetime);
            }
        );

        jQuery(".punchOut").on(
            "click", function () {

                var ticketId = jQuery(this).attr("value");

                // POST to server using $.post or $.ajax
                jQuery.ajax(
                    {
                        data: "ticketId="+ticketId,
                        type: 'POST',
                        url: leantime.appUrl+'/tickets/showAll&raw=true&punchOut=true',

                    }
                ).done(
                    function (msg) {
                        //This is easier for now and MVP. Later this needs to be refactored to reload the list of tickets async

                        if(msg == 0) {
                            jQuery.jGrowl("You worked less than 6 minutes. Hours not logged");
                        }else{
                            jQuery.jGrowl("You logged "+msg+" hours");
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
        chartColors: chartColors,
        initBurndown: initBurndown,
        initChartButtonClick: initChartButtonClick,
        initBacklogBurndown:initBacklogBurndown,
        initBacklogChartButtonClick:initBacklogChartButtonClick,
        initProgressChart:initProgressChart,
        initTicketTimers:initTicketTimers

    };
})();
