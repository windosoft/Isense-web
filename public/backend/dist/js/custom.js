$('#liveFilterDate').datepicker({
    autoclose: true,
    format: 'dd-mm-yyyy',
    orientation: "bottom auto"
});

function renderDoughnotChart() {
    $.get(adminUrl + '/dashboard/getDoughnotDetails', function (data) {
        var config = {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [
                        data.totalDisable,
                        data.totalSuccess,
                        data.totalWarning,
                        data.totalDanger,
                    ],
                    backgroundColor: [
                        '#666666',
                        '#87c253',
                        '#f7b500',
                        '#d9021f',
                    ],
                    borderWidth: 0,
                    percentageInnerCutout: 50,
                    cutoutPercentage: 50,
                }],
                labels: [
                    'Disabled',
                    'Success',
                    'Warning',
                    'Danger',
                ]
            },
            options: {
                cutoutPercentage: 70,
                legend: {
                    display: false
                },
                borderColor: '#172b47',
                animation: {
                    animateScale: true,
                    animateRotate: true
                },
                backgroundColor: '#172b47',
            }
        };
        var ctx = document.getElementById('chart-area').getContext('2d');
        window.myDoughnut = new Chart(ctx, config);
        /*var chart = new CanvasJS.Chart("canvas-holder", {
            animationEnabled: true,
            backgroundColor:'#172b47',
            data: [{
                type: "doughnut",
                startAngle: 10,
                indexLabelFontSize: 11,
                indexLabel: "{label}",
                indexLabelFontColor:'#FFFFFF',
                toolTipContent: "<b>{label}:</b> {y} (#percent%)",
                dataPoints: [
                    { y: data.totalDisable, label: "Disabled",color:'#666666' },
                    { y: data.totalSuccess, label: "Success",color:'#87c253' },
                    { y: data.totalWarning, label: "Warning",color:'#f7b500' },
                    { y: data.totalDanger, label: "Danger",color:'#d9021f'},
                ]
            }]
        });
        chart.render();*/
    });
}

function renderLineChart() {
    $('.box-Blocker').show();
    var getSelectedMachine = '';
    var getSelectedDate = '';
    $('.machineFilterList').each(function () {
        if ($(this).hasClass('activeMachines')) {
            getSelectedMachine = $(this).data('id');
        }
    });
    $('.dateFilterList').each(function () {
        if ($(this).hasClass('activeDate')) {
            getSelectedDate = $(this).data('id');
        }
    });
    if (getSelectedMachine != '' && getSelectedDate != '') {
        $.post(adminUrl + '/dashboard/getLineChartData', {
            _token: $('meta[name="csrf-token"]').attr('content'),
            getSelectedMachine: getSelectedMachine,
            getSelectedDate: getSelectedDate
        }, function (data) {
            var lineChartDataBlock = JSON.parse(data);

            $('.box-Blocker').hide();
            var lineChartData = lineChartDataBlock.dashboardData;
            var finalDataTemp = [];
            var finalDataHumidity = [];
            if (lineChartData.length > 0) {

                for (var li = 0; li < lineChartData.length; li++) {
                    finalDataTemp.push(parseFloat(lineChartData[li]['temperature_avg']).toFixed(0));
                    finalDataHumidity.push(parseFloat(lineChartData[li]['humidity_avg']).toFixed(0));
                }
                const ShadowLineElement = Chart.elements.Line.extend({
                    draw() {
                        const {ctx} = this._chart
                        const originalStroke = ctx.stroke
                        ctx.stroke = function () {
                            ctx.save()
                            ctx.shadowColor = '#ffffff52'
                            ctx.shadowBlur = 5
                            ctx.shadowOffsetX = 0
                            ctx.shadowOffsetY = 1
                            originalStroke.apply(this, arguments)
                            ctx.restore()
                        }

                        Chart.elements.Line.prototype.draw.apply(this, arguments)

                        ctx.stroke = originalStroke;
                    }
                })

                Chart.defaults.ShadowLine = Chart.defaults.line
                Chart.controllers.ShadowLine = Chart.controllers.line.extend({
                    datasetElementType: ShadowLineElement
                })

                var config = {
                    type: 'ShadowLine',
                    data: {
                        labels: lineChartDataBlock.labels,
                        datasets: [{
                            label: 'Temperature',
                            backgroundColor: '#87c253',
                            fontColor: '#FFFFFF',
                            borderColor: '#87c253',
                            data: finalDataTemp,
                            fill: false,
                        }, {
                            label: 'Humidity',
                            fill: false,
                            backgroundColor: '#fff300',
                            fontColor: '#FFFFFF',
                            borderColor: '#fff300',
                            data: finalDataHumidity,
                        }]
                    },
                    options: {
                        legend: {
                            display: false
                        },
                        responsive: true,
                        title: {
                            display: false,
                        },
                        tooltips: {
                            mode: 'label',
                            intersect: false,
                            enabled: true,
                            display: false,
                            backgroundColor: '#0e1e38',
                            borderColor: '#FFFFFF',
                        },
                        hover: {
                            mode: 'nearest',
                            intersect: false,
                            display: false,
                        },
                        scales: {
                            xAxes: [{
                                gridLines: {
                                    display: false,
                                },
                                ticks: {
                                    fontColor: "#a4a4a4", // this here
                                },
                            }],
                            yAxes: [{
                                gridLines: {
                                    display: true,
                                    borderDash: [8, 4],
                                    color: '#ffffff22'
                                },
                                ticks: {
                                    fontColor: "#a4a4a4", // this here
                                },
                            }],
                        }
                    }
                };
                var ctx = document.getElementById('line-area').getContext('2d');
                window.myLine = new Chart(ctx, config);
                //myLine.destroy();
            } else {
                $('.box-Blocker').hide();
            }
        });
    }
}

function renderLiveTempData() {
    $('#liveFilterModal').modal('hide');
    var dateofLive = $('#liveFilterDate').val();
    $.post(adminUrl + '/dashboard/getLiveHistory', {
        _token: $('meta[name="csrf-token"]').attr('content'),
        dataDate: dateofLive
    }, function (data) {
        var dataFull = JSON.parse(data);
        $('#putLiveTable').html(dataFull.tableData);

        $('#liveHistoryData').DataTable({
            processing: false,
            serverSide: false,
            searching: false,
            iDisplayLength: 10,
            bSortable: false,
            ordering: false,
            bPaginate: true,
            lengthChange: false,
            lengthMenu: [
                [10, 25, 50, 100],
                [10, 25, 50, 100]
            ]
        });

    });

}

function renderNotifications() {
    $.get(adminUrl + '/dashboard/getLiveNotification', function (data) {
        //var dataFull = JSON.parse(data);
        $('#putAlertsHere').html(data);
    });

}

function openLiveFilter() {
    $('#liveFilterModal').modal('show');
}

$('body').on('click', '.machineFilterList', function () {
    if (!$(this).hasClass('activeMachines')) {
        $('.machineFilterList').removeClass('activeMachines');
        $(this).addClass('activeMachines');
        $('#selectedMachine').text($(this).text());
        /*if (myLine) {
            myLine.destroy();
        }*/
        renderLineChart();
    }
});
$('body').on('click', '.dateFilterList', function () {
    if (!$(this).hasClass('activeDate')) {
        $('.dateFilterList').removeClass('activeDate');
        $(this).addClass('activeDate');
        $('#selectedDate').html('<i class="fa fa-calendar"></i> &nbsp;&nbsp;' + $(this).text());
        /*if (myLine != undefined && myLine != '') {
            myLine.destroy();
        }*/
        renderLineChart();
    }
});
