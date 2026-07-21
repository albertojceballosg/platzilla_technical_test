(function (jQuery) {
	// private variables
	var modal = null;

	// Private methods

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		if (CKEDITOR.instances [ 'body' ]) {
			CKEDITOR.instances [ 'body' ].destroy ();
		}
		jQuery (this).remove ();
		modal = null;
	};

	var fillEmailComposerData = function (modal, response, account) {
		var section, field, value, recipients, dummies, i;

		section = modal.find ('#email-composer');

		field = section.find ('#from');
		field.val (account);

		recipients = response [ 'from' ] ? response [ 'from' ].split (',') : [];
		dummies = response [ 'to' ] ? response [ 'to' ].split (',') : [];
		for (i = 0; i < dummies.length; i += 1) {
			if (dummies [ i ].indexOf (account) === -1) {
				recipients.push (dummies [ i ].trim ());
			}
		}
		section.find ('#to').val (recipients.join (', '));

		recipients = [];
		dummies = response [ 'cc' ] ? response [ 'cc' ].split (',') : [];
		for (i = 0; i < dummies.length; i += 1) {
			if (dummies [ i ].indexOf (account) === -1) {
				recipients.push (dummies [ i ].trim ());
			}
		}
		section.find ('#cc').val (recipients.join (', '));

		recipients = [];
		dummies = response [ 'bcc' ] ? response [ 'bcc' ].split (',') : [];
		for (i = 0; i < dummies.length; i += 1) {
			if (dummies [ i ].indexOf (account) === -1) {
				recipients.push (dummies [ i ].trim ());
			}
		}
		section.find ('#bcc').val (recipients.join (', '));

		field = section.find ('#subject');
		value = response [ 'subject' ];
		if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
			field.val ('Re: ' + value);
		} else {
			field.val ('');
		}

		field = section.find ('#body');
		value = response [ 'body' ];
		if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
			field.val ('<p></p><p>------------------</p>' + value);
		} else {
			field.val ('');
		}

		section.hide ();
	};

	var fillEmailViewerData = function (modal, response) {
		var section, field, value, i, attachments, attachment;

		section = modal.find ('#email-viewer');

		field = section.find ('#from');
		value = response [ 'from' ];
		if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
			field.val (value);
			field.closest ('.row').show ();
		} else {
			field.val ('');
			field.closest ('.row').hide ();
		}

		field = section.find ('#to');
		value = response [ 'to' ];
		if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
			field.val (value);
			field.closest ('.row').show ();
		} else {
			field.val ('');
			field.closest ('.row').hide ();
		}

		field = section.find ('#cc');
		value = response [ 'cc' ];
		if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
			field.val (value);
			field.closest ('.row').show ();
		} else {
			field.val ('');
			field.closest ('.row').hide ();
		}

		field = section.find ('#bcc');
		value = response [ 'bcc' ];
		if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
			field.val (value);
			field.closest ('.row').show ();
		} else {
			field.val ('');
			field.closest ('.row').hide ();
		}

		field = section.find ('#subject');
		value = response [ 'subject' ];
		if ((value !== null) && (value !== undefined) && (value.trim () !== '')) {
			field.val (value);
			field.closest ('.row').show ();
		} else {
			field.val ('');
			field.closest ('.row').hide ();
		}

		field = section.find ('#attachments');
		if ((jQuery.isArray (response [ 'attachments' ])) && (response [ 'attachments' ].length > 0)) {
			attachments = [];
			for (i = 0; i < response [ 'attachments' ].length; i += 1) {
				attachment = jQuery ('<a></a>')
					.attr ('href', response [ 'attachments' ][ i ][ 'uri' ])
					.attr ('target', '_blank')
					.text (response [ 'attachments' ][ i ][ 'name' ]);
				attachments.push (jQuery ('<li></li>').addClass ('attachment').append (attachment));
			}
			field.append (attachments);
			field.closest ('.row').show ();
		} else {
			field.closest ('.row').hide ();
		}

		section.find ('#email-body').attr ('src', 'index.php?module=webmail&action=GetMailMessageBody&Ajax=true&record=' + response [ 'id' ]);
		section.show ();
	};

	var loadBodyEditor = function () {
		loadCkEditor (
			'body',
			{
				toolbar: [
					[ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'Subscript', 'Superscript' ],
					[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent' ],
					[ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
					[ 'Link', 'Unlink', 'Anchor', '-', 'Undo', 'Redo', '-', 'Find', 'Replace', '-', 'SelectAll', 'RemoveFormat', '-', 'Image', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'TextColor', 'BGColor' ],
					'/',
					[ 'Styles', 'Format', 'Font', 'FontSize' ]
				]
			}
		);
	};

	var loadCkEditor = function (inputId, additionalOptions) {
		var options = {
			contentsCss:   [ 'themes/centaurus/css/bootstrap/bootstrap.min.css' ],
			entities:      false,
			language:      'es',
			removePlugins: 'elementspath'
		};
		jQuery.extend (options, additionalOptions);
		if (CKEDITOR.instances[ inputId ]) {
			CKEDITOR.instances[ inputId ].setData (jQuery ('#' + inputId).val ());
		} else {
			CKEDITOR.replace (inputId, options);
		}
	};

	var onGenericFailureHandler = function (jQueryResponse) {
		var response = jQueryResponse.responseJSON,
			message;
		if ((response === null) || (response === undefined)) {
			message = 'Se ha recibido una respuesta inesperada. Intenta más tarde';
		} else {
			message = response;
		}
		alert (message);
	};

	var onSearchEmailMessagesSuccessHandler = function (response) {
		var container = jQuery ('#emails-conversation');

		if ((response === null) || (response === undefined)) {
			alert ('Se ha recibido una respuesta inesperada del servidor. Intenta más tarde');
			return;
		}

		container.find ('.conversation-inner').empty ().append (response);
	};

	var onSendEmailMessageSuccessHandler = function (response) {
		if ((response === null) || (response === undefined)) {
			alert ('Se ha recibido una respuesta inesperada del servidor. Intenta más tarde');
			return;
		}

		alert ('Se ha enviado el correo');
		modal.modal ('hide');
	};

	var onGetAvailableFoldersFailureHandler = function (jQueryResponse) {
		var container = jQuery ('#account-settings-container'),
			message;

		if ((jQueryResponse) && (jQueryResponse.hasOwnProperty ('responseText')) && (jQueryResponse.responseText)) {
			message = 'Se ha presentado un error: ' + jQueryResponse.responseText;
		} else {
			message = 'Se ha presentado un error inesperado. Intenta más tarde';
		}
		alert (message);

		container.find ('#access-token-section').find ('.button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-check').addClass ('fa-times');
		container.find ('#incoming-folder-name').empty ().prop ('disabled', true);
		container.find ('#outgoing-folder-name').empty ().prop ('disabled', true);
		container.find ('#folders-section').hide ();
		container.find ('.action-bar').hide ();
	};

	var onGetAvailableFoldersSuccessHandler = function (response) {
		var container = jQuery ('#account-settings-container'),
			incomingFolderNames, outgoingFolderNames, i, option;

		if ((response !== null) && (response !== undefined) && (jQuery.isArray (response))) {
			incomingFolderNames = [ jQuery ('<option></option>').val ('').text ('Selecciona la carpeta') ];
			outgoingFolderNames = [ jQuery ('<option></option>').val ('').text ('Selecciona la carpeta') ];

			for (i = 0; i < response.length; i += 1) {
				incomingFolderNames.push (jQuery ('<option></option>').val (response [ i ]).text (response [ i ]));
				outgoingFolderNames.push (jQuery ('<option></option>').val (response [ i ]).text (response [ i ]));
			}
		}

		container.find ('#access-token-section').find ('.button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-times').addClass ('fa-check');
		container.find ('#incoming-folder-name').empty ().append (incomingFolderNames).prop ('disabled', false);
		container.find ('#outgoing-folder-name').empty ().append (outgoingFolderNames).prop ('disabled', false);
		container.find ('#folders-section').show ();
		container.find ('.action-bar').show ();
	};

	var onGetMailServerSettingsFailureHandler = function (jQueryResponse) {
		var container = jQuery ('#account-settings-container'),
			settings, response;

		response = jQueryResponse.responseJSON;
		if ((response === null) || (response === undefined)) {
			alert ('Se ha recibido una respuesta inesperada. Intenta más tarde');
			return;
		}

		container.find ('#incoming-host-name').val ('');
		container.find ('#incoming-port').val ('');
		container.find ('#incoming-security-type').val ('');
		container.find ('#incoming-service').val ('');
		container.find ('#incoming-authentication-method').val ('');
		container.find ('#incoming-username-type').val ('');
		container.find ('#outgoing-host-name').val ('');
		container.find ('#outgoing-port').val ('');
		container.find ('#outgoing-security-type').val ('');
		container.find ('#outgoing-service').val ('');
		container.find ('#outgoing-authentication-method').val ('');
		container.find ('#outgoing-username-type').val ('');

		container.find ('#server-settings-section').show ();
		container.find ('#access-token-section').hide ();
		container.find ('#folders-section').hide ();
		container.find ('.action-bar').hide ();
		container.find ('#email-address-section .button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-check').addClass ('fa-times');
	};

	var onGetMailServerSettingsSuccessHandler = function (response) {
		var container = jQuery ('#account-settings-container'),
			accessTokenSection, accessToken;
		if ((response === null) || (response === undefined)) {
			return;
		}

		container.find ('#incoming-host-name').val (response [ 'incominghostname' ]);
		container.find ('#incoming-port').val (response [ 'incomingport' ]);
		container.find ('#incoming-security-type').val (response [ 'incomingsecuritytype' ]);
		container.find ('#incoming-service').val (response [ 'incomingservice' ]);
		container.find ('#incoming-authentication-method').val (response [ 'incomingauthenticationmethod' ]);
		container.find ('#incoming-username-type').val (response [ 'incomingusernametype' ]);
		container.find ('#outgoing-host-name').val (response [ 'outgoinghostname' ]);
		container.find ('#outgoing-port').val (response [ 'outgoingport' ]);
		container.find ('#outgoing-security-type').val (response [ 'outgoingsecuritytype' ]);
		container.find ('#outgoing-service').val (response [ 'outgoingservice' ]);
		container.find ('#outgoing-authentication-method').val (response [ 'outgoingauthenticationmethod' ]);
		container.find ('#outgoing-username-type').val (response [ 'outgoingusernametype' ]);

		container.find ('#server-settings-section').hide ();
		accessTokenSection = container.find ('#access-token-section');
		if (response [ 'incomingauthenticationmethod' ] === 'oauth2') {
			accessToken = container.find ('#access-token').val ();
			accessTokenSection.find ('.email-password-stuff').hide ();
			if (accessToken.trim () === '') {
				accessTokenSection.find ('.oauth2-token-stuff.with-token').hide ();
				accessTokenSection.find ('.oauth2-token-stuff.without-token').show ();
			} else {
				accessTokenSection.find ('.oauth2-token-stuff.with-token').show ();
				accessTokenSection.find ('.oauth2-token-stuff.without-token').hide ();
			}
		} else {
			accessTokenSection.find ('.oauth2-token-stuff').hide ();
			accessTokenSection.find ('.email-password-stuff').show ();
		}
		accessTokenSection.show ();
		container.find ('#folders-section').hide ();
		container.find ('.action-bar').hide ();
		container.find ('#email-address-section .button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-times').addClass ('fa-check');
	};

	var onTestMailServerSettingsFailureHandler = function (jQueryResponse) {
		var container = jQuery ('#account-settings-container'),
			response  = jQueryResponse.responseJSON,
			message;

		if ((response === null) || (response === undefined)) {
			message = 'Se ha recibido una respuesta inesperada. Intenta más tarde';
		} else {
			message = response;
		}
		alert (message);

		container.find ('#server-settings-section').show ();
		container.find ('#access-token-section').hide ();
		container.find ('#folders-section').hide ();
		container.find ('.action-bar').hide ();
		container.find ('#server-settings-section .button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-check').addClass ('fa-times');
	};

	var onTestMailServerSettingsSuccessHandler = function () {
		var container          = jQuery ('#account-settings-container'),
			accessTokenSection = container.find ('#access-token-section');

		accessTokenSection.find ('.oauth2-token-stuff').hide ();
		accessTokenSection.find ('.email-password-stuff').show ();
		accessTokenSection.show ();
		container.find ('#folders-section').hide ();
		container.find ('.action-bar').hide ();
		container.find ('#server-settings-section .button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-spinner').removeClass ('fa-times').addClass ('fa-check');
	};

	var setCountMain = function () {
        var bellNum     = jQuery('#bell-num'),
            totalMain   = 0;
        totalMain   = parseInt (bellNum.html ());
        if (totalMain > 2) {
            totalMain -= 1;
            bellNum.html (totalMain);
        } else if (!bellNum.hasClass('hide')) {
            bellNum.addClass ('hide');
        }
	};

	var validateRelatedEntityRow = function (relatedEntityRow) {
		var field, value;

		field = relatedEntityRow.find ('.module-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el módulo');
			field.focus ();
			return false;
		}

		field = relatedEntityRow.find ('.display-field');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el registro');
			field.focus ();
			return false;
		}

		return true;
	};

	var validateRelatedEntities = function (form) {
		var relatedEntities = form.find ('.related-entity-row'),
			i;

		if (relatedEntities.length === 0) {
			alert ('Debes seleccionar algún registro para asociar');
			return false;
		}

		for (i = 0; i < relatedEntities.length; i += 1) {
			if (!validateRelatedEntityRow (jQuery (relatedEntities [ i ]))) {
				return false;
			}
		}

		return true;
	};

	// Public methods

	var addRelatedEntityRow = function (buttonElement) {
		var relatedEntityRowTemplate = jQuery ('#email-related-entity-row-template'),
			button                   = jQuery (buttonElement),
			messageId                = button.closest ('.conversation-item').data ('id'),
			tableBody                = button.closest ('.related-entities').find ('table > tbody'),
			totalEntityRows          = tableBody.find ('.related-entity-row').length,
			entityRow                = tableBody.find ('.related-entity-row:last-child');

		if ((totalEntityRows > 0) && (!validateRelatedEntityRow (entityRow))) {
			return;
		}

		entityRow = jQuery (relatedEntityRowTemplate.html ());
		entityRow.find ('.data-field').attr ('id', 'relatedentityid-' + messageId + '-' + totalEntityRows);
		entityRow.find ('.display-field').attr ('id', 'relatedentityid-' + messageId + '-' + totalEntityRows + '-display');
		tableBody.append (entityRow)

	};

	var clearRelatedEntityFields = function (buttonElement) {
		var button    = jQuery (buttonElement),
			container = button.closest ('.input-group');

		container.find ('.data-field').val ('');
		container.find ('.display-field').val ('');
	};

	var composeEmail = function () {
		modal.find ('#email-viewer').hide ();
		modal.find ('#email-composer').show ();
		loadBodyEditor ();
	};

	var deleteRelatedEntity = function (buttonElement) {
		if (!confirm ('¿Estás seguro que quieres eliminar la asociación seleccionada?')) {
			return;
		}

		jQuery (buttonElement).closest ('.related-entity-row').remove ();
	};

	var enableSaveButton = function () {
		var container          = jQuery ('#account-settings-container'),
			incomingFolderName = container.find ('#incoming-folder-name').val (),
			outgoingFolderName = container.find ('#outgoing-folder-name').val ();

		if (
			(incomingFolderName !== null) && (incomingFolderName !== undefined) && (incomingFolderName.trim () !== '') &&
			(outgoingFolderName !== null) && (outgoingFolderName !== undefined) && (outgoingFolderName.trim () !== '')
		) {
			container.find ('#btn-save').prop ('disabled', false);
		} else {
			container.find ('#btn-save').prop ('disabled', true);
		}
	};

	var getAvailableFolders = function () {
		var container = jQuery ('#account-settings-container'),
			emailAddress, accessToken, hostName, port, securityType, authenticationMethod, userNameType, service, field, arguments;

		field = container.find ('#email-address');
		emailAddress = field.val ();
		if ((emailAddress === null) || (emailAddress === undefined) || (emailAddress.trim () === '')) {
			alert ('Introduce la dirección de correo');
			field.focus ();
			return;
		}

		field = container.find ('#incoming-host-name');
		hostName = field.val ();
		if ((hostName === null) || (hostName === undefined) || (hostName.trim () === '')) {
			alert ('Introduce el nombre del servidor');
			field.focus ();
			return;
		}

		field = container.find ('#incoming-port');
		port = field.val ();
		if ((port === null) || (port === undefined) || (!jQuery.isNumeric (port)) || (parseInt (port) < 1) || (parseInt (port) > 65535)) {
			alert ('Introduce un número de puerto válido');
			field.focus ();
			return;
		}

		field = container.find ('#incoming-security-type');
		securityType = field.val ();
		if ((securityType === null) || (securityType === undefined) || (securityType.trim () === '')) {
			alert ('Selecciona el tipo de seguridad');
			field.focus ();
			return;
		}

		field = container.find ('#incoming-authentication-method');
		authenticationMethod = field.val ();
		if ((authenticationMethod === null) || (authenticationMethod === undefined) || (authenticationMethod.trim () === '')) {
			alert ('Selecciona el mecanismo de autenticación');
			field.focus ();
			return;
		}

		if (authenticationMethod === 'password-cleartext') {
			field = container.find ('#email-password');
			accessToken = field.val ();
			if ((accessToken === null) || (accessToken === undefined) || (accessToken.trim () === '')) {
				alert ('Introduce la contraseña');
				field.focus ();
				return;
			}
		} else {
			field = container.find ('#access-token');
			accessToken = field.val ();
		}

		field = container.find ('#incoming-username-type');
		userNameType = field.val ();

		field = container.find ('#incoming-service');
		service = field.val ();

		container.find ('#folders-section').hide ();
		container.find ('#access-token-section').find ('.button-container > .fa').removeClass ('fa-times').removeClass ('fa-check').addClass ('fa-pulse').addClass ('fa-spinner');

		arguments = [
			'module=webmail',
			'action=GetAvailableFolders',
			'Ajax=true',
			'emailaddress=' + encodeURIComponent (emailAddress),
			'accesstoken=' + encodeURIComponent (Base64.encode (accessToken)),
			'hostname=' + encodeURIComponent (hostName),
			'port=' + encodeURIComponent (port),
			'securitytype=' + encodeURIComponent (securityType),
			'service=' + encodeURIComponent (service),
			'authenticationmethod=' + encodeURIComponent (authenticationMethod),
			'usernametype=' + encodeURIComponent (userNameType)
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (onGetAvailableFoldersSuccessHandler).fail (onGetAvailableFoldersFailureHandler);
	};

	var getMailServerSettings = function () {
		var container = jQuery ('#account-settings-container'),
			emailAddress, field, arguments;

		field = container.find ('#email-address');
		emailAddress = field.val ();
		if ((emailAddress === null) || (emailAddress === undefined) || (emailAddress.trim () === '')) {
			alert ('Introduce la dirección de correo');
			field.focus ();
			return;
		}
		container.find ('#server-settings-section').hide ();
		container.find ('#access-token-section').hide ();
		container.find ('#folders-section').hide ();
		container.find ('#email-address-section').find ('.button-container > .fa').removeClass ('fa-times').removeClass ('fa-check').addClass ('fa-pulse').addClass ('fa-spinner');

		arguments = [
			'module=webmail',
			'action=DetectMailServerSettings',
			'Ajax=true',
			'emailaddress=' + encodeURIComponent (emailAddress)
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (onGetMailServerSettingsSuccessHandler).fail (onGetMailServerSettingsFailureHandler);
	};

	var getOauth2Token = function () {
		var container = jQuery ('#account-settings-container'),
			emailAddress, arguments,
			incomingHostName, incomingPort, incomingSecurityType, incomingAuthenticationMethod, incomingUserNameType, incomingService,
			outgoingHostName, outgoingPort, outgoingSecurityType, outgoingAuthenticationMethod, outgoingUserNameType, outgoingService;

		emailAddress = container.find ('#email-address').val ();
		if ((emailAddress === null) || (emailAddress === undefined) || (emailAddress.trim () === '')) {
			alert ('Introduce la dirección de correo');
			return;
		}

		incomingHostName = container.find ('#incoming-host-name').val ();
		if ((incomingHostName === null) || (incomingHostName === undefined) || (incomingHostName.trim () === '')) {
			alert ('Introduce el nombre del servidor');
			return;
		}

		incomingService = container.find ('#incoming-service').val ();
		incomingPort = container.find ('#incoming-port').val ();
		incomingSecurityType = container.find ('#incoming-security-type').val ();
		incomingAuthenticationMethod = container.find ('#incoming-authentication-method').val ();
		incomingUserNameType = container.find ('#incoming-username-type').val ();

		outgoingService = container.find ('#outgoing-service').val ();
		outgoingHostName = container.find ('#outgoing-host-name').val ();
		outgoingPort = container.find ('#outgoing-port').val ();
		outgoingSecurityType = container.find ('#outgoing-security-type').val ();
		outgoingAuthenticationMethod = container.find ('#outgoing-authentication-method').val ();
		outgoingUserNameType = container.find ('#outgoing-username-type').val ();

		arguments = [
			'module=webmail',
			'action=SerializeMailAccount',
			'Ajax=true',
			'emailaddress=' + encodeURIComponent (emailAddress),
			'incominghostname=' + encodeURIComponent (incomingHostName),
			'incomingport=' + encodeURIComponent (incomingPort),
			'incomingsecuritytype=' + encodeURIComponent (incomingSecurityType),
			'incomingservice=' + encodeURIComponent (incomingService),
			'incomingauthenticationmethod=' + encodeURIComponent (incomingAuthenticationMethod),
			'incomingusernametype=' + encodeURIComponent (incomingUserNameType),
			'outgoinghostname=' + encodeURIComponent (outgoingHostName),
			'outgoingport=' + encodeURIComponent (outgoingPort),
			'outgoingsecuritytype=' + encodeURIComponent (outgoingSecurityType),
			'outgoingservice=' + encodeURIComponent (outgoingService),
			'outgoingauthenticationmethod=' + encodeURIComponent (outgoingAuthenticationMethod),
			'outgoingusernametype=' + encodeURIComponent (outgoingUserNameType)
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			var redirectTo = 'index.php?module=webmail&action=AccountEditView',
				arguments;

			arguments = [
				'module=OAuth2Manager',
				'action=GetToken',
				'Popup=true',
				'redirectto=' + encodeURIComponent (redirectTo),
				'hostname=' + encodeURIComponent (incomingHostName)
			];
			window.location.href = 'index.php?' + arguments.join ('&');
		}).fail (function () {
			alert ('Se ha presentado un error inesperado. Intenta más tarde');
		});
	};

	var openEmailViewerModal = function (buttonElement) {
		var button      = jQuery (buttonElement),
			container   = button.closest ('.conversation-item'),
			messageId   = container.data ('id'),
			account     = container.data ('account'),
            messagesTab = jQuery('#home-MESSAGES'),
			totalMsnTab = 0,
			arguments;

		if ((!jQuery.isNumeric (messageId)) || ((account === null) || (account === undefined) || (account.trim () === ''))) {
			alert ('Se ha presentadoun error inesperado. Intenta más tarde');
			return;
		}

		arguments = [
			'module=webmail',
			'action=GetMailMessageData',
			'record=' + encodeURIComponent (messageId),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			if (button.hasClass ('unread-email')) {
				button.removeClass ('unread-email');
                totalMsnTab = parseInt (messagesTab.html ());
                if (totalMsnTab > 2) {
                    totalMsnTab -= 1;
                    messagesTab.html (totalMsnTab);
				} else if (!messagesTab.hasClass('hide')) {
                	messagesTab.addClass ('hide');
				}
                setCountMain ();
			}
			var modalTemplate = jQuery ('#email-viewer-modal-template');

			if ((response === null) || (response === undefined)) {
				alert ('Se ha recibido una respuesta inesperada del servidor. Intenta más tarde');
				return;
			}

			modal = jQuery (modalTemplate.html ());
			fillEmailViewerData (modal, response);
			fillEmailComposerData (modal, response, account);

			modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
		}).fail (onGenericFailureHandler);
	};

	var openRelatedEntityModal = function (buttonElement) {
		var button            = jQuery (buttonElement),
			relatedEntityRow  = button.closest ('.related-entity-row'),
			moduleNameElement = relatedEntityRow.find ('.module-name'),
			displayFieldId    = relatedEntityRow.find ('.display-field').attr ('id'),
			dataFieldId       = relatedEntityRow.find ('.data-field').attr ('id'),
			moduleName        = moduleNameElement.val (),
			moduleLabel       = moduleNameElement.find ('option:selected').text ();

		if ((moduleName === undefined) || (moduleName === null) || (moduleName.trim () === '')) {
			alert ('Selecciona el módulo');
			moduleNameElement.focus ();
			return false;
		}

		button.attr ('data-current-module', 'Calendar');
		button.attr ('data-display-field-id', displayFieldId);
		button.attr ('data-field-id', dataFieldId);
		button.attr ('data-referenced-module', moduleName);
		button.attr ('data-title', moduleLabel);

		RelatedModuleModalUtils.openModal (buttonElement);
	};

	var relateEntities = function (formElement) {
		var form = jQuery (formElement);

		if (!validateRelatedEntities (form)) {
			return;
		}

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (function () {
			alert ('Se ha relacionado el correo');
			searchEmailMessages (jQuery ('form.filters-form'));
		}).fail (onGenericFailureHandler);
	};

	var searchEmailMessages = function (formElement) {
		var form = jQuery (formElement);

		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'html',
			method:   'get'
		}).done (onSearchEmailMessagesSuccessHandler).fail (onGenericFailureHandler);
	};

	var sendEmailMessage = function (formElement) {
		var form = jQuery (formElement);

		CKEDITOR.instances [ 'body' ].updateElement ();
		jQuery.ajax ('index.php', {
			data:     form.serialize (),
			dataType: 'json',
			method:   'post'
		}).done (onSendEmailMessageSuccessHandler).fail (onGenericFailureHandler);
	};

	var setDatePeriod = function () {
        var select= jQuery ('#emails-tab-period');
        setFilterPeriod (select, 1);
    };

	var setEmailType = function (selectElement) {
		var select = jQuery (selectElement),
			form   = select.closest ('form');

		form.submit ();
	};

	var setFilterPeriod = function (selectElement, callFron) {
		var select        = jQuery (selectElement),
			form          = select.closest ('form'),
			from          = form.find ('.from-field'),
			DateFrom      = new Date (from.val ()),
			lastDate      = select.data ('last-time'),
            DateLastDate  = new Date (lastDate),
			to            = form.find ('.to-field'),
			DateTo        = new Date (to.val ()),
			helpMsn       = jQuery ('#emails-tab-help'),
			DateToday     = new Date (select.data ('today'));
		from.datepicker ('remove');
		to.datepicker ('remove');
		helpMsn.html('');
		if (select.val () === 'CUSTOMIZE' ) {
			if (!callFron) {
                from.val (lastDate);
                helpMsn.html('Seleccione las fechas');
			} else if ((DateFrom.getTime() < DateLastDate.getTime()) || (DateFrom.getTime() > DateTo.getTime())) {
                from.val (lastDate);
			}
			if (DateFrom.getTime() > DateTo.getTime()) {
				if (DateToday.getTime() < DateTo.getTime()) {
					DateTo = new Date (select.data ('today'));
				}
				from.val (DateTo.getFullYear () + '-' + DateTo.getMonth () + '-' + DateTo.getDate ());
                to.val (DateTo.getFullYear () + '-' + (DateTo.getMonth () + 1) + '-' + DateTo.getDate ());
			}

            from.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
            to.datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
		} else {
            from.val (select.val ());
            to.val (select.data ('today'));
		}

        form.submit ();
	};

	var testMailServerSettings = function () {
		var container = jQuery ('#account-settings-container'),
			arguments, field, value;

		arguments = [
			'module=webmail',
			'action=TestMailServerSettings',
			'Ajax=true'
		];

		field = container.find ('#incoming-host-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre del servidor de correo de entrada');
			field.focus ();
			return;
		}
		arguments.push ('incominghostname=' + encodeURIComponent (value));

		field = container.find ('#incoming-port');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el puerto de correo de entrada');
			field.focus ();
			return;
		}
		arguments.push ('incomingport=' + encodeURIComponent (value));

		field = container.find ('#incoming-security-type');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el tipo de seguridad del correo de entrada');
			field.focus ();
			return;
		}
		arguments.push ('incomingsecuritytype=' + encodeURIComponent (value));

		field = container.find ('#incoming-authentication-method');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el mecanismo de autenticación del correo de entrada');
			field.focus ();
			return;
		}
		arguments.push ('incomingauthenticationmethod=' + encodeURIComponent (value));

		container.find ('#incoming-service').val ('imap');
		arguments.push ('incomingservice=imap');

		container.find ('#incoming-username-type').val ('%emailaddress%');
		arguments.push ('incomingusernametype=' + encodeURIComponent ('%emailaddress%'));

		field = container.find ('#outgoing-host-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre del servidor de correo de salida');
			field.focus ();
			return;
		}
		arguments.push ('outgoinghostname=' + encodeURIComponent (value));

		field = container.find ('#outgoing-port');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el puerto de correo de salida');
			field.focus ();
			return;
		}
		arguments.push ('outgoingport=' + encodeURIComponent (value));

		field = container.find ('#outgoing-security-type');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el tipo de seguridad del correo de salida');
			field.focus ();
			return;
		}
		arguments.push ('outgoingsecuritytype=' + encodeURIComponent (value));

		field = container.find ('#outgoing-authentication-method');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el mecanismo de autenticación del correo de salida');
			field.focus ();
			return;
		}
		arguments.push ('outgoingauthenticationmethod=' + encodeURIComponent (value));

		container.find ('#outgoing-service').val ('smtp');
		arguments.push ('outgoingservice=smtp');

		container.find ('#outgoing-username-type').val ('%emailaddress%');
		arguments.push ('outgoingusernametype=' + encodeURIComponent ('%emailaddress%'));

		container.find ('#server-settings-section .button-container > .fa').removeClass ('fa-pulse').removeClass ('fa-check').removeClass ('fa-times').addClass ('fa-spinner');
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (onTestMailServerSettingsSuccessHandler)
			  .fail (onTestMailServerSettingsFailureHandler);
	};

	var validateEmailAccount = function (form) {
		var field, value;

		field = form.find ('#email-address');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce la dirección de correo');
			field.focus ();
			return false;
		}

		field = form.find ('#incoming-host-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce el nombre del servidor');
			field.focus ();
			return false;
		}

		field = form.find ('#incoming-port');
		value = field.val ();
		if ((value === null) || (value === undefined) || (!jQuery.isNumeric (value)) || (parseInt (value) < 1) || (parseInt (value) > 65535)) {
			alert ('Introduce un número de puerto válido');
			field.focus ();
			return false;
		}

		field = form.find ('#incoming-security-type');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el tipo de seguridad');
			field.focus ();
			return false;
		}

		field = form.find ('#incoming-authentication-method');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona el mecanismo de autenticación');
			field.focus ();
			return false;
		}

		if (value === 'oauth2') {
			field = form.find ('#incoming-access-token');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Debes autorizarnos para acceder a tu correo electrónico');
				field.focus ();
				return false;
			}
		} else {
			field = form.find ('#email-password');
			value = field.val ();
			if ((value === null) || (value === undefined) || (value.trim () === '')) {
				alert ('Introduce la contraseña');
				field.focus ();
				return false;
			}
		}

		field = form.find ('#incoming-folder-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona la carpeta de correos entrantes');
			field.focus ();
			return false;
		}

		field = form.find ('#outgoing-folder-name');
		value = field.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Selecciona la carpeta de correos salientes');
			field.focus ();
			return false;
		}

		return true;
	};

	var viewEmail = function () {
		modal.find ('#email-composer').hide ();
		modal.find ('#email-viewer').show ();
	};

	window.WebmailUtils = {
		addRelatedEntityRow:      addRelatedEntityRow,
		clearRelatedEntityFields: clearRelatedEntityFields,
		composeEmail:             composeEmail,
		deleteRelatedEntity:      deleteRelatedEntity,
		enableSaveButton:         enableSaveButton,
		getAvailableFolders:      getAvailableFolders,
		getMailServerSettings:    getMailServerSettings,
		getOauth2Token:           getOauth2Token,
		openEmailViewerModal:     openEmailViewerModal,
		openRelatedEntityModal:   openRelatedEntityModal,
		relateEntities:           relateEntities,
		searchEmailMessages:      searchEmailMessages,
		sendEmailMessage:         sendEmailMessage,
        setDatePeriod:            setDatePeriod,
		setEmailType:             setEmailType,
		setFilterPeriod:          setFilterPeriod,
		testMailServerSettings:   testMailServerSettings,
		validateEmailAccount:     validateEmailAccount,
		viewEmail:                viewEmail
	};

    var onDocumentReadyHandler = function () {
        jQuery  ('#emails-tab-from').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
        jQuery  ('#emails-tab-to').datepicker ({ format: 'yyyy-mm-dd', language: 'es', weekStart: 1 });
    };
	var stopPropagation = function (evt) {
		evt.stopPropagation ();
	};

	jQuery (document).on ('click', '.conversation-body.email', function () {
		openEmailViewerModal (this);
	});
	jQuery (document).on ('click', '.conversation-body.email .actions', stopPropagation);
	jQuery (document).on ('click', '.conversation-body.email .related-entities', stopPropagation);
    jQuery (document).ready (onDocumentReadyHandler);
} (jQuery));
