(function (jQuery) {
	var hostedFieldsInstance = null;

	var onCardTypeChangeHandler = function (evt) {
		// Handle a field's change, such as a change in validity or credit card type
		if (evt.cards.length === 1) {
			jQuery ('#cardtype').text (evt.cards [ 0 ].niceType);
		} else {
			jQuery ('#cardtype').text ('Tarjeta');
		}
	};

	var onHostedFieldsValidityChangeHandler = function (evt) {
		var field = evt.fields [ evt.emittedBy ];
		if (field.isValid) {
			if ((evt.emittedBy === 'expirationMonth') || (evt.emittedBy === 'expirationYear')) {
				if ((!evt.fields.expirationMonth.isValid) || (!evt.fields.expirationYear.isValid)) {
					return;
				}
			} else if (evt.emittedBy === 'number') {
				jQuery ('#cardnumber').next ('span').text ('');
			}

			// Remove any previously applied error or warning classes. Apply styling for a valid field
			jQuery (field.container).parents ('.form-group').removeClass ('has-warning').removeClass ('has-success').addClass ('has-success');
		} else if (field.isPotentiallyValid) {
			// Remove styling  from potentially valid fields
			jQuery (field.container).parents ('.form-group').removeClass ('has-warning').removeClass ('has-success');
			if (evt.emittedBy === 'number') {
				jQuery ('#cardnumber').next ('span').text ('');
			}
		} else {
			// Add styling to invalid fields
			jQuery (field.container).parents ('.form-group').addClass ('has-warning');
			// Add helper text for an invalid card number
			if (evt.emittedBy === 'number') {
				jQuery ('#cardnumber').next ('span').text ('El número de tarjeta suministrado no es válido');
			}
		}
	};

	var onCreateBraintreeHostedFieldsHandler = function (error, hostedFields) {
		if (error) {
			alert ('Se ha presentado un error. Intenta más tarde');
			console.error (error);
			return;
		}
		hostedFieldsInstance = hostedFields;
		hostedFieldsInstance.on ('validityChange', onHostedFieldsValidityChangeHandler);
		hostedFieldsInstance.on ('cardTypeChange', onCardTypeChangeHandler);
		jQuery ('#paymentmethodcontainer').removeClass ('hidden');
	};

	var createBraintreeHostedFields = function (clientInstance) {
		var options = {
			client: clientInstance,
			styles: {
				'input':  {
					'font-size':   '13px',
					'font-family': '"Open Sans", sans-serif',
					'color':       '#555'
				},
				':focus': {
					'outline': '0'
				},
				'select': {
					'font-size':   '13px',
					'font-family': '"Open Sans", sans-serif',
					'color':       '#555'
				}
			},
			fields: {
				number:          {
					selector: '#cardnumber'
				},
				cvv:             {
					selector: '#cvv'
				},
				expirationMonth: {
					selector:    '#expirationmonth',
					placeholder: 'Mes',
					select:      true
				},
				expirationYear:  {
					selector:    '#expirationyear',
					placeholder: 'Año',
					select:      true
				}
			}
		};
		braintree.hostedFields.create (options, onCreateBraintreeHostedFieldsHandler);
	};

	var onCreateBraintreeClientHandler = function (error, clientInstance) {
		if (error) {
			alert ('Se ha presentado un error. Intenta más tarde');
			console.error (error);
			return;
		}

		createBraintreeHostedFields (clientInstance);
	};

	var initializeBraintreeHostedFields = function () {
		var token   = jQuery ('#paymentmethodcontainer').attr ('data-token'),
			options = {
				authorization: token
			};

		if (!token) {
			alert ('No se ha configurado el token de la plataforma de pagos. Notifica al administrador de la aplicación');
			return;
		}

		braintree.client.create (options, onCreateBraintreeClientHandler);
	};

	var selectBillingAddress = function (addressIdElement) {
		var addressId = jQuery (addressIdElement).val (),
			selectedAddress;
		if (!addressId) {
			jQuery ('.billingaddressfield').val ('').removeAttr ('disabled');
		} else {
			selectedAddress = jQuery (addressIdElement).find ('option:selected');
			jQuery ('.billingaddressfield').attr ('disabled', 'disabled');
			jQuery ('input[name="firstname"]').val (selectedAddress.attr ('data-firstname'));
			jQuery ('input[name="lastname"]').val (selectedAddress.attr ('data-lastname'));
			jQuery ('input[name="company"]').val (selectedAddress.attr ('data-company'));
			jQuery ('input[name="streetaddress"]').val (selectedAddress.attr ('data-streetaddress'));
			jQuery ('input[name="extendedaddress"]').val (selectedAddress.attr ('data-extendedaddress'));
			jQuery ('input[name="city"]').val (selectedAddress.attr ('data-city'));
			jQuery ('input[name="state"]').val (selectedAddress.attr ('data-state'));
			jQuery ('input[name="zipcode"]').val (selectedAddress.attr ('data-zipcode'));
			jQuery ('select[name="countrycode"]').val (selectedAddress.attr ('data-countrycode'));
		}
	};

	var validatePaymentForm = function (formElement) {
		var form  = jQuery (formElement),
			nonce = form.find ('input[name="nonce"]'),
			hasCC = form.find ('input[name="hasCredidcart"]'),
			cardholderName, postalCode, field, value;
		if (nonce.val () || (hasCC.val () === '1')) {
			return true;
		}

		var state = hostedFieldsInstance.getState ();

		field = state.fields.number;
		if (field.isEmpty) {
			alert ('Introduce el número de la tarjeta');
			hostedFieldsInstance.focus ('number');
			return false;
		} else if (!field.isValid) {
			alert ('El número de tarjeta suministrado no es válido');
			hostedFieldsInstance.focus ('number');
			return false;
		}

		cardholderName = jQuery ('#cardholderName');
		value = cardholderName.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el nombre del titular de la tarjeta');
			cardholderName.focus ();
			return false;
		}

		field = state.fields.expirationMonth;
		if (field.isEmpty) {
			alert ('Selecciona el mes de vencimiento de la tarjeta');
			hostedFieldsInstance.focus ('expirationMonth');
			return false;
		} else if (!field.isValid) {
			alert ('El mes de vencimiento suministrado no es válido');
			hostedFieldsInstance.focus ('expirationMonth');
			return false;
		}

		field = state.fields.expirationYear;
		if (field.isEmpty) {
			alert ('Selecciona el año de vencimiento de la tarjeta');
			hostedFieldsInstance.focus ('expirationYear');
			return false;
		} else if (!field.isValid) {
			alert ('El año de vencimiento suministrado no es válido');
			hostedFieldsInstance.focus ('expirationYear');
			return false;
		}

		field = state.fields.cvv;
		if (field.isEmpty) {
			alert ('Introduce el código de seguridad de la tarjeta');
			hostedFieldsInstance.focus ('cvv');
			return false;
		} else if (!field.isValid) {
			alert ('El código de seguridad suministrado no es válido');
			hostedFieldsInstance.focus ('cvv');
			return false;
		}

		field = jQuery ('#addressid');
		value = field.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			field = jQuery ('#firstname');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce el nombre del cliente');
				field.focus ();
				return false;
			}

			field = jQuery ('#lastname');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce los apellidos del cliente');
				field.focus ();
				return false;
			}

			field = jQuery ('#streetaddress');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce la dirección de facturación');
				field.focus ();
				return false;
			}

			field = jQuery ('#countrycode');
			value = field.val ();
			if ((value === undefined) || (value === null) || (value.trim () === '')) {
				alert ('Introduce el país de la dirección de facturación');
				field.focus ();
				return false;
			}
		}

		postalCode = jQuery ('#zipcode');
		value = postalCode.val ();
		if ((value === undefined) || (value === null) || (value.trim () === '')) {
			alert ('Introduce el código postal de la dirección de facturación');
			field.focus ();
			return false;
		}

		hostedFieldsInstance.tokenize (
			{
				cardholderName: cardholderName.val (),
				postalCode: postalCode.val ()
			},
			function (error, payload) {
				var i, n, message;

				if (error) {
					switch (error.code) {
						case 'HOSTED_FIELDS_FIELDS_EMPTY':
							// occurs when none of the fields are filled in
							alert ('Introduce la información relacionada con el método de pago');
							break;
						case 'HOSTED_FIELDS_FIELDS_INVALID':
							// occurs when certain fields do not pass client side validation
							message = [];
							n = error.details.invalidFieldKeys.length;
							for (i = 0; i < n; i += 1) {
								switch (error.details.invalidFieldKeys [ i ]) {
									case 'number':
										message.push ('- Número de tarjeta');
										break;
									case 'expirationMonth':
										message.push ('- Mes de vencimiento');
										break;
									case 'expirationYear':
										message.push ('- Año de vencimiento');
										break;
									case 'cvv':
										message.push ('- Código de seguridad');
										break;
									default:
										// Do nothing
										break;
								}
							}
							alert ('Revisa la información de los siguientes campos:\n' + message.join ('\n'));
							break;
						case 'HOSTED_FIELDS_TOKENIZATION_FAIL_ON_DUPLICATE':
							// occurs when:
							//   * the client token used for client authorization was generated
							//     with a customer ID and the fail on duplicate payment method
							//     option is set to true
							//   * the card being tokenized has previously been vaulted (with any customer)
							// See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.fail_on_duplicate_payment_method
							alert ('El método de pago suministrado ya ha sido usado');
							break;
						case 'HOSTED_FIELDS_TOKENIZATION_CVV_VERIFICATION_FAILED':
							// occurs when:
							//   * the client token used for client authorization was generated
							//     with a customer ID and the verify card option is set to true
							//     and you have credit card verification turned on in the Braintree
							//     control panel
							//   * the cvv does not pass verfication (https://developers.braintreepayments.com/reference/general/testing/#avs-and-cvv/cid-responses)
							// See: https://developers.braintreepayments.com/reference/request/client-token/generate/#options.verify_card
							alert ('El código de seguridad suministrado no es válido');
							break;
						case 'HOSTED_FIELDS_FAILED_TOKENIZATION':
							// occurs for any other tokenization error on the server
							alert ('Se ha presentado un error con el método de pago. Revisa y vuelve a intentar');
							break;
						case 'HOSTED_FIELDS_TOKENIZATION_NETWORK_ERROR':
							// occurs when the Braintree gateway cannot be contacted
							alert ('Se ha presentado un error de conexión con el proveedor de pagos. Revisa si tu conexión a internet está activa');
							break;
						default:
							alert ('Se ha presentado un error. Intenta más tarde');
							console.error (error);
							break;
					}
					form.find ('input[name="nonce"]').val ('');
				} else {
					form.find ('input[name="nonce"]').val (payload.nonce);
					form.submit ();
				}
			}
		);
		return false;
	};

	window.PaymentCourseUtils = {
		selectBillingAddress: selectBillingAddress,
		validatePaymentForm:  validatePaymentForm
	};

	jQuery (document).ready (function () {
		var hasCC  = jQuery ('#hasCredidcart').val ();
            if (hasCC !== '1') {
                initializeBraintreeHostedFields ();
            }
    });
} (jQuery));
