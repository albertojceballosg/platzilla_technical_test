(function (jQuery) {
	var modal           = null;

	// Private Methods
	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var createAccessToken = function (code) {
		var arguments;
		if (!confirm ('¿Estás seguro que quieres acceder a la instancia ' + code + '?')) {
			return;
		}

		arguments = [
			'module=instances',
			'action=CreateAccessToken',
			'code=' + encodeURIComponent (code),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data: arguments.join ('&'),
			dataType: 'json',
			method: 'post'
		}).done (function (response) {
			var modalTemplate,
				modal;
			if (response === null) {
				alert ('La instancia seleccionada no está registrada');
				return;
			}  else if ((!jQuery.isPlainObject (response)) || (!response.hasOwnProperty ('url'))) {
				alert ('Se ha recibido una respuesta inesperada del servidor. Intenta más tarde');
				return;
			}
			modalTemplate = jQuery ('#create-access-token-modal-template');
			modal = jQuery (modalTemplate.html ());
			modal.find ('#create-access-token-modal-url').val (response.url);
			modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
		}).fail (function (jQueryResponse) {
			alert ('Se ha presentado un error: ' + jQueryResponse.responseText);
			console.error (jQueryResponse.responseText);
		});
	};

	var validateForm = function (formElement) {
		var form = jQuery (formElement),
			field, value;

		field = form.find ('input[name="instancecode"]');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona la instancia');
			field.focus ();
			return false;
		}
		field = form.find ('#total-users');
		value = field.val ();
		if ((value === undefined) || (value === null) || (!value)) {
			alert ('Introduce el total de usuarios');
			field.focus ();
			return false;
		}
		return true;
	};

	window.InstancesUtils = {
		createAccessToken: createAccessToken,
		validateForm: validateForm
	};
} (jQuery));