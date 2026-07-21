(function (jQuery) {
	// Private variables

	var modal = null;

	// Private methods

	var addActivityRow = function (container) {
		var rowTemplate = jQuery ('#record-activity-modal-row-template').html (),
			row         = jQuery (rowTemplate),
			rows, i, rowId, dummy;

		rowId = -1;
		rows = container.find ('.activity-row');
		for (i = 0; i < rows.length; i += 1) {
			dummy = jQuery (rows [ i ]);
			rowId = Math.max (rowId, dummy.data ('id'));
		}
		rowId += 1;

		row.data ('id', rowId);
		row.find ('.activity-name').attr ('name', 'activities[' + rowId + '][name]');
		row.find ('.activity-comment').attr ('name', 'activities[' + rowId + '][comment]');
		row.find ('.activity-related-module-name').attr ('name', 'activities[' + rowId + '][relatedmodulename]');
		row.find ('.data-field').attr ('id', 'record-activity-modal-related-entity-id-' + rowId).attr ('name', 'activities[' + rowId + '][relatedcrmid]');
		row.find ('.display-field').attr ('id', 'record-activity-modal-related-entity-id-display-' + rowId);
		row.find ('.activity-start-date').attr ('name', 'activities[' + rowId + '][startdate]');
		row.find ('.activity-start-time').attr ('name', 'activities[' + rowId + '][starttime]').timepicker ({ minuteStep: 5, showMeridian: false, disableFocus: false, showInputs: false, template: false });
		row.find ('.activity-end-date').attr ('name', 'activities[' + rowId + '][enddate]');
		row.find ('.activity-end-time').attr ('name', 'activities[' + rowId + '][endtime]').timepicker ({ minuteStep: 5, showMeridian: false, disableFocus: false, showInputs: false, template: false });
		container.append (row);
		modal.find ('.activity-start-date:last-child').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		modal.find ('.activity-end-date:last-child').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
	};

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var validateActivities = function (formElement) {
		var form = jQuery (formElement),
			rows = form.find ('.activity-row'),
			row, field, value, i;

		if (rows.length === 0) {
			alert ('Debes introducir alguna actividad para registrar');
			return false;
		}

		for (i = 0; i < rows.length; i += 1) {
			row = jQuery (rows [ i ]);

			field = row.find ('.activity-name');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce el nombre de la actividad');
				field.focus ();
				return false;
			}

			field = row.find ('.activity-comment');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce un comentario');
				field.focus ();
				return false;
			}

			field = row.find ('.activity-start-date');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce la fecha de inicio');
				field.focus ();
				return false;
			}

			field = row.find ('.activity-start-time');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce la hora de inicio');
				field.focus ();
				return false;
			}

			field = row.find ('.activity-end-date');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce la fecha de fin');
				field.focus ();
				return false;
			}

			field = row.find ('.activity-end-time');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce la hora de fin');
				field.focus ();
				return false;
			}
		}
		return true;
	};

	// Public methods

	var addRow = function (buttonElement) {
		var button    = jQuery (buttonElement),
			container = button.closest ('.table-responsive').find ('table > tbody');

		addActivityRow (container);
	};

	var clearRelatedModuleFields = function (buttonElement) {
		var button       = jQuery (buttonElement),
			rowContainer = button.closest ('.activity-related-module');

		rowContainer.find ('.data-field').val ('');
		rowContainer.find ('.display-field').val ('');
	};

	var deleteRow = function (buttonElement) {
		var button = jQuery (buttonElement);

		if (!confirm ('¿Estás seguro que quieres eliminar la actividad seleccionada?')) {
			return;
		}

		button.closest ('.activity-row').remove ();
	};

	var openModal = function () {
		var modalTemplate = jQuery ('#record-activity-modal-template');

		if (modal === null) {
			modal = jQuery (modalTemplate.html ());
			addActivityRow (modal.find ('.table-responsive table > tbody'));
			modal.modal ({ backdrop: 'static' });
			modal.find ()
		} else {
			modal.modal ('show');
		}
	};

	var openRelatedModuleModal = function (buttonElement) {
		var button                   = jQuery (buttonElement),
			rowContainer             = button.closest ('.activity-row'),
			moduleNameElement        = rowContainer.find ('.activity-related-module-name'),
			moduleName               = moduleNameElement.val (),
			relatedCrmFieldId        = rowContainer.find ('.data-field').attr ('id'),
			relatedCrmDisplayFieldId = rowContainer.find ('.display-field').attr ('id');

		if ((moduleName === undefined) || (moduleName === null) || (moduleName.trim () === '')) {
			alert ('Selecciona el módulo');
			moduleNameElement.focus ();
			return false;
		}

		button.attr ('data-current-module', '');
		button.attr ('data-display-field-id', relatedCrmDisplayFieldId);
		button.attr ('data-field-id', relatedCrmFieldId);
		button.attr ('data-referenced-module', moduleName);
		button.attr ('data-title', 'Relacionado con');

		RelatedModuleModalUtils.openModal (buttonElement);
	};

	var saveActivities = function (formElement) {
		var form = jQuery (formElement);

		if (!validateActivities (formElement)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data: form.serialize (),
			dataType: 'json',
			method: 'post'
		}).done (function () {
			modal.modal ('hide');
			destroyModal ();
            if (location.href.indexOf("?") === -1) {
                window.location = location.href += "?tab=ACTIVITY";
            }
            else if (location.href.indexOf("&tab") === -1) {
                window.location = location.href += "&tab=ACTIVITY";
            } else {
                window.location.reload ();
			}

		}).fail (function (jQueryResponse) {
			var message;

			if (jQueryResponse.responseJSON) {
				message = jQueryResponse.responseJSON;
			} else {
				message = 'Se ha presentado un error inesperado. Intenta más tarde';
			}
			alert (message);
		});
	};

	window.RecordActivityUtils = {
		addRow:                   addRow,
		clearRelatedModuleFields: clearRelatedModuleFields,
		deleteRow:                deleteRow,
		openModal:                openModal,
		openRelatedModuleModal:   openRelatedModuleModal,
		saveActivities:           saveActivities
	};
} (jQuery));