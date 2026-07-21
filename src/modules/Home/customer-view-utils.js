(function (jQuery) {
	var modal = null;

	var changeSubscriptionPlan = function (billingPlanId) {
		var arguments;

		jQuery ('body').addClass ('loading');
		arguments = [
			'module=store',
			'action=ChangeSubscriptionBillingPlan',
			'billingplanid=' + encodeURIComponent (billingPlanId),
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
			window.location.href = 'index.php?module=Home&action=CustomerView&tab=subscription';
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
			d = dummy [2],
			m = dummy [1],
			y = dummy [0];
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

	var cancelSubscription = function () {
		return confirm ('¿Estás seguro que quieres cancelar tu suscripción y darte de baja? Esta operación no es reversible, y se perderán todos tus datos');
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
			window.location.href = 'index.php?module=Home&action=CustomerView&tab=subscription';
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

	var deletePaymentMethod = function () {
		return confirm ('¿Estás seguro que quieres eliminar el método de pago seleccionado?');
	};

	var chargeDefaultPaymentMethod = function () {
		var totalDebt = jQuery ('#totaldebt').text ();
		return confirm ('Se intentará realizar un cargo a tu método de pago activo por la cantidad de ' + totalDebt + ' EUR. ¿Estás de acuerdo?');
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

	var subscribeApplication = function (applicationCode, applicationName, totalPaymentMethods) {
		var arguments;
		if (!totalPaymentMethods) {
			if (confirm ('Para poder suscribirte debes dar la alta un método de pago. ¿Desea dar de alta alguno?')) {
				window.location.href = 'index.php?module=Home&action=ViewSubscriptionDetails&tab=payment-methods';
			}
			return;
		} else if (!confirm ('¿Estás seguro que quieres agregar la aplicación ' + applicationName + ' a tu suscripción?')) {
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
			window.location.href = 'index.php?module=Home&action=CustomerView&tab=subscription';
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
			window.location.href = 'index.php?module=Home&action=CustomerView&tab=subscription';
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

	var validatePlanChange = function (buttonElement) {
		var button           = jQuery (buttonElement),
			form             = button.closest ('form'),
			oldBillingPlanId = parseInt (form.find ('input#change-billing-plan-modal-old-billing-plan-id').val ()),
			field, value, arguments;

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

		jQuery ('body').addClass ('loading');
		arguments = [
			'module=store',
			'action=CalculateSubscriptionPayments',
			'billingplanid=' + encodeURIComponent (value),
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
			message += 'Valor con impuesto: ' + formatNumber (subscription.amountwithtax, 2, ',', '.') + ' EUR\n\n';
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
			changeSubscriptionPlan (value);
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

	var validatePassword = function () {
		var password, confirmation, value;
		password = jQuery ('#new_password');
		value = password.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce la nueva contraseña');
			password.focus ();
			return false;
		}
		confirmation = jQuery ('#confirm_new_password');
		value = confirmation.val ();
		if ((value === null) || (value === undefined) || (value.trim () === '')) {
			alert ('Introduce nuevamente la nueva contraseña');
			confirmation.focus ();
			return false;
		}
		if (password.val () !== confirmation.val ()) {
			alert ('Las contraseñas no coinciden');
			confirmation.focus ();
			return false;
		}
		return true;
	};

	window.CustomerViewUtils = {
		cancelSubscription:         cancelSubscription,
		chargeDefaultPaymentMethod: chargeDefaultPaymentMethod,
		deleteApplication:          deleteApplication,
		deletePaymentMethod:        deletePaymentMethod,
		openChangeBillingPlanModal: openChangeBillingPlanModal,
		setDefaultPaymentMethod:    setDefaultPaymentMethod,
		subscribeApplication:       subscribeApplication,
		unsubscribeApplication:     unsubscribeApplication,
		validatePlanChange:         validatePlanChange,
		validatePassword:           validatePassword
	};
} (jQuery));