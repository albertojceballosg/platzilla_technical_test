(function (jQuery) {
	var validateRegisterForm = function (formElement) {
		var form = jQuery (formElement),
			regex, field;

		field = form.find ('#email');
		if (!field.val ()) {
			alert ('Debes suministrar tu dirección de correo electrónico');
			field.focus ();
			return false;
		}

		regex = /^(([^<>()[\]\.,;:\s@"]+(\.[^<>()[\]\.,;:\s@"]+)*)|(".+"))@(([^<>()[\]\.,;:\s@"]+\.)+[^<>()[\]\.,;:\s@"]{2,})$/i;
		if (!regex.test (field.val ())) {
			alert ('Debes suministrar una dirección de correo válida');
			field.focus ();
			return false;
		}

		field = form.find ('#privacypolicy');
		if (!field.is (':checked')) {
			alert ('Debes aceptar nuestra Política de Privacidad');
			field.focus ();
			return false;
		}
		return true;
	};

	var showLoginForm = function (buttonElement) {
		var button    = jQuery (buttonElement),
			container = button.closest ('.item-content').find ('.form-container'),
			form      = jQuery (jQuery ('#login-form-template').html ());

		button.closest ('.content').hide ();
		container.html (form);
	};

	window.LoginUtils = {
		showLoginForm:        showLoginForm,
		validateRegisterForm: validateRegisterForm
	};

	jQuery.fn.carousel.Constructor.prototype.keydown = function () { };
} (jQuery));