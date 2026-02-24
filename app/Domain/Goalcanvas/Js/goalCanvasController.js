import '../../Canvas/Js/canvasController.js';
leantime.goalCanvasController = leantime.canvasController.createController('goal', {
    extras: {
        initProgressChart: function (chartId, complete, incomplete) {
            var config = {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [complete, incomplete],
                        backgroundColor: [
                            leantime.dashboardController.chartColors.green,
                            leantime.dashboardController.chartColors.grey
                        ],
                        label: leantime.i18n.__("label.project_done")
                    }],
                    labels: [complete + '%']
                },
                options: {
                    maintainAspectRatio: true,
                    responsive: true,
                    plugins: {
                        legend: { position: 'none' },
                        title: { display: false, text: 'Complete' }
                    },
                    animation: {
                        animateScale: true,
                        animateRotate: true
                    }
                }
            };
            var ctx = document.getElementById(chartId).getContext('2d');
            new Chart(ctx, config);
        }
    }
});
