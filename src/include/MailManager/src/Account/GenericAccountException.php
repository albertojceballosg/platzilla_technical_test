<?php
	namespace Platzilla\MailManager\Account;

	use Platzilla\MailManager\MailException;

	class GenericAccountException extends MailException {
		const EMPTY_EMAIL_ADDRESS   = 'No se ha suministrado la dirección de correo';
		const INVALID_ACCESS_TOKEN  = 'No se ha suministrado el token de acceso';
		const INVALID_EMAIL_ADDRESS = 'La dirección de correo suministrada no es válida';
		const INVALID_PROVIDER      = 'El proveedor suministrado no es válido';

	}
