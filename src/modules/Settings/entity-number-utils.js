(function (jQuery) {
	// Private variables
	var modal = null;

	// Private functions
	var allowNumbersOnly = function (evt) {
		evt = (evt) ? evt : window.event;
		var charCode = evt.which ? evt.which : evt.keyCode;
		return !((charCode > 31) && ((charCode < 48) || (charCode > 57)));
	};

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#entity-number-prefix');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el prefijo');
			field.focus ();
			return false;
		}

		field = form.find ('#entity-number-initial-sequence');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce la secuencia inicial');
			field.focus ();
			return false;
		}

		field = form.find ('#entity-number-current-sequence');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce la secuencia actual');
			field.focus ();
			return false;
		}

		return true;
	};

	// Public functions
	var openModal = function (moduleName, prefix, initialSequence, currentSequence) {
		var modalTemplate = jQuery ('#entity-number-modal-template');

		modal = jQuery (modalTemplate.html ());
		modal.find ('input[name="modulename"]').val (moduleName);
		modal.find ('input[name="prefix"]').val (prefix);
		modal.find ('input[name="initialsequence"]').val (initialSequence).on ('keypress', allowNumbersOnly);
		modal.find ('input[name="currentsequence"]').val (currentSequence).on ('keypress', allowNumbersOnly);
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var saveEntityNumber = function (formElement) {
		if (!validateForm (formElement)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data:     jQuery (formElement).serialize (),
			dataType: 'json',
			'method': 'post'
		}).done (function () {
			alert ('El número de registro ha sido actualizado');
			modal.modal ('hide');
			window.location.reload ();
		}).fail (function (jQueryResponse) {
			var message;
			try {
				message = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				message = 'Se ha presentado un error. Intenta más tarde';
			}
			alert (message);
		});
	};

	window.EntityNumberUtils = {
		openModal:        openModal,
		saveEntityNumber: saveEntityNumber
	};
} (jQuery));
