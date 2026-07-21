(function (jQuery) {
    var appcode         = 'all',
        typeofdata        = [],
        fLabels           = [],
        isStart           = false,
        totalFilterGroups = -1,
        createModal       = jQuery('#createAlert'),
        selectOption      = '<option value="">' + alert_arr.LBL_SELECT_ALERT + '</option>';

    // private method
    var getFilterId = function (filterGroup) {
        var filters  = filterGroup.find ('.filter'),
            filterId = 0,
            filter, i;
        if (filters.length > 0) {
            for (i = 0; i < filters.length; i += 1) {
                filter = jQuery (filters [ i ]);
                if (parseInt (filter.attr ('data-id')) < filterId) {
                    filterId = parseInt (filter.attr ('data-id'));
                }
            }
        }
        return (filterId - 1);
    };

    var resetValue = function() {
        var selectReference = jQuery ('#codeElementValue-select'),
            valueReference  = jQuery ('#codeElementValue');

        selectReference.addClass('hide').prop ('disabled', true);
        valueReference.removeClass('hide').prop ('disabled', false);
        valueReference.val('');
        valueReference.removeClass ('input-readonly');
        valueReference.prop('readonly', false);
        valueReference.datepicker ('remove');
    };

    var setLocationAlert = function (card) {
        var isLoad    = card.el.find ('.wizard-input-section'),
            codeType  = card.el.find ('#edit-app').val(),
            idAlert   = card.el.find ('#systemAlertId').val(),
            mode      = card.el.find ('#mode').val(),
            arguments =  {};

        if (isLoad.attr('data-load') === '0') {
            arguments = {
                'module':        'systemalerts',
                'action':        'AjaxSystemAlertsUtils',
                'function':      'LOCATION_ALERT',
                'codeType':      codeType,
                'mode':          mode,
                'systemAlertId': idAlert,
                'Ajax':          'true'
            };
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        isLoad.attr('data-load', '1');
                        isLoad.html((message.html));
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        }
    };

    var setSourceAlert = function (card) {
        var startSection;
        jQuery('.data-section').each(function (i, div) {
            var cardName = jQuery(div).parent();
            if (cardName.attr('data-cardname') === 'start') {
                startSection = cardName;
            }
        });
        var isLoad      = card.el.find ('.wizard-input-section'),
            codeType    = jQuery (startSection).find ('#edit-app').val(),
            codeElement = jQuery (startSection).find ('#cod-type').val(),
            idAlert     = jQuery (startSection).find ('#systemAlertId').val(),
            mode        = jQuery (startSection).find ('#mode').val(),
            scale       = jQuery (startSection).find ('#scaledatarel').val (),
            type,
            arguments =  {};
        if (isLoad.attr('data-load') === '0') {
            type = (codeElement === 'Indicators') ? codeElement : codeType;
            arguments = {
                'module':        'systemalerts',
                'action':        'AjaxSystemAlertsUtils',
                'function':      'SOURCE_ALERT',
                'codeType':      type,
                'mode':          mode,
                'scale':         scale,
                'systemAlertId': idAlert,
                'Ajax':          'true'
            };
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse(JSON.stringify(data));
                    if (message.error !== 'OK') {
                        throw message.error;
                    } else {
                        isLoad.attr('data-load', '1');
                        isLoad.html((message.html));
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        }
    };

    var setFilterAlert = function (card) {
        var idSource = '',
            startSection = '',
            isLoad   = card.el.find ('.wizard-input-section');
        jQuery('.data-section').each(function (i, div) {
            var fieldSet     = jQuery(div).find ('fieldset').eq(0),
                cardName = jQuery(div).parent(),
                fieldSetName = fieldSet.attr('name');
           if (fieldSetName === 'alert-source') {
               idSource = fieldSet.attr('id');
           } else if (cardName.attr('data-cardname') === 'start') {
               startSection = cardName;
           }
        });
        if (idSource === '') {
           isLoad.html('<div class="alert alert-danger"><strong>¡Error! </strong>Fuente no encontrada</div>');
           return false;
        }
        var codeType   = jQuery ('#codetype-' + idSource).val (),
            scale      = jQuery ('#scale-' + idSource).val (),
            element    = jQuery ('#codeElement-' + idSource),
            moduleName = jQuery ('option:selected', element).attr ('tabname'),
            idAlert    = jQuery (startSection).find ('#systemAlertId').val(),
            mode       = jQuery (startSection).find ('#mode').val(),
            arguments  =  {};
        if (codeType === 'Indicators') {
            isLoad.html('<div class="alert alert-info"><strong>¡Atento! </strong>Los indicadores ya tienen sus propias condiciones</div>');
        } else {
            if (isLoad.attr('data-load') === '0') {
                arguments = {
                    'module':        'systemalerts',
                    'action':        'AjaxSystemAlertsUtils',
                    'function':      'FILTER_ALERT',
                    'codeType':      codeType,
                    'mode':          mode,
                    'systemAlertId': idAlert,
                    'flmodule':      moduleName,
                    'Ajax':          'true'
                };
                jQuery.post('index.php', arguments, function (data) {
                    var message;
                    try {
                        message = JSON.parse(JSON.stringify(data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            isLoad.attr('data-load', '1');
                            isLoad.html((message.html));
                        }
                    }
                    catch (e) {
                        alert(e);
                    }
                });
            }
        }
    };

    var trimfValues = function  (value) {
        var string_array;
        if (value === '') {
            return '';
        }
        string_array = value.split (":");
        return string_array[ 4 ];
    };

    var validateSearch = function () {
        var from      = jQuery ('#date_from').val ().split ('-'),
            to        = jQuery ('#date_to').val ().split ('-'),
            dateStart = new Date (from[ 0 ], (from[ 1 ] - 1), from[ 2 ]),
            dateEnd   = new Date (to[ 0 ], (to[ 1 ] - 1), to[ 2 ]);

        if (dateStart > dateEnd) {
            alert (alert_arr.INVALID_DATES_SEARCH);
            return false;
        }
        return true;
    };

    var validateAddAlert = function () {
        var alertType   = jQuery ('#codetype'),
            alertModule = jQuery ('#codeElement'),
            flModule    = jQuery ('#flmodule'),
            flModuleLabel = jQuery ('#flmodulelabel');

        if (jQuery ('#appAlertNew').is (":visible") && jQuery ('#codeApp').val () == '') {
            alert (alert_arr.SELECT_ALERT_TYPE);
            jQuery ('#codeApp').focus ();
            return false;
        }
        if (jQuery ('#titleAlert').val () == '') {
            alert (alert_arr.SELECT_ALERT_TITLE);
            jQuery ('#titleAlert').focus ();
            return false;
        }
        if (alertType.val () == '') {
            alert (alert_arr.SELECT_ALERT_TYPEALERT);
            alertType.focus ();
            return false;
        }
        if (jQuery ('#periodAlert').is (":visible") && jQuery ('#scale').val () == '') {
            alert (alert_arr.SELECT_ALERT_SCALE);
            jQuery ('#scale').focus ();
            return false;
        }
        if (alertModule.is (":visible") && alertModule.val () === '') {
            alert (alert_arr.SELECT_ALERT_ELEMENT);
            alertModule.focus ();
            return false;
        } else if (alertModule.is (":visible") && alertModule.val () !== '') {
            flModule.val (jQuery('option:selected', alertModule).attr ('tabname'));
            flModuleLabel.val(jQuery('option:selected', alertModule).attr ('tablabel'));
        }

        if (jQuery ('#appAlertElementField').is (":visible") && jQuery ('#codeElementField').val () == '') {
            alert (alert_arr.SELECT_ALERT_FIELD);
            jQuery ('#codeElementField').focus ();
            return false;
        }
        if (jQuery ('#codeElementOperator').val () === '') {
            alert (alert_arr.SELECT_ALERT_OPERATOR);
            jQuery ('#codeElementOperator').focus ();
            return false;
        }
        if (!jQuery ('#codeElementValue').hasClass('hide')) {
            if (jQuery('#codeElementValue').val() === '') {
                alert(alert_arr.SELECT_ALERT_VALUE);
                jQuery('#codeElementValue').focus();
                return false;
            }
        } else {
            if (jQuery('#codeElementValue-select').val() === '') {
                alert(alert_arr.SELECT_ALERT_VALUE);
                jQuery('#codeElementValue-select').focus();
                return false;
            }
        }
        return true;
    };

    //Wizard method
    var destroyWizard = function () {
        wizard = null;
        // window.location.reload ();
    };

    var submitWizard = function (wizard) {
        jQuery.ajax ('index.php', {
            data:     wizard.serialize (),
            dataType: 'json',
            method:   'post'
        }).done (function () {
            wizard.submitSuccess ();
            wizard.hideButtons ();
        }).fail (function (jQueryResponse) {
            wizard.el.find ('.wizard-failure .message').text (jQueryResponse.responseJSON);
            wizard.submitFailure ();
            wizard.hideButtons ();
        });
    };

    var updateProgressBar = function () {
        var cards      = wizard.cards,
            activeCard = wizard.getActiveCard (),
            index      = 0,
            cardName;
        for (cardName in cards) {
            if (cardName === activeCard.name) {
                break;
            }
            index += 1;
        }
        wizard.updateProgressBar ((index * 100) / Object.keys (wizard.cards).length);
    };

    var validateField = function(field) {
        var isValidate   = true,
            value        = field.val (),
            elementTitle = field.attr('title');

        if ((value === null) || (value === undefined) || (value.trim () === '')) {
            field.parent ().addClass ('has-error');
            if (field.parent ().find ('.help-block').length) {
                field.parent ().find ('.help-block').html (elementTitle + ' es requerido');
            } else {
                field.parent ().parent ().find ('.help-block').html (elementTitle + ' es requerido');
            }
            isValidate = false;
        }
        return isValidate;
    };

    var validateLocation = function (card) {
        var isValidate = true,
            codeType   = card.el.find ('#edit-app'),
            area       = card.el.find ('#codeApp'),
            alertTitle =  card.el.find ('#titleAlert');

        card.el.find ('span[id ^= sp-]').html ('');
        card.el.find ('div[id ^= div-]').removeClass ('has-error');
        [area, alertTitle].each(function (element, i) {
            var result = validateField(element);
            if (isValidate) {
                isValidate = result;
            }
        });
        if (isValidate) {
            codeType.val (area.val ())
        }
        return isValidate;
    };

    var validateSource = function (card) {
        var id           = card.el.find ('fieldset').eq(0).attr('id'),
            isValidate   = true,
            fields       = [],
            divCodeType  = jQuery ('#dv-appAlertType-' + id),
            codeType     = jQuery ('#codetype-' + id),
            divScale     = jQuery ('#dv-periodAlert-' + id),
            scale        = jQuery ('#scale-' + id),
            divElement   = jQuery ('#dv-appAlertElement-' + id),
            element      = jQuery ('#codeElement-' + id),
            startSection;

        card.el.find ('span[id ^= sp-]').html ('');
        card.el.find ('div[id ^= dv-]').removeClass ('has-error');

        if (codeType.val () === '') {
            fields = [codeType]
        } else if (codeType.val () === 'Indicators') {
            fields = [codeType, scale, element];
        } else {
            fields = [codeType, element];
        }
        fields.each(function (element, i) {
            var result = validateField(element);
            if (isValidate) {
                isValidate = result;
            }
        });
        if (isValidate) {
            jQuery('.data-section').each(function (i, div) {
                var cardName = jQuery(div).parent();
                if (cardName.attr('data-cardname') === 'start') {
                    startSection = cardName;
                }
            });
            var  moduleName  = jQuery (startSection).find ('#flmodule'),
                moduleLabel  = jQuery (startSection).find ('#flmodulelabel'),
                dataRel      = jQuery (startSection).find ('#datarel'),
                bxDataRel    = jQuery (startSection).find ('#bxdatarel'),
                boxScoreId   = jQuery (startSection).find ('#boxscoreid'),
                scaleDataRel = jQuery (startSection).find ('#scaledatarel');
            if ((codeType.val() === 'Task_object_no_cump') || (codeType.val() === 'Task_prog')) {
                moduleName.val(jQuery('option:selected', element).attr('tabname'));
                moduleLabel.val(jQuery('option:selected', element).attr('tablabel'));
                dataRel.val ('');
                bxDataRel.val ('');
                boxScoreId.val ('');
                scaleDataRel.val ('');
            } else {
                dataRel.val (jQuery('option:selected', element).attr('datarel'));
                bxDataRel.val (jQuery('option:selected', element).attr('bxdatarel'));
                boxScoreId.val (element.val());
                scaleDataRel.val (jQuery('option:selected', element).attr('scaledatarel'));
                moduleName.val('');
                moduleLabel.val('');
            }
        } else {
            dataRel.val ('');
            bxDataRel.val ('');
            boxScoreId.val ('');
            scaleDataRel.val ('');
            moduleName.val('');
            moduleLabel.val('');
        }
        return isValidate
    };

    var validateFilter = function (card) {
        var fieldSetFilter = card.el.find ('fieldset'),
            filterGroup    = fieldSetFilter.find('ul'),
            isValidate     = true,
            alertType, startSection,
            sourceId = '';

        jQuery('.data-section').each(function (i, div) {
            var cardName = jQuery(div).parent();
            if (cardName.attr('data-cardname') === 'step-1') {
                sourceId = jQuery(div).find ('fieldset').attr('id');
            } else if (cardName.attr('data-cardname') === 'start') {
                startSection = cardName;
            }
        });

        if (sourceId === '') {
            alert('Fuente de la alerta no encontrada!');
            return false
        }
        alertType = jQuery('#codetype-' + sourceId).val();
        if (alertType === 'Indicators') {
            return true;
        } else if (filterGroup.length === 0) {
            alert('Uoops! debe incluir alguna condición');
            return false;
        } else {
            card.el.find ('span[id ^= sp-]').html ('');
            card.el.find ('div[id ^= div-]').removeClass ('has-error');
            filterGroup.each(function (i, ul) {
               jQuery(ul).find('li').each (function (k, li) {
                   var result,
                       field       = jQuery (li).find ('select').eq (0),
                       operator    = jQuery (li).find ('select').eq (1),
                       inputValue  = jQuery (li).find ('input').eq (0),
                       selectValue = jQuery (li).find ('select').eq (2);
                   result = validateField (field);
                   if (isValidate) {
                       isValidate = result;
                   }
                   result = validateField (operator);
                   if (isValidate) {
                       isValidate = result;
                   }
                   if (inputValue.hasClass('hide')) {
                       result = validateField (selectValue);
                       if (isValidate) {
                           isValidate = result;
                       }
                   } else {
                       result = validateField (inputValue);
                       if (isValidate) {
                           isValidate = result;
                       }
                   }
               })

            })
        }
        return isValidate;
    };

    //public method
    var addFilter = function (buttonElement) {
        var group              = jQuery (buttonElement).closest ('.filter-group'),
            groupId            = group.attr ('data-id'),
            filters            = group.find ('.filters'),
            filterId           = getFilterId (group),
            filterTemplateHtml = jQuery ('#system-alert-filter-template').html ().replace (/__GROUP_ID__/g, groupId).replace (/__FILTER_ID__/g, filterId),
            filterTemplate     = jQuery (filterTemplateHtml);

        filters.find ('.operator:last').removeClass ('hidden').removeAttr ('disabled');
        filters.append (filterTemplate);
    };

    var addFilterGroup = function (id, groupId) {
        var filterGroups        = jQuery ('.filter-groups'),
            key                 = groupId ? groupId : totalFilterGroups,
            filterGroupTemplate = jQuery (jQuery ('#system-alert-filter-group-template').html ().replace (/__GROUP_ID__/g, key)),
            filterTemplateHtml  = jQuery ('#system-alert-filter-template').html ().replace (/__GROUP_ID__/g, key).replace (/__FILTER_ID__/g, -1),
            filterTemplate      = jQuery (filterTemplateHtml);

        filterGroupTemplate.find ('.filters').append (filterTemplate);
        filterGroups.find ('.filter-group-operator:last > .operator').removeClass ('hidden').removeAttr ('disabled');
        filterGroups.append (filterGroupTemplate);
        if (!groupId) {
            totalFilterGroups -= 1;
        }
    };

    var changeStatus = function (obj, idAlert) {
        var btn      = jQuery    (obj),
            myStatus = parseInt (btn.attr('data-status')),
            arguments = {
                'module':   'systemalerts',
                'action':   'AjaxSystemAlertsUtils',
                'function': 'CHANGE_STATUS',
                'record':   idAlert,
                'status':   myStatus,
                'Ajax':     'true'
            };
        jQuery.post('index.php', arguments, function (data) {
            var message, viewIndicators = jQuery ('#viewIndicators');
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    if (myStatus === 1) {
                        myStatus = 0;
                        btn.html ('<i class="fa fa-square-o" aria-hidden="true"></i>')
                    } else {
                        myStatus = 1;
                        btn.html ('<i class="fa fa-check-square-o" aria-hidden="true"></i>')
                    }
                    btn.attr('data-status', myStatus);
                }
            }
            catch (e) {
                alert(e);
            }
        });

    };

    var createAlert = function (mode, codeType, systemAlertId){
        var view      = jQuery ('#viewPeriod').val (),
            app       = jQuery ('#app').val (),
            modal     = jQuery ('#createAlert'),
            arguments = {
                'module':        'systemalerts',
                'action':        'CreateAlertIndicator',
                'app':            appcode,
                'viewPeriod':     view,
                'mode':           mode,
                'codeType':       codeType,
                'systemAlertId': systemAlertId,
                'Ajax':          'true'
            };

        jQuery.post('index.php', arguments, function (data) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    modal.addClass ('md-show');
                    modal.html((message.html));
                    jQuery ('#codeApp').val (appcode);
                }
            }
            catch (e) {
                alert(e);
                sendButton.removeAttr('disabled');
            }
        });
    };

    var closeAlertWizard = function () {
        if (wizard) {
            wizard.reset ().close ().trigger ('closed');
            window.location.reload ();
        }
    };

    var detailViewAlert = function (systemAlertId, viewSearch, app, sourceAlert) {
        var from      = jQuery ('#date_from').val (),
            to        = jQuery ('#date_to').val (),
            arguments = {
                'module':     'systemalerts',
                'action':     'AjaxSystemAlertsUtils',
                'function':   'VIEW_ALERTS',
                'app':         app,
                'record':      systemAlertId,
                'date_from':   from,
                'date_to':     to,
                'sourceAlert': sourceAlert,
                'viewScale':   viewSearch,
                'Ajax':       'true'
            };
        jQuery ('.md-overlay').css ({ opacity: 1, visibility: 'visible' });
        jQuery.post('index.php', arguments, function (data) {
            var message, viewIndicators = jQuery ('#viewIndicators');
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    viewIndicators.html (message.html);
                    viewIndicators.addClass ('md-show');
                }
            }
            catch (e) {
                alert(e);
                //sendButton.removeAttr('disabled');
            }
        });
    };

    var deleteAlert = function (systemAlertId, codeType) {
        if (!confirm (alert_arr.MESS_DELETE_ALERT)) {
            return false;
        }
        var arguments = {
            'module':   'systemalerts',
            'action':   'AjaxSystemAlertsUtils',
            'function': 'DELETE_ALERTS',
            'record':   systemAlertId,
            'codeType': codeType,
            'delete':   'true',
            'Ajax':     'true'
        };
        jQuery.post('index.php', arguments, function (data) {
            var message, row = jQuery ('#row-' + systemAlertId);
            try {
                message = JSON.parse (JSON.stringify (data));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    row.fadeOut (
                        function () {
                            row.remove ();
                        }
                    );
                }
            }
            catch (e) {
                alert(e);
                //sendButton.removeAttr('disabled');
            }
        });
    };

    var deleteFilter = function (buttonElement) {
        var button = jQuery (buttonElement),
            group  = button.parent().parent().parent().parent(),
            filter = button.closest ('.filter');
        if (!confirm ('¿Estás seguro de borrar el filtro seleccionado?')) {
            return;
        }

        filter.remove ();
        group.find ('.operator:last').addClass ('hidden').attr ('disabled', 'disabled');
    };

    var deleteFilterGroup = function (buttonElement) {
        var group = jQuery (buttonElement).closest ('.filter-group-container');
        if (!confirm ('¿Estás seguro de borrar el grupo de filtros seleccionado?')) {
            return;
        }
        group.remove ();
        jQuery ('.filter-groups').find ('.filter-group-operator:last > .operator').addClass ('hidden').attr ('disabled', 'disabled');
    };

    var loadAlerts = function (obj, id) {
        var arguments       = {},
            btnGroup        = jQuery (obj).parent(),
            codeApplication = obj.id.split ('--')[ 1 ],
            date            = new Date (),
            first           = new Date (date.getFullYear (), date.getMonth (), 1),
            last            = new Date (date.getFullYear (), date.getMonth () + 1, 0),
            newBlock        = jQuery ('#newblock').val (),
            myTab           =  jQuery ('#tab-' + codeApplication),
            viewSelect      = jQuery ('#dinamicViewScale').val (),
            viewPeriod      = jQuery ('#viewPeriod').val (),
            dateFrom        = jQuery ('#date_from').val (),
            dateTo          = jQuery ('#date_to').val (),
            d               = '',
            m               = '';

        appcode = codeApplication;
        console.log(appcode);
        if (first.getDate () < 10) {
            d = '0' + first.getDate ();
        } else {
            d = first.getDate ();
        }
        if ((first.getMonth () + 1) < 10) {
            m = '0' + (first.getMonth () + 1);
        } else {
            m = (first.getMonth () + 1);
        }
        var from = first.getFullYear () + "-" + m + "-" + d;
        if (last.getDate () < 10) {
            d = '0' + last.getDate ();
        } else {
            d = last.getDate ();
        }
        if ((last.getMonth () + 1) < 10) {
            m = '0' + (last.getMonth () + 1);
        } else {
            m = (last.getMonth () + 1);
        }
        var to = last.getFullYear () + "-" + m + "-" + d;

        if (viewSelect === '' && viewPeriod === '') {
            viewSelect = 'Month';
        } else {
            if (newBlock === 'reload') {
                viewSelect = viewPeriod;
                from       = dateFrom;
                to         = dateTo;
            } else {
                viewSelect = 'Month';
            }
        }

        if (newBlock === 'reload' || (obj.className.indexOf ('active') == -1)) {
            jQuery ('div .loadAlertstabs').html ('');
            btnGroup.find('a').each(function (i, item) {
                if(jQuery(item).attr('id') === jQuery(obj).attr('id')) {
                    jQuery(item).removeClass('btn-default');
                    jQuery(item).addClass('btn-primary');
                } else {
                    jQuery(item).addClass('btn-default');
                    jQuery(item).removeClass('btn-primary');
                }
            });
            myTab.html('<img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />')
            arguments = {
                'module':     'systemalerts',
                'action':     'AjaxSystemAlertsUtils',
                'app':        codeApplication,
                'function':   'LOAD_ALERTS',
                'date_from':  from,
                'date_to':    to,
                'viewPeriod': viewSelect,
                'idView':     id,
                'Ajax':       'true'
            };
            jQuery.post('index.php', arguments, function (data) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (data));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        myTab.html ('');
                        myTab.html (message.html);

                        jQuery ('#newblock').val ('');
                        jQuery ('#dinamicViewScale').val ('');

                        var view = jQuery ('#viewScale');
                        if (view.val () === '') {
                            view.val ('Month');
                        }

                        jQuery ('#date_from').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
                        jQuery ('#date_to').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
                    }
                }
                catch (e) {
                    alert(e);
                    //sendButton.removeAttr('disabled');
                }
            });
        }
    };

    var lookAlert = function (obj) {
        var button = jQuery(obj),
            table  = button.attr('data-table'),
            view   = button.attr('data-view'),
            fa     = '<i class="fa fa-eye"></i>&nbsp;',
            label  = '';
        console.log(view);
        if (view === 'all') {
            button.attr('data-view', 'alert');
            label = 'Todas las alertas'
            jQuery(table + ' tr').each(function (index, tr) {
                var num = parseInt (jQuery(tr).attr('data-alerts-occurrences'));
                if(num === 0) {
                    jQuery(tr).addClass('hide');
                }
            })
        } else {
            button.attr('data-view', 'all');
            label = 'Alertas pendientes'
            jQuery(table + ' tr').each(function (index, tr) {
                var num = parseInt (jQuery(tr).attr('data-alerts-occurrences'));
                if(jQuery(tr).hasClass('hide')) {
                    jQuery(tr).removeClass('hide');
                }
            })
        }
        button.html(fa + label);
    };

    var saveAlerts = function (id) {
        var arguments,
            form        = jQuery ('#system-alert-' + id),
            codeType    = jQuery ('#codetype').val(),
            codeElement = jQuery ('#codeElement');
        if (validateAddAlert()) {
            if (codeType === 'Indicators') {
                var datarel      = jQuery ('option:selected', codeElement).attr ('datarel'),
                    scale        = jQuery ('option:selected', codeElement).attr ('scale'),
                    bxdatarel    = jQuery ('option:selected', codeElement).attr ('bxdatarel'),
                    boxscoreid   = jQuery ('option:selected', codeElement).attr ('boxscoreid'),
                    scaledatarel = jQuery ('option:selected', codeElement).attr ('scaledatarel');

                arguments = form.serialize() + '&boxscoreid=' + boxscoreid + '&datarel=' + datarel + '&scale=' + scale + '&bxdatarel=' + bxdatarel + '&scaledatarel=' + scaledatarel;
            } else {
                arguments = form.serialize();
            }
            jQuery.post('index.php', arguments, function (response) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (response));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        alert (message.html);
                        window.location.reload ();
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        } else {
            return false
        }
    };

    var searchAlerts = function () {
        if (validateSearch ()) {
            var appcode = jQuery ('#app').val (),
                view    = jQuery ('#viewPeriod').val (),
                obj     = jQuery ('#li--' + appcode);

            jQuery ('#newblock').val ('reload');
            jQuery ('#dinamicViewScale').val (view);
            obj.click ();

        } else {
            return false;
        }
    };

    var searchAlertsDate = function (obj) {
        var from  = jQuery ('#date_from'),
            to    = jQuery ('#date_to');
        if (jQuery (obj).val () === '') {
            return
        }
        from.val (jQuery (obj).val());
        searchAlerts();
    };

    var selectApp = function (obj) {
        jQuery('.data-section').each(function (i, div) {
           if (jQuery(div).parent().attr('data-cardname') !== 'start') {
               jQuery(div).attr('data-load', '0');
               jQuery(div).html('<img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />')
           }
        })
    };

    var selectAlertType = function (obj, id) {
        var arguments       = {},
            codeApp         = jQuery ('#codeApp').val (),
            type            = jQuery (obj),
            typeSelected    = type.val(),
            title           = jQuery ('option:selected', type).text(),
            idAlert         = jQuery('#systemAlertId-' + id).val (),
            optionTitle     = jQuery('#codeElement-title-' + id),
            divElement      = jQuery ('#dv-appAlertElement-' + id),
            alertElement    = jQuery ('#codeElement-' + id),
            indicatorPeriod = jQuery ('#dv-periodAlert-' + id),
            period          = jQuery ('#scale-' + id);

        jQuery('.data-section').each(function (i, div) {
            if (jQuery(div).parent().attr('data-cardname') === 'step-2') {
                jQuery(div).attr('data-load', '0');
                jQuery(div).html('<img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />')
            }
        });
        console.log( jQuery ('#codeApp'));
        console.log(codeApp)
        if (typeSelected === 'Indicators' && period.val() === '' && obj !== ('#codetype-' + id)) {
            alertElement.val ('');
            divElement.hide();
            indicatorPeriod.show();
        } else if ((typeSelected !== '')) {
            arguments = {
                'module':        'systemalerts',
                'action':        'AjaxSystemAlertsUtils',
                'function':      'PARAM_FIELD_ELEMENTS',
                'appSelect':     codeApp,
                'type':          type.val(),
                'systemAlertId': idAlert,
                'viewPeriod':    period.val(),
                'Ajax':          'true'
            };

            jQuery.post('index.php', arguments, function (response) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (response));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        var data = message.html;
                        //Module or task
                        alertElement.empty();
                        alertElement.html (selectOption);
                        if ((typeSelected !== 'Indicators') && (typeSelected !== '')) {
                            period.val('');
                            indicatorPeriod.hide();
                        optionTitle.html((title === 'Tareas') ? '(' + title + ') Modulo' : title);
                        for (var j = 0; j < data.length; j++) {
                            alertElement.append(
                                jQuery('<option>', {
                                        value: data[j].tabid,
                                        text: data[j].tablabel
                                    }
                                ).attr('tabname', data[j].name).attr('tablabel', data[j].tablabel)//.attr('selected', (mode === 'edit' && data[j].fieldName === elementField))
                            );
                        }
                        divElement.show()
                        // indicator
                        } else if (typeSelected === 'Indicators') {
                            alertElement.empty();
                            alertElement.html (selectOption);
                            for (var j = 0; j < data.length; j++) {
                                alertElement.append(
                                    jQuery('<option>', {
                                            value: data[j].box_score_dataid,
                                            text: data[j].box_score
                                        }
                                    ).attr('datarel', data[j].datarel).attr('scale', data[j].scale).attr('bxdatarel', data[j].bxdatarel).attr('scaledatarel', data[j].scaledatarel)//.attr('selected', (mode === 'edit' && data[j].fieldName === elementField))
                                );
                            }
                            optionTitle.html(title);
                            divElement.show()
                        }
                    }
                }
                catch (e) {
                    alert(e);
                }
            });
        } else {
            alertElement.val ('');
            divElement.hide();
            period.val('');
            indicatorPeriod.hide();
        }

    };

    var selectAlertModule = function (obj, id) {
        jQuery('.data-section').each(function (i, div) {
            if (jQuery(div).parent().attr('data-cardname') === 'step-2') {
                jQuery(div).attr('data-load', '0');
                jQuery(div).html('<img id="loading-graphic"  src="themes/images/loading.gif" alt="Loading" style="padding 0!important;" class="img-responsive center-block" />')
            }
        });
    };

    var selectElement = function (obj, access, mode, elementField, valoperator, tabName) {
        var arguments = {},
            functionAction   = 'CODE_ELEMENT_FIELD',
            codeElementField = jQuery ('#codeElementField'),
            type             = jQuery ('#codetype').val (),
            tab              = (access === '1') ? tabName : jQuery ('option:selected', obj).attr ('tabname'),
            tabId            = jQuery ('#codeElement').val (),
            title            = jQuery ('#codeElementField-title'),
            taskList         = jQuery ('#taskalert');

        if (type !== 'Indicators' && jQuery(obj).val () !== '') {
            if (type === 'Task_prog') {
                functionAction = 'VIEW-TASK';
                title.html('Campos de las tareas');
            } else {
                title.html('Campos del módulo')
            }
            arguments = {
                'module':   'systemalerts',
                'action':   'AjaxSystemAlertsUtils',
                'function': functionAction,
                'tabname':  tab,
                'tabid':    tabId,
                'Ajax':     'true'
            };

            jQuery.post('index.php', arguments, function (response) {
                var message;
                try {
                    message = JSON.parse (JSON.stringify (response));
                    if(message.error !== 'OK') {
                        throw message.error;
                    } else {
                        var data             = message.html,
                            divElementField  = jQuery ('#appAlertElementField'),
                            selected         = '';
                        codeElementField.empty();
                        codeElementField.html(selectOption);
                            for (var j = 0; j < data.length; j++) {
                                codeElementField.append(
                                    jQuery('<option>', {
                                            value: data[j].fieldName,
                                            text: data[j].fieldLabel
                                        }
                                    ).attr('data-type', data[j].fieldType).attr('data-uitype', data[j].uiType).attr('selected', (mode === 'edit' && data[j].fieldName === elementField))
                                );
                            }
                            divElementField.show();
                    }
                }
                catch (e) {
                    alert(e);
                    //sendButton.removeAttr('disabled');
                }
            });
        } else {
            codeElementField.empty ();
            codeElementField.html (selectOption);
        }
    };

    var selectElementField = function (obj, id) {
        var filterField                 = jQuery (obj),
            selectedFilterField         = filterField.find ('option:selected'),
            selectedFilterFieldDataType = selectedFilterField.attr ('data-type'),
            uiType                      = selectedFilterField.attr ('data-uitype'),
            selectedFilterTypeOfData    = selectedFilterField.attr ('data-field-type'),
            selectedDataType            = selectedFilterFieldDataType ? selectedFilterFieldDataType : 'TEXT',
            row                         = filterField.parent().parent(),
            selectReference             = row.find('select').eq(2),
            valueReference              = row.find('input').eq(0),
            comparator                  = filterField.closest ('.filter').find ('.comparator'),
            options                     = comparator.find ('option'),
            dateFieldOption             = jQuery('#system-alert-date-field-template'),
            functionAction;

        options.each (function (index, optionElement) {
            var option   = jQuery (optionElement),
                dataType = option.attr ('data-type');
            option.prop ('selected', false).hide ();
            uiType = parseInt(uiType);
            if ((jQuery.inArray (uiType, [5, 6, 53]) !== -1)) {
                if (jQuery.inArray (jQuery(option).val(), ['EQUALS', 'LESS', 'GREATER']) !== -1) {
                    option.show ();
                }
            } else if ((!selectedDataType) || (!dataType) || (selectedDataType === dataType)) {
                option.show ();
            } else {
                option.prop ('selected', false).hide ();
            }
        });
        if (selectedFilterTypeOfData === 'D' || selectedFilterTypeOfData === 'DT'){
            selectReference.removeClass('hide').prop ('disabled', false);
            valueReference.addClass ('hide').prop ('disabled', true);
            selectReference.empty();
            selectReference.html(dateFieldOption.html ())
        } else {
            uiType = parseInt(uiType);
            if (jQuery.inArray (uiType, [ 15, 8192 ]) !== -1) {
                valueReference.addClass('hide').prop('disabled', true);
                selectReference.removeClass('hide').prop('disabled', false);
                functionAction = (uiType === 15) ? 'FETCH-PICKLIST' : 'FETCH-PIPELINE';
                var arguments = {
                    'module': 'systemalerts',
                    'action': 'AjaxSystemAlertsUtils',
                    'fieldname': filterField.val (),
                    'function': functionAction,
                    'Ajax': true
                };
                jQuery.post('index.php', arguments, function (data) {
                    var message;
                    try {
                        message = JSON.parse(JSON.stringify(data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            selectReference.empty();
                            selectReference.append(message.html)
                        }
                    }
                    catch (e) {
                        alert(e);
                    }
                });
            } else if (jQuery.inArray (uiType, [ 53 ]) !== -1) {
                valueReference.addClass('hide').prop ('disabled', true);
                selectReference.removeClass('hide').prop ('disabled', false);
                var arguments  = {
                    'module':   'systemalerts',
                    'action':   'AjaxSystemAlertsUtils',
                    'function': 'FIELD_TYPE_OWNER',
                    'Ajax':     'true'
                };
                jQuery.post('index.php', arguments, function (data) {
                    var message;
                    try {
                        message = JSON.parse (JSON.stringify (data));
                        if (message.error !== 'OK') {
                            throw message.error;
                        } else {
                            selectReference.empty();
                            selectReference.append(message.html)
                        }
                    }
                    catch (e) {
                        alert (e);
                    }
                });
            } else if (jQuery.inArray (uiType, [ 56 ]) !== -1) {
                valueReference.addClass('hide').prop ('disabled', true);
                selectReference.removeClass('hide').prop ('disabled', false);
                selectReference.empty();
                selectReference.append (
                    jQuery ('<option>', {
                            value:1,
                            text: 'Si'
                        }
                    )
                );
                selectReference.append (
                    jQuery ('<option>', {
                            value:0,
                            text: 'No'
                        }
                    )
                );
            } else {
                selectReference.addClass('hide').prop ('disabled', true);
                valueReference.removeClass('hide').prop ('disabled', false);
                valueReference.val('');
                valueReference.removeClass ('input-readonly');
                valueReference.prop('readonly', false);
                valueReference.datepicker ('remove');
            }
        }

    };

    var setlookAlert = function (obj) {
        var button      = jQuery (obj),
            idModal     = button.attr('data-modal-id'),
            idOcurrence = button.attr('data-ocurrence'),
            row         = jQuery ('#ocurrence-' + idOcurrence + '-' + idModal),
            arguments   = jQuery('#form-view-' + idModal).serialize() + '&idOcurrence=' + idOcurrence;

        button.attr('disabled', 'disabled');

        jQuery.post('index.php', arguments, function (response) {
            var message;
            try {
                message = JSON.parse (JSON.stringify (response));
                if(message.error !== 'OK') {
                    throw message.error;
                } else {
                    row.find('td:nth-child(3)').html('Descartada');
                }
            }
            catch (e) {
                alert(e);
                button.attr('disabled', false);
            }
        });
    };

    var openModalAlert = function (mode, codeType, systemAlertId, id) {
        var template = jQuery ('#alert-wizard-template-' + id);
        wizard = jQuery (template.html ()).wizard ({
            backdrop:   'static',
            showCancel: true,
            width:      '980',
            buttons:    {
                cancelText:     'Cancelar',
                nextText:       'Siguiente →',
                backText:       '← Atrás',
                submitText:     'Guardar',
                submittingText: 'Guardando...'
            },
            baseHeight: 340
        });

        wizard.cards [ 'start' ].el.find ('#mode').val (mode);
        wizard.cards [ 'start' ].el.find ('#systemAlertId').val (systemAlertId);
        wizard.cards [ 'start' ].el.find ('#cod-type').val (codeType);
        wizard.cards [ 'start' ].el.find ('#edit-app').val (codeType);

        wizard.cards [ 'start' ].on ('validate', validateLocation)
                                .on ('selected', setLocationAlert);
        wizard.cards [ 'step-1' ].on ('validate', validateSource)
                                 .on ('selected', setSourceAlert);
        wizard.cards [ 'step-2' ].on ('validate', validateFilter)
                                 .on ('selected', setFilterAlert);
        /* wizard.cards [ 'step-3' ].on ('validate', validateStepThree)
             .on ('selected', setWindowsSize);*/
        wizard.on ('submit', submitWizard)
            .on ('closed', destroyWizard)
            .on ('incrementCard', updateProgressBar)
            .on ('decrementCard', updateProgressBar);
        wizard.show ();
        jQuery ('.wizard-modal .wizard-nav-item > .wizard-nav-link').on ('click', function (evt) {
            var link  = jQuery (this),
                links = link.closest ('.nav-list').find ('.wizard-nav-item'),
                i, requestedCardIndex, activeCardIndex;
            if ((!link.closest ('.wizard-nav-item').hasClass ('already-visited')) || (links.length === 0)) {
                return;
            }

            requestedCardIndex = null;
            for (i = 0; i < links.length; i += 1) {
                if (link.text () === jQuery (links [ i ]).text ()) {
                    requestedCardIndex = i;
                    break;
                }
            }

            evt.preventDefault ();
            evt.stopPropagation ();
            if (!requestedCardIndex) {
                return;
            }

            activeCardIndex = wizard.getActiveCard ().index;
            if (activeCardIndex > requestedCardIndex) {
                for (i = activeCardIndex; i > requestedCardIndex; i -= 1) {
                    wizard.decrementCard ();
                }
            } else if (activeCardIndex < requestedCardIndex) {
                for (i = activeCardIndex; i < requestedCardIndex; i += 1) {
                    wizard.incrementCard ();
                }
            }
        });
    };

    window.SystemAlertUtils = {
        addFilter:          addFilter,
        addFilterGroup:     addFilterGroup,
        changeStatus:       changeStatus,
        createAlert:        createAlert,
        closeAlertWizard:   closeAlertWizard,
        detailViewAlert:    detailViewAlert,
        deleteAlert:        deleteAlert,
        deleteFilter:       deleteFilter,
        deleteFilterGroup:  deleteFilterGroup,
        loadAlerts:         loadAlerts,
        lookAlert:          lookAlert,
        openModalAlert:     openModalAlert,
        saveAlerts:         saveAlerts,
        searchAlerts:       searchAlerts,
        searchAlertsDate:   searchAlertsDate,
        selectApp:          selectApp,
        selectAlertModule:  selectAlertModule,
        selectAlertType:    selectAlertType,
        selectElement:      selectElement,
        selectElementField: selectElementField,
        setlookAlert:       setlookAlert
    };

    var onDocumentReadyHandler = function () {
        typeofdata[ 'V' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];
        typeofdata[ 'N' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
        typeofdata[ 'T' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'b', 'a' ];
        typeofdata[ 'I' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
        typeofdata[ 'C' ] = [ 'e', 'n' ];
        typeofdata[ 'D' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'b', 'a' ];
        typeofdata[ 'DT' ] = [ 'e', 'n', 'l', 'g', 'm', 'h', 'b', 'a' ];
        typeofdata[ 'NN' ] = [ 'e', 'n', 'l', 'g', 'm', 'h' ];
        typeofdata[ 'E' ] = [ 'e', 'n', 's', 'ew', 'c', 'k' ];

        fLabels[ 'e' ] = alert_arr.EQUALS;
        fLabels[ 'n' ] = alert_arr.NOT_EQUALS_TO;
        fLabels[ 's' ] = alert_arr.STARTS_WITH;
        fLabels[ 'ew' ] = alert_arr.ENDS_WITH;
        fLabels[ 'c' ] = alert_arr.CONTAINS;
        fLabels[ 'k' ] = alert_arr.DOES_NOT_CONTAINS;
        fLabels[ 'l' ] = alert_arr.LESS_THAN;
        fLabels[ 'g' ] = alert_arr.GREATER_THAN;
        fLabels[ 'm' ] = alert_arr.LESS_OR_EQUALS;
        fLabels[ 'h' ] = alert_arr.GREATER_OR_EQUALS;
        fLabels[ 'bw' ] = alert_arr.BETWEEN;
        fLabels[ 'b' ] = alert_arr.BEFORE;
        fLabels[ 'a' ] = alert_arr.AFTER;
        if (appcode === 'all') {
            var obj = jQuery('#li--' + appcode);

            jQuery('#newblock').val('reload');
            jQuery('#dinamicViewScale').val(jQuery('#viewPeriod').val());
            //obj.click();
        }
        jQuery('#date_from').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});
        jQuery('#date_to').datepicker({format: 'yyyy-mm-dd', language: 'es', weekStart: 1});

    };

    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));