(function (jQuery) {

    var getProcessSteps = function (obj, record, e) {
        var btn       = jQuery (obj),
            id        = btn.attr ('rel'),
            processId = btn.attr ('data-processid'),
            container = jQuery ('#process-panel-container-' + id),
            period    = jQuery ('#period-dates-' + id).val (),
            fromDate  = jQuery ('#start-date-' + id).val(),
            toDate    = jQuery ('#end-date-' + id).val(),
            dateFrom  = new Date (fromDate),
            dateTo    = new Date (toDate),
            arguments = {
            'module':   'Home',
            'action':   'AjaxHomeUtils',
            'function': 'GET-PROCESS-STEPS',
            'case':      record,
            'processId': processId,
            'period':    period,
            'fromDate':  fromDate,
            'toDate':    toDate,
            'hometabid': id,
            'Ajax':      true
        };
        if (period === '') {
            alert ('Uoops! seleccione un período.');
            return;
        } else if (period === 'custom') {
            if ((dateFrom.getTime() > dateTo.getTime()) || fromDate === '' || toDate === '' ) {
                alert ('Uoops! error en periodo personalizado');
                return;
            }
        }

        container.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
        jQuery.post ('index.php', arguments, function (data) {
            var message, data;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    container.html ( message.html);
                }
            } catch (e) {
                alert (e);
            }
        });
        e.preventDefault();
        e.stopPropagation();
    }

    var getProcessDetailView = function (obj, e) {
        var btn       = jQuery (obj),
            id        = btn.attr ('data-id'),
            process   = btn.attr('rel'),
            container = jQuery ('#process-panel-container-' + id),
            period    = jQuery ('#period-dates-' + id).val (),
            fromDate  = jQuery ('#start-date-' + id).val(),
            toDate    = jQuery ('#end-date-' + id).val(),
            dateFrom  = new Date (fromDate),
            dateTo    = new Date (toDate),
            arguments = {
                'module':   'Home',
                'action':   'AjaxHomeUtils',
                'function': 'PROCESS-DETAIL-VIEW',
                'process':   process,
                'period':    period,
                'fromDate':  fromDate,
                'toDate':    toDate,
                'hometabid': id,
                'Ajax':      true
            };
        if (period === '') {
            alert ('Uoops! seleccione un período.');
            return;
        } else if (period === 'custom') {
            if ((dateFrom.getTime() > dateTo.getTime()) || fromDate === '' || toDate === '' ) {
                alert ('Uoops! error en periodo personalizado');
                return;
            }
        }

        container.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
        jQuery.post ('index.php', arguments, function (data) {
            var message, data;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    container.html ( message.html);
                }
            } catch (e) {
                alert (e);
            }
        });
        e.preventDefault();
        e.stopPropagation();
    }

    var searchProcessCase = function (obj, id) {
        var btn          = jQuery (obj),
            form         = jQuery ('#process-panel-form-' + id),
            arguments    = form.serialize (),
            argumentArr  = arguments.split ('&'),
            dataTask     = form.serializeArray (),
            container    = jQuery ('#process-panel-container-' + id),
            period       = jQuery ('#period-dates-' + id).val (),
            fromDate     =  jQuery ('#start-date-' + id).val(),
            toDate       =  jQuery ('#end-date-' + id).val(),
            dateFrom     =  new Date (fromDate),
            dateTo       =  new Date (toDate),
            processId    = jQuery ('#quality-process-' + id).val (),
            users        = [];
        btn.attr("disabled", true);

        if (period === '') {
            alert ('Uoops! seleccione un período.');
            btn.removeAttr("disabled");
            return;
        } else if (period === 'custom') {
            if ((dateFrom.getTime() > dateTo.getTime()) || fromDate === '' || toDate === '' ) {
                alert ('Uoops! error en periodo personalizado');
                btn.removeAttr("disabled");
                return;
            }
        }
        if (processId !== undefined) {
            console.log(processId)
            if (processId === '') {
                alert ('Uoops! seleccione un proceso.');
                btn.removeAttr("disabled");
                return;
            }
            if (jQuery ('#users-' + id).val() === '') {
                alert ('Uoops! seleccione al menos un usuario.');
                btn.removeAttr("disabled");
                return;
            }
        }

        container.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
        jQuery.post ('index.php', argumentArr.join ('&'), function (data) {
            var message, data;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    container.html ( message.html);
                    btn.removeAttr ("disabled");
                }
            } catch (e) {
                alert (e);
                btn.removeAttr ("disabled");
            }
        });
    }

    var selectedCase = function (obj, id) {
        var select    = jQuery (obj),
            record    = select.val (),
            processId = select.attr ('data-processid'),
            container = jQuery ('#process-panel-container-' + id),
            period    = jQuery ('#period-dates-' + id).val (),
            fromDate  = jQuery ('#start-date-' + id).val(),
            toDate    = jQuery ('#end-date-' + id).val(),
            dateFrom  = new Date (fromDate),
            dateTo    = new Date (toDate),
            arguments = {
                'module':   'Home',
                'action':   'AjaxHomeUtils',
                'function': 'GET-PROCESS-STEPS',
                'case':      record,
                'processId': processId,
                'period':    period,
                'fromDate':  fromDate,
                'toDate':    toDate,
                'hometabid': id,
                'Ajax':      true
            };
        if (record === '') {
            return;
        }
        if (period === '') {
            alert ('Uoops! seleccione un período.');
            return;
        } else if (period === 'custom') {
            if ((dateFrom.getTime() > dateTo.getTime()) || fromDate === '' || toDate === '' ) {
                alert ('Uoops! error en periodo personalizado');
                return;
            }
        }

        container.html('<img src="themes/images/loading.gif" alt="Loading" class="img-responsive center-block"/>');
        jQuery.post ('index.php', arguments, function (data) {
            var message, data;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    container.html ( message.html);
                }
            } catch (e) {
                alert (e);
            }
        });
    }

    var selectedPeriod = function (obj, id) {
        var period = jQuery (obj).val (),
            customDate = jQuery ('.process-control-date-' + id);
        if (period === 'custom') {
            customDate.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
            customDate.parent ().parent ().removeClass ('hide');
        } else {
            customDate.parent ().parent ().addClass ('hide');
            customDate.val('');
        }

    };

    var selectedProcess = function (obj, id) {}

    var selectedProcessType = function (obj, id) {
        var processType = jQuery (obj),
            typeValue   = processType.attr('rel'),
            process     = jQuery ('#quality-process-' + id),
            ul          = processType.parent().parent();
        ul.find ('li').each(function (index, element) {
            jQuery (element).removeClass('active');
        });
        processType.parent().addClass('active');
        process.find('option').each(function (index, element) {
            var processOp   = jQuery(element),
                processType = processOp.attr('data-type');
            if (processType !== 'BLOCK') {
                if ((processType === typeValue) || (typeValue === '')) {
                    processOp.removeClass('hide');
                } else {
                    processOp.addClass('hide');
                }
            }
        });
    }

    var selectedUser = function (e, obj, id) {
        var allList      = jQuery ('#process-quality-user-' + id + ' li'),
            btn          = jQuery ('#btn-group-user-' + id),
            list         = jQuery (obj).parent(),
            helpText     = jQuery ('#help-user-' + id),
            userId       = jQuery (obj).attr ('rel'),
            users        = jQuery ('#users-' + id),
            faClass      = btn.find('i').eq (0),
            userSelected = [],
            found    = 0, infoText = '';

        if (list.hasClass ('active')) {
            list.removeClass ('active');
        }  else {
            list.addClass ('active');
        }

        faClass.removeClass('fa-user');
        faClass.removeClass('fa-users');
        users.val('');
        allList.each (function() {
            var li = jQuery (this),
                userId = li.children ('a').attr ('rel');
            if (li.hasClass ('active') && userId !== 'undefined') {
                userSelected.push(li.find('a').eq(0).attr('title'));
                found += 1;
                    users.val(users.val() + userId + ',');
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

    window.ProcessCasesUtils = {
        getProcessSteps:      getProcessSteps,
        getProcessDetailView: getProcessDetailView,
        searchProcessCase:    searchProcessCase,
        selectedCase:         selectedCase,
        selectedPeriod:       selectedPeriod,
        selectedProcess:      selectedProcess,
        selectedProcessType:  selectedProcessType,
        selectedUser:         selectedUser
    };

    jQuery(document).on('ready', function () {

    });
}(jQuery));