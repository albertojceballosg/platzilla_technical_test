(function (jQuery) {
	var modal = null;

	var changeSubscriptionPlan = function (billingPlanId, numUser) {
		var arguments;

		jQuery ('body').addClass ('loading');
		arguments = [
			'module=store',
			'action=ChangeSubscriptionBillingPlan',
			'billingplanid=' + encodeURIComponent (billingPlanId),
            'numusers=' + numUser,
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			jQuery ('body').removeClass ('loading');
			alert (response);
			modal.modal ('hide');
			window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails';
		}).fail (function (jQueryResponse) {
			var responseText;

			try {
				responseText = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				responseText = 'Se ha recibido una respuesta inesperada';
			}
			jQuery ('body').removeClass ('loading');
			alert (responseText);
		});
	};

	var destroyModal = function () {
		if (modal === null) {
			return;
		}

		jQuery (this).remove ();
		modal = null;
	};

	var formatDateString = function (dateString) {
		var dummy = dateString.split ('-'),
			d     = dummy [ 2 ],
			m     = dummy [ 1 ],
			y     = dummy [ 0 ];
		return d + '/' + m + '/' + y;
	};

	var formatNumber = function (number, decimals, decimalPoint, thousandsSeparator) {
		// Strip all characters but numerical ones.
		number = (number + '').replace (/[^0-9+\-Ee.]/g, '');
		var n          = !isFinite (+number) ? 0 : +number,
			prec       = !isFinite (+decimals) ? 0 : Math.abs (decimals),
			sep        = (typeof thousandsSeparator === 'undefined') ? ',' : thousandsSeparator,
			dec        = (typeof decimalPoint === 'undefined') ? '.' : decimalPoint,
			s,
			toFixedFix = function (n, prec) {
				var k = Math.pow (10, prec);
				return '' + Math.round (n * k) / k;
			};
		// Fix for IE parseFloat(0.55).toFixed(0) = 0;
		s = (prec ? toFixedFix (n, prec) : '' + Math.round (n)).split ('.');
		if (s[ 0 ].length > 3) {
			s[ 0 ] = s[ 0 ].replace (/\B(?=(?:\d{3})+(?!\d))/g, sep);
		}
		if ((s[ 1 ] || '').length < prec) {
			s[ 1 ] = s[ 1 ] || '';
			s[ 1 ] += new Array (prec - s[ 1 ].length + 1).join ('0');
		}
		return s.join (dec);
	};

	var loadApplicationsInCart = function (response, applicationImagesUri) {
		var applications = response.applications,
			n            = jQuery.isArray (applications) ? applications.length : 0,
			i;

		jQuery ('#list_items_ready').empty ();
		jQuery ('.app-icon').removeClass ('app-checked');
		jQuery ('.btn-add').removeClass ('hidden');
		jQuery ('.btn-remove').addClass ('hidden');
		jQuery ('#error_totalapps').html ('');
		jQuery ('#required_app').hide ();
		jQuery ('#error_totalapps2').html ('');
		jQuery ('#added_apps').val (n);
		for (i = 0; i < n; i += 1) {
			jQuery (
				'<li id="item_ready_' + applications [ i ].config_applicationsid + '" style="margin-right: 5px; margin-left: 5px;">' +
				'<div style="text-align: center; width: 200px;">' +
				'<img src="' + applicationImagesUri + '/' + applications [ i ].app_code + '.png" alt="" id="image_app_' + applications [ i ].config_applicationsid + '" style="border-radius: 50%;">' +
				'<div>' +
				'<h4>' + applications [ i ].app_name + '</h4>' +
				'</div>' +
				'</div>' +
				'</li>'
			).hide ().prependTo ('#list_items_ready').fadeIn ('fast');
			jQuery ('.addApp_' + applications [ i ].config_applicationsid).addClass ('app-checked');
			jQuery ('.btn-add[data-application-id="' + applications [ i ].config_applicationsid + '"]').addClass ('hidden').next ('.btn-remove').removeClass ('hidden');
		}
	};

	var onCartOperationFailureHandler = function (jQueryResponse) {
		alert (jQueryResponse.responseText);
		console.error (jQueryResponse.responseText);
	};

	var addApplication = function (applicationCode) {
		var arguments;
		if (!confirm ('¿Estás seguro que quieres instalar la aplicación seleccionada?')) {
			return;
		}
		jQuery ('body').addClass ('loading');

		arguments = [
			'module=store',
			'action=AddApplication',
			'applicationcode=' + encodeURIComponent (applicationCode),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			alert (response);
			jQuery ('body').removeClass ('loading');
			window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails';
		}).fail (function (jQueryResponse) {
			alert (jQueryResponse.responseText);
			jQuery ('body').removeClass ('loading');
			console.error (jQueryResponse.responseText);
		});
	};

	var addApplicationToCart = function (buttonElement, applicationImagesUri) {
		var button        = jQuery (buttonElement),
			applicationId = button.attr ('data-application-id'),
			arguments;
		arguments = [
			'module=store',
			'action=AddApplicationToCart',
			'applicationid=' + encodeURIComponent (applicationId),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			loadApplicationsInCart (response, applicationImagesUri);
		}).fail (onCartOperationFailureHandler);
	};

	var calculatePrice = function (obj) {
		var objUsers      = jQuery (obj),
			costByUser    = parseFloat (objUsers.attr ('data-cost')),
            selectedUsers = parseInt (objUsers.val ()),
			total         = 0;
		total = (costByUser * selectedUsers).toFixed (2);
		objUsers.parent ().parent().find ('.col-price').html (total.toString () + ' EUR')
	};

	var cancelSubscription = function () {
		return confirm ('¿Estás seguro que quieres cancelar tu suscripción y darte de baja? Esta operación no es reversible, y se perderán todos tus datos');
	};

	var createInstance = function () {
		if (!validateRegisterForm ()) {
			return false;
		} else {
			var interval, i, messages;

			jQuery ('body').css ({
				overflow: 'hidden'
			});
			jQuery ('#clock').show ();
			messages = [
				'Recopilando información necesaria',
				'Instalando módulos',
				'Instalando aplicaciones',
				'Configurando permisologías',
				'Personalizando la nueva cuenta',
				'Ejecutando limpieza final',
				'Preparando para iniciar por primera vez'
			];
			i = 0;
			interval = setInterval (function () {
				jQuery ('#clock').find ('.message').text (messages [ i ]);
				i += 1;
				if (i === 7) {
					clearInterval (interval);
				}
			}, Math.random () * (10000 - 7000) + 7000);
			return true;
		}
	};

	var deleteApplication = function (applicationCode, applicationName) {
		var arguments;
		if (!confirm ('¿Estás seguro que quieres eliminar la aplicación ' + applicationName + ' de tu instancia?')) {
			return;
		}

		jQuery ('body').addClass ('loading');
		arguments = [
			'module=store',
			'action=DeleteApplication',
			'applicationcode=' + encodeURIComponent (applicationCode),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			jQuery ('body').removeClass ('loading');
			alert (response);
			window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails';
		}).fail (function (jQueryResponse) {
			var responseText;
			try {
				responseText = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				responseText = 'Se ha recibido una respuesta inesperada';
			}
			jQuery ('body').removeClass ('loading');
			alert (responseText);
		});
	};

	var deleteApplicationFromCart = function (buttonElement, applicationImagesUri) {
		var button        = jQuery (buttonElement),
			applicationId = button.attr ('data-application-id'),
			arguments;
		arguments = [
			'module=store',
			'action=DeleteApplicationFromCart',
			'applicationid=' + encodeURIComponent (applicationId),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			loadApplicationsInCart (response, applicationImagesUri);
		}).fail (onCartOperationFailureHandler);
	};

	var deletePaymentMethod = function () {
		return confirm ('¿Estás seguro que quieres eliminar el método de pago seleccionado?');
	};

	var openChangeBillingPlanModal = function (buttonElement, totalPaymentMethods) {
		var modalTemplate = jQuery ('#change-billing-plan-modal-template');

		if (!totalPaymentMethods) {
			if (confirm ('Para poder suscribirte debes dar la alta un método de pago. ¿Desea dar de alta alguno?')) {
				window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails&tab=payment-methods';
			}
			return;
		}

		modal = jQuery (modalTemplate.html ());
		modal.modal ({ backdrop: 'static' }).on ('hidden.bs.modal', destroyModal);
	};

	var setDefaultPaymentMethod = function () {
		return confirm ('¿Estás seguro que quieres establecer el método de pago seleccionado como el método de pago por defecto?');
	};

	var subscribeApplication = function (applicationCode, applicationName, totalPaymentMethods, maxApplications, subscribedApplications) {
		var arguments, message;
		if (!totalPaymentMethods) {
			if (confirm ('Para poder suscribirte debes dar la alta un método de pago. ¿Desea dar de alta alguno?')) {
				window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails&tab=payment-methods';
			}
			return;
		}

		if (maxApplications != -1) {
			message = 'Te queda espacio para instalar ' + (maxApplications - subscribedApplications) + ' aplicaciones. ';
		} else {
			message = '';
		}
		if (!confirm (message + '¿Estás seguro que quieres agregar la aplicación ' + applicationName + ' a tu suscripción?')) {
			return;
		}

		jQuery ('body').addClass ('loading');
		arguments = [
			'module=store',
			'action=SubscribeApplication',
			'applicationcode=' + encodeURIComponent (applicationCode),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			jQuery ('body').removeClass ('loading');
			alert (response);
			window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails';
		}).fail (function (jQueryResponse) {
			var responseText;
			try {
				responseText = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				responseText = 'Se ha recibido una respuesta inesperada';
			}
			jQuery ('body').removeClass ('loading');
			alert (responseText);
		});
	};

	var unsubscribeApplication = function (applicationCode, applicationName) {
		var arguments;
		if (!confirm ('¿Estás seguro que quieres cancelar la aplicación ' + applicationName + ' de tu suscripción?')) {
			return;
		}

		jQuery ('body').addClass ('loading');
		arguments = [
			'module=store',
			'action=UnsubscribeApplication',
			'applicationcode=' + encodeURIComponent (applicationCode),
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'post'
		}).done (function (response) {
			jQuery ('body').removeClass ('loading');
			alert (response);
			window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails';
		}).fail (function (jQueryResponse) {
			var responseText;
			try {
				responseText = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				responseText = 'Se ha recibido una respuesta inesperada';
			}
			jQuery ('body').removeClass ('loading');
			alert (responseText);
		});
	};

	var validateCart = function () {
		var totalPrice = jQuery ('#totalprice').val ();
		if (!totalPrice) {
			jQuery ('#error').html ('Elige al menos una aplicación');
			return false;
		} else {
			return true;
		}
	};

	var validatePlanChange = function (buttonElement) {
		var button           = jQuery (buttonElement),
			form             = button.closest ('form'),
			oldBillingPlanId = parseInt (form.find ('input#change-billing-plan-modal-old-billing-plan-id').val ()),
			field, numUser, value, arguments;

		field = form.find ('input[name="billingplanid"]:checked');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Selecciona el plan');
			field.focus ();
			return false;
		} else if (value === oldBillingPlanId) {
			alert ('Seleccionaste tu plan actual');
			field.focus ();
			return false;
		}
        numUser = parseInt (form.find ('select[name="numusers_' + value + '"]').val ());

		jQuery ('body').addClass ('loading');
		arguments = [
			'module=store',
			'action=CalculateSubscriptionPayments',
			'billingplanid=' + encodeURIComponent (value),
			'numusers=' + numUser,
			'Ajax=true'
		];
		jQuery.ajax ('index.php', {
			data:     arguments.join ('&'),
			dataType: 'json',
			method:   'get'
		}).done (function (response) {
			var subscription, proratedPayment, nextMonthSubscription, message;

			jQuery ('body').removeClass ('loading');
			if ((!response) || (!response.hasOwnProperty ('subscription'))) {
				alert ('Se ha presentado un error. Intenta más tarde');
				return;
			}

			proratedPayment = response.proratedpayment;
			subscription = response.subscription;
			nextMonthSubscription = response.nextmonthsubscription;
			message = 'Antes de continuar es necesario que sepas lo siguiente:\n\n';
			if (proratedPayment) {
				message += 'Al suscribirte al plan seleccionado debemos realizar un cargo NO REEMBOLSABLE a tu tarjeta de crédito con las siguientes características:\n';
				message += 'Concepto: Actualización de suscripción a Platzilla\n';
				message += 'Fecha de inicio: ' + formatDateString (proratedPayment.from) + '\n';
				message += 'Fecha de fin: ' + formatDateString (proratedPayment.to) + '\n';
				message += 'Valor sin impuesto: ' + formatNumber (proratedPayment.amountwithouttax, 2, ',', '.') + ' EUR\n';
				message += 'Impuesto (' + formatNumber (proratedPayment.taxpercentage, 2, ',', '.') + ' %): ' + formatNumber (proratedPayment.taxamount, 2, ',', '.') + ' EUR\n';
				message += 'Valor con impuesto: ' + formatNumber (proratedPayment.amountwithtax, 2, ',', '.') + ' EUR\n\n';
			}
			message += 'Tu suscripción mensual a Platzilla quedará de la siguiente forma:\n';
			message += 'Fecha de inicio: ' + formatDateString (subscription.from) + '\n';
			message += 'Fecha de fin: ' + formatDateString (subscription.to) + '\n';
			message += 'Valor sin impuesto: ' + formatNumber (subscription.amountwithouttax, 2, ',', '.') + ' EUR\n';
			message += 'Impuesto (' + formatNumber (subscription.taxpercentage, 2, ',', '.') + ' %): ' + formatNumber (subscription.taxamount, 2, ',', '.') + ' EUR\n';
			message += 'Valor con impuesto: ' + formatNumber (subscription.amountwithtax, 2, ',', '.') + ' EUR\n';
            message += 'Total  usuarios suscritos: ' + subscription.totalUsers + '\n\n';
			if (nextMonthSubscription) {
				message += 'Una vez alcanzada la fecha de fin, tu suscripción a Platzilla quedará de la siguiente forma:\n';
				message += 'Valor sin impuesto: ' + formatNumber (nextMonthSubscription.amountwithouttax, 2, ',', '.') + ' EUR\n';
				message += 'Impuesto (' + formatNumber (nextMonthSubscription.taxpercentage, 2, ',', '.') + ' %): ' + formatNumber (nextMonthSubscription.taxamount, 2, ',', '.') + ' EUR\n';
				message += 'Valor con impuesto: ' + formatNumber (nextMonthSubscription.amountwithtax, 2, ',', '.') + ' EUR\n\n';
			}
			message += '¿Deseas continuar?';
			if (!confirm (message)) {
				return;
			}
			changeSubscriptionPlan (value, numUser);
		}).fail (function (jQueryResponse) {
			var responseText;

			try {
				responseText = JSON.parse (jQueryResponse.responseText);
			} catch (e) {
				responseText = 'Se ha recibido una respuesta inesperada';
			}
			jQuery ('body').removeClass ('loading');
			alert (responseText);
		});
	};

	var validateRegisterForm = function () {
		var errorName            = jQuery ('#error_name'),
			errorLastName        = jQuery ('#error_lastname'),
			errorEmail           = jQuery ('#error_email'),
			errorPassword        = jQuery ('#error_password'),
			errorPasswordConfirm = jQuery ('#error_password_confirm'),
			errorRGPDstep2       = jQuery ('#error_RGPDstep2'),
			regex, field, passwordField;

		errorName.html ('');
		errorLastName.html ('');
		errorEmail.html ('');
		errorPassword.html ('');
		errorPasswordConfirm.html ('');
		errorRGPDstep2.html ('');
		field = jQuery ('#name');
		if (!field.val ()) {
			errorName.html ('Debes suministrar tu nombre');
			field.trigger ('focus');
			return false;
		}

		field = jQuery ('#lastname');
		if (!field.val ()) {
			errorLastName.html ('Debes suministrar tu apellido');
			field.trigger ('focus');
			return false;
		}
		field = jQuery ('#usuarioEmail');
		if (!field.val ()) {
			errorEmail.html ('Debes suministrar tu dirección de correo electrónico');
			field.trigger ('focus');
			return false;
		}

		regex = /^(([^<>()[\]\.,;:\s@\"]+(\.[^<>()[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
		if (!regex.test (field.val ())) {
			errorEmail.html ('Debes suministrar una dirección de correo válida');
			field.trigger ('focus');
			return false;
		}

		passwordField = jQuery ('#clave');
		if (!passwordField.val ()) {
			errorPassword.html ('Debes suministrar tu contraseña');
			field.trigger ('focus');
			return false;
		}

		field = jQuery ('#claveConfirm');
		if (!field.val ()) {
			errorPasswordConfirm.html ('Debes suministrar la confirmación de tu contraseña');
			field.trigger ('focus');
			return false;
		}

		if (passwordField.val () !== field.val ()) {
			jQuery ('claveConfirm').trigger ('focus');
			errorPasswordConfirm.html ('Las contraseñas no coinciden');
			field.trigger ('focus');
			return false;
		}

		field = jQuery ('#RGPDstep2');
		if (!field.is (':checked')) {
			errorRGPDstep2.html ('Debes aceptar nuestra Política de Privacidad');
			field.trigger ('focus');
			return false;
		}
		return true;
	};

	window.StoreUtils = {
		addApplication:             addApplication,
		addApplicationToCart:       addApplicationToCart,
        calculatePrice:             calculatePrice,
		cancelSubscription:         cancelSubscription,
		createInstance:             createInstance,
		deleteApplication:          deleteApplication,
		deleteApplicationFromCart:  deleteApplicationFromCart,
		deletePaymentMethod:        deletePaymentMethod,
		openChangeBillingPlanModal: openChangeBillingPlanModal,
		setDefaultPaymentMethod:    setDefaultPaymentMethod,
		subscribeApplication:       subscribeApplication,
		unsubscribeApplication:     unsubscribeApplication,
		validateCart:               validateCart,
		validatePlanChange:         validatePlanChange,
		validateRegisterForm:       validateRegisterForm
	};
} (jQuery));
