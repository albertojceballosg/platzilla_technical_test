(function (jQuery) {

    // private methods
    var resetTask = function (id) {
        var task   = jQuery ('#step_task-' + id);
        task.find('option').each(function () {
            jQuery(this).show();
        });
        task.val('');
    }

    // public methods
    var moduleSelected = function (obj, id) {
        var module = jQuery(obj),
            task   = jQuery ('#step_task-' + id);
        if (module.val () !== '') {
            task.val('');
            task.find('option').each(function () {
                var task = jQuery(this);
                if (task.data('module') !== module.val() && task.val() !== '') {
                    task.hide();
                } else {
                    task.show();
                }
            });
        } else {
            resetTask(id);
        }
    }

    var stepTypeSelected = function (obj, id) {
        var $stepType     = jQuery(obj).val(),
            module        = jQuery ('#step_module-' + id),
            task          = jQuery ('#step_task-' + id),
            view          = jQuery ('#step_view-' + id).find ('option'),
            typeManual    = jQuery ('.step-manual'),
            typePlatzilla = jQuery ('.step-no-manual'),
            typeAssisted  = jQuery ('.step-no-automatic');

        resetTask (id);

        if ($stepType === 'MANUAL') {
            typeManual.removeClass('hidden');
            typePlatzilla.addClass('hidden');
            module.val('');
            task.val('');
        }else if ($stepType === 'ASSISTED') {
            typeManual.addClass ('hidden');
            typePlatzilla.removeClass('hidden');
            typeAssisted.addClass ('hidden');
            module.val('');
            task.val('');
        } else {
            typeManual.addClass('hidden');
            typePlatzilla.removeClass('hidden');
            typeAssisted.removeClass('hidden');
            module.val('');
            task.val('');
        }
    }

    window.StepTypeUtls = {
        moduleSelected:   moduleSelected,
        stepTypeSelected: stepTypeSelected
    };

    var onDocumentReadyHandler = function () {
    };
    jQuery(document).ready(onDocumentReadyHandler);
}(jQuery));
