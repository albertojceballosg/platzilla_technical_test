(function (jQuery) {
    var actionList = [
        'Verificando instancias del cliente.',
        'Cargando recursos del plan.',
        'Creando recursos del plan en la instancia.',
        'Cargando plan de acción.',
        'Creando plan de acción en la instancia.',
        'Asignando recursos al plan de acción.',
        'Cargando destino.',
        'Creando destino.'
    ];
    // private methods
    var getInstanceCode = function (email, intervalId) {
       var arguments = {
                'module':   'model_action_plan',
                'action':   'AjaxModelActionPlanUtils',
                'function': 'GET_INSTANCE_CODE',
                'record':   1,
                'email':    encodeURIComponent(email),
                'Ajax':      'true'
            },
           instanceData = [];

        jQuery.ajaxSetup ({async: false});
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    instanceData.push (message.html.code);
                    instanceData.push (message.html.name);
                }
            }
            catch (e) {
                clearInterval (intervalId);
                alert(e);
            }
        });
        return instanceData;
    };

    // public methods
    var selectedPlan = function (obj, id) {
        var button      = jQuery (obj),
            data        = button.attr ('data-destination'),
            arguments   = {
                'module':   'model_action_plan',
                'action':   'AjaxModelActionPlanUtils',
                'function': 'COPY_ACTION_PLAN',
                'record':   1,
                'instance': '',
                'initiatives': '',
                'destination': data,
                'Ajax':     'true'
            },
            initiatives = [],
            instance    = jQuery ('#client-email-' + id).val (),
            instanceData,
            index       = 0,
            tBody       = jQuery ('#inititives-' + id),
            selectedInfo = jQuery ('#info-plan-selected-' + id),
            textInfo    = selectedInfo.find('p').eq(0);
        if (instance !== '') {
            instanceData = getInstanceCode(instance, intervalId);
            if (instanceData.length === 0) {
                selectedInfo.addClass('hide');
                return false
            }
            arguments.instance = instanceData;
        }
        selectedInfo.removeClass('hide');
        const intervalId = setInterval(function () {
            textInfo.html(actionList[index]);
            if (index < (actionList.length - 1)) {
                index += 1;
            } else {
                clearInterval(intervalId);
            }
            }, 1200);
        tBody.find('tr').each(function (index, tr) {
            var idInitiative = jQuery(tr).attr('id');
            initiatives.push(jQuery(tr).attr('id'))
        });

        arguments.initiatives = initiatives;
        button.prop ("disabled",true);
        jQuery.post('index.php', arguments, function (data) {
            try {
                var message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    clearInterval (intervalId);
                    selectedInfo.addClass('hide');
                    alert('El plan de acción ha sido instalado.')
                    button.prop("disabled",false);
                    if (message.url !== '') {
                        location.href = message.url;
                    }
                }
            }
            catch (e) {
                clearInterval (intervalId);
                selectedInfo.addClass('hide');
                if (e === 'undefined' || !e || e === undefined) {
                    e = 'Uoops! algo salió mal, revise los componentes del plan de acción'
                }
                alert(e);
                button.prop("disabled",false);
            }
        });
    };
    
    window.ModelActionPlanUtls = {
        selectedPlan: selectedPlan
    };

    var onDocumentReadyHandler = function () {

    };
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));