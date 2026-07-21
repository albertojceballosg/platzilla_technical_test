(function (jQuery) {
	var modal = null;

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var openUpdateLimitModal = function (buttonElement) {
		var button        = jQuery (buttonElement),
			maxRecords    = button.attr ('data-max-records'),
			moduleLabel   = button.attr ('data-module-label'),
			moduleName    = button.attr ('data-module-name'),
			modalTemplate = jQuery ('#change-module-limits-modal-template');

		modal = jQuery (modalTemplate.html ());
		modal.find ('#change-module-limits-modal-module-name').val (moduleName);
		modal.find ('#change-module-limits-modal-max-records').val (maxRecords);
		modal.find ('#change-module-limits-modal-module-label').text (moduleLabel);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var deletePlan = function (planName) {
		return confirm ('¿Estás seguro que quieres eliminar el plan "' + planName + '"?')
	};

	var validateLimitForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#change-module-limits-modal-max-records');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce la cantidad de registros');
			field.focus ();
			return false;
		} else if ((!jQuery.isNumeric (value)) || (value < -1)) {
			alert ('Introduce una cantidad de registros válida');
			field.focus ();
			return false;
		}
		return true;
	};

	var validatePlanForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#planname');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nombre');
			field.focus ();
			return false;
		}

		field = form.find ('#description');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce la descripción');
			field.focus ();
			return false;
		}

		field = form.find ('#total-applications');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el total de aplicaciones incluidas');
			field.focus ();
			return false;
		} else if ((!jQuery.isNumeric (value)) || (value < -1)) {
			alert ('Introduce un total de aplicaciones válido');
			field.focus ();
			return false;
		}

		field = form.find ('#total-users');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el total de usuarios incluidos');
			field.focus ();
			return false;
		} else if ((!jQuery.isNumeric (value)) || (value < -1)) {
			alert ('Introduce un total de usuarios válido');
			field.focus ();
			return false;
		}

		field = form.find ('#total-disk-space');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el total de espacio en disco incluido');
			field.focus ();
			return false;
		} else if ((!jQuery.isNumeric (value)) || (value < -1)) {
			alert ('Introduce un total de espacio en disco válido');
			field.focus ();
			return false;
		}

		field = form.find ('#baseprice');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el precio');
			field.focus ();
			return false;
		} else if ((!jQuery.isNumeric (value)) || (value < 0)) {
			alert ('Introduce un precio válido');
			field.focus ();
			return false;
		}

		field = form.find ('#status');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el status');
			field.focus ();
			return false;
		}

		return true;
	};

	window.PlatformBillingPlanUtils = {
		deletePlan:           deletePlan,
		openUpdateLimitModal: openUpdateLimitModal,
		validateLimitForm:    validateLimitForm,
		validatePlanForm:     validatePlanForm
	};
} (jQuery));
