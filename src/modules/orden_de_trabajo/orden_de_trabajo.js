(function (jQuery) {
    //private variables
    var matrixImportance = {
            'baja':  2,
            'media': 6,
            'alta':  10
        };
    var matrixPriority = {
        'realizar_de_inmediato': 10,
        'segun_planificacion':   5
    };

    // private functions
    var getNormalizedText = function (value) {
        if (value === '' || value === null || value === undefined || value === ' ') {
            return '';
        }
        var from = 'àáäâèéëêìíïîòóöôùúüûñç·/-,:;',
            to   = 'aaaaeeeeiiiioooouuuunc______',
            i, l;
        value = value.toLowerCase ().replace (' ', '_');
    		// remove accents, swap ñ for n, etc
        for (i = 0, l = from.length; i < l; i++) {
    			value = value.replace (new RegExp (from.charAt (i), 'g'), to.charAt (i));
        }

        value = value.replace (/[^a-z0-9 _]/g, '').replace (/\s+/g, '_').replace (/-+/g, '_');
        return value;
    };

    //public functions
    onchange_importance_work = function (obj) {
        var importance        = jQuery (obj).val(),
            priorityIndex     = jQuery('#priority_index'),
            workPriority      = jQuery ('#work_priority').val(),
            importanceValue   = getNormalizedText(importance),
            workPriorityValue = getNormalizedText(workPriority);

        if ((importanceValue === '') || (workPriorityValue === '')) {
            priorityIndex.val('');
        } else {
            priorityIndex.val((matrixImportance[importanceValue] * matrixPriority[workPriorityValue]).toString());
        }
    }


    onchange_work_priority = function (obj) {
        var priority          = jQuery (obj).val(),
            importance        = jQuery ('#importance_work').val(),
            priorityIndex     = jQuery('#priority_index'),
            importanceValue   = getNormalizedText(importance),
            workPriorityValue = getNormalizedText(priority);

        if ((importanceValue === '') || (workPriorityValue === '')) {
            priorityIndex.val('');
        } else {
            priorityIndex.val((matrixImportance[importanceValue] * matrixPriority[workPriorityValue]).toString());
        }
    }

    window.OderOfWorkUtils = {
    }
    var onDocumentReadyHandler = function () {
        var urlPage       = window.location.href,
            dummy         = urlPage.split('&'),
            priorityIndex = jQuery('#priority_index');
        if (
            (jQuery.inArray('action=DetailView', dummy) === -1) &&
            (jQuery.inArray('action=ListView', dummy) === -1)
        ) {
            priorityIndex.attr ('readonly', 'readonly');
        }
    };

    jQuery(document).ready(onDocumentReadyHandler);

}(jQuery));
