(function (jQuery) {
	// Private variables
	var modal = null;

	// Private methods
	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var onFailureHandler = function (jQueryResponse) {
		alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
	};

	var onGetMassEditFieldsSuccessHandler = function (response) {
		var modalTemplate = jQuery ('#mass-edit-modal-template'),
			selectedRecordIds = jQuery ('form#massdelete').find ('#allselectedboxes').val ().split (';'),
			i, field, fields;

		if (selectedRecordIds.length > 0) {
			fields = [];
			for (i = 0; i < selectedRecordIds.length; i += 1) {
				if (selectedRecordIds [ i ] === '') {
					continue;
				}

				field = jQuery ('<input>').attr ('type', 'text').attr ('name', 'recordids[]').attr ('value', selectedRecordIds [ i ]);
				fields.push (field);
			}
		} else {
			fields = null;
		}
		modal = jQuery (modalTemplate.html ());
		modal.find ('form').append (fields);
		modal.find ('.modal-title').text ('Edición masiva');
		modal.find ('.modal-body').append (response);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	// Public methods
	var openModal = function (moduleName) {
		var selectedRecords = jQuery ('form#massdelete').find ('#allselectedboxes'),
			arguments;

		if ((selectedRecords.val () === null) || (selectedRecords.val () === undefined) ||(selectedRecords.val ().trim () === '')) {
			alert ('Debes seleccionar algún registro para editar');
			return;
		}

		arguments = [
			'module=' + encodeURIComponent (moduleName),
			'action=MassEdit',
			'Popup=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'html',
			method:   'get'
		}).done (onGetMassEditFieldsSuccessHandler).fail (onFailureHandler);
	};

	window.MassEditUtils = {
		openModal: openModal
	};
} (jQuery));
