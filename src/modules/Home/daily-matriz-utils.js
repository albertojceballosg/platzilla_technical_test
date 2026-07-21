(function (jQuery) {
    //private var
    var idActivity = '',
        highHigh   = [],
        lowHigh    = [],
        highLow    = [],
        lowLow     = [],
        index      = 0,
        chartTitle = 'No hay actividades en el período!',
        graphicDiv = '',
        total      = 0,
        totalTime  = 0;

    var drawChart = function () {
        var data = google.visualization.arrayToDataTable([
            ['Tareas', 'Cantidad del periodo'],
            ['Importante-Urgente',     highHigh[index]],
            ['Importante-No urgente',      lowHigh[index]],
            ['No importante-Urgente',  highLow[index]],
            ['No importante-No urgente', lowLow[index]]
        ]);

        var options = {
            //title: chartTitle, titleTextStyle:{width:'100%'},
            height: 180,
            width:  180,
            chartArea:{left:'0%',right:'0%',top:2,width:'75%',height:'100%'},
            legend: 'none',
            slices: [{color:'#ee1e2d'}, {color:'#00a65a'}, {color:'#f3c022'}, {color:'#F0F0F0',textStyle:{color:'000000'}}],
            is3D: true
        };
        if (index === 0) {
            jQuery('#piechart_3d_title').html(chartTitle);
            var chartTask = new google.visualization.PieChart(document.getElementById('piechart_3d'));
            google.visualization.events.addListener(chartTask, 'ready', DailyMatrixUtls.taskHandler);
            chartTask.draw(data, options);
        } else {
            var chartEstimated = new google.visualization.PieChart(document.getElementById('piechart_3d_estimated'));
            //google.visualization.events.addListener(chartTask, 'ready', DailyMatrixUtls.taskHandler);
            chartEstimated.draw(data, options);
            jQuery('#piechart_3d_estimated_title').html(chartTitle);
        }


    };
    //public method
    var createReportDate = function (obj, id, event) {
        var arguments    = {},
            objSelected  = jQuery (obj),
            hiddenObj    = jQuery ('.other-date'),
            myDate       = objSelected.parent().parent().find ('input').eq(0),
            otherDate    = jQuery ('#other-date-' + id),
            btnGroup     = jQuery ('#date-group-' + id),
            dateReport   = jQuery ('#report-date-' + id),
            datesDisable = jQuery('#reported_day-' + id).val().split(';'),
            url          = 'index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp=';
        if (objSelected.attr('type') === 'text') {
            objSelected.parent().parent().css ('display', 'block');
        } else {

            if (objSelected.attr('data-date') === '') {
                dateReport.datepicker (
                        {
                            format: 'yyyy-mm-dd',
                            language: 'es',
                            weekStart: 1,
                            beforeShowDay: function(date){
                                dmy = (date.getMonth() + 1)+ "-" + date.getDate() + "-" + date.getFullYear();
                                if(datesDisable.indexOf(dmy) !== -1){
                                    return false;
                                }
                                else{
                                    return true;
                                }
                            }
                        }
                    );
                dateReport.datepicker ().on ('changeDate', function(e) {
                    objSelected.parent ().parent ().css ('display', 'block');
                    otherDate.attr ('data-date', myDate.val());
                    otherDate.html ('Para el: ' + myDate.val());
                    myDate.val('');
                    e.preventDefault();
                    e.stopPropagation();
                });
                hiddenObj.removeClass('hide');
                myDate.val('');
            } else {
                otherDate.html ('<i class="fa fa-spinner fa-spin fa-fw"></i> ' + otherDate.html ());
                url       = url + btoa (objSelected.attr ('data-date') + '@' + objSelected.attr ('rel'));
                arguments = {
                    'module':   'Home',
                    'action':   'AjaxHomeUtils',
                    'function': 'CHECK-DAILY-REPORT',
                    'date':     objSelected.attr ('data-date'),
                    'Ajax':     true
                };
                jQuery.post ('index.php', arguments, function (data) {
                    var message, data;
                    try {
                        message = JSON.parse(JSON.stringify(data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            if (message.html.status === 'Revisado') {
                                otherDate.html ('Otra fecha');
                                alert('Uoops! El informe se encuentra en estado \" Revisado \"');
                            } else {
                                window.location.href = url + '&mode=' + message.html.form + '&record=' + message.html.crmid;
                            }
                        }
                    }
                    catch (e) {
                        alert(e);
                    }
                })
            }
        }
        event.preventDefault();
        event.stopPropagation();
    };

    var goReportDate = function (obj, id, event) {
        var objSelected = jQuery (obj),
            label       = objSelected.html(),
            dateReport  = objSelected.attr ('data-date'),
            url         = objSelected.attr ('href'),
            arguments = {
                'module':   'Home',
                'action':   'AjaxHomeUtils',
                'function': 'CHECK-DAILY-REPORT',
                'date':     dateReport,
                'Ajax':        true
            };
        objSelected.html ('<i class="fa fa-spinner fa-spin fa-fw"></i> ' + objSelected.html ());
        jQuery.post ('index.php', arguments, function (data) {
            var message, data;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    if (message.html.status === 'Revisado') {
                        objSelected.html(label);
                        alert('Uoops! El informe se encuentra en estado \" Revisado \"');
                    } else {
                        window.location.href = url + '&mode=' + message.html.form + '&record=' + message.html.crmid;
                    }
                }
            }
            catch (e) {
                alert (e);
            }
        });
        event.preventDefault();
        event.stopPropagation();
    };

    var initEstimated = function (hh, lh, hl, ll, t, time) {
        highHigh [1]   = hh;
        lowHigh [1]    = lh;
        highLow [1]    = hl;
        lowLow [1]     = ll;
        total          = t;
        totalTime      = time;
    };

    var initTask = function (id, hh, lh, hl, ll, t) {
        idActivity = id;
        highHigh [0]   = hh;
        lowHigh [0]    = lh;
        highLow [0]    = hl;
        lowLow [0]     = ll;
        if (t) {
            chartTitle =  'Cantidad de actividades del periodo: ' + t;
        }

        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChart);
        jQuery('.daily-matrix-date-' + id ).datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
    };

    var editViewTask = function (e, idTask, id) {
        e.preventDefault ();
    };

    var searchTaskForMatrix = function (obj, id) {
        var btn          = jQuery (obj),
            form         = jQuery ('#daily-matrix-form-' + id),
            arguments    = form.serialize (),
            argumentArr  = arguments.split ('&'),
            dataTask     = form.serializeArray (),
            users        = jQuery ('#daily-matrix-user-' + id + ' li'),
            quadrants    = jQuery ('#daily-matrix-quadrants-' + id),
            period       = jQuery ('#period-dates-' + id).val (),
            fronDate     =  jQuery ('#start-date-' + id).val(),
            toDate       =  jQuery ('#end-date-' + id).val(),
            dateFrom     =  new Date (fronDate),
            dateTo       =  new Date (toDate),
            userSelected = [];
        btn.attr("disabled", true);
        users.each (function() {
            var li = jQuery (this);
            if (li.hasClass('active')) {
                userSelected.push (li.children ('a').attr('rel'));
                dataTask.push({'name':'username','value':li.children ('a').attr ('title')})
            }
        });
        if (userSelected.length === 0) {
            alert ('Uoops! Seleccione al menos un usuario');
            btn.removeAttr("disabled");
            return;
        } else if (period === '') {
            alert ('Uoops! seleccione un período.');
            btn.removeAttr("disabled");
            return;
        } else if (period === 'custom') {
            if ((dateFrom.getTime() > dateTo.getTime()) || fronDate === '' || toDate === '' ) {
                alert ('Uoops! error en periodo personalizado');
                btn.removeAttr("disabled");
                return;
            }
        }

        argumentArr.push ('inviteesid=' + encodeURIComponent (userSelected.join(', ')));
        quadrants.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
        jQuery.post ('index.php', argumentArr.join ('&'), function (data) {
            var message, data;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    quadrants.html ( message.html);
                    btn.removeAttr ("disabled");
                }
            }
            catch (e) {
                alert (e);
                btn.removeAttr ("disabled");
            }
        });

    };

    var selectedPeriod = function (obj, id) {
        var period = jQuery (obj).val (),
            customDate = jQuery ('.daily-matrix-date-' + id);
        if (period === 'custom') {
            customDate.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
            customDate.parent ().parent ().removeClass ('hide');
        } else {
            customDate.parent ().parent ().addClass ('hide');
            customDate.val('');
        }

    };

    var selectedUser = function (e, obj, id) {
        var allList  = jQuery ('#daily-matrix-user-' + id + ' li'),
            btn      = jQuery ('#btn-group-user-' + id),
            list     = jQuery (obj).parent(),
            helpText = jQuery ('#help-user-' + id),
            userId   = jQuery (obj).attr ('rel'),
            faClass  = btn.find('i').eq (0),
            found    = 0, infoText = '';

        if (list.hasClass ('active')) {
            list.removeClass ('active');
        }  else {
            list.addClass ('active');
        }

        faClass.removeClass('fa-user');
        faClass.removeClass('fa-users');
        allList.each (function() {
            var li = jQuery (this),
                userId = li.children ('a').attr ('rel');
            if (li.hasClass ('active') && userId !== 'undefined') {
                userSelected.push(li.find('a').eq(0).attr('title'));
                found += 1;
            }
        });
        if (found === 0) {
            faClass.addClass('fa-user');
            helpText.html('');
        } else if (found === 1) {
            faClass.addClass('fa-user');
            helpText.html('<b>Usuario:</b>&nbsp;' + userSelected.join(','));
        } else {
            faClass.addClass('fa-users');
            helpText.html('<b>Usuarios:</b>&nbsp;' + userSelected.join(','));
        }

        e.preventDefault ();
        //e.stopPropagation ()
    };

    var taskHandler = function () {
        index      = 1;
        chartTitle =  'Actividades con tiempo planificado: ' + total + ' Total horas planificadas: ' + totalTime;
        google.charts.load("current", {packages:["corechart"]});
        google.charts.setOnLoadCallback(drawChart);

    };

    window.DailyMatrixUtls = {
        createReportDate:     createReportDate,
        goReportDate:         goReportDate,
        initEstimated:        initEstimated,
        initTask:             initTask,
        editViewTask:         editViewTask,
        drawChart:            drawChart,
        searchTaskForMatrix:  searchTaskForMatrix,
        selectedPeriod:       selectedPeriod,
        selectedUser:         selectedUser,
        taskHandler:          taskHandler
    };

    var onDocumentReadyHandler = function () {

    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));