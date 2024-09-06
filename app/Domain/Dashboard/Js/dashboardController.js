import Chart from 'chart.js/auto';
import jQuery from 'jquery';
import i18n from 'i18n';
import { theme } from 'js/app/core/instance-info.module';
import { getFormatFromSettings } from 'js/app/core/dateHelper.module';
import { updateDueDates } from 'domain/Tickets/Js/ticketsRepository';
import moment from 'moment';

export const chartColors = {
    red: 'rgb(201,48,44)',
    orange: 'rgb(255, 159, 64)',
    yellow: 'rgb(255, 205, 86)',
    green: 'rgb(90,182,90)',
    blue: 'rgb(54, 162, 235)',
    purple: 'rgb(153, 102, 255)',
    grey: theme == "dark" ? 'rgb(56, 56, 56)' : 'rgb(201, 203, 207)'
};

let _progressChart = '';

export const prepareHiddenDueDate = function () {
    var thisFriday = moment().startOf('week').add(5, 'days');
    jQuery("#dateToFinish").val(thisFriday.format("YYYY-MM-DD"));
};

export const initProgressChart = function (chartId, complete, incomplete ) {
    var config = {
        type: 'doughnut',

        data: {
            datasets: [{
                data: [
                    complete,
                    incomplete

                ],
                backgroundColor: [
                    chartColors.green,
                    chartColors.grey
                ],
                label: i18n.__("label.project_done")
            }],
            labels: [
                complete + '% Done',
                incomplete + '% Open'
            ]
        },
        options: {
            maintainAspectRatio : false,
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false,
                    text: ''
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    };

    var ctx = document.getElementById(chartId).getContext('2d');
    _progressChart = new Chart(ctx, config);
};

export const initBurndown = function (labels, plannedData, actualData) {

    moment.locale(i18n.__("language.code"));

    var MONTHS = labels;
    var config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: i18n.__("label.ideal"),
                    backgroundColor: chartColors.blue,
                    borderColor: chartColors.blue,
                    data: plannedData,
                    fill: false,
                    lineTension: 0,
            },
                {
                    label: 'Actual',
                    backgroundColor: chartColors.red,
                    borderColor: chartColors.red,
                    data: actualData,
                    fill: false,
                    lineTension: 0,
            }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio : false,


            hover: {
                mode: 'nearest',
                intersect: true
            },

            plugins: {
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false,
                    text: 'Line Chart'
                },
                tooltips: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: i18n.__("label.date"),
                    },
                    type: 'time',
                    time: {
                        unit: 'day'
                    }
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: i18n.__("label.num_tickets")
                    },
                    ticks: {
                        beginAtZero:true
                    }
                }
            }
        }
    };

    var ctx2 = document.getElementById('sprintBurndown').getContext('2d');
    _burndownChart = new Chart(ctx2, config);

    return _burndownChart;

};

export const initChartButtonClick = function (id, label, plannedData, actualData, chart) {
    jQuery("#" + id).click(
        function (event) {
            chart.data.datasets[0].data = plannedData;
            chart.data.datasets[1].data = actualData;
            chart.options.scales.y.title.text = label;
            //chart.options.scales.yAxes[0].scaleLabel.labelString = label;
            jQuery(".chartButtons").removeClass('active');
            jQuery(this).addClass('active');
            chart.update();

        }
    );

};

export const initBacklogBurndown = function (labels, actualData) {
    moment.locale(i18n.__("language.code"));

    var MONTHS = labels;
    var config = {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: i18n.__("label.done_todos"),
                    backgroundColor: chartColors.green,
                    borderColor: chartColors.green,
                    data: actualData.done.data,
                    fill: true,
                    lineTension: 0,
                    pointRadius:0,
                },
                {
                    label: i18n.__("label.progress_todos"),
                    backgroundColor: chartColors.yellow,
                    borderColor: chartColors.yellow,
                    data: actualData.progress.data,
                    fill: true,
                    lineTension: 0,
                    pointRadius:0,
                },
                {
                    label: i18n.__("label.new_todos"),
                    backgroundColor: chartColors.red,
                    borderColor: chartColors.red,
                    data: actualData.open.data,
                    fill: true,
                    lineTension: 0,
                    pointRadius:0,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio : false,
            hover: {
                mode: 'nearest',
                intersect: true
            },
            elements: {
                point: {
                    pointStyle: "line",
                    radius:"0"
                }
            },
            plugins: {
                tooltips: {
                    mode: 'index',
                    intersect: false,
                },
                legend: {
                    position: 'bottom',
                },
                title: {
                    display: false,
                    text: 'Line Chart'
                },
            },
            scales: {
                x: {
                    display: true,
                    title: {
                        display: true,
                        text: i18n.__("label.date"),
                    },
                    type: 'time',
                    time: {
                        unit: 'day'
                    },
                },
                y: {
                    display: true,
                    title: {
                        display: true,
                        text: i18n.__("label.num_tickets")
                    },
                    ticks: {
                        beginAtZero:true
                    },
                    stacked: true
                }
            }
        }
    };

    var ctx2 = document.getElementById('backlogBurndown').getContext('2d');
    _burndownChart = new Chart(ctx2, config);

    return _burndownChart;
};

export const initBacklogChartButtonClick = function (id, actualData, label, chart) {
    jQuery("#" + id).click(
        function (event) {
            chart.data.datasets[0].data = actualData.done.data;

            chart.data.datasets[1].data = actualData.progress.data;
            chart.data.datasets[2].data = actualData.open.data;

            chart.options.scales.y.title.text = label;
            jQuery(".backlogChartButtons").removeClass('active');
            jQuery(this).addClass('active');
            chart.update();
        }
    );
};

export const initDueDateTimePickers = function () {
    jQuery(document).ready(function () {
        jQuery(".duedates").datepicker(
            {
                dateFormat: getFormatFromSettings("dateformat", "jquery"),
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
                onClose: function (date) {
                    var newDate = "";

                    if (date == "") {
                        jQuery(this).val(i18n.__("text.anytime"));
                    }

                    var dateTime = moment(date, i18n.__("language.momentJSDate")).format("YYYY-MM-DD HH:mm:ss");

                    var id = jQuery(this).attr("data-id");
                    newDate = dateTime;

                    updateDueDates(id, newDate, function () {
                        jQuery.growl({message: i18n.__("short_notifications.duedate_updated")});
                    });

                }
            }
        );
    });
};

// Make public what you want to have public, everything else is private
export default {
    chartColors: chartColors,
    initBurndown: initBurndown,
    initChartButtonClick: initChartButtonClick,
    initBacklogBurndown:initBacklogBurndown,
    initBacklogChartButtonClick:initBacklogChartButtonClick,
    initProgressChart:initProgressChart,
    prepareHiddenDueDate:prepareHiddenDueDate,
    initDueDateTimePickers:initDueDateTimePickers
};
