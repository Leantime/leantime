import jQuery from 'jquery';
import i18n from 'i18n';
import { appUrl } from 'js/app/core/instance-info.module';
import { chartColors } from 'domain/Dashboard/Js/dashboardController';
import Chart from 'chart.js/auto';

let canvasName = 'goal';

export const setRowHeights = function () {
    var nbRows = 2;
    var rowHeight = jQuery("html").height() - 320 - 20 * nbRows - 25;

    /*
    var firstRowHeight = rowHeight / nbRows;
    jQuery("#firstRow div.contentInner").each(function(){
        if(jQuery(this).height() > firstRowHeight){
            firstRowHeight = jQuery(this).height() + 50;
        }
    });
    jQuery("#firstRow .column .contentInner").css("height", firstRowHeight);

    var secondRowHeight = rowHeight / nbRows;
    jQuery("#secondRow div.contentInner").each(function(){
        if(jQuery(this).height() > secondRowHeight){
            secondRowHeight = jQuery(this).height() + 50;
        }
    });

    jQuery("#secondRow .column .contentInner").css("height", secondRowHeight);

     */
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
                complete + '%',
            ]
        },
        options: {
            maintainAspectRatio : true,
            responsive: true,
            plugins: {
                legend: {
                    position: 'none',
                },
                title: {
                    display: false,
                    text: 'Complete'
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true
            }
        }
    };

    var ctx = document.getElementById(chartId).getContext('2d');
    let _progressChart = new Chart(ctx, config);
};


// Make public what you want to have public, everything else is private
export default {
    setRowHeights: setRowHeights,
    initProgressChart: initProgressChart
};
