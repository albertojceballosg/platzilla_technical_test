<?php

	class PaymentGatewayException extends Exception {
		const ERROR_PAYMENT_GATEWAY_CUSTOMER_NOT_REGISTERED               = 'El cliente no está registrado en la pasarela de pagos';
		const ERROR_PAYMENT_GATEWAY_DEFAULT_PAYMENT_METHOD_NOT_REGISTERED = 'El cliente no tiene asociado un método de pago por defecto';
		const ERROR_PAYMENT_GATEWAY_DEFAULT_SUBSCRIPTION_NOT_REGISTERED   = 'El cliente no tiene registrada una suscripción';
		const ERROR_PAYMENT_GATEWAY_EMPTY_ARGUMENTS                       = 'No has suministrado los argumentos del método de pago';
		const ERROR_PAYMENT_GATEWAY_EMPTY_ADDRESS                         = 'No has suministrado la dirección de cobro';
		const ERROR_PAYMENT_GATEWAY_EMPTY_COUNTRY                         = 'No has suministrado el país';
		const ERROR_PAYMENT_GATEWAY_EMPTY_FIRST_NAME                      = 'No has suministrado el nombre del cliente';
		const ERROR_PAYMENT_GATEWAY_EMPTY_INSTANCE_CODE                   = 'No has suministrado el código de la instancia';
		const ERROR_PAYMENT_GATEWAY_EMPTY_LAST_NAME                       = 'No has suministrado los apellidos del cliente';
		const ERROR_PAYMENT_GATEWAY_EMPTY_NONCE                           = 'No has suministrado el token generado por el proveedor de cobros';
		const ERROR_PAYMENT_GATEWAY_EMPTY_SUBSCRIPTION                    = 'No tienes una suscripción activa con el proveedor de cobros';
		const ERROR_PAYMENT_GATEWAY_EMPTY_ZIPCODE                         = 'No has suministrado el código postal';
		const ERROR_PAYMENT_GATEWAY_INVALID_INSTANCE_CODE                 = 'No se encuentra registrada la instancia con el código';
		const ERROR_PAYMENT_GATEWAY_INVALID_PAYMENT_METHOD                = 'El método de pago suministrado no está asociado al cliente';
		const ERROR_PAYMENT_GATEWAY_UNABLE_TO_DELETE_CUSTOMER             = 'Imposible eliminar el cliente en la pasarela de pagos';
		const ERROR_PAYMENT_GATEWAY_UNKNOWN_ADDRESS                       = 'La dirección suministrada no está registrada';

	}
