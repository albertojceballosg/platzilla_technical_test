(function (jQuery) {
    // Función para convertir fecha de formato MM-DD-YYYY al formato del usuario
    function convertDateFormat(dateStr, userFormat) {
        if (!dateStr) return '';
        
        // Parsear MM-DD-YYYY
        var parts = dateStr.split('-');
        if (parts.length !== 3) return dateStr;
        
        var month = parts[0];
        var day = parts[1];
        var year = parts[2];
        
        // Convertir al formato del usuario
        switch(userFormat) {
            case 'dd-mm-yyyy':
                return day + '-' + month + '-' + year;
            case 'mm-dd-yyyy':
                return dateStr; // Ya está en este formato
            case 'yyyy-mm-dd':
                return year + '-' + month + '-' + day;
            case 'dd/mm/yyyy':
                return day + '/' + month + '/' + year;
            case 'mm/dd/yyyy':
                return month + '/' + day + '/' + year;
            case 'yyyy/mm/dd':
                return year + '/' + month + '/' + day;
            default:
                return dateStr;
        }
    }

    //public method
       var createReportDate = function (obj, id, event) {
           var ajaxArgs    = {},
               objSelected  = jQuery (obj),
               hiddenObj    = jQuery ('#other-date-input-' + id),
               myDate       = objSelected.parent().parent().find ('input').eq(0),
               otherDate    = jQuery ('#other-date-' + id),
               btnGroup     = jQuery ('#date-group-' + id),
               dateReport   = jQuery ('#report-date-' + id),
               reportedDaysValue = jQuery ('#reported_day-' + id).val(),
               userDateFormat = jQuery ('#user-date-format-' + id).val() || 'yyyy-mm-dd',
               datesDisableRaw = reportedDaysValue ? reportedDaysValue.split(';') : [],
               datesDisable = [],
               url   = 'index.php?module=daily_report&action=EditView&return_module=daily_report&return_action=index&parenttab=&afp=';
       
       // Convertir fechas al formato del usuario
       for (var i = 0; i < datesDisableRaw.length; i++) {
           if (datesDisableRaw[i]) {
               datesDisable.push(convertDateFormat(datesDisableRaw[i], userDateFormat));
           }
       }
       
           if (objSelected.attr('type') === 'text') {
               objSelected.parent().parent().css ('display', 'block');
           } else {
               if (objSelected.attr('data-date') === '') {
                   // Eliminar datepicker existente si lo hay (el método correcto es 'remove', no 'destroy')
                   var existingDp = dateReport.data('datepicker');
                   if (existingDp) { existingDp.remove(); }
                   
                   dateReport.datepicker({
                               format: 'yyyy-mm-dd',
                               language: 'es',
                               weekStart: 1,
                               autoclose: true,
                               todayHighlight: true,
                               beforeShowDay: function(date){
                                   var month = (date.getMonth() + 1);
                                   var day = date.getDate();
                                   var year = date.getFullYear();
                                   var dateStr = (month < 10 ? '0' + month : month) + "-" + (day < 10 ? '0' + day : day) + "-" + year;
                                   var dmy = convertDateFormat(dateStr, userDateFormat);
                                   
                                   if(datesDisable.indexOf(dmy) !== -1){
                                       return { enabled: true, classes: "has-daily-report", tooltip: "Ya existe informe diario" };
                                   }
                                   return { enabled: true };
                               }
                           }).on ('changeDate', function(e) {
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
                   ajaxArgs = {
                       'module':   'Home',
                       'action':   'AjaxHomeUtils',
                       'function': 'CHECK-DAILY-REPORT',
                       'date':     objSelected.attr ('data-date'),
                       'Ajax':     true
                   };
                   jQuery.post ('index.php', ajaxArgs, function (data) {
                       var message;
                       try {
                           if (typeof data === 'string') {
                               message = JSON.parse(data);
                           } else {
                               message = data;
                           }

                           if (typeof message !== 'object' || message === null || message.error === undefined) {
                               throw 'Respuesta inesperada del servidor';
                           }
                           if (message.error !== 'OK') {
                               throw message.error;
                           }
                           if (typeof message.html !== 'object' || message.html === null) {
                               throw 'Respuesta del servidor incompleta';
                           }
                           if (message.html.status === 'Revisado') {
                               otherDate.html ('Otra fecha');
                               alert('Uoops! El informe se encuentra en estado \" Revisado \"');
                           } else {
                               window.location.href = url + '&mode=' + message.html.form + '&record=' + message.html.crmid;
                           }
                       }
                       catch (e) {
                           otherDate.html ('Otra fecha');
                           alert('Error al verificar el informe diario: ' + e);
                       }
                   })
                   .fail(function (jqXHR, textStatus, errorThrown) {
                       otherDate.html ('Otra fecha');
                       alert('Error de comunicación al verificar el informe diario: ' + textStatus + ' - ' + errorThrown);
                   });
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
               ajaxArgs = {
                   'module':   'Home',
                   'action':   'AjaxHomeUtils',
                   'function': 'CHECK-DAILY-REPORT',
                   'date':     dateReport,
                   'Ajax':     true
               };
           objSelected.html ('<i class="fa fa-spinner fa-spin fa-fw"></i> ' + objSelected.html ());
           jQuery.post ('index.php', ajaxArgs, function (data) {
               var message;
               try {
                   if (typeof data === 'string') {
                       message = JSON.parse(data);
                   } else {
                       message = data;
                   }

                   if (typeof message !== 'object' || message === null || message.error === undefined) {
                       throw 'Respuesta inesperada del servidor';
                   }
                   if (message.error !== 'OK') {
                       throw message.error;
                   }
                   if (typeof message.html !== 'object' || message.html === null) {
                       throw 'Respuesta del servidor incompleta';
                   }
                   if (message.html.status === 'Revisado') {
                       objSelected.html(label);
                       alert('Uoops! El informe se encuentra en estado \" Revisado \"');
                   } else {
                       var redirectUrl = url + '&mode=' + message.html.form + '&record=' + message.html.crmid;
                       window.location.href = redirectUrl;
                   }
               }
               catch (e) {
                   objSelected.html(label);
                   alert('Error al verificar el informe diario: ' + e);
               }
           })
           .fail(function (jqXHR, textStatus, errorThrown) {
               objSelected.html(label);
               alert('Error de comunicación al verificar el informe diario: ' + textStatus + ' - ' + errorThrown);
           });
           event.preventDefault();
           event.stopPropagation();
       };

    window.DailyReportNavBar = {
        createReportDate: createReportDate,
        goReportDate:      goReportDate
    };

    jQuery(document).on('ready', function () {

    });
}(jQuery));