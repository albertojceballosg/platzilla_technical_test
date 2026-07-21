(function (jQuery) {
    //private var
    var dateTable = '',
        chartTitle = '';

    var drawPdf = function (imgUrl,answerId,idView) {
        var arguments = {
                'module':   'answers',
                'action':   'AjaxAnswersUtils',
                'record':    answerId,
                'function': 'SAVE_TEMPLATE',
                'imgpage':  imgUrl,
                'Ajax':     'true'
            } ;

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    window.location.href = 'index.php?module=reportmanager&action=View&Ajax=true&record=' + answerId + '&modulename=answers&idview='+ idView;
                    jQuery('canvas').remove();
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var drawPieChart = function () {
        var data = google.visualization.arrayToDataTable(dateTable);
        var options = {
            title: chartTitle,
            height: 550,
            width:  550,
            legend: 'none',
            is3D: false
        };
        var chart = new google.visualization.PieChart(document.getElementById('piechart_3d'));
        chart.draw(data, options);
    };

    var drawStackedBars = function () {
        var data = google.visualization.arrayToDataTable(dateTable);
        var options = {
            title: chartTitle,
            chartArea: {width: '50%'},
            isStacked: true,
            legend: 'none',
            hAxis: {
                title: 'Importancia',
                minValue: 0
            },
            vAxis: {
                title: 'Áreas'
            }
        };
        var chart = new google.visualization.BarChart(document.getElementById('stacked_bars'));
        chart.draw(data, options);
    };

    //public method
    var exportPdf = function (answerId, questionnaireId, idView) {
        var record = jQuery ('input[name=record]').val (),
            arguments;
        arguments = {
            'module':   'answers',
            'action':   'AjaxAnswersUtils',
            'function': 'VALIDATE_TEMPLATE',
            'Ajax':     'true'
        } ;
        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    var dataURL= {};
                    html2canvas(document.querySelector("#tblProcesamientodelaencuesta")).then(canvas => {
                        document.body.appendChild(canvas);
                        dataURL = canvas.toDataURL();
                        drawPdf (dataURL, answerId, idView);
                    });
                }
            }
            catch (e) {
                alert(e);
            }
        });
    };

    var exportTablePdf = function (answerId, questionnaireId, idView) {
        window.location.href = 'index.php?module=reportmanager&action=View&Ajax=true&record=' + questionnaireId + '&askingforid=' + answerId + '&idview='+ idView;
    };

    var getStackedBars = function (dataChart, gTitle) {
        dateTable = JSON.parse(dataChart);
        chartTitle = gTitle;
        google.charts.load("current", {packages:['corechart', 'bar']});
        google.charts.setOnLoadCallback (drawStackedBars);
    };

    var init = function (dataChart, gTitle) {
        dateTable  = JSON.parse(dataChart);
        chartTitle = gTitle;
        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawPieChart);
    };


    window.ResponseEvaluationUtils = {
        init:           init,
        drawPieChart:   drawPieChart,
        exportPdf:      exportPdf,
        exportTablePdf: exportTablePdf,
        getStackedBars: getStackedBars
    };

    var onDocumentReadyHandler = function () {

    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));