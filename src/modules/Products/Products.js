(function (jQuery) {
	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('#productname');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre');
			field.focus ();
			return false;
		}

		field = form.find ('#type');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el tipo');
			field.focus ();
			return false;
		}

		field = form.find ('#baseprice');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el precio');
			field.focus ();
			return false;
		} else if ((!jQuery.isNumeric (value)) || (value < 0)) {
			alert ('Introduce un precio válido');
			field.focus ();
			return false;
		}

		return true;
	};

	window.ProductUtils = {
		validateForm: validateForm
	};
} (jQuery));
