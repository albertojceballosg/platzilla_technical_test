(function (jQuery) {
	var modal = null;

	// Private methods

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var onGetAvailableFoldersSuccessHandler = function (response) {
		var incomingFolderNames, outgoingFolderNames, i, option;
		if ((response === null) || (response === undefined) || (!jQuery.isArray (response))) {
			return;
		}

		incomingFolderNames = [ jQuery ('<option></option>').val ('').text ('Selecciona la carpeta') ];
		outgoingFolderNames = [ jQuery ('<option></option>').val ('').text ('Selecciona la carpeta') ];

		for (i = 0; i < response.length; i += 1) {
			incomingFolderNames.push (jQuery ('<option></option>').val (response [ i ]).text (response [ i ]));
			outgoingFolderNames.push (jQuery ('<option></option>').val (response [ i ]).text (response [ i ]));
		}

		modal.find ('#server-settings-section').find ('.button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-times').addClass ('fa-check');
		modal.find ('#incoming-folder-name').empty ().append (incomingFolderNames).prop ('disabled', false);
		modal.find ('#outgoing-folder-name').empty ().append (outgoingFolderNames).prop ('disabled', false);
		modal.find ('#folders-section').show ();
		modal.find ('#btn-save').prop ('disabled', true);
	};

	var onGetAvailableFoldersFailureHandler = function (jQueryResponse) {
		var message;

		if ((jQueryResponse) && (jQueryResponse.hasOwnProperty ('responseText')) && (jQueryResponse.responseText)) {
			message = 'Se ha presentado un error: ' + jQueryResponse.responseText;
		} else {
			message = 'Se ha presentado un error inesperado. Intenta más tarde';
		}
		alert (message);

		modal.find ('#server-settings-section').find ('.button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-check').addClass ('fa-times');
		modal.find ('#incoming-folder-name').empty ().prop ('disabled', true);
		modal.find ('#outgoing-folder-name').empty ().prop ('disabled', true);
		modal.find ('#folders-section').hide ();
		modal.find ('#btn-save').prop ('disabled', true);
	};

	var onGetMailServerSettingsSuccessHandler = function (response) {
		var incomingFolderNames, outgoingFolderNames, i, option;
		if ((response === null) || (response === undefined) || (!response.hasOwnProperty ('folders')) || (!jQuery.isArray (response [ 'folders' ]))) {
			return;
		}

		incomingFolderNames = [ jQuery ('<option></option>').val ('').text ('Selecciona la carpeta') ];
		outgoingFolderNames = [ jQuery ('<option></option>').val ('').text ('Selecciona la carpeta') ];

		for (i = 0; i < response [ 'folders' ].length; i += 1) {
			incomingFolderNames.push (jQuery ('<option></option>').val (response [ 'folders' ][ i ]).text (response [ 'folders' ][ i ]));
			outgoingFolderNames.push (jQuery ('<option></option>').val (response [ 'folders' ][ i ]).text (response [ 'folders' ][ i ]));
		}

		modal.find ('#credentials-section').find ('.button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-times').addClass ('fa-check');
		modal.find ('#incoming-host-name').val (response [ 'serversettings' ][ 'hostname' ]);
		modal.find ('#incoming-port').val (response [ 'serversettings' ][ 'port' ]);
		modal.find ('#incoming-security-type').val (response [ 'serversettings' ][ 'securitytype' ]);
		modal.find ('#incoming-authentication-method').val (response [ 'serversettings' ][ 'authenticationmethod' ]);
		modal.find ('#server-settings-section').hide ();
		modal.find ('#incoming-folder-name').empty ().append (incomingFolderNames).prop ('disabled', false);
		modal.find ('#outgoing-folder-name').empty ().append (outgoingFolderNames).prop ('disabled', false);
		modal.find ('#folders-section').show ();
		modal.find ('#server-settings-section').hide ();
		modal.find ('#btn-save').prop ('disabled', true);
	};

	var onGetMailServerSettingsFailureHandler = function (jQueryResponse) {
		var hostname             = null,
			port                 = null,
			securityType         = null,
			authenticationMethod = null,
			response, message;

		try {
			response = jQueryResponse.responseJSON;
			if ((response === null) || (response === undefined)) {
				message = 'Se ha recibido una respuesta inesperada. Intenta más tarde';
			} else if ((!response.hasOwnProperty ('serversettings')) || (!jQuery.isPlainObject (response [ 'serversettings' ]))) {
				// No se encontró información del servidor de correo
				message = 'No encontramos los datos de tu servidor de correo. Necesitamos que los suministres, o ponte en contacto con nosotros y te ayudaremos';
			} else {
				// Se encontró información del servidor de correo, pero es imposible conectarse
				hostname = response [ 'serversettings' ][ 'hostname' ];
				port = response [ 'serversettings' ][ 'port' ];
				securityType = response [ 'serversettings' ][ 'securitytype' ];
				authenticationMethod = response [ 'serversettings' ][ 'authenticationmethod' ];
				message = 'Hemos encontrado que tu servidor de correo tiene las siguientes características, pero ha sido imposible conectarse. Por favor verifica tu dirección de correo, contraseña y estos datos';
			}
		} catch (e) {
			message = 'Se ha presentado un error inesperado. Intenta más tarde';
		}
		alert (message);

		modal.find ('#credentials-section').find ('.button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-check').addClass ('fa-times');
		modal.find ('#incoming-host-name').val (hostname);
		modal.find ('#incoming-port').val (port);
		modal.find ('#incoming-security-type').val (securityType);
		modal.find ('#incoming-authentication-method').val (authenticationMethod);
		modal.find ('#server-settings-section').show ();
		modal.find ('#incoming-folder-name').empty ().prop ('disabled', true);
		modal.find ('#outgoing-folder-name').empty ().prop ('disabled', true);
		modal.find ('#folders-section').hide ();
		modal.find ('#btn-save').prop ('disabled', true);
	};

	var validateEmailAccount = function (form) {
		var username             = form.find ('#email-address').val (),
			plainPassword        = form.find ('#email-password').val (),
			hostName             = form.find ('#incoming-host-name').val (),
			port                 = form.find ('#incoming-port').val (),
			securityType         = form.find ('#incoming-security-type').val (),
			authenticationMethod = form.find ('#incoming-authentication-method').val (),
			incomingFolderName   = form.find ('#incoming-folder-name').val (),
			outgoingFolderName   = form.find ('#outgoing-folder-name').val ();

		if ((username === null) || (username === undefined) || (username.trim () === '')) {
			alert ('Introduce la dirección de correo');
			return false;
		} else if ((plainPassword === null) || (plainPassword === undefined) || (plainPassword.trim () === '')) {
			alert ('Introduce la contraseña');
			return false;
		} else if ((hostName === null) || (hostName === undefined) || (hostName.trim () === '')) {
			alert ('Introduce el nombre del servidor');
			return false;
		} else if ((port === null) || (port === undefined) || (!jQuery.isNumeric (port)) || (parseInt (port) < 1) || (parseInt (port) > 65535)) {
			alert ('Introduce un número de puerto válido');
			return false;
		} else if ((securityType === null) || (securityType === undefined) || (securityType.trim () === '')) {
			alert ('Selecciona el tipo de seguridad');
			return false;
		} else if ((authenticationMethod === null) || (authenticationMethod === undefined) || (authenticationMethod.trim () === '')) {
			alert ('Selecciona el mecanismo de autenticación');
			return false;
		} else if ((incomingFolderName === null) || (incomingFolderName === undefined) || (incomingFolderName.trim () === '')) {
			alert ('Selecciona la carpeta de correos entrantes');
			return false;
		} else if ((outgoingFolderName === null) || (outgoingFolderName === undefined) || (outgoingFolderName.trim () === '')) {
			alert ('Selecciona la carpeta de correos salientes');
			return false;
		}

		return true;
	};

	// Public methods
	var enableSaveButton = function () {
		var incomingFolderName = modal.find ('#incoming-folder-name').val (),
			outgoingFolderName = modal.find ('#outgoing-folder-name').val ();

		if (
			(incomingFolderName !== null) && (incomingFolderName !== undefined) && (incomingFolderName.trim () !== '') &&
			(outgoingFolderName !== null) && (outgoingFolderName !== undefined) && (outgoingFolderName.trim () !== '')
		) {
			modal.find ('#btn-save').prop ('disabled', false);
		} else {
			modal.find ('#btn-save').prop ('disabled', true);
		}
	};

	var getAvailableFolders = function () {
		var username, plainPassword, hostName, port, securityType, field, arguments;

		field = modal.find ('#email-address');
		username = field.val ();
		if ((username === null) || (username === undefined) || (username.trim () === '')) {
			alert ('Introduce la dirección de correo');
			field.focus ();
			return;
		}

		field = modal.find ('#email-password');
		plainPassword = field.val ();
		if ((plainPassword === null) || (plainPassword === undefined) || (plainPassword.trim () === '')) {
			alert ('Introduce la contraseña');
			field.focus ();
			return;
		}

		field = modal.find ('#incoming-host-name');
		hostName = field.val ();
		if ((hostName === null) || (hostName === undefined) || (hostName.trim () === '')) {
			alert ('Introduce el nombre del servidor');
			field.focus ();
			return;
		}

		field = modal.find ('#incoming-port');
		port = field.val ();
		if ((port === null) || (port === undefined) || (!jQuery.isNumeric (port)) || (parseInt (port) < 1) || (parseInt (port) > 65535)) {
			alert ('Introduce un número de puerto válido');
			field.focus ();
			return;
		}

		field = modal.find ('#incoming-security-type');
		securityType = field.val ();
		arguments = [
			'module=webmail',
			'action=GetAvailableFolders',
			'Ajax=true',
			'username=' + encodeURIComponent (username),
			'password=' + encodeURIComponent (Base64.encode (plainPassword)),
			'hostname=' + encodeURIComponent (hostName),
			'port=' + encodeURIComponent (port),
			'securitytype=' + encodeURIComponent (securityType)
		];

		modal.find ('#folders-section').hide ();
		modal.find ('#server-settings-section').find ('.button-container > .fa').removeClass ('fa-times').removeClass ('fa-check').addClass ('fa-pulse').addClass ('fa-spinner');
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (onGetAvailableFoldersSuccessHandler).fail (onGetAvailableFoldersFailureHandler);
	};

	var getMailServerSettings = function () {
		var username, plainPassword, field, arguments;

		field = modal.find ('#email-address');
		username = field.val ();
		if ((username === null) || (username === undefined) || (username.trim () === '')) {
			alert ('Introduce la dirección de correo');
			field.focus ();
			return;
		}

		field = modal.find ('#email-password');
		plainPassword = field.val ();
		if ((plainPassword === null) || (plainPassword === undefined) || (plainPassword.trim () === '')) {
			alert ('Introduce la contraseña');
			field.focus ();
			return;
		}

		arguments = [
			'module=webmail',
			'action=DetectMailServerSettings',
			'Ajax=true',
			'username=' + encodeURIComponent (username),
			'password=' + encodeURIComponent (Base64.encode (plainPassword))
		];

		modal.find ('#server-settings-section').hide ();
		modal.find ('#folders-section').hide ();
		modal.find ('#credentials-section').find ('.button-container > .fa').removeClass ('fa-times').removeClass ('fa-check').addClass ('fa-pulse').addClass ('fa-spinner');
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (onGetMailServerSettingsSuccessHandler).fail (onGetMailServerSettingsFailureHandler);
	};

	var openModal = function () {
		var modalTemplate = jQuery ('#email-account-modal-template');

		modal = jQuery (modalTemplate.html ());
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var saveEmailAccount = function (formElement) {
		var form = jQuery (formElement),
			arguments;

		if (!validateEmailAccount (form)) {
			return;
		}

		arguments = [
			'module=webmail',
			'action=SaveAccount',
			'Ajax=true',
			'emailaddress=' + encodeURIComponent (form.find ('#email-address').val ()),
			'password=' + encodeURIComponent (Base64.encode (form.find ('#email-password').val ())),
			'hostname=' + encodeURIComponent (form.find ('#incoming-host-name').val ()),
			'port=' + encodeURIComponent (form.find ('#incoming-port').val ()),
			'securitytype=' + encodeURIComponent (form.find ('#incoming-security-type').val ()),
			'authenticationmethod=' + encodeURIComponent (form.find ('#incoming-authentication-method').val ()),
			'incomingfoldername=' + encodeURIComponent (form.find ('#incoming-folder-name').val ()),
			'outgoingfoldername=' + encodeURIComponent (form.find ('#outgoing-folder-name').val ())
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			alert ('Se asoció tu cuenta de correo');
			modal.hide ();
			window.location.reload ();
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

	window.WebmailUtils = {
		enableSaveButton:      enableSaveButton,
		getAvailableFolders:   getAvailableFolders,
		getMailServerSettings: getMailServerSettings,
		openModal:             openModal,
		saveEmailAccount:      saveEmailAccount
	};
} (jQuery));
