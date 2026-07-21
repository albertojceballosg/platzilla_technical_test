(function (jQuery) {

	var hideAdminVisibilityForm = function (formElement) {
		var form     = jQuery (formElement),
			oldValue = form.closest ('.admin-visibility').find ('.old-value');
		form.addClass ('hidden').find ('.new-value').val (oldValue.text ());
		oldValue.removeClass ('hidden');
	};

	var hideMenuForm = function (formElement) {
		var form     = jQuery (formElement),
			oldValue = form.closest ('.menu').find ('.old-value');
		form.addClass ('hidden').find ('.new-value').val (oldValue.text ());
		oldValue.removeClass ('hidden');
	};

	var saveAdminVisibility = function (formElement) {
		var form     = jQuery (formElement),
			oldValue = form.closest ('.admin-visibility').find ('.old-value'),
			newValue = form.find ('.new-value');
		if (newValue.val () === oldValue.text ()) {
			hideAdminVisibilityForm (formElement);
			return;
		}

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			oldValue.text (newValue.val ());
			form.addClass ('hidden');
			oldValue.removeClass ('hidden');
			alert ('Se ha cambiado la visibilidad');
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
		return false;
	};

	var saveMenu = function (formElement) {
		var form     = jQuery (formElement),
			oldValue = form.closest ('.menu').find ('.old-value'),
			newValue = form.find ('.new-value');
		if (newValue.val () === oldValue.text ()) {
			hideMenuForm (formElement);
			return;
		}

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			oldValue.text (newValue.val ());
			form.addClass ('hidden');
			oldValue.removeClass ('hidden');
			alert ('Se ha cambiado el menú');
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
		return false;
	};

	var showAdminVisibilityForm = function (labelElement) {
		var label = jQuery (labelElement),
			form  = label.closest ('.admin-visibility').find ('.admin-visibility-form');
		label.addClass ('hidden');
		form.removeClass ('hidden');
	};

	var showMenuForm = function (labelElement) {
		var label = jQuery (labelElement),
			form  = label.closest ('.menu').find ('.menu-form');
		label.addClass ('hidden');
		form.removeClass ('hidden');
	};

	window.ModuleUtils = {
		hideAdminVisibilityForm: hideAdminVisibilityForm,
		hideMenuForm:            hideMenuForm,
		saveAdminVisibility:     saveAdminVisibility,
		saveMenu:                saveMenu,
		showAdminVisibilityForm: showAdminVisibilityForm,
		showMenuForm:            showMenuForm
	};
} (jQuery));