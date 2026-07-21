(function (jQuery) {
    'use strict';

    window.ActivityUtils = window.ActivityUtils || {};

    ActivityUtils.saveTask = function (obj, id) {
        var formId = '#EditView-' + id,
            form = jQuery(formId),
            btnClose = jQuery('#close-' + id),
            formData = form.serialize();

        if (!ActivityUtils.validateForm(formId, id)) {
            return;
        }
        jQuery.post('index.php', formData, function (data) {
            try {
                var message = JSON.parse(JSON.stringify(data));
                if (message.error !== 'OK') {
                    throw message.error;
                } else {
                    alert('Tarea actualizada!');
                    btnClose.trigger('click');
                    location.reload(true);
                }
            } catch (e) {
                alert(e);
            }
        });
    };

    ActivityUtils.validateForm = function (formElement, id) {
        var form = jQuery(formElement),
            field, value;

        field = form.find('#activitytype-' + id);
        value = field.val();
        if (!value || value.trim() === '') {
            alert('Selecciona el tipo de tarea'); field.focus(); return false;
        }
        field = form.find('#taskname-' + id);
        value = field.val();
        if (!value || value.trim() === '') {
            alert('Introduce el asunto de la tarea'); field.focus(); return false;
        }
        field = form.find('#task_description-' + id);
        value = field.val();
        if (!value || value.trim() === '') {
            alert('Introduce la descripción de la tarea'); field.focus(); return false;
        }
        field = form.find('#date_start-' + id);
        value = field.val();
        if (!value || value.trim() === '' || value.trim() === '--') {
            alert('Selecciona la fecha de inicio de la tarea'); field.focus(); return false;
        }
        field = form.find('#start_time-' + id);
        value = field.val();
        if (!value || value.trim() === '') {
            alert('Selecciona la hora de inicio de la tarea'); field.focus(); return false;
        }
        var startDate = form.find('#date_start-' + id).val(),
            dueDate = form.find('#due_date-' + id).val();
        if (!dueDate || dueDate.trim() === '' || dueDate.trim() === '--') {
            alert('Selecciona la fecha de vencimiento de la tarea'); form.find('#due_date-' + id).focus(); return false;
        }
        if (dueDate <= startDate) {
            alert('La fecha de vencimiento debe ser mayor que la fecha de inicio'); form.find('#due_date-' + id).focus(); return false;
        }
        return true;
    };
}(jQuery));
